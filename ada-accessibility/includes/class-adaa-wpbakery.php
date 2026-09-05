<?php
/**
 * WPBakery Page Builder integration.
 *
 * Sits alongside the Elementor widget rather than replacing it: both builders
 * are supported, and both render through ADAA_Renderer so the toolbar markup
 * is identical whichever one places it.
 *
 * WPBakery has no scoped-selector system, so style parameters are validated by
 * ADAA_Style and written onto the element as inline custom properties.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps the toolbar as a WPBakery element.
 */
class ADAA_WPBakery {

	const CATEGORY = 'Accessibility';

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'vc_before_init', array( $this, 'map' ) );
	}

	/**
	 * Whether WPBakery is available.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return function_exists( 'vc_map' );
	}

	/**
	 * A Yes/No dropdown.
	 *
	 * WPBakery omits an unchecked checkbox from the shortcode entirely, which
	 * would silently fall back to the attribute default instead of switching
	 * the option off, so booleans are dropdowns.
	 *
	 * @param string $heading Label.
	 * @param string $param   Param name.
	 * @param string $default Default value.
	 * @param array  $extra   Extra keys.
	 * @return array
	 */
	protected function toggle( $heading, $param, $default = 'yes', $extra = array() ) {
		return array_merge(
			array(
				'type'       => 'dropdown',
				'heading'    => $heading,
				'param_name' => $param,
				'value'      => array(
					__( 'Yes', 'ada-accessibility' ) => 'yes',
					__( 'No', 'ada-accessibility' )  => 'no',
				),
				'std'        => $default,
			),
			$extra
		);
	}

	/**
	 * A colour picker.
	 *
	 * @param string $heading Label.
	 * @param string $param   Param name.
	 * @param string $group   Tab.
	 * @return array
	 */
	protected function color( $heading, $param, $group ) {
		return array(
			'type'             => 'colorpicker',
			'heading'          => $heading,
			'param_name'       => $param,
			'group'            => $group,
			'edit_field_class' => 'vc_col-sm-6',
		);
	}

	/**
	 * A pixel value.
	 *
	 * @param string $heading Label.
	 * @param string $param   Param name.
	 * @param string $group   Tab.
	 * @param string $hint    Description.
	 * @return array
	 */
	protected function size( $heading, $param, $group, $hint = '' ) {
		return array(
			'type'             => 'textfield',
			'heading'          => $heading,
			'param_name'       => $param,
			'group'            => $group,
			'edit_field_class' => 'vc_col-sm-6',
			'description'      => ( '' !== $hint ) ? $hint : __( 'In pixels. Leave empty for the default.', 'ada-accessibility' ),
		);
	}

	/**
	 * Register the element.
	 *
	 * @return void
	 */
	public function map() {
		if ( ! self::is_available() ) {
			return;
		}

		$launcher = __( 'Launcher', 'ada-accessibility' );
		$panel    = __( 'Panel', 'ada-accessibility' );
		$items    = __( 'Items', 'ada-accessibility' );
		$icons    = __( 'Icons', 'ada-accessibility' );
		$place    = __( 'Placement', 'ada-accessibility' );

		vc_map(
			array(
				'name'        => __( 'Accessibility Toolbar', 'ada-accessibility' ),
				'base'        => 'ada_accessibility',
				'category'    => self::CATEGORY,
				'icon'        => 'icon-wpb-application-icon-large',
				'description' => __( 'High contrast, text resizing and skip to content', 'ada-accessibility' ),
				'params'      => array(

					/* ---------------- General ---------------- */
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Panel Side', 'ada-accessibility' ),
						'param_name'  => 'panel_side',
						'value'       => array(
							__( 'Left', 'ada-accessibility' )  => 'left',
							__( 'Right', 'ada-accessibility' ) => 'right',
						),
						'std'         => 'left',
						'description' => __( 'Which side the panel opens from.', 'ada-accessibility' ),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Button Position', 'ada-accessibility' ),
						'param_name' => 'button_position',
						'value'      => array(
							__( 'Bottom Right', 'ada-accessibility' ) => 'bottom-right',
							__( 'Bottom Left', 'ada-accessibility' )  => 'bottom-left',
							__( 'Top Right', 'ada-accessibility' )    => 'top-right',
							__( 'Top Left', 'ada-accessibility' )     => 'top-left',
						),
						'std'        => 'bottom-right',
					),

					/* ---------------- Buttons ---------------- */
					$this->toggle( __( 'Show Skip to Content', 'ada-accessibility' ), 'show_skip', 'yes', array( 'group' => __( 'Buttons', 'ada-accessibility' ) ) ),
					$this->toggle( __( 'Show High Contrast', 'ada-accessibility' ), 'show_contrast', 'yes', array( 'group' => __( 'Buttons', 'ada-accessibility' ) ) ),
					$this->toggle( __( 'Show Text Size', 'ada-accessibility' ), 'show_text', 'yes', array( 'group' => __( 'Buttons', 'ada-accessibility' ) ) ),
					$this->toggle( __( 'Show Clear All', 'ada-accessibility' ), 'show_reset', 'yes', array( 'group' => __( 'Buttons', 'ada-accessibility' ) ) ),

					array(
						'type'       => 'textfield',
						'heading'    => __( 'Skip to Content Label', 'ada-accessibility' ),
						'param_name' => 'label_skip',
						'value'      => 'Skip to Content',
						'group'      => __( 'Buttons', 'ada-accessibility' ),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'High Contrast Label', 'ada-accessibility' ),
						'param_name' => 'label_contrast',
						'value'      => 'High Contrast',
						'group'      => __( 'Buttons', 'ada-accessibility' ),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Text Size Label', 'ada-accessibility' ),
						'param_name' => 'label_text',
						'value'      => 'Increase Text Size',
						'group'      => __( 'Buttons', 'ada-accessibility' ),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Clear All Label', 'ada-accessibility' ),
						'param_name' => 'label_reset',
						'value'      => 'Clear All',
						'group'      => __( 'Buttons', 'ada-accessibility' ),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Close Label', 'ada-accessibility' ),
						'param_name' => 'label_close',
						'value'      => 'Close',
						'group'      => __( 'Buttons', 'ada-accessibility' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Skip to Content Target', 'ada-accessibility' ),
						'param_name'  => 'skip_target',
						'group'       => __( 'Buttons', 'ada-accessibility' ),
						'description' => __( 'CSS selector to jump to, e.g. #main. Leave empty to auto-detect.', 'ada-accessibility' ),
					),
					$this->size( __( 'Skip Offset', 'ada-accessibility' ), 'skip_offset', __( 'Buttons', 'ada-accessibility' ), __( 'Pixels to leave above the target, for a sticky header.', 'ada-accessibility' ) ),

					/* ---------------- Launcher ---------------- */
					$this->color( __( 'Background', 'ada-accessibility' ), 'launcher_bg', $launcher ),
					$this->color( __( 'Icon Colour', 'ada-accessibility' ), 'launcher_fg', $launcher ),
					$this->color( __( 'Hover Background', 'ada-accessibility' ), 'launcher_hover_bg', $launcher ),
					$this->color( __( 'Hover Icon Colour', 'ada-accessibility' ), 'launcher_hover_fg', $launcher ),
					$this->size( __( 'Button Size', 'ada-accessibility' ), 'launcher_size', $launcher ),
					$this->size( __( 'Icon Size', 'ada-accessibility' ), 'launcher_icon', $launcher ),
					$this->size( __( 'Corner Radius', 'ada-accessibility' ), 'launcher_radius', $launcher ),
					$this->size( __( 'Ring Width', 'ada-accessibility' ), 'launcher_ring', $launcher ),
					$this->color( __( 'Ring Colour', 'ada-accessibility' ), 'launcher_ring_color', $launcher ),
					$this->color( __( 'Ring Colour (hover)', 'ada-accessibility' ), 'launcher_ring_hover', $launcher ),

					/* ---------------- Panel ---------------- */
					$this->color( __( 'Background', 'ada-accessibility' ), 'panel_bg', $panel ),
					$this->color( __( 'Item Hover Background', 'ada-accessibility' ), 'panel_hover', $panel ),
					$this->size( __( 'Width', 'ada-accessibility' ), 'panel_w', $panel ),
					$this->size( __( 'Max Width', 'ada-accessibility' ), 'panel_maxw', $panel ),
					$this->size( __( 'Padding Top', 'ada-accessibility' ), 'panel_pt', $panel ),
					$this->size( __( 'Padding Bottom', 'ada-accessibility' ), 'panel_pb', $panel ),
					$this->size( __( 'Corner Radius', 'ada-accessibility' ), 'panel_radius', $panel ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Shadow', 'ada-accessibility' ),
						'param_name'       => 'panel_shadow',
						'value'            => array(
							__( 'Default', 'ada-accessibility' ) => '',
							__( 'None', 'ada-accessibility' )    => 'none',
							__( 'Soft', 'ada-accessibility' )    => 'soft',
							__( 'Medium', 'ada-accessibility' )  => 'medium',
							__( 'Strong', 'ada-accessibility' )  => 'strong',
						),
						'std'              => '',
						'group'            => $panel,
						'edit_field_class' => 'vc_col-sm-6',
					),

					/* ---------------- Items ---------------- */
					$this->color( __( 'Text Colour', 'ada-accessibility' ), 'item_fg', $items ),
					$this->color( __( 'Text Colour (hover)', 'ada-accessibility' ), 'item_hover_fg', $items ),
					$this->size( __( 'Font Size', 'ada-accessibility' ), 'item_font', $items ),
					$this->size( __( 'Padding (vertical)', 'ada-accessibility' ), 'item_py', $items ),
					$this->size( __( 'Padding (horizontal)', 'ada-accessibility' ), 'item_px', $items ),
					$this->size( __( 'Corner Radius', 'ada-accessibility' ), 'item_radius', $items ),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Text Transform', 'ada-accessibility' ),
						'param_name'       => 'item_transform',
						'value'            => array(
							__( 'Default', 'ada-accessibility' )    => '',
							__( 'None', 'ada-accessibility' )       => 'none',
							__( 'Uppercase', 'ada-accessibility' )  => 'uppercase',
							__( 'Capitalize', 'ada-accessibility' ) => 'capitalize',
							__( 'Lowercase', 'ada-accessibility' )  => 'lowercase',
						),
						'std'              => '',
						'group'            => $items,
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Alignment', 'ada-accessibility' ),
						'param_name'       => 'item_justify',
						'value'            => array(
							__( 'Default', 'ada-accessibility' )       => '',
							__( 'Left', 'ada-accessibility' )          => 'flex-start',
							__( 'Center', 'ada-accessibility' )        => 'center',
							__( 'Right', 'ada-accessibility' )         => 'flex-end',
							__( 'Space Between', 'ada-accessibility' ) => 'space-between',
						),
						'std'              => '',
						'group'            => $items,
						'edit_field_class' => 'vc_col-sm-6',
					),
					$this->color( __( 'Divider Colour', 'ada-accessibility' ), 'rule_color', $items ),
					$this->color( __( 'Divider Colour (hover)', 'ada-accessibility' ), 'rule_hover_color', $items ),
					$this->size( __( 'Divider Width', 'ada-accessibility' ), 'rule_w', $items ),

					/* ---------------- Icons ---------------- */
					$this->color( __( 'Icon Colour', 'ada-accessibility' ), 'icon_color', $icons ),
					$this->color( __( 'Icon Colour (hover)', 'ada-accessibility' ), 'icon_hover_color', $icons ),
					$this->size( __( 'Icon Size', 'ada-accessibility' ), 'icon_size', $icons ),
					$this->size( __( 'Gap to Label', 'ada-accessibility' ), 'icon_gap', $icons ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Stroke Width', 'ada-accessibility' ),
						'param_name'       => 'icon_stroke',
						'group'            => $icons,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'A number, typically between 1 and 3.', 'ada-accessibility' ),
					),
					array(
						'type'             => 'dropdown',
						'heading'          => __( 'Show Toggle Dot', 'ada-accessibility' ),
						'param_name'       => 'dot_display',
						'value'            => array(
							__( 'Default', 'ada-accessibility' ) => '',
							__( 'Show', 'ada-accessibility' )    => 'block',
							__( 'Hide', 'ada-accessibility' )    => 'none',
						),
						'std'              => '',
						'group'            => $icons,
						'edit_field_class' => 'vc_col-sm-6',
					),
					$this->color( __( 'Toggle Dot Colour', 'ada-accessibility' ), 'dot_color', $icons ),
					$this->size( __( 'Toggle Dot Size', 'ada-accessibility' ), 'dot_size', $icons ),

					/* ---------------- Placement ---------------- */
					$this->size( __( 'Offset X', 'ada-accessibility' ), 'offset_x', $place ),
					$this->size( __( 'Offset Y', 'ada-accessibility' ), 'offset_y', $place ),
					$this->size( __( 'Offset Y when scrolled', 'ada-accessibility' ), 'offset_y_scrolled', $place ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Open Speed (seconds)', 'ada-accessibility' ),
						'param_name'       => 'speed',
						'group'            => $place,
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Hover Speed (seconds)', 'ada-accessibility' ),
						'param_name'       => 'speed_hover',
						'group'            => $place,
						'edit_field_class' => 'vc_col-sm-6',
					),
					$this->color( __( 'Focus Ring Colour', 'ada-accessibility' ), 'focus_color', $place ),
					array(
						'type'             => 'textfield',
						'heading'          => __( 'Z-index', 'ada-accessibility' ),
						'param_name'       => 'z_index',
						'group'            => $place,
						'edit_field_class' => 'vc_col-sm-6',
						'description'      => __( 'Raise this if the toolbar sits behind something.', 'ada-accessibility' ),
					),
				),
			)
		);
	}
}
