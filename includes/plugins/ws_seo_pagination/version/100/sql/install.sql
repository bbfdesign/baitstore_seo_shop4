CREATE TABLE IF NOT EXISTS `xplugin_ws_seo_pagination_kategorie` (
    `kKategorie` INT(10) UNSIGNED NOT NULL,
    `cRobots` VARCHAR(50) DEFAULT NULL COMMENT 'z.B. noindex, follow',
    `nPaginierungAb` INT(5) UNSIGNED DEFAULT 2,
    `nPaginierungBis` INT(5) UNSIGNED DEFAULT 0 COMMENT '0 = unbegrenzt',
    `dErstellt` DATETIME DEFAULT NULL,
    `dAktualisiert` DATETIME DEFAULT NULL,
    PRIMARY KEY (`kKategorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
