=== Measuremate – GA4 Audit, Track, Reports & Insights ===
Contributors: jyotpatelimnu
Tags: ga4, gtm, google analytics, tracking, e-commerce
Requires at least: 4.5
Tested up to: 6.8
Stable tag: 1.1.0
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

= 1.1.0 =
* Fixed Critical Issues
* Fixed issues with wordpress website - plugin does not break your website when installing on wordpress websites that does not support woocommerce.

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

== Upgrade Notice ==
Nothing here.

== Screenshots ==
1. **Measuremate** - is your personal GA4, GTM & BigQuery Expert. It lets you handle everything from event tagging and scheduled reports to AI-based alerts and actionable insights with zero code or spreadsheet wrangling.
2. **1-tool to rule them all** - Manage your entire analytics stack in one place - GA4, GTM, and BigQuery.
3. **Tracking & Pixel Setup** - Effortlessly deploy tracking pixels using prebuilt templates for every major platform. (Including Shopify, Woo-commerce)
4. **Measurement Plan Builder** - Build a full tracking strategy with AI-recommended events and no guesswork. Automate GA4, GTM Configuration & Event Validations. One-click setup for product views, cart, checkout, purchase and 17+ Woo-commerce/Shopify events.
5. **Scheduled Reports** - Schedule automated reports to your favorite tools and stay ahead of KPIs. Bring your data to Figma, Slack, WhatsApp, Email, Sheets, XLS from GA4 or BigQuery.
6. **GA4 Co-pilot** - Talk to your GA4 data without ever struggling to get the reports.
7. **Actionable Insights** - Generate clear insights like funnels and attribution paths, exit paths with zero setup / BigQuery knowledge.