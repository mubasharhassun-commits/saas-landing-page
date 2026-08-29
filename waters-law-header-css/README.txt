Waters Law — header burger menu fix
===================================

FILES
-----
1. waters-law-header-menu.css   → your complete, corrected CSS (replaces the
                                  whole block you were using, including the
                                  1300px / 1299px / 1298px media queries).
2. waters-law-header-menu.js    → small snippet that closes the drawer when
                                  you click outside it or press Esc.

WHERE TO PASTE
--------------
CSS : Elementor > Site Settings > Custom CSS
      (or Appearance > Customize > Additional CSS)
      Replace your existing block entirely with this file's contents.

JS  : Elementor > Custom Code > Add New
        Location: Body - End
      Paste the file contents as-is (the <script> tags are included).
      Alternative: a Custom HTML widget in the footer template.

WHAT WAS FIXED
--------------
1. CLOSE (X) BUTTON — section 5 of the CSS.
   An opaque bar is now pinned to the top of the drawer, fixed to the
   viewport, sitting above the menu list (z-index 9998) and below the X
   (z-index 9999). The X never moves and menu rows scroll cleanly out of
   sight behind the bar instead of running across the icon.
   The bar grows automatically for the WordPress admin bar (+32px desktop,
   +46px under 782px), which is why the X used to collide while logged in.

2. SUB-MENUS OPEN BY DEFAULT ON RESPONSIVE — section 9 of the CSS.
   ul.sub-menu is now `display:none` by default. This is deliberately
   WITHOUT !important: Elementor/SmartMenus writes an inline `display:block`
   when the arrow is tapped, and an inline style beats a stylesheet rule, so
   the sub-menu is collapsed on load and toggles on click.
   >> Do not add !important to that rule or the sub-menu will never open. <<
   The arrow also got a 40x40 tap target and rotates when expanded.

3. DRAWER FORCED OPEN AT <=1298px — section 0 + 3 of the CSS.
   The old query contained
       .elementor-nav-menu--dropdown-tablet .elementor-nav-menu--dropdown { display:block; }
       ... ul.elementor-nav-menu { background:#fff; }
       ... .elementor-nav-menu__container { margin:20px 0 0; }
   Those forced the dropdown visible/white and shifted the panel. Removed.
   The drawer's closed state is now hard-locked (transform + visibility +
   pointer-events, all !important) and only the active toggle opens it.

4. CLICK OUTSIDE / ESC TO CLOSE — the JS file.
   CSS cannot detect a click on the backdrop, so this is the one piece that
   needs a snippet. It doesn't re-implement anything: it just triggers
   Elementor's own toggle, so all classes and aria states stay correct.
   Clicks on the burger, inside the drawer, and on the WP admin bar are
   ignored.

Nothing outside the header menu (#header_menu) is touched — other sections,
layouts and the desktop header are unchanged.
