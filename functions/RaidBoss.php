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
    foreach ($gameData['raidBossStages'] as $id => $stage) {
        if ($stage['maxHp'] <= 0)
        {
            // character max hp must > 0
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
        if (!empty($availabilities))
        {
            foreach ($availabilities as $key => $value)
            {
                if (date('w') == $value['day'])
                {
                    $fromTime = mktime($value['startTimeHour'], $value['startTimeMinute'], 0);
                    $toTime = $fromTime + (60*60*$value['durationHour']) + (60*$value['durationMinute']);
                    // Create new raid event
                    $raidEvent = new RaidEvent();
                    $raidEvent->dataId = $id;
                    $raidEvent->remainingHp = $stage['maxHp'];
                    $raidEvent->startTime = $fromTime;
                    $raidEvent->endTime = $toTime;
                    $raidEvent->save();
                    $eventIds[] = $raidEvent->id;
                }
            }
        }
    }
    // Create new raid event creation
    $raidEventCreation = new RaidEventCreation();
    $raidEventCreation->createDate = $createDate;
    $raidEventCreation->events = json_encode($eventIds);
    $raidEventCreation->save();
}

function RaidEventRewarding()
{
    $gameData = \Base::instance()->get('GameData');
    $currentTime = time();
    $endTime = $currentTime - (60 * 15);
    $raidEventDb = new RaidEvent();
    $raidEvents = $raidEventDb->find(array(
        'endTime <= ? AND rewarded = 0',
        $endTime
    ));
    foreach ($raidEvents as $index => $raidEvent) {
        // Rewarding
        $rankCount = 0;
        $stageDataId = $raidEvent->dataId;
        $stage = $gameData['raidBossStages'][$stageDataId];
        $rewards = $stage['rewards'];
        $rewardIndex = 0;
        $rankingLimit = 0;
        foreach ($rewards as $rewardInddex => $reward) {
            if ($reward['rankMax'] > $rankingLimit) {
                $rankingLimit = $reward['rankMax'];
            }
        }
        $raidEventRankingDb = new RaidEventRanking();
        $raidEventRankings = $raidEventRankingDb->find(array(
            'eventId = ?',
            $raidEvent->id
        ), array(
            'order' => 'damage DESC, updatedAt ASC',
            'LIMIT' => $rankingLimit
        ));
        foreach ($raidEventRankings as $index2 => $raidEventRanking) {
            $rankCount++;
            $reward = $rewards[$rewardIndex];
            $items = $reward['rewardItems'];
            $currencies = $reward['rewardCustomCurrencies'];
            if (!empty($reward['rewardSoftCurrency'])) {
                $currencies[] = array(
                    'id' => $gameData['softCurrencyId'],
                    'amount' => $reward['rewardSoftCurrency']
                );
            }
            if (!empty($reward['rewardHardCurrency'])) {
                $currencies[] = array(
                    'id' => $gameData['hardCurrencyId'],
                    'amount' => $reward['rewardHardCurrency']
                );
            }
            // Send mail reward
            $mail = new Mail();
            $mail->playerId  = $raidEventRanking->playerId;
            $mail->title = "Raid boss reward#".$rankCount;
            if (!empty($items) || !empty($currencies)) {
                $mail->items = json_encode($items);
                $mail->currencies = json_encode($currencies);
                $mail->hasReward = 1;
            }
            $mail->save();
            // Update reward index
            if ($rankCount >= $reward['rankMax']) {
                $rewardIndex++;
            }
        }
        $raidEvent->rewarded = 1;
        $raidEvent->save();
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
        'id = ? AND remainingHp > 0 AND startTime < ? AND endTime >= ? AND rewarded = 0',
        $eventId,
        $currentTime,
        $currentTime
    ));
    if (!$raidEvent) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $stageDataId = $raidEvent->dataId;
        $stage = $gameData['raidBossStages'][$stageDataId];
        $staminaId = $gameData['stageStaminaId'];
        if (!empty($stage['requireCustomStamina']) && !empty($gameData['staminas'][$stage['requireCustomStamina']]))
        {
            // Use custom stamina
            $staminaId = $stage['requireCustomStamina'];
        }
        $stamina = GetStamina($playerId, $staminaId);
        
        if (!DecreasePlayerStamina($playerId, $staminaId, $stage['requireStamina']))
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
            $output['remainingHp'] = $raidEvent->remainingHp;
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
        'id = ? AND rewarded = 0',
        $playerBattle->dataId
    ));

    if (!$playerBattle || !$raidEvent) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION';
    } else {
        // Set raid event
        $stageDataId = $raidEvent->dataId;
        $stage = $gameData['raidBossStages'][$stageDataId];
        $maxHp = $stage['maxHp'];
        $sumDamage = 0;
        // Find old battles to calculate damage
        $oldPlayerBattles = $playerBattleDb->find(array(
            'id != ? AND playerId = ? AND dataId = ? AND battleType = ?',
            $playerBattle->id,
            $playerId,
            $playerBattle->dataId,
            EBattleType::RaidBoss
        ));
        // Calculate damage
        foreach ($oldPlayerBattles as $index => $oldBattle) {
            $sumDamage += $oldBattle->totalDamage;
        }
        // Player trying to hack?
        if ($sumDamage >= $maxHp) {
            $totalDamage = 0; 
        } else {
            // Total damage must not over max HP
            if ($sumDamage + $totalDamage > $maxHp) {
                $totalDamage = $maxHp - $sumDamage;
            }
            // Set ranking
            $raidEventRankingDb = new RaidEventRanking();
            $raidEventRanking = $raidEventRankingDb->findone(array(
                'playerId = ? AND eventId = ?',
                $playerId,
                $raidEvent->id
            ));
            if (!$raidEventRanking) {
                $raidEventRanking = new RaidEventRanking();
                $raidEventRanking->playerId = $playerId;
                $raidEventRanking->eventId = $raidEvent->id;
            }
            $raidEventRanking->damage = $sumDamage + $totalDamage;
            $raidEventRanking->save();
            // Set remaining HP and end time
            $raidEvent->remainingHp = $raidEvent->remainingHp - $totalDamage;
            if ($raidEvent->remainingHp <= 0) {
                $raidEvent->remainingHp = 0;
                $raidEvent->endTime = time();
            }
            $raidEvent->update();
        }
        $rating = 0;
        // Set battle session
        $playerBattle->battleResult = $battleResult;
        $playerBattle->totalDamage = $totalDamage;
        if ($battleResult == EBattleResult::Win) {
            $rating = 3 - $deadCharacters;
            if ($rating <= 0) {
                $rating = 1;
            }
        }
        $playerBattle->rating = $rating;
        $playerBattle->update();

        $output['totalDamage'] = $sumDamage + $totalDamage;
        $output['rating'] = $rating;
        $output['raidEvent'] = CursorToArray($raidEvent);
    }
    echo json_encode($output);
}
?>