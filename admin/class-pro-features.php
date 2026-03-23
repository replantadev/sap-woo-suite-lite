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
            'sapwc-lite',
            __( 'PRO Features', 'replanta-connector-sap-woocommerce' ),
            '<span style="color:#41999f;">' . esc_html__( 'PRO Features', 'replanta-connector-sap-woocommerce' ) . '</span>',
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
                'title' => __( 'Full Product Import', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Products are imported from SAP with images, attributes, categories, and variations -- no manual data entry needed.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Stock & prices only', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'clipboard',
                'title' => __( 'Order Sync to SAP', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Every WooCommerce order flows to SAP as a Sales Order -- zero copy-paste, zero delays.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'groups',
                'title' => __( 'Customer Sync', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Business Partners are created and updated in SAP from WooCommerce customers -- including B2B addresses.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'category',
                'title' => __( 'Category Import', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'SAP item groups are imported as WooCommerce categories with full hierarchy.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'admin-settings',
                'title' => __( 'Field Mapping', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Map any SAP field (including UDFs) to WooCommerce attributes. Data flows on every sync cycle.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'rest-api',
                'title' => __( 'REST API & Webhooks', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Expose API endpoints for external integrations -- trigger workflows and connect third-party tools.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'store',
                'title' => __( 'Multi-channel Support', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'TikTok Shop, Amazon, and Miravia orders sync to SAP through SAP Woo Suite addons.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'update',
                'title' => __( 'Smart Retry System', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Failed orders retry with intelligent backoff -- no manual re-sending required.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Not available', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'database',
                'title' => __( 'Multi-warehouse Support', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Different warehouses per product with stock aggregation and warehouse-specific pricing.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Global warehouse only', 'replanta-connector-sap-woocommerce' ),
            ),
            array(
                'icon'  => 'analytics',
                'title' => __( 'Analytics Dashboard', 'replanta-connector-sap-woocommerce' ),
                'desc'  => __( 'Revenue charts, top customers, channel distribution -- all generated from your sync data.', 'replanta-connector-sap-woocommerce' ),
                'lite'  => __( 'Basic logs', 'replanta-connector-sap-woocommerce' ),
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
            <h1><?php esc_html_e( 'PRO Features', 'replanta-connector-sap-woocommerce' ); ?></h1>

            <div class="sapwc-pro-hero">
                <h2><?php esc_html_e( 'Connect Your Entire Store to SAP', 'replanta-connector-sap-woocommerce' ); ?></h2>
                <p>
                    <?php
                    printf(
                        /* translators: %s: Lite badge */
                        esc_html__( "You're using the %s version -- stock and prices stay in sync. Upgrade to PRO to also sync orders, products, customers, and multi-channel sales.", 'replanta-connector-sap-woocommerce' ),
                        '<strong>Lite</strong>'
                    );
                    ?>
                </p>
                <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button button-primary button-hero">
                    <?php esc_html_e( 'Get SAP Woo Suite PRO', 'replanta-connector-sap-woocommerce' ); ?>
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
                                esc_html__( 'Lite: %s', 'replanta-connector-sap-woocommerce' ),
                                esc_html( $feature['lite'] )
                            );
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sapwc-pro-cta">
                <h3><?php esc_html_e( 'Ready for the full SAP connection?', 'replanta-connector-sap-woocommerce' ); ?></h3>
                <p><?php esc_html_e( 'SAP Woo Suite PRO keeps your entire WooCommerce-SAP workflow in sync. Set it up once and let it run.', 'replanta-connector-sap-woocommerce' ); ?></p>
                <div class="sapwc-cta-buttons">
                    <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" class="button-cta-primary">
                        <?php esc_html_e( 'Get SAP Woo Suite PRO', 'replanta-connector-sap-woocommerce' ); ?>
                    </a>
                    <a href="https://replantadev.github.io/sapwoo/" target="_blank" class="button-cta-secondary">
                        <?php esc_html_e( 'Read Documentation', 'replanta-connector-sap-woocommerce' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize.
new SAPWC_Lite_Pro_Features();
