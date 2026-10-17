/* BOK FOOD · Accessibility panel (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { A11Y_KEY, TEXT_SIZES, a11yState, applyA11y, prefersReducedMotion, notifyMotion } = window.BOK;

  /* -------------------------------------------------------------------------
     Accessibility panel: a tab on the left edge pulls the panel out. Text
     size, high contrast, stopping animations and underlining links live on
     <html> and are remembered in this browser. Escape, the close button or
     a click outside puts the panel away.
     ---------------------------------------------------------------------- */
  const initA11yPanel = () => {
    const root = document.querySelector("[data-a11y]");
    const tab = root && root.querySelector(".a11y__tab");
    const panel = root && root.querySelector(".a11y__panel");
    if (!root || !tab || !panel) {
      return;
    }
    const closeButton = root.querySelector(".a11y__close");
    const sizeValue = root.querySelector(".a11y__value");
    root.hidden = false;

    const save = () => {
      try {
        window.localStorage.setItem(A11Y_KEY, JSON.stringify(a11yState));
      } catch (err) {
        /* Not remembered this time; the settings still apply */
      }
    };

    const render = () => {
      if (sizeValue) {
        sizeValue.textContent = `${TEXT_SIZES[a11yState.size]} %`;
      }
      root.querySelectorAll("[data-a11y-size]").forEach((button) => {
        const step = Number(button.dataset.a11ySize);
        button.disabled = step < 0 ? a11yState.size === 0 : a11yState.size === TEXT_SIZES.length - 1;
      });
      root.querySelectorAll("[data-a11y-toggle]").forEach((button) => {
        button.setAttribute("aria-pressed", String(a11yState[button.dataset.a11yToggle] === true));
      });
    };

    const update = (change) => {
      const wasStill = prefersReducedMotion();
      const oldSize = a11yState.size;
      Object.assign(a11yState, change);
      applyA11y();
      save();
      render();
      if (prefersReducedMotion() !== wasStill || ((oldSize === 0) !== (a11yState.size === 0))) {
        notifyMotion();
      }
      if (a11yState.size !== oldSize) {
        /* Text reflow moves everything: let films, parallax and strokes re-measure */
        window.dispatchEvent(new Event("resize"));
      }
    };

    const isOpen = () => root.classList.contains("is-open");
    const setOpen = (open, moveFocus) => {
      root.classList.toggle("is-open", open);
      tab.setAttribute("aria-expanded", String(open));
      if (moveFocus) {
        (open ? panel : tab).focus();
      }
    };

    tab.addEventListener("click", () => setOpen(!isOpen(), true));
    if (closeButton) {
      closeButton.addEventListener("click", () => setOpen(false, true));
    }
    root.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && isOpen()) {
        setOpen(false, true);
      }
    });
    document.addEventListener("click", (event) => {
      if (isOpen() && !root.contains(event.target)) {
        setOpen(false, false);
      }
    });

    root.addEventListener("click", (event) => {
      const sizeButton = event.target.closest("[data-a11y-size]");
      if (sizeButton) {
        const next = a11yState.size + Number(sizeButton.dataset.a11ySize);
        update({ size: Math.min(TEXT_SIZES.length - 1, Math.max(0, next)) });
        return;
      }
      const toggle = event.target.closest("[data-a11y-toggle]");
      if (toggle) {
        const name = toggle.dataset.a11yToggle;
        update({ [name]: !a11yState[name] });
        return;
      }
      if (event.target.closest("[data-a11y-reset]")) {
        update({ size: 0, light: false, contrast: false, still: false, links: false });
      }
    });

    render();
  };

  window.BOK.initA11yPanel = initA11yPanel;
})();
