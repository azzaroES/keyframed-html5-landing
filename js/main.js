/* BOK FOOD · Boot: runs last, once every feature script has registered
   itself on window.BOK (all scripts are deferred, so the DOM is parsed).
   Scroll effects run in BOK's shared frame loop in the order they start
   here; paging goes first, because the assembly and strokes on each page
   read how far it has been uncovered this frame. */

(() => {
  "use strict";
  const BOK = window.BOK;

  BOK.initPageReveal();
  BOK.initAssembly();
  BOK.initStrokes();
  const remeasureParallax = BOK.initParallax();
  document.querySelectorAll(".film").forEach((film) => BOK.initFilm(film, remeasureParallax));
  /* After the films, so the loading screen can tell whether they load */
  BOK.initPreloader();
  BOK.initBackToTop();
  BOK.initA11yPanel();
  BOK.initFabNav();
  BOK.initWhatsApp();
  BOK.initSignup();
})();
