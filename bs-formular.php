<?php
/**
 * WP BS-Formular
 *
 *
 * @link              https://www.hummelt-werbeagentur.de/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       BS-Formular - Boostrap Formular Plugin
 * Plugin URI:        https://www.hummelt-werbeagentur.de/leistungen/
 * Description:       Bootstrap Formulare mit verschiedenen Ausgabeoptionen.
 * Version:           1.0.7
 * Author:            Jens Wiecker
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:      8.0
 * Requires at least: 5.8
 * Tested up to:      5.7
 * Stable tag:        1.0.7
 */

defined( 'ABSPATH' ) or die();

//DATABASE VERSION
const BS_FORMULAR_PLUGIN_DB_VERSION = '1.0.2';
const BS_FORMULAR_MIN_PHP_VERSION = '8.0';
const BS_FORMULAR_MIN_WP_VERSION = '5.7';
const SET_EMAIL_DEFAULT_MELDUNGEN = true;
const BS_FORMULAR_QUERY_VAR = 'get-bs-form-email';
const BS_FORMULAR_QUERY_URI = 1206711901102021;
//Settings ID
const BS_FORMULAR_SETTINGS_ID = 1;
//PLUGIN VERSION
$plugin_data = get_file_data(dirname(__FILE__) . '/bs-formular.php', array('Version' => 'Version'), false);
define( "BS_FORMULAR_PLUGIN_VERSION", $plugin_data['Version']);
//PLUGIN ROOT PATH
define('BS_FORMULAR_PLUGIN_DIR', dirname(__FILE__));
//PLUGIN SLUG
define('BS_FORMULAR_SLUG_PATH', plugin_basename(__FILE__));
define('BS_FORMULAR_BASENAME', plugin_basename(__DIR__));
//PLUGIN URL
define('BS_FORMULAR_PLUGIN_URL', plugins_url('bs-formular'));
//PLUGIN INC DIR
const BS_FORMULAR_INC = BS_FORMULAR_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;
// E-MAIL TEMPLATE FOLDER
const EMAIL_TEMPLATES_DIR = BS_FORMULAR_INC . 'templates' . DIRECTORY_SEPARATOR;

//PLUGIN ASSETS URL
define('BS_FORMULAR_PLUGIN_ASSETS_URL', plugins_url('bs-formular') . '/assets/public/');

//PLUGIN GUTENBERG DATA PATH
const BS_FORMULAR_GUTENBERG_DATA = BS_FORMULAR_INC  . 'gutenberg' . DIRECTORY_SEPARATOR . 'plugin-data' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR;
//PLUGIN GUTENBERG DATA URL
define('BS_FORMULAR_GUTENBERG_URL', plugins_url('bs-formular').'/inc/gutenberg/plugin-data/build/');

//File UPLOAD DIR
$upload_dir = wp_get_upload_dir();
define("BS_FILE_UPLOAD_DIR", $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'bs-formular-files' . DIRECTORY_SEPARATOR);
define("BS_FILE_UPLOAD_URL", $upload_dir['baseurl'] .'/bs-formular-files/');

/**
 * REGISTER PLUGIN
 */

require 'inc/license/license-init.php';

function activate_bs_formular() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bs-formular-activator.php';
	Bs_Formular_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bs-formular-deactivator.php
 */
function deactivate_bs_formular() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bs-formular-deactivator.php';
	Bs_Formular_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bs_formular' );
register_deactivation_hook( __FILE__, 'deactivate_bs_formular' );


if(get_option('bs_formular_product_install_authorize')) {
	delete_transient('show_lizenz_info');
    require 'inc/register-bs-formular.php';
    require 'inc/optionen/optionen-init.php';
	require 'inc/enqueue.php';
	require 'inc/update-checker/autoload.php';
	$bsFormularUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/team-hummelt/bs-formular/',
		__FILE__,
		'bs-formular'
	);
	$bsFormularUpdateChecker->getVcsApi()->enableReleaseAssets();
}

function showSitemapInfo() {
	if(get_transient('show_lizenz_info')) {
		echo '<div class="error"><p>' .
		     'BS-Formular ungültige Lizenz: Zum Aktivieren geben Sie Ihre Zugangsdaten ein.' .
		     '</p></div>';
	}
}

add_action('admin_notices','showSitemapInfo');