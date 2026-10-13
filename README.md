# BOK FOOD Â· web

One-page site for BOK FOOD (C/ Migdia 45, Tortosa): a keyframed HTML5 landing where the scroll scrubs a film frame by frame and every section uncovers the next. Plain HTML5, CSS and JavaScript: no framework, no build step, no dependencies.

## Run it

```sh
node tools/serve.mjs       # then open http://localhost:8080
```

Any static server works too, and `index.html` also opens straight from disk (the films then load their frames through a slower path). The original renders in `assets/source/` are not in the repository (24 MB); they are only needed to re-export the frames.

## Layout

```text
index.html              the page: semantic sections, in order
assets/
  frames/               frame_000â€“099.webp, the film both scroll animations play
  logo/                 bok-food-logo.webp, the official logo (loading screen, end of the hero)
  source/               original renders (zip), kept locally for re-exporting (not in git)
tools/serve.mjs         local preview server (node, no dependencies)
.agents/rules/          the project's house rules (colours, HTML, spacing, motion)
css/                    linked from index.html in this order (it is the cascade)
  tokens.css            every colour, font, size, space and timing value
  base.css              reset, body, headings
  components.css        buttons, tooltip (.tip), kicker and paragraph strokes
  themes.css            dark section themes and the light theme
  effects/assembly.css  pieces sliding in from the sides on scroll
  sections/             one file per page section, top to bottom
    header.css  films.css  order.css  pillars.css  menu.css  footer.css  club.css
  ui/                   floating controls
    a11y-panel.css  whatsapp.css  fab-nav.css  back-to-top.css
  effects/paging.css    every section uncovered from beneath the one before, like turning pages
  ui/preloader.css      the loading screen: the logo fills with colour as the film loads
  motion.css            reduced motion and "Detener animaciones" (always last)
js/                     loaded with defer, in this order
  core.js               shared state on window.BOK: accessibility settings, motion switch,
                        and the one frame loop every scroll effect runs in
  films/frames.js       loads the frames once for both films (kept decoded on phones)
  films/film.js         the scroll-scrubbed films, their credits and veil
  scroll/               assembly, parallax, strokes, paging (which also steers in-page links)
  ui/                   back-to-top, accessibility panel, menu FAB, WhatsApp
  signup.js             club email form (no backend yet)
  ui/preloader.js       the loading screen's progress and opening
  main.js               starts everything, always last
```

## Common changes

| To change | Edit |
| --- | --- |
| Colours, fonts, sizes | `css/tokens.css` (every colour is a token there) |
| Section copy, prices, links | `index.html` |
| When a film credit appears | `data-cast-start` / `data-cast-end` on its `<li>` in `index.html` (a share of that film's scroll, 0 to 1) |
| The film footage | replace `assets/frames/`, then update `data-frame-count` and the other `data-frame-*` attributes on each `.film` |
| WhatsApp numbers | the two `wa.me` links at the end of `index.html` (BOK FOOD +34 642 79 28 79, developer +34 642 099 257) |
| How long each film scrolls | `--scrub-hero` and `--scrub-menu` in `css/tokens.css` |
| The logo | replace `assets/logo/bok-food-logo.webp` (square, transparent background) |
| Where "CÃ³mo llegar" goes | the Google Maps link on the logo at the end of the hero in `index.html` |
| How long the loading screen waits | `FRAMES_TO_OPEN`, `MIN_SHOW` and `MAX_WAIT` in `js/ui/preloader.js` |
| In-page links | link to a section's `id` as usual; `js/scroll/paging.js` takes the page to where that section is fully uncovered |

## Rules this project keeps

- Every colour is a CSS variable from `css/tokens.css`.
- Spacing sits on the 8px grid (`--space-*` tokens).
- Semantic HTML5 landmarks and headings.
- Every animation respects `prefers-reduced-motion` and the accessibility panel's "Detener animaciones" switch. With JavaScript off, everything is visible and the forms still work.

## Keeping it smooth on phones

- Scroll effects never listen to `scroll` themselves: they register with `BOK.onFrame({ read, write })` in `js/core.js`. Each frame runs every `read` (layout measurements) first, then every `write` (style changes), so the page is laid out once per frame, and a write only touches what changed.
- Anything that changes every frame of a scroll animates `transform` or `opacity` only, on an element with `will-change`, so it moves without repainting.
- On phones held upright, the films keep each frame decoded as a bitmap cut to the part of the frame the screen shows (all 100 frames, within a 160 MB budget). Without that, the browser decodes a full 1920Ã—1080 frame again for almost every frame it draws while scrolling. Over http the frames are fetched and decoded off the main thread; opened from disk, or if the host blocks `fetch`, they load as images and become bitmaps in idle time.
- The films blend between the two nearest frames, so the picture moves continuously instead of stepping.
- Pages waiting under the one above are left out of the drawing (`.is-covered`) until half a screen before their turn; otherwise the phone composites every page of the site on every frame.
- Every page must fit a short phone screen (about 560px tall) to be uncovered whole; a taller one (`.is-tall`) just scrolls in. Check new content at 360Ã—560.
