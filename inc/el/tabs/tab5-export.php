<?php
/**
 * Engagement Letter System - Tab 5: PDF Export
 * 
 * Handles final PDF export and download:
 * - Print-ready HTML generation
 * - PDF download handling
 * - Final document storage
 * - Completion workflow
 * - Email notifications
 * 
 * LOAD ORDER: Tab module (after all core modules) - FINAL MODULE
 * DEPENDENCIES: constants.php, session.php, helpers.php, merge-tags.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// PRINT-READY HTML GENERATION
// ============================================

/**
 * Renders print-ready HTML (optimized for PDF conversion)
 * 
 * @param string $reference PDF reference
 * @return string Print-ready HTML
 */
function el_render_print_ready_html($reference) {
    $pdf_data = el_get_pdf_data($reference);
    
    if (!$pdf_data) {
        return '<p>PDF data not found or expired.</p>';
    }
    
    // Build merge data
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
    
    // Start HTML document
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engagement Letter - ' . esc_html($reference) . '</title>
    <style>
        @page {
            size: A4;
            margin: 25mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: white;
        }
        
        .container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 20mm;
        }
        
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid ' . EL_COLOR_PRIMARY . ';
            padding-bottom: 20px;
        }
        
        .header-info {
            text-align: right;
            margin-bottom: 25px;
            font-size: 11pt;
        }
        
        .client-address {
            margin-bottom: 25px;
        }
        
        h1 {
            font-size: 18pt;
            margin: 30px 0 20px 0;
            color: ' . EL_COLOR_NAVY . ';
        }
        
        h2 {
            font-size: 14pt;
            margin: 25px 0 15px 0;
            color: ' . EL_COLOR_NAVY . ';
        }
        
        h3 {
            font-size: 12pt;
            margin: 20px 0 10px 0;
        }
        
        p {
            margin: 10px 0;
            text-align: justify;
        }
        
        .service-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-left: 3px solid ' . EL_COLOR_PRIMARY . ';
            page-break-inside: avoid;
        }
        
        .totals {
            margin: 30px 0;
            padding: 20px;
            background: ' . EL_COLOR_BG_LIGHT . ';
            border-radius: 8px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .totals-total {
            border-top: 2px solid ' . EL_COLOR_PRIMARY . ';
            padding-top: 10px;
            margin-top: 10px;
            font-size: 14pt;
            font-weight: bold;
        }
        
        .signature-block {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            width: 300px;
            margin: 60px 0 10px 0;
        }
        
        .footer {
            margin-top: 40px;
            font-size: 10pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">';
    
    // Letterhead
    if (!empty($pdf_data['boilerplate']['letterhead'])) {
        $html .= '<div class="letterhead">';
        $html .= el_replace_merge_tags($pdf_data['boilerplate']['letterhead'], $merge_data);
        $html .= '</div>';
    }
    
    // Header info
    $html .= '<div class="header-info">';
    $html .= '<p><strong>Date:</strong> ' . date('F j, Y') . '</p>';
    $html .= '<p><strong>Reference:</strong> ' . esc_html($pdf_data['reference']) . '</p>';
    $html .= '</div>';
    
    // Client address
    $html .= '<div class="client-address">';
    $html .= '<p><strong>' . esc_html($merge_data['client_name']) . '</strong></p>';
    if (!empty($pdf_data['client']['street_address'])) {
        $html .= '<p>' . nl2br(esc_html(el_format_full_address($pdf_data['client']))) . '</p>';
    }
    $html .= '</div>';
    
    // Opening
    if (!empty($pdf_data['boilerplate']['opening_left'])) {
        $html .= '<div class="opening">';
        $html .= '<p>' . el_replace_merge_tags($pdf_data['boilerplate']['opening_left'], $merge_data) . '</p>';
        $html .= '</div>';
    }
    
    // Services
    $html .= '<h2>Scope of Services</h2>';
    
    foreach ($pdf_data['services'] as $service) {
        $html .= '<div class="service-item">';
        $html .= '<h3>' . esc_html($service['name']) . '</h3>';
        
        if (!empty($service['acf_data']['pdf_el_text'])) {
            $service_merge_data = array_merge($merge_data, [
                'service_price' => el_format_currency($service['price']),
            ]);
            $html .= '<div>' . el_replace_merge_tags($service['acf_data']['pdf_el_text'], $service_merge_data) . '</div>';
        }
        
        $html .= '<p style="font-weight: 600; margin-top: 10px;">Fee: ' . el_format_currency($service['price']) . '</p>';
        $html .= '</div>';
    }
    
    // Totals
    $html .= '<div class="totals">';
    $html .= '<div class="totals-row">';
    $html .= '<span><strong>Subtotal:</strong></span>';
    $html .= '<span><strong>' . el_format_currency($pdf_data['cart_totals']['subtotal']) . '</strong></span>';
    $html .= '</div>';
    
    if ($pdf_data['cart_totals']['tax'] > 0) {
        $html .= '<div class="totals-row">';
        $html .= '<span>Tax:</span>';
        $html .= '<span>' . el_format_currency($pdf_data['cart_totals']['tax']) . '</span>';
        $html .= '</div>';
    }
    
    $html .= '<div class="totals-row totals-total">';
    $html .= '<span>TOTAL:</span>';
    $html .= '<span>' . el_format_currency($pdf_data['cart_totals']['total']) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Footer boilerplate
    if (!empty($pdf_data['boilerplate']['footer'])) {
        $html .= '<div class="footer">';
        $html .= el_replace_merge_tags($pdf_data['boilerplate']['footer'], $merge_data);
        $html .= '</div>';
    }
    
    // Signature block
    $html .= '<div class="signature-block">';
    if (!empty($pdf_data['boilerplate']['signature_block'])) {
        $html .= el_replace_merge_tags($pdf_data['boilerplate']['signature_block'], $merge_data);
    } else {
        $html .= '<div class="signature-line"></div>';
        $html .= '<p><strong>Client Signature</strong></p>';
        $html .= '<p>Date: _________________</p>';
    }
    $html .= '</div>';
    
    $html .= '    </div>
</body>
</html>';
    
    return $html;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX handler: Download final PDF
 */
function el_ajax_download_final_pdf() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    
    if (!$reference) {
        $reference = el_get_session(EL_SESSION_PDF_REF);
    }
    
    if (!$reference) {
        wp_send_json_error(['message' => 'No PDF reference provided']);
    }
    
    // Generate download URL
    $download_url = add_query_arg([
        'action' => 'el_download_pdf',
        'reference' => $reference,
        'nonce' => wp_create_nonce('el_download_' . $reference),
    ], admin_url('admin-ajax.php'));
    
    wp_send_json_success([
        'download_url' => $download_url,
        'reference' => $reference,
    ]);
}
add_action('wp_ajax_' . EL_AJAX_DOWNLOAD_PDF, 'el_ajax_download_final_pdf');

/**
 * Direct download handler
 */
function el_handle_pdf_download() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'el_download_pdf') {
        return;
    }
    
    $reference = sanitize_text_field($_GET['reference'] ?? '');
    $nonce = sanitize_text_field($_GET['nonce'] ?? '');
    
    if (!$reference || !wp_verify_nonce($nonce, 'el_download_' . $reference)) {
        wp_die('Invalid download request', 'Unauthorised', ['response' => 403]);
    }
    
    // Get print-ready HTML
    $html = el_render_print_ready_html($reference);
    
    // Set headers for download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="engagement-letter-' . $reference . '.html"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    echo $html;
    exit;
}
add_action('init', 'el_handle_pdf_download');

/**
 * AJAX handler: Mark engagement as completed
 */
function el_ajax_complete_engagement() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = el_get_current_engagement_id();
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'No active engagement']);
    }
    
    // Update status
    el_set_meta($engagement_id, 'status', EL_STATUS_COMPLETED);
    el_set_meta($engagement_id, 'completed_date', current_time('mysql'));
    
    wp_send_json_success([
        'message' => 'Engagement letter completed',
        'engagement_id' => $engagement_id,
    ]);
}
add_action('wp_ajax_el_complete_engagement', 'el_ajax_complete_engagement');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: PDF export interface
 * 
 * Usage: [el_pdf_export]
 */
function el_pdf_export_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to export PDF.</p>';
    }
    
    $reference = el_get_session(EL_SESSION_PDF_REF);
    
    if (!$reference) {
        return '<div class="el-no-pdf" style="
            text-align: center;
            padding: 60px 40px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
        ">
            <p style="font-size: 16px; color: #6b7280;">No PDF preview available. Please generate a preview first.</p>
            <button type="button" class="el-back-to-preview" style="
                background: ' . EL_COLOR_PRIMARY . ';
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                cursor: pointer;
                margin-top: 20px;
            ">← Back to Preview</button>
        </div>';
    }
    
    $output = '<div class="el-pdf-export-wrapper">';
    
    // Success message
    $output .= '<div class="el-success-message" style="
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 30px;
        border-radius: 16px;
        text-align: center;
        margin-bottom: 30px;
    ">';
    $output .= '<div style="font-size: 64px; margin-bottom: 20px;">✓</div>';
    $output .= '<h2 style="margin: 0 0 10px 0; font-size: 24px;">Engagement Letter Ready!</h2>';
    $output .= '<p style="margin: 0; font-size: 14px; opacity: 0.9;">Your document has been generated and is ready for download.</p>';
    $output .= '</div>';
    
    // Export options
    $output .= '<div class="el-export-options" style="
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    ">';
    
    $output .= '<h3 style="margin: 0 0 20px 0; font-size: 20px; color: ' . EL_COLOR_NAVY . ';">Download Options</h3>';
    
    // Download button
    $output .= '<button type="button" id="elDownloadPdfButton" data-reference="' . esc_attr($reference) . '" style="
        width: 100%;
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-bottom: 15px;
    ">📄 Download HTML for Print</button>';
    
    $output .= '<p style="font-size: 13px; color: #6b7280; margin-bottom: 20px;">Download the HTML file and open in your browser, then use Print to PDF (Ctrl+P or Cmd+P)</p>';
    
    // Email options (if needed)
    $output .= '<div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb;">';
    $output .= '<h4 style="margin: 0 0 15px 0;">Next Steps</h4>';
    $output .= '<ul style="list-style: none; padding: 0;">';
    $output .= '<li style="padding: 8px 0;">✓ Download the document</li>';
    $output .= '<li style="padding: 8px 0;">✓ Print to PDF using your browser</li>';
    $output .= '<li style="padding: 8px 0;">✓ Send to client for signature</li>';
    $output .= '</ul>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    // Reference info
    $output .= '<div style="margin-top: 20px; text-align: center; font-size: 13px; color: #6b7280;">';
    $output .= '<p>Reference: <strong>' . esc_html($reference) . '</strong></p>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_pdf_export', 'el_pdf_export_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

/**
 * Enqueues Tab 5 JavaScript
 */
function el_enqueue_tab5_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Download PDF button
            $('#elDownloadPdfButton').on('click', function() {
                var reference = $(this).data('reference');
                var button = $(this);
                
                button.prop('disabled', true).text('Preparing download...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_DOWNLOAD_PDF . "',
                        nonce: elAjax.nonce,
                        reference: reference
                    },
                    success: function(response) {
                        if (response.success) {
                            // Open download URL in new window
                            window.open(response.data.download_url, '_blank');
                            
                            button.prop('disabled', false).text('📄 Download HTML for Print');
                            
                            // Show success message
                            button.after('<p style=\"color: #10b981; margin-top: 10px; font-size: 13px;\">✓ Download started! Open the file and use Print to PDF.</p>');
                            
                            setTimeout(function() {
                                button.next('p').fadeOut();
                            }, 5000);
                        } else {
                            alert('Error: ' + response.data.message);
                            button.prop('disabled', false).text('📄 Download HTML for Print');
                        }
                    }
                });
            });
            
            // Back to preview button
            $('.el-back-to-preview').on('click', function() {
                $('" . el_get_tab_selector(4) . "').click();
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab5_script');

// ============================================
// COMPLETION HOOK
// ============================================

/**
 * Triggers actions when engagement letter is completed
 * 
 * @param int $engagement_id Engagement letter post ID
 */
function el_on_engagement_completed($engagement_id) {
    // Hook for other modules to trigger on completion
    do_action('el_engagement_completed', $engagement_id);
    
    if (EL_DEBUG_MODE) {
        el_log('Engagement ' . $engagement_id . ' completed', 'info');
    }
}

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Tab 5 (PDF Export) module loaded successfully - ALL MODULES COMPLETE!', 'info');
}
