<?php
/**
 * Removes plugin data when the administrator opted in to it.
 *
 * @package MKM_Review_Us
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$mkm_settings = get_option( 'mkm_review_us_settings', array() );

if ( empty( $mkm_settings['delete_data'] ) ) {
	return;
}

global $wpdb;

$mkm_table = $wpdb->prefix . 'review_feedback';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$mkm_table}" );

delete_option( 'mkm_review_us_settings' );
delete_option( 'mkm_review_us_db_version' );
