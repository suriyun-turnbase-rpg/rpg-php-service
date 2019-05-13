<?php
function GetItemList()
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

function GetCurrencyList()
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

function GetStaminaList()
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

function GetFormationList()
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

function GetUnlockItemList()
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

function GetClearStageList()
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

function GetHelperList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetFriendList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetFriendRequestList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetOpponentList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => $list));
}

function GetServiceTime()
{
    echo json_encode(array('serviceTime' => time()));
}
?>