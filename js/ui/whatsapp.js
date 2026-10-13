/* BOK FOOD · WhatsApp (deferred; runs before js/main.js) */

(() => {
  "use strict";
  /* -------------------------------------------------------------------------
     WhatsApp: a native <details> menu (it opens without JS); this only adds
     closing on Escape and on a click outside.
     ---------------------------------------------------------------------- */
  const initWhatsApp = () => {
    const widget = document.querySelector(".wa");
    if (!widget) {
      return;
    }
    const summary = widget.querySelector("summary");
    document.addEventListener("click", (event) => {
      if (widget.open && !widget.contains(event.target)) {
        widget.open = false;
      }
    });
    widget.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && widget.open) {
        widget.open = false;
        if (summary) {
          summary.focus();
        }
      }
    });
  };

  window.BOK.initWhatsApp = initWhatsApp;
})();
