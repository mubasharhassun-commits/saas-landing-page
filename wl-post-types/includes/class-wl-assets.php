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

	const STYLE  = 'wl-post-types';
	const SCRIPT = 'wl-post-types';

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register' ) );
		add_action( 'vc_frontend_editor_enqueue_js_css', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue in the head when the page being viewed actually uses an element.
	 *
	 * WPBakery stores its layout as shortcodes in post_content, so the content
	 * can be checked before rendering. Enqueueing only at render time means the
	 * stylesheet is requested after wp_head, and while WordPress can print late
	 * styles in the footer, themes and optimisation layers routinely drop them.
	 */
	public static function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post || empty( $post->post_content ) ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'wl_cards' )
			|| has_shortcode( $post->post_content, 'wl_news' ) ) {
			self::enqueue();
		}

		// Only the Cases cards need the script, and only they ask for it.
		if ( has_shortcode( $post->post_content, 'wl_cards' )
			&& false !== strpos( $post->post_content, 'source="cases"' ) ) {
			self::enqueue_script();
		}
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

		wp_register_script(
			self::SCRIPT,
			WL_URL . 'public/js/wl-post-types.js',
			array(),
			WL_VERSION,
			true
		);
	}

	/**
	 * The hover-class helper, used by the Cases cards only.
	 */
	public static function enqueue_script() {
		if ( ! wp_script_is( self::SCRIPT, 'registered' ) ) {
			self::register();
		}

		wp_enqueue_script( self::SCRIPT );
	}

	public static function enqueue() {
		self::register();
		wp_enqueue_style( self::STYLE );
	}
}
