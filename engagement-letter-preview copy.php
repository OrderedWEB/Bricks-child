<?php
/**
 * Template Name: Engagement Letter Preview
 * Description: Printable engagement letter with tick boxes for client to fill out
 */

// Get reference from URL
$reference = sanitize_text_field($_GET['ref'] ?? '');

if (!$reference) {
    wp_die('Invalid reference - please regenerate from wizard');
}

// Get PDF data from transient
$pdf_data = get_transient('el_pdf_data_' . $reference);

if (!$pdf_data) {
    wp_die('Preview expired or not found. Please regenerate from the engagement letter wizard.');
}

// Get boilerplate content from ACF options (using your existing field names)
$letterhead = get_field('boilerplate_letterhead', 'option') ?: '<h1>Studio Legale Metta</h1>';
$top_left = get_field('boilerplate_opening_tl', 'option') ?: '';
$top_right = get_field('boilerplate_opening_tr_copy', 'option') ?: '';
$footer_boilerplate = get_field('footer_boilerplate', 'option') ?: '';
$signature_block = get_field('signature_block_template', 'option') ?: '';
$firm_footer = get_field('firm_footer', 'option') ?: '';

// Replace placeholders in boilerplate
$top_right = str_replace(['{DATE}', '{date}'], $pdf_data['date'], $top_right);
$top_right = str_replace(['{REFERENCE}', '{reference}'], $pdf_data['reference'], $top_right);
$top_right = str_replace(['{CLIENT_NAME}', '{client_name}'], $pdf_data['client']['name'], $top_right);

// Calculate totals
$main_service_fee = 0;
$optional_services_total = 0;
$is_first = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engagement Letter - <?php echo esc_html($pdf_data['reference']); ?></title>
    <style>
        @media print {
            @page {
                margin: 2cm;
                size: A4 portrait;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .avoid-break {
                page-break-inside: avoid;
            }
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }
        
        /* HEADER SECTION */
        .letterhead {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }
        
        .letterhead h1 {
            margin: 0 0 10px 0;
            font-size: 20pt;
            font-weight: bold;
        }
        
        .top-columns {
            display: table;
            width: 100%;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .top-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .top-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
            padding-left: 15px;
        }
        
        /* CLIENT INFO */
        .client-section {
            margin: 25px 0;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .client-section h2 {
            margin: 0 0 10px 0;
            font-size: 14pt;
            font-weight: bold;
        }
        
        /* SERVICE SECTIONS */
        .services-intro {
            margin: 25px 0;
            font-size: 11pt;
            line-height: 1.6;
        }
        
        .service-item {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #333;
            background: #fff;
            page-break-inside: avoid;
        }
        
        .service-item.main-service {
            background: #e8f4fd;
            border-color: #0066cc;
        }
        
        .service-header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 1px solid #999;
            padding-bottom: 10px;
        }
        
        .service-checkbox {
            display: table-cell;
            width: 40px;
            vertical-align: middle;
        }
        
        .checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            display: inline-block;
            vertical-align: middle;
            position: relative;
        }
        
        .checkbox.checked::after {
            content: "✓";
            position: absolute;
            top: -3px;
            left: 2px;
            font-size: 18pt;
            font-weight: bold;
        }
        
        .service-title-block {
            display: table-cell;
            vertical-align: middle;
        }
        
        .service-title {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
        }
        
        .service-subtitle {
            font-size: 10pt;
            color: #666;
            margin: 3px 0 0 0;
        }
        
        .service-price-block {
            display: table-cell;
            width: 140px;
            text-align: right;
            vertical-align: middle;
        }
        
        .engagement-fee {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
        }
        
        .fee-label {
            font-size: 9pt;
            color: #666;
        }
        
        .service-description {
            margin: 15px 0;
            font-size: 11pt;
            line-height: 1.5;
        }
        
        .service-description p {
            margin: 8px 0;
        }
        
        .fee-structure {
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-left: 3px solid #666;
            font-size: 10pt;
        }
        
        .fee-structure-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        /* TOTALS SECTION */
        .totals-section {
            margin: 30px 0;
            padding: 20px;
            border: 3px double #000;
            background: #f9f9f9;
        }
        
        .totals-section h3 {
            margin: 0 0 15px 0;
            font-size: 14pt;
            text-align: center;
        }
        
        .total-line {
            display: table;
            width: 100%;
            margin: 8px 0;
            font-size: 11pt;
        }
        
        .total-label {
            display: table-cell;
            padding: 5px 0;
        }
        
        .total-amount {
            display: table-cell;
            text-align: right;
            padding: 5px 0;
            width: 140px;
        }
        
        .grand-total {
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14pt;
            font-weight: bold;
        }
        
        .manual-calc {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border: 2px dashed #666;
        }
        
        .calc-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }
        
        .calc-label {
            display: table-cell;
            width: 60%;
        }
        
        .calc-input {
            display: table-cell;
            width: 40%;
            text-align: right;
        }
        
        .calc-input input {
            width: 120px;
            border: none;
            border-bottom: 1px solid #000;
            text-align: right;
            font-size: 11pt;
        }
        
        /* FOOTER SECTIONS */
        .footer-boilerplate {
            margin: 30px 0;
            font-size: 10pt;
            line-height: 1.5;
        }
        
        .signature-section {
            margin: 40px 0;
            page-break-inside: avoid;
        }
        
        .signature-block {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #000;
        }
        
        .signature-line {
            margin: 15px 0;
        }
        
        .signature-line label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .signature-line input {
            width: 100%;
            border: none;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            font-size: 11pt;
        }
        
        .firm-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
            line-height: 1.4;
        }
        
        /* INSTRUCTIONS BOX */
        .instructions {
            margin: 20px 0;
            padding: 15px;
            background: #fffbf0;
            border: 2px solid #ffcc00;
            font-size: 10pt;
        }
        
        .instructions strong {
            display: block;
            margin-bottom: 8px;
            font-size: 11pt;
        }
        
        /* PRINT BUTTON */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .print-button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14pt;
            cursor: pointer;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0052a3;
        }
        
        /* Special Instructions */
        .special-instructions {
            margin: 25px 0;
            padding: 15px;
            background: #f0f9ff;
            border-left: 4px solid #0066cc;
        }
        
        .special-instructions h3 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #0066cc;
        }
    </style>
</head>
<body>

<!-- PRINT BUTTON -->
<div class="no-print">
    <button class="print-button" onclick="window.print()">🖨️ Print Engagement Letter</button>
</div>

<!-- HEADER SECTION -->
<div class="letterhead">
    <?php echo $letterhead; ?>
</div>

<div class="top-columns">
    <div class="top-left">
        <?php echo $top_left; ?>
    </div>
    <div class="top-right">
        <?php echo $top_right; ?>
    </div>
</div>

<!-- CLIENT INFO -->
<div class="client-section">
    <h2>CLIENT DETAILS</h2>
    <p>
        <strong>Name:</strong> <?php echo esc_html($pdf_data['client']['name']); ?><br>
        <strong>Email:</strong> <?php echo esc_html($pdf_data['client']['email']); ?>
    </p>
</div>

<!-- INSTRUCTIONS -->
<div class="instructions">
    <strong>📋 INSTRUCTIONS FOR CLIENT:</strong>
    <ol style="margin: 5px 0 0 20px; padding: 0;">
        <li>Review all services listed below</li>
        <li>The main service (highlighted in blue) is pre-selected and required</li>
        <li>Tick additional services you wish to include</li>
        <li>Calculate your total in the box at the bottom</li>
        <li>Sign and return this letter with payment</li>
    </ol>
</div>

<!-- SERVICES SECTION -->
<div class="services-intro">
    <h2 style="font-size: 14pt; margin: 0 0 10px 0;">PROPOSED LEGAL SERVICES</h2>
    <p>We propose to provide the following legal services. Please review each service and indicate your selections by ticking the appropriate boxes.</p>
</div>

<?php
// MAIN SERVICE (First item - always pre-ticked)
$is_first_item = true;
foreach ($pdf_data['items'] as $item):
    $is_main = $is_first_item;
    $is_first_item = false;
    
    $service_class = $is_main ? 'service-item main-service' : 'service-item';
    
    // Calculate line total
    $line_total = floatval($item['engagement_fee']) * intval($item['quantity']);
    
    if ($is_main) {
        $main_service_fee += $line_total;
    } else {
        $optional_services_total += $line_total;
    }
?>

<div class="<?php echo $service_class; ?> avoid-break">
    <div class="service-header">
        <div class="service-checkbox">
            <span class="checkbox <?php echo $is_main ? 'checked' : ''; ?>"></span>
        </div>
        <div class="service-title-block">
            <h3 class="service-title">
                <?php echo esc_html($item['pdf_title'] ?: $item['name']); ?>
                <?php if ($is_main): ?>
                    <span style="color: #0066cc; font-size: 10pt;">(REQUIRED)</span>
                <?php endif; ?>
            </h3>
            <?php if ($item['pdf_subtitle']): ?>
                <p class="service-subtitle"><?php echo esc_html($item['pdf_subtitle']); ?></p>
            <?php endif; ?>
        </div>
        <div class="service-price-block">
            <div class="engagement-fee">€<?php echo number_format($item['engagement_fee'], 2); ?></div>
            <div class="fee-label">Engagement Fee</div>
            <?php if ($item['quantity'] > 1): ?>
                <div class="fee-label">Qty: <?php echo $item['quantity']; ?></div>
                <div class="fee-label" style="font-weight: bold;">Total: €<?php echo number_format($line_total, 2); ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($item['pdf_text']): ?>
    <div class="service-description">
        <?php echo wpautop($item['pdf_text']); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($item['fee_structure']): ?>
    <div class="fee-structure">
        <div class="fee-structure-title">Fee Structure: <?php echo esc_html($item['fee_structure']); ?></div>
        <p style="margin: 5px 0 0 0;">
            <?php
            // Display fee structure details based on type
            if ($item['fee_structure'] == 'Fixed Fee') {
                echo 'Fixed fee of €' . number_format($item['engagement_fee'], 2) . ' covers all services described above.';
            } elseif ($item['fee_structure'] == 'Per Hour') {
                echo 'Hourly billing. Engagement fee is due today; additional hours billed as incurred.';
            } elseif ($item['fee_structure'] == 'Capped') {
                echo 'Percentage-based fee with cap. Details provided in full terms.';
            }
            ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<!-- SPECIAL INSTRUCTIONS (if any) -->
<?php if (!empty($pdf_data['client_instructions'])): ?>
<div class="special-instructions">
    <h3>Special Instructions</h3>
    <?php echo wpautop($pdf_data['client_instructions']); ?>
</div>
<?php endif; ?>

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
    
    <!-- CLIENT CALCULATION BOX -->
    <div class="manual-calc">
        <p style="margin: 0 0 10px 0; font-weight: bold;">Client's Calculation (if selecting optional services):</p>
        <div class="calc-row">
            <div class="calc-label">Main Service Fee:</div>
            <div class="calc-input">€ <input type="text" value="<?php echo number_format($main_service_fee, 2); ?>" readonly style="font-weight: bold;"></div>
        </div>
        <div class="calc-row">
            <div class="calc-label">+ Optional Services (write amount):</div>
            <div class="calc-input">€ <input type="text" value="___________"></div>
        </div>
        <div class="calc-row" style="border-top: 2px solid #000; margin-top: 10px; padding-top: 10px;">
            <div class="calc-label"><strong>= TOTAL TO PAY:</strong></div>
            <div class="calc-input"><strong>€ <input type="text" value="___________"></strong></div>
        </div>
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
        <!-- Default signature block if none configured -->
        <div class="signature-block">
            <p style="margin: 0 0 10px 0;">By signing below, I/we accept the terms of this engagement letter and agree to the fees outlined above.</p>
            
            <div class="signature-line">
                <label>Client Name (Print):</label>
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

<!-- Internal Reference (not for client eyes) -->
<div class="firm-footer" style="font-size: 8pt;">
    Internal Reference: <?php echo esc_html($pdf_data['reference']); ?>
    <?php if (!empty($pdf_data['internal_notes'])): ?>
        <br>Internal Notes: <?php echo esc_html($pdf_data['internal_notes']); ?>
    <?php endif; ?>
</div>

</body>
</html>