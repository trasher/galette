<?php
/**
 * Dependency injection configuration
 *
 * PHP version 5
 *
 * Copyright © 2003-2014 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Functions
 * @package   Galette
 *
 * @author    Frédéric Jaqcuot <unknown@unknow.com>
 * @author    Georges Khaznadar (password encryption, images) <unknown@unknow.com>
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2003-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 */

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Register Smarty View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Smarty(
        rtrim(GALETTE_ROOT . GALETTE_TPL_SUBDIR, DIRECTORY_SEPARATOR),
        [
            'cacheDir' => rtrim(GALETTE_CACHE_DIR, DIRECTORY_SEPARATOR),
            'compileDir' => rtrim(GALETTE_COMPILE_DIR, DIRECTORY_SEPARATOR),
            'pluginsDir' => [
                GALETTE_ROOT . 'includes/smarty_plugins'
            ]
        ]
    );

    // Add Slim specific plugins
    $view->addSlimPlugins($c['router'], $c['request']->getUri());

    $smarty = $view->getSmarty();
    $smarty->inheritance_merge_compiled_includes = false;

    $smarty->assign('flash', $c->get('flash'));

    $smarty->assign('login', $c->login);
    $smarty->assign('logo', $c->logo);
    $smarty->assign('tpl', $smarty);
    $smarty->assign('headers', $c->plugins->getTplHeaders());
    $smarty->assign('plugin_actions', $c->plugins->getTplAdhActions());
    $smarty->assign(
        'plugin_batch_actions',
        $c->plugins->getTplAdhBatchActions()
    );
    $smarty->assign(
        'plugin_detailled_actions',
        $c->plugins->getTplAdhDetailledActions()
    );
    $smarty->assign('jquery_dir', 'js/jquery/');
    $smarty->assign('jquery_version', JQUERY_VERSION);
    $smarty->assign('jquery_migrate_version', JQUERY_MIGRATE_VERSION);
    $smarty->assign('jquery_ui_version', JQUERY_UI_VERSION);
    $smarty->assign('jquery_markitup_version', JQUERY_MARKITUP_VERSION);
    $smarty->assign('jquery_jqplot_version', JQUERY_JQPLOT_VERSION);
    $smarty->assign('scripts_dir', 'js/');
    $smarty->assign('PAGENAME', basename($_SERVER['SCRIPT_NAME']));
    $smarty->assign('galette_base_path', './');
    $smarty->assign('GALETTE_VERSION', GALETTE_VERSION);
    $smarty->assign('GALETTE_MODE', GALETTE_MODE);

    /*if ($this->parserConfigDir) {
        $instance->setConfigDir($this->parserConfigDir);
    }*/

    $smarty->assign('template_subdir', GALETTE_THEME);
    foreach ($c->plugins->getTplAssignments() as $k => $v) {
        $smarty->assign($k, $v);
    }
    /** galette_lang should be removed and languages used instead */
    $smarty->assign('galette_lang', $c->i18n->getAbbrev());
    $smarty->assign('languages', $c->i18n->getList());
    $smarty->assign('plugins', $c->plugins);
    $smarty->assign('preferences', $c->preferences);
    $smarty->assign('pref_slogan', $c->preferences->pref_slogan);
    $smarty->assign('pref_theme', $c->preferences->pref_theme);
    $smarty->assign(
        'pref_editor_enabled',
        $c->preferences->pref_editor_enabled
    );
    $smarty->assign('pref_mail_method', $c->preferences->pref_mail_method);
    $smarty->assign('existing_mailing', $c->get('session')->mailing !== null);
    $smarty->assign('require_tabs', null);
    $smarty->assign('contentcls', null);
    $smarty->assign('require_cookie', false);
    $smarty->assign('additionnal_html_class', null);
    $smarty->assign('require_calendar', null);
    $smarty->assign('head_redirect', null);
    $smarty->assign('error_detected', null);
    $smarty->assign('warning_detected', null);
    $smarty->assign('success_detected', null);
    $smarty->assign('color_picker', null);
    $smarty->assign('require_sorter', null);
    $smarty->assign('require_dialog', null);
    $smarty->assign('require_tree', null);
    $smarty->assign('html_editor', null);
    $smarty->assign('require_charts', null);

    return $view;
};

// Flash messages
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};

$container['plugins'] = function ($c) use ($app) {
    $plugins = new Galette\Core\Plugins($c->preferences);
    $i18n = $c->get('i18n');
    $plugins->loadModules(GALETTE_PLUGINS_PATH, $i18n->getFileName());
    return $plugins;
};

$container['i18n'] = function ($c) {
    return new Galette\Core\I18n();
};

$container['zdb'] = function ($c) {
    $zdb = new Galette\Core\Db();
    return $zdb;
};

$container['preferences'] = function ($c) {
    return new Galette\Core\Preferences($c->zdb);
};

$container['session_name'] = function ($c) use ($session_name) {
    return $session_name;
};

$container['login'] = function ($c) {
    $login = $c->get('session')->login;
    if (!$login) {
        $session = $c->get('session');
        $login = new Galette\Core\Login(
            $c->get('zdb'),
            $c->get('i18n'),
            $c->get('session')
        );
    } else {
        $login->setDb($c->get('zdb'));
    }
    return $login;
};

$container['session'] = function ($c) {
    $session = new \RKA\Session();
    return $session;
};

$container['logo'] = function ($c) {
    return new Galette\Core\Logo();
};

$container['history'] = function ($c) {
    return new Galette\Core\History();
};

$container['acls'] = function ($c) {
    $acls = [
        'preferences'       => 'admin',
        'store-preferences' => 'admin',
        'dashboard'         => 'groupmanager',
        'sysinfos'          => 'staff',
        'charts'            => 'staff',
        'plugins'           => 'admin',
        'history'           => 'staff',
        'members'           => 'groupmanager',
        'filter-memberslist'=> 'groupmanager',
        'advanced-search'   => 'groupmanager',
        'batch-memberslist' => 'groupmanager',
        'mailing'           => 'staff',
        'csv-memberslist'   => 'staff',
        'groups'            => 'groupmanager',
        'me'                => 'member',
        'member'            => 'member',
        'pdf-members-cards' => 'member',
        'pdf-members-labels'=> 'groupmanager',
        'mailings'          => 'staff',
        'contributions'     => 'staff',
        'transactions'      => 'staff',
        'payments_filter'   => 'member',
        'editmember'        => 'member',
        'storemembers'      => 'member',
        'impersonate'       => 'superadmin',
        'unimpersonate'     => 'member',
        'reminders'         => 'staff',
        'export'            => 'staff',
        'doExport'          => 'staff',
        'removeCsv'         => 'staff',
        'getCsv'            => 'staff',
        'import'            => 'staff',
        'doImport'          => 'staff',
        'importModel'       => 'staff',
        'getImportModel'    => 'staff',
        'storeImportModel'  => 'staff',
        'uploadImportFile'  => 'staff',
        'pdfModels'         => 'staff',
        'titles'            => 'staff',
        'removeTitle'       => 'staff',
        'editTitle'         => 'staff',
        'texts'             => 'staff',
        'transaction'       => 'staff',
        'doEditTransaction' => 'staff',
        'contribution'      => 'staff',
        'attendance_sheet_details'  => 'groupmanager',
        'attendance_sheet'  => 'groupmanager',
        'entitleds'         => 'staff',
        'editEntitled'      => 'staff',
        'removeEntitled'    => 'staff',
        'dynamicTranslations'       => 'staff',
        'editDynamicTranslation'    => 'staff',
        'attach_contribution'       => 'staff',
        'detach_contribution'       => 'staff',
        'removeContributions'       => 'staff',
        'pdf_groups'                => 'groupmanager',
        'ajax_group'                => 'groupmanager',
        'ajax_groupname_unique'     => 'staff',
        'add_group'                 => 'staff',
        'removeGroup'               => 'staff',
        'doRemoveGroup'             => 'staff',
        'doEditGroup'               => 'staff',
        'adhesionForm'              => 'staff',
        'removeMember'              => 'staff',
        'doRemoveMember'            => 'staff',
        'doRemoveContribution'      => 'staff',
        'configureCoreFields'       => 'admin',
        'configureDynamicFields'    => 'admin',
        'storeCoreFieldsConfig'     => 'admin',
        'editDynamicFields'         => 'admin'
    ];

    //load user defined ACLs
    if (file_exists(GALETTE_CONFIG_PATH  . 'local_acls.inc.php')) {
        $acls = array_merge($acls, $local_acls);
    }

    return $acls;
};

$container['texts_fields'] = function ($c) {
    include_once GALETTE_ROOT . 'includes/fields_defs/texts_fields.php';
    return $texts_fields;
};

$container['members_fields'] = function ($c) {
    include_once GALETTE_ROOT . 'includes/fields_defs/members_fields.php';
    return $members_fields;
};

$container['members_fields_cats'] = function ($c) {
    include_once GALETTE_ROOT . 'includes/fields_defs/members_fields_cats.php';
    return $members_fields_cats;
};

$container['pdfmodels_fields'] = function ($c) {
    include_once GALETTE_ROOT . 'includes/fields_defs/pdfmodels_fields.php';
    return $pdfmodels_fields;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], $setting['logger']['level']));
    return $logger;
};

$container['fields_config'] = function ($c) {
    $fc = new Galette\Entity\FieldsConfig(
        $c->get('zdb'),
        Galette\Entity\Adherent::TABLE,
        $c->get('members_fields'),
        $c->get('members_fields_cats')
    );
    return $fc;
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container['App\Action\HomeAction'] = function ($c) {
    return new App\Action\HomeAction($c->get('view'), $c->get('logger'));
};

//For bad existing globals can be used...
$hist = $container['history'];
$login = $container['login'];
$zdb = $container['zdb'];

/**
 * Language instantiation
 */
if (isset($session['lang'])) {
    $i18n = unserialize($session['lang']);
} else {
    $i18n = new Galette\Core\I18n();
}

if (isset($_POST['pref_lang'])
    && (strpos($_SERVER['PHP_SELF'], 'self_adherent.php') !== false
    || strpos($_SERVER['PHP_SELF'], 'install/index.php') !== false)
) {
    $_GET['pref_lang'] = $_POST['pref_lang'];
}
if (isset($_GET['pref_lang'])) {
    $i18n->changeLanguage($_GET['pref_lang']);
}
$session['lang'] = serialize($i18n);
require_once GALETTE_ROOT . 'includes/i18n.inc.php';
