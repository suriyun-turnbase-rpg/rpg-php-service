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
    // TODO: Improve this
    $db = \Base::instance()->get('DB');
    $prefix = \Base::instance()->get('db_prefix');
    $rows = $db->exec('SELECT ' . $prefix . 'player.id AS id, ' .
        $prefix . 'player_friend.targetPlayerId AS targetPlayerId FROM ' .
        $prefix . 'player RIGHT JOIN ' . $prefix . 'player_friend ON ' .
        $prefix . 'player.id = ' . $prefix . 'player_friend.playerId WHERE '.
        $prefix . 'player.id = :playerId',
        array(':playerId' => $playerId));
    // Add list
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row['targetPlayerId']);
        if ($socialPlayer) {
            $list[] = $socialPlayer;
        }
    }
    // If helpers not enough, fill more
    $countRows = count($rows);
    $limit = 25 - $countRows;
    if ($limit > 0) {
        $rows = $db->exec('SELECT id FROM ' . $prefix . 'player WHERE profileName != "" ORDER BY rand() LIMIT ' . $limit);
        foreach ($rows as $row) {
            $socialPlayer = GetSocialPlayer($playerId, $row['id']);
            if ($socialPlayer) {
                $list[] = $socialPlayer;
            }
        }
    }
    echo json_encode(array('list' => $list));
}

function GetFriendList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Improve this
    $db = \Base::instance()->get('DB');
    $prefix = \Base::instance()->get('db_prefix');
    $rows = $db->exec('SELECT ' . $prefix . 'player.id AS id, ' .
        $prefix . 'player_friend.targetPlayerId AS targetPlayerId FROM ' .
        $prefix . 'player RIGHT JOIN ' . $prefix . 'player_friend ON ' .
        $prefix . 'player.id = ' . $prefix . 'player_friend.playerId WHERE '.
        $prefix . 'player.id = :playerId',
        array(':playerId' => $playerId));
    // Add list
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row['targetPlayerId']);
        if ($socialPlayer) {
            $list[] = $socialPlayer;
        }
    }
    echo json_encode(array('list' => $list));
}

function GetFriendRequestList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Improve this
    $db = \Base::instance()->get('DB');
    $prefix = \Base::instance()->get('db_prefix');
    $rows = $db->exec('SELECT ' . $prefix . 'player.id AS id, ' .
        $prefix . 'player_friend_request.targetPlayerId AS targetPlayerId FROM ' .
        $prefix . 'player RIGHT JOIN ' . $prefix . 'player_friend_request ON ' .
        $prefix . 'player.id = ' . $prefix . 'player_friend_request.playerId WHERE '.
        $prefix . 'player_friend_request.playerId = :playerId',
        array(':playerId' => $playerId));
    // Add list
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row['targetPlayerId']);
        if ($socialPlayer) {
            $list[] = $socialPlayer;
        }
    }
    echo json_encode(array('list' => $list));
}

function GetOpponentList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $arenaScore = $player->arenaScore;
    $list = array();
    // TODO: Improve this
    $arenaScoreCap = $arenaScore + 100;
    $limit = 25;
    $rows = $db->exec('SELECT id FROM ' . $prefix . 'player WHERE ' . 
        'profileName != "" AND ' . 
        'arenaScore < ' . $arenaScoreCap . ' ' . 
        'ORDER BY arenaScore DESC LIMIT ' . $limit);
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row['id']);
        if ($socialPlayer) {
            $list[] = $socialPlayer;
        }
    }
    // TODO: If opponents not enough, fill more (May be fake players)
    echo json_encode(array('list' => $list));
}

function GetServiceTime()
{
    echo json_encode(array('serviceTime' => time()));
}
?>