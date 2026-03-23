<?php
/**
 * Dashboard Page for Replanta Connector with SAP for WooCommerce
 *
 * Visual overview of sync status, product health, and recent activity.
 * Designed to match the PRO design system while staying within Lite scope.
 *
 * @package SAPWC_Lite
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SAPWC_Lite_Dashboard_Page {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ), 5 );
    }

    /**
     * Register top-level menu + Dashboard as the default landing page.
     */
    public function register_menu() {
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="black">'
            . '<path d="M10.2 3.28c3.53 0 6.43 2.61 6.92 6h2.08l-3.5 4-3.5-4h2.32c-.45-1.97-2.21-3.45-4.32-3.45-1.45 0-2.73.71-3.54 1.78L4.95 5.66C6.23 4.2 8.11 3.28 10.2 3.28zm-4.4 11.17c.45 1.97 2.21 3.45 4.32 3.45 1.45 0 2.73-.71 3.54-1.78l1.71 1.95c-1.28 1.46-3.15 2.38-5.25 2.38-3.53 0-6.43-2.61-6.92-6H1.12l3.5-4 3.5 4H5.8z"/>'
            . '</svg>'
        );

        /* ── Top-level menu ── */
        add_menu_page(
            __( 'Replanta Connector', 'replanta-connector-sap-woocommerce' ),
            __( 'SAP Woo Lite', 'replanta-connector-sap-woocommerce' ),
            'manage_woocommerce',
            'sapwc-lite',
            array( $this, 'render' ),
            $icon_svg,
            56
        );

        /* ── First submenu: same slug as parent → overrides auto-label ── */
        add_submenu_page(
            'sapwc-lite',
            __( 'Dashboard', 'replanta-connector-sap-woocommerce' ),
            __( 'Dashboard', 'replanta-connector-sap-woocommerce' ),
            'manage_woocommerce',
            'sapwc-lite',
            array( $this, 'render' )
        );
    }

    /* ── Data Helpers ─────────────────────────────────────────────────── */

    /**
     * Count WooCommerce products with managed stock (SAP-synced).
     */
    private static function get_product_stats() {
        global $wpdb;

        $total = (int) wp_count_posts( 'product' )->publish;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $with_sku = (int) $wpdb->get_var( "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                AND pm.meta_key = '_sku' AND pm.meta_value != ''
            WHERE p.post_type = 'product'
              AND p.post_status = 'publish'
        " );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $managed = (int) $wpdb->get_var( "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                AND pm.meta_key = '_manage_stock' AND pm.meta_value = 'yes'
            WHERE p.post_type IN ('product', 'product_variation')
              AND p.post_status = 'publish'
        " );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $in_stock = (int) $wpdb->get_var( "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm  ON p.ID = pm.post_id
                AND pm.meta_key = '_stock_status' AND pm.meta_value = 'instock'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
                AND pm2.meta_key = '_manage_stock' AND pm2.meta_value = 'yes'
            WHERE p.post_type IN ('product', 'product_variation')
              AND p.post_status = 'publish'
        " );

        $out_of_stock = $managed - $in_stock;
        if ( $out_of_stock < 0 ) {
            $out_of_stock = 0;
        }

        return compact( 'total', 'with_sku', 'managed', 'in_stock', 'out_of_stock' );
    }

    /**
     * Get log summary (last 7 days).
     */
    private static function get_log_summary() {
        global $wpdb;
        $table = $wpdb->prefix . 'sapwc_logs';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return array( 'total' => 0, 'success' => 0, 'error' => 0, 'warning' => 0, 'recent' => array() );
        }

        $since = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $counts = $wpdb->get_results( $wpdb->prepare( "
            SELECT status, COUNT(*) AS cnt
            FROM {$wpdb->prefix}sapwc_logs
            WHERE created_at >= %s
              AND action = 'stock_sync'
            GROUP BY status
        ", $since ), ARRAY_A );

        $summary = array( 'total' => 0, 'success' => 0, 'error' => 0, 'warning' => 0 );
        foreach ( $counts as $c ) {
            $s = strtolower( $c['status'] );
            if ( isset( $summary[ $s ] ) ) {
                $summary[ $s ] = (int) $c['cnt'];
            }
            $summary['total'] += (int) $c['cnt'];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $summary['recent'] = $wpdb->get_results( $wpdb->prepare( "
            SELECT action, status, message, created_at
            FROM {$wpdb->prefix}sapwc_logs
            WHERE action = 'stock_sync'
            ORDER BY created_at DESC
            LIMIT %d
        ", 8 ), ARRAY_A );

        return $summary;
    }

    /**
     * Get daily sync event counts for the last N days (for the mini chart).
     */
    private static function get_sync_trend( $days = 14 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sapwc_logs';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return array( 'labels' => array(), 'success' => array(), 'error' => array() );
        }

        $date_from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results( $wpdb->prepare( "
            SELECT DATE(created_at) AS day, status, COUNT(*) AS cnt
            FROM {$wpdb->prefix}sapwc_logs
            WHERE created_at >= %s
              AND action = 'stock_sync'
            GROUP BY day, status
            ORDER BY day
        ", $date_from ), ARRAY_A );

        $map = array();
        foreach ( $rows as $r ) {
            $map[ $r['day'] ][ strtolower( $r['status'] ) ] = (int) $r['cnt'];
        }

        $labels  = array();
        $success = array();
        $error   = array();

        for ( $i = $days; $i >= 0; $i-- ) {
            $d         = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $labels[]  = gmdate( 'd M', strtotime( $d ) );
            $success[] = isset( $map[ $d ]['success'] ) ? $map[ $d ]['success'] : 0;
            $error[]   = isset( $map[ $d ]['error'] ) ? $map[ $d ]['error'] : 0;
        }

        return compact( 'labels', 'success', 'error' );
    }

    /* ── Render ───────────────────────────────────────────────────────── */

    public function render() {
        $conn       = sapwc_lite_get_connection();
        $products   = self::get_product_stats();
        $logs       = self::get_log_summary();
        $trend      = self::get_sync_trend( 14 );
        $last_sync  = get_option( 'sapwc_stock_last_sync', '' );
        $auto_stock = get_option( 'sapwc_sync_stock_auto', '1' ) === '1';
        $auto_price = get_option( 'sapwc_sync_prices_auto', '1' ) === '1';
        $interval   = get_option( 'sapwc_stock_sync_interval', 'hourly' );
        $tariff     = get_option( 'sapwc_selected_tariff', '' );
        $warehouses = get_option( 'sapwc_selected_warehouses', array() );
        $next_cron  = wp_next_scheduled( 'sapwc_lite_cron_sync' );

        // Health score
        $health = 0;
        if ( $conn )           $health += 25;
        if ( $tariff )         $health += 15;
        if ( ! empty( $warehouses ) ) $health += 15;
        if ( $last_sync )      $health += 20;
        if ( $logs['error'] === 0 && $logs['total'] > 0 ) $health += 25;
        elseif ( $logs['total'] > 0 && $logs['error'] < $logs['success'] ) $health += 10;

        $health_class = $health >= 80 ? 'success' : ( $health >= 50 ? 'warning' : 'danger' );

        // Stock distribution for doughnut
        $stock_dist = array(
            'labels' => array(
                __( 'In stock', 'replanta-connector-sap-woocommerce' ),
                __( 'Out of stock', 'replanta-connector-sap-woocommerce' ),
                __( 'Unmanaged', 'replanta-connector-sap-woocommerce' ),
            ),
            'values' => array(
                $products['in_stock'],
                $products['out_of_stock'],
                max( 0, $products['total'] - $products['managed'] ),
            ),
        );

        // JS data for charts
        $js_data = array(
            'syncTrend' => $trend,
            'stockDist' => $stock_dist,
            'health'    => $health,
        );

        ?>
        <script>var sapwcLiteDash = <?php echo wp_json_encode( $js_data ); ?>;</script>

        <div class="wrap sapwc-wrap">

            <!-- Header -->
            <div class="sapwc-dashboard-header">
                <div class="sapwc-dashboard-header__left">
                    <h1>
                        <span class="dashicons dashicons-chart-area"></span>
                        <?php esc_html_e( 'Dashboard', 'replanta-connector-sap-woocommerce' ); ?>
                    </h1>
                    <p class="sapwc-subtitle">
                        <?php esc_html_e( 'Your inventory stays in sync with SAP Business One.', 'replanta-connector-sap-woocommerce' ); ?>
                    </p>
                </div>
                <div class="sapwc-dashboard-header__right">
                    <span class="sapwc-badge sapwc-badge--neutral">
                        Lite v<?php echo esc_html( SAPWC_LITE_VERSION ); ?>
                    </span>
                    <?php if ( $conn ) : ?>
                        <span class="sapwc-badge sapwc-badge--success">
                            <span class="sapwc-dot sapwc-dot--success sapwc-dot--pulse"></span>
                            <?php esc_html_e( 'Connected & syncing', 'replanta-connector-sap-woocommerce' ); ?>
                        </span>
                    <?php else : ?>
                        <span class="sapwc-badge sapwc-badge--danger">
                            <span class="sapwc-dot sapwc-dot--danger"></span>
                            <?php esc_html_e( 'Setup needed', 'replanta-connector-sap-woocommerce' ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>


            <!-- KPI Row -->
            <div class="sapwc-section">
                <div class="sapwc-grid sapwc-grid--4">

                    <!-- Health Score -->
                    <div class="sapwc-kpi sapwc-kpi--<?php echo esc_attr( $health_class ); ?>">
                        <div class="sapwc-kpi__icon">
                            <span class="dashicons dashicons-heart"></span>
                        </div>
                        <div class="sapwc-kpi__value"><?php echo (int) $health; ?>%</div>
                        <div class="sapwc-kpi__label"><?php esc_html_e( 'Sync Health', 'replanta-connector-sap-woocommerce' ); ?></div>
                        <div class="sapwc-kpi__detail">
                            <?php
                            if ( $health >= 80 ) {
                                esc_html_e( 'Running smoothly', 'replanta-connector-sap-woocommerce' );
                            } elseif ( $health >= 50 ) {
                                esc_html_e( 'Almost ready', 'replanta-connector-sap-woocommerce' );
                            } else {
                                esc_html_e( 'Complete setup to start', 'replanta-connector-sap-woocommerce' );
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Products managed -->
                    <div class="sapwc-kpi sapwc-kpi--info">
                        <div class="sapwc-kpi__icon">
                            <span class="dashicons dashicons-products"></span>
                        </div>
                        <div class="sapwc-kpi__value"><?php echo esc_html( number_format( $products['managed'], 0, ',', '.' ) ); ?></div>
                        <div class="sapwc-kpi__label"><?php esc_html_e( 'Products Managed', 'replanta-connector-sap-woocommerce' ); ?></div>
                        <div class="sapwc-kpi__detail">
                            <?php
                            /* translators: %s: total number of published products */
                            printf( esc_html__( 'of %s published', 'replanta-connector-sap-woocommerce' ), esc_html( number_format( $products['total'], 0, ',', '.' ) ) ); ?>
                        </div>
                    </div>

                    <!-- Last sync -->
                    <div class="sapwc-kpi">
                        <div class="sapwc-kpi__icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="sapwc-kpi__value" style="font-size: var(--sapwc-text-lg);">
                            <?php
                            if ( $last_sync ) {
                                echo esc_html( date_i18n( 'd M H:i', strtotime( $last_sync ) ) );
                            } else {
                                esc_html_e( 'Never', 'replanta-connector-sap-woocommerce' );
                            }
                            ?>
                        </div>
                        <div class="sapwc-kpi__label"><?php esc_html_e( 'Last Sync', 'replanta-connector-sap-woocommerce' ); ?></div>
                        <div class="sapwc-kpi__detail">
                            <?php
                            if ( $next_cron ) {
                                printf(
                                    /* translators: %s: date and time of next scheduled sync */
                                    esc_html__( 'Next: %s', 'replanta-connector-sap-woocommerce' ),
                                    esc_html( date_i18n( 'd M H:i', $next_cron ) )
                                );
                            } else {
                                esc_html_e( 'Schedule sync in Settings', 'replanta-connector-sap-woocommerce' );
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Log health -->
                    <div class="sapwc-kpi <?php echo esc_attr( $logs['error'] > 0 ? 'sapwc-kpi--danger' : ( $logs['total'] > 0 ? 'sapwc-kpi--success' : '' ) ); ?>">
                        <div class="sapwc-kpi__icon">
                            <span class="dashicons dashicons-list-view"></span>
                        </div>
                        <div class="sapwc-kpi__value"><?php echo (int) $logs['total']; ?></div>
                        <div class="sapwc-kpi__label"><?php esc_html_e( 'Events (7d)', 'replanta-connector-sap-woocommerce' ); ?></div>
                        <div class="sapwc-kpi__detail">
                            <span style="color: var(--sapwc-success);"><?php echo (int) $logs['success']; ?> ok</span>
                            <?php if ( $logs['error'] > 0 ) : ?>
                                &middot; <span style="color: var(--sapwc-danger);"><?php echo (int) $logs['error']; ?> <?php esc_html_e( 'errors', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- PRO teaser KPI -->
                    <div class="sapwc-kpi sapwc-kpi--locked">
                        <div class="sapwc-kpi__value" style="display:flex;align-items:center;gap:6px;font-size:var(--sapwc-text-lg);">
                            <span class="dashicons dashicons-lock" style="font-size:20px;width:20px;height:20px;"></span>
                            <span>PRO</span>
                        </div>
                        <div class="sapwc-kpi__label"><?php esc_html_e( 'Orders synced', 'replanta-connector-sap-woocommerce' ); ?></div>
                        <div class="sapwc-kpi__detail">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sapwc-lite-pro' ) ); ?>" style="color: var(--sapwc-lite-accent); text-decoration:none; font-weight:600;">
                                <?php esc_html_e( 'Available in PRO', 'replanta-connector-sap-woocommerce' ); ?> &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Charts Row: Sync Trend + Stock Distribution -->
            <div class="sapwc-section">
                <div class="sapwc-grid sapwc-grid--2">

                    <div class="sapwc-card sapwc-card--flush">
                        <div class="sapwc-card__header">
                            <h3 class="sapwc-card__title">
                                <span class="dashicons dashicons-chart-bar"></span>
                                <?php esc_html_e( 'Sync Activity - 14 days', 'replanta-connector-sap-woocommerce' ); ?>
                            </h3>
                        </div>
                        <div style="padding: 0 24px 24px;">
                            <div class="sapwc-chart-wrap" style="height:240px;">
                                <canvas id="sapwc-lite-chart-trend"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="sapwc-card sapwc-card--flush">
                        <div class="sapwc-card__header">
                            <h3 class="sapwc-card__title">
                                <span class="dashicons dashicons-archive"></span>
                                <?php esc_html_e( 'Stock Distribution', 'replanta-connector-sap-woocommerce' ); ?>
                            </h3>
                        </div>
                        <div style="padding: 16px 24px 24px; display:flex; justify-content:center;">
                            <div class="sapwc-chart-wrap" style="height:220px; max-width:320px;">
                                <canvas id="sapwc-lite-chart-stock"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Config Status + Recent Activity -->
            <div class="sapwc-section">
                <div class="sapwc-grid sapwc-grid--2">

                    <!-- Config Summary -->
                    <div class="sapwc-card">
                        <h2>
                            <span class="dashicons dashicons-admin-generic" style="color: var(--sapwc-gray-400);"></span>
                            <?php esc_html_e( 'Current Configuration', 'replanta-connector-sap-woocommerce' ); ?>
                        </h2>
                        <table class="sapwc-table" style="margin-top:12px;">
                            <tbody>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'SAP Connection', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">
                                        <?php if ( $conn ) : ?>
                                            <span class="sapwc-badge sapwc-badge--success"><?php esc_html_e( 'Configured', 'replanta-connector-sap-woocommerce' ); ?></span>
                                        <?php else : ?>
                                            <span class="sapwc-badge sapwc-badge--danger"><?php esc_html_e( 'Not configured', 'replanta-connector-sap-woocommerce' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'Stock sync', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">
                                        <?php
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is hardcoded HTML with esc_html__() values
                                        echo $auto_stock
                                            ? '<span class="sapwc-badge sapwc-badge--success">' . esc_html__( 'Active', 'replanta-connector-sap-woocommerce' ) . '</span>'
                                            : '<span class="sapwc-badge sapwc-badge--neutral">' . esc_html__( 'Disabled', 'replanta-connector-sap-woocommerce' ) . '</span>'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'Price sync', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">
                                        <?php
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is hardcoded HTML with esc_html__() values
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is hardcoded HTML with esc_html__() values
                                        echo $auto_price
                                            ? '<span class="sapwc-badge sapwc-badge--success">' . esc_html__( 'Active', 'replanta-connector-sap-woocommerce' ) . '</span>'
                                            : '<span class="sapwc-badge sapwc-badge--neutral">' . esc_html__( 'Disabled', 'replanta-connector-sap-woocommerce' ) . '</span>'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'SAP Price List', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right; font-weight:700;">
                                        <?php
                                        if ( $tariff ) {
                                            echo esc_html( $tariff );
                                        } else {
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded HTML with esc_html__() value
                                            echo '<span style="color:var(--sapwc-danger);">' . esc_html__( 'Not selected', 'replanta-connector-sap-woocommerce' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'Warehouses', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right; font-weight:700;">
                                        <?php
                                        if ( ! empty( $warehouses ) ) {
                                            echo esc_html( implode( ', ', (array) $warehouses ) );
                                        } else {
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded HTML with esc_html__() value
                                            echo '<span style="color:var(--sapwc-danger);">' . esc_html__( 'Not selected', 'replanta-connector-sap-woocommerce' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'SKU Readiness', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">
                                        <?php
                                        if ( $products['with_sku'] > 0 ) {
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded HTML wrapper with escaped sprintf
                                            printf(
                                                '<span class="sapwc-badge sapwc-badge--success">%s</span>',
                                                /* translators: %1$d: number of products with SKU, %2$d: total products */
                                                sprintf( esc_html__( '%1$d of %2$d products', 'replanta-connector-sap-woocommerce' ), (int) $products['with_sku'], (int) $products['total'] )
                                            );
                                        } else {
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded HTML with esc_html__() value
                                            echo '<span class="sapwc-badge sapwc-badge--danger">' . esc_html__( 'No products with SKU', 'replanta-connector-sap-woocommerce' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'Interval', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">
                                        <?php
                                        $intervals = array(
                                            'hourly'     => __( 'Hourly', 'replanta-connector-sap-woocommerce' ),
                                            'twicedaily' => __( 'Twice daily', 'replanta-connector-sap-woocommerce' ),
                                            'daily'      => __( 'Daily', 'replanta-connector-sap-woocommerce' ),
                                        );
                                        echo esc_html( $intervals[ $interval ] ?? $interval );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:500;"><?php esc_html_e( 'Lite Version', 'replanta-connector-sap-woocommerce' ); ?></td>
                                    <td style="text-align:right;">v<?php echo esc_html( SAPWC_LITE_VERSION ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recent Activity -->
                    <div class="sapwc-card sapwc-card--flush">
                        <div class="sapwc-card__header">
                            <h3 class="sapwc-card__title">
                                <span class="dashicons dashicons-backup"></span>
                                <?php esc_html_e( 'Recent Activity', 'replanta-connector-sap-woocommerce' ); ?>
                            </h3>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sapwc-lite-logs' ) ); ?>" class="sapwc-card__link">
                                <?php esc_html_e( 'View all', 'replanta-connector-sap-woocommerce' ); ?> &rarr;
                            </a>
                        </div>
                        <?php if ( ! empty( $logs['recent'] ) ) : ?>
                            <div style="padding: 0 20px 20px; max-height: 340px; overflow-y: auto;">
                                <?php foreach ( $logs['recent'] as $entry ) : ?>
                                    <div class="sapwc-lite-log-entry sapwc-lite-log-entry--<?php echo esc_attr( $entry['status'] ); ?>">
                                        <div class="sapwc-lite-log-entry__dot"></div>
                                        <div class="sapwc-lite-log-entry__body">
                                            <div class="sapwc-lite-log-entry__msg">
                                                <?php echo esc_html( wp_trim_words( $entry['message'], 16, '...' ) ); ?>
                                            </div>
                                            <div class="sapwc-lite-log-entry__meta">
                                                <span class="sapwc-badge sapwc-badge--<?php
                                                    echo $entry['status'] === 'success' ? 'success' : ( $entry['status'] === 'error' ? 'danger' : 'neutral' );
                                                ?>" style="font-size:10px; padding:1px 6px;">
                                                    <?php echo esc_html( $entry['action'] ); ?>
                                                </span>
                                                <span style="color:var(--sapwc-gray-400); font-size:11px;">
                                                    <?php echo esc_html( date_i18n( 'd M H:i', strtotime( $entry['created_at'] ) ) ); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div style="padding: 40px 24px; text-align:center;">
                                <span class="dashicons dashicons-admin-page" style="font-size:36px; width:36px; height:36px; color:var(--sapwc-gray-300);"></span>
                                <p style="margin-top:8px; color:var(--sapwc-gray-500);">
                                    <?php esc_html_e( 'No activity recorded yet.', 'replanta-connector-sap-woocommerce' ); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>


            <!-- PRO Upgrade Banner -->
            <div class="sapwc-section">
                <div class="sapwc-lite-upgrade-banner">
                    <div class="sapwc-lite-upgrade-banner__content">
                        <h2><?php esc_html_e( 'Your entire store, connected to SAP', 'replanta-connector-sap-woocommerce' ); ?></h2>
                        <p>
                            <?php esc_html_e( 'SAP Woo Suite PRO syncs orders, customers and products with SAP Business One. Set it up once and let it run -- no manual data entry.', 'replanta-connector-sap-woocommerce' ); ?>
                        </p>
                        <div class="sapwc-lite-upgrade-banner__features">
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Orders to SAP', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Full product import', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'B2B customer sync', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'TikTok Shop + Amazon', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Smart retry on failures', 'replanta-connector-sap-woocommerce' ); ?></span>
                            <span><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Revenue dashboard', 'replanta-connector-sap-woocommerce' ); ?></span>
                        </div>
                    </div>
                    <div class="sapwc-lite-upgrade-banner__cta">
                        <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="sapwc-btn sapwc-btn--primary sapwc-btn--lg">
                            <?php esc_html_e( 'Get SAP Woo Suite PRO', 'replanta-connector-sap-woocommerce' ); ?> &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- .sapwc-wrap -->
        <?php
    }
}

// Initialize.
new SAPWC_Lite_Dashboard_Page();
