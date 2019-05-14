<?php
function StartStage($stageDataId)
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
    } else if (!DecreasePlayerStamina($playerId, 'STAGE', $stage['requireStamina'])) {
        $output['error'] = 'ERROR_NOT_ENOUGH_STAGE_STAMINA';
    } else {
        $session = md5($playerId + '_' + $stageDataId + '_' + time());
        $newData = new PlayerBattle();
        $newData->playerId = $playerId;
        $newData->dataId = $dataId;
        $newData->session = $session;
        $newData->save();

        $staminaTable = $gameData['staminas']['STAGE'];
        $stamina = GetStamina($playerId, $staminaTable['id']);
        $output['stamina'] = $stamina;
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
    $playerBattle = $playerBattleDb->load(array(
        'playerId = ? AND session = ?',
        $playerId,
        $session
    ));

    if ($playerBattle) {
        $output['error'] = 'ERROR_INVALID_BATTLE_SESSION';
    } else {
        $stage = $gameData['stages'][$playerBattle->dataId];
        if (!$stage) {
            $output['error'] = 'ERROR_INVALID_STAGE_DATA';
        } else {
            // Prepare results
            $rewardItems = array();
            $createItems = array();
            $updateItems = array();
            $deleteItemIds = array();
            $updateCurrencies = array();
            $rewardPlayerExp = 0;
            $rewardCharacterExp = 0;
            $rewardSoftCurrency = 0;
            $rating = 0;
            $clearedStage = array();
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
                $rewardPlayerExp = $stage['rewardPlayerExp'];
                // Player exp
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
                        $characterEntry = $playerItemDb->load(array(
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
                $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']);
                $softCurrency->amount += $rewardSoftCurrency;
                $updateCurrencies[] = $softCurrency;
                // Items
                $rewardItems = $stage['rewardItems'];
                $countRewardItems = $stage['rewardItems'];
                for ($i = 0; $i < $countRewardItems; ++$i)
                {
                    $rewardItem = $rewardItems[$i];
                    if (empty($rewardItem) || empty($rewardItem['id']) || rand(0, 1) > $rewardItem['randomRate']) {
                        continue;
                    }
                    
                    $addItemsResult = AddItems($playerId, $rewardItem['id'], $rewardItem['amount']);
                    if ($addItemsResult['success'])
                    {
                        $createItems = $addItemsResult['createItems'];
                        $updateItems = $addItemsResult['updateItems'];
                        $countCreateItems = count($createItems);
                        $countUpdateItems = count($updateItems);
                        for ($j = 0; $j < $countCreateItems; ++$j)
                        {
                            $createItem = $createItems[$j];
                            $createItem->save();
                            HelperUnlockItem($playerId, $createItem->dataId);
                            $rewardItems[] = $createItem;
                            $createItems[] = $createItem;
                        }
                        for ($j = 0; j < $countUpdateItems; ++$j)
                        {
                            $updateItem = $updateItems[$j];
                            $updateItem->update();
                            $rewardItems[] = $updateItem;
                            $updateItems[] = $updateItem;
                        }
                    }
                    // End add item condition
                }
                // End reward items loop
                $clearedStage = HelperClearStage($playerId, $stage['id'], $rating);
            }
            $player->update();
            $output['rewardItems'] = $rewardItems;
            $output['createItems'] = $createItems;
            $output['updateItems'] = $updateItems;
            $output['deleteItemIds'] = $deleteItemIds;
            $output['updateCurrencies'] = $updateCurrencies;
            $output['rewardPlayerExp'] = $rewardPlayerExp;
            $output['rewardCharacterExp'] = $rewardCharacterExp;
            $output['rewardSoftCurrency'] = $rewardSoftCurrency;
            $output['rating'] = $rating;
            $output['clearStage'] = $clearedStage;
            $output['player'] = $player;
        }
    }
    echo json_encode($output);
}

function ReviveCharacters()
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $hardCurrencyId = $gameData['currencies']['HARD_CURRENCY'];
    $hardCurrency = GetCurrency($playerId, $hardCurrencyId);
    $revivePrice = $gameData['revivePrice'];
    if ($revivePrice > $hardCurrency->amount) {
        $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
    }
    else
    {
        $hardCurrency->amount -= $revivePrice;
        $updateCurrencies = array();
        $updateCurrencies[] = $hardCurrency;
        $output['updateCurrencies'] = $updateCurrencies;
    }
    echo json_encode($output);
}

function SelectFormation($formationName, $formationType)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    if (formationType == EFormationType::Stage)
    {
        $player->selectedFormation = $formationName;
    }
    else if (formationType == EFormationType::Arena)
    {
        $player->selectedArenaFormation = $formationName;
    }
    $player->update();
    $output['player'] = $player;
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
    $output['list'] = $list;
    echo json_encode($output);
}
?>