<?php
function GetChatMessages($lastTime)
{
    $chatDb = new Chat();
    echo json_encode(array(
        'list' => CursorsToArray(
            $chatDb->find(array(
                'createdAt > ?',
                $lastTime
            ), array(
                'order' => 'id DESC',
                'LIMIT' => 25
            ))
        )
    ));
}

function EnterChatMessage($targetClanId, $message)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $profileName = $player->profileName;
    $clanId = $player->clanId;
    $clanName = '';
    if (!empty($targetClanId)) {
        $clanDb = new Clan();
        $clan = $clanDb->findone(array('id = ?', $clanId));
        if (!$clan || $targetClanId != $clanId) {
            $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
        } else {
            $clanName = $clan->name;
            $chat = new Chat();
            $chat->playerId = $playerId;
            $chat->profileName = $profileName;
            $chat->clanId = $clanId;
            $chat->clanName = $clanName;
            $chat->message = $message;
            $chat->save();
        }
    } else {
        $chat = new Chat();
        $chat->playerId = $playerId;
        $chat->profileName = $profileName;
        $chat->clanId = $clanId;
        $chat->clanName = $clanName;
        $chat->message = $message;
        $chat->save();
    }
    echo json_encode($output);
}
?>