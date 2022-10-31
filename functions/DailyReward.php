<?php
function GetDailyRewardCycleStart($mode) {
    return $mode == EDailyRewardMode::Weekly ? GetStartOfWeek() : GetStartOfMonth();
}

function GetDailyRewardCycleEnd($mode) {
    return $mode == EDailyRewardMode::Weekly ? GetEndOfWeek() : GetEndOfMonth();
}

function IsAnyDailyRewardClaimedWithinDate($entries, $currentDate, $testDate) {
    $startOfCurrentDate = strtotime("today", $currentDate);
    $endOfCurrentDate = strtotime("tomorrow", $startOfCurrentDate) - 1 /* -1 second so the time will be 23:59:59 */;
    $count = count($entries);
    for ($j = 0; $j < $count; $j++) {
        $createdAt = strtotime($entries[$j]->createdAt);
        if ($createdAt >= $startOfCurrentDate &&
            $createdAt <= $endOfCurrentDate &&
            $createdAt >= $testDate &&
            $createdAt <= $testDate + (60 * 60 * 24) /* +1 day */)
        {
            return true;
        }
    }
    return false;
}

function GetClaimableDailyRewards($currentDate, $cycleStart, $cycleEnd, $rewards, $consecutive, $playerId, $dailyRewardId) {
    $startOfCurrentDate = strtotime("today", $currentDate);
    $rewardGiven = new DailyRewardGiven();
    $entries = $rewardGiven->find(array(
        'playerId = ? AND dailyRewardId = ? AND createdAt >= ? AND createdAt <= ?',
        $playerId,
        $dailyRewardId,
        $cycleStart,
        $cycleEnd,
    ), array(
        'order' => 'createdAt DESC',
    ));
    $count = count($entries);
    $result = array();
    $claimableDate = $cycleStart;
    $foundClaimableEntry = false;
    $countRewards = count($rewards);
    for ($i = 0; $i < $countRewards; $i++) {
        $reward = $rewards[$i];
        $isClaimed = false;
        if (!$consecutive) {
            $isClaimed = IsAnyDailyRewardClaimedWithinDate($entries, $currentDate, $claimableDate);
        } else {
            $isClaimed = $i < $count;
        }
        $canClaim = false;
        if (!$isClaimed) {
            if ($count > 0) {
                $canClaim = strtotime($entries[$count - 1]->createdAt) < $startOfCurrentDate;
            } else {
                $canClaim = true;
            }
            if (!$consecutive) {
                $canClaim = $canClaim && $currentDate >= $claimableDate;
                $canClaim = $canClaim && $currentDate <= $claimableDate + (60 * 60 * 24) /* +1 day */;
            } else {
                $canClaim = $canClaim && !$foundClaimableEntry;
            }
            if (!$foundClaimableEntry) {
                $foundClaimableEntry = $canClaim;
            }
        }
        $result[] = array(
            'isClaimed' => $isClaimed,
            'canClaim' => $canClaim,
            'reward' => $reward,
        );
        $claimableDate = $claimableDate + (60 * 60 * 24) /* +1 day */;
    }
    return $result;
}

function GetAllDailyRewardList() {
    $gameData = \Base::instance()->get('GameData');
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $currentDate = GetCurrentDate();
    $allDailyRewards = $gameData['dailyRewards'];
    $list = array();
    foreach ($allDailyRewards as $dailyRewardId => $dailyRewards) {
        $cycleStart = GetDailyRewardCycleStart($dailyRewards['mode']);
        $cycleEnd = GetDailyRewardCycleEnd($dailyRewards['mode']);
        $rewards = $dailyRewards['rewards'];
        $consecutive = $dailyRewards['consecutive'];
        // Get reward list and earn state
        $claimableRewards = GetClaimableDailyRewards($currentDate, $cycleStart, $cycleEnd, $rewards, $consecutive, $playerId, $dailyRewardId);
        $entry = array();
        $entry['id'] = $dailyRewardId;
        $entry['rewards'] = $claimableRewards;
        $entry['currentDate'] = $currentDate;
        $entry['cycleStart'] = $cycleStart;
        $entry['cycleEnd'] = $cycleEnd;
        $list[] = $entry;
    }
    $output['dailyRewards'] = $list;
    echo json_encode($output);
}

function GetDailyRewardList($dailyRewardId) {
    $gameData = \Base::instance()->get('GameData');
    $dailyRewards = $gameData['dailyRewards'][$dailyRewardId];
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $currentDate = GetCurrentDate();
    $cycleStart = GetDailyRewardCycleStart($dailyRewards['mode']);
    $cycleEnd = GetDailyRewardCycleEnd($dailyRewards['mode']);
    $rewards = $dailyRewards['rewards'];
    $consecutive = $dailyRewards['consecutive'];
    // Get reward list and earn state
    $claimableRewards = GetClaimableDailyRewards($currentDate, $cycleStart, $cycleEnd, $rewards, $consecutive, $playerId, $dailyRewardId);
    $output['id'] = $dailyRewardId;
    $output['rewards'] = $claimableRewards;
    $output['currentDate'] = $currentDate;
    $output['cycleStart'] = $cycleStart;
    $output['cycleEnd'] = $cycleEnd;
    echo json_encode($output);
}

function ClaimDailyReward($dailyRewardId) {
    $gameData = \Base::instance()->get('GameData');
    $dailyRewards = $gameData['dailyRewards'][$dailyRewardId];
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $currentDate = GetCurrentDate();
    $cycleStart = GetDailyRewardCycleStart();
    $cycleEnd = GetDailyRewardCycleEnd();
    $rewards = $dailyRewards['rewards'];
    $consecutive = $dailyRewards['consecutive'];
    // Get reward list and earn state
    $claimableRewards = GetClaimableDailyRewards($currentDate, $cycleStart, $cycleEnd, $rewards, $consecutive, $playerId, $dailyRewardId);
    $count = count($claimableRewards);
    for ($i = 0; $i < $count; $i++) {
        $element = $claimableRewards[$i];
        if ($element['canClaim']) {
            $reward = $element['reward'];
            // Send rewards
            $mail = new Mail();
            $mail->playerId  = $playerId;
            $mail->title = "Daily Reward";
            if (!empty($reward['items']) || !empty($reward['currencies'])) {
                $mail->items = json_encode($reward['items']);
                $mail->currencies = json_encode($reward['currencies']);
                $mail->hasReward = 1;
            }
            $mail->save();
            // Store given rewards
            $rewardGiven = new DailyRewardGiven();
            $rewardGiven->playerId = $playerId;
            $rewardGiven->dailyRewardId = $dailyRewardId;
            $rewardGiven->createdAt = date("Y-m-d H:i:s", $currentDate);
            $rewardGiven->save();
            // Write output
            $output['reward'] = $reward;
            echo json_encode($output);
            return;
        }
    }
    // No rewards found
    $output['error'] = 'ERROR_NO_REWARDS';
    echo json_encode($output);
}
?>