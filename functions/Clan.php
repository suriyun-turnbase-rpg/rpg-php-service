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
    } else if (empty($clanName)) {
        $output['error'] = 'ERROR_EMPTY_CLAN_NAME';
    } else {
        $softCurrency = GetCurrency($playerId, $gameData['softCurrencyId']);
        $hardCurrency = GetCurrency($playerId, $gameData['hardCurrencyId']);
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
            '1'
        ), array('limit' => 25));
    }
    else
    {
        $foundClans = $clanDb->find(array(
            'name LIKE ?',
            '%'.$clanName.'%'
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
            $member = $memberDb->findone(array(
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
    $member = $memberDb->findone(array('id = ?', $targetPlayerId));
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
        $member = $memberDb->findone(array('id = ? AND clanId = ?', $targetPlayerId, $clanId));
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
        $db->exec('UPDATE ' . $prefix . 'player SET clanId=0, clanRole=0 WHERE clanId="' . $clanId . '"');
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
    $clan = $clanDb->findone(array('id = ?', $clanId));
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
            $foundClan = $clanDb->findone(array('id = ?', $foundRequest->clanId));
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
        $member = $memberDb->findone(array('id = ? AND clanId = ?', $targetPlayerId, $clanId));
        if ($member)
        {
            $member->clanRole = $targetClanRole;
            $member->update();
        }
    }
    echo json_encode($output);
}

function HasClanCheckin()
{
    $output = array('alreadyCheckin' => false);
    $player = GetPlayer();
    $playerId = $player->id;
    $checkInDate = strtotime(date('Y-m-d'));
    $clanCheckinDb = new ClanCheckin();
    $clanCheckin = $clanCheckinDb->findone(array(
        'playerId = ? AND checkInDate = ?',
        $playerId,
        $checkInDate));
    if ($clanCheckin) {
        $output['alreadyCheckin'] = true;
    }
    echo json_encode($output);
}

function ClanCheckin()
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $checkInDate = strtotime(date('Y-m-d'));
    $clanDb = new Clan();
    $clanCheckinDb = new ClanCheckin();
    if (!($clan = $clanDb->findone(array('id = ?', $clanId))))
    {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    }
    else if (($clanCheckin = $clanCheckinDb->findone(array(
        'playerId = ? AND checkInDate = ?',
        $playerId,
        $checkInDate))))
    {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    }
    else
    {
        $clanCheckin = new ClanCheckin();
        $clanCheckin->playerId = $playerId;
        $clanCheckin->checkInDate = $checkInDate;
        $clanCheckin->clanId = $clanId;
        $clanCheckin->save();
    }
    echo json_encode($output);
}

function HasClanDonation()
{
    $output = array('alreadyDonation' => false);
    $player = GetPlayer();
    $playerId = $player->id;
    $checkInDate = strtotime(date('Y-m-d'));
    $clanDonationDb = new ClanDonation();
    $clanDonation = $clanDonationDb->findone(array(
        'playerId = ? AND checkInDate = ?',
        $playerId,
        $checkInDate));
    if ($clanDonation) {
        $output['alreadyDonation'] = true;
    }
    echo json_encode($output);
}

function ClanDonation($clanDonationDataId)
{
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $clanId = $player->clanId;
    $checkInDate = strtotime(date('Y-m-d'));
    $clanDb = new Clan();
    $clanDonationDb = new ClanDonation();
    $clanDonationData = $gameData['clanDonations'][$clanDonationDataId];
    if (!$clanDonationData)
    {
        $output['error'] = 'ERROR_INVALID_CLAN_DONATION_DATA';
    }
    else if (!($clan = $clanDb->findone(array('id = ?', $clanId))))
    {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    }
    else if (($clanDonation = $clanDonationDb->findone(array(
        'playerId = ? AND checkInDate = ?',
        $playerId,
        $checkInDate))))
    {
        $output['error'] = 'ERROR_NOT_HAVE_PERMISSION';
    }
    else
    {
        $requireCurrencyId = $clanDonationData['requireCurrencyId'];
        $requireCurrencyAmount = $clanDonationData['requireCurrencyAmount'];
        $rewardClanExp = $clanDonationData['rewardClanExp'];
        $currency = GetCurrency($playerId, $clanDonation[$requireCurrencyId]);
        if ($requireCurrencyAmount > $currency->amount)
        {
            $output['error'] = 'ERROR_NOT_ENOUGH_CURRENCY';
        }
        else
        {
            $currency->amount -= $requireCurrencyAmount;
            $clan->exp += $rewardClanExp;
            $clan->update();
            $clanDonation = new ClanDonation();
            $clanDonation->playerId = $playerId;
            $clanDonation->checkInDate = $checkInDate;
            $clanDonation->clanId = $clanId;
            $clanDonation->dataId = $dataId;
            $clanDonation->save();
            $output['clan'] = CursorToArray($clan);
        }
    }
    echo json_encode($output);
}
?>