<?php
/**
 * SLM Task Instances
 * 
 * Manages actual task instances assigned to cases.
 * Handles completion, dependencies, progress tracking.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_Instances {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('save_post_slm_task_instance', [__CLASS__, 'on_task_save'], 10, 3);
        add_action('admin_menu', [__CLASS__, 'add_admin_pages']);
        add_filter('manage_slm_task_instance_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_slm_task_instance_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_filter('manage_edit-slm_task_instance_sortable_columns', [__CLASS__, 'sortable_columns']);
    }
    
    public static function add_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=slm_case',
            __('All Tasks', 'flavor'),
            __('All Tasks', 'flavor'),
            'edit_posts',
            'edit.php?post_type=slm_task_instance'
        );
    }
    
    public static function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $value) {
            $new[$key] = $value;
            if ($key === 'title') {
                $new['case'] = __('Case', 'flavor');
                $new['assigned'] = __('Assigned To', 'flavor');
                $new['task_type'] = __('Type', 'flavor');
                $new['status'] = __('Status', 'flavor');
                $new['due_date'] = __('Due Date', 'flavor');
            }
        }
        unset($new['date']);
        return $new;
    }
    
    public static function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'case':
                $case_id = get_post_meta($post_id, '_slm_case_id', true);
                if ($case_id) {
                    echo '<a href="' . get_edit_post_link($case_id) . '">' . esc_html(get_the_title($case_id)) . '</a>';
                }
                break;
                
            case 'assigned':
                $user_id = get_post_meta($post_id, '_slm_assigned_user', true);
                if ($user_id) {
                    $user = get_userdata($user_id);
                    echo $user ? esc_html($user->display_name) : '-';
                }
                break;
                
            case 'task_type':
                $type = get_post_meta($post_id, '_slm_task_type', true);
                $types = SLM_Tasks::get_task_types();
                echo esc_html($types[$type] ?? $type);
                break;
                
            case 'status':
                $status = get_post_meta($post_id, '_slm_task_status', true);
                $statuses = SLM_Tasks::get_task_statuses();
                $class = self::get_status_class($status);
                echo '<span class="slm-status slm-status-' . esc_attr($class) . '">' . esc_html($statuses[$status] ?? $status) . '</span>';
                break;
                
            case 'due_date':
                $due = get_post_meta($post_id, '_slm_due_date', true);
                if ($due) {
                    $date = new DateTime($due);
                    $today = new DateTime('today');
                    $class = $date < $today ? 'overdue' : '';
                    echo '<span class="' . esc_attr($class) . '">' . esc_html($date->format('d/m/Y')) . '</span>';
                }
                break;
        }
    }
    
    public static function sortable_columns($columns) {
        $columns['due_date'] = 'due_date';
        $columns['status'] = 'status';
        return $columns;
    }
    
    private static function get_status_class($status) {
        $map = [
            'locked' => 'grey',
            'available' => 'blue',
            'in_progress' => 'orange',
            'pending_review' => 'purple',
            'complete' => 'green',
            'cancelled' => 'red'
        ];
        return $map[$status] ?? 'grey';
    }
    
    public static function on_task_save($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        
        // Ensure status is set
        $status = get_post_meta($post_id, '_slm_task_status', true);
        if (empty($status)) {
            update_post_meta($post_id, '_slm_task_status', 'locked');
        }
    }
    
    /**
     * Apply a task list to a case, creating task instances
     */
    public static function apply_task_list_to_case($task_list_id, $case_id) {
        $task_list = get_post($task_list_id);
        if (!$task_list || $task_list->post_type !== 'slm_task_list') {
            return new WP_Error('invalid_list', 'Invalid task list');
        }
        
        $case = get_post($case_id);
        if (!$case || $case->post_type !== 'slm_case') {
            return new WP_Error('invalid_case', 'Invalid case');
        }
        
        $tasks_repeater = get_field('tasks', $task_list_id);
        if (empty($tasks_repeater)) {
            return new WP_Error('empty_list', 'Task list has no tasks');
        }
        
        $service_tier = get_post_meta($case_id, '_slm_service_tier', true) ?: 'standard';
        $client_id = get_post_meta($case_id, '_slm_client_id', true);
        $lawyer_id = get_post_meta($case_id, '_slm_lead_lawyer', true);
        
        $created_tasks = [];
        $template_to_instance = [];
        
        // First pass: create all task instances
        foreach ($tasks_repeater as $index => $task_row) {
            $template_id = $task_row['task_template'];
            $template = get_post($template_id);
            
            if (!$template || $template->post_type !== 'slm_task_template') {
                continue;
            }
            
            // Check conditional rules
            if (!empty($task_row['is_conditional']) && !empty($task_row['conditional_rules'])) {
                $rules = json_decode($task_row['conditional_rules'], true);
                if (!self::evaluate_conditional_rules($rules, $case_id)) {
                    continue;
                }
            }
            
            // Get template data
            $task_type = get_post_meta($template_id, '_slm_task_type', true);
            $default_role = get_post_meta($template_id, '_slm_default_assigned_role', true);
            $description = get_post_meta($template_id, '_slm_task_description', true);
            
            // Determine assigned user
            $assigned_user = ($default_role === 'client') ? $client_id : $lawyer_id;
            
            // Calculate due date
            $working_days = self::get_working_days_for_tier($template_id, $service_tier);
            $timing_anchor = get_post_meta($template_id, '_slm_timing_anchor', true);
            $due_date = SLM_Timeline::calculate_due_date(new DateTime(), $working_days, $service_tier);
            
            // Create task instance
            $task_instance_id = wp_insert_post([
                'post_type' => 'slm_task_instance',
                'post_title' => $template->post_title,
                'post_status' => 'publish',
                'post_author' => get_current_user_id()
            ]);
            
            if (is_wp_error($task_instance_id)) {
                continue;
            }
            
            // Set meta fields
            update_post_meta($task_instance_id, '_slm_case_id', $case_id);
            update_post_meta($task_instance_id, '_slm_source_task_template', $template_id);
            update_post_meta($task_instance_id, '_slm_source_task_list', $task_list_id);
            update_post_meta($task_instance_id, '_slm_assigned_user', $assigned_user);
            update_post_meta($task_instance_id, '_slm_task_type', $task_type);
            update_post_meta($task_instance_id, '_slm_task_description', $description);
            update_post_meta($task_instance_id, '_slm_sequence_order', $task_row['sequence_order'] ?? (($index + 1) * 10));
            update_post_meta($task_instance_id, '_slm_due_date', $due_date->format('Y-m-d'));
            update_post_meta($task_instance_id, '_slm_created_date', current_time('mysql'));
            
            // Copy type-specific settings
            self::copy_type_specific_settings($task_instance_id, $template_id, $task_type);
            
            // Initial status: locked (will be unlocked after dependency check)
            update_post_meta($task_instance_id, '_slm_task_status', 'locked');
            
            $template_to_instance[$template_id] = $task_instance_id;
            $created_tasks[] = $task_instance_id;
        }
        
        // Second pass: set up dependencies
        foreach ($tasks_repeater as $task_row) {
            $template_id = $task_row['task_template'];
            
            if (!isset($template_to_instance[$template_id])) {
                continue;
            }
            
            $task_instance_id = $template_to_instance[$template_id];
            $dependencies = $task_row['dependencies'] ?? [];
            
            if (empty($dependencies)) {
                // No dependencies - unlock immediately
                update_post_meta($task_instance_id, '_slm_task_status', 'available');
                self::send_task_available_notification($task_instance_id);
            } else {
                // Store dependencies
                $dep_instance_ids = [];
                foreach ($dependencies as $dep_template_id) {
                    if (isset($template_to_instance[$dep_template_id])) {
                        $dep_instance_ids[] = $template_to_instance[$dep_template_id];
                    }
                }
                
                if (!empty($dep_instance_ids)) {
                    update_post_meta($task_instance_id, '_slm_dependencies', $dep_instance_ids);
                    self::create_dependency_records($task_instance_id, $dep_instance_ids);
                } else {
                    // Dependencies weren't created (conditional) - unlock
                    update_post_meta($task_instance_id, '_slm_task_status', 'available');
                    self::send_task_available_notification($task_instance_id);
                }
            }
        }
        
        // Log audit
        SLM_Task_Audit::log('task_list_applied', 'case', $case_id, [
            'task_list_id' => $task_list_id,
            'task_list_name' => $task_list->post_title,
            'tasks_created' => count($created_tasks)
        ]);
        
        return [
            'tasks_created' => count($created_tasks),
            'tasks' => array_map([__CLASS__, 'get_task_data'], $created_tasks)
        ];
    }
    
    /**
     * Create an ad-hoc task (not from a template)
     */
    public static function create_ad_hoc_task($case_id, $data) {
        $case = get_post($case_id);
        if (!$case || $case->post_type !== 'slm_case') {
            return new WP_Error('invalid_case', 'Invalid case');
        }
        
        $title = sanitize_text_field($data['title'] ?? '');
        if (empty($title)) {
            return new WP_Error('missing_title', 'Task title is required');
        }
        
        $task_type = sanitize_text_field($data['task_type'] ?? 'checkbox');
        $assigned_user = intval($data['assigned_user'] ?? 0);
        $due_date = sanitize_text_field($data['due_date'] ?? '');
        $description = wp_kses_post($data['description'] ?? '');
        
        $task_id = wp_insert_post([
            'post_type' => 'slm_task_instance',
            'post_title' => $title,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);
        
        if (is_wp_error($task_id)) {
            return $task_id;
        }
        
        update_post_meta($task_id, '_slm_case_id', $case_id);
        update_post_meta($task_id, '_slm_task_type', $task_type);
        update_post_meta($task_id, '_slm_task_description', $description);
        update_post_meta($task_id, '_slm_assigned_user', $assigned_user ?: get_post_meta($case_id, '_slm_client_id', true));
        update_post_meta($task_id, '_slm_task_status', 'available');
        update_post_meta($task_id, '_slm_created_date', current_time('mysql'));
        update_post_meta($task_id, '_slm_is_ad_hoc', true);
        
        if (!empty($due_date)) {
            update_post_meta($task_id, '_slm_due_date', $due_date);
        }
        
        // Type-specific settings from data
        if ($task_type === 'upload') {
            update_post_meta($task_id, '_slm_allowed_file_types', $data['allowed_file_types'] ?? ['pdf', 'jpg', 'png']);
            update_post_meta($task_id, '_slm_max_file_size_mb', $data['max_file_size_mb'] ?? 10);
            update_post_meta($task_id, '_slm_document_category', $data['document_category'] ?? '');
        } elseif ($task_type === 'payment') {
            update_post_meta($task_id, '_slm_payment_type', $data['payment_type'] ?? 'fixed');
            update_post_meta($task_id, '_slm_fixed_amount', $data['amount'] ?? 0);
            update_post_meta($task_id, '_slm_currency', $data['currency'] ?? 'EUR');
        } elseif ($task_type === 'external') {
            update_post_meta($task_id, '_slm_external_instructions', $data['external_instructions'] ?? '');
            update_post_meta($task_id, '_slm_external_url', $data['external_url'] ?? '');
            update_post_meta($task_id, '_slm_require_proof', $data['require_proof'] ?? false);
            update_post_meta($task_id, '_slm_require_lawyer_verification', $data['require_verification'] ?? false);
        }
        
        // Handle dependencies if provided
        if (!empty($data['dependencies'])) {
            update_post_meta($task_id, '_slm_dependencies', $data['dependencies']);
            self::create_dependency_records($task_id, $data['dependencies']);
            update_post_meta($task_id, '_slm_task_status', 'locked');
        }
        
        self::send_task_available_notification($task_id);
        
        SLM_Task_Audit::log('task_created', 'task', $task_id, [
            'case_id' => $case_id,
            'is_ad_hoc' => true,
            'task_type' => $task_type
        ]);
        
        return $task_id;
    }
    
    /**
     * Complete a task
     */
    public static function complete_task($task_id, $user_id, $completion_data = []) {
        $task = get_post($task_id);
        if (!$task || $task->post_type !== 'slm_task_instance') {
            return new WP_Error('invalid_task', 'Invalid task');
        }
        
        $current_status = get_post_meta($task_id, '_slm_task_status', true);
        if ($current_status === 'complete') {
            return new WP_Error('already_complete', 'Task is already complete');
        }
        
        if ($current_status === 'locked') {
            return new WP_Error('task_locked', 'Task is locked and cannot be completed');
        }
        
        if ($current_status === 'cancelled') {
            return new WP_Error('task_cancelled', 'Task has been cancelled');
        }
        
        $task_type = get_post_meta($task_id, '_slm_task_type', true);
        $old_status = $current_status;
        
        // Type-specific validation and data storage
        switch ($task_type) {
            case 'upload':
                if (empty($completion_data['document_id'])) {
                    return new WP_Error('missing_document', 'Document upload required');
                }
                update_post_meta($task_id, '_slm_uploaded_document_id', $completion_data['document_id']);
                break;
                
            case 'payment':
                if (empty($completion_data['transaction_id']) && empty($completion_data['marked_by'])) {
                    return new WP_Error('missing_payment', 'Payment confirmation required');
                }
                update_post_meta($task_id, '_slm_payment_method', $completion_data['payment_method'] ?? 'unknown');
                update_post_meta($task_id, '_slm_transaction_reference', $completion_data['transaction_id'] ?? '');
                update_post_meta($task_id, '_slm_payment_amount', $completion_data['amount'] ?? 0);
                if (!empty($completion_data['marked_by'])) {
                    update_post_meta($task_id, '_slm_payment_marked_by', $completion_data['marked_by']);
                }
                break;
                
            case 'form':
                if (!empty($completion_data['entry_id'])) {
                    update_post_meta($task_id, '_slm_gravity_form_entry_id', $completion_data['entry_id']);
                }
                break;
                
            case 'signature':
                if (!empty($completion_data['signed_document_id'])) {
                    update_post_meta($task_id, '_slm_signed_document_id', $completion_data['signed_document_id']);
                }
                if (!empty($completion_data['envelope_id'])) {
                    update_post_meta($task_id, '_slm_envelope_id', $completion_data['envelope_id']);
                }
                break;
                
            case 'external':
                $require_verification = get_post_meta($task_id, '_slm_require_lawyer_verification', true);
                
                update_post_meta($task_id, '_slm_external_completion_notes', $completion_data['notes'] ?? '');
                update_post_meta($task_id, '_slm_external_reference', $completion_data['reference'] ?? '');
                
                if (!empty($completion_data['proof_document_id'])) {
                    update_post_meta($task_id, '_slm_external_proof_upload_id', $completion_data['proof_document_id']);
                }
                
                if ($require_verification) {
                    update_post_meta($task_id, '_slm_task_status', 'pending_review');
                    update_post_meta($task_id, '_slm_verification_status', 'pending');
                    
                    // Notify lawyer
                    $case_id = get_post_meta($task_id, '_slm_case_id', true);
                    $lawyer_id = get_post_meta($case_id, '_slm_lead_lawyer', true);
                    
                    if ($lawyer_id) {
                        SLM_Notifications::create([
                            'recipient' => $lawyer_id,
                            'type' => 'task_requires_review',
                            'case_id' => $case_id,
                            'task_id' => $task_id,
                            'title' => 'External Task Requires Verification',
                            'body' => sprintf('The task "%s" requires your verification.', $task->post_title)
                        ]);
                    }
                    
                    SLM_Task_Audit::log('task_pending_review', 'task', $task_id, [
                        'submitted_by' => $user_id
                    ]);
                    
                    return ['status' => 'pending_review', 'message' => 'Task submitted for verification'];
                }
                break;
        }
        
        // Mark complete
        update_post_meta($task_id, '_slm_task_status', 'complete');
        update_post_meta($task_id, '_slm_completed_date', current_time('mysql'));
        update_post_meta($task_id, '_slm_completed_by', $user_id);
        
        // Log audit
        SLM_Task_Audit::log('task_completed', 'task', $task_id, [
            'completed_by' => $user_id,
            'old_status' => $old_status,
            'task_type' => $task_type,
            'completion_data' => $completion_data
        ]);
        
        // Check and unlock dependent tasks
        self::process_dependent_tasks($task_id);
        
        // Send completion notification
        self::send_task_completed_notification($task_id, $user_id);
        
        // Update progress snapshot
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        self::update_progress_snapshot($case_id);
        
        return ['status' => 'complete', 'message' => 'Task completed successfully'];
    }
    
    /**
     * Verify an external task (lawyer approval)
     */
    public static function verify_task($task_id, $lawyer_id) {
        $task = get_post($task_id);
        if (!$task) {
            return new WP_Error('invalid_task', 'Invalid task');
        }
        
        $status = get_post_meta($task_id, '_slm_task_status', true);
        if ($status !== 'pending_review') {
            return new WP_Error('invalid_status', 'Task is not pending review');
        }
        
        update_post_meta($task_id, '_slm_task_status', 'complete');
        update_post_meta($task_id, '_slm_verification_status', 'verified');
        update_post_meta($task_id, '_slm_verified_by', $lawyer_id);
        update_post_meta($task_id, '_slm_verified_date', current_time('mysql'));
        update_post_meta($task_id, '_slm_completed_date', current_time('mysql'));
        update_post_meta($task_id, '_slm_completed_by', $lawyer_id);
        
        SLM_Task_Audit::log('task_verified', 'task', $task_id, [
            'verified_by' => $lawyer_id
        ]);
        
        self::process_dependent_tasks($task_id);
        
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        self::update_progress_snapshot($case_id);
        
        // Notify original completer
        $assigned_user = get_post_meta($task_id, '_slm_assigned_user', true);
        if ($assigned_user) {
            SLM_Notifications::create([
                'recipient' => $assigned_user,
                'type' => 'task_completed',
                'case_id' => $case_id,
                'task_id' => $task_id,
                'title' => 'Task Verified',
                'body' => sprintf('Your task "%s" has been verified and marked complete.', $task->post_title)
            ]);
        }
        
        return ['status' => 'verified', 'message' => 'Task verified successfully'];
    }
    
    /**
     * Update a task (lawyer only, requires reason)
     */
    public static function update_task($task_id, $updates, $reason) {
        $task = get_post($task_id);
        if (!$task) {
            return new WP_Error('invalid_task', 'Invalid task');
        }
        
        $old_values = [];
        $new_values = [];
        
        $allowed_fields = [
            'title', 'description', 'assigned_user', 'due_date', 
            'task_status', 'sequence_order'
        ];
        
        foreach ($updates as $field => $value) {
            if (!in_array($field, $allowed_fields)) {
                continue;
            }
            
            if ($field === 'title') {
                $old_values['title'] = $task->post_title;
                wp_update_post(['ID' => $task_id, 'post_title' => sanitize_text_field($value)]);
                $new_values['title'] = sanitize_text_field($value);
            } else {
                $meta_key = '_slm_' . ($field === 'description' ? 'task_description' : $field);
                $old_values[$field] = get_post_meta($task_id, $meta_key, true);
                update_post_meta($task_id, $meta_key, $value);
                $new_values[$field] = $value;
            }
        }
        
        // Log edit with reason
        SLM_Task_Audit::log('task_edited', 'task', $task_id, [
            'edited_by' => get_current_user_id(),
            'reason' => $reason,
            'old_values' => $old_values,
            'new_values' => $new_values
        ]);
        
        // Store edit history
        $edit_history = get_post_meta($task_id, '_slm_edit_history', true) ?: [];
        $edit_history[] = [
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'reason' => $reason,
            'changes' => array_keys($new_values)
        ];
        update_post_meta($task_id, '_slm_edit_history', $edit_history);
        
        return true;
    }
    
    /**
     * Cancel a task
     */
    public static function cancel_task($task_id, $reason, $handle_dependents = 'reassign') {
        $task = get_post($task_id);
        if (!$task) {
            return new WP_Error('invalid_task', 'Invalid task');
        }
        
        $status = get_post_meta($task_id, '_slm_task_status', true);
        if ($status === 'complete') {
            return new WP_Error('already_complete', 'Cannot cancel a completed task');
        }
        
        update_post_meta($task_id, '_slm_task_status', 'cancelled');
        update_post_meta($task_id, '_slm_cancelled_date', current_time('mysql'));
        update_post_meta($task_id, '_slm_cancelled_by', get_current_user_id());
        update_post_meta($task_id, '_slm_cancellation_reason', $reason);
        
        // Handle dependent tasks
        $affected_tasks = [];
        $dependents = self::get_dependent_tasks($task_id);
        
        foreach ($dependents as $dependent_id) {
            switch ($handle_dependents) {
                case 'cancel':
                    // Also cancel dependent tasks
                    update_post_meta($dependent_id, '_slm_task_status', 'cancelled');
                    update_post_meta($dependent_id, '_slm_cancellation_reason', 'Parent task cancelled');
                    update_post_meta($dependent_id, '_slm_dependent_status_change', 'cancelled');
                    $affected_tasks[] = ['id' => $dependent_id, 'action' => 'cancelled'];
                    break;
                    
                case 'unlock':
                    // Manually unlock
                    self::remove_dependency($dependent_id, $task_id);
                    if (self::all_dependencies_met($dependent_id)) {
                        update_post_meta($dependent_id, '_slm_task_status', 'available');
                        update_post_meta($dependent_id, '_slm_dependent_status_change', 'unlocked');
                        self::send_task_available_notification($dependent_id);
                    }
                    $affected_tasks[] = ['id' => $dependent_id, 'action' => 'unlocked'];
                    break;
                    
                case 'reassign':
                default:
                    // Remove this dependency, keep others
                    self::remove_dependency($dependent_id, $task_id);
                    update_post_meta($dependent_id, '_slm_dependent_status_change', 'reassigned');
                    if (self::all_dependencies_met($dependent_id)) {
                        update_post_meta($dependent_id, '_slm_task_status', 'available');
                        self::send_task_available_notification($dependent_id);
                    }
                    $affected_tasks[] = ['id' => $dependent_id, 'action' => 'reassigned'];
                    break;
            }
        }
        
        SLM_Task_Audit::log('task_cancelled', 'task', $task_id, [
            'cancelled_by' => get_current_user_id(),
            'reason' => $reason,
            'handle_dependents' => $handle_dependents,
            'affected_tasks' => $affected_tasks
        ]);
        
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        self::update_progress_snapshot($case_id);
        
        return ['affected_tasks' => $affected_tasks];
    }
    
    /**
     * Get tasks for a case
     */
    public static function get_tasks_for_case($case_id, $filters = []) {
        $args = [
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_case_id', 'value' => $case_id]
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => '_slm_sequence_order',
            'order' => 'ASC'
        ];
        
        // Filter by status
        if (!empty($filters['status'])) {
            $args['meta_query'][] = [
                'key' => '_slm_task_status',
                'value' => (array) $filters['status'],
                'compare' => 'IN'
            ];
        }
        
        // Filter by assigned user (my tasks view)
        if (isset($filters['view']) && $filters['view'] === 'my' && !empty($filters['user_id'])) {
            $args['meta_query'][] = [
                'key' => '_slm_assigned_user',
                'value' => $filters['user_id']
            ];
        }
        
        // Filter by task list
        if (!empty($filters['task_list_id'])) {
            $args['meta_query'][] = [
                'key' => '_slm_source_task_list',
                'value' => $filters['task_list_id']
            ];
        }
        
        $tasks = get_posts($args);
        return array_map([__CLASS__, 'get_task_data'], $tasks);
    }
    
    /**
     * Get user's tasks across all cases
     */
    public static function get_user_tasks($user_id, $filters = []) {
        $args = [
            'post_type' => 'slm_task_instance',
            'posts_per_page' => $filters['limit'] ?? -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_assigned_user', 'value' => $user_id]
            ]
        ];
        
        if (!empty($filters['status'])) {
            $args['meta_query'][] = [
                'key' => '_slm_task_status',
                'value' => (array) $filters['status'],
                'compare' => 'IN'
            ];
        }
        
        if (!empty($filters['has_due_date'])) {
            $args['meta_query'][] = [
                'key' => '_slm_due_date',
                'compare' => 'EXISTS'
            ];
        }
        
        return get_posts($args);
    }
    
    /**
     * Get task data as array
     */
    public static function get_task_data($task, $include_details = false) {
        if (is_numeric($task)) {
            $task = get_post($task);
        }
        
        if (!$task) return null;
        
        $task_id = $task->ID;
        $status = get_post_meta($task_id, '_slm_task_status', true);
        $type = get_post_meta($task_id, '_slm_task_type', true);
        
        $data = [
            'id' => $task_id,
            'title' => $task->post_title,
            'status' => $status,
            'status_label' => SLM_Tasks::get_task_statuses()[$status] ?? $status,
            'type' => $type,
            'type_label' => SLM_Tasks::get_task_types()[$type] ?? $type,
            'case_id' => get_post_meta($task_id, '_slm_case_id', true),
            'assigned_user' => get_post_meta($task_id, '_slm_assigned_user', true),
            'due_date' => get_post_meta($task_id, '_slm_due_date', true),
            'sequence_order' => get_post_meta($task_id, '_slm_sequence_order', true),
            'is_ad_hoc' => (bool) get_post_meta($task_id, '_slm_is_ad_hoc', true),
            'created_date' => get_post_meta($task_id, '_slm_created_date', true),
        ];
        
        // Add assigned user name
        if ($data['assigned_user']) {
            $user = get_userdata($data['assigned_user']);
            $data['assigned_user_name'] = $user ? $user->display_name : '';
        }
        
        // Check if overdue
        if ($data['due_date'] && $status !== 'complete' && $status !== 'cancelled') {
            $data['is_overdue'] = strtotime($data['due_date']) < strtotime('today');
        } else {
            $data['is_overdue'] = false;
        }
        
        if ($include_details) {
            $data['description'] = get_post_meta($task_id, '_slm_task_description', true);
            $data['task_list_id'] = get_post_meta($task_id, '_slm_source_task_list', true);
            $data['template_id'] = get_post_meta($task_id, '_slm_source_task_template', true);
            $data['dependencies'] = get_post_meta($task_id, '_slm_dependencies', true) ?: [];
            $data['completed_date'] = get_post_meta($task_id, '_slm_completed_date', true);
            $data['completed_by'] = get_post_meta($task_id, '_slm_completed_by', true);
            $data['edit_history'] = get_post_meta($task_id, '_slm_edit_history', true) ?: [];
            
            // Type-specific data
            $data['type_data'] = self::get_type_specific_data($task_id, $type);
        }
        
        return $data;
    }
    
    /**
     * Get case progress by task list
     */
    public static function get_case_progress($case_id) {
        $tasks = get_posts([
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_case_id', 'value' => $case_id],
                ['key' => '_slm_task_status', 'value' => 'cancelled', 'compare' => '!=']
            ]
        ]);
        
        $by_list = [];
        $ad_hoc = ['total' => 0, 'completed' => 0];
        
        foreach ($tasks as $task) {
            $task_list_id = get_post_meta($task->ID, '_slm_source_task_list', true);
            $status = get_post_meta($task->ID, '_slm_task_status', true);
            $is_complete = ($status === 'complete');
            
            if (empty($task_list_id)) {
                $ad_hoc['total']++;
                if ($is_complete) $ad_hoc['completed']++;
            } else {
                if (!isset($by_list[$task_list_id])) {
                    $by_list[$task_list_id] = [
                        'id' => $task_list_id,
                        'name' => get_the_title($task_list_id),
                        'total' => 0,
                        'completed' => 0
                    ];
                }
                $by_list[$task_list_id]['total']++;
                if ($is_complete) $by_list[$task_list_id]['completed']++;
            }
        }
        
        // Calculate percentages
        foreach ($by_list as &$list) {
            $list['percentage'] = $list['total'] > 0 
                ? round(($list['completed'] / $list['total']) * 100, 1) 
                : 0;
        }
        
        $ad_hoc['percentage'] = $ad_hoc['total'] > 0 
            ? round(($ad_hoc['completed'] / $ad_hoc['total']) * 100, 1) 
            : 0;
        
        // Overall
        $total = array_sum(array_column($by_list, 'total')) + $ad_hoc['total'];
        $completed = array_sum(array_column($by_list, 'completed')) + $ad_hoc['completed'];
        
        return [
            'overall' => [
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
            ],
            'by_list' => array_values($by_list),
            'ad_hoc' => $ad_hoc
        ];
    }
    
    /**
     * Process overdue tasks (cron job)
     */
    public static function process_overdue_tasks() {
        global $wpdb;
        
        $today = date('Y-m-d');
        
        $overdue_tasks = get_posts([
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_task_status', 'value' => ['available', 'in_progress', 'pending'], 'compare' => 'IN'],
                ['key' => '_slm_due_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE']
            ]
        ]);
        
        $config = get_option('slm_notification_config');
        $warning_days = $config['task_due_warning_days'] ?? [3, 1];
        
        foreach ($overdue_tasks as $task) {
            $due_date = get_post_meta($task->ID, '_slm_due_date', true);
            $days_overdue = (strtotime('today') - strtotime($due_date)) / DAY_IN_SECONDS;
            
            // Check if we already sent overdue notification
            $last_overdue_notice = get_post_meta($task->ID, '_slm_last_overdue_notice', true);
            if ($last_overdue_notice === $today) {
                continue;
            }
            
            $assigned_user = get_post_meta($task->ID, '_slm_assigned_user', true);
            $case_id = get_post_meta($task->ID, '_slm_case_id', true);
            
            SLM_Notifications::create([
                'recipient' => $assigned_user,
                'type' => 'task_overdue',
                'case_id' => $case_id,
                'task_id' => $task->ID,
                'priority' => 'urgent',
                'title' => 'Task Overdue',
                'body' => sprintf(
                    'The task "%s" is %d day(s) overdue.',
                    $task->post_title,
                    $days_overdue
                )
            ]);
            
            update_post_meta($task->ID, '_slm_last_overdue_notice', $today);
        }
        
        // Also check for upcoming due dates
        foreach ($warning_days as $days) {
            $warning_date = date('Y-m-d', strtotime("+{$days} days"));
            
            $upcoming_tasks = get_posts([
                'post_type' => 'slm_task_instance',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => [
                    ['key' => '_slm_task_status', 'value' => ['available', 'in_progress'], 'compare' => 'IN'],
                    ['key' => '_slm_due_date', 'value' => $warning_date, 'compare' => '=']
                ]
            ]);
            
            foreach ($upcoming_tasks as $task) {
                $assigned_user = get_post_meta($task->ID, '_slm_assigned_user', true);
                $case_id = get_post_meta($task->ID, '_slm_case_id', true);
                
                SLM_Notifications::create([
                    'recipient' => $assigned_user,
                    'type' => 'task_due_soon',
                    'case_id' => $case_id,
                    'task_id' => $task->ID,
                    'title' => 'Task Due Soon',
                    'body' => sprintf(
                        'The task "%s" is due in %d day(s).',
                        $task->post_title,
                        $days
                    )
                ]);
            }
        }
    }
    
    /**
     * Process escalations (cron job)
     */
    public static function process_escalations() {
        $config = get_option('slm_notification_config');
        $escalation_days = $config['escalation_days'] ?? [1, 3, 7, 14];
        
        $overdue_tasks = get_posts([
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_task_status', 'value' => ['available', 'in_progress'], 'compare' => 'IN'],
                ['key' => '_slm_due_date', 'value' => date('Y-m-d'), 'compare' => '<', 'type' => 'DATE']
            ]
        ]);
        
        foreach ($overdue_tasks as $task) {
            $due_date = get_post_meta($task->ID, '_slm_due_date', true);
            $days_overdue = (strtotime('today') - strtotime($due_date)) / DAY_IN_SECONDS;
            $case_id = get_post_meta($task->ID, '_slm_case_id', true);
            $assigned_user = get_post_meta($task->ID, '_slm_assigned_user', true);
            
            $escalation_level = get_post_meta($task->ID, '_slm_escalation_level', true) ?: 0;
            $last_escalation = get_post_meta($task->ID, '_slm_last_escalation_date', true);
            
            // Determine current escalation level
            $new_level = 0;
            foreach ($escalation_days as $idx => $threshold) {
                if ($days_overdue >= $threshold) {
                    $new_level = $idx + 1;
                }
            }
            
            if ($new_level > $escalation_level || ($new_level === 4 && $last_escalation !== date('Y-m-d'))) {
                $recipients = [$assigned_user];
                
                // Level 2+: Include primary lawyer
                if ($new_level >= 2) {
                    $lawyer_id = get_post_meta($case_id, '_slm_lead_lawyer', true);
                    if ($lawyer_id && !in_array($lawyer_id, $recipients)) {
                        $recipients[] = $lawyer_id;
                    }
                }
                
                // Level 3+: Include all case team and supervisors
                if ($new_level >= 3) {
                    $team = get_post_meta($case_id, '_slm_case_team', true) ?: [];
                    $supervisors = get_post_meta($case_id, '_slm_supervisors', true) ?: [];
                    $recipients = array_unique(array_merge($recipients, $team, $supervisors));
                }
                
                foreach ($recipients as $recipient_id) {
                    SLM_Notifications::create([
                        'recipient' => $recipient_id,
                        'type' => 'escalation',
                        'case_id' => $case_id,
                        'task_id' => $task->ID,
                        'priority' => 'urgent',
                        'title' => 'Task Escalation',
                        'body' => sprintf(
                            '[Level %d Escalation] The task "%s" is %d day(s) overdue.',
                            $new_level,
                            $task->post_title,
                            $days_overdue
                        )
                    ]);
                }
                
                update_post_meta($task->ID, '_slm_escalation_level', $new_level);
                update_post_meta($task->ID, '_slm_last_escalation_date', date('Y-m-d'));
                
                SLM_Task_Audit::log('task_escalated', 'task', $task->ID, [
                    'level' => $new_level,
                    'days_overdue' => $days_overdue,
                    'notified' => $recipients
                ]);
            }
        }
    }
    
    // Private helper methods
    
    private static function get_working_days_for_tier($template_id, $tier) {
        $standard = get_post_meta($template_id, '_slm_working_days_standard', true) ?: 5;
        
        if ($tier === 'fast') {
            $override = get_post_meta($template_id, '_slm_working_days_fast_override', true);
            return $override ?: $standard;
        } elseif ($tier === 'expedited') {
            $override = get_post_meta($template_id, '_slm_working_days_expedited_override', true);
            return $override ?: $standard;
        }
        
        return $standard;
    }
    
    private static function copy_type_specific_settings($instance_id, $template_id, $type) {
        switch ($type) {
            case 'upload':
                foreach (['allowed_file_types', 'max_file_size_mb', 'document_category'] as $field) {
                    $value = get_post_meta($template_id, '_slm_' . $field, true);
                    if ($value) update_post_meta($instance_id, '_slm_' . $field, $value);
                }
                break;
                
            case 'form':
                foreach (['gravity_form_id', 'completion_trigger', 'pre_populate_fields'] as $field) {
                    $value = get_post_meta($template_id, '_slm_' . $field, true);
                    if ($value) update_post_meta($instance_id, '_slm_' . $field, $value);
                }
                break;
                
            case 'payment':
                foreach (['payment_type', 'fixed_amount', 'currency', 'stripe_enabled', 'payment_instructions'] as $field) {
                    $value = get_post_meta($template_id, '_slm_' . $field, true);
                    if ($value) update_post_meta($instance_id, '_slm_' . $field, $value);
                }
                break;
                
            case 'signature':
                foreach (['document_template_id', 'require_initials', 'witness_required'] as $field) {
                    $value = get_post_meta($template_id, '_slm_' . $field, true);
                    if ($value) update_post_meta($instance_id, '_slm_' . $field, $value);
                }
                break;
                
            case 'external':
                foreach (['external_instructions', 'external_url', 'require_proof', 'require_lawyer_verification'] as $field) {
                    $value = get_post_meta($template_id, '_slm_' . $field, true);
                    if ($value) update_post_meta($instance_id, '_slm_' . $field, $value);
                }
                break;
        }
    }
    
    private static function get_type_specific_data($task_id, $type) {
        $data = [];
        
        switch ($type) {
            case 'upload':
                $data['allowed_file_types'] = get_post_meta($task_id, '_slm_allowed_file_types', true);
                $data['max_file_size_mb'] = get_post_meta($task_id, '_slm_max_file_size_mb', true);
                $data['document_category'] = get_post_meta($task_id, '_slm_document_category', true);
                $data['uploaded_document_id'] = get_post_meta($task_id, '_slm_uploaded_document_id', true);
                break;
                
            case 'form':
                $data['gravity_form_id'] = get_post_meta($task_id, '_slm_gravity_form_id', true);
                $data['entry_id'] = get_post_meta($task_id, '_slm_gravity_form_entry_id', true);
                $data['form_summary'] = get_post_meta($task_id, '_slm_cached_form_summary', true);
                break;
                
            case 'payment':
                $data['payment_type'] = get_post_meta($task_id, '_slm_payment_type', true);
                $data['amount'] = get_post_meta($task_id, '_slm_fixed_amount', true);
                $data['currency'] = get_post_meta($task_id, '_slm_currency', true);
                $data['stripe_enabled'] = get_post_meta($task_id, '_slm_stripe_enabled', true);
                $data['payment_method'] = get_post_meta($task_id, '_slm_payment_method', true);
                $data['transaction_reference'] = get_post_meta($task_id, '_slm_transaction_reference', true);
                break;
                
            case 'signature':
                $data['envelope_id'] = get_post_meta($task_id, '_slm_envelope_id', true);
                $data['signed_document_id'] = get_post_meta($task_id, '_slm_signed_document_id', true);
                break;
                
            case 'external':
                $data['instructions'] = get_post_meta($task_id, '_slm_external_instructions', true);
                $data['external_url'] = get_post_meta($task_id, '_slm_external_url', true);
                $data['require_proof'] = get_post_meta($task_id, '_slm_require_proof', true);
                $data['require_verification'] = get_post_meta($task_id, '_slm_require_lawyer_verification', true);
                $data['verification_status'] = get_post_meta($task_id, '_slm_verification_status', true);
                $data['completion_notes'] = get_post_meta($task_id, '_slm_external_completion_notes', true);
                $data['external_reference'] = get_post_meta($task_id, '_slm_external_reference', true);
                break;
        }
        
        return $data;
    }
    
    private static function evaluate_conditional_rules($rules, $case_id) {
        if (empty($rules)) return true;
        
        foreach ($rules as $rule) {
            $field = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? '=';
            $value = $rule['value'] ?? '';
            
            $actual_value = get_post_meta($case_id, $field, true);
            
            switch ($operator) {
                case '=':
                    if ($actual_value != $value) return false;
                    break;
                case '!=':
                    if ($actual_value == $value) return false;
                    break;
                case 'contains':
                    if (strpos($actual_value, $value) === false) return false;
                    break;
                case 'exists':
                    if (empty($actual_value)) return false;
                    break;
                case 'not_exists':
                    if (!empty($actual_value)) return false;
                    break;
            }
        }
        
        return true;
    }
    
    private static function create_dependency_records($task_id, $dependency_ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_task_dependencies';
        
        foreach ($dependency_ids as $dep_id) {
            $wpdb->insert($table, [
                'task_instance_id' => $task_id,
                'depends_on_task_id' => $dep_id,
                'dependency_type' => 'completion',
                'status' => 'pending'
            ]);
        }
    }
    
    private static function process_dependent_tasks($completed_task_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_task_dependencies';
        
        // Mark this dependency as resolved
        $wpdb->update(
            $table,
            ['status' => 'resolved', 'resolved_at' => current_time('mysql')],
            ['depends_on_task_id' => $completed_task_id, 'status' => 'pending']
        );
        
        // Find tasks that were waiting on this one
        $dependent_tasks = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT task_instance_id FROM {$table} WHERE depends_on_task_id = %d",
            $completed_task_id
        ));
        
        foreach ($dependent_tasks as $task_id) {
            if (self::all_dependencies_met($task_id)) {
                $current_status = get_post_meta($task_id, '_slm_task_status', true);
                if ($current_status === 'locked') {
                    update_post_meta($task_id, '_slm_task_status', 'available');
                    self::send_task_available_notification($task_id);
                }
            }
        }
    }
    
    private static function all_dependencies_met($task_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_task_dependencies';
        
        $pending = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE task_instance_id = %d AND status = 'pending'",
            $task_id
        ));
        
        return (int) $pending === 0;
    }
    
    private static function get_dependent_tasks($task_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_task_dependencies';
        
        return $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT task_instance_id FROM {$table} WHERE depends_on_task_id = %d",
            $task_id
        ));
    }
    
    private static function remove_dependency($task_id, $dependency_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_task_dependencies';
        
        $wpdb->update(
            $table,
            ['status' => 'removed', 'resolved_at' => current_time('mysql')],
            ['task_instance_id' => $task_id, 'depends_on_task_id' => $dependency_id]
        );
    }
    
    private static function send_task_available_notification($task_id) {
        $task = get_post($task_id);
        if (!$task) return;
        
        $assigned_user = get_post_meta($task_id, '_slm_assigned_user', true);
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        
        if ($assigned_user) {
            SLM_Notifications::create([
                'recipient' => $assigned_user,
                'type' => 'task_available',
                'case_id' => $case_id,
                'task_id' => $task_id,
                'title' => 'New Task Available',
                'body' => sprintf('A new task is available: "%s"', $task->post_title)
            ]);
        }
    }
    
    private static function send_task_completed_notification($task_id, $completed_by) {
        $task = get_post($task_id);
        if (!$task) return;
        
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        $lawyer_id = get_post_meta($case_id, '_slm_lead_lawyer', true);
        $assigned_user = get_post_meta($task_id, '_slm_assigned_user', true);
        
        // Notify lawyer if client completed
        if ($lawyer_id && (int) $completed_by !== (int) $lawyer_id) {
            $completer = get_userdata($completed_by);
            SLM_Notifications::create([
                'recipient' => $lawyer_id,
                'type' => 'task_completed',
                'case_id' => $case_id,
                'task_id' => $task_id,
                'title' => 'Task Completed',
                'body' => sprintf(
                    '%s has completed the task "%s".',
                    $completer ? $completer->display_name : 'A user',
                    $task->post_title
                )
            ]);
        }
    }
    
    private static function update_progress_snapshot($case_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_progress_snapshots';
        
        $progress = self::get_case_progress($case_id);
        $today = date('Y-m-d');
        
        // Overall snapshot
        $wpdb->replace($table, [
            'case_id' => $case_id,
            'task_list_id' => 0,
            'total_tasks' => $progress['overall']['total'],
            'completed_tasks' => $progress['overall']['completed'],
            'percentage' => $progress['overall']['percentage'],
            'snapshot_date' => $today
        ]);
        
        // Per-list snapshots
        foreach ($progress['by_list'] as $list) {
            $wpdb->replace($table, [
                'case_id' => $case_id,
                'task_list_id' => $list['id'],
                'total_tasks' => $list['total'],
                'completed_tasks' => $list['completed'],
                'percentage' => $list['percentage'],
                'snapshot_date' => $today
            ]);
        }
    }
}
