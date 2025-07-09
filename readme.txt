=== Measuremate – GA4 Audit, Track, Reports & Insights ===
Contributors: JubatusAI Labs Pvt Ltd., Taggrs
Tags: woocommerce, google analytics, ga4, e-commerce analytics, enhanced conversions
Requires at least: 4.5
Tested up to: 6.8
Stable tag: 1.1.6
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate GA4 with WooCommerce using client tracking for accurate insights and enhanced e-commerce analytics.

== Description ==

Measuremate is your all-in-one Google Analytics™ 4 (GA4) expert. Measuremate Woocomerce/WordPress Plugin integrates GTM with WooCommerce Store using client-side tracking for accurate pageview, events and enhanced e-commerce analytics.

**Key Features:**

1. Implement GTM on all pages of your store/website. 
2. Push basic Pageview, events and variables into the DataLayer object to use with GTM tracking.
3. Push all Ecommerce events and variables into the DataLayer object to use with GTM tracking.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/measuremate-ga4-audit-track-reports-insights` or install via WordPress Plugins.
2. Activate the plugin through **Plugins > Installed Plugins**.
3. Navigate to **Tools > Measuremate - GA4 Audit, Track, Reports & Insights** to start using the plugin.

== Frequently Asked Questions ==

= Does this plugin support Google Tag Manager (GTM)? =
Yes. GTM support is built in, and you can auto-push GTM code and required DataLayers using this Plugin.

= Is any coding required? =
No coding is needed. Everything is configurable from the Woocomerce/WordPress admin panel.

== Changelog ==

= 1.0.0 =
* Initial stable release
* Measuremate - Your Personal GA4 Expert

== External Services ==

This plugin integrates with the Measuremate Web App to provide enhanced GA4 insights and support within the WordPress admin interface.

### 🧩 Measuremate Web App (iframe)
- **Purpose**: Embeds the app at `https://app.themeasuremate.com` to offer setup guidance, real-time analytics previews, and configuration help.
- **When it's used**: The iframe loads when the admin settings page is opened.
- **Data sent**: 
  - **None** by this plugin itself—no admin or site data is directly transmitted.
  - The embedded app may collect data **provided directly** by the user (e.g., email, name, payment details) during registration or account actions.
- **Provider contact**:
  - **Privacy Policy**: https://themeasuremate.com/privacy.html :contentReference[oaicite:6]{index=6}  
  - **Terms of Service**: https://themeasuremate.com/terms.html :contentReference[oaicite:7]{index=7}

---

### 🔗 Google Tag Manager (GTM)
- **Purpose**: Enables client-side GA4 tracking via DataLayer pushes for events.
- **When it's used**: Only after the admin adds their GTM container ID.
- **Data sent**: Standard ecommerce event data (page views, cart actions, purchases) directly from end-users' browsers to Google servers.
- **Provider policies**:
  - **Privacy Policy**: https://policies.google.com/privacy  
  - **Terms of Service**: https://marketingplatform.google.com/about/analytics/tag-manager/use-policy/
