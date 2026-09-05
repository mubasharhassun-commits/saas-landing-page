<?php
/**
 * Every database read and write for the plugin lives here.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Data access layer for review funnel entries.
 */
class MKM_RF_Repository {

	/**
	 * Inserts an entry.
	 *
	 * @param array $data Raw (already sanitised) entry values.
	 * @return int|WP_Error Inserted row id, or WP_Error on failure.
	 */
	public static function insert( array $data ) {
		global $wpdb;

		$row = array(
			'sentiment'    => isset( $data['sentiment'] ) && 'positive' === $data['sentiment'] ? 'positive' : 'negative',
			'entry_type'   => isset( $data['entry_type'] ) && 'click' === $data['entry_type'] ? 'click' : 'feedback',
			'destination'  => isset( $data['destination'] ) ? substr( (string) $data['destination'], 0, 50 ) : '',
			'first_name'   => isset( $data['first_name'] ) ? substr( (string) $data['first_name'], 0, 100 ) : '',
			'last_name'    => isset( $data['last_name'] ) ? substr( (string) $data['last_name'], 0, 100 ) : '',
			'email'        => isset( $data['email'] ) ? substr( (string) $data['email'], 0, 190 ) : '',
			'phone'        => isset( $data['phone'] ) ? substr( (string) $data['phone'], 0, 50 ) : '',
			'client_status'=> isset( $data['client_status'] ) ? substr( (string) $data['client_status'], 0, 100 ) : '',
			'message'      => isset( $data['message'] ) ? (string) $data['message'] : '',
			'consent'      => empty( $data['consent'] ) ? 0 : 1,
			'consent_text' => isset( $data['consent_text'] ) ? (string) $data['consent_text'] : '',
			'source_url'   => isset( $data['source_url'] ) ? substr( (string) $data['source_url'], 0, 255 ) : '',
			'referrer'     => isset( $data['referrer'] ) ? substr( (string) $data['referrer'], 0, 255 ) : '',
			'user_agent'   => isset( $data['user_agent'] ) ? substr( (string) $data['user_agent'], 0, 255 ) : '',
			'ip_address'   => isset( $data['ip_address'] ) ? substr( (string) $data['ip_address'], 0, 100 ) : '',
			'status'       => isset( $data['status'] ) ? substr( (string) $data['status'], 0, 20 ) : 'new',
			'created_at'   => current_time( 'mysql' ),
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom table, no core API available.
		$inserted = $wpdb->insert( MKM_RF_Install::table_name(), $row, $formats );

		if ( false === $inserted ) {
			return new WP_Error( 'mkm_rf_insert_failed', __( 'The entry could not be saved.', 'mkm-review-funnel' ) );
		}

		$id = (int) $wpdb->insert_id;

		/**
		 * Fires after an entry has been stored.
		 *
		 * @param int   $id  Entry id.
		 * @param array $row Stored values.
		 */
		do_action( 'mkm_rf_entry_created', $id, $row );

		return $id;
	}

	/**
	 * Fetches a single entry.
	 *
	 * @param int $id Entry id.
	 * @return array|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$table = MKM_RF_Install::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is not user input.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) ), ARRAY_A );

		return $row ? $row : null;
	}

	/**
	 * Builds the WHERE clause shared by query() and count().
	 *
	 * @param array $args Query args.
	 * @return array{0:string,1:array} SQL fragment and bind values.
	 */
	protected static function build_where( array $args ) {
		global $wpdb;

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['sentiment'] ) && in_array( $args['sentiment'], array( 'positive', 'negative' ), true ) ) {
			$where[]  = 'sentiment = %s';
			$values[] = $args['sentiment'];
		}

		if ( ! empty( $args['entry_type'] ) && in_array( $args['entry_type'], array( 'feedback', 'click' ), true ) ) {
			$where[]  = 'entry_type = %s';
			$values[] = $args['entry_type'];
		}

		if ( ! empty( $args['status'] ) && in_array( $args['status'], array( 'new', 'read', 'archived' ), true ) ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}

		if ( ! empty( $args['email'] ) ) {
			$where[]  = 'email = %s';
			$values[] = $args['email'];
		}

		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
			$where[]  = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR message LIKE %s)';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		return array( implode( ' AND ', $where ), $values );
	}

	/**
	 * Queries entries.
	 *
	 * @param array $args Query args: sentiment, entry_type, status, search, orderby, order, per_page, page.
	 * @return array[]
	 */
	public static function query( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'orderby'  => 'created_at',
				'order'    => 'DESC',
				'per_page' => 20,
				'page'     => 1,
			)
		);

		$table                 = MKM_RF_Install::table_name();
		list( $where, $binds ) = self::build_where( $args );

		// Whitelist ordering: never interpolate raw user input into SQL.
		$allowed_orderby = array( 'id', 'created_at', 'sentiment', 'status', 'email', 'last_name' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

		$per_page = max( 1, min( 500, absint( $args['per_page'] ) ) );
		$offset   = max( 0, ( max( 1, absint( $args['page'] ) ) - 1 ) * $per_page );

		$sql   = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$binds = array_merge( $binds, array( $per_page, $offset ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- prepared immediately below.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $binds ), ARRAY_A );

		return $rows ? $rows : array();
	}

	/**
	 * Counts entries matching the given filters.
	 *
	 * @param array $args Query args.
	 * @return int
	 */
	public static function count( array $args = array() ) {
		global $wpdb;

		$table                 = MKM_RF_Install::table_name();
		list( $where, $binds ) = self::build_where( $args );

		$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";

		if ( $binds ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- prepared here.
			$sql = $wpdb->prepare( $sql, $binds );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- prepared above when needed.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Updates the status of one entry.
	 *
	 * @param int    $id     Entry id.
	 * @param string $status new|read|archived.
	 * @return bool
	 */
	public static function set_status( $id, $status ) {
		global $wpdb;

		if ( ! in_array( $status, array( 'new', 'read', 'archived' ), true ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom table.
		return false !== $wpdb->update(
			MKM_RF_Install::table_name(),
			array( 'status' => $status ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Deletes one entry.
	 *
	 * @param int $id Entry id.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom table.
		return false !== $wpdb->delete( MKM_RF_Install::table_name(), array( 'id' => absint( $id ) ), array( '%d' ) );
	}

	/**
	 * Deletes every entry belonging to an email address. Used by the privacy eraser.
	 *
	 * @param string $email Email address.
	 * @return int Rows removed.
	 */
	public static function delete_by_email( $email ) {
		global $wpdb;

		$email = sanitize_email( $email );

		if ( ! $email ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- custom table.
		return (int) $wpdb->delete( MKM_RF_Install::table_name(), array( 'email' => $email ), array( '%s' ) );
	}

	/**
	 * Counts entries grouped by sentiment and type, for the admin summary.
	 *
	 * @return array{positive_clicks:int,negative_feedback:int,unread:int}
	 */
	public static function summary() {
		return array(
			'positive_clicks'   => self::count( array( 'entry_type' => 'click' ) ),
			'negative_feedback' => self::count(
				array(
					'entry_type' => 'feedback',
					'sentiment'  => 'negative',
				)
			),
			'unread'            => self::count(
				array(
					'entry_type' => 'feedback',
					'status'     => 'new',
				)
			),
		);
	}
}
