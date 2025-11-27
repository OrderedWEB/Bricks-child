<?php
/**
 * Engagement Letter System - Core Constants
 * 
 * Centralises all magic strings, nonce actions, meta keys, and system values.
 * Prevents typos and ensures consistency across all modules.
 * 
 * LOAD ORDER: Must load FIRST (before any other EL modules)
 * DEPENDENCIES: None
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// SYSTEM VERSION & PATHS
// ============================================

define('EL_VERSION', '2.0.0');

// Note: EL_PATH is defined in functions.php bootstrap (loads before this file)
// define('EL_PATH', get_stylesheet_directory() . '/inc/el/');  // Already defined

define('EL_URL', get_stylesheet_directory_uri() . '/inc/el/');

// ============================================
// NONCE ACTIONS
// Used in 23+ AJAX handlers across the system
// ============================================

define('EL_NONCE', 'el_nonce');                           // Main nonce (used by most AJAX)
define('EL_CART_NONCE', 'el_cart_update');                // Cart operations
define('EL_SIGNATURE_NONCE', 'el_signature_nonce');       // Signature collection
define('EL_REFRESH_NONCE', 'el_refresh');                 // Cart refresh operations
define('EL_DIAGNOSTIC_NONCE', 'el_diagnostic');           // System diagnostics
define('EL_START_OVER_NONCE', 'el_start_over');           // Reset wizard
define('EL_WIZARD_NONCE', 'el_wizard_nonce');             // Wizard navigation
define('EL_CLIENT_SEARCH_NONCE', 'el_client_search_nonce'); // Client search
define('EL_HEARTBEAT_NONCE', 'el_heartbeat');             // Viewing heartbeat

// ============================================
// TRANSIENT KEY PREFIXES
// Ensures consistent key naming for temporary data storage
// ============================================

define('EL_PDF_DATA_PREFIX', 'el_pdf_data_');             // PDF preview/export data
define('EL_CART_STATE_PREFIX', 'el_saved_cart_state');    // Saved cart configurations
define('EL_VIEW_TOKEN_PREFIX', 'el_view_token_');         // Secure viewing tokens

// ============================================
// POST META KEYS
// Engagement letter custom post type fields
// ============================================

define('EL_META_REFERENCE', '_el_reference');             // Unique reference code
define('EL_META_STATUS', '_el_status');                   // draft|signed|paid
define('EL_META_CLIENT_ID', '_el_client_id');             // WordPress user ID
define('EL_META_LAWYER_ID', '_el_lawyer_id');             // Lawyer user ID
define('EL_META_TEMPLATE_ID', '_el_template_id');         // WooCommerce product ID
define('EL_META_FORM_DATA', '_el_form_data');             // Gravity Forms data array
define('EL_META_CART_CONTENTS', '_el_cart_contents');     // WooCommerce cart snapshot
define('EL_META_CURRENT_TAB', '_el_current_tab');         // Wizard position (1-5)
define('EL_META_LAST_ACTIVE', '_el_last_active');         // MySQL datetime
define('EL_META_CREATED_DATE', '_el_created_date');       // MySQL datetime
define('EL_META_MODIFIED_DATE', '_el_modified_date');     // MySQL datetime
define('EL_META_PRACTICE_AREA', '_el_practice_area');     // Practice area string
define('EL_META_SIGNED_REFERENCE', 'el_signed_reference'); // Signed PDF reference
define('EL_META_SIGNATURE_DATE', 'el_signature_date');    // Date signed
define('EL_META_SIGNED_PDF_URL', 'el_signed_pdf_url');    // Download URL
define('EL_META_SIGNED_PDF_EXPIRY', 'el_signed_pdf_expiry'); // Expiry date
define('EL_META_PDF_ATTACHMENT_ID', '_el_pdf_attachment_id'); // WP attachment ID

// ============================================
// SESSION KEYS
// PHP session variable names
// ============================================

define('EL_SESSION_CLIENT_NAME', 'el_client_name');           // Client full name
define('EL_SESSION_CLIENT_EMAIL', 'el_client_email');         // Client email
define('EL_SESSION_CLIENT_ID', 'el_current_client_id');       // WordPress user ID
define('EL_SESSION_FORM_DATA', 'el_form_data');               // Form data array
define('EL_SESSION_PDF_REF', 'el_pdf_reference');             // Current PDF reference
define('EL_SESSION_ENGAGEMENT_ID', 'el_engagement_letter_id'); // CPT post ID
define('EL_SESSION_SELECTED_TEMPLATE', 'el_selected_template'); // Product ID
define('EL_SESSION_NO_CLIENT_MODE', 'el_no_client_mode');     // Boolean flag

// Grouped products session keys
define('EL_SESSION_GROUPED_PARENT_ID', 'el_grouped_parent_id');       // Parent product ID
define('EL_SESSION_GROUPED_PARENT_NAME', 'el_grouped_parent_name');   // Parent product name
define('EL_SESSION_GROUPED_PARENT_DATA', 'el_grouped_parent_data');   // Parent ACF data array

// ============================================
// COLOUR SCHEME (Updated Blue Theme)
// Replace old green values with new blue values
// ============================================

define('EL_COLOR_PRIMARY', '#a8bcceff');        // Primary blue (was #10b981)
define('EL_COLOR_DARK', '#8fadd3ff');           // Dark blue (was #059669)
define('EL_COLOR_LIGHT', '#bad8f6ff');          // Light blue (was #34d399)
define('EL_COLOR_NAVY', '#1a3649ff');           // Navy (was #047857)
define('EL_COLOR_BG_LIGHT', '#d5e4f6ff');       // Light background (was #d1fae5)
define('EL_COLOR_BG_PALE', '#d5e4f6ff');        // Pale background (was #f0fdf4)
define('EL_COLOR_TINT', '#bad8f6ff');           // Tint (was #dcfce7)

// ============================================
// STATUS VALUES
// Engagement letter lifecycle states
// ============================================

define('EL_STATUS_DRAFT', 'draft');             // Being created
define('EL_STATUS_GENERATED', 'generated');     // PDF generated
define('EL_STATUS_SENT', 'sent');               // Sent to client
define('EL_STATUS_SIGNED', 'signed');           // Client signed
define('EL_STATUS_PAID', 'paid');               // Payment received
define('EL_STATUS_COMPLETED', 'completed');     // Fully completed

// ============================================
// POST TYPES & TAXONOMIES
// ============================================

define('EL_CPT_ENGAGEMENT', 'engagement_letter'); // Custom post type slug
define('EL_CPT_CLIENT', 'el_client');             // Client CPT (if used)

// ============================================
// TIME CONSTANTS
// ============================================

define('EL_TRANSIENT_PDF_EXPIRY', HOUR_IN_SECONDS);          // 1 hour for PDF data
define('EL_TRANSIENT_SIGNED_EXPIRY', 89 * DAY_IN_SECONDS);   // 89 days for signed PDFs
define('EL_SESSION_LIFETIME', 86400);                         // 24 hours session

// ============================================
// AJAX ACTIONS
// WordPress AJAX action names
// ============================================

define('EL_AJAX_SAVE_CLIENT', 'el_save_client_ajax');
define('EL_AJAX_ADD_TEMPLATE', 'el_add_template_to_cart');
define('EL_AJAX_GENERATE_PDF', 'el_generate_pdf_preview');
define('EL_AJAX_REFRESH_CART', 'el_refresh_cart_editor');
define('EL_AJAX_UPDATE_QTY', 'el_update_cart_quantity');
define('EL_AJAX_REMOVE_ITEM', 'el_remove_cart_item');
define('EL_AJAX_START_OVER', 'el_start_over');
define('EL_AJAX_RESUME_DRAFT', 'el_resume_draft');
define('EL_AJAX_SEARCH_CLIENTS', 'el_search_clients');
define('EL_AJAX_SUBMIT_SIGNATURE', 'el_submit_signature');
define('EL_AJAX_DOWNLOAD_PDF', 'el_download_final_pdf');
define('EL_AJAX_REFRESH_CART_SESSION', 'el_refresh_cart_session');
define('EL_AJAX_LOAD_CLIENT', 'el_load_client');
define('EL_AJAX_ENABLE_NO_CLIENT', 'el_enable_no_client_mode');
define('EL_AJAX_REMOVE_TEMPLATE', 'el_remove_template_from_cart');
define('EL_AJAX_LOAD_TEMPLATES', 'el_load_templates');
define('EL_AJAX_STORE_GROUPED_PARENT', 'el_store_grouped_parent');
define('EL_AJAX_ADD_GROUPED_CHILD', 'el_add_grouped_child');
define('EL_AJAX_CLEAR_GROUPED_PARENT', 'el_clear_grouped_parent');
define('EL_AJAX_GET_GROUPED_STATUS', 'el_get_grouped_parent_status');
define('EL_AJAX_CREATE_ORDER', 'el_create_order');
define('EL_AJAX_GET_PAYMENT_STATUS', 'el_get_payment_status');
define('EL_AJAX_VALIDATE_TOKEN', 'el_validate_view_token');
define('EL_AJAX_GET_USER_DRAFTS', 'el_get_user_drafts');
define('EL_AJAX_DELETE_DRAFT', 'el_delete_draft');
define('EL_AJAX_SAVE_CURRENT_TAB', 'el_save_current_tab');
define('EL_AJAX_DISMISS_BANNER', 'el_dismiss_resume_banner');
define('EL_AJAX_LOAD_PDF_PREVIEW', 'el_load_pdf_preview');
define('EL_AJAX_COMPLETE_ENGAGEMENT', 'el_complete_engagement');
define('EL_AJAX_CLEAR_SESSION', 'el_clear_session');
define('EL_AJAX_HEALTH_CHECK', 'el_health_check');
define('EL_AJAX_ROTATE_KEY', 'el_rotate_key');
define('EL_AJAX_ANONYMISE_DATA', 'el_anonymise_data');
define('EL_AJAX_EXPORT_PERSONAL_DATA', 'el_export_personal_data');

// ============================================
// USER META KEYS
// Lawyer/user-specific meta fields
// ============================================

define('EL_USER_META_RATE', 'lawyer_rate');         // Hourly rate (float)
define('EL_USER_META_ROLE', 'lawyer_role');         // Job title/role
define('EL_USER_META_CAN_EDIT', 'el_can_edit_documents'); // Edit permission (boolean)

// ============================================
// CART ITEM META KEYS
// WooCommerce cart item custom data
// ============================================

define('EL_CART_META_PARENT_ID', 'grouped_parent_id');       // Parent product ID
define('EL_CART_META_IS_CHILD', 'is_grouped_child');         // Boolean flag
define('EL_CART_META_PARENT_NAME', 'grouped_parent_name');   // Parent name
define('EL_CART_META_PARENT_DATA', 'parent_acf_data');       // ACF data array
define('EL_CART_META_REQUIREMENT', 'requirement');           // required|suggested

// ============================================
// ACF FIELD NAMES
// Advanced Custom Fields field keys
// ============================================

define('EL_ACF_PDF_TITLE', 'pdf_el_title');
define('EL_ACF_PDF_SUBTITLE', 'pdf_el_subtitle');
define('EL_ACF_PDF_TEXT', 'pdf_el_text');
define('EL_ACF_INTRODUCTION', 'el_introduction_texts');
define('EL_ACF_PRACTICE_AREA', 'practice_area');
define('EL_ACF_ENGAGEMENT_FEE', 'engagement_fee_due_today');
define('EL_ACF_EXPECTED_COST', 'expected_total_cost');
define('EL_ACF_FEE_STRUCTURE', 'fee_structure');
define('EL_ACF_HOURLY_RATE', 'hourly_rate');
define('EL_ACF_RATE_CAP', 'rate_cap');
define('EL_ACF_PDF_CLAUSES', 'pdf_clauses');
define('EL_ACF_PDF_FOOTER', 'pdf_footer_notes');

// Boilerplate ACF fields (options page)
define('EL_ACF_LETTERHEAD', 'boilerplate_letterhead');
define('EL_ACF_OPENING_LEFT', 'boilerplate_opening_tl');
define('EL_ACF_OPENING_RIGHT', 'boilerplate_opening_tr_copy');
define('EL_ACF_FOOTER_BOILERPLATE', 'footer_boilerplate');
define('EL_ACF_SIGNATURE_BLOCK', 'signature_block_template');
define('EL_ACF_FIRM_FOOTER', 'firm_footer');

// ============================================
// ENCRYPTION
// ============================================

define('EL_ENCRYPTION_METHOD', 'AES-256-CBC');
define('EL_ENCRYPTION_KEY_OPTION', 'el_encryption_key');

// ============================================
// PAGINATION
// ============================================

define('EL_PAGE_HEIGHT_MM', 297);               // A4 height in mm
define('EL_PAGE_WIDTH_MM', 210);                // A4 width in mm
define('EL_CHARS_PER_PAGE', 3500);              // Approximate characters per A4 page

// ============================================
// WIZARD TAB IDs
// Bricks Builder element IDs for navigation
// ============================================

define('EL_TAB_1_ID', '#brxe-kjwfkc');          // Tab 1: Client Details
define('EL_TAB_2_ID', '#brxe-caqeqv');          // Tab 2: Template Selection
define('EL_TAB_3_ID', '#brxe-mhedar');          // Tab 3: Cart Editor
define('EL_TAB_4_ID', '#brxe-ihqhkg');          // Tab 4: PDF Preview
define('EL_TAB_5_ID', '#brxe-zmmopw');          // Tab 5: PDF Export

// ============================================
// DEBUG & LOGGING
// ============================================

define('EL_DEBUG_MODE', defined('WP_DEBUG') && WP_DEBUG);
define('EL_LOG_PREFIX', '[EL System] ');

// ============================================
// HELPER FUNCTIONS FOR CONSTANTS
// ============================================

if (!function_exists('el_get_version')) {
    /**
     * Retrieves system version
     * 
     * @return string Version number
     */
    function el_get_version() {
        return EL_VERSION;
    }
}

if (!function_exists('el_is_debug')) {
    /**
     * Checks if system is in debug mode
     * 
     * @return bool True if debugging enabled
     */
    function el_is_debug() {
        return defined('EL_DEBUG_MODE') && EL_DEBUG_MODE;
    }
}

if (!function_exists('el_log')) {
    /**
     * Logs message if debug mode active
     * 
     * @param string $message Message to log
     * @param string $type    Message type (info|error|warning)
     */
    function el_log($message, $type = 'info') {
        if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
            $prefix = defined('EL_LOG_PREFIX') ? EL_LOG_PREFIX : '[EL System] ';
            $prefix .= '[' . strtoupper($type) . '] ';
            error_log($prefix . $message);
        }
    }
}

if (!function_exists('el_get_color')) {
    /**
     * Retrieves colour value by name
     * 
     * @param string $color_name Color constant name (primary|dark|light|navy)
     * @return string Hex colour code
     */
    function el_get_color($color_name) {
        $colors = [
            'primary' => defined('EL_COLOR_PRIMARY') ? EL_COLOR_PRIMARY : '#a8bcceff',
            'dark' => defined('EL_COLOR_DARK') ? EL_COLOR_DARK : '#8fadd3ff',
            'light' => defined('EL_COLOR_LIGHT') ? EL_COLOR_LIGHT : '#bad8f6ff',
            'navy' => defined('EL_COLOR_NAVY') ? EL_COLOR_NAVY : '#1a3649ff',
            'bg_light' => defined('EL_COLOR_BG_LIGHT') ? EL_COLOR_BG_LIGHT : '#d5e4f6ff',
            'bg_pale' => defined('EL_COLOR_BG_PALE') ? EL_COLOR_BG_PALE : '#d5e4f6ff',
            'tint' => defined('EL_COLOR_TINT') ? EL_COLOR_TINT : '#bad8f6ff',
        ];
        
        return $colors[$color_name] ?? (defined('EL_COLOR_PRIMARY') ? EL_COLOR_PRIMARY : '#a8bcceff');
    }
}

if (!function_exists('el_get_tab_selector')) {
    /**
     * Retrieves tab selector by number
     * 
     * @param int $tab_number Tab number (1-5)
     * @return string jQuery selector
     */
    function el_get_tab_selector($tab_number) {
        $tabs = [
            1 => defined('EL_TAB_1_ID') ? EL_TAB_1_ID : '#brxe-kjwfkc',
            2 => defined('EL_TAB_2_ID') ? EL_TAB_2_ID : '#brxe-caqeqv',
            3 => defined('EL_TAB_3_ID') ? EL_TAB_3_ID : '#brxe-mhedar',
            4 => defined('EL_TAB_4_ID') ? EL_TAB_4_ID : '#brxe-ihqhkg',
            5 => defined('EL_TAB_5_ID') ? EL_TAB_5_ID : '#brxe-zmmopw',
        ];
        
        return $tabs[$tab_number] ?? (defined('EL_TAB_1_ID') ? EL_TAB_1_ID : '#brxe-kjwfkc');
    }
}

// Log constants loaded (if debug mode)
if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
    el_log('Constants loaded successfully', 'info');
}