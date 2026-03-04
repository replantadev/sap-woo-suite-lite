<?php
/**
 * Simple Logger for SAP Woo Suite Lite
 *
 * Uses same table structure as PRO version (sapwc_logs) for seamless upgrade.
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_Logger
{
    // Use same table name as PRO for compatibility
    const TABLE_NAME = 'sapwc_logs';

    /**
     * Create logs table on plugin activation
     * Uses same structure as PRO version
     */
    public static function create_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $charset = $wpdb->get_charset_collate();

        // Same table structure as PRO version
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED DEFAULT 0,
            action VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            docentry BIGINT UNSIGNED DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_action (action),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log a message - compatible with PRO version
     *
     * @param int    $order_id Order ID (0 for non-order actions)
     * @param string $action   Action type (stock_sync, price_sync, connection, etc.)
     * @param string $status   Status (success, error, warning, info)
     * @param string $message  Log message
     * @param int    $docentry SAP DocEntry reference
     */
    public static function log($order_id, $action, $status, $message, $docentry = null)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;

        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            self::create_table();
        }

        $wpdb->insert($table, [
            'order_id'   => is_numeric($order_id) ? (int) $order_id : 0,
            'action'     => sanitize_text_field($action),
            'status'     => sanitize_text_field($status),
            'message'    => sanitize_textarea_field($message),
            'docentry'   => is_numeric($docentry) ? (int) $docentry : null,
            'created_at' => current_time('mysql')
        ], ['%d', '%s', '%s', '%s', '%d', '%s']);

        // Also log to error_log in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[SAPWC Lite] [$action] [$status] $message");
        }
    }

    /**
     * Get recent logs
     */
    public static function get_logs($limit = 100, $action = null, $status = null)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }

        $where = ['1=1'];
        $values = [];

        if ($action) {
            $where[] = 'action = %s';
            $values[] = $action;
        }

        if ($status) {
            $where[] = 'status = %s';
            $values[] = $status;
        }

        $where_sql = implode(' AND ', $where);
        $values[] = (int) $limit;

        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d";

        if (count($values) > 1) {
            $sql = $wpdb->prepare($sql, $values);
        } else {
            $sql = $wpdb->prepare($sql, $limit);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Clear old logs (keep last 7 days)
     */
    public static function cleanup($days = 7)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }

    /**
     * Drop table on uninstall
     * Note: Only called if PRO is not installed (checked in uninstall.php)
     */
    public static function drop_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}

// Create table on plugin activation
register_activation_hook(SAPWC_LITE_FILE, ['SAPWC_Lite_Logger', 'create_table']);

// Schedule daily cleanup
add_action('sapwc_lite_daily_cleanup', ['SAPWC_Lite_Logger', 'cleanup']);
if (!wp_next_scheduled('sapwc_lite_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'sapwc_lite_daily_cleanup');
}
