=== Replanta Connector with SAP for WooCommerce ===
Contributors: replanta
Donate link: https://replanta.net/
Tags: sap, sap business one, woocommerce, erp, stock sync
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.19
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync WooCommerce stock and prices with SAP Business One via Service Layer. Automatic, scheduled integration -- configure once, runs forever.

== Description ==

**Replanta Connector with SAP for WooCommerce** is the easiest way to keep your WooCommerce store in sync with SAP Business One. Connect via the SAP Service Layer API, choose your price list and warehouses, enable sync -- done.

No middleware. No complex setup. No recurring manual work.

= Key Features =

* **Stock Sync** -- WooCommerce stock quantities update automatically from SAP Business One
* **Price Sync** -- Product prices stay in sync with your SAP price list, always up to date
* **Multiple Warehouses** -- Aggregate stock from one or more SAP warehouses
* **Scheduled Sync** -- Runs automatically every hour, twice daily, or daily via WP-Cron
* **One-click Sync** -- Trigger an immediate sync any time from the WordPress admin
* **Sync Dashboard** -- Visual overview of sync health, SKU readiness, and recent activity
* **Activity Logs** -- Every sync operation logged with status and message
* **SKU Readiness Check** -- See instantly how many of your products are ready for SAP matching
* **HPOS Compatible** -- Supports WooCommerce High Performance Order Storage

= How It Works =

Products are matched by SKU: the WooCommerce product SKU must equal the ItemCode in SAP Business One. Once matched, stock and prices flow from SAP to WooCommerce on your chosen schedule.

1. Enter your SAP Business One Service Layer URL, credentials, and company database
2. Test the connection -- it shows you live price lists and warehouses from your SAP
3. Choose your price list and warehouses
4. Enable sync and pick a schedule
5. That's it -- your store stays in sync automatically

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher
* SAP Business One 9.3 or higher with Service Layer enabled (HANA or SQL Server)
* WooCommerce products must have SKUs matching SAP ItemCodes

= Upgrade to PRO =

Need your entire business connected to SAP? **SAP Woo Suite PRO** goes further:

* **Full product import** from SAP -- attributes, images, categories, variations
* **Order sync to SAP** -- every WooCommerce order creates a Sales Order in SAP automatically
* **Customer sync** -- Business Partners created and updated from WooCommerce customers
* **Category import** from SAP item groups
* **Field mapping** -- map any SAP field (including UDFs) to WooCommerce attributes
* **REST API endpoints** for external integrations and webhooks
* **Multi-channel** -- TikTok Shop, Amazon, Miravia via SAP Woo Suite addons
* **Smart retry** -- failed syncs retry automatically with intelligent backoff
* **Priority support**

[Get SAP Woo Suite PRO](https://replanta.net/conector-sap-woocommerce/)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/replanta-connector-sap-woocommerce` or install through WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **SAP Woo Lite > Settings** to configure your SAP connection
4. Enter your Service Layer URL, username, password, and company database
5. Test the connection
6. Go to the **Sync Settings** tab to configure price list and warehouses
7. Enable sync -- your store is now connected to SAP

== Frequently Asked Questions ==

= What SAP Business One version do I need? =

SAP Business One 9.3 or higher with Service Layer enabled. Both HANA and SQL Server editions are supported.

= Does it work with WPML or multilingual stores? =

Yes. The sync works on product level by SKU regardless of language. The plugin admin interface has a full Spanish translation included, and other languages can be added via translate.wordpress.org.

= Do products need to exist in WooCommerce first? =

Yes. This plugin syncs stock and prices to existing WooCommerce products. Products must have SKUs matching the ItemCode in SAP. For full product import from SAP (create products automatically), upgrade to PRO.

= Can it sync orders to SAP? =

Order sync is available in SAP Woo Suite PRO. The Lite version only syncs stock and prices from SAP to WooCommerce.

= My products don't have SKUs -- what do I do? =

The plugin includes a SKU Readiness dashboard that shows you exactly how many products are missing SKUs. You'll need to add SKUs to your WooCommerce products that match the ItemCodes in SAP before sync will work for those products.

= Is SSL required for the Service Layer connection? =

SSL verification is recommended for production. You can disable certificate verification for testing environments with self-signed certificates.

= How often can it sync? =

The Lite version supports hourly, twice daily, or daily automatic sync via WP-Cron, plus on-demand one-click sync. For intervals as low as every 5 minutes, upgrade to PRO.

= Will it slow down my website? =

No. Sync runs in the background via WP-Cron and has no impact on your store's frontend performance.

= Is it compatible with WooCommerce HPOS? =

Yes, the plugin declares full compatibility with WooCommerce High Performance Order Storage (HPOS).

= Where can I get help? =

Post in the [support forum](https://wordpress.org/support/plugin/replanta-connector-sap-woocommerce/) or visit [replanta.net](https://replanta.net/) for documentation and PRO support.

== Screenshots ==

1. Dashboard -- Visual overview of sync health, SKU readiness, and recent activity
2. Connection settings -- Configure your SAP Service Layer URL, credentials and company database
3. Sync settings -- Select price list, warehouses, sync options and schedule

== Changelog ==

= 1.2.19 =
* Fix: Translation files renamed to match plugin text domain -- Spanish (es_ES) translations now load correctly
* Updated Spanish translation headers and removed obsolete strings
* Improved readme.txt with expanded FAQ and feature descriptions

= 1.2.18 =
* Security: sanitize password field with sanitize_text_field() per WP.org guidelines
* Removed disabled PRO sync interval options (trialware guideline compliance)
* Exposed log action/status filters in the Lite logs page (were incorrectly marked as PRO-only)

= 1.2.17 =
* Rename: Plugin renamed to Replanta Connector with SAP for WooCommerce for WordPress.org compliance

= 1.2.3 - 2026-03-05 =
* Fixed license file to match GPL-2.0-or-later declaration
* Improved output escaping across dashboard and settings pages
* Replaced json_encode with wp_json_encode for WP coding standards
* Version consistency fix across all plugin files

= 1.2.0 - 2026-03-05 =
* New Dashboard with sync health, SKU readiness, and visual charts
* Full i18n support -- English source strings with Spanish translation
* Toned down marketing copy across all pages
* Fixed lock icon display on PRO teaser KPI card
* Lite-only log filtering (no more PRO log cross-contamination)
* Log action renamed to stock_sync for clarity
* Added SKU readiness metric to dashboard configuration table
* Version bump to 1.2.0

= 1.1.0 - 2026-03-05 =
* New design system - modern, elegant admin UI with CSS custom properties
* Unified visual language with SAP Woo Suite PRO
* Redesigned tabs, form inputs, buttons, and log badges
* PRO Features page styles moved from inline to external CSS
* Responsive improvements for mobile admin

= 1.0.1 - 2026-03-04 =
* Added SKU matching info in sync settings UI
* Hide other plugin notices on our admin pages
* Fixed version constant mismatch

= 1.0.0 - 2026-03-04 =
* Initial release
* Stock sync from SAP warehouses
* Price sync from SAP price lists
* Manual and automatic sync options
* Activity logging
* PRO features preview page

== Upgrade Notice ==

= 1.2.19 =
Important fix: Spanish translations now load correctly. Update recommended for all Spanish-language WordPress sites.

= 1.2.18 =
Security and guideline compliance update. Update recommended.

= 1.2.17 =
Plugin renamed to Replanta Connector with SAP for WooCommerce for WordPress.org compliance. No configuration changes required.

= 1.2.3 =
Coding standards and license compliance improvements for WP.org submission.

= 1.1.0 =
New design system with modern, elegant admin UI.

= 1.0.1 =
Added SKU matching info and UI improvements.

= 1.0.0 =
Initial release. Connect your WooCommerce store with SAP Business One.
