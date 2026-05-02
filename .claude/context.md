---
project: wp-client-tour
session: 6
last_updated: 2026-05-02
phase: "v1.1.0 shipped. Landing page live. v1.2.0 (manual triggers) is next — core mechanism already built."
continue_with: "Build v1.2.0 manual triggers: helper function, shortcode, admin bar hook, flip manual trigger tours from skipped to live. See HANDOFF.md."
blockers: "None."

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
- `skill/prompt.md` — LLM-agnostic standalone authoring prompt (new this session)
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
- wct_force URL param is the manual trigger mechanism (already in loader)

## Recent sessions

### Session 5 (2026-05-02): Landing page, dashboard widget, update checker, LLM-agnostic prompt, mobile audit
Built GitHub Pages landing page with animated WP admin demo. Added dashboard widget (toggle in settings, lists tours with completion state and Start/Replay buttons). Added GitHub update checker (12h transient, admin notice on plugins screen). Added wct_force loader branch for dashboard widget launch. Added skill/prompt.md for non-Claude LLMs. Full mobile audit and fixes (overflow, padding, schema grid). Content audit — removed Claude-specific language throughout.

### Session 4 (2026-05-02): Phase 2 (multi-page tours) built + v1.1.0 released
Built full multi-page tour system: URL param handoff (wct_resume + wct_step), dual-list step model, global step counter, cross-page Back, navigate_on_next / navigate_label / per-step target_page JSON fields, pulse animation. E2E tested on brauwerk-hoffman. Tagged v1.1.0, created GitHub release, updated README and CHANGELOG.

### Session 3 (2026-05-02): E2E test + Phase 2 design
E2E test on brauwerk-hoffman succeeded. Four tours rendered correctly. Designed Phase 2 via collab plan review. Mechanism: URL param handoff. Key decisions locked in HANDOFF.md.

## Reference

| File | Purpose |
|------|---------|
| `SPEC.md` | Full technical specification — read before writing any code |
| `CLAUDE.md` | Project rules, build order, WP submission checklist |
| `HANDOFF.md` | Current state and next steps |
| `.claude/rules/development-workflow.md` | Git, commit, WP-specific workflow rules |
