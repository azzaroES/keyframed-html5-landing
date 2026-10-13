/* BOK FOOD · Strokes (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, onFrame, requestFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     Strokes: the gold underline on each section kicker and the rule beside
     each paragraph draw as they scroll up into place and undraw on the way
     back. A stroke starts when its element's top enters the bottom of the
     viewport and is complete by the upper half; on a page uncovered from
     beneath (paging) it draws as the page is revealed instead. Film
     credits are left out: theirs follow --cast-t. With motion off every
     stroke stays full.
     ---------------------------------------------------------------------- */
  const initStrokes = () => {
    const inks = Array.from(document.querySelectorAll(".eyebrow, .stroke-p"))
      .filter((el) => !el.closest(".film"))
      .map((el) => ({ el, written: "" }));
    if (!inks.length) {
      return;
    }

    onFrame({
      read: () => {
        if (prefersReducedMotion()) {
          return null;
        }
        const vh = window.innerHeight;
        return inks.map((ink) => {
          const paged = window.BOK.pageReveal ? window.BOK.pageReveal(ink.el) : null;
          const linear =
            paged !== null
              ? Math.min(1, Math.max(0, (paged - 0.3) / 0.5))
              : Math.min(1, Math.max(0, (vh * 0.92 - ink.el.getBoundingClientRect().top) / (vh * 0.45)));
          return (1 - Math.pow(1 - linear, 3)).toFixed(3);
        });
      },
      write: (values) => {
        if (!values) {
          return;
        }
        values.forEach((value, i) => {
          if (value !== inks[i].written) {
            inks[i].el.style.setProperty("--ink", value);
            inks[i].written = value;
          }
        });
      },
    });

    onMotionChange((event) => {
      if (event.matches) {
        inks.forEach((ink) => {
          ink.el.style.removeProperty("--ink");
          ink.written = "";
        });
      } else {
        requestFrame();
      }
    });
  };

  window.BOK.initStrokes = initStrokes;
})();
