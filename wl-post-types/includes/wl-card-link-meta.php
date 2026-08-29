<?php
/**
 * Per-post "Card Link" field for Vessels & Cases.
 *
 * Lets an editor point a card at any URL of their choosing instead of the
 * post's own single page. Blank falls back to the permalink.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Post types that get the field. */
function waterslaw_card_link_post_types() {
	return array( 'vessels', 'cases' );
}

function waterslaw_add_card_link_meta_box() {
	foreach ( waterslaw_card_link_post_types() as $screen ) {
		add_meta_box(
			'wl_card_link',
			__( 'Card Link', 'waterslaw' ),
			'waterslaw_render_card_link_meta_box',
			$screen,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'waterslaw_add_card_link_meta_box' );

function waterslaw_render_card_link_meta_box( $post ) {
	wp_nonce_field( 'wl_card_link_save', 'wl_card_link_nonce' );
	$value = get_post_meta( $post->ID, '_wl_card_link', true );
	?>
	<p>
		<label for="wl_card_link_field"><strong><?php esc_html_e( 'Custom URL', 'waterslaw' ); ?></strong></label>
		<input type="url" id="wl_card_link_field" name="wl_card_link" class="widefat"
		       value="<?php echo esc_attr( $value ); ?>" placeholder="https://example.com/page/" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Where the WL Cards widget sends visitors who click this item. Leave blank to use this post\'s own page.', 'waterslaw' ); ?>
	</p>
	<?php
}

function waterslaw_save_card_link_meta( $post_id ) {
	if ( ! isset( $_POST['wl_card_link_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wl_card_link_nonce'] ) ), 'wl_card_link_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$url = isset( $_POST['wl_card_link'] ) ? esc_url_raw( trim( wp_unslash( $_POST['wl_card_link'] ) ) ) : '';

	if ( '' === $url ) {
		delete_post_meta( $post_id, '_wl_card_link' );
	} else {
		update_post_meta( $post_id, '_wl_card_link', $url );
	}
}
add_action( 'save_post', 'waterslaw_save_card_link_meta' );

/**
 * Resolve a card's destination: the custom URL if one is set, else the permalink.
 */
function waterslaw_get_card_link( $post_id ) {
	$custom = get_post_meta( $post_id, '_wl_card_link', true );
	return $custom ? $custom : get_permalink( $post_id );
}
