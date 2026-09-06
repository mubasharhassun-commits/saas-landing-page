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
	var EXCLUDE = '.adaa, .adaa *, #wpadminbar, #wpadminbar *, script, style, svg, path, circle, br, hr';

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

			/*
			 * Menu spacing is always returned to normal first, so each size is
			 * measured against the theme's own layout rather than the last
			 * tightened one.
			 */
			restoreMenus();

			document.documentElement.classList.toggle( 'adaa-text-scaled', factor !== 1 );
			document.documentElement.style.setProperty( '--adaa-text-scale', factor );

			if ( factor !== 1 ) {
				tightenMenus();
			}
		}

		/* ---------- menus at a larger text size ----------
		 *
		 * Bigger type makes a horizontal menu wider than its bar and it wraps
		 * onto a second row, which pushes the header down. The words cannot
		 * shrink - that is the whole point of the button - so the space around
		 * them gives way instead: the padding either side of each item is
		 * reduced, a step at a time, only until the menu fits on one row again.
		 */

		var TIGHTEN_STEPS = [ 0.85, 0.7, 0.55, 0.4, 0.28, 0.18 ];
		var tightened = [];

		function rowCount( list ) {
			var tops = [];

			for ( var i = 0; i < list.children.length; i++ ) {
				var top = Math.round( list.children[ i ].offsetTop );

				if ( -1 === tops.indexOf( top ) ) {
					tops.push( top );
				}
			}

			return tops.length;
		}

		/* Lists inside the site's own bars that are laid out as a row. */
		function horizontalMenus() {
			var bars = contrastRoots();
			var out = [];

			for ( var i = 0; i < bars.length; i++ ) {
				var lists = bars[ i ].querySelectorAll( 'ul, ol' );

				for ( var j = 0; j < lists.length; j++ ) {
					var list = lists[ j ];

					if ( list.children.length < 3 || root.contains( list ) ) {
						continue;
					}

					// Side by side, not stacked.
					if ( Math.round( list.children[ 0 ].offsetTop ) !== Math.round( list.children[ 1 ].offsetTop ) ) {
						continue;
					}

					out.push( list );
				}
			}

			return out;
		}

		function captureSpacing( el ) {
			if ( el.dataset.adaaSpace ) {
				return;
			}

			var cs = window.getComputedStyle( el );

			el.dataset.adaaSpace = [
				parseFloat( cs.paddingLeft ) || 0,
				parseFloat( cs.paddingRight ) || 0,
				parseFloat( cs.marginLeft ) || 0,
				parseFloat( cs.marginRight ) || 0,
				parseFloat( cs.columnGap ) || 0
			].join( ',' );

			tightened.push( {
				el: el,
				props: [ 'padding-left', 'padding-right', 'margin-left', 'margin-right', 'column-gap' ].map( function ( prop ) {
					return {
						prop: prop,
						value: el.style.getPropertyValue( prop ),
						priority: el.style.getPropertyPriority( prop )
					};
				} )
			} );
		}

		function tightenTo( el, factor ) {
			var base = ( el.dataset.adaaSpace || '' ).split( ',' );

			if ( base.length < 5 ) {
				return;
			}

			var props = [ 'padding-left', 'padding-right', 'margin-left', 'margin-right', 'column-gap' ];

			for ( var i = 0; i < props.length; i++ ) {
				var value = parseFloat( base[ i ] ) || 0;

				// A gap of zero is not a gap; leave it alone.
				if ( 4 === i && value <= 0 ) {
					continue;
				}

				el.style.setProperty( props[ i ], ( value * factor ).toFixed( 2 ) + 'px', 'important' );
			}
		}

		function tightenMenus() {
			var lists = horizontalMenus();

			for ( var i = 0; i < lists.length; i++ ) {
				var list = lists[ i ];

				if ( rowCount( list ) < 2 ) {
					continue;
				}

				// The list itself, its items, and whatever each item wraps.
				var targets = [ list ];

				for ( var j = 0; j < list.children.length; j++ ) {
					var item = list.children[ j ];
					targets.push( item );

					var inner = item.firstElementChild;

					if ( inner && ( 'A' === inner.tagName || 'SPAN' === inner.tagName || 'BUTTON' === inner.tagName ) ) {
						targets.push( inner );
					}
				}

				for ( var t = 0; t < targets.length; t++ ) {
					captureSpacing( targets[ t ] );
				}

				for ( var s = 0; s < TIGHTEN_STEPS.length && rowCount( list ) > 1; s++ ) {
					for ( var k = 0; k < targets.length; k++ ) {
						tightenTo( targets[ k ], TIGHTEN_STEPS[ s ] );
					}
				}
			}
		}

		function restoreMenus() {
			for ( var i = tightened.length - 1; i >= 0; i-- ) {
				var entry = tightened[ i ];

				for ( var j = 0; j < entry.props.length; j++ ) {
					var saved = entry.props[ j ];

					if ( saved.value ) {
						entry.el.style.setProperty( saved.prop, saved.value, saved.priority );
					} else {
						entry.el.style.removeProperty( saved.prop );
					}
				}

				delete entry.el.dataset.adaaSpace;
			}

			tightened = [];
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

		/*
		 * Controls keep their own colours: a Review Us button or a coloured
		 * call to action stays legible on its own terms. Matching on the
		 * control itself replaces an earlier rule that only recoloured bands
		 * at least 60% as wide as the header, which quietly skipped the very
		 * container painting the header on a wide screen.
		 */
		var CONTROLS = 'a, button, input, select, textarea, label, [role="button"], [role="link"]';

		/* A <header> or <footer> inside the page content is an article's own,
		   not the site's, and must be left alone. */
		var CONTENT = 'main, article, .entry-content, .entry, .post, .page, .hentry, #content';

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

		/* Painted by a colour or by an image - a gradient bar counts. */
		function isPainted( el ) {
			var cs = window.getComputedStyle( el );

			return ! isTransparent( cs.backgroundColor ) || cs.backgroundImage !== 'none';
		}

		/*
		 * Bars pinned over the page, found by what they do rather than what
		 * they are called. A theme's sticky header is often a second element
		 * with a name of its own - wpex-sticky-header-holder, a cloned wrapper,
		 * an "is-stuck" div - that no selector list can predict, and missing it
		 * leaves a strip of the original colour across the top. Reading the
		 * elements actually painting the top of the viewport catches it
		 * whatever it is called.
		 */
		function pinnedBars() {
			var out = [];
			var width = window.innerWidth;

			if ( ! document.elementsFromPoint || ! width ) {
				return out;
			}

			var xs = [ width * 0.25, width * 0.5, width * 0.75 ];

			for ( var y = 2; y <= 160; y += 8 ) {
				for ( var i = 0; i < xs.length; i++ ) {
					var stack = document.elementsFromPoint( xs[ i ], y ) || [];

					for ( var j = 0; j < stack.length; j++ ) {
						var el = stack[ j ];

						if ( el === root || root.contains( el ) ) {
							continue;
						}
						if ( el === document.body || el === document.documentElement ) {
							continue;
						}
						// The admin bar belongs to WordPress, not the site.
						if ( el.id === 'wpadminbar' || el.closest( '#wpadminbar' ) ) {
							continue;
						}
						if ( el.closest( CONTENT ) ) {
							continue;
						}

						var cs = window.getComputedStyle( el );

						if ( cs.position !== 'fixed' && cs.position !== 'sticky' ) {
							continue;
						}
						if ( el.getBoundingClientRect().width < width * 0.8 ) {
							continue;
						}
						if ( -1 === out.indexOf( el ) ) {
							out.push( el );
						}
					}
				}
			}

			return out;
		}

		/* The site's own header and footer bars, never an article's. */
		function contrastRoots() {
			var found = document.querySelectorAll( HEADER_FOOTER );
			var out = [];
			var i;

			for ( i = 0; i < found.length; i++ ) {
				if ( ! found[ i ].closest( CONTENT ) ) {
					out.push( found[ i ] );
				}
			}

			var pinned = pinnedBars();

			for ( i = 0; i < pinned.length; i++ ) {
				if ( -1 === out.indexOf( pinned[ i ] ) ) {
					out.push( pinned[ i ] );
				}
			}

			// Drop any that sit inside another one already in the list.
			return out.filter( function ( el ) {
				return ! out.some( function ( other ) {
					return other !== el && other.contains( el );
				} );
			} );
		}

		var paintedSet = ( typeof window.Set === 'function' ) ? new window.Set() : null;
		var watching = false;
		var painting = false;
		var observer = null;
		var queued = false;

		function alreadyPainted( el ) {
			if ( paintedSet ) {
				return paintedSet.has( el );
			}

			for ( var i = 0; i < painted.length; i++ ) {
				if ( painted[ i ].el === el ) {
					return true;
				}
			}

			return false;
		}

		function blacken( el ) {
			if ( alreadyPainted( el ) ) {
				return;
			}

			if ( paintedSet ) {
				paintedSet.add( el );
			}

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

		/*
		 * Sticky headers are the reason this has to keep running. A theme
		 * reveals a second bar once the page is scrolled, or swaps one in on a
		 * class change, and a bar that did not exist when the button was
		 * pressed would otherwise keep its own colour - which is the strip of
		 * original background left showing across the top.
		 */
		function watch() {
			if ( watching ) {
				return;
			}

			watching = true;

			window.addEventListener( 'scroll', repaint, { passive: true } );
			window.addEventListener( 'resize', repaint, { passive: true } );

			if ( typeof window.MutationObserver === 'function' ) {
				observer = new window.MutationObserver( function () {
					// Our own writes must not feed back into the observer.
					if ( ! painting ) {
						repaint();
					}
				} );

				observer.observe( document.body, {
					childList: true,
					subtree: true,
					attributes: true,
					attributeFilter: [ 'class', 'style' ]
				} );
			}
		}

		function unwatch() {
			if ( ! watching ) {
				return;
			}

			watching = false;

			window.removeEventListener( 'scroll', repaint );
			window.removeEventListener( 'resize', repaint );

			if ( observer ) {
				observer.disconnect();
				observer = null;
			}
		}

		/* Coalesced to one pass per frame. */
		function repaint() {
			if ( ! state.contrast || queued ) {
				return;
			}

			queued = true;

			window.requestAnimationFrame( function () {
				queued = false;

				if ( state.contrast ) {
					contrastOn();
				}
			} );
		}

		function contrastOn() {
			var roots = contrastRoots();

			painting = true;

			for ( var i = 0; i < roots.length; i++ ) {
				var rootEl = roots[ i ];

				if ( rootEl.contains( root ) ) {
					continue;
				}

				// The bar itself, painted or not: a header that is transparent
				// over a hero image still has to become a solid black band.
				blacken( rootEl );

				var kids = rootEl.querySelectorAll( '*' );

				for ( var k = 0; k < kids.length; k++ ) {
					var el = kids[ k ];

					if ( el === root || root.contains( el ) ) {
						continue;
					}

					if ( el.tagName === 'IMG' || el.tagName === 'SVG' || el.tagName === 'PICTURE' ) {
						continue;
					}

					// Buttons and links keep their own look, however wide.
					if ( el.closest( CONTROLS ) ) {
						continue;
					}

					if ( ! isPainted( el ) ) {
						continue;
					}

					blacken( el );
				}
			}

			document.body.classList.add( 'adaa-contrast' );

			painting = false;

			watch();
		}

		function contrastOff() {
			unwatch();

			painting = true;

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

			if ( paintedSet ) {
				paintedSet.clear();
			}

			document.body.classList.remove( 'adaa-contrast' );

			painting = false;
		}

		function setContrast( on ) {
			state.contrast = !! on;

			if ( state.contrast ) {
				contrastOn();
			} else {
				contrastOff();
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

		/*
		 * The selector typed on the settings screen, when it matches something.
		 * querySelector throws on a malformed selector, so a typo there must not
		 * take the button down with it.
		 */
		function configuredTarget() {
			var selector = ( root.dataset.skipTarget || '' ).trim();

			if ( ! selector ) {
				return null;
			}

			try {
				return document.querySelector( selector );
			} catch ( e ) {
				return null;
			}
		}

		if ( btn.skip ) {
			btn.skip.addEventListener( 'click', function () {
				/*
				 * Skip to content means the content, so this jumps past the
				 * header rather than to the top of the page, and the panel is
				 * left open: a visitor reaching for it again should not have
				 * to reopen it.
				 */
				var target =
					configuredTarget() ||
					document.querySelector( 'main' ) ||
					document.querySelector( '[role="main"]' ) ||
					document.getElementById( 'content' ) ||
					document.querySelector( '.entry-content' ) ||
					document.querySelector( 'article' );

				if ( ! target ) {
					return;
				}

				target.setAttribute( 'tabindex', '-1' );

				/*
				 * A sticky or fixed bar would otherwise cover the first lines
				 * of the very content this button exists to reach. A figure set
				 * on the settings screen wins; otherwise the bar is measured.
				 */
				var configured = parseInt( root.dataset.skipOffset, 10 );
				var fixedOffset = ( ! isNaN( configured ) && configured > 0 );
				var offset = fixedOffset ? configured : stickyOffset();

				/*
				 * One computed move rather than scrollIntoView with a
				 * scroll-margin: writing that margin and letting the browser
				 * re-resolve the target mid-animation is what made the jump
				 * stutter on a page whose sticky header changes height.
				 */
				scrollToTarget( target, offset, fixedOffset );

				target.focus( { preventScroll: true } );
			} );
		}

		/*
		 * The tallest bar pinned over the top of the viewport. Both sources are
		 * consulted: the named header and footer elements, and the bars found
		 * by reading what is actually painting the top - a theme's sticky
		 * header often answers to neither name.
		 */
		function stickyOffset() {
			var bars = Array.prototype.slice.call( document.querySelectorAll( HEADER_FOOTER ) );
			var offset = 0;
			var pinned = pinnedBars();
			var i;

			for ( i = 0; i < pinned.length; i++ ) {
				if ( -1 === bars.indexOf( pinned[ i ] ) ) {
					bars.push( pinned[ i ] );
				}
			}

			for ( i = 0; i < bars.length; i++ ) {
				var el = bars[ i ];

				if ( el === root || el.contains( root ) ) {
					continue;
				}

				var cs = window.getComputedStyle( el );

				if ( cs.position !== 'fixed' && cs.position !== 'sticky' ) {
					continue;
				}

				var box = el.getBoundingClientRect();

				if ( box.top <= 1 && box.bottom > offset ) {
					offset = box.bottom;
				}
			}

			return Math.round( offset );
		}

		/*
		 * Scroll to the target, and follow it if a sticky bar appears on the
		 * way down. A bar revealed at a scroll threshold has no height at the
		 * moment the button is pressed, so the first sum lands the content
		 * underneath it. Rather than correcting afterwards - which would be
		 * the jerk of scrolling back up - the destination is moved up the
		 * instant the bar appears, while the page is still travelling towards
		 * it, so the browser simply stops earlier.
		 */
		function scrollToTarget( target, offset, fixedOffset ) {
			var destination = function ( off ) {
				return Math.max(
					0,
					Math.round( window.pageYOffset + target.getBoundingClientRect().top - off )
				);
			};

			var lastOffset = offset;
			var started = Date.now();

			window.scrollTo( { top: destination( offset ), behavior: 'smooth' } );

			// A figure typed on the settings screen is a deliberate choice and
			// is never second-guessed; only a measured one is followed.
			if ( fixedOffset ) {
				return;
			}

			( function follow() {
				// Give up once the page has had time to settle.
				if ( Date.now() - started > 1500 ) {
					return;
				}

				var now = stickyOffset();

				if ( now !== lastOffset ) {
					lastOffset = now;
					window.scrollTo( { top: destination( now ), behavior: 'smooth' } );
				}

				window.requestAnimationFrame( follow );
			} )();
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
