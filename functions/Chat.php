<?php
function GetChatMessages($lastTime)
{
    $chatDb = new Chat();
    echo json_encode(array(
        'list' => CursorsToArray(
            $chatDb->find(array(
                'chatTime > ? AND clanId <= ?',
                $lastTime,
                0
            ), array(
                'order' => 'id DESC',
                'LIMIT' => 25
            ))
        )
    ));
}

function GetClanChatMessages($lastTime)
{
    $chatDb = new Chat();
    $player = GetPlayer();
    $clanId = $player->clanId;
    if ($clanId == 0) {
        echo '{"list":[]}';
    } else {
        echo json_encode(array(
            'list' => CursorsToArray(
                $chatDb->find(array(
                    'chatTime > ? AND clanId = ?',
                    $lastTime,
                    $clanId
                ), array(
                    'order' => 'id DESC',
                    'LIMIT' => 25
                ))
            )
        ));
    }
}

function EnterChatMessage($message)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $profileName = $player->profileName;
    $chat = new Chat();
    $chat->playerId = $playerId;
    $chat->profileName = $profileName;
    $chat->clanId = 0;
    $chat->clanName = '';
    $chat->message = $message;
    $chat->chatTime = time();
    $chat->save();
    echo json_encode($output);
}

function EnterClanChatMessage($message)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $profileName = $player->profileName;
    $clanId = $player->clanId;
    $clanDb = new Clan();
    $clan = $clanDb->findone(array('id = ?', $clanId));
    if (!$clan) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clanName = $clan->name;
        $chat = new Chat();
        $chat->playerId = $playerId;
        $chat->profileName = $profileName;
        $chat->clanId = $clanId;
        $chat->clanName = $clanName;
        $chat->message = $message;
        $chat->chatTime = time();
        $chat->save();
    }
    echo json_encode($output);
}
?>