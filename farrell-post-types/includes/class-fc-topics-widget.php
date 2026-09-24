<?php
/**
 * Elementor widget for Trending Topics.
 *
 * Elementor has scoped selectors, but this renders through the same FC_Render
 * call as WPBakery and the shortcode, so the two builders cannot drift apart.
 * Controls are therefore mapped onto the same argument names.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Elementor\Widget_Base' ) ) {
	return;
}

class FC_Topics_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'fc_topics';
	}

	public function get_title() {
		return __( 'Trending Topics', 'farrell' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return array( 'farrell' );
	}

	private function number( $id, $label, $default, $min = 0, $max = 200 ) {
		$this->add_control(
			$id,
			array(
				'label'   => $label,
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => $min,
				'max'     => $max,
				'default' => $default,
			)
		);
	}

	private function switcher( $id, $label, $default = 'yes' ) {
		$this->add_control(
			$id,
			array(
				'label'        => $label,
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => $default,
			)
		);
	}

	private function colour( $id, $label, $default ) {
		$this->add_control(
			$id,
			array(
				'label'   => $label,
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => $default,
			)
		);
	}

	protected function register_controls() {

		/* ---------- Content ---------- */
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'farrell' ) )
		);

		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category Slug', 'farrell' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Filter to one post category slug. Blank shows all posts.', 'farrell' ),
			)
		);

		$this->number( 'count', __( 'Total Posts', 'farrell' ), 3, 2, 12 );

		$this->add_control(
			'orderby',
			array(
				'label'       => __( 'Order By', 'farrell' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => array(
					'added'      => __( 'Recently Added', 'farrell' ),
					'date'       => __( 'Publish Date', 'farrell' ),
					'modified'   => __( 'Last Modified', 'farrell' ),
					'title'      => __( 'Title', 'farrell' ),
					'menu_order' => __( 'Menu Order', 'farrell' ),
				),
				'default'     => 'added',
				'description' => __( 'Recently Added uses the order posts were added to the site, so a back-dated article still shows first when you upload it.', 'farrell' ),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'farrell' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'DESC' => __( 'Descending (newest first)', 'farrell' ),
					'ASC'  => __( 'Ascending (oldest first)', 'farrell' ),
				),
				'default' => 'DESC',
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading Text', 'farrell' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Blank renders no heading.', 'farrell' ),
			)
		);

		$this->switcher( 'show_excerpt', __( 'Show Excerpt', 'farrell' ), 'yes' );
		$this->number( 'excerpt_words', __( 'Featured Excerpt Words', 'farrell' ), 28, 0, 200 );
		$this->number( 'item_excerpt_words', __( 'List Excerpt Words', 'farrell' ), 18, 0, 200 );
		$this->number( 'title_words', __( 'Featured Title Words', 'farrell' ), 0, 0, 100 );
		$this->number( 'item_title_words', __( 'List Title Words', 'farrell' ), 0, 0, 100 );
		$this->switcher( 'show_date', __( 'Show Date Badge', 'farrell' ), 'yes' );
		$this->switcher( 'show_category', __( 'Show Category', 'farrell' ), '' );
		$this->switcher( 'show_author', __( 'Show Author', 'farrell' ), '' );
		$this->switcher( 'show_button', __( 'Show Button', 'farrell' ), 'yes' );

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button Text', 'farrell' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'READ MORE',
			)
		);

		$this->switcher( 'show_arrow', __( 'Show Arrow', 'farrell' ), 'yes' );

		$this->end_controls_section();

		/* ---------- Style ---------- */
		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Style', 'farrell' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->colour( 'gold', __( 'Gold', 'farrell' ), '#d5af34' );
		$this->colour( 'navy', __( 'Navy', 'farrell' ), '#162542' );
		$this->number( 'heading_size', __( 'Heading Size', 'farrell' ), 36, 10, 90 );
		$this->number( 'heading_lh', __( 'Heading Line Height', 'farrell' ), 40, 10, 120 );
		$this->number( 'title_size', __( 'Featured Title Size', 'farrell' ), 36, 10, 90 );
		$this->number( 'title_lh', __( 'Featured Title Line Height', 'farrell' ), 40, 10, 120 );
		$this->number( 'item_title_size', __( 'List Title Size', 'farrell' ), 26, 10, 90 );
		$this->number( 'item_title_lh', __( 'List Title Line Height', 'farrell' ), 32, 10, 120 );
		$this->number( 'text_size', __( 'Text Size', 'farrell' ), 18, 8, 48 );
		$this->number( 'text_lh', __( 'Text Line Height', 'farrell' ), 30, 8, 80 );
		$this->number( 'featured_width', __( 'Featured Column Width (%)', 'farrell' ), 50, 20, 80 );
		$this->number( 'column_gap', __( 'Gap: Featured to List', 'farrell' ), 30, 0, 160 );
		$this->number( 'row_gap', __( 'Gap Between List Items', 'farrell' ), 30, 0, 160 );
		$this->number( 'date_size', __( 'Badge Font Size', 'farrell' ), 16, 8, 40 );
		$this->number( 'date_lh', __( 'Badge Line Height', 'farrell' ), 20, 8, 60 );
		$this->number( 'date_right', __( 'Badge Inset From Right', 'farrell' ), 20, 0, 120 );
		$this->number( 'date_bottom', __( 'Badge Inset From Bottom', 'farrell' ), 20, 0, 120 );
		$this->colour( 'date_color', __( 'Badge Text Color', 'farrell' ), '#162542' );
		$this->number( 'btn_size', __( 'Button Font Size', 'farrell' ), 18, 8, 40 );
		$this->number( 'item_media_pct', __( 'List Photo Width (%)', 'farrell' ), 52, 20, 80 );

		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$args = array();

		foreach ( FC_Render::defaults() as $key => $default ) {
			if ( ! isset( $s[ $key ] ) ) {
				continue;
			}

			$args[ $key ] = $s[ $key ];
		}

		// A switcher that is off arrives as '' rather than 'no'.
		foreach ( array( 'show_excerpt', 'show_date', 'show_category', 'show_author', 'show_button', 'show_arrow' ) as $flag ) {
			$args[ $flag ] = ( isset( $s[ $flag ] ) && 'yes' === $s[ $flag ] ) ? 'yes' : 'no';
		}

		foreach ( FC_Style::topics_schema() as $key => $rule ) {
			if ( isset( $s[ $key ] ) && '' !== $s[ $key ] ) {
				$args[ $key ] = $s[ $key ];
			}
		}

		echo FC_Render::topics( $args ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- escaped in the renderer.
	}
}
