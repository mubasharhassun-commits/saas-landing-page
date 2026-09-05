<?php
/**
 * Plugin Name:       MKM Review Funnel
 * Plugin URI:        https://github.com/mubasharhassun-commits/saas-landing-page
 * Description:       Review funnel with its own storage: routes happy clients to your public review profiles and captures private feedback from unhappy ones. Ships a shortcode and a WPBakery Page Builder element.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Law Offices of Michael Kevin Murphy, PLLC
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mkm-review-funnel
 * Domain Path:       /languages
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

define( 'MKM_RF_VERSION', '1.0.0' );
define( 'MKM_RF_FILE', __FILE__ );
define( 'MKM_RF_PATH', plugin_dir_path( __FILE__ ) );
define( 'MKM_RF_URL', plugin_dir_url( __FILE__ ) );
define( 'MKM_RF_BASENAME', plugin_basename( __FILE__ ) );

require_once MKM_RF_PATH . 'includes/class-mkm-rf-install.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-settings.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-repository.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-spam.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-mailer.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-rest.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-shortcode.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-wpbakery.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-privacy.php';
require_once MKM_RF_PATH . 'includes/class-mkm-rf-plugin.php';

register_activation_hook( __FILE__, array( 'MKM_RF_Install', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MKM_RF_Install', 'deactivate' ) );

/**
 * Main plugin instance.
 *
 * @return MKM_RF_Plugin
 */
function mkm_rf() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new MKM_RF_Plugin();
	}

	return $plugin;
}

mkm_rf()->init();
