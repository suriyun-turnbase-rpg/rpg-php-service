<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Origin, Cache-Control, X-Requested-With, Content-Type, Access-Control-Allow-Origin');
header('Access-Control-Allow-Methods: *');
header('Content-type: application/json');

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
    'host='.$f3->get('db_host').';'.
    'port='.$f3->get('db_port').';'.
    'dbname='.$f3->get('db_name'), 
    $f3->get('db_user'), 
    $f3->get('db_pass')));

// Prepare functions
$f3->set('AUTOLOAD', 'databases/|enums/');
require_once('jwt/BeforeValidException.php');
require_once('jwt/ExpiredException.php');
require_once('jwt/SignatureInvalidException.php');
require_once('jwt/JWT.php');
require_once('functions/Helpers.php');
require_once('functions/Listing.php');
require_once('functions/Achievement.php');
require_once('functions/Auth.php');
require_once('functions/Item.php');
require_once('functions/Social.php');
require_once('functions/Battle.php');
require_once('functions/Arena.php');
require_once('functions/Billing.php');

// Services
$f3->route('GET /', function() {
    echo ';)';
});

// Auth services
$f3->route('POST /login', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    Login($postBody['username'], $postBody['password']);
});
$f3->route('POST /register', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    Register($postBody['username'], $postBody['password']);
});
$f3->route('POST /guest-login', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    GuestLogin($postBody['deviceId']);
});
$f3->route('POST /validate-login-token', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    ValidateLoginToken($postBody['refreshToken']);
});
$f3->route('POST /set-profile-name', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    SetProfileName($postBody['profileName']);
});
// Listing services
$f3->route('GET /achievements', function($f3, $params) {
    GetAchievementList();
});
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
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    LevelUpItem($postBody['itemId'], $postBody['materials']);
});
$f3->route('POST /evolve-item', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    EvolveItem($postBody['itemId'], $postBody['materials']);
});
$f3->route('POST /sell-items', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    SellItems($postBody['items']);
});
$f3->route('POST /equip-item', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    EquipItem($postBody['characterId'], $postBody['equipmentId'], $postBody['equipPosition']);
});
$f3->route('POST /unequip-item', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    UnEquipItem($postBody['equipmentId']);
});
$f3->route('GET /available-lootboxes', function($f3, $params) {
    GetAvailableLootBoxList();
});
$f3->route('GET /available-iap-packages', function($f3, $params) {
    GetAvailableIapPackageList();
});
$f3->route('POST /open-lootbox', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    OpenLootBox($postBody['lootBoxDataId'], $postBody['packIndex']);
});
// Social services
$f3->route('POST /friend-request', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FriendRequest($postBody['targetPlayerId']);
});
$f3->route('POST /friend-accept', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FriendAccept($postBody['targetPlayerId']);
});
$f3->route('POST /friend-decline', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FriendDecline($postBody['targetPlayerId']);
});
$f3->route('POST /friend-delete', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FriendDelete($postBody['targetPlayerId']);
});
$f3->route('POST /find-player', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FindPlayer($postBody['profileName']);
});
// Battle services
$f3->route('POST /start-stage', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    StartStage($postBody['stageDataId']);
});
$f3->route('POST /finish-stage', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FinishStage($postBody['session'], $postBody['battleResult'], $postBody['deadCharacters']);
});
$f3->route('POST /revive-characters', function($f3, $params) {
    ReviveCharacters();
});
$f3->route('POST /select-formation', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    SelectFormation($postBody['formationName'], $postBody['formationType']);
});
$f3->route('POST /set-formation', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    SetFormation($postBody['characterId'], $postBody['formationName'], $postBody['position']);
});
// Arena services
$f3->route('POST /start-duel', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    StartDuel($postBody['targetPlayerId']);
});
$f3->route('POST /finish-duel', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    FinishDuel($postBody['session'], $postBody['battleResult'], $postBody['deadCharacters']);
});
// Billing services
$f3->route('POST /ios-buy-goods', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    IOSBuyGoods($postBody['iapPackageDataId'], $postBody['receipt']);
});
$f3->route('POST /google-play-buy-goods', function($f3, $params) {
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    AndroidBuyGoods($postBody['iapPackageDataId'], $postBody['data'], $postBody['signature']);
});

$f3->run();
?>