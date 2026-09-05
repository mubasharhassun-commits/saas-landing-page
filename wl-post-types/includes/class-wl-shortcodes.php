<?php
/**
 * [wl_cards] and [wl_news].
 *
 * The WPBakery elements map onto these, so builder output and a hand-written
 * shortcode are identical.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Shortcodes {

	public static function init() {
		add_shortcode( 'wl_cards', array( __CLASS__, 'cards' ) );
		add_shortcode( 'wl_news', array( __CLASS__, 'news' ) );
	}

	/**
	 * Content defaults plus every style key, so shortcode_atts() keeps the
	 * style parameters instead of stripping them as unknown attributes.
	 */
	private static function defaults( $content, $schema ) {
		return array_merge( $content, array_fill_keys( array_keys( $schema ), '' ) );
	}

	public static function cards( $atts ) {
		$atts = shortcode_atts(
			self::defaults( WL_Render::cards_defaults(), WL_Style::cards_schema() ),
			$atts,
			'wl_cards'
		);

		return WL_Render::cards( $atts );
	}

	public static function news( $atts ) {
		$atts = shortcode_atts(
			self::defaults( WL_Render::news_defaults(), WL_Style::news_schema() ),
			$atts,
			'wl_news'
		);

		return WL_Render::news( $atts );
	}
}
