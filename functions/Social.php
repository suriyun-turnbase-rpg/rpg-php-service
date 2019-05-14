<?php
function FriendRequest($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerFriendDb = new PlayerFriend();
    $playerFriend = $playerFriendDb->load(array(
        'playerId = ? AND targetPlayerId = ?',
        $playerId,
        $targetPlayerId
    ));

    $playerFriendRequestDb = new PlayerFriendRequest();
    $playerFriendRequest = $playerFriendRequestDb->load(array(
        'playerId = ? AND targetPlayerId = ?',
        $playerId,
        $targetPlayerId
    ));
    
    if (!$playerFriend && !$playerFriendRequest)
    {
        $newRequest = new PlayerFriendRequest();
        $newRequest->playerId = $playerId;
        $newRequest->targetPlayerId = $targetPlayerId;
        $newRequest->save();
    }
    echo json_encode($output);
}

function FriendAccept($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    // Validate request
    $playerFriendRequestDb = new PlayerFriendRequest();
    if ($playerFriendRequestDb->load(array(
        'playerId = ? AND targetPlayerId = ?',
        $targetPlayerId,
        $playerId
    )))
    {
        // Remove requests
        $playerFriendRequestDb = new PlayerFriendRequest();
        $playerFriendRequestDb->erase(array(
            '(playerId = ? AND targetPlayerId = ?) OR (playerId = ? AND targetPlayerId = ?)',
            $playerId,
            $targetPlayerId,
            $targetPlayerId,
            $playerId
        ));
        // Add friend
        $playerFriendA = new PlayerFriend();
        $playerFriendA->playerId = $playerId;
        $playerFriendA->targetPlayerId = $targetPlayerId;
        $playerFriendA->save();
        // B
        $playerFriendB = new PlayerFriend();
        $playerFriendB->playerId = $targetPlayerId;
        $playerFriendB->targetPlayerId = $playerId;
        $playerFriendB->save();
    }
    echo json_encode($output);
}

function FriendDecline($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    // Validate request
    $playerFriendRequestDb = new PlayerFriendRequest();
    if ($playerFriendRequestDb->load(array(
        'playerId = ? AND targetPlayerId = ?',
        $targetPlayerId,
        $playerId
    )))
    {
        // Remove requests
        $playerFriendRequestDb = new PlayerFriendRequest();
        $playerFriendRequestDb->erase(array(
            '(playerId = ? AND targetPlayerId = ?) OR (playerId = ? AND targetPlayerId = ?)',
            $playerId,
            $targetPlayerId,
            $targetPlayerId,
            $playerId
        ));
    }
    echo json_encode($output);
}

function FriendDelete($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $playerFriendDb = new PlayerFriend();
    $playerFriendDb->erase(array(
        '(playerId = ? AND targetPlayerId = ?) OR (playerId = ? AND targetPlayerId = ?)',
        $playerId,
        $targetPlayerId,
        $targetPlayerId,
        $playerId
    ));
    echo json_encode($output);
}

function FindPlayer($profileName)
{
    
}
?>