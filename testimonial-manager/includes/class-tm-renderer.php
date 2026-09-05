<?php
/**
 * All front-end markup lives here.
 *
 * The shortcode and the WPBakery elements all call
 * TM_Renderer::grid(), so the output can never drift between them.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Renderer {

	/**
	 * Set once a grid has rendered, so the shared modal shell is printed only
	 * when a page actually needs it.
	 *
	 * @var bool
	 */
	private static $needs_modal = false;

	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'modal_shell' ), 20 );
	}

	/**
	 * Render a grid. Returns HTML; never echoes.
	 */
	public static function grid( $args = array() ) {
		$args  = TM_Query::normalize( $args );
		$query = TM_Query::get( $args );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		TM_Assets::enqueue();
		self::$needs_modal = true;

		$style = sprintf(
			'--tm-cols:%d;--tm-cols-l:%d;--tm-cols-t:%d;--tm-cols-m:%d;--tm-gap:%dpx;',
			$args['columns'],
			$args['columns_laptop'],
			$args['columns_tablet'],
			$args['columns_mobile'],
			$args['gap']
		);

		// Style parameters become custom properties on the element itself.
		$style .= TM_Style::build( TM_Style::grid_schema(), $args );

		$classes = 'tm-grid';

		if ( ! empty( $args['quote_icon'] ) ) {
			$classes .= ' tm-show-quote';
		}
		if ( '' !== $args['class'] ) {
			$classes .= ' ' . $args['class'];
		}

		$out = sprintf(
			'<div class="%1$s" style="%2$s">',
			esc_attr( $classes ),
			esc_attr( $style )
		);

		while ( $query->have_posts() ) {
			$query->the_post();
			$out .= self::card( get_post(), $args );
		}

		wp_reset_postdata();

		$out .= '</div>';

		return $out;
	}

	/**
	 * Render a slider. Returns HTML; never echoes.
	 *
	 * Slides are stacked in a single grid cell so the track is always as tall as
	 * the longest testimonial and nothing jumps as slides change.
	 */
	public static function slider( $args = array() ) {
		$args  = TM_Query::normalize( $args );
		$query = TM_Query::get( $args );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		TM_Assets::enqueue();
		TM_Assets::enqueue_slider();

		$slides = array();

		while ( $query->have_posts() ) {
			$query->the_post();
			$slides[] = get_post();
		}
		wp_reset_postdata();

		$total = count( $slides );
		$uid   = wp_unique_id( 'tm-slider-' );

		$classes = 'tm-slider tm-effect-' . $args['effect'];

		if ( ! empty( $args['quote_icon'] ) ) {
			$classes .= ' tm-show-quote';
		}

		// Lets the stylesheet reserve room so arrows never sit over the text.
		if ( $args['arrows'] && count( $slides ) > 1 ) {
			$classes .= ' tm-has-arrows';
		}
		if ( '' !== $args['class'] ) {
			$classes .= ' ' . $args['class'];
		}

		$out = sprintf(
			'<div class="%1$s" id="%2$s" style="%3$s" data-autoplay="%4$d" data-pause-hover="%5$s">',
			esc_attr( $classes ),
			esc_attr( $uid ),
			esc_attr( TM_Style::build( TM_Style::slider_schema(), $args ) ),
			(int) $args['autoplay'],
			$args['pause_hover'] ? '1' : '0'
		);

		$out .= sprintf(
			'<div class="tm-slider-viewport" aria-roledescription="carousel" aria-label="%s">',
			esc_attr__( 'Client testimonials', 'testimonial-manager' )
		);
		$out .= '<div class="tm-slides">';

		foreach ( $slides as $i => $post ) {
			$out .= self::slide( $post, $args, $i, $total );
		}

		$out .= '</div></div>';

		if ( $args['arrows'] && $total > 1 ) {
			$out .= sprintf(
				'<button type="button" class="tm-slider-nav tm-slider-prev" data-tm-prev aria-controls="%1$s" aria-label="%2$s">%3$s</button>',
				esc_attr( $uid ),
				esc_attr__( 'Previous testimonial', 'testimonial-manager' ),
				self::chevron( 'left' )
			);
			$out .= sprintf(
				'<button type="button" class="tm-slider-nav tm-slider-next" data-tm-next aria-controls="%1$s" aria-label="%2$s">%3$s</button>',
				esc_attr( $uid ),
				esc_attr__( 'Next testimonial', 'testimonial-manager' ),
				self::chevron( 'right' )
			);
		}

		if ( $args['dots'] && $total > 1 ) {
			$out .= '<div class="tm-slider-dots">';
			for ( $i = 0; $i < $total; $i++ ) {
				$out .= sprintf(
					'<button type="button" class="tm-slider-dot%1$s" data-tm-goto="%2$d" aria-label="%3$s"%4$s></button>',
					0 === $i ? ' is-active' : '',
					$i,
					esc_attr(
						sprintf(
							/* translators: 1: slide number, 2: total slides. */
							__( 'Show testimonial %1$d of %2$d', 'testimonial-manager' ),
							$i + 1,
							$total
						)
					),
					0 === $i ? ' aria-current="true"' : ''
				);
			}
			$out .= '</div>';
		}

		$out .= '</div>';

		return $out;
	}

	/**
	 * One slide. Only the first is exposed to assistive tech until the script
	 * takes over, so a no-JS reader is not handed every testimonial at once.
	 */
	private static function slide( $post, $args, $index, $total ) {
		$meta = TM_Meta_Fields::get( $post->ID );
		$name = get_the_title( $post );

		$text = $args['full_text']
			? wp_strip_all_tags( strip_shortcodes( $post->post_content ) )
			: self::short_text( $post, $args['excerpt_words'] );

		$out = sprintf(
			'<div class="tm-slide%1$s" role="group" aria-roledescription="%2$s" aria-label="%3$s"%4$s>',
			0 === $index ? ' is-active' : '',
			esc_attr__( 'slide', 'testimonial-manager' ),
			esc_attr(
				sprintf(
					/* translators: 1: slide number, 2: total slides. */
					__( '%1$d of %2$d', 'testimonial-manager' ),
					$index + 1,
					$total
				)
			),
			0 === $index ? '' : ' aria-hidden="true"'
		);

		if ( $args['show_rating'] ) {
			$out .= self::stars( $meta['rating'] );
		}

		$out .= '<blockquote class="tm-slide-quote"><p>' . esc_html( $text ) . '</p></blockquote>';

		$out .= '<div class="tm-person">';
		if ( $args['show_image'] ) {
			$out .= self::avatar( $post, $name, $args );
		}
		$out .= '<div class="tm-person-text"><span class="tm-name">' . esc_html( $name ) . '</span>';

		$sub = self::subtitle( $meta, $args );
		if ( '' !== $sub ) {
			$out .= '<span class="tm-role">' . esc_html( $sub ) . '</span>';
		}
		$out .= '</div></div></div>';

		return $out;
	}

	private static function chevron( $dir ) {
		$path = ( 'left' === $dir ) ? 'M15 5l-7 7 7 7' : 'M9 5l7 7-7 7';

		return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
			. '<path d="' . $path . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * A single testimonial card, plus the inert template holding its full text.
	 */
	private static function card( $post, $args ) {
		$meta  = TM_Meta_Fields::get( $post->ID );
		$uid   = wp_unique_id( 'tm-full-' );
		$name  = get_the_title( $post );
		$short = self::short_text( $post, $args['excerpt_words'] );
		$tag   = $args['title_tag'];

		$out = '<article class="tm-card">';

		if ( $args['show_rating'] ) {
			$out .= self::stars( $meta['rating'] );
		}

		$out .= '<div class="tm-quote"><blockquote class="tm-excerpt"><p>' . esc_html( $short ) . '</p></blockquote></div>';

		$out .= '<div class="tm-person">';
		if ( $args['show_image'] ) {
			$out .= self::avatar( $post, $name, $args );
		}
		$out .= '<div class="tm-person-text">';
		$out .= '<' . $tag . ' class="tm-name">' . esc_html( $name ) . '</' . $tag . '>';

		$sub = self::subtitle( $meta, $args );
		if ( '' !== $sub ) {
			$out .= '<span class="tm-role">' . esc_html( $sub ) . '</span>';
		}
		$out .= '</div></div>';

		if ( $args['show_button'] ) {
			$out .= sprintf(
				'<button type="button" class="tm-btn" data-tm-open="%1$s" aria-haspopup="dialog">%2$s</button>',
				esc_attr( $uid ),
				esc_html( $args['button_text'] )
			);

			// Inert until the modal clones it. Never rendered by the browser.
			$out .= '<template id="' . esc_attr( $uid ) . '">' . self::modal_content( $post, $meta, $name, $args ) . '</template>';
		}

		$out .= '</article>';

		return $out;
	}

	/**
	 * The body the modal shows. The full testimonial is never truncated here.
	 */
	private static function modal_content( $post, $meta, $name, $args ) {
		$out = '';

		if ( $args['show_rating'] ) {
			$out .= self::stars( $meta['rating'] );
		}

		$full = wpautop( wp_kses_post( $post->post_content ) );
		$out .= '<div class="tm-modal-quote">' . $full . '</div>';

		$out .= '<div class="tm-person">';
		if ( $args['show_image'] ) {
			$out .= self::avatar( $post, $name, $args );
		}
		$out .= '<div class="tm-person-text"><span class="tm-name">' . esc_html( $name ) . '</span>';

		$sub = self::subtitle( $meta, $args );
		if ( '' !== $sub ) {
			$out .= '<span class="tm-role">' . esc_html( $sub ) . '</span>';
		}
		$out .= '</div></div>';

		return $out;
	}

	/**
	 * "Position, Company" - whichever parts are enabled and present.
	 */
	private static function subtitle( $meta, $args ) {
		$parts = array();

		if ( $args['show_position'] && '' !== $meta['position'] ) {
			$parts[] = $meta['position'];
		}
		if ( $args['show_company'] && '' !== $meta['company'] ) {
			$parts[] = $meta['company'];
		}

		return implode( ', ', $parts );
	}

	/**
	 * The manual excerpt if there is one, otherwise a word-safe trim of the full
	 * testimonial. wp_trim_words() never cuts a word in half.
	 */
	private static function short_text( $post, $words ) {
		if ( has_excerpt( $post ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post ) );
		}

		$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );

		return wp_trim_words( $content, $words, '...' );
	}

	/**
	 * Star rating. The glyphs are hidden from assistive tech and replaced by a
	 * single readable label on the wrapper.
	 */
	private static function stars( $rating ) {
		$rating = max( 1, min( 5, (int) $rating ) );

		$label = sprintf(
			/* translators: %d: star rating out of five. */
			__( 'Rated %d out of 5 stars', 'testimonial-manager' ),
			$rating
		);

		$out = '<div class="tm-stars" role="img" aria-label="' . esc_attr( $label ) . '">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$class = ( $i <= $rating ) ? 'tm-star is-on' : 'tm-star';
			$out  .= '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
				. '<path d="M12 2.3l2.9 6.1 6.6.9-4.8 4.6 1.2 6.6L12 17.4 6.1 20.5l1.2-6.6L2.5 9.3l6.6-.9z"/></svg>';
		}

		return $out . '</div>';
	}

	/**
	 * Circular client image, in priority order:
	 *   1. the Client Image chosen on the testimonial itself
	 *   2. its Featured image
	 *   3. the widget's Default Client Image
	 *   4. a lettered circle, so the layout always holds
	 */
	private static function avatar( $post, $name, $args = array() ) {
		$image_id = (int) get_post_meta( $post->ID, '_tm_image_id', true );

		if ( $image_id ) {
			$html = wp_get_attachment_image(
				$image_id,
				'tm_avatar',
				false,
				array(
					'class'   => 'tm-avatar',
					'alt'     => '',
					'loading' => 'lazy',
				)
			);

			// Empty when the attachment has since been deleted; fall through.
			if ( $html ) {
				return $html;
			}
		}

		if ( has_post_thumbnail( $post ) ) {
			return get_the_post_thumbnail(
				$post,
				'tm_avatar',
				array(
					'class'   => 'tm-avatar',
					'loading' => 'lazy',
					'alt'     => '',
				)
			);
		}

		if ( ! empty( $args['fallback_image'] ) ) {
			return sprintf(
				'<img class="tm-avatar" src="%s" alt="" loading="lazy" />',
				esc_url( $args['fallback_image'] )
			);
		}

		$name = trim( wp_strip_all_tags( $name ) );

		if ( function_exists( 'mb_substr' ) ) {
			$initial = mb_strtoupper( mb_substr( $name, 0, 1 ) );
		} else {
			$initial = strtoupper( substr( $name, 0, 1 ) );
		}

		return '<span class="tm-avatar tm-avatar-fallback" aria-hidden="true">' . esc_html( $initial ) . '</span>';
	}

	/**
	 * One empty modal per page, reused by every card and every grid.
	 */
	public static function modal_shell() {
		if ( ! self::$needs_modal ) {
			return;
		}
		?>
		<div class="tm-modal" id="tm-modal" hidden>
			<div class="tm-modal-overlay" data-tm-close></div>
			<div class="tm-modal-dialog" role="dialog" aria-modal="true"
			     aria-label="<?php esc_attr_e( 'Client testimonial', 'testimonial-manager' ); ?>" tabindex="-1">
				<button type="button" class="tm-modal-close" data-tm-close
				        aria-label="<?php esc_attr_e( 'Close testimonial', 'testimonial-manager' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"/>
					</svg>
				</button>
				<div class="tm-modal-body"></div>
			</div>
		</div>
		<?php
	}
}
