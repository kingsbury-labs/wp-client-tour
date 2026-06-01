---
project: wp-client-tour
session: 13
last_updated: 2026-05-31
continue_with: "Wait for WordPress.org to confirm domain verification and approve slug change to kingsbury-client-tour."
blockers: "None. DNS TXT record added and reply sent. Awaiting reviewer response."
---

# WP Client Tour — Handoff

## Status

Plugin under active review at WordPress.org. Slug currently `client-tour`, slug change to `kingsbury-client-tour` requested. Domain verification (DNS TXT) completed. Updated zip `wp-client-tour-v2.zip` uploaded.

GitHub release v1.2.2 is live: https://github.com/kingsbury-labs/wp-client-tour/releases/tag/v1.2.2

---

## Rebuild the Zip

```bash
cd c:/xampp/htdocs/wp-client-tour
python3 -c "
import zipfile, os
plugin_src = 'plugin/wp-client-tour'
exclude = {'tours/.gitkeep', 'spacely-installer.php'}
with zipfile.ZipFile('wp-client-tour-v2.zip', 'w', zipfile.ZIP_DEFLATED) as zf:
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
- `wp-client-tour-v2.zip` — current submission zip (gitignored)
- `skill/wp-client-tour.md` — Claude Code skill
- `skill/prompt.md` — LLM-agnostic standalone authoring prompt
- `skill/examples/` — three example tours + multipage example
- `docs/index.html` — GitHub Pages landing page (live)
- `README.md`, `SPEC.md`, `CHANGELOG.md`, `ROADMAP.md`, `LICENSE`

---

## Local Environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- Dev test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Demo test site: `c:\xampp\htdocs\wordpress-demo`
- Plugin path on both: `wp-content/plugins/wp-client-tour/`
- Credentials: `c:/xampp/htdocs/.credentials/wordpress-demo.md`, `c:/xampp/htdocs/.credentials/brauwerk.md`
- GitHub: `https://github.com/kingsbury-labs/wp-client-tour`
- DNS managed via WHC cPanel API (`c:/xampp/htdocs/.credentials/whc-hosting.md`)

---

## Session Summaries

### Session 13 (2026-05-31): Reviewer feedback round 2 + DNS verification
Addressed all reviewer feedback: renamed to "Kingsbury Client Tour" (slug: kingsbury-client-tour), text domain updated throughout, dashboard widget CSS converted to wp_add_inline_style(), JS variable renamed to wctTourData, REST namespace changed to wct/v1, External Services section added to readme.txt, Tested up to updated to 7.0. Added DNS TXT record `wordpressorg-robkingsbury-verification` to kingsburycreative.com via WHC cPanel API. Replied to reviewer. Awaiting approval.

### Session 11 (2026-05-06): WordPress.org submission
Fixed all Plugin Check errors. Renamed plugin to "Client Tour". Updated text domain to client-tour throughout. Fixed broken GitHub tags. Created GitHub release v1.2.2. Submitted to WordPress.org — slug client-tour assigned.
