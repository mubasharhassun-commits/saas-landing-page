<?php
/**
 * Anti-spam: signed timestamps, honeypot, rate limiting and optional captcha.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Submission abuse protections.
 */
class MKM_RF_Spam {

	/**
	 * How long a form token stays valid, in seconds.
	 */
	const TOKEN_TTL = 3600;

	/**
	 * Signs a timestamp so the "form was filled in too fast" check cannot be forged.
	 *
	 * @param int $timestamp Unix timestamp.
	 * @return string
	 */
	public static function sign_timestamp( $timestamp ) {
		return hash_hmac( 'sha256', 'mkm_rf|' . (int) $timestamp, wp_salt( 'nonce' ) );
	}

	/**
	 * Verifies the signed timestamp and the minimum fill-in time.
	 *
	 * @param int    $timestamp Timestamp issued to the browser.
	 * @param string $signature Signature issued alongside it.
	 * @return true|WP_Error
	 */
	public static function check_timestamp( $timestamp, $signature ) {
		$timestamp = (int) $timestamp;
		$signature = is_string( $signature ) ? $signature : '';

		if ( ! $timestamp || ! $signature || ! hash_equals( self::sign_timestamp( $timestamp ), $signature ) ) {
			return new WP_Error( 'mkm_rf_bad_token', __( 'Your session expired. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		$age = time() - $timestamp;

		if ( $age > self::TOKEN_TTL ) {
			return new WP_Error( 'mkm_rf_expired_token', __( 'Your session expired. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		$minimum = (int) MKM_RF_Settings::get( 'min_seconds' );

		if ( $minimum > 0 && $age < $minimum ) {
			return new WP_Error( 'mkm_rf_too_fast', __( 'That was submitted a little too quickly. Please try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Rejects the request when the honeypot field was filled in.
	 *
	 * @param string $value Honeypot value.
	 * @return true|WP_Error
	 */
	public static function check_honeypot( $value ) {
		if ( '' !== trim( (string) $value ) ) {
			return new WP_Error( 'mkm_rf_honeypot', __( 'Your submission could not be processed.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * The visitor IP, as reported by the server.
	 *
	 * @return string
	 */
	public static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		/**
		 * Filters the client IP used for rate limiting and storage.
		 *
		 * Behind a proxy or CDN the real address may live in another header; resolve it
		 * here rather than trusting a forwarded header by default.
		 *
		 * @param string $ip Remote address.
		 */
		$ip = apply_filters( 'mkm_rf_client_ip', $ip );

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	/**
	 * The IP value to store, honouring the privacy setting.
	 *
	 * @return string
	 */
	public static function storable_ip() {
		$mode = MKM_RF_Settings::get( 'store_ip' );
		$ip   = self::client_ip();

		if ( 'none' === $mode || '' === $ip ) {
			return '';
		}

		if ( 'full' === $mode ) {
			return $ip;
		}

		return wp_hash( $ip );
	}

	/**
	 * Applies a per-IP hourly submission cap.
	 *
	 * @param string $bucket Bucket name so clicks and feedback are counted separately.
	 * @return true|WP_Error
	 */
	public static function check_rate_limit( $bucket = 'feedback' ) {
		$limit = (int) MKM_RF_Settings::get( 'rate_limit' );

		if ( $limit <= 0 ) {
			return true;
		}

		$ip = self::client_ip();

		if ( '' === $ip ) {
			return true;
		}

		$key   = 'mkm_rf_rl_' . md5( $bucket . '|' . $ip );
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return new WP_Error( 'mkm_rf_rate_limited', __( 'Too many submissions from this connection. Please try again later.', 'mkm-review-funnel' ), array( 'status' => 429 ) );
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Verifies the configured captcha, when one is enabled.
	 *
	 * @param string $token Captcha response token from the browser.
	 * @return true|WP_Error
	 */
	public static function check_captcha( $token ) {
		$provider = MKM_RF_Settings::get( 'captcha_provider' );

		if ( 'none' === $provider ) {
			return true;
		}

		$secret = trim( (string) MKM_RF_Settings::get( 'captcha_secret_key' ) );

		if ( '' === $secret ) {
			// Misconfigured rather than malicious: do not lock visitors out of the form.
			return true;
		}

		$token = is_string( $token ) ? trim( $token ) : '';

		if ( '' === $token ) {
			return new WP_Error( 'mkm_rf_captcha_missing', __( 'Spam verification failed. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		$endpoint = 'turnstile' === $provider
			? 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
			: 'https://www.google.com/recaptcha/api/siteverify';

		$body = array(
			'secret'   => $secret,
			'response' => $token,
		);

		$ip = self::client_ip();

		if ( $ip ) {
			$body['remoteip'] = $ip;
		}

		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 10,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			// The verification service is unreachable; fall back to the other protections.
			return true;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $result ) || empty( $result['success'] ) ) {
			return new WP_Error( 'mkm_rf_captcha_failed', __( 'Spam verification failed. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
		}

		if ( 'recaptcha_v3' === $provider && isset( $result['score'] ) ) {
			$threshold = (float) MKM_RF_Settings::get( 'captcha_threshold' );

			if ( (float) $result['score'] < $threshold ) {
				return new WP_Error( 'mkm_rf_captcha_score', __( 'Spam verification failed. Please reload the page and try again.', 'mkm-review-funnel' ), array( 'status' => 400 ) );
			}
		}

		return true;
	}
}
