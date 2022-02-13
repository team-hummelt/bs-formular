<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bs_formulare';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'bs_form_message';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'bs_post_eingang';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'bs_form_settings';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);


delete_option("jal_bs_formular_db_version");

delete_option("email_empfang_aktiv");
delete_option("email_abs_name");
delete_option("bs_abs_email");
delete_option("bs_form_smtp_host");
delete_option("bs_form_smtp_auth_check");
delete_option("bs_form_smtp_port");
delete_option("bs_form_email_benutzer");
delete_option("bs_form_email_passwort");
delete_option("bs_form_smtp_secure");

delete_option("bs_formular_product_install_authorize");
delete_option("bs_formular_client_id");
delete_option("bs_formular_client_secret");
delete_option("bs_formular_message");
delete_option("bs_formular_access_token");

delete_option("bs_formular_user_role");

