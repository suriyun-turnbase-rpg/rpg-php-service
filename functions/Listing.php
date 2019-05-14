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
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetCurrencyList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $gameData = \Base::instance()->get('GameData');
    echo json_encode(array('list' => CursorsToArray([
        GetCurrency($playerId, $gameData['currencies']['HARD_CURRENCY']['id']),
        GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id'])
    ])));
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
    echo json_encode(array('list' => CursorsToArray($list)));
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
    echo json_encode(array('list' => CursorsToArray($list)));
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
    echo json_encode(array('list' => CursorsToArray($list)));
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
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetHelperList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetFriendList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetFriendRequestList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetOpponentList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Implement this
    echo json_encode(array('list' => CursorsToArray($list)));
}

function GetServiceTime()
{
    echo json_encode(array('serviceTime' => time()));
}
?>