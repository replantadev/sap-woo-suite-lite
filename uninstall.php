<?php
/**
 * Uninstall SAP Woo Suite Lite
 *
 * Fired when the plugin is uninstalled.
 * Cleans up options and database tables created by the plugin.
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

// Exit if not called by WordPress uninstaller
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove options
$options = [
    'sapwc_lite_installed',
    'sapwc_lite_version',
    'sapwc_lite_sap_url',
    'sapwc_lite_sap_user',
    'sapwc_lite_sap_pass',
    'sapwc_lite_sap_db',
    'sapwc_lite_sap_ssl',
    'sapwc_lite_price_list',
    'sapwc_lite_warehouses',
    'sapwc_lite_sync_stock',
    'sapwc_lite_sync_prices',
    'sapwc_lite_auto_sync',
    'sapwc_lite_sync_interval',
    'sapwc_lite_last_sync',
];

foreach ($options as $option) {
    delete_option($option);
}

// Remove logs table
global $wpdb;
$table = $wpdb->prefix . 'sapwc_lite_logs';
$wpdb->query("DROP TABLE IF EXISTS $table");

// Clear scheduled events
wp_clear_scheduled_hook('sapwc_lite_cron_sync');
wp_clear_scheduled_hook('sapwc_lite_daily_cleanup');
