<?php
/**
 * Plugin Name:       ADA Accessibility
 * Plugin URI:        https://example.com/ada-accessibility
 * Description:       A front-end accessibility toolbar with high contrast, text resizing and skip-to-content. Works standalone, or as an Elementor widget and a WPBakery Page Builder element.
 * Version:           1.26.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Mubashar
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ada-accessibility
 * Domain Path:       /languages
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

define( 'ADAA_VERSION', '1.26.0' );
define( 'ADAA_FILE', __FILE__ );
define( 'ADAA_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADAA_URL', plugin_dir_url( __FILE__ ) );
define( 'ADAA_OPTION', 'adaa_settings' );

require_once ADAA_PATH . 'includes/class-adaa-settings.php';
require_once ADAA_PATH . 'includes/class-adaa-renderer.php';
require_once ADAA_PATH . 'includes/class-adaa-frontend.php';
require_once ADAA_PATH . 'includes/class-adaa-admin.php';
require_once ADAA_PATH . 'includes/class-adaa-style.php';
require_once ADAA_PATH . 'includes/class-adaa-elementor.php';
require_once ADAA_PATH . 'includes/class-adaa-wpbakery.php';
require_once ADAA_PATH . 'includes/class-adaa-plugin.php';

/**
 * Boot the plugin.
 *
 * @return ADAA_Plugin
 */
function adaa() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new ADAA_Plugin();
	}

	return $instance;
}

add_action( 'plugins_loaded', 'adaa' );

/**
 * Seed default settings on activation.
 *
 * @return void
 */
function adaa_activate() {
	if ( false === get_option( ADAA_OPTION, false ) ) {
		add_option( ADAA_OPTION, ADAA_Settings::defaults() );
	}
}
register_activation_hook( __FILE__, 'adaa_activate' );
