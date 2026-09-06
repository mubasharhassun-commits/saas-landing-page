<?php
/**
 * All front-end markup for WL Cards and WL News.
 *
 * The shortcodes and the WPBakery elements both call these methods, so their
 * output can never drift apart.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WL_Render {

	/*
	 * The Cases cards are drawn entirely by two overlay artworks - a soft
	 * gradient at rest and a dark wash carrying the Read More button on hover -
	 * so no tint or opacity is involved. These are the ones the site uses.
	 *
	 * Resolved in this order, so the plugin travels between sites cleanly:
	 *   1. an image chosen on the element itself
	 *   2. a copy shipped in the plugin at assets/img/
	 *   3. the uploaded URL below
	 * Drop cases-overlay.png / cases-overlay-hover.png into assets/img/ and the
	 * plugin stops depending on the media library entirely. The
	 * "wl_cases_overlay_images" filter overrides all of it.
	 */
	const CASES_OVERLAY       = 'https://waterlawvadev.wpenginepowered.com/wp-content/uploads/2026/09/Cover.png';
	const CASES_OVERLAY_HOVER = 'https://waterlawvadev.wpenginepowered.com/wp-content/uploads/2026/09/Hover.png';

	/**
	 * The two default Cases overlays, as URLs.
	 */
	public static function cases_overlays() {
		$bundled = array(
			'overlay_image'       => 'cases-overlay.png',
			'hover_overlay_image' => 'cases-overlay-hover.png',
		);

		$out = array(
			'overlay_image'       => self::CASES_OVERLAY,
			'hover_overlay_image' => self::CASES_OVERLAY_HOVER,
		);

		foreach ( $bundled as $key => $file ) {
			if ( defined( 'WL_PATH' ) && file_exists( WL_PATH . 'assets/img/' . $file ) ) {
				$out[ $key ] = WL_URL . 'assets/img/' . $file;
			}
		}

		return apply_filters( 'wl_cases_overlay_images', $out );
	}

	/* =============================================================
	 * WL CARDS
	 * ============================================================= */

	public static function cards_defaults() {
		return array(
			'source'         => 'vessels',
			'count'          => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'layout'         => 'grid',
			'columns'        => 4,
			'columns_laptop' => 0,
			'columns_tablet' => 2,
			'columns_mobile' => 1,
			'link_cards'     => 'yes',
			'title_source'   => 'post',
			'titles'         => '',
			'links'          => '',
			'show_button'    => 'no',
			'button_text'    => 'READ MORE',
			'hide_title_hover' => 'yes',
			'custom_size'    => 'no',
			'pause_hover'    => 'yes',
			'autoscroll'     => 'yes',
			'overlay_color'  => 'yes',
			'overlay_stack'  => 'no',
			'class'          => '',
			'items'          => '',
		);
	}

	public static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Render the cards grid or continuous slider. Returns HTML; never echoes.
	 */
	public static function cards( $args ) {
		$args = wp_parse_args( $args, self::cards_defaults() );

		$source = in_array( $args['source'], array( 'vessels', 'cases', 'manual' ), true ) ? $args['source'] : 'vessels';
		$layout = ( 'carousel' === $args['layout'] ) ? 'carousel' : 'grid';
		$cols   = max( 1, min( 12, (int) $args['columns'] ) );
		$cols_l = max( 0, min( 12, (int) $args['columns_laptop'] ) );
		$cols_l = ( $cols_l < 1 ) ? $cols : $cols_l;
		$cols_t = max( 1, min( 12, (int) $args['columns_tablet'] ) );
		$cols_m = max( 1, min( 12, (int) $args['columns_mobile'] ) );

		$linked = self::to_bool( $args['link_cards'] );
		$btn    = self::to_bool( $args['show_button'] ) ? sanitize_text_field( $args['button_text'] ) : '';
		// Hiding the title on hover only makes sense when the button takes its
		// place; with no button the card would just go blank.
		$hidet  = ( '' !== $btn && self::to_bool( $args['hide_title_hover'] ) ) ? ' wl-hide-title' : '';
		$fixed  = self::to_bool( $args['custom_size'] ) ? ' wl-fixed' : '';
		$pause  = self::to_bool( $args['pause_hover'] ) ? 'paused' : 'running';

		WL_Assets::enqueue();

		$cards = ( 'manual' === $source )
			? self::manual_cards( $args, $btn, $linked )
			: self::post_cards( $args, $source, $btn, $linked );

		if ( '' === $cards ) {
			return '';
		}

		$style = sprintf(
			'--wl-cols:%d;--wl-cols-l:%d;--wl-cols-t:%d;--wl-cols-m:%d;--wl-pause:%s;',
			$cols,
			$cols_l,
			$cols_t,
			$cols_m,
			$pause
		);

		/*
		 * Cases cards are artwork-only: the tinted layers and their opacities
		 * are dropped so the two overlay images render exactly as supplied,
		 * whatever an older saved element still carries.
		 */
		if ( 'cases' === $source ) {
			$defaults = self::cases_overlays();

			foreach ( $defaults as $key => $url ) {
				if ( empty( $args[ $key ] ) ) {
					$args[ $key ] = $url;
				}
			}

			foreach ( array( 'overlay', 'hover_overlay', 'overlay_opacity', 'hover_overlay_opacity' ) as $key ) {
				$args[ $key ] = '';
			}

			$args['overlay_color'] = 'no';
		}

		// The image pickers store attachment IDs; the style layer needs URLs.
		foreach ( array( 'overlay_image', 'hover_overlay_image' ) as $key ) {
			if ( ! empty( $args[ $key ] ) && is_numeric( $args[ $key ] ) ) {
				$url           = wp_get_attachment_image_url( absint( $args[ $key ] ), 'large' );
				$args[ $key ] = $url ? $url : '';
			}
		}

		$style .= WL_Style::build( WL_Style::cards_schema(), $args );

		// Images-only overlays: the tinted layers come off entirely.
		$nocolor = self::to_bool( $args['overlay_color'] ) ? '' : ' wl-no-ov-color';

		// Stacked rather than crossfaded overlays.
		$stack = self::to_bool( $args['overlay_stack'] ) ? ' wl-ov-stack' : '';

		$classes = 'wl-cards ' . $layout . $hidet . $fixed . $nocolor . $stack;
		if ( '' !== $args['class'] ) {
			$classes .= ' ' . sanitize_html_class( $args['class'] );
		}

		$out = sprintf( '<div class="%s" style="%s">', esc_attr( $classes ), esc_attr( $style ) );

		if ( 'carousel' === $layout ) {
			if ( self::to_bool( $args['autoscroll'] ) ) {
				// The set is duplicated so the marquee loops seamlessly.
				$out .= '<div class="wl-marquee-wrap"><div class="wl-marquee">' . $cards . $cards . '</div></div>';
			} else {
				// Standing still, a second copy would only be dead weight.
				$out .= '<div class="wl-marquee-wrap"><div class="wl-marquee wl-static">' . $cards . '</div></div>';
			}
		} else {
			$out .= '<div class="wl-grid">' . $cards . '</div>';
		}

		return $out . '</div>';
	}

	/**
	 * One card. An empty $href renders a non-clickable card.
	 */
	private static function card_html( $img, $text, $href, $btn, $attrs = '' ) {
		$open  = ( '' !== $href )
			? '<a class="wl-card" href="' . esc_url( $href ) . '"' . $attrs . '>'
			: '<div class="wl-card wl-card-static">';
		$close = ( '' !== $href ) ? '</a>' : '</div>';

		$out  = $open;
		$out .= '<div class="wl-card-img" style="background-image:url(\'' . esc_url( $img ) . '\');">';
		$out .= '<div class="wl-card-ov wl-ov-normal"></div>';
		$out .= '<div class="wl-card-ov wl-ov-normal-img"></div>';
		$out .= '<div class="wl-card-ov wl-ov-hover"></div>';
		$out .= '<div class="wl-card-ov wl-ov-hover-img"></div>';

		if ( '' !== $btn ) {
			$out .= '<span class="wl-card-btn">' . esc_html( $btn ) . '</span>';
		}
		if ( '' !== $text ) {
			$out .= '<h3 class="wl-card-title">' . esc_html( $text ) . '</h3>';
		}

		return $out . '</div>' . $close;
	}

	/**
	 * Cards pulled from the Vessels / Cases post types.
	 */
	private static function post_cards( $args, $type, $btn, $linked ) {
		$titles  = self::lines( isset( $args['titles'] ) ? $args['titles'] : '' );
		$links   = self::lines( isset( $args['links'] ) ? $args['links'] : '' );
		$source  = in_array( $args['title_source'], array( 'custom', 'none' ), true ) ? $args['title_source'] : 'post';
		$index   = 0;
		$orderby = in_array( $args['orderby'], array( 'menu_order', 'date', 'title', 'rand' ), true )
			? $args['orderby']
			: 'menu_order';

		$q = new WP_Query(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => (int) $args['count'],
				'orderby'        => ( 'menu_order' === $orderby ) ? 'menu_order date' : $orderby,
				'order'          => ( 'DESC' === strtoupper( (string) $args['order'] ) ) ? 'DESC' : 'ASC',
				'no_found_rows'  => true,
			)
		);

		if ( ! $q->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		$out = '';

		while ( $q->have_posts() ) {
			$q->the_post();
			$id   = get_the_ID();
			$text = waterslaw_get_card_text( $id );

			if ( 'none' === $source ) {
				$text = '';
			} elseif ( 'custom' === $source && isset( $titles[ $index ] ) && '' !== $titles[ $index ] ) {
				$text = $titles[ $index ];
			}

			$href = '';

			if ( $linked ) {
				// A URL typed into the builder wins; otherwise the post's own
				// Card Link field is used.
				$href = ( isset( $links[ $index ] ) && '' !== $links[ $index ] )
					? esc_url_raw( $links[ $index ] )
					: waterslaw_get_card_link( $id );
			}

			$out .= self::card_html(
				(string) get_the_post_thumbnail_url( $id, 'large' ),
				$text,
				$href,
				$btn
			);

			$index++;
		}

		wp_reset_postdata();

		return $out;
	}

	/**
	 * A textarea typed into the builder, split into one value per card.
	 *
	 * A blank line means "leave this card alone", so a single heading or link
	 * can be set without retyping the rest.
	 */
	private static function lines( $raw ) {
		$raw = (string) $raw;

		if ( '' === trim( $raw ) ) {
			return array();
		}

		$out = array();

		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$out[] = sanitize_text_field( trim( $line ) );
		}

		return $out;
	}

	/**
	 * Cards built by hand in the builder, from WPBakery's param_group.
	 *
	 * The repeater arrives as a base64-encoded, url-encoded JSON string.
	 */
	private static function manual_cards( $args, $btn, $linked ) {
		$items = self::decode_param_group( $args['items'] );
		$out   = '';

		foreach ( $items as $item ) {
			$img = '';

			if ( ! empty( $item['item_image'] ) ) {
				$url = is_numeric( $item['item_image'] )
					? wp_get_attachment_image_url( absint( $item['item_image'] ), 'large' )
					: esc_url_raw( $item['item_image'] );
				$img = $url ? $url : '';
			}

			$text = isset( $item['item_text'] ) ? sanitize_text_field( $item['item_text'] ) : '';
			$href = '';
			$attrs = '';

			if ( $linked && ! empty( $item['item_link'] ) ) {
				$link = self::parse_vc_link( $item['item_link'] );
				$href = $link['url'];

				if ( '' !== $href && '_blank' === $link['target'] ) {
					$attrs = ' target="_blank" rel="noopener"';
				}
			}

			$out .= self::card_html( $img, $text, $href, $btn, $attrs );
		}

		return $out;
	}

	/**
	 * WPBakery param_group values: base64 of a url-encoded JSON array.
	 */
	public static function decode_param_group( $raw ) {
		if ( is_array( $raw ) ) {
			return $raw;
		}
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return array();
		}

		$decoded = base64_decode( $raw, true );

		if ( false === $decoded ) {
			return array();
		}

		$json = json_decode( rawurldecode( $decoded ), true );

		return is_array( $json ) ? $json : array();
	}

	/**
	 * WPBakery vc_link values: "url:https%3A%2F%2F...|title:X|target:_blank".
	 */
	public static function parse_vc_link( $raw ) {
		$out = array(
			'url'    => '',
			'target' => '',
		);

		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return $out;
		}

		if ( function_exists( 'vc_build_link' ) ) {
			$link          = vc_build_link( $raw );
			$out['url']    = isset( $link['url'] ) ? esc_url_raw( rawurldecode( $link['url'] ) ) : '';
			$out['target'] = ( isset( $link['target'] ) && false !== strpos( $link['target'], '_blank' ) ) ? '_blank' : '';

			return self::clean_link( $out );
		}

		// Same format, parsed directly, so the shortcode works without WPBakery.
		foreach ( explode( '|', $raw ) as $pair ) {
			$bits = explode( ':', $pair, 2 );

			if ( 2 !== count( $bits ) ) {
				continue;
			}

			if ( 'url' === $bits[0] ) {
				$out['url'] = esc_url_raw( rawurldecode( $bits[1] ) );
			} elseif ( 'target' === $bits[0] && false !== strpos( $bits[1], '_blank' ) ) {
				$out['target'] = '_blank';
			}
		}

		return self::clean_link( $out );
	}

	/**
	 * A rejected URL must not carry a target with it. Nothing currently acts on
	 * a target without a URL, but returning one invites a caller to.
	 *
	 * @param array $link Parsed link parts.
	 * @return array
	 */
	protected static function clean_link( $link ) {
		if ( '' === $link['url'] ) {
			$link['target'] = '';
		}

		return $link;
	}

	/* =============================================================
	 * WL NEWS
	 * ============================================================= */

	public static function news_defaults() {
		return array(
			'source'        => 'news',
			'category'      => '',
			'count'         => 3,
			'show_date'     => 'yes',
			'show_category' => 'yes',
			'show_author'   => 'yes',
			'meta_wrap'     => '',
			'button_text'   => 'READ MORE',
			'class'         => '',
		);
	}

	/**
	 * Featured post on the left, a list on the right. Returns HTML.
	 */
	public static function news( $args ) {
		$args = wp_parse_args( $args, self::news_defaults() );

		$type  = ( 'post' === $args['source'] ) ? 'post' : 'news';
		$btn   = sanitize_text_field( $args['button_text'] );
		$btn   = ( '' === $btn ) ? __( 'READ MORE', 'waterslaw' ) : $btn;
		$count = max( 2, min( 10, (int) $args['count'] ) );

		$query = array(
			'post_type'      => $type,
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'no_found_rows'  => true,
		);

		if ( '' !== trim( (string) $args['category'] ) ) {
			$slug = sanitize_title( $args['category'] );

			if ( 'news' === $type ) {
				$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'news_category',
						'field'    => 'slug',
						'terms'    => $slug,
					),
				);
			} else {
				$query['category_name'] = $slug;
			}
		}

		$q = new WP_Query( $query );

		if ( ! $q->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		WL_Assets::enqueue();

		$posts = $q->posts;
		$first = array_shift( $posts );

		$style   = WL_Style::build( WL_Style::news_schema(), $args );
		$classes = 'wl-news';

		// Keeping the meta on one line takes more than flex-wrap: the text
		// inside each item has to stop wrapping too.
		if ( isset( $args['meta_wrap'] ) && 'nowrap' === strtolower( trim( (string) $args['meta_wrap'] ) ) ) {
			$classes .= ' wl-meta-1line';
		}

		if ( '' !== $args['class'] ) {
			$classes .= ' ' . sanitize_html_class( $args['class'] );
		}

		$out = sprintf( '<div class="%s" style="%s">', esc_attr( $classes ), esc_attr( $style ) );

		$fimg = get_the_post_thumbnail_url( $first->ID, 'large' );

		$out .= '<a class="wl-news-featured" href="' . esc_url( get_permalink( $first->ID ) ) . '"'
			. ' style="background-image:url(\'' . esc_url( (string) $fimg ) . '\');">';
		$out .= '<div class="wl-news-featured-overlay"></div>';
		$out .= '<div class="wl-news-featured-body">';
		$out .= '<h3 class="wl-news-featured-title">' . esc_html( get_the_title( $first->ID ) ) . '</h3>';
		$out .= '<div class="wl-news-meta light">' . self::meta_html( $first, $args ) . '</div>';
		$out .= '</div></a>';

		$out .= '<div class="wl-news-list">';

		foreach ( $posts as $post ) {
			$timg = get_the_post_thumbnail_url( $post->ID, 'large' );
			$link = get_permalink( $post->ID );

			$out .= '<div class="wl-news-item">';
			$out .= '<a class="wl-news-thumb" href="' . esc_url( $link ) . '"'
				. ' style="background-image:url(\'' . esc_url( (string) $timg ) . '\');"></a>';
			$out .= '<div class="wl-news-body">';
			$out .= '<a class="wl-news-title" href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a>';
			$out .= '<div class="wl-news-meta">' . self::meta_html( $post, $args ) . '</div>';
			$out .= '<a class="wl-news-readmore" href="' . esc_url( $link ) . '">' . esc_html( $btn ) . '</a>';
			$out .= '</div></div>';
		}

		$out .= '</div></div>';

		wp_reset_postdata();

		return $out;
	}

	/**
	 * Date / category / author, each with an inline icon.
	 */
	private static function meta_html( $post, $args ) {
		$out = '';

		if ( self::to_bool( $args['show_date'] ) ) {
			$out .= '<span class="wl-m">'
				. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>'
				. esc_html( get_the_date( '', $post ) ) . '</span>';
		}

		if ( self::to_bool( $args['show_category'] ) ) {
			$term_name = '';

			foreach ( array( 'news_category', 'category' ) as $taxonomy ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$term_name = $terms[0]->name;
					break;
				}
			}

			if ( '' !== $term_name ) {
				$out .= '<span class="wl-m">'
					. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 7h6l2 2h10v9H3z"/></svg>'
					. esc_html__( 'Category:', 'waterslaw' ) . ' ' . esc_html( $term_name ) . '</span>';
			}
		}

		if ( self::to_bool( $args['show_author'] ) ) {
			$out .= '<span class="wl-m">'
				. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>'
				. esc_html__( 'Author:', 'waterslaw' ) . ' '
				. esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . '</span>';
		}

		return $out;
	}
}
