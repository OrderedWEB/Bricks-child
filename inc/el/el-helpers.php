<?php
/**
 * Engagement Letter System - Core Helper Functions
 * 
 * Centralised utility functions for data storage, retrieval, validation,
 * and common operations used throughout the system.
 * 
 * LOAD ORDER: #3 (after constants.php, session.php)
 * DEPENDENCIES: constants.php, session.php
 * USED BY: All modules
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// TRANSIENT HELPERS (PDF Data, Cart State)
// Ensures consistent key naming and expiry
// ============================================

/**
 * Stores PDF data with consistent key format
 * 
 * @param string $reference Engagement letter reference code
 * @param array  $data      PDF data array
 * @param int    $expiry    Expiry time in seconds (default: 1 hour)
 * @return bool True if stored successfully
 */
function el_set_pdf_data($reference, $data, $expiry = EL_TRANSIENT_PDF_EXPIRY) {
    $key = EL_PDF_DATA_PREFIX . sanitize_key($reference);
    $result = set_transient($key, $data, $expiry);
    
    if (EL_DEBUG_MODE) {
        el_log('PDF data stored for reference: ' . $reference . ' (expiry: ' . $expiry . 's)', 'info');
    }
    
    return $result;
}

/**
 * Retrieves PDF data by reference
 * 
 * @param string $reference Engagement letter reference code
 * @return array|false PDF data array or false if expired/not found
 */
function el_get_pdf_data($reference) {
    $key = EL_PDF_DATA_PREFIX . sanitize_key($reference);
    $data = get_transient($key);
    
    if ($data === false && EL_DEBUG_MODE) {
        el_log('PDF data not found or expired for reference: ' . $reference, 'warning');
    }
    
    return $data;
}

/**
 * Deletes PDF data by reference
 * 
 * @param string $reference Engagement letter reference code
 * @return bool True if deleted successfully
 */
function el_delete_pdf_data($reference) {
    $key = EL_PDF_DATA_PREFIX . sanitize_key($reference);
    return delete_transient($key);
}

/**
 * Checks if PDF data exists for reference
 * 
 * @param string $reference Engagement letter reference code
 * @return bool True if data exists and not expired
 */
function el_has_pdf_data($reference) {
    return el_get_pdf_data($reference) !== false;
}

// ============================================
// POST META HELPERS (Engagement Letters)
// Consistent meta key naming and retrieval
// ============================================

/**
 * Retrieves engagement letter meta field with fallback
 * 
 * @param int    $post_id Post ID
 * @param string $field   Field name (use short name, not full meta key)
 * @param mixed  $default Default value if not found
 * @return mixed Meta value or default
 */
function el_get_meta($post_id, $field, $default = '') {
    // Map short names to actual meta keys (defined in constants)
    $meta_map = [
        'reference' => EL_META_REFERENCE,
        'status' => EL_META_STATUS,
        'client_id' => EL_META_CLIENT_ID,
        'lawyer_id' => EL_META_LAWYER_ID,
        'template_id' => EL_META_TEMPLATE_ID,
        'form_data' => EL_META_FORM_DATA,
        'cart_contents' => EL_META_CART_CONTENTS,
        'current_tab' => EL_META_CURRENT_TAB,
        'last_active' => EL_META_LAST_ACTIVE,
        'created_date' => EL_META_CREATED_DATE,
        'modified_date' => EL_META_MODIFIED_DATE,
        'practice_area' => EL_META_PRACTICE_AREA,
        'signed_reference' => EL_META_SIGNED_REFERENCE,
        'signature_date' => EL_META_SIGNATURE_DATE,
        'signed_pdf_url' => EL_META_SIGNED_PDF_URL,
    ];
    
    // Get actual meta key (or fallback to prefixed field name)
    $meta_key = $meta_map[$field] ?? '_el_' . $field;
    
    // Retrieve value
    $value = get_post_meta($post_id, $meta_key, true);
    
    return $value ?: $default;
}

/**
 * Sets engagement letter meta field
 * 
 * @param int    $post_id Post ID
 * @param string $field   Field name (short name)
 * @param mixed  $value   Value to store
 * @return bool True if updated successfully
 */
function el_set_meta($post_id, $field, $value) {
    $meta_map = [
        'reference' => EL_META_REFERENCE,
        'status' => EL_META_STATUS,
        'client_id' => EL_META_CLIENT_ID,
        'lawyer_id' => EL_META_LAWYER_ID,
        'template_id' => EL_META_TEMPLATE_ID,
        'form_data' => EL_META_FORM_DATA,
        'cart_contents' => EL_META_CART_CONTENTS,
        'current_tab' => EL_META_CURRENT_TAB,
        'last_active' => EL_META_LAST_ACTIVE,
        'practice_area' => EL_META_PRACTICE_AREA,
        'signed_reference' => EL_META_SIGNED_REFERENCE,
        'signature_date' => EL_META_SIGNATURE_DATE,
        'signed_pdf_url' => EL_META_SIGNED_PDF_URL,
    ];
    
    $meta_key = $meta_map[$field] ?? '_el_' . $field;
    
    $result = update_post_meta($post_id, $meta_key, $value);
    
    if (EL_DEBUG_MODE) {
        el_log('Meta "' . $field . '" updated for post ' . $post_id, 'info');
    }
    
    return $result !== false;
}

/**
 * Deletes engagement letter meta field
 * 
 * @param int    $post_id Post ID
 * @param string $field   Field name
 * @return bool True if deleted successfully
 */
function el_delete_meta($post_id, $field) {
    $meta_map = [
        'reference' => EL_META_REFERENCE,
        'status' => EL_META_STATUS,
        'client_id' => EL_META_CLIENT_ID,
        // ... (add all mappings)
    ];
    
    $meta_key = $meta_map[$field] ?? '_el_' . $field;
    
    return delete_post_meta($post_id, $meta_key);
}

// ============================================
// VALIDATION HELPERS
// ============================================

/**
 * Validates email address
 * 
 * @param string $email Email address to validate
 * @return bool True if valid email
 */
function el_validate_email($email) {
    $email = sanitize_email($email);
    return is_email($email) !== false;
}

/**
 * Validates engagement letter reference format
 * 
 * Expected format: EL-YYYYMMDD-XXXXXX
 * 
 * @param string $reference Reference code to validate
 * @return bool True if valid format
 */
function el_validate_reference($reference) {
    return (bool) preg_match('/^EL-\d{8}-[a-f0-9]{6}$/i', $reference);
}

/**
 * Validates post is engagement letter CPT
 * 
 * @param int $post_id Post ID
 * @return bool True if valid engagement letter post
 */
function el_validate_engagement_post($post_id) {
    return get_post_type($post_id) === EL_CPT_ENGAGEMENT;
}

/**
 * Validates user has capability to edit engagement letters
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return bool True if user can edit
 */
function el_user_can_edit($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    // Check custom meta or standard capability
    $can_edit = get_user_meta($user_id, EL_USER_META_CAN_EDIT, true);
    
    return $can_edit === '1' || user_can($user_id, 'edit_posts');
}

// ============================================
// REFERENCE GENERATION
// ============================================

/**
 * Generates unique engagement letter reference code
 * 
 * Format: EL-YYYYMMDD-XXXXXX (e.g., EL-20250127-a3f8c2)
 * 
 * @return string Unique reference code
 */
function el_generate_reference() {
    $date_part = date('Ymd');
    $random_part = substr(md5(uniqid(mt_rand(), true)), 0, 6);
    
    return 'EL-' . $date_part . '-' . $random_part;
}

/**
 * Checks if reference code is unique
 * 
 * @param string $reference Reference code to check
 * @return bool True if unique (not already in use)
 */
function el_reference_is_unique($reference) {
    global $wpdb;
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} 
         WHERE meta_key = %s AND meta_value = %s",
        EL_META_REFERENCE,
        $reference
    ));
    
    return $count == 0;
}

/**
 * Generates guaranteed unique reference code
 * 
 * @param int $max_attempts Maximum generation attempts (default: 10)
 * @return string|false Unique reference or false if failed
 */
function el_generate_unique_reference($max_attempts = 10) {
    for ($i = 0; $i < $max_attempts; $i++) {
        $reference = el_generate_reference();
        
        if (el_reference_is_unique($reference)) {
            return $reference;
        }
    }
    
    el_log('Failed to generate unique reference after ' . $max_attempts . ' attempts', 'error');
    return false;
}

// ============================================
// FORMATTING HELPERS
// ============================================

/**
 * Formats currency amount
 * 
 * @param float  $amount   Amount to format
 * @param string $currency Currency symbol (default: €)
 * @return string Formatted currency string
 */
function el_format_currency($amount, $currency = '€') {
    return $currency . number_format((float) $amount, 2, '.', ',');
}

/**
 * Formats date/time for display
 * 
 * @param string $datetime MySQL datetime string
 * @param string $format   PHP date format (default: F j, Y)
 * @return string Formatted date string
 */
function el_format_date($datetime, $format = 'F j, Y') {
    if (empty($datetime)) {
        return '';
    }
    
    return date($format, strtotime($datetime));
}

/**
 * Calculates human-readable time difference
 * 
 * @param string $datetime MySQL datetime string
 * @return string Human-readable time ago (e.g., "2 hours ago")
 */
function el_time_ago($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    return human_time_diff(strtotime($datetime), current_time('timestamp')) . ' ago';
}

// ============================================
// WOOCOMMERCE HELPERS
// ============================================

/**
 * Ensures WooCommerce cart is initialised
 * 
 * @return bool True if cart available
 */
function el_ensure_cart() {
    if (!function_exists('WC')) {
        el_log('WooCommerce not available', 'error');
        return false;
    }
    
    if (is_null(WC()->cart)) {
        wc_load_cart();
    }
    
    return WC()->cart !== null;
}

/**
 * Checks if product is in el-templates category
 * 
 * @param int $product_id Product ID
 * @return bool True if in el-templates category
 */
function el_is_template_product($product_id) {
    return has_term('el-templates', 'product_cat', $product_id);
}

/**
 * Retrieves all ACF data for a product
 * 
 * @param int $product_id Product ID
 * @return array Associative array of ACF field values
 */
function el_get_product_acf_data($product_id) {
    return [
        'pdf_el_title' => get_field(EL_ACF_PDF_TITLE, $product_id) ?: '',
        'pdf_el_subtitle' => get_field(EL_ACF_PDF_SUBTITLE, $product_id) ?: '',
        'pdf_el_text' => get_field(EL_ACF_PDF_TEXT, $product_id) ?: '',
        'el_introduction_texts' => get_field(EL_ACF_INTRODUCTION, $product_id) ?: '',
        'pdf_footer_notes' => get_field(EL_ACF_PDF_FOOTER, $product_id) ?: '',
        'fee_structure' => get_field(EL_ACF_FEE_STRUCTURE, $product_id) ?: '',
        'pdf_clauses' => get_field(EL_ACF_PDF_CLAUSES, $product_id) ?: [],
        'practice_area' => get_field(EL_ACF_PRACTICE_AREA, $product_id) ?: '',
        'engagement_fee' => get_field(EL_ACF_ENGAGEMENT_FEE, $product_id) ?: 0,
        'expected_total_cost' => get_field(EL_ACF_EXPECTED_COST, $product_id) ?: 0,
        'hourly_rate' => get_field(EL_ACF_HOURLY_RATE, $product_id) ?: 0,
        'rate_cap' => get_field(EL_ACF_RATE_CAP, $product_id) ?: 0,
    ];
}

// ============================================
// ENGAGEMENT LETTER CRUD HELPERS
// ============================================

/**
 * Creates new engagement letter post
 * 
 * @param array $args Post arguments
 * @return int|false Post ID or false on failure
 */
function el_create_engagement_letter($args = []) {
    $defaults = [
        'title' => 'Engagement Letter - ' . date('Y-m-d H:i:s'),
        'client_id' => 0,
        'lawyer_id' => get_current_user_id(),
        'status' => EL_STATUS_DRAFT,
        'form_data' => [],
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Generate unique reference
    $reference = el_generate_unique_reference();
    if (!$reference) {
        return false;
    }
    
    // Create post
    $post_id = wp_insert_post([
        'post_title' => $args['title'],
        'post_type' => EL_CPT_ENGAGEMENT,
        'post_status' => 'publish',
        'post_author' => $args['lawyer_id'],
    ]);
    
    if (is_wp_error($post_id)) {
        el_log('Failed to create engagement letter: ' . $post_id->get_error_message(), 'error');
        return false;
    }
    
    // Set meta data using helper
    el_set_meta($post_id, 'reference', $reference);
    el_set_meta($post_id, 'status', $args['status']);
    el_set_meta($post_id, 'client_id', $args['client_id']);
    el_set_meta($post_id, 'lawyer_id', $args['lawyer_id']);
    el_set_meta($post_id, 'form_data', $args['form_data']);
    el_set_meta($post_id, 'current_tab', 1);
    el_set_meta($post_id, 'created_date', current_time('mysql'));
    el_set_meta($post_id, 'last_active', current_time('mysql'));
    
    // Store in session
    el_set_current_engagement_id($post_id);
    
    if (EL_DEBUG_MODE) {
        el_log('Created engagement letter ' . $post_id . ' with reference ' . $reference, 'info');
    }
    
    return $post_id;
}

/**
 * Updates engagement letter meta fields
 * 
 * @param int   $post_id Post ID
 * @param array $args    Meta fields to update
 * @return bool True if updated successfully
 */
function el_update_engagement_letter($post_id, $args = []) {
    if (!el_validate_engagement_post($post_id)) {
        return false;
    }
    
    // Update last active timestamp
    el_set_meta($post_id, 'last_active', current_time('mysql'));
    el_set_meta($post_id, 'modified_date', current_time('mysql'));
    
    // Update provided fields
    foreach ($args as $field => $value) {
        el_set_meta($post_id, $field, $value);
    }
    
    return true;
}

/**
 * Retrieves complete engagement letter data
 * 
 * @param int $post_id Post ID
 * @return array|false Complete engagement data or false if invalid
 */
function el_get_engagement_letter($post_id) {
    if (!el_validate_engagement_post($post_id)) {
        return false;
    }
    
    $post = get_post($post_id);
    
    return [
        'ID' => $post->ID,
        'title' => $post->post_title,
        'reference' => el_get_meta($post_id, 'reference'),
        'status' => el_get_meta($post_id, 'status'),
        'client_id' => el_get_meta($post_id, 'client_id'),
        'lawyer_id' => el_get_meta($post_id, 'lawyer_id'),
        'template_id' => el_get_meta($post_id, 'template_id'),
        'form_data' => el_get_meta($post_id, 'form_data'),
        'cart_contents' => el_get_meta($post_id, 'cart_contents'),
        'current_tab' => el_get_meta($post_id, 'current_tab'),
        'practice_area' => el_get_meta($post_id, 'practice_area'),
        'created_date' => el_get_meta($post_id, 'created_date'),
        'last_active' => el_get_meta($post_id, 'last_active'),
        'modified_date' => el_get_meta($post_id, 'modified_date'),
    ];
}

// ============================================
// SANITIZATION HELPERS
// ============================================

/**
 * Sanitises form data array
 * 
 * @param array $form_data Raw form data
 * @return array Sanitised form data
 */
function el_sanitize_form_data($form_data) {
    return [
        'first_name' => sanitize_text_field($form_data['first_name'] ?? ''),
        'last_name' => sanitize_text_field($form_data['last_name'] ?? ''),
        'email' => sanitize_email($form_data['email'] ?? ''),
        'phone' => sanitize_text_field($form_data['phone'] ?? ''),
        'street_address' => sanitize_text_field($form_data['street_address'] ?? ''),
        'city' => sanitize_text_field($form_data['city'] ?? ''),
        'state' => sanitize_text_field($form_data['state'] ?? ''),
        'zip' => sanitize_text_field($form_data['zip'] ?? ''),
        'country' => sanitize_text_field($form_data['country'] ?? ''),
        'notes' => sanitize_textarea_field($form_data['notes'] ?? ''),
    ];
}

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Helpers module loaded successfully', 'info');
}