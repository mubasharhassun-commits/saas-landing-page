<?php
/**
 * Settings screen markup.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

$mkm_rf_settings = MKM_RF_Settings::all();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only message.
$mkm_rf_import_notice = isset( $_GET['mkm_rf_import'] ) ? sanitize_key( wp_unslash( $_GET['mkm_rf_import'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only message.
$mkm_rf_import_count = isset( $_GET['mkm_rf_count'] ) ? absint( wp_unslash( $_GET['mkm_rf_count'] ) ) : 0;
?>
<div class="wrap mkm-rf-admin">
	<h1><?php esc_html_e( 'Review Funnel Settings', 'mkm-review-funnel' ); ?></h1>

	<?php if ( 'done' === $mkm_rf_import_notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: number of imported entries. */
					esc_html__( 'Imported %s entries from Gravity Forms.', 'mkm-review-funnel' ),
					esc_html( number_format_i18n( $mkm_rf_import_count ) )
				);
				?>
			</p>
		</div>
	<?php elseif ( 'no-gf' === $mkm_rf_import_notice ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Gravity Forms is not active on this site, so there is nothing to import.', 'mkm-review-funnel' ); ?></p>
		</div>
	<?php elseif ( $mkm_rf_import_notice ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'The import could not be completed. Check the form ID and try again.', 'mkm-review-funnel' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
		<?php settings_fields( 'mkm_rf_settings_group' ); ?>
		<?php $mkm_rf_name = MKM_RF_Settings::OPTION; ?>

		<h2 class="title"><?php esc_html_e( 'How the funnel behaves', 'mkm-review-funnel' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mkm_rf_mode"><?php esc_html_e( 'Mode', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<select name="<?php echo esc_attr( $mkm_rf_name ); ?>[mode]" id="mkm_rf_mode">
						<option value="open" <?php selected( $mkm_rf_settings['mode'], 'open' ); ?>><?php esc_html_e( 'Open - everyone sees both options', 'mkm-review-funnel' ); ?></option>
						<option value="gated" <?php selected( $mkm_rf_settings['mode'], 'gated' ); ?>><?php esc_html_e( 'Gated - ask about the experience first', 'mkm-review-funnel' ); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Gated mode shows the public review links only to visitors who report a positive experience. Google\'s review policy prohibits selectively soliciting positive reviews, so open mode is the safer default: it offers both the review links and the private feedback form to everyone.', 'mkm-review-funnel' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_heading"><?php esc_html_e( 'Heading', 'mkm-review-funnel' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mkm_rf_heading" name="<?php echo esc_attr( $mkm_rf_name ); ?>[heading]" value="<?php echo esc_attr( $mkm_rf_settings['heading'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_intro"><?php esc_html_e( 'Intro text', 'mkm-review-funnel' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="mkm_rf_intro" name="<?php echo esc_attr( $mkm_rf_name ); ?>[intro]"><?php echo esc_textarea( $mkm_rf_settings['intro'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Button labels', 'mkm-review-funnel' ); ?></th>
				<td>
					<p>
						<label><?php esc_html_e( 'Positive (gated mode)', 'mkm-review-funnel' ); ?><br />
							<input type="text" class="regular-text" name="<?php echo esc_attr( $mkm_rf_name ); ?>[positive_label]" value="<?php echo esc_attr( $mkm_rf_settings['positive_label'] ); ?>" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Negative (gated mode)', 'mkm-review-funnel' ); ?><br />
							<input type="text" class="regular-text" name="<?php echo esc_attr( $mkm_rf_name ); ?>[negative_label]" value="<?php echo esc_attr( $mkm_rf_settings['negative_label'] ); ?>" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Review link (open mode)', 'mkm-review-funnel' ); ?><br />
							<input type="text" class="regular-text" name="<?php echo esc_attr( $mkm_rf_name ); ?>[open_review_label]" value="<?php echo esc_attr( $mkm_rf_settings['open_review_label'] ); ?>" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Private feedback (open mode)', 'mkm-review-funnel' ); ?><br />
							<input type="text" class="regular-text" name="<?php echo esc_attr( $mkm_rf_name ); ?>[open_feedback_label]" value="<?php echo esc_attr( $mkm_rf_settings['open_feedback_label'] ); ?>" />
						</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_review_intro"><?php esc_html_e( 'Review popup text', 'mkm-review-funnel' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="mkm_rf_review_intro" name="<?php echo esc_attr( $mkm_rf_name ); ?>[review_intro]"><?php echo esc_textarea( $mkm_rf_settings['review_intro'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_feedback_intro"><?php esc_html_e( 'Feedback popup text', 'mkm-review-funnel' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="mkm_rf_feedback_intro" name="<?php echo esc_attr( $mkm_rf_name ); ?>[feedback_intro]"><?php echo esc_textarea( $mkm_rf_settings['feedback_intro'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_success"><?php esc_html_e( 'Thank-you message', 'mkm-review-funnel' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="mkm_rf_success" name="<?php echo esc_attr( $mkm_rf_name ); ?>[success_message]"><?php echo esc_textarea( $mkm_rf_settings['success_message'] ); ?></textarea></td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Review profiles', 'mkm-review-funnel' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mkm_rf_google"><?php esc_html_e( 'Google review URL', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="url" class="large-text code" id="mkm_rf_google" name="<?php echo esc_attr( $mkm_rf_name ); ?>[google_url]" value="<?php echo esc_attr( $mkm_rf_settings['google_url'] ); ?>" placeholder="https://g.page/r/…/review" />
					<p class="description"><?php esc_html_e( 'Use the "write a review" link from your Google Business Profile so visitors land straight on the review box.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_facebook"><?php esc_html_e( 'Facebook review URL', 'mkm-review-funnel' ); ?></label></th>
				<td><input type="url" class="large-text code" id="mkm_rf_facebook" name="<?php echo esc_attr( $mkm_rf_name ); ?>[facebook_url]" value="<?php echo esc_attr( $mkm_rf_settings['facebook_url'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_avvo"><?php esc_html_e( 'Avvo review URL', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="url" class="large-text code" id="mkm_rf_avvo" name="<?php echo esc_attr( $mkm_rf_name ); ?>[avvo_url]" value="<?php echo esc_attr( $mkm_rf_settings['avvo_url'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Leave a field empty to hide that profile.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Notifications', 'mkm-review-funnel' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Email on new feedback', 'mkm-review-funnel' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $mkm_rf_name ); ?>[notify_enabled]" value="1" <?php checked( $mkm_rf_settings['notify_enabled'], 1 ); ?> />
						<?php esc_html_e( 'Send an email when private feedback is submitted', 'mkm-review-funnel' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_emails"><?php esc_html_e( 'Recipients', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="mkm_rf_emails" name="<?php echo esc_attr( $mkm_rf_name ); ?>[notify_emails]" value="<?php echo esc_attr( $mkm_rf_settings['notify_emails'] ); ?>" />
					<p class="description">
						<?php
						printf(
							/* translators: %s: site administrator email address. */
							esc_html__( 'Comma separated. Leave empty to use the site administrator address (%s).', 'mkm-review-funnel' ),
							esc_html( get_option( 'admin_email' ) )
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_subject"><?php esc_html_e( 'Subject', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="text" class="large-text" id="mkm_rf_subject" name="<?php echo esc_attr( $mkm_rf_name ); ?>[notify_subject]" value="<?php echo esc_attr( $mkm_rf_settings['notify_subject'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Available tags: {site_name}, {sentiment}', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Consent and privacy', 'mkm-review-funnel' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mkm_rf_consent"><?php esc_html_e( 'Disclaimer text', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<textarea class="large-text" rows="5" id="mkm_rf_consent" name="<?php echo esc_attr( $mkm_rf_name ); ?>[consent_text]"><?php echo esc_textarea( $mkm_rf_settings['consent_text'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Shown next to the consent checkbox and stored with each submission. Leave empty to hide the checkbox.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Require consent', 'mkm-review-funnel' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $mkm_rf_name ); ?>[require_consent]" value="1" <?php checked( $mkm_rf_settings['require_consent'], 1 ); ?> />
						<?php esc_html_e( 'The checkbox must be ticked before the form can be submitted', 'mkm-review-funnel' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_store_ip"><?php esc_html_e( 'Store visitor IP', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<select name="<?php echo esc_attr( $mkm_rf_name ); ?>[store_ip]" id="mkm_rf_store_ip">
						<option value="hashed" <?php selected( $mkm_rf_settings['store_ip'], 'hashed' ); ?>><?php esc_html_e( 'Hashed (recommended)', 'mkm-review-funnel' ); ?></option>
						<option value="full" <?php selected( $mkm_rf_settings['store_ip'], 'full' ); ?>><?php esc_html_e( 'Full address', 'mkm-review-funnel' ); ?></option>
						<option value="none" <?php selected( $mkm_rf_settings['store_ip'], 'none' ); ?>><?php esc_html_e( 'Do not store', 'mkm-review-funnel' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Track review clicks', 'mkm-review-funnel' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $mkm_rf_name ); ?>[track_clicks]" value="1" <?php checked( $mkm_rf_settings['track_clicks'], 1 ); ?> />
						<?php esc_html_e( 'Record how many visitors click through to each review profile', 'mkm-review-funnel' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'On uninstall', 'mkm-review-funnel' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $mkm_rf_name ); ?>[delete_data_on_uninstall]" value="1" <?php checked( $mkm_rf_settings['delete_data_on_uninstall'], 1 ); ?> />
						<?php esc_html_e( 'Delete every stored entry when the plugin is deleted', 'mkm-review-funnel' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Off by default so feedback survives a reinstall.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Spam protection', 'mkm-review-funnel' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mkm_rf_min_seconds"><?php esc_html_e( 'Minimum fill-in time', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="number" min="0" max="60" id="mkm_rf_min_seconds" name="<?php echo esc_attr( $mkm_rf_name ); ?>[min_seconds]" value="<?php echo esc_attr( $mkm_rf_settings['min_seconds'] ); ?>" class="small-text" />
					<?php esc_html_e( 'seconds (0 disables the check)', 'mkm-review-funnel' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_rate_limit"><?php esc_html_e( 'Submissions per hour', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="number" min="0" max="100" id="mkm_rf_rate_limit" name="<?php echo esc_attr( $mkm_rf_name ); ?>[rate_limit]" value="<?php echo esc_attr( $mkm_rf_settings['rate_limit'] ); ?>" class="small-text" />
					<?php esc_html_e( 'per IP address (0 disables the limit)', 'mkm-review-funnel' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_captcha"><?php esc_html_e( 'Captcha', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<select name="<?php echo esc_attr( $mkm_rf_name ); ?>[captcha_provider]" id="mkm_rf_captcha">
						<option value="none" <?php selected( $mkm_rf_settings['captcha_provider'], 'none' ); ?>><?php esc_html_e( 'None', 'mkm-review-funnel' ); ?></option>
						<option value="recaptcha_v3" <?php selected( $mkm_rf_settings['captcha_provider'], 'recaptcha_v3' ); ?>><?php esc_html_e( 'Google reCAPTCHA v3', 'mkm-review-funnel' ); ?></option>
						<option value="turnstile" <?php selected( $mkm_rf_settings['captcha_provider'], 'turnstile' ); ?>><?php esc_html_e( 'Cloudflare Turnstile', 'mkm-review-funnel' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Register every domain the form runs on, staging included, or the widget will refuse to load there.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_site_key"><?php esc_html_e( 'Site key', 'mkm-review-funnel' ); ?></label></th>
				<td><input type="text" class="large-text code" id="mkm_rf_site_key" name="<?php echo esc_attr( $mkm_rf_name ); ?>[captcha_site_key]" value="<?php echo esc_attr( $mkm_rf_settings['captcha_site_key'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_secret_key"><?php esc_html_e( 'Secret key', 'mkm-review-funnel' ); ?></label></th>
				<td><input type="password" class="large-text code" id="mkm_rf_secret_key" name="<?php echo esc_attr( $mkm_rf_name ); ?>[captcha_secret_key]" value="<?php echo esc_attr( $mkm_rf_settings['captcha_secret_key'] ); ?>" autocomplete="new-password" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mkm_rf_threshold"><?php esc_html_e( 'reCAPTCHA v3 threshold', 'mkm-review-funnel' ); ?></label></th>
				<td>
					<input type="number" step="0.1" min="0" max="1" id="mkm_rf_threshold" name="<?php echo esc_attr( $mkm_rf_name ); ?>[captcha_threshold]" value="<?php echo esc_attr( $mkm_rf_settings['captcha_threshold'] ); ?>" class="small-text" />
					<p class="description"><?php esc_html_e( 'Scores below this are rejected. 0.5 is Google\'s default.', 'mkm-review-funnel' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<hr />

	<h2 class="title"><?php esc_html_e( 'Adding the funnel to a page', 'mkm-review-funnel' ); ?></h2>
	<p><?php esc_html_e( 'In WPBakery Page Builder, add the "Review Funnel" element. Anywhere else, paste this shortcode:', 'mkm-review-funnel' ); ?></p>
	<p><code>[mkm_review_funnel]</code></p>
	<p><?php esc_html_e( 'Every attribute is optional and overrides the settings above for that one placement:', 'mkm-review-funnel' ); ?></p>
	<p><code>[mkm_review_funnel mode="open" heading="Review Us" align="center"]</code></p>

	<?php if ( class_exists( 'GFAPI' ) ) : ?>
		<hr />
		<h2 class="title"><?php esc_html_e( 'Import from Gravity Forms', 'mkm-review-funnel' ); ?></h2>
		<p><?php esc_html_e( 'Copy existing entries from the Gravity Forms form the old review popup used, so past feedback lives alongside the new submissions. Entries are copied, not moved: nothing is removed from Gravity Forms.', 'mkm-review-funnel' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'mkm_rf_import_gf' ); ?>
			<input type="hidden" name="action" value="mkm_rf_import_gf" />
			<p>
				<label for="mkm_rf_gf_form_id"><?php esc_html_e( 'Gravity Forms form ID', 'mkm-review-funnel' ); ?></label>
				<input type="number" min="1" id="mkm_rf_gf_form_id" name="gf_form_id" class="small-text" />
			</p>
			<?php submit_button( __( 'Import entries', 'mkm-review-funnel' ), 'secondary', 'submit', false ); ?>
		</form>
	<?php endif; ?>
</div>
