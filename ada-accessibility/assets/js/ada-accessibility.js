/**
 * ADA Accessibility
 *
 * No dependencies. Text scaling reads each element's computed font size and
 * scales from that cached base, so it works on themes that hard-code pixels.
 */
( function () {
	'use strict';

	// One press enlarges by 10%, the next press returns to normal.
	var STEPS = [ 1, 1.1 ];
	var EXCLUDE = '.adaa, .adaa *, script, style, svg, path, circle, br, hr';

	function init( root ) {
		if ( root.dataset.adaaReady ) {
			return;
		}
		root.dataset.adaaReady = '1';

		var launcher = root.querySelector( '.adaa__launcher' );
		var panel = root.querySelector( '.adaa__panel' );

		if ( ! launcher || ! panel ) {
			return;
		}

		var state = { step: 0, contrast: false };

		var btn = {
			close: root.querySelector( '[data-adaa="close"]' ),
			skip: root.querySelector( '[data-adaa="skip"]' ),
			contrast: root.querySelector( '[data-adaa="contrast"]' ),
			text: root.querySelector( '[data-adaa="text"]' ),
			reset: root.querySelector( '[data-adaa="reset"]' )
		};

		/* ---------- open / close ---------- */

		function open() {
			panel.classList.add( 'is-open' );
			launcher.setAttribute( 'aria-expanded', 'true' );
			var first = panel.querySelector( 'button' );
			if ( first ) {
				first.focus();
			}
		}

		function close( returnFocus ) {
			panel.classList.remove( 'is-open' );
			launcher.setAttribute( 'aria-expanded', 'false' );
			if ( returnFocus !== false ) {
				launcher.focus();
			}
		}

		launcher.addEventListener( 'click', function () {
			if ( panel.classList.contains( 'is-open' ) ) {
				close();
			} else {
				open();
			}
		} );

		if ( btn.close ) {
			btn.close.addEventListener( 'click', function () {
				close();
			} );
		}

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && panel.classList.contains( 'is-open' ) ) {
				close();
			}
		} );

		// Keep tab focus inside the panel while it is open.
		panel.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'Tab' ) {
				return;
			}
			var items = panel.querySelectorAll( 'button' );
			if ( ! items.length ) {
				return;
			}
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];

			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		} );

		/* ---------- text size ---------- */

		function scalableNodes() {
			var all = document.body.querySelectorAll( '*' );
			var out = [];

			for ( var i = 0; i < all.length; i++ ) {
				if ( ! all[ i ].matches( EXCLUDE ) ) {
					out.push( all[ i ] );
				}
			}

			return out;
		}

		/*
		 * Two passes. Measuring and scaling in a single loop meant a child
		 * measured after its parent had already grown recorded the inflated
		 * size as its baseline, so nested elements compounded. Every base is
		 * now captured before anything is written.
		 */
		function applyScale( factor ) {
			var nodes = scalableNodes();
			var i;

			for ( i = 0; i < nodes.length; i++ ) {
				if ( ! nodes[ i ].dataset.adaaBase ) {
					nodes[ i ].dataset.adaaBase = parseFloat(
						window.getComputedStyle( nodes[ i ] ).fontSize
					) || 0;
				}
			}

			for ( i = 0; i < nodes.length; i++ ) {
				var base = parseFloat( nodes[ i ].dataset.adaaBase );

				if ( ! base ) {
					continue;
				}

				nodes[ i ].style.fontSize = factor === 1
					? ''
					: ( base * factor ).toFixed( 2 ) + 'px';
			}

			// Never let the toolbar itself grow with the page.
			var own = root.querySelectorAll( '*' );
			for ( i = 0; i < own.length; i++ ) {
				own[ i ].style.fontSize = '';
				delete own[ i ].dataset.adaaBase;
			}
			root.style.fontSize = '';
		}

		if ( btn.text ) {
			btn.text.addEventListener( 'click', function () {
				state.step = ( state.step + 1 ) % STEPS.length;
				applyScale( STEPS[ state.step ] );
				btn.text.setAttribute( 'aria-pressed', state.step > 0 ? 'true' : 'false' );
			} );
		}

		/* ---------- high contrast ---------- */

		var HEADER_FOOTER = [
			'header',
			'.site-header',
			'#site-header',
			'#masthead',
			'#top-bar-wrap',
			'.elementor-location-header',
			'footer',
			'.site-footer',
			'#site-footer',
			'#footer',
			'#footer-bottom',
			'#colophon',
			'.elementor-location-footer'
		].join( ',' );

		var painted = [];

		function isTransparent( value ) {
			if ( ! value || value === 'transparent' ) {
				return true;
			}

			var open = value.indexOf( '(' );
			var close = value.indexOf( ')' );

			if ( open === -1 || close === -1 ) {
				return false;
			}

			var parts = value.slice( open + 1, close ).split( ',' );

			if ( parts.length < 4 ) {
				return false;
			}

			return parseFloat( parts[ 3 ] ) === 0;
		}

		function blacken( el ) {
			painted.push( {
				el: el,
				bg: el.style.getPropertyValue( 'background-color' ),
				bgPri: el.style.getPropertyPriority( 'background-color' ),
				img: el.style.getPropertyValue( 'background-image' ),
				imgPri: el.style.getPropertyPriority( 'background-image' )
			} );

			el.style.setProperty( 'background-color', '#000', 'important' );
			el.style.setProperty( 'background-image', 'none', 'important' );
		}

		function contrastOn() {
			var roots = document.querySelectorAll( HEADER_FOOTER );

			for ( var i = 0; i < roots.length; i++ ) {
				var rootEl = roots[ i ];

				if ( rootEl.contains( root ) ) {
					continue;
				}

				var rootWidth = rootEl.getBoundingClientRect().width;

				if ( ! isTransparent( window.getComputedStyle( rootEl ).backgroundColor ) ) {
					blacken( rootEl );
				}

				var kids = rootEl.querySelectorAll( '*' );

				for ( var k = 0; k < kids.length; k++ ) {
					var el = kids[ k ];

					if ( el === root || root.contains( el ) || el.tagName === 'IMG' ) {
						continue;
					}

					if ( isTransparent( window.getComputedStyle( el ).backgroundColor ) ) {
						continue;
					}

					/*
					 * Only full-width bands are recoloured. That catches the
					 * container actually painting the header or footer, while
					 * leaving buttons, badges and logo blocks exactly as they
					 * are — the Review Us button keeps its white background
					 * and its dark text.
					 */
					if ( el.getBoundingClientRect().width < rootWidth * 0.6 ) {
						continue;
					}

					blacken( el );
				}
			}

			document.body.classList.add( 'adaa-contrast' );
		}

		function contrastOff() {
			for ( var i = painted.length - 1; i >= 0; i-- ) {
				var p = painted[ i ];

				if ( p.bg ) {
					p.el.style.setProperty( 'background-color', p.bg, p.bgPri );
				} else {
					p.el.style.removeProperty( 'background-color' );
				}

				if ( p.img ) {
					p.el.style.setProperty( 'background-image', p.img, p.imgPri );
				} else {
					p.el.style.removeProperty( 'background-image' );
				}
			}

			painted = [];
			document.body.classList.remove( 'adaa-contrast' );
		}

		function setContrast( on ) {
			state.contrast = !! on;

			contrastOff();

			if ( state.contrast ) {
				contrastOn();
			}

			if ( btn.contrast ) {
				btn.contrast.setAttribute( 'aria-pressed', String( state.contrast ) );
			}
		}

		if ( btn.contrast ) {
			btn.contrast.addEventListener( 'click', function () {
				setContrast( ! state.contrast );
			} );
		}

		/* ---------- skip to content ---------- */

		if ( btn.skip ) {
			btn.skip.addEventListener( 'click', function () {
				close( false );

				// Always returns to the top of the page, from anywhere.
				window.scrollTo( { top: 0, behavior: 'smooth' } );

				var target =
					document.querySelector( 'header' ) ||
					document.getElementById( 'content' ) ||
					document.querySelector( 'main' );

				if ( target ) {
					target.setAttribute( 'tabindex', '-1' );
					target.focus( { preventScroll: true } );
				}
			} );
		}

		/* ---------- reset ---------- */

		if ( btn.reset ) {
			btn.reset.addEventListener( 'click', function () {
				state.step = 0;
				applyScale( 1 );
				setContrast( false );

				var pressed = panel.querySelectorAll( '[aria-pressed]' );
				for ( var i = 0; i < pressed.length; i++ ) {
					pressed[ i ].setAttribute( 'aria-pressed', 'false' );
				}

			} );
		}

		/* ---------- scroll offset ---------- */

		window.addEventListener(
			'scroll',
			function () {
				root.classList.toggle( 'is-scrolled', window.scrollY > 300 );
			},
			{ passive: true }
		);

		lift();

		/*
		 * Some themes and plugins set very high z-index values on sticky
		 * headers and chat widgets. Rather than guess a number, find the
		 * highest one actually in use and sit above it.
		 */
		function lift() {
			var declared = parseInt(
				window.getComputedStyle( root ).getPropertyValue( '--adaa-z' ),
				10
			) || 0;

			var highest = declared;
			var all = document.body.querySelectorAll( '*' );

			for ( var i = 0; i < all.length; i++ ) {
				var el = all[ i ];

				if ( el === root || root.contains( el ) ) {
					continue;
				}

				var cs = window.getComputedStyle( el );

				if ( cs.position === 'static' ) {
					continue;
				}

				var z = parseInt( cs.zIndex, 10 );

				if ( ! isNaN( z ) && z > highest ) {
					highest = z;
				}
			}

			if ( highest >= declared ) {
				root.style.setProperty( '--adaa-z', Math.min( highest + 1, 2147483646 ) );
			}

			// Last resort: if anything still renders above us, being the final
			// element in the body wins ties in the stacking order.
			if ( root.parentNode !== document.body || root.nextElementSibling ) {
				document.body.appendChild( root );
			}
		}
	}

	function boot() {
		var roots = document.querySelectorAll( '.adaa' );
		for ( var i = 0; i < roots.length; i++ ) {
			init( roots[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Re-init after an Elementor editor re-render.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/ada_accessibility.default',
					boot
				);
			}
		} );
	}
} )();
