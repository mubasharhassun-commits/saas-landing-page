<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Stored feedback is kept unless the site explicitly opted in to deletion,
 * so an accidental delete-and-reinstall does not lose client messages.
 *
 * @package MKM_Review_Funnel
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$mkm_rf_settings = get_option( 'mkm_rf_settings', array() );

if ( ! is_array( $mkm_rf_settings ) || empty( $mkm_rf_settings['delete_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;

$mkm_rf_table = $wpdb->prefix . 'mkm_review_feedback';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is built from the prefix, not user input.
$wpdb->query( "DROP TABLE IF EXISTS {$mkm_rf_table}" );

delete_option( 'mkm_rf_settings' );
delete_option( 'mkm_rf_db_version' );
