=== Farrell Post Types ===
Contributors: rustamalirandhawa
Tags: post types, news, wpbakery, elementor
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Renders your posts as a featured story beside a list - the Trending Topics
section.

== Description ==

One element, three ways to place it: the "Trending Topics" WPBakery element,
an Elementor widget of the same name, or the [fc_topics] shortcode. All three
call the same renderer, so a page built in either builder looks identical.

Stories are ordinary WordPress posts - the plugin registers no post type of
its own. The shortcode's source attribute can point it at another registered
post type, and falls back to posts when that type is not registered.

Ordering defaults to Recently Added, which sorts on the order posts were
added to the site rather than the date printed on the badge. The two differ
whenever an article is back-dated: a piece from an older year uploaded today
is the newest thing on the site and the oldest thing by publish date, and
sorting on publish date would bury it.

== Security ==

The plugin has no admin screens, no forms, no AJAX or REST endpoints, and
reads no superglobals, so it presents no authenticated surface to protect
with nonces or capability checks. It runs no direct database queries; posts
are fetched through WP_Query with a whitelisted orderby, a two-value order, a
capped post count, a slugged category and a post type checked against the
registered list.

Every value that reaches a style attribute is type-checked by FC_Style and
dropped if it does not match - colours, lengths, ratios, keywords and image
URLs each have their own validator, and an unrecognised parameter is not
written at all. Every value that reaches the document is escaped at the point
of output.

== Changelog ==

= 1.5.0 =
* List headings and paragraphs are exactly three lines deep, so two rows
  beside each other always measure the same. A word limit could never do
  this on its own: the same 13 words wrap to three lines or four depending
  on how long the words are. A short heading is held open to the full depth
  and a long one is clamped to it, so a one-word title and a twenty-five
  word title produce rows of identical height, with the buttons at the same
  place in each.
* Both line counts are controls. 0 lets a row be its own height.
* Headings are capped at 13 words as well, which is what the longest of the
  current titles runs to.
* Stacked on tablet and phone the fixed depth is dropped - the rows are full
  width there and no longer sit beside each other, so it would only leave
  gaps.

= 1.4.0 =
* Fixed: a crafted featured-image URL could close the CSS string in the
  photo's style attribute and add declarations of its own. esc_url encodes a
  quote as &#039;, and the browser turns that back into a quote while reading
  the attribute, before any CSS is parsed - so escaping alone was not enough.
  The URL is now validated as a CSS value and rejected outright if it carries
  a quote, bracket, semicolon or space; a URL that cannot be made safe renders
  the empty frame instead. Four attack strings that got through before are
  now blocked.
* Hardened: index.php in every directory, an uninstall.php that documents
  that the plugin stores nothing, and the date format passed through
  sanitize_text_field.
* Translations are loaded on init.

= 1.3.0 =
* No post type of its own. Stories are ordinary WordPress posts, so the
  Trending Topics item is gone from the admin menu and the Source control is
  gone from both builders.
* An element saved while the post type still existed carries source="news".
  Rather than rendering nothing, an unregistered type now falls back to posts,
  so nothing has to be reconfigured.
* Category filtering and the category meta line read whichever category-like
  taxonomy the post's type actually has, instead of a hard-coded name.

= 1.2.1 =
* Type sizes now hold against a theme that forces its own. This site's
  custom.css carries h3 { font-size: 30px !important } and p { font-size:
  17px !important }, which flattened the featured title, the list titles and
  both paragraphs to one size. Every type declaration here is forced too, and
  every forced value is still a custom property, so the builder stays in
  charge of what it is forced to.
* The right-hand headings carry their own figures at every width, written
  out explicitly for tablet and phone rather than inherited from the featured
  one, so changing either size cannot silently move the other.
* The section heading is blank by default. A page usually carries its own
  heading above the element, and two of them is worse than none. Type
  something into Heading Text to bring one back.

= 1.1.0 =
* Photo sizes taken from the comp: the featured photo is 750 x 310 and the
  list photo 375 x 310 - half the width, the same height. Both are held as
  proportions rather than pixels, so the pair keeps that relationship at any
  container width and the two photos always start on the same line.
* The date badge sits 20px in from the right and bottom of the photo, at
  16px on a 20px line in #162542.
* The gap between the featured story and the list, and between list rows,
  is 30px.
* Desktop and laptop share one layout at one set of figures. Tablet and
  phone get the stacked version: one column, photo above the words.
* Word limits for headings and paragraphs, counted separately for the
  featured story and the list rows.
* A story with no photo renders an empty frame rather than nothing, so the
  row below it cannot ride up and break the alignment.

= 1.0.0 =
* First release, replacing WL Post Types.
* Vessels and Cases are gone, along with the cards grid and slider that drew
  them. Only Trending Topics remains.
* News is renamed Trending Topics throughout - the admin menu, the builder
  element and the section heading. The post type slug stays "news" and its
  taxonomy "news_category", so posts written before the rename keep their
  URLs and their terms.
* Redesigned: photo above the story with a gold date badge on its corner,
  the featured story beside a list whose photos sit next to the text.
* Gold #d5af34 and navy #162542, section heading 36/40, body text 18/30, and
  an outlined uppercase Read More. Every one of those is a builder control.
