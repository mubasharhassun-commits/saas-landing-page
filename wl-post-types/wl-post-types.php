<?php
/**
 * Plugin Name:       WL Post Types (Waters Law)
 * Plugin URI:        https://waterslaw.com
 * Description:        Registers Vessels, Cases & News custom post types and adds two WPBakery Page Builder elements: "WL Cards" (grid or continuous auto-scrolling slider with hover overlay, slide-up title and Read More button) and "WL News" (featured post plus list). Also available as the [wl_cards] and [wl_news] shortcodes.
 * Version:           3.0.1
 * Author:            Waters Law Dev
 * Text Domain:       waterslaw
 * License:           GPL-2.0-or-later
 *
 * Slugs (vessels, cases) match your CPT UI types, so existing posts keep working.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =============================================================
 * 1. REGISTER POST TYPES
 * ============================================================= */
function waterslaw_register_post_types() {

	register_post_type( 'vessels', array(
		'labels' => array(
			'name'          => __( 'Vessels', 'waterslaw' ),
			'singular_name' => __( 'Vessel', 'waterslaw' ),
			'menu_name'     => __( 'Vessels', 'waterslaw' ),
			'add_new_item'  => __( 'Add New Vessel', 'waterslaw' ),
			'edit_item'     => __( 'Edit Vessel', 'waterslaw' ),
			'all_items'     => __( 'All Vessels', 'waterslaw' ),
			'not_found'     => __( 'No vessels found', 'waterslaw' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_position' => 25,
		'menu_icon'     => 'dashicons-anchor',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'       => array( 'slug' => 'vessels' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'cases', array(
		'labels' => array(
			'name'          => __( 'Cases', 'waterslaw' ),
			'singular_name' => __( 'Case', 'waterslaw' ),
			'menu_name'     => __( 'Cases', 'waterslaw' ),
			'add_new_item'  => __( 'Add New Case', 'waterslaw' ),
			'edit_item'     => __( 'Edit Case', 'waterslaw' ),
			'all_items'     => __( 'All Cases', 'waterslaw' ),
			'not_found'     => __( 'No cases found', 'waterslaw' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_position' => 26,
		'menu_icon'     => 'dashicons-book',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'       => array( 'slug' => 'cases' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'news', array(
		'labels' => array(
			'name'          => __( 'News', 'waterslaw' ),
			'singular_name' => __( 'News Item', 'waterslaw' ),
			'menu_name'     => __( 'News', 'waterslaw' ),
			'add_new_item'  => __( 'Add News Item', 'waterslaw' ),
			'edit_item'     => __( 'Edit News Item', 'waterslaw' ),
			'all_items'     => __( 'All News', 'waterslaw' ),
			'not_found'     => __( 'No news found', 'waterslaw' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_position' => 27,
		'menu_icon'     => 'dashicons-megaphone',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
		'taxonomies'    => array( 'news_category' ),
		'rewrite'       => array( 'slug' => 'news' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'waterslaw_register_post_types' );

/* Optional: category taxonomy for Cases. Delete if unused. */
function waterslaw_register_taxonomies() {
	register_taxonomy( 'case_category', 'cases', array(
		'labels'            => array(
			'name'          => __( 'Case Categories', 'waterslaw' ),
			'singular_name' => __( 'Case Category', 'waterslaw' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'case-category' ),
	) );

	register_taxonomy( 'news_category', 'news', array(
		'labels'            => array(
			'name'          => __( 'News Categories', 'waterslaw' ),
			'singular_name' => __( 'News Category', 'waterslaw' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'news-category' ),
	) );
}
add_action( 'init', 'waterslaw_register_taxonomies' );

define( 'WL_VERSION', '3.0.1' );
define( 'WL_PATH', plugin_dir_path( __FILE__ ) );
define( 'WL_URL', plugin_dir_url( __FILE__ ) );

require_once WL_PATH . 'includes/wl-card-meta.php';
require_once WL_PATH . 'includes/class-wl-style.php';
require_once WL_PATH . 'includes/class-wl-assets.php';
require_once WL_PATH . 'includes/class-wl-render.php';
require_once WL_PATH . 'includes/class-wl-shortcodes.php';
require_once WL_PATH . 'includes/class-wl-wpbakery.php';

/**
 * Boot the display modules. Each registers its own hooks.
 */
function waterslaw_bootstrap() {
	WL_Assets::init();
	WL_Shortcodes::init();
	WL_WPBakery::init();
}
waterslaw_bootstrap();

/* =============================================================
 * 3. ACTIVATION / DEACTIVATION
 * ============================================================= */
function waterslaw_activate() {
	waterslaw_register_post_types();
	waterslaw_register_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'waterslaw_activate' );

function waterslaw_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'waterslaw_deactivate' );
