---
project: wp-client-tour
session: 4
last_updated: 2026-05-02
phase: "Phase 1 complete. E2E tested on brauwerk-hoffman. Phase 2 (multi-page tours) designed and ready to build."
continue_with: "Build Phase 2: multi-page cross-page tour navigation. See HANDOFF.md for full spec and decisions."
blockers: "None."

tech:
  product_name: WP Client Tour
  type: Open source WordPress plugin + Claude Code skill
  plugin_language: PHP 7.4+
  plugin_js: Vanilla ES6 (no build step)
  plugin_css: Plain CSS (WP admin palette)
  skill_format: Markdown (Claude Code skill)
  tour_config: JSON
  wp_minimum: "6.0"
  coding_standard: WordPress PHP Coding Standards (WPCS)
---

# WP Client Tour — Project State

## What exists

- `SPEC.md` — full technical specification
- `CLAUDE.md` — project rules and build context
- `CHANGELOG.md` — version history stub
- `LICENSE` — MIT
- `README.md` — public readme stub
- `skill/wp-client-tour.md` — Claude Code skill for AI-powered tour authoring
- `skill/examples/woocommerce-orders.json` — example tour
- `skill/examples/acf-fields.json` — example tour
- `skill/examples/custom-dashboard.json` — example tour
- `plugin/BUILD_ORDER.md` — build order stub (no PHP/JS/CSS files yet)
- `.claude/` — project scaffold (this session)

## What is decided

- Stack: PHP 7.4+, Vanilla ES6, plain CSS, JSON tour config, no build step
- No jQuery, no external libraries, no CDN calls
- WordPress Coding Standards (WPCS) for all PHP
- Graceful degradation: missing selectors warn and skip, never throw
- z-index: modal at 10001, targets at 10000, stays below adminbar (99999)
- Open source, MIT license
- Two components: WordPress plugin (delivery) + Claude Code skill (authoring)

## What is pending

### Phase 1 — Plugin build (in order)
1. ~~`plugin/wp-client-tour/wp-client-tour.php`~~ — done
2. ~~`plugin/wp-client-tour/includes/class-tour-loader.php`~~ — done
3. ~~`plugin/wp-client-tour/includes/class-tour-renderer.php`~~ — done
4. ~~`plugin/wp-client-tour/assets/tour-client.js`~~ — done
5. ~~`plugin/wp-client-tour/assets/tour-client.css`~~ — done
6. ~~`plugin/wp-client-tour/includes/class-admin-page.php`~~ — done

### Post-MVP
- README.md (written after MVP works end-to-end)
- WordPress.org submission checklist (see CLAUDE.md)

## Recent sessions

### Session 3 (2026-05-02): E2E test + Phase 2 design
E2E test on brauwerk-hoffman succeeded. Four tours created and rendered correctly: bh-welcome (dashboard), bh-back-office, bh-beers, bh-events. All auto_once, all targeting correct pages. Designed Phase 2 (multi-page cross-page tours) via collab plan review (Soren + Atlas + Morgan). Mechanism: URL param handoff (wct_resume + wct_step), not sessionStorage. Key decisions logged in HANDOFF.md. Ready to build.

### Session 2 (2026-05-01): Phase 1 complete + collab-audit fixes
Wrote remaining 3 plugin files: tour-client.js, tour-client.css, class-admin-page.php. Then ran collab-audit (Soren + Atlas + Morgan, 4 HIGH / 13 MEDIUM / 13 LOW findings). Applied all actionable fixes: H1 (test mode wiring), H2 (skip marks dismissed), H3 (Escape key), step-nav refactor (M2/M3/M4 — validSteps array), accessibility (M5 aria-describedby, M6 :focus-visible, M13 aria-live), WP.org blockers (M10 meta key constant `WCT_META_KEY`, M11 tours dir filter, M12 uninstall.php, L1/L2 wp_unslash), shared validation (M9), overlay click block (M7), scroll timing (M8), conditional position:relative (M1), per-action nonces (L5), tour file existence check on REST (L6), activation hook (L7), Created column (L8), resize handler (L10), generic reset message (L11), strip metadata before localize (L14), object cache invalidation (L3). Skipped: L9 (example file not yet written), L12/L13/L15 (audit said accept for v1), UX1 (product call).

### Session 1 (2026-05-01): Plugin build started + scaffold
Created init-project global skill from sembr scaffold pattern. Initialized .claude/ scaffold. Wrote first 3 plugin files: wp-client-tour.php (main file + hooks), class-tour-loader.php (JSON scan, validate, page/role/seen filtering), class-tour-renderer.php (asset enqueue, wp_localize_script, REST endpoint for marking tours complete).

## Reference

| File | Purpose |
|------|---------|
| `SPEC.md` | Full technical specification — read before writing any code |
| `CLAUDE.md` | Project rules, build order, WP submission checklist |
| `HANDOFF.md` | Current state and next steps |
| `.claude/rules/development-workflow.md` | Git, commit, WP-specific workflow rules |
| `plugin/BUILD_ORDER.md` | Plugin build order stub |
