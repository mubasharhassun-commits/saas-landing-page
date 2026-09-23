<?php
/**
 * Elementor integration.
 *
 * Sits alongside the WPBakery element: both builders are supported and both
 * render the same markup, so a page built in either looks identical.
 *
 * Every hook is guarded, so with Elementor absent nothing is registered and
 * the shortcode and WPBakery element carry on working.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_Elementor {

	/**
	 * Guards against double registration when both the modern and the legacy
	 * hook fire on the same install.
	 *
	 * @var bool
	 */
	private static $registered = false;

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/widgets/widgets_registered', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( 'FC_Assets', 'enqueue' ) );
	}

	public static function is_available() {
		return class_exists( 'Elementor\Widget_Base' );
	}

	public static function category( $manager ) {
		$manager->add_category(
			'farrell',
			array(
				'title' => __( 'Farrell', 'farrell' ),
				'icon'  => 'fa fa-newspaper-o',
			)
		);
	}

	public static function register_widgets( $widgets_manager = null ) {
		if ( self::$registered || ! self::is_available() ) {
			return;
		}

		if ( ! $widgets_manager && class_exists( 'Elementor\Plugin' ) ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		}

		if ( ! $widgets_manager ) {
			return;
		}

		$file = FC_PT_DIR . 'includes/class-fc-topics-widget.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		require_once $file;

		if ( ! class_exists( 'FC_Topics_Widget' ) ) {
			return;
		}

		$widget = new FC_Topics_Widget();

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( $widget );
		} elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
			$widgets_manager->register_widget_type( $widget );
		}

		self::$registered = true;
	}
}
