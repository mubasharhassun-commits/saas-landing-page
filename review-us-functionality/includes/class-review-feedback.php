<?php
/**
 * Storage, AJAX handling and admin screens for negative feedback.
 *
 * @package MKM_Review_Us
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the private feedback submissions.
 */
class MKM_Review_Us_Feedback {

	const DB_VERSION        = '1.1.0';
	const DB_VERSION_OPTION = 'mkm_review_us_db_version';
	const AJAX_ACTION       = 'mkm_review_us_feedback';
	const NONCE_ACTION      = 'mkm_review_us_feedback_submit';
	const MENU_SLUG         = 'mkm-review-feedback';
	const PER_PAGE          = 20;

	/**
	 * Registers hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( $this, 'handle_submission' ) );

		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_post_mkm_review_us_delete', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_mkm_review_us_export', array( $this, 'handle_export' ) );

		add_action( 'wpcf7_mail_sent', array( $this, 'store_cf7_submission' ) );
	}

	/**
	 * Fully qualified table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'review_feedback';
	}

	/**
	 * Creates or migrates the feedback table. Only called on activation and after a version bump.
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(190) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			client_status varchar(20) NOT NULL DEFAULT '',
			feedback_message longtext NOT NULL,
			disclaimer_accepted tinyint(1) NOT NULL DEFAULT 0,
			source varchar(20) NOT NULL DEFAULT 'builtin',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY email (email)
		) {$charset_collate};";

		dbDelta( $sql );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Options for the "Are you a new client?" field.
	 *
	 * @return array Key => label.
	 */
	public static function client_statuses() {
		return apply_filters(
			'mkm_review_us_client_statuses',
			array(
				'new'      => __( 'Yes, I am a potential new client.', 'mkm-review-us' ),
				'existing' => __( "No, I'm a current existing client.", 'mkm-review-us' ),
				'neither'  => __( "I'm neither.", 'mkm-review-us' ),
			)
		);
	}

	/**
	 * Human readable label for a stored client status key.
	 *
	 * @param string $key Status key.
	 * @return string
	 */
	public static function client_status_label( $key ) {
		$statuses = self::client_statuses();

		return isset( $statuses[ $key ] ) ? $statuses[ $key ] : $key;
	}

	/**
	 * Validates and stores a built-in form submission.
	 */
	public function handle_submission() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Your session expired. Please reload the page and try again.', 'mkm-review-us' ) ),
				403
			);
		}

		// Honeypot: a real browser never fills this in.
		$honeypot = isset( $_POST['mkm_hp'] ) ? trim( (string) wp_unslash( $_POST['mkm_hp'] ) ) : '';

		if ( '' !== $honeypot ) {
			wp_send_json_success( array( 'message' => $this->success_message() ) );
		}

		// Time trap: reject anything submitted within three seconds of the form rendering.
		$elapsed = isset( $_POST['mkm_elapsed'] ) ? absint( $_POST['mkm_elapsed'] ) : 0;

		if ( $elapsed > 0 && $elapsed < 3000 ) {
			wp_send_json_error(
				array( 'message' => __( 'That was a little too quick. Please try again.', 'mkm-review-us' ) ),
				400
			);
		}

		if ( $this->is_rate_limited() ) {
			wp_send_json_error(
				array( 'message' => __( 'You have already sent us several messages. Please call the office if you need more help.', 'mkm-review-us' ) ),
				429
			);
		}

		$post = wp_unslash( $_POST );

		$data = array(
			'first_name'          => isset( $post['first_name'] ) ? sanitize_text_field( $post['first_name'] ) : '',
			'last_name'           => isset( $post['last_name'] ) ? sanitize_text_field( $post['last_name'] ) : '',
			'email'               => isset( $post['email'] ) ? sanitize_email( $post['email'] ) : '',
			'phone'               => isset( $post['phone'] ) ? sanitize_text_field( $post['phone'] ) : '',
			'client_status'       => isset( $post['client_status'] ) ? sanitize_key( $post['client_status'] ) : '',
			'feedback_message'    => isset( $post['feedback_message'] ) ? sanitize_textarea_field( $post['feedback_message'] ) : '',
			'disclaimer_accepted' => empty( $post['disclaimer'] ) ? 0 : 1,
			'source'              => 'builtin',
		);

		$errors = $this->validate( $data );

		if ( $errors ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please correct the highlighted fields.', 'mkm-review-us' ),
					'fields'  => $errors,
				),
				400
			);
		}

		$insert_id = self::insert( $data );

		if ( ! $insert_id ) {
			wp_send_json_error(
				array( 'message' => __( 'We could not save your feedback. Please call the office so we can help.', 'mkm-review-us' ) ),
				500
			);
		}

		$this->bump_rate_limit();
		$this->notify( $data );

		wp_send_json_success( array( 'message' => $this->success_message() ) );
	}

	/**
	 * Server side validation.
	 *
	 * @param array $data Sanitized data.
	 * @return array Field key => error message.
	 */
	private function validate( array $data ) {
		$errors = array();

		if ( '' === $data['first_name'] ) {
			$errors['first_name'] = __( 'Please enter your first name.', 'mkm-review-us' );
		}

		if ( '' === $data['last_name'] ) {
			$errors['last_name'] = __( 'Please enter your last name.', 'mkm-review-us' );
		}

		if ( '' === $data['email'] || ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'mkm-review-us' );
		}

		if ( '' !== $data['phone'] && ! preg_match( '/^[0-9\s\-\+\(\)\.]{7,25}$/', $data['phone'] ) ) {
			$errors['phone'] = __( 'Please enter a valid phone number.', 'mkm-review-us' );
		}

		if ( ! array_key_exists( $data['client_status'], self::client_statuses() ) ) {
			$errors['client_status'] = __( 'Please tell us whether you are a new client.', 'mkm-review-us' );
		}

		if ( strlen( trim( $data['feedback_message'] ) ) < 5 ) {
			$errors['feedback_message'] = __( 'Please tell us a little more so we can look into it.', 'mkm-review-us' );
		}

		if ( empty( $data['disclaimer_accepted'] ) ) {
			$errors['disclaimer'] = __( 'Please confirm you have read the notice above.', 'mkm-review-us' );
		}

		return $errors;
	}

	/**
	 * Inserts a row with a prepared query.
	 *
	 * @param array $data Sanitized data.
	 * @return int Insert ID, 0 on failure.
	 */
	public static function insert( array $data ) {
		global $wpdb;

		$row = array(
			'first_name'          => isset( $data['first_name'] ) ? $data['first_name'] : '',
			'last_name'           => isset( $data['last_name'] ) ? $data['last_name'] : '',
			'email'               => isset( $data['email'] ) ? $data['email'] : '',
			'phone'               => isset( $data['phone'] ) ? $data['phone'] : '',
			'client_status'       => isset( $data['client_status'] ) ? $data['client_status'] : '',
			'feedback_message'    => isset( $data['feedback_message'] ) ? $data['feedback_message'] : '',
			'disclaimer_accepted' => empty( $data['disclaimer_accepted'] ) ? 0 : 1,
			'source'              => isset( $data['source'] ) ? $data['source'] : 'builtin',
			'created_at'          => current_time( 'mysql' ),
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' );

		$inserted = $wpdb->insert( self::table_name(), $row, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( ! $inserted ) {
			return 0;
		}

		$insert_id = (int) $wpdb->insert_id;

		/**
		 * Fires after a negative feedback entry is stored.
		 *
		 * @param int   $insert_id Row ID.
		 * @param array $row       Stored data.
		 */
		do_action( 'mkm_review_us_feedback_stored', $insert_id, $row );

		return $insert_id;
	}

	/**
	 * Mirrors a Contact Form 7 submission into the feedback table.
	 *
	 * @param WPCF7_ContactForm $contact_form Submitted form.
	 */
	public function store_cf7_submission( $contact_form ) {
		if ( 'cf7' !== MKM_Review_Us_Settings::resolved_form_mode() ) {
			return;
		}

		$configured = MKM_Review_Us_Settings::get( 'cf7_shortcode' );

		if ( $configured && preg_match( '/id=["\']?([^"\'\s\]]+)/', $configured, $matches ) ) {
			$expected = $matches[1];
			$form_id  = method_exists( $contact_form, 'id' ) ? (string) $contact_form->id() : '';
			$hash     = method_exists( $contact_form, 'hash' ) ? (string) $contact_form->hash() : '';

			if ( $expected !== $form_id && 0 !== strpos( $hash, $expected ) ) {
				return;
			}
		}

		if ( ! class_exists( 'WPCF7_Submission' ) ) {
			return;
		}

		$submission = WPCF7_Submission::get_instance();

		if ( ! $submission ) {
			return;
		}

		$posted = $submission->get_posted_data();

		if ( ! is_array( $posted ) ) {
			return;
		}

		$value = static function ( $keys ) use ( $posted ) {
			foreach ( (array) $keys as $key ) {
				if ( isset( $posted[ $key ] ) ) {
					$raw = $posted[ $key ];

					return is_array( $raw ) ? implode( ', ', array_map( 'strval', $raw ) ) : (string) $raw;
				}
			}

			return '';
		};

		$status_raw = sanitize_text_field( $value( array( 'new-client', 'menu-new-client', 'are-you-a-new-client' ) ) );
		$status_key = '';

		foreach ( self::client_statuses() as $key => $label ) {
			if ( strtolower( $status_raw ) === strtolower( $label ) || $status_raw === $key ) {
				$status_key = $key;
				break;
			}
		}

		self::insert(
			array(
				'first_name'          => sanitize_text_field( $value( array( 'first-name', 'first_name', 'your-first-name' ) ) ),
				'last_name'           => sanitize_text_field( $value( array( 'last-name', 'last_name', 'your-last-name' ) ) ),
				'email'               => sanitize_email( $value( array( 'your-email', 'email' ) ) ),
				'phone'               => sanitize_text_field( $value( array( 'phone', 'your-phone', 'tel-phone' ) ) ),
				'client_status'       => $status_key ? $status_key : $status_raw,
				'feedback_message'    => sanitize_textarea_field( $value( array( 'your-message', 'message', 'feedback' ) ) ),
				'disclaimer_accepted' => $value( array( 'disclaimer', 'acceptance', 'your-consent' ) ) ? 1 : 0,
				'source'              => 'cf7',
			)
		);
	}

	/**
	 * Success copy shown after a submission.
	 *
	 * @return string
	 */
	private function success_message() {
		return __( 'Thank you for your feedback. We appreciate you taking the time to help us improve.', 'mkm-review-us' );
	}

	/**
	 * Transient key for the current visitor.
	 *
	 * @return string
	 */
	private function rate_limit_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

		return 'mkm_ru_rl_' . md5( wp_hash( $ip ) );
	}

	/**
	 * Whether the visitor sent too many submissions in the last hour.
	 *
	 * @return bool
	 */
	private function is_rate_limited() {
		return (int) get_transient( $this->rate_limit_key() ) >= (int) apply_filters( 'mkm_review_us_rate_limit', 5 );
	}

	/**
	 * Records one submission against the hourly limit.
	 */
	private function bump_rate_limit() {
		$key = $this->rate_limit_key();
		set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
	}

	/**
	 * Emails the office about a new submission.
	 *
	 * @param array $data Stored data.
	 */
	private function notify( array $data ) {
		$to = MKM_Review_Us_Settings::get( 'notification_email' );

		if ( ! $to || ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		$subject = sprintf(
			/* translators: %s: site name. */
			__( '[%s] New private review feedback', 'mkm-review-us' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);

		$lines = array(
			sprintf( __( 'Name: %s', 'mkm-review-us' ), trim( $data['first_name'] . ' ' . $data['last_name'] ) ),
			sprintf( __( 'Email: %s', 'mkm-review-us' ), $data['email'] ),
			sprintf( __( 'Phone: %s', 'mkm-review-us' ), $data['phone'] ? $data['phone'] : __( 'Not provided', 'mkm-review-us' ) ),
			sprintf( __( 'Are you a new client: %s', 'mkm-review-us' ), self::client_status_label( $data['client_status'] ) ),
			sprintf( __( 'Disclaimer accepted: %s', 'mkm-review-us' ), empty( $data['disclaimer_accepted'] ) ? __( 'No', 'mkm-review-us' ) : __( 'Yes', 'mkm-review-us' ) ),
			'',
			__( 'Message:', 'mkm-review-us' ),
			$data['feedback_message'],
			'',
			sprintf( __( 'View all feedback: %s', 'mkm-review-us' ), admin_url( 'admin.php?page=' . self::MENU_SLUG ) ),
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		if ( is_email( $data['email'] ) ) {
			$headers[] = 'Reply-To: ' . $data['email'];
		}

		wp_mail(
			apply_filters( 'mkm_review_us_notification_recipient', $to, $data ),
			$subject,
			implode( "\n", $lines ),
			$headers
		);
	}

	/**
	 * Adds the Review Feedback admin menu.
	 */
	public function add_admin_page() {
		add_menu_page(
			__( 'Review Feedback', 'mkm-review-us' ),
			__( 'Review Feedback', 'mkm-review-us' ),
			MKM_Review_Us_Settings::capability(),
			self::MENU_SLUG,
			array( $this, 'render_admin_page' ),
			'dashicons-testimonial',
			26
		);
	}

	/**
	 * Reads one page of entries.
	 *
	 * @param int $paged Page number, 1 based.
	 * @return array {
	 *     @type array $items Rows.
	 *     @type int   $total Total rows.
	 * }
	 */
	private function get_entries( $paged ) {
		global $wpdb;

		$table  = self::table_name();
		$paged  = max( 1, (int) $paged );
		$offset = ( $paged - 1 ) * self::PER_PAGE;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d",
				self::PER_PAGE,
				$offset
			),
			ARRAY_A
		);
		// phpcs:enable

		return array(
			'items' => is_array( $items ) ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Renders the entries table.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( MKM_Review_Us_Settings::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to view feedback.', 'mkm-review-us' ) );
		}

		$paged   = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$data    = $this->get_entries( $paged );
		$pages   = (int) ceil( $data['total'] / self::PER_PAGE );
		$notice   = isset( $_GET['mkm_notice'] ) ? sanitize_key( $_GET['mkm_notice'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$statuses = self::client_statuses();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Review Feedback', 'mkm-review-us' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mkm_review_us_export' ), 'mkm_review_us_export' ) ); ?>">
				<?php esc_html_e( 'Export CSV', 'mkm-review-us' ); ?>
			</a>
			<hr class="wp-header-end" />

			<?php if ( 'deleted' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry deleted.', 'mkm-review-us' ); ?></p></div>
			<?php endif; ?>

			<p class="description">
				<?php
				printf(
					/* translators: %d: number of entries. */
					esc_html( _n( '%d submission.', '%d submissions.', $data['total'], 'mkm-review-us' ) ),
					(int) $data['total']
				);
				?>
			</p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" style="width:60px;"><?php esc_html_e( 'ID', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Name', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Email', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Phone', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'New client?', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Message', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Disclaimer', 'mkm-review-us' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Submitted', 'mkm-review-us' ); ?></th>
						<th scope="col" style="width:90px;"><?php esc_html_e( 'Actions', 'mkm-review-us' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $data['items'] ) : ?>
						<tr><td colspan="9"><?php esc_html_e( 'No feedback has been submitted yet.', 'mkm-review-us' ); ?></td></tr>
					<?php endif; ?>

					<?php foreach ( $data['items'] as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item['id'] ); ?></td>
							<td><?php echo esc_html( trim( $item['first_name'] . ' ' . $item['last_name'] ) ); ?></td>
							<td><a href="mailto:<?php echo esc_attr( $item['email'] ); ?>"><?php echo esc_html( $item['email'] ); ?></a></td>
							<td><?php echo esc_html( $item['phone'] ); ?></td>
							<td><?php echo esc_html( isset( $statuses[ $item['client_status'] ] ) ? $statuses[ $item['client_status'] ] : $item['client_status'] ); ?></td>
							<td><?php echo nl2br( esc_html( $item['feedback_message'] ) ); ?></td>
							<td><?php echo empty( $item['disclaimer_accepted'] ) ? esc_html__( 'No', 'mkm-review-us' ) : esc_html__( 'Yes', 'mkm-review-us' ); ?></td>
							<td>
								<?php
								echo esc_html(
									mysql2date(
										get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
										$item['created_at']
									)
								);
								?>
							</td>
							<td>
								<a class="submitdelete"
									href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mkm_review_us_delete&entry=' . (int) $item['id'] ), 'mkm_review_us_delete_' . (int) $item['id'] ) ); ?>"
									onclick="return confirm( '<?php echo esc_js( __( 'Delete this feedback entry permanently?', 'mkm-review-us' ) ); ?>' );">
									<?php esc_html_e( 'Delete', 'mkm-review-us' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'current'   => max( 1, $paged ),
								'total'     => $pages,
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
							)
						)
					);
					?>
				</div></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Deletes a single entry.
	 */
	public function handle_delete() {
		if ( ! current_user_can( MKM_Review_Us_Settings::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to delete feedback.', 'mkm-review-us' ), 403 );
		}

		$entry_id = isset( $_GET['entry'] ) ? absint( $_GET['entry'] ) : 0;

		check_admin_referer( 'mkm_review_us_delete_' . $entry_id );

		if ( $entry_id ) {
			global $wpdb;
			$wpdb->delete( self::table_name(), array( 'id' => $entry_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		wp_safe_redirect( add_query_arg( 'mkm_notice', 'deleted', admin_url( 'admin.php?page=' . self::MENU_SLUG ) ) );
		exit;
	}

	/**
	 * Streams every entry as CSV.
	 */
	public function handle_export() {
		if ( ! current_user_can( MKM_Review_Us_Settings::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to export feedback.', 'mkm-review-us' ), 403 );
		}

		check_admin_referer( 'mkm_review_us_export' );

		global $wpdb;

		$table = self::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC", ARRAY_A );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=review-feedback-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		fputcsv(
			$output,
			array(
				__( 'ID', 'mkm-review-us' ),
				__( 'First Name', 'mkm-review-us' ),
				__( 'Last Name', 'mkm-review-us' ),
				__( 'Email', 'mkm-review-us' ),
				__( 'Phone', 'mkm-review-us' ),
				__( 'New Client', 'mkm-review-us' ),
				__( 'Message', 'mkm-review-us' ),
				__( 'Disclaimer Accepted', 'mkm-review-us' ),
				__( 'Source', 'mkm-review-us' ),
				__( 'Submitted', 'mkm-review-us' ),
			)
		);

		foreach ( (array) $rows as $row ) {
			fputcsv(
				$output,
				array(
					$row['id'],
					$row['first_name'],
					$row['last_name'],
					$row['email'],
					$row['phone'],
					self::client_status_label( $row['client_status'] ),
					$row['feedback_message'],
					empty( $row['disclaimer_accepted'] ) ? 'No' : 'Yes',
					$row['source'],
					$row['created_at'],
				)
			);
		}

		fclose( $output );
		exit;
	}
}
