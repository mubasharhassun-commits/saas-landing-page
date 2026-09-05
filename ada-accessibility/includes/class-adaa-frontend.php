<?php
/**
 * Front-end output: assets, auto-injection and shortcode.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles everything the visitor sees.
 */
class ADAA_Frontend {

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_footer', array( $this, 'maybe_auto_inject' ), 99 );
		add_shortcode( 'ada_accessibility', array( $this, 'shortcode' ) );
	}

	/**
	 * Register (not enqueue) the assets so Elementor can enqueue them on demand.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'adaa',
			ADAA_URL . 'assets/css/ada-accessibility.css',
			array(),
			ADAA_VERSION
		);

		wp_register_script(
			'adaa',
			ADAA_URL . 'assets/js/ada-accessibility.js',
			array(),
			ADAA_VERSION,
			true
		);

		$settings = ADAA_Settings::get();

		if ( $settings['auto_inject'] ) {
			wp_enqueue_style( 'adaa' );
			wp_enqueue_script( 'adaa' );
		}
	}

	/**
	 * Enqueue in the head when the page being viewed places the toolbar itself.
	 *
	 * WPBakery stores its layout as shortcodes in post_content, so the content
	 * can be checked before rendering. Relying only on the render-time enqueue
	 * means the assets are requested after wp_head, and themes and optimisation
	 * layers routinely drop late styles.
	 *
	 * @return void
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post || empty( $post->post_content ) ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'ada_accessibility' ) ) {
			self::ensure_assets();
		}
	}

	/**
	 * Make sure the assets are on the page before markup is printed.
	 *
	 * @return void
	 */
	public static function ensure_assets() {
		if ( ! wp_style_is( 'adaa', 'enqueued' ) ) {
			wp_enqueue_style( 'adaa' );
		}
		if ( ! wp_script_is( 'adaa', 'enqueued' ) ) {
			wp_enqueue_script( 'adaa' );
		}
	}

	/**
	 * Print the toolbar in the footer when auto-injection is on.
	 *
	 * @return void
	 */
	public function maybe_auto_inject() {
		if ( ADAA_Renderer::has_printed() ) {
			return;
		}

		$settings = ADAA_Settings::get();

		if ( empty( $settings['auto_inject'] ) ) {
			return;
		}

		/**
		 * Allow themes to suppress the toolbar on specific templates.
		 *
		 * @param bool $show Whether to output the toolbar.
		 */
		if ( ! apply_filters( 'adaa_show_toolbar', true ) ) {
			return;
		}

		echo ADAA_Renderer::render( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the renderer.
	}

	/**
	 * [ada_accessibility] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		if ( ADAA_Renderer::has_printed() ) {
			return '';
		}

		self::ensure_assets();

		$content_atts = array(
			'panel_side'      => '',
			'button_position' => '',
			'show_skip'       => '',
			'show_contrast'   => '',
			'show_text'       => '',
			'show_reset'      => '',
			'label_skip'      => '',
			'label_contrast'  => '',
			'label_text'      => '',
			'label_reset'     => '',
			'label_close'     => '',
			'skip_target'     => '',
			'skip_offset'     => '',
			'z_index'         => '',
		);

		// Every style key is allowed through, or shortcode_atts() would strip it.
		$style_keys = array_fill_keys( array_keys( ADAA_Style::schema() ), '' );

		$atts = shortcode_atts( array_merge( $content_atts, $style_keys ), $atts, 'ada_accessibility' );

		// Style parameters are validated separately, not merged into settings.
		$style = ADAA_Style::build( ADAA_Style::schema(), $atts );

		foreach ( array_keys( $style_keys ) as $key ) {
			unset( $atts[ $key ] );
		}

		$settings = ADAA_Settings::get();
		$atts     = array_filter( $atts, array( $this, 'is_set' ) );

		// A builder writes yes/no; the settings expect 1/0.
		foreach ( array( 'show_skip', 'show_contrast', 'show_text', 'show_reset' ) as $flag ) {
			if ( isset( $atts[ $flag ] ) ) {
				$atts[ $flag ] = in_array( strtolower( (string) $atts[ $flag ] ), array( '1', 'yes', 'true', 'on' ), true ) ? 1 : 0;
			}
		}

		$args = ADAA_Settings::sanitize( array_merge( $settings, $atts ) );

		$args['inline_css'] = $style;

		return ADAA_Renderer::render( $args );
	}

	/**
	 * Keep only attributes the caller actually supplied. array_filter()'s
	 * default would also discard a deliberate "0".
	 *
	 * @param mixed $value Attribute value.
	 * @return bool
	 */
	public function is_set( $value ) {
		return '' !== $value && null !== $value;
	}
}
