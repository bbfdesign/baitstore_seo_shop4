<?php
/**
 * Plugin: ws_seo_pagination
 * Admin-Menu: Kategorien SEO Einstellungen
 *
 * Wird von admin/plugin.php per require eingebunden.
 * Verfuegbare Variablen: $oPlugin, $smarty
 */

if (!defined('PFAD_ROOT')) {
    return;
}

$cHinweis = '';
$cFehler  = '';

// --- Formular verarbeiten ---
if (isset($_POST['ws_seo_action']) && $_POST['ws_seo_action'] === 'save' && validateToken()) {
    $robots_arr = isset($_POST['robots']) && is_array($_POST['robots']) ? $_POST['robots'] : array();
    $ab_arr     = isset($_POST['ab']) && is_array($_POST['ab']) ? $_POST['ab'] : array();
    $bis_arr    = isset($_POST['bis']) && is_array($_POST['bis']) ? $_POST['bis'] : array();

    $nGespeichert = 0;
    foreach ($robots_arr as $kKategorie => $cRobots) {
        $kKategorie = (int)$kKategorie;
        if ($kKategorie <= 0) {
            continue;
        }

        $cRobots = trim($cRobots);
        $nAb     = isset($ab_arr[$kKategorie]) ? (int)$ab_arr[$kKategorie] : 2;
        $nBis    = isset($bis_arr[$kKategorie]) ? (int)$bis_arr[$kKategorie] : 0;

        if ($nAb < 1) {
            $nAb = 1;
        }
        if ($nBis < 0) {
            $nBis = 0;
        }

        // Pruefen ob bereits ein Eintrag existiert
        $oExisting = Shop::DB()->select(
            'xplugin_ws_seo_pagination_kategorie',
            'kKategorie', $kKategorie
        );

        if ($cRobots === 'standard' || $cRobots === '') {
            // Standard = keinen Override -> Eintrag loeschen falls vorhanden
            if ($oExisting !== null) {
                Shop::DB()->delete(
                    'xplugin_ws_seo_pagination_kategorie',
                    'kKategorie',
                    $kKategorie
                );
            }
        } else {
            $oData = new stdClass();
            $oData->kKategorie    = $kKategorie;
            $oData->cRobots       = $cRobots;
            $oData->nPaginierungAb  = $nAb;
            $oData->nPaginierungBis = $nBis;

            if ($oExisting !== null) {
                $oData->dAktualisiert = date('Y-m-d H:i:s');
                Shop::DB()->update(
                    'xplugin_ws_seo_pagination_kategorie',
                    'kKategorie',
                    $kKategorie,
                    $oData
                );
            } else {
                $oData->dErstellt     = date('Y-m-d H:i:s');
                $oData->dAktualisiert = date('Y-m-d H:i:s');
                Shop::DB()->insert(
                    'xplugin_ws_seo_pagination_kategorie',
                    $oData
                );
            }
            $nGespeichert++;
        }
    }

    $cHinweis = 'Einstellungen wurden gespeichert.';
}

// --- Kategorien aus DB laden (flache Liste mit Tiefe) ---
$oKategorien_arr = array();

/**
 * Rekursiv alle Kategorien laden und als flache Liste mit Tiefe zurueckgeben
 *
 * @param int $kOberKategorie
 * @param int $nLevel
 * @param int $kSprache
 * @return array
 */
function wsSeoPagGetKategorien($kOberKategorie = 0, $nLevel = 0, $kSprache = 0)
{
    if ($kSprache <= 0) {
        $kSprache = (int)$_SESSION['kSprache'];
    }

    $result = array();
    $oKategorien = Shop::DB()->query(
        "SELECT k.kKategorie, COALESCE(ks.cName, k.cName) AS cName, k.nSort
         FROM tkategorie k
         LEFT JOIN tkategoriesprache ks
            ON ks.kKategorie = k.kKategorie AND ks.kSprache = " . (int)$kSprache . "
         WHERE k.kOberKategorie = " . (int)$kOberKategorie . "
         ORDER BY k.nSort ASC, cName ASC", 2
    );

    if (is_array($oKategorien)) {
        foreach ($oKategorien as $oKat) {
            $oKat->nLevel     = $nLevel;
            $oKat->kKategorie = (int)$oKat->kKategorie;
            $result[]         = $oKat;
            // Rekursiv Unterkategorien laden
            $children = wsSeoPagGetKategorien($oKat->kKategorie, $nLevel + 1, $kSprache);
            $result   = array_merge($result, $children);
        }
    }

    return $result;
}

$oKategorien_arr = wsSeoPagGetKategorien(0, 0);

// --- Bestehende Kategorie-Einstellungen laden ---
$oKatSettings = array();
$oKatSettingsDB = Shop::DB()->query(
    "SELECT * FROM xplugin_ws_seo_pagination_kategorie", 2
);
if (is_array($oKatSettingsDB)) {
    foreach ($oKatSettingsDB as $oKS) {
        $oKatSettings[(int)$oKS->kKategorie] = $oKS;
    }
}

// --- Admin-URL bauen ---
$adminURL = Shop::getURL() . '/' . PFAD_ADMIN . 'plugin.php';

// --- Template rendern ---
$smarty->assign('oPlugin', $oPlugin)
    ->assign('oKategorien_arr', $oKategorien_arr)
    ->assign('oKatSettings', $oKatSettings)
    ->assign('adminURL', $adminURL)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler);

echo $smarty->fetch($oPlugin->cAdminmenuPfad . 'templates/kategorien.tpl');
