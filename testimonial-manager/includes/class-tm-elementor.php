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

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );
	}

	public static function is_available() {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Widget_Base' );
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

	public static function register_widget( $widgets_manager ) {
		if ( ! self::is_available() ) {
			return;
		}

		require_once TM_PATH . 'includes/widgets/class-tm-widget-grid.php';

		$widgets_manager->register( new TM_Widget_Grid() );
	}
}
