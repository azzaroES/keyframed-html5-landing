/* BOK FOOD · Paging (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { prefersReducedMotion, onMotionChange, onFrame, requestFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     Paging: every page after the hero waits beneath the page before it
     (sticky in the CSS, or for the menu film, tucked a screen up under the
     method page) and is uncovered as that page lifts off. As each one is
     uncovered, --page-reveal goes from 0 to 1, fading its dark veil; the
     assembly and strokes inside it play along via BOK.pageReveal(), which
     reads this frame's values (paging measures first in the frame loop).

     A page whose content is taller than the screen with the toolbars
     showing (100svh, which stays put while they slide away) is .is-tall
     and scrolls in normally. Until half a screen before its turn a page
     is .is-covered, out of the drawing: the pages all wait stuck to the
     bottom of the screen, and drawing every one under the hero would make
     the phone composite the whole site on every frame. A page starts to be
     uncovered when the page above reaches the bottom of the screen in its
     own place in the flow, so the flow is followed section by section.
     With motion off everything sits in normal flow and stays fully shown.
     ---------------------------------------------------------------------- */
  const PAGES = ".order, .pillars, .film--menu, .cta, .site-footer";
  const LEAD = 0.5; /* screens of warning a page gets before it is uncovered */
  const current = new Map();

  /* How far the page holding `el` has been uncovered, 0 to 1, or null when
     that page is not being paged (motion off, text zoomed, too tall, or not a page) */
  const pageReveal = (el) => {
    const page = el.closest(PAGES);
    const state = page && current.get(page);
    const zoomed = document.documentElement.classList.contains("a11y-zoomed");
    return state && !state.tall && !prefersReducedMotion() && !zoomed ? state.reveal : null;
  };

  const initPageReveal = () => {
    const main = document.getElementById("main");
    if (!main) {
      return;
    }
    const footer = document.querySelector(".site-footer");
    const flow = Array.from(main.children);
    if (footer && !main.contains(footer)) {
      flow.push(footer);
    }
    /* Each section with the one above it; only pages get paged */
    const sections = flow.map((el, i) => ({
      el,
      cover: i > 0 ? flow[i - 1] : null,
      page: i > 0 && el.matches(PAGES),
      film: el.classList.contains("film"),
      tall: false,
      covered: false,
      written: "",
      uncoveredAt: 0,
    }));
    const pages = sections.filter((section) => section.page);
    if (!pages.length) {
      return;
    }

    const probe = document.createElement("div");
    probe.className = "page-probe";
    probe.setAttribute("aria-hidden", "true");
    document.body.appendChild(probe);

    const clear = () => {
      current.clear();
      pages.forEach((page) => {
        page.el.style.removeProperty("--page-reveal");
        page.el.classList.remove("is-tall", "is-covered");
        page.written = "";
        page.tall = false;
        page.covered = false;
      });
    };

    onFrame({
      read: () => {
        if (prefersReducedMotion() || document.documentElement.classList.contains("a11y-zoomed")) {
          if (current.size > 0) {
            clear();
          }
          return null;
        }
        const vh = window.innerHeight;
        const screen = probe.offsetHeight || vh;
        const y = window.scrollY;
        /* Where each section sits in the flow: a stuck page is placed by
           the flow, anything else by where it is drawn */
        let flowBottom = 0;
        return sections.map((section) => {
          const content = section.el.querySelector(".container") || section.el.firstElementChild;
          const contentHeight = content ? (content.scrollHeight || content.offsetHeight) : 0;
          // Determine tallness ONLY from the inner content height, never from section.el.offsetHeight which has CSS min-height: 100svh
          // Use hysteresis to eliminate frame-by-frame flapping
          const wasTall = section.tall;
          const tall = !section.film && (wasTall ? contentHeight > screen - 36 : contentHeight > screen - 12);
          const stuck = section.page && !section.film && !tall;
          const height = stuck ? screen : Math.max(contentHeight, section.el.offsetHeight || 0);
          const top = stuck ? flowBottom : section.el.getBoundingClientRect().top + y;
          const coverBottom = flowBottom;
          flowBottom = top + height;
          if (!section.page) {
            return { skip: true, covered: false, uncoveredAt: top };
          }
          /* Only a running film is tucked under the page above */
          if (section.film && !section.el.classList.contains("has-sequence")) {
            return { skip: true, covered: false, uncoveredAt: coverBottom };
          }
          const span = section.film ? vh : Math.min(height, vh);
          const uncovered = vh - section.cover.getBoundingClientRect().bottom;
          const reveal = Math.min(1, Math.max(0, uncovered / span));
          current.set(section.el, { reveal, tall });
          return {
            tall,
            reveal: reveal.toFixed(3),
            covered: y + vh < coverBottom - LEAD * vh,
            uncoveredAt: coverBottom,
          };
        });
      },
      write: (states) => {
        if (!states) {
          return;
        }
        states.forEach((state, i) => {
          const section = sections[i];
          section.uncoveredAt = state.uncoveredAt;
          if (!section.page) {
            return;
          }
          if (state.covered !== section.covered) {
            section.el.classList.toggle("is-covered", state.covered);
            section.covered = state.covered;
          }
          if (state.skip) {
            if (section.written) {
              section.el.style.removeProperty("--page-reveal");
              section.written = "";
            }
            return;
          }
          if (!section.film && state.tall !== section.tall) {
            section.el.classList.toggle("is-tall", state.tall);
            section.tall = state.tall;
          }
          if (state.reveal !== section.written) {
            section.el.style.setProperty("--page-reveal", state.reveal);
            section.written = state.reveal;
          }
        });
      },
    });

    const scrollToY = (top, smooth) => {
      try {
        window.scrollTo({ top, left: 0, behavior: smooth ? "smooth" : "instant" });
      } catch (err) {
        window.scrollTo(0, top); /* older engines: no "instant" enum */
      }
    };

    /* The section an in-page link points at, if it is one of the pages */
    const sectionFor = (id) => {
      const target = id ? document.getElementById(id) : null;
      return target ? sections.find((section) => section.el === target || section.el.contains(target)) || null : null;
    };

    /* A keyboard user tabbing into a page still covered: bring the page up,
       fully uncovered, so the focused link is in sight (not when a link
       below is already on its way there) */
    let following = false;
    document.addEventListener("focusin", (event) => {
      if (following) {
        return;
      }
      const section = pages.find((page) => page.covered && page.el.contains(event.target));
      if (!section) {
        return;
      }
      window.requestAnimationFrame(() => scrollToY(section.uncoveredAt, false));
    });

    /* In-page links (the menu button, the footer, the wordmarks, "Desliza"):
       a stuck page is drawn at the bottom of the screen, not where it sits
       in the page, so the browser's own jump lands short or not at all.
       Go instead to where the page is fully uncovered, and move focus there
       without a second jump. */
    document.addEventListener("click", (event) => {
      if (prefersReducedMotion() || event.defaultPrevented || event.button !== 0) {
        return;
      }
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
      }
      const link = event.target.closest('a[href^="#"]');
      const id = link ? decodeURIComponent(link.hash.slice(1)) : "";
      const section = sectionFor(id);
      if (!section) {
        return;
      }
      event.preventDefault();
      scrollToY(section.uncoveredAt, true);
      if (window.location.hash !== `#${id}`) {
        window.history.pushState(null, "", `#${id}`);
      }
      const target = document.getElementById(id);
      if (!target.hasAttribute("tabindex")) {
        target.setAttribute("tabindex", "-1");
      }
      following = true;
      target.focus({ preventScroll: true });
      following = false;
    });

    /* Opened at, or switched to, an address naming a section */
    const revealHash = () => {
      const section = !prefersReducedMotion() && sectionFor(decodeURIComponent(window.location.hash.slice(1)));
      if (section) {
        requestFrame();
        window.requestAnimationFrame(() => window.requestAnimationFrame(() => scrollToY(section.uncoveredAt, false)));
      }
    };
    window.addEventListener("hashchange", revealHash);
    window.BOK.revealHash = revealHash;

    onMotionChange((event) => {
      if (event.matches || document.documentElement.classList.contains("a11y-zoomed")) {
        clear();
      } else {
        requestFrame();
      }
    });
  };

  window.BOK.pageReveal = pageReveal;
  window.BOK.initPageReveal = initPageReveal;
})();
