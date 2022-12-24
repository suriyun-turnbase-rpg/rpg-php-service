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

function SetPlayerIcon($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $data = $gameData['playerIcons'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new PlayerUnlockIcon();
        $count = $db->count(array('playerId = ? AND dataId = ?', $playerId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $player->iconId = $dataId;
        $player->update();
        $output['dataId'] = $dataId;
    }
    echo json_encode($output);
}

function SetPlayerFrame($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $data = $gameData['playerFrames'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new PlayerUnlockFrame();
        $count = $db->count(array('playerId = ? AND dataId = ?', $playerId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $player->frameId = $dataId;
        $player->update();
        $output['dataId'] = $dataId;
    }
    echo json_encode($output);
}

function SetPlayerTitle($dataId) {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $data = $gameData['playerTitles'][$dataId];
    $canUse = true;
    if ($data['locked']) {
        $db = new PlayerUnlockTitle();
        $count = $db->count(array('playerId = ? AND dataId = ?', $playerId, $dataId));
        if ($count <= 0) {
            $canUse = false;
        }
    }
    if (!$canUse) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $player->titleId = $dataId;
        $player->update();
        $output['dataId'] = $dataId;
    }
    echo json_encode($output);
}
?>