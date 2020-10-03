<?php
function StartStage($stageDataId, $helperPlayerId)
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
        EBattleType::Stage
    ));

    $stage = $gameData['stages'][$stageDataId];
    if (!$stage) {
        $output['error'] = 'ERROR_INVALID_STAGE_DATA';
    } else if (!DecreasePlayerStamina($playerId, $gameData['stageStaminaId'], $stage['requireStamina'])) {
        $output['error'] = 'ERROR_NOT_ENOUGH_STAGE_STAMINA';
    } else {
        $session = md5($playerId . '_' . $stageDataId . '_' . time());
        $newData = new PlayerBattle();
        $newData->playerId = $playerId;
        $newData->dataId = $stageDataId;
        $newData->session = $session;
        $newData->save();

        $staminaTable = $gameData['staminas'][$gameData['stageStaminaId']];
        $stamina = GetStamina($playerId, $staminaTable['id']);
        
        if (!empty($helperPlayerId))
        {
            // Update achievement
            QueryUpdateAchievement(UpdateCountUseHelper($player->id, GetAchievementListInternal($player->id)));
        }

        $output['stamina'] = CursorToArray($stamina);
        $output['session'] = $session;
    }
    echo json_encode($output);
}

function FinishStage($session, $battleResult, $deadCharacters)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerBattleDb = new PlayerBattle();
    $playerBattle = $playerBattleDb->findone(array(
        'playerId = ? AND session = ?',
        $playerId,
        $session
    ));

    if (!$playerBattle) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION'.$session;
    } else {
        $stage = $gameData['stages'][$playerBattle->dataId];
        if (!$stage) {
            $output['error'] = 'ERROR_INVALID_STAGE_DATA';
        } else {
            // Prepare results
            $output['rewardItems'] = array();
            $output['createItems'] = array();
            $output['updateItems'] = array();
            $output['deleteItemIds'] = array();
            $output['updateCurrencies'] = array();
            $output['rewardPlayerExp'] = 0;
            $output['rewardCharacterExp'] = 0;
            $output['rating'] = 0;
            $output['firstClearRewardPlayerExp'] = 0;
            $output['firstClearRewardSoftCurrency'] = 0;
            $output['firstClearRewardHardCurrency'] = 0;
            $output['firstClearRewardItems'] = array();
            $rewardItems = array();
            $createItems = array();
            $updateItems = array();
            $deleteItemIds = array();
            $updateCurrencies = array();
            $rewardPlayerExp = 0;
            $rewardCharacterExp = 0;
            $rewardSoftCurrency = 0;
            $rating = 0;
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
                $playerSelectedFormation = $player->selectedFormation;
                // Player exp
                $rewardPlayerExp = $stage['rewardPlayerExp'];
                $player->exp += $rewardPlayerExp;
                // Character exp
                $characterIds = GetFormationCharacterIds($playerId, $playerSelectedFormation);
                $countCharacterIds = count($characterIds);
                if ($countCharacterIds > 0)
                {
                    $devivedExp = floor($stage['rewardCharacterExp'] / $countCharacterIds);
                    $rewardCharacterExp = $devivedExp;
                    $playerItemDb = new PlayerItem();
                    for ($i = 0; $i < $countCharacterIds; ++$i)
                    {
                        $characterId = $characterIds[$i];
                        $characterEntry = $playerItemDb->findone(array(
                            'id = ?',
                            $characterId
                        ));
                        if ($characterEntry) {
                            $characterEntry->exp += $devivedExp;
                            $characterEntry->update();
                            $updateItems[] = $characterEntry;
                        }
                    }
                }
                // Soft currency
                $rewardSoftCurrency = rand($stage['randomSoftCurrencyMinAmount'], $stage['randomSoftCurrencyMaxAmount']);
                $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
                $softCurrency->amount += $rewardSoftCurrency;
                $softCurrency->update();
                $updateCurrencies[] = $softCurrency;
                // Items
                $stageRewardItems = $stage['rewardItems'];
                $countRewardItems = count($stageRewardItems);
                for ($i = 0; $i < $countRewardItems; ++$i)
                {
                    $rewardItem = $stageRewardItems[$i];
                    if (empty($rewardItem) || empty($rewardItem['id']) || rand(0, 1) > $rewardItem['randomRate']) {
                        continue;
                    }
                    
                    $addItemsResult = AddItems($playerId, $rewardItem['id'], $rewardItem['amount']);
                    if ($addItemsResult['success'])
                    {
                        $rewardItems[] = CreateEmptyItem($i, $playerId, $rewardItem['id'], $rewardItem['amount']);
                        
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
                $output['rewardItems'] = ItemCursorsToArray($rewardItems);
                $output['createItems'] = ItemCursorsToArray($createItems);
                $output['updateItems'] = ItemCursorsToArray($updateItems);
                $output['deleteItemIds'] = $deleteItemIds;
                $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
                $output['rewardPlayerExp'] = $rewardPlayerExp;
                $output['rewardCharacterExp'] = $rewardCharacterExp;
                $output['rewardSoftCurrency'] = $rewardSoftCurrency;
                $output['rating'] = $rating;
                $output = HelperClearStage($createItems, $updateItems, $output, $player, $stage, $rating);
            }
            $player->update();
            $output['player'] = CursorToArray($player);
        }
    }
    echo json_encode($output);
}

function ReviveCharacters()
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $hardCurrency = GetCurrency($player->id, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
    $revivePrice = $gameData['revivePrice'];
    if ($revivePrice > $hardCurrency->amount) {
        $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
    }
    else
    {
        $hardCurrency->amount -= $revivePrice;
        $hardCurrency->update();
        $updateCurrencies = array();
        $updateCurrencies[] = $hardCurrency;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    }
    // Update achievement
    QueryUpdateAchievement(UpdateCountRevive($player->id, GetAchievementListInternal($player->id)));
    
    echo json_encode($output);
}

function SelectFormation($formationName, $formationType)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    if ($formationType == EFormationType::Stage)
    {
        $player->selectedFormation = $formationName;
    }
    else if ($formationType == EFormationType::Arena)
    {
        $player->selectedArenaFormation = $formationName;
    }
    $player->update();
    $output['player'] = CursorToArray($player);
    echo json_encode($output);
}

function SetFormation($characterId, $formationName, $position)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    HelperSetFormation($playerId, $characterId, $formationName, $position);
    $playerFormationDb = new PlayerFormation();
    $list = $playerFormationDb->find(array(
        'playerId = ?',
        $playerId
    ));
    $output['list'] = CursorsToArray($list);
    echo json_encode($output);
}
?>