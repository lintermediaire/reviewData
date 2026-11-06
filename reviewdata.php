<?php
/**
 * Revue de données : contrôles de cohérence SQL sur la base du PrestaShop courant.
 * Compatible PrestaShop 1.7.6 → 9.x
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ReviewData extends Module
{
    public const ADMIN_CONTROLLER = 'AdminReviewData';

    public function __construct()
    {
        $this->name = 'reviewdata';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Digital Leader';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Revue de données');
        $this->description = $this->l('Contrôles de cohérence (références orphelines, doublons, stocks…) sur la base de la boutique.');
        $this->confirmUninstall = $this->l('Supprimer le module Revue de données ?');
    }

    public function install()
    {
        return parent::install() && $this->installTab() && $this->restrictTabToSuperAdmin();
    }

    public function uninstall()
    {
        return $this->uninstallTab() && parent::uninstall();
    }

    /**
     * Le bouton « Configurer » du gestionnaire de modules ouvre directement le tableau de bord.
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_CONTROLLER));
    }

    private function installTab()
    {
        if ((int) Tab::getIdFromClassName(self::ADMIN_CONTROLLER) > 0) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = self::ADMIN_CONTROLLER;
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdvancedParameters');
        $tab->name = [];
        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Revue de données';
        }

        return (bool) $tab->add();
    }

    /**
     * À sa création, PrestaShop donne aussi l'onglet au profil de l'employé qui installe.
     * On retire ces droits à tous les profils sauf SuperAdmin pour masquer l'onglet du menu.
     * (Le contrôleur refuse de toute façon l'accès aux autres profils.)
     */
    private function restrictTabToSuperAdmin()
    {
        $slugs = [];
        foreach (['CREATE', 'READ', 'UPDATE', 'DELETE'] as $action) {
            $slugs[] = "'" . pSQL('ROLE_MOD_TAB_' . strtoupper(self::ADMIN_CONTROLLER) . '_' . $action) . "'";
        }

        return (bool) Db::getInstance()->execute(
            'DELETE a FROM `' . _DB_PREFIX_ . 'access` a
            INNER JOIN `' . _DB_PREFIX_ . 'authorization_role` r ON r.id_authorization_role = a.id_authorization_role
            WHERE r.slug IN (' . implode(',', $slugs) . ')
            AND a.id_profile <> ' . (int) _PS_ADMIN_PROFILE_
        );
    }

    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName(self::ADMIN_CONTROLLER);
        if ($idTab === 0) {
            return true;
        }

        $tab = new Tab($idTab);

        return (bool) $tab->delete();
    }
}
