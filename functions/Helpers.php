<?php
function EncodeJwt($payload)
{
    // JWT will be used as access token, no expiration date, regenerate everytime when player login.
    return \Firebase\JWT\JWT::encode($payload, \Base::instance()->get('jwt_secret'));
}

function DecodeJwt($token)
{
    if (empty($token))
        return array();
    return (array)\Firebase\JWT\JWT::decode($token, \Base::instance()->get('jwt_secret'), array('HS256'));
}

function GetAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function GetBearerToken()
{
    $headers = GetAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        $headersData = explode(" ", $headers);
        return $headersData[1];
    } else if (!empty($_GET["logintoken"])) {
        return $_GET["logintoken"];
    }
    
    return null;
}

function CursorToArray($cursor)
{
    $arr = array();
    $fields = $cursor->fields();
    foreach ($fields as $field)
    {
        if ($field == 'createdAt' || $field == 'updatedAt') {
            $arr[$field] = strtotime($cursor->get($field));
        } else {
            $arr[$field] = $cursor->get($field);
        }
    }
    return $arr;
}

function CursorsToArray($cursors)
{
    $arr = array();
    if (!empty($cursors)) {
        foreach ($cursors as $cursor)
        {
            $arr[] = CursorToArray($cursor);
        }
    }
    return $arr;
}

function ItemCursorToArray($cursor)
{
    $arr = CursorToArray($cursor);
    if (!empty($arr['randomedAttributes']))
        $arr['randomedAttributes'] = json_decode($arr['randomedAttributes']);
    return $arr;
}

function ItemCursorsToArray($cursors)
{
    $arr = array();
    if (!empty($cursors)) {
        foreach ($cursors as $cursor)
        {
            $arr[] = ItemCursorToArray($cursor);
        }
    }
    return $arr;
}

function GetPlayer()
{
    $player = \Base::instance()->get('PLAYER');
    if (!$player)
    {
        // Get Player by Id and LoginToken from header
        // Then get player from database, finally set to f3 data
        $loginToken = GetBearerToken();
        if (!empty($loginToken))
        {
            $decodedData = DecodeJwt($loginToken);
            $playerDb = new Player();
            $player = $playerDb->load(array(
                'id = ? AND loginToken = ?',
                $decodedData['id'],
                $loginToken,
            ));
        }
        if (!$player) {
            exit('{"error":"ERROR_INVALID_LOGIN_TOKEN","loginToken":"'.$loginToken.'"}');
        } else {
            \Base::instance()->set('PLAYER', $player);
        }
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
    return empty($item->equipItemId);
}

function CanSellItem($item)
{
    return empty($item->equipItemId);
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
    $gameFormations = $gameData['formations'];
    $firstFormation = $gameFormations[0];
    $lastFormation = $gameFormations[count($gameFormations) - 1];
    $playerId = $player->id;
    
    $startItems = $gameData['startItems'];
    $countStartItems = count($startItems);
    for ($i = 0; $i < $countStartItems; ++$i) {
        $startItem = $startItems[$i];
        $addItemsResult = AddItems($playerId, $startItem['id'], $startItem['amount']);
        if ($addItemsResult['success'])
        {
            $resultCreateItems = $addItemsResult['createItems'];
            $resultUpdateItems = $addItemsResult['updateItems'];
            $countCreateItems = count($resultCreateItems);
            $countUpdateItems = count($resultUpdateItems);
            for ($j = 0; $j < $countCreateItems; ++$j)
            {
                $createItem = $resultCreateItems[$j];
                $createItem->save();
                HelperUnlockItem($playerId, $createItem->dataId);
            }
            for ($j = 0; $j < $countUpdateItems; ++$j)
            {
                $updateItem = $resultUpdateItems[$j];
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
            $resultCreateItems = $addItemsResult['createItems'];
            $resultUpdateItems = $addItemsResult['updateItems'];
            $countCreateItems = count($resultCreateItems);
            $countUpdateItems = count($resultUpdateItems);
            for ($j = 0; $j < $countCreateItems; ++$j)
            {
                $createItem = $resultCreateItems[$j];
                $createItem->save();
                HelperUnlockItem($playerId, $createItem->dataId);
                HelperSetFormation($playerId, $createItem->id, $firstFormation, $i);
                HelperSetFormation($playerId, $createItem->id, $lastFormation, $i);
            }
            for ($j = 0; $j < $countUpdateItems; ++$j)
            {
                $updateItem = $resultUpdateItems[$j];
                $updateItem->update();
            }
        }
    }
    // Currencies
    $currencies = $gameData['currencies'];
    foreach ($currencies as $key => $value) {
        $data = GetCurrency($playerId, $key);
        $data->amount = $value['startAmount'];
        $ata->update();
    }
    return $player;
}

function InsertNewPlayer($type, $username, $password)
{
    $gameData = \Base::instance()->get('GameData');
    $gameFormations = $gameData['formations'];
    $firstFormation = $gameFormations[0];
    $lastFormation = $gameFormations[count($gameFormations) - 1];
    $player = new Player();
    $player->exp = 0;
    $player->selectedFormation = $firstFormation;
    $player->selectedArenaFormation = $lastFormation;
    $player->save();
    $player = SetNewPlayerData($player);
    $player->update();
    $playerAuth = new PlayerAuth();
    $playerAuth->playerId = $player->id;
    $playerAuth->type = $type;
    $playerAuth->username = $username;
    $playerAuth->password = $password;
    $playerAuth->save();
    UpdateAllPlayerStamina($player->id);
    return $player;
}

function UpdatePlayerLoginToken($player)
{
    $payload = array(
        'id' => $player->id,
        'profileName' => $player->profileName
    );
    $player->loginToken = EncodeJwt($payload);
    $player->update();
    return $player;
}

function IsPlayerWithUsernameFound($type, $username)
{
    $playerAuthDb = new PlayerAuth();
    return $playerAuthDb->count(array(
        'type = ? AND username = ?',
        $type,
        $username
    )) > 0;
}

function DecreasePlayerStamina($playerId, $staminaType, $decreaseAmount)
{
    $gameData = \Base::instance()->get('GameData');
    $staminaTable = $gameData['staminas'][$staminaType];
    if (!$staminaTable) {
        return;
    }

    $playerDb = new Player();
    $player = $playerDb->findone(array(
        'id = ?',
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
    $player = $playerDb->findone(array(
        'id = ?',
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
        $currentTimeInSeconds = time();
        $diffTimeInSeconds = $currentTimeInSeconds - $stamina->recoveredTime;
        $devideAmount = 1;
        switch ($staminaTable['recoverUnit'])
        {
            case EStaminaUnit::Days:
                $devideAmount = 60 * 60 * 24;
                break;
            case EStaminaUnit::Hours:
                $devideAmount = 60 * 60;
                break;
            case EStaminaUnit::Minutes:
                $devideAmount = 60;
                break;
            case EStaminaUnit::Seconds:
                $devideAmount = 1;
                break;
        }
        $recoveryAmount = floor(($diffTimeInSeconds / $devideAmount) / $staminaTable['recoverDuration']);
        if ($recoveryAmount > 0)
        {
            if ($stamina->amount < $maxStamina)
            {
                $stamina->amount += $recoveryAmount;
                if ($stamina->amount > $maxStamina) {
                    $stamina->amount = $maxStamina;
                }
                $stamina->recoveredTime = $currentTimeInSeconds;
                $stamina->update();
            }
        }
    }
}

function UpdateAllPlayerStamina($playerId)
{
    $gameData = \Base::instance()->get('GameData');
    $staminas = $gameData['staminas'];
    foreach ($staminas as $key => $value) {
        UpdatePlayerStamina($playerId, $key);
    }
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

function GetItemRandomAttributes($dataId)
{
    $gameData = \Base::instance()->get('GameData');
    
    $item = $gameData['items'][$dataId];
    if (empty($item)) {
        return array();
    }
    
    $randomAttributes = $item['randomAttributes'];
    if (empty($randomAttributes)) {
        return array();
    }

    $minType = 0;
    if (!empty($randomAttributes['minType'])) {
        $minType = $randomAttributes['minType'];
    }
    $maxType = 0;
    if (!empty($randomAttributes['maxType'])) {
        $maxType = $randomAttributes['maxType'];
    }
    $minHp = 0;
    if (!empty($randomAttributes['minHp'])) {
        $minHp = $randomAttributes['minHp'];
    }
    $maxHp = 0;
    if (!empty($randomAttributes['maxHp'])) {
        $maxHp = $randomAttributes['maxHp'];
    }
    $minPAtk = 0;
    if (!empty($randomAttributes['minPAtk'])) {
        $minPAtk = $randomAttributes['minPAtk'];
    }
    $maxPAtk = 0;
    if (!empty($randomAttributes['maxPAtk'])) {
        $maxPAtk = $randomAttributes['maxPAtk'];
    }
    $minPDef = 0;
    if (!empty($randomAttributes['minPDef'])) {
        $minPDef = $randomAttributes['minPDef'];
    }
    $maxPDef = 0;
    if (!empty($randomAttributes['maxPDef'])) {
        $maxPDef = $randomAttributes['maxPDef'];
    }
    $minMAtk = 0;
    if (!empty($randomAttributes['minMAtk'])) {
        $minMAtk = $randomAttributes['minMAtk'];
    }
    $maxMAtk = 0;
    if (!empty($randomAttributes['maxMAtk'])) {
        $maxMAtk = $randomAttributes['maxMAtk'];
    }
    $minMDef = 0;
    if (!empty($randomAttributes['minMDef'])) {
        $minMDef = $randomAttributes['minMDef'];
    }
    $maxMDef = 0;
    if (!empty($randomAttributes['maxMDef'])) {
        $maxMDef = $randomAttributes['maxMDef'];
    }
    $minSpd = 0;
    if (!empty($randomAttributes['minSpd'])) {
        $minSpd = $randomAttributes['minSpd'];
    }
    $maxSpd = 0;
    if (!empty($randomAttributes['maxSpd'])) {
        $maxSpd = $randomAttributes['maxSpd'];
    }
    $minEva = 0;
    if (!empty($randomAttributes['minEva'])) {
        $minEva = $randomAttributes['minEva'];
    }
    $maxEva = 0;
    if (!empty($randomAttributes['maxEva'])) {
        $maxEva = $randomAttributes['maxEva'];
    }
    $minAcc = 0;
    if (!empty($randomAttributes['minAcc'])) {
        $minAcc = $randomAttributes['minAcc'];
    }
    $maxAcc = 0;
    if (!empty($randomAttributes['maxAcc'])) {
        $maxAcc = $randomAttributes['maxAcc'];
    }
    $minCritChance = 0;
    if (!empty($randomAttributes['minCritChance'])) {
        $minCritChance = $randomAttributes['minCritChance'];
    }
    $maxCritChance = 0;
    if (!empty($randomAttributes['maxCritChance'])) {
        $maxCritChance = $randomAttributes['maxCritChance'];
    }
    $minCritDamageRate = 0;
    if (!empty($randomAttributes['minCritDamageRate'])) {
        $minCritDamageRate = $randomAttributes['minCritDamageRate'];
    }
    $maxCritDamageRate = 0;
    if (!empty($randomAttributes['maxCritDamageRate'])) {
        $maxCritDamageRate = $randomAttributes['maxCritDamageRate'];
    }
    $minBlockChance = 0;
    if (!empty($randomAttributes['minBlockChance'])) {
        $minBlockChance = $randomAttributes['minBlockChance'];
    }
    $maxBlockChance = 0;
    if (!empty($randomAttributes['maxBlockChance'])) {
        $maxBlockChance = $randomAttributes['maxBlockChance'];
    }
    $minBlockDamageRate = 0;
    if (!empty($randomAttributes['minBlockDamageRate'])) {
        $minBlockDamageRate = $randomAttributes['minBlockDamageRate'];
    }
    $maxBlockDamageRate = 0;
    if (!empty($randomAttributes['maxBlockDamageRate'])) {
        $maxBlockDamageRate = $randomAttributes['maxBlockDamageRate'];
    }
    $minResistanceChance = 0;
    if (!empty($randomAttributes['minResistanceChance'])) {
        $minResistanceChance = $randomAttributes['minResistanceChance'];
    }
    $maxResistanceChance = 0;
    if (!empty($randomAttributes['maxResistanceChance'])) {
        $maxResistanceChance = $randomAttributes['maxResistanceChance'];
    }
    $minBloodStealRateByPAtk = 0;
    if (!empty($randomAttributes['minBloodStealRateByPAtk'])) {
        $minBloodStealRateByPAtk = $randomAttributes['minBloodStealRateByPAtk'];
    }
    $maxBloodStealRateByPAtk = 0;
    if (!empty($randomAttributes['maxBloodStealRateByPAtk'])) {
        $maxBloodStealRateByPAtk = $randomAttributes['maxBloodStealRateByPAtk'];
    }
    $minBloodStealRateByMAtk = 0;
    if (!empty($randomAttributes['minBloodStealRateByMAtk'])) {
        $minBloodStealRateByMAtk = $randomAttributes['minBloodStealRateByMAtk'];
    }
    $maxBloodStealRateByMAtk = 0;
    if (!empty($randomAttributes['maxBloodStealRateByMAtk'])) {
        $maxBloodStealRateByMAtk = $randomAttributes['maxBloodStealRateByMAtk'];
    }
    
    $result = array();
    $randomingAmounts = array();

    // Hp
    $tempIntVal = rand($minHp, $maxHp);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::Hp] = $tempIntVal;
    }
    // PAtk
    $tempIntVal = rand($minPAtk, $maxPAtk);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::PAtk] = $tempIntVal;
    }
    // PDef
    $tempIntVal = rand($minPDef, $maxPDef);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::PDef] = $tempIntVal;
    }
    // MAtk
    $tempIntVal = rand($minMAtk, $maxMAtk);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::MAtk] = $tempIntVal;
    }
    // MDef
    $tempIntVal = rand($minMDef, $maxMDef);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::MDef] = $tempIntVal;
    }
    // Spd
    $tempIntVal = rand($minSpd, $maxSpd);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::Spd] = $tempIntVal;
    }
    // Eva
    $tempIntVal = rand($minEva, $maxEva);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::Eva] = $tempIntVal;
    }
    // Acc
    $tempIntVal = rand($minAcc, $maxAcc);
    if ($tempIntVal != 0) {
        $randomingAmounts[EAttributeType::Acc] = $tempIntVal;
    }
    // Crit Chance
    $tempFloatVal = rand($minCritChance, $maxCritChance);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::CritChance] = $tempFloatVal;
    }
    // Crit Damage Rate
    $tempFloatVal = rand($minCritDamageRate, $maxCritDamageRate);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::CritDamageRate] = $tempFloatVal;
    }
    // Block Chance
    $tempFloatVal = rand($minBlockChance, $maxBlockChance);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::BlockChance] = $tempFloatVal;
    }
    // Block Damage Rate
    $tempFloatVal = rand($minBlockDamageRate, $maxBlockDamageRate);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::BlockDamageRate] = $tempFloatVal;
    }
    // Resistance
    $tempFloatVal = rand($minResistanceChance, $maxResistanceChance);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::ResistanceChance] = $tempFloatVal;
    }
    // Blood Steal PATK
    $tempFloatVal = rand($minBloodStealRateByPAtk, $maxBloodStealRateByPAtk);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::BloodStealRateByPAtk] = $tempFloatVal;
    }
    // Blood Steal MATK
    $tempFloatVal = rand($minBloodStealRateByMAtk, $maxBloodStealRateByMAtk);
    if ($tempFloatVal != 0) {
        $randomingAmounts[EAttributeType::BloodStealRateByMAtk] = $tempFloatVal;
    }

    $shufflingKeys = array_keys($randomingAmounts);
    shuffle($shufflingKeys);
    $tempIntVal = rand($minType, $maxType);
    if (count($randomingAmounts) < $tempIntVal)
        $tempIntVal = count($randomingAmounts);

    for ($i = 0; $i < $tempIntVal; ++$i) {
        switch ($shufflingKeys[$i])
        {
            case EAttributeType::Hp:
                $result['hp'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::PAtk:
                $result['pAtk'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::PDef:
                $result['pDef'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::MAtk:
                $result['mAtk'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::MDef:
                $result['mDef'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::Spd:
                $result['spd'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::Eva:
                $result['eva'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::Acc:
                $result['acc'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::CritChance:
                $result['critChance'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::CritDamageRate:
                $result['critDamageRate'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::BlockChance:
                $result['blockChance'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::BlockDamageRate:
                $result['blockDamageRate'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::ResistanceChance:
                $result['resistanceChance'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::BloodStealRateByPAtk:
                $result['bloodStealRateByPAtk'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
            case EAttributeType::BloodStealRateByMAtk:
                $result['bloodStealRateByMAtk'] = $randomingAmounts[$shufflingKeys[$i]];
            break;
        }
    }

    return $result;
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
        $randomAttributes = GetItemRandomAttributes($dataId);
        $newEntry->randomedAttributes = empty($randomAttributes) ? '{}' : json_encode($randomAttributes);
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
        $oldFormation = $oldFormation->findone(array(
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
    $formation = $formation->findone(array(
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
    $playerUnlockItem = $playerUnlockItemDb->findone(array(
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

function HelperClearStage($createItems, $updateItems, $output, $player, $stage, $rating)
{
    $gameData = \Base::instance()->get('GameData');
    $playerId = $player->id;
    $playerClearStageDb = new PlayerClearStage();
    $playerClearStage = $playerClearStageDb->findone(array(
        'playerId = ? AND dataId = ?',
        $playerId,
        $stage['id']
    ));
    
    if (!$playerClearStage) {
        $playerClearStage = new PlayerClearStage();
        $playerClearStage->playerId = $playerId;
        $playerClearStage->dataId = $stage['id'];
        $playerClearStage->bestRating = $rating;
        $playerClearStage->save();
        // First clear rewards
        $updateCurrencies = array();
        $firstClearRewardPlayerExp = $stage['firstClearRewardPlayerExp'];
        $firstClearRewardSoftCurrency = $stage['firstClearRewardSoftCurrency'];
        $firstClearRewardHardCurrency = $stage['firstClearRewardHardCurrency'];
        $firstClearRewardItems = array();
        // Player exp
        $player->exp += $firstClearRewardPlayerExp;
        // Soft currency
        $softCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['softCurrencyId']]['id']);
        $softCurrency->amount += $firstClearRewardSoftCurrency;
        $softCurrency->update();
        $updateCurrencies[] = $softCurrency;
        // Hard currency
        $hardCurrency = GetCurrency($playerId, $gameData['currencies'][$gameData['hardCurrencyId']]['id']);
        $hardCurrency->amount += $firstClearRewardHardCurrency;
        $hardCurrency->update();
        $updateCurrencies[] = $hardCurrency;
        // Items
        $countfirstClearRewardItems = count($stage['firstClearRewardItems']);
        for ($i = 0; $i < $countfirstClearRewardItems; ++$i)
        {
            $rewardItem = $stage['firstClearRewardItems'][$i];
            if (empty($rewardItem) || empty($rewardItem['id'])) {
                continue;
            }
            
            $addItemsResult = AddItems($playerId, $rewardItem['id'], $rewardItem['amount']);
            if ($addItemsResult['success'])
            {
                $firstClearRewardItems[] = CreateEmptyItem($i, $playerId, $rewardItem['id'], $rewardItem['amount']);

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
        $output['isFirstClear'] = true;
        $output['createItems'] = ItemCursorsToArray($createItems);
        $output['updateItems'] = ItemCursorsToArray($updateItems);
        $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        $output['firstClearRewardPlayerExp'] = $firstClearRewardPlayerExp;
        $output['firstClearRewardSoftCurrency'] = $firstClearRewardSoftCurrency;
        $output['firstClearRewardHardCurrency'] = $firstClearRewardHardCurrency;
        $output['firstClearRewardItems'] = ItemCursorsToArray($firstClearRewardItems);
    } else {
        // If end stage with more rating, replace old rating
        if ($playerClearStage->bestRating < $rating) {
            $playerClearStage->bestRating = $rating;
            $playerClearStage->update();
        }
    }
    $output['clearStage'] = !empty($playerClearStage) ? CursorToArray($playerClearStage) : array('' => '');
    // Update achievement
    $playerAchievements = GetAchievementListInternal($playerId);
    $playerClearStages = GetClearStageListInternal($playerId);
    QueryUpdateAchievement(UpdateTotalClearStage($playerId, $playerAchievements, $playerClearStages));
    QueryUpdateAchievement(UpdateTotalClearStageRating($playerId, $playerAchievements, $playerClearStages));
    QueryUpdateAchievement(UpdateCountWinStage($playerId, $playerAchievements));
    return $output;
}

function QueryUpdateAchievement($updateResult)
{
    for ($i = 0; $i < count($updateResult['createAchievements']); ++$i)
    {
        $createAchievement = $updateResult['createAchievements'][$i];
        $createAchievement->save();
    }
    for ($i = 0; $i < count($updateResult['updateAchievements']); ++$i)
    {
        $updateAchievement = $updateResult['updateAchievements'][$i];
        $updateAchievement->update();
    }
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
            $currentCharacterData = $playerItemDb->findone(array(
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
    return $level;
}

function GetArenaRank($arenaScore)
{
    $gameData = \Base::instance()->get('GameData');
    $level = CalculateArenaRankLevel($arenaScore);
    if ($level >= count($gameData['arenaRanks'])) {
        $level = count($gameData['arenaRanks']) - 1;
    }
    return $level >= 0 ? $gameData['arenaRanks'][$level] : NULL;
}

function GetClanOwner($playerId, $clanId)
{
    $playerDb = new Player();
    $player = $playerDb->findone(array(
        'clanId = ? AND clanRole = 2',
        $clanId
    ));
    if ($player) {
        $playerFriendDb = new PlayerFriend();
        $isFriend = $playerFriendDb->count(array(
            'playerId = ? AND targetPlayerId = ?',
            $playerId,
            $player->id
        )) > 0;
        // Show leader character
        $character = GetLeaderCharacter($player->id, $player->selectedFormation);
        if (!empty($player->profileName) && $character) {
            return array(
                'id' => $player->id,
                'profileName' => $player->profileName,
                'exp' => $player->exp,
                'mainCharacter' => $character->dataId,
                'mainCharacterExp' => $character->exp,
                'selectedFormation' => $player->selectedFormation,
                'selectedArenaFormation' => $player->selectedArenaFormation,
                'clanId' => $player->clanId,
                'clanRole' => $player->clanRole,
                'isFriend' => $isFriend
            );
        }
    }
    return false;
}

function GetSocialPlayer($playerId, $targetPlayerId)
{
    $playerDb = new Player();
    $player = $playerDb->findone(array(
        'id = ?',
        $targetPlayerId
    ));
    if ($player) {
        $playerFriendDb = new PlayerFriend();
        $isFriend = $playerFriendDb->count(array(
            'playerId = ? AND targetPlayerId = ?',
            $playerId,
            $targetPlayerId
        )) > 0;
        if (empty($player->clanId)) {
            $player->clanId = '';
        }
        // Show leader character
        $character = GetLeaderCharacter($targetPlayerId, $player->selectedFormation);
        if (!empty($player->profileName) && $character) {
            return array(
                'id' => $targetPlayerId,
                'profileName' => $player->profileName,
                'exp' => $player->exp,
                'mainCharacter' => $character->dataId,
                'mainCharacterExp' => $character->exp,
                'selectedFormation' => $player->selectedFormation,
                'selectedArenaFormation' => $player->selectedArenaFormation,
                'clanId' => $player->clanId,
                'clanRole' => $player->clanRole,
                'isFriend' => $isFriend
            );
        }
    }
    return false;
}

function CreateEmptyItem($id, $playerId, $dataId, $amount)
{
    $newRewardEntry = new PlayerItem();
    $newRewardEntry->id = $id;
    $newRewardEntry->playerId = $playerId;
    $newRewardEntry->dataId = $dataId;
    $newRewardEntry->amount = $amount;
    $newRewardEntry->exp = 0;
    $newRewardEntry->equipItemId = '';
    $newRewardEntry->equipPosition = '';
    $newRewardEntry->randomedAttributes = '{}';
    $newRewardEntry->createdAt = 0;
    $newRewardEntry->updatedAt = 0;
    return $newRewardEntry;
}

function HaveEnoughMaterials($playerId, $materials, $requiredMaterials)
{
    $enoughMaterials = true;
    $playerItemDb = new PlayerItem();
    $updateItems = array();
    $deleteItemIds = array();
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

        $materialItems[] = $foundItem;
    }
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
    return array(
        "success" => $enoughMaterials,
        "updateItems" => $updateItems,
        "deleteItemIds" => $deleteItemIds
    );
}

function IsStageAvailable($stage)
{
    $available = true;
    $currentTime = mktime();
    $availabilities = $stage['availabilities'];
    if (!empty($availabilities)) {
        $available = false;
        foreach ($availabilities as $key => $value) {
            $fromTime = mktime($value['startTimeHour'], $value['startTimeMinute'], 0);
            $toTime = $fromTime + (60*60*$value['durationHour']) + (60*$value['durationMinute']);
            if (date('w') == $value['day'] && $currentTime >= $fromTime && $currentTime < $toTime) {
                $available = true;
                break;
            }
        }
    }
    if ($available) {
        if (!$stage['hasAvailableDate']) {
            return true;
        }
        $currentDate = mktime(0, 0, 0);
        $startDate = mktime(0, 0, 0, $stage['startMonth'], $stage['startDay'], $stage['startYear']);
        $endDate = $startDate + (60*60*24*$stage['durationDays']);
        return $currentDate >= $startDate && $currentDate < $endDate;
    }
    return false;
}
?>