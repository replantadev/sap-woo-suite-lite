<?php
/**
 * SAP Woo Suite Lite
 *
 * Connect WooCommerce with SAP Business One for stock and price synchronization.
 *
 * @package           SAPWC_Lite
 * @author            Replanta Dev
 * @copyright         2024 Replanta
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       SAP Woo Suite Lite
 * Plugin URI:        https://replanta.net/conector-sap-woocommerce/
 * Description:       Connect your WooCommerce store with SAP Business One. Sync stock and prices automatically. Upgrade to PRO for full product sync, orders, field mapping, and more.
 * Version: 1.1.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Replanta Dev
 * Author URI:        https://replanta.net
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sap-woo-suite-lite
 * Domain Path:       /languages
 * WC requires at least: 6.0
 * WC tested up to:   9.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ──────────────────────────────────────────────
// Constants
// ──────────────────────────────────────────────
define( 'SAPWC_LITE_VERSION', '1.1.0' );
define( 'SAPWC_LITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SAPWC_LITE_URL', plugin_dir_url( __FILE__ ) );
define( 'SAPWC_LITE_FILE', __FILE__ );
define( 'SAPWC_LITE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
 *
 * @since 1.0.0
 */
function sapwc_lite_declare_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}
add_action( 'before_woocommerce_init', 'sapwc_lite_declare_hpos_compatibility' );

/**
 * Load plugin text domain for translations.
 *
 * @since 1.0.0
 */
function sapwc_lite_load_textdomain() {
    load_plugin_textdomain(
        'sap-woo-suite-lite',
        false,
        dirname( SAPWC_LITE_BASENAME ) . '/languages'
    );
}
add_action( 'init', 'sapwc_lite_load_textdomain' );

/**
 * Check if PRO version is active and deactivate Lite.
 *
 * @since 1.0.0
 */
function sapwc_lite_check_pro_version() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if ( is_plugin_active( 'sap-woo-suite/sap-woo-suite.php' ) ) {
        deactivate_plugins( SAPWC_LITE_BASENAME );
        add_action( 'admin_notices', 'sapwc_lite_pro_active_notice' );
    }
}
add_action( 'admin_init', 'sapwc_lite_check_pro_version' );

/**
 * Display notice when PRO version is active.
 *
 * @since 1.0.0
 */
function sapwc_lite_pro_active_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php esc_html_e( 'SAP Woo Suite Lite', 'sap-woo-suite-lite' ); ?></strong>
            <?php esc_html_e( 'has been deactivated. You have the PRO version active - all your settings have been preserved!', 'sap-woo-suite-lite' ); ?>
        </p>
    </div>
    <?php
}

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 */
function sapwc_lite_activate() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Check if PRO is active.
    if ( is_plugin_active( 'sap-woo-suite/sap-woo-suite.php' ) ) {
        deactivate_plugins( SAPWC_LITE_BASENAME );
        wp_die(
            esc_html__( 'SAP Woo Suite PRO is already active. The Lite version is not needed - your settings will work with PRO.', 'sap-woo-suite-lite' ),
            esc_html__( 'Plugin Activation Error', 'sap-woo-suite-lite' ),
            array( 'back_link' => true )
        );
    }

    // Initialize connection array if not exists (PRO-compatible format).
    if ( false === get_option( 'sapwc_connections' ) ) {
        update_option( 'sapwc_connections', array() );
        update_option( 'sapwc_connection_index', 0 );
    }

    // Set install timestamp.
    if ( false === get_option( 'sapwc_lite_installed' ) ) {
        update_option( 'sapwc_lite_installed', time() );
        update_option( 'sapwc_lite_version', SAPWC_LITE_VERSION );
    }
}
register_activation_hook( __FILE__, 'sapwc_lite_activate' );

/**
 * Load plugin dependencies after all plugins are loaded.
 *
 * @since 1.0.0
 */
function sapwc_lite_load_dependencies() {
    // Check WooCommerce.
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'sapwc_lite_woocommerce_missing_notice' );
        return;
    }

    // Load core classes.
    require_once SAPWC_LITE_PATH . 'includes/class-api-client.php';
    require_once SAPWC_LITE_PATH . 'includes/class-logger.php';
    require_once SAPWC_LITE_PATH . 'includes/class-stock-sync.php';

    // Load admin UI.
    if ( is_admin() ) {
        require_once SAPWC_LITE_PATH . 'admin/class-settings-page.php';
        require_once SAPWC_LITE_PATH . 'admin/class-pro-features.php';
    }

    // Initialize stock sync.
    SAPWC_Lite_Stock_Sync::init();

    /**
     * Fires when SAP Woo Suite Lite is fully loaded.
     *
     * @since 1.0.0
     * @param string $version Plugin version.
     */
    do_action( 'sapwc_lite_loaded', SAPWC_LITE_VERSION );
}
add_action( 'plugins_loaded', 'sapwc_lite_load_dependencies' );

/**
 * Hide other plugin notices on SAP Woo Suite Lite admin pages.
 *
 * @since 1.0.0
 */
function sapwc_lite_hide_other_notices() {
    $screen = get_current_screen();

    // Our admin page IDs.
    $our_pages = array(
        'toplevel_page_sapwc-lite-settings',
        'sap-woo-suite-lite_page_sapwc-lite-pro-features',
    );

    if ( $screen && in_array( $screen->id, $our_pages, true ) ) {
        // Remove all admin notices.
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'network_admin_notices' );

        // Re-add only our notices on PRO features page.
        if ( 'sap-woo-suite-lite_page_sapwc-lite-pro-features' === $screen->id ) {
            // No notices needed - the page has its own upsell CTA.
        }
    }
}
add_action( 'admin_head', 'sapwc_lite_hide_other_notices', 1 );

/**
 * Display notice when WooCommerce is not active.
 *
 * @since 1.0.0
 */
function sapwc_lite_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e( 'SAP Woo Suite Lite', 'sap-woo-suite-lite' ); ?></strong>
            <?php esc_html_e( 'requires WooCommerce to be installed and activated.', 'sap-woo-suite-lite' ); ?>
        </p>
    </div>
    <?php
}

/**
 * Add plugin action links.
 *
 * @since 1.0.0
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function sapwc_lite_plugin_action_links( $links ) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url( admin_url( 'admin.php?page=sapwc-lite-settings' ) ),
        esc_html__( 'Settings', 'sap-woo-suite-lite' )
    );

    $pro_link = sprintf(
        '<a href="%s" target="_blank" style="color:#41999f;font-weight:bold;">%s</a>',
        esc_url( 'https://replanta.net/conector-sap-woocommerce/' ),
        esc_html__( 'Upgrade to PRO', 'sap-woo-suite-lite' )
    );

    array_unshift( $links, $settings_link, $pro_link );

    return $links;
}
add_filter( 'plugin_action_links_' . SAPWC_LITE_BASENAME, 'sapwc_lite_plugin_action_links' );

/**
 * Get the active SAP connection.
 *
 * Uses PRO-compatible option names for seamless upgrade path.
 *
 * @since 1.0.0
 * @return array|null Connection data or null if not configured.
 */
function sapwc_lite_get_connection() {
    $connections = get_option( 'sapwc_connections', array() );
    $index       = get_option( 'sapwc_connection_index', 0 );

    if ( ! isset( $connections[ $index ] ) ) {
        return null;
    }

    $conn = $connections[ $index ];

    // Ensure SSL field exists.
    if ( ! isset( $conn['ssl'] ) ) {
        $conn['ssl'] = false;
    }

    // Validate required fields.
    if ( empty( $conn['url'] ) || empty( $conn['user'] ) || empty( $conn['pass'] ) || empty( $conn['db'] ) ) {
        return null;
    }

    return $conn;
}

/**
 * Add SAP connection status to admin bar.
 *
 * @since 1.0.0
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function sapwc_lite_admin_bar_status( $wp_admin_bar ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    $conn  = sapwc_lite_get_connection();
    $color = $conn ? '#41999f' : '#dc3545';
    $title = $conn
        ? __( 'SAP Lite: Connected', 'sap-woo-suite-lite' )
        : __( 'SAP Lite: Not configured', 'sap-woo-suite-lite' );

    $wp_admin_bar->add_node(
        array(
            'id'    => 'sapwc-lite-status',
            'title' => '<span style="color:' . esc_attr( $color ) . ';">&#9679;</span> ' . esc_html__( 'SAP Lite', 'sap-woo-suite-lite' ),
            'href'  => esc_url( admin_url( 'admin.php?page=sapwc-lite-settings' ) ),
            'meta'  => array( 'title' => $title ),
        )
    );
}
add_action( 'admin_bar_menu', 'sapwc_lite_admin_bar_status', 100 );
