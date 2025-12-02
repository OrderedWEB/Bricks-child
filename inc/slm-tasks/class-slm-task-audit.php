<?php
/**
 * SLM Task Audit
 * 
 * Handles audit logging for task system activities.
 * All actions are logged for compliance and debugging.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_Audit {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('admin_menu', [__CLASS__, 'add_admin_pages']);
        
        // Scheduled cleanup
        add_action('slm_audit_cleanup', [__CLASS__, 'cleanup_old_logs']);
        
        if (!wp_next_scheduled('slm_audit_cleanup')) {
            wp_schedule_event(time(), 'daily', 'slm_audit_cleanup');
        }
    }
    
    public static function add_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=slm_task_list',
            __('Audit Log', 'flavor'),
            __('Audit Log', 'flavor'),
            'manage_options',
            'slm-audit-log',
            [__CLASS__, 'render_audit_page']
        );
    }
    
    /**
     * Log an audit event
     */
    public static function log($event_type, $object_type, $object_id, $metadata = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_audit_log';
        
        $user_id = get_current_user_id();
        
        // Get IP address
        $ip_address = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        
        // Get user agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) 
            ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) 
            : '';
        
        // Extract old/new values if present
        $old_value = isset($metadata['old_values']) ? wp_json_encode($metadata['old_values']) : null;
        $new_value = isset($metadata['new_values']) ? wp_json_encode($metadata['new_values']) : null;
        
        // Remove from metadata to avoid duplication
        unset($metadata['old_values'], $metadata['new_values']);
        
        $result = $wpdb->insert($table, [
            'event_type' => $event_type,
            'object_type' => $object_type,
            'object_id' => $object_id,
            'user_id' => $user_id ?: null,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'metadata' => !empty($metadata) ? wp_json_encode($metadata) : null,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => current_time('mysql')
        ]);
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get audit logs
     */
    public static function get_logs($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_audit_log';
        
        $defaults = [
            'object_type' => null,
            'object_id' => null,
            'event_type' => null,
            'user_id' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['1=1'];
        $values = [];
        
        if ($args['object_type']) {
            $where[] = 'object_type = %s';
            $values[] = $args['object_type'];
        }
        
        if ($args['object_id']) {
            $where[] = 'object_id = %d';
            $values[] = $args['object_id'];
        }
        
        if ($args['event_type']) {
            $where[] = 'event_type = %s';
            $values[] = $args['event_type'];
        }
        
        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }
        
        if ($args['date_from']) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'];
        }
        
        $orderby = in_array($args['orderby'], ['created_at', 'event_type', 'object_type']) 
            ? $args['orderby'] : 'created_at';
        $order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) 
            . " ORDER BY {$orderby} {$order}"
            . " LIMIT %d OFFSET %d";
        
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        return array_map([__CLASS__, 'format_log'], $results);
    }
    
    /**
     * Get logs count
     */
    public static function get_logs_count($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_audit_log';
        
        $where = ['1=1'];
        $values = [];
        
        if (!empty($args['object_type'])) {
            $where[] = 'object_type = %s';
            $values[] = $args['object_type'];
        }
        
        if (!empty($args['object_id'])) {
            $where[] = 'object_id = %d';
            $values[] = $args['object_id'];
        }
        
        if (!empty($args['event_type'])) {
            $where[] = 'event_type = %s';
            $values[] = $args['event_type'];
        }
        
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $where);
        
        if (!empty($values)) {
            return (int) $wpdb->get_var($wpdb->prepare($sql, $values));
        }
        
        return (int) $wpdb->get_var($sql);
    }
    
    /**
     * Get logs for specific object
     */
    public static function get_object_history($object_type, $object_id, $limit = 50) {
        return self::get_logs([
            'object_type' => $object_type,
            'object_id' => $object_id,
            'limit' => $limit
        ]);
    }
    
    /**
     * Clean up old logs
     */
    public static function cleanup_old_logs() {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_audit_log';
        
        $config = get_option('slm_audit_retention_config');
        $retention_years = $config['minimum_retention_years'] ?? 7;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_years} years"));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE created_at < %s",
            $cutoff_date
        ));
        
        if ($deleted > 0) {
            self::log('audit_cleanup', 'system', 0, [
                'deleted_count' => $deleted,
                'cutoff_date' => $cutoff_date
            ]);
        }
        
        return $deleted;
    }
    
    /**
     * Format log entry for display
     */
    private static function format_log($log) {
        $user = $log->user_id ? get_userdata($log->user_id) : null;
        
        return [
            'id' => $log->id,
            'event_type' => $log->event_type,
            'event_label' => self::get_event_label($log->event_type),
            'object_type' => $log->object_type,
            'object_id' => $log->object_id,
            'object_title' => self::get_object_title($log->object_type, $log->object_id),
            'user_id' => $log->user_id,
            'user_name' => $user ? $user->display_name : __('System', 'flavor'),
            'old_value' => $log->old_value ? json_decode($log->old_value, true) : null,
            'new_value' => $log->new_value ? json_decode($log->new_value, true) : null,
            'metadata' => $log->metadata ? json_decode($log->metadata, true) : null,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at,
            'created_at_formatted' => date('d/m/Y H:i:s', strtotime($log->created_at))
        ];
    }
    
    /**
     * Get human-readable event label
     */
    private static function get_event_label($event_type) {
        $labels = [
            'task_created' => __('Task Created', 'flavor'),
            'task_completed' => __('Task Completed', 'flavor'),
            'task_edited' => __('Task Edited', 'flavor'),
            'task_cancelled' => __('Task Cancelled', 'flavor'),
            'task_verified' => __('Task Verified', 'flavor'),
            'task_pending_review' => __('Task Pending Review', 'flavor'),
            'task_escalated' => __('Task Escalated', 'flavor'),
            'task_reassigned' => __('Task Reassigned', 'flavor'),
            'task_list_applied' => __('Task List Applied', 'flavor'),
            'case_created' => __('Case Created', 'flavor'),
            'case_status_changed' => __('Case Status Changed', 'flavor'),
            'case_claimed' => __('Case Claimed', 'flavor'),
            'document_uploaded' => __('Document Uploaded', 'flavor'),
            'form_submitted' => __('Form Submitted', 'flavor'),
            'payment_received' => __('Payment Received', 'flavor'),
            'signature_completed' => __('Signature Completed', 'flavor'),
            'notification_sent' => __('Notification Sent', 'flavor'),
            'login_attempt' => __('Login Attempt', 'flavor'),
            'login_success' => __('Login Success', 'flavor'),
            'login_failed' => __('Login Failed', 'flavor'),
            'account_locked' => __('Account Locked', 'flavor'),
            'audit_cleanup' => __('Audit Cleanup', 'flavor'),
        ];
        
        return $labels[$event_type] ?? $event_type;
    }
    
    /**
     * Get object title for display
     */
    private static function get_object_title($object_type, $object_id) {
        if ($object_type === 'system') {
            return __('System', 'flavor');
        }
        
        $post = get_post($object_id);
        return $post ? $post->post_title : '#' . $object_id;
    }
    
    /**
     * Render audit log admin page
     */
    public static function render_audit_page() {
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;
        $offset = ($current_page - 1) * $per_page;
        
        $filters = [
            'event_type' => isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : null,
            'object_type' => isset($_GET['object_type']) ? sanitize_text_field($_GET['object_type']) : null,
            'user_id' => isset($_GET['user_id']) ? intval($_GET['user_id']) : null,
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : null,
        ];
        
        $logs = self::get_logs(array_merge($filters, [
            'limit' => $per_page,
            'offset' => $offset
        ]));
        
        $total = self::get_logs_count($filters);
        $total_pages = ceil($total / $per_page);
        ?>
        <div class="wrap">
            <h1><?php _e('Audit Log', 'flavor'); ?></h1>
            
            <form method="get" class="slm-audit-filters" style="margin:20px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="post_type" value="slm_task_list">
                <input type="hidden" name="page" value="slm-audit-log">
                
                <div>
                    <label><?php _e('Event Type:', 'flavor'); ?></label><br>
                    <select name="event_type">
                        <option value=""><?php _e('All', 'flavor'); ?></option>
                        <?php foreach (self::get_event_types() as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['event_type'], $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label><?php _e('Object Type:', 'flavor'); ?></label><br>
                    <select name="object_type">
                        <option value=""><?php _e('All', 'flavor'); ?></option>
                        <option value="task" <?php selected($filters['object_type'], 'task'); ?>><?php _e('Task', 'flavor'); ?></option>
                        <option value="case" <?php selected($filters['object_type'], 'case'); ?>><?php _e('Case', 'flavor'); ?></option>
                        <option value="task_list" <?php selected($filters['object_type'], 'task_list'); ?>><?php _e('Task List', 'flavor'); ?></option>
                        <option value="system" <?php selected($filters['object_type'], 'system'); ?>><?php _e('System', 'flavor'); ?></option>
                    </select>
                </div>
                
                <div>
                    <label><?php _e('Date From:', 'flavor'); ?></label><br>
                    <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>">
                </div>
                
                <div>
                    <label><?php _e('Date To:', 'flavor'); ?></label><br>
                    <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>">
                </div>
                
                <div>
                    <button type="submit" class="button"><?php _e('Filter', 'flavor'); ?></button>
                    <a href="<?php echo admin_url('edit.php?post_type=slm_task_list&page=slm-audit-log'); ?>" class="button">
                        <?php _e('Reset', 'flavor'); ?>
                    </a>
                </div>
            </form>
            
            <p class="description">
                <?php printf(__('Showing %d of %d entries', 'flavor'), count($logs), $total); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="150"><?php _e('Date/Time', 'flavor'); ?></th>
                        <th width="120"><?php _e('Event', 'flavor'); ?></th>
                        <th width="100"><?php _e('Type', 'flavor'); ?></th>
                        <th><?php _e('Object', 'flavor'); ?></th>
                        <th width="120"><?php _e('User', 'flavor'); ?></th>
                        <th><?php _e('Details', 'flavor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6"><?php _e('No audit log entries found.', 'flavor'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['created_at_formatted']); ?></td>
                                <td>
                                    <span class="slm-event-badge slm-event-<?php echo esc_attr(self::get_event_severity($log['event_type'])); ?>">
                                        <?php echo esc_html($log['event_label']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(ucfirst($log['object_type'])); ?></td>
                                <td>
                                    <?php if ($log['object_type'] !== 'system'): ?>
                                        <a href="<?php echo get_edit_post_link($log['object_id']); ?>">
                                            <?php echo esc_html($log['object_title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo esc_html($log['object_title']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($log['user_name']); ?></td>
                                <td>
                                    <?php if ($log['metadata']): ?>
                                        <button type="button" class="button button-small slm-view-details" 
                                                data-details='<?php echo esc_attr(wp_json_encode($log)); ?>'>
                                            <?php _e('View', 'flavor'); ?>
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        $page_links = paginate_links([
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        ]);
                        echo $page_links;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Details Modal -->
        <div id="slm-details-modal" style="display:none;">
            <div class="slm-modal-content" style="max-width:600px;margin:50px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.2);max-height:80vh;overflow:auto;">
                <h2><?php _e('Log Details', 'flavor'); ?></h2>
                <pre id="slm-details-content" style="background:#f6f7f7;padding:15px;overflow:auto;max-height:400px;"></pre>
                <p style="text-align:right">
                    <button type="button" class="button slm-close-modal"><?php _e('Close', 'flavor'); ?></button>
                </p>
            </div>
        </div>
        
        <style>
            #slm-details-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 100000;
            }
            .slm-event-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 500;
            }
            .slm-event-info { background: #e5f5fa; color: #006ba1; }
            .slm-event-success { background: #d4edda; color: #155724; }
            .slm-event-warning { background: #fff3cd; color: #856404; }
            .slm-event-danger { background: #f8d7da; color: #721c24; }
        </style>
        
        <script>
        jQuery(function($) {
            $('.slm-view-details').on('click', function() {
                var details = $(this).data('details');
                $('#slm-details-content').text(JSON.stringify(details, null, 2));
                $('#slm-details-modal').show();
            });
            
            $('.slm-close-modal, #slm-details-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#slm-details-modal').hide();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get all event types for filter
     */
    private static function get_event_types() {
        return [
            'task_created' => __('Task Created', 'flavor'),
            'task_completed' => __('Task Completed', 'flavor'),
            'task_edited' => __('Task Edited', 'flavor'),
            'task_cancelled' => __('Task Cancelled', 'flavor'),
            'task_verified' => __('Task Verified', 'flavor'),
            'task_escalated' => __('Task Escalated', 'flavor'),
            'task_list_applied' => __('Task List Applied', 'flavor'),
            'case_created' => __('Case Created', 'flavor'),
            'case_status_changed' => __('Case Status Changed', 'flavor'),
            'login_failed' => __('Login Failed', 'flavor'),
            'account_locked' => __('Account Locked', 'flavor'),
        ];
    }
    
    /**
     * Get event severity for styling
     */
    private static function get_event_severity($event_type) {
        $severities = [
            'task_completed' => 'success',
            'task_verified' => 'success',
            'case_created' => 'success',
            'task_created' => 'info',
            'task_list_applied' => 'info',
            'task_edited' => 'warning',
            'task_escalated' => 'warning',
            'task_cancelled' => 'danger',
            'login_failed' => 'danger',
            'account_locked' => 'danger',
        ];
        
        return $severities[$event_type] ?? 'info';
    }
}
