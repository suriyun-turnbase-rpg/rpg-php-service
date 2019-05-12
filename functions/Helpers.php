<?php
function GetPlayer()
{
    $player = \Base::instance()->get('PLAYER');
    if (!$player)
    {
        // Get Player by Id and LoginToken from header
        // Then get player from database, finally set to f3 data
        $playerDb = new Player();
        $player = $playerDb->load(array(
            'playerId = ? AND loginToken = ?',
            $_SERVER['playerId'],
            $_SERVER['loginToken'],
        ));
        \Base::instance()->set('PLAYER', $player);
    }
    return $player;
}

function WeightedRandom($weights, $noResultWeight)
{
    // Usage example: WeightedRandom({'a':0.5,'b':0.3,'c':0.2}); //Have chance to receives a = 50%, b = 30%, c = 20%
    
    if (!$noResultWeight) {
        $noResultWeight = 0;
    }
        
    $keys = array();
    $sum = 0;
    foreach ($weights as $key => $weight) {
        $sum += $weight;
        $keys[] = $key;
    }
    
    if (count($keys) == 0) {
        return NULL;
    }

    // TODO: This may invalid, will check later
    $roll = rand(0, $sum + $noResultWeight);
    $selected = $keys[count($keys) - 1];
    foreach ($weights as $key => $weight) {
        if ($roll < $weight)
        {
            $selected = $key;
            break;
        }
        $roll -= $weight;
    }
    
    return $selected;
}

function RandomLootBoxReward($lootBox)
{
    $lootboxRewards = $lootBox['lootboxRewards'];
    $generatedResult = array();
    $generatedWeight = array();
    $countLootboxRewards = count($lootboxRewards);
    for ($i = 0; $i < $countLootboxRewards; ++$i)
    {
        $lootboxReward = $lootboxRewards[$i];
        $id = '_' + $i;
        $generatedResult[$id] = $lootboxReward;
        $generatedWeight[$id] = $lootboxReward.randomWeight;
    }
    
    $takenId = WeightedRandom($generatedWeight, 0);
    if ($takenId) {
        return $generatedResult[$takenId];
    }
    return NULL;
}

function CalculateIntAttribute($currentLevel, $maxLevel, $minValue, $maxValue, $growth)
{
    if ($currentLevel <= 0)
        $currentLevel = 1;
    if ($maxLevel <= 0)
        $maxLevel = 1;
    if ($currentLevel == 1)
        return $minValue;
    if ($currentLevel == $maxLevel)
        return $maxValue;
    return $minValue + round(($maxValue - $minValue) * pow(($currentLevel - 1) / ($maxLevel - 1), $growth));
}

function CalculateFloatAttribute($currentLevel, $maxLevel, $minValue, $maxValue, $growth)
{
    if ($currentLevel <= 0)
        $currentLevel = 1;
    if ($maxLevel <= 0)
        $maxLevel = 1;
    if ($currentLevel == 1)
        return $minValue;
    if ($currentLevel == $maxLevel)
        return $maxValue;
    return $minValue + (($maxValue - $minValue) * pow(($currentLevel - 1) / ($maxLevel - 1), $growth));
}

function CalculatePlayerLevel($exp)
{
    $gameData = \Base::instance()->get('GameData');
    $maxLevel = $gameData['playerMaxLevel'];
    $expTable = $gameData['playerExpTable'];
    return CalculateLevel($exp, $maxLevel, $expTable['minValue'], $expTable['maxValue'], $expTable['growth']);
}

function CalculateItemLevel($exp, $itemTier)
{
    $maxLevel = $itemTier['maxLevel'];
    $expTable = $itemTier['expTable'];
    return CalculateLevel($exp, $maxLevel, $expTable['minValue'], $expTable['maxValue'], $expTable['growth']);
}

function CalculateLevel($exp, $maxLevel, $minValue, $maxValue, $growth)
{
    $remainExp = $exp;
    $level = 1;
    for ($level = 1; $level < $maxLevel; ++$level)
    {
        $nextExp = CalculateIntAttribute($level, $maxLevel, $minValue, $maxValue, $growth);
        if ($remainExp - $nextExp < 0) {
            break;
        }
        $remainExp -= $nextExp;
    }
    return $level;
}

function CanItemBeMaterial($item)
{
    return !empty($item->equipItemId);
}

function CanSellItem($item)
{
    return !empty($item->equipItemId);
}

function CalculateItemLevelUpPrice($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return 0;
    }
        
    if ($itemData['useFixLevelUpPrice']) {
        return $itemData['fixLevelUpPrice'];
    }
    
    $itemTier = $itemData['itemTier'];
    if (!$itemTier) {
        return 0;
    }
        
    $levelUpPriceTable = $itemTier['levelUpPriceTable'];
    $currentLevel = CalculateItemLevel($item->exp, $itemTier);
    return CalculateIntAttribute($currentLevel, $itemTier['maxLevel'], $levelUpPriceTable['minValue'], $levelUpPriceTable['maxValue'], $levelUpPriceTable['growth']);
}

function CalculateItemEvolvePrice($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return 0;
    }
    
    $itemTier = $itemData['itemTier'];
    if (!$itemTier) {
        return 0;
    }
        
    return $itemTier['evolvePrice'];
}

function CalculateItemRewardExp($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return 0;
    }
        
    if ($itemData['useFixRewardExp']) {
        return $itemData['fixRewardExp'];
    }
    
    $itemTier = $itemData['itemTier'];
    if (!$itemTier) {
        return 0;
    }
        
    $rewardExpTable = $itemTier['rewardExpTable'];
    $currentLevel = CalculateItemLevel($item->exp, $itemTier);
    return CalculateIntAttribute($currentLevel, $itemTier['maxLevel'], $rewardExpTable['minValue'], $rewardExpTable['maxValue'], $rewardExpTable['growth']);
}

function CalculateItemSellPrice($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return 0;
    }
        
    if ($itemData['useFixSellPrice']) {
        return $itemData['fixSellPrice'];
    }
    
    $itemTier = $itemData['itemTier'];
    if (!$itemTier) {
        return 0;
    }
        
    $sellPriceTable = $itemTier['sellPriceTable'];
    $currentLevel = CalculateItemLevel($item->exp, $itemTier);
    return CalculateIntAttribute($currentLevel, $itemTier['maxLevel'], $sellPriceTable['minValue'], $sellPriceTable['maxValue'], $sellPriceTable['growth']);
}

function GetItemEvolveMaterials($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return [];
    }
        
    $evolveInfo = $itemData['evolveInfo'];
    if (!$evolveInfo) {
        return [];
    }
    
    return $evolveInfo['requiredMaterials'];
}

function GetItemEvolve($item)
{
    $gameData = \Base::instance()->get('GameData');
    $itemData = $gameData['items'][$item->dataId];
    if (!$itemData) {
        return $item;
    }
    
    $evolveInfo = $itemData['evolveInfo'];
    if (!$evolveInfo) {
        return $item;
    }
    
    $item->dataId = $evolveInfo['evolveItem'];
    if ($gameData['resetItemLevelAfterEvolve'])
        $item->exp = 0;
    return $item;
}
    
function SetNewPlayerData($player)
{
    $gameData = \Base::instance()->get('GameData');
    $firstFormation = $gameData['formations'][0];
    $playerId = $player->id;
    $player->exp = 0;
    $player->selectedFormation = $firstFormation;
    
    $startItems = $gameData['startItems'];
    $countStartItems = count($startItems);
    for ($i = 0; $i < $countStartItems; ++$i) {
        $startItem = $startItems[$i];
        $addItemsResult = AddItems($playerId, $startItem['id'], $startItem['amount']);
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
            }
            for ($j = 0; $j < $countUpdateItems; ++$j)
            {
                $updateItem = $updateItems[$j];
                $updateItem->update();
            }
        }
    }
    
    $startCharacters = $gameData['startCharacters'];
    $countStartCharacters = count($startCharacters);
    for ($i = 0; $i < $countStartCharacters; ++$i)
    {
        $startCharacter = $startCharacters[$i];
        $addItemsResult = AddItems($playerId, $startCharacter, 1);
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
                HelperSetFormation($playerId, $createItem->id, $firstFormation, $i);
            }
            for ($j = 0; $j < $countUpdateItems; ++$j)
            {
                $updateItem = $updateItems[$j];
                $updateItem->update();
            }
        }
    }
}

function InsertNewPlayer($type, $username, $password)
{
    $player = new Player();
    $player->save();
    $player = SetNewPlayerData($player);
    $player->update();
    $playerAuth = new PlayerAuth();
    $playerAuth->playerId = $player->id;
    $playerAuth->type = $type;
    $playerAuth->username = $username;
    $playerAuth->password = $password;
    UpdatePlayerStamina($player);
    return $player;
}

function DecreasePlayerStamina($playerId, $staminaType, $decreaseAmount)
{
    $gameData = \Base::instance()->get('GameData');
    $staminaTable = $gameData['staminas'][$staminaType];
    if (!$staminaTable) {
        return;
    }

    $playerDb = new Player();
    $player = $playerDb->load(array(
        'playerId = ?',
        $playerId
    ));
    $exp = $player->exp;
    $stamina = GetStamina($playerId, $staminaTable['id']);
    $currentLevel = CalculatePlayerLevel($exp);
    $maxLevel = $gameData['playerMaxLevel'];
    $maxAmountTable = $staminaTable['maxAmountTable'];
    $maxStamina = CalculateIntAttribute($currentLevel, $maxLevel, $maxAmountTable['minValue'], $maxAmountTable['maxValue'], $maxAmountTable['growth']);
    if ($stamina->amount >= $decreaseAmount) {
        if ($stamina->amount >= $maxStamina && $stamina->amount - $decreaseAmount < $maxStamina) {
            $stamina->recoveredTime = time();
        }
        $stamina->amount -= $decreaseAmount;
        $stamina->update();
        UpdatePlayerStamina($playerId, $staminaType);
        return true;
    }
    return false;
}

function UpdatePlayerStamina($playerId, $staminaType)
{
    $gameData = \Base::instance()->get('GameData');
    $staminaTable = $gameData['staminas'][$staminaType];
    if (!$staminaTable) {
        return;
    }
    
    $playerDb = new Player();
    $player = $playerDb->load(array(
        'playerId = ?',
        $playerId
    ));
    $exp = $player->exp;
    $stamina = GetStamina($playerId, $staminaTable['id']);
    $currentLevel = CalculatePlayerLevel($exp);
    $maxLevel = $gameData['playerMaxLevel'];
    $maxAmountTable = $staminaTable['maxAmountTable'];
    $maxStamina = CalculateIntAttribute($currentLevel, $maxLevel, $maxAmountTable['minValue'], $maxAmountTable['maxValue'], $maxAmountTable['growth']);
    if ($stamina->amount < $maxStamina)
    {
        $currentTimeInMillisecond = time();
        $diffTimeInMillisecond = $currentTimeInMillisecond - $stamina->recoveredTime;
        $devideAmount = 1;
        switch ($staminaTable['recoverUnit'])
        {
            case EStaminaUnit::Days:
                $devideAmount = 1000 * 60 * 60 * 24;
                break;
            case EStaminaUnit::Hours:
                $devideAmount = 1000 * 60 * 60;
                break;
            case EStaminaUnit::Minutes:
                $devideAmount = 1000 * 60;
                break;
            case EStaminaUnit::Seconds:
                $devideAmount = 1000;
                break;
        }
        $recoveryAmount = floor(($diffTimeInMillisecond / $devideAmount) / $staminaTable['recoverDuration']);
        if ($recoveryAmount > 0)
        {
            $stamina->amount += $recoveryAmount;
            if ($stamina->amount > $maxStamina)
                $stamina->amount = $maxStamina;
            $stamina->recoveredTime = $currentTimeInMillisecond;
            $stamina->update();
        }
    }
}

function UpdateAllPlayerStamina($playerId)
{
    UpdatePlayerStamina($playerId, 'STAGE');
    UpdatePlayerStamina($playerId, 'ARENA');
}

function GetCurrency($playerId, $dataId)
{
    $playerCurrencyDb = new PlayerCurrency();
    $playerCurrency = $playerCurrencyDb->load(array(
        'playerId = ? AND dataId = ?',
        $playerId,
        $dataId
    ));
    if (!$playerCurrency) {
        $playerCurrency = new PlayerCurrency();
        $playerCurrency->playerId = $playerId;
        $playerCurrency->dataId = $dataId;
        $playerCurrency->save();
    }
    return $playerCurrency;
}

function GetStamina($playerId, $dataId)
{
    $playerStaminaDb = new PlayerStamina();
    $playerStamina = $playerStaminaDb->load(array(
        'playerId = ? AND dataId = ?',
        $playerId,
        $dataId
    ));
    if (!$playerStamina) {
        $playerStamina = new PlayerStamina();
        $playerStamina->playerId = $playerId;
        $playerStamina->dataId = $dataId;
        $playerStamina->amount = 0;
        $playerStamina->save();
    }
    return $playerStamina;
}

function AddItems($playerId, $dataId, $amount)
{
    $gameData = \Base::instance()->get('GameData');
    $item = $gameData['items'][$dataId];
    if (!$item) {
        return array('success' => false);
    }
        
    $maxStack = $item['maxStack'];
    $playerItemDb = new PlayerItem();
    $playerItems = $playerItemDb->find(array(
        'playerId = ? AND dataId = ? AND amount < ?',
        $playerId,
        $dataId,
        $amount
    ));
    // Update amount
    $createItems = array();
    $updateItems = array();
    foreach ($playerItems as $playerItem) {
        $sumAmount = $playerItem->amount + $amount;
        if ($sumAmount > $maxStack) {
            $playerItem->amount = $maxStack;
            $amount = $sumAmount - $maxStack;
        } else {
            $playerItem->amount += $amount;
            $amount = 0;
        }
        $updateItems[] = $playerItem;

        if ($amount == 0) {
            break;
        }
    }
    while ($amount > 0)
    {
        $newEntry = new PlayerItem();
        $newEntry->playerId = $playerId;
        $newEntry->dataId = $dataId;
        if ($amount > $maxStack) {
            $newEntry->amount = $maxStack;
            $amount -= $maxStack;
        } else {
            $newEntry->amount = $amount;
            $amount = 0;
        }
        $createItems[] = $newEntry;
    }
    return array('success' => true, 'createItems' => $createItems, 'updateItems' => $updateItems);
}

function HelperSetFormation($playerId, $characterId, $formationName, $position)
{
    $oldFormation = NULL;
    if (!empty($characterId))
    {
        $oldFormation = new PlayerFormation();
        $oldFormation = $oldFormation->load(array(
            'playerId = ? AND itemId = ? AND dataId = ?',
            $playerId,
            $characterId,
            $formationName
        ));
        
        if ($oldFormation)
        {
            $oldFormation->itemId = '';
            $oldFormation->update();
        }
    }

    $formation = new PlayerFormation();
    $formation = $formation->load(array(
        'playerId = ? AND dataId = ? AND position = ?',
        $playerId,
        $formationName,
        $position
    ));

    if (!$formation) {
        $formation = new PlayerFormation();
        $formation->playerId = $playerId;
        $formation->dataId = $formationName;
        $formation->position = $position;
        $formation->itemId = $characterId;
        $formation->save();
    } else {
        if ($oldFormation) {
            $oldFormation->itemId = $formation->itemId;
            $oldFormation->update();
        }
        $formation->itemId = $characterId;
        $formation->update();
    }
}

function HelperUnlockItem($playerId, $dataId)
{
    $playerUnlockItemDb = new PlayerUnlockItem();
    $playerUnlockItem = $playerUnlockItemDb->load(array(
        'playerId = ? AND dataId = ?',
        $playerId,
        $dataId
    ));

    if (!$playerUnlockItem)
    {
        $playerUnlockItem = new PlayerUnlockItem();
        $playerUnlockItem->playerId = $playerId;
        $playerUnlockItem->dataId = $dataId;
        $playerUnlockItem->amount = 1;
        $playerUnlockItem->save();
    } else {
        $playerUnlockItem->amount++;
        $playerUnlockItem->update();
    }
    return $playerUnlockItem;
}

function HelperClearStage($playerId, $dataId, $rating)
{
    $playerClearStageDb = new PlayerClearStage();
    $playerClearStage = $playerClearStageDb->load(array(
        'playerId = ? AND dataId = ?',
        $playerId,
        $dataId
    ));
    
    if (!$playerClearStage) {
        $playerClearStage = new PlayerClearStage();
        $playerClearStage->playerId = $playerId;
        $playerClearStage->dataId = $dataId;
        $playerClearStage->bestRating = $rating;
        $playerClearStage->save();
    } else {
        // If end stage with more rating, replace old rating
        if ($playerClearStage->bestRating < $rating) {
            $playerClearStage->bestRating = $rating;
            $playerClearStage->update();
        }
    }
    return $playerClearStage;
}

function GetFormationCharacterIds($playerId, $playerSelectedFormation)
{
    $characterIds = array();
    $playerFormationDb = new PlayerFormation();
    $formations = $playerFormationDb->find(array(
        'playerId = ?',
        $playerId
    ));
    foreach ($formations as $formation) {
        if ($formation->dataId == $playerSelectedFormation && !empty($formation->itemId)) {
            $characterIds[] = $formation->itemId;
        }
    }
    return $characterIds;
}

function GetFormationCharacter($playerId, $playerSelectedFormation)
{
    $characters = array();
    $characterIds = GetFormationCharacterIds($playerId, $playerSelectedFormation);
    $count = count($characterIds);
    $playerItemDb = new PlayerItem();
    for ($i = 0; $i < $count; ++$i)
    {
        $characterId = $characterIds[$i];
        $characterData = $playerItemDb->load(array(
            'id => ?',
            $characterId
        ));
        // Add data to list if existed
        if ($characterData) {
            $characters[] = $characterData;
        }
    }
    return $characters;
}

function GetLeaderCharacter($playerId, $playerSelectedFormation)
{
    $characterData = NULL;
    $playerFormationDb = new PlayerFormation();
    $formations = $playerFormationDb->find(array(
        'playerId = ?',
        $playerId
    ));
    $playerItemDb = new PlayerItem();
    foreach ($formations as $formation) {
        if ($formation->dataId == $playerSelectedFormation && !empty($formation->itemId))
        {
            $currentCharacterData = $playerItemDb->load(array(
                'id = ?',
                $formation->itemId
            ));
            if ($currentCharacterData)
            {
                if (!$characterData) {
                    // Set first found character, will return it when leader not found
                    $characterData = $currentCharacterData;
                }
                if ($formation->isLeader) {
                    return $currentCharacterData;
                }
            }
        }
    }
    return $characterData;
}

function CalculateArenaRankLevel($arenaScore)
{
    $gameData = \Base::instance()->get('GameData');
    $level = 0;
    $count = count($gameData['arenaRanks']);
    for ($i = 0; $i < $count; ++$i) {
        $arenaRank = $gameData['arenaRanks'][$i];
        if ($arenaScore < $arenaRank['scoreToRankUp']) {
            break;
        }
        $level++;
    }
    return level;
}

function GetArenaRank($arenaScore)
{
    $gameData = \Base::instance()->get('GameData');
    $level = CalculateArenaRankLevel(arenaScore);
    if ($level >= count($gameData['arenaRanks'])) {
        $level = count($gameData['arenaRanks']) - 1;
    }
    return $level >= 0 ? $gameData['arenaRanks']['level'] : NULL;
}

function GetSocialPlayer($playerId, $targetPlayerId)
{
    $playerDb = new Player();
    $player = $playerDb->load(array(
        'id = ?',
        $targetPlayerId
    ));
    $playerFriendDb = new PlayerFriend();
    if ($player) {
        $isFriend = $playerFriendDb->count(array(
            'playerId = ? AND targetPlayerId = ?',
            $playerId,
            $targetPlayerId
        )) > 0;
        // Show leader character
        $character = GetLeaderCharacter($targetPlayerId, $player->selectedFormation);
        if (!empty($player->profileName) && $character) {
            return array(
                'id' => $targetPlayerId,
                'profileName' => $player->profileName,
                'exp' => $player->exp,
                'mainCharacter' => $character->dataId,
                'mainCharacterExp' => $character->exp,
                'isFriend' => $isFriend
            );
        }
    }
    return false;
}
?>