<?php
/**
 * Paginated A4 Secure Viewer for Engagement Letters
 * 
 * Renders PDFs one page at a time using PDF.js with running headers,
 * footers, page navigation, and per-page signature lines.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * FEATURES:
 *   - Single A4 page display at a time
 *   - Running header with firm letterhead
 *   - Running footer with page X of Y, reference, date
 *   - Previous/Next page navigation
 *   - Per-page client signature line (paper-only mode)
 *   - PDF.js canvas rendering (no native PDF viewer)
 *   - Watermark overlay with viewer email
 *   - Session timeout and access logging
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Paginated_Viewer {
    
    /**
     * PDF.js CDN URL
     */
    const PDFJS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174';
    
    /**
     * Session timeout in seconds (30 minutes)
     */
    const SESSION_TIMEOUT = 1800;
    
    /**
     * Initialize viewer hooks
     */
    public static function init() {
        add_action('init', [__CLASS__, 'handle_viewer_request'], 1);
        add_action('wp_ajax_el_paginated_page', [__CLASS__, 'ajax_get_page']);
        add_action('wp_ajax_nopriv_el_paginated_page', [__CLASS__, 'ajax_get_page']);
    }
    
    /**
     * Handle viewer request
     */
    public static function handle_viewer_request() {
        if (!isset($_GET['el_view']) || $_GET['el_view'] !== '1') {
            return;
        }
        
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        
        if (empty($token)) {
            self::render_error('Missing access token.');
            exit;
        }
        
        // Verify token using existing secure viewer
        $token_data = EL_Secure_Viewer::verify_token($token);
        
        if (!$token_data) {
            self::render_error('Invalid or expired access link. Please request a new viewing link.');
            exit;
        }
        
        // Get document metadata
        $file_id = $token_data['file_id'];
        $email = $token_data['email'];
        $permissions = $token_data['permissions'];
        
        // Get PDF content and convert to base64 for PDF.js
        $pdf_content = EL_PDF_Encryption::retrieve_pdf($file_id);
        
        if ($pdf_content === false) {
            self::render_error('Document not found or has been removed.');
            exit;
        }
        
        // Get document metadata from storage
        $doc_metadata = self::get_document_metadata($file_id);
        
        // Start session
        if (!session_id()) {
            session_start();
        }
        $_SESSION['el_viewer_token'] = $token;
        $_SESSION['el_viewer_file_id'] = $file_id;
        $_SESSION['el_viewer_last_activity'] = time();
        
        // Log access
        self::log_access($file_id, $email, 'view');
        
        // Render paginated viewer
        self::render_viewer($pdf_content, $token_data, $doc_metadata);
        exit;
    }
    
    /**
     * Get document metadata from database
     */
    private static function get_document_metadata($file_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE file_id = %s",
            $file_id
        ), ARRAY_A);
        
        $metadata = [
            'reference' => 'N/A',
            'created_at' => current_time('F j, Y'),
            'client_name' => '',
            'total_pages' => 1,
        ];
        
        if ($row && !empty($row['metadata_json'])) {
            $stored = json_decode($row['metadata_json'], true);
            $metadata['reference'] = $row['reference'] ?? $metadata['reference'];
            $metadata['created_at'] = date('F j, Y', strtotime($row['created_at']));
            $metadata['total_pages'] = $stored['total_pages'] ?? 1;
            $metadata['client_name'] = $stored['client_name'] ?? '';
        }
        
        return $metadata;
    }
    
    /**
     * Log access
     */
    private static function log_access($file_id, $email, $action) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_access_log';
        
        $wpdb->insert(
            $table_name,
            [
                'file_id' => $file_id,
                'action' => $action,
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'accessed_at' => current_time('mysql'),
                'details' => 'email:' . $email,
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }
    
    /**
     * Get client IP
     */
    private static function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Render paginated viewer
     */
    private static function render_viewer($pdf_content, $token_data, $doc_metadata) {
        $permissions = $token_data['permissions'];
        $email = $token_data['email'];
        $file_id = $token_data['file_id'];
        $token = $_GET['token'];
        
        // Firm details (could be from ACF options)
        $firm_name = get_option('el_firm_name', 'Studio Legale Metta');
        $firm_address = get_option('el_firm_address', '');
        $firm_logo_url = get_option('el_firm_logo', '');
        
        // PDF as base64
        $pdf_base64 = base64_encode($pdf_content);
        
        // Watermark text
        $watermark_text = sprintf('%s | %s', $email, current_time('F j, Y g:i A'));
        
        // Session expiry timestamp
        $session_expires = time() + self::SESSION_TIMEOUT;
        
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Engagement Letter - <?php echo esc_html($doc_metadata['reference']); ?></title>
    
    <!-- PDF.js -->
    <script src="<?php echo self::PDFJS_CDN; ?>/pdf.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --header-height: 60px;
            --footer-height: 50px;
            --nav-height: 50px;
            --page-shadow: 0 4px 20px rgba(0,0,0,0.3);
            --accent-color: #1a365d;
            --accent-light: #2c5282;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }
        
        /* ===== HEADER ===== */
        .viewer-header {
            height: var(--header-height);
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-light) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .firm-logo {
            height: 36px;
            width: auto;
        }
        
        .firm-name {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
        
        .header-center {
            text-align: center;
        }
        
        .doc-title {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
        }
        
        .doc-ref {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
            margin-top: 2px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .viewer-email {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
        }
        
        .header-btn {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .header-btn:hover {
            background: rgba(255,255,255,0.25);
        }
        
        .header-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .header-btn.primary {
            background: #e53e3e;
            border-color: #e53e3e;
        }
        
        .header-btn.primary:hover {
            background: #c53030;
        }
        
        /* ===== MAIN CONTENT ===== */
        .viewer-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: calc(var(--header-height) + 20px) 20px calc(var(--footer-height) + var(--nav-height) + 20px);
            background: #16213e;
            overflow-y: auto;
        }
        
        /* ===== PAGE CONTAINER ===== */
        .page-container {
            background: #fff;
            box-shadow: var(--page-shadow);
            position: relative;
            width: 100%;
            max-width: 794px; /* A4 width at 96dpi */
        }
        
        /* Page header (running header) */
        .page-header {
            background: #f8fafc;
            border-bottom: 2px solid var(--accent-color);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header-logo {
            height: 28px;
            width: auto;
        }
        
        .page-header-firm {
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .page-header-right {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        
        /* PDF canvas area */
        .page-content {
            position: relative;
            background: #fff;
        }
        
        #pdfCanvas {
            display: block;
            width: 100%;
            height: auto;
        }
        
        /* Watermark overlay */
        .page-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 24px;
            color: rgba(0,0,0,0.04);
            white-space: nowrap;
            pointer-events: none;
            z-index: 10;
        }
        
        /* Page footer (running footer) */
        .page-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-footer-left {
            font-size: 11px;
            color: #64748b;
        }
        
        .page-footer-center {
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .page-footer-right {
            font-size: 11px;
            color: #64748b;
        }
        
        /* Per-page signature line */
        .page-signature-line {
            background: #fffbeb;
            border-top: 1px dashed #d97706;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #92400e;
        }
        
        .signature-field {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .signature-line {
            width: 180px;
            border-bottom: 1px solid #92400e;
        }
        
        .date-line {
            width: 100px;
            border-bottom: 1px solid #92400e;
        }
        
        /* ===== NAVIGATION BAR ===== */
        .viewer-nav {
            height: var(--nav-height);
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: fixed;
            bottom: var(--footer-height);
            left: 0;
            right: 0;
            z-index: 100;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .nav-btn:hover:not(:disabled) {
            background: rgba(255,255,255,0.2);
        }
        
        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .nav-btn svg {
            width: 20px;
            height: 20px;
        }
        
        .page-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .page-input {
            width: 50px;
            padding: 6px 8px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            text-align: center;
            font-size: 14px;
        }
        
        .page-input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.4);
        }
        
        .page-total {
            color: rgba(255,255,255,0.6);
        }
        
        /* ===== STATUS FOOTER ===== */
        .viewer-footer {
            height: var(--footer-height);
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
        }
        
        .footer-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .session-timer {
            font-family: monospace;
            color: rgba(255,255,255,0.7);
        }
        
        .session-warning {
            color: #f59e0b;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* ===== LOADING STATE ===== */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #1a1a2e;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .loading-overlay.hidden {
            display: none;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.1);
            border-top-color: #e53e3e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 16px;
            color: rgba(255,255,255,0.7);
        }
        
        /* ===== PRINT STYLES ===== */
        @media print {
            .viewer-header,
            .viewer-nav,
            .viewer-footer {
                display: none !important;
            }
            
            .viewer-main {
                padding: 0;
                background: white;
            }
            
            .page-container {
                box-shadow: none;
                max-width: none;
            }
            
            .page-watermark {
                color: rgba(0,0,0,0.08) !important;
            }
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 850px) {
            .page-container {
                max-width: 100%;
            }
            
            .header-center {
                display: none;
            }
            
            .viewer-email {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading document...</div>
    </div>
    
    <!-- Header -->
    <header class="viewer-header">
        <div class="header-left">
            <?php if ($firm_logo_url): ?>
            <img src="<?php echo esc_url($firm_logo_url); ?>" alt="<?php echo esc_attr($firm_name); ?>" class="firm-logo">
            <?php endif; ?>
            <span class="firm-name"><?php echo esc_html($firm_name); ?></span>
        </div>
        
        <div class="header-center">
            <div class="doc-title">Engagement Letter</div>
            <div class="doc-ref">Ref: <?php echo esc_html($doc_metadata['reference']); ?></div>
        </div>
        
        <div class="header-right">
            <span class="viewer-email"><?php echo esc_html($email); ?></span>
            
            <?php if (!empty($permissions['can_print'])): ?>
            <button class="header-btn" onclick="printDocument()" title="Print">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                Print
            </button>
            <?php endif; ?>
            
            <?php if (!empty($permissions['can_download'])): ?>
            <button class="header-btn primary" onclick="downloadDocument()" title="Download">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download
            </button>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Main content area -->
    <main class="viewer-main">
        <div class="page-container">
            <!-- Running header -->
            <div class="page-header">
                <div class="page-header-left">
                    <?php if ($firm_logo_url): ?>
                    <img src="<?php echo esc_url($firm_logo_url); ?>" alt="" class="page-header-logo">
                    <?php endif; ?>
                    <span class="page-header-firm"><?php echo esc_html($firm_name); ?></span>
                </div>
                <div class="page-header-right">
                    <div>ENGAGEMENT LETTER</div>
                    <div>Ref: <?php echo esc_html($doc_metadata['reference']); ?></div>
                </div>
            </div>
            
            <!-- PDF canvas -->
            <div class="page-content">
                <canvas id="pdfCanvas"></canvas>
                <?php if (!empty($permissions['show_watermark'])): ?>
                <div class="page-watermark"><?php echo esc_html($watermark_text); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Per-page signature line (for paper-only version) -->
            <div class="page-signature-line" id="pageSignatureLine">
                <div class="signature-field">
                    <span>Client Signature:</span>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-field">
                    <span>Date:</span>
                    <span class="date-line"></span>
                </div>
                <div class="signature-field">
                    <span>Page <span id="sigPageNum">1</span> of <span id="sigPageTotal">1</span></span>
                </div>
            </div>
            
            <!-- Running footer -->
            <div class="page-footer">
                <div class="page-footer-left">
                    <?php echo esc_html($doc_metadata['created_at']); ?>
                </div>
                <div class="page-footer-center">
                    Page <span id="footerPageNum">1</span> of <span id="footerPageTotal">1</span>
                </div>
                <div class="page-footer-right">
                    <?php echo esc_html($doc_metadata['reference']); ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Navigation bar -->
    <nav class="viewer-nav">
        <button class="nav-btn" id="btnFirst" onclick="goToPage(1)" title="First page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/>
            </svg>
        </button>
        
        <button class="nav-btn" id="btnPrev" onclick="prevPage()" title="Previous page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        
        <div class="page-indicator">
            <input type="number" class="page-input" id="pageInput" value="1" min="1" onchange="goToPage(this.value)">
            <span class="page-total">of <span id="totalPages">1</span></span>
        </div>
        
        <button class="nav-btn" id="btnNext" onclick="nextPage()" title="Next page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>
        
        <button class="nav-btn" id="btnLast" onclick="goToPage(totalPages)" title="Last page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 17l5-5-5-5M6 17l5-5-5-5"/>
            </svg>
        </button>
    </nav>
    
    <!-- Status footer -->
    <footer class="viewer-footer">
        <div class="footer-item">
            <span>🔒 Secure Document</span>
        </div>
        <div class="footer-item">
            <span>Access expires: <?php echo esc_html(date('M j, Y', strtotime($token_data['expires_at']))); ?></span>
        </div>
        <div class="footer-item">
            <span>Session:</span>
            <span class="session-timer" id="sessionTimer">30:00</span>
        </div>
    </footer>
    
    <script>
    (function() {
        'use strict';
        
        // Configuration
        const SESSION_TIMEOUT = <?php echo self::SESSION_TIMEOUT; ?>;
        const TOKEN = '<?php echo esc_js($token); ?>';
        const AJAX_URL = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        // PDF.js setup
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo self::PDFJS_CDN; ?>/pdf.worker.min.js';
        
        // State
        let pdfDoc = null;
        let currentPage = 1;
        let totalPages = 1;
        let rendering = false;
        let pendingPage = null;
        let sessionExpires = Date.now() + (SESSION_TIMEOUT * 1000);
        let lastActivity = Date.now();
        
        // Canvas
        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas.getContext('2d');
        
        // Load PDF from base64
        const pdfData = atob('<?php echo $pdf_base64; ?>');
        const pdfArray = new Uint8Array(pdfData.length);
        for (let i = 0; i < pdfData.length; i++) {
            pdfArray[i] = pdfData.charCodeAt(i);
        }
        
        // Initialize PDF
        pdfjsLib.getDocument({ data: pdfArray }).promise.then(function(pdf) {
            pdfDoc = pdf;
            totalPages = pdf.numPages;
            
            // Update UI
            document.getElementById('totalPages').textContent = totalPages;
            document.getElementById('footerPageTotal').textContent = totalPages;
            document.getElementById('sigPageTotal').textContent = totalPages;
            document.getElementById('pageInput').max = totalPages;
            
            // Hide loading overlay
            document.getElementById('loadingOverlay').classList.add('hidden');
            
            // Render first page
            renderPage(1);
            
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            document.querySelector('.loading-text').textContent = 'Error loading document';
        });
        
        // Render a specific page
        function renderPage(num) {
            if (rendering) {
                pendingPage = num;
                return;
            }
            
            rendering = true;
            currentPage = num;
            
            pdfDoc.getPage(num).then(function(page) {
                // Calculate scale to fit container width (794px max for A4)
                const containerWidth = document.querySelector('.page-content').clientWidth;
                const viewport = page.getViewport({ scale: 1 });
                const scale = containerWidth / viewport.width;
                const scaledViewport = page.getViewport({ scale: scale });
                
                // Set canvas dimensions
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;
                
                // Render
                const renderContext = {
                    canvasContext: ctx,
                    viewport: scaledViewport
                };
                
                page.render(renderContext).promise.then(function() {
                    rendering = false;
                    
                    // Update page indicators
                    document.getElementById('pageInput').value = num;
                    document.getElementById('footerPageNum').textContent = num;
                    document.getElementById('sigPageNum').textContent = num;
                    
                    // Update navigation buttons
                    document.getElementById('btnFirst').disabled = (num <= 1);
                    document.getElementById('btnPrev').disabled = (num <= 1);
                    document.getElementById('btnNext').disabled = (num >= totalPages);
                    document.getElementById('btnLast').disabled = (num >= totalPages);
                    
                    // Render pending page if any
                    if (pendingPage !== null) {
                        const pending = pendingPage;
                        pendingPage = null;
                        renderPage(pending);
                    }
                });
            });
        }
        
        // Navigation functions
        window.goToPage = function(num) {
            num = parseInt(num);
            if (num >= 1 && num <= totalPages && num !== currentPage) {
                renderPage(num);
            }
        };
        
        window.prevPage = function() {
            if (currentPage > 1) {
                renderPage(currentPage - 1);
            }
        };
        
        window.nextPage = function() {
            if (currentPage < totalPages) {
                renderPage(currentPage + 1);
            }
        };
        
        window.totalPages = totalPages;
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT') return;
            
            switch(e.key) {
                case 'ArrowLeft':
                case 'PageUp':
                    prevPage();
                    e.preventDefault();
                    break;
                case 'ArrowRight':
                case 'PageDown':
                case ' ':
                    nextPage();
                    e.preventDefault();
                    break;
                case 'Home':
                    goToPage(1);
                    e.preventDefault();
                    break;
                case 'End':
                    goToPage(totalPages);
                    e.preventDefault();
                    break;
            }
        });
        
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Disable print shortcut (use our button)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Track activity
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, function() {
                lastActivity = Date.now();
                sessionExpires = Date.now() + (SESSION_TIMEOUT * 1000);
                document.getElementById('sessionTimer').classList.remove('session-warning');
            });
        });
        
        // Session timer
        setInterval(function() {
            const remaining = Math.max(0, Math.floor((sessionExpires - Date.now()) / 1000));
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            
            const timerEl = document.getElementById('sessionTimer');
            timerEl.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            if (remaining <= 300) {
                timerEl.classList.add('session-warning');
            }
            
            if (remaining <= 0) {
                alert('Your session has expired. Please request a new viewing link.');
                window.location.href = '<?php echo home_url(); ?>';
            }
        }, 1000);
        
        // Print function
        window.printDocument = function() {
            window.print();
        };
        
        // Download function
        window.downloadDocument = function() {
            const link = document.createElement('a');
            link.href = AJAX_URL + '?action=el_secure_download&token=' + encodeURIComponent(TOKEN);
            link.click();
        };
        
    })();
    </script>
</body>
</html>
        <?php
    }
    
    /**
     * Render error page
     */
    private static function render_error($message) {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .error-box {
            background: #16213e;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
        }
        .error-icon { font-size: 48px; margin-bottom: 20px; }
        .error-title { font-size: 24px; margin-bottom: 10px; }
        .error-message { color: #94a3b8; margin-bottom: 20px; }
        .error-link { color: #e53e3e; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-icon">🔒</div>
        <div class="error-title">Access Denied</div>
        <div class="error-message"><?php echo esc_html($message); ?></div>
        <a href="<?php echo home_url(); ?>" class="error-link">Return to homepage</a>
    </div>
</body>
</html>
        <?php
    }
}

// Initialize
EL_Paginated_Viewer::init();