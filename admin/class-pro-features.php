<?php
/**
 * PRO Features Page - Shows blocked features with upgrade prompts
 *
 * @package SAPWC_Lite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SAPWC_Lite_Pro_Features
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_pro_menu'], 20);
    }

    public function add_pro_menu()
    {
        add_submenu_page(
            'sapwc-lite-settings',
            'PRO Features',
            '<span style="color:#41999f;">PRO Features</span>',
            'manage_woocommerce',
            'sapwc-lite-pro',
            [$this, 'render_page']
        );
    }

    public function render_page()
    {
        $features = [
            [
                'icon'  => 'cart',
                'title' => 'Full Product Sync',
                'desc'  => 'Import products from SAP to WooCommerce with full attribute mapping, images, categories, and variations.',
                'lite'  => 'Stock & prices only'
            ],
            [
                'icon'  => 'clipboard',
                'title' => 'Order Sync to SAP',
                'desc'  => 'Automatically send WooCommerce orders to SAP Business One as Sales Orders with full line item details.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'groups',
                'title' => 'Customer Sync',
                'desc'  => 'Create and update Business Partners in SAP from WooCommerce customers with address synchronization.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'category',
                'title' => 'Category Import',
                'desc'  => 'Import SAP item groups as WooCommerce categories with hierarchy support.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'admin-settings',
                'title' => 'Advanced Field Mapping',
                'desc'  => 'Map any SAP field to WooCommerce product attributes, including UDFs and custom fields.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'rest-api',
                'title' => 'REST API Endpoints',
                'desc'  => 'Expose API endpoints for external integrations, webhooks, and automation workflows.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'store',
                'title' => 'Multi-channel Support',
                'desc'  => 'Connect TikTok Shop, Amazon, Miravia, and other marketplaces via SAP Woo Suite addons.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'update',
                'title' => 'Auto-retry Failed Orders',
                'desc'  => 'Intelligent retry system with exponential backoff for failed SAP orders.',
                'lite'  => 'Not available'
            ],
            [
                'icon'  => 'database',
                'title' => 'Multiple Warehouses per Product',
                'desc'  => 'Assign different warehouses to different products with warehouse-specific tariffs.',
                'lite'  => 'Global warehouse only'
            ],
            [
                'icon'  => 'analytics',
                'title' => 'Detailed Logging & Analytics',
                'desc'  => 'Comprehensive logs with filtering, export, and sync statistics dashboard.',
                'lite'  => 'Basic logs'
            ],
        ];
        ?>
        <div class="wrap sapwc-lite-wrap">
            <h1>PRO Features</h1>

            <div class="sapwc-pro-hero">
                <h2>Unlock the Full Power of SAP Woo Suite</h2>
                <p>You're using the <strong>Lite</strong> version with stock and price sync. Upgrade to PRO for complete SAP Business One integration.</p>
                <a href="https://replanta.dev/sap-woo-suite" target="_blank" class="button button-primary button-hero">
                    Get SAP Woo Suite PRO
                </a>
            </div>

            <div class="sapwc-pro-features-grid">
                <?php foreach ($features as $feature): ?>
                    <div class="sapwc-pro-feature-card">
                        <div class="sapwc-pro-feature-icon">
                            <span class="dashicons dashicons-<?php echo esc_attr($feature['icon']); ?>"></span>
                        </div>
                        <h3><?php echo esc_html($feature['title']); ?></h3>
                        <p><?php echo esc_html($feature['desc']); ?></p>
                        <div class="sapwc-pro-feature-lite">
                            <span class="dashicons dashicons-warning"></span>
                            Lite: <?php echo esc_html($feature['lite']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sapwc-pro-cta">
                <h3>Ready to upgrade?</h3>
                <p>Get SAP Woo Suite PRO and connect your entire business workflow.</p>
                <a href="https://replanta.dev/sap-woo-suite" target="_blank" class="button button-primary">
                    View Pricing
                </a>
                <a href="https://replantadev.github.io/sapwoo/" target="_blank" class="button">
                    Read Documentation
                </a>
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
                background: #f5f5f5;
                border-radius: 8px;
                padding: 30px;
                text-align: center;
                margin-top: 20px;
            }
            .sapwc-pro-cta h3 {
                margin: 0 0 8px;
            }
            .sapwc-pro-cta p {
                margin: 0 0 20px;
                color: #666;
            }
            .sapwc-pro-cta .button {
                margin: 0 5px;
            }
        </style>
        <?php
    }
}

// Initialize
new SAPWC_Lite_Pro_Features();
