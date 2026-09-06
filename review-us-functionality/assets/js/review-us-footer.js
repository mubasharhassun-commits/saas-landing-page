/**
 * Optional fallback for page-builder footers.
 *
 * WordPress menu items are repointed server side. This only runs when the
 * "Footer button fallback" setting is enabled, for footers built with
 * Elementor, WPBakery or similar, where the link lives in builder data.
 */
( function () {
	'use strict';

	var config = window.mkmReviewUsFooter || {};

	if ( ! config.url ) {
		return;
	}

	function repoint() {
		var containers = document.querySelectorAll( 'footer, .site-footer, [data-elementor-type="footer"], .elementor-location-footer' );

		Array.prototype.forEach.call( containers, function ( container ) {
			Array.prototype.forEach.call( container.querySelectorAll( 'a' ), function ( link ) {
				var text = ( link.textContent || '' ).trim().toLowerCase();

				if ( text === ( config.label || 'review us' ) ) {
					link.setAttribute( 'href', config.url );
				}
			} );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', repoint );
	} else {
		repoint();
	}
} )();
