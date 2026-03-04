<?php
/**
 * Uninstall SAP Woo Suite Lite
 *
 * Fired when the plugin is uninstalled.
 * IMPORTANT: Does NOT delete shared options with PRO version to preserve
 * settings when user upgrades to PRO.
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

// Exit if not called by WordPress uninstaller
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if PRO version is active - if so, don't delete anything
if (is_plugin_active('sap-woo-suite/sap-woo-suite.php')) {
    return;
}

// Only remove Lite-specific options (NOT shared options with PRO)
// This preserves sapwc_connections, sapwc_selected_tariff, etc. for PRO upgrade
$lite_only_options = [
    'sapwc_lite_installed',
    'sapwc_lite_version',
];

foreach ($lite_only_options as $option) {
    delete_option($option);
}

// Remove logs table - use same table name as PRO for shared logs
global $wpdb;
$table = $wpdb->prefix . 'sapwc_logs';

// Only delete if PRO is not installed (check by file existence)
$pro_path = WP_PLUGIN_DIR . '/sap-woo-suite/sap-woo-suite.php';
if (!file_exists($pro_path)) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
    
    // If PRO is not installed, also clean shared options
    // User can re-configure if they ever install Lite or PRO again
    $shared_options = [
        'sapwc_connections',
        'sapwc_connection_index',
        'sapwc_selected_tariff',
        'sapwc_selected_warehouses',
        'sapwc_sync_stock_auto',
        'sapwc_sync_prices_auto',
        'sapwc_stock_sync_interval',
        'sapwc_stock_last_sync',
    ];
    
    foreach ($shared_options as $option) {
        delete_option($option);
    }
}

// Clear scheduled events
wp_clear_scheduled_hook('sapwc_lite_cron_sync');
wp_clear_scheduled_hook('sapwc_lite_daily_cleanup');
