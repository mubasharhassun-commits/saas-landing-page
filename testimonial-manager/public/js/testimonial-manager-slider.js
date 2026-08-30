/**
 * Testimonial Manager - testimonial slider.
 *
 * Each slider on the page runs independently. Slides are stacked in one grid
 * cell, so changing slide is purely a matter of which one is visible - the
 * track never resizes and nothing below it jumps.
 *
 * No jQuery, no dependencies.
 */
( function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* Which input device last moved focus. Checking :focus-visible inside a
	   focusin handler is unreliable - the browser has not necessarily resolved
	   it yet - so the modality is tracked directly, once for the whole page. */
	var pointerInput = false;

	document.addEventListener( 'pointerdown', function () {
		pointerInput = true;
	}, true );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Tab' === event.key || 0 === String( event.key ).indexOf( 'Arrow' ) ) {
			pointerInput = false;
		}
	}, true );

	function Slider( root ) {
		this.root    = root;
		this.slides  = Array.prototype.slice.call( root.querySelectorAll( '.tm-slide' ) );
		this.dots    = Array.prototype.slice.call( root.querySelectorAll( '.tm-slider-dot' ) );
		this.index   = 0;
		this.timer   = null;
		this.delay   = parseInt( root.getAttribute( 'data-autoplay' ), 10 ) || 0;
		this.pauseOnHover = '1' === root.getAttribute( 'data-pause-hover' );

		/* Held while the pointer is over the slider or focus is inside it. Kept
		   as state rather than just stopping the timer, so that restarting after
		   a click cannot resume autoplay under the user's cursor. */
		this.hovering = false;
		this.focused  = false;

		if ( this.slides.length < 2 ) {
			return;
		}

		this.bind();
		this.start();
	}

	Slider.prototype.bind = function () {
		var self = this;

		this.root.addEventListener( 'click', function ( event ) {
			var target = event.target.closest( '[data-tm-prev], [data-tm-next], [data-tm-goto]' );

			if ( ! target ) {
				return;
			}

			event.preventDefault();

			if ( target.hasAttribute( 'data-tm-prev' ) ) {
				self.go( self.index - 1 );
			} else if ( target.hasAttribute( 'data-tm-next' ) ) {
				self.go( self.index + 1 );
			} else {
				self.go( parseInt( target.getAttribute( 'data-tm-goto' ), 10 ) );
			}

			// A deliberate move should not be yanked away a moment later.
			self.restart();
		} );

		this.root.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				self.go( self.index - 1 );
				self.restart();
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				self.go( self.index + 1 );
				self.restart();
			}
		} );

		if ( this.pauseOnHover ) {
			this.root.addEventListener( 'mouseenter', function () {
				self.hovering = true;
				self.stop();
			} );
			this.root.addEventListener( 'mouseleave', function () {
				self.hovering = false;
				self.start();
			} );
		}

		// Keyboard users get the same courtesy as the mouse.
		this.root.addEventListener( 'focusin', function () {
			/* Only a keyboard visit holds the slider. Clicking an arrow or a dot
			   also focuses it, and that should not switch autoplay off for good
			   once the pointer has moved away. */
			if ( ! pointerInput ) {
				self.focused = true;
				self.stop();
			}
		} );
		this.root.addEventListener( 'focusout', function () {
			if ( ! self.root.contains( document.activeElement ) ) {
				self.focused = false;
				self.start();
			}
		} );

		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				self.stop();
			} else {
				self.start();
			}
		} );

		this.swipe();
	};

	Slider.prototype.swipe = function () {
		var self = this;
		var x0 = null;
		var y0 = null;

		this.root.addEventListener( 'touchstart', function ( event ) {
			x0 = event.touches[ 0 ].clientX;
			y0 = event.touches[ 0 ].clientY;
		}, { passive: true } );

		this.root.addEventListener( 'touchend', function ( event ) {
			if ( null === x0 ) {
				return;
			}

			var dx = event.changedTouches[ 0 ].clientX - x0;
			var dy = event.changedTouches[ 0 ].clientY - y0;

			// Horizontal intent only, so a vertical page scroll is left alone.
			if ( Math.abs( dx ) > 45 && Math.abs( dx ) > Math.abs( dy ) ) {
				self.go( self.index + ( dx < 0 ? 1 : -1 ) );
				self.restart();
			}

			x0 = null;
			y0 = null;
		}, { passive: true } );
	};

	Slider.prototype.go = function ( next ) {
		var count = this.slides.length;

		next = ( ( next % count ) + count ) % count; // wraps in both directions

		this.slides.forEach( function ( slide, i ) {
			var on = i === next;

			slide.classList.toggle( 'is-active', on );

			if ( on ) {
				slide.removeAttribute( 'aria-hidden' );
			} else {
				slide.setAttribute( 'aria-hidden', 'true' );
			}
		} );

		this.dots.forEach( function ( dot, i ) {
			var on = i === next;

			dot.classList.toggle( 'is-active', on );

			if ( on ) {
				dot.setAttribute( 'aria-current', 'true' );
			} else {
				dot.removeAttribute( 'aria-current' );
			}
		} );

		this.index = next;
	};

	Slider.prototype.start = function () {
		// Auto-advancing motion is exactly what reduced-motion asks us not to do.
		if ( this.timer || ! this.delay || REDUCED ) {
			return;
		}

		// Never resume while the reader is hovering, focused inside, or away.
		if ( this.hovering || this.focused || document.hidden ) {
			return;
		}

		var self = this;
		this.timer = window.setInterval( function () { self.go( self.index + 1 ); }, this.delay );
	};

	Slider.prototype.stop = function () {
		if ( this.timer ) {
			window.clearInterval( this.timer );
			this.timer = null;
		}
	};

	Slider.prototype.restart = function () {
		this.stop();
		this.start();
	};

	function init( scope ) {
		var root = scope || document;

		Array.prototype.forEach.call( root.querySelectorAll( '.tm-slider' ), function ( el ) {
			if ( ! el.tmSlider ) {
				el.tmSlider = new Slider( el );
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { init(); } );
	} else {
		init();
	}

	// Elementor re-renders widgets in the editor without reloading the page.
	window.addEventListener( 'elementor/frontend/init', function () { init(); } );
}() );
