<?php
/**
 * Validates builder parameters before they reach a style attribute.
 *
 * WPBakery has no scoped-selector system, so styling is applied as inline CSS
 * custom properties. Every value is type-checked and anything unrecognised is
 * dropped rather than passed through.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Style {

	/* Allowed background values, shared by both overlay image layers. */
	const BG_SIZES = array( 'cover', 'contain', 'auto' );

	const BG_POSITIONS = array(
		'center',
		'top',
		'bottom',
		'left',
		'right',
		'top left',
		'top right',
		'bottom left',
		'bottom right',
	);

	const BG_REPEATS = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );

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
	 * An aspect ratio such as 3/4 or 16/9. Anything else returns ''.
	 */
	public static function ratio( $value ) {
		$value = trim( (string) $value );

		return preg_match( '#^[0-9]{1,4}\s*/\s*[0-9]{1,4}$#', $value ) ? $value : '';
	}

	/**
	 * A percentage 0-100 expressed as a 0-1 multiplier, for opacity.
	 */
	public static function opacity( $value ) {
		if ( '' === trim( (string) $value ) ) {
			return '';
		}

		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return (string) round( max( 0, min( 100, $number ) ) / 100, 3 );
	}

	public static function seconds( $value, $min = 5, $max = 120 ) {
		if ( '' === trim( (string) $value ) ) {
			return '';
		}

		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return (int) max( $min, min( $max, $number ) ) . 's';
	}

	/**
	 * An image URL as a CSS url() value. Anything containing a quote, bracket,
	 * semicolon or whitespace is rejected outright rather than escaped, since
	 * no legitimate media-library URL needs them here.
	 */
	public static function image( $value ) {
		$url = esc_url_raw( trim( (string) $value ) );

		if ( '' === $url || preg_match( '/["\'();\s]/', $url ) ) {
			return '';
		}

		return 'url("' . $url . '")';
	}

	public static function keyword( $value, $allowed ) {
		$value = strtolower( trim( (string) $value ) );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	public static function build( $schema, $atts ) {
		$css = '';

		foreach ( $schema as $key => $rule ) {
			if ( ! isset( $atts[ $key ] ) || '' === trim( (string) $atts[ $key ] ) ) {
				continue;
			}

			$raw = $atts[ $key ];
			$min = isset( $rule['min'] ) ? $rule['min'] : 0;
			$max = isset( $rule['max'] ) ? $rule['max'] : 2000;

			switch ( $rule['type'] ) {
				case 'color':
					$value = self::color( $raw );
					break;
				case 'px':
					$value = self::px( $raw, $min, $max );
					break;
				case 'number':
					$value = self::number( $raw, $min, $max );
					break;
				case 'spacing':
					$value = self::spacing( $raw );
					break;
				case 'ratio':
					$value = self::ratio( $raw );
					break;
				case 'opacity':
					$value = self::opacity( $raw );
					break;
				case 'seconds':
					$value = self::seconds( $raw );
					break;
				case 'image':
					$value = self::image( $raw );
					break;
				case 'keyword':
					$value = self::keyword( $raw, $rule['allowed'] );
					break;
				default:
					$value = '';
			}

			if ( '' !== $value ) {
				$css .= $rule['var'] . ':' . $value . ';';
			}
		}

		return $css;
	}

	public static function cards_schema() {
		return array(
			'gap'            => array( 'var' => '--wl-gap', 'type' => 'px', 'max' => 120 ),
			'ratio'          => array( 'var' => '--wl-ratio', 'type' => 'ratio' ),
			'radius'         => array( 'var' => '--wl-radius', 'type' => 'px', 'max' => 80 ),
			'card_width'     => array( 'var' => '--wl-card-w', 'type' => 'px', 'min' => 60, 'max' => 900 ),
			'card_height'    => array( 'var' => '--wl-card-h', 'type' => 'px', 'min' => 60, 'max' => 1200 ),
			'overlay'        => array( 'var' => '--wl-overlay', 'type' => 'color' ),
			'hover_overlay'  => array( 'var' => '--wl-hover-overlay', 'type' => 'color' ),
			'overlay_image'         => array( 'var' => '--wl-ov-n-img', 'type' => 'image' ),
			'hover_overlay_image'   => array( 'var' => '--wl-ov-h-img', 'type' => 'image' ),
			'overlay_opacity'       => array( 'var' => '--wl-ov-n-op', 'type' => 'opacity' ),
			'hover_overlay_opacity' => array( 'var' => '--wl-ov-h-op', 'type' => 'opacity' ),
			'overlay_size'          => array( 'var' => '--wl-ov-n-size', 'type' => 'keyword', 'allowed' => self::BG_SIZES ),
			'overlay_position'      => array( 'var' => '--wl-ov-n-pos', 'type' => 'keyword', 'allowed' => self::BG_POSITIONS ),
			'overlay_repeat'        => array( 'var' => '--wl-ov-n-repeat', 'type' => 'keyword', 'allowed' => self::BG_REPEATS ),
			'hover_overlay_size'     => array( 'var' => '--wl-ov-h-size', 'type' => 'keyword', 'allowed' => self::BG_SIZES ),
			'hover_overlay_position' => array( 'var' => '--wl-ov-h-pos', 'type' => 'keyword', 'allowed' => self::BG_POSITIONS ),
			'hover_overlay_repeat'   => array( 'var' => '--wl-ov-h-repeat', 'type' => 'keyword', 'allowed' => self::BG_REPEATS ),
			'title_color'    => array( 'var' => '--wl-title', 'type' => 'color' ),
			'title_size'     => array( 'var' => '--wl-title-size', 'type' => 'px', 'min' => 8, 'max' => 80 ),
			'title_line_height' => array( 'var' => '--wl-title-line-height', 'type' => 'px', 'min' => 8, 'max' => 120 ),
			'title_weight'   => array(
				'var'     => '--wl-title-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
			'btn_color'      => array( 'var' => '--wl-btn-color', 'type' => 'color' ),
			'btn_border'     => array( 'var' => '--wl-btn-border', 'type' => 'color' ),
			'btn_bg'         => array( 'var' => '--wl-btn-bg', 'type' => 'color' ),
			'btn_padding'    => array( 'var' => '--wl-btn-padding', 'type' => 'spacing' ),
			'btn_radius'     => array( 'var' => '--wl-btn-radius', 'type' => 'px', 'max' => 80 ),
			'btn_border_width' => array( 'var' => '--wl-btn-border-width', 'type' => 'px', 'max' => 20 ),
			'btn_border_style' => array(
				'var'     => '--wl-btn-border-style',
				'type'    => 'keyword',
				'allowed' => array( 'solid', 'dashed', 'dotted', 'double', 'none' ),
			),
			'btn_color_h'    => array( 'var' => '--wl-btn-color-h', 'type' => 'color' ),
			'btn_border_h'   => array( 'var' => '--wl-btn-border-h', 'type' => 'color' ),
			'btn_bg_h'       => array( 'var' => '--wl-btn-bg-h', 'type' => 'color' ),
			'btn_size'       => array( 'var' => '--wl-btn-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'btn_spacing'    => array( 'var' => '--wl-btn-spacing', 'type' => 'px', 'min' => 0, 'max' => 20 ),
			'btn_weight'     => array(
				'var'     => '--wl-btn-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
			'duration'       => array( 'var' => '--wl-duration', 'type' => 'seconds' ),
		);
	}

	public static function news_schema() {
		return array(
			'accent'                => array( 'var' => '--wl-accent', 'type' => 'color' ),
			'columns_gap'           => array( 'var' => '--wl-news-gap', 'type' => 'px', 'max' => 120 ),
			'list_gap'              => array( 'var' => '--wl-news-list-gap', 'type' => 'px', 'max' => 120 ),
			'featured_height'       => array( 'var' => '--wl-news-featured-h', 'type' => 'px', 'max' => 900 ),
			'featured_radius'       => array( 'var' => '--wl-news-featured-radius', 'type' => 'px', 'max' => 80 ),
			'featured_overlay'      => array( 'var' => '--wl-news-featured-overlay', 'type' => 'color' ),
			'featured_title_color'  => array( 'var' => '--wl-news-featured-title', 'type' => 'color' ),
			'featured_title_size'   => array( 'var' => '--wl-news-featured-title-size', 'type' => 'px', 'min' => 8, 'max' => 80 ),
			'thumb_w'               => array( 'var' => '--wl-news-thumb-w', 'type' => 'px', 'min' => 30, 'max' => 400 ),
			'thumb_h'               => array( 'var' => '--wl-news-thumb-h', 'type' => 'px', 'min' => 30, 'max' => 400 ),
			'thumb_radius'          => array( 'var' => '--wl-news-thumb-radius', 'type' => 'px', 'max' => 80 ),
			'list_title_color'      => array( 'var' => '--wl-news-title', 'type' => 'color' ),
			'list_title_hover'      => array( 'var' => '--wl-news-title-hover', 'type' => 'color' ),
			'list_title_size'       => array( 'var' => '--wl-news-title-size', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'meta_color'            => array( 'var' => '--wl-news-meta-color', 'type' => 'color' ),
			'meta_icon_color'       => array( 'var' => '--wl-news-meta-icon-color', 'type' => 'color' ),
			'meta_icon_size'        => array( 'var' => '--wl-news-meta-icon-size', 'type' => 'px', 'min' => 6, 'max' => 40 ),
			'meta_size'             => array( 'var' => '--wl-news-meta-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'meta_gap'              => array( 'var' => '--wl-news-meta-gap', 'type' => 'px', 'max' => 60 ),
			'meta_row_gap'          => array( 'var' => '--wl-news-meta-row-gap', 'type' => 'px', 'max' => 60 ),
			'meta_wrap'             => array(
				'var'     => '--wl-news-meta-wrap',
				'type'    => 'keyword',
				'allowed' => array( 'wrap', 'nowrap' ),
			),
			'title_space'           => array( 'var' => '--wl-news-title-space', 'type' => 'px', 'max' => 60 ),
			'meta_space'            => array( 'var' => '--wl-news-meta-space', 'type' => 'px', 'max' => 60 ),
			'btn_color'             => array( 'var' => '--wl-news-btn-color', 'type' => 'color' ),
			'btn_bg'                => array( 'var' => '--wl-news-btn-bg', 'type' => 'color' ),
			'btn_border'            => array( 'var' => '--wl-news-btn-border', 'type' => 'color' ),
			'btn_color_h'           => array( 'var' => '--wl-news-btn-color-h', 'type' => 'color' ),
			'btn_bg_h'              => array( 'var' => '--wl-news-btn-bg-h', 'type' => 'color' ),
			'btn_padding'           => array( 'var' => '--wl-news-btn-padding', 'type' => 'spacing' ),
			'btn_radius'            => array( 'var' => '--wl-news-btn-radius', 'type' => 'px', 'max' => 80 ),
			'btn_size'              => array( 'var' => '--wl-news-btn-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'btn_spacing'           => array( 'var' => '--wl-news-btn-spacing', 'type' => 'px', 'max' => 20 ),
			'btn_border_width'      => array( 'var' => '--wl-news-btn-border-width', 'type' => 'px', 'max' => 20 ),
			'btn_weight'            => array(
				'var'     => '--wl-news-btn-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
		);
	}
}
