<?php
function GetUnlockIconList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetUnlockIconListInternal($playerId))));
}

function GetUnlockIconListInternal($playerId)
{
    $playerUnlockIconDb = new PlayerUnlockIcon();
    return $playerUnlockIconDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetUnlockFrameList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetUnlockFrameListInternal($playerId))));
}

function GetUnlockFrameListInternal($playerId)
{
    $playerUnlockFrameDb = new PlayerUnlockFrame();
    return $playerUnlockFrameDb->find(array(
        'playerId = ?',
        $playerId
    ));
}

function GetUnlockTitleList()
{
    $player = GetPlayer();
    $playerId = $player->id;
    echo json_encode(array('list' => CursorsToArray(GetUnlockTitleListInternal($playerId))));
}

function GetUnlockTitleListInternal($playerId)
{
    $playerUnlockTitleDb = new PlayerUnlockTitle();
    return $playerUnlockTitleDb->find(array(
        'playerId = ?',
        $playerId
    ));
}
?>