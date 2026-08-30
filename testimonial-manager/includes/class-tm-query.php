<?php
/**
 * Turns normalised display arguments into a WP_Query.
 *
 * Shared by the shortcode and the Elementor widget so both select testimonials
 * by exactly the same rules.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Query {

	/**
	 * Everything a caller may pass, with its default.
	 */
	public static function defaults() {
		return array(
			'count'          => 6,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'featured'       => false,
			'category'       => '',
			'columns'        => 3,
			'columns_laptop' => 0,
			'columns_tablet' => 2,
			'columns_mobile' => 1,
			'gap'            => 24,
			'excerpt_words'  => 32,
			'show_rating'    => true,
			'show_image'     => true,
			'show_company'   => true,
			'show_position'  => true,
			'show_button'    => true,
			'button_text'    => '',
			'fallback_image' => '',
			'title_tag'      => 'h3',
			'class'          => '',

			/* Slider only. */
			'autoplay'       => 6000,
			'effect'         => 'fade',
			'arrows'         => true,
			'dots'           => true,
			'pause_hover'    => true,
			'full_text'      => false,
		);
	}

	/**
	 * Coerce loose input (shortcode strings, Elementor switchers) into real types.
	 */
	public static function normalize( $args ) {
		$args = wp_parse_args( $args, self::defaults() );

		$args['count']          = (int) $args['count'];
		$args['columns']        = max( 1, min( 6, (int) $args['columns'] ) );
		$args['columns_tablet'] = max( 1, min( 6, (int) $args['columns_tablet'] ) );

		// Left empty, the laptop count inherits desktop, so existing grids are
		// unchanged until a number is entered.
		$args['columns_laptop'] = max( 0, min( 6, (int) $args['columns_laptop'] ) );
		if ( $args['columns_laptop'] < 1 ) {
			$args['columns_laptop'] = $args['columns'];
		}

		$args['columns_mobile'] = max( 1, min( 6, (int) $args['columns_mobile'] ) );
		$args['gap']            = max( 0, (int) $args['gap'] );
		$args['excerpt_words']  = max( 5, min( 200, (int) $args['excerpt_words'] ) );

		$args['orderby'] = in_array( $args['orderby'], array( 'menu_order', 'date', 'title', 'rand', 'rating' ), true )
			? $args['orderby']
			: 'menu_order';

		$args['order'] = ( 'DESC' === strtoupper( (string) $args['order'] ) ) ? 'DESC' : 'ASC';

		$args['title_tag'] = in_array( $args['title_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' ), true )
			? $args['title_tag']
			: 'h3';

		// 0 disables autoplay; anything else is clamped to a sane dwell time.
		$args['autoplay'] = (int) $args['autoplay'];
		if ( $args['autoplay'] > 0 ) {
			$args['autoplay'] = max( 1500, min( 30000, $args['autoplay'] ) );
		} else {
			$args['autoplay'] = 0;
		}

		$args['effect'] = ( 'slide' === $args['effect'] ) ? 'slide' : 'fade';

		foreach ( array( 'featured', 'show_rating', 'show_image', 'show_company', 'show_position', 'show_button', 'arrows', 'dots', 'pause_hover', 'full_text' ) as $flag ) {
			$args[ $flag ] = self::to_bool( $args[ $flag ] );
		}

		$args['fallback_image'] = esc_url_raw( (string) $args['fallback_image'] );
		$args['category']    = sanitize_text_field( (string) $args['category'] );
		$args['button_text'] = sanitize_text_field( (string) $args['button_text'] );
		$args['class']       = sanitize_html_class( (string) $args['class'] );

		if ( '' === $args['button_text'] ) {
			$args['button_text'] = __( 'READ FULL REVIEW', 'testimonial-manager' );
		}

		return $args;
	}

	/**
	 * Accepts "yes", "true", "1", true, 1 - anything else is false.
	 */
	public static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Build the WP_Query arguments for a normalised set of display arguments.
	 */
	public static function args( $args ) {
		$query = array(
			'post_type'           => TM_Post_Type::POST_TYPE,
			'post_status'         => 'publish',
			'posts_per_page'      => ( $args['count'] > 0 ) ? $args['count'] : -1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'order'               => $args['order'],
		);

		if ( 'rating' === $args['orderby'] ) {
			$query['meta_key'] = '_tm_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query['orderby']  = 'meta_value_num';
		} elseif ( 'menu_order' === $args['orderby'] ) {
			$query['orderby'] = 'menu_order date';
		} else {
			$query['orderby'] = $args['orderby'];
		}

		if ( $args['featured'] ) {
			$query['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_tm_featured',
					'value' => '1',
				),
			);
		}

		if ( '' !== $args['category'] ) {
			$terms = array_filter( array_map( 'trim', explode( ',', $args['category'] ) ) );

			if ( $terms ) {
				$numeric = ( count( $terms ) === count( array_filter( $terms, 'is_numeric' ) ) );

				$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => TM_Post_Type::TAXONOMY,
						'field'    => $numeric ? 'term_id' : 'slug',
						'terms'    => $numeric ? array_map( 'absint', $terms ) : array_map( 'sanitize_title', $terms ),
					),
				);
			}
		}

		return $query;
	}

	public static function get( $args ) {
		return new WP_Query( self::args( $args ) );
	}
}
