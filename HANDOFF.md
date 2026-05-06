---
project: wp-client-tour
session: 12
last_updated: 2026-05-06
continue_with: "Wait for WordPress.org review email. Address any reviewer feedback."
blockers: "None. Plugin is submitted and awaiting review."
---

# WP Client Tour — Handoff

## Status

Plugin submitted to WordPress.org on 2026-05-06. Slug assigned: `client-tour`. Awaiting manual review (285 plugins in queue, 1-14 days).

GitHub release v1.2.2 is live: https://github.com/kingsbury-labs/wp-client-tour/releases/tag/v1.2.2

---

## If the Reviewer Requests Changes

Common reviewer requests and how to handle them:

- **Security issue** — fix the flagged code, rebuild the zip, upload via the "Upload updated plugin" button on the submission page
- **Slug conflict** — if `client-tour` is taken, choose a new slug (e.g. `guided-client-tour`, `wct-guided-tours`) and contact WordPress.org before they close the ticket
- **Readme issues** — edit `plugin/wp-client-tour/readme.txt`, commit, rebuild zip, reupload

To rebuild the zip after any changes:
```bash
cd c:/xampp/htdocs/wp-client-tour
python3 -c "
import zipfile, os
plugin_src = 'plugin/wp-client-tour'
exclude = {'tours/.gitkeep', 'spacely-installer.php'}
with zipfile.ZipFile('wp-client-tour.zip', 'w', zipfile.ZIP_DEFLATED) as zf:
    for root, dirs, files in os.walk(plugin_src):
        dirs[:] = [d for d in dirs if not d.startswith('.')]
        for f in files:
            full = os.path.join(root, f)
            rel = os.path.relpath(full, plugin_src).replace(os.sep, '/')
            if rel not in exclude:
                zf.write(full, 'wp-client-tour/' + rel)
"
```

---

## What Exists

- Full plugin: `plugin/wp-client-tour/` — PHP, JS, CSS complete and E2E tested
- `plugin/wp-client-tour/readme.txt` — WordPress.org format readme
- `.distignore` — documents what to exclude from zip builds
- `wp-client-tour.zip` — current submission zip (gitignored)
- `skill/wp-client-tour.md` — Claude Code skill
- `skill/prompt.md` — LLM-agnostic standalone authoring prompt
- `skill/examples/` — three example tours + multipage example
- `docs/index.html` — GitHub Pages landing page (live)
- `demo.gif`, `demo.mp4` — screen recording of plugin in action
- `README.md`, `SPEC.md`, `CHANGELOG.md`, `ROADMAP.md`, `LICENSE`

---

## Local Environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- Dev test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Demo test site: `c:\xampp\htdocs\wordpress-demo`
- Plugin path on both: `wp-content/plugins/wp-client-tour/`
- Credentials: `c:/xampp/htdocs/.credentials/wordpress-demo.md`, `c:/xampp/htdocs/.credentials/brauwerk.md`
- GitHub: `https://github.com/kingsbury-labs/wp-client-tour`

---

## Session Summaries

### Session 11 (2026-05-06): WordPress.org submission
Fixed all Plugin Check errors (inline esc_url/esc_html in printf, NoCaching phpcs:ignore on bulk DB deletes). Renamed plugin to "Client Tour" — WordPress.org rejects "wp" and "wordpress" in plugin names. Updated text domain from wp-client-tour to client-tour throughout all PHP files. Fixed broken GitHub tags (deleted bad v1.2.2/v1.2.3, retagged HEAD as v1.2.2). Created GitHub release v1.2.2 with zip attached. Submitted to WordPress.org — slug client-tour assigned. Clip-path highlight fix verified in-browser on brauwerk-hoffman.

### Session 10 (2026-05-03): Community health files
Added CONTRIBUTING.md (stack constraints, PR workflow) and .github/ISSUE_TEMPLATE/bug_report.md. Community health score was 37% — both files improve it.

### Session 9 (2026-05-03): Demo video, README embed
Processed screen recording into demo.gif (613KB) and demo.mp4 (228KB). Embedded GIF in README between intro and Table of Contents. Committed and pushed.
