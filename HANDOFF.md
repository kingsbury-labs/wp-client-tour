---
project: wp-client-tour
session: 5
last_updated: 2026-05-02
continue_with: "Build Phase 2: multi-page cross-page tour navigation. Full spec below."
blockers: "None."
---

# WP Client Tour — Handoff

## Status

Phase 1 complete and E2E tested. Phase 2 fully designed, decisions locked, ready to build.

## What was done this session

- E2E test on brauwerk-hoffman succeeded
- Four tours created and rendered correctly in the browser
- Designed Phase 2 (multi-page tours) via collab plan review (Soren + Atlas + Morgan, 1 exchange)
- All five pre-build questions answered and decisions locked (see below)

## E2E test results (brauwerk-hoffman)

Tours created at `c:\xampp\htdocs\brauwerk-hoffman\wp-content\plugins\wp-client-tour\tours\`:
- `bh-welcome.json` — dashboard overview, 5 steps, targets `index.php`
- `bh-back-office.json` — season status and hours, 4 steps, targets `admin.php?page=kc-back-office`
- `bh-beers.json` — beer dashboard, 5 steps, targets `edit.php?post_type=bh_beer&page=bh-beer-dashboard`
- `bh-events.json` — events list, 3 steps, targets `edit.php?post_type=bh_event`

All tours render correctly. All currently `auto_once`. Medium-confidence selectors in bh-back-office (`#bh-season-open`, `#bh-season-open-date`, `#bh-season-status-message`) should be verified in DevTools.

## Phase 2 spec: multi-page cross-page tours

### Vision

Tours work like game tutorials. A step can say "Click Add New" with a pulse on the button, and clicking Next auto-navigates to the next admin page where the tour continues. Users can go Back across pages. The step counter shows global position across the whole multi-page tour.

### Mechanism: URL param handoff

URL params, not sessionStorage. sessionStorage has a ghost-resume bug (user hits Back, navigates elsewhere, stale token fires on wrong page). URL params don't survive the back/forward cycle. PHP validates at server boundary.

Navigation URL format: `admin.php?page=whatever&wct_resume=tour-id&wct_step=3`

On resume: loader reads `$_GET['wct_resume']` + `$_GET['wct_step']`, bypasses normal page matching, loads tour, injects `resumeStep` into JS config. JS cleans the URL immediately via `history.replaceState()`.

### Schema changes (backward compatible)

Two new optional per-step fields:

```json
{
  "id": "step-2",
  "selector": ".page-title-action",
  "position": "bottom",
  "title": "Add a New Event",
  "body": "Next will take you to the Add New Event screen.",
  "navigate_on_next": "post-new.php?post_type=bh_event",
  "target_page": "edit.php?post_type=bh_event"
}
```

- `navigate_on_next` (string, optional): relative admin path for the next page. Validated to reject `http`, `//`, `javascript:`, `..`.
- `target_page` per step (string, optional): which page this step lives on. Defaults to tour-level `target_page`. Needed so `computeValidSteps()` can distinguish "selector missing because broken" from "selector missing because it's on another page."

### Decisions locked

| # | Decision |
|---|---|
| 1 | Dual-list step model: `tour.steps` = global list (counter, resume token). `currentPageSteps` = DOM-validated subset (rendering, Back/Next). |
| 2 | Cross-page Back IS supported. Store previous page + step in URL params. User can go back and forth as needed. |
| 3 | Visual indicator on Next button when navigating: "Next: Add New Event →" instead of "Next". |
| 4 | Redirect detection: graceful silent failure. If the destination page redirects away (WooCommerce, ACF options pages), the tour doesn't resume and cleans up. Document as known limitation. No server-side preflight. |
| 5 | Declarative auto-navigate is the model. "Next: Add Event →" clicks Next, browser navigates. Interactive "wait for user to click the element" is shelved. |
| 6 | Pulse animation on highlighted target element IS in scope for this phase. Makes the declarative model feel intentional rather than slideshow-like. |

### Code changes required

| File | Change |
|---|---|
| `class-tour-loader.php` | `get_eligible_tours()`: resume branch at top. Read `wct_resume` + `wct_step` from `$_GET`, validate, load tour by ID, check role, skip seen-state for `auto_once`, inject `resumeStep`. |
| `class-tour-loader.php` | `validate_tour()`: accept optional per-step `target_page` and `navigate_on_next`. Validate `navigate_on_next` as safe relative path. |
| `class-tour-renderer.php` | `strip_tour_for_js()`: pass `target_page` and `navigate_on_next` through allowlist. |
| `class-tour-renderer.php` | `enqueue()`: add `adminUrl` (from `admin_url()`) to localized config object. |
| `tour-client.js` | `computeValidSteps()` refactor: dual-list model. `currentPageSteps` (DOM-validated for this page) + `globalStepIndex` mapping. |
| `tour-client.js` | `advanceStep()`: check `navigate_on_next`, build URL with `wct_resume` + `wct_step` params using `config.adminUrl`, navigate. |
| `tour-client.js` | `goBack()`: if at first step of resumed page, build URL back to previous page with previous step param. |
| `tour-client.js` | Boot sequence: check `config.resumeStep`, call `history.replaceState()` to clean URL, start from saved index. |
| `tour-client.js` | Next button label: when step has `navigate_on_next`, render "Next: [page label] →". |
| `tour-client.js` | Pulse animation: CSS keyframe pulse on highlighted target element. |
| `tour-client.css` | Pulse keyframe animation for highlighted targets. |
| `skill/wp-client-tour.md` | Update with multi-page authoring guidance and known limitations (redirect-stripping pages, AJAX-driven pages like Gutenberg). |

### Known limitations to document

- Pages that redirect on load (WooCommerce options, ACF options) strip URL params. Tours targeting these as resume destinations will silently fail.
- AJAX-driven pages (Gutenberg, WooCommerce HPOS) don't fire DOMContentLoaded on internal navigation. `navigate_on_next` requires a full HTTP page load at the destination.
- Subdirectory WP installs: JS must resolve `navigate_on_next` against `config.adminUrl`, never string-concatenate.

### Build sequence (from Atlas)

1. Schema + validation: update `validate_tour()` for new fields. No behavioral change yet.
2. Loader resume branch: PHP reads params, loads tour, injects `resumeStep`. Test with manually crafted URL.
3. Renderer passthrough: `strip_tour_for_js()` passes new fields. `adminUrl` in config.
4. JS `computeValidSteps()` refactor. Most invasive change - design dual-list model before coding. Test single-page tours for regression.
5. JS navigation + resume: `advanceStep()` handles `navigate_on_next`. Boot handles `resumeStep` + URL cleanup.
6. JS Back at page boundaries: cross-page Back via URL params.
7. Next button label: "Next: [page] →" when step has `navigate_on_next`.
8. Pulse animation: CSS keyframe + JS apply on highlight.
9. Multi-page example tour JSON for E2E testing.
10. Skill doc update.

### Validation gate

Phase complete when: a two-page tour (e.g. Events list → Add New Event) runs end-to-end on brauwerk-hoffman with correct global step counter, clean URL after resume, cross-page Back works, pulse on highlighted elements, no regression on existing single-page tours.

## All Phase 1 files (complete, not changing)

1. `plugin/wp-client-tour/wp-client-tour.php`
2. `plugin/wp-client-tour/includes/class-tour-loader.php`
3. `plugin/wp-client-tour/includes/class-tour-renderer.php`
4. `plugin/wp-client-tour/assets/tour-client.js`
5. `plugin/wp-client-tour/assets/tour-client.css`
6. `plugin/wp-client-tour/includes/class-admin-page.php`
7. `plugin/wp-client-tour/uninstall.php`

## Open issues

- No GitHub repo yet
- Deferred from audit (known limits, not blockers): L12 (REST failure swallowed), L13 (no JSON caching), L15 (overflow:hidden box-shadow clipping), UX1 (tour auto-chaining)
- Medium-confidence selectors in bh-back-office need DevTools verification on the live site

## Local environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- E2E test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Plugin install path: `c:\xampp\htdocs\brauwerk-hoffman\wp-content\plugins\wp-client-tour\`
- No GitHub repo yet
