<?php
/**
 * Bricks Child Theme - Functions.php
 * 
 * Master loader for:
 * - Engagement Letter System
 * - SLM Systems (DMS, Client Onboarding, Tasks, Messaging, Portal)
 * 
 * @package flavor
 * @version 2.1.0
 */

if (!defined('ABSPATH')) exit;

// Suppress deprecated warnings in production
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);

// =============================================================================
// SECTION 1: SYSTEM PATHS & CONSTANTS
// =============================================================================

define('EL_CHILD_PATH', get_stylesheet_directory());
define('EL_CHILD_URL', get_stylesheet_directory_uri());

if (!defined('EL_PATH')) {
    define('EL_PATH', EL_CHILD_PATH . '/inc/el/');
}

// =============================================================================
// SECTION 2: ENGAGEMENT LETTER SYSTEM
// =============================================================================

/**
 * Phase 1: Load EL Core Modules (No dependencies)
 */
function el_load_core_modules() {
    $core_files = [
        'core/constants.php',
        'core/session.php',
        'core/helpers.php',
        'core/merge-tags.php',
    ];
    
    foreach ($core_files as $file) {
        $filepath = EL_PATH . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}
el_load_core_modules();

/**
 * Phase 2: Load EL WooCommerce Modules
 */
function el_load_woocommerce_modules() {
    if (!function_exists('WC') || !class_exists('WooCommerce')) {
        return;
    }
    
    $wc_files = [
        'core/woocommerce.php',
        'features/grouped-products.php',
    ];
    
    foreach ($wc_files as $file) {
        $filepath = EL_PATH . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}
add_action('after_setup_theme', 'el_load_woocommerce_modules', 99);

/**
 * Phase 3: Load EL Feature Modules
 */
function el_load_feature_modules() {
    $feature_files = [
        'features/resume-draft.php',
        'features/payment-handler.php',
        'features/signature-collection.php',
        'features/encryption.php',
        'features/start-over.php',
        '../el-print-system.php',
    ];
    
    foreach ($feature_files as $file) {
        $filepath = EL_PATH . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}
add_action('init', 'el_load_feature_modules', 20);

/**
 * Phase 4: Load EL Tab Modules
 */
function el_load_tab_modules() {
    $tab_files = [
        'tabs/tab1-client.php',
        'tabs/tab1-email-autocomplete.php',
        'tabs/tab1-user-handler.php',
        'tabs/tab2-templates.php',
        'tabs/tab3-cart.php',
        'tabs/tab4-preview.php',
        'tabs/tab5-export.php',
    ];
    
    foreach ($tab_files as $file) {
        $filepath = EL_PATH . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}
add_action('init', 'el_load_tab_modules', 25);

/**
 * Phase 5: Load EL Admin Modules
 */
function el_load_admin_modules() {
    $admin_files = [
        'admin/cpt-engagement.php',
    ];
    
    foreach ($admin_files as $file) {
        $filepath = EL_PATH . $file;
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}
add_action('init', 'el_load_admin_modules', 30);

// =============================================================================
// SECTION 3: SLM SYSTEMS (DMS, Onboarding, Tasks, Messaging, Portal)
// =============================================================================

/**
 * SLM System Toggles
 */
define('SLM_DMS_ENABLED', true);
define('SLM_CLIENT_ONBOARDING_ENABLED', true);
define('SLM_TASKS_ENABLED', true);
define('SLM_MESSAGING_ENABLED', true);
define('SLM_PORTAL_ENABLED', false); // Enable when portal is installed

define('SLM_DEBUG', false);
define('SLM_VERSION', '1.0.0');

/**
 * Load all SLM Systems
 */
function slm_load_systems() {
    $theme_dir = get_stylesheet_directory();
    $loaded = [];
    $errors = [];
    
    // 1. Document Management System
    if (SLM_DMS_ENABLED) {
        $file = $theme_dir . '/inc/dms/slm-dms.php';
        if (file_exists($file)) {
            require_once $file;
            $loaded[] = 'DMS';
        } else {
            $errors[] = 'DMS';
        }
    }
    
    // 2. Client Onboarding
    if (SLM_CLIENT_ONBOARDING_ENABLED) {
        $file = $theme_dir . '/inc/client-onboarding/slm-client-onboarding.php';
        if (file_exists($file)) {
            require_once $file;
            $loaded[] = 'Client Onboarding';
        } else {
            $errors[] = 'Client Onboarding';
        }
    }
    
    // 3. Task Management
    if (SLM_TASKS_ENABLED) {
        $file = $theme_dir . '/inc/slm-tasks/slm-tasks.php';
        if (file_exists($file)) {
            require_once $file;
            $loaded[] = 'Tasks';
        } else {
            $errors[] = 'Tasks';
        }
    }
    
    // 4. Messaging
    if (SLM_MESSAGING_ENABLED) {
        $file = $theme_dir . '/inc/slm-messaging/slm-messaging.php';
        if (file_exists($file)) {
            require_once $file;
            $loaded[] = 'Messaging';
        } else {
            $errors[] = 'Messaging';
        }
    }
    
    // 5. Portal (when ready)
    if (SLM_PORTAL_ENABLED) {
        $file = $theme_dir . '/inc/slm-portal/slm-portal.php';
        if (file_exists($file)) {
            require_once $file;
            $loaded[] = 'Portal';
        }
    }
    
    // Store loaded systems
    if (!defined('SLM_LOADED_SYSTEMS')) {
        define('SLM_LOADED_SYSTEMS', implode(', ', $loaded));
    }
    
    // Admin notice for missing files
    if (!empty($errors) && is_admin()) {
        add_action('admin_notices', function() use ($errors) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>SLM Systems:</strong> Failed to load: ' . esc_html(implode(', ', $errors));
            echo '</p></div>';
        });
    }
}


add_action('after_setup_theme', 'slm_load_systems', 5);

/**
 * SLM Dependency Check
 */
function slm_check_dependencies() {
    $missing = [];
    
    if (!class_exists('ACF')) {
        $missing[] = 'Advanced Custom Fields PRO';
    }
    if (!class_exists('WooCommerce')) {
        $missing[] = 'WooCommerce';
    }
    if (!class_exists('GFForms')) {
        $missing[] = 'Gravity Forms';
    }
    
    if (!empty($missing) && is_admin()) {
        add_action('admin_notices', function() use ($missing) {
            echo '<div class="notice notice-warning"><p>';
            echo '<strong>SLM Systems:</strong> Missing plugins: ' . esc_html(implode(', ', $missing));
            echo '</p></div>';
        });
    }
}
/**
 * Initialize SLM system classes
 */
function slm_init_systems() {
    // Client Onboarding - uses instance()
    if (class_exists('SLM_Client_Onboarding') && method_exists('SLM_Client_Onboarding', 'instance')) {
        SLM_Client_Onboarding::instance();
    }
    
    // DMS
    if (class_exists('SLM_DMS')) {
        if (method_exists('SLM_DMS', 'get_instance')) {
            SLM_DMS::get_instance();
        } elseif (method_exists('SLM_DMS', 'instance')) {
            SLM_DMS::instance();
        }
    }
    
    // Tasks
    if (class_exists('SLM_Tasks')) {
        if (method_exists('SLM_Tasks', 'get_instance')) {
            SLM_Tasks::get_instance();
        } elseif (method_exists('SLM_Tasks', 'instance')) {
            SLM_Tasks::instance();
        }
    }
    
    // Messaging  
    if (class_exists('SLM_Messaging')) {
        if (method_exists('SLM_Messaging', 'get_instance')) {
            SLM_Messaging::get_instance();
        } elseif (method_exists('SLM_Messaging', 'instance')) {
            SLM_Messaging::instance();
        }
    }
}
add_action('init', 'slm_init_systems', 5);
add_action('admin_init', 'slm_check_dependencies');

// =============================================================================
// SECTION 4: SLM HELPER FUNCTIONS
// =============================================================================

/**
 * Check if user is a lawyer/staff member
 */
function slm_is_lawyer($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return false;
    }
    
    $lawyer_roles = ['administrator', 'editor', 'slm_lawyer', 'slm_paralegal', 'slm_staff'];
    return !empty(array_intersect($lawyer_roles, $user->roles));
}

/**
 * Check if user is a client
 */
function slm_is_client($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return false;
    }
    
    $client_roles = ['subscriber', 'customer', 'slm_client'];
    return !empty(array_intersect($client_roles, $user->roles));
}

/**
 * Check if user can access a case
 */
function slm_user_can_access_case($user_id, $case_id) {
    if (!$user_id || !$case_id) {
        return false;
    }
    
    if (user_can($user_id, 'manage_options')) {
        return true;
    }
    
    // Check client
    if (get_post_meta($case_id, '_slm_client_id', true) == $user_id) {
        return true;
    }
    
    // Check additional clients
    $additional = get_post_meta($case_id, '_slm_additional_clients', true) ?: [];
    if (in_array($user_id, (array) $additional)) {
        return true;
    }
    
    // Check case team
    $team = get_post_meta($case_id, '_slm_case_team', true) ?: [];
    if (in_array($user_id, (array) $team)) {
        return true;
    }
    
    // Check lead lawyer
    if (get_post_meta($case_id, '_slm_lead_lawyer', true) == $user_id) {
        return true;
    }
    
    return false;
}

/**
 * Get user's accessible case IDs
 */
function slm_get_user_cases($user_id = null) {
    global $wpdb;
    
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (user_can($user_id, 'manage_options')) {
        return get_posts([
            'post_type' => 'slm_case',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'publish',
        ]);
    }
    
    $case_ids = [];
    
    // As primary client
    $case_ids = array_merge($case_ids, $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_slm_client_id' AND meta_value = %d",
        $user_id
    )));
    
    // As lead lawyer
    $case_ids = array_merge($case_ids, $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_slm_lead_lawyer' AND meta_value = %d",
        $user_id
    )));
    
    // In case team
    $case_ids = array_merge($case_ids, $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_slm_case_team' AND meta_value LIKE %s",
        '%"' . $user_id . '"%'
    )));
    
    // As additional client
    $case_ids = array_merge($case_ids, $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_slm_additional_clients' AND meta_value LIKE %s",
        '%"' . $user_id . '"%'
    )));
    
    return array_unique(array_filter($case_ids));
}

/**
 * Get portal URL for current user
 */
function slm_get_portal_url() {
    return slm_is_lawyer() ? home_url('/lawyer-portal/') : home_url('/client-portal/');
}

/**
 * Get login URL
 */
function slm_get_login_url($context = 'client') {
    return $context === 'lawyer' 
        ? home_url('/lawyer-portal/login/') 
        : home_url('/client-portal/login/');
}

/**
 * Debug logging (only when SLM_DEBUG is true)
 */
function slm_log($message, $data = null) {
    if (!SLM_DEBUG) return;
    
    $log = 'SLM: ' . $message;
    if ($data !== null) {
        $log .= ' | ' . print_r($data, true);
    }
    error_log($log);
}

// =============================================================================
// SECTION 5: SLM CUSTOM ROLES
// =============================================================================

/**
 * Register custom user roles on theme activation
 */
function slm_register_roles() {
    add_role('slm_lawyer', 'Lawyer', [
        'read' => true,
        'edit_posts' => true,
        'upload_files' => true,
        'edit_slm_cases' => true,
        'edit_others_slm_cases' => true,
        'publish_slm_cases' => true,
        'read_private_slm_cases' => true,
        'delete_slm_cases' => true,
        'edit_slm_documents' => true,
        'edit_others_slm_documents' => true,
        'manage_slm_tasks' => true,
    ]);
    
    add_role('slm_paralegal', 'Paralegal', [
        'read' => true,
        'upload_files' => true,
        'edit_slm_cases' => true,
        'edit_slm_documents' => true,
        'manage_slm_tasks' => true,
    ]);
    
    add_role('slm_staff', 'Staff', [
        'read' => true,
        'upload_files' => true,
        'edit_slm_documents' => true,
    ]);
    
    add_role('slm_client', 'Client', [
        'read' => true,
        'upload_files' => true,
    ]);
}
add_action('after_switch_theme', 'slm_register_roles');

// =============================================================================
// SECTION 6: SLM ADMIN BAR & UI
// =============================================================================

/**
 * Add portal links to admin bar
 */
function slm_admin_bar_links($admin_bar) {
    if (!is_user_logged_in()) return;
    
    if (slm_is_client()) {
        $admin_bar->add_node([
            'id' => 'slm-portal',
            'title' => '📋 My Portal',
            'href' => slm_get_portal_url(),
        ]);
    }
    
    if (slm_is_lawyer()) {
        $admin_bar->add_node([
            'id' => 'slm-lawyer-portal',
            'title' => '⚖️ Lawyer Portal',
            'href' => home_url('/lawyer-portal/'),
        ]);
        
        $admin_bar->add_node([
            'id' => 'slm-cases',
            'title' => 'Cases',
            'href' => admin_url('edit.php?post_type=slm_case'),
            'parent' => 'slm-lawyer-portal',
        ]);
        
        $admin_bar->add_node([
            'id' => 'slm-documents',
            'title' => 'Documents',
            'href' => admin_url('edit.php?post_type=slm_document'),
            'parent' => 'slm-lawyer-portal',
        ]);
        
        $admin_bar->add_node([
            'id' => 'slm-tasks-admin',
            'title' => 'Task Lists',
            'href' => admin_url('edit.php?post_type=slm_task_list'),
            'parent' => 'slm-lawyer-portal',
        ]);
    }
}
add_action('admin_bar_menu', 'slm_admin_bar_links', 100);

/**
 * Add SLM-specific body classes
 */
function slm_body_classes($classes) {
    if (is_user_logged_in()) {
        if (slm_is_lawyer()) {
            $classes[] = 'slm-user-lawyer';
        } elseif (slm_is_client()) {
            $classes[] = 'slm-user-client';
        }
    }
    
    if (strpos($_SERVER['REQUEST_URI'], '/client-portal') !== false) {
        $classes[] = 'slm-portal slm-client-portal';
    } elseif (strpos($_SERVER['REQUEST_URI'], '/lawyer-portal') !== false) {
        $classes[] = 'slm-portal slm-lawyer-portal';
    }
    
    if (isset($_COOKIE['slm_dark_mode']) && $_COOKIE['slm_dark_mode'] === 'true') {
        $classes[] = 'slm-dark-mode';
    }
    
    return $classes;
}
add_filter('body_class', 'slm_body_classes');



// =============================================================================
// SECTION 7: SESSION MANAGEMENT
// =============================================================================

/**
 * Start PHP session early if needed
 */
function slm_maybe_start_session() {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'slm_maybe_start_session', 1);

// =============================================================================
// SECTION 8: CRON SCHEDULES
// =============================================================================

/**
 * Register custom cron schedules
 */
function slm_cron_schedules($schedules) {
    $schedules['slm_five_minutes'] = [
        'interval' => 300,
        'display' => 'Every 5 Minutes',
    ];
    
    $schedules['slm_twice_daily'] = [
        'interval' => 43200,
        'display' => 'Twice Daily',
    ];
    
    return $schedules;
}
add_filter('cron_schedules', 'slm_cron_schedules');

// =============================================================================
// SECTION 9: ENGAGEMENT LETTER AJAX & SCRIPTS
// =============================================================================

/**
 * Check if current page is engagement letter wizard
 */
function el_is_wizard_page() {
    return is_page(151) || is_page('create-engagement-letter');
}

/**
 * Enqueue AJAX nonce for EL wizard
 */
function el_enqueue_ajax_nonce() {
    if (!el_is_wizard_page()) return;
    
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'el_enqueue_ajax_nonce', 1);

/**
 * Output EL AJAX variables in footer
 */
function el_output_ajax_vars() {
    if (!is_page('create-engagement-letter')) return;
    ?>
    <script>
    var el_ajax = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce(defined('EL_NONCE') ? EL_NONCE : 'el_nonce'); ?>'
    };
    var elAjax = {
        ajaxUrl: el_ajax.ajax_url,
        nonce: el_ajax.nonce
    };
    </script>
    <?php
}
add_action('wp_footer', 'el_output_ajax_vars', 999);

/**
 * Enqueue EL wizard JavaScript
 */
function el_enqueue_wizard_js() {
    if (!el_is_wizard_page()) return;
    
    wp_enqueue_script(
        'el-wizard',
        get_stylesheet_directory_uri() . '/js/el-wizard.js',
        ['jquery'],
        '1.0.2',
        true
    );
    
    wp_localize_script('el-wizard', 'el_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce(defined('EL_NONCE') ? EL_NONCE : 'el_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'el_enqueue_wizard_js', 20);

// =============================================================================
// SECTION 10: EL HEALTH CHECK & UTILITIES
// =============================================================================

/**
 * AJAX: System health check
 */
function el_ajax_health_check() {
    if (!defined('EL_DIAGNOSTIC_NONCE')) {
        wp_send_json_error(['message' => 'Diagnostic nonce not defined']);
        return;
    }
    
    check_ajax_referer(EL_DIAGNOSTIC_NONCE, 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
        return;
    }
    
    $health = [
        'status' => 'healthy',
        'version' => defined('EL_VERSION') ? EL_VERSION : 'UNKNOWN',
        'modules_loaded' => [
            'constants' => defined('EL_VERSION'),
            'session' => function_exists('el_init_session'),
            'helpers' => function_exists('el_get_meta'),
            'merge_tags' => function_exists('el_replace_merge_tags'),
            'woocommerce' => function_exists('el_save_cart_state'),
            'grouped_products' => function_exists('el_store_grouped_parent'),
        ],
        'dependencies' => [
            'woocommerce' => class_exists('WooCommerce'),
            'acf' => function_exists('get_field'),
            'gravity_forms' => class_exists('GFForms'),
        ],
    ];
    
    wp_send_json_success($health);
}
add_action('wp_ajax_el_health_check', 'el_ajax_health_check');

/**
 * AJAX: Check EL class existence
 */
function el_ajax_check_class_exists() {
    wp_send_json_success([
        'EL_Preview_Viewer' => class_exists('EL_Preview_Viewer'),
        'EL_Print_Data_Assembler' => class_exists('EL_Print_Data_Assembler'),
        'EL_Content_Blocks' => class_exists('EL_Content_Blocks'),
        'EL_MPDF_Generator' => class_exists('EL_MPDF_Generator'),
        'shortcode_exists' => shortcode_exists('el_preview_viewer'),
    ]);
}
add_action('wp_ajax_el_check_class_exists', 'el_ajax_check_class_exists');

/**
 * Cleanup on theme switch
 */
function el_cleanup_on_deactivation() {
    $timestamp = wp_next_scheduled('el_daily_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'el_daily_cleanup');
    }
}
add_action('switch_theme', 'el_cleanup_on_deactivation');

// =============================================================================
// SECTION 11: EMERGENCY KILL SWITCH
// =============================================================================

/**
 * Add define('EL_SYSTEM_DISABLED', true); to wp-config.php to disable EL system
 */
if (defined('EL_SYSTEM_DISABLED') && EL_SYSTEM_DISABLED) {
    remove_action('after_setup_theme', 'el_load_woocommerce_modules', 99);
    remove_action('init', 'el_load_feature_modules', 20);
    remove_action('init', 'el_load_tab_modules', 25);
    remove_action('init', 'el_load_admin_modules', 30);
}

// =============================================================================
// SECTION 12: DIAGNOSTIC OUTPUT (Admin Only)
// =============================================================================

/**
 * Output system diagnostic in page source (admins only)
 */
function slm_output_diagnostic() {
    if (!current_user_can('manage_options')) return;
    
    $theme_dir = get_stylesheet_directory();
    
    // Check files
    $files = [
        'DMS' => '/inc/dms/slm-dms.php',
        'Onboarding' => '/inc/client-onboarding/slm-client-onboarding.php',
        'Tasks' => '/inc/slm-tasks/slm-tasks.php',
        'Messaging' => '/inc/slm-messaging/slm-messaging.php',
    ];
    
    // Check classes
    $classes = [
        'SLM_Client_Onboarding',
        'SLM_DMS',
        'SLM_Tasks',
        'SLM_Messaging',
    ];
    
    // Check shortcodes
    global $shortcode_tags;
    $slm_shortcodes = array_filter(array_keys($shortcode_tags), function($k) {
        return strpos($k, 'slm_') === 0;
    });
    
    echo "\n<!-- SLM DIAGNOSTIC\n";
    echo "Files:\n";
    foreach ($files as $name => $path) {
        $status = file_exists($theme_dir . $path) ? '✓' : '✗';
        echo "  {$name}: {$status}\n";
    }
    
    echo "Classes:\n";
    foreach ($classes as $class) {
        $status = class_exists($class) ? '✓' : '✗';
        echo "  {$class}: {$status}\n";
    }
    
    echo "Shortcodes: " . (empty($slm_shortcodes) ? 'NONE' : implode(', ', $slm_shortcodes)) . "\n";
    echo "Loaded Systems: " . (defined('SLM_LOADED_SYSTEMS') ? SLM_LOADED_SYSTEMS : 'None') . "\n";
    echo "-->\n";
}
add_action('wp_footer', 'slm_output_diagnostic', 9999);

// =============================================================================
// END OF FUNCTIONS.PHP
// =============================================================================

/**
 * Add rewrite rules for client onboarding URLs
 */
function slm_onboarding_rewrite_rules() {
    add_rewrite_rule(
        '^client-onboarding/([a-f0-9]{64})/?$',
        'index.php?pagename=client-onboarding&slm_token=$matches[1]',
        'top'
    );
}
add_action('init', 'slm_onboarding_rewrite_rules');

/**
 * Register slm_token as a query var
 */
function slm_onboarding_query_vars($vars) {
    $vars[] = 'slm_token';
    return $vars;
}
add_filter('query_vars', 'slm_onboarding_query_vars');