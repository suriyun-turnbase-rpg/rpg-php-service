<?php
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
        $staminaTable = $gameData['staminas'][$gameData['stageStaminaId']];
        if (!empty($stage['requireCustomStamina']) && !empty($gameData['staminas'][$stage['requireCustomStamina']]))
        {
            // Use custom stamina
            $staminaTable = $gameData['staminas'][$stage['requireCustomStamina']];
        }
        $stamina = GetStamina($playerId, $staminaTable['id']);
        
        if (!DecreasePlayerStamina($playerId, $staminaTable['id'], $stage['requireStamina']))
        {
            $output['error'] = 'ERROR_NOT_ENOUGH_STAGE_STAMINA';
        }
        else
        {
            $session = md5($playerId . '_' . $eventId . '_' . time());
            $newData = new PlayerBattle();
            $newData->playerId = $playerId;
            $newData->dataId = $eventId;
            $newData->session = $session;
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