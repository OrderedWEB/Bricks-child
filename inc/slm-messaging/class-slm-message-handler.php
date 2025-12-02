<?php
/**
 * SLM Message Handler
 *
 * Handles message CRUD operations:
 * - Create messages with attachments
 * - Retrieve messages by case/task/document
 * - Mark messages as read
 * - Render message HTML
 *
 * @package SLM_Messaging
 */

defined('ABSPATH') || exit;

class SLM_Message_Handler {

    private static $initialized = false;

    /**
     * Initialize.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Admin columns
        add_filter('manage_slm_message_posts_columns', [__CLASS__, 'add_admin_columns']);
        add_action('manage_slm_message_posts_custom_column', [__CLASS__, 'render_admin_columns'], 10, 2);

        // Prevent editing/deleting (immutability)
        add_filter('post_row_actions', [__CLASS__, 'remove_row_actions'], 10, 2);
        add_action('admin_head', [__CLASS__, 'hide_edit_controls']);
    }

    /**
     * Create a new message.
     */
    public static function create_message($args) {
        $defaults = [
            'case_id' => 0,
            'task_id' => null,
            'document_id' => null,
            'content' => '',
            'linked_document_ids' => [],
            'uploaded_document_ids' => [],
            'system_message' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate
        if (!$args['case_id']) {
            return new WP_Error('missing_case', __('Case ID is required.', 'flavor'));
        }

        $user_id = get_current_user_id();

        if (!$args['system_message'] && !SLM_Messaging::user_can_access_case($user_id, $args['case_id'])) {
            return new WP_Error('permission_denied', __('You cannot message in this case.', 'flavor'));
        }

        // Create message post
        $post_id = wp_insert_post([
            'post_type' => 'slm_message',
            'post_title' => 'Message ' . current_time('Y-m-d H:i:s'),
            'post_status' => 'publish',
            'post_author' => $user_id ?: 1,
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Set core meta fields
        update_post_meta($post_id, '_slm_sender', $user_id);
        update_post_meta($post_id, '_slm_related_case', $args['case_id']);
        update_post_meta($post_id, '_slm_message_content', wp_kses_post($args['content']));
        update_post_meta($post_id, '_slm_message_timestamp', current_time('mysql'));
        update_post_meta($post_id, '_slm_is_system_message', $args['system_message']);

        // Optional context
        if ($args['task_id']) {
            update_post_meta($post_id, '_slm_related_task', $args['task_id']);
        }
        if ($args['document_id']) {
            update_post_meta($post_id, '_slm_related_document', $args['document_id']);
        }

        // Handle attachments
        $attachments = [];

        if (!empty($args['uploaded_document_ids'])) {
            update_post_meta($post_id, '_slm_attachment_type', 'upload');

            foreach ($args['uploaded_document_ids'] as $doc_id) {
                $attachments[] = [
                    'document_id' => $doc_id,
                    'original_filename' => get_the_title($doc_id),
                ];
            }
        } elseif (!empty($args['linked_document_ids'])) {
            update_post_meta($post_id, '_slm_attachment_type', 'link');
            update_post_meta($post_id, '_slm_linked_documents', $args['linked_document_ids']);
        } else {
            update_post_meta($post_id, '_slm_attachment_type', 'none');
        }

        if (!empty($attachments)) {
            update_post_meta($post_id, '_slm_uploaded_attachments', $attachments);
        }

        // Mark as read by sender
        $read_by = [
            [
                'user_id' => $user_id,
                'read_timestamp' => current_time('mysql'),
            ],
        ];
        update_post_meta($post_id, '_slm_read_by', $read_by);

        // Send notifications
        if (!$args['system_message']) {
            SLM_Message_Notifications::notify_recipients($post_id);
        }

        // Log to audit
        self::log_message_sent($post_id, $args);

        return $post_id;
    }

    /**
     * Get messages with filters.
     */
    public static function get_messages($args = []) {
        $defaults = [
            'case_id' => 0,
            'task_id' => null,
            'document_id' => null,
            'context' => 'all', // 'all', 'case_only', 'task', 'document'
            'since' => null,
            'limit' => -1,
        ];

        $args = wp_parse_args($args, $defaults);

        $meta_query = [];

        // Case filter (required)
        if ($args['case_id']) {
            $meta_query[] = [
                'key' => '_slm_related_case',
                'value' => $args['case_id'],
            ];
        }

        // Context-specific filters
        switch ($args['context']) {
            case 'case_only':
                // Messages with no task or document link
                $meta_query[] = [
                    'relation' => 'AND',
                    [
                        'key' => '_slm_related_task',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => '_slm_related_document',
                        'compare' => 'NOT EXISTS',
                    ],
                ];
                break;

            case 'task':
                if ($args['task_id']) {
                    $meta_query[] = [
                        'key' => '_slm_related_task',
                        'value' => $args['task_id'],
                    ];
                }
                break;

            case 'document':
                if ($args['document_id']) {
                    $meta_query[] = [
                        'key' => '_slm_related_document',
                        'value' => $args['document_id'],
                    ];
                }
                break;
        }

        // Specific task/document filter (regardless of context)
        if ($args['task_id'] && $args['context'] !== 'task') {
            $meta_query[] = [
                'key' => '_slm_related_task',
                'value' => $args['task_id'],
            ];
        }

        if ($args['document_id'] && $args['context'] !== 'document') {
            $meta_query[] = [
                'key' => '_slm_related_document',
                'value' => $args['document_id'],
            ];
        }

        $query_args = [
            'post_type' => 'slm_message',
            'posts_per_page' => $args['limit'],
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'meta_key' => '_slm_message_timestamp',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        ];

        // Since filter for polling
        if ($args['since']) {
            $query_args['date_query'] = [
                [
                    'after' => $args['since'],
                    'inclusive' => false,
                ],
            ];
        }

        return get_posts($query_args);
    }

    /**
     * Get messages for a task.
     */
    public static function get_task_messages($task_id) {
        return self::get_messages([
            'task_id' => $task_id,
            'context' => 'task',
        ]);
    }

    /**
     * Get messages for a document.
     */
    public static function get_document_messages($document_id) {
        return self::get_messages([
            'document_id' => $document_id,
            'context' => 'document',
        ]);
    }

    /**
     * Get message data as array.
     */
    public static function get_message_data($message_id) {
        $post = get_post($message_id);

        if (!$post || $post->post_type !== 'slm_message') {
            return null;
        }

        $sender_id = get_post_meta($message_id, '_slm_sender', true);
        $sender = get_user_by('id', $sender_id);

        $data = [
            'id' => $message_id,
            'sender_id' => $sender_id,
            'sender_name' => $sender ? $sender->display_name : __('Unknown', 'flavor'),
            'sender_avatar' => get_avatar_url($sender_id, ['size' => 48]),
            'sender_is_lawyer' => SLM_Messaging::user_is_lawyer($sender_id),
            'case_id' => get_post_meta($message_id, '_slm_related_case', true),
            'task_id' => get_post_meta($message_id, '_slm_related_task', true),
            'document_id' => get_post_meta($message_id, '_slm_related_document', true),
            'content' => get_post_meta($message_id, '_slm_message_content', true),
            'timestamp' => get_post_meta($message_id, '_slm_message_timestamp', true),
            'timestamp_formatted' => self::format_timestamp(get_post_meta($message_id, '_slm_message_timestamp', true)),
            'is_system_message' => (bool) get_post_meta($message_id, '_slm_is_system_message', true),
            'attachment_type' => get_post_meta($message_id, '_slm_attachment_type', true),
            'attachments' => self::get_attachments($message_id),
            'read_by' => get_post_meta($message_id, '_slm_read_by', true) ?: [],
        ];

        // Context labels
        if ($data['task_id']) {
            $data['context_type'] = 'task';
            $data['context_label'] = get_the_title($data['task_id']);
        } elseif ($data['document_id']) {
            $data['context_type'] = 'document';
            $data['context_label'] = get_the_title($data['document_id']);
        } else {
            $data['context_type'] = 'case';
            $data['context_label'] = null;
        }

        return $data;
    }

    /**
     * Get message attachments.
     */
    public static function get_attachments($message_id) {
        $type = get_post_meta($message_id, '_slm_attachment_type', true);
        $attachments = [];

        if ($type === 'upload') {
            $uploaded = get_post_meta($message_id, '_slm_uploaded_attachments', true) ?: [];

            foreach ($uploaded as $item) {
                $doc_id = $item['document_id'];
                $attachments[] = [
                    'id' => $doc_id,
                    'type' => 'uploaded',
                    'filename' => $item['original_filename'],
                    'title' => get_the_title($doc_id),
                    'url' => self::get_document_url($doc_id),
                    'icon' => self::get_file_icon($item['original_filename']),
                ];
            }
        } elseif ($type === 'link') {
            $linked = get_post_meta($message_id, '_slm_linked_documents', true) ?: [];

            foreach ($linked as $doc_id) {
                $attachments[] = [
                    'id' => $doc_id,
                    'type' => 'linked',
                    'filename' => get_the_title($doc_id),
                    'title' => get_the_title($doc_id),
                    'url' => self::get_document_url($doc_id),
                    'icon' => self::get_file_icon(get_the_title($doc_id)),
                ];
            }
        }

        return $attachments;
    }

    /**
     * Render message HTML.
     */
    public static function render_message($message_id, $current_user_id = null) {
        $data = self::get_message_data($message_id);

        if (!$data) {
            return '';
        }

        if (!$current_user_id) {
            $current_user_id = get_current_user_id();
        }

        $is_own = ($data['sender_id'] == $current_user_id);
        $is_read = self::user_has_read_message($current_user_id, $message_id);
        $is_system = $data['is_system_message'];

        $classes = ['slm-message'];
        $classes[] = $is_own ? 'slm-message-own' : 'slm-message-other';
        $classes[] = $data['sender_is_lawyer'] ? 'slm-message-lawyer' : 'slm-message-client';

        if (!$is_read && !$is_own) {
            $classes[] = 'slm-message-unread';
        }
        if ($is_system) {
            $classes[] = 'slm-message-system';
        }

        ob_start();
        ?>
        <div id="message-<?php echo esc_attr($message_id); ?>" 
             class="<?php echo esc_attr(implode(' ', $classes)); ?>" 
             data-message-id="<?php echo esc_attr($message_id); ?>">
            
            <?php if (!$is_system): ?>
            <div class="slm-message-avatar">
                <img src="<?php echo esc_url($data['sender_avatar']); ?>" 
                     alt="<?php echo esc_attr($data['sender_name']); ?>">
            </div>
            <?php endif; ?>
            
            <div class="slm-message-body">
                <div class="slm-message-header">
                    <?php if (!$is_system): ?>
                    <span class="slm-message-sender">
                        <?php echo esc_html($data['sender_name']); ?>
                        <?php if ($data['sender_is_lawyer']): ?>
                            <span class="slm-badge slm-badge-lawyer"><?php _e('Lawyer', 'flavor'); ?></span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    
                    <span class="slm-message-time" title="<?php echo esc_attr($data['timestamp']); ?>">
                        <?php echo esc_html($data['timestamp_formatted']); ?>
                    </span>
                    
                    <?php if (!$is_read && !$is_own): ?>
                    <span class="slm-message-unread-dot" title="<?php _e('Unread', 'flavor'); ?>"></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($data['context_label']): ?>
                <div class="slm-message-context">
                    <span class="slm-context-icon">
                        <?php echo $data['context_type'] === 'task' ? '📋' : '📄'; ?>
                    </span>
                    <span class="slm-context-label">
                        <?php echo esc_html($data['context_label']); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="slm-message-content">
                    <?php echo wp_kses_post($data['content']); ?>
                </div>
                
                <?php if (!empty($data['attachments'])): ?>
                <div class="slm-message-attachments">
                    <?php foreach ($data['attachments'] as $attachment): ?>
                    <a href="<?php echo esc_url($attachment['url']); ?>" 
                       class="slm-attachment" 
                       target="_blank"
                       title="<?php echo esc_attr($attachment['title']); ?>">
                        <span class="slm-attachment-icon"><?php echo $attachment['icon']; ?></span>
                        <span class="slm-attachment-name"><?php echo esc_html($attachment['filename']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Mark message as read by user.
     */
    public static function mark_message_read($message_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $read_by = get_post_meta($message_id, '_slm_read_by', true) ?: [];

        // Check if already read
        foreach ($read_by as $entry) {
            if ($entry['user_id'] == $user_id) {
                return true;
            }
        }

        // Add read entry
        $read_by[] = [
            'user_id' => $user_id,
            'read_timestamp' => current_time('mysql'),
        ];

        update_post_meta($message_id, '_slm_read_by', $read_by);

        // Log
        self::log_message_read($message_id, $user_id);

        return true;
    }

    /**
     * Mark all case messages as read for user.
     */
    public static function mark_case_messages_read($case_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $messages = self::get_messages(['case_id' => $case_id]);

        foreach ($messages as $message) {
            if (!self::user_has_read_message($user_id, $message->ID)) {
                self::mark_message_read($message->ID, $user_id);
            }
        }

        return true;
    }

    /**
     * Check if user has read message.
     */
    public static function user_has_read_message($user_id, $message_id) {
        $read_by = get_post_meta($message_id, '_slm_read_by', true) ?: [];

        foreach ($read_by as $entry) {
            if ($entry['user_id'] == $user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get unread count for user.
     */
    public static function get_unread_count($user_id, $case_id = null) {
        // Get accessible cases
        if ($case_id) {
            $case_ids = [$case_id];
        } else {
            $case_ids = self::get_user_case_ids($user_id);
        }

        if (empty($case_ids)) {
            return 0;
        }

        $messages = get_posts([
            'post_type' => 'slm_message',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_slm_related_case',
                    'value' => $case_ids,
                    'compare' => 'IN',
                ],
            ],
            'fields' => 'ids',
        ]);

        $unread = 0;

        foreach ($messages as $message_id) {
            // Don't count own messages
            $sender = get_post_meta($message_id, '_slm_sender', true);
            if ($sender == $user_id) {
                continue;
            }

            if (!self::user_has_read_message($user_id, $message_id)) {
                $unread++;
            }
        }

        return $unread;
    }

    /**
     * Get case IDs user has access to.
     */
    private static function get_user_case_ids($user_id) {
        global $wpdb;

        // Get cases where user is client
        $as_client = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_slm_client_id' AND meta_value = %d",
            $user_id
        ));

        // Get cases where user is lead lawyer
        $as_lawyer = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_slm_lead_lawyer' AND meta_value = %d",
            $user_id
        ));

        // Get cases where user is in team (serialized array)
        $as_team = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_slm_case_team' AND meta_value LIKE %s",
            '%"' . $user_id . '"%'
        ));

        // Get cases where user is additional client
        $as_additional = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_slm_additional_clients' AND meta_value LIKE %s",
            '%"' . $user_id . '"%'
        ));

        return array_unique(array_merge($as_client, $as_lawyer, $as_team, $as_additional));
    }

    /**
     * Format timestamp for display.
     */
    private static function format_timestamp($timestamp) {
        if (!$timestamp) {
            return '';
        }

        $time = strtotime($timestamp);
        $now = current_time('timestamp');
        $diff = $now - $time;

        // Today
        if (date('Y-m-d', $time) === date('Y-m-d', $now)) {
            return sprintf(__('Today at %s', 'flavor'), date_i18n('g:i a', $time));
        }

        // Yesterday
        if (date('Y-m-d', $time) === date('Y-m-d', $now - DAY_IN_SECONDS)) {
            return sprintf(__('Yesterday at %s', 'flavor'), date_i18n('g:i a', $time));
        }

        // This week
        if ($diff < WEEK_IN_SECONDS) {
            return date_i18n('l, g:i a', $time);
        }

        // This year
        if (date('Y', $time) === date('Y', $now)) {
            return date_i18n('M j, g:i a', $time);
        }

        // Older
        return date_i18n('M j, Y', $time);
    }

    /**
     * Get document URL.
     */
    private static function get_document_url($document_id) {
        // Try DMS secure viewer
        if (function_exists('slm_get_document_view_url')) {
            return slm_get_document_view_url($document_id);
        }

        // Fallback to attachment URL
        return wp_get_attachment_url($document_id) ?: '#';
    }

    /**
     * Get file icon based on extension.
     */
    private static function get_file_icon($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $icons = [
            'pdf' => '📕',
            'doc' => '📘',
            'docx' => '📘',
            'xls' => '📗',
            'xlsx' => '📗',
            'ppt' => '📙',
            'pptx' => '📙',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'zip' => '📦',
            'rar' => '📦',
            'txt' => '📄',
        ];

        return $icons[$ext] ?? '📎';
    }

    /**
     * Log message sent.
     */
    private static function log_message_sent($message_id, $args) {
        if (class_exists('SLM_Task_Audit')) {
            SLM_Task_Audit::get_instance()->log(
                'message',
                $message_id,
                'message_sent',
                [
                    'case_id' => $args['case_id'],
                    'task_id' => $args['task_id'],
                    'document_id' => $args['document_id'],
                    'has_attachments' => !empty($args['linked_document_ids']) || !empty($args['uploaded_document_ids']),
                ]
            );
        }
    }

    /**
     * Log message read.
     */
    private static function log_message_read($message_id, $user_id) {
        if (class_exists('SLM_Task_Audit')) {
            $case_id = get_post_meta($message_id, '_slm_related_case', true);

            SLM_Task_Audit::get_instance()->log(
                'message',
                $message_id,
                'message_read',
                [
                    'case_id' => $case_id,
                    'user_id' => $user_id,
                ]
            );
        }
    }

    // =========================================================================
    // Admin Columns
    // =========================================================================

    /**
     * Add admin columns.
     */
    public static function add_admin_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns['sender'] = __('Sender', 'flavor');
                $new_columns['case'] = __('Case', 'flavor');
                $new_columns['context'] = __('Context', 'flavor');
                $new_columns['preview'] = __('Message', 'flavor');
                $new_columns['sent'] = __('Sent', 'flavor');
            } else {
                $new_columns[$key] = $value;
            }
        }

        unset($new_columns['title']);
        unset($new_columns['date']);

        return $new_columns;
    }

    /**
     * Render admin columns.
     */
    public static function render_admin_columns($column, $post_id) {
        switch ($column) {
            case 'sender':
                $sender_id = get_post_meta($post_id, '_slm_sender', true);
                $sender = get_user_by('id', $sender_id);
                if ($sender) {
                    echo esc_html($sender->display_name);
                    if (SLM_Messaging::user_is_lawyer($sender_id)) {
                        echo ' <span class="slm-badge">' . __('Lawyer', 'flavor') . '</span>';
                    }
                }
                break;

            case 'case':
                $case_id = get_post_meta($post_id, '_slm_related_case', true);
                if ($case_id) {
                    printf(
                        '<a href="%s">%s</a>',
                        esc_url(get_edit_post_link($case_id)),
                        esc_html(get_the_title($case_id))
                    );
                }
                break;

            case 'context':
                $task_id = get_post_meta($post_id, '_slm_related_task', true);
                $doc_id = get_post_meta($post_id, '_slm_related_document', true);

                if ($task_id) {
                    echo '📋 ' . esc_html(get_the_title($task_id));
                } elseif ($doc_id) {
                    echo '📄 ' . esc_html(get_the_title($doc_id));
                } else {
                    echo '<span class="na">—</span>';
                }
                break;

            case 'preview':
                $content = get_post_meta($post_id, '_slm_message_content', true);
                echo esc_html(wp_trim_words(strip_tags($content), 15));

                $type = get_post_meta($post_id, '_slm_attachment_type', true);
                if ($type && $type !== 'none') {
                    echo ' <span class="slm-has-attachment">📎</span>';
                }
                break;

            case 'sent':
                $timestamp = get_post_meta($post_id, '_slm_message_timestamp', true);
                if ($timestamp) {
                    echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($timestamp)));
                }
                break;
        }
    }

    /**
     * Remove edit/delete row actions (immutability).
     */
    public static function remove_row_actions($actions, $post) {
        if ($post->post_type === 'slm_message') {
            unset($actions['edit']);
            unset($actions['inline hide-if-no-js']);
            unset($actions['trash']);

            $actions['view'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(get_edit_post_link($post->ID)),
                __('View', 'flavor')
            );
        }

        return $actions;
    }

    /**
     * Hide edit controls in admin.
     */
    public static function hide_edit_controls() {
        $screen = get_current_screen();

        if ($screen && $screen->post_type === 'slm_message') {
            ?>
            <style>
                .post-type-slm_message .page-title-action,
                .post-type-slm_message #delete-action,
                .post-type-slm_message #publishing-action,
                .post-type-slm_message .edit-post-status,
                .post-type-slm_message .edit-visibility,
                .post-type-slm_message .edit-timestamp {
                    display: none !important;
                }
                .post-type-slm_message #minor-publishing-actions {
                    display: none;
                }
                .post-type-slm_message .acf-field input,
                .post-type-slm_message .acf-field textarea,
                .post-type-slm_message .acf-field select {
                    pointer-events: none;
                    background: #f0f0f0;
                }
            </style>
            <?php
        }
    }
}
