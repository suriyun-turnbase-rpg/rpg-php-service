
ALTER TABLE `<<__prefix__>>raid_event_creation` DROP INDEX `createDate`;
ALTER TABLE `<<__prefix__>>raid_event_creation` ADD PRIMARY KEY(`createDate`);

CREATE TABLE `<<__prefix__>>clan_event` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `clanId` BIGINT NOT NULL,
  `dataId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remainingHp` INT NOT NULL,
  `startTime` INT NOT NULL DEFAULT '0',
  `endTime` INT NOT NULL DEFAULT '0',
  `rewarded` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `<<__prefix__>>clan_event_creation` (
  `clanId` BIGINT NOT NULL,
  `createDate` INT NOT NULL,
  `events` TEXT NOT NULL,
  PRIMARY KEY (`clanId`, `createDate`)
) ENGINE = InnoDB;

CREATE TABLE `<<__prefix__>>clan_event_ranking` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `playerId` BIGINT NOT NULL DEFAULT '0',
  `eventId` BIGINT NOT NULL DEFAULT '0',
  `damage` INT NOT NULL DEFAULT '0',
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), INDEX (`playerId`), INDEX (`eventId`)
) ENGINE = InnoDB;

CREATE TABLE `<<__prefix__>>random_store` (
  `dataId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `playerId` BIGINT NOT NULL,
  `randomedItems` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `purchaseItems` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastRefresh` INT NOT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dataId`, `playerId`)
) ENGINE = InnoDB;
