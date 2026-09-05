<?php
/**
 * Personal data exporter and eraser for WordPress privacy tools.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin into the core privacy export/erase requests.
 */
class MKM_RF_Privacy {

	/**
	 * Registers the exporter and eraser.
	 */
	public function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Adds the exporter.
	 *
	 * @param array $exporters Registered exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['mkm-review-funnel'] = array(
			'exporter_friendly_name' => __( 'Review Funnel feedback', 'mkm-review-funnel' ),
			'callback'               => array( $this, 'export' ),
		);

		return $exporters;
	}

	/**
	 * Adds the eraser.
	 *
	 * @param array $erasers Registered erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['mkm-review-funnel'] = array(
			'eraser_friendly_name' => __( 'Review Funnel feedback', 'mkm-review-funnel' ),
			'callback'             => array( $this, 'erase' ),
		);

		return $erasers;
	}

	/**
	 * Exports every entry belonging to an email address.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number (unused; all rows are returned at once).
	 * @return array
	 */
	public function export( $email, $page = 1 ) {
		$entries = MKM_RF_Repository::query(
			array(
				'email'    => sanitize_email( $email ),
				'per_page' => 500,
				'page'     => 1,
			)
		);

		$export = array();

		foreach ( $entries as $entry ) {
			$export[] = array(
				'group_id'    => 'mkm-review-funnel',
				'group_label' => __( 'Review Funnel feedback', 'mkm-review-funnel' ),
				'item_id'     => 'mkm-rf-' . (int) $entry['id'],
				'data'        => array(
					array(
						'name'  => __( 'Submitted', 'mkm-review-funnel' ),
						'value' => $entry['created_at'],
					),
					array(
						'name'  => __( 'Name', 'mkm-review-funnel' ),
						'value' => trim( $entry['first_name'] . ' ' . $entry['last_name'] ),
					),
					array(
						'name'  => __( 'Email', 'mkm-review-funnel' ),
						'value' => $entry['email'],
					),
					array(
						'name'  => __( 'Phone', 'mkm-review-funnel' ),
						'value' => $entry['phone'],
					),
					array(
						'name'  => __( 'Message', 'mkm-review-funnel' ),
						'value' => $entry['message'],
					),
				),
			);
		}

		return array(
			'data' => $export,
			'done' => true,
		);
	}

	/**
	 * Deletes every entry belonging to an email address.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number (unused).
	 * @return array
	 */
	public function erase( $email, $page = 1 ) {
		$removed = MKM_RF_Repository::delete_by_email( $email );

		return array(
			'items_removed'  => $removed > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}
