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

5. ARROW = TOGGLE, TEXT = LINK — section 9 of the CSS.
   Elementor's own behaviour is kept exactly as it ships:
     desktop     -> sub-menu opens on hover, as before
     responsive  -> sub-menu opens on click, as before
   The only thing that changed is where the control lives. The arrow now has
   a real click target of its own — the right-hand 64px of the row in the
   mobile drawer, and an enlarged box on desktop (the padding that grows it
   is cancelled by an equal negative margin, so the header bar does not move
   a pixel). The "CASES WE HANDLE" text is a plain link to its own page.

   Why it used to misfire: the anchor's padding ran past the little arrow, so
   clicks aimed just beside the arrow landed on the link instead of the
   toggle. The arrow now owns that space.

   ONE THING TO KNOW ON REAL TOUCH DEVICES: SmartMenus (the library Elementor
   runs these menus with) has a built-in touch rule — the first tap on a
   parent LINK opens its sub-menu, the second tap follows the link. That is
   Elementor's stock behaviour and it is untouched here, per your request.
   Tapping the ARROW is unaffected: it toggles, every time, first tap.
   If you ever want the text to navigate on the very first tap instead, that
   needs a JavaScript snippet — it is a SmartMenus handler, not something a
   stylesheet can reach.

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
