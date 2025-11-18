<script>
jQuery(document).ready(function($) {
    console.log('Debug: Page loaded');
    console.log('Debug: Buttons found:', $('.el-select-template-btn').length);
    console.log('Debug: el_ajax object:', typeof el_ajax !== 'undefined' ? 'EXISTS' : 'MISSING');
    
    // Test direct binding
    $(document).on('click', '.el-select-template-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('================');
        console.log('BUTTON CLICKED!');
        console.log('Product ID:', $(this).data('product-id'));
        console.log('Product Name:', $(this).data('product-name'));
        console.log('el_ajax.ajax_url:', el_ajax.ajax_url);
        console.log('el_ajax.nonce:', el_ajax.nonce);
        console.log('================');
        
        const $button = $(this);
        const productId = $button.data('product-id');
        
        // Change button text to show it's working
        $button.css('background', 'red').text('CLICKING WORKS - Testing AJAX...');
        
        // Test AJAX
        $.ajax({
            url: el_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'el_setup_engagement_cart',
                product_id: productId,
                nonce: el_ajax.nonce
            },
            success: function(response) {
                console.log('SUCCESS:', response);
                alert('SUCCESS! Check console for details');
                
                // Refresh WooCommerce mini cart
                $(document.body).trigger('wc_fragment_refresh');
                
                // Try to switch tab
                if (typeof switchToTab === 'function') {
                    switchToTab(3);
                } else {
                    console.error('switchToTab function not found');
                    // Manual tab switch
                    $('.el-tab-content').hide();
                    $('#el-tab-3').show();
                }
            },
            error: function(xhr, status, error) {
                console.log('ERROR:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                alert('ERROR: ' + error + '\nCheck console');
            },
            complete: function() {
                $button.css('background', 'green').text('AJAX Complete');
            }
        });
    });
    
    // Also test if button exists after page load
    setTimeout(function() {
        console.log('After 2 seconds, buttons found:', $('.el-select-template-btn').length);
    }, 2000);
});
</script>