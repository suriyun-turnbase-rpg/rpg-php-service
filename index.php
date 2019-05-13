<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Origin, Cache-Control, X-Requested-With, Content-Type, Access-Control-Allow-Origin');
header('Access-Control-Allow-Methods: *');
header('Content-type: application/json');

require_once('config.php');
$f3 = require_once('fatfree/lib/base.php');

if ((float)PCRE_VERSION < 7.9) {
    trigger_error('PCRE version is out of date');
}

// Read configs
$f3->config('configs/config.ini');

// Read game data
$gameDataJson = file_get_contents('./GameData.json');
$f3->set('GameData', json_decode($gameDataJson, true));

// Prepare database
$f3->set('DB', new DB\SQL('mysql:'.
    'host='.$GLOBALS['db_host'].';'.
    'port='.$GLOBALS['db_port'].';'.
    'dbname='.$GLOBALS['db_name'], 
    $GLOBALS['db_user'], 
    $GLOBALS['db_pass']));

// Prepare functions
$f3->set('AUTOLOAD', 'databases/|functions/|jwt/');

// Services
$f3->route('GET /', function() {
    echo ';)';
});

// Auth services
$f3->route('POST /login', function($f3, $params) {
    Login($_POST['username'], $_POST['password']);
});
$f3->route('POST /register', function($f3, $params) {
    Register($_POST['username'], $_POST['password']);
});
$f3->route('POST /guest-login', function($f3, $params) {
    GuestLogin($_POST['deviceId']);
});
$f3->route('POST /validate-login-token', function($f3, $params) {
    GuestLogin($_POST['refreshToken']);
});
$f3->route('POST /set-profile-name', function($f3, $params) {
    GuestLogin($_POST['profileName']);
});
// Listing services
$f3->route('GET /items', function($f3, $params) {
    GetItemList();
});
$f3->route('GET /currencies', function($f3, $params) {
    GetCurrencyList();
});
$f3->route('GET /staminas', function($f3, $params) {
    GetStaminaList();
});
$f3->route('GET /formations', function($f3, $params) {
    GetFormationList();
});
$f3->route('GET /unlock-items', function($f3, $params) {
    GetUnlockItemList();
});
$f3->route('GET /clear-stages', function($f3, $params) {
    GetClearStageList();
});
$f3->route('GET /helpers', function($f3, $params) {
    GetHelperList();
});
$f3->route('GET /friends', function($f3, $params) {
    GetFriendList();
});
$f3->route('GET /friend-requests', function($f3, $params) {
    GetFriendRequestList();
});
$f3->route('GET /opponents', function($f3, $params) {
    GetOpponentList();
});
$f3->route('GET /service-time', function($f3, $params) {
    GetServiceTime();
});
// Item services
$f3->route('POST /levelup-item', function($f3, $params) {
    LevelUpItem($_POST['itemId'], $_POST['materials']);
});
$f3->route('POST /evolve-item', function($f3, $params) {
    EvolveItem($_POST['itemId'], $_POST['materials']);
});
$f3->route('POST /sell-items', function($f3, $params) {
    SellItems($_POST['items']);
});
$f3->route('POST /equip-item', function($f3, $params) {
    EquipItem($_POST['characterId'], $_POST['equipmentId'], $_POST['equipPosition']);
});
$f3->route('POST /unequip-item', function($f3, $params) {
    UnEquipItem($_POST['equipmentId']);
});
$f3->route('GET /available-lootboxes', function($f3, $params) {
    GetAvailableLootBoxList();
});
$f3->route('GET /available-iap-packages', function($f3, $params) {
    GetAvailableIapPackageList();
});
$f3->route('POST /open-lootbox', function($f3, $params) {
    OpenLootBox($_POST['lootBoxDataId'], $_POST['packIndex']);
});
// Social services
$f3->route('POST /friend-request', function($f3, $params) {
    FriendRequest($_POST['targetPlayerId']);
});
$f3->route('POST /friend-accept', function($f3, $params) {
    FriendAccept($_POST['targetPlayerId']);
});
$f3->route('POST /friend-decline', function($f3, $params) {
    FriendDecline($_POST['targetPlayerId']);
});
$f3->route('POST /friend-delete', function($f3, $params) {
    FriendDelete($_POST['targetPlayerId']);
});
$f3->route('POST /find-player', function($f3, $params) {
    FindPlayer($_POST['profileName']);
});
// Battle services
$f3->route('POST /start-stage', function($f3, $params) {
    StartStage($_POST['stageDataId']);
});
$f3->route('POST /finish-stage', function($f3, $params) {
    FinishStage($_POST['session'], $_POST['battleResult'], $_POST['deadCharacters']);
});
$f3->route('POST /revive-characters', function($f3, $params) {
    ReviveCharacters();
});
$f3->route('POST /select-formation', function($f3, $params) {
    SelectFormation($_POST['formationName'], $_POST['formationType']);
});
$f3->route('POST /set-formation', function($f3, $params) {
    SetFormation($_POST['characterId'], $_POST['formationName'], $_POST['position']);
});
// Arena services
$f3->route('POST /start-duel', function($f3, $params) {
    StartStage($_POST['targetPlayerId']);
});
$f3->route('POST /finish-duel', function($f3, $params) {
    FinishDuel($_POST['session'], $_POST['battleResult'], $_POST['deadCharacters']);
});
$f3->route('POST /finish-duel', function($f3, $params) {
    FinishDuel($_POST['session'], $_POST['battleResult'], $_POST['deadCharacters']);
});

$f3->run();
?>