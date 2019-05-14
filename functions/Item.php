<?php
function LevelUpItem($itemId, $materials)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $playerItemDb = new PlayerItem();
    $item = $playerItemDb->load(array(
        'playerId = ? AND id = ?',
        $playerId,
        $itemId
    ));
    
    if (!$item) {
        $output['error'] = 'ERROR_INVALID_PLAYER_ITEM_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
        $levelUpPrice = CalculateItemLevelUpPrice($item);
        $requireCurrency = 0;
        $increasingExp = 0;
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $materialItems = array();
        foreach ($materials as $materialItemId => $amount) {
            $foundItem = $playerItemDb->load(array(
                'playerId = ? AND id = ?',
                $playerId,
                $materialItemId
            ));
            
            if (!$foundItem) {
                continue;
            }
    
            if (CanItemBeMaterial($foundItem)) {
                $materialItems[] = $foundItem;
            }
        }
        $countMaterialItems = count($materialItems);
        for ($i = 0; $i < $countMaterialItems; ++$i) {
            $materialItem = $materialItems[$i];
            $usingAmount = $materials[$materialItem->id];
            if ($usingAmount > $materialItem->amount) {
                $usingAmount = $materialItem->amount;
            }
            $requireCurrency += $levelUpPrice * $usingAmount;
            $increasingExp += CalculateItemRewardExp($materialItem) * $usingAmount;
            $materialItem->amount -= $usingAmount;
            if ($materialItem->amount > 0) {
                $updateItems[] = $materialItem;
            } else {
                $deleteItemIds[] = $materialItem->id;
            }
        }
        // Reduce currency amount
        if ($requireCurrency > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else {
            $softCurrency->amount -= $requireCurrency;
            $item->exp += $increasingExp;
            $updateItems[] = $item;
            $countUpdateItems = count($updateItems);
            for ($i = 0; $i < $countUpdateItems; ++$i) {
                $updateItem = $updateItems[$i];
                $updateItem->update();
            }
            $countDeleteItemIds = count($deleteItemIds);
            for ($i = 0; $i < $countDeleteItemIds; ++$i) {
                $deleteItemId = $deleteItemIds[$i];
                $deletingItem = $playerItemDb->load(array(
                    'id = ?',
                    $deleteItemId
                ));
                if ($deletingItem) {
                    $deletingItem->erase();
                }
            }
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            $output['updateItems'] = CursorsToArray($updateItems);
            $output['deleteItemIds'] = $deleteItemIds;
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        }
    }
    echo json_encode($output);
}

function EvolveItem($itemId, $materials)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $playerItemDb = new PlayerItem();
    $item = $playerItemDb->load(array(
        'playerId = ? AND id = ?',
        $playerId,
        $itemId
    ));
    
    if (!$item) {
        $output['error'] = 'ERROR_INVALID_PLAYER_ITEM_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
        $requireCurrency = CalculateItemEvolvePrice($item);
        $enoughMaterials = true;
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $materialItems = array();
        foreach ($materials as $materialItemId => $amount) {
            $foundItem = $playerItemDb->load(array(
                'playerId = ? AND id = ?',
                $playerId,
                $materialItemId
            ));
            
            if (!$foundItem) {
                continue;
            }
    
            if (CanItemBeMaterial($foundItem)) {
                $materialItems[] = $foundItem;
            }
        }
        $requiredMaterials = GetItemEvolveMaterials($item);   // This is Key-Value Pair for `playerItem.DataId`, `Required Amount`
        $countRequiredMaterials = count($requiredMaterials);
        for ($i = 0; $i < $countRequiredMaterials; ++$i) {
            $requiredMaterial = $requiredMaterials[$i];
            $dataId = $requiredMaterial['id'];
            $amount = $requiredMaterial['amount'];
            $countMaterialItems = count($materialItems);
            for ($j = 0; $j < $countMaterialItems; ++$j) {
                $materialItem = $materialItems[$j];
                if ($materialItem->dataId != $dataId) {
                    continue;
                }
                $usingAmount = $materials[$materialItem->id];
                if ($usingAmount > $materialItem->amount) {
                    $usingAmount = $materialItem->amount;
                }
                if ($usingAmount > $amount) {
                    $usingAmount = $amount;
                }
                $materialItem->amount -= $usingAmount;
                $amount -= $usingAmount;
                if ($materialItem->amount > 0) {
                    $updateItems[] = $materialItem;
                } else {
                    $deleteItemIds[] = $materialItem->id;
                }
                if ($amount == 0) {
                    break;
                }
            }
            if ($amount > 0) {
                $enoughMaterials = false;
                break;
            }
        }
        
        if ($requireCurrency > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if (!$enoughMaterials) {
            $output['error'] = 'ERROR_NOT_ENOUGH_ITEMS';
        }
        else
        {
            $softCurrency->amount -= $requireCurrency;
            $item = GetItemEvolve($item);
            $updateItems[] = $item;
            $countUpdateItems = count($updateItems);
            for ($i = 0; $i < $countUpdateItems; ++$i) {
                $updateItem = $updateItems[$i];
                $updateItem->update();
            }
            $countDeleteItemIds = count($deleteItemIds);
            for ($i = 0; $i < $countDeleteItemIds; ++$i) {
                $deleteItemId = $deleteItemIds[$i];
                $deletingItem = $playerItemDb->load(array(
                    'id = ?',
                    $deleteItemId
                ));
                if ($deletingItem) {
                    $deletingItem->erase();
                }
            }
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            $output['updateItems'] = CursorsToArray($updateItems);
            $output['deleteItemIds'] = $deleteItemIds;
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        }
    }
    echo json_encode($output);
}

function SellItems($items)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
    $returnCurrency = 0;
    $updateItems = array();
    $deleteItemIds = array();
    $updateCurrencies = array();
    $sellingItems = array();
    
    $playerItemDb = new PlayerItem();
    foreach ($items as $sellingItemId => $amount) {
        $foundItem = $playerItemDb->load(array(
            'playerId = ? AND id = ?',
            $playerId,
            $sellingItemId
        ));
        
        if (!$foundItem) {
            continue;
        }

        if (CanSellItem($foundItem)) {
            $sellingItems[] = $foundItem;
        }
    }
    $countSellingItems = count($sellingItems);
    for ($i = 0; $i < $countSellingItems; ++$i) {
        $sellingItem = $sellingItems[$i];
        $usingAmount = $items[$sellingItem->id];
        if ($usingAmount > $sellingItem->amount) {
            $usingAmount = $sellingItem->amount;
        }
        $returnCurrency += CalculateItemSellPrice($sellingItem) * $usingAmount;
        $sellingItem->amount -= $usingAmount;
        if ($sellingItem->amount > 0) {
            $updateItems[] = $sellingItem;
        } else {
            $deleteItemIds[] = $sellingItem->id;
        }
    }
    // Increase currency amount
    $softCurrency->amount += $returnCurrency;
    $countUpdateItems = count($updateItems);
    for ($i = 0; $i < $countUpdateItems; ++$i) {
        $updateItem = $updateItems[$i];
        $updateItem->update();
    }
    $countDeleteItemIds = count($deleteItemIds);
    for ($i = 0; $i < $countDeleteItemIds; ++$i) {
        $deleteItemId = $deleteItemIds[$i];
        $deletingItem = $playerItemDb->load(array(
            'id = ?',
            $deleteItemId
        ));
        if ($deletingItem) {
            $deletingItem->erase();
        }
    }
    $softCurrency->update();
    $updateCurrencies[] = $softCurrency;
    $output['updateItems'] = CursorsToArray($updateItems);
    $output['deleteItemIds'] = $deleteItemIds;
    $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    echo json_encode($output);
}

function EquipItem($characterId, $equipmentId, $equipPosition)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $playerItemDb = new PlayerItem();

    $character = $playerItemDb->load(array(
        'playerId = ? AND id = ?',
        $playerId,
        $characterId
    ));

    $equipment = $playerItemDb->load(array(
        'playerId = ? AND id = ?',
        $playerId,
        $equipmentId
    ));

    if (!$character || !$equipment) {
        $output['error'] = 'ERROR_INVALID_PLAYER_ITEM_DATA';
    } else {
        $equipmentData = $gameData['items'][$equipment->dataId];
        $equippablePositions = $equipmentData['equippablePositions'];
        if (!$equipmentData) {
            $output['error'] = 'ERROR_INVALID_ITEM_DATA';
        } else if ($equippablePositions && 
            count($equippablePositions) > 0 && 
            !in_array($equipPosition, $equippablePositions)) {
            $output['error'] = 'ERROR_INVALID_EQUIP_POSITION';
        } else {
            $updateItems = array();
            $unEquipItems = $playerItemDb->find(array(
                'equipItemId = ? AND equipPosition = ? AND playerId = ?',
                $characterId,
                $equipPosition,
                $playerId
            ));
            foreach ($unEquipItems as $unEquipItem) {
                $unEquipItem->equipItemId = '';
                $unEquipItem->equipPosition = '';
                $unEquipItem->update();
                $updateItems[] = $unEquipItem;
            }
            $equipment->equipItemId = $characterId;
            $equipment->equipPosition = $equipPosition;
            $equipItem = $playerItemDb->load(array(
                'id = ?',
                $equipment->id
            ));
            $equipment->update();
            $updateItems[] = $equipment;
            $output['updateItems'] = CursorsToArray($updateItems);
        }
    }
    echo json_encode($output);
}

function UnEquipItem($equipmentId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    $playerItemDb = new PlayerItem();

    $unEquipItem = $playerItemDb->load(array(
        'playerId = ? AND id = ?',
        $playerId,
        $equipmentId
    ));

    if (!$unEquipItem) {
        $output['error'] = 'ERROR_INVALID_PLAYER_ITEM_DATA';
    } else {
        $updateItems = array();
        $unEquipItem->equipItemId = '';
        $unEquipItem->equipPosition = '';
        $unEquipItem->update();
        $updateItems[] = $unEquipItem;
        $output['updateItems'] = CursorsToArray($updateItems);
    }
    echo json_encode($output);
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
        $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies']['HARD_CURRENCY']['id']);
        
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
                    $createItems = $addItemsResult['createItems'];
                    $updateItems = $addItemsResult['updateItems'];
                    $countCreateItems = count($createItems);
                    $countUpdateItems = count($updateItems);
                    for ($j = 0; $j < $countCreateItems; ++$j)
                    {
                        $createItem = $createItems[$j];
                        $createItem->save();
                        HelperUnlockItem($playerId, $createItem->dataId);
                        $createItems[] = $createItem;
                    }
                    for ($j = 0; j < $countUpdateItems; ++$j)
                    {
                        $updateItem = $updateItems[$j];
                        $updateItem->update();
                        $updateItems[] = $updateItem;
                    }
                }
                // End add item condition
            }
            // End reward items loop
        }
        $output['createItems'] = CursorsToArray($createItems);
        $output['updateItems'] =CursorsToArray($updateItems);
        $output['deleteItemIds'] = $deleteItemIds;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    }
    echo json_encode($output);
}
?>