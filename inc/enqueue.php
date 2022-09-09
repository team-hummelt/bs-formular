<?php
defined('ABSPATH') or die();
/**
 * Jens Wiecker PHP Class
 * @package Jens Wiecker WordPress Plugin
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 *
 */

if (!function_exists('bootstrap_formular_public_style')) {
    function bootstrap_formular_public_style()
    {
        $ifHupaStarter = wp_get_theme('hupa-starter');
        $ifHupaStarterV2 = wp_get_theme('starter-theme-v2');
        if (!$ifHupaStarterV2->exists()) :
            if (!$ifHupaStarter->exists()) :
                $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/admin/css/font-awesome.css'));
                wp_enqueue_style('bootstrap-formular-font-awesome', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/font-awesome.css', array(), $modificated, '');
                $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/public/css/bs/bootstrap.min.css'));
                wp_enqueue_style('bootstrap-formular-namespace', BS_FORMULAR_PLUGIN_URL . '/assets/public/css/bs/bootstrap.min.css', array(), $modificated, '');
                $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/public/js/bs/bootstrap.bundle.min.js'));
                wp_enqueue_script('bootstrap-bs-formular', BS_FORMULAR_PLUGIN_URL . '/assets/public/js/bs/bootstrap.bundle.min.js', array(), $modificated, true);
            endif;
        endif;

        //filepond
        //$modificated = date( 'YmdHi', filemtime( BS_FORMULAR_PLUGIN_DIR . '/assets/public/css/filepond/filepond.min.css' ) );
        //wp_enqueue_style( 'bootstrap-formular-filepond-style', BS_FORMULAR_PLUGIN_URL . '/assets/public/css/filepond/filepond.min.css', array(), $modificated, '');

        $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/public/css/bs-formular-public.css'));
        wp_enqueue_style('bootstrap-formular-public-style', BS_FORMULAR_PLUGIN_URL . '/assets/public/css/bs-formular-public.css', array(), $modificated, '');
        $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/public/js/bs-formular-public.js'));
        wp_enqueue_script('bootstrap-formular-public-script', BS_FORMULAR_PLUGIN_URL . '/assets/public/js/bs-formular-public.js', array(), $modificated, true);
        //filepond
        $modificated = date('YmdHi', filemtime(BS_FORMULAR_PLUGIN_DIR . '/assets/public/js/filepond/filepond-config.js'));
        wp_enqueue_script('bootstrap-formular-filepond-script', BS_FORMULAR_PLUGIN_URL . '/assets/public/js/filepond/filepond-config.js', array(), $modificated, true);

    }
}
add_action('wp_enqueue_scripts', 'bootstrap_formular_public_style');

if (!function_exists('bootstrap_formular_admin_style')) {

    function bootstrap_formular_admin_style()
    {
        $plugin_data = get_file_data(plugin_dir_path(__DIR__) . 'bs-formular.php', array('Version' => 'Version'), false);
        global $bs_form_version;
        $bs_form_version = $plugin_data['Version'];
        // TODO DASHBOARD WP STYLES
        wp_enqueue_style('bs-form-admin-custom-tools', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/tools.css', array(), $bs_form_version, false);
        wp_enqueue_style('bs-formular-fonts', BS_FORMULAR_PLUGIN_URL . '/assets/admin/css/Glyphter.css', array(), $bs_form_version, false);
    }

}
add_action('admin_enqueue_scripts', 'bootstrap_formular_admin_style');
