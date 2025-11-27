<?php
/**
 * Engagement Letter System - Merge Tags
 * 
 * Handles {{field_name}} merge tag replacement throughout PDF content.
 * Supports client data, lawyer data, product data, and system values.
 * 
 * LOAD ORDER: #4 (after constants, session, helpers)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * USED BY: PDF preview, PDF export, print system
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// MAIN MERGE TAG REPLACEMENT FUNCTION
// ============================================

/**
 * Replaces all merge tags in content with actual values
 * 
 * Processes {{field_name}} syntax throughout HTML/text content.
 * Supports nested arrays using dot notation: {{address.street}}
 * 
 * @param string $content  Content containing merge tags
 * @param array  $data     Data array for replacement
 * @param array  $options  Additional options (fallback_char, preserve_unknown)
 * @return string Content with merge tags replaced
 */
function el_replace_merge_tags($content, $data = [], $options = []) {
    if (empty($content)) {
        return $content;
    }
    
    // Default options
    $defaults = [
        'fallback_char' => '___________',  // Used for missing/empty values
        'preserve_unknown' => false,        // Keep {{unknown}} tags as-is
        'strip_empty' => false,             // Remove empty merge tags entirely
    ];
    
    $options = wp_parse_args($options, $defaults);
    
    // Build complete data array from all sources
    $merge_data = el_build_merge_data($data);
    
    // Find all merge tags: {{anything_here}}
    preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
    
    if (empty($matches[0])) {
        return $content; // No merge tags found
    }
    
    $replacements = [];
    
    foreach ($matches[1] as $index => $field_name) {
        $field_name = trim($field_name);
        $full_tag = $matches[0][$index]; // {{field_name}}
        
        // Get value from data (supports dot notation)
        $value = el_get_nested_value($merge_data, $field_name);
        
        // Handle missing/empty values
        if ($value === null || $value === '') {
            if ($options['strip_empty']) {
                $replacements[$full_tag] = '';
            } elseif ($options['preserve_unknown']) {
                $replacements[$full_tag] = $full_tag;
            } else {
                $replacements[$full_tag] = $options['fallback_char'];
            }
        } else {
            // Format value based on type
            $replacements[$full_tag] = el_format_merge_value($value, $field_name);
        }
    }
    
    // Perform replacements
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    
    if (EL_DEBUG_MODE) {
        el_log('Replaced ' . count($replacements) . ' merge tags', 'info');
    }
    
    return $content;
}

// ============================================
// DATA BUILDING FUNCTIONS
// ============================================

/**
 * Builds complete merge data array from all sources
 * 
 * Priority order:
 * 1. Manually passed data (highest priority)
 * 2. Session data
 * 3. Current engagement letter
 * 4. Current user/lawyer data
 * 5. System defaults
 * 
 * @param array $manual_data Manually provided data
 * @return array Complete merge data array
 */
function el_build_merge_data($manual_data = []) {
    $merge_data = [];
    
    // 1. System defaults
    $merge_data = array_merge($merge_data, el_get_system_merge_data());
    
    // 2. Current user/lawyer data
    $merge_data = array_merge($merge_data, el_get_lawyer_merge_data());
    
    // 3. Session data
    $merge_data = array_merge($merge_data, el_get_session_merge_data());
    
    // 4. Current engagement letter
    $engagement_id = el_get_current_engagement_id();
    if ($engagement_id) {
        $engagement_data = el_get_engagement_merge_data($engagement_id);
        $merge_data = array_merge($merge_data, $engagement_data);
    }
    
    // 5. Manual data (highest priority - overwrites everything)
    $merge_data = array_merge($merge_data, $manual_data);
    
    return $merge_data;
}

/**
 * Retrieves system-level merge data
 * 
 * @return array System merge data
 */
function el_get_system_merge_data() {
    return [
        'site_name' => get_bloginfo('name'),
        'site_url' => get_bloginfo('url'),
        'current_date' => date('F j, Y'),
        'current_date_short' => date('m/d/Y'),
        'current_year' => date('Y'),
        'firm_name' => get_field('firm_name', 'option') ?: get_bloginfo('name'),
        'firm_address' => get_field('firm_address', 'option') ?: '',
        'firm_phone' => get_field('firm_phone', 'option') ?: '',
        'firm_email' => get_field('firm_email', 'option') ?: get_bloginfo('admin_email'),
        'firm_website' => get_field('firm_website', 'option') ?: get_bloginfo('url'),
    ];
}

/**
 * Retrieves lawyer/current user merge data
 * 
 * @param int|null $user_id User ID (null = current user)
 * @return array Lawyer merge data
 */
function el_get_lawyer_merge_data($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return [];
    }
    
    $user = get_userdata($user_id);
    
    if (!$user) {
        return [];
    }
    
    return [
        'lawyer_name' => $user->display_name,
        'lawyer_first_name' => $user->first_name,
        'lawyer_last_name' => $user->last_name,
        'lawyer_email' => $user->user_email,
        'lawyer_role' => get_user_meta($user_id, EL_USER_META_ROLE, true) ?: '',
        'lawyer_rate' => get_user_meta($user_id, EL_USER_META_RATE, true) ?: '',
    ];
}

/**
 * Retrieves session-based merge data
 * 
 * @return array Session merge data
 */
function el_get_session_merge_data() {
    if (!el_session_active()) {
        return [];
    }
    
    $form_data = el_get_session(EL_SESSION_FORM_DATA, []);
    
    $data = [
        'client_name' => el_get_session(EL_SESSION_CLIENT_NAME, ''),
        'client_email' => el_get_session(EL_SESSION_CLIENT_EMAIL, ''),
        'pdf_reference' => el_get_session(EL_SESSION_PDF_REF, ''),
    ];
    
    // Add form data fields
    if (!empty($form_data)) {
        $data = array_merge($data, [
            'first_name' => $form_data['first_name'] ?? '',
            'last_name' => $form_data['last_name'] ?? '',
            'full_name' => trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
            'email' => $form_data['email'] ?? '',
            'phone' => $form_data['phone'] ?? '',
            'street_address' => $form_data['street_address'] ?? '',
            'city' => $form_data['city'] ?? '',
            'state' => $form_data['state'] ?? '',
            'zip' => $form_data['zip'] ?? '',
            'country' => $form_data['country'] ?? '',
            'full_address' => el_format_full_address($form_data),
        ]);
    }
    
    return $data;
}

/**
 * Retrieves engagement letter merge data
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return array Engagement merge data
 */
function el_get_engagement_merge_data($engagement_id) {
    $data = [];
    
    $engagement = el_get_engagement_letter($engagement_id);
    
    if (!$engagement) {
        return $data;
    }
    
    $data['reference'] = $engagement['reference'];
    $data['status'] = $engagement['status'];
    $data['created_date'] = el_format_date($engagement['created_date']);
    $data['practice_area'] = $engagement['practice_area'];
    
    // Add form data from engagement
    $form_data = $engagement['form_data'];
    if (!empty($form_data)) {
        $data = array_merge($data, [
            'client_first_name' => $form_data['first_name'] ?? '',
            'client_last_name' => $form_data['last_name'] ?? '',
            'client_full_name' => trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
            'client_email' => $form_data['email'] ?? '',
            'client_phone' => $form_data['phone'] ?? '',
            'client_address' => el_format_full_address($form_data),
        ]);
    }
    
    return $data;
}

/**
 * Retrieves product-specific merge data
 * 
 * @param int $product_id Product ID
 * @return array Product merge data
 */
function el_get_product_merge_data($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return [];
    }
    
    return [
        'product_name' => $product->get_name(),
        'product_price' => el_format_currency($product->get_price()),
        'product_description' => $product->get_short_description(),
        'practice_area' => get_field(EL_ACF_PRACTICE_AREA, $product_id) ?: '',
        'engagement_fee' => get_field(EL_ACF_ENGAGEMENT_FEE, $product_id) ?: 0,
        'expected_cost' => get_field(EL_ACF_EXPECTED_COST, $product_id) ?: 0,
        'hourly_rate' => get_field(EL_ACF_HOURLY_RATE, $product_id) ?: 0,
    ];
}

// ============================================
// VALUE FORMATTING FUNCTIONS
// ============================================

/**
 * Formats merge tag value based on field type
 * 
 * @param mixed  $value      Value to format
 * @param string $field_name Field name (used for type detection)
 * @return string Formatted value
 */
function el_format_merge_value($value, $field_name) {
    // Arrays/objects → JSON (should be flattened already)
    if (is_array($value) || is_object($value)) {
        return json_encode($value);
    }
    
    // Boolean → Yes/No
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    
    // Price/currency fields
    if (strpos($field_name, 'price') !== false || 
        strpos($field_name, 'fee') !== false || 
        strpos($field_name, 'cost') !== false || 
        strpos($field_name, 'rate') !== false) {
        return el_format_currency($value);
    }
    
    // Date fields
    if (strpos($field_name, 'date') !== false) {
        return el_format_date($value);
    }
    
    // Default: string
    return (string) $value;
}

// Note: el_format_full_address() is now in helpers.php (moved for early availability)

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Retrieves nested array value using dot notation
 * 
 * Supports: {{address.street}}, {{client.name.first}}
 * 
 * @param array  $array Array to search
 * @param string $key   Dot-notation key
 * @return mixed Value or null if not found
 */
function el_get_nested_value($array, $key) {
    // Direct key exists (no nesting)
    if (isset($array[$key])) {
        return $array[$key];
    }
    
    // Check for dot notation
    if (strpos($key, '.') === false) {
        return null;
    }
    
    // Parse nested key
    $keys = explode('.', $key);
    $value = $array;
    
    foreach ($keys as $nested_key) {
        if (!is_array($value) || !isset($value[$nested_key])) {
            return null;
        }
        $value = $value[$nested_key];
    }
    
    return $value;
}

/**
 * Lists all merge tags found in content
 * 
 * Useful for debugging and validation.
 * 
 * @param string $content Content to scan
 * @return array List of unique merge tags
 */
function el_find_merge_tags($content) {
    preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
    
    if (empty($matches[1])) {
        return [];
    }
    
    return array_unique(array_map('trim', $matches[1]));
}

/**
 * Validates all merge tags have data
 * 
 * @param string $content Content to validate
 * @param array  $data    Available data
 * @return array List of merge tags missing data
 */
function el_validate_merge_tags($content, $data = []) {
    $merge_data = el_build_merge_data($data);
    $tags = el_find_merge_tags($content);
    
    $missing = [];
    
    foreach ($tags as $tag) {
        $value = el_get_nested_value($merge_data, $tag);
        if ($value === null || $value === '') {
            $missing[] = $tag;
        }
    }
    
    return $missing;
}

// ============================================
// CART MERGE DATA (for PDF generation)
// ============================================

/**
 * Builds merge data from WooCommerce cart contents
 * 
 * @return array Cart-based merge data
 */
function el_get_cart_merge_data() {
    if (!el_ensure_cart()) {
        return [];
    }
    
    $cart = WC()->cart;
    $data = [];
    
    // Cart totals
    $data['cart_subtotal'] = el_format_currency($cart->get_subtotal());
    $data['cart_total'] = el_format_currency($cart->get_total(''));
    
    // Build services list
    $services = [];
    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $services[] = $product->get_name();
    }
    
    $data['services_list'] = implode(', ', $services);
    $data['services_count'] = count($services);
    
    return $data;
}

// ============================================
// SHORTCODE FOR TESTING
// ============================================

/**
 * Shortcode: Display available merge tags (admin only)
 * 
 * Usage: [el_merge_tags_list]
 */
function el_merge_tags_list_shortcode() {
    if (!current_user_can('manage_options')) {
        return 'Access denied';
    }
    
    $merge_data = el_build_merge_data();
    
    $output = '<div style="background: #f3f4f6; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 12px;">';
    $output .= '<h3>📋 Available Merge Tags</h3>';
    $output .= '<table style="width: 100%; border-collapse: collapse;">';
    $output .= '<tr><th style="text-align: left; padding: 8px; border-bottom: 1px solid #ccc;">Merge Tag</th><th style="text-align: left; padding: 8px; border-bottom: 1px solid #ccc;">Current Value</th></tr>';
    
    foreach ($merge_data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $value = '[Complex Data]';
        }
        
        $output .= '<tr>';
        $output .= '<td style="padding: 8px; border-bottom: 1px solid #eee;"><code>{{' . esc_html($key) . '}}</code></td>';
        $output .= '<td style="padding: 8px; border-bottom: 1px solid #eee;">' . esc_html($value) . '</td>';
        $output .= '</tr>';
    }
    
    $output .= '</table>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_merge_tags_list', 'el_merge_tags_list_shortcode');

// ============================================
// BOILERPLATE CONTENT HELPERS
// ============================================

/**
 * Retrieves and processes boilerplate content with merge tags
 * 
 * @param string $field_name ACF field name
 * @param array  $data       Additional merge data
 * @return string Processed boilerplate content
 */
function el_get_boilerplate($field_name, $data = []) {
    $content = get_field($field_name, 'option');
    
    if (empty($content)) {
        return '';
    }
    
    return el_replace_merge_tags($content, $data);
}

/**
 * Retrieves letterhead HTML with merge tags replaced
 * 
 * @param array $data Merge data
 * @return string Processed letterhead HTML
 */
function el_get_letterhead($data = []) {
    return el_get_boilerplate(EL_ACF_LETTERHEAD, $data);
}

/**
 * Retrieves footer boilerplate with merge tags replaced
 * 
 * @param array $data Merge data
 * @return string Processed footer HTML
 */
function el_get_footer_boilerplate($data = []) {
    return el_get_boilerplate(EL_ACF_FOOTER_BOILERPLATE, $data);
}

/**
 * Retrieves signature block with merge tags replaced
 * 
 * @param array $data Merge data
 * @return string Processed signature block HTML
 */
function el_get_signature_block($data = []) {
    return el_get_boilerplate(EL_ACF_SIGNATURE_BLOCK, $data);
}

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Merge tags module loaded successfully', 'info');
}