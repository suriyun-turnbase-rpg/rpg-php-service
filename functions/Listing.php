<?php
function GetItemList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerItemDb = new PlayerItem();
    $list = $playerItemDb->find(array(
        'playerId = ?',
        $playerId
    ), array(
        'order' => 'updatedAt DESC'
    ));
    echo json_encode(array('list' => $list));
}

function GetCurrencyList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerCurrencyDb = new PlayerCurrency();
    $list = $playerCurrencyDb->find(array(
        'playerId = ?',
        $playerId
    ));
    echo json_encode(array('list' => $list));
}

function GetStaminaList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerStaminaDb = new PlayerStamina();
    $list = $playerStaminaDb->find(array(
        'playerId = ?',
        $playerId
    ));
    echo json_encode(array('list' => $list));
}

function GetFormationList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerFormationDb = new PlayerFormation();
    $list = $playerFormationDb->find(array(
        'playerId = ?',
        $playerId
    ));
    echo json_encode(array('list' => $list));
}

function GetUnlockItemList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerUnlockItemDb = new PlayerUnlockItem();
    $list = $playerUnlockItemDb->find(array(
        'playerId = ?',
        $playerId
    ));
    echo json_encode(array('list' => $list));
}

function GetClearStageList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $playerClearStageDb = new PlayerClearStage();
    $list = $playerClearStageDb->find(array(
        'playerId = ?',
        $playerId
    ));
    echo json_encode(array('list' => $list));
}

function GetHelperList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetFriendList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetFriendRequestList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetOpponentList($f3, $params)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function ServiceTime($f3, $params)
{
    echo json_encode(array('serviceTime' => time()));
}
?>