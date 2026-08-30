/**
 * Testimonial Manager - admin.
 *
 * Drives the Client Image picker in the Testimonial Details box using the
 * WordPress media frame. No jQuery.
 */
( function () {
	'use strict';

	function init() {
		var wrap = document.getElementById( 'tm-image-field' );

		if ( ! wrap || ! window.wp || ! window.wp.media ) {
			return;
		}

		var input   = document.getElementById( 'tm_image_id' );
		var preview = wrap.querySelector( '.tm-image-preview' );
		var select  = wrap.querySelector( '.tm-image-select' );
		var remove  = wrap.querySelector( '.tm-image-remove' );
		var frame;

		function show( url ) {
			preview.textContent = '';

			if ( ! url ) {
				return;
			}

			var img = document.createElement( 'img' );
			img.src = url;
			img.alt = '';
			preview.appendChild( img );
		}

		select.addEventListener( 'click', function ( event ) {
			event.preventDefault();

			if ( ! frame ) {
				frame = window.wp.media( {
					title:    wrap.getAttribute( 'data-title' ),
					button:   { text: wrap.getAttribute( 'data-button' ) },
					library:  { type: 'image' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var item = frame.state().get( 'selection' ).first().toJSON();
					var url  = ( item.sizes && item.sizes.thumbnail ) ? item.sizes.thumbnail.url : item.url;

					input.value = item.id;
					show( url );
					wrap.classList.add( 'has-image' );
				} );
			}

			frame.open();
		} );

		remove.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			input.value = '';
			show( '' );
			wrap.classList.remove( 'has-image' );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
