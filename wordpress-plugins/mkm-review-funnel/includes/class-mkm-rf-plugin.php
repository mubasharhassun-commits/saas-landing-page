<?php
/**
 * Plugin bootstrap: wires every component together.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads and holds the plugin components.
 */
class MKM_RF_Plugin {

	/**
	 * Shortcode/front-end renderer.
	 *
	 * @var MKM_RF_Shortcode
	 */
	protected $shortcode;

	/**
	 * REST controller.
	 *
	 * @var MKM_RF_Rest
	 */
	protected $rest;

	/**
	 * Admin screens, loaded only in wp-admin.
	 *
	 * @var MKM_RF_Admin|null
	 */
	protected $admin = null;

	/**
	 * Boots the plugin.
	 */
	public function init() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( 'MKM_RF_Install', 'maybe_upgrade' ) );

		$this->shortcode = new MKM_RF_Shortcode();
		$this->shortcode->init();

		$this->rest = new MKM_RF_Rest();
		$this->rest->init();

		$wpbakery = new MKM_RF_WPBakery();
		$wpbakery->init();

		$privacy = new MKM_RF_Privacy();
		$privacy->init();

		if ( is_admin() ) {
			require_once MKM_RF_PATH . 'includes/class-mkm-rf-admin.php';

			$this->admin = new MKM_RF_Admin();
			$this->admin->init();
		}
	}

	/**
	 * Loads translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mkm-review-funnel', false, dirname( MKM_RF_BASENAME ) . '/languages' );
	}

	/**
	 * The shortcode renderer.
	 *
	 * @return MKM_RF_Shortcode
	 */
	public function shortcode() {
		return $this->shortcode;
	}

	/**
	 * The capability required to manage entries and settings.
	 *
	 * @return string
	 */
	public static function capability() {
		/**
		 * Filters the capability required to manage the review funnel.
		 *
		 * @param string $capability Capability name.
		 */
		return (string) apply_filters( 'mkm_rf_capability', 'manage_options' );
	}
}
