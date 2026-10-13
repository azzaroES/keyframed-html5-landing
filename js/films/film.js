/* BOK FOOD · Films (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, getFrameSource, isTouch, onFrame, requestFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     FILMS — scroll-driven scrubs
     -------------------------------------------------------------------------
     Each .film scrubs the frame set declared on it (data-frame-*) through its
     .film__canvas, forwards or, with data-frame-reverse, backwards. Once the
     film's first frame decodes, .has-sequence stretches the section into a
     scroll track (the CSS), the stage pins, and scroll progress maps to a
     frame drawn cover-cropped at device pixel ratio (capped 2x, 1.5x on
     touch screens); the nearest loaded frame stands in for any still on its
     way. The same progress plays the credits ([data-cast-start/end] items)
     by writing --cast-t, which moves them on transform and opacity only.
     While the first frame loads, .is-loading-sequence keeps the credits out
     of flow so the static layout never flashes; past 8s the static layout
     shows until the frame arrives.
     A film never starts (staying one viewport tall with static credits)
     without JS, with motion off or Save-Data, or if loading fails, and it
     unwinds live when motion is switched off. onLayoutChange tells the
     parallax module the document height changed.
     ---------------------------------------------------------------------- */
  const initFilm = (film, onLayoutChange) => {
    const relayout = typeof onLayoutChange === "function" ? onLayoutChange : () => {};
    const stage = film.querySelector(".film__stage");
    const mount = film.querySelector(".film__media");
    const canvas = film.querySelector(".film__canvas");
    const title = film.querySelector(".film__title");
    const veil = film.querySelector(".film__veil");
    if (!stage || !mount || !canvas || typeof canvas.getContext !== "function") {
      return;
    }
    /* Opaque: every frame covers the whole canvas, and an opaque layer lets
       the browser skip drawing whatever sits behind it */
    const context = canvas.getContext("2d", { alpha: false });
    if (!context) {
      return;
    }

    const count = parseInt(film.dataset.frameCount || "", 10);
    const base = film.dataset.frameBase || "";
    const pad = parseInt(film.dataset.framePad || "3", 10);
    const ext = film.dataset.frameExt || ".webp";
    if (base === "" || !Number.isFinite(count) || count < 2) {
      return;
    }
    const reverse = film.hasAttribute("data-frame-reverse");
    const firstFrame = reverse ? count - 1 : 0;
    const source = getFrameSource(base, count, pad, ext);

    const cast = Array.from(film.querySelectorAll("[data-cast-start]"))
      .map((el) => ({
        el,
        start: parseFloat(el.dataset.castStart),
        end: parseFloat(el.dataset.castEnd),
        t: -1,
      }))
      .filter((credit) => Number.isFinite(credit.start) && credit.end > credit.start);

    let active = false;
    let failed = false;
    let drawnKey = "";
    let cssWidth = 0;
    let cssHeight = 0;
    /* Last values written, so a frame only touches what changed */
    let lastSpot = "";
    let lastTitle = "";

    /* overflow: clip mirrors the @supports gate on the pinned track in
       the CSS: without it the sticky stage cannot pin. */
    const sequenceAllowed = () =>
      !failed &&
      !source.isAborted() &&
      !prefersReducedMotion() &&
      Boolean(window.CSS && CSS.supports("overflow", "clip")) &&
      !(navigator.connection && navigator.connection.saveData);

    /* 0 at the top of the track, 1 when the stage unpins. A film tucked
       under the page before it (data-uncover, see the paging CSS) spends
       its first screen being uncovered, so the scrub starts after it. */
    const uncovers = film.hasAttribute("data-uncover");
    const progress = (rect = film.getBoundingClientRect()) => {
      const lead = uncovers && active ? stage.offsetHeight : 0;
      const travel = film.offsetHeight - stage.offsetHeight - lead;
      if (travel <= 0) {
        return 0;
      }
      return Math.min(1, Math.max(0, (-rect.top - lead) / travel));
    };

    const paint = (index, alpha) => {
      const image = source.image(index);
      const imageWidth = image.naturalWidth || image.width;
      const imageHeight = image.naturalHeight || image.height;
      const scale = Math.max(cssWidth / imageWidth, cssHeight / imageHeight);
      const width = imageWidth * scale;
      const height = imageHeight * scale;
      context.globalAlpha = alpha;
      context.drawImage(image, (cssWidth - width) / 2, (cssHeight - height) / 2, width, height);
    };

    /* The scroll lands between two frames: draw the earlier one and lay
       the later one over it, as opaque as the scroll is close to it, so the
       picture moves continuously instead of stepping from frame to frame
       (and, while frames are still loading, blends across the gap) */
    const BLEND_STEPS = 24;
    const drawFrame = (force, p) => {
      if (!active) {
        return;
      }
      const exact = (reverse ? 1 - p : p) * (count - 1);
      let from = source.readyAtOrBelow(Math.floor(exact));
      let to = source.readyAtOrAbove(Math.ceil(exact));
      if (from === -1 && to === -1) {
        return;
      }
      if (from === -1) {
        from = to;
      }
      if (to === -1) {
        to = from;
      }
      const mix = to === from ? 0 : Math.round(((exact - from) / (to - from)) * BLEND_STEPS) / BLEND_STEPS;
      const key = `${from}:${to}:${mix}`;
      if (key === drawnKey && !force) {
        return;
      }
      paint(mix < 1 ? from : to, 1);
      if (mix > 0 && mix < 1) {
        paint(to, mix);
      }
      context.globalAlpha = 1;
      drawnKey = key;
    };

    /* A veil, if the film has one, opens its spotlight over the first
       stretch of the scrub; the title card bows out just behind it; each
       credit then fades, slides and strokes in and out across its own
       window, and one that ends at 1 holds to the end. */
    const SPOT_SPAN = 0.07;
    const TITLE_START = 0.03;
    const TITLE_SPAN = 0.06;
    const CAST_FADE = 0.28; /* share of a credit's window spent fading each way */
    const styleCopy = (p) => {
      if (veil) {
        if (active) {
          const open = Math.min(1, p / SPOT_SPAN);
          const eased = (open < 0.5 ? 2 * open * open : 1 - Math.pow(-2 * open + 2, 2) / 2).toFixed(3);
          if (eased !== lastSpot) {
            veil.style.setProperty("--spot", eased);
            lastSpot = eased;
          }
        } else {
          veil.style.removeProperty("--spot");
          lastSpot = "";
        }
      }
      if (title) {
        if (active) {
          const exit = Math.min(1, Math.max(0, (p - TITLE_START) / TITLE_SPAN));
          const key = exit.toFixed(3);
          if (key !== lastTitle) {
            title.style.opacity = (1 - exit).toFixed(3);
            title.style.transform = `translate3d(0, ${(exit * -48).toFixed(1)}px, 0)`;
            lastTitle = key;
          }
        } else {
          title.style.opacity = "";
          title.style.transform = "";
          lastTitle = "";
        }
      }
      cast.forEach((credit) => {
        const style = credit.el.style;
        if (!active) {
          style.removeProperty("--cast-t");
          style.removeProperty("visibility");
          credit.t = -1;
          return;
        }
        const local = (p - credit.start) / (credit.end - credit.start);
        const fadeIn = local / CAST_FADE;
        const fadeOut = credit.end >= 1 ? 1 : (1 - local) / CAST_FADE;
        const linear = Math.min(1, Math.max(0, Math.min(fadeIn, fadeOut)));
        const t = Math.round(linear * linear * (3 - 2 * linear) * 1000) / 1000;
        if (t !== credit.t) {
          style.setProperty("--cast-t", String(t));
          style.visibility = t > 0 ? "" : "hidden";
          credit.t = t;
        }
      });
    };

    /* In the shared frame loop: measure where the film is (one layout read),
       then draw and restyle. A film well off screen is left alone. */
    onFrame({
      read: () => {
        if (!active) {
          return null;
        }
        const rect = film.getBoundingClientRect();
        const vh = window.innerHeight;
        if (rect.bottom < -vh || rect.top > vh * 2) {
          return null;
        }
        return progress(rect);
      },
      write: (p) => {
        if (p === null || p === undefined) {
          return;
        }
        drawFrame(false, p);
        styleCopy(p);
      },
    });
    const queueScrub = requestFrame;

    /* Touch screens draw at 1.5x: sharp enough on a phone, and a third
       fewer pixels to fill on every frame than 2x */
    const MAX_SCALE = isTouch ? 1.5 : 2;
    const fit = () => {
      const box = mount.getBoundingClientRect();
      const scale = Math.min(window.devicePixelRatio || 1, MAX_SCALE);
      cssWidth = box.width;
      cssHeight = box.height;
      canvas.width = Math.max(1, Math.round(box.width * scale));
      canvas.height = Math.max(1, Math.round(box.height * scale));
      context.setTransform(scale, 0, 0, scale, 0, 0);
      /* Only a running film's canvas is the pinned stage's size */
      if (active) {
        source.fitTo(cssWidth, cssHeight, scale);
      }
      drawnKey = "";
      /* A resize or rotation changes the track's travel, so progress()
         moved: re-sync the copy with the redrawn frame right away. */
      const p = active ? progress() : 0;
      drawFrame(true, p);
      lastSpot = "";
      lastTitle = "";
      styleCopy(p);
    };

    /* Growing or collapsing the scroll track shifts everything below the
       film by several viewports. When the viewport is already past the
       film, re-anchor it on the next section so the toggle is invisible even
       in browsers without scroll anchoring. Measuring the neighbour's
       residual shift (instead of the raw height delta) avoids
       double-compensating where the browser anchors on its own. */
    const withScrollCompensation = (mutate) => {
      const anchor = film.nextElementSibling;
      const filmTop = film.getBoundingClientRect().top + window.scrollY;
      const pastFilm = anchor !== null && window.scrollY >= filmTop + stage.offsetHeight;
      const anchorTop = pastFilm ? anchor.getBoundingClientRect().top : 0;
      mutate();
      if (!pastFilm) {
        return;
      }
      const shift = anchor.getBoundingClientRect().top - anchorTop;
      if (Math.abs(shift) > 1) {
        try {
          window.scrollBy({ top: shift, left: 0, behavior: "instant" });
        } catch (err) {
          window.scrollBy(0, shift); /* older engines: no "instant" enum */
        }
      }
    };

    const setLoading = (on) => {
      if (film.classList.contains("is-loading-sequence") === on) {
        return;
      }
      withScrollCompensation(() => {
        film.classList.toggle("is-loading-sequence", on);
      });
    };

    const activate = () => {
      if (active || !sequenceAllowed() || !source.isReady(firstFrame)) {
        return;
      }
      active = true;
      withScrollCompensation(() => {
        film.classList.remove("is-loading-sequence");
        film.classList.add("has-sequence");
      });
      mount.style.transform = ""; /* clear any fallback parallax drift */
      relayout();
      fit();
    };

    const deactivate = () => {
      if (!active) {
        return;
      }
      active = false;
      withScrollCompensation(() => {
        film.classList.remove("has-sequence");
      });
      drawnKey = "";
      styleCopy(0);
      relayout();
    };

    source.subscribe((index, ok) => {
      if (!ok) {
        /* No opening frame, or too many holes: back to the static film */
        if (index === firstFrame || source.isAborted()) {
          failed = true;
          setLoading(false);
          deactivate();
        }
        return;
      }
      if (index === firstFrame) {
        activate();
      }
      queueScrub(); /* a nearer frame may now exist for the current position */
    });

    if ("ResizeObserver" in window) {
      new ResizeObserver(fit).observe(mount);
    } else {
      window.addEventListener("resize", fit);
    }
    onMotionChange((event) => {
      if (event.matches) {
        deactivate();
        setLoading(false);
        return;
      }
      if (!sequenceAllowed()) {
        return;
      }
      if (source.isReady(firstFrame)) {
        activate();
        return;
      }
      setLoading(true);
      source.start();
    });

    fit();
    if (sequenceAllowed()) {
      setLoading(true);
      source.start();
      activate(); /* a film sharing frames another film already loaded */
      window.setTimeout(() => {
        if (!active) {
          setLoading(false);
        }
      }, 8000);
    }
  };

  window.BOK.initFilm = initFilm;
})();
