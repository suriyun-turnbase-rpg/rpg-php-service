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
    'login' => function($params, $postBody) {
        Login($postBody['username'], $postBody['password']);
    },
    'register' => function($params, $postBody) {
        Register($postBody['username'], $postBody['password']);
    },
    'guest-login' => function($params, $postBody) {
        GuestLogin($postBody['deviceId']);
    },
    'validate-login-token' => function($params, $postBody) {
        ValidateLoginToken($postBody['refreshToken']);
    },
    'set-profile-name' => function($params, $postBody) {
        SetProfileName($postBody['profileName']);
    },
    'achievements' => function($params, $postBody) {
        GetAchievementList();
    },
    'items' => function($params, $postBody) {
        GetItemList();
    },
    'currencies' => function($params, $postBody) {
        GetCurrencyList();
    },
    'staminas' => function($params, $postBody) {
        GetStaminaList();
    },
    'formations' => function($params, $postBody) {
        GetFormationList();
    },
    'unlock-items' => function($params, $postBody) {
        GetUnlockItemList();
    },
    'clear-stages' => function($params, $postBody) {
        GetClearStageList();
    },
    'helpers' => function($params, $postBody) {
        GetHelperList();
    },
    'friends' => function($params, $postBody) {
        GetFriendList();
    },
    'friend-requests' => function($params, $postBody) {
        GetFriendRequestList();
    },
    'pending-requests' => function($params, $postBody) {
        GetPendingRequestList();
    },
    'opponents' => function($params, $postBody) {
        GetOpponentList();
    },
    'raid-events' => function($params, $postBody) {
        GetRaidEventList();
    },
    'clan-events' => function($params, $postBody) {
        GetClanEventList();
    },
    'service-time' => function($params, $postBody) {
        GetServiceTime();
    },
    'levelup-item' => function($params, $postBody) {
        LevelUpItem($postBody['itemId'], $postBody['materials']);
    },
    'evolve-item' => function($params, $postBody) {
        EvolveItem($postBody['itemId'], $postBody['materials']);
    },
    'sell-items' => function($params, $postBody) {
        SellItems($postBody['items']);
    },
    'equip-item' => function($params, $postBody) {
        EquipItem($postBody['characterId'], $postBody['equipmentId'], $postBody['equipPosition']);
    },
    'unequip-item' => function($params, $postBody) {
        UnEquipItem($postBody['equipmentId']);
    },
    'craft-item' => function($params, $postBody) {
        CraftItem($postBody['itemCraftId'], $postBody['materials']);
    },
    'available-lootboxes' => function($params, $postBody) {
        GetAvailableLootBoxList();
    },
    'available-iap-packages' => function($params, $postBody) {
        GetAvailableIapPackageList();
    },
    'available-ingame-packages' => function($params, $postBody) {
        GetAvailableInGamePackageList();
    },
    'open-lootbox' => function($params, $postBody) {
        OpenLootBox($postBody['lootBoxDataId'], $postBody['packIndex']);
    },
    'open-ingame-package' => function($params, $postBody) {
        OpenInGamePackage($postBody['inGamePackageDataId']);
    },
    'convert-hard-currency' => function($params, $postBody) {
        ConvertHardCurrency($postBody['requireHardCurrency']);
    },
    'refill-stamina' => function($params, $postBody) {
        RefillStamina($postBody['staminaDataId']);
    },
    'refill-stamina-info' => function($params, $postBody) {
        GetRefillStaminaInfo($params['staminaDataId']);
    },
    'available-stages' => function($params, $postBody) {
        GetAvailableStageList();
    },
    'friend-request' => function($params, $postBody) {
        FriendRequest($postBody['targetPlayerId']);
    },
    'friend-accept' => function($params, $postBody) {
        FriendAccept($postBody['targetPlayerId']);
    },
    'friend-decline' => function($params, $postBody) {
        FriendDecline($postBody['targetPlayerId']);
    },
    'friend-delete' => function($params, $postBody) {
        FriendDelete($postBody['targetPlayerId']);
    },
    'friend-request-delete' => function($params, $postBody) {
        FriendRequestDelete($postBody['targetPlayerId']);
    },
    'find-player' => function($params, $postBody) {
        FindPlayer($postBody['profileName']);
    },
    'start-stage' => function($params, $postBody) {
        StartStage($postBody['stageDataId'], $postBody['helperPlayerId']);
    },
    'finish-stage' => function($params, $postBody) {
        FinishStage($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'revive-characters' => function($params, $postBody) {
        ReviveCharacters();
    },
    'select-formation' => function($params, $postBody) {
        SelectFormation($postBody['formationName'], $postBody['formationType']);
    },
    'set-formation' => function($params, $postBody) {
        SetFormation($postBody['characterId'], $postBody['formationName'], $postBody['position']);
    },
    'start-duel' => function($params, $postBody) {
        StartDuel($postBody['targetPlayerId']);
    },
    'finish-duel' => function($params, $postBody) {
        FinishDuel($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'start-raid-boss-battle' => function($params, $postBody) {
        StartRaidBossBattle($postBody['eventId']);
    },
    'finish-raid-boss-battle' => function($params, $postBody) {
        FinishRaidBossBattle($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'start-clan-boss-battle' => function($params, $postBody) {
        StartClanBossBattle($postBody['eventId']);
    },
    'finish-clan-boss-battle' => function($params, $postBody) {
        FinishClanBossBattle($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'ios-buy-goods' => function($params, $postBody) {
        IOSBuyGoods($postBody['iapPackageDataId'], $postBody['receipt']);
    },
    'google-play-buy-goods' => function($params, $postBody) {
        AndroidBuyGoods($postBody['iapPackageDataId'], $postBody['data'], $postBody['signature']);
    },
    'earn-achievement-reward' => function($params, $postBody) {
        EarnAchievementReward($postBody['achievementId']);
    },
    'create-clan' => function($params, $postBody) {
        CreateClan($postBody['clanName']);
    },
    'find-clan' => function($params, $postBody) {
        FindClan($postBody['clanName']);
    },
    'clan-join-request' => function($params, $postBody) {
        ClanJoinRequest($postBody['clanId']);
    },
    'clan-join-accept' => function($params, $postBody) {
        ClanJoinAccept($postBody['targetPlayerId']);
    },
    'clan-join-decline' => function($params, $postBody) {
        ClanJoinDecline($postBody['targetPlayerId']);
    },
    'clan-member-delete' => function($params, $postBody) {
        ClanMemberDelete($postBody['targetPlayerId']);
    },
    'clan-join-request-delete' => function($params, $postBody) {
        ClanJoinRequestDelete($postBody['clanId']);
    },
    'clan-members' => function($params, $postBody) {
        ClanMembers();
    },
    'clan-owner-transfer' => function($params, $postBody) {
        ClanOwnerTransfer($postBody['targetPlayerId']);
    },
    'clan-terminate' => function($params, $postBody) {
        ClanTerminate();
    },
    'clan' => function($params, $postBody) {
        GetClan();
    },
    'clan-join-requests' => function($params, $postBody) {
        ClanJoinRequests();
    },
    'clan-join-pending-requests' => function($params, $postBody) {
        ClanJoinPendingRequests();
    },
    'clan-exit' => function($params, $postBody) {
        ClanExit();
    },
    'clan-set-role' => function($params, $postBody) {
        ClanSetRole($postBody['targetPlayerId'], $postBody['clanRole']);
    },
    'clan-checkin' => function($params, $postBody) {
        ClanCheckin();
    },
    'clan-checkin-status' => function($params, $postBody) {
        GetClanCheckinStatus();
    },
    'clan-donation' => function($params, $postBody) {
        ClanDonation($postBody['clanDonationDataId']);
    },
    'clan-donation-status' => function($params, $postBody) {
        GetClanDonationStatus();
    },
    'chat-messages' => function($params, $postBody) {
        GetChatMessages($params['lastTime']);
    },
    'clan-chat-messages' => function($params, $postBody) {
        GetClanChatMessages($params['lastTime']);
    },
    'enter-chat-message' => function($params, $postBody) {
        EnterChatMessage($postBody['message']);
    },
    'enter-clan-chat-message' => function($params, $postBody) {
        EnterClanChatMessage($postBody['message']);
    },
    'start-raid-boss-battle' => function($params, $postBody) {
        StartRaidBossBattle($postBody['eventId']);
    },
    'finish-raid-boss-battle' => function($params, $postBody) {
        FinishRaidBossBattle($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'start-clan-boss-battle' => function($params, $postBody) {
        StartClanBossBattle($postBody['eventId']);
    },
    'finish-clan-boss-battle' => function($params, $postBody) {
        FinishClanBossBattle($postBody['session'], $postBody['battleResult'], $postBody['totalDamage'], $postBody['deadCharacters']);
    },
    'mails' => function($params, $postBody) {
        GetMailList();
    },
    'read-mail' => function($params, $postBody) {
        ReadMail($postBody['id']);
    },
    'claim-mail-rewards' => function($params, $postBody) {
        ClaimMailRewards($postBody['id']);
    },
    'delete-mail' => function($params, $postBody) {
        DeleteMail($postBody['id']);
    },
    'mails-count' => function($params, $postBody) {
        GetMailsCount();
    },
    'random-store' => function($params, $postBody) {
        GetRandomStore($params['id']);
    },
    'purchase-random-store-item' => function($params, $postBody) {
        PurchaseRandomStoreItem($postBody['id'], $postBody['index']);
    },
    'refresh-random-store' => function($params, $postBody) {
        RefreshRandomStore($postBody['id']);
    },
    'daily-rewarding' => function($params, $postBody) {
        GetDailyRewardList($params['id']);
    },
    'daily-rewarding-claim' => function($params, $postBody) {
        ClaimDailyReward($postBody['id']);
    },
    'formation-characters-and-equipments' => function($params, $postBody) {
        echo json_encode(GetFormationCharactersAndEquipments($params['playerId'], $params['formationDataId']));
    },
    'arena-formation-characters-and-equipments' => function($params, $postBody) {
        $playerDb = new Player();
        $player = $playerDb->findone(array(
            'id = ?',
            $params['playerId']
        ));
        echo json_encode(GetFormationCharactersAndEquipments($params['playerId'], $player->selectedArenaFormation));
    },
);
// API actions functions
function DoGetAction($actionName, $params)
{
    call_user_func($GLOBALS['actions'][$actionName], $params, array());
}

function DoPostAction($actionName, $f3, $params)
{
    $postBody = json_decode(urldecode($f3->get('BODY')), true);
    call_user_func($GLOBALS['actions'][$actionName], $params, $postBody);
}
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
    } else if ($requestMethod === 'GET') {
        call_user_func($GLOBALS['actions'][$actionName], $_GET, array());
    } else if ($requestMethod === 'POST') {
        $postBody = json_decode(urldecode(file_get_contents('php://input')), true);
        call_user_func($GLOBALS['actions'][$actionName], $_GET, $postBody);
    }
} else {
    // Services
    $f3->route('GET /', function() {
        echo ";)";
    });
    // Auth services
    $f3->route('POST /login', function($f3, $params) {
        DoPostAction('login', $f3, $params);
    });
    $f3->route('POST /register', function($f3, $params) {
        DoPostAction('register', $f3, $params);
    });
    $f3->route('POST /guest-login', function($f3, $params) {
        DoPostAction('guest-login', $f3, $params);
    });
    $f3->route('POST /validate-login-token', function($f3, $params) {
        DoPostAction('validate-login-token', $f3, $params);
    });
    $f3->route('POST /set-profile-name', function($f3, $params) {
        DoPostAction('set-profile-name', $f3, $params);
    });
    // Listing services
    $f3->route('GET /achievements', function($f3, $params) {
        DoGetAction('achievements', $params);
    });
    $f3->route('GET /items', function($f3, $params) {
        DoGetAction('items', $params);
    });
    $f3->route('GET /currencies', function($f3, $params) {
        DoGetAction('currencies', $params);
    });
    $f3->route('GET /staminas', function($f3, $params) {
        DoGetAction('staminas', $params);
    });
    $f3->route('GET /formations', function($f3, $params) {
        DoGetAction('formations', $params);
    });
    $f3->route('GET /unlock-items', function($f3, $params) {
        DoGetAction('unlock-items', $params);
    });
    $f3->route('GET /clear-stages', function($f3, $params) {
        DoGetAction('clear-stages', $params);
    });
    $f3->route('GET /helpers', function($f3, $params) {
        DoGetAction('helpers', $params);
    });
    $f3->route('GET /friends', function($f3, $params) {
        DoGetAction('friends', $params);
    });
    $f3->route('GET /friend-requests', function($f3, $params) {
        DoGetAction('friend-requests', $params);
    });
    $f3->route('GET /pending-requests', function($f3, $params) {
        DoGetAction('pending-requests', $params);
    });
    $f3->route('GET /opponents', function($f3, $params) {
        DoGetAction('opponents', $params);
    });
    $f3->route('GET /raid-events', function($f3, $params) {
        DoGetAction('raid-events', $params);
    });
    $f3->route('GET /clan-events', function($f3, $params) {
        DoGetAction('clan-events', $params);
    });
    $f3->route('GET /service-time', function($f3, $params) {
        DoGetAction('service-time', $params);
    });
    // Item services
    $f3->route('POST /levelup-item', function($f3, $params) {
        DoPostAction('levelup-item', $f3, $params);
    });
    $f3->route('POST /evolve-item', function($f3, $params) {
        DoPostAction('evolve-item', $f3, $params);
    });
    $f3->route('POST /sell-items', function($f3, $params) {
        DoPostAction('sell-items', $f3, $params);
    });
    $f3->route('POST /equip-item', function($f3, $params) {
        DoPostAction('equip-item', $f3, $params);
    });
    $f3->route('POST /unequip-item', function($f3, $params) {
        DoPostAction('unequip-item', $f3, $params);
    });
    $f3->route('POST /craft-item', function($f3, $params) {
        DoPostAction('craft-item', $f3, $params);
    });
    $f3->route('GET /available-lootboxes', function($f3, $params) {
        DoGetAction('available-lootboxes', $params);
    });
    $f3->route('GET /available-iap-packages', function($f3, $params) {
        DoGetAction('available-iap-packages', $params);
    });
    $f3->route('GET /available-ingame-packages', function($f3, $params) {
        DoGetAction('available-ingame-packages', $params);
    });
    $f3->route('POST /open-lootbox', function($f3, $params) {
        DoPostAction('open-lootbox', $f3, $params);
    });
    $f3->route('POST /open-ingame-package', function($f3, $params) {
        DoPostAction('open-ingame-package', $f3, $params);
    });
    $f3->route('POST /convert-hard-currency', function($f3, $params) {
        DoPostAction('convert-hard-currency', $f3, $params);
    });
    $f3->route('POST /refill-stamina', function($f3, $params) {
        DoPostAction('refill-stamina', $f3, $params);
    });
    $f3->route('GET /refill-stamina-info/@staminaDataId', function($f3, $params) {
        DoGetAction('refill-stamina-info', $params);
    });
    $f3->route('GET /available-stages', function($f3, $params) {
        DoGetAction('available-stages', $params);
    });
    // Social services
    $f3->route('POST /friend-request', function($f3, $params) {
        DoPostAction('friend-request', $f3, $params);
    });
    $f3->route('POST /friend-accept', function($f3, $params) {
        DoPostAction('friend-accept', $f3, $params);
    });
    $f3->route('POST /friend-decline', function($f3, $params) {
        DoPostAction('friend-decline', $f3, $params);
    });
    $f3->route('POST /friend-delete', function($f3, $params) {
        DoPostAction('friend-delete', $f3, $params);
    });
    $f3->route('POST /friend-request-delete', function($f3, $params) {
        DoPostAction('friend-request-delete', $f3, $params);
    });
    $f3->route('POST /find-player', function($f3, $params) {
        DoPostAction('find-player', $f3, $params);
    });
    // Battle services
    $f3->route('POST /start-stage', function($f3, $params) {
        DoPostAction('start-stage', $f3, $params);
    });
    $f3->route('POST /finish-stage', function($f3, $params) {
        DoPostAction('finish-stage', $f3, $params);
    });
    $f3->route('POST /revive-characters', function($f3, $params) {
        DoPostAction('revive-characters', $f3, $params);
    });
    $f3->route('POST /select-formation', function($f3, $params) {
        DoPostAction('select-formation', $f3, $params);
    });
    $f3->route('POST /set-formation', function($f3, $params) {
        DoPostAction('set-formation', $f3, $params);
    });
    // Arena services
    $f3->route('POST /start-duel', function($f3, $params) {
        DoPostAction('start-duel', $f3, $params);
    });
    $f3->route('POST /finish-duel', function($f3, $params) {
        DoPostAction('finish-duel', $f3, $params);
    });
    // Raid boss services
    $f3->route('POST /start-raid-boss-battle', function($f3, $params) {
        DoPostAction('start-raid-boss-battle', $f3, $params);
    });
    $f3->route('POST /finish-raid-boss-battle', function($f3, $params) {
        DoPostAction('finish-raid-boss-battle', $f3, $params);
    });
    // Clan boss services
    $f3->route('POST /start-clan-boss-battle', function($f3, $params) {
        DoPostAction('start-clan-boss-battle', $f3, $params);
    });
    $f3->route('POST /finish-clan-boss-battle', function($f3, $params) {
        DoPostAction('finish-clan-boss-battle', $f3, $params);
    });
    // Billing services
    $f3->route('POST /ios-buy-goods', function($f3, $params) {
        DoPostAction('ios-buy-goods', $f3, $params);
    });
    $f3->route('POST /google-play-buy-goods', function($f3, $params) {
        DoPostAction('google-play-buy-goods', $f3, $params);
    });
    // Achievement services
    $f3->route('POST /earn-achievement-reward', function($f3, $params) {
        DoPostAction('earn-achievement-reward', $f3, $params);
    });
    // Clan services
    $f3->route('POST /create-clan', function($f3, $params) {
        DoPostAction('create-clan', $f3, $params);
    });
    $f3->route('POST /find-clan', function($f3, $params) {
        DoPostAction('find-clan', $f3, $params);
    });
    $f3->route('POST /clan-join-request', function($f3, $params) {
        DoPostAction('clan-join-request', $f3, $params);
    });
    $f3->route('POST /clan-join-accept', function($f3, $params) {
        DoPostAction('clan-join-accept', $f3, $params);
    });
    $f3->route('POST /clan-join-decline', function($f3, $params) {
        DoPostAction('clan-join-decline', $f3, $params);
    });
    $f3->route('POST /clan-member-delete', function($f3, $params) {
        DoPostAction('clan-member-delete', $f3, $params);
    });
    $f3->route('POST /clan-join-request-delete', function($f3, $params) {
        DoPostAction('clan-join-request-delete', $f3, $params);
    });
    $f3->route('GET /clan-members', function($f3, $params) {
        DoGetAction('clan-members', $params);
    });
    $f3->route('POST /clan-owner-transfer', function($f3, $params) {
        DoPostAction('clan-owner-transfer', $f3, $params);
    });
    $f3->route('POST /clan-terminate', function($f3, $params) {
        DoPostAction('clan-terminate', $f3, $params);
    });
    $f3->route('GET /clan', function($f3, $params) {
        DoGetAction('clan', $params);
    });
    $f3->route('GET /clan-join-requests', function($f3, $params) {
        DoGetAction('clan-join-requests', $params);
    });
    $f3->route('GET /clan-join-pending-requests', function($f3, $params) {
        DoGetAction('clan-join-pending-requests', $params);
    });
    $f3->route('POST /clan-exit', function($f3, $params) {
        DoPostAction('clan-exit', $f3, $params);
    });
    $f3->route('POST /clan-set-role', function($f3, $params) {
        DoPostAction('clan-set-role', $f3, $params);
    });
    $f3->route('POST /clan-checkin', function($f3, $params) {
        DoPostAction('clan-checkin', $f3, $params);
    });
    $f3->route('GET /clan-checkin-status', function($f3, $params) {
        DoGetAction('clan-checkin-status', $params);
    });
    $f3->route('POST /clan-donation', function($f3, $params) {
        DoPostAction('clan-donation', $f3, $params);
    });
    $f3->route('GET /clan-donation-status', function($f3, $params) {
        DoGetAction('clan-donation-status', $params);
    });
    // Chat services
    $f3->route('GET /chat-messages/@lastTime', function($f3, $params) {
        DoGetAction('chat-messages', $params);
    });
    $f3->route('GET /clan-chat-messages/@lastTime', function($f3, $params) {
        DoGetAction('clan-chat-messages', $params);
    });
    $f3->route('POST /enter-chat-message', function($f3, $params) {
        DoPostAction('enter-chat-message', $f3, $params);
    });
    $f3->route('POST /enter-clan-chat-message', function($f3, $params) {
        DoPostAction('enter-clan-chat-message', $f3, $params);
    });
    // Mail services
    $f3->route('GET /mails', function($f3, $params) {
        DoGetAction('mails', $params);
    });
    $f3->route('POST /read-mail', function($f3, $params) {
        DoPostAction('read-mail', $f3, $params);
    });
    $f3->route('POST /claim-mail-rewards', function($f3, $params) {
        DoPostAction('claim-mail-rewards', $f3, $params);
    });
    $f3->route('POST /delete-mail', function($f3, $params) {
        DoPostAction('delete-mail', $f3, $params);
    });
    $f3->route('GET /mails-count', function($f3, $params) {
        DoGetAction('mails-count', $params);
    });
    // Random store services
    $f3->route('GET /random-store/@id', function($f3, $params) {
        DoGetAction('random-store', $params);
    });
    $f3->route('POST /purchase-random-store-item', function($f3, $params) {
        DoPostAction('purchase-random-store-item', $f3, $params);
    });
    $f3->route('POST /refresh-random-store', function($f3, $params) {
        DoPostAction('refresh-random-store', $f3, $params);
    });
    // Daily reward services
    $f3->route('GET /daily-rewarding/@id', function ($f3, $params) {
        DoGetAction('daily-rewarding', $params);
    });
    $f3->route('POST /daily-rewarding-claim', function ($f3, $params) {
        DoGetAction('daily-rewarding-claim', $f3, $params);
    });
    // Other services
    $f3->route('GET /formation-characters-and-equipments/@playerId/@formationDataId', function($f3, $params) {
        DoGetAction('formation-characters-and-equipments', $params);
    });
    $f3->route('GET /arena-formation-characters-and-equipments/@playerId', function($f3, $params) {
        DoGetAction('arena-formation-characters-and-equipments', $params);
    });
    $f3->run();
}
?>