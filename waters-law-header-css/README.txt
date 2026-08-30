Waters Law — header burger menu fix
===================================

FILE
----
waters-law-header-menu.css   → your complete, corrected CSS (replaces the
                               whole block you were using, including the
                               1300px / 1299px / 1298px media queries).

CSS only. No JavaScript, no snippets, nothing else to install.

WHERE TO PASTE
--------------
Elementor > Site Settings > Custom CSS
(or Appearance > Customize > Additional CSS)
Replace your existing block entirely with this file's contents.

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

4. TWO BORDER LINES UNDER THE X — section 5 of the CSS.
   The fixed top bar had its own border-bottom, sitting about 10px above the
   first menu row's border-top, so you saw two stacked lines. The bar's border
   is gone; the row's border-top is the single divider now.

5. PARENT ROW NAVIGATING INSTEAD OF CLOSING — section 9 of the CSS.
   "CASES WE HANDLE" is a real link. SmartMenus swallows the FIRST click to
   open the sub-menu, then lets the SECOND click through as a normal link —
   which is why trying to close it loaded the Cases We Handle page and
   reloaded everything.
   Fix: .sub-arrow is SmartMenus' own toggle control and it cancels the link
   on every click, so it is now stretched across the whole row. Every click on
   the row hits the toggle: open, close, open, close, no navigation.
   Trade-off: the parent's own page is no longer reachable by tapping that row
   in the drawer (keyboard Tab + Enter still follows it). If you want it
   reachable, add it as the first sub-item in Appearance > Menus, e.g.
   "All Cases We Handle" above "Maritime Negligence Claims".

CLOSING THE DRAWER
------------------
The X button closes it. Clicking the dark backdrop does NOT close it — that
needs JavaScript, and this build is CSS only by request.

The backdrop still blocks clicks on the page behind the drawer (unchanged
from your original CSS, section 4: `pointer-events: auto`). If you would
rather the page stay clickable while the menu is open, change that one line
to `pointer-events: none`.

Nothing outside the header menu (#header_menu) is touched — other sections,
layouts and the desktop header are unchanged.
