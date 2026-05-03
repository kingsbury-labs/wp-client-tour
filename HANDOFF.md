---
project: wp-client-tour
session: 10
last_updated: 2026-05-03
continue_with: "Verify clip-path highlight fix in-browser on both test sites. Then fix the broken GitHub tags."
blockers: "GitHub tags v1.2.2 and v1.2.3 point at broken session-8 commits. HEAD (39ac645) is the correct v1.2.2 but is untagged."
---

# WP Client Tour — Handoff

## Status

Session 9 was housekeeping: demo video processed and embedded in README. The clip-path fix (committed in 39ac645 at end of session 8) has not been browser-tested yet.

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

### Session 9 (2026-05-03): Demo video, README embed
Processed screen recording into demo.gif (613KB) and demo.mp4 (228KB). Embedded GIF in README between intro and Table of Contents. Committed and pushed.

### Session 8 (2026-05-03): Broken patches, clip-path fix
Multiple broken highlight patches reverted. Clip-path overlay approach implemented (39ac645) — overlay cuts a hole at target bounding rect. Not yet browser-tested. GitHub tags v1.2.2/v1.2.3 still point at bad commits.
