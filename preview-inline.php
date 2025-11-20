<?php
/**
 * Engagement Letter Template Functions
 * Contains: el_render_engagement_letter_html()
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render engagement letter HTML from PDF data
 * 
 * @param array $pdf_data The PDF data array
 * @return string The rendered HTML
 */
function el_render_engagement_letter_html($pdf_data) {
    // DEBUG: Check if function is called
    error_log('🔍 el_render_engagement_letter_html called');
    error_log('PDF Data: ' . print_r($pdf_data, true));
    
    // Get form data for merge tags
    $form_data = $pdf_data['form_data'] ?? [];
    
    // Load ACF boilerplate
    $letterhead_raw = function_exists('get_field') ? (get_field('boilerplate_letterhead', 'option') ?: '<h1>Studio Legale Metta</h1>') : '<h1>Studio Legale Metta</h1>';
    $top_left_raw = function_exists('get_field') ? (get_field('boilerplate_opening_tl', 'option') ?: '') : '';
    $top_right_raw = function_exists('get_field') ? (get_field('boilerplate_opening_tr_copy', 'option') ?: '') : '';
    $footer_boilerplate_raw = function_exists('get_field') ? (get_field('footer_boilerplate', 'option') ?: '') : '';
    $signature_block_raw = function_exists('get_field') ? (get_field('signature_block_template', 'option') ?: '') : '';
    $firm_footer_raw = function_exists('get_field') ? (get_field('firm_footer', 'option') ?: '') : '';

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
    
<style>
/* Scope ALL styles to paper document only */
#paper-document {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    line-height: 1.5;
    color: #000;
    background: white;
}

@page {
    size: A4 portrait;
    margin: 10mm 15mm 20mm 15mm;
}

#paper-document *,
#paper-document * {
    box-sizing: border-box;
    font-family: 'Times New Roman', Times, serif !important;
}

#paper-document h1 { font-size: 16pt; margin-bottom: 10px; }
#paper-document h2 { font-size: 14pt; margin-bottom: 8px; margin-top: 15px; }
#paper-document h3 { font-size: 12pt; margin-bottom: 6px; margin-top: 10px; }
#paper-document p { margin-bottom: 8px; }

#paper-document .letterhead {
    text-align: center;
    margin: 0 0 10mm 0;
    padding: 0 0 5mm 0;
    border-bottom: 1px solid #000;
}

#paper-document .letterhead h1 {
    font-size: 16pt;
    margin: 0;
}

#paper-document .letterhead img {
    max-height: 25mm;
    width: auto;
    display: block;
    margin: 0 auto 3mm auto;
}

#paper-document .top-columns {
    margin: 8mm 0;
    display: table;
    width: 100%;
}

#paper-document .top-left {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    font-size: 10pt;
}

#paper-document .top-right {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    text-align: right;
    font-size: 10pt;
}

#paper-document .client-section {
    margin: 8mm 0;
    padding: 5mm;
}

#paper-document .services-intro {
    margin: 8mm 0;
}

#paper-document .el-introduction {
    margin: 8mm 0 4mm 0;
    padding: 5mm;
    background: #f8f9fa;
    border-left: 4px solid #4a90e2;
    font-size: 10.5pt;
    page-break-inside: avoid;
}

#paper-document .el-introduction h3 {
    margin-top: 0;
    color: #4a90e2;
}

#paper-document .service-item {
    margin: 6mm 0;
    padding: 5mm;
    page-break-inside: avoid;
}

#paper-document .service-item.main-service {
    background: #ffffffff;
}

#paper-document .service-header {
    margin-bottom: 4mm;
    padding-bottom: 3mm;
    border-bottom: 1px solid #ccc;
}

#paper-document .service-title {
    font-weight: bold;
    font-size: 12pt;
}

#paper-document .service-subtitle {
    font-size: 10pt;
    font-style: italic;
    margin-top: 2mm;
}

#paper-document .engagement-fee {
    font-weight: bold;
    text-align: right;
    margin-top: 3mm;
}

#paper-document .service-description {
    margin: 4mm 0;
    font-size: 10.5pt;
}

#paper-document .totals-section {
    margin: 8mm 0;
    padding: 6mm;
    page-break-inside: avoid;
}

#paper-document .total-line {
    margin: 3mm 0;
    font-size: 11pt;
}

#paper-document .grand-total {
    margin-top: 4mm;
    padding-top: 4mm;
    border-top: 2px solid #000;
    font-weight: bold;
    font-size: 13pt;
}

#paper-document .footer-boilerplate {
    margin: 8mm 0;
    font-size: 10pt;
}

#paper-document .signature-section {
    margin: 8mm 0;
    page-break-inside: avoid;
}

#paper-document .signature-block {
    margin: 6mm 0;
    padding: 6mm;
    border: 1px solid #000;
}

#paper-document .signature-line {
    margin: 4mm 0;
}

#paper-document .signature-line label {
    font-weight: bold;
    display: block;
    margin-bottom: 2mm;
}

#paper-document .firm-footer {
    margin-top: 10mm;
    padding-top: 4mm;
    border-top: 1px solid #ffffffff;
    font-size: 9pt;
    text-align: center;
}

#paper-document .el-content-wrapper {
    padding: 0 15mm;
}

@media print {
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    #paper-document .el-content-wrapper {
        padding: 0 !important;
    }
}
</style>
<!-- Content wrapper with proper padding -->
<div class="el-content-wrapper">

<!-- LETTERHEAD -->
<div class="letterhead">
    <?php echo $letterhead; ?>
</div>

<!-- DATE & ADDRESS SECTION -->
<div class="top-columns">
    <div class="top-left">
        <?php echo $top_left; ?>
    </div>
    <div class="top-right">
        <?php echo $top_right; ?>
    </div>
</div>


<!-- SERVICES SECTION -->

<?php
// Process items
$is_first_item = true;
if (!empty($pdf_data['items'])) {
    foreach ($pdf_data['items'] as $item):
        $is_main = $is_first_item;
        $is_first_item = false;
        
        $service_class = $is_main ? 'service-item main-service' : 'service-item';
        
        // Calculate line total
        $line_total = floatval($item['engagement_fee'] ?? 0) * intval($item['quantity'] ?? 1);
        
        if ($is_main) {
            $main_service_fee += $line_total;
        } else {
            $optional_services_total += $line_total;
        }
        
        // GET INTRODUCTION TEXT FOR THIS PRODUCT (PAPER ONLY)
        $product_intro = '';
        if (!empty($item['product_id']) && function_exists('get_field')) {
            $product_intro_raw = get_field('el_introduction_texts', $item['product_id']);
            if ($product_intro_raw && function_exists('el_replace_merge_tags')) {
                $product_intro = el_replace_merge_tags($product_intro_raw, $form_data, $pdf_data);
            } else {
                $product_intro = $product_intro_raw;
            }
        }
?>

<?php if ($product_intro): ?>
<!-- INTRODUCTION TEXT - BEFORE THIS SERVICE -->
<div class="el-introduction">
    <?php echo wpautop($product_intro); ?>
</div>
<?php endif; ?>

<div class="<?php echo $service_class; ?> avoid-break">
    <div class="service-header">
        <div class="service-checkbox">
            <span class="checkbox <?php echo $is_main ? 'checked' : ''; ?>"></span>
        </div>
        <div class="service-title-block">
            <h3 class="service-title">
                <?php echo esc_html($item['pdf_title'] ?? $item['name'] ?? 'Service'); ?>
                <?php if ($is_main): ?>
                    <span style="color: #0066cc; font-size: 10pt;">(REQUIRED)</span>
                <?php endif; ?>
            </h3>
            <?php if (!empty($item['pdf_subtitle'])): ?>
                <p class="service-subtitle"><?php echo esc_html($item['pdf_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        <div class="service-price-block">
            <div class="engagement-fee">€<?php echo number_format($item['engagement_fee'] ?? 0, 2); ?></div>
            <div class="fee-label">Engagement Fee</div>
            <?php if (($item['quantity'] ?? 1) > 1): ?>
                <div class="fee-label">Qty: <?php echo $item['quantity']; ?></div>
                <div class="fee-label" style="font-weight: bold;">Total: €<?php echo number_format($line_total, 2); ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($item['pdf_text'])): ?>
    <div class="service-description">
        <?php echo wpautop($item['pdf_text']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($item['fee_structure'])): ?>
    <div class="fee-structure">
        <div class="fee-structure-title">Fee Structure: <?php echo esc_html($item['fee_structure']); ?></div>
    </div>
    <?php endif; ?>
</div>

<?php 
    endforeach;
}
?>

<!-- TOTALS SECTION -->
<div class="totals-section avoid-break">
    <h3>ENGAGEMENT FEES SUMMARY</h3>
    
    <div class="total-line">
        <div class="total-label"><strong>Main Service (Required):</strong></div>
        <div class="total-amount"><strong>€<?php echo number_format($main_service_fee, 2); ?></strong></div>
    </div>
    
    <?php if ($optional_services_total > 0): ?>
    <div class="total-line">
        <div class="total-label">Additional Services Selected:</div>
        <div class="total-amount">€<?php echo number_format($optional_services_total, 2); ?></div>
    </div>
    <?php endif; ?>
    
    <div class="total-line grand-total">
        <div class="total-label">TOTAL DUE TODAY:</div>
        <div class="total-amount">€<?php echo number_format($main_service_fee + $optional_services_total, 2); ?></div>
    </div>
</div>

<!-- FOOTER BOILERPLATE -->
<?php if ($footer_boilerplate): ?>
<div class="footer-boilerplate">
    <?php echo $footer_boilerplate; ?>
</div>
<?php endif; ?>

<!-- SIGNATURE SECTION -->
<div class="signature-section">
    <h2 style="font-size: 14pt; margin: 0 0 20px 0;">CLIENT ACCEPTANCE</h2>
    
    <?php if ($signature_block): ?>
        <?php echo $signature_block; ?>
    <?php else: ?>
        <div class="signature-block">
            <p style="margin: 0 0 10px 0;">By signing below, I/we accept the terms of this engagement letter.</p>
            
            <div class="signature-line">
                <label>Client Name:</label>
                <input type="text" value="">
            </div>
            
            <div class="signature-line">
                <label>Signature:</label>
                <input type="text" value="">
            </div>
            
            <div class="signature-line">
                <label>Date:</label>
                <input type="text" value="">
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- FIRM FOOTER -->
<?php if ($firm_footer): ?>
<div class="firm-footer">
    <?php echo $firm_footer; ?>
</div>
<?php endif; ?>

<!-- Internal Reference -->
<div class="firm-footer" style="font-size: 8pt;">
    Internal Reference: <?php echo esc_html($pdf_data['reference'] ?? 'N/A'); ?>
</div>

</div> 

<?php
$output = ob_get_clean();
error_log('📄 Generated HTML length: ' . strlen($output));
error_log('📄 First 200 chars: ' . substr($output, 0, 200));
return $output;
}