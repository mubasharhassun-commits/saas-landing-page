<?php
/**
 * Turns builder parameters into inline CSS custom properties.
 *
 * WPBakery has no scoped-selector system like Elementor's {{WRAPPER}}, so every
 * style setting is written onto the element as a custom property instead. These
 * values reach a style attribute, so each one is validated against its type and
 * anything unrecognised is dropped rather than passed through.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Style {

	/**
	 * Hex, rgb()/rgba(), hsl()/hsla(), or a bare colour keyword. Anything else
	 * (including url(), expression(), or a stray semicolon) returns ''.
	 */
	public static function color( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^(rgba?|hsla?)\(\s*[0-9.,%\s\/deg]+\)$/i', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^[a-z]{3,20}$/i', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * A single length in pixels.
	 */
	/**
	 * The first number in the string, or null when there isn't one. Taking the
	 * first token rather than stripping every non-digit means "10px<junk>9"
	 * reads as 10, not 109.
	 */
	private static function first_number( $value ) {
		if ( ! preg_match( '/-?\d+(?:\.\d+)?/', (string) $value, $m ) ) {
			return null;
		}

		return (float) $m[0];
	}

	public static function px( $value, $min = 0, $max = 2000 ) {
		if ( '' === trim( (string) $value ) ) {
			return '';
		}

		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return round( max( $min, min( $max, $number ) ), 2 ) . 'px';
	}

	/**
	 * A plain number, for unitless properties.
	 */
	public static function number( $value, $min = 0, $max = 2000 ) {
		if ( '' === trim( (string) $value ) ) {
			return '';
		}

		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return (string) max( $min, min( $max, $number ) );
	}

	/**
	 * One to four pixel lengths, as a padding or margin shorthand.
	 */
	public static function spacing( $value ) {
		$parts = preg_split( '/[\s,]+/', trim( (string) $value ), -1, PREG_SPLIT_NO_EMPTY );

		if ( ! $parts ) {
			return '';
		}

		$out = array();

		foreach ( array_slice( $parts, 0, 4 ) as $part ) {
			$px = self::px( $part, 0, 400 );

			if ( '' === $px ) {
				return '';
			}

			$out[] = $px;
		}

		return implode( ' ', $out );
	}

	/**
	 * A value that must be one of a known set.
	 */
	public static function keyword( $value, $allowed ) {
		$value = strtolower( trim( (string) $value ) );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * One position keyword expands to the four properties that place the popup
	 * close button, since WPBakery cannot map a single control to several.
	 */
	public static function close_position( $value ) {
		$map = array(
			'top-right'  => '--tm-close-top:12px;--tm-close-right:12px;--tm-close-left:auto;--tm-close-transform:none;',
			'top-center' => '--tm-close-top:0px;--tm-close-right:auto;--tm-close-left:50%;--tm-close-transform:translate(-50%,-50%);',
			'top-left'   => '--tm-close-top:12px;--tm-close-right:auto;--tm-close-left:12px;--tm-close-transform:none;',
		);

		$key = self::keyword( $value, array_keys( $map ) );

		return ( '' === $key ) ? '' : $map[ $key ];
	}

	/**
	 * Apply a schema to a set of raw parameters and return a style attribute
	 * value. Only properties that validated are included.
	 *
	 * @param array $schema property name => array( var, type, ...options ).
	 * @param array $atts   raw parameter values.
	 */
	public static function build( $schema, $atts ) {
		$css = '';

		foreach ( $schema as $key => $rule ) {
			if ( ! isset( $atts[ $key ] ) || '' === trim( (string) $atts[ $key ] ) ) {
				continue;
			}

			$raw = $atts[ $key ];

			switch ( $rule['type'] ) {
				case 'color':
					$value = self::color( $raw );
					break;

				case 'px':
					$value = self::px(
						$raw,
						isset( $rule['min'] ) ? $rule['min'] : 0,
						isset( $rule['max'] ) ? $rule['max'] : 2000
					);
					break;

				case 'number':
					$value = self::number(
						$raw,
						isset( $rule['min'] ) ? $rule['min'] : 0,
						isset( $rule['max'] ) ? $rule['max'] : 2000
					);
					break;

				case 'spacing':
					$value = self::spacing( $raw );
					break;

				case 'keyword':
					$value = self::keyword( $raw, $rule['allowed'] );
					break;

				case 'close_position':
					$value = self::close_position( $raw );
					break;

				default:
					$value = '';
			}

			if ( '' === $value ) {
				continue;
			}

			// An empty var name means the rule produced complete declarations.
			$css .= ( '' === $rule['var'] ) ? $value : $rule['var'] . ':' . $value . ';';
		}

		return $css;
	}

	/**
	 * Style properties for the testimonial grid.
	 */
	public static function grid_schema() {
		return array(
			'card_bg'            => array( 'var' => '--tm-card-bg', 'type' => 'color' ),
			'card_border_color'  => array( 'var' => '--tm-card-border-color', 'type' => 'color' ),
			'card_border_width'  => array( 'var' => '--tm-card-border-width', 'type' => 'px', 'max' => 20 ),
			'card_radius'        => array( 'var' => '--tm-card-radius', 'type' => 'px', 'max' => 80 ),
			'card_padding'       => array( 'var' => '--tm-card-padding', 'type' => 'spacing' ),
			'avatar_size'        => array( 'var' => '--tm-avatar-size', 'type' => 'px', 'min' => 16, 'max' => 200 ),
			'star_color'         => array( 'var' => '--tm-star-color', 'type' => 'color' ),
			'star_empty_color'   => array( 'var' => '--tm-star-empty-color', 'type' => 'color' ),
			'star_size'          => array( 'var' => '--tm-star-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_color'        => array( 'var' => '--tm-text-color', 'type' => 'color' ),
			'quote_size'         => array( 'var' => '--tm-quote-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_align'        => array( 'var' => '--tm-quote-align', 'type' => 'keyword', 'allowed' => array( 'left', 'center', 'right', 'justify' ) ),
			'quote_style'        => array( 'var' => '--tm-quote-style', 'type' => 'keyword', 'allowed' => array( 'italic', 'normal' ) ),
			'name_color'         => array( 'var' => '--tm-name-color', 'type' => 'color' ),
			'name_size'          => array( 'var' => '--tm-name-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'role_color'         => array( 'var' => '--tm-role-color', 'type' => 'color' ),
			'role_size'          => array( 'var' => '--tm-role-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'btn_bg'             => array( 'var' => '--tm-btn-bg', 'type' => 'color' ),
			'btn_color'          => array( 'var' => '--tm-btn-color', 'type' => 'color' ),
			'btn_border_color'   => array( 'var' => '--tm-btn-border-color', 'type' => 'color' ),
			'btn_bg_hover'       => array( 'var' => '--tm-btn-bg-hover', 'type' => 'color' ),
			'btn_color_hover'    => array( 'var' => '--tm-btn-color-hover', 'type' => 'color' ),
			'btn_border_hover'   => array( 'var' => '--tm-btn-border-hover', 'type' => 'color' ),
			'btn_border_width'   => array( 'var' => '--tm-btn-border-width', 'type' => 'px', 'max' => 20 ),
			'btn_radius'         => array( 'var' => '--tm-btn-radius', 'type' => 'px', 'max' => 80 ),
			'btn_padding'        => array( 'var' => '--tm-btn-padding', 'type' => 'spacing' ),
			'btn_font_size'      => array( 'var' => '--tm-btn-font-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),

			/* Read by the script and copied onto the shared popup. */
			'popup_max_width'    => array( 'var' => '--tm-modal-max-width', 'type' => 'px', 'min' => 280, 'max' => 1600 ),
			'popup_bg'           => array( 'var' => '--tm-modal-bg', 'type' => 'color' ),
			'popup_overlay'      => array( 'var' => '--tm-modal-overlay', 'type' => 'color' ),
			'popup_text_color'   => array( 'var' => '--tm-modal-text-color', 'type' => 'color' ),
			'popup_font_size'    => array( 'var' => '--tm-modal-font-size', 'type' => 'px', 'min' => 10, 'max' => 40 ),
			'popup_radius'       => array( 'var' => '--tm-modal-radius', 'type' => 'px', 'max' => 80 ),
			'popup_padding'      => array( 'var' => '--tm-modal-padding', 'type' => 'px', 'max' => 120 ),
			'popup_close_bg'     => array( 'var' => '--tm-modal-close-bg', 'type' => 'color' ),
			'popup_close_color'  => array( 'var' => '--tm-modal-close-color', 'type' => 'color' ),
			'popup_close_size'   => array( 'var' => '--tm-close-size', 'type' => 'px', 'min' => 20, 'max' => 90 ),
			'popup_close_position' => array( 'var' => '', 'type' => 'close_position' ),
		);
	}

	/**
	 * Style properties for the slider.
	 */
	public static function slider_schema() {
		return array(
			'panel_bg'           => array( 'var' => '--tm-slider-bg', 'type' => 'color' ),
			'panel_radius'       => array( 'var' => '--tm-slider-radius', 'type' => 'px', 'max' => 80 ),
			'panel_padding'      => array( 'var' => '--tm-slider-padding', 'type' => 'spacing' ),
			'panel_min_height'   => array( 'var' => '--tm-slider-min-height', 'type' => 'px', 'max' => 900 ),
			'avatar_size'        => array( 'var' => '--tm-avatar-size', 'type' => 'px', 'min' => 16, 'max' => 200 ),
			'star_color'         => array( 'var' => '--tm-star-color', 'type' => 'color' ),
			'star_empty_color'   => array( 'var' => '--tm-star-empty-color', 'type' => 'color' ),
			'star_size'          => array( 'var' => '--tm-star-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_color'        => array( 'var' => '--tm-slider-color', 'type' => 'color' ),
			'quote_size'         => array( 'var' => '--tm-slider-quote-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'name_color'         => array( 'var' => '--tm-name-color', 'type' => 'color' ),
			'name_size'          => array( 'var' => '--tm-slider-name-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'role_color'         => array( 'var' => '--tm-role-color', 'type' => 'color' ),
			'nav_color'          => array( 'var' => '--tm-slider-nav-color', 'type' => 'color' ),
			'nav_bg'             => array( 'var' => '--tm-slider-nav-bg', 'type' => 'color' ),
			'dot_color'          => array( 'var' => '--tm-slider-dot', 'type' => 'color' ),
			'dot_active_color'   => array( 'var' => '--tm-slider-dot-active', 'type' => 'color' ),
		);
	}
}
