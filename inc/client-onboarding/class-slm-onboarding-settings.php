<?php
/**
 * SLM Onboarding Settings
 * 
 * Admin settings page for configuring:
 * - Firm information
 * - Email templates
 * - Magic link expiry
 * - Terms content
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Onboarding_Settings {
    
    /**
     * Option group name
     */
    const OPTION_GROUP = 'slm_onboarding_settings';
    
    /**
     * Settings page slug
     */
    const PAGE_SLUG = 'slm-onboarding-settings';
    
    /**
     * Initialize hooks
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }
    
    /**
     * Add settings submenu page
     */
    public static function add_settings_page() {
        add_submenu_page(
            'slm-client-onboarding',
            __('Settings', 'flavor'),
            __('Settings', 'flavor'),
            'manage_options',
            self::PAGE_SLUG,
            [__CLASS__, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // Firm Information Section
        add_settings_section(
            'slm_firm_section',
            __('Firm Information', 'flavor'),
            [__CLASS__, 'render_firm_section'],
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'slm_firm_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Studio Legale Metta',
        ]);
        
        add_settings_field(
            'slm_firm_name',
            __('Firm Name', 'flavor'),
            [__CLASS__, 'render_text_field'],
            self::PAGE_SLUG,
            'slm_firm_section',
            [
                'id' => 'slm_firm_name',
                'description' => __('Used in emails and document headers.', 'flavor'),
            ]
        );
        
        register_setting(self::OPTION_GROUP, 'slm_firm_email', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => '',
        ]);
        
        add_settings_field(
            'slm_firm_email',
            __('Firm Email', 'flavor'),
            [__CLASS__, 'render_email_field'],
            self::PAGE_SLUG,
            'slm_firm_section',
            [
                'id' => 'slm_firm_email',
                'description' => __('Reply-to address for system emails. Leave blank to use admin email.', 'flavor'),
            ]
        );
        
        register_setting(self::OPTION_GROUP, 'slm_firm_phone', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        
        add_settings_field(
            'slm_firm_phone',
            __('Firm Phone', 'flavor'),
            [__CLASS__, 'render_text_field'],
            self::PAGE_SLUG,
            'slm_firm_section',
            [
                'id' => 'slm_firm_phone',
                'description' => __('Contact phone number for clients.', 'flavor'),
            ]
        );
        
        // Magic Link Section
        add_settings_section(
            'slm_magic_link_section',
            __('Magic Link Settings', 'flavor'),
            [__CLASS__, 'render_magic_link_section'],
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'slm_magic_link_expiry_hours', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 24,
        ]);
        
        add_settings_field(
            'slm_magic_link_expiry_hours',
            __('Link Expiry (Hours)', 'flavor'),
            [__CLASS__, 'render_number_field'],
            self::PAGE_SLUG,
            'slm_magic_link_section',
            [
                'id' => 'slm_magic_link_expiry_hours',
                'min' => 1,
                'max' => 168,
                'description' => __('How long magic links remain valid. Default: 24 hours.', 'flavor'),
            ]
        );
        
        // Terms Agreement Section
        add_settings_section(
            'slm_terms_section',
            __('Terms Agreement', 'flavor'),
            [__CLASS__, 'render_terms_section'],
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'slm_terms_content', [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => '',
        ]);
        
        add_settings_field(
            'slm_terms_content',
            __('Terms Content', 'flavor'),
            [__CLASS__, 'render_editor_field'],
            self::PAGE_SLUG,
            'slm_terms_section',
            [
                'id' => 'slm_terms_content',
                'description' => __('The terms of agreement that clients will sign. Leave blank to use ACF field or default placeholder.', 'flavor'),
            ]
        );
        
        // Portal URLs Section
        add_settings_section(
            'slm_urls_section',
            __('Portal URLs', 'flavor'),
            [__CLASS__, 'render_urls_section'],
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'slm_client_portal_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '/client-portal/',
        ]);
        
        add_settings_field(
            'slm_client_portal_url',
            __('Client Portal URL', 'flavor'),
            [__CLASS__, 'render_text_field'],
            self::PAGE_SLUG,
            'slm_urls_section',
            [
                'id' => 'slm_client_portal_url',
                'description' => __('Where to redirect clients after completing onboarding.', 'flavor'),
            ]
        );
        
        register_setting(self::OPTION_GROUP, 'slm_lawyer_portal_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '/lawyer-portal/',
        ]);
        
        add_settings_field(
            'slm_lawyer_portal_url',
            __('Lawyer Portal URL', 'flavor'),
            [__CLASS__, 'render_text_field'],
            self::PAGE_SLUG,
            'slm_urls_section',
            [
                'id' => 'slm_lawyer_portal_url',
                'description' => __('Lawyer dashboard page URL.', 'flavor'),
            ]
        );
        
        // Notification Section
        add_settings_section(
            'slm_notification_section',
            __('Notifications', 'flavor'),
            [__CLASS__, 'render_notification_section'],
            self::PAGE_SLUG
        );
        
        register_setting(self::OPTION_GROUP, 'slm_notify_admin_onboarding', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false,
        ]);
        
        add_settings_field(
            'slm_notify_admin_onboarding',
            __('Notify Admin', 'flavor'),
            [__CLASS__, 'render_checkbox_field'],
            self::PAGE_SLUG,
            'slm_notification_section',
            [
                'id' => 'slm_notify_admin_onboarding',
                'label' => __('Send admin notification when client completes onboarding', 'flavor'),
            ]
        );
        
        register_setting(self::OPTION_GROUP, 'slm_send_welcome_email', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ]);
        
        add_settings_field(
            'slm_send_welcome_email',
            __('Welcome Email', 'flavor'),
            [__CLASS__, 'render_checkbox_field'],
            self::PAGE_SLUG,
            'slm_notification_section',
            [
                'id' => 'slm_send_welcome_email',
                'label' => __('Send welcome email to client after onboarding complete', 'flavor'),
            ]
        );
    }
    /**
 * AJAX: Get client documents
 */
public function ajax_get_client_documents() {
    check_ajax_referer('slm_onboarding_nonce', 'nonce');
    
    if (!current_user_can('edit_users')) {
        wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
    }
    
    $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
    
    if (!$user_id) {
        wp_send_json_error(['message' => __('Invalid user ID.', 'flavor')]);
    }
    
    $documents = $this->get_client_documents($user_id);
    
    wp_send_json_success(['documents' => $documents]);
}

/**
 * Get documents for a client
 */
private function get_client_documents($user_id) {
    global $wpdb;
    
    $documents = [];
    
    // Get from slm_documents table
    $table_name = $wpdb->prefix . 'slm_documents';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
        $user_id
    ));
    
    if ($results) {
        foreach ($results as $doc) {
            $documents[] = [
                'id' => $doc->id,
                'name' => $doc->document_name ?: __('Terms Agreement', 'flavor'),
                'type' => $doc->document_type ?: 'terms',
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($doc->created_at)),
                'size' => $this->format_file_size($doc->file_size ?? 0),
                'download_url' => $this->get_document_download_url($doc->id),
                'view_url' => $this->get_document_view_url($doc->id),
            ];
        }
    }
    
    // Also check for terms document in user meta
    $terms_doc_id = get_user_meta($user_id, 'slm_terms_document_id', true);
    if ($terms_doc_id && !$this->document_in_list($terms_doc_id, $documents)) {
        // Document might be stored differently, add it
        $terms_ref = get_user_meta($user_id, 'slm_terms_reference', true);
        $terms_date = get_user_meta($user_id, 'slm_terms_signed_date', true);
        
        $documents[] = [
            'id' => $terms_doc_id,
            'name' => __('Signed Terms Agreement', 'flavor'),
            'type' => 'terms',
            'date' => $terms_date ? date_i18n(get_option('date_format'), strtotime($terms_date)) : '—',
            'size' => '—',
            'download_url' => $this->get_document_download_url($terms_doc_id),
            'view_url' => $this->get_document_view_url($terms_doc_id),
            'reference' => $terms_ref,
        ];
    }
    
    return $documents;
}

/**
 * Check if document already in list
 */
private function document_in_list($doc_id, $documents) {
    foreach ($documents as $doc) {
        if ($doc['id'] == $doc_id) {
            return true;
        }
    }
    return false;
}

/**
 * Format file size
 */
private function format_file_size($bytes) {
    if ($bytes <= 0) return '—';
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

/**
 * Get document download URL
 */
private function get_document_download_url($doc_id) {
    return add_query_arg([
        'slm_download_doc' => $doc_id,
        'nonce' => wp_create_nonce('slm_download_' . $doc_id)
    ], home_url());
}

/**
 * Get document view URL
 */
private function get_document_view_url($doc_id) {
    return add_query_arg([
        'slm_view_doc' => $doc_id,
        'nonce' => wp_create_nonce('slm_view_' . $doc_id)
    ], home_url());
}
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if settings were saved
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'slm_onboarding_messages',
                'slm_onboarding_message',
                __('Settings saved.', 'flavor'),
                'updated'
            );
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('slm_onboarding_messages'); ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button(__('Save Settings', 'flavor'));
                ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e('System Status', 'flavor'); ?></h2>
            <?php self::render_system_status(); ?>
            
            <hr>
            
            <h2><?php esc_html_e('Tools', 'flavor'); ?></h2>
            <?php self::render_tools(); ?>
        </div>
        <?php
    }
    
    /**
     * Render section descriptions
     */
    public static function render_firm_section() {
        echo '<p>' . esc_html__('Configure your firm\'s information for emails and documents.', 'flavor') . '</p>';
    }
    
    public static function render_magic_link_section() {
        echo '<p>' . esc_html__('Settings for client onboarding magic links.', 'flavor') . '</p>';
    }
    
    public static function render_terms_section() {
        echo '<p>' . esc_html__('Configure the terms of agreement that clients must sign.', 'flavor') . '</p>';
        
        // Check for ACF
        if (function_exists('get_field')) {
            $acf_content = get_field('terms_agreement_content', 'option');
            if (!empty($acf_content)) {
                echo '<div class="notice notice-info inline"><p>';
                echo esc_html__('Note: An ACF field "terms_agreement_content" exists and will be used if the field below is empty.', 'flavor');
                echo '</p></div>';
            }
        }
    }
    
    public static function render_urls_section() {
        echo '<p>' . esc_html__('Configure portal page URLs.', 'flavor') . '</p>';
    }
    
    public static function render_notification_section() {
        echo '<p>' . esc_html__('Configure email notifications.', 'flavor') . '</p>';
    }
    
    /**
     * Render text field
     */
    public static function render_text_field($args) {
        $value = get_option($args['id'], '');
        ?>
        <input 
            type="text" 
            id="<?php echo esc_attr($args['id']); ?>" 
            name="<?php echo esc_attr($args['id']); ?>" 
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
        >
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    /**
     * Render email field
     */
    public static function render_email_field($args) {
        $value = get_option($args['id'], '');
        ?>
        <input 
            type="email" 
            id="<?php echo esc_attr($args['id']); ?>" 
            name="<?php echo esc_attr($args['id']); ?>" 
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
        >
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    
    /**
     * Render number field
     */
    public static function render_number_field($args) {
        $value = get_option($args['id'], $args['min'] ?? 1);
        ?>
        <input 
            type="number" 
            id="<?php echo esc_attr($args['id']); ?>" 
            name="<?php echo esc_attr($args['id']); ?>" 
            value="<?php echo esc_attr($value); ?>"
            min="<?php echo esc_attr($args['min'] ?? 0); ?>"
            max="<?php echo esc_attr($args['max'] ?? ''); ?>"
            class="small-text"
        >
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }
    add_action('wp_ajax_slm_get_client_documents', [$this, 'ajax_get_client_documents']);
    /**
     * Render checkbox field
     */
    public static function render_checkbox_field($args) {
        $value = get_option($args['id'], false);
        ?>
        <label for="<?php echo esc_attr($args['id']); ?>">
            <input 
                type="checkbox" 
                id="<?php echo esc_attr($args['id']); ?>" 
                name="<?php echo esc_attr($args['id']); ?>" 
                value="1"
                <?php checked($value, true); ?>
            >
            <?php echo esc_html($args['label']); ?>
        </label>
        <?php
    }
    
    /**
     * Render editor field
     */
    public static function render_editor_field($args) {
        $value = get_option($args['id'], '');
        
        wp_editor($value, $args['id'], [
            'textarea_name' => $args['id'],
            'textarea_rows' => 15,
            'media_buttons' => false,
            'teeny' => false,
            'quicktags' => true,
        ]);
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Render system status
     */
    private static function render_system_status() {
        global $wpdb;
        
        $checks = [];
        
        // Check database tables
        $tables = ['documents', 'access_log', 'folders', 'magic_links'];
        foreach ($tables as $table) {
            $table_name = SLM_Client_Onboarding::get_table($table);
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            $checks['table_' . $table] = [
                'label' => sprintf(__('Table: %s', 'flavor'), $table),
                'status' => $exists,
            ];
        }
        
        // Check upload directories
        $upload_dir = wp_upload_dir();
        $private_dir = $upload_dir['basedir'] . '/private/slm-documents';
        $checks['upload_dir'] = [
            'label' => __('Private upload directory', 'flavor'),
            'status' => is_dir($private_dir) && is_writable($private_dir),
        ];
        
        // Check .htaccess protection
        $htaccess = $private_dir . '/.htaccess';
        $checks['htaccess'] = [
            'label' => __('.htaccess protection', 'flavor'),
            'status' => file_exists($htaccess),
        ];
        
        // Check encryption key
        $has_key = defined('SLM_ENCRYPTION_KEY') || get_option('slm_encryption_key');
        $checks['encryption'] = [
            'label' => __('Encryption key configured', 'flavor'),
            'status' => (bool) $has_key,
        ];
        
        // Check WooCommerce
        $checks['woocommerce'] = [
            'label' => __('WooCommerce active', 'flavor'),
            'status' => class_exists('WooCommerce'),
        ];
        
        // Check Gravity PDF (for mPDF)
        $checks['gravity_pdf'] = [
            'label' => __('Gravity PDF (for PDF generation)', 'flavor'),
            'status' => class_exists('GPDFAPI'),
        ];
        
        // Check permalinks
        $checks['permalinks'] = [
            'label' => __('Permalinks enabled', 'flavor'),
            'status' => get_option('permalink_structure') !== '',
        ];
        
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Component', 'flavor'); ?></th>
                    <th><?php esc_html_e('Status', 'flavor'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $key => $check): ?>
                    <tr>
                        <td><?php echo esc_html($check['label']); ?></td>
                        <td>
                            <?php if ($check['status']): ?>
                                <span style="color: #16a34a;">✓ <?php esc_html_e('OK', 'flavor'); ?></span>
                            <?php else: ?>
                                <span style="color: #dc2626;">✗ <?php esc_html_e('Missing', 'flavor'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Render tools section
     */
    private static function render_tools() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Flush Rewrite Rules', 'flavor'); ?></th>
                <td>
                    <form method="post" action="">
                        <?php wp_nonce_field('slm_flush_rules', 'slm_flush_nonce'); ?>
                        <button type="submit" name="slm_flush_rules" class="button">
                            <?php esc_html_e('Flush Permalinks', 'flavor'); ?>
                        </button>
                        <p class="description">
                            <?php esc_html_e('Run this after first installation to enable the /client-onboarding/ URL.', 'flavor'); ?>
                        </p>
                    </form>
                    <?php
                    if (isset($_POST['slm_flush_rules']) && wp_verify_nonce($_POST['slm_flush_nonce'], 'slm_flush_rules')) {
                        flush_rewrite_rules();
                        echo '<div class="notice notice-success inline"><p>' . esc_html__('Rewrite rules flushed.', 'flavor') . '</p></div>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Create Upload Directories', 'flavor'); ?></th>
                <td>
                    <form method="post" action="">
                        <?php wp_nonce_field('slm_create_dirs', 'slm_dirs_nonce'); ?>
                        <button type="submit" name="slm_create_dirs" class="button">
                            <?php esc_html_e('Create Directories', 'flavor'); ?>
                        </button>
                        <p class="description">
                            <?php esc_html_e('Creates the secure upload directories with .htaccess protection.', 'flavor'); ?>
                        </p>
                    </form>
                    <?php
                    if (isset($_POST['slm_create_dirs']) && wp_verify_nonce($_POST['slm_dirs_nonce'], 'slm_create_dirs')) {
                        SLM_Client_Onboarding::create_upload_directories();
                        echo '<div class="notice notice-success inline"><p>' . esc_html__('Directories created.', 'flavor') . '</p></div>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Generate Encryption Key', 'flavor'); ?></th>
                <td>
                    <?php if (defined('SLM_ENCRYPTION_KEY')): ?>
                        <p class="description">
                            <span style="color: #16a34a;">✓</span>
                            <?php esc_html_e('Encryption key is defined in wp-config.php (recommended).', 'flavor'); ?>
                        </p>
                    <?php elseif (get_option('slm_encryption_key')): ?>
                        <p class="description">
                            <span style="color: #ca8a04;">⚠</span>
                            <?php esc_html_e('Encryption key stored in database. For better security, add to wp-config.php:', 'flavor'); ?>
                        </p>
                        <code style="display: block; margin-top: 10px; padding: 10px; background: #f0f0f0;">
                            define('SLM_ENCRYPTION_KEY', 'base64:<?php echo esc_html(get_option('slm_encryption_key')); ?>');
                        </code>
                    <?php else: ?>
                        <form method="post" action="">
                            <?php wp_nonce_field('slm_gen_key', 'slm_key_nonce'); ?>
                            <button type="submit" name="slm_gen_key" class="button">
                                <?php esc_html_e('Generate Key', 'flavor'); ?>
                            </button>
                            <p class="description">
                                <?php esc_html_e('Generate a new encryption key (stored in database).', 'flavor'); ?>
                            </p>
                        </form>
                        <?php
                        if (isset($_POST['slm_gen_key']) && wp_verify_nonce($_POST['slm_key_nonce'], 'slm_gen_key')) {
                            $key = base64_encode(random_bytes(32));
                            update_option('slm_encryption_key', $key);
                            echo '<div class="notice notice-success inline"><p>' . esc_html__('Encryption key generated.', 'flavor') . '</p></div>';
                            echo '<p>' . esc_html__('Add this to wp-config.php for better security:', 'flavor') . '</p>';
                            echo '<code>define(\'SLM_ENCRYPTION_KEY\', \'base64:' . esc_html($key) . '\');</code>';
                        }
                        ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Get setting with default
     */
    public static function get($key, $default = '') {
        return get_option('slm_' . $key, $default);
    }
}
