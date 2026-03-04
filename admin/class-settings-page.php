<?php
/**
 * Admin Settings Page for SAP Woo Suite Lite
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
        add_action('admin_init', [$this, 'register_settings']);
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

    public function register_settings()
    {
        // Connection settings
        register_setting('sapwc_lite_settings', 'sapwc_lite_sap_url');
        register_setting('sapwc_lite_settings', 'sapwc_lite_sap_user');
        register_setting('sapwc_lite_settings', 'sapwc_lite_sap_pass');
        register_setting('sapwc_lite_settings', 'sapwc_lite_sap_db');
        register_setting('sapwc_lite_settings', 'sapwc_lite_sap_ssl');

        // Sync settings
        register_setting('sapwc_lite_sync', 'sapwc_lite_price_list');
        register_setting('sapwc_lite_sync', 'sapwc_lite_warehouses');
        register_setting('sapwc_lite_sync', 'sapwc_lite_sync_stock');
        register_setting('sapwc_lite_sync', 'sapwc_lite_sync_prices');
        register_setting('sapwc_lite_sync', 'sapwc_lite_auto_sync');
        register_setting('sapwc_lite_sync', 'sapwc_lite_sync_interval');
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
        $url  = get_option('sapwc_lite_sap_url', '');
        $user = get_option('sapwc_lite_sap_user', '');
        $pass = get_option('sapwc_lite_sap_pass', '');
        $db   = get_option('sapwc_lite_sap_db', '');
        $ssl  = get_option('sapwc_lite_sap_ssl', '0');
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('sapwc_lite_settings'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="sapwc_lite_sap_url">Service Layer URL</label></th>
                    <td>
                        <input type="url" id="sapwc_lite_sap_url" name="sapwc_lite_sap_url" 
                               value="<?php echo esc_attr($url); ?>" class="regular-text" 
                               placeholder="https://sap-server:50000" required>
                        <p class="description">SAP Business One Service Layer URL (without /b1s/v1)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sapwc_lite_sap_user">Username</label></th>
                    <td>
                        <input type="text" id="sapwc_lite_sap_user" name="sapwc_lite_sap_user" 
                               value="<?php echo esc_attr($user); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sapwc_lite_sap_pass">Password</label></th>
                    <td>
                        <input type="password" id="sapwc_lite_sap_pass" name="sapwc_lite_sap_pass" 
                               value="<?php echo esc_attr($pass); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sapwc_lite_sap_db">Company Database</label></th>
                    <td>
                        <input type="text" id="sapwc_lite_sap_db" name="sapwc_lite_sap_db" 
                               value="<?php echo esc_attr($db); ?>" class="regular-text" 
                               placeholder="SBODEMOUS" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="sapwc_lite_sap_ssl">SSL Verification</label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="sapwc_lite_sap_ssl" name="sapwc_lite_sap_ssl" 
                                   value="1" <?php checked($ssl, '1'); ?>>
                            Verify SSL certificate (recommended for production)
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <?php submit_button('Save Connection', 'primary', 'submit', false); ?>
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

        $selected_tariff = get_option('sapwc_lite_price_list', '');
        $selected_warehouses = get_option('sapwc_lite_warehouses', []);
        if (!is_array($selected_warehouses)) {
            $selected_warehouses = [];
        }
        $sync_stock = get_option('sapwc_lite_sync_stock', '1');
        $sync_prices = get_option('sapwc_lite_sync_prices', '1');
        $auto_sync = get_option('sapwc_lite_auto_sync', '0');
        $interval = get_option('sapwc_lite_sync_interval', 'hourly');
        $last_sync = get_option('sapwc_lite_last_sync', 'Never');
        ?>
        <?php if (!$conn): ?>
            <div class="notice notice-warning">
                <p><strong>Configure your SAP connection first</strong> before setting up sync options.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('sapwc_lite_sync'); ?>

            <h2>Sync Configuration</h2>

            <table class="form-table">
                <tr>
                    <th><label for="sapwc_lite_price_list">Price List</label></th>
                    <td>
                        <select id="sapwc_lite_price_list" name="sapwc_lite_price_list" <?php echo empty($price_lists) ? 'disabled' : ''; ?>>
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
                                    <input type="checkbox" name="sapwc_lite_warehouses[]" 
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
                            <input type="checkbox" name="sapwc_lite_sync_stock" value="1" 
                                   <?php checked($sync_stock, '1'); ?>>
                            <strong>Sync Stock</strong> - Update WooCommerce stock quantities from SAP
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="sapwc_lite_sync_prices" value="1" 
                                   <?php checked($sync_prices, '1'); ?>>
                            <strong>Sync Prices</strong> - Update WooCommerce prices from SAP price list
                        </label>
                    </td>
                </tr>
            </table>

            <h2>Automatic Sync</h2>

            <table class="form-table">
                <tr>
                    <th><label for="sapwc_lite_auto_sync">Enable Auto Sync</label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="sapwc_lite_auto_sync" name="sapwc_lite_auto_sync" 
                                   value="1" <?php checked($auto_sync, '1'); ?>>
                            Automatically sync stock and prices on schedule
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><label for="sapwc_lite_sync_interval">Sync Interval</label></th>
                    <td>
                        <select id="sapwc_lite_sync_interval" name="sapwc_lite_sync_interval">
                            <option value="hourly" <?php selected($interval, 'hourly'); ?>>Every Hour</option>
                            <option value="twicedaily" <?php selected($interval, 'twicedaily'); ?>>Twice Daily</option>
                            <option value="daily" <?php selected($interval, 'daily'); ?>>Daily</option>
                        </select>
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
                <?php submit_button('Save Sync Settings', 'primary', 'submit', false); ?>
                <button type="button" id="sapwc-lite-sync-now" class="button" <?php echo !$conn ? 'disabled' : ''; ?>>
                    Sync Now
                </button>
                <span id="sapwc-lite-sync-result"></span>
            </p>
        </form>
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
                        <th>Context</th>
                        <th>Level</th>
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
                                <td><?php echo esc_html($log['context']); ?></td>
                                <td>
                                    <span class="sapwc-lite-level-<?php echo esc_attr($log['level']); ?>">
                                        <?php echo esc_html(strtoupper($log['level'])); ?>
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
