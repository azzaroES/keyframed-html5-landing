/* BOK FOOD · Film Controller (deferred; runs before js/main.js)
   Controls scroll-driven frame scrubbing, canvas cover-crop drawing,
   and synchronized copy/cast card transitions.
   ========================================================================== */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, getFrameSource, isTouch, onFrame, requestFrame } = window.BOK;

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
        style: el.style,
        isClosing: el.classList.contains("cast-card--closing"),
        start: parseFloat(el.dataset.castStart),
        end: parseFloat(el.dataset.castEnd),
        t: -1,
      }))
      .filter((credit) => Number.isFinite(credit.start) && credit.end > credit.start);

    let active = false;
    let failed = false;
    let drawnKey = -1;
    let cssWidth = 0;
    let cssHeight = 0;
    let lastSpot = "";
    let lastTitle = "";

    const sequenceAllowed = () =>
      !failed &&
      !source.isAborted() &&
      !prefersReducedMotion() &&
      Boolean(window.CSS && CSS.supports("overflow", "clip")) &&
      !(navigator.connection && navigator.connection.saveData);

    const uncovers = film.hasAttribute("data-uncover");
    const isTucked = () => uncovers && active && (parseFloat(window.getComputedStyle(film).marginTop) || 0) < -10;

    const progress = (rect = film.getBoundingClientRect()) => {
      const lead = isTucked() ? stage.offsetHeight : 0;
      const travel = film.offsetHeight - stage.offsetHeight - lead;
      if (travel <= 0) {
        return 0;
      }
      return Math.min(1, Math.max(0, (-rect.top - lead) / travel));
    };

    /* -------------------------------------------------------------------------
       1. Frame Painting & Rendering
       ------------------------------------------------------------------------- */
    const paint = (index) => {
      const image = source.image(index);
      if (!image) {
        return;
      }
      const imageWidth = image.naturalWidth || image.width;
      const imageHeight = image.naturalHeight || image.height;
      if (!imageWidth || !imageHeight) {
        return;
      }
      const scale = Math.max(cssWidth / imageWidth, cssHeight / imageHeight);
      const width = imageWidth * scale;
      const height = imageHeight * scale;
      context.drawImage(image, (cssWidth - width) / 2, (cssHeight - height) / 2, width, height);
    };

    const drawFrame = (force, p) => {
      if (!active) {
        return;
      }
      const exact = (reverse ? 1 - p : p) * (count - 1);
      const target = Math.round(exact);
      const frameIndex = typeof source.readyNearest === "function" ? source.readyNearest(target) : target;
      if (frameIndex === -1) {
        return;
      }
      if (frameIndex === drawnKey && !force) {
        return;
      }
      paint(frameIndex);
      drawnKey = frameIndex;
    };

    /* -------------------------------------------------------------------------
       2. Copy & Cast Animation
       ------------------------------------------------------------------------- */
    const SPOT_SPAN = 0.07;
    const TITLE_START = 0.03;
    const TITLE_SPAN = 0.06;
    const CAST_FADE = 0.28;

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
        const style = credit.style;
        if (!active) {
          style.removeProperty("--cast-t");
          style.removeProperty("opacity");
          style.removeProperty("pointer-events");
          style.removeProperty("visibility");
          credit.t = -1;
          return;
        }
        const local = (p - credit.start) / (credit.end - credit.start);
        const fadeIn = local / CAST_FADE;
        const fadeOut = credit.end >= 1 ? 1 : (1 - local) / CAST_FADE;
        const linear = Math.min(1, Math.max(0, Math.min(fadeIn, fadeOut)));
        const t = Math.round(linear * linear * (3 - 2 * linear) * 1000) / 1000;

        if (t === 0) {
          if (credit.t !== 0) {
            style.opacity = "0";
            style.pointerEvents = "none";
            style.setProperty("--cast-t", "0");
            credit.t = 0;
          }
          return;
        }

        if (t !== credit.t) {
          style.opacity = String(t);
          style.pointerEvents = (t > 0.7 && credit.isClosing) ? "auto" : "none";
          style.setProperty("--cast-t", String(t));
          credit.t = t;
        }
      });
    };

    /* -------------------------------------------------------------------------
       3. Shared Frame Loop Integration
       ------------------------------------------------------------------------- */
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

    /* -------------------------------------------------------------------------
       4. Viewport Fitting & Mobile Address Bar Guard
       ------------------------------------------------------------------------- */
    const MAX_SCALE = isTouch ? 1.5 : 2;

    const fit = () => {
      const box = mount.getBoundingClientRect();
      const scale = Math.min(window.devicePixelRatio || 1, MAX_SCALE);
      const targetW = Math.max(1, Math.round(box.width * scale));
      const targetH = Math.max(1, Math.round(box.height * scale));

      /* Guard: Ignore mobile address bar micro-shifts (< 120px) to prevent
         flushing the canvas backing store during scroll */
      if (canvas.width > 0 && Math.abs(canvas.width - targetW) < 4 && Math.abs(canvas.height - targetH) < 120) {
        cssWidth = box.width;
        cssHeight = box.height;
        return;
      }

      cssWidth = box.width;
      cssHeight = box.height;
      canvas.width = targetW;
      canvas.height = targetH;
      context.setTransform(scale, 0, 0, scale, 0, 0);

      drawnKey = -1;
      const p = active ? progress() : 0;
      drawFrame(true, p);
      lastSpot = "";
      lastTitle = "";
      styleCopy(p);
    };

    /* -------------------------------------------------------------------------
       5. Scroll Compensation & Lifecycle
       ------------------------------------------------------------------------- */
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
          window.scrollBy(0, shift);
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
      mount.style.transform = "";
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
      drawnKey = -1;
      styleCopy(0);
      relayout();
    };

    source.subscribe((index, ok) => {
      if (!ok) {
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
      queueScrub();
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
      activate();
      window.setTimeout(() => {
        if (!active) {
          setLoading(false);
        }
      }, 8000);
    }
  };

  window.BOK.initFilm = initFilm;
})();
