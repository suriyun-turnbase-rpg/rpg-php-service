ALTER TABLE `<<__prefix__>>player` ADD `iconId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `clanRole`;
ALTER TABLE `<<__prefix__>>player` ADD `frameId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `iconId`;
ALTER TABLE `<<__prefix__>>player` ADD `titleId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `frameId`;
ALTER TABLE `<<__prefix__>>clan` ADD `iconId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `description`;
ALTER TABLE `<<__prefix__>>clan` ADD `frameId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `iconId`;
ALTER TABLE `<<__prefix__>>clan` ADD `titleId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `frameId`;

CREATE TABLE `<<__prefix__>>player_unlock_icon` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `<<__prefix__>>player_unlock_frame` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `<<__prefix__>>player_unlock_title` (
  `id` bigint(20) NOT NULL,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `<<__prefix__>>daily_reward_given` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `playerId` bigint(20) NOT NULL DEFAULT 0,
  `dailyRewardId` VARCHAR(50) NOT NULL DEFAULT '',
  `createdAt` DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;