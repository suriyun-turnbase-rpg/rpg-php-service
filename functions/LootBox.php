<?php
function RandomLootBoxReward($lootBox)
{
    $lootboxRewards = $lootBox['lootboxRewards'];
    $generatedResult = array();
    $generatedWeight = array();
    $countLootboxRewards = count($lootboxRewards);
    for ($i = 0; $i < $countLootboxRewards; ++$i)
    {
        $lootboxReward = $lootboxRewards[$i];
        $id = '_' . $i;
        $generatedResult[$id] = $lootboxReward;
        $generatedWeight[$id] = $lootboxReward['randomWeight'];
    }
    
    $takenId = WeightedRandom($generatedWeight, 0);
    if ($takenId) {
        return $generatedResult[$takenId];
    }
    return NULL;
}

function GetAvailableLootBoxList()
{
    $list = array();
    $gameData = \Base::instance()->get('GameData');
    $lootBoxes = $gameData['lootBoxes'];
    foreach ($lootBoxes as $key => $value) {
        $list[] = $key;
    }
    $output = array('error' => '');
    $output['list'] = $list;
    echo json_encode($output);
}

function OpenLootBox($lootBoxDataId, $packIndex)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $lootBox = $gameData['lootBoxes'][$lootBoxDataId];
    if (!$lootBox) {
        $output['error'] = 'ERROR_INVALID_LOOT_BOX_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['softCurrencyId']);
        $hardCurrency = GetCurrency($playerId, $gameData['hardCurrencyId']);
        
        $rewardItems = array();
        $createItems = array();
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $requirementType = $lootBox['requirementType'];
        if ($packIndex > count($lootBox['lootboxPacks']) - 1) {
            $packIndex = 0;
        }
        $pack = $lootBox['lootboxPacks'][$packIndex];
        $price = $pack['price'];
        $openAmount = $pack['openAmount'];
        if ($requirementType == ELootboxRequirementType::SoftCurrency && $price > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if ($requirementType == ELootboxRequirementType::HardCurrency && $price > $hardCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
        } else {
            switch ($requirementType)
            {
                case ELootboxRequirementType::SoftCurrency:
                    $softCurrency->amount -= $price;
                    $softCurrency->update();
                    $updateCurrencies[] = $softCurrency;
                    break;
                case ELootboxRequirementType::HardCurrency:
                    $hardCurrency->amount -= $price;
                    $hardCurrency->update();
                    $updateCurrencies[] = $hardCurrency;
                    break;
            }
            
            for ($i = 0; $i < $openAmount; ++$i)
            {
                $rewardItem = RandomLootBoxReward($lootBox);
                if (!$rewardItem) {
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
        }
        $output['rewardItems'] = ItemCursorsToArray($rewardItems);
        $output['createItems'] = ItemCursorsToArray($createItems);
        $output['updateItems'] = ItemCursorsToArray($updateItems);
        $output['deleteItemIds'] = $deleteItemIds;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    }
    echo json_encode($output);
}
?>