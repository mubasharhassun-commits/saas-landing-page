=== ADA Accessibility ===
Contributors: rustamalirandhawa
Tags: accessibility, a11y, contrast, elementor, wcag
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.29.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A front-end accessibility toolbar with high contrast, text resizing and
skip-to-content. Works standalone or as an Elementor widget.

== Description ==

Adds a floating button that opens a panel with:

* Skip to Content — moves keyboard focus to the main content region
* High Contrast — black background, white text, yellow underlined links
* Increase Text Size — cycles 100% / 115% / 130%
* Clear All — resets everything

Three ways to place it:

1. **Everywhere** — leave "Show on every page" on in Settings → ADA Accessibility
2. **Elementor** — drop the "ADA Accessibility" widget into a header or footer template
3. **Shortcode** — `[ada_accessibility]`

Only one toolbar prints per page, whichever way it gets there.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the zip via
   Plugins → Add New → Upload Plugin.
2. Activate it.
3. Settings → ADA Accessibility to configure.

== Frequently Asked Questions ==

= Does this make my site WCAG compliant? =

No. It is a visitor-facing convenience layer. It does not fix missing alt
text, bad heading order, unlabelled form fields, or poor colour contrast in
your own design. Those still need doing properly. Treat this as a
supplement, not a substitute.

= High contrast breaks my logo =

The contrast rules are intentionally broad so they work on any theme. Add
your own override in Appearance → Customize → Additional CSS:

`body.adaa-contrast .my-logo img { filter: brightness(0) invert(1); }`

= Text sizing does not affect some elements =

Content injected after page load (lazy sliders, AJAX forms) is not scaled
until the option is toggled again.

== Changelog ==

= 1.29.0 =
* The header is a static 78px, on every screen and at all times - phone,
  tablet and desktop, sitting at the top of the page or pinned after a scroll.
  78 is a constant in the script; nothing is measured.
* --wpex-sticky-header-height is removed from the <body> tag. Total writes that
  variable by measuring its own header, which is where the 61px came from, and
  where 1.28.0's 110.61px came from once the plugin's own measurement fed back
  into it. The declaration is now stripped off the body tag and stripped again
  every time the theme writes it back, so no calculated figure is left on the
  page at all.
* High contrast paints the full 78px of the bar black, in both the in-flow and
  the pinned state, and leaves the footer at its own height.
* Skip to Content makes one smooth move to the top of the page and stops.
  Measuring a sticky bar and correcting for it was what made the page stutter
  and sometimes stop short at the banner: the bar's height changes as the page
  moves, so every correction invited another. The top of the page is a fixed
  number nothing can move, so the scroll is issued once and simply arrives. A
  target set on the settings screen still wins.

= 1.18.0 =
* Floating button redrawn to match the reference design: a disc in the icon
  colour with the figure punched through it, plus a ring around the edge.
* Button colours taken from the site palette.
* The current page's menu link turns white in high contrast, instead of
  keeping a dark accent colour that vanished against the black bar.

= 1.17.0 =
* High contrast changes background colours only. Text colours are never
  altered, so the Review Us button keeps its white background and dark text.
* The background is found at runtime on whichever element actually paints it,
  which on Elementor sites is an inner container rather than the header or
  footer element itself.
* Only full-width bands are recoloured, so buttons, icons and logo blocks are
  left untouched.

= 1.16.0 =
* High contrast turns the header and footer black again, with white text.
* Elements carrying their own background — the Review Us button, badges —
  are measured before the change and put back exactly as they were.
* Logo blocks and dropdown arrows stay transparent, so nothing covers text
  and no black block appears behind a transparent logo.
* Skip to Content returns to the top of the page from anywhere.

= 1.15.0 =
* Settings now apply to the current page only. Navigating to another page
  starts clean, with high contrast and text size back to normal. The
  "Remember visitor choices" option has been removed.

= 1.14.1 =
* Fixed a broken background check that stopped every 1.14.0 change from
  taking effect.

= 1.14.0 =
* High contrast now leaves any element that already paints its own background
  untouched. The logo block and buttons such as "Review Us" keep their own
  colours; only the bar itself turns black.
* Choices carry across page navigation reliably, applied in the page head
  before the first paint rather than after the footer script runs.
* No flash or jump when switching contrast on or moving between pages.

= 1.13.2 =
* Logo wrappers stay transparent in high contrast, so a transparent logo no
  longer sits on a black block overflowing the header bar.

= 1.13.1 =
* Fixed the last letter of a menu item being covered in high contrast. Only
  the header and footer containers are painted black; their children are
  transparent, so overlapping elements no longer hide the text beneath.

= 1.13.0 =
* High contrast now only turns the header and footer black. Section
  backgrounds, images and body content are left untouched.
* Increase Text Size is a single 10% step. The second press returns the page
  to its original size.

= 1.12.3 =
* Default colours matched to the site's slide-out menu: #161a20 panel,
  #8cb5b8 highlight, 10% white dividers.

= 1.12.2 =
* Skip to Content works from anywhere on the page again, scrolling up to the
  content start regardless of how far down the visitor is.

= 1.12.1 =
* Skip to Content never scrolls backwards. Clicking it from the footer no
  longer throws the visitor back to the top of the page.

= 1.12.0 =
* Z-index raised to the 32-bit maximum, and the toolbar moves itself to the
  end of the body so DOM order cannot place anything above it.
* Skip to Content measures any sticky header at click time and offsets by it
  automatically, so no manual offset is needed. Target auto-detection covers
  more theme and Elementor wrappers.

= 1.11.0 =
* Default colours now match a dark slide-out menu: near-black panel, white
  text, hairline dividers and a teal highlight on hover.
* "Match site colours" defaults to off, so the plugin scheme applies unless
  you switch it to an Elementor global.

= 1.10.0 =
* The toolbar now takes its colours from the site's Elementor global palette
  instead of a fixed navy, so it matches the rest of the site by default.
* New "Match site colours" setting to choose which global colour it uses.

= 1.9.0 =
* High contrast keeps the toolbar's own dividers instead of stripping them.
* Increase Text Size no longer enlarges the toolbar itself, only the page.
* The toolbar now measures the highest z-index actually in use on the page
  and places itself above it, instead of relying on a fixed number.

= 1.8.0 =
* Fixed Increase Text Size blowing up the page. Sizes are now measured for
  every element before any of them is changed, so nested elements no longer
  compound each other's scaling.
* Reverted the blanket border removal in high contrast. Only the toolbar's
  own borders are stripped; the page keeps its borders.

= 1.7.1 =
* High contrast hides the page's own borders by matching them to the black
  background, so nav links, phone numbers and buttons no longer appear boxed.

= 1.7.0 =
* High contrast leaves the toolbar's own colour scheme alone. The panel, the
  floating button and the menu items keep their configured colours while the
  rest of the page flips to black and white.

= 1.6.0 =
* Default z-index raised to the practical maximum and marked !important, with
  a plain fallback for browsers that fail to parse the custom property.

= 1.5.2 =
* High contrast strips every border, outline and shadow from the toolbar
  itself, including the item dividers.

= 1.5.1 =
* High contrast no longer draws white outlines around menu links, buttons,
  form fields or the toolbar itself. Elements keep their own borders.

= 1.5.0 =
* Skip to Content now takes a target selector and a scroll offset, so it can
  clear a sticky header instead of landing underneath it. Auto-detection
  falls back through a longer list of common content wrappers.
* Layer (z-index) is now a setting, and the default is much higher, so a
  sticky header no longer covers the panel.
* Both are available on the settings page and on the Elementor widget.

= 1.4.0 =
* Background groups (solid or gradient) on the panel, the menu items and the
  floating button, each with its own hover state.
* Border groups on the panel, the menu items and the floating button, plus a
  hover border colour for items and the button.
* Box shadow on the menu items.
* Base CSS specificity raised so these groups can override the defaults while
  a theme's own button rules still cannot.

= 1.3.0 =
* Icon pickers on the Content tab: swap the floating button icon and any menu
  item icon for a Font Awesome icon or your own SVG.
* Floating button gains its own icon size, line weight and border controls.
* Removed the colour pickers from Settings. All styling now happens on the
  Elementor widget.

= 1.2.0 =
* Every appearance value is now an Elementor control. Style tab sections:
  Panel, Divider, Menu Items, Icons, Active Indicator, Floating Button,
  Motion & Focus.
* Removed the plugin's own type styling; typography is inherited until you
  set it in the Typography group.

= 1.1.0 =
* Full Elementor Style tab: typography, text shadow, normal/hover tabs, icon
  size and colour, item padding, divider colour and thickness, panel width,
  floating button colour, size, radius, offsets and box shadow.
* Hardened CSS so themes that style bare button elements can no longer
  reshape the panel (fixes uneven item widths, forced uppercase and
  theme hover colours bleeding through).

= 1.0.0 =
* Initial release.
