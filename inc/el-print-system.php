<?php
/**
 * Engagement Letter Print PDF System - Main Loader
 * 
 * Integrates all components for print-ready PDF generation with encryption
 * and secure viewing capabilities.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * INSTALLATION:
 *   1. Add all class files to /wp-content/themes/bricks-child/inc/
 *   2. Add encryption key to wp-config.php:
 *      define('EL_ENCRYPTION_KEY', 'base64:your-32-byte-key');
 *   3. Include this file in functions.php:
 *      require_once get_stylesheet_directory() . '/inc/el-print-system.php';
 * 
 * COMPONENTS:
 *   - EL_Print_Data_Assembler: Collects PDF-layer ACF data
 *   - EL_Content_Blocks: Block types with height estimation
 *   - EL_Page_Break_Engine: Pagination rules engine
 *   - EL_MPDF_Generator: mPDF integration via Gravity PDF
 *   - EL_PDF_Encryption: AES-256-GCM encryption
 *   - EL_Secure_Viewer: Token-based secure access
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Print System Class
 */
class EL_Print_System {
    
    /**
     * System version
     */
    const VERSION = '1.0.0';
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Components loaded flag
     */
    private $components_loaded = false;
    
    /**
     * Get singleton instance
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_components();
        $this->init_hooks();
    }
    
    /**
     * Load all component classes
     */
    private function load_components() {
        $inc_dir = get_stylesheet_directory() . '/inc/';
        
        $components = [
            'class-el-print-data-assembler.php',
            'class-el-content-blocks.php',
            'class-el-page-break-engine.php',
            'class-el-mpdf-generator.php',
            'class-el-pdf-encryption.php',
            'class-el-secure-viewer.php',
            'class-el-paginated-viewer.php',
            'class-el-preview-viewer.php',
        ];
        
        foreach ($components as $file) {
            $path = $inc_dir . $file;
            if (file_exists($path)) {
                require_once $path;
            } else {
                error_log('EL Print System: Missing component - ' . $file);
            }
        }
        
        $this->components_loaded = true;
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // AJAX handlers for authenticated users
        add_action('wp_ajax_el_generate_print_pdf', [$this, 'ajax_generate_print_pdf']);
        add_action('wp_ajax_el_download_print_pdf', [$this, 'ajax_download_print_pdf']);
        add_action('wp_ajax_el_preview_print_pdf', [$this, 'ajax_preview_print_pdf']);
        add_action('wp_ajax_el_create_secure_link', [$this, 'ajax_create_secure_link']);
        add_action('wp_ajax_el_get_pdf_status', [$this, 'ajax_get_pdf_status']);
        
        // Admin initialization
        add_action('admin_init', [$this, 'maybe_create_tables']);
        
        // Cron for cleanup
        add_action('el_daily_cleanup', [$this, 'daily_cleanup']);
        
        if (!wp_next_scheduled('el_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'el_daily_cleanup');
        }
        
        // Enqueue scripts for print editor
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        if (!$this->is_el_page()) {
            return;
        }
        
        wp_enqueue_script(
            'el-print-system',
            get_stylesheet_directory_uri() . '/js/el-print-system.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_enqueue_script(
            'el-preview-viewer',
            get_stylesheet_directory_uri() . '/js/el-preview-viewer.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_localize_script('el-print-system', 'elPrintSystem', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('el_print_system'),
            'strings' => [
                'generating' => __('Generating PDF...', 'el'),
                'encrypting' => __('Securing document...', 'el'),
                'complete' => __('PDF ready!', 'el'),
                'error' => __('An error occurred. Please try again.', 'el'),
            ],
        ]);
    }
    
    /**
     * Check if current page is engagement letter related
     */
    private function is_el_page() {
        // Check for EL wizard page or shortcode
        global $post;
        
        if (!$post) {
            return false;
        }
        
        return has_shortcode($post->post_content, 'el_print_editor') ||
               has_shortcode($post->post_content, 'el_wizard') ||
               is_page('engagement-letter');
    }
    
    /**
     * Create database tables
     */
    public function maybe_create_tables() {
        if (class_exists('EL_PDF_Encryption')) {
            EL_PDF_Encryption::maybe_create_table();
        }
        
        if (class_exists('EL_Secure_Viewer')) {
            EL_Secure_Viewer::maybe_create_tables();
        }
    }
    
    /**
     * Daily cleanup task
     */
    public function daily_cleanup() {
        // Clean expired encrypted documents
        if (class_exists('EL_PDF_Encryption')) {
            $deleted_docs = EL_PDF_Encryption::cleanup_expired();
            error_log('EL Print System: Cleaned ' . $deleted_docs . ' expired documents');
        }
        
        // Clean expired tokens
        if (class_exists('EL_Secure_Viewer')) {
            $deleted_tokens = EL_Secure_Viewer::cleanup_expired_tokens();
            error_log('EL Print System: Cleaned ' . $deleted_tokens . ' expired tokens');
        }
        
        // Clean temp files
        if (class_exists('EL_MPDF_Generator')) {
            $deleted_temp = EL_MPDF_Generator::cleanup_temp_files(24);
            error_log('EL Print System: Cleaned ' . $deleted_temp . ' temp files');
        }
    }
    
    /**
     * AJAX: Generate print PDF
     */
    public function ajax_generate_print_pdf() {
        check_ajax_referer('el_print_system', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        // Get options from request
        $include_user_data = isset($_POST['include_user_data']) ? $_POST['include_user_data'] === 'true' : true;
        $include_signatures = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
        $signature_format = sanitize_text_field($_POST['signature_format'] ?? 'Client signature …………..……………………… Date ………… Page %d/%d');
        $encrypt = isset($_POST['encrypt']) ? $_POST['encrypt'] === 'true' : true;
        
        // Step 1: Assemble data
        $data = EL_Print_Data_Assembler::assemble_data($include_user_data);
        
        if (is_wp_error($data)) {
            wp_send_json_error(['message' => $data->get_error_message()]);
        }
        
        // Step 2: Check mPDF availability
        if (!EL_MPDF_Generator::is_mpdf_available()) {
            wp_send_json_error(['message' => 'PDF generator not available. Please ensure Gravity PDF is installed.']);
        }
        
        // Step 3: Generate PDF
        $generator = new EL_MPDF_Generator();
        $result = $generator->generate_from_data($data, $include_signatures, $signature_format);
        
        if (!$result['success']) {
            wp_send_json_error(['message' => $result['error']]);
        }
        
        $response = [
            'reference' => $data['meta']['reference'],
            'total_pages' => $result['total_pages'],
            'generated_at' => current_time('c'),
        ];
        
        // Step 4: Encrypt and store (if requested)
        if ($encrypt && EL_PDF_Encryption::is_configured()) {
            $pdf_content = file_get_contents($result['pdf_path']);
            
            $storage = EL_PDF_Encryption::store_pdf(
                $pdf_content,
                $data['meta']['reference'],
                [
                    'client_id' => $data['client']['id'],
                    'client_email' => $data['client']['email'],
                    'lawyer_id' => $data['lawyer']['id'],
                    'include_user_data' => $include_user_data,
                    'include_signatures' => $include_signatures,
                    'total_pages' => $result['total_pages'],
                ]
            );
            
            if ($storage) {
                $response['file_id'] = $storage['file_id'];
                $response['encrypted'] = true;
                $response['expires_at'] = $storage['expires_at'];
                
                // Delete unencrypted file
                EL_MPDF_Generator::delete_pdf($result['pdf_path']);
            } else {
                // Keep unencrypted as fallback
                $response['pdf_path'] = $result['pdf_path'];
                $response['encrypted'] = false;
            }
        } else {
            $response['pdf_path'] = $result['pdf_path'];
            $response['encrypted'] = false;
            
            if (!EL_PDF_Encryption::is_configured()) {
                $response['encryption_warning'] = 'Encryption not configured. Document stored unencrypted.';
            }
        }
        
        // Store reference in session for download
        if (!session_id()) {
            session_start();
        }
        $_SESSION['el_print_reference'] = $data['meta']['reference'];
        $_SESSION['el_print_file_id'] = $response['file_id'] ?? null;
        $_SESSION['el_print_pdf_path'] = $response['pdf_path'] ?? null;
        
        // Save to engagement letter post if exists
        $el_post_id = $_SESSION['el_engagement_letter_id'] ?? 0;
        if ($el_post_id) {
            update_post_meta($el_post_id, '_el_print_reference', $data['meta']['reference']);
            update_post_meta($el_post_id, '_el_print_file_id', $response['file_id'] ?? '');
            update_post_meta($el_post_id, '_el_print_generated', current_time('mysql'));
            update_post_meta($el_post_id, '_el_print_pages', $result['total_pages']);
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * AJAX: Download print PDF
     */
    public function ajax_download_print_pdf() {
        check_ajax_referer('el_print_system', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        $reference = sanitize_text_field($_GET['reference'] ?? '');
        $file_id = sanitize_text_field($_GET['file_id'] ?? '');
        
        // Try to get from encrypted storage first
        if ($file_id && EL_PDF_Encryption::is_configured()) {
            $pdf_content = EL_PDF_Encryption::retrieve_pdf($file_id);
            
            if ($pdf_content) {
                $filename = 'engagement-letter-' . ($reference ?: $file_id) . '.pdf';
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($pdf_content));
                header('Cache-Control: no-cache, no-store, must-revalidate');
                
                echo $pdf_content;
                exit;
            }
        }
        
        // Try session fallback
        if (!session_id()) {
            session_start();
        }
        
        $pdf_path = $_SESSION['el_print_pdf_path'] ?? '';
        
        if ($pdf_path && file_exists($pdf_path)) {
            $filename = 'engagement-letter-' . $reference . '.pdf';
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($pdf_path));
            
            readfile($pdf_path);
            exit;
        }
        
        wp_die('Document not found or expired');
    }
    
    /**
     * AJAX: Preview print PDF (inline)
     */
    public function ajax_preview_print_pdf() {
        check_ajax_referer('el_print_system', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $include_user_data = isset($_POST['include_user_data']) ? $_POST['include_user_data'] === 'true' : true;
        $include_signatures = isset($_POST['paper_only']) ? $_POST['paper_only'] === 'true' : false;
        
        // Assemble data
        $data = EL_Print_Data_Assembler::assemble_data($include_user_data);
        
        if (is_wp_error($data)) {
            wp_send_json_error(['message' => $data->get_error_message()]);
        }
        
        // Create blocks and paginate
        $blocks = EL_Content_Blocks::create_blocks_from_data($data, $include_signatures);
        $engine = new EL_Page_Break_Engine($include_signatures);
        $paginated = $engine->paginate($blocks);
        
        // Render HTML preview
        $html = $engine->render_html($paginated);
        $css = EL_Page_Break_Engine::get_print_css();
        
        wp_send_json_success([
            'html' => $html,
            'css' => $css,
            'total_pages' => $paginated['total_pages'],
            'stats' => $paginated['stats'],
            'reference' => $data['meta']['reference'],
        ]);
    }
    
    /**
     * AJAX: Create secure viewing link
     */
    public function ajax_create_secure_link() {
        check_ajax_referer('el_print_system', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $file_id = sanitize_text_field($_POST['file_id'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $expiry_days = intval($_POST['expiry_days'] ?? 14);
        $can_print = isset($_POST['can_print']) ? $_POST['can_print'] === 'true' : true;
        $can_download = isset($_POST['can_download']) ? $_POST['can_download'] === 'true' : true;
        
        if (empty($file_id)) {
            wp_send_json_error(['message' => 'File ID required']);
        }
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'Valid email required']);
        }
        
        // Generate token
        $token_data = EL_Secure_Viewer::generate_access_token(
            $file_id,
            $email,
            $expiry_days,
            [
                'can_print' => $can_print,
                'can_download' => $can_download,
                'show_watermark' => true,
            ]
        );
        
        if (!$token_data) {
            wp_send_json_error(['message' => 'Failed to create secure link']);
        }
        
        wp_send_json_success($token_data);
    }
    
    /**
     * AJAX: Get PDF status
     */
    public function ajax_get_pdf_status() {
        check_ajax_referer('el_print_system', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $reference = sanitize_text_field($_POST['reference'] ?? '');
        
        if (empty($reference)) {
            wp_send_json_error(['message' => 'Reference required']);
        }
        
        // Check encrypted storage
        $pdf_content = EL_PDF_Encryption::retrieve_pdf($reference, true);
        
        if ($pdf_content) {
            wp_send_json_success([
                'exists' => true,
                'encrypted' => true,
                'reference' => $reference,
            ]);
        }
        
        wp_send_json_success([
            'exists' => false,
            'reference' => $reference,
        ]);
    }
    
    /**
     * Check system status
     */
    public static function get_system_status() {
        return [
            'version' => self::VERSION,
            'components' => [
                'data_assembler' => class_exists('EL_Print_Data_Assembler'),
                'content_blocks' => class_exists('EL_Content_Blocks'),
                'page_break_engine' => class_exists('EL_Page_Break_Engine'),
                'mpdf_generator' => class_exists('EL_MPDF_Generator'),
                'pdf_encryption' => class_exists('EL_PDF_Encryption'),
                'secure_viewer' => class_exists('EL_Secure_Viewer'),
                'paginated_viewer' => class_exists('EL_Paginated_Viewer'),
                'preview_viewer' => class_exists('EL_Preview_Viewer'),
            ],
            'mpdf_available' => class_exists('EL_MPDF_Generator') ? EL_MPDF_Generator::is_mpdf_available() : false,
            'encryption_configured' => class_exists('EL_PDF_Encryption') ? EL_PDF_Encryption::is_configured() : false,
            'imagick_available' => class_exists('Imagick'),
            'gravity_pdf_active' => class_exists('GFPDF_Core'),
            'woocommerce_active' => class_exists('WooCommerce'),
        ];
    }
    
    /**
     * Generate key helper (for setup)
     */
    public static function generate_encryption_key() {
        if (class_exists('EL_PDF_Encryption')) {
            return EL_PDF_Encryption::generate_key();
        }
        return 'base64:' . base64_encode(random_bytes(32));
    }
}

/**
 * Initialize the print system
 */
function el_print_system() {
    return EL_Print_System::instance();
}

// Initialize on plugins loaded
add_action('plugins_loaded', 'el_print_system', 20);

/**
 * Activation hook - create tables
 */
function el_print_system_activate() {
    $system = el_print_system();
    $system->maybe_create_tables();
}
register_activation_hook(__FILE__, 'el_print_system_activate');

/**
 * Admin notice if encryption not configured
 */
add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only show on relevant pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, ['dashboard', 'edit-engagement_letter', 'engagement_letter'])) {
        return;
    }
    
    if (class_exists('EL_PDF_Encryption') && !EL_PDF_Encryption::is_configured()) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Engagement Letter System:</strong> 
                PDF encryption is not configured. Add <code>EL_ENCRYPTION_KEY</code> to wp-config.php for secure document storage.
                <br>
                <small>Generate a key: <code>&lt;?php echo EL_Print_System::generate_encryption_key(); ?&gt;</code></small>
            </p>
        </div>
        <?php
    }
    
    if (class_exists('EL_MPDF_Generator') && !EL_MPDF_Generator::is_mpdf_available()) {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>Engagement Letter System:</strong> 
                PDF generation unavailable. Please install and activate <a href="https://gravitypdf.com/" target="_blank">Gravity PDF</a>.
            </p>
        </div>
        <?php
    }
    
    if (!class_exists('Imagick')) {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Engagement Letter System:</strong> 
                Imagick extension not installed. Page-by-page preview will not be available. 
                <br><small>Install Imagick for PHP to enable server-side page rendering.</small>
            </p>
        </div>
        <?php
    }
});

/**
 * Shortcode: Print PDF button
 * Usage: [el_print_pdf_button]
 */
add_shortcode('el_print_pdf_button', function($atts) {
    if (!current_user_can('edit_posts')) {
        return '';
    }
    
    $atts = shortcode_atts([
        'label' => 'Generate Print PDF',
        'paper_only' => 'true',
        'class' => 'button button-primary',
    ], $atts);
    
    ob_start();
    ?>
    <button 
        type="button" 
        class="el-generate-print-pdf <?php echo esc_attr($atts['class']); ?>"
        data-paper-only="<?php echo esc_attr($atts['paper_only']); ?>"
    >
        <?php echo esc_html($atts['label']); ?>
    </button>
    <div class="el-print-pdf-status" style="display:none; margin-top:10px;"></div>
    <?php
    return ob_get_clean();
});

/**
 * Shortcode: System status (admin only)
 * Usage: [el_print_system_status]
 */
add_shortcode('el_print_system_status', function() {
    if (!current_user_can('manage_options')) {
        return '<p>Access denied.</p>';
    }
    
    $status = EL_Print_System::get_system_status();
    
    ob_start();
    ?>
    <div class="el-system-status" style="font-family: monospace; background: #f5f5f5; padding: 15px; border-radius: 4px;">
        <h4 style="margin-top:0;">EL Print System Status</h4>
        <p><strong>Version:</strong> <?php echo esc_html($status['version']); ?></p>
        
        <p><strong>Components:</strong></p>
        <ul style="list-style: none; padding-left: 0;">
            <?php foreach ($status['components'] as $name => $loaded): ?>
            <li>
                <?php echo $loaded ? '✅' : '❌'; ?>
                <?php echo esc_html(ucwords(str_replace('_', ' ', $name))); ?>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <p><strong>Dependencies:</strong></p>
        <ul style="list-style: none; padding-left: 0;">
            <li><?php echo $status['gravity_pdf_active'] ? '✅' : '❌'; ?> Gravity PDF</li>
            <li><?php echo $status['woocommerce_active'] ? '✅' : '❌'; ?> WooCommerce</li>
            <li><?php echo $status['mpdf_available'] ? '✅' : '❌'; ?> mPDF Library</li>
            <li><?php echo $status['imagick_available'] ? '✅' : '❌'; ?> Imagick (for page rendering)</li>
            <li><?php echo $status['encryption_configured'] ? '✅' : '❌'; ?> Encryption Key</li>
        </ul>
        
        <?php if (!$status['encryption_configured']): ?>
        <p style="background: #fff3cd; padding: 10px; border-radius: 4px;">
            <strong>Setup Required:</strong> Add to wp-config.php:<br>
            <code>define('EL_ENCRYPTION_KEY', '<?php echo esc_html(EL_Print_System::generate_encryption_key()); ?>');</code>
        </p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});