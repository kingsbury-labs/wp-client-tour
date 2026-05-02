/* global wpClientTour */
( function () {
	'use strict';

	const config = window.wpClientTour;
	if ( ! config || ! Array.isArray( config.tours ) || config.tours.length === 0 ) {
		return;
	}

	let currentTourIndex = 0;
	let validSteps       = []; // Array of { step, globalIndex }
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
		// Clean resume params from URL so Back/forward navigation doesn't re-fire.
		if ( window.location.search.indexOf( 'wct_resume' ) !== -1 ) {
			const url = new URL( window.location.href );
			url.searchParams.delete( 'wct_resume' );
			url.searchParams.delete( 'wct_step' );
			history.replaceState( null, '', url.toString() );
		}
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
				// If resuming, seek to the matching global step index.
				if ( tour.resumeStep !== undefined ) {
					for ( let i = 0; i < validSteps.length; i++ ) {
						if ( validSteps[ i ].globalIndex === tour.resumeStep ) {
							currentValidIdx = i;
							break;
						}
					}
				}
				renderStep( tour );
				return;
			}
			// All selectors missing on this page — escalate for manual-trigger tours
			// so developers see a clear signal in the console rather than silent nothing.
			if ( tour.trigger === 'manual' ) {
				console.error( '[WP Client Tour] Tour "' + tour.id + '" was launched but has no valid selectors on this page. Check that target_page and selectors match the current screen.' );
			}
			currentTourIndex++;
		}
	}

	/**
	 * Build the dual-list model for a tour.
	 *
	 * Returns only steps that belong to the current page AND have a live DOM
	 * selector. Steps on other pages are silently skipped (not broken — just
	 * elsewhere). Each entry carries the step's index in tour.steps so the
	 * global counter and cross-page navigation stay accurate.
	 *
	 * @param {Object} tour
	 * @returns {Array<{step: Object, globalIndex: number}>}
	 */
	function computeValidSteps( tour ) {
		const result = [];
		for ( let i = 0; i < tour.steps.length; i++ ) {
			const step     = tour.steps[ i ];
			const stepPage = step.target_page || tour.targetPage;

			if ( stepPage !== config.currentPage ) {
				continue; // on a different page — not missing, just not here
			}

			if ( document.querySelector( step.selector ) ) {
				result.push( { step: step, globalIndex: i } );
			} else {
				console.warn( '[WP Client Tour] Selector not found, skipping step:', step.selector );
			}
		}
		return result;
	}

	// -------------------------------------------------------------------------
	// Step rendering
	// -------------------------------------------------------------------------

	function renderStep( tour ) {
		const entry  = validSteps[ currentValidIdx ];
		const step   = entry.step;
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
		target.classList.add( 'wct-pulse' );
	}

	function restoreTarget() {
		if ( ! highlightedEl ) {
			return;
		}
		highlightedEl.style.position  = savedStyles.position;
		highlightedEl.style.zIndex    = savedStyles.zIndex;
		highlightedEl.style.boxShadow = savedStyles.boxShadow;
		highlightedEl.classList.remove( 'wct-pulse' );
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

		const entry       = validSteps[ currentValidIdx ];
		const globalIdx   = entry.globalIndex;
		const totalGlobal = tour.steps.length;

		// First step globally = no back possible at all.
		const isFirst = currentValidIdx === 0 && globalIdx === 0;
		// Last step on this page = Done if no navigate_on_next, else navigate.
		const isLast  = currentValidIdx === validSteps.length - 1 && ! step.navigate_on_next;

		let nextLabel = 'Next';
		if ( step.navigate_on_next ) {
			nextLabel = 'Next' + ( step.navigate_label ? ': ' + escHtml( step.navigate_label ) : '' ) + ' →';
		}

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
				'<span class="wct-counter">Step ' + ( globalIdx + 1 ) + ' of ' + totalGlobal + '</span>' +
				( isLast
					? '<button type="button" class="wct-btn-primary wct-finish">Done</button>'
					: '<button type="button" class="wct-btn-primary wct-next">' + nextLabel + '</button>'
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
				goBack( tour );
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
		const entry = validSteps[ currentValidIdx ];
		const step  = entry.step;

		if ( step.navigate_on_next ) {
			navigateToNextPage( tour, entry );
			return;
		}

		if ( currentValidIdx < validSteps.length - 1 ) {
			goToValidStep( tour, currentValidIdx + 1 );
		} else {
			completeTour( tour );
		}
	}

	function goBack( tour ) {
		if ( currentValidIdx > 0 ) {
			goToValidStep( tour, currentValidIdx - 1 );
			return;
		}

		// Cross-page back: navigate to the previous global step's page.
		const prevGlobalIndex = validSteps[ 0 ].globalIndex - 1;
		if ( prevGlobalIndex < 0 ) {
			return;
		}
		const prevStep = tour.steps[ prevGlobalIndex ];
		const prevPage = prevStep.target_page || tour.targetPage;
		window.location.href = buildResumeUrl( prevPage, tour.id, prevGlobalIndex );
	}

	function navigateToNextPage( tour, entry ) {
		const nextGlobalIndex = entry.globalIndex + 1;
		window.location.href = buildResumeUrl( entry.step.navigate_on_next, tour.id, nextGlobalIndex );
	}

	/**
	 * Build a resume URL pointing at the given admin path with wct_resume + wct_step appended.
	 * Resolves against config.adminUrl so subdirectory installs work correctly.
	 *
	 * @param {string} adminPath Relative admin path, e.g. "post-new.php?post_type=bh_event"
	 * @param {string} tourId
	 * @param {number} stepIndex Global step index to resume at.
	 * @returns {string}
	 */
	function buildResumeUrl( adminPath, tourId, stepIndex ) {
		// config.adminUrl ends with a trailing slash (WordPress convention).
		const base = config.adminUrl + adminPath;
		const sep  = base.indexOf( '?' ) !== -1 ? '&' : '?';
		return base + sep + 'wct_resume=' + encodeURIComponent( tourId ) + '&wct_step=' + stepIndex;
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
					const entry = validSteps[ currentValidIdx ];
					if ( entry && document.body.contains( currentTarget ) ) {
						positionModal( modal, currentTarget, entry.step.position );
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
