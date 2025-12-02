<?php
/**
 * SLM Document Management System
 * 
 * Complete document management with:
 * - Encrypted storage with per-file keys
 * - Version history
 * - Secure PDF viewer with read tracking
 * - External sharing with passwords
 * - DocuSign-style signing envelopes
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS {
    
    /**
     * System version
     */
    const VERSION = '1.0.0';
    
    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Table names cache
     */
    private $tables = [];
    
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
        $this->init_tables();
        $this->check_database();
        $this->load_components();
        $this->init_hooks();
    }
    
    /**
     * Define constants
     */
    private function define_constants() {
        if (!defined('SLM_DMS_PATH')) {
            define('SLM_DMS_PATH', get_stylesheet_directory() . '/inc/dms/');
        }
        
        if (!defined('SLM_DMS_URL')) {
            define('SLM_DMS_URL', get_stylesheet_directory_uri() . '/inc/dms/');
        }
        
        if (!defined('SLM_DMS_DEBUG')) {
            define('SLM_DMS_DEBUG', WP_DEBUG);
        }
    }
    
    /**
     * Initialize table names
     */
    private function init_tables() {
        global $wpdb;
        
        $this->tables = [
            'viewing_sessions' => $wpdb->prefix . 'slm_viewing_sessions',
            'share_access_log' => $wpdb->prefix . 'slm_share_access_log',
            'document_versions' => $wpdb->prefix . 'slm_document_versions',
            'signing_fields' => $wpdb->prefix . 'slm_signing_fields',
        ];
    }
    
    /**
     * Get table name
     */
    public static function get_table($name) {
        $instance = self::instance();
        return isset($instance->tables[$name]) ? $instance->tables[$name] : null;
    }
    
    /**
     * Check and create database tables
     */
    private function check_database() {
        $installed_version = get_option('slm_dms_db_version', '0');
        
        if (version_compare($installed_version, self::DB_VERSION, '<')) {
            $this->create_tables();
            update_option('slm_dms_db_version', self::DB_VERSION);
        }
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Viewing sessions table
        $sql = "CREATE TABLE {$this->tables['viewing_sessions']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id bigint(20) UNSIGNED NOT NULL,
            version_id bigint(20) UNSIGNED DEFAULT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            share_link_id bigint(20) UNSIGNED DEFAULT NULL,
            session_token varchar(64) NOT NULL,
            pages_viewed text,
            total_pages int(11) DEFAULT 0,
            max_page_reached int(11) DEFAULT 0,
            read_depth_percent int(11) DEFAULT 0,
            total_time_seconds int(11) DEFAULT 0,
            completed tinyint(1) DEFAULT 0,
            ip_address varchar(45),
            user_agent text,
            started_at datetime NOT NULL,
            last_update_at datetime,
            ended_at datetime,
            PRIMARY KEY (id),
            KEY document_id (document_id),
            KEY user_id (user_id),
            KEY session_token (session_token),
            KEY share_link_id (share_link_id)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Share access log table
        $sql = "CREATE TABLE {$this->tables['share_access_log']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            share_link_id bigint(20) UNSIGNED NOT NULL,
            document_id bigint(20) UNSIGNED NOT NULL,
            access_type enum('view','download','password_attempt') NOT NULL,
            success tinyint(1) DEFAULT 1,
            ip_address varchar(45),
            user_agent text,
            referer text,
            accessed_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY share_link_id (share_link_id),
            KEY document_id (document_id),
            KEY accessed_at (accessed_at)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Document versions table
        $sql = "CREATE TABLE {$this->tables['document_versions']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id bigint(20) UNSIGNED NOT NULL,
            version_number int(11) NOT NULL DEFAULT 1,
            file_uuid varchar(36) NOT NULL,
            file_path text NOT NULL,
            file_size bigint(20) UNSIGNED DEFAULT 0,
            file_hash varchar(64),
            mime_type varchar(100),
            encryption_iv varchar(32),
            encryption_tag varchar(32),
            uploaded_by bigint(20) UNSIGNED,
            upload_note text,
            is_current tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY document_id (document_id),
            KEY file_uuid (file_uuid),
            KEY is_current (is_current)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Signing fields table
        $sql = "CREATE TABLE {$this->tables['signing_fields']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            envelope_id bigint(20) UNSIGNED NOT NULL,
            signer_index int(11) NOT NULL DEFAULT 0,
            field_type enum('signature','initials','date','text','checkbox') NOT NULL,
            page_number int(11) NOT NULL DEFAULT 1,
            x_position decimal(10,4) NOT NULL,
            y_position decimal(10,4) NOT NULL,
            width decimal(10,4) DEFAULT 200,
            height decimal(10,4) DEFAULT 50,
            required tinyint(1) DEFAULT 1,
            placeholder text,
            field_value text,
            signed_at datetime,
            PRIMARY KEY (id),
            KEY envelope_id (envelope_id),
            KEY signer_index (signer_index)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        self::log('Database tables created/updated');
    }
    
    /**
     * Load component classes
     */
    private function load_components() {
        $components = [
            'class-slm-dms-encryption.php',
            'class-slm-dms-documents.php',
            'class-slm-dms-folders.php',
            'class-slm-dms-viewer.php',
            'class-slm-dms-sharing.php',
            'class-slm-dms-envelopes.php',
            'class-slm-dms-settings.php',
            'class-slm-dms-acf-fields.php',
            'class-slm-case-cpt.php',
        ];
        
        foreach ($components as $file) {
            $path = SLM_DMS_PATH . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register CPTs
        add_action('init', [$this, 'register_post_types']);
        
        // Register taxonomies
        add_action('init', [$this, 'register_taxonomies']);
        
        // Rewrite rules
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_viewer_request']);
        
        // REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        
        // Admin menu
        add_action('admin_menu', [$this, 'register_admin_menu']);
        
        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // Initialize components
        add_action('init', [$this, 'init_components'], 15);
        
        // Activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        // Frontend AJAX handlers
        add_action('wp_ajax_slm_get_folder_contents', [$this, 'ajax_get_folder_contents']);
        add_action('wp_ajax_nopriv_slm_get_folder_contents', [$this, 'ajax_get_folder_contents']);
        add_action('wp_ajax_slm_create_view_session', [$this, 'ajax_create_view_session']);
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Document CPT
        register_post_type('slm_document', [
            'labels' => [
                'name' => __('Documents', 'flavor'),
                'singular_name' => __('Document', 'flavor'),
                'add_new' => __('Add New', 'flavor'),
                'add_new_item' => __('Add New Document', 'flavor'),
                'edit_item' => __('Edit Document', 'flavor'),
                'view_item' => __('View Document', 'flavor'),
                'search_items' => __('Search Documents', 'flavor'),
                'not_found' => __('No documents found', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'slm-dms',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-media-document',
        ]);
        
        // Folder CPT
        register_post_type('slm_folder', [
            'labels' => [
                'name' => __('Folders', 'flavor'),
                'singular_name' => __('Folder', 'flavor'),
                'add_new' => __('Add New', 'flavor'),
                'add_new_item' => __('Add New Folder', 'flavor'),
                'edit_item' => __('Edit Folder', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'slm-dms',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => true,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-portfolio',
        ]);
        
        // Envelope CPT (signing requests)
        register_post_type('slm_envelope', [
            'labels' => [
                'name' => __('Signing Envelopes', 'flavor'),
                'singular_name' => __('Envelope', 'flavor'),
                'add_new' => __('Create Envelope', 'flavor'),
                'add_new_item' => __('Create Signing Request', 'flavor'),
                'edit_item' => __('Edit Envelope', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'slm-dms',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-edit-page',
        ]);
        
        // Share Link CPT
        register_post_type('slm_share_link', [
            'labels' => [
                'name' => __('Share Links', 'flavor'),
                'singular_name' => __('Share Link', 'flavor'),
                'add_new' => __('Create Link', 'flavor'),
                'add_new_item' => __('Create Share Link', 'flavor'),
                'edit_item' => __('Edit Share Link', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'slm-dms',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-share',
        ]);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Document category
        register_taxonomy('slm_doc_category', 'slm_document', [
            'labels' => [
                'name' => __('Document Categories', 'flavor'),
                'singular_name' => __('Category', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
        ]);
        
        // Document tags
        register_taxonomy('slm_doc_tag', 'slm_document', [
            'labels' => [
                'name' => __('Document Tags', 'flavor'),
                'singular_name' => __('Tag', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'hierarchical' => false,
            'show_admin_column' => true,
        ]);
    }
    
    /**
     * Add rewrite rules
     */
    public function add_rewrite_rules() {
        // Secure document viewer
        add_rewrite_rule(
            '^document-viewer/([0-9]+)/([a-zA-Z0-9]+)/?$',
            'index.php?slm_doc_viewer=1&slm_doc_id=$matches[1]&slm_session=$matches[2]',
            'top'
        );
        
        // External share view
        add_rewrite_rule(
            '^shared-document/([a-zA-Z0-9]+)/?$',
            'index.php?slm_shared_doc=1&slm_share_token=$matches[1]',
            'top'
        );
        
        // Signing page
        add_rewrite_rule(
            '^sign-document/([a-zA-Z0-9]+)/?$',
            'index.php?slm_sign_doc=1&slm_sign_token=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'slm_doc_viewer';
        $vars[] = 'slm_doc_id';
        $vars[] = 'slm_session';
        $vars[] = 'slm_shared_doc';
        $vars[] = 'slm_share_token';
        $vars[] = 'slm_sign_doc';
        $vars[] = 'slm_sign_token';
        return $vars;
    }
    
    /**
     * Handle viewer/share/sign requests
     */
    public function handle_viewer_request() {
        if (get_query_var('slm_doc_viewer')) {
            if (class_exists('SLM_DMS_Viewer')) {
                SLM_DMS_Viewer::render_viewer(
                    get_query_var('slm_doc_id'),
                    get_query_var('slm_session')
                );
                exit;
            }
        }
        
        if (get_query_var('slm_shared_doc')) {
            if (class_exists('SLM_DMS_Sharing')) {
                SLM_DMS_Sharing::render_shared_view(
                    get_query_var('slm_share_token')
                );
                exit;
            }
        }
        
        if (get_query_var('slm_sign_doc')) {
            if (class_exists('SLM_DMS_Envelopes')) {
                SLM_DMS_Envelopes::render_signing_page(
                    get_query_var('slm_sign_token')
                );
                exit;
            }
        }
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Viewer update endpoint
        register_rest_route('slm/v1', '/viewer-update', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_viewer_update'],
            'permission_callback' => '__return_true',
        ]);
        
        // Document operations
        register_rest_route('slm/v1', '/documents', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_documents'],
            'permission_callback' => [$this, 'rest_check_permission'],
        ]);
        
        register_rest_route('slm/v1', '/documents/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_document'],
            'permission_callback' => [$this, 'rest_check_permission'],
        ]);
        
        register_rest_route('slm/v1', '/documents/upload', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_upload_document'],
            'permission_callback' => [$this, 'rest_check_permission'],
        ]);
    }
    
    /**
     * REST: Check permission
     */
    public function rest_check_permission() {
        return is_user_logged_in();
    }
    
    /**
     * REST: Viewer update
     */
    public function rest_viewer_update($request) {
        $data = json_decode($request->get_body(), true);
        
        if (empty($data['session_token'])) {
            return new WP_Error('invalid_session', 'Invalid session token', ['status' => 400]);
        }
        
        if (class_exists('SLM_DMS_Viewer')) {
            SLM_DMS_Viewer::update_session($data);
        }
        
        return ['success' => true];
    }
    
    /**
     * REST: Get documents
     */
    public function rest_get_documents($request) {
        if (class_exists('SLM_DMS_Documents')) {
            return SLM_DMS_Documents::get_documents_list($request->get_params());
        }
        return [];
    }
    
    /**
     * REST: Get single document
     */
    public function rest_get_document($request) {
        $id = $request->get_param('id');
        
        if (class_exists('SLM_DMS_Documents')) {
            return SLM_DMS_Documents::get_document($id);
        }
        return new WP_Error('not_found', 'Document not found', ['status' => 404]);
    }
    
    /**
     * REST: Upload document
     */
    public function rest_upload_document($request) {
        if (class_exists('SLM_DMS_Documents')) {
            return SLM_DMS_Documents::handle_upload($request);
        }
        return new WP_Error('upload_failed', 'Upload failed', ['status' => 500]);
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu() {
        add_menu_page(
            __('Document Management', 'flavor'),
            __('DMS', 'flavor'),
            'edit_posts',
            'slm-dms',
            [$this, 'render_admin_page'],
            'dashicons-portfolio',
            31
        );
        
        add_submenu_page(
            'slm-dms',
            __('All Documents', 'flavor'),
            __('All Documents', 'flavor'),
            'edit_posts',
            'edit.php?post_type=slm_document'
        );
        
        add_submenu_page(
            'slm-dms',
            __('Folders', 'flavor'),
            __('Folders', 'flavor'),
            'edit_posts',
            'edit.php?post_type=slm_folder'
        );
        
        add_submenu_page(
            'slm-dms',
            __('Signing Envelopes', 'flavor'),
            __('Signing', 'flavor'),
            'edit_posts',
            'edit.php?post_type=slm_envelope'
        );
        
        add_submenu_page(
            'slm-dms',
            __('Share Links', 'flavor'),
            __('Share Links', 'flavor'),
            'edit_posts',
            'edit.php?post_type=slm_share_link'
        );
        
        add_submenu_page(
            'slm-dms',
            __('DMS Settings', 'flavor'),
            __('Settings', 'flavor'),
            'manage_options',
            'slm-dms-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Document Management System', 'flavor'); ?></h1>
            
            <div class="slm-dms-dashboard">
                <!-- Quick stats -->
                <div class="slm-dms-stats">
                    <?php $this->render_dashboard_stats(); ?>
                </div>
                
                <!-- Recent documents -->
                <div class="slm-dms-recent">
                    <h2><?php esc_html_e('Recent Documents', 'flavor'); ?></h2>
                    <?php $this->render_recent_documents(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render dashboard stats
     */
    private function render_dashboard_stats() {
        $docs_count = wp_count_posts('slm_document');
        $envelopes_count = wp_count_posts('slm_envelope');
        $shares_count = wp_count_posts('slm_share_link');
        
        ?>
        <div class="slm-stat-cards">
            <div class="stat-card">
                <span class="stat-number"><?php echo intval($docs_count->publish ?? 0); ?></span>
                <span class="stat-label"><?php esc_html_e('Documents', 'flavor'); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo intval($envelopes_count->publish ?? 0); ?></span>
                <span class="stat-label"><?php esc_html_e('Envelopes', 'flavor'); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo intval($shares_count->publish ?? 0); ?></span>
                <span class="stat-label"><?php esc_html_e('Share Links', 'flavor'); ?></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render recent documents
     */
    private function render_recent_documents() {
        $docs = get_posts([
            'post_type' => 'slm_document',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        if (empty($docs)) {
            echo '<p>' . esc_html__('No documents yet.', 'flavor') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Document', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Uploaded', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Actions', 'flavor') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($docs as $doc) {
            echo '<tr>';
            echo '<td>' . esc_html($doc->post_title) . '</td>';
            echo '<td>' . esc_html(get_the_date('', $doc)) . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(get_edit_post_link($doc->ID)) . '">' . esc_html__('Edit', 'flavor') . '</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('DMS Settings', 'flavor'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('slm_dms_settings');
                do_settings_sections('slm-dms-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'slm-dms') === false && get_post_type() !== 'slm_document') {
            return;
        }
        
        wp_enqueue_style(
            'slm-dms-admin',
            SLM_DMS_URL . 'assets/css/admin.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'slm-dms-admin',
            SLM_DMS_URL . 'assets/js/admin.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_localize_script('slm-dms-admin', 'slmDMS', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('slm/v1/'),
            'nonce' => wp_create_nonce('slm_dms_nonce'),
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue on DMS pages or portal
        $enqueue = is_singular('slm_document') 
            || get_query_var('slm_doc_viewer') 
            || is_page('client-portal')
            || is_page('documents');
        
        if (!$enqueue) {
            return;
        }
        
        wp_enqueue_style(
            'slm-dms-frontend',
            SLM_DMS_URL . 'assets/css/frontend.css',
            [],
            self::VERSION
        );
        
        wp_enqueue_script(
            'slm-dms-frontend',
            SLM_DMS_URL . 'assets/js/frontend.js',
            [],
            self::VERSION,
            true
        );
        
        // Get current user's case if client
        $case_id = 0;
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $cases = SLM_Case_CPT::get_user_cases($user_id, 'active');
            if (!empty($cases)) {
                $case_id = $cases[0]->ID;
            }
        }
        
        wp_localize_script('slm-dms-frontend', 'slmDMSConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('slm/v1/'),
            'nonce' => wp_create_nonce('slm_dms_nonce'),
            'caseId' => $case_id,
            'userId' => get_current_user_id(),
        ]);
    }
    
    /**
     * Initialize components
     */
    public function init_components() {
        if (class_exists('SLM_DMS_Encryption')) {
            SLM_DMS_Encryption::init();
        }
        
        if (class_exists('SLM_DMS_Documents')) {
            SLM_DMS_Documents::init();
        }
        
        if (class_exists('SLM_DMS_Folders')) {
            SLM_DMS_Folders::init();
        }
        
        if (class_exists('SLM_DMS_Viewer')) {
            SLM_DMS_Viewer::init();
        }
        
        if (class_exists('SLM_DMS_Sharing')) {
            SLM_DMS_Sharing::init();
        }
        
        if (class_exists('SLM_DMS_Envelopes')) {
            SLM_DMS_Envelopes::init();
        }
        
        if (class_exists('SLM_DMS_Settings')) {
            SLM_DMS_Settings::init();
        }
        
        if (class_exists('SLM_DMS_ACF_Fields')) {
            SLM_DMS_ACF_Fields::init();
        }
        
        if (class_exists('SLM_Case_CPT')) {
            SLM_Case_CPT::init();
        }
    }
    
    /**
     * AJAX: Get folder contents for portal
     */
    public function ajax_get_folder_contents() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Not authenticated.', 'flavor')]);
        }
        
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
        $user_id = get_current_user_id();
        
        // Verify user has access to the case
        if ($case_id) {
            $client_id = get_post_meta($case_id, '_slm_client_id', true);
            if ($client_id != $user_id && !current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
            }
        }
        
        // Get subfolders
        $folder_args = [
            'post_type' => 'slm_folder',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_slm_client_visible',
                    'value' => '1',
                ],
            ],
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ];
        
        if ($folder_id) {
            $folder_args['meta_query'][] = [
                'key' => '_slm_parent_folder',
                'value' => $folder_id,
            ];
        } else {
            $folder_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => '_slm_parent_folder',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_slm_parent_folder',
                    'value' => '',
                ],
                [
                    'key' => '_slm_parent_folder',
                    'value' => '0',
                ],
            ];
        }
        
        if ($case_id) {
            $folder_args['meta_query'][] = [
                'key' => '_slm_case_id',
                'value' => $case_id,
            ];
        }
        
        $folders = get_posts($folder_args);
        $folder_list = [];
        foreach ($folders as $folder) {
            $folder_list[] = [
                'id' => $folder->ID,
                'name' => $folder->post_title,
                'color' => get_post_meta($folder->ID, '_slm_folder_color', true) ?: '#f59e0b',
            ];
        }
        
        // Get documents
        $doc_args = [
            'post_type' => 'slm_document',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_slm_client_visible',
                    'value' => '1',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        if ($folder_id) {
            $doc_args['meta_query'][] = [
                'key' => '_slm_folder_id',
                'value' => $folder_id,
            ];
        } else {
            $doc_args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => '_slm_folder_id',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_slm_folder_id',
                    'value' => '',
                ],
                [
                    'key' => '_slm_folder_id',
                    'value' => '0',
                ],
            ];
        }
        
        if ($case_id) {
            $doc_args['meta_query'][] = [
                'key' => '_slm_case_id',
                'value' => $case_id,
            ];
        }
        
        $documents = get_posts($doc_args);
        $doc_list = [];
        foreach ($documents as $doc) {
            $doc_list[] = [
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'description' => get_post_meta($doc->ID, '_slm_doc_description', true),
                'mime_type' => get_post_meta($doc->ID, '_slm_mime_type', true),
                'created_at' => $doc->post_date,
                'download_allowed' => get_post_meta($doc->ID, '_slm_download_allowed', true) === '1',
                'tags' => wp_get_post_terms($doc->ID, 'slm_doc_tag', ['fields' => 'names']),
            ];
        }
        
        // Build breadcrumb
        $breadcrumb = [];
        if ($folder_id) {
            $current = $folder_id;
            while ($current) {
                $folder = get_post($current);
                if (!$folder) break;
                
                array_unshift($breadcrumb, [
                    'id' => $folder->ID,
                    'name' => $folder->post_title,
                ]);
                
                $current = get_post_meta($folder->ID, '_slm_parent_folder', true);
            }
        }
        
        wp_send_json_success([
            'folders' => $folder_list,
            'documents' => $doc_list,
            'breadcrumb' => $breadcrumb,
        ]);
    }
    
    /**
     * AJAX: Create viewing session
     */
    public function ajax_create_view_session() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Not authenticated.', 'flavor')]);
        }
        
        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
        
        if (!$document_id) {
            wp_send_json_error(['message' => __('Invalid document.', 'flavor')]);
        }
        
        $document = get_post($document_id);
        if (!$document || $document->post_type !== 'slm_document') {
            wp_send_json_error(['message' => __('Document not found.', 'flavor')]);
        }
        
        $user_id = get_current_user_id();
        
        // Check access - user must be case client or staff
        $case_id = get_post_meta($document_id, '_slm_case_id', true);
        if ($case_id) {
            $client_id = get_post_meta($case_id, '_slm_client_id', true);
            if ($client_id != $user_id && !current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
            }
        }
        
        // Create viewing session using the Viewer class
        if (class_exists('SLM_DMS_Viewer')) {
            $session = SLM_DMS_Viewer::create_session($document_id, $user_id);
            
            if ($session) {
                $viewer_url = home_url('/document-viewer/' . $session['token'] . '/');
                wp_send_json_success([
                    'viewer_url' => $viewer_url,
                    'session_token' => $session['token'],
                ]);
            }
        }
        
        wp_send_json_error(['message' => __('Failed to create viewing session.', 'flavor')]);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_post_types();
        $this->register_taxonomies();
        $this->add_rewrite_rules();
        flush_rewrite_rules();
        
        // Create storage directories
        $this->create_storage_directories();
        
        // Generate storage hashes if not exist
        if (!get_option('slm_storage_hash_1')) {
            update_option('slm_storage_hash_1', wp_generate_password(8, false));
        }
        if (!get_option('slm_storage_hash_2')) {
            update_option('slm_storage_hash_2', wp_generate_password(8, false));
        }
    }
    
    /**
     * Create storage directories
     */
    private function create_storage_directories() {
        $upload_dir = wp_upload_dir();
        $hash1 = get_option('slm_storage_hash_1', 'temp1');
        $hash2 = get_option('slm_storage_hash_2', 'temp2');
        
        $base_path = $upload_dir['basedir'] . '/private/' . $hash1 . '/' . $hash2 . '/docs';
        
        if (!file_exists($base_path)) {
            wp_mkdir_p($base_path);
        }
        
        // Add .htaccess protection
        $htaccess = dirname($base_path) . '/.htaccess';
        if (!file_exists($htaccess)) {
            $content = "# Deny all direct access\nOrder deny,allow\nDeny from all\n";
            file_put_contents($htaccess, $content);
        }
        
        // Add index.php
        $index = dirname($base_path) . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
        }
    }
    
    /**
     * Get storage base path
     */
    public static function get_storage_path() {
        $upload_dir = wp_upload_dir();
        $hash1 = get_option('slm_storage_hash_1');
        $hash2 = get_option('slm_storage_hash_2');
        
        return $upload_dir['basedir'] . '/private/' . $hash1 . '/' . $hash2 . '/docs';
    }
    
    /**
     * Logging helper
     */
    public static function log($message, $level = 'info') {
        if (!SLM_DMS_DEBUG && $level !== 'error') {
            return;
        }
        
        error_log(sprintf('[SLM DMS] [%s] %s', strtoupper($level), $message));
    }
}

// Initialize
function slm_dms() {
    return SLM_DMS::instance();
}

// Start on plugins_loaded
add_action('plugins_loaded', 'slm_dms');
