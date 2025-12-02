<?php
/**
 * SLM Task Lists
 * 
 * Manages task list templates (SOPs) that can be applied to cases.
 * Supports firm-wide and personal templates with import/export.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_Lists {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_slm_task_list', [__CLASS__, 'save_meta_boxes'], 10, 2);
        add_filter('manage_slm_task_list_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_slm_task_list_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_filter('views_edit-slm_task_list', [__CLASS__, 'admin_views']);
        add_action('pre_get_posts', [__CLASS__, 'filter_admin_list']);
        
        // AJAX handlers
        add_action('wp_ajax_slm_export_task_list', [__CLASS__, 'ajax_export']);
        add_action('wp_ajax_slm_import_task_list', [__CLASS__, 'ajax_import']);
        add_action('wp_ajax_slm_duplicate_task_list', [__CLASS__, 'ajax_duplicate']);
        add_action('wp_ajax_slm_reorder_list_tasks', [__CLASS__, 'ajax_reorder_tasks']);
    }
    
    public static function add_meta_boxes() {
        add_meta_box(
            'slm_task_list_tasks',
            __('Tasks in This List', 'flavor'),
            [__CLASS__, 'render_tasks_meta_box'],
            'slm_task_list',
            'normal',
            'high'
        );
        
        add_meta_box(
            'slm_task_list_settings',
            __('List Settings', 'flavor'),
            [__CLASS__, 'render_settings_meta_box'],
            'slm_task_list',
            'side',
            'default'
        );
        
        add_meta_box(
            'slm_task_list_actions',
            __('Actions', 'flavor'),
            [__CLASS__, 'render_actions_meta_box'],
            'slm_task_list',
            'side',
            'low'
        );
    }
    
    public static function render_tasks_meta_box($post) {
        wp_nonce_field('slm_task_list_tasks', 'slm_task_list_tasks_nonce');
        
        $tasks = get_field('tasks', $post->ID) ?: [];
        $all_templates = self::get_all_task_templates();
        ?>
        <div class="slm-task-list-builder">
            <div class="slm-task-list-toolbar">
                <button type="button" class="button slm-add-task-row">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php _e('Add Task', 'flavor'); ?>
                </button>
                <span class="slm-task-count">
                    <?php printf(__('%d tasks', 'flavor'), count($tasks)); ?>
                </span>
            </div>
            
            <table class="slm-task-list-table widefat">
                <thead>
                    <tr>
                        <th class="slm-col-drag" width="30"></th>
                        <th class="slm-col-order" width="60"><?php _e('Order', 'flavor'); ?></th>
                        <th class="slm-col-task"><?php _e('Task Template', 'flavor'); ?></th>
                        <th class="slm-col-type" width="120"><?php _e('Type', 'flavor'); ?></th>
                        <th class="slm-col-role" width="100"><?php _e('Assigned To', 'flavor'); ?></th>
                        <th class="slm-col-deps" width="150"><?php _e('Dependencies', 'flavor'); ?></th>
                        <th class="slm-col-conditional" width="80"><?php _e('Conditional', 'flavor'); ?></th>
                        <th class="slm-col-actions" width="60"></th>
                    </tr>
                </thead>
                <tbody id="slm-task-list-rows">
                    <?php 
                    if (!empty($tasks)) {
                        foreach ($tasks as $index => $task) {
                            self::render_task_row($index, $task, $all_templates, $tasks);
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <script type="text/template" id="slm-task-row-template">
                <?php self::render_task_row('{{INDEX}}', [], $all_templates, []); ?>
            </script>
        </div>
        
        <style>
            .slm-task-list-builder { margin: -6px -12px -12px; }
            .slm-task-list-toolbar { padding: 12px; background: #f6f7f7; border-bottom: 1px solid #c3c4c7; display: flex; justify-content: space-between; align-items: center; }
            .slm-task-list-table { border: none; }
            .slm-task-list-table th { background: #f0f0f1; padding: 10px; }
            .slm-task-list-table td { padding: 8px 10px; vertical-align: middle; }
            .slm-task-list-table .slm-col-drag { cursor: move; text-align: center; color: #c3c4c7; }
            .slm-task-list-table .slm-col-drag:hover { color: #2271b1; }
            .slm-task-list-table select { width: 100%; }
            .slm-task-list-table input[type="number"] { width: 60px; }
            .slm-task-row.ui-sortable-helper { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
            .slm-remove-task { color: #b32d2e; cursor: pointer; }
            .slm-remove-task:hover { color: #a00; }
            .slm-conditional-rules { display: none; margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; }
            .slm-conditional-rules.active { display: block; }
        </style>
        <?php
    }
    
    private static function render_task_row($index, $task, $all_templates, $all_tasks) {
        $template_id = $task['task_template'] ?? '';
        $sequence = $task['sequence_order'] ?? (($index + 1) * 10);
        $dependencies = $task['dependencies'] ?? [];
        $is_conditional = $task['is_conditional'] ?? false;
        $conditional_rules = $task['conditional_rules'] ?? '';
        
        $template_data = $template_id ? self::get_template_data($template_id) : null;
        ?>
        <tr class="slm-task-row" data-index="<?php echo esc_attr($index); ?>">
            <td class="slm-col-drag">
                <span class="dashicons dashicons-menu"></span>
            </td>
            <td class="slm-col-order">
                <input type="number" 
                       name="slm_tasks[<?php echo esc_attr($index); ?>][sequence_order]" 
                       value="<?php echo esc_attr($sequence); ?>" 
                       min="1" step="10">
            </td>
            <td class="slm-col-task">
                <select name="slm_tasks[<?php echo esc_attr($index); ?>][task_template]" 
                        class="slm-template-select" required>
                    <option value=""><?php _e('Select a task template...', 'flavor'); ?></option>
                    <?php foreach ($all_templates as $tmpl): ?>
                        <option value="<?php echo esc_attr($tmpl['id']); ?>" 
                                data-type="<?php echo esc_attr($tmpl['type']); ?>"
                                data-role="<?php echo esc_attr($tmpl['role']); ?>"
                                <?php selected($template_id, $tmpl['id']); ?>>
                            <?php echo esc_html($tmpl['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="slm-col-type">
                <span class="slm-type-badge">
                    <?php echo $template_data ? esc_html($template_data['type_label']) : '-'; ?>
                </span>
            </td>
            <td class="slm-col-role">
                <span class="slm-role-badge">
                    <?php echo $template_data ? esc_html(ucfirst($template_data['role'])) : '-'; ?>
                </span>
            </td>
            <td class="slm-col-deps">
                <select name="slm_tasks[<?php echo esc_attr($index); ?>][dependencies][]" 
                        class="slm-deps-select" multiple>
                    <?php 
                    foreach ($all_tasks as $idx => $t) {
                        if ($idx == $index) continue;
                        $dep_id = $t['task_template'] ?? '';
                        if (!$dep_id) continue;
                        $dep_title = get_the_title($dep_id);
                        $selected = in_array($dep_id, $dependencies) ? 'selected' : '';
                        echo '<option value="' . esc_attr($dep_id) . '" ' . $selected . '>' . esc_html($dep_title) . '</option>';
                    }
                    ?>
                </select>
            </td>
            <td class="slm-col-conditional">
                <label>
                    <input type="checkbox" 
                           name="slm_tasks[<?php echo esc_attr($index); ?>][is_conditional]" 
                           value="1" 
                           class="slm-conditional-toggle"
                           <?php checked($is_conditional); ?>>
                </label>
            </td>
            <td class="slm-col-actions">
                <span class="slm-remove-task dashicons dashicons-trash" title="<?php esc_attr_e('Remove', 'flavor'); ?>"></span>
            </td>
        </tr>
        <?php if ($is_conditional): ?>
        <tr class="slm-conditional-row">
            <td colspan="8">
                <div class="slm-conditional-rules active">
                    <label><?php _e('Conditional Rules (JSON):', 'flavor'); ?></label>
                    <textarea name="slm_tasks[<?php echo esc_attr($index); ?>][conditional_rules]" 
                              rows="3" style="width:100%"><?php echo esc_textarea($conditional_rules); ?></textarea>
                    <p class="description">
                        <?php _e('Example: [{"field":"_slm_case_type","operator":"=","value":"CIT"}]', 'flavor'); ?>
                    </p>
                </div>
            </td>
        </tr>
        <?php endif;
    }
    
    public static function render_settings_meta_box($post) {
        $is_firm_template = get_post_meta($post->ID, '_slm_is_firm_template', true);
        $is_shareable = get_post_meta($post->ID, '_slm_is_shareable', true);
        $category = get_post_meta($post->ID, '_slm_task_list_category', true);
        
        $categories = [
            'onboarding' => __('Onboarding', 'flavor'),
            'offboarding' => __('Offboarding', 'flavor'),
            'sop' => __('Standard Operating Procedure', 'flavor'),
            'info_update' => __('Information Update', 'flavor'),
            'other' => __('Other', 'flavor'),
        ];
        ?>
        <p>
            <label for="slm_task_list_category"><?php _e('Category:', 'flavor'); ?></label>
            <select name="slm_task_list_category" id="slm_task_list_category" style="width:100%">
                <?php foreach ($categories as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($category, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label>
                <input type="checkbox" name="slm_is_firm_template" value="1" <?php checked($is_firm_template); ?>>
                <?php _e('Firm-wide template', 'flavor'); ?>
            </label>
            <br>
            <span class="description"><?php _e('Available to all lawyers', 'flavor'); ?></span>
        </p>
        
        <p class="slm-shareable-option" style="<?php echo $is_firm_template ? 'display:none' : ''; ?>">
            <label>
                <input type="checkbox" name="slm_is_shareable" value="1" <?php checked($is_shareable); ?>>
                <?php _e('Allow sharing', 'flavor'); ?>
            </label>
            <br>
            <span class="description"><?php _e('Other lawyers can use this template', 'flavor'); ?></span>
        </p>
        
        <script>
        jQuery(function($) {
            $('input[name="slm_is_firm_template"]').on('change', function() {
                $('.slm-shareable-option').toggle(!this.checked);
            });
        });
        </script>
        <?php
    }
    
    public static function render_actions_meta_box($post) {
        if ($post->post_status !== 'publish') {
            echo '<p class="description">' . __('Save the list first to enable actions.', 'flavor') . '</p>';
            return;
        }
        ?>
        <p>
            <button type="button" class="button button-secondary slm-duplicate-list" 
                    data-id="<?php echo esc_attr($post->ID); ?>" style="width:100%">
                <span class="dashicons dashicons-admin-page" style="vertical-align:middle"></span>
                <?php _e('Duplicate List', 'flavor'); ?>
            </button>
        </p>
        <p>
            <button type="button" class="button button-secondary slm-export-list" 
                    data-id="<?php echo esc_attr($post->ID); ?>" style="width:100%">
                <span class="dashicons dashicons-download" style="vertical-align:middle"></span>
                <?php _e('Export as CSV', 'flavor'); ?>
            </button>
        </p>
        <hr>
        <p>
            <strong><?php _e('Import Tasks', 'flavor'); ?></strong>
        </p>
        <p>
            <input type="file" id="slm-import-file" accept=".csv">
        </p>
        <p>
            <button type="button" class="button button-secondary slm-import-list" 
                    data-id="<?php echo esc_attr($post->ID); ?>" style="width:100%">
                <span class="dashicons dashicons-upload" style="vertical-align:middle"></span>
                <?php _e('Import from CSV', 'flavor'); ?>
            </button>
        </p>
        <?php
    }
    
    public static function save_meta_boxes($post_id, $post) {
        if (!isset($_POST['slm_task_list_tasks_nonce'])) return;
        if (!wp_verify_nonce($_POST['slm_task_list_tasks_nonce'], 'slm_task_list_tasks')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        // Save settings
        update_post_meta($post_id, '_slm_task_list_category', sanitize_text_field($_POST['slm_task_list_category'] ?? 'other'));
        update_post_meta($post_id, '_slm_is_firm_template', !empty($_POST['slm_is_firm_template']));
        update_post_meta($post_id, '_slm_is_shareable', !empty($_POST['slm_is_shareable']));
        
        // Set created_by on first save
        if (!get_post_meta($post_id, '_slm_created_by', true)) {
            update_post_meta($post_id, '_slm_created_by', get_current_user_id());
        }
        
        // Save tasks via ACF if available, otherwise save directly
        if (!empty($_POST['slm_tasks']) && !function_exists('update_field')) {
            $tasks = [];
            foreach ($_POST['slm_tasks'] as $task) {
                $tasks[] = [
                    'task_template' => intval($task['task_template'] ?? 0),
                    'sequence_order' => intval($task['sequence_order'] ?? 10),
                    'dependencies' => array_map('intval', $task['dependencies'] ?? []),
                    'is_conditional' => !empty($task['is_conditional']),
                    'conditional_rules' => sanitize_textarea_field($task['conditional_rules'] ?? '')
                ];
            }
            
            usort($tasks, function($a, $b) {
                return $a['sequence_order'] - $b['sequence_order'];
            });
            
            update_post_meta($post_id, 'tasks', $tasks);
        }
    }
    
    public static function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $value) {
            $new[$key] = $value;
            if ($key === 'title') {
                $new['category'] = __('Category', 'flavor');
                $new['task_count'] = __('Tasks', 'flavor');
                $new['scope'] = __('Scope', 'flavor');
                $new['created_by'] = __('Created By', 'flavor');
            }
        }
        return $new;
    }
    
    public static function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'category':
                $cat = get_post_meta($post_id, '_slm_task_list_category', true);
                $categories = [
                    'onboarding' => __('Onboarding', 'flavor'),
                    'offboarding' => __('Offboarding', 'flavor'),
                    'sop' => __('SOP', 'flavor'),
                    'info_update' => __('Info Update', 'flavor'),
                    'other' => __('Other', 'flavor'),
                ];
                echo esc_html($categories[$cat] ?? $cat);
                break;
                
            case 'task_count':
                $tasks = get_field('tasks', $post_id) ?: [];
                echo count($tasks);
                break;
                
            case 'scope':
                $is_firm = get_post_meta($post_id, '_slm_is_firm_template', true);
                $is_shareable = get_post_meta($post_id, '_slm_is_shareable', true);
                
                if ($is_firm) {
                    echo '<span class="dashicons dashicons-admin-multisite" title="' . esc_attr__('Firm-wide', 'flavor') . '"></span> ' . __('Firm', 'flavor');
                } elseif ($is_shareable) {
                    echo '<span class="dashicons dashicons-share" title="' . esc_attr__('Shareable', 'flavor') . '"></span> ' . __('Shared', 'flavor');
                } else {
                    echo '<span class="dashicons dashicons-admin-users" title="' . esc_attr__('Personal', 'flavor') . '"></span> ' . __('Personal', 'flavor');
                }
                break;
                
            case 'created_by':
                $user_id = get_post_meta($post_id, '_slm_created_by', true);
                if ($user_id) {
                    $user = get_userdata($user_id);
                    echo $user ? esc_html($user->display_name) : '-';
                }
                break;
        }
    }
    
    public static function admin_views($views) {
        global $wpdb;
        
        $current_user = get_current_user_id();
        
        // Count firm templates
        $firm_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} p 
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'slm_task_list' 
             AND p.post_status = 'publish' 
             AND pm.meta_key = '_slm_is_firm_template' 
             AND pm.meta_value = '1'"
        );
        
        // Count my templates
        $my_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p 
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'slm_task_list' 
             AND p.post_status = 'publish' 
             AND pm.meta_key = '_slm_created_by' 
             AND pm.meta_value = %d",
            $current_user
        ));
        
        $current_filter = isset($_GET['slm_filter']) ? $_GET['slm_filter'] : '';
        
        $views['firm'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            add_query_arg(['slm_filter' => 'firm', 'post_type' => 'slm_task_list'], admin_url('edit.php')),
            $current_filter === 'firm' ? 'current' : '',
            __('Firm Templates', 'flavor'),
            $firm_count
        );
        
        $views['mine'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
            add_query_arg(['slm_filter' => 'mine', 'post_type' => 'slm_task_list'], admin_url('edit.php')),
            $current_filter === 'mine' ? 'current' : '',
            __('My Templates', 'flavor'),
            $my_count
        );
        
        return $views;
    }
    
    public static function filter_admin_list($query) {
        if (!is_admin() || !$query->is_main_query()) return;
        if ($query->get('post_type') !== 'slm_task_list') return;
        
        $filter = isset($_GET['slm_filter']) ? $_GET['slm_filter'] : '';
        
        if ($filter === 'firm') {
            $query->set('meta_query', [
                ['key' => '_slm_is_firm_template', 'value' => '1']
            ]);
        } elseif ($filter === 'mine') {
            $query->set('meta_query', [
                ['key' => '_slm_created_by', 'value' => get_current_user_id()]
            ]);
        }
    }
    
    // AJAX handlers
    
    public static function ajax_export() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        $list_id = intval($_GET['list_id'] ?? 0);
        if (!$list_id) {
            wp_send_json_error(['message' => 'Invalid list ID']);
        }
        
        $list = get_post($list_id);
        if (!$list || $list->post_type !== 'slm_task_list') {
            wp_send_json_error(['message' => 'Task list not found']);
        }
        
        $tasks = get_field('tasks', $list_id) ?: [];
        
        $csv_data = [];
        $csv_data[] = ['Order', 'Template ID', 'Template Title', 'Type', 'Assigned Role', 'Dependencies', 'Conditional', 'Rules'];
        
        foreach ($tasks as $task) {
            $template_id = $task['task_template'];
            $template = get_post($template_id);
            $type = get_post_meta($template_id, '_slm_task_type', true);
            $role = get_post_meta($template_id, '_slm_default_assigned_role', true);
            
            $deps = [];
            foreach ($task['dependencies'] ?? [] as $dep_id) {
                $deps[] = get_the_title($dep_id);
            }
            
            $csv_data[] = [
                $task['sequence_order'],
                $template_id,
                $template ? $template->post_title : '',
                $type,
                $role,
                implode('; ', $deps),
                $task['is_conditional'] ? 'Yes' : 'No',
                $task['conditional_rules'] ?? ''
            ];
        }
        
        wp_send_json_success([
            'filename' => sanitize_file_name($list->post_title) . '.csv',
            'data' => $csv_data
        ]);
    }
    
    public static function ajax_import() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $list_id = intval($_POST['list_id'] ?? 0);
        $csv_data = $_POST['csv_data'] ?? [];
        $mode = sanitize_text_field($_POST['mode'] ?? 'append');
        
        if (!$list_id || empty($csv_data)) {
            wp_send_json_error(['message' => 'Missing data']);
        }
        
        $existing_tasks = ($mode === 'append') ? (get_field('tasks', $list_id) ?: []) : [];
        $max_order = 0;
        
        foreach ($existing_tasks as $task) {
            $max_order = max($max_order, $task['sequence_order'] ?? 0);
        }
        
        $imported = 0;
        $errors = [];
        
        // Skip header row
        array_shift($csv_data);
        
        foreach ($csv_data as $row) {
            if (count($row) < 4) continue;
            
            $template_id = intval($row[1] ?? 0);
            
            // Try to find by title if ID doesn't exist
            if (!$template_id || !get_post($template_id)) {
                $title = $row[2] ?? '';
                $found = get_posts([
                    'post_type' => 'slm_task_template',
                    'title' => $title,
                    'posts_per_page' => 1,
                    'fields' => 'ids'
                ]);
                
                if (!empty($found)) {
                    $template_id = $found[0];
                } else {
                    $errors[] = sprintf(__('Template not found: %s', 'flavor'), $title);
                    continue;
                }
            }
            
            $max_order += 10;
            
            $existing_tasks[] = [
                'task_template' => $template_id,
                'sequence_order' => intval($row[0] ?? $max_order),
                'dependencies' => [],
                'is_conditional' => strtolower($row[6] ?? '') === 'yes',
                'conditional_rules' => $row[7] ?? ''
            ];
            
            $imported++;
        }
        
        update_field('tasks', $existing_tasks, $list_id);
        
        wp_send_json_success([
            'imported' => $imported,
            'errors' => $errors,
            'message' => sprintf(__('%d tasks imported', 'flavor'), $imported)
        ]);
    }
    
    public static function ajax_duplicate() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $list_id = intval($_POST['list_id'] ?? 0);
        if (!$list_id) {
            wp_send_json_error(['message' => 'Invalid list ID']);
        }
        
        $original = get_post($list_id);
        if (!$original || $original->post_type !== 'slm_task_list') {
            wp_send_json_error(['message' => 'Task list not found']);
        }
        
        // Create duplicate
        $new_id = wp_insert_post([
            'post_type' => 'slm_task_list',
            'post_title' => $original->post_title . ' ' . __('(Copy)', 'flavor'),
            'post_content' => $original->post_content,
            'post_status' => 'draft',
            'post_author' => get_current_user_id()
        ]);
        
        if (is_wp_error($new_id)) {
            wp_send_json_error(['message' => $new_id->get_error_message()]);
        }
        
        // Copy meta
        $meta_keys = [
            '_slm_task_list_category',
            '_slm_is_shareable',
            'tasks'
        ];
        
        foreach ($meta_keys as $key) {
            $value = get_post_meta($list_id, $key, true);
            if ($value) {
                update_post_meta($new_id, $key, $value);
            }
        }
        
        // Set as personal template
        update_post_meta($new_id, '_slm_is_firm_template', false);
        update_post_meta($new_id, '_slm_created_by', get_current_user_id());
        
        wp_send_json_success([
            'new_id' => $new_id,
            'edit_url' => get_edit_post_link($new_id, 'raw'),
            'message' => __('Task list duplicated', 'flavor')
        ]);
    }
    
    public static function ajax_reorder_tasks() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $list_id = intval($_POST['list_id'] ?? 0);
        $order = $_POST['order'] ?? [];
        
        if (!$list_id || empty($order)) {
            wp_send_json_error(['message' => 'Missing data']);
        }
        
        $tasks = get_field('tasks', $list_id) ?: [];
        $reordered = [];
        
        foreach ($order as $new_index => $old_index) {
            if (isset($tasks[$old_index])) {
                $task = $tasks[$old_index];
                $task['sequence_order'] = ($new_index + 1) * 10;
                $reordered[] = $task;
            }
        }
        
        update_field('tasks', $reordered, $list_id);
        
        wp_send_json_success(['message' => __('Tasks reordered', 'flavor')]);
    }
    
    // Helper methods
    
    private static function get_all_task_templates() {
        $templates = get_posts([
            'post_type' => 'slm_task_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        $result = [];
        foreach ($templates as $template) {
            $result[] = self::get_template_data($template);
        }
        
        return $result;
    }
    
    private static function get_template_data($template) {
        if (is_numeric($template)) {
            $template = get_post($template);
        }
        
        if (!$template) return null;
        
        $type = get_post_meta($template->ID, '_slm_task_type', true);
        $types = SLM_Tasks::get_task_types();
        
        return [
            'id' => $template->ID,
            'title' => $template->post_title,
            'type' => $type,
            'type_label' => $types[$type] ?? $type,
            'role' => get_post_meta($template->ID, '_slm_default_assigned_role', true)
        ];
    }
    
    /**
     * Get task lists available to a user
     */
    public static function get_available_lists($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $args = [
            'post_type' => 'slm_task_list',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_slm_is_firm_template', 'value' => '1'],
                ['key' => '_slm_created_by', 'value' => $user_id],
                [
                    'relation' => 'AND',
                    ['key' => '_slm_is_shareable', 'value' => '1'],
                    ['key' => '_slm_is_firm_template', 'value' => '0']
                ]
            ],
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        
        return get_posts($args);
    }
    
    /**
     * Get list data for display
     */
    public static function get_list_data($list_id) {
        $list = get_post($list_id);
        if (!$list || $list->post_type !== 'slm_task_list') {
            return null;
        }
        
        $tasks = get_field('tasks', $list_id) ?: [];
        
        return [
            'id' => $list_id,
            'title' => $list->post_title,
            'description' => get_post_meta($list_id, '_slm_task_list_description', true),
            'category' => get_post_meta($list_id, '_slm_task_list_category', true),
            'is_firm_template' => (bool) get_post_meta($list_id, '_slm_is_firm_template', true),
            'is_shareable' => (bool) get_post_meta($list_id, '_slm_is_shareable', true),
            'task_count' => count($tasks),
            'created_by' => get_post_meta($list_id, '_slm_created_by', true),
        ];
    }
}
