# WP Client Tour — Skill Definition
# File: wp-client-tour.md
# Install to: ~/.claude/skills/wp-client-tour/SKILL.md (macOS/Linux)
#              C:\Users\[you]\.claude\skills\wp-client-tour\SKILL.md (Windows)

---

## Skill: WP Client Tour Authoring

**Trigger phrases:**
- "create a client tour for [page/workflow]"
- "build a help tour for [description]"
- "add a guided walkthrough for [description]"
- "tour the [page] for my client"
- "generate a wp-admin tour"
- "make a tour for [plugin name] admin screen"

---

## What This Skill Does

This skill authors interactive guided help tours for WordPress admin screens. It uses Playwright MCP to screenshot a live wp-admin page, analyses the UI elements present, and produces a `tour.json` config file that the WP Client Tour plugin can serve to clients.

The output is a JSON file written directly to the plugin's `tours/` directory. No manual selector hunting. No hand-coding tour steps. The developer reviews confidence flags and ships.

---

## Required MCP Tools

- **Playwright MCP** — for navigating to and screenshotting the target admin URL
- **Filesystem MCP** — for writing the output JSON file

If Playwright MCP is not connected, inform the developer and provide the JSON output for manual placement instead.

---

## Workflow

### Step 1 — Gather inputs

Ask the developer for (if not already provided in the prompt):

1. **Target URL** — the full wp-admin URL of the page to tour (e.g. `http://localhost/wp-admin/admin.php?page=wc-orders`)
2. **Workflow description** — what the client needs to do on this page (e.g. "fulfill a WooCommerce order")
3. **Target role** — who sees this tour (default: `editor`)
4. **Output path** — where to write the JSON (default: `plugin/wp-client-tour/tours/`)
5. **Tone** — how to write step descriptions (default: plain, friendly English suitable for a non-technical business owner)

### Step 2 — Screenshot the page

Use Playwright MCP to:
- Navigate to the target URL (ensure the user is logged in — ask if credentials are needed)
- Wait for the page to fully load
- Take a full-page screenshot

### Step 3 — Analyse the UI

Review the screenshot and identify:

**Always include if present:**
- Primary navigation menu item for this section (left sidebar)
- Page title / heading
- Primary action buttons (Add New, Save, Publish, etc.)
- Key data areas (order list, form fields, metaboxes)
- Any custom plugin UI elements specific to this workflow

**Prioritise elements that:**
- A client would need to find on day one
- Cause the most common support questions
- Are non-obvious or hidden behind clicks

**Avoid touring:**
- WP adminbar items (z-index conflict)
- Developer-only tools (Screen Options, Help tab)
- Items that require hover to reveal
- Items only visible after interaction (unless noting this in the step body)

### Step 4 — Generate tour steps

For each element identified, produce a step with:

| Field | How to fill it |
|---|---|
| `id` | `step-N` (sequential) |
| `selector` | CSS selector — prefer ID > stable class > element+class combo. Avoid nth-child, positional selectors. |
| `position` | Where to show the modal relative to the element: `top`, `right`, `bottom`, `left`. Choose based on available space in the screenshot. |
| `title` | 3–6 words. What this UI element IS. |
| `body` | 1–3 sentences. What the client DOES with it, in plain language. No jargon. |
| `confidence` | `high` (stable ID/core class), `medium` (plugin class, may change), `low` (positional, fragile) |

**Writing style for step body:**
- Address the client directly ("This is where you...", "Click here to...")
- Avoid WordPress jargon ("post type", "taxonomy", "hook")
- Keep it action-oriented — what do they DO here?
- Assume zero technical knowledge

**Example step:**
```json
{
  "id": "step-3",
  "selector": ".wc-order-status",
  "position": "right",
  "title": "Order Status",
  "body": "This shows whether the order is pending, processing, or complete. You'll change this after you've packed and shipped the order.",
  "confidence": "medium"
}
```

### Step 5 — Assemble the JSON

```json
{
  "id": "[page-slug-kebab-case]",
  "version": "1.0",
  "label": "[Human-readable tour name]",
  "target_page": "[URL query string after /wp-admin/ e.g. admin.php?page=wc-orders]",
  "target_roles": ["[role]"],
  "trigger": "auto_once",
  "created": "[YYYY-MM-DD]",
  "steps": [ ... ]
}
```

### Step 6 — Write the file

Write to: `[project-root]/plugin/wp-client-tour/tours/[id].json`

If the output path cannot be determined, print the JSON for the developer to place manually.

### Step 7 — Print summary

```
✅ Tour generated: [id]
   📍 Target page: [target_page]
   👤 Roles: [roles]
   📝 Steps: [N] ([X high], [Y medium], [Z low] confidence)

[If any medium/low confidence:]
⚠️  Review before shipping:
   Step N — selector "[selector]" ([confidence])
   Reason: [brief explanation of why it might be fragile]

📁 Written to: plugin/wp-client-tour/tours/[id].json

Generate another tour? Describe the next page or workflow.
```

---

## Selector Confidence Guidelines

| Confidence | Selector type | Example |
|---|---|---|
| high | WordPress core ID | `#toplevel_page_woocommerce` |
| high | Stable WP core class | `.page-title-action` |
| high | Form field ID | `#title` |
| medium | Plugin-specific class | `.wc-orders-page` |
| medium | Theme-dependent class | `.entry-content` |
| low | Positional | `ul.wp-submenu li:nth-child(3)` |
| low | Text-dependent | `a:contains("Orders")` |
| low | Auto-generated | `.wp-block-12345` |

---

## Common WordPress Admin Selectors Reference

```
WP Admin menu:          #adminmenu
Specific menu item:     #toplevel_page_{slug}
Submenu:                #menu-{slug}
Page heading:           .wp-heading-inline
Add New button:         .page-title-action
List table:             .wp-list-table
List table rows:        .wp-list-table tbody tr
Notices area:           #wpbody-content .notice
Save button (post):     #publish
Post title field:       #title
Admin bar:              #wpadminbar  ← DO NOT TOUR (z-index conflict)
```

---

## Example Tours

See `skill/examples/` for three complete example JSON files:
- `woocommerce-orders.json` — Touring the WooCommerce orders screen
- `acf-fields.json` — Touring an ACF-powered custom page template
- `custom-dashboard.json` — Touring a simplified client dashboard

---

## Error Handling

**If Playwright can't reach the URL:**
- Check if the dev server is running (XAMPP / Local / etc.)
- Confirm the user is logged in to wp-admin
- Ask for credentials if needed (never store them)

**If a page has no tourable elements:**
- Tell the developer and ask if they want to tour a different page

**If confidence is all low:**
- Flag this to the developer — the page may use highly dynamic or JS-rendered UI
- Suggest targeting the page after manual interaction if needed
