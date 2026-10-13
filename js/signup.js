/* BOK FOOD · Email signup (deferred; runs before js/main.js) */

(() => {
  "use strict";
  /* -------------------------------------------------------------------------
     Email signup: no backend. Validate locally, then swap the form for an
     inline success note and move focus to it.
     ---------------------------------------------------------------------- */
  const initSignup = () => {
    const form = document.getElementById("cta-form");
    const input = document.getElementById("cta-email");
    if (!form || !input) {
      return;
    }
    /* Claimed only once JS runs; without JS the markup keeps the browser's
       native email validation as the fallback. */
    form.noValidate = true;
    const error = document.getElementById("cta-error");
    const success = document.getElementById("cta-success");
    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    const clearError = () => {
      if (error) {
        error.hidden = true;
      }
      input.removeAttribute("aria-invalid");
    };

    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const value = input.value.trim();
      if (!EMAIL_PATTERN.test(value)) {
        if (error) {
          error.hidden = false;
        }
        input.setAttribute("aria-invalid", "true");
        input.focus();
        return;
      }
      clearError();
      form.hidden = true;
      if (success) {
        success.hidden = false;
        success.focus();
      }
    });

    input.addEventListener("input", clearError);
  };

  window.BOK.initSignup = initSignup;
})();
