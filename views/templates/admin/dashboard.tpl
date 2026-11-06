{*
 * Revue de données : liste des contrôles qui renvoient des résultats ou une erreur.
 *}
<div class="reviewdata">
  <div class="panel">
    <div class="panel-heading">
      <i class="icon-search"></i> Revue de données
      <span class="panel-heading-action">
        <a class="btn btn-default" href="{$rd_refresh_url|escape:'htmlall':'UTF-8'}">
          <i class="icon-refresh"></i> Relancer les contrôles
        </a>
      </span>
    </div>

    <p class="rd-status">
      <span class="rd-dot"></span>
      Base <code>{$rd_db_name|escape:'htmlall':'UTF-8'}</code> (préfixe <code>{$rd_prefix|escape:'htmlall':'UTF-8'}</code>)
      · exécuté le {$rd_date|escape:'htmlall':'UTF-8'}
    </p>

    <div class="rd-summary">
      <div class="rd-card rd-card-accent">
        <span class="rd-label">Vérifications</span>
        <strong class="rd-value">{$rd_total_checks|intval}</strong>
        <span class="rd-note">{$rd_clean|intval} sans anomalie (masquées)</span>
      </div>
      <div class="rd-card">
        <span class="rd-label">Anomalies listées</span>
        <strong class="rd-value">{$rd_total_rows|escape:'htmlall':'UTF-8'}</strong>
        <span class="rd-note">maximum {$rd_row_limit|intval} lignes par contrôle</span>
      </div>
      <div class="rd-card{if $rd_errors > 0} rd-card-error{/if}">
        <span class="rd-label">Erreurs</span>
        <strong class="rd-value">{$rd_errors|intval}</strong>
        <span class="rd-note">requêtes non exécutées</span>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-heading rd-list-heading">
      <span><i class="icon-warning"></i> Contrôles à traiter</span>
      <input id="rd-search" class="form-control rd-search" type="search" placeholder="Filtrer les contrôles…" autocomplete="off">
    </div>

    {if empty($rd_listed)}
      <p class="alert alert-success">Aucun contrôle ne renvoie de résultat ni d’erreur.</p>
    {else}
      <ul class="rd-list" id="rd-list">
        {foreach from=$rd_listed item=check}
          <li class="rd-item rd-{$check.state|escape:'htmlall':'UTF-8'}" data-rd-item>
            <div class="rd-item-heading">
              <div>
                <span class="rd-category">{$check.category|escape:'htmlall':'UTF-8'}</span>
                <h4>{$check.title|escape:'htmlall':'UTF-8'}</h4>
                <p>{$check.description|escape:'htmlall':'UTF-8'}</p>
              </div>
              <span class="rd-count">{$check.count|escape:'htmlall':'UTF-8'}</span>
            </div>

            {if $check.state == 'error'}
              <p class="rd-error">{$check.error|escape:'htmlall':'UTF-8'}</p>
            {else}
              <details class="rd-details">
                <summary>Afficher les résultats{if $check.capped} ({$rd_row_limit|intval} premières lignes){/if}</summary>
                <div class="table-responsive">
                  <table class="table table-condensed">
                    <thead>
                      <tr>
                        {foreach from=$check.columns item=label}
                          <th>{$label|escape:'htmlall':'UTF-8'}</th>
                        {/foreach}
                      </tr>
                    </thead>
                    <tbody>
                      {foreach from=$check.rows item=row}
                        <tr>
                          {foreach from=$check.columns key=column item=label}
                            <td>{if isset($row[$column])}{$row[$column]|escape:'htmlall':'UTF-8'}{/if}</td>
                          {/foreach}
                        </tr>
                      {/foreach}
                    </tbody>
                  </table>
                </div>
              </details>
            {/if}
          </li>
        {/foreach}
      </ul>
      <p class="alert alert-info" id="rd-no-results" hidden>Aucun contrôle ne correspond à votre recherche.</p>
    {/if}
  </div>
</div>
