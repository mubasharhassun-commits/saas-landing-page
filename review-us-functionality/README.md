# MKM Review Us Functionality

A self-contained WordPress plugin that turns the existing footer **Review Us** button into a full
review funnel:

```
Footer "Review Us" button
        ↓
/review-us/  →  👍 Thumbs Up  →  Popup 1 (thank you + Google logo)  →  Popup 2 (sign-in notice + CTA)  →  Google Business Profile
             →  👎 Thumbs Down →  Private feedback popup → stored in WordPress admin
```

## Files

```
review-us-functionality/
├── review-us-functionality.php      Bootstrap, activation, admin notice
├── uninstall.php                    Optional data removal
├── includes/
│   ├── class-review-settings.php    Settings API screen (Settings → Review Us)
│   ├── class-review-feedback.php    Custom table, AJAX handler, Review Feedback admin, CSV export
│   └── class-review-page.php        Shortcode, assets, popups, footer menu integration
└── assets/
    ├── css/review-us.css
    ├── js/review-us.js              Popups, focus trap, AJAX form
    ├── js/review-us-footer.js       Optional page-builder footer fallback
    └── images/google-logo.svg
```

## Install

1. Upload the `review-us-functionality` folder to `wp-content/plugins/`.
2. Activate **MKM Review Us Functionality**.
   On activation the plugin reuses an existing `/review-us/` page if there is one, otherwise it
   creates a published page titled *Review Us* containing `[mkm_review_us]`.
3. If the page already existed, edit it and add the shortcode `[mkm_review_us]` where the funnel
   should appear (remove the old thumbs up / thumbs down markup so it is not duplicated).
4. Go to **Settings → Review Us** and fill in the Google Review URL.

## Settings → Review Us

| Field | Purpose |
| --- | --- |
| Google Review URL | Where the *Review Us on Google* CTA sends the visitor. Use the "Ask for reviews" short link from Google Business Profile (`https://g.page/r/…/review`) or `https://search.google.com/local/writereview?placeid=…`. |
| Review Us Page | The page holding the shortcode. Used for the footer link and to decide where assets load. |
| Open Google in a new tab | On by default. Recommended: the sign-in flow stays separate and the visitor keeps the site open. Rendered with `rel="noopener noreferrer"`. |
| Footer button fallback | Only needed for page-builder footers. See below. |
| Form source | Automatic, Gravity Forms, Contact Form 7 or the built-in form. |
| Gravity Forms form ID / Contact Form 7 shortcode | Which form renders inside the negative popup. |
| Notification email | Where built-in submissions are emailed. Defaults to the site admin email. |
| Delete data on uninstall | Off by default, so entries survive a reinstall. |

## Connecting the existing footer button

Do not add a second button. Pick the case that matches the footer:

* **WordPress menu item** — nothing to do. `wp_nav_menu_objects` repoints any item titled
  "Review Us" (or already pointing at `/review-us`) to the configured page, using `get_permalink()`,
  so the link follows a domain change.
* **Elementor / WPBakery / theme template button** — edit the existing button once and set its link
  to the Review Us page. In a theme template use `<?php echo esc_url( mkm_review_us_page_url() ); ?>`.
* **Can't edit the builder link** — enable *Footer button fallback*. A ~1 KB script rewrites the
  `href` of footer links whose text is "Review Us". Off by default because it loads sitewide.

## Feedback storage

* **Gravity Forms** — entries land in **Forms → Entries** as usual.
* **Contact Form 7** — CF7 does not store entries, so the plugin also mirrors each successful
  submission into its own table (`wpcf7_mail_sent`). Field names are matched from
  `first-name`, `last-name`, `your-email`, `phone`, `reason`, `your-message`, `contact-permission`.
* **Built-in form** — validated and stored server side, then emailed to the notification address.

All submissions are visible under **Review Feedback** in the admin menu: paginated table, secure
delete (nonce + capability check), and CSV export.

Table: `{$wpdb->prefix}review_feedback`, created with `dbDelta()` on activation and re-checked only
after a version bump in the admin — never on a front-end request.

## Spam protection

The built-in form uses a honeypot field, a three-second time trap and a per-IP hourly rate limit
(5 submissions, IP hashed and not stored). Gravity Forms and Contact Form 7 keep whatever
anti-spam they are already configured with — nothing new is added.

## Notes

* Assets load only on the Review Us page (matched by page ID or `has_shortcode`), versioned with the
  plugin version for cache busting.
* Popup z-index is `99000` via the `--mkm-review-z` custom property in `review-us.css`. Raise it there
  if a sticky header or accessibility widget overlaps.
* Brand colours live in the custom properties at the top of `review-us.css`.
* With a full-page cache, the AJAX nonce can go stale after 24 hours; the form then shows
  "Your session expired. Please reload the page." Exclude `/review-us/` from page caching to avoid it.

## Testing

1. Footer button → lands on `/review-us/`, no duplicate button.
2. Thumbs up → Popup 1 → click the Google logo → Popup 2 → CTA opens the configured URL.
3. Thumbs down → feedback popup, submit empty (validation), submit valid (success message, popup
   stays open until the visitor closes it).
4. **Review Feedback** shows the entry; CSV export downloads; delete works.
5. Keyboard only: Tab to each option, Enter opens, focus lands in the dialog, Tab cycles inside it,
   Esc closes, focus returns to the button that opened it.
6. Mobile: options stack, popups fit the viewport and scroll, no horizontal scroll, background is
   locked while a popup is open and the scroll position is restored on close.
