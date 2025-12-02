<?php
/**
 * SLM DMS Viewer
 * 
 * Secure document viewer:
 * - PDF.js based rendering
 * - Read depth tracking
 * - Time tracking
 * - Version switching
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Viewer {
    
    /**
     * Session token length
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * Session expiry (hours)
     */
    const SESSION_EXPIRY = 4;
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('wp_ajax_slm_create_view_session', [__CLASS__, 'ajax_create_session']);
        add_action('wp_ajax_slm_get_document_page', [__CLASS__, 'ajax_get_page']);
    }
    
    /**
     * Create viewing session
     */
    public static function create_session($document_id, $user_id = null, $share_link_id = null) {
        global $wpdb;
        
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        // Validate document access
        if (!$share_link_id && $user_id && !SLM_DMS_Documents::user_can_access($document_id, $user_id)) {
            return new WP_Error('access_denied', __('You do not have permission to view this document.', 'flavor'));
        }
        
        // Get document info
        $document = SLM_DMS_Documents::get_document($document_id);
        if (!$document) {
            return new WP_Error('not_found', __('Document not found.', 'flavor'));
        }
        
        // Get page count for PDF
        $total_pages = self::get_page_count($document_id);
        
        // Generate session token
        $session_token = wp_generate_password(self::TOKEN_LENGTH, false);
        
        // Create session record
        $table = SLM_DMS::get_table('viewing_sessions');
        
        $wpdb->insert($table, [
            'document_id' => $document_id,
            'version_id' => null,
            'user_id' => $user_id ?: null,
            'share_link_id' => $share_link_id,
            'session_token' => $session_token,
            'total_pages' => $total_pages,
            'pages_viewed' => json_encode([]),
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '',
            'started_at' => current_time('mysql'),
            'last_update_at' => current_time('mysql'),
        ]);
        
        return [
            'session_token' => $session_token,
            'document_id' => $document_id,
            'total_pages' => $total_pages,
            'viewer_url' => home_url('/document-viewer/' . $document_id . '/' . $session_token . '/'),
        ];
    }
    
    /**
     * Update viewing session
     */
    public static function update_session($data) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('viewing_sessions');
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE session_token = %s LIMIT 1",
            $data['session_token']
        ));
        
        if (!$session) {
            return false;
        }
        
        // Merge pages viewed
        $existing_pages = json_decode($session->pages_viewed, true) ?: [];
        $new_pages = $data['pages_viewed'] ?? [];
        $all_pages = array_unique(array_merge($existing_pages, $new_pages));
        sort($all_pages);
        
        $update_data = [
            'pages_viewed' => json_encode($all_pages),
            'max_page_reached' => max($data['max_page_reached'] ?? 0, $session->max_page_reached),
            'read_depth_percent' => $data['read_depth_percent'] ?? $session->read_depth_percent,
            'total_time_seconds' => $data['total_time_seconds'] ?? $session->total_time_seconds,
            'completed' => ($data['completed'] ?? false) ? 1 : $session->completed,
            'last_update_at' => current_time('mysql'),
        ];
        
        if (!empty($data['final'])) {
            $update_data['ended_at'] = current_time('mysql');
        }
        
        $wpdb->update(
            $table,
            $update_data,
            ['id' => $session->id]
        );
        
        return true;
    }
    
    /**
     * Validate session token
     */
    public static function validate_session($session_token) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('viewing_sessions');
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE session_token = %s LIMIT 1",
            $session_token
        ));
        
        if (!$session) {
            return new WP_Error('invalid_session', __('Invalid viewing session.', 'flavor'));
        }
        
        // Check expiry
        $expiry_time = strtotime($session->started_at) + (self::SESSION_EXPIRY * 3600);
        if (time() > $expiry_time) {
            return new WP_Error('session_expired', __('Viewing session has expired.', 'flavor'));
        }
        
        return $session;
    }
    
    /**
     * Get page count for document
     */
    private static function get_page_count($document_id) {
        // Try to get cached count
        $count = get_post_meta($document_id, '_slm_page_count', true);
        if ($count) {
            return intval($count);
        }
        
        // Get document content
        $content_data = SLM_DMS_Documents::get_document_content($document_id);
        if (is_wp_error($content_data)) {
            return 1;
        }
        
        // Count pages for PDF
        if ($content_data['mime_type'] === 'application/pdf') {
            $count = self::count_pdf_pages($content_data['content']);
            update_post_meta($document_id, '_slm_page_count', $count);
            return $count;
        }
        
        return 1;
    }
    
    /**
     * Count PDF pages
     */
    private static function count_pdf_pages($pdf_content) {
        // Simple regex to count pages
        preg_match_all('/\/Type\s*\/Page[^s]/i', $pdf_content, $matches);
        $count = count($matches[0]);
        
        return max(1, $count);
    }
    
    /**
     * Render secure viewer page
     */
    public static function render_viewer($document_id, $session_token) {
        // Validate session
        $session = self::validate_session($session_token);
        
        if (is_wp_error($session)) {
            self::render_error_page($session->get_error_message());
            return;
        }
        
        // Get document
        $document = SLM_DMS_Documents::get_document($document_id);
        
        if (!$document) {
            self::render_error_page(__('Document not found.', 'flavor'));
            return;
        }
        
        // Get versions for dropdown
        $versions = SLM_DMS_Documents::get_versions($document_id);
        
        // Render viewer HTML
        self::render_viewer_html($document, $session, $versions);
    }
    
    /**
     * Render viewer HTML
     */
    private static function render_viewer_html($document, $session, $versions) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo get_locale(); ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($document['title']); ?> - <?php echo esc_html($firm_name); ?></title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #1a1a2e;
                    color: #fff;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }
                
                .viewer-header {
                    background: #16213e;
                    padding: 12px 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    border-bottom: 1px solid #0f3460;
                }
                
                .viewer-title {
                    font-size: 16px;
                    font-weight: 500;
                    max-width: 400px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                
                .viewer-controls {
                    display: flex;
                    align-items: center;
                    gap: 16px;
                }
                
                .version-select {
                    background: #0f3460;
                    color: #fff;
                    border: 1px solid #1a4a7a;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 14px;
                    cursor: pointer;
                }
                
                .viewer-btn {
                    background: #e94560;
                    color: #fff;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    font-size: 14px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                
                .viewer-btn:hover {
                    background: #d63850;
                }
                
                .viewer-btn.secondary {
                    background: #0f3460;
                }
                
                .viewer-btn.secondary:hover {
                    background: #1a4a7a;
                }
                
                .close-btn {
                    background: transparent;
                    color: #fff;
                    border: none;
                    padding: 8px;
                    cursor: pointer;
                    font-size: 20px;
                    line-height: 1;
                }
                
                .viewer-container {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                }
                
                .pdf-container {
                    flex: 1;
                    overflow: auto;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 20px;
                    gap: 20px;
                }
                
                .pdf-page {
                    background: #fff;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    max-width: 100%;
                }
                
                .viewer-footer {
                    background: #16213e;
                    padding: 12px 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    border-top: 1px solid #0f3460;
                }
                
                .page-nav {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                
                .page-info {
                    font-size: 14px;
                    color: #a0a0a0;
                }
                
                .zoom-controls {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .zoom-btn {
                    background: #0f3460;
                    color: #fff;
                    border: none;
                    width: 32px;
                    height: 32px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 18px;
                }
                
                .zoom-btn:hover {
                    background: #1a4a7a;
                }
                
                .zoom-level {
                    font-size: 14px;
                    min-width: 60px;
                    text-align: center;
                }
                
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(26, 26, 46, 0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                    gap: 16px;
                    z-index: 1000;
                }
                
                .spinner {
                    width: 48px;
                    height: 48px;
                    border: 4px solid #0f3460;
                    border-top-color: #e94560;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
                
                .error-page {
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                    gap: 16px;
                    text-align: center;
                    padding: 40px;
                }
                
                .error-icon {
                    font-size: 48px;
                }
                
                .security-badge {
                    position: fixed;
                    bottom: 80px;
                    right: 20px;
                    background: #16213e;
                    border: 1px solid #0f3460;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 12px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                
                .security-badge svg {
                    fill: #10b981;
                }
                
                @media (max-width: 768px) {
                    .viewer-title {
                        max-width: 150px;
                    }
                    
                    .viewer-controls {
                        gap: 8px;
                    }
                    
                    .viewer-btn span {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="loading-overlay" id="loading">
                <div class="spinner"></div>
                <p><?php esc_html_e('Loading document...', 'flavor'); ?></p>
            </div>
            
            <header class="viewer-header">
                <div class="viewer-title"><?php echo esc_html($document['title']); ?></div>
                
                <div class="viewer-controls">
                    <?php if (count($versions) > 1): ?>
                    <select class="version-select" id="version-selector">
                        <?php foreach ($versions as $v): ?>
                        <option value="<?php echo esc_attr($v->version_number); ?>" <?php selected($v->is_current, 1); ?>>
                            <?php printf(
                                __('v%d %s - %s', 'flavor'),
                                $v->version_number,
                                $v->is_current ? __('(Current)', 'flavor') : '',
                                date_i18n(get_option('date_format'), strtotime($v->created_at))
                            ); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    
                    <?php if ($document['download_allowed']): ?>
                    <button class="viewer-btn secondary" id="download-btn">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        <span><?php esc_html_e('Download', 'flavor'); ?></span>
                    </button>
                    <?php endif; ?>
                    
                    <button class="close-btn" id="close-btn" title="<?php esc_attr_e('Close', 'flavor'); ?>">×</button>
                </div>
            </header>
            
            <div class="viewer-container">
                <div class="pdf-container" id="pdf-container"></div>
            </div>
            
            <footer class="viewer-footer">
                <div class="page-nav">
                    <button class="zoom-btn" id="prev-page">‹</button>
                    <span class="page-info">
                        <span id="current-page">1</span> / <span id="total-pages"><?php echo intval($session->total_pages); ?></span>
                    </span>
                    <button class="zoom-btn" id="next-page">›</button>
                </div>
                
                <div class="zoom-controls">
                    <button class="zoom-btn" id="zoom-out">−</button>
                    <span class="zoom-level" id="zoom-level">100%</span>
                    <button class="zoom-btn" id="zoom-in">+</button>
                </div>
            </footer>
            
            <div class="security-badge">
                <svg width="14" height="14" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
                <?php esc_html_e('Encrypted & Secure', 'flavor'); ?>
            </div>
            
            <script>
            (function() {
                const config = {
                    documentId: <?php echo intval($document['id']); ?>,
                    sessionToken: '<?php echo esc_js($session->session_token); ?>',
                    totalPages: <?php echo intval($session->total_pages); ?>,
                    ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                    restUrl: '<?php echo esc_js(rest_url('slm/v1/')); ?>',
                    nonce: '<?php echo esc_js(wp_create_nonce('slm_dms_nonce')); ?>',
                    downloadAllowed: <?php echo $document['download_allowed'] ? 'true' : 'false'; ?>
                };
                
                // PDF.js setup
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                
                // Viewer state
                let pdfDoc = null;
                let currentPage = 1;
                let scale = 1;
                let pagesViewed = new Set();
                let startTime = Date.now();
                let lastUpdate = Date.now();
                
                // DOM elements
                const loading = document.getElementById('loading');
                const container = document.getElementById('pdf-container');
                const currentPageEl = document.getElementById('current-page');
                const totalPagesEl = document.getElementById('total-pages');
                const zoomLevelEl = document.getElementById('zoom-level');
                
                // Load document
                async function loadDocument(version = null) {
                    loading.style.display = 'flex';
                    
                    try {
                        const formData = new FormData();
                        formData.append('action', 'slm_get_document_page');
                        formData.append('nonce', config.nonce);
                        formData.append('document_id', config.documentId);
                        formData.append('session_token', config.sessionToken);
                        if (version) formData.append('version', version);
                        
                        const response = await fetch(config.ajaxUrl, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (!result.success) {
                            throw new Error(result.data.message || 'Failed to load document');
                        }
                        
                        // Load PDF from base64
                        const pdfData = atob(result.data.content);
                        const pdfArray = new Uint8Array(pdfData.length);
                        for (let i = 0; i < pdfData.length; i++) {
                            pdfArray[i] = pdfData.charCodeAt(i);
                        }
                        
                        pdfDoc = await pdfjsLib.getDocument({ data: pdfArray }).promise;
                        totalPagesEl.textContent = pdfDoc.numPages;
                        config.totalPages = pdfDoc.numPages;
                        
                        await renderAllPages();
                        
                    } catch (error) {
                        console.error('Load error:', error);
                        container.innerHTML = '<div class="error-page"><div class="error-icon">⚠️</div><p>' + error.message + '</p></div>';
                    } finally {
                        loading.style.display = 'none';
                    }
                }
                
                // Render all pages
                async function renderAllPages() {
                    container.innerHTML = '';
                    
                    for (let i = 1; i <= pdfDoc.numPages; i++) {
                        const page = await pdfDoc.getPage(i);
                        const viewport = page.getViewport({ scale: scale });
                        
                        const canvas = document.createElement('canvas');
                        canvas.className = 'pdf-page';
                        canvas.dataset.page = i;
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        const context = canvas.getContext('2d');
                        await page.render({ canvasContext: context, viewport: viewport }).promise;
                        
                        container.appendChild(canvas);
                    }
                    
                    // Setup intersection observer for tracking
                    setupPageTracking();
                }
                
                // Setup page tracking
                function setupPageTracking() {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const pageNum = parseInt(entry.target.dataset.page);
                                pagesViewed.add(pageNum);
                                currentPageEl.textContent = pageNum;
                                currentPage = pageNum;
                            }
                        });
                    }, { threshold: 0.5 });
                    
                    document.querySelectorAll('.pdf-page').forEach(page => {
                        observer.observe(page);
                    });
                }
                
                // Send tracking update
                function sendUpdate(final = false) {
                    const data = {
                        session_token: config.sessionToken,
                        pages_viewed: Array.from(pagesViewed),
                        total_time_seconds: Math.floor((Date.now() - startTime) / 1000),
                        max_page_reached: Math.max(...pagesViewed, 0),
                        read_depth_percent: Math.floor((pagesViewed.size / config.totalPages) * 100),
                        completed: pagesViewed.has(config.totalPages),
                        final: final
                    };
                    
                    navigator.sendBeacon(config.restUrl + 'viewer-update', JSON.stringify(data));
                    lastUpdate = Date.now();
                }
                
                // Zoom functions
                function updateZoom(newScale) {
                    scale = Math.max(0.5, Math.min(3, newScale));
                    zoomLevelEl.textContent = Math.round(scale * 100) + '%';
                    renderAllPages();
                }
                
                // Event listeners
                document.getElementById('zoom-in').addEventListener('click', () => updateZoom(scale + 0.25));
                document.getElementById('zoom-out').addEventListener('click', () => updateZoom(scale - 0.25));
                
                document.getElementById('prev-page').addEventListener('click', () => {
                    if (currentPage > 1) {
                        document.querySelectorAll('.pdf-page')[currentPage - 2].scrollIntoView({ behavior: 'smooth' });
                    }
                });
                
                document.getElementById('next-page').addEventListener('click', () => {
                    if (currentPage < config.totalPages) {
                        document.querySelectorAll('.pdf-page')[currentPage].scrollIntoView({ behavior: 'smooth' });
                    }
                });
                
                const versionSelector = document.getElementById('version-selector');
                if (versionSelector) {
                    versionSelector.addEventListener('change', (e) => {
                        loadDocument(e.target.value);
                    });
                }
                
                const downloadBtn = document.getElementById('download-btn');
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', () => {
                        window.location.href = config.ajaxUrl + '?action=slm_download_document&document_id=' + config.documentId;
                    });
                }
                
                document.getElementById('close-btn').addEventListener('click', () => {
                    sendUpdate(true);
                    window.close();
                    // Fallback if window.close doesn't work
                    setTimeout(() => {
                        history.back();
                    }, 100);
                });
                
                // Periodic updates
                setInterval(() => sendUpdate(), 30000);
                
                // Final update on close
                window.addEventListener('beforeunload', () => sendUpdate(true));
                
                // Keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                        document.getElementById('prev-page').click();
                    } else if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                        document.getElementById('next-page').click();
                    } else if (e.key === '+' || e.key === '=') {
                        document.getElementById('zoom-in').click();
                    } else if (e.key === '-') {
                        document.getElementById('zoom-out').click();
                    } else if (e.key === 'Escape') {
                        document.getElementById('close-btn').click();
                    }
                });
                
                // Initialize
                loadDocument();
            })();
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render error page
     */
    private static function render_error_page($message) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php esc_html_e('Error', 'flavor'); ?></title>
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
                .error-container {
                    text-align: center;
                    padding: 40px;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                .error-message {
                    font-size: 18px;
                    color: #e94560;
                    margin-bottom: 20px;
                }
                .back-btn {
                    background: #e94560;
                    color: #fff;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 6px;
                    font-size: 16px;
                    cursor: pointer;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <p class="error-message"><?php echo esc_html($message); ?></p>
                <a href="javascript:history.back()" class="back-btn"><?php esc_html_e('Go Back', 'flavor'); ?></a>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Get client IP
     */
    private static function get_client_ip() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
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
     * Get viewing statistics for document
     */
    public static function get_document_stats($document_id) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('viewing_sessions');
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_viewers,
                AVG(read_depth_percent) as avg_read_depth,
                AVG(total_time_seconds) as avg_time_seconds,
                SUM(completed) as completed_views
             FROM $table 
             WHERE document_id = %d",
            $document_id
        ));
    }
    
    /**
     * AJAX: Create view session
     */
    public static function ajax_create_session() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $document_id = intval($_POST['document_id'] ?? 0);
        
        if (!$document_id) {
            wp_send_json_error(['message' => __('Invalid document.', 'flavor')]);
        }
        
        $result = self::create_session($document_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Get document page content
     */
    public static function ajax_get_page() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $document_id = intval($_POST['document_id'] ?? 0);
        $session_token = sanitize_text_field($_POST['session_token'] ?? '');
        $version = isset($_POST['version']) ? intval($_POST['version']) : null;
        
        // Validate session
        $session = self::validate_session($session_token);
        if (is_wp_error($session)) {
            wp_send_json_error(['message' => $session->get_error_message()]);
        }
        
        if ($session->document_id != $document_id) {
            wp_send_json_error(['message' => __('Session mismatch.', 'flavor')]);
        }
        
        // Get content
        $content_data = SLM_DMS_Documents::get_document_content($document_id, $version);
        
        if (is_wp_error($content_data)) {
            wp_send_json_error(['message' => $content_data->get_error_message()]);
        }
        
        wp_send_json_success([
            'content' => base64_encode($content_data['content']),
            'mime_type' => $content_data['mime_type'],
            'version' => $content_data['version'],
        ]);
    }
}
