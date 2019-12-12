<?php
function FilterAchievements($type)
{
    $gameData = \Base::instance()->get('GameData');
    $achievements = array();
    foreach ($gameData['achievements'] as $achievementId => $achievement) {
        if ($achievement['type'] == $type) {
            $achievements[$achievementId] = $achievement;
        }
    }
    return $achievements;
}

function FilterPlayerAchievements($achievements, $playerAchievements)
{
    $result = array();
    for ($i = 0; $i < count($playerAchievements); ++$i) {
        $playerAchievement = $playerAchievements[$i];
        if (array_key_exists($playerAchievement['dataId'], $achievements))
            $result[$playerAchievement['DataId']] = $playerAchievement;
    }
    return $result;
}

function UpdateTotalClearStage($playerId, $playerAchievements, $playerClearStages)
{
    $createPlayerAchievements = array();
    $updatePlayerAchievements = array();
    $achievements = FilterAchievements(EAchievementType::TotalClearStage);
    $playerAchievementDict = FilterPlayerAchievements($achievements, $playerAchievements);
    foreach ($achievements as $achievementId => $achievement) {
        if (!array_key_exists($achievementId, $playerAchievementDict))
        {
            $newPlayerAchievement = array();
            $newPlayerAchievement['playerId'] = $playerId;
            $newPlayerAchievement['dataId'] = $achievementId;
            $newPlayerAchievement['progress'] = count($playerClearStages);
            $createPlayerAchievements[] = $newPlayerAchievement;
        }
        else
        {
            $oldPlayerAchievement = $playerAchievementDict[$achievementId];
            $oldPlayerAchievement['progress'] = count($playerClearStages);
            $updatePlayerAchievements[] = $oldPlayerAchievement;
        }
    }
    return array('createAchievements' => $createPlayerAchievements, 'updateAchievements' => $updatePlayerAchievements);
}

function UpdateTotalClearStageRating($playerId, $playerAchievements, $playerClearStages)
{
    $createPlayerAchievements = array();
    $updatePlayerAchievements = array();
    $achievements = FilterAchievements(EAchievementType::TotalClearStageRating);
    $playerAchievementDict = FilterPlayerAchievements($achievements, $playerAchievements);
    $countRating = 0;
    for ($i = 0; $i < count($playerClearStages); ++$i)
    {
        $countRating += $playerClearStages[$i]['bestRating'];
    }
    foreach ($achievements as $achievementId => $achievement) {
        if (!array_key_exists($achievementId, $playerAchievementDict))
        {
            $newPlayerAchievement = array();
            $newPlayerAchievement['playerId'] = $playerId;
            $newPlayerAchievement['dataId'] = $achievementId;
            $newPlayerAchievement['progress'] = $countRating;
            $createPlayerAchievements[] = $newPlayerAchievement;
        }
        else
        {
            $oldPlayerAchievement = $playerAchievementDict[$achievementId];
            $oldPlayerAchievement['progress'] = $countRating;
            $updatePlayerAchievements[] = $oldPlayerAchievement;
        }
    }
    return array('createAchievements' => $createPlayerAchievements, 'updateAchievements' => $updatePlayerAchievements);
}

function UpdateCountLevelUpCharacter($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountLevelUpCharacter);
}

function UpdateCountLevelUpEquipment($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountLevelUpEquipment);
}

function UpdateCountEvolveCharacter($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountEvolveCharacter);
}

function UpdateCountEvolveEquipment($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountEvolveEquipment);
}

function UpdateCountRevive($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountRevive);
}

function UpdateCountUseHelper($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountUseHelper);
}

function UpdateCountWinStage($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountWinStage);
}

function UpdateCountWinDuel($playerId, $playerAchievements)
{
    UpdateCountingProgress($playerId, $playerAchievements, EAchievementType::CountWinDuel);
}

function UpdateCountingProgress($playerId, $playerAchievements, $type)
{
    $createPlayerAchievements = array();
    $updatePlayerAchievements = array();
    $achievements = FilterAchievements($type);
    $playerAchievementDict = FilterPlayerAchievements($achievements, $playerAchievements);
    foreach ($achievements as $achievementId => $achievement) {
        if (!array_key_exists($achievementId, $playerAchievementDict))
        {
            $newPlayerAchievement = array();
            $newPlayerAchievement['playerId'] = $playerId;
            $newPlayerAchievement['dataId'] = $achievementId;
            $newPlayerAchievement['progress'] = 1;
            $createPlayerAchievements[] = $newPlayerAchievement;
        }
        else
        {
            $oldPlayerAchievement = $playerAchievementDict[$achievementId];
            ++$oldPlayerAchievement['progress'];
            $updatePlayerAchievements[] = $oldPlayerAchievement;
        }
    }
    return array('createAchievements' => $createPlayerAchievements, 'updateAchievements' => $updatePlayerAchievements);
}
?>