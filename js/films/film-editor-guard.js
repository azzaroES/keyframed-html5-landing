/**
 * Gutenberg Site Editor Guard for 3D Keyframe Hero
 *
 * Ensures that inside the WordPress Block Editor iframe/window:
 * 1. The canvas renders a high-fidelity static poster (Frame 1)
 * 2. Editor scrolling is not hijacked
 * 3. Text blocks (titles, buttons, kickers) remain 100% clickable and editable
 *
 * @package Cariñena_Kebab_FSE
 */

(function () {
  'use strict';

  if (!window.wp || !wp.domReady) {
    return;
  }

  wp.domReady(function () {
    var checkEditorCanvas = function () {
      var films = document.querySelectorAll('.film');
      films.forEach(function (film) {
        var canvas = film.querySelector('.film__canvas');
        if (!canvas) return;

        var base = film.dataset.frameBase || '';
        var pad = parseInt(film.dataset.framePad || '3', 10);
        var ext = film.dataset.frameExt || '.webp';

        if (!base) return;

        // Render Frame 1
        var frameStr = String(1).padStart(pad, '0');
        var imgUrl = base + frameStr + ext;

        var img = new Image();
        img.onload = function () {
          var ctx = canvas.getContext('2d');
          if (!ctx) return;
          var w = canvas.parentElement.offsetWidth || window.innerWidth;
          var h = canvas.parentElement.offsetHeight || window.innerHeight;
          canvas.width = w;
          canvas.height = h;

          var scale = Math.max(w / img.width, h / img.height);
          var sw = img.width * scale;
          var sh = img.height * scale;
          ctx.drawImage(img, (w - sw) / 2, (h - sh) / 2, sw, sh);
        };
        img.src = imgUrl;

        // Ensure text overlays are clearly visible and editable
        var veil = film.querySelector('.film__veil');
        if (veil) {
          veil.style.opacity = '1';
          veil.style.pointerEvents = 'none';
        }
      });
    };

    setTimeout(checkEditorCanvas, 500);
    setInterval(checkEditorCanvas, 3000); // Re-run when blocks are inserted
  });
})();
