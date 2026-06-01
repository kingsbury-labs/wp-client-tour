---
project: wp-client-tour
session: 13
last_updated: 2026-05-31
phase: "Under review at WordPress.org. DNS verified. Slug change to kingsbury-client-tour requested. Awaiting approval."
continue_with: "Wait for WordPress.org to confirm domain verification and approve slug change."
blockers: "None. Ball is in WordPress.org's court."

tech:
  product_name: Kingsbury Client Tour
  type: Open source WordPress plugin + LLM-agnostic authoring skill
  plugin_language: PHP 7.4+
  plugin_js: Vanilla ES6 (no build step)
  plugin_css: Plain CSS (WP admin palette)
  skill_format: Markdown (Claude Code skill + standalone prompt.md)
  tour_config: JSON
  wp_minimum: "6.0"
  coding_standard: WordPress PHP Coding Standards (WPCS)
---

# WP Client Tour — Project State

## What exists

- Full plugin: `plugin/wp-client-tour/` — all PHP, JS, CSS complete and E2E tested
- `skill/wp-client-tour.md` — Claude Code skill
- `skill/prompt.md` — LLM-agnostic standalone authoring prompt
- `skill/examples/` — three example tours + multipage example
- `docs/index.html` — GitHub Pages landing page (live)
- `README.md`, `SPEC.md`, `CHANGELOG.md`, `ROADMAP.md`, `LICENSE`
- `plugin/wp-client-tour/readme.txt` — WordPress.org format readme
- `.distignore` — excludes dev files from zip builds

## What is decided

- Stack: PHP 7.4+, Vanilla ES6, plain CSS, JSON tour config, no build step
- No jQuery, no external libraries, no CDN calls
- WordPress Coding Standards (WPCS) for all PHP
- Graceful degradation: missing selectors warn and skip, never throw
- z-index: modal at 10001, targets at 10000, stays below adminbar (99999)
- Open source, MIT license, GitHub: kingsbury-labs/wp-client-tour
- Manual triggers: admin bar Tours menu, [wct_launch] shortcode, wct_tour_launch_url() helper, all in class-manual-trigger.php
- WordPress.org submission name: "Kingsbury Client Tour", requested slug: kingsbury-client-tour, text domain: kingsbury-client-tour
- Author URI changed to https://github.com/robkingsbury (avoids domain verification requirement)
- DNS TXT record `wordpressorg-robkingsbury-verification` added to kingsburycreative.com (WHC cPanel API)

## Recent sessions

### Session 13 (2026-05-31): Reviewer feedback + DNS verification
Renamed to "Kingsbury Client Tour", text domain kingsbury-client-tour throughout. Dashboard widget CSS to wp_add_inline_style(). JS var renamed to wctTourData, REST namespace to wct/v1. External Services + Tested up to 7.0 in readme.txt. Added WordPress.org DNS TXT verification record to kingsburycreative.com via WHC cPanel API. Uploaded zip, replied to reviewer.

### Session 12 (2026-05-29): Reviewer feedback round 1
Received reviewer email requesting slug rename, inline style fix, JS var rename. Applied all fixes, rebuilt zip as wp-client-tour-v2.zip, uploaded to WordPress.org, replied to review email.

### Session 11 (2026-05-06): WordPress.org submission
Fixed all Plugin Check errors. Renamed to "Client Tour". Updated text domain. Fixed GitHub tags. Created release v1.2.2. Submitted — slug client-tour assigned.

## Reference

| File | Purpose |
|------|---------|
| `SPEC.md` | Full technical specification — read before writing any code |
| `CLAUDE.md` | Project rules, build order, WP submission checklist |
| `HANDOFF.md` | Current state and next steps |
| `.claude/rules/development-workflow.md` | Git, commit, WP-specific workflow rules |
| `PLUGIN-CHECK-ISSUES.md` | Plugin Check audit results from session 11 (all resolved) |
