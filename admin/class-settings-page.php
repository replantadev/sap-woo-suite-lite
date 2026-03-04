<?php
/**
 * Admin Settings Page for SAP Woo Suite Lite
 * Uses PRO-compatible option names for seamless upgrade
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_Settings_Page
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'handle_save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_menu()
    {
        add_menu_page(
            'SAP Woo Suite Lite',
            'SAP Woo Lite',
            'manage_woocommerce',
            'sapwc-lite-settings',
            [$this, 'render_page'],
            'dashicons-cloud',
            56
        );

        add_submenu_page(
            'sapwc-lite-settings',
            'Settings',
            'Settings',
            'manage_woocommerce',
            'sapwc-lite-settings',
            [$this, 'render_page']
        );

        add_submenu_page(
            'sapwc-lite-settings',
            'Logs',
            'Logs',
            'manage_woocommerce',
            'sapwc-lite-logs',
            [$this, 'render_logs_page']
        );
    }

    /**
     * Handle form submissions manually to use PRO-compatible option format
     */
    public function handle_save()
    {
        if (!isset($_POST['sapwc_lite_save']) || !current_user_can('manage_woocommerce')) {
            return;
        }

        check_admin_referer('sapwc_lite_settings_nonce');

        $tab = isset($_POST['sapwc_lite_tab']) ? sanitize_text_field($_POST['sapwc_lite_tab']) : 'connection';

        if ($tab === 'connection') {
            // Save connection in PRO-compatible format (array)
            $connection = [
                'url'  => rtrim(sanitize_text_field($_POST['sap_url'] ?? ''), '/'),
                'user' => sanitize_text_field($_POST['sap_user'] ?? ''),
                'pass' => $_POST['sap_pass'] ?? '', // Don't sanitize password
                'db'   => sanitize_text_field($_POST['sap_db'] ?? ''),
                'ssl'  => isset($_POST['sap_ssl']) && $_POST['sap_ssl'] === '1'
            ];

            // Store as array with index 0 (PRO supports multiple connections)
            update_option('sapwc_connections', [$connection]);
            update_option('sapwc_connection_index', 0);

            add_settings_error('sapwc_lite', 'connection_saved', 'Connection settings saved.', 'success');

        } elseif ($tab === 'sync') {
            // Save sync settings (PRO-compatible names)
            update_option('sapwc_selected_tariff', sanitize_text_field($_POST['price_list'] ?? ''));
            
            $warehouses = isset($_POST['warehouses']) && is_array($_POST['warehouses']) 
                ? array_map('sanitize_text_field', $_POST['warehouses']) 
                : [];
            update_option('sapwc_selected_warehouses', $warehouses);
            
            update_option('sapwc_sync_stock_auto', isset($_POST['sync_stock']) ? '1' : '0');
            update_option('sapwc_sync_prices_auto', isset($_POST['sync_prices']) ? '1' : '0');
            update_option('sapwc_stock_sync_interval', sanitize_text_field($_POST['sync_interval'] ?? 'hourly'));

            add_settings_error('sapwc_lite', 'sync_saved', 'Sync settings saved.', 'success');
        }

        set_transient('settings_errors', get_settings_errors(), 30);
        wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'sapwc-lite') === false) {
            return;
        }

        wp_enqueue_style('sapwc-lite-admin', SAPWC_LITE_URL . 'admin/css/admin.css', [], SAPWC_LITE_VERSION);
        wp_enqueue_script('sapwc-lite-admin', SAPWC_LITE_URL . 'admin/js/admin.js', ['jquery'], SAPWC_LITE_VERSION, true);

        wp_localize_script('sapwc-lite-admin', 'sapwcLite', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('sapwc_lite_nonce')
        ]);
    }

    public function render_page()
    {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'connection';
        
        // Show any saved settings messages
        settings_errors('sapwc_lite');
        ?>
        <div class="wrap sapwc-lite-wrap">
            <h1>
                <span class="sapwc-lite-logo">SAP Woo Suite Lite</span>
                <span class="sapwc-lite-version">v<?php echo esc_html(SAPWC_LITE_VERSION); ?></span>
            </h1>

            <div class="sapwc-lite-pro-banner">
                <div class="sapwc-lite-pro-content">
                    <strong>Unlock Full Power with PRO</strong>
                    <p>Sync products, orders, customers, field mapping, REST API, and more.</p>
                </div>
                <a href="https://replanta.dev/sap-woo-suite" target="_blank" class="button button-primary">
                    Upgrade to PRO
                </a>
            </div>

            <nav class="nav-tab-wrapper">
                <a href="?page=sapwc-lite-settings&tab=connection" 
                   class="nav-tab <?php echo $tab === 'connection' ? 'nav-tab-active' : ''; ?>">
                    Connection
                </a>
                <a href="?page=sapwc-lite-settings&tab=sync" 
                   class="nav-tab <?php echo $tab === 'sync' ? 'nav-tab-active' : ''; ?>">
                    Sync Settings
                </a>
            </nav>

            <div class="sapwc-lite-content">
                <?php
                if ($tab === 'connection') {
                    $this->render_connection_tab();
                } else {
                    $this->render_sync_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_connection_tab()
    {
        // Get connection from PRO-compatible format
        $connections = get_option('sapwc_connections', []);
        $conn = isset($connections[0]) ? $connections[0] : [];
        
        $url  = $conn['url'] ?? '';
        $user = $conn['user'] ?? '';
        $pass = $conn['pass'] ?? '';
        $db   = $conn['db'] ?? '';
        $ssl  = !empty($conn['ssl']);
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('sapwc_lite_settings_nonce'); ?>
            <input type="hidden" name="sapwc_lite_save" value="1">
            <input type="hidden" name="sapwc_lite_tab" value="connection">

            <table class="form-table">
                <tr>
                    <th><label for="sap_url">Service Layer URL</label></th>
                    <td>
                        <input type="url" id="sap_url" name="sap_url" 
                               value="<?php echo esc_attr($url); ?>" class="regular-text" 
                               placeholder="https://sap-server:50000" required>
                        <p class="description">SAP Business One Service Layer URL (without /b1s/v1)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_user">Username</label></th>
                    <td>
                        <input type="text" id="sap_user" name="sap_user" 
                               value="<?php echo esc_attr($user); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_pass">Password</label></th>
                    <td>
                        <input type="password" id="sap_pass" name="sap_pass" 
                               value="<?php echo esc_attr($pass); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_db">Company Database</label></th>
                    <td>
                        <input type="text" id="sap_db" name="sap_db" 
                               value="<?php echo esc_attr($db); ?>" class="regular-text" 
                               placeholder="SBODEMOUS" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sap_ssl">SSL Verification</label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="sap_ssl" name="sap_ssl" 
                                   value="1" <?php checked($ssl); ?>>
                            Verify SSL certificate (recommended for production)
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Connection</button>
                <button type="button" id="sapwc-lite-test-connection" class="button">
                    Test Connection
                </button>
                <span id="sapwc-lite-test-result"></span>
            </p>
        </form>
        <?php
    }

    private function render_sync_tab()
    {
        $conn = sapwc_lite_get_connection();
        $price_lists = [];
        $warehouses_list = [];

        if ($conn) {
            $client = new SAPWC_Lite_API_Client($conn['url']);
            $login = $client->login($conn['user'], $conn['pass'], $conn['db'], $conn['ssl']);
            if ($login['success']) {
                $price_lists = $client->get_price_lists();
                $warehouses_list = $client->get_warehouses();
                $client->logout();
            }
        }

        // Read PRO-compatible options
        $selected_tariff = get_option('sapwc_selected_tariff', '');
        $selected_warehouses = get_option('sapwc_selected_warehouses', []);
        if (!is_array($selected_warehouses)) {
            $selected_warehouses = [$selected_warehouses];
        }
        $sync_stock = get_option('sapwc_sync_stock_auto', '1');
        $sync_prices = get_option('sapwc_sync_prices_auto', '1');
        $interval = get_option('sapwc_stock_sync_interval', 'hourly');
        $last_sync = get_option('sapwc_stock_last_sync', 'Never');
        ?>
        <?php if (!$conn): ?>
            <div class="notice notice-warning">
                <p><strong>Configure your SAP connection first</strong> before setting up sync options.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('sapwc_lite_settings_nonce'); ?>
            <input type="hidden" name="sapwc_lite_save" value="1">
            <input type="hidden" name="sapwc_lite_tab" value="sync">

            <h2>Sync Configuration</h2>

            <table class="form-table">
                <tr>
                    <th><label for="price_list">Price List</label></th>
                    <td>
                        <select id="price_list" name="price_list" <?php echo empty($price_lists) ? 'disabled' : ''; ?>>
                            <option value="">-- Select Price List --</option>
                            <?php foreach ($price_lists as $pl): ?>
                                <option value="<?php echo esc_attr($pl['id']); ?>" 
                                        <?php selected($selected_tariff, $pl['id']); ?>>
                                    <?php echo esc_html($pl['name']); ?> (<?php echo esc_html($pl['id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($price_lists) && $conn): ?>
                            <p class="description" style="color:#dc3545;">Could not load price lists. Check your connection.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label>Warehouses</label></th>
                    <td>
                        <?php if (!empty($warehouses_list)): ?>
                            <?php foreach ($warehouses_list as $wh): ?>
                                <label style="display:block;margin-bottom:5px;">
                                    <input type="checkbox" name="warehouses[]" 
                                           value="<?php echo esc_attr($wh['code']); ?>"
                                           <?php checked(in_array($wh['code'], $selected_warehouses, true)); ?>>
                                    <?php echo esc_html($wh['name']); ?> (<?php echo esc_html($wh['code']); ?>)
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="description">Connect to SAP to load warehouses.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>What to Sync</th>
                    <td>
                        <label style="display:block;margin-bottom:8px;">
                            <input type="checkbox" name="sync_stock" value="1" 
                                   <?php checked($sync_stock, '1'); ?>>
                            <strong>Sync Stock</strong> - Update WooCommerce stock quantities from SAP
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="sync_prices" value="1" 
                                   <?php checked($sync_prices, '1'); ?>>
                            <strong>Sync Prices</strong> - Update WooCommerce prices from SAP price list
                        </label>
                    </td>
                </tr>
            </table>

            <h2>Automatic Sync</h2>

            <table class="form-table">
                <tr>
                    <th><label for="sync_interval">Sync Interval</label></th>
                    <td>
                        <select id="sync_interval" name="sync_interval">
                            <option value="hourly" <?php selected($interval, 'hourly'); ?>>Every Hour</option>
                            <option value="twicedaily" <?php selected($interval, 'twicedaily'); ?>>Twice Daily</option>
                            <option value="daily" <?php selected($interval, 'daily'); ?>>Daily</option>
                        </select>
                        <p class="description">Automatic sync runs via WP-Cron when stock sync is enabled.</p>
                    </td>
                </tr>
                <tr>
                    <th>Last Sync</th>
                    <td>
                        <strong><?php echo esc_html($last_sync); ?></strong>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">Save Sync Settings</button>
                <button type="button" id="sapwc-lite-sync-now" class="button" <?php echo !$conn ? 'disabled' : ''; ?>>
                    Sync Now
                </button>
                <span id="sapwc-lite-sync-result"></span>
            </p>
        </form>

        <div class="sapwc-lite-pro-features-preview" style="margin-top:30px;padding:20px;background:#f9f9f9;border:1px solid #ddd;border-radius:8px;">
            <h3 style="margin-top:0;">PRO Features Available</h3>
            <ul style="list-style:disc;margin-left:20px;">
                <li><strong>Full Product Import</strong> - Import products from SAP with images, attributes, variations</li>
                <li><strong>Order Sync</strong> - Automatically send orders to SAP as Sales Orders</li>
                <li><strong>Customer Sync</strong> - Create Business Partners from WooCommerce customers</li>
                <li><strong>Field Mapping</strong> - Map any SAP field to WooCommerce attributes</li>
                <li><strong>REST API</strong> - External integrations and webhooks</li>
                <li><strong>Multi-channel</strong> - TikTok Shop, Amazon, Miravia integrations</li>
            </ul>
            <a href="?page=sapwc-lite-pro" class="button">See All PRO Features</a>
        </div>
        <?php
    }

    public function render_logs_page()
    {
        $logs = SAPWC_Lite_Logger::get_logs(100);
        ?>
        <div class="wrap">
            <h1>SAP Woo Suite Lite - Logs</h1>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4">No logs yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['created_at']); ?></td>
                                <td><?php echo esc_html($log['action']); ?></td>
                                <td>
                                    <span class="sapwc-lite-status-<?php echo esc_attr($log['status']); ?>">
                                        <?php echo esc_html(strtoupper($log['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Initialize
new SAPWC_Lite_Settings_Page();
