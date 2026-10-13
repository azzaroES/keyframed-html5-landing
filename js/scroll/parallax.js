/* BOK FOOD · Parallax (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, onFrame, requestFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     Gentle parallax: while the hero film is not running, its copy and media
     drift at different rates; [data-parallax] elements ease against the
     scroll. Runs in the shared frame loop, transform-only, and off when
     motion is off. Returns remeasure(), for when the page height changes.
     ---------------------------------------------------------------------- */
  const initParallax = () => {
    const hero = document.querySelector(".hero");
    const heroContent = hero && hero.querySelector(".hero__content");
    const heroMedia = hero && hero.querySelector(".film__media");
    const heroScroll = hero && hero.querySelector(".hero__scroll");
    const drifters = Array.from(document.querySelectorAll("[data-parallax]"));
    if (!hero && !drifters.length) {
      return () => {};
    }

    let metrics = [];
    let lastShift = "";

    /* Measure untransformed document positions so the drift never feeds back
       into its own math (rare: on load and resize only). */
    const measure = () => {
      metrics = drifters.map((el) => {
        const previous = el.style.transform;
        el.style.transform = "";
        const box = el.getBoundingClientRect();
        const data = { el, top: box.top + window.scrollY, height: box.height };
        el.style.transform = previous;
        return data;
      });
    };

    const reset = () => {
      [heroContent, heroMedia].concat(drifters).forEach((el) => {
        if (el) {
          el.style.transform = "";
        }
      });
      lastShift = "";
    };

    onFrame({
      read: () => {
        if (prefersReducedMotion()) {
          return null;
        }
        return {
          y: window.scrollY,
          vh: window.innerHeight,
          heroHeight: hero ? hero.offsetHeight : 0,
          running: hero ? hero.classList.contains("has-sequence") : false,
        };
      },
      write: (m) => {
        if (!m) {
          return;
        }
        if (hero) {
          /* Once the hero film pins, the scrub owns its motion; this
             gradient-stage drift only applies to the static fallback. */
          if (!m.running) {
            const shift = Math.min(m.y, m.heroHeight);
            const key = shift.toFixed(1);
            if (key !== lastShift) {
              if (heroContent) {
                heroContent.style.transform = `translate3d(0, ${(shift * 0.16).toFixed(1)}px, 0)`;
              }
              if (heroMedia) {
                heroMedia.style.transform = `translate3d(0, ${(shift * 0.07).toFixed(1)}px, 0)`;
              }
              lastShift = key;
            }
          }
          if (heroScroll) {
            heroScroll.classList.toggle("is-hidden", m.y > 96);
          }
        }
        const viewportMid = m.vh / 2;
        metrics.forEach((item) => {
          const center = item.top - m.y + item.height / 2;
          if (center < -240 || center > m.vh + 240) {
            return;
          }
          const offset = (center - viewportMid) * -0.06;
          item.el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
        });
      },
    });

    const remeasure = () => {
      measure();
      requestFrame();
    };

    window.addEventListener("resize", remeasure);
    window.addEventListener("load", remeasure);
    onMotionChange((event) => {
      if (event.matches) {
        reset();
      } else {
        remeasure();
      }
    });

    measure();
    return remeasure;
  };

  window.BOK.initParallax = initParallax;
})();
