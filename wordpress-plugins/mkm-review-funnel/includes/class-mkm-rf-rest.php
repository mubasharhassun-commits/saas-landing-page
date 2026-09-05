<?php
/**
 * REST endpoints for the public funnel.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the public submission endpoints.
 */
class MKM_RF_Rest {

	/**
	 * REST namespace.
	 */
	const NAMESPACE_V1 = 'mkm-review-funnel/v1';

	/**
	 * Nonce action used for public submissions.
	 */
	const NONCE_ACTION = 'mkm_rf_submit';

	/**
	 * Hooks the route registration.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers every route.
	 */
	public function register_routes() {
		// Issued on demand so a full-page cache can never serve a stale nonce.
		register_rest_route(
			self::NAMESPACE_V1,
			'/token',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_token' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/feedback',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_feedback' ),
				'permission_callback' => '__return_true',
				'args'                => $this->feedback_args(),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/click',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'record_click' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'destination' => array(
						'type'     => 'string',
						'required' => true,
					),
					'nonce'       => array( 'type' => 'string' ),
					'ts'          => array( 'type' => 'integer' ),
					'sig'         => array( 'type' => 'string' ),
					'source_url'  => array( 'type' => 'string' ),
				),
			)
		);
	}

	/**
	 * Argument schema for the feedback endpoint.
	 *
	 * @return array
	 */
	protected function feedback_args() {
		return array(
			'first_name'    => array( 'type' => 'string' ),
			'last_name'     => array( 'type' => 'string' ),
			'email'         => array( 'type' => 'string' ),
			'phone'         => array( 'type' => 'string' ),
			'client_status' => array( 'type' => 'string' ),
			'message'       => array( 'type' => 'string' ),
			'consent'       => array( 'type' => 'string' ),
			'sentiment'     => array( 'type' => 'string' ),
			'website'       => array( 'type' => 'string' ), // Honeypot.
			'nonce'         => array( 'type' => 'string' ),
			'ts'            => array( 'type' => 'integer' ),
			'sig'           => array( 'type' => 'string' ),
			'captcha'       => array( 'type' => 'string' ),
			'source_url'    => array( 'type' => 'string' ),
		);
	}

	/**
	 * Returns a fresh nonce and signed timestamp.
	 *
	 * @return WP_REST_Response
	 */
	public function get_token() {
		$now = time();

		$response = new WP_REST_Response(
			array(
				'nonce' => wp_create_nonce( self::NONCE_ACTION ),
				'ts'    => $now,
				'sig'   => MKM_RF_Spam::sign_timestamp( $now ),
			)
		);

		// This response is per-visitor and must never be cached.
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );

		return $response;
	}

	/**
	 * Shared verification for both write endpoints.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $bucket  Rate-limit bucket.
	 * @return true|WP_Error
	 */
	protected function verify( WP_REST_Request $request, $bucket ) {
		$nonce = (string) $request->get_param( 'nonce' );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return new WP_Error( 'mkm_rf_bad_nonce', __( 'Your session expired. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 403 ) );
		}

		$checks = array(
			MKM_RF_Spam::check_honeypot( $request->get_param( 'website' ) ),
			MKM_RF_Spam::check_timestamp( $request->get_param( 'ts' ), $request->get_param( 'sig' ) ),
			MKM_RF_Spam::check_rate_limit( $bucket ),
		);

		foreach ( $checks as $check ) {
			if ( is_wp_error( $check ) ) {
				return $check;
			}
		}

		return true;
	}

	/**
	 * Validates and stores a feedback submission.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_feedback( WP_REST_Request $request ) {
		$verified = $this->verify( $request, 'feedback' );

		if ( is_wp_error( $verified ) ) {
			return $verified;
		}

		$captcha = MKM_RF_Spam::check_captcha( $request->get_param( 'captcha' ) );

		if ( is_wp_error( $captcha ) ) {
			return $captcha;
		}

		$first_name    = sanitize_text_field( (string) $request->get_param( 'first_name' ) );
		$last_name     = sanitize_text_field( (string) $request->get_param( 'last_name' ) );
		$email         = sanitize_email( (string) $request->get_param( 'email' ) );
		$phone         = sanitize_text_field( (string) $request->get_param( 'phone' ) );
		$client_status = sanitize_text_field( (string) $request->get_param( 'client_status' ) );
		$message       = sanitize_textarea_field( (string) $request->get_param( 'message' ) );
		$consent       = in_array( (string) $request->get_param( 'consent' ), array( '1', 'true', 'on', 'yes' ), true );

		$errors = array();

		if ( '' === $first_name ) {
			$errors['first_name'] = __( 'Please enter your first name.', 'mkm-review-funnel' );
		}

		if ( '' === $last_name ) {
			$errors['last_name'] = __( 'Please enter your last name.', 'mkm-review-funnel' );
		}

		if ( '' === $email || ! is_email( $email ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'mkm-review-funnel' );
		}

		if ( '' === $phone ) {
			$errors['phone'] = __( 'Please enter a phone number.', 'mkm-review-funnel' );
		}

		if ( '' === $message ) {
			$errors['message'] = __( 'Please tell us what happened.', 'mkm-review-funnel' );
		}

		if ( MKM_RF_Settings::get( 'require_consent' ) && ! $consent ) {
			$errors['consent'] = __( 'Please confirm you have read the notice above.', 'mkm-review-funnel' );
		}

		if ( $errors ) {
			return new WP_Error(
				'mkm_rf_validation',
				__( 'Please check the highlighted fields.', 'mkm-review-funnel' ),
				array(
					'status' => 422,
					'fields' => $errors,
				)
			);
		}

		$sentiment = 'positive' === $request->get_param( 'sentiment' ) ? 'positive' : 'negative';

		$entry = array(
			'sentiment'     => $sentiment,
			'entry_type'    => 'feedback',
			'first_name'    => $first_name,
			'last_name'     => $last_name,
			'email'         => $email,
			'phone'         => $phone,
			'client_status' => $client_status,
			'message'       => $message,
			'consent'       => $consent ? 1 : 0,
			// Snapshot the disclaimer wording that was actually agreed to.
			'consent_text'  => $consent ? (string) MKM_RF_Settings::get( 'consent_text' ) : '',
			'source_url'    => esc_url_raw( (string) $request->get_param( 'source_url' ) ),
			'referrer'      => esc_url_raw( (string) $request->get_header( 'referer' ) ),
			'user_agent'    => sanitize_text_field( (string) $request->get_header( 'user_agent' ) ),
			'ip_address'    => MKM_RF_Spam::storable_ip(),
			'status'        => 'new',
		);

		$id = MKM_RF_Repository::insert( $entry );

		if ( is_wp_error( $id ) ) {
			return new WP_Error( 'mkm_rf_save_failed', $id->get_error_message(), array( 'status' => 500 ) );
		}

		$entry['created_at'] = current_time( 'mysql' );

		MKM_RF_Mailer::notify( $id, $entry );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => (string) MKM_RF_Settings::get( 'success_message' ),
			),
			201
		);
	}

	/**
	 * Records a click through to a public review profile.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function record_click( WP_REST_Request $request ) {
		if ( ! MKM_RF_Settings::get( 'track_clicks' ) ) {
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		$verified = $this->verify( $request, 'click' );

		if ( is_wp_error( $verified ) ) {
			return $verified;
		}

		$destination  = sanitize_key( (string) $request->get_param( 'destination' ) );
		$destinations = MKM_RF_Settings::destinations();

		if ( ! isset( $destinations[ $destination ] ) ) {
			return new WP_Error( 'mkm_rf_bad_destination', __( 'Unknown review destination.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		$id = MKM_RF_Repository::insert(
			array(
				'sentiment'   => 'positive',
				'entry_type'  => 'click',
				'destination' => $destination,
				'source_url'  => esc_url_raw( (string) $request->get_param( 'source_url' ) ),
				'referrer'    => esc_url_raw( (string) $request->get_header( 'referer' ) ),
				'user_agent'  => sanitize_text_field( (string) $request->get_header( 'user_agent' ) ),
				'ip_address'  => MKM_RF_Spam::storable_ip(),
				'status'      => 'read',
			)
		);

		if ( is_wp_error( $id ) ) {
			return new WP_Error( 'mkm_rf_save_failed', $id->get_error_message(), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( array( 'success' => true ), 201 );
	}
}
