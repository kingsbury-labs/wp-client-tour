# WP Client Tour — Roadmap

This roadmap reflects planned and potential future work. Nothing here is scheduled; priorities shift based on feedback and usage.

---

## v1.0.0 — Core delivery (released)

- WordPress plugin with JSON-driven tour engine
- Auto-trigger by admin page, user role, and seen-state
- Vanilla JS renderer: modal, highlight, overlay, positioning
- REST endpoint to mark tours complete
- Settings page with test mode
- Claude Code skill for AI-powered tour authoring

---

## v1.1.0 — Multi-page tours (in progress)

- Cross-page tour navigation via URL param handoff (`wct_resume` + `wct_step`)
- Global step counter across pages
- Cross-page Back navigation
- "Next: [Page Name] →" label on navigation steps
- Pulse animation on highlighted target elements
- Per-step `target_page` and `navigate_on_next` fields in tour JSON schema

---

## v1.2.0 — Manual triggers

- Trigger a tour from any link or button in wp-admin
- `[wct-trigger tour="tour-id"]` shortcode for admin pages
- `data-wct-trigger="tour-id"` HTML attribute support
- Admin bar "Replay Tour" button (contextual, shown when a tour exists for the current page)

---

## v1.3.0 — Admin UI simplification

- Show or hide wp-admin menu items per user role
- Config driven from JSON, same authoring workflow as tours
- Reversible: changes are applied at render time, not saved to the database

---

## v1.4.0 — Analytics

- Track tour starts, completions, and skips per user and per tour
- Simple admin dashboard with per-tour funnel (started / completed / dropped at step N)
- CSV export
- No external services — data stored in WordPress

---

## v2.0.0 — Visual tour editor

- Point-and-click tour builder inside wp-admin
- Click an element on any admin page to capture its selector
- Edit step copy inline
- Preview tour without saving
- Export to tour.json for version control

---

## Backlog (no version assigned)

- **Front-end tours** — run tours on public-facing pages, not just wp-admin
- **Multisite support** — network-level tours, per-site overrides
- **Conditional steps** — show a step only if a condition is met (e.g. a plugin is active, a setting has a value)
- **Tour chaining** — automatically start a follow-up tour after another completes
- **Gutenberg compatibility** — tours targeting block editor UI (AJAX-driven, no full page load between steps)
- **WooCommerce HPOS compatibility** — same challenge as Gutenberg for order management pages
- **REST API for tour management** — CRUD tours via REST for headless or CI workflows
- **WP-CLI commands** — `wp client-tour list`, `wp client-tour reset <user> <tour>`, etc.
- **Selector confidence scoring** — warn when a tour's selectors haven't been verified against the current WP/plugin version
- **WordPress.org plugin directory submission**

---

## Known limitations (document, not fix in v1.x)

- Pages that redirect on load (some WooCommerce options, ACF options pages) strip URL params. Tours targeting these as resume destinations will silently not resume.
- AJAX-driven page transitions (Gutenberg, WooCommerce HPOS) do not fire `DOMContentLoaded`. `navigate_on_next` requires a full HTTP page load at the destination.
- Subdirectory WordPress installs are handled via `config.adminUrl` — never string-concatenate admin paths in JS.
