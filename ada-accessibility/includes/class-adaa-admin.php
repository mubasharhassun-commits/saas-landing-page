<?php
/**
 * Admin settings screen, built on the Settings API so nonce
 * verification and capability checks are handled by core.
 *
 * @package ADA_Accessibility
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings screen under Settings → ADA Accessibility.
 */
class ADAA_Admin {

	const PAGE_SLUG = 'ada-accessibility';

	/**
	 * Hook in.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ADAA_FILE ), array( $this, 'action_link' ) );
	}

	/**
	 * Register the options page.
	 *
	 * @return void
	 */
	public function add_page() {
		add_options_page(
			__( 'ADA Accessibility', 'ada-accessibility' ),
			__( 'ADA Accessibility', 'ada-accessibility' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Add a Settings link on the plugins list.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function action_link( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );

		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'ada-accessibility' ) . '</a>'
		);

		return $links;
	}

	/**
	 * Load the colour picker only on our screen.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function assets( $hook ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		// Reserved for future admin assets.
	}

	/**
	 * Register the setting and its fields.
	 *
	 * @return void
	 */
	public function register() {
		register_setting(
			'adaa_group',
			ADAA_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'ADAA_Settings', 'sanitize' ),
				'default'           => ADAA_Settings::defaults(),
			)
		);

		add_settings_section( 'adaa_general', __( 'General', 'ada-accessibility' ), '__return_false', self::PAGE_SLUG );
		add_settings_section( 'adaa_appearance', __( 'Placement', 'ada-accessibility' ), array( $this, 'placement_intro' ), self::PAGE_SLUG );
		add_settings_section( 'adaa_buttons', __( 'Buttons', 'ada-accessibility' ), array( $this, 'buttons_intro' ), self::PAGE_SLUG );

		$this->field( 'auto_inject', __( 'Show on every page', 'ada-accessibility' ), 'checkbox', 'adaa_general', __( 'Turn this off if you want to place the toolbar with the Elementor widget or the shortcode instead.', 'ada-accessibility' ) );

		$this->field(
			'panel_side',
			__( 'Panel opens from', 'ada-accessibility' ),
			'select',
			'adaa_appearance',
			'',
			array(
				'left'  => __( 'Left', 'ada-accessibility' ),
				'right' => __( 'Right', 'ada-accessibility' ),
			)
		);

		$this->field(
			'button_position',
			__( 'Button position', 'ada-accessibility' ),
			'select',
			'adaa_appearance',
			'',
			array(
				'bottom-right' => __( 'Bottom right', 'ada-accessibility' ),
				'bottom-left'  => __( 'Bottom left', 'ada-accessibility' ),
				'middle-right' => __( 'Middle right', 'ada-accessibility' ),
				'middle-left'  => __( 'Middle left', 'ada-accessibility' ),
			)
		);

		$this->field(
			'color_source',
			__( 'Match site colours', 'ada-accessibility' ),
			'select',
			'adaa_appearance',
			__( 'Which Elementor global colour the toolbar should use for its background. Pick whichever one is your brand colour.', 'ada-accessibility' ),
			array(
				'primary'   => __( 'Elementor global: Primary', 'ada-accessibility' ),
				'secondary' => __( 'Elementor global: Secondary', 'ada-accessibility' ),
				'accent'    => __( 'Elementor global: Accent', 'ada-accessibility' ),
				'text'      => __( 'Elementor global: Text', 'ada-accessibility' ),
				'default'   => __( 'Plugin default (navy)', 'ada-accessibility' ),
			)
		);

		$this->field( 'skip_target', __( 'Skip to content target', 'ada-accessibility' ), 'text', 'adaa_appearance', __( 'CSS selector for the element “Skip to Content” should jump to, e.g. #main or .elementor-location-single. Leave empty to auto-detect.', 'ada-accessibility' ) );
		$this->field( 'skip_offset', __( 'Scroll offset (px)', 'ada-accessibility' ), 'number', 'adaa_appearance', __( 'Stops a sticky header covering the target. Try the header height.', 'ada-accessibility' ) );
		$this->field( 'z_index', __( 'Layer (z-index)', 'ada-accessibility' ), 'number', 'adaa_appearance', __( 'Raise this if a sticky header or chat widget sits on top of the toolbar.', 'ada-accessibility' ) );

		$this->field( 'label_close', __( 'Close label', 'ada-accessibility' ), 'text', 'adaa_buttons' );
		$this->field( 'show_skip', __( 'Skip to content', 'ada-accessibility' ), 'toggle_label', 'adaa_buttons', '', array( 'label_key' => 'label_skip' ) );
		$this->field( 'show_contrast', __( 'High contrast', 'ada-accessibility' ), 'toggle_label', 'adaa_buttons', '', array( 'label_key' => 'label_contrast' ) );
		$this->field( 'show_text', __( 'Increase text size', 'ada-accessibility' ), 'toggle_label', 'adaa_buttons', '', array( 'label_key' => 'label_text' ) );
		$this->field( 'show_reset', __( 'Clear all', 'ada-accessibility' ), 'toggle_label', 'adaa_buttons', '', array( 'label_key' => 'label_reset' ) );
	}

	/**
	 * Intro copy for the placement section.
	 *
	 * @return void
	 */
	public function placement_intro() {
		echo '<p>' . esc_html__( 'Colours, fonts, sizes and spacing are set on the Elementor widget, not here.', 'ada-accessibility' ) . '</p>';
	}

	/**
	 * Intro copy for the buttons section.
	 *
	 * @return void
	 */
	public function buttons_intro() {
		echo '<p>' . esc_html__( 'Uncheck a button to hide it. The text beside each checkbox is what visitors will read.', 'ada-accessibility' ) . '</p>';
	}

	/**
	 * Helper to register a single field.
	 *
	 * @param string $key     Option key.
	 * @param string $title   Field label.
	 * @param string $type    Field type.
	 * @param string $section Section id.
	 * @param string $help    Description text.
	 * @param array  $extra   Extra args (select options or label_key).
	 * @return void
	 */
	protected function field( $key, $title, $type, $section, $help = '', $extra = array() ) {
		add_settings_field(
			$key,
			$title,
			array( $this, 'render_field' ),
			self::PAGE_SLUG,
			$section,
			array(
				'key'   => $key,
				'type'  => $type,
				'help'  => $help,
				'extra' => $extra,
			)
		);
	}

	/**
	 * Output a single field.
	 *
	 * @param array $args Field args.
	 * @return void
	 */
	public function render_field( $args ) {
		$settings = ADAA_Settings::get();
		$key      = $args['key'];
		$name     = ADAA_OPTION . '[' . $key . ']';
		$value    = isset( $settings[ $key ] ) ? $settings[ $key ] : '';

		switch ( $args['type'] ) {
			case 'checkbox':
				printf(
					'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $name ),
					checked( 1, (int) $value, false ),
					esc_html__( 'Enabled', 'ada-accessibility' )
				);
				break;

			case 'toggle_label':
				$label_key   = $args['extra']['label_key'];
				$label_name  = ADAA_OPTION . '[' . $label_key . ']';
				$label_value = isset( $settings[ $label_key ] ) ? $settings[ $label_key ] : '';

				printf(
					'<label><input type="checkbox" name="%1$s" value="1" %2$s></label> <input type="text" name="%3$s" value="%4$s" class="regular-text">',
					esc_attr( $name ),
					checked( 1, (int) $value, false ),
					esc_attr( $label_name ),
					esc_attr( $label_value )
				);
				break;

			case 'select':
				printf( '<select name="%s">', esc_attr( $name ) );
				foreach ( $args['extra'] as $option_value => $option_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $option_value ),
						selected( $value, $option_value, false ),
						esc_html( $option_label )
					);
				}
				echo '</select>';
				break;

			case 'number':
				printf(
					'<input type="number" name="%1$s" value="%2$s" class="small-text">',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;

			case 'text':
			default:
				printf(
					'<input type="text" name="%1$s" value="%2$s" class="regular-text">',
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;
		}

		if ( ! empty( $args['help'] ) ) {
			echo '<p class="description">' . esc_html( $args['help'] ) . '</p>';
		}
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'ada-accessibility' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<p>
				<?php esc_html_e( 'Place the toolbar with the Elementor widget, the shortcode below, or leave it on every page.', 'ada-accessibility' ); ?>
				<code>[ada_accessibility]</code>
			</p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'adaa_group' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
