---
project: wp-client-tour
session: 10
last_updated: 2026-05-03
phase: "v1.2.2 stable. Clip-path overlay fix shipped. Demo GIF/MP4 added to repo and README."
continue_with: "Verify clip-path highlight fix in-browser on both test sites. Clean up broken session 8 tags on GitHub (v1.2.2/v1.2.3 point at broken commits). Consider tagging HEAD as v1.2.2 properly."
blockers: "GitHub tags v1.2.2 and v1.2.3 still point at broken session-8 commits. New v1.2.2 commit (39ac645) is live on main but untagged."

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

## What is decided

- Stack: PHP 7.4+, Vanilla ES6, plain CSS, JSON tour config, no build step
- No jQuery, no external libraries, no CDN calls
- WordPress Coding Standards (WPCS) for all PHP
- Graceful degradation: missing selectors warn and skip, never throw
- z-index: modal at 10001, targets at 10000, stays below adminbar (99999)
- Open source, MIT license, GitHub: kingsbury-labs/wp-client-tour
- Manual triggers: admin bar Tours menu, [wct_launch] shortcode, wct_tour_launch_url() helper, all in class-manual-trigger.php

## Recent sessions

### Session 9 (2026-05-03): Demo video, README embed
Processed original screen recording into demo.gif (613KB, 800x592, 15fps) and demo.mp4 (228KB, 1280x952, 30fps, no audio, faststart). Embedded GIF in README between intro and Table of Contents. Committed and pushed all three files.

### Session 8 (2026-05-03): Broken patch attempts, clip-path fix shipped
Multiple broken highlight patches (v1.2.2-v1.2.4) reverted. Clip-path overlay approach implemented in commit 39ac645 as new v1.2.2 — overlay cuts a hole at target bounding rect instead of lifting target z-index. GitHub tags v1.2.2/v1.2.3 still point at broken session-8 commits and need cleanup.

### Session 7 (2026-05-02): v1.2.1 overlay/z-index fix
Fixed overlay dimming, moved from box-shadow to #wct-overlay background. Added modal arrow groundwork. Tagged v1.2.1, synced both local installs.

## Reference

| File | Purpose |
|------|---------|
| `SPEC.md` | Full technical specification — read before writing any code |
| `CLAUDE.md` | Project rules, build order, WP submission checklist |
| `HANDOFF.md` | Current state and next steps |
| `.claude/rules/development-workflow.md` | Git, commit, WP-specific workflow rules |
