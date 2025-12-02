<?php
/**
 * SLM Message Notifications
 *
 * Handles message notifications:
 * - In-app notifications via SLM_Notifications
 * - Email notifications (full or link-only)
 * - Digest emails
 * - URL generation for replies
 *
 * @package SLM_Messaging
 */

defined('ABSPATH') || exit;

class SLM_Message_Notifications {

    private static $initialized = false;

    /**
     * Initialize.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Digest cron
        add_action('slm_send_message_digest', [__CLASS__, 'send_digest_emails']);

        // Schedule digest if not scheduled
        if (!wp_next_scheduled('slm_send_message_digest')) {
            // Schedule for 8 AM daily
            $timestamp = strtotime('tomorrow 08:00:00');
            wp_schedule_event($timestamp, 'daily', 'slm_send_message_digest');
        }
    }

    /**
     * Notify all recipients of a new message.
     */
    public static function notify_recipients($message_id) {
        $sender_id = get_post_meta($message_id, '_slm_sender', true);
        $case_id = get_post_meta($message_id, '_slm_related_case', true);
        $task_id = get_post_meta($message_id, '_slm_related_task', true);

        // Get all case participants
        $participants = SLM_Messaging::get_case_participants($case_id);

        $sender = get_user_by('id', $sender_id);
        $sender_name = $sender ? $sender->display_name : __('Someone', 'flavor');

        foreach ($participants as $user_id) {
            // Don't notify sender
            if ($user_id == $sender_id) {
                continue;
            }

            // Create in-app notification
            self::create_in_app_notification($message_id, $user_id, $sender_name, $case_id, $task_id);

            // Send email if enabled for user
            if (SLM_Messaging::user_has_email_enabled($user_id)) {
                self::send_email_notification($message_id, $user_id);
            }
        }
    }

    /**
     * Create in-app notification.
     */
    private static function create_in_app_notification($message_id, $recipient_id, $sender_name, $case_id, $task_id = null) {
        // Use SLM_Notifications if available
        if (class_exists('SLM_Notifications')) {
            $content = get_post_meta($message_id, '_slm_message_content', true);

            SLM_Notifications::create([
                'recipient' => $recipient_id,
                'type' => 'new_message',
                'case_id' => $case_id,
                'task_id' => $task_id,
                'title' => sprintf(__('New message from %s', 'flavor'), $sender_name),
                'body' => wp_trim_words(strip_tags($content), 20),
                'priority' => 'normal',
                'data' => [
                    'message_id' => $message_id,
                ],
            ]);
        }
    }

    /**
     * Send email notification for a message.
     */
    public static function send_email_notification($message_id, $recipient_id) {
        $user = get_user_by('id', $recipient_id);

        if (!$user || !$user->user_email) {
            return false;
        }

        // Get preference
        $content_pref = SLM_Messaging::get_email_content_preference($recipient_id);

        // Build email
        if ($content_pref === 'full') {
            $email = self::build_full_message_email($message_id, $recipient_id);
        } else {
            $email = self::build_link_only_email($message_id, $recipient_id);
        }

        // Send
        $sent = wp_mail(
            $user->user_email,
            $email['subject'],
            $email['body'],
            $email['headers']
        );

        // Mark as sent
        if ($sent) {
            $sent_to = get_post_meta($message_id, '_slm_email_sent_to', true) ?: [];
            $sent_to[] = [
                'user_id' => $recipient_id,
                'sent_at' => current_time('mysql'),
                'type' => $content_pref,
            ];
            update_post_meta($message_id, '_slm_email_sent_to', $sent_to);
        }

        return $sent;
    }

    /**
     * Build full message email (includes content).
     */
    private static function build_full_message_email($message_id, $recipient_id) {
        $sender_id = get_post_meta($message_id, '_slm_sender', true);
        $case_id = get_post_meta($message_id, '_slm_related_case', true);
        $task_id = get_post_meta($message_id, '_slm_related_task', true);
        $content = get_post_meta($message_id, '_slm_message_content', true);

        $sender = get_user_by('id', $sender_id);
        $sender_name = $sender ? $sender->display_name : __('Someone', 'flavor');
        $case_title = get_the_title($case_id);

        $firm_name = get_option('slm_firm_name', get_bloginfo('name'));
        $reply_url = self::get_message_reply_url($message_id);
        $view_url = self::get_message_view_url($message_id);

        // Subject
        $subject = sprintf(__('[%s] New message from %s', 'flavor'), $case_title, $sender_name);

        // Context line
        $context = '';
        if ($task_id) {
            $context = sprintf(__('Regarding task: %s', 'flavor'), get_the_title($task_id));
        }

        // Build HTML body
        $body = self::get_email_template([
            'firm_name' => $firm_name,
            'recipient_name' => get_user_by('id', $recipient_id)->display_name,
            'sender_name' => $sender_name,
            'case_title' => $case_title,
            'context' => $context,
            'message_content' => wpautop($content),
            'has_attachments' => (get_post_meta($message_id, '_slm_attachment_type', true) !== 'none'),
            'view_url' => $view_url,
            'reply_url' => $reply_url,
            'show_full_content' => true,
        ]);

        return [
            'subject' => $subject,
            'body' => $body,
            'headers' => [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
            ],
        ];
    }

    /**
     * Build link-only email (secure, no content).
     */
    private static function build_link_only_email($message_id, $recipient_id) {
        $sender_id = get_post_meta($message_id, '_slm_sender', true);
        $case_id = get_post_meta($message_id, '_slm_related_case', true);

        $sender = get_user_by('id', $sender_id);
        $sender_name = $sender ? $sender->display_name : __('Someone', 'flavor');
        $case_title = get_the_title($case_id);

        $firm_name = get_option('slm_firm_name', get_bloginfo('name'));
        $view_url = self::get_message_view_url($message_id);
        $reply_url = self::get_message_reply_url($message_id);

        // Subject
        $subject = sprintf(__('[%s] New message from %s', 'flavor'), $case_title, $sender_name);

        // Build HTML body
        $body = self::get_email_template([
            'firm_name' => $firm_name,
            'recipient_name' => get_user_by('id', $recipient_id)->display_name,
            'sender_name' => $sender_name,
            'case_title' => $case_title,
            'view_url' => $view_url,
            'reply_url' => $reply_url,
            'show_full_content' => false,
        ]);

        return [
            'subject' => $subject,
            'body' => $body,
            'headers' => [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
            ],
        ];
    }

    /**
     * Get email template HTML.
     */
    private static function get_email_template($args) {
        $defaults = [
            'firm_name' => '',
            'recipient_name' => '',
            'sender_name' => '',
            'case_title' => '',
            'context' => '',
            'message_content' => '',
            'has_attachments' => false,
            'view_url' => '',
            'reply_url' => '',
            'show_full_content' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:20px 0;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background:#1a365d;padding:24px;text-align:center;">
                                    <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:600;">
                                        <?php echo esc_html($args['firm_name']); ?>
                                    </h1>
                                </td>
                            </tr>
                            
                            <!-- Body -->
                            <tr>
                                <td style="padding:32px;">
                                    <p style="margin:0 0 16px;color:#333;font-size:16px;">
                                        <?php printf(__('Hello %s,', 'flavor'), esc_html($args['recipient_name'])); ?>
                                    </p>
                                    
                                    <p style="margin:0 0 24px;color:#333;font-size:16px;">
                                        <?php printf(
                                            __('You have a new message from <strong>%s</strong> regarding your case: <strong>%s</strong>.', 'flavor'),
                                            esc_html($args['sender_name']),
                                            esc_html($args['case_title'])
                                        ); ?>
                                    </p>
                                    
                                    <?php if ($args['context']): ?>
                                    <p style="margin:0 0 16px;color:#666;font-size:14px;font-style:italic;">
                                        <?php echo esc_html($args['context']); ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($args['show_full_content']): ?>
                                    <!-- Message Content -->
                                    <div style="background:#f8f9fa;border-left:4px solid #4a90e2;padding:16px;margin:0 0 24px;border-radius:0 4px 4px 0;">
                                        <div style="color:#333;font-size:15px;line-height:1.6;">
                                            <?php echo $args['message_content']; ?>
                                        </div>
                                        
                                        <?php if ($args['has_attachments']): ?>
                                        <p style="margin:16px 0 0;color:#666;font-size:14px;">
                                            📎 <?php _e('This message has attachments. View in portal to download.', 'flavor'); ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <!-- Link Only Notice -->
                                    <div style="background:#fff3cd;border:1px solid #ffc107;padding:16px;margin:0 0 24px;border-radius:4px;">
                                        <p style="margin:0;color:#856404;font-size:14px;">
                                            🔒 <?php _e('For your security, the message content is not included in this email. Please log in to the portal to read the full message.', 'flavor'); ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Action Buttons -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="padding-right:8px;">
                                                <a href="<?php echo esc_url($args['view_url']); ?>" 
                                                   style="display:inline-block;background:#4a90e2;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:4px;font-size:14px;font-weight:600;">
                                                    <?php _e('View Message', 'flavor'); ?>
                                                </a>
                                            </td>
                                            <td align="center" style="padding-left:8px;">
                                                <a href="<?php echo esc_url($args['reply_url']); ?>" 
                                                   style="display:inline-block;background:#28a745;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:4px;font-size:14px;font-weight:600;">
                                                    <?php _e('Reply', 'flavor'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background:#f8f9fa;padding:24px;text-align:center;border-top:1px solid #eee;">
                                    <p style="margin:0 0 8px;color:#666;font-size:13px;">
                                        <?php echo esc_html($args['firm_name']); ?>
                                    </p>
                                    <p style="margin:0;color:#999;font-size:12px;">
                                        <?php _e('This is an automated notification. Please do not reply directly to this email.', 'flavor'); ?>
                                    </p>
                                    <p style="margin:8px 0 0;color:#999;font-size:12px;">
                                        <a href="<?php echo esc_url(home_url('/portal/settings/')); ?>" style="color:#4a90e2;text-decoration:none;">
                                            <?php _e('Manage notification settings', 'flavor'); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Send digest emails.
     */
    public static function send_digest_emails() {
        // Get users with digest preference
        $users = get_users([
            'meta_query' => [
                [
                    'key' => 'slm_email_delivery',
                    'value' => 'digest',
                ],
            ],
        ]);

        foreach ($users as $user) {
            $unread_count = SLM_Message_Handler::get_unread_count($user->ID);

            if ($unread_count > 0) {
                self::send_digest_email($user->ID, $unread_count);
            }
        }
    }

    /**
     * Send digest email to user.
     */
    private static function send_digest_email($user_id, $unread_count) {
        $user = get_user_by('id', $user_id);

        if (!$user || !$user->user_email) {
            return false;
        }

        $firm_name = get_option('slm_firm_name', get_bloginfo('name'));
        $portal_url = home_url('/portal/messages/');

        $subject = sprintf(
            __('[%s] You have %d unread message(s)', 'flavor'),
            $firm_name,
            $unread_count
        );

        // Get unread messages grouped by case
        $case_ids = SLM_Message_Handler::get_user_case_ids($user_id);
        $cases_data = [];

        foreach ($case_ids as $case_id) {
            $messages = SLM_Message_Handler::get_messages(['case_id' => $case_id]);
            $unread = 0;

            foreach ($messages as $msg) {
                if (!SLM_Message_Handler::user_has_read_message($user_id, $msg->ID)) {
                    $sender_id = get_post_meta($msg->ID, '_slm_sender', true);
                    if ($sender_id != $user_id) {
                        $unread++;
                    }
                }
            }

            if ($unread > 0) {
                $cases_data[] = [
                    'title' => get_the_title($case_id),
                    'unread' => $unread,
                    'url' => home_url('/portal/case/' . $case_id . '/messages/'),
                ];
            }
        }

        // Build email
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:20px 0;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">
                            <tr>
                                <td style="background:#1a365d;padding:24px;text-align:center;">
                                    <h1 style="margin:0;color:#fff;font-size:24px;"><?php echo esc_html($firm_name); ?></h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:32px;">
                                    <p style="margin:0 0 16px;font-size:16px;">
                                        <?php printf(__('Hello %s,', 'flavor'), esc_html($user->display_name)); ?>
                                    </p>
                                    <p style="margin:0 0 24px;font-size:16px;">
                                        <?php printf(
                                            _n(
                                                'You have %d unread message waiting for you.',
                                                'You have %d unread messages waiting for you.',
                                                $unread_count,
                                                'flavor'
                                            ),
                                            $unread_count
                                        ); ?>
                                    </p>
                                    
                                    <?php if (!empty($cases_data)): ?>
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                        <?php foreach ($cases_data as $case): ?>
                                        <tr>
                                            <td style="padding:12px;background:#f8f9fa;border-bottom:1px solid #eee;">
                                                <strong><?php echo esc_html($case['title']); ?></strong><br>
                                                <span style="color:#666;font-size:14px;">
                                                    <?php printf(
                                                        _n('%d new message', '%d new messages', $case['unread'], 'flavor'),
                                                        $case['unread']
                                                    ); ?>
                                                </span>
                                            </td>
                                            <td style="padding:12px;background:#f8f9fa;border-bottom:1px solid #eee;text-align:right;">
                                                <a href="<?php echo esc_url($case['url']); ?>" 
                                                   style="color:#4a90e2;text-decoration:none;font-size:14px;">
                                                    <?php _e('View →', 'flavor'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php endif; ?>
                                    
                                    <p style="text-align:center;">
                                        <a href="<?php echo esc_url($portal_url); ?>" 
                                           style="display:inline-block;background:#4a90e2;color:#fff;text-decoration:none;padding:12px 32px;border-radius:4px;font-weight:600;">
                                            <?php _e('View All Messages', 'flavor'); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#f8f9fa;padding:24px;text-align:center;border-top:1px solid #eee;">
                                    <p style="margin:0;color:#999;font-size:12px;">
                                        <?php _e('This is your daily message digest.', 'flavor'); ?>
                                        <a href="<?php echo esc_url(home_url('/portal/settings/')); ?>" style="color:#4a90e2;">
                                            <?php _e('Manage preferences', 'flavor'); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        $body = ob_get_clean();

        return wp_mail(
            $user->user_email,
            $subject,
            $body,
            [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
            ]
        );
    }

    /**
     * Get URL to view a message.
     */
    public static function get_message_view_url($message_id) {
        $case_id = get_post_meta($message_id, '_slm_related_case', true);

        return home_url('/portal/case/' . $case_id . '/messages/#message-' . $message_id);
    }

    /**
     * Get URL to reply to a message.
     */
    public static function get_message_reply_url($message_id) {
        $case_id = get_post_meta($message_id, '_slm_related_case', true);

        return home_url('/portal/case/' . $case_id . '/messages/?reply=true#message-' . $message_id);
    }

    /**
     * Get unread count badge HTML.
     */
    public static function get_unread_badge($user_id = null, $case_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $count = SLM_Message_Handler::get_unread_count($user_id, $case_id);

        if ($count === 0) {
            return '';
        }

        $display = $count > 99 ? '99+' : $count;

        return sprintf(
            '<span class="slm-unread-badge">%s</span>',
            esc_html($display)
        );
    }
}
