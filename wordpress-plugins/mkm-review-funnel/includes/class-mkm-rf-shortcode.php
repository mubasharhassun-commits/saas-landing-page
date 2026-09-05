<?php
/**
 * Front-end rendering: the [mkm_review_funnel] shortcode and its assets.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the funnel and loads its assets.
 */
class MKM_RF_Shortcode {

	/**
	 * Shortcode tag.
	 */
	const TAG = 'mkm_review_funnel';

	/**
	 * Whether the assets have been enqueued for this request.
	 *
	 * @var bool
	 */
	protected $enqueued = false;

	/**
	 * Instance counter so multiple funnels on one page get unique ids.
	 *
	 * @var int
	 */
	protected $instance = 0;

	/**
	 * Hooks the shortcode and asset registration.
	 */
	public function init() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp', array( $this, 'maybe_enqueue_early' ) );
	}

	/**
	 * Registers (but does not enqueue) the front-end assets.
	 */
	public function register_assets() {
		wp_register_style(
			'mkm-review-funnel',
			MKM_RF_URL . 'assets/css/mkm-review-funnel.css',
			array(),
			MKM_RF_VERSION
		);

		wp_register_script(
			'mkm-review-funnel',
			MKM_RF_URL . 'assets/js/mkm-review-funnel.js',
			array(),
			MKM_RF_VERSION,
			true
		);

		$provider = MKM_RF_Settings::get( 'captcha_provider' );
		$site_key = trim( (string) MKM_RF_Settings::get( 'captcha_site_key' ) );

		wp_localize_script(
			'mkm-review-funnel',
			'mkmReviewFunnel',
			array(
				'tokenUrl'    => esc_url_raw( rest_url( MKM_RF_Rest::NAMESPACE_V1 . '/token' ) ),
				'feedbackUrl' => esc_url_raw( rest_url( MKM_RF_Rest::NAMESPACE_V1 . '/feedback' ) ),
				'clickUrl'    => esc_url_raw( rest_url( MKM_RF_Rest::NAMESPACE_V1 . '/click' ) ),
				'trackClicks' => MKM_RF_Settings::get( 'track_clicks' ) ? 1 : 0,
				'captcha'     => array(
					'provider' => $provider,
					'siteKey'  => 'none' === $provider ? '' : $site_key,
				),
				'i18n'        => array(
					'genericError' => __( 'Something went wrong. Please try again.', 'mkm-review-funnel' ),
					'sending'      => __( 'Sending…', 'mkm-review-funnel' ),
					'close'        => __( 'Close', 'mkm-review-funnel' ),
				),
			)
		);

		if ( 'recaptcha_v3' === $provider && $site_key ) {
			wp_register_script(
				'mkm-rf-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters -- third-party URL, no version.
				true
			);
		}

		if ( 'turnstile' === $provider && $site_key ) {
			wp_register_script(
				'mkm-rf-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters -- third-party URL, no version.
				true
			);
		}
	}

	/**
	 * Enqueues assets in the head when the funnel is present in the page content.
	 */
	public function maybe_enqueue_early() {
		if ( is_admin() || ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( has_shortcode( (string) $post->post_content, self::TAG ) ) {
			$this->enqueue();
		}
	}

	/**
	 * Enqueues the assets once per request.
	 */
	public function enqueue() {
		if ( $this->enqueued ) {
			return;
		}

		wp_enqueue_style( 'mkm-review-funnel' );
		wp_enqueue_script( 'mkm-review-funnel' );

		$provider = MKM_RF_Settings::get( 'captcha_provider' );

		if ( 'recaptcha_v3' === $provider && wp_script_is( 'mkm-rf-recaptcha', 'registered' ) ) {
			wp_enqueue_script( 'mkm-rf-recaptcha' );
		}

		if ( 'turnstile' === $provider && wp_script_is( 'mkm-rf-turnstile', 'registered' ) ) {
			wp_enqueue_script( 'mkm-rf-turnstile' );
		}

		$this->enqueued = true;
	}

	/**
	 * Default instructions shown before sending a visitor to a review profile.
	 *
	 * @param string $slug  Destination slug.
	 * @param string $label Destination label.
	 * @return string[]
	 */
	protected function instructions( $slug, $label ) {
		$steps = array(
			/* translators: %s: review site name. */
			sprintf( __( 'Sign in to your %s account, or create one if you do not have one yet.', 'mkm-review-funnel' ), $label ),
			__( 'Leave your rating and a few words about your experience.', 'mkm-review-funnel' ),
			__( 'Post your review so it is visible to others.', 'mkm-review-funnel' ),
		);

		/**
		 * Filters the review instructions for a destination.
		 *
		 * @param string[] $steps Instruction steps.
		 * @param string   $slug  Destination slug.
		 */
		return (array) apply_filters( 'mkm_rf_instructions', $steps, $slug );
	}

	/**
	 * Renders the shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts = array() ) {
		$defaults = array(
			'mode'           => '',
			'heading'        => '',
			'intro'          => '',
			'positive_label' => '',
			'negative_label' => '',
			'review_label'   => '',
			'feedback_label' => '',
			'align'          => 'center',
			'class'          => '',
			'css'            => '',
		);

		$atts = shortcode_atts( $defaults, is_array( $atts ) ? $atts : array(), self::TAG );

		// The builder submits empty strings for untouched fields, so empty means
		// "inherit the global setting" rather than "render nothing".
		$fallbacks = array(
			'mode'           => 'mode',
			'heading'        => 'heading',
			'intro'          => 'intro',
			'positive_label' => 'positive_label',
			'negative_label' => 'negative_label',
			'review_label'   => 'open_review_label',
			'feedback_label' => 'open_feedback_label',
		);

		foreach ( $fallbacks as $att => $setting ) {
			if ( '' === trim( (string) $atts[ $att ] ) ) {
				$atts[ $att ] = MKM_RF_Settings::get( $setting );
			}
		}

		$mode  = 'gated' === $atts['mode'] ? 'gated' : 'open';
		$align = in_array( $atts['align'], array( 'left', 'center', 'right' ), true ) ? $atts['align'] : 'center';

		$destinations = MKM_RF_Settings::destinations();

		// With nothing configured the review path is hidden for everyone; admins
		// also get a note explaining why, since the omission is easy to miss.
		$admin_notice = '';

		if ( ! $destinations && current_user_can( MKM_RF_Plugin::capability() ) ) {
			$admin_notice = '<p class="mkm-rf-notice">' . esc_html__( 'Review Funnel: no review profile URLs are configured yet. Add at least one under Review Funnel, Settings. Only site administrators see this message.', 'mkm-review-funnel' ) . '</p>';
		}

		$this->enqueue();

		$this->instance++;
		$uid = 'mkm-rf-' . absint( $this->instance );

		$classes = array( 'mkm-rf', 'mkm-rf--' . $mode, 'mkm-rf--align-' . $align );

		if ( $atts['class'] ) {
			foreach ( preg_split( '/\s+/', $atts['class'] ) as $extra ) {
				$classes[] = sanitize_html_class( $extra );
			}
		}

		// Design Options set in the WPBakery element editor.
		if ( $atts['css'] && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$classes[] = vc_shortcode_custom_css_class( $atts['css'] );
		}

		ob_start();

		// Already escaped when built above.
		echo $admin_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" id="<?php echo esc_attr( $uid ); ?>" data-mkm-rf data-mode="<?php echo esc_attr( $mode ); ?>">

			<?php if ( $atts['heading'] ) : ?>
				<h2 class="mkm-rf__heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $atts['intro'] ) : ?>
				<p class="mkm-rf__intro"><?php echo esc_html( $atts['intro'] ); ?></p>
			<?php endif; ?>

			<div class="mkm-rf__choices">
				<?php if ( 'gated' === $mode && $destinations ) : ?>
					<button type="button" class="mkm-rf__choice mkm-rf__choice--positive" data-mkm-rf-open="review" aria-haspopup="dialog">
						<span class="mkm-rf__choice-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M2 21h4V9H2v12zm20-11a2 2 0 0 0-2-2h-6.31l.95-4.57.03-.32a1.5 1.5 0 0 0-.44-1.06L13.17 1 6.59 7.59A2 2 0 0 0 6 9v10a2 2 0 0 0 2 2h9a2 2 0 0 0 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
						</span>
						<span class="mkm-rf__choice-label"><?php echo esc_html( $atts['positive_label'] ); ?></span>
					</button>
					<button type="button" class="mkm-rf__choice mkm-rf__choice--negative" data-mkm-rf-open="feedback" aria-haspopup="dialog">
						<span class="mkm-rf__choice-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M22 3h-4v12h4V3zM2 14a2 2 0 0 0 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L10.83 23l6.58-6.59c.37-.36.59-.86.59-1.41V5a2 2 0 0 0-2-2H7a2 2 0 0 0-1.84 1.22L2.14 11.27c-.09.23-.14.47-.14.73v2z"/></svg>
						</span>
						<span class="mkm-rf__choice-label"><?php echo esc_html( $atts['negative_label'] ); ?></span>
					</button>
				<?php else : ?>
					<?php if ( $destinations ) : ?>
					<button type="button" class="mkm-rf__choice mkm-rf__choice--positive" data-mkm-rf-open="review" aria-haspopup="dialog">
						<span class="mkm-rf__choice-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
						</span>
						<span class="mkm-rf__choice-label"><?php echo esc_html( $atts['review_label'] ); ?></span>
					</button>
					<?php endif; ?>
					<button type="button" class="mkm-rf__choice mkm-rf__choice--negative" data-mkm-rf-open="feedback" aria-haspopup="dialog">
						<span class="mkm-rf__choice-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
						</span>
						<span class="mkm-rf__choice-label"><?php echo esc_html( $atts['feedback_label'] ); ?></span>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( $destinations ) : ?>
				<?php $this->render_review_modal( $uid, $destinations ); ?>
			<?php endif; ?>
			<?php $this->render_feedback_modal( $uid ); ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders the review-profile modal.
	 *
	 * @param string $uid          Instance id.
	 * @param array  $destinations Configured destinations.
	 */
	protected function render_review_modal( $uid, array $destinations ) {
		?>
		<div class="mkm-rf-modal" id="<?php echo esc_attr( $uid ); ?>-review" data-mkm-rf-modal="review" hidden>
			<div class="mkm-rf-modal__overlay" data-mkm-rf-dismiss></div>
			<div class="mkm-rf-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $uid ); ?>-review-title">
				<button type="button" class="mkm-rf-modal__close" data-mkm-rf-dismiss>
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'mkm-review-funnel' ); ?></span>
				</button>

				<div class="mkm-rf-modal__body" data-mkm-rf-pane="chooser">
					<p class="mkm-rf-modal__intro" id="<?php echo esc_attr( $uid ); ?>-review-title"><?php echo esc_html( MKM_RF_Settings::get( 'review_intro' ) ); ?></p>
					<div class="mkm-rf-sites">
						<?php foreach ( $destinations as $slug => $destination ) : ?>
							<button type="button" class="mkm-rf-site mkm-rf-site--<?php echo esc_attr( $slug ); ?>" data-mkm-rf-site="<?php echo esc_attr( $slug ); ?>">
								<span class="mkm-rf-site__name"><?php echo esc_html( $destination['label'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<?php foreach ( $destinations as $slug => $destination ) : ?>
					<div class="mkm-rf-modal__body" data-mkm-rf-pane="site" data-mkm-rf-site-pane="<?php echo esc_attr( $slug ); ?>" hidden>
						<h3 class="mkm-rf-modal__title"><?php echo esc_html( $destination['label'] ); ?></h3>
						<ul class="mkm-rf-steps">
							<?php foreach ( $this->instructions( $slug, $destination['label'] ) as $step ) : ?>
								<li><?php echo esc_html( $step ); ?></li>
							<?php endforeach; ?>
						</ul>
						<a class="mkm-rf-button mkm-rf-button--go"
							href="<?php echo esc_url( $destination['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							data-mkm-rf-go="<?php echo esc_attr( $slug ); ?>">
							<?php
							/* translators: %s: review site name. */
							echo esc_html( sprintf( __( 'Click to review us on %s', 'mkm-review-funnel' ), $destination['label'] ) );
							?>
						</a>
						<button type="button" class="mkm-rf-back" data-mkm-rf-back><?php esc_html_e( 'Back', 'mkm-review-funnel' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the private feedback modal.
	 *
	 * @param string $uid Instance id.
	 */
	protected function render_feedback_modal( $uid ) {
		$consent_text = (string) MKM_RF_Settings::get( 'consent_text' );
		$require      = (bool) MKM_RF_Settings::get( 'require_consent' );
		?>
		<div class="mkm-rf-modal" id="<?php echo esc_attr( $uid ); ?>-feedback" data-mkm-rf-modal="feedback" hidden>
			<div class="mkm-rf-modal__overlay" data-mkm-rf-dismiss></div>
			<div class="mkm-rf-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $uid ); ?>-feedback-title">
				<button type="button" class="mkm-rf-modal__close" data-mkm-rf-dismiss>
					<span aria-hidden="true">&times;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'mkm-review-funnel' ); ?></span>
				</button>

				<div class="mkm-rf-modal__body">
					<p class="mkm-rf-modal__intro" id="<?php echo esc_attr( $uid ); ?>-feedback-title"><?php echo esc_html( MKM_RF_Settings::get( 'feedback_intro' ) ); ?></p>

					<form class="mkm-rf-form" data-mkm-rf-form novalidate>
						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-first"><?php esc_html_e( 'First Name', 'mkm-review-funnel' ); ?></label>
							<input type="text" id="<?php echo esc_attr( $uid ); ?>-first" name="first_name" autocomplete="given-name" placeholder="<?php esc_attr_e( 'First Name', 'mkm-review-funnel' ); ?>" required>
							<span class="mkm-rf-error" data-mkm-rf-error="first_name" role="alert"></span>
						</div>

						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-last"><?php esc_html_e( 'Last Name', 'mkm-review-funnel' ); ?></label>
							<input type="text" id="<?php echo esc_attr( $uid ); ?>-last" name="last_name" autocomplete="family-name" placeholder="<?php esc_attr_e( 'Last Name', 'mkm-review-funnel' ); ?>" required>
							<span class="mkm-rf-error" data-mkm-rf-error="last_name" role="alert"></span>
						</div>

						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email', 'mkm-review-funnel' ); ?></label>
							<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" autocomplete="email" placeholder="<?php esc_attr_e( 'Email', 'mkm-review-funnel' ); ?>" required>
							<span class="mkm-rf-error" data-mkm-rf-error="email" role="alert"></span>
						</div>

						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Phone', 'mkm-review-funnel' ); ?></label>
							<input type="tel" id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" autocomplete="tel" placeholder="<?php esc_attr_e( 'Phone', 'mkm-review-funnel' ); ?>" required>
							<span class="mkm-rf-error" data-mkm-rf-error="phone" role="alert"></span>
						</div>

						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-status"><?php esc_html_e( 'Are you a new client?', 'mkm-review-funnel' ); ?></label>
							<select id="<?php echo esc_attr( $uid ); ?>-status" name="client_status">
								<option value=""><?php esc_html_e( 'Are you a new client?', 'mkm-review-funnel' ); ?></option>
								<option value="new"><?php esc_html_e( 'Yes, I am a potential new client.', 'mkm-review-funnel' ); ?></option>
								<option value="existing"><?php esc_html_e( "No, I'm a current existing client.", 'mkm-review-funnel' ); ?></option>
								<option value="neither"><?php esc_html_e( "I'm neither.", 'mkm-review-funnel' ); ?></option>
							</select>
						</div>

						<div class="mkm-rf-field">
							<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'Message', 'mkm-review-funnel' ); ?></label>
							<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="6" placeholder="<?php esc_attr_e( 'Message', 'mkm-review-funnel' ); ?>" required></textarea>
							<span class="mkm-rf-error" data-mkm-rf-error="message" role="alert"></span>
						</div>

						<?php if ( $consent_text ) : ?>
							<div class="mkm-rf-field mkm-rf-field--consent">
								<label for="<?php echo esc_attr( $uid ); ?>-consent">
									<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>-consent" name="consent" value="1" <?php echo $require ? 'required' : ''; ?>>
									<span><?php echo esc_html( $consent_text ); ?></span>
								</label>
								<span class="mkm-rf-error" data-mkm-rf-error="consent" role="alert"></span>
							</div>
						<?php endif; ?>

						<?php // Honeypot: hidden from people, tempting to bots. ?>
						<div class="mkm-rf-hp" aria-hidden="true">
							<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'mkm-review-funnel' ); ?></label>
							<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="website" tabindex="-1" autocomplete="off">
						</div>

						<?php if ( 'turnstile' === MKM_RF_Settings::get( 'captcha_provider' ) && MKM_RF_Settings::get( 'captcha_site_key' ) ) : ?>
							<div class="mkm-rf-captcha" data-mkm-rf-captcha></div>
						<?php endif; ?>

						<p class="mkm-rf-form__status" data-mkm-rf-status role="status"></p>

						<button type="submit" class="mkm-rf-button mkm-rf-button--submit" data-mkm-rf-submit>
							<?php esc_html_e( 'Submit Information', 'mkm-review-funnel' ); ?>
						</button>
					</form>

					<div class="mkm-rf-success" data-mkm-rf-success hidden>
						<p><?php echo esc_html( MKM_RF_Settings::get( 'success_message' ) ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
