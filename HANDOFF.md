---
project: wp-client-tour
session: 6
last_updated: 2026-05-02
continue_with: "Build v1.2.0: manual triggers. Core mechanism (wct_force) already done. Need helper function, shortcode, admin bar hook, and flip manual trigger tours from skipped to live."
blockers: "None."
---

# WP Client Tour — Handoff

## Status

v1.1.0 shipped. Multi-page tours live. Landing page live at kingsbury-labs.github.io/wp-client-tour/. v1.2.0 is next — manual triggers. Core mechanism already built this session.

## v1.2.0 Scope (ready to build)

The `wct_force=<id>` URL param is already in the loader (built this session). Manual triggering from any link already works. What remains:

| Task | File | Notes |
|---|---|---|
| PHP helper `wct_tour_launch_url( $tour_id )` | new or `wp-client-tour.php` | Clean API for theme/plugin authors |
| Shortcode `[wct_launch tour="id" label="text"]` | new `class-shortcode.php` | Renders a button anywhere in wp-admin |
| Admin bar item support | `class-tour-renderer.php` or new class | Register node in `admin_bar_menu` |
| Flip `trigger: "manual"` from skipped to supported | `class-tour-loader.php` | Currently `continue`d in `get_eligible_tours()` — remove that skip, rely on `wct_force` |
| Documentation | README.md, skill/prompt.md | How to wire a manual trigger |

Estimated effort: 2-3 hours.

## What was done this session (Session 5)

Built and shipped landing page, update checker, dashboard widget, LLM-agnostic prompt, and mobile fixes. See Session 6 summary in context.md.

## Session 4 summary

Phase 2 (multi-page tours) built and E2E tested on brauwerk-hoffman. All working. v1.1.0 tagged and released on GitHub.

## Local environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- E2E test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Plugin install path: `c:\xampp\htdocs\brauwerk-hoffman\wp-content\plugins\wp-client-tour\`
- GitHub: `https://github.com/kingsbury-labs/wp-client-tour`
- Landing page: `https://kingsbury-labs.github.io/wp-client-tour/`
