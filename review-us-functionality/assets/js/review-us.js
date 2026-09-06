/**
 * MKM Review Us — popup flow and built-in feedback form.
 *
 * Vanilla JS, no dependencies. Gravity Forms and Contact Form 7 bring their own
 * scripts; this file only reacts to their success events.
 */
( function () {
	'use strict';

	var config = window.mkmReviewUs || {};
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	var openDialog = null;
	var lastFocused = null;
	var scrollPosition = 0;
	var renderedAt = Date.now();

	function focusable( container ) {
		return Array.prototype.filter.call(
			container.querySelectorAll( FOCUSABLE ),
			function ( el ) {
				return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
			}
		);
	}

	function lockScroll() {
		scrollPosition = window.pageYOffset || document.documentElement.scrollTop || 0;
		document.body.style.top = '-' + scrollPosition + 'px';
		document.body.style.position = 'fixed';
		document.body.style.width = '100%';
		document.documentElement.classList.add( 'mkm-review-locked' );
	}

	function unlockScroll() {
		document.documentElement.classList.remove( 'mkm-review-locked' );
		document.body.style.position = '';
		document.body.style.top = '';
		document.body.style.width = '';
		window.scrollTo( 0, scrollPosition );
	}

	function open( id, opener ) {
		var overlay = document.getElementById( id );

		if ( ! overlay ) {
			return;
		}

		var wasOpen = openDialog;

		if ( wasOpen && wasOpen !== overlay ) {
			hide( wasOpen );
		} else if ( ! wasOpen ) {
			lastFocused = opener || document.activeElement;
			lockScroll();
		}

		overlay.hidden = false;
		// Force a reflow so the fade transition runs on first paint.
		void overlay.offsetWidth;
		overlay.classList.add( 'is-visible' );
		openDialog = overlay;

		var targets = focusable( overlay );
		var preferred = overlay.querySelector( '[data-mkm-autofocus]' ) || targets[ 0 ];

		if ( preferred ) {
			preferred.focus();
		}
	}

	function hide( overlay ) {
		overlay.classList.remove( 'is-visible' );
		overlay.hidden = true;
	}

	function close() {
		if ( ! openDialog ) {
			return;
		}

		hide( openDialog );
		openDialog = null;
		unlockScroll();

		if ( lastFocused && typeof lastFocused.focus === 'function' ) {
			lastFocused.focus();
		}

		lastFocused = null;
	}

	function trapFocus( event ) {
		if ( ! openDialog || 'Tab' !== event.key ) {
			return;
		}

		var targets = focusable( openDialog );

		if ( ! targets.length ) {
			event.preventDefault();

			return;
		}

		var first = targets[ 0 ];
		var last = targets[ targets.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		} else if ( ! openDialog.contains( document.activeElement ) ) {
			event.preventDefault();
			first.focus();
		}
	}

	function bindDialogs() {
		document.addEventListener( 'click', function ( event ) {
			if ( ! event.target || ! event.target.closest ) {
				return;
			}

			var opener = event.target.closest( '[data-mkm-open]' );

			if ( opener ) {
				event.preventDefault();
				open( opener.getAttribute( 'data-mkm-open' ), opener );

				return;
			}

			if ( event.target.closest( '[data-mkm-close]' ) ) {
				event.preventDefault();
				close();

				return;
			}

			// Click on the overlay itself, outside the dialog box.
			if ( openDialog && event.target === openDialog ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key || 'Esc' === event.key ) {
				close();

				return;
			}

			trapFocus( event );
		} );
	}

	function setFieldError( form, name, message ) {
		var input = form.querySelector( '[name="' + name + '"]' );
		var holder = form.querySelector( '[data-mkm-error-for="' + name + '"]' );
		var field = input ? input.closest( '.mkm-feedback-field' ) : null;

		if ( holder ) {
			holder.textContent = message || '';
		}

		if ( field ) {
			field.classList.toggle( 'has-error', !! message );
		}

		if ( input ) {
			if ( message ) {
				input.setAttribute( 'aria-invalid', 'true' );
			} else {
				input.removeAttribute( 'aria-invalid' );
			}
		}
	}

	function clearErrors( form ) {
		Array.prototype.forEach.call(
			form.querySelectorAll( '[data-mkm-error-for]' ),
			function ( holder ) {
				setFieldError( form, holder.getAttribute( 'data-mkm-error-for' ), '' );
			}
		);
	}

	function validate( form ) {
		var errors = {};
		var required = [ 'first_name', 'last_name', 'email', 'reason', 'feedback_message' ];

		required.forEach( function ( name ) {
			var input = form.querySelector( '[name="' + name + '"]' );

			if ( input && '' === input.value.trim() ) {
				errors[ name ] = config.i18n ? config.i18n.required : 'This field is required.';
			}
		} );

		var email = form.querySelector( '[name="email"]' );

		if ( email && email.value.trim() && ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( email.value.trim() ) ) {
			errors.email = 'Please enter a valid email address.';
		}

		return errors;
	}

	function showSuccess( popup, message ) {
		var wrap = popup.querySelector( '[data-mkm-form-wrap]' );
		var success = popup.querySelector( '[data-mkm-success]' );

		if ( wrap ) {
			wrap.hidden = true;
		}

		if ( success ) {
			if ( message ) {
				var text = success.querySelector( '.mkm-feedback-success__text' );

				if ( text ) {
					text.textContent = message;
				}
			}

			success.hidden = false;

			var closeButton = success.querySelector( '[data-mkm-close]' );

			if ( closeButton ) {
				closeButton.focus();
			}
		}
	}

	function bindBuiltinForm() {
		var form = document.querySelector( '[data-mkm-feedback-form]' );

		if ( ! form ) {
			return;
		}

		var submitting = false;

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			if ( submitting ) {
				return;
			}

			var button = form.querySelector( '[data-mkm-submit]' );
			var label = form.querySelector( '[data-mkm-submit-label]' );
			var spinner = form.querySelector( '.mkm-feedback-spinner' );
			var summary = form.querySelector( '[data-mkm-form-error]' );
			var originalLabel = label ? label.textContent : '';

			clearErrors( form );

			if ( summary ) {
				summary.hidden = true;
				summary.textContent = '';
			}

			var errors = validate( form );
			var names = Object.keys( errors );

			if ( names.length ) {
				names.forEach( function ( name ) {
					setFieldError( form, name, errors[ name ] );
				} );

				var firstInput = form.querySelector( '[name="' + names[ 0 ] + '"]' );

				if ( firstInput ) {
					firstInput.focus();
				}

				return;
			}

			submitting = true;

			if ( button ) {
				button.disabled = true;
				button.setAttribute( 'aria-busy', 'true' );
			}

			if ( label && config.i18n ) {
				label.textContent = config.i18n.submitting;
			}

			if ( spinner ) {
				spinner.hidden = false;
			}

			var payload = new FormData( form );
			payload.append( 'action', config.action );
			payload.append( 'nonce', config.nonce );
			payload.append( 'mkm_elapsed', String( Date.now() - renderedAt ) );

			window
				.fetch( config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: payload,
				} )
				.then( function ( response ) {
					return response.json().catch( function () {
						return { success: false, data: {} };
					} );
				} )
				.then( function ( result ) {
					if ( result && result.success ) {
						showSuccess(
							document.getElementById( 'mkm-review-popup-negative' ),
							result.data ? result.data.message : ''
						);

						return;
					}

					var data = ( result && result.data ) || {};

					if ( data.fields ) {
						Object.keys( data.fields ).forEach( function ( name ) {
							setFieldError( form, name, data.fields[ name ] );
						} );
					}

					if ( summary ) {
						summary.textContent = data.message || ( config.i18n ? config.i18n.genericError : 'Something went wrong.' );
						summary.hidden = false;
						summary.focus && summary.focus();
					}
				} )
				.catch( function () {
					if ( summary ) {
						summary.textContent = config.i18n ? config.i18n.genericError : 'Something went wrong.';
						summary.hidden = false;
					}
				} )
				.finally( function () {
					submitting = false;

					if ( button ) {
						button.disabled = false;
						button.removeAttribute( 'aria-busy' );
					}

					if ( label ) {
						label.textContent = originalLabel;
					}

					if ( spinner ) {
						spinner.hidden = true;
					}
				} );
		} );
	}

	function bindThirdPartyForms() {
		// Contact Form 7 fires this on the document after a successful send.
		document.addEventListener( 'wpcf7mailsent', function () {
			showSuccess( document.getElementById( 'mkm-review-popup-negative' ), '' );
		} );

		// Gravity Forms renders its confirmation inside the popup; keep it in view.
		if ( window.jQuery ) {
			window.jQuery( document ).on( 'gform_confirmation_loaded', function () {
				var popup = document.getElementById( 'mkm-review-popup-negative' );

				if ( popup ) {
					popup.scrollTop = 0;
				}
			} );
		}
	}

	function init() {
		bindDialogs();
		bindBuiltinForm();
		bindThirdPartyForms();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
