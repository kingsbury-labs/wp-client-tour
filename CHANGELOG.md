# Changelog

All notable changes to WP Client Tour will be documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

---

## [1.2.3] — 2026-05-03

### Fixed
- Pulse outline color changed from white to WP admin blue (`#2271b1`). The white outline was invisible against light-background elements (headings, content areas) that sit above the overlay. Blue is visible on both dark and light backgrounds.

---

## [1.2.2] — 2026-05-03

### Fixed
- `.wct-pulse` now sets `z-index: 9999 !important` so highlighted target elements always render above the `#wct-overlay` (z-index 9998). Without this, elements with `position: relative` but no explicit z-index fell behind the overlay and became invisible during the tour.

---

## [1.2.1] — 2026-05-02

### Fixed
- Overlay dimming now handled by `#wct-overlay` (set to `rgba(0,0,0,0.72)`) instead of the box-shadow highlight technique. The previous approach applied `box-shadow: 0 0 0 9999px` directly to the target element, which broke inside high z-index stacking contexts — most visibly when targeting elements inside `#wpadminbar` (z-index 99999), where the shadow painted over the modal.
- `highlightTarget()` no longer manipulates `z-index` or `box-shadow` on the target element. The pulse animation remains the visual "look here" signal.
- Modal arrow groundwork: `positionModal()` now sets `data-position` on the modal and computes a dynamic `--wct-arrow-offset` CSS custom property so arrows track the target centre correctly after viewport clamping. Arrow rendering is included but not visible by default — reserved for a future design pass.

---

## [1.2.0] — 2026-05-02

### Added
- `trigger: "manual"` tours now fully supported — no longer skipped by the loader; they fire only via explicit launch
- PHP helper `wct_tour_launch_url( $tour_id )` — returns an admin URL with `wct_force` param; returns `false` for unknown IDs; logs to `error_log()` when `WP_DEBUG` is on
- `[wct_launch tour="id" label="text"]` shortcode — renders a WP-native button in any wp-admin context; role-checks before output; empty string for unknown tours or insufficient role
- Admin bar **Tours** menu — lists all `trigger: "manual"` tours eligible for the current user; completed tours show "(Replay)"; parent node suppressed when no eligible tours exist; each node has a descriptive `aria-label`
- `class-manual-trigger.php` — new class encapsulating all manual trigger logic; registered via `plugins_loaded`
- Dashboard widget now includes `trigger: "manual"` tours alongside auto tours
- `console.error` escalation in `tour-client.js` when a manually-triggered tour has no valid selectors on the current page

### Changed
- `WCT_Tour_Loader::matches_user_role()` changed from `private` to `public static` for use by shortcode and admin bar; no external API consumers at v1.x
- `WCT_Tour_Loader::get_all_valid_tours()` now uses a static request-level cache to avoid repeated filesystem glob calls when multiple surfaces (admin bar, shortcode, widget) load tours in the same request

### Known limitation
- When a manually-triggered tour is launched but no selectors match the current page, the UI is silent (only `console.error` logged). A visible client-side fallback message is planned for v1.3.0.

---

## [1.1.0] — 2026-05-02

### Added
- Multi-page tour support via URL param handoff (`wct_resume` + `wct_step`)
- Loader resume branch: validates params, bypasses page-matching, injects `resumeStep`; role check still enforced
- Per-step optional fields: `target_page`, `navigate_on_next`, `navigate_label`
- `is_safe_relative_path()` validation rejects absolute URLs, protocol-relative paths, `javascript:`, `data:`, and path traversal in `navigate_on_next` values
- Dual-list step model in JS: global step list (counter, resume token) + per-page DOM-validated subset (rendering)
- Global step counter: "Step 3 of 8" across all pages of a multi-page tour
- Cross-page Back navigation via URL params
- "Next: [Label] →" button text when a step has `navigate_on_next` and `navigate_label`
- `buildResumeUrl()` resolves against `config.adminUrl` for subdirectory WordPress installs
- URL cleanup on resume page load: `history.replaceState` strips `wct_resume`/`wct_step` so Back/Forward don't re-fire
- Pulse animation (CSS keyframe) on highlighted target elements
- `adminUrl` and `currentPage` added to localized JS config object
- `ROADMAP.md` with versioned feature plans
- Example multi-page tour: `skill/examples/multipage-new-post.json` (Posts list → block editor)

### Changed
- `strip_tour_for_js()` now passes `targetPage` (tour level) and optional per-step `target_page`, `navigate_on_next`, `navigate_label`, `resumeStep`
- `computeValidSteps()` refactored to dual-list model; returns `{ step, globalIndex }` pairs; steps on other pages silently skipped (no `console.warn`)
- Step counter shows global position across all pages, not local position within current page

---

## [1.0.0] — 2026-05-01

### Added
- WordPress plugin (Phase 1):
  - Main plugin file with constants, hooks, activation hook for `tours/` directory
  - `WCT_Tour_Loader` — scans `tours/`, validates JSON schema, filters by `$pagenow` + query string + role + completion state, respects Test Mode
  - `WCT_Tour_Renderer` — enqueues vanilla JS/CSS, passes tour data via `wp_localize_script`, registers REST endpoint `POST /wp-client-tour/v1/complete`
  - `WCT_Admin_Page` — Settings → WP Client Tour with tour table, Test Mode toggle, Reset All, Reset User
  - `tour-client.js` — vanilla ES6 renderer with overlay, box-shadow punch-out highlight, modal positioning with viewport flip/clamp, focus trap, Escape-to-dismiss, throttled resize handler, ARIA attributes
  - `tour-client.css` — overlay + modal styles in WP admin palette, `:focus-visible` outlines
  - `uninstall.php` — clears option + bulk-deletes completion meta on plugin deletion
- Claude Code skill for AI-powered tour authoring (`skill/wp-client-tour.md`)
- Three example tour JSON files (WooCommerce Orders, ACF Fields, Custom Dashboard)
- Filter `wct_tours_dir` for relocating the tours directory outside the plugin folder
- Constants `WCT_META_KEY` and `WCT_OPTION_TEST_MODE`
- REST endpoint validates that `tour_id` corresponds to an actual file before recording completion
- Object-cache invalidation after bulk reset
- Per-action nonces on all admin form submissions
- Full specification (`SPEC.md`), README, MIT license

### Security
- All form submissions guarded by per-action nonces and `manage_options` capability check
- All `$_POST` reads pass through `wp_unslash` before sanitisation
- Reset User shows generic confirmation message (no username enumeration)
- REST endpoint requires logged-in user; `tour_id` regex-validated and existence-checked

### Accessibility
- Modal uses `role="dialog"`, `aria-modal`, `aria-labelledby`, `aria-describedby`
- Step title has `aria-live="polite"` for screen-reader step announcements
- Focus trap on Tab/Shift+Tab; Escape dismisses
- All interactive elements have visible `:focus-visible` outline
