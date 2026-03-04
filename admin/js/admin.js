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
            $result.removeClass('success error').html('<span class="sapwc-lite-loading"></span> Testing...');

            $.ajax({
                url: sapwcLite.ajax_url,
                type: 'POST',
                data: {
                    action: 'sapwc_lite_test_connection',
                    nonce: sapwcLite.nonce,
                    url: $('#sapwc_lite_sap_url').val(),
                    user: $('#sapwc_lite_sap_user').val(),
                    pass: $('#sapwc_lite_sap_pass').val(),
                    db: $('#sapwc_lite_sap_db').val(),
                    ssl: $('#sapwc_lite_sap_ssl').is(':checked') ? '1' : '0'
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    
                    if (response.success) {
                        var version = response.data.version ? 
                            ' (SL v' + response.data.version.ServiceLayerVersion + ')' : '';
                        $result.addClass('success').text('Connected!' + version);
                    } else {
                        $result.addClass('error').text(response.data.message || 'Connection failed');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    $result.addClass('error').text('Request failed');
                }
            });
        });

        // Sync Now
        $('#sapwc-lite-sync-now').on('click', function() {
            var $btn = $(this);
            var $result = $('#sapwc-lite-sync-result');
            
            $btn.prop('disabled', true);
            $result.removeClass('success error').html('<span class="sapwc-lite-loading"></span> Syncing...');

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
                            response.data.updated + ' products updated'
                        );
                        
                        // Reload page after 2 seconds to show updated last_sync
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $result.addClass('error').text(response.data.message || 'Sync failed');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    $result.addClass('error').text('Request failed');
                }
            });
        });

    });

})(jQuery);
