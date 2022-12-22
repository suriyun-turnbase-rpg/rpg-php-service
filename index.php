<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Origin, Cache-Control, X-Requested-With, Content-Type, Access-Control-Allow-Origin');
header('Access-Control-Allow-Methods: *');
header('Content-type: application/json');

$f3 = require_once('fatfree/base.php');

// Read configs
$f3->config('configs/config.ini');

// Read game data
$gameDataJson = file_get_contents('./GameData.json');
if (!$f3->exists('GameData')) {
    $f3->set('GameData', json_decode($gameDataJson, true));
}

// Prepare database
if (!$f3->exists('DB')) {
    $f3->set('DB', new DB\SQL('mysql:'.
        'host='.$f3->get('db_host').';'.
        'port='.$f3->get('db_port').';'.
        'dbname='.$f3->get('db_name'), 
        $f3->get('db_user'), 
        $f3->get('db_pass')));
}

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
require_once('functions/Clan.php');
require_once('functions/Chat.php');
require_once('functions/RaidBoss.php');
require_once('functions/ClanBoss.php');
require_once('functions/Mail.php');
require_once('functions/RandomStore.php');
require_once('functions/DailyReward.php');
// Initial services
// TODO: Theses should be called by cronjob settings
$player = GetPlayer(true);
if ($player) {
    CreateRaidEvent();
    CreateClanEvent();
    RaidEventRewarding();
    ClanEventRewarding();
}
// API actions
$actions = array(
    // Auth services
    'login' => array('POST', 'Login', array('username', 'password')),
    'register' => array('POST', 'Register', array('username', 'password')),
    'guest-login' => array('POST', 'GuestLogin', array('deviceId')),
    'validate-login-token' => array('POST', 'ValidateLoginToken', array('refreshToken')),
    'set-profile-name' => array('POST', 'SetProfileName', array('profileName')),
    // Listing services
    'achievements' => array('GET', 'GetAchievementList', array()),
    'items' => array('GET', 'GetItemList', array()),
    'currencies' => array('GET', 'GetCurrencyList', array()),
    'staminas' => array('GET', 'GetStaminaList', array()),
    'formations' => array('GET', 'GetFormationList', array()),
    'unlock-items' => array('GET', 'GetUnlockItemList', array()),
    'clear-stages' => array('GET', 'GetClearStageList', array()),
    'helpers' => array('GET', 'GetHelperList', array()),
    'friends' => array('GET', 'GetFriendList', array()),
    'friend-requests' => array('GET', 'GetFriendRequestList', array()),
    'pending-requests' => array('GET', 'GetPendingRequestList', array()),
    'opponents' => array('GET', 'GetOpponentList', array()),
    'raid-events' => array('GET', 'GetRaidEventList', array()),
    'clan-events' => array('GET', 'GetClanEventList', array()),
    'service-time' => array('GET', 'GetServiceTime', array()),
    // Item services
    'levelup-item' => array('POST', 'LevelUpItem', array('itemId', 'materials')),
    'evolve-item' => array('POST', 'EvolveItem', array('itemId', 'materials')),
    'sell-items' => array('POST', 'SellItems', array('items')),
    'equip-item' => array('POST', 'EquipItem', array('characterId', 'equipmentId', 'equipPosition')),
    'unequip-item' => array('POST', 'UnEquipItem', array('equipmentId')),
    'craft-item' => array('POST', 'CraftItem', array('itemCraftId', 'materials')),
    'available-lootboxes' => array('GET', 'GetAvailableLootBoxList', array()),
    'available-iap-packages' => array('GET', 'GetAvailableIapPackageList', array()),
    'available-ingame-packages' => array('GET', 'GetAvailableInGamePackageList', array()),
    'open-lootbox' => array('POST', 'OpenLootBox', array('lootBoxDataId', 'packIndex')),
    'open-ingame-package' => array('POST', 'OpenInGamePackage', array('inGamePackageDataId')),
    'convert-hard-currency' => array('POST', 'ConvertHardCurrency', array('requireHardCurrency')),
    'refill-stamina' => array('POST', 'RefillStamina', array('staminaDataId')),
    'refill-stamina-info' => array('GET', 'GetRefillStaminaInfo', array('staminaDataId')),
    'available-stages' => array('GET', 'GetAvailableStageList', array()),
    // Social services
    'friend-request' => array('POST', 'FriendRequest', array('targetPlayerId')),
    'friend-accept' => array('POST', 'FriendAccept', array('targetPlayerId')),
    'friend-decline' => array('POST', 'FriendDecline', array('targetPlayerId')),
    'friend-delete' => array('POST', 'FriendDelete', array('targetPlayerId')),
    'friend-request-delete' => array('POST', 'FriendRequestDelete', array('targetPlayerId')),
    'find-player' => array('POST', 'FindPlayer', array('profileName')),
    // Battle services
    'start-stage' => array('POST', 'StartStage', array('stageDataId', 'helperPlayerId')),
    'finish-stage' => array('POST', 'FinishStage', array('session', 'battleResult', 'totalDamage', 'deadCharacters')),
    'revive-characters' => array('POST', 'ReviveCharacters', array()),
    'select-formation' => array('POST', 'SelectFormation', array('formationName', 'formationType')),
    'set-formation' => array('POST', 'SetFormation', array('characterId', 'formationName', 'position')),
    // Arena services
    'start-duel' => array('POST', 'StartDuel', array('targetPlayerId')),
    'finish-duel' => array('POST', 'FinishDuel', array('session', 'battleResult', 'totalDamage', 'deadCharacters')),
    // Raid boss services
    'start-raid-boss-battle' => array('POST', 'StartRaidBossBattle', array('eventId')),
    'finish-raid-boss-battle' => array('POST', 'FinishRaidBossBattle', array('session', 'battleResult', 'totalDamage', 'deadCharacters')),
    // Clan boss services
    'start-clan-boss-battle' => array('POST', 'StartClanBossBattle', array('eventId')),
    'finish-clan-boss-battle' => array('POST', 'FinishClanBossBattle', array('session', 'battleResult', 'totalDamage', 'deadCharacters')),
    // Billing services
    'ios-buy-goods' => array('POST', 'IOSBuyGoods', array('iapPackageDataId', 'receipt')),
    'google-play-buy-goods' => array('POST', 'AndroidBuyGoods', array('iapPackageDataId', 'data', 'signature')),
    // Achievement services
    'earn-achievement-reward' => array('POST', 'EarnAchievementReward', array('achievementId')),
    // Clan services
    'create-clan' => array('POST', 'CreateClan', array('clanName')),
    'find-clan' => array('POST', 'FindClan', array('clanName')),
    'clan-join-request' => array('POST', 'ClanJoinRequest', array('clanId')),
    'clan-join-accept' => array('POST', 'ClanJoinAccept', array('targetPlayerId')),
    'clan-join-decline' => array('POST', 'ClanJoinDecline', array('targetPlayerId')),
    'clan-member-delete' => array('POST', 'ClanMemberDelete', array('targetPlayerId')),
    'clan-join-request-delete' => array('POST', 'ClanJoinRequestDelete', array('clanId')),
    'clan-members' => array('GET', 'ClanMembers', array()),
    'clan-owner-transfer' => array('POST', 'ClanOwnerTransfer', array('targetPlayerId')),
    'clan-terminate' => array('POST', 'ClanTerminate', array()),
    'clan' => array('GET', 'GetClan', array()),
    'clan-join-requests' => array('GET', 'ClanJoinRequests', array()),
    'clan-join-pending-requests' => array('GET', 'ClanJoinPendingRequests', array()),
    'clan-exit' => array('POST', 'ClanExit', array()),
    'clan-set-role' => array('POST', 'ClanSetRole', array('targetPlayerId', 'clanRole')),
    'clan-checkin' => array('POST', 'ClanCheckin', array()),
    'clan-checkin-status' => array('GET', 'GetClanCheckinStatus', array()),
    'clan-donation' => array('POST', 'ClanDonation', array('clanDonationDataId')),
    'clan-donation-status' => array('GET', 'GetClanDonationStatus', array()),
    // Chat services
    'chat-messages' => array('GET', 'GetChatMessages', array('lastTime')),
    'clan-chat-messages' => array('GET', 'GetClanChatMessages', array('lastTime')),
    'enter-chat-message' => array('POST', 'EnterChatMessage', array('message')),
    'enter-clan-chat-message' => array('POST', 'EnterClanChatMessage', array('message')),
    // Mail services
    'mails' => array('GET', 'GetMailList', array()),
    'read-mail' => array('POST', 'ReadMail', array('id')),
    'claim-mail-rewards' => array('POST', 'ClaimMailRewards', array('id')),
    'delete-mail' => array('POST', 'DeleteMail', array('id')),
    'mails-count' => array('GET', 'GetMailsCount', array()),
    // Random store services
    'random-store' => array('GET', 'GetRandomStore', array('id')),
    'purchase-random-store-item' => array('POST', 'PurchaseRandomStoreItem', array('id', 'index')),
    'refresh-random-store' => array('POST', 'RefreshRandomStore', array('id')),
    // Daily reward services
    'all-daily-rewarding' => array('GET', 'GetAllDailyRewardList', array()),
    'daily-rewarding' => array('GET', 'GetDailyRewardList', array('id')),
    'daily-rewarding-claim' => array('POST', 'ClaimDailyReward', array('id')),
    // Player profile
    'unlock-icons' => array('GET', 'GetUnlockIconList', array()),
    'unlock-frames' => array('GET', 'GetUnlockFrameList', array()),
    'unlock-titles' => array('GET', 'GetUnlockTitleList', array()),
    // Clan profile
    'clan-unlock-icons' => array('GET', 'GetClanUnlockIconList', array()),
    'clan-unlock-frames' => array('GET', 'GetClanUnlockFrameList', array()),
    'clan-unlock-titles' => array('GET', 'GetClanUnlockTitleList', array()),
    // Other services
    'formation-characters-and-equipments' => array('GET', 'GetFormationCharactersAndEquipments', array('playerId', 'formationDataId')),
    'arena-formation-characters-and-equipments' => array('GET', 'GetArenaFormationCharactersAndEquipments', array('playerId')),
);

// Other services
$it = new RecursiveDirectoryIterator("./extensions");
foreach(new RecursiveIteratorIterator($it) as $file) {
    if (in_array(strtolower(array_pop(explode('.', $file))), array('php'))) {
        include $file;
    }
}
if (\Base::instance()->get('use_request_query_action')) {
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    $actionName = $_GET['action'];
    if (empty($actionName)) {
        echo ";)";
        return;
    }

    if (!isset($GLOBALS['actions'][$actionName])) {
        echo json_encode(array('error' => 'No action: ', $actionName));
        return;
    }

    $actionMethod = $GLOBALS['actions'][$actionName][0];
    if ($requestMethod !== $actionMethod) {
        echo json_encode(array('error' => 'Wrong method: ', $requestMethod, ' for ', $actionName));
        return;
    }

    $functionName = $GLOBALS['actions'][$actionName][1];
    $functionParams = array();
    if (isset($GLOBALS['actions'][$actionName][2]) && !empty($GLOBALS['actions'][$actionName][2])) {
        $dataSource = $actionMethod === 'GET' ? $_GET : json_decode(urldecode(file_get_contents('php://input')), true);
        $fieldNames = $GLOBALS['actions'][$actionName][2];
        foreach ($fieldNames as $fieldName) {
            $functionParams[] = $dataSource[$fieldName];
        }
    }
    
    try {
        call_user_func_array($functionName, $functionParams);
    } catch (Exception $ex) {
        echo json_encode(array('error' => 'Caught exception: ', $ex->getMessage()));
    }
} else {
    // Services
    $f3->route('GET /', function() {
        echo ";)";
    });
    // Implement services
    foreach ($GLOBALS['actions'] as $actionName => $data) {
        $actionMethod = $data[0];
        $functionName = $data[1];
        $route = "$actionMethod /$actionName";
        $functionParams = array();
        if (isset($data[2]) && !empty($data[2])) {
            $fieldNames = $data[2];
            if ($actionMethod === 'GET') {
                foreach ($fieldNames as $fieldName) {
                    $route .= "/@$fieldName";
                }
            } else {
                $dataSource = json_decode(urldecode($f3->get('BODY')), true);
                foreach ($fieldNames as $fieldName) {
                    $functionParams[] = $dataSource[$fieldName];
                }
            }
        }

        $f3->route($route, function($f3, $params) {
            if ($actionMethod === 'GET') {
                try {
                    call_user_func_array($functionName, $params);
                } catch (Exception $ex) {
                    echo json_encode(array('error' => 'Caught exception: ', $ex->getMessage()));
                }
            } else {
                try {
                    call_user_func_array($functionName, $functionParams);
                } catch (Exception $ex) {
                    echo json_encode(array('error' => 'Caught exception: ', $ex->getMessage()));
                }
            }
        });
    }

    // Run the fatfree instance
    $f3->run();
}
?>