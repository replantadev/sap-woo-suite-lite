<?php
/**
 * Admin Settings Page for Replanta Connector with SAP for WooCommerce
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
        add_submenu_page(
            'sapwc-lite',
            __( 'Settings', 'replanta-connector-sap-woocommerce' ),
            __( 'Settings', 'replanta-connector-sap-woocommerce' ),
            'manage_woocommerce',
            'sapwc-lite-settings',
            array( $this, 'render_page' )
        );

        add_submenu_page(
            'sapwc-lite',
            __( 'Logs', 'replanta-connector-sap-woocommerce' ),
            __( 'Logs', 'replanta-connector-sap-woocommerce' ),
            'manage_woocommerce',
            'sapwc-lite-logs',
            array( $this, 'render_logs_page' )
        );
    }

    /**
     * Handle form submissions manually to use PRO-compatible option format
     */
    public function handle_save() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- early bail-out before nonce check
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
                'url'  => isset( $_POST['sap_url'] ) ? esc_url_raw( rtrim( sanitize_text_field( wp_unslash( $_POST['sap_url'] ) ), '/' ) ) : '',
                'user' => isset( $_POST['sap_user'] ) ? sanitize_text_field( wp_unslash( $_POST['sap_user'] ) ) : '',
                'pass' => isset( $_POST['sap_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['sap_pass'] ) ) : '',
                'db'   => isset( $_POST['sap_db'] ) ? sanitize_text_field( wp_unslash( $_POST['sap_db'] ) ) : '',
                'ssl'  => isset( $_POST['sap_ssl'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['sap_ssl'] ) ),
            );

            // Store as array with index 0 (PRO supports multiple connections).
            update_option( 'sapwc_connections', array( $connection ) );
            update_option( 'sapwc_connection_index', 0 );

            add_settings_error(
                'sapwc_lite',
                'connection_saved',
                __( 'Connection settings saved.', 'replanta-connector-sap-woocommerce' ),
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
                __( 'Sync settings saved.', 'replanta-connector-sap-woocommerce' ),
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

        // Chart.js + dashboard.js only on dashboard page
        if ( 'toplevel_page_sapwc-lite' === $hook ) {
            wp_enqueue_script(
                'chartjs',
                SAPWC_LITE_URL . 'admin/js/chart.min.js',
                array(),
                '4.4.7',
                true
            );
            wp_enqueue_script(
                'sapwc-lite-dashboard',
                SAPWC_LITE_URL . 'admin/js/dashboard.js',
                array( 'chartjs' ),
                SAPWC_LITE_VERSION,
                true
            );
        }

        wp_localize_script(
            'sapwc-lite-admin',
            'sapwcLite',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'sapwc_lite_nonce' ),
                'i18n'     => array(
                    'testing'           => __( 'Testing...', 'replanta-connector-sap-woocommerce' ),
                    'connected'         => __( 'Connected!', 'replanta-connector-sap-woocommerce' ),
                    'connection_failed' => __( 'Connection failed', 'replanta-connector-sap-woocommerce' ),
                    'request_failed'    => __( 'Request failed', 'replanta-connector-sap-woocommerce' ),
                    'syncing'           => __( 'Syncing...', 'replanta-connector-sap-woocommerce' ),
                    'products_updated'  => __( 'products updated', 'replanta-connector-sap-woocommerce' ),
                    'sync_failed'       => __( 'Sync failed', 'replanta-connector-sap-woocommerce' ),
                    'pro_tip'           => __( 'PRO Tip:', 'replanta-connector-sap-woocommerce' ),
                    'go_pro'            => __( 'Upgrade to PRO', 'replanta-connector-sap-woocommerce' ),
                ),
            )
        );
    }

    /**
     * Render main settings page
     */
    public function render_page() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab navigation
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'connection';

        // Show any saved settings messages.
        settings_errors( 'sapwc_lite' );
        ?>
        <div class="wrap sapwc-wrap">
            <h1>
                <span class="sapwc-lite-logo">Replanta Connector</span>
                <span class="sapwc-lite-version">v<?php echo esc_html( SAPWC_LITE_VERSION ); ?></span>
            </h1>

            <div class="sapwc-lite-pro-banner">
                <div class="sapwc-lite-pro-content">
                    <strong><?php esc_html_e( 'Sync your entire business with PRO', 'replanta-connector-sap-woocommerce' ); ?></strong>
                    <p><?php esc_html_e( 'Orders, products, customers and multi-channel -- all connected to SAP.', 'replanta-connector-sap-woocommerce' ); ?></p>
                </div>
                <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button button-primary">
                    <?php esc_html_e( 'Upgrade to PRO', 'replanta-connector-sap-woocommerce' ); ?>
                </a>
            </div>

            <nav class="nav-tab-wrapper">
                <a href="?page=sapwc-lite-settings&tab=connection"
                   class="nav-tab <?php echo esc_attr( 'connection' === $tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Connection', 'replanta-connector-sap-woocommerce' ); ?>
                </a>
                <a href="?page=sapwc-lite-settings&tab=sync"
                   class="nav-tab <?php echo esc_attr( 'sync' === $tab ? 'nav-tab-active' : '' ); ?>">
                    <?php esc_html_e( 'Sync Settings', 'replanta-connector-sap-woocommerce' ); ?>
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
                    <th><label for="sap_url"><?php esc_html_e( 'Service Layer URL', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <input type="url" id="sap_url" name="sap_url"
                               value="<?php echo esc_attr( $url ); ?>" class="regular-text"
                               placeholder="https://sap-server:50000" required>
                        <p class="description">
                            <?php esc_html_e( 'SAP Business One Service Layer URL (without /b1s/v1)', 'replanta-connector-sap-woocommerce' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_user"><?php esc_html_e( 'Username', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <input type="text" id="sap_user" name="sap_user"
                               value="<?php echo esc_attr( $user ); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_pass"><?php esc_html_e( 'Password', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <input type="password" id="sap_pass" name="sap_pass"
                               value="<?php echo esc_attr( $pass ); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_db"><?php esc_html_e( 'Company Database', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <input type="text" id="sap_db" name="sap_db"
                               value="<?php echo esc_attr( $db ); ?>" class="regular-text"
                               placeholder="SBODEMOUS" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_ssl"><?php esc_html_e( 'SSL Verification', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="sap_ssl" name="sap_ssl"
                                   value="1" <?php checked( $ssl ); ?>>
                            <?php esc_html_e( 'Verify SSL certificate (recommended for production)', 'replanta-connector-sap-woocommerce' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Save Connection', 'replanta-connector-sap-woocommerce' ); ?>
                </button>
                <button type="button" id="sapwc-lite-test-connection" class="button">
                    <?php esc_html_e( 'Test Connection', 'replanta-connector-sap-woocommerce' ); ?>
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
            $last_sync = __( 'Never', 'replanta-connector-sap-woocommerce' );
        }

        if ( ! $conn ) :
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e( 'Configure your SAP connection first', 'replanta-connector-sap-woocommerce' ); ?></strong>
                    <?php esc_html_e( 'before setting up sync options.', 'replanta-connector-sap-woocommerce' ); ?>
                </p>
            </div>
            <?php
        endif;
        ?>

        <div class="sapwc-info-box">
            <p style="margin:0;">
                <strong><?php esc_html_e( 'How sync works:', 'replanta-connector-sap-woocommerce' ); ?></strong>
                <?php esc_html_e( 'Products are matched by SKU (WooCommerce SKU = SAP ItemCode). Once configured, stock and prices sync on your chosen schedule -- set it and forget it.', 'replanta-connector-sap-woocommerce' ); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'sapwc_lite_settings_nonce' ); ?>
            <input type="hidden" name="sapwc_lite_save" value="1">
            <input type="hidden" name="sapwc_lite_tab" value="sync">

            <h2><?php esc_html_e( 'Sync Configuration', 'replanta-connector-sap-woocommerce' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th><label for="price_list"><?php esc_html_e( 'Price List', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <select id="price_list" name="price_list" <?php echo esc_attr( empty( $price_lists ) ? 'disabled' : '' ); ?>>
                            <option value=""><?php esc_html_e( '-- Select Price List --', 'replanta-connector-sap-woocommerce' ); ?></option>
                            <?php foreach ( $price_lists as $pl ) : ?>
                                <option value="<?php echo esc_attr( $pl['id'] ); ?>"
                                        <?php selected( $selected_tariff, $pl['id'] ); ?>>
                                    <?php echo esc_html( $pl['name'] ); ?> (<?php echo esc_html( $pl['id'] ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( empty( $price_lists ) && $conn ) : ?>
                            <p class="description" style="color:#dc3545;">
                                <?php esc_html_e( 'Could not load price lists. Check your connection.', 'replanta-connector-sap-woocommerce' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( 'Warehouses', 'replanta-connector-sap-woocommerce' ); ?></label></th>
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
                            <p class="description"><?php esc_html_e( 'Connect to SAP to load warehouses.', 'replanta-connector-sap-woocommerce' ); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'What to Sync', 'replanta-connector-sap-woocommerce' ); ?></th>
                    <td>
                        <label style="display:block;margin-bottom:8px;">
                            <input type="checkbox" name="sync_stock" value="1"
                                   <?php checked( $sync_stock, '1' ); ?>>
                            <strong><?php esc_html_e( 'Sync Stock', 'replanta-connector-sap-woocommerce' ); ?></strong>
                            - <?php esc_html_e( 'Keep WooCommerce stock in sync with SAP', 'replanta-connector-sap-woocommerce' ); ?>
                        </label>
                        <label style="display:block;margin-bottom:8px;">
                            <input type="checkbox" name="sync_prices" value="1"
                                   <?php checked( $sync_prices, '1' ); ?>>
                            <strong><?php esc_html_e( 'Sync Prices', 'replanta-connector-sap-woocommerce' ); ?></strong>
                            - <?php esc_html_e( 'Keep prices updated from your SAP price list', 'replanta-connector-sap-woocommerce' ); ?>
                        </label>

                        <!-- Locked PRO sync options -->
                        <div style="margin-top:12px; padding-top:12px; border-top:1px dashed var(--sapwc-gray-200, #e2e8f0);">
                            <label style="display:block;margin-bottom:8px; opacity:0.5; cursor:not-allowed;">
                                <input type="checkbox" disabled>
                                <strong><?php esc_html_e( 'Sync Orders', 'replanta-connector-sap-woocommerce' ); ?></strong>
                                - <?php esc_html_e( 'Send orders to SAP as Sales Orders', 'replanta-connector-sap-woocommerce' ); ?>
                                <span style="color:#41999f; font-weight:600; font-size:11px; margin-left:4px;">PRO</span>
                            </label>
                            <label style="display:block;margin-bottom:8px; opacity:0.5; cursor:not-allowed;">
                                <input type="checkbox" disabled>
                                <strong><?php esc_html_e( 'Sync Customers', 'replanta-connector-sap-woocommerce' ); ?></strong>
                                - <?php esc_html_e( 'Create Business Partners in SAP from customers', 'replanta-connector-sap-woocommerce' ); ?>
                                <span style="color:#41999f; font-weight:600; font-size:11px; margin-left:4px;">PRO</span>
                            </label>
                            <label style="display:block; opacity:0.5; cursor:not-allowed;">
                                <input type="checkbox" disabled>
                                <strong><?php esc_html_e( 'Import Products', 'replanta-connector-sap-woocommerce' ); ?></strong>
                                - <?php esc_html_e( 'Import products from SAP with images and attributes', 'replanta-connector-sap-woocommerce' ); ?>
                                <span style="color:#41999f; font-weight:600; font-size:11px; margin-left:4px;">PRO</span>
                            </label>
                        </div>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Automatic Sync', 'replanta-connector-sap-woocommerce' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th><label for="sync_interval"><?php esc_html_e( 'Sync Interval', 'replanta-connector-sap-woocommerce' ); ?></label></th>
                    <td>
                        <select id="sync_interval" name="sync_interval">
                            <option value="hourly" <?php selected( $interval, 'hourly' ); ?>>
                                <?php esc_html_e( 'Every Hour', 'replanta-connector-sap-woocommerce' ); ?>
                            </option>
                            <option value="twicedaily" <?php selected( $interval, 'twicedaily' ); ?>>
                                <?php esc_html_e( 'Twice Daily', 'replanta-connector-sap-woocommerce' ); ?>
                            </option>
                            <option value="daily" <?php selected( $interval, 'daily' ); ?>>
                                <?php esc_html_e( 'Daily', 'replanta-connector-sap-woocommerce' ); ?>
                            </option>

                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Syncs run on this schedule. Set it and forget it.', 'replanta-connector-sap-woocommerce' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Last Sync', 'replanta-connector-sap-woocommerce' ); ?></th>
                    <td>
                        <strong><?php echo esc_html( $last_sync ); ?></strong>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Save Sync Settings', 'replanta-connector-sap-woocommerce' ); ?>
                </button>
                <button type="button" id="sapwc-lite-sync-now" class="button" <?php echo esc_attr( ! $conn ? 'disabled' : '' ); ?>>
                    <?php esc_html_e( 'Sync Now', 'replanta-connector-sap-woocommerce' ); ?>
                </button>
                <span id="sapwc-lite-sync-result"></span>
            </p>
        </form>

        <div class="sapwc-lite-pro-features-preview">
            <h3><?php esc_html_e( 'Want to sync more than stock and prices?', 'replanta-connector-sap-woocommerce' ); ?></h3>
            <p style="margin-bottom:12px; color: var(--sapwc-gray-600, #475569);">
                <?php esc_html_e( 'With PRO, orders, products, customers and more also sync with SAP. Set it up once, it just works.', 'replanta-connector-sap-woocommerce' ); ?>
            </p>
            <ul>
                <li><strong><?php esc_html_e( 'Full Product Import', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'Products created and updated from SAP', 'replanta-connector-sap-woocommerce' ); ?></li>
                <li><strong><?php esc_html_e( 'Order Sync', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'Every order flows to SAP as a Sales Order', 'replanta-connector-sap-woocommerce' ); ?></li>
                <li><strong><?php esc_html_e( 'Customer Sync', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'Business Partners created from WooCommerce customers', 'replanta-connector-sap-woocommerce' ); ?></li>
                <li><strong><?php esc_html_e( 'Field Mapping', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'Map any SAP field to WooCommerce attributes', 'replanta-connector-sap-woocommerce' ); ?></li>
                <li><strong><?php esc_html_e( 'Smart Retry', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'Failed syncs retry with intelligent backoff', 'replanta-connector-sap-woocommerce' ); ?></li>
                <li><strong><?php esc_html_e( 'Multi-channel', 'replanta-connector-sap-woocommerce' ); ?></strong> - <?php esc_html_e( 'TikTok Shop, Amazon, Miravia included', 'replanta-connector-sap-woocommerce' ); ?></li>
            </ul>
            <a href="?page=sapwc-lite-pro" class="button"><?php esc_html_e( 'See All PRO Features', 'replanta-connector-sap-woocommerce' ); ?></a>
        </div>
        <?php
    }

    /**
     * Render logs page
     */
    public function render_logs_page() {
        $filter_action = isset( $_GET['sapwc_log_action'] ) ? sanitize_text_field( wp_unslash( $_GET['sapwc_log_action'] ) ) : '';
        $filter_status = isset( $_GET['sapwc_log_status'] ) ? sanitize_text_field( wp_unslash( $_GET['sapwc_log_status'] ) ) : '';
        $logs = SAPWC_Lite_Logger::get_logs( 100, $filter_action ?: null, $filter_status ?: null );
        ?>
        <div class="wrap sapwc-wrap">
            <h1><?php esc_html_e( 'Replanta Connector - Logs', 'replanta-connector-sap-woocommerce' ); ?></h1>

            <form method="get" style="margin-bottom:16px;">
                <input type="hidden" name="page" value="sapwc-lite-logs">
                <select name="sapwc_log_action">
                    <option value=""><?php esc_html_e( 'All actions', 'replanta-connector-sap-woocommerce' ); ?></option>
                    <?php foreach ( array( 'stock_sync', 'price_sync', 'connection' ) as $act ) : ?>
                        <option value="<?php echo esc_attr( $act ); ?>" <?php selected( $filter_action, $act ); ?>><?php echo esc_html( $act ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sapwc_log_status">
                    <option value=""><?php esc_html_e( 'All statuses', 'replanta-connector-sap-woocommerce' ); ?></option>
                    <?php foreach ( array( 'success', 'error', 'warning', 'info' ) as $st ) : ?>
                        <option value="<?php echo esc_attr( $st ); ?>" <?php selected( $filter_status, $st ); ?>><?php echo esc_html( $st ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e( 'Filter', 'replanta-connector-sap-woocommerce' ); ?></button>
                <?php if ( $filter_action || $filter_status ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sapwc-lite-logs' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'replanta-connector-sap-woocommerce' ); ?></a>
                <?php endif; ?>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'replanta-connector-sap-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'replanta-connector-sap-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'replanta-connector-sap-woocommerce' ); ?></th>
                        <th><?php esc_html_e( 'Message', 'replanta-connector-sap-woocommerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $logs ) ) : ?>
                        <tr>
                            <td colspan="4"><?php esc_html_e( 'No logs yet.', 'replanta-connector-sap-woocommerce' ); ?></td>
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
