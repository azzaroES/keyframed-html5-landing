/* BOK FOOD · Boot: runs last, once every feature script has registered
   itself on window.BOK (all scripts are deferred, so the DOM is parsed).
   Scroll effects run in BOK's shared frame loop in the order they start
   here; paging goes first, because the assembly and strokes on each page
   read how far it has been uncovered this frame. */

(() => {
  "use strict";
  const BOK = window.BOK || {};

  const safeRun = (name, fn) => {
    try {
      if (typeof fn === "function") {
        return fn();
      }
    } catch (e) {
      console.warn(`[Cariñena Kebab] ${name} notice:`, e);
    }
    return null;
  };

  safeRun("PageReveal", BOK.initPageReveal);
  safeRun("Assembly", BOK.initAssembly);
  safeRun("Strokes", BOK.initStrokes);
  const remeasureParallax = safeRun("Parallax", BOK.initParallax) || (() => {});
  if (typeof BOK.initFilm === "function") {
    document.querySelectorAll(".film").forEach((film) => {
      try {
        BOK.initFilm(film, remeasureParallax);
      } catch (e) {
        console.warn("[Cariñena Kebab] Film init notice:", e);
      }
    });
  }
  /* After the films, so the loading screen can tell whether they load */
  safeRun("Preloader", BOK.initPreloader);
  safeRun("BackToTop", BOK.initBackToTop);
  safeRun("A11yPanel", BOK.initA11yPanel);
  safeRun("FabNav", BOK.initFabNav);
  safeRun("WhatsApp", BOK.initWhatsApp);
  safeRun("Signup", BOK.initSignup);

  /* Argon terminal badge: mobile tap toggle & outside/escape click dismiss */
  const argonBadge = document.querySelector(".argon-terminal-badge");
  if (argonBadge) {
    argonBadge.addEventListener("click", (e) => {
      if (e.target.closest(".terminal-popup")) return;
      const isActive = argonBadge.classList.toggle("is-active");
      argonBadge.setAttribute("aria-expanded", String(isActive));
    });
    document.addEventListener("click", (e) => {
      if (!argonBadge.contains(e.target)) {
        argonBadge.classList.remove("is-active");
        argonBadge.setAttribute("aria-expanded", "false");
      }
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && argonBadge.classList.contains("is-active")) {
        argonBadge.classList.remove("is-active");
        argonBadge.setAttribute("aria-expanded", "false");
        argonBadge.focus();
      }
    });
  }
})();

