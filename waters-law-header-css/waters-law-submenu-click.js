<script>
/* ==========================================================================
   Waters Law — sub-menus open on CLICK, never on hover
   Paste into: Elementor > Custom Code > Add New, Location: "Body - End"
               (or a Custom HTML widget in the footer template)

   WHY THIS EXISTS
   ---------------
   Elementor runs its nav menus with SmartMenus. SmartMenus decides:
     - desktop: hovering a parent opens its sub-menu
     - touch:   the FIRST tap on a parent link opens the sub-menu instead of
                following the link, the SECOND tap follows it
   Both are JavaScript behaviours. CSS can style the arrow, but it cannot
   change what a click or a hover does — hence this file.

   WHAT IT CHANGES
   ---------------
   One SmartMenus option: noMouseOver = true.
     - hover no longer opens anything, desktop or mobile
     - the arrow (SmartMenus' own .sub-arrow control) opens and closes on click
     - the text is left as a plain link, so it goes to its page on the first
       click, desktop and mobile
     - clicking elsewhere still closes an open sub-menu (SmartMenus' own
       hideOnClick, left at its default)

   It does not replace the menu or add handlers of its own — it flips a flag
   on Elementor's existing instance, so everything else stays stock.
   ========================================================================== */
(function () {
  'use strict';

  function applyTo($, $menu) {
    if (!$menu.length || !$menu.data('smartmenus')) return false;
    try {
      $menu.smartmenus('option', 'noMouseOver', true);
      $menu.smartmenus('option', 'hideOnClick', true);
      return true;
    } catch (e) {
      return false;
    }
  }

  function applyAll($) {
    var done = true;
    $('.elementor-nav-menu').each(function () {
      if (!applyTo($, $(this))) done = false;
    });
    return done;
  }

  function boot($) {
    // SmartMenus initialises after Elementor's frontend script runs, and the
    // sticky/duplicated header can mount later still — so retry briefly.
    var tries = 0;
    var timer = setInterval(function () {
      tries++;
      if (applyAll($) || tries > 20) clearInterval(timer);
    }, 250);

    // Re-apply whenever Elementor (re)builds a nav menu widget, e.g. in the
    // editor preview or a sticky header clone.
    if (window.elementorFrontend && elementorFrontend.hooks) {
      elementorFrontend.hooks.addAction(
        'frontend/element_ready/nav-menu.default',
        function ($scope) { applyTo($, $scope.find('.elementor-nav-menu')); }
      );
    }
  }

  if (window.jQuery) {
    jQuery(function ($) { boot($); });
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      if (window.jQuery) jQuery(function ($) { boot($); });
    });
  }
})();
</script>
