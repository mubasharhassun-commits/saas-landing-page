<?php
/**
 * Entries list table.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Lists review funnel entries in the dashboard.
 */
class MKM_RF_List_Table extends WP_List_Table {

	/**
	 * Counts per view, filled by prepare_items().
	 *
	 * @var array
	 */
	protected $counts = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'mkm_rf_entry',
				'plural'   => 'mkm_rf_entries',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'mkm-review-funnel' ),
			'sentiment'  => __( 'Type', 'mkm-review-funnel' ),
			'contact'    => __( 'Contact', 'mkm-review-funnel' ),
			'message'    => __( 'Message', 'mkm-review-funnel' ),
			'status'     => __( 'Status', 'mkm-review-funnel' ),
			'created_at' => __( 'Received', 'mkm-review-funnel' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
			'sentiment'  => array( 'sentiment', false ),
			'status'     => array( 'status', false ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			'mark_read' => __( 'Mark as read', 'mkm-review-funnel' ),
			'archive'   => __( 'Archive', 'mkm-review-funnel' ),
			'delete'    => __( 'Delete permanently', 'mkm-review-funnel' ),
		);
	}

	/**
	 * The current view filter.
	 *
	 * @return string
	 */
	protected function current_view() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'feedback';

		return in_array( $view, array( 'feedback', 'new', 'archived', 'clicks' ), true ) ? $view : 'feedback';
	}

	/**
	 * Translates the current view into repository query args.
	 *
	 * @param string $view View slug.
	 * @return array
	 */
	protected function view_args( $view ) {
		switch ( $view ) {
			case 'new':
				return array(
					'entry_type' => 'feedback',
					'status'     => 'new',
				);
			case 'archived':
				return array(
					'entry_type' => 'feedback',
					'status'     => 'archived',
				);
			case 'clicks':
				return array( 'entry_type' => 'click' );
			default:
				return array( 'entry_type' => 'feedback' );
		}
	}

	/**
	 * View links above the table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$current = $this->current_view();
		$base    = admin_url( 'admin.php?page=mkm-review-funnel' );

		$views = array(
			'feedback' => __( 'All feedback', 'mkm-review-funnel' ),
			'new'      => __( 'Unread', 'mkm-review-funnel' ),
			'archived' => __( 'Archived', 'mkm-review-funnel' ),
			'clicks'   => __( 'Review clicks', 'mkm-review-funnel' ),
		);

		$out = array();

		foreach ( $views as $slug => $label ) {
			$count = MKM_RF_Repository::count( $this->view_args( $slug ) );
			$url   = add_query_arg( 'view', $slug, $base );

			$out[ $slug ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$current === $slug ? ' class="current" aria-current="page"' : '',
				esc_html( $label ),
				esc_html( number_format_i18n( $count ) )
			);
		}

		return $out;
	}

	/**
	 * Loads the rows.
	 */
	public function prepare_items() {
		$per_page = 20;
		$view     = $this->current_view();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
		$search  = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		$order   = isset( $_REQUEST['order'] ) ? sanitize_key( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$args = array_merge(
			$this->view_args( $view ),
			array(
				'search'   => $search,
				'orderby'  => $orderby,
				'order'    => $order,
				'per_page' => $per_page,
				'page'     => $this->get_pagenum(),
			)
		);

		$total = MKM_RF_Repository::count( $args );

		$this->items = MKM_RF_Repository::query( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'name' );
	}

	/**
	 * Message shown when there is nothing to list.
	 */
	public function no_items() {
		esc_html_e( 'No entries yet.', 'mkm-review-funnel' );
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="entry[]" value="%d" />', (int) $item['id'] );
	}

	/**
	 * Name column with row actions.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_name( $item ) {
		$id   = (int) $item['id'];
		$name = trim( $item['first_name'] . ' ' . $item['last_name'] );

		if ( '' === $name ) {
			$name = 'click' === $item['entry_type']
				? __( '(review click)', 'mkm-review-funnel' )
				: __( '(no name)', 'mkm-review-funnel' );
		}

		$view_url = admin_url( 'admin.php?page=mkm-review-funnel&action=view&entry=' . $id );

		$actions = array();

		if ( 'feedback' === $item['entry_type'] ) {
			$actions['view'] = sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), esc_html__( 'View', 'mkm-review-funnel' ) );

			$actions['archive'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					wp_nonce_url(
						admin_url( 'admin.php?page=mkm-review-funnel&action=archive&entry=' . $id ),
						'mkm_rf_row_' . $id
					)
				),
				esc_html__( 'Archive', 'mkm-review-funnel' )
			);
		}

		$actions['delete'] = sprintf(
			'<a href="%s" class="submitdelete" onclick="return confirm(\'%s\');">%s</a>',
			esc_url(
				wp_nonce_url(
					admin_url( 'admin.php?page=mkm-review-funnel&action=delete&entry=' . $id ),
					'mkm_rf_row_' . $id
				)
			),
			esc_js( __( 'Delete this entry permanently?', 'mkm-review-funnel' ) ),
			esc_html__( 'Delete', 'mkm-review-funnel' )
		);

		$label = 'feedback' === $item['entry_type']
			? sprintf( '<a href="%s"><strong>%s</strong></a>', esc_url( $view_url ), esc_html( $name ) )
			: '<strong>' . esc_html( $name ) . '</strong>';

		if ( 'new' === $item['status'] && 'feedback' === $item['entry_type'] ) {
			$label .= ' <span class="mkm-rf-badge mkm-rf-badge--new">' . esc_html__( 'New', 'mkm-review-funnel' ) . '</span>';
		}

		return $label . $this->row_actions( $actions );
	}

	/**
	 * Type column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_sentiment( $item ) {
		if ( 'click' === $item['entry_type'] ) {
			$destination = $item['destination'] ? $item['destination'] : __( 'unknown', 'mkm-review-funnel' );

			return sprintf(
				'<span class="mkm-rf-badge mkm-rf-badge--click">%s</span>',
				esc_html( sprintf( /* translators: %s: review site slug. */ __( 'Click: %s', 'mkm-review-funnel' ), $destination ) )
			);
		}

		$class = 'positive' === $item['sentiment'] ? 'positive' : 'negative';
		$label = 'positive' === $item['sentiment']
			? __( 'Positive', 'mkm-review-funnel' )
			: __( 'Negative', 'mkm-review-funnel' );

		return sprintf( '<span class="mkm-rf-badge mkm-rf-badge--%s">%s</span>', esc_attr( $class ), esc_html( $label ) );
	}

	/**
	 * Contact column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_contact( $item ) {
		$parts = array();

		if ( $item['email'] ) {
			$parts[] = sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $item['email'] ) );
		}

		if ( $item['phone'] ) {
			$parts[] = esc_html( $item['phone'] );
		}

		return $parts ? implode( '<br />', $parts ) : '&mdash;';
	}

	/**
	 * Message excerpt column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_message( $item ) {
		if ( '' === trim( (string) $item['message'] ) ) {
			return '&mdash;';
		}

		return esc_html( wp_trim_words( $item['message'], 18, '…' ) );
	}

	/**
	 * Status column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_status( $item ) {
		$labels = array(
			'new'      => __( 'New', 'mkm-review-funnel' ),
			'read'     => __( 'Read', 'mkm-review-funnel' ),
			'archived' => __( 'Archived', 'mkm-review-funnel' ),
		);

		return isset( $labels[ $item['status'] ] ) ? esc_html( $labels[ $item['status'] ] ) : esc_html( $item['status'] );
	}

	/**
	 * Received column.
	 *
	 * @param array $item Row.
	 * @return string
	 */
	protected function column_created_at( $item ) {
		$timestamp = strtotime( $item['created_at'] );

		if ( ! $timestamp ) {
			return '&mdash;';
		}

		return esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
	}

	/**
	 * Fallback for any other column.
	 *
	 * @param array  $item        Row.
	 * @param string $column_name Column key.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}
}
