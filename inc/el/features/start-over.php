<?php
/**
 * Start Over Feature
 * 
 * Provides [el_start_over_button] shortcode and AJAX handler
 * Clears cart, session, and resets wizard to Tab 1
 */

if (!defined('ABSPATH')) exit;

// ============================================
// AJAX HANDLER
// ============================================

/**
 * AJAX: Start Over
 * Clears cart, session data, and resets wizard
 */
function el_handle_start_over() {
    // Verify nonce
    if (!check_ajax_referer('el_start_over', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Clear WooCommerce cart
    if (function_exists('WC') && WC()->cart) {
        WC()->cart->empty_cart();
    }
    
    // Clear session data using core session function
    if (function_exists('el_clear_session_data')) {
        el_clear_session_data();
    }
    
    wp_send_json_success([
        'message' => 'Wizard reset successfully',
        'redirect_to_tab' => 1
    ]);
}

add_action('wp_ajax_el_start_over', 'el_handle_start_over');
add_action('wp_ajax_nopriv_el_start_over', 'el_handle_start_over');

// ============================================
// SHORTCODE
// ============================================

/**
 * Shortcode: Start Over Button
 * 
 * Usage: [el_start_over_button]
 * Attributes:
 *   - text: Button text (default: "Start Over")
 *   - style: default, danger, outline
 *   - position: inline, fixed-top, fixed-bottom
 *   - confirm: yes/no - show confirmation dialog
 */
function el_render_start_over_button($atts) {
    // Parse attributes
    $atts = shortcode_atts([
        'text' => 'Start Over',
        'style' => 'danger',
        'position' => 'inline',
        'confirm' => 'yes'
    ], $atts);
    
    $button_text = esc_html($atts['text']);
    $show_confirm = ($atts['confirm'] === 'yes');
    
    // Style variations
    $button_styles = [
        'default' => 'background: #6b7280; color: white; border: 2px solid #6b7280;',
        'danger' => 'background: #dc2626; color: white; border: 2px solid #dc2626;',
        'outline' => 'background: transparent; color: #dc2626; border: 2px solid #dc2626;'
    ];
    
    $selected_style = $button_styles[$atts['style']] ?? $button_styles['danger'];
    
    // Position variations
    $wrapper_styles = [
        'inline' => 'display: inline-block;',
        'fixed-top' => 'position: fixed; top: 20px; right: 20px; z-index: 9999;',
        'fixed-bottom' => 'position: fixed; bottom: 20px; right: 20px; z-index: 9999;'
    ];
    
    $selected_position = $wrapper_styles[$atts['position']] ?? $wrapper_styles['inline'];
    
    ob_start();
    ?>
    
    <div class="el-start-over-wrapper" style="<?php echo $selected_position; ?>">
        <button 
            id="el-start-over-btn" 
            class="el-start-over-button"
            data-confirm="<?php echo $show_confirm ? '1' : '0'; ?>"
            style="<?php echo $selected_style; ?> padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
            🔄 <?php echo $button_text; ?>
        </button>
    </div>
    
    <style>
    .el-start-over-button:hover {
        opacity: 0.8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .el-start-over-button:active {
        transform: translateY(0);
    }
    
    .el-start-over-button.loading {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    .el-confirm-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99999;
        align-items: center;
        justify-content: center;
    }
    
    .el-confirm-modal.active {
        display: flex;
    }
    
    .el-confirm-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 450px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .el-confirm-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px 0;
    }
    
    .el-confirm-message {
        font-size: 15px;
        color: #6b7280;
        margin: 0 0 24px 0;
        line-height: 1.6;
    }
    
    .el-confirm-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    
    .el-confirm-btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .el-confirm-cancel {
        background: #f3f4f6;
        color: #374151;
    }
    
    .el-confirm-cancel:hover {
        background: #e5e7eb;
    }
    
    .el-confirm-proceed {
        background: #dc2626;
        color: white;
    }
    
    .el-confirm-proceed:hover {
        background: #b91c1c;
    }
    </style>
    
    <!-- Confirmation Modal -->
    <div id="el-confirm-modal" class="el-confirm-modal">
        <div class="el-confirm-content">
            <h3 class="el-confirm-title">⚠️ Start Over?</h3>
            <p class="el-confirm-message">
                This will clear your current progress, remove all selected services, and reset the engagement letter wizard. This action cannot be undone.
            </p>
            <div class="el-confirm-actions">
                <button class="el-confirm-btn el-confirm-cancel" id="el-confirm-cancel">
                    Cancel
                </button>
                <button class="el-confirm-btn el-confirm-proceed" id="el-confirm-proceed">
                    Yes, Start Over
                </button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var $startOverBtn = $('#el-start-over-btn');
        var $confirmModal = $('#el-confirm-modal');
        var showConfirm = $startOverBtn.data('confirm') == 1;
        
        $startOverBtn.on('click', function(e) {
            e.preventDefault();
            
            if ($startOverBtn.hasClass('loading')) return;
            
            if (showConfirm) {
                $confirmModal.addClass('active');
            } else {
                executeStartOver();
            }
        });
        
        $('#el-confirm-cancel').on('click', function() {
            $confirmModal.removeClass('active');
        });
        
        $('#el-confirm-proceed').on('click', function() {
            $confirmModal.removeClass('active');
            executeStartOver();
        });
        
        $confirmModal.on('click', function(e) {
            if ($(e.target).is('#el-confirm-modal')) {
                $confirmModal.removeClass('active');
            }
        });
        
        function executeStartOver() {
            $startOverBtn.addClass('loading').text('Resetting...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_start_over',
                    nonce: '<?php echo wp_create_nonce('el_start_over'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $startOverBtn.text('✓ Reset Complete');
                        
                        setTimeout(function() {
                            // Switch to Tab 1
                            var $tab1 = $('#brxe-kjwfkc');
                            if ($tab1.length) {
                                $tab1.click();
                            } else {
                                window.location.reload();
                            }
                            
                            setTimeout(function() {
                                $startOverBtn.removeClass('loading').text('🔄 <?php echo $button_text; ?>');
                            }, 500);
                        }, 800);
                    } else {
                        $startOverBtn.removeClass('loading').text('Error');
                        setTimeout(function() {
                            $startOverBtn.text('🔄 <?php echo $button_text; ?>');
                        }, 3000);
                    }
                },
                error: function() {
                    $startOverBtn.removeClass('loading').text('Error');
                    setTimeout(function() {
                        $startOverBtn.text('🔄 <?php echo $button_text; ?>');
                    }, 3000);
                }
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('el_start_over_button', 'el_render_start_over_button');
add_shortcode('el_start_over', 'el_render_start_over_button'); // Alias for convenience