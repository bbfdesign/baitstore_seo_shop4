{if isset($cHinweis) && $cHinweis|strlen > 0}
    <div class="alert alert-success">
        <i class="fa fa-check"></i> {$cHinweis}
    </div>
{/if}
{if isset($cFehler) && $cFehler|strlen > 0}
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i> {$cFehler}
    </div>
{/if}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">SEO Pagination - Kategorien-Einstellungen</h3>
    </div>
    <div class="panel-body">
        <p class="text-muted">
            Hier k&ouml;nnen Sie pro Kategorie individuelle Robots-Tags und Paginierungsgrenzen festlegen.
            Diese Einstellungen haben Vorrang vor den globalen Plugin-Einstellungen.<br>
            <strong>Standard</strong> = Globale Plugin-Einstellung wird verwendet.
        </p>

        <form method="post" action="{$adminURL}">
            {$jtl_token}
            <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}">
            <input type="hidden" name="cPluginTab" value="Kategorien SEO">
            <input type="hidden" name="bbf_seo_action" value="save">

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Kategorie</th>
                            <th style="width: 25%;">Robots-Override</th>
                            <th style="width: 15%;">Ab Seite</th>
                            <th style="width: 15%;">Bis Seite</th>
                            <th style="width: 5%;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if isset($oKategorien_arr) && $oKategorien_arr|@count > 0}
                            {foreach from=$oKategorien_arr item=oKat}
                                <tr>
                                    <td style="padding-left: {($oKat->nLevel * 20) + 8}px;">
                                        {if $oKat->nLevel > 0}
                                            <span class="text-muted">{section name=i loop=$oKat->nLevel}&mdash;{/section}</span>
                                        {/if}
                                        {$oKat->cName}
                                    </td>
                                    <td>
                                        <select name="robots[{$oKat->kKategorie}]" class="form-control input-sm">
                                            <option value="standard"{if !isset($oKatSettings[$oKat->kKategorie]) || $oKatSettings[$oKat->kKategorie]->cRobots === 'standard' || $oKatSettings[$oKat->kKategorie]->cRobots === ''} selected{/if}>Standard</option>
                                            <option value="index, follow"{if isset($oKatSettings[$oKat->kKategorie]) && $oKatSettings[$oKat->kKategorie]->cRobots === 'index, follow'} selected{/if}>index, follow</option>
                                            <option value="noindex, follow"{if isset($oKatSettings[$oKat->kKategorie]) && $oKatSettings[$oKat->kKategorie]->cRobots === 'noindex, follow'} selected{/if}>noindex, follow</option>
                                            <option value="noindex, nofollow"{if isset($oKatSettings[$oKat->kKategorie]) && $oKatSettings[$oKat->kKategorie]->cRobots === 'noindex, nofollow'} selected{/if}>noindex, nofollow</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="ab[{$oKat->kKategorie}]" class="form-control input-sm"
                                               value="{if isset($oKatSettings[$oKat->kKategorie])}{$oKatSettings[$oKat->kKategorie]->nPaginierungAb}{else}2{/if}"
                                               min="1" max="100">
                                    </td>
                                    <td>
                                        <input type="number" name="bis[{$oKat->kKategorie}]" class="form-control input-sm"
                                               value="{if isset($oKatSettings[$oKat->kKategorie])}{$oKatSettings[$oKat->kKategorie]->nPaginierungBis}{else}0{/if}"
                                               min="0" max="1000">
                                    </td>
                                    <td class="text-muted small">{$oKat->kKategorie}</td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="5" class="text-center text-muted">Keine Kategorien gefunden.</td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> Einstellungen speichern
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Hilfe</h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal">
            <dt>Robots-Override</dt>
            <dd>
                <strong>Standard:</strong> Globale Plugin-Einstellung wird verwendet.<br>
                <strong>index, follow:</strong> Seite wird indexiert und Links werden gefolgt.<br>
                <strong>noindex, follow:</strong> Seite wird NICHT indexiert, Links werden aber gefolgt.<br>
                <strong>noindex, nofollow:</strong> Seite wird NICHT indexiert, Links werden NICHT gefolgt.
            </dd>
            <dt>Ab Seite</dt>
            <dd>Ab welcher paginierten Seite der Robots-Override greifen soll. Standard: 2 (= ab Seite 2).</dd>
            <dt>Bis Seite</dt>
            <dd>Bis zu welcher paginierten Seite der Override gilt. 0 = unbegrenzt (alle Seiten ab &quot;Ab Seite&quot;).</dd>
        </dl>
    </div>
</div>
