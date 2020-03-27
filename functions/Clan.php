<?php
function CreateClan($clanName)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;

    if (!empty($clanId)) {
        $output['error'] = 'ERROR_JOINED_CLAN';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['currencies']['SOFT_CURRENCY']['id']);
        $hardCurrency = GetCurrency($playerId, $gameData['currencies']['HARD_CURRENCY']['id']);
        $requirementType = $gameData['createClanCurrencyType'];
        $price = $gameData['createClanCurrencyAmount'];
        if ($requirementType == ECreateClanRequirementType::SoftCurrency && $price > $softCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_SOFT_CURRENCY';
        } else if ($requirementType == ECreateClanRequirementType::HardCurrency && $price > $hardCurrency->amount) {
            $output['error'] = 'ERROR_NOT_ENOUGH_HARD_CURRENCY';
        } else {
            switch ($requirementType)
            {
                case ECreateClanRequirementType::SoftCurrency:
                    $softCurrency->amount -= $price;
                    $softCurrency->update();
                    $updateCurrencies[] = $softCurrency;
                    break;
                case ECreateClanRequirementType::HardCurrency:
                    $hardCurrency->amount -= $price;
                    $hardCurrency->update();
                    $updateCurrencies[] = $hardCurrency;
                    break;
            }
            $clan = new Clan();
            $clan->name = $clanName;
            $clan->save();
            $player->clanId = $clan->id;
            $player->clanRole = 2;
            $player->update();

            $output['clan'] = array(
                'id' => $clan->id,
                'name' => $clan->name,
                'owner' => GetClanOwner($playerId, $clan->id)
            );
            $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
        }
    }
    echo json_encode($output);
}

function FindClan($clanName)
{
    $player = GetPlayer();
    $playerId = $player->id;
    $list = array();
    $clanDb = new Clan();
    if (empty($clanName)) {
        $foundClans = $clanDb->find(array(
        ), array('limit' => 25));
    }
    else
    {
        $foundClans = $clanDb->find(array(
            'name = ?',
            $clanName.'%'
        ), array('limit' => 25));
    }
    // Add list
    foreach ($foundClans as $foundClan) {
        $list[] = array(
            'id' => $foundClan->id,
            'name' => $foundClan->name,
            'owner' => GetClanOwner($playerId, $foundClan->id)
        );
    }
    echo json_encode(array('list' => $list));
}

function ClanJoinRequest($joinClanId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    if (!empty($clanId)) {
        $output['error'] = 'ERROR_JOINED_CLAN';
    } else {
        // Delete request to this clan (if found)
        $clanJoinRequest = new ClanJoinRequest();
        $clanJoinRequest->erase(array('playerId = ? AND clanId = ?', $playerId, $joinClanId));
        // Create new request record
        $clanJoinRequest = new ClanJoinRequest();
        $clanJoinRequest->playerId = $playerId;
        $clanJoinRequest->clanId = $joinClanId;
        $clanJoinRequest->save();
    }
    echo json_encode($output);
}

function ClanJoinAccept($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan <= 0 || $clanRole < 1) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clanJoinRequest = new ClanJoinRequest();
        $countRequest = $clanJoinRequest->count(array('playerId = ? AND clanId = ?', $targetPlayerId, $clanId));
        if ($countRequest > 0) {
            // Delete request record
            $clanJoinRequest = new ClanJoinRequest();
            $clanJoinRequest->erase(array('playerId = ?', $targetPlayerId));
            // Update clan ID
            $memberDb = new Player();
            $member = $memberDb->load(array(
                'id = ?',
                $targetPlayerId,
            ));
            if (empty($member->clanId)) {
                $member->clanId = $clanId;
                $member->clanRole = 0;
                $member->update();
            }
        }
    }
    echo json_encode($output);
}

function ClanJoinDecline($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan <= 0 || $clanRole < 1) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clanJoinRequest = new ClanJoinRequest();
        $countRequest = $clanJoinRequest->count(array('playerId = ? AND clanId = ?', $targetPlayerId, $clanId));
        if ($countRequest > 0) {
            // Delete request record
            $clanJoinRequest = new ClanJoinRequest();
            $clanJoinRequest->erase(array('playerId = ? AND clanId = ?', $targetPlayerId, $clanId));
        }
    }
    echo json_encode($output);
}

function ClanMemberDelete($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $memberDb = new Player();
    $member = $memberDb->load(array('id = ?', $targetPlayerId));
    if (!$member || $member->clanId != $clanId || $member->clanRole >= $clanRole) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $member->clanId = 0;
        $member->clanRole = 0;
        $member->update();
    }
    echo json_encode($output);
}

function ClanJoinRequestDelete($clanId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    // Delete request record
    $clanJoinRequest = new ClanJoinRequest();
    $clanJoinRequest->erase(array('playerId = ? AND clanId = ?', $playerId, $clanId));
    echo json_encode($output);
}

function ClanMembers()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $list = array();
    if (!empty($clanId)) {
        $playerDb = new Player();
        $foundPlayers = $playerDb->find(array('clanId = ?', $clanId));
        // Add list
        foreach ($foundPlayers as $foundPlayer) {
            $socialPlayer = GetSocialPlayer($playerId, $foundPlayer->id);
            if ($socialPlayer) {
                $list[] = $socialPlayer;
            }
        }
    }
    echo json_encode(array('list' => $list));
}

function ClanOwnerTransfer($targetPlayerId)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan <= 0 || $clanRole < 2) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $memberDb = new Player();
        $member = $memberDb->load(array('id = ? AND clanId = ?', $targetPlayerId, $clanId));
        if ($member)
        {
            $player->clanRole = 1;
            $player->update();
            $member->clanRole = 2;
            $member->update();
        }
    }
    echo json_encode($output);
}

function ClanTerminate()
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan <= 0 || $clanRole < 2) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $clanDb = new Clan();
        $clanDb->erase(array('id = ?', $clanId));
        $db = \Base::instance()->get('DB');
        $prefix = \Base::instance()->get('db_prefix');
        $db->exec('UPDATE ' . $prefix . 'player SET clanId=0 AND clanRole=0 WHERE clanId="' . $clanId . '"');
    }
    echo json_encode($output);
}

function GetClan()
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $clanDb = new Clan();
    $clan = $clanDb->load(array('id = ?', $clanId));
    if ($clan) {
        $output['clan'] = array(
            'id' => $clan->id,
            'name' => $clan->name,
            'owner' => GetClanOwner($playerId, $clanId)
        );
    }
    echo json_encode($output);
}

function ClanJoinRequests()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $list = array();
    if (!empty($clanId)) {
        $joinRequestDb = new ClanJoinRequest();
        $foundRequests = $joinRequestDb->find(array('clanId = ?', $clanId));
        // Add list
        foreach ($foundRequests as $foundRequest) {
            $socialPlayer = GetSocialPlayer($playerId, $foundRequest->playerId);
            if ($socialPlayer) {
                $list[] = $socialPlayer;
            }
        }
    }
    echo json_encode(array('list' => $list));
}

function ClanJoinPendingRequests()
{
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $list = array();
    if (empty($clanId)) {
        $joinRequestDb = new ClanJoinRequest();
        $foundRequests = $joinRequestDb->find(array('playerId = ?', $playerId));
        // Add list
        foreach ($foundRequests as $foundRequest) {
            $clanDb = new Clan();
            $foundClan = $clanDb->load(array('id = ?', $foundRequest->clanId));
            if ($foundClan) {
                $list[] = array(
                    'id' => $foundClan->id,
                    'name' => $foundClan->name,
                    'owner' => GetClanOwner($playerId, $foundClan->id)
                );
            }
        }
    }
    echo json_encode(array('list' => $list));
}

function ClanExit()
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan > 0 && $clanRole == 2) {
        $output['error'] = 'ERROR_CLAN_OWNER_CANNOT_EXIT';
    } else {
        $player->clanId = 0;
        $player->clanRole = 0;
        $player->update();
    }
    echo json_encode($output);
}

function ClanSetRole($targetPlayerId, $targetClanRole)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $clanId = $player->clanId;
    $clanRole = $player->clanRole;
    $clanDb = new Clan();
    $countClan = $clanDb->count(array('id = ?', $clanId));
    if ($countClan <= 0 || $clanRole < 2 || $targetClanRole >= 2) {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    } else {
        $memberDb = new Player();
        $member = $memberDb->load(array('id = ? AND clanId = ?', $targetPlayerId, $clanId));
        if ($member)
        {
            $member->clanRole = $targetClanRole;
            $member->update();
        }
    }
    echo json_encode($output);
}
?>