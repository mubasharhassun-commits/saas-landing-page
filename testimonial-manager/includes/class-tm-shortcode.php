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
		add_shortcode( 'testimonials_slider', array( __CLASS__, 'render_slider' ) );
	}

	/**
	 * Every attribute maps one-to-one onto TM_Query::defaults().
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts( TM_Query::defaults(), $atts, 'testimonials' );

		return TM_Renderer::grid( $atts );
	}

	/**
	 * [testimonials_slider] - same attributes, plus the slider ones. Defaults to
	 * newest first so the latest testimonial leads.
	 */
	public static function render_slider( $atts ) {
		$defaults = TM_Query::defaults();

		$defaults['count']       = 5;
		$defaults['orderby']     = 'date';
		$defaults['order']       = 'DESC';
		$defaults['show_button'] = false;

		$atts = shortcode_atts( $defaults, $atts, 'testimonials_slider' );

		return TM_Renderer::slider( $atts );
	}
}
