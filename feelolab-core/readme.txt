=== FeeloLab Core ===
Contributors: feelolab
Tags: business, services, contact form, schema, woocommerce
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content types, business settings, contact form, schema and privacy tools for small business sites. Works with any theme.

== Description ==

FeeloLab Core keeps a business site's content in a plugin, so it survives a change of theme.

* Content types you turn on as needed: services, products, projects, team, testimonials, FAQ, locations and clients, each with its own categories.
* Site settings in one place: business name and description, phone, WhatsApp, email, address, opening hours and social profiles.
* Contact form that works with page cache: signed timestamp, honeypot, rate limit and optional Cloudflare Turnstile. Messages are stored in the admin (Messages) and emailed.
* Newsletter sign-up, stored locally or sent to Brevo or Mailchimp.
* Structured data (LocalBusiness, Product, FAQ, Breadcrumbs) and meta descriptions, plus an llms.txt file.
* Cookie banner with Google Consent Mode v2, and optional Google Analytics 4 or Google Tag Manager with events (WhatsApp, phone, email, form, share).
* Privacy: personal data exporter and eraser for stored messages, automatic deletion after the retention period you choose, and suggested text for the privacy policy.
* CSV importer for services, products and more (with WooCommerce support), with preview and image download.
* First steps wizard: creates the Home, Contact and Blog pages, the menu, pretty permalinks and the privacy page.
* Launch checklist that reviews what is missing before going live.
* New images are saved as WebP (can be turned off).

The interface is written in Spanish and includes an English (en_US) translation.

== Installation ==

1. Install from Plugins → Add New Plugin, or upload the zip, and activate it.
2. Follow the notice to open Site settings → First steps, or go straight to Site settings.

== Frequently Asked Questions ==

= Why don't the contact and newsletter forms use a WordPress nonce? =

Business sites are usually behind a page cache. A cached page carries an expired nonce and the form would fail silently hours later. The forms use a signed timestamp (with the site's secret keys), a honeypot field, a per-IP rate limit and, optionally, Cloudflare Turnstile. No logged-in action is taken from these forms: they only store a message or a subscription.

= Does it work with page cache? =

Yes. Forms, the cookie banner and tracking work on cached pages.

= What is deleted when I uninstall the plugin? =

Settings, caches and scheduled tasks. Stored messages and subscriptions are deleted only if you check that option in Site settings → Form. Your content (services, products, team…) is never deleted.

== External services ==

The plugin connects to these services only when you turn on the feature and enter its credentials. Nothing is sent otherwise.

Cloudflare Turnstile (spam protection for the contact form): when you enter a Turnstile site key, the form page loads https://challenges.cloudflare.com/turnstile/v0/api.js, and on submit the server sends the challenge token and the visitor's IP address to https://challenges.cloudflare.com/turnstile/v0/siteverify. Terms: https://www.cloudflare.com/website-terms/ · Privacy: https://www.cloudflare.com/privacypolicy/

Brevo (newsletter): when you choose Brevo and enter an API key, each sign-up sends the email address to https://api.brevo.com/v3/contacts. Terms: https://www.brevo.com/legal/termsofuse/ · Privacy: https://www.brevo.com/legal/privacypolicy/

Mailchimp (newsletter): when you choose Mailchimp and enter an API key and audience, each sign-up sends the email address to https://<dc>.api.mailchimp.com/3.0/. Terms: https://mailchimp.com/legal/terms/ · Privacy: https://www.intuit.com/privacy/statement/

Google Analytics 4 and Google Tag Manager: when you enter a measurement ID or container ID, pages load https://www.googletagmanager.com/gtag/js or https://www.googletagmanager.com/gtm.js. With the cookie banner on, analytics and ads storage stay denied (Consent Mode v2) until the visitor accepts. Terms: https://marketingplatform.google.com/about/analytics/terms/us/ · Privacy: https://policies.google.com/privacy

CSV importer images: when a CSV row has an image URL, the server downloads that image from the address in the file.

Share and contact links (WhatsApp, Facebook, X, LinkedIn, Google Maps) are plain links: nothing is sent until the visitor clicks them.

GitHub (updates): the plugin and the FeeloLab theme check https://github.com for new versions of themselves (the update.json file of the public releases repository) twice a day, through the regular WordPress update check. No site data is sent. GitHub Terms: https://docs.github.com/site-policy/github-terms/github-terms-of-service · Privacy: https://docs.github.com/site-policy/privacy-policies/github-general-privacy-statement

== Changelog ==

= 0.9.0 =
* See CHANGELOG.md in the repository for the full history.

== Copyright ==

FeeloLab Core, Copyright 2026 FeeloLab.
FeeloLab Core is distributed under the terms of the GNU GPL v2 or later.

Mascot illustrations (assets/img/ardilla-lupa.webp, assets/img/ardilla-megafono.webp): Copyright 2026 FeeloLab, own work, GPLv2 or later.
