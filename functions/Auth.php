<?php

function Login($username, $password)
{
    $output = array();
    if (empty($username) || empty($password)) {
        $output['error'] = 'EMPTY_USERNAME_OR_PASSWORD';
    } else {
        $playerAuthDb = new PlayerAuth();
        $playerAuth = $playerAuthDb->load(array(
            'username = ? AND password = ? AND type = 1',
            $username,
            $password
        ));
        $playerDb = new Player();
        $player = $playerDb->load(array(
            'id = ?',
            $playerAuth->id
        ));
        if (!$player) {
            $output['error'] = 'INVALID_USERNAME_OR_PASSWORD';
        } else {
            $player = UpdatePlayerLoginToken($player);
            UpdatePlayerStamina($player);
            $output['player'] = $player;
        }
    }
    echo json_encode($output);
}

function RegisterOrLogin($username, $password)
{
    $playerAuthDb = new PlayerAuth();
    $playerAuth = $playerAuthDb->load(array(
        'username = ? AND type = 1',
        $username
    ));
    if ($playerAuth) {
        Login($username, $password);
    } else {
        Register($username, $password);
    }
}

function GuestLogin($deviceId)
{
    $output = array();
    if (empty($deviceId)) {
        $output['error'] = 'EMPTY_USERNAME_OR_PASSWORD';
    }  else if (IsPlayerWithUsernameFound(0, $deviceId)) {
        $playerDb = new Player();
        $player = $playerDb->load(array(
            'id = ?',
            $playerAuth->id
        ));
        if (!$player) {
            $output['error'] = 'INVALID_USERNAME_OR_PASSWORD';
        } else {
            $player = UpdatePlayerLoginToken($player);
            UpdatePlayerStamina($player);
            $output['player'] = $player;
        }
    } else {
        $player = InsertNewPlayer(0, $deviceId, $deviceId);
        $output['player'] = $resultPlayer;
    }
    echo json_encode($output);
}

function ValidateLoginToken($refreshToken)
{
    $output = array();
    $player = GetPlayer();
    if (!$player) {
        $output['error'] = 'INVALID_LOGIN_TOKEN';
    } else {
        if ($refreshToken) {
            $player = UpdatePlayerLoginToken($player);
        }
        UpdatePlayerStamina($player);
        $output['player'] = $player;
    }
    echo json_encode($output);
}

function SetProfileName($profileName)
{
    $output = array();
    $player = GetPlayer();
    $playerDb = new Player();
    $countPlayerWithName = $playerDb->count(array(
        'profileName = ?',
        $profileName
    ));
    if (!$player) {
        $output['error'] = 'INVALID_LOGIN_TOKEN';
    } else if (empty($profileName)) {
        $output['error'] = 'EMPTY_PROFILE_NAME';
    } else if ($countPlayerWithName > 0) {
        $output['error'] = 'EXISTED_PROFILE_NAME';
    } else {
        $player->profileName = $profileName;
        $player->update();
        $output['player'] = $player;
    }
    echo json_encode($output);
}

function Register($username, $password)
{
    $output = array();
    if (empty($username) || empty($password)) {
        $output['error'] = 'EMPTY_USERNAME_OR_PASSWORD';
    } else if (IsPlayerWithUsernameFound(1, $username)) {
        $output['error'] = 'EXISTED_USERNAME';
    } else {
        $player = InsertNewPlayer(1, $username, $password);
        $output['player'] = $player;
    }
    echo json_encode($output);
}
?>