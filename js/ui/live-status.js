/**
 * Live Business Schedule Evaluator (Client Hydration)
 * 
 * Accurately calculates open/closed status in user's browser,
 * allowing the page HTML to be cached permanently by LiteSpeed Cache / CDN!
 *
 * @package Cariñena_Kebab_FSE
 */

(function () {
  'use strict';

  function evaluateSchedulePill(pill) {
    var rawSchedule = pill.getAttribute('data-schedule');
    if (!rawSchedule) return;

    var schedule;
    try {
      schedule = JSON.parse(rawSchedule);
    } catch (e) {
      console.warn('Invalid schedule JSON:', e);
      return;
    }

    var textEl = pill.querySelector('.status-indicator-text');
    if (!textEl) return;

    var now = new Date();
    var days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    var currentDay = days[now.getDay()];
    var currentMinutes = now.getHours() * 60 + now.getMinutes();

    var todaySchedule = schedule[currentDay];
    var isOpen = false;
    var closesAt = '';
    var opensAt = '';

    if (todaySchedule && !todaySchedule.closed && todaySchedule.open && todaySchedule.close) {
      var openParts = todaySchedule.open.split(':').map(Number);
      var closeParts = todaySchedule.close.split(':').map(Number);

      var openMin = openParts[0] * 60 + openParts[1];
      var closeMin = closeParts[0] * 60 + closeParts[1];

      // Handle after-midnight closing times (e.g., 00:30)
      if (closeMin < openMin) {
        closeMin += 24 * 60;
        var adjustedCurrent = (now.getHours() < 6) ? currentMinutes + 24 * 60 : currentMinutes;
        if (adjustedCurrent >= openMin && adjustedCurrent < closeMin) {
          isOpen = true;
          closesAt = todaySchedule.close;
        }
      } else {
        if (currentMinutes >= openMin && currentMinutes < closeMin) {
          isOpen = true;
          closesAt = todaySchedule.close;
        } else if (currentMinutes < openMin) {
          opensAt = todaySchedule.open;
        }
      }
    }

    if (isOpen) {
      pill.classList.remove('is-closed');
      pill.classList.add('is-open');
      textEl.textContent = 'Deschis acum • închidem la ' + closesAt;
    } else {
      pill.classList.remove('is-open');
      pill.classList.add('is-closed');
      if (opensAt) {
        textEl.textContent = 'Închis acum • deschidem la ' + opensAt;
      } else {
        // Find next opening day
        var nextDayIdx = (now.getDay() + 1) % 7;
        var nextDayName = days[nextDayIdx];
        var nextDaySchedule = schedule[nextDayName];
        var nextOpen = (nextDaySchedule && nextDaySchedule.open) ? nextDaySchedule.open : '12:00';
        textEl.textContent = 'Închis acum • deschidem mâine la ' + nextOpen;
      }
    }
  }

  function initLiveStatusPills() {
    var pills = document.querySelectorAll('.kebab-live-status-pill');
    for (var i = 0; i < pills.length; i++) {
      evaluateSchedulePill(pills[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLiveStatusPills);
  } else {
    initLiveStatusPills();
  }

  // Periodic refresh every 60 seconds
  setInterval(initLiveStatusPills, 60000);
})();
