<?php

namespace Hupa\FormLicense;

defined('ABSPATH') or die();

/**
 * REGISTER HUPA CUSTOM THEME
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */
final class RegisterBsFormular
{
    private static $bs_formular_instance;
    private string $plugin_dir;

    /**
     * @return static
     */
    public static function bs_formular_instance(): self
    {
        if (is_null(self::$bs_formular_instance)) {
            self::$bs_formular_instance = new self();
        }
        return self::$bs_formular_instance;
    }

    public function __construct(){
        $file_path_from_plugin_root = str_replace(WP_PLUGIN_DIR . '/', '', __DIR__);
        $path_array = explode('/', $file_path_from_plugin_root);
        $plugin_folder_name = reset($path_array);
        $this->plugin_dir = $plugin_folder_name;
    }

    public function init_bs_formular(): void
    {

        // TODO REGISTER LICENSE MENU
        if(!get_option('bs_formular_product_install_authorize')) {
            add_action('admin_menu', array($this, 'register_license_bs_formular_plugin'));
        }
        add_action('wp_ajax_BsFormularLicenceHandle', array($this, 'prefix_ajax_BsFormularLicenceHandle'));
        add_action( 'init', array( $this, 'bs_formular_license_site_trigger_check' ) );
        add_action( 'template_redirect',array($this, 'bs_formular_license_callback_trigger_check' ));
    }

    /**
     * =================================================
     * =========== REGISTER THEME ADMIN MENU ===========
     * =================================================
     */

    public function register_license_bs_formular_plugin(): void
    {
        $hook_suffix = add_menu_page(
            __('BS-Formular', 'bs-formular'),
            __('BS-Formular', 'bs-formular'),
            'manage_options',
            'bs-formular-license',
            array($this, 'bs_formular_license'),
            'dashicons-lock', 2
        );
        add_action('load-' . $hook_suffix, array($this, 'bs_formular_load_ajax_admin_options_script'));
    }


    public function bs_formular_license(): void
    {
        require 'activate-bs-formular-page.php';
    }


    /**
     * =========================================
     * =========== ADMIN AJAX HANDLE ===========
     * =========================================
     */

    public function bs_formular_load_ajax_admin_options_script(): void
    {
        add_action('admin_enqueue_scripts', array($this, 'load_bs_formular_admin_style'));
        $title_nonce = wp_create_nonce('bs_formular_license_handle');
        wp_register_script('bs-formular-selector-ajax-script', '', [], '', true);
        wp_enqueue_script('bs-formular-selector-ajax-script');
        wp_localize_script('bs-formular-selector-ajax-script', 'bs_formulare_license_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $title_nonce
        ));
    }

    /**
     * ==================================================
     * =========== THEME AJAX RESPONSE HANDLE ===========
     * ==================================================
     */

    public function prefix_ajax_BsFormularLicenceHandle(): void {
        $responseJson = null;
        check_ajax_referer( 'bs_formular_license_handle' );
        require 'bs-formular-license-ajax.php';
        wp_send_json( $responseJson );
    }

    /*===============================================
       TODO GENERATE CUSTOM SITES
    =================================================
    */
    public function bs_formular_license_site_trigger_check(): void {
        global $wp;
        $wp->add_query_var( $this->plugin_dir );
    }

    function bs_formular_license_callback_trigger_check(): void {
        $file_path_from_plugin_root = str_replace(WP_PLUGIN_DIR . '/', '', __DIR__);
        $path_array = explode('/', $file_path_from_plugin_root);
        $plugin_folder_name = reset($path_array);
        //$requestUri = base64_encode($plugin_folder_name);
       if ( get_query_var( $this->plugin_dir ) === $this->plugin_dir) {
            require 'api-request-page.php';
            exit;
        }
    }

    /**
     * ====================================================
     * =========== THEME ADMIN DASHBOARD STYLES ===========
     * ====================================================
     */

    public function load_bs_formular_admin_style(): void
    {
        wp_enqueue_style('bs-formular-license-style',plugins_url('bs-formular') . '/inc/license/assets/license-backend.css', array(), '');
        wp_enqueue_script('js-bs-formular-license', plugins_url('bs-formular') . '/inc/license/license-script.js', array(), '', true );
    }
}

$register_bs_formular = RegisterBsFormular::bs_formular_instance();
if (!empty($register_bs_formular)) {
	$register_bs_formular->init_bs_formular();
}
