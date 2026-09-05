<?php
/**
 * Dashboard screens: entries, single entry, settings, export and import.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

require_once MKM_RF_PATH . 'includes/class-mkm-rf-list-table.php';

/**
 * Admin UI.
 */
class MKM_RF_Admin {

	/**
	 * Entries screen hook suffix.
	 *
	 * @var string
	 */
	protected $entries_hook = '';

	/**
	 * The list table, built on the entries screen.
	 *
	 * @var MKM_RF_List_Table|null
	 */
	protected $list_table = null;

	/**
	 * Hooks the admin screens.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_mkm_rf_export', array( $this, 'handle_export' ) );
		add_action( 'admin_post_mkm_rf_import_gf', array( $this, 'handle_gravity_forms_import' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Adds the menu entries.
	 */
	public function register_menu() {
		$capability = MKM_RF_Plugin::capability();

		$unread = MKM_RF_Repository::count(
			array(
				'entry_type' => 'feedback',
				'status'     => 'new',
			)
		);

		$title = __( 'Review Funnel', 'mkm-review-funnel' );

		if ( $unread > 0 ) {
			$title .= sprintf(
				' <span class="update-plugins count-%1$d"><span class="plugin-count">%1$d</span></span>',
				(int) $unread
			);
		}

		$this->entries_hook = add_menu_page(
			__( 'Review Funnel', 'mkm-review-funnel' ),
			$title,
			$capability,
			'mkm-review-funnel',
			array( $this, 'render_entries_page' ),
			'dashicons-star-filled',
			26
		);

		add_submenu_page(
			'mkm-review-funnel',
			__( 'Entries', 'mkm-review-funnel' ),
			__( 'Entries', 'mkm-review-funnel' ),
			$capability,
			'mkm-review-funnel',
			array( $this, 'render_entries_page' )
		);

		add_submenu_page(
			'mkm-review-funnel',
			__( 'Review Funnel Settings', 'mkm-review-funnel' ),
			__( 'Settings', 'mkm-review-funnel' ),
			$capability,
			'mkm-review-funnel-settings',
			array( $this, 'render_settings_page' )
		);

		if ( $this->entries_hook ) {
			add_action( 'load-' . $this->entries_hook, array( $this, 'handle_actions' ) );
		}
	}

	/**
	 * Loads the admin stylesheet on our screens only.
	 *
	 * @param string $hook Current screen hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'mkm-review-funnel' ) ) {
			return;
		}

		wp_enqueue_style( 'mkm-rf-admin', MKM_RF_URL . 'assets/css/admin.css', array(), MKM_RF_VERSION );
	}

	/**
	 * Registers the settings with the Settings API.
	 */
	public function register_settings() {
		register_setting(
			'mkm_rf_settings_group',
			MKM_RF_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'MKM_RF_Settings', 'sanitize' ),
				'default'           => MKM_RF_Settings::defaults(),
			)
		);
	}

	/**
	 * Handles row and bulk actions before the entries screen renders.
	 */
	public function handle_actions() {
		if ( ! current_user_can( MKM_RF_Plugin::capability() ) ) {
			return;
		}

		$this->list_table = new MKM_RF_List_Table();

		$this->handle_bulk_actions();
		$this->handle_row_actions();
	}

	/**
	 * Applies a bulk action to the selected entries.
	 */
	protected function handle_bulk_actions() {
		$action = $this->list_table->current_action();

		if ( ! $action || ! in_array( $action, array( 'mark_read', 'archive', 'delete' ), true ) ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->list_table->_args['plural'] );

		$ids = isset( $_REQUEST['entry'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['entry'] ) ) : array();
		$ids = array_filter( $ids );

		if ( ! $ids ) {
			return;
		}

		foreach ( $ids as $id ) {
			switch ( $action ) {
				case 'mark_read':
					MKM_RF_Repository::set_status( $id, 'read' );
					break;
				case 'archive':
					MKM_RF_Repository::set_status( $id, 'archived' );
					break;
				case 'delete':
					MKM_RF_Repository::delete( $id );
					break;
			}
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => 'mkm-review-funnel',
					'mkm_rf_done'  => $action,
					'mkm_rf_count' => count( $ids ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Applies a single-row action.
	 */
	protected function handle_row_actions() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked below once an action is present.
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';

		if ( ! in_array( $action, array( 'archive', 'delete', 'read' ), true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified on the next line.
		$id = isset( $_GET['entry'] ) ? absint( wp_unslash( $_GET['entry'] ) ) : 0;

		check_admin_referer( 'mkm_rf_row_' . $id );

		if ( ! $id ) {
			return;
		}

		if ( 'delete' === $action ) {
			MKM_RF_Repository::delete( $id );
		} else {
			MKM_RF_Repository::set_status( $id, 'archive' === $action ? 'archived' : 'read' );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'mkm-review-funnel',
					'mkm_rf_done' => $action,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the entries screen, or a single entry.
	 */
	public function render_entries_page() {
		if ( ! current_user_can( MKM_RF_Plugin::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to view review funnel entries.', 'mkm-review-funnel' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified inside render_single_entry().
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'view' === $action ) {
			$this->render_single_entry();
			return;
		}

		if ( ! $this->list_table ) {
			$this->list_table = new MKM_RF_List_Table();
		}

		$this->list_table->prepare_items();

		$summary = MKM_RF_Repository::summary();
		?>
		<div class="wrap mkm-rf-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Review Funnel', 'mkm-review-funnel' ); ?></h1>
			<a href="<?php echo esc_url( $this->export_url() ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'mkm-review-funnel' ); ?></a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notice(); ?>

			<div class="mkm-rf-stats">
				<div class="mkm-rf-stat">
					<span class="mkm-rf-stat__value"><?php echo esc_html( number_format_i18n( $summary['negative_feedback'] ) ); ?></span>
					<span class="mkm-rf-stat__label"><?php esc_html_e( 'Private feedback received', 'mkm-review-funnel' ); ?></span>
				</div>
				<div class="mkm-rf-stat">
					<span class="mkm-rf-stat__value"><?php echo esc_html( number_format_i18n( $summary['positive_clicks'] ) ); ?></span>
					<span class="mkm-rf-stat__label"><?php esc_html_e( 'Click-throughs to review sites', 'mkm-review-funnel' ); ?></span>
				</div>
				<div class="mkm-rf-stat">
					<span class="mkm-rf-stat__value"><?php echo esc_html( number_format_i18n( $summary['unread'] ) ); ?></span>
					<span class="mkm-rf-stat__label"><?php esc_html_e( 'Unread', 'mkm-review-funnel' ); ?></span>
				</div>
			</div>

			<?php $this->list_table->views(); ?>

			<form method="get">
				<input type="hidden" name="page" value="mkm-review-funnel" />
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter.
				$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';
				?>
				<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>" />
				<?php $this->list_table->search_box( __( 'Search entries', 'mkm-review-funnel' ), 'mkm-rf-search' ); ?>
			</form>

			<form method="post">
				<?php
				wp_nonce_field( 'bulk-' . $this->list_table->_args['plural'] );
				$this->list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders one entry in full.
	 */
	protected function render_single_entry() {
		// Read-only screen, gated by capability. No nonce: the link in the
		// notification email has to keep working straight from an inbox.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nothing is mutated by user input here.
		$id = isset( $_GET['entry'] ) ? absint( wp_unslash( $_GET['entry'] ) ) : 0;

		$entry = $id ? MKM_RF_Repository::get( $id ) : null;

		if ( ! $entry ) {
			wp_die( esc_html__( 'That entry no longer exists.', 'mkm-review-funnel' ) );
		}

		if ( 'new' === $entry['status'] ) {
			MKM_RF_Repository::set_status( $id, 'read' );
			$entry['status'] = 'read';
		}

		$fields = array(
			__( 'Received', 'mkm-review-funnel' )      => $entry['created_at'],
			__( 'Sentiment', 'mkm-review-funnel' )     => $entry['sentiment'],
			__( 'Name', 'mkm-review-funnel' )          => trim( $entry['first_name'] . ' ' . $entry['last_name'] ),
			__( 'Email', 'mkm-review-funnel' )         => $entry['email'],
			__( 'Phone', 'mkm-review-funnel' )         => $entry['phone'],
			__( 'Client status', 'mkm-review-funnel' ) => $entry['client_status'],
			__( 'Submitted from', 'mkm-review-funnel' ) => $entry['source_url'],
			__( 'Referrer', 'mkm-review-funnel' )      => $entry['referrer'],
			__( 'IP', 'mkm-review-funnel' )            => $entry['ip_address'],
			__( 'User agent', 'mkm-review-funnel' )    => $entry['user_agent'],
		);
		?>
		<div class="wrap mkm-rf-admin">
			<h1><?php esc_html_e( 'Feedback entry', 'mkm-review-funnel' ); ?></h1>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mkm-review-funnel' ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to entries', 'mkm-review-funnel' ); ?>
				</a>
			</p>

			<table class="widefat striped mkm-rf-entry">
				<tbody>
				<?php foreach ( $fields as $label => $value ) : ?>
					<tr>
						<th scope="row" style="width:200px;"><?php echo esc_html( $label ); ?></th>
						<td><?php echo '' === trim( (string) $value ) ? '&mdash;' : esc_html( $value ); ?></td>
					</tr>
				<?php endforeach; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Message', 'mkm-review-funnel' ); ?></th>
						<td><?php echo nl2br( esc_html( $entry['message'] ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Consent', 'mkm-review-funnel' ); ?></th>
						<td>
							<?php if ( $entry['consent'] ) : ?>
								<p><strong><?php esc_html_e( 'Accepted', 'mkm-review-funnel' ); ?></strong></p>
								<p class="description"><?php echo esc_html( $entry['consent_text'] ); ?></p>
							<?php else : ?>
								<?php esc_html_e( 'Not accepted', 'mkm-review-funnel' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				<?php if ( $entry['email'] ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( 'mailto:' . $entry['email'] ); ?>">
						<?php esc_html_e( 'Reply by email', 'mkm-review-funnel' ); ?>
					</a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=mkm-review-funnel&action=archive&entry=' . $id ), 'mkm_rf_row_' . $id ) ); ?>">
					<?php esc_html_e( 'Archive', 'mkm-review-funnel' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Renders the notice after a row or bulk action.
	 */
	protected function render_admin_notice() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only message.
		$done = isset( $_GET['mkm_rf_done'] ) ? sanitize_key( wp_unslash( $_GET['mkm_rf_done'] ) ) : '';

		if ( ! $done ) {
			return;
		}

		$messages = array(
			'mark_read' => __( 'Entries marked as read.', 'mkm-review-funnel' ),
			'read'      => __( 'Entry marked as read.', 'mkm-review-funnel' ),
			'archive'   => __( 'Entry archived.', 'mkm-review-funnel' ),
			'delete'    => __( 'Entries deleted.', 'mkm-review-funnel' ),
			'imported'  => __( 'Gravity Forms entries imported.', 'mkm-review-funnel' ),
		);

		if ( ! isset( $messages[ $done ] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( $messages[ $done ] )
		);
	}

	/**
	 * Nonce-protected CSV export URL for the current view.
	 *
	 * @return string
	 */
	protected function export_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter.
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'feedback';

		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'mkm_rf_export',
					'view'   => $view,
				),
				admin_url( 'admin-post.php' )
			),
			'mkm_rf_export'
		);
	}

	/**
	 * Streams the entries as CSV.
	 */
	public function handle_export() {
		if ( ! current_user_can( MKM_RF_Plugin::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to export entries.', 'mkm-review-funnel' ) );
		}

		check_admin_referer( 'mkm_rf_export' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified above.
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'feedback';

		$args = array( 'entry_type' => 'clicks' === $view ? 'click' : 'feedback' );

		if ( 'archived' === $view ) {
			$args['status'] = 'archived';
		} elseif ( 'new' === $view ) {
			$args['status'] = 'new';
		}

		$filename = sprintf( 'review-funnel-%s-%s.csv', $view, gmdate( 'Y-m-d' ) );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$out = fopen( 'php://output', 'w' );

		$columns = array( 'id', 'created_at', 'sentiment', 'entry_type', 'destination', 'first_name', 'last_name', 'email', 'phone', 'client_status', 'message', 'consent', 'status', 'source_url' );

		fputcsv( $out, $columns );

		// Page through the whole set rather than capping the export.
		$per_page = 200;
		$page     = 1;

		do {
			$rows = MKM_RF_Repository::query(
				array_merge(
					$args,
					array(
						'per_page' => $per_page,
						'page'     => $page,
					)
				)
			);

			foreach ( $rows as $row ) {
				$line = array();

				foreach ( $columns as $column ) {
					$line[] = self::csv_cell( isset( $row[ $column ] ) ? $row[ $column ] : '' );
				}

				fputcsv( $out, $line );
			}

			$page++;
		} while ( count( $rows ) === $per_page );

		fclose( $out );
		exit;
	}

	/**
	 * Neutralises spreadsheet formula injection in exported values.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	protected static function csv_cell( $value ) {
		$value = (string) $value;

		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}

		return $value;
	}

	/**
	 * Imports existing entries from a Gravity Forms form.
	 */
	public function handle_gravity_forms_import() {
		if ( ! current_user_can( MKM_RF_Plugin::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to import entries.', 'mkm-review-funnel' ) );
		}

		check_admin_referer( 'mkm_rf_import_gf' );

		$redirect = admin_url( 'admin.php?page=mkm-review-funnel-settings' );

		if ( ! class_exists( 'GFAPI' ) ) {
			wp_safe_redirect( add_query_arg( 'mkm_rf_import', 'no-gf', $redirect ) );
			exit;
		}

		$form_id = isset( $_POST['gf_form_id'] ) ? absint( wp_unslash( $_POST['gf_form_id'] ) ) : 0;

		if ( ! $form_id ) {
			wp_safe_redirect( add_query_arg( 'mkm_rf_import', 'no-form', $redirect ) );
			exit;
		}

		$entries = GFAPI::get_entries( $form_id, array(), null, array( 'offset' => 0, 'page_size' => 500 ) );

		if ( is_wp_error( $entries ) ) {
			wp_safe_redirect( add_query_arg( 'mkm_rf_import', 'error', $redirect ) );
			exit;
		}

		$form   = GFAPI::get_form( $form_id );
		$map    = $this->map_gravity_fields( $form );
		$count  = 0;

		foreach ( $entries as $entry ) {
			$get = static function ( $key ) use ( $entry, $map ) {
				return isset( $map[ $key ], $entry[ $map[ $key ] ] ) ? (string) $entry[ $map[ $key ] ] : '';
			};

			$stored = MKM_RF_Repository::insert(
				array(
					'sentiment'     => 'negative',
					'entry_type'    => 'feedback',
					'first_name'    => sanitize_text_field( $get( 'first_name' ) ),
					'last_name'     => sanitize_text_field( $get( 'last_name' ) ),
					'email'         => sanitize_email( $get( 'email' ) ),
					'phone'         => sanitize_text_field( $get( 'phone' ) ),
					'client_status' => sanitize_text_field( $get( 'client_status' ) ),
					'message'       => sanitize_textarea_field( $get( 'message' ) ),
					'consent'       => 1,
					'consent_text'  => (string) MKM_RF_Settings::get( 'consent_text' ),
					'source_url'    => isset( $entry['source_url'] ) ? esc_url_raw( $entry['source_url'] ) : '',
					'status'        => 'read',
				)
			);

			if ( ! is_wp_error( $stored ) ) {
				$count++;
			}
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'mkm_rf_import' => 'done',
					'mkm_rf_count'  => $count,
				),
				$redirect
			)
		);
		exit;
	}

	/**
	 * Guesses which Gravity Forms field holds which value, by field type and label.
	 *
	 * @param array|false $form Gravity Forms form definition.
	 * @return array<string,string>
	 */
	protected function map_gravity_fields( $form ) {
		$map = array();

		if ( ! is_array( $form ) || empty( $form['fields'] ) ) {
			return $map;
		}

		foreach ( $form['fields'] as $field ) {
			$id    = isset( $field->id ) ? (string) $field->id : '';
			$type  = isset( $field->type ) ? (string) $field->type : '';
			$label = isset( $field->label ) ? strtolower( (string) $field->label ) : '';

			if ( '' === $id ) {
				continue;
			}

			if ( 'email' === $type && ! isset( $map['email'] ) ) {
				$map['email'] = $id;
			} elseif ( 'phone' === $type && ! isset( $map['phone'] ) ) {
				$map['phone'] = $id;
			} elseif ( 'textarea' === $type && ! isset( $map['message'] ) ) {
				$map['message'] = $id;
			} elseif ( 'select' === $type && ! isset( $map['client_status'] ) ) {
				$map['client_status'] = $id;
			} elseif ( false !== strpos( $label, 'first' ) && ! isset( $map['first_name'] ) ) {
				$map['first_name'] = $id;
			} elseif ( false !== strpos( $label, 'last' ) && ! isset( $map['last_name'] ) ) {
				$map['last_name'] = $id;
			}
		}

		return $map;
	}

	/**
	 * Renders the settings screen.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( MKM_RF_Plugin::capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'mkm-review-funnel' ) );
		}

		require MKM_RF_PATH . 'includes/views/settings.php';
	}
}
