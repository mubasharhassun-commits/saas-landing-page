<?php
/**
 * "Testimonials Slider" Elementor widget.
 *
 * Built for a banner or hero area: one testimonial at a time on a translucent
 * panel, newest first by default.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Widget_Slider extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tm_testimonials_slider';
	}

	public function get_title() {
		return __( 'Testimonials Slider', 'testimonial-manager' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return array( 'testimonial-manager' );
	}

	public function get_keywords() {
		return array( 'testimonial', 'slider', 'carousel', 'review', 'banner', 'quote' );
	}

	public function get_style_depends() {
		return array( TM_Assets::STYLE );
	}

	public function get_script_depends() {
		return array( TM_Assets::SLIDER );
	}

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

		/* ---------------- Query ---------------- */
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
				'label'       => __( 'Number of Slides', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 20,
				'default'     => 5,
				'description' => __( 'How many testimonials to cycle through.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'       => __( 'Order By', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'date',
				'options'     => array(
					'date'       => __( 'Date Published', 'testimonial-manager' ),
					'menu_order' => __( 'Display Order', 'testimonial-manager' ),
					'title'      => __( 'Client Name', 'testimonial-manager' ),
					'rating'     => __( 'Rating', 'testimonial-manager' ),
					'rand'       => __( 'Random', 'testimonial-manager' ),
				),
				'description' => __( 'Date Published with Newest First puts the latest testimonial on the first slide.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => __( 'Order', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => __( 'Newest / Highest First', 'testimonial-manager' ),
					'ASC'  => __( 'Oldest / Lowest First', 'testimonial-manager' ),
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

		/* ---------------- Slider ---------------- */
		$this->start_controls_section(
			'section_slider',
			array(
				'label' => __( 'Slider', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'       => __( 'Autoplay Speed (ms)', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 30000,
				'step'        => 500,
				'default'     => 6000,
				'description' => __( 'Time each slide is shown. Use 0 to turn autoplay off.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'effect',
			array(
				'label'   => __( 'Transition', 'testimonial-manager' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => array(
					'fade'  => __( 'Fade', 'testimonial-manager' ),
					'slide' => __( 'Slide', 'testimonial-manager' ),
				),
			)
		);

		$this->add_control(
			'pause_hover',
			array(
				'label'        => __( 'Pause on Hover', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Show Arrows', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => __( 'Show Dots', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------------- Display ---------------- */
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
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => in_array( $key, array( 'show_rating', 'show_image' ), true ) ? 'yes' : '',
				)
			);
		}

		$this->add_control(
			'full_text',
			array(
				'label'        => __( 'Show Full Testimonial', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'description'  => __( 'Off trims each slide to the length below, which keeps the panel a consistent height.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'excerpt_words',
			array(
				'label'     => __( 'Text Length (words)', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 5,
				'max'       => 200,
				'default'   => 45,
				'condition' => array( 'full_text!' => 'yes' ),
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label'       => __( 'Default Client Image', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => array( 'url' => '' ),
				'description' => __( 'Used for any testimonial without a Client Image of its own.', 'testimonial-manager' ),
				'condition'   => array( 'show_image' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: panel ---------------- */
		$this->start_controls_section(
			'section_style_panel',
			array(
				'label' => __( 'Panel', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(18,26,32,0.72)',
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'panel_radius',
			array(
				'label'      => __( 'Border Radius', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'      => __( 'Padding', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 100 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 44 ),
				'selectors'  => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-padding: {{SIZE}}px;' ),
			)
		);

		$this->add_responsive_control(
			'panel_min_height',
			array(
				'label'       => __( 'Minimum Height', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 600 ) ),
				'default'     => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'   => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-min-height: {{SIZE}}px;' ),
				'description' => __( 'Optional. The panel is already as tall as the longest slide.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'avatar_size',
			array(
				'label'      => __( 'Client Image Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 52 ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .tm-slider' => '--tm-avatar-size: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: text ---------------- */
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => __( 'Typography', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-star-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_empty_color',
			array(
				'label'     => __( 'Empty Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.3)',
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-star-empty-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_size',
			array(
				'label'      => __( 'Star Size', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array( '{{WRAPPER}} .tm-slider' => '--tm-star-size: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'quote_color',
			array(
				'label'     => __( 'Testimonial Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'quote_typo',
				'selector' => '{{WRAPPER}} .tm-slide-quote',
			)
		);

		$this->add_control(
			'name_color',
			array(
				'label'     => __( 'Client Name Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-name-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'name_typo',
				'selector' => '{{WRAPPER}} .tm-slider .tm-name',
			)
		);

		$this->end_controls_section();

		/* ---------------- Style: navigation ---------------- */
		$this->start_controls_section(
			'section_style_nav',
			array(
				'label' => __( 'Arrows & Dots', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'nav_color',
			array(
				'label'     => __( 'Arrow Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => array( 'arrows' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-nav-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_bg',
			array(
				'label'     => __( 'Arrow Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.14)',
				'condition' => array( 'arrows' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-nav-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => __( 'Dot Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.4)',
				'separator' => 'before',
				'condition' => array( 'dots' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-dot: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_active_color',
			array(
				'label'     => __( 'Active Dot Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7a9a98',
				'condition' => array( 'dots' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-slider' => '--tm-slider-dot-active: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$html = TM_Renderer::slider(
			array(
				'count'          => $s['count'],
				'orderby'        => $s['orderby'],
				'order'          => $s['order'],
				'category'       => $s['category'],
				'featured'       => $s['featured'],
				'autoplay'       => $s['autoplay'],
				'effect'         => $s['effect'],
				'pause_hover'    => $s['pause_hover'],
				'arrows'         => $s['arrows'],
				'dots'           => $s['dots'],
				'show_rating'    => $s['show_rating'],
				'show_image'     => $s['show_image'],
				'show_position'  => $s['show_position'],
				'show_company'   => $s['show_company'],
				'show_button'    => false,
				'full_text'      => $s['full_text'],
				'excerpt_words'  => $s['excerpt_words'],
				'fallback_image' => isset( $s['fallback_image']['url'] ) ? $s['fallback_image']['url'] : '',
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
