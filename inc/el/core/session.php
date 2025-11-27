<?php
/**
 * Engagement Letter System - Session Management
 * 
 * Handles PHP session initialisation and management with security best practices.
 * CRITICAL: This must load before any module that uses session data.
 * 
 * LOAD ORDER: #2 (after constants.php only)
 * DEPENDENCIES: constants.php
 * USED BY: All tabs, cart system, PDF generation, grouped products
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// SESSION INITIALISATION
// Priority 1 = loads before any other init hooks
// ============================================

/**
 * Initialises PHP session early in WordPress lifecycle
 * 
 * Runs on 'init' hook with priority 1 to ensure session starts before
 * any output or other plugins attempt to use session data.
 * 
 * Security: Uses secure cookie parameters, httponly, and SameSite protection.
 */
function el_init_session() {
    // Only start session if not already started and headers not sent
    if (!session_id() && !headers_sent()) {
        
        // Configure secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => EL_SESSION_LIFETIME,          // 24 hours
            'path' => COOKIEPATH,                       // WordPress cookie path
            'domain' => COOKIE_DOMAIN,                  // WordPress cookie domain
            'secure' => is_ssl(),                       // HTTPS only if SSL active
            'httponly' => true,                         // Prevent JavaScript access
            'samesite' => 'Lax'                         // CSRF protection
        ]);
        
        // Start the session
        session_start();
        
        // Log session start if debugging
        if (EL_DEBUG_MODE) {
            el_log('Session started successfully (ID: ' . session_id() . ')', 'info');
        }
        
        return true;
    }
    
    // Session already active or headers sent
    if (session_id() && EL_DEBUG_MODE) {
        el_log('Session already active (ID: ' . session_id() . ')', 'info');
    }
    
    if (headers_sent() && EL_DEBUG_MODE) {
        el_log('Cannot start session - headers already sent', 'warning');
    }
    
    return false;
}

// Hook to init with priority 1 (very early, before most plugins)
add_action('init', 'el_init_session', 1);

// ============================================
// SESSION HELPER FUNCTIONS
// Safe wrappers that handle all session operations
// ============================================

/**
 * Checks if session is currently active
 * 
 * @return bool True if session is active and usable
 */
function el_session_active() {
    return session_id() !== '';
}

/**
 * Retrieves value from session with fallback
 * 
 * @param string $key     Session key (use EL_SESSION_* constants)
 * @param mixed  $default Default value if key not found
 * @return mixed Session value or default
 */
function el_get_session($key, $default = null) {
    // Ensure session is started (defensive programming)
    if (!el_session_active()) {
        if (EL_DEBUG_MODE) {
            el_log('Attempted to get session key "' . $key . '" but session not active', 'warning');
        }
        return $default;
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Stores value in session
 * 
 * @param string $key   Session key (use EL_SESSION_* constants)
 * @param mixed  $value Value to store
 * @return bool True if value stored successfully
 */
function el_set_session($key, $value) {
    // Ensure session is started
    if (!el_session_active()) {
        if (EL_DEBUG_MODE) {
            el_log('Attempted to set session key "' . $key . '" but session not active', 'error');
        }
        return false;
    }
    
    $_SESSION[$key] = $value;
    
    if (EL_DEBUG_MODE) {
        el_log('Session key "' . $key . '" set successfully', 'info');
    }
    
    return true;
}

/**
 * Removes value from session
 * 
 * @param string $key Session key to remove
 * @return bool True if value was removed
 */
function el_unset_session($key) {
    if (!el_session_active()) {
        return false;
    }
    
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        
        if (EL_DEBUG_MODE) {
            el_log('Session key "' . $key . '" removed', 'info');
        }
        
        return true;
    }
    
    return false;
}

/**
 * Clears all engagement letter session data
 * 
 * Removes all EL-specific session keys but preserves other session data
 * (e.g., WordPress authentication, other plugins).
 * 
 * @return int Number of keys cleared
 */
function el_clear_session_data() {
    if (!el_session_active()) {
        return 0;
    }
    
    // List of all EL session keys to clear
    $keys_to_clear = [
        EL_SESSION_CLIENT_NAME,
        EL_SESSION_CLIENT_EMAIL,
        EL_SESSION_CLIENT_ID,
        EL_SESSION_FORM_DATA,
        EL_SESSION_PDF_REF,
        EL_SESSION_ENGAGEMENT_ID,
        EL_SESSION_SELECTED_TEMPLATE,
        EL_SESSION_NO_CLIENT_MODE,
        EL_SESSION_GROUPED_PARENT_ID,
        EL_SESSION_GROUPED_PARENT_NAME,
        EL_SESSION_GROUPED_PARENT_DATA,
    ];
    
    $cleared = 0;
    
    foreach ($keys_to_clear as $key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            $cleared++;
        }
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Cleared ' . $cleared . ' session keys', 'info');
    }
    
    return $cleared;
}

/**
 * Retrieves all engagement letter session data
 * 
 * @return array Associative array of all EL session data
 */
function el_get_all_session_data() {
    if (!el_session_active()) {
        return [];
    }
    
    return [
        'client_name' => el_get_session(EL_SESSION_CLIENT_NAME),
        'client_email' => el_get_session(EL_SESSION_CLIENT_EMAIL),
        'client_id' => el_get_session(EL_SESSION_CLIENT_ID),
        'form_data' => el_get_session(EL_SESSION_FORM_DATA),
        'pdf_reference' => el_get_session(EL_SESSION_PDF_REF),
        'engagement_id' => el_get_session(EL_SESSION_ENGAGEMENT_ID),
        'selected_template' => el_get_session(EL_SESSION_SELECTED_TEMPLATE),
        'no_client_mode' => el_get_session(EL_SESSION_NO_CLIENT_MODE),
        'grouped_parent_id' => el_get_session(EL_SESSION_GROUPED_PARENT_ID),
        'grouped_parent_name' => el_get_session(EL_SESSION_GROUPED_PARENT_NAME),
        'grouped_parent_data' => el_get_session(EL_SESSION_GROUPED_PARENT_DATA),
    ];
}

/**
 * Checks if an engagement is currently in progress
 * 
 * @return bool True if user has active engagement in session
 */
function el_has_active_engagement() {
    return el_session_active() && !empty(el_get_session(EL_SESSION_ENGAGEMENT_ID));
}

/**
 * Retrieves current engagement letter ID from session
 * 
 * @return int|null Post ID or null if none active
 */
function el_get_current_engagement_id() {
    $id = el_get_session(EL_SESSION_ENGAGEMENT_ID);
    return $id ? intval($id) : null;
}

/**
 * Sets current engagement letter ID in session
 * 
 * @param int $engagement_id Post ID
 * @return bool Success status
 */
function el_set_current_engagement_id($engagement_id) {
    return el_set_session(EL_SESSION_ENGAGEMENT_ID, intval($engagement_id));
}

// ============================================
// SESSION CLEANUP HOOKS
// ============================================

/**
 * Cleans up session data on user logout
 * 
 * Prevents session data leaking between users on shared computers.
 */
function el_cleanup_on_logout() {
    if (el_session_active()) {
        el_clear_session_data();
        
        // Optionally destroy entire session (more secure)
        if (defined('EL_DESTROY_SESSION_ON_LOGOUT') && EL_DESTROY_SESSION_ON_LOGOUT) {
            session_destroy();
        }
        
        if (EL_DEBUG_MODE) {
            el_log('Session cleaned up on logout', 'info');
        }
    }
}
add_action('wp_logout', 'el_cleanup_on_logout');

/**
 * Cleans up expired session data periodically
 * 
 * Runs once daily to remove stale engagement letter records.
 */
function el_cleanup_expired_sessions() {
    global $wpdb;
    
    // Find draft engagement letters older than 30 days
    $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    $expired_posts = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = %s 
         AND post_status = 'publish'
         AND post_modified < %s",
        EL_CPT_ENGAGEMENT,
        $thirty_days_ago
    ));
    
    if (!empty($expired_posts)) {
        foreach ($expired_posts as $post_id) {
            $status = get_post_meta($post_id, EL_META_STATUS, true);
            
            // Only delete drafts (not signed or paid)
            if ($status === EL_STATUS_DRAFT) {
                wp_trash_post($post_id);
            }
        }
        
        if (EL_DEBUG_MODE) {
            el_log('Cleaned up ' . count($expired_posts) . ' expired draft engagements', 'info');
        }
    }
}

// Schedule daily cleanup
if (!wp_next_scheduled('el_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'el_daily_cleanup');
}
add_action('el_daily_cleanup', 'el_cleanup_expired_sessions');

// ============================================
// AJAX: Clear Session (for "Start Over")
// ============================================

/**
 * AJAX handler: Clears all session data
 * 
 * Used by "Start Over" button to reset wizard completely.
 */
function el_ajax_clear_session() {
    // Verify security nonce
    if (!check_ajax_referer(EL_NONCE, 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $cleared = el_clear_session_data();
    
    wp_send_json_success([
        'message' => 'Session cleared',
        'keys_cleared' => $cleared
    ]);
}
add_action('wp_ajax_el_clear_session', 'el_ajax_clear_session');
add_action('wp_ajax_nopriv_el_clear_session', 'el_ajax_clear_session');

// ============================================
// DIAGNOSTICS (Debug Mode Only)
// ============================================

/**
 * Outputs session status for debugging
 * 
 * Only available when WP_DEBUG is enabled.
 * 
 * @return array Session diagnostic information
 */
function el_session_diagnostics() {
    if (!EL_DEBUG_MODE) {
        return ['error' => 'Debug mode not enabled'];
    }
    
    return [
        'session_id' => session_id() ?: 'Not active',
        'session_active' => el_session_active(),
        'engagement_active' => el_has_active_engagement(),
        'current_engagement_id' => el_get_current_engagement_id(),
        'session_data' => el_get_all_session_data(),
        'session_lifetime' => EL_SESSION_LIFETIME,
        'cookie_params' => session_get_cookie_params(),
    ];
}

/**
 * Shortcode: Display session diagnostics (admin only)
 * 
 * Usage: [el_session_debug]
 */
function el_session_debug_shortcode() {
    if (!current_user_can('manage_options')) {
        return 'Access denied';
    }
    
    $diagnostics = el_session_diagnostics();
    
    $output = '<div style="background: #f3f4f6; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 12px;">';
    $output .= '<h3>🔍 Session Diagnostics</h3>';
    $output .= '<pre>' . print_r($diagnostics, true) . '</pre>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_session_debug', 'el_session_debug_shortcode');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Session module loaded successfully', 'info');
}