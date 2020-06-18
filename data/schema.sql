
CREATE DATABASE IF NOT EXISTS `rungeeklogs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE `logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `bucket` varchar(255) NOT NULL,
    `date` date NOT NULL,
    `time` time NOT NULL,
    `datetime` datetime NOT NULL,
    `ip` varchar(20) NOT NULL,
    `file` varchar(255) NOT NULL,
    `useragent` varchar(255) DEFAULT NULL,
    `created` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
