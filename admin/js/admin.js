/**
 * SAP Woo Suite Lite - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Test Connection
        $('#sapwc-lite-test-connection').on('click', function() {
            var $btn = $(this);
            var $result = $('#sapwc-lite-test-result');
            
            $btn.prop('disabled', true);
            $result.removeClass('success error').html('<span class="sapwc-lite-loading"></span> ' + sapwcLite.i18n.testing);

            $.ajax({
                url: sapwcLite.ajax_url,
                type: 'POST',
                data: {
                    action: 'sapwc_lite_test_connection',
                    nonce: sapwcLite.nonce,
                    url: $('#sap_url').val(),
                    user: $('#sap_user').val(),
                    pass: $('#sap_pass').val(),
                    db: $('#sap_db').val(),
                    ssl: $('#sap_ssl').is(':checked') ? '1' : '0'
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    
                    if (response.success) {
                        var version = response.data.version ? 
                            ' (SL v' + response.data.version.ServiceLayerVersion + ')' : '';
                        $result.addClass('success').text(sapwcLite.i18n.connected + version);
                    } else {
                        $result.addClass('error').text(response.data.message || sapwcLite.i18n.connection_failed);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    $result.addClass('error').text(sapwcLite.i18n.request_failed);
                }
            });
        });

        // Sync Now
        $('#sapwc-lite-sync-now').on('click', function() {
            var $btn = $(this);
            var $result = $('#sapwc-lite-sync-result');
            
            $btn.prop('disabled', true);
            $result.removeClass('success error').html('<span class="sapwc-lite-loading"></span> ' + sapwcLite.i18n.syncing);

            $.ajax({
                url: sapwcLite.ajax_url,
                type: 'POST',
                data: {
                    action: 'sapwc_lite_sync_now',
                    nonce: sapwcLite.nonce
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    
                    if (response.success) {
                        $result.addClass('success').text(
                            response.data.updated + ' ' + sapwcLite.i18n.products_updated
                        );

                        // Show PRO tip after successful sync
                        if (response.data.pro_tip) {
                            var $tip = $('<div class="sapwc-lite-pro-tip" style="margin-top:12px;padding:10px 14px;background:linear-gradient(135deg,#f0fdfa,#e0f7fa);border-left:3px solid #41999f;border-radius:4px;font-size:13px;color:#1e293b;">' +
                                '<strong style="color:#41999f;">' + sapwcLite.i18n.pro_tip + '</strong> ' +
                                $('<span>').text(response.data.pro_tip).html() +
                                ' <a href="https://replanta.net/conector-sap-woocommerce/" target="_blank" style="color:#41999f;font-weight:600;text-decoration:none;">' + sapwcLite.i18n.go_pro + ' &rarr;</a>' +
                                '</div>');
                            $result.after($tip);
                        }
                        
                        // Reload page after 4 seconds to show updated last_sync
                        setTimeout(function() {
                            location.reload();
                        }, 4000);
                    } else {
                        $result.addClass('error').text(response.data.message || sapwcLite.i18n.sync_failed);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    $result.addClass('error').text(sapwcLite.i18n.request_failed);
                }
            });
        });

    });

})(jQuery);
