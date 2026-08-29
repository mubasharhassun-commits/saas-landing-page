<?php
/**
 * Per-post "Card Settings" for Vessels & Cases.
 *
 * Gives each item its own card text (use the post title, write custom text,
 * or show none at all) and its own destination URL. Both are read by the
 * WL Cards widget at render time.
 *
 * @package waterslaw
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Post types that get the fields. */
function waterslaw_card_meta_post_types() {
	return array( 'vessels', 'cases' );
}

function waterslaw_add_card_meta_box() {
	foreach ( waterslaw_card_meta_post_types() as $screen ) {
		add_meta_box(
			'wl_card_settings',
			__( 'Card Settings', 'waterslaw' ),
			'waterslaw_render_card_meta_box',
			$screen,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'waterslaw_add_card_meta_box' );

function waterslaw_render_card_meta_box( $post ) {
	wp_nonce_field( 'wl_card_meta_save', 'wl_card_meta_nonce' );

	$mode = get_post_meta( $post->ID, '_wl_card_text_mode', true );
	$mode = in_array( $mode, array( 'custom', 'none' ), true ) ? $mode : 'title';
	$text = get_post_meta( $post->ID, '_wl_card_text', true );
	$link = get_post_meta( $post->ID, '_wl_card_link', true );
	?>
	<p>
		<label for="wl_card_text_mode"><strong><?php esc_html_e( 'Text on Image', 'waterslaw' ); ?></strong></label>
		<select id="wl_card_text_mode" name="wl_card_text_mode" class="widefat">
			<option value="title"  <?php selected( $mode, 'title' ); ?>><?php esc_html_e( 'Use the post title', 'waterslaw' ); ?></option>
			<option value="custom" <?php selected( $mode, 'custom' ); ?>><?php esc_html_e( 'Custom text', 'waterslaw' ); ?></option>
			<option value="none"   <?php selected( $mode, 'none' ); ?>><?php esc_html_e( 'No text', 'waterslaw' ); ?></option>
		</select>
	</p>

	<p id="wl_card_text_row">
		<label for="wl_card_text"><strong><?php esc_html_e( 'Custom Text', 'waterslaw' ); ?></strong></label>
		<textarea id="wl_card_text" name="wl_card_text" class="widefat" rows="2"
		          placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
	</p>

	<p>
		<label for="wl_card_link"><strong><?php esc_html_e( 'Card Link', 'waterslaw' ); ?></strong></label>
		<input type="url" id="wl_card_link" name="wl_card_link" class="widefat"
		       value="<?php echo esc_attr( $link ); ?>" placeholder="https://example.com/page/" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Where a click on this card goes. Leave blank to use this post\'s own page.', 'waterslaw' ); ?>
	</p>

	<script>
	( function () {
		var sel = document.getElementById( 'wl_card_text_mode' ),
		    row = document.getElementById( 'wl_card_text_row' );
		if ( ! sel || ! row ) { return; }
		function sync() { row.style.display = ( 'custom' === sel.value ) ? '' : 'none'; }
		sel.addEventListener( 'change', sync );
		sync();
	} )();
	</script>
	<?php
}

function waterslaw_save_card_meta( $post_id ) {
	if ( ! isset( $_POST['wl_card_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wl_card_meta_nonce'] ) ), 'wl_card_meta_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/* Text mode */
	$mode = isset( $_POST['wl_card_text_mode'] ) ? sanitize_key( wp_unslash( $_POST['wl_card_text_mode'] ) ) : 'title';
	if ( in_array( $mode, array( 'custom', 'none' ), true ) ) {
		update_post_meta( $post_id, '_wl_card_text_mode', $mode );
	} else {
		delete_post_meta( $post_id, '_wl_card_text_mode' );
	}

	/* Custom text */
	$text = isset( $_POST['wl_card_text'] ) ? sanitize_text_field( wp_unslash( $_POST['wl_card_text'] ) ) : '';
	if ( '' === $text ) {
		delete_post_meta( $post_id, '_wl_card_text' );
	} else {
		update_post_meta( $post_id, '_wl_card_text', $text );
	}

	/* Link */
	$url = isset( $_POST['wl_card_link'] ) ? esc_url_raw( trim( wp_unslash( $_POST['wl_card_link'] ) ) ) : '';
	if ( '' === $url ) {
		delete_post_meta( $post_id, '_wl_card_link' );
	} else {
		update_post_meta( $post_id, '_wl_card_link', $url );
	}
}
add_action( 'save_post', 'waterslaw_save_card_meta' );

/**
 * Resolve a card's overlay text. Returns '' when the card should show none.
 */
function waterslaw_get_card_text( $post_id ) {
	$mode = get_post_meta( $post_id, '_wl_card_text_mode', true );

	if ( 'none' === $mode ) {
		return '';
	}
	if ( 'custom' === $mode ) {
		$text = (string) get_post_meta( $post_id, '_wl_card_text', true );
		return ( '' !== $text ) ? $text : get_the_title( $post_id );
	}
	return get_the_title( $post_id );
}

/**
 * Resolve a card's destination: the custom URL if one is set, else the permalink.
 */
function waterslaw_get_card_link( $post_id ) {
	$custom = get_post_meta( $post_id, '_wl_card_link', true );
	return $custom ? $custom : get_permalink( $post_id );
}
