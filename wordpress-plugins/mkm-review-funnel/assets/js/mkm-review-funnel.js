/**
 * Review Funnel front end.
 *
 * No jQuery, no build step. Handles the modals, the outbound click tracking
 * and the private feedback submission.
 */
( function () {
	'use strict';

	var config = window.mkmReviewFunnel || {};
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	var token = null;
	var tokenFetchedAt = 0;
	var tokenRequest = null;

	/**
	 * Fetches (and caches for 30 minutes) the nonce and signed timestamp.
	 * Requested separately from the page so a full-page cache cannot serve a stale one.
	 *
	 * @return {Promise<Object|null>} Token payload.
	 */
	function getToken() {
		var fresh = token && Date.now() - tokenFetchedAt < 30 * 60 * 1000;

		if ( fresh ) {
			return Promise.resolve( token );
		}

		if ( tokenRequest ) {
			return tokenRequest;
		}

		tokenRequest = fetch( config.tokenUrl, {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json' }
		} )
			.then( function ( response ) {
				return response.ok ? response.json() : null;
			} )
			.then( function ( data ) {
				tokenRequest = null;

				if ( data && data.nonce ) {
					token = data;
					tokenFetchedAt = Date.now();
				}

				return token;
			} )
			.catch( function () {
				tokenRequest = null;
				return null;
			} );

		return tokenRequest;
	}

	/**
	 * Resolves a captcha response token, when a captcha is configured.
	 *
	 * @param {HTMLFormElement} form The feedback form.
	 * @return {Promise<string>} Captcha token, or an empty string.
	 */
	function getCaptchaToken( form ) {
		var captcha = config.captcha || {};

		if ( ! captcha.provider || captcha.provider === 'none' || ! captcha.siteKey ) {
			return Promise.resolve( '' );
		}

		if ( captcha.provider === 'recaptcha_v3' ) {
			if ( ! window.grecaptcha || ! window.grecaptcha.execute ) {
				return Promise.resolve( '' );
			}

			return new Promise( function ( resolve ) {
				window.grecaptcha.ready( function () {
					window.grecaptcha
						.execute( captcha.siteKey, { action: 'review_feedback' } )
						.then( resolve )
						.catch( function () {
							resolve( '' );
						} );
				} );
			} );
		}

		if ( captcha.provider === 'turnstile' ) {
			var holder = form.querySelector( '[data-mkm-rf-captcha]' );

			if ( ! holder || ! window.turnstile ) {
				return Promise.resolve( '' );
			}

			var existing = window.turnstile.getResponse( holder.dataset.widgetId );

			return Promise.resolve( existing || '' );
		}

		return Promise.resolve( '' );
	}

	/**
	 * Renders the Turnstile widget once its script is ready.
	 *
	 * @param {HTMLElement} root Funnel root element.
	 */
	function renderTurnstile( root, attempt ) {
		var captcha = config.captcha || {};

		if ( captcha.provider !== 'turnstile' || ! captcha.siteKey ) {
			return;
		}

		// The Turnstile script is deferred; give it a few moments if it is not up yet.
		if ( ! window.turnstile ) {
			attempt = attempt || 0;

			if ( attempt < 10 ) {
				window.setTimeout( function () {
					renderTurnstile( root, attempt + 1 );
				}, 300 );
			}

			return;
		}

		root.querySelectorAll( '[data-mkm-rf-captcha]' ).forEach( function ( holder ) {
			if ( holder.dataset.widgetId ) {
				return;
			}

			holder.dataset.widgetId = window.turnstile.render( holder, {
				sitekey: captcha.siteKey
			} );
		} );
	}

	/**
	 * Traps keyboard focus inside an open dialog.
	 *
	 * @param {HTMLElement} modal   Modal element.
	 * @param {KeyboardEvent} event Key event.
	 */
	function trapFocus( modal, event ) {
		var nodes = Array.prototype.filter.call(
			modal.querySelectorAll( FOCUSABLE ),
			function ( node ) {
				return node.offsetParent !== null;
			}
		);

		if ( ! nodes.length ) {
			return;
		}

		var first = nodes[ 0 ];
		var last = nodes[ nodes.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	/**
	 * Wires up one funnel instance.
	 *
	 * @param {HTMLElement} root Funnel root element.
	 */
	function setup( root ) {
		var modals = {};
		var lastFocused = null;

		root.querySelectorAll( '[data-mkm-rf-modal]' ).forEach( function ( modal ) {
			modals[ modal.getAttribute( 'data-mkm-rf-modal' ) ] = modal;
		} );

		function showPane( modal, name, site ) {
			modal.querySelectorAll( '[data-mkm-rf-pane]' ).forEach( function ( pane ) {
				var matches = pane.getAttribute( 'data-mkm-rf-pane' ) === name &&
					( ! site || pane.getAttribute( 'data-mkm-rf-site-pane' ) === site );

				pane.hidden = ! matches;
			} );
		}

		function open( name ) {
			var modal = modals[ name ];

			if ( ! modal ) {
				return;
			}

			lastFocused = document.activeElement;
			modal.hidden = false;
			document.body.classList.add( 'mkm-rf-modal-open' );

			if ( name === 'review' ) {
				showPane( modal, 'chooser' );
			}

			// The token is only needed once a modal is in play.
			getToken();
			renderTurnstile( root );

			var focusTarget = modal.querySelector( FOCUSABLE );

			if ( focusTarget ) {
				focusTarget.focus();
			}
		}

		function close( modal ) {
			modal.hidden = true;
			document.body.classList.remove( 'mkm-rf-modal-open' );

			if ( lastFocused && typeof lastFocused.focus === 'function' ) {
				lastFocused.focus();
			}
		}

		function closeAll() {
			Object.keys( modals ).forEach( function ( key ) {
				if ( ! modals[ key ].hidden ) {
					close( modals[ key ] );
				}
			} );
		}

		root.querySelectorAll( '[data-mkm-rf-open]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				open( button.getAttribute( 'data-mkm-rf-open' ) );
			} );
		} );

		root.querySelectorAll( '[data-mkm-rf-dismiss]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var modal = button.closest( '[data-mkm-rf-modal]' );

				if ( modal ) {
					close( modal );
				}
			} );
		} );

		root.querySelectorAll( '[data-mkm-rf-site]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				showPane( modals.review, 'site', button.getAttribute( 'data-mkm-rf-site' ) );

				var pane = modals.review.querySelector( '[data-mkm-rf-site-pane="' + button.getAttribute( 'data-mkm-rf-site' ) + '"]' );
				var focusTarget = pane && pane.querySelector( FOCUSABLE );

				if ( focusTarget ) {
					focusTarget.focus();
				}
			} );
		} );

		root.querySelectorAll( '[data-mkm-rf-back]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				showPane( modals.review, 'chooser' );
			} );
		} );

		// Outbound click tracking. keepalive lets the request survive the navigation.
		root.querySelectorAll( '[data-mkm-rf-go]' ).forEach( function ( link ) {
			link.addEventListener( 'click', function () {
				if ( ! Number( config.trackClicks ) ) {
					return;
				}

				getToken().then( function ( data ) {
					if ( ! data ) {
						return;
					}

					fetch( config.clickUrl, {
						method: 'POST',
						credentials: 'same-origin',
						keepalive: true,
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify( {
							destination: link.getAttribute( 'data-mkm-rf-go' ),
							nonce: data.nonce,
							ts: data.ts,
							sig: data.sig,
							source_url: window.location.href
						} )
					} ).catch( function () {} );
				} );
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			var openModal = null;

			Object.keys( modals ).forEach( function ( key ) {
				if ( ! modals[ key ].hidden ) {
					openModal = modals[ key ];
				}
			} );

			if ( ! openModal ) {
				return;
			}

			if ( event.key === 'Escape' ) {
				closeAll();
			} else if ( event.key === 'Tab' ) {
				trapFocus( openModal, event );
			}
		} );

		var form = root.querySelector( '[data-mkm-rf-form]' );

		if ( form ) {
			setupForm( root, form );
		}
	}

	/**
	 * Wires the feedback form.
	 *
	 * @param {HTMLElement}     root Funnel root element.
	 * @param {HTMLFormElement} form Feedback form.
	 */
	function setupForm( root, form ) {
		var submit = form.querySelector( '[data-mkm-rf-submit]' );
		var status = form.querySelector( '[data-mkm-rf-status]' );
		var success = root.querySelector( '[data-mkm-rf-success]' );
		var submitLabel = submit ? submit.textContent : '';

		function clearErrors() {
			form.querySelectorAll( '[data-mkm-rf-error]' ).forEach( function ( node ) {
				node.textContent = '';
			} );

			form.querySelectorAll( '.mkm-rf-field--invalid' ).forEach( function ( node ) {
				node.classList.remove( 'mkm-rf-field--invalid' );
			} );

			if ( status ) {
				status.textContent = '';
				status.classList.remove( 'mkm-rf-form__status--error' );
			}
		}

		function showFieldErrors( fields ) {
			Object.keys( fields || {} ).forEach( function ( field ) {
				var node = form.querySelector( '[data-mkm-rf-error="' + field + '"]' );

				if ( ! node ) {
					return;
				}

				node.textContent = fields[ field ];

				var wrapper = node.closest( '.mkm-rf-field' );

				if ( wrapper ) {
					wrapper.classList.add( 'mkm-rf-field--invalid' );
				}
			} );
		}

		function setBusy( busy ) {
			if ( ! submit ) {
				return;
			}

			submit.disabled = busy;
			submit.textContent = busy ? ( config.i18n && config.i18n.sending ) || 'Sending…' : submitLabel;
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			clearErrors();
			setBusy( true );

			Promise.all( [ getToken(), getCaptchaToken( form ) ] )
				.then( function ( results ) {
					var data = results[ 0 ];
					var captcha = results[ 1 ];

					if ( ! data ) {
						throw new Error( 'token' );
					}

					var payload = {
						first_name: form.elements.first_name.value,
						last_name: form.elements.last_name.value,
						email: form.elements.email.value,
						phone: form.elements.phone.value,
						client_status: form.elements.client_status ? form.elements.client_status.value : '',
						message: form.elements.message.value,
						consent: form.elements.consent && form.elements.consent.checked ? '1' : '',
						website: form.elements.website ? form.elements.website.value : '',
						sentiment: 'negative',
						nonce: data.nonce,
						ts: data.ts,
						sig: data.sig,
						captcha: captcha,
						source_url: window.location.href
					};

					return fetch( config.feedbackUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify( payload )
					} );
				} )
				.then( function ( response ) {
					return response.json().then( function ( body ) {
						return { ok: response.ok, body: body };
					} );
				} )
				.then( function ( result ) {
					setBusy( false );

					if ( result.ok ) {
						form.hidden = true;

						if ( success ) {
							success.hidden = false;
							success.setAttribute( 'tabindex', '-1' );
							success.focus();
						}

						return;
					}

					var body = result.body || {};
					var fields = body.data && body.data.fields ? body.data.fields : null;

					if ( fields ) {
						showFieldErrors( fields );
					}

					if ( status ) {
						status.textContent = body.message || ( config.i18n && config.i18n.genericError ) || '';
						status.classList.add( 'mkm-rf-form__status--error' );
					}

					// A rejected nonce is usually a stale token; drop it so the retry gets a new one.
					token = null;
				} )
				.catch( function () {
					setBusy( false );
					token = null;

					if ( status ) {
						status.textContent = ( config.i18n && config.i18n.genericError ) || 'Something went wrong.';
						status.classList.add( 'mkm-rf-form__status--error' );
					}
				} );
		} );
	}

	function init() {
		document.querySelectorAll( '[data-mkm-rf]' ).forEach( setup );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
