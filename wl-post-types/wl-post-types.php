<?php
/**
 * Plugin Name:       WL Post Types (Waters Law)
 * Plugin URI:        https://waterslaw.com
 * Description:        Registers Vessels & Cases custom post types and adds a native Elementor widget ("WL Cards") to display them as a responsive grid or continuous auto-scrolling slider with hover overlay + slide-up title + Read More button. Works on FREE Elementor.
 * Version:           2.5.0
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

/* Per-post card text + link settings (admin fields + resolvers). */
require_once plugin_dir_path( __FILE__ ) . 'includes/wl-card-meta.php';

/* =============================================================
 * 2. ELEMENTOR WIDGET
 * ============================================================= */
add_action( 'elementor/elements/categories_registered', function( $mgr ) {
	$mgr->add_category( 'waterslaw', array(
		'title' => __( 'Waters Law', 'waterslaw' ),
		'icon'  => 'fa fa-anchor',
	) );
} );

add_action( 'elementor/widgets/register', function( $widgets_manager ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wl-cards-widget.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wl-news-widget.php';
	$widgets_manager->register( new \WL_Cards_Widget() );
	$widgets_manager->register( new \WL_News_Widget() );
} );

/* Front-end + editor CSS. */
function waterslaw_assets() {
	wp_register_style( 'waterslaw-cards', false );
	wp_enqueue_style( 'waterslaw-cards' );
	wp_add_inline_style( 'waterslaw-cards', waterslaw_inline_css() );
}
add_action( 'wp_enqueue_scripts', 'waterslaw_assets' );
add_action( 'elementor/preview/enqueue_scripts', 'waterslaw_assets' );

function waterslaw_inline_css() {
	return '
	.wl-cards{position:relative;width:100%;--active-cols:var(--wl-cols,4)}
	@media(max-width:1300px){.wl-cards{--active-cols:var(--wl-cols-l,var(--wl-cols,4))}}
	@media(max-width:1024px){.wl-cards{--active-cols:var(--wl-cols-t,2)}}
	@media(max-width:767px){.wl-cards{--active-cols:var(--wl-cols-m,1)}}

	/* GRID */
	.wl-grid{display:grid;grid-template-columns:repeat(var(--active-cols,4),1fr);gap:var(--wl-gap,8px)}

	/* CONTINUOUS SLIDER (marquee) */
	.wl-marquee-wrap{container-type:inline-size;overflow:hidden;width:100%}
	.wl-marquee{display:flex;width:max-content;animation:wl-scroll var(--wl-duration,30s) linear infinite}
	.wl-marquee-wrap:hover .wl-marquee{animation-play-state:var(--wl-pause,paused)}
	.wl-marquee .wl-card{flex:0 0 calc(100cqw / var(--active-cols,4) - var(--wl-gap,8px));margin-right:var(--wl-gap,8px)}
	@keyframes wl-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
	@media(prefers-reduced-motion:reduce){.wl-marquee{animation:none}}

	/* CARD */
	.wl-card{position:relative;display:block;text-decoration:none;overflow:hidden;border-radius:var(--wl-radius,0)}
	.wl-card-static{cursor:default}
	.wl-card-img{position:relative;width:100%;aspect-ratio:var(--wl-ratio,3/4);background-size:cover;background-position:center;display:flex;align-items:flex-end;justify-content:center;overflow:hidden}
	.wl-card-ov{position:absolute;inset:0;z-index:1;transition:opacity .4s ease}
	.wl-ov-normal{opacity:1;background:linear-gradient(to top,var(--wl-overlay,rgba(10,25,40,.85)) 0%,rgba(10,25,40,.1) 55%,rgba(10,25,40,0) 100%)}
	.wl-ov-normal-img{opacity:var(--wl-ov-n-op,1)}
	.wl-ov-hover{opacity:0;background:var(--wl-hover-overlay,rgba(0,0,0,.55))}
	.wl-ov-hover-img{opacity:0}
	.wl-card:hover .wl-ov-normal,.wl-card:hover .wl-ov-normal-img{opacity:0}
	.wl-card:hover .wl-ov-hover{opacity:1}
	.wl-card:hover .wl-ov-hover-img{opacity:var(--wl-ov-h-op,1)}
	.wl-card-title{position:relative;z-index:2;color:var(--wl-title,#fff);text-align:center;margin:0 0 18px;padding:0 12px;font-size:var(--wl-title-size,18px);font-weight:600;line-height:1.25;transition:transform .4s ease}
	.wl-card:hover .wl-card-title{transform:translateY(-10px)}
	.wl-card-btn{position:absolute;z-index:3;top:50%;left:50%;transform:translate(-50%,-35%);opacity:0;transition:opacity .4s ease,transform .4s ease;color:#fff;border:1px solid #fff;padding:9px 22px;font-size:13px;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap;background:rgba(0,0,0,.15)}
	.wl-card:hover .wl-card-btn{opacity:1;transform:translate(-50%,-50%)}
	.wl-cards.wl-hide-title .wl-card:hover .wl-card-title{opacity:0;transform:translateY(14px)}
	@media(max-width:767px){.wl-card-title{font-size:15px}}

	/* ================= WL NEWS ================= */
	.wl-news{display:grid;grid-template-columns:1fr 1fr;gap:34px;align-items:stretch;--wl-accent:#7a9a98;--wl-news-title:#1a2b3c}
	/* Tablet & down: stack featured + list full width */
	@media(max-width:1024px){.wl-news{grid-template-columns:1fr;gap:26px}}
	/* Mobile: also stack each list item (thumb on top) */
	@media(max-width:767px){.wl-news{gap:22px}.wl-news-item{flex-direction:column}.wl-news-thumb{flex-basis:auto;width:100%;height:180px}}
	.wl-news-featured{position:relative;display:flex;align-items:flex-end;min-height:340px;border-radius:4px;overflow:hidden;text-decoration:none;background-size:cover;background-position:center}
	.wl-news-featured-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.1) 60%,transparent 100%)}
	.wl-news-featured-body{position:relative;z-index:2;padding:24px}
	.wl-news-featured-title{color:#fff;font-size:22px;font-weight:700;line-height:1.25;margin:0 0 10px}
	.wl-news-list{display:flex;flex-direction:column;gap:24px;justify-content:center}
	.wl-news-item{display:flex;gap:16px;align-items:flex-start}
	.wl-news-thumb{flex:0 0 92px;height:92px;border-radius:4px;background-size:cover;background-position:center;display:block}
	.wl-news-body{flex:1;min-width:0}
	.wl-news-title{display:block;color:var(--wl-news-title);font-size:17px;font-weight:700;line-height:1.3;text-decoration:none;margin-bottom:8px}
	.wl-news-title:hover{color:var(--wl-accent)}
	.wl-news-meta{display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:#8a8a8a;margin-bottom:10px}
	.wl-news-meta.light{color:rgba(255,255,255,.85)}
	.wl-news-meta .wl-m{display:inline-flex;align-items:center;gap:5px}
	.wl-news-meta .wl-m svg{opacity:.85;flex:0 0 auto}
	.wl-news-readmore{display:inline-block;font-size:12px;letter-spacing:.5px;text-transform:uppercase;color:var(--wl-news-title);text-decoration:none;border:1px solid #d9d9d9;padding:7px 16px;transition:.25s}
	.wl-news-readmore:hover{background:var(--wl-accent);border-color:var(--wl-accent);color:#fff}
	';
}

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
