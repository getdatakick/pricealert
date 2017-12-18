CREATE TABLE IF NOT EXISTS `PREFIX_ph_pricealert` (
	`id_pricealert` int(10) unsigned NOT NULL auto_increment,
	`id_shop` int(10) unsigned default 1,
	`id_shop_group` int(10) unsigned default 1,
	`id_product` int(10) unsigned NOT NULL,
	`id_product_attribute` int(10) unsigned NULL,
	`date_add` datetime NOT NULL,
	`date_send` datetime NULL,
	`id_customer` int(10) unsigned NULL,
	`id_local` varchar(40) NOT NULL,
	`email` varchar(255) NOT NULL,
	`price` decimal(13,4) NOT NULL,
	`id_format_currency` int(10) unsigned default 1,
	PRIMARY KEY  (`id_pricealert`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
