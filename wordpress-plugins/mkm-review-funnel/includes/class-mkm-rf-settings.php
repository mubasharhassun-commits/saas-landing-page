<?php
/**
 * Plugin settings: defaults, reads and sanitisation.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stores every plugin option in one array option.
 */
class MKM_RF_Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'mkm_rf_settings';

	/**
	 * Cached settings for the current request.
	 *
	 * @var array|null
	 */
	protected static $cache = null;

	/**
	 * Default values for every setting.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			// 'gated' asks for sentiment first; 'open' shows both paths to everyone.
			'mode'                 => 'open',
			'heading'              => __( 'Review Us', 'mkm-review-funnel' ),
			'intro'                => __( 'Please take a moment to review your experience with us. Your feedback not only helps us, it helps other potential clients.', 'mkm-review-funnel' ),
			'positive_label'       => __( 'I had a positive experience.', 'mkm-review-funnel' ),
			'negative_label'       => __( 'I had a negative experience.', 'mkm-review-funnel' ),
			'open_review_label'    => __( 'Leave a public review', 'mkm-review-funnel' ),
			'open_feedback_label'  => __( 'Send us private feedback', 'mkm-review-funnel' ),
			'review_intro'         => __( 'Thank you! We need your help. Would you share your experience on one of these sites?', 'mkm-review-funnel' ),
			'feedback_intro'       => __( 'We strive for 100% customer satisfaction. If we fell short, please tell us more so we can address your concerns.', 'mkm-review-funnel' ),
			'success_message'      => __( 'Thank you. Your message has been sent and someone from our office will be in touch.', 'mkm-review-funnel' ),
			'consent_text'         => __( 'The information you obtain at this site is not, nor is it intended to be, legal advice. You should consult an attorney for advice regarding your individual situation. We invite you to contact us and welcome your calls, letters and electronic mail. Contacting us does not create an attorney-client relationship. Please do not send any confidential information to us until such time as an attorney-client relationship has been established.', 'mkm-review-funnel' ),
			'require_consent'      => 1,
			'google_url'           => '',
			'facebook_url'         => '',
			'avvo_url'             => '',
			'notify_enabled'       => 1,
			'notify_emails'        => '',
			'notify_subject'       => __( '[{site_name}] New review-funnel feedback', 'mkm-review-funnel' ),
			'track_clicks'         => 1,
			'store_ip'             => 'hashed', // hashed | full | none.
			'min_seconds'          => 3,
			'rate_limit'           => 5,
			'captcha_provider'     => 'none', // none | recaptcha_v3 | turnstile.
			'captcha_site_key'     => '',
			'captcha_secret_key'   => '',
			'captcha_threshold'    => '0.5',
			'delete_data_on_uninstall' => 0,
		);
	}

	/**
	 * Installs defaults without overwriting existing values.
	 */
	public static function install_defaults() {
		$existing = get_option( self::OPTION, array() );

		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		if ( ! isset( $existing['notify_emails'] ) || '' === $existing['notify_emails'] ) {
			$existing['notify_emails'] = get_option( 'admin_email' );
		}

		update_option( self::OPTION, array_merge( self::defaults(), $existing ) );
		self::$cache = null;
	}

	/**
	 * Returns every setting merged over the defaults.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$stored = get_option( self::OPTION, array() );

			if ( ! is_array( $stored ) ) {
				$stored = array();
			}

			self::$cache = array_merge( self::defaults(), $stored );
		}

		return self::$cache;
	}

	/**
	 * Returns a single setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback when the key is unknown.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		$all = self::all();

		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	/**
	 * Clears the request cache. Used after saving.
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Review destinations that have a URL configured.
	 *
	 * @return array<string,array{label:string,url:string}>
	 */
	public static function destinations() {
		$map = array(
			'google'   => __( 'Google', 'mkm-review-funnel' ),
			'facebook' => __( 'Facebook', 'mkm-review-funnel' ),
			'avvo'     => __( 'Avvo', 'mkm-review-funnel' ),
		);

		$out = array();

		foreach ( $map as $slug => $label ) {
			$url = trim( (string) self::get( $slug . '_url' ) );

			if ( '' === $url ) {
				continue;
			}

			$out[ $slug ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		/**
		 * Filters the review destinations offered to visitors.
		 *
		 * @param array $out Destination slug => label/url pairs.
		 */
		return apply_filters( 'mkm_rf_destinations', $out );
	}

	/**
	 * Recipients for the notification email.
	 *
	 * @return string[]
	 */
	public static function notify_recipients() {
		$raw = (string) self::get( 'notify_emails' );

		if ( '' === trim( $raw ) ) {
			$raw = (string) get_option( 'admin_email' );
		}

		$emails = array();

		foreach ( preg_split( '/[,\s]+/', $raw ) as $candidate ) {
			$candidate = sanitize_email( trim( $candidate ) );

			if ( $candidate && is_email( $candidate ) ) {
				$emails[] = $candidate;
			}
		}

		return array_values( array_unique( $emails ) );
	}

	/**
	 * Sanitises a full settings payload coming from the settings screen.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$current  = self::all();
		$clean    = array();

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$clean['mode'] = in_array( isset( $input['mode'] ) ? $input['mode'] : '', array( 'open', 'gated' ), true )
			? $input['mode']
			: $defaults['mode'];

		$text_fields = array( 'heading', 'positive_label', 'negative_label', 'open_review_label', 'open_feedback_label', 'notify_subject' );

		foreach ( $text_fields as $field ) {
			$clean[ $field ] = isset( $input[ $field ] )
				? sanitize_text_field( wp_unslash( $input[ $field ] ) )
				: $current[ $field ];
		}

		$textarea_fields = array( 'intro', 'review_intro', 'feedback_intro', 'success_message', 'consent_text' );

		foreach ( $textarea_fields as $field ) {
			$clean[ $field ] = isset( $input[ $field ] )
				? sanitize_textarea_field( wp_unslash( $input[ $field ] ) )
				: $current[ $field ];
		}

		foreach ( array( 'google_url', 'facebook_url', 'avvo_url' ) as $field ) {
			$raw             = isset( $input[ $field ] ) ? trim( wp_unslash( $input[ $field ] ) ) : '';
			$clean[ $field ] = $raw ? esc_url_raw( $raw, array( 'http', 'https' ) ) : '';
		}

		$clean['notify_emails'] = isset( $input['notify_emails'] )
			? sanitize_text_field( wp_unslash( $input['notify_emails'] ) )
			: $current['notify_emails'];

		foreach ( array( 'require_consent', 'notify_enabled', 'track_clicks', 'delete_data_on_uninstall' ) as $flag ) {
			$clean[ $flag ] = empty( $input[ $flag ] ) ? 0 : 1;
		}

		$clean['store_ip'] = in_array( isset( $input['store_ip'] ) ? $input['store_ip'] : '', array( 'hashed', 'full', 'none' ), true )
			? $input['store_ip']
			: $defaults['store_ip'];

		// Cast, then clamp: absint() would silently turn -4 into 4.
		$clean['min_seconds'] = isset( $input['min_seconds'] )
			? max( 0, min( 60, (int) $input['min_seconds'] ) )
			: $defaults['min_seconds'];

		$clean['rate_limit'] = isset( $input['rate_limit'] )
			? max( 0, min( 100, (int) $input['rate_limit'] ) )
			: $defaults['rate_limit'];

		$clean['captcha_provider'] = in_array( isset( $input['captcha_provider'] ) ? $input['captcha_provider'] : '', array( 'none', 'recaptcha_v3', 'turnstile' ), true )
			? $input['captcha_provider']
			: $defaults['captcha_provider'];

		foreach ( array( 'captcha_site_key', 'captcha_secret_key' ) as $field ) {
			$clean[ $field ] = isset( $input[ $field ] )
				? sanitize_text_field( wp_unslash( $input[ $field ] ) )
				: $current[ $field ];
		}

		$threshold              = isset( $input['captcha_threshold'] ) ? (float) $input['captcha_threshold'] : 0.5;
		$clean['captcha_threshold'] = (string) max( 0, min( 1, $threshold ) );

		self::$cache = null;

		return array_merge( $defaults, $clean );
	}
}
