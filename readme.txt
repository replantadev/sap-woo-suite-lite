=== SAP Woo Suite Lite - SAP Business One Integration for WooCommerce ===
Contributors: replantadev
Tags: sap, sap business one, woocommerce, erp, inventory sync
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce with SAP Business One. Sync stock and prices automatically via Service Layer API.

== Description ==

**SAP Woo Suite Lite** connects your WooCommerce store with SAP Business One, keeping your inventory stock and prices synchronized automatically.

= Key Features =

* **Stock Sync** - Automatically update WooCommerce product stock quantities from SAP B1
* **Price Sync** - Keep product prices in sync with your SAP price lists
* **Multiple Warehouses** - Aggregate stock from selected SAP warehouses
* **Scheduled Sync** - Set up automatic hourly, twice daily, or daily synchronization
* **Manual Sync** - One-click sync whenever you need it
* **Activity Logs** - Track all sync operations with detailed logging

= How It Works =

1. Configure your SAP Business One Service Layer connection
2. Select your price list and warehouses
3. Enable automatic sync or sync manually
4. Your WooCommerce products are updated with SAP stock and prices

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher
* SAP Business One with Service Layer (version 9.3 or higher)
* Products in WooCommerce must have SKUs matching SAP ItemCodes

= Upgrade to PRO =

Need more features? **SAP Woo Suite PRO** includes:

* Full product import from SAP (with attributes, images, variations)
* Order sync to SAP (automatic Sales Order creation)
* Customer sync (Business Partner creation)
* Category import from SAP item groups
* Advanced field mapping (including UDFs)
* REST API endpoints for integrations
* Multi-channel support (TikTok Shop, Amazon, Miravia)
* Auto-retry failed orders
* Priority support

[Learn more about SAP Woo Suite PRO](https://replanta.dev/sap-woo-suite)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/sap-woo-suite-lite` or install through WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **SAP Woo Lite > Settings** to configure your SAP connection
4. Enter your Service Layer URL, username, password, and company database
5. Test the connection
6. Go to the **Sync Settings** tab to configure price list and warehouses
7. Enable automatic sync or click "Sync Now" to sync manually

== Frequently Asked Questions ==

= What SAP Business One version is required? =

SAP Business One 9.3 or higher with Service Layer enabled. Both HANA and SQL Server versions are supported.

= Do products need to exist in WooCommerce first? =

Yes, SAP Woo Suite Lite syncs stock and prices to existing WooCommerce products. Products must have SKUs that match the ItemCode in SAP. For full product import, upgrade to PRO.

= Can I sync orders to SAP? =

Order sync is available in SAP Woo Suite PRO. The Lite version only syncs stock and prices from SAP to WooCommerce.

= Is SSL required? =

SSL verification is recommended for production environments but can be disabled for testing with self-signed certificates.

= How often does it sync? =

You can choose hourly, twice daily, or daily automatic sync. Manual sync is always available.

== Screenshots ==

1. Connection settings - Configure your SAP Service Layer connection
2. Sync settings - Select price list and warehouses
3. PRO features - See what's available in the full version

== Changelog ==

= 1.0.0 - 2026-03-04 =
* Initial release
* Stock sync from SAP warehouses
* Price sync from SAP price lists
* Manual and automatic sync options
* Activity logging
* PRO features preview page

== Upgrade Notice ==

= 1.0.0 =
Initial release. Connect your WooCommerce store with SAP Business One.
