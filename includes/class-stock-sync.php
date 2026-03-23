<?php
/**
 * Stock and Price Sync for Replanta Connector with SAP for WooCommerce
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_Stock_Sync
{
    /**
     * Initialize hooks
     */
    public static function init()
    {
        // Register cron hook
        add_action('sapwc_lite_cron_sync', [__CLASS__, 'run_sync']);

        // Schedule cron if enabled
        add_action('init', [__CLASS__, 'maybe_schedule_cron']);

        // AJAX handlers
        add_action('wp_ajax_sapwc_lite_sync_now', [__CLASS__, 'ajax_sync_now']);
        add_action('wp_ajax_sapwc_lite_test_connection', [__CLASS__, 'ajax_test_connection']);
    }

    /**
     * Schedule cron job if auto-sync is enabled
     */
    public static function maybe_schedule_cron()
    {
        // Use PRO-compatible option names
        $sync_stock = get_option('sapwc_sync_stock_auto', '1') === '1';
        $interval = get_option('sapwc_stock_sync_interval', 'hourly');

        if ($sync_stock) {
            if (!wp_next_scheduled('sapwc_lite_cron_sync')) {
                wp_schedule_event(time() + 60, $interval, 'sapwc_lite_cron_sync');
            }
        } else {
            $timestamp = wp_next_scheduled('sapwc_lite_cron_sync');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'sapwc_lite_cron_sync');
            }
        }
    }

    /**
     * Run the sync process
     */
    public static function run_sync($is_ajax = false)
    {
        $conn = sapwc_lite_get_connection();
        if (!$conn) {
            if ($is_ajax) {
                return ['success' => false, 'message' => 'SAP connection not configured.'];
            }
            SAPWC_Lite_Logger::log(0, 'stock_sync', 'error', 'No SAP connection configured');
            return false;
        }

        $client = new SAPWC_Lite_API_Client($conn['url']);
        $login = $client->login($conn['user'], $conn['pass'], $conn['db'], $conn['ssl']);

        if (!$login['success']) {
            $msg = 'SAP login failed: ' . ($login['message'] ?? 'Unknown error');
            SAPWC_Lite_Logger::log(0, 'stock_sync', 'error', $msg);
            if ($is_ajax) {
                return ['success' => false, 'message' => $msg];
            }
            return false;
        }

        // Get sync settings (PRO-compatible option names)
        $tariff = get_option('sapwc_selected_tariff', '');
        $warehouses = get_option('sapwc_selected_warehouses', []);
        $sync_stock = get_option('sapwc_sync_stock_auto', '1') === '1';
        $sync_prices = get_option('sapwc_sync_prices_auto', '1') === '1';

        if (empty($tariff) || empty($warehouses)) {
            $msg = 'Missing configuration: select a price list and at least one warehouse.';
            SAPWC_Lite_Logger::log(0, 'stock_sync', 'error', $msg);
            if ($is_ajax) {
                return ['success' => false, 'message' => $msg];
            }
            return false;
        }

        if (!is_array($warehouses)) {
            $warehouses = [$warehouses];
        }

        // Fetch items from SAP
        $response = $client->get('/Items?$select=ItemCode,ItemPrices,ItemWarehouseInfoCollection');

        if (!isset($response['value'])) {
            $msg = 'Failed to fetch items from SAP.';
            SAPWC_Lite_Logger::log(0, 'stock_sync', 'error', $msg);
            if ($is_ajax) {
                return ['success' => false, 'message' => $msg];
            }
            return false;
        }

        $prices_include_tax = get_option('woocommerce_prices_include_tax') === 'yes';
        $updated = 0;
        $skipped = 0;

        foreach ($response['value'] as $item) {
            $sku = $item['ItemCode'];

            $product_id = wc_get_product_id_by_sku($sku);
            if (!$product_id) {
                $skipped++;
                continue;
            }

            $product = wc_get_product($product_id);
            if (!$product) {
                $skipped++;
                continue;
            }

            // Calculate stock from selected warehouses
            $stock_total = 0;
            if ($sync_stock && isset($item['ItemWarehouseInfoCollection'])) {
                foreach ($item['ItemWarehouseInfoCollection'] as $wh) {
                    if (in_array($wh['WarehouseCode'], $warehouses, true)) {
                        $stock_total += (float) $wh['InStock'];
                    }
                }
            }

            // Get price from selected price list
            $price = null;
            if ($sync_prices && isset($item['ItemPrices'])) {
                foreach ($item['ItemPrices'] as $priceItem) {
                    if ((string) $priceItem['PriceList'] === (string) $tariff) {
                        $price = (float) $priceItem['Price'];
                        break;
                    }
                }
            }

            // Apply tax if needed
            $price_final = $price;
            if ($prices_include_tax && $price !== null) {
                $tax_class = $product->get_tax_class();
                $tax_rates = WC_Tax::get_rates($tax_class);
                if (!empty($tax_rates)) {
                    $rate = reset($tax_rates);
                    if (isset($rate['rate'])) {
                        $price_final = round($price * (1 + ((float) $rate['rate'] / 100)), 4);
                    }
                }
            }

            // Update product
            if ($sync_stock) {
                $product->set_manage_stock(true);
                $product->set_stock_quantity((int) $stock_total);
                $product->set_stock_status($stock_total > 0 ? 'instock' : 'outofstock');
            }

            if ($sync_prices && $price_final !== null) {
                $product->set_regular_price($price_final);
                $product->set_price($price_final);
            }

            $product->save();
            $updated++;
        }

        $client->logout();

        // Use PRO-compatible option name
        update_option('sapwc_stock_last_sync', current_time('mysql'));

        $msg = "Sync completed: $updated products updated, $skipped skipped.";
        SAPWC_Lite_Logger::log(0, 'stock_sync', 'success', $msg);

        if ($is_ajax) {
            $pro_tips = array(
                'Stock and prices in sync. Want orders to flow to SAP too? Try PRO.',
                'Inventory up to date. Sync orders, customers and products with PRO.',
                'Sync running smoothly. PRO adds order sync, customer sync and multi-channel.',
                'Products updated. With PRO, new products are also imported from SAP.',
            );
            return [
                'success'   => true,
                'message'   => $msg,
                'updated'   => $updated,
                'skipped'   => $skipped,
                'last_sync' => get_option('sapwc_stock_last_sync'),
                'pro_tip'   => $pro_tips[ array_rand( $pro_tips ) ],
            ];
        }

        return true;
    }

    /**
     * AJAX handler for manual sync
     */
    public static function ajax_sync_now()
    {
        check_ajax_referer('sapwc_lite_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $result = self::run_sync(true);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX handler for connection test
     */
    public static function ajax_test_connection()
    {
        check_ajax_referer('sapwc_lite_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $url  = sanitize_text_field( wp_unslash( $_POST['url'] ?? '' ) );
        $user = sanitize_text_field( wp_unslash( $_POST['user'] ?? '' ) );
        $pass = wp_unslash( $_POST['pass'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password cannot be sanitized
        $db   = sanitize_text_field( wp_unslash( $_POST['db'] ?? '' ) );
        $ssl  = sanitize_text_field( wp_unslash( $_POST['ssl'] ?? '0' ) ) === '1';

        if (empty($url) || empty($user) || empty($pass) || empty($db)) {
            wp_send_json_error(['message' => 'All fields are required.']);
        }

        $client = new SAPWC_Lite_API_Client($url);
        $result = $client->login($user, $pass, $db, $ssl);

        if ($result['success']) {
            $version = $client->get_version_info();
            $client->logout();

            wp_send_json_success([
                'message' => 'Connection successful!',
                'version' => $version
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Connection failed: ' . ($result['message'] ?? 'Unknown error')
            ]);
        }
    }
}
