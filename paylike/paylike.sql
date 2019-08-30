CREATE TABLE `#__hikashop_payment_plg_paylike` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(1) UNSIGNED DEFAULT NULL,
  `order_number` char(64) DEFAULT NULL,
  `paymentmethod_id` mediumint(1) UNSIGNED DEFAULT NULL,
  `payment_name` varchar(5000) DEFAULT NULL,
  `amount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `status` varchar(225) DEFAULT NULL,
  `mode` varchar(225) DEFAULT NULL,
  `productinfo` text,
  `txnid` varchar(29) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `#__hikashop_payment_plg_paylike` ADD PRIMARY KEY (`id`);

ALTER TABLE `#__hikashop_payment_plg_paylike` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


