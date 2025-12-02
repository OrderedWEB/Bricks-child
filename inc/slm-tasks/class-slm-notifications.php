<?php
/**
 * SLM Notifications
 * 
 * Manages task system notifications including
 * creation, delivery, and digest emails.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Notifications {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('admin_bar_menu', [__CLASS__, 'add_admin_bar_icon'], 100);
        add_action('wp_footer', [__CLASS__, 'render_notification_panel']);
        add_action('admin_footer', [__CLASS__, 'render_notification_panel']);
    }
    
    /**
     * Create a notification
     */
    public static function create($args) {
        $defaults = [
            'recipient' => 0,
            'type' => 'general',
            'case_id' => 0,
            'task_id' => 0,
            'title' => '',
            'body' => '',
            'priority' => 'normal'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        if (empty($args['recipient']) || empty($args['title'])) {
            return new WP_Error('missing_data', 'Recipient and title are required');
        }
        
        // Check user notification preferences
        $user_prefs = self::get_user_preferences($args['recipient']);
        
        // Check for duplicate prevention
        if (self::is_duplicate($args)) {
            return false;
        }
        
        // Create notification post
        $notification_id = wp_insert_post([
            'post_type' => 'slm_notification',
            'post_title' => $args['title'],
            'post_status' => 'publish',
            'post_author' => 1
        ]);
        
        if (is_wp_error($notification_id)) {
            return $notification_id;
        }
        
        // Set meta fields
        update_post_meta($notification_id, '_slm_recipient', $args['recipient']);
        update_post_meta($notification_id, '_slm_notification_type', $args['type']);
        update_post_meta($notification_id, '_slm_related_case', $args['case_id']);
        update_post_meta($notification_id, '_slm_related_task', $args['task_id']);
        update_post_meta($notification_id, '_slm_message_title', $args['title']);
        update_post_meta($notification_id, '_slm_message_body', $args['body']);
        update_post_meta($notification_id, '_slm_priority', $args['priority']);
        update_post_meta($notification_id, '_slm_read_status', false);
        update_post_meta($notification_id, '_slm_email_sent', false);
        update_post_meta($notification_id, '_slm_created_at', current_time('mysql'));
        
        // Send immediate email if not in digest mode
        $config = get_option('slm_notification_config');
        
        if (!empty($config['enable_email_notifications'])) {
            if (!$user_prefs['digest_mode'] || $args['priority'] === 'urgent') {
                self::send_email($notification_id);
            }
        }
        
        return $notification_id;
    }
    
    /**
     * Get user notifications
     */
    public static function get_user_notifications($user_id, $args = []) {
        $defaults = [
            'unread_only' => false,
            'limit' => 20,
            'offset' => 0,
            'type' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = [
            'post_type' => 'slm_notification',
            'posts_per_page' => $args['limit'],
            'offset' => $args['offset'],
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => '_slm_recipient', 'value' => $user_id]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        if ($args['unread_only']) {
            $query_args['meta_query'][] = [
                'key' => '_slm_read_status',
                'value' => '1',
                'compare' => '!='
            ];
        }
        
        if ($args['type']) {
            $query_args['meta_query'][] = [
                'key' => '_slm_notification_type',
                'value' => $args['type']
            ];
        }
        
        $notifications = get_posts($query_args);
        
        return array_map([__CLASS__, 'format_notification'], $notifications);
    }
    
    /**
     * Get unread count
     */
    public static function get_unread_count($user_id) {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_slm_recipient'
             JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_slm_read_status'
             WHERE p.post_type = 'slm_notification'
             AND p.post_status = 'publish'
             AND pm1.meta_value = %d
             AND pm2.meta_value != '1'",
            $user_id
        ));
    }
    
    /**
     * Mark notification as read
     */
    public static function mark_read($notification_id, $user_id = null) {
        if ($user_id) {
            // Verify ownership
            $recipient = get_post_meta($notification_id, '_slm_recipient', true);
            if ((int) $recipient !== (int) $user_id) {
                return false;
            }
        }
        
        update_post_meta($notification_id, '_slm_read_status', true);
        update_post_meta($notification_id, '_slm_read_timestamp', current_time('mysql'));
        
        return true;
    }
    
    /**
     * Mark all as read for user
     */
    public static function mark_all_read($user_id) {
        global $wpdb;
        
        $notifications = $wpdb->get_col($wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'slm_notification'
             AND pm.meta_key = '_slm_recipient'
             AND pm.meta_value = %d",
            $user_id
        ));
        
        foreach ($notifications as $id) {
            update_post_meta($id, '_slm_read_status', true);
            update_post_meta($id, '_slm_read_timestamp', current_time('mysql'));
        }
        
        return count($notifications);
    }
    
    /**
     * Send email notification
     */
    public static function send_email($notification_id) {
        $notification = get_post($notification_id);
        if (!$notification) return false;
        
        $recipient_id = get_post_meta($notification_id, '_slm_recipient', true);
        $recipient = get_userdata($recipient_id);
        
        if (!$recipient || empty($recipient->user_email)) {
            return false;
        }
        
        $title = get_post_meta($notification_id, '_slm_message_title', true);
        $body = get_post_meta($notification_id, '_slm_message_body', true);
        $type = get_post_meta($notification_id, '_slm_notification_type', true);
        $case_id = get_post_meta($notification_id, '_slm_related_case', true);
        $task_id = get_post_meta($notification_id, '_slm_related_task', true);
        $priority = get_post_meta($notification_id, '_slm_priority', true);
        
        // Build email content
        $email_body = self::build_email_body([
            'recipient_name' => $recipient->display_name,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'case_id' => $case_id,
            'task_id' => $task_id,
            'priority' => $priority
        ]);
        
        $subject = $title;
        if ($priority === 'urgent') {
            $subject = '[URGENT] ' . $subject;
        }
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $sent = wp_mail($recipient->user_email, $subject, $email_body, $headers);
        
        if ($sent) {
            update_post_meta($notification_id, '_slm_email_sent', true);
            update_post_meta($notification_id, '_slm_email_sent_timestamp', current_time('mysql'));
        }
        
        return $sent;
    }
    
    /**
     * Send daily digest emails
     */
    public static function send_daily_digests() {
        global $wpdb;
        
        // Get users with pending notifications who prefer digest
        $users_with_pending = $wpdb->get_col(
            "SELECT DISTINCT pm.meta_value FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_slm_recipient'
             JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_slm_email_sent'
             WHERE p.post_type = 'slm_notification'
             AND p.post_status = 'publish'
             AND pm2.meta_value != '1'"
        );
        
        foreach ($users_with_pending as $user_id) {
            $prefs = self::get_user_preferences($user_id);
            
            if (!$prefs['digest_mode']) {
                continue;
            }
            
            $pending_notifications = get_posts([
                'post_type' => 'slm_notification',
                'posts_per_page' => -1,
                'meta_query' => [
                    ['key' => '_slm_recipient', 'value' => $user_id],
                    ['key' => '_slm_email_sent', 'value' => '1', 'compare' => '!=']
                ],
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if (empty($pending_notifications)) {
                continue;
            }
            
            $user = get_userdata($user_id);
            if (!$user || empty($user->user_email)) {
                continue;
            }
            
            $email_body = self::build_digest_email($user, $pending_notifications);
            
            $subject = sprintf(
                __('[%s] You have %d new notifications', 'flavor'),
                get_bloginfo('name'),
                count($pending_notifications)
            );
            
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            
            $sent = wp_mail($user->user_email, $subject, $email_body, $headers);
            
            if ($sent) {
                foreach ($pending_notifications as $notification) {
                    update_post_meta($notification->ID, '_slm_email_sent', true);
                    update_post_meta($notification->ID, '_slm_email_sent_timestamp', current_time('mysql'));
                }
            }
        }
    }
    
    /**
     * Add notification icon to admin bar
     */
    public static function add_admin_bar_icon($wp_admin_bar) {
        if (!is_user_logged_in()) return;
        
        $user_id = get_current_user_id();
        $unread = self::get_unread_count($user_id);
        
        $class = $unread > 0 ? 'slm-has-notifications' : '';
        $badge = $unread > 0 ? '<span class="slm-notification-badge">' . ($unread > 99 ? '99+' : $unread) . '</span>' : '';
        
        $wp_admin_bar->add_node([
            'id' => 'slm-notifications',
            'title' => '<span class="ab-icon dashicons dashicons-bell"></span>' . $badge,
            'href' => '#',
            'meta' => [
                'class' => 'slm-notifications-trigger ' . $class,
                'title' => __('Notifications', 'flavor')
            ]
        ]);
    }
    
    /**
     * Render notification panel
     */
    public static function render_notification_panel() {
        if (!is_user_logged_in()) return;
        ?>
        <div id="slm-notification-panel" class="slm-notification-panel" style="display:none;">
            <div class="slm-notification-header">
                <h3><?php _e('Notifications', 'flavor'); ?></h3>
                <button type="button" class="slm-mark-all-read" title="<?php esc_attr_e('Mark all as read', 'flavor'); ?>">
                    <span class="dashicons dashicons-yes-alt"></span>
                </button>
                <button type="button" class="slm-close-panel">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="slm-notification-list">
                <div class="slm-notification-loading">
                    <span class="spinner is-active"></span>
                </div>
            </div>
            <div class="slm-notification-footer">
                <a href="<?php echo esc_url(home_url('/client-portal/notifications/')); ?>">
                    <?php _e('View All Notifications', 'flavor'); ?>
                </a>
            </div>
        </div>
        
        <style>
            .slm-notifications-trigger .ab-icon { position: relative; }
            .slm-notification-badge { 
                position: absolute; top: 2px; right: -8px;
                background: #d63638; color: #fff;
                font-size: 10px; line-height: 1;
                padding: 2px 5px; border-radius: 10px;
                min-width: 14px; text-align: center;
            }
            .slm-notification-panel {
                position: fixed; top: 32px; right: 10px;
                width: 380px; max-height: 500px;
                background: #fff; border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 100001; overflow: hidden;
            }
            .slm-notification-header {
                display: flex; align-items: center;
                padding: 12px 15px; border-bottom: 1px solid #ddd;
                background: #f6f7f7;
            }
            .slm-notification-header h3 { margin: 0; flex: 1; font-size: 14px; }
            .slm-notification-header button {
                background: none; border: none; cursor: pointer;
                padding: 5px; color: #666;
            }
            .slm-notification-header button:hover { color: #2271b1; }
            .slm-notification-list {
                max-height: 380px; overflow-y: auto;
            }
            .slm-notification-item {
                padding: 12px 15px; border-bottom: 1px solid #eee;
                cursor: pointer; transition: background 0.2s;
            }
            .slm-notification-item:hover { background: #f6f7f7; }
            .slm-notification-item.unread { background: #f0f6fc; }
            .slm-notification-item.unread:hover { background: #e5effb; }
            .slm-notification-title { font-weight: 600; font-size: 13px; margin-bottom: 4px; }
            .slm-notification-body { font-size: 12px; color: #666; line-height: 1.4; }
            .slm-notification-meta { font-size: 11px; color: #999; margin-top: 6px; }
            .slm-notification-urgent .slm-notification-title { color: #d63638; }
            .slm-notification-footer {
                padding: 10px 15px; border-top: 1px solid #ddd;
                text-align: center; background: #f6f7f7;
            }
            .slm-notification-footer a { font-size: 13px; text-decoration: none; }
            .slm-notification-empty {
                padding: 40px 20px; text-align: center; color: #666;
            }
            .slm-notification-loading { padding: 30px; text-align: center; }
        </style>
        
        <script>
        jQuery(function($) {
            var $panel = $('#slm-notification-panel');
            var $trigger = $('.slm-notifications-trigger');
            var loaded = false;
            
            $trigger.on('click', function(e) {
                e.preventDefault();
                $panel.toggle();
                
                if ($panel.is(':visible') && !loaded) {
                    loadNotifications();
                }
            });
            
            $('.slm-close-panel').on('click', function() {
                $panel.hide();
            });
            
            $('.slm-mark-all-read').on('click', function() {
                $.ajax({
                    url: slmTasksConfig ? slmTasksConfig.ajaxUrl : ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_mark_notification_read',
                        nonce: slmTasksConfig ? slmTasksConfig.nonce : '',
                        mark_all: 'true'
                    },
                    success: function() {
                        $('.slm-notification-item').removeClass('unread');
                        $('.slm-notification-badge').remove();
                        $trigger.removeClass('slm-has-notifications');
                    }
                });
            });
            
            $(document).on('click', '.slm-notification-item', function() {
                var $item = $(this);
                var id = $item.data('id');
                var url = $item.data('url');
                
                if ($item.hasClass('unread')) {
                    $.ajax({
                        url: slmTasksConfig ? slmTasksConfig.ajaxUrl : ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'slm_mark_notification_read',
                            nonce: slmTasksConfig ? slmTasksConfig.nonce : '',
                            notification_id: id
                        }
                    });
                    $item.removeClass('unread');
                }
                
                if (url) {
                    window.location.href = url;
                }
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#slm-notification-panel, .slm-notifications-trigger').length) {
                    $panel.hide();
                }
            });
            
            function loadNotifications() {
                $.ajax({
                    url: slmTasksConfig ? slmTasksConfig.ajaxUrl : ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'slm_get_notifications',
                        nonce: slmTasksConfig ? slmTasksConfig.nonce : '',
                        limit: 10
                    },
                    success: function(response) {
                        loaded = true;
                        if (response.success) {
                            renderNotifications(response.data.notifications);
                        }
                    }
                });
            }
            
            function renderNotifications(notifications) {
                var $list = $('.slm-notification-list');
                
                if (!notifications || !notifications.length) {
                    $list.html('<div class="slm-notification-empty"><?php _e('No notifications', 'flavor'); ?></div>');
                    return;
                }
                
                var html = '';
                notifications.forEach(function(n) {
                    var classes = ['slm-notification-item'];
                    if (!n.read) classes.push('unread');
                    if (n.priority === 'urgent') classes.push('slm-notification-urgent');
                    
                    html += '<div class="' + classes.join(' ') + '" data-id="' + n.id + '" data-url="' + (n.url || '') + '">';
                    html += '<div class="slm-notification-title">' + escapeHtml(n.title) + '</div>';
                    html += '<div class="slm-notification-body">' + escapeHtml(n.body) + '</div>';
                    html += '<div class="slm-notification-meta">' + n.time_ago + '</div>';
                    html += '</div>';
                });
                
                $list.html(html);
            }
            
            function escapeHtml(text) {
                if (!text) return '';
                return $('<div>').text(text).html();
            }
        });
        </script>
        <?php
    }
    
    // Private helper methods
    
    private static function format_notification($notification) {
        $case_id = get_post_meta($notification->ID, '_slm_related_case', true);
        $task_id = get_post_meta($notification->ID, '_slm_related_task', true);
        
        $url = '';
        if ($task_id) {
            $url = home_url('/client-portal/tasks/?task=' . $task_id);
        } elseif ($case_id) {
            $url = home_url('/client-portal/case/' . $case_id . '/');
        }
        
        return [
            'id' => $notification->ID,
            'title' => get_post_meta($notification->ID, '_slm_message_title', true),
            'body' => get_post_meta($notification->ID, '_slm_message_body', true),
            'type' => get_post_meta($notification->ID, '_slm_notification_type', true),
            'priority' => get_post_meta($notification->ID, '_slm_priority', true),
            'case_id' => $case_id,
            'task_id' => $task_id,
            'read' => (bool) get_post_meta($notification->ID, '_slm_read_status', true),
            'created_at' => get_post_meta($notification->ID, '_slm_created_at', true),
            'time_ago' => human_time_diff(strtotime($notification->post_date), current_time('timestamp')) . ' ' . __('ago', 'flavor'),
            'url' => $url
        ];
    }
    
    private static function is_duplicate($args) {
        global $wpdb;
        
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_slm_recipient'
             JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_slm_notification_type'
             JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_slm_related_task'
             WHERE p.post_type = 'slm_notification'
             AND p.post_date > %s
             AND pm1.meta_value = %d
             AND pm2.meta_value = %s
             AND pm3.meta_value = %d
             LIMIT 1",
            $one_hour_ago,
            $args['recipient'],
            $args['type'],
            $args['task_id']
        ));
        
        return !empty($exists);
    }
    
    private static function get_user_preferences($user_id) {
        $defaults = [
            'email_notifications' => true,
            'digest_mode' => false
        ];
        
        $prefs = get_user_meta($user_id, '_slm_notification_preferences', true);
        
        return wp_parse_args($prefs ?: [], $defaults);
    }
    
    private static function build_email_body($args) {
        $site_name = get_bloginfo('name');
        $portal_url = home_url('/client-portal/');
        
        $task_link = '';
        if ($args['task_id']) {
            $task_link = home_url('/client-portal/tasks/?task=' . $args['task_id']);
        }
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e3a5f; color: #fff; padding: 20px; text-align: center; }
                .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
                .urgent { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 15px 0; }
                .button { display: inline-block; background: #2271b1; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($site_name); ?></h1>
                </div>
                <div class="content">
                    <p><?php printf(__('Hello %s,', 'flavor'), esc_html($args['recipient_name'])); ?></p>
                    
                    <?php if ($args['priority'] === 'urgent'): ?>
                        <div class="urgent">
                            <strong><?php _e('URGENT NOTIFICATION', 'flavor'); ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <h2><?php echo esc_html($args['title']); ?></h2>
                    
                    <p><?php echo wp_kses_post($args['body']); ?></p>
                    
                    <?php if ($task_link): ?>
                        <p>
                            <a href="<?php echo esc_url($task_link); ?>" class="button">
                                <?php _e('View Task', 'flavor'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <p>
                        <a href="<?php echo esc_url($portal_url); ?>" class="button">
                            <?php _e('Go to Portal', 'flavor'); ?>
                        </a>
                    </p>
                </div>
                <div class="footer">
                    <p><?php echo esc_html($site_name); ?></p>
                    <p><?php _e('This is an automated notification. Please do not reply to this email.', 'flavor'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private static function build_digest_email($user, $notifications) {
        $site_name = get_bloginfo('name');
        $portal_url = home_url('/client-portal/');
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e3a5f; color: #fff; padding: 20px; text-align: center; }
                .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
                .notification-item { padding: 15px; border-bottom: 1px solid #eee; }
                .notification-item:last-child { border-bottom: none; }
                .notification-title { font-weight: 600; margin-bottom: 5px; }
                .notification-body { color: #666; font-size: 14px; }
                .notification-meta { font-size: 12px; color: #999; margin-top: 5px; }
                .urgent { border-left: 3px solid #dc2626; padding-left: 12px; }
                .button { display: inline-block; background: #2271b1; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($site_name); ?></h1>
                    <p><?php _e('Daily Notification Digest', 'flavor'); ?></p>
                </div>
                <div class="content">
                    <p><?php printf(__('Hello %s,', 'flavor'), esc_html($user->display_name)); ?></p>
                    
                    <p><?php printf(__('You have %d new notifications:', 'flavor'), count($notifications)); ?></p>
                    
                    <div class="notification-list">
                        <?php foreach ($notifications as $notification): 
                            $priority = get_post_meta($notification->ID, '_slm_priority', true);
                            $title = get_post_meta($notification->ID, '_slm_message_title', true);
                            $body = get_post_meta($notification->ID, '_slm_message_body', true);
                        ?>
                            <div class="notification-item <?php echo $priority === 'urgent' ? 'urgent' : ''; ?>">
                                <div class="notification-title">
                                    <?php if ($priority === 'urgent'): ?>[URGENT] <?php endif; ?>
                                    <?php echo esc_html($title); ?>
                                </div>
                                <div class="notification-body"><?php echo esc_html(wp_trim_words($body, 30)); ?></div>
                                <div class="notification-meta"><?php echo esc_html(human_time_diff(strtotime($notification->post_date), current_time('timestamp'))); ?> <?php _e('ago', 'flavor'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="<?php echo esc_url($portal_url); ?>" class="button">
                            <?php _e('View All in Portal', 'flavor'); ?>
                        </a>
                    </p>
                </div>
                <div class="footer">
                    <p><?php echo esc_html($site_name); ?></p>
                    <p><?php _e('To change your notification preferences, visit your portal settings.', 'flavor'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get notification type labels
     */
    public static function get_types() {
        return [
            'task_available' => __('Task Available', 'flavor'),
            'task_assigned' => __('Task Assigned', 'flavor'),
            'task_due_soon' => __('Task Due Soon', 'flavor'),
            'task_overdue' => __('Task Overdue', 'flavor'),
            'task_completed' => __('Task Completed', 'flavor'),
            'task_requires_review' => __('Task Requires Review', 'flavor'),
            'payment_received' => __('Payment Received', 'flavor'),
            'document_signed' => __('Document Signed', 'flavor'),
            'case_assigned' => __('Case Assigned', 'flavor'),
            'case_status_change' => __('Case Status Change', 'flavor'),
            'escalation' => __('Escalation', 'flavor'),
            'new_message' => __('New Message', 'flavor'),
            'failed_login_attempt' => __('Failed Login Attempt', 'flavor'),
            'account_locked' => __('Account Locked', 'flavor')
        ];
    }
}
