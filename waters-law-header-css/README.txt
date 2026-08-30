Waters Law — header burger menu fix
===================================

FILES
-----
waters-law-header-menu.css   → your complete, corrected CSS (replaces the
                               whole block you were using, including the
                               1300px / 1299px / 1298px media queries).
waters-law-submenu-click.js  → OPTIONAL. Makes sub-menus open on click
                               instead of hover. See item 6 below for why
                               this one cannot be done in CSS.

WHERE TO PASTE
--------------
CSS : Elementor > Site Settings > Custom CSS
      (or Appearance > Customize > Additional CSS)
      Replace your existing block entirely with this file's contents.

JS  : Elementor > Custom Code > Add New, Location: "Body - End"
      Paste as-is, the <script> tags are included.
      Skip this file if you want zero JavaScript — see item 6.

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

5. ARROW = TOGGLE, TEXT = LINK — section 9 of the CSS.
   The arrow now has its own click target: the right-hand 64px of the row in
   the mobile drawer, and an enlarged (but layout-neutral) box on desktop.
   The text is left alone as a plain link.

6. CLICK INSTEAD OF HOVER — waters-law-submenu-click.js.
   This is the one piece CSS cannot do, and it is worth knowing why before
   you decide whether to paste it.

   Elementor runs these menus with a library called SmartMenus. SmartMenus
   is what opens a sub-menu when you hover on desktop, and what makes the
   FIRST tap on a mobile parent link open the sub-menu rather than follow the
   link (that is the "it redirects to Cases We Handle" behaviour). Both are
   JavaScript. A stylesheet can move the arrow, resize it, colour it — it has
   no way to cancel a JS hover handler or redefine what a click does.

   The snippet flips a single SmartMenus option, noMouseOver = true. It adds
   no handlers of its own and does not replace the menu. Result:
     - hover opens nothing, desktop or mobile
     - the arrow opens and closes the sub-menu on click
     - the text goes to its page on the first click, desktop and mobile
     - clicking elsewhere closes an open sub-menu

   IF YOU SKIP THE SNIPPET: the arrow still toggles and the CSS still works,
   but desktop sub-menus keep opening on hover, and the first tap on a mobile
   parent still opens instead of navigating.

   CSS-ONLY ALTERNATIVE for mobile, if you want no JavaScript at all: in
   section 9, change the arrow's `width: 64px !important;` to
   `width: auto !important; left: 0 !important;`. That stretches the arrow
   across the whole row, so every tap toggles and nothing navigates — which is
   what the previous version did. The row then stops linking to its own page,
   and desktop hover is unaffected either way.

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
