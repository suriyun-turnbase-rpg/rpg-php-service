<?php
function StartDuel($targetPlayerId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerBattleDb = new PlayerBattle();
    $playerBattleDb->erase(array(
        'playerId = ? AND battleResult = ? AND battleType = ?',
        $playerId,
        EBattleResult::None,
        EBattleType::Arena
    ));

    if (!DecreasePlayerStamina($playerId, 'ARENA', 1)) {
        $output['error'] = 'ERROR_NOT_ENOUGH_ARENA_STAMINA';
    } else {
        $session = md5($playerId . '_' . $targetPlayerId . '_' . time());
        $newData = new PlayerBattle();
        $newData->playerId = $playerId;
        $newData->dataId = $targetPlayerId;
        $newData->session = $session;
        $newData->save();
        
        $opponent = new Player();
        $opponent = $opponent->load($targetPlayerId);
        
        $staminaTable = $gameData['staminas']['ARENA'];
        $stamina = GetStamina($playerId, $staminaTable['id']);
        $output['stamina'] = CursorToArray($stamina);
        $output['session'] = $session;
        
        $opponentCharacters = [];
        $opponentCharacterIds = GetFormationCharacterIds($opponent->id, $opponent->selectedArenaFormation);
        $count = count($opponentCharacterIds);
        $playerItemDb = new PlayerItem();
        for ($i = 0; $i < $count; ++$i)
        {
            $characterId = $opponentCharacterIds[$i];
            $characterEntry = $playerItemDb->load(array(
                'id = ?',
                $characterId
            ));
            if ($characterEntry) {
                $opponentCharacters[] = $characterEntry;
            }
        }
        $output['opponentCharacters'] = CursorsToArray($opponentCharacters);
    }
    echo json_encode($output);
}

function FinishDuel($session, $battleResult, $deadCharacters)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerBattleDb = new PlayerBattle();
    $playerBattle = $playerBattleDb->load(array(
        'playerId = ? AND session = ?',
        $playerId,
        $session
    ));

    if (!$playerBattle) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION';
    } else {
        // Prepare results
        $rewardItems = array();
        $createItems = array();
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $rewardSoftCurrency = 0;
        $rewardHardCurrency = 0;
        $rating = 0;
        $updateScore = 0;
        $arenaScore = $player->arenaScore;
        $arenaRank = GetArenaRank($arenaScore);
        // Set battle session
        $playerBattle->battleResult = $battleResult;
        if ($battleResult == EBattleResult::Win)
        {
            $rating = 3 - $deadCharacters;
            if ($rating <= 0) {
                $rating = 1;
            }
        }
        $playerBattle->rating = $rating;
        $playerBattle->update();
        if ($battleResult == EBattleResult::Win)
        {
            $oldArenaLevel = CalculateArenaRankLevel($arenaScore);
            $highestArenaRank = $player->highestArenaRank;
            $highestArenaRankCurrentSeason = $player->highestArenaRankCurrentSeason;
            $updateScore = $gameData['arenaWinScoreIncrease'];
            $arenaScore += $gameData['arenaWinScoreIncrease'];
            $player->arenaScore = $arenaScore;
            $arenaLevel = CalculateArenaRankLevel($arenaScore);
            // Arena rank up, rewarding items
            if ($arenaRank && $arenaLevel > $oldArenaLevel && $highestArenaRankCurrentSeason < $arenaLevel)
            {
                // Update highest rank
                $player->highestArenaRankCurrentSeason = $arenaLevel;
                if ($highestArenaRank < $arenaLevel) {
                    $player->highestArenaRank = $arenaLevel;
                }
                    
                // Soft currency
                $rewardSoftCurrency = $arenaRank['rewardSoftCurrency'];
                $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
                $softCurrency->amount += $rewardSoftCurrency;
                $softCurrency->update();
                $updateCurrencies[] = $softCurrency;
                // Hard currency
                $rewardHardCurrency = $arenaRank['rewardHardCurrency'];
                $hardCurrency = GetCurrency($playerId, $gameData['currencies']['HARD_CURRENCY']['id']);
                $hardCurrency->amount += $rewardHardCurrency;
                $hardCurrency->update();
                $updateCurrencies[] = $hardCurrency;
                // Items
                $arenaRewardItems = $arenaRank['rewardItems'];
                $countRewardItems = count($arenaRewardItems);
                for ($i = 0; $i < $countRewardItems; ++$i) {
                    $rewardItem = $arenaRewardItems[$i];
                    if (empty($rewardItem) || empty($rewardItem['id'])) {
                        continue;
                    }
                    
                    $addItemsResult = AddItems($playerId, $rewardItem['id'], $rewardItem['amount']);
                    if ($addItemsResult['success'])
                    {
                        $newRewardEntry = new PlayerItem();
                        $newRewardEntry->playerId = $playerId;
                        $newRewardEntry->dataId = $rewardItem['id'];
                        $newRewardEntry->amount = $rewardItem['amount'];
                        $rewardItems[] = $newRewardEntry;
                        
                        $resultCreateItems = $addItemsResult['createItems'];
                        $resultUpdateItems = $addItemsResult['updateItems'];
                        $countCreateItems = count($resultCreateItems);
                        $countUpdateItems = count($resultUpdateItems);
                        for ($j = 0; $j < $countCreateItems; ++$j)
                        {
                            $createItem = $resultCreateItems[$j];
                            $createItem->save();
                            HelperUnlockItem($playerId, $createItem->dataId);
                            $createItems[] = $createItem;
                        }
                        for ($j = 0; $j < $countUpdateItems; ++$j)
                        {
                            $updateItem = $resultUpdateItems[$j];
                            $updateItem->update();
                            $updateItems[] = $updateItem;
                        }
                    }
                    // End add item condition
                }
                // End reward items loop
            }
            // Update achievement
            QueryUpdateAchievement(UpdateCountWinDuel($player->id, GetAchievementListInternal($player->id)));
        }
        else
        {
            $updateScore = -$gameData['arenaLoseScoreDecrease'];
            $arenaScore -= $gameData['arenaLoseScoreDecrease'];
            if ($arenaScore < 0)
            {
                // Min arena score is 0
                $arenaScore = 0;
            }
            $player->arenaScore = $arenaScore;
        }
        $player->update();
        $output['rewardItems'] = CursorsToArray($rewardItems);
        $output['createItems'] = CursorsToArray($createItems);
        $output['updateItems'] = CursorsToArray($updateItems);
        $output['deleteItemIds'] = $deleteItemIds;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        $output['rewardSoftCurrency'] = $rewardSoftCurrency;
        $output['rewardHardCurrency'] = $rewardHardCurrency;
        $output['rating'] = $rating;
        $output['updateScore'] = $updateScore;
        $output['player'] = CursorToArray($player);
    }
    echo json_encode($output);
}
?>