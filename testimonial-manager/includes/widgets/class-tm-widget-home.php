<?php
/**
 * "Home Testimonial" Elementor widget.
 *
 * The home page panel: one wide tinted block with an oversized quote mark and
 * the client photo down the left, the testimonial and signature beside them,
 * and a pair of chevrons bottom right. Same testimonials as the grid and the
 * banner slider, so adding one anywhere shows it everywhere.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Widget_Home extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tm_home_testimonial';
	}

	public function get_title() {
		return __( 'Home Testimonial', 'testimonial-manager' );
	}

	public function get_icon() {
		return 'eicon-testimonial';
	}

	public function get_categories() {
		return array( 'testimonial-manager' );
	}

	public function get_keywords() {
		return array( 'testimonial', 'home', 'slider', 'review', 'quote' );
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

	/** Shorthand for a px slider bound to one custom property. */
	private function px( $id, $label, $min, $max, $default, $var, $extra = array() ) {
		$args = array(
			'label'      => $label,
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => $min, 'max' => $max ) ),
			'selectors'  => array( '{{WRAPPER}} .tm-home' => $var . ': {{SIZE}}px;' ),
		);

		if ( null !== $default ) {
			$args['default'] = array( 'unit' => 'px', 'size' => $default );
		}

		$this->add_control( $id, array_merge( $args, $extra ) );
	}

	private function weight_options() {
		return array(
			'300' => __( 'Light', 'testimonial-manager' ),
			'400' => __( 'Normal', 'testimonial-manager' ),
			'500' => __( 'Medium', 'testimonial-manager' ),
			'600' => __( 'Semi Bold', 'testimonial-manager' ),
			'700' => __( 'Bold', 'testimonial-manager' ),
			'800' => __( 'Extra Bold', 'testimonial-manager' ),
		);
	}

	protected function register_controls() {

		/* ---------------- Content ---------------- */
		$this->start_controls_section(
			'sec_query',
			array( 'label' => __( 'Testimonials', 'testimonial-manager' ) )
		);

		$this->add_control(
			'count',
			array(
				'label'       => __( 'Number of Testimonials', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 30,
				'default'     => 5,
				'description' => __( 'Newest first, so a new testimonial appears here on its own.', 'testimonial-manager' ),
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

		$this->add_control(
			'salutation',
			array(
				'label'       => __( 'Text Above the Name', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Sincerely,', 'testimonial-manager' ),
				'description' => __( 'Leave empty to show the name on its own.', 'testimonial-manager' ),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'       => __( 'Autoplay Speed (ms)', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 6000,
				'description' => __( 'Use 0 to turn autoplay off.', 'testimonial-manager' ),
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
				'label'        => __( 'Show Chevrons', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => __( 'Show Dots', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();

		/* ---------------- Display ---------------- */
		$this->start_controls_section(
			'sec_display',
			array( 'label' => __( 'Display', 'testimonial-manager' ) )
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => __( 'Show Client Image', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_rating',
			array(
				'label'        => __( 'Show Rating', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'show_position',
			array(
				'label'        => __( 'Show Position', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'show_company',
			array(
				'label'        => __( 'Show Company', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'full_text',
			array(
				'label'        => __( 'Show Full Testimonial', 'testimonial-manager' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
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
				'condition' => array( 'full_text' => '' ),
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label' => __( 'Default Client Image', 'testimonial-manager' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			)
		);

		$this->end_controls_section();

		/* ---------------- Layout ---------------- */
		$this->start_controls_section(
			'style_layout',
			array(
				'label' => __( 'Layout', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'      => __( 'Panel Padding', 'testimonial-manager' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => array( 'top' => 48, 'right' => 48, 'bottom' => 48, 'left' => 48, 'unit' => 'px', 'isLinked' => true ),
				'selectors'  => array( '{{WRAPPER}} .tm-home' => '--tm-home-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->px( 'panel_min_height', __( 'Panel Minimum Height', 'testimonial-manager' ), 0, 900, 0, '--tm-home-min-height' );
		$this->px( 'panel_radius', __( 'Panel Corner Radius', 'testimonial-manager' ), 0, 80, 0, '--tm-home-radius' );
		$this->px( 'column_width', __( 'Left Column Width', 'testimonial-manager' ), 40, 320, 128, '--tm-home-col', array( 'description' => __( 'Holds the quote mark and the photo below it.', 'testimonial-manager' ) ) );
		$this->px( 'column_gap', __( 'Gap Between Columns', 'testimonial-manager' ), 0, 120, 26, '--tm-home-col-gap' );
		$this->px( 'row_gap', __( 'Gap Between Rows', 'testimonial-manager' ), 0, 120, 40, '--tm-home-row-gap' );

		$this->end_controls_section();

		/* ---------------- Panel & quote mark ---------------- */
		$this->start_controls_section(
			'style_panel',
			array(
				'label' => __( 'Panel & Quote Mark', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Panel Background', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7ab3af',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'mark_color',
			array(
				'label'     => __( 'Quote Mark Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-mark-color: {{VALUE}};' ),
			)
		);

		$this->px( 'mark_size', __( 'Quote Mark Size', 'testimonial-manager' ), 20, 300, 96, '--tm-home-mark-size' );

		$this->add_control(
			'mark_weight',
			array(
				'label'     => __( 'Quote Mark Weight', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '700',
				'options'   => $this->weight_options() + array( '900' => __( 'Black', 'testimonial-manager' ) ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-mark-weight: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'font_family',
			array(
				'label'       => __( 'Font Family', 'testimonial-manager' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Leave empty to inherit the theme font.', 'testimonial-manager' ),
				'selectors'   => array( '{{WRAPPER}} .tm-home' => '--tm-font: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Testimonial text ---------------- */
		$this->start_controls_section(
			'style_quote',
			array(
				'label' => __( 'Testimonial Text', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'quote_color',
			array(
				'label'     => __( 'Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-quote-color: {{VALUE}};' ),
			)
		);

		$this->px( 'quote_size', __( 'Size', 'testimonial-manager' ), 8, 60, 19, '--tm-home-quote-size' );

		$this->add_control(
			'quote_weight',
			array(
				'label'     => __( 'Weight', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '400',
				'options'   => $this->weight_options(),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-quote-weight: {{VALUE}};' ),
			)
		);

		$this->px( 'quote_lh', __( 'Line Height', 'testimonial-manager' ), 8, 120, null, '--tm-home-quote-lh' );
		$this->px( 'quote_spacing', __( 'Letter Spacing', 'testimonial-manager' ), -5, 20, null, '--tm-home-quote-spacing' );

		$this->add_control(
			'quote_style',
			array(
				'label'     => __( 'Style', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'italic',
				'options'   => array(
					'italic' => __( 'Italic', 'testimonial-manager' ),
					'normal' => __( 'Normal', 'testimonial-manager' ),
				),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-quote-style: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'quote_align',
			array(
				'label'     => __( 'Alignment', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => __( 'Left', 'testimonial-manager' ), 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => __( 'Center', 'testimonial-manager' ), 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => __( 'Right', 'testimonial-manager' ), 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => __( 'Justify', 'testimonial-manager' ), 'icon' => 'eicon-text-align-justify' ),
				),
				'default'   => 'left',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-quote-align: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------------- Client ---------------- */
		$this->start_controls_section(
			'style_client',
			array(
				'label' => __( 'Client', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->px( 'avatar_size', __( 'Image Size', 'testimonial-manager' ), 16, 220, 92, '--tm-avatar-size' );

		$this->add_control(
			'avatar_ring_color',
			array(
				'label'     => __( 'Image Ring Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-ring-color: {{VALUE}};' ),
			)
		);

		$this->px( 'avatar_ring_width', __( 'Image Ring Width', 'testimonial-manager' ), 0, 20, 3, '--tm-home-ring-width' );

		$this->add_control(
			'name_color',
			array(
				'label'     => __( 'Name Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-name-color: {{VALUE}};' ),
			)
		);

		$this->px( 'name_size', __( 'Name Size', 'testimonial-manager' ), 8, 60, 17, '--tm-home-name-size' );

		$this->add_control(
			'name_weight',
			array(
				'label'     => __( 'Name Weight', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '600',
				'options'   => $this->weight_options(),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-name-weight: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'name_style',
			array(
				'label'     => __( 'Name Style', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'italic',
				'options'   => array(
					'italic' => __( 'Italic', 'testimonial-manager' ),
					'normal' => __( 'Normal', 'testimonial-manager' ),
				),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-name-style: {{VALUE}};' ),
			)
		);

		$this->px( 'name_lh', __( 'Name Line Height', 'testimonial-manager' ), 8, 120, null, '--tm-home-name-lh' );

		$this->add_control(
			'role_color',
			array(
				'label'     => __( 'Position / Company Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.8)',
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-role-color: {{VALUE}};' ),
			)
		);

		$this->px( 'role_size', __( 'Position / Company Size', 'testimonial-manager' ), 8, 40, 14, '--tm-home-role-size' );

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => array( 'show_rating' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-star-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'star_empty_color',
			array(
				'label'     => __( 'Empty Star Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.35)',
				'condition' => array( 'show_rating' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-star-empty-color: {{VALUE}};' ),
			)
		);

		$this->px( 'star_size', __( 'Star Size', 'testimonial-manager' ), 8, 120, 18, '--tm-star-size', array( 'condition' => array( 'show_rating' => 'yes' ) ) );
		$this->px( 'star_gap', __( 'Space Between Stars', 'testimonial-manager' ), 0, 40, 3, '--tm-star-gap', array( 'condition' => array( 'show_rating' => 'yes' ) ) );

		$this->end_controls_section();

		/* ---------------- Chevrons ---------------- */
		$this->start_controls_section(
			'style_nav',
			array(
				'label' => __( 'Chevrons', 'testimonial-manager' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'nav_color',
			array(
				'label'     => __( 'Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.65)',
				'condition' => array( 'arrows' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-nav-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_hover_color',
			array(
				'label'     => __( 'Hover Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => array( 'arrows' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-home-nav-hover: {{VALUE}};' ),
			)
		);

		$this->px( 'nav_size', __( 'Size', 'testimonial-manager' ), 12, 90, 30, '--tm-home-nav-size', array( 'condition' => array( 'arrows' => 'yes' ) ) );
		$this->px( 'nav_gap', __( 'Space Between Chevrons', 'testimonial-manager' ), 0, 90, 48, '--tm-home-nav-gap', array( 'condition' => array( 'arrows' => 'yes' ) ) );
		$this->px( 'nav_bottom', __( 'Distance From Bottom', 'testimonial-manager' ), 0, 400, 78, '--tm-home-nav-bottom', array( 'condition' => array( 'arrows' => 'yes' ) ) );
		$this->px( 'nav_right', __( 'Distance From Right', 'testimonial-manager' ), 0, 400, 48, '--tm-home-nav-right', array( 'condition' => array( 'arrows' => 'yes' ) ) );

		$this->add_control(
			'dot_color',
			array(
				'label'     => __( 'Dot Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.4)',
				'condition' => array( 'dots' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-slider-dot: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_active_color',
			array(
				'label'     => __( 'Active Dot Color', 'testimonial-manager' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => array( 'dots' => 'yes' ),
				'selectors' => array( '{{WRAPPER}} .tm-home' => '--tm-slider-dot-active: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		// Styling is applied by Elementor's own selectors, so only the content
		// settings are handed to the shared renderer.
		$html = TM_Renderer::home_slider(
			array(
				'count'          => $s['count'],
				'orderby'        => 'date',
				'order'          => 'DESC',
				'category'       => $s['category'],
				'featured'       => $s['featured'],
				'salutation'     => $s['salutation'],
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
