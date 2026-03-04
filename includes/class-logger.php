<?php
/**
 * Simple Logger for SAP Woo Suite Lite
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_Logger
{
    const TABLE_NAME = 'sapwc_lite_logs';

    /**
     * Create logs table on plugin activation
     */
    public static function create_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED DEFAULT 0,
            context VARCHAR(50) NOT NULL,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            sap_ref VARCHAR(100) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_context (context),
            INDEX idx_level (level),
            INDEX idx_created (created_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log a message
     */
    public static function log($order_id, $context, $level, $message, $sap_ref = null)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;

        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            self::create_table();
        }

        $wpdb->insert($table, [
            'order_id'   => (int) $order_id,
            'context'    => sanitize_text_field($context),
            'level'      => sanitize_text_field($level),
            'message'    => sanitize_textarea_field($message),
            'sap_ref'    => $sap_ref ? sanitize_text_field($sap_ref) : null,
            'created_at' => current_time('mysql')
        ], ['%d', '%s', '%s', '%s', '%s', '%s']);

        // Also log to error_log in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[SAPWC Lite] [$context] [$level] $message");
        }
    }

    /**
     * Get recent logs
     */
    public static function get_logs($limit = 100, $context = null, $level = null)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;

        $where = ['1=1'];
        $values = [];

        if ($context) {
            $where[] = 'context = %s';
            $values[] = $context;
        }

        if ($level) {
            $where[] = 'level = %s';
            $values[] = $level;
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
