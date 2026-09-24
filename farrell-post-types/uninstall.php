<?php
/**
 * Uninstall.
 *
 * The plugin stores nothing: no options, no post meta, no custom tables, no
 * transients and no post type of its own. There is therefore nothing to clean
 * up, and nothing here deletes anyone's posts. The file exists so that the
 * intent is on the record rather than assumed, and so WordPress has a
 * guarded entry point rather than none.
 *
 * @package farrell
 */

// Only ever reached by WordPress during an uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
