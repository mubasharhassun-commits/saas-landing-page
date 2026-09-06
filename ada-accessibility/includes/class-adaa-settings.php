<?php
/**
 * Settings schema, defaults and sanitisation.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Holds the option schema and all sanitisation logic.
 */
class ADAA_Settings {

	/**
	 * Default option values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'auto_inject'     => 1,
			'panel_side'      => 'left',
			'button_position' => 'bottom-right',
			'show_skip'       => 1,
			'show_contrast'   => 1,
			'show_text'       => 1,
			'show_reset'      => 1,
			'label_skip'      => 'Skip to Content',
			'label_contrast'  => 'High Contrast',
			'label_text'      => 'Increase Text Size',
			'label_reset'     => 'Clear All',
			'label_close'     => 'Close',
			'skip_target'     => '',
			'skip_offset'     => 0,
			'z_index'         => 2147483646,
			'color_source'    => 'default',
		);
	}

	/**
	 * Merge stored settings over defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$stored = get_option( ADAA_OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Allowed values for the enumerated fields.
	 *
	 * @return array
	 */
	public static function choices() {
		return array(
			'panel_side'      => array( 'left', 'right' ),
			'button_position' => array( 'bottom-right', 'bottom-left', 'middle-right', 'middle-left' ),
			'color_source'    => array( 'primary', 'secondary', 'accent', 'text', 'default' ),
		);
	}

	/**
	 * Sanitise the whole option array.
	 *
	 * Registered as the sanitize_callback for register_setting(), so WordPress
	 * has already verified the nonce and the manage_options capability before
	 * this runs.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$choices  = self::choices();
		$clean    = array();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		// Checkboxes.
		foreach ( array( 'auto_inject', 'show_skip', 'show_contrast', 'show_text', 'show_reset' ) as $key ) {
			$clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}

		// Enumerated selects.
		foreach ( $choices as $key => $allowed ) {
			$value         = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : '';
			$clean[ $key ] = in_array( $value, $allowed, true ) ? $value : $defaults[ $key ];
		}

		// Skip target: a CSS selector, so keep it to safe selector characters.
		$target = isset( $input['skip_target'] ) ? wp_strip_all_tags( wp_unslash( $input['skip_target'] ) ) : '';
		$target = preg_replace( '/[^A-Za-z0-9 _\-#.\[\]=":>~+]/', '', $target );
		$clean['skip_target'] = substr( trim( $target ), 0, 200 );

		/*
		 * Cast, then clamp. absint() would turn -999 into 999 and the clamp
		 * would then hand back the maximum, so typing a negative offset gave
		 * the largest one instead of none.
		 */
		$clean['skip_offset'] = isset( $input['skip_offset'] ) ? max( 0, min( 500, (int) $input['skip_offset'] ) ) : 0;
		$clean['z_index']     = isset( $input['z_index'] ) ? max( 1, min( 2147483646, (int) $input['z_index'] ) ) : $defaults['z_index'];

		// Button labels.
		foreach ( array( 'label_skip', 'label_contrast', 'label_text', 'label_reset', 'label_close' ) as $key ) {
			$value         = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : '';
			$clean[ $key ] = '' !== $value ? $value : $defaults[ $key ];
		}

		return $clean;
	}
}
