<?php
/**
 * Plugin: ws_seo_pagination
 * Uninstall Script - wird bei Plugin-Deinstallation ausgefuehrt
 */
if (defined('PFAD_ROOT')) {
    Shop::DB()->query("DROP TABLE IF EXISTS `xplugin_ws_seo_pagination_kategorie`", 3);
}
