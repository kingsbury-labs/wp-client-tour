# WP Client Tour

**AI-authored guided help tours for WordPress admin. Built for developers. Designed for clients.**

WP Client Tour is an open source tool that lets WordPress developers generate interactive step-by-step help tours inside wp-admin in minutes — eliminating the need for support docs, training videos, and recurring hand-holding.

It has two parts:

- A **WordPress plugin** that reads JSON tour files and renders them as guided overlays in wp-admin. Zero dependencies, role-aware, set-and-forget.
- An **AI authoring skill** (Claude Code) that screenshots a real wp-admin page, identifies the UI elements clients need help with, and produces the JSON for you. Optional — the plugin works fine with hand-written JSON too.

---

## Table of Contents

- [Quick Start](#quick-start)
  - [If you use Claude Code](#if-you-use-claude-code)
  - [If you don't use Claude Code](#if-you-dont-use-claude-code)
- [How It Works](#how-it-works)
- [Installation](#installation)
  - [Plugin (any WordPress site)](#plugin-any-wordpress-site)
  - [Skill (Claude Code users only, one-time)](#skill-claude-code-users-only-one-time)
- [Authoring a Tour](#authoring-a-tour)
- [Tour JSON Schema](#tour-json-schema)
- [Plugin Settings](#plugin-settings)
- [Filters & Hooks](#filters--hooks)
- [REST API](#rest-api)
- [Known Limitations](#known-limitations)
- [Requirements](#requirements)
- [Contributing](#contributing)
- [License](#license)

---

## Quick Start

### If you use Claude Code

From any project directory, point Claude at the wp-client-tour folder:

> "Use the wp-client-tour folder. Install the plugin into `c:\xampp\htdocs\my-site` and create a tour for the WooCommerce orders page."

Claude will (with this README and SPEC.md as reference):

1. Copy the plugin into your site's `wp-content/plugins/` directory
2. Activate it (or print activation instructions if WP-CLI isn't available)
3. Use Playwright MCP to screenshot the target wp-admin page
4. Generate a `tour.json` with selectors, copy, and confidence flags
5. Save the JSON into the plugin's `tours/` directory
6. Tell you what to do next (log in as the target role, reload, watch the tour fire)

### If you don't use Claude Code

1. Copy `plugin/wp-client-tour/` into your site's `wp-content/plugins/wp-client-tour/`
2. Activate **WP Client Tour** from the wp-admin Plugins page
3. Open one of the example tours in [skill/examples/](skill/examples/) as a template
4. Edit the `id`, `target_page`, `target_roles`, and `steps` to fit your site
5. Save the JSON into `wp-content/plugins/wp-client-tour/tours/your-tour.json`
6. Reload wp-admin as a user with the target role — the tour fires automatically

The schema is documented in full below ([Tour JSON Schema](#tour-json-schema)). If you can grab a CSS selector with browser DevTools and write plain English, you can author a tour by hand in 10–15 minutes.

---

## How It Works

```
┌──────────────────────────┐     ┌──────────────────────────┐     ┌──────────────────────────┐
│  Authoring (optional)    │     │  Tour file               │     │  Delivery                │
│                          │     │                          │     │                          │
│  Skill drives Playwright │ ──► │  tours/my-tour.json      │ ──► │  Plugin scans tours/     │
│  → screenshots wp-admin  │     │  on disk                 │     │  → filters by page+role  │
│  → analyzes UI           │     │                          │     │  → renders overlay      │
│  → writes JSON           │     │  (or hand-written)       │     │  → REST: mark complete   │
└──────────────────────────┘     └──────────────────────────┘     └──────────────────────────┘
```

The plugin and the skill never communicate directly. The bridge is a flat JSON file. That means:

- You can use either side without the other
- Tours are easy to version-control, share, copy between sites, edit by hand
- A non-Claude user can install the plugin and write JSON manually without losing functionality

---

## Installation

### Plugin (any WordPress site)

**Manual install:**

1. Download or clone this repo
2. Copy the `plugin/wp-client-tour/` folder into your site's `wp-content/plugins/` directory so the path becomes `wp-content/plugins/wp-client-tour/`
3. In wp-admin, go to **Plugins > Installed Plugins** and activate **WP Client Tour**
4. The plugin's activation hook creates `wp-content/plugins/wp-client-tour/tours/` if it doesn't already exist

**Via WP-CLI:**

```bash
cp -r plugin/wp-client-tour /path/to/site/wp-content/plugins/
wp plugin activate wp-client-tour --path=/path/to/site
```

**Verifying install:**

After activating, go to **Settings > WP Client Tour** in wp-admin. You should see an empty tour table and three management forms (Test Mode, Reset All, Reset User).

### Skill (Claude Code users only, one-time)

The authoring skill needs to be installed once into your global Claude Code skills directory. After that it's available in any project.

**Windows (PowerShell):**

```powershell
$dest = "$env:USERPROFILE\.claude\skills\wp-client-tour"
New-Item -ItemType Directory -Force -Path $dest | Out-Null
Copy-Item "skill\wp-client-tour.md" "$dest\SKILL.md"
Copy-Item -Recurse "skill\examples" "$dest\examples"
```

**macOS / Linux:**

```bash
mkdir -p ~/.claude/skills/wp-client-tour
cp skill/wp-client-tour.md ~/.claude/skills/wp-client-tour/SKILL.md
cp -r skill/examples ~/.claude/skills/wp-client-tour/examples
```

**Required MCP servers:**

- **Playwright MCP** — the skill drives a real browser to screenshot wp-admin. Without it, the skill can still produce JSON, but you'll have to provide selectors yourself.

To verify, open Claude Code in any project and try:

> "Create a client tour for /wp-admin/edit.php"

---

## Authoring a Tour

### With the skill

The skill handles everything. Provide:

1. **Target URL** — full wp-admin URL of the page to tour (e.g. `http://localhost/wp-admin/admin.php?page=wc-orders`)
2. **Workflow description** — what the client needs to do (e.g. "fulfill an order")
3. **Target role** — who sees this tour (default: `editor`)
4. **Tone** — how to phrase steps (default: plain, friendly, non-technical)

The skill screenshots the page, picks 3–7 elements that matter for the workflow, writes the steps, and saves the JSON. Each step gets a confidence flag (`high` / `medium` / `low`) so you know which selectors to double-check.

### By hand

1. Copy one of the [example files](skill/examples/) as a starting template
2. Open the target page in your browser, log in as the target role
3. For each step you want to teach, use DevTools to find a stable CSS selector for the element
4. Write a 3–6 word `title` and 1–3 sentence `body` in plain language
5. Save as `wp-content/plugins/wp-client-tour/tours/your-tour-id.json`

**Selector tips:**

- Prefer IDs (`#toplevel_page_woocommerce`) over classes
- Prefer WordPress core classes (`.page-title-action`) over plugin classes
- Avoid positional selectors (`:nth-child(3)`) — they break when admins reorder things
- Avoid the WP adminbar (`#wpadminbar` and children) — its z-index 99999 sits above everything

---

## Tour JSON Schema

```json
{
  "id": "woocommerce-orders",
  "version": "1.1",
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
      "title": "Your Orders Menu",
      "body": "Everything for your online store lives here. Click Next and we'll go to your orders.",
      "navigate_on_next": "admin.php?page=wc-orders",
      "navigate_label": "View Orders",
      "confidence": "high"
    },
    {
      "id": "step-2",
      "target_page": "admin.php?page=wc-orders",
      "selector": ".wc-orders-list-table",
      "position": "top",
      "title": "Your Order List",
      "body": "Each row is one customer order. Click any order number to open it.",
      "confidence": "high"
    }
  ]
}
```

### Top-level fields

| Field | Required | Type | Description |
|---|---|---|---|
| `id` | yes | string | Unique tour ID. Lowercase letters, digits, hyphens only (matches `^[a-z0-9-]+$`). Used as the filename and in completion-tracking meta. |
| `target_page` | yes | string | wp-admin URL after `/wp-admin/`. Examples: `edit.php`, `admin.php?page=wc-orders`, `post.php?post_type=page`. Every key=value in the query string must match `$_GET` (partial match — extra params don't break it). |
| `target_roles` | yes | string[] | WordPress role slugs the tour should appear for. Examples: `["administrator"]`, `["editor", "shop_manager"]`. |
| `trigger` | yes | enum | One of `auto_once`, `auto_always`, `manual`. See [Triggers](#triggers). |
| `steps` | yes | object[] | Ordered list of step definitions. At least one. |
| `label` | optional | string | Human-readable name shown in the admin tour table. Falls back to `id` if missing. |
| `version` | optional | string | Schema/tour version. Currently informational. |
| `created` | optional | string | ISO date (YYYY-MM-DD). Shown in the admin tour table. |

### Step fields

| Field | Required | Type | Description |
|---|---|---|---|
| `id` | yes | string | Unique step ID within the tour. Convention: `step-1`, `step-2`, etc. |
| `selector` | yes | string | CSS selector for the target element. If the selector matches nothing on the page, the step is skipped with a `console.warn`. |
| `position` | yes | enum | One of `top`, `right`, `bottom`, `left`. Preferred placement of the modal relative to the target. The renderer flips to the opposite side if it overflows the viewport, then centres if neither fits. |
| `title` | yes | string | Heading shown in the modal. Recommended 3–6 words. |
| `body` | yes | string | Description shown in the modal. Recommended 1–3 sentences in plain language. |
| `target_page` | optional | string | Which admin page this step belongs to. Steps whose `target_page` doesn't match the current page are silently skipped during rendering. Required on steps after the first page in a multi-page tour. |
| `navigate_on_next` | optional | string | Relative admin path (e.g. `post-new.php?post_type=bh_event`). When set, clicking Next navigates to this page and resumes the tour at the following step. Absolute URLs and path traversal are rejected. |
| `navigate_label` | optional | string | Label shown on the Next button when `navigate_on_next` is set. The button reads "Next: [label] →". Falls back to plain "Next →" if omitted. |
| `confidence` | optional | enum | One of `high`, `medium`, `low`. Authoring metadata only — not used at runtime. |

### Triggers

- **`auto_once`** — Tour fires the first time a user with a matching role visits the matching page. Once they finish or skip it, it never fires for them again (unless reset via the admin page or unless Test Mode is on).
- **`auto_always`** — Tour fires every time the page loads, regardless of completion state. Useful for documentation-style tours that should always be available.
- **`manual`** — Currently a no-op. The plugin skips these tours entirely. Reserved for v1.2's manual trigger feature (button, link, admin bar item).

### Validation rules

A tour is rejected (silently — never fires, but doesn't crash anything) if:

- Any required top-level field is missing
- `id` doesn't match `^[a-z0-9-]+$`
- `target_roles` is empty
- `trigger` is not one of the three enum values
- `steps` is empty
- Any step is missing a required field, or has a non-string field, or has a `position` outside the four enum values

You can see exactly what validates by reading [includes/class-tour-loader.php](plugin/wp-client-tour/includes/class-tour-loader.php).

---

## Plugin Settings

Go to **Settings → WP Client Tour** in wp-admin. The page provides:

- **Tour table** — every JSON file in `tours/` that passes validation. Shows ID, label, target page, roles, step count, trigger, created date.
- **Test Mode** — a toggle that makes all `auto_once` tours behave as `auto_always`. Lets you re-watch tours during development without resetting completion state.
- **Reset All Users** — clears the `wct_completed_tours` user meta for every user. After clicking, every user will see every `auto_once` tour again on their next visit. Confirmation prompt before firing.
- **Reset User** — clear completion data for a single user by username. Shows a generic "Reset complete" message regardless of whether the user exists (avoids username enumeration).

Each form uses a per-action nonce. All actions require the `manage_options` capability.

---

## Filters & Hooks

### `wct_tours_dir` (filter)

Override the directory the plugin scans for tour JSON files. Useful if you want to keep tours outside the plugin folder so they survive plugin updates.

```php
add_filter( 'wct_tours_dir', function( $dir ) {
    return WP_CONTENT_DIR . '/wct-tours/';
} );
```

### `register_activation_hook`

The plugin registers an activation hook that calls `wp_mkdir_p()` on the tours directory. If you change `wct_tours_dir` after activation, you'll need to create the new directory yourself.

### `uninstall.php`

When the plugin is deleted via the wp-admin Plugins page, `uninstall.php` runs and:

- Deletes the `wct_test_mode` option
- Bulk-deletes the `wct_completed_tours` user meta from every user

Deactivating the plugin (without deleting) leaves all data in place.

---

## REST API

### `POST /wp-json/wp-client-tour/v1/complete`

Marks a tour as completed for the current user. The plugin's JS calls this automatically when the user finishes or skips a tour.

**Auth:** logged-in user. Nonce: `wp_rest`. Header: `X-WP-Nonce`.

**Body:**

```json
{ "tour_id": "woocommerce-orders" }
```

**Validation:** `tour_id` must match `^[a-z0-9-]+$` AND correspond to an actual JSON file in the tours directory. Format-valid but nonexistent tour IDs return 404.

**Response (success):**

```json
{ "success": true }
```

**Response (unknown tour):**

```json
{ "success": false, "error": "unknown_tour" }
```

---

## Known Limitations

These are documented limits, not bugs. Phase 2 may address some.

- **No tour authoring UI in the plugin.** Tours are JSON files. Use the AI skill or hand-write them.
- **No manual triggers yet.** `trigger: "manual"` is a v1.2 feature; tours with that trigger are skipped today.
- **Box-shadow highlight clips under `overflow: hidden` parents.** Visual-only failure on uncommon layouts. Re-targeting to an outer element is the workaround.
- **Tour auto-chaining is unconditional.** If multiple eligible tours exist on one page, they fire back-to-back with no gap. Author tours so this doesn't happen, or set them to different roles.
- **REST completion failures are silent.** If the network call to mark the tour complete fails (offline, expired nonce), the tour will replay on next page load. The browser console logs a warning.
- **No persistent JSON cache.** Tours are re-read from disk on every admin page load. Fine for typical usage (1–10 tours per site).

---

## Requirements

- **WordPress** 6.0+
- **PHP** 7.4+
- **No JS framework requirement** — vanilla ES6, no jQuery dependency
- **For the authoring skill only:** Claude Code + Playwright MCP

---

## Contributing

Pull requests welcome. Please read [SPEC.md](SPEC.md) for the full architecture and constraints, and [CLAUDE.md](CLAUDE.md) for project rules (WPCS, vanilla JS only, no build step).

Issues and feature requests: [github.com/kingsbury-labs/wp-client-tour/issues](https://github.com/kingsbury-labs/wp-client-tour/issues)

---

## License

MIT — free for personal and commercial use. See [LICENSE](LICENSE).

---

## Credits

Created by [Rob Kingsbury](https://kingsburycreative.com) / Kingsbury Creative, Arnprior, Ontario.

Plugin and skill co-developed with Claude (Anthropic).
