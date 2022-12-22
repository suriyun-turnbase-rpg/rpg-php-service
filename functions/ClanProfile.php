<?php
function GetClanUnlockIconList()
{
    $player = GetPlayer();
    $clanId = $player->clanId;
    echo json_encode(array('list' => CursorsToArray(GetClanUnlockIconListInternal($clanId))));
}

function GetClanUnlockIconListInternal($clanId)
{
    $clanUnlockIconDb = new ClanUnlockIcon();
    return $clanUnlockIconDb->find(array(
        'clanId = ?',
        $clanId
    ));
}

function GetClanUnlockFrameList()
{
    $player = GetPlayer();
    $clanId = $player->clanId;
    echo json_encode(array('list' => CursorsToArray(GetClanUnlockFrameListInternal($clanId))));
}

function GetClanUnlockFrameListInternal($clanId)
{
    $clanUnlockFrameDb = new ClanUnlockFrame();
    return $clanUnlockFrameDb->find(array(
        'clanId = ?',
        $clanId
    ));
}

function GetClanUnlockTitleList()
{
    $player = GetPlayer();
    $clanId = $player->clanId;
    echo json_encode(array('list' => CursorsToArray(GetClanUnlockTitleListInternal($clanId))));
}

function GetClanUnlockTitleListInternal($clanId)
{
    $clanUnlockTitleDb = new ClanUnlockTitle();
    return $clanUnlockTitleDb->find(array(
        'clanId = ?',
        $clanId
    ));
}
?>