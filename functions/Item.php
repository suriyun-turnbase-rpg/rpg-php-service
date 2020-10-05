<?php
function LevelUpItem($itemId, $materials)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $playerItemDb = new PlayerItem();
    $item = $playerItemDb->findone(array(
        'playerId = ? AND id = ?',
        $playerId,
        $itemId
    ));
    
    if (!$item) {
        $output['error'] = 'ERROR_INVALID_PLAYER_ITEM_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
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
                $deletingItem = $playerItemDb->findone(array(
                    'id = ?',
                    $deleteItemId
                ));
                if ($deletingItem) {
                    $deletingItem->erase();
                }
            }
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            // Update achievement
            $itemData = $gameData['items'][$item->dataId];
            if ($itemData)
            {
                if ($itemData['type'] == "CharacterItem") {
                    QueryUpdateAchievement(UpdateCountLevelUpCharacter($player->id, GetAchievementListInternal($player->id)));
                }
                if ($itemData['type'] == "EquipmentItem") {
                    QueryUpdateAchievement(UpdateCountLevelUpEquipment($player->id, GetAchievementListInternal($player->id)));
                }
            }

            $output['updateItems'] = ItemCursorsToArray($updateItems);
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
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
        $requireCurrency = CalculateItemEvolvePrice($item);
        $updateCurrencies = array();
        $enoughMaterialsResult = HaveEnoughMaterials($playerId, $materials, GetItemEvolveMaterials($item));
        if ($requireCurrency > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if (!$enoughMaterialsResult['success']) {
            $output['error'] = 'ERROR_NOT_ENOUGH_ITEMS';
        } else {
            $updateItems = $enoughMaterialsResult['updateItems'];
            $deleteItemIds = $enoughMaterialsResult['deleteItemIds'];
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
                $deletingItem = $playerItemDb->findone(array(
                    'id = ?',
                    $deleteItemId
                ));
                if ($deletingItem) {
                    $deletingItem->erase();
                }
            }
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            // Update achievement
            $itemData = $gameData['items'][$item->dataId];
            if ($itemData)
            {
                if ($itemData['type'] == "CharacterItem") {
                    QueryUpdateAchievement(UpdateCountEvolveCharacter($player->id, GetAchievementListInternal($player->id)));
                }
                if ($itemData['type'] == "EquipmentItem") {
                    QueryUpdateAchievement(UpdateCountEvolveEquipment($player->id, GetAchievementListInternal($player->id)));
                }
            }

            $output['updateItems'] = ItemCursorsToArray($updateItems);
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
    
    $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
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
        $deletingItem = $playerItemDb->findone(array(
            'id = ?',
            $deleteItemId
        ));
        if ($deletingItem) {
            $deletingItem->erase();
        }
    }
    $softCurrency->update();
    $updateCurrencies[] = $softCurrency;
    $output['updateItems'] = ItemCursorsToArray($updateItems);
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

    $character = $playerItemDb->findone(array(
        'playerId = ? AND id = ?',
        $playerId,
        $characterId
    ));

    $equipment = $playerItemDb->findone(array(
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
            $output['updateItems'] = ItemCursorsToArray($updateItems);
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

    $unEquipItem = $playerItemDb->findone(array(
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
        $output['updateItems'] = ItemCursorsToArray($updateItems);
    }
    echo json_encode($output);
}

function CraftItem($itemCraftId, $materials)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;

    $playerItemDb = new PlayerItem();
    $itemCraft = $gameData['itemCrafts'][$itemCraftId];
    if (!$itemCraft) {
        $output['error'] = 'ERROR_INVALID_ITEM_CRAFT_FORMULA_DATA';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);

        $rewardItems = array();
        $createItems = array();
        $updateItems = array();
        $deleteItemIds = array();
        $updateCurrencies = array();
        $requirementType = $itemCraft['requirementType'];
        $resultItem = $itemCraft['resultItem'];
        $price = $itemCraft['price'];
        $enoughMaterialsResult = HaveEnoughMaterials($playerId, $materials, $itemCraft['materials']);
        if ($requirementType == ECraftRequirementType::SoftCurrency && $price > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if ($requirementType == ECraftRequirementType::HardCurrency && $price > $hardCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
        } else if (!$enoughMaterialsResult['success']) {
            $output['error'] = 'ERROR_NOT_ENOUGH_ITEMS';
        } else {
            // Query items
            $updateItems = $enoughMaterialsResult['updateItems'];
            $deleteItemIds = $enoughMaterialsResult['deleteItemIds'];
            $countUpdateItems = count($updateItems);
            for ($i = 0; $i < $countUpdateItems; ++$i) {
                $updateItem = $updateItems[$i];
                $updateItem->update();
            }
            $countDeleteItemIds = count($deleteItemIds);
            for ($i = 0; $i < $countDeleteItemIds; ++$i) {
                $deleteItemId = $deleteItemIds[$i];
                $deletingItem = $playerItemDb->findone(array(
                    'id = ?',
                    $deleteItemId
                ));
                if ($deletingItem) {
                    $deletingItem->erase();
                }
            }
            // Update currencies
            switch ($requirementType)
            {
                case ECraftRequirementType::SoftCurrency:
                    $softCurrency->amount -= $price;
                    break;
                case ECraftRequirementType::HardCurrency:
                    $hardCurrency->amount -= $price;
                    break;
            }
            // Update soft currency
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            // Update hard currency
            $hardCurrency->update();
            $updateCurrencies[] = $hardCurrency;
            // Add items
            $addItemsResult = AddItems($playerId, $resultItem['id'], $resultItem['amount']);
            if ($addItemsResult['success'])
            {
                $rewardItems[] = CreateEmptyItem(0, $playerId, $resultItem['id'], $resultItem['amount']);

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

            $output['rewardItems'] = ItemCursorsToArray($rewardItems);
            $output['createItems'] = ItemCursorsToArray($createItems);
            $output['updateItems'] =ItemCursorsToArray($updateItems);
            $output['deleteItemIds'] = $deleteItemIds;
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        }
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
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
        
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
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
        
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

function ConvertHardCurrency($requireHardCurrency)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
    $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
    
    if ($requireHardCurrency > $hardCurrency->amount) {
        $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
    } else {
        $updateCurrencies = array();
        $hardCurrency->amount -= $requireHardCurrency;
        $hardCurrency->update();
        $updateCurrencies[] = $hardCurrency;
        $receiveSoftCurrency = $gameData['hardToSoftCurrencyConversion'] * $requireHardCurrency;
        $softCurrency->amount += $receiveSoftCurrency;
        $softCurrency->update();
        $updateCurrencies[] = $softCurrency;
        $output['requireHardCurrency'] = $requireHardCurrency;
        $output['receiveSoftCurrency'] = $receiveSoftCurrency;
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    }
    echo json_encode($output);
}

function RefillStamina($staminaDataId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    
    $stamina = $gameData['staminas'][$staminaDataId];
    if (!$stamina) {
        $output['error'] = 'ERROR_INVALID_STAMINA_DATA';
    } else if (count($stamina['refillPrices']) == 0) {
        $output['error'] = 'ERROR_CANNOT_REFILL_STAMINA';
    } else {
        $playerStamina = GetStamina($playerId, $stamina['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
        $currentDate = mktime(0, 0, 0);
        $lastRefillDate = mktime(0, 0, 0, date('n', $playerStamina->lastRefillTime), date('j', $playerStamina->lastRefillTime), date('Y', $playerStamina->lastRefillTime));
        if ($currentDate > $lastRefillDate) {
            $playerStamina->refillCount = 0;
        }
        $indexOfPrice = $playerStamina->refillCount;
        if ($indexOfPrice >= count($stamina['refillPrices'])) {
            $indexOfPrice = count($stamina['refillPrices']) - 1;
        }
        $price = $stamina['refillPrices'][$indexOfPrice];
        if ($price > $hardCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
        }
        else
        {
            $hardCurrency->amount -= $price;
            $hardCurrency->update();
            $currentLevel = CalculatePlayerLevel($exp);
            $maxLevel = $gameData['playerMaxLevel'];
            $maxAmountTable = $stamina['maxAmountTable'];
            $refillAmount = CalculateIntAttribute($currentLevel, $maxLevel, $maxAmountTable['minValue'], $maxAmountTable['maxValue'], $maxAmountTable['growth']);
            $playerStamina->amount += $refillAmount;
            $playerStamina->recoveredTime = time();
            $playerStamina->lastRefillTime = time();
            $playerStamina->refillCount++;
            $playerStamina->update();
            $result['currency'] = $hardCurrency;
            $result['stamina'] = $playerStamina;
        }
    }
    echo json_encode($output);
}

function EarnAchievementReward($achievementId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $achievement = $gameData['achievements'][$achievementId];
    if (!$achievement) {
        $output['error'] = 'ERROR_INVALID_ACHIEVEMENT_DATA';
    } else {
        
        $playerAchievementDb = new PlayerAchievement();
        $playerAchievement = $playerAchievementDb->findone(array(
            'playerId = ? AND dataId = ?',
            $playerId,
            $achievementId,
        ));

        if (!$playerAchievement) {
            $output['error'] = 'ERROR_ACHIEVEMENT_UNDONE';
        } else if ($playerAchievement->earned) {
            $output['error'] = 'ERROR_ACHIEVEMENT_EARNED';
        } else if ($playerAchievement->progress < $achievement['targetAmount']) {
            $output['error'] = 'ERROR_ACHIEVEMENT_UNDONE';
        } else {
            $playerAchievement->earned = true;
            $playerAchievement->update();

            $updateCurrencies = array();
            $createItems = array();
            $updateItems = array();
            $rewardItems = array();
            $rewardPlayerExp = $achievement['rewardPlayerExp'];
            $rewardSoftCurrency = $achievement['rewardSoftCurrency'];
            $rewardHardCurrency = $achievement['rewardHardCurrency'];
            // Player exp
            $player->exp += $rewardPlayerExp;
            $player->update();
            // Soft currency
            $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
            $softCurrency->amount += $rewardSoftCurrency;
            $softCurrency->update();
            $updateCurrencies[] = $softCurrency;
            // Hard currency
            $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
            $hardCurrency->amount += $rewardHardCurrency;
            $hardCurrency->update();
            $updateCurrencies[] = $hardCurrency;
            // Items
            $countRewardItems = count($achievement['rewardItems']);
            for ($i = 0; $i < $countRewardItems; ++$i)
            {
                $rewardItem = $achievement['rewardItems'][$i];
                if (empty($rewardItem) || empty($rewardItem['id'])) {
                    continue;
                }
                
                $addItemsResult = AddItems($player->id, $rewardItem['id'], $rewardItem['amount']);
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
                        HelperUnlockItem($player->id, $createItem->dataId);
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

            $output['rewardPlayerExp'] = $rewardPlayerExp;
            $output['rewardSoftCurrency'] = $rewardSoftCurrency;
            $output['rewardHardCurrency'] = $rewardHardCurrency;
            $output['rewardItems'] = ItemCursorsToArray($rewardItems);
            $output['createItems'] = ItemCursorsToArray($createItems);
            $output['updateItems'] = ItemCursorsToArray($updateItems);
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
            $output['player'] = CursorToArray($player);
        }
    }
    echo json_encode($output);
}
?>