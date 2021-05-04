<?php
function CreateRaidEvent()
{
    $gameData = \Base::instance()->get('GameData');
    // Create date is server today timestamp
    $createDate = mktime(0, 0, 0);
    $raidEventCreationDb = new RaidEventCreation();
    $raidEventCreation = $raidEventCreationDb->findone(array(
        'createDate = ?',
        $createDate,
    ));
    if ($raidEventCreation) {
        // Already create events, skip it
        return;
    }
    $eventIds = array();
    // Create raid event by stage data
    foreach ($gameData['stages'] as $id => $stage) {
        if ($stage['stageType'] != EStageType::RaidEvent && $stage['stageType'] != EStageType::ClanRaidEvent)
        {
            // stage type must be raid event or clan raid event
            continue;
        }
        $hasAvailableDate = $stage['hasAvailableDate'];
        $startDate = mktime(0, 0, 0, $stage['startMonth'], $stage['startDay'], $stage['startYear']);
        $endDate = $startDate + (60*60*24*$stage['durationDays']);
        if ($hasAvailableDate && ($createDate < $startDate || $createDate > $endDate))
        {
            // stage is not available
            continue;
        }
        $availabilities = $stage['availabilities'];
        if (!empty($availabilities)) {
            $available = false;
            foreach ($availabilities as $key => $value) {
                if (date('w') == $value['day']) {
                    $fromTime = mktime($value['startTimeHour'], $value['startTimeMinute'], 0);
                    $toTime = $fromTime + (60*60*$value['durationHour']) + (60*$value['durationMinute']);
                    // Create new raid event
                    $raidEvent = new RaidEvent();
                    $raidEvent->dataId = $id;
                    $raidEvent->remainingHp = 0;    // Implement this
                    $raidEvent->startTime = $fromTime;
                    $raidEvent->endTime = $toTime;
                }
            }
        }
    }
}

function StartRaidBossBattle($eventId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    $currentTime = time();
    $raidEventDb = new RaidEvent();
    $raidEvent = $raidEventDb->findone(array(
        'id = ? AND remainingHp > 0 AND startTime > ? AND endTime < ?',
        $eventId,
        $currentTime,
        $currentTime
    ));
    if (!$raidEvent) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $stageDataId = $raidEvent->dataId;
        $stage = $gameData['stages'][$stageDataId];
        $staminaId = $gameData['stageStaminaId'];
        if (!empty($stage['requireCustomStamina']) && !empty($gameData['staminas'][$stage['requireCustomStamina']]))
        {
            // Use custom stamina
            $staminaId = $stage['requireCustomStamina'];
        }
        $stamina = GetStamina($playerId, $staminaId);
        
        if (!DecreasePlayerStamina($playerId, $staminaTable['id'], $stage['requireStamina']))
        {
            $output['error'] = 'ERROR_NOT_ENOUGH_STAGE_STAMINA';
        }
        else
        {
            $playerBattleDb = new PlayerBattle();
            $playerBattleDb->erase(array(
                'playerId = ? AND battleResult = ? AND battleType = ?',
                $playerId,
                EBattleResult::None,
                EBattleType::RaidBoss
            ));

            $session = md5($playerId . '_' . $eventId . '_' . time());
            $newData = new PlayerBattle();
            $newData->playerId = $playerId;
            $newData->dataId = $eventId;
            $newData->session = $session;
            $newData->battleType = EBattleType::RaidBoss;
            $newData->save();

            $output['stamina'] = CursorToArray($stamina);
            $output['session'] = $session;
        }
    }
    echo json_encode($output);
}

function FinishRaidBossBattle($session, $battleResult, $totalDamage, $deadCharacters)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerBattleDb = new PlayerBattle();
    $playerBattle = $playerBattleDb->findone(array(
        'playerId = ? AND session = ? AND battleType = ?',
        $playerId,
        $session,
        EBattleType::RaidBoss
    ));

    $raidEventDb = new RaidEvent();
    $raidEvent = $raidEventDb->findone(array(
        'id = ?',
        $playerBattle->dataId
    ));

    if (!$playerBattle) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION';
    } else {
        // Set raid event
        $raidEvent->remainingHp = $raidEvent->remainingHp - $totalDamage;
        if ($raidEvent->remainingHp < 0) {
            $raidEvent->remainingHp = 0;
        }
        $raidEvent->update();
        // Set battle session
        $playerBattle->battleResult = $battleResult;
        $playerBattle->totalDamage = $totalDamage;
        if ($battleResult == EBattleResult::Win)
        {
            $rating = 3 - $deadCharacters;
            if ($rating <= 0) {
                $rating = 1;
            }
        }
        $playerBattle->rating = $rating;
        $playerBattle->update();

        $output['totalDamage'] = $totalDamage;
        $output['rating'] = $rating;
    }
    echo json_encode($output);
}
?>