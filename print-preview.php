<?php
/**
 * Print Preview Renderer for Engagement Letters
 * Generates print-ready HTML using PDF-specific ACF fields
 * Used by Tab 5 for paper version with checkboxes and manual signatures
 * ALL STYLES ARE INLINE TO AVOID PAGED.JS CSS CONFLICTS
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render engagement letter print HTML from PDF data
 * Uses print-specific fields: client_fillable_pdf_text, service_plan_table, pdf_el_text
 * 
 * @param array $pdf_data The PDF data array
 * @return string The rendered HTML for print
 */
function el_render_print_preview_html($pdf_data) {
    // Get form data for merge tags
    $form_data = $pdf_data['form_data'] ?? [];
    
    // Load ACF boilerplate from Options page
    $letterhead_raw = get_field('boilerplate_letterhead', 'option') ?: '<h1>Studio Legale Metta</h1>';
    $top_left_raw = get_field('boilerplate_opening_tl', 'option') ?: '';
    $top_right_raw = get_field('boilerplate_opening_tr_copy', 'option') ?: '';
    $footer_boilerplate_raw = get_field('footer_boilerplate', 'option') ?: '';
    $signature_block_raw = get_field('signature_block_template', 'option') ?: '';
    $firm_footer_raw = get_field('firm_footer', 'option') ?: '';

    // Apply merge tag replacement
    $letterhead = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($letterhead_raw, $form_data, $pdf_data) : $letterhead_raw;
    $top_left = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($top_left_raw, $form_data, $pdf_data) : $top_left_raw;
    $top_right = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($top_right_raw, $form_data, $pdf_data) : $top_right_raw;
    $footer_boilerplate = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($footer_boilerplate_raw, $form_data, $pdf_data) : $footer_boilerplate_raw;
    $signature_block = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($signature_block_raw, $form_data, $pdf_data) : $signature_block_raw;
    $firm_footer = function_exists('el_replace_merge_tags') ? el_replace_merge_tags($firm_footer_raw, $form_data, $pdf_data) : $firm_footer_raw;

    // Calculate totals
    $main_service_fee = 0;
    $optional_services_total = 0;
    
    // Start output buffering
    ob_start();
    ?>
    
<div style="max-width: 210mm; margin: 0 auto; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000;">
        
    <!-- LETTERHEAD -->
    <div style="text-align: center; margin-bottom: 15mm; padding-bottom: 10mm; border-bottom: 2px solid #000;">
        <?php echo wp_kses_post($letterhead); ?>
    </div>

    <!-- HEADER COLUMNS -->
    <div style="display: table; width: 100%; margin-bottom: 10mm;">
        <div style="display: table-cell; width: 50%; vertical-align: top; font-size: 11pt;">
            <?php echo wp_kses_post($top_left); ?>
        </div>
        <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right; font-size: 11pt;">
            <?php echo wp_kses_post($top_right); ?>
        </div>
    </div>

    <?php
    // Check if first product is EL template and has introduction text
    $first_item = $pdf_data['items'][0] ?? null;
    $el_introduction = '';
    
    if ($first_item && !empty($first_item['product_id'])) {
        $el_introduction_raw = get_field('el_introduction_texts', $first_item['product_id']);
        if ($el_introduction_raw) {
            $el_introduction = function_exists('el_replace_merge_tags') 
                ? el_replace_merge_tags($el_introduction_raw, $form_data, $pdf_data) 
                : $el_introduction_raw;
        }
    }
    ?>

    <?php if ($el_introduction): ?>
    <!-- INTRODUCTION FROM EL TEMPLATE -->
    <div style="margin: 10mm 0; padding: 8mm; background: #f5f5f5; border-left: 4mm solid #333;">
        <?php echo wp_kses_post($el_introduction); ?>
    </div>
    <?php endif; ?>

    <!-- PRODUCTS / SERVICES -->
    <?php
    $is_first_item = true;
    foreach ($pdf_data['items'] as $item):
        $is_main = $is_first_item;
        $is_first_item = false;
        
        // Get PRINT-SPECIFIC fields for this product
        $product_id = $item['product_id'] ?? 0;
        
        $client_fillable_text_raw = get_field('client_fillable_pdf_text', $product_id) ?: '';
        $service_plan_table_raw = get_field('service_plan_table', $product_id) ?: '';
        $pdf_el_text_raw = get_field('pdf_el_text', $product_id) ?: '';
        $pdf_footer_notes_raw = get_field('pdf_footer_notes', $product_id) ?: '';
        
        // Apply merge tags
        $client_fillable_text = function_exists('el_replace_merge_tags') 
            ? el_replace_merge_tags($client_fillable_text_raw, $form_data, $pdf_data) 
            : $client_fillable_text_raw;
            
        $service_plan_table = function_exists('el_replace_merge_tags') 
            ? el_replace_merge_tags($service_plan_table_raw, $form_data, $pdf_data) 
            : $service_plan_table_raw;
            
        $pdf_el_text = function_exists('el_replace_merge_tags') 
            ? el_replace_merge_tags($pdf_el_text_raw, $form_data, $pdf_data) 
            : $pdf_el_text_raw;
            
        $pdf_footer_notes = function_exists('el_replace_merge_tags') 
            ? el_replace_merge_tags($pdf_footer_notes_raw, $form_data, $pdf_data) 
            : $pdf_footer_notes_raw;
        
        // Calculate totals
        $line_total = floatval($item['engagement_fee'] ?? 0) * intval($item['quantity'] ?? 1);
        if ($is_main) {
            $main_service_fee += $line_total;
        } else {
            $optional_services_total += $line_total;
        }
    ?>

    <div style="margin: 10mm 0; page-break-inside: avoid;">
        <h2 style="font-size: 14pt; margin: 0 0 5mm 0; font-weight: bold;">
            <?php echo esc_html($item['name'] ?? 'Service'); ?><?php if ($is_main): ?> (REQUIRED)<?php endif; ?>
        </h2>
        
        <?php if ($client_fillable_text): ?>
        <div style="margin: 5mm 0;">
            <?php echo wp_kses_post($client_fillable_text); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($service_plan_table): ?>
        <div style="margin: 5mm 0;">
            <?php echo wp_kses_post($service_plan_table); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($pdf_el_text): ?>
        <div style="margin: 5mm 0;">
            <?php echo wp_kses_post($pdf_el_text); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($pdf_footer_notes): ?>
        <div style="margin-top: 5mm; font-size: 10pt; color: #666;">
            <?php echo wp_kses_post($pdf_footer_notes); ?>
        </div>
        <?php endif; ?>
        
        <p style="margin-top: 5mm;"><strong>Engagement Fee: €<?php echo number_format($line_total, 2); ?></strong></p>
    </div>

    <?php endforeach; ?>

    <!-- TOTALS SUMMARY -->
    <div style="margin: 10mm 0; padding: 8mm; border: 2px solid #000; page-break-inside: avoid;">
        <h3 style="margin: 0 0 5mm 0; font-size: 13pt; font-weight: bold;">Engagement Fees Summary</h3>
        <p style="margin: 3mm 0;"><strong>Main Service:</strong> €<?php echo number_format($main_service_fee, 2); ?></p>
        <?php if ($optional_services_total > 0): ?>
        <p style="margin: 3mm 0;"><strong>Additional Services:</strong> €<?php echo number_format($optional_services_total, 2); ?></p>
        <?php endif; ?>
        <p style="margin-top: 5mm; font-size: 14pt;"><strong>TOTAL DUE TODAY: €<?php echo number_format($main_service_fee + $optional_services_total, 2); ?></strong></p>
    </div>

    <!-- FOOTER BOILERPLATE -->
    <?php if ($footer_boilerplate): ?>
    <div style="margin: 10mm 0; font-size: 11pt;">
        <?php echo wp_kses_post($footer_boilerplate); ?>
    </div>
    <?php endif; ?>

    <!-- SIGNATURE SECTION -->
    <div style="margin: 15mm 0; page-break-inside: avoid;">
        <h2 style="font-size: 14pt; margin: 0 0 10mm 0; font-weight: bold;">CLIENT ACCEPTANCE</h2>
        <?php if ($signature_block): ?>
            <?php echo wp_kses_post($signature_block); ?>
        <?php else: ?>
            <div style="padding: 10mm; border: 2px solid #000;">
                <p style="margin: 0 0 10mm 0;">By signing below, I/we accept the terms of this engagement letter.</p>
                
                <div style="margin: 8mm 0;">
                    <strong style="display: block; margin-bottom: 2mm;">Client Name:</strong>
                    _____________________________________________________
                </div>
                
                <div style="margin: 8mm 0;">
                    <strong style="display: block; margin-bottom: 2mm;">Signature:</strong>
                    _____________________________________________________
                </div>
                
                <div style="margin: 8mm 0;">
                    <strong style="display: block; margin-bottom: 2mm;">Date:</strong>
                    _____________________________________________________
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FIRM FOOTER -->
    <?php if ($firm_footer): ?>
    <div style="margin-top: 10mm; padding-top: 5mm; border-top: 1px solid #000; text-align: center; font-size: 10pt; color: #666;">
        <?php echo wp_kses_post($firm_footer); ?>
    </div>
    <?php endif; ?>

    <!-- Internal Reference -->
    <div style="text-align: center; font-size: 9pt; color: #999; margin-top: 5mm;">
        Internal Reference: <?php echo esc_html($pdf_data['reference'] ?? 'N/A'); ?>
    </div>

</div>

    <?php
    return ob_get_clean();
}