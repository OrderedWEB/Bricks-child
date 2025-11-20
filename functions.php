<?php
/**
 * Engagement Letter System Functions - Fixed Theme Path Version
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * =================================================================
 * CENTRALIZED SESSION MANAGEMENT
 * Start session early in WordPress lifecycle before any output
 * =================================================================
 */

/**
 * Start session on WordPress init hook - runs before any output
 */

function el_init_session() {
    // Only start session if not already started
    if (!session_id() && !headers_sent()) {
        // Set session cookie parameters for better security
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Start the session
        session_start();
        
        // Log for debugging (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('EL Session started at init hook');
        }
    }
}

// Hook to init with priority 1 (very early)
add_action('init', 'el_init_session', 1);

/**
 * Alternative: Start session even earlier if needed
 * Uncomment if init is still too late
 */
// add_action('after_setup_theme', 'el_init_session', 1);

/**
 * Helper function to safely get session value
 */
function el_get_session($key, $default = null) {
    // Ensure session is started
    if (!session_id() && !headers_sent()) {
        // Session already started via init hook
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Helper function to safely set session value
 */
function el_set_session($key, $value) {
    // Ensure session is started
    if (!session_id() && !headers_sent()) {
        // Session already started via init hook
    }
    
    $_SESSION[$key] = $value;
}

/**
 * Helper function to safely unset session value
 */
function el_unset_session($key) {
    // Ensure session is started
    if (!session_id() && !headers_sent()) {
        // Session already started via init hook
    }
    
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Helper function to check if session is active
 */
function el_session_active() {
    return session_id() !== '';
}

/**
 * Clean up session on logout
 */
add_action('wp_logout', function() {
    if (session_id()) {
        session_destroy();
    }
});

/**
 * ================================================================
 * ENQUEUE SCRIPTS & STYLES
 * ================================================================
 */
add_action('wp_enqueue_scripts', 'el_print_editor_enqueue_scripts');

function el_print_editor_enqueue_scripts() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'el_print_editor')) {
        
        wp_enqueue_script(
            'el-print-editor-enhanced',
            get_stylesheet_directory_uri() . '/js/el-print-editor-enhanced.js',
            array('jquery'),
            '1.0.1',
            true
        );
        
        wp_localize_script('el-print-editor-enhanced', 'el_print_config', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('el_nonce'),
            'can_edit' => current_user_can('edit_posts') ? '1' : '0',
            'paper_only_default' => '0'
        ));
    }
}


/**
 * Central pagination handler - single page version
 */
function el_apply_central_pagination($html_content, $options = []) {
    // Extract style block
    $style_block = '';
    if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $html_content, $matches)) {
        $style_block = $matches[0];
        $html_content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html_content);
    }
    
    // Extract header (letterhead) - but keep it in content too
    $header_html = '';
    if (preg_match('/<div[^>]*class="[^"]*letterhead[^"]*"[^>]*>.*?<\/div>/is', $html_content, $matches)) {
        $header_html = $matches[0];
        // DON'T remove it from content
    }
    
    // Default options
    $defaults = [
        'paper_only' => true,
        'add_page_signatures' => false,
        'signature_format' => 'Client signature …………..……………………… Date ………… Page %d/%d',
        'lines_per_page' => 35,
        'force_new_page_sections' => false,
        'keep_signatures_together' => true
    ];
    
    $options = array_merge($defaults, $options);
    
    // Use pagination handler (which now returns single page)
    if (class_exists('EL_Pagination_Handler')) {
        $result = EL_Pagination_Handler::paginate_content($html_content, $options);
        $page_count = $result['page_count'];
        $pages_html = $result['html'];
    } else {
        $pages_html = $html_content;
        $page_count = 1;
    }
    
    // Build final HTML - single page with all content, NO PADDING
    $final_html = '<div class="print-page" data-page="1">';
    
    // Add all content (letterhead is already in it)
    $final_html .= $pages_html;
    
    // Add signature line at bottom
    if ($options['add_page_signatures']) {
        $signature_text = sprintf($options['signature_format'], 1, 1);
        $final_html .= '<div class="el-page-signature">' . esc_html($signature_text) . '</div>';
    }
    
    // Add page footer
    $final_html .= '<div class="page-footer">';
    $final_html .= '<div class="page-number">Page 1 of 1</div>';
    $final_html .= '</div>';
    
    $final_html .= '</div>';
    
    // Add minimal CSS
    $print_css = '
    <style>
    .print-page {
        margin: 0;
        padding: 0;
    }
    
    .el-page-signature {
        margin-top: 10mm;
        padding-top: 3mm;
        border-top: 1px solid #ccc;
    }
    
    .page-footer {
        margin-top: 5mm;
        padding-top: 3mm;
        border-top: 1px solid #ccc;
        text-align: center;
    }
    
    @media print {
        @page {
            size: A4;
            margin: 0;
        }
    }
    </style>
    ';
    
    // Add style blocks back at the top
    $final_html = $style_block . $print_css . $final_html;

    return [
        'html' => $final_html,
        'page_count' => 1
    ];
}
/**
 * =================================================================
 * ENHANCED PRINT EDITOR INTEGRATION
 * =================================================================
 */
function el_integrate_enhanced_print_editor() {
    // Get theme directory path
    $theme_dir = get_stylesheet_directory();
    
    // Only load if we haven't already loaded the original shortcode
    if (!shortcode_exists('el_print_editor')) {
        // Include the enhanced version using theme directory
        if (file_exists($theme_dir . '/el-print-editor-enhanced.php')) {
            require_once $theme_dir . '/el-print-editor-enhanced.php';
        }
        if (file_exists($theme_dir . '/class-el-pagination.php')) {
            require_once $theme_dir . '/class-el-pagination.php';
        }
        if (file_exists($theme_dir . '/el-print-enhanced.php')) {
            require_once $theme_dir . '/el-print-enhanced.php';
        }
    } else {
        // Remove the original shortcode and replace with enhanced
        remove_shortcode('el_print_editor');
        
        // Include enhanced files from theme directory
        if (file_exists($theme_dir . '/el-print-editor-enhanced.php')) {
            require_once $theme_dir . '/el-print-editor-enhanced.php';
        }
        if (file_exists($theme_dir . '/class-el-pagination.php')) {
            require_once $theme_dir . '/class-el-pagination.php';
        }
        if (file_exists($theme_dir . '/el-print-enhanced.php')) {
            require_once $theme_dir . '/el-print-enhanced.php';
        }
    }
}
add_action('init', 'el_integrate_enhanced_print_editor', 20);

/**
 * Create print history table
 */
function el_create_print_history_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'el_print_history';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        reference varchar(50) NOT NULL,
        user_id int(11) DEFAULT NULL,
        action varchar(50) DEFAULT NULL,
        paper_only tinyint(1) DEFAULT 0,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY reference (reference),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'el_create_print_history_table');

/**
 * Apply pagination filter
 */
add_filter('el_print_editor_html', function($html, $pdf_data) {
    // Check if paper-only mode
    $is_paper_only = false;
    if (class_exists('EL_Pagination_Handler')) {
        $is_paper_only = EL_Pagination_Handler::is_paper_only($pdf_data);
    }
    
    if ($is_paper_only) {
        // Apply pagination with page signatures
        $pagination_options = [
            'paper_only' => true,
            'add_page_signatures' => true,
            'signature_format' => get_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d')
        ];
        
        if (class_exists('EL_Pagination_Handler')) {
            $paginated_result = EL_Pagination_Handler::paginate_content($html, $pagination_options);
            return $paginated_result['html'];
        }
    }

    return $html;
}, 10, 2);

/**
 * Set enhanced defaults
 */
function el_set_enhanced_defaults() {
    // Default print settings
    add_option('el_default_paper_only', '0');
    add_option('el_signature_format', 'Client signature …………..……………………… Date ………… Page %d/%d');
    add_option('el_min_lines_per_page', '2');
    add_option('el_force_new_page_sections', '1');
    add_option('el_keep_signatures_together', '1');
}
register_activation_hook(__FILE__, 'el_set_enhanced_defaults');

/**
 * COMPATIBILITY LAYER
 * Ensures backward compatibility with existing code
 */

// Maintain compatibility with existing AJAX actions
if (!has_action('wp_ajax_el_load_print_editor')) {
    add_action('wp_ajax_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');
    add_action('wp_ajax_nopriv_el_load_print_editor', 'el_ajax_load_print_editor_enhanced');
}

if (!has_action('wp_ajax_el_save_edited_pdf')) {
    add_action('wp_ajax_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');
    add_action('wp_ajax_nopriv_el_save_edited_pdf', 'el_ajax_save_edited_pdf_enhanced');
}

// Maintain compatibility with existing JavaScript globals
add_action('wp_footer', function() {
    if (is_page_template('engagement-letter-wizard.php') || has_shortcode(get_post()->post_content, 'el_print_editor')) {
        ?>
        <script>
        // Maintain backward compatibility with existing code
        if (typeof el_ajax !== 'undefined' && typeof el_print_config !== 'undefined') {
            el_print_config.ajax_url = el_ajax.ajax_url;
            el_print_config.nonce = el_ajax.nonce;
        }
        </script>
        <?php
    }
});


/**
 * =================================================================
 * AJAX FORM SUBMISSION HANDLER FOR TAB 1
 * =================================================================
 */
add_action('wp_ajax_el_save_client_ajax', 'el_save_client_ajax');
add_action('wp_ajax_nopriv_el_save_client_ajax', 'el_save_client_ajax');


function el_save_client_ajax() {
    // Debug logging
    error_log('EL SAVE: Starting save process');
    error_log('EL SAVE: POST data: ' . print_r($_POST, true));
    
    // Single nonce verification - using el_ajax_nonce consistently
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'el_nonce')) {
        error_log('EL SAVE: Nonce verification FAILED');
        error_log('EL SAVE: Expected nonce action: el_nonce');
        error_log('EL SAVE: Received nonce: ' . ($_POST['nonce'] ?? 'none'));
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    error_log('EL SAVE: Nonce verification PASSED');
    
    // Get form data
    $form_data = $_POST['form_data'] ?? [];
    
    // Parse form data if it's serialized
    if (is_string($form_data)) {
        parse_str($form_data, $form_data);
    }
    
    // Extract all form values
    $first_name = sanitize_text_field($form_data['input_1_3'] ?? $form_data['input_1'] ?? '');
    $last_name = sanitize_text_field($form_data['input_1_6'] ?? '');
    $email = sanitize_email($form_data['input_2'] ?? '');
    $phone = sanitize_text_field($form_data['input_5'] ?? '');
    
    // Address fields (ID 6)
    $street_address = sanitize_text_field($form_data['input_6_1'] ?? '');
    $city = sanitize_text_field($form_data['input_6_3'] ?? '');
    $state = sanitize_text_field($form_data['input_6_4'] ?? '');
    $zip = sanitize_text_field($form_data['input_6_5'] ?? '');
    $country = sanitize_text_field($form_data['input_6_6'] ?? 'United States');
    
    // Combine name
    $client_name = trim($first_name . ' ' . $last_name);
    
    if (empty($client_name) || empty($email)) {
        error_log('EL SAVE: Missing required fields - Name: ' . $client_name . ', Email: ' . $email);
        wp_send_json_error(['message' => 'Name and email are required']);
        return;
    }
    
  // Start session if not already started
    if (!session_id()) {
        session_start();
    }
    
    // Save to session
    $_SESSION['el_client_name'] = $client_name;
    $_SESSION['el_client_email'] = $email;
    $_SESSION['el_form_data'] = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'street_address' => $street_address,
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'country' => $country,
        'full_name' => $client_name,
        'full_address' => trim($street_address . ', ' . $city . ', ' . $state . ' ' . $zip . ', ' . $country, ', ')
    ];
    
    // Check if client exists
    $existing_client_id = 0;
    $clients = get_posts([
        'post_type' => 'el_client',
        'meta_key' => '_el_client_email',
        'meta_value' => $email,
        'posts_per_page' => 1
    ]);
    
    if (!empty($clients)) {
        $existing_client_id = $clients[0]->ID;
        $_SESSION['el_current_client_id'] = $existing_client_id;
        
        // Update existing client
        wp_update_post([
            'ID' => $existing_client_id,
            'post_title' => $client_name
        ]);
    } else {
        // Create new client
        $client_id = wp_insert_post([
            'post_title' => $client_name,
            'post_type' => 'el_client',
            'post_status' => 'publish'
        ]);
        
        if (!is_wp_error($client_id)) {
            $_SESSION['el_current_client_id'] = $client_id;
            $existing_client_id = $client_id;
        }
    }
    
    // Update client meta
    if ($existing_client_id) {
        update_post_meta($existing_client_id, '_el_client_email', $email);
        update_post_meta($existing_client_id, '_el_client_phone', $phone);
        update_post_meta($existing_client_id, '_el_client_address', $_SESSION['el_form_data']['full_address']);
        update_post_meta($existing_client_id, '_el_form_data', $_SESSION['el_form_data']);
    }
    
    error_log('EL SAVE: Client saved successfully - ID: ' . $existing_client_id);
    
    // ============================================
    // CREATE OR UPDATE ENGAGEMENT LETTER POST
    // ============================================
    
    $engagement_letter_id = isset($_SESSION['el_engagement_letter_id']) ? intval($_SESSION['el_engagement_letter_id']) : 0;
    
    // Check if engagement letter exists and is valid
    if (!$engagement_letter_id || get_post_type($engagement_letter_id) !== 'engagement_letter') {
        
        // Generate unique reference
        $reference = 'EL-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);
        
        // Create new engagement letter post
        $engagement_letter_id = wp_insert_post([
            'post_type'   => 'engagement_letter',
            'post_title'  => 'Draft - ' . $client_name . ' - ' . date('Y-m-d H:i:s'),
            'post_status' => 'publish',
            'post_author' => get_current_user_id() ?: 1
        ]);
        
        if (!is_wp_error($engagement_letter_id)) {
            // Save initial meta data
            update_post_meta($engagement_letter_id, '_el_reference', $reference);
            update_post_meta($engagement_letter_id, 'el_reference', $reference);
            update_post_meta($engagement_letter_id, '_el_status', 'draft');
            update_post_meta($engagement_letter_id, 'el_status', 'draft');
            update_post_meta($engagement_letter_id, '_el_client_id', $existing_client_id);
            update_post_meta($engagement_letter_id, '_el_lawyer_id', get_current_user_id() ?: 1);
            update_post_meta($engagement_letter_id, '_el_form_data', $_SESSION['el_form_data']);
            update_post_meta($engagement_letter_id, '_el_current_tab', 1);
            update_post_meta($engagement_letter_id, '_el_last_active', current_time('mysql'));
            update_post_meta($engagement_letter_id, '_el_created_date', current_time('mysql'));
            
            // Save to session
            $_SESSION['el_engagement_letter_id'] = $engagement_letter_id;
            
            error_log('✅ EL SAVE: Created engagement letter post ID: ' . $engagement_letter_id);
        } else {
            error_log('❌ EL SAVE: Failed to create engagement letter: ' . $engagement_letter_id->get_error_message());
        }
    } else {
        // Update existing engagement letter
        update_post_meta($engagement_letter_id, '_el_form_data', $_SESSION['el_form_data']);
        update_post_meta($engagement_letter_id, '_el_client_id', $existing_client_id);
        update_post_meta($engagement_letter_id, '_el_current_tab', 1);
        update_post_meta($engagement_letter_id, '_el_last_active', current_time('mysql'));
        
        // Update post title with latest client name
        wp_update_post([
            'ID' => $engagement_letter_id,
            'post_title' => 'Draft - ' . $client_name . ' - ' . date('Y-m-d H:i:s')
        ]);
        
        error_log('✅ EL SAVE: Updated existing engagement letter ID: ' . $engagement_letter_id);
    }
    
    // ============================================
    // END ENGAGEMENT LETTER CREATION
    // ============================================
    
    wp_send_json_success([
        'message' => 'Client information saved successfully',
        'client_id' => $existing_client_id,
        'client_name' => $client_name,
        'engagement_letter_id' => $engagement_letter_id,
        'next_tab' => 2
    ]);
}


/**
 * Tab 5 - Export & Download with Paged.js
 * Alternative implementation using Paged.js for client-side pagination
 */

function el_tab5_pagedjs_php() {
    // Security check
    if (!current_user_can('manage_options')) {
        return '<p>You do not have permission to view this content.</p>';
    }
    
    // Get the saved PDF data from session
    if (!session_id()) {
        session_start();
    }
    
    $pdf_reference = isset($_SESSION['el_pdf_reference']) ? $_SESSION['el_pdf_reference'] : '';
    
    if (empty($pdf_reference)) {
        return '<p>No engagement letter found. Please complete the previous steps first.</p>';
    }
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $pdf_reference);
    
    if (!$pdf_data) {
        return '<p>Engagement letter data not found. Please regenerate the preview.</p>';
    }
    
    // Generate the HTML content
    $html_content = '';
    if (function_exists('el_render_engagement_letter_html')) {
        $html_content = el_render_engagement_letter_html($pdf_data);
    }
    
    ob_start();
    ?>
    
    <div class="el-tab-content el-tab5-content">
        <!-- Tab Header -->
        <div class="el-tab-header">
            <h2>Export & Download - Print Version</h2>
            <p>Review the paginated document with automatic page signatures</p>
        </div>
        
        <!-- Controls -->
        <div class="el-print-controls">
            <label class="el-paper-toggle">
                <input type="checkbox" id="paper-only-toggle" checked>
                <span>Paper-only mode (with page signatures)</span>
            </label>
            
            <div class="el-print-actions">
                <button type="button" id="el-download-pdf" class="button button-primary">
                    <span class="dashicons dashicons-download"></span> Download PDF
                </button>
                <button type="button" id="el-print-preview" class="button">
                    <span class="dashicons dashicons-printer"></span> Print Preview
                </button>
            </div>
        </div>
        
        <!-- Document Container for Paged.js -->
        <div id="paged-document" class="pagedjs-document">
            <?php echo $html_content; ?>
        </div>
    </div>
    
    <!-- Load Paged.js from CDN -->
    <script src="https://unpkg.com/pagedjs@0.4.3/dist/paged.min.js"></script>
    
    <style>
        /* Tab 5 specific styles */
        .el-tab5-content {
            padding: 20px;
            background: #f9f9f9;
        }
        
        .el-tab-header {
            margin-bottom: 30px;
        }
        
        .el-tab-header h2 {
            margin: 0 0 10px;
            color: #23282d;
        }
        
        .el-print-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .el-paper-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .el-print-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Paged.js container */
        .pagedjs_pages {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            padding: 20px;
            background: #e0e0e0;
            overflow-x: auto;
        }
        
        .pagedjs_page {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* Hide page signatures by default, show when paper-only is checked */
        .page-signature-line {
            display: none;
            position: absolute;
            bottom: 25mm;
            left: 25mm;
            right: 25mm;
            text-align: center;
            font-size: 10pt;
            color: #333;
            border-top: 1px solid #999;
            padding-top: 5mm;
        }
        
        body.paper-only .page-signature-line {
            display: block;
        }
        
        /* Paged Media styles */
        @page {
            size: A4;
            margin: 25mm;
            
            @bottom-center {
                content: none; /* We'll add signatures via JavaScript */
            }
        }
        
        /* Content styles from engagement letter */
        .engagement-letter {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        
        .engagement-letter h1,
        .engagement-letter h2,
        .engagement-letter h3 {
            font-weight: bold;
            margin-top: 1em;
            margin-bottom: 0.5em;
        }
        
        .engagement-letter h1 { font-size: 16pt; }
        .engagement-letter h2 { font-size: 14pt; }
        .engagement-letter h3 { font-size: 12pt; }
        
        /* Pagination rules */
        h1, h2, h3 {
            break-after: avoid;
        }
        
        p {
            orphans: 2;
            widows: 2;
        }
        
        table, figure {
            break-inside: avoid;
        }
        
        /* Signature blocks should stay together */
        .signature-block {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        let paged = null;
        
        // Paged.js class for handling page signatures
        class PageSignatures extends Paged.Handler {
            constructor(chunker, polisher, caller) {
                super(chunker, polisher, caller);
            }
            
            afterPageLayout(pageElement, page, breakToken) {
                // Only add signatures if paper-only mode is active
                if (!document.body.classList.contains('paper-only')) {
                    return;
                }
                
                // Don't add signature to the last page
                const totalPages = document.querySelectorAll('.pagedjs_page').length;
                if (page.number === totalPages) {
                    return;
                }
                
                // Create signature line
                const signature = document.createElement('div');
                signature.className = 'page-signature-line';
                signature.innerHTML = `Client signature …………..……………………… Date ………… Page ${page.number}/${totalPages}`;
                
                // Add to page
                const pageArea = pageElement.querySelector('.pagedjs_page_content');
                if (pageArea) {
                    pageArea.appendChild(signature);
                }
            }
        }
        
        // Register the handler
        Paged.registerHandlers(PageSignatures);
        
        // Initialize Paged.js
        function initializePaged() {
            // Add paper-only class if checkbox is checked
            if ($('#paper-only-toggle').is(':checked')) {
                $('body').addClass('paper-only');
            } else {
                $('body').removeClass('paper-only');
            }
            
            // Preview the document
            paged = new Paged.Previewer();
            paged.preview().then(() => {
                console.log('Paged.js rendering complete');
                updatePageCount();
            });
        }
        
        // Update page count after rendering
        function updatePageCount() {
            const totalPages = document.querySelectorAll('.pagedjs_page').length;
            console.log('Total pages:', totalPages);
            
            // Update all page signatures with correct total
            if ($('body').hasClass('paper-only')) {
                $('.page-signature-line').each(function(index) {
                    if (index < totalPages - 1) { // Not on last page
                        $(this).html(`Client signature …………..……………………… Date ………… Page ${index + 1}/${totalPages}`);
                    }
                });
            }
        }
        
        // Paper-only toggle handler
        $('#paper-only-toggle').on('change', function() {
            // Clear existing preview
            $('.pagedjs_pages').remove();
            
            // Re-render with new settings
            initializePaged();
        });
        
        // Download PDF handler
        $('#el-download-pdf').on('click', function() {
            window.print();
        });
        
        // Print preview handler
        $('#el-print-preview').on('click', function() {
            window.print();
        });
        
        // Initialize on load
        initializePaged();
    });
    </script>
    
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode('el_print_editor_pagedjs', 'el_tab5_pagedjs_php');
/**
 * =================================================================
 * PART 2: ENHANCED JAVASCRIPT FOR FORM HANDLING & NAVIGATION
 * =================================================================
 */


add_shortcode('el_template_selection', function() {
    ob_start();
    
    global $wpdb;
    
    // Get distinct practice areas from products in el-templates category
    $practice_areas = $wpdb->get_results("
        SELECT DISTINCT pm.meta_value as practice_area
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND t.slug = 'el-templates'
        AND pm.meta_key = 'practice_area'
        AND pm.meta_value != ''
        ORDER BY pm.meta_value ASC
    ");
    
    // Get all product tags used by products in el-templates category
    $tags = $wpdb->get_results("
        SELECT DISTINCT t.term_id, t.name, t.slug
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        INNER JOIN {$wpdb->term_relationships} tr2 ON p.ID = tr2.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t2 ON tt2.term_id = t2.term_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND t2.slug = 'el-templates'
        AND tt.taxonomy = 'product_tag'
        ORDER BY t.name ASC
    ");
    
    // Get all products in el-templates category with modified date
    $products = $wpdb->get_results("
        SELECT DISTINCT p.ID, p.post_title, p.post_modified, p.post_content
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND t.slug = 'el-templates'
        ORDER BY p.post_title ASC
    ");
    
    ?>
    <style>
    /* Full-width template layout */
    .el-templates-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .el-filter-section {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 40px;
        border: 1px solid #e1e4e8;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .el-filters-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }
    
    .el-filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .el-filter-label {
        display: block;
        color: #2c3e50;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }
    
    .el-filter-dropdown {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #cbd5e0;
        border-radius: 8px;
        background: #ffffff;
        color: #2c3e50;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .el-filter-dropdown:hover,
    .el-filter-dropdown:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        outline: none;
    }
    
    .el-filter-dropdown option {
        background: #ffffff;
        color: #2c3e50;
        padding: 12px;
    }
    
    /* Template items - full width cards */
    .el-templates-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    
    .el-template-item {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
       transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        display: block;
    }
    
    .el-template-item:hover {
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
        border-color: #4a90e2;
        transform: translateY(-4px);
    }
    
    .el-template-inner {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 32px;
        padding: 30px;
        align-items: center;
    }
    
    /* Left content area */
    .el-template-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .el-template-header {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .el-template-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #a9c4e3ff;
        color: #2e2e2eff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 6px;
        width: fit-content;
    }
    
    .el-template-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.3;
        margin: 0;
    }
    
    .el-service-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #e8edf2;
        color: #1a3649ff;
        font-size: 13px;
        font-weight: 600;
        border-radius: 6px;
        border: 1px solid #d1dce6;
    }
    
    .el-template-teaser {
        font-size: 18px;
        color: #444;
        line-height: 1.6;
        margin: 0;
        font-weight: 400;
    }
    
    /* Tags display */
    .el-template-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    
    .el-tag {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: #f0f4f8;
        color: #334155;
        font-size: 12px;
        font-weight: 500;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    
    /* Last updated date */
    .el-last-updated {
        font-size: 13px;
        color: #64748b;
        font-style: italic;
        margin-top: 8px;
    }
    
    /* Description toggle */
    .el-description-container {
        margin-top: 16px;
    }
    
    .el-description-toggle {
        background: none;
        border: none;
        color: #4a90e2;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        padding: 8px 0;
        text-decoration: underline;
        transition: all 0.2s ease;
    }
    
    .el-description-toggle:hover {
        color: #2563eb;
    }
    
    .el-full-description {
        display: none;
        margin-top: 12px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 3px solid #4a90e2;
        font-size: 15px;
        line-height: 1.6;
        color: #475569;
    }
    
    .el-full-description.show {
        display: block;
    }
    
    /* Pricing type display */
    .el-pricing-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #dcfce7;
        color: #166534;
        font-size: 13px;
        font-weight: 600;
        border-radius: 6px;
        border: 1px solid #bbf7d0;
        margin-top: 8px;
    }
    
    /* Fee structure info */
    .el-fee-details {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .el-fee-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .el-fee-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        font-weight: 600;
    }
    
    .el-fee-value {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }
    
    /* Right pricing area */
    .el-template-pricing {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 24px;
        min-width: 280px;
    }
    
    .el-price-card {
        background: linear-gradient(135deg, #d5e4f6ff 0%, #bad8f6ff 100%);
        padding: 16px 12px;
        text-align: center;
   
        width: 100%;
    }
    
    .el-price-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 700;
        margin-bottom: 12px;
    }
    
    .el-price-amount {
        font-size: 48px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .el-price-currency {
        font-size: 24px;
            margin-right: 4px;
    }
    
    .el-price-note {
        font-size: 13px;
        color: rgba(72, 72, 72, 0.8);
        font-weight: 500;
        font-style: italic;
    }
    
    /* Select button */
    .el-select-btn {
        width: 100%;
        padding: 18px 32px;
        background: #a8bcceff;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 3px 12px rgba(74, 144, 226, 0.25);
    }
    
    .el-select-btn:hover {
        background: #357abd;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(74, 144, 226, 0.35);
    }
    
    .el-select-btn:active {
        transform: translateY(0);
    }
    
    .el-select-btn.loading {
        opacity: 0.7;
        cursor: wait;
    }
    
    /* No results message */
    .el-no-results {
        text-align: center;
        padding: 60px 20px;
        background: #f8fafc;
        border-radius: 16px;
        border: 2px dashed #cbd5e1;
        display: none;
    }
    
    .el-no-results-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    
    .el-no-results-text {
        font-size: 18px;
        color: #64748b;
        font-weight: 500;
    }
    
    /* Responsive design */
    @media (max-width: 1024px) {
        .el-template-inner {
            grid-template-columns: 1fr;
            gap: 32px;
        }
        
        .el-template-pricing {
            align-items: stretch;
            width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .el-template-title {
            font-size: 24px;
        }
        
        .el-price-amount {
            font-size: 36px;
        }
        
        .el-template-inner {
            padding: 24px;
        }
    }
    
    /* Hide WooCommerce added to cart messages in template selection area */
    .el-templates-container .woocommerce-message,
    .el-templates-container .woocommerce-info,
    .el-templates-container .wc-forward {
        display: none !important;
    }
    </style>
    
    <div class="el-templates-container">
        <!-- Filters Section -->
        <div class="el-filter-section">
            <div class="el-filters-row">
                <div class="el-filter-group">
                    <label class="el-filter-label" for="practice-area-filter">
                        Filter by Practice Area
                    </label>
                    <select id="practice-area-filter" class="el-filter-dropdown">
                        <option value="">All Practice Areas</option>
                        <?php foreach ($practice_areas as $area): ?>
                            <option value="<?php echo esc_attr($area->practice_area); ?>">
                                <?php echo esc_html($area->practice_area); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="el-filter-group">
                    <label class="el-filter-label" for="tag-filter">
                        Filter by Tag
                    </label>
                    <select id="tag-filter" class="el-filter-dropdown">
                        <option value="">All Tags</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo esc_attr($tag->slug); ?>">
                                <?php echo esc_html($tag->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Templates List -->
        <div class="el-templates-list">
            <?php 
            foreach ($products as $product):
                $product_id = $product->ID;
                
                // Get ACF fields
                $practice_area = get_field('practice_area', $product_id);
                $el_title = get_field('el_title', $product_id);
                $el_teaser = get_field('el_teaser', $product_id);
                $service_type = get_field('service_type', $product_id);
                
                // Pricing fields
                $engagement_fee_due = get_field('engagement_fee_due_today', $product_id);
                $full_engagement_price = get_field('engagement_price', $product_id);
                $fee_structure = get_field('fee_structure', $product_id);
                $hourly_rate = get_field('hourly_rate', $product_id);
                $rate_cap = get_field('rate_cap', $product_id);
                
                // Get product tags
                $product_tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'all'));
                $tag_slugs = array();
                if (!is_wp_error($product_tags) && !empty($product_tags)) {
                    $tag_slugs = wp_list_pluck($product_tags, 'slug');
                }
                
                // Format last updated date
                $last_updated = get_the_modified_date('F j, Y', $product_id);
                
                // Get full description
                $full_description = $product->post_content;
                
                // Build pricing type display
                $pricing_type = '';
                
                if ($fee_structure) {
                    $fee_lower = strtolower($fee_structure);
                    
                    // Check for specific patterns
                    if (strpos($fee_lower, 'hour') !== false || strpos($fee_lower, 'hourly') !== false) {
                        if ($rate_cap) {
                            $pricing_type = 'Per Hour, Capped';
                        } else {
                            $pricing_type = 'Per Hour';
                        }
                    } elseif (strpos($fee_lower, 'flat') !== false) {
                        $pricing_type = 'Flat Fee';
                    } elseif (strpos($fee_lower, 'fixed') !== false) {
                        $pricing_type = 'Fixed Fee';
                    } elseif (strpos($fee_lower, 'retainer') !== false) {
                        $pricing_type = 'Retainer';
                    } elseif (strpos($fee_lower, 'contingency') !== false) {
                        $pricing_type = 'Contingency';
                    } else {
                        // If no pattern matches, use the fee_structure value as-is
                        $pricing_type = $fee_structure;
                    }
                } elseif ($hourly_rate && $rate_cap) {
                    // Fallback: determine from other fields when fee_structure is empty
                    $pricing_type = 'Per Hour, Capped';
                } elseif ($hourly_rate) {
                    $pricing_type = 'Per Hour';
                } elseif ($full_engagement_price) {
                    $pricing_type = 'Fixed Fee';
                }
            ?>
<div class="el-template-item" 
     data-practice-area="<?php echo esc_attr($practice_area); ?>" 
     data-product-id="<?php echo esc_attr($product_id); ?>"
     data-tags="<?php echo esc_attr(implode(',', $tag_slugs)); ?>">
    <div class="el-template-inner">
        <!-- Left: Content -->
        <div class="el-template-content">
            <div class="el-template-header">
                <h3 class="el-template-title">
                    <?php echo esc_html($el_title ?: $product->post_title); ?>
                </h3>
                
                <!-- Badges Row -->
                <div class="el-badges-row">
                    <?php if ($practice_area): ?>
                    <span class="el-template-badge">
                        📋 <?php echo esc_html($practice_area); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($service_type): ?>
                    <span class="el-service-type">
                        ⚖️ <?php echo esc_html($service_type); ?>
                    </span>
                    <?php endif; ?>
                </div>
                           
                <!-- DEBUG: pricing_type = "<?php echo esc_attr($pricing_type); ?>", fee_structure = "<?php echo esc_attr($fee_structure); ?>", hourly = "<?php echo esc_attr($hourly_rate); ?>", cap = "<?php echo esc_attr($rate_cap); ?>", full_price = "<?php echo esc_attr($full_engagement_price); ?>" -->
                
                <?php if ($pricing_type): ?>
                <span class="el-pricing-type">
                    💰 Pricing: <?php echo esc_html($pricing_type); ?>
                </span>
                <?php endif; ?>
                
                <?php if (!empty($product_tags)): ?>
                <div class="el-template-tags">
                    <?php foreach ($product_tags as $tag): ?>
                        <span class="el-tag">🏷️ <?php echo esc_html($tag->name); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                            
                            <?php if ($last_updated): ?>
                            <div class="el-last-updated">
                                Last updated: <?php echo esc_html($last_updated); ?>
                            </div>
                            <?php endif; ?>
                        
                            <?php if ($el_teaser): ?>
                            <p class="el-template-teaser">
                                <?php echo esc_html($el_teaser); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($full_description): ?>
                            <div class="el-description-container">
                                <button class="el-description-toggle" data-product-id="<?php echo esc_attr($product_id); ?>">
                                    + View Full Description
                                </button>
                                <div class="el-full-description" id="desc-<?php echo esc_attr($product_id); ?>">
                                    <?php echo wp_kses_post(wpautop($full_description)); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    
                        <!-- Fee structure details -->
                        <?php if ($fee_structure || $hourly_rate || $rate_cap || $full_engagement_price): ?>
                        <div class="el-fee-details">
                            <?php if ($fee_structure): ?>
                            <div class="el-fee-item">
                                <span class="el-fee-label">Fee Structure</span>
                                <span class="el-fee-value"><?php echo esc_html($fee_structure); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($hourly_rate): ?>
                            <div class="el-fee-item">
                                <span class="el-fee-label">Hourly Rate</span>
                                <span class="el-fee-value">€<?php echo number_format($hourly_rate, 2); ?>/hr</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($rate_cap): ?>
                            <div class="el-fee-item">
                                <span class="el-fee-label">Rate Cap</span>
                                <span class="el-fee-value">€<?php echo number_format($rate_cap, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($full_engagement_price && $full_engagement_price != $engagement_fee_due): ?>
                            <div class="el-fee-item">
                                <span class="el-fee-label">Total Engagement</span>
                                <span class="el-fee-value">€<?php echo number_format($full_engagement_price, 2); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Right: Pricing & Button -->
                    <div class="el-template-pricing">
                        <div class="el-price-card">
                            <div class="el-price-label">Engagement Fee Due Today</div>
                            <div class="el-price-amount">
                                <?php if ($engagement_fee_due): ?>
                                <span class="el-price-currency">€</span><?php echo number_format($engagement_fee_due, 0); ?>
                                <?php else: ?>
                                <span style="font-size: 24px;">Contact Us</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($engagement_fee_due): ?>
                            <div class="el-price-note">Due upon signing</div>
                            <?php endif; ?>
                        </div>
                        
                        <button class="el-select-btn" data-product-id="<?php echo esc_attr($product_id); ?>">
                            Select This Template
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- No results message -->
        <div class="el-no-results">
            <div class="el-no-results-icon">🔍</div>
            <div class="el-no-results-text">No templates found matching your filters</div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('🎯 EL Template Selection - Enhanced with Tag Filter & Description Toggle');
        
        // Combined filter function
        function filterTemplates() {
            var selectedArea = $('#practice-area-filter').val();
            var selectedTag = $('#tag-filter').val();
            var $items = $('.el-template-item');
            var $noResults = $('.el-no-results');
            var visibleCount = 0;
            
            console.log('🔍 Filtering by:', {
                practiceArea: selectedArea || 'All',
                tag: selectedTag || 'All'
            });
            
            $items.each(function() {
                var $item = $(this);
                var itemArea = $item.attr('data-practice-area');
                var itemTags = $item.attr('data-tags') || '';
                var itemTagArray = itemTags ? itemTags.split(',') : [];
                
                var areaMatch = !selectedArea || itemArea === selectedArea;
                var tagMatch = !selectedTag || itemTagArray.includes(selectedTag);
                
                if (areaMatch && tagMatch) {
                    $item.fadeIn(300);
                    visibleCount++;
                } else {
                    $item.fadeOut(300);
                }
            });
            
            if (visibleCount === 0) {
                $noResults.fadeIn(300);
            } else {
                $noResults.hide();
            }
        }
        
        // Practice area filter
        $('#practice-area-filter').on('change', filterTemplates);
        
        // Tag filter
        $('#tag-filter').on('change', filterTemplates);
        
        // Description toggle
        $('.el-description-toggle').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var productId = $btn.data('product-id');
            var $description = $('#desc-' + productId);
            
            if ($description.hasClass('show')) {
                $description.removeClass('show').slideUp(300);
                $btn.text('+ View Full Description');
            } else {
                $description.addClass('show').slideDown(300);
                $btn.text('- Hide Full Description');
            }
        });
        
        // Select template button - WITH CART REFRESH
        $('.el-select-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var productId = $btn.data('product-id');
            
            if ($btn.hasClass('loading')) {
                return;
            }
            
            console.log('🎯 Adding product to cart:', productId);
            $btn.addClass('loading').text('Adding...');
            
            $.ajax({
                url: (window.el_ajax && el_ajax.ajax_url) ? el_ajax.ajax_url : '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'el_add_template_to_cart',
                    product_id: productId,
                    nonce: '<?php echo wp_create_nonce("el_add_template"); ?>'
                },
 success: function(response) {
    console.log('✅ Cart response:', response);
    
    if (response.success) {
        $btn.removeClass('loading').text('Added!');
        
        // Trigger WooCommerce cart refresh
        $(document.body).trigger('wc_fragment_refresh');
        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
        
        console.log('🔄 Triggered WooCommerce cart refresh');
        
        // Refresh cart editor if exists
        if ($('#el-cart-editor').length) {
            console.log('🔄 Refreshing cart editor...');
            $.ajax({
                url: (window.el_ajax && el_ajax.ajax_url) ? el_ajax.ajax_url : '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'el_refresh_cart_editor',
                    nonce: '<?php echo wp_create_nonce("el_cart_refresh"); ?>'
                },
                success: function(resp) {
                    if (resp.success && resp.data.html) {
                        $('#el-cart-editor').html(resp.data.html);
                        console.log('✅ Cart editor refreshed');
                    }
                }
            });
        }
        
        // Switch to Tab 3 after 1 second
        setTimeout(function() {
            console.log('🎯 Switching to Tab 3...');
            
            var $tab3 = $('#brxe-mhedar');
            if ($tab3.length) {
                console.log('✅ Found Tab 3 (#brxe-mhedar), clicking...');
                $tab3.click();
            } else {
                console.warn('⚠️ Tab 3 not found');
            }
            
            setTimeout(function() {
                $btn.removeClass('loading').text('Select This Template');
            }, 500);
        }, 1000);
    } else {
        $btn.removeClass('loading').text('Error - Try Again');
        console.error('❌ Error:', response.data);
        setTimeout(function() {
            $btn.text('Select This Template');
        }, 3000);
    }
},
            });
        });
        
        console.log('🚀 EL Template Selection initialized');
    });
    </script>
    <?php
    return ob_get_clean();
});



// Auto-generate PDF preview
add_shortcode('el_pdf_preview_auto', 'el_render_auto_pdf_preview');

function el_render_auto_pdf_preview() {
    // Enqueue the script
    add_action('wp_footer', 'el_pdf_preview_script', 999);
    
    ob_start();
    ?>
    <div id="el-pdf-preview-container" style="min-height: 400px;">
        <div class="el-pdf-loading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px;">
            <div class="el-spinner" style="width: 60px; height: 60px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: el-spin 1s linear infinite;"></div>
            <p style="margin-top: 20px; color: #6b7280;">Generating your engagement letter preview...</p>
        </div>
    </div>
    
    <style>
    @keyframes el-spin {
        to { transform: rotate(360deg); }
    }
    </style>
    <?php
    return ob_get_clean();
}

// FIXED JavaScript that waits for Tab 4 to be visible
function el_pdf_preview_script() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var pdfGenerated = false;
        
        // Function to generate PDF
        function generatePDFPreview() {
            if (pdfGenerated) return;
            
            var $container = $('#el-pdf-preview-container');
            
            // Only proceed if container exists and is visible
            if (!$container.length || !$container.is(':visible')) {
                console.log('📄 PDF container not visible yet');
                return;
            }
            
            // Check if already has content (not loading state)
            if (!$container.find('.el-pdf-loading').length && $container.children().length > 0) {
                console.log('📄 PDF already generated');
                return;
            }
            
            pdfGenerated = true;
            console.log('📄 Generating PDF preview now...');
            
            // Ensure loading state is shown
            if (!$container.find('.el-pdf-loading').length) {
                $container.html(
                    '<div class="el-pdf-loading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px;">' +
                    '<div class="el-spinner" style="width: 60px; height: 60px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: el-spin 1s linear infinite;"></div>' +
                    '<p style="margin-top: 20px; color: #6b7280;">Generating your engagement letter preview...</p>' +
                    '</div>'
                );
            }
           // First ensure cart is refreshed - ASYNC VERSION
$.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    type: 'POST',
    data: {
        action: 'el_refresh_cart_session',
        nonce: '<?php echo wp_create_nonce('el_refresh'); ?>'
    }
}).done(function(response) {
    console.log('✅ Cart refreshed before PDF generation');
    
    // Now generate PDF after cart is refreshed
    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'el_generate_pdf_preview',
            nonce: '<?php echo wp_create_nonce('el_nonce'); ?>',
            source: 'auto_shortcode'
        },
        success: function(response) {
            console.log('✅ PDF preview response:', response);
            
            if (response.success && response.data && response.data.html) {
                $container.hide().html(response.data.html).fadeIn(500);
                window.elPdfReference = response.data.reference;
                window.currentPDFData = response.data; // For Tab 5
                console.log('✅ Preview loaded successfully');
            } else {
                var errorMsg = response.data && response.data.message ? response.data.message : 'Please ensure you have items in your cart';
                pdfGenerated = false; // Allow retry
                
                $container.html(
                    '<div style="padding: 40px; text-align: center; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; margin: 20px;">' +
                    '<p style="color: #dc2626; font-size: 18px; font-weight: 600; margin-bottom: 10px;">⚠️ Could not generate preview</p>' +
                    '<p style="color: #6b7280; margin-bottom: 20px;">' + errorMsg + '</p>' +
                    '<button onclick="retryPDFGeneration()" style="padding: 10px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">🔄 Try Again</button>' +
                    '</div>'
                );
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            pdfGenerated = false; // Allow retry
            
            $container.html(
                '<div style="padding: 40px; text-align: center; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; margin: 20px;">' +
                '<p style="color: #dc2626; font-size: 18px; font-weight: 600; margin-bottom: 10px;">⚠️ Connection error</p>' +
                '<p style="color: #6b7280; margin-bottom: 20px;">Please check your connection and try again</p>' +
                '<button onclick="location.reload()" style="padding: 10px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">🔄 Reload Page</button>' +
                '</div>'
            );
        }
    });
    
}).fail(function() {
    console.error('❌ Cart refresh failed');
    pdfGenerated = false;
    
    $container.html(
        '<div style="padding: 40px; text-align: center; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; margin: 20px;">' +
        '<p style="color: #dc2626; font-size: 18px; font-weight: 600; margin-bottom: 10px;">⚠️ Could not refresh cart</p>' +
        '<p style="color: #6b7280; margin-bottom: 20px;">Please try again</p>' +
        '<button onclick="location.reload()" style="padding: 10px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">🔄 Reload Page</button>' +
        '</div>'
    );
});
}
        // Make retry function globally available
        window.retryPDFGeneration = function() {
            pdfGenerated = false;
            generatePDFPreview();
        };
        
        // Method 1: Check if container is already visible (user might already be on Tab 4)
        setTimeout(function() {
            generatePDFPreview();
        }, 500);
        
        // Method 2: Listen for Tab 4 clicks
        $(document).on('click', '#brxe-ihqhkg, .el-tab-4, [aria-controls*="el-pdf"]', function() {
            console.log('📍 Tab 4 clicked - will generate PDF');
            setTimeout(function() {
                pdfGenerated = false; // Reset flag to allow regeneration
                generatePDFPreview();
            }, 500);
        });
        
        // Method 3: Listen for Preview button from Tab 3
        $(document).on('click', '#el-preview-pdf-btn', function(e) {
            e.preventDefault();
            console.log('🎯 Preview button clicked');
            $('#brxe-ihqhkg').trigger('click');
            setTimeout(function() {
                pdfGenerated = false;
                generatePDFPreview();
            }, 800);
        });
        
        // Method 4: Monitor visibility changes
        var checkInterval = setInterval(function() {
            if ($('#el-pdf-preview-container').is(':visible') && !pdfGenerated) {
                clearInterval(checkInterval);
                generatePDFPreview();
            }
        }, 1000);
        
        // Stop checking after 30 seconds
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 30000);
    });
    </script>
    <?php
}



// Ensure cart loads for PDF generation
add_action('wp_ajax_el_generate_pdf_preview', function() {
    // Log source for debugging
    error_log('PDF generation requested from: ' . ($_POST['source'] ?? 'unknown'));
    
    if (function_exists('WC')) {
        if (is_null(WC()->cart)) {
            wc_load_cart();
        }
        WC()->cart->get_cart_from_session();
        error_log('Cart items available: ' . count(WC()->cart->get_cart()));
    }
}, 5);


  /**
 * =================================================================
 * AJAX Handler: Add Template to Cart
 * =================================================================
 */
add_action('wp_ajax_el_add_template_to_cart', 'el_add_template_to_cart');
add_action('wp_ajax_nopriv_el_add_template_to_cart', 'el_add_template_to_cart');

function el_add_template_to_cart() {
    // Check if WooCommerce is available
    if (!function_exists('WC') || !class_exists('WooCommerce')) {
        wp_send_json_error(['message' => 'WooCommerce not loaded']);
        return;
    }
    
    // Verify nonce
    if (!check_ajax_referer('el_add_template', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'No product ID provided']);
        return;
    }
    
    // Verify product exists
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Product not found']);
        return;
    }
    
    if ($product->get_status() !== 'publish') {
        wp_send_json_error(['message' => 'Product is not available']);
        return;
    }
    
    // Initialize WooCommerce cart
    if (is_null(WC()->cart)) {
        wc_load_cart();
    }
    
    // Clear existing cart
    WC()->cart->empty_cart();
    
    // Add product to cart
    try {
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
        
        if (!$cart_item_key) {
            $notices = wc_get_notices('error');
            $error_msg = 'Could not add to cart';
            if (!empty($notices)) {
                $error_msg .= ': ' . wp_strip_all_tags(implode(', ', array_column($notices, 'notice')));
            }
            wc_clear_notices();
            wp_send_json_error(['message' => $error_msg]);
            return;
        }
        
        // Check if this product is from el-templates category
        $is_template = has_term('el-templates', 'product_cat', $product_id);
        
        if ($is_template) {
            // Get upsells and cross-sells
            $upsell_ids = $product->get_upsell_ids();
            $crosssell_ids = $product->get_cross_sell_ids();
            
            // Combine both arrays and remove duplicates
            $additional_products = array_unique(array_merge($upsell_ids, $crosssell_ids));
            
            // Add each upsell/cross-sell
            if (!empty($additional_products)) {
                foreach ($additional_products as $additional_product_id) {
                    $additional_product = wc_get_product($additional_product_id);
                    
                    if ($additional_product && $additional_product->get_status() === 'publish') {
                        WC()->cart->add_to_cart($additional_product_id, 1);
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Error: ' . $e->getMessage()]);
        return;
    }
    
    // Store in session
    if (!session_id()) {
        // Session already started via init hook
    }
    $_SESSION['el_selected_template'] = $product_id;
    
    // Update engagement letter with template selection
    if (isset($_SESSION['el_engagement_letter_id'])) {
        $engagement_letter_id = intval($_SESSION['el_engagement_letter_id']);
        
        // Get practice area from product
        $practice_area = get_field('practice_area', $product_id);
        
        // Get cart contents for storage
        $cart_contents = array();
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_contents[] = array(
                'product_id' => $cart_item['product_id'],
                'quantity' => $cart_item['quantity'],
                'product_name' => $cart_item['data']->get_name(),
            );
        }
        
        el_update_engagement_letter($engagement_letter_id, array(
            'template_id' => $product_id,
            'practice_area' => $practice_area,
            'cart_contents' => $cart_contents,
        ));
    }
    
    // Calculate totals and get cart fragments
    WC()->cart->calculate_totals();
    
   WC()->cart->calculate_totals();
$cart_fragments = apply_filters('woocommerce_add_to_cart_fragments', [
    'div.widget_shopping_cart_content' => wc_get_template_html('cart/mini-cart.php')
]);

    wp_send_json_success([
        'message' => 'Template added successfully',
        'product_id' => $product_id,
        'product_name' => $product->get_name(),
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'fragments' => $cart_fragments['fragments'] ?? [],
        'cart_hash' => WC()->cart->get_cart_hash()
    ]);
} 

/**
 * Handle "No Client" workflow - Clear session and proceed to template selection
 */
add_action('wp_ajax_el_no_client_start', 'el_handle_no_client_start');
function el_handle_no_client_start() {
    check_ajax_referer('el_wizard_nonce', 'nonce');
    

    
    // Clear any client session data
    if (isset($_SESSION['el_client_data'])) {
        unset($_SESSION['el_client_data']);
    }
    
    // Set flag that we're in no-client mode
    $_SESSION['el_no_client_mode'] = true;
    
    error_log('EL: No-client mode activated');
    
    wp_send_json_success([
        'message' => 'Proceeding without client details',
        'next_tab' => 2
    ]);
}

/**
 * Shortcode: No Client Button
 */
function el_no_client_button_shortcode() {
    ob_start();
    ?>
    <div class="el-no-client-wrapper">
        <button id="el-no-client-btn" class="el-no-client-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            <span>Create Engagement Letter Without Client Details</span>
        </button>
        <p class="el-no-client-note">Skip client details and proceed directly to template selection</p>
    </div>
    
<script>
jQuery(document).ready(function($) {
    console.log('🎯 No Client button initialized');
    
    $('#el-no-client-btn').on('click', function(e) {
        e.preventDefault();
        console.log('🔵 Button clicked');
        
        var $btn = $(this);
        
        if ($btn.hasClass('loading')) {
            return;
        }
        
        $btn.addClass('loading').prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'el_no_client_start',
                nonce: '<?php echo wp_create_nonce('el_wizard_nonce'); ?>'
            },
            success: function(response) {
                console.log('✅ Success:', response);
                
                if (response.success) {
                    $btn.removeClass('loading').text('✓ Success!').css('background', '#10b981');
                    
                    setTimeout(function() {
                        console.log('🎯 Attempting to switch to Tab 2...');
                        
                        // Try multiple selectors
                        var $tab2 = $('#brxe-fttuln');
                        console.log('Tab 2 element found:', $tab2.length);
                        
                        if ($tab2.length) {
                            console.log('✅ Clicking Tab 2:', $tab2[0]);
                            $tab2.click();
                            $tab2.trigger('click');
                            
                            // Also try clicking parent
                            $tab2.parent().click();
                        } else {
                            console.error('❌ Tab 2 element #brxe-fttuln not found!');
                            
                            // Try alternate selector
                            var $altTab = $('.el-tab-2, [data-tab="2"]').first();
                            if ($altTab.length) {
                                console.log('✅ Found alternate Tab 2, clicking...');
                                $altTab.click();
                            }
                        }
                    }, 500);
                } else {
                    $btn.removeClass('loading').text('Error - Try Again').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error:', error);
                $btn.removeClass('loading').text('Connection Error').prop('disabled', false);
            }
        });
    });
});
</script>
    
    <style>
    .el-no-client-wrapper {
        margin: 30px 0;
        text-align: center;
    }
    .el-no-client-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #000;
        border: 2px solid #f59e0b;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .el-no-client-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
    }
    .el-no-client-btn.loading {
        opacity: 0.6;
        cursor: wait;
    }
    .el-no-client-note {
        margin-top: 12px;
        font-size: 14px;
        color: #6b7280;
        font-style: italic;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('el_no_client_button', 'el_no_client_button_shortcode');

/**
 * =================================================================
 * SHORTCODE: No Client Button
 * Usage: [el_no_client_button]
 * =================================================================
 */
add_shortcode('el_no_client_button', 'el_render_no_client_button');


function el_render_no_client_button() {
    ob_start();
    ?>
    <div class="el-no-client-wrapper">
        <button id="el-no-client-btn" class="el-no-client-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            <span>Create Engagement Letter Without Client Details</span>
        </button>
        <p class="el-no-client-note">Skip client details and proceed directly to template selection</p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('🎯 No Client button initialized');
        
        $('#el-no-client-btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔵 Button clicked');
            
            var $btn = $(this);
            
            if ($btn.hasClass('loading')) {
                return;
            }
            
            $btn.addClass('loading').prop('disabled', true).text('Processing...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_no_client_start',
                    nonce: '<?php echo wp_create_nonce('el_wizard_nonce'); ?>'
                },
                success: function(response) {
                    console.log('✅ Success:', response);
                    
                    if (response.success) {
                        $btn.removeClass('loading').text('✓ Success!').css('background', '#10b981');
                        
                        setTimeout(function() {
                            console.log('🎯 Switching to Tab 2');
                            $('#brxe-fttuln').click();
                        }, 500);
                    } else {
                        $btn.removeClass('loading').text('Error - Try Again').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error:', error);
                    $btn.removeClass('loading').text('Connection Error').prop('disabled', false);
                }
            });
        });
    });
    </script>
    
    <style>
    .el-no-client-wrapper {
        margin: 30px 0;
        text-align: center;
    }
    .el-no-client-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #000;
        border: 2px solid #f59e0b;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .el-no-client-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
    }
    .el-no-client-btn.loading {
        opacity: 0.6;
        cursor: wait;
    }
    .el-no-client-note {
        margin-top: 12px;
        font-size: 14px;
        color: #6b7280;
        font-style: italic;
    }
    </style>
    <?php
    return ob_get_clean();
}
/**
 * =================================================================
 * AJAX HANDLER: Start Engagement Without Client
 * =================================================================
 */
add_action('wp_ajax_el_start_no_client', 'el_handle_start_no_client');
add_action('wp_ajax_nopriv_el_start_no_client', 'el_handle_start_no_client');

function el_handle_start_no_client() {
    // Verify nonce
    if (!check_ajax_referer('el_wizard_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Clear any existing cart
    if (class_exists('WooCommerce') && WC()->cart) {
        WC()->cart->empty_cart();
    }
    
    // Start session if not started
    if (!session_id()) {
        // Session already started via init hook
    }
    
    // Set flag for "no client" mode
    $_SESSION['el_no_client_mode'] = true;
    $_SESSION['el_client_name'] = 'No Client';
    $_SESSION['el_client_email'] = '';
    $_SESSION['el_current_client_id'] = 0;
    
    // Create a minimal engagement letter record
    $engagement_id = el_create_engagement_letter([
        'title' => 'Engagement Letter (No Client) - ' . date('Y-m-d H:i'),
        'client_id' => 0,
        'lawyer_id' => get_current_user_id(),
        'status' => 'draft',
        'form_data' => ['no_client_mode' => true]
    ]);
    
    $_SESSION['el_engagement_letter_id'] = $engagement_id;
    
    wp_send_json_success([
        'message' => 'No-client mode activated',
        'engagement_id' => $engagement_id
    ]);
}
/**
 * AJAX: Search existing clients with complete billing details
 */
add_action('wp_ajax_search_existing_client', 'el_search_existing_client');
add_action('wp_ajax_nopriv_search_existing_client', 'el_search_existing_client');

function el_search_existing_client() {
    check_ajax_referer('el_client_search_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search_term'] ?? '');
    $search_type = sanitize_text_field($_POST['search_type'] ?? 'email');
    
    if (strlen($search_term) < 2) {
        wp_send_json_success([]);
    }
    
    global $wpdb;
    $clients = [];
    
    if ($search_type === 'email') {
        $search_pattern = '%' . $wpdb->esc_like($search_term) . '%';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_email, display_name 
            FROM {$wpdb->users} 
            WHERE user_email LIKE %s 
            ORDER BY display_name ASC 
            LIMIT 10",
            $search_pattern
        ));
        
        foreach ($users as $user_data) {
            $user = get_user_by('ID', $user_data->ID);
            
            $clients[] = [
                'id' => $user->ID,
                'first_name' => get_user_meta($user->ID, 'billing_first_name', true) ?: $user->first_name,
                'last_name' => get_user_meta($user->ID, 'billing_last_name', true) ?: $user->last_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_phone', true),
                'street_address' => get_user_meta($user->ID, 'billing_address_1', true),
                'address_2' => get_user_meta($user->ID, 'billing_address_2', true),
                'city' => get_user_meta($user->ID, 'billing_city', true),
                'state' => get_user_meta($user->ID, 'billing_state', true),
                'zip' => get_user_meta($user->ID, 'billing_postcode', true),
                'country' => get_user_meta($user->ID, 'billing_country', true),
                'display' => $user->display_name
            ];
        }
        
    } else {
        // Name search - search all users
        $search_pattern = '%' . $wpdb->esc_like($search_term) . '%';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_email, display_name 
            FROM {$wpdb->users} 
            WHERE display_name LIKE %s OR user_email LIKE %s
            ORDER BY display_name ASC 
            LIMIT 10",
            $search_pattern,
            $search_pattern
        ));
        
        foreach ($users as $user_data) {
            $user = get_user_by('ID', $user_data->ID);
            
            $clients[] = [
                'id' => $user->ID,
                'first_name' => get_user_meta($user->ID, 'billing_first_name', true) ?: $user->first_name,
                'last_name' => get_user_meta($user->ID, 'billing_last_name', true) ?: $user->last_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_phone', true),
                'street_address' => get_user_meta($user->ID, 'billing_address_1', true),
                'address_2' => get_user_meta($user->ID, 'billing_address_2', true),
                'city' => get_user_meta($user->ID, 'billing_city', true),
                'state' => get_user_meta($user->ID, 'billing_state', true),
                'zip' => get_user_meta($user->ID, 'billing_postcode', true),
                'country' => get_user_meta($user->ID, 'billing_country', true),
                'display' => $user->display_name
            ];
        }
    }
    
    wp_send_json_success($clients);
}
add_action('wp_footer', 'el_enhanced_wizard_scripts', 999);

function el_enhanced_wizard_scripts() {
    if (!is_page()) return;
    ?>
    <!-- Enhanced CSS for Tab 2 Template Selection -->
    <style>
        /* Global Reset Button */
        .el-start-again-btn {
            position: fixed;
            top: 100px;
            right: 20px;
            background: #dc2626;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.3);
            transition: all 0.3s ease;
        }
        
        .el-start-again-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }
        
        .el-start-again-btn svg {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
        
        /* Enhanced Tab 2 Template Grid */
        #el-template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 14px;
            padding: 10px 0;
        }
        
        .el-template-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .el-template-item:hover {
            border-color: #3b82f6;
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .el-template-header {
           
            color: white;
            
            position: relative;
        }
        
        .el-template-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }
        
        .el-practice-area {
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .el-template-body {
            padding: 20px;
        }
        
        .el-template-description {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            min-height: 60px;
        }
        
        .el-template-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .el-template-price {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .el-price-label {
            font-size: 11px;
            color: #151921ff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .el-price-amount {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        
        .el-select-template-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .el-select-template-btn:hover {
            background: #031957ff;
            transform: scale(1.05);
        }
        
        .el-select-template-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Practice Area Filter Dropdown */
        #practice-area-filter {
            width: 100%;
            max-width: 320px;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            background: white;
            transition: border-color 0.3s ease;
            margin-bottom: 20px;
        }
        
        #practice-area-filter:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Tab 3 Enhanced Cart Display */
        .el-cart-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        
        .el-cart-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .el-cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }
        
        .el-cart-item-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }
        
        .el-cart-item-prices {
            text-align: right;
        }
        .el-badges-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}
        .el-engagement-fee {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .el-engagement-amount {
            font-size: 20px;
            font-weight: 700;
            color: #031957ff;
        }
        
        .el-expected-total {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .el-expected-amount {
            font-weight: 600;
            color: #dc2626;
        }
        
        /* PDF Preview Container */
        #el-pdf-preview-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 20px;
            min-height: 600px;
            position: relative;
        }
        
        .el-pdf-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }
        
        .el-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .el-pdf-content {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            color: #111827;
        }
    </style>
    
    <script type="text/javascript">
    jQuery(function($) {
        console.log('🚀 Enhanced EL Wizard Initializing...');
        
        // Add "Start Again" button to the page
        if ($('.el-wizard-container, .brxe-tabs').length) {
            $('body').append(
                '<button class="el-start-again-btn" onclick="elStartAgain()">' +
                '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />' +
                '</svg>Start New Engagement Letter</button>'
            );
        }
        
        // Global function to start again
        window.elStartAgain = function() {
            if (confirm('Are you sure you want to start a new engagement letter? This will clear all current data.')) {
                // Clear session data via AJAX
                $.post(el_ajax.ajax_url, {
                    action: 'el_clear_session',
                    nonce: el_ajax.nonce
                }, function() {
                    // Clear WooCommerce cart
                    $.post(el_ajax.ajax_url, {
                        action: 'el_clear_cart',
                        nonce: el_ajax.nonce
                    }, function() {
                        // Clear form fields
                        $('#gform_1')[0].reset();
                        
                        // Reset to Tab 1
                        if (typeof switchToTab === 'function') {
                            switchToTab(1);
                        } else {
                            $('.el-tab-1').click();
                        }
                        
                        // Show notification
                        showNotification('Ready to start a new engagement letter', 'info');
                    });
                });
            }
        };
        
jQuery(document).ready(function($) {
    // AJAX form submission for Tab 1
    $(document).on('submit', '#gform_1', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('.gform_button');
        
        // Show loading state
        $submitBtn.text('Saving...').prop('disabled', true);
        
        // Gather form data
        var formData = $form.serialize();
        
        // Submit via AJAX with correct nonce
        $.ajax({
            url: el_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'el_save_client_ajax',
                form_data: formData,
                nonce: el_ajax.nonce  // Use the el_ajax.nonce that's already localized
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    console.log('Client saved successfully');
                    
                    // Store client ID if needed
                    if (response.data.client_id) {
                        window.el_current_client_id = response.data.client_id;
                    }
                    
                    // Move to next tab
                    if (response.data.next_tab) {
                        // Switch to Tab 2 (adjust selector as needed)
                        $('.el-tab-2').click();
                        // Or use your specific tab switching method
                    }
                    
                    // Update UI
                    $submitBtn.text('Saved!').addClass('success');
                    setTimeout(function() {
                        $submitBtn.text('Save & Continue').removeClass('success').prop('disabled', false);
                    }, 2000);
                } else {
                    // Show error message
                    alert('Error: ' + (response.data.message || 'Could not save client information'));
                    $submitBtn.text('Save & Continue').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Connection error. Please try again.');
                $submitBtn.text('Save & Continue').prop('disabled', false);
            }
        });
    });
});
        
        // Enhanced PDF Preview Generation (No iframe)
        window.elGeneratePDFPreview = function() {
            console.log('📄 Generating PDF Preview...');
            
            var $container = $('#el-pdf-preview-container');
            
            if (!$container.length) {
                console.error('PDF preview container not found');
                return;
            }
            
            // Show loading spinner
            $container.html(
                '<div class="el-pdf-loading">' +
                '<div class="el-spinner"></div>' +
                '<p style="margin-top: 20px; color: #6b7280;">Generating your engagement letter...</p>' +
                '</div>'
            );
            
            // Make AJAX call to generate preview
            $.ajax({
                url: el_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'el_generate_pdf_preview',
                    nonce: el_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        // Insert HTML directly into container
                        $container.html(response.data.html);
                        
                        // Store PDF data globally if needed
                        window.currentPDFData = response.data;
                        
                        // Enable action buttons
                        $('#el-download-pdf, #el-send-for-signature').prop('disabled', false);
                        
                        showNotification('✓ Preview generated successfully', 'success');
                    } else {
                        $container.html(
                            '<div class="el-pdf-error" style="padding: 40px; text-align: center;">' +
                            '<p style="color: #dc2626; font-size: 18px;">⚠️ Could not generate preview</p>' +
                            '<p style="color: #6b7280; margin-top: 10px;">' + 
                            (response.data.message || 'Please try again or contact support') + '</p>' +
                            '</div>'
                        );
                    }
                },
                error: function() {
                    $container.html(
                        '<div class="el-pdf-error" style="padding: 40px; text-align: center;">' +
                        '<p style="color: #dc2626; font-size: 18px;">⚠️ Connection error</p>' +
                        '<p style="color: #6b7280; margin-top: 10px;">Please check your connection and try again</p>' +
                        '</div>'
                    );
                }
            });
        };
        
        // Notification function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            $('.el-notification').remove();
            
            var typeClasses = {
                'success': 'background: #10b981; color: white;',
                'error': 'background: #dc2626; color: white;',
                'warning': 'background: #f59e0b; color: white;',
                'info': 'background: #3b82f6; color: white;'
            };
            
            var $notification = $(
                '<div class="el-notification" style="position: fixed; top: 20px; right: 20px; padding: 16px 24px; ' +
                'border-radius: 8px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15); ' +
                'font-size: 15px; font-weight: 500; display: none; ' + typeClasses[type] + '">' +
                message +
                '<span style="margin-left: 20px; cursor: pointer; font-size: 20px;" onclick="jQuery(this).parent().fadeOut()">×</span>' +
                '</div>'
            );
            
            $('body').append($notification);
            $notification.fadeIn(300);
            
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Make notification function globally available
        window.showNotification = showNotification;
        
        console.log('✅ Enhanced EL Wizard Ready');
    });
    </script>
    <?php
}
add_action('wp_ajax_el_setup_engagement_cart', 'el_setup_engagement_cart');
add_action('wp_ajax_nopriv_el_setup_engagement_cart', 'el_setup_engagement_cart');

function el_setup_engagement_cart() {
    // Verify nonce
    if (!check_ajax_referer('el_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'No product ID provided']);
    }
    
    // Verify product exists
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Product not found (ID: ' . $product_id . ')']);
    }
    
    if ($product->get_status() !== 'publish') {
        wp_send_json_error(['message' => 'Product is not published (Status: ' . $product->get_status() . ')']);
    }
    
    // Check if product is purchasable
    if (!$product->is_purchasable()) {
        wp_send_json_error(['message' => 'Product is not purchasable (Type: ' . $product->get_type() . ')']);
    }
    
    // Initialize cart if needed
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    // Clear existing cart (one template per engagement letter)
    WC()->cart->empty_cart();
    
    // Add product to cart with error handling
    try {
        // Set a default price if none exists (for virtual/service products)
        $price = $product->get_price();
        if (empty($price) || $price == 0) {
            // Try to get engagement_fee from ACF
            $engagement_fee = get_field('engagement_fee_due_today', $product_id);
            if (!$engagement_fee) {
                $engagement_fee = get_field('engagement_fee', $product_id);
            }
            if ($engagement_fee && $engagement_fee > 0) {
                $product->set_price($engagement_fee);
            }
        }
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
        
        if (!$cart_item_key) {
            // Get WooCommerce notices to see what went wrong
            $notices = wc_get_notices('error');
            $error_msg = 'Could not add product to cart';
            if (!empty($notices)) {
                $error_msg .= ': ' . wp_strip_all_tags(implode(', ', array_column($notices, 'notice')));
            }
            $error_msg .= ' (Type: ' . $product->get_type() . ', Price: ' . $product->get_price() . ')';
            wc_clear_notices();
            wp_send_json_error([
                'message' => $error_msg, 
                'debug' => [
                    'product_type' => $product->get_type(),
                    'is_purchasable' => $product->is_purchasable(),
                    'is_in_stock' => $product->is_in_stock(),
                    'price' => $product->get_price(),
                    'product_name' => $product->get_name()
                ]
            ]);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Exception: ' . $e->getMessage()]);
    }
    
    // Store in session
    if (!session_id()) {
        // Session already started via init hook
    }
    $_SESSION['el_selected_product'] = $product_id;
    
    wp_send_json_success([
        'message' => 'Template added successfully',
        'product_id' => $product_id,
        'product_name' => $product->get_name(),
        'product_type' => $product->get_type(),
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_contents' => array_keys(WC()->cart->get_cart())
    ]);
}
add_action('wp_ajax_el_refresh_cart_editor', 'el_ajax_refresh_cart_editor');
add_action('wp_ajax_nopriv_el_refresh_cart_editor', 'el_ajax_refresh_cart_editor');

function el_ajax_refresh_cart_editor() {
    // Verify nonce
    if (!check_ajax_referer('el_cart_refresh', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Initialize cart if needed
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    // Calculate totals to ensure everything is up to date
    WC()->cart->calculate_totals();
    
    // Get the cart editor HTML
    $cart_html = el_get_cart_editor_content();
    
    wp_send_json_success([
        'html' => $cart_html,
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_total()
    ]);
}

/**
 * Helper function: Get cart editor content
 * This is the inner content of the cart editor, separated for reusability
 */



function el_get_cart_editor_content() {
    ob_start();
    
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $cart = WC()->cart->get_cart();
    
    ?>
    <style>
    /* Tab 3 Styles - Matching Tab 4 Design */
    .el-cart-items {
        margin: 20px 0;
    }
    
   .el-cart-item {
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}
    
.el-cart-item:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border-color: #3b82f6;
    transform: translateY(-2px);
}
    
.el-cart-item-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    padding: 20px;
    align-items: center;
}
    
    .el-cart-item-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0 0 12px 0;
    }
    
    .el-cart-item-prices {
        text-align: right;
        min-width: 200px;
    }
    
    .el-price-badge {
        background: linear-gradient(135deg, #d5e4f6ff 0%, #bad8f6ff 100%);
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(74, 144, 226, 0.25);
        margin-bottom: 12px;
    }
    
    .el-engagement-fee {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #1e40af;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .el-engagement-amount {
        font-size: 36px;
        font-weight: 800;
        color: #1a1a2e;
        display: block;
    }
    
    .el-expected-total {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-top: 12px;
        font-weight: 600;
    }
    
    .el-expected-amount {
        font-size: 18px;
        font-weight: 700;
        color: #dc2626;
    }
    
.el-cart-item-actions {
    padding: 0 20px 20px 20px;
    display: flex;
    gap: 15px;
    align-items: center;
    border-top: 1px solid #f3f4f6;
    padding-top: 16px;
    margin: 0 20px 16px 20px;
}
    
    .el-qty-update {
        width: 80px;
        padding: 10px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 16px;
        text-align: center;
        transition: border-color 0.3s ease;
    }
    
    .el-qty-update:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .el-remove-item {
        background: #dc2626;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .el-remove-item:hover {
        background: #b91c1c;
        transform: translateY(-2px);
    }
    
    .el-remove-item:disabled,
    .el-qty-update:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
.el-bundle-components {
    margin: 0 20px 16px 20px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
    
    .el-bundle-components h4 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #1e293b;
        font-weight: 600;
    }
    
    .el-bundle-component {
        display: block;
        margin-bottom: 12px;
        padding: 10px;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .el-bundle-component:hover {
        background: #f0f9ff;
    }
    
    .el-bundle-component input[type="checkbox"] {
        margin-right: 10px;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .el-cart-totals {
        margin-top: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 3px solid #3b82f6;
        border-radius: 16px;
    }
    
    .el-cart-totals h3 {
        font-size: 26px;
        font-weight: 700;
        color: #1e40af;
        margin: 0 0 20px 0;
    }
    
    .el-total-row {
        display: flex;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #bfdbfe;
        font-size: 18px;
    }
    
    .el-total-row:last-child {
        border-bottom: none;
        padding-top: 20px;
        margin-top: 10px;
        border-top: 2px solid #3b82f6;
    }
    
    .el-total-label {
        color: #1e40af;
        font-weight: 600;
    }
    
    .el-total-value {
        font-size: 30px;
        font-weight: 800;
        color: #031957ff;
    }
    
    .el-cart-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }
    
    .el-cart-actions button {
        padding: 16px 32px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    #el-preview-pdf-btn {
        background: #3b82f6;
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    #el-preview-pdf-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    
    .el-empty-cart {
        text-align: center;
        padding: 60px 40px;
        background: #f8fafc;
        border-radius: 16px;
        border: 2px dashed #cbd5e1;
    }
    
    .el-empty-cart p {
        font-size: 18px;
        color: #64748b;
        margin: 10px 0;
    }
    
    .el-template-notice {
        margin: 0 40px 20px 40px;
        padding: 15px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 4px;
    }
    
    .el-template-notice p {
        margin: 0;
        font-size: 15px;
        color: #1e40af;
        line-height: 1.6;
    }
    
    .el-updating {
        opacity: 0.6;
        pointer-events: none;
    }
    </style>
    <?php
    
    if (empty($cart)): ?>
        <div class="el-empty-cart">
            <p style="font-size: 24px; margin-bottom: 20px;">🛒</p>
            <p style="font-size: 20px; font-weight: 600; color: #1e293b;">No services selected yet.</p>
            <p>Please go back to Tab 2 and select a template.</p>
        </div>
    <?php else: ?>
        <div class="el-cart-items">
            <?php
            $total_engagement = 0;
            $total_expected = 0;
            
            foreach ($cart as $cart_item_key => $cart_item):
                $product = $cart_item['data'];
                $product_id = $cart_item['product_id'];
                $is_template = has_term('el-templates', 'product_cat', $product_id);
                
                $engagement_fee = get_field('engagement_fee', $product_id) ?: $product->get_price();
                $expected_cost = get_field('expected_total_cost', $product_id) ?: 0;
                
                $item_engagement = floatval($engagement_fee) * $cart_item['quantity'];
                $item_expected = floatval($expected_cost) * $cart_item['quantity'];
                
                $total_engagement += $item_engagement;
                $total_expected += $item_expected;
            ?>
                <div class="el-cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>" data-product-id="<?php echo $product_id; ?>">
                    
                    <div class="el-cart-item-header">
                        <div>
                            <div class="el-cart-item-title">
                                <?php echo esc_html($product->get_name()); ?>
                                <?php if ($is_template): ?>
                                    <span style="display: inline-block; margin-left: 12px; padding: 4px 12px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                                        TEMPLATE
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($desc = get_field('el_teaser', $product_id)): ?>
                                <p style="color: #6b7280; font-size: 16px; margin-top: 8px; line-height: 1.6;">
                                    <?php echo esc_html($desc); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="el-cart-item-prices">
                            <div class="el-price-badge">
                                <span class="el-engagement-fee">Engagement Fee</span>
                                <span class="el-engagement-amount">€<?php echo number_format($item_engagement, 0); ?></span>
                            </div>
                            
                            <?php if ($item_expected > 0): ?>
                                <span class="el-expected-total">Expected Total</span>
                                <span class="el-expected-amount">€<?php echo number_format($item_expected, 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($is_template): ?>
                        <div class="el-template-notice">
                            <p>ℹ️ This is your engagement letter template and cannot be removed or modified here.</p>
                        </div>
                    <?php else: ?>
                        <div class="el-cart-item-actions">
                            <label style="font-weight: 600; color: #374151;">Quantity:</label>
                            <input type="number" 
                                   class="el-qty-update" 
                                   data-key="<?php echo esc_attr($cart_item_key); ?>"
                                   value="<?php echo esc_attr($cart_item['quantity']); ?>"
                                   min="1"
                                   max="99">
                            
                            <button class="el-remove-item" 
                                    data-key="<?php echo esc_attr($cart_item_key); ?>">
                                🗑️ Remove
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    if (class_exists('WC_Product_Bundle') && $product->is_type('bundle')):
                        $bundle = new WC_Product_Bundle($product_id);
                        $bundled_items = $bundle->get_bundled_items();
                        
                        if (!empty($bundled_items)):
                            $has_optional = false;
                            foreach ($bundled_items as $bundled_item) {
                                if ($bundled_item->is_optional()) {
                                    $has_optional = true;
                                    break;
                                }
                            }
                            
                            if ($has_optional):
                    ?>
                        <div class="el-bundle-components">
                            <h4>📦 Optional Add-ons:</h4>
                            <?php foreach ($bundled_items as $bundled_item): 
                                $bundled_product = $bundled_item->get_product();
                                $is_optional = $bundled_item->is_optional();
                                
                                if ($is_optional):
                                    $bundled_item_id = $bundled_product->get_id();
                                    $is_checked = isset($cart_item['bundled_items'][$bundled_item_id]);
                            ?>
                                <label class="el-bundle-component">
                                    <input type="checkbox" 
                                           class="el-bundle-checkbox"
                                           data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                           data-bundle-id="<?php echo $product_id; ?>"
                                           data-item-id="<?php echo $bundled_item_id; ?>"
                                           <?php checked($is_checked); ?>>
                                    <strong><?php echo esc_html($bundled_product->get_name()); ?></strong>
                                    <span style="color: #031957ff; margin-left: 8px;">+€<?php echo number_format($bundled_product->get_price(), 2); ?></span>
                                </label>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php 
                            endif;
                        endif;
                    endif;
                    
                    if (class_exists('WC_Product_Composite') && $product->is_type('composite')):
                        $composite = new WC_Product_Composite($product_id);
                        $components = $composite->get_composite_data();
                        
                        if (!empty($components)):
                    ?>
                        <div class="el-bundle-components">
                            <h4>🔧 Configure Components:</h4>
                            <?php foreach ($components as $component_id => $component_data): ?>
                                <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px;">
                                    <strong style="display: block; margin-bottom: 8px;"><?php echo esc_html($component_data['title']); ?></strong>
                                    <select class="el-composite-component" 
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                            data-component-id="<?php echo $component_id; ?>"
                                            style="width: 100%; padding: 8px; border: 2px solid #e5e7eb; border-radius: 4px;">
                                        <option value="">Select option...</option>
                                        <?php
                                        $component_options = $component_data['assigned_ids'];
                                        foreach ($component_options as $option_id) {
                                            $option_product = wc_get_product($option_id);
                                            if ($option_product) {
                                                $selected = isset($cart_item['composite_data'][$component_id]['product_id']) && 
                                                           $cart_item['composite_data'][$component_id]['product_id'] == $option_id;
                                                echo '<option value="' . $option_id . '" ' . selected($selected, true, false) . '>' . 
                                                     esc_html($option_product->get_name()) . ' (+€' . number_format($option_product->get_price(), 2) . ')</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php
                        endif;
                    endif;
                    
                    if ($product->is_type('grouped')):
                        $grouped_products = $product->get_children();
                        if (!empty($grouped_products)):
                    ?>
                        <div class="el-bundle-components">
                            <h4>📋 Grouped Products:</h4>
                            <?php foreach ($grouped_products as $grouped_product_id): 
                                $grouped_product = wc_get_product($grouped_product_id);
                                if ($grouped_product):
                            ?>
                                <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span><strong><?php echo esc_html($grouped_product->get_name()); ?></strong></span>
                                    <span style="color: #031957ff;">€<?php echo number_format($grouped_product->get_price(), 2); ?></span>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php
                        endif;
                    endif;
                    ?>
                    
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="el-cart-totals">
            <h3>📋 Engagement Letter Summary</h3>
            
            <div class="el-total-row">
                <span class="el-total-label">Total Engagement Fee:</span>
                <strong class="el-total-value">€<?php echo number_format($total_engagement, 2); ?></strong>
            </div>
            
            <?php if ($total_expected > 0): ?>
            <div class="el-total-row">
                <span class="el-total-label">Expected Total Cost:</span>
                <strong style="font-size: 22px; font-weight: 700; color: #dc2626;">€<?php echo number_format($total_expected, 2); ?></strong>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="el-cart-actions">
            <button id="el-preview-pdf-btn">
                📄 Preview PDF →
            </button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
          // Replace the #el-preview-pdf-btn click handler in el_get_cart_editor_content()

$('#el-preview-pdf-btn').on('click', function(e) {
    e.preventDefault();
    
    console.log('🎯 Preview PDF button clicked');
    
    // Target the correct Tab 4: Preview Engagement Letter
    var $tab4 = $('#brxe-ihqhkg');
    
    if ($tab4.length) {
        console.log('✅ Found Tab 4: Preview Engagement Letter');
        
        // Trigger click on the tab container
        $tab4.trigger('click');
        $tab4.click();
        
        // Also try clicking the title inside (backup)
        $tab4.find('.el-tab-title').trigger('click');
        
        // Set ARIA attributes for accessibility
        $tab4.attr('aria-selected', 'true');
        $tab4.attr('tabindex', '0');
        
        // Remove selected state from other tabs
        $('.tab-title').not($tab4).attr('aria-selected', 'false').attr('tabindex', '-1');
        
        console.log('✅ Tab 4 clicked successfully');
    } else {
        console.error('❌ Tab 4 (#brxe-ihqhkg) not found');
        
        // Fallback: try class selector
        var $fallback = $('.el-tab-4').first();
        if ($fallback.length) {
            console.log('⚠️ Using fallback selector: .el-tab-4');
            $fallback.trigger('click');
        } else {
            alert('Could not find Preview tab. Please click it manually.');
        }
    }
});
            $('.el-qty-update').on('change', function() {
                var $input = $(this);
                var cartKey = $input.data('key');
                var newQty = parseInt($input.val());
                
                if (newQty < 1) {
                    $input.val(1);
                    return;
                }
                
                $input.prop('disabled', true);
                $('.el-cart-item[data-key="' + cartKey + '"]').addClass('el-updating');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_update_cart_quantity',
                        cart_key: cartKey,
                        quantity: newQty,
                        nonce: '<?php echo wp_create_nonce('el_cart_update'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to update quantity');
                            $input.prop('disabled', false);
                            $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $input.prop('disabled', false);
                        $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                    }
                });
            });
            
            $('.el-remove-item').on('click', function() {
                var $btn = $(this);
                var cartKey = $btn.data('key');
                
                if (!confirm('Remove this item from cart?')) {
                    return;
                }
                
                $btn.prop('disabled', true).text('Removing...');
                $('.el-cart-item[data-key="' + cartKey + '"]').addClass('el-updating');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_remove_cart_item',
                        cart_key: cartKey,
                        nonce: '<?php echo wp_create_nonce('el_cart_update'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to remove item');
                            $btn.prop('disabled', false).text('🗑️ Remove');
                            $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $btn.prop('disabled', false).text('🗑️ Remove');
                        $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                    }
                });
            });
            
            $('.el-bundle-checkbox').on('change', function() {
                var $checkbox = $(this);
                var cartKey = $checkbox.data('cart-key');
                var itemId = $checkbox.data('item-id');
                var isChecked = $checkbox.is(':checked');
                
                $checkbox.prop('disabled', true);
                $('.el-cart-item[data-key="' + cartKey + '"]').addClass('el-updating');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_update_bundle_item',
                        cart_key: cartKey,
                        item_id: itemId,
                        checked: isChecked ? 1 : 0,
                        nonce: '<?php echo wp_create_nonce('el_cart_update'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to update bundle');
                            $checkbox.prop('disabled', false).prop('checked', !isChecked);
                            $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $checkbox.prop('disabled', false).prop('checked', !isChecked);
                        $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                    }
                });
            });
            
            $('.el-composite-component').on('change', function() {
                var $select = $(this);
                var cartKey = $select.data('cart-key');
                var componentId = $select.data('component-id');
                var selectedProduct = $select.val();
                
                $select.prop('disabled', true);
                $('.el-cart-item[data-key="' + cartKey + '"]').addClass('el-updating');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_update_composite_component',
                        cart_key: cartKey,
                        component_id: componentId,
                        product_id: selectedProduct,
                        nonce: '<?php echo wp_create_nonce('el_cart_update'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Failed to update component');
                            $select.prop('disabled', false);
                            $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $select.prop('disabled', false);
                        $('.el-cart-item[data-key="' + cartKey + '"]').removeClass('el-updating');
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
    
    <?php
    return ob_get_clean();
}

// AJAX: Update cart quantity
add_action('wp_ajax_el_update_cart_quantity', 'el_ajax_update_cart_quantity');
add_action('wp_ajax_nopriv_el_update_cart_quantity', 'el_ajax_update_cart_quantity');

function el_ajax_update_cart_quantity() {
    check_ajax_referer('el_cart_update', 'nonce');
    
    $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (!$cart_key || $quantity < 1) {
        wp_send_json_error(['message' => 'Invalid parameters']);
    }
    
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $result = WC()->cart->set_quantity($cart_key, $quantity);
    
    if ($result) {
        WC()->cart->calculate_totals();
        wp_send_json_success(['message' => 'Quantity updated']);
    } else {
        wp_send_json_error(['message' => 'Failed to update quantity']);
    }
}

// AJAX: Remove cart item
add_action('wp_ajax_el_remove_cart_item', 'el_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_el_remove_cart_item', 'el_ajax_remove_cart_item');

function el_ajax_remove_cart_item() {
    check_ajax_referer('el_cart_update', 'nonce');
    
    $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
    
    if (!$cart_key) {
        wp_send_json_error(['message' => 'Invalid cart key']);
    }
    
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $result = WC()->cart->remove_cart_item($cart_key);
    
    if ($result) {
        WC()->cart->calculate_totals();
        wp_send_json_success(['message' => 'Item removed']);
    } else {
        wp_send_json_error(['message' => 'Failed to remove item']);
    }
}

// AJAX: Update bundle item
add_action('wp_ajax_el_update_bundle_item', 'el_ajax_update_bundle_item');
add_action('wp_ajax_nopriv_el_update_bundle_item', 'el_ajax_update_bundle_item');

function el_ajax_update_bundle_item() {
    check_ajax_referer('el_cart_update', 'nonce');
    
    $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
    $item_id = intval($_POST['item_id'] ?? 0);
    $checked = intval($_POST['checked'] ?? 0);
    
    if (!$cart_key || !$item_id) {
        wp_send_json_error(['message' => 'Invalid parameters']);
    }
    
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $cart = WC()->cart->get_cart();
    
    if (!isset($cart[$cart_key])) {
        wp_send_json_error(['message' => 'Cart item not found']);
    }
    
    $cart_item = $cart[$cart_key];
    
    if ($checked) {
        if (!isset($cart_item['bundled_items'])) {
            $cart_item['bundled_items'] = [];
        }
        $cart_item['bundled_items'][$item_id] = ['product_id' => $item_id];
    } else {
        if (isset($cart_item['bundled_items'][$item_id])) {
            unset($cart_item['bundled_items'][$item_id]);
        }
    }
    
    WC()->cart->cart_contents[$cart_key] = $cart_item;
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    
    wp_send_json_success(['message' => 'Bundle updated']);
}

// AJAX: Update composite component
add_action('wp_ajax_el_update_composite_component', 'el_ajax_update_composite_component');
add_action('wp_ajax_nopriv_el_update_composite_component', 'el_ajax_update_composite_component');

function el_ajax_update_composite_component() {
    check_ajax_referer('el_cart_update', 'nonce');
    
    $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
    $component_id = sanitize_text_field($_POST['component_id'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if (!$cart_key || !$component_id) {
        wp_send_json_error(['message' => 'Invalid parameters']);
    }
    
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $cart = WC()->cart->get_cart();
    
    if (!isset($cart[$cart_key])) {
        wp_send_json_error(['message' => 'Cart item not found']);
    }
    
    $cart_item = $cart[$cart_key];
    
    if (!isset($cart_item['composite_data'])) {
        $cart_item['composite_data'] = [];
    }
    
    if ($product_id) {
        $cart_item['composite_data'][$component_id] = ['product_id' => $product_id];
    } else {
        unset($cart_item['composite_data'][$component_id]);
    }
    
    WC()->cart->cart_contents[$cart_key] = $cart_item;
    WC()->cart->set_session();
    WC()->cart->calculate_totals();
    
    wp_send_json_success(['message' => 'Component updated']);
}
/**
 * =================================================================
 * PART 3: AJAX HANDLERS FOR CLEARING SESSION & CART
 * =================================================================
 */

add_action('wp_ajax_el_clear_session', 'el_clear_session');
add_action('wp_ajax_nopriv_el_clear_session', 'el_clear_session');

function el_clear_session() {
    check_ajax_referer('el_nonce', 'nonce');
    
    if (!session_id()) {
        // Session already started via init hook
    }
    
    // Clear all EL session variables
    unset($_SESSION['el_current_client_id']);
    unset($_SESSION['el_current_entry_id']);
    unset($_SESSION['el_client_name']);
    unset($_SESSION['el_client_email']);
    unset($_SESSION['el_client_scenario']);
    unset($_SESSION['el_selected_product']);
    unset($_SESSION['el_pdf_reference']);
    
    wp_send_json_success(['message' => 'Session cleared']);
}

add_action('wp_ajax_el_clear_cart', 'el_clear_cart');
add_action('wp_ajax_nopriv_el_clear_cart', 'el_clear_cart');

function el_clear_cart() {
    check_ajax_referer('el_nonce', 'nonce');
    
    if (WC()->cart) {
        WC()->cart->empty_cart();
    }
    
    wp_send_json_success(['message' => 'Cart cleared']);
}

/**
 * =================================================================
 * MERGE TAG REPLACEMENT SYSTEM
 * Replaces {{field_name}} placeholders with actual form data
 * =================================================================
 */

/**
 * Replace merge tags in content with form data
 * 
 * @param string $content Content with {{field_name}} placeholders
 * @param array $form_data Form data array from session
 * @param array $pdf_data PDF data array (optional, for additional fields)
 * @return string Content with placeholders replaced
 */
function el_replace_merge_tags($content, $form_data = [], $pdf_data = []) {
    if (empty($content)) {
        return $content;
    }
    
    // Get form data from session if not provided
    if (empty($form_data)) {
        if (!session_id()) {
            // Session already started via init hook
        }
        $form_data = $_SESSION['el_form_data'] ?? [];
    }
    
    // Build replacement map
    $replacements = [
        // Name fields
        '{{first_name}}' => $form_data['first_name'] ?? '',
        '{{last_name}}' => $form_data['last_name'] ?? '',
        '{{full_name}}' => $form_data['full_name'] ?? '',
        '{{name}}' => $form_data['full_name'] ?? '', // Alias
        '{{client_name}}' => $form_data['full_name'] ?? '', // Alias
        
        // Contact fields
        '{{email}}' => $form_data['email'] ?? '',
        '{{phone}}' => $form_data['phone'] ?? '',
        
        // Address fields
        '{{street_address}}' => $form_data['street_address'] ?? '',
        '{{city}}' => $form_data['city'] ?? '',
        '{{state}}' => $form_data['state'] ?? '',
        '{{zip}}' => $form_data['zip'] ?? '',
        '{{country}}' => $form_data['country'] ?? '',
        '{{full_address}}' => $form_data['full_address'] ?? '',
        '{{address}}' => $form_data['full_address'] ?? '', // Alias
        
        // Co-signer fields
        '{{cosigner_first_name}}' => $form_data['cosigner_first_name'] ?? '',
        '{{cosigner_last_name}}' => $form_data['cosigner_last_name'] ?? '',
        '{{cosigner_full_name}}' => $form_data['cosigner_full_name'] ?? '',
        '{{cosigner_name}}' => $form_data['cosigner_full_name'] ?? '', // Alias
        '{{has_cosigner}}' => ($form_data['add_cosigner'] ?? false) ? 'Yes' : 'No',
        
        // Notes
        '{{notes}}' => $form_data['notes'] ?? '',
        
        // PDF metadata (if provided)
        '{{date}}' => $pdf_data['date'] ?? current_time('F j, Y'),
        '{{reference}}' => $pdf_data['reference'] ?? '',
        '{{ref}}' => $pdf_data['reference'] ?? '', // Alias
    ];
    
    // Special handling for signature placeholders
    // Check if this is a signed version (has signature data)
    $is_signed_version = isset($pdf_data['signed']) && $pdf_data['signed'] === true;
    
    if ($is_signed_version) {
        // Replace with actual signature images for final signed PDF
        // These will appear at EVERY instance of {{sig}} or {{sig2}}
        if (!empty($pdf_data['signature_1_image'])) {
            $sig1_img = '<img src="' . esc_url($pdf_data['signature_1_image']) . '" alt="Client Signature" style="max-width:200px; height:auto; display:inline-block; vertical-align:middle;" />';
            $replacements['{{sig}}'] = $sig1_img;
            $replacements['{{sig1}}'] = $sig1_img; // Alias
            $replacements['{{SIG}}'] = $sig1_img;
            $replacements['{{SIG1}}'] = $sig1_img; // Alias
        } else {
            $replacements['{{sig}}'] = '________________________';
            $replacements['{{sig1}}'] = '________________________';
            $replacements['{{SIG}}'] = '________________________';
            $replacements['{{SIG1}}'] = '________________________';
        }
        
        if (!empty($pdf_data['signature_2_image'])) {
            $sig2_img = '<img src="' . esc_url($pdf_data['signature_2_image']) . '" alt="Co-signer Signature" style="max-width:200px; height:auto; display:inline-block; vertical-align:middle;" />';
            $replacements['{{sig2}}'] = $sig2_img;
            $replacements['{{SIG2}}'] = $sig2_img;
        } else {
            $replacements['{{sig2}}'] = '________________________';
            $replacements['{{SIG2}}'] = '________________________';
        }
        
        // Use actual signature date if available
        if (!empty($pdf_data['signature_date'])) {
            $replacements['{{date}}'] = $pdf_data['signature_date'];
            $replacements['{{DATE}}'] = $pdf_data['signature_date'];
        }
    }
    // If NOT signed version, leave {{sig}}, {{sig1}}, {{sig2}} intact for signature UI to detect
    // Don't add them to replacements array
    
    // CRITICAL: Before doing replacements, protect signature placeholders in unsigned versions
    // by temporarily replacing them with unique tokens that won't be affected by str_ireplace
    $protected_content = $content;
    if (!$is_signed_version) {
        // Protect ONLY signature placeholders (not {{date}})
        $sig_placeholders = ['{{sig}}', '{{sig1}}', '{{sig2}}', '{{SIG}}', '{{SIG1}}', '{{SIG2}}'];
        $protection_map = [];
        
        foreach ($sig_placeholders as $placeholder) {
            $token = '___PROTECTED_' . md5($placeholder) . '___';
            $protection_map[$token] = $placeholder;
            $protected_content = str_replace($placeholder, $token, $protected_content);
        }
    }
    
    // Add uppercase variants for backwards compatibility
    foreach ($replacements as $key => $value) {
        $upper_key = strtoupper($key);
        if (!isset($replacements[$upper_key])) {
            $replacements[$upper_key] = $value;
        }
    }
    
    // Perform replacements (case-insensitive)
    $content = str_ireplace(array_keys($replacements), array_values($replacements), $protected_content);
    
    // Restore protected signature placeholders in unsigned versions
    if (!$is_signed_version && isset($protection_map)) {
        foreach ($protection_map as $token => $placeholder) {
            $content = str_replace($token, $placeholder, $content);
        }
    }
    
    return $content;
}

/**
 * Enqueue signature UI JavaScript
 */
add_action('wp_enqueue_scripts', 'el_enqueue_signature_ui');

function el_enqueue_signature_ui() {
    // Only load on pages that might have PDF previews or signature collection
    // Check for: signature view (?el_view=1), any page with ?ref parameter, or singular posts
    $should_load = (
        isset($_GET['el_view']) || 
        isset($_GET['ref']) || 
        isset($_GET['el_sign']) ||
        is_singular('engagement_letter')
    );
    
    if ($should_load) {
        wp_enqueue_script(
            'el-signature-ui',
            get_stylesheet_directory_uri() . '/js/el-signature-ui.js',
            ['jquery'],
            '1.0.1', // Increment version to bust cache
            true
        );
        
        wp_localize_script('el-signature-ui', 'elSignatureData', [
            'nonce' => wp_create_nonce('el_signature_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
        
        // Log for debugging
        error_log('EL Signature UI: JavaScript enqueued on ' . $_SERVER['REQUEST_URI']);
    }
}

/**
 * =================================================================
 * SIGNATURE WORKFLOW - CLIENT SIGNS ENGAGEMENT LETTER
 * =================================================================
 */

/**
 * Handle signature submission from client
 */
add_action('wp_ajax_el_submit_signature', 'el_ajax_submit_signature');
add_action('wp_ajax_nopriv_el_submit_signature', 'el_ajax_submit_signature');

function el_ajax_submit_signature() {
    // Log for debugging
    error_log('EL Signature Submission - POST data: ' . print_r($_POST, true));
    
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'el_signature_nonce')) {
        error_log('EL Signature Error: Invalid nonce');
        wp_send_json_error(['message' => 'Security check failed. Please refresh the page and try again.']);
    }
    
    // Get reference and signature data
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    $signature_1 = $_POST['signature_1'] ?? ''; // Don't sanitize base64 image data
    $signature_2 = $_POST['signature_2'] ?? '';
    
    if (empty($reference)) {
        error_log('EL Signature Error: Missing reference number');
        wp_send_json_error(['message' => 'Missing reference number. Please refresh the page and try again.']);
    }
    
    // Validate signature 1 exists
    if (empty($signature_1) || !str_starts_with($signature_1, 'data:image/png;base64,')) {
        error_log('EL Signature Error: Invalid signature 1 data');
        wp_send_json_error(['message' => 'Invalid signature data. Please sign again.']);
    }
    
    // Get original PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    
    if (!$pdf_data) {
        error_log('EL Signature Error: PDF data not found for reference: ' . $reference);
        wp_send_json_error(['message' => 'Engagement letter expired or not found. Please request a new one from your lawyer.']);
    }
    
    error_log('EL Signature: Found PDF data for reference: ' . $reference);
    
    // Save signatures as base64 data URLs (from canvas)
    $pdf_data['signature_1_image'] = $signature_1;
    if (!empty($signature_2) && str_starts_with($signature_2, 'data:image/png;base64,')) {
        $pdf_data['signature_2_image'] = $signature_2;
    }
    
    // Mark as signed
    $pdf_data['signed'] = true;
    $pdf_data['signature_date'] = current_time('F j, Y');
    $pdf_data['signature_timestamp'] = current_time('mysql');
    $pdf_data['signature_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Generate new reference for signed version
    $signed_reference = $reference . '-SIGNED';
    
    // Store signed PDF data with 89-day expiry
    set_transient('el_pdf_data_' . $signed_reference, $pdf_data, 89 * DAY_IN_SECONDS);
    
    error_log('EL Signature: Stored signed PDF with reference: ' . $signed_reference);
    
    // Update the engagement letter post with signed PDF link
    $el_id = $pdf_data['el_post_id'] ?? 0;
    $signed_pdf_url = '';
    
    if ($el_id) {
        update_post_meta($el_id, 'el_signed_reference', $signed_reference);
        update_post_meta($el_id, 'el_signature_date', $pdf_data['signature_date']);
        update_post_meta($el_id, 'el_signature_timestamp', $pdf_data['signature_timestamp']);
        update_post_meta($el_id, 'el_status', 'signed');
        
        // Generate signed PDF download URL (89-day expiry)
        $signed_pdf_url = add_query_arg([
            'action' => 'el_download_final_pdf',
            'ref' => $signed_reference
        ], site_url());
        
        update_post_meta($el_id, 'el_signed_pdf_url', $signed_pdf_url);
        update_post_meta($el_id, 'el_signed_pdf_expiry', date('Y-m-d H:i:s', time() + (89 * DAY_IN_SECONDS)));
        
        error_log('EL Signature: Updated post meta for EL ID: ' . $el_id);
    } else {
        // Fallback if no EL post ID
        $signed_pdf_url = add_query_arg([
            'action' => 'el_download_final_pdf',
            'ref' => $signed_reference
        ], site_url());
        
        error_log('EL Signature: No EL post ID found, using fallback URL');
    }
    
    // Email lawyer with signed PDF link
    $lawyer_email = $pdf_data['lawyer_email'] ?? get_option('admin_email');
    $client_name = $pdf_data['form_data']['full_name'] ?? 'Client';
    
    $email_subject = sprintf('Engagement Letter Signed: %s (%s)', $client_name, $reference);
    $email_body = sprintf(
        "Good news! Your client has signed their engagement letter.\n\n" .
        "Client: %s\n" .
        "Reference: %s\n" .
        "Signed: %s\n\n" .
        "Download signed PDF (valid for 89 days):\n%s\n\n" .
        "This link expires on: %s",
        $client_name,
        $reference,
        $pdf_data['signature_date'],
        $signed_pdf_url,
        date('F j, Y', time() + (89 * DAY_IN_SECONDS))
    );
    
    $email_sent = wp_mail($lawyer_email, $email_subject, $email_body);
    error_log('EL Signature: Email sent to ' . $lawyer_email . ' - ' . ($email_sent ? 'SUCCESS' : 'FAILED'));
    
    // Return success with download link for client
    wp_send_json_success([
        'message' => 'Thank you! Your engagement letter has been signed.',
        'download_url' => $signed_pdf_url,
        'reference' => $signed_reference,
        'expiry_days' => 89
    ]);
}

/**
 * =================================================================
 * PART 4: ENHANCED PDF PREVIEW GENERATION
 * =================================================================
 */

add_action('wp_ajax_el_generate_pdf_preview', 'el_ajax_generate_pdf_preview');
add_action('wp_ajax_nopriv_el_generate_pdf_preview', 'el_ajax_generate_pdf_preview');

function el_ajax_generate_pdf_preview() {
    check_ajax_referer('el_nonce', 'nonce');
    
    if (!session_id()) {
        // Session already started via init hook
    }
    
    // Gather client info from session
    $client_name = $_SESSION['el_client_name'] ?? 'Client';
    $client_email = $_SESSION['el_client_email'] ?? '';
    $client_id = $_SESSION['el_current_client_id'] ?? 0;
    $form_data = $_SESSION['el_form_data'] ?? [];
    
    // Get cart items
    $cart_items = WC()->cart->get_cart();
    
    if (empty($cart_items)) {
        wp_send_json_error(['message' => 'No items in engagement letter']);
    }
    
    // Build PDF data structure
    $pdf_data = [
        'date' => current_time('F j, Y'),
        'reference' => 'EL-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6),
        'client' => [
            'id' => $client_id,
            'name' => $client_name,
            'email' => $client_email
        ],
        'form_data' => $form_data, // Include all form data for merge tags
        'lawyer_email' => wp_get_current_user()->user_email, // For signature notifications
        'items' => [],
        'total_engagement_fee' => 0,
        'total_expected_cost' => 0
    ];
    
    // Process each cart item
    foreach ($cart_items as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        
        // Get ACF fields
        $engagement_fee = get_field('engagement_fee', $product_id) ?: $product->get_price();
        $expected_cost = get_field('expected_total_cost', $product_id) ?: 0;
        
        // Get PDF-specific fields and apply merge tags
        $pdf_title_raw = get_field('pdf_title', $product_id) ?: $product->get_name();
        $pdf_subtitle_raw = get_field('pdf_subtitle', $product_id) ?: '';
        $pdf_text_raw = get_field('pdf_text', $product_id) ?: $product->get_description();
        $pdf_footer_raw = get_field('pdf_footer', $product_id) ?: '';
        
        $item_data = [
            'name' => $product->get_name(),
            'quantity' => $quantity,
            'engagement_fee' => floatval($engagement_fee) * $quantity,
            'expected_cost' => floatval($expected_cost) * $quantity,
            'pdf_title' => el_replace_merge_tags($pdf_title_raw, $form_data, $pdf_data),
            'pdf_subtitle' => el_replace_merge_tags($pdf_subtitle_raw, $form_data, $pdf_data),
            'pdf_text' => el_replace_merge_tags($pdf_text_raw, $form_data, $pdf_data),
            'pdf_clauses' => get_field('pdf_clauses', $product_id) ?: [],
            'pdf_annexes' => get_field('pdf_annexes', $product_id) ?: [],
            'pdf_footer' => el_replace_merge_tags($pdf_footer_raw, $form_data, $pdf_data),
            'fee_structure' => get_field('fee_structure', $product_id) ?: ''
        ];
        
        $pdf_data['items'][] = $item_data;
        $pdf_data['total_engagement_fee'] += $item_data['engagement_fee'];
        $pdf_data['total_expected_cost'] += $item_data['expected_cost'];
    }
    
    // Store in transient for download
    $reference = $pdf_data['reference'];
    set_transient('el_pdf_data_' . $reference, $pdf_data, HOUR_IN_SECONDS);
    $_SESSION['el_pdf_reference'] = $reference;
    
    // Store EL post ID in PDF data for later reference
    // Check both possible session variable names
    $el_post_id = $_SESSION['el_engagement_letter_id'] ?? $_SESSION['el_current_post_id'] ?? 0;
    
    if (!empty($el_post_id)) {
        $pdf_data['el_post_id'] = $el_post_id;
        set_transient('el_pdf_data_' . $reference, $pdf_data, HOUR_IN_SECONDS);
        
        // CRITICAL: Save reference to post meta so signature link can be generated
        update_post_meta($el_post_id, 'el_reference', $reference);
        update_post_meta($el_post_id, 'el_status', 'draft'); // Mark as draft until signed
        
        // Also save form data and cart to post meta for record keeping
        update_post_meta($el_post_id, 'el_form_data', $form_data);
        update_post_meta($el_post_id, 'el_pdf_generated_date', current_time('mysql'));
        
        // Log for debugging
        error_log('EL PDF Generated: Saved reference ' . $reference . ' to post ID ' . $el_post_id);
    } else {
        error_log('EL PDF Generated: WARNING - No engagement letter post ID found in session');
    }
    
    // Generate HTML for preview with reference embedded
    $html = el_render_pdf_preview_html($pdf_data);
    
    wp_send_json_success([
        'html' => $html,
        'reference' => $reference,
        'pdf_url' => add_query_arg(['action' => 'el_download_final_pdf', 'ref' => $reference], site_url()),
        'total_engagement_fee' => wc_price($pdf_data['total_engagement_fee']),
        'total_expected_cost' => wc_price($pdf_data['total_expected_cost'])
    ]);
}

/**
 * Render PDF Preview HTML (WYSIWYG fields properly rendered)
 */
function el_render_pdf_preview_html($pdf_data) {
    // Get form data for merge tags
    $form_data = $pdf_data['form_data'] ?? [];
    
    // Get boilerplate content from ACF options
    $letterhead_raw = get_field('boilerplate_letterhead', 'option') ?: '';
    $top_left_raw = get_field('boilerplate_opening_tl', 'option') ?: '';
    $top_right_raw = get_field('boilerplate_opening_tr_copy', 'option') ?: '';
    $footer_boilerplate_raw = get_field('footer_boilerplate', 'option') ?: '';
    $signature_block_raw = get_field('signature_block_template', 'option') ?: '';
    $firm_footer_raw = get_field('firm_footer', 'option') ?: '';
    
    // Apply merge tags to all boilerplate content
    $letterhead = el_replace_merge_tags($letterhead_raw, $form_data, $pdf_data);
    $top_left = el_replace_merge_tags($top_left_raw, $form_data, $pdf_data);
    $top_right = el_replace_merge_tags($top_right_raw, $form_data, $pdf_data);
    $footer_boilerplate = el_replace_merge_tags($footer_boilerplate_raw, $form_data, $pdf_data);
    $signature_block = el_replace_merge_tags($signature_block_raw, $form_data, $pdf_data);
    $firm_footer = el_replace_merge_tags($firm_footer_raw, $form_data, $pdf_data);
    
    $allowed_html = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [],
        'u' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'blockquote' => [], 'a' => ['href' => true, 'target' => true],
        'span' => ['style' => true], 'div' => ['style' => true], 'img' => ['src' => true, 'alt' => true, 'style' => true]
    ];
    
    ob_start();
    ?>
    <style>
    /* PDF Preview Styles - ALL FIXES APPLIED */
    .el-pdf-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        line-height: 1.8;
        color: #1a1a2e;
        background: #ffffff;
        font-size: 18px;
    }
    
    /* Header Section - WHITE BACKGROUND */
    .el-pdf-header {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px;
        margin-bottom: 40px;
        border: 2px solid #e5e7eb;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    }
    
    .el-letterhead {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 3px solid #4a90e2;
    }
    
    .el-letterhead h1 {
        color: #1a1a2e;
        margin: 0 0 12px 0;
        font-size: 36px;
        font-weight: 700;
        line-height: 1.3;
    }
    
    .el-letterhead p {
        font-size: 18px;
        line-height: 1.8;
        margin: 10px 0;
    }
    
    .el-header-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 30px;
    }
    
    .el-header-left, .el-header-right {
        font-size: 18px;
        line-height: 1.8;
    }
    
    .el-header-left p, .el-header-right p {
        margin: 10px 0;
        line-height: 1.8;
    }
    
    .el-header-right {
        text-align: right;
    }
    
    /* Product Introduction Text - ABOVE EACH PRODUCT CARD */
    .el-product-introduction {
        margin: 40px 0 20px 0;
        padding: 25px 30px;
        background: #ffffff;
        border-radius: 12px;
        border-left: 4px solid #4a90e2;
        border-right: 1px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 18px;
        line-height: 1.8;
    }
    
    .el-product-introduction p {
        margin: 12px 0;
        line-height: 1.8;
    }
    
    /* Service Cards */
    .el-services-section {
        margin: 40px 0;
    }
    
    .el-service-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e5e7eb;
        margin-bottom: 48px; /* More space between products */
    }
    
    .el-service-card:hover {
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
        border-color: #4a90e2;
        transform: translateY(-4px);
    }
    
    .el-service-inner {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 32px;
        padding: 40px;
        align-items: center;
    }
    
    /* Left Content Area */
    .el-service-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .el-service-header {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .el-service-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.3;
        margin: 0;
    }
    
    .el-service-subtitle {
        color: #6b7280;
        font-size: 20px;
        font-weight: 500;
        margin: 0;
        line-height: 1.6;
    }
    
    .el-service-text {
        font-size: 18px;
        color: #444;
        line-height: 1.8;
        margin: 0;
    }
    
    .el-service-text p {
        margin: 12px 0;
        line-height: 1.8;
    }
    
    .el-service-clauses {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .el-service-clauses h4 {
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin: 0 0 12px 0;
        font-weight: 600;
    }
    
    .el-service-clauses ul {
        margin: 0;
        padding-left: 24px;
        list-style: disc;
    }
    
    .el-service-clauses li {
        margin-bottom: 10px;
        color: #1e293b;
        line-height: 1.8;
        font-size: 17px;
    }
    
    /* Right Pricing Area */
    .el-service-pricing {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 24px;
        min-width: 280px;
    }
    
    .el-price-card {
        background: linear-gradient(135deg, #d5e4f6ff 0%, #bad8f6ff 100%);
        padding: 24px;
        text-align: center;
        border-radius: 12px;
        width: 100%;
    }
    
    .el-price-label {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #1e40af;
        font-weight: 700;
        margin-bottom: 12px;
        display: block;
    }
    
    .el-price-amount {
        font-size: 48px;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1;
       
    }
    
    .el-price-currency {
        font-size: 24px;
         margin-right: 4px;
    }
    
    .el-price-note {
        font-size: 15px;
        color: #64748b;
        font-weight: 500;
        font-style: italic;
    }
    
    /* Summary Section */
    .el-summary-section {
        margin-top: 40px;
        padding: 30px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 3px solid #3b82f6;
        border-radius: 16px;
    }
    
    .el-summary-title {
        font-size: 26px;
        font-weight: 700;
        color: #1e40af;
        margin: 0 0 20px 0;
    }
    
    .el-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #bfdbfe;
    }
    
    .el-summary-row:last-child {
        border-bottom: none;
        padding-top: 20px;
        margin-top: 10px;
        border-top: 2px solid #3b82f6;
    }
    
    .el-summary-label {
        font-size: 18px;
        color: #1e40af;
        font-weight: 600;
    }
    
    .el-summary-value {
        font-size: 30px;
        font-weight: 800;
        color: #031957ff;
    }
    
    /* Footer Sections - WHITE BACKGROUNDS */
    .el-footer-section {
        margin-top: 40px;
        padding: 30px;
        background: #ffffff;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        font-size: 18px;
        line-height: 1.8;
    }
    
    .el-footer-section p {
        margin: 12px 0;
        line-height: 1.8;
    }
    
    .el-signature-section {
        margin-top: 40px;
        padding: 30px;
        background: #fffbeb;
        border: 2px solid #fbbf24;
        border-radius: 12px;
        font-size: 18px;
        line-height: 1.8;
    }
    
    .el-signature-section p {
        margin: 12px 0;
        line-height: 1.8;
    }
    
    .el-firm-footer {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 3px solid #e5e7eb;
        text-align: center;
        color: #6b7280;
        font-size: 16px;
        line-height: 1.8;
    }
    
    .el-firm-footer p {
        margin: 10px 0;
        line-height: 1.8;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .el-service-inner {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        .el-service-pricing {
            align-items: stretch;
            width: 100%;
        }
        
        .el-header-columns {
            grid-template-columns: 1fr;
        }
    }
    
    @media print {
        .el-service-card {
            page-break-inside: avoid;
        }
    }
    </style>
    
    <div class="el-pdf-content" data-el-reference="<?php echo esc_attr($pdf_data['reference'] ?? ''); ?>">
        <!-- Header Section -->
        <div class="el-pdf-header">
            <?php if ($letterhead): ?>
            <div class="el-letterhead">
                <?php echo wpautop(wp_kses_post($letterhead)); ?>
            </div>
            <?php endif; ?>
            
            <div class="el-header-columns">
                <div class="el-header-left">
                    <?php echo wpautop(wp_kses_post($top_left)); ?>
                </div>
                <div class="el-header-right">
                    <?php 
                    $top_right_content = str_replace(
                        ['{DATE}', '{REFERENCE}', '{CLIENT_NAME}'],
                        [
                            esc_html($pdf_data['date']),
                            esc_html($pdf_data['reference']),
                            esc_html($pdf_data['client']['name'])
                        ],
                        $top_right
                    );
                    echo wpautop(wp_kses_post($top_right_content));
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Services Section - Each Product with Its Introduction -->
        <div class="el-services-section">
            <?php foreach ($pdf_data['items'] as $item): ?>
                
                <?php 
                // Get introduction text for THIS product
                $product_intro = '';
                if (isset($item['product_id'])) {
                    $product_intro = get_field('el_introduction_texts', $item['product_id']);
                }
                ?>
                
                <?php if ($product_intro): ?>
                <!-- Product Introduction Text - ABOVE THIS PRODUCT -->
                <div class="el-product-introduction">
                    <?php echo wpautop(wp_kses_post($product_intro)); ?>
                </div>
                <?php endif; ?>
                
                <!-- Product Card -->
                <div class="el-service-card">
                    <div class="el-service-inner">
                        <!-- Left: Content -->
                        <div class="el-service-content">
                            <div class="el-service-header">
                                <h3 class="el-service-title">
                                    <?php echo esc_html($item['pdf_title']); ?>
                                </h3>
                                <?php if (!empty($item['pdf_subtitle'])): ?>
                                <p class="el-service-subtitle">
                                    <?php echo esc_html($item['pdf_subtitle']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($item['pdf_text'])): ?>
                            <div class="el-service-text">
                                <?php echo wpautop(wp_kses($item['pdf_text'], $allowed_html)); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['pdf_clauses'])): ?>
                            <div class="el-service-clauses">
                                <h4>Key Terms:</h4>
                                <ul>
                                    <?php foreach ($item['pdf_clauses'] as $clause): ?>
                                    <li><?php echo wpautop(wp_kses(is_array($clause) ? ($clause['text'] ?? '') : $clause, $allowed_html)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['pdf_footer'])): ?>
                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e5e7eb; font-size: 16px; color: #6b7280;">
                                <?php echo wpautop(wp_kses($item['pdf_footer'], $allowed_html)); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Right: Pricing -->
                        <div class="el-service-pricing">
                            <div class="el-price-card">
                                <span class="el-price-label">Engagement Fee</span>
                                <div class="el-price-amount">
                                    <span class="el-price-currency">€</span><?php echo number_format($item['engagement_fee'], 0); ?>
                                </div>
                                <div class="el-price-note">Due upon signing</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Summary Section -->
        <div class="el-summary-section">
            <h3 class="el-summary-title">📋 Engagement Letter Summary</h3>
            
            <div class="el-summary-row">
                <span class="el-summary-label">Client:</span>
                <span style="font-weight: 600; color: #1e293b; font-size: 18px;"><?php echo esc_html($pdf_data['client']['name']); ?></span>
            </div>
            
            <div class="el-summary-row">
                <span class="el-summary-label">Reference:</span>
                <span style="font-weight: 600; color: #1e293b; font-size: 18px;"><?php echo esc_html($pdf_data['reference']); ?></span>
            </div>
            
            <div class="el-summary-row">
                <span class="el-summary-label">Date:</span>
                <span style="font-weight: 600; color: #1e293b; font-size: 18px;"><?php echo esc_html($pdf_data['date']); ?></span>
            </div>
            
            <div class="el-summary-row">
                <span class="el-summary-label">Total Engagement Fee:</span>
                <span class="el-summary-value">
                    €<?php echo number_format($pdf_data['total_engagement_fee'], 2); ?>
                </span>
            </div>
            
            <?php if ($pdf_data['total_expected_cost'] > 0): ?>
            <div class="el-summary-row" style="border-top: 1px solid #bfdbfe; padding-top: 12px;">
                <span class="el-summary-label">Expected Total Cost:</span>
                <span style="font-size: 22px; font-weight: 700; color: #dc2626;">
                    €<?php echo number_format($pdf_data['total_expected_cost'], 2); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Boilerplate -->
        <?php if ($footer_boilerplate): ?>
        <div class="el-footer-section">
            <?php echo wpautop(wp_kses_post($footer_boilerplate)); ?>
        </div>
        <?php endif; ?>
        
        <!-- Signature Block -->
        <?php if ($signature_block): ?>
        <div class="el-signature-section">
            <?php echo wpautop(wp_kses_post($signature_block)); ?>
        </div>
        <?php endif; ?>
        
        <!-- Firm Footer -->
        <?php if ($firm_footer): ?>
        <div class="el-firm-footer">
            <?php echo wpautop(wp_kses_post($firm_footer)); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * =================================================================
 * PART 5: ENHANCED CART EDITOR FOR TAB 3
 * Updated to prevent removal/quantity changes for el-templates products
 * =================================================================
 */
/**
 * =================================================================
 * GLOBAL "START OVER" BUTTON
 * Clears cart, session, and resets wizard to Tab 1
 * =================================================================
 */

// Shortcode to display the Start Over button
add_shortcode('el_start_over_button', 'el_render_start_over_button');

function el_render_start_over_button($atts) {
    // Parse attributes for customization
    $atts = shortcode_atts([
        'text' => 'Start Over',
        'style' => 'default', // default, danger, outline
        'position' => 'inline', // inline, fixed-top, fixed-bottom
        'confirm' => 'yes' // yes or no - show confirmation dialog
    ], $atts);
    
    $button_text = esc_html($atts['text']);
    $show_confirm = ($atts['confirm'] === 'yes');
    
    // Style variations
    $button_styles = [
        'default' => 'background: #6b7280; color: white; border: 2px solid #6b7280;',
        'danger' => 'background: #dc2626; color: white; border: 2px solid #dc2626;',
        'outline' => 'background: transparent; color: #dc2626; border: 2px solid #dc2626;'
    ];
    
    $selected_style = $button_styles[$atts['style']] ?? $button_styles['default'];
    
    // Position variations
    $wrapper_styles = [
        'inline' => 'display: inline-block;',
        'fixed-top' => 'position: fixed; top: 20px; right: 20px; z-index: 9999;',
        'fixed-bottom' => 'position: fixed; bottom: 20px; right: 20px; z-index: 9999;'
    ];
    
    $selected_position = $wrapper_styles[$atts['position']] ?? $wrapper_styles['inline'];
    
    ob_start();
    ?>
    
    <div class="el-start-over-wrapper" style="<?php echo $selected_position; ?>">
        <button 
            id="el-start-over-btn" 
            class="el-start-over-button"
            data-confirm="<?php echo $show_confirm ? '1' : '0'; ?>"
            style="<?php echo $selected_style; ?> padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
            🔄 <?php echo $button_text; ?>
        </button>
    </div>
    
    <style>
    .el-start-over-button:hover {
        opacity: 0.8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .el-start-over-button:active {
        transform: translateY(0);
    }
    
    .el-start-over-button.loading {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    /* Confirmation modal styles */
    .el-confirm-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99999;
        align-items: center;
        justify-content: center;
    }
    
    .el-confirm-modal.active {
        display: flex;
    }
    
    .el-confirm-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 450px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .el-confirm-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px 0;
    }
    
    .el-confirm-message {
        font-size: 15px;
        color: #6b7280;
        margin: 0 0 24px 0;
        line-height: 1.6;
    }
    
    .el-confirm-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    
    .el-confirm-btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .el-confirm-cancel {
        background: #f3f4f6;
        color: #374151;
    }
    
    .el-confirm-cancel:hover {
        background: #e5e7eb;
    }
    
    .el-confirm-proceed {
        background: #dc2626;
        color: white;
    }
    
    .el-confirm-proceed:hover {
        background: #b91c1c;
    }
    </style>
    
    <!-- Confirmation Modal -->
    <div id="el-confirm-modal" class="el-confirm-modal">
        <div class="el-confirm-content">
            <h3 class="el-confirm-title">⚠️ Start Over?</h3>
            <p class="el-confirm-message">
                This will clear your current progress, remove all selected services, and reset the engagement letter wizard. This action cannot be undone.
            </p>
            <div class="el-confirm-actions">
                <button class="el-confirm-btn el-confirm-cancel" id="el-confirm-cancel">
                    Cancel
                </button>
                <button class="el-confirm-btn el-confirm-proceed" id="el-confirm-proceed">
                    Yes, Start Over
                </button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('🔄 Start Over button initialized');
        
        var $startOverBtn = $('#el-start-over-btn');
        var $confirmModal = $('#el-confirm-modal');
        var showConfirm = $startOverBtn.data('confirm') == 1;
        
        // Handle Start Over button click
        $startOverBtn.on('click', function(e) {
            e.preventDefault();
            
            if ($startOverBtn.hasClass('loading')) {
                return;
            }
            
            if (showConfirm) {
                // Show confirmation modal
                $confirmModal.addClass('active');
            } else {
                // Proceed directly
                executeStartOver();
            }
        });
        
        // Handle Cancel in modal
        $('#el-confirm-cancel').on('click', function() {
            $confirmModal.removeClass('active');
        });
        
        // Handle Proceed in modal
        $('#el-confirm-proceed').on('click', function() {
            $confirmModal.removeClass('active');
            executeStartOver();
        });
        
        // Close modal on background click
        $confirmModal.on('click', function(e) {
            if ($(e.target).is('#el-confirm-modal')) {
                $confirmModal.removeClass('active');
            }
        });
        
        // Execute the start over action
        function executeStartOver() {
            console.log('🔄 Starting over...');
            
            $startOverBtn.addClass('loading').text('Resetting...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_start_over',
                    nonce: '<?php echo wp_create_nonce('el_start_over'); ?>'
                },
                success: function(response) {
                    console.log('✅ Reset response:', response);
                    
                    if (response.success) {
                        // Show success message
                        $startOverBtn.text('✓ Reset Complete');
                        
                        // Wait a moment then switch to Tab 1
                        setTimeout(function() {
                            // Try Bricks tabs first
                            var $firstTab = $('.brxe-tabs [data-tab-index="0"]');
                            if ($firstTab.length) {
                                console.log('🎯 Switching to Bricks tab 1');
                                $firstTab.click();
                            } else {
                                // Try custom tabs
                                $firstTab = $('[data-tab="1"]');
                                if ($firstTab.length) {
                                    console.log('🎯 Switching to custom tab 1');
                                    $firstTab.click();
                                } else {
                                    // Fallback: reload page
                                    console.log('🔄 Reloading page...');
                                    window.location.reload();
                                }
                            }
                            
                            // Reset button text
                            setTimeout(function() {
                                $startOverBtn.removeClass('loading').text('🔄 <?php echo $button_text; ?>');
                            }, 500);
                        }, 800);
                    } else {
                        $startOverBtn.removeClass('loading').text('Error - Try Again');
                        console.error('❌ Error:', response.data);
                        
                        setTimeout(function() {
                            $startOverBtn.text('🔄 <?php echo $button_text; ?>');
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    $startOverBtn.removeClass('loading').text('Error - Try Again');
                    
                    setTimeout(function() {
                        $startOverBtn.text('🔄 <?php echo $button_text; ?>');
                    }, 3000);
                }
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_el_cart_test', 'el_cart_test');
add_action('wp_ajax_nopriv_el_cart_test', 'el_cart_test');

function el_cart_test() {
    wp_send_json_success(['message' => 'Cart test works!', 'wc_exists' => class_exists('WooCommerce')]);
}

/**
 * =================================================================
 * AJAX HANDLER: Start Over
 * Clears cart, session data, and resets wizard
 * =================================================================
 */
add_action('wp_ajax_el_start_over', 'el_handle_start_over');
add_action('wp_ajax_nopriv_el_start_over', 'el_handle_start_over');

function el_handle_start_over() {
    // Verify nonce
    if (!check_ajax_referer('el_start_over', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Clear WooCommerce cart
    if (WC()->cart) {
        WC()->cart->empty_cart();
    }
    
    // Clear session data
    if (!session_id()) {
        // Session already started via init hook
    }
    
    // Remove all EL-related session variables
    $session_keys = [
        'el_current_client_id',
        'el_client_name',
        'el_client_email',
        'el_client_scenario',
        'el_selected_template'
    ];
    
    foreach ($session_keys as $key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    // Clear any WooCommerce session data
    if (WC()->session) {
        WC()->session->set('el_wizard_data', null);
    }
    
    wp_send_json_success([
        'message' => 'Wizard reset successfully',
        'redirect_to_tab' => 1
    ]);
}







add_shortcode('el_cart_editor_enhanced', 'el_render_cart_editor_enhanced');

function el_render_cart_editor_enhanced() {
    ob_start();
    ?>
    <div id="el-cart-editor">
        <?php echo el_get_cart_editor_content(); ?>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Uses el_render_pdf_preview_html() 
 */
add_shortcode('el_engagement_preview', function($atts){
    $atts = shortcode_atts(['ref' => ''], $atts, 'el_engagement_preview');
    $ref = sanitize_text_field($atts['ref'] ?: ($_GET['ref'] ?? ''));
    if (!$ref) return '<div>Missing reference (?ref=...)</div>';

    $pdf_data = get_transient('el_pdf_data_' . $ref);
    if (!$pdf_data) {
        // Try Gravity Forms meta fallback if present
        if (class_exists('GFAPI')) {
            $entries = GFAPI::get_entries(0, ['status'=>'active','field_filters'=>[['key'=>'pdf_reference','value'=>$ref]]]);
            if (!is_wp_error($entries) && !empty($entries)) {
                $json = gform_get_meta($entries[0]['id'], 'engagement_data');
                if ($json) $pdf_data = json_decode($json, true);
            }
        }
    }
    if (!$pdf_data) return '<div>Preview expired or not found.</div>';

    if (!function_exists('el_render_pdf_preview_html')) {
        return '<div>Renderer not found: el_render_pdf_preview_html().</div>';
    }
    return el_render_pdf_preview_html($pdf_data);
});

// Add nonce to JavaScript
add_action('wp_enqueue_scripts', 'el_enqueue_scripts');
function el_enqueue_scripts() {
    wp_localize_script('jquery', 'el_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('el_nonce')
    ]);
}
/**
 * AJAX: Search and load complete customer billing details
 */
add_action('wp_ajax_el_search_clients', 'el_ajax_search_clients');
add_action('wp_ajax_nopriv_el_search_clients', 'el_ajax_search_clients');

function el_ajax_search_clients() {
    check_ajax_referer('el_nonce', 'nonce');
    
    $search = sanitize_text_field($_POST['search'] ?? '');
    
    if (strlen($search) < 2) {
        wp_send_json_success(['clients' => []]);
    }
    
    // Search WooCommerce customers
    $args = [
        'role__in' => ['customer', 'administrator'],
        'search' => "*{$search}*",
        'search_columns' => ['user_email', 'user_login', 'display_name'],
        'number' => 10,
        'orderby' => 'display_name',
        'order' => 'ASC'
    ];
    
    $user_query = new WP_User_Query($args);
    $clients = [];
    
    foreach ($user_query->get_results() as $user) {
        // Get complete billing details
        $clients[] = [
            'id' => $user->ID,
            'first_name' => get_user_meta($user->ID, 'billing_first_name', true) ?: $user->first_name,
            'last_name' => get_user_meta($user->ID, 'billing_last_name', true) ?: $user->last_name,
            'email' => $user->user_email,
            'phone' => get_user_meta($user->ID, 'billing_phone', true),
            'street_address' => get_user_meta($user->ID, 'billing_address_1', true),
            'address_2' => get_user_meta($user->ID, 'billing_address_2', true),
            'city' => get_user_meta($user->ID, 'billing_city', true),
            'state' => get_user_meta($user->ID, 'billing_state', true),
            'zip' => get_user_meta($user->ID, 'billing_postcode', true),
            'country' => get_user_meta($user->ID, 'billing_country', true),
            'display_name' => $user->display_name
        ];
    }
    
    wp_send_json_success(['clients' => $clients]);
}
// Fix for email lookup with correct field IDs
add_action('wp_footer', 'el_fix_email_lookup_fields', 9999);
function el_fix_email_lookup_fields() {
    if (!is_page()) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        console.log('🔧 Fixing email lookup field IDs...');
        
        // Attach to the correct email field
        $('#input_1_2').on('input keyup', function() {
            var searchTerm = $(this).val().trim();
            console.log('📧 Email search:', searchTerm);
            
            if (searchTerm.length < 2) {
                $('.el-client-suggestions').remove();
                return;
            }
            
// Debounce
            clearTimeout(window.elSearchTimeout);
            window.elSearchTimeout = setTimeout(function() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'search_existing_client',
                        search_term: searchTerm,
                        search_type: 'email',
                        nonce: '<?php echo wp_create_nonce('el_client_search_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ Search results:', response);
                        
                        $('.el-client-suggestions').remove();
                        
                        if (response.success && response.data.length > 0) {
                            var $suggestions = $('<div class="el-client-suggestions"></div>');
                            
                            response.data.forEach(function(client) {
                                var $item = $('<div class="el-suggestion-item"></div>')
                                    .html('<strong>' + client.display + '</strong><br><small>' + client.email + '</small>')
                                    .data('client', client)
                                    .on('click', function() {
                                        var c = $(this).data('client');
                                        
                                        // Name and contact
                                        $('#input_1_1_3').val(c.first_name || '');
                                        $('#input_1_1_6').val(c.last_name || '');
                                        $('#input_1_2').val(c.email || '');
                                        $('#input_1_5').val(c.phone || '');
                                        
                                        // Complete address (Field ID 6)
                                        $('#input_1_6_1').val(c.street_address || c.billing_address_1 || '');
                                        $('#input_1_6_2').val(c.address_2 || c.billing_address_2 || '');
                                        $('#input_1_6_3').val(c.city || c.billing_city || '');
                                        $('#input_1_6_4').val(c.state || c.billing_state || '');
                                        $('#input_1_6_5').val(c.zip || c.billing_postcode || '');
                                        $('#input_1_6_6').val(c.country || c.billing_country || '');
                                        
                                        $('.el-client-suggestions').remove();
                                        
                                        console.log('✅ Loaded billing details:', c);
                                    });
                                $suggestions.append($item);
                            });
                            
                            $('#input_1_2').after($suggestions);
                        }
                    }
                });
            }, 300);
        });
        
        console.log('✅ Email lookup attached to correct fields');
    });
    </script>
    <style>
    .el-client-suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 9999;
        width: 100%;
        margin-top: 4px;
    }
    .el-suggestion-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .el-suggestion-item:hover {
        background: #f5f5f5;
    }
    .el-suggestion-item:last-child {
        border-bottom: none;
    }
    </style>
    <?php
}

/**
 * =================================================================
 * ENGAGEMENT LETTERS CUSTOM POST TYPE
 * =================================================================
 */

/**
 * Register Engagement Letters custom post type
 */
add_action('init', 'el_register_engagement_letters_cpt');

function el_register_engagement_letters_cpt() {
    $labels = array(
        'name'                  => _x('Engagement Letters', 'Post type general name', 'bricks-child'),
        'singular_name'         => _x('Engagement Letter', 'Post type singular name', 'bricks-child'),
        'menu_name'             => _x('Engagement Letters', 'Admin Menu text', 'bricks-child'),
        'name_admin_bar'        => _x('Engagement Letter', 'Add New on Toolbar', 'bricks-child'),
        'add_new'               => __('Add New', 'bricks-child'),
        'add_new_item'          => __('Add New Engagement Letter', 'bricks-child'),
        'new_item'              => __('New Engagement Letter', 'bricks-child'),
        'edit_item'             => __('Edit Engagement Letter', 'bricks-child'),
        'view_item'             => __('View Engagement Letter', 'bricks-child'),
        'all_items'             => __('All Engagement Letters', 'bricks-child'),
        'search_items'          => __('Search Engagement Letters', 'bricks-child'),
        'parent_item_colon'     => __('Parent Engagement Letters:', 'bricks-child'),
        'not_found'             => __('No engagement letters found.', 'bricks-child'),
        'not_found_in_trash'    => __('No engagement letters found in Trash.', 'bricks-child'),
        'featured_image'        => _x('Featured Image', 'Overrides the "Featured Image" phrase', 'bricks-child'),
        'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'bricks-child'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'bricks-child'),
        'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'bricks-child'),
        'archives'              => _x('Engagement Letter archives', 'The post type archive label', 'bricks-child'),
        'insert_into_item'      => _x('Insert into engagement letter', 'Overrides the "Insert into post" phrase', 'bricks-child'),
        'uploaded_to_this_item' => _x('Uploaded to this engagement letter', 'Overrides the "Uploaded to this post" phrase', 'bricks-child'),
        'filter_items_list'     => _x('Filter engagement letters list', 'Screen reader text for the filter links', 'bricks-child'),
        'items_list_navigation' => _x('Engagement letters list navigation', 'Screen reader text for the pagination', 'bricks-child'),
        'items_list'            => _x('Engagement letters list', 'Screen reader text for the items list', 'bricks-child'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => array('title'),
        'show_in_rest'       => false,
    );

    register_post_type('engagement_letter', $args);
}

/**
 * Add custom columns to Engagement Letters admin list
 */
add_filter('manage_engagement_letter_posts_columns', 'el_engagement_letter_columns');

function el_engagement_letter_columns($columns) {
    $new_columns = array(
        'cb'             => $columns['cb'],
        'title'          => __('Title', 'bricks-child'),
        'el_client'      => __('Client', 'bricks-child'),
        'el_lawyer'      => __('Lawyer', 'bricks-child'),
        'el_template'    => __('Template', 'bricks-child'),
        'el_status'      => __('Status', 'bricks-child'),
        'el_practice'    => __('Practice Area', 'bricks-child'),
        'el_generated'   => __('Generated Date', 'bricks-child'),
        'date'           => $columns['date'],
    );
    
    return $new_columns;
}

/**
 * Populate custom columns
 */
add_action('manage_engagement_letter_posts_custom_column', 'el_engagement_letter_column_content', 10, 2);

function el_engagement_letter_column_content($column, $post_id) {
    switch ($column) {
        case 'el_client':
            $client_id = get_post_meta($post_id, '_el_client_id', true);
            if ($client_id) {
                $client = get_userdata($client_id);
                if ($client) {
                    echo '<a href="' . admin_url('user-edit.php?user_id=' . $client_id) . '">';
                    echo esc_html($client->display_name);
                    echo '</a>';
                } else {
                    echo '—';
                }
            } else {
                echo '<em>Blank Template</em>';
            }
            break;
            
        case 'el_lawyer':
            $lawyer_id = get_post_meta($post_id, '_el_lawyer_id', true);
            if ($lawyer_id) {
                $lawyer = get_userdata($lawyer_id);
                if ($lawyer) {
                    echo esc_html($lawyer->display_name);
                }
            } else {
                echo '—';
            }
            break;
            
        case 'el_template':
            $template_id = get_post_meta($post_id, '_el_template_id', true);
            if ($template_id) {
                $template = get_post($template_id);
                if ($template) {
                    echo '<a href="' . admin_url('post.php?post=' . $template_id . '&action=edit') . '">';
                    echo esc_html($template->post_title);
                    echo '</a>';
                }
            } else {
                echo '—';
            }
            break;
            
        case 'el_status':
            $status = get_post_meta($post_id, '_el_status', true);
            $status_labels = array(
                'draft'       => '<span style="color: #999;">Draft</span>',
                'generated'   => '<span style="color: #2271b1;">Generated</span>',
                'sent'        => '<span style="color: #d63638;">Sent</span>',
                'signed'      => '<span style="color: #00a32a;">Signed</span>',
                'paid'        => '<span style="color: #007017;"><strong>Paid</strong></span>',
            );
            echo $status_labels[$status] ?? '<span style="color: #dba617;">Unknown</span>';
            break;
            
        case 'el_practice':
            $practice = get_post_meta($post_id, '_el_practice_area', true);
            echo $practice ? esc_html($practice) : '—';
            break;
            
        case 'el_generated':
            $generated = get_post_meta($post_id, '_el_generated_date', true);
            if ($generated) {
                echo date('d/m/Y H:i', strtotime($generated));
            } else {
                echo '—';
            }
            break;
    }
}

/**
 * Make columns sortable
 */
add_filter('manage_edit-engagement_letter_sortable_columns', 'el_engagement_letter_sortable_columns');

function el_engagement_letter_sortable_columns($columns) {
    $columns['el_client'] = 'el_client';
    $columns['el_lawyer'] = 'el_lawyer';
    $columns['el_status'] = 'el_status';
    $columns['el_generated'] = 'el_generated';
    return $columns;
}

/**
 * Handle sorting
 */
add_action('pre_get_posts', 'el_engagement_letter_orderby');

function el_engagement_letter_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ('engagement_letter' !== $query->get('post_type')) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('el_client' === $orderby) {
        $query->set('meta_key', '_el_client_id');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ('el_lawyer' === $orderby) {
        $query->set('meta_key', '_el_lawyer_id');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ('el_status' === $orderby) {
        $query->set('meta_key', '_el_status');
        $query->set('orderby', 'meta_value');
    }
    
    if ('el_generated' === $orderby) {
        $query->set('meta_key', '_el_generated_date');
        $query->set('orderby', 'meta_value');
    }
}

/**
 * Helper function to create new engagement letter
 */
function el_create_engagement_letter($args = array()) {
    $defaults = array(
        'title'          => 'Engagement Letter',
        'client_id'      => 0,
        'lawyer_id'      => get_current_user_id(),
        'template_id'    => 0,
        'status'         => 'draft',
        'practice_area'  => '',
        'form_data'      => array(),
        'cart_contents'  => array(),
        'order_id'       => 0,
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Generate unique reference code
    $reference = strtolower(wp_generate_password(12, false));
    
    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'   => $args['title'],
        'post_type'    => 'engagement_letter',
        'post_status'  => 'publish',
        'post_author'  => $args['lawyer_id'],
    ));
    
    if (is_wp_error($post_id)) {
        return false;
    }
    
    // Save meta data
    update_post_meta($post_id, '_el_reference', $reference); // Legacy with underscore
    update_post_meta($post_id, 'el_reference', $reference); // New without underscore - for signature system
    update_post_meta($post_id, '_el_client_id', intval($args['client_id']));
    update_post_meta($post_id, '_el_lawyer_id', intval($args['lawyer_id']));
    update_post_meta($post_id, '_el_template_id', intval($args['template_id']));
    update_post_meta($post_id, '_el_status', sanitize_text_field($args['status']));
    update_post_meta($post_id, 'el_status', sanitize_text_field($args['status'])); // New without underscore - for signature system
    update_post_meta($post_id, '_el_practice_area', sanitize_text_field($args['practice_area']));
    update_post_meta($post_id, '_el_form_data', $args['form_data']);
    update_post_meta($post_id, '_el_cart_contents', $args['cart_contents']);
    update_post_meta($post_id, '_el_order_id', intval($args['order_id']));
    update_post_meta($post_id, '_el_created_date', current_time('mysql'));
    
    // Create unique directory for this engagement letter
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'] . '/xengagement-letters/' . $reference;
    
    if (!file_exists($base_dir)) {
        wp_mkdir_p($base_dir);
        
        // Add .htaccess for security
        $htaccess = $base_dir . '/.htaccess';
        file_put_contents($htaccess, "Options -Indexes\nDeny from all");
        
        // Add index.php for security
        $index = $base_dir . '/index.php';
        file_put_contents($index, "<?php\n// Silence is golden.");
    }
    
    update_post_meta($post_id, '_el_directory', $reference);
    
    return $post_id;
}

/**
 * Helper function to update engagement letter
 */
function el_update_engagement_letter($post_id, $args = array()) {
    if (get_post_type($post_id) !== 'engagement_letter') {
        return false;
    }
    
    $allowed_fields = array(
        'template_id'    => '_el_template_id',
        'status'         => '_el_status',
        'practice_area'  => '_el_practice_area',
        'form_data'      => '_el_form_data',
        'cart_contents'  => '_el_cart_contents',
        'edited_html'    => '_el_edited_html',
        'order_id'       => '_el_order_id',
    );
    
    foreach ($args as $field => $value) {
        if (isset($allowed_fields[$field])) {
            update_post_meta($post_id, $allowed_fields[$field], $value);
        }
    }
    
    // Update modified date
    update_post_meta($post_id, '_el_modified_date', current_time('mysql'));
    
    return true;
}

/**
 * Helper function to get engagement letter data
 */
function el_get_engagement_letter($post_id) {
    if (get_post_type($post_id) !== 'engagement_letter') {
        return false;
    }
    
    $post = get_post($post_id);
    
    return array(
        'ID'             => $post->ID,
        'title'          => $post->post_title,
        'reference'      => get_post_meta($post_id, '_el_reference', true),
        'client_id'      => get_post_meta($post_id, '_el_client_id', true),
        'lawyer_id'      => get_post_meta($post_id, '_el_lawyer_id', true),
        'template_id'    => get_post_meta($post_id, '_el_template_id', true),
        'status'         => get_post_meta($post_id, '_el_status', true),
        'practice_area'  => get_post_meta($post_id, '_el_practice_area', true),
        'form_data'      => get_post_meta($post_id, '_el_form_data', true),
        'cart_contents'  => get_post_meta($post_id, '_el_cart_contents', true),
        'edited_html'    => get_post_meta($post_id, '_el_edited_html', true),
        'pdf_path'       => get_post_meta($post_id, '_el_pdf_path', true),
        'order_id'       => get_post_meta($post_id, '_el_order_id', true),
        'directory'      => get_post_meta($post_id, '_el_directory', true),
        'created_date'   => get_post_meta($post_id, '_el_created_date', true),
        'modified_date'  => get_post_meta($post_id, '_el_modified_date', true),
        'generated_date' => get_post_meta($post_id, '_el_generated_date', true),
        'paid_date'      => get_post_meta($post_id, '_el_paid_date', true),
    );
}

/**
 * =================================================================
 * LAWYER USER FIELDS & SHORTCODE SYSTEM
 * =================================================================
 */

/**
 * Add custom fields to lawyer user profiles
 */
add_action('show_user_profile', 'el_add_lawyer_profile_fields');
add_action('edit_user_profile', 'el_add_lawyer_profile_fields');

function el_add_lawyer_profile_fields($user) {
    // Only show for lawyers and admins
    if (!in_array('lawyer', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
        return;
    }
    
    $lawyer_rate = get_user_meta($user->ID, 'lawyer_rate', true);
    $lawyer_role = get_user_meta($user->ID, 'lawyer_role', true);
    ?>
    
    <h3><?php _e('Lawyer Information', 'bricks-child'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th>
                <label for="lawyer_role"><?php _e('Legal Role/Title', 'bricks-child'); ?></label>
            </th>
            <td>
                <input type="text" 
                       name="lawyer_role" 
                       id="lawyer_role" 
                       value="<?php echo esc_attr($lawyer_role); ?>" 
                       class="regular-text" 
                       placeholder="e.g., Senior Partner, Associate, Counsel" />
                <p class="description"><?php _e('The lawyer\'s role or title (e.g., Senior Partner, Associate)', 'bricks-child'); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="lawyer_rate"><?php _e('Hourly Rate (EUR)', 'bricks-child'); ?></label>
            </th>
            <td>
                <input type="number" 
                       name="lawyer_rate" 
                       id="lawyer_rate" 
                       value="<?php echo esc_attr($lawyer_rate); ?>" 
                       class="regular-text" 
                       step="0.01" 
                       min="0"
                       placeholder="150.00" />
                <p class="description"><?php _e('Hourly rate in EUR (e.g., 150.00)', 'bricks-child'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save lawyer profile fields
 */
add_action('personal_options_update', 'el_save_lawyer_profile_fields');
add_action('edit_user_profile_update', 'el_save_lawyer_profile_fields');

function el_save_lawyer_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    // Save lawyer role
    if (isset($_POST['lawyer_role'])) {
        update_user_meta($user_id, 'lawyer_role', sanitize_text_field($_POST['lawyer_role']));
    }
    
    // Save lawyer rate
    if (isset($_POST['lawyer_rate'])) {
        $rate = floatval($_POST['lawyer_rate']);
        update_user_meta($user_id, 'lawyer_rate', $rate);
    }
}

/**
 * Flexible lawyer info shortcode
 * 
 * Usage examples:
 * [lawyer_info user_id="123" fields="role,firstname,lastname,rate"]
 * [lawyer_info user_id="123" fields="role,fullname,rate"]
 * [lawyer_info user_id="123" fields="firstname,lastname,rate" fixedlength="100"]
 * [lawyer_info user_id="current" fields="fullname,rate"]
 * [lawyer_info user_id="123" fields="role,fullname,rate" role_colon="true"]
 * 
 * Available fields:
 * - role: Lawyer role/title
 * - firstname: First name
 * - lastname: Last name
 * - fullname: Full name (first + last)
 * - rate: Hourly rate formatted as €XXX.XX/h
 * - rate_raw: Just the number without formatting
 * 
 * Parameters:
 * - user_id: User ID or "current" for logged-in user
 * - fields: Comma-separated list of fields to display
 * - fixedlength: Optional total character length with dot padding BEFORE the rate
 * - separator: Character(s) between fields (default: space)
 * - rate_format: Custom rate format (default: "€{rate}/h")
 * - padding_char: Character for padding (default: ".")
 * - role_colon: Add colon and space after role (default: false)
 */
add_shortcode('lawyer_info', 'el_lawyer_info_shortcode');

function el_lawyer_info_shortcode($atts) {
    $atts = shortcode_atts([
        'user_id' => '',
        'fields' => 'fullname,rate',
        'fixedlength' => '',
        'separator' => ' ',
        'rate_format' => '€{rate}/h',
        'padding_char' => '.',
        'role_colon' => 'false',
    ], $atts, 'lawyer_info');
    
    // Get user ID
    if ($atts['user_id'] === 'current') {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return '<span class="lawyer-info-error">No user logged in</span>';
        }
    } else {
        $user_id = intval($atts['user_id']);
    }
    
    if (!$user_id) {
        return '<span class="lawyer-info-error">Invalid user ID</span>';
    }
    
    // Get user data
    $user = get_userdata($user_id);
    if (!$user) {
        return '<span class="lawyer-info-error">User not found</span>';
    }
    
    // Get lawyer-specific meta
    $lawyer_role = get_user_meta($user_id, 'lawyer_role', true);
    $lawyer_rate = get_user_meta($user_id, 'lawyer_rate', true);
    
    // Parse fields to display
    $fields_array = array_map('trim', explode(',', $atts['fields']));
    $output_parts = [];
    $rate_output = '';
    $has_rate = false;
    $add_role_colon = filter_var($atts['role_colon'], FILTER_VALIDATE_BOOLEAN);
    
    foreach ($fields_array as $field) {
        switch (strtolower($field)) {
            case 'role':
                if ($lawyer_role) {
                    $role_output = $lawyer_role;
                    if ($add_role_colon) {
                        $role_output .= ':';
                    }
                    $output_parts[] = $role_output;
                }
                break;
                
            case 'firstname':
                if ($user->first_name) {
                    $output_parts[] = $user->first_name;
                }
                break;
                
            case 'lastname':
                if ($user->last_name) {
                    $output_parts[] = $user->last_name;
                }
                break;
                
            case 'fullname':
                $full_name = trim($user->first_name . ' ' . $user->last_name);
                if ($full_name) {
                    $output_parts[] = $full_name;
                } else {
                    $output_parts[] = $user->display_name;
                }
                break;
                
            case 'rate':
                if ($lawyer_rate) {
                    $rate_output = str_replace('{rate}', number_format($lawyer_rate, 2), $atts['rate_format']);
                    $has_rate = true;
                }
                break;
                
            case 'rate_raw':
                if ($lawyer_rate) {
                    $rate_output = number_format($lawyer_rate, 2);
                    $has_rate = true;
                }
                break;
        }
    }
    
    // Build output with smart padding
    if ($atts['fixedlength'] && $has_rate) {
        // Fixed length with rate: name...dots...rate format
        $name_part = implode($atts['separator'], $output_parts);
        $total_length = intval($atts['fixedlength']);
        $name_length = mb_strlen($name_part);
        $rate_length = mb_strlen($rate_output);
        
        // Add space before dots and after dots
        $dots_space = $total_length - $name_length - $rate_length - 2; // -2 for spaces
        
        if ($dots_space < 1) {
            // Not enough room, truncate name
            $available_for_name = $total_length - $rate_length - 2 - 3; // -3 for minimum dots
            $name_part = mb_substr($name_part, 0, $available_for_name);
            $dots_space = 3;
        }
        
        $output = $name_part . ' ' . str_repeat($atts['padding_char'], $dots_space) . ' ' . $rate_output;
        
    } elseif ($atts['fixedlength']) {
        // Fixed length without rate: pad at end
        $output = implode($atts['separator'], $output_parts);
        $output = el_pad_lawyer_info($output, intval($atts['fixedlength']), $atts['padding_char']);
        
    } else {
        // No fixed length: just join all parts
        if ($has_rate) {
            $output_parts[] = $rate_output;
        }
        $output = implode($atts['separator'], $output_parts);
    }
    
    return '<span class="lawyer-info">' . esc_html($output) . '</span>';
}

/**
 * Helper function to pad text to fixed length with dots
 */
function el_pad_lawyer_info($text, $length, $padding_char = '.') {
    $text_length = mb_strlen($text);
    
    if ($text_length >= $length) {
        // Text is already longer, truncate it
        return mb_substr($text, 0, $length);
    }
    
    // Calculate padding needed
    $padding_needed = $length - $text_length;
    
    // Add padding
    return $text . str_repeat($padding_char, $padding_needed);
}

/**
 * Get all lawyers for dropdowns/selection
 */
function el_get_all_lawyers($args = []) {
    $defaults = [
        'role__in' => ['lawyer'],
        'orderby' => 'display_name',
        'order' => 'ASC',
        'fields' => 'all'
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $users = get_users($args);
    
    $lawyers = [];
    foreach ($users as $user) {
        $lawyers[] = [
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => get_user_meta($user->ID, 'lawyer_role', true),
            'rate' => get_user_meta($user->ID, 'lawyer_rate', true)
        ];
    }
    
    return $lawyers;
}

/**
 * Admin column for lawyers list - show rate
 */
add_filter('manage_users_columns', 'el_add_lawyer_columns');
function el_add_lawyer_columns($columns) {
    $columns['lawyer_role'] = __('Legal Role', 'bricks-child');
    $columns['lawyer_rate'] = __('Rate (EUR/h)', 'bricks-child');
    return $columns;
}

add_filter('manage_users_custom_column', 'el_show_lawyer_columns', 10, 3);
function el_show_lawyer_columns($value, $column_name, $user_id) {
    $user = get_userdata($user_id);
    
    // Only show for lawyers
    if (!in_array('lawyer', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
        return $value;
    }
    
    if ($column_name === 'lawyer_role') {
        $role = get_user_meta($user_id, 'lawyer_role', true);
        return $role ? esc_html($role) : '—';
    }
    
    if ($column_name === 'lawyer_rate') {
        $rate = get_user_meta($user_id, 'lawyer_rate', true);
        return $rate ? '€' . number_format($rate, 2) . '/h' : '—';
    }
    
    return $value;
}

/**
 * Make lawyer columns sortable
 */
add_filter('manage_users_sortable_columns', 'el_make_lawyer_columns_sortable');
function el_make_lawyer_columns_sortable($columns) {
    $columns['lawyer_rate'] = 'lawyer_rate';
    $columns['lawyer_role'] = 'lawyer_role';
    return $columns;
}

add_action('pre_get_users', 'el_sort_lawyer_columns');
function el_sort_lawyer_columns($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ($orderby === 'lawyer_rate') {
        $query->set('meta_key', 'lawyer_rate');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ($orderby === 'lawyer_role') {
        $query->set('meta_key', 'lawyer_role');
        $query->set('orderby', 'meta_value');
    }
}

/**
 * Enable shortcodes in ACF fields
 */
add_filter('acf/format_value/type=text', 'do_shortcode');
add_filter('acf/format_value/type=textarea', 'do_shortcode');
add_filter('acf/format_value/type=wysiwyg', 'do_shortcode');

/**
 * =================================================================
 * Remove WooCommerce "Added to cart" Messages & View Cart Links
 * =================================================================
 */

// Remove "View cart" link from added to cart messages
add_filter('wc_add_to_cart_message_html', '__return_empty_string');

// Remove all add-to-cart success notices for our engagement letter products
add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id) {
    // Check if this is an el-templates product
    if (has_term('el-templates', 'product_cat', $product_id)) {
        // Clear all WooCommerce notices before adding to cart
        wc_clear_notices();
    }
    return $passed;
}, 10, 2);

// Prevent WooCommerce from adding success notices after adding to cart
add_action('woocommerce_add_to_cart', function($cart_item_key, $product_id) {
    // Check if this is an el-templates product
    if (has_term('el-templates', 'product_cat', $product_id)) {
        // Clear the success notices that WooCommerce adds
        wc_clear_notices();
    }
}, 10, 2);

/**
 * =================================================================
 * PART 3: CART STATE MANAGEMENT - SAVE & RESTORE
 * Handles complete cart state for complex products (bundles, composite, tiered, etc.)
 * =================================================================
 */

/**
 * Save complete cart state to WooCommerce session
 * Captures ALL product types and their configurations
 */
function el_save_cart_state() {
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $cart_contents = WC()->cart->get_cart();
    
    if (empty($cart_contents)) {
        WC()->session->set('el_saved_cart_state', null);
        return ['success' => true, 'message' => 'Empty cart saved', 'items' => 0];
    }
    
    $saved_items = [];
    
    foreach ($cart_contents as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'] ?? 0;
        
        // Base item data
        $item_data = [
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $cart_item['quantity'],
            'product_type' => $product->get_type(),
        ];
        
        // Variation attributes (for variable products)
        if (!empty($cart_item['variation'])) {
            $item_data['variation'] = $cart_item['variation'];
        }
        
        // Bundle products (WooCommerce Product Bundles)
        if (isset($cart_item['bundled_items']) && !empty($cart_item['bundled_items'])) {
            $item_data['is_bundle'] = true;
            $item_data['bundled_items'] = [];
            
            foreach ($cart_item['bundled_items'] as $bundled_item_key) {
                if (isset($cart_contents[$bundled_item_key])) {
                    $bundled_item = $cart_contents[$bundled_item_key];
                    $item_data['bundled_items'][] = [
                        'bundled_item_id' => $bundled_item['bundled_item_id'] ?? null,
                        'product_id' => $bundled_item['product_id'],
                        'variation_id' => $bundled_item['variation_id'] ?? 0,
                        'quantity' => $bundled_item['quantity'],
                        'variation' => $bundled_item['variation'] ?? [],
                        'stamp' => $bundled_item['stamp'] ?? [],
                    ];
                }
            }
        }
        
        // Bundle stamp (configuration)
        if (isset($cart_item['stamp'])) {
            $item_data['stamp'] = $cart_item['stamp'];
        }
        
        // Composite products (WooCommerce Composite Products)
        if (isset($cart_item['composite_data']) && !empty($cart_item['composite_data'])) {
            $item_data['is_composite'] = true;
            $item_data['composite_data'] = $cart_item['composite_data'];
        }
        
        if (isset($cart_item['composite_children']) && !empty($cart_item['composite_children'])) {
            $item_data['composite_children'] = [];
            
            foreach ($cart_item['composite_children'] as $child_key) {
                if (isset($cart_contents[$child_key])) {
                    $child_item = $cart_contents[$child_key];
                    $item_data['composite_children'][] = [
                        'component_id' => $child_item['component_id'] ?? null,
                        'composite_item_id' => $child_item['composite_item_id'] ?? null,
                        'product_id' => $child_item['product_id'],
                        'variation_id' => $child_item['variation_id'] ?? 0,
                        'quantity' => $child_item['quantity'],
                        'variation' => $child_item['variation'] ?? [],
                    ];
                }
            }
        }
        
        // Product Add-Ons
        if (isset($cart_item['addons']) && !empty($cart_item['addons'])) {
            $item_data['addons'] = $cart_item['addons'];
        }
        
        // Tiered pricing data
        if (isset($cart_item['tm_epo_options'])) {
            $item_data['tm_epo_options'] = $cart_item['tm_epo_options'];
        }
        
        if (isset($cart_item['tmcartepo'])) {
            $item_data['tmcartepo'] = $cart_item['tmcartepo'];
        }
        
        // Custom pricing (discounts, fees, etc.)
        if (isset($cart_item['line_subtotal'])) {
            $item_data['line_subtotal'] = $cart_item['line_subtotal'];
        }
        
        if (isset($cart_item['line_total'])) {
            $item_data['line_total'] = $cart_item['line_total'];
        }
        
        // Any other custom cart item data
        $item_data['custom_data'] = [];
        foreach ($cart_item as $key => $value) {
            // Skip standard WooCommerce keys and already captured data
            if (!in_array($key, [
                'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data',
                'line_subtotal', 'line_tax', 'line_subtotal_tax', 'line_total', 'line_tax_data',
                'bundled_items', 'stamp', 'composite_data', 'composite_children',
                'addons', 'tm_epo_options', 'tmcartepo'
            ])) {
                $item_data['custom_data'][$key] = $value;
            }
        }
        
        $saved_items[] = $item_data;
    }
    
    // Save to WooCommerce session
    WC()->session->set('el_saved_cart_state', $saved_items);
    
    return [
        'success' => true,
        'message' => 'Cart state saved successfully',
        'items' => count($saved_items),
        'data' => $saved_items
    ];
}

/**
 * Restore cart from saved state
 * Handles all product types and complex configurations
 */
function el_restore_cart_state() {
    if (!WC()->cart) {
        WC()->initialize_cart();
    }
    
    $saved_state = WC()->session->get('el_saved_cart_state');
    
    if (empty($saved_state)) {
        return [
            'success' => false,
            'message' => 'No saved cart state found',
            'items' => 0
        ];
    }
    
    // Clear current cart
    WC()->cart->empty_cart();
    
    // Separate parent items from children
    $parent_items = [];
    $child_items = [];
    
    foreach ($saved_state as $item) {
        if (isset($item['is_bundle']) || isset($item['is_composite'])) {
            $parent_items[] = $item;
        } else {
            $child_items[] = $item;
        }
    }
    
    $restored_count = 0;
    
    // Restore parent items first (bundles and composites)
    foreach ($parent_items as $item) {
        if (el_restore_single_cart_item($item)) {
            $restored_count++;
        }
    }
    
    // Then restore standalone items
    foreach ($child_items as $item) {
        if (el_restore_single_cart_item($item)) {
            $restored_count++;
        }
    }
    
    return [
        'success' => true,
        'message' => 'Cart restored successfully',
        'items' => $restored_count,
        'total_saved' => count($saved_state)
    ];
}

/**
 * Helper function to restore a single cart item
 */
function el_restore_single_cart_item($item) {
    $product_id = $item['product_id'];
    $variation_id = $item['variation_id'] ?? 0;
    $quantity = $item['quantity'];
    
    // Get the product
    $product = wc_get_product($variation_id ? $variation_id : $product_id);
    
    if (!$product || !$product->is_purchasable()) {
        return false;
    }
    
    // Prepare cart item data
    $cart_item_data = [];
    
    // Variation attributes
    if (!empty($item['variation'])) {
        $cart_item_data['variation'] = $item['variation'];
    }
    
    // Bundle data
    if (isset($item['is_bundle']) && $item['is_bundle']) {
        if (isset($item['stamp'])) {
            $cart_item_data['stamp'] = $item['stamp'];
        }
        
        // Bundle plugin will handle bundled items automatically
        // We just need to pass the configuration
    }
    
    // Composite data
    if (isset($item['is_composite']) && $item['is_composite']) {
        if (isset($item['composite_data'])) {
            $cart_item_data['composite_data'] = $item['composite_data'];
        }
        
        // Composite plugin will handle component items automatically
    }
    
    // Product Add-Ons
    if (isset($item['addons'])) {
        $cart_item_data['addons'] = $item['addons'];
    }
    
    // Tiered pricing
    if (isset($item['tm_epo_options'])) {
        $cart_item_data['tm_epo_options'] = $item['tm_epo_options'];
    }
    
    if (isset($item['tmcartepo'])) {
        $cart_item_data['tmcartepo'] = $item['tmcartepo'];
    }
    
    // Custom data
    if (!empty($item['custom_data'])) {
        foreach ($item['custom_data'] as $key => $value) {
            $cart_item_data[$key] = $value;
        }
    }
    
    // Add to cart
    try {
        if ($variation_id) {
            $cart_item_key = WC()->cart->add_to_cart(
                $product_id,
                $quantity,
                $variation_id,
                $item['variation'] ?? [],
                $cart_item_data
            );
        } else {
            $cart_item_key = WC()->cart->add_to_cart(
                $product_id,
                $quantity,
                0,
                [],
                $cart_item_data
            );
        }
        
        return !empty($cart_item_key);
        
    } catch (Exception $e) {
        error_log('Cart restoration error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Auto-save cart state when cart is updated
 */
add_action('woocommerce_cart_updated', 'el_auto_save_cart_state');
add_action('woocommerce_add_to_cart', 'el_auto_save_cart_state');
add_action('woocommerce_cart_item_removed', 'el_auto_save_cart_state');
add_action('woocommerce_cart_item_restored', 'el_auto_save_cart_state');

function el_auto_save_cart_state() {
    // Only auto-save if we're in an engagement letter session
    if (!session_id()) {
        // Session already started via init hook
    }
    
    if (isset($_SESSION['el_current_client_id']) || isset($_SESSION['el_engagement_letter_id'])) {
        // Save to WooCommerce session
        el_save_cart_state();
        
        // Also save to engagement letter CPT
        $engagement_letter_id = isset($_SESSION['el_engagement_letter_id']) ? intval($_SESSION['el_engagement_letter_id']) : 0;
        
        if ($engagement_letter_id && get_post_type($engagement_letter_id) === 'engagement_letter') {
            if (!WC()->cart) {
                WC()->initialize_cart();
            }
            
            // Build cart contents array
            $cart_contents = array();
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $cart_contents[] = array(
                    'product_id' => $cart_item['product_id'],
                    'variation_id' => $cart_item['variation_id'] ?? 0,
                    'quantity' => $cart_item['quantity'],
                    'product_name' => $cart_item['data']->get_name(),
                );
            }
            
            // Save to engagement letter
            update_post_meta($engagement_letter_id, '_el_cart_contents', $cart_contents);
            update_post_meta($engagement_letter_id, '_el_modified_date', current_time('mysql'));
        }
    }
}

/**
 * AJAX handler to manually save cart state
 */
add_action('wp_ajax_el_save_cart_state', 'el_ajax_save_cart_state');
add_action('wp_ajax_nopriv_el_save_cart_state', 'el_ajax_save_cart_state');

function el_ajax_save_cart_state() {
    check_ajax_referer('el_cart_nonce', 'nonce');
    
    $result = el_save_cart_state();
    
    wp_send_json($result);
}

/**
 * AJAX handler to manually restore cart state
 */
add_action('wp_ajax_el_restore_cart_state', 'el_ajax_restore_cart_state');
add_action('wp_ajax_nopriv_el_restore_cart_state', 'el_ajax_restore_cart_state');

function el_ajax_restore_cart_state() {
    check_ajax_referer('el_cart_nonce', 'nonce');
    
    $result = el_restore_cart_state();
    
    wp_send_json($result);
}

/**
 * Clear saved cart state when engagement letter is completed
 */
add_action('el_engagement_letter_completed', 'el_clear_saved_cart_state');

function el_clear_saved_cart_state() {
    if (WC()->session) {
        WC()->session->set('el_saved_cart_state', null);
    }
}




/**
 * Generate PDF from HTML and attach to engagement letter post
 */
function el_generate_and_attach_pdf($html, $el_id, $reference) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Create temporary HTML file
    $upload_dir = wp_upload_dir();
    $temp_html_path = $upload_dir['path'] . '/temp_' . $reference . '.html';
    file_put_contents($temp_html_path, $html);
    
    // Use wkhtmltopdf or TCPDF to generate PDF
    // For now, we'll create a basic implementation using DomPDF if available
    $pdf_path = $upload_dir['path'] . '/engagement-letter-' . $reference . '.pdf';
    
    // Try to use wkhtmltopdf (most reliable for complex HTML)
    $wkhtmltopdf_path = '/usr/bin/wkhtmltopdf'; // Adjust path as needed
    if (file_exists($wkhtmltopdf_path)) {
        $command = sprintf(
            '%s --page-size A4 --margin-top 20mm --margin-bottom 25mm --margin-left 15mm --margin-right 15mm %s %s 2>&1',
            escapeshellarg($wkhtmltopdf_path),
            escapeshellarg($temp_html_path),
            escapeshellarg($pdf_path)
        );
        exec($command, $output, $return_var);
        
        if ($return_var !== 0 || !file_exists($pdf_path)) {
            // Fallback: just save HTML as "PDF" (will need proper PDF library)
            copy($temp_html_path, $pdf_path);
        }
    } else {
        // Fallback: save as HTML with PDF extension (client can print to PDF)
        copy($temp_html_path, $pdf_path);
    }
    
    // Clean up temp HTML
    @unlink($temp_html_path);
    
    if (!file_exists($pdf_path)) {
        return null;
    }
    
    // Attach PDF to engagement letter post
    $attachment = [
        'guid'           => $upload_dir['url'] . '/' . basename($pdf_path),
        'post_mime_type' => 'application/pdf',
        'post_title'     => 'Engagement Letter - ' . $reference,
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];
    
    $attachment_id = wp_insert_attachment($attachment, $pdf_path, $el_id);
    
    if (!is_wp_error($attachment_id)) {
        $attach_data = wp_generate_attachment_metadata($attachment_id, $pdf_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        // Store attachment ID in post meta
        update_post_meta($el_id, '_el_pdf_attachment_id', $attachment_id);
        
        return $attachment_id;
    }
    
    return null;
}

/**
 * ================================================================
 * SHORTCODE: [el_print_editor]
 * ================================================================
 */
add_shortcode('el_print_editor', 'el_render_print_editor_shortcode');

function el_render_print_editor_shortcode($atts) {
    $can_edit = current_user_can('edit_posts');
    
    ob_start();
    ?>
    
    <div id="el-print-editor-wrapper" class="el-print-editor-wrapper">
        
        <!-- Page Info Bar -->
        <div class="el-page-info-bar" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <div class="el-page-stats" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <span style="font-size: 14px;">📄 Pages: <strong id="el-total-pages">0</strong></span>
                <span style="font-size: 14px;">📋 Mode: <strong id="el-print-mode">Digital</strong></span>
                <span id="el-signature-indicator" style="display: none; font-size: 14px;">✍️ Page signatures enabled</span>
            </div>
        </div>
        
        <!-- Load Document Section -->
        <div id="el-load-document-section" style="background: #f9fafb; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 40px; text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #1e293b; font-size: 20px;">📄 Load Engagement Letter</h3>
            <p style="color: #64748b; margin-bottom: 20px; font-size: 15px;">Load the document you generated in the wizard to edit and finalize for printing.</p>
            
            <button id="el-load-print-content" class="button button-primary button-large" style="padding: 15px 40px !important; font-size: 16px !important; height: auto !important;">
                📂 Load Document
            </button>
            
            <div id="el-print-editor-loading" style="display: none; margin-top: 20px;">
                <div class="spinner is-active" style="float: none; margin: 0 auto;"></div>
                <p style="color: #64748b; margin-top: 10px;">Loading...</p>
            </div>
        </div>
        
        <!-- Print Options -->
        <div class="el-print-options" style="display: none; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            
            <?php if ($can_edit): ?>
            <div class="el-edit-toolbar" style="display: none; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="el-format-btn button" data-command="bold" title="Bold" style="min-width: 40px;"><strong>B</strong></button>
                    <button class="el-format-btn button" data-command="italic" title="Italic" style="min-width: 40px;"><em>I</em></button>
                    <button class="el-format-btn button" data-command="underline" title="Underline" style="min-width: 40px;"><u>U</u></button>
                    <button class="el-format-btn button" data-command="justifyLeft" title="Align Left">⬅️ Left</button>
                    <button class="el-format-btn button" data-command="justifyCenter" title="Center">↔️ Center</button>
                    <button class="el-format-btn button" data-command="justifyRight" title="Align Right">➡️ Right</button>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 20px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; padding: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" id="el-paper-only-toggle" style="width: 20px; height: 20px;">
                    <span style="font-weight: 600; flex: 1;">Paper-Only Mode</span>
                    <span style="color: #92400e; font-size: 13px;">Adds client signature line to each page</span>
                </label>
            </div>
        </div>
        
        <!-- Status Messages -->
        <div class="el-editor-status" style="margin-bottom: 20px; padding: 12px 20px; border-radius: 6px; display: none;"></div>
        
        <!-- Editor Content -->
        <?php if ($can_edit): ?>
        <div id="el-print-editor-content" contenteditable="true" style="display: none; background: white; border: 2px solid #e2e8f0; border-radius: 8px; padding: 40px; min-height: 600px; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6;"></div>
        <?php else: ?>
        <div id="el-print-preview-readonly" style="display: none; background: white; border: 2px solid #e2e8f0; border-radius: 8px; padding: 40px; min-height: 600px; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6;"></div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="el-editor-actions" style="display: none; margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
            <?php if ($can_edit): ?>
            <button id="el-save-print-content" class="button button-primary button-large" style="display: none;">💾 Save Document</button>
            <?php endif; ?>
            <button id="el-print-now" class="button button-large">🖨️ Print</button>
            <button id="el-download-pdf-preview" class="button button-large">📥 Download PDF</button>
        </div>
        
        <!-- Share Link -->
        <div id="el-share-link-container" style="display: none; margin-top: 20px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 8px; padding: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #166534;">📋 Shareable Link (89 days):</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="el-share-link" readonly style="flex: 1; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: monospace; font-size: 14px;">
                <button id="el-copy-link" class="button button-large"><span class="dashicons dashicons-clipboard"></span> Copy</button>
            </div>
        </div>
    </div>
    
    <style>
    .el-editor-status.success { background: #d1fae5; color: #065f46; border: 2px solid #86efac; display: block !important; }
    .el-editor-status.error { background: #fee2e2; color: #991b1b; border: 2px solid #fca5a5; display: block !important; }
    .el-editor-status.info { background: #dbeafe; color: #1e40af; border: 2px solid #93c5fd; display: block !important; }
    .el-page-break-indicator { text-align: center; color: #94a3b8; font-size: 12px; padding: 10px; border-top: 2px dashed #cbd5e1; border-bottom: 2px dashed #cbd5e1; margin: 20px 0; background: #f8fafc; }
    .el-signature-highlight { background: #fffbf0 !important; border: 2px dashed #ffc107 !important; padding: 8px !important; }
    </style>
    
    <?php
    return ob_get_clean();
}
// AJAX handler to load engagement letter into print editor
add_action('wp_ajax_el_load_print_editor', 'el_handle_load_print_editor');
add_action('wp_ajax_nopriv_el_load_print_editor', 'el_handle_load_print_editor');

function el_handle_load_print_editor() {
    // Verify nonce
    check_ajax_referer('el_nonce', 'nonce');
    
    // Get reference from session or POST
    $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : '';
    
    // If no reference provided, try to get from session (set by Tab 4)
    if (empty($reference)) {
        if (!session_id()) {
            session_start();
        }
        $reference = isset($_SESSION['el_pdf_reference']) ? $_SESSION['el_pdf_reference'] : '';
    }
    
    if (empty($reference)) {
        wp_send_json_error(['message' => 'No document reference found. Please generate the preview in Tab 4 first.']);
    }
    
    // Check if paper-only mode requested
    $paper_only = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
    
    // Get the saved engagement letter HTML from Tab 4
    $html = get_transient('el_saved_pdf_' . $reference);
    
    if (!$html) {
        // Try to get from PDF data if saved HTML not found
        $pdf_data = get_transient('el_pdf_data_' . $reference);
        
        if ($pdf_data) {
            // Load the preview rendering function
            $preview_file = get_stylesheet_directory() . '/preview-inline.php';
            if (file_exists($preview_file)) {
                require_once $preview_file;
                
                if (function_exists('el_render_engagement_letter_html')) {
                    $html = el_render_engagement_letter_html($pdf_data);
                    
                    // Save for future use
                    set_transient('el_saved_pdf_' . $reference, $html, 24 * HOUR_IN_SECONDS);
                }
            }
        }
    }
    
    if (!$html) {
        wp_send_json_error(['message' => 'Document not found or expired. Please regenerate in Tab 4.']);
    }
    
    // Apply pagination if paper-only mode and pagination class exists
    $response_data = [
        'html' => $html,
        'paper_only' => $paper_only,
        'pages' => 1,
        'reference' => $reference,
        'message' => 'Document loaded successfully'
    ];
    
    if ($paper_only && class_exists('EL_Pagination_Handler')) {
        $pagination_options = [
            'paper_only' => true,
            'add_page_signatures' => true,
            'signature_format' => 'Client signature …………..……………………… Date ………… Page %d/%d',
            'lines_per_page' => 54, // Less lines to accommodate signature
            'force_new_page_sections' => true
        ];
        
        $paginated = EL_Pagination_Handler::paginate_content($html, $pagination_options);
        $response_data['html'] = $paginated['html'];
        $response_data['pages'] = $paginated['page_count'];
        $response_data['paginated'] = true;
    }
    
    wp_send_json_success($response_data);
}



// Initialize JavaScript configuration
add_action('wp_footer', 'el_print_editor_config_script');
function el_print_editor_config_script() {
    if (is_page() || is_single()) {
        ?>
        <script>
        var el_print_config = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('el_nonce'); ?>',
            can_edit: <?php echo current_user_can('edit_posts') ? 'true' : 'false'; ?>
        };
        </script>
        <?php
    }
}


/**
 * ================================================================
 * AJAX: Download PDF
 * ================================================================
 */
add_action('wp_ajax_el_download_pdf', 'el_handle_download_pdf');
add_action('wp_ajax_nopriv_el_download_pdf', 'el_handle_download_pdf');

function el_handle_download_pdf() {
    check_ajax_referer('el_nonce', 'nonce');
    
    $reference = sanitize_text_field($_GET['ref'] ?? '');
    
    if (!$reference) {
        wp_die('Invalid reference');
    }
    
    // Get saved HTML
    $html = get_transient('el_saved_pdf_' . $reference);
    
    if (!$html) {
        // Try to get from original PDF data
        $pdf_data = get_transient('el_pdf_data_' . $reference);
        if ($pdf_data) {
            $preview_file = get_stylesheet_directory() . '/preview-inline.php';
            if (file_exists($preview_file)) {
                require_once $preview_file;
                if (function_exists('el_render_engagement_letter_html')) {
                    $html = el_render_engagement_letter_html($pdf_data);
                }
            }
        }
    }
    
    if (!$html) {
        wp_die('Document not found or expired');
    }
    
    // Generate PDF filename
    $filename = 'engagement-letter-' . $reference . '.pdf';
    
    // Headers for download (you would integrate with mPDF here)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "PDF would be generated here using mPDF with the HTML content";
    exit;
}
/**
 * Encryption and GDPR Compliance Functions
 */

/**
 * Get encryption key from wp-config or generate one
 */
function el_get_encryption_key() {
    // Check if key is defined in wp-config
    if (defined('EL_ENCRYPTION_KEY') && !empty(EL_ENCRYPTION_KEY)) {
        return EL_ENCRYPTION_KEY;
    }
    
    // Get or generate key stored in options
    $key = get_option('el_encryption_key');
    
    if (empty($key)) {
        // Generate a new key
        $key = base64_encode(random_bytes(32));
        update_option('el_encryption_key', $key, false); // Don't autoload
    }
    
    return $key;
}

/**
 * Encrypt sensitive data
 */
function el_encrypt_data($data) {
    if (empty($data)) {
        return '';
    }
    
    $key = el_get_encryption_key();
    $key = base64_decode($key);
    
    // Generate initialization vector
    $iv = random_bytes(16);
    
    // Encrypt the data
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    // Return IV + encrypted data (base64 encoded)
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function el_decrypt_data($encrypted_data) {
    if (empty($encrypted_data)) {
        return '';
    }
    
    $key = el_get_encryption_key();
    $key = base64_decode($key);
    
    // Decode the base64
    $data = base64_decode($encrypted_data);
    
    // Extract IV (first 16 bytes)
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    // Decrypt the data
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    
    return $decrypted;
}

/**
 * Generate time-limited access token for secure viewing
 */
function el_generate_view_token($el_id, $user_id, $expires_minutes = 60) {
    $token_data = [
        'el_id' => $el_id,
        'user_id' => $user_id,
        'expires' => time() + ($expires_minutes * 60),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'nonce' => wp_create_nonce('el_view_' . $el_id . '_' . $user_id)
    ];
    
    $encrypted_token = el_encrypt_data(json_encode($token_data));
    
    // Store token in transient (auto-expires)
    $token_key = 'el_view_token_' . md5($encrypted_token);
    set_transient($token_key, $token_data, $expires_minutes * 60);
    
    return $encrypted_token;
}

/**
 * Verify and decode view token
 */
function el_verify_view_token($token) {
    if (empty($token)) {
        return false;
    }
    
    try {
        $decrypted = el_decrypt_data($token);
        $token_data = json_decode($decrypted, true);
        
        if (!$token_data) {
            return false;
        }
        
        // Check expiration
        if (time() > $token_data['expires']) {
            return false;
        }
        
        // Verify IP hasn't changed
        $current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($current_ip !== $token_data['ip']) {
            return false;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($token_data['nonce'], 'el_view_' . $token_data['el_id'] . '_' . $token_data['user_id'])) {
            return false;
        }
        
        // Check transient still exists
        $token_key = 'el_view_token_' . md5($token);
        if (!get_transient($token_key)) {
            return false;
        }
        
        return $token_data;
    } catch (Exception $e) {
        error_log('Token verification error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Secure PDF/Document Viewer with watermark and access control
 */
add_action('init', 'el_handle_secure_viewer', 1);

function el_handle_secure_viewer() {
    if (!isset($_GET['el_view']) || !isset($_GET['token'])) {
        return;
    }
    
    $token = sanitize_text_field($_GET['token']);
    $token_data = el_verify_view_token($token);
    
    if (!$token_data) {
        wp_die('Invalid or expired access token. Please request a new viewing link.', 'Access Denied', ['response' => 403]);
    }
    
    $el_id = intval($token_data['el_id']);
    $user_id = intval($token_data['user_id']);
    
    // Verify user has access to this engagement letter
    $post = get_post($el_id);
    if (!$post || $post->post_type !== 'engagement_letter') {
        wp_die('Document not found.', 'Not Found', ['response' => 404]);
    }
    
    // Check user permissions
    if ($post->post_author != $user_id && !current_user_can('edit_post', $el_id)) {
        wp_die('You do not have permission to view this document.', 'Access Denied', ['response' => 403]);
    }
    
    // Get encrypted HTML
    $encrypted_html = get_post_meta($el_id, '_el_final_html_encrypted', true);
    
    if (empty($encrypted_html)) {
        // Fallback to unencrypted if exists
        $html = get_post_meta($el_id, '_el_final_html', true);
    } else {
        $html = el_decrypt_data($encrypted_html);
    }
    
    if (empty($html)) {
        wp_die('Document content not available.', 'Not Found', ['response' => 404]);
    }
    
    // Log the view
    el_log_document_access($el_id, $user_id, 'view');
    
    // Render secure viewer
    el_render_secure_viewer($html, $el_id, $user_id, $token_data);
    exit;
}

/**
 * Render secure document viewer with watermark
 */
function el_render_secure_viewer($html, $el_id, $user_id, $token_data) {
    $user = get_userdata($user_id);
    $expires_at = date('F j, Y g:i A', $token_data['expires']);
    $days_remaining = ceil(($token_data['expires'] - time()) / 86400);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Engagement Letter #<?php echo $el_id; ?> - <?php echo esc_html($user->display_name); ?></title>
        <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #2c3e50;
        }
        
        .viewer-header {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .viewer-info {
            font-size: 14px;
        }
        
        .viewer-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .expiry-badge {
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .viewer-container {
            height: calc(100vh - 70px);
            overflow: auto;
            background: #ecf0f1;
            padding: 20px;
        }
        
        .document-wrapper {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        
        /* Subtle watermark */
        .watermark-overlay {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255,255,255,0.9);
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 11px;
            color: #7f8c8d;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            pointer-events: none;
            z-index: 9999;
        }
        
        @media print {
            .viewer-header, .watermark-overlay {
                display: none;
            }
            .viewer-container {
                height: auto;
                background: white;
                padding: 0;
            }
            body {
                background: white;
            }
        }
        
        .copy-notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 10000;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        </style>
    </head>
    <body>
        <div class="viewer-header">
            <div class="viewer-info">
                <strong>📄 Engagement Letter #<?php echo $el_id; ?></strong> | 
                Viewing as: <?php echo esc_html($user->display_name); ?>
            </div>
            <div class="viewer-actions">
                <span class="expiry-badge">
                    ⏱ Expires in <?php echo $days_remaining; ?> day<?php echo $days_remaining != 1 ? 's' : ''; ?>
                </span>
                <button class="btn btn-primary" onclick="window.print()">
                    🖨️ Print
                </button>
                <button class="btn btn-success" onclick="copyShareLink()">
                    🔗 Copy Share Link
                </button>
            </div>
        </div>
        
        <div class="copy-notification" id="copyNotification">
            ✓ Share link copied to clipboard!
        </div>
        
        <div class="watermark-overlay">
            Viewed by: <?php echo esc_html($user->display_name); ?><br>
            <?php echo date('Y-m-d H:i:s'); ?><br>
            Link expires: <?php echo $expires_at; ?>
        </div>
        
        <div class="viewer-container">
            <div class="document-wrapper">
                <?php echo $html; ?>
            </div>
        </div>
        
        <script>
        function copyShareLink() {
            var shareUrl = window.location.href;
            
            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl).then(function() {
                    showCopyNotification();
                }).catch(function() {
                    fallbackCopy(shareUrl);
                });
            } else {
                fallbackCopy(shareUrl);
            }
        }
        
        function fallbackCopy(text) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopyNotification();
            } catch (err) {
                alert('Unable to copy. Please copy the URL manually:\n\n' + text);
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopyNotification() {
            var notification = document.getElementById('copyNotification');
            notification.style.display = 'block';
            
            setTimeout(function() {
                notification.style.display = 'none';
            }, 3000);
        }
        
        // Log viewing heartbeat every 2 minutes
        setInterval(function() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=el_log_viewing_heartbeat&el_id=<?php echo $el_id; ?>&nonce=<?php echo wp_create_nonce('el_heartbeat'); ?>'
            });
        }, 120000);
        </script>
    </body>
    </html>
    <?php
}

/**
 * Log document access for audit trail
 */
function el_log_document_access($el_id, $user_id, $action = 'view') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'el_access_log';
    
    $wpdb->insert(
        $table_name,
        [
            'el_id' => $el_id,
            'user_id' => $user_id,
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'access_date' => current_time('mysql')
        ],
        ['%d', '%d', '%s', '%s', '%s', '%s']
    );
}

/**
 * Heartbeat to track active viewing sessions
 */
add_action('wp_ajax_el_log_viewing_heartbeat', 'el_ajax_log_viewing_heartbeat');
add_action('wp_ajax_nopriv_el_log_viewing_heartbeat', 'el_ajax_log_viewing_heartbeat');

function el_ajax_log_viewing_heartbeat() {
    check_ajax_referer('el_heartbeat', 'nonce');
    
    $el_id = intval($_POST['el_id'] ?? 0);
    
    if ($el_id) {
        el_log_document_access($el_id, get_current_user_id(), 'heartbeat');
    }
    
    wp_send_json_success();
}

/**
 * Log GDPR consent
 */
function el_log_gdpr_consent($user_id, $consent_type, $ip_address = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'el_gdpr_consent';
    
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'consent_type' => $consent_type,
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'consent_date' => current_time('mysql'),
            'consent_given' => 1
        ],
        ['%d', '%s', '%s', '%s', '%s', '%d']
    );
}

/**
 * Check if user has given GDPR consent
 */
function el_has_gdpr_consent($user_id, $consent_type) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'el_gdpr_consent';
    
    $consent = $wpdb->get_var($wpdb->prepare(
        "SELECT consent_given FROM $table_name 
         WHERE user_id = %d AND consent_type = %s 
         ORDER BY consent_date DESC LIMIT 1",
        $user_id,
        $consent_type
    ));
    
    return $consent === '1';
}

/**
 * Delete user's engagement letter data (GDPR Right to Erasure)
 */
function el_delete_user_data($user_id) {
    global $wpdb;
    
    // Delete engagement letter posts
    $posts = get_posts([
        'post_type' => 'engagement_letter',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'any'
    ]);
    
    foreach ($posts as $post) {
        // Delete attachments
        $attachments = get_attached_media('', $post->ID);
        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true);
        }
        
        // Delete post
        wp_delete_post($post->ID, true);
    }
    
    // Delete user meta
    delete_user_meta($user_id, 'el_can_edit_documents');
    
    // Delete GDPR consent logs
    $consent_table = $wpdb->prefix . 'el_gdpr_consent';
    $wpdb->delete($consent_table, ['user_id' => $user_id], ['%d']);
    
    // Delete access logs
    $access_table = $wpdb->prefix . 'el_access_log';
    $wpdb->delete($access_table, ['user_id' => $user_id], ['%d']);
    
    // Log the deletion
    error_log("Engagement Letter data deleted for user ID: $user_id");
    
    return true;
}

/**
 * SIGNATURE COLLECTION SYSTEM
 */

/**
 * Add signature collection settings to engagement letter post
 */
add_action('add_meta_boxes', 'el_add_signature_meta_box');

function el_add_signature_meta_box() {
    add_meta_box(
        'el_signature_settings',
        'Signature Collection Settings',
        'el_signature_meta_box_callback',
        'engagement_letter',
        'side',
        'high'
    );
}

function el_signature_meta_box_callback($post) {
    wp_nonce_field('el_signature_settings', 'el_signature_nonce');
    
    $status = get_post_meta($post->ID, 'el_status', true);
    $reference = get_post_meta($post->ID, 'el_reference', true);
    $signed_reference = get_post_meta($post->ID, 'el_signed_reference', true);
    $signature_date = get_post_meta($post->ID, 'el_signature_date', true);
    $signed_pdf_url = get_post_meta($post->ID, 'el_signed_pdf_url', true);
    $signed_pdf_expiry = get_post_meta($post->ID, 'el_signed_pdf_expiry', true);
    
    // Generate client signature link (unsigned PDF with {{sig}} placeholders)
    $client_view_url = '';
    if ($reference) {
        $client_view_url = add_query_arg([
            'el_view' => '1',
            'ref' => $reference
        ], home_url('/'));
    }
    
    ?>
    <div class="el-signature-settings">
        <?php if ($status === 'signed' && $signed_reference): ?>
            <!-- SIGNED STATUS -->
            <div class="notice notice-success inline" style="margin:0 0 15px 0; padding:10px;">
                <strong>✅ Engagement Letter Signed</strong><br>
                <small>Signed on: <?php echo esc_html($signature_date); ?></small>
            </div>
            
            <?php if ($signed_pdf_url): ?>
            <p>
                <strong>📄 Signed PDF Download:</strong><br>
                <a href="<?php echo esc_url($signed_pdf_url); ?>" class="button button-primary" target="_blank">
                    <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
                    Download Signed PDF
                </a>
                <br>
                <small style="color:#666;">
                    <?php if ($signed_pdf_expiry): ?>
                        Expires: <?php echo date('F j, Y', strtotime($signed_pdf_expiry)); ?>
                    <?php endif; ?>
                </small>
            </p>
            <?php endif; ?>
            
            <hr style="margin:15px 0;">
            
        <?php endif; ?>
        
        <!-- CLIENT SIGNATURE LINK -->
        <?php if ($client_view_url): ?>
        <p>
            <strong>📝 Send to Client for Signature:</strong><br>
            <small style="display:block; margin:5px 0 10px 0; color:#666;">
                Share this link with your client. They will view the engagement letter and sign electronically.
            </small>
            
            <div style="display:flex; gap:5px;">
                <input type="text" 
                       id="el-client-signature-link" 
                       value="<?php echo esc_attr($client_view_url); ?>" 
                       readonly 
                       style="flex:1; padding:5px; font-size:11px; font-family:monospace; background:#f9f9f9;">
                <button type="button" 
                        class="button" 
                        onclick="navigator.clipboard.writeText(document.getElementById('el-client-signature-link').value); this.textContent='✓ Copied'; setTimeout(() => this.textContent='Copy', 2000);">
                    Copy
                </button>
            </div>
        </p>
        
        <div style="margin-top:10px; padding:10px; background:#fffbeb; border-left:4px solid #f59e0b; font-size:12px;">
            <strong>💡 How it works:</strong>
            <ol style="margin:5px 0 0 20px; padding:0;">
                <li>Client opens the link</li>
                <li>Reviews the engagement letter</li>
                <li>Signs using HTML5 signature pad (if {{sig}} placeholders are in template)</li>
                <li>You receive an email with signed PDF link (89-day validity)</li>
                <li>Signed PDF is automatically attached to this record</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="notice notice-warning inline" style="margin:0; padding:10px;">
            <strong>⚠️ No Reference Number</strong><br>
            <small>Generate the PDF preview in the wizard to create a signature link.</small>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    .el-signature-settings .button .dashicons {
        margin-top: 3px;
    }
    </style>
    <?php
}

/**
 * Save signature collection settings (now minimal - most data saved via AJAX)
 */
add_action('save_post_engagement_letter', 'el_save_signature_settings');

function el_save_signature_settings($post_id) {
    if (!isset($_POST['el_signature_nonce']) || !wp_verify_nonce($_POST['el_signature_nonce'], 'el_signature_settings')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Signature data is saved via AJAX when client signs
    // This function exists to maintain nonce verification
}

/**
 * Client view endpoint - displays PDF with signature placeholders
 */
add_action('template_redirect', 'el_handle_client_signature_view');

function el_handle_client_signature_view() {
    if (!isset($_GET['el_view']) || $_GET['el_view'] !== '1') {
        return;
    }
    
    if (!isset($_GET['ref']) || empty($_GET['ref'])) {
        wp_die('Invalid or missing reference number', 'Error', ['response' => 400]);
    }
    
    $reference = sanitize_text_field($_GET['ref']);
    
    // Get PDF data from transient
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    
    if (!$pdf_data) {
        wp_die('Engagement letter not found or has expired. Please contact your lawyer for a new link.', 'Not Found', ['response' => 404]);
    }
    
    // Generate HTML with signature placeholders intact
    $html = el_render_print_ready_html($pdf_data);
    
    // Wrap in a clean page template
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Engagement Letter - <?php echo esc_html($reference); ?></title>
        <?php wp_head(); ?>
        <style>
            body {
                margin: 0;
                padding: 20px;
                background: #f3f4f6;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .el-signature-page-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-radius: 8px;
            }
            .el-page-header {
                text-align: center;
                padding: 20px 0 30px 0;
                border-bottom: 2px solid #e5e7eb;
                margin-bottom: 30px;
            }
            .el-page-header h1 {
                margin: 0 0 10px 0;
                color: #1f2937;
                font-size: 28px;
            }
            .el-page-header .reference {
                color: #6b7280;
                font-size: 14px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="el-signature-page-wrapper" data-el-reference="<?php echo esc_attr($reference); ?>">
            <div class="el-page-header">
                <h1>📋 Engagement Letter</h1>
                <div class="reference">Reference: <?php echo esc_html($reference); ?></div>
            </div>
            
            <div id="el-pdf-$_SESSION['el_pdf_reference']-content">
                <?php echo $html; ?>
            </div>
        </div>
        
        <script>
        // Inline signature detection - bypasses enqueue issues
        console.log('=== INLINE SIGNATURE SCRIPT LOADING ===');
        console.log('jQuery available:', typeof jQuery !== 'undefined');
        console.log('Reference:', '<?php echo esc_js($reference); ?>');
        </script>
        
        <?php wp_footer(); ?>
        
        <script>
        // Load signature UI after wp_footer
        console.log('=== POST WP_FOOTER ===');
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                console.log('jQuery ready, checking for signature placeholders...');
                
                var content = $('#el-pdf-preview-content').html();
                if (content && (content.includes('{{sig}}') || content.includes('{{sig1}}'))) {
                    console.log('✓ Signature placeholders detected!');
                    console.log('Loading signature UI script...');
                    
                    // Load the signature UI script dynamically
                    var script = document.createElement('script');
                    script.src = '<?php echo get_stylesheet_directory_uri(); ?>/js/el-signature-ui.js?v=<?php echo time(); ?>';
                    script.onload = function() {
                        console.log('✓ Signature UI script loaded successfully');
                    };
                    script.onerror = function() {
                        console.error('✗ Failed to load signature UI script');
                        console.error('Expected path:', script.src);
                    };
                    document.head.appendChild(script);
                    
                    // Pass data to script
                    window.elSignatureData = {
                        nonce: '<?php echo wp_create_nonce('el_signature_nonce'); ?>',
                        ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
                        reference: '<?php echo esc_js($reference); ?>'
                    };
                } else {
                    console.log('No signature placeholders found in content');
                }
            });
        } else {
            console.error('jQuery not available!');
        }
        </script>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Handle signature collection page (old - kept for backward compatibility)
 */
add_action('template_redirect', 'el_handle_signature_collection');

function el_handle_signature_collection() {
    if (!isset($_GET['el_sign']) || !isset($_GET['token'])) {
        return;
    }
    
    $token = urldecode($_GET['token']);
    
    try {
        $decrypted = el_decrypt_data($token);
        $token_data = json_decode($decrypted, true);
        
        if (!$token_data || $token_data['purpose'] !== 'signature') {
            wp_die('Invalid signature link', 'Invalid Link', ['response' => 403]);
        }
        
        if (time() > $token_data['expires']) {
            wp_die('This signature link has expired. Please contact the sender for a new link.', 'Link Expired', ['response' => 403]);
        }
        
        $el_id = intval($token_data['el_id']);
        $form_id = intval($token_data['form_id']);
        
        $signatures_collected = get_post_meta($el_id, '_el_signatures_collected', true);
        if ($signatures_collected) {
            el_render_already_signed_page($el_id, $signatures_collected);
            exit;
        }
        
        el_render_signature_page($el_id, $form_id, $token);
        exit;
        
    } catch (Exception $e) {
        wp_die('Invalid signature link', 'Invalid Link', ['response' => 403]);
    }
}

/**
 * Render signature collection page
 */
function el_render_signature_page($el_id, $form_id, $token) {
    $encrypted_html = get_post_meta($el_id, '_el_final_html_encrypted', true);
    $document_html = !empty($encrypted_html) ? el_decrypt_data($encrypted_html) : get_post_meta($el_id, '_el_final_html', true);
    
    if (empty($document_html)) {
        wp_die('Document not found', 'Not Found', ['response' => 404]);
    }
    
    get_header();
    ?>
    <div style="max-width:1200px; margin:40px auto; padding:0 20px;">
        <h1 style="color:#1e293b; border-bottom:2px solid #e2e8f0; padding-bottom:15px;">📝 Sign Engagement Letter</h1>
        
        <div style="background:white; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); padding:30px; margin:20px 0; max-height:500px; overflow-y:auto;">
            <h3 style="color:#64748b; margin-top:0;">Document Preview</h3>
            <?php echo $document_html; ?>
        </div>
        
        <div style="background:white; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); padding:30px;">
            <h2 style="color:#1e293b;">Signature Required</h2>
            
            <div style="background:#f1f5f9; border:2px solid #cbd5e1; border-radius:6px; padding:20px; margin:20px 0;">
                <label style="display:flex; align-items:start; gap:10px; cursor:pointer;">
                    <input type="checkbox" id="consent-checkbox" required style="margin-top:4px; width:20px; height:20px;">
                    <span>
                        <strong>I have read and agree to the terms above.</strong><br>
                        <small>By checking this box and signing below, you agree to all terms and conditions outlined in this engagement letter.</small>
                    </span>
                </label>
            </div>
            
            <?php
            if (function_exists('gravity_form')) {
                echo '<div id="signature-form-container">';
                gravity_form($form_id, true, false, false, ['el_id' => $el_id, 'token' => $token], true);
                echo '</div>';
            }
            ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var $form = $('#signature-form-container form');
        var $submitBtn = $form.find('input[type="submit"]');
        
        $submitBtn.prop('disabled', true).css('opacity', '0.5');
        
        $('#consent-checkbox').on('change', function() {
            $submitBtn.prop('disabled', !this.checked).css('opacity', this.checked ? '1' : '0.5');
        });
    });
    </script>
    <?php
    get_footer();
}

/**
 * Render already signed page
 */
function el_render_already_signed_page($el_id, $signed_date) {
    get_header();
    ?>
    <div style="max-width:500px; margin:100px auto; padding:40px; background:white; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); text-align:center;">
        <h1 style="color:#10b981; font-size:48px; margin:0 0 20px 0;">✓</h1>
        <h2 style="color:#1e293b; margin:0 0 10px 0;">Document Already Signed</h2>
        <p style="color:#64748b;">This engagement letter was signed on <strong><?php echo esc_html($signed_date); ?></strong>.</p>
        <p style="color:#64748b;">If you believe this is an error, please contact the sender.</p>
    </div>
    <?php
    get_footer();
}

/**
 * Hook into Gravity Forms submission
 */
add_action('gform_after_submission', 'el_mark_signatures_collected', 10, 2);

function el_mark_signatures_collected($entry, $form) {
    if (empty($entry['el_id'])) {
        return;
    }
    
    $el_id = intval($entry['el_id']);
    
    update_post_meta($el_id, '_el_signatures_collected', current_time('mysql'));
    update_post_meta($el_id, '_el_signature_entry_id', $entry['id']);
    
    el_log_document_access($el_id, 0, 'signature_collected');
    
    // Notify lawyer
    $post = get_post($el_id);
    if ($post && $post->post_author) {
        $author = get_userdata($post->post_author);
        if ($author && $author->user_email) {
            wp_mail(
                $author->user_email,
                'Engagement Letter Signed - #' . $el_id,
                "The engagement letter has been signed.\n\nView it here: " . get_edit_post_link($el_id, 'raw')
            );
        }
    }
}

/**
 * Create GDPR and access log tables
 */
function el_create_gdpr_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // GDPR Consent table
    $consent_table = $wpdb->prefix . 'el_gdpr_consent';
    $consent_sql = "CREATE TABLE IF NOT EXISTS $consent_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        consent_type varchar(50) NOT NULL,
        consent_given tinyint(1) NOT NULL,
        ip_address varchar(45) NOT NULL,
        user_agent text NOT NULL,
        consent_date datetime NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY consent_type (consent_type)
    ) $charset_collate;";
    
    // Access Log table
    $access_table = $wpdb->prefix . 'el_access_log';
    $access_sql = "CREATE TABLE IF NOT EXISTS $access_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        el_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        action varchar(50) NOT NULL,
        ip_address varchar(45) NOT NULL,
        user_agent text NOT NULL,
        access_date datetime NOT NULL,
        PRIMARY KEY (id),
        KEY el_id (el_id),
        KEY user_id (user_id),
        KEY access_date (access_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($consent_sql);
    dbDelta($access_sql);
}

// Create tables on activation
add_action('after_switch_theme', 'el_create_gdpr_tables');
add_action('init', 'el_create_gdpr_tables', 5);

/**
 * Update save function to encrypt sensitive data
 */
add_action('wp_ajax_el_save_edited_pdf', 'el_ajax_save_edited_pdf');
add_action('wp_ajax_nopriv_el_save_edited_pdf', 'el_ajax_save_edited_pdf');

/**
 * Early download handler - catches requests before WordPress outputs anything
 */
add_action('init', 'el_handle_early_download', 1);

function el_handle_early_download() {
    // Check if this is a download request
    if (!isset($_GET['action']) || $_GET['action'] !== 'el_download_final_pdf') {
        return;
    }
    
    if (!isset($_GET['ref']) || empty($_GET['ref'])) {
        return;
    }
    
    // This is our download request - handle it immediately
    el_process_download_request();
    exit;
}

/**
 * Process download request
 */
function el_process_download_request() {
    $reference = sanitize_text_field($_GET['ref']);
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    
    if (!$pdf_data) {
        wp_die('PDF data expired. Please regenerate the document.');
    }
    
    // Get edited HTML or generate fresh
    $html = isset($pdf_data['edited_html']) ? $pdf_data['edited_html'] : el_render_print_ready_html($pdf_data);
    
    $upload_dir = wp_upload_dir();
    $temp_html_path = $upload_dir['path'] . '/temp_' . $reference . '.html';
    file_put_contents($temp_html_path, $html);
    
    $pdf_path = $upload_dir['path'] . '/engagement-letter-' . $reference . '.pdf';
    
    // Try wkhtmltopdf
    $wkhtmltopdf = '/usr/bin/wkhtmltopdf';
    $pdf_generated = false;
    
    if (file_exists($wkhtmltopdf)) {
        $command = sprintf(
            '%s --page-size A4 --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0 --enable-local-file-access %s %s 2>&1',
            escapeshellarg($wkhtmltopdf),
            escapeshellarg($temp_html_path),
            escapeshellarg($pdf_path)
        );
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($pdf_path)) {
            $pdf_generated = true;
        }
    }
    
    if ($pdf_generated) {
        // Serve PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="engagement-letter-' . $reference . '.pdf"');
        header('Content-Length: ' . filesize($pdf_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($pdf_path);
        
        // Cleanup
        @unlink($temp_html_path);
        @unlink($pdf_path);
    } else {
        // Serve HTML for browser to print
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        echo $html;
        
        @unlink($temp_html_path);
    }
}

/**
 * Download final PDF
 */
add_action('wp_ajax_el_download_final_pdf', 'el_ajax_download_final_pdf');
add_action('wp_ajax_nopriv_el_download_final_pdf', 'el_ajax_download_final_pdf');

function el_ajax_download_final_pdf() {
    // Verify nonce for security
    if (!isset($_GET['ref']) || empty($_GET['ref'])) {
        wp_die('Invalid PDF reference');
    }
    
    $reference = sanitize_text_field($_GET['ref']);
    
    // Get PDF data
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    
    if (!$pdf_data) {
        wp_die('PDF data expired. Please regenerate the document.');
    }
    
    // Get edited HTML or generate fresh
    $html = $pdf_data['edited_html'] ?? el_render_print_ready_html($pdf_data);
    
    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer
    ob_start();
    
    $upload_dir = wp_upload_dir();
    $temp_html_path = $upload_dir['path'] . '/temp_' . $reference . '.html';
    file_put_contents($temp_html_path, $html);
    
    $pdf_path = $upload_dir['path'] . '/engagement-letter-' . $reference . '.pdf';
    
    // Try wkhtmltopdf
    $wkhtmltopdf = '/usr/bin/wkhtmltopdf';
    if (file_exists($wkhtmltopdf)) {
        $command = sprintf(
            '%s --page-size A4 --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0 --enable-local-file-access %s %s 2>&1',
            escapeshellarg($wkhtmltopdf),
            escapeshellarg($temp_html_path),
            escapeshellarg($pdf_path)
        );
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($pdf_path)) {
            // Clear output buffer
            ob_end_clean();
            
            // Serve PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="engagement-letter-' . $reference . '.pdf"');
            header('Content-Length: ' . filesize($pdf_path));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            readfile($pdf_path);
            
            // Cleanup
            @unlink($temp_html_path);
            @unlink($pdf_path);
            
            die();
        }
    }
    
    // Fallback: serve HTML for browser to print to PDF
    ob_end_clean();
    
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="engagement-letter-' . $reference . '.html"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    echo $html;
    
    @unlink($temp_html_path);
    die();
}

/**
 * Add user profile field for document editing permission
 */
add_action('show_user_profile', 'el_add_user_edit_field');
add_action('edit_user_profile', 'el_add_user_edit_field');

function el_add_user_edit_field($user) {
    if (!current_user_can('edit_users')) {
        return;
    }
    
    $can_edit = get_user_meta($user->ID, 'el_can_edit_documents', true);
    ?>
    <h3>Engagement Letter Permissions</h3>
    <table class="form-table">
        <tr>
            <th><label for="el_can_edit_documents">Can Edit Documents</label></th>
            <td>
                <label>
                    <input type="checkbox" 
                           name="el_can_edit_documents" 
                           id="el_can_edit_documents" 
                           value="1" 
                           <?php checked($can_edit, '1'); ?> />
                    Allow this user to edit engagement letter documents in WYSIWYG editor
                </label>
                <p class="description">If unchecked, user can only view documents (read-only).</p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'el_save_user_edit_field');
add_action('edit_user_profile_update', 'el_save_user_edit_field');

function el_save_user_edit_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    $can_edit = isset($_POST['el_can_edit_documents']) ? '1' : '0';
    update_user_meta($user_id, 'el_can_edit_documents', $can_edit);
}

/**
 * Download engagement letter as PDF file
 */
add_action('wp_ajax_el_download_pdf', 'el_download_pdf');
add_action('wp_ajax_nopriv_el_download_pdf', 'el_download_pdf');

function el_download_pdf() {
    check_ajax_referer('el_wizard_nonce', 'nonce');
    
    if (!isset($_GET['ref'])) {
        wp_die('Missing reference', 'Error', ['response' => 400]);
    }
    
    $reference = sanitize_text_field($_GET['ref']);
    $pdf_data = get_transient('el_pdf_data_' . $reference);
    
    if (!$pdf_data) {
        wp_die('PDF data not found or expired', 'Error', ['response' => 404]);
    }
    
    // Generate HTML
    $html = el_render_print_ready_html($pdf_data);
    
    // Check if Gravity PDF is available
    if (class_exists('GFPDF_Core')) {
        // Use Gravity PDF's advanced PDF generation
        require_once(WP_PLUGIN_DIR . '/gravity-forms-pdf-extended/src/helper/Helper_PDF.php');
        
        $gfpdf = GFPDF_Core::get_instance();
        $pdf_generator = $gfpdf->get_pdf_engine();
        
        $pdf_generator->set_paper('A4', 'portrait');
        $pdf_generator->set_html($html);
        $pdf_generator->render();
        
        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $reference . '.pdf"');
        echo $pdf_generator->output();
        exit;
    }
    
    // Fallback: Use WordPress built-in PDF generation (if available via plugins)
    // Or use mPDF/TCPDF if installed
    if (class_exists('mPDF')) {
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_top' => 20,
            'margin_right' => 15,
            'margin_bottom' => 25,
            'margin_left' => 15,
        ]);
        
        $mpdf->WriteHTML($html);
        $mpdf->Output($reference . '.pdf', 'D'); // D = download
        exit;
    }
    
    // Final fallback: Just output the HTML with print instructions
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    echo '<style>@media print { .print-instructions { display: none; } }</style>';
    echo '</head><body>';
    echo '<div class="print-instructions" style="background:#fff3cd; border:2px solid #ffc107; padding:20px; margin:20px; border-radius:8px;">';
    echo '<h2>⚠️ PDF Library Not Available</h2>';
    echo '<p>To download this as a PDF, please use your browser\'s Print function (Ctrl+P or Cmd+P) and select "Save as PDF".</p>';
    echo '<button onclick="window.print()" style="background:#007bff; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; font-size:16px;">Print / Save as PDF</button>';
    echo '</div>';
    echo $html;
    echo '</body></html>';
    exit;
}

/**
 * Generate print-ready HTML with A4 page breaks
 */
function el_render_print_ready_html($pdf_data) {
    $form_data = $pdf_data['form_data'] ?? [];
    
    // Get boilerplate content
    $letterhead_raw = get_field('boilerplate_letterhead', 'option') ?: '';
    $top_left_raw = get_field('boilerplate_opening_tl', 'option') ?: '';
    $top_right_raw = get_field('boilerplate_opening_tr_copy', 'option') ?: '';
    $footer_boilerplate_raw = get_field('footer_boilerplate', 'option') ?: '';
    $signature_block_raw = get_field('signature_block_template', 'option') ?: '';
    $firm_footer_raw = get_field('firm_footer', 'option') ?: '';
    
    // Apply merge tags
    $letterhead = el_replace_merge_tags($letterhead_raw, $form_data, $pdf_data);
    $top_left = el_replace_merge_tags($top_left_raw, $form_data, $pdf_data);
    $top_right = el_replace_merge_tags($top_right_raw, $form_data, $pdf_data);
    $footer_boilerplate = el_replace_merge_tags($footer_boilerplate_raw, $form_data, $pdf_data);
    $signature_block = el_replace_merge_tags($signature_block_raw, $form_data, $pdf_data);
    $firm_footer = el_replace_merge_tags($firm_footer_raw, $form_data, $pdf_data);
    
    // Build CSS as a string to prevent WordPress from mangling it
    $print_css = <<<CSS
/* A4 Print-Perfect Styles */
@page { size: A4 portrait; margin: 5mm 15mm 25mm 15mm; }

* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box;
    font-family: 'Times New Roman', Times, serif !important;
}

body {
    font-family: 'Times New Roman', Times, serif !important;
    font-size: 11pt;
    line-height: 1.5;
    color: #000000;
    background: #e5e5e5;
    width: 100%;
    margin: 0;
    padding: 20px 0;
}

p, div, span, h1, h2, h3, h4, h5, h6, li, td, th {
    font-family: 'Times New Roman', Times, serif !important;
}
CSS;
    
    $print_css .= <<<CSS

/* Each page looks like a separate A4 sheet */
.print-page {
    width: 210mm;
    min-height: 297mm;
    height: auto;
    padding: 0;
    background: white;
    position: relative;
    margin: 0 auto 5mm auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    page-break-after: always;
    overflow: visible;
    display: flex;
    flex-direction: column;
}

.print-page:last-child {
    page-break-after: auto;
    margin-bottom: 20mm;
}

/* Fix image alignment */
img {
    display: block;
    max-width: 100%;
    height: auto;
}

.aligncenter, img.aligncenter {
    margin: 0 auto !important;
    display: block !important;
    float: none !important;
}

.alignleft, img.alignleft {
    float: left;
    margin-right: 10mm;
}

.alignright, img.alignright {
    float: right;
    margin-left: 10mm;
}

.alignnone, img.alignnone { margin: 0; }

/* Center alignment for divs and text */
[style*="text-align: center"], [style*="text-align:center"] {
    text-align: center !important;
}

[style*="text-align: center"] img, [style*="text-align:center"] img {
    margin-left: auto !important;
    margin-right: auto !important;
    display: block !important;
    float: none !important;
}

/* Page Header - tight to top edge like Word header */
.page-header {
    text-align: center;
    padding: 10mm 15mm 5mm 15mm;
    border-bottom: 1px solid #cccccc;
    flex-shrink: 0;
}

.page-header img { 
    margin: 0 auto !important;
    display: block !important;
    float: none !important;
}
.page-header h1 { font-size: 16pt; font-weight: bold; margin-bottom: 3mm; }
.page-header .firm-details { font-size: 9pt; color: #666666; }

/* Opening Section */
.opening-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10mm;
    margin-bottom: 10mm;
    page-break-inside: avoid;
}

.opening-section .left-col, .opening-section .right-col { font-size: 10pt; }

/* Content Sections */
.content-section {
    margin-bottom: 8mm;
    page-break-inside: avoid;
    border: 1px solid #e0e0e0;
    padding: 5mm;
}

.content-section h2 { font-size: 13pt; font-weight: bold; margin-bottom: 3mm; color: #000000; }
.content-section h3 { font-size: 11pt; font-weight: bold; margin: 3mm 0 2mm 0; }
.content-section p { margin-bottom: 3mm; text-align: justify; }
.content-section ul, .content-section ol { margin: 2mm 0 3mm 8mm; }
.content-section li { margin-bottom: 2mm; }

/* Page content area with proper margins */
.page-content-area {
    flex: 1;
    padding: 0 20mm;  /* Left and right margins */
    margin: 15mm 0;   /* Top and bottom spacing from header/footer */
    overflow: visible;
}

/* Clauses */
.clause {
    margin-bottom: 6mm;
    page-break-inside: avoid;
    padding: 3mm;
    border-left: 3px solid #cccccc;
}

.clause-title { font-weight: bold; font-size: 11pt; margin-bottom: 2mm; }
.clause-content { text-align: justify; }

/* Signature Block - HIGHLIGHTED */
.signature-section {
    margin-top: 15mm;
    page-break-inside: avoid;
    background: #f9f9f9;
    border: 2px solid #333333;
    padding: 8mm;
}

.signature-section h3 {
    font-size: 12pt;
    font-weight: bold;
    margin-bottom: 5mm;
    text-transform: uppercase;
}

.signature-block {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10mm;
    margin-top: 8mm;
}

.signature-field {
    border-top: 2px solid #000000;
    padding-top: 2mm;
    min-height: 15mm;
}

.signature-field .label {
    font-weight: bold;
    font-size: 9pt;
    text-transform: uppercase;
}

/* Page Footer - tight to bottom edge like Word footer */
.page-footer {
    border-top: 1px solid #cccccc;
    padding: 5mm 15mm 10mm 15mm;
    font-size: 8pt;
    color: #666666;
    flex-shrink: 0;
    margin-top: auto;
}

.page-number {
    text-align: center;
    margin-bottom: 2mm;
    font-weight: bold;
}

.firm-footer {
    font-size: 7pt;
    text-align: center;
    color: #999999;
}

.firm-footer img { 
    margin: 0 auto !important;
    display: block !important;
    float: none !important;
}

/* Print-specific overrides */
@media print {
    body { background: white; padding: 0; }
    .print-page { margin: 0; box-shadow: none; width: 100%; height: auto; min-height: auto; }
    .page-footer { position: fixed; bottom: 10mm; }
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 3mm 0;
    page-break-inside: avoid;
}

th, td { border: 1px solid #cccccc; padding: 2mm; text-align: left; }
th { background: #f5f5f5; font-weight: bold; }

/* Dotty fields */
.dotty:empty::after { content: "..............."; }
CSS;
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style><?php echo $print_css; ?></style>
    </head>
    <body>
    <body>
        <?php
        // Calculate total pages conditionally
        $has_signature_page = !empty($signature_block) || !empty($footer_boilerplate);
        $total_pages = count($pdf_data['items']) + 1 + ($has_signature_page ? 1 : 0); // 1 for opening page, +1 if signature page exists
        $current_page = 1;
        ?>
        
        <!-- Page 1: Header and Opening -->
        <div class="print-page">
            <div class="page-header">
                <?php echo wp_kses_post($letterhead); ?>
            </div>
            
            <div class="page-content-area">
                <div class="opening-section">
                    <div class="left-col">
                        <?php echo wp_kses_post($top_left); ?>
                    </div>
                    <div class="right-col">
                        <?php echo wp_kses_post($top_right); ?>
                    </div>
                </div>
                
                <div class="content-section">
                    <h2>Engagement Letter</h2>
                    <p><strong>Date:</strong> <?php echo esc_html($pdf_data['date']); ?></p>
                    <p><strong>Reference:</strong> <?php echo esc_html($pdf_data['reference']); ?></p>
                    <?php if (!empty($form_data['full_name'])): ?>
                    <p><strong>Client:</strong> <?php echo esc_html($form_data['full_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="page-footer">
                <div class="page-number">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
                <div class="firm-footer"><?php echo wp_kses_post($firm_footer); ?></div>
            </div>
        </div>
        
        <?php foreach ($pdf_data['items'] as $idx => $item): 
            $current_page++;
        ?>
        <!-- Service Page: <?php echo esc_html($item['name']); ?> -->
        <div class="print-page">
            <div class="page-header">
                <?php echo wp_kses_post($letterhead); ?>
            </div>
            
            <div class="page-content-area">
                <div class="content-section">
                    <h2><?php echo esc_html($item['pdf_title'] ?: $item['name']); ?></h2>
                    <?php if (!empty($item['pdf_subtitle'])): ?>
                    <h3><?php echo esc_html($item['pdf_subtitle']); ?></h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['pdf_text'])): ?>
                    <div class="service-description">
                        <?php echo wpautop(wp_kses_post($item['pdf_text'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($item['pdf_clauses'])): ?>
                <div class="content-section">
                    <h3>Terms and Conditions</h3>
                    <?php foreach ($item['pdf_clauses'] as $clause_idx => $clause): ?>
                    <div class="clause">
                        <?php if (!empty($clause['title'])): ?>
                        <div class="clause-title"><?php echo ($clause_idx + 1) . '. ' . esc_html($clause['title']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($clause['body'])): ?>
                        <div class="clause-content"><?php echo wpautop(wp_kses_post($clause['body'])); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($item['pdf_footer'])): ?>
                <div class="content-section">
                    <?php echo wpautop(wp_kses_post($item['pdf_footer'])); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="page-footer">
                <div class="page-number">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
                <div class="firm-footer"><?php echo wp_kses_post($firm_footer); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Final Page: Signatures (only if signature block template is filled) -->
        <?php if (!empty($signature_block) || !empty($footer_boilerplate)): ?>
        <?php $current_page++; ?>
        <div class="print-page">
            <div class="page-header">
                <?php echo wp_kses_post($letterhead); ?>
            </div>
            
            <div class="page-content-area">
                <?php if (!empty($signature_block)): ?>
                <div class="signature-section">
                    <h3>⚠️ Signatures Required</h3>
                    <?php echo wpautop(wp_kses_post($signature_block)); ?>
                    
                    <?php 
                    // Only show manual signature fields if the signature block doesn't contain {{sig}} placeholders
                    $has_sig_placeholders = (stripos($signature_block, '{{sig}}') !== false || 
                                             stripos($signature_block, '{{sig1}}') !== false || 
                                             stripos($signature_block, '{{sig2}}') !== false);
                    
                    if (!$has_sig_placeholders): 
                    ?>
                    <div class="signature-block">
                        <div class="signature-field">
                            <div class="label">Client Signature</div>
                            <p>&nbsp;</p>
                            <p><strong>Name:</strong> <?php echo esc_html($form_data['full_name'] ?? '________________________'); ?></p>
                            <p><strong>Date:</strong> ________________________</p>
                        </div>
                        
                        <?php if (!empty($form_data['cosigner_full_name'])): ?>
                        <div class="signature-field">
                            <div class="label">Co-signer Signature</div>
                            <p>&nbsp;</p>
                            <p><strong>Name:</strong> <?php echo esc_html($form_data['cosigner_full_name']); ?></p>
                            <p><strong>Date:</strong> ________________________</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($footer_boilerplate)): ?>
                <div class="content-section">
                    <?php echo wpautop(wp_kses_post($footer_boilerplate)); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="page-footer">
                <div class="page-number">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
                <div class="firm-footer"><?php echo wp_kses_post($firm_footer); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
add_action('wp_footer', 'el_sync_pdf_with_tab3_changes', 999);

function el_sync_pdf_with_tab3_changes() {
    if (!is_page_template('engagement-letter-wizard.php')) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        console.log('🛒 Tab 3 to PDF Sync Active');
        
        var cartModified = false;
        
        // Hook into ALL cart modification events in Tab 3
        $(document).on('change', '.el-qty-update, .quantity input, .el-bundle-component, input[type="checkbox"], input[type="radio"]', function() {
            console.log('📦 Cart changed in Tab 3');
            cartModified = true;
        });
        
        // When Preview button is clicked after cart changes
        $(document).off('click', '#el-preview-pdf-btn').on('click', '#el-preview-pdf-btn', function(e) {
            e.preventDefault();
            console.log('🎯 Preview clicked - refreshing cart first');
            
            // Click Tab 4
            $('#brxe-ihqhkg').trigger('click');
            
            // Generate PDF with fresh cart
            setTimeout(function() {
                var $container = $('#el-pdf-preview-container');
                $container.html('<div style="text-align:center;padding:60px;"><div style="width:50px;height:50px;border:4px solid #e5e7eb;border-top:4px solid #3b82f6;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto;"></div><p style="margin-top:20px;">Loading updated cart...</p></div>');
                
                // Force cart refresh then generate PDF
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_refresh_cart_session',
                        nonce: '<?php echo wp_create_nonce('el_refresh'); ?>'
                    },
                    success: function() {
                        console.log('✅ Cart refreshed, generating PDF...');
                        
                        // Now generate PDF
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'el_generate_pdf_preview',
                                nonce: '<?php echo wp_create_nonce('el_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success && response.data && response.data.html) {
                                    $container.html(response.data.html);
                                } else {
                                    $container.html('<div style="text-align:center;padding:40px;"><p style="color:#dc2626;">Still no cart items detected</p><button onclick="location.reload()" style="margin-top:20px;padding:10px 24px;background:#3b82f6;color:white;border:none;border-radius:4px;">Reload Page</button></div>');
                                }
                            }
                        });
                    }
                });
            }, 800);
        });
        
        $('head').append('<style>@keyframes spin{to{transform:rotate(360deg)}}</style>');
    });
    </script>
    <?php
}

// PHP handler to refresh cart
add_action('wp_ajax_el_refresh_cart_session', 'el_handle_refresh_cart_session');
add_action('wp_ajax_nopriv_el_refresh_cart_session', 'el_handle_refresh_cart_session');



// Diagnostic to find the difference
add_action('wp_footer', 'el_diagnostic_page_vs_ajax', 999);

function el_diagnostic_page_vs_ajax() {
    if (!is_page_template('engagement-letter-wizard.php')) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Add diagnostic button to Tab 4
        $(document).on('click', '#brxe-ihqhkg', function() {
            setTimeout(function() {
                var $container = $('#el-pdf-preview-container');
                if ($container.length && $container.children().length === 0) {
                    $container.html(`
                        <div style="text-align:center;padding:40px;">
                            <button id="run-diagnostic" style="padding:12px 30px;background:#6366f1;color:white;border:none;border-radius:6px;margin:10px;">
                                🔍 Run Diagnostic
                            </button>
                            <div id="diagnostic-results" style="margin-top:20px;text-align:left;"></div>
                        </div>
                    `);
                }
            }, 500);
        });
        
        $(document).on('click', '#run-diagnostic', function() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_run_diagnostic',
                    nonce: '<?php echo wp_create_nonce('el_diagnostic'); ?>'
                },
                success: function(response) {
                    $('#diagnostic-results').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    console.log('Diagnostic:', response.data);
                }
            });
        });
    });
    </script>
    <?php
}

// Diagnostic handler
add_action('wp_ajax_el_run_diagnostic', 'el_handle_diagnostic');
add_action('wp_ajax_nopriv_el_run_diagnostic', 'el_handle_diagnostic');

function el_handle_diagnostic() {
    check_ajax_referer('el_diagnostic', 'nonce');
    
    // Check what's available
    $diagnostics = [
        'session_id' => session_id(),
        'session_started' => session_id() ? 'Yes' : 'No',
        'wc_loaded' => function_exists('WC') ? 'Yes' : 'No',
        'cart_null' => is_null(WC()->cart) ? 'Yes' : 'No',
        'cart_items' => 0,
        'user_id' => get_current_user_id(),
        'cookies' => $_COOKIE
    ];
    
    // Try to load cart
    if (function_exists('WC')) {
        if (is_null(WC()->cart)) {
            wc_load_cart();
        }
        if (WC()->cart) {
            WC()->cart->get_cart_from_session();
            $diagnostics['cart_items'] = count(WC()->cart->get_cart());
        }
    }
    
    wp_send_json_success($diagnostics);
}



// Remove old shortcode if it exists
remove_shortcode('el_pdf_export_auto');

// Simple Tab 5 Shortcode
add_shortcode('el_pdf_export_auto', 'el_tab5_simple_php');

function el_tab5_simple_php() {
    // Start session
    if (!session_id()) {
        session_start();
    }
    
    // Get PDF reference from session (set by Tab 4)
    $reference = isset($_SESSION['el_pdf_reference']) ? $_SESSION['el_pdf_reference'] : '';
    $pdf_data = null;
    $html_content = '';
    
    // Try to get PDF data
    if (!empty($reference)) {
        $pdf_data = get_transient('el_pdf_data_' . $reference);
    }
    
    // Check if user can edit
    $can_edit = current_user_can('edit_posts');
    
    ob_start();
    ?>  
    
    <div id="el-tab5-wrapper" style="width: 100%; padding: 20px;">
        
        <?php if (empty($pdf_data)): ?>
        
        <!-- No Document Found -->
        <div style="background: #fff7ed; border: 2px solid #fb923c; border-radius: 12px; padding: 40px; text-align: center;">
            <h2 style="color: #ea580c; margin: 0 0 20px 0;">📋 No Document Ready</h2>
            <p style="margin: 0 0 10px 0; font-size: 16px;">Please complete the previous steps first:</p>
            <ol style="text-align: left; max-width: 400px; margin: 20px auto; line-height: 2;">
                <li>Fill in client details (Tab 1)</li>
                <li>Select template (Tab 2)</li>
                <li>Customize services (Tab 3)</li>
                <li><strong>Generate preview (Tab 4)</strong></li>
            </ol>
            <p style="margin-top: 20px;">
                <button onclick="location.reload()" style="padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 15px; font-weight: 600;">
                    🔄 Check Again
                </button>
            </p>
        </div>
        
        <?php else: 
            
// RENDER THE PAPER ENGAGEMENT LETTER
$preview_file = get_stylesheet_directory() . '/preview-inline.php';

if (file_exists($preview_file)) {
    require_once $preview_file;
    
if (function_exists('el_render_engagement_letter_html')) {
    // Generate base HTML from template
    $html_content = el_render_engagement_letter_html($pdf_data);
    
    // Apply central pagination
    $result = el_apply_central_pagination($html_content);
    $html_content = $result['html'];
    $total_pages = $result['page_count'];
    
} else {
    $html_content = '<p style="color: red;">Template rendering function not found.</p>';
}
} else {
    $html_content = '<p style="color: red;">Template file not found: preview-inline.php</p>';
}
?>
        
        <!-- Toolbar -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2 style="margin: 0 0 5px 0; font-size: 20px; color: #1a1a2e;">📄 Paper Engagement Letter</h2>
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        Reference: <strong><?php echo esc_html($reference); ?></strong>
                        <?php if (isset($total_pages)): ?>
                         | Pages: <strong><?php echo $total_pages; ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        🖨️ Print
                    </button>
                    <?php if ($can_edit): ?>
                    <button id="save-edits-btn" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        💾 Save
                    </button>
                    <?php endif; ?>
                    <button onclick="location.reload()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        🔄 Refresh
                    </button>
                </div>
            </div>
            <?php if ($can_edit): ?>
            <p id="save-status" style="margin: 10px 0 0 0; font-size: 14px; color: #666;"></p>
            <?php endif; ?>
        </div>
        
        <!-- Paper Document -->
        <div id="paper-document" <?php echo $can_edit ? 'contenteditable="true"' : ''; ?> style="background: white; padding: 0; box-shadow: 0 0 20px rgba(0,0,0,0.1); font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.5; color: #000; min-height: 297mm;">
            <?php echo $html_content; ?>
        </div>
        
        <?php if ($can_edit): ?>
        <script>
        jQuery(document).ready(function($) {
            $('#save-edits-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#save-status');
                var editedContent = $('#paper-document').html();
                
                $btn.text('Saving...').prop('disabled', true);
                $status.text('');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'el_save_paper_edits',
                        reference: '<?php echo esc_js($reference); ?>',
                        content: editedContent,
                        nonce: '<?php echo wp_create_nonce('el_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.text('✅ Saved successfully!').css('color', '#10b981');
                            $btn.text('💾 Save').prop('disabled', false);
                        } else {
                            $status.text('❌ Error: ' + (response.data.message || 'Unknown error')).css('color', '#ef4444');
                            $btn.text('💾 Save').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $status.text('❌ Connection error').css('color', '#ef4444');
                        $btn.text('💾 Save').prop('disabled', false);
                    }
                });
            });
        });
console.log('Pagination applied:', <?php echo json_encode($total_pages); ?>);

        </script>
        <?php endif; ?>
        
        <?php endif; // end if pdf_data exists ?>
        
    </div>
    
<style>
    <style>
/* Minimal Tab 5 wrapper styles only */
#el-tab5-wrapper {
    width: 100%;
    max-width: 100%;
}

/* Print-specific overrides */
@media print {
    body * { 
        visibility: hidden; 
    }
    
    #paper-document, #paper-document * { 
        visibility: visible; 
    }
    
    #paper-document {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        width: auto !important;
    }
    
    #el-tab5-wrapper > *:not(#paper-document) { 
        display: none !important; 
    }
}
/* Visual page separation on screen */
@media screen {
    .el-page {
        background: white;
        margin: 20px auto;
        padding: 15mm;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
        page-break-after: always;
    }
    
    .el-page:first-child {
        margin-top: 0;
    }
    
    .el-page-signature {
        display: block !important;
        margin-top: 20mm;
        padding-top: 5mm;
        border-top: 1px solid #ccc;
        text-align: center;
        font-size: 10pt;
        color: #666;
    }
}
</style>
    
    <?php
    return ob_get_clean();
}


/**
 * Resume draft banner shortcode - Only shows on initial page load
 */
add_shortcode('el_resume_banner', 'el_resume_banner_shortcode');

function el_resume_banner_shortcode() {
    if (!session_id()) {
        session_start();
    }
    
    // Check if there's a saved engagement letter
    $engagement_letter_id = isset($_SESSION['el_engagement_letter_id']) ? intval($_SESSION['el_engagement_letter_id']) : 0;
    
    if (!$engagement_letter_id || get_post_type($engagement_letter_id) !== 'engagement_letter') {
        return ''; // No banner if no saved engagement letter
    }
    
    // Get saved data
    $el_data = el_get_engagement_letter($engagement_letter_id);
    
    if (!$el_data || $el_data['status'] !== 'draft') {
        return ''; // Not a draft or doesn't exist
    }
    
    // Get saved tab position and last active time
    $saved_tab = get_post_meta($engagement_letter_id, '_el_current_tab', true) ?: 1;
    $last_active = get_post_meta($engagement_letter_id, '_el_last_active', true);
    
    // Generate tab-specific context message
    $tab_messages = array(
        1 => 'adding client details',
        2 => 'selecting a template',
        3 => 'customizing services',
        4 => 'reviewing the document',
        5 => 'finalizing the letter'
    );
    
    $context_message = isset($tab_messages[$saved_tab]) ? $tab_messages[$saved_tab] : 'creating an engagement letter';
    
    // Format last active time
    $time_ago = '';
    if ($last_active) {
        $time_diff = human_time_diff(strtotime($last_active), current_time('timestamp'));
        $time_ago = ' (' . $time_diff . ' ago)';
    }
    
    ob_start();
    ?>
    <div class="el-resume-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 24px; margin-bottom: 30px; box-shadow: 0 4px 24px rgba(102, 126, 234, 0.25); animation: el-slide-down 0.4s ease-out;">
        <style>
        @keyframes el-slide-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .el-resume-banner-inner {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
        }
        
        .el-resume-icon {
            font-size: 48px;
            line-height: 1;
        }
        
        .el-resume-content {
            color: #ffffff;
        }
        
        .el-resume-title {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
        }
        
        .el-resume-details {
            margin: 0;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .el-resume-actions {
            display: flex;
            gap: 12px;
        }
        
        .el-resume-btn {
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .el-btn-resume-draft {
            background: #ffffff;
            color: #667eea;
        }
        
        .el-btn-resume-draft:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .el-btn-start-fresh {
            background: transparent;
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .el-btn-start-fresh:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 768px) {
            .el-resume-banner-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .el-resume-actions {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
        </style>
        
        <div class="el-resume-banner-inner">
            <div class="el-resume-icon">📋</div>
            <div class="el-resume-content">
                <h3 class="el-resume-title">Continue Your Engagement Letter</h3>
                <p class="el-resume-details">
                    You were <strong><?php echo esc_html($context_message); ?></strong><?php echo esc_html($time_ago); ?>
                </p>
            </div>
            <div class="el-resume-actions">
                <button type="button" class="el-resume-btn el-btn-resume-draft" data-engagement-id="<?php echo esc_attr($engagement_letter_id); ?>" data-saved-tab="<?php echo esc_attr($saved_tab); ?>">
                    Continue →
                </button>
                <button type="button" class="el-resume-btn el-btn-start-fresh">
                    Start Fresh
                </button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var bannerDismissed = false;
        
        // Resume draft
        $('.el-btn-resume-draft').on('click', function() {
            var $btn = $(this);
            var engagementId = $btn.data('engagement-id');
            var savedTab = $btn.data('saved-tab');
            
            $btn.text('Loading...').prop('disabled', true);
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_resume_draft',
                    engagement_id: engagementId,
                    nonce: '<?php echo wp_create_nonce('el_resume_draft'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Draft resumed:', response.data);
                        
                        // Hide banner
                        $('.el-resume-banner').fadeOut(300);
                        bannerDismissed = true;
                        
                        // Populate Tab 1 form if we have data
                        if (response.data.form_data) {
                            var fd = response.data.form_data;
                            $('#input_1_1_3').val(fd.first_name || '');
                            $('#input_1_1_6').val(fd.last_name || '');
                            $('#input_1_2').val(fd.email || '');
                            $('#input_1_5').val(fd.phone || '');
                            $('#input_1_6_1').val(fd.street_address || '');
                            $('#input_1_6_3').val(fd.city || '');
                            $('#input_1_6_4').val(fd.state || '');
                            $('#input_1_6_5').val(fd.zip || '');
                            $('#input_1_6_6').val(fd.country || '');
                            $('#input_1_7').val(fd.notes || '');
                        }
                        
                        // Navigate to the exact saved tab
                        var resumeTab = response.data.saved_tab || savedTab || 1;
                        var tabMap = {
                            1: '#brxe-kjwfkc',
                            2: '#brxe-caqeqv',
                            3: '#brxe-mhedar',
                            4: '#brxe-ihqhkg',
                            5: '#brxe-zmmopw'
                        };
                        
                        var $tab = $(tabMap[resumeTab]);
                        if ($tab.length) {
                            setTimeout(function() {
                                $tab.click();
                            }, 500);
                        }
                    } else {
                        alert('Error resuming draft: ' + (response.data.message || 'Unknown error'));
                        $btn.text('Continue →').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.text('Continue →').prop('disabled', false);
                }
            });
        });
        
        // Start fresh
        $('.el-btn-start-fresh').on('click', function() {
            if (!confirm('Are you sure? Your draft will be permanently deleted.')) {
                return;
            }
            
            var $btn = $(this);
            $btn.text('Clearing...').prop('disabled', true);
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'el_start_fresh',
                    nonce: '<?php echo wp_create_nonce('el_start_fresh'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Started fresh');
                        $('.el-resume-banner').fadeOut(300, function() {
                            $(this).remove();
                        });
                        bannerDismissed = true;
                        
                        // Clear form fields
                        $('.gform_wrapper input[type="text"], .gform_wrapper input[type="email"], .gform_wrapper textarea').val('');
                        
                        // Navigate to Tab 1
                        setTimeout(function() {
                            $('#brxe-kjwfkc').click();
                        }, 400);
                    } else {
                        alert('Error: ' + (response.data.message || 'Unknown error'));
                        $btn.text('Start Fresh').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.text('Start Fresh').prop('disabled', false);
                }
            });
        });
        
        // Auto-hide banner on any wizard interaction
        $(document).on('click', '.brxe-tab, .el-tab-nav, .gform_button, .el-select-template-btn, [data-tab]', function() {
            if (!bannerDismissed) {
                $('.el-resume-banner').fadeOut(300);
                bannerDismissed = true;
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Simple session management fixes
 */
add_action('init', 'el_ensure_session', 1);
function el_ensure_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}

/**
 * Debug helper - Add this temporarily to check what's happening
 */
add_shortcode('el_debug_info', 'el_show_debug_info');
function el_show_debug_info() {
    if (!current_user_can('manage_options')) {
        return 'Debug info only visible to admins';
    }
    
    if (!session_id()) {
        session_start();
    }
    
    $info = [
        'Session ID' => session_id() ? 'Active' : 'Not active',
        'PDF Reference' => $_SESSION['el_pdf_reference'] ?? 'Not set',
        'jQuery Version' => 'Check console',
        'PHP Version' => PHP_VERSION,
        'WordPress Version' => get_bloginfo('version'),
        'Active Theme' => get_template(),
        'Memory Limit' => ini_get('memory_limit'),
    ];
    
    $output = '<div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; font-family: monospace;">';
    $output .= '<h3>🔍 Debug Information</h3>';
    
    foreach ($info as $key => $value) {
        $output .= '<p><strong>' . $key . ':</strong> ' . esc_html($value) . '</p>';
    }
    
    // Check for transients
    if (!empty($_SESSION['el_pdf_reference'])) {
        $pdf_data = get_transient('el_pdf_data_' . $_SESSION['el_pdf_reference']);
        $output .= '<p><strong>PDF Data:</strong> ' . ($pdf_data ? 'Found' : 'Not found') . '</p>';
    }
    
    $output .= '</div>';
    
    // Add jQuery version check
    $output .= '<script>
    if (typeof jQuery !== "undefined") {
        console.log("jQuery Version: " + jQuery.fn.jquery);
    } else {
        console.log("jQuery is not loaded!");
    }
    </script>';
    
    return $output;
}