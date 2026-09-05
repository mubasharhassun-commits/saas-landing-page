<?php
/**
 * WPBakery Page Builder integration.
 *
 * Both elements are thin mappings over the plugin's own shortcodes, so the
 * builder and a hand-written shortcode produce identical output.
 *
 * WPBakery has no scoped-selector system, so style parameters are validated by
 * TM_Style and written onto the element as inline custom properties.
 *
 * Booleans are mapped as dropdowns rather than checkboxes on purpose: WPBakery
 * omits an unchecked checkbox from the shortcode entirely, which would silently
 * fall back to the attribute default instead of switching the option off.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_WPBakery {

	const CATEGORY = 'Testimonial Manager';

	public static function init() {
		add_action( 'vc_before_init', array( __CLASS__, 'map' ) );
	}

	public static function is_available() {
		return function_exists( 'vc_map' );
	}

	/**
	 * A Yes/No dropdown. WPBakery always writes the chosen value into the
	 * shortcode, so the option can actually be turned off.
	 */
	private static function toggle( $heading, $param, $default = 'yes', $extra = array() ) {
		return array_merge(
			array(
				'type'       => 'dropdown',
				'heading'    => $heading,
				'param_name' => $param,
				'value'      => array(
					__( 'Yes', 'testimonial-manager' ) => 'yes',
					__( 'No', 'testimonial-manager' )  => 'no',
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
				'description'      => __( 'In pixels.', 'testimonial-manager' ),
			),
			$extra
		);
	}

	/**
	 * Published testimonial categories, for the filter dropdown.
	 */
	private static function category_options() {
		$options = array( __( 'All categories', 'testimonial-manager' ) => '' );

		$terms = get_terms(
			array(
				'taxonomy'   => TM_Post_Type::TAXONOMY,
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->name ] = $term->slug;
			}
		}

		return $options;
	}

	private static function order_options() {
		return array(
			__( 'Display Order', 'testimonial-manager' )  => 'menu_order',
			__( 'Date Published', 'testimonial-manager' ) => 'date',
			__( 'Client Name', 'testimonial-manager' )    => 'title',
			__( 'Rating', 'testimonial-manager' )         => 'rating',
			__( 'Random', 'testimonial-manager' )         => 'rand',
		);
	}

	public static function map() {
		if ( ! self::is_available() ) {
			return;
		}

		self::map_grid();
		self::map_slider();
	}

	/* =============================================================
	 * GRID
	 * ============================================================= */

	private static function map_grid() {
		$style  = __( 'Style', 'testimonial-manager' );
		$button = __( 'Button', 'testimonial-manager' );
		$popup  = __( 'Popup', 'testimonial-manager' );

		vc_map(
			array(
				'name'        => __( 'Testimonials Grid', 'testimonial-manager' ),
				'base'        => 'testimonials',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-application-icon-large',
				'description' => __( 'Responsive grid of client testimonials', 'testimonial-manager' ),
				'params'      => array(

					/* ---------- General ---------- */
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Number of Testimonials', 'testimonial-manager' ),
						'param_name'  => 'count',
						'value'       => '6',
						'description' => __( 'Use -1 to show all.', 'testimonial-manager' ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Order By', 'testimonial-manager' ),
						'param_name' => 'orderby',
						'value'      => self::order_options(),
						'std'        => 'menu_order',
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Order', 'testimonial-manager' ),
						'param_name' => 'order',
						'value'      => array(
							__( 'Ascending', 'testimonial-manager' )  => 'ASC',
							__( 'Descending', 'testimonial-manager' ) => 'DESC',
						),
						'std'        => 'ASC',
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Category', 'testimonial-manager' ),
						'param_name' => 'category',
						'value'      => self::category_options(),
						'std'        => '',
					),
					self::toggle( __( 'Featured Only', 'testimonial-manager' ), 'featured', 'no' ),

					/* ---------- Layout ---------- */
					self::size( __( 'Columns - Desktop', 'testimonial-manager' ), 'columns', '3', __( 'Layout', 'testimonial-manager' ), array( 'description' => '' ) ),
					self::size( __( 'Columns - Laptop (1300px)', 'testimonial-manager' ), 'columns_laptop', '', __( 'Layout', 'testimonial-manager' ), array( 'description' => __( 'Leave empty to use the desktop count.', 'testimonial-manager' ) ) ),
					self::size( __( 'Columns - Tablet (1024px)', 'testimonial-manager' ), 'columns_tablet', '2', __( 'Layout', 'testimonial-manager' ), array( 'description' => '' ) ),
					self::size( __( 'Columns - Mobile (767px)', 'testimonial-manager' ), 'columns_mobile', '1', __( 'Layout', 'testimonial-manager' ), array( 'description' => '' ) ),
					self::size( __( 'Card Spacing', 'testimonial-manager' ), 'gap', '24', __( 'Layout', 'testimonial-manager' ) ),

					/* ---------- Display ---------- */
					self::toggle( __( 'Show Rating', 'testimonial-manager' ), 'show_rating', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Client Image', 'testimonial-manager' ), 'show_image', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Position', 'testimonial-manager' ), 'show_position', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Company', 'testimonial-manager' ), 'show_company', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Button', 'testimonial-manager' ), 'show_button', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Button Text', 'testimonial-manager' ),
						'param_name' => 'button_text',
						'value'      => 'READ FULL REVIEW',
						'group'      => __( 'Display', 'testimonial-manager' ),
						'dependency' => array( 'element' => 'show_button', 'value' => array( 'yes' ) ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Short Text Length (words)', 'testimonial-manager' ),
						'param_name'  => 'excerpt_words',
						'value'       => '32',
						'group'       => __( 'Display', 'testimonial-manager' ),
						'description' => __( 'Only applies when a testimonial has no Excerpt of its own. Words are never cut in half.', 'testimonial-manager' ),
					),
					array(
						'type'        => 'attach_image',
						'heading'     => __( 'Default Client Image', 'testimonial-manager' ),
						'param_name'  => 'fallback_image',
						'group'       => __( 'Display', 'testimonial-manager' ),
						'description' => __( 'Used for any testimonial without a Client Image of its own.', 'testimonial-manager' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Client Name HTML Tag', 'testimonial-manager' ),
						'param_name'  => 'title_tag',
						'value'       => array( 'H3' => 'h3', 'H2' => 'h2', 'H4' => 'h4', 'H5' => 'h5', 'H6' => 'h6', 'div' => 'div', 'p' => 'p' ),
						'std'         => 'h3',
						'group'       => __( 'Display', 'testimonial-manager' ),
						'description' => __( 'Pick the level that fits this page heading outline.', 'testimonial-manager' ),
					),

					/* ---------- Style: card ---------- */
					self::color( __( 'Card Background', 'testimonial-manager' ), 'card_bg', '#ffffff', $style ),
					self::color( __( 'Card Border Color', 'testimonial-manager' ), 'card_border_color', '#a9c6c4', $style ),
					self::size( __( 'Card Border Width', 'testimonial-manager' ), 'card_border_width', '1', $style ),
					self::size( __( 'Card Border Radius', 'testimonial-manager' ), 'card_radius', '2', $style ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Card Padding', 'testimonial-manager' ),
						'param_name'       => 'card_padding',
						'value'            => '26',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values, e.g. 26 or 26 20 26 20.', 'testimonial-manager' ),
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Font Family', 'testimonial-manager' ),
						'param_name'       => 'font_family',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'Leave empty to inherit the theme font, e.g. Poppins, sans-serif.', 'testimonial-manager' ),
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Card Shadow', 'testimonial-manager' ),
						'param_name'       => 'card_shadow',
						'value'            => array(
							__( 'None', 'testimonial-manager' )   => 'none',
							__( 'Soft', 'testimonial-manager' )   => 'soft',
							__( 'Medium', 'testimonial-manager' ) => 'medium',
							__( 'Strong', 'testimonial-manager' ) => 'strong',
						),
						'std'              => 'none',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Client Image Size', 'testimonial-manager' ), 'avatar_size', '44', $style ),

					self::color( __( 'Star Color', 'testimonial-manager' ), 'star_color', '#7a9a98', $style ),
					self::color( __( 'Empty Star Color', 'testimonial-manager' ), 'star_empty_color', '#d8e3e2', $style ),
					self::size( __( 'Star Size', 'testimonial-manager' ), 'star_size', '17', $style ),

					self::color( __( 'Testimonial Text Color', 'testimonial-manager' ), 'quote_color', '#33383d', $style ),
					self::size( __( 'Testimonial Text Size', 'testimonial-manager' ), 'quote_size', '15', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Testimonial Alignment', 'testimonial-manager' ),
						'param_name'       => 'quote_align',
						'value'            => array(
							__( 'Justified', 'testimonial-manager' ) => 'justify',
							__( 'Left', 'testimonial-manager' )      => 'left',
							__( 'Center', 'testimonial-manager' )    => 'center',
						),
						'std'              => 'justify',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Testimonial Style', 'testimonial-manager' ),
						'param_name'       => 'quote_style',
						'value'            => array(
							__( 'Italic', 'testimonial-manager' ) => 'italic',
							__( 'Normal', 'testimonial-manager' ) => 'normal',
						),
						'std'              => 'italic',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),

					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Weight', 'testimonial-manager' ),
						'param_name'       => 'quote_weight',
						'value'            => array(
							__( 'Normal', 'testimonial-manager' )     => '400',
							__( 'Light', 'testimonial-manager' )      => '300',
							__( 'Medium', 'testimonial-manager' )     => '500',
							__( 'Semi Bold', 'testimonial-manager' )  => '600',
							__( 'Bold', 'testimonial-manager' )       => '700',
							__( 'Extra Bold', 'testimonial-manager' ) => '800',
						),
						'std'              => '400',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Testimonial Line Height', 'testimonial-manager' ), 'quote_lh', '', $style, array( 'description' => __( 'In pixels. Empty for automatic.', 'testimonial-manager' ) ) ),
					self::size( __( 'Testimonial Letter Spacing', 'testimonial-manager' ), 'quote_spacing', '', $style ),
					self::color( __( 'Client Name Color', 'testimonial-manager' ), 'name_color', '#1f2d3a', $style ),
					self::size( __( 'Client Name Size', 'testimonial-manager' ), 'name_size', '17', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Weight', 'testimonial-manager' ),
						'param_name'       => 'name_weight',
						'value'            => array(
							__( 'Normal', 'testimonial-manager' )     => '400',
							__( 'Light', 'testimonial-manager' )      => '300',
							__( 'Medium', 'testimonial-manager' )     => '500',
							__( 'Semi Bold', 'testimonial-manager' )  => '600',
							__( 'Bold', 'testimonial-manager' )       => '700',
							__( 'Extra Bold', 'testimonial-manager' ) => '800',
						),
						'std'              => '500',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Client Name Line Height', 'testimonial-manager' ), 'name_lh', '', $style ),
					self::size( __( 'Client Name Letter Spacing', 'testimonial-manager' ), 'name_spacing', '', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Client Name Transform', 'testimonial-manager' ),
						'param_name'       => 'name_transform',
						'value'            => array(
							__( 'None', 'testimonial-manager' )       => 'none',
							__( 'Uppercase', 'testimonial-manager' )  => 'uppercase',
							__( 'Capitalize', 'testimonial-manager' ) => 'capitalize',
							__( 'Lowercase', 'testimonial-manager' )  => 'lowercase',
						),
						'std'              => 'none',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::color( __( 'Position / Company Color', 'testimonial-manager' ), 'role_color', '#7b8792', $style ),
					self::size( __( 'Position / Company Size', 'testimonial-manager' ), 'role_size', '13', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Weight', 'testimonial-manager' ),
						'param_name'       => 'role_weight',
						'value'            => array(
							__( 'Normal', 'testimonial-manager' )     => '400',
							__( 'Light', 'testimonial-manager' )      => '300',
							__( 'Medium', 'testimonial-manager' )     => '500',
							__( 'Semi Bold', 'testimonial-manager' )  => '600',
							__( 'Bold', 'testimonial-manager' )       => '700',
							__( 'Extra Bold', 'testimonial-manager' ) => '800',
						),
						'std'              => '400',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Position / Company Line Height', 'testimonial-manager' ), 'role_lh', '', $style ),

					/* ---------- Style: button ---------- */
					self::color( __( 'Background', 'testimonial-manager' ), 'btn_bg', '#7a9a98', $button ),
					self::color( __( 'Text Color', 'testimonial-manager' ), 'btn_color', '#ffffff', $button ),
					self::color( __( 'Border Color', 'testimonial-manager' ), 'btn_border_color', '#7a9a98', $button ),
					self::color( __( 'Hover Background', 'testimonial-manager' ), 'btn_bg_hover', '#658b88', $button ),
					self::color( __( 'Hover Text Color', 'testimonial-manager' ), 'btn_color_hover', '#ffffff', $button ),
					self::color( __( 'Hover Border Color', 'testimonial-manager' ), 'btn_border_hover', '#658b88', $button ),
					self::size( __( 'Border Width', 'testimonial-manager' ), 'btn_border_width', '1', $button ),
					self::size( __( 'Border Radius', 'testimonial-manager' ), 'btn_radius', '2', $button ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Padding', 'testimonial-manager' ),
						'param_name'       => 'btn_padding',
						'value'            => '14 18',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'testimonial-manager' ),
					),
					self::size( __( 'Font Size', 'testimonial-manager' ), 'btn_font_size', '14', $button ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Weight', 'testimonial-manager' ),
						'param_name'       => 'btn_weight',
						'value'            => array(
							__( 'Normal', 'testimonial-manager' )     => '400',
							__( 'Light', 'testimonial-manager' )      => '300',
							__( 'Medium', 'testimonial-manager' )     => '500',
							__( 'Semi Bold', 'testimonial-manager' )  => '600',
							__( 'Bold', 'testimonial-manager' )       => '700',
							__( 'Extra Bold', 'testimonial-manager' ) => '800',
						),
						'std'              => '500',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Letter Spacing', 'testimonial-manager' ), 'btn_spacing', '', $button ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Text Transform', 'testimonial-manager' ),
						'param_name'       => 'btn_transform',
						'value'            => array(
							__( 'Uppercase', 'testimonial-manager' )  => 'uppercase',
							__( 'None', 'testimonial-manager' )       => 'none',
							__( 'Capitalize', 'testimonial-manager' ) => 'capitalize',
						),
						'std'              => 'uppercase',
						'group'            => $button,
						'edit_field_class' => 'vc_col-sm-6',
					),

					/* ---------- Style: popup ---------- */
					self::size( __( 'Max Width', 'testimonial-manager' ), 'popup_max_width', '840', $popup ),
					self::color( __( 'Background', 'testimonial-manager' ), 'popup_bg', '#ffffff', $popup ),
					self::color( __( 'Overlay Color', 'testimonial-manager' ), 'popup_overlay', 'rgba(20,30,38,0.72)', $popup ),
					self::color( __( 'Text Color', 'testimonial-manager' ), 'popup_text_color', '#33383d', $popup ),
					self::size( __( 'Text Size', 'testimonial-manager' ), 'popup_font_size', '16', $popup ),
					self::size( __( 'Border Radius', 'testimonial-manager' ), 'popup_radius', '2', $popup ),
					self::size( __( 'Padding', 'testimonial-manager' ), 'popup_padding', '38', $popup ),
					self::color( __( 'Close Button Background', 'testimonial-manager' ), 'popup_close_bg', '#7a9a98', $popup ),
					self::color( __( 'Close Button Icon', 'testimonial-manager' ), 'popup_close_color', '#ffffff', $popup ),
					self::size( __( 'Close Button Size', 'testimonial-manager' ), 'popup_close_size', '40', $popup ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Close Button Position', 'testimonial-manager' ),
						'param_name'       => 'popup_close_position',
						'value'            => array(
							__( 'Top Right (inside)', 'testimonial-manager' )    => 'top-right',
							__( 'Top Center (on the edge)', 'testimonial-manager' ) => 'top-center',
							__( 'Top Left (inside)', 'testimonial-manager' )     => 'top-left',
						),
						'std'              => 'top-right',
						'group'            => $popup,
						'edit_field_class' => 'vc_col-sm-6',
					),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS Class', 'testimonial-manager' ),
						'param_name' => 'class',
						'group'      => $style,
					),
				),
			)
		);
	}

	/* =============================================================
	 * SLIDER
	 * ============================================================= */

	private static function map_slider() {
		$style = __( 'Style', 'testimonial-manager' );
		$nav   = __( 'Arrows & Dots', 'testimonial-manager' );

		vc_map(
			array(
				'name'        => __( 'Testimonials Slider', 'testimonial-manager' ),
				'base'        => 'testimonials_slider',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-slider',
				'description' => __( 'One testimonial at a time, for a banner area', 'testimonial-manager' ),
				'params'      => array(

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Number of Slides', 'testimonial-manager' ),
						'param_name' => 'count',
						'value'      => '5',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Order By', 'testimonial-manager' ),
						'param_name'  => 'orderby',
						'value'       => self::order_options(),
						'std'         => 'date',
						'description' => __( 'Date Published with Newest First puts the latest testimonial on the first slide.', 'testimonial-manager' ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Order', 'testimonial-manager' ),
						'param_name' => 'order',
						'value'      => array(
							__( 'Newest / Highest First', 'testimonial-manager' ) => 'DESC',
							__( 'Oldest / Lowest First', 'testimonial-manager' )  => 'ASC',
						),
						'std'        => 'DESC',
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Category', 'testimonial-manager' ),
						'param_name' => 'category',
						'value'      => self::category_options(),
						'std'        => '',
					),
					self::toggle( __( 'Featured Only', 'testimonial-manager' ), 'featured', 'no' ),

					/* ---------- Slider ---------- */
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Autoplay Speed (ms)', 'testimonial-manager' ),
						'param_name'  => 'autoplay',
						'value'       => '6000',
						'group'       => __( 'Slider', 'testimonial-manager' ),
						'description' => __( 'Time each slide is shown. Use 0 to turn autoplay off.', 'testimonial-manager' ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Transition', 'testimonial-manager' ),
						'param_name' => 'effect',
						'value'      => array(
							__( 'Fade', 'testimonial-manager' )  => 'fade',
							__( 'Slide', 'testimonial-manager' ) => 'slide',
						),
						'std'        => 'fade',
						'group'      => __( 'Slider', 'testimonial-manager' ),
					),
					self::toggle( __( 'Pause on Hover', 'testimonial-manager' ), 'pause_hover', 'yes', array( 'group' => __( 'Slider', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Arrows', 'testimonial-manager' ), 'arrows', 'no', array( 'group' => __( 'Slider', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Dots', 'testimonial-manager' ), 'dots', 'no', array( 'group' => __( 'Slider', 'testimonial-manager' ) ) ),

					/* ---------- Display ---------- */
					self::toggle( __( 'Show Rating', 'testimonial-manager' ), 'show_rating', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Client Image', 'testimonial-manager' ), 'show_image', 'yes', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Position', 'testimonial-manager' ), 'show_position', 'no', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Company', 'testimonial-manager' ), 'show_company', 'no', array( 'group' => __( 'Display', 'testimonial-manager' ) ) ),
					self::toggle( __( 'Show Full Testimonial', 'testimonial-manager' ), 'full_text', 'no', array( 'group' => __( 'Display', 'testimonial-manager' ), 'description' => __( 'No trims each slide to the length below, which keeps the panel a consistent height.', 'testimonial-manager' ) ) ),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Text Length (words)', 'testimonial-manager' ),
						'param_name' => 'excerpt_words',
						'value'      => '45',
						'group'      => __( 'Display', 'testimonial-manager' ),
						'dependency' => array( 'element' => 'full_text', 'value' => array( 'no' ) ),
					),
					array(
						'type'       => 'attach_image',
						'heading'    => __( 'Default Client Image', 'testimonial-manager' ),
						'param_name' => 'fallback_image',
						'group'      => __( 'Display', 'testimonial-manager' ),
					),

					/* ---------- Style ---------- */
					self::color( __( 'Panel Background', 'testimonial-manager' ), 'panel_bg', 'rgba(18,26,32,0.72)', $style ),
					self::size( __( 'Panel Border Radius', 'testimonial-manager' ), 'panel_radius', '0', $style ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Panel Padding', 'testimonial-manager' ),
						'param_name'       => 'panel_padding',
						'value'            => '44',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'One to four pixel values.', 'testimonial-manager' ),
					),
					self::size( __( 'Panel Minimum Height', 'testimonial-manager' ), 'panel_min_height', '0', $style ),
					self::size( __( 'Client Image Size', 'testimonial-manager' ), 'avatar_size', '52', $style ),

					self::color( __( 'Star Color', 'testimonial-manager' ), 'star_color', '#7a9a98', $style ),
					self::color( __( 'Empty Star Color', 'testimonial-manager' ), 'star_empty_color', 'rgba(255,255,255,0.3)', $style ),
					self::size( __( 'Star Size', 'testimonial-manager' ), 'star_size', '18', $style ),

					self::color( __( 'Testimonial Color', 'testimonial-manager' ), 'quote_color', '#ffffff', $style ),
					self::size( __( 'Testimonial Size', 'testimonial-manager' ), 'quote_size', '19', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Testimonial Weight', 'testimonial-manager' ),
						'param_name'       => 'quote_weight',
						'value'            => array(
							__( 'Normal', 'testimonial-manager' )     => '400',
							__( 'Light', 'testimonial-manager' )      => '300',
							__( 'Medium', 'testimonial-manager' )     => '500',
							__( 'Semi Bold', 'testimonial-manager' )  => '600',
							__( 'Bold', 'testimonial-manager' )       => '700',
						),
						'std'              => '400',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::size( __( 'Testimonial Line Height', 'testimonial-manager' ), 'quote_lh', '', $style, array( 'description' => __( 'In pixels. Empty for automatic.', 'testimonial-manager' ) ) ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Testimonial Style', 'testimonial-manager' ),
						'param_name'       => 'quote_style',
						'value'            => array(
							__( 'Italic', 'testimonial-manager' ) => 'italic',
							__( 'Normal', 'testimonial-manager' ) => 'normal',
						),
						'std'              => 'italic',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Font Family', 'testimonial-manager' ),
						'param_name'       => 'font_family',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'Leave empty to inherit the theme font.', 'testimonial-manager' ),
					),
					self::color( __( 'Client Name Color', 'testimonial-manager' ), 'name_color', '#ffffff', $style ),
					self::size( __( 'Client Name Size', 'testimonial-manager' ), 'name_size', '18', $style ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Client Name Weight', 'testimonial-manager' ),
						'param_name'       => 'name_weight',
						'value'            => array(
							__( 'Medium', 'testimonial-manager' )    => '500',
							__( 'Normal', 'testimonial-manager' )    => '400',
							__( 'Semi Bold', 'testimonial-manager' ) => '600',
							__( 'Bold', 'testimonial-manager' )      => '700',
						),
						'std'              => '500',
						'group'            => $style,
						'edit_field_class' => 'vc_col-sm-6',
					),
					self::color( __( 'Position / Company Color', 'testimonial-manager' ), 'role_color', 'rgba(255,255,255,0.75)', $style ),

					/* ---------- Arrows & dots ---------- */
					self::color( __( 'Arrow Color', 'testimonial-manager' ), 'nav_color', '#ffffff', $nav ),
					self::color( __( 'Arrow Background', 'testimonial-manager' ), 'nav_bg', 'rgba(255,255,255,0.14)', $nav ),
					self::color( __( 'Dot Color', 'testimonial-manager' ), 'dot_color', 'rgba(255,255,255,0.4)', $nav ),
					self::color( __( 'Active Dot Color', 'testimonial-manager' ), 'dot_active_color', '#7a9a98', $nav ),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS Class', 'testimonial-manager' ),
						'param_name' => 'class',
						'group'      => $style,
					),
				),
			)
		);
	}
}
