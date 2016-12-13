ALTER TABLE `ar_product_option_value` ADD `model` VARCHAR( 255 ) NOT NULL AFTER `option_value_id`;
ALTER TABLE `ar_product_option_value` ADD `sku` VARCHAR( 255 ) NOT NULL AFTER `model`;