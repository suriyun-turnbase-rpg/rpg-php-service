--
-- Database: `tbrpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>chat`
--

CREATE TABLE `<<__prefix__>>chat` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL,
  `clanId` bigint NOT NULL,
  `profileName` varchar(50) NOT NULL,
  `clanName` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `chatTime` int NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan`
--

CREATE TABLE `<<__prefix__>>clan` (
  `id` bigint NOT NULL,
  `exp` int NOT NULL DEFAULT '0',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan_checkin`
--

CREATE TABLE `<<__prefix__>>clan_checkin` (
  `playerId` bigint NOT NULL,
  `checkInDate` int NOT NULL,
  `clanId` bigint NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan_donation`
--

CREATE TABLE `<<__prefix__>>clan_donation` (
  `playerId` bigint NOT NULL,
  `donationDate` int NOT NULL,
  `clanId` bigint NOT NULL,
  `dataId` varchar(50) NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan_join_request`
--

CREATE TABLE `<<__prefix__>>clan_join_request` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `clanId` bigint NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player`
--

CREATE TABLE `<<__prefix__>>player` (
  `id` bigint NOT NULL,
  `profileName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `loginToken` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exp` int NOT NULL DEFAULT '0',
  `selectedFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `selectedArenaFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacter` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacterExp` int NOT NULL DEFAULT '0',
  `arenaScore` int NOT NULL DEFAULT '0',
  `highestArenaRank` int NOT NULL DEFAULT '0',
  `highestArenaRankCurrentSeason` int NOT NULL DEFAULT '0',
  `clanId` bigint NOT NULL DEFAULT '0',
  `clanRole` tinyint NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_achievement`
--

CREATE TABLE `<<__prefix__>>player_achievement` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `progress` int NOT NULL DEFAULT '0',
  `earned` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_auth`
--

CREATE TABLE `<<__prefix__>>player_auth` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `type` tinyint NOT NULL DEFAULT '0',
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_battle`
--

CREATE TABLE `<<__prefix__>>player_battle` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `session` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `battleResult` tinyint NOT NULL DEFAULT '0',
  `rating` tinyint NOT NULL DEFAULT '0',
  `battleType` tinyint NOT NULL DEFAULT '0',
  `totalDamage` int NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_clear_stage`
--

CREATE TABLE `<<__prefix__>>player_clear_stage` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bestRating` tinyint NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_currency`
--

CREATE TABLE `<<__prefix__>>player_currency` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int NOT NULL DEFAULT '0',
  `purchasedAmount` int NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_formation`
--

CREATE TABLE `<<__prefix__>>player_formation` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `position` tinyint NOT NULL DEFAULT '0',
  `itemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isLeader` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_friend`
--

CREATE TABLE `<<__prefix__>>player_friend` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `targetPlayerId` bigint NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_friend_request`
--

CREATE TABLE `<<__prefix__>>player_friend_request` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `targetPlayerId` bigint NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_item`
--

CREATE TABLE `<<__prefix__>>player_item` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int NOT NULL DEFAULT '0',
  `exp` int NOT NULL DEFAULT '0',
  `equipItemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `equipPosition` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `randomedAttributes` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_stamina`
--

CREATE TABLE `<<__prefix__>>player_stamina` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int NOT NULL DEFAULT '0',
  `recoveredTime` int NOT NULL DEFAULT '0',
  `refillCount` int NOT NULL DEFAULT '0',
  `lastRefillTime` int NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_unlock_item`
--

CREATE TABLE `<<__prefix__>>player_unlock_item` (
  `id` bigint NOT NULL,
  `playerId` bigint NOT NULL DEFAULT '0',
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `amount` int NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>raid_event`
--

CREATE TABLE `<<__prefix__>>raid_event` (
  `id` bigint NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remainingHp` int NOT NULL,
  `startTime` int NOT NULL DEFAULT '0',
  `endTime` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>raid_event_creation`
--

CREATE TABLE `<<__prefix__>>raid_event_creation` (
  `createDate` int NOT NULL,
  `events` text NOT NULL
) ENGINE=InnoDB;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `<<__prefix__>>chat`
--
ALTER TABLE `<<__prefix__>>chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>clan`
--
ALTER TABLE `<<__prefix__>>clan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>clan_checkin`
--
ALTER TABLE `<<__prefix__>>clan_checkin`
  ADD PRIMARY KEY (`playerId`,`checkInDate`),
  ADD KEY `clanId` (`clanId`);

--
-- Indexes for table `<<__prefix__>>clan_donation`
--
ALTER TABLE `<<__prefix__>>clan_donation`
  ADD PRIMARY KEY (`playerId`,`donationDate`),
  ADD KEY `clanId` (`clanId`);

--
-- Indexes for table `<<__prefix__>>clan_join_request`
--
ALTER TABLE `<<__prefix__>>clan_join_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player`
--
ALTER TABLE `<<__prefix__>>player`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loginToken` (`loginToken`(255));

--
-- Indexes for table `<<__prefix__>>player_achievement`
--
ALTER TABLE `<<__prefix__>>player_achievement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_auth`
--
ALTER TABLE `<<__prefix__>>player_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_battle`
--
ALTER TABLE `<<__prefix__>>player_battle`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_clear_stage`
--
ALTER TABLE `<<__prefix__>>player_clear_stage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_currency`
--
ALTER TABLE `<<__prefix__>>player_currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_formation`
--
ALTER TABLE `<<__prefix__>>player_formation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_friend`
--
ALTER TABLE `<<__prefix__>>player_friend`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_friend_request`
--
ALTER TABLE `<<__prefix__>>player_friend_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_item`
--
ALTER TABLE `<<__prefix__>>player_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_stamina`
--
ALTER TABLE `<<__prefix__>>player_stamina`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>player_unlock_item`
--
ALTER TABLE `<<__prefix__>>player_unlock_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>raid_event`
--
ALTER TABLE `<<__prefix__>>raid_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `<<__prefix__>>raid_event_creation`
--
ALTER TABLE `<<__prefix__>>raid_event_creation`
  ADD UNIQUE KEY `createDate` (`createDate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `<<__prefix__>>chat`
--
ALTER TABLE `<<__prefix__>>chat`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>clan`
--
ALTER TABLE `<<__prefix__>>clan`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>clan_join_request`
--
ALTER TABLE `<<__prefix__>>clan_join_request`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player`
--
ALTER TABLE `<<__prefix__>>player`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_achievement`
--
ALTER TABLE `<<__prefix__>>player_achievement`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_auth`
--
ALTER TABLE `<<__prefix__>>player_auth`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_battle`
--
ALTER TABLE `<<__prefix__>>player_battle`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_clear_stage`
--
ALTER TABLE `<<__prefix__>>player_clear_stage`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_currency`
--
ALTER TABLE `<<__prefix__>>player_currency`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_formation`
--
ALTER TABLE `<<__prefix__>>player_formation`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_friend`
--
ALTER TABLE `<<__prefix__>>player_friend`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_friend_request`
--
ALTER TABLE `<<__prefix__>>player_friend_request`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_item`
--
ALTER TABLE `<<__prefix__>>player_item`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_stamina`
--
ALTER TABLE `<<__prefix__>>player_stamina`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_unlock_item`
--
ALTER TABLE `<<__prefix__>>player_unlock_item`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>raid_event`
--
ALTER TABLE `<<__prefix__>>raid_event`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;
