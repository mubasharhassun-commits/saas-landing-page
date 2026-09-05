<?php
/**
 * Elementor integration.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Elementor widget when Elementor is present and compatible.
 */
class ADAA_Elementor {

	const MIN_ELEMENTOR = '3.5.0';
	const MIN_PHP       = '7.4';

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Bail quietly unless Elementor is loaded and new enough.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_elementor_version' ) );
			return;
		}

		if ( ! version_compare( PHP_VERSION, self::MIN_PHP, '>=' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Admin notice for an outdated Elementor.
	 *
	 * @return void
	 */
	public function notice_elementor_version() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: minimum Elementor version. */
					__( 'ADA Accessibility needs Elementor %s or newer for its widget. The toolbar itself still works.', 'ada-accessibility' ),
					self::MIN_ELEMENTOR
				)
			)
		);
	}

	/**
	 * Add a panel category.
	 *
	 * @param \Elementor\Elements_Manager $manager Elements manager.
	 * @return void
	 */
	public function register_category( $manager ) {
		$manager->add_category(
			'ada-accessibility',
			array(
				'title' => __( 'Accessibility', 'ada-accessibility' ),
				'icon'  => 'eicon-accessibility',
			)
		);
	}

	/**
	 * Register the widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Widgets manager.
	 * @return void
	 */
	public function register_widget( $manager ) {
		require_once ADAA_PATH . 'widgets/class-adaa-widget.php';

		$manager->register( new ADAA_Widget() );
	}
}
