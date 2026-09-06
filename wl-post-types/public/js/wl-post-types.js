/**
 * WL Post Types - front end.
 *
 * Cases cards are styled entirely in the site's own CSS, and the one thing CSS
 * cannot express there is "the pointer is over this card" as a class. This adds
 * wl-card-img-hover to the image wrapper while the card is hovered or holds
 * keyboard focus, and takes it off again. Nothing else.
 *
 * No dependencies.
 */
( function () {
	'use strict';

	var HOVER = 'wl-card-img-hover';

	/*
	 * The Cases card an event belongs to. Anchored on the card rather than on
	 * the image wrapper: focus lands on the card's own link, which is the
	 * wrapper's parent, so looking only for an ancestor wrapper would miss
	 * every keyboard user.
	 */
	function card( node ) {
		if ( ! node || ! node.closest ) {
			return null;
		}

		var el = node.closest( '.wl-card' );

		if ( ! el || ! el.closest( '.wl-cards.wl-ov-plain' ) ) {
			return null;
		}

		return el;
	}

	function enter( event ) {
		var el = card( event.target );
		var img = el && el.querySelector( '.wl-card-img' );

		if ( img ) {
			img.classList.add( HOVER );
		}
	}

	function leave( event ) {
		var el = card( event.target );

		if ( ! el ) {
			return;
		}

		// Moving between two parts of the same card is not leaving it.
		if ( event.relatedTarget && el.contains( event.relatedTarget ) ) {
			return;
		}

		var img = el.querySelector( '.wl-card-img' );

		if ( img ) {
			img.classList.remove( HOVER );
		}
	}

	/* Delegated, so cards added later are covered without rebinding. */
	document.addEventListener( 'mouseover', enter );
	document.addEventListener( 'mouseout', leave );
	document.addEventListener( 'focusin', enter );
	document.addEventListener( 'focusout', leave );
} )();
