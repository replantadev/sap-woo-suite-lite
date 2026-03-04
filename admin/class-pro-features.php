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
        <div class="wrap sapwc-lite-wrap">
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
                <a href="https://replanta.dev/sap-woo-suite" target="_blank" class="button button-primary button-hero">
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
                    <a href="https://replanta.dev/sap-woo-suite" target="_blank" class="button-cta-primary">
                        <?php esc_html_e( 'View Pricing', 'sap-woo-suite-lite' ); ?>
                    </a>
                    <a href="https://replantadev.github.io/sapwoo/" target="_blank" class="button-cta-secondary">
                        <?php esc_html_e( 'Read Documentation', 'sap-woo-suite-lite' ); ?>
                    </a>
                </div>
            </div>
        </div>

        <style>
            .sapwc-pro-hero {
                background: linear-gradient(135deg, #41999f 0%, #357a7f 100%);
                color: #fff;
                padding: 40px;
                border-radius: 8px;
                text-align: center;
                margin: 20px 0;
            }
            .sapwc-pro-hero h2 {
                color: #fff;
                margin: 0 0 10px;
            }
            .sapwc-pro-hero p {
                font-size: 16px;
                margin: 0 0 20px;
                opacity: 0.9;
            }
            .sapwc-pro-hero .button-hero {
                background: #fff;
                color: #41999f;
                border: none;
                font-weight: 600;
            }
            .sapwc-pro-hero .button-hero:hover {
                background: #f0f0f0;
                color: #357a7f;
            }

            .sapwc-pro-features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }

            .sapwc-pro-feature-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 24px;
                position: relative;
            }
            .sapwc-pro-feature-card::before {
                content: 'PRO';
                position: absolute;
                top: 12px;
                right: 12px;
                background: #41999f;
                color: #fff;
                font-size: 10px;
                font-weight: 600;
                padding: 2px 8px;
                border-radius: 4px;
            }

            .sapwc-pro-feature-icon {
                width: 48px;
                height: 48px;
                background: #f0f7f7;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
            }
            .sapwc-pro-feature-icon .dashicons {
                font-size: 24px;
                width: 24px;
                height: 24px;
                color: #41999f;
            }

            .sapwc-pro-feature-card h3 {
                margin: 0 0 8px;
                font-size: 16px;
            }
            .sapwc-pro-feature-card p {
                margin: 0 0 12px;
                color: #666;
                font-size: 13px;
                line-height: 1.5;
            }

            .sapwc-pro-feature-lite {
                background: #fff8e5;
                border: 1px solid #ffd54f;
                border-radius: 4px;
                padding: 8px 12px;
                font-size: 12px;
                color: #856404;
            }
            .sapwc-pro-feature-lite .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
                vertical-align: middle;
                margin-right: 4px;
            }

            .sapwc-pro-cta {
                background: linear-gradient(135deg, #1e2f23 0%, #2a3f30 100%);
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                margin-top: 30px;
                border: 2px solid #93f1c9;
                position: relative;
                overflow: hidden;
            }
            .sapwc-pro-cta::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -50%;
                width: 100%;
                height: 100%;
                background: radial-gradient(circle, rgba(147, 241, 201, 0.1) 0%, transparent 70%);
                pointer-events: none;
            }
            .sapwc-pro-cta h3 {
                margin: 0 0 12px;
                color: #93f1c9;
                font-size: 24px;
                font-weight: 600;
            }
            .sapwc-pro-cta p {
                margin: 0 0 24px;
                color: #fff;
                font-size: 16px;
                opacity: 0.9;
            }
            .sapwc-pro-cta .sapwc-cta-buttons {
                display: flex;
                gap: 12px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .sapwc-pro-cta .button-cta-primary {
                background: #41999f;
                color: #fff;
                border: none;
                padding: 12px 28px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(65, 153, 159, 0.3);
            }
            .sapwc-pro-cta .button-cta-primary:hover {
                background: #357a7f;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(65, 153, 159, 0.4);
                color: #fff;
            }
            .sapwc-pro-cta .button-cta-secondary {
                background: transparent;
                color: #93f1c9;
                border: 2px solid #93f1c9;
                padding: 10px 26px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            .sapwc-pro-cta .button-cta-secondary:hover {
                background: #93f1c9;
                color: #1e2f23;
            }
        </style>
        <?php
    }
}

// Initialize.
new SAPWC_Lite_Pro_Features();
