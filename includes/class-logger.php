<?php
/**
 * Simple Logger for Replanta Connector with SAP for WooCommerce
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
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sapwc_logs (
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            self::create_table();
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
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
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return [];
        }

        $base = "SELECT * FROM {$wpdb->prefix}sapwc_logs";

        if ( $action && $status ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return $wpdb->get_results(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from $wpdb->prefix
                    "{$base} WHERE action = %s AND status = %s ORDER BY created_at DESC LIMIT %d",
                    sanitize_text_field( $action ),
                    sanitize_text_field( $status ),
                    (int) $limit
                ),
                ARRAY_A
            );
        }

        if ( $action ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return $wpdb->get_results(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    "{$base} WHERE action = %s ORDER BY created_at DESC LIMIT %d",
                    sanitize_text_field( $action ),
                    (int) $limit
                ),
                ARRAY_A
            );
        }

        if ( $status ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return $wpdb->get_results(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    "{$base} WHERE status = %s ORDER BY created_at DESC LIMIT %d",
                    sanitize_text_field( $status ),
                    (int) $limit
                ),
                ARRAY_A
            );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "{$base} ORDER BY created_at DESC LIMIT %d",
                (int) $limit
            ),
            ARRAY_A
        );
    }

    /**
     * Clear old logs (keep last 7 days)
     */
    public static function cleanup($days = 7)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix is safe
            "DELETE FROM {$wpdb->prefix}sapwc_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sapwc_logs" );
    }
}

// Create table on plugin activation
register_activation_hook(SAPWC_LITE_FILE, ['SAPWC_Lite_Logger', 'create_table']);

// Schedule daily cleanup
add_action('sapwc_lite_daily_cleanup', ['SAPWC_Lite_Logger', 'cleanup']);
if (!wp_next_scheduled('sapwc_lite_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'sapwc_lite_daily_cleanup');
}
