<?php
/**
 * Engagement Letter System - Resume Draft
 * 
 * Handles "resume where you left off" functionality:
 * - Detects incomplete engagement letters
 * - Shows banner with resume option
 * - Restores complete state (session, cart, tab position)
 * - Manages draft lifecycle
 * 
 * LOAD ORDER: Feature module (after core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php, woocommerce.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// DRAFT DETECTION
// ============================================

/**
 * Checks if user has incomplete engagement letters
 * 
 * Looks for draft engagements modified in last 30 days.
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return array Array of draft engagement post IDs
 */
function el_get_user_drafts($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return [];
    }
    
    global $wpdb;
    
    // Find draft engagements modified in last 30 days
    $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    $query = $wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
         WHERE p.post_type = %s
         AND p.post_status = 'publish'
         AND p.post_author = %d
         AND p.post_modified > %s
         AND pm.meta_key = %s
         AND pm.meta_value = %s
         ORDER BY p.post_modified DESC",
        EL_CPT_ENGAGEMENT,
        $user_id,
        $thirty_days_ago,
        EL_META_STATUS,
        EL_STATUS_DRAFT
    );
    
    $draft_ids = $wpdb->get_col($query);
    
    return $draft_ids ?: [];
}

/**
 * Retrieves most recent draft for user
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return int|false Draft post ID or false if none
 */
function el_get_latest_draft($user_id = null) {
    $drafts = el_get_user_drafts($user_id);
    
    return !empty($drafts) ? $drafts[0] : false;
}

/**
 * Checks if user has any incomplete drafts
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return bool True if user has drafts
 */
function el_has_drafts($user_id = null) {
    return !empty(el_get_user_drafts($user_id));
}

/**
 * Checks if engagement is resumable
 * 
 * @param int $engagement_id Engagement post ID
 * @return bool True if can be resumed
 */
function el_is_resumable($engagement_id) {
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    // Check status is draft
    $status = el_get_meta($engagement_id, 'status');
    if ($status !== EL_STATUS_DRAFT) {
        return false;
    }
    
    // Check not too old (30 days)
    $modified = get_post_modified_time('U', true, $engagement_id);
    $age_days = (time() - $modified) / DAY_IN_SECONDS;
    
    if ($age_days > 30) {
        return false;
    }
    
    return true;
}

// ============================================
// DRAFT RESTORATION
// ============================================

/**
 * Restores complete engagement letter state
 * 
 * Restores:
 * - Session data (client info, form data, engagement ID)
 * - WooCommerce cart contents
 * - Current tab position
 * - Grouped parent (if applicable)
 * 
 * @param int $engagement_id Engagement post ID
 * @return bool True if restored successfully
 */
function el_resume_draft($engagement_id) {
    if (!el_is_resumable($engagement_id)) {
        if (EL_DEBUG_MODE) {
            el_log('Cannot resume engagement ' . $engagement_id . ' - not resumable', 'error');
        }
        return false;
    }
    
    // Get complete engagement data
    $engagement = el_get_engagement_letter($engagement_id);
    
    if (!$engagement) {
        return false;
    }
    
    // 1. Restore session data
    el_restore_session_from_engagement($engagement);
    
    // 2. Restore cart
    if (function_exists('el_restore_cart_state')) {
        el_restore_cart_state($engagement_id);
    }
    
    // 3. Restore grouped parent if applicable
    if (function_exists('el_restore_grouped_parent')) {
        el_restore_grouped_parent($engagement_id);
    }
    
    // 4. Update last active timestamp
    el_set_meta($engagement_id, 'last_active', current_time('mysql'));
    
    if (EL_DEBUG_MODE) {
        el_log('Successfully resumed engagement ' . $engagement_id, 'info');
    }
    
    return true;
}

/**
 * Restores session data from engagement letter
 * 
 * @param array $engagement Engagement data array
 * @return bool True if restored successfully
 */
function el_restore_session_from_engagement($engagement) {
    if (!el_session_active()) {
        return false;
    }
    
    // Set engagement ID
    el_set_session(EL_SESSION_ENGAGEMENT_ID, $engagement['ID']);
    
    // Restore form data
    $form_data = $engagement['form_data'];
    if (!empty($form_data)) {
        el_set_session(EL_SESSION_FORM_DATA, $form_data);
        el_set_session(EL_SESSION_CLIENT_NAME, trim(
            ($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')
        ));
        el_set_session(EL_SESSION_CLIENT_EMAIL, $form_data['email'] ?? '');
    }
    
    // Restore client ID if set
    if (!empty($engagement['client_id'])) {
        el_set_session(EL_SESSION_CLIENT_ID, $engagement['client_id']);
    }
    
    // Restore PDF reference if set
    $reference = $engagement['reference'];
    if ($reference) {
        el_set_session(EL_SESSION_PDF_REF, $reference);
    }
    
    // Restore template selection
    if (!empty($engagement['template_id'])) {
        el_set_session(EL_SESSION_SELECTED_TEMPLATE, $engagement['template_id']);
    }
    
    return true;
}

/**
 * Retrieves draft summary for display
 * 
 * @param int $engagement_id Engagement post ID
 * @return array Summary data
 */
function el_get_draft_summary($engagement_id) {
    $engagement = el_get_engagement_letter($engagement_id);
    
    if (!$engagement) {
        return [];
    }
    
    $form_data = $engagement['form_data'];
    
    return [
        'id' => $engagement['ID'],
        'reference' => $engagement['reference'],
        'client_name' => trim(
            ($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')
        ),
        'practice_area' => $engagement['practice_area'],
        'current_tab' => $engagement['current_tab'],
        'last_active' => $engagement['last_active'],
        'last_active_human' => el_time_ago($engagement['last_active']),
        'created_date' => $engagement['created_date'],
        'cart_item_count' => !empty($engagement['cart_contents']['items']) ? 
            count($engagement['cart_contents']['items']) : 0,
    ];
}

// ============================================
// BANNER DISPLAY
// ============================================

/**
 * Displays resume draft banner
 * 
 * Shows at top of wizard page when drafts available.
 * 
 * @return string HTML banner
 */
function el_render_resume_banner() {
    // Only show to logged-in users
    if (!is_user_logged_in()) {
        return '';
    }
    
    // Don't show if already resuming an engagement
    if (el_has_active_engagement()) {
        return '';
    }
    
    $latest_draft = el_get_latest_draft();
    
    if (!$latest_draft) {
        return '';
    }
    
    $summary = el_get_draft_summary($latest_draft);
    
    if (empty($summary)) {
        return '';
    }
    
    // Build banner HTML
    $banner = '<div class="el-resume-banner" id="elResumeBanner" style="
        background: linear-gradient(135deg, ' . EL_COLOR_PRIMARY . ' 0%, ' . EL_COLOR_DARK . ' 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
    ">';
    
    $banner .= '<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">';
    
    // Left side - info
    $banner .= '<div class="el-resume-info">';
    $banner .= '<h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">📋 Continue Your Engagement Letter</h3>';
    
    if (!empty($summary['client_name'])) {
        $banner .= '<p style="margin: 5px 0; opacity: 0.95;"><strong>Client:</strong> ' . esc_html($summary['client_name']) . '</p>';
    }
    
    if (!empty($summary['practice_area'])) {
        $banner .= '<p style="margin: 5px 0; opacity: 0.95;"><strong>Practice Area:</strong> ' . esc_html($summary['practice_area']) . '</p>';
    }
    
    $banner .= '<p style="margin: 5px 0; opacity: 0.9; font-size: 13px;">Last updated ' . esc_html($summary['last_active_human']) . '</p>';
    
    if ($summary['cart_item_count'] > 0) {
        $banner .= '<p style="margin: 5px 0; opacity: 0.9; font-size: 13px;">Cart: ' . $summary['cart_item_count'] . ' ' . _n('item', 'items', $summary['cart_item_count']) . '</p>';
    }
    
    $banner .= '</div>';
    
    // Right side - actions
    $banner .= '<div class="el-resume-actions" style="display: flex; gap: 12px; align-items: center;">';
    
    $banner .= '<button type="button" class="el-resume-button" data-engagement-id="' . esc_attr($latest_draft) . '" style="
        background: white;
        color: ' . EL_COLOR_NAVY . ';
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    ">Resume Draft</button>';
    
    $banner .= '<button type="button" class="el-dismiss-banner" style="
        background: transparent;
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    ">Start Fresh</button>';
    
    $banner .= '</div>';
    
    $banner .= '</div>'; // Close flex container
    
    $banner .= '</div>'; // Close banner
    
    return $banner;
}

/**
 * Shortcode: Resume draft banner
 * 
 * Usage: [el_resume_banner]
 */
function el_resume_banner_shortcode() {
    return el_render_resume_banner();
}
add_shortcode('el_resume_banner', 'el_resume_banner_shortcode');

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Resume draft engagement
 */
function el_ajax_resume_draft() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'Invalid engagement ID']);
    }
    
    // Verify user owns this engagement
    $post = get_post($engagement_id);
    if (!$post || $post->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    // Resume the draft
    $result = el_resume_draft($engagement_id);
    
    if ($result) {
        $summary = el_get_draft_summary($engagement_id);
        
        wp_send_json_success([
            'message' => 'Draft resumed',
            'engagement_id' => $engagement_id,
            'current_tab' => $summary['current_tab'] ?? 1,
            'redirect_tab' => el_get_tab_selector($summary['current_tab'] ?? 1),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to resume draft']);
    }
}
add_action('wp_ajax_' . EL_AJAX_RESUME_DRAFT, 'el_ajax_resume_draft');

/**
 * AJAX: Dismiss resume banner
 */
function el_ajax_dismiss_resume_banner() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    // Store dismissal in user meta (prevents showing again this session)
    $user_id = get_current_user_id();
    
    if ($user_id) {
        update_user_meta($user_id, '_el_banner_dismissed', time());
    }
    
    wp_send_json_success(['message' => 'Banner dismissed']);
}
add_action('wp_ajax_el_dismiss_resume_banner', 'el_ajax_dismiss_resume_banner');

/**
 * AJAX: Get user drafts list
 */
function el_ajax_get_user_drafts() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $draft_ids = el_get_user_drafts();
    
    $drafts = [];
    
    foreach ($draft_ids as $draft_id) {
        $summary = el_get_draft_summary($draft_id);
        if (!empty($summary)) {
            $drafts[] = $summary;
        }
    }
    
    wp_send_json_success([
        'drafts' => $drafts,
        'count' => count($drafts),
    ]);
}
add_action('wp_ajax_el_get_user_drafts', 'el_ajax_get_user_drafts');

/**
 * AJAX: Delete draft engagement
 */
function el_ajax_delete_draft() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'Invalid engagement ID']);
    }
    
    // Verify user owns this engagement
    $post = get_post($engagement_id);
    if (!$post || $post->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    // Verify it's a draft
    $status = el_get_meta($engagement_id, 'status');
    if ($status !== EL_STATUS_DRAFT) {
        wp_send_json_error(['message' => 'Cannot delete signed engagement letters']);
    }
    
    // Move to trash
    $result = wp_trash_post($engagement_id);
    
    if ($result) {
        wp_send_json_success(['message' => 'Draft deleted']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete draft']);
    }
}
add_action('wp_ajax_el_delete_draft', 'el_ajax_delete_draft');

// ============================================
// DRAFT MANAGEMENT UI
// ============================================

/**
 * Renders draft management table (for admin/lawyer portal)
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return string HTML table
 */
function el_render_drafts_table($user_id = null) {
    $draft_ids = el_get_user_drafts($user_id);
    
    if (empty($draft_ids)) {
        return '<p class="el-no-drafts">No draft engagement letters found.</p>';
    }
    
    $output = '<div class="el-drafts-table-wrapper">';
    $output .= '<table class="el-drafts-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
    
    // Table header
    $output .= '<thead>';
    $output .= '<tr style="background: ' . EL_COLOR_BG_LIGHT . '; border-bottom: 2px solid ' . EL_COLOR_PRIMARY . ';">';
    $output .= '<th style="padding: 12px; text-align: left;">Reference</th>';
    $output .= '<th style="padding: 12px; text-align: left;">Client</th>';
    $output .= '<th style="padding: 12px; text-align: left;">Practice Area</th>';
    $output .= '<th style="padding: 12px; text-align: left;">Last Active</th>';
    $output .= '<th style="padding: 12px; text-align: center;">Actions</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    
    // Table body
    $output .= '<tbody>';
    
    foreach ($draft_ids as $draft_id) {
        $summary = el_get_draft_summary($draft_id);
        
        if (empty($summary)) {
            continue;
        }
        
        $output .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
        
        $output .= '<td style="padding: 12px;">' . esc_html($summary['reference']) . '</td>';
        $output .= '<td style="padding: 12px;">' . esc_html($summary['client_name'] ?: '—') . '</td>';
        $output .= '<td style="padding: 12px;">' . esc_html($summary['practice_area'] ?: '—') . '</td>';
        $output .= '<td style="padding: 12px;">' . esc_html($summary['last_active_human']) . '</td>';
        
        $output .= '<td style="padding: 12px; text-align: center;">';
        $output .= '<button type="button" class="el-resume-button" data-engagement-id="' . esc_attr($draft_id) . '" style="margin-right: 8px;">Resume</button>';
        $output .= '<button type="button" class="el-delete-draft" data-engagement-id="' . esc_attr($draft_id) . '">Delete</button>';
        $output .= '</td>';
        
        $output .= '</tr>';
    }
    
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Shortcode: Drafts table
 * 
 * Usage: [el_drafts_table]
 */
function el_drafts_table_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your drafts.</p>';
    }
    
    return el_render_drafts_table();
}
add_shortcode('el_drafts_table', 'el_drafts_table_shortcode');

// ============================================
// AUTO-SAVE FUNCTIONALITY
// ============================================

/**
 * Auto-saves current tab position
 * 
 * Called via AJAX when user navigates between tabs.
 * 
 * @param int $engagement_id Engagement post ID
 * @param int $tab_number    Current tab (1-5)
 * @return bool True if saved
 */
function el_save_current_tab($engagement_id, $tab_number) {
    return el_set_meta($engagement_id, 'current_tab', intval($tab_number));
}

/**
 * AJAX: Save current tab position
 */
function el_ajax_save_current_tab() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = el_get_current_engagement_id();
    $tab_number = intval($_POST['tab_number'] ?? 1);
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'No active engagement']);
    }
    
    $result = el_save_current_tab($engagement_id, $tab_number);
    
    if ($result) {
        wp_send_json_success(['message' => 'Tab position saved']);
    } else {
        wp_send_json_error(['message' => 'Failed to save tab position']);
    }
}
add_action('wp_ajax_el_save_current_tab', 'el_ajax_save_current_tab');

// ============================================
// JAVASCRIPT ENQUEUE
// ============================================

/**
 * Enqueues resume draft JavaScript
 */
function el_enqueue_resume_draft_script() {
    if (!is_page('engagement-letter-wizard')) {
        return;
    }
    
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            // Resume draft button click
            $(document).on("click", ".el-resume-button", function() {
                var engagementId = $(this).data("engagement-id");
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: "POST",
                    data: {
                        action: "' . EL_AJAX_RESUME_DRAFT . '",
                        nonce: elAjax.nonce,
                        engagement_id: engagementId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Hide banner
                            $("#elResumeBanner").fadeOut();
                            
                            // Navigate to appropriate tab
                            if (response.data.redirect_tab) {
                                $(response.data.redirect_tab).click();
                            }
                            
                            // Refresh page to load restored state
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        }
                    }
                });
            });
            
            // Dismiss banner button
            $(document).on("click", ".el-dismiss-banner", function() {
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: "POST",
                    data: {
                        action: "el_dismiss_resume_banner",
                        nonce: elAjax.nonce
                    },
                    success: function() {
                        $("#elResumeBanner").fadeOut();
                    }
                });
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'el_enqueue_resume_draft_script');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Resume draft module loaded successfully', 'info');
}