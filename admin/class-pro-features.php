<?php
/**
 * PRO Features Page - Shows blocked features with upgrade prompts
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PRO Features promotion page class.
 *
 * @since 1.0.0
 */
class SAPWC_Lite_Pro_Features {

    /**
     * Constructor - register hooks.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_pro_menu' ), 20 );
    }

    /**
     * Add PRO Features submenu page.
     */
    public function add_pro_menu() {
        add_submenu_page(
            'sapwc-lite-settings',
            __( 'PRO Features', 'sap-woo-suite-lite' ),
            '<span style="color:#41999f;">' . esc_html__( 'PRO Features', 'sap-woo-suite-lite' ) . '</span>',
            'manage_woocommerce',
            'sapwc-lite-pro',
            array( $this, 'render_page' )
        );
    }

    /**
     * Get list of PRO features.
     *
     * @return array Array of feature data.
     */
    private function get_features() {
        return array(
            array(
                'icon'  => 'cart',
                'title' => __( 'Full Product Sync', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Import products from SAP to WooCommerce with full attribute mapping, images, categories, and variations.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Stock & prices only', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'clipboard',
                'title' => __( 'Order Sync to SAP', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Automatically send WooCommerce orders to SAP Business One as Sales Orders with full line item details.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'groups',
                'title' => __( 'Customer Sync', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Create and update Business Partners in SAP from WooCommerce customers with address synchronization.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'category',
                'title' => __( 'Category Import', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Import SAP item groups as WooCommerce categories with hierarchy support.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'admin-settings',
                'title' => __( 'Advanced Field Mapping', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Map any SAP field to WooCommerce product attributes, including UDFs and custom fields.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'rest-api',
                'title' => __( 'REST API Endpoints', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Expose API endpoints for external integrations, webhooks, and automation workflows.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'store',
                'title' => __( 'Multi-channel Support', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Connect TikTok Shop, Amazon, Miravia, and other marketplaces via SAP Woo Suite addons.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'update',
                'title' => __( 'Auto-retry Failed Orders', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Intelligent retry system with exponential backoff for failed SAP orders.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Not available', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'database',
                'title' => __( 'Multiple Warehouses per Product', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Assign different warehouses to different products with warehouse-specific tariffs.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Global warehouse only', 'sap-woo-suite-lite' ),
            ),
            array(
                'icon'  => 'analytics',
                'title' => __( 'Detailed Logging & Analytics', 'sap-woo-suite-lite' ),
                'desc'  => __( 'Comprehensive logs with filtering, export, and sync statistics dashboard.', 'sap-woo-suite-lite' ),
                'lite'  => __( 'Basic logs', 'sap-woo-suite-lite' ),
            ),
        );
    }

    /**
     * Render the PRO Features page.
     */
    public function render_page() {
        $features = $this->get_features();
        ?>
        <div class="wrap sapwc-wrap">
            <h1><?php esc_html_e( 'PRO Features', 'sap-woo-suite-lite' ); ?></h1>

            <div class="sapwc-pro-hero">
                <h2><?php esc_html_e( 'Unlock the Full Power of SAP Woo Suite', 'sap-woo-suite-lite' ); ?></h2>
                <p>
                    <?php
                    printf(
                        /* translators: %s: Lite badge */
                        esc_html__( "You're using the %s version with stock and price sync. Upgrade to PRO for complete SAP Business One integration.", 'sap-woo-suite-lite' ),
                        '<strong>Lite</strong>'
                    );
                    ?>
                </p>
                <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button button-primary button-hero">
                    <?php esc_html_e( 'Get SAP Woo Suite PRO', 'sap-woo-suite-lite' ); ?>
                </a>
            </div>

            <div class="sapwc-pro-features-grid">
                <?php foreach ( $features as $feature ) : ?>
                    <div class="sapwc-pro-feature-card">
                        <div class="sapwc-pro-feature-icon">
                            <span class="dashicons dashicons-<?php echo esc_attr( $feature['icon'] ); ?>"></span>
                        </div>
                        <h3><?php echo esc_html( $feature['title'] ); ?></h3>
                        <p><?php echo esc_html( $feature['desc'] ); ?></p>
                        <div class="sapwc-pro-feature-lite">
                            <span class="dashicons dashicons-warning"></span>
                            <?php
                            printf(
                                /* translators: %s: Feature limitation in Lite version */
                                'Lite: %s',
                                esc_html( $feature['lite'] )
                            );
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sapwc-pro-cta">
                <h3><?php esc_html_e( 'Ready to upgrade?', 'sap-woo-suite-lite' ); ?></h3>
                <p><?php esc_html_e( 'Get SAP Woo Suite PRO and connect your entire business workflow.', 'sap-woo-suite-lite' ); ?></p>
                <div class="sapwc-cta-buttons">
                    <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button-cta-primary">
                        <?php esc_html_e( 'View Pricing', 'sap-woo-suite-lite' ); ?>
                    </a>
                    <a href="https://replantadev.github.io/sapwoo/" target="_blank" class="button-cta-secondary">
                        <?php esc_html_e( 'Read Documentation', 'sap-woo-suite-lite' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize.
new SAPWC_Lite_Pro_Features();
