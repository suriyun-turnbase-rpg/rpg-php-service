CREATE TABLE `<<__prefix__>>raid_event_ranking` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `playerId` BIGINT NOT NULL DEFAULT '0',
  `eventId` BIGINT NOT NULL DEFAULT '0',
  `damage` INT NOT NULL DEFAULT '0',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), INDEX (`playerId`), INDEX (`eventId`)
) ENGINE = InnoDB;

ALTER TABLE `<<__prefix__>>raid_event` ADD `rewarded` tinyint(1) NOT NULL DEFAULT '0' AFTER `endTime`;

CREATE TABLE `<<__prefix__>>mail` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `playerId` BIGINT NOT NULL DEFAULT '0',
  `title` VARCHAR(160) NOT NULL DEFAULT '',
  `content` TEXT NULL,
  `currencies` TEXT NULL,
  `items` TEXT NULL,
  `hasReward` TINYINT(1) NOT NULL DEFAULT '0',
  `isRead` TINYINT(1) NOT NULL DEFAULT '0',
  `readTimestamp` TIMESTAMP NULL,
  `isClaim` TINYINT(1) NOT NULL DEFAULT '0',
  `claimTimestamp` TIMESTAMP NULL,
  `isDelete` TINYINT(1) NOT NULL DEFAULT '0',
  `deleteTimestamp` TIMESTAMP NULL,
  `sentTimestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), INDEX (`playerId`), INDEX (`hasReward`), INDEX (`isRead`), INDEX (`isClaim`), INDEX (`isDelete`)
) ENGINE = InnoDB;
