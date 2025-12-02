<?php
/**
 * SLM Task Templates
 * 
 * Manages reusable task template definitions.
 * Supports different task types with type-specific settings.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_Templates {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_slm_task_template', [__CLASS__, 'save_meta_boxes'], 10, 2);
        add_filter('manage_slm_task_template_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_slm_task_template_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_filter('manage_edit-slm_task_template_sortable_columns', [__CLASS__, 'sortable_columns']);
        
        // AJAX for type-specific fields
        add_action('wp_ajax_slm_get_type_fields', [__CLASS__, 'ajax_get_type_fields']);
    }
    
    public static function add_meta_boxes() {
        add_meta_box(
            'slm_task_template_settings',
            __('Task Settings', 'flavor'),
            [__CLASS__, 'render_settings_meta_box'],
            'slm_task_template',
            'normal',
            'high'
        );
        
        add_meta_box(
            'slm_task_template_type',
            __('Task Type Settings', 'flavor'),
            [__CLASS__, 'render_type_meta_box'],
            'slm_task_template',
            'normal',
            'default'
        );
        
        add_meta_box(
            'slm_task_template_timeline',
            __('Timeline Settings', 'flavor'),
            [__CLASS__, 'render_timeline_meta_box'],
            'slm_task_template',
            'side',
            'default'
        );
        
        add_meta_box(
            'slm_task_template_usage',
            __('Usage', 'flavor'),
            [__CLASS__, 'render_usage_meta_box'],
            'slm_task_template',
            'side',
            'low'
        );
    }
    
    public static function render_settings_meta_box($post) {
        wp_nonce_field('slm_task_template_settings', 'slm_task_template_nonce');
        
        $task_type = get_post_meta($post->ID, '_slm_task_type', true) ?: 'checkbox';
        $description = get_post_meta($post->ID, '_slm_task_description', true);
        $assigned_role = get_post_meta($post->ID, '_slm_default_assigned_role', true) ?: 'client';
        
        $types = SLM_Tasks::get_task_types();
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="slm_task_type"><?php _e('Task Type', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_task_type" id="slm_task_type" class="regular-text">
                        <?php foreach ($types as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($task_type, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Determines how the task is completed.', 'flavor'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_task_description"><?php _e('Instructions', 'flavor'); ?></label>
                </th>
                <td>
                    <?php
                    wp_editor($description, 'slm_task_description', [
                        'textarea_name' => 'slm_task_description',
                        'textarea_rows' => 6,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => ['buttons' => 'strong,em,link,ul,ol,li']
                    ]);
                    ?>
                    <p class="description"><?php _e('Instructions shown to the user when completing this task.', 'flavor'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_assigned_role"><?php _e('Default Assigned To', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_assigned_role" id="slm_assigned_role">
                        <option value="client" <?php selected($assigned_role, 'client'); ?>><?php _e('Client', 'flavor'); ?></option>
                        <option value="lawyer" <?php selected($assigned_role, 'lawyer'); ?>><?php _e('Lawyer', 'flavor'); ?></option>
                    </select>
                    <p class="description"><?php _e('Who typically completes this task.', 'flavor'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_type_meta_box($post) {
        $task_type = get_post_meta($post->ID, '_slm_task_type', true) ?: 'checkbox';
        ?>
        <div id="slm-type-fields-container">
            <?php self::render_type_fields($post->ID, $task_type); ?>
        </div>
        
        <script>
        jQuery(function($) {
            $('#slm_task_type').on('change', function() {
                var type = $(this).val();
                var postId = <?php echo $post->ID; ?>;
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_get_type_fields',
                        nonce: '<?php echo wp_create_nonce('slm_tasks_admin'); ?>',
                        post_id: postId,
                        task_type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#slm-type-fields-container').html(response.data.html);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public static function render_type_fields($post_id, $task_type) {
        switch ($task_type) {
            case 'checkbox':
                echo '<p class="description">' . __('Simple checkbox task - user clicks to mark complete.', 'flavor') . '</p>';
                break;
                
            case 'upload':
                self::render_upload_fields($post_id);
                break;
                
            case 'form':
                self::render_form_fields($post_id);
                break;
                
            case 'payment':
                self::render_payment_fields($post_id);
                break;
                
            case 'signature':
                self::render_signature_fields($post_id);
                break;
                
            case 'external':
                self::render_external_fields($post_id);
                break;
        }
    }
    
    private static function render_upload_fields($post_id) {
        $allowed_types = get_post_meta($post_id, '_slm_allowed_file_types', true) ?: ['pdf', 'jpg', 'png'];
        $max_size = get_post_meta($post_id, '_slm_max_file_size_mb', true) ?: 10;
        $category = get_post_meta($post_id, '_slm_document_category', true);
        
        $all_types = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'doc', 'xlsx', 'xls'];
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Allowed File Types', 'flavor'); ?></th>
                <td>
                    <?php foreach ($all_types as $type): ?>
                        <label style="margin-right: 15px;">
                            <input type="checkbox" 
                                   name="slm_allowed_file_types[]" 
                                   value="<?php echo esc_attr($type); ?>"
                                   <?php checked(in_array($type, (array)$allowed_types)); ?>>
                            <?php echo strtoupper($type); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_max_file_size"><?php _e('Max File Size (MB)', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" name="slm_max_file_size" id="slm_max_file_size" 
                           value="<?php echo esc_attr($max_size); ?>" min="1" max="100" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_document_category"><?php _e('Document Category', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="text" name="slm_document_category" id="slm_document_category" 
                           value="<?php echo esc_attr($category); ?>" class="regular-text">
                    <p class="description"><?php _e('e.g., "Passport", "Birth Certificate"', 'flavor'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private static function render_form_fields($post_id) {
        $form_id = get_post_meta($post_id, '_slm_gravity_form_id', true);
        $completion_trigger = get_post_meta($post_id, '_slm_completion_trigger', true) ?: 'on_submit';
        
        $forms = [];
        if (class_exists('GFAPI')) {
            $forms = GFAPI::get_forms();
        }
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="slm_gravity_form_id"><?php _e('Gravity Form', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_gravity_form_id" id="slm_gravity_form_id" class="regular-text">
                        <option value=""><?php _e('Select a form...', 'flavor'); ?></option>
                        <?php foreach ($forms as $form): ?>
                            <option value="<?php echo esc_attr($form['id']); ?>" <?php selected($form_id, $form['id']); ?>>
                                <?php echo esc_html($form['title']); ?> (ID: <?php echo $form['id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($forms)): ?>
                        <p class="description" style="color:#d63638;">
                            <?php _e('Gravity Forms not found. Please install and activate Gravity Forms.', 'flavor'); ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_completion_trigger"><?php _e('Task Completes', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_completion_trigger" id="slm_completion_trigger">
                        <option value="on_submit" <?php selected($completion_trigger, 'on_submit'); ?>>
                            <?php _e('On Form Submit', 'flavor'); ?>
                        </option>
                        <option value="on_approval" <?php selected($completion_trigger, 'on_approval'); ?>>
                            <?php _e('On Lawyer Approval', 'flavor'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Whether task completes immediately or requires lawyer review.', 'flavor'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private static function render_payment_fields($post_id) {
        $payment_type = get_post_meta($post_id, '_slm_payment_type', true) ?: 'fixed';
        $fixed_amount = get_post_meta($post_id, '_slm_fixed_amount', true);
        $currency = get_post_meta($post_id, '_slm_currency', true) ?: 'EUR';
        $stripe_enabled = get_post_meta($post_id, '_slm_stripe_enabled', true);
        $instructions = get_post_meta($post_id, '_slm_payment_instructions', true);
        
        if ($stripe_enabled === '') $stripe_enabled = true;
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="slm_payment_type"><?php _e('Payment Type', 'flavor'); ?></label>
                </th>
                <td>
                    <select name="slm_payment_type" id="slm_payment_type">
                        <option value="invoice" <?php selected($payment_type, 'invoice'); ?>><?php _e('From Invoice', 'flavor'); ?></option>
                        <option value="fixed" <?php selected($payment_type, 'fixed'); ?>><?php _e('Fixed Amount', 'flavor'); ?></option>
                        <option value="variable" <?php selected($payment_type, 'variable'); ?>><?php _e('Variable (set at creation)', 'flavor'); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="slm-fixed-amount-row" style="<?php echo $payment_type !== 'fixed' ? 'display:none' : ''; ?>">
                <th scope="row">
                    <label for="slm_fixed_amount"><?php _e('Amount', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" name="slm_fixed_amount" id="slm_fixed_amount" 
                           value="<?php echo esc_attr($fixed_amount); ?>" step="0.01" min="0" class="small-text">
                    <select name="slm_currency" id="slm_currency" style="width:auto">
                        <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR €</option>
                        <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP £</option>
                        <option value="USD" <?php selected($currency, 'USD'); ?>>USD $</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Payment Methods', 'flavor'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="slm_stripe_enabled" value="1" <?php checked($stripe_enabled); ?>>
                        <?php _e('Enable online payment (Stripe)', 'flavor'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_payment_instructions"><?php _e('Bank Transfer Instructions', 'flavor'); ?></label>
                </th>
                <td>
                    <textarea name="slm_payment_instructions" id="slm_payment_instructions" 
                              rows="4" class="large-text"><?php echo esc_textarea($instructions); ?></textarea>
                    <p class="description"><?php _e('Shown when client chooses bank transfer.', 'flavor'); ?></p>
                </td>
            </tr>
        </table>
        
        <script>
        jQuery(function($) {
            $('#slm_payment_type').on('change', function() {
                $('.slm-fixed-amount-row').toggle($(this).val() === 'fixed');
            });
        });
        </script>
        <?php
    }
    
    private static function render_signature_fields($post_id) {
        $document_template = get_post_meta($post_id, '_slm_document_template_id', true);
        $require_initials = get_post_meta($post_id, '_slm_require_initials', true);
        $witness_required = get_post_meta($post_id, '_slm_witness_required', true);
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="slm_document_template"><?php _e('Document Template ID', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="number" name="slm_document_template" id="slm_document_template" 
                           value="<?php echo esc_attr($document_template); ?>" class="small-text">
                    <p class="description"><?php _e('ID of the document template to generate for signing.', 'flavor'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Signature Options', 'flavor'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="slm_require_initials" value="1" <?php checked($require_initials); ?>>
                        <?php _e('Require initials on each page', 'flavor'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="slm_witness_required" value="1" <?php checked($witness_required); ?>>
                        <?php _e('Witness signature required', 'flavor'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private static function render_external_fields($post_id) {
        $instructions = get_post_meta($post_id, '_slm_external_instructions', true);
        $url = get_post_meta($post_id, '_slm_external_url', true);
        $require_proof = get_post_meta($post_id, '_slm_require_proof', true);
        $require_verification = get_post_meta($post_id, '_slm_require_lawyer_verification', true);
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="slm_external_instructions"><?php _e('Instructions', 'flavor'); ?></label>
                </th>
                <td>
                    <?php
                    wp_editor($instructions, 'slm_external_instructions', [
                        'textarea_name' => 'slm_external_instructions',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true
                    ]);
                    ?>
                    <p class="description"><?php _e('Instructions for completing the external action.', 'flavor'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="slm_external_url"><?php _e('External URL', 'flavor'); ?></label>
                </th>
                <td>
                    <input type="url" name="slm_external_url" id="slm_external_url" 
                           value="<?php echo esc_url($url); ?>" class="large-text">
                    <p class="description"><?php _e('Optional link to external website.', 'flavor'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Completion Requirements', 'flavor'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="slm_require_proof" value="1" <?php checked($require_proof); ?>>
                        <?php _e('Require proof upload (receipt, screenshot, etc.)', 'flavor'); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="slm_require_verification" value="1" <?php checked($require_verification); ?>>
                        <?php _e('Require lawyer verification', 'flavor'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public static function render_timeline_meta_box($post) {
        $working_days = get_post_meta($post->ID, '_slm_working_days_standard', true) ?: 5;
        $fast_override = get_post_meta($post->ID, '_slm_working_days_fast_override', true);
        $expedited_override = get_post_meta($post->ID, '_slm_working_days_expedited_override', true);
        $timing_anchor = get_post_meta($post->ID, '_slm_timing_anchor', true) ?: 'case_creation';
        ?>
        <p>
            <label for="slm_working_days"><?php _e('Standard Working Days:', 'flavor'); ?></label>
            <input type="number" name="slm_working_days" id="slm_working_days" 
                   value="<?php echo esc_attr($working_days); ?>" min="1" class="small-text" style="width:60px">
        </p>
        
        <p>
            <label for="slm_fast_override"><?php _e('Fast Tier Override:', 'flavor'); ?></label>
            <input type="number" name="slm_fast_override" id="slm_fast_override" 
                   value="<?php echo esc_attr($fast_override); ?>" min="1" class="small-text" style="width:60px">
            <span class="description"><?php _e('(optional)', 'flavor'); ?></span>
        </p>
        
        <p>
            <label for="slm_expedited_override"><?php _e('Expedited Override:', 'flavor'); ?></label>
            <input type="number" name="slm_expedited_override" id="slm_expedited_override" 
                   value="<?php echo esc_attr($expedited_override); ?>" min="1" class="small-text" style="width:60px">
            <span class="description"><?php _e('(optional)', 'flavor'); ?></span>
        </p>
        
        <hr>
        
        <p>
            <label for="slm_timing_anchor"><?php _e('Calculate Due Date From:', 'flavor'); ?></label>
            <select name="slm_timing_anchor" id="slm_timing_anchor" style="width:100%">
                <option value="case_creation" <?php selected($timing_anchor, 'case_creation'); ?>>
                    <?php _e('Case Creation', 'flavor'); ?>
                </option>
                <option value="previous_task" <?php selected($timing_anchor, 'previous_task'); ?>>
                    <?php _e('Previous Task Completion', 'flavor'); ?>
                </option>
                <option value="critical_deadline" <?php selected($timing_anchor, 'critical_deadline'); ?>>
                    <?php _e('Critical Deadline (minus days)', 'flavor'); ?>
                </option>
                <option value="manual" <?php selected($timing_anchor, 'manual'); ?>>
                    <?php _e('Manual Date Entry', 'flavor'); ?>
                </option>
            </select>
        </p>
        <?php
    }
    
    public static function render_usage_meta_box($post) {
        global $wpdb;
        
        // Count usage in task lists
        $list_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
             WHERE meta_key = 'tasks' AND meta_value LIKE %s",
            '%"task_template";i:' . $post->ID . '%'
        ));
        
        // Count active instances
        $instance_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p 
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'slm_task_instance' 
             AND p.post_status = 'publish' 
             AND pm.meta_key = '_slm_source_task_template' 
             AND pm.meta_value = %d",
            $post->ID
        ));
        ?>
        <p>
            <strong><?php _e('Used in Task Lists:', 'flavor'); ?></strong>
            <?php echo intval($list_count); ?>
        </p>
        <p>
            <strong><?php _e('Active Instances:', 'flavor'); ?></strong>
            <?php echo intval($instance_count); ?>
        </p>
        
        <?php if ($instance_count > 0): ?>
            <p class="description" style="color:#d63638;">
                <?php _e('Note: Changes will only affect new task instances, not existing ones.', 'flavor'); ?>
            </p>
        <?php endif;
    }
    
    public static function save_meta_boxes($post_id, $post) {
        if (!isset($_POST['slm_task_template_nonce'])) return;
        if (!wp_verify_nonce($_POST['slm_task_template_nonce'], 'slm_task_template_settings')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        // Core settings
        update_post_meta($post_id, '_slm_task_type', sanitize_text_field($_POST['slm_task_type'] ?? 'checkbox'));
        update_post_meta($post_id, '_slm_task_description', wp_kses_post($_POST['slm_task_description'] ?? ''));
        update_post_meta($post_id, '_slm_default_assigned_role', sanitize_text_field($_POST['slm_assigned_role'] ?? 'client'));
        
        // Timeline settings
        update_post_meta($post_id, '_slm_working_days_standard', intval($_POST['slm_working_days'] ?? 5));
        update_post_meta($post_id, '_slm_timing_anchor', sanitize_text_field($_POST['slm_timing_anchor'] ?? 'case_creation'));
        
        if (!empty($_POST['slm_fast_override'])) {
            update_post_meta($post_id, '_slm_working_days_fast_override', intval($_POST['slm_fast_override']));
        } else {
            delete_post_meta($post_id, '_slm_working_days_fast_override');
        }
        
        if (!empty($_POST['slm_expedited_override'])) {
            update_post_meta($post_id, '_slm_working_days_expedited_override', intval($_POST['slm_expedited_override']));
        } else {
            delete_post_meta($post_id, '_slm_working_days_expedited_override');
        }
        
        // Type-specific settings
        $task_type = $_POST['slm_task_type'] ?? 'checkbox';
        
        switch ($task_type) {
            case 'upload':
                $allowed = isset($_POST['slm_allowed_file_types']) ? array_map('sanitize_text_field', $_POST['slm_allowed_file_types']) : ['pdf'];
                update_post_meta($post_id, '_slm_allowed_file_types', $allowed);
                update_post_meta($post_id, '_slm_max_file_size_mb', intval($_POST['slm_max_file_size'] ?? 10));
                update_post_meta($post_id, '_slm_document_category', sanitize_text_field($_POST['slm_document_category'] ?? ''));
                break;
                
            case 'form':
                update_post_meta($post_id, '_slm_gravity_form_id', intval($_POST['slm_gravity_form_id'] ?? 0));
                update_post_meta($post_id, '_slm_completion_trigger', sanitize_text_field($_POST['slm_completion_trigger'] ?? 'on_submit'));
                break;
                
            case 'payment':
                update_post_meta($post_id, '_slm_payment_type', sanitize_text_field($_POST['slm_payment_type'] ?? 'fixed'));
                update_post_meta($post_id, '_slm_fixed_amount', floatval($_POST['slm_fixed_amount'] ?? 0));
                update_post_meta($post_id, '_slm_currency', sanitize_text_field($_POST['slm_currency'] ?? 'EUR'));
                update_post_meta($post_id, '_slm_stripe_enabled', !empty($_POST['slm_stripe_enabled']));
                update_post_meta($post_id, '_slm_payment_instructions', sanitize_textarea_field($_POST['slm_payment_instructions'] ?? ''));
                break;
                
            case 'signature':
                update_post_meta($post_id, '_slm_document_template_id', intval($_POST['slm_document_template'] ?? 0));
                update_post_meta($post_id, '_slm_require_initials', !empty($_POST['slm_require_initials']));
                update_post_meta($post_id, '_slm_witness_required', !empty($_POST['slm_witness_required']));
                break;
                
            case 'external':
                update_post_meta($post_id, '_slm_external_instructions', wp_kses_post($_POST['slm_external_instructions'] ?? ''));
                update_post_meta($post_id, '_slm_external_url', esc_url_raw($_POST['slm_external_url'] ?? ''));
                update_post_meta($post_id, '_slm_require_proof', !empty($_POST['slm_require_proof']));
                update_post_meta($post_id, '_slm_require_lawyer_verification', !empty($_POST['slm_require_verification']));
                break;
        }
    }
    
    public static function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $value) {
            $new[$key] = $value;
            if ($key === 'title') {
                $new['task_type'] = __('Type', 'flavor');
                $new['assigned_to'] = __('Assigned To', 'flavor');
                $new['working_days'] = __('Working Days', 'flavor');
            }
        }
        unset($new['date']);
        $new['usage'] = __('Usage', 'flavor');
        return $new;
    }
    
    public static function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'task_type':
                $type = get_post_meta($post_id, '_slm_task_type', true);
                $types = SLM_Tasks::get_task_types();
                $icons = [
                    'checkbox' => 'yes',
                    'upload' => 'upload',
                    'form' => 'feedback',
                    'payment' => 'money-alt',
                    'signature' => 'edit',
                    'external' => 'external'
                ];
                $icon = $icons[$type] ?? 'admin-generic';
                echo '<span class="dashicons dashicons-' . esc_attr($icon) . '" title="' . esc_attr($types[$type] ?? '') . '"></span> ';
                echo esc_html($types[$type] ?? $type);
                break;
                
            case 'assigned_to':
                $role = get_post_meta($post_id, '_slm_default_assigned_role', true);
                echo esc_html(ucfirst($role));
                break;
                
            case 'working_days':
                $days = get_post_meta($post_id, '_slm_working_days_standard', true);
                echo intval($days);
                break;
                
            case 'usage':
                global $wpdb;
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} p 
                     JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                     WHERE p.post_type = 'slm_task_instance' 
                     AND pm.meta_key = '_slm_source_task_template' 
                     AND pm.meta_value = %d",
                    $post_id
                ));
                echo intval($count);
                break;
        }
    }
    
    public static function sortable_columns($columns) {
        $columns['task_type'] = 'task_type';
        $columns['working_days'] = 'working_days';
        return $columns;
    }
    
    public static function ajax_get_type_fields() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $task_type = sanitize_text_field($_POST['task_type'] ?? 'checkbox');
        
        ob_start();
        self::render_type_fields($post_id, $task_type);
        $html = ob_get_clean();
        
        wp_send_json_success(['html' => $html]);
    }
    
    /**
     * Get template data for display
     */
    public static function get_template_data($template_id) {
        $template = get_post($template_id);
        if (!$template || $template->post_type !== 'slm_task_template') {
            return null;
        }
        
        $type = get_post_meta($template_id, '_slm_task_type', true);
        
        return [
            'id' => $template_id,
            'title' => $template->post_title,
            'description' => get_post_meta($template_id, '_slm_task_description', true),
            'type' => $type,
            'type_label' => SLM_Tasks::get_task_types()[$type] ?? $type,
            'assigned_role' => get_post_meta($template_id, '_slm_default_assigned_role', true),
            'working_days' => get_post_meta($template_id, '_slm_working_days_standard', true),
            'timing_anchor' => get_post_meta($template_id, '_slm_timing_anchor', true)
        ];
    }
}
