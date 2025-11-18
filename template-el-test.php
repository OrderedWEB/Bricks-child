<?php
/**
 * Template Name: EL Test Page
 */

get_header();
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    
    <div style="padding: 20px; background: #f0f9ff; border: 2px solid #0284c7; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 10px 0;">🧪 Engagement Letter System Test</h2>
        <p style="margin: 0 0 20px 0;">Test the PDF generation system before using the full wizard.</p>
        
        <button id="test-ajax-setup" style="background: #059669; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 10px;">
            1. Test AJAX Setup
        </button>
        
        <button id="generate-test-pdf" style="background: #0284c7; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            2. Generate Test PDF
        </button>
        
        <div id="test-results" style="margin-top: 20px; min-height: 100px;"></div>
    </div>
    
    <div style="padding: 20px; background: #fffbeb; border: 2px solid #f59e0b; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0;">📋 Diagnostic Info</h3>
        <div style="font-family: monospace; font-size: 12px; background: white; padding: 15px; border-radius: 4px;">
            <?php
            if (!session_id()) {
                session_start();
            }
            
            echo '<strong>WordPress:</strong> ' . get_bloginfo('version') . '<br>';
            echo '<strong>Theme:</strong> ' . wp_get_theme()->get('Name') . '<br>';
            echo '<strong>WooCommerce:</strong> ' . (class_exists('WooCommerce') ? '✓ Active' : '✗ Inactive') . '<br>';
            echo '<strong>ACF:</strong> ' . (function_exists('get_field') ? '✓ Active' : '✗ Inactive') . '<br>';
            echo '<strong>Gravity Forms:</strong> ' . (class_exists('GFAPI') ? '✓ Active' : '✗ Inactive') . '<br><br>';
            
            echo '<strong>Session Data:</strong><br>';
            echo 'Client Name: ' . ($_SESSION['el_client_name'] ?? 'Not set') . '<br>';
            echo 'Client Email: ' . ($_SESSION['el_client_email'] ?? 'Not set') . '<br>';
            echo 'Client ID: ' . ($_SESSION['el_current_client_id'] ?? 'Not set') . '<br><br>';
            
            if (class_exists('WooCommerce') && WC()->cart) {
                echo '<strong>Cart:</strong><br>';
                echo 'Items: ' . WC()->cart->get_cart_contents_count() . '<br>';
                echo 'Total: ' . WC()->cart->get_total() . '<br>';
            }
            ?>
        </div>
    </div>
    
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var $results = $('#test-results');
    
    // Test AJAX Setup
    $('#test-ajax-setup').on('click', function() {
        $results.html('<div style="background: #e0f2fe; padding: 15px; border-radius: 4px;">Testing AJAX setup...</div>');
        
        var info = '<div style="background: white; padding: 15px; border-radius: 4px; border: 2px solid #0284c7;">';
        info += '<strong>AJAX Configuration:</strong><br><br>';
        info += 'jQuery loaded: ' + (typeof jQuery !== 'undefined' ? '✓ YES' : '✗ NO') + '<br>';
        info += 'el_ajax defined: ' + (typeof el_ajax !== 'undefined' ? '✓ YES' : '✗ NO') + '<br>';
        
        if (typeof el_ajax !== 'undefined') {
            info += 'AJAX URL: ' + el_ajax.ajax_url + '<br>';
            info += 'Nonce present: ' + (el_ajax.nonce ? '✓ YES' : '✗ NO') + '<br>';
        } else {
            info += '<br><span style="color: #dc2626;">⚠️ el_ajax not defined - localization issue</span><br>';
        }
        
        info += '</div>';
        $results.html(info);
    });
    
    // Generate Test PDF
    $('#generate-test-pdf').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.text();
        
        $btn.prop('disabled', true).text('Generating...');
        $results.html('<div style="background: #e0f2fe; padding: 15px; border-radius: 4px;">Generating test PDF...</div>');
        
        // Use multiple fallback methods
        var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var nonce = '<?php echo wp_create_nonce('el_nonce'); ?>';
        
        console.log('Making AJAX request to:', ajaxUrl);
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'el_generate_test_pdf',
                nonce: nonce
            },
            success: function(response) {
                console.log('Success:', response);
                $btn.prop('disabled', false).text(originalText);
                
                if (response.success && response.data && response.data.pdf_url) {
                    $results.html(
                        '<div style="background: #d1fae5; padding: 20px; border-radius: 4px; border: 2px solid #10b981;">' +
                        '<div style="font-size: 18px; margin-bottom: 15px;">✓ <strong>Test PDF Generated Successfully!</strong></div>' +
                        '<div style="margin-bottom: 15px;">Reference: <code>' + response.data.reference + '</code></div>' +
                        '<a href="' + response.data.pdf_url + '" target="_blank" class="button" style="display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;">📄 Open Test PDF</a>' +
                        '</div>'
                    );
                } else {
                    $results.html(
                        '<div style="background: #fee2e2; padding: 20px; border-radius: 4px; border: 2px solid #ef4444;">' +
                        '<strong style="color: #dc2626;">Error:</strong><br>' +
                        (response.data && response.data.message ? response.data.message : 'Unknown error') +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                $btn.prop('disabled', false).text(originalText);
                
                $results.html(
                    '<div style="background: #fee2e2; padding: 20px; border-radius: 4px; border: 2px solid #ef4444;">' +
                    '<strong style="color: #dc2626;">Connection Error</strong><br><br>' +
                    '<strong>Status:</strong> ' + status + '<br>' +
                    '<strong>Error:</strong> ' + error + '<br><br>' +
                    '<em>Check browser console (F12) for more details</em>' +
                    '</div>'
                );
            }
        });
    });
});
</script>

<?php
get_footer();