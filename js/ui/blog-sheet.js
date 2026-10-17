/**
 * Cinematic Blog Post Sheet Pull-Up Controller
 * 
 * Provides smooth scroll-linked elevation for the contrast content sheet
 * over the 100vh pinned video hero, conserving battery and mobile GPU.
 *
 * @package Cariñena_Kebab_FSE
 */

(function () {
  'use strict';

  function initBlogSheetPullUp() {
    var hero = document.querySelector('.kebab-cinema-hero');
    var mediaWrap = document.querySelector('.kebab-cinema-media-wrap');
    var sheet = document.querySelector('.kebab-content-sheet-container');
    var video = mediaWrap ? mediaWrap.querySelector('video') : null;

    if (!hero || !sheet) return;

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function handleScroll() {
      var scrollY = window.pageYOffset || document.documentElement.scrollTop;
      var heroHeight = hero.offsetHeight || window.innerHeight;

      // When scrolled well past hero, pause video if playing to save battery
      if (video) {
        if (scrollY > heroHeight * 1.5) {
          if (!video.paused) video.pause();
        } else {
          if (video.paused) video.play().catch(function () {});
        }
      }

      if (!prefersReducedMotion && mediaWrap) {
        // Subtle opacity fade for video as sheet slides up
        var progress = Math.min(1, Math.max(0, scrollY / heroHeight));
        mediaWrap.style.opacity = (1 - progress * 0.45).toFixed(2);
      }
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBlogSheetPullUp);
  } else {
    initBlogSheetPullUp();
  }
})();
