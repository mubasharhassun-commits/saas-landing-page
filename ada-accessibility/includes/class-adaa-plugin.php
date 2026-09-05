<?php
/**
 * Plugin container.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires the pieces together.
 */
class ADAA_Plugin {

	/**
	 * Boot.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		new ADAA_Elementor();
		new ADAA_WPBakery();

		if ( is_admin() ) {
			new ADAA_Admin();
		}

		// Always instantiated: its hooks are front-end only, but the asset
		// registration must exist before Elementor resolves widget dependencies.
		new ADAA_Frontend();
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ada-accessibility',
			false,
			dirname( plugin_basename( ADAA_FILE ) ) . '/languages'
		);
	}
}
