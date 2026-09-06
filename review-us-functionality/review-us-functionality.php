<?php
/**
 * Plugin Name:       RA Review Us
 * Plugin URI:        https://mkmlaw.net/
 * Description:       Review funnel for the Law Offices of Michael Kevin Murphy: a Review Us page with thumbs up / thumbs down, a two-step Google review popup flow, and a private negative-feedback form stored in WordPress.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            Rustam Ali Randhawa
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mkm-review-us
 *
 * @package MKM_Review_Us
 */

defined( 'ABSPATH' ) || exit;

define( 'MKM_REVIEW_US_VERSION', '1.1.0' );
define( 'MKM_REVIEW_US_FILE', __FILE__ );
define( 'MKM_REVIEW_US_PATH', plugin_dir_path( __FILE__ ) );
define( 'MKM_REVIEW_US_URL', plugin_dir_url( __FILE__ ) );

require_once MKM_REVIEW_US_PATH . 'includes/class-review-settings.php';
require_once MKM_REVIEW_US_PATH . 'includes/class-review-feedback.php';
require_once MKM_REVIEW_US_PATH . 'includes/class-review-page.php';

/**
 * Bootstraps the plugin.
 */
final class MKM_Review_Us {

	/**
	 * Singleton instance.
	 *
	 * @var MKM_Review_Us|null
	 */
	private static $instance = null;

	/**
	 * Settings handler.
	 *
	 * @var MKM_Review_Us_Settings
	 */
	public $settings;

	/**
	 * Feedback storage handler.
	 *
	 * @var MKM_Review_Us_Feedback
	 */
	public $feedback;

	/**
	 * Front end handler.
	 *
	 * @var MKM_Review_Us_Page
	 */
	public $page;

	/**
	 * Returns the shared instance.
	 *
	 * @return MKM_Review_Us
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wires the plugin up.
	 */
	private function __construct() {
		$this->settings = new MKM_Review_Us_Settings();
		$this->feedback = new MKM_Review_Us_Feedback();
		$this->page     = new MKM_Review_Us_Page();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'maybe_upgrade_database' ) );
		add_action( 'admin_notices', array( $this, 'configuration_notice' ) );
	}

	/**
	 * Loads translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mkm-review-us', false, dirname( plugin_basename( MKM_REVIEW_US_FILE ) ) . '/languages' );
	}

	/**
	 * Creates the feedback table and the Review Us page on activation.
	 */
	public static function activate() {
		MKM_Review_Us_Feedback::create_table();
		MKM_Review_Us_Settings::ensure_review_page();
	}

	/**
	 * Runs the table migration once after a plugin update, never on the front end.
	 */
	public function maybe_upgrade_database() {
		if ( get_option( MKM_Review_Us_Feedback::DB_VERSION_OPTION ) === MKM_Review_Us_Feedback::DB_VERSION ) {
			return;
		}

		MKM_Review_Us_Feedback::create_table();
	}

	/**
	 * Warns administrators when the Google review URL has not been set yet.
	 */
	public function configuration_notice() {
		if ( ! current_user_can( MKM_Review_Us_Settings::capability() ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'settings_page_mkm-review-us' === $screen->id ) {
			return;
		}

		if ( MKM_Review_Us_Settings::get( 'google_review_url' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post(
				sprintf(
					/* translators: %s: settings page URL. */
					__( 'MKM Review Us: add your Google Review URL in <a href="%s">Settings &rarr; Review Us</a> so the thumbs up flow can send clients to Google.', 'mkm-review-us' ),
					esc_url( admin_url( 'options-general.php?page=mkm-review-us' ) )
				)
			)
		);
	}
}

register_activation_hook( __FILE__, array( 'MKM_Review_Us', 'activate' ) );

MKM_Review_Us::instance();

/**
 * Returns the public URL of the Review Us page, for use in themes and templates.
 *
 * @return string
 */
function mkm_review_us_page_url() {
	return MKM_Review_Us_Settings::page_url();
}
