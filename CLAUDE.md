# CLAUDE.md

Guidance for Claude working in this repository. `README.md` maps the files and explains how things work; this file holds the rules and the owner's preferences.

## The project

A one-page site for BOK FOOD, a fried-chicken shop at C/ Migdia 45, Tortosa (Tarragona). All copy is Spanish (Spain usage, prices in €). Plain HTML5, CSS and vanilla JavaScript: no framework, no build step, no dependencies. Scripts are classic deferred files sharing `window.BOK` (no ES modules), so the page also works opened from disk.

Preview with `node tools/serve.mjs` (http://localhost:8080). A `package.json` in a parent folder sets `"type": "module"`, so any Node script added here must be `.mjs`.

## House rules (also in `.agents/rules/`)

- Every colour is a CSS variable from `css/tokens.css`; no raw hex or rgb values anywhere else.
- Semantic HTML5: landmarks, headings in order, real buttons and links.
- Spacing sits on the 8px grid (`--space-*` tokens).
- Every animation respects `prefers-reduced-motion` and the accessibility panel's "Detener animaciones" switch (`.a11y-still`). With JavaScript off, everything is visible and usable.

## The owner's taste (stated firmly; do not drift from it)

- No card, panel or box containers around floating text ("AI cards"). Text floats on the imagery.
- No glass effect on text. Film credit titles are solid ivory with a plain static shadow.
- Decorative strokes go on kickers (underline) and paragraphs (side rule), never on titles.
- Type is big and clearly visible: bold readable titles (Archivo 800), light spacious paragraphs (Lexend).
- Floating controls are icon-first, with names only in hover or focus tooltips.
- WhatsApp: the gold button bottom left never turns into an X and has no container; the two round initial profiles (BF, DW) rise straight up out of it.
- Every section uncovers the next like turning pages: hero film, Pide ahora, pillars, menu film, club, then the footer, which waits fixed in place and is revealed as the club lifts off.
- Use the official logo, `assets/logo/bok-food-logo.webp`, whole: never crop it or cut a logo out of the film frames.

## Performance rules (the owner tests on a phone, often in a short viewer)

- Scroll effects never add their own `scroll` listener: register with `BOK.onFrame({ read, write })` (`js/core.js`). Reads measure, writes change styles, and a write only touches what changed.
- Anything that changes during scroll or animates animates `transform` or `opacity` only, on an element with `will-change` where it changes every frame. Never animate `filter`, `color`, `text-shadow`, `box-shadow` or backgrounds for an effect: draw the glow once and fade it.
- Keep the film pipeline in `js/films/frames.js` and `js/films/film.js` as it is: decoded bitmap slices on phones, a blend between the two nearest frames, an opaque canvas.
- Every paged section must fit a 360×560 screen, or it becomes `.is-tall` and stops being uncovered. Check new content at 360×560, 390×844 and 1440×900.
- In-page links go through `js/scroll/paging.js`, which scrolls to where a page is fully uncovered. Link to a section `id` as usual.

## Gotchas met so far

- Phone `@media` blocks must come after the base rules they override in the same file, or they silently lose (the footer once grew to 837px this way).
- Windows PowerShell's `Set-Content -Encoding utf8` writes a byte-order mark; write files without one.
- The heavier checks used for this site ran in a separate headless Chrome driven over the DevTools protocol, never the owner's own browser window.
