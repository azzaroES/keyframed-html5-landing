/* BOK FOOD · Assembly (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, onFrame, requestFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     Assembly: each [data-reveal] piece composes into place from its side
     (data-from in the CSS) as it rises from the bottom of the screen to
     the middle, scrubbed by the scroll, so it reverses on the way back up.
     On a page that is uncovered from beneath (paging), the pieces land as
     the page is revealed instead. data-reveal-delay holds a piece back a
     step so neighbours land one after another. With motion off every piece
     simply sits in place.
     ---------------------------------------------------------------------- */
  const initAssembly = () => {
    const pieces = Array.from(document.querySelectorAll("[data-reveal]")).map((el) => ({
      el,
      lag: Number(el.dataset.revealDelay || 0) * 0.12,
      flashes: el.hasAttribute("data-flash"),
      written: "",
      landed: false,
      timer: 0,
    }));
    if (!pieces.length) {
      return;
    }
    /* [data-flash] pieces flash once as they land (.is-flash for the CSS),
       and again after scrolling well back out and in. A flash still running
       restarts on the next frame (never by forcing a layout mid-frame). */
    const flash = (piece) => {
      const { el } = piece;
      window.clearTimeout(piece.timer);
      if (el.classList.contains("is-flash")) {
        el.classList.remove("is-flash");
        window.requestAnimationFrame(() => el.classList.add("is-flash"));
      } else {
        el.classList.add("is-flash");
      }
      piece.timer = window.setTimeout(() => el.classList.remove("is-flash"), 1500);
    };

    onFrame({
      read: () => {
        if (prefersReducedMotion()) {
          return null;
        }
        const vh = window.innerHeight;
        return pieces.map((piece) => {
          const paged = window.BOK.pageReveal ? window.BOK.pageReveal(piece.el) : null;
          const linear =
            paged !== null
              ? Math.min(1, Math.max(0, (paged - piece.lag * 0.6) / 0.6))
              : Math.min(1, Math.max(0, (vh - piece.el.getBoundingClientRect().top) / (vh * 0.5) - piece.lag));
          return 1 - Math.pow(1 - linear, 3);
        });
      },
      write: (values) => {
        if (!values) {
          return;
        }
        values.forEach((eased, i) => {
          const piece = pieces[i];
          const value = eased.toFixed(3);
          if (value !== piece.written) {
            piece.el.style.setProperty("--assemble", value);
            piece.written = value;
          }
          if (piece.flashes) {
            if (eased >= 0.98 && !piece.landed) {
              piece.landed = true;
              flash(piece);
            } else if (eased < 0.5) {
              piece.landed = false;
            }
          }
        });
      },
    });

    onMotionChange((event) => {
      if (event.matches) {
        pieces.forEach((piece) => {
          piece.el.style.removeProperty("--assemble");
          piece.written = "";
        });
      } else {
        requestFrame();
      }
    });
  };

  window.BOK.initAssembly = initAssembly;
})();
