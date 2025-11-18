<?php
/**
 * Enhanced Print Editor Shortcode
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include dependencies using theme directory path
$theme_dir = get_stylesheet_directory();
if (file_exists($theme_dir . '/class-el-pagination.php')) {
    require_once $theme_dir . '/class-el-pagination.php';
}

/**
 * Enhanced shortcode: [el_print_editor]
 * Drop-in replacement with pagination support
 */
if (!shortcode_exists('el_print_editor')) {
    add_shortcode('el_print_editor', 'el_print_editor_enhanced_shortcode');
}

function el_print_editor_enhanced_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts([
        'paper_only' => 'auto',
        'show_signatures' => 'true'
    ], $atts);
    
    // Check if current user can edit
    $current_user = wp_get_current_user();
    $can_edit = false;
    
    if ($current_user->ID) {
        $can_edit = get_user_meta($current_user->ID, 'el_can_edit_documents', true);
    }
    
    // FIX: Use proper theme URLs (not plugin URLs!)
    $theme_url = get_stylesheet_directory_uri();
    
    // Check if files exist and enqueue them with CORRECT URLs
    $js_file = '/js/el-print-editor-enhanced.js';
    $css_file = '/css/el-print-editor-enhanced.css';
    
    // Only enqueue if files exist
    if (file_exists(get_stylesheet_directory() . $js_file)) {
        wp_enqueue_script(
            'el-print-editor-enhanced', 
            $theme_url . $js_file, 
            array('jquery'), 
            '1.0.1', 
            true
        );
    } else {
        // Log error but don't break the site
        error_log('EL Print Editor: JavaScript file not found at ' . get_stylesheet_directory() . $js_file);
    }
    
    if (file_exists(get_stylesheet_directory() . $css_file)) {
        wp_enqueue_style(
            'el-print-editor-enhanced', 
            $theme_url . $css_file, 
            array(), 
            '1.0.1'
        );
    } else {
        // Log error but don't break the site
        error_log('EL Print Editor: CSS file not found at ' . get_stylesheet_directory() . $css_file);
    }
    
    // Localize script with proper data
    wp_localize_script('el-print-editor-enhanced', 'el_print_config', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('el_print_editor_nonce'),
        'paper_only_default' => get_option('el_default_paper_only', '0'),
        'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d'),
        'is_admin' => current_user_can('manage_options'),
        'can_edit' => $can_edit,
        'current_user_id' => get_current_user_id(),
        'theme_url' => $theme_url,
        'debug' => defined('WP_DEBUG') && WP_DEBUG
    ));
    
    // Output HTML structure
    ob_start();
    ?>
    
    <div class="el-editor-container">
        <!-- Toolbar -->
        <div class="el-editor-toolbar">
            <!-- Action buttons -->
            <button type="button" class="el-btn el-btn-primary" id="el-print-btn">
                <span class="dashicons dashicons-printer"></span> Print
            </button>
            
            <button type="button" class="el-btn el-btn-accent" id="el-download-btn">
                <span class="dashicons dashicons-download"></span> Download PDF
            </button>
            
            <?php if ($can_edit): ?>
            <button type="button" class="el-btn el-btn-success" id="el-save-btn">
                <span class="dashicons dashicons-saved"></span> Save Changes
            </button>
            
            <!-- Edit toolbar -->
            <div class="el-edit-toolbar">
                <button type="button" class="el-format-btn" data-command="bold" title="Bold">
                    <strong>B</strong>
                </button>
                <button type="button" class="el-format-btn" data-command="italic" title="Italic">
                    <em>I</em>
                </button>
                <button type="button" class="el-format-btn" data-command="underline" title="Underline">
                    <u>U</u>
                </button>
                <span class="separator">|</span>
                <button type="button" class="el-format-btn" data-command="justifyLeft" title="Align Left">
                    ⬅
                </button>
                <button type="button" class="el-format-btn" data-command="justifyCenter" title="Align Center">
                    ⬌
                </button>
                <button type="button" class="el-format-btn" data-command="justifyRight" title="Align Right">
                    ➡
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
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="el-print-editor-loading" style="display:none; text-align:center; padding:40px;">
            <div class="spinner" style="visibility:visible; float:none; margin:0 auto 20px;"></div>
            <p>Loading print-ready document...</p>
        </div>
        
        <?php if ($can_edit): ?>
        <!-- Editable content area -->
        <div id="el-print-editor-content" 
             contenteditable="true" 
             style="display:none; background:#f5f5f5; padding:20px; min-height:500px; border:1px solid #ddd; border-radius:4px;">
            <!-- Content will be loaded here -->
        </div>
        <?php else: ?>
        <!-- Read-only preview -->
        <div id="el-print-preview-readonly" 
             style="display:none; background:#f5f5f5; padding:20px; min-height:500px; border:1px solid #ddd; border-radius:4px;">
            <!-- Content will be loaded here -->
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Add inline styles if CSS file is missing -->
    <?php if (!file_exists(get_stylesheet_directory() . $css_file)): ?>
    <style>
    .el-editor-container {
        max-width: 100%;
        margin: 0 auto;
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .el-editor-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
        flex-wrap: wrap;
    }
    .el-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    .el-btn-primary {
        background: #4a90e2;
        color: white;
    }
    .el-btn-accent {
        background: #f59e0b;
        color: white;
    }
    .el-btn-success {
        background: #10b981;
        color: white;
    }
    .el-edit-toolbar {
        display: flex;
        gap: 5px;
        padding: 5px 10px;
        background: #f3f4f6;
        border-radius: 4px;
    }
    .el-format-btn {
        padding: 6px 10px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        cursor: pointer;
    }
    </style>
    <?php endif; ?>
    
    <?php
    return ob_get_clean();
}

// Include AJAX handlers only if they don't exist
if (!has_action('wp_ajax_el_load_print_editor')) {
    add_action('wp_ajax_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');
    add_action('wp_ajax_nopriv_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');
}

if (!has_action('wp_ajax_el_save_edited_pdf')) {
    add_action('wp_ajax_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');
    add_action('wp_ajax_nopriv_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');
}

/**
 * Enhanced AJAX handler for loading editor content
 */
function el_ajax_load_print_editor_enhanced() {
    // Verify nonce
    check_ajax_referer('el_print_editor_nonce', 'nonce');
    
    $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : '';
    $paper_only = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
    
    if (empty($reference)) {
        wp_send_json_error(['message' => 'No reference provided']);
    }
    
    // Get PDF data
    global $wpdb;
    $table_name = $wpdb->prefix . 'el_pdfs';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wp_send_json_error(['message' => 'PDF table not found. Please contact support.']);
    }
    
    $pdf_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE reference = %s",
        $reference
    ), ARRAY_A);
    
    if (!$pdf_data) {
        wp_send_json_error(['message' => 'Document not found']);
    }
    
    // Decrypt HTML content if encryption function exists
    $html = $pdf_data['html'];
    if (function_exists('el_decrypt_data')) {
        $decrypted = el_decrypt_data($html);
        if ($decrypted) {
            $html = $decrypted;
        }
    }
    
    // Apply pagination if requested and handler exists
    $response_data = [
        'html' => $html,
        'paper_only' => $paper_only,
        'pages' => 1,
        'reference' => $reference
    ];
    
    if ($paper_only && class_exists('EL_Pagination_Handler')) {
        $pagination_options = [
            'paper_only' => true,
            'add_page_signatures' => true,
            'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
        ];
        
        $paginated = EL_Pagination_Handler::paginate_content($html, $pagination_options);
        $response_data['html'] = $paginated['html'];
        $response_data['pages'] = $paginated['page_count'];
        $response_data['paginated'] = true;
    }
    
    wp_send_json_success($response_data);
}

/**
 * Enhanced AJAX handler for saving edited content
 */
function el_ajax_save_edited_pdf_enhanced() {
    // Verify nonce
    check_ajax_referer('el_print_editor_nonce', 'nonce');
    
    $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : '';
    $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
    $paper_only = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
    
    if (empty($reference) || empty($content)) {
        wp_send_json_error(['message' => 'Invalid data']);
    }
    
    // Check permissions
    $current_user = wp_get_current_user();
    $can_edit = get_user_meta($current_user->ID, 'el_can_edit_documents', true);
    
    if (!$can_edit) {
        wp_send_json_error(['message' => 'You do not have permission to edit documents']);
    }
    
    // Remove pagination markers if handler exists
    if (class_exists('EL_Pagination_Handler')) {
        $content = EL_Pagination_Handler::remove_pagination_markers($content);
    }
    
    // Encrypt if function exists
    $html_to_save = $content;
    if (function_exists('el_encrypt_data')) {
        $encrypted = el_encrypt_data($content);
        if ($encrypted) {
            $html_to_save = $encrypted;
        }
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'el_pdfs';
    
    $updated = $wpdb->update(
        $table_name,
        [
            'html' => $html_to_save,
            'updated_at' => current_time('mysql')
        ],
        ['reference' => $reference]
    );
    
    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to save changes']);
    }
    
    wp_send_json_success([
        'message' => 'Changes saved successfully',
        'timestamp' => current_time('mysql')
    ]);
}