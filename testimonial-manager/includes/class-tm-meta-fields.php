<?php
/**
 * Custom fields for a testimonial.
 *
 * Client name, full text, short text, image and display order all live in native
 * WordPress fields (title, content, excerpt, featured image, menu_order), so only
 * the genuinely custom values are stored as post meta here.
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TM_Meta_Fields {

	const NONCE = 'tm_meta_nonce';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . TM_Post_Type::POST_TYPE, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	/**
	 * Meta key => sanitize callback.
	 */
	public static function schema() {
		return array(
			'_tm_rating'   => array( __CLASS__, 'sanitize_rating' ),
			'_tm_position' => 'sanitize_text_field',
			'_tm_company'  => 'sanitize_text_field',
			'_tm_source'   => 'sanitize_text_field',
			'_tm_date'     => array( __CLASS__, 'sanitize_date' ),
			'_tm_featured' => array( __CLASS__, 'sanitize_bool' ),
		);
	}

	public static function meta_keys() {
		return array_keys( self::schema() );
	}

	public static function sanitize_rating( $value ) {
		return min( 5, max( 1, absint( $value ) ) );
	}

	public static function sanitize_date( $value ) {
		$value = sanitize_text_field( $value );
		$date  = DateTime::createFromFormat( 'Y-m-d', $value );
		return ( $date && $date->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	public static function sanitize_bool( $value ) {
		return $value ? '1' : '';
	}

	public static function register_meta() {
		foreach ( self::schema() as $key => $sanitize ) {
			register_post_meta(
				TM_Post_Type::POST_TYPE,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $sanitize,
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}

	public static function get_rating( $post_id ) {
		$rating = get_post_meta( $post_id, '_tm_rating', true );
		return ( '' === $rating ) ? 5 : self::sanitize_rating( $rating );
	}

	/**
	 * Every custom field a template needs, normalised.
	 */
	public static function get( $post_id ) {
		return array(
			'rating'   => self::get_rating( $post_id ),
			'position' => (string) get_post_meta( $post_id, '_tm_position', true ),
			'company'  => (string) get_post_meta( $post_id, '_tm_company', true ),
			'source'   => (string) get_post_meta( $post_id, '_tm_source', true ),
			'date'     => (string) get_post_meta( $post_id, '_tm_date', true ),
			'featured' => (bool) get_post_meta( $post_id, '_tm_featured', true ),
		);
	}

	public static function add_meta_box() {
		add_meta_box(
			'tm_details',
			__( 'Testimonial Details', 'testimonial-manager' ),
			array( __CLASS__, 'render_meta_box' ),
			TM_Post_Type::POST_TYPE,
			'side',
			'high'
		);
	}

	public static function admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		if ( get_post_type() !== TM_Post_Type::POST_TYPE ) {
			return;
		}
		wp_enqueue_style( 'tm-admin', TM_URL . 'admin/css/testimonial-manager-admin.css', array(), TM_VERSION );
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'tm_save_meta', self::NONCE );
		$data = self::get( $post->ID );
		?>
		<fieldset class="tm-field tm-rating-field">
			<legend><strong><?php esc_html_e( 'Rating', 'testimonial-manager' ); ?></strong></legend>
			<div class="tm-stars-picker">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<input type="radio" id="tm_rating_<?php echo (int) $i; ?>" name="tm_rating"
					       value="<?php echo (int) $i; ?>" <?php checked( $data['rating'], $i ); ?> />
					<label for="tm_rating_<?php echo (int) $i; ?>">
						<span aria-hidden="true">&#9733;</span>
						<span class="screen-reader-text">
							<?php
							printf(
								/* translators: %d: number of stars. */
								esc_html( _n( '%d star', '%d stars', $i, 'testimonial-manager' ) ),
								(int) $i
							);
							?>
						</span>
					</label>
				<?php endfor; ?>
			</div>
		</fieldset>

		<p class="tm-field">
			<label for="tm_position"><strong><?php esc_html_e( 'Client Position / Job Title', 'testimonial-manager' ); ?></strong></label>
			<input type="text" id="tm_position" name="tm_position" class="widefat"
			       value="<?php echo esc_attr( $data['position'] ); ?>" />
		</p>

		<p class="tm-field">
			<label for="tm_company"><strong><?php esc_html_e( 'Company Name', 'testimonial-manager' ); ?></strong></label>
			<input type="text" id="tm_company" name="tm_company" class="widefat"
			       value="<?php echo esc_attr( $data['company'] ); ?>" />
		</p>

		<p class="tm-field">
			<label for="tm_source"><strong><?php esc_html_e( 'Source', 'testimonial-manager' ); ?></strong></label>
			<input type="text" id="tm_source" name="tm_source" class="widefat"
			       value="<?php echo esc_attr( $data['source'] ); ?>"
			       placeholder="<?php esc_attr_e( 'Google, Facebook, Avvo', 'testimonial-manager' ); ?>" />
		</p>

		<p class="tm-field">
			<label for="tm_date"><strong><?php esc_html_e( 'Testimonial Date', 'testimonial-manager' ); ?></strong></label>
			<input type="date" id="tm_date" name="tm_date" class="widefat"
			       value="<?php echo esc_attr( $data['date'] ); ?>" />
		</p>

		<p class="tm-field">
			<label for="tm_featured">
				<input type="checkbox" id="tm_featured" name="tm_featured" value="1" <?php checked( $data['featured'] ); ?> />
				<strong><?php esc_html_e( 'Featured testimonial', 'testimonial-manager' ); ?></strong>
			</label>
		</p>

		<p class="description">
			<?php esc_html_e( 'Client name is the title. Full testimonial is the main editor. Short testimonial is the Excerpt - leave it blank and one is generated automatically. Client image is the Featured image. Display order is the Order field under Page Attributes.', 'testimonial-manager' ); ?>
		</p>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), 'tm_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$input = array(
			'_tm_rating'   => isset( $_POST['tm_rating'] ) ? wp_unslash( $_POST['tm_rating'] ) : 5,
			'_tm_position' => isset( $_POST['tm_position'] ) ? wp_unslash( $_POST['tm_position'] ) : '',
			'_tm_company'  => isset( $_POST['tm_company'] ) ? wp_unslash( $_POST['tm_company'] ) : '',
			'_tm_source'   => isset( $_POST['tm_source'] ) ? wp_unslash( $_POST['tm_source'] ) : '',
			'_tm_date'     => isset( $_POST['tm_date'] ) ? wp_unslash( $_POST['tm_date'] ) : '',
			'_tm_featured' => isset( $_POST['tm_featured'] ) ? '1' : '',
		);

		foreach ( self::schema() as $key => $sanitize ) {
			$value = call_user_func( $sanitize, $input[ $key ] );

			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
