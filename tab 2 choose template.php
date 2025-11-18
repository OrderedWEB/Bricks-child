<?php
/**
 * Tab 2: Template Selection
 * Engagement Letter System
 */
?>

<div id="el-tab-2" class="el-tab-content">
    
    <!-- Filter Section -->
    <div class="el-filter-section">
        <label for="practice-area-filter" class="el-filter-label">
            Filter by Practice Area:
        </label>
        <select id="practice-area-filter">
            <option value="">All Practice Areas</option>
            <?php
            // Get all unique practice areas from products
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'el-templates', // Your engagement letter category
                    ),
                ),
                'meta_key' => 'practice_area',
                'meta_compare' => 'EXISTS'
            );
            
            $products = new WP_Query($args);
            $practice_areas = array();
            
            if ($products->have_posts()) {
                while ($products->have_posts()) {
                    $products->the_post();
                    $practice_area = get_field('practice_area', get_the_ID());
                    if ($practice_area && !in_array($practice_area, $practice_areas)) {
                        $practice_areas[] = $practice_area;
                    }
                }
                wp_reset_postdata();
            }
            
            sort($practice_areas);
            foreach ($practice_areas as $area) {
                echo '<option value="' . esc_attr(sanitize_title($area)) . '">' . esc_html($area) . '</option>';
            }
            ?>
        </select>
    </div>
    
    <!-- Template Grid -->
    <div id="el-template-grid">
        <?php
        // Query engagement letter products
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'el-templates', // Your engagement letter category
                ),
            ),
        );
        
        $products = new WP_Query($args);
        
        if ($products->have_posts()) :
            while ($products->have_posts()) : $products->the_post();
                
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                
                // Get ACF fields
                $el_title = get_field('el_title', $product_id) ?: get_the_title();
                $el_teaser = get_field('el_teaser', $product_id);
                $practice_area = get_field('practice_area', $product_id);
                $fee_structure_type = get_field('fee_structure_type', $product_id);
                $engagement_fee = get_field('engagement_fee_due_today', $product_id);
                $require_second_signer = get_field('require_second_signer', $product_id);
                
                // Get product type
                $product_type = $product->get_type();
                $product_type_label = '';
                
                switch($product_type) {
                    case 'bundle':
                        $product_type_label = 'Package';
                        break;
                    case 'composite':
                        $product_type_label = 'Configurable';
                        break;
                    case 'variable':
                        $product_type_label = 'Multiple Options';
                        break;
                    default:
                        $product_type_label = 'Standard';
                }
                
                // Prepare data attributes for filtering
                $practice_area_slug = sanitize_title($practice_area);
                ?>
                
                <div class="el-template-item" 
                     data-practice-area="<?php echo esc_attr($practice_area_slug); ?>"
                     data-product-type="<?php echo esc_attr($product_type); ?>">
                    
                    <!-- Practice Area Badge -->
                    <?php if ($practice_area) : ?>
                        <div class="el-template-practice">
                            <?php echo esc_html($practice_area); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Template Title -->
                    <h3 class="el-template-title">
                        <?php echo esc_html($el_title); ?>
                    </h3>
                    
                    <!-- Product Type Badge -->
                    <?php if ($product_type !== 'simple') : ?>
                        <span class="el-cart-item-type">
                            <?php echo esc_html($product_type_label); ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Template Description -->
                    <?php if ($el_teaser) : ?>
                        <div class="el-template-description">
                            <?php echo wp_kses_post($el_teaser); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Fee Information -->
                    <div class="el-template-fee-info">
                        <?php if ($engagement_fee) : ?>
                            <div class="el-template-fee">
                                <?php echo wc_price($engagement_fee); ?>
                                <span class="el-template-fee-label">Engagement Fee</span>
                            </div>
                        <?php elseif ($product->get_price()) : ?>
                            <div class="el-template-fee">
                                <?php echo $product->get_price_html(); ?>
                                <?php if ($fee_structure_type) : ?>
                                    <span class="el-template-fee-label">
                                        <?php echo esc_html($fee_structure_type); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="el-template-meta">
                        <?php if ($require_second_signer) : ?>
                            <div class="el-template-meta-item">
                                <span class="dashicons dashicons-groups"></span>
                                Two signers required
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product_type === 'bundle') : ?>
                            <?php
                            $bundle = new WC_Product_Bundle($product_id);
                            $bundled_items = $bundle->get_bundled_items();
                            $item_count = count($bundled_items);
                            ?>
                            <div class="el-template-meta-item">
                                <span class="dashicons dashicons-portfolio"></span>
                                <?php echo $item_count; ?> included services
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Select Button -->
                    <button class="el-select-template-btn" 
                            data-product-id="<?php echo esc_attr($product_id); ?>"
                            data-product-name="<?php echo esc_attr($el_title); ?>"
                            data-product-type="<?php echo esc_attr($product_type); ?>">
                        Select This Template
                    </button>
                    
                </div>
                
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            
            <div id="el-no-templates-message" style="display: block;">
                <p>No engagement letter templates found.</p>
                <p>Please contact your administrator to set up templates.</p>
            </div>
            
        <?php endif; ?>
    </div>
    
<!-- Hidden message for filtered results (FIXED: hidden by default) -->
    <div id="el-no-templates-message" style="display: none;">
        <p>No templates found for the selected practice area.</p>
        <p>Try selecting a different practice area or choose "All Practice Areas".</p>
    </div>
    
</div>