<?php
/**
 * [testimonials] shortcode.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Shortcode {

	public static function init() {
		add_shortcode( 'testimonials', array( __CLASS__, 'render' ) );
	}

	/**
	 * Every attribute maps one-to-one onto TM_Query::defaults().
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts( TM_Query::defaults(), $atts, 'testimonials' );

		return TM_Renderer::grid( $atts );
	}
}
