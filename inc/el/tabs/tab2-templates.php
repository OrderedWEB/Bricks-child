<?php
/**
 * Engagement Letter System - Tab 2: Template Selection
 * 
 * Handles engagement letter template selection:
 * - WooCommerce product grid display
 * - Practice area filtering
 * - Tag-based filtering
 * - Grouped products handling
 * - Add to cart functionality
 * 
 * LOAD ORDER: Tab module (after all core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php, woocommerce.php, grouped-products.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// PRODUCT GRID RENDERING
// ============================================

/**
 * Retrieves engagement letter templates (products)
 * 
 * @param array $args Query arguments
 * @return array Product IDs
 */
function el_get_templates($args = []) {
    $defaults = [
        'category' => 'el-templates',
        'practice_area' => '',
        'tag' => '',
        'limit' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $query_args = [
        'post_type' => 'product',
        'posts_per_page' => $args['limit'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $args['category'],
            ],
        ],
    ];
    
    // Filter by practice area (ACF field)
    if (!empty($args['practice_area'])) {
        $query_args['meta_query'] = [
            [
                'key' => EL_ACF_PRACTICE_AREA,
                'value' => $args['practice_area'],
                'compare' => '=',
            ],
        ];
    }
    
    // Filter by tag
    if (!empty($args['tag'])) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $args['tag'],
        ];
    }
    
    $query = new WP_Query($query_args);
    
    return wp_list_pluck($query->posts, 'ID');
}

/**
 * Renders template grid
 * 
 * @param array $args Display arguments
 * @return string HTML grid
 */
function el_render_template_grid($args = []) {
    $defaults = [
        'practice_area' => '',
        'tag' => '',
        'columns' => 3,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $template_ids = el_get_templates($args);
    
    if (empty($template_ids)) {
        return '<p class="el-no-templates">No templates found matching your criteria.</p>';
    }
    
    $output = '<div class="el-template-grid" style="display: grid; grid-template-columns: repeat(' . $args['columns'] . ', 1fr); gap: 20px; margin: 20px 0;">';
    
    foreach ($template_ids as $product_id) {
        $output .= el_render_template_card($product_id);
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Renders individual template card (tile design)
 * 
 * @param int $product_id Product ID
 * @return string HTML card
 */
function el_render_template_card($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return '';
    }
    
    $practice_area = get_field(EL_ACF_PRACTICE_AREA, $product_id);
    $pdf_description = get_field(EL_ACF_PDF_TEXT, $product_id);
    $is_grouped = $product->get_type() === 'grouped';
    $in_cart = el_is_product_in_cart($product_id);
    
    // Get product tags
    $tags = get_the_terms($product_id, 'product_tag');
    
    $output = '<div class="el-template-tile" data-product-id="' . $product_id . '" style="
        background: white;
        border: 2px solid ' . ($in_cart ? EL_COLOR_PRIMARY : '#e5e7eb') . ';
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    ">';
    
    // Practice area badge (top right)
    if ($practice_area) {
        $output .= '<div style="
            position: absolute;
            top: 16px;
            right: 16px;
            background: ' . EL_COLOR_BG_LIGHT . ';
            color: ' . EL_COLOR_NAVY . ';
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        ">' . esc_html($practice_area) . '</div>';
    }
    
    // Title
    $output .= '<h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; padding-right: 100px;">' . esc_html($product->get_name()) . '</h3>';
    
    // Tags (if any)
    if ($tags && !is_wp_error($tags)) {
        $output .= '<div class="el-template-tags" style="margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 6px;">';
        foreach ($tags as $tag) {
            $output .= '<span style="
                background: rgba(' . hexdec(substr(EL_COLOR_PRIMARY, 1, 2)) . ', ' . hexdec(substr(EL_COLOR_PRIMARY, 3, 2)) . ', ' . hexdec(substr(EL_COLOR_PRIMARY, 5, 2)) . ', 0.1);
                color: ' . EL_COLOR_PRIMARY . ';
                padding: 4px 10px;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 600;
            ">' . esc_html($tag->name) . '</span>';
        }
        $output .= '</div>';
    }
    
    // Short description (first line)
    if ($product->get_short_description()) {
        $output .= '<p style="font-size: 14px; color: #6b7280; margin-bottom: 15px; line-height: 1.5;">' . wp_trim_words($product->get_short_description(), 15) . '</p>';
    }
    
    // Toggle for PDF description
    if ($pdf_description) {
        $output .= '<div class="el-pdf-description-toggle" style="margin-bottom: 15px;">';
        $output .= '<button type="button" class="el-toggle-description" data-product-id="' . $product_id . '" style="
            background: none;
            border: none;
            color: ' . EL_COLOR_PRIMARY . ';
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        ">Show Full Description ▼</button>';
        
        $output .= '<div class="el-pdf-description-content" id="el-desc-' . $product_id . '" style="
            display: none;
            margin-top: 10px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.6;
            color: #374151;
        ">' . wp_kses_post($pdf_description) . '</div>';
        $output .= '</div>';
    }
    
    // Grouped products indicator
    if ($is_grouped) {
        $children = $product->get_children();
        $output .= '<p style="font-size: 13px; color: ' . EL_COLOR_PRIMARY . '; margin-bottom: 12px; font-weight: 600;">📦 Bundle: ' . count($children) . ' services</p>';
    }
    
    // Price removed per requirements
    
    // Button
    if ($in_cart) {
        $output .= '<button type="button" class="el-remove-template" data-product-id="' . $product_id . '" style="
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
        ">✓ Selected - Remove</button>';
    } else {
        if ($is_grouped) {
            $output .= '<button type="button" class="el-select-grouped" data-product-id="' . $product_id . '" style="
                width: 100%;
                background: ' . EL_COLOR_PRIMARY . ';
                color: white;
                border: none;
                padding: 12px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 15px;
            ">Select Services →</button>';
        } else {
            $output .= '<button type="button" class="el-add-template" data-product-id="' . $product_id . '" style="
                width: 100%;
                background: ' . EL_COLOR_PRIMARY . ';
                color: white;
                border: none;
                padding: 12px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 15px;
            ">Select Template</button>';
        }
    }
    
    $output .= '</div>';
    
    return $output;
}

// ============================================
// FILTERS
// ============================================

/**
 * Retrieves all practice areas
 * 
 * @return array Practice area names
 */
function el_get_practice_areas() {
    $template_ids = el_get_templates();
    $areas = [];
    
    foreach ($template_ids as $product_id) {
        $area = get_field(EL_ACF_PRACTICE_AREA, $product_id);
        if ($area && !in_array($area, $areas)) {
            $areas[] = $area;
        }
    }
    
    sort($areas);
    
    return $areas;
}

/**
 * Renders practice area filter
 * 
 * @return string HTML filter
 */
function el_render_practice_area_filter() {
    $areas = el_get_practice_areas();
    
    if (empty($areas)) {
        return '';
    }
    
    $output = '<div class="el-filter-practice-area" style="margin-bottom: 20px;">';
    $output .= '<label style="display: block; font-weight: 600; margin-bottom: 8px;">Filter by Practice Area:</label>';
    $output .= '<select id="elPracticeAreaFilter" style="padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; width: 100%; max-width: 300px;">';
    $output .= '<option value="">All Practice Areas</option>';
    
    foreach ($areas as $area) {
        $output .= '<option value="' . esc_attr($area) . '">' . esc_html($area) . '</option>';
    }
    
    $output .= '</select>';
    $output .= '</div>';
    
    return $output;
}

/**
 * Renders tag filter
 * 
 * @return string HTML filter
 */
function el_render_tag_filter() {
    // Get product tags from el-templates category
    $template_ids = el_get_templates();
    $tags = [];
    
    foreach ($template_ids as $product_id) {
        $product_tags = get_the_terms($product_id, 'product_tag');
        
        if ($product_tags && !is_wp_error($product_tags)) {
            foreach ($product_tags as $tag) {
                if (!isset($tags[$tag->slug])) {
                    $tags[$tag->slug] = $tag->name;
                }
            }
        }
    }
    
    if (empty($tags)) {
        return '';
    }
    
    $output = '<div class="el-filter-tags" style="margin-bottom: 20px;">';
    $output .= '<label style="display: block; font-weight: 600; margin-bottom: 8px;">Filter by Tag:</label>';
    $output .= '<div class="el-tag-buttons" style="display: flex; flex-wrap: wrap; gap: 8px;">';
    
    foreach ($tags as $slug => $name) {
        $output .= '<button type="button" class="el-tag-filter" data-tag="' . esc_attr($slug) . '" style="
            background: white;
            border: 2px solid ' . EL_COLOR_PRIMARY . ';
            color: ' . EL_COLOR_PRIMARY . ';
            padding: 6px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
        ">' . esc_html($name) . '</button>';
    }
    
    $output .= '</div>';
    $output .= '</div>';
    
    return $output;
}

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX handler: Add template to cart
 */
function el_ajax_add_template_to_cart() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    // Check if already in cart
    if (el_is_product_in_cart($product_id)) {
        wp_send_json_error(['message' => 'Template already in cart']);
    }
    
    // Add to cart
    $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
    
    if (!$cart_item_key) {
        wp_send_json_error(['message' => 'Failed to add to cart']);
    }
    
    // Save cart state
    if (function_exists('el_save_cart_state')) {
        el_save_cart_state();
    }
    
    // Store template selection in session
    el_set_session(EL_SESSION_SELECTED_TEMPLATE, $product_id);
    
    // Update engagement letter
    $engagement_id = el_get_current_engagement_id();
    if ($engagement_id) {
        el_set_meta($engagement_id, 'template_id', $product_id);
        el_set_meta($engagement_id, 'current_tab', 2);
    }
    
    $product = wc_get_product($product_id);
    
    wp_send_json_success([
        'message' => 'Template added to cart',
        'product_name' => $product->get_name(),
        'cart_count' => el_get_cart_count(),
    ]);
}
add_action('wp_ajax_' . EL_AJAX_ADD_TEMPLATE, 'el_ajax_add_template_to_cart');

/**
 * AJAX handler: Remove template from cart
 */
function el_ajax_remove_template_from_cart() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }
    
    if (!el_ensure_cart()) {
        wp_send_json_error(['message' => 'Cart not available']);
    }
    
    // Find cart item
    $cart_item = el_get_cart_item_by_product_id($product_id);
    
    if (!$cart_item) {
        wp_send_json_error(['message' => 'Product not in cart']);
    }
    
    // Remove from cart
    WC()->cart->remove_cart_item($cart_item['cart_item_key']);
    
    wp_send_json_success([
        'message' => 'Template removed from cart',
        'cart_count' => el_get_cart_count(),
    ]);
}
add_action('wp_ajax_el_remove_template_from_cart', 'el_ajax_remove_template_from_cart');

/**
 * AJAX handler: Load filtered templates
 */
function el_ajax_load_templates() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $practice_area = sanitize_text_field($_POST['practice_area'] ?? '');
    $tag = sanitize_text_field($_POST['tag'] ?? '');
    
    $html = el_render_template_grid([
        'practice_area' => $practice_area,
        'tag' => $tag,
    ]);
    
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_el_load_templates', 'el_ajax_load_templates');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: Template selection grid
 * 
 * Usage: [el_template_selection]
 */
function el_template_selection_shortcode($atts) {
    $atts = shortcode_atts([
        'practice_area' => '',
        'columns' => 3,
    ], $atts);
    
    if (!is_user_logged_in()) {
        return '<p>Please log in to select templates.</p>';
    }
    
    $output = '<div class="el-template-selection-wrapper">';
    
    // Filters
    $output .= '<div class="el-template-filters" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">';
    $output .= el_render_practice_area_filter();
    $output .= el_render_tag_filter();
    $output .= '</div>';
    
    // Grid container
    $output .= '<div id="elTemplateGridContainer">';
    $output .= el_render_template_grid($atts);
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_template_selection', 'el_template_selection_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

/**
 * Enqueues Tab 2 JavaScript
 */
function el_enqueue_tab2_script() {
    // Only load on engagement letter wizard page
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            var currentFilters = {
                practice_area: '',
                tag: ''
            };
            
            // Practice area filter
            $(document).on('change', '#elPracticeAreaFilter', function() {
                currentFilters.practice_area = $(this).val();
                loadFilteredTemplates();
            });
            
            // Tag filter
            $(document).on('click', '.el-tag-filter', function() {
                var tag = $(this).data('tag');
                
                // Toggle active state
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active').css({
                        'background': 'white',
                        'color': '" . EL_COLOR_PRIMARY . "'
                    });
                    currentFilters.tag = '';
                } else {
                    $('.el-tag-filter').removeClass('active').css({
                        'background': 'white',
                        'color': '" . EL_COLOR_PRIMARY . "'
                    });
                    $(this).addClass('active').css({
                        'background': '" . EL_COLOR_PRIMARY . "',
                        'color': 'white'
                    });
                    currentFilters.tag = tag;
                }
                
                loadFilteredTemplates();
            });
            
            // Load filtered templates
            function loadFilteredTemplates() {
                $('#elTemplateGridContainer').html('<div style=\"text-align: center; padding: 40px;\"><p>Loading templates...</p></div>');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_load_templates',
                        nonce: elAjax.nonce,
                        practice_area: currentFilters.practice_area,
                        tag: currentFilters.tag
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#elTemplateGridContainer').html(response.data.html);
                        } else {
                            $('#elTemplateGridContainer').html('<div style=\"text-align: center; padding: 40px;\"><p>Error loading templates.</p></div>');
                        }
                    },
                    error: function() {
                        $('#elTemplateGridContainer').html('<div style=\"text-align: center; padding: 40px;\"><p>Error loading templates.</p></div>');
                    }
                });
            }
            
            // Toggle description
            $(document).on('click', '.el-toggle-description', function(e) {
                e.stopPropagation();
                var productId = $(this).data('product-id');
                var content = $('#el-desc-' + productId);
                
                if (content.is(':visible')) {
                    content.slideUp(200);
                    $(this).html('Show Full Description ▼');
                } else {
                    content.slideDown(200);
                    $(this).html('Hide Description ▲');
                }
            });
            
            // Add template to cart
            $(document).on('click', '.el-add-template', function(e) {
                e.stopPropagation();
                var productId = $(this).data('product-id');
                var button = $(this);
                var tile = button.closest('.el-template-tile');
                
                button.prop('disabled', true).text('Adding...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_ADD_TEMPLATE . "',
                        nonce: elAjax.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            tile.css('border-color', '" . EL_COLOR_PRIMARY . "');
                            button.text('✓ Selected - Remove')
                                .removeClass('el-add-template')
                                .addClass('el-remove-template')
                                .css('background', '#dc2626');
                            
                            // Show success message briefly
                            var successMsg = $('<div style=\"position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\">✓ Template added</div>');
                            $('body').append(successMsg);
                            setTimeout(function() {
                                successMsg.fadeOut(function() { $(this).remove(); });
                            }, 2000);
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to add template'));
                            button.prop('disabled', false).text('Select Template');
                        }
                    },
                    error: function() {
                        alert('Network error. Please try again.');
                        button.prop('disabled', false).text('Select Template');
                    }
                });
            });
            
            // Remove template from cart
            $(document).on('click', '.el-remove-template', function(e) {
                e.stopPropagation();
                var productId = $(this).data('product-id');
                var button = $(this);
                var tile = button.closest('.el-template-tile');
                
                button.prop('disabled', true).text('Removing...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_remove_template_from_cart',
                        nonce: elAjax.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            tile.css('border-color', '#e5e7eb');
                            button.text('Select Template')
                                .removeClass('el-remove-template')
                                .addClass('el-add-template')
                                .css('background', '" . EL_COLOR_PRIMARY . "')
                                .prop('disabled', false);
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to remove template'));
                            button.prop('disabled', false).text('✓ Selected - Remove');
                        }
                    },
                    error: function() {
                        alert('Network error. Please try again.');
                        button.prop('disabled', false).text('✓ Selected - Remove');
                    }
                });
            });
            
       
            // Select grouped product
$(document).on('click', '.el-select-grouped', function(e) {
    e.stopPropagation();
    
    console.log('🔵 GROUPED CLICKED!');
    
    var productId = $(this).data('product-id');
    var button = $(this);
    
    console.log('Product ID:', productId);
    console.log('elAjax:', elAjax);
    
    button.prop('disabled', true).text('Processing...');
    
    $.ajax({
        url: elAjax.ajaxUrl,
        type: 'POST',
        data: {
            action: 'el_store_grouped_parent',
            nonce: elAjax.nonce,
            product_id: productId
        },
        success: function(response) {
            console.log('✅ Response:', response);
            
            if (response.success) {
                $('" . el_get_tab_selector(3) . "').click();
            } else {
                alert('Error: ' + (response.data.message || 'Failed to select bundle'));
                button.prop('disabled', false).text('Select Services →');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            
            alert('Network error. Please try again.');
            button.prop('disabled', false).text('Select Services →');
        }
    });
});
            // Tile hover effects
            $(document).on('mouseenter', '.el-template-tile', function() {
                if (!$(this).find('button').prop('disabled')) {
                    $(this).css({
                        'transform': 'translateY(-4px)',
                        'box-shadow': '0 8px 16px rgba(0, 0, 0, 0.1)'
                    });
                }
            }).on('mouseleave', '.el-template-tile', function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': 'none'
                });
            });
            
            // Button hover effects
            $(document).on('mouseenter', '.el-add-template, .el-select-grouped', function() {
                if (!$(this).prop('disabled')) {
                    $(this).css({
                        'transform': 'scale(1.02)',
                        'box-shadow': '0 4px 8px rgba(0, 0, 0, 0.15)'
                    });
                }
            }).on('mouseleave', '.el-add-template, .el-select-grouped', function() {
                $(this).css({
                    'transform': 'scale(1)',
                    'box-shadow': 'none'
                });
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab2_script');

/**
 * Enqueues Tab 2 CSS
 */
function el_enqueue_tab2_css() {
    // Only load on engagement letter wizard page
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_style('wp-block-library', "
        .el-template-tile {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .el-template-tile button {
            transition: all 0.2s ease !important;
        }
        
        .el-pdf-description-content {
            transition: all 0.3s ease !important;
        }
        
        .el-toggle-description:hover {
            opacity: 0.8;
        }
        
        .el-tag-filter {
            transition: all 0.2s ease !important;
        }
        
        .el-tag-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        #elPracticeAreaFilter {
            transition: border-color 0.2s ease;
        }
        
        #elPracticeAreaFilter:focus {
            outline: none;
            border-color: " . EL_COLOR_PRIMARY . ";
            box-shadow: 0 0 0 3px rgba(168, 188, 206, 0.1);
        }
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab2_css');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Tab 2 (Template Selection) module loaded successfully', 'info');
}