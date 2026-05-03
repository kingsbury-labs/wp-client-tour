---
project: wp-client-tour
session: 8
last_updated: 2026-05-03
continue_with: "Fix pulse highlight rendering — full problem statement below. Code is in inconsistent state, needs cleanup before any new work."
blockers: "Pulse highlight broken. Code/version/tag state is inconsistent. See State Audit below."
---

# WP Client Tour — Handoff

## Status

Session 8 was a disaster. The pulse highlight problem was not solved. Multiple bad patches were shipped and partially reverted. The repo, tags, and local installs are in an inconsistent state. Read the full State Audit before touching anything.

---

## State Audit (as of end of session 8)

### Git tags on GitHub
| Tag | Commit | CSS state | Status |
|---|---|---|---|
| v1.2.1 | ea87b5b | White outline, no z-index on pulse | Last known good |
| v1.2.2 | e2d4ae1 | + `z-index: 9999 !important` on `.wct-pulse` | Broken — white hole in overlay |
| v1.2.3 | 8229a30 | + blue outline `rgba(34,113,177)` | Partially better but still white hole |
| v1.2.4 | 54dec47 | + `mix-blend-mode: multiply` | Deleted tag, still a commit on main |

### main HEAD (cd53dc4)
- CSS (`tour-client.css`): **reverted to v1.2.1 state** — white outline, `.wct-pulse` has no z-index, no mix-blend-mode
- JS (`tour-client.js`): **reverted to v1.2.1 state** — dead `scrollIntoViewClear` code removed
- Plugin version constant (`wp-client-tour.php`): **still says 1.2.3** — not updated during revert
- CHANGELOG: **still has v1.2.2, v1.2.3, v1.2.4 entries** — not cleaned up

### Local installs (both synced to main HEAD)
- `c:/xampp/htdocs/wordpress-demo/wp-content/plugins/wp-client-tour/` — v1.2.1 CSS, version string 1.2.3
- `c:/xampp/htdocs/brauwerk-hoffman/wp-content/plugins/wp-client-tour/` — same

### Credentials
- wordpress-demo password was accidentally changed and restored during session. Final confirmed password: `demo2026!`
- Credentials file created: `c:/xampp/htdocs/.credentials/wordpress-demo.md`

---

## The Actual Problem (unsolved)

### What should happen
The overlay (`#wct-overlay`, z-index 9998, `background: rgba(0,0,0,0.72)`) dims the entire page. The highlighted target element should appear visually distinct — the user should be able to see clearly which element the tour step is pointing at.

### What happens at v1.2.1
- Target has `.wct-pulse` added — only adds a white outline animation
- No z-index on target, so target sits BELOW the overlay (z-index 9998)
- Outline also sits below overlay — invisible
- For elements in `#wpadminbar` (z-index 99999), they sit ABOVE the overlay naturally — outline visible but bleeds under adminbar if element is flush with top
- For regular page elements: effectively no highlight visible at all

### What the broken patches tried
- v1.2.2: Added `z-index: 9999 !important` to lift target above overlay. This made the outline visible but exposed the element's own white background fully — creating a bright white rectangle punched through the dark overlay. Not a highlight, just a hole.
- v1.2.3: Changed outline color to blue `#2271b1`. Made the outline more visible but didn't address the white background problem.
- v1.2.4: Added `mix-blend-mode: multiply` hoping to darken the element's background via blending. Made it worse visually.

### What the correct fix looks like
The fundamental architectural problem: you cannot simultaneously lift an element above the overlay (to show its outline) AND have the overlay's dimming show through the element's background, using only z-index.

**Recommended approach: CSS clip-path on the overlay**

In JS, after computing the target's bounding rect, set a `clip-path` on `#wct-overlay` that covers the entire viewport EXCEPT the target's rect. The overlay then dims everything outside the target naturally. The target stays in its normal stacking context — no z-index changes needed. The outline (white, original color) is visible because the target is surrounded by the dark overlay.

Implementation sketch in `renderStep()` or a new `updateOverlayClip(target)` function:
```js
function updateOverlayClip( target ) {
    const r   = target.getBoundingClientRect();
    const vw  = window.innerWidth;
    const vh  = window.innerHeight;
    // Polygon covering full viewport with a rectangular hole at the target
    overlay.style.clipPath = [
        'polygon(',
        '0 0,',
        vw + 'px 0,',
        vw + 'px ' + vh + 'px,',
        '0 ' + vh + 'px,',
        '0 0,',                                      // outer rect
        r.left + 'px ' + r.top + 'px,',             // hole: top-left
        r.left + 'px ' + r.bottom + 'px,',          // hole: bottom-left
        r.right + 'px ' + r.bottom + 'px,',         // hole: bottom-right
        r.right + 'px ' + r.top + 'px,',            // hole: top-right
        r.left + 'px ' + r.top + 'px',              // hole: back to top-left
        ')'
    ].join( ' ' );
}
```

Call `updateOverlayClip(target)` after `ensureOverlay()` in `renderStep()`, and also inside the resize handler. Clear `overlay.style.clipPath` in `removeOverlay()`.

With this approach:
- Overlay z-index stays at 9998
- Target stays at its natural z-index
- White outline (original v1.2.1 color) is visible against the dark overlay surrounding the target
- Works for any element at any position including full-width headings and adminbar items
- The adminbar (z-index 99999) stays above everything as before

---

## Cleanup Needed Before New Work

1. Decide whether to keep or delete tags v1.2.2 and v1.2.3 (v1.2.4 tag already deleted)
2. Reset plugin version constant to `1.2.1` in `wp-client-tour.php` to match the code state
3. Remove or mark-as-reverted the v1.2.2/v1.2.3/v1.2.4 CHANGELOG entries
4. Implement the clip-path fix
5. Tag properly once fix is confirmed working

---

## Local environment

- Project root: `c:\xampp\htdocs\wp-client-tour`
- Dev test site: `c:\xampp\htdocs\brauwerk-hoffman`
- Demo test site: `c:\xampp\htdocs\wordpress-demo`
- Plugin path on both: `wp-content/plugins/wp-client-tour/`
- Credentials: `c:\xampp\htdocs\.credentials\wordpress-demo.md`, `c:\xampp\htdocs\.credentials\brauwerk.md`
- GitHub: `https://github.com/kingsbury-labs/wp-client-tour`

## Recent commits
cd53dc4 Revert v1.2.2-v1.2.4: pulse highlight fixes were broken
54dec47 v1.2.4: fix highlighted target appearing as white hole in overlay (BROKEN, tag deleted)
8229a30 v1.2.3: fix pulse outline invisible on light backgrounds
e2d4ae1 v1.2.2: fix highlighted target visibility beneath overlay
ea87b5b v1.2.1: fix overlay dimming z-index, add arrow groundwork
