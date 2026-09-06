<?php
/**
 * Settings API integration: Settings -> Review Us.
 *
 * @package MKM_Review_Us
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and reads the plugin settings.
 */
class MKM_Review_Us_Settings {

	const OPTION      = 'mkm_review_us_settings';
	const GROUP       = 'mkm_review_us_settings_group';
	const PAGE_SLUG   = 'mkm-review-us';

	/**
	 * Hooks the settings screen.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Capability required to manage the plugin.
	 *
	 * @return string
	 */
	public static function capability() {
		return apply_filters( 'mkm_review_us_capability', 'manage_options' );
	}

	/**
	 * Default setting values.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'google_review_url'  => '',
			'page_id'            => 0,
			'open_in_new_tab'    => 1,
			'form_mode'          => 'auto',
			'gravity_form_id'    => 0,
			'cf7_shortcode'      => '',
			'notification_email' => '',
			'footer_auto_link'   => 0,
			'delete_data'        => 0,
		);
	}

	/**
	 * Returns one setting, or the whole sanitized array when no key is given.
	 *
	 * @param string|null $key Setting key.
	 * @return mixed
	 */
	public static function get( $key = null ) {
		$settings = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );

		if ( null === $key ) {
			return $settings;
		}

		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	/**
	 * The configured Google Business Profile review URL.
	 *
	 * @return string
	 */
	public static function google_review_url() {
		return (string) self::get( 'google_review_url' );
	}

	/**
	 * ID of the page that hosts the review funnel.
	 *
	 * @return int
	 */
	public static function page_id() {
		$page_id = (int) self::get( 'page_id' );

		if ( $page_id > 0 ) {
			return $page_id;
		}

		$page = get_page_by_path( 'review-us' );

		return $page ? (int) $page->ID : 0;
	}

	/**
	 * Public URL of the Review Us page. Falls back to /review-us/ on the current site.
	 *
	 * @return string
	 */
	public static function page_url() {
		$page_id = self::page_id();

		if ( $page_id > 0 ) {
			$permalink = get_permalink( $page_id );

			if ( $permalink ) {
				return $permalink;
			}
		}

		return home_url( '/review-us/' );
	}

	/**
	 * Which form implementation should render inside the negative feedback popup.
	 *
	 * @return string One of gravity, cf7, builtin.
	 */
	public static function resolved_form_mode() {
		$mode = self::get( 'form_mode' );

		if ( 'gravity' === $mode || 'cf7' === $mode || 'builtin' === $mode ) {
			return $mode;
		}

		if ( self::gravity_forms_active() && self::get( 'gravity_form_id' ) ) {
			return 'gravity';
		}

		if ( self::cf7_active() && self::get( 'cf7_shortcode' ) ) {
			return 'cf7';
		}

		return 'builtin';
	}

	/**
	 * Whether Gravity Forms is available.
	 *
	 * @return bool
	 */
	public static function gravity_forms_active() {
		return class_exists( 'GFForms' ) || class_exists( 'GFAPI' );
	}

	/**
	 * Whether Contact Form 7 is available.
	 *
	 * @return bool
	 */
	public static function cf7_active() {
		return class_exists( 'WPCF7' );
	}

	/**
	 * Creates the Review Us page on activation when it does not exist yet.
	 */
	public static function ensure_review_page() {
		$settings = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		$existing = get_page_by_path( 'review-us' );

		if ( $existing instanceof WP_Post ) {
			$settings['page_id'] = (int) $existing->ID;
			update_option( self::OPTION, $settings );

			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Review Us', 'mkm-review-us' ),
				'post_name'    => 'review-us',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[mkm_review_us]',
			)
		);

		if ( ! is_wp_error( $page_id ) ) {
			$settings['page_id'] = (int) $page_id;
			update_option( self::OPTION, $settings );
		}
	}

	/**
	 * Adds the settings screen under Settings.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Review Us Settings', 'mkm-review-us' ),
			__( 'Review Us', 'mkm-review-us' ),
			self::capability(),
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers the option, sections and fields.
	 */
	public function register_settings() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'mkm_review_us_general',
			__( 'Review funnel', 'mkm-review-us' ),
			array( $this, 'render_general_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'google_review_url',
			__( 'Google Review URL', 'mkm-review-us' ),
			array( $this, 'render_google_review_url_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_general'
		);

		add_settings_field(
			'page_id',
			__( 'Review Us Page', 'mkm-review-us' ),
			array( $this, 'render_page_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_general'
		);

		add_settings_field(
			'open_in_new_tab',
			__( 'Open Google in a new tab', 'mkm-review-us' ),
			array( $this, 'render_new_tab_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_general'
		);

		add_settings_field(
			'footer_auto_link',
			__( 'Footer button fallback', 'mkm-review-us' ),
			array( $this, 'render_footer_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_general'
		);

		add_settings_section(
			'mkm_review_us_form',
			__( 'Negative feedback form', 'mkm-review-us' ),
			array( $this, 'render_form_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'form_mode',
			__( 'Form source', 'mkm-review-us' ),
			array( $this, 'render_form_mode_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_form'
		);

		add_settings_field(
			'gravity_form_id',
			__( 'Gravity Forms form ID', 'mkm-review-us' ),
			array( $this, 'render_gravity_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_form'
		);

		add_settings_field(
			'cf7_shortcode',
			__( 'Contact Form 7 shortcode', 'mkm-review-us' ),
			array( $this, 'render_cf7_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_form'
		);

		add_settings_field(
			'notification_email',
			__( 'Notification email', 'mkm-review-us' ),
			array( $this, 'render_notification_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_form'
		);

		add_settings_section(
			'mkm_review_us_advanced',
			__( 'Advanced', 'mkm-review-us' ),
			'__return_false',
			self::PAGE_SLUG
		);

		add_settings_field(
			'delete_data',
			__( 'Delete data on uninstall', 'mkm-review-us' ),
			array( $this, 'render_delete_data_field' ),
			self::PAGE_SLUG,
			'mkm_review_us_advanced'
		);
	}

	/**
	 * Validates and sanitizes every submitted value.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$current  = self::get();
		$defaults = self::defaults();
		$clean    = $defaults;

		$url = isset( $input['google_review_url'] ) ? trim( (string) $input['google_review_url'] ) : '';

		if ( '' === $url ) {
			$clean['google_review_url'] = '';
		} else {
			$url    = esc_url_raw( $url, array( 'http', 'https' ) );
			$parsed = wp_parse_url( $url );

			if ( ! $url || empty( $parsed['host'] ) ) {
				add_settings_error(
					self::OPTION,
					'mkm_review_us_url',
					__( 'The Google Review URL is not a valid http(s) address. The previous value was kept.', 'mkm-review-us' )
				);
				$clean['google_review_url'] = $current['google_review_url'];
			} else {
				$clean['google_review_url'] = $url;
			}
		}

		$clean['page_id']            = isset( $input['page_id'] ) ? absint( $input['page_id'] ) : 0;
		$clean['open_in_new_tab']    = empty( $input['open_in_new_tab'] ) ? 0 : 1;
		$clean['footer_auto_link']   = empty( $input['footer_auto_link'] ) ? 0 : 1;
		$clean['delete_data']        = empty( $input['delete_data'] ) ? 0 : 1;
		$clean['gravity_form_id']    = isset( $input['gravity_form_id'] ) ? absint( $input['gravity_form_id'] ) : 0;
		$clean['notification_email'] = isset( $input['notification_email'] ) ? sanitize_email( $input['notification_email'] ) : '';

		$mode              = isset( $input['form_mode'] ) ? sanitize_key( $input['form_mode'] ) : 'auto';
		$clean['form_mode'] = in_array( $mode, array( 'auto', 'gravity', 'cf7', 'builtin' ), true ) ? $mode : 'auto';

		$shortcode = isset( $input['cf7_shortcode'] ) ? trim( wp_strip_all_tags( (string) $input['cf7_shortcode'] ) ) : '';

		if ( '' !== $shortcode && ! preg_match( '/^\[contact-form-7[^\]]*\]$/', $shortcode ) ) {
			add_settings_error(
				self::OPTION,
				'mkm_review_us_cf7',
				__( 'The Contact Form 7 field must contain a single [contact-form-7 ...] shortcode. The previous value was kept.', 'mkm-review-us' )
			);
			$shortcode = $current['cf7_shortcode'];
		}

		$clean['cf7_shortcode'] = $shortcode;

		return $clean;
	}

	/**
	 * Section intro for the funnel settings.
	 */
	public function render_general_intro() {
		echo '<p>' . esc_html__( 'These settings drive the thumbs up flow and the link used by the footer Review Us button.', 'mkm-review-us' ) . '</p>';
	}

	/**
	 * Section intro for the form settings.
	 */
	public function render_form_intro() {
		$detected = array();

		if ( self::gravity_forms_active() ) {
			$detected[] = __( 'Gravity Forms', 'mkm-review-us' );
		}

		if ( self::cf7_active() ) {
			$detected[] = __( 'Contact Form 7', 'mkm-review-us' );
		}

		$message = $detected
			? sprintf(
				/* translators: %s: comma separated plugin names. */
				__( 'Detected on this site: %s.', 'mkm-review-us' ),
				implode( ', ', $detected )
			)
			: __( 'No Gravity Forms or Contact Form 7 installation was detected, so the built-in form will be used.', 'mkm-review-us' );

		echo '<p>' . esc_html( $message ) . '</p>';
		echo '<p>' . esc_html__( 'Submissions from the built-in form are stored under Review Feedback in the admin menu. Gravity Forms submissions are stored as normal Gravity Forms entries.', 'mkm-review-us' ) . '</p>';
	}

	/**
	 * Google review URL field.
	 */
	public function render_google_review_url_field() {
		$value = self::get( 'google_review_url' );
		?>
		<input type="url" class="regular-text code" name="<?php echo esc_attr( self::OPTION ); ?>[google_review_url]" id="mkm_google_review_url" value="<?php echo esc_attr( $value ); ?>" placeholder="https://g.page/r/XXXXXXXXXXXX/review" />
		<p class="description">
			<?php esc_html_e( 'Paste the "Ask for reviews" short link from your Google Business Profile, or a https://search.google.com/local/writereview?placeid=... URL.', 'mkm-review-us' ); ?>
		</p>
		<?php
	}

	/**
	 * Review Us page selector.
	 */
	public function render_page_field() {
		wp_dropdown_pages(
			array(
				'name'              => esc_attr( self::OPTION ) . '[page_id]',
				'id'                => 'mkm_review_us_page_id',
				'selected'          => self::page_id(),
				'show_option_none'  => __( '— Select a page —', 'mkm-review-us' ),
				'option_none_value' => 0,
			)
		);
		?>
		<p class="description">
			<?php
			printf(
				/* translators: %s: shortcode. */
				esc_html__( 'The page that contains the %s shortcode. Used for the footer link and for loading the front end assets.', 'mkm-review-us' ),
				'<code>[mkm_review_us]</code>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * New tab checkbox.
	 */
	public function render_new_tab_field() {
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[open_in_new_tab]" value="1" <?php checked( 1, (int) self::get( 'open_in_new_tab' ) ); ?> />
			<?php esc_html_e( 'Open the Google review page in a new tab (recommended).', 'mkm-review-us' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'A new tab keeps your site open behind the Google sign-in flow, so the client comes back to the site after reviewing.', 'mkm-review-us' ); ?>
		</p>
		<?php
	}

	/**
	 * Footer fallback checkbox.
	 */
	public function render_footer_field() {
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[footer_auto_link]" value="1" <?php checked( 1, (int) self::get( 'footer_auto_link' ) ); ?> />
			<?php esc_html_e( 'Repoint any footer link labelled "Review Us" to the Review Us page.', 'mkm-review-us' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'WordPress menu items are repointed in PHP automatically. Enable this only if the footer button is built with a page builder (Elementor, WPBakery) and you would rather not edit it by hand. It loads a 1 KB script sitewide.', 'mkm-review-us' ); ?>
		</p>
		<?php
	}

	/**
	 * Form mode selector.
	 */
	public function render_form_mode_field() {
		$value   = self::get( 'form_mode' );
		$options = array(
			'auto'    => __( 'Automatic (Gravity Forms, then Contact Form 7, then built-in)', 'mkm-review-us' ),
			'gravity' => __( 'Gravity Forms', 'mkm-review-us' ),
			'cf7'     => __( 'Contact Form 7', 'mkm-review-us' ),
			'builtin' => __( 'Built-in form (stored in this plugin)', 'mkm-review-us' ),
		);
		?>
		<select name="<?php echo esc_attr( self::OPTION ); ?>[form_mode]" id="mkm_review_us_form_mode">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php
			printf(
				/* translators: %s: resolved form mode. */
				esc_html__( 'Currently rendering: %s.', 'mkm-review-us' ),
				'<strong>' . esc_html( self::resolved_form_mode() ) . '</strong>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Gravity Forms ID field.
	 */
	public function render_gravity_field() {
		?>
		<input type="number" min="0" step="1" class="small-text" name="<?php echo esc_attr( self::OPTION ); ?>[gravity_form_id]" value="<?php echo esc_attr( (string) self::get( 'gravity_form_id' ) ); ?>" />
		<p class="description"><?php esc_html_e( 'The form is rendered with AJAX enabled so the popup is never reloaded.', 'mkm-review-us' ); ?></p>
		<?php
	}

	/**
	 * CF7 shortcode field.
	 */
	public function render_cf7_field() {
		?>
		<input type="text" class="large-text code" name="<?php echo esc_attr( self::OPTION ); ?>[cf7_shortcode]" value="<?php echo esc_attr( self::get( 'cf7_shortcode' ) ); ?>" placeholder='[contact-form-7 id="123" title="Negative Feedback"]' />
		<p class="description"><?php esc_html_e( 'Copy the shortcode from Contact → Contact Forms. Contact Form 7 does not store entries, so submissions are also saved to the Review Feedback table by this plugin.', 'mkm-review-us' ); ?></p>
		<?php
	}

	/**
	 * Notification email field.
	 */
	public function render_notification_field() {
		?>
		<input type="email" class="regular-text" name="<?php echo esc_attr( self::OPTION ); ?>[notification_email]" value="<?php echo esc_attr( self::get( 'notification_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
		<p class="description"><?php esc_html_e( 'Where built-in form submissions are emailed. Defaults to the site administration email.', 'mkm-review-us' ); ?></p>
		<?php
	}

	/**
	 * Uninstall data field.
	 */
	public function render_delete_data_field() {
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[delete_data]" value="1" <?php checked( 1, (int) self::get( 'delete_data' ) ); ?> />
			<?php esc_html_e( 'Drop the feedback table and delete settings when the plugin is uninstalled.', 'mkm-review-us' ); ?>
		</label>
		<?php
	}

	/**
	 * Renders the settings screen.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to manage these settings.', 'mkm-review-us' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Review Us Settings', 'mkm-review-us' ); ?></h1>
			<?php settings_errors( self::OPTION ); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
			<h2><?php esc_html_e( 'Usage', 'mkm-review-us' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: shortcode, 2: review page URL. */
					esc_html__( 'Place %1$s on the Review Us page. Current page URL: %2$s', 'mkm-review-us' ),
					'<code>[mkm_review_us]</code>',
					'<a href="' . esc_url( self::page_url() ) . '">' . esc_html( self::page_url() ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}
