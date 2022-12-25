<?php
function GetAvailableIapPackageList()
{
    $list = array();
    $gameData = \Base::instance()->get('GameData');
    $iapPackages = $gameData['iapPackages'];
    foreach ($iapPackages as $key => $value) {
        $list[] = $key;
    }
    $output = array('error' => '');
    $output['list'] = $list;
    echo json_encode($output);
}

function GetAvailableInGamePackageList()
{
    $list = array();
    $gameData = \Base::instance()->get('GameData');
    $inGamePackages = $gameData['inGamePackages'];
    foreach ($inGamePackages as $key => $value) {
        $list[] = $key;
    }
    $output = array('error' => '');
    $output['list'] = $list;
    echo json_encode($output);
}

function OpenInGamePackage($inGamePackageDataId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $inGamePackage = $gameData['inGamePackages'][$inGamePackageDataId];
    if (!$inGamePackage) {
        $output['error'] = 'ERROR_INVALID_IN_GAME_PACKAGE_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['softCurrencyId']);
        $hardCurrency = GetCurrency($playerId, $gameData['hardCurrencyId']);
        
        $rewardItems = array();
        $createItems = array();
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $requirementType = $inGamePackage['requirementType'];
        $price = $inGamePackage['price'];
        $rewardSoftCurrency = $inGamePackage['rewardSoftCurrency'];
        $rewardHardCurrency = $inGamePackage['rewardHardCurrency'];
        if ($requirementType == EInGamePackageRequirementType::SoftCurrency && $price > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if ($requirementType == EInGamePackageRequirementType::HardCurrency && $price > $hardCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
        } else {
            switch ($requirementType)
            {
                case EInGamePackageRequirementType::SoftCurrency:
                    $softCurrency->amount -= $price;
                    break;
                case EInGamePackageRequirementType::HardCurrency:
                    $hardCurrency->amount -= $price;
                    break;
            }
            // Increase soft currency
            $softCurrency->amount += $rewardSoftCurrency;
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            // Increase hard currency
            $hardCurrency->amount += $rewardHardCurrency;
            $hardCurrency->update();
            $updateCurrencies[] = $hardCurrency;
                
            $countRewardItems = count($inGamePackage['rewardItems']);
            for ($i = 0; $i < $countRewardItems; ++$i)
            {
                $rewardItem = $inGamePackage['rewardItems'][$i];
                if (empty($rewardItem) || empty($rewardItem['id'])) {
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
            }
            $output['rewardItems'] = ItemCursorsToArray($rewardItems);
            $output['createItems'] = ItemCursorsToArray($createItems);
            $output['updateItems'] =ItemCursorsToArray($updateItems);
            $output['deleteItemIds'] = $deleteItemIds;
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
            $output['rewardSoftCurrency'] = $rewardSoftCurrency;
            $output['rewardHardCurrency'] = $rewardHardCurrency;
        }
    }
    echo json_encode($output);
}
?>