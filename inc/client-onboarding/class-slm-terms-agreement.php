<?php
/**
 * SLM Terms Agreement
 * 
 * Handles terms of agreement signing:
 * - Signature submission processing
 * - PDF generation with embedded signature
 * - Certificate of signing with audit trail
 * - Document storage in DMS
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Terms_Agreement {
    
    /**
     * Document reference prefix
     */
    const REF_PREFIX = 'TRM';
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Hooks initialized in main class
    }
    
    /**
     * AJAX: Submit terms signature
     */
    public static function ajax_submit_signature() {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $signature_type = isset($_POST['signature_type']) ? sanitize_text_field($_POST['signature_type']) : '';
        $signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';
        $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
        $client_info = isset($_POST['client_info']) ? $_POST['client_info'] : '{}';
        
        // Validate required fields
        if (empty($token) || empty($signature_type) || empty($signature_data) || empty($full_name)) {
            wp_send_json_error(['message' => __('Missing required fields.', 'flavor')]);
        }
        
        // Validate token
        $validation = SLM_Magic_Link::validate_token($token);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        $user_id = $validation['user_id'];
        
        // Check if already signed
        $already_signed = get_user_meta($user_id, 'slm_terms_signed', true);
        
        if ($already_signed) {
            wp_send_json_error(['message' => __('Terms have already been signed.', 'flavor')]);
        }
        
        // Parse client info
        $client_info = json_decode(stripslashes($client_info), true) ?: [];
        
        // Get IP address
        $ip_address = self::get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        // Build signing data
        $signing_data = [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $validation['email'],
            'signature_type' => $signature_type,
            'signature_data' => $signature_data,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'signed_at' => current_time('mysql'),
            'signed_at_utc' => gmdate('Y-m-d H:i:s'),
            'timezone' => $client_info['timezone'] ?? 'UTC',
            'screen_resolution' => $client_info['screenResolution'] ?? '',
            'language' => $client_info['language'] ?? '',
        ];
        
        // Generate document reference
        $reference = self::generate_reference();
        $signing_data['reference'] = $reference;
        
        // Generate PDF
        $pdf_result = self::generate_signed_pdf($signing_data);
        
        if (is_wp_error($pdf_result)) {
            wp_send_json_error(['message' => $pdf_result->get_error_message()]);
        }
        
        // Store document in DMS
        $document_id = self::store_document($user_id, $pdf_result, $signing_data);
        
        if (is_wp_error($document_id)) {
            wp_send_json_error(['message' => $document_id->get_error_message()]);
        }
        
        // Update user meta
        update_user_meta($user_id, 'slm_terms_signed', true);
        update_user_meta($user_id, 'slm_terms_signed_date', current_time('mysql'));
        update_user_meta($user_id, 'slm_terms_document_id', $document_id);
        update_user_meta($user_id, 'slm_terms_reference', $reference);
        
        // Log
        SLM_Client_Onboarding::log('Terms signed by user ' . $user_id . ' - Reference: ' . $reference);
        
        wp_send_json_success([
            'message' => __('Terms signed successfully.', 'flavor'),
            'reference' => $reference,
            'document_id' => $document_id,
        ]);
    }
    
    /**
     * Generate document reference
     */
    private static function generate_reference() {
        $year = date('Y');
        
        // Get next sequence number
        $sequence_option = 'slm_terms_sequence_' . $year;
        $sequence = (int) get_option($sequence_option, 0) + 1;
        update_option($sequence_option, $sequence);
        
        return sprintf('%s-%s-%06d', self::REF_PREFIX, $year, $sequence);
    }
/**
     * Generate signed PDF document
     */
    private static function generate_signed_pdf($signing_data) {
        // Load Gravity PDF autoloader
        $autoload_path = WP_PLUGIN_DIR . '/gravity-pdf/vendor/autoload.php';
        
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
        }
        
        // Determine which class is available (allow autoloading to work)
        if (class_exists('GFPDF_Vendor\\Mpdf\\Mpdf')) {
            $mpdf_class = 'GFPDF_Vendor\\Mpdf\\Mpdf';
        } elseif (class_exists('Mpdf\\Mpdf')) {
            $mpdf_class = 'Mpdf\\Mpdf';
        } else {
            return new WP_Error('mpdf_missing', __('PDF generation library not available.', 'flavor'));
        }
        
     try {
            // Get writable temp directory
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/slm-temp';
            
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }


            // Get Gravity PDF font directory
            $font_dir = WP_PLUGIN_DIR . '/gravity-pdf/vendor_prefixed/mpdf/mpdf/ttfonts/';
            
            
                  // Initialize mPDF
            $mpdf = new $mpdf_class([
                'mode' => 'c',
                'format' => 'A4',
                'margin_left' => 20,
                'margin_right' => 20,
                'margin_top' => 25,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 10,
                'tempDir' => $temp_dir,
                'default_font' => 'helvetica',
            ]);
            
            // Set document properties
            $mpdf->SetTitle('Terms of Agreement - ' . $signing_data['reference']);
            $mpdf->SetAuthor(get_option('slm_firm_name', 'Studio Legale Metta'));
            $mpdf->SetCreator('SLM Client Onboarding System');
            
            // Generate HTML content
            $html = self::build_pdf_html($signing_data);
            
            // Write HTML
            $mpdf->WriteHTML($html);
            
            // Get PDF content
            $pdf_content = $mpdf->Output('', 'S');
            
            // Calculate document hash
            $document_hash = hash('sha256', $pdf_content);
            
            return [
                'content' => $pdf_content,
                'hash' => $document_hash,
                'filename' => 'terms-agreement-' . $signing_data['reference'] . '.pdf',
            ];
            
        } catch (Exception $e) {
            SLM_Client_Onboarding::log('PDF generation failed: ' . $e->getMessage(), 'error');
            return new WP_Error('pdf_generation_failed', __('Failed to generate PDF document.', 'flavor'));
        }
    }

    
    /**
     * Build PDF HTML content
     */
    private static function build_pdf_html($signing_data) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        $terms_content = self::get_terms_content();
        
        // Process signature image
        $signature_html = self::get_signature_html($signing_data);
        
        // Build HTML
        $html = '
        <style>
             body {
                font-family: sans-serif;
                font-size: 11pt;
                line-height: 1.5;
                color: #333;
            }
            .header {
                text-align: center;
                border-bottom: 2px solid #1e3a5f;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .header h1 {
                color: #1e3a5f;
                font-size: 18pt;
                margin: 0 0 5px 0;
            }
            .header .reference {
                font-size: 10pt;
                color: #666;
            }
            .content {
                margin-bottom: 30px;
            }
            .content h2 {
                color: #1e3a5f;
                font-size: 14pt;
                margin-top: 20px;
                margin-bottom: 10px;
            }
            .content h3 {
                font-size: 12pt;
                margin-top: 15px;
                margin-bottom: 8px;
            }
            .content p {
                margin-bottom: 10px;
                text-align: justify;
            }
            .content ul {
                margin: 10px 0;
                padding-left: 25px;
            }
            .content li {
                margin-bottom: 5px;
            }
            .signature-block {
                margin-top: 40px;
                border-top: 2px solid #1e3a5f;
                padding-top: 20px;
            }
            .signature-block h2 {
                color: #1e3a5f;
                font-size: 14pt;
                margin-bottom: 20px;
            }
            .signature-row {
                margin-bottom: 15px;
            }
            .signature-label {
                font-weight: bold;
                color: #1e3a5f;
                margin-bottom: 5px;
            }
            .signature-image {
                border-bottom: 1px solid #333;
                padding: 10px 0;
                min-height: 60px;
            }
            .signature-image img {
                max-height: 60px;
            }
            .signature-typed {
                font-family: cursive;
                font-size: 24pt;
                color: #1e3a5f;
                border-bottom: 1px solid #333;
                padding: 10px 0;
            }
            .signature-line {
                border-bottom: 1px solid #333;
                padding: 10px 0;
                min-height: 20px;
            }
            .certificate {
                margin-top: 40px;
                background: #f5f5f5;
                border: 1px solid #ddd;
                padding: 20px;
                page-break-inside: avoid;
            }
            .certificate h2 {
                color: #1e3a5f;
                font-size: 12pt;
                margin: 0 0 15px 0;
                border-bottom: 1px solid #ccc;
                padding-bottom: 10px;
            }
            .cert-row {
                display: table;
                width: 100%;
                margin-bottom: 8px;
                font-size: 9pt;
            }
            .cert-label {
                display: table-cell;
                width: 140px;
                font-weight: bold;
                color: #666;
            }
            .cert-value {
                display: table-cell;
                color: #333;
                word-break: break-all;
            }
            .cert-hash {
                font-family: monospace;
                font-size: 8pt;
                background: #fff;
                padding: 5px;
                border: 1px solid #ddd;
                word-break: break-all;
            }
            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 8pt;
                color: #999;
                border-top: 1px solid #ddd;
                padding-top: 5px;
            }
        </style>
        
        <div class="header">
            <h1>' . esc_html($firm_name) . '</h1>
            <div class="reference">Document Reference: ' . esc_html($signing_data['reference']) . '</div>
        </div>
        
        <div class="content">
            <h2>TERMS OF AGREEMENT</h2>
            ' . wp_kses_post($terms_content) . '
        </div>
        
        <div class="signature-block">
            <h2>SIGNATURE &amp; ACCEPTANCE</h2>
            <p>I confirm that I have read, understood, and agree to the above terms.</p>
            
            <div class="signature-row">
                <div class="signature-label">Signature:</div>
                ' . $signature_html . '
            </div>
            
            <div class="signature-row">
                <div class="signature-label">Full Name:</div>
                <div class="signature-line">' . esc_html($signing_data['full_name']) . '</div>
            </div>
            
            <div class="signature-row">
                <div class="signature-label">Date:</div>
                <div class="signature-line">' . date_i18n('F j, Y \a\t g:i A', strtotime($signing_data['signed_at'])) . '</div>
            </div>
        </div>
        
        <div class="certificate">
            <h2>CERTIFICATE OF SIGNING</h2>
            
            <div class="cert-row">
                <div class="cert-label">Document ID:</div>
                <div class="cert-value">' . esc_html($signing_data['reference']) . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Signed By:</div>
                <div class="cert-value">' . esc_html($signing_data['full_name']) . ' (' . esc_html($signing_data['email']) . ')</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">IP Address:</div>
                <div class="cert-value">' . esc_html($signing_data['ip_address']) . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">User Agent:</div>
                <div class="cert-value" style="font-size: 8pt;">' . esc_html(substr($signing_data['user_agent'], 0, 150)) . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Signing Method:</div>
                <div class="cert-value">' . esc_html(ucfirst($signing_data['signature_type']) . ' Signature') . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Timestamp (Local):</div>
                <div class="cert-value">' . esc_html($signing_data['signed_at']) . ' (' . esc_html($signing_data['timezone']) . ')</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Timestamp (UTC):</div>
                <div class="cert-value">' . esc_html($signing_data['signed_at_utc']) . ' UTC</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Screen Resolution:</div>
                <div class="cert-value">' . esc_html($signing_data['screen_resolution'] ?: 'Not available') . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Browser Language:</div>
                <div class="cert-value">' . esc_html($signing_data['language'] ?: 'Not available') . '</div>
            </div>
            
            <div class="cert-row">
                <div class="cert-label">Document Hash:</div>
                <div class="cert-value"><div class="cert-hash">SHA-256: [HASH_PLACEHOLDER]</div></div>
            </div>
        </div>
        
        <div class="footer">
            This document was electronically signed via ' . esc_html($firm_name) . ' Client Portal.
            Generated: ' . date_i18n('Y-m-d H:i:s T') . '
        </div>
        ';
        
        return $html;
    }
    
    /**
     * Get signature HTML based on type
     */
    private static function get_signature_html($signing_data) {
        if ($signing_data['signature_type'] === 'draw') {
            // Drawn signature - embed as image
            $image_data = $signing_data['signature_data'];
            
            // Validate base64 image
            if (strpos($image_data, 'data:image/png;base64,') === 0) {
                return '<div class="signature-image"><img src="' . $image_data . '" /></div>';
            }
            
            return '<div class="signature-line">[Signature data invalid]</div>';
        } else {
            // Typed signature
            return '<div class="signature-typed">' . esc_html($signing_data['signature_data']) . '</div>';
        }
    }
    
    /**
     * Get terms content
     */
    private static function get_terms_content() {
        // Check for ACF field
        $terms_content = get_field('terms_agreement_content', 'option');
        
        if (!empty($terms_content)) {
            return $terms_content;
        }
        
        // Fallback placeholder
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        
        return '
        <h3>1. INTRODUCTION</h3>
        <p>These terms govern the relationship between ' . esc_html($firm_name) . ' ("the Firm") and you ("the Client"). By signing this agreement, you acknowledge and accept these terms.</p>
        
        <h3>2. SCOPE OF SERVICES</h3>
        <p>The Firm agrees to provide legal services as outlined in any engagement letter or service agreement. Services not explicitly included require separate agreement.</p>
        
        <h3>3. CLIENT OBLIGATIONS</h3>
        <p>The Client agrees to:</p>
        <ul>
            <li>Provide accurate and complete information as requested</li>
            <li>Respond to communications in a timely manner</li>
            <li>Pay invoices within the agreed payment terms</li>
            <li>Notify the Firm promptly of any relevant changes</li>
        </ul>
        
        <h3>4. FEES AND PAYMENT</h3>
        <p>Fees will be charged as agreed in writing. The Firm reserves the right to suspend services if payments are overdue.</p>
        
        <h3>5. CONFIDENTIALITY</h3>
        <p>All information shared between the Firm and Client is confidential and protected by attorney-client privilege where applicable.</p>
        
        <h3>6. DATA PROTECTION</h3>
        <p>Personal data will be processed in accordance with applicable data protection laws, including GDPR where applicable.</p>
        
        <h3>7. LIMITATION OF LIABILITY</h3>
        <p>The Firm\'s liability is limited to the extent permitted by law and professional regulations.</p>
        
        <h3>8. TERMINATION</h3>
        <p>Either party may terminate the engagement with written notice, subject to any ongoing legal obligations.</p>
        
        <h3>9. GOVERNING LAW</h3>
        <p>This agreement is governed by Italian law and subject to the exclusive jurisdiction of Italian courts.</p>
        ';
    }
    
    /**
     * Store document in DMS
     */
    private static function store_document($user_id, $pdf_result, $signing_data) {
        // Update PDF with actual hash
        $pdf_content = str_replace('[HASH_PLACEHOLDER]', $pdf_result['hash'], $pdf_result['content']);
        
        // Use document storage class if available
        if (class_exists('SLM_Document_Storage')) {
            return SLM_Document_Storage::store_document([
                'user_id' => $user_id,
                'document_type' => SLM_TERMS_DOC_TYPE,
                'file_name' => $pdf_result['filename'],
                'content' => $pdf_content,
                'mime_type' => 'application/pdf',
                'is_signed' => true,
                'signed_by' => $user_id,
                'signed_at' => $signing_data['signed_at'],
                'signing_ip' => $signing_data['ip_address'],
                'signing_user_agent' => $signing_data['user_agent'],
                'signing_method' => $signing_data['signature_type'],
                'file_hash' => $pdf_result['hash'],
                'metadata' => [
                    'reference' => $signing_data['reference'],
                    'full_name' => $signing_data['full_name'],
                    'email' => $signing_data['email'],
                    'timezone' => $signing_data['timezone'],
                ],
            ]);
        }
        
        // Fallback: Store directly
        return self::store_document_fallback($user_id, $pdf_content, $pdf_result, $signing_data);
    }
    
    /**
     * Fallback document storage
     */
    private static function store_document_fallback($user_id, $pdf_content, $pdf_result, $signing_data) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return new WP_Error('db_error', __('Documents table not found.', 'flavor'));
        }
        
        // Create storage path
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/private/slm-documents/terms';
        
        if (!file_exists($base_path)) {
            wp_mkdir_p($base_path);
        }
        
        // Generate unique filename
        $file_uuid = wp_generate_uuid4();
        $file_path = $base_path . '/' . $file_uuid . '.pdf';
        
        // For now, store unencrypted (encryption would be added via SLM_Document_Storage)
        $written = file_put_contents($file_path, $pdf_content);
        
        if ($written === false) {
            return new WP_Error('storage_error', __('Failed to store document.', 'flavor'));
        }
        
        // Insert database record
        $inserted = $wpdb->insert(
            $table,
            [
                'user_id' => $user_id,
                'document_type' => SLM_TERMS_DOC_TYPE,
                'file_name' => $file_uuid . '.pdf',
                'original_name' => $pdf_result['filename'],
                'file_path' => $file_path,
                'file_size' => strlen($pdf_content),
                'mime_type' => 'application/pdf',
                'encryption_iv' => '', // Not encrypted in fallback
                'encryption_tag' => '',
                'file_hash' => $pdf_result['hash'],
                'is_signed' => 1,
                'signed_by' => $user_id,
                'signed_at' => $signing_data['signed_at'],
                'signing_ip' => $signing_data['ip_address'],
                'signing_user_agent' => $signing_data['user_agent'],
                'signing_method' => $signing_data['signature_type'],
                'created_by' => $user_id,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        if (!$inserted) {
            // Clean up file
            @unlink($file_path);
            return new WP_Error('db_error', __('Failed to record document.', 'flavor'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Get signed document for user
     */
    public static function get_signed_document($user_id) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return null;
        }
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table 
             WHERE user_id = %d 
             AND document_type = %s 
             AND is_signed = 1 
             ORDER BY created_at DESC 
             LIMIT 1",
            $user_id,
            SLM_TERMS_DOC_TYPE
        ));
    }
    
    /**
     * Generate viewing token for document
     */
    public static function generate_view_token($document_id, $user_id) {
        $token_data = [
            'document_id' => $document_id,
            'user_id' => $user_id,
            'expires' => time() + HOUR_IN_SECONDS,
            'nonce' => wp_generate_password(16, false),
        ];
        
        $token = base64_encode(json_encode($token_data));
        $signature = hash_hmac('sha256', $token, wp_salt('auth'));
        
        return $token . '.' . $signature;
    }
    
    /**
     * Validate viewing token
     */
    public static function validate_view_token($token_string) {
        $parts = explode('.', $token_string);
        
        if (count($parts) !== 2) {
            return false;
        }
        
        list($token, $signature) = $parts;
        
        $expected_signature = hash_hmac('sha256', $token, wp_salt('auth'));
        
        if (!hash_equals($expected_signature, $signature)) {
            return false;
        }
        
        $token_data = json_decode(base64_decode($token), true);
        
        if (!$token_data || $token_data['expires'] < time()) {
            return false;
        }
        
        return $token_data;
    }
}
