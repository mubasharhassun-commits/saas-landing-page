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
	/**
	 * A font-family list. Quotes, brackets and semicolons are rejected rather
	 * than escaped: CSS accepts unquoted family names, so none are needed.
	 */
	public static function font( $value ) {
		$value = trim( (string) $value );

		return preg_match( '/^[A-Za-z0-9 ,\-]{1,120}$/', $value ) ? $value : '';
	}

	/**
	 * Box-shadow presets. Arbitrary shadow strings are not accepted, so there
	 * is nothing to sanitise at the point of use.
	 */
	public static function shadow( $value ) {
		$presets = array(
			'none'   => 'none',
			'soft'   => '0 4px 14px rgba(0,0,0,0.08)',
			'medium' => '0 8px 24px rgba(0,0,0,0.14)',
			'strong' => '0 16px 40px rgba(0,0,0,0.22)',
		);

		$key = self::keyword( $value, array_keys( $presets ) );

		return ( '' === $key ) ? '' : $presets[ $key ];
	}

	/**
	 * An image URL as a CSS url() value. Anything carrying a quote, bracket,
	 * semicolon or whitespace is rejected outright rather than escaped: no
	 * media-library URL needs them here, and escaping is easy to get wrong.
	 */
	public static function image( $value ) {
		$url = esc_url_raw( trim( (string) $value ) );

		if ( '' === $url || preg_match( '/["\'();\s]/', $url ) ) {
			return '';
		}

		return 'url("' . $url . '")';
	}

	/**
	 * A percentage 0-100 expressed as a 0-1 multiplier, for opacity.
	 */
	public static function opacity( $value ) {
		if ( '' === trim( (string) $value ) ) {
			return '';
		}

		if ( ! preg_match( '/-?\d+(?:\.\d+)?/', (string) $value, $m ) ) {
			return '';
		}

		return (string) round( max( 0, min( 100, (float) $m[0] ) ) / 100, 3 );
	}

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

				case 'font':
					$value = self::font( $raw );
					break;

				case 'shadow':
					$value = self::shadow( $raw );
					break;

				case 'image':
					$value = self::image( $raw );
					break;
				case 'opacity':
					$value = self::opacity( $raw );
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
			'card_shadow'        => array( 'var' => '--tm-card-shadow', 'type' => 'shadow' ),
			'font_family'        => array( 'var' => '--tm-font', 'type' => 'font' ),
			'avatar_size'        => array( 'var' => '--tm-avatar-size', 'type' => 'px', 'min' => 16, 'max' => 200 ),
			'star_color'         => array( 'var' => '--tm-star-color', 'type' => 'color' ),
			'star_empty_color'   => array( 'var' => '--tm-star-empty-color', 'type' => 'color' ),
			'star_size'          => array( 'var' => '--tm-star-size', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'star_gap'           => array( 'var' => '--tm-star-gap', 'type' => 'px', 'max' => 40 ),
			'quote_mark_size'    => array( 'var' => '--tm-quote-mark-size', 'type' => 'px', 'min' => 10, 'max' => 160 ),
			'quote_mark_color'   => array( 'var' => '--tm-quote-mark-color', 'type' => 'color' ),
			'quote_color'        => array( 'var' => '--tm-text-color', 'type' => 'color' ),
			'quote_size'         => array( 'var' => '--tm-quote-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_align'        => array( 'var' => '--tm-quote-align', 'type' => 'keyword', 'allowed' => array( 'left', 'center', 'right', 'justify' ) ),
			'quote_style'        => array( 'var' => '--tm-quote-style', 'type' => 'keyword', 'allowed' => array( 'italic', 'normal' ) ),
			'quote_weight'       => array( 'var' => '--tm-quote-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'quote_lh'           => array( 'var' => '--tm-quote-lh', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'quote_spacing'      => array( 'var' => '--tm-quote-spacing', 'type' => 'px', 'min' => -5, 'max' => 20 ),
			'name_color'         => array( 'var' => '--tm-name-color', 'type' => 'color' ),
			'name_size'          => array( 'var' => '--tm-name-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'name_weight'        => array( 'var' => '--tm-name-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'name_lh'            => array( 'var' => '--tm-name-lh', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'name_spacing'       => array( 'var' => '--tm-name-spacing', 'type' => 'px', 'min' => -5, 'max' => 20 ),
			'name_transform'     => array( 'var' => '--tm-name-transform', 'type' => 'keyword', 'allowed' => array( 'none', 'uppercase', 'capitalize', 'lowercase' ) ),
			'role_color'         => array( 'var' => '--tm-role-color', 'type' => 'color' ),
			'role_size'          => array( 'var' => '--tm-role-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'role_weight'        => array( 'var' => '--tm-role-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'role_lh'            => array( 'var' => '--tm-role-lh', 'type' => 'px', 'min' => 8, 'max' => 80 ),
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
			'btn_weight'         => array( 'var' => '--tm-btn-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'btn_spacing'        => array( 'var' => '--tm-btn-spacing', 'type' => 'px', 'min' => -5, 'max' => 20 ),
			'btn_transform'      => array( 'var' => '--tm-btn-transform', 'type' => 'keyword', 'allowed' => array( 'none', 'uppercase', 'capitalize', 'lowercase' ) ),

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
	 * Style properties for the home testimonial panel.
	 */
	public static function home_schema() {
		return array(
			'panel_bg'          => array( 'var' => '--tm-home-bg', 'type' => 'color' ),
			'panel_bg_image'    => array( 'var' => '--tm-home-bg-image', 'type' => 'image' ),
			'panel_bg_size'     => array( 'var' => '--tm-home-bg-size', 'type' => 'keyword', 'allowed' => array( 'cover', 'contain', 'auto' ) ),
			'panel_bg_position' => array(
				'var'     => '--tm-home-bg-position',
				'type'    => 'keyword',
				'allowed' => array( 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' ),
			),
			'panel_bg_repeat'   => array( 'var' => '--tm-home-bg-repeat', 'type' => 'keyword', 'allowed' => array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' ) ),
			'panel_bg_opacity'  => array( 'var' => '--tm-home-bg-opacity', 'type' => 'opacity' ),
			'panel_radius'      => array( 'var' => '--tm-home-radius', 'type' => 'px', 'max' => 80 ),
			'panel_padding'     => array( 'var' => '--tm-home-padding', 'type' => 'spacing' ),
			'panel_min_height'  => array( 'var' => '--tm-home-min-height', 'type' => 'px', 'max' => 900 ),
			'column_width'      => array( 'var' => '--tm-home-col', 'type' => 'px', 'min' => 40, 'max' => 320 ),
			'column_gap'        => array( 'var' => '--tm-home-col-gap', 'type' => 'px', 'max' => 120 ),
			'row_gap'           => array( 'var' => '--tm-home-row-gap', 'type' => 'px', 'max' => 120 ),
			'font_family'       => array( 'var' => '--tm-font', 'type' => 'font' ),

			'mark_color'        => array( 'var' => '--tm-home-mark-color', 'type' => 'color' ),
			'mark_size'         => array( 'var' => '--tm-home-mark-size', 'type' => 'px', 'min' => 20, 'max' => 300 ),
			'mark_weight'       => array( 'var' => '--tm-home-mark-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800', '900' ) ),
			'mark_image_width'  => array( 'var' => '--tm-home-mark-img-width', 'type' => 'px', 'min' => 10, 'max' => 320 ),

			'quote_color'       => array( 'var' => '--tm-home-quote-color', 'type' => 'color' ),
			'quote_size'        => array( 'var' => '--tm-home-quote-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_weight'      => array( 'var' => '--tm-home-quote-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'quote_lh'          => array( 'var' => '--tm-home-quote-lh', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'quote_style'       => array( 'var' => '--tm-home-quote-style', 'type' => 'keyword', 'allowed' => array( 'italic', 'normal' ) ),
			'quote_align'       => array( 'var' => '--tm-home-quote-align', 'type' => 'keyword', 'allowed' => array( 'left', 'center', 'right', 'justify' ) ),
			'quote_spacing'     => array( 'var' => '--tm-home-quote-spacing', 'type' => 'px', 'min' => -5, 'max' => 20 ),

			'avatar_size'       => array( 'var' => '--tm-avatar-size', 'type' => 'px', 'min' => 16, 'max' => 220 ),
			'avatar_ring_color' => array( 'var' => '--tm-home-ring-color', 'type' => 'color' ),
			'avatar_ring_width' => array( 'var' => '--tm-home-ring-width', 'type' => 'px', 'max' => 20 ),

			'name_color'        => array( 'var' => '--tm-home-name-color', 'type' => 'color' ),
			'name_size'         => array( 'var' => '--tm-home-name-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'name_weight'       => array( 'var' => '--tm-home-name-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'name_style'        => array( 'var' => '--tm-home-name-style', 'type' => 'keyword', 'allowed' => array( 'italic', 'normal' ) ),
			'name_lh'           => array( 'var' => '--tm-home-name-lh', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'role_color'        => array( 'var' => '--tm-role-color', 'type' => 'color' ),
			'role_size'         => array( 'var' => '--tm-home-role-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),

			'star_color'        => array( 'var' => '--tm-star-color', 'type' => 'color' ),
			'star_empty_color'  => array( 'var' => '--tm-star-empty-color', 'type' => 'color' ),
			'star_size'         => array( 'var' => '--tm-star-size', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'star_gap'          => array( 'var' => '--tm-star-gap', 'type' => 'px', 'max' => 40 ),

			'nav_color'         => array( 'var' => '--tm-home-nav-color', 'type' => 'color' ),
			'nav_hover_color'   => array( 'var' => '--tm-home-nav-hover', 'type' => 'color' ),
			'nav_size'          => array( 'var' => '--tm-home-nav-size', 'type' => 'px', 'min' => 12, 'max' => 90 ),
			'nav_gap'           => array( 'var' => '--tm-home-nav-gap', 'type' => 'px', 'max' => 90 ),
			'nav_bottom'        => array( 'var' => '--tm-home-nav-bottom', 'type' => 'px', 'max' => 400 ),
			'nav_right'         => array( 'var' => '--tm-home-nav-right', 'type' => 'px', 'max' => 400 ),

			'dot_color'         => array( 'var' => '--tm-slider-dot', 'type' => 'color' ),
			'dot_active_color'  => array( 'var' => '--tm-slider-dot-active', 'type' => 'color' ),
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
			'star_size'          => array( 'var' => '--tm-star-size', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'star_gap'           => array( 'var' => '--tm-star-gap', 'type' => 'px', 'max' => 40 ),
			'quote_mark_size'    => array( 'var' => '--tm-quote-mark-size', 'type' => 'px', 'min' => 10, 'max' => 160 ),
			'quote_mark_color'   => array( 'var' => '--tm-quote-mark-color', 'type' => 'color' ),
			'quote_color'        => array( 'var' => '--tm-slider-color', 'type' => 'color' ),
			'quote_size'         => array( 'var' => '--tm-slider-quote-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'quote_weight'       => array( 'var' => '--tm-slider-quote-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'quote_lh'           => array( 'var' => '--tm-slider-quote-lh', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'quote_style'        => array( 'var' => '--tm-slider-quote-style', 'type' => 'keyword', 'allowed' => array( 'italic', 'normal' ) ),
			'quote_align'        => array( 'var' => '--tm-slider-quote-align', 'type' => 'keyword', 'allowed' => array( 'left', 'center', 'right', 'justify' ) ),
			'quote_spacing'      => array( 'var' => '--tm-slider-quote-spacing', 'type' => 'px', 'min' => -5, 'max' => 20 ),
			'role_size'          => array( 'var' => '--tm-slider-role-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'font_family'        => array( 'var' => '--tm-font', 'type' => 'font' ),
			'name_color'         => array( 'var' => '--tm-name-color', 'type' => 'color' ),
			'name_size'          => array( 'var' => '--tm-slider-name-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'name_weight'        => array( 'var' => '--tm-slider-name-weight', 'type' => 'keyword', 'allowed' => array( '300', '400', '500', '600', '700', '800' ) ),
			'role_color'         => array( 'var' => '--tm-role-color', 'type' => 'color' ),
			'nav_color'          => array( 'var' => '--tm-slider-nav-color', 'type' => 'color' ),
			'nav_bg'             => array( 'var' => '--tm-slider-nav-bg', 'type' => 'color' ),
			'dot_color'          => array( 'var' => '--tm-slider-dot', 'type' => 'color' ),
			'dot_active_color'   => array( 'var' => '--tm-slider-dot-active', 'type' => 'color' ),
		);
	}
}
