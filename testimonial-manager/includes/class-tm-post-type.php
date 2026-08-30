<?php
/**
 * Testimonial post type, taxonomy and admin list table.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Post_Type {

	const POST_TYPE = 'testimonial';
	const TAXONOMY  = 'testimonial_category';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'image_size' ) );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'admin_query' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'admin_filters' ) );

		add_filter( 'post_row_actions', array( __CLASS__, 'duplicate_link' ), 10, 2 );
		add_action( 'admin_post_tm_duplicate', array( __CLASS__, 'duplicate' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * A square crop for the circular avatar. 2x the 44px display size for retina.
	 */
	public static function image_size() {
		add_image_size( 'tm_avatar', 120, 120, true );
	}

	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'        => array(
					'name'               => __( 'Testimonials', 'testimonial-manager' ),
					'singular_name'      => __( 'Testimonial', 'testimonial-manager' ),
					'menu_name'          => __( 'Testimonials', 'testimonial-manager' ),
					'add_new'            => __( 'Add New', 'testimonial-manager' ),
					'add_new_item'       => __( 'Add New Testimonial', 'testimonial-manager' ),
					'edit_item'          => __( 'Edit Testimonial', 'testimonial-manager' ),
					'new_item'           => __( 'New Testimonial', 'testimonial-manager' ),
					'view_item'          => __( 'View Testimonial', 'testimonial-manager' ),
					'search_items'       => __( 'Search Testimonials', 'testimonial-manager' ),
					'not_found'          => __( 'No testimonials found', 'testimonial-manager' ),
					'not_found_in_trash' => __( 'No testimonials found in Trash', 'testimonial-manager' ),
					'all_items'          => __( 'All Testimonials', 'testimonial-manager' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_menu'  => true,
				'show_in_rest'  => true,
				'menu_position' => 26,
				'menu_icon'     => 'dashicons-format-quote',
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
				'has_archive'   => false,
				'rewrite'       => false,
				'capability_type' => 'post',
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Testimonial Categories', 'testimonial-manager' ),
					'singular_name' => __( 'Testimonial Category', 'testimonial-manager' ),
					'search_items'  => __( 'Search Categories', 'testimonial-manager' ),
					'all_items'     => __( 'All Categories', 'testimonial-manager' ),
					'edit_item'     => __( 'Edit Category', 'testimonial-manager' ),
					'add_new_item'  => __( 'Add New Category', 'testimonial-manager' ),
				),
				'hierarchical'      => true,
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => false,
			)
		);
	}

	public static function title_placeholder( $text, $post ) {
		return ( self::POST_TYPE === $post->post_type )
			? __( 'Client name', 'testimonial-manager' )
			: $text;
	}

	/* ---------------------------------------------------------------
	 * Admin list table
	 * ------------------------------------------------------------- */

	public static function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new[ $key ]      = __( 'Client Name', 'testimonial-manager' );
				$new['tm_rating'] = __( 'Rating', 'testimonial-manager' );
				continue;
			}
			if ( 'date' === $key ) {
				$new['tm_company']  = __( 'Company', 'testimonial-manager' );
				$new['tm_featured'] = __( 'Featured', 'testimonial-manager' );
				$new['tm_order']    = __( 'Order', 'testimonial-manager' );
				$new[ $key ]        = $label;
				continue;
			}
			$new[ $key ] = $label;
		}

		return $new;
	}

	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'tm_rating':
				$rating = TM_Meta_Fields::get_rating( $post_id );
				printf(
					'<span aria-hidden="true">%1$s</span><span class="screen-reader-text">%2$s</span>',
					esc_html( str_repeat( "\u{2605}", $rating ) . str_repeat( "\u{2606}", 5 - $rating ) ),
					esc_html(
						sprintf(
							/* translators: %d: star rating out of five. */
							__( 'Rated %d out of 5', 'testimonial-manager' ),
							$rating
						)
					)
				);
				break;

			case 'tm_company':
				echo esc_html( get_post_meta( $post_id, '_tm_company', true ) );
				break;

			case 'tm_featured':
				echo get_post_meta( $post_id, '_tm_featured', true )
					? '<span class="dashicons dashicons-star-filled" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Featured', 'testimonial-manager' ) . '</span>'
					: '&mdash;';
				break;

			case 'tm_order':
				echo (int) get_post_field( 'menu_order', $post_id );
				break;
		}
	}

	public static function sortable_columns( $columns ) {
		$columns['tm_rating'] = 'tm_rating';
		$columns['tm_order']  = 'menu_order';
		return $columns;
	}

	/**
	 * Apply the rating sort and the category / featured filters on the list screen.
	 */
	public static function admin_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( 'tm_rating' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_tm_rating' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filter.
		if ( isset( $_GET['tm_featured'] ) && '1' === $_GET['tm_featured'] ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => '_tm_featured',
						'value' => '1',
					),
				)
			);
		}
		// phpcs:enable
	}

	public static function admin_filters( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filter.
		$featured = isset( $_GET['tm_featured'] ) ? sanitize_key( wp_unslash( $_GET['tm_featured'] ) ) : '';
		?>
		<label class="screen-reader-text" for="tm_featured"><?php esc_html_e( 'Filter by featured', 'testimonial-manager' ); ?></label>
		<select name="tm_featured" id="tm_featured">
			<option value=""><?php esc_html_e( 'All testimonials', 'testimonial-manager' ); ?></option>
			<option value="1" <?php selected( $featured, '1' ); ?>><?php esc_html_e( 'Featured only', 'testimonial-manager' ); ?></option>
		</select>
		<?php
	}

	/* ---------------------------------------------------------------
	 * Duplicate
	 * ------------------------------------------------------------- */

	public static function duplicate_link( $actions, $post ) {
		if ( self::POST_TYPE !== $post->post_type || ! current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=tm_duplicate&post=' . $post->ID ),
			'tm_duplicate_' . $post->ID
		);

		$actions['tm_duplicate'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Duplicate', 'testimonial-manager' )
		);

		return $actions;
	}

	/**
	 * Copy a testimonial, its meta and its terms into a new draft.
	 */
	public static function duplicate() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		if ( ! $post_id
			|| ! isset( $_GET['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'tm_duplicate_' . $post_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'testimonial-manager' ) );
		}

		$original = get_post( $post_id );

		if ( ! $original || self::POST_TYPE !== $original->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to duplicate this testimonial.', 'testimonial-manager' ) );
		}

		$new_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'draft',
				/* translators: %s: original testimonial title. */
				'post_title'   => sprintf( __( '%s (copy)', 'testimonial-manager' ), $original->post_title ),
				'post_content' => $original->post_content,
				'post_excerpt' => $original->post_excerpt,
				'menu_order'   => $original->menu_order,
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		foreach ( TM_Meta_Fields::meta_keys() as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( '' !== $value ) {
				update_post_meta( $new_id, $key, $value );
			}
		}

		$thumb = get_post_thumbnail_id( $post_id );
		if ( $thumb ) {
			set_post_thumbnail( $new_id, $thumb );
		}

		$terms = wp_get_object_terms( $post_id, self::TAXONOMY, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) && $terms ) {
			wp_set_object_terms( $new_id, $terms, self::TAXONOMY );
		}

		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
	}
}
