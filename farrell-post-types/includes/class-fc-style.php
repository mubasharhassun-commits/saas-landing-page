<?php
/**
 * Validates builder parameters before they reach a style attribute.
 *
 * WPBakery has no scoped-selector system, so styling is applied as inline CSS
 * custom properties. Every value is type-checked and anything unrecognised is
 * dropped rather than passed through.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_Style {

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

	public static function topics_schema() {
		return array(
			/* Palette */
			'gold'              => array( 'var' => '--fc-gold', 'type' => 'color' ),
			'navy'              => array( 'var' => '--fc-navy', 'type' => 'color' ),

			/* Section heading */
			'heading_color'     => array( 'var' => '--fc-heading-color', 'type' => 'color' ),
			'heading_size'      => array( 'var' => '--fc-heading-size', 'type' => 'px', 'min' => 10, 'max' => 90 ),
			'heading_lh'        => array( 'var' => '--fc-heading-lh', 'type' => 'px', 'min' => 10, 'max' => 120 ),
			'heading_weight'    => array(
				'var'     => '--fc-heading-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
			'heading_space'     => array( 'var' => '--fc-heading-space', 'type' => 'px', 'max' => 160 ),
			'heading_align'     => array(
				'var'     => '--fc-heading-align',
				'type'    => 'keyword',
				'allowed' => array( 'left', 'center', 'right' ),
			),

			/* Layout */
			'column_gap'        => array( 'var' => '--fc-col-gap', 'type' => 'px', 'max' => 160 ),
			'row_gap'           => array( 'var' => '--fc-row-gap', 'type' => 'px', 'max' => 160 ),
			'featured_width'    => array( 'var' => '--fc-featured-width', 'type' => 'number', 'min' => 20, 'max' => 80 ),
			'media_ratio'       => array( 'var' => '--fc-media-ratio', 'type' => 'ratio' ),
			'item_media_ratio'  => array( 'var' => '--fc-item-media-ratio', 'type' => 'ratio' ),
			'item_media_pct'    => array( 'var' => '--fc-item-media-pct', 'type' => 'number', 'min' => 20, 'max' => 80 ),
			'item_gap'          => array( 'var' => '--fc-item-gap', 'type' => 'px', 'max' => 100 ),
			'media_radius'      => array( 'var' => '--fc-media-radius', 'type' => 'px', 'max' => 60 ),

			/* Date badge */
			'date_bg'           => array( 'var' => '--fc-date-bg', 'type' => 'color' ),
			'date_color'        => array( 'var' => '--fc-date-color', 'type' => 'color' ),
			'date_size'         => array( 'var' => '--fc-date-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'date_lh'           => array( 'var' => '--fc-date-lh', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'date_right'        => array( 'var' => '--fc-date-right', 'type' => 'px', 'max' => 120 ),
			'date_bottom'       => array( 'var' => '--fc-date-bottom', 'type' => 'px', 'max' => 120 ),
			'date_padding'      => array( 'var' => '--fc-date-padding', 'type' => 'spacing' ),

			/* Titles */
			'title_font'        => array(
				'var'     => '--fc-title-font',
				'type'    => 'keyword',
				'allowed' => array( 'serif', 'sans-serif' ),
			),
			'title_color'       => array( 'var' => '--fc-title-color', 'type' => 'color' ),
			'title_hover'       => array( 'var' => '--fc-title-hover', 'type' => 'color' ),
			'title_size'        => array( 'var' => '--fc-title-size', 'type' => 'px', 'min' => 10, 'max' => 90 ),
			'title_lh'          => array( 'var' => '--fc-title-lh', 'type' => 'px', 'min' => 10, 'max' => 120 ),
			'item_title_size'   => array( 'var' => '--fc-item-title-size', 'type' => 'px', 'min' => 10, 'max' => 90 ),
			'item_title_lh'     => array( 'var' => '--fc-item-title-lh', 'type' => 'px', 'min' => 10, 'max' => 120 ),
			'item_title_lines'  => array( 'var' => '--fc-item-title-lines', 'type' => 'number', 'min' => 0, 'max' => 12 ),
			'item_text_lines'   => array( 'var' => '--fc-item-text-lines', 'type' => 'number', 'min' => 0, 'max' => 12 ),
			'title_weight'      => array(
				'var'     => '--fc-title-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
			'title_space'       => array( 'var' => '--fc-title-space', 'type' => 'px', 'max' => 80 ),

			/* Excerpt */
			'text_color'        => array( 'var' => '--fc-text-color', 'type' => 'color' ),
			'text_size'         => array( 'var' => '--fc-text-size', 'type' => 'px', 'min' => 8, 'max' => 48 ),
			'text_lh'           => array( 'var' => '--fc-text-lh', 'type' => 'px', 'min' => 8, 'max' => 80 ),
			'item_text_size'    => array( 'var' => '--fc-item-text-size', 'type' => 'px', 'min' => 8, 'max' => 48 ),
			'item_text_lh'      => array( 'var' => '--fc-item-text-lh', 'type' => 'px', 'min' => 8, 'max' => 80 ),
			'text_space'        => array( 'var' => '--fc-text-space', 'type' => 'px', 'max' => 80 ),

			/* Read More */
			'btn_color'         => array( 'var' => '--fc-btn-color', 'type' => 'color' ),
			'btn_bg'            => array( 'var' => '--fc-btn-bg', 'type' => 'color' ),
			'btn_border'        => array( 'var' => '--fc-btn-border', 'type' => 'color' ),
			'btn_color_h'       => array( 'var' => '--fc-btn-color-h', 'type' => 'color' ),
			'btn_bg_h'          => array( 'var' => '--fc-btn-bg-h', 'type' => 'color' ),
			'btn_border_h'      => array( 'var' => '--fc-btn-border-h', 'type' => 'color' ),
			'btn_size'          => array( 'var' => '--fc-btn-size', 'type' => 'px', 'min' => 8, 'max' => 40 ),
			'btn_lh'            => array( 'var' => '--fc-btn-lh', 'type' => 'px', 'min' => 8, 'max' => 60 ),
			'btn_weight'        => array(
				'var'     => '--fc-btn-weight',
				'type'    => 'keyword',
				'allowed' => array( '300', '400', '500', '600', '700', '800' ),
			),
			'btn_padding'       => array( 'var' => '--fc-btn-padding', 'type' => 'spacing' ),
			'btn_radius'        => array( 'var' => '--fc-btn-radius', 'type' => 'px', 'max' => 60 ),
			'btn_border_width'  => array( 'var' => '--fc-btn-border-width', 'type' => 'px', 'max' => 12 ),

			/* Meta line */
			'meta_color'        => array( 'var' => '--fc-meta-color', 'type' => 'color' ),
			'meta_size'         => array( 'var' => '--fc-meta-size', 'type' => 'px', 'min' => 8, 'max' => 30 ),
			'meta_gap'          => array( 'var' => '--fc-meta-gap', 'type' => 'px', 'max' => 60 ),
			'meta_space'        => array( 'var' => '--fc-meta-space', 'type' => 'px', 'max' => 60 ),
		);
	}
}
