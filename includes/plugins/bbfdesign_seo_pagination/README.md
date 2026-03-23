# SEO Pagination Fix - JTL Shop 4 Plugin

Plugin zur Korrektur von SEO-relevanten Tags (Canonical, Robots, prev/next) bei paginierten Kategorieseiten.

## Anforderungen

- JTL-Shop 4.06
- PHP 7.3+
- MariaDB 5.5+

## Installation

1. Plugin-Ordner `bbfdesign_seo_pagination` nach `includes/plugins/` kopieren
2. Im JTL-Backend unter **Pluginverwaltung** das Plugin installieren und aktivieren
3. Einstellungen unter **Pluginverwaltung > SEO Pagination Fix > Einstellungen** konfigurieren
4. Kategorie-spezifische Overrides unter **Pluginverwaltung > SEO Pagination Fix > Kategorien SEO**

## Funktionen

### Self-Referencing Canonicals
Paginierte Kategorieseiten (z.B. `/Angelfutter_s5`) erhalten einen Canonical-Tag auf ihre eigene URL statt auf Seite 1.

### Robots-Steuerung
- **Global:** Paginierte Seiten ab einer konfigurierbaren Seitenzahl erhalten `noindex, follow`
- **Pro Kategorie:** Individuelle Robots-Tags und Paginierungsgrenzen je Kategorie

### rel=prev/next
Korrekte Pagination-Links im Head-Bereich fuer Suchmaschinen.

## Deinstallation

Ueber die JTL-Pluginverwaltung deinstallieren. Die Plugin-Tabelle wird automatisch entfernt.
