<?php
/**
 * Engagement Letter System - Tab 4: PDF Preview
 * 
 * Handles PDF preview generation and display:
 * - Auto-generation from cart + client data
 * - Merge tag replacement
 * - HTML preview rendering
 * - Transient storage with expiry
 * - Signature placeholder insertion
 * 
 * LOAD ORDER: Tab module (after all core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php, merge-tags.php, woocommerce.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// PDF DATA BUILDING
// ============================================

/**
 * Builds complete PDF data from current state
 * 
 * @return array|false PDF data array or false on failure
 */
function el_build_pdf_data() {
    $engagement_id = el_get_current_engagement_id();
    
    if (!$engagement_id) {
        el_log('Cannot build PDF data - no active engagement', 'error');
        return false;
    }
    
    // Get engagement data
    $engagement = el_get_engagement_letter($engagement_id);
    $form_data = $engagement['form_data'];
    
    // Get cart data
    if (!el_ensure_cart()) {
        return false;
    }
    
    $cart = WC()->cart;
    
    if ($cart->is_empty()) {
        el_log('Cannot build PDF - cart is empty', 'error');
        return false;
    }
    
    // Build services list
    $services = [];
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        
        $service = [
            'name' => $product->get_name(),
            'description' => $product->get_short_description(),
            'price' => $product->get_price(),
            'quantity' => $cart_item['quantity'],
            'total' => $cart_item['line_total'],
            'acf_data' => el_get_product_acf_data($product_id),
        ];
        
        // Add parent data if grouped child
        if (!empty($cart_item[EL_CART_META_PARENT_DATA])) {
            $service['parent_data'] = $cart_item[EL_CART_META_PARENT_DATA];
            $service['parent_name'] = $cart_item[EL_CART_META_PARENT_NAME] ?? '';
        }
        
        $services[] = $service;
    }
    
    // Build complete PDF data
    $pdf_data = [
        'reference' => $engagement['reference'],
        'created_date' => current_time('mysql'),
        'client' => $form_data,
        'lawyer' => el_get_lawyer_merge_data(),
        'services' => $services,
        'cart_totals' => [
            'subtotal' => $cart->get_subtotal(),
            'tax' => $cart->get_total_tax(),
            'total' => $cart->get_total(''),
        ],
        'boilerplate' => [
            'letterhead' => get_field(EL_ACF_LETTERHEAD, 'option'),
            'opening_left' => get_field(EL_ACF_OPENING_LEFT, 'option'),
            'opening_right' => get_field(EL_ACF_OPENING_RIGHT, 'option'),
            'footer' => get_field(EL_ACF_FOOTER_BOILERPLATE, 'option'),
            'signature_block' => get_field(EL_ACF_SIGNATURE_BLOCK, 'option'),
        ],
        'engagement_id' => $engagement_id,
    ];
    
    return $pdf_data;
}

// ============================================
// PDF HTML GENERATION
// ============================================

/**
 * Generates PDF preview HTML
 * 
 * @param array $pdf_data PDF data array
 * @return string HTML preview
 */
function el_render_pdf_preview_html($pdf_data) {
    if (empty($pdf_data)) {
        return '<p>No PDF data available.</p>';
    }
    
    // Build merge data for tag replacement
    $merge_data = array_merge(
        el_get_system_merge_data(),
        el_get_lawyer_merge_data(),
        [
            'reference' => $pdf_data['reference'],
            'current_date' => date('F j, Y'),
            'client_name' => trim(($pdf_data['client']['first_name'] ?? '') . ' ' . ($pdf_data['client']['last_name'] ?? '')),
            'client_email' => $pdf_data['client']['email'] ?? '',
            'client_address' => el_format_full_address($pdf_data['client']),
            'total_amount' => el_format_currency($pdf_data['cart_totals']['total']),
        ]
    );
    
    $output = '<div class="el-pdf-preview" style="
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 60px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-family: \'Times New Roman\', serif;
        line-height: 1.6;
    ">';
    
    // Letterhead
    if (!empty($pdf_data['boilerplate']['letterhead'])) {
        $output .= '<div class="el-letterhead" style="margin-bottom: 40px; text-align: center;">';
        $output .= el_replace_merge_tags($pdf_data['boilerplate']['letterhead'], $merge_data);
        $output .= '</div>';
    }
    
    // Date and reference
    $output .= '<div class="el-header-info" style="margin-bottom: 30px; text-align: right; font-size: 14px;">';
    $output .= '<p style="margin: 5px 0;"><strong>Date:</strong> ' . date('F j, Y') . '</p>';
    $output .= '<p style="margin: 5px 0;"><strong>Reference:</strong> ' . esc_html($pdf_data['reference']) . '</p>';
    $output .= '</div>';
    
    // Client address block
    $output .= '<div class="el-client-address" style="margin-bottom: 30px;">';
    $output .= '<p style="margin: 0;"><strong>' . esc_html($merge_data['client_name']) . '</strong></p>';
    if (!empty($pdf_data['client']['street_address'])) {
        $output .= '<p style="margin: 5px 0;">' . nl2br(esc_html(el_format_full_address($pdf_data['client']))) . '</p>';
    }
    $output .= '</div>';
    
    // Opening
    $output .= '<div class="el-opening" style="margin-bottom: 25px;">';
    if (!empty($pdf_data['boilerplate']['opening_left'])) {
        $output .= '<p>' . el_replace_merge_tags($pdf_data['boilerplate']['opening_left'], $merge_data) . '</p>';
    }
    $output .= '</div>';
    
    // Services section
    $output .= '<h2 style="font-size: 18px; margin: 30px 0 20px 0; color: ' . EL_COLOR_NAVY . ';">Scope of Services</h2>';
    
    foreach ($pdf_data['services'] as $service) {
        $output .= '<div class="el-service-item" style="margin-bottom: 25px; padding: 15px; background: #f9fafb; border-left: 3px solid ' . EL_COLOR_PRIMARY . ';">';
        
        // Service name
        $output .= '<h3 style="margin: 0 0 10px 0; font-size: 16px;">' . esc_html($service['name']) . '</h3>';
        
        // Service description (with merge tags replaced)
        if (!empty($service['acf_data']['pdf_el_text'])) {
            $service_merge_data = array_merge($merge_data, [
                'service_price' => el_format_currency($service['price']),
            ]);
            $output .= '<div style="font-size: 14px; margin-bottom: 10px;">';
            $output .= el_replace_merge_tags($service['acf_data']['pdf_el_text'], $service_merge_data);
            $output .= '</div>';
        }
        
        // Price
        $output .= '<p style="font-weight: 600; margin: 10px 0 0 0;">Fee: ' . el_format_currency($service['price']) . '</p>';
        
        $output .= '</div>';
    }
    
    // Totals
    $output .= '<div class="el-totals" style="margin: 30px 0; padding: 20px; background: ' . EL_COLOR_BG_LIGHT . '; border-radius: 8px;">';
    $output .= '<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">';
    $output .= '<strong>Subtotal:</strong>';
    $output .= '<strong>' . el_format_currency($pdf_data['cart_totals']['subtotal']) . '</strong>';
    $output .= '</div>';
    
    if ($pdf_data['cart_totals']['tax'] > 0) {
        $output .= '<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">';
        $output .= '<span>Tax:</span>';
        $output .= '<span>' . el_format_currency($pdf_data['cart_totals']['tax']) . '</span>';
        $output .= '</div>';
    }
    
    $output .= '<div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid ' . EL_COLOR_PRIMARY . '; font-size: 18px;">';
    $output .= '<strong>Total:</strong>';
    $output .= '<strong>' . el_format_currency($pdf_data['cart_totals']['total']) . '</strong>';
    $output .= '</div>';
    $output .= '</div>';
    
    // Footer boilerplate
    if (!empty($pdf_data['boilerplate']['footer'])) {
        $output .= '<div class="el-footer" style="margin-top: 40px; font-size: 13px; color: #6b7280;">';
        $output .= el_replace_merge_tags($pdf_data['boilerplate']['footer'], $merge_data);
        $output .= '</div>';
    }
    
    // Signature block
    $output .= '<div class="el-signature-block" style="margin-top: 60px;">';
    if (!empty($pdf_data['boilerplate']['signature_block'])) {
        $output .= el_replace_merge_tags($pdf_data['boilerplate']['signature_block'], $merge_data);
    } else {
        $output .= '<div style="margin-top: 60px;">';
        $output .= '<div style="border-top: 2px solid #000; width: 300px; margin-bottom: 10px;"></div>';
        $output .= '<p style="margin: 0;"><strong>Client Signature</strong></p>';
        $output .= '<p style="margin: 5px 0 0 0; font-size: 13px;">Date: _________________</p>';
        $output .= '</div>';
    }
    $output .= '</div>';
    
    $output .= '</div>'; // Close preview
    
    return $output;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX handler: Generate PDF preview
 */
function el_ajax_generate_pdf_preview() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    // Build PDF data
    $pdf_data = el_build_pdf_data();
    
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'Failed to build PDF data. Please ensure you have selected services and entered client details.']);
    }
    
    // Store PDF data in transient
    $reference = $pdf_data['reference'];
    $stored = el_set_pdf_data($reference, $pdf_data);
    
    if (!$stored) {
        wp_send_json_error(['message' => 'Failed to store PDF data']);
    }
    
    // Store reference in session
    el_set_session(EL_SESSION_PDF_REF, $reference);
    
    // Update engagement letter
    $engagement_id = $pdf_data['engagement_id'];
    el_set_meta($engagement_id, 'current_tab', 4);
    el_set_meta($engagement_id, 'status', EL_STATUS_GENERATED);
    
    // Generate HTML preview
    $html = el_render_pdf_preview_html($pdf_data);
    
    wp_send_json_success([
        'message' => 'PDF generated successfully',
        'reference' => $reference,
        'html' => $html,
    ]);
}
add_action('wp_ajax_' . EL_AJAX_GENERATE_PDF, 'el_ajax_generate_pdf_preview');

/**
 * AJAX handler: Load existing PDF preview
 */
function el_ajax_load_pdf_preview() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    
    if (!$reference) {
        // Try to get from session
        $reference = el_get_session(EL_SESSION_PDF_REF);
    }
    
    if (!$reference) {
        wp_send_json_error(['message' => 'No PDF reference provided']);
    }
    
    // Load PDF data from transient
    $pdf_data = el_get_pdf_data($reference);
    
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'PDF data expired or not found. Please regenerate.']);
    }
    
    // Generate HTML
    $html = el_render_pdf_preview_html($pdf_data);
    
    wp_send_json_success([
        'reference' => $reference,
        'html' => $html,
    ]);
}
add_action('wp_ajax_el_load_pdf_preview', 'el_ajax_load_pdf_preview');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: PDF preview with auto-generation
 * 
 * Usage: [el_pdf_preview_auto]
 */
function el_pdf_preview_auto_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view PDF preview.</p>';
    }
    
    $output = '<div class="el-pdf-preview-wrapper">';
    
    // Auto-generate button
    $output .= '<div class="el-preview-controls" style="margin-bottom: 30px; text-align: center;">';
    $output .= '<button type="button" id="elGeneratePdfButton" class="button button-primary" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        color: white;
    ">Generate Preview</button>';
    $output .= '<p style="margin-top: 10px; font-size: 13px; color: #6b7280;">This will create a preview of your engagement letter</p>';
    $output .= '</div>';
    
    // Preview container
    $output .= '<div id="elPdfPreviewContainer" style="min-height: 400px;"></div>';
    
    // Loading state
    $output .= '<div id="elPdfLoading" style="display: none; text-align: center; padding: 60px 0;">';
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

/**
 * Enqueues Tab 4 JavaScript
 */
function el_enqueue_tab4_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            var pdfGenerated = false;
            
            // Generate PDF button
            $('#elGeneratePdfButton').on('click', function() {
                generatePdfPreview();
            });
            
            // Auto-generate when tab is shown (if not already generated)
            $('" . el_get_tab_selector(4) . "').on('click', function() {
                setTimeout(function() {
                    if (!pdfGenerated) {
                        generatePdfPreview();
                    }
                }, 300);
            });
            
            // Generate PDF preview
            function generatePdfPreview() {
                $('#elPdfLoading').show();
                $('#elPdfPreviewContainer').html('');
                $('#elGeneratePdfButton').prop('disabled', true).text('Generating...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_GENERATE_PDF . "',
                        nonce: elAjax.nonce
                    },
                    success: function(response) {
                        $('#elPdfLoading').hide();
                        
                        if (response.success) {
                            $('#elPdfPreviewContainer').html(response.data.html);
                            $('#elGeneratePdfButton').text('✓ Preview Generated').css('background', '#10b981');
                            pdfGenerated = true;
                            
                            // Add fade-in animation
                            $('#elPdfPreviewContainer .el-pdf-preview').css({
                                'opacity': '0',
                                'transform': 'translateY(20px)'
                            }).animate({
                                'opacity': '1',
                                'transform': 'translateY(0)'
                            }, 500);
                        } else {
                            alert('Error: ' + response.data.message);
                            $('#elGeneratePdfButton').prop('disabled', false).text('Generate Preview');
                        }
                    },
                    error: function() {
                        $('#elPdfLoading').hide();
                        alert('An error occurred while generating the preview.');
                        $('#elGeneratePdfButton').prop('disabled', false).text('Generate Preview');
                    }
                });
            }
            
            // Load existing preview if reference exists
            var existingReference = '" . esc_js(el_get_session(EL_SESSION_PDF_REF, '')) . "';
            if (existingReference) {
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_load_pdf_preview',
                        nonce: elAjax.nonce,
                        reference: existingReference
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#elPdfPreviewContainer').html(response.data.html);
                            $('#elGeneratePdfButton').text('✓ Preview Generated').css('background', '#10b981');
                            pdfGenerated = true;
                        }
                    }
                });
            }
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab4_script');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Tab 4 (PDF Preview) module loaded successfully', 'info');
}