/* BOK FOOD · Menu FAB (deferred; runs before js/main.js) */

(() => {
  "use strict";
  /* -------------------------------------------------------------------------
     Menu FAB: the burger at the bottom centre opens the section titles to
     either side of it. It shows once the hero's own scroll cue is gone,
     closes after a pick, on Escape, or on a click outside.
     ---------------------------------------------------------------------- */
  const initFabNav = () => {
    const nav = document.querySelector("[data-fab-nav]");
    const button = nav && nav.querySelector(".fab-nav__button");
    if (!nav || !button) {
      return;
    }
    nav.hidden = false;
    const isOpen = () => nav.classList.contains("is-open");
    const setOpen = (open) => {
      nav.classList.toggle("is-open", open);
      button.setAttribute("aria-expanded", String(open));
    };

    button.addEventListener("click", () => setOpen(!isOpen()));
    nav.addEventListener("click", (event) => {
      if (event.target.closest("a")) {
        setOpen(false);
      }
    });
    nav.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && isOpen()) {
        setOpen(false);
        button.focus();
      }
    });
    document.addEventListener("click", (event) => {
      if (isOpen() && !nav.contains(event.target)) {
        setOpen(false);
      }
    });

    /* Off again as the footer comes in (it carries its own navigation, and
       the button would sit on its bottom line), and through the club after
       it. The footer starts right where <main> ends. */
    const main = document.getElementById("main");
    const footer = document.querySelector(".site-footer");
    window.BOK.onFrame({
      read: () => {
        const y = window.scrollY;
        const vh = window.innerHeight;
        const footerIn =
          main !== null &&
          footer !== null &&
          y + vh > main.offsetTop + main.offsetHeight + footer.offsetHeight * 0.4;
        return y > vh * 0.6 && !footerIn;
      },
      write: (show) => {
        nav.classList.toggle("is-visible", show);
        if (!show && isOpen()) {
          setOpen(false);
        }
      },
    });
  };

  window.BOK.initFabNav = initFabNav;
})();
