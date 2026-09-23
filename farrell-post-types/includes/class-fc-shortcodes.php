<?php
/**
 * [fc_topics] - the same renderer the builders use.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_Shortcodes {

	public static function init() {
		add_shortcode( 'fc_topics', array( __CLASS__, 'topics' ) );
	}

	public static function topics( $atts ) {
		$atts = shortcode_atts( FC_Render::defaults(), $atts, 'fc_topics' );

		return FC_Render::topics( $atts );
	}
}
