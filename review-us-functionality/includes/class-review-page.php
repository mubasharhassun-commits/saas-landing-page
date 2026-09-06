<?php
/**
 * Front end: shortcode, assets, popups and footer link integration.
 *
 * @package MKM_Review_Us
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the review funnel.
 */
class MKM_Review_Us_Page {

	/**
	 * Set once the shortcode has rendered, so assets and markup are printed once.
	 *
	 * @var bool
	 */
	private $rendered = false;

	/**
	 * Registers hooks.
	 */
	public function __construct() {
		add_shortcode( 'mkm_review_us', array( $this, 'render_shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_footer_fallback' ) );

		add_filter( 'wp_nav_menu_objects', array( $this, 'filter_menu_items' ), 10, 2 );
	}

	/**
	 * Whether the current request should load the funnel assets.
	 *
	 * @return bool
	 */
	private function is_review_page() {
		if ( is_admin() ) {
			return false;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( (int) $post->ID === MKM_Review_Us_Settings::page_id() ) {
			return true;
		}

		return has_shortcode( (string) $post->post_content, 'mkm_review_us' );
	}

	/**
	 * Loads the funnel CSS and JS only where the funnel is used.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_review_page() ) {
			return;
		}

		wp_enqueue_style(
			'mkm-review-us',
			MKM_REVIEW_US_URL . 'assets/css/review-us.css',
			array(),
			MKM_REVIEW_US_VERSION
		);

		wp_enqueue_script(
			'mkm-review-us',
			MKM_REVIEW_US_URL . 'assets/js/review-us.js',
			array(),
			MKM_REVIEW_US_VERSION,
			true
		);

		wp_localize_script(
			'mkm-review-us',
			'mkmReviewUs',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'action'          => MKM_Review_Us_Feedback::AJAX_ACTION,
				'nonce'           => wp_create_nonce( MKM_Review_Us_Feedback::NONCE_ACTION ),
				'googleUrl'       => MKM_Review_Us_Settings::google_review_url(),
				'openInNewTab'    => (bool) MKM_Review_Us_Settings::get( 'open_in_new_tab' ),
				'formMode'        => MKM_Review_Us_Settings::resolved_form_mode(),
				'i18n'            => array(
					'submitting'   => __( 'Sending…', 'mkm-review-us' ),
					'genericError' => __( 'Something went wrong. Please try again or call the office.', 'mkm-review-us' ),
					'required'     => __( 'Please complete the required fields.', 'mkm-review-us' ),
				),
			)
		);

		// Gravity Forms needs its assets queued up front when the form is rendered with AJAX.
		if ( 'gravity' === MKM_Review_Us_Settings::resolved_form_mode() && function_exists( 'gravity_form_enqueue_scripts' ) ) {
			$form_id = (int) MKM_Review_Us_Settings::get( 'gravity_form_id' );

			if ( $form_id ) {
				gravity_form_enqueue_scripts( $form_id, true );
			}
		}
	}

	/**
	 * Optional sitewide fallback that repoints page-builder footer buttons.
	 */
	public function enqueue_footer_fallback() {
		if ( is_admin() || ! MKM_Review_Us_Settings::get( 'footer_auto_link' ) ) {
			return;
		}

		wp_enqueue_script(
			'mkm-review-us-footer',
			MKM_REVIEW_US_URL . 'assets/js/review-us-footer.js',
			array(),
			MKM_REVIEW_US_VERSION,
			true
		);

		wp_localize_script(
			'mkm-review-us-footer',
			'mkmReviewUsFooter',
			array(
				'url'   => MKM_Review_Us_Settings::page_url(),
				'label' => 'review us',
			)
		);
	}

	/**
	 * Points existing "Review Us" menu items at the Review Us page.
	 *
	 * @param array  $items Menu items.
	 * @param object $args  Menu args.
	 * @return array
	 */
	public function filter_menu_items( $items, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		$url = MKM_Review_Us_Settings::page_url();

		if ( ! $url ) {
			return $items;
		}

		foreach ( $items as $item ) {
			if ( empty( $item->title ) ) {
				continue;
			}

			$title = strtolower( trim( wp_strip_all_tags( $item->title ) ) );
			$path  = isset( $item->url ) ? trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' ) : '';

			if ( 'review us' === $title || 'review-us' === $path ) {
				$item->url = $url;
			}
		}

		return $items;
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		if ( $this->rendered ) {
			return '';
		}

		$this->rendered = true;

		$atts = shortcode_atts(
			array(
				'heading'     => __( 'How Was Your Experience?', 'mkm-review-us' ),
				'description' => __( 'We value your feedback. Please let us know about your experience with our firm.', 'mkm-review-us' ),
			),
			$atts,
			'mkm_review_us'
		);

		ob_start();
		$this->render_intro( $atts );
		$this->render_positive_popup();
		$this->render_google_popup();
		$this->render_negative_popup();

		return ob_get_clean();
	}

	/**
	 * Heading and the two rating buttons.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	private function render_intro( array $atts ) {
		?>
		<section class="mkm-review-page" aria-labelledby="mkm-review-heading">
			<h2 class="mkm-review-page__heading" id="mkm-review-heading"><?php echo esc_html( $atts['heading'] ); ?></h2>

			<?php if ( $atts['description'] ) : ?>
				<p class="mkm-review-page__intro"><?php echo esc_html( $atts['description'] ); ?></p>
			<?php endif; ?>

			<div class="mkm-review-options">
				<button type="button" class="mkm-review-option mkm-review-option--up" data-mkm-open="mkm-review-popup-positive">
					<span class="mkm-review-option__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" role="presentation" focusable="false">
							<path d="M2 20h3V9H2v11Zm19.5-9.5c0-.83-.67-1.5-1.5-1.5h-5.19l.78-3.76.02-.26c0-.41-.17-.79-.44-1.06L14.17 3 8.6 8.59A1.99 1.99 0 0 0 8 10v8c0 1.1.9 2 2 2h7.5c.7 0 1.31-.42 1.58-1.03l2.26-5.28c.07-.18.11-.36.11-.56v-2.63Z" />
						</svg>
					</span>
					<span class="mkm-review-option__label"><?php esc_html_e( 'Thumbs Up', 'mkm-review-us' ); ?></span>
					<span class="mkm-review-option__hint"><?php esc_html_e( 'I had a positive experience', 'mkm-review-us' ); ?></span>
				</button>

				<button type="button" class="mkm-review-option mkm-review-option--down" data-mkm-open="mkm-review-popup-negative">
					<span class="mkm-review-option__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" role="presentation" focusable="false">
							<path d="M22 4h-3v11h3V4ZM2.5 13.5c0 .83.67 1.5 1.5 1.5h5.19l-.78 3.76-.02.26c0 .41.17.79.44 1.06L9.83 21l5.57-5.59c.37-.36.6-.86.6-1.41V6c0-1.1-.9-2-2-2H6.5c-.7 0-1.31.42-1.58 1.03L2.66 10.3c-.07.19-.11.37-.11.57v2.63Z" />
						</svg>
					</span>
					<span class="mkm-review-option__label"><?php esc_html_e( 'Thumbs Down', 'mkm-review-us' ); ?></span>
					<span class="mkm-review-option__hint"><?php esc_html_e( 'I had a negative experience', 'mkm-review-us' ); ?></span>
				</button>
			</div>
		</section>
		<?php
	}

	/**
	 * Opens a popup wrapper.
	 *
	 * @param string $id       Popup ID.
	 * @param string $label_id ID of the element that labels the dialog.
	 * @param string $classes  Extra classes.
	 */
	private function open_popup( $id, $label_id, $classes = '' ) {
		?>
		<div class="mkm-review-popup-overlay" id="<?php echo esc_attr( $id ); ?>" data-mkm-popup hidden>
			<div class="mkm-review-popup <?php echo esc_attr( $classes ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
				<button type="button" class="mkm-review-popup__close" data-mkm-close>
					<span class="screen-reader-text mkm-review-sr"><?php esc_html_e( 'Close this dialog', 'mkm-review-us' ); ?></span>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M18.3 5.71 12 12.01l-6.3-6.3-1.41 1.41 6.3 6.3-6.3 6.3 1.41 1.41 6.3-6.3 6.3 6.3 1.41-1.41-6.3-6.3 6.3-6.3z" />
					</svg>
				</button>
				<div class="mkm-review-popup__body">
		<?php
	}

	/**
	 * Closes a popup wrapper.
	 */
	private function close_popup() {
		?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Popup 1: thank you plus the Google logo.
	 */
	private function render_positive_popup() {
		$this->open_popup( 'mkm-review-popup-positive', 'mkm-review-popup-positive-title', 'mkm-review-popup--narrow' );
		?>
		<h2 class="mkm-review-popup__title" id="mkm-review-popup-positive-title"><?php esc_html_e( 'Thank you for rating us!', 'mkm-review-us' ); ?></h2>
		<p class="mkm-review-popup__text"><?php esc_html_e( "We're glad you had a positive experience. Would you share it on Google?", 'mkm-review-us' ); ?></p>

		<button type="button" class="mkm-review-google-button" data-mkm-open="mkm-review-popup-google">
			<img class="mkm-review-google-button__logo"
				src="<?php echo esc_url( MKM_REVIEW_US_URL . 'assets/images/google-logo.svg' ); ?>"
				alt="<?php esc_attr_e( 'Continue to Google Reviews', 'mkm-review-us' ); ?>"
				width="220" height="80" loading="lazy" decoding="async" />
			<span class="mkm-review-google-button__caption"><?php esc_html_e( 'Review us on Google', 'mkm-review-us' ); ?></span>
		</button>
		<?php
		$this->close_popup();
	}

	/**
	 * Popup 2: sign-in reminder plus the CTA.
	 */
	private function render_google_popup() {
		$google_url = MKM_Review_Us_Settings::google_review_url();
		$new_tab    = (bool) MKM_Review_Us_Settings::get( 'open_in_new_tab' );

		$this->open_popup( 'mkm-review-popup-google', 'mkm-review-popup-google-title', 'mkm-review-popup--narrow' );
		?>
		<h2 class="mkm-review-popup__title" id="mkm-review-popup-google-title"><?php esc_html_e( 'Before you continue', 'mkm-review-us' ); ?></h2>
		<p class="mkm-review-popup__text"><?php esc_html_e( 'You need to be logged in to your Google/Gmail account before proceeding.', 'mkm-review-us' ); ?></p>
		<ul class="mkm-review-steps">
			<li><?php esc_html_e( 'If prompted, sign up or log in.', 'mkm-review-us' ); ?></li>
			<li><?php esc_html_e( 'Leave your rating and feedback.', 'mkm-review-us' ); ?></li>
			<li><?php esc_html_e( 'Click post to share with others.', 'mkm-review-us' ); ?></li>
		</ul>

		<?php if ( $google_url ) : ?>
			<a class="mkm-review-cta"
				href="<?php echo esc_url( $google_url ); ?>"
				<?php if ( $new_tab ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
				<?php esc_html_e( 'Review Us on Google', 'mkm-review-us' ); ?>
				<?php if ( $new_tab ) : ?>
					<span class="mkm-review-sr"><?php esc_html_e( '(opens in a new tab)', 'mkm-review-us' ); ?></span>
				<?php endif; ?>
			</a>
		<?php else : ?>
			<p class="mkm-review-popup__notice"><?php esc_html_e( 'The Google review link has not been configured yet. Please contact the site administrator.', 'mkm-review-us' ); ?></p>
		<?php endif; ?>
		<?php
		$this->close_popup();
	}

	/**
	 * Negative feedback popup with the configured form.
	 */
	private function render_negative_popup() {
		$this->open_popup( 'mkm-review-popup-negative', 'mkm-review-popup-negative-title' );
		?>
		<h2 class="mkm-review-popup__title" id="mkm-review-popup-negative-title"><?php esc_html_e( "We're sorry to hear that.", 'mkm-review-us' ); ?></h2>
		<p class="mkm-review-popup__text"><?php esc_html_e( 'We strive for 100% customer satisfaction. If we fell short, please tell us more so we can address your concerns.', 'mkm-review-us' ); ?></p>

		<div class="mkm-feedback-form-wrap" data-mkm-form-wrap>
			<?php $this->render_form(); ?>
		</div>

		<div class="mkm-feedback-success" data-mkm-success role="status" aria-live="polite" hidden>
			<h3 class="mkm-feedback-success__title"><?php esc_html_e( 'Thank you for your feedback.', 'mkm-review-us' ); ?></h3>
			<p class="mkm-feedback-success__text"><?php esc_html_e( 'We appreciate you taking the time to help us improve. Someone from our office will review your message.', 'mkm-review-us' ); ?></p>
			<button type="button" class="mkm-review-cta mkm-review-cta--ghost" data-mkm-close><?php esc_html_e( 'Close', 'mkm-review-us' ); ?></button>
		</div>
		<?php
		$this->close_popup();
	}

	/**
	 * Renders whichever form is configured.
	 */
	private function render_form() {
		$mode = MKM_Review_Us_Settings::resolved_form_mode();

		if ( 'gravity' === $mode ) {
			$form_id = (int) MKM_Review_Us_Settings::get( 'gravity_form_id' );

			if ( $form_id && MKM_Review_Us_Settings::gravity_forms_active() ) {
				echo do_shortcode( sprintf( '[gravityform id="%d" title="false" description="false" ajax="true"]', $form_id ) );

				return;
			}
		}

		if ( 'cf7' === $mode ) {
			$shortcode = MKM_Review_Us_Settings::get( 'cf7_shortcode' );

			if ( $shortcode && MKM_Review_Us_Settings::cf7_active() ) {
				echo do_shortcode( $shortcode );

				return;
			}
		}

		$this->render_builtin_form();
	}

	/**
	 * The plugin's own form, submitted over AJAX and stored in the feedback table.
	 *
	 * Field order and copy mirror the firm's existing contact form. Labels are
	 * visually hidden rather than removed, so the placeholders stay accessible.
	 */
	private function render_builtin_form() {
		$statuses = MKM_Review_Us_Feedback::client_statuses();
		?>
		<form class="mkm-feedback-form" data-mkm-feedback-form novalidate>
			<p class="mkm-feedback-form__error" data-mkm-form-error role="alert" hidden></p>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-first-name"><?php esc_html_e( 'First Name', 'mkm-review-us' ); ?></label>
				<input type="text" id="mkm-first-name" name="first_name" placeholder="<?php esc_attr_e( 'First Name', 'mkm-review-us' ); ?>" autocomplete="given-name" required aria-describedby="mkm-first-name-error" />
				<span class="mkm-feedback-field__error" id="mkm-first-name-error" data-mkm-error-for="first_name"></span>
			</div>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-last-name"><?php esc_html_e( 'Last Name', 'mkm-review-us' ); ?></label>
				<input type="text" id="mkm-last-name" name="last_name" placeholder="<?php esc_attr_e( 'Last Name', 'mkm-review-us' ); ?>" autocomplete="family-name" required aria-describedby="mkm-last-name-error" />
				<span class="mkm-feedback-field__error" id="mkm-last-name-error" data-mkm-error-for="last_name"></span>
			</div>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-email"><?php esc_html_e( 'Email', 'mkm-review-us' ); ?></label>
				<input type="email" id="mkm-email" name="email" placeholder="<?php esc_attr_e( 'Email', 'mkm-review-us' ); ?>" autocomplete="email" required aria-describedby="mkm-email-error" />
				<span class="mkm-feedback-field__error" id="mkm-email-error" data-mkm-error-for="email"></span>
			</div>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-phone"><?php esc_html_e( 'Phone', 'mkm-review-us' ); ?></label>
				<input type="tel" id="mkm-phone" name="phone" placeholder="<?php esc_attr_e( 'Phone', 'mkm-review-us' ); ?>" autocomplete="tel" aria-describedby="mkm-phone-error" />
				<span class="mkm-feedback-field__error" id="mkm-phone-error" data-mkm-error-for="phone"></span>
			</div>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-client-status"><?php esc_html_e( 'Are you a new client?', 'mkm-review-us' ); ?></label>
				<select id="mkm-client-status" name="client_status" required aria-describedby="mkm-client-status-error">
					<option value=""><?php esc_html_e( 'Are you a new client?', 'mkm-review-us' ); ?></option>
					<?php foreach ( $statuses as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="mkm-feedback-field__error" id="mkm-client-status-error" data-mkm-error-for="client_status"></span>
			</div>

			<div class="mkm-feedback-field">
				<label class="mkm-review-sr" for="mkm-message"><?php esc_html_e( 'Message', 'mkm-review-us' ); ?></label>
				<textarea id="mkm-message" name="feedback_message" rows="5" placeholder="<?php esc_attr_e( 'Message', 'mkm-review-us' ); ?>" required aria-describedby="mkm-message-error"></textarea>
				<span class="mkm-feedback-field__error" id="mkm-message-error" data-mkm-error-for="feedback_message"></span>
			</div>

			<div class="mkm-feedback-field mkm-feedback-consent">
				<label class="mkm-feedback-consent__label" for="mkm-disclaimer">
					<input type="checkbox" id="mkm-disclaimer" name="disclaimer" value="1" required aria-describedby="mkm-disclaimer-error" />
					<span class="mkm-feedback-consent__text">
						<?php esc_html_e( 'The information you obtain at this site is not, nor is it intended to be, legal advice. You should consult an attorney for advice regarding your individual situation. We invite you to contact us and welcome your calls, letters and electronic mail. Contacting us does not create an attorney-client relationship. Please do not send any confidential information to us until such time as an attorney-client relationship has been established.', 'mkm-review-us' ); ?>
					</span>
				</label>
				<span class="mkm-feedback-field__error" id="mkm-disclaimer-error" data-mkm-error-for="disclaimer"></span>
			</div>

			<div class="mkm-feedback-honeypot" aria-hidden="true">
				<label for="mkm-website"><?php esc_html_e( 'Leave this field empty', 'mkm-review-us' ); ?></label>
				<input type="text" id="mkm-website" name="mkm_hp" tabindex="-1" autocomplete="off" />
			</div>

			<button type="submit" class="mkm-review-cta mkm-feedback-submit" data-mkm-submit>
				<span data-mkm-submit-label><?php esc_html_e( 'Submit Information', 'mkm-review-us' ); ?></span>
				<span class="mkm-feedback-spinner" aria-hidden="true" hidden></span>
			</button>
		</form>
		<?php
	}
}
