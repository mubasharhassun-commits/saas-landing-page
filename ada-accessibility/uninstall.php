<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * @package ADA_Accessibility
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'adaa_settings' );

// Multisite: clean each site in the network.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );

	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'adaa_settings' );
		restore_current_blog();
	}
}
