<?php
/**
 * Engagement Letter System - Payment Handler
 * 
 * Handles payment processing and WooCommerce order creation:
 * - Creates WooCommerce orders from cart
 * - Bank transfer payment processing
 * - Payment status tracking
 * - Order → Engagement letter linking
 * 
 * LOAD ORDER: Feature module (after core + WooCommerce modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php, woocommerce.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// Dependency guard
if (!function_exists('WC')) {
    if (EL_DEBUG_MODE) {
        el_log('Payment handler not loaded - WooCommerce not available', 'warning');
    }
    return;
}

// ============================================
// ORDER CREATION
// ============================================

/**
 * Creates WooCommerce order from engagement letter
 * 
 * Converts engagement letter cart into actual WC order.
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return int|false Order ID or false on failure
 */
function el_create_order_from_engagement($engagement_id) {
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    // Get engagement data
    $engagement = el_get_engagement_letter($engagement_id);
    $cart_contents = el_get_meta($engagement_id, 'cart_contents');
    
    if (empty($cart_contents['items'])) {
        el_log('Cannot create order - no cart items', 'error');
        return false;
    }
    
    // Create order
    $order = wc_create_order([
        'customer_id' => $engagement['client_id'] ?: 0,
        'created_via' => 'engagement_letter',
        'status' => 'pending',
    ]);
    
    if (is_wp_error($order)) {
        el_log('Order creation failed: ' . $order->get_error_message(), 'error');
        return false;
    }
    
    // Add items to order
    foreach ($cart_contents['items'] as $item) {
        $product = wc_get_product($item['product_id']);
        
        if (!$product) {
            continue;
        }
        
        // Add product to order
        $order_item_id = $order->add_product($product, $item['quantity'], [
            'subtotal' => $item['total'],
            'total' => $item['total'],
        ]);
        
        // Add custom meta to order item
        if ($order_item_id && !empty($item['custom_meta'])) {
            foreach ($item['custom_meta'] as $meta_key => $meta_value) {
                if ($meta_value !== null) {
                    wc_add_order_item_meta($order_item_id, $meta_key, $meta_value);
                }
            }
        }
    }
    
    // Set billing details from form data
    $form_data = $engagement['form_data'];
    if (!empty($form_data)) {
        $order->set_billing_first_name($form_data['first_name'] ?? '');
        $order->set_billing_last_name($form_data['last_name'] ?? '');
        $order->set_billing_email($form_data['email'] ?? '');
        $order->set_billing_phone($form_data['phone'] ?? '');
        $order->set_billing_address_1($form_data['street_address'] ?? '');
        $order->set_billing_city($form_data['city'] ?? '');
        $order->set_billing_state($form_data['state'] ?? '');
        $order->set_billing_postcode($form_data['zip'] ?? '');
        $order->set_billing_country($form_data['country'] ?? '');
    }
    
    // Calculate totals
    $order->calculate_totals();
    
    // Link order to engagement letter
    $order->add_meta_data('_el_engagement_id', $engagement_id);
    $order->add_meta_data('_el_reference', $engagement['reference']);
    
    // Link engagement to order
    el_set_meta($engagement_id, 'order_id', $order->get_id());
    
    // Update engagement status
    el_set_meta($engagement_id, 'status', EL_STATUS_SENT);
    
    $order->save();
    
    if (EL_DEBUG_MODE) {
        el_log('Created order ' . $order->get_id() . ' for engagement ' . $engagement_id, 'info');
    }
    
    return $order->get_id();
}

/**
 * Retrieves order ID for engagement letter
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return int|false Order ID or false if not found
 */
function el_get_engagement_order($engagement_id) {
    $order_id = el_get_meta($engagement_id, 'order_id');
    
    if (!$order_id) {
        return false;
    }
    
    // Verify order exists
    $order = wc_get_order($order_id);
    
    return $order ? $order_id : false;
}

/**
 * Retrieves engagement ID from order
 * 
 * @param int $order_id WooCommerce order ID
 * @return int|false Engagement ID or false if not found
 */
function el_get_order_engagement($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return false;
    }
    
    $engagement_id = $order->get_meta('_el_engagement_id');
    
    return $engagement_id ?: false;
}

// ============================================
// BANK TRANSFER HANDLING
// ============================================

/**
 * Processes bank transfer payment
 * 
 * Marks order as on-hold pending bank transfer confirmation.
 * 
 * @param int $order_id Order ID
 * @return bool True if processed successfully
 */
function el_process_bank_transfer($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return false;
    }
    
    // Set payment method
    $order->set_payment_method('bacs'); // Bank transfer
    $order->set_payment_method_title('Bank Transfer');
    
    // Update status to on-hold
    $order->update_status('on-hold', 'Awaiting bank transfer payment.');
    
    // Send email to client with bank details
    WC()->mailer()->emails['WC_Email_Customer_On_Hold_Order']->trigger($order_id);
    
    // Get engagement and update status
    $engagement_id = el_get_order_engagement($order_id);
    
    if ($engagement_id) {
        el_set_meta($engagement_id, 'status', EL_STATUS_SENT);
        el_set_meta($engagement_id, 'payment_method', 'bank_transfer');
        el_set_meta($engagement_id, 'payment_requested_date', current_time('mysql'));
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Bank transfer initiated for order ' . $order_id, 'info');
    }
    
    return true;
}

/**
 * Confirms bank transfer payment received
 * 
 * Manually called by admin when payment verified.
 * 
 * @param int $order_id Order ID
 * @return bool True if confirmed successfully
 */
function el_confirm_bank_transfer($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return false;
    }
    
    // Update order status to processing/completed
    $order->payment_complete();
    
    // Update engagement status
    $engagement_id = el_get_order_engagement($order_id);
    
    if ($engagement_id) {
        el_set_meta($engagement_id, 'status', EL_STATUS_PAID);
        el_set_meta($engagement_id, 'payment_confirmed_date', current_time('mysql'));
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Bank transfer confirmed for order ' . $order_id, 'info');
    }
    
    return true;
}

// ============================================
// PAYMENT STATUS TRACKING
// ============================================

/**
 * Checks if engagement letter is paid
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return bool True if paid
 */
function el_is_paid($engagement_id) {
    $status = el_get_meta($engagement_id, 'status');
    
    if ($status === EL_STATUS_PAID || $status === EL_STATUS_COMPLETED) {
        return true;
    }
    
    // Check linked order status
    $order_id = el_get_engagement_order($engagement_id);
    
    if ($order_id) {
        $order = wc_get_order($order_id);
        return $order && $order->is_paid();
    }
    
    return false;
}

/**
 * Retrieves payment status summary
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return array Payment status data
 */
function el_get_payment_status($engagement_id) {
    $order_id = el_get_engagement_order($engagement_id);
    
    $status = [
        'has_order' => (bool) $order_id,
        'order_id' => $order_id,
        'is_paid' => el_is_paid($engagement_id),
        'payment_method' => el_get_meta($engagement_id, 'payment_method'),
        'amount' => 0,
        'currency' => get_woocommerce_currency(),
        'order_status' => null,
    ];
    
    if ($order_id) {
        $order = wc_get_order($order_id);
        
        if ($order) {
            $status['amount'] = $order->get_total();
            $status['order_status'] = $order->get_status();
            $status['payment_method'] = $order->get_payment_method();
        }
    }
    
    return $status;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Create order and process payment
 */
function el_ajax_create_order() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = el_get_current_engagement_id();
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'No active engagement']);
    }
    
    // Check if order already exists
    $existing_order = el_get_engagement_order($engagement_id);
    
    if ($existing_order) {
        wp_send_json_error(['message' => 'Order already exists for this engagement']);
    }
    
    // Create order
    $order_id = el_create_order_from_engagement($engagement_id);
    
    if (!$order_id) {
        wp_send_json_error(['message' => 'Failed to create order']);
    }
    
    // Get payment method from request
    $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'bacs');
    
    // Process payment based on method
    if ($payment_method === 'bacs' || $payment_method === 'bank_transfer') {
        el_process_bank_transfer($order_id);
        
        $message = 'Order created. Awaiting bank transfer payment.';
    } else {
        $message = 'Order created successfully.';
    }
    
    wp_send_json_success([
        'message' => $message,
        'order_id' => $order_id,
        'order_url' => get_permalink(wc_get_page_id('myaccount')) . 'view-order/' . $order_id,
    ]);
}
add_action('wp_ajax_el_create_order', 'el_ajax_create_order');

/**
 * AJAX: Get payment status
 */
function el_ajax_get_payment_status() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    
    if (!$engagement_id) {
        $engagement_id = el_get_current_engagement_id();
    }
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'No engagement specified']);
    }
    
    $status = el_get_payment_status($engagement_id);
    
    wp_send_json_success($status);
}
add_action('wp_ajax_el_get_payment_status', 'el_ajax_get_payment_status');

// ============================================
// WOOCOMMERCE HOOKS
// ============================================

/**
 * Updates engagement status when order status changes
 * 
 * Keeps engagement and order statuses synchronized.
 * 
 * @param int    $order_id   Order ID
 * @param string $old_status Previous status
 * @param string $new_status New status
 */
function el_sync_order_status_to_engagement($order_id, $old_status, $new_status) {
    $engagement_id = el_get_order_engagement($order_id);
    
    if (!$engagement_id) {
        return; // Not an engagement letter order
    }
    
    // Map WC order statuses to engagement statuses
    $status_map = [
        'pending' => EL_STATUS_SENT,
        'on-hold' => EL_STATUS_SENT,
        'processing' => EL_STATUS_PAID,
        'completed' => EL_STATUS_COMPLETED,
        'failed' => EL_STATUS_DRAFT,
        'cancelled' => EL_STATUS_DRAFT,
        'refunded' => EL_STATUS_DRAFT,
    ];
    
    if (isset($status_map[$new_status])) {
        el_set_meta($engagement_id, 'status', $status_map[$new_status]);
        
        if (EL_DEBUG_MODE) {
            el_log('Engagement ' . $engagement_id . ' status updated to ' . $status_map[$new_status] . ' (order: ' . $new_status . ')', 'info');
        }
    }
}
add_action('woocommerce_order_status_changed', 'el_sync_order_status_to_engagement', 10, 3);

/**
 * Adds engagement letter meta to order emails
 * 
 * @param WC_Order $order        Order object
 * @param bool     $sent_to_admin Sending to admin
 * @param bool     $plain_text    Plain text email
 */
function el_add_engagement_meta_to_emails($order, $sent_to_admin, $plain_text) {
    $engagement_id = $order->get_meta('_el_engagement_id');
    
    if (!$engagement_id) {
        return;
    }
    
    $reference = $order->get_meta('_el_reference');
    
    if ($plain_text) {
        echo "\n\n";
        echo "ENGAGEMENT LETTER REFERENCE: " . $reference . "\n";
        echo "---\n";
    } else {
        echo '<div style="margin: 20px 0; padding: 15px; background: #f7f7f7; border-left: 4px solid ' . EL_COLOR_PRIMARY . ';">';
        echo '<p style="margin: 0; font-weight: bold;">Engagement Letter Reference:</p>';
        echo '<p style="margin: 5px 0 0 0;">' . esc_html($reference) . '</p>';
        echo '</div>';
    }
}
add_action('woocommerce_email_before_order_table', 'el_add_engagement_meta_to_emails', 10, 3);

// ============================================
// PAYMENT DISPLAY HELPERS
// ============================================

/**
 * Renders payment status badge
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return string HTML badge
 */
function el_render_payment_badge($engagement_id) {
    $is_paid = el_is_paid($engagement_id);
    $status = el_get_meta($engagement_id, 'status');
    
    if ($is_paid) {
        $badge = '<span class="el-badge el-badge-paid" style="
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        ">✓ Paid</span>';
    } elseif ($status === EL_STATUS_SENT) {
        $badge = '<span class="el-badge el-badge-pending" style="
            background: #f59e0b;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        ">⏳ Payment Pending</span>';
    } else {
        $badge = '<span class="el-badge el-badge-draft" style="
            background: #6b7280;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        ">Draft</span>';
    }
    
    return $badge;
}

/**
 * Renders payment summary box
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return string HTML summary
 */
function el_render_payment_summary($engagement_id) {
    $status = el_get_payment_status($engagement_id);
    
    $output = '<div class="el-payment-summary" style="
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    ">';
    
    $output .= '<h3 style="margin: 0 0 15px 0; font-size: 16px;">Payment Status</h3>';
    
    if ($status['has_order']) {
        $order = wc_get_order($status['order_id']);
        
        $output .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">';
        
        $output .= '<div>';
        $output .= '<p style="margin: 0; font-size: 12px; color: #6b7280;">Order Number</p>';
        $output .= '<p style="margin: 5px 0 0 0; font-weight: 600;">#' . $order->get_order_number() . '</p>';
        $output .= '</div>';
        
        $output .= '<div>';
        $output .= '<p style="margin: 0; font-size: 12px; color: #6b7280;">Total Amount</p>';
        $output .= '<p style="margin: 5px 0 0 0; font-weight: 600;">' . el_format_currency($status['amount'], '€') . '</p>';
        $output .= '</div>';
        
        $output .= '<div>';
        $output .= '<p style="margin: 0; font-size: 12px; color: #6b7280;">Payment Method</p>';
        $output .= '<p style="margin: 5px 0 0 0;">' . ucwords(str_replace('_', ' ', $status['payment_method'])) . '</p>';
        $output .= '</div>';
        
        $output .= '<div>';
        $output .= '<p style="margin: 0; font-size: 12px; color: #6b7280;">Status</p>';
        $output .= '<p style="margin: 5px 0 0 0;">' . el_render_payment_badge($engagement_id) . '</p>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        if ($order->get_status() === 'on-hold') {
            $output .= '<div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 12px; border-radius: 6px; margin-top: 15px;">';
            $output .= '<p style="margin: 0; font-size: 13px;"><strong>⏳ Awaiting Payment</strong></p>';
            $output .= '<p style="margin: 5px 0 0 0; font-size: 13px;">Please complete your bank transfer using the details provided.</p>';
            $output .= '</div>';
        }
        
        $output .= '<a href="' . esc_url($order->get_view_order_url()) . '" class="button" style="margin-top: 15px; display: inline-block;">View Order Details</a>';
        
    } else {
        $output .= '<p>No payment order created yet.</p>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Shortcode: Payment summary
 * 
 * Usage: [el_payment_summary]
 */
function el_payment_summary_shortcode($atts) {
    $atts = shortcode_atts([
        'engagement_id' => null,
    ], $atts);
    
    $engagement_id = $atts['engagement_id'] ?: el_get_current_engagement_id();
    
    if (!$engagement_id) {
        return '<p>No engagement specified.</p>';
    }
    
    return el_render_payment_summary($engagement_id);
}
add_shortcode('el_payment_summary', 'el_payment_summary_shortcode');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Payment handler module loaded successfully', 'info');
}