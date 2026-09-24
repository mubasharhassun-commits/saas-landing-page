<?php
/**
 * Plugin Name:       Farrell Post Types
 * Plugin URI:        https://farrellcroft.com
 * Description:       Registers the Trending Topics post type and renders it as a featured story beside a list, through the "Trending Topics" WPBakery element, an Elementor widget, or the [fc_topics] shortcode.
 * Version:           1.2.0
 * Requires PHP:      7.4
 * Author:            Rustam Ali Randhawa
 * Text Domain:       farrell
 * License:           GPL-2.0-or-later
 *
 * The post type slug stays "news" and its taxonomy "news_category" so posts
 * written before the rename keep their URLs and their terms. Only the labels
 * say Trending Topics.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FC_PT_VERSION', '1.2.0' );
define( 'FC_PT_DIR', plugin_dir_path( __FILE__ ) );
define( 'FC_PT_URL', plugin_dir_url( __FILE__ ) );

/* =============================================================
 * POST TYPE
 * ============================================================= */

function farrell_register_post_types() {
	register_post_type(
		'news',
		array(
			'labels'        => array(
				'name'          => __( 'Trending Topics', 'farrell' ),
				'singular_name' => __( 'Topic', 'farrell' ),
				'menu_name'     => __( 'Trending Topics', 'farrell' ),
				'add_new_item'  => __( 'Add New Topic', 'farrell' ),
				'edit_item'     => __( 'Edit Topic', 'farrell' ),
				'all_items'     => __( 'All Topics', 'farrell' ),
				'not_found'     => __( 'No topics found', 'farrell' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_position' => 25,
			'menu_icon'     => 'dashicons-megaphone',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'rewrite'       => array( 'slug' => 'news' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'farrell_register_post_types' );

function farrell_register_taxonomies() {
	register_taxonomy(
		'news_category',
		'news',
		array(
			'labels'            => array(
				'name'          => __( 'Topic Categories', 'farrell' ),
				'singular_name' => __( 'Topic Category', 'farrell' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'news-category' ),
		)
	);
}
add_action( 'init', 'farrell_register_taxonomies' );

/* =============================================================
 * BOOT
 * ============================================================= */

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
