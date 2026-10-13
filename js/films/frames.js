/* BOK FOOD · Shared frames (deferred; runs before js/main.js) */

(() => {
  "use strict";
  /* -------------------------------------------------------------------------
     Shared frames: both films scrub the same image sequence, so each set is
     downloaded and decoded once. Frames load through a ladder (the first and
     last frame, then every 8th, then the rest) so any scrub position has
     frames on both sides early (the films blend between the two nearest)
     and the motion sharpens as the gaps fill. Subscribers hear about every
     frame that lands or fails; past count / 4 failures the source gives up.

     Phones held upright: the browser keeps only a few decoded 1920x1080
     frames, so a scrub would decode nearly every frame it draws again, tens
     of milliseconds each on the main thread, and the cover crop shows only
     a narrow middle slice of each one anyway. There each frame is decoded
     once, cut down to the slice the canvas shows (at no more pixels than it
     draws, the whole set within a memory budget) and kept as a bitmap. Over
     http each file is fetched and decoded off the main thread; where fetch
     is refused (opened from disk, a strict content policy) the frame loads
     as an image and is turned into a bitmap in idle time instead. A resize
     the slice no longer covers (a rotation) cuts them again. Elsewhere
     frames load as plain images.
     ---------------------------------------------------------------------- */
  const memory = navigator.deviceMemory || 0;
  const BITMAP_BUDGET = (memory && memory <= 2 ? 64 : memory && memory <= 4 ? 128 : 160) * 1024 * 1024;
  /* Headroom around the slice, so a small resize keeps the bitmaps */
  const SLACK = 1.06;

  const frameSources = new Map();

  const whenIdle = (task) => {
    if (typeof window.requestIdleCallback === "function") {
      window.requestIdleCallback(task, { timeout: 600 });
    } else {
      window.setTimeout(task, 48);
    }
  };

  const getFrameSource = (base, count, pad, ext) => {
    const key = [base, count, pad, ext].join("|");
    if (frameSources.has(key)) {
      return frameSources.get(key);
    }

    const frames = [];
    const listeners = [];
    const order = [0, count - 1];
    for (let i = 8; i < count - 1; i += 8) {
      order.push(i);
    }
    for (let i = 0; i < count; i += 1) {
      if (order.indexOf(i) === -1) {
        order.push(i);
      }
    }

    const MAX_IN_FLIGHT = 6;
    let started = false;
    let aborted = false;
    let failures = 0;
    let inFlight = 0;
    let cursor = 0;

    /* Bitmap mode: the canvas size the films draw at, the frames' own size
       (from the first one decoded), and the slice every frame is kept as */
    let keepBitmaps = false;
    let useFetch = typeof window.fetch === "function" && /^https?:$/.test(window.location.protocol);
    let view = null;
    let natural = null;
    let slice = null;
    let generation = 0;

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

    /* The middle part of a frame a canvas of this size shows when cover
       cropped, and the pixel size to keep it at */
    const sliceFor = (target, slack) => {
      const cover = Math.max(target.width / natural.width, target.height / natural.height);
      const sw = Math.min(natural.width, Math.ceil((target.width / cover) * slack));
      const sh = Math.min(natural.height, Math.ceil((target.height / cover) * slack));
      let shrink = Math.min(1, cover * target.scale);
      const bytes = sw * sh * shrink * shrink * 4 * order.length;
      if (bytes > BITMAP_BUDGET) {
        shrink *= Math.sqrt(BITMAP_BUDGET / bytes);
      }
      return {
        sx: Math.floor((natural.width - sw) / 2),
        sy: Math.floor((natural.height - sh) / 2),
        sw,
        sh,
        width: Math.max(1, Math.round(sw * shrink)),
        height: Math.max(1, Math.round(sh * shrink)),
      };
    };

    const covers = (kept, target) => {
      const need = sliceFor(target, 1);
      return need.sw <= kept.sw && need.sh <= kept.sh && need.width <= kept.width * 1.15 && need.height <= kept.height * 1.15;
    };

    const cut = (full) => {
      const resized = slice.width !== slice.sw || slice.height !== slice.sh;
      const options = resized ? { resizeWidth: slice.width, resizeHeight: slice.height, resizeQuality: "medium" } : {};
      return window.createImageBitmap(full, slice.sx, slice.sy, slice.sw, slice.sh, options);
    };

    /* Decode a frame from its file (a Blob, decoded off the main thread, or
       a loaded image) and keep its slice */
    const keep = (index, origin) => {
      const made = generation;
      return window
        .createImageBitmap(origin)
        .then((full) => {
          if (!natural) {
            natural = { width: full.width, height: full.height };
            slice = sliceFor(view, SLACK);
          }
          return cut(full).then(
            (bitmap) => {
              full.close();
              return bitmap;
            },
            (err) => {
              full.close();
              throw err;
            },
          );
        })
        .then((bitmap) => {
          /* Cut for a size the films have since left: cut it again */
          if (made !== generation) {
            bitmap.close();
            return keep(index, origin);
          }
          const frame = frames[index];
          if (frame.bitmap) {
            frame.bitmap.close();
          }
          frame.bitmap = bitmap;
          frame.ready = true;
          return undefined;
        });
    };

    const recutAll = () => {
      generation += 1;
      const queue = order.filter((index) => frames[index] && frames[index].origin);
      queue.forEach((index) => {
        const frame = frames[index];
        if (frame.bitmap) {
          frame.bitmap.close();
          frame.bitmap = null;
        }
        /* An image still draws (slowly) while its new slice is cut */
        frame.ready = Boolean(frame.img);
      });
      /* One at a time, nearest the ladder's start first, so memory never
         holds more than one full frame extra */
      const next = () => {
        const index = queue.shift();
        if (index === undefined) {
          return;
        }
        keep(index, frames[index].origin)
          .then(
            () => emit(index, true),
            () => emit(index, false),
          )
          .then(next);
      };
      next();
    };

    const loadImage = (index, onLoad) => {
      const img = new Image();
      img.decoding = "async";
      frames[index] = { img, origin: null, bitmap: null, ready: false };
      inFlight += 1;
      /* Decoded before it counts as ready, so drawing it mid-scroll does not
         start on decoding a 1920x1080 image */
      const ready = () => {
        frames[index].ready = true;
        settle(index, true);
        if (onLoad) {
          onLoad(img);
        }
      };
      img.onload = () => {
        if (typeof img.decode === "function") {
          img.decode().then(ready, ready);
        } else {
          ready();
        }
      };
      img.onerror = () => settle(index, false);
      img.src = url(index);
    };

    /* No fetch: the image draws as soon as it lands, and its bitmap (a
       decode on the main thread) is made when the page is idle, one at a
       time, and swapped in */
    const idleQueue = [];
    let idleBusy = false;
    const keepWhenIdle = () => {
      if (idleBusy || !idleQueue.length) {
        return;
      }
      idleBusy = true;
      whenIdle(() => {
        const index = idleQueue.shift();
        const frame = frames[index];
        frame.origin = frame.img;
        keep(index, frame.img)
          .then(
            () => emit(index, true),
            () => {
              frame.origin = null;
            },
          )
          .then(() => {
            idleBusy = false;
            keepWhenIdle();
          });
      });
    };

    const loadBitmap = (index) => {
      if (!useFetch) {
        loadImage(index, () => {
          idleQueue.push(index);
          keepWhenIdle();
        });
        return;
      }
      frames[index] = { img: null, origin: null, bitmap: null, ready: false };
      inFlight += 1;
      window
        .fetch(url(index))
        .then((response) => {
          if (!response.ok) {
            throw new Error(String(response.status));
          }
          return response.blob();
        })
        .then((blob) => {
          frames[index].origin = blob;
          return keep(index, blob);
        })
        .then(
          () => settle(index, true),
          () => {
            /* Fetching or cutting refused: this frame and the rest load as
               images (a frame that fails that way too counts as a failure) */
            useFetch = false;
            inFlight -= 1;
            loadBitmap(index);
          },
        );
    };

    const pump = () => {
      while (!aborted && cursor < order.length && inFlight < MAX_IN_FLIGHT) {
        (keepBitmaps ? loadBitmap : loadImage)(order[cursor]);
        cursor += 1;
      }
    };

    /* The nearest frame ready at or below (step -1) or at or above (+1) */
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
      /* A kept slice or the whole image; either draws the same cover crop */
      image: (index) => frames[index].bitmap || frames[index].img,
      readyAtOrBelow: (index) => readyFrom(index, -1),
      readyAtOrAbove: (index) => readyFrom(index, 1),
      subscribe: (listener) => {
        listeners.push(listener);
      },
      /* A running film reports the canvas size it draws at (CSS pixels and
         the pixel scale) as it starts and on every resize; until then the
         window stands in, as the pinned stage fills it */
      fitTo: (width, height, scale) => {
        if (width < 1 || height < 1) {
          return;
        }
        view = { width, height, scale };
        if (keepBitmaps && slice && !covers(slice, view)) {
          slice = sliceFor(view, SLACK);
          recutAll();
        }
      },
      start: () => {
        if (started) {
          return;
        }
        started = true;
        if (!view) {
          view = {
            width: window.innerWidth,
            height: window.innerHeight,
            scale: Math.min(window.devicePixelRatio || 1, window.BOK.isTouch ? 1.5 : 2),
          };
        }
        keepBitmaps =
          typeof window.createImageBitmap === "function" &&
          window.matchMedia("(max-width: 720px)").matches &&
          view.height > view.width;
        pump();
      },
    };
    frameSources.set(key, source);
    return source;
  };

  window.BOK.getFrameSource = getFrameSource;
})();
