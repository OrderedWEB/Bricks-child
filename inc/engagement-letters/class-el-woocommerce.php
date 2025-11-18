<?php
/**
 * Engagement Letters WooCommerce Integration
 * 
 * Handles automatic order creation, status syncing, and bidirectional linking
 * between engagement letters and WooCommerce orders.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EL_WooCommerce {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - initialize hooks
     */
    private function __construct() {
        // Create order when EL is created
        add_action('save_post_engagement_letter', [$this, 'maybe_create_order'], 20, 3);
        
        // Sync statuses between EL and Order
        add_action('woocommerce_order_status_changed', [$this, 'sync_order_status_to_el'], 10, 4);
        add_action('set_object_terms', [$this, 'sync_el_status_to_order'], 10, 6);
        
        // Add EL info to order admin
        add_action('add_meta_boxes', [$this, 'add_order_meta_box']);
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_el_link_in_order']);
        
        // Add order notes when EL status changes
        add_action('set_object_terms', [$this, 'add_order_note_on_el_status_change'], 10, 6);
    }
    
    /**
     * Create WooCommerce order when engagement letter is saved
     */
    public function maybe_create_order($post_id, $post, $update) {
        // Don't create order if one already exists
        $existing_order_id = get_post_meta($post_id, '_el_order_id', true);
        if ($existing_order_id && get_post($existing_order_id)) {
            return;
        }
        
        // Don't create order for autosaves or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Get required data
        $client_id = get_post_meta($post_id, '_el_client_id', true);
        $template_id = get_post_meta($post_id, '_el_template_id', true);
        
        if (!$client_id || !$template_id) {
            return; // Need both to create order
        }
        
        // Get cart items from session (if available)
        $cart_items = WC()->session ? WC()->session->get('el_cart_state') : null;
        
        // Create the order
        try {
            $order = wc_create_order([
                'customer_id' => $client_id,
                'created_via' => 'engagement_letter',
                'status' => 'pending'
            ]);
            
            if (is_wp_error($order)) {
                error_log('EL WooCommerce: Failed to create order - ' . $order->get_error_message());
                return;
            }
            
            // Add items to order
            if ($cart_items && !empty($cart_items['items'])) {
                // Restore from saved cart state
                foreach ($cart_items['items'] as $cart_item_key => $item_data) {
                    $this->add_item_to_order($order, $item_data);
                }
            } else {
                // Just add the template product
                $product = wc_get_product($template_id);
                if ($product) {
                    $order->add_product($product, 1);
                }
            }
            
            // Calculate totals
            $order->calculate_totals();
            
            // Add order note
            $order->add_order_note(
                sprintf(
                    'Order automatically created from Engagement Letter #%d (%s)',
                    $post_id,
                    get_the_title($post_id)
                )
            );
            
            // Add custom meta
            $order->update_meta_data('_el_id', $post_id);
            $order->update_meta_data('_created_from_el', 'yes');
            
            $matter_ref = get_post_meta($post_id, '_el_matter_reference', true);
            if ($matter_ref) {
                $order->update_meta_data('_matter_reference', $matter_ref);
            }
            
            $order->save();
            
            // Link order to engagement letter
            update_post_meta($post_id, '_el_order_id', $order->get_id());
            
            // Log success
            error_log(sprintf(
                'EL WooCommerce: Created order #%d for engagement letter #%d',
                $order->get_id(),
                $post_id
            ));
            
        } catch (Exception $e) {
            error_log('EL WooCommerce: Exception creating order - ' . $e->getMessage());
        }
    }
    
    /**
     * Add item to order from cart item data
     */
    private function add_item_to_order($order, $item_data) {
        $product_id = $item_data['product_id'];
        $variation_id = $item_data['variation_id'] ?? 0;
        $quantity = $item_data['quantity'];
        
        $product = $variation_id ? wc_get_product($variation_id) : wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        // Add product to order
        $item_id = $order->add_product($product, $quantity);
        
        if (!$item_id) {
            return;
        }
        
        $item = $order->get_item($item_id);
        
        // Add variation attributes
        if (!empty($item_data['variation'])) {
            foreach ($item_data['variation'] as $key => $value) {
                $item->add_meta_data($key, $value, true);
            }
        }
        
        // Add product add-ons
        if (!empty($item_data['addons'])) {
            foreach ($item_data['addons'] as $addon) {
                $item->add_meta_data($addon['name'], $addon['value'], true);
            }
        }
        
        // Add bundle data
        if (!empty($item_data['bundled_items'])) {
            $item->add_meta_data('_bundled_items', $item_data['bundled_items'], true);
        }
        
        if (!empty($item_data['stamp'])) {
            $item->add_meta_data('_stamp', $item_data['stamp'], true);
        }
        
        // Add composite data
        if (!empty($item_data['composite_data'])) {
            $item->add_meta_data('_composite_data', $item_data['composite_data'], true);
        }
        
        if (!empty($item_data['composite_children'])) {
            $item->add_meta_data('_composite_children', $item_data['composite_children'], true);
        }
        
        // Add any custom meta
        if (!empty($item_data['meta_data'])) {
            foreach ($item_data['meta_data'] as $meta_key => $meta_value) {
                if (strpos($meta_key, '_') !== 0) { // Skip private meta
                    $item->add_meta_data($meta_key, $meta_value, true);
                }
            }
        }
        
        $item->save();
    }
    
    /**
     * Sync order status changes to engagement letter status
     */
    public function sync_order_status_to_el($order_id, $old_status, $new_status, $order) {
        // Get linked engagement letter
        $el_id = $order->get_meta('_el_id');
        
        if (!$el_id) {
            return;
        }
        
        // Map order statuses to EL statuses
        $status_map = [
            'pending' => 'ready_to_send',
            'processing' => 'paid',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
            'failed' => 'cancelled'
        ];
        
        // Get target EL status
        $target_el_status = $status_map[$new_status] ?? null;
        
        if (!$target_el_status) {
            return;
        }
        
        // Get current EL status
        $current_el_status = wp_get_object_terms($el_id, 'el_status', ['fields' => 'slugs']);
        $current_el_status = !empty($current_el_status) ? $current_el_status[0] : 'draft';
        
        // Don't downgrade status or sync if already at target
        if ($current_el_status === $target_el_status) {
            return;
        }
        
        // Check if transition is allowed
        $allowed_transitions = EL_Core::STATUSES[$current_el_status]['allow_transitions'] ?? [];
        
        if (!in_array($target_el_status, $allowed_transitions)) {
            return; // Not allowed
        }
        
        // Update EL status
        wp_set_object_terms($el_id, $target_el_status, 'el_status');
        
        // Log timestamp for paid status
        if ($target_el_status === 'paid') {
            update_post_meta($el_id, '_el_paid_date', time());
        }
        
        // Add note to order
        $order->add_order_note(
            sprintf(
                'Engagement Letter #%d status automatically updated to: %s',
                $el_id,
                EL_Core::STATUSES[$target_el_status]['label']
            )
        );
    }
    
    /**
     * Sync engagement letter status changes to order status
     */
    public function sync_el_status_to_order($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
        // Only handle el_status taxonomy
        if ($taxonomy !== 'el_status') {
            return;
        }
        
        // Get the post
        $post = get_post($object_id);
        if (!$post || $post->post_type !== 'engagement_letter') {
            return;
        }
        
        // Get linked order
        $order_id = get_post_meta($object_id, '_el_order_id', true);
        if (!$order_id) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Get new EL status
        $term = get_term($tt_ids[0]);
        $new_el_status = $term->slug;
        
        // Map EL statuses to order statuses
        $status_map = [
            'draft' => 'pending',
            'ready_to_send' => 'pending',
            'sent_for_signature' => 'on-hold',
            'signed' => 'on-hold',
            'paid' => 'processing',
            'completed' => 'completed',
            'cancelled' => 'cancelled'
        ];
        
        $target_order_status = $status_map[$new_el_status] ?? null;
        
        if (!$target_order_status) {
            return;
        }
        
        // Don't update if already at target status
        if ($order->get_status() === $target_order_status) {
            return;
        }
        
        // Update order status
        $order->update_status(
            $target_order_status,
            sprintf(
                'Status synced from Engagement Letter #%d: %s',
                $object_id,
                EL_Core::STATUSES[$new_el_status]['label']
            )
        );
    }
    
    /**
     * Add order note when EL status changes
     */
    public function add_order_note_on_el_status_change($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
        // Only handle el_status taxonomy
        if ($taxonomy !== 'el_status') {
            return;
        }
        
        // Get the post
        $post = get_post($object_id);
        if (!$post || $post->post_type !== 'engagement_letter') {
            return;
        }
        
        // Get linked order
        $order_id = get_post_meta($object_id, '_el_order_id', true);
        if (!$order_id) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Get old and new status
        $old_term = !empty($old_tt_ids) ? get_term($old_tt_ids[0]) : null;
        $new_term = get_term($tt_ids[0]);
        
        $old_status = $old_term ? $old_term->slug : 'unknown';
        $new_status = $new_term->slug;
        
        // Add order note
        $order->add_order_note(
            sprintf(
                'Engagement Letter status changed from "%s" to "%s"',
                EL_Core::STATUSES[$old_status]['label'] ?? $old_status,
                EL_Core::STATUSES[$new_status]['label']
            )
        );
    }
    
    /**
     * Add meta box to order admin
     */
    public function add_order_meta_box() {
        add_meta_box(
            'el_order_info',
            'Engagement Letter',
            [$this, 'render_order_meta_box'],
            'shop_order',
            'side',
            'high'
        );
        
        add_meta_box(
            'el_order_info',
            'Engagement Letter',
            [$this, 'render_order_meta_box'],
            'woocommerce_page_wc-orders',
            'side',
            'high'
        );
    }
    
    /**
     * Render order meta box
     */
    public function render_order_meta_box($post_or_order) {
        $order = $post_or_order instanceof WP_Post 
            ? wc_get_order($post_or_order->ID) 
            : $post_or_order;
            
        if (!$order) {
            return;
        }
        
        $el_id = $order->get_meta('_el_id');
        
        if (!$el_id) {
            echo '<p style="color: #64748b; font-size: 13px;">No engagement letter linked to this order.</p>';
            return;
        }
        
        $el_data = EL_Core::get_el_data($el_id);
        
        if (!$el_data) {
            echo '<p style="color: #ef4444; font-size: 13px;">Linked engagement letter not found.</p>';
            return;
        }
        
        ?>
        <style>
            .el-order-card {
                background: #f8fafc;
                border-left: 4px solid <?php echo $el_data['status_info']['color']; ?>;
                padding: 15px;
                border-radius: 6px;
            }
            .el-order-title {
                font-size: 14px;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 8px;
            }
            .el-order-title a {
                text-decoration: none;
                color: #667eea;
            }
            .el-order-title a:hover {
                text-decoration: underline;
            }
            .el-order-meta {
                font-size: 12px;
                color: #64748b;
                margin: 6px 0;
            }
            .el-order-actions {
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid #e2e8f0;
            }
            .el-order-button {
                display: inline-block;
                padding: 6px 12px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                transition: background 0.2s;
            }
            .el-order-button:hover {
                background: #5568d3;
            }
        </style>
        
        <div class="el-order-card">
            <div class="el-order-title">
                <a href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>" target="_blank">
                    <?php echo esc_html($el_data['title']); ?>
                </a>
            </div>
            
            <div class="el-order-meta">
                <?php echo EL_Core::get_status_badge($el_data['status']); ?>
            </div>
            
            <?php if ($el_data['matter_ref']): ?>
            <div class="el-order-meta">
                <strong>Matter:</strong> <?php echo esc_html($el_data['matter_ref']); ?>
            </div>
            <?php endif; ?>
            
            <div class="el-order-meta">
                <strong>Created:</strong> <?php echo date('M j, Y', strtotime($el_data['created'])); ?>
            </div>
            
            <?php if ($el_data['sent_date']): ?>
            <div class="el-order-meta">
                <strong>Sent:</strong> <?php echo date('M j, Y', $el_data['sent_date']); ?>
            </div>
            <?php endif; ?>
            
            <div class="el-order-actions">
                <a href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>" 
                   class="el-order-button" 
                   target="_blank">
                    View Engagement Letter →
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display EL link in order details
     */
    public function display_el_link_in_order($order) {
        $el_id = $order->get_meta('_el_id');
        
        if (!$el_id) {
            return;
        }
        
        $el_data = EL_Core::get_el_data($el_id);
        
        if (!$el_data) {
            return;
        }
        
        ?>
        <div class="order_data_column" style="clear:both; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            <h3 style="margin-bottom: 10px;">Engagement Letter</h3>
            <p>
                <strong><?php echo esc_html($el_data['title']); ?></strong><br>
                <?php echo EL_Core::get_status_badge($el_data['status']); ?><br>
                <a href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>" target="_blank">
                    Edit Engagement Letter →
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get order by engagement letter ID
     */
    public static function get_order_by_el_id($el_id) {
        $order_id = get_post_meta($el_id, '_el_order_id', true);
        
        if (!$order_id) {
            return null;
        }
        
        return wc_get_order($order_id);
    }
    
    /**
     * Get engagement letter ID by order ID
     */
    public static function get_el_by_order_id($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return null;
        }
        
        $el_id = $order->get_meta('_el_id');
        
        return $el_id ? absint($el_id) : null;
    }
}

// Initialize
EL_WooCommerce::get_instance();