<?php
/**
 * WPBakery Page Builder integration for WL Cards and WL News.
 *
 * Both elements are thin mappings over the plugin's shortcodes. Style
 * parameters are validated by WL_Style and written as inline custom
 * properties, since WPBakery has no scoped-selector system.
 *
 * Booleans are Yes/No dropdowns rather than checkboxes: WPBakery omits an
 * unchecked checkbox from the shortcode, which would silently fall back to the
 * attribute default instead of switching the option off.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_WPBakery {

	const CATEGORY = 'Waters Law';

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
					__( 'Yes', 'waterslaw' ) => 'yes',
					__( 'No', 'waterslaw' )  => 'no',
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
				'description'      => __( 'In pixels.', 'waterslaw' ),
			),
			$extra
		);
	}

	public static function map() {
		if ( ! self::is_available() ) {
			return;
		}

		self::map_cards();
		self::map_news();
	}

	/* =============================================================
	 * WL CARDS
	 * ============================================================= */

	private static function map_cards() {
		$style   = __( 'Style', 'waterslaw' );
		$overlay = __( 'Overlay & Title', 'waterslaw' );
		$button  = __( 'Hover Button', 'waterslaw' );

		vc_map(
			array(
				'name'        => __( 'WL Cards (Vessels / Cases)', 'waterslaw' ),
				'base'        => 'wl_cards',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-application-icon-large',
				'description' => __( 'Vessels, Cases or custom cards as a grid or auto-scrolling slider', 'waterslaw' ),
				'params'      => array(

					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Source', 'waterslaw' ),
						'param_name'  => 'source',
						'value'       => array(
							__( 'Vessels (post type)', 'waterslaw' )         => 'vessels',
							__( 'Cases (post type)', 'waterslaw' )           => 'cases',
							__( 'Custom Cards (build them here)', 'waterslaw' ) => 'manual',
						),
						'std'         => 'vessels',
						'description' => __( 'Choose Custom Cards to add your own images, text and page links below.', 'waterslaw' ),
					),
					array(
						'type'       => 'param_group',
						'heading'    => __( 'Cards', 'waterslaw' ),
						'param_name' => 'items',
						'dependency' => array( 'element' => 'source', 'value' => array( 'manual' ) ),
						'params'     => array(
							array(
								'type'       => 'attach_image',
								'heading'    => __( 'Image', 'waterslaw' ),
								'param_name' => 'item_image',
							),
							array(
								'type'        => 'textfield',
								'heading'     => __( 'Text on Image', 'waterslaw' ),
								'param_name'  => 'item_text',
								'description' => __( 'Leave blank for an image-only card.', 'waterslaw' ),
							),
							array(
								'type'        => 'vc_link',
								'heading'     => __( 'Link', 'waterslaw' ),
								'param_name'  => 'item_link',
								'description' => __( 'The page this card opens. Leave blank to make it non-clickable.', 'waterslaw' ),
							),
						),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Number of Items', 'waterslaw' ),
						'param_name'  => 'count',
						'value'       => '-1',
						'description' => __( 'Use -1 to show all.', 'waterslaw' ),
						'dependency'  => array( 'element' => 'source', 'value' => array( 'vessels', 'cases' ) ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Order By', 'waterslaw' ),
						'param_name' => 'orderby',
						'value'      => array(
							__( 'Menu Order', 'waterslaw' ) => 'menu_order',
							__( 'Date', 'waterslaw' )       => 'date',
							__( 'Title', 'waterslaw' )      => 'title',
							__( 'Random', 'waterslaw' )     => 'rand',
						),
						'std'        => 'menu_order',
						'dependency' => array( 'element' => 'source', 'value' => array( 'vessels', 'cases' ) ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Order', 'waterslaw' ),
						'param_name' => 'order',
						'value'      => array( 'ASC' => 'ASC', 'DESC' => 'DESC' ),
						'std'        => 'ASC',
						'dependency' => array( 'element' => 'source', 'value' => array( 'vessels', 'cases' ) ),
					),

					/* ---------- Layout ---------- */
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Layout', 'waterslaw' ),
						'param_name'  => 'layout',
						'value'       => array(
							__( 'Grid', 'waterslaw' )              => 'grid',
							__( 'Continuous Slider', 'waterslaw' ) => 'carousel',
						),
						'std'         => 'grid',
						'group'       => __( 'Layout', 'waterslaw' ),
						'description' => __( 'Grid = static rows. Continuous Slider = seamless auto-scroll.', 'waterslaw' ),
					),
					self::toggle(
						__( 'Use Card Links', 'waterslaw' ),
						'link_cards',
						'yes',
						array(
							'group'       => __( 'Layout', 'waterslaw' ),
							'description' => __( 'A card links only where you have given it a link of your own - on the card below for Custom Cards, or in the post\'s "Card Settings" box for Vessels and Cases. Set this to No to switch every link off at once.', 'waterslaw' ),
						)
					),
					self::size( __( 'Columns - Desktop', 'waterslaw' ), 'columns', '4', __( 'Layout', 'waterslaw' ), array( 'description' => '' ) ),
					self::size( __( 'Columns - Laptop (1300px)', 'waterslaw' ), 'columns_laptop', '', __( 'Layout', 'waterslaw' ), array( 'description' => __( 'Leave empty to use the desktop count.', 'waterslaw' ) ) ),
					self::size( __( 'Columns - Tablet (1024px)', 'waterslaw' ), 'columns_tablet', '2', __( 'Layout', 'waterslaw' ), array( 'description' => '' ) ),
					self::size( __( 'Columns - Mobile (767px)', 'waterslaw' ), 'columns_mobile', '1', __( 'Layout', 'waterslaw' ), array( 'description' => '' ) ),
					self::size( __( 'Gap', 'waterslaw' ), 'gap', '8', __( 'Layout', 'waterslaw' ) ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Card Ratio (w/h)', 'waterslaw' ),
						'param_name'       => 'ratio',
						'value'            => '3/4',
						'group'            => __( 'Layout', 'waterslaw' ),
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'e.g. 3/4 portrait, 1/1 square, 4/3 landscape.', 'waterslaw' ),
					),
					self::size( __( 'Corner Radius', 'waterslaw' ), 'radius', '0', __( 'Layout', 'waterslaw' ) ),
					self::toggle( __( 'Use Custom Card Size', 'waterslaw' ), 'custom_size', 'no', array( 'group' => __( 'Layout', 'waterslaw' ), 'description' => __( 'Sets an exact card width and height, overriding columns and ratio.', 'waterslaw' ) ) ),
					self::size( __( 'Card Width', 'waterslaw' ), 'card_width', '360', __( 'Layout', 'waterslaw' ), array( 'dependency' => array( 'element' => 'custom_size', 'value' => array( 'yes' ) ) ) ),
					self::size( __( 'Card Height', 'waterslaw' ), 'card_height', '520', __( 'Layout', 'waterslaw' ), array( 'dependency' => array( 'element' => 'custom_size', 'value' => array( 'yes' ) ) ) ),

					/* ---------- Slider ---------- */
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Scroll Duration (seconds)', 'waterslaw' ),
						'param_name'  => 'duration',
						'value'       => '30',
						'group'       => __( 'Slider', 'waterslaw' ),
						'description' => __( 'Higher is slower. Increase for more items.', 'waterslaw' ),
						'dependency'  => array( 'element' => 'layout', 'value' => array( 'carousel' ) ),
					),
					self::toggle( __( 'Pause on Hover', 'waterslaw' ), 'pause_hover', 'yes', array( 'group' => __( 'Slider', 'waterslaw' ), 'dependency' => array( 'element' => 'layout', 'value' => array( 'carousel' ) ) ) ),

					/* ---------- Overlay & title ---------- */
					self::color( __( 'Overlay (normal)', 'waterslaw' ), 'overlay', 'rgba(10,25,40,0.85)', $overlay ),
					self::color( __( 'Overlay (on hover)', 'waterslaw' ), 'hover_overlay', 'rgba(0,0,0,0.55)', $overlay ),
					array(
						'type'        => 'attach_image',
						'heading'     => __( 'Overlay Image (normal)', 'waterslaw' ),
						'param_name'  => 'overlay_image',
						'group'       => $overlay,
						'description' => __( 'Optional image layered over the photo. The colour above stays underneath it.', 'waterslaw' ),
					),
					array(
						'type'        => 'attach_image',
						'heading'     => __( 'Overlay Image (on hover)', 'waterslaw' ),
						'param_name'  => 'hover_overlay_image',
						'group'       => $overlay,
					),
					self::size( __( 'Overlay Opacity (normal) %', 'waterslaw' ), 'overlay_opacity', '100', $overlay, array( 'description' => __( '0-100.', 'waterslaw' ) ) ),
					self::size( __( 'Overlay Opacity (hover) %', 'waterslaw' ), 'hover_overlay_opacity', '100', $overlay, array( 'description' => __( '0-100.', 'waterslaw' ) ) ),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Headings', 'waterslaw' ),
						'param_name'  => 'title_source',
						'value'       => array(
							__( 'Use each post title', 'waterslaw' )   => 'post',
							__( 'Write them here', 'waterslaw' )       => 'custom',
							__( 'No headings at all', 'waterslaw' )    => 'none',
						),
						'std'         => 'post',
						'group'       => $overlay,
						'dependency'  => array( 'element' => 'source', 'value' => array( 'vessels', 'cases' ) ),
						'description' => __( 'Custom Cards always use the text typed on each card.', 'waterslaw' ),
					),
					array(
						'type'        => 'textarea',
						'heading'     => __( 'Headings (one per line)', 'waterslaw' ),
						'param_name'  => 'titles',
						'group'       => $overlay,
						'dependency'  => array( 'element' => 'title_source', 'value' => array( 'custom' ) ),
						'description' => __( 'One heading per line, in the same order as the cards. Leave a line empty to keep that card\'s own title.', 'waterslaw' ),
					),
					self::color( __( 'Title Color', 'waterslaw' ), 'title_color', '#ffffff', $overlay ),
					self::size( __( 'Title Size', 'waterslaw' ), 'title_size', '18', $overlay ),
					self::size( __( 'Title Line Height', 'waterslaw' ), 'title_line_height', '', $overlay, array( 'description' => __( 'In pixels. Leave empty for automatic.', 'waterslaw' ) ) ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Title Weight', 'waterslaw' ),
						'param_name'       => 'title_weight',
						'value'            => array(
							__( 'Semi Bold', 'waterslaw' )   => '600',
							__( 'Light', 'waterslaw' )       => '300',
							__( 'Normal', 'waterslaw' )      => '400',
							__( 'Medium', 'waterslaw' )      => '500',
							__( 'Bold', 'waterslaw' )        => '700',
							__( 'Extra Bold', 'waterslaw' )  => '800',
						),
						'std'              => '600',
						'group'            => $overlay,
						'edit_field_class' => 'vc_col-sm-6',
					),

					/* ---------- Hover button ---------- */
					self::toggle( __( 'Show Read More Button', 'waterslaw' ), 'show_button', 'no', array( 'group' => $button ) ),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Button Text', 'waterslaw' ),
						'param_name' => 'button_text',
						'value'      => 'READ MORE',
						'group'      => $button,
						'dependency' => array( 'element' => 'show_button', 'value' => array( 'yes' ) ),
					),
					self::toggle(
						__( 'Hide Title on Hover', 'waterslaw' ),
						'hide_title_hover',
						'yes',
						array(
							'group'       => $button,
							'description' => __( 'Only applies while the Read More button is on - with no button the title always stays put.', 'waterslaw' ),
						)
					),
					self::color( __( 'Button Text Color', 'waterslaw' ), 'btn_color', '#ffffff', $button ),
					self::color( __( 'Button Border Color', 'waterslaw' ), 'btn_border', '#ffffff', $button ),
					self::color( __( 'Button Background', 'waterslaw' ), 'btn_bg', 'rgba(0,0,0,0.15)', $button ),
					self::color( __( 'Button Text Color (hover)', 'waterslaw' ), 'btn_color_h', '', $button ),
					self::color( __( 'Button Border Color (hover)', 'waterslaw' ), 'btn_border_h', '', $button ),
					self::color( __( 'Button Background (hover)', 'waterslaw' ), 'btn_bg_h', '', $button, array( 'description' => __( 'Leave the three hover colours empty to keep the normal ones.', 'waterslaw' ) ) ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Button Padding', 'waterslaw' ),
						'param_name'       => 'btn_padding',
						'value'            => '9 22',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'waterslaw' ),
					),
					self::size( __( 'Button Border Width', 'waterslaw' ), 'btn_border_width', '1', $button ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Button Border Style', 'waterslaw' ),
						'param_name'       => 'btn_border_style',
						'value'            => array(
							__( 'Solid', 'waterslaw' )  => 'solid',
							__( 'Dashed', 'waterslaw' ) => 'dashed',
							__( 'Dotted', 'waterslaw' ) => 'dotted',
							__( 'Double', 'waterslaw' ) => 'double',
							__( 'None', 'waterslaw' )   => 'none',
						),
						'std'              => 'solid',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Button Radius', 'waterslaw' ), 'btn_radius', '0', $button ),
					self::size( __( 'Button Font Size', 'waterslaw' ), 'btn_size', '13', $button ),
					self::size( __( 'Button Letter Spacing', 'waterslaw' ), 'btn_spacing', '', $button, array( 'description' => __( 'In pixels. Leave empty for the default 0.5.', 'waterslaw' ) ) ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Button Weight', 'waterslaw' ),
						'param_name'       => 'btn_weight',
						'value'            => array(
							__( 'Normal', 'waterslaw' )     => '400',
							__( 'Light', 'waterslaw' )      => '300',
							__( 'Medium', 'waterslaw' )     => '500',
							__( 'Semi Bold', 'waterslaw' )  => '600',
							__( 'Bold', 'waterslaw' )       => '700',
							__( 'Extra Bold', 'waterslaw' ) => '800',
						),
						'std'              => '400',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
					),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS Class', 'waterslaw' ),
						'param_name' => 'class',
						'group'      => $style,
					),
				),
			)
		);
	}

	/* =============================================================
	 * WL NEWS
	 * ============================================================= */

	private static function map_news() {
		$layout   = __( 'Layout', 'waterslaw' );
		$featured = __( 'Featured', 'waterslaw' );
		$list     = __( 'List', 'waterslaw' );
		$button   = __( 'Read More Button', 'waterslaw' );

		vc_map(
			array(
				'name'        => __( 'WL News (Featured + List)', 'waterslaw' ),
				'base'        => 'wl_news',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-application-icon-large',
				'description' => __( 'One large featured post beside a list of recent posts', 'waterslaw' ),
				'params'      => array(

					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Source', 'waterslaw' ),
						'param_name' => 'source',
						'value'      => array(
							__( 'News (CPT)', 'waterslaw' )  => 'news',
							__( 'Blog Posts', 'waterslaw' )  => 'post',
						),
						'std'        => 'news',
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Category Slug', 'waterslaw' ),
						'param_name'  => 'category',
						'description' => __( 'Filter to one category term slug. Blank shows all.', 'waterslaw' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Total Posts', 'waterslaw' ),
						'param_name'  => 'count',
						'value'       => '3',
						'description' => __( 'One becomes the big featured post; the rest fill the list. Between 2 and 10.', 'waterslaw' ),
					),

					self::toggle( __( 'Show Date', 'waterslaw' ), 'show_date', 'yes', array( 'group' => __( 'Meta', 'waterslaw' ) ) ),
					self::toggle( __( 'Show Category', 'waterslaw' ), 'show_category', 'yes', array( 'group' => __( 'Meta', 'waterslaw' ) ) ),
					self::toggle( __( 'Show Author', 'waterslaw' ), 'show_author', 'yes', array( 'group' => __( 'Meta', 'waterslaw' ) ) ),
					self::color( __( 'Meta Text Color', 'waterslaw' ), 'meta_color', '#8a8a8a', __( 'Meta', 'waterslaw' ) ),
					self::color( __( 'Meta Icon Color', 'waterslaw' ), 'meta_icon_color', '', __( 'Meta', 'waterslaw' ) ),
					self::size( __( 'Meta Icon Size', 'waterslaw' ), 'meta_icon_size', '14', __( 'Meta', 'waterslaw' ) ),
					self::size( __( 'Meta Gap', 'waterslaw' ), 'meta_gap', '14', __( 'Meta', 'waterslaw' ) ),

					self::color( __( 'Accent Color', 'waterslaw' ), 'accent', '#7a9a98', $layout ),
					self::size( __( 'Gap: Featured to List', 'waterslaw' ), 'columns_gap', '34', $layout ),
					self::size( __( 'Gap Between List Items', 'waterslaw' ), 'list_gap', '24', $layout ),

					self::size( __( 'Height', 'waterslaw' ), 'featured_height', '340', $featured ),
					self::size( __( 'Corner Radius', 'waterslaw' ), 'featured_radius', '4', $featured ),
					self::color( __( 'Overlay Color', 'waterslaw' ), 'featured_overlay', 'rgba(0,0,0,0.75)', $featured ),
					self::color( __( 'Title Color', 'waterslaw' ), 'featured_title_color', '#ffffff', $featured ),
					self::size( __( 'Title Size', 'waterslaw' ), 'featured_title_size', '22', $featured ),

					self::size( __( 'Thumbnail Width', 'waterslaw' ), 'thumb_w', '92', $list ),
					self::size( __( 'Thumbnail Height', 'waterslaw' ), 'thumb_h', '92', $list ),
					self::size( __( 'Thumbnail Radius', 'waterslaw' ), 'thumb_radius', '4', $list ),
					self::color( __( 'Title Color', 'waterslaw' ), 'list_title_color', '#1a2b3c', $list ),
					self::color( __( 'Title Hover Color', 'waterslaw' ), 'list_title_hover', '#7a9a98', $list ),
					self::size( __( 'Title Size', 'waterslaw' ), 'list_title_size', '17', $list ),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Button Text', 'waterslaw' ),
						'param_name' => 'button_text',
						'value'      => 'READ MORE',
						'group'      => $button,
					),
					self::color( __( 'Text Color', 'waterslaw' ), 'btn_color', '#1a2b3c', $button ),
					self::color( __( 'Background', 'waterslaw' ), 'btn_bg', '', $button ),
					self::color( __( 'Border Color', 'waterslaw' ), 'btn_border', '#d9d9d9', $button ),
					self::color( __( 'Hover Text Color', 'waterslaw' ), 'btn_color_h', '#ffffff', $button ),
					self::color( __( 'Hover Background', 'waterslaw' ), 'btn_bg_h', '#7a9a98', $button ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Padding', 'waterslaw' ),
						'param_name'       => 'btn_padding',
						'value'            => '7 16',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'waterslaw' ),
					),
					self::size( __( 'Border Radius', 'waterslaw' ), 'btn_radius', '0', $button ),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS Class', 'waterslaw' ),
						'param_name' => 'class',
						'group'      => $layout,
					),
				),
			)
		);
	}
}
