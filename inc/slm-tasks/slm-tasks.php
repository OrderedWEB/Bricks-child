<?php
/**
 * SLM Task Management System
 * 
 * Main orchestrator for task lists, templates, instances,
 * notifications, timeline calculations, and progress tracking.
 * 
 * @package SLM_Tasks
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

class SLM_Tasks {
    
    private static $instance = null;
    private static $initialized = false;
    
    const VERSION = '1.0.0';
    const DB_VERSION = '1.0.0';
    const PLUGIN_PATH = __DIR__;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        $instance = self::instance();
        $instance->load_dependencies();
        $instance->init_hooks();
        $instance->init_components();
    }
    
    private function load_dependencies() {
        $files = [
            'class-slm-task-lists.php',
            'class-slm-task-templates.php',
            'class-slm-task-instances.php',
            'class-slm-notifications.php',
            'class-slm-timeline.php',
            'class-slm-task-audit.php',
            'class-slm-task-acf.php',
            'class-slm-task-settings.php',
        ];
        
        foreach ($files as $file) {
            $path = self::PLUGIN_PATH . '/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        add_action('init', [$this, 'register_post_types'], 5);
        add_action('init', [$this, 'register_taxonomies'], 6);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_slm_complete_task', [$this, 'ajax_complete_task']);
        add_action('wp_ajax_slm_get_task_details', [$this, 'ajax_get_task_details']);
        add_action('wp_ajax_slm_get_case_tasks', [$this, 'ajax_get_case_tasks']);
        add_action('wp_ajax_slm_apply_task_list', [$this, 'ajax_apply_task_list']);
        add_action('wp_ajax_slm_create_ad_hoc_task', [$this, 'ajax_create_ad_hoc_task']);
        add_action('wp_ajax_slm_update_task', [$this, 'ajax_update_task']);
        add_action('wp_ajax_slm_cancel_task', [$this, 'ajax_cancel_task']);
        add_action('wp_ajax_slm_get_notifications', [$this, 'ajax_get_notifications']);
        add_action('wp_ajax_slm_mark_notification_read', [$this, 'ajax_mark_notification_read']);
        add_action('wp_ajax_slm_get_ical_url', [$this, 'ajax_get_ical_url']);
        
        // Cron jobs
        add_action('slm_check_overdue_tasks', [$this, 'cron_check_overdue_tasks']);
        add_action('slm_send_daily_digest', [$this, 'cron_send_daily_digest']);
        add_action('slm_process_escalations', [$this, 'cron_process_escalations']);
        
        // Integration hooks
        add_action('gform_after_submission', [$this, 'handle_form_submission'], 10, 2);
        add_action('slm_document_uploaded', [$this, 'handle_document_upload'], 10, 3);
        add_action('slm_envelope_completed', [$this, 'handle_signature_complete'], 10, 2);
        add_action('woocommerce_payment_complete', [$this, 'handle_payment_complete'], 10, 1);
        
        // iCal feed
        add_action('init', [$this, 'register_ical_endpoint']);
        add_action('template_redirect', [$this, 'handle_ical_request']);
    }
    
    private function init_components() {
        if (class_exists('SLM_Task_Lists')) {
            SLM_Task_Lists::init();
        }
        if (class_exists('SLM_Task_Templates')) {
            SLM_Task_Templates::init();
        }
        if (class_exists('SLM_Task_Instances')) {
            SLM_Task_Instances::init();
        }
        if (class_exists('SLM_Notifications')) {
            SLM_Notifications::init();
        }
        if (class_exists('SLM_Timeline')) {
            SLM_Timeline::init();
        }
        if (class_exists('SLM_Task_Audit')) {
            SLM_Task_Audit::init();
        }
        if (class_exists('SLM_Task_ACF')) {
            SLM_Task_ACF::init();
        }
        if (class_exists('SLM_Task_Settings')) {
            SLM_Task_Settings::init();
        }
    }
    
    public function activate() {
        $this->create_tables();
        $this->create_default_options();
        $this->schedule_cron_jobs();
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Audit log table
        $audit_table = $wpdb->prefix . 'slm_audit_log';
        $audit_sql = "CREATE TABLE IF NOT EXISTS {$audit_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(50) NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            old_value LONGTEXT NULL,
            new_value LONGTEXT NULL,
            metadata JSON NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_object (object_type, object_id),
            KEY idx_user (user_id),
            KEY idx_event (event_type),
            KEY idx_created (created_at)
        ) {$charset_collate};";
        
        // Firm holidays table
        $holidays_table = $wpdb->prefix . 'slm_firm_holidays';
        $holidays_sql = "CREATE TABLE IF NOT EXISTS {$holidays_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            holiday_date DATE NOT NULL,
            holiday_name VARCHAR(255) NOT NULL,
            recurring TINYINT(1) NOT NULL DEFAULT 0,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_date (holiday_date),
            KEY idx_recurring (recurring)
        ) {$charset_collate};";
        
        // Task dependencies table
        $deps_table = $wpdb->prefix . 'slm_task_dependencies';
        $deps_sql = "CREATE TABLE IF NOT EXISTS {$deps_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            task_instance_id BIGINT UNSIGNED NOT NULL,
            depends_on_task_id BIGINT UNSIGNED NOT NULL,
            dependency_type VARCHAR(20) NOT NULL DEFAULT 'completion',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            resolved_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_task (task_instance_id),
            KEY idx_depends (depends_on_task_id),
            KEY idx_status (status)
        ) {$charset_collate};";
        
        // Progress snapshots
        $progress_table = $wpdb->prefix . 'slm_progress_snapshots';
        $progress_sql = "CREATE TABLE IF NOT EXISTS {$progress_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            case_id BIGINT UNSIGNED NOT NULL,
            task_list_id BIGINT UNSIGNED NULL,
            total_tasks INT UNSIGNED NOT NULL DEFAULT 0,
            completed_tasks INT UNSIGNED NOT NULL DEFAULT 0,
            percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            snapshot_date DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_case_list_date (case_id, task_list_id, snapshot_date),
            KEY idx_case (case_id),
            KEY idx_date (snapshot_date)
        ) {$charset_collate};";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($audit_sql);
        dbDelta($holidays_sql);
        dbDelta($deps_sql);
        dbDelta($progress_sql);
        
        update_option('slm_tasks_db_version', self::DB_VERSION);
    }
    
    private function create_default_options() {
        if (!get_option('slm_service_tier_config')) {
            update_option('slm_service_tier_config', [
                'standard' => ['multiplier' => 1.0, 'working_week' => 5, 'label' => 'Standard'],
                'fast' => ['multiplier' => 0.5, 'working_week' => 5, 'label' => 'Fast'],
                'expedited' => ['multiplier' => 0.25, 'working_week' => 7, 'label' => 'Expedited']
            ]);
        }
        
        if (!get_option('slm_case_type_config')) {
            update_option('slm_case_type_config', [
                'CIT' => 'Citizenship',
                'VISA' => 'Visa Application',
                'IMM' => 'Immigration',
                'PROB' => 'Probate',
                'CORP' => 'Corporate',
                'PROP' => 'Property'
            ]);
        }
        
        if (!get_option('slm_notification_config')) {
            update_option('slm_notification_config', [
                'task_due_warning_days' => [3, 1],
                'escalation_days' => [1, 3, 7, 14],
                'digest_time' => '09:00',
                'enable_email_notifications' => true,
                'enable_digest_mode' => true
            ]);
        }
        
        if (!get_option('slm_onboarding_config')) {
            update_option('slm_onboarding_config', [
                'max_failed_attempts' => 5,
                'lockout_minutes' => 30,
                'max_lockouts_24h' => 3,
                'meeting_date_tolerance_days' => 2,
                'first_login_link_expiry_hours' => 72,
                'notify_lawyer_after_attempts' => 3
            ]);
        }
        
        if (!get_option('slm_audit_retention_config')) {
            update_option('slm_audit_retention_config', [
                'minimum_retention_years' => 7,
                'maximum_retention_years' => 10,
                'last_purge_date' => null,
                'purged_by' => null
            ]);
        }
    }
    
    private function schedule_cron_jobs() {
        if (!wp_next_scheduled('slm_check_overdue_tasks')) {
            wp_schedule_event(time(), 'six_hours', 'slm_check_overdue_tasks');
        }
        
        if (!wp_next_scheduled('slm_send_daily_digest')) {
            $digest_time = get_option('slm_notification_config')['digest_time'] ?? '09:00';
            $timestamp = strtotime('today ' . $digest_time);
            if ($timestamp < time()) {
                $timestamp = strtotime('tomorrow ' . $digest_time);
            }
            wp_schedule_event($timestamp, 'daily', 'slm_send_daily_digest');
        }
        
        if (!wp_next_scheduled('slm_process_escalations')) {
            wp_schedule_event(time(), 'six_hours', 'slm_process_escalations');
        }
    }
    
    public function register_post_types() {
        register_post_type('slm_task_list', [
            'labels' => [
                'name' => __('Task Lists', 'flavor'),
                'singular_name' => __('Task List', 'flavor'),
                'add_new' => __('Add Task List', 'flavor'),
                'add_new_item' => __('Add New Task List', 'flavor'),
                'edit_item' => __('Edit Task List', 'flavor'),
                'view_item' => __('View Task List', 'flavor'),
                'search_items' => __('Search Task Lists', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-list-view',
            'menu_position' => 27,
            'supports' => ['title', 'editor'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'rewrite' => false,
        ]);
        
        register_post_type('slm_task_template', [
            'labels' => [
                'name' => __('Task Templates', 'flavor'),
                'singular_name' => __('Task Template', 'flavor'),
                'add_new' => __('Add Template', 'flavor'),
                'add_new_item' => __('Add New Task Template', 'flavor'),
                'edit_item' => __('Edit Task Template', 'flavor'),
                'view_item' => __('View Task Template', 'flavor'),
                'search_items' => __('Search Task Templates', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=slm_task_list',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'rewrite' => false,
        ]);
        
        register_post_type('slm_task_instance', [
            'labels' => [
                'name' => __('Tasks', 'flavor'),
                'singular_name' => __('Task', 'flavor'),
                'add_new' => __('Add Task', 'flavor'),
                'add_new_item' => __('Add New Task', 'flavor'),
                'edit_item' => __('Edit Task', 'flavor'),
                'view_item' => __('View Task', 'flavor'),
                'search_items' => __('Search Tasks', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=slm_case',
            'menu_icon' => 'dashicons-yes-alt',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'rewrite' => false,
        ]);
        
        register_post_type('slm_notification', [
            'labels' => [
                'name' => __('Notifications', 'flavor'),
                'singular_name' => __('Notification', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'menu_icon' => 'dashicons-bell',
            'supports' => ['title'],
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'rewrite' => false,
        ]);
    }
    
    public function register_taxonomies() {
        register_taxonomy('slm_task_category', ['slm_task_template'], [
            'labels' => [
                'name' => __('Task Categories', 'flavor'),
                'singular_name' => __('Task Category', 'flavor'),
            ],
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => false,
        ]);
    }
    
    public function enqueue_admin_assets($hook) {
        $screen = get_current_screen();
        $task_screens = ['slm_task_list', 'slm_task_template', 'slm_task_instance', 'slm_case', 'slm_notification'];
        
        $is_task_screen = false;
        foreach ($task_screens as $type) {
            if (strpos($screen->id, $type) !== false) {
                $is_task_screen = true;
                break;
            }
        }
        
        if (!$is_task_screen && strpos($hook, 'slm-tasks') === false) {
            return;
        }
        
        wp_enqueue_style('slm-tasks-admin', get_stylesheet_directory_uri() . '/inc/tasks/assets/css/admin.css', [], self::VERSION);
        wp_enqueue_script('slm-tasks-admin', get_stylesheet_directory_uri() . '/inc/tasks/assets/js/admin.js', ['jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker'], self::VERSION, true);
        
        wp_localize_script('slm-tasks-admin', 'slmTasksAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slm_tasks_admin'),
            'strings' => [
                'confirmCancel' => __('Are you sure you want to cancel this task?', 'flavor'),
                'confirmApplyList' => __('Apply this task list to the case?', 'flavor'),
                'savingChanges' => __('Saving changes...', 'flavor'),
                'changesSaved' => __('Changes saved successfully.', 'flavor'),
                'error' => __('An error occurred. Please try again.', 'flavor'),
            ],
        ]);
    }
    
    public function enqueue_frontend_assets() {
        if (!is_page(['client-portal', 'lawyer-portal', 'my-tasks', 'case-tasks'])) {
            return;
        }
        
        wp_enqueue_style('slm-tasks-frontend', get_stylesheet_directory_uri() . '/inc/tasks/assets/css/frontend.css', [], self::VERSION);
        wp_enqueue_script('slm-tasks-frontend', get_stylesheet_directory_uri() . '/inc/tasks/assets/js/frontend.js', ['jquery'], self::VERSION, true);
        
        $user_id = get_current_user_id();
        $active_case = $this->get_user_active_case($user_id);
        
        wp_localize_script('slm-tasks-frontend', 'slmTasksConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('slm/v1/'),
            'nonce' => wp_create_nonce('slm_tasks_frontend'),
            'caseId' => $active_case ? $active_case->ID : 0,
            'userId' => $user_id,
            'isLawyer' => $this->user_is_lawyer($user_id),
            'strings' => [
                'loading' => __('Loading...', 'flavor'),
                'markComplete' => __('Mark Complete', 'flavor'),
                'uploading' => __('Uploading...', 'flavor'),
                'viewAll' => __('View All Tasks', 'flavor'),
                'viewMine' => __('View My Tasks', 'flavor'),
            ],
        ]);
    }
    
    private function get_user_active_case($user_id) {
        if (class_exists('SLM_Case_CPT')) {
            $cases = SLM_Case_CPT::get_user_cases($user_id, 'active');
            return !empty($cases) ? $cases[0] : null;
        }
        
        $args = [
            'post_type' => 'slm_case',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_slm_client_id', 'value' => $user_id, 'compare' => '='],
                ['key' => '_slm_additional_clients', 'value' => $user_id, 'compare' => 'LIKE']
            ],
            'tax_query' => [
                ['taxonomy' => 'slm_case_status', 'field' => 'slug', 'terms' => 'active']
            ]
        ];
        
        $cases = get_posts($args);
        return !empty($cases) ? $cases[0] : null;
    }
    
    private function user_is_lawyer($user_id) {
        $user = get_userdata($user_id);
        if (!$user) return false;
        
        $lawyer_roles = ['administrator', 'editor', 'slm_lawyer', 'slm_paralegal'];
        return !empty(array_intersect($user->roles, $lawyer_roles));
    }
    
    public function register_ical_endpoint() {
        add_rewrite_tag('%slm_ical%', '([a-zA-Z0-9]+)');
        add_rewrite_rule('calendar/tasks/([a-zA-Z0-9]+)/?$', 'index.php?slm_ical=$matches[1]', 'top');
    }
    
    public function handle_ical_request() {
        $ical_token = get_query_var('slm_ical');
        if (empty($ical_token)) return;
        
        $user_id = $this->validate_ical_token($ical_token);
        if (!$user_id) {
            wp_die('Invalid calendar token', 'Calendar Error', ['response' => 403]);
        }
        
        $this->output_ical_feed($user_id);
        exit;
    }
    
    private function validate_ical_token($token) {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_slm_ical_token' AND meta_value = %s",
            $token
        ));
        return $user_id ? (int) $user_id : false;
    }
    
    private function output_ical_feed($user_id) {
        $tasks = class_exists('SLM_Task_Instances') 
            ? SLM_Task_Instances::get_user_tasks($user_id, ['status' => ['pending', 'in_progress', 'available'], 'has_due_date' => true])
            : [];
        
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="tasks.ics"');
        
        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//Studio Legale Metta//Task Manager//EN\r\n";
        echo "CALSCALE:GREGORIAN\r\n";
        echo "METHOD:PUBLISH\r\n";
        echo "X-WR-CALNAME:{$site_name} Tasks\r\n";
        
        foreach ($tasks as $task) {
            $due_date = get_post_meta($task->ID, '_slm_due_date', true);
            if (empty($due_date)) continue;
            
            $case_id = get_post_meta($task->ID, '_slm_case_id', true);
            $case_title = get_the_title($case_id);
            
            $uid = 'task-' . $task->ID . '@' . parse_url($site_url, PHP_URL_HOST);
            $dtstamp = gmdate('Ymd\THis\Z');
            $dtstart = date('Ymd', strtotime($due_date));
            
            $summary = $this->ical_escape($task->post_title);
            $description = $this->ical_escape("Case: {$case_title}\n" . get_post_meta($task->ID, '_slm_task_description', true));
            
            echo "BEGIN:VEVENT\r\n";
            echo "UID:{$uid}\r\n";
            echo "DTSTAMP:{$dtstamp}\r\n";
            echo "DTSTART;VALUE=DATE:{$dtstart}\r\n";
            echo "SUMMARY:{$summary}\r\n";
            echo "DESCRIPTION:{$description}\r\n";
            echo "END:VEVENT\r\n";
        }
        
        echo "END:VCALENDAR\r\n";
    }
    
    private function ical_escape($string) {
        return str_replace(['\\', "\n", ',', ';'], ['\\\\', '\\n', '\\,', '\\;'], $string);
    }
    
    // AJAX Handlers
    
    public function ajax_complete_task() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $task_id = intval($_POST['task_id'] ?? 0);
        $completion_data = $_POST['data'] ?? [];
        
        if (!$task_id) {
            wp_send_json_error(['message' => 'Invalid task ID']);
        }
        
        $user_id = get_current_user_id();
        if (!$this->user_can_complete_task($user_id, $task_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $result = SLM_Task_Instances::complete_task($task_id, $user_id, $completion_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => 'Task completed successfully',
            'task' => SLM_Task_Instances::get_task_data($task_id)
        ]);
    }
    
    public function ajax_get_task_details() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $task_id = intval($_GET['task_id'] ?? 0);
        if (!$task_id) {
            wp_send_json_error(['message' => 'Invalid task ID']);
        }
        
        $user_id = get_current_user_id();
        if (!$this->user_can_view_task($user_id, $task_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        wp_send_json_success(SLM_Task_Instances::get_task_data($task_id, true));
    }
    
    public function ajax_get_case_tasks() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $case_id = intval($_GET['case_id'] ?? 0);
        $view = sanitize_text_field($_GET['view'] ?? 'my');
        
        if (!$case_id) {
            wp_send_json_error(['message' => 'Invalid case ID']);
        }
        
        $user_id = get_current_user_id();
        if (!$this->user_can_view_case($user_id, $case_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $tasks = SLM_Task_Instances::get_tasks_for_case($case_id, ['view' => $view, 'user_id' => $user_id]);
        $progress = SLM_Task_Instances::get_case_progress($case_id);
        
        wp_send_json_success(['tasks' => $tasks, 'progress' => $progress]);
    }
    
    public function ajax_apply_task_list() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $case_id = intval($_POST['case_id'] ?? 0);
        $task_list_id = intval($_POST['task_list_id'] ?? 0);
        
        if (!$case_id || !$task_list_id) {
            wp_send_json_error(['message' => 'Missing required parameters']);
        }
        
        $result = SLM_Task_Instances::apply_task_list_to_case($task_list_id, $case_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => 'Task list applied successfully',
            'tasks_created' => $result['tasks_created'],
            'tasks' => $result['tasks']
        ]);
    }
    
    public function ajax_create_ad_hoc_task() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $case_id = intval($_POST['case_id'] ?? 0);
        $task_data = $_POST['task'] ?? [];
        
        if (!$case_id) {
            wp_send_json_error(['message' => 'Missing case ID']);
        }
        
        $result = SLM_Task_Instances::create_ad_hoc_task($case_id, $task_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => 'Task created successfully',
            'task' => SLM_Task_Instances::get_task_data($result)
        ]);
    }
    
    public function ajax_update_task() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $task_id = intval($_POST['task_id'] ?? 0);
        $updates = $_POST['updates'] ?? [];
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');
        
        if (!$task_id) {
            wp_send_json_error(['message' => 'Invalid task ID']);
        }
        
        if (empty($reason)) {
            wp_send_json_error(['message' => 'Edit reason is required']);
        }
        
        $result = SLM_Task_Instances::update_task($task_id, $updates, $reason);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => 'Task updated successfully',
            'task' => SLM_Task_Instances::get_task_data($task_id)
        ]);
    }
    
    public function ajax_cancel_task() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $task_id = intval($_POST['task_id'] ?? 0);
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');
        $handle_dependents = sanitize_text_field($_POST['handle_dependents'] ?? 'reassign');
        
        if (!$task_id) {
            wp_send_json_error(['message' => 'Invalid task ID']);
        }
        
        if (empty($reason)) {
            wp_send_json_error(['message' => 'Cancellation reason is required']);
        }
        
        $result = SLM_Task_Instances::cancel_task($task_id, $reason, $handle_dependents);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => 'Task cancelled',
            'affected_tasks' => $result['affected_tasks']
        ]);
    }
    
    public function ajax_get_notifications() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $user_id = get_current_user_id();
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
        $limit = intval($_GET['limit'] ?? 20);
        
        $notifications = SLM_Notifications::get_user_notifications($user_id, ['unread_only' => $unread_only, 'limit' => $limit]);
        $unread_count = SLM_Notifications::get_unread_count($user_id);
        
        wp_send_json_success(['notifications' => $notifications, 'unread_count' => $unread_count]);
    }
    
    public function ajax_mark_notification_read() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $notification_id = intval($_POST['notification_id'] ?? 0);
        $mark_all = isset($_POST['mark_all']) && $_POST['mark_all'] === 'true';
        $user_id = get_current_user_id();
        
        if ($mark_all) {
            SLM_Notifications::mark_all_read($user_id);
        } elseif ($notification_id) {
            SLM_Notifications::mark_read($notification_id, $user_id);
        }
        
        wp_send_json_success(['message' => 'Marked as read']);
    }
    
    public function ajax_get_ical_url() {
        check_ajax_referer('slm_tasks_frontend', 'nonce');
        
        $user_id = get_current_user_id();
        $token = get_user_meta($user_id, '_slm_ical_token', true);
        
        if (empty($token)) {
            $token = wp_generate_password(32, false);
            update_user_meta($user_id, '_slm_ical_token', $token);
        }
        
        wp_send_json_success(['url' => home_url("/calendar/tasks/{$token}/")]);
    }
    
    // Integration handlers
    
    public function handle_form_submission($entry, $form) {
        $task_instance_id = rgar($entry, 'slm_task_instance_id');
        
        if (empty($task_instance_id)) {
            foreach ($form['fields'] as $field) {
                if ($field->type === 'hidden' && strpos($field->inputName, 'task_instance') !== false) {
                    $task_instance_id = rgar($entry, $field->id);
                    break;
                }
            }
        }
        
        if (empty($task_instance_id)) return;
        
        $task = get_post($task_instance_id);
        if (!$task || $task->post_type !== 'slm_task_instance') return;
        
        $task_type = get_post_meta($task_instance_id, '_slm_task_type', true);
        if ($task_type !== 'form') return;
        
        $completion_trigger = get_post_meta($task_instance_id, '_slm_completion_trigger', true);
        
        update_post_meta($task_instance_id, '_slm_gravity_form_entry_id', $entry['id']);
        update_post_meta($task_instance_id, '_slm_cached_form_summary', $this->build_form_summary($entry, $form));
        
        if ($completion_trigger === 'on_submit') {
            SLM_Task_Instances::complete_task($task_instance_id, $entry['created_by'], [
                'source' => 'gravity_form',
                'entry_id' => $entry['id']
            ]);
        } else {
            update_post_meta($task_instance_id, '_slm_task_status', 'pending_review');
            
            $case_id = get_post_meta($task_instance_id, '_slm_case_id', true);
            $lawyer_id = get_post_meta($case_id, '_slm_lead_lawyer', true);
            
            if ($lawyer_id) {
                SLM_Notifications::create([
                    'recipient' => $lawyer_id,
                    'type' => 'task_requires_review',
                    'case_id' => $case_id,
                    'task_id' => $task_instance_id,
                    'title' => 'Form Submission Requires Review',
                    'body' => sprintf('A form submission for "%s" requires your review.', $task->post_title)
                ]);
            }
        }
    }
    
    private function build_form_summary($entry, $form) {
        $summary = [];
        foreach ($form['fields'] as $field) {
            if ($field->visibility === 'administrative') continue;
            if (in_array($field->type, ['hidden', 'html', 'section', 'page'])) continue;
            
            $value = rgar($entry, $field->id);
            if (!empty($value)) {
                $summary[$field->label] = $value;
            }
        }
        return $summary;
    }
    
    public function handle_document_upload($document_id, $case_id, $uploader_id) {
        $category = get_post_meta($document_id, '_slm_doc_category', true);
        
        $tasks = get_posts([
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_slm_case_id', 'value' => $case_id],
                ['key' => '_slm_task_type', 'value' => 'upload'],
                ['key' => '_slm_task_status', 'value' => ['pending', 'available', 'in_progress'], 'compare' => 'IN'],
                ['key' => '_slm_document_category', 'value' => $category]
            ]
        ]);
        
        foreach ($tasks as $task) {
            SLM_Task_Instances::complete_task($task->ID, $uploader_id, [
                'source' => 'document_upload',
                'document_id' => $document_id
            ]);
            break;
        }
    }
    
    public function handle_signature_complete($envelope_id, $document_id) {
        $tasks = get_posts([
            'post_type' => 'slm_task_instance',
            'posts_per_page' => -1,
            'meta_query' => [
                ['key' => '_slm_task_type', 'value' => 'signature'],
                ['key' => '_slm_envelope_id', 'value' => $envelope_id]
            ]
        ]);
        
        foreach ($tasks as $task) {
            $client_id = get_post_meta(get_post_meta($task->ID, '_slm_case_id', true), '_slm_client_id', true);
            SLM_Task_Instances::complete_task($task->ID, $client_id, [
                'source' => 'envelope_signed',
                'envelope_id' => $envelope_id,
                'signed_document_id' => $document_id
            ]);
        }
    }
    
    public function handle_payment_complete($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $task_id = $order->get_meta('_slm_task_instance_id');
        if (empty($task_id)) return;
        
        $task = get_post($task_id);
        if (!$task || $task->post_type !== 'slm_task_instance') return;
        
        SLM_Task_Instances::complete_task($task_id, $order->get_customer_id(), [
            'source' => 'woocommerce_payment',
            'order_id' => $order_id,
            'payment_method' => $order->get_payment_method(),
            'amount' => $order->get_total(),
            'transaction_id' => $order->get_transaction_id()
        ]);
    }
    
    // Cron handlers
    public function cron_check_overdue_tasks() { SLM_Task_Instances::process_overdue_tasks(); }
    public function cron_send_daily_digest() { SLM_Notifications::send_daily_digests(); }
    public function cron_process_escalations() { SLM_Task_Instances::process_escalations(); }
    
    // Permission helpers
    
    private function user_can_complete_task($user_id, $task_id) {
        $assigned_user = get_post_meta($task_id, '_slm_assigned_user', true);
        if ((int) $assigned_user === (int) $user_id) return true;
        
        $case_id = get_post_meta($task_id, '_slm_case_id', true);
        return $this->user_is_case_lawyer($user_id, $case_id);
    }
    
    private function user_can_view_task($user_id, $task_id) {
        return $this->user_can_view_case($user_id, get_post_meta($task_id, '_slm_case_id', true));
    }
    
    private function user_can_view_case($user_id, $case_id) {
        $client_id = get_post_meta($case_id, '_slm_client_id', true);
        if ((int) $client_id === (int) $user_id) return true;
        
        $additional = get_post_meta($case_id, '_slm_additional_clients', true);
        if (is_array($additional) && in_array($user_id, $additional)) return true;
        
        return $this->user_is_case_lawyer($user_id, $case_id);
    }
    
    private function user_is_case_lawyer($user_id, $case_id) {
        $lead = get_post_meta($case_id, '_slm_lead_lawyer', true);
        if ((int) $lead === (int) $user_id) return true;
        
        $team = get_post_meta($case_id, '_slm_case_team', true);
        if (is_array($team) && in_array($user_id, $team)) return true;
        
        return user_can($user_id, 'manage_options');
    }
    
    // Utility methods
    
    public static function get_task_types() {
        return [
            'checkbox' => __('Simple Checkbox', 'flavor'),
            'upload' => __('Document Upload', 'flavor'),
            'form' => __('Form Submission', 'flavor'),
            'payment' => __('Payment', 'flavor'),
            'signature' => __('Signature', 'flavor'),
            'external' => __('External Action', 'flavor')
        ];
    }
    
    public static function get_task_statuses() {
        return [
            'locked' => __('Locked', 'flavor'),
            'available' => __('Available', 'flavor'),
            'in_progress' => __('In Progress', 'flavor'),
            'pending_review' => __('Pending Review', 'flavor'),
            'complete' => __('Complete', 'flavor'),
            'cancelled' => __('Cancelled', 'flavor')
        ];
    }
}

add_action('plugins_loaded', ['SLM_Tasks', 'init'], 20);

add_filter('cron_schedules', function($schedules) {
    if (!isset($schedules['six_hours'])) {
        $schedules['six_hours'] = ['interval' => 6 * HOUR_IN_SECONDS, 'display' => __('Every 6 Hours', 'flavor')];
    }
    return $schedules;
});
