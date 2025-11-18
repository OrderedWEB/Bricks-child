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

echo el_render_engagement_letter_html($pdf_data);
</body>
</html>