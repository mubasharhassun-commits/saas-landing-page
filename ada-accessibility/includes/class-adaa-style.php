<?php
/**
 * Validates builder parameters before they reach a style attribute.
 *
 * The toolbar is driven entirely by CSS custom properties. Elementor sets them
 * through its own generated stylesheet; WPBakery has no equivalent, so its
 * element writes them inline instead. Every value is type-checked here and
 * anything unrecognised is dropped rather than passed through.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Style parameter validation.
 */
class ADAA_Style {

	/**
	 * Hex, rgb()/rgba(), hsl()/hsla(), or a bare colour keyword.
	 *
	 * @param string $value Raw value.
	 * @return string Safe colour, or ''.
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
	 * First number in the string, or null. Taking the first token rather than
	 * stripping non-digits means "10px<junk>9" reads as 10, not 109.
	 *
	 * @param string $value Raw value.
	 * @return float|null
	 */
	protected static function first_number( $value ) {
		if ( ! preg_match( '/-?\d+(?:\.\d+)?/', (string) $value, $m ) ) {
			return null;
		}

		return (float) $m[0];
	}

	/**
	 * A length in pixels.
	 *
	 * @param string $value Raw value.
	 * @param float  $min   Lower bound.
	 * @param float  $max   Upper bound.
	 * @return string
	 */
	public static function px( $value, $min = 0, $max = 2000 ) {
		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return round( max( $min, min( $max, $number ) ), 2 ) . 'px';
	}

	/**
	 * A unitless number.
	 *
	 * @param string $value Raw value.
	 * @param float  $min   Lower bound.
	 * @param float  $max   Upper bound.
	 * @return string
	 */
	public static function number( $value, $min = 0, $max = 2147483647 ) {
		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return (string) max( $min, min( $max, $number ) );
	}

	/**
	 * A duration in seconds, decimals allowed.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function duration( $value ) {
		$number = self::first_number( $value );

		if ( null === $number ) {
			return '';
		}

		return round( max( 0, min( 5, $number ) ), 2 ) . 's';
	}

	/**
	 * A value that must be one of a known set.
	 *
	 * @param string $value   Raw value.
	 * @param array  $allowed Permitted values.
	 * @return string
	 */
	public static function keyword( $value, $allowed ) {
		$value = strtolower( trim( (string) $value ) );

		return in_array( $value, $allowed, true ) ? $value : '';
	}

	/**
	 * Panel shadow presets. Arbitrary box-shadow strings are not accepted, so
	 * there is nothing to sanitise at the point of use.
	 *
	 * @param string $value Preset key.
	 * @return string
	 */
	public static function shadow( $value ) {
		$presets = array(
			'none'   => 'none',
			'soft'   => '0 6px 18px rgba(0,0,0,0.12)',
			'medium' => '0 10px 30px rgba(0,0,0,0.18)',
			'strong' => '0 18px 48px rgba(0,0,0,0.28)',
		);

		$key = self::keyword( $value, array_keys( $presets ) );

		return ( '' === $key ) ? '' : $presets[ $key ];
	}

	/**
	 * Apply a schema to raw parameters and return declarations for a style
	 * attribute. Only properties that validated are included.
	 *
	 * @param array $schema Property name => rule.
	 * @param array $atts   Raw parameter values.
	 * @return string
	 */
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
				case 'duration':
					$value = self::duration( $raw );
					break;
				case 'shadow':
					$value = self::shadow( $raw );
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

	/**
	 * Every style parameter the WPBakery element exposes, mapped onto the
	 * custom property it sets. Mirrors the Elementor widget's Style tab.
	 *
	 * @return array
	 */
	public static function schema() {
		return array(
			/* Launcher button. */
			'launcher_bg'          => array( 'var' => '--adaa-launcher-bg', 'type' => 'color' ),
			'launcher_fg'          => array( 'var' => '--adaa-launcher-fg', 'type' => 'color' ),
			'launcher_hover_bg'    => array( 'var' => '--adaa-launcher-hover-bg', 'type' => 'color' ),
			'launcher_hover_fg'    => array( 'var' => '--adaa-launcher-hover-fg', 'type' => 'color' ),
			'launcher_size'        => array( 'var' => '--adaa-size', 'type' => 'px', 'min' => 24, 'max' => 160 ),
			'launcher_icon'        => array( 'var' => '--adaa-launcher-icon', 'type' => 'px', 'min' => 8, 'max' => 100 ),
			'launcher_radius'      => array( 'var' => '--adaa-launcher-radius', 'type' => 'px', 'max' => 200 ),
			'launcher_ring'        => array( 'var' => '--adaa-launcher-ring', 'type' => 'px', 'max' => 20 ),
			'launcher_ring_color'  => array( 'var' => '--adaa-launcher-ring-color', 'type' => 'color' ),
			'launcher_ring_hover'  => array( 'var' => '--adaa-launcher-ring-hover', 'type' => 'color' ),

			/* Panel. */
			'panel_bg'             => array( 'var' => '--adaa-bg', 'type' => 'color' ),
			'panel_hover'          => array( 'var' => '--adaa-hover', 'type' => 'color' ),
			'panel_w'              => array( 'var' => '--adaa-panel-w', 'type' => 'px', 'min' => 120, 'max' => 700 ),
			'panel_maxw'           => array( 'var' => '--adaa-panel-maxw', 'type' => 'px', 'min' => 120, 'max' => 900 ),
			'panel_pt'             => array( 'var' => '--adaa-panel-pt', 'type' => 'px', 'max' => 120 ),
			'panel_pb'             => array( 'var' => '--adaa-panel-pb', 'type' => 'px', 'max' => 120 ),
			'panel_radius'         => array( 'var' => '--adaa-panel-radius', 'type' => 'px', 'max' => 100 ),
			'panel_shadow'         => array( 'var' => '--adaa-panel-shadow', 'type' => 'shadow' ),

			/* Items. */
			'item_fg'              => array( 'var' => '--adaa-fg', 'type' => 'color' ),
			'item_hover_fg'        => array( 'var' => '--adaa-hover-fg', 'type' => 'color' ),
			'item_font'            => array( 'var' => '--adaa-item-font', 'type' => 'px', 'min' => 8, 'max' => 48 ),
			'item_py'              => array( 'var' => '--adaa-item-py', 'type' => 'px', 'max' => 80 ),
			'item_px'              => array( 'var' => '--adaa-item-px', 'type' => 'px', 'max' => 80 ),
			'item_radius'          => array( 'var' => '--adaa-item-radius', 'type' => 'px', 'max' => 100 ),
			'item_transform'       => array(
				'var'     => '--adaa-item-transform',
				'type'    => 'keyword',
				'allowed' => array( 'none', 'uppercase', 'capitalize', 'lowercase' ),
			),
			'item_justify'         => array(
				'var'     => '--adaa-item-justify',
				'type'    => 'keyword',
				'allowed' => array( 'flex-start', 'center', 'flex-end', 'space-between' ),
			),
			'rule_color'           => array( 'var' => '--adaa-rule', 'type' => 'color' ),
			'rule_hover_color'     => array( 'var' => '--adaa-rule-hover', 'type' => 'color' ),
			'rule_w'               => array( 'var' => '--adaa-rule-w', 'type' => 'px', 'max' => 20 ),

			/* Icons. */
			'icon_color'           => array( 'var' => '--adaa-icon-color', 'type' => 'color' ),
			'icon_hover_color'     => array( 'var' => '--adaa-icon-hover-color', 'type' => 'color' ),
			'icon_size'            => array( 'var' => '--adaa-icon-size', 'type' => 'px', 'min' => 8, 'max' => 80 ),
			'icon_gap'             => array( 'var' => '--adaa-icon-gap', 'type' => 'px', 'max' => 60 ),
			'icon_stroke'          => array( 'var' => '--adaa-icon-stroke', 'type' => 'number', 'min' => 0, 'max' => 8 ),

			/* Toggle dot. */
			'dot_display'          => array(
				'var'     => '--adaa-dot-display',
				'type'    => 'keyword',
				'allowed' => array( 'none', 'block', 'inline-block' ),
			),
			'dot_color'            => array( 'var' => '--adaa-dot-color', 'type' => 'color' ),
			'dot_size'             => array( 'var' => '--adaa-dot-size', 'type' => 'px', 'max' => 40 ),

			/* Placement and motion. */
			'offset_x'             => array( 'var' => '--adaa-offset-x', 'type' => 'px', 'max' => 400 ),
			'offset_y'             => array( 'var' => '--adaa-offset-y', 'type' => 'px', 'max' => 400 ),
			'offset_y_scrolled'    => array( 'var' => '--adaa-offset-y-scrolled', 'type' => 'px', 'max' => 400 ),
			'speed'                => array( 'var' => '--adaa-speed', 'type' => 'duration' ),
			'speed_hover'          => array( 'var' => '--adaa-speed-hover', 'type' => 'duration' ),
			'focus_color'          => array( 'var' => '--adaa-focus', 'type' => 'color' ),
		);
	}
}
