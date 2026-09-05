<?php
/**
 * Notification emails.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sends the admin notification for new feedback.
 */
class MKM_RF_Mailer {

	/**
	 * Notifies the configured recipients about a new feedback entry.
	 *
	 * @param int   $entry_id Entry id.
	 * @param array $entry    Stored entry values.
	 * @return bool
	 */
	public static function notify( $entry_id, array $entry ) {
		if ( ! MKM_RF_Settings::get( 'notify_enabled' ) ) {
			return false;
		}

		if ( isset( $entry['entry_type'] ) && 'feedback' !== $entry['entry_type'] ) {
			return false;
		}

		$recipients = MKM_RF_Settings::notify_recipients();

		if ( ! $recipients ) {
			return false;
		}

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$subject   = str_replace(
			array( '{site_name}', '{sentiment}' ),
			array( $site_name, $entry['sentiment'] ),
			(string) MKM_RF_Settings::get( 'notify_subject' )
		);

		$name = trim( $entry['first_name'] . ' ' . $entry['last_name'] );

		$lines = array(
			__( 'A new review-funnel submission was received.', 'mkm-review-funnel' ),
			'',
			sprintf( '%s: %s', __( 'Sentiment', 'mkm-review-funnel' ), $entry['sentiment'] ),
			sprintf( '%s: %s', __( 'Name', 'mkm-review-funnel' ), $name ),
			sprintf( '%s: %s', __( 'Email', 'mkm-review-funnel' ), $entry['email'] ),
			sprintf( '%s: %s', __( 'Phone', 'mkm-review-funnel' ), $entry['phone'] ),
			sprintf( '%s: %s', __( 'Client status', 'mkm-review-funnel' ), $entry['client_status'] ),
			'',
			__( 'Message:', 'mkm-review-funnel' ),
			$entry['message'],
			'',
			sprintf( '%s: %s', __( 'Submitted from', 'mkm-review-funnel' ), $entry['source_url'] ),
			sprintf( '%s: %s', __( 'Received', 'mkm-review-funnel' ), $entry['created_at'] ),
			'',
			sprintf(
				/* translators: %s: admin URL of the entry. */
				__( 'View in the dashboard: %s', 'mkm-review-funnel' ),
				admin_url( 'admin.php?page=mkm-review-funnel&action=view&entry=' . absint( $entry_id ) )
			),
		);

		$message = implode( "\n", $lines );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		// Reply-To lets staff answer the client directly without spoofing the From address.
		if ( ! empty( $entry['email'] ) && is_email( $entry['email'] ) ) {
			$reply_name = $name ? $name : $entry['email'];
			$headers[]  = sprintf( 'Reply-To: %s <%s>', $reply_name, $entry['email'] );
		}

		/**
		 * Filters the notification email arguments.
		 *
		 * @param array $args     Recipients, subject, message and headers.
		 * @param int   $entry_id Entry id.
		 * @param array $entry    Stored entry values.
		 */
		$args = apply_filters(
			'mkm_rf_notification_email',
			array(
				'to'      => $recipients,
				'subject' => $subject,
				'message' => $message,
				'headers' => $headers,
			),
			$entry_id,
			$entry
		);

		return (bool) wp_mail( $args['to'], $args['subject'], $args['message'], $args['headers'] );
	}
}
