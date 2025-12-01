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
    error_log('❌ [GROUPED PRODUCTS] WooCommerce not available - ABORTING');
    if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
        el_log('Grouped products not loaded - WooCommerce not available', 'warning');
    }
    return;
}

if (!function_exists('el_session_active')) {
    error_log('❌ [GROUPED PRODUCTS] Session module not loaded - ABORTING');
    if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
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
function el_store_grouped_parent($product_id) {
    $debug_file = ABSPATH . 'ajax-debug.txt';
    file_put_contents($debug_file, "  [STORE PARENT] Function called with ID: $product_id\n", FILE_APPEND);
    
    if (!WC()->session) {
        file_put_contents($debug_file, "  [STORE PARENT] ERROR: No WC session\n", FILE_APPEND);
        return false;
    }
    file_put_contents($debug_file, "  [STORE PARENT] WC session exists\n", FILE_APPEND);
    
    $product = wc_get_product($product_id);
    if (!$product) {
        file_put_contents($debug_file, "  [STORE PARENT] ERROR: Product not found\n", FILE_APPEND);
        return false;
    }
    file_put_contents($debug_file, "  [STORE PARENT] Product object retrieved\n", FILE_APPEND);
    
    file_put_contents($debug_file, "  [STORE PARENT] Setting session data\n", FILE_APPEND);
    WC()->session->set('el_grouped_parent', [
        'id' => $product_id,
        'name' => $product->get_name(),
        'timestamp' => time()
    ]);
    file_put_contents($debug_file, "  [STORE PARENT] Session data set successfully\n", FILE_APPEND);
    
    return true;
}

/**
 * Retrieves grouped parent data from session
 * 
 * @return array|false Parent data array or false if not set
 */
function el_get_grouped_parent() {
    $debug_file = ABSPATH . 'ajax-debug.txt';
    file_put_contents($debug_file, "      [GET PARENT] Function called\n", FILE_APPEND);
    
    if (!WC()->session) {
        file_put_contents($debug_file, "      [GET PARENT] ERROR: No WC session\n", FILE_APPEND);
        return null;
    }
    file_put_contents($debug_file, "      [GET PARENT] WC session exists\n", FILE_APPEND);
    
    $parent = WC()->session->get('el_grouped_parent');
    
    if ($parent) {
        file_put_contents($debug_file, "      [GET PARENT] Found parent: " . print_r($parent, true) . "\n", FILE_APPEND);
    } else {
        file_put_contents($debug_file, "      [GET PARENT] ERROR: Session key 'el_grouped_parent' is empty or null\n", FILE_APPEND);
    }
    
    return $parent;
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
    
    error_log('🗑️ [el_clear_grouped_parent] Cleared parent from session');
    
    if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
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
function el_add_grouped_child_to_cart($child_id, $quantity = 1) {
    $debug_file = ABSPATH . 'ajax-debug.txt';
    file_put_contents($debug_file, "    [ADD CHILD] Function called for ID: $child_id\n", FILE_APPEND);
    
    $parent = el_get_grouped_parent();
    if (!$parent) {
        file_put_contents($debug_file, "    [ADD CHILD] ERROR: No parent in session\n", FILE_APPEND);
        return false;
    }
    file_put_contents($debug_file, "    [ADD CHILD] Parent found: {$parent['id']}\n", FILE_APPEND);
    
    $child_product = wc_get_product($child_id);
    if (!$child_product) {
        file_put_contents($debug_file, "    [ADD CHILD] ERROR: Child product not found\n", FILE_APPEND);
        return false;
    }
    file_put_contents($debug_file, "    [ADD CHILD] Child product retrieved\n", FILE_APPEND);
    
    file_put_contents($debug_file, "    [ADD CHILD] Attempting WC()->cart->add_to_cart()\n", FILE_APPEND);
    $cart_item_key = WC()->cart->add_to_cart(
        $child_id,
        $quantity,
        0,
        [],
        ['el_grouped_parent' => $parent['id']]
    );
    
    if ($cart_item_key) {
        file_put_contents($debug_file, "    [ADD CHILD] SUCCESS: Cart key = $cart_item_key\n", FILE_APPEND);
    } else {
        file_put_contents($debug_file, "    [ADD CHILD] FAILED: add_to_cart() returned false\n", FILE_APPEND);
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
    
    if ($removed > 0) {
        error_log('🗑️ [el_remove_grouped_children] Removed ' . $removed . ' children for parent ' . $parent_id);
        
        if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
            el_log('Removed ' . $removed . ' grouped children for parent ' . $parent_id, 'info');
        }
    }
    
    return $removed;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Stores grouped parent in session AND adds all children to cart
 * 
 * Called when user clicks on grouped product in template selection.
 * This handles the complete workflow: store parent + add all children.
 */
function el_ajax_store_grouped_parent() {
    $debug_file = ABSPATH . 'ajax-debug.txt';
    file_put_contents($debug_file, date('[Y-m-d H:i:s] ') . "🚨 AJAX HANDLER EXECUTED\n", FILE_APPEND);
    
    file_put_contents($debug_file, "Step 1: Checking nonce\n", FILE_APPEND);
    check_ajax_referer(EL_NONCE, 'nonce');
    file_put_contents($debug_file, "Step 2: Nonce OK\n", FILE_APPEND);
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    file_put_contents($debug_file, "Step 3: Product ID: $product_id\n", FILE_APPEND);
    
    if (!$product_id) {
        file_put_contents($debug_file, "Step 4: No product ID - ERROR\n", FILE_APPEND);
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    file_put_contents($debug_file, "Step 5: Product ID valid\n", FILE_APPEND);
    
    file_put_contents($debug_file, "Step 6: Getting product object\n", FILE_APPEND);
    $product = wc_get_product($product_id);
    file_put_contents($debug_file, "Step 7: Product object retrieved\n", FILE_APPEND);
    
    if (!$product) {
        file_put_contents($debug_file, "Step 8: Product not found - ERROR\n", FILE_APPEND);
        wp_send_json_error(['message' => 'Product not found']);
    }
    file_put_contents($debug_file, "Step 9: Product exists\n", FILE_APPEND);
    
    file_put_contents($debug_file, "Step 10: Getting product type\n", FILE_APPEND);
    $product_type = $product->get_type();
    file_put_contents($debug_file, "Step 11: Product type: $product_type\n", FILE_APPEND);
    
    if ($product_type !== 'grouped') {
        file_put_contents($debug_file, "Step 12: Not grouped - ERROR\n", FILE_APPEND);
        wp_send_json_error(['message' => 'Invalid grouped product']);
    }
    file_put_contents($debug_file, "Step 13: Product is grouped\n", FILE_APPEND);
    
    // Continue with rest of function...
    file_put_contents($debug_file, "Step 14: Storing parent\n", FILE_APPEND);
    el_store_grouped_parent($product_id);
    file_put_contents($debug_file, "Step 15: Parent stored\n", FILE_APPEND);
    
    // Get children
    $children = $product->get_children();
    file_put_contents($debug_file, "Found " . count($children) . " children\n", FILE_APPEND);
    
    // Empty cart
    WC()->cart->empty_cart();
    file_put_contents($debug_file, "Cart emptied\n", FILE_APPEND);
    
    // Add children
    $added_count = 0;
    foreach ($children as $child_id) {
        file_put_contents($debug_file, "Processing child $child_id\n", FILE_APPEND);
        
        $child_product = wc_get_product($child_id);
        if (!$child_product) {
            file_put_contents($debug_file, "  Child product not found\n", FILE_APPEND);
            continue;
        }
        
        file_put_contents($debug_file, "  Purchasable: " . ($child_product->is_purchasable() ? 'YES' : 'NO') . "\n", FILE_APPEND);
        file_put_contents($debug_file, "  In Stock: " . ($child_product->is_in_stock() ? 'YES' : 'NO') . "\n", FILE_APPEND);
        
        $cart_item_key = el_add_grouped_child_to_cart($child_id, 1);
        
        if ($cart_item_key) {
            $added_count++;
            file_put_contents($debug_file, "  ✅ ADDED to cart\n", FILE_APPEND);
        } else {
            file_put_contents($debug_file, "  ❌ FAILED to add\n", FILE_APPEND);
        }
    }
    
    file_put_contents($debug_file, "Total added: $added_count\n", FILE_APPEND);
    
    // Build response (keep existing code)
    $cart_count = WC()->cart->get_cart_contents_count();
    
    $children_data = [];
    foreach ($children as $child_id) {
        $child = wc_get_product($child_id);
        if ($child) {
            $in_cart = false;
            foreach (WC()->cart->get_cart() as $item) {
                if ($item['product_id'] === $child_id) {
                    $in_cart = true;
                    break;
                }
            }
            $children_data[] = [
                'id' => $child_id,
                'name' => $child->get_name(),
                'price' => wc_price($child->get_price()),
                'in_cart' => $in_cart
            ];
        }
    }
    
    wp_send_json_success([
        'message' => "Parent stored and $added_count services added to cart",
        'parent' => [
            'id' => $product_id,
            'name' => $product->get_name()
        ],
        'children' => $children_data,
        'cart_count' => $cart_count,
        'children_added' => $added_count,
        'debug' => [
            'file_version' => 'FIXED-2024-11-28',
            'total_children' => count($children),
            'children_ids' => $children,
            'loop_executed' => true
        ]
    ]);
}

add_action('wp_ajax_el_store_grouped_parent', 'el_ajax_store_grouped_parent');

/**
 * AJAX: Adds grouped child to cart
 */
function el_ajax_add_grouped_child() {
    error_log('🌐 [AJAX] el_add_grouped_child called');
    
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (!$product_id) {
        error_log('❌ [AJAX] Invalid product ID');
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    
    // Verify parent exists
    if (!el_has_grouped_parent()) {
        error_log('❌ [AJAX] No parent product selected');
        wp_send_json_error(['message' => 'No parent product selected']);
    }
    
    // Check if already in cart
    if (el_is_grouped_child_in_cart($product_id)) {
        error_log('⚠️ [AJAX] Product already in cart');
        wp_send_json_error(['message' => 'Product already in cart']);
    }
    
    // Add to cart
    $cart_item_key = el_add_grouped_child_to_cart($product_id, $quantity);
    
    if ($cart_item_key) {
        $product = wc_get_product($product_id);
        
        error_log('✅ [AJAX] Child added successfully');
        
        wp_send_json_success([
            'message' => 'Child added to cart',
            'cart_item_key' => $cart_item_key,
            'product_name' => $product->get_name(),
            'cart_count' => el_get_cart_count(),
        ]);
    } else {
        error_log('❌ [AJAX] Failed to add to cart');
        wp_send_json_error(['message' => 'Failed to add to cart']);
    }
}


add_action('wp_ajax_el_add_grouped_child', 'el_ajax_add_grouped_child');

/**
 * AJAX: Clears grouped parent and optionally removes children
 */
function el_ajax_clear_grouped_parent() {
    error_log('🌐 [AJAX] el_clear_grouped_parent called');
    
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
        
        if (defined('EL_DEBUG_MODE') && EL_DEBUG_MODE) {
            el_log('Cleared grouped parent - cart emptied', 'info');
        }
    }
}
add_action('woocommerce_cart_emptied', 'el_cleanup_grouped_parent_on_empty_cart');

