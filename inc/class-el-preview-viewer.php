<?php
/**
 * Engagement Letter Preview Viewer for Lawyers
 * 
 * Server-side rendered preview with page-by-page navigation for paginated docs
 * or continuous scroll for unpaginated docs. Shows exactly what will print.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * FEATURES:
 *   - Server-side page rendering (PDF never sent to browser)
 *   - WYSIWYG preview (exactly what prints)
 *   - Handles paginated and unpaginated documents
 *   - Page break indicators for unpaginated docs
 *   - Print View / Download PDF / Print PDF buttons
 *   - Shortcode integration for Tab 5
 * 
 * USAGE:
 *   Shortcode: [el_preview_viewer mode="paginated"]
 *   PHP: EL_Preview_Viewer::render_viewer($mode)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Preview_Viewer {
    
    /**
     * Viewer modes
     */
    const MODE_PAGINATED = 'paginated';
    const MODE_CONTINUOUS = 'continuous';
    
    /**
     * Image quality for page rendering
     */
    const IMAGE_QUALITY = 90;
    
    /**
     * DPI for page rendering (higher = better quality, larger file)
     */
    const RENDER_DPI = 150;
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // AJAX handlers for page rendering
        add_action('wp_ajax_el_preview_render_page', [__CLASS__, 'ajax_render_page']);
        add_action('wp_ajax_el_preview_generate', [__CLASS__, 'ajax_generate_preview']);
        add_action('wp_ajax_el_preview_download', [__CLASS__, 'ajax_download_pdf']);
        add_action('wp_ajax_el_preview_print', [__CLASS__, 'ajax_print_pdf']);
        
        // Shortcode
        add_shortcode('el_preview_viewer', [__CLASS__, 'shortcode']);
    }
    
    /**
     * Shortcode handler
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function shortcode($atts) {
        $atts = shortcode_atts([
            'mode' => self::MODE_PAGINATED,
            'auto_load' => 'true',
            'height' => '800px',
            'include_user_data' => 'true',
        ], $atts);
        
        return self::render_viewer($atts['mode'], $atts['auto_load'] === 'true', $atts['height'], $atts['include_user_data'] === 'true');
    }
    
    /**
     * Render viewer HTML
     * 
     * @param string $mode Viewer mode
     * @param bool $auto_load Auto-load preview on page load
     * @param string $height Container height
     * @param bool $include_user_data Include personal information
     * @return string HTML
     */
    public static function render_viewer($mode = self::MODE_PAGINATED, $auto_load = true, $height = '800px', $include_user_data = true) {
        $nonce = wp_create_nonce('el_preview_viewer');
        
        ob_start();
        ?>
        <div class="el-preview-viewer" data-mode="<?php echo esc_attr($mode); ?>" data-auto-load="<?php echo $auto_load ? '1' : '0'; ?>" data-include-user-data="<?php echo $include_user_data ? '1' : '0'; ?>">
            <!-- Toolbar -->
            <div class="el-preview-toolbar">
                <div class="toolbar-left">
                    <button type="button" class="el-btn el-btn-secondary" id="elPreviewGenerate">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                        </svg>
                        Generate Preview
                    </button>
                    
                    <span class="toolbar-separator"></span>
                    
                    <button type="button" class="el-btn el-btn-outline security-indicator" id="elSecurityInfo" title="Security Information">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                        <span class="security-badge">AES-256</span>
                    </button>
                    
                    <span class="toolbar-separator"></span>
                    
                    <div class="toggle-switch">
                        <label for="elIncludeUserData" class="toggle-label">
                            <input type="checkbox" id="elIncludeUserData" <?php checked($include_user_data, true); ?>>
                            <span class="toggle-slider"></span>
                            <span class="toggle-text">Include Personal Information</span>
                        </label>
                    </div>
                    
                    <span class="toolbar-separator"></span>
                    
                    <div class="mode-toggle">
                        <label>
                            <input type="radio" name="preview_mode" value="<?php echo self::MODE_PAGINATED; ?>" <?php checked($mode, self::MODE_PAGINATED); ?>>
                            <span>Paginated</span>
                        </label>
                        <label>
                            <input type="radio" name="preview_mode" value="<?php echo self::MODE_CONTINUOUS; ?>" <?php checked($mode, self::MODE_CONTINUOUS); ?>>
                            <span>Continuous</span>
                        </label>
                    </div>
                </div>
                
                <div class="toolbar-right">
                    <button type="button" class="el-btn el-btn-outline" id="elPreviewPrintView" disabled>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                        Print View
                    </button>
                    
                    <button type="button" class="el-btn el-btn-outline" id="elPreviewDownload" disabled>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        Download PDF
                    </button>
                    
                    <button type="button" class="el-btn el-btn-primary" id="elPreviewPrint" disabled>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                        </svg>
                        Print PDF
                    </button>
                </div>
            </div>
            
            <!-- Status bar -->
            <div class="el-preview-status" id="elPreviewStatus" style="display: none;">
                <div class="status-content"></div>
            </div>
            
            <!-- Viewer container -->
            <div class="el-preview-container" id="elPreviewContainer" style="height: <?php echo esc_attr($height); ?>;">
                <div class="preview-empty">
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                    </svg>
                    <p>Click "Generate Preview" to see your document</p>
                </div>
            </div>
            
            <!-- Navigation (paginated mode only) -->
            <div class="el-preview-nav" id="elPreviewNav" style="display: none;">
                <button type="button" class="nav-btn" id="elNavFirst" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11 2.5v11L5.5 8 11 2.5z"/>
                        <path d="M4.5 2v12H3V2h1.5z"/>
                    </svg>
                </button>
                
                <button type="button" class="nav-btn" id="elNavPrev" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11 2.5v11L5.5 8 11 2.5z"/>
                    </svg>
                </button>
                
                <div class="page-indicator">
                    <span>Page</span>
                    <input type="number" id="elPageInput" value="1" min="1" disabled>
                    <span>of <span id="elTotalPages">1</span></span>
                </div>
                
                <button type="button" class="nav-btn" id="elNavNext" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M5 2.5v11l5.5-5.5L5 2.5z"/>
                    </svg>
                </button>
                
                <button type="button" class="nav-btn" id="elNavLast" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M5 2.5v11l5.5-5.5L5 2.5z"/>
                        <path d="M11.5 2v12H13V2h-1.5z"/>
                    </svg>
                </button>
            </div>
            
            <input type="hidden" id="elPreviewNonce" value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" id="elPreviewReference" value="">
            <input type="hidden" id="elPreviewFileId" value="">
            
            <!-- Security Information Modal -->
            <div class="el-security-modal" id="elSecurityModal" style="display: none;">
                <div class="security-modal-overlay"></div>
                <div class="security-modal-content">
                    <div class="security-modal-header">
                        <svg width="32" height="32" fill="#10b981" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                        <h3>AES-256 Secure Viewer</h3>
                        <button type="button" class="modal-close" id="elSecurityModalClose">&times;</button>
                    </div>
                    <div class="security-modal-body">
                        <p class="security-intro">Your engagement letter documents are protected with military-grade encryption.</p>
                        
                        <div class="security-features">
                            <div class="security-feature">
                                <svg width="20" height="20" fill="#10b981" viewBox="0 0 16 16">
                                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                                </svg>
                                <div>
                                    <strong>AES-256-GCM Encryption</strong>
                                    <p>Document content is stored fully encrypted at rest using Advanced Encryption Standard with 256-bit keys</p>
                                </div>
                            </div>
                            
                            <div class="security-feature">
                                <svg width="20" height="20" fill="#10b981" viewBox="0 0 16 16">
                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                                </svg>
                                <div>
                                    <strong>Secure Decryption</strong>
                                    <p>Documents are decrypted securely in this viewer and never transmitted unencrypted over the network</p>
                                </div>
                            </div>
                            
                            <div class="security-feature">
                                <svg width="20" height="20" fill="#10b981" viewBox="0 0 16 16">
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                </svg>
                                <div>
                                    <strong>Server-Side Rendering</strong>
                                    <p>PDF content is rendered to images on the server. The actual PDF file is never sent to your browser</p>
                                </div>
                            </div>
                            
                            <div class="security-feature">
                                <svg width="20" height="20" fill="#10b981" viewBox="0 0 16 16">
                                    <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5z"/>
                                    <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
                                </svg>
                                <div>
                                    <strong>Access Logging</strong>
                                    <p>Every view and download is logged with timestamp and IP address for complete audit trails</p>
                                </div>
                            </div>
                            
                            <div class="security-feature">
                                <svg width="20" height="20" fill="#10b981" viewBox="0 0 16 16">
                                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                </svg>
                                <div>
                                    <strong>GDPR Compliant</strong>
                                    <p>Document storage and access controls comply with European data protection regulations</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="security-footer">
                            <p><strong>Encryption Key Management:</strong> All encryption keys are stored securely in server configuration files, never in the database. Per-file initialization vectors ensure unique encryption for every document.</p>
                        </div>
                    </div>
                    <div class="security-modal-footer">
                        <button type="button" class="el-btn el-btn-primary" id="elSecurityModalOk">Got it</button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .el-preview-viewer {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Toolbar */
        .el-preview-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .toolbar-separator {
            width: 1px;
            height: 24px;
            background: #cbd5e1;
            margin: 0 4px;
        }
        
        /* Mode toggle */
        .mode-toggle {
            display: flex;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .mode-toggle label {
            display: flex;
            align-items: center;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 13px;
            margin: 0;
            transition: background 0.2s;
        }
        
        .mode-toggle label:hover {
            background: #f1f5f9;
        }
        
        .mode-toggle input[type="radio"] {
            display: none;
        }
        
        .mode-toggle input[type="radio"]:checked + span {
            background: #3b82f6;
            color: #fff;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 4px;
            margin: -6px -12px;
            padding: 6px 12px;
        }
        
        /* Toggle switch */
        .toggle-switch {
            display: flex;
            align-items: center;
        }
        
        .toggle-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin: 0;
            user-select: none;
        }
        
        .toggle-label input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: relative;
            width: 44px;
            height: 24px;
            background: #cbd5e1;
            border-radius: 12px;
            transition: background 0.2s;
        }
        
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            top: 3px;
            left: 3px;
            transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .toggle-label input[type="checkbox"]:checked + .toggle-slider {
            background: #3b82f6;
        }
        
        .toggle-label input[type="checkbox"]:checked + .toggle-slider::before {
            transform: translateX(20px);
        }
        
        .toggle-text {
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }
        
        .toggle-label input[type="checkbox"]:checked ~ .toggle-text {
            color: #1e293b;
        }
        
        /* Buttons */
        .el-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .el-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .el-btn-primary {
            background: #3b82f6;
            color: #fff;
        }
        
        .el-btn-primary:hover:not(:disabled) {
            background: #2563eb;
        }
        
        .el-btn-secondary {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .el-btn-secondary:hover:not(:disabled) {
            background: #e2e8f0;
        }
        
        .el-btn-outline {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        
        .el-btn-outline:hover:not(:disabled) {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        
        /* Security indicator */
        .security-indicator {
            position: relative;
        }
        
        .security-badge {
            background: #10b981;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }
        
        .security-indicator:hover .security-badge {
            background: #059669;
        }
        
        /* Security modal */
        .el-security-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .security-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .security-modal-content {
            position: relative;
            background: #fff;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .security-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 24px 24px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .security-modal-header h3 {
            flex: 1;
            margin: 0;
            font-size: 20px;
            color: #1e293b;
        }
        
        .modal-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            font-size: 28px;
            color: #64748b;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .modal-close:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .security-modal-body {
            padding: 24px;
        }
        
        .security-intro {
            font-size: 15px;
            color: #475569;
            margin: 0 0 24px 0;
            line-height: 1.6;
        }
        
        .security-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .security-feature {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        
        .security-feature svg {
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .security-feature strong {
            display: block;
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .security-feature p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }
        
        .security-footer {
            margin-top: 24px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .security-footer p {
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
            margin: 0;
        }
        
        .security-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }
        
        /* Status bar */
        .el-preview-status {
            padding: 12px 16px;
            background: #f0f9ff;
            border-bottom: 1px solid #e0f2fe;
        }
        
        .el-preview-status.success {
            background: #f0fdf4;
            border-color: #dcfce7;
        }
        
        .el-preview-status.error {
            background: #fef2f2;
            border-color: #fee2e2;
        }
        
        .status-content {
            font-size: 13px;
            color: #0c4a6e;
        }
        
        .el-preview-status.success .status-content {
            color: #14532d;
        }
        
        .el-preview-status.error .status-content {
            color: #7f1d1d;
        }
        
        /* Container */
        .el-preview-container {
            position: relative;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px;
        }
        
        .preview-empty {
            text-align: center;
            color: #94a3b8;
            padding: 60px 20px;
        }
        
        .preview-empty svg {
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .preview-empty p {
            margin: 0;
            font-size: 14px;
        }
        
        /* Loading state */
        .preview-loading {
            text-align: center;
            padding: 60px 20px;
        }
        
        .preview-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .preview-loading p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        
        /* Page content (paginated) */
        .preview-page {
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 794px; /* A4 width */
            width: 100%;
        }
        
        .preview-page img {
            display: block;
            width: 100%;
            height: auto;
        }
        
        /* Continuous scroll content */
        .preview-continuous {
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 794px;
            width: 100%;
        }
        
        .preview-continuous img {
            display: block;
            width: 100%;
            height: auto;
        }
        
        .page-break-indicator {
            position: relative;
            height: 2px;
            background: linear-gradient(to right, transparent 0%, #cbd5e1 50%, transparent 100%);
            margin: 20px 0;
        }
        
        .page-break-indicator::before {
            content: 'Page Break';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 8px;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        
        /* Navigation */
        .el-preview-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .nav-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .nav-btn:hover:not(:disabled) {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        
        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .page-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #475569;
        }
        
        #elPageInput {
            width: 50px;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-align: center;
            font-size: 13px;
        }
        
        #elPageInput:focus {
            outline: none;
            border-color: #3b82f6;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * AJAX: Generate preview
     */
public static function ajax_generate_preview() {
    try {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'el_preview_viewer')) {
            wp_send_json_error(['message' => 'DEBUG: Nonce check failed']);
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'DEBUG: Permission denied']);
        }
        
        $mode = sanitize_text_field($_POST['mode'] ?? self::MODE_PAGINATED);
        $include_user_data = isset($_POST['include_user_data']) ? $_POST['include_user_data'] === 'true' : true;
        $paper_only = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
        
        // Check if class exists
        if (!class_exists('EL_Print_Data_Assembler')) {
            wp_send_json_error(['message' => 'DEBUG: EL_Print_Data_Assembler class not found']);
        }
        
        // Assemble data
        $data = EL_Print_Data_Assembler::assemble_data();
        
        if (is_wp_error($data)) {
            wp_send_json_error(['message' => 'DEBUG: assemble_data error - ' . $data->get_error_message()]);
        }
        
        if (empty($data)) {
            wp_send_json_error(['message' => 'DEBUG: assemble_data returned empty']);
        }
        
        // Check mPDF class
        if (!class_exists('EL_MPDF_Generator')) {
            wp_send_json_error(['message' => 'DEBUG: EL_MPDF_Generator class not found']);
        }
        
        // Check mPDF availability
        if (!EL_MPDF_Generator::is_mpdf_available()) {
            wp_send_json_error(['message' => 'DEBUG: mPDF not available - is Gravity PDF installed?']);
        }
        
        // Generate PDF
        $generator = new EL_MPDF_Generator();
     $result = $generator->generate_from_data($data, $paper_only, '', $include_user_data);
        
        if (!$result['success']) {
            wp_send_json_error(['message' => 'DEBUG: PDF generation failed - ' . $result['error']]);
        }
        
  // Store in session - force update
if (!session_id() && !headers_sent()) {
    session_start();
}

// Clear old values first
unset($_SESSION['el_preview_pdf_path']);
unset($_SESSION['el_preview_reference']);
unset($_SESSION['el_preview_mode']);
unset($_SESSION['el_preview_total_pages']);

// Set new values
$_SESSION['el_preview_pdf_path'] = $result['pdf_path'];
$_SESSION['el_preview_reference'] = $data['reference'];
$_SESSION['el_preview_mode'] = $mode;
$_SESSION['el_preview_total_pages'] = $result['total_pages'];

// Force session write
session_write_close();
session_start();

error_log('EL Preview: Stored total_pages = ' . $result['total_pages']);
        
        wp_send_json_success([
            'reference' => $data['reference'],
            'total_pages' => $result['total_pages'],
            'mode' => $mode,
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'DEBUG: Exception - ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    } catch (Error $e) {
        wp_send_json_error(['message' => 'DEBUG: Fatal Error - ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    }
}
    
    /**
     * AJAX: Render single page as image
     */
    public static function ajax_render_page() {
        check_ajax_referer('el_preview_viewer', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        if (!session_id()) {
            session_start();
        }
        
        $pdf_path = $_SESSION['el_preview_pdf_path'] ?? '';
        $page_num = intval($_POST['page'] ?? 1);
        
        if (empty($pdf_path) || !file_exists($pdf_path)) {
            wp_send_json_error(['message' => 'PDF not found. Please generate preview first.']);
        }
        
        // Render page to image
        $image_data = self::render_page_to_image($pdf_path, $page_num);
        
        if ($image_data === false) {
            wp_send_json_error(['message' => 'Failed to render page']);
        }
        
        wp_send_json_success([
            'page' => $page_num,
            'image' => 'data:image/jpeg;base64,' . base64_encode($image_data),
        ]);
    }
    
    /**
     * Render PDF page to JPEG image
     * 
     * @param string $pdf_path Path to PDF
     * @param int $page_num Page number (1-indexed)
     * @return string|false JPEG binary data or false
     */
    private static function render_page_to_image($pdf_path, $page_num) {
        // Check for Imagick
        if (!class_exists('Imagick')) {
            error_log('EL Preview: Imagick not available');
            return false;
        }
        
        try {
            $imagick = new Imagick();
            
            // Resource safeguard: Set memory and disk limits to prevent runaway processes
            // 256MB per render, 512MB disk map
            if (method_exists($imagick, 'setResourceLimit')) {
                $imagick->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 256 * 1024 * 1024);
                $imagick->setResourceLimit(Imagick::RESOURCETYPE_MAP, 512 * 1024 * 1024);
                $imagick->setResourceLimit(Imagick::RESOURCETYPE_DISK, 1024 * 1024 * 1024); // 1GB disk max
            }
            
            $imagick->setResolution(self::RENDER_DPI, self::RENDER_DPI);
            $imagick->readImage($pdf_path . '[' . ($page_num - 1) . ']');
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(self::IMAGE_QUALITY);
            
            $image_data = $imagick->getImageBlob();
            
            // Clean up Imagick resources immediately
            $imagick->clear();
            $imagick->destroy();
            
            return $image_data;
            
        } catch (Exception $e) {
            error_log('EL Preview: Imagick error - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * AJAX: Download PDF
     */
    public static function ajax_download_pdf() {
        check_ajax_referer('el_preview_viewer', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        if (!session_id()) {
            session_start();
        }
        
        $pdf_path = $_SESSION['el_preview_pdf_path'] ?? '';
        $reference = $_SESSION['el_preview_reference'] ?? 'document';
        
        if (empty($pdf_path) || !file_exists($pdf_path)) {
            wp_die('PDF not found');
        }
        
        $filename = 'engagement-letter-' . $reference . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($pdf_path));
        
        readfile($pdf_path);
        exit;
    }
    
    /**
     * AJAX: Print PDF (inline view)
     */
    public static function ajax_print_pdf() {
        check_ajax_referer('el_preview_viewer', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        if (!session_id()) {
            session_start();
        }
        
        $pdf_path = $_SESSION['el_preview_pdf_path'] ?? '';
        
        if (empty($pdf_path) || !file_exists($pdf_path)) {
            wp_die('PDF not found');
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="preview.pdf"');
        
        readfile($pdf_path);
        exit;
    }
}

// Initialize
EL_Preview_Viewer::init();