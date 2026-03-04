<?php
/**
 * Plugin Name: SAP Woo Suite Lite
 * Plugin URI: https://replanta.dev/sap-woo-suite
 * Description: Connect your WooCommerce store with SAP Business One. Sync stock and prices automatically. Upgrade to PRO for full product sync, orders, field mapping, and more.
 * Version: 1.0.0
 * Author: Replanta Dev
 * Author URI: https://replanta.dev
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sap-woo-suite-lite
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.6
 */

if (!defined('ABSPATH')) {
    exit;
}

// ──────────────────────────────────────────────
// Constants
// ──────────────────────────────────────────────
define('SAPWC_LITE_VERSION', '1.0.0');
define('SAPWC_LITE_PATH', plugin_dir_path(__FILE__));
define('SAPWC_LITE_URL', plugin_dir_url(__FILE__));
define('SAPWC_LITE_FILE', __FILE__);
define('SAPWC_LITE_BASENAME', plugin_basename(__FILE__));

// Pro version detection
define('SAPWC_LITE_IS_PRO_ACTIVE', class_exists('SAPWC_API_Client') || is_plugin_active('sap-woo-suite/sap-woo-suite.php'));

// ──────────────────────────────────────────────
// Declare HPOS compatibility
// ──────────────────────────────────────────────
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// ──────────────────────────────────────────────
// Check if PRO is active - deactivate Lite
// ──────────────────────────────────────────────
add_action('admin_init', function () {
    if (is_plugin_active('sap-woo-suite/sap-woo-suite.php')) {
        deactivate_plugins(SAPWC_LITE_BASENAME);
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>SAP Woo Suite Lite</strong> has been deactivated. ';
            echo 'You have the PRO version active - all your settings have been preserved!</p>';
            echo '</div>';
        });
    }
});

// Also check on plugin activation
register_activation_hook(__FILE__, function () {
    if (is_plugin_active('sap-woo-suite/sap-woo-suite.php')) {
        deactivate_plugins(SAPWC_LITE_BASENAME);
        wp_die(
            'SAP Woo Suite PRO is already active. The Lite version is not needed - your settings will work with PRO.',
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }
    
    // Initialize connection array if not exists (PRO-compatible format)
    if (get_option('sapwc_connections') === false) {
        update_option('sapwc_connections', []);
        update_option('sapwc_connection_index', 0);
    }
});

// ──────────────────────────────────────────────
// Load dependencies
// ──────────────────────────────────────────────
add_action('plugins_loaded', 'sapwc_lite_load_dependencies');
function sapwc_lite_load_dependencies()
{
    // Check WooCommerce
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error">';
            echo '<p><strong>SAP Woo Suite Lite</strong> requires WooCommerce to be installed and activated.</p>';
            echo '</div>';
        });
        return;
    }

    // Core classes
    require_once SAPWC_LITE_PATH . 'includes/class-api-client.php';
    require_once SAPWC_LITE_PATH . 'includes/class-logger.php';
    require_once SAPWC_LITE_PATH . 'includes/class-stock-sync.php';

    // Admin UI
    if (is_admin()) {
        require_once SAPWC_LITE_PATH . 'admin/class-settings-page.php';
        require_once SAPWC_LITE_PATH . 'admin/class-pro-features.php';
    }

    // Initialize stock sync
    SAPWC_Lite_Stock_Sync::init();

    do_action('sapwc_lite_loaded', SAPWC_LITE_VERSION);
}

// ──────────────────────────────────────────────
// Activation hook - setup defaults
// ──────────────────────────────────────────────
register_activation_hook(__FILE__, function () {
    // Set default options if not exist
    if (get_option('sapwc_lite_installed') === false) {
        update_option('sapwc_lite_installed', time());
        update_option('sapwc_lite_version', SAPWC_LITE_VERSION);
    }
});

// ──────────────────────────────────────────────
// Add Settings link on Plugins page
// ──────────────────────────────────────────────
add_filter('plugin_action_links_' . SAPWC_LITE_BASENAME, function ($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=sapwc-lite-settings') . '">Settings</a>';
    $pro_link = '<a href="https://replanta.dev/sap-woo-suite" target="_blank" style="color:#41999f;font-weight:bold;">Upgrade to PRO</a>';
    array_unshift($links, $settings_link, $pro_link);
    return $links;
});

// ──────────────────────────────────────────────
// Helper: Get active SAP connection (PRO-compatible)
// Uses same option names as PRO for seamless upgrade
// ──────────────────────────────────────────────
function sapwc_lite_get_connection()
{
    $connections = get_option('sapwc_connections', []);
    $index = get_option('sapwc_connection_index', 0);

    if (!isset($connections[$index])) {
        return null;
    }

    $conn = $connections[$index];

    // Ensure SSL field exists
    if (!isset($conn['ssl'])) {
        $conn['ssl'] = false;
    }

    if (empty($conn['url']) || empty($conn['user']) || empty($conn['pass']) || empty($conn['db'])) {
        return null;
    }

    return $conn;
}

// ──────────────────────────────────────────────
// Admin bar indicator
// ──────────────────────────────────────────────
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $conn = sapwc_lite_get_connection();
    $status = $conn ? 'configured' : 'not-configured';
    $color = $conn ? '#41999f' : '#dc3545';
    $title = $conn ? 'SAP Lite: Connected' : 'SAP Lite: Not configured';

    $wp_admin_bar->add_node([
        'id'    => 'sapwc-lite-status',
        'title' => '<span style="color:' . $color . ';">&#9679;</span> SAP Lite',
        'href'  => admin_url('admin.php?page=sapwc-lite-settings'),
        'meta'  => ['title' => $title]
    ]);
}, 100);
