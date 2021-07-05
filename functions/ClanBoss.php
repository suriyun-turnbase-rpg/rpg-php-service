<?php
function CreateClanEvent()
{
    // If player is not joined clan, skip it
    $player = GetPlayer();
    if ($player->clanId <= 0) {
        return;
    }
    $clanId = $player->clanId;
    $gameData = \Base::instance()->get('GameData');
    // Create date is server today timestamp
    $createDate = mktime(0, 0, 0);
    $clanEventCreationDb = new ClanEventCreation();
    $clanEventCreation = $clanEventCreationDb->findone(array(
        'clanId = ? AND createDate = ?',
        $clanId,
        $createDate,
    ));
    if ($clanEventCreation) {
        // Already create events, skip it
        return;
    }
    $eventIds = array();
    // Create clan event by stage data
    foreach ($gameData['clanBossStages'] as $id => $stage) {
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
                    // Create new clan event
                    $clanEvent = new ClanEvent();
                    $clanEvent->clanId = $clanId;
                    $clanEvent->dataId = $id;
                    $clanEvent->remainingHp = $stage['maxHp'];
                    $clanEvent->startTime = $fromTime;
                    $clanEvent->endTime = $toTime;
                    $clanEvent->save();
                    $eventIds[] = $clanEvent->id;
                }
            }
        }
    }
    // Create new clan event creation
    $clanEventCreation = new ClanEventCreation();
    $clanEventCreation->clanId = $clanId;
    $clanEventCreation->createDate = $createDate;
    $clanEventCreation->events = json_encode($eventIds);
    $clanEventCreation->save();
}

function ClanEventRewarding()
{
    // If player is not joined clan, skip it
    $player = GetPlayer();
    if ($player->clanId <= 0) {
        return;
    }
    $clanId = $player->clanId;
    $gameData = \Base::instance()->get('GameData');
    $currentTime = time();
    $endTime = $currentTime - \Base::instance()->get('clan_boss_rewarding_delay');
    $clanEventDb = new ClanEvent();
    $clanEvents = $clanEventDb->find(array(
        'endTime <= ? AND rewarded = 0',
        $endTime
    ));
    foreach ($clanEvents as $index => $clanEvent) {
        // Rewarding
        $rankCount = 0;
        $stageDataId = $clanEvent->dataId;
        $stage = $gameData['clanBossStages'][$stageDataId];
        $rewards = $stage['rewards'];
        $rewardsCount = count($rewards);
        $clanEventRankingDb = new ClanEventRanking();
        $clanEventRankings = $clanEventRankingDb->find(array(
            'eventId = ?',
            $clanEvent->id
        ), array(
            'order' => 'damage ASC, updatedAt ASC',
            'LIMIT' => $rankingLimit
        ));
        foreach ($clanEventRankings as $index2 => $clanEventRanking) {
            $damage = $clanEventRanking->damage;
            for ($i = 0; $i < $rewardsCount; $i++) { 
                $reward = $rewards[$i];
                if ($reward['damageDealtMin'] >= $damage && 
                    ($reward['damageDealtMax'] <= 0 || $reward['damageDealtMax'] < $damage))
                {
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
                    $mail->playerId  = $clanEventRanking->playerId;
                    $mail->title = "Clan boss reward#".$rankCount;
                    if (!empty($items) || !empty($currencies)) {
                        $mail->items = json_encode($items);
                        $mail->currencies = json_encode($currencies);
                        $mail->hasReward = 1;
                    }
                    $mail->save();
                }
            }
        }
        $clanEvent->rewarded = 1;
        $clanEvent->save();
    }
}

function StartClanBossBattle($eventId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    
    $currentTime = time();
    $clanEventDb = new ClanEvent();
    $clanEvent = $clanEventDb->findone(array(
        'id = ? AND clanId = ? AND remainingHp > 0 AND startTime < ? AND endTime >= ? AND rewarded = 0',
        $eventId,
        $clanId,
        $currentTime,
        $currentTime
    ));
    if (!$clanEvent) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $stageDataId = $clanEvent->dataId;
        $stage = $gameData['clanBossStages'][$stageDataId];
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
                EBattleType::ClanBoss
            ));

            $session = md5($playerId . '_' . $eventId . '_' . time());
            $newData = new PlayerBattle();
            $newData->playerId = $playerId;
            $newData->dataId = $eventId;
            $newData->session = $session;
            $newData->battleType = EBattleType::ClanBoss;
            $newData->save();

            $output['stamina'] = CursorToArray($stamina);
            $output['session'] = $session;
            $output['remainingHp'] = $clanEvent->remainingHp;
        }
    }
    echo json_encode($output);
}

function FinishClanBossBattle($session, $battleResult, $totalDamage, $deadCharacters)
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
        EBattleType::ClanBoss
    ));

    $clanEventDb = new ClanEvent();
    $clanEvent = $clanEventDb->findone(array(
        'id = ? AND rewarded = 0',
        $playerBattle->dataId
    ));

    if (!$playerBattle || !$clanEvent) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION';
    } else {
        // Set clan event
        $stageDataId = $clanEvent->dataId;
        $stage = $gameData['clanBossStages'][$stageDataId];
        $maxHp = $stage['maxHp'];
        $sumDamage = 0;
        // Find old battles to calculate damage
        $oldPlayerBattles = $playerBattleDb->find(array(
            'id != ? AND playerId = ? AND dataId = ? AND battleType = ?',
            $playerBattle->id,
            $playerId,
            $playerBattle->dataId,
            EBattleType::ClanBoss
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
            $clanEventRankingDb = new ClanEventRanking();
            $clanEventRanking = $clanEventRankingDb->findone(array(
                'playerId = ? AND eventId = ?',
                $playerId,
                $clanEvent->id
            ));
            if (!$clanEventRanking) {
                $clanEventRanking = new ClanEventRanking();
                $clanEventRanking->playerId = $playerId;
                $clanEventRanking->eventId = $clanEvent->id;
            }
            $clanEventRanking->damage = $sumDamage + $totalDamage;
            $clanEventRanking->save();
            // Set remaining HP and end time
            $clanEvent->remainingHp = $clanEvent->remainingHp - $totalDamage;
            if ($clanEvent->remainingHp <= 0) {
                $clanEvent->remainingHp = 0;
                $clanEvent->endTime = time();
            }
            $clanEvent->update();
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
        $output['clanEvent'] = CursorToArray($clanEvent);
    }
    echo json_encode($output);
}
?>