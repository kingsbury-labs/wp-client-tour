# Changelog

All notable changes to WP Client Tour will be documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added
- WordPress plugin (Phase 1 complete):
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
- Constants `WCT_META_KEY` (`wct_completed_tours`) and `WCT_OPTION_TEST_MODE` (`wct_test_mode`)
- REST endpoint validates that `tour_id` corresponds to an actual file before recording completion
- Object-cache invalidation (`clean_user_cache`) after bulk reset
- Per-action nonces (`wct_toggle_test_mode`, `wct_reset_all`, `wct_reset_user`)
- Initial project structure, full specification (`SPEC.md`), comprehensive README

### Security
- All form submissions guarded by per-action nonces and `manage_options` capability check
- All `$_POST` reads pass through `wp_unslash` before sanitisation (WPCS-compliant)
- Reset User shows generic confirmation message (no username enumeration)
- REST endpoint requires logged-in user; `tour_id` regex-validated and existence-checked

### Accessibility
- Modal uses `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, `aria-describedby`
- Step title element has `aria-live="polite"` for screen-reader announcement on transitions
- Focus trap on Tab/Shift+Tab
- Escape key dismisses modal
- All focusable elements have visible `:focus-visible` outline
