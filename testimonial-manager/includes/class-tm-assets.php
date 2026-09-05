<?php
/**
 * Front-end CSS and JS, registered up front but only enqueued when a grid
 * actually renders on the page.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Assets {

	const STYLE  = 'testimonial-manager';
	const SCRIPT = 'testimonial-manager';
	const SLIDER = 'testimonial-manager-slider';

	public static function init() {
		// Registered in every context an element may render, including
		// WPBakery's front-end editor and its back-end preview, which are admin
		// screens where wp_enqueue_scripts does not always run.
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
			TM_URL . 'public/css/testimonial-manager.css',
			array(),
			TM_VERSION
		);

		wp_register_script(
			self::SCRIPT,
			TM_URL . 'public/js/testimonial-manager.js',
			array(),
			TM_VERSION,
			true
		);

		wp_register_script(
			self::SLIDER,
			TM_URL . 'public/js/testimonial-manager-slider.js',
			array(),
			TM_VERSION,
			true
		);
	}

	/**
	 * Only pages with a slider on them pay for the slider script.
	 */
	public static function enqueue_slider() {
		if ( ! wp_script_is( self::SLIDER, 'registered' ) ) {
			self::register();
		}

		wp_enqueue_script( self::SLIDER );
	}

	/**
	 * Safe to call repeatedly. WordPress prints anything enqueued after
	 * wp_head in the footer, so calling this mid-content still works.
	 */
	public static function enqueue() {
		if ( ! wp_style_is( self::STYLE, 'registered' ) ) {
			self::register();
		}

		wp_enqueue_style( self::STYLE );
		wp_enqueue_script( self::SCRIPT );
	}
}
