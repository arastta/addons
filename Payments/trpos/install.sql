CREATE TABLE IF NOT EXISTS `ar_trpos_bank` (
    `bank_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `image` varchar(64) NOT NULL,
    `method` varchar(64) NOT NULL,
    `model` varchar(64) NOT NULL,
    `short` varchar(64) NOT NULL,
    `status` tinyint(1) NOT NULL,
    PRIMARY KEY (`bank_id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;