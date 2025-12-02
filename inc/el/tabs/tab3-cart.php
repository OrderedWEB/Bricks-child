<?php
/**
 * Engagement Letter System - Tab 3: Cart Editor (Glass Morphism)
 * 
 * Pricing: Only charges parent's "Engagement Fee Due Today"
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// CART RENDERING
// ============================================

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
    $output .= '<h2 style="margin: 0; font-size: 24px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">Your Engagement Letter</h2>';
    
    // Count optional services (children only)
    $optional_count = 0;
    foreach ($cart->get_cart() as $item) {
        if (!empty($item['el_grouped_parent'])) {
            $optional_count++;
        }
    }
    
    $output .= '<p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px; font-weight: 400;">' . $optional_count . ' optional ' . _n('service', 'services', $optional_count) . '</p>';
    $output .= '</div>';
    
    // Cart items
    $output .= '<div class="el-cart-items">';
    
    $displayed_parents = [];
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $parent_id = $cart_item['el_grouped_parent'] ?? null;
        $is_child = !empty($parent_id);
        
        if ($is_child && $parent_id && !in_array($parent_id, $displayed_parents)) {
            $parent = el_get_grouped_parent();
            if ($parent) {
                $displayed_parents[] = $parent_id;
                $output .= el_render_grouped_parent_header($parent);
            }
        }
        
        $output .= el_render_cart_item($cart_item_key, $cart_item);
    }
    
    $output .= '</div>';
    
    // Cart totals
    $output .= el_render_cart_totals();
    
    $output .= '</div>';
    
    return $output;
}

function el_render_grouped_parent_header($parent) {
    $parent_product = wc_get_product($parent['id']);
    
    // Get "Engagement Fee Due Today" from ACF
    $engagement_fee = get_field('engagement_fee_due_today', $parent['id']);
    $engagement_fee = $engagement_fee ? floatval($engagement_fee) : 0;
    
    $output = '<div class="el-grouped-parent-header" data-parent-id="' . esc_attr($parent['id']) . '" style="
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 0;
        border: 2px solid #10b981;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    ">';
    
    $output .= '<div style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">';
    
    // Left side
    $output .= '<div style="display: flex; align-items: center; gap: 15px; flex: 1;">';
    
    $output .= '<div style="
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    ">📋</div>';
    
    $output .= '<div>';
    $output .= '<h3 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 600; color: #047857;">';
    $output .= esc_html($parent['name']);
    $output .= '</h3>';
    $output .= '<p style="margin: 0; font-size: 13px; color: #059669; font-weight: 400;">Engagement Letter</p>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    // Right side
    $output .= '<div style="text-align: right;">';
    $output .= '<div style="margin-bottom: 4px;">';
    $output .= '<span style="font-size: 11px; color: #059669; font-weight: 500; display: block;">Engagement Fee</span>';
    $output .= '</div>';
    $output .= '<div style="margin-bottom: 12px;">';
    $output .= '<span style="font-size: 28px; font-weight: 700; color: #047857;">' . wc_price($engagement_fee) . '</span>';
    $output .= '</div>';
    $output .= '<button type="button" class="el-remove-grouped-package" data-parent-id="' . esc_attr($parent['id']) . '" style="
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s;
    ">Remove</button>';
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

function el_render_cart_item($cart_item_key, $cart_item) {
    $product = $cart_item['data'];
    $product_id = $cart_item['product_id'];
    
    $is_grouped_child = !empty($cart_item['el_grouped_parent']);
    $item_type = $cart_item['el_item_type'] ?? 'optional';
    $show_description = $cart_item['el_show_description'] ?? false;
    
    // Styling
    if ($is_grouped_child) {
        $item_styles = 'background: rgba(250, 250, 250, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 0 16px 16px 0;
            padding: 18px 20px 18px 35px;
            margin-bottom: 0;
            margin-left: 20px;
            border-left: 3px solid #10b981;
            border-top: 1px dashed #e5e7eb;
            transition: all 0.3s;';
    } else {
        $item_styles = 'background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(168, 188, 206, 0.2);
            transition: all 0.3s;';
    }
    
    $output = '<div class="el-cart-item' . ($is_grouped_child ? ' el-grouped-child' : '') . '" data-cart-key="' . esc_attr($cart_item_key) . '" style="' . $item_styles . '">';
    
    // Top row: Name and PDF teaser (if children)
    $output .= '<div style="margin-bottom: 8px;">';
    
    // Name
    $output .= '<h3 style="margin: 0 0 4px 0; font-size: 17px; font-weight: 500; color: ' . EL_COLOR_NAVY . ';">';
    if ($is_grouped_child) {
        $output .= '<span style="display: inline-block; width: 20px; color: #10b981; font-weight: 400;">└</span> ';
    }
    $output .= esc_html($product->get_name());
    $output .= '</h3>';
    
    // PDF teaser text under title (for children only)
    if ($is_grouped_child) {
        $pdf_teaser = get_field('el_teaser', $product_id);
        if ($pdf_teaser) {
            $output .= '<div style="font-size: 13px; color: #64748b; line-height: 1.5; margin-left: 20px;">';
            $output .= esc_html($pdf_teaser);
            $output .= '</div>';
        }
    }
    
    $output .= '</div>';
    
    // Row 2: Pill selector centered and Price on far right
    if ($is_grouped_child) {
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">';
        
        // Empty spacer on left for centering
        $output .= '<div style="flex: 1;"></div>';
        
        // Pill selector with 4 options - centered
        $output .= '<div class="el-pill-selector" data-cart-key="' . esc_attr($cart_item_key) . '" style="
            display: flex;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            padding: 3px;
            gap: 2px;
        ">';
        
        // Mandatory pill
        $mandatory_active = $item_type === 'mandatory';
        $output .= '<button type="button" class="el-pill-option" data-type="mandatory" style="
            background: ' . ($mandatory_active ? 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)' : 'transparent') . ';
            color: ' . ($mandatory_active ? 'white' : '#6b7280') . ';
            border: none;
            padding: 5px 10px;
            border-radius: 16px;
            font-size: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        ">Mandatory</button>';
        
        // Suggested pill
        $suggested_active = $item_type === 'suggested';
        $output .= '<button type="button" class="el-pill-option" data-type="suggested" style="
            background: ' . ($suggested_active ? 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)' : 'transparent') . ';
            color: ' . ($suggested_active ? 'white' : '#6b7280') . ';
            border: none;
            padding: 5px 10px;
            border-radius: 16px;
            font-size: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        ">Suggested</button>';
        
        // Optional pill
        $optional_active = $item_type === 'optional';
        $output .= '<button type="button" class="el-pill-option" data-type="optional" style="
            background: ' . ($optional_active ? 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)' : 'transparent') . ';
            color: ' . ($optional_active ? 'white' : '#6b7280') . ';
            border: none;
            padding: 5px 10px;
            border-radius: 16px;
            font-size: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        ">Optional</button>';
        
        // Hide pill
        $hide_active = $item_type === 'hide';
        $output .= '<button type="button" class="el-pill-option" data-type="hide" style="
            background: ' . ($hide_active ? 'linear-gradient(135deg, #64748b 0%, #475569 100%)' : 'transparent') . ';
            color: ' . ($hide_active ? 'white' : '#6b7280') . ';
            border: none;
            padding: 5px 10px;
            border-radius: 16px;
            font-size: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        ">Hide</button>';
        
        $output .= '</div>'; // Close pill selector
        
        // Price - aligned with slider
        $output .= '<div style="text-align: right; min-width: 100px;">';
        $output .= '<span style="font-size: 17px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">' . wc_price($product->get_price()) . '</span>';
        $output .= '</div>';
        
        $output .= '</div>'; // Close slider + price row
    } else {
        // For non-grouped items, just show price and remove button
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">';
        $output .= '<span style="font-size: 17px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">' . wc_price($product->get_price()) . '</span>';
        $output .= '<button type="button" class="el-remove-cart-item" data-cart-key="' . esc_attr($cart_item_key) . '" style="
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        ">Remove</button>';
        $output .= '</div>';
    }
    
    // Details collapsible (for children only)
    if ($is_grouped_child) {
        $pdf_description = get_field('pdf_el_text', $product_id);
        
        if ($pdf_description) {
            $output .= '<div style="margin-top: 8px;">';
            
            // Collapsible header - less prominent with arrow next to text
            $output .= '<div class="el-pdf-description-toggle" data-cart-key="' . esc_attr($cart_item_key) . '" style="
                background: transparent;
                border: none;
                border-bottom: 1px solid #f1f5f9;
                padding: 6px 0;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s;
            ">';
            $output .= '<span style="font-size: 11px; font-weight: 400; color: #94a3b8;">Details</span>';
            $output .= '<span class="el-toggle-icon" style="font-size: 9px; color: #cbd5e1; transition: transform 0.2s;">' . ($show_description ? '▼' : '▶') . '</span>';
            $output .= '</div>';
            
            // Collapsible content - render HTML
            if ($show_description) {
                $output .= '<div class="el-pdf-description-content" style="
                    margin-top: 8px;
                    padding: 12px;
                    background: rgba(255, 255, 255, 0.5);
                    border: 1px solid #f1f5f9;
                    border-radius: 8px;
                    font-size: 12px;
                    color: #64748b;
                    line-height: 1.6;
                ">' . wp_kses_post($pdf_description) . '</div>';
            }
            
            $output .= '</div>';
        }
    }
    
    $output .= '</div>'; // Close cart item
    
    return $output;
}

function el_render_cart_totals() {
    if (!el_ensure_cart()) {
        return '';
    }
    
    // Calculate total engagement fee (sum of all parent products' engagement fees)
    $total_engagement_fee = 0;
    $parent = el_get_grouped_parent();
    
    if ($parent) {
        $engagement_fee = get_field('engagement_fee_due_today', $parent['id']);
        $total_engagement_fee = $engagement_fee ? floatval($engagement_fee) : 0;
    }
    
    // Also add non-grouped products
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (empty($cart_item['el_grouped_parent'])) {
            $product = $cart_item['data'];
            $total_engagement_fee += floatval($product->get_price()) * $cart_item['quantity'];
        }
    }
    
    $output = '<div class="el-cart-totals" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        border-radius: 16px;
        padding: 25px;
        margin-top: 25px;
        color: white;
    ">';
    
    // Total only (no subtotal)
    $output .= '<div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">';
    $output .= '<span style="font-size: 18px; font-weight: 500;">Total Engagement Fee</span>';
    $output .= '<span style="font-size: 28px; font-weight: 700;">' . wc_price($total_engagement_fee) . '</span>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}

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
    $output .= '<h3 style="margin: 0 0 10px 0; font-size: 22px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">Your cart is empty</h3>';
    $output .= '<p style="margin: 0 0 25px 0; color: #6b7280; font-weight: 400;">Add templates from the previous step to continue.</p>';
    
    $output .= '<button type="button" class="el-back-to-templates" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
    ">← Back to Templates</button>';
    
    $output .= '</div>';
    
    return $output;
}

// ============================================
// AJAX HANDLERS
// ============================================

function el_ajax_toggle_description() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    
    if (!$cart_item_key) {
        wp_send_json_error(['message' => 'Invalid cart item key']);
    }
    
    $cart = WC()->cart->get_cart();
    if (isset($cart[$cart_item_key])) {
        $current_state = $cart[$cart_item_key]['el_show_description'] ?? false;
        WC()->cart->cart_contents[$cart_item_key]['el_show_description'] = !$current_state;
        WC()->cart->set_session();
        
        wp_send_json_success([
            'message' => 'Description visibility toggled',
            'show_description' => !$current_state
        ]);
    }
    
    wp_send_json_error(['message' => 'Cart item not found']);
}
add_action('wp_ajax_el_toggle_description', 'el_ajax_toggle_description');

function el_ajax_update_item_type() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : 'optional';
    
    if (!$cart_item_key || !in_array($item_type, ['mandatory', 'suggested', 'optional', 'hide'])) {
        wp_send_json_error(['message' => 'Invalid parameters']);
    }
    
    $cart = WC()->cart->get_cart();
    if (isset($cart[$cart_item_key])) {
        WC()->cart->cart_contents[$cart_item_key]['el_item_type'] = $item_type;
        WC()->cart->set_session();
        
        wp_send_json_success([
            'message' => 'Item type updated',
            'item_type' => $item_type
        ]);
    }
    
    wp_send_json_error(['message' => 'Cart item not found']);
}
add_action('wp_ajax_el_update_item_type', 'el_ajax_update_item_type');

function el_ajax_remove_grouped_package() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    if (!$parent_id) {
        wp_send_json_error(['message' => 'Invalid parent ID']);
    }
    
    $removed_count = 0;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['el_grouped_parent']) && $cart_item['el_grouped_parent'] == $parent_id) {
            WC()->cart->remove_cart_item($cart_item_key);
            $removed_count++;
        }
    }
    
    el_clear_grouped_parent();
    
    wp_send_json_success([
        'message' => 'Package removed',
        'removed_count' => $removed_count,
        'cart_count' => el_get_cart_count()
    ]);
}
add_action('wp_ajax_el_remove_grouped_package', 'el_ajax_remove_grouped_package');

function el_ajax_refresh_cart_editor() {
  check_ajax_referer(EL_NONCE, 'nonce');
    
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

function el_cart_editor_glass_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your cart.</p>';
    }
    
    $output = '<div id="elCartEditorContainer">';
    $output .= el_render_glass_cart();
    $output .= '</div>';
    
    $output .= '<div style="margin-top: 30px; display: flex; justify-content: space-between;">';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_cart_editor_glass', 'el_cart_editor_glass_shortcode');

function el_cart_editor_enhanced_shortcode() {
    return el_cart_editor_glass_shortcode();
}
add_shortcode('el_cart_editor_enhanced', 'el_cart_editor_enhanced_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

function el_enqueue_tab3_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Toggle PDF description
            $(document).on('click', '.el-pdf-description-toggle', function() {
                var cartKey = $(this).data('cart-key');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_toggle_description',
                        nonce: elAjax.nonce,
                        cart_item_key: cartKey
                    },
                    success: function(response) {
                        if (response.success) {
                            refreshCartDisplay();
                        }
                    }
                });
            });
            
            // Pill selector click
            $(document).on('click', '.el-pill-option', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var button = $(this);
                var selector = button.closest('.el-pill-selector');
                var cartKey = selector.data('cart-key');
                var itemType = button.data('type');
                
                // Instant visual feedback - update UI immediately
                selector.find('.el-pill-option').each(function() {
                    var btn = $(this);
                    var type = btn.data('type');
                    
                    if (type === itemType) {
                        // Active state
                        if (type === 'mandatory') {
                            btn.css({
                                'background': 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)',
                                'color': 'white'
                            });
                        } else if (type === 'suggested') {
                            btn.css({
                                'background': 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                                'color': 'white'
                            });
                        } else if (type === 'optional') {
                            btn.css({
                                'background': 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)',
                                'color': 'white'
                            });
                        } else if (type === 'hide') {
                            btn.css({
                                'background': 'linear-gradient(135deg, #64748b 0%, #475569 100%)',
                                'color': 'white'
                            });
                        }
                    } else {
                        // Inactive state
                        btn.css({
                            'background': 'transparent',
                            'color': '#6b7280'
                        });
                    }
                });
                
                // Save to backend
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_update_item_type',
                        nonce: elAjax.nonce,
                        cart_item_key: cartKey,
                        item_type: itemType
                    },
                    success: function(response) {
                        // Already updated UI, no need to refresh
                        if (!response.success) {
                            // Only refresh if there was an error
                            refreshCartDisplay();
                        }
                    },
                    error: function() {
                        // Revert on error
                        refreshCartDisplay();
                    }
                });
            });
            
            // Remove grouped package
            $(document).on('click', '.el-remove-grouped-package', function() {
                var parentId = $(this).data('parent-id');
                var button = $(this);
                
                if (!confirm('Remove this engagement letter?')) {
                    return;
                }
                
                button.prop('disabled', true).text('Removing...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_remove_grouped_package',
                        nonce: elAjax.nonce,
                        parent_id: parentId
                    },
                    success: function(response) {
                        if (response.success) {
                            refreshCartDisplay();
                        } else {
                            alert('Error: ' + response.data.message);
                            button.prop('disabled', false).text('Remove');
                        }
                    }
                });
            });
            
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
                            refreshCartDisplay();
                        } else {
                            alert('Error: ' + response.data.message);
                            item.css('opacity', '1');
                        }
                    }
                });
            });
            
            // Back to templates
            $(document).on('click', '.el-back-to-templates', function() {
                $('" . el_get_tab_selector(2) . "').click();
            });
            
            // Refresh cart
            function refreshCartDisplay() {
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_REFRESH_CART . "',
                        nonce: elAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#elCartEditorContainer').html(response.data.html);
                        }
                    }
                });
            }
            
            // Auto-refresh on tab click
            $('" . el_get_tab_selector(3) . "').on('click', function() {
                setTimeout(refreshCartDisplay, 300);
            });
            
            // Hover effects
            $(document).on('mouseenter', '.el-cart-item:not(.el-grouped-child)', function() {
                $(this).css({
                    'transform': 'translateY(-2px)',
                    'box-shadow': '0 12px 24px rgba(0, 0, 0, 0.15)'
                });
            }).on('mouseleave', '.el-cart-item:not(.el-grouped-child)', function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': 'none'
                });
            });
            
            $(document).on('mouseenter', '.el-grouped-child', function() {
                $(this).css({
                    'background': 'rgba(240, 253, 244, 0.5)',
                    'border-left-width': '4px'
                });
            }).on('mouseleave', '.el-grouped-child', function() {
                $(this).css({
                    'background': 'rgba(250, 250, 250, 0.9)',
                    'border-left-width': '3px'
                });
            });
            
            $(document).on('mouseenter', '.el-pdf-description-toggle', function() {
                $(this).css('border-bottom-color', '#cbd5e1');
            }).on('mouseleave', '.el-pdf-description-toggle', function() {
                $(this).css('border-bottom-color', '#f1f5f9');
            });
            
            $(document).on('mouseenter', '.el-pill-option:not([style*=\"linear-gradient\"])', function() {
                $(this).css('background', '#f3f4f6');
            }).on('mouseleave', '.el-pill-option:not([style*=\"linear-gradient\"])', function() {
                $(this).css('background', 'transparent');
            });
        });
    ");
    
    wp_add_inline_style('wp-block-library', "
        .el-cart-glass-wrapper {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .el-cart-item {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .el-grouped-parent-header {
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .el-grouped-child:last-of-type {
            margin-bottom: 20px !important;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab3_script');

if (EL_DEBUG_MODE) {
    el_log('Tab 3 (Cart Editor - Glass) module loaded successfully', 'info');
}