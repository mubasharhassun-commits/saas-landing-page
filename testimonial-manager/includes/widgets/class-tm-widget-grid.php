<?php
/**
 * "Testimonials Grid" Elementor widget.
 *
 * Style controls write CSS custom properties onto the grid element. The modal
 * lives at page level and cannot be scoped by {{WRAPPER}}, so the popup controls
 * write --tm-modal-* properties which the script copies onto the modal when a
 * card in this grid opens it. That keeps per-widget popup styling working with
 * a single shared modal.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Widget_Grid extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tm_testimonials_grid';
	}

	public function get_title() {
		return __( 'Testimonials Grid', 'testimonial-manager' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return array( 'testimonial-manager' );
	}

	public function get_keywords() {
		return array( 'testimonial', 'review', 'rating', 'stars', 'client', 'quote' );
	}

	public function get_style_depends() {
		return array( TM_Assets::STYLE );
	}

	public function get_script_depends() {
		return array( TM_Assets::SCRIPT );
	}

	/**
	 * Category terms for the filter dropdown.
	 */
	private function category_options() {
		$options = array( '' => __( 'All categories', 'testimonial-manager' ) );

		$terms = get_terms(
			array(
				'taxonomy'   => TM_Post_Type::TAXONOMY,
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}

	protected function register_controls() {
		$this->query_section();
		$this->layout_section();
		$this->display_section();
		$this->style_card_section();
		$this->style_text_section();
		$this->style_button_section();
		$this->style_popup_section();
	}

	/* =============================================================
	 * CONTENT
	 * ============================================================= */

	private function query_section() {
		$this->start_controls_section(
			'section_query',
			array(
				'label' => __( 'Query', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'count',
			array(
				'label'       => __( 'Number of Testimonials', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => -1,
				'default'     => 6,
				'description' => __( 'Use -1 to show all.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => array(
					'menu_order' => __( 'Display Order', 'testimonial-manager' ),
					'date'       => __( 'Date Published', 'testimonial-manager' ),
					'title'      => __( 'Client Name', 'testimonial-manager' ),
					'rating'     => __( 'Rating', 'testimonial-manager' ),
					'rand'       => __( 'Random', 'testimonial-manager' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => __( 'Order', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'ASC',
				'options'   => array(
					'ASC'  => __( 'Ascending', 'testimonial-manager' ),
					'DESC' => __( 'Descending', 'testimonial-manager' ),
				),
				'condition' => array( 'orderby!' => 'rand' ),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'   => __( 'Category', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->category_options(),
			)
		);

		$this->add_control(
			'featured',
			array(
				'label'        => __( 'Featured Only', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();
	}

	private function layout_section() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns - Desktop', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'default' => 3,
			)
		);

		$this->add_control(
			'columns_laptop',
			array(
				'label'       => __( 'Columns - Laptop', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 6,
				'default'     => '',
				'description' => __( 'Applies at 1300px and below. Leave empty to use the Desktop count.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'columns_tablet',
			array(
				'label'   => __( 'Columns - Tablet', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'default' => 2,
			)
		);

		$this->add_control(
			'columns_mobile',
			array(
				'label'   => __( 'Columns - Mobile', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'default' => 1,
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Card Spacing', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-gap: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	private function display_section() {
		$this->start_controls_section(
			'section_display',
			array(
				'label' => __( 'Display', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		foreach ( array(
			'show_rating'   => __( 'Show Rating', 'testimonial-manager' ),
			'show_image'    => __( 'Show Client Image', 'testimonial-manager' ),
			'show_position' => __( 'Show Position', 'testimonial-manager' ),
			'show_company'  => __( 'Show Company', 'testimonial-manager' ),
			'show_button'   => __( 'Show Button', 'testimonial-manager' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);
		}

		$this->add_control(
			'button_text',
			array(
				'label'     => __( 'Button Text', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'READ FULL REVIEW', 'testimonial-manager' ),
				'condition' => array( 'show_button' => 'yes' ),
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label'       => __( 'Default Client Image', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => array( 'url' => '' ),
				'description' => __( 'Used for any testimonial with no Featured image of its own, in place of the initial letter. A firm logo works well here.', 'testimonial-manager' ),
				'condition'   => array( 'show_image' => 'yes' ),
			)
		);

		$this->add_control(
			'excerpt_words',
			array(
				'label'       => __( 'Short Text Length (words)', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 5,
				'max'         => 200,
				'default'     => 32,
				'description' => __( 'Only applies when a testimonial has no Excerpt of its own. Words are never cut in half.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'       => __( 'Client Name HTML Tag', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'h3',
				'options'     => array(
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'h6'  => 'H6',
					'div' => 'div',
					'p'   => 'p',
				),
				'description' => __( 'Pick the level that fits this page heading outline.', 'testimonial-manager' ),
			)
		);

		$this->end_controls_section();
	}

	/* =============================================================
	 * STYLE
	 * ============================================================= */

	private function style_card_section() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Card', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-card-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_border_color',
			array(
				'label'     => __( 'Border Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a9c6c4',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-card-border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_border_width',
			array(
				'label'      => __( 'Border Width', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 8 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-card-border-width: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'Border Radius', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 2 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-card-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .tm-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 26,
					'right'    => 26,
					'bottom'   => 26,
					'left'     => 26,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .tm-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'avatar_size',
			array(
				'label'      => __( 'Client Image Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 44 ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-avatar-size: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	private function style_text_section() {
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => __( 'Typography', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'heading_rating',
			array(
				'label' => __( 'Rating', 'testimonial-manager' ),
				'type'  => \Elementor\Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-star-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_empty_color',
			array(
				'label'     => __( 'Empty Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#d8e3e2',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-star-empty-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_size',
			array(
				'label'      => __( 'Star Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 17 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-star-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'star_gap',
			array(
				'label'      => __( 'Space Between Stars', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 3 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-star-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'quote_icon',
			array(
				'label'        => __( 'Show Quote Icon', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'The large quote mark above the testimonial. Off also hides the one some themes add of their own.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'quote_mark_size',
			array(
				'label'      => __( 'Quote Icon Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 160 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 44 ),
				'condition'  => array( 'quote_icon' => 'yes' ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-quote-mark-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'quote_mark_color',
			array(
				'label'     => __( 'Quote Icon Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array( 'quote_icon' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-quote-mark-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'heading_quote',
			array(
				'label'     => __( 'Testimonial Text', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'quote_color',
			array(
				'label'     => __( 'Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#33383d',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-text-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'quote_typo',
				'selector' => '{{WRAPPER}} .tm-excerpt',
			)
		);

		$this->add_responsive_control(
			'quote_align',
			array(
				'label'     => __( 'Alignment', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'default'   => 'justify',
				'options'   => array(
					'left'    => array(
						'title' => __( 'Left', 'testimonial-manager' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'testimonial-manager' ),
						'icon'  => 'eicon-text-align-center',
					),
					'justify' => array(
						'title' => __( 'Justified', 'testimonial-manager' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'selectors' => array( '{{WRAPPER}} .tm-excerpt' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'heading_name',
			array(
				'label'     => __( 'Client Name', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'name_color',
			array(
				'label'     => __( 'Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1f2d3a',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-name-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'name_typo',
				'selector' => '{{WRAPPER}} .tm-name',
			)
		);

		$this->add_control(
			'heading_role',
			array(
				'label'     => __( 'Position / Company', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'role_color',
			array(
				'label'     => __( 'Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7b8792',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-role-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'role_typo',
				'selector' => '{{WRAPPER}} .tm-role',
			)
		);

		$this->end_controls_section();
	}

	private function style_button_section() {
		$this->start_controls_section(
			'section_style_button',
			array(
				'label'     => __( 'Button', 'testimonial-manager' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_button' => 'yes' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typo',
				'selector' => '{{WRAPPER}} .tm-btn',
			)
		);

		$this->start_controls_tabs( 'btn_tabs' );

		$this->start_controls_tab( 'btn_normal', array( 'label' => __( 'Normal', 'testimonial-manager' ) ) );

		$this->add_control(
			'btn_bg',
			array(
				'label'     => __( 'Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_color',
			array(
				'label'     => __( 'Text Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_border_color',
			array(
				'label'     => __( 'Border Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'btn_hover', array( 'label' => __( 'Hover', 'testimonial-manager' ) ) );

		$this->add_control(
			'btn_bg_h',
			array(
				'label'     => __( 'Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#658b88',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-bg-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_color_h',
			array(
				'label'     => __( 'Text Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-color-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'btn_border_color_h',
			array(
				'label'     => __( 'Border Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#658b88',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-border-hover: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'btn_border_width',
			array(
				'label'      => __( 'Border Width', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 6 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-border-width: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'btn_radius',
			array(
				'label'      => __( 'Border Radius', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 2 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-btn-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'btn_padding',
			array(
				'label'      => __( 'Padding', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'top'      => 14,
					'right'    => 18,
					'bottom'   => 14,
					'left'     => 18,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .tm-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	private function style_popup_section() {
		$this->start_controls_section(
			'section_style_popup',
			array(
				'label'     => __( 'Popup', 'testimonial-manager' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_button' => 'yes' ),
			)
		);

		$this->add_control(
			'popup_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => __( 'The popup is shared by every testimonial on the page. These settings are applied to it when a card from this widget opens it.', 'testimonial-manager' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'popup_max_width',
			array(
				'label'      => __( 'Max Width', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 320, 'max' => 1200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 840 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-max-width: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'popup_bg',
			array(
				'label'     => __( 'Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'popup_overlay',
			array(
				'label'     => __( 'Overlay Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(20,30,38,0.72)',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-overlay: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'popup_text_color',
			array(
				'label'     => __( 'Text Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#33383d',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-text-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'popup_font_size',
			array(
				'label'      => __( 'Text Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 28 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 16 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-font-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'popup_radius',
			array(
				'label'      => __( 'Border Radius', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 2 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'popup_padding',
			array(
				'label'      => __( 'Padding', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 90 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 38 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-padding: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'popup_close_position',
			array(
				'label'                => __( 'Close Button Position', 'testimonial-manager' ),
				'type'                 => \Elementor\Controls_Manager::SELECT,
				'default'              => 'top-right',
				'separator'            => 'before',
				'options'              => array(
					'top-right'  => __( 'Top Right (inside)', 'testimonial-manager' ),
					'top-center' => __( 'Top Center (on the edge)', 'testimonial-manager' ),
					'top-left'   => __( 'Top Left (inside)', 'testimonial-manager' ),
				),
				'selectors_dictionary' => array(
					'top-right'  => '--tm-close-top:12px;--tm-close-right:12px;--tm-close-left:auto;--tm-close-transform:none;',
					'top-center' => '--tm-close-top:0px;--tm-close-right:auto;--tm-close-left:50%;--tm-close-transform:translate(-50%,-50%);',
					'top-left'   => '--tm-close-top:12px;--tm-close-right:auto;--tm-close-left:12px;--tm-close-transform:none;',
				),
				'selectors'            => array( '{{WRAPPER}} .tm-grid' => '{{VALUE}}' ),
			)
		);

		$this->add_control(
			'popup_close_size',
			array(
				'label'      => __( 'Close Button Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 26, 'max' => 72 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 40 ),
				'selectors'  => array( '{{WRAPPER}} .tm-grid' => '--tm-close-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'popup_close_bg',
			array(
				'label'     => __( 'Close Button Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-close-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'popup_close_color',
			array(
				'label'     => __( 'Close Button Icon', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-grid' => '--tm-modal-close-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/* =============================================================
	 * RENDER
	 * ============================================================= */

	protected function render() {
		$s = $this->get_settings_for_display();

		$html = TM_Renderer::grid(
			array(
				'count'          => $s['count'],
				'orderby'        => $s['orderby'],
				'order'          => $s['order'],
				'category'       => $s['category'],
				'featured'       => $s['featured'],
				'columns'        => $s['columns'],
				'columns_laptop' => $s['columns_laptop'],
				'columns_tablet' => $s['columns_tablet'],
				'columns_mobile' => $s['columns_mobile'],
				'gap'            => isset( $s['gap']['size'] ) ? $s['gap']['size'] : 24,
				'excerpt_words'  => $s['excerpt_words'],
				'show_rating'    => $s['show_rating'],
				'show_image'     => $s['show_image'],
				'show_company'   => $s['show_company'],
				'show_position'  => $s['show_position'],
				'show_button'    => $s['show_button'],
				'quote_icon'     => isset( $s['quote_icon'] ) ? $s['quote_icon'] : '',
				'button_text'    => $s['button_text'],
				'fallback_image' => isset( $s['fallback_image']['url'] ) ? $s['fallback_image']['url'] : '',
				'title_tag'      => $s['title_tag'],
			)
		);

		if ( '' === $html ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'No testimonials found. Add some under Dashboard - Testimonials.', 'testimonial-manager' ) . '</p>';
			}
			return;
		}

		// Escaped inside TM_Renderer.
		echo $html; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
	}
}
