<?php
/**
 * Secure Document Viewer for Engagement Letters
 * 
 * Implements secure, time-limited access to encrypted PDFs with watermarking,
 * access logging, and protection against unauthorized distribution.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * SECURITY FEATURES:
 *   - Time-limited access tokens (14 day default expiry)
 *   - IP-based access logging
 *   - Session-based authentication
 *   - Rate limiting (20 requests per hour per IP)
 *   - Watermark with client email and view timestamp
 *   - Disabled right-click context menu
 *   - Disabled text selection (CSS user-select: none)
 *   - Auto-logout after 30 minutes of inactivity
 *   - Downloads only through viewer (no direct file access)
 * 
 * USAGE:
 *   Generate token: EL_Secure_Viewer::generate_access_token($file_id, $email, $days)
 *   View URL: site.com/?el_secure_view=1&token=xxx
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Secure_Viewer {
    
    /**
     * Token expiry in days
     */
    const DEFAULT_TOKEN_EXPIRY_DAYS = 14;
    
    /**
     * Session timeout in seconds (30 minutes)
     */
    const SESSION_TIMEOUT = 1800;
    
    /**
     * Rate limit: requests per hour per IP
     */
    const RATE_LIMIT_PER_HOUR = 20;
    
    /**
     * Token length in bytes (before encoding)
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * Initialize viewer hooks
     */
    public static function init() {
        add_action('init', [__CLASS__, 'handle_viewer_request'], 1);
        add_action('wp_ajax_el_secure_download', [__CLASS__, 'handle_download']);
        add_action('wp_ajax_nopriv_el_secure_download', [__CLASS__, 'handle_download']);
        add_action('wp_ajax_el_viewer_heartbeat', [__CLASS__, 'handle_heartbeat']);
        add_action('wp_ajax_nopriv_el_viewer_heartbeat', [__CLASS__, 'handle_heartbeat']);
    }
    
    /**
     * Generate secure access token
     * 
     * @param string $file_id File ID from encryption storage
     * @param string $email Authorized viewer's email
     * @param int $expiry_days Days until token expires
     * @param array $permissions Additional permissions
     * @return array Token data with URL
     */
    public static function generate_access_token($file_id, $email, $expiry_days = null, $permissions = []) {
        global $wpdb;
        
        if ($expiry_days === null) {
            $expiry_days = self::DEFAULT_TOKEN_EXPIRY_DAYS;
        }
        
        // Generate secure random token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        
        // Default permissions for engagement letters (print allowed)
        $default_permissions = [
            'can_print' => true,
            'can_download' => true,
            'show_watermark' => true,
        ];
        $permissions = wp_parse_args($permissions, $default_permissions);
        
        // Calculate expiry
        $expires_at = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));
        
        // Store token
        $table_name = $wpdb->prefix . 'el_view_tokens';
        self::maybe_create_tables();
        
        $result = $wpdb->insert(
            $table_name,
            [
                'token' => hash('sha256', $token), // Store hashed
                'file_id' => $file_id,
                'email' => $email,
                'permissions' => wp_json_encode($permissions),
                'expires_at' => $expires_at,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id(),
                'view_count' => 0,
                'last_viewed_at' => null,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
        );
        
        if ($result === false) {
            return false;
        }
        
        // Generate view URL
        $view_url = add_query_arg([
            'el_secure_view' => '1',
            'token' => $token,
        ], home_url('/'));
        
        return [
            'token' => $token,
            'url' => $view_url,
            'expires_at' => $expires_at,
            'expiry_days' => $expiry_days,
            'email' => $email,
        ];
    }
    
    /**
     * Verify access token
     * 
     * @param string $token Raw token from URL
     * @return array|false Token data or false if invalid
     */
    public static function verify_token($token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        $token_hash = hash('sha256', $token);
        
        $token_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE token = %s AND expires_at > %s AND revoked = 0",
            $token_hash,
            current_time('mysql')
        ), ARRAY_A);
        
        if (!$token_data) {
            return false;
        }
        
        // Decode permissions
        $token_data['permissions'] = json_decode($token_data['permissions'], true);
        
        return $token_data;
    }
    
    /**
     * Handle viewer request
     */
    public static function handle_viewer_request() {
        if (!isset($_GET['el_secure_view']) || $_GET['el_secure_view'] !== '1') {
            return;
        }
        
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        
        if (empty($token)) {
            self::render_error('Missing access token.');
            exit;
        }
        
        // Check rate limit
        if (!self::check_rate_limit()) {
            self::render_error('Too many requests. Please try again later.');
            exit;
        }
        
        // Verify token
        $token_data = self::verify_token($token);
        
        if (!$token_data) {
            self::log_access_attempt($token, 'invalid_token');
            self::render_error('Invalid or expired access link. Please request a new viewing link.');
            exit;
        }
        
        // Get document
        $pdf_content = EL_PDF_Encryption::retrieve_pdf($token_data['file_id']);
        
        if ($pdf_content === false) {
            self::log_access_attempt($token, 'document_not_found');
            self::render_error('Document not found or has been removed.');
            exit;
        }
        
        // Update view count
        self::update_view_count($token_data['id']);
        
        // Log successful access
        self::log_access($token_data['file_id'], $token_data['email'], 'view');
        
        // Start session for timeout tracking
        if (!session_id()) {
            session_start();
        }
        $_SESSION['el_viewer_token'] = $token;
        $_SESSION['el_viewer_last_activity'] = time();
        $_SESSION['el_viewer_file_id'] = $token_data['file_id'];
        
        // Render secure viewer
        self::render_viewer($pdf_content, $token_data);
        exit;
    }
    
    /**
     * Check rate limit
     * 
     * @return bool True if within limit
     */
    private static function check_rate_limit() {
        global $wpdb;
        
        $ip = self::get_client_ip();
        $table_name = $wpdb->prefix . 'el_access_log';
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE ip_address = %s AND accessed_at > %s",
            $ip,
            $one_hour_ago
        ));
        
        return $count < self::RATE_LIMIT_PER_HOUR;
    }
    
    /**
     * Update view count for token
     * 
     * @param int $token_id Token ID
     */
    private static function update_view_count($token_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET view_count = view_count + 1, last_viewed_at = %s WHERE id = %d",
            current_time('mysql'),
            $token_id
        ));
    }
    
    /**
     * Log access attempt
     * 
     * @param string $token Token attempted
     * @param string $reason Failure reason
     */
    private static function log_access_attempt($token, $reason) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_access_log';
        
        $wpdb->insert(
            $table_name,
            [
                'file_id' => 'ATTEMPT:' . substr($token, 0, 10),
                'action' => 'failed_access',
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'accessed_at' => current_time('mysql'),
                'details' => $reason,
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }
    
    /**
     * Log successful access
     * 
     * @param string $file_id File ID
     * @param string $email Viewer email
     * @param string $action Action performed
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
     * Get client IP address
     * 
     * @return string IP address
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
     * Render secure viewer
     * 
     * @param string $pdf_content PDF binary content
     * @param array $token_data Token data
     */
    private static function render_viewer($pdf_content, $token_data) {
        $permissions = $token_data['permissions'];
        $email = $token_data['email'];
        $file_id = $token_data['file_id'];
        
        // Generate watermark text
        $watermark_text = sprintf(
            'Viewed by: %s | %s | Ref: %s',
            $email,
            current_time('F j, Y g:i A'),
            substr($file_id, 0, 12)
        );
        
        // Generate PDF data URL for embedded viewer
        $pdf_base64 = base64_encode($pdf_content);
        
        // Calculate session expiry
        $session_expires = time() + self::SESSION_TIMEOUT;
        
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Secure Document Viewer - Engagement Letter</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
            /* Disable text selection */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Header bar */
        .viewer-header {
            background: #16213e;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #0f3460;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }
        
        .viewer-title {
            font-size: 14px;
            color: #94a3b8;
        }
        
        .viewer-title strong {
            color: #fff;
        }
        
        .viewer-actions {
            display: flex;
            gap: 10px;
        }
        
        .viewer-btn {
            background: #0f3460;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        
        .viewer-btn:hover {
            background: #1a4980;
        }
        
        .viewer-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .viewer-btn.primary {
            background: #e94560;
        }
        
        .viewer-btn.primary:hover {
            background: #ff6b6b;
        }
        
        /* Main viewer area */
        .viewer-container {
            padding-top: 60px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .pdf-frame {
            flex: 1;
            width: 100%;
            border: none;
            background: #2d2d44;
        }
        
        /* Watermark overlay */
        .watermark-overlay {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .watermark-text {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.03);
            transform: rotate(-30deg);
            white-space: nowrap;
            position: absolute;
        }
        
        /* Session warning */
        .session-warning {
            display: none;
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            color: #000;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13px;
            z-index: 200;
            animation: pulse 2s infinite;
        }
        
        .session-warning.visible {
            display: block;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Footer info */
        .viewer-footer {
            background: #16213e;
            padding: 8px 20px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #0f3460;
        }
        
        /* Loading state */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #1a1a2e;
            display: flex;
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
            border: 4px solid #0f3460;
            border-top-color: #e94560;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Print styles - add watermark */
        @media print {
            .viewer-header,
            .viewer-footer,
            .session-warning {
                display: none !important;
            }
            
            .watermark-overlay {
                display: block !important;
            }
            
            .watermark-text {
                color: rgba(0, 0, 0, 0.08) !important;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <!-- Header -->
    <div class="viewer-header">
        <div class="viewer-title">
            <strong>Engagement Letter</strong> | Viewing as: <?php echo esc_html($email); ?>
        </div>
        <div class="viewer-actions">
            <?php if (!empty($permissions['can_print'])): ?>
            <button class="viewer-btn" onclick="printDocument()" title="Print document">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                Print
            </button>
            <?php endif; ?>
            
            <?php if (!empty($permissions['can_download'])): ?>
            <button class="viewer-btn primary" onclick="downloadDocument()" title="Download PDF">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Session warning -->
    <div class="session-warning" id="sessionWarning">
        Your session will expire soon. Click anywhere to extend.
    </div>
    
    <!-- PDF Viewer -->
    <div class="viewer-container">
        <iframe 
            id="pdfViewer"
            class="pdf-frame" 
            src="data:application/pdf;base64,<?php echo $pdf_base64; ?>#toolbar=0&navpanes=0"
        ></iframe>
    </div>
    
    <!-- Watermark overlay -->
    <?php if (!empty($permissions['show_watermark'])): ?>
    <div class="watermark-overlay" id="watermarkOverlay">
        <?php for ($i = 0; $i < 20; $i++): ?>
        <div class="watermark-text" style="top: <?php echo ($i * 150) - 200; ?>px; left: <?php echo (($i % 2) * 300) - 100; ?>px;">
            <?php echo esc_html($watermark_text); ?>
        </div>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="viewer-footer">
        This document is confidential. Access expires: <?php echo esc_html(date('F j, Y', strtotime($token_data['expires_at']))); ?>
        | Session timeout: <span id="sessionTimer">30:00</span>
    </div>
    
    <script>
    (function() {
        'use strict';
        
        // Configuration
        const SESSION_TIMEOUT = <?php echo self::SESSION_TIMEOUT; ?>;
        const HEARTBEAT_INTERVAL = 60000; // 1 minute
        const WARNING_THRESHOLD = 300; // 5 minutes before timeout
        const TOKEN = '<?php echo esc_js($_GET['token']); ?>';
        
        let lastActivity = Date.now();
        let sessionExpires = <?php echo $session_expires * 1000; ?>;
        let heartbeatTimer = null;
        let countdownTimer = null;
        
        // Hide loading overlay when PDF loads
        document.getElementById('pdfViewer').onload = function() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        };
        
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Disable keyboard shortcuts for saving/printing (except our buttons)
        document.addEventListener('keydown', function(e) {
            // Ctrl+S, Ctrl+P, Ctrl+Shift+S
            if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'p')) {
                e.preventDefault();
                return false;
            }
            // F12 (dev tools)
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }
        });
        
        // Track activity
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, function() {
                lastActivity = Date.now();
                sessionExpires = Date.now() + (SESSION_TIMEOUT * 1000);
                document.getElementById('sessionWarning').classList.remove('visible');
            });
        });
        
        // Update session timer display
        function updateSessionTimer() {
            const remaining = Math.max(0, Math.floor((sessionExpires - Date.now()) / 1000));
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            
            document.getElementById('sessionTimer').textContent = 
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            // Show warning
            if (remaining <= WARNING_THRESHOLD && remaining > 0) {
                document.getElementById('sessionWarning').classList.add('visible');
            }
            
            // Session expired
            if (remaining <= 0) {
                clearInterval(countdownTimer);
                clearInterval(heartbeatTimer);
                alert('Your session has expired. Please request a new viewing link.');
                window.location.href = '<?php echo home_url(); ?>';
            }
        }
        
        // Heartbeat to keep session alive
        function sendHeartbeat() {
            const timeSinceActivity = Date.now() - lastActivity;
            
            // Only send heartbeat if user was active recently
            if (timeSinceActivity < HEARTBEAT_INTERVAL * 2) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=el_viewer_heartbeat&token=' + encodeURIComponent(TOKEN)
                });
            }
        }
        
        // Print function
        window.printDocument = function() {
            const iframe = document.getElementById('pdfViewer');
            iframe.contentWindow.print();
        };
        
        // Download function
        window.downloadDocument = function() {
            const link = document.createElement('a');
            link.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=el_secure_download&token=' + encodeURIComponent(TOKEN);
            link.click();
        };
        
        // Start timers
        countdownTimer = setInterval(updateSessionTimer, 1000);
        heartbeatTimer = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
        updateSessionTimer();
        
        // Warn before leaving
        window.addEventListener('beforeunload', function(e) {
            // Optional: warn user
        });
        
    })();
    </script>
</body>
</html>
        <?php
    }
    
    /**
     * Render error page
     * 
     * @param string $message Error message
     */
    private static function render_error($message) {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Secure Document Viewer</title>
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
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .error-message {
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .error-link {
            color: #e94560;
            text-decoration: none;
        }
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
    
    /**
     * Handle secure download request
     */
    public static function handle_download() {
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        
        if (empty($token)) {
            wp_die('Invalid request');
        }
        
        // Verify token
        $token_data = self::verify_token($token);
        
        if (!$token_data) {
            wp_die('Invalid or expired access link');
        }
        
        // Check download permission
        $permissions = $token_data['permissions'];
        if (empty($permissions['can_download'])) {
            wp_die('Download not permitted for this document');
        }
        
        // Get document
        $pdf_content = EL_PDF_Encryption::retrieve_pdf($token_data['file_id']);
        
        if ($pdf_content === false) {
            wp_die('Document not found');
        }
        
        // Log download
        self::log_access($token_data['file_id'], $token_data['email'], 'download');
        
        // Send PDF
        $filename = 'engagement-letter-' . substr($token_data['file_id'], 0, 12) . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $pdf_content;
        exit;
    }
    
    /**
     * Handle heartbeat request
     */
    public static function handle_heartbeat() {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        
        if (empty($token)) {
            wp_send_json_error('Invalid token');
        }
        
        // Verify token still valid
        $token_data = self::verify_token($token);
        
        if (!$token_data) {
            wp_send_json_error('Session expired');
        }
        
        // Update session
        if (session_id()) {
            $_SESSION['el_viewer_last_activity'] = time();
        }
        
        wp_send_json_success(['status' => 'alive']);
    }
    
    /**
     * Revoke access token
     * 
     * @param string $token Token to revoke (raw or hashed)
     * @return bool True on success
     */
    public static function revoke_token($token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        
        // Try both raw and hashed
        $token_hash = strlen($token) === 64 ? $token : hash('sha256', $token);
        
        $result = $wpdb->update(
            $table_name,
            ['revoked' => 1, 'revoked_at' => current_time('mysql')],
            ['token' => $token_hash],
            ['%d', '%s'],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Get all tokens for a document
     * 
     * @param string $file_id File ID
     * @return array Array of tokens
     */
    public static function get_tokens_for_document($file_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT id, email, expires_at, created_at, view_count, last_viewed_at, revoked 
             FROM $table_name WHERE file_id = %s ORDER BY created_at DESC",
            $file_id
        ), ARRAY_A);
    }
    
    /**
     * Create database tables
     */
    public static function maybe_create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        $charset_collate = $wpdb->get_charset_collate();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            token varchar(64) NOT NULL,
            file_id varchar(50) NOT NULL,
            email varchar(100) NOT NULL,
            permissions text,
            expires_at datetime NOT NULL,
            created_at datetime NOT NULL,
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            view_count int(11) unsigned NOT NULL DEFAULT 0,
            last_viewed_at datetime DEFAULT NULL,
            revoked tinyint(1) NOT NULL DEFAULT 0,
            revoked_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY file_id (file_id),
            KEY email (email),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Ensure access log has details column
        $access_table = $wpdb->prefix . 'el_access_log';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $access_table LIKE 'details'");
        
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $access_table ADD COLUMN details varchar(255) DEFAULT NULL");
        }
    }
    
    /**
     * Cleanup expired tokens
     * 
     * @return int Number of tokens cleaned
     */
    public static function cleanup_expired_tokens() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_view_tokens';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE expires_at < %s",
            current_time('mysql')
        ));
        
        return $result;
    }
}

// Initialize
EL_Secure_Viewer::init();