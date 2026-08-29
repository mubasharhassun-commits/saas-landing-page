<script>
/* ==========================================================================
   Waters Law — header burger menu helper
   Paste into: Elementor > Custom Code (Location: Body - End)
               or Appearance > Theme File Editor > footer, before </body>
               or any "Custom HTML" widget in the footer template.

   Adds two behaviours that CSS cannot do on its own:
     1. Clicking anywhere on the page / dark backdrop closes the drawer.
     2. Pressing Esc closes the drawer.

   It does NOT re-implement the menu — it simply clicks Elementor's own
   toggle, so Elementor keeps full control of classes and aria state.
   ========================================================================== */
(function () {
  'use strict';

  var WIDGET = '#header_menu';
  var TOGGLE = '.elementor-menu-toggle';
  var PANEL  = '.elementor-nav-menu--dropdown.elementor-nav-menu__container';

  function openToggle() {
    var widget = document.querySelector(WIDGET);
    if (!widget) return null;
    var toggle = widget.querySelector(TOGGLE);
    if (!toggle) return null;
    var isOpen = toggle.classList.contains('elementor-active') ||
                 toggle.getAttribute('aria-expanded') === 'true';
    return isOpen ? toggle : null;
  }

  function closeMenu(toggle) {
    // Let Elementor do the closing so classes / aria stay consistent.
    toggle.click();
  }

  // --- 1. click anywhere outside the drawer (incl. the dark backdrop) ------
  document.addEventListener('click', function (e) {
    var toggle = openToggle();
    if (!toggle) return;

    // ignore clicks on the burger / X itself
    if (toggle.contains(e.target) || e.target === toggle) return;

    // ignore clicks inside the drawer (menu links, sub-menu arrows, scrollbar)
    var widget = document.querySelector(WIDGET);
    var panel  = widget && widget.querySelector(PANEL);
    if (panel && panel.contains(e.target)) return;

    // ignore the WordPress admin bar for logged-in users
    if (e.target.closest && e.target.closest('#wpadminbar')) return;

    closeMenu(toggle);
  }, true);

  // --- 2. Esc key ---------------------------------------------------------
  document.addEventListener('keyup', function (e) {
    if (e.key !== 'Escape' && e.keyCode !== 27) return;
    var toggle = openToggle();
    if (toggle) closeMenu(toggle);
  });
})();
</script>
