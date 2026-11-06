<?php
/**
 * Back-office > Paramètres avancés > Revue de données
 * Exécute les contrôles de config/checks.php sur la base du PrestaShop courant.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminReviewDataController extends ModuleAdminController
{
    private const ROW_LIMIT = 50;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS($this->module->getPathUri() . 'views/css/admin.css');
        $this->addJS($this->module->getPathUri() . 'views/js/admin.js');
    }

    /**
     * Accès réservé au profil SuperAdmin, quelles que soient les permissions de l'onglet.
     */
    public function viewAccess($disable = false)
    {
        $employee = $this->context->employee;
        if (!$employee || (int) $employee->id_profile !== (int) _PS_ADMIN_PROFILE_) {
            return false;
        }

        return parent::viewAccess($disable);
    }

    public function initContent()
    {
        // Sans droit d'accès, le parent ajoute le message d'erreur : on n'exécute aucune requête.
        parent::initContent();
        if (!$this->viewAccess()) {
            return;
        }

        $checks = require _PS_MODULE_DIR_ . $this->module->name . '/config/checks.php';
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $listed = [];
        $totalRows = 0;
        $capped = false;
        $clean = 0;
        $errors = 0;

        foreach ($checks as $check) {
            $sql = str_replace('%s', _DB_PREFIX_, $check['sql']) . ' LIMIT ' . (self::ROW_LIMIT + 1);
            $rows = null;
            $error = null;

            try {
                $rows = $db->executeS($sql, true, false);
                if ($rows === false) {
                    $error = $db->getMsgError() ?: 'Erreur SQL inconnue.';
                }
            } catch (Exception $exception) {
                // En mode debug, PrestaShop lève une PrestaShopDatabaseException.
                $error = $exception->getMessage();
            }

            if ($error !== null) {
                ++$errors;
                PrestaShopLogger::addLog('reviewdata: ' . $check['id'] . ' : ' . $error, 2, null, 'Module', null, false);
                $listed[] = $check + ['state' => 'error', 'error' => $error, 'rows' => [], 'count' => 'Erreur', 'capped' => false];
                continue;
            }

            $rows = is_array($rows) ? $rows : [];
            if ($rows === []) {
                ++$clean;
                continue;
            }

            $hasMore = count($rows) > self::ROW_LIMIT;
            $capped = $capped || $hasMore;
            $totalRows += $hasMore ? self::ROW_LIMIT : count($rows);
            $listed[] = $check + [
                'state' => 'issues',
                'error' => null,
                'rows' => array_slice($rows, 0, self::ROW_LIMIT),
                'count' => $hasMore ? self::ROW_LIMIT . '+' : count($rows),
                'capped' => $hasMore,
            ];
        }

        $this->context->smarty->assign([
            'rd_listed' => $listed,
            'rd_total_checks' => count($checks),
            'rd_total_rows' => $totalRows . ($capped ? '+' : ''),
            'rd_clean' => $clean,
            'rd_errors' => $errors,
            'rd_db_name' => _DB_NAME_,
            'rd_prefix' => _DB_PREFIX_,
            'rd_date' => date('d/m/Y H:i'),
            'rd_refresh_url' => $this->context->link->getAdminLink('AdminReviewData'),
            'rd_row_limit' => self::ROW_LIMIT,
        ]);

        $this->setTemplate('dashboard.tpl');
    }
}
