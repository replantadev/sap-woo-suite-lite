<?php
/**
 * Admin Settings Page for SAP Woo Suite Lite
 * Uses PRO-compatible option names for seamless upgrade
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Page class
 */
class SAPWC_Lite_Settings_Page {

    /**
     * Constructor - register hooks
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_save' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_menu() {
        // SVG icon for sync (dashicons-update)
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="black">
                <path d="M10.2 3.28c3.53 0 6.43 2.61 6.92 6h2.08l-3.5 4-3.5-4h2.32c-.45-1.97-2.21-3.45-4.32-3.45-1.45 0-2.73.71-3.54 1.78L4.95 5.66C6.23 4.2 8.11 3.28 10.2 3.28zm-4.4 11.17c.45 1.97 2.21 3.45 4.32 3.45 1.45 0 2.73-.71 3.54-1.78l1.71 1.95c-1.28 1.46-3.15 2.38-5.25 2.38-3.53 0-6.43-2.61-6.92-6H1.12l3.5-4 3.5 4H5.8z"/>
            </svg>'
        );

        add_menu_page(
            __( 'SAP Woo Suite Lite', 'sap-woo-suite-lite' ),
            __( 'SAP Woo Lite', 'sap-woo-suite-lite' ),
            'manage_woocommerce',
            'sapwc-lite-settings',
            array( $this, 'render_page' ),
            $icon_svg,
            56
        );

        add_submenu_page(
            'sapwc-lite-settings',
            __( 'Settings', 'sap-woo-suite-lite' ),
            __( 'Settings', 'sap-woo-suite-lite' ),
            'manage_woocommerce',
            'sapwc-lite-settings',
            array( $this, 'render_page' )
        );

        add_submenu_page(
            'sapwc-lite-settings',
            __( 'Logs', 'sap-woo-suite-lite' ),
            __( 'Logs', 'sap-woo-suite-lite' ),
            'manage_woocommerce',
            'sapwc-lite-logs',
            array( $this, 'render_logs_page' )
        );
    }

    /**
     * Handle form submissions manually to use PRO-compatible option format
     */
    public function handle_save() {
        if ( ! isset( $_POST['sapwc_lite_save'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'sapwc_lite_settings_nonce' ) ) {
            return;
        }

        $tab = isset( $_POST['sapwc_lite_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['sapwc_lite_tab'] ) ) : 'connection';

        if ( 'connection' === $tab ) {
            // Save connection in PRO-compatible format (array).
            $connection = array(
                'url'  => isset( $_POST['sap_url'] ) ? esc_url_raw( rtrim( wp_unslash( $_POST['sap_url'] ), '/' ) ) : '',
                'user' => isset( $_POST['sap_user'] ) ? sanitize_text_field( wp_unslash( $_POST['sap_user'] ) ) : '',
                'pass' => isset( $_POST['sap_pass'] ) ? wp_unslash( $_POST['sap_pass'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                'db'   => isset( $_POST['sap_db'] ) ? sanitize_text_field( wp_unslash( $_POST['sap_db'] ) ) : '',
                'ssl'  => isset( $_POST['sap_ssl'] ) && '1' === $_POST['sap_ssl'],
            );

            // Store as array with index 0 (PRO supports multiple connections).
            update_option( 'sapwc_connections', array( $connection ) );
            update_option( 'sapwc_connection_index', 0 );

            add_settings_error(
                'sapwc_lite',
                'connection_saved',
                __( 'Connection settings saved.', 'sap-woo-suite-lite' ),
                'success'
            );

        } elseif ( 'sync' === $tab ) {
            // Save sync settings (PRO-compatible names).
            $price_list = isset( $_POST['price_list'] ) ? sanitize_text_field( wp_unslash( $_POST['price_list'] ) ) : '';
            update_option( 'sapwc_selected_tariff', $price_list );

            $warehouses = array();
            if ( isset( $_POST['warehouses'] ) && is_array( $_POST['warehouses'] ) ) {
                $warehouses = array_map( 'sanitize_text_field', wp_unslash( $_POST['warehouses'] ) );
            }
            update_option( 'sapwc_selected_warehouses', $warehouses );

            update_option( 'sapwc_sync_stock_auto', isset( $_POST['sync_stock'] ) ? '1' : '0' );
            update_option( 'sapwc_sync_prices_auto', isset( $_POST['sync_prices'] ) ? '1' : '0' );

            $interval = isset( $_POST['sync_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_interval'] ) ) : 'hourly';
            update_option( 'sapwc_stock_sync_interval', $interval );

            add_settings_error(
                'sapwc_lite',
                'sync_saved',
                __( 'Sync settings saved.', 'sap-woo-suite-lite' ),
                'success'
            );
        }

        set_transient( 'settings_errors', get_settings_errors(), 30 );

        $referer = wp_get_referer();
        if ( $referer ) {
            wp_safe_redirect( add_query_arg( 'settings-updated', 'true', $referer ) );
            exit;
        }
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        if ( false === strpos( $hook, 'sapwc-lite' ) ) {
            return;
        }

        wp_enqueue_style(
            'sapwc-lite-admin',
            SAPWC_LITE_URL . 'admin/css/admin.css',
            array(),
            SAPWC_LITE_VERSION
        );

        wp_enqueue_script(
            'sapwc-lite-admin',
            SAPWC_LITE_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            SAPWC_LITE_VERSION,
            true
        );

        wp_localize_script(
            'sapwc-lite-admin',
            'sapwcLite',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'sapwc_lite_nonce' ),
                'i18n'     => array(
                    'testing'           => __( 'Testing...', 'sap-woo-suite-lite' ),
                    'connected'         => __( 'Connected!', 'sap-woo-suite-lite' ),
                    'connection_failed' => __( 'Connection failed', 'sap-woo-suite-lite' ),
                    'request_failed'    => __( 'Request failed', 'sap-woo-suite-lite' ),
                    'syncing'           => __( 'Syncing...', 'sap-woo-suite-lite' ),
                    'products_updated'  => __( 'products updated', 'sap-woo-suite-lite' ),
                    'sync_failed'       => __( 'Sync failed', 'sap-woo-suite-lite' ),
                ),
            )
        );
    }

    /**
     * Render main settings page
     */
    public function render_page() {
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'connection';

        // Show any saved settings messages.
        settings_errors( 'sapwc_lite' );
        ?>
        <div class="wrap sapwc-wrap">
            <h1>
                <span class="sapwc-lite-logo">SAP Woo Suite Lite</span>
                <span class="sapwc-lite-version">v<?php echo esc_html( SAPWC_LITE_VERSION ); ?></span>
            </h1>

            <div class="sapwc-lite-pro-banner">
                <div class="sapwc-lite-pro-content">
                    <strong><?php esc_html_e( 'Unlock Full Power with PRO', 'sap-woo-suite-lite' ); ?></strong>
                    <p><?php esc_html_e( 'Sync products, orders, customers, field mapping, REST API, and more.', 'sap-woo-suite-lite' ); ?></p>
                </div>
                <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button button-primary">
                    <?php esc_html_e( 'Upgrade to PRO', 'sap-woo-suite-lite' ); ?>
                </a>
            </div>

            <nav class="nav-tab-wrapper">
                <a href="?page=sapwc-lite-settings&tab=connection"
                   class="nav-tab <?php echo 'connection' === $tab ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Connection', 'sap-woo-suite-lite' ); ?>
                </a>
                <a href="?page=sapwc-lite-settings&tab=sync"
                   class="nav-tab <?php echo 'sync' === $tab ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Sync Settings', 'sap-woo-suite-lite' ); ?>
                </a>
            </nav>

            <div class="sapwc-lite-content">
                <?php
                if ( 'connection' === $tab ) {
                    $this->render_connection_tab();
                } else {
                    $this->render_sync_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render connection settings tab
     */
    private function render_connection_tab() {
        // Get connection from PRO-compatible format.
        $connections = get_option( 'sapwc_connections', array() );
        $conn        = isset( $connections[0] ) ? $connections[0] : array();

        $url  = isset( $conn['url'] ) ? $conn['url'] : '';
        $user = isset( $conn['user'] ) ? $conn['user'] : '';
        $pass = isset( $conn['pass'] ) ? $conn['pass'] : '';
        $db   = isset( $conn['db'] ) ? $conn['db'] : '';
        $ssl  = ! empty( $conn['ssl'] );
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'sapwc_lite_settings_nonce' ); ?>
            <input type="hidden" name="sapwc_lite_save" value="1">
            <input type="hidden" name="sapwc_lite_tab" value="connection">

            <table class="form-table">
                <tr>
                    <th><label for="sap_url"><?php esc_html_e( 'Service Layer URL', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <input type="url" id="sap_url" name="sap_url"
                               value="<?php echo esc_attr( $url ); ?>" class="regular-text"
                               placeholder="https://sap-server:50000" required>
                        <p class="description">
                            <?php esc_html_e( 'SAP Business One Service Layer URL (without /b1s/v1)', 'sap-woo-suite-lite' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_user"><?php esc_html_e( 'Username', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <input type="text" id="sap_user" name="sap_user"
                               value="<?php echo esc_attr( $user ); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_pass"><?php esc_html_e( 'Password', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <input type="password" id="sap_pass" name="sap_pass"
                               value="<?php echo esc_attr( $pass ); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_db"><?php esc_html_e( 'Company Database', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <input type="text" id="sap_db" name="sap_db"
                               value="<?php echo esc_attr( $db ); ?>" class="regular-text"
                               placeholder="SBODEMOUS" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_ssl"><?php esc_html_e( 'SSL Verification', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="sap_ssl" name="sap_ssl"
                                   value="1" <?php checked( $ssl ); ?>>
                            <?php esc_html_e( 'Verify SSL certificate (recommended for production)', 'sap-woo-suite-lite' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Save Connection', 'sap-woo-suite-lite' ); ?>
                </button>
                <button type="button" id="sapwc-lite-test-connection" class="button">
                    <?php esc_html_e( 'Test Connection', 'sap-woo-suite-lite' ); ?>
                </button>
                <span id="sapwc-lite-test-result"></span>
            </p>
        </form>
        <?php
    }

    /**
     * Render sync settings tab
     */
    private function render_sync_tab() {
        $conn            = sapwc_lite_get_connection();
        $price_lists     = array();
        $warehouses_list = array();

        if ( $conn ) {
            $client = new SAPWC_Lite_API_Client( $conn['url'] );
            $login  = $client->login( $conn['user'], $conn['pass'], $conn['db'], $conn['ssl'] );
            if ( $login['success'] ) {
                $price_lists     = $client->get_price_lists();
                $warehouses_list = $client->get_warehouses();
                $client->logout();
            }
        }

        // Read PRO-compatible options.
        $selected_tariff     = get_option( 'sapwc_selected_tariff', '' );
        $selected_warehouses = get_option( 'sapwc_selected_warehouses', array() );
        if ( ! is_array( $selected_warehouses ) ) {
            $selected_warehouses = array( $selected_warehouses );
        }
        $sync_stock  = get_option( 'sapwc_sync_stock_auto', '1' );
        $sync_prices = get_option( 'sapwc_sync_prices_auto', '1' );
        $interval    = get_option( 'sapwc_stock_sync_interval', 'hourly' );
        $last_sync   = get_option( 'sapwc_stock_last_sync', '' );
        if ( empty( $last_sync ) ) {
            $last_sync = __( 'Never', 'sap-woo-suite-lite' );
        }

        if ( ! $conn ) :
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e( 'Configure your SAP connection first', 'sap-woo-suite-lite' ); ?></strong>
                    <?php esc_html_e( 'before setting up sync options.', 'sap-woo-suite-lite' ); ?>
                </p>
            </div>
            <?php
        endif;
        ?>

        <div class="sapwc-info-box">
            <p style="margin:0;">
                <strong><?php esc_html_e( 'How it works:', 'sap-woo-suite-lite' ); ?></strong>
                <?php esc_html_e( 'Products are matched by SKU. The WooCommerce product SKU must match the SAP ItemCode exactly.', 'sap-woo-suite-lite' ); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'sapwc_lite_settings_nonce' ); ?>
            <input type="hidden" name="sapwc_lite_save" value="1">
            <input type="hidden" name="sapwc_lite_tab" value="sync">

            <h2><?php esc_html_e( 'Sync Configuration', 'sap-woo-suite-lite' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th><label for="price_list"><?php esc_html_e( 'Price List', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <select id="price_list" name="price_list" <?php echo empty( $price_lists ) ? 'disabled' : ''; ?>>
                            <option value=""><?php esc_html_e( '-- Select Price List --', 'sap-woo-suite-lite' ); ?></option>
                            <?php foreach ( $price_lists as $pl ) : ?>
                                <option value="<?php echo esc_attr( $pl['id'] ); ?>"
                                        <?php selected( $selected_tariff, $pl['id'] ); ?>>
                                    <?php echo esc_html( $pl['name'] ); ?> (<?php echo esc_html( $pl['id'] ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( empty( $price_lists ) && $conn ) : ?>
                            <p class="description" style="color:#dc3545;">
                                <?php esc_html_e( 'Could not load price lists. Check your connection.', 'sap-woo-suite-lite' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( 'Warehouses', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <?php if ( ! empty( $warehouses_list ) ) : ?>
                            <?php foreach ( $warehouses_list as $wh ) : ?>
                                <label style="display:block;margin-bottom:5px;">
                                    <input type="checkbox" name="warehouses[]"
                                           value="<?php echo esc_attr( $wh['code'] ); ?>"
                                           <?php checked( in_array( $wh['code'], $selected_warehouses, true ) ); ?>>
                                    <?php echo esc_html( $wh['name'] ); ?> (<?php echo esc_html( $wh['code'] ); ?>)
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description"><?php esc_html_e( 'Connect to SAP to load warehouses.', 'sap-woo-suite-lite' ); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'What to Sync', 'sap-woo-suite-lite' ); ?></th>
                    <td>
                        <label style="display:block;margin-bottom:8px;">
                            <input type="checkbox" name="sync_stock" value="1"
                                   <?php checked( $sync_stock, '1' ); ?>>
                            <strong><?php esc_html_e( 'Sync Stock', 'sap-woo-suite-lite' ); ?></strong>
                            - <?php esc_html_e( 'Update WooCommerce stock quantities from SAP', 'sap-woo-suite-lite' ); ?>
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="sync_prices" value="1"
                                   <?php checked( $sync_prices, '1' ); ?>>
                            <strong><?php esc_html_e( 'Sync Prices', 'sap-woo-suite-lite' ); ?></strong>
                            - <?php esc_html_e( 'Update WooCommerce prices from SAP price list', 'sap-woo-suite-lite' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Automatic Sync', 'sap-woo-suite-lite' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th><label for="sync_interval"><?php esc_html_e( 'Sync Interval', 'sap-woo-suite-lite' ); ?></label></th>
                    <td>
                        <select id="sync_interval" name="sync_interval">
                            <option value="hourly" <?php selected( $interval, 'hourly' ); ?>>
                                <?php esc_html_e( 'Every Hour', 'sap-woo-suite-lite' ); ?>
                            </option>
                            <option value="twicedaily" <?php selected( $interval, 'twicedaily' ); ?>>
                                <?php esc_html_e( 'Twice Daily', 'sap-woo-suite-lite' ); ?>
                            </option>
                            <option value="daily" <?php selected( $interval, 'daily' ); ?>>
                                <?php esc_html_e( 'Daily', 'sap-woo-suite-lite' ); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Automatic sync runs via WP-Cron when stock sync is enabled.', 'sap-woo-suite-lite' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Last Sync', 'sap-woo-suite-lite' ); ?></th>
                    <td>
                        <strong><?php echo esc_html( $last_sync ); ?></strong>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Save Sync Settings', 'sap-woo-suite-lite' ); ?>
                </button>
                <button type="button" id="sapwc-lite-sync-now" class="button" <?php echo ! $conn ? 'disabled' : ''; ?>>
                    <?php esc_html_e( 'Sync Now', 'sap-woo-suite-lite' ); ?>
                </button>
                <span id="sapwc-lite-sync-result"></span>
            </p>
        </form>

        <div class="sapwc-lite-pro-features-preview">
            <h3><?php esc_html_e( 'PRO Features Available', 'sap-woo-suite-lite' ); ?></h3>
            <ul>
                <li><strong><?php esc_html_e( 'Full Product Import', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'Import products from SAP with images, attributes, variations', 'sap-woo-suite-lite' ); ?></li>
                <li><strong><?php esc_html_e( 'Order Sync', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'Automatically send orders to SAP as Sales Orders', 'sap-woo-suite-lite' ); ?></li>
                <li><strong><?php esc_html_e( 'Customer Sync', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'Create Business Partners from WooCommerce customers', 'sap-woo-suite-lite' ); ?></li>
                <li><strong><?php esc_html_e( 'Field Mapping', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'Map any SAP field to WooCommerce attributes', 'sap-woo-suite-lite' ); ?></li>
                <li><strong><?php esc_html_e( 'REST API', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'External integrations and webhooks', 'sap-woo-suite-lite' ); ?></li>
                <li><strong><?php esc_html_e( 'Multi-channel', 'sap-woo-suite-lite' ); ?></strong> - <?php esc_html_e( 'TikTok Shop, Amazon, Miravia integrations', 'sap-woo-suite-lite' ); ?></li>
            </ul>
            <a href="?page=sapwc-lite-pro" class="button"><?php esc_html_e( 'See All PRO Features', 'sap-woo-suite-lite' ); ?></a>
        </div>
        <?php
    }

    /**
     * Render logs page
     */
    public function render_logs_page() {
        $logs = SAPWC_Lite_Logger::get_logs( 100 );
        ?>
        <div class="wrap sapwc-wrap">
            <h1><?php esc_html_e( 'SAP Woo Suite Lite - Logs', 'sap-woo-suite-lite' ); ?></h1>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'sap-woo-suite-lite' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'sap-woo-suite-lite' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'sap-woo-suite-lite' ); ?></th>
                        <th><?php esc_html_e( 'Message', 'sap-woo-suite-lite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $logs ) ) : ?>
                        <tr>
                            <td colspan="4"><?php esc_html_e( 'No logs yet.', 'sap-woo-suite-lite' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log['created_at'] ); ?></td>
                                <td><?php echo esc_html( $log['action'] ); ?></td>
                                <td>
                                    <span class="sapwc-lite-status-<?php echo esc_attr( $log['status'] ); ?>">
                                        <?php echo esc_html( strtoupper( $log['status'] ) ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $log['message'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Initialize.
new SAPWC_Lite_Settings_Page();
