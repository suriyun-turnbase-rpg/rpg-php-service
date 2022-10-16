SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `tbrpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `<<__prefix__>>chat` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL,
  `profileName` varchar(50) NOT NULL,
  `clanName` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `chatTime` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan`
--

CREATE TABLE `<<__prefix__>>clan` (
  `id` bigint(20) NOT NULL,
  `exp` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `iconId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `frameId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titleId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_checkin`
--

CREATE TABLE `<<__prefix__>>clan_checkin` (
  `playerId` bigint(20) NOT NULL,
  `checkInDate` int(11) NOT NULL,
  `clanId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_donation`
--

CREATE TABLE `<<__prefix__>>clan_donation` (
  `playerId` bigint(20) NOT NULL,
  `donationDate` int(11) NOT NULL,
  `count` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `clanId` bigint(20) NOT NULL,
  `dataId` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_event`
--

CREATE TABLE `<<__prefix__>>clan_event` (
  `id` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remainingHp` int(11) NOT NULL,
  `startTime` int(11) NOT NULL DEFAULT 0,
  `endTime` int(11) NOT NULL DEFAULT 0,
  `rewarded` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_event_creation`
--

CREATE TABLE `<<__prefix__>>clan_event_creation` (
  `clanId` bigint(20) NOT NULL,
  `createDate` int(11) NOT NULL,
  `events` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_event_ranking`
--

CREATE TABLE `<<__prefix__>>clan_event_ranking` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `eventId` bigint(20) NOT NULL DEFAULT 0,
  `damage` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clan_join_request`
--

CREATE TABLE `<<__prefix__>>clan_join_request` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `daily_reward_given`
--

CREATE TABLE `<<__prefix__>>daily_reward_given` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dailyRewardId` VARCHAR(50) NOT NULL DEFAULT '',
  `createdAt` DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE `<<__prefix__>>mail` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(160) NOT NULL DEFAULT '',
  `content` text DEFAULT NULL,
  `currencies` text DEFAULT NULL,
  `items` text DEFAULT NULL,
  `hasReward` tinyint(1) NOT NULL DEFAULT 0,
  `isRead` tinyint(1) NOT NULL DEFAULT 0,
  `readTimestamp` timestamp NULL DEFAULT NULL,
  `isClaim` tinyint(1) NOT NULL DEFAULT 0,
  `claimTimestamp` timestamp NULL DEFAULT NULL,
  `isDelete` tinyint(1) NOT NULL DEFAULT 0,
  `deleteTimestamp` timestamp NULL DEFAULT NULL,
  `sentTimestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE `<<__prefix__>>player` (
  `id` bigint(20) NOT NULL,
  `profileName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `loginToken` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exp` int(11) NOT NULL DEFAULT 0,
  `selectedFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `selectedArenaFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacter` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacterExp` int(11) NOT NULL DEFAULT 0,
  `arenaScore` int(11) NOT NULL DEFAULT 0,
  `highestArenaRank` int(11) NOT NULL DEFAULT 0,
  `highestArenaRankCurrentSeason` int(11) NOT NULL DEFAULT 0,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `clanRole` tinyint(4) NOT NULL DEFAULT 0,
  `iconId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `frameId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titleId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_achievement`
--

CREATE TABLE `<<__prefix__>>player_achievement` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `progress` int(11) NOT NULL DEFAULT 0,
  `earned` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_auth`
--

CREATE TABLE `<<__prefix__>>player_auth` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_battle`
--

CREATE TABLE `<<__prefix__>>player_battle` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `session` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `battleResult` tinyint(4) NOT NULL DEFAULT 0,
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `battleType` tinyint(4) NOT NULL DEFAULT 0,
  `totalDamage` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_clear_stage`
--

CREATE TABLE `<<__prefix__>>player_clear_stage` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bestRating` tinyint(4) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_currency`
--

CREATE TABLE `<<__prefix__>>player_currency` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT 0,
  `purchasedAmount` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_formation`
--

CREATE TABLE `<<__prefix__>>player_formation` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `position` tinyint(4) NOT NULL DEFAULT 0,
  `itemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isLeader` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_friend`
--

CREATE TABLE `<<__prefix__>>player_friend` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `targetPlayerId` bigint(20) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_friend_request`
--

CREATE TABLE `<<__prefix__>>player_friend_request` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `targetPlayerId` bigint(20) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_item`
--

CREATE TABLE `<<__prefix__>>player_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT 0,
  `exp` int(11) NOT NULL DEFAULT 0,
  `equipItemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `equipPosition` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `randomedAttributes` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_stamina`
--

CREATE TABLE `<<__prefix__>>player_stamina` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT 0,
  `recoveredTime` int(11) NOT NULL DEFAULT 0,
  `refillCount` int(11) NOT NULL DEFAULT 0,
  `lastRefillTime` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlock_item`
--

CREATE TABLE `<<__prefix__>>player_unlock_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlock_icon`
--

CREATE TABLE `<<__prefix__>>player_unlock_icon` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlock_frame`
--

CREATE TABLE `<<__prefix__>>player_unlock_frame` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlock_title`
--

CREATE TABLE `<<__prefix__>>player_unlock_title` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `raid_event`
--

CREATE TABLE `<<__prefix__>>raid_event` (
  `id` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remainingHp` int(11) NOT NULL,
  `startTime` int(11) NOT NULL DEFAULT 0,
  `endTime` int(11) NOT NULL DEFAULT 0,
  `rewarded` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `raid_event_creation`
--

CREATE TABLE `<<__prefix__>>raid_event_creation` (
  `createDate` int(11) NOT NULL,
  `events` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `raid_event_ranking`
--

CREATE TABLE `<<__prefix__>>raid_event_ranking` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `eventId` bigint(20) NOT NULL DEFAULT 0,
  `damage` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `random_store`
--

CREATE TABLE `<<__prefix__>>random_store` (
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `randomedItems` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `purchaseItems` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastRefresh` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `<<__prefix__>>chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clan`
--
ALTER TABLE `<<__prefix__>>clan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clan_checkin`
--
ALTER TABLE `<<__prefix__>>clan_checkin`
  ADD PRIMARY KEY (`playerId`,`checkInDate`),
  ADD KEY `clanId` (`clanId`);

--
-- Indexes for table `clan_donation`
--
ALTER TABLE `<<__prefix__>>clan_donation`
  ADD PRIMARY KEY (`playerId`,`donationDate`,`count`) USING BTREE,
  ADD KEY `clanId` (`clanId`);

--
-- Indexes for table `clan_event`
--
ALTER TABLE `<<__prefix__>>clan_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clan_event_creation`
--
ALTER TABLE `<<__prefix__>>clan_event_creation`
  ADD PRIMARY KEY (`clanId`,`createDate`);

--
-- Indexes for table `clan_event_ranking`
--
ALTER TABLE `<<__prefix__>>clan_event_ranking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playerId` (`playerId`),
  ADD KEY `eventId` (`eventId`);

--
-- Indexes for table `clan_join_request`
--
ALTER TABLE `<<__prefix__>>clan_join_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mail`
--
ALTER TABLE `<<__prefix__>>mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playerId` (`playerId`),
  ADD KEY `hasReward` (`hasReward`),
  ADD KEY `isRead` (`isRead`),
  ADD KEY `isClaim` (`isClaim`),
  ADD KEY `isDelete` (`isDelete`);

--
-- Indexes for table `player`
--
ALTER TABLE `<<__prefix__>>player`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loginToken` (`loginToken`(255));

--
-- Indexes for table `player_achievement`
--
ALTER TABLE `<<__prefix__>>player_achievement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_auth`
--
ALTER TABLE `<<__prefix__>>player_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_battle`
--
ALTER TABLE `<<__prefix__>>player_battle`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_clear_stage`
--
ALTER TABLE `<<__prefix__>>player_clear_stage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_currency`
--
ALTER TABLE `<<__prefix__>>player_currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_formation`
--
ALTER TABLE `<<__prefix__>>player_formation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_friend`
--
ALTER TABLE `<<__prefix__>>player_friend`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_friend_request`
--
ALTER TABLE `<<__prefix__>>player_friend_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_item`
--
ALTER TABLE `<<__prefix__>>player_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_stamina`
--
ALTER TABLE `<<__prefix__>>player_stamina`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_unlock_item`
--
ALTER TABLE `<<__prefix__>>player_unlock_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raid_event`
--
ALTER TABLE `<<__prefix__>>raid_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `raid_event_creation`
--
ALTER TABLE `<<__prefix__>>raid_event_creation`
  ADD PRIMARY KEY (`createDate`);

--
-- Indexes for table `raid_event_ranking`
--
ALTER TABLE `<<__prefix__>>raid_event_ranking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `playerId` (`playerId`),
  ADD KEY `eventId` (`eventId`);

--
-- Indexes for table `random_store`
--
ALTER TABLE `<<__prefix__>>random_store`
  ADD PRIMARY KEY (`dataId`,`playerId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `<<__prefix__>>chat`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clan`
--
ALTER TABLE `<<__prefix__>>clan`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clan_event`
--
ALTER TABLE `<<__prefix__>>clan_event`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clan_event_ranking`
--
ALTER TABLE `<<__prefix__>>clan_event_ranking`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clan_join_request`
--
ALTER TABLE `<<__prefix__>>clan_join_request`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail`
--
ALTER TABLE `<<__prefix__>>mail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `<<__prefix__>>player`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_achievement`
--
ALTER TABLE `<<__prefix__>>player_achievement`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_auth`
--
ALTER TABLE `<<__prefix__>>player_auth`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_battle`
--
ALTER TABLE `<<__prefix__>>player_battle`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_clear_stage`
--
ALTER TABLE `<<__prefix__>>player_clear_stage`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_currency`
--
ALTER TABLE `<<__prefix__>>player_currency`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_formation`
--
ALTER TABLE `<<__prefix__>>player_formation`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_friend`
--
ALTER TABLE `<<__prefix__>>player_friend`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_friend_request`
--
ALTER TABLE `<<__prefix__>>player_friend_request`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_item`
--
ALTER TABLE `<<__prefix__>>player_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_stamina`
--
ALTER TABLE `<<__prefix__>>player_stamina`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_unlock_item`
--
ALTER TABLE `<<__prefix__>>player_unlock_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raid_event`
--
ALTER TABLE `<<__prefix__>>raid_event`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raid_event_ranking`
--
ALTER TABLE `<<__prefix__>>raid_event_ranking`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;
