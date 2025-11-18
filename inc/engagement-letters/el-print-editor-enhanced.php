<?php
/**
 * Enhanced Print Editor Shortcode
 * Drop-in replacement for [el_print_editor] with pagination and page signatures
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include dependencies
require_once plugin_dir_path(__FILE__) . 'class-el-pagination.php';

/**
 * Enhanced shortcode: [el_print_editor]
 * Drop-in replacement with pagination support
 */
add_shortcode('el_print_editor', 'el_print_editor_enhanced_shortcode');

function el_print_editor_enhanced_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts([
        'paper_only' => 'auto', // 'auto', 'true', 'false'
        'show_signatures' => 'true'
    ], $atts);
    
    // Check if current user can edit
    $current_user = wp_get_current_user();
    $can_edit = false;
    
    if ($current_user->ID) {
        $can_edit = get_user_meta($current_user->ID, 'el_can_edit_documents', true);
    }
    
    // Enqueue enhanced scripts and styles
    wp_enqueue_script('el-print-editor-enhanced', plugin_dir_url(__FILE__) . 'js/el-print-editor-enhanced.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('el-print-editor-enhanced', plugin_dir_url(__FILE__) . 'css/el-print-editor-enhanced.css', array(), '1.0.0');
    
    // Pass configuration to JavaScript
    wp_localize_script('el-print-editor-enhanced', 'el_print_config', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('el_nonce'),
        'can_edit' => $can_edit,
        'paper_only_default' => get_option('el_default_paper_only', '0'),
        'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
    ));
    
    ob_start();
    ?>
    <div id="el-print-editor-wrapper" class="el-editor-container el-enhanced">
        <!-- Toolbar -->
        <div class="el-editor-toolbar">
            <button type="button" id="el-load-print-content" class="el-btn el-btn-primary">
                <span class="dashicons dashicons-download"></span> Load Document
            </button>
            
            <?php if ($can_edit): ?>
            <!-- Save button -->
            <button type="button" id="el-save-print-content" class="el-btn el-btn-success" style="display:none;">
                <span class="dashicons dashicons-saved"></span> Save & Generate Link
            </button>
            
            <!-- Edit toolbar -->
            <div class="el-edit-toolbar" style="display:none; margin-left: 20px;">
                <button type="button" class="el-format-btn" data-command="bold" title="Bold">
                    <strong>B</strong>
                </button>
                <button type="button" class="el-format-btn" data-command="italic" title="Italic">
                    <em>I</em>
                </button>
                <button type="button" class="el-format-btn" data-command="underline" title="Underline">
                    <u>U</u>
                </button>
                <button type="button" class="el-format-btn" data-command="justifyLeft" title="Align Left">
                    ☰
                </button>
                <button type="button" class="el-format-btn" data-command="justifyCenter" title="Align Center">
                    ≡
                </button>
                <button type="button" class="el-format-btn" data-command="justifyRight" title="Align Right">
                    ☷
                </button>
                <span class="separator">|</span>
                <button type="button" class="el-format-btn" data-command="insertOrderedList" title="Numbered List">
                    1.
                </button>
                <button type="button" class="el-format-btn" data-command="insertUnorderedList" title="Bullet List">
                    •
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Paper-only toggle -->
            <div class="el-print-options" style="display:none; margin-left: 20px;">
                <label class="el-toggle-wrapper">
                    <input type="checkbox" id="el-paper-only-toggle" <?php checked(get_option('el_default_paper_only'), '1'); ?>>
                    <span class="el-toggle-label">Paper-Only Mode</span>
                    <span class="el-toggle-help" title="Adds signature lines to each page for physical printing">?</span>
                </label>
            </div>
            
            <!-- Share link container -->
            <div id="el-share-link-container" style="display:none; margin-left:20px; flex:1;">
                <div style="display:flex; gap:10px; align-items:center; background:#f0f9ff; padding:10px; border-radius:6px; border:2px solid #3b82f6;">
                    <span style="font-weight:600; color:#1e40af;">📎 Share Link:</span>
                    <input type="text" id="el-share-link" readonly style="flex:1; padding:8px; border:1px solid #bfdbfe; border-radius:4px; font-family:monospace; font-size:12px;">
                    <button type="button" id="el-copy-link" class="el-btn el-btn-accent">
                        <span class="dashicons dashicons-clipboard"></span> Copy
                    </button>
                </div>
                <p style="margin:5px 0 0 0; font-size:12px; color:#64748b;">
                    ⏱ Link expires in 14 days | 🔒 Encrypted & secure
                </p>
            </div>
            
            <!-- Status indicator -->
            <div class="el-editor-status"></div>
        </div>
        
        <!-- Page info bar -->
        <div class="el-page-info-bar" style="display:none;">
            <div class="el-page-stats">
                <span class="el-stat">
                    <strong>Pages:</strong> <span id="el-total-pages">0</span>
                </span>
                <span class="el-stat">
                    <strong>Mode:</strong> <span id="el-print-mode">Digital</span>
                </span>
                <span class="el-stat" id="el-signature-indicator" style="display:none;">
                    <span class="dashicons dashicons-edit"></span> Page signatures enabled
                </span>
            </div>
            <div class="el-view-controls">
                <button type="button" class="el-view-btn active" data-view="edit">
                    <span class="dashicons dashicons-edit"></span> Edit View
                </button>
                <button type="button" class="el-view-btn" data-view="preview">
                    <span class="dashicons dashicons-visibility"></span> Print Preview
                </button>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="el-print-editor-loading" style="display:none; text-align:center; padding:40px;">
            <div class="spinner" style="visibility:visible; float:none; margin:0 auto 20px;"></div>
            <p>Loading print-ready document with pagination optimization...</p>
        </div>
        
        <?php if ($can_edit): ?>
        <!-- Editable content area -->
        <div id="el-print-editor-content" 
             contenteditable="true" 
             style="display:none; background:#e5e5e5; padding:20px; overflow-y:auto; min-height:80vh; outline:none;"
             data-paper-only="false">
            <!-- Content will be loaded here -->
        </div>
        <?php else: ?>
        <!-- Read-only preview -->
        <div id="el-print-preview-readonly" style="display:none; background:#e5e5e5; padding:20px; overflow-y:auto; max-height:80vh;">
            <!-- Print preview will be loaded here -->
        </div>
        <?php endif; ?>
        
        <!-- Print preview overlay (for preview mode) -->
        <div id="el-print-preview-overlay" class="el-preview-overlay" style="display:none;">
            <div class="el-preview-header">
                <h3>Print Preview</h3>
                <button type="button" class="el-close-preview">✕</button>
            </div>
            <div class="el-preview-content">
                <!-- Preview will be rendered here -->
            </div>
            <div class="el-preview-footer">
                <button type="button" class="el-btn el-btn-primary" id="el-print-now">
                    <span class="dashicons dashicons-printer"></span> Print Now
                </button>
                <button type="button" class="el-btn" id="el-download-pdf-preview">
                    <span class="dashicons dashicons-pdf"></span> Download PDF
                </button>
            </div>
        </div>
        
        <!-- Bottom action bar -->
        <div class="el-editor-actions" style="display:none;">
            <?php if (!$can_edit): ?>
            <button type="button" id="el-download-final-pdf" class="el-btn el-btn-primary">
                <span class="dashicons dashicons-download"></span> Download PDF
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Enhanced AJAX handler for loading print editor content
 */
add_action('wp_ajax_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');
add_action('wp_ajax_nopriv_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');

function el_ajax_load_print_editor_enhanced() {
    check_ajax_referer('el_nonce', 'nonce');
    
    if (!session_id()) {
        session_start();
    }
    
    // Get PDF reference from session
    $pdf_reference = $_SESSION['el_pdf_reference'] ?? '';
    
    if (empty($pdf_reference)) {
        wp_send_json_error(['message' => 'No PDF data found. Please generate preview first.']);
        return;
    }
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $pdf_reference);
    
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'PDF data expired. Please regenerate.']);
        return;
    }
    
    // Check if paper-only mode
    $is_paper_only = EL_Pagination_Handler::is_paper_only($pdf_data);
    
    // Generate HTML with pagination
    $pagination_options = [
        'paper_only' => $is_paper_only,
        'add_page_signatures' => $is_paper_only,
        'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
    ];
    
    // Get base HTML
    $base_html = el_render_print_ready_html_enhanced($pdf_data);
    
    // Apply pagination
    $paginated_result = EL_Pagination_Handler::paginate_content($base_html, $pagination_options);
    
    wp_send_json_success([
        'reference' => $pdf_reference,
        'html' => $paginated_result['html'],
        'total_pages' => $paginated_result['total_pages'],
        'paper_only' => $is_paper_only,
        'can_edit' => current_user_can('edit_documents')
    ]);
}

/**
 * Enhanced AJAX handler for saving edited content
 */
add_action('wp_ajax_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');
add_action('wp_ajax_nopriv_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');

function el_ajax_save_edited_pdf_enhanced() {
    check_ajax_referer('el_nonce', 'nonce');
    
    // Check permissions
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'Not logged in']);
        return;
    }
    
    $can_edit = get_user_meta($current_user->ID, 'el_can_edit_documents', true);
    if (!$can_edit) {
        wp_send_json_error(['message' => 'You do not have permission to edit documents']);
        return;
    }
    
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    $edited_html = wp_kses_post($_POST['html'] ?? '');
    $paper_only = filter_var($_POST['paper_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
    
    if (empty($reference) || empty($edited_html)) {
        wp_send_json_error(['message' => 'Missing required data']);
        return;
    }
    
    // Get original PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'Reference expired']);
        return;
    }
    
    // Re-apply pagination to edited content if needed
    if ($paper_only) {
        $pagination_options = [
            'paper_only' => true,
            'add_page_signatures' => true,
            'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
        ];
        
        $paginated_result = EL_Pagination_Handler::paginate_content($edited_html, $pagination_options);
        $final_html = $paginated_result['html'];
        $pdf_data['total_pages'] = $paginated_result['total_pages'];
    } else {
        $final_html = $edited_html;
    }
    
    // Store edited version
    $pdf_data['edited_html'] = $final_html;
    $pdf_data['edited_by'] = $current_user->ID;
    $pdf_data['edited_at'] = current_time('mysql');
    $pdf_data['paper_only'] = $paper_only;
    
    // Update transient with longer expiry for sharing
    set_transient('el_pdf_data_' . $reference, $pdf_data, 14 * DAY_IN_SECONDS);
    
    // Generate shareable link
    $share_url = add_query_arg([
        'el_view' => 'engagement_letter',
        'ref' => $reference,
        'token' => wp_generate_password(20, false)
    ], home_url());
    
    // Store share token
    set_transient('el_share_' . $reference, [
        'token' => wp_hash($reference . $pdf_data['edited_at']),
        'expires' => time() + (14 * DAY_IN_SECONDS)
    ], 14 * DAY_IN_SECONDS);
    
    // Log in print history
    el_log_print_activity($reference, 'saved', $paper_only);
    
    wp_send_json_success([
        'message' => 'Document saved successfully',
        'share_url' => $share_url,
        'reference' => $reference,
        'total_pages' => $pdf_data['total_pages'] ?? 1,
        'paper_only' => $paper_only
    ]);
}

/**
 * AJAX handler to toggle paper-only mode
 */
add_action('wp_ajax_el_toggle_paper_only', 'el_ajax_toggle_paper_only');
add_action('wp_ajax_nopriv_el_toggle_paper_only', 'el_ajax_toggle_paper_only');

function el_ajax_toggle_paper_only() {
    check_ajax_referer('el_nonce', 'nonce');
    
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    $paper_only = filter_var($_POST['paper_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $content = wp_kses_post($_POST['content'] ?? '');
    
    if (empty($reference)) {
        wp_send_json_error(['message' => 'Missing reference']);
        return;
    }
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'Reference expired']);
        return;
    }
    
    // Update paper-only setting
    $pdf_data['paper_only'] = $paper_only;
    
    // Re-paginate content
    $pagination_options = [
        'paper_only' => $paper_only,
        'add_page_signatures' => $paper_only,
        'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
    ];
    
    // Use provided content or existing HTML
    $html_to_paginate = !empty($content) ? $content : ($pdf_data['edited_html'] ?? el_render_print_ready_html_enhanced($pdf_data));
    
    $paginated_result = EL_Pagination_Handler::paginate_content($html_to_paginate, $pagination_options);
    
    // Update transient
    $pdf_data['edited_html'] = $paginated_result['html'];
    $pdf_data['total_pages'] = $paginated_result['total_pages'];
    set_transient('el_pdf_data_' . $reference, $pdf_data, HOUR_IN_SECONDS);
    
    wp_send_json_success([
        'html' => $paginated_result['html'],
        'total_pages' => $paginated_result['total_pages'],
        'paper_only' => $paper_only,
        'message' => $paper_only ? 'Paper-only mode enabled' : 'Digital mode enabled'
    ]);
}

/**
 * Log print activity
 */
function el_log_print_activity($reference, $action, $paper_only = false) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'el_print_history';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        $wpdb->insert(
            $table_name,
            [
                'reference' => $reference,
                'user_id' => get_current_user_id(),
                'action' => $action,
                'paper_only' => $paper_only ? 1 : 0,
                'timestamp' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%d', '%s']
        );
    }
}

/**
 * Handle viewing shared engagement letters
 */
add_action('template_redirect', 'el_handle_shared_view');
function el_handle_shared_view() {
    if (get_query_var('el_view') !== 'engagement_letter') {
        return;
    }
    
    $reference = sanitize_text_field($_GET['ref'] ?? '');
    $token = sanitize_text_field($_GET['token'] ?? '');
    
    if (empty($reference) || empty($token)) {
        wp_die('Invalid link');
    }
    
    // Verify token
    $share_data = get_transient('el_share_' . $reference);
    if (!$share_data || $share_data['expires'] < time()) {
        wp_die('Link expired');
    }
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    if (!$pdf_data) {
        wp_die('Document not found');
    }
    
    // Display the document
    el_display_shared_document($pdf_data, $reference);
    exit;
}

/**
 * Display shared document
 */
function el_display_shared_document($pdf_data, $reference) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Engagement Letter - <?php echo esc_html($reference); ?></title>
        <?php echo el_get_print_styles($pdf_data['paper_only'] ?? false); ?>
        <style>
            .viewer-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #1e293b;
                color: white;
                padding: 15px 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }
            
            .viewer-header h1 {
                margin: 0;
                font-size: 18px;
                font-weight: 500;
            }
            
            .viewer-actions {
                display: flex;
                gap: 10px;
            }
            
            .viewer-btn {
                padding: 8px 16px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: background 0.2s;
            }
            
            .viewer-btn:hover {
                background: #2563eb;
            }
            
            .viewer-content {
                margin-top: 70px;
                padding: 20px;
                background: #e5e5e5;
            }
            
            @media print {
                .viewer-header {
                    display: none;
                }
                
                .viewer-content {
                    margin-top: 0;
                    padding: 0;
                    background: white;
                }
            }
        </style>
    </head>
    <body>
        <div class="viewer-header">
            <h1>Engagement Letter - <?php echo esc_html($reference); ?></h1>
            <div class="viewer-actions">
                <button onclick="window.print()" class="viewer-btn">
                    📄 Print
                </button>
                <a href="?el_download=1&ref=<?php echo esc_attr($reference); ?>" class="viewer-btn">
                    💾 Download PDF
                </a>
            </div>
        </div>
        <div class="viewer-content">
            <?php echo $pdf_data['edited_html'] ?? el_render_print_ready_html_enhanced($pdf_data); ?>
        </div>
    </body>
    </html>
    <?php
}