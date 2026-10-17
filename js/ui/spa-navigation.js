/**
 * Client-Side Inner Page Router (Zero-Dependency SPA Transitions)
 * 
 * Satisfies user specification:
 * "refreshes on every page change should refresh only when to home"
 * 
 * - Seamlessly transitions between inner pages (Blog, Post, Contact) without hard reloads
 * - Forces full browser refresh ONLY when navigating to Home (/) so 3D keyframe canvas re-initializes fresh.
 *
 * @package Cariñena_Kebab_FSE
 */

(function () {
  'use strict';

  var homeUrl = (window.kebabThemeConfig && window.kebabThemeConfig.homeUrl) ? window.kebabThemeConfig.homeUrl.replace(/\/$/, '') : window.location.origin;

  function isHomeTarget(url) {
    var cleanUrl = url.split('#')[0].replace(/\/$/, '');
    var cleanHome = homeUrl.replace(/\/$/, '');
    var path = url.replace(window.location.origin, '').split('#')[0].replace(/\/$/, '');
    return cleanUrl === cleanHome || path === '' || path === '/';
  }

  function isInternalLink(a) {
    if (!a || !a.href) return false;
    if (a.target && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    if (a.href.indexOf('mailto:') === 0 || a.href.indexOf('tel:') === 0) return false;
    if (a.closest && (a.closest('#wpadminbar') || a.closest('.edit-link') || a.closest('.post-edit-link'))) return false;
    if (a.href.indexOf('wp-admin') !== -1 || a.href.indexOf('wp-login') !== -1) return false;
    if (a.href.indexOf('/go/') !== -1) return false; // allow shortlinker redirects
    return a.origin === window.location.origin;
  }

  function loadPage(url, pushState) {
    var main = document.getElementById('main') || document.querySelector('main') || document.querySelector('.wp-block-group');
    if (!main) {
      window.location.href = url;
      return;
    }

    // Add loading transition indicator
    document.body.classList.add('kebab-page-transitioning');

    fetch(url, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) throw new Error('Network error');
        return response.text();
      })
      .then(function (html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var newMain = doc.getElementById('main') || doc.querySelector('main') || doc.querySelector('.wp-block-group');
        if (!newMain) {
          window.location.href = url;
          return;
        }

        var updateDOM = function () {
          main.innerHTML = newMain.innerHTML;
          main.className = newMain.className;
          document.title = doc.title;

          // Copy body classes
          if (doc.body.className) {
            document.body.className = doc.body.className;
          }

          // Update body class between front-page and inner-page
          var isNewHome = isHomeTarget(url);
          if (isNewHome) {
            document.body.classList.add('kebab-front-page');
            document.body.classList.remove('kebab-inner-page');
          } else {
            document.body.classList.add('kebab-inner-page');
            document.body.classList.remove('kebab-front-page');
          }

          if (pushState) {
            window.history.pushState({ url: url }, doc.title, url);
          }

          // Update bottom navigation active tab
          if (window.KebabBottomNav && window.KebabBottomNav.update) {
            window.KebabBottomNav.update();
          }

          // Re-hydrate any live schedule pills on newly loaded page
          var pills = document.querySelectorAll('.kebab-live-status-pill');
          if (pills.length && window.evaluateSchedulePills) {
            window.evaluateSchedulePills();
          }

          // Re-init blog hero if available
          if (window.BOK && window.BOK.initBlogHero) {
            try {
              window.BOK.initBlogHero();
            } catch (err) {}
          }

          window.scrollTo(0, 0);
          document.body.classList.remove('kebab-page-transitioning');
        };

        // Use modern View Transitions API if available in browser
        if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
          try {
            var transition = document.startViewTransition(updateDOM);
            if (transition && transition.ready) {
              transition.ready.catch(function () {});
            }
            if (transition && transition.finished) {
              transition.finished.catch(function () {});
            }
          } catch (err) {
            updateDOM();
          }
        } else {
          updateDOM();
        }
      })
      .catch(function () {
        // Fallback to traditional hard navigation on error
        window.location.href = url;
      });
  }

  function handleLinkClick(e) {
    var a = e.target.closest('a');
    if (!a || !isInternalLink(a)) return;

    var dest = a.href;
    var hash = a.hash;

    // RULE: If navigating to Home (/), do NOT intercept! Allow full browser refresh!
    if (isHomeTarget(dest)) {
      return; // Full browser refresh to home as requested!
    }

    // If it's purely an in-page hash anchor on the same page, let browser scroll
    var currentBase = window.location.href.split('#')[0];
    var destBase = dest.split('#')[0];
    if (currentBase === destBase && hash) {
      return;
    }

    // Intercept navigation between inner pages
    e.preventDefault();
    loadPage(dest, true);
  }

  window.addEventListener('popstate', function () {
    // If popstate leads back to home, full reload
    if (isHomeTarget(window.location.href)) {
      window.location.reload();
    } else {
      loadPage(window.location.href, false);
    }
  });

  document.addEventListener('click', handleLinkClick);
})();
