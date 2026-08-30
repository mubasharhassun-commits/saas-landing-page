<?php
/**
 * Plugin Name:       Testimonial Manager
 * Plugin URI:        https://waterslaw.com
 * Description:       Create and manage client testimonials from the WordPress dashboard, and display them as a responsive grid with an accessible "read full review" modal. Works via shortcode anywhere, and as a native Elementor widget when Elementor is active.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Waters Law Dev
 * Text Domain:       testimonial-manager
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package TestimonialManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TM_VERSION', '1.0.1' );
define( 'TM_FILE', __FILE__ );
define( 'TM_PATH', plugin_dir_path( __FILE__ ) );
define( 'TM_URL', plugin_dir_url( __FILE__ ) );

require_once TM_PATH . 'includes/class-tm-post-type.php';
require_once TM_PATH . 'includes/class-tm-meta-fields.php';
require_once TM_PATH . 'includes/class-tm-query.php';
require_once TM_PATH . 'includes/class-tm-assets.php';
require_once TM_PATH . 'includes/class-tm-renderer.php';
require_once TM_PATH . 'includes/class-tm-shortcode.php';
require_once TM_PATH . 'includes/class-tm-elementor.php';

/**
 * Boot every module. Each one registers its own hooks.
 */
function tm_bootstrap() {
	TM_Post_Type::init();
	TM_Meta_Fields::init();
	TM_Assets::init();
	TM_Renderer::init();
	TM_Shortcode::init();
	TM_Elementor::init();
}
tm_bootstrap();

/**
 * Load translations.
 */
function tm_load_textdomain() {
	load_plugin_textdomain( 'testimonial-manager', false, dirname( plugin_basename( TM_FILE ) ) . '/languages' );
}
add_action( 'init', 'tm_load_textdomain' );

/**
 * Activation: register the post type so rewrite rules can be flushed, and seed
 * the example categories once (never again, so deleted terms stay deleted).
 */
function tm_activate() {
	TM_Post_Type::register();

	if ( ! get_option( 'tm_seeded_terms' ) ) {
		$examples = array(
			__( 'Maritime Law', 'testimonial-manager' ),
			__( 'Personal Injury', 'testimonial-manager' ),
			__( 'Employment Law', 'testimonial-manager' ),
			__( 'Family Law', 'testimonial-manager' ),
			__( 'Criminal Law', 'testimonial-manager' ),
		);
		foreach ( $examples as $name ) {
			if ( ! term_exists( $name, TM_Post_Type::TAXONOMY ) ) {
				wp_insert_term( $name, TM_Post_Type::TAXONOMY );
			}
		}
		update_option( 'tm_seeded_terms', 1 );
	}

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tm_activate' );

/**
 * Deactivation: drop the rewrite rules this plugin added. Content is untouched.
 */
function tm_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tm_deactivate' );
