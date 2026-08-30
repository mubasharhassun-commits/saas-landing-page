# Testimonial Manager

Manage client testimonials from the WordPress dashboard and display them as a
responsive grid with an accessible "read full review" popup.

- Works through the `[testimonials]` shortcode anywhere on the site.
- Adds a native **Testimonials Grid** widget when Elementor is active.
- Works on **Elementor Free** — the popup is built into this plugin and does
  not use Elementor Pro's Popup Builder.
- Does not require Elementor at all. With Elementor absent the widget simply
  is not registered and the shortcode keeps working.

Requires WordPress 6.0+ and PHP 7.4+.

---

## Installation

1. Plugins → Add New → Upload Plugin.
2. Choose `testimonial-manager.zip` and click **Install Now**.
3. Click **Activate**.

A **Testimonials** menu appears in the dashboard. Five example categories are
created on first activation (Maritime Law, Personal Injury, Employment Law,
Family Law, Criminal Law). Delete any you do not need — they are not recreated.

---

## Creating a testimonial

**Testimonials → Add New.**

| What you enter | Where |
|---|---|
| Client name | The **title** field at the top |
| Full testimonial | The main **editor** |
| Short testimonial | The **Excerpt** panel |
| Client image / logo | **Client Image** in Testimonial Details (or Featured image) |
| Display order | **Order**, under Page Attributes |
| Rating, position, company, source, date, featured | The **Testimonial Details** box |

### Short testimonial

Leave the Excerpt blank and the card generates one from the full testimonial,
trimmed to whole words and ending in `...`. Fill it in when you want to control
exactly what the card shows. The popup always shows the complete testimonial,
never truncated.

### Adding the image

Use **Client Image** in the Testimonial Details box - click *Select Image* and
pick from the media library. Each testimonial has its own. The image is cropped
square and shown as a circle.

The plugin looks for an image in this order, using the first it finds:

1. **Client Image** on the testimonial
2. Its **Featured image**
3. The widget's **Default Client Image**
4. A circle with the client's initial Roughly 120×120px or larger gives a sharp result on retina screens.
A testimonial with no image falls back to the widget's **Default Client Image**
if one is set, and to a lettered circle otherwise, so the layout never breaks.

### Rating

Click a star in **Testimonial Details**, or tab to the control and use the
arrow keys. Ratings run 1–5 and default to 5.

### Categories

Assign one or more **Testimonial Categories** in the sidebar, then filter by
category in the widget or the shortcode.

### Display order

Set **Order** under Page Attributes (0, 1, 2 …) and order the grid by *Display
Order*. Lower numbers come first.

### Duplicating

Hover a row on the Testimonials list and click **Duplicate**. You get a draft
copy with the same text, meta, image and categories.

---

## Elementor widget

Search for **Testimonials Grid** in the Elementor panel, under the
**Testimonial Manager** category.

**Content → Query** — number of testimonials, order by (display order, date,
client name, rating, random), order, category, featured only.

**Content → Layout** — columns for desktop / laptop / tablet / mobile, card
spacing. Laptop applies at 1300px and below; leave it empty to use the desktop
count.

### Breakpoints

| Screen | Applies at | Falls back to |
|---|---|---|
| Desktop | above 1300px | — |
| Laptop | 1300px and below | Desktop, when left empty |
| Tablet | 1024px and below | — |
| Mobile | 767px and below | — |

**Content → Display** — show or hide the rating, client image, position,
company and button; button text; short text length; the heading tag used for
the client name.

**Style → Card** — background, border colour and width, radius, box shadow,
padding, client image size.

**Style → Typography** — colour, font and size for the stars, testimonial text,
client name and position/company, plus text alignment.

**Style → Button** — typography, normal and hover colours, border, radius,
padding.

**Style → Popup** — max width, background, overlay colour, text colour and
size, radius, padding, close button position, size and colours.

### A note on popup styling

One popup is shared by every testimonial on the page rather than one being
built per testimonial. Popup settings are stored on the widget and applied to
that shared popup when a card from that widget opens it, so two grids on one
page can each style their own popup.

---

## Testimonials Slider (banner)

A second widget, **Testimonials Slider**, shows one testimonial at a time on a
translucent panel - built for a banner or hero area. It defaults to *Date
Published* / *Newest First*, so the latest testimonial leads and the rest follow.

**Content → Query** - number of slides, order by, order, category, featured only.

**Content → Slider** - autoplay speed in milliseconds (0 turns it off),
transition (fade or slide), pause on hover, arrows, dots.

**Content → Display** - rating, client image, position, company, full text or a
word limit, default client image.

**Style** - panel background, radius, padding, minimum height, client image
size, star colour and size, testimonial and client name colour and typography,
arrow and dot colours.

Behaviour:

- Autoplay pauses on hover, while focus is inside it, and when the browser tab
  is hidden.
- Arrow keys move between slides; arrows, dots, swipe and keyboard all work.
- Slides are stacked in one grid cell, so the panel is as tall as the longest
  testimonial and nothing below it shifts as slides change.
- Autoplay does not run at all under `prefers-reduced-motion`.

Shortcode form:

```
[testimonials_slider count="5" autoplay="6000" effect="fade" dots="true"]
```

It takes the same attributes as `[testimonials]` plus `autoplay`, `effect`
(`fade` or `slide`), `arrows`, `dots`, `pause_hover` and `full_text`.

## Shortcode

```
[testimonials]
```

With attributes:

```
[testimonials count="6" columns="3" featured="false" orderby="menu_order"]
```

| Attribute | Default | Accepts |
|---|---|---|
| `count` | `6` | Any number. `-1` shows all |
| `columns` | `3` | 1–6 |
| `columns_laptop` | *(inherits desktop)* | 1–6, applies at 1300px and below |
| `columns_tablet` | `2` | 1–6 |
| `columns_mobile` | `1` | 1–6 |
| `gap` | `24` | Spacing between cards, in px |
| `orderby` | `menu_order` | `menu_order`, `date`, `title`, `rating`, `rand` |
| `order` | `ASC` | `ASC`, `DESC` |
| `featured` | `false` | `true`, `false` |
| `category` | *(empty)* | Category slug, or several comma separated |
| `excerpt_words` | `32` | 5–200 |
| `show_rating` | `true` | `true`, `false` |
| `show_image` | `true` | `true`, `false` |
| `show_company` | `true` | `true`, `false` |
| `show_position` | `true` | `true`, `false` |
| `show_button` | `true` | `true`, `false` |
| `button_text` | `READ FULL REVIEW` | Any text |
| `fallback_image` | *(empty)* | URL used when a testimonial has no Featured image |
| `title_tag` | `h3` | `h2`–`h6`, `div`, `p` |
| `class` | *(empty)* | Extra CSS class on the grid |

Examples:

```
[testimonials count="3" featured="true"]
[testimonials category="maritime-law" columns="2" orderby="rating" order="DESC"]
[testimonials count="-1" show_button="false" excerpt_words="60"]
```

Several grids can appear on the same page. Each keeps its own settings and
they share one popup.

---

## Accessibility

The plugin follows WCAG 2.2 AA practices:

- Star ratings are announced as "Rated 5 out of 5 stars" instead of five
  repeated star characters.
- The popup is a proper `role="dialog"` with `aria-modal`, focus moved into it
  on open, focus trapped inside it, and focus returned to the button that
  opened it on close.
- Escape closes the popup, as does clicking the overlay.
- Background scrolling is locked while the popup is open.
- Visible focus outlines throughout, with no keyboard traps.
- Animations are disabled under `prefers-reduced-motion`.
- Semantic markup: `<article>`, `<blockquote>`, real `<button>` elements, and a
  configurable heading level for the client name so the page outline stays
  correct.

Installing this plugin does not by itself make a website ADA compliant. It
applies accessibility best practices to the markup it generates.

## Performance

- CSS and JS load only on pages where a grid actually renders.
- No jQuery, no external libraries, no CDN requests.
- No AJAX. Full testimonials ship inside inert `<template>` elements, so the
  popup opens instantly and works behind full-page caching.
- Client images use the cropped `tm_avatar` size, not full-size uploads.

## Security

- Nonce plus `current_user_can( 'edit_post' )` on every save and on duplicate.
- Every field has a typed sanitiser; the rating is clamped to 1–5.
- Output is escaped at the point of use, and the full testimonial passes
  through `wp_kses_post`.
- No custom SQL anywhere — all reads go through `WP_Query`.

## Uninstall

Deleting the plugin leaves your testimonials in place. To erase all
testimonials, categories and meta, add this to `wp-config.php` before deleting:

```php
define( 'TM_REMOVE_ALL_DATA', true );
```

## Structure

```
testimonial-manager/
├── testimonial-manager.php          Plugin header, constants, bootstrap
├── uninstall.php
├── README.md
├── includes/
│   ├── class-tm-post-type.php       CPT, taxonomy, admin columns, duplicate
│   ├── class-tm-meta-fields.php     Meta box, sanitising, saving
│   ├── class-tm-query.php           Argument normalising and WP_Query building
│   ├── class-tm-renderer.php        All front-end markup
│   ├── class-tm-shortcode.php       [testimonials]
│   ├── class-tm-assets.php          Conditional CSS/JS loading
│   ├── class-tm-elementor.php       Elementor registration, guarded
│   └── widgets/
│       └── class-tm-widget-grid.php Testimonials Grid widget
├── admin/css/testimonial-manager-admin.css
└── public/
    ├── css/testimonial-manager.css
    └── js/testimonial-manager.js
```

The Elementor widget and the shortcode both call `TM_Renderer::grid()`, so
their output can never drift apart.

## Changelog

### 1.0.0
Initial release.
