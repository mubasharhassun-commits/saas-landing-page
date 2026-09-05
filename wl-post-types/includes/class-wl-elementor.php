<?php
/**
 * Elementor integration.
 *
 * Sits alongside the WPBakery elements: both builders are supported, and both
 * render the same markup, so a page built in either looks identical.
 *
 * Every hook is guarded, so with Elementor absent nothing is registered and
 * the shortcodes and WPBakery elements carry on working.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Elementor {

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
		add_action( 'elementor/preview/enqueue_scripts', array( 'WL_Assets', 'enqueue' ) );
	}

	public static function is_available() {
		return class_exists( 'Elementor\Widget_Base' );
	}

	public static function category( $manager ) {
		$manager->add_category(
			'waterslaw',
			array(
				'title' => __( 'Waters Law', 'waterslaw' ),
				'icon'  => 'fa fa-anchor',
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

		$widgets = array(
			'WL_Cards_Widget' => 'includes/class-wl-cards-widget.php',
			'WL_News_Widget'  => 'includes/class-wl-news-widget.php',
		);

		foreach ( $widgets as $class => $relative ) {
			$file = WL_PATH . $relative;

			if ( ! file_exists( $file ) ) {
				continue;
			}

			require_once $file;

			if ( ! class_exists( $class ) ) {
				continue;
			}

			$widget = new $class();

			if ( method_exists( $widgets_manager, 'register' ) ) {
				$widgets_manager->register( $widget );
			} elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
				$widgets_manager->register_widget_type( $widget );
			}
		}

		self::$registered = true;
	}
}
