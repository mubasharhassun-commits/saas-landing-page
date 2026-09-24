=== Farrell Post Types ===
Contributors: rustamalirandhawa
Tags: post types, news, wpbakery, elementor
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Registers the Trending Topics post type and renders a featured story beside
a list of recent ones.

== Description ==

One element, three ways to place it: the "Trending Topics" WPBakery element,
an Elementor widget of the same name, or the [fc_topics] shortcode. All three
call the same renderer, so a page built in either builder looks identical.

Ordering defaults to Recently Added, which sorts on the order posts were
added to the site rather than the date printed on the badge. The two differ
whenever an article is back-dated: a piece from an older year uploaded today
is the newest thing on the site and the oldest thing by publish date, and
sorting on publish date would bury it.

== Changelog ==

= 1.2.0 =
* Type sizes now hold against a theme that forces its own. This site's
  custom.css carries h3 { font-size: 30px !important } and p { font-size:
  17px !important }, which flattened the featured title, the list titles and
  both paragraphs to one size. Every type declaration here is forced too, and
  every forced value is still a custom property, so the builder stays in
  charge of what it is forced to.
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
