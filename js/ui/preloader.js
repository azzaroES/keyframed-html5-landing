/* BOK FOOD · Loading screen (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, getFrameSource } = window.BOK;

  /* -------------------------------------------------------------------------
     Loading screen: the logo fills with colour (--load, 0 to 1) as the hero
     film's frames arrive, and the page opens once enough of them are in
     for the film to start smooth (the rest keep loading behind it) and
     the fonts are ready. A film that is not going to run (motion off,
     Save-Data, no support) leaves only the fonts to wait for. It shows for
     a moment at least, so the fill is seen, and never longer than 12s.
     Runs after the films start (main.js), so it can tell whether they do.
     ---------------------------------------------------------------------- */
  const FRAMES_TO_OPEN = 32;
  const MIN_SHOW = 900;
  const MAX_WAIT = 12000;

  const initPreloader = () => {
    const screen = document.querySelector(".preloader");
    if (!screen) {
      return;
    }
    const count = screen.querySelector(".preloader__count");
    const hero = document.querySelector(".hero");
    const started = performance.now();
    let fontsReady = !document.fonts;
    let opened = false;
    let shown = -1;

    const film =
      hero && (hero.classList.contains("is-loading-sequence") || hero.classList.contains("has-sequence"))
        ? hero
        : null;
    const frameCount = film ? parseInt(film.dataset.frameCount || "0", 10) : 0;
    const source = film
      ? getFrameSource(
          film.dataset.frameBase || "",
          frameCount,
          parseInt(film.dataset.framePad || "3", 10),
          film.dataset.frameExt || ".webp",
        )
      : null;

    const framesIn = () => {
      let ready = 0;
      for (let i = 0; i < frameCount; i += 1) {
        if (source.isReady(i)) {
          ready += 1;
        }
      }
      return ready;
    };

    /* No scrolling the page behind the screen while it loads */
    const holdWheel = (event) => event.preventDefault();
    window.addEventListener("wheel", holdWheel, { passive: false });

    const open = () => {
      if (opened) {
        return;
      }
      opened = true;
      window.removeEventListener("wheel", holdWheel);
      screen.setAttribute("aria-valuenow", "100");
      /* Hold the full colour a beat, then fade the screen open */
      window.setTimeout(
        () => {
          screen.classList.add("is-open");
          screen.setAttribute("aria-hidden", "true");
          if (window.BOK.revealHash) {
            window.BOK.revealHash();
          }
          window.setTimeout(() => {
            screen.hidden = true;
          }, 800);
        },
        prefersReducedMotion() ? 0 : 280,
      );
    };

    const update = () => {
      if (opened) {
        return;
      }
      const filmDone = !source || source.isAborted() || framesIn() >= FRAMES_TO_OPEN;
      const frames = source ? Math.min(1, framesIn() / FRAMES_TO_OPEN) : 1;
      const load = (filmDone ? 1 : frames) * 0.9 + (fontsReady ? 0.1 : 0);
      const percent = Math.round(load * 100);
      if (percent !== shown) {
        shown = percent;
        screen.style.setProperty("--load", load.toFixed(3));
        screen.setAttribute("aria-valuenow", String(percent));
        if (count) {
          count.textContent = `${percent} %`;
        }
      }
      if (filmDone && fontsReady) {
        window.setTimeout(open, Math.max(0, MIN_SHOW - (performance.now() - started)));
      }
    };

    if (source) {
      source.subscribe(update);
    }
    if (document.fonts) {
      document.fonts.ready.then(() => {
        fontsReady = true;
        update();
      });
    }
    window.setTimeout(open, Math.max(0, MAX_WAIT - (performance.now() - started)));
    update();
  };

  window.BOK.initPreloader = initPreloader;
})();
