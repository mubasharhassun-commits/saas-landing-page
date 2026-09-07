<?php
/**
 * Elementor widget.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * ADA Accessibility widget.
 */
class ADAA_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ada_accessibility';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'ADA Accessibility', 'ada-accessibility' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-accessibility';
	}

	/**
	 * Panel categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'ada-accessibility', 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'accessibility', 'a11y', 'contrast', 'text size', 'toolbar', 'wcag' );
	}

	/**
	 * Styles this widget depends on.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'adaa' );
	}

	/**
	 * Scripts this widget depends on.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'adaa' );
	}

	/**
	 * This widget is a fixed overlay, so Elementor should not try to reuse
	 * a cached render across pages.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/**
	 * Build the controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$defaults = ADAA_Settings::defaults();

		/* ---------- Content ---------- */

		$this->start_controls_section(
			'section_buttons',
			array(
				'label' => __( 'Buttons', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'icon_launcher',
			array(
				'label'       => __( 'Floating button icon', 'ada-accessibility' ),
				'description' => __( 'Leave empty to keep the built-in accessibility icon.', 'ada-accessibility' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			)
		);

		$this->add_control(
			'icon_close',
			array(
				'label'       => __( 'Close icon', 'ada-accessibility' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
			)
		);

		$this->add_control(
			'label_close',
			array(
				'label'   => __( 'Close label', 'ada-accessibility' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $defaults['label_close'],
			)
		);

		$rows = array(
			'skip'     => __( 'Skip to content', 'ada-accessibility' ),
			'contrast' => __( 'High contrast', 'ada-accessibility' ),
			'text'     => __( 'Increase text size', 'ada-accessibility' ),
			'reset'    => __( 'Clear all', 'ada-accessibility' ),
		);

		foreach ( $rows as $slug => $title ) {
			$this->add_control(
				'show_' . $slug,
				array(
					'label'        => $title,
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'ada-accessibility' ),
					'label_off'    => __( 'Hide', 'ada-accessibility' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'separator'    => 'before',
				)
			);

			$this->add_control(
				'label_' . $slug,
				array(
					'label'     => __( 'Label', 'ada-accessibility' ),
					'type'      => Controls_Manager::TEXT,
					'default'   => $defaults[ 'label_' . $slug ],
					'condition' => array( 'show_' . $slug => 'yes' ),
				)
			);

			$this->add_control(
				'icon_' . $slug,
				array(
					'label'       => __( 'Icon', 'ada-accessibility' ),
					'type'        => Controls_Manager::ICONS,
					'skin'        => 'inline',
					'label_block' => false,
					'condition'   => array( 'show_' . $slug => 'yes' ),
				)
			);
		}

		$this->end_controls_section();

		/* ---------- Layout ---------- */

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'panel_side',
			array(
				'label'   => __( 'Panel opens from', 'ada-accessibility' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['panel_side'],
				'options' => array(
					'left'  => __( 'Left', 'ada-accessibility' ),
					'right' => __( 'Right', 'ada-accessibility' ),
				),
			)
		);

		$this->add_control(
			'button_position',
			array(
				'label'   => __( 'Button position', 'ada-accessibility' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['button_position'],
				'options' => array(
					'bottom-right' => __( 'Bottom right', 'ada-accessibility' ),
					'bottom-left'  => __( 'Bottom left', 'ada-accessibility' ),
					'middle-right' => __( 'Middle right', 'ada-accessibility' ),
					'middle-left'  => __( 'Middle left', 'ada-accessibility' ),
				),
			)
		);

		$this->add_control(
			'skip_target',
			array(
				'label'       => __( 'Skip to content target', 'ada-accessibility' ),
				'description' => __( 'CSS selector to jump to, e.g. #main. Leave empty to return to the top of the page.', 'ada-accessibility' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'condition'   => array( 'show_skip' => 'yes' ),
			)
		);

		$this->add_control(
			'skip_offset',
			array(
				'label'       => __( 'Scroll offset (px)', 'ada-accessibility' ),
				'description' => __( 'Pixels to leave above the target. Only used when a target is set above.', 'ada-accessibility' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 500,
				'default'     => 0,
				'condition'   => array( 'show_skip' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: panel ---------- */

		$this->start_controls_section(
			'section_style_panel',
			array(
				'label' => __( 'Panel', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'panel_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .adaa .adaa__panel',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'panel_border',
				'selector'  => '{{WRAPPER}} .adaa .adaa__panel',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => __( 'Width', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw' ),
				'range'      => array(
					'px' => array( 'min' => 180, 'max' => 600 ),
					'vw' => array( 'min' => 20, 'max' => 100 ),
				),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-panel-w: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_maxw',
			array(
				'label'      => __( 'Max width', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'vw', 'px' ),
				'range'      => array(
					'vw' => array( 'min' => 30, 'max' => 100 ),
					'px' => array( 'min' => 200, 'max' => 800 ),
				),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-panel-maxw: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_pt',
			array(
				'label'      => __( 'Top padding', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-panel-pt: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_pb',
			array(
				'label'      => __( 'Bottom padding', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-panel-pb: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_radius',
			array(
				'label'      => __( 'Border radius', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-panel-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'panel_shadow',
				'selector'  => '{{WRAPPER}} .adaa .adaa__panel',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		/* ---------- Style: divider ---------- */

		$this->start_controls_section(
			'section_style_divider',
			array(
				'label' => __( 'Divider', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'divider_color',
			array(
				'label'     => __( 'Color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-rule: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'divider_width',
			array(
				'label'      => __( 'Thickness', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-rule-w: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: menu items ---------- */

		$this->start_controls_section(
			'section_style_items',
			array(
				'label' => __( 'Menu Items', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .adaa .adaa__panel .adaa__item > span',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'item_text_shadow',
				'selector' => '{{WRAPPER}} .adaa .adaa__panel .adaa__item > span',
			)
		);

		$this->add_control(
			'item_transform',
			array(
				'label'     => __( 'Text transform', 'ada-accessibility' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'       => __( 'Default', 'ada-accessibility' ),
					'uppercase'  => __( 'Uppercase', 'ada-accessibility' ),
					'lowercase'  => __( 'Lowercase', 'ada-accessibility' ),
					'capitalize' => __( 'Capitalize', 'ada-accessibility' ),
				),
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-item-transform: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'item_align',
			array(
				'label'     => __( 'Alignment', 'ada-accessibility' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'ada-accessibility' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'ada-accessibility' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'ada-accessibility' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-item-justify: {{VALUE}};' ),
			)
		);

		$this->start_controls_tabs( 'item_state_tabs' );

		$this->start_controls_tab( 'item_tab_normal', array( 'label' => __( 'Normal', 'ada-accessibility' ) ) );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .adaa .adaa__panel .adaa__item',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .adaa .adaa__panel .adaa__item',
			)
		);

		$this->add_control(
			'color_text',
			array(
				'label'     => __( 'Text color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-fg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-icon-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_tab_hover', array( 'label' => __( 'Hover', 'ada-accessibility' ) ) );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'item_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .adaa .adaa__panel .adaa__item:hover, {{WRAPPER}} .adaa .adaa__panel .adaa__item:focus-visible',
			)
		);

		$this->add_control(
			'item_border_color_hover',
			array(
				'label'     => __( 'Border color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa .adaa__panel .adaa__item:hover, {{WRAPPER}} .adaa .adaa__panel .adaa__item:focus-visible' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'color_hover_text',
			array(
				'label'     => __( 'Text color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-hover-fg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => __( 'Icon color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-icon-hover-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'item_shadow',
				'selector'  => '{{WRAPPER}} .adaa .adaa__panel .adaa__item',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'item_padding_y',
			array(
				'label'      => __( 'Vertical padding', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-item-py: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'item_padding_x',
			array(
				'label'      => __( 'Horizontal padding', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-item-px: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'item_radius',
			array(
				'label'      => __( 'Border radius', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-item-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: icons ---------- */

		$this->start_controls_section(
			'section_style_icons',
			array(
				'label' => __( 'Icons', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => __( 'Size', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-icon-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'      => __( 'Spacing', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-icon-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'icon_stroke',
			array(
				'label'      => __( 'Line weight', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => .5, 'max' => 4, 'step' => .1 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-icon-stroke: {{SIZE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: active indicator ---------- */

		$this->start_controls_section(
			'section_style_dot',
			array(
				'label' => __( 'Active Indicator', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'dot_show',
			array(
				'label'       => __( 'Show indicator', 'ada-accessibility' ),
				'description' => __( 'The dot that marks which options are currently switched on.', 'ada-accessibility' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'block',
				'options'     => array(
					'block' => __( 'Show', 'ada-accessibility' ),
					'none'  => __( 'Hide', 'ada-accessibility' ),
				),
				'selectors'   => array( '{{WRAPPER}} .adaa' => '--adaa-dot-display: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => __( 'Color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'dot_show' => 'block' ),
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-dot-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_size',
			array(
				'label'      => __( 'Size', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 24 ) ),
				'condition'  => array( 'dot_show' => 'block' ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-dot-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: floating button ---------- */

		$this->start_controls_section(
			'section_style_launcher',
			array(
				'label' => __( 'Floating Button', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'launcher_state_tabs' );

		$this->start_controls_tab( 'launcher_tab_normal', array( 'label' => __( 'Normal', 'ada-accessibility' ) ) );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'launcher_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .adaa .adaa__launcher',
			)
		);

		$this->add_control(
			'launcher_fg',
			array(
				'label'     => __( 'Icon color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-fg: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'launcher_tab_hover', array( 'label' => __( 'Hover', 'ada-accessibility' ) ) );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'launcher_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .adaa .adaa__launcher:hover, {{WRAPPER}} .adaa .adaa__launcher:focus-visible',
			)
		);

		$this->add_control(
			'launcher_border_color_hover',
			array(
				'label'     => __( 'Border color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa .adaa__launcher:hover, {{WRAPPER}} .adaa .adaa__launcher:focus-visible' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'launcher_hover_fg',
			array(
				'label'     => __( 'Icon color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-hover-fg: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_size',
			array(
				'label'      => __( 'Size', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 110 ) ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'launcher_icon_size',
			array(
				'label'      => __( 'Icon size', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-icon: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'launcher_border',
				'selector'  => '{{WRAPPER}} .adaa .adaa__launcher',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'launcher_ring',
			array(
				'label'      => __( 'Ring thickness', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 12 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-ring: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'launcher_ring_color',
			array(
				'label'     => __( 'Ring color', 'ada-accessibility' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-ring-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'launcher_radius',
			array(
				'label'      => __( 'Border radius', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 60 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-launcher-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'offset_x',
			array(
				'label'      => __( 'Distance from side', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 160 ) ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-offset-x: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'offset_y',
			array(
				'label'      => __( 'Distance from bottom', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'condition'  => array( 'button_position' => array( 'bottom-right', 'bottom-left' ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-offset-y: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'offset_y_scrolled',
			array(
				'label'       => __( 'Distance after scrolling', 'ada-accessibility' ),
				'description' => __( 'Lets the button clear a sticky footer or chat bubble once the visitor scrolls past 300px.', 'ada-accessibility' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 300 ) ),
				'condition'   => array( 'button_position' => array( 'bottom-right', 'bottom-left' ) ),
				'selectors'   => array( '{{WRAPPER}} .adaa' => '--adaa-offset-y-scrolled: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'launcher_shadow',
				'selector'  => '{{WRAPPER}} .adaa .adaa__launcher',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		/* ---------- Style: motion & focus ---------- */

		$this->start_controls_section(
			'section_style_misc',
			array(
				'label' => __( 'Motion & Focus', 'ada-accessibility' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'speed_panel',
			array(
				'label'      => __( 'Panel slide speed (s)', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1.5, 'step' => .05 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-speed: {{SIZE}}s;' ),
			)
		);

		$this->add_control(
			'speed_hover',
			array(
				'label'      => __( 'Hover fade speed (s)', 'ada-accessibility' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => .05 ) ),
				'selectors'  => array( '{{WRAPPER}} .adaa' => '--adaa-speed-hover: {{SIZE}}s;' ),
			)
		);

		$this->add_control(
			'focus_color',
			array(
				'label'       => __( 'Keyboard focus outline', 'ada-accessibility' ),
				'description' => __( 'Shown when someone tabs to a button. Keep it visible against the panel background.', 'ada-accessibility' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .adaa' => '--adaa-focus: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'z_index',
			array(
				'label'       => __( 'Z-index', 'ada-accessibility' ),
				'description' => __( 'Raise this if another fixed element sits on top of the button.', 'ada-accessibility' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 999999,
				'selectors'   => array( '{{WRAPPER}} .adaa' => '--adaa-z: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Front-end output.
	 *
	 * @return void
	 */
	protected function render() {
		if ( ADAA_Renderer::has_printed() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'A toolbar is already showing on this page. Turn off “Show on every page” in Settings → ADA Accessibility, or remove the duplicate widget.', 'ada-accessibility' ) . '</p>';
			}
			return;
		}

		$s = $this->get_settings_for_display();

		$args = array(
			'panel_side'      => $s['panel_side'],
			'button_position' => $s['button_position'],
			'label_close'     => $s['label_close'],
			'show_skip'       => 'yes' === $s['show_skip'] ? 1 : 0,
			'show_contrast'   => 'yes' === $s['show_contrast'] ? 1 : 0,
			'show_text'       => 'yes' === $s['show_text'] ? 1 : 0,
			'show_reset'      => 'yes' === $s['show_reset'] ? 1 : 0,
			'label_skip'      => $s['label_skip'],
			'label_contrast'  => $s['label_contrast'],
			'label_text'      => $s['label_text'],
			'label_reset'     => $s['label_reset'],
			'skip_target'     => isset( $s['skip_target'] ) ? $s['skip_target'] : '',
			'skip_offset'     => isset( $s['skip_offset'] ) ? $s['skip_offset'] : 0,
			'z_index'         => isset( $s['z_index'] ) && $s['z_index'] ? $s['z_index'] : 2147483646,
			'auto_inject'     => 0,
		);

		// Run everything through the same sanitiser the settings screen uses.
		$args = ADAA_Settings::sanitize( $args );

		// Any icon the user picked replaces the built-in one.
		$icons = array();

		foreach ( array( 'launcher', 'close', 'skip', 'contrast', 'text', 'reset' ) as $slot ) {
			$chosen = isset( $s[ 'icon_' . $slot ] ) ? $s[ 'icon_' . $slot ] : array();

			if ( empty( $chosen['value'] ) ) {
				continue;
			}

			$html = Icons_Manager::try_get_icon_html( $chosen, array( 'aria-hidden' => 'true' ) );

			if ( $html ) {
				$icons[ $slot ] = $html;
			}
		}

		$args['icons'] = $icons;

		// Elementor emits the colour custom properties itself via the style tab.
		$args['inline_colors'] = false;

		echo ADAA_Renderer::render( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the renderer.
	}
}
