---
project: wp-client-tour
session: 11
last_updated: 2026-05-03
continue_with: "Verify clip-path highlight fix in-browser on both test sites. Then fix broken GitHub tags."
blockers: "GitHub tags v1.2.2 and v1.2.3 point at broken session-8 commits. HEAD (39ac645) is the correct v1.2.2 but is untagged."
---

# WP Client Tour — Handoff

## Status

Session 10 added community health files (CONTRIBUTING.md, bug report issue template). The clip-path fix (39ac645) is still unverified in-browser.

---

## Priority: Verify Clip-Path Fix

The clip-path overlay approach was implemented in commit `39ac645` but never confirmed working in a real browser. Before tagging or releasing:

1. Sync both local installs to HEAD
2. Log into wordpress-demo or brauwerk-hoffman as a user with a tour eligible
3. Confirm the target element is surrounded by dark overlay (not a white hole)
4. Confirm pulse outline is visible
5. Test on an adminbar element, a page-body element, and a full-width heading if possible

Credentials: `c:/xampp/htdocs/.credentials/wordpress-demo.md` (password: `demo2026!`)

---

## Tag Cleanup Needed

GitHub tags `v1.2.2` and `v1.2.3` point at broken session-8 commits. The correct approach once the fix is verified:

```bash
# Delete the bad remote tags
git push origin :refs/tags/v1.2.2
git push origin :refs/tags/v1.2.3

# Tag HEAD as v1.2.2
git tag v1.2.2 39ac645
git push origin v1.2.2
```

Then create a GitHub release for v1.2.2.

---

## What Exists

- Full plugin: `plugin/wp-client-tour/` — PHP, JS, CSS complete and E2E tested
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

### Session 10 (2026-05-03): Community health files
Added CONTRIBUTING.md (stack constraints, PR workflow) and .github/ISSUE_TEMPLATE/bug_report.md. Community health score was 37% — both files improve it. No traffic from posts yet (all uniques=1, only May 2 activity).

### Session 9 (2026-05-03): Demo video, README embed
Processed screen recording into demo.gif (613KB) and demo.mp4 (228KB). Embedded GIF in README between intro and Table of Contents. Committed and pushed.
