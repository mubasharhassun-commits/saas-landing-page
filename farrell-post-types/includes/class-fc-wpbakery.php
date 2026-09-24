<?php
/**
 * WPBakery Page Builder integration for Trending Topics.
 *
 * The element is a thin mapping over the [fc_topics] shortcode. Style
 * parameters are validated by FC_Style and written as inline custom
 * properties, since WPBakery has no scoped-selector system.
 *
 * Booleans are Yes/No dropdowns rather than checkboxes: WPBakery omits an
 * unchecked checkbox from the shortcode, which would silently fall back to the
 * attribute default instead of switching the option off.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_WPBakery {

	const CATEGORY = 'Farrell';

	public static function init() {
		add_action( 'vc_before_init', array( __CLASS__, 'map' ) );
	}

	public static function is_available() {
		return function_exists( 'vc_map' );
	}

	private static function toggle( $heading, $param, $default = 'yes', $extra = array() ) {
		return array_merge(
			array(
				'type'       => 'dropdown',
				'heading'    => $heading,
				'param_name' => $param,
				'value'      => array(
					__( 'Yes', 'farrell' ) => 'yes',
					__( 'No', 'farrell' )  => 'no',
				),
				'std'        => $default,
			),
			$extra
		);
	}

	private static function color( $heading, $param, $default, $group, $extra = array() ) {
		return array_merge(
			array(
				'type'             => 'colorpicker',
				'heading'          => $heading,
				'param_name'       => $param,
				'value'            => $default,
				'group'            => $group,
				'edit_field_class' => 'vc_col-sm-6',
			),
			$extra
		);
	}

	private static function size( $heading, $param, $default, $group, $extra = array() ) {
		return array_merge(
			array(
				'type'             => 'textfield',
				'heading'          => $heading,
				'param_name'       => $param,
				'value'            => $default,
				'group'            => $group,
				'edit_field_class' => 'vc_col-sm-6',
				'description'      => __( 'In pixels.', 'farrell' ),
			),
			$extra
		);
	}

	private static function weight( $heading, $param, $default, $group ) {
		return array(
			'type'             => 'dropdown',
			'heading'          => $heading,
			'param_name'       => $param,
			'value'            => array( '300', '400', '500', '600', '700', '800' ),
			'std'              => $default,
			'group'            => $group,
			'edit_field_class' => 'vc_col-sm-6',
		);
	}

	public static function map() {
		if ( ! self::is_available() ) {
			return;
		}

		$content = __( 'Content', 'farrell' );
		$layout  = __( 'Layout', 'farrell' );
		$heading = __( 'Section Heading', 'farrell' );
		$titles  = __( 'Titles', 'farrell' );
		$text    = __( 'Excerpt', 'farrell' );
		$date    = __( 'Date Badge', 'farrell' );
		$button  = __( 'Read More Button', 'farrell' );

		vc_map(
			array(
				'name'        => __( 'Trending Topics', 'farrell' ),
				'base'        => 'fc_topics',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-application-icon-large',
				'description' => __( 'A featured story beside a list of recent ones', 'farrell' ),
				'params'      => array(

					/* ---------- Content ---------- */
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Source', 'farrell' ),
						'param_name' => 'source',
						'value'      => array(
							__( 'Trending Topics (CPT)', 'farrell' ) => 'news',
							__( 'Blog Posts', 'farrell' )            => 'post',
						),
						'std'        => 'news',
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Category Slug', 'farrell' ),
						'param_name'  => 'category',
						'description' => __( 'Filter to one category term slug. Blank shows all.', 'farrell' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Total Posts', 'farrell' ),
						'param_name'  => 'count',
						'value'       => '3',
						'description' => __( 'One becomes the featured story; the rest fill the list. Between 2 and 12.', 'farrell' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Order By', 'farrell' ),
						'param_name'  => 'orderby',
						'value'       => array(
							__( 'Recently Added', 'farrell' ) => 'added',
							__( 'Publish Date', 'farrell' )   => 'date',
							__( 'Last Modified', 'farrell' )  => 'modified',
							__( 'Title', 'farrell' )          => 'title',
							__( 'Menu Order', 'farrell' )     => 'menu_order',
						),
						'std'         => 'added',
						'description' => __( 'Recently Added uses the order posts were added to the site, so an article back-dated to an older year still shows first when you upload it. Publish Date uses the date printed on the badge.', 'farrell' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Order', 'farrell' ),
						'param_name'  => 'order',
						'value'       => array(
							__( 'Descending (newest first)', 'farrell' ) => 'DESC',
							__( 'Ascending (oldest first)', 'farrell' )  => 'ASC',
						),
						'std'         => 'DESC',
						'description' => __( 'The first post in this order becomes the featured story.', 'farrell' ),
					),
					self::toggle( __( 'Show Excerpt', 'farrell' ), 'show_excerpt', 'yes', array( 'group' => $content ) ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Featured Excerpt Words', 'farrell' ),
						'param_name'       => 'excerpt_words',
						'value'            => '28',
						'group'            => $content,
						'edit_field_class' => 'vc_col-sm-6',
						'dependency'       => array( 'element' => 'show_excerpt', 'value' => 'yes' ),
						'description'      => __( 'Words to keep before the ellipsis. 0 shows none.', 'farrell' ),
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'List Excerpt Words', 'farrell' ),
						'param_name'       => 'item_excerpt_words',
						'value'            => '18',
						'group'            => $content,
						'edit_field_class' => 'vc_col-sm-6',
						'dependency'       => array( 'element' => 'show_excerpt', 'value' => 'yes' ),
						'description'      => __( 'A list row is narrower than the featured story, so it usually wants fewer.', 'farrell' ),
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Featured Title Words', 'farrell' ),
						'param_name'       => 'title_words',
						'value'            => '0',
						'group'            => $content,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( '0 keeps the whole title.', 'farrell' ),
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'List Title Words', 'farrell' ),
						'param_name'       => 'item_title_words',
						'value'            => '0',
						'group'            => $content,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( '0 keeps the whole title.', 'farrell' ),
					),
					self::toggle( __( 'Show Category', 'farrell' ), 'show_category', 'no', array( 'group' => $content ) ),
					self::toggle( __( 'Show Author', 'farrell' ), 'show_author', 'no', array( 'group' => $content ) ),

					/* ---------- Section heading ---------- */
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Heading Text', 'farrell' ),
						'param_name'  => 'heading',
						'value'       => 'Trending Topics',
						'group'       => $heading,
						'description' => __( 'Leave blank to render no heading.', 'farrell' ),
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Heading Tag', 'farrell' ),
						'param_name'       => 'heading_tag',
						'value'            => array( 'h2', 'h1', 'h3', 'h4', 'div', 'p' ),
						'std'              => 'h2',
						'group'            => $heading,
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Heading Alignment', 'farrell' ),
						'param_name'       => 'heading_align',
						'value'            => array(
							__( 'Center', 'farrell' ) => 'center',
							__( 'Left', 'farrell' )   => 'left',
							__( 'Right', 'farrell' )  => 'right',
						),
						'std'              => 'center',
						'group'            => $heading,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::color( __( 'Heading Color', 'farrell' ), 'heading_color', '#162542', $heading ),
					self::size( __( 'Heading Font Size', 'farrell' ), 'heading_size', '36', $heading ),
					self::size( __( 'Heading Line Height', 'farrell' ), 'heading_lh', '40', $heading ),
					self::weight( __( 'Heading Weight', 'farrell' ), 'heading_weight', '400', $heading ),
					self::size( __( 'Space Below Heading', 'farrell' ), 'heading_space', '44', $heading ),

					/* ---------- Layout ---------- */
					self::color( __( 'Gold', 'farrell' ), 'gold', '#d5af34', $layout ),
					self::color( __( 'Navy', 'farrell' ), 'navy', '#162542', $layout ),
					self::size( __( 'Featured Column Width (%)', 'farrell' ), 'featured_width', '50', $layout, array( 'description' => __( 'Percent of the row the featured story takes.', 'farrell' ) ) ),
					self::size( __( 'Gap: Featured to List', 'farrell' ), 'column_gap', '30', $layout ),
					self::size( __( 'Gap Between List Items', 'farrell' ), 'row_gap', '30', $layout ),
					self::size( __( 'List Photo Width (%)', 'farrell' ), 'item_media_pct', '52', $layout, array( 'description' => __( 'Percent of the list row the photo takes. This is what keeps its height level with the featured photo.', 'farrell' ) ) ),
					self::size( __( 'Gap: Photo to Text', 'farrell' ), 'item_gap', '22', $layout ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'List Photo Ratio', 'farrell' ),
						'param_name'       => 'item_media_ratio',
						'value'            => '375 / 310',
						'group'            => $layout,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'Width / height, e.g. 4 / 3.', 'farrell' ),
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Featured Photo Ratio', 'farrell' ),
						'param_name'       => 'media_ratio',
						'value'            => '750 / 310',
						'group'            => $layout,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'Width / height, e.g. 16 / 10.', 'farrell' ),
					),
					self::size( __( 'Photo Corner Radius', 'farrell' ), 'media_radius', '0', $layout ),

					/* ---------- Date badge ---------- */
					self::toggle( __( 'Show Date Badge', 'farrell' ), 'show_date', 'yes', array( 'group' => $date ) ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Date Format', 'farrell' ),
						'param_name'       => 'date_format',
						'value'            => 'M d, Y',
						'group'            => $date,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'PHP date format, e.g. M d, Y.', 'farrell' ),
					),
					self::color( __( 'Badge Background', 'farrell' ), 'date_bg', '#d5af34', $date ),
					self::color( __( 'Badge Text Color', 'farrell' ), 'date_color', '#162542', $date ),
					self::size( __( 'Badge Font Size', 'farrell' ), 'date_size', '16', $date ),
					self::size( __( 'Badge Line Height', 'farrell' ), 'date_lh', '20', $date ),
					self::size( __( 'Badge Inset From Right', 'farrell' ), 'date_right', '20', $date ),
					self::size( __( 'Badge Inset From Bottom', 'farrell' ), 'date_bottom', '20', $date ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Badge Padding', 'farrell' ),
						'param_name'       => 'date_padding',
						'value'            => '7px 12px',
						'group'            => $date,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'farrell' ),
					),

					/* ---------- Titles ---------- */
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Title Typeface', 'farrell' ),
						'param_name'       => 'title_font',
						'value'            => array(
							__( 'Serif', 'farrell' )      => 'serif',
							__( 'Sans-serif', 'farrell' ) => 'sans-serif',
						),
						'std'              => 'serif',
						'group'            => $titles,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::color( __( 'Title Color', 'farrell' ), 'title_color', '#162542', $titles ),
					self::color( __( 'Title Hover Color', 'farrell' ), 'title_hover', '#d5af34', $titles ),
					self::size( __( 'Featured Title Size', 'farrell' ), 'title_size', '36', $titles ),
					self::size( __( 'Featured Title Line Height', 'farrell' ), 'title_lh', '40', $titles ),
					self::size( __( 'List Title Size', 'farrell' ), 'item_title_size', '26', $titles ),
					self::size( __( 'List Title Line Height', 'farrell' ), 'item_title_lh', '32', $titles ),
					self::weight( __( 'Title Weight', 'farrell' ), 'title_weight', '400', $titles ),
					self::size( __( 'Space Below Title', 'farrell' ), 'title_space', '14', $titles ),

					/* ---------- Excerpt ---------- */
					self::color( __( 'Text Color', 'farrell' ), 'text_color', '#4a5462', $text ),
					self::size( __( 'Featured Text Size', 'farrell' ), 'text_size', '18', $text ),
					self::size( __( 'Featured Line Height', 'farrell' ), 'text_lh', '30', $text ),
					self::size( __( 'List Text Size', 'farrell' ), 'item_text_size', '15', $text ),
					self::size( __( 'List Line Height', 'farrell' ), 'item_text_lh', '24', $text ),
					self::size( __( 'Space Below Text', 'farrell' ), 'text_space', '18', $text ),
					self::color( __( 'Meta Color', 'farrell' ), 'meta_color', '#6b7280', $text ),
					self::size( __( 'Meta Font Size', 'farrell' ), 'meta_size', '13', $text ),

					/* ---------- Button ---------- */
					self::toggle( __( 'Show Button', 'farrell' ), 'show_button', 'yes', array( 'group' => $button ) ),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Button Text', 'farrell' ),
						'param_name' => 'button_text',
						'value'      => 'READ MORE',
						'group'      => $button,
					),
					self::toggle( __( 'Show Arrow', 'farrell' ), 'show_arrow', 'yes', array( 'group' => $button ) ),
					self::color( __( 'Text Color', 'farrell' ), 'btn_color', '#162542', $button ),
					self::color( __( 'Background', 'farrell' ), 'btn_bg', '', $button ),
					self::color( __( 'Border Color', 'farrell' ), 'btn_border', '#162542', $button ),
					self::color( __( 'Hover Text Color', 'farrell' ), 'btn_color_h', '#ffffff', $button ),
					self::color( __( 'Hover Background', 'farrell' ), 'btn_bg_h', '#162542', $button ),
					self::color( __( 'Hover Border Color', 'farrell' ), 'btn_border_h', '#162542', $button ),
					self::size( __( 'Font Size', 'farrell' ), 'btn_size', '18', $button ),
					self::size( __( 'Line Height', 'farrell' ), 'btn_lh', '24', $button ),
					self::weight( __( 'Font Weight', 'farrell' ), 'btn_weight', '400', $button ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Padding', 'farrell' ),
						'param_name'       => 'btn_padding',
						'value'            => '7px 20px',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'farrell' ),
					),
					self::size( __( 'Border Width', 'farrell' ), 'btn_border_width', '1', $button ),
					self::size( __( 'Corner Radius', 'farrell' ), 'btn_radius', '0', $button ),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS Class', 'farrell' ),
						'param_name' => 'class',
						'group'      => $layout,
					),
				),
			)
		);
	}
}
