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
			'label'   => __( 'Source', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'vessels',
			'options' => array(
				'vessels' => __( 'Vessels', 'waterslaw' ),
				'cases'   => __( 'Cases', 'waterslaw' ),
			),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Items (-1 = all)', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => -1,
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
		) );

		$this->add_control( 'order', array(
			'label'   => __( 'Order', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'ASC',
			'options' => array( 'ASC' => 'ASC', 'DESC' => 'DESC' ),
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
			'label'        => __( 'Link Cards to Single Post', 'waterslaw' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
			'description'  => __( 'Turn OFF to make the cards non-clickable, so visitors are not sent to a single Vessel page.', 'waterslaw' ),
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
			'label'       => __( 'Columns \u2014 Laptop', 'waterslaw' ),
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
			'default'      => '',
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
	protected function render() {
		$s      = $this->get_settings_for_display();
		$type   = in_array( $s['post_type'], array( 'vessels', 'cases' ), true ) ? $s['post_type'] : 'vessels';
		$layout = ( 'carousel' === $s['layout'] ) ? 'carousel' : 'grid';
		$cols   = max( 1, (int) ( $s['columns'] ?? 4 ) );
		$cols_l = max( 0, (int) ( $s['columns_laptop'] ?? 0 ) );
		$cols_l = ( $cols_l < 1 ) ? $cols : $cols_l; // empty = inherit desktop
		$cols_t = max( 1, (int) ( $s['columns_tablet'] ?? 2 ) );
		$cols_m = max( 1, (int) ( $s['columns_mobile'] ?? 1 ) );
		$btn    = ( 'yes' === $s['show_button'] ) ? $s['button_text'] : '';
		$dur    = max( 5, (int) ( $s['duration'] ?? 30 ) );
		$pause  = ( 'yes' === ( $s['pause_hover'] ?? 'yes' ) ) ? 'paused' : 'running';
		$hidet  = ( 'yes' === ( $s['hide_title_hover'] ?? '' ) ) ? ' wl-hide-title' : '';
		$linked = ( 'yes' === ( $s['link_cards'] ?? 'yes' ) );

		$q = new WP_Query( array(
			'post_type'      => $type,
			'posts_per_page' => (int) ( $s['count'] ?? -1 ),
			'orderby'        => $s['orderby'] ?? 'menu_order date',
			'order'          => $s['order'] ?? 'ASC',
			'no_found_rows'  => true,
		) );

		if ( ! $q->have_posts() ) {
			echo '<p>' . esc_html__( 'No items found. Add some first.', 'waterslaw' ) . '</p>';
			return;
		}

		$style = sprintf(
			'--wl-cols:%d;--wl-cols-l:%d;--wl-cols-t:%d;--wl-cols-m:%d;--wl-duration:%ds;--wl-pause:%s;',
			$cols, $cols_l, $cols_t, $cols_m, $dur, esc_attr( $pause )
		);

		/* Build the card markup once (reused, and duplicated for the slider). */
		ob_start();
		while ( $q->have_posts() ) :
			$q->the_post();
			$img  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
			$open = $linked
				? '<a class="wl-card" href="' . esc_url( get_permalink() ) . '">'
				: '<div class="wl-card wl-card-static">';
			?>
			<?php echo $open; // phpcs:ignore WordPress.Security.EscapingOutput -- URL escaped above ?>
				<div class="wl-card-img" style="background-image:url('<?php echo esc_url( $img ); ?>');">
					<div class="wl-card-ov wl-ov-normal"></div>
					<div class="wl-card-ov wl-ov-normal-img"></div>
					<div class="wl-card-ov wl-ov-hover"></div>
					<div class="wl-card-ov wl-ov-hover-img"></div>
					<?php if ( '' !== $btn ) : ?>
						<span class="wl-card-btn"><?php echo esc_html( $btn ); ?></span>
					<?php endif; ?>
					<h3 class="wl-card-title"><?php the_title(); ?></h3>
				</div>
			<?php echo $linked ? '</a>' : '</div>'; ?>
			<?php
		endwhile;
		wp_reset_postdata();
		$cards = ob_get_clean();
		?>
		<div class="wl-cards <?php echo esc_attr( $layout . $hidet ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<?php if ( 'carousel' === $layout ) : ?>
				<div class="wl-marquee-wrap">
					<div class="wl-marquee">
						<?php
						echo $cards; // first set
						echo $cards; // duplicate set for a seamless loop
						?>
					</div>
				</div>
			<?php else : ?>
				<div class="wl-grid"><?php echo $cards; ?></div>
			<?php endif; ?>
		</div>
		<?php
	}
}
