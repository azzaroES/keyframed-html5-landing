/* BOK FOOD · Back to top (deferred; runs before js/main.js) */

(() => {
  "use strict";
  const { docEl, prefersReducedMotion, onFrame } = window.BOK;

  /* -------------------------------------------------------------------------
     Back to top: the 3px ring fills with the scroll and carries a checkpoint
     for each section anchor that blinks as you reach it and stays lit once
     passed. Pressing it, or the footer's "Desliza" cue ([data-rewind]),
     rewinds the page over a few seconds so both films play back on the way
     up; with motion off it jumps. A wheel, touch, key or click elsewhere
     hands control back.
     ---------------------------------------------------------------------- */
  const initBackToTop = () => {
    const button = document.querySelector("[data-totop]");
    const main = document.getElementById("main");
    const fill = button && button.querySelector(".totop__fill");
    const marksGroup = button && button.querySelector(".totop__marks");
    if (!button || !main || !fill || !marksGroup) {
      return;
    }
    button.hidden = false;
    const SVG_NS = "http://www.w3.org/2000/svg";
    const RADIUS = 26.5;
    /* The page in flow order: <main>'s sections, then the footer and the
       club, which follow it */
    const flow = Array.from(main.children).concat(
      Array.from(document.querySelectorAll(".site-footer, .cta")).filter((el) => !main.contains(el))
    );
    const sections = flow.filter((el) => el.id && el.id !== "top");
    let marks = [];
    let measuredFor = "";
    let lastDash = "";
    let rewind = null;

    /* Where each section sits in normal flow; the footer is sticky, so its
       box can be displaced and its rect would mislead. */
    const naturalTops = () => {
      const tops = new Map();
      let y = main.offsetTop;
      flow.forEach((child) => {
        /* The menu film tucks a screen up under the page before it */
        y += parseFloat(window.getComputedStyle(child).marginTop) || 0;
        tops.set(child, y);
        y += child.offsetHeight;
      });
      return tops;
    };

    const buildMarks = (tops, max) => {
      marksGroup.textContent = "";
      marks = sections.map((section) => {
        const at = Math.min(1, (tops.get(section) || 0) / max);
        const angle = at * Math.PI * 2;
        const dot = document.createElementNS(SVG_NS, "circle");
        dot.setAttribute("class", "totop__mark");
        dot.setAttribute("cx", (28 + RADIUS * Math.cos(angle)).toFixed(2));
        dot.setAttribute("cy", (28 + RADIUS * Math.sin(angle)).toFixed(2));
        dot.setAttribute("r", "3");
        dot.addEventListener("animationend", () => dot.classList.remove("is-blink"));
        marksGroup.appendChild(dot);
        return { dot, at, passed: null };
      });
    };

    /* In the shared frame loop: measure the scroll (and, when films grow
       into tracks or the screen resizes, every checkpoint), then draw */
    onFrame({
      read: () => {
        const scrollHeight = docEl.scrollHeight;
        const vh = window.innerHeight;
        const size = `${scrollHeight}x${vh}`;
        const max = Math.max(1, scrollHeight - vh);
        return { size, max, vh, y: window.scrollY, tops: size !== measuredFor ? naturalTops() : null };
      },
      write: (m) => {
        if (m.tops) {
          measuredFor = m.size;
          buildMarks(m.tops, m.max);
        }
        const progress = Math.min(1, Math.max(0, m.y / m.max));
        const dash = `${(progress * 100).toFixed(2)} 100`;
        if (dash !== lastDash) {
          fill.style.strokeDasharray = dash;
          lastDash = dash;
        }
        marks.forEach((mark) => {
          const passed = progress >= mark.at - 0.002;
          if (passed === mark.passed) {
            return;
          }
          /* Blink on arrival; a rebuilt ring (null) just takes its state */
          if (passed && mark.passed === false && !prefersReducedMotion()) {
            mark.dot.classList.add("is-blink");
          }
          mark.dot.classList.toggle("is-passed", passed);
          mark.passed = passed;
        });
        button.classList.toggle("is-visible", m.y > m.vh * 0.6);
      },
    });

    const stopRewind = () => {
      if (rewind !== null) {
        window.cancelAnimationFrame(rewind);
        rewind = null;
        docEl.style.scrollBehavior = "";
      }
    };

    const land = () => {
      docEl.style.scrollBehavior = "";
      main.focus({ preventScroll: true });
    };

    const triggers = [button].concat(Array.from(document.querySelectorAll("[data-rewind]")));
    const startRewind = (event) => {
      event.preventDefault();
      stopRewind();
      const from = window.scrollY;
      /* Each step must land exactly, not glide under CSS smooth scrolling */
      docEl.style.scrollBehavior = "auto";
      if (prefersReducedMotion() || from < 2) {
        window.scrollTo(0, 0);
        land();
        return;
      }
      const duration = Math.min(4200, Math.max(900, from / 3));
      const start = performance.now();
      const step = (now) => {
        const t = Math.min(1, (now - start) / duration);
        const eased = t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        window.scrollTo(0, Math.round(from * (1 - eased)));
        if (t < 1) {
          rewind = window.requestAnimationFrame(step);
        } else {
          rewind = null;
          land();
        }
      };
      rewind = window.requestAnimationFrame(step);
    };
    triggers.forEach((trigger) => trigger.addEventListener("click", startRewind));

    ["wheel", "touchstart", "keydown", "pointerdown"].forEach((type) => {
      window.addEventListener(
        type,
        (event) => {
          if (rewind !== null && !triggers.some((trigger) => trigger.contains(event.target))) {
            stopRewind();
          }
        },
        { passive: true }
      );
    });

    window.addEventListener("load", window.BOK.requestFrame);
  };

  window.BOK.initBackToTop = initBackToTop;
})();
