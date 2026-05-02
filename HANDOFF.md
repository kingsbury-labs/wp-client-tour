---
project: wp-client-tour
session: 7
last_updated: 2026-05-02
continue_with: "Plan v1.3.0: admin UI simplification — show/hide wp-admin menu items per role, configured in JSON."
blockers: "None."
---

# WP Client Tour — Handoff

## Status

v1.2.0 shipped. Manual triggers live. Landing page updated. v1.3.0 is next — admin UI simplification.

## What was done this session (Session 6)

### v1.2.0: Manual triggers

All four delivery surfaces built and E2E tested:

| Surface | Implementation |
|---|---|
| Admin bar Tours menu | `class-manual-trigger.php::register_admin_bar()` — all eligible manual tours, "(Replay)" suffix for completed, parent node suppressed when empty, `aria-label` on each node |
| `[wct_launch]` shortcode | `class-manual-trigger.php::render_shortcode()` — registered on `admin_init`, role-checked, WP-native `<a class="button">`, empty string for unknown tour or wrong role |
| `wct_tour_launch_url()` | Global PHP helper in `wp-client-tour.php` delegating to `WCT_Manual_Trigger::get_launch_url()` — returns `false` for unknown IDs, logs to `error_log` when WP_DEBUG is on |
| `trigger: "manual"` tours in dashboard widget | One-line fix in `class-dashboard-widget.php` — removed manual tour filter |

### Supporting changes

- `get_all_valid_tours()` — static request-level cache added (4 lines) to avoid repeated glob calls
- `matches_user_role()` — changed from `private` to `public static` for use by shortcode and admin bar
- `console.error` in `tour-client.js` when a manually-triggered tour has no valid selectors on the current page
- Full docs audit: README (new Manual Triggers section, trigger descriptions, Known Limitations), CHANGELOG (v1.2.0 entry), SPEC (trigger table, plugin structure, Out of Scope), landing page (comparison table, roadmap table, schema section, new feature card), skill/prompt.md (manual trigger description)

### Known limitation (deferred to v1.3.0)

When a manually-triggered tour is launched but no selectors match the current page, the UI is silent. `console.error` is now logged, but no visible client-side message. Tracked in CHANGELOG.

## v1.3.0 Scope (next)

Per ROADMAP.md: admin UI simplification — show/hide wp-admin menu items per role, configured in JSON.

## Local environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- E2E test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Plugin install path: `c:\xampp\htdocs\brauwerk-hoffman\wp-content\plugins\wp-client-tour\`
- GitHub: `https://github.com/kingsbury-labs/wp-client-tour`
- Landing page: `https://kingsbury-labs.github.io/wp-client-tour/`
