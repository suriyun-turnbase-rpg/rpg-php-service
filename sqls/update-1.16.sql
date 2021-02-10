ALTER TABLE `<<__prefix__>>clan` ADD `exp` INT NOT NULL DEFAULT '0' AFTER `id`;
ALTER TABLE `<<__prefix__>>player_battle` ADD `totalDamage` INT NOT NULL DEFAULT '0' AFTER `battleType`;
CREATE TABLE `<<__prefix__>>clan_checkin` ( `playerId` BIGINT NOT NULL , `checkInDate` INT NOT NULL , `clanId` BIGINT NOT NULL , PRIMARY KEY (`playerId`, `checkInDate`), INDEX (`clanId`)) ENGINE = InnoDB;
CREATE TABLE `<<__prefix__>>clan_donation` ( `playerId` BIGINT NOT NULL , `donationDate` INT NOT NULL , `clanId` BIGINT NOT NULL , `dataId` VARCHAR(50) NOT NULL , PRIMARY KEY (`playerId`, `donationDate`), INDEX (`clanId`)) ENGINE = InnoDB;
CREATE TABLE `<<__prefix__>>raid_event` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `dataId` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , `remainingHp` INT NOT NULL , `startTime` INT NOT NULL DEFAULT '0' , `endTime` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `<<__prefix__>>raid_event_creation` ( `createDate` INT NOT NULL , `events` TEXT NOT NULL , UNIQUE (`createDate`)) ENGINE = InnoDB;
