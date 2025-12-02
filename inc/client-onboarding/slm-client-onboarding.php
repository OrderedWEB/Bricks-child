<?php
/**
 * SLM Client Onboarding System
 * 
 * Complete client onboarding workflow:
 * - Lawyer searches and selects client
 * - Magic link sent to client email (24hr expiry)
 * - Client signs terms agreement
 * - Password setup and WooCommerce customer upgrade
 * - Signed document stored in encrypted DMS
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Client Onboarding System Class
 */
class SLM_Client_Onboarding {
    
    /**
     * System version
     */
    const VERSION = '1.0.0';
    
    /**
     * Database version for migrations
     */
    const DB_VERSION = '1.0.0';
    
    /**
     * Option name for DB version tracking
     */
    const DB_VERSION_OPTION = 'slm_onboarding_db_version';
    
    /**
     * Singleton instance
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
        $this->define_constants();
        $this->check_database();
        $this->load_components();
        $this->init_hooks();
    }
    
    /**
     * Define system constants
     */
    private function define_constants() {
        // Paths
        if (!defined('SLM_ONBOARDING_PATH')) {
            define('SLM_ONBOARDING_PATH', get_stylesheet_directory() . '/inc/client-onboarding/');
        }
        
        if (!defined('SLM_ONBOARDING_URL')) {
            define('SLM_ONBOARDING_URL', get_stylesheet_directory_uri() . '/inc/client-onboarding/');
        }
        
        // Magic link settings
        if (!defined('SLM_MAGIC_LINK_EXPIRY_HOURS')) {
            define('SLM_MAGIC_LINK_EXPIRY_HOURS', 24);
        }
        
        // Document settings
        if (!defined('SLM_TERMS_DOC_TYPE')) {
            define('SLM_TERMS_DOC_TYPE', 'terms_agreement');
        }
        
        // Debug mode
        if (!defined('SLM_ONBOARDING_DEBUG')) {
            define('SLM_ONBOARDING_DEBUG', WP_DEBUG);
        }
    }
    
    /**
     * Check and create/update database tables
     */
    private function check_database() {
        $installed_version = get_option(self::DB_VERSION_OPTION, '0');
        
        if (version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->create_tables();
            update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
        }
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Documents table
        $table_documents = $wpdb->prefix . 'slm_documents';
        
        $sql_documents = "CREATE TABLE $table_documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            case_id BIGINT UNSIGNED NULL,
            folder_id BIGINT UNSIGNED NULL,
            document_type VARCHAR(50) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT UNSIGNED NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            encryption_iv VARCHAR(64) NOT NULL,
            encryption_tag VARCHAR(64) NOT NULL,
            file_hash VARCHAR(128) NOT NULL,
            version INT UNSIGNED DEFAULT 1,
            is_signed TINYINT(1) DEFAULT 0,
            signed_by BIGINT UNSIGNED NULL,
            signed_at DATETIME NULL,
            signing_ip VARCHAR(45) NULL,
            signing_user_agent TEXT NULL,
            signing_method VARCHAR(20) NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_user (user_id),
            KEY idx_case (case_id),
            KEY idx_folder (folder_id),
            KEY idx_type (document_type),
            KEY idx_signed (is_signed),
            KEY idx_deleted (deleted_at)
        ) $charset_collate;";
        
        // Document access log table
        $table_access_log = $wpdb->prefix . 'slm_document_access_log';
        
        $sql_access_log = "CREATE TABLE $table_access_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            access_type VARCHAR(20) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            access_token VARCHAR(64) NULL,
            pages_viewed TEXT NULL,
            view_duration INT UNSIGNED NULL,
            accessed_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_document (document_id),
            KEY idx_user (user_id),
            KEY idx_type (access_type),
            KEY idx_accessed (accessed_at)
        ) $charset_collate;";
        
        // Client folders table
        $table_folders = $wpdb->prefix . 'slm_client_folders';
        
        $sql_folders = "CREATE TABLE $table_folders (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            case_id BIGINT UNSIGNED NULL,
            parent_id BIGINT UNSIGNED NULL,
            folder_name VARCHAR(255) NOT NULL,
            folder_slug VARCHAR(255) NOT NULL,
            folder_type VARCHAR(50) DEFAULT 'custom',
            sort_order INT UNSIGNED DEFAULT 0,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_user (user_id),
            KEY idx_case (case_id),
            KEY idx_parent (parent_id),
            KEY idx_type (folder_type)
        ) $charset_collate;";
        
        // Magic link tokens table
        $table_magic_links = $wpdb->prefix . 'slm_magic_links';
        
        $sql_magic_links = "CREATE TABLE $table_magic_links (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            purpose VARCHAR(50) NOT NULL DEFAULT 'onboarding',
            created_by BIGINT UNSIGNED NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            used_ip VARCHAR(45) NULL,
            used_user_agent TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY idx_token (token_hash),
            KEY idx_user (user_id),
            KEY idx_purpose (purpose),
            KEY idx_expires (expires_at),
            KEY idx_used (used_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_documents);
        dbDelta($sql_access_log);
        dbDelta($sql_folders);
        dbDelta($sql_magic_links);
        
        // Create uploads directory structure
        $this->create_upload_directories();
        
        if (SLM_ONBOARDING_DEBUG) {
            error_log('SLM Onboarding: Database tables created/updated to version ' . self::DB_VERSION);
        }
    }
    
    /**
     * Create secure upload directories
     */
    private function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/private/slm-documents';
        
        // Create directory structure
        $directories = [
            $base_path,
            $base_path . '/terms',
            $base_path . '/engagement-letters',
            $base_path . '/identity',
            $base_path . '/correspondence',
            $base_path . '/temp',
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
        
        // Create .htaccess to block direct access
        $htaccess_path = $base_path . '/.htaccess';
        if (!file_exists($htaccess_path)) {
            $htaccess_content = "# Deny direct access to all files\n";
            $htaccess_content .= "Order Deny,Allow\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "\n";
            $htaccess_content .= "# Block directory listing\n";
            $htaccess_content .= "Options -Indexes\n";
            
            file_put_contents($htaccess_path, $htaccess_content);
        }
        
        // Create index.php to prevent directory listing
        $index_path = $base_path . '/index.php';
        if (!file_exists($index_path)) {
            file_put_contents($index_path, '<?php // Silence is golden');
        }
    }
    
    /**
     * Load component classes
     */
    private function load_components() {
        $components = [
            'class-slm-client-search.php',
            'class-slm-magic-link.php',
            'class-slm-onboarding-flow.php',
            'class-slm-terms-agreement.php',
            'class-slm-signature-handler.php',
            'class-slm-document-storage.php',
            'class-slm-woo-customer.php',
            'class-slm-onboarding-settings.php',
            'class-slm-email-templates.php',
        ];
        
        foreach ($components as $file) {
            $path = SLM_ONBOARDING_PATH . $file;
            if (file_exists($path)) {
                require_once $path;
            } else {
                if (SLM_ONBOARDING_DEBUG) {
                    error_log('SLM Onboarding: Missing component - ' . $file);
                }
            }
        }
        
        $this->components_loaded = true;
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components after WordPress is fully loaded
        add_action('init', [$this, 'init_components'], 5);
        
        // Register shortcodes
        add_action('init', [$this, 'register_shortcodes']);
        
        // Admin menu
        add_action('admin_menu', [$this, 'register_admin_menu']);
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_slm_search_clients', [$this, 'ajax_search_clients']);
        add_action('wp_ajax_slm_get_client_details', [$this, 'ajax_get_client_details']);
        add_action('wp_ajax_slm_send_magic_link', [$this, 'ajax_send_magic_link']);
        
// Frontend AJAX (for onboarding flow)
add_action('wp_ajax_nopriv_slm_validate_magic_link', [$this, 'ajax_validate_magic_link']);
add_action('wp_ajax_nopriv_slm_submit_terms_signature', [$this, 'ajax_submit_terms_signature']);
add_action('wp_ajax_nopriv_slm_set_password', [$this, 'ajax_set_password']);

// Also register for logged-in users (testing/admin)
add_action('wp_ajax_slm_validate_magic_link', [$this, 'ajax_validate_magic_link']);
add_action('wp_ajax_slm_submit_terms_signature', [$this, 'ajax_submit_terms_signature']);
add_action('wp_ajax_slm_set_password', [$this, 'ajax_set_password']);
    }
    
    /**
     * Initialize component classes
     */
    public function init_components() {
        if (class_exists('SLM_Client_Search')) {
            SLM_Client_Search::init();
        }
        
        if (class_exists('SLM_Magic_Link')) {
            SLM_Magic_Link::init();
        }
        
        if (class_exists('SLM_Onboarding_Flow')) {
            SLM_Onboarding_Flow::init();
        }
        
        if (class_exists('SLM_Terms_Agreement')) {
            SLM_Terms_Agreement::init();
        }
        
        if (class_exists('SLM_Signature_Handler')) {
            SLM_Signature_Handler::init();
        }
        
        if (class_exists('SLM_Document_Storage')) {
            SLM_Document_Storage::init();
        }
        
        if (class_exists('SLM_Woo_Customer')) {
            SLM_Woo_Customer::init();
        }
        
        if (class_exists('SLM_Onboarding_Settings')) {
            SLM_Onboarding_Settings::init();
        }
        
        if (class_exists('SLM_Email_Templates')) {
            SLM_Email_Templates::init();
        }
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('slm_client_search', [$this, 'shortcode_client_search']);
        add_shortcode('slm_onboarding_flow', [$this, 'shortcode_onboarding_flow']);
    }
    
    /**
     * Client search shortcode callback
     */
    public function shortcode_client_search($atts) {
        if (!current_user_can('edit_users')) {
            return '<p>' . esc_html__('You do not have permission to access this feature.', 'flavor') . '</p>';
        }
        
        if (class_exists('SLM_Client_Search')) {
            return SLM_Client_Search::render_search_interface();
        }
        
        return '<p>' . esc_html__('Client search component not loaded.', 'flavor') . '</p>';
    }
    
    /**
     * Onboarding flow shortcode callback
     */
    public function shortcode_onboarding_flow($atts) {
        if (class_exists('SLM_Onboarding_Flow')) {
            return SLM_Onboarding_Flow::render_flow();
        }
        
        return '<p>' . esc_html__('Onboarding component not loaded.', 'flavor') . '</p>';
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu() {
        add_menu_page(
            __('Client Onboarding', 'flavor'),
            __('Client Onboarding', 'flavor'),
            'edit_users',
            'slm-client-onboarding',
            [$this, 'render_admin_page'],
            'dashicons-id-alt',
            30
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('edit_users')) {
            wp_die(__('You do not have permission to access this page.', 'flavor'));
        }
        
        if (class_exists('SLM_Client_Search')) {
            SLM_Client_Search::render_admin_page();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('Client Onboarding', 'flavor') . '</h1>';
            echo '<p>' . esc_html__('Client search component not loaded.', 'flavor') . '</p></div>';
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_slm-client-onboarding') {
            return;
        }
        
        wp_enqueue_style(
            'slm-client-onboarding-admin',
            SLM_ONBOARDING_URL . 'assets/css/admin.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'slm-client-onboarding-admin',
            SLM_ONBOARDING_URL . 'assets/js/admin.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_localize_script('slm-client-onboarding-admin', 'slmOnboarding', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slm_onboarding_nonce'),
            'strings' => [
                'searching' => __('Searching...', 'flavor'),
                'noResults' => __('No clients found.', 'flavor'),
                'selectClient' => __('Select a client to view details.', 'flavor'),
                'sendingLink' => __('Sending onboarding link...', 'flavor'),
                'linkSent' => __('Onboarding link sent successfully!', 'flavor'),
                'error' => __('An error occurred. Please try again.', 'flavor'),
                'confirmSend' => __('Send onboarding link to this client?', 'flavor'),
            ],
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on pages with our shortcodes
        global $post;
        
        if (!$post) {
            return;
        }
        
        $has_shortcode = has_shortcode($post->post_content, 'slm_onboarding_flow') 
            || has_shortcode($post->post_content, 'slm_client_search');
        
        // Also check for magic link token in URL
        $has_token = isset($_GET['slm_token']);
        
        if (!$has_shortcode && !$has_token) {
            return;
        }
        // Load search assets if client_search shortcode is present
if (has_shortcode($post->post_content, 'slm_client_search') && current_user_can('edit_users')) {
    wp_enqueue_style(
        'slm-client-onboarding-admin',
        SLM_ONBOARDING_URL . 'assets/css/admin.css',
        [],
        self::VERSION
    );
    
    wp_enqueue_script(
        'slm-client-onboarding-admin',
        SLM_ONBOARDING_URL . 'assets/js/admin.js',
        ['jquery'],
        self::VERSION,
        true
    );
    
    wp_localize_script('slm-client-onboarding-admin', 'slmOnboarding', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('slm_onboarding_nonce'),
        'strings' => [
            'searching' => __('Searching...', 'flavor'),
            'noResults' => __('No clients found.', 'flavor'),
            'selectClient' => __('Select a client to view details.', 'flavor'),
            'sendingLink' => __('Sending onboarding link...', 'flavor'),
            'linkSent' => __('Onboarding link sent successfully!', 'flavor'),
            'error' => __('An error occurred. Please try again.', 'flavor'),
            'confirmSend' => __('Send onboarding link to this client?', 'flavor'),
        ],
    ]);
}
        
        wp_enqueue_style(
            'slm-onboarding-frontend',
            SLM_ONBOARDING_URL . 'assets/css/frontend.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'slm-signature-pad',
            'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
            [],
            '4.1.7',
            true
        );
        
        wp_enqueue_script(
            'slm-onboarding-frontend',
            SLM_ONBOARDING_URL . 'assets/js/frontend.js',
            ['jquery', 'slm-signature-pad'],
            self::VERSION,
            true
        );
        
        wp_localize_script('slm-onboarding-frontend', 'slmOnboardingFront', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slm_onboarding_front_nonce'),
            'strings' => [
                'signing' => __('Processing your signature...', 'flavor'),
                'signed' => __('Terms signed successfully!', 'flavor'),
                'settingPassword' => __('Setting your password...', 'flavor'),
                'complete' => __('Account setup complete!', 'flavor'),
                'error' => __('An error occurred. Please try again.', 'flavor'),
                'signatureRequired' => __('Please provide your signature.', 'flavor'),
                'nameRequired' => __('Please enter your full name.', 'flavor'),
                'passwordMismatch' => __('Passwords do not match.', 'flavor'),
                'passwordWeak' => __('Password must be at least 8 characters.', 'flavor'),
            ],
        ]);
    }
    
    /**
     * AJAX: Search clients
     */
    public function ajax_search_clients() {
        check_ajax_referer('slm_onboarding_nonce', 'nonce');
        
        if (!current_user_can('edit_users')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        if (class_exists('SLM_Client_Search')) {
            SLM_Client_Search::ajax_search();
        } else {
            wp_send_json_error(['message' => __('Search component not available.', 'flavor')]);
        }
    }
    
    /**
     * AJAX: Get client details
     */
    public function ajax_get_client_details() {
        check_ajax_referer('slm_onboarding_nonce', 'nonce');
        
        if (!current_user_can('edit_users')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        if (class_exists('SLM_Client_Search')) {
            SLM_Client_Search::ajax_get_details();
        } else {
            wp_send_json_error(['message' => __('Search component not available.', 'flavor')]);
        }
    }
    
    /**
     * AJAX: Send magic link
     */
    public function ajax_send_magic_link() {
        check_ajax_referer('slm_onboarding_nonce', 'nonce');
        
        if (!current_user_can('edit_users')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        if (class_exists('SLM_Magic_Link')) {
            SLM_Magic_Link::ajax_send_link();
        } else {
            wp_send_json_error(['message' => __('Magic link component not available.', 'flavor')]);
        }
    }
    
    /**
     * AJAX: Validate magic link (no login required)
     */
    public function ajax_validate_magic_link() {
        check_ajax_referer('slm_onboarding_front_nonce', 'nonce');
        
        if (class_exists('SLM_Magic_Link')) {
            SLM_Magic_Link::ajax_validate();
        } else {
            wp_send_json_error(['message' => __('Validation component not available.', 'flavor')]);
        }
    }
    
    /**
     * AJAX: Submit terms signature (no login required)
     */
public function ajax_submit_terms_signature() {
    error_log('=== SLM Terms Signature Debug ===');
    error_log('POST: ' . print_r($_POST, true));
    error_log('Class exists: ' . (class_exists('SLM_Terms_Agreement') ? 'yes' : 'no'));
    
    check_ajax_referer('slm_onboarding_front_nonce', 'nonce');
    
    if (class_exists('SLM_Terms_Agreement')) {
        error_log('Calling SLM_Terms_Agreement::ajax_submit_signature()');
        SLM_Terms_Agreement::ajax_submit_signature();
    } else {
        error_log('SLM_Terms_Agreement class not found!');
        wp_send_json_error(['message' => __('Terms component not available.', 'flavor')]);
    }
}
    
    /**
     * AJAX: Set password (no login required)
     */
    public function ajax_set_password() {
        check_ajax_referer('slm_onboarding_front_nonce', 'nonce');
        
        if (class_exists('SLM_Onboarding_Flow')) {
            SLM_Onboarding_Flow::ajax_set_password();
        } else {
            wp_send_json_error(['message' => __('Onboarding component not available.', 'flavor')]);
        }
    }
    
    /**
     * Get table name helper
     */
    public static function get_table($table) {
        global $wpdb;
        
        $tables = [
            'documents' => $wpdb->prefix . 'slm_documents',
            'access_log' => $wpdb->prefix . 'slm_document_access_log',
            'folders' => $wpdb->prefix . 'slm_client_folders',
            'magic_links' => $wpdb->prefix . 'slm_magic_links',
        ];
        
        return $tables[$table] ?? null;
    }
    
    /**
     * Log helper
     */
    public static function log($message, $level = 'info') {
        if (!SLM_ONBOARDING_DEBUG && $level !== 'error') {
            return;
        }
        
        $prefix = '[SLM Onboarding]';
        
        switch ($level) {
            case 'error':
                error_log("$prefix ERROR: $message");
                break;
            case 'warning':
                error_log("$prefix WARNING: $message");
                break;
            default:
                error_log("$prefix INFO: $message");
        }
    }
}

/**
 * Initialize the system
 */
function slm_client_onboarding() {
    return SLM_Client_Onboarding::instance();
}

// Initialize on plugins_loaded to ensure all dependencies are available
add_action('plugins_loaded', 'slm_client_onboarding', 20);
