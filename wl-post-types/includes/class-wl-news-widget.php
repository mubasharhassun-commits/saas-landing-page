<?php
/**
 * WL News — Elementor widget: "Recent News" with one large featured post
 * (left) and a list of posts (right) with icon meta + Read More.
 * Fully customizable: icons, fonts, colors, image sizes, spacing.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_News_Widget extends \Elementor\Widget_Base {

	public function get_name()       { return 'wl_news'; }
	public function get_title()      { return __( 'WL News (Featured + List)', 'waterslaw' ); }
	public function get_icon()       { return 'eicon-post-list'; }
	public function get_categories() { return array( 'waterslaw' ); }
	public function get_keywords()   { return array( 'news', 'posts', 'recent', 'featured', 'waters' ); }

	/* =========================================================
	 * CONTROLS
	 * ======================================================= */
	protected function register_controls() {

		/* ---------------- CONTENT: Query ---------------- */
		$this->start_controls_section( 'sec_query', array(
			'label' => __( 'Query', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'post_type', array(
			'label'   => __( 'Source', 'waterslaw' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'news',
			'options' => array(
				'news' => __( 'News (CPT)', 'waterslaw' ),
				'post' => __( 'Blog Posts', 'waterslaw' ),
			),
		) );

		$this->add_control( 'category', array(
			'label'       => __( 'Category Slug (optional)', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'Filter to one category term slug. Blank = all.', 'waterslaw' ),
		) );

		$this->add_control( 'count', array(
			'label'       => __( 'Total Posts', 'waterslaw' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 3,
			'min'         => 2,
			'max'         => 10,
			'description' => __( '1 = big featured, the rest fill the list.', 'waterslaw' ),
		) );

		$this->end_controls_section();

		/* ---------------- CONTENT: Meta & Icons ---------------- */
		$this->start_controls_section( 'sec_meta', array(
			'label' => __( 'Meta & Icons', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'show_date', array(
			'label' => __( 'Show Date', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes', 'default' => 'yes',
		) );
		$this->add_control( 'date_icon', array(
			'label' => __( 'Date Icon', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::ICONS,
			'default' => array( 'value' => '', 'library' => '' ), // empty = built-in SVG
			'description' => __( 'Leave empty for the built-in icon, or pick your own.', 'waterslaw' ),
			'condition' => array( 'show_date' => 'yes' ),
		) );

		$this->add_control( 'show_category', array(
			'label' => __( 'Show Category', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes', 'default' => 'yes', 'separator' => 'before',
		) );
		$this->add_control( 'category_icon', array(
			'label' => __( 'Category Icon', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::ICONS,
			'default' => array( 'value' => '', 'library' => '' ),
			'condition' => array( 'show_category' => 'yes' ),
		) );

		$this->add_control( 'show_author', array(
			'label' => __( 'Show Author', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes', 'default' => 'yes', 'separator' => 'before',
		) );
		$this->add_control( 'author_icon', array(
			'label' => __( 'Author Icon', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::ICONS,
			'default' => array( 'value' => '', 'library' => '' ),
			'condition' => array( 'show_author' => 'yes' ),
		) );

		$this->add_control( 'button_text', array(
			'label' => __( 'Read More Text', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'READ MORE', 'waterslaw' ), 'separator' => 'before',
		) );

		$this->end_controls_section();

		/* ==================== STYLE TAB ==================== */

		/* ---- Layout ---- */
		$this->start_controls_section( 'style_layout', array(
			'label' => __( 'Layout', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'columns_gap', array(
			'label' => __( 'Gap: Featured ↔ List', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'default' => array( 'size' => 34 ),
			'selectors' => array( '{{WRAPPER}} .wl-news' => 'gap: {{SIZE}}px;' ),
		) );

		$this->add_responsive_control( 'list_gap', array(
			'label' => __( 'Gap Between List Items', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'default' => array( 'size' => 24 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-list' => 'gap: {{SIZE}}px;' ),
		) );

		$this->end_controls_section();

		/* ---- Featured ---- */
		$this->start_controls_section( 'style_featured', array(
			'label' => __( 'Featured (Left)', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'featured_height', array(
			'label' => __( 'Height (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 200, 'max' => 700 ) ), 'default' => array( 'size' => 340 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-featured' => 'min-height: {{SIZE}}px;' ),
		) );
		$this->add_control( 'featured_radius', array(
			'label' => __( 'Corner Radius (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 4 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-featured' => 'border-radius: {{SIZE}}px;' ),
		) );
		$this->add_control( 'featured_overlay', array(
			'label' => __( 'Overlay Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'default' => 'rgba(0,0,0,0.75)',
			'selectors' => array( '{{WRAPPER}} .wl-news-featured-overlay' => 'background: linear-gradient(to top, {{VALUE}} 0%, rgba(0,0,0,0.1) 60%, transparent 100%);' ),
		) );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name' => 'featured_title_typo', 'label' => __( 'Title Typography', 'waterslaw' ),
			'selector' => '{{WRAPPER}} .wl-news-featured-title',
		) );
		$this->add_control( 'featured_title_color', array(
			'label' => __( 'Title Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'default' => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .wl-news-featured-title' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();

		/* ---- List: Thumbnail ---- */
		$this->start_controls_section( 'style_thumb', array(
			'label' => __( 'List Thumbnail', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'thumb_w', array(
			'label' => __( 'Width (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 50, 'max' => 220 ) ), 'default' => array( 'size' => 92 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-thumb' => 'flex-basis: {{SIZE}}px;' ),
		) );
		$this->add_responsive_control( 'thumb_h', array(
			'label' => __( 'Height (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 50, 'max' => 220 ) ), 'default' => array( 'size' => 92 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-thumb' => 'height: {{SIZE}}px;' ),
		) );
		$this->add_control( 'thumb_radius', array(
			'label' => __( 'Corner Radius (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 4 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-thumb' => 'border-radius: {{SIZE}}px;' ),
		) );

		$this->end_controls_section();

		/* ---- List: Title ---- */
		$this->start_controls_section( 'style_list_title', array(
			'label' => __( 'List Title', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name' => 'list_title_typo', 'selector' => '{{WRAPPER}} .wl-news-title',
		) );
		$this->add_control( 'list_title_color', array(
			'label' => __( 'Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'default' => '#1a2b3c',
			'selectors' => array( '{{WRAPPER}} .wl-news-title' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'list_title_hover', array(
			'label' => __( 'Hover Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'default' => '#7a9a98',
			'selectors' => array( '{{WRAPPER}} .wl-news-title:hover' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();

		/* ---- Meta ---- */
		$this->start_controls_section( 'style_meta', array(
			'label' => __( 'Meta (Date / Category / Author)', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name' => 'meta_typo', 'selector' => '{{WRAPPER}} .wl-news-meta',
		) );
		$this->add_control( 'meta_color', array(
			'label' => __( 'Text Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'default' => '#8a8a8a',
			'selectors' => array( '{{WRAPPER}} .wl-news-list .wl-news-meta' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'meta_icon_color', array(
			'label' => __( 'Icon Color', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .wl-news-list .wl-news-meta .wl-m svg, {{WRAPPER}} .wl-news-list .wl-news-meta .wl-m i' => 'color: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'meta_icon_size', array(
			'label' => __( 'Icon Size (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 8, 'max' => 28 ) ), 'default' => array( 'size' => 14 ),
			'selectors' => array(
				'{{WRAPPER}} .wl-news-meta .wl-m svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				'{{WRAPPER}} .wl-news-meta .wl-m i'   => 'font-size: {{SIZE}}px;',
			),
		) );
		$this->add_responsive_control( 'meta_gap', array(
			'label' => __( 'Gap Between Items', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 4, 'max' => 40 ) ), 'default' => array( 'size' => 14 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-meta' => 'gap: {{SIZE}}px;' ),
		) );

		$this->end_controls_section();

		/* ---- Read More ---- */
		$this->start_controls_section( 'style_button', array(
			'label' => __( 'Read More Button', 'waterslaw' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name' => 'btn_typo', 'selector' => '{{WRAPPER}} .wl-news-readmore',
		) );

		$this->start_controls_tabs( 'btn_tabs' );

		$this->start_controls_tab( 'btn_normal', array( 'label' => __( 'Normal', 'waterslaw' ) ) );
		$this->add_control( 'btn_color', array(
			'label' => __( 'Text', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#1a2b3c',
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'btn_bg', array(
			'label' => __( 'Background', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => 'transparent',
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore' => 'background: {{VALUE}};' ),
		) );
		$this->add_control( 'btn_border', array(
			'label' => __( 'Border', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#d9d9d9',
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore' => 'border-color: {{VALUE}};' ),
		) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'btn_hover', array( 'label' => __( 'Hover', 'waterslaw' ) ) );
		$this->add_control( 'btn_color_h', array(
			'label' => __( 'Text', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore:hover' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'btn_bg_h', array(
			'label' => __( 'Background', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#7a9a98',
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore:hover' => 'background: {{VALUE}}; border-color: {{VALUE}};' ),
		) );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'btn_padding', array(
			'label' => __( 'Padding', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => array( 'px' ),
			'default' => array( 'top' => 7, 'right' => 16, 'bottom' => 7, 'left' => 16, 'unit' => 'px', 'isLinked' => false ),
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->add_control( 'btn_radius', array(
			'label' => __( 'Border Radius (px)', 'waterslaw' ), 'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 0 ),
			'selectors' => array( '{{WRAPPER}} .wl-news-readmore' => 'border-radius: {{SIZE}}px;' ),
		) );

		$this->end_controls_section();
	}

	/* ---------- icon render helper (chosen icon OR fallback SVG) ---------- */
	private function icon( $setting, $fallback_svg ) {
		if ( is_array( $setting ) && ! empty( $setting['value'] ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $setting, array( 'aria-hidden' => 'true' ) );
			$html = ob_get_clean();
			if ( '' !== trim( (string) $html ) ) {
				return $html;
			}
		}
		return $fallback_svg;
	}

	/* Load FontAwesome only if the user actually picked FA icons. */
	private function enqueue_icon_fonts( $s ) {
		$map = array(
			'fa-regular' => 'elementor-icons-fa-regular',
			'fa-solid'   => 'elementor-icons-fa-solid',
			'fa-brands'  => 'elementor-icons-fa-brands',
		);
		foreach ( array( 'date_icon', 'category_icon', 'author_icon' ) as $k ) {
			if ( ! empty( $s[ $k ]['library'] ) && isset( $map[ $s[ $k ]['library'] ] ) ) {
				wp_enqueue_style( $map[ $s[ $k ]['library'] ] );
			}
		}
	}

	/* ---------- meta row ---------- */
	private function meta_html( $s ) {
		$out = '';

		if ( 'yes' === $s['show_date'] ) {
			$ic = $this->icon( $s['date_icon'], '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>' );
			$out .= '<span class="wl-m">' . $ic . esc_html( get_the_date() ) . '</span>';
		}

		if ( 'yes' === $s['show_category'] ) {
			$term_name = '';
			foreach ( array( 'category', 'news_category' ) as $tax ) {
				$terms = get_the_terms( get_the_ID(), $tax );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { $term_name = $terms[0]->name; break; }
			}
			if ( '' !== $term_name ) {
				$ic = $this->icon( $s['category_icon'], '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h6l2 2h10v9H3z"/></svg>' );
				$out .= '<span class="wl-m">' . $ic . esc_html__( 'Category:', 'waterslaw' ) . ' ' . esc_html( $term_name ) . '</span>';
			}
		}

		if ( 'yes' === $s['show_author'] ) {
			$ic = $this->icon( $s['author_icon'], '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>' );
			$out .= '<span class="wl-m">' . $ic . esc_html__( 'Author:', 'waterslaw' ) . ' ' . esc_html( get_the_author() ) . '</span>';
		}

		return $out;
	}

	/* =========================================================
	 * RENDER
	 * ======================================================= */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$type = sanitize_key( $s['post_type'] ? $s['post_type'] : 'news' );
		$btn  = $s['button_text'] ? $s['button_text'] : 'READ MORE';

		$this->enqueue_icon_fonts( $s );

		$args = array(
			'post_type'      => $type,
			'posts_per_page' => max( 2, (int) $s['count'] ),
			'no_found_rows'  => true,
		);
		if ( ! empty( $s['category'] ) ) {
			$cat_slug = sanitize_title( $s['category'] );
			if ( 'news' === $type ) {
				$args['tax_query'] = array( array( 'taxonomy' => 'news_category', 'field' => 'slug', 'terms' => $cat_slug ) );
			} else {
				$args['category_name'] = $cat_slug;
			}
		}

		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) {
			echo '<p>' . esc_html__( 'No posts found.', 'waterslaw' ) . '</p>';
			return;
		}

		$posts = $q->posts;
		$first = array_shift( $posts );
		?>
		<div class="wl-news">

			<?php
			$GLOBALS['post'] = $first; // phpcs:ignore
			setup_postdata( $first );
			$fimg = get_the_post_thumbnail_url( $first->ID, 'large' );
			?>
			<a class="wl-news-featured" href="<?php echo esc_url( get_permalink( $first->ID ) ); ?>"
			   style="background-image:url('<?php echo esc_url( $fimg ); ?>');">
				<div class="wl-news-featured-overlay"></div>
				<div class="wl-news-featured-body">
					<h3 class="wl-news-featured-title"><?php echo esc_html( get_the_title( $first->ID ) ); ?></h3>
					<div class="wl-news-meta light"><?php echo $this->meta_html( $s ); // phpcs:ignore WordPress.Security.EscapingOutput -- values escaped inside meta_html ?></div>
				</div>
			</a>

			<div class="wl-news-list">
				<?php foreach ( $posts as $p ) : ?>
					<?php
					$GLOBALS['post'] = $p; // phpcs:ignore
					setup_postdata( $p );
					$timg = get_the_post_thumbnail_url( $p->ID, 'medium' );
					$link = get_permalink( $p->ID );
					?>
					<div class="wl-news-item">
						<a class="wl-news-thumb" href="<?php echo esc_url( $link ); ?>"
						   style="background-image:url('<?php echo esc_url( $timg ); ?>');"></a>
						<div class="wl-news-body">
							<a class="wl-news-title" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></a>
							<div class="wl-news-meta"><?php echo $this->meta_html( $s ); // phpcs:ignore WordPress.Security.EscapingOutput -- values escaped inside meta_html ?></div>
							<a class="wl-news-readmore" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $btn ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
		<?php
		wp_reset_postdata();
	}
}
