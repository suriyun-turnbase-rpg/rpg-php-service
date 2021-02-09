ALTER TABLE `<<__prefix__>>clan` ADD `exp` INT NOT NULL DEFAULT '0' AFTER `id`;
CREATE TABLE `<<__prefix__>>clan_checkin` ( `playerId` BIGINT NOT NULL , `checkInDate` INT NOT NULL , `clanId` BIGINT NOT NULL , PRIMARY KEY (`playerId`, `checkInDate`), INDEX (`clanId`)) ENGINE = InnoDB;
CREATE TABLE `<<__prefix__>>clan_donation` ( `playerId` BIGINT NOT NULL , `donationDate` INT NOT NULL , `clanId` BIGINT NOT NULL , `dataId` VARCHAR(50) NOT NULL , PRIMARY KEY (`playerId`, `donationDate`), INDEX (`clanId`)) ENGINE = InnoDB;