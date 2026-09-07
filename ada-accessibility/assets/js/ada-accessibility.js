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

	/*
	 * The height of the header bar, in pixels.
	 *
	 * A fixed number on purpose. Measuring the header is what went wrong
	 * before: the measurement fed back into the theme, which republished a
	 * bigger --wpex-sticky-header-height, and the next pass measured that and
	 * grew again. This is the site's real header height and it is the same on
	 * every screen, so the bar is simply told to be it - nothing is measured,
	 * and re-applying it can never change anything. One line to change if the
	 * header is ever redesigned.
	 */
	var BAR_HEIGHT = 78;
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
			var roots = contrastRoots();
			var out = { lists: [], bars: [] };

			for ( var i = 0; i < roots.length; i++ ) {
				var lists = roots[ i ].querySelectorAll( 'ul, ol' );

				for ( var j = 0; j < lists.length; j++ ) {
					var list = lists[ j ];

					if ( list.children.length < 3 || root.contains( list ) ) {
						continue;
					}

					// Side by side, not stacked.
					if ( Math.round( list.children[ 0 ].offsetTop ) !== Math.round( list.children[ 1 ].offsetTop ) ) {
						continue;
					}

					out.lists.push( list );
					out.bars.push( roots[ i ] );
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
			var found = horizontalMenus();
			var lists = found.lists;
			var bars = found.bars;

			for ( var i = 0; i < lists.length; i++ ) {
				var list = lists[ i ];

				if ( rowCount( list ) < 2 ) {
					continue;
				}

				/*
				 * Everything inside the list, at any depth, plus the wrappers
				 * around it up to the bar.
				 *
				 * Themes do not agree on where menu spacing lives. Total puts
				 * it on a span inside the link - li > a > span.link-inner - so
				 * reaching only the item and its first child left the padding
				 * untouched and the menu still wrapped. Whatever holds it is
				 * now included, and the header row's own padding gives way too
				 * when the menu alone cannot free enough room.
				 */
				var targets = [ list ];
				var inside = list.querySelectorAll( '*' );
				var j;

				for ( j = 0; j < inside.length; j++ ) {
					targets.push( inside[ j ] );
				}

				for ( var up = list.parentElement; up && up !== bars[ i ]; up = up.parentElement ) {
					targets.push( up );
				}

				targets.push( bars[ i ] );

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

		/*
		 * A <header> or <footer> inside the page content is an article's own,
		 * not the site's, and must be left alone.
		 *
		 * Only markers that WordPress puts on the content itself are listed.
		 * "page", "home", "post" and friends are body_class() output, so a
		 * selector list containing them matched the <body> for every bar on
		 * the site and quietly filtered out all of them - High Contrast then
		 * did nothing whatsoever on any Page. insideContent() ignores a match
		 * on <body> or <html> for that reason.
		 */
		var CONTENT = 'main, article, .entry-content, .hentry';

		function insideContent( el ) {
			var owner = el.closest( CONTENT );

			return !! owner && owner !== document.body && owner !== document.documentElement;
		}

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
						if ( insideContent( el ) ) {
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
				if ( ! insideContent( found[ i ] ) ) {
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
		var sized = [];
		var sizedSet = ( typeof window.Set === 'function' ) ? new window.Set() : null;
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
		 * The wrappers a theme puts around its header bar.
		 *
		 * Total wraps the header in #site-header-sticky-wrapper and paints the
		 * bar's colour and height there, not on the header. Walking only
		 * downwards left that wrapper its own colour, showing as a band the
		 * black never covered. Climbing is bounded by height: a wrapper is
		 * part of the bar, a page wrapper is many times taller and is where
		 * the climb stops, so the whole page can never be blackened.
		 */
		function barWrappers( el ) {
			var out = [];
			var limit = el.getBoundingClientRect().height * 1.8 + 24;
			var parent = el.parentElement;

			while ( parent && parent !== document.body && parent !== document.documentElement ) {
				if ( root.contains( parent ) || parent.contains( root ) ) {
					break;
				}

				if ( parent.getBoundingClientRect().height > limit ) {
					break;
				}

				if ( isPainted( parent ) ) {
					out.push( parent );
				}

				parent = parent.parentElement;
			}

			return out;
		}

		/*
		 * The bar that sits across the top of the screen, as opposed to the
		 * footer. Its own top edge is at the top of the page, or pinned there,
		 * which is true whether the page is scrolled or not.
		 */
		// A bar held across the top of the viewport right now, rather than
		// sitting in the flow of the page.
		function pinned( el ) {
			var position = window.getComputedStyle( el ).position;

			if ( 'fixed' !== position && 'sticky' !== position ) {
				return false;
			}

			var admin = document.getElementById( 'wpadminbar' );
			var ceiling = 2;

			if ( admin ) {
				var adminBox = admin.getBoundingClientRect();

				if ( adminBox.bottom > ceiling ) {
					ceiling = adminBox.bottom + 2;
				}
			}

			return el.getBoundingClientRect().top <= ceiling;
		}

		function topBar( el ) {
			if ( pinned( el ) ) {
				return true;
			}

			/*
			 * Otherwise, a bar that starts at the top of the document. Measured
			 * against the document rather than the viewport so it still reads
			 * as the header once the page is scrolled, and with room above it
			 * for an admin bar and a utility row.
			 */
			return ( el.getBoundingClientRect().top + ( window.pageYOffset || 0 ) ) <= 300;
		}

		/*
		 * Give the black bar its height: BAR_HEIGHT, on every screen, always.
		 *
		 * The colour and the height can live on different elements, so painting
		 * alone can leave the black shorter than the bar it is meant to cover.
		 * Nothing here reads a height off the page - the number is fixed, so
		 * running this again on the next repaint writes the same value and the
		 * bar can never creep.
		 */
		function setBarHeight( el ) {
			if ( sizedSet ? sizedSet.has( el ) : sized.some( function ( r ) { return r.el === el; } ) ) {
				return;
			}

			if ( sizedSet ) {
				sizedSet.add( el );
			}

			sized.push( {
				el: el,
				h: el.style.getPropertyValue( 'height' ),
				hPri: el.style.getPropertyPriority( 'height' ),
				min: el.style.getPropertyValue( 'min-height' ),
				minPri: el.style.getPropertyPriority( 'min-height' )
			} );

			// height pins it, whichever side the theme's own figure falls on;
			// min-height holds the floor if a later rule frees the height again.
			el.style.setProperty( 'height', BAR_HEIGHT + 'px', 'important' );
			el.style.setProperty( 'min-height', BAR_HEIGHT + 'px', 'important' );
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

				/*
				 * Its wrappers, which is where the bar's own colour and height
				 * live. Painting them is enough; nothing here sets a height.
				 * Writing one made the header taller, the theme measured it and
				 * republished a larger --wpex-sticky-header-height, the next
				 * repaint read that back and grew it again - 61px became
				 * 110.61px. The bar already has the height its own CSS gives
				 * it, so the plugin only ever changes colour.
				 */
				var wrappers = barWrappers( rootEl );

				for ( var w = 0; w < wrappers.length; w++ ) {
					blacken( wrappers[ w ] );
				}

				/*
				 * The header bar is a fixed BAR_HEIGHT tall, on every screen.
				 *
				 * Which element the black is actually seen on depends on where
				 * the page is. Sitting at the top, it is the outermost wrapper
				 * - Total paints the bar on #site-header-sticky-wrapper and the
				 * header inside it is shorter. Scrolled down, the header
				 * detaches to position:fixed and becomes the bar you see, while
				 * that wrapper stays behind in the flow as a spacer. So the
				 * wrapper is sized always, and the bar itself as well once it
				 * is pinned. The footer is left alone: it is as tall as its own
				 * content.
				 */
				if ( topBar( rootEl ) ) {
					setBarHeight( wrappers.length ? wrappers[ wrappers.length - 1 ] : rootEl );

					if ( pinned( rootEl ) ) {
						setBarHeight( rootEl );
					}
				}

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

			for ( var h = sized.length - 1; h >= 0; h-- ) {
				var q = sized[ h ];

				if ( q.h ) {
					q.el.style.setProperty( 'height', q.h, q.hPri );
				} else {
					q.el.style.removeProperty( 'height' );
				}

				if ( q.min ) {
					q.el.style.setProperty( 'min-height', q.min, q.minPri );
				} else {
					q.el.style.removeProperty( 'min-height' );
				}
			}

			painted = [];
			sized = [];

			if ( paintedSet ) {
				paintedSet.clear();
			}

			if ( sizedSet ) {
				sizedSet.clear();
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
			return find( ( root.dataset.skipTarget || '' ).trim() );
		}

		function find( selector ) {
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
				 * One smooth move to the top of the page, and nothing after it.
				 *
				 * Measuring a sticky bar and correcting for it is what made
				 * this stutter: the bar's height changes as the page moves, so
				 * every correction invited another. The top of the page is a
				 * fixed number that nothing on the page can move, so the scroll
				 * is issued once and simply arrives. A selector set on the
				 * settings screen still wins, for a site that wants somewhere
				 * else.
				 */
				var target = configuredTarget();
				var top = 0;

				if ( target ) {
					var configured = parseInt( root.dataset.skipOffset, 10 );
					var offset = isNaN( configured ) ? 0 : Math.max( 0, configured );

					top = Math.max(
						0,
						Math.round( window.pageYOffset + target.getBoundingClientRect().top - offset )
					);
				}

				window.scrollTo( { top: top, behavior: 'smooth' } );

				// Focus the content for screen readers without moving the page
				// again; the panel is left open so it can be used once more.
				var landmark = target ||
					document.querySelector( 'main' ) ||
					document.querySelector( '[role="main"]' ) ||
					document.getElementById( 'content' );

				if ( landmark ) {
					landmark.setAttribute( 'tabindex', '-1' );
					landmark.focus( { preventScroll: true } );
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

	/*
	 * Hold the header at BAR_HEIGHT, on every page and for every visitor.
	 *
	 * This is not part of high contrast - it runs whether the toolbar has been
	 * touched or not, because the height is meant to be the same at all times.
	 *
	 * Total measures its own header and publishes the figure as
	 * --wpex-sticky-header-height on <body> - that is where the 61px, and
	 * before it the 110.61px, came from. That declaration is stripped off the
	 * body tag here and put back off every time the theme writes it again, and
	 * the header is given a flat BAR_HEIGHT instead. Nothing is measured and
	 * no figure is published, so there is no number left to drift.
	 */
	var pinning = false;

	function stickyWrapper() {
		return document.querySelector( '#site-header-sticky-wrapper, .wpex-sticky-header-holder' );
	}

	function hold( el ) {
		if ( ! el ) {
			return;
		}

		if ( el.style.getPropertyValue( 'height' ) !== BAR_HEIGHT + 'px' ) {
			el.style.setProperty( 'height', BAR_HEIGHT + 'px', 'important' );
		}

		if ( el.style.getPropertyValue( 'min-height' ) !== BAR_HEIGHT + 'px' ) {
			el.style.setProperty( 'min-height', BAR_HEIGHT + 'px', 'important' );
		}
	}

	function pinHeader() {
		if ( pinning ) {
			return;
		}

		pinning = true;

		var body = document.body;

		if ( body ) {
			if ( body.style.getPropertyValue( '--wpex-sticky-header-height' ) ) {
				body.style.removeProperty( '--wpex-sticky-header-height' );
			}

			// Leave the tag clean rather than carrying an empty attribute.
			if ( body.hasAttribute( 'style' ) && '' === body.getAttribute( 'style' ).trim() ) {
				body.removeAttribute( 'style' );
			}
		}

		// The placeholder that holds the bar's space in the flow, and the
		// header itself, which is the bar you see once it goes sticky.
		hold( stickyWrapper() );

		var header = document.getElementById( 'site-header' );

		if ( header ) {
			var position = window.getComputedStyle( header ).position;

			if ( 'fixed' === position || 'sticky' === position ) {
				hold( header );
			}
		}

		pinning = false;
	}

	function holdHeader() {
		pinHeader();

		window.addEventListener( 'scroll', pinHeader, { passive: true } );
		window.addEventListener( 'resize', pinHeader, { passive: true } );
		window.addEventListener( 'load', pinHeader );

		// Total republishes the variable on its own schedule; put it back.
		if ( typeof window.MutationObserver === 'function' ) {
			new window.MutationObserver( function () {
				if ( ! pinning ) {
					pinHeader();
				}
			} ).observe( document.documentElement, {
				subtree: true,
				attributes: true,
				attributeFilter: [ 'style', 'class' ]
			} );
		}
	}

	function boot() {
		holdHeader();

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
