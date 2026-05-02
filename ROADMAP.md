# WP Client Tour — Roadmap

This file describes the planned direction for the project. Milestones and individual issues live on GitHub, where you can track progress, comment, and contribute.

---

## Versions at a glance

| Version | Theme | Status | Milestone |
|---------|-------|--------|-----------|
| 1.0.0 | Core tour delivery | Released | — |
| 1.1.0 | Multi-page tours | Code complete, testing | [Milestone 1](https://github.com/kingsbury-labs/wp-client-tour/milestone/1) |
| 1.2.0 | Manual triggers | Planned | [Milestone 2](https://github.com/kingsbury-labs/wp-client-tour/milestone/2) |
| 1.3.0 | Admin UI simplification | Planned | [Milestone 3](https://github.com/kingsbury-labs/wp-client-tour/milestone/3) |
| 1.4.0 | Analytics | Planned | [Milestone 4](https://github.com/kingsbury-labs/wp-client-tour/milestone/4) |
| 2.0.0 | Visual tour editor | Planned | [Milestone 5](https://github.com/kingsbury-labs/wp-client-tour/milestone/5) |

---

## v1.0.0 — Core delivery (released)

- WordPress plugin with JSON-driven tour engine
- Auto-trigger by admin page, user role, and seen-state
- Vanilla JS renderer: modal, highlight, overlay, positioning
- REST endpoint to mark tours complete
- Settings page with test mode
- Claude Code skill for AI-powered tour authoring

---

## v1.1.0 — Multi-page tours

Cross-page tour navigation. Steps can say "click Next to go to the Add New Event page" and the tour resumes exactly where it left off. Global step counter across pages. Cross-page Back.

See [milestone 1](https://github.com/kingsbury-labs/wp-client-tour/milestone/1) for open tasks.

**New JSON fields**
```json
{
  "navigate_on_next": "post-new.php?post_type=bh_event",
  "navigate_label": "Add New Event",
  "target_page": "post-new.php?post_type=bh_event"
}
```

**Known limitations**
- Pages that redirect on load (some WooCommerce options, ACF options) strip URL params — tours targeting these as resume destinations will silently not resume.
- AJAX-driven page transitions (Gutenberg, WooCommerce HPOS) don't fire `DOMContentLoaded`. `navigate_on_next` requires a full HTTP page load at the destination.

---

## v1.2.0 — Manual triggers

Trigger a tour from any link, button, or admin bar item. Useful for "Replay Tour" buttons, help links inside plugin settings pages, and onboarding flows the user can re-open.

See [milestone 2](https://github.com/kingsbury-labs/wp-client-tour/milestone/2) for open tasks.

---

## v1.3.0 — Admin UI simplification

Show or hide wp-admin menu and submenu items per user role, configured in JSON. Applied at render time — no database changes, fully reversible.

See [milestone 3](https://github.com/kingsbury-labs/wp-client-tour/milestone/3) for open tasks.

---

## v1.4.0 — Analytics

Track tour starts, completions, skips, and step-level drop-off in a local database table. Admin dashboard with per-tour funnel and CSV export. No external services.

See [milestone 4](https://github.com/kingsbury-labs/wp-client-tour/milestone/4) for open tasks.

---

## v2.0.0 — Visual tour editor

Point-and-click tour builder inside wp-admin. Click any element to capture its selector, write step copy inline, preview the tour, export to JSON. No code required for basic tours.

See [milestone 5](https://github.com/kingsbury-labs/wp-client-tour/milestone/5) for open tasks.

---

## Backlog

Ideas tracked as issues but not yet assigned to a version. See [open backlog issues](https://github.com/kingsbury-labs/wp-client-tour/issues?q=is%3Aopen+label%3Abacklog).

- Front-end tours (public-facing pages)
- Multisite support
- Conditional steps
- Tour chaining
- Gutenberg / WooCommerce HPOS compatibility
- WP-CLI commands
- WordPress.org plugin directory submission

---

## Contributing

Bug reports, feature requests, and pull requests are welcome. Check the [open issues](https://github.com/kingsbury-labs/wp-client-tour/issues) — items labelled `good first issue` or `help wanted` are good starting points.
