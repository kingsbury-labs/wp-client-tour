# CLAUDE.md — WP Client Tour

@.claude/context.md

This file is read automatically by Claude Code at the start of every session. It gives you full context on this project so you never need to ask for background.

---

## What This Project Is

**WP Client Tour** is an open source WordPress developer tool with two components:

1. **A Claude Code skill** (`skill/wp-client-tour.md`) — AI-powered tour authoring. Uses Playwright MCP to screenshot wp-admin, analyses the UI, and generates `tour.json` config files.
2. **A WordPress plugin** (`plugin/wp-client-tour/`) — Tour delivery engine. Reads JSON files, filters by user role and seen-state, and fires guided tours inside wp-admin using vanilla JS.

Read `SPEC.md` for full technical details before writing any code.

---

## Team

You are working with Rob Kingsbury (lead) and the Claude Code collab team (Morgan, Soren, Atlas). Rob's preferred workflow:

- Ask clarifying questions before starting any significant build task
- Prefer small, verifiable commits over large monolithic changes
- Write clean, commented PHP following WordPress coding standards
- Vanilla JS only — no jQuery, no frameworks, no external dependencies in the plugin
- Test assumptions by checking the DOM structure in the actual WP admin before writing selectors

---

## Stack

| Layer | Technology |
|---|---|
| Plugin language | PHP 7.4+ |
| Plugin JS | Vanilla ES6 (no build step required) |
| Plugin CSS | Plain CSS (WP admin palette) |
| Skill format | Markdown (Claude Code skill) |
| Tour config | JSON |
| WP minimum | 6.0 |
| Coding standard | WordPress PHP Coding Standards (WPCS) |

---

## Project Structure

```
wp-client-tour/
├── SPEC.md          ← Full technical specification — read this first
├── CLAUDE.md        ← This file
├── README.md        ← Public readme (write after MVP)
├── CHANGELOG.md     ← Version history
├── LICENSE          ← MIT
├── plugin/
│   └── wp-client-tour/
│       ├── wp-client-tour.php
│       ├── includes/
│       │   ├── class-tour-loader.php
│       │   ├── class-tour-renderer.php
│       │   └── class-admin-page.php
│       ├── assets/
│       │   ├── tour-client.js
│       │   └── tour-client.css
│       └── tours/
│           └── .gitkeep
└── skill/
    ├── wp-client-tour.md
    └── examples/
        ├── woocommerce-orders.json
        ├── acf-fields.json
        └── custom-dashboard.json
```

---

## Phase 1 Build Order (Recommended)

Build in this order to allow end-to-end testing as early as possible:

1. `plugin/wp-client-tour/wp-client-tour.php` — Main file, hooks, autoloader
2. `plugin/wp-client-tour/includes/class-tour-loader.php` — JSON loading and filtering
3. `plugin/wp-client-tour/includes/class-tour-renderer.php` — JS enqueue + REST endpoint
4. `plugin/wp-client-tour/assets/tour-client.js` — Renderer
5. `plugin/wp-client-tour/assets/tour-client.css` — Styles
6. `plugin/wp-client-tour/includes/class-admin-page.php` — Settings page
7. `skill/examples/*.json` — Three example tour files for testing
8. `skill/wp-client-tour.md` — The skill itself
9. `README.md` — Written last once everything works

---

## Key Technical Constraints

- **No jQuery.** WordPress loads it but we do not use it. Vanilla JS only.
- **No external libraries.** No Shepherd.js, no Intro.js, no CDN calls. The renderer is custom and self-contained.
- **No build step.** The JS is plain ES6, readable as-is. No webpack, no Vite, no npm.
- **WPCS compliance.** All PHP must pass WordPress coding standards: proper nonce verification, sanitisation, escaping, capability checks.
- **z-index awareness.** `#wpadminbar` is `z-index: 99999`. Modal is `z-index: 10001`. Target elements during tour are `z-index: 10000`. Do not fight the adminbar.
- **Graceful degradation.** If a selector is missing from the DOM, skip that step with a `console.warn` and continue. Never throw.

---

## Phase 2 Features (Do Not Build Yet)

- Admin UI simplification (show/hide menu items per role)
- Multi-page / cross-page tours
- Manual trigger (button/link)
- Re-audit skill for broken selectors
- Visual tour editor
- Analytics
- Multisite support
- Front-end tours

---

## WordPress.org Submission Checklist (Post-MVP)

Before submitting to the plugin directory:

- [ ] All user-facing strings wrapped in `__()` / `_e()` with text domain `wp-client-tour`
- [ ] All `$_GET`/`$_POST` inputs sanitised
- [ ] All output escaped with `esc_html()`, `esc_attr()`, `esc_url()` as appropriate
- [ ] Nonces on all form submissions and REST calls
- [ ] Capability checks on all admin actions (`current_user_can()`)
- [ ] No direct file access (`defined('ABSPATH') || exit;` at top of each PHP file)
- [ ] `readme.txt` in WordPress.org format
- [ ] Tested on WordPress 6.0, 6.4, 6.7 (current)
- [ ] Tested with default themes (Twenty Twenty-Four)
- [ ] Tested with WooCommerce, ACF, Yoast active simultaneously

---

## Useful References

- WP REST API: https://developer.wordpress.org/rest-api/
- `wp_localize_script()`: https://developer.wordpress.org/reference/functions/wp_localize_script/
- `update_user_meta()`: https://developer.wordpress.org/reference/functions/update_user_meta/
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
- `$pagenow` global: https://codex.wordpress.org/Global_Variables#Current_Screen_Info
