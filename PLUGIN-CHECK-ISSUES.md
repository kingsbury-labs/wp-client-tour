# WordPress.org Plugin Check Issues

Run date: 2026-05-03
Tool: Plugin Check (PCP) — all categories checked

These must be resolved before submitting to the WordPress.org plugin repository.

---

## ERRORS (submission blockers)

### 1. Hidden file in tours/ directory
**File:** `plugin/wp-client-tour/tours/.gitkeep`
**Issue:** Hidden files are not permitted in WordPress.org plugins.
**Fix:** Delete `tours/.gitkeep` from the plugin zip before submission. The `tours/` directory is created by the activation hook anyway, so the file serves no purpose in the distributed plugin. Keep it in the source repo if needed for Git, but exclude it from the zip via `.distignore` or the build script.

### 2. Unescaped output in class-dashboard-widget.php
**File:** `plugin/wp-client-tour/includes/class-dashboard-widget.php`
**Lines:** 87 (`$checkmark`), 88 (`$label`), 92 (`$launch_url`)
**Issue:** `WordPress.Security.EscapeOutput.OutputNotEscaped` — all output must be run through an escaping function before being echoed.
**Fix:**
- `$checkmark` — wrap with `esc_html()`
- `$label` — wrap with `esc_html()`
- `$launch_url` — wrap with `esc_url()`

### 3. Unescaped output in class-update-checker.php
**File:** `plugin/wp-client-tour/includes/class-update-checker.php`
**Line:** 49 (`$url`)
**Issue:** `WordPress.Security.EscapeOutput.OutputNotEscaped`
**Fix:** Wrap `$url` with `esc_url()` at point of output.

### 4. readme.txt not found in installed plugin
**File:** `plugin/wp-client-tour/readme.txt`
**Issue:** Plugin Check couldn't find readme.txt because it wasn't copied into the demo site's installed plugin. The file exists in the repo now (written this session) but the installed copy in `wp-content/plugins/wp-client-tour/` doesn't have it yet.
**Fix:** Copy `plugin/wp-client-tour/readme.txt` into the demo site plugin folder, OR just ensure the build/zip process always includes it. Verify by re-running Plugin Check after copying.

---

## WARNINGS (not hard blockers but fix before submission)

### 5. Plugin name and slug contain restricted term "wp" -- DECISION MADE
**File:** `plugin/wp-client-tour/wp-client-tour.php`
**Issue:** WordPress.org does not allow "wp" in plugin names or slugs. "WP Client Tour" and slug "wp-client-tour" will both be flagged.
**Decision:** Keep "WP Client Tour" as the public brand everywhere (GitHub, DEV.to, etc.) since it's already out there. Submit to WordPress.org under a modified name/slug that references the brand without leading with "wp".
**Recommended approach:**
- **Plugin Name header:** `Client Tour -- WP Client Tour`
- **Slug:** `client-tour-wct`
This keeps the public brand intact while satisfying WordPress.org's restriction. Update only the Plugin Name header in `wp-client-tour.php` and the readme.txt stable tag slug. Do not rename the repo, folder, or any public-facing links.

### 6. spacely-installer.php should not be in the plugin zip
**File:** `plugin/wp-client-tour/spacely-installer.php` (also present in demo site plugin folder)
**Issue:** This is a dev utility written into the plugin folder during demo setup. It has a `wp_redirect()` (should be `wp_safe_redirect()`) and missing nonce verification.
**Fix:** Delete `spacely-installer.php` from the plugin folder entirely. It has no place in the distributed plugin.

### 7. Direct DB queries without caching
**Files:**
- `includes/class-admin-page.php` line 250
- `uninstall.php` line 11
**Issue:** `WordPress.DB.DirectDatabaseQuery.NoCaching` — direct DB calls should use wp_cache_get/set.
**Fix:** These are bulk delete operations that run rarely (reset all users, uninstall). Caching isn't meaningful here, but the checker flags them. Add a `// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk delete, caching not applicable` comment on each line.

---

## Build process recommendation

Before the next submission attempt, add a `.distignore` file to exclude dev-only files from the zip:

```
.git
.gitignore
.distignore
tours/.gitkeep
spacely-installer.php
audit-report.md
PLUGIN-CHECK-ISSUES.md
scroll-analysis-output/
skill/
docs/
demo.gif
demo.mp4
*.mp4
```

Then rebuild the zip and re-run Plugin Check clean.
