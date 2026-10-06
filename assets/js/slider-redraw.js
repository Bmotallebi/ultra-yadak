/**
 * Force Slider Revolution to recalculate its fullwidth position once the
 * layout has settled — same effect as the manual window resize that fixed it.
 */
( function () {
	function redraw() {
		if ( ! document.querySelector( 'rs-module' ) ) {
			return;
		}

		if ( window.jQuery && window.jQuery.fn.revredraw ) {
			window.jQuery( 'rs-module' ).each( function () {
				try {
					window.jQuery( this ).revredraw();
				} catch ( e ) {}
			} );
		}

		window.dispatchEvent( new Event( 'resize' ) );
	}

	function schedule() {
		redraw();
		setTimeout( redraw, 300 );
		setTimeout( redraw, 1000 );
	}

	if ( document.readyState === 'complete' ) {
		schedule();
	} else {
		window.addEventListener( 'load', schedule );
	}

	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( redraw );
	}
} )();
