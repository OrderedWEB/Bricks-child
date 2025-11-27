<?php
/**
 * Engagement Letter System - Tab 3: Cart Editor (Glass Morphism)
 * 
 * Modern cart display and editing interface:
 * - Glass morphism design
 * - Real-time cart updates
 * - Item quantity management
 * - Remove items
 * - Price calculations
 * - Grouped products display
 * 
 * LOAD ORDER: Tab module (after all core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php, woocommerce.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// CART RENDERING
// ============================================

/**
 * Renders modern glass morphism cart
 * 
 * @return string HTML cart
 */
function el_render_glass_cart() {
    if (!el_ensure_cart()) {
        return '<p>Cart not available.</p>';
    }
    
    $cart = WC()->cart;
    
    if ($cart->is_empty()) {
        return el_render_empty_cart();
    }
    
    $output = '<div class="el-cart-glass-wrapper" style="
        background: linear-gradient(135deg, rgba(168, 188, 206, 0.1) 0%, rgba(143, 173, 211, 0.1) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        border: 1px solid rgba(168, 188, 206, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    ">';
    
    // Header
    $output .= '<div class="el-cart-header" style="margin-bottom: 25px;">';
    $output .= '<h2 style="margin: 0; font-size: 24px; color: ' . EL_COLOR_NAVY . ';">Your Selected Services</h2>';
    $output .= '<p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">' . $cart->get_cart_contents_count() . ' ' . _n('item', 'items', $cart->get_cart_contents_count()) . ' in cart</p>';
    $output .= '</div>';
    
    // Cart items
    $output .= '<div class="el-cart-items">';
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $output .= el_render_cart_item($cart_item_key, $cart_item);
    }
    
    $output .= '</div>';
    
    // Cart totals
    $output .= el_render_cart_totals();
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Renders individual cart item with glass effect
 * 
 * @param string $cart_item_key Cart item key
 * @param array  $cart_item     Cart item data
 * @return string HTML cart item
 */
function el_render_cart_item($cart_item_key, $cart_item) {
    $product = $cart_item['data'];
    $product_id = $cart_item['product_id'];
    
    // Check if this is a grouped child
    $is_grouped_child = !empty($cart_item[EL_CART_META_IS_CHILD]);
    $parent_name = $cart_item[EL_CART_META_PARENT_NAME] ?? null;
    
    $output = '<div class="el-cart-item" data-cart-key="' . esc_attr($cart_item_key) . '" style="
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid rgba(168, 188, 206, 0.2);
        transition: all 0.3s;
    ">';
    
    $output .= '<div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">';
    
    // Left side - Product info
    $output .= '<div style="flex: 1;">';
    
    // Parent name badge (if grouped child)
    if ($is_grouped_child && $parent_name) {
        $output .= '<div style="
            display: inline-block;
            background: rgba(' . hexdec(substr(EL_COLOR_PRIMARY, 1, 2)) . ', ' . hexdec(substr(EL_COLOR_PRIMARY, 3, 2)) . ', ' . hexdec(substr(EL_COLOR_PRIMARY, 5, 2)) . ', 0.1);
            color: ' . EL_COLOR_PRIMARY . ';
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        ">📦 ' . esc_html($parent_name) . '</div>';
    }
    
    // Product name
    $output .= '<h3 style="margin: 0 0 8px 0; font-size: 18px; color: ' . EL_COLOR_NAVY . ';">';
    if ($is_grouped_child) {
        $output .= '↳ ';
    }
    $output .= esc_html($product->get_name());
    $output .= '</h3>';
    
    // Product description (if available)
    if ($product->get_short_description()) {
        $output .= '<p style="margin: 0; font-size: 13px; color: #6b7280;">' . wp_trim_words($product->get_short_description(), 15) . '</p>';
    }
    
    // Practice area
    $practice_area = get_field(EL_ACF_PRACTICE_AREA, $product_id);
    if ($practice_area) {
        $output .= '<div style="margin-top: 8px;">';
        $output .= '<span style="
            font-size: 11px;
            color: ' . EL_COLOR_PRIMARY . ';
            font-weight: 600;
        ">🏛 ' . esc_html($practice_area) . '</span>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Right side - Price and controls
    $output .= '<div style="text-align: right; min-width: 150px;">';
    
    // Price
    $output .= '<div class="el-cart-item-price" style="margin-bottom: 15px;">';
    $output .= '<span style="font-size: 22px; font-weight: 700; color: ' . EL_COLOR_NAVY . ';">' . wc_price($product->get_price()) . '</span>';
    $output .= '</div>';
    
    // Quantity (always 1 for engagement letters)
    $output .= '<div style="
        background: rgba(255, 255, 255, 0.5);
        padding: 8px 16px;
        border-radius: 8px;
        margin-bottom: 10px;
        border: 1px solid rgba(168, 188, 206, 0.3);
    ">';
    $output .= '<span style="font-size: 13px; color: #6b7280;">Quantity: <strong>1</strong></span>';
    $output .= '</div>';
    
    // Remove button
    $output .= '<button type="button" class="el-remove-cart-item" data-cart-key="' . esc_attr($cart_item_key) . '" style="
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
    ">Remove</button>';
    
    $output .= '</div>';
    
    $output .= '</div>'; // Close flex container
    $output .= '</div>'; // Close cart item
    
    return $output;
}

/**
 * Renders cart totals section
 * 
 * @return string HTML totals
 */
function el_render_cart_totals() {
    if (!el_ensure_cart()) {
        return '';
    }
    
    $cart = WC()->cart;
    
    $output = '<div class="el-cart-totals" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        border-radius: 16px;
        padding: 25px;
        margin-top: 25px;
        color: white;
    ">';
    
    $output .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';
    $output .= '<span style="font-size: 16px; opacity: 0.9;">Subtotal</span>';
    $output .= '<span style="font-size: 20px; font-weight: 600;">' . wc_price($cart->get_subtotal()) . '</span>';
    $output .= '</div>';
    
    // Tax (if applicable)
    if ($cart->get_total_tax() > 0) {
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';
        $output .= '<span style="font-size: 14px; opacity: 0.8;">Tax</span>';
        $output .= '<span style="font-size: 16px;">' . wc_price($cart->get_total_tax()) . '</span>';
        $output .= '</div>';
    }
    
    // Total
    $output .= '<div style="
        border-top: 2px solid rgba(255, 255, 255, 0.3);
        padding-top: 15px;
        margin-top: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">';
    $output .= '<span style="font-size: 18px; font-weight: 700;">TOTAL</span>';
    $output .= '<span style="font-size: 28px; font-weight: 700;">' . wc_price($cart->get_total('')) . '</span>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Renders empty cart state
 * 
 * @return string HTML empty state
 */
function el_render_empty_cart() {
    $output = '<div class="el-cart-empty" style="
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        border: 1px solid rgba(168, 188, 206, 0.2);
    ">';
    
    $output .= '<div style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;">🛒</div>';
    $output .= '<h3 style="margin: 0 0 10px 0; font-size: 22px; color: ' . EL_COLOR_NAVY . ';">Your cart is empty</h3>';
    $output .= '<p style="margin: 0 0 25px 0; color: #6b7280;">Add templates from the previous step to continue.</p>';
    
    $output .= '<button type="button" class="el-back-to-templates" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    ">← Back to Templates</button>';
    
    $output .= '</div>';
    
    return $output;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX handler: Refresh cart display
 */
function el_ajax_refresh_cart_editor() {
    check_ajax_referer(EL_REFRESH_NONCE, 'nonce');
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    $html = el_render_glass_cart();
    
    wp_send_json_success([
        'html' => $html,
        'cart_count' => el_get_cart_count(),
        'cart_total' => WC()->cart->get_total(''),
    ]);
}
add_action('wp_ajax_' . EL_AJAX_REFRESH_CART, 'el_ajax_refresh_cart_editor');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: Glass morphism cart editor
 * 
 * Usage: [el_cart_editor_glass]
 */
function el_cart_editor_glass_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your cart.</p>';
    }
    
    $output = '<div id="elCartEditorContainer">';
    $output .= el_render_glass_cart();
    $output .= '</div>';
    
    // Add back button
    $output .= '<div style="margin-top: 30px; display: flex; justify-content: space-between;">';
    $output .= '<button type="button" class="el-back-to-templates" style="
        background: white;
        color: ' . EL_COLOR_PRIMARY . ';
        border: 2px solid ' . EL_COLOR_PRIMARY . ';
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    ">← Add More Templates</button>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_cart_editor_glass', 'el_cart_editor_glass_shortcode');

// Legacy shortcode alias
function el_cart_editor_enhanced_shortcode() {
    return el_cart_editor_glass_shortcode();
}
add_shortcode('el_cart_editor_enhanced', 'el_cart_editor_enhanced_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

/**
 * Enqueues Tab 3 JavaScript
 */
function el_enqueue_tab3_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Remove cart item
            $(document).on('click', '.el-remove-cart-item', function() {
                var cartKey = $(this).data('cart-key');
                var item = $(this).closest('.el-cart-item');
                
                item.css('opacity', '0.5');
                $(this).prop('disabled', true).text('Removing...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_REMOVE_ITEM . "',
                        nonce: elAjax.nonce,
                        cart_item_key: cartKey
                    },
                    success: function(response) {
                        if (response.success) {
                            // Refresh entire cart display
                            refreshCartDisplay();
                        } else {
                            alert('Error: ' + response.data.message);
                            item.css('opacity', '1');
                        }
                    }
                });
            });
            
            // Back to templates button
            $(document).on('click', '.el-back-to-templates', function() {
                $('" . el_get_tab_selector(2) . "').click();
            });
            
            // Refresh cart display
            function refreshCartDisplay() {
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_REFRESH_CART . "',
                        nonce: elAjax.refreshNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#elCartEditorContainer').html(response.data.html);
                        }
                    }
                });
            }
            
            // Auto-refresh cart when navigating to tab
            $('" . el_get_tab_selector(3) . "').on('click', function() {
                setTimeout(refreshCartDisplay, 300);
            });
            
            // Add hover effects
            $(document).on('mouseenter', '.el-cart-item', function() {
                $(this).css({
                    'transform': 'translateY(-2px)',
                    'box-shadow': '0 12px 24px rgba(0, 0, 0, 0.15)'
                });
            }).on('mouseleave', '.el-cart-item', function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': 'none'
                });
            });
            
            // Button hover effects
            $(document).on('mouseenter', '.el-remove-cart-item', function() {
                $(this).css({
                    'background': 'linear-gradient(135deg, #b91c1c 0%, #991b1b 100%)',
                    'transform': 'scale(1.05)'
                });
            }).on('mouseleave', '.el-remove-cart-item', function() {
                $(this).css({
                    'background': 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)',
                    'transform': 'scale(1)'
                });
            });
        });
    ");
    
    // Add custom CSS
    wp_add_inline_style('wp-block-library', "
        .el-cart-glass-wrapper {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .el-cart-item {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab3_script');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Tab 3 (Cart Editor - Glass) module loaded successfully', 'info');
}