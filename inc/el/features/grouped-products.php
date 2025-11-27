<?php
/**
 * Engagement Letter System - Grouped Products
 * 
 * Handles WooCommerce grouped products where:
 * - Parent product stored in SESSION (WooCommerce removes it from cart)
 * - Child products added to cart WITH parent ACF data
 * - Parent metadata preserved throughout workflow
 * 
 * LOAD ORDER: #6 (after constants, session, helpers, merge-tags, woocommerce)
 * DEPENDENCIES: constants.php, session.php, helpers.php, woocommerce.php
 * LOAD GUARD: Must be loaded inside woocommerce_loaded hook
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// Dependency guards
if (!function_exists('WC')) {
    if (EL_DEBUG_MODE) {
        el_log('Grouped products not loaded - WooCommerce not available', 'warning');
    }
    return;
}

if (!function_exists('el_session_active')) {
    if (EL_DEBUG_MODE) {
        el_log('Grouped products not loaded - Session module not loaded', 'error');
    }
    return;
}

// ============================================
// PARENT PRODUCT SESSION STORAGE
// ============================================

/**
 * Stores grouped parent product in session
 * 
 * Parent products are NOT added to cart (WooCommerce removes them).
 * Instead, we store parent data in session and attach it to children.
 * 
 * @param int    $product_id   Parent product ID
 * @param string $product_name Parent product name
 * @param array  $acf_data     Parent product ACF data
 * @return bool True if stored successfully
 */
function el_store_grouped_parent($product_id, $product_name, $acf_data = []) {
    // Session guard - CRITICAL
    if (!el_session_active()) {
        el_log('Cannot store grouped parent - session not active', 'error');
        return false;
    }
    
    // Validate product exists and is grouped type
    $product = wc_get_product($product_id);
    
    if (!$product) {
        el_log('Cannot store grouped parent - product ' . $product_id . ' not found', 'error');
        return false;
    }
    
    if ($product->get_type() !== 'grouped') {
        el_log('Product ' . $product_id . ' is not a grouped product', 'warning');
        // Continue anyway - might be intentional
    }
    
    // If ACF data not provided, fetch it
    if (empty($acf_data)) {
        $acf_data = el_get_product_acf_data($product_id);
    }
    
    // Store in session using constants
    el_set_session(EL_SESSION_GROUPED_PARENT_ID, $product_id);
    el_set_session(EL_SESSION_GROUPED_PARENT_NAME, $product_name);
    el_set_session(EL_SESSION_GROUPED_PARENT_DATA, $acf_data);
    
    if (EL_DEBUG_MODE) {
        el_log('Stored grouped parent: ' . $product_name . ' (ID: ' . $product_id . ')', 'info');
    }
    
    return true;
}

/**
 * Retrieves grouped parent data from session
 * 
 * @return array|false Parent data array or false if not set
 */
function el_get_grouped_parent() {
    if (!el_session_active()) {
        return false;
    }
    
    $parent_id = el_get_session(EL_SESSION_GROUPED_PARENT_ID);
    
    if (!$parent_id) {
        return false;
    }
    
    return [
        'id' => $parent_id,
        'name' => el_get_session(EL_SESSION_GROUPED_PARENT_NAME),
        'acf_data' => el_get_session(EL_SESSION_GROUPED_PARENT_DATA, []),
    ];
}

/**
 * Checks if grouped parent is currently set
 * 
 * @return bool True if parent product stored in session
 */
function el_has_grouped_parent() {
    if (!el_session_active()) {
        return false;
    }
    
    return !empty(el_get_session(EL_SESSION_GROUPED_PARENT_ID));
}

/**
 * Clears grouped parent from session
 * 
 * Called when user finishes selecting children or abandons grouped selection.
 * 
 * @return bool True if cleared successfully
 */
function el_clear_grouped_parent() {
    if (!el_session_active()) {
        return false;
    }
    
    el_unset_session(EL_SESSION_GROUPED_PARENT_ID);
    el_unset_session(EL_SESSION_GROUPED_PARENT_NAME);
    el_unset_session(EL_SESSION_GROUPED_PARENT_DATA);
    
    if (EL_DEBUG_MODE) {
        el_log('Cleared grouped parent from session', 'info');
    }
    
    return true;
}

// ============================================
// CHILD PRODUCT CART MANAGEMENT
// ============================================

/**
 * Adds child product to cart with parent metadata
 * 
 * Automatically attaches parent data from session to cart item.
 * 
 * @param int   $product_id Product ID (child)
 * @param int   $quantity   Quantity (default: 1)
 * @param array $extra_data Additional cart item data
 * @return string|false Cart item key or false on failure
 */
function el_add_grouped_child_to_cart($product_id, $quantity = 1, $extra_data = []) {
    if (!el_ensure_cart()) {
        return false;
    }
    
    // Verify parent exists in session
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        el_log('Cannot add grouped child - no parent in session', 'error');
        return false;
    }
    
    // Build cart item data with parent metadata
    $cart_item_data = array_merge([
        EL_CART_META_PARENT_ID => $parent['id'],
        EL_CART_META_IS_CHILD => true,
        EL_CART_META_PARENT_NAME => $parent['name'],
        EL_CART_META_PARENT_DATA => $parent['acf_data'],
    ], $extra_data);
    
    // Add to cart (woocommerce.php filter will also add parent data)
    $cart_item_key = WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        0, // No variation
        [], // No variation attributes
        $cart_item_data
    );
    
    if ($cart_item_key && EL_DEBUG_MODE) {
        $product = wc_get_product($product_id);
        el_log('Added grouped child: ' . $product->get_name() . ' (parent: ' . $parent['name'] . ')', 'info');
    }
    
    return $cart_item_key;
}

/**
 * Retrieves all child products for current parent
 * 
 * @return array Array of child product IDs
 */
function el_get_grouped_children() {
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        return [];
    }
    
    $parent_product = wc_get_product($parent['id']);
    
    if (!$parent_product || $parent_product->get_type() !== 'grouped') {
        return [];
    }
    
    return $parent_product->get_children();
}

/**
 * Checks if child product is already in cart
 * 
 * @param int $product_id Child product ID
 * @return bool True if already in cart
 */
function el_is_grouped_child_in_cart($product_id) {
    if (!el_ensure_cart()) {
        return false;
    }
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id && 
            !empty($cart_item[EL_CART_META_IS_CHILD])) {
            return true;
        }
    }
    
    return false;
}

/**
 * Removes all children of a grouped parent from cart
 * 
 * Useful when user wants to start over with parent selection.
 * 
 * @param int $parent_id Parent product ID
 * @return int Number of children removed
 */
function el_remove_grouped_children($parent_id) {
    if (!el_ensure_cart()) {
        return 0;
    }
    
    $removed = 0;
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item[EL_CART_META_PARENT_ID]) && 
            $cart_item[EL_CART_META_PARENT_ID] == $parent_id) {
            WC()->cart->remove_cart_item($cart_item_key);
            $removed++;
        }
    }
    
    if ($removed > 0 && EL_DEBUG_MODE) {
        el_log('Removed ' . $removed . ' grouped children for parent ' . $parent_id, 'info');
    }
    
    return $removed;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Stores grouped parent in session
 * 
 * Called when user clicks on grouped product in template selection.
 */
function el_ajax_store_grouped_parent() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error(['message' => 'Product not found']);
    }
    
    // Get product name and ACF data
    $product_name = $product->get_name();
    $acf_data = el_get_product_acf_data($product_id);
    
    // Store in session
    $result = el_store_grouped_parent($product_id, $product_name, $acf_data);
    
    if ($result) {
        // Get children for display
        $children = el_get_grouped_children();
        $children_data = [];
        
        foreach ($children as $child_id) {
            $child_product = wc_get_product($child_id);
            if ($child_product) {
                $children_data[] = [
                    'id' => $child_id,
                    'name' => $child_product->get_name(),
                    'price' => $child_product->get_price(),
                    'in_cart' => el_is_grouped_child_in_cart($child_id),
                ];
            }
        }
        
        wp_send_json_success([
            'message' => 'Parent stored',
            'parent' => [
                'id' => $product_id,
                'name' => $product_name,
            ],
            'children' => $children_data,
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to store parent']);
    }
}
add_action('wp_ajax_el_store_grouped_parent', 'el_ajax_store_grouped_parent');

/**
 * AJAX: Adds grouped child to cart
 */
function el_ajax_add_grouped_child() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    
    // Verify parent exists
    if (!el_has_grouped_parent()) {
        wp_send_json_error(['message' => 'No parent product selected']);
    }
    
    // Check if already in cart
    if (el_is_grouped_child_in_cart($product_id)) {
        wp_send_json_error(['message' => 'Product already in cart']);
    }
    
    // Add to cart
    $cart_item_key = el_add_grouped_child_to_cart($product_id, $quantity);
    
    if ($cart_item_key) {
        $product = wc_get_product($product_id);
        
        wp_send_json_success([
            'message' => 'Child added to cart',
            'cart_item_key' => $cart_item_key,
            'product_name' => $product->get_name(),
            'cart_count' => el_get_cart_count(),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to add to cart']);
    }
}
add_action('wp_ajax_el_add_grouped_child', 'el_ajax_add_grouped_child');

/**
 * AJAX: Clears grouped parent and optionally removes children
 */
function el_ajax_clear_grouped_parent() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $remove_children = isset($_POST['remove_children']) && $_POST['remove_children'] === 'true';
    
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        wp_send_json_error(['message' => 'No parent to clear']);
    }
    
    $removed_count = 0;
    
    if ($remove_children) {
        $removed_count = el_remove_grouped_children($parent['id']);
    }
    
    el_clear_grouped_parent();
    
    wp_send_json_success([
        'message' => 'Parent cleared',
        'children_removed' => $removed_count,
    ]);
}
add_action('wp_ajax_el_clear_grouped_parent', 'el_ajax_clear_grouped_parent');

/**
 * AJAX: Gets current grouped parent status
 */
function el_ajax_get_grouped_parent_status() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        wp_send_json_success([
            'has_parent' => false,
        ]);
    }
    
    // Get children and their cart status
    $children = el_get_grouped_children();
    $children_data = [];
    
    foreach ($children as $child_id) {
        $child_product = wc_get_product($child_id);
        if ($child_product) {
            $children_data[] = [
                'id' => $child_id,
                'name' => $child_product->get_name(),
                'in_cart' => el_is_grouped_child_in_cart($child_id),
            ];
        }
    }
    
    wp_send_json_success([
        'has_parent' => true,
        'parent' => $parent,
        'children' => $children_data,
    ]);
}
add_action('wp_ajax_el_get_grouped_parent_status', 'el_ajax_get_grouped_parent_status');

// ============================================
// PDF GENERATION INTEGRATION
// ============================================

/**
 * Retrieves parent ACF data for cart item
 * 
 * Used during PDF generation to access parent product data.
 * 
 * @param array $cart_item Cart item data
 * @return array|false Parent ACF data or false if not grouped child
 */
function el_get_cart_item_parent_data($cart_item) {
    if (empty($cart_item[EL_CART_META_PARENT_DATA])) {
        return false;
    }
    
    return $cart_item[EL_CART_META_PARENT_DATA];
}

/**
 * Retrieves parent product name for cart item
 * 
 * @param array $cart_item Cart item data
 * @return string|false Parent name or false if not grouped child
 */
function el_get_cart_item_parent_name($cart_item) {
    if (empty($cart_item[EL_CART_META_PARENT_NAME])) {
        return false;
    }
    
    return $cart_item[EL_CART_META_PARENT_NAME];
}

/**
 * Checks if cart item is grouped child
 * 
 * @param array $cart_item Cart item data
 * @return bool True if grouped child
 */
function el_is_cart_item_grouped_child($cart_item) {
    return !empty($cart_item[EL_CART_META_IS_CHILD]);
}

// ============================================
// CART CLEANUP HOOKS
// ============================================

/**
 * Clears grouped parent when cart is emptied
 * 
 * Prevents orphaned parent data in session.
 */
function el_cleanup_grouped_parent_on_empty_cart() {
    if (el_has_grouped_parent()) {
        el_clear_grouped_parent();
        
        if (EL_DEBUG_MODE) {
            el_log('Cleared grouped parent - cart emptied', 'info');
        }
    }
}
add_action('woocommerce_cart_emptied', 'el_cleanup_grouped_parent_on_empty_cart');

/**
 * Maintains grouped parent data when restoring cart
 * 
 * Ensures parent data persists across sessions.
 * 
 * @param int $engagement_id Engagement letter post ID
 */
function el_restore_grouped_parent($engagement_id) {
    if (!el_ensure_cart()) {
        return;
    }
    
    // Check if any cart items have grouped parent data
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (!empty($cart_item[EL_CART_META_PARENT_ID])) {
            // Restore parent to session
            el_store_grouped_parent(
                $cart_item[EL_CART_META_PARENT_ID],
                $cart_item[EL_CART_META_PARENT_NAME],
                $cart_item[EL_CART_META_PARENT_DATA]
            );
            
            // Only need to restore once
            break;
        }
    }
}

// ============================================
// DISPLAY HELPERS
// ============================================

/**
 * Retrieves grouped parent display HTML for admin
 * 
 * Shows current parent selection in UI.
 * 
 * @return string HTML output
 */
function el_get_grouped_parent_display() {
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        return '<p class="el-no-parent">No parent product selected</p>';
    }
    
    $product = wc_get_product($parent['id']);
    
    if (!$product) {
        return '<p class="el-no-parent">Parent product not found</p>';
    }
    
    $output = '<div class="el-grouped-parent-display">';
    $output .= '<h4>Selected Parent Product</h4>';
    $output .= '<div class="el-parent-info">';
    $output .= '<strong>' . esc_html($parent['name']) . '</strong>';
    $output .= '<p>ID: ' . $parent['id'] . '</p>';
    
    // Show children count
    $children = el_get_grouped_children();
    $output .= '<p>Available children: ' . count($children) . '</p>';
    
    $output .= '</div>';
    $output .= '<button type="button" class="button el-clear-parent">Clear Parent</button>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Shortcode: Display grouped parent status (debug)
 * 
 * Usage: [el_grouped_parent_status]
 */
function el_grouped_parent_status_shortcode() {
    if (!current_user_can('edit_posts')) {
        return '';
    }
    
    $parent = el_get_grouped_parent();
    
    if (!$parent) {
        return '<div class="el-debug">No grouped parent in session</div>';
    }
    
    $output = '<div class="el-debug">';
    $output .= '<h4>Grouped Parent Status</h4>';
    $output .= '<pre>' . print_r($parent, true) . '</pre>';
    
    $children = el_get_grouped_children();
    $output .= '<p><strong>Children:</strong> ' . count($children) . '</p>';
    
    foreach ($children as $child_id) {
        $child = wc_get_product($child_id);
        $in_cart = el_is_grouped_child_in_cart($child_id) ? '✓' : '✗';
        $output .= '<p>' . $in_cart . ' ' . $child->get_name() . '</p>';
    }
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_grouped_parent_status', 'el_grouped_parent_status_shortcode');

// ============================================
// REQUIREMENT LEVEL HANDLING
// ============================================

/**
 * Sets requirement level for grouped child
 * 
 * Marks child as 'required' or 'suggested'.
 * 
 * @param int    $product_id   Child product ID
 * @param string $requirement  'required' or 'suggested'
 * @return bool True if set successfully
 */
function el_set_child_requirement($product_id, $requirement = 'suggested') {
    if (!in_array($requirement, ['required', 'suggested'])) {
        $requirement = 'suggested';
    }
    
    // This would be set in product meta or ACF
    return update_post_meta($product_id, '_el_requirement', $requirement);
}

/**
 * Retrieves requirement level for grouped child
 * 
 * @param int $product_id Child product ID
 * @return string 'required' or 'suggested'
 */
function el_get_child_requirement($product_id) {
    $requirement = get_post_meta($product_id, '_el_requirement', true);
    return $requirement ?: 'suggested';
}

/**
 * Filters grouped children by requirement level
 * 
 * @param array  $children     Array of child product IDs
 * @param string $requirement  'required' or 'suggested'
 * @return array Filtered children
 */
function el_filter_children_by_requirement($children, $requirement) {
    return array_filter($children, function($child_id) use ($requirement) {
        return el_get_child_requirement($child_id) === $requirement;
    });
}

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Grouped products module loaded successfully', 'info');
}