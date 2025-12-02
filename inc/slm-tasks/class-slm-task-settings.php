<?php
/**
 * SLM Task Settings
 * 
 * Admin settings page for task system configuration.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_Settings {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }
    
    public static function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=slm_task_list',
            __('Task Settings', 'flavor'),
            __('Settings', 'flavor'),
            'manage_options',
            'slm-task-settings',
            [__CLASS__, 'render_settings_page']
        );
    }
    
    public static function register_settings() {
        // Service Tiers
        register_setting('slm_task_settings', 'slm_service_tier_config', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_tier_config']
        ]);
        
        // Notification Settings
        register_setting('slm_task_settings', 'slm_notification_config', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_notification_config']
        ]);
        
        // Onboarding Settings
        register_setting('slm_task_settings', 'slm_onboarding_config', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_onboarding_config']
        ]);
        
        // Audit Settings
        register_setting('slm_task_settings', 'slm_audit_retention_config', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_audit_config']
        ]);
        
        // Case Types
        register_setting('slm_task_settings', 'slm_case_type_config', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize_case_type_config']
        ]);
    }
    
    public static function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'tiers';
        
        $tabs = [
            'tiers' => __('Service Tiers', 'flavor'),
            'notifications' => __('Notifications', 'flavor'),
            'onboarding' => __('Client Onboarding', 'flavor'),
            'audit' => __('Audit & Compliance', 'flavor'),
            'case_types' => __('Case Types', 'flavor'),
        ];
        ?>
        <div class="wrap">
            <h1><?php _e('Task System Settings', 'flavor'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <?php foreach ($tabs as $tab => $label): ?>
                    <a href="<?php echo admin_url('edit.php?post_type=slm_task_list&page=slm-task-settings&tab=' . $tab); ?>" 
                       class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <form method="post" action="options.php">
                <?php settings_fields('slm_task_settings'); ?>
                
                <div class="slm-settings-content" style="max-width:800px;margin-top:20px;">
                    <?php
                    switch ($active_tab) {
                        case 'tiers':
                            self::render_tier_settings();
                            break;
                        case 'notifications':
                            self::render_notification_settings();
                            break;
                        case 'onboarding':
                            self::render_onboarding_settings();
                            break;
                        case 'audit':
                            self::render_audit_settings();
                            break;
                        case 'case_types':
                            self::render_case_type_settings();
                            break;
                    }
                    ?>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    private static function render_tier_settings() {
        $config = get_option('slm_service_tier_config', []);
        
        $defaults = [
            'standard' => ['label' => 'Standard', 'multiplier' => 1.0, 'working_week' => 5],
            'fast' => ['label' => 'Fast Track', 'multiplier' => 0.5, 'working_week' => 5],
            'expedited' => ['label' => 'Expedited', 'multiplier' => 0.25, 'working_week' => 7],
        ];
        
        $config = wp_parse_args($config, $defaults);
        ?>
        <h2><?php _e('Service Tier Configuration', 'flavor'); ?></h2>
        <p class="description">
            <?php _e('Configure timeline multipliers for different service levels. Lower multipliers = faster deadlines.', 'flavor'); ?>
        </p>
        
        <table class="form-table">
            <?php foreach ($config as $key => $tier): ?>
                <tr>
                    <th colspan="4">
                        <h3 style="margin:0;"><?php echo esc_html($tier['label']); ?></h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Display Label', 'flavor'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="slm_service_tier_config[<?php echo esc_attr($key); ?>][label]" 
                               value="<?php echo esc_attr($tier['label']); ?>" 
                               class="regular-text">
                    </td>
                    <th scope="row">
                        <label><?php _e('Multiplier', 'flavor'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="slm_service_tier_config[<?php echo esc_attr($key); ?>][multiplier]" 
                               value="<?php echo esc_attr($tier['multiplier']); ?>" 
                               step="0.05" min="0.1" max="2.0" 
                               class="small-text">
                        <span class="description">x</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Working Week', 'flavor'); ?></label>
                    </th>
                    <td colspan="3">
                        <select name="slm_service_tier_config[<?php echo esc_attr($key); ?>][working_week]">
                            <option value="5" <?php selected($tier['working_week'], 5); ?>>
                                <?php _e('5 days (Mon-Fri)', 'flavor'); ?>
                            </option>
                            <option value="6" <?php selected($tier['working_week'], 6); ?>>
                                <?php _e('6 days (Mon-Sat)', 'flavor'); ?>
                            </option>
                            <option value="7" <?php selected($tier['working_week'], 7); ?>>
                                <?php _e('7 days (all week)', 'flavor'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        
        <div class="slm-tier-example" style="background:#f6f7f7;padding:15px;margin-top:20px;border:1px solid #ddd;">
            <h4 style="margin-top:0;"><?php _e('Example Calculation', 'flavor'); ?></h4>
            <p><?php _e('For a task with 10 standard working days:', 'flavor'); ?></p>
            <ul>
                <li><strong>Standard (1.0x):</strong> 10 working days</li>
                <li><strong>Fast Track (0.5x):</strong> 5 working days</li>
                <li><strong>Expedited (0.25x):</strong> 3 working days (rounded up)</li>
            </ul>
        </div>
        <?php
    }
    
    private static function render_notification_settings() {
        $config = get_option('slm_notification_config', []);
        
        $defaults = [
            'enable_email_notifications' => true,
            'task_due_warning_days' => [3, 1],
            'escalation_days' => [1, 3, 7, 14],
            'digest_time' => '09:00',
            'send_to_supervisors_at_level' => 3,
        ];
        
        $config = wp_parse_args($config, $defaults);
        ?>
        <h2><?php _e('Notification Settings', 'flavor'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Email Notifications', 'flavor'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="slm_notification_config[enable_email_notifications]" 
                               value="1" 
                               <?php checked($config['enable_email_notifications']); ?>>
                        <?php _e('Enable email notifications for task events', 'flavor'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Due Date Warnings', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="slm_notification_config[task_due_warning_days]" 
                           value="<?php echo esc_attr(implode(', ', $config['task_due_warning_days'])); ?>" 
                           class="regular-text">
                    <p class="description">
                        <?php _e('Days before due date to send warnings (comma-separated). e.g., "3, 1"', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Escalation Schedule', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="slm_notification_config[escalation_days]" 
                           value="<?php echo esc_attr(implode(', ', $config['escalation_days'])); ?>" 
                           class="regular-text">
                    <p class="description">
                        <?php _e('Days overdue for each escalation level (comma-separated). e.g., "1, 3, 7, 14"', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Daily Digest Time', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="time" 
                           name="slm_notification_config[digest_time]" 
                           value="<?php echo esc_attr($config['digest_time']); ?>">
                    <p class="description">
                        <?php _e('Time to send daily digest emails (for users who prefer digest mode).', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Supervisor Escalation', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_notification_config[send_to_supervisors_at_level]">
                        <option value="2" <?php selected($config['send_to_supervisors_at_level'], 2); ?>>
                            <?php _e('Level 2', 'flavor'); ?>
                        </option>
                        <option value="3" <?php selected($config['send_to_supervisors_at_level'], 3); ?>>
                            <?php _e('Level 3', 'flavor'); ?>
                        </option>
                        <option value="4" <?php selected($config['send_to_supervisors_at_level'], 4); ?>>
                            <?php _e('Level 4', 'flavor'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Escalation level at which to notify supervisors.', 'flavor'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private static function render_onboarding_settings() {
        $config = get_option('slm_onboarding_config', []);
        
        $defaults = [
            'max_failed_attempts' => 5,
            'lockout_minutes' => 30,
            'max_lockouts_24h' => 3,
            'meeting_date_tolerance_days' => 2,
            'first_login_link_expiry_hours' => 72,
        ];
        
        $config = wp_parse_args($config, $defaults);
        ?>
        <h2><?php _e('Client Onboarding Settings', 'flavor'); ?></h2>
        <p class="description">
            <?php _e('Configure security settings for client portal access.', 'flavor'); ?>
        </p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Max Failed PIN Attempts', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_onboarding_config[max_failed_attempts]" 
                           value="<?php echo esc_attr($config['max_failed_attempts']); ?>" 
                           min="3" max="10" class="small-text">
                    <p class="description">
                        <?php _e('Number of failed attempts before account lockout.', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Lockout Duration', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_onboarding_config[lockout_minutes]" 
                           value="<?php echo esc_attr($config['lockout_minutes']); ?>" 
                           min="5" max="120" class="small-text">
                    <?php _e('minutes', 'flavor'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Max Lockouts per 24h', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_onboarding_config[max_lockouts_24h]" 
                           value="<?php echo esc_attr($config['max_lockouts_24h']); ?>" 
                           min="1" max="10" class="small-text">
                    <p class="description">
                        <?php _e('After this many lockouts, manual reset required.', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Meeting Date Tolerance', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_onboarding_config[meeting_date_tolerance_days]" 
                           value="<?php echo esc_attr($config['meeting_date_tolerance_days']); ?>" 
                           min="0" max="7" class="small-text">
                    <?php _e('days', 'flavor'); ?>
                    <p class="description">
                        <?php _e('Tolerance for meeting date verification (±days).', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('First Login Link Expiry', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_onboarding_config[first_login_link_expiry_hours]" 
                           value="<?php echo esc_attr($config['first_login_link_expiry_hours']); ?>" 
                           min="24" max="168" class="small-text">
                    <?php _e('hours', 'flavor'); ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private static function render_audit_settings() {
        $config = get_option('slm_audit_retention_config', []);
        
        $defaults = [
            'minimum_retention_years' => 7,
            'maximum_retention_years' => 10,
        ];
        
        $config = wp_parse_args($config, $defaults);
        
        global $wpdb;
        $table = $wpdb->prefix . 'slm_audit_log';
        $log_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $oldest_log = $wpdb->get_var("SELECT MIN(created_at) FROM {$table}");
        ?>
        <h2><?php _e('Audit & Compliance Settings', 'flavor'); ?></h2>
        
        <div class="slm-audit-stats" style="background:#f6f7f7;padding:15px;margin-bottom:20px;border:1px solid #ddd;">
            <h4 style="margin-top:0;"><?php _e('Audit Log Statistics', 'flavor'); ?></h4>
            <p>
                <strong><?php _e('Total Log Entries:', 'flavor'); ?></strong> 
                <?php echo number_format_i18n($log_count ?: 0); ?>
            </p>
            <p>
                <strong><?php _e('Oldest Entry:', 'flavor'); ?></strong> 
                <?php echo $oldest_log ? date('d/m/Y', strtotime($oldest_log)) : '-'; ?>
            </p>
        </div>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Minimum Retention', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_audit_retention_config[minimum_retention_years]" 
                           value="<?php echo esc_attr($config['minimum_retention_years']); ?>" 
                           min="5" max="15" class="small-text">
                    <?php _e('years', 'flavor'); ?>
                    <p class="description">
                        <?php _e('Legal minimum for audit log retention (typically 7 years for legal services).', 'flavor'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Maximum Retention', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="slm_audit_retention_config[maximum_retention_years]" 
                           value="<?php echo esc_attr($config['maximum_retention_years']); ?>" 
                           min="5" max="20" class="small-text">
                    <?php _e('years', 'flavor'); ?>
                    <p class="description">
                        <?php _e('GDPR compliance - logs older than this will be automatically deleted.', 'flavor'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Log Cleanup', 'flavor'); ?></h3>
        <p>
            <?php _e('Automatic cleanup runs daily. Logs older than the minimum retention period are removed.', 'flavor'); ?>
        </p>
        <?php
    }
    
    private static function render_case_type_settings() {
        $config = get_option('slm_case_type_config', []);
        
        $defaults = [
            'CIT' => ['label' => 'Citizenship', 'color' => '#4c6ef5'],
            'VISA' => ['label' => 'Visa', 'color' => '#37b24d'],
            'IMM' => ['label' => 'Immigration', 'color' => '#f59f00'],
            'PROB' => ['label' => 'Probate', 'color' => '#ae3ec9'],
            'CORP' => ['label' => 'Corporate', 'color' => '#1098ad'],
            'PROP' => ['label' => 'Property', 'color' => '#e67700'],
        ];
        
        $config = wp_parse_args($config, $defaults);
        ?>
        <h2><?php _e('Case Type Configuration', 'flavor'); ?></h2>
        <p class="description">
            <?php _e('Configure case types and their display colors.', 'flavor'); ?>
        </p>
        
        <table class="widefat" style="max-width:600px;">
            <thead>
                <tr>
                    <th><?php _e('Code', 'flavor'); ?></th>
                    <th><?php _e('Label', 'flavor'); ?></th>
                    <th><?php _e('Color', 'flavor'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="slm-case-types">
                <?php foreach ($config as $code => $type): ?>
                    <tr>
                        <td>
                            <input type="text" 
                                   name="slm_case_type_config[<?php echo esc_attr($code); ?>][code]" 
                                   value="<?php echo esc_attr($code); ?>" 
                                   class="small-text" 
                                   style="text-transform:uppercase" 
                                   maxlength="6">
                        </td>
                        <td>
                            <input type="text" 
                                   name="slm_case_type_config[<?php echo esc_attr($code); ?>][label]" 
                                   value="<?php echo esc_attr($type['label']); ?>" 
                                   class="regular-text">
                        </td>
                        <td>
                            <input type="color" 
                                   name="slm_case_type_config[<?php echo esc_attr($code); ?>][color]" 
                                   value="<?php echo esc_attr($type['color'] ?? '#666666'); ?>">
                        </td>
                        <td>
                            <button type="button" class="button button-small slm-remove-type">
                                <?php _e('Remove', 'flavor'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top:10px;">
            <button type="button" class="button" id="slm-add-case-type">
                <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle"></span>
                <?php _e('Add Case Type', 'flavor'); ?>
            </button>
        </p>
        
        <script>
        jQuery(function($) {
            var typeIndex = <?php echo count($config); ?>;
            
            $('#slm-add-case-type').on('click', function() {
                var newCode = 'NEW' + typeIndex;
                var html = '<tr>' +
                    '<td><input type="text" name="slm_case_type_config[' + newCode + '][code]" value="" class="small-text" style="text-transform:uppercase" maxlength="6"></td>' +
                    '<td><input type="text" name="slm_case_type_config[' + newCode + '][label]" value="" class="regular-text"></td>' +
                    '<td><input type="color" name="slm_case_type_config[' + newCode + '][color]" value="#666666"></td>' +
                    '<td><button type="button" class="button button-small slm-remove-type"><?php _e("Remove", "flavor"); ?></button></td>' +
                    '</tr>';
                $('#slm-case-types').append(html);
                typeIndex++;
            });
            
            $(document).on('click', '.slm-remove-type', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }
    
    // Sanitization callbacks
    
    public static function sanitize_tier_config($input) {
        $output = [];
        
        foreach ($input as $key => $tier) {
            $key = sanitize_key($key);
            $output[$key] = [
                'label' => sanitize_text_field($tier['label'] ?? ''),
                'multiplier' => max(0.1, min(2.0, floatval($tier['multiplier'] ?? 1.0))),
                'working_week' => in_array(intval($tier['working_week']), [5, 6, 7]) ? intval($tier['working_week']) : 5
            ];
        }
        
        return $output;
    }
    
    public static function sanitize_notification_config($input) {
        $output = [];
        
        $output['enable_email_notifications'] = !empty($input['enable_email_notifications']);
        
        if (!empty($input['task_due_warning_days'])) {
            $days = array_map('intval', array_filter(explode(',', $input['task_due_warning_days'])));
            $output['task_due_warning_days'] = array_values(array_filter($days, function($d) { return $d > 0; }));
        } else {
            $output['task_due_warning_days'] = [3, 1];
        }
        
        if (!empty($input['escalation_days'])) {
            $days = array_map('intval', array_filter(explode(',', $input['escalation_days'])));
            $output['escalation_days'] = array_values(array_filter($days, function($d) { return $d > 0; }));
        } else {
            $output['escalation_days'] = [1, 3, 7, 14];
        }
        
        $output['digest_time'] = sanitize_text_field($input['digest_time'] ?? '09:00');
        $output['send_to_supervisors_at_level'] = max(2, min(4, intval($input['send_to_supervisors_at_level'] ?? 3)));
        
        return $output;
    }
    
    public static function sanitize_onboarding_config($input) {
        return [
            'max_failed_attempts' => max(3, min(10, intval($input['max_failed_attempts'] ?? 5))),
            'lockout_minutes' => max(5, min(120, intval($input['lockout_minutes'] ?? 30))),
            'max_lockouts_24h' => max(1, min(10, intval($input['max_lockouts_24h'] ?? 3))),
            'meeting_date_tolerance_days' => max(0, min(7, intval($input['meeting_date_tolerance_days'] ?? 2))),
            'first_login_link_expiry_hours' => max(24, min(168, intval($input['first_login_link_expiry_hours'] ?? 72))),
        ];
    }
    
    public static function sanitize_audit_config($input) {
        $min = max(5, min(15, intval($input['minimum_retention_years'] ?? 7)));
        $max = max($min, min(20, intval($input['maximum_retention_years'] ?? 10)));
        
        return [
            'minimum_retention_years' => $min,
            'maximum_retention_years' => $max,
        ];
    }
    
    public static function sanitize_case_type_config($input) {
        $output = [];
        
        foreach ($input as $old_key => $type) {
            $code = strtoupper(sanitize_key($type['code'] ?? $old_key));
            if (empty($code)) continue;
            
            $output[$code] = [
                'label' => sanitize_text_field($type['label'] ?? $code),
                'color' => sanitize_hex_color($type['color'] ?? '#666666') ?: '#666666'
            ];
        }
        
        return $output;
    }
}
