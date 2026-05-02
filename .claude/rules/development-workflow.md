# Development Workflow Rules

**DO NOT REMOVE** - These rules define how we develop and maintain projects together.

## Core Principles

1. **You direct, Claude builds**: You set priorities and make decisions, I implement and maintain
2. **Privacy first**: Never commit PII, credentials, or sensitive data
3. **Test locally**: Check in the actual WP admin before writing selectors or assuming DOM structure
4. **Incremental progress**: Small commits, clear descriptions
5. **Do it right, not right now**: Don't defer work that will need to be done anyway
6. **WordPress standards**: All PHP follows WPCS — nonces, sanitisation, escaping, capability checks

## Branch Strategy

This is an open source project with no CI pipeline yet. Work directly on `main` until a GitHub repo is set up.

Once a remote repo exists:
- `main` - always deployable, tagged releases
- `feature/*`, `fix/*`, `chore/*` - short-lived branches for non-trivial changes

## Working Patterns

### Starting a New Feature

1. Read SPEC.md and any relevant existing files first
2. Check the DOM structure in the actual WP admin before writing selectors
3. Implement incrementally, test in browser
4. Commit with a clear message

### Making Changes

1. Read existing files first (never guess at structure or APIs)
2. Prefer editing over creating new files
3. Keep changes focused on the task
4. No over-engineering or unnecessary refactoring
5. Vanilla JS only — no jQuery, no frameworks, no external dependencies

### When Stuck or Unsure

1. Ask clarifying questions
2. Present options with trade-offs
3. Wait for decision
4. Never make assumptions about preferences

## Commit Messages

Format:
```
Short description of change

- Detail 1
- Detail 2

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

No emojis. No "Generated with Claude Code" footer.

## WordPress-Specific Rules

- **No jQuery** — WordPress loads it but we do not use it
- **No external libraries** — no Shepherd.js, no CDN calls, self-contained renderer only
- **No build step** — plain ES6, readable as-is
- **WPCS compliance** — nonce verification, sanitisation, escaping, capability checks on every admin action
- **Graceful degradation** — if a selector is missing from DOM, `console.warn` and continue; never throw
- **z-index awareness** — `#wpadminbar` is z-index 99999; stay below it

## Destructive Actions

Always require confirmation before:
- Deleting files or directories
- Force pushing to any branch
- Resetting git history
- Bulk updates/deletes

## Before Every Commit

Checklist:
- [ ] No credentials or PII in changed files
- [ ] PHP passes WPCS (nonces, sanitisation, escaping, capability checks)
- [ ] JS is vanilla ES6, no external deps introduced
- [ ] Changes match the task scope
- [ ] Tested in WP admin where applicable
- [ ] Commit message is clear

## Session Handoff Procedure

Before ending any session:

1. Update `.claude/context.md` with what was done and what's next
2. Update `HANDOFF.md` with current status and blockers
3. Commit all changes with a clear message

---

*These rules evolve as we work together. Update as needed.*
