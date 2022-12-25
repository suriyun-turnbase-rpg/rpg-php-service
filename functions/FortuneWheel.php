<?php
function RandomFortuneWheelReward($fortuneWheel)
{
    $rewards = $fortuneWheel['rewards'];
    $generatedResult = array();
    $generatedWeight = array();
    $countRewards = count($rewards);
    for ($i = 0; $i < $countRewards; ++$i)
    {
        $reward = $rewards[$i];
        $id = '_' . $i;
        $generatedResult[$id] = $reward;
        $generatedResult[$id]['rewardIndex'] = $i;
        $generatedWeight[$id] = $reward['randomWeight'];
    }
    
    $takenId = WeightedRandom($generatedWeight, 0);
    if ($takenId) {
        return $generatedResult[$takenId];
    }
    return NULL;
}

function GetAvailableFortuneWheelList()
{
    $list = array();
    $gameData = \Base::instance()->get('GameData');
    $fortuneWheels = $gameData['fortuneWheels'];
    foreach ($fortuneWheels as $key => $value) {
        $list[] = $key;
    }
    $output = array('error' => '');
    $output['list'] = $list;
    echo json_encode($output);
}

function SpinFortuneWheel($fortuneWheelDataId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $fortuneWheel = $gameData['fortuneWheels'][$fortuneWheelDataId];
    if (!$fortuneWheel) {
        $output['error'] = 'ERROR_INVALID_FORTUNE_WHEEL_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['softCurrencyId']);
        $hardCurrency = GetCurrency($playerId, $gameData['hardCurrencyId']);
        
        $rewardIndex = 0;
        $rewardItems = array();
        $createItems = array();
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $requirementType = $fortuneWheel['requirementType'];
        $price = $fortuneWheel['price'];
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
            
            $reward = RandomFortuneWheelReward($fortuneWheel);
            if ($reward)
            {
                $rewardIndex = $reward['rewardIndex'];

                // Soft currency
                $rewardSoftCurrency = $reward['rewardSoftCurrency'];
                $softCurrency = GetCurrency($playerId, $gameData['softCurrencyId']);
                $softCurrency->amount += $rewardSoftCurrency;
                $softCurrency->update();
                $updateCurrencies[] = $softCurrency;

                // Hard currency
                $rewardHardCurrency = $reward['rewardHardCurrency'];
                $hardCurrency = GetCurrency($playerId, $gameData['hardCurrencyId']);
                $hardCurrency->amount += $rewardHardCurrency;
                $hardCurrency->update();
                $updateCurrencies[] = $hardCurrency;

                $wheelRewardItems = $reward['rewardItems'];
                $countRewardItems = count($wheelRewardItems);
                for ($i = 0; $i < $countRewardItems; ++$i)
                {
                    $rewardItem = $wheelRewardItems[$i];
                    $addItemsResult = AddItems($playerId, $rewardItem['id'], $rewardItem['amount']);
                    if ($addItemsResult['success'])
                    {
                        $rewards[] = CreateEmptyItem($i, $playerId, $rewardItem['id'], $rewardItem['amount']);

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
                }
            }
        }
        $output['rewardItems'] = ItemCursorsToArray($rewardItems);
        $output['createItems'] = ItemCursorsToArray($createItems);
        $output['updateItems'] = ItemCursorsToArray($updateItems);
        $output['deleteItemIds'] = $deleteItemIds;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        $output['rewardIndex'] = $rewardIndex;
    }
    echo json_encode($output);
}
?>