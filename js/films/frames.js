/* BOK FOOD · Shared Frames Loader
   Progressive hierarchical sequence loader:
   - Downloads 240 WebP frames in progressive passes (step 8 -> 4 -> 2 -> 1)
   - Decodes off main thread via HTMLImageElement async decoding
   - Caches decoded frames in memory shared across both films
   - Provides O(1) readyNearest(target) for crisp 60fps/120fps scrubbing
   ========================================================================== */

(() => {
  "use strict";

  const frameSources = new Map();

  const getFrameSource = (base, count, pad, ext) => {
    const key = [base, count, pad, ext].join("|");
    if (frameSources.has(key)) {
      return frameSources.get(key);
    }

    const frames = [];
    const listeners = [];
    const order = [];
    const added = new Set();
    const addFrame = (i) => {
      if (i >= 0 && i < count && !added.has(i)) {
        added.add(i);
        order.push(i);
      }
    };

    /* Hierarchical progressive loading:
       Pass 0: First and last frames (0, count - 1)
       Pass 1: Every 8th frame (360° skeleton)
       Pass 2: Every 4th frame (halves spacing across whole sequence)
       Pass 3: Every 2nd frame (halves spacing again)
       Pass 4: All remaining frames (complete 240-frame density) */
    addFrame(0);
    addFrame(count - 1);
    for (let i = 0; i < count; i += 8) addFrame(i);
    for (let i = 4; i < count; i += 8) addFrame(i);
    for (let i = 2; i < count; i += 4) addFrame(i);
    for (let i = 1; i < count; i += 2) addFrame(i);

    const MAX_IN_FLIGHT = 6;
    let started = false;
    let aborted = false;
    let failures = 0;
    let inFlight = 0;
    let cursor = 0;

    const url = (index) => base + String(index).padStart(pad, "0") + ext;

    const emit = (index, ok) => {
      listeners.forEach((listener) => listener(index, ok));
    };

    const settle = (index, ok) => {
      inFlight -= 1;
      if (!ok) {
        failures += 1;
        if (failures > Math.ceil(count / 4)) {
          aborted = true;
        }
      }
      emit(index, ok);
      pump();
    };

    const loadImage = (index) => {
      const img = new Image();
      img.decoding = "async";
      frames[index] = { img, ready: false };
      inFlight += 1;

      const onDecoded = () => {
        frames[index].ready = true;
        settle(index, true);
      };

      img.onload = () => {
        if (typeof img.decode === "function") {
          img.decode().then(onDecoded, onDecoded);
        } else {
          onDecoded();
        }
      };
      img.onerror = () => settle(index, false);
      img.src = url(index);
    };

    const pump = () => {
      while (!aborted && cursor < order.length && inFlight < MAX_IN_FLIGHT) {
        loadImage(order[cursor]);
        cursor += 1;
      }
    };

    /* Closest ready frame to target index (O(1) fast-path, outward expansion) */
    const readyNearest = (target) => {
      const clamped = Math.max(0, Math.min(count - 1, target));
      if (frames[clamped] && frames[clamped].ready) {
        return clamped;
      }
      for (let delta = 1; delta < count; delta += 1) {
        const down = clamped - delta;
        if (down >= 0 && frames[down] && frames[down].ready) {
          return down;
        }
        const up = clamped + delta;
        if (up < count && frames[up] && frames[up].ready) {
          return up;
        }
      }
      return -1;
    };

    const readyFrom = (index, step) => {
      for (let i = index; i >= 0 && i < count; i += step) {
        if (frames[i] && frames[i].ready) {
          return i;
        }
      }
      return -1;
    };

    const source = {
      isReady: (index) => Boolean(frames[index] && frames[index].ready),
      isAborted: () => aborted,
      image: (index) => (frames[index] ? frames[index].img : null),
      readyNearest,
      readyAtOrBelow: (index) => readyFrom(index, -1),
      readyAtOrAbove: (index) => readyFrom(index, 1),
      subscribe: (listener) => {
        listeners.push(listener);
      },
      fitTo: () => {},
      start: () => {
        if (started) {
          return;
        }
        started = true;
        pump();
      },
    };

    frameSources.set(key, source);
    return source;
  };

  window.BOK.getFrameSource = getFrameSource;
})();
