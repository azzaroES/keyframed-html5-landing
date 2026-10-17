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

    var isContactPage = path.indexOf('/contact') !== -1 || path.indexOf('/contacto') !== -1 || document.body.classList.contains('page-template-page-contact');
    var isBlogPage = path.indexOf('/blog') !== -1 || path.indexOf('/category') !== -1 || path.indexOf('/categoria') !== -1 || 
                     document.body.classList.contains('single') || document.body.classList.contains('single-post') || 
                     document.body.classList.contains('blog') || document.body.classList.contains('archive') ||
                     Boolean(document.querySelector('.kebab-blog-archive-container, .kebab-pullup-hero'));

    links.forEach(function (link) {
      link.classList.remove('is-active');
      link.removeAttribute('aria-current');

      var href = link.getAttribute('href');
      if (!href) return;

      var cleanHref = href.split('#')[0].replace(/\/$/, '') || '/';
      var linkHash = href.indexOf('#') !== -1 ? href.substring(href.indexOf('#')) : '';

      var isMatch = false;

      if (linkHash) {
        if (hash === linkHash) {
          isMatch = true;
        }
      } else if (cleanHref.indexOf('/contact') !== -1 || cleanHref.indexOf('/contacto') !== -1) {
        if (isContactPage) {
          isMatch = true;
        }
      } else if (cleanHref.indexOf('/blog') !== -1) {
        if (isBlogPage && !isContactPage) {
          isMatch = true;
        }
      } else if (cleanHref === '/') {
        if ((path === '/' || path === '') && !isBlogPage && !isContactPage && (!hash || hash === '#top')) {
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
