# Copilot Instructions — SAP Woo Suite Lite

This is the FREE (WP.org) WordPress plugin. Subset of PRO — stock + price sync only.

## Quick Reference

- **Main file**: `sap-woo-suite-lite.php`
- **Text domain**: `sap-woo-suite-lite`
- **Version constant**: `SAPWC_LITE_VERSION`
- **License**: GPL-2.0-or-later
- **No build script** — managed by PRO's build.ps1

## WP.org Compliance (Critical)

This plugin MUST pass the WordPress Plugin Check at all times:

1. **Escaping**: Every `echo` must use `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses_post()`. Use `phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` ONLY when outputting hardcoded HTML that wraps already-escaped values.
2. **No forbidden functions**: `json_encode` -> `wp_json_encode`, no `file_get_contents`, `curl_init`, `eval`, `exec`, etc.
3. **Nonces**: All forms must use `wp_nonce_field()` / `check_admin_referer()`
4. **Capabilities**: All admin pages check `manage_woocommerce`
5. **Direct DB**: Use `phpcs:ignore` comments on `$wpdb` calls
6. **Text domain**: Every string uses `'sap-woo-suite-lite'`
7. **Version sync**: Header Version, `SAPWC_LITE_VERSION` constant, and readme.txt Stable tag MUST match
8. **LICENSE**: Must be GPLv2 text (not GPLv3)

## File Structure

```
sap-woo-suite-lite.php       — Bootstrap, hooks, constants
includes/
  class-api-client.php       — SAP Service Layer client (wp_remote_*)
  class-stock-sync.php       — Stock + price sync engine
  class-logger.php           — DB logger
admin/
  class-dashboard-page.php   — Dashboard with Chart.js charts
  class-settings-page.php    — Connection + sync settings
  class-pro-features.php     — PRO upsell page (auto-generated)
  js/dashboard.js            — Chart rendering
  js/admin.js                — Settings page JS
  js/chart.min.js            — Bundled Chart.js (no CDN)
  css/admin.css              — Design system
languages/                   — .pot, .po, .mo, .l10n.php
```

## Relationship with PRO

- Lite auto-deactivates when PRO is active
- PRO's build.ps1 auto-syncs the PRO features list into Lite's `class-pro-features.php`
- Settings keys are compatible: upgrading to PRO preserves all config
- Log table name is shared: `{prefix}sapwc_logs`
