# MKM Review Funnel

A self-contained WordPress plugin for the "Review Us" flow: it sends happy clients to your
public review profiles and captures unhappy ones privately, storing every submission in its
own database table instead of leaving it inside a form plugin.

Ships as a shortcode **and** a native WPBakery Page Builder element, so the page can be
styled in the builder exactly like the rest of the site.

## What it replaces

The current `/review-us/` page is built from hardcoded popups in `total-child-theme`, a set
of `.open-popup` links inside a WPBakery text block, and Gravity Forms form #1 ("contact
us") embedded in the negative-experience popup. That setup has three problems this plugin
fixes:

1. Negative feedback lands in the same Gravity Forms entry list as ordinary contact-us
   submissions, with no field marking where it came from.
2. Nothing at all is recorded when someone takes the positive path, so there is no way to
   know how many people were sent to Google.
3. The popup markup is printed on **every** page of the site, not just `/review-us/`, which
   renders and loads a Gravity Form site-wide.

## Installation

1. Copy the `mkm-review-funnel` folder into `wp-content/plugins/`, or upload the zip via
   **Plugins → Add New → Upload Plugin**.
2. Activate it. The entry table is created on activation.
3. Go to **Review Funnel → Settings** and paste your Google review URL. Use the "write a
   review" link from the Google Business Profile so visitors land straight on the review box.
4. Edit the `/review-us/` page with WPBakery and add the **Review Funnel** element, or drop
   `[mkm_review_funnel]` into any text block.
5. Remove the old popup markup from the child theme and the two `.open-popup` links from the
   page so the flows do not run side by side.

## Gated vs. open mode

* **Open** (default) — everyone sees both the public review links and the private feedback
  form and picks for themselves.
* **Gated** — visitors are asked whether their experience was positive or negative first,
  and only positive answers see the review links.

Gated is what the site does today. It is also what Google's review policy prohibits:
selectively soliciting reviews from people you expect to be positive is against their
prohibited-content rules and puts the Business Profile at risk. Open mode gets the same
result without the exposure. The setting is per-site with a per-element override, so you can
change your mind without touching code.

## Shortcode

```
[mkm_review_funnel]
[mkm_review_funnel mode="gated" heading="Review Us" align="center"]
```

| Attribute | Default | Notes |
| --- | --- | --- |
| `mode` | site setting | `open` or `gated` |
| `heading` | site setting | Empty inherits the setting |
| `intro` | site setting | Empty inherits the setting |
| `positive_label` / `negative_label` | site setting | Gated mode only |
| `review_label` / `feedback_label` | site setting | Open mode only |
| `align` | `center` | `left`, `center`, `right` |
| `class` | — | Extra CSS classes |

## Where the data goes

Everything is written to `{prefix}mkm_review_feedback`:

* **Feedback rows** — name, email, phone, client status, message, the consent flag *and a
  snapshot of the disclaimer wording that was agreed to*, plus source URL, referrer, user
  agent, timestamp and status (`new` / `read` / `archived`).
* **Click rows** — one lightweight row each time someone clicks through to a review
  profile, so the funnel can finally be measured.

A custom table rather than a custom post type: these are private records, not content. They
stay out of the posts table, out of search, out of the REST API and out of sitemaps, and
reporting stays fast.

Read them under **Review Funnel → Entries** (filter, search, mark read, archive, delete,
export CSV). Every new feedback submission also emails the recipients configured in
settings, with `Reply-To` set to the submitter so staff can answer directly.

## Importing existing Gravity Forms entries

If Gravity Forms is active, **Review Funnel → Settings** shows an importer. Enter the form
ID used by the old popup (form **1** on this site) and it copies those entries into the
plugin's table. Entries are copied, not moved — nothing is removed from Gravity Forms.

## Security

* Public writes go through the REST API with a nonce that is fetched from its own
  uncached endpoint, so WP Engine's full-page cache can never serve a stale one.
* Every value is sanitised on input (`sanitize_text_field`, `sanitize_email`,
  `sanitize_textarea_field`) and escaped on output (`esc_html`, `esc_attr`, `esc_url`),
  including attributes coming from the WPBakery editor.
* Every query goes through `$wpdb->prepare()`; ordering columns are whitelisted, never
  interpolated.
* Admin actions check both `current_user_can()` and a nonce. The CSV export is
  capability-gated and escapes leading `=`, `+`, `-` and `@` so a submitted message cannot
  become a spreadsheet formula.
* Spam: honeypot field, server-signed minimum fill-in time, per-IP hourly rate limit, and
  optional reCAPTCHA v3 or Cloudflare Turnstile.
* IP addresses are hashed by default; full or no storage are both options.
* Registers WordPress's personal-data exporter and eraser, so GDPR/CCPA requests cover
  this data too.
* Uninstall keeps the data unless you tick the opt-in box in settings.

## Known issue on the current site

The Gravity Forms popup on `mkmlawdev.wpenginepowered.com` shows *"ERROR for site owner:
Invalid domain for site key"*. The reCAPTCHA key is registered for the production domain
only, so the captcha cannot render on the dev host and the form cannot be submitted there.
Add the dev domain in the reCAPTCHA admin console, or use separate keys per environment.
This plugin's captcha settings are per-site for the same reason.

## Requirements

WordPress 5.8+, PHP 7.4+. WPBakery is optional — without it the shortcode still works.
