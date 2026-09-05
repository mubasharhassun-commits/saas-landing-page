<?php
/**
 * WL Cards — native Elementor widget to display Vessels / Cases as a
 * responsive grid or a continuous auto-scrolling slider, with hover
 * black overlay, slide-up title, and optional Read More button.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Cards_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wl_cards';
	}

	public function get_title() {
		return __( 'WL Cards (Vessels / Cases)', 'waterslaw' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'waterslaw' );
	}

	public function get_keywords() {
		return array( 'vessels', 'cases', 'cards', 'grid', 'slider', 'carousel', 'waters' );
	}

	/* ---------------------------------------------------------
	 * CONTROLS
	 * ------------------------------------------------------- */
	protected function register_controls() {

		/* ---------- Query ---------- */
		$this->start_controls_section( 'sec_query', array(
			'label' => __( 'Query', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'post_type', array(
			'label'       => __( 'Source', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'default'     => 'vessels',
			'options'     => array(
				'vessels' => __( 'Vessels (post type)', 'waterslaw' ),
				'cases'   => __( 'Cases (post type)', 'waterslaw' ),
				'manual'  => __( 'Custom Cards (build them here)', 'waterslaw' ),
			),
			'description' => __( 'Choose Custom Cards to add your own images, text and page links below — no posts needed.', 'waterslaw' ),
		) );

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( 'item_image', array(
			'label'   => __( 'Image', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ),
		) );

		$repeater->add_control( 'item_text', array(
			'label'       => __( 'Text on Image', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => '',
			'description' => __( 'Leave blank for an image-only card.', 'waterslaw' ),
		) );

		$repeater->add_control( 'item_link', array(
			'label'       => __( 'Link', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::URL,
			'label_block' => true,
			'placeholder' => 'https://waterslaw.com/your-page/',
			'default'     => array( 'url' => '' ),
			'description' => __( 'The page this card opens. Leave blank to make this card non-clickable.', 'waterslaw' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Cards', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ item_text || \'Card\' }}}',
			'default'     => array(
				array( 'item_text' => '' ),
				array( 'item_text' => '' ),
				array( 'item_text' => '' ),
			),
			'condition'   => array( 'post_type' => 'manual' ),
		) );

		$this->add_control( 'count', array(
			'label'     => __( 'Number of Items (-1 = all)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => -1,
			'condition' => array( 'post_type!' => 'manual' ),
		) );

		$this->add_control( 'orderby', array(
			'label'   => __( 'Order By', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'menu_order date',
			'options' => array(
				'menu_order date' => __( 'Menu Order', 'waterslaw' ),
				'date'            => __( 'Date', 'waterslaw' ),
				'title'           => __( 'Title', 'waterslaw' ),
				'rand'            => __( 'Random', 'waterslaw' ),
			),
			'condition' => array( 'post_type!' => 'manual' ),
		) );

		$this->add_control( 'order', array(
			'label'     => __( 'Order', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::SELECT,
			'default'   => 'ASC',
			'options'   => array( 'ASC' => 'ASC', 'DESC' => 'DESC' ),
			'condition' => array( 'post_type!' => 'manual' ),
		) );

		$this->end_controls_section();

		/* ---------- Layout ---------- */
		$this->start_controls_section( 'sec_layout', array(
			'label' => __( 'Layout', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'layout', array(
			'label'       => __( 'Layout', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'default'     => 'grid',
			'options'     => array(
				'grid'     => __( 'Grid', 'waterslaw' ),
				'carousel' => __( 'Continuous Slider', 'waterslaw' ),
			),
			'description' => __( 'Grid = static rows. Continuous Slider = seamless auto-scroll (great for 10+ items).', 'waterslaw' ),
		) );

		$this->add_control( 'link_cards', array(
			'label'        => __( 'Make Cards Clickable', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'no',
			'separator'    => 'before',
			'description'  => __( 'Turn OFF so no card links anywhere. Custom Cards use the Link set on each card.', 'waterslaw' ),
		) );

		/* Responsive columns: desktop 4 / laptop / tablet 2 / mobile 1 */
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns — Desktop', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 12,
			'default' => 4,
		) );

		$this->add_control( 'columns_laptop', array(
			'label'       => __( 'Columns — Laptop', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'min'         => 1,
			'max'         => 12,
			'default'     => '',
			'description' => __( 'Applies at 1300px and below. Leave empty to use the Desktop count.', 'waterslaw' ),
		) );

		$this->add_control( 'columns_tablet', array(
			'label'   => __( 'Columns — Tablet', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 12,
			'default' => 2,
		) );

		$this->add_control( 'columns_mobile', array(
			'label'   => __( 'Columns — Mobile', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 12,
			'default' => 1,
		) );

		$this->add_control( 'gap', array(
			'label'     => __( 'Gap (px)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'default'   => array( 'size' => 8 ),
			'selectors' => array( '{{WRAPPER}} .wl-cards' => '--wl-gap: {{SIZE}}px;' ),
		) );

		$this->add_control( 'ratio', array(
			'label'       => __( 'Card Ratio (w/h)', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '3/4',
			'description' => __( 'e.g. 3/4 portrait, 1/1 square, 4/3 landscape', 'waterslaw' ),
			'selectors'   => array( '{{WRAPPER}} .wl-card-img' => 'aspect-ratio: {{VALUE}};' ),
		) );

		$this->add_control( 'radius', array(
			'label'     => __( 'Corner Radius (px)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'default'   => array( 'size' => 0 ),
			'selectors' => array( '{{WRAPPER}} .wl-card' => '--wl-radius: {{SIZE}}px;' ),
		) );

		$this->add_control( 'custom_size', array(
			'label'        => __( 'Use Custom Card Size', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => '',
			'separator'    => 'before',
			'description'  => __( 'Set exact card width & height (px), overriding columns/ratio. Great for matching a PSD (e.g. 360×520).', 'waterslaw' ),
		) );

		$this->add_responsive_control( 'card_width', array(
			'label'      => __( 'Card Width (px)', 'waterslaw' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 100, 'max' => 700 ) ),
			'default'    => array( 'unit' => 'px', 'size' => 360 ),
			'condition'  => array( 'custom_size' => 'yes' ),
			'selectors'  => array(
				// Exact column count (desktop/tablet/mobile) at the custom width, centered — no empty slots.
				'{{WRAPPER}} .wl-grid'                   => 'grid-template-columns: repeat(var(--active-cols,4), minmax(0, {{SIZE}}px)); justify-content: center;',
				'{{WRAPPER}} .wl-carousel .wl-card, {{WRAPPER}} .wl-marquee .wl-card' => 'flex: 0 0 {{SIZE}}px;',
			),
		) );

		$this->add_responsive_control( 'card_height', array(
			'label'      => __( 'Card Height (px)', 'waterslaw' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 100, 'max' => 900 ) ),
			'default'    => array( 'unit' => 'px', 'size' => 520 ),
			'condition'  => array( 'custom_size' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .wl-card-img' => 'height: {{SIZE}}px; aspect-ratio: auto;',
			),
		) );

		$this->end_controls_section();

		/* ---------- Overlay & Title ---------- */
		$this->start_controls_section( 'sec_overlay', array(
			'label' => __( 'Overlay & Title', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => __( 'Overlay (normal, bottom)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(10,25,40,0.85)',
			'selectors' => array( '{{WRAPPER}} .wl-cards' => '--wl-overlay: {{VALUE}};' ),
		) );

		$this->add_control( 'hover_overlay_color', array(
			'label'     => __( 'Overlay (on hover — black)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(0,0,0,0.55)',
			'selectors' => array( '{{WRAPPER}} .wl-cards' => '--wl-hover-overlay: {{VALUE}};' ),
		) );

		$this->add_control( 'ov_img_heading', array(
			'label'       => __( 'Overlay Image — Normal', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::HEADING,
			'separator'   => 'before',
			'description' => __( 'Optional image layered over the photo (color tint stays underneath).', 'waterslaw' ),
		) );

		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), array(
			'name'     => 'ov_normal_img',
			'types'    => array( 'classic', 'gradient' ),
			'selector' => '{{WRAPPER}} .wl-ov-normal-img',
			// Group_Control_Background provides Image, Position, Attachment, Repeat, Size, etc.
		) );

		$this->add_control( 'ov_normal_img_opacity', array(
			'label'      => __( 'Image Opacity (normal)', 'waterslaw' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( '%' ),
			'range'      => array( '%' => array( 'min' => 0, 'max' => 100, 'step' => 1 ) ),
			'default'    => array( 'unit' => '%', 'size' => 100 ),
			'selectors'  => array( '{{WRAPPER}} .wl-cards' => '--wl-ov-n-op: calc({{SIZE}}/100);' ),
		) );

		$this->add_control( 'ov_img_hover_heading', array(
			'label'     => __( 'Overlay Image — On Hover', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( \Elementor\Group_Control_Background::get_type(), array(
			'name'     => 'ov_hover_img',
			'types'    => array( 'classic', 'gradient' ),
			'selector' => '{{WRAPPER}} .wl-ov-hover-img',
		) );

		$this->add_control( 'ov_hover_img_opacity', array(
			'label'      => __( 'Image Opacity (on hover)', 'waterslaw' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( '%' ),
			'range'      => array( '%' => array( 'min' => 0, 'max' => 100, 'step' => 1 ) ),
			'default'    => array( 'unit' => '%', 'size' => 100 ),
			'selectors'  => array( '{{WRAPPER}} .wl-cards' => '--wl-ov-h-op: calc({{SIZE}}/100);' ),
		) );

		$this->add_control( 'title_color', array(
			'label'     => __( 'Title Color', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .wl-cards' => '--wl-title: {{VALUE}};' ),
		) );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name'     => 'title_typo',
			'label'    => __( 'Title Typography', 'waterslaw' ),
			'selector' => '{{WRAPPER}} .wl-card-title',
		) );

		$this->end_controls_section();

		/* ---------- Hover Button ---------- */
		$this->start_controls_section( 'sec_button', array(
			'label' => __( 'Hover Button', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'show_button', array(
			'label'        => __( 'Show Read More Button', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => '',
			'description'  => __( 'Turn ON for Cases, OFF for Vessels.', 'waterslaw' ),
		) );

		$this->add_control( 'button_text', array(
			'label'     => __( 'Button Text', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => __( 'READ MORE', 'waterslaw' ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'hide_title_hover', array(
			'label'        => __( 'Hide Title on Hover', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => __( 'On hover, hide the title so only the button shows.', 'waterslaw' ),
			'condition'    => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'btn_heading', array(
			'label'     => __( 'Button Style', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name'      => 'btn_typo',
			'label'     => __( 'Typography', 'waterslaw' ),
			'selector'  => '{{WRAPPER}} .wl-card-btn',
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'btn_color', array(
			'label'     => __( 'Text Color', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .wl-card-btn' => 'color: {{VALUE}};' ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'btn_border_color', array(
			'label'     => __( 'Border Color', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .wl-card-btn' => 'border-color: {{VALUE}};' ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'btn_bg', array(
			'label'     => __( 'Background', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(0,0,0,0.15)',
			'selectors' => array( '{{WRAPPER}} .wl-card-btn' => 'background: {{VALUE}};' ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_responsive_control( 'btn_padding', array(
			'label'      => __( 'Padding', 'waterslaw' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array( '{{WRAPPER}} .wl-card-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			'condition'  => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'btn_radius', array(
			'label'     => __( 'Border Radius (px)', 'waterslaw' ),
			'type'      => \Elementor\Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'default'   => array( 'size' => 0 ),
			'selectors' => array( '{{WRAPPER}} .wl-card-btn' => 'border-radius: {{SIZE}}px;' ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->end_controls_section();

		/* ---------- Slider Options ---------- */
		$this->start_controls_section( 'sec_slider', array(
			'label'     => __( 'Slider Options', 'waterslaw' ),
			'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
			'condition' => array( 'layout' => 'carousel' ),
		) );

		$this->add_control( 'duration', array(
			'label'       => __( 'Scroll Duration (seconds)', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 30,
			'min'         => 5,
			'max'         => 120,
			'description' => __( 'Higher = slower. Increase for more items.', 'waterslaw' ),
		) );

		$this->add_control( 'pause_hover', array(
			'label'        => __( 'Pause on Hover', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------
	 * RENDER
	 * ------------------------------------------------------- */

	public function get_style_depends() {
		return array( WL_Assets::STYLE );
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		// Delegates to the shared renderer so an Elementor page and a WPBakery
		// page produce byte-identical markup.
		$html = WL_Render::cards(
			array(
				'source'           => $s['post_type'],
				'count'            => $s['count'],
				'orderby'          => $s['orderby'],
				'order'            => $s['order'],
				'layout'           => $s['layout'],
				'columns'          => $s['columns'],
				'columns_laptop'   => $s['columns_laptop'],
				'columns_tablet'   => $s['columns_tablet'],
				'columns_mobile'   => $s['columns_mobile'],
				'link_cards'       => $s['link_cards'],
				'show_button'      => $s['show_button'],
				'button_text'      => $s['button_text'],
				'hide_title_hover' => $s['hide_title_hover'],
				'custom_size'      => $s['custom_size'],
				'pause_hover'      => $s['pause_hover'],
				'items'            => isset( $s['items'] ) ? $s['items'] : '',
			)
		);

		if ( '' === $html ) {
			echo '<p>' . esc_html__( 'No items found. Add some first.', 'waterslaw' ) . '</p>';
			return;
		}

		// Escaped inside WL_Render.
		echo $html; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
	}
}
