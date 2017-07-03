CREATE TABLE IF NOT EXISTS `ar_hepsipay_order` (
    `hepsipay_order_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `transaction_id` varchar(100) NOT NULL,
    `bank_id` varchar(100) NOT NULL,
    `status` tinyint(1) NOT NULL,
    `use3d`  tinyint(1) NOT NULL,
    `client_ip` varchar(50) NOT NULL,
    `installments` int(11) NOT NULL,
    `extra_installments` int(11) NOT NULL,
    `campaign_id` int(11) NOT NULL,
    `ErrorMSG` text NOT NULL,
    `ErrorCode` varchar(11) NOT NULL,
    `conversion_rate` DECIMAL( 10, 2 ) NOT NULL,
    `try_total` DECIMAL( 10, 2 ) NOT NULL,
    `original` text NOT NULL,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`hepsipay_order_id`)
    ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `ar_hepsipay_3d_form` (
  `hepsipay_3d_form_id` int(11) NOT NULL AUTO_INCREMENT,
  `html` text NOT NULL,
  PRIMARY KEY (`hepsipay_3d_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
