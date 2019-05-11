-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 11, 2019 at 10:31 PM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tbrpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE `player` (
  `id` bigint(20) NOT NULL,
  `profileName` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `loginToken` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `exp` int(11) NOT NULL,
  `selectedFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `selectedArenaFormation` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `mainCharacter` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `mainCharacterExp` int(11) NOT NULL,
  `arenaScore` int(11) NOT NULL,
  `highestArenaRank` int(11) NOT NULL,
  `highestArenaRankCurrentSeason` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_auth`
--

CREATE TABLE `player_auth` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_battle`
--

CREATE TABLE `player_battle` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `session` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `battleResult` tinyint(4) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `battleType` tinyint(4) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_clear_stage`
--

CREATE TABLE `player_clear_stage` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `bestRating` tinyint(4) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_currency`
--

CREATE TABLE `player_currency` (
  `id` bigint(20) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `purchasedAmount` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_formation`
--

CREATE TABLE `player_formation` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `position` tinyint(4) NOT NULL,
  `itemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_friend`
--

CREATE TABLE `player_friend` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `targetPlayerId` bigint(20) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_friend_request`
--

CREATE TABLE `player_friend_request` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `targetPlayerId` bigint(20) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_item`
--

CREATE TABLE `player_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `exp` int(11) NOT NULL,
  `equipItemId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `equipPosition` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_stamina`
--

CREATE TABLE `player_stamina` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `recoveredTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `player_unlock_item`
--

CREATE TABLE `player_unlock_item` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL,
  `dataId` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_auth`
--
ALTER TABLE `player_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_battle`
--
ALTER TABLE `player_battle`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_clear_stage`
--
ALTER TABLE `player_clear_stage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_currency`
--
ALTER TABLE `player_currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_formation`
--
ALTER TABLE `player_formation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_friend`
--
ALTER TABLE `player_friend`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_friend_request`
--
ALTER TABLE `player_friend_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_item`
--
ALTER TABLE `player_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_stamina`
--
ALTER TABLE `player_stamina`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player_unlock_item`
--
ALTER TABLE `player_unlock_item`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `player`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_auth`
--
ALTER TABLE `player_auth`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_battle`
--
ALTER TABLE `player_battle`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_clear_stage`
--
ALTER TABLE `player_clear_stage`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_currency`
--
ALTER TABLE `player_currency`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_formation`
--
ALTER TABLE `player_formation`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_friend`
--
ALTER TABLE `player_friend`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_friend_request`
--
ALTER TABLE `player_friend_request`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_item`
--
ALTER TABLE `player_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_stamina`
--
ALTER TABLE `player_stamina`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `player_unlock_item`
--
ALTER TABLE `player_unlock_item`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
