<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://jenswiecker.de
 * @since      1.0.0
 *
 * @package    Bs_Formular
 * @subpackage Bs_Formular/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Bs_Formular
 * @subpackage Bs_Formular/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Bs_Formular_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option("bs_formular_product_install_authorize");
		delete_option("bs_formular_client_id");
		delete_option("bs_formular_client_secret");
		delete_option("bs_formular_message");
		delete_option("bs_formular_access_token");
		$infoTxt = 'deaktiviert am ' . date('d.m.Y H:i:s')."\r\n";
		file_put_contents(BS_FORMULAR_PLUGIN_DIR.'/bs-formular.txt', $infoTxt,  FILE_APPEND | LOCK_EX);
	}
}
