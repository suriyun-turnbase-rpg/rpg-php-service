-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2020 at 12:04 PM
-- Server version: 10.1.40-MariaDB
-- PHP Version: 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `tbrpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan`
--

CREATE TABLE `<<__prefix__>>clan` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>clan_join_request`
--

CREATE TABLE `<<__prefix__>>clan_join_request` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player`
--

CREATE TABLE `<<__prefix__>>player` (
  `id` bigint(20) NOT NULL,
  `profileName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `loginToken` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exp` int(11) NOT NULL DEFAULT '0',
  `selectedFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `selectedArenaFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacter` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mainCharacterExp` int(11) NOT NULL DEFAULT '0',
  `arenaScore` int(11) NOT NULL DEFAULT '0',
  `highestArenaRank` int(11) NOT NULL DEFAULT '0',
  `highestArenaRankCurrentSeason` int(11) NOT NULL DEFAULT '0',
  `clanId` bigint(20) NOT NULL,
  `clanRole` tinyint(4) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_achievement`
--

CREATE TABLE `<<__prefix__>>player_achievement` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `progress` int(11) NOT NULL DEFAULT '0',
  `earned` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_auth`
--

CREATE TABLE `<<__prefix__>>player_auth` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_battle`
--

CREATE TABLE `<<__prefix__>>player_battle` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `session` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `battleResult` tinyint(4) NOT NULL DEFAULT '0',
  `rating` tinyint(4) NOT NULL DEFAULT '0',
  `battleType` tinyint(4) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_clear_stage`
--

CREATE TABLE `<<__prefix__>>player_clear_stage` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `bestRating` tinyint(4) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_currency`
--

CREATE TABLE `<<__prefix__>>player_currency` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '0',
  `purchasedAmount` int(11) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_formation`
--

CREATE TABLE `<<__prefix__>>player_formation` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `position` tinyint(4) NOT NULL DEFAULT '0',
  `itemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `isLeader` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_friend`
--

CREATE TABLE `<<__prefix__>>player_friend` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `targetPlayerId` bigint(20) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_friend_request`
--

CREATE TABLE `<<__prefix__>>player_friend_request` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `targetPlayerId` bigint(20) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_item`
--

CREATE TABLE `<<__prefix__>>player_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '0',
  `exp` int(11) NOT NULL DEFAULT '0',
  `equipItemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `equipPosition` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `randomedAttributes` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_stamina`
--

CREATE TABLE `<<__prefix__>>player_stamina` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '0',
  `recoveredTime` int(11) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `<<__prefix__>>player_unlock_item`
--

CREATE TABLE `<<__prefix__>>player_unlock_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT '0',
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `<<__prefix__>>clan`
--
ALTER TABLE `<<__prefix__>>clan`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `<<__prefix__>>clan`
--
ALTER TABLE `<<__prefix__>>clan`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>clan_join_request`
--
ALTER TABLE `<<__prefix__>>clan_join_request`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player`
--
ALTER TABLE `<<__prefix__>>player`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_achievement`
--
ALTER TABLE `<<__prefix__>>player_achievement`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_auth`
--
ALTER TABLE `<<__prefix__>>player_auth`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_battle`
--
ALTER TABLE `<<__prefix__>>player_battle`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_clear_stage`
--
ALTER TABLE `<<__prefix__>>player_clear_stage`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_currency`
--
ALTER TABLE `<<__prefix__>>player_currency`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_formation`
--
ALTER TABLE `<<__prefix__>>player_formation`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_friend`
--
ALTER TABLE `<<__prefix__>>player_friend`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_friend_request`
--
ALTER TABLE `<<__prefix__>>player_friend_request`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_item`
--
ALTER TABLE `<<__prefix__>>player_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_stamina`
--
ALTER TABLE `<<__prefix__>>player_stamina`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `<<__prefix__>>player_unlock_item`
--
ALTER TABLE `<<__prefix__>>player_unlock_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;
