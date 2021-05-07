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
    echo json_encode(array('list' => ItemCursorsToArray(GetItemListInternal($playerId))));
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
    $list = array();
    $currencies = $gameData['currencies'];
    foreach ($currencies as $key => $value) {
        $list[] = GetCurrency($playerId, $key);
    }
    return $list;
}

function GetStaminaList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetStaminaListInternal($playerId))));
}

function GetStaminaListInternal($playerId)
{
    $gameData = \Base::instance()->get('GameData');
    $list = array();
    $staminas = $gameData['staminas'];
    foreach ($staminas as $key => $value) {
        $list[] = GetStamina($playerId, $key);
    }
    return $list;
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
        $playerDb = new Player();
        $rows = $playerDb->find(
            array('profileName != "" AND id != ?', $playerId),
            array('order' => 'rand() ', 'limit' => $limit)
        );
        foreach ($rows as $row) {
            $socialPlayer = GetSocialPlayer($playerId, $row->id);
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

function GetPendingRequestList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    // TODO: Improve this
    $playerFriendRequestDb = new PlayerFriendRequest();
    $playerFriendRequests = $playerFriendRequestDb->find(array(
        'playerId = ?',
        $playerId
    ));
    // Add list
    foreach ($playerFriendRequests as $playerFriendRequest) {
        $socialPlayer = GetSocialPlayer($playerId, $playerFriendRequest->targetPlayerId);
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
    $arenaScoreCap = $arenaScore + 100;
    $limit = 25;
    $playerDb = new Player();
    $rows = $playerDb->find(
        array('profileName != "" AND id != ? AND arenaScore < ?', $playerId, $arenaScoreCap),
        array('order' => 'arenaScore DESC', 'limit' => $limit)
    );
    foreach ($rows as $row) {
        $socialPlayer = GetSocialPlayer($playerId, $row->id);
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

function GetRaidEventList()
{
    $currentTime = time();
    $raidEventDb = new RaidEvent();
    $raidEvents = $raidEventDb->find(array(
        'startTime < ? AND endTime >= ?',
        $currentTime,
        $currentTime
    ));
    echo json_encode(array('list' => ItemCursorsToArray($raidEvents)));
}

function GetMailList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => ItemCursorsToArray(GetMailListInternal($playerId))));
}

function GetMailListInternal($playerId)
{
    $mailDb = new Mail();
    return $mailDb->find(array(
        'playerId = ? AND isDelete = 0',
        $playerId
    ), array(
        'order' => 'sentTimestamp DESC, isRead ASC, isClaim ASC'
    ));
}

function GetServiceTime()
{
    echo json_encode(array('serviceTime' => time()));
}
?>