<?php
/**
 * SLM Messaging System - Main Orchestrator
 *
 * Unified secure messaging for lawyer-client communication:
 * - Case-level, task-level, and document-level messages
 * - Immutable messages with full audit trail
 * - Read tracking and email notifications
 * - Attachment support (upload or link existing)
 *
 * @package SLM_Messaging
 */

defined('ABSPATH') || exit;

class SLM_Messaging {

    private static $initialized = false;

    /**
     * Initialize the messaging system.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Register CPT
        add_action('init', [__CLASS__, 'register_post_types']);

        // Include sub-classes
        self::load_classes();

        // Initialize sub-classes
        add_action('init', [__CLASS__, 'init_classes'], 20);

        // AJAX handlers
        self::register_ajax_handlers();

        // Enqueue assets
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);

        // REST API
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);

        // User profile settings
        add_action('show_user_profile', [__CLASS__, 'render_user_settings']);
        add_action('edit_user_profile', [__CLASS__, 'render_user_settings']);
        add_action('personal_options_update', [__CLASS__, 'save_user_settings']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_user_settings']);

        // Mark messages read on view
        add_action('template_redirect', [__CLASS__, 'maybe_mark_messages_read']);

        // Integration hooks
        add_action('slm_task_completed', [__CLASS__, 'on_task_completed'], 10, 2);
        add_action('slm_document_uploaded', [__CLASS__, 'on_document_uploaded'], 10, 2);
    }

    /**
     * Load sub-classes.
     */
    private static function load_classes() {
        $dir = dirname(__FILE__);

        require_once $dir . '/class-slm-message-handler.php';
        require_once $dir . '/class-slm-message-notifications.php';
        require_once $dir . '/class-slm-message-acf.php';
    }

    /**
     * Initialize sub-classes.
     */
    public static function init_classes() {
        SLM_Message_Handler::init();
        SLM_Message_Notifications::init();
        SLM_Message_ACF::init();
    }

    /**
     * Register message CPT.
     */
    public static function register_post_types() {
        register_post_type('slm_message', [
            'label' => __('Messages', 'flavor'),
            'labels' => [
                'name' => __('Messages', 'flavor'),
                'singular_name' => __('Message', 'flavor'),
                'add_new' => __('New Message', 'flavor'),
                'add_new_item' => __('Add New Message', 'flavor'),
                'edit_item' => __('View Message', 'flavor'),
                'view_item' => __('View Message', 'flavor'),
                'search_items' => __('Search Messages', 'flavor'),
                'not_found' => __('No messages found', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=slm_case',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow', // Disable admin creation
            ],
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-format-chat',
        ]);
    }

    /**
     * Register AJAX handlers.
     */
    private static function register_ajax_handlers() {
        // Authenticated users
        $handlers = [
            'slm_send_message',
            'slm_get_messages',
            'slm_get_message',
            'slm_mark_message_read',
            'slm_mark_all_messages_read',
            'slm_get_unread_count',
            'slm_upload_message_attachment',
            'slm_get_linkable_documents',
            'slm_save_message_preferences',
        ];

        foreach ($handlers as $handler) {
            add_action('wp_ajax_' . $handler, [__CLASS__, 'handle_ajax_' . str_replace('slm_', '', $handler)]);
        }
    }

    /**
     * Register REST routes.
     */
    public static function register_rest_routes() {
        register_rest_route('slm/v1', '/messages/case/(?P<case_id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'rest_get_case_messages'],
            'permission_callback' => function($request) {
                return self::user_can_access_case(get_current_user_id(), $request['case_id']);
            },
        ]);

        register_rest_route('slm/v1', '/messages/unread', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'rest_get_unread_count'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Enqueue frontend assets.
     */
    public static function enqueue_frontend_assets() {
        if (!is_user_logged_in()) {
            return;
        }

        // Only on relevant pages
        if (!self::is_messaging_page()) {
            return;
        }

        wp_enqueue_style(
            'slm-messaging',
            plugin_dir_url(__FILE__) . 'assets/messaging.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/messaging.css')
        );

        wp_enqueue_script(
            'slm-messaging',
            plugin_dir_url(__FILE__) . 'assets/messaging.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/messaging.js'),
            true
        );

        $case_id = self::get_current_case_id();

        wp_localize_script('slm-messaging', 'slmMessagingConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('slm/v1/'),
            'nonce' => wp_create_nonce('slm_messaging'),
            'caseId' => $case_id,
            'userId' => get_current_user_id(),
            'isLawyer' => self::user_is_lawyer(get_current_user_id()),
            'pollInterval' => 30000, // 30 seconds
            'strings' => [
                'sending' => __('Sending...', 'flavor'),
                'sent' => __('Message sent', 'flavor'),
                'error' => __('Error sending message', 'flavor'),
                'uploading' => __('Uploading...', 'flavor'),
                'confirmDelete' => __('Remove this attachment?', 'flavor'),
            ],
        ]);
    }

    /**
     * Enqueue admin assets.
     */
    public static function enqueue_admin_assets($hook) {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->post_type, ['slm_message', 'slm_case'])) {
            return;
        }

        wp_enqueue_style(
            'slm-messaging-admin',
            plugin_dir_url(__FILE__) . 'assets/messaging-admin.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/messaging-admin.css')
        );
    }

    /**
     * Check if current page is a messaging page.
     */
    private static function is_messaging_page() {
        // Check for portal pages
        if (is_page_template('templates/portal-messages.php')) {
            return true;
        }

        // Check for case detail pages with messages
        if (is_singular('slm_case')) {
            return true;
        }

        // Check query vars
        if (get_query_var('slm_portal') === 'messages') {
            return true;
        }

        return false;
    }

    /**
     * Get current case ID from context.
     */
    private static function get_current_case_id() {
        // From query var
        $case_id = get_query_var('case_id');
        if ($case_id) {
            return intval($case_id);
        }

        // From singular
        if (is_singular('slm_case')) {
            return get_the_ID();
        }

        // From request
        if (isset($_REQUEST['case_id'])) {
            return intval($_REQUEST['case_id']);
        }

        return 0;
    }

    /**
     * Render user messaging settings.
     */
    public static function render_user_settings($user) {
        // Only show to clients
        if (self::user_is_lawyer($user->ID)) {
            return;
        }

        $email_content = get_user_meta($user->ID, 'slm_email_message_content', true) ?: 'link_only';
        $email_enabled = get_user_meta($user->ID, 'slm_email_notifications', true) !== 'disabled';
        ?>
        <h3><?php _e('Message Notification Settings', 'flavor'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="slm_email_notifications"><?php _e('Email Notifications', 'flavor'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="slm_email_notifications" id="slm_email_notifications" value="enabled" <?php checked($email_enabled); ?>>
                        <?php _e('Receive email notifications for new messages', 'flavor'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Message Content in Emails', 'flavor'); ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="slm_email_message_content" value="full" <?php checked($email_content, 'full'); ?>>
                            <?php _e('Full message (convenient, less secure)', 'flavor'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="slm_email_message_content" value="link_only" <?php checked($email_content, 'link_only'); ?>>
                            <?php _e('Link only (secure, must log in to read)', 'flavor'); ?>
                        </label>
                        <p class="description">
                            <?php _e('For your security, we recommend "Link only" if you access email on shared devices.', 'flavor'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save user messaging settings.
     */
    public static function save_user_settings($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        // Email notifications
        $enabled = isset($_POST['slm_email_notifications']) ? 'enabled' : 'disabled';
        update_user_meta($user_id, 'slm_email_notifications', $enabled);

        // Content preference
        if (isset($_POST['slm_email_message_content'])) {
            $content = sanitize_text_field($_POST['slm_email_message_content']);
            if (in_array($content, ['full', 'link_only'])) {
                update_user_meta($user_id, 'slm_email_message_content', $content);
            }
        }
    }

    /**
     * Mark messages as read when viewing case.
     */
    public static function maybe_mark_messages_read() {
        if (!is_user_logged_in()) {
            return;
        }

        $case_id = self::get_current_case_id();
        if (!$case_id) {
            return;
        }

        if (!self::user_can_access_case(get_current_user_id(), $case_id)) {
            return;
        }

        // Mark visible messages as read
        SLM_Message_Handler::mark_case_messages_read($case_id, get_current_user_id());
    }

    // =========================================================================
    // AJAX Handlers
    // =========================================================================

    /**
     * Send a new message.
     */
    public static function handle_ajax_send_message() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $task_id = intval($_POST['task_id'] ?? 0) ?: null;
        $document_id = intval($_POST['document_id'] ?? 0) ?: null;
        $linked_docs = isset($_POST['linked_documents']) ? array_map('intval', $_POST['linked_documents']) : [];
        $uploaded_ids = isset($_POST['uploaded_attachments']) ? array_map('intval', $_POST['uploaded_attachments']) : [];

        if (!$case_id || empty(trim(strip_tags($content)))) {
            wp_send_json_error(['message' => __('Case ID and message content are required.', 'flavor')]);
        }

        if (!self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('You do not have access to this case.', 'flavor')]);
        }

        $result = SLM_Message_Handler::create_message([
            'case_id' => $case_id,
            'task_id' => $task_id,
            'document_id' => $document_id,
            'content' => $content,
            'linked_document_ids' => $linked_docs,
            'uploaded_document_ids' => $uploaded_ids,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message_id' => $result,
            'message' => __('Message sent successfully.', 'flavor'),
            'html' => SLM_Message_Handler::render_message($result, get_current_user_id()),
        ]);
    }

    /**
     * Get messages for a case/task/document.
     */
    public static function handle_ajax_get_messages() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0);
        $task_id = intval($_POST['task_id'] ?? 0) ?: null;
        $document_id = intval($_POST['document_id'] ?? 0) ?: null;
        $context = sanitize_text_field($_POST['context'] ?? 'all');
        $since = sanitize_text_field($_POST['since'] ?? '');

        if (!$case_id) {
            wp_send_json_error(['message' => __('Case ID is required.', 'flavor')]);
        }

        if (!self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        $args = [
            'case_id' => $case_id,
            'context' => $context,
        ];

        if ($task_id) {
            $args['task_id'] = $task_id;
        }
        if ($document_id) {
            $args['document_id'] = $document_id;
        }
        if ($since) {
            $args['since'] = $since;
        }

        $messages = SLM_Message_Handler::get_messages($args);
        $user_id = get_current_user_id();

        $html = '';
        foreach ($messages as $message) {
            $html .= SLM_Message_Handler::render_message($message->ID, $user_id);
        }

        wp_send_json_success([
            'html' => $html,
            'count' => count($messages),
            'timestamp' => current_time('mysql'),
        ]);
    }

    /**
     * Get single message.
     */
    public static function handle_ajax_get_message() {
        check_ajax_referer('slm_messaging', 'nonce');

        $message_id = intval($_POST['message_id'] ?? 0);

        if (!$message_id) {
            wp_send_json_error(['message' => __('Message ID required.', 'flavor')]);
        }

        $case_id = get_post_meta($message_id, '_slm_related_case', true);

        if (!self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        $html = SLM_Message_Handler::render_message($message_id, get_current_user_id());

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Mark message as read.
     */
    public static function handle_ajax_mark_message_read() {
        check_ajax_referer('slm_messaging', 'nonce');

        $message_id = intval($_POST['message_id'] ?? 0);

        if (!$message_id) {
            wp_send_json_error(['message' => __('Message ID required.', 'flavor')]);
        }

        $case_id = get_post_meta($message_id, '_slm_related_case', true);

        if (!self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        SLM_Message_Handler::mark_message_read($message_id, get_current_user_id());

        wp_send_json_success(['marked' => true]);
    }

    /**
     * Mark all messages in case as read.
     */
    public static function handle_ajax_mark_all_messages_read() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0);

        if (!$case_id || !self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        SLM_Message_Handler::mark_case_messages_read($case_id, get_current_user_id());

        wp_send_json_success(['marked' => true]);
    }

    /**
     * Get unread message count.
     */
    public static function handle_ajax_get_unread_count() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0) ?: null;
        $user_id = get_current_user_id();

        $count = SLM_Message_Handler::get_unread_count($user_id, $case_id);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Upload message attachment.
     */
    public static function handle_ajax_upload_message_attachment() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0);

        if (!$case_id || !self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 'flavor')]);
        }

        $file = $_FILES['file'];

        // Validate file
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_types)) {
            wp_send_json_error(['message' => __('File type not allowed.', 'flavor')]);
        }

        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(['message' => __('File too large. Maximum 10MB.', 'flavor')]);
        }

        // Create document via DMS
        if (function_exists('slm_create_document')) {
            $document_id = slm_create_document([
                'case_id' => $case_id,
                'file' => $file,
                'name' => sanitize_file_name($file['name']),
                'category' => 'correspondence',
                'uploaded_by' => get_current_user_id(),
            ]);

            if (is_wp_error($document_id)) {
                wp_send_json_error(['message' => $document_id->get_error_message()]);
            }

            wp_send_json_success([
                'document_id' => $document_id,
                'filename' => $file['name'],
                'size' => size_format($file['size']),
            ]);
        } else {
            // Fallback to WordPress media library
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload('file', 0);

            if (is_wp_error($attachment_id)) {
                wp_send_json_error(['message' => $attachment_id->get_error_message()]);
            }

            // Store case association
            update_post_meta($attachment_id, '_slm_case_id', $case_id);
            update_post_meta($attachment_id, '_slm_is_message_attachment', true);

            wp_send_json_success([
                'document_id' => $attachment_id,
                'filename' => $file['name'],
                'size' => size_format($file['size']),
            ]);
        }
    }

    /**
     * Get documents that can be linked.
     */
    public static function handle_ajax_get_linkable_documents() {
        check_ajax_referer('slm_messaging', 'nonce');

        $case_id = intval($_POST['case_id'] ?? 0);
        $search = sanitize_text_field($_POST['search'] ?? '');

        if (!$case_id || !self::user_can_access_case(get_current_user_id(), $case_id)) {
            wp_send_json_error(['message' => __('Access denied.', 'flavor')]);
        }

        $args = [
            'post_type' => 'slm_document',
            'posts_per_page' => 50,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $case_id,
                ],
            ],
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        $documents = get_posts($args);
        $results = [];

        foreach ($documents as $doc) {
            $folder_id = get_post_meta($doc->ID, '_slm_folder_id', true);
            $folder_name = $folder_id ? get_the_title($folder_id) : __('Uncategorized', 'flavor');

            $results[] = [
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'folder' => $folder_name,
                'date' => get_the_date('', $doc),
            ];
        }

        wp_send_json_success(['documents' => $results]);
    }

    /**
     * Save message preferences (for portal).
     */
    public static function handle_ajax_save_message_preferences() {
        check_ajax_referer('slm_messaging', 'nonce');

        $user_id = get_current_user_id();

        if (isset($_POST['email_content'])) {
            $content = sanitize_text_field($_POST['email_content']);
            if (in_array($content, ['full', 'link_only'])) {
                update_user_meta($user_id, 'slm_email_message_content', $content);
            }
        }

        if (isset($_POST['email_notifications'])) {
            $enabled = $_POST['email_notifications'] === 'enabled' ? 'enabled' : 'disabled';
            update_user_meta($user_id, 'slm_email_notifications', $enabled);
        }

        wp_send_json_success(['saved' => true]);
    }

    // =========================================================================
    // REST Handlers
    // =========================================================================

    /**
     * REST: Get case messages.
     */
    public static function rest_get_case_messages($request) {
        $case_id = intval($request['case_id']);
        $context = sanitize_text_field($request->get_param('context') ?: 'all');

        $messages = SLM_Message_Handler::get_messages([
            'case_id' => $case_id,
            'context' => $context,
        ]);

        $data = [];
        foreach ($messages as $message) {
            $data[] = SLM_Message_Handler::get_message_data($message->ID);
        }

        return rest_ensure_response($data);
    }

    /**
     * REST: Get unread count.
     */
    public static function rest_get_unread_count($request) {
        if (!is_user_logged_in()) {
            return rest_ensure_response(['count' => 0]);
        }

        $case_id = intval($request->get_param('case_id')) ?: null;
        $count = SLM_Message_Handler::get_unread_count(get_current_user_id(), $case_id);

        return rest_ensure_response(['count' => $count]);
    }

    // =========================================================================
    // Integration Hooks
    // =========================================================================

    /**
     * Handle task completed - maybe notify.
     */
    public static function on_task_completed($task_id, $user_id) {
        $case_id = get_post_meta($task_id, '_slm_case_id', true);

        if (!$case_id) {
            return;
        }

        // Auto-create message for task completion if configured
        $auto_message = get_option('slm_messaging_auto_task_complete');

        if ($auto_message) {
            $task_title = get_the_title($task_id);
            $user = get_user_by('id', $user_id);

            SLM_Message_Handler::create_message([
                'case_id' => $case_id,
                'task_id' => $task_id,
                'content' => sprintf(
                    __('%s completed the task: %s', 'flavor'),
                    $user->display_name,
                    $task_title
                ),
                'system_message' => true,
            ]);
        }
    }

    /**
     * Handle document uploaded.
     */
    public static function on_document_uploaded($document_id, $case_id) {
        $auto_message = get_option('slm_messaging_auto_doc_upload');

        if ($auto_message) {
            $doc_title = get_the_title($document_id);
            $user = wp_get_current_user();

            SLM_Message_Handler::create_message([
                'case_id' => $case_id,
                'document_id' => $document_id,
                'content' => sprintf(
                    __('%s uploaded a document: %s', 'flavor'),
                    $user->display_name,
                    $doc_title
                ),
                'system_message' => true,
            ]);
        }
    }

    // =========================================================================
    // Permission Helpers
    // =========================================================================

    /**
     * Check if user can access a case.
     */
    public static function user_can_access_case($user_id, $case_id) {
        if (!$user_id || !$case_id) {
            return false;
        }

        // Admins always have access
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        // Check if client
        $client_id = get_post_meta($case_id, '_slm_client_id', true);
        if ($client_id == $user_id) {
            return true;
        }

        // Check additional clients
        $additional = get_post_meta($case_id, '_slm_additional_clients', true) ?: [];
        if (in_array($user_id, $additional)) {
            return true;
        }

        // Check case team
        $team = get_post_meta($case_id, '_slm_case_team', true) ?: [];
        if (in_array($user_id, $team)) {
            return true;
        }

        // Check lead lawyer
        $lawyer = get_post_meta($case_id, '_slm_lead_lawyer', true);
        if ($lawyer == $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is a lawyer/staff.
     */
    public static function user_is_lawyer($user_id) {
        $user = get_user_by('id', $user_id);

        if (!$user) {
            return false;
        }

        $lawyer_roles = ['administrator', 'editor', 'slm_lawyer', 'slm_paralegal', 'slm_staff'];

        return !empty(array_intersect($lawyer_roles, $user->roles));
    }

    /**
     * Get case participants.
     */
    public static function get_case_participants($case_id) {
        $participants = [];

        // Client
        $client_id = get_post_meta($case_id, '_slm_client_id', true);
        if ($client_id) {
            $participants[] = intval($client_id);
        }

        // Additional clients
        $additional = get_post_meta($case_id, '_slm_additional_clients', true) ?: [];
        foreach ($additional as $id) {
            $participants[] = intval($id);
        }

        // Lead lawyer
        $lawyer = get_post_meta($case_id, '_slm_lead_lawyer', true);
        if ($lawyer) {
            $participants[] = intval($lawyer);
        }

        // Case team
        $team = get_post_meta($case_id, '_slm_case_team', true) ?: [];
        foreach ($team as $id) {
            $participants[] = intval($id);
        }

        return array_unique(array_filter($participants));
    }

    /**
     * Get email content preference for user.
     */
    public static function get_email_content_preference($user_id) {
        // Lawyers always get full content
        if (self::user_is_lawyer($user_id)) {
            return 'full';
        }

        // Clients check preference
        $pref = get_user_meta($user_id, 'slm_email_message_content', true);

        return $pref ?: 'link_only'; // Default to secure
    }

    /**
     * Check if user has email notifications enabled.
     */
    public static function user_has_email_enabled($user_id) {
        $setting = get_user_meta($user_id, 'slm_email_notifications', true);

        return $setting !== 'disabled';
    }
}

// Initialize
add_action('plugins_loaded', ['SLM_Messaging', 'init']);
