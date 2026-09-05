<?php
/**
 * Front-end CSS, registered up front but only enqueued when something renders.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Assets {

	const STYLE = 'wl-post-types';

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register' ) );
		add_action( 'vc_frontend_editor_enqueue_js_css', array( __CLASS__, 'enqueue' ) );
	}

	public static function register() {
		if ( wp_style_is( self::STYLE, 'registered' ) ) {
			return;
		}

		wp_register_style(
			self::STYLE,
			WL_URL . 'public/css/wl-post-types.css',
			array(),
			WL_VERSION
		);
	}

	public static function enqueue() {
		self::register();
		wp_enqueue_style( self::STYLE );
	}
}
