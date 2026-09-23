<?php
/**
 * Registers the stylesheet and enqueues it only on pages that render the
 * element, so a page without Trending Topics loads nothing.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_Assets {

	const HANDLE = 'farrell-post-types';

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		wp_register_style(
			self::HANDLE,
			FC_PT_URL . 'public/css/farrell-post-types.css',
			array(),
			FC_PT_VERSION
		);
	}

	/**
	 * Called from the renderer. Registering late (a shortcode inside a widget,
	 * an Elementor preview) still has to work, so the style is registered on
	 * the spot if wp_enqueue_scripts has already run.
	 */
	public static function enqueue() {
		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			self::register();
		}

		wp_enqueue_style( self::HANDLE );
	}
}
