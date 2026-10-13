/* BOK FOOD · Core, shared by every other script
   Everything in js/ is optional polish: with JavaScript off the page is
   fully visible, the films show static credits, the WhatsApp menu still
   opens (native <details>) and the signup form degrades to a plain form.
   All scripts load with defer, in order: core, the features, then main. */

(() => {
  "use strict";

  /* Gates the hidden-at-rest reveal states and the hero entrance in
     the CSS; must happen before anything else. */
  const docEl = document.documentElement;
  docEl.classList.add("js");

  /* Accessibility preferences are restored before anything reads them, so
     "Detener animaciones" holds from the first frame. */
  const A11Y_KEY = "bokfood-a11y";
  const TEXT_SIZES = [100, 115, 130, 150];
  const a11yState = { size: 0, light: false, contrast: false, still: false, links: false };

  const applyA11y = () => {
    docEl.style.fontSize = a11yState.size ? `${TEXT_SIZES[a11yState.size]}%` : "";
    docEl.classList.toggle("a11y-contrast", a11yState.contrast);
    docEl.classList.toggle("a11y-still", a11yState.still);
    docEl.classList.toggle("a11y-links", a11yState.links);
    docEl.classList.toggle("a11y-light", a11yState.light);
  };

  try {
    const saved = JSON.parse(window.localStorage.getItem(A11Y_KEY) || "null");
    if (saved && typeof saved === "object") {
      const size = Number(saved.size);
      a11yState.size = Number.isInteger(size) && size >= 0 && size < TEXT_SIZES.length ? size : 0;
      a11yState.contrast = saved.contrast === true;
      a11yState.still = saved.still === true;
      a11yState.links = saved.links === true;
      a11yState.light = saved.light === true;
    }
  } catch (err) {
    /* Storage blocked or unreadable: keep the defaults */
  }
  applyA11y();

  /* Motion is off when the system asks for it or when the accessibility
     panel's "Detener animaciones" is on; handlers hear about both. */
  const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  const motionHandlers = [];
  const prefersReducedMotion = () => motionQuery.matches || docEl.classList.contains("a11y-still");
  const onMotionChange = (handler) => {
    motionHandlers.push(handler);
  };
  const notifyMotion = () => {
    const event = { matches: prefersReducedMotion() };
    motionHandlers.forEach((handler) => handler(event));
  };
  if (typeof motionQuery.addEventListener === "function") {
    motionQuery.addEventListener("change", notifyMotion);
  } else if (typeof motionQuery.addListener === "function") {
    motionQuery.addListener(notifyMotion);
  }

  /* Touch screens get the lighter version of a few effects (.is-touch in
     the CSS, a lower canvas resolution in the films) */
  const isTouch = window.matchMedia("(pointer: coarse)").matches;
  docEl.classList.toggle("is-touch", isTouch);

  /* One frame loop for every scroll effect. Each registers a task whose
     read() only measures layout and whose write(measured) only changes
     styles; on scroll or resize all the reads run, then all the writes,
     once per frame. Measuring and writing interleaved across a dozen
     effects makes the browser lay the page out again and again in a
     single frame, which is what made scrolling stutter on phones. */
  const frameTasks = [];
  let framePending = false;
  const runFrame = () => {
    framePending = false;
    const measured = frameTasks.map((task) => (task.read ? task.read() : undefined));
    frameTasks.forEach((task, i) => {
      if (task.write) {
        task.write(measured[i]);
      }
    });
  };
  const requestFrame = () => {
    if (!framePending) {
      framePending = true;
      window.requestAnimationFrame(runFrame);
    }
  };
  const onFrame = (task) => {
    frameTasks.push(task);
    requestFrame();
  };
  window.addEventListener("scroll", requestFrame, { passive: true });
  window.addEventListener("resize", requestFrame);

  window.BOK = {
    docEl,
    A11Y_KEY,
    TEXT_SIZES,
    a11yState,
    applyA11y,
    prefersReducedMotion,
    onMotionChange,
    notifyMotion,
    isTouch,
    onFrame,
    requestFrame,
  };
})();
