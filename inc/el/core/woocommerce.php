<?php
/**
 * Engagement Letter System - WooCommerce Integration
 * 
 * Handles cart management, state persistence, product display customisation,
 * and integration with engagement letter workflow.
 * 
 * LOAD ORDER: #5 (after constants, session, helpers, merge-tags)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * LOAD GUARD: Must be loaded inside woocommerce_loaded hook
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// Dependency guard - this file requires WooCommerce
if (!function_exists('WC')) {
    if (EL_DEBUG_MODE) {
        el_log('WooCommerce integration not loaded - WooCommerce not available', 'warning');
    }
    return;
}

// ============================================
// CART STATE MANAGEMENT
// ============================================

/**
 * Saves current cart state to engagement letter
 * 
 * Stores complete cart snapshot including products, quantities,
 * pricing, and custom cart item meta.
 * 
 * @return bool True if saved successfully
 */
function el_save_cart_state() {
    if (!el_ensure_cart()) {
        return false;
    }
    
    $engagement_id = el_get_current_engagement_id();
    
    if (!$engagement_id) {
        if (EL_DEBUG_MODE) {
            el_log('Cannot save cart state - no active engagement', 'warning');
        }
        return false;
    }
    
    $cart = WC()->cart;
    
    // Build cart snapshot
    $cart_state = [
        'items' => [],
        'totals' => [
            'subtotal' => $cart->get_subtotal(),
            'total' => $cart->get_total(''),
            'tax' => $cart->get_total_tax(),
        ],
        'timestamp' => current_time('mysql'),
    ];
    
    // Capture each cart item
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        
        $cart_state['items'][] = [
            'product_id' => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
            'quantity' => $cart_item['quantity'],
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'total' => $cart_item['line_total'],
            'cart_item_key' => $cart_item_key,
            
            // Custom meta (grouped products, requirements, etc.)
            'custom_meta' => [
                'grouped_parent_id' => $cart_item[EL_CART_META_PARENT_ID] ?? null,
                'is_grouped_child' => $cart_item[EL_CART_META_IS_CHILD] ?? false,
                'grouped_parent_name' => $cart_item[EL_CART_META_PARENT_NAME] ?? null,
                'parent_acf_data' => $cart_item[EL_CART_META_PARENT_DATA] ?? null,
                'requirement' => $cart_item[EL_CART_META_REQUIREMENT] ?? null,
            ],
        ];
    }
    
    // Save to engagement letter
    $result = el_set_meta($engagement_id, 'cart_contents', $cart_state);
    
    if ($result && EL_DEBUG_MODE) {
        el_log('Cart state saved for engagement ' . $engagement_id . ' (' . count($cart_state['items']) . ' items)', 'info');
    }
    
    return $result;
}

/**
 * Restores cart state from engagement letter
 * 
 * Rebuilds cart exactly as it was saved, including all custom meta.
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return bool True if restored successfully
 */
function el_restore_cart_state($engagement_id) {
    if (!el_ensure_cart()) {
        return false;
    }
    
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    $cart_state = el_get_meta($engagement_id, 'cart_contents');
    
    if (empty($cart_state) || empty($cart_state['items'])) {
        if (EL_DEBUG_MODE) {
            el_log('No cart state to restore for engagement ' . $engagement_id, 'info');
        }
        return false;
    }
    
    $cart = WC()->cart;
    
    // Clear current cart
    $cart->empty_cart();
    
    // Restore each item
    foreach ($cart_state['items'] as $item) {
        $cart_item_data = [];
        
        // Add custom meta back to cart item
        if (!empty($item['custom_meta'])) {
            foreach ($item['custom_meta'] as $key => $value) {
                if ($value !== null) {
                    $cart_item_data[$key] = $value;
                }
            }
        }
        
        // Add to cart
        $cart->add_to_cart(
            $item['product_id'],
            $item['quantity'],
            $item['variation_id'],
            [],
            $cart_item_data
        );
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Cart state restored for engagement ' . $engagement_id . ' (' . count($cart_state['items']) . ' items)', 'info');
    }
    
    return true;
}

/**
 * Auto-saves cart state whenever cart is updated
 * 
 * Triggered by WooCommerce cart update events.
 */
function el_auto_save_cart_state() {
    // Only auto-save if there's an active engagement
    if (!el_has_active_engagement()) {
        return;
    }
    
    el_save_cart_state();
}

// Hook to cart update events
add_action('woocommerce_cart_updated', 'el_auto_save_cart_state');
add_action('woocommerce_add_to_cart', 'el_auto_save_cart_state');
add_action('woocommerce_cart_item_removed', 'el_auto_save_cart_state');
add_action('woocommerce_cart_item_restored', 'el_auto_save_cart_state');

// ============================================
// CART DISPLAY CUSTOMISATION
// ============================================

/**
 * Customises product name display in cart
 * 
 * Shows parent product name for grouped children.
 * 
 * @param string $product_name Original product name
 * @param array  $cart_item    Cart item data
 * @param string $cart_item_key Cart item key
 * @return string Customised product name
 */
function el_custom_cart_item_name($product_name, $cart_item, $cart_item_key) {
    // Show parent name for grouped children
    if (!empty($cart_item[EL_CART_META_PARENT_NAME])) {
        $parent_name = esc_html($cart_item[EL_CART_META_PARENT_NAME]);
        return '<span class="el-parent-product">' . $parent_name . '</span><br><span class="el-child-product">↳ ' . $product_name . '</span>';
    }
    
    return $product_name;
}
add_filter('woocommerce_cart_item_name', 'el_custom_cart_item_name', 10, 3);

/**
 * Adds custom data to cart item
 * 
 * Preserves grouped product relationships and requirements.
 * 
 * @param array $cart_item_data Existing cart item data
 * @param int   $product_id     Product ID
 * @param int   $variation_id   Variation ID
 * @return array Modified cart item data
 */
function el_add_custom_cart_item_data($cart_item_data, $product_id, $variation_id) {
    // Check for grouped parent in session
    $grouped_parent_id = el_get_session(EL_SESSION_GROUPED_PARENT_ID);
    
    if ($grouped_parent_id) {
        // This is a grouped child product
        $cart_item_data[EL_CART_META_PARENT_ID] = $grouped_parent_id;
        $cart_item_data[EL_CART_META_IS_CHILD] = true;
        $cart_item_data[EL_CART_META_PARENT_NAME] = el_get_session(EL_SESSION_GROUPED_PARENT_NAME);
        $cart_item_data[EL_CART_META_PARENT_DATA] = el_get_session(EL_SESSION_GROUPED_PARENT_DATA);
        
        if (EL_DEBUG_MODE) {
            el_log('Added grouped child ' . $product_id . ' with parent ' . $grouped_parent_id, 'info');
        }
    }
    
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'el_add_custom_cart_item_data', 10, 3);

/**
 * Displays custom cart item meta in cart/checkout
 * 
 * @param array $item_data Item meta to display
 * @param array $cart_item Cart item data
 * @return array Modified item meta
 */
function el_display_custom_cart_item_meta($item_data, $cart_item) {
    // Show requirement level
    if (!empty($cart_item[EL_CART_META_REQUIREMENT])) {
        $requirement = $cart_item[EL_CART_META_REQUIREMENT];
        
        $item_data[] = [
            'key' => 'Requirement',
            'value' => ucfirst($requirement),
            'display' => ucfirst($requirement),
        ];
    }
    
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'el_display_custom_cart_item_meta', 10, 2);

// ============================================
// CART VALIDATION
// ============================================

/**
 * Validates product can be added to cart
 * 
 * Prevents duplicate products and enforces business rules.
 * 
 * @param bool $passed      Validation status
 * @param int  $product_id  Product ID
 * @param int  $quantity    Quantity being added
 * @return bool True if validation passes
 */
function el_validate_add_to_cart($passed, $product_id, $quantity) {
    if (!el_ensure_cart()) {
        return $passed;
    }
    
    // Check if product already in cart
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            wc_add_notice('This service is already in your engagement letter.', 'error');
            return false;
        }
    }
    
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'el_validate_add_to_cart', 10, 3);

// ============================================
// CART QUANTITY MANAGEMENT
// ============================================

/**
 * Forces quantity to 1 for engagement letter products
 * 
 * Prevents multiple quantities of legal services.
 * 
 * @param int    $quantity  Requested quantity
 * @param string $cart_item_key Cart item key
 * @return int Always returns 1
 */
function el_force_cart_quantity($quantity, $cart_item_key) {
    return 1;
}
add_filter('woocommerce_cart_item_quantity', 'el_force_cart_quantity', 10, 2);

/**
 * Prevents quantity field display in cart
 * 
 * @param bool   $readonly      Whether quantity is readonly
 * @param array  $cart_item     Cart item data
 * @param string $cart_item_key Cart item key
 * @return bool Always true for EL products
 */
function el_make_cart_quantity_readonly($readonly, $cart_item, $cart_item_key) {
    // Check if this is an engagement letter product
    if (el_is_template_product($cart_item['product_id'])) {
        return true;
    }
    
    return $readonly;
}
add_filter('woocommerce_cart_item_quantity', 'el_make_cart_quantity_readonly', 10, 3);

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Updates cart item quantity
 */
function el_ajax_update_cart_quantity() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (empty($cart_item_key)) {
        wp_send_json_error(['message' => 'Invalid cart item']);
    }
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    $result = WC()->cart->set_quantity($cart_item_key, $quantity);
    
    if ($result) {
        el_save_cart_state();
        
        wp_send_json_success([
            'message' => 'Quantity updated',
            'cart_total' => WC()->cart->get_total(''),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to update quantity']);
    }
}
add_action('wp_ajax_' . EL_AJAX_UPDATE_QTY, 'el_ajax_update_cart_quantity');

/**
 * AJAX: Removes item from cart
 */
function el_ajax_remove_cart_item() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    
    if (empty($cart_item_key)) {
        wp_send_json_error(['message' => 'Invalid cart item']);
    }
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    $result = WC()->cart->remove_cart_item($cart_item_key);
    
    if ($result) {
        el_save_cart_state();
        
        wp_send_json_success([
            'message' => 'Item removed',
            'cart_total' => WC()->cart->get_total(''),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to remove item']);
    }
}
add_action('wp_ajax_' . EL_AJAX_REMOVE_ITEM, 'el_ajax_remove_cart_item');

/**
 * AJAX: Refreshes cart session
 * 
 * Forces cart recalculation and returns updated HTML.
 */
function el_ajax_refresh_cart_session() {
    check_ajax_referer(EL_REFRESH_NONCE, 'nonce');
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    WC()->cart->calculate_totals();
    el_save_cart_state();
    
    wp_send_json_success([
        'message' => 'Cart refreshed',
        'cart_total' => WC()->cart->get_total(''),
        'cart_count' => WC()->cart->get_cart_contents_count(),
    ]);
}
add_action('wp_ajax_' . EL_AJAX_REFRESH_CART_SESSION, 'el_ajax_refresh_cart_session');

// ============================================
// CART UTILITY FUNCTIONS
// Note: Basic cart functions (el_get_cart_product_ids, el_is_product_in_cart, etc.)
// are now in helpers.php for early availability
// ============================================

/**
 * Retrieves all grouped child products in cart
 * 
 * @param int $parent_id Parent product ID
 * @return array Array of cart items that are children of parent
 */
function el_get_grouped_children_in_cart($parent_id) {
    if (!el_ensure_cart()) {
        return [];
    }
    
    $children = [];
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item[EL_CART_META_PARENT_ID]) && 
            $cart_item[EL_CART_META_PARENT_ID] == $parent_id) {
            $cart_item['cart_item_key'] = $cart_item_key;
            $children[] = $cart_item;
        }
    }
    
    return $children;
}

// Note: el_get_cart_count() moved to helpers.php for early availability

// ============================================
// MINI CART CUSTOMISATION
// ============================================

/**
 * Customises mini cart display
 * 
 * Hides mini cart widget during engagement letter creation.
 */
function el_hide_mini_cart_widget() {
    // Only hide on engagement letter pages
    if (!is_page('engagement-letter-wizard')) {
        return;
    }
    
    echo '<style>.woocommerce-mini-cart { display: none !important; }</style>';
}
add_action('wp_head', 'el_hide_mini_cart_widget');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('WooCommerce integration module loaded successfully', 'info');
}