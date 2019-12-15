<?php
function GetAchievementList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetAchievementListInternal($playerId))));
}

function GetAchievementListInternal($playerId)
{
    $playerAchievementDb = new PlayerAchievement();
    return $playerAchievementDb->find(array(
        'playerId = ?',
        $playerId
    ), array(
        'order' => 'updatedAt DESC'
    ));
}

function GetItemList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetItemListInternal($playerId))));
}

function GetItemListInternal($playerId)
{
    $playerItemDb = new PlayerItem();
    return $playerItemDb->find(array(
        'playerId = ?',
        $playerId
    ), array(
        'order' => 'updatedAt DESC'
    ));
}

function GetCurrencyList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetCurrencyListInternal($playerId))));
}

function GetCurrencyListInternal($playerId)
{
    $gameData = \Base::instance()->get('GameData');
    return [
        GetCurrency($playerId, $gameData['currencies']['HARD_CURRENCY']['id']),
        GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id'])
    ];
}

function GetStaminaList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetStaminaListInternal($playerId))));
}

function GetStaminaListInternal($playerId)
{
    $playerStaminaDb = new PlayerStamina();
    return $playerStaminaDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetFormationList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetFormationListInternal($playerId))));
}

function GetFormationListInternal($playerId)
{
    $playerFormationDb = new PlayerFormation();
    return $playerFormationDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetUnlockItemList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetUnlockItemListInternal($playerId))));
}

function GetUnlockItemListInternal($playerId)
{
    $playerUnlockItemDb = new PlayerUnlockItem();
    return $playerUnlockItemDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetClearStageList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetClearStageListInternal($playerId))));
}

function GetClearStageListInternal($playerId)
{
    $playerClearStageDb = new PlayerClearStage();
    return $playerClearStageDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetHelperList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Improve this
    $db = \Base::instance()->get('DB');
    $prefix = \Base::instance()->get('db_prefix');
    $playerFriendDb = new PlayerFriend();
    $playerFriends = $playerFriendDb->find(array(
        'playerId = ?',
        $playerId
    ));
    // Add list
    foreach ($playerFriends as $playerFriend) {
        $socialPlayer = GetSocialPlayer($playerId, $playerFriend->targetPlayerId);
        if ($socialPlayer) {
            $list[] = $socialPlayer;
        }
    }
    // If helpers not enough, fill more
    $countRows = count($list);
    $limit = 25 - $countRows;
    if ($limit > 0) {
        $rows = $db->exec('SELECT id FROM ' . $prefix . 'player WHERE profileName != "" AND id != "' . $playerId . '" ORDER BY rand() LIMIT ' . $limit);
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
    $playerFriendDb = new PlayerFriend();
    $playerFriends = $playerFriendDb->find(array(
        'playerId = ?',
        $playerId
    ));
    // Add list
    foreach ($playerFriends as $playerFriend) {
        $socialPlayer = GetSocialPlayer($playerId, $playerFriend->targetPlayerId);
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
    $playerFriendRequestDb = new PlayerFriendRequest();
    $playerFriendRequests = $playerFriendRequestDb->find(array(
        'targetPlayerId = ?',
        $playerId
    ));
    // Add list
    foreach ($playerFriendRequests as $playerFriendRequest) {
        $socialPlayer = GetSocialPlayer($playerId, $playerFriendRequest->playerId);
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
    $db = \Base::instance()->get('DB');
    $prefix = \Base::instance()->get('db_prefix');
    $arenaScoreCap = $arenaScore + 100;
    $limit = 25;
    $rows = $db->exec('SELECT id FROM ' . $prefix . 'player WHERE ' .
        'id != "' . $playerId . '" AND ' .
        'profileName != "" AND ' .
        'arenaScore < ' . $arenaScoreCap . ' ' .
        'ORDER BY arenaScore DESC LIMIT ' . $limit);
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row['id']);
        if (!$socialPlayer) {
            continue;
        }
        $opponentCharacterIds = GetFormationCharacterIds($socialPlayer['id'], $socialPlayer['selectedArenaFormation']);
        $count = count($opponentCharacterIds);
        if ($count <= 0) {
            continue;
        }
        $list[] = $socialPlayer;
    }
    // TODO: If opponents not enough, fill more (May be fake players)
    echo json_encode(array('list' => $list));
}

function GetServiceTime()
{
    echo json_encode(array('serviceTime' => time()));
}
?>