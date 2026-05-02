# WP Client Tour — Project Specification v1.0

> **Status:** Pre-build. Hand this document to Claude Code to begin implementation.
> **Last updated:** 2026-05-01
> **Author:** Rob Kingsbury / Kingsbury Creative

---

## Project Overview

WP Client Tour is an open source developer tool for building interactive help tours inside the WordPress admin. It eliminates the need for bulky help documentation, training videos, and support materials by delivering in-place, step-by-step guided tours directly inside wp-admin — scoped per user role, per page, and per workflow.

It consists of two independent components:

| Component | What it is | Where it lives |
|---|---|---|
| **Claude Code Skill** | AI-powered tour authoring tool | Developer's machine (`~/.claude/skills/`) |
| **WordPress Plugin** | Tour delivery engine | Inside the WordPress install |

The skill generates tour config files. The plugin serves them. They are fully decoupled — the plugin works without the skill, and the skill can target any WordPress install.

---

## Problem Being Solved

WordPress developers who build custom sites for clients face a recurring support burden: clients don't remember how to use their own admin. The current solutions are:

- Written help docs (time-consuming to produce, rarely read)
- Training videos (expensive, go stale fast)
- Repeated support calls (costs developer time and client money)

WP Client Tour solves this by letting a developer spend 10–15 minutes generating a context-aware, role-scoped guided tour directly inside wp-admin. The tour highlights exactly the right elements, in the right order, with plain-English descriptions — and never shows again once the user has completed it.

---

## Target Users

**Primary:** WordPress developers (agency devs, freelancers, vibe coders) who build client sites and want to hand off a polished, self-explaining admin experience.

**Secondary:** The WordPress plugin developer community — anyone who wants to add onboarding tours to their own plugin's admin UI without building from scratch.

**End users (indirect):** The developer's clients — business owners, editors, shop managers — who use the WordPress admin daily and benefit from the tours without ever knowing the underlying tooling.

---

## Core Design Principles

1. **Zero dependencies in production.** The plugin uses vanilla JS and PHP only. No jQuery, no external libraries, no CDN calls.
2. **Decoupled authoring from delivery.** Tours are JSON files. They can be hand-written, AI-generated, or imported. The plugin doesn't care.
3. **Non-destructive.** The plugin never modifies WordPress core, theme files, or other plugin files.
4. **Role-aware by default.** Every tour targets specific user roles. Admins and editors see different tours.
5. **Seen-state tracked per user.** Once a user completes or dismisses a tour, it won't show again unless reset.
6. **Developer-first DX.** The skill should make tour authoring feel effortless. The plugin should be trivial to install and forget.
7. **Open source, MIT licensed.** No tracking, no cloud, no analytics beyond local user meta.

---

## Repository Structure

```
wp-client-tour/                    ← GitHub repo root
│
├── SPEC.md                        ← This file
├── CLAUDE.md                      ← Instructions for Claude Code agents
├── README.md                      ← Public-facing readme
├── CHANGELOG.md                   ← Version history
├── LICENSE                        ← MIT
│
├── plugin/                        ← The WordPress plugin
│   └── wp-client-tour/
│       ├── wp-client-tour.php     ← Main plugin file
│       ├── includes/
│       │   ├── class-tour-loader.php
│       │   ├── class-tour-renderer.php
│       │   └── class-admin-page.php
│       ├── assets/
│       │   ├── tour-client.js     ← Vanilla JS renderer (~150 lines)
│       │   └── tour-client.css    ← Overlay, highlight, modal styles
│       └── tours/
│           └── .gitkeep           ← Tour JSON files live here
│
└── skill/                         ← The Claude Code skill
    ├── wp-client-tour.md          ← Skill definition (SKILL.md format)
    └── examples/
        ├── woocommerce-orders.json
        ├── acf-fields.json
        └── custom-dashboard.json
```

---

## Component 1: Claude Code Skill

### Purpose

The skill is invoked inside a Claude Code session while a developer is building or maintaining a client WordPress site. It uses Playwright MCP to screenshot wp-admin pages, analyses the UI, and generates ready-to-use `tour.json` config files — writing them directly into the plugin's `tours/` directory.

### Location

```
C:\Users\roban\.claude\skills\wp-client-tour\
```
(or equivalent on other machines)

### Invocation

The skill auto-triggers when the developer uses natural language like:

- "Create a client tour for the WooCommerce orders screen"
- "Build a help tour for the custom settings page"
- "Add a guided walkthrough for the editor dashboard"
- "Tour the ACF field group for this client"

### Skill Workflow

```
1. Developer invokes skill naturally in Claude Code
2. Skill asks for: target URL, workflow/page description, target role (default: editor)
3. Playwright MCP navigates to the URL and captures a full-page screenshot
4. Claude analyses the screenshot: identifies tourable UI regions, labels, buttons, menu items
5. Claude generates ordered tour steps with CSS selectors and plain-English descriptions
6. Skill writes tour.json to /plugin/wp-client-tour/tours/
7. Skill prints summary: step count, confidence flags, any low-confidence selectors
8. Skill offers to generate additional tours for other pages
```

### Skill Inputs

The developer provides (at minimum):

| Input | Required | Default |
|---|---|---|
| Target admin URL or page description | Yes | — |
| Target user role | No | `editor` |
| Tone of step descriptions | No | Plain, friendly English |
| Elements to explicitly include | No | Auto-detected |
| Elements to explicitly exclude | No | None |

### Tour JSON Output Format

```json
{
  "id": "woocommerce-orders",
  "version": "1.0",
  "label": "WooCommerce Orders",
  "target_page": "admin.php?page=wc-orders",
  "target_roles": ["editor", "shop_manager"],
  "trigger": "auto_once",
  "created": "2026-05-01",
  "steps": [
    {
      "id": "step-1",
      "selector": "#toplevel_page_woocommerce",
      "position": "right",
      "title": "Your Orders",
      "body": "This is where all your customer orders live. Click here any time to see what needs fulfilling.",
      "confidence": "high"
    },
    {
      "id": "step-2",
      "selector": ".wc-orders-page .page-title-action",
      "position": "bottom",
      "title": "Add a Manual Order",
      "body": "Need to create an order for a phone or in-person sale? Use this button to add it manually.",
      "confidence": "medium"
    }
  ]
}
```

### Confidence Field

| Value | Meaning | Action required |
|---|---|---|
| `high` | Selector is stable, uses ID or reliable WP core class | None — safe to ship |
| `medium` | Selector may vary by theme or plugin version | Developer should verify in browser |
| `low` | Selector is fragile or ambiguous | Must be manually confirmed before going live |

### Skill Output to Developer

After writing the file, the skill prints:

```
✅ Tour generated: woocommerce-orders
   📍 Target page: admin.php?page=wc-orders
   👤 Roles: editor, shop_manager
   📝 Steps: 6 (5 high confidence, 1 medium)

⚠️  Review needed:
   Step 4 — selector ".wc-action-button-complete" (medium confidence)
   Reason: Button class may differ if WooCommerce version < 7.0

📁 Written to: plugin/wp-client-tour/tours/woocommerce-orders.json

Generate another tour? (e.g. "tour the product edit screen")
```

---

## Component 2: WordPress Plugin

### Plugin Header

```php
/**
 * Plugin Name: WP Client Tour
 * Plugin URI:  https://github.com/[org]/wp-client-tour
 * Description: AI-authored guided help tours for your clients inside wp-admin. Zero dependencies, role-aware, set-and-forget.
 * Version:     1.0.0
 * Author:      Rob Kingsbury
 * Author URI:  https://kingsburycreative.com
 * License:     MIT
 * Text Domain: wp-client-tour
 */
```

### Requirements

- PHP 7.4+
- WordPress 6.0+
- No other plugins required
- No external services

### Plugin Directory Structure

```
wp-client-tour/
├── wp-client-tour.php
├── includes/
│   ├── class-tour-loader.php      ← Reads + validates JSON, filters by page/role
│   ├── class-tour-renderer.php    ← Outputs JS config to page head
│   ├── class-admin-page.php       ← Settings UI (Settings > WP Client Tour)
│   ├── class-update-checker.php   ← GitHub update check (12h transient)
│   ├── class-dashboard-widget.php ← Dashboard tour launcher widget
│   └── class-manual-trigger.php   ← Admin bar, shortcode, PHP helper (v1.2)
├── assets/
│   ├── tour-client.js             ← Vanilla JS renderer
│   └── tour-client.css            ← All styles
└── tours/
    └── .gitkeep
```

---

### class-tour-loader.php

**Responsibilities:**
- On each `admin_init`, scan the `tours/` directory for `.json` files
- Parse and validate each file against the schema
- Filter to tours matching the current admin page URL (`$pagenow` + query string)
- Filter by current user role — only load tours the user is eligible for
- Check user meta `wp_tour_client_completed` — skip tours this user has already seen
- Return array of eligible tour configs to the renderer

**Key methods:**
```php
public function get_eligible_tours(): array
private function matches_current_page( array $tour ): bool
private function matches_user_role( array $tour ): bool
private function user_has_completed( string $tour_id ): bool
private function validate_tour( array $tour ): bool
```

---

### class-tour-renderer.php

**Responsibilities:**
- If eligible tours exist for the current page/user, enqueue `tour-client.js` and `tour-client.css`
- Pass tour data to JS via `wp_localize_script()` as `wpClientTour.tours`
- Register a REST API endpoint `POST /wp-client-tour/v1/complete` for marking tours seen
- Handle nonce verification on the REST endpoint

**REST endpoint:**
```
POST /wp-json/wp-client-tour/v1/complete
Body: { "tour_id": "woocommerce-orders" }
Auth: Nonce (wp_rest)
Effect: Appends tour_id to user meta array wp_tour_client_completed
```

---

### class-admin-page.php

**Responsibilities:**
- Register settings page at **Settings > WP Client Tour**
- Display a table of all tours in `tours/` directory showing:
  - Tour ID
  - Label
  - Target page
  - Target roles
  - Step count
  - Created date
- Provide a **Reset All** button — clears `wp_tour_client_completed` for every user
- Provide a **Reset for User** field — enter a username to reset just that user's completion data
- Provide a **Test Mode** toggle — sets trigger to `auto_always` globally for testing
- No tour editing — tours are authored by the skill, not in the UI

---

### tour-client.js — Renderer Specification

**Zero dependencies. Vanilla JS only. ~150–200 lines.**

#### Initialisation

```javascript
// Passed in via wp_localize_script
const config = window.wpClientTour; // { tours: [...], nonce: "...", restUrl: "..." }
```

#### Per-tour flow

```
1. Find first eligible tour (trigger === 'auto_once' or 'auto_always')
2. Attempt to locate step[0].selector in the DOM
3. If found: begin tour
4. If not found: skip that step, log console warning, try next step
5. Never throw — graceful degradation always
```

#### Per-step rendering

```
1. Create/update overlay element (full-page dark overlay)
2. Find target element, apply highlight via box-shadow punch-out technique
3. Scroll target element into view
4. Position modal adjacent to target using position hints + viewport collision detection
5. Render modal: title, body, Back/Next buttons, step counter (e.g. "Step 2 of 6"), Skip link
6. Trap focus inside modal for accessibility
```

#### Highlight technique

```css
/* Applied to target element */
position: relative;
z-index: 10000;
box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.72);
border-radius: 3px;
```

This avoids needing a separate SVG overlay or clip-path and handles irregular element shapes cleanly.

#### Modal positioning

```
Preferred position comes from step.position: "top" | "right" | "bottom" | "left"
If modal would overflow viewport edge → flip to opposite side
If still overflows → centre on screen
Always keep modal fully within viewport
```

#### On tour complete or skip

```
1. Fire POST to /wp-json/wp-client-tour/v1/complete with tour_id
2. Remove overlay and modal from DOM
3. Restore target element styles
4. If another eligible tour exists for this page → begin it
```

#### WP Admin compatibility requirements

- Must not break the WP admin top bar (`#wpadminbar`, `position: fixed, z-index: 99999`)
- Must not break the folded/unfolded sidebar (`#adminmenu`)
- Must not conflict with common plugin admin UIs: ACF, WooCommerce, Yoast, Elementor
- Must work at 1280px+ viewport width (WP admin minimum usable width)
- Must degrade gracefully if a selector no longer exists in the DOM

---

### tour-client.css — Style Specification

```css
/* Overlay */
#wct-overlay { position: fixed; inset: 0; z-index: 9998; background: transparent; pointer-events: none; }

/* Modal */
#wct-modal { position: fixed; z-index: 10001; background: #fff; border-radius: 6px; box-shadow: 0 4px 24px rgba(0,0,0,0.18); padding: 20px 24px; max-width: 340px; min-width: 260px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }

/* Modal elements */
.wct-title { font-size: 15px; font-weight: 600; margin: 0 0 8px; color: #1d2327; }
.wct-body  { font-size: 13px; line-height: 1.6; color: #50575e; margin: 0 0 16px; }
.wct-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.wct-btn-primary { background: #2271b1; color: #fff; border: none; border-radius: 3px; padding: 6px 14px; font-size: 13px; cursor: pointer; }
.wct-btn-secondary { background: transparent; color: #2271b1; border: 1px solid #2271b1; border-radius: 3px; padding: 6px 14px; font-size: 13px; cursor: pointer; }
.wct-skip { font-size: 12px; color: #787c82; text-decoration: underline; cursor: pointer; margin-left: auto; }
.wct-counter { font-size: 12px; color: #787c82; }
```

All colours use WP admin design system values to blend naturally with the admin UI.

---

### User Meta Storage

```
Key:   wp_tour_client_completed
Type:  Serialised PHP array
Value: [ "woocommerce-orders", "custom-dashboard", ... ]
Scope: Per WordPress user (update_user_meta / get_user_meta)
```

---

### Trigger Types

| Value | Behaviour |
|---|---|
| `auto_once` | Fires automatically the first time an eligible user visits the target page. Never repeats after completion or dismissal. |
| `auto_always` | Fires every page load. Used for development/testing only. |
| `manual` | Never fires automatically. Only launches via `wct_force` URL param, the admin bar Tours menu, the `[wct_launch]` shortcode, or the `wct_tour_launch_url()` PHP helper. |

---

## Phase 1 Deliverables (MVP)

- [ ] Claude Code skill (`skill/wp-client-tour.md`) that produces valid tour JSON from a Playwright screenshot
- [ ] `class-tour-loader.php` — JSON loading, validation, page/role/seen filtering
- [ ] `class-tour-renderer.php` — JS enqueue, data localisation, REST endpoint
- [ ] `class-admin-page.php` — Settings page with tour list and reset controls
- [ ] `tour-client.js` — Vanilla JS renderer with overlay, highlight, modal, navigation, skip
- [ ] `tour-client.css` — All styles, WP admin palette, no external fonts
- [ ] `wp-client-tour.php` — Main plugin file, hooks, autoloader
- [ ] `README.md` — Public readme covering both skill and plugin install
- [ ] Example tour JSON files (3 examples in `skill/examples/`)
- [ ] Working end-to-end test: generate a tour with the skill, install plugin, see tour fire in WP admin

---

## Out of Scope for Phase 1

These are confirmed Phase 2 features. Do not build them in Phase 1:

- Admin UI simplification (show/hide menu items per role)
- Multi-page / cross-page tours (state persists across page navigations)
- ~~Manual trigger (button or link that fires a tour on demand)~~ — shipped in v1.2.0
- Re-audit skill (checks existing tour selectors against live admin, flags broken ones)
- Visual tour editor in wp-admin
- Analytics or completion rate tracking
- WordPress Multisite support
- Front-end (non-admin) tours

---

## Known Constraints and Gotchas

**Selector fragility:** CSS selectors break when plugins update their UI. The skill's confidence scoring exists to surface this risk. A Phase 2 re-audit skill will address it systematically.

**WP Admin z-index stack:** `#wpadminbar` is `z-index: 99999`. The highlight technique uses `z-index: 10000` on the target element and `z-index: 10001` on the modal. This means the adminbar will render above highlighted elements — this is intentional and acceptable. Avoid touring the adminbar itself.

**Dynamic elements:** Some WP admin elements only appear after user interaction (dropdown menus, metabox toggles). Playwright can interact with these but static screenshot analysis cannot. The skill should flag steps targeting dynamic elements as `confidence: medium` and note the interaction required.

**WP admin screen detection:** Use `$pagenow` combined with `$_GET` query string parameters for page matching. A WooCommerce orders page is `admin.php?page=wc-orders` — matching on `$pagenow === 'admin.php'` alone is not sufficient.

**Nonce handling:** All REST API calls from the JS renderer must include the `X-WP-Nonce` header. Pass the nonce via `wp_localize_script`.

---

## Tour JSON Schema (Reference)

```json
{
  "$schema": "http://json-schema.org/draft-07/schema",
  "type": "object",
  "required": ["id", "target_page", "target_roles", "trigger", "steps"],
  "properties": {
    "id":           { "type": "string", "pattern": "^[a-z0-9-]+$" },
    "version":      { "type": "string" },
    "label":        { "type": "string" },
    "target_page":  { "type": "string" },
    "target_roles": { "type": "array", "items": { "type": "string" } },
    "trigger":      { "type": "string", "enum": ["auto_once", "auto_always", "manual"] },
    "created":      { "type": "string" },
    "steps": {
      "type": "array",
      "minItems": 1,
      "items": {
        "type": "object",
        "required": ["id", "selector", "position", "title", "body"],
        "properties": {
          "id":         { "type": "string" },
          "selector":   { "type": "string" },
          "position":   { "type": "string", "enum": ["top", "right", "bottom", "left"] },
          "title":      { "type": "string" },
          "body":       { "type": "string" },
          "confidence": { "type": "string", "enum": ["high", "medium", "low"] }
        }
      }
    }
  }
}
```

---

## Delivery and Distribution

**GitHub:** Primary source of truth. Both `plugin/` and `skill/` directories live in one repo.

**WordPress.org:** Plugin directory submission after MVP is stable. Slug: `wp-client-tour`.

**Skill distribution:** Raw `.md` file linked from README. Install with:
```powershell
# Windows (PowerShell)
Copy-Item -Recurse "skill\wp-client-tour" "$env:USERPROFILE\.claude\skills\wp-client-tour"
```
```bash
# macOS / Linux
cp -r skill/wp-client-tour ~/.claude/skills/wp-client-tour
```

**License:** MIT. No restrictions on commercial use.

---

## Open Questions for Phase 2 Planning

1. Should the admin simplifier (menu item show/hide per role) live in this plugin or a separate companion plugin?
2. Should the re-audit skill be a separate skill file or an extension of the main skill?
3. What's the right UX for a "manual" trigger — a shortcode, a WP admin bar button, or a programmatic function call?
4. Should completed tour state be exportable (e.g. "all users on this site have seen this tour")?
