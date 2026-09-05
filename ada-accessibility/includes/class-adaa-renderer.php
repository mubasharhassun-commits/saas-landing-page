<?php
/**
 * Builds the toolbar markup. Shared by the auto-injected instance,
 * the shortcode and the Elementor widget so there is one source of truth.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Markup builder.
 */
class ADAA_Renderer {

	/**
	 * Guard so only one toolbar can ever print on a page.
	 *
	 * @var bool
	 */
	protected static $printed = false;

	/**
	 * Whether a toolbar has already been output.
	 *
	 * @return bool
	 */
	public static function has_printed() {
		return self::$printed;
	}

	/**
	 * Original line-art icons, drawn for this plugin.
	 *
	 * @param string $name Icon key.
	 * @return string Raw SVG markup.
	 */
	protected static function icon( $name, $custom = array() ) {
		if ( ! empty( $custom[ $name ] ) ) {
			return $custom[ $name ];
		}

		$open  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
		$close = '</svg>';

		if ( 'launcher' === $name ) {
			/*
			 * The glyph carries its own disc: the circle is the icon colour
			 * and the path is punched through it in the button colour, which
			 * is how the reference button is drawn. Both are coloured from
			 * CSS so the Elementor controls still apply.
			 */
			return '<svg viewBox="0 0 36 36" aria-hidden="true" focusable="false">'
				. '<circle class="adaa__glyph-disc" cx="17.99" cy="17.995" r="18"/>'
				. '<path class="adaa__glyph-mark" d="M17.991 35.995C8.068 35.995-0.002 27.922-0.002 17.996C-0.002 8.069 8.068-0.005 17.991-0.005C27.915-0.005 35.987 8.069 35.987 17.996C35.987 27.922 27.915 35.995 17.991 35.995ZM17.941 7.171C16.591 7.171 15.492 8.276 15.492 9.634C15.492 10.992 16.591 12.097 17.941 12.097C19.29 12.097 20.389 10.992 20.389 9.634C20.389 8.276 19.29 7.171 17.941 7.171ZM24.798 12.89L24.66 12.897L19.503 13.632L16.471 13.638L11.27 12.903L11.091 12.89C10.368 12.89 9.736 13.502 9.623 14.314C9.567 14.722 9.647 15.128 9.849 15.461C10.07 15.827 10.426 16.067 10.819 16.122L15.692 16.812L15.692 19.591L12.832 26.532C12.491 27.361 12.885 28.315 13.709 28.658C13.905 28.738 14.111 28.779 14.322 28.779C14.98 28.779 15.568 28.385 15.821 27.776L17.941 22.628L20.063 27.774C20.312 28.383 20.9 28.778 21.561 28.778C21.774 28.778 21.98 28.737 22.176 28.655C22.573 28.491 22.883 28.177 23.049 27.774C23.216 27.37 23.216 26.929 23.05 26.531L20.192 19.589L20.192 16.81L25.065 16.122C25.847 16.011 26.388 15.202 26.268 14.314C26.156 13.502 25.524 12.89 24.798 12.89Z"/>'
				. '</svg>';
		}

		$paths = array(
			'launcher' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="6.6" r="1.5" fill="currentColor" stroke="none"/><path d="M4.8 9.8h14.4"/><path d="M12 9.8v5.1l-2.6 6.1"/><path d="M12 14.9l2.6 6.1"/>',
			'close'    => '<path d="M5.5 5.5l13 13"/><path d="M18.5 5.5l-13 13"/>',
			'skip'     => '<path d="M3 6h12"/><path d="M3 12h18"/><path d="M3 18h9"/><path d="M17 8l4 4-4 4"/>',
			'contrast' => '<circle cx="12" cy="12" r="9.2"/><path d="M12 2.8a9.2 9.2 0 000 18.4z" fill="currentColor" stroke="none"/>',
			'text'     => '<path d="M2.5 19L8 5.4 13.5 19"/><path d="M4.6 14.4h6.8"/><path d="M18.5 10.5v7"/><path d="M15 14h7"/>',
			'reset'    => '<path d="M20.5 11.5a8.5 8.5 0 11-2.6-6.1"/><path d="M20.5 3.5v5h-5"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		return $open . $paths[ $name ] . $close;
	}

	/**
	 * Build the toolbar HTML.
	 *
	 * Every dynamic value is escaped at the point of output.
	 *
	 * @param array $args Settings array (already sanitised).
	 * @return string
	 */
	public static function render( array $args ) {
		$args   = wp_parse_args( $args, ADAA_Settings::defaults() );
		$custom = isset( $args['icons'] ) && is_array( $args['icons'] ) ? $args['icons'] : array();

		/*
		 * Elementor sets the custom properties through its own stylesheet, so
		 * the widget passes inline_colors = false and no inline style is
		 * printed. The auto-injected and shortcode instances need it.
		 */
		$inline_style = ! isset( $args['inline_colors'] ) || $args['inline_colors'];

		$uid = 'adaa-panel';

		// Map the chosen Elementor global onto the toolbar's colours.
		$scheme = '';

		if ( ! empty( $args['color_source'] ) && 'default' !== $args['color_source'] ) {
			$pairs = array(
				'primary'   => 'secondary',
				'secondary' => 'primary',
				'accent'    => 'primary',
				'text'      => 'accent',
			);

			$base  = $args['color_source'];
			$hover = isset( $pairs[ $base ] ) ? $pairs[ $base ] : 'secondary';

			$scheme = sprintf(
				'--adaa-bg: var(--e-global-color-%1$s, #1c2b4a); --adaa-hover: var(--e-global-color-%2$s, #2f4470);',
				$base,
				$hover
			);
		}

		/*
		 * WPBakery has no scoped-selector system, so its element passes the
		 * style parameters here already validated by ADAA_Style. Elementor
		 * leaves this empty and writes its own stylesheet instead.
		 */
		$extra_css = isset( $args['inline_css'] ) ? (string) $args['inline_css'] : '';

		$wrap_classes = array(
			'adaa',
			'adaa--side-' . $args['panel_side'],
			'adaa--btn-' . $args['button_position'],
		);

		$buttons = array();

		if ( $args['show_skip'] ) {
			$buttons[] = array( 'skip', $args['label_skip'], false );
		}
		if ( $args['show_contrast'] ) {
			$buttons[] = array( 'contrast', $args['label_contrast'], true );
		}
		if ( $args['show_text'] ) {
			$buttons[] = array( 'text', $args['label_text'], true );
		}
		if ( $args['show_reset'] ) {
			$buttons[] = array( 'reset', $args['label_reset'], false );
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>"
			data-skip-target="<?php echo esc_attr( $args['skip_target'] ); ?>"
			data-skip-offset="<?php echo esc_attr( (int) $args['skip_offset'] ); ?>"
			<?php if ( $inline_style ) : ?>
				style="--adaa-z: <?php echo esc_attr( (int) $args['z_index'] ); ?>; <?php echo esc_attr( $scheme ); ?><?php echo esc_attr( $extra_css ); ?>"
			<?php endif; ?>>

			<button type="button"
				class="adaa__launcher"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $uid ); ?>"
				aria-label="<?php esc_attr_e( 'Accessibility options', 'ada-accessibility' ); ?>">
				<?php echo self::icon( 'launcher', $custom ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal SVG. ?>
			</button>

			<ul class="adaa__panel" id="<?php echo esc_attr( $uid ); ?>">
				<li>
					<button type="button" class="adaa__item" data-adaa="close">
						<?php echo self::icon( 'close', $custom ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal SVG. ?>
						<span><?php echo esc_html( $args['label_close'] ); ?></span>
					</button>
				</li>
				<?php foreach ( $buttons as $button ) : ?>
					<?php list( $action, $label, $toggle ) = $button; ?>
					<li>
						<button type="button"
							class="adaa__item"
							data-adaa="<?php echo esc_attr( $action ); ?>"
							<?php
							if ( $toggle ) {
								printf( ' aria-pressed="%s"', esc_attr( 'false' ) );
							}
							?>>
							<?php echo self::icon( $action, $custom ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal SVG. ?>
							<span><?php echo esc_html( $label ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
		self::$printed = true;

		return trim( ob_get_clean() );
	}
}
