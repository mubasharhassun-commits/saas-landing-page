<?php
/**
 * Elementor integration.
 *
 * Every hook here is guarded, so with Elementor absent or disabled the plugin
 * simply never registers a widget and the shortcode carries on working.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Elementor {

	/**
	 * Guards against double registration when both the modern and the legacy
	 * hook fire on the same install.
	 *
	 * @var bool
	 */
	private static $registered = false;

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );

		// Elementor 3.5 and later.
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );

		// Elementor before 3.5.
		add_action( 'elementor/widgets/widgets_registered', array( __CLASS__, 'register_widget' ) );
	}

	public static function is_available() {
		return class_exists( 'Elementor\Widget_Base' );
	}

	public static function category( $manager ) {
		$manager->add_category(
			'testimonial-manager',
			array(
				'title' => __( 'Testimonial Manager', 'testimonial-manager' ),
				'icon'  => 'eicon-testimonial',
			)
		);
	}

	/**
	 * Register the grid widget.
	 *
	 * The manager is passed by the modern hook; the legacy hook passes it too,
	 * but fall back to the singleton so neither path depends on the argument.
	 */
	public static function register_widget( $widgets_manager = null ) {
		if ( self::$registered || ! self::is_available() ) {
			return;
		}

		$file = TM_PATH . 'includes/widgets/class-tm-widget-grid.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		require_once $file;

		if ( ! class_exists( 'TM_Widget_Grid' ) ) {
			return;
		}

		if ( ! $widgets_manager && class_exists( 'Elementor\Plugin' ) ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		}

		if ( ! $widgets_manager ) {
			return;
		}

		$widget = new TM_Widget_Grid();

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( $widget );
		} elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
			$widgets_manager->register_widget_type( $widget );
		} else {
			return;
		}

		self::$registered = true;
	}
}
