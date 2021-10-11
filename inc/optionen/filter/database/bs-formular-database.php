<?php


defined( 'ABSPATH' ) or die();

		function bs_formular_theme_jal_install() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;

			$table_name = $wpdb->prefix . 'bs_formulare';
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        shortcode varchar(14) NOT NULL UNIQUE,
        bezeichnung varchar(50) NOT NULL,
        input_class varchar(64) NULL,
        form_class varchar(64) NULL,
        btn_class varchar(64) NULL,
        btn_icon varchar(64) NULL,
        label_class varchar(64) NULL,
        class_aktiv tinyint(1) NOT NULL DEFAULT 0,
        layout text NOT NULL,
        inputs text NOT NULL,
        user_layout text NOT NULL,
        form_meldungen text NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
     ) $charset_collate;";
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'bs_form_message';
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        formId mediumint(9) NOT NULL UNIQUE,
        betreff varchar(128) NULL,
        email_at varchar(50) NOT NULL,
        email_cc text NULL,
        message text NOT NULL,
        response_aktiv mediumint(1) NOT NULL DEFAULT 0,
        auto_betreff varchar(128) NULL,
        auto_msg text NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
     ) $charset_collate;";
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'bs_post_eingang';
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        form_id mediumint(9) NOT NULL,
        betreff varchar(128) NULL,
        email_at varchar(50) NOT NULL,
        abs_ip varchar(50) NOT NULL,
        message text NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
     ) $charset_collate;";
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'bs_form_settings';
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL,
        form_meldungen text NULL,
       PRIMARY KEY (id)
     ) $charset_collate;";
			dbDelta($sql);
			apply_filters('bs_form_set_default_settings', false);

			update_option("jal_bs_formular_db_version", BS_FORMULAR_PLUGIN_DB_VERSION);
		}

function bs_formular_plugin_update_dbCheck()
{
	if (get_option('jal_bs_formular_db_version') != BS_FORMULAR_PLUGIN_DB_VERSION) {
		bs_formular_theme_jal_install();
	}
}

add_action('bs_formular_plugin_update_dbCheck', 'bs_formular_plugin_update_dbCheck', false);
add_action('bs_formular_plugin_create_db', 'bs_formular_theme_jal_install', false);