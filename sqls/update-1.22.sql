CREATE TABLE `<<__prefix__>>clan_unlock_icon` (
  `id` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `<<__prefix__>>clan_unlock_frame` (
  `id` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `<<__prefix__>>clan_unlock_title` (
  `id` bigint(20) NOT NULL,
  `clanId` bigint(20) NOT NULL DEFAULT 0,
  `dataId` varchar(50) NOT NULL DEFAULT '',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `<<__prefix__>>clan_unlock_icon` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `<<__prefix__>>clan_unlock_frame` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `<<__prefix__>>clan_unlock_title` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);

ALTER TABLE `<<__prefix__>>player_unlock_icon` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `<<__prefix__>>player_unlock_frame` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
ALTER TABLE `<<__prefix__>>player_unlock_title` CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);