<?php
/**
 * Tab 3: Cart Editor
 * Engagement Letter System
 */
?>

<div id="el-tab-3" class="el-tab-content">
    
    <div id="el-cart-container">
        
        <!-- Cart Header -->
        <div class="el-cart-header">
            <h3 class="el-cart-title">Engagement Letter Services</h3>
            <span class="el-cart-count">
                <?php echo WC()->cart->get_cart_contents_count(); ?> items
            </span>
        </div>
        
        <?php if (WC()->cart->is_empty()) : ?>
            
            <!-- Empty Cart Message -->
            <div class="el-empty-cart">
                <p>No services selected yet.</p>
                <button onclick="switchToTab(2)" class="el-btn el-btn-primary">
                    ← Select Template
                </button>
            </div>
            
        <?php else : ?>
            
            <!-- Cart Items -->
            <div class="el-cart-items">
                <?php
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                    $product_id = $cart_item['product_id'];
                    $product = $cart_item['data'];
                    $product_type = $product->get_type();
                    
                    // Get ACF fields for display
                    $el_title = get_field('el_title', $product_id) ?: $product->get_name();
                    $practice_area = get_field('practice_area', $product_id);
                    
                    // Check if this is the main engagement letter (first item)
                    $is_main = ($cart_item_key === array_key_first(WC()->cart->get_cart()));
                    ?>
                    
                    <div class="el-cart-item <?php echo $is_main ? 'el-main-item' : ''; ?>" 
                         data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                         data-product-type="<?php echo esc_attr($product_type); ?>">
                        
                        <!-- Item Header -->
                        <div class="el-cart-item-header">
                            <div>
                                <h4 class="el-cart-item-name">
                                    <?php echo esc_html($el_title); ?>
                                    <?php if ($is_main) : ?>
                                        <span class="el-main-badge">Main Template</span>
                                    <?php endif; ?>
                                </h4>
                                
                                <?php if ($practice_area) : ?>
                                    <span class="el-cart-item-meta">
                                        <?php echo esc_html($practice_area); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($product_type !== 'simple') : ?>
                                    <span class="el-cart-item-type">
                                        <?php echo esc_html(ucfirst($product_type)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="el-cart-item-price">
                                <?php echo WC()->cart->get_product_price($product); ?>
                            </div>
                        </div>
                        
                        <?php
                        // Handle different product types
                        switch ($product_type) :
                            
                            case 'bundle':
                                ?>
                                <!-- Bundle Components -->
                                <div class="el-bundled-items">
                                    <div class="el-bundled-items-title">
                                        Included Services:
                                    </div>
                                    
                                    <?php
                                    if (class_exists('WC_Product_Bundle')) :
                                        $bundle = new WC_Product_Bundle($product_id);
                                        $bundled_items = $bundle->get_bundled_items();
                                        
                                        foreach ($bundled_items as $bundled_item_id => $bundled_item) :
                                            $bundled_product = $bundled_item->get_product();
                                            $is_optional = $bundled_item->is_optional();
                                            $is_selected = true; // Default selected
                                            
                                            // Check if item is in cart bundle data
                                            if (isset($cart_item['bundled_items'])) {
                                                $is_selected = isset($cart_item['bundled_items'][$bundled_item_id]);
                                            }
                                            ?>
                                            
                                            <div class="el-bundled-item">
                                                <label>
                                                    <input type="checkbox" 
                                                           class="el-bundle-component"
                                                           data-bundle-key="<?php echo esc_attr($cart_item_key); ?>"
                                                           data-bundle-id="<?php echo esc_attr($product_id); ?>"
                                                           data-item-id="<?php echo esc_attr($bundled_item_id); ?>"
                                                           <?php checked($is_selected); ?>
                                                           <?php echo !$is_optional ? 'disabled' : ''; ?>>
                                                    
                                                    <span class="el-bundled-item-name">
                                                        <?php echo esc_html($bundled_product->get_name()); ?>
                                                        <?php if (!$is_optional) : ?>
                                                            <span class="el-required">(Required)</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    
                                                    <?php if ($bundled_item->is_priced_individually()) : ?>
                                                        <span class="el-bundled-item-price">
                                                            +<?php echo wc_price($bundled_product->get_price()); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                            
                                        <?php endforeach;
                                    endif; ?>
                                </div>
                                <?php
                                break;
                                
                            case 'composite':
                                ?>
                                <!-- Composite Components -->
                                <div class="el-composite-items">
                                    <div class="el-composite-title">
                                        Configuration Options:
                                    </div>
                                    
                                    <?php
                                    if (class_exists('WC_Product_Composite')) :
                                        $composite = new WC_Product_Composite($product_id);
                                        // Add composite handling here based on your setup
                                        echo '<p class="el-info">Edit composite options in the next step.</p>';
                                    endif;
                                    ?>
                                </div>
                                <?php
                                break;
                                
                            case 'variable':
                                ?>
                                <!-- Variable Product Options -->
                                <div class="el-variable-options">
                                    <?php
                                    $variations = $product->get_available_variations();
                                    if (!empty($variations)) :
                                        ?>
                                        <label>Select Option:</label>
                                        <select class="el-variation-select" 
                                                data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                            <?php foreach ($variations as $variation) : ?>
                                                <option value="<?php echo esc_attr($variation['variation_id']); ?>">
                                                    <?php echo esc_html(implode(', ', $variation['attributes'])); ?>
                                                    - <?php echo wc_price($variation['display_price']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <?php
                                break;
                                
                        endswitch;
                        ?>
                        
                        <!-- Quantity and Remove Controls -->
                        <div class="el-cart-item-controls">
                            <div class="el-qty-wrapper">
                                <label class="el-qty-label">Quantity:</label>
                                <input type="number" 
                                       class="el-qty-update" 
                                       data-key="<?php echo esc_attr($cart_item_key); ?>"
                                       value="<?php echo $cart_item['quantity']; ?>"
                                       min="<?php echo $is_main ? '1' : '0'; ?>"
                                       <?php echo $is_main ? 'readonly' : ''; ?>>
                            </div>
                            
                            <?php if (!$is_main) : ?>
                                <a href="#" class="el-remove-item" 
                                   data-key="<?php echo esc_attr($cart_item_key); ?>">
                                    Remove
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Line Total -->
                        <div class="el-line-total">
                            Line Total: <?php echo wc_price($cart_item['line_total']); ?>
                        </div>
                        
                    </div>
                    
                <?php endforeach; ?>
            </div>
            
            <!-- Add Additional Services Button -->
            <div class="el-add-services">
                <button class="el-btn el-btn-outline" onclick="switchToTab(2)">
                    + Add Additional Services
                </button>
            </div>
            
            <!-- Cart Totals -->
            <div class="el-cart-totals">
                <div class="el-cart-total-row">
                    <span class="el-cart-total-label">Subtotal:</span>
                    <span class="el-cart-subtotal">
                        <?php echo WC()->cart->get_cart_subtotal(); ?>
                    </span>
                </div>
                
                <?php if (WC()->cart->get_discount_total() > 0) : ?>
                    <div class="el-cart-total-row">
                        <span class="el-cart-total-label">Discount:</span>
                        <span class="el-cart-discount">
                            -<?php echo wc_price(WC()->cart->get_discount_total()); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php 
                // Check for deposit amount if using WooCommerce Deposits
                if (class_exists('WC_Deposits')) :
                    $deposit_amount = WC()->cart->get_meta('deposit_amount');
                    if ($deposit_amount) :
                        ?>
                        <div class="el-cart-total-row">
                            <span class="el-cart-total-label">Due Today (Deposit):</span>
                            <span class="el-cart-deposit">
                                <?php echo wc_price($deposit_amount); ?>
                            </span>
                        </div>
                        <div class="el-cart-total-row">
                            <span class="el-cart-total-label">Remaining Balance:</span>
                            <span class="el-cart-balance">
                                <?php echo wc_price(WC()->cart->get_total('edit') - $deposit_amount); ?>
                            </span>
                        </div>
                    <?php endif;
                endif;
                ?>
                
                <div class="el-cart-total-row el-total-final">
                    <span class="el-cart-total-label">Total:</span>
                    <span class="el-cart-total">
                        <?php echo WC()->cart->get_total(); ?>
                    </span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="el-cart-actions">
                <button id="el-preview-pdf-btn" class="el-btn el-btn-primary">
                    Preview Engagement Letter →
                </button>
            </div>
            
            <!-- Additional Options -->
            <div class="el-cart-options">
                <details class="el-additional-notes">
                    <summary>Add Internal Notes</summary>
                    <textarea id="el-internal-notes" 
                              placeholder="Add any internal notes about this engagement..."
                              rows="4"></textarea>
                </details>
                
                <details class="el-client-instructions">
                    <summary>Special Instructions for Client</summary>
                    <textarea id="el-client-instructions" 
                              placeholder="Add any special instructions that will appear in the engagement letter..."
                              rows="4"></textarea>
                </details>
            </div>
            
        <?php endif; ?>
        
    </div>
    
</div>