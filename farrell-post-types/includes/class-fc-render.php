<?php
/**
 * Builds the Trending Topics markup. Returns HTML; never echoes.
 *
 * @package farrell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FC_Render {

	public static function defaults() {
		return array(
			'source'        => 'post',
			'category'      => '',
			'count'         => 3,

			/*
			 * Sorted by when the post was added to the site, not by the date
			 * printed on the card. Those differ whenever an article is
			 * back-dated: a piece from an older year uploaded today is the
			 * newest thing on the site and the oldest thing by publish date.
			 */
			'orderby'       => 'added',
			'order'         => 'DESC',

			// Blank by default: pages usually carry their own heading already,
			// and two of them is worse than none.
			'heading'       => '',
			'heading_tag'   => 'h2',

			/*
			 * Word limits, kept separate for the two shapes: the featured
			 * story has a wide column and can carry more than a list row can.
			 * 0 means no limit.
			 *
			 * Headings are capped at 13 so that two of them beside each other
			 * come out the same depth. Left uncapped, an 8-word title and a
			 * 13-word one sat at three lines and four.
			 */
			'title_words'        => 13,
			'item_title_words'   => 13,
			'show_excerpt'       => 'yes',
			'excerpt_words'      => 28,
			'item_excerpt_words' => 18,

			'show_date'     => 'yes',
			'date_format'   => 'M d, Y',
			'show_category' => 'no',
			'show_author'   => 'no',

			'show_button'   => 'yes',
			'button_text'   => 'READ MORE',
			'show_arrow'    => 'yes',

			'class'         => '',
		);
	}

	public static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Trim a heading to a word count. 0 keeps it whole.
	 */
	private static function title( $post, $words ) {
		$title = wp_strip_all_tags( (string) get_the_title( $post->ID ) );

		return ( $words < 1 ) ? $title : wp_trim_words( $title, $words, '...' );
	}

	/**
	 * Post text trimmed to a word count, falling back to the content when no
	 * excerpt has been written.
	 */
	private static function excerpt( $post, $words ) {
		$text = has_excerpt( $post->ID )
			? get_the_excerpt( $post )
			: strip_shortcodes( $post->post_content );

		$text = wp_strip_all_tags( (string) $text, true );
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );

		if ( '' === $text || $words < 1 ) {
			return '';
		}

		return wp_trim_words( $text, $words, '...' );
	}

	private static function arrow() {
		return '<svg class="fc-topic-arrow" width="14" height="10" viewBox="0 0 14 10" aria-hidden="true" focusable="false">'
			. '<path d="M9 1l4 4-4 4M13 5H1" fill="none" stroke="currentColor" stroke-width="1.4"'
			. ' stroke-linecap="square"/></svg>';
	}

	/**
	 * The date chip that sits on the corner of the photo.
	 */
	private static function date_badge( $post, $args ) {
		if ( ! self::to_bool( $args['show_date'] ) ) {
			return '';
		}

		$format = sanitize_text_field( (string) $args['date_format'] );
		$format = ( '' === $format ) ? 'M d, Y' : $format;

		return '<span class="fc-topic-date">' . esc_html( get_the_date( $format, $post->ID ) ) . '</span>';
	}

	/**
	 * Category and author, when either is switched on. The date lives on the
	 * photo instead, so it is not repeated here.
	 */
	private static function meta_html( $post, $args ) {
		$bits = array();

		if ( self::to_bool( $args['show_category'] ) ) {
			// Whatever category-like taxonomy this post's type actually has,
			// rather than a hard-coded name.
			$taxonomies = get_object_taxonomies( $post->post_type, 'names' );
			usort(
				$taxonomies,
				function ( $a, $b ) {
					return ( false !== strpos( $b, 'categor' ) ) <=> ( false !== strpos( $a, 'categor' ) );
				}
			);

			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( $terms && ! is_wp_error( $terms ) ) {
					$names  = wp_list_pluck( $terms, 'name' );
					$bits[] = '<span class="fc-topic-meta-item">' . esc_html( implode( ', ', $names ) ) . '</span>';
					break;
				}
			}
		}

		if ( self::to_bool( $args['show_author'] ) ) {
			$author = get_the_author_meta( 'display_name', (int) $post->post_author );

			if ( '' !== (string) $author ) {
				$bits[] = '<span class="fc-topic-meta-item">' . esc_html( $author ) . '</span>';
			}
		}

		return $bits ? '<div class="fc-topic-meta">' . implode( '', $bits ) . '</div>' : '';
	}

	private static function media( $post, $args ) {
		$img  = get_the_post_thumbnail_url( $post->ID, 'large' );
		$link = get_permalink( $post->ID );

		/*
		 * The URL is validated as a CSS value, not merely escaped as one.
		 *
		 * esc_url turns a quote into &#039;, and the browser turns that back
		 * into a quote while reading the attribute - before any CSS is
		 * parsed. A url() built that way can therefore be closed early by a
		 * crafted filename. FC_Style::image rejects a URL containing a quote,
		 * bracket, semicolon or space outright, and esc_attr then keeps the
		 * finished declaration inside the attribute.
		 */
		$css = FC_Style::image( (string) $img );

		if ( '' === $css ) {
			// An empty frame rather than nothing: the photos in the two
			// columns have to start on the same line.
			return '<span class="fc-topic-media-empty" aria-hidden="true"></span>';
		}

		return '<a class="fc-topic-media" href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true">'
			. '<span class="fc-topic-img" style="' . esc_attr( 'background-image:' . $css ) . '"></span>'
			. self::date_badge( $post, $args )
			. '</a>';
	}

	private static function button( $post, $args ) {
		if ( ! self::to_bool( $args['show_button'] ) ) {
			return '';
		}

		$label = sanitize_text_field( (string) $args['button_text'] );
		$label = ( '' === $label ) ? __( 'READ MORE', 'farrell' ) : $label;

		$arrow = self::to_bool( $args['show_arrow'] ) ? self::arrow() : '';

		return '<a class="fc-topic-btn" href="' . esc_url( get_permalink( $post->ID ) ) . '">'
			. '<span>' . esc_html( $label ) . '</span>' . $arrow . '</a>';
	}

	/**
	 * One story. $variant is 'featured' or 'item'.
	 */
	private static function topic( $post, $args, $variant ) {
		$link = get_permalink( $post->ID );

		$title_key   = ( 'item' === $variant ) ? 'item_title_words' : 'title_words';
		$excerpt_key = ( 'item' === $variant ) ? 'item_excerpt_words' : 'excerpt_words';

		$words   = max( 0, min( 100, (int) $args[ $title_key ] ) );
		$ewords  = max( 0, min( 200, (int) $args[ $excerpt_key ] ) );
		$excerpt = self::to_bool( $args['show_excerpt'] ) ? self::excerpt( $post, $ewords ) : '';

		$out = '<article class="fc-topic fc-topic-' . esc_attr( $variant ) . '">';
		$out .= self::media( $post, $args );

		$out .= '<div class="fc-topic-body">';
		$out .= '<h3 class="fc-topic-title"><a href="' . esc_url( $link ) . '">'
			. esc_html( self::title( $post, $words ) ) . '</a></h3>';
		$out .= self::meta_html( $post, $args );

		if ( '' !== $excerpt ) {
			$out .= '<p class="fc-topic-excerpt">' . esc_html( $excerpt ) . '</p>';
		}

		$out .= self::button( $post, $args );
		$out .= '</div></article>';

		return $out;
	}

	/**
	 * Featured story on the left, the rest listed beside it.
	 */
	public static function topics( $args ) {
		$args = wp_parse_args( $args, self::defaults() );

		/*
		 * Ordinary posts unless something else is asked for by name, and
		 * ordinary posts again if that name is not a registered type.
		 *
		 * The fallback is the point: an element saved while this plugin still
		 * had a post type of its own carries source="news", and without it
		 * that element would quietly render nothing.
		 */
		$type = sanitize_key( (string) $args['source'] );

		if ( '' === $type || ! post_type_exists( $type ) ) {
			$type = 'post';
		}

		$count = max( 2, min( 12, (int) $args['count'] ) );
		$order = ( 'ASC' === strtoupper( trim( (string) $args['order'] ) ) ) ? 'ASC' : 'DESC';

		$query = array(
			'post_type'      => $type,
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'no_found_rows'  => true,
			'order'          => $order,
		);

		switch ( strtolower( trim( (string) $args['orderby'] ) ) ) {
			case 'date':
				$query['orderby'] = 'date';
				break;
			case 'modified':
				$query['orderby'] = 'modified';
				break;
			case 'title':
				$query['orderby'] = 'title';
				break;
			case 'menu_order':
				$query['orderby'] = 'menu_order date';
				break;
			default:
				// Added to the site: the ID rises with every new post whatever
				// date the article itself carries.
				$query['orderby'] = 'ID';
				break;
		}

		if ( '' !== trim( (string) $args['category'] ) ) {
			$slug = sanitize_title( $args['category'] );

			if ( 'post' === $type ) {
				$query['category_name'] = $slug;
			} else {
				// Whichever category-like taxonomy that type actually has.
				$taxonomies = get_object_taxonomies( $type, 'names' );
				$taxonomy   = '';

				foreach ( $taxonomies as $candidate ) {
					if ( false !== strpos( $candidate, 'categor' ) ) {
						$taxonomy = $candidate;
						break;
					}
				}

				if ( '' === $taxonomy && $taxonomies ) {
					$taxonomy = reset( $taxonomies );
				}

				if ( '' !== $taxonomy ) {
					$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $slug,
						),
					);
				}
			}
		}

		$q = new WP_Query( $query );

		if ( ! $q->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		FC_Assets::enqueue();

		$posts = $q->posts;
		$first = array_shift( $posts );

		$classes = 'fc-topics';

		if ( '' !== $args['class'] ) {
			$classes .= ' ' . sanitize_html_class( $args['class'] );
		}

		$style = FC_Style::build( FC_Style::topics_schema(), $args );

		$out = sprintf( '<div class="%s" style="%s">', esc_attr( $classes ), esc_attr( $style ) );

		$heading = trim( (string) $args['heading'] );

		if ( '' !== $heading ) {
			$tag = in_array( $args['heading_tag'], array( 'h1', 'h2', 'h3', 'h4', 'div', 'p' ), true )
				? $args['heading_tag']
				: 'h2';

			$out .= '<' . $tag . ' class="fc-topics-heading">' . esc_html( $heading ) . '</' . $tag . '>';
		}

		$out .= '<div class="fc-topics-grid">';
		$out .= self::topic( $first, $args, 'featured' );

		$out .= '<div class="fc-topics-list">';

		foreach ( $posts as $post ) {
			$out .= self::topic( $post, $args, 'item' );
		}

		$out .= '</div></div></div>';

		wp_reset_postdata();

		return $out;
	}
}
