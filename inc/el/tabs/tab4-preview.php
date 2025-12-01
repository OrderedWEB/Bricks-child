<?php
/**
 * Engagement Letter System - Tab 4: Quick Web Preview
 * 
 * Fast and loose sanity check preview for lawyers
 * Shows services with checkboxes based on type:
 * - Mandatory: checked box ✓
 * - Suggested: empty box with "Suggested by lawyer" flag
 * - Optional: empty box
 * - Hide: not shown
 * 
 * @package Engagement_Letter_System
 * @version 2.1.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// PREVIEW DATA BUILDING
// ============================================

/**
 * Builds quick preview data from current state
 */
function el_build_quick_preview_data() {
    $engagement_id = el_get_current_engagement_id();
    $form_data = [];
    $reference = 'PREVIEW-' . date('Ymd');
    $has_engagement_error = false;
    
    if (!$engagement_id) {
        el_log('No engagement ID found - using defaults', 'warning');
        $has_engagement_error = 'no_engagement';
        $form_data = [
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'test@example.com',
        ];
    } else {
        // Get engagement data
        $engagement = el_get_engagement_letter($engagement_id);
        
        if (!$engagement || empty($engagement['form_data'])) {
            el_log('No engagement data or form data', 'warning');
            $has_engagement_error = 'no_form_data';
            $form_data = [
                'first_name' => 'Test',
                'last_name' => 'Client',
                'email' => 'test@example.com',
            ];
        } else {
            $form_data = $engagement['form_data'];
            $reference = $engagement['reference'];
        }
    }
    
    // Get cart data
    if (!el_ensure_cart()) {
        return [
            'error' => 'no_cart',
            'message' => 'Cart not available.'
        ];
    }
    
    $cart = WC()->cart;
    
    if ($cart->is_empty()) {
        return [
            'error' => 'empty_cart',
            'message' => 'Please add services in Tab 2 first.'
        ];
    }
    
    // Get parent product if exists
    $parent = el_get_grouped_parent();
    $parent_data = null;
    
    if ($parent) {
        $parent_product = wc_get_product($parent['id']);
        if ($parent_product) {
            $engagement_fee = get_field('engagement_fee_due_today', $parent['id']);
            $parent_data = [
                'id' => $parent['id'],
                'name' => $parent['name'],
                'engagement_fee' => $engagement_fee ? floatval($engagement_fee) : 0,
            ];
        }
    }
    
    // Build services list
    $services = [];
    
    el_log('Starting to build services list. Cart item count: ' . count($cart->get_cart()), 'debug');
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        
        // Get item type (default to optional if not set)
        $item_type = $cart_item['el_item_type'] ?? 'optional';
        
        el_log('Processing product ' . $product_id . ': ' . $product->get_name() . ' - Type: ' . $item_type, 'debug');
        
        // Skip items marked as 'hide'
        if ($item_type === 'hide') {
            el_log('Skipping hidden item: ' . $product->get_name(), 'debug');
            continue;
        }
        
        $services[] = [
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'item_type' => $item_type,
            'description' => get_field('pdf_el_text', $product_id), // Full PDF description
        ];
    }
    
    el_log('Built services list. Total services: ' . count($services), 'debug');
    
    // Build return data
    $return_data = [
        'reference' => $reference,
        'created_date' => current_time('mysql'),
        'client' => $form_data,
        'parent' => $parent_data,
        'services' => $services,
    ];
    
    // Add error flag if there was one
    if ($has_engagement_error) {
        $return_data['error'] = $has_engagement_error;
    }
    
    return $return_data;
}

// ============================================
// HTML RENDERING
// ============================================

/**
 * Renders quick preview HTML
 */
function el_render_quick_preview_html($preview_data) {
    if (empty($preview_data)) {
        return '<p>No preview data available.</p>';
    }
    
    // Show warning if there's an error state
    $warning = '';
    if (isset($preview_data['error'])) {
        if ($preview_data['error'] === 'no_engagement' || $preview_data['error'] === 'no_form_data') {
            $warning = '<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">';
            $warning .= '<p style="margin: 0; font-size: 14px; color: #92400e;"><strong>⚠️ Note:</strong> Using default client information for preview. Please complete Tab 1 for actual client details.</p>';
            $warning .= '</div>';
        }
    }
    
    $output = '<div class="el-quick-preview" style="
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
        line-height: 1.6;
    ">';
    
    // Show warning if present
    $output .= $warning;
    
    // Rest of the rendering code stays the same...
    // Header with date and reference
    $output .= '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid ' . EL_COLOR_PRIMARY . ';">';
    $output .= '<div>';
    $output .= '<h1 style="margin: 0 0 10px 0; font-size: 28px; color: ' . EL_COLOR_NAVY . ';">Engagement Letter Preview</h1>';
    $output .= '<p style="margin: 0; color: #6b7280; font-size: 14px;">Quick review before finalization</p>';
    $output .= '</div>';
    $output .= '<div style="text-align: right;">';
    $output .= '<p style="margin: 0 0 5px 0; font-size: 14px;"><strong>Date:</strong> ' . date('F j, Y') . '</p>';
    $output .= '<p style="margin: 0; font-size: 14px;"><strong>Reference:</strong> ' . esc_html($preview_data['reference']) . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    
    // Client Details
    $client = $preview_data['client'];
    $output .= '<div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px;">';
    $output .= '<h2 style="margin: 0 0 15px 0; font-size: 18px; color: ' . EL_COLOR_NAVY . ';">Client Details</h2>';
    $output .= '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">';
    
    $output .= '<div><strong>Name:</strong> ' . esc_html(($client['first_name'] ?? '') . ' ' . ($client['last_name'] ?? '')) . '</div>';
    $output .= '<div><strong>Email:</strong> ' . esc_html($client['email'] ?? '') . '</div>';
    
    if (!empty($client['phone'])) {
        $output .= '<div><strong>Phone:</strong> ' . esc_html($client['phone']) . '</div>';
    }
    
    if (!empty($client['street_address'])) {
        $output .= '<div style="grid-column: 1 / -1;"><strong>Address:</strong><br>' . nl2br(esc_html($client['street_address'])) . '</div>';
        if (!empty($client['city'])) {
            $output .= '<div><strong>City:</strong> ' . esc_html($client['city']) . '</div>';
        }
        if (!empty($client['postal_code'])) {
            $output .= '<div><strong>Postal Code:</strong> ' . esc_html($client['postal_code']) . '</div>';
        }
        if (!empty($client['country'])) {
            $output .= '<div><strong>Country:</strong> ' . esc_html($client['country']) . '</div>';
        }
    }
    
    $output .= '</div>';
    $output .= '</div>';
    
    // Parent Engagement Letter (if exists)
    if ($preview_data['parent']) {
        $output .= '<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 25px; border-radius: 12px; border-left: 4px solid #10b981; margin-bottom: 30px;">';
        $output .= '<h2 style="margin: 0 0 5px 0; font-size: 22px; color: #047857;">' . esc_html($preview_data['parent']['name']) . '</h2>';
        $output .= '<p style="margin: 0; font-size: 18px; font-weight: 600; color: #059669;">Total Engagement Fee: ' . wc_price($preview_data['parent']['engagement_fee']) . '</p>';
        $output .= '</div>';
    }
    
    // Services Section
    $output .= '<h2 style="margin: 30px 0 20px 0; font-size: 20px; color: ' . EL_COLOR_NAVY . ';">Services Included</h2>';
    
    if (empty($preview_data['services'])) {
        $output .= '<div style="background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px; text-align: center;">';
        $output .= '<p style="margin: 0; color: #991b1b;">No services found. Please add services in Tab 2 and Tab 3.</p>';
        $output .= '</div>';
    } else {
        foreach ($preview_data['services'] as $service) {
        $output .= '<div style="
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        ">';
        
        // Header row: checkbox + name + price
        $output .= '<div style="display: flex; align-items: start; gap: 15px; margin-bottom: 12px;">';
        
        // Checkbox based on type
        if ($service['item_type'] === 'mandatory') {
            // Checked box
            $output .= '<div style="
                width: 20px;
                height: 20px;
                border: 2px solid #1e40af;
                border-radius: 3px;
                background: #1e40af;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                flex-shrink: 0;
                margin-top: 2px;
            ">✓</div>';
        } else {
            // Empty checkbox
            $output .= '<div style="
                width: 20px;
                height: 20px;
                border: 2px solid #9ca3af;
                border-radius: 3px;
                flex-shrink: 0;
                margin-top: 2px;
            "></div>';
        }
        
        // Service name
        $output .= '<div style="flex: 1;">';
        $output .= '<h3 style="margin: 0; font-size: 16px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">' . esc_html($service['name']) . '</h3>';
        
        // Suggested flag
        if ($service['item_type'] === 'suggested') {
            $output .= '<span style="
                display: inline-block;
                margin-top: 5px;
                padding: 3px 10px;
                background: #dbeafe;
                color: #1e40af;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
            ">✨ Suggested by lawyer</span>';
        }
        
        $output .= '</div>';
        
        // Price
        $output .= '<div style="text-align: right; font-size: 18px; font-weight: 600; color: ' . EL_COLOR_NAVY . ';">';
        $output .= wc_price($service['price']);
        $output .= '</div>';
        
        $output .= '</div>'; // Close header row
        
        // Description
        if (!empty($service['description'])) {
            $output .= '<div style="
                margin-left: 35px;
                font-size: 14px;
                color: #4b5563;
                line-height: 1.7;
                padding-top: 10px;
                border-top: 1px dashed #e5e7eb;
            ">';
            $output .= wp_kses_post($service['description']);
            $output .= '</div>';
        }
        
        $output .= '</div>'; // Close service box
    }
    } // Close services loop
    
    // Note about pricing
    if ($preview_data['parent']) {
        $output .= '<div style="
            margin-top: 30px;
            padding: 15px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        ">';
        $output .= '<p style="margin: 0; font-size: 13px; color: #92400e;"><strong>Note:</strong> Individual service prices shown above are for reference. Your total engagement fee is ' . wc_price($preview_data['parent']['engagement_fee']) . ' as shown at the top.</p>';
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Close preview wrapper
    
    return $output;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Generate quick preview
 */
function el_ajax_generate_quick_preview() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $preview_data = el_build_quick_preview_data();
    
    if (!$preview_data) {
        wp_send_json_error(['message' => 'Failed to build preview data.']);
    }
    
    // Check for specific errors
    if (isset($preview_data['error'])) {
        // Log but continue with default data for now
        el_log('Preview warning: ' . $preview_data['error'], 'warning');
    }
    
    $html = el_render_quick_preview_html($preview_data);
    
    wp_send_json_success([
        'message' => 'Preview generated',
        'html' => $html,
    ]);
}
add_action('wp_ajax_el_generate_quick_preview', 'el_ajax_generate_quick_preview');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: Quick preview
 */
function el_pdf_preview_auto_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view preview.</p>';
    }
    
    $output = '<div class="el-quick-preview-wrapper">';
    
    $output .= '<div style="text-align: center; margin-bottom: 30px;">';
    $output .= '<button type="button" id="elGenerateQuickPreview" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        color: white;
    ">Generate Quick Preview</button>';
    $output .= '</div>';
    
    $output .= '<div id="elQuickPreviewContainer"></div>';
    
    $output .= '<div id="elQuickPreviewLoading" style="display: none; text-align: center; padding: 60px 0;">';
    $output .= '<div style="font-size: 48px; margin-bottom: 20px;">📄</div>';
    $output .= '<p style="font-size: 16px; color: #6b7280;">Generating preview...</p>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_pdf_preview_auto', 'el_pdf_preview_auto_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

function el_enqueue_tab4_quick_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            var previewGenerated = false;
            
            // Generate button
            $('#elGenerateQuickPreview').on('click', function() {
                generateQuickPreview();
            });
            
            // Auto-generate when tab 4 is shown
            $('" . el_get_tab_selector(4) . "').on('click', function() {
                setTimeout(function() {
                    if (!previewGenerated) {
                        generateQuickPreview();
                    }
                }, 300);
            });
            
            function generateQuickPreview() {
                $('#elQuickPreviewLoading').show();
                $('#elQuickPreviewContainer').html('');
                $('#elGenerateQuickPreview').prop('disabled', true).text('Generating...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_generate_quick_preview',
                        nonce: elAjax.nonce
                    },
                    success: function(response) {
                        $('#elQuickPreviewLoading').hide();
                        
                        if (response.success) {
                            $('#elQuickPreviewContainer').html(response.data.html);
                            $('#elGenerateQuickPreview').text('✓ Preview Generated').css('background', '#10b981');
                            previewGenerated = true;
                        } else {
                            alert('Error: ' + response.data.message);
                            $('#elGenerateQuickPreview').prop('disabled', false).text('Generate Quick Preview');
                        }
                    },
                    error: function() {
                        $('#elQuickPreviewLoading').hide();
                        alert('An error occurred while generating the preview.');
                        $('#elGenerateQuickPreview').prop('disabled', false).text('Generate Quick Preview');
                    }
                });
            }
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab4_quick_script');

if (EL_DEBUG_MODE) {
    el_log('Tab 4 (Quick Preview) module loaded successfully', 'info');
}