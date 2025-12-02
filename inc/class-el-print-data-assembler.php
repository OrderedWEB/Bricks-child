<?php
/**
 * Engagement Letter Print Data Assembler
 * 
 * Assembles all data needed for PDF generation from:
 * - Session data (client info, grouped parent)
 * - WooCommerce cart (services/products)
 * - ACF fields (content, pricing)
 * - Engagement letter post meta
 * - Processes merge tags when include_user_data is true
 * 
 * Supports:
 * - Grouped products (parent content, child pricing)
 * - Item types (mandatory, suggested, optional, hide)
 * - Merge tag replacement with dotted placeholders
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class EL_Print_Data_Assembler {
    
    /**
     * Merge tag pattern for replacement {UPPERCASE}
     */
    const MERGE_TAG_PATTERN = '/\{([A-Z_]+)\}/';
    
    /**
     * Gravity Forms merge tag pattern {{lowercase}}
     */
    const GF_MERGE_TAG_PATTERN = '/\{\{([a-z_]+)\}\}/';
    
    /**
     * Dotted line placeholder for unfilled fields
     */
    const DOTTED_PLACEHOLDER = '............................';
    
    /**
     * Short dotted placeholder for smaller fields
     */
    const DOTTED_SHORT = '....................';
    
    /**
     * Item type sort order (lower = first)
     */
    const ITEM_TYPE_ORDER = [
        'mandatory' => 1,
        'suggested' => 2,
        'optional'  => 3,
    ];
    
    /**
     * Assemble complete data for PDF generation
     * 
     * @param bool $include_user_data Whether to include actual user data or use dotted placeholders
     * @return array|WP_Error Complete data array or error
     */
    public static function assemble_data($include_user_data = true) {
        // Start session if needed
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        
        error_log('📦 EL Data Assembler: Starting data assembly (include_user_data: ' . ($include_user_data ? 'true' : 'false') . ')');
        
        // Get client data from session (handles placeholders internally)
        $session_data = self::get_session_data($include_user_data);
        
        // Get cart items
        if (!function_exists('WC') || !WC()->cart) {
            error_log('❌ WooCommerce cart not available');
            return new WP_Error('no_cart', 'Cart not available');
        }
        
        // Ensure cart is loaded from session
        WC()->cart->get_cart_from_session();
        $cart_items = WC()->cart->get_cart();
        
        if (empty($cart_items)) {
            error_log('❌ Cart is empty');
            return new WP_Error('empty_cart', 'No services in cart');
        }
        
        error_log('📦 Processing ' . count($cart_items) . ' items from cart');
        
        // Get boilerplate from options
        $boilerplate = self::get_boilerplate();
        
   // Check for grouped product parent in WooCommerce session
        $grouped_parent = null;
        if (function_exists('el_get_grouped_parent')) {
            $grouped_parent = el_get_grouped_parent();
        }
        $grouped_parent_id = $grouped_parent['id'] ?? null;
        
        if ($grouped_parent_id) {
            error_log('✅ Found grouped parent ID in session: ' . $grouped_parent_id);
        }
        
        // Initialize data structure
        $data = [
            'reference' => self::generate_reference(),
            'date' => current_time('F j, Y'),
            'timestamp' => current_time('mysql'),
            'client' => $session_data['client'],
            'form_data' => $session_data['form_data'],
            'boilerplate' => $boilerplate,
            'products' => [],
            'services' => [], // Alias for backwards compatibility
            'totals' => [
                'engagement_fee' => 0,
                'expected_cost' => 0,
                'product_count' => 0,
            ],
            'meta' => [
                'include_user_data' => $include_user_data,
                'has_grouped_parent' => !empty($grouped_parent_id),
                'grouped_parent_id' => $grouped_parent_id,
            ],
            'lawyer' => [
                'email' => wp_get_current_user()->user_email ?: '',
                'name' => wp_get_current_user()->display_name ?: '',
            ],
        ];
        
        // Process each cart item
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_data = self::process_cart_item($cart_item, $data, $include_user_data, $grouped_parent_id, $grouped_parent_data);
            
            if ($product_data) {
                $data['products'][] = $product_data;
                $data['totals']['engagement_fee'] += $product_data['fees']['engagement_fee'];
                $data['totals']['expected_cost'] += $product_data['fees']['expected_cost'];
                $data['totals']['product_count']++;
            }
        }
        // Add parent product content for grouped products
        if ($grouped_parent_id) {
            // Try to get parent data from session first
            $parent_content = $grouped_parent_data;
            
            // If not in session, fetch from ACF
            if (empty($parent_content)) {
                $parent_content = self::get_product_acf_data($grouped_parent_id);
            }
            
            // Get parent product for name fallback
            $parent_product = wc_get_product($grouped_parent_id);
            $parent_name = $parent_product ? $parent_product->get_name() : '';
            
            $data['parent'] = [
                'id' => $grouped_parent_id,
                'title' => $parent_content['pdf_el_title'] ?: $parent_name,
                'subtitle' => $parent_content['pdf_el_subtitle'] ?? '',
                'introduction' => $parent_content['el_introduction_texts'] ?? '',
                'description' => $parent_content['pdf_el_text'] ?? '',
                'client_fillable_text' => $parent_content['client_fillable_pdf_text'] ?? '',
                'service_plan_table' => $parent_content['service_plan_table'] ?? get_field('service_plan_table', $grouped_parent_id) ?? '',
                'footer_notes' => $parent_content['pdf_footer_notes'] ?? '',
                'clauses' => self::process_repeater_field($parent_content['pdf_clauses'] ?? []),
                'annexes' => self::process_repeater_field($parent_content['pdf_annexes'] ?? []),
            ];
            
            // Apply merge tags to parent content
            if ($include_user_data) {
                $replacements = self::build_merge_tag_replacements($data);
                foreach (['title', 'subtitle', 'introduction', 'description', 'client_fillable_text', 'service_plan_table', 'footer_notes'] as $field) {
                    if (!empty($data['parent'][$field])) {
                        $data['parent'][$field] = self::replace_merge_tags($data['parent'][$field], $replacements);
                    }
                }
            }
            
            error_log('✅ Added parent content: ' . $data['parent']['title']);
        }
        
        // Sort products by item_type (mandatory first, then suggested, then optional)
        // Sort products by item_type (mandatory first, then suggested, then optional)
        usort($data['products'], function($a, $b) {
            $order_a = self::ITEM_TYPE_ORDER[$a['item_type']] ?? 99;
            $order_b = self::ITEM_TYPE_ORDER[$b['item_type']] ?? 99;
            return $order_a - $order_b;
        });
        // DEBUG: Output to browser console
        add_action('wp_footer', function() use ($grouped_parent_id, $data) {
            ?>
            <script>
                console.log('🔍 EL Assembler Debug:');
                console.log('  grouped_parent_id:', <?php echo json_encode($grouped_parent_id); ?>);
                console.log('  parent exists:', <?php echo isset($data['parent']) ? 'true' : 'false'; ?>);
                <?php if (isset($data['parent'])): ?>
                console.log('  parent title:', <?php echo json_encode($data['parent']['title'] ?? 'EMPTY'); ?>);
                console.log('  parent intro:', <?php echo json_encode(substr($data['parent']['introduction'] ?? 'EMPTY', 0, 100)); ?>);
                console.log('  parent description:', <?php echo json_encode(substr($data['parent']['description'] ?? 'EMPTY', 0, 100)); ?>);
                <?php endif; ?>
            </script>
            <?php
        }, 9999);
        // Copy to services for backwards compatibility
        $data['services'] = $data['products'];
        $data['service_count'] = count($data['products']);
        
        // Apply merge tags to boilerplate
        $data['boilerplate'] = self::process_boilerplate_merge_tags($data['boilerplate'], $data);
        
        // Add formatted totals
        $data['totals']['engagement_fee_formatted'] = wc_price($data['totals']['engagement_fee']);
        $data['totals']['expected_cost_formatted'] = wc_price($data['totals']['expected_cost']);
        
        // Also add totals at root level for backwards compatibility
        $data['total_engagement_fee'] = $data['totals']['engagement_fee'];
        $data['total_expected_cost'] = $data['totals']['expected_cost'];
        $data['pricing_source'] = $grouped_parent_id ? 'grouped_parent' : 'cart_sum';
        
        // Add boilerplate fields at root level for backwards compatibility
        $data['letterhead'] = $boilerplate['letterhead'];
        $data['opening_left'] = $boilerplate['opening_top_left'];
        $data['opening_right'] = $boilerplate['opening_top_right'];
        $data['footer_boilerplate'] = $boilerplate['footer_content'];
        $data['signature_block'] = $boilerplate['signature_block'];
        $data['firm_footer'] = $boilerplate['firm_footer'];
        
        if (empty($data['products'])) {
            error_log('❌ No products after processing (all hidden?)');
            return new WP_Error('no_services', 'No visible services in cart');
        }
        
        error_log('✅ Data assembly complete. Products: ' . count($data['products']));
        
        return $data;
    }
    
    /**
     * Get session data (client info and form data)
     * 
     * @param bool $include_user_data Whether to use actual data or placeholders
     * @return array Session data with client and form_data keys
     */
    private static function get_session_data($include_user_data) {
        if ($include_user_data) {
            // Get form data from session (saved in Tab 1)
            $form_data = $_SESSION['el_form_data'] ?? [];
            
            // Use session form data if available
          // Use session form data if available
            if (!empty($form_data)) {
                $client_data = [
                    'first_name' => $form_data['first_name'] ?? '',
                    'last_name' => $form_data['last_name'] ?? '',
                    'name' => $form_data['full_name'] ?? trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
                    'email' => $form_data['email'] ?? '',
                    'phone' => $form_data['phone'] ?? '',
                    'company' => $form_data['company'] ?? '',
                    'address' => [
                        'line_1' => $form_data['street_address'] ?? '',
                        'line_2' => $form_data['address_2'] ?? '',
                        'city' => $form_data['city'] ?? '',
                        'state' => $form_data['state'] ?? '',
                        'postcode' => $form_data['zip'] ?? '',
                        'country' => $form_data['country'] ?? '',
                    ],
                    // Also store flat address for backwards compatibility
                    'street_address' => $form_data['street_address'] ?? '',
                    'city' => $form_data['city'] ?? '',
                    'state' => $form_data['state'] ?? '',
                    'zip' => $form_data['zip'] ?? '',
                    'country' => $form_data['country'] ?? '',
                ];
                
                // Build full_address string from components
                $client_data['full_address'] = implode(', ', array_filter([
                    $client_data['address']['line_1'],
                    $client_data['address']['line_2'],
                    $client_data['address']['city'],
                    $client_data['address']['state'],
                    $client_data['address']['postcode'],
                    $client_data['address']['country'],
                ]));
                
            } else {
                // Fallback to WooCommerce customer if no session data
                $customer = WC()->customer;
                if ($customer) {
                    $client_data = [
                        'first_name' => $customer->get_billing_first_name(),
                        'last_name' => $customer->get_billing_last_name(),
                        'name' => trim($customer->get_billing_first_name() . ' ' . $customer->get_billing_last_name()),
                        'email' => $customer->get_billing_email(),
                        'phone' => $customer->get_billing_phone(),
                        'company' => $customer->get_billing_company(),
                        'address' => [
                            'line_1' => $customer->get_billing_address_1(),
                            'line_2' => $customer->get_billing_address_2(),
                            'city' => $customer->get_billing_city(),
                            'state' => $customer->get_billing_state(),
                            'postcode' => $customer->get_billing_postcode(),
                            'country' => $customer->get_billing_country(),
                        ],
                    ];
                    
                    // Build full_address string from components
                    $client_data['full_address'] = implode(', ', array_filter([
                        $client_data['address']['line_1'],
                        $client_data['address']['line_2'],
                        $client_data['address']['city'],
                        $client_data['address']['state'],
                        $client_data['address']['postcode'],
                        $client_data['address']['country'],
                    ]));
                    
                } else {
                    // No data available - use empty
                    $client_data = self::get_empty_client_data();
                }
            }
            
            return [
                'client' => $client_data,
                'form_data' => $form_data,
            ];
            
        } else {
            // Blank placeholders for template/paper version
            $long_dots = str_repeat('.', 30);
            $short_dots = str_repeat('.', 20);
            
            $client_data = [
                'first_name' => $short_dots,
                'last_name' => $short_dots,
                'name' => $long_dots,
                'email' => $long_dots,
                'phone' => $short_dots,
                'company' => $long_dots,
                'address' => [
                    'line_1' => $long_dots,
                    'line_2' => '',
                    'city' => $short_dots,
                    'state' => $short_dots,
                    'postcode' => $short_dots,
                    'country' => $short_dots,
                ],
                'full_address' => $long_dots,
                'street_address' => $long_dots,
                'city' => $short_dots,
                'state' => $short_dots,
                'zip' => $short_dots,
                'country' => $short_dots,
            ];
            
            return [
                'client' => $client_data,
                'form_data' => [],
            ];
        }
    }
    
    /**
     * Get empty client data structure
     * 
     * @return array Empty client data
     */
    private static function get_empty_client_data() {
        return [
            'first_name' => '',
            'last_name' => '',
            'name' => '',
            'email' => '',
            'phone' => '',
            'company' => '',
            'address' => [
                'line_1' => '',
                'line_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => '',
            ],
            'full_address' => '',
        ];
    }
    
    /**
     * Get boilerplate content from ACF options
     * 
     * @return array Boilerplate content
     */
    private static function get_boilerplate() {
        return [
            'letterhead' => get_field('boilerplate_letterhead', 'option') ?: '',
            'opening_top_left' => get_field('boilerplate_opening_tl', 'option') ?: '',
            'opening_top_right' => get_field('boilerplate_opening_tr_copy', 'option') ?: '',
            'footer_content' => get_field('footer_boilerplate', 'option') ?: '',
            'firm_footer' => get_field('firm_footer', 'option') ?: '',
            'signature_block' => get_field('signature_block_template', 'option') ?: '',
        ];
    }
    
    /**
     * Process a single cart item into product data
     * 
     * GROUPED PRODUCT LOGIC:
     * - If is_grouped_child: Use PARENT's ACF content fields, but CHILD's pricing
     * - Parent ACF data comes from: session > cart item > direct lookup
     * - Fees always come from the actual child product
     * 
     * @param array $cart_item WooCommerce cart item
     * @param array $data Current assembled data (for merge tags)
     * @param bool $include_user_data Whether to process merge tags
     * @param int|null $session_parent_id Grouped parent ID from session
     * @param array $session_parent_data Grouped parent ACF data from session
     * @return array|null Product data or null if hidden/invalid
     */
    private static function process_cart_item($cart_item, $data, $include_user_data, $session_parent_id = null, $session_parent_data = []) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'] ?? 0;
        $quantity = $cart_item['quantity'];
        
        if (!$product) {
            error_log('⚠️ Product not found for cart item');
            return null;
        }
        
        // Get item type (mandatory/suggested/optional/hide)
        $item_type = $cart_item['el_item_type'] ?? 'optional';
        
        error_log('📋 Processing: ' . $product->get_name() . ' (Type: ' . $item_type . ')');
        
        // SKIP hidden items completely
        if ($item_type === 'hide') {
            error_log('⏭️ Skipping hidden item: ' . $product->get_name());
            return null;
        }
        
        // Check if this is a grouped product parent
        $is_grouped_parent = isset($cart_item['is_grouped_parent']) && $cart_item['is_grouped_parent'];
        
        // Check if this is a grouped product child
        $is_grouped_child = isset($cart_item['is_grouped_child']) && $cart_item['is_grouped_child'];
        $grouped_parent_id = $cart_item['grouped_parent_id'] ?? $session_parent_id;
        
        // Determine which product ID to use for ACF fields
        // CONTENT: From parent (for grouped children) or from product itself
        // PRICING: Always from actual product/variation
        if ($is_grouped_child && $grouped_parent_id) {
            $content_acf_id = $grouped_parent_id;
            $pricing_acf_id = $variation_id ?: $product_id;
            error_log('  → Grouped child: Content from parent #' . $grouped_parent_id . ', pricing from #' . $pricing_acf_id);
        } else if ($variation_id) {
            $content_acf_id = $variation_id;
            $pricing_acf_id = $variation_id;
        } else {
            $content_acf_id = $product_id;
            $pricing_acf_id = $product_id;
        }
        
        // Get fees (always from the actual product/variation for correct pricing)
        $engagement_fee = floatval(get_field('engagement_fee', $pricing_acf_id) ?: get_field('engagement_fee_due_today', $pricing_acf_id) ?: $product->get_price());
        $expected_cost = floatval(get_field('expected_total_cost', $pricing_acf_id) ?: 0);
        
        // Get PDF-layer content fields with grouped product inheritance
        $content_fields = self::get_content_fields($cart_item, $content_acf_id, $product, $is_grouped_child, $session_parent_data);
        
        // Build product data structure
        $product_data = [
            'id' => $product_id,
            'variation_id' => $variation_id,
            'acf_id' => $content_acf_id,
            'pricing_acf_id' => $pricing_acf_id,
            'cart_item_key' => $cart_item['key'] ?? '',
            'quantity' => $quantity,
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            
            // Item type for display (mandatory gets pre-checked checkbox, etc.)
            'item_type' => $item_type,
            
            // Grouped product flags
            'is_grouped_parent' => $is_grouped_parent,
            'is_grouped_child' => $is_grouped_child,
            'grouped_parent_id' => $grouped_parent_id,
            'grouped_parent_name' => $cart_item['grouped_parent_name'] ?? '',
            
            // PDF Content (from parent for grouped children)
            'content' => [
                'title' => $content_fields['pdf_el_title'],
                'subtitle' => $content_fields['pdf_el_subtitle'],
                'introduction' => $content_fields['el_introduction_texts'],
                'body_text' => $content_fields['pdf_el_text'],
                'client_fillable_text' => $content_fields['client_fillable_pdf_text'],
                'footer_notes' => $content_fields['pdf_footer_notes'],
                'fee_structure' => $content_fields['fee_structure'],
            ],
            
            // Structured content (from parent for grouped children)
            'clauses' => self::process_repeater_field($content_fields['pdf_clauses']),
            'annexes' => self::process_repeater_field($content_fields['pdf_annexes']),
            
            // Fees (from actual child product for correct pricing)
            'fees' => [
                'engagement_fee' => $engagement_fee * $quantity,
                'expected_cost' => $expected_cost * $quantity,
                'unit_engagement_fee' => $engagement_fee,
                'unit_expected_cost' => $expected_cost,
                'engagement_fee_formatted' => wc_price($engagement_fee * $quantity),
                'expected_cost_formatted' => wc_price($expected_cost * $quantity),
            ],
            
            // Backwards compatibility - flat fields
            'pdf_title' => $content_fields['pdf_el_title'],
            'pdf_subtitle' => $content_fields['pdf_el_subtitle'],
            'pdf_text' => $content_fields['pdf_el_text'],
            'pdf_introduction' => $content_fields['el_introduction_texts'],
            'pdf_footer' => $content_fields['pdf_footer_notes'],
            'pdf_clauses' => $content_fields['pdf_clauses'],
            'pdf_annexes' => $content_fields['pdf_annexes'],
            'client_fillable_text' => $content_fields['client_fillable_pdf_text'],
            'practice_area' => $content_fields['practice_area'],
            'service_type' => $content_fields['service_type'],
            'fee_structure' => $content_fields['fee_structure'],
            'engagement_fee' => $engagement_fee * $quantity,
            'expected_cost' => $expected_cost * $quantity,
            
            // Metadata
            'meta' => [
                'categories' => self::get_product_categories($content_acf_id),
                'practice_area' => $content_fields['practice_area'],
                'engagement_type' => get_field('engagement_type', $content_acf_id) ?: '',
            ],
        ];
        
        // Apply merge tags if including user data
        if ($include_user_data) {
            $product_data['content'] = self::process_content_merge_tags($product_data['content'], $data);
            $product_data['clauses'] = self::process_clauses_merge_tags($product_data['clauses'], $data);
            $product_data['annexes'] = self::process_clauses_merge_tags($product_data['annexes'], $data);
        }
        
        return $product_data;
    }
    
    /**
     * Get content fields with grouped product inheritance
     * 
     * Priority for grouped children:
     * 1. Session parent data (best - pre-loaded)
     * 2. Cart item parent_acf_data (cached when added to cart)
     * 3. Direct lookup from parent product
     * 4. Fallback to child product fields
     * 
     * @param array $cart_item Cart item
     * @param int $content_acf_id ACF ID to use for content
     * @param WC_Product $product Product object
     * @param bool $is_grouped_child Whether this is a grouped child
     * @param array $session_parent_data Parent data from session
     * @return array Content fields
     */
    private static function get_content_fields($cart_item, $content_acf_id, $product, $is_grouped_child, $session_parent_data) {
        // Define all content fields we need
        $field_keys = [
            'pdf_el_title',
            'pdf_el_subtitle', 
            'pdf_el_text',
            'client_fillable_pdf_text',
            'el_introduction_texts',
            'pdf_footer_notes',
            'fee_structure',
            'pdf_clauses',
            'pdf_annexes',
            'practice_area',
            'service_type',
        ];
        
        $fields = [];
        
        // Source 1: Session parent data (for grouped children)
        if ($is_grouped_child && !empty($session_parent_data)) {
            error_log('  → Using session parent data for content');
            foreach ($field_keys as $key) {
                $fields[$key] = $session_parent_data[$key] ?? null;
            }
        }
        // Source 2: Cart item parent_acf_data (for grouped children)
        elseif ($is_grouped_child && !empty($cart_item['parent_acf_data'])) {
            error_log('  → Using cart item parent_acf_data for content');
            $parent_acf = $cart_item['parent_acf_data'];
            foreach ($field_keys as $key) {
                $fields[$key] = $parent_acf[$key] ?? null;
            }
        }
        // Source 3 & 4: Direct ACF lookup
        else {
            error_log('  → Direct ACF lookup from #' . $content_acf_id);
        }
        
        // Fill in any missing fields from ACF
        foreach ($field_keys as $key) {
            if (!isset($fields[$key]) || $fields[$key] === null || $fields[$key] === '') {
                $acf_value = get_field($key, $content_acf_id);
                $fields[$key] = $acf_value ?: '';
            }
        }
        
        // Special handling for arrays
        if (!is_array($fields['pdf_clauses'])) {
            $fields['pdf_clauses'] = [];
        }
        if (!is_array($fields['pdf_annexes'])) {
            $fields['pdf_annexes'] = [];
        }
        
        // Fallback for title
        if (empty($fields['pdf_el_title'])) {
            $fields['pdf_el_title'] = $product->get_name();
        }
        
        return $fields;
    }
    
    /**
     * Process repeater field into standardized array
     * 
     * @param array|null $repeater_data Raw repeater data from ACF
     * @return array Processed repeater items
     */
    private static function process_repeater_field($repeater_data) {
        if (empty($repeater_data) || !is_array($repeater_data)) {
            return [];
        }
        
        $processed = [];
        $index = 1;
        
        foreach ($repeater_data as $item) {
            $processed[] = [
                'index' => $index,
                'title' => $item['title'] ?? $item['clause_title'] ?? '',
                'body' => $item['body'] ?? $item['clause_body'] ?? $item['content'] ?? '',
                'page_break_before' => !empty($item['page_break_before']),
            ];
            $index++;
        }
        
        return $processed;
    }
    
    /**
     * Get product categories
     * 
     * @param int $product_id Product ID
     * @return array Category names
     */
    private static function get_product_categories($product_id) {
        $categories = get_the_terms($product_id, 'product_cat');
        
        if (empty($categories) || is_wp_error($categories)) {
            return [];
        }
        
        return array_map(function($cat) {
            return $cat->name;
        }, $categories);
    }
    
    /**
     * Generate unique reference ID
     * 
     * @return string Reference ID (e.g., EL-20231025-A3B2C1)
     */
    private static function generate_reference() {
        // Check for existing reference in session
        $existing_ref = $_SESSION['el_pdf_reference'] ?? null;
        
        if ($existing_ref) {
            return $existing_ref;
        }
        
        // Generate new reference
        $reference = sprintf(
            'EL-%s-%s',
            date('Ymd'),
            strtolower(substr(md5(uniqid(rand(), true)), 0, 6))
        );
        
        // Store in session
        $_SESSION['el_pdf_reference'] = $reference;
        
        return $reference;
    }
    
    /**
     * Process merge tags in content array
     * 
     * @param array $content Content array (title, body, etc.)
     * @param array $data Full data array for replacements
     * @return array Processed content
     */
    private static function process_content_merge_tags($content, $data) {
        $replacements = self::build_merge_tag_replacements($data);
        
        foreach ($content as $key => $value) {
            if (is_string($value)) {
                $content[$key] = self::replace_merge_tags($value, $replacements);
            }
        }
        
        return $content;
    }
    
    /**
     * Process merge tags in clauses/annexes
     * 
     * @param array $items Clauses or annexes array
     * @param array $data Full data array for replacements
     * @return array Processed items
     */
    private static function process_clauses_merge_tags($items, $data) {
        $replacements = self::build_merge_tag_replacements($data);
        
        foreach ($items as &$item) {
            if (isset($item['title'])) {
                $item['title'] = self::replace_merge_tags($item['title'], $replacements);
            }
            if (isset($item['body'])) {
                $item['body'] = self::replace_merge_tags($item['body'], $replacements);
            }
        }
        
        return $items;
    }
    
    /**
     * Process merge tags in boilerplate content
     * 
     * @param array $boilerplate Boilerplate array
     * @param array $data Full data array for replacements
     * @return array Processed boilerplate
     */
    private static function process_boilerplate_merge_tags($boilerplate, $data) {
        $replacements = self::build_merge_tag_replacements($data);
        
        foreach ($boilerplate as $key => $value) {
            if (is_string($value)) {
                $boilerplate[$key] = self::replace_merge_tags($value, $replacements);
            }
        }
        
        return $boilerplate;
    }
    
    /**
     * Build merge tag replacement map
     * 
     * @param array $data Full assembled data
     * @return array Key => value replacements
     */
    private static function build_merge_tag_replacements($data) {
        $client = $data['client'] ?? [];
        $form_data = $data['form_data'] ?? [];
        $totals = $data['totals'] ?? [];
        $meta = $data['meta'] ?? [];
        
        // Handle address - can be array or string
        $address = $client['address'] ?? [];
        if (is_array($address)) {
            $address_parts = array_filter([
                $address['line_1'] ?? '',
                $address['line_2'] ?? '',
                $address['city'] ?? '',
                $address['state'] ?? '',
                $address['postcode'] ?? '',
                $address['country'] ?? '',
            ]);
            $full_address = implode(', ', $address_parts);
        } else {
            $full_address = $address;
            $address = [
                'line_1' => '',
                'line_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => '',
            ];
        }
        
        $replacements = [
            // Client fields - uppercase format {CLIENT_NAME}
            '{CLIENT_NAME}' => $client['name'] ?? '',
            '{CLIENT_FIRST_NAME}' => $client['first_name'] ?? '',
            '{CLIENT_LAST_NAME}' => $client['last_name'] ?? '',
            '{CLIENT_EMAIL}' => $client['email'] ?? '',
            '{CLIENT_PHONE}' => $client['phone'] ?? '',
            '{CLIENT_COMPANY}' => $client['company'] ?? '',
            '{CLIENT_ADDRESS}' => $full_address,
            '{CLIENT_ADDRESS_1}' => $address['line_1'] ?? '',
            '{CLIENT_ADDRESS_2}' => $address['line_2'] ?? '',
            '{CLIENT_CITY}' => $address['city'] ?? '',
            '{CLIENT_STATE}' => $address['state'] ?? '',
            '{CLIENT_POSTCODE}' => $address['postcode'] ?? '',
            '{CLIENT_COUNTRY}' => $address['country'] ?? '',
            
            // Gravity Forms format - lowercase double braces {{field}}
            '{{first_name}}' => $client['first_name'] ?? '',
            '{{last_name}}' => $client['last_name'] ?? '',
            '{{full_name}}' => $client['name'] ?? '',
            '{{email}}' => $client['email'] ?? '',
            '{{phone}}' => $client['phone'] ?? '',
            '{{company}}' => $client['company'] ?? '',
            '{{street_address}}' => $address['line_1'] ?? $client['street_address'] ?? '',
            '{{address_line_1}}' => $address['line_1'] ?? '',
            '{{address_line_2}}' => $address['line_2'] ?? '',
            '{{city}}' => $address['city'] ?? $client['city'] ?? '',
            '{{state}}' => $address['state'] ?? $client['state'] ?? '',
            '{{zip}}' => $address['postcode'] ?? $client['zip'] ?? '',
            '{{postcode}}' => $address['postcode'] ?? '',
            '{{country}}' => $address['country'] ?? $client['country'] ?? '',
            '{{cosigner_first_name}}' => $form_data['cosigner_first_name'] ?? '',
            '{{cosigner_last_name}}' => $form_data['cosigner_last_name'] ?? '',
            
            // Document fields
            '{DATE}' => $data['date'] ?? date('F j, Y'),
            '{REFERENCE}' => $data['reference'] ?? '',
            '{TODAY}' => $data['date'] ?? date('F j, Y'),
            '{{date}}' => $data['date'] ?? date('F j, Y'),
            '{{reference}}' => $data['reference'] ?? '',
            '{{sig}}' => '_______________________', // Signature placeholder
            
            // Totals
            '{TOTAL_ENGAGEMENT_FEE}' => $totals['engagement_fee_formatted'] ?? wc_price($totals['engagement_fee'] ?? 0),
            '{TOTAL_EXPECTED_COST}' => $totals['expected_cost_formatted'] ?? wc_price($totals['expected_cost'] ?? 0),
            '{ENGAGEMENT_FEE}' => $totals['engagement_fee_formatted'] ?? wc_price($totals['engagement_fee'] ?? 0),
            '{EXPECTED_COST}' => $totals['expected_cost_formatted'] ?? wc_price($totals['expected_cost'] ?? 0),
            
            // Lawyer fields
            '{LAWYER_NAME}' => $data['lawyer']['name'] ?? '',
            '{LAWYER_EMAIL}' => $data['lawyer']['email'] ?? '',
        ];
        
        // Add form data fields (from Gravity Form / session)
        foreach ($form_data as $key => $value) {
            if (is_scalar($value)) {
                // Uppercase format {FIELD}
                $tag_key = '{' . strtoupper($key) . '}';
                $replacements[$tag_key] = $value;
                
                // Underscore format {FIELD_NAME}
                $underscore_key = '{' . strtoupper(str_replace(' ', '_', $key)) . '}';
                $replacements[$underscore_key] = $value;
                
                // Gravity Forms format {{field_name}}
                $gf_key = '{{' . strtolower(str_replace(' ', '_', $key)) . '}}';
                $replacements[$gf_key] = $value;
            }
        }
        
        return $replacements;
    }
    
    /**
     * Replace merge tags in string
     * 
     * @param string $content Content with merge tags
     * @param array $replacements Replacement map
     * @return string Processed content
     */
    private static function replace_merge_tags($content, $replacements) {
        if (!is_string($content)) {
            return $content;
        }
        
        // Direct replacement for known tags (handles both formats)
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Handle any remaining unmatched tags by replacing with dotted placeholder
        // Match {UPPERCASE} format
        $content = preg_replace(self::MERGE_TAG_PATTERN, self::DOTTED_PLACEHOLDER, $content);
        
        // Match {{lowercase}} Gravity Forms format  
        $content = preg_replace(self::GF_MERGE_TAG_PATTERN, self::DOTTED_PLACEHOLDER, $content);
        
        return $content;
    }
    
    /**
     * Get data from engagement letter post
     * 
     * @param int $post_id Engagement letter post ID
     * @return array|WP_Error Post data or error
     */
    public static function get_from_post($post_id) {
        if (get_post_type($post_id) !== 'engagement_letter') {
            return new WP_Error('invalid_post', 'Not an engagement letter post');
        }
        
        $form_data = get_post_meta($post_id, '_el_form_data', true) ?: [];
        $cart_contents = get_post_meta($post_id, '_el_cart_contents', true) ?: [];
        $boilerplate = self::get_boilerplate();
        
        // Build data structure similar to assemble_data()
        return [
            'reference' => get_post_meta($post_id, '_el_reference', true),
            'date' => get_post_meta($post_id, '_el_created_date', true),
            'client' => [
                'name' => $form_data['full_name'] ?? '',
                'first_name' => $form_data['first_name'] ?? '',
                'last_name' => $form_data['last_name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? '',
                'company' => $form_data['company'] ?? '',
                'address' => [
                    'line_1' => $form_data['street_address'] ?? '',
                    'line_2' => $form_data['address_2'] ?? '',
                    'city' => $form_data['city'] ?? '',
                    'state' => $form_data['state'] ?? '',
                    'postcode' => $form_data['zip'] ?? '',
                    'country' => $form_data['country'] ?? '',
                ],
            ],
            'form_data' => $form_data,
            'services' => $cart_contents,
            'products' => $cart_contents,
            'boilerplate' => $boilerplate,
            'letterhead' => $boilerplate['letterhead'],
            'firm_footer' => $boilerplate['firm_footer'],
        ];
    }
    
    /**
     * Get ACF data for a specific product
     * 
     * @param int $product_id Product ID
     * @return array ACF data
     */
    public static function get_product_acf_data($product_id) {
        return [
            'pdf_el_title' => get_field('pdf_el_title', $product_id) ?: '',
            'pdf_el_subtitle' => get_field('pdf_el_subtitle', $product_id) ?: '',
            'pdf_el_text' => get_field('pdf_el_text', $product_id) ?: '',
            'client_fillable_pdf_text' => get_field('client_fillable_pdf_text', $product_id) ?: '',
            'el_introduction_texts' => get_field('el_introduction_texts', $product_id) ?: '',
            'pdf_footer_notes' => get_field('pdf_footer_notes', $product_id) ?: '',
            'fee_structure' => get_field('fee_structure', $product_id) ?: '',
            'pdf_clauses' => get_field('pdf_clauses', $product_id) ?: [],
            'pdf_annexes' => get_field('pdf_annexes', $product_id) ?: [],
            'practice_area' => get_field('practice_area', $product_id) ?: '',
            'service_type' => get_field('service_type', $product_id) ?: '',
            'engagement_fee' => get_field('engagement_fee_due_today', $product_id) ?: 0,
            'expected_total_cost' => get_field('expected_total_cost', $product_id) ?: 0,
        ];
    }
    
    /**
     * Get assembled data as JSON for storage
     * 
     * @param bool $include_user_data Whether to include user data
     * @return string JSON encoded data
     */
    public static function get_json($include_user_data = true) {
        $data = self::assemble_data($include_user_data);
        
        if (is_wp_error($data)) {
            return json_encode(['error' => $data->get_error_message()]);
        }
        
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Validate assembled data structure
     * 
     * @param array $data Assembled data to validate
     * @return array Validation results with 'valid' => bool and 'errors' => array
     */
    public static function validate_data($data) {
        $errors = [];
        
        // Check for WP_Error
        if (is_wp_error($data)) {
            return [
                'valid' => false,
                'errors' => [$data->get_error_message()],
            ];
        }
        
        // Check required top-level keys
        $required_keys = ['client', 'boilerplate', 'products', 'totals'];
        foreach ($required_keys as $key) {
            if (!isset($data[$key])) {
                $errors[] = "Missing required key: {$key}";
            }
        }
        
        // Validate client data
        if (isset($data['client'])) {
            if (empty($data['client']['name']) && empty($data['client']['first_name'])) {
                $errors[] = "Client name is missing";
            }
            if (empty($data['client']['email'])) {
                $errors[] = "Client email is missing";
            }
        }
        
        // Validate products
        if (isset($data['products']) && empty($data['products'])) {
            $errors[] = "No products in cart";
        }
        
        // Validate reference
        if (empty($data['reference'])) {
            $errors[] = "Reference ID is missing";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}