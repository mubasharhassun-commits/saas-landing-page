/**
 * Testimonial Manager - accessible "read full review" modal.
 *
 * One modal serves every card on the page. Each card carries its full
 * testimonial in an inert <template>, so opening one needs no network request
 * and works behind full-page caching.
 *
 * No jQuery, no dependencies.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

	/* The modal is re-parented to <body>, so it inherits nothing from the grid.
	   Every token the popup renders with is copied across on open, which is what
	   makes the popup match the card it came from. */
	var STYLE_VARS = [
		'--tm-accent',
		'--tm-text-color',
		'--tm-role-color',
		'--tm-star-color',
		'--tm-star-empty-color',
		'--tm-star-size',
		'--tm-avatar-size',
		'--tm-modal-max-width',
		'--tm-modal-bg',
		'--tm-modal-overlay',
		'--tm-modal-text-color',
		'--tm-modal-font-size',
		'--tm-modal-radius',
		'--tm-modal-padding',
		'--tm-modal-close-bg',
		'--tm-modal-close-color',
		'--tm-close-top',
		'--tm-close-right',
		'--tm-close-left',
		'--tm-close-transform',
		'--tm-close-size',
		'--tm-name-color'
	];

	var modal   = null;
	var dialog  = null;
	var body    = null;
	var opener  = null;

	function init() {
		modal = document.getElementById( 'tm-modal' );

		if ( ! modal ) {
			return;
		}

		dialog = modal.querySelector( '.tm-modal-dialog' );
		body   = modal.querySelector( '.tm-modal-body' );

		/* A fixed-position element is positioned against a transformed ancestor
		   rather than the viewport, and page-builder rows do use transforms.
		   Re-parenting to <body> keeps the modal centred on screen. */
		if ( modal.parentNode !== document.body ) {
			document.body.appendChild( modal );
		}

		document.addEventListener( 'click', onClick );
		document.addEventListener( 'keydown', onKeydown );
	}

	function onClick( event ) {
		var trigger = event.target.closest( '[data-tm-open]' );

		if ( trigger ) {
			event.preventDefault();
			open( trigger );
			return;
		}

		if ( event.target.closest( '[data-tm-close]' ) ) {
			event.preventDefault();
			close();
		}
	}

	function onKeydown( event ) {
		if ( modal.hidden ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			event.preventDefault();
			close();
			return;
		}

		if ( 'Tab' === event.key ) {
			trapFocus( event );
		}
	}

	function open( trigger ) {
		var template = document.getElementById( trigger.getAttribute( 'data-tm-open' ) );

		if ( ! template || ! template.content ) {
			return;
		}

		opener = trigger;

		body.innerHTML = '';
		body.appendChild( template.content.cloneNode( true ) );

		applyGridStyles( trigger.closest( '.tm-grid' ) );

		modal.hidden = false;
		document.body.classList.add( 'tm-modal-open' );

		dialog.scrollTop = 0;
		dialog.focus();
	}

	function close() {
		if ( modal.hidden ) {
			return;
		}

		modal.hidden = true;
		document.body.classList.remove( 'tm-modal-open' );
		body.innerHTML = '';

		/* Send focus back where it came from, so keyboard users do not lose
		   their place in the grid. */
		if ( opener && document.contains( opener ) ) {
			opener.focus();
		}

		opener = null;
	}

	/**
	 * Copy this grid's popup custom properties onto the shared modal.
	 */
	function applyGridStyles( grid ) {
		STYLE_VARS.forEach( function ( name ) {
			modal.style.removeProperty( name );
		} );

		// The quote mark is opt-in per grid, and the popup lives outside it.
		modal.classList.remove( 'tm-show-quote' );

		if ( ! grid ) {
			return;
		}

		if ( grid.classList.contains( 'tm-show-quote' ) ) {
			modal.classList.add( 'tm-show-quote' );
		}

		var computed = window.getComputedStyle( grid );

		STYLE_VARS.forEach( function ( name ) {
			var value = computed.getPropertyValue( name );

			if ( value && value.trim() ) {
				modal.style.setProperty( name, value.trim() );
			}
		} );
	}

	/**
	 * Keep Tab and Shift+Tab inside the dialog while it is open.
	 */
	function trapFocus( event ) {
		var items = Array.prototype.filter.call(
			dialog.querySelectorAll( FOCUSABLE ),
			function ( el ) {
				return null !== el.offsetParent;
			}
		);

		if ( ! items.length ) {
			event.preventDefault();
			dialog.focus();
			return;
		}

		var first = items[ 0 ];
		var last  = items[ items.length - 1 ];

		if ( event.shiftKey && ( document.activeElement === first || document.activeElement === dialog ) ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	/* Both builders swap element markup in their editors without reloading. */
	document.addEventListener( 'vc-full-width-row', function () {
		if ( ! modal ) {
			init();
		}
	} );

	window.addEventListener( 'elementor/frontend/init', function () {
		if ( ! modal ) {
			init();
		}
	} );
}() );
