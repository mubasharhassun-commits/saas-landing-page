<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Testimonials are content someone typed by hand, so by default they survive
 * uninstall - deleting the plugin to troubleshoot something should not destroy
 * them. Only the plugin's own options and meta are removed.
 *
 * To erase everything, add this to wp-config.php before deleting the plugin:
 *
 *     define( 'TM_REMOVE_ALL_DATA', true );
 *
 * @package TestimonialManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'tm_seeded_terms' );

if ( ! defined( 'TM_REMOVE_ALL_DATA' ) || ! TM_REMOVE_ALL_DATA ) {
	return;
}

$tm_posts = get_posts(
	array(
		'post_type'        => 'testimonial',
		'post_status'      => 'any',
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => true,
	)
);

foreach ( $tm_posts as $tm_post_id ) {
	wp_delete_post( $tm_post_id, true );
}

$tm_terms = get_terms(
	array(
		'taxonomy'   => 'testimonial_category',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);

if ( ! is_wp_error( $tm_terms ) ) {
	foreach ( $tm_terms as $tm_term_id ) {
		wp_delete_term( $tm_term_id, 'testimonial_category' );
	}
}
