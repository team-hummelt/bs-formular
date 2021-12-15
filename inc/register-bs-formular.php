<?php

namespace Form\BsFormular;

use JetBrains\PhpStorm\NoReturn;

defined('ABSPATH') or die();

/**
 * BS-Formular Plugin
 * @package Hummelt & Partner Gutenberg Block Plugin
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */
final class RegisterBsFormularPlugin
{

    private static $bs_form_instance;
    private bool $dependencies;

    /**
     * @return static
     */
    public static function bs_form_instance(): self
    {
        if (is_null(self::$bs_form_instance)) {
            self::$bs_form_instance = new self();
        }
        return self::$bs_form_instance;
    }

    public function __construct()
    {
        $this->dependencies = $this->check_dependencies();
        add_action('admin_notices', array($this, 'showSitemapInfo'));
    }

    function showSitemapInfo()
    {
        if (get_transient('show_lizenz_info')) {
            echo '<div class="error"><p>' .
                'BS-Formular ungültige Lizenz: Zum Aktivieren geben Sie Ihre Zugangsdaten ein.' .
                '</p></div>';
        }
    }

    /**
     * ============================================
     * =========== REGISTER BS-Formular ===========
     * ============================================
     */
    public function init_bs_formular()
    {

        if (!$this->dependencies) {
            return;
        }

        // TODO GUTENBERG PLUGIN
        add_action('init', array($this, 'gutenberg_block_bootstrap_formular_register'));

        // TODO Create Database
        add_action('init', array($this, 'boostrap_formular_create_db'));

        //add_action( 'init', array( $this, 'bootstrap_formular_removes_api_endpoints_for_not_logged_in' ) );
        add_action('enqueue_block_editor_assets', array($this, 'bs_formular_plugin_editor_block_scripts'));
        add_action('enqueue_block_assets', array($this, 'bs_formular_plugin_public_scripts'));

        // TODO Load Textdomain
        add_action('init', array($this, 'load_bs_formular_textdomain'));

        //TODO REGISTER ADMIN MAPS PAGE
        add_action('admin_menu', array($this, 'register_bs_formular_menu'));


        //TODO PUBLIC SITES TRIGGER
        add_action('template_redirect', array($this, 'bs_formular_public_one_trigger_check'));

        add_action('init', array($this, 'bs_formular_site_trigger_check'));
        add_action('template_redirect', array($this, 'bs_formular_callback_trigger_check'));

        // TODO AJAX ADMIN AND PUBLIC RESPONSE HANDLE
        add_action('wp_ajax_BsFormularHandle', array($this, 'prefix_ajax_BsFormularHandle'));
        add_action('wp_ajax_nopriv_BsFormularNoAdmin', array($this, 'prefix_ajax_BsFormularNoAdmin'));
        add_action('wp_ajax_BsFormularNoAdmin', array($this, 'prefix_ajax_BsFormularNoAdmin'));
        //TODO AJAX FILE-UPLOAD
        add_action('wp_ajax_nopriv_BsFormularFileUploadNoAdmin', array($this, 'prefix_ajax_BsFormularFileUploadNoAdmin'));
        add_action('wp_ajax_BsFormularFileUploadNoAdmin', array($this, 'prefix_ajax_BsFormularFileUploadNoAdmin'));


        //TODO REGISTER WP-MAIL SMTP
        add_action('phpmailer_init', array($this, 'bs_formular_mailer_phpmailer_configure'));
        add_filter('wp_mail_content_type', array($this, 'bs_formular_mail_content_type'));
        add_action('wp_mail_failed', array($this, 'bs_formular_log_mailer_errors', 10, 1));
    }


    public function load_bs_formular_textdomain(): void
    {
        load_plugin_textdomain('bs-formular', false, dirname(BS_FORMULAR_SLUG_PATH) . '/language/');
    }

    public function register_bs_formular_menu(): void
    {
        $hook_suffix = add_menu_page(
            __('Formulare', 'bs-formular'),
            __('Formulare', 'bs-formular'),
            'manage_options',
            'bs-formular',
            array($this, 'admin_bs_formular_page'),
            'dashicons-email-alt', 7
        );

        add_action('load-' . $hook_suffix, array($this, 'bs_formular_load_ajax_admin_options_script'));
    }

    /**
     * ===================================
     * =========== ADMIN PAGES ===========
     * ===================================
     */
    public function admin_bs_formular_page(): void
    {
        require 'admin-pages/bs-formulare.php';
    }

    public function bs_formular_load_ajax_admin_options_script(): void
    {
        add_action('admin_enqueue_scripts', array($this, 'load_bs_formular_admin_style'));
        $title_nonce = wp_create_nonce('bs_formular_admin_handle');

        wp_register_script('bs-formular-ajax-script', '', [], '', true);
        wp_enqueue_script('bs-formular-ajax-script');
        wp_localize_script('bs-formular-ajax-script', 'bs_form_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $title_nonce,
        ));
    }

    /**
     * ========================================================
     * =========== BS-FORMULAR AJAX RESPONSE HANDLE ===========
     * ========================================================
     */
    public function bs_formular_public_one_trigger_check()
    {
        $fileLang = [
            //Datei auswählen
            'datei_select' => __('Select file', 'bs-formular'),
            //Datei hier per Drag & Drop ablegen.
            'drag_file' => __('Drag and drop the file here.', 'bs-formular'),
            //Fehler beim Upload
            'upload_err' => __('Upload error', 'bs-formular'),
            //erneut versuchen
            'erneut_vers' => __('Try again', 'bs-formular'),
            //zum Abbrechen antippen
            'tap_cancel' => __('Tap to cancel', 'bs-formular'),
            //zum Löschen klicken
            'click_delete' => __('Click to delete', 'bs-formular'),
            //entfernen
            'remove' => __('remove', 'bs-formular'),

            //Datei ist zu groß
            'file_large' => __('File is too large', 'bs-formular'),
            //Maximale Dateigröße ist {filesize}
            'max_filesize' => __('Maximum file size is {filesize}', 'bs-formular'),
            //Maximale Gesamtgröße überschritten
            'max_total_size' => __('Maximum total size exceeded', 'bs-formular'),
            //Maximale Gesamtgröße der Datei ist {filesize}
            'max_total_file' => __('Maximum total size of the file is {filesize}', 'bs-formular'),
            //Ungültiger Dateityp
            'invalid_type' => __('Invalid file type', 'bs-formular'),
            //Erwartet {allButLastType} oder {lastType}
            'expects' => __('Expects {allButLastType} or {lastType}', 'bs-formular'),
        ];

        $title_nonce = wp_create_nonce('bs_form_public_handle');
        wp_register_script('bs-formular-public-ajax-script', '', [], '', true);
        wp_enqueue_script('bs-formular-public-ajax-script');
        wp_localize_script('bs-formular-public-ajax-script', 'bs_form_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $title_nonce,
            'file_size' => get_option('file_max_size') * 1024 * 1024,
            'file_size_mb' => get_option('file_max_size'),
            'file_size_all_mb' => get_option('file_max_all_size'),
            'max_files' => get_option('upload_max_files'),
            'assets_url' => BS_FORMULAR_PLUGIN_ASSETS_URL,
            'language' => $fileLang
        ));
    }

    public function prefix_ajax_BsFormularHandle(): void
    {
        $responseJson = null;
        check_ajax_referer('bs_formular_admin_handle');
        require 'bs-form-ajax/bs-form-admin-ajax.php';
        wp_send_json($responseJson);
    }

    /*===============================================
    TODO AJAX PUBLIC RESPONSE HANDLE
    =================================================
    */
    public function prefix_ajax_BsFormularNoAdmin(): void
    {
        $responseJson = null;
        check_ajax_referer('bs_form_public_handle');
        require 'bs-form-ajax/bs-form-public-ajax.php';
        wp_send_json($responseJson);
    }

    /*===============================================
    TODO AJAX PUBLIC UPLOAD HANDLE
    =================================================
    */
    public function prefix_ajax_BsFormularFileUploadNoAdmin(): void
    {
        $responseJson = null;
        check_ajax_referer('bs_form_public_handle');
        require 'bs-form-ajax/bs-form-public-upload-files.php';
        wp_send_json($responseJson);
    }

    /**
     * =====================================================
     * =========== BS-FORMULAR PHP-MAILER CONFIG ===========
     * =====================================================
     */
    public function bs_formular_mailer_phpmailer_configure($phpmailer)
    {
        if (get_option('bs_form_smtp_host')) {
            $smtpCheck = apply_filters('bs_form_get_smtp_test', false);
            if ($smtpCheck['status']) {
                $phpmailer->isSMTP();
                $phpmailer->Host = get_option('bs_form_smtp_host');
                $phpmailer->SMTPAuth = get_option('bs_form_smtp_auth_check');
                $phpmailer->Port = get_option('bs_form_smtp_port');
                $phpmailer->Username = get_option('bs_form_email_benutzer');
                $phpmailer->Password = get_option('bs_form_email_passwort');
                $phpmailer->SMTPSecure = get_option('bs_form_smtp_secure');
                $phpmailer->SMTPDebug = 0;
                $phpmailer->CharSet = "utf-8";
            }
        }
    }

    public function bs_formular_mail_content_type(): string
    {
        return "text/html";
    }

    public function bs_formular_log_mailer_errors($wp_error)
    {
        $dir = BS_FORMULAR_INC . 'log' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return '';
            }
        }

        $file = $dir . 'mail-error.log';
        $current = "Mailer Error: " . $wp_error->get_error_message() . "\n";
        file_put_contents($file, $current, LOCK_EX);
        // $wp_error->get_error_message();
    }


    /*===============================================
       TODO GENERATE CUSTOM SITES
    =================================================
    */
    public function bs_formular_site_trigger_check(): void
    {
        global $wp;
        $set = '';
        $file = BS_FORMULAR_INC . 'optionen/Mailer/email-template.html';
        //file_put_contents($file, $set, LOCK_EX);
        $wp->add_query_var(BS_FORMULAR_QUERY_VAR);
    }

    function bs_formular_callback_trigger_check(): void
    {
        if (get_query_var(BS_FORMULAR_QUERY_VAR) == BS_FORMULAR_QUERY_URI) {
            require 'optionen/Mailer/get-bs-formular-email.php';
            exit;
        }
    }

    /**
     * ======================================================
     * =========== THEME CREATE / UPDATE OPTIONEN ===========
     * ======================================================
     */

    public function boostrap_formular_create_db(): void
    {
        require 'optionen/filter/database/bs-formular-database.php';
        do_action('bs_formular_plugin_update_dbCheck', false);
    }

    /**
     * ==========================================================
     * =========== REGISTER GUTENBERG FORMULAR PLUGIN ===========
     * ==========================================================
     */
    public function gutenberg_block_bootstrap_formular_register()
    {
        register_block_type('bs/bootstrap-formular', array(
            'render_callback' => 'callback_bootstrap_formular_block',
            'editor_script' => 'gutenberg-bootstrap-formular-block',
        ));

        add_filter('gutenberg_block_bs_formular_render', 'gutenberg_block_bs_formular_render_filter', 10, 20);
    }

    /**
     * =======================================================================
     * =========== REGISTER GUTENBERG BS-FORMULAR JAVASCRIPT | CSS ===========
     * =======================================================================
     */
    public function bs_formular_plugin_editor_block_scripts(): void
    {
        $plugin_asset = require BS_FORMULAR_GUTENBERG_DATA . 'index.asset.php';

        // Scripts.
        wp_enqueue_script(
            'gutenberg-bootstrap-formular-block',
            BS_FORMULAR_GUTENBERG_URL . 'index.js',
            $plugin_asset['dependencies'], BS_FORMULAR_PLUGIN_VERSION
        );

        // Styles.
        wp_enqueue_style(
            'gutenberg-bootstrap-formular-block', // Handle.
            BS_FORMULAR_GUTENBERG_URL . 'index.css', array(), BS_FORMULAR_PLUGIN_VERSION
        );

        wp_register_script('bs-formular-rest-gutenberg-js-localize', '', [], BS_FORMULAR_PLUGIN_VERSION, true);
        wp_enqueue_script('bs-formular-rest-gutenberg-js-localize');
        wp_localize_script('bs-formular-rest-gutenberg-js-localize',
            'WPBSFRestObj',
            array(
                'url' => esc_url_raw(rest_url('bs-formular-endpoint/v1/method/')),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );
    }

    public function bs_formular_plugin_public_scripts()
    {

    }

    /**
     * ================================================
     * =========== REMOVE REST API ENDPOINT ===========
     * ================================================
     */
    public function bootstrap_formular_removes_api_endpoints_for_not_logged_in(): void
    {
        if (!is_user_logged_in()) {
            // Removes WordPress endpoints:
            remove_action('rest_api_init', 'create_initial_rest_routes', 99);

            // Removes Woocommerce endpoints
            if (function_exists('WC')) {
                remove_action('rest_api_init', array(WC()->api, 'register_rest_routes'), 10);
            }
        }
    }


    /*======================================
    TODO VERSIONS CHECK
    ========================================
    */
    public function check_dependencies(): bool
    {
        global $wp_version;
        if (version_compare(PHP_VERSION, BS_FORMULAR_MIN_PHP_VERSION, '<') || $wp_version < BS_FORMULAR_MIN_WP_VERSION) {
            $this->maybe_self_deactivate();
            return false;
        }
        return true;
    }

    /*=======================================
    TODO SELF-DEACTIVATE
    =========================================
     */
    public function maybe_self_deactivate(): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        deactivate_plugins(BS_FORMULAR_SLUG_PATH);
        add_action('admin_notices', array($this, 'self_deactivate_notice'));
    }

    /*==============================================
    TODO DEACTIVATE-ADMIN-NOTIZ
    ================================================
     */
    #[NoReturn] public function self_deactivate_notice()
    {
        echo sprintf('<div class="error" style="margin-top:5rem"><p>' . __('This plugin has been disabled because it requires a PHP version greater than %s and a WordPress version greater than %s. Your PHP version can be updated by your hosting provider.', 'lva-buchungssystem') . '</p></div>', BS_FORMULAR_MIN_PHP_VERSION, BS_FORMULAR_MIN_WP_VERSION);
        exit();
    }

    /**
     * ======================================================
     * =========== BS-FORM ADMIN DASHBOARD STYLES ===========
     * ======================================================
     */
    public function load_bs_formular_admin_style(): void
    {
        $plugin_data = get_file_data(plugin_dir_path(__DIR__) . 'bs-formular.php', array('Version' => 'Version'), false);
        global $bs_form_version;
        $bs_form_version = $plugin_data['Version'];

        //TODO FontAwesome / Bootstrap
        wp_enqueue_style('bs-formular-admin-bs-style', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/bs/bootstrap.min.css', array(), $bs_form_version, false);
        // TODO ADMIN ICONS
        wp_enqueue_style('bs-formular-admin-icons-style', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/font-awesome.css', array(), $bs_form_version, false);
        // TODO DASHBOARD STYLES
        wp_enqueue_style('bs-formular-admin-dashboard-style', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/admin-dashboard-style.css', array(), $bs_form_version, false);
        wp_enqueue_style('bs-formular-data-table-style', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/tools/dataTables.bootstrap5.min.css', array(), $bs_form_version, false);

        // TODO ADMIN localize Script
        wp_register_script('bs-formular-admin-js-localize', '', [], '', true);
        wp_enqueue_script('bs-formular-admin-js-localize');
        wp_localize_script('bs-formular-admin-js-localize',
            'bs_form',
            array(
                'admin_url' => BS_FORMULAR_PLUGIN_URL,
                'data_table' => BS_FORMULAR_PLUGIN_URL . '/assets/json/DataTablesGerman.json',
                'site_url' => get_bloginfo('url'),
            )
        );

        wp_enqueue_script('jquery');

        wp_enqueue_script('bs-formular-bs', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/bs/bootstrap.bundle.min.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-tiny5', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/tools/tiny5/tinymce.min.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-tiny5-jquery', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/tools/tiny5/jquery.tinymce.min.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-init-tiny5', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/tools/tiny5/form-tiny-init.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-jquery-table-js', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/tools/data-table/jquery.dataTables.min.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs5-data-table', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/tools/data-table/dataTables.bootstrap5.min.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-data-js', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/admin-formulare.js', array(), $bs_form_version, true);
        wp_enqueue_script('bs-formular-table-bs-js', BS_FORMULAR_PLUGIN_URL . '/assets/admin/js/formular-table.js', array(), $bs_form_version, true);
    }
}

global $bs_form_selector;
$bs_form_selector = RegisterBsFormularPlugin::bs_form_instance();
$bs_form_selector->init_bs_formular();

require 'gutenberg/bootstrap-form-render.php';
require 'gutenberg/bootstrap-form-endpoint.php';

