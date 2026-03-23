=== Replanta Connector with SAP for WooCommerce ===
Contributors: replanta
Donate link: https://replanta.net/
Tags: sap, sap business one, woocommerce, erp, inventory
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.17
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce with SAP Business One. Stock and prices sync via Service Layer API -- set it once, it just works.

== Description ==

**Replanta Connector with SAP for WooCommerce** connects your WooCommerce store with SAP Business One, keeping your inventory stock and prices synchronized -- set it up once and forget about it.

= Key Features =

* **Stock Sync** - WooCommerce stock quantities update from SAP Business One on your schedule
* **Price Sync** - Product prices stay in sync with your SAP price lists -- always up to date
* **Multiple Warehouses** - Aggregate stock from selected SAP warehouses
* **Scheduled Sync** - Set your schedule (hourly, twice daily, or daily) and forget about it
* **One-click Sync** - Need an immediate update? One click and it's done
* **Activity Logs** - Track every sync operation with detailed logging
* **SKU Readiness** - See how many products have SKUs ready for SAP matching

= How It Works =

1. Configure your SAP Business One Service Layer connection
2. Select your price list and warehouses
3. Enable sync -- that's it, you're done
4. Your WooCommerce products update with SAP stock and prices on schedule

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher
* SAP Business One with Service Layer (version 9.3 or higher)
* Products in WooCommerce must have SKUs matching SAP ItemCodes

= Upgrade to PRO =

Want your entire business connected to SAP? **SAP Woo Suite PRO** handles everything:

* Full product import from SAP (with attributes, images, variations)
* Order sync to SAP (Sales Orders created instantly)
* Customer sync (Business Partners created from WooCommerce customers)
* Category import from SAP item groups
* Field mapping (including UDFs)
* REST API endpoints for integrations
* Multi-channel support (TikTok Shop, Amazon, Miravia)
* Smart retry for failed orders (intelligent backoff)
* Priority support

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

= What SAP Business One version is required? =

SAP Business One 9.3 or higher with Service Layer enabled. Both HANA and SQL Server versions are supported.

= Do products need to exist in WooCommerce first? =

Yes, Replanta Connector with SAP for WooCommerce syncs stock and prices to existing WooCommerce products. Products must have SKUs that match the ItemCode in SAP. For full product import, upgrade to PRO.

= Can I sync orders to SAP? =

Order sync is available in SAP Woo Suite PRO. The Lite version only syncs stock and prices from SAP to WooCommerce.

= Is SSL required? =

SSL verification is recommended for production environments but can be disabled for testing with self-signed certificates.

= How often does it sync? =

You can choose hourly, twice daily, or daily sync. Once configured, it runs in the background. One-click sync is always available if you need an immediate update. PRO offers intervals as fast as every 5 minutes.

== Screenshots ==

1. Dashboard - Visual overview of your sync status, product health and SKU readiness
2. Connection settings - Configure your SAP Service Layer connection
3. Sync settings - Select price list, warehouses, and schedule
4. PRO features - See what's available with the full version

== Changelog ==

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
