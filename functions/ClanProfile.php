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

function SetClanIcon($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $data = $gameData['clanIcons'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new ClanUnlockIcon();
        $count = $db->count(array('clanId = ? AND dataId = ?', $clanId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    $clan = GetClan();
    if (!$clan) {
        $canUse = false;
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clan->frameId = $dataId;
        $clan->update();
    }
    echo json_encode($output);
}

function SetClanFrame($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $data = $gameData['clanFrames'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new ClanUnlockFrame();
        $count = $db->count(array('clanId = ? AND dataId = ?', $clanId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    $clan = GetClan();
    if (!$clan) {
        $canUse = false;
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clan->titleId = $dataId;
        $clan->update();
    }
    echo json_encode($output);
}

function SetClanTitle($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $data = $gameData['clanTitles'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new ClanUnlockTitle();
        $count = $db->count(array('clanId = ? AND dataId = ?', $clanId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    $clan = GetClan();
    if (!$clan) {
        $canUse = false;
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clan->titleId = $dataId;
        $clan->update();
    }
    echo json_encode($output);
}
?>