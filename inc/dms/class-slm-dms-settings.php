<?php
/**
 * SLM DMS Settings
 * 
 * Admin settings page for:
 * - Storage configuration
 * - Encryption key management
 * - Default categories
 * - Viewer settings
 * - Signing defaults
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Settings {
    
    /**
     * Settings group
     */
    const SETTINGS_GROUP = 'slm_dms_settings';
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('wp_ajax_slm_dms_flush_rewrite', [__CLASS__, 'ajax_flush_rewrite']);
        add_action('wp_ajax_slm_dms_create_directories', [__CLASS__, 'ajax_create_directories']);
        add_action('wp_ajax_slm_dms_generate_key', [__CLASS__, 'ajax_generate_key']);
        add_action('wp_ajax_slm_dms_create_categories', [__CLASS__, 'ajax_create_categories']);
    }
    
    /**
     * Add settings page
     */
    public static function add_settings_page() {
        add_submenu_page(
            'slm-dms',
            __('DMS Settings', 'flavor'),
            __('Settings', 'flavor'),
            'manage_options',
            'slm-dms-settings',
            [__CLASS__, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // Storage settings
        register_setting(self::SETTINGS_GROUP, 'slm_dms_storage_type');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_storage_path');
        
        // Viewer settings
        register_setting(self::SETTINGS_GROUP, 'slm_dms_viewer_session_hours');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_track_reading');
        
        // Sharing defaults
        register_setting(self::SETTINGS_GROUP, 'slm_dms_default_share_expiry');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_require_share_password');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_default_download_allowed');
        
        // Signing defaults
        register_setting(self::SETTINGS_GROUP, 'slm_dms_default_envelope_expiry');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_default_signing_mode');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_send_reminders');
        
        // Notifications
        register_setting(self::SETTINGS_GROUP, 'slm_dms_notify_on_upload');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_notify_on_view');
        register_setting(self::SETTINGS_GROUP, 'slm_dms_notify_on_sign');
        
        // Storage section
        add_settings_section(
            'slm_dms_storage_section',
            __('Storage Configuration', 'flavor'),
            [__CLASS__, 'render_storage_section'],
            'slm-dms-settings'
        );
        
        add_settings_field(
            'slm_dms_storage_type',
            __('Storage Type', 'flavor'),
            [__CLASS__, 'render_storage_type_field'],
            'slm-dms-settings',
            'slm_dms_storage_section'
        );
        
        // Viewer section
        add_settings_section(
            'slm_dms_viewer_section',
            __('Document Viewer', 'flavor'),
            [__CLASS__, 'render_viewer_section'],
            'slm-dms-settings'
        );
        
        add_settings_field(
            'slm_dms_viewer_session_hours',
            __('Session Duration', 'flavor'),
            [__CLASS__, 'render_session_hours_field'],
            'slm-dms-settings',
            'slm_dms_viewer_section'
        );
        
        add_settings_field(
            'slm_dms_track_reading',
            __('Track Reading Progress', 'flavor'),
            [__CLASS__, 'render_track_reading_field'],
            'slm-dms-settings',
            'slm_dms_viewer_section'
        );
        
        // Sharing section
        add_settings_section(
            'slm_dms_sharing_section',
            __('External Sharing', 'flavor'),
            [__CLASS__, 'render_sharing_section'],
            'slm-dms-settings'
        );
        
        add_settings_field(
            'slm_dms_default_share_expiry',
            __('Default Link Expiry', 'flavor'),
            [__CLASS__, 'render_share_expiry_field'],
            'slm-dms-settings',
            'slm_dms_sharing_section'
        );
        
        add_settings_field(
            'slm_dms_default_download_allowed',
            __('Allow Downloads', 'flavor'),
            [__CLASS__, 'render_download_allowed_field'],
            'slm-dms-settings',
            'slm_dms_sharing_section'
        );
        
        // Signing section
        add_settings_section(
            'slm_dms_signing_section',
            __('Document Signing', 'flavor'),
            [__CLASS__, 'render_signing_section'],
            'slm-dms-settings'
        );
        
        add_settings_field(
            'slm_dms_default_envelope_expiry',
            __('Default Signing Expiry', 'flavor'),
            [__CLASS__, 'render_envelope_expiry_field'],
            'slm-dms-settings',
            'slm_dms_signing_section'
        );
        
        add_settings_field(
            'slm_dms_default_signing_mode',
            __('Default Signing Mode', 'flavor'),
            [__CLASS__, 'render_signing_mode_field'],
            'slm-dms-settings',
            'slm_dms_signing_section'
        );
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Document Management Settings', 'flavor'); ?></h1>
            
            <!-- System Status -->
            <div class="slm-settings-card">
                <h2><?php esc_html_e('System Status', 'flavor'); ?></h2>
                <?php self::render_system_status(); ?>
            </div>
            
            <!-- Settings Form -->
            <form method="post" action="options.php">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections('slm-dms-settings');
                submit_button();
                ?>
            </form>
            
            <!-- Tools -->
            <div class="slm-settings-card">
                <h2><?php esc_html_e('Tools', 'flavor'); ?></h2>
                <?php self::render_tools(); ?>
            </div>
        </div>
        
        <style>
            .slm-settings-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
            }
            .slm-settings-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .slm-status-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .slm-status-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px;
                background: #f9f9f9;
                border-radius: 4px;
            }
            .slm-status-icon {
                font-size: 18px;
            }
            .slm-status-icon.success { color: #46b450; }
            .slm-status-icon.error { color: #dc3232; }
            .slm-status-icon.warning { color: #ffb900; }
            .slm-status-label {
                font-weight: 500;
            }
            .slm-status-value {
                color: #666;
                font-size: 13px;
            }
            .slm-tools-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .slm-tool-item {
                padding: 15px;
                background: #f9f9f9;
                border-radius: 4px;
            }
            .slm-tool-item h4 {
                margin: 0 0 8px;
            }
            .slm-tool-item p {
                margin: 0 0 12px;
                color: #666;
                font-size: 13px;
            }
            .slm-key-display {
                background: #f1f1f1;
                padding: 10px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
                word-break: break-all;
                margin-top: 10px;
            }
        </style>
        <?php
    }
    
    /**
     * Render system status
     */
    private static function render_system_status() {
        global $wpdb;
        
        $checks = [];
        
        // Database tables
        $tables = [
            'viewing_sessions' => $wpdb->prefix . 'slm_viewing_sessions',
            'share_access_log' => $wpdb->prefix . 'slm_share_access_log',
            'document_versions' => $wpdb->prefix . 'slm_document_versions',
            'signing_fields' => $wpdb->prefix . 'slm_signing_fields',
        ];
        
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
            $checks[] = [
                'label' => sprintf(__('Table: %s', 'flavor'), $name),
                'status' => $exists ? 'success' : 'error',
                'value' => $exists ? __('Exists', 'flavor') : __('Missing', 'flavor'),
            ];
        }
        
        // Storage directory
        $storage_path = SLM_DMS::get_storage_path();
        $storage_exists = file_exists($storage_path);
        $storage_writable = $storage_exists && is_writable($storage_path);
        
        $checks[] = [
            'label' => __('Storage Directory', 'flavor'),
            'status' => $storage_writable ? 'success' : ($storage_exists ? 'warning' : 'error'),
            'value' => $storage_writable ? __('Writable', 'flavor') : ($storage_exists ? __('Not writable', 'flavor') : __('Not created', 'flavor')),
        ];
        
        // .htaccess protection
        $htaccess = dirname($storage_path) . '/.htaccess';
        $htaccess_exists = file_exists($htaccess);
        
        $checks[] = [
            'label' => __('.htaccess Protection', 'flavor'),
            'status' => $htaccess_exists ? 'success' : 'error',
            'value' => $htaccess_exists ? __('Protected', 'flavor') : __('Not protected', 'flavor'),
        ];
        
        // Encryption key
        $has_key = defined('SLM_MASTER_ENCRYPTION_KEY') || get_option('slm_encryption_key');
        $key_in_config = defined('SLM_MASTER_ENCRYPTION_KEY');
        
        $checks[] = [
            'label' => __('Encryption Key', 'flavor'),
            'status' => $has_key ? ($key_in_config ? 'success' : 'warning') : 'error',
            'value' => $has_key ? ($key_in_config ? __('In config (secure)', 'flavor') : __('In database', 'flavor')) : __('Not configured', 'flavor'),
        ];
        
        // OpenSSL
        $openssl = extension_loaded('openssl');
        $checks[] = [
            'label' => __('OpenSSL Extension', 'flavor'),
            'status' => $openssl ? 'success' : 'error',
            'value' => $openssl ? __('Installed', 'flavor') : __('Not installed', 'flavor'),
        ];
        
        // Permalinks
        $permalinks = get_option('permalink_structure');
        $checks[] = [
            'label' => __('Permalinks', 'flavor'),
            'status' => $permalinks ? 'success' : 'error',
            'value' => $permalinks ? __('Enabled', 'flavor') : __('Disabled (required)', 'flavor'),
        ];
        
        // Document count
        $doc_count = wp_count_posts('slm_document');
        $checks[] = [
            'label' => __('Documents', 'flavor'),
            'status' => 'success',
            'value' => sprintf(__('%d total', 'flavor'), $doc_count->publish ?? 0),
        ];
        
        // Envelope count
        $envelope_count = wp_count_posts('slm_envelope');
        $checks[] = [
            'label' => __('Signing Envelopes', 'flavor'),
            'status' => 'success',
            'value' => sprintf(__('%d total', 'flavor'), $envelope_count->publish ?? 0),
        ];
        
        echo '<div class="slm-status-grid">';
        foreach ($checks as $check) {
            $icon = $check['status'] === 'success' ? '✓' : ($check['status'] === 'warning' ? '⚠' : '✕');
            ?>
            <div class="slm-status-item">
                <span class="slm-status-icon <?php echo esc_attr($check['status']); ?>"><?php echo $icon; ?></span>
                <div>
                    <div class="slm-status-label"><?php echo esc_html($check['label']); ?></div>
                    <div class="slm-status-value"><?php echo esc_html($check['value']); ?></div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }
    
    /**
     * Render tools section
     */
    private static function render_tools() {
        ?>
        <div class="slm-tools-grid">
            <div class="slm-tool-item">
                <h4><?php esc_html_e('Flush Rewrite Rules', 'flavor'); ?></h4>
                <p><?php esc_html_e('Refresh URL routes for document viewer and signing pages.', 'flavor'); ?></p>
                <button type="button" class="button" id="slm-flush-rewrite">
                    <?php esc_html_e('Flush Rules', 'flavor'); ?>
                </button>
            </div>
            
            <div class="slm-tool-item">
                <h4><?php esc_html_e('Create Storage Directories', 'flavor'); ?></h4>
                <p><?php esc_html_e('Create secure storage folders with .htaccess protection.', 'flavor'); ?></p>
                <button type="button" class="button" id="slm-create-directories">
                    <?php esc_html_e('Create Directories', 'flavor'); ?>
                </button>
            </div>
            
            <div class="slm-tool-item">
                <h4><?php esc_html_e('Generate Encryption Key', 'flavor'); ?></h4>
                <p><?php esc_html_e('Generate a new encryption key. Add to wp-config.php for security.', 'flavor'); ?></p>
                <button type="button" class="button" id="slm-generate-key">
                    <?php esc_html_e('Generate Key', 'flavor'); ?>
                </button>
                <div class="slm-key-display" id="slm-key-output" style="display:none;"></div>
            </div>
            
            <div class="slm-tool-item">
                <h4><?php esc_html_e('Create Default Categories', 'flavor'); ?></h4>
                <p><?php esc_html_e('Create standard document categories for legal documents.', 'flavor'); ?></p>
                <button type="button" class="button" id="slm-create-categories">
                    <?php esc_html_e('Create Categories', 'flavor'); ?>
                </button>
            </div>
        </div>
        
        <script>
        jQuery(function($) {
            $('#slm-flush-rewrite').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('<?php echo esc_js(__('Flushing...', 'flavor')); ?>');
                
                $.post(ajaxurl, {
                    action: 'slm_dms_flush_rewrite',
                    nonce: '<?php echo wp_create_nonce('slm_dms_tools'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('<?php echo esc_js(__('Flush Rules', 'flavor')); ?>');
                    alert(response.success ? '<?php echo esc_js(__('Rewrite rules flushed!', 'flavor')); ?>' : '<?php echo esc_js(__('Failed', 'flavor')); ?>');
                });
            });
            
            $('#slm-create-directories').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('<?php echo esc_js(__('Creating...', 'flavor')); ?>');
                
                $.post(ajaxurl, {
                    action: 'slm_dms_create_directories',
                    nonce: '<?php echo wp_create_nonce('slm_dms_tools'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('<?php echo esc_js(__('Create Directories', 'flavor')); ?>');
                    alert(response.success ? '<?php echo esc_js(__('Directories created!', 'flavor')); ?>' : response.data.message);
                    if (response.success) location.reload();
                });
            });
            
            $('#slm-generate-key').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('<?php echo esc_js(__('Generating...', 'flavor')); ?>');
                
                $.post(ajaxurl, {
                    action: 'slm_dms_generate_key',
                    nonce: '<?php echo wp_create_nonce('slm_dms_tools'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('<?php echo esc_js(__('Generate Key', 'flavor')); ?>');
                    if (response.success) {
                        $('#slm-key-output').show().html(
                            '<strong><?php echo esc_js(__('Add to wp-config.php:', 'flavor')); ?></strong><br><br>' +
                            "define('SLM_MASTER_ENCRYPTION_KEY', '" + response.data.key + "');"
                        );
                    }
                });
            });
            
            $('#slm-create-categories').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('<?php echo esc_js(__('Creating...', 'flavor')); ?>');
                
                $.post(ajaxurl, {
                    action: 'slm_dms_create_categories',
                    nonce: '<?php echo wp_create_nonce('slm_dms_tools'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('<?php echo esc_js(__('Create Categories', 'flavor')); ?>');
                    alert(response.success ? '<?php echo esc_js(__('Categories created!', 'flavor')); ?>' : '<?php echo esc_js(__('Failed', 'flavor')); ?>');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Section callbacks
     */
    public static function render_storage_section() {
        echo '<p>' . esc_html__('Configure where and how documents are stored.', 'flavor') . '</p>';
    }
    
    public static function render_viewer_section() {
        echo '<p>' . esc_html__('Settings for the secure document viewer.', 'flavor') . '</p>';
    }
    
    public static function render_sharing_section() {
        echo '<p>' . esc_html__('Default settings for external document sharing.', 'flavor') . '</p>';
    }
    
    public static function render_signing_section() {
        echo '<p>' . esc_html__('Default settings for document signing envelopes.', 'flavor') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public static function render_storage_type_field() {
        $value = get_option('slm_dms_storage_type', 'local');
        ?>
        <select name="slm_dms_storage_type">
            <option value="local" <?php selected($value, 'local'); ?>><?php esc_html_e('Local (wp-content/uploads)', 'flavor'); ?></option>
            <option value="external" <?php selected($value, 'external'); ?> disabled><?php esc_html_e('External Path (coming soon)', 'flavor'); ?></option>
        </select>
        <?php
    }
    
    public static function render_session_hours_field() {
        $value = get_option('slm_dms_viewer_session_hours', 4);
        ?>
        <input type="number" name="slm_dms_viewer_session_hours" value="<?php echo esc_attr($value); ?>" min="1" max="24" style="width:80px;">
        <span class="description"><?php esc_html_e('hours', 'flavor'); ?></span>
        <?php
    }
    
    public static function render_track_reading_field() {
        $value = get_option('slm_dms_track_reading', '1');
        ?>
        <label>
            <input type="checkbox" name="slm_dms_track_reading" value="1" <?php checked($value, '1'); ?>>
            <?php esc_html_e('Track pages viewed and time spent reading', 'flavor'); ?>
        </label>
        <?php
    }
    
    public static function render_share_expiry_field() {
        $value = get_option('slm_dms_default_share_expiry', 168);
        ?>
        <input type="number" name="slm_dms_default_share_expiry" value="<?php echo esc_attr($value); ?>" min="1" max="720" style="width:80px;">
        <span class="description"><?php esc_html_e('hours (168 = 7 days)', 'flavor'); ?></span>
        <?php
    }
    
    public static function render_download_allowed_field() {
        $value = get_option('slm_dms_default_download_allowed', '0');
        ?>
        <label>
            <input type="checkbox" name="slm_dms_default_download_allowed" value="1" <?php checked($value, '1'); ?>>
            <?php esc_html_e('Allow downloads by default for shared links', 'flavor'); ?>
        </label>
        <?php
    }
    
    public static function render_envelope_expiry_field() {
        $value = get_option('slm_dms_default_envelope_expiry', 14);
        ?>
        <input type="number" name="slm_dms_default_envelope_expiry" value="<?php echo esc_attr($value); ?>" min="1" max="90" style="width:80px;">
        <span class="description"><?php esc_html_e('days', 'flavor'); ?></span>
        <?php
    }
    
    public static function render_signing_mode_field() {
        $value = get_option('slm_dms_default_signing_mode', 'sequential');
        ?>
        <select name="slm_dms_default_signing_mode">
            <option value="sequential" <?php selected($value, 'sequential'); ?>><?php esc_html_e('Sequential (one at a time)', 'flavor'); ?></option>
            <option value="parallel" <?php selected($value, 'parallel'); ?>><?php esc_html_e('Parallel (all at once)', 'flavor'); ?></option>
        </select>
        <?php
    }
    
    /**
     * AJAX: Flush rewrite rules
     */
    public static function ajax_flush_rewrite() {
        check_ajax_referer('slm_dms_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        flush_rewrite_rules();
        wp_send_json_success();
    }
    
    /**
     * AJAX: Create directories
     */
    public static function ajax_create_directories() {
        check_ajax_referer('slm_dms_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $upload_dir = wp_upload_dir();
        $hash1 = get_option('slm_storage_hash_1');
        $hash2 = get_option('slm_storage_hash_2');
        
        if (!$hash1) {
            $hash1 = wp_generate_password(8, false);
            update_option('slm_storage_hash_1', $hash1);
        }
        
        if (!$hash2) {
            $hash2 = wp_generate_password(8, false);
            update_option('slm_storage_hash_2', $hash2);
        }
        
        $base_path = $upload_dir['basedir'] . '/private/' . $hash1 . '/' . $hash2 . '/docs';
        
        if (!wp_mkdir_p($base_path)) {
            wp_send_json_error(['message' => __('Failed to create directories.', 'flavor')]);
        }
        
        // Add .htaccess
        $htaccess = dirname($base_path) . '/.htaccess';
        file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
        
        // Add index.php
        file_put_contents(dirname($base_path) . '/index.php', '<?php // Silence is golden');
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: Generate encryption key
     */
    public static function ajax_generate_key() {
        check_ajax_referer('slm_dms_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $key = 'base64:' . base64_encode(random_bytes(32));
        
        // Store in database as backup
        update_option('slm_encryption_key', base64_encode(random_bytes(32)));
        
        wp_send_json_success(['key' => $key]);
    }
    
    /**
     * AJAX: Create default categories
     */
    public static function ajax_create_categories() {
        check_ajax_referer('slm_dms_tools', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $categories = [
            'contracts' => __('Contracts & Agreements', 'flavor'),
            'identity' => __('Identity Documents', 'flavor'),
            'correspondence' => __('Correspondence', 'flavor'),
            'court' => __('Court Documents', 'flavor'),
            'financial' => __('Financial Documents', 'flavor'),
            'immigration' => __('Immigration Documents', 'flavor'),
            'citizenship' => __('Citizenship Documents', 'flavor'),
            'corporate' => __('Corporate Documents', 'flavor'),
            'property' => __('Property Documents', 'flavor'),
            'other' => __('Other', 'flavor'),
        ];
        
        foreach ($categories as $slug => $name) {
            if (!term_exists($slug, 'slm_doc_category')) {
                wp_insert_term($name, 'slm_doc_category', ['slug' => $slug]);
            }
        }
        
        wp_send_json_success();
    }
    
    /**
     * Get setting value
     */
    public static function get($key, $default = '') {
        return get_option('slm_dms_' . $key, $default);
    }
}
