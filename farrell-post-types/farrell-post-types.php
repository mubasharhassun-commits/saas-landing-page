<?php
/**
 * Plugin Name:       Farrell Post Types
 * Plugin URI:        https://farrellcroft.com
 * Description:       Renders your posts as a featured story beside a list - the Trending Topics section - through the "Trending Topics" WPBakery element, an Elementor widget, or the [fc_topics] shortcode.
 * Version:           1.5.1
 * Requires PHP:      7.4
 * Author:            Rustam Ali Randhawa
 * Text Domain:       farrell
 * License:           GPL-2.0-or-later
 *
 * No post type of its own: stories are ordinary WordPress posts. The element
 * can be pointed at any registered post type through its source attribute,
 * and falls back to posts when that type is not registered.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FC_PT_VERSION', '1.5.1' );
define( 'FC_PT_DIR', plugin_dir_path( __FILE__ ) );
define( 'FC_PT_URL', plugin_dir_url( __FILE__ ) );

/* =============================================================
 * BOOT
 * ============================================================= */

/**
 * Translations, loaded on init rather than at file scope so the locale is
 * settled by the time they are asked for.
 */
function farrell_load_textdomain() {
	load_plugin_textdomain( 'farrell', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'farrell_load_textdomain' );

require_once FC_PT_DIR . 'includes/class-fc-style.php';
require_once FC_PT_DIR . 'includes/class-fc-assets.php';
require_once FC_PT_DIR . 'includes/class-fc-render.php';
require_once FC_PT_DIR . 'includes/class-fc-shortcodes.php';
require_once FC_PT_DIR . 'includes/class-fc-wpbakery.php';
require_once FC_PT_DIR . 'includes/class-fc-elementor.php';

FC_Assets::init();
FC_Shortcodes::init();
FC_WPBakery::init();
FC_Elementor::init();
