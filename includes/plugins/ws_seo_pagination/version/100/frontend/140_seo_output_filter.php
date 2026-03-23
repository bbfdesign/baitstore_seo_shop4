<?php
/**
 * Plugin: ws_seo_pagination
 * Hook: HOOK_SMARTY_OUTPUTFILTER (140)
 *
 * Korrigiert Canonical-Tags, setzt Robots-Meta und prev/next
 * bei paginierten Kategorieseiten.
 *
 * Verfuegbare Variablen im Hook-Kontext:
 * - $oPlugin (Plugin-Objekt)
 * - $smarty (JTLSmarty-Instanz)
 * - $GLOBALS['doc'] (phpQuery-Dokument des HTML-Outputs)
 */

if (!defined('PFAD_ROOT')) {
    return;
}

// Nur auf Artikellisten-/Kategorieseiten aktiv (PAGE_ARTIKELLISTE = 2)
$nSeitenTyp = Shop::getPageType();
if ($nSeitenTyp !== PAGE_ARTIKELLISTE) {
    return;
}

// Plugin-Einstellungen laden
$conf = $oPlugin->oPluginEinstellungAssoc_arr;

// Aktuelle Seitenzahl ermitteln
$nSeite   = 1;
$nMaxSeit = 1;
$kKategorie = 0;

if (isset($GLOBALS['NaviFilter'])) {
    $NaviFilter = $GLOBALS['NaviFilter'];

    if (isset($NaviFilter->nSeite) && (int)$NaviFilter->nSeite > 0) {
        $nSeite = (int)$NaviFilter->nSeite;
    }

    // Kategorie-ID ermitteln
    if (isset($NaviFilter->Kategorie->kKategorie) && (int)$NaviFilter->Kategorie->kKategorie > 0) {
        $kKategorie = (int)$NaviFilter->Kategorie->kKategorie;
    }
}

// Max-Seiten aus Suchergebnissen
if (isset($GLOBALS['oSuchergebnisse']->Seitenzahlen->MaxSeiten)) {
    $nMaxSeit = (int)$GLOBALS['oSuchergebnisse']->Seitenzahlen->MaxSeiten;
} elseif (isset($GLOBALS['Suchergebnisse']->Seitenzahlen->MaxSeiten)) {
    $nMaxSeit = (int)$GLOBALS['Suchergebnisse']->Seitenzahlen->MaxSeiten;
}

// phpQuery-Dokument holen
$doc = $GLOBALS['doc'];
if (!is_object($doc)) {
    return;
}

// --- 1. Self-Referencing Canonical korrigieren ---
if (isset($conf['canonical_self_ref']) && $conf['canonical_self_ref'] === 'Y' && $nSeite > 1) {
    // Aktuelle vollstaendige URL mit Seitenparameter zusammenbauen
    $cBaseCanonical = '';

    // Bestehenden Canonical auslesen
    $canonicalTag = pq('link[rel="canonical"]');
    if ($canonicalTag->length > 0) {
        $cBaseCanonical = $canonicalTag->attr('href');
    }

    if (!empty($cBaseCanonical)) {
        // Pruefen ob der Canonical bereits den Seiten-Suffix enthaelt
        $cSuffix = SEP_SEITE . $nSeite; // z.B. _s2

        if (strpos($cBaseCanonical, $cSuffix) === false) {
            // Eventuell vorhandenen alten Seiten-Suffix entfernen
            $cClean = preg_replace('/' . preg_quote(SEP_SEITE, '/') . '[0-9]+$/', '', $cBaseCanonical);
            $cNeuerCanonical = $cClean . $cSuffix;

            // Canonical-Tag aktualisieren
            $canonicalTag->attr('href', $cNeuerCanonical);

            // Auch og:url und itemprop url aktualisieren
            pq('meta[property="og:url"]')->attr('content', $cNeuerCanonical);
            pq('meta[itemprop="url"]')->not('[content*="' . Shop::getURL() . '/"]')->attr('content', $cNeuerCanonical);
            // itemprop="url" im head-Bereich (nicht das Logo-Meta)
            $metaUrls = pq('head meta[itemprop="url"]');
            foreach ($metaUrls as $metaUrl) {
                $el = pq($metaUrl);
                $content = $el->attr('content');
                // Nur das Seiten-URL-Meta aktualisieren, nicht das Organisation-Meta
                if ($content === $cBaseCanonical || strpos($content, SEP_SEITE) !== false) {
                    $el->attr('content', $cNeuerCanonical);
                }
            }
        }
    }
}

// --- 2. Robots-Tag steuern ---
$cRobotsOverride = '';

if ($nSeite > 1 && $kKategorie > 0) {
    // Kategorie-spezifische Einstellung pruefen
    $oKatSeo = Shop::DB()->select(
        'xplugin_ws_seo_pagination_kategorie',
        'kKategorie', (int)$kKategorie
    );

    if ($oKatSeo !== null && !empty($oKatSeo->cRobots) && $oKatSeo->cRobots !== 'standard') {
        $nAb  = (int)$oKatSeo->nPaginierungAb;
        $nBis = (int)$oKatSeo->nPaginierungBis;

        if ($nSeite >= $nAb && ($nBis === 0 || $nSeite <= $nBis)) {
            $cRobotsOverride = $oKatSeo->cRobots;
        }
    }
}

// Fallback auf globale Einstellung
if (empty($cRobotsOverride) && $nSeite > 1) {
    if (isset($conf['pagination_noindex']) && $conf['pagination_noindex'] === 'Y') {
        $nGlobalAb  = isset($conf['pagination_noindex_ab']) ? (int)$conf['pagination_noindex_ab'] : 2;
        $nGlobalBis = isset($conf['pagination_noindex_bis']) ? (int)$conf['pagination_noindex_bis'] : 0;

        if ($nSeite >= $nGlobalAb && ($nGlobalBis === 0 || $nSeite <= $nGlobalBis)) {
            $cRobotsOverride = 'noindex, follow';
        }
    }
}

// Robots-Override fuer Seite 1 (nur wenn Kategorie-spezifisch gesetzt)
if (empty($cRobotsOverride) && $nSeite <= 1 && $kKategorie > 0) {
    $oKatSeo = Shop::DB()->select(
        'xplugin_ws_seo_pagination_kategorie',
        'kKategorie', (int)$kKategorie
    );

    if ($oKatSeo !== null && !empty($oKatSeo->cRobots) && $oKatSeo->cRobots !== 'standard') {
        // Auf Seite 1 nur anwenden, wenn nPaginierungAb = 1 ist
        $nAb = (int)$oKatSeo->nPaginierungAb;
        if ($nAb <= 1) {
            $cRobotsOverride = $oKatSeo->cRobots;
        }
    }
}

// Robots-Meta-Tag im HTML ersetzen
if (!empty($cRobotsOverride)) {
    $robotsMeta = pq('meta[name="robots"]');
    if ($robotsMeta->length > 0) {
        $robotsMeta->attr('content', $cRobotsOverride);
    } else {
        // Robots-Tag existiert nicht -> hinzufuegen
        pq('head')->prepend('<meta name="robots" content="' . htmlspecialchars($cRobotsOverride) . '">');
    }
}

// --- 3. rel=prev/next Tags ---
if (isset($conf['prev_next_tags']) && $conf['prev_next_tags'] === 'Y' && $nMaxSeit > 1 && $kKategorie > 0) {
    // Bestehende prev/next entfernen, um Duplikate zu vermeiden
    pq('link[rel="prev"]')->remove();
    pq('link[rel="next"]')->remove();

    // Basis-URL ermitteln (ohne Seiten-Suffix)
    $cBaseURL = '';
    $canonicalTag = pq('link[rel="canonical"]');
    if ($canonicalTag->length > 0) {
        $cBaseURL = $canonicalTag->attr('href');
        // Seiten-Suffix entfernen fuer Basis-URL
        $cBaseURL = preg_replace('/' . preg_quote(SEP_SEITE, '/') . '[0-9]+$/', '', $cBaseURL);
    }

    if (!empty($cBaseURL)) {
        $cPrevNextHTML = '';

        // rel=prev (nicht auf Seite 1)
        if ($nSeite > 1) {
            if ($nSeite === 2) {
                // Seite 2 -> prev zeigt auf Seite 1 (ohne _s1)
                $cPrevURL = $cBaseURL;
            } else {
                $cPrevURL = $cBaseURL . SEP_SEITE . ($nSeite - 1);
            }
            $cPrevNextHTML .= '<link rel="prev" href="' . htmlspecialchars($cPrevURL) . '">';
        }

        // rel=next (nicht auf letzter Seite)
        if ($nSeite < $nMaxSeit) {
            $cNextURL = $cBaseURL . SEP_SEITE . ($nSeite + 1);
            $cPrevNextHTML .= '<link rel="next" href="' . htmlspecialchars($cNextURL) . '">';
        }

        if (!empty($cPrevNextHTML)) {
            // Nach dem Canonical-Tag einfuegen
            if ($canonicalTag->length > 0) {
                $canonicalTag->after($cPrevNextHTML);
            } else {
                pq('head')->append($cPrevNextHTML);
            }
        }
    }
}
