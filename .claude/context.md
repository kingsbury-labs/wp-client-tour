---
project: wp-client-tour
session: 12
last_updated: 2026-05-06
phase: "Submitted to WordPress.org plugin directory. Awaiting review. v1.2.2 tagged and released on GitHub."
continue_with: "Wait for WordPress.org review email. Address any reviewer feedback."
blockers: "None. Awaiting WordPress.org review (285 plugins in queue, 1-14 days)."

tech:
  product_name: WP Client Tour
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
- WordPress.org submission name: "Client Tour", slug: client-tour, text domain: client-tour

## Recent sessions

### Session 11 (2026-05-06): WordPress.org submission
Fixed all Plugin Check errors (output escaping, phpcs:ignore on DB queries). Renamed plugin from "WP Client Tour" to "Client Tour" (WP.org restriction on "wp"/"wordpress" in names). Updated text domain from wp-client-tour to client-tour throughout. Fixed broken GitHub tags (v1.2.2/v1.2.3 deleted, HEAD retagged as v1.2.2). Created GitHub release v1.2.2 with zip. Submitted to wordpress.org/plugins — assigned slug client-tour, awaiting review. Clip-path highlight fix verified in-browser on brauwerk-hoffman.

### Session 10 (2026-05-03): Community health files
Added CONTRIBUTING.md and .github/ISSUE_TEMPLATE/bug_report.md. Community health was 37%. Checked traffic — no external visitors yet, all activity is owner.

### Session 9 (2026-05-03): Demo video, README embed
Processed original screen recording into demo.gif (613KB, 800x592, 15fps) and demo.mp4 (228KB, 1280x952, 30fps, no audio, faststart). Embedded GIF in README between intro and Table of Contents. Committed and pushed all three files.

### Session 8 (2026-05-03): Broken patch attempts, clip-path fix shipped
Multiple broken highlight patches reverted. Clip-path overlay approach implemented — overlay cuts a hole at target bounding rect instead of lifting target z-index.

## Reference

| File | Purpose |
|------|---------|
| `SPEC.md` | Full technical specification — read before writing any code |
| `CLAUDE.md` | Project rules, build order, WP submission checklist |
| `HANDOFF.md` | Current state and next steps |
| `.claude/rules/development-workflow.md` | Git, commit, WP-specific workflow rules |
| `PLUGIN-CHECK-ISSUES.md` | Plugin Check audit results from session 11 (all resolved) |
