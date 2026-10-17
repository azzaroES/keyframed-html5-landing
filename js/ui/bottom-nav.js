/**
 * Adaptive Mobile Bottom App Bar Controller & Active State Tracker
 * 
 * Accurately detects the active page and updates the selected tab state.
 *
 * @package Cariñena_Kebab_FSE
 */

(function () {
  'use strict';

  function updateActiveBottomNavTab() {
    var nav = document.querySelector('.kebab-bottom-app-bar');
    if (!nav) return;

    var links = nav.querySelectorAll('.kebab-bottom-nav-link');
    var path = window.location.pathname.replace(/\/$/, '') || '/';
    var hash = window.location.hash;

    links.forEach(function (link) {
      link.classList.remove('is-active');
      link.removeAttribute('aria-current');

      var href = link.getAttribute('href');
      if (!href) return;

      var cleanHref = href.split('#')[0].replace(/\/$/, '') || '/';
      var linkHash = href.indexOf('#') !== -1 ? href.substring(href.indexOf('#')) : '';

      var isMatch = false;

      if (linkHash && hash === linkHash) {
        isMatch = true;
      } else if (!linkHash) {
        if (cleanHref === '/' && path === '/') {
          isMatch = true;
        } else if (cleanHref !== '/' && path.indexOf(cleanHref) === 0) {
          isMatch = true;
        }
      }

      if (isMatch) {
        link.classList.add('is-active');
        link.setAttribute('aria-current', 'page');
      }
    });
  }

  // Run on DOM ready, hash change, and popstate
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateActiveBottomNavTab);
  } else {
    updateActiveBottomNavTab();
  }

  window.addEventListener('hashchange', updateActiveBottomNavTab);
  window.addEventListener('popstate', updateActiveBottomNavTab);

  // Expose globally for SPA router
  window.KebabBottomNav = {
    update: updateActiveBottomNavTab
  };
})();
