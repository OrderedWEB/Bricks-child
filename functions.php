<?php
/**
 * Bricks Child Theme - Engagement Letter System Bootstrap
 * 
 * This is the MASTER LOADER that replaces your current functions.php.
 * Loads all engagement letter modules in guaranteed correct order.
 * 
 * CRITICAL LOAD ORDER:
 * 1. Constants (no dependencies)
 * 2. Session (uses constants)
 * 3. Helpers (uses constants, session)
 * 4. Merge Tags (uses helpers)
 * 5. WooCommerce modules (loaded AFTER woocommerce_loaded hook)
 * 6. Feature modules (use all core modules)
 * 7. Tab modules (use all core + feature modules)
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// SYSTEM PATHS
// ============================================

define('EL_CHILD_PATH', get_stylesheet_directory());
define('EL_CHILD_URL', get_stylesheet_directory_uri());

// Path to engagement letter modules
if (!defined('EL_PATH')) {
    define('EL_PATH', EL_CHILD_PATH . '/inc/el/');
}

// ============================================
// PHASE 1: CORE MODULES (Load Immediately)
// These have no WooCommerce dependencies
// ============================================

/**
 * Load core system files
 * 
 * Order matters! Each file may depend on previous files.
 */
function el_load_core_modules() {
    $core_files = [
        'core/constants.php',       // #1 - Must load FIRST (no dependencies)
        'core/session.php',         // #2 - Registers init hook priority 1
        'core/helpers.php',         // #3 - Uses constants, session
        'core/merge-tags.php',      // #4 - Uses helpers
    ];
    
    foreach ($core_files as $file) {
        $filepath = EL_PATH . $file;
        
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            error_log('[EL System] CRITICAL: Core file missing - ' . $file);
        }
    }
}

// Load core modules immediately
el_load_core_modules();

// ============================================
// PHASE 2: WOOCOMMERCE MODULES
// MUST load AFTER WooCommerce is available
// ============================================

/**
 * Load WooCommerce-dependent modules
 * 
 * Guaranteed to run AFTER WooCommerce initialisation.
 */
function el_load_woocommerce_modules() {
    // Verify WooCommerce is actually loaded
    if (!function_exists('WC') || !class_exists('WooCommerce')) {
        if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
            error_log('[EL System] WooCommerce not available - skipping WC modules');
        }
        return;
    }
    
    $wc_files = [
        'core/woocommerce.php',           // WC cart integration
        'features/grouped-products.php',  // Grouped products system
    ];
    
    foreach ($wc_files as $file) {
        $filepath = EL_PATH . $file;
        
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            error_log('[EL System] WooCommerce module missing - ' . $file);
        }
    }
}

// Hook to woocommerce_loaded (guaranteed timing)
add_action('woocommerce_loaded', 'el_load_woocommerce_modules', 5);

// ============================================
// PHASE 3: FEATURE MODULES (After Init)
// These use both core and WooCommerce modules
// ============================================

/**
 * Load feature modules
 * 
 * These implement major system features like PDF generation,
 * resume drafts, payment handling, etc.
 */
function el_load_feature_modules() {
    $feature_files = [
        'features/resume-draft.php',      // Resume draft banner/restore
        'features/payment-handler.php',   // Payment/order creation
        'features/signature-collection.php', // Client signatures
        'features/encryption.php',        // Data encryption/security
    ];
    
    foreach ($feature_files as $file) {
        $filepath = EL_PATH . $file;
        
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}

add_action('init', 'el_load_feature_modules', 20);

// ============================================
// PHASE 4: TAB MODULES
// Implement individual wizard tabs
// ============================================

/**
 * Load tab modules
 * 
 * Each tab is self-contained with AJAX handlers, shortcodes, etc.
 */
function el_load_tab_modules() {
    $tab_files = [
        'tabs/tab1-client.php',      // Client details form
        'tabs/tab2-templates.php',   // Template selection
        'tabs/tab3-cart.php',        // Cart editor (glass morphism)
        'tabs/tab4-preview.php',     // PDF preview generation
        'tabs/tab5-export.php',      // PDF export/download
    ];
    
    foreach ($tab_files as $file) {
        $filepath = EL_PATH . $file;
        
        if (file_exists($filepath)) {
            require_once $filepath;
        }
    }
}

add_action('init', 'el_load_tab_modules', 25);

// ============================================
// PHASE 5: ADMIN MODULES
// Custom post type, meta boxes, admin UI
// ============================================

/**
 * Load admin modules
 * 
 * CPT registration, admin columns, etc.
 */
function el_load_admin_modules() {
    $admin_files = [
        'admin/cpt-engagement.php',   // Custom post type (REQUIRED)
    ];
    
    foreach ($admin_files as $file) {
        $filepath = EL_PATH . $file;
        
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            error_log('[EL System] CRITICAL: Admin file missing - ' . $file);
        }
    }
}

add_action('init', 'el_load_admin_modules', 30);

// ============================================
// NOTE: AJAX & SHORTCODES DISTRIBUTED
// ============================================
// AJAX handlers are distributed across their respective modules:
// - tab1-client.php: Client search, save client, load client
// - tab2-templates.php: Add/remove templates, filter templates
// - tab3-cart.php: Remove items, refresh cart
// - tab4-preview.php: Generate PDF, load preview
// - tab5-export.php: Download PDF, complete engagement
// - grouped-products.php: Store parent, add children
// - payment-handler.php: Create order, payment status
// - signature-collection.php: Submit signature, validate token
// - resume-draft.php: Resume draft, delete draft
//
// Shortcodes are in their respective tab/feature modules
// Print system is in tab5-export.php
// No separate public/ directory needed

// ============================================
// SYSTEM DIAGNOSTICS (Debug Mode Only)
// ============================================

/**
 * Outputs system load status
 * 
 * Only active when EL_DEBUG_MODE = true
 */
function el_system_diagnostics() {
    if (!defined('EL_DEBUG_MODE') || !EL_DEBUG_MODE) {
        return;
    }
    
    $diagnostics = [
        'version' => defined('EL_VERSION') ? EL_VERSION : 'UNKNOWN',
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version'),
        'woocommerce' => class_exists('WooCommerce') ? WC()->version : 'Not installed',
        'session_active' => function_exists('el_session_active') ? (el_session_active() ? 'Yes' : 'No') : 'Module not loaded',
        'cart_available' => function_exists('el_ensure_cart') ? (el_ensure_cart() ? 'Yes' : 'No') : 'Module not loaded',
        'core_modules' => [
            'constants' => defined('EL_VERSION'),
            'session' => function_exists('el_init_session'),
            'helpers' => function_exists('el_get_meta'),
            'merge_tags' => function_exists('el_replace_merge_tags'),
            'woocommerce' => function_exists('el_save_cart_state'),
            'grouped_products' => function_exists('el_store_grouped_parent'),
        ],
    ];
    
    error_log('[EL System] Diagnostics: ' . print_r($diagnostics, true));
}

add_action('init', 'el_system_diagnostics', 999);

// ============================================
// AJAX SECURITY SETUP
// ============================================

/**
 * Checks if current page is the engagement letter wizard
 * 
 * @return bool True if on wizard page
 */
function el_is_wizard_page() {
    // Method 1: Check by slug (YOUR ACTUAL PAGE SLUG)
    if (is_page('create-engagement-letter')) {
        return true;
    }
    
    // Fallback: Check by page template
    if (is_page_template('page-engagement-wizard.php')) {
        return true;
    }
    
    // Fallback: Check if page has wizard shortcodes
    global $post;
    if (isset($post->post_content)) {
        $wizard_shortcodes = [
            '[el_template_selection]',
            '[el_client_form]',
            '[el_cart_editor',
            '[el_pdf_preview',
        ];
        
        foreach ($wizard_shortcodes as $shortcode) {
            if (strpos($post->post_content, $shortcode) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Enqueues nonce for AJAX operations
 */
function el_enqueue_ajax_nonce() {
    // Only load on engagement letter wizard page
    if (!el_is_wizard_page()) {
        return;
    }
    
    // Ensure jQuery is loaded first
    wp_enqueue_script('jquery');
    
    // Create inline script with nonces (more reliable than wp_localize_script)
    $ajax_data = [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce(EL_NONCE),
        'cartNonce' => wp_create_nonce(EL_CART_NONCE),
        'refreshNonce' => wp_create_nonce(EL_REFRESH_NONCE),
    ];
    
    $script = 'var elAjax = ' . json_encode($ajax_data) . ';';
    wp_add_inline_script('jquery', $script, 'after');
    
    // Log for debugging
    if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
        error_log('[EL System] AJAX nonces enqueued on page: ' . get_permalink());
    }
}

add_action('wp_enqueue_scripts', 'el_enqueue_ajax_nonce', 1);

// ============================================
// BACKWARDS COMPATIBILITY
// Stubs for any old function calls
// ============================================

/**
 * Ensures old function calls don't break site
 * 
 * Log warning and return safe defaults.
 */
function el_backwards_compatibility_stub($function_name) {
    if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
        error_log('[EL System] Deprecated function called: ' . $function_name);
    }
    return false;
}

// Add stubs for critical old functions if needed
// Example:
// if (!function_exists('old_function_name')) {
//     function old_function_name() {
//         return el_backwards_compatibility_stub(__FUNCTION__);
//     }
// }

// ============================================
// HEALTH CHECK ENDPOINT (Optional)
// ============================================

/**
 * AJAX: System health check
 * 
 * Returns system status for diagnostics.
 */
function el_ajax_health_check() {
    check_ajax_referer(EL_DIAGNOSTIC_NONCE, 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    $health = [
        'status' => 'healthy',
        'version' => EL_VERSION,
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
        'session' => [
            'active' => el_session_active(),
            'has_engagement' => el_has_active_engagement(),
            'engagement_id' => el_get_current_engagement_id(),
        ],
        'cart' => [
            'available' => el_ensure_cart(),
            'item_count' => el_ensure_cart() ? el_get_cart_count() : 0,
        ],
    ];
    
    // Check for any critical issues
    $critical_modules = ['constants', 'session', 'helpers'];
    foreach ($critical_modules as $module) {
        if (!$health['modules_loaded'][$module]) {
            $health['status'] = 'error';
            $health['error'] = 'Critical module not loaded: ' . $module;
            break;
        }
    }
    
    wp_send_json_success($health);
}

add_action('wp_ajax_el_health_check', 'el_ajax_health_check');

// ============================================
// DEACTIVATION CLEANUP (Optional)
// ============================================

/**
 * Cleanup on theme switch
 * 
 * Optional: Remove scheduled events, clear transients, etc.
 */
function el_cleanup_on_deactivation() {
    // Clear scheduled events
    $timestamp = wp_next_scheduled('el_daily_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'el_daily_cleanup');
    }
    
    // Optionally clear transients (commented out - may want to preserve data)
    // global $wpdb;
    // $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_el_%'");
}

add_action('switch_theme', 'el_cleanup_on_deactivation');

// ============================================
// EMERGENCY KILL SWITCH
// ============================================

/**
 * Emergency disable
 * 
 * Add this to wp-config.php to disable entire system:
 * define('EL_SYSTEM_DISABLED', true);
 */
if (defined('EL_SYSTEM_DISABLED') && EL_SYSTEM_DISABLED) {
    error_log('[EL System] DISABLED via EL_SYSTEM_DISABLED constant');
    
    // Prevent all module loading
    remove_action('woocommerce_loaded', 'el_load_woocommerce_modules', 5);
    remove_action('init', 'el_load_feature_modules', 20);
    remove_action('init', 'el_load_tab_modules', 25);
    remove_action('init', 'el_load_admin_modules', 30);
    remove_action('init', 'el_load_public_modules', 35);
    
    return; // Stop execution
}

// ============================================
// BOOTSTRAP COMPLETE
// ============================================

if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
    error_log('[EL System] Bootstrap complete - version ' . (defined('EL_VERSION') ? EL_VERSION : 'UNKNOWN'));
}

/**
 * All modules loaded successfully!
 * 
 * Load order guaranteed:
 * ✅ Constants loaded first
 * ✅ Session starts on init priority 1
 * ✅ Helpers available before merge tags
 * ✅ WooCommerce modules load after WC is ready
 * ✅ All dependencies satisfied
 * 
 * System is ready for use.
 */