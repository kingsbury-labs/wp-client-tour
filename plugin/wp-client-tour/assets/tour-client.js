/* global wpClientTour */
( function () {
	'use strict';

	const config = window.wpClientTour;
	if ( ! config || ! Array.isArray( config.tours ) || config.tours.length === 0 ) {
		return;
	}

	let currentTourIndex = 0;
	let validSteps       = [];
	let currentValidIdx  = 0;
	let overlay          = null;
	let modal            = null;
	let highlightedEl    = null;
	let savedStyles      = {};
	let keydownHandler   = null;
	let resizeHandler    = null;
	let resizeRaf        = 0;
	let currentTour      = null;
	let currentTarget    = null;

	// -------------------------------------------------------------------------
	// Entry point
	// -------------------------------------------------------------------------

	function init() {
		startNextTour();
	}

	// -------------------------------------------------------------------------
	// Tour sequencing
	// -------------------------------------------------------------------------

	function startNextTour() {
		while ( currentTourIndex < config.tours.length ) {
			const tour = config.tours[ currentTourIndex ];
			validSteps = computeValidSteps( tour );
			if ( validSteps.length > 0 ) {
				currentValidIdx = 0;
				renderStep( tour );
				return;
			}
			currentTourIndex++;
		}
	}

	function computeValidSteps( tour ) {
		const result = [];
		for ( let i = 0; i < tour.steps.length; i++ ) {
			if ( document.querySelector( tour.steps[ i ].selector ) ) {
				result.push( tour.steps[ i ] );
			} else {
				console.warn( '[WP Client Tour] Selector not found, skipping step:', tour.steps[ i ].selector );
			}
		}
		return result;
	}

	// -------------------------------------------------------------------------
	// Step rendering
	// -------------------------------------------------------------------------

	function renderStep( tour ) {
		const step   = validSteps[ currentValidIdx ];
		const target = document.querySelector( step.selector );

		// Selector may have been removed since computeValidSteps ran (e.g. AJAX repaint).
		if ( ! target ) {
			console.warn( '[WP Client Tour] Selector vanished at render time, skipping:', step.selector );
			advanceStep( tour );
			return;
		}

		currentTour   = tour;
		currentTarget = target;

		ensureOverlay();
		highlightTarget( target );
		target.scrollIntoView( { behavior: 'instant', block: 'nearest' } );
		renderModal( tour, step, target );
		ensureResizeHandler();
	}

	// -------------------------------------------------------------------------
	// Overlay
	// -------------------------------------------------------------------------

	function ensureOverlay() {
		if ( overlay ) {
			return;
		}
		overlay = document.createElement( 'div' );
		overlay.id = 'wct-overlay';
		document.body.appendChild( overlay );
	}

	function removeOverlay() {
		if ( overlay ) {
			overlay.remove();
			overlay = null;
		}
	}

	// -------------------------------------------------------------------------
	// Target highlight
	// -------------------------------------------------------------------------

	function highlightTarget( target ) {
		restoreTarget();
		highlightedEl = target;
		savedStyles = {
			position:  target.style.position,
			zIndex:    target.style.zIndex,
			boxShadow: target.style.boxShadow,
		};
		// Only force position:relative on static elements; preserve fixed/absolute/sticky.
		if ( getComputedStyle( target ).position === 'static' ) {
			target.style.position = 'relative';
		}
		target.style.zIndex    = '10000';
		target.style.boxShadow = '0 0 0 9999px rgba(0, 0, 0, 0.72)';
	}

	function restoreTarget() {
		if ( ! highlightedEl ) {
			return;
		}
		highlightedEl.style.position  = savedStyles.position;
		highlightedEl.style.zIndex    = savedStyles.zIndex;
		highlightedEl.style.boxShadow = savedStyles.boxShadow;
		highlightedEl = null;
		savedStyles   = {};
	}

	// -------------------------------------------------------------------------
	// Modal
	// -------------------------------------------------------------------------

	function renderModal( tour, step, target ) {
		if ( modal ) {
			modal.remove();
		}

		const totalValid = validSteps.length;
		const isFirst    = currentValidIdx === 0;
		const isLast     = currentValidIdx === totalValid - 1;

		modal = document.createElement( 'div' );
		modal.id   = 'wct-modal';
		modal.role = 'dialog';
		modal.setAttribute( 'aria-modal', 'true' );
		modal.setAttribute( 'aria-labelledby', 'wct-title' );
		modal.setAttribute( 'aria-describedby', 'wct-body' );

		// aria-live on the title so screen readers announce each new step
		// when the modal is removed and recreated between steps.
		modal.innerHTML =
			'<h2 class="wct-title" id="wct-title" aria-live="polite">' + escHtml( step.title ) + '</h2>' +
			'<div class="wct-body" id="wct-body">' + escHtml( step.body ) + '</div>' +
			'<div class="wct-footer">' +
				( ! isFirst ? '<button type="button" class="wct-btn-secondary wct-back">Back</button>' : '<span></span>' ) +
				'<span class="wct-counter">Step ' + ( currentValidIdx + 1 ) + ' of ' + totalValid + '</span>' +
				( isLast
					? '<button type="button" class="wct-btn-primary wct-finish">Done</button>'
					: '<button type="button" class="wct-btn-primary wct-next">Next</button>'
				) +
			'</div>' +
			'<button type="button" class="wct-skip" aria-label="Skip tour">Skip</button>';

		document.body.appendChild( modal );
		positionModal( modal, target, step.position );
		trapFocus( modal, tour );

		modal.querySelector( '.wct-skip' ).addEventListener( 'click', function () {
			completeTour( tour );
		} );

		if ( ! isFirst ) {
			modal.querySelector( '.wct-back' ).addEventListener( 'click', function () {
				goToValidStep( tour, currentValidIdx - 1 );
			} );
		}

		if ( isLast ) {
			modal.querySelector( '.wct-finish' ).addEventListener( 'click', function () {
				completeTour( tour );
			} );
		} else {
			modal.querySelector( '.wct-next' ).addEventListener( 'click', function () {
				advanceStep( tour );
			} );
		}
	}

	function advanceStep( tour ) {
		if ( currentValidIdx < validSteps.length - 1 ) {
			goToValidStep( tour, currentValidIdx + 1 );
		} else {
			completeTour( tour );
		}
	}

	function goToValidStep( tour, validIdx ) {
		currentValidIdx = validIdx;
		restoreTarget();
		renderStep( tour );
	}

	// -------------------------------------------------------------------------
	// Modal positioning
	// -------------------------------------------------------------------------

	function positionModal( el, target, preferred ) {
		const gap  = 12;
		const vw   = window.innerWidth;
		const vh   = window.innerHeight;
		const rect = target.getBoundingClientRect();
		const mw   = el.offsetWidth  || 340;
		const mh   = el.offsetHeight || 160;

		const positions = {
			top:    { top: rect.top  - mh - gap,                  left: rect.left + ( rect.width - mw ) / 2 },
			bottom: { top: rect.bottom + gap,                     left: rect.left + ( rect.width - mw ) / 2 },
			left:   { top: rect.top  + ( rect.height - mh ) / 2,  left: rect.left - mw - gap },
			right:  { top: rect.top  + ( rect.height - mh ) / 2,  left: rect.right + gap },
		};

		const opposite = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };

		function fits( pos ) {
			const p = positions[ pos ];
			return p.top >= 0 && p.left >= 0 && p.top + mh <= vh && p.left + mw <= vw;
		}

		let chosen = preferred;
		if ( ! fits( chosen ) ) {
			chosen = opposite[ chosen ];
		}

		let top, left;
		if ( fits( chosen ) ) {
			top  = positions[ chosen ].top;
			left = positions[ chosen ].left;
		} else {
			top  = ( vh - mh ) / 2;
			left = ( vw - mw ) / 2;
		}

		const margin = 8;
		top  = Math.max( margin, Math.min( top,  vh - mh - margin ) );
		left = Math.max( margin, Math.min( left, vw - mw - margin ) );

		el.style.top  = top  + 'px';
		el.style.left = left + 'px';
	}

	// -------------------------------------------------------------------------
	// Tour completion
	// -------------------------------------------------------------------------

	function completeTour( tour ) {
		teardown();
		// Mark complete on both finish and skip — both count as "user has dealt with it".
		markComplete( tour.id );
		currentTourIndex++;
		startNextTour();
	}

	function teardown() {
		restoreTarget();
		removeOverlay();
		if ( modal ) {
			if ( keydownHandler ) {
				modal.removeEventListener( 'keydown', keydownHandler );
				keydownHandler = null;
			}
			modal.remove();
			modal = null;
		}
		if ( resizeHandler ) {
			window.removeEventListener( 'resize', resizeHandler );
			resizeHandler = null;
		}
		currentTour   = null;
		currentTarget = null;
	}

	function ensureResizeHandler() {
		if ( resizeHandler ) {
			return;
		}
		resizeHandler = function () {
			if ( resizeRaf ) {
				return;
			}
			resizeRaf = window.requestAnimationFrame( function () {
				resizeRaf = 0;
				if ( modal && currentTarget && currentTour ) {
					const step = validSteps[ currentValidIdx ];
					if ( document.body.contains( currentTarget ) ) {
						positionModal( modal, currentTarget, step.position );
					}
				}
			} );
		};
		window.addEventListener( 'resize', resizeHandler );
	}

	function markComplete( tourId ) {
		fetch( config.restUrl, {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   config.nonce,
			},
			body: JSON.stringify( { tour_id: tourId } ),
		} ).catch( function ( err ) {
			console.warn( '[WP Client Tour] Could not mark tour complete:', err );
		} );
	}

	// -------------------------------------------------------------------------
	// Focus trap + keyboard handling
	// -------------------------------------------------------------------------

	function trapFocus( container, tour ) {
		const focusable = container.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		if ( ! focusable.length ) {
			return;
		}
		const first = focusable[ 0 ];
		const last  = focusable[ focusable.length - 1 ];

		first.focus();

		keydownHandler = function ( e ) {
			if ( e.key === 'Escape' ) {
				e.preventDefault();
				completeTour( tour );
				return;
			}
			if ( e.key !== 'Tab' ) {
				return;
			}
			if ( e.shiftKey ) {
				if ( document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				}
			} else {
				if ( document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
			}
		};
		container.addEventListener( 'keydown', keydownHandler );
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

	function escHtml( str ) {
		return str
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

	// -------------------------------------------------------------------------
	// Boot
	// -------------------------------------------------------------------------

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
