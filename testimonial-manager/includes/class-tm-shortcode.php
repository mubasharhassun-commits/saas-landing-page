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
		add_shortcode( 'testimonials_home', array( __CLASS__, 'render_home' ) );
	}

	/**
	 * Every attribute maps one-to-one onto TM_Query::defaults().
	 */
	/**
	 * Content defaults plus every style key, so shortcode_atts() keeps the
	 * style parameters instead of stripping them as unknown attributes.
	 */
	private static function defaults( $schema ) {
		return array_merge(
			TM_Query::defaults(),
			array_fill_keys( array_keys( $schema ), '' )
		);
	}

	public static function render( $atts ) {
		$atts = shortcode_atts( self::defaults( TM_Style::grid_schema() ), $atts, 'testimonials' );

		return TM_Renderer::grid( $atts );
	}

	/**
	 * [testimonials_slider] - same attributes, plus the slider ones. Defaults to
	 * newest first so the latest testimonial leads.
	 */
	public static function render_slider( $atts ) {
		$defaults = self::defaults( TM_Style::slider_schema() );

		$defaults['count']       = 5;
		$defaults['orderby']     = 'date';
		$defaults['order']       = 'DESC';
		$defaults['show_button'] = false;

		$atts = shortcode_atts( $defaults, $atts, 'testimonials_slider' );

		return TM_Renderer::slider( $atts );
	}

	/**
	 * [testimonials_home] - the home page panel. Same testimonials, newest
	 * first, with the chevrons on by default since that is its whole nav.
	 */
	public static function render_home( $atts ) {
		$defaults = self::defaults( TM_Style::home_schema() );

		$defaults['count']       = 5;
		$defaults['orderby']     = 'date';
		$defaults['order']       = 'DESC';
		$defaults['show_button'] = false;
		$defaults['show_rating'] = false;
		$defaults['arrows']      = true;
		$defaults['excerpt_words'] = 45;
		$defaults['salutation']  = '';
		$defaults['mark_image']  = '';
		$defaults['client_image'] = '';

		$atts = shortcode_atts( $defaults, $atts, 'testimonials_home' );

		return TM_Renderer::home_slider( $atts );
	}
}
