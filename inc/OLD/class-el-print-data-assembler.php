<?php
/**
 * Print PDF Data Assembler  OLD VERSION
 * 
 * Assembles all data needed for engagement letter PDF generation:
 * - Client information from WooCommerce/session
 * - Product data from cart items
 * - Boilerplate from options
 * - Form data from Gravity Forms entries
 * - Processes merge tags when include_user_data is true
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data Assembler for Print PDFs
 * 
 * Collects and organizes all data needed for PDF generation.
 * Handles merge tag processing when include_user_data is enabled.
 */
class EL_Print_Data_Assembler {
    
    /**
     * Merge tag pattern for replacement
     */
    const MERGE_TAG_PATTERN = '/\{([A-Z_]+)\}/';
    
    /**
     * Gravity Forms merge tag pattern (double curly braces, lowercase)
     */
    const GF_MERGE_TAG_PATTERN = '/\{\{([a-z_]+)\}\}/';
    
    /**
     * Dotted line placeholder for unfilled fields
     */
    const DOTTED_PLACEHOLDER = '............................';
    
    /**
     * Assemble all data needed for print PDF generation
     * 
     * @param bool $include_user_data Whether to include actual user data or use blank placeholders
     * @return array Assembled data structure
     */
    public static function assemble_data($include_user_data = true) {
        // Get session/client data
        $session_data = self::get_session_data($include_user_data);
        
        // Get cart items
        $cart_items = WC()->cart->get_cart();
        
        // Get boilerplate from options
        $boilerplate = self::get_boilerplate();
        
        // Initialize data structure
        $data = [
            'client' => $session_data['client'],
            'form_data' => $session_data['form_data'],
            'boilerplate' => $boilerplate,
            'products' => [],
            'totals' => [
                'engagement_fee' => 0,
                'expected_cost' => 0,
                'product_count' => 0,
            ],
            'meta' => [
                'reference' => self::generate_reference(),
                'date' => date('F j, Y'),
                'timestamp' => current_time('timestamp'),
                'include_user_data' => $include_user_data,
            ],
            'lawyer' => [
                'email' => wp_get_current_user()->user_email,
                'name' => wp_get_current_user()->display_name,
            ],
        ];
        
        // Process each cart item
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_data = self::process_cart_item($cart_item, $data, $include_user_data);
            
            if ($product_data) {
                $data['products'][] = $product_data;
                $data['totals']['engagement_fee'] += $product_data['fees']['engagement_fee'];
                $data['totals']['expected_cost'] += $product_data['fees']['expected_cost'];
                $data['totals']['product_count']++;
            }
        }
        
        // Apply merge tags to boilerplate if including user data
      
        $data['boilerplate'] = self::process_boilerplate_merge_tags($data['boilerplate'], $data);
        
        
        // Add computed fields
        $data['totals']['engagement_fee_formatted'] = wc_price($data['totals']['engagement_fee']);
        $data['totals']['expected_cost_formatted'] = wc_price($data['totals']['expected_cost']);
        
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
        // Get form data from session (saved in Step 1)
        if (!session_id()) {
            session_start();
        }
        
        $form_data = $_SESSION['el_form_data'] ?? [];
        
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
            ];
        } else {
            // Fallback to WooCommerce customer if no session data
            $customer = WC()->customer;
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
        }
        
        return [
            'client' => $client_data,
            'form_data' => $form_data,
        ];
        
} else {
    // Blank placeholders for template version - use longer dotted lines
    $long_dots = str_repeat('.', 30); // Longer line to fill space
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
    ];
    
    return [
        'client' => $client_data,
        'form_data' => [],
    ];
}
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
     * @param array $cart_item WooCommerce cart item
     * @param array $data Current assembled data (for merge tags)
     * @param bool $include_user_data Whether to process merge tags
     * @return array|null Product data or null if invalid
     */
private static function process_cart_item($cart_item, $data, $include_user_data) {
    $product = $cart_item['data'];
    $product_id = $cart_item['product_id'];
    $variation_id = $cart_item['variation_id'] ?? 0;
    $quantity = $cart_item['quantity'];
    
    // Check if this is a grouped product parent
    $is_grouped_parent = isset($cart_item['is_grouped_parent']) && $cart_item['is_grouped_parent'];
    
    // Check if this is a grouped product child
    $is_grouped_child = isset($cart_item['is_grouped_child']) && $cart_item['is_grouped_child'];
    $grouped_parent_id = $cart_item['grouped_parent_id'] ?? null;
    
    // Determine which product ID to use for ACF fields
    if ($is_grouped_child && $grouped_parent_id) {
        // For grouped children, use PARENT product ID for content, but child for pricing
        $content_acf_id = $grouped_parent_id;
        $pricing_acf_id = $variation_id ?: $product_id;
    } else if ($variation_id) {
        // For variations, use variation ID
        $content_acf_id = $variation_id;
        $pricing_acf_id = $variation_id;
    } else {
        // For regular products, use product ID
        $content_acf_id = $product_id;
        $pricing_acf_id = $product_id;
    }
    
    // Get fees (always from the actual product/variation for correct pricing)
    $engagement_fee = floatval(get_field('engagement_fee', $pricing_acf_id) ?: $product->get_price());
    $expected_cost = floatval(get_field('expected_total_cost', $pricing_acf_id) ?: 0);
    
    // Get PDF-layer content fields
    // For grouped children, try parent ACF data from cart first, then from parent product
    if ($is_grouped_child && isset($cart_item['parent_acf_data'])) {
        // Use cached parent ACF data from cart
        $parent_acf = $cart_item['parent_acf_data'];
        $pdf_el_title = $parent_acf['pdf_el_title'] ?: get_field('pdf_el_title', $content_acf_id);
        $pdf_el_subtitle = $parent_acf['pdf_el_subtitle'] ?: get_field('pdf_el_subtitle', $content_acf_id);
        $pdf_el_text = $parent_acf['pdf_el_text'] ?: get_field('pdf_el_text', $content_acf_id);
        $client_fillable_text = $parent_acf['client_fillable_pdf_text'] ?: get_field('client_fillable_pdf_text', $content_acf_id);
        $el_introduction = $parent_acf['el_introduction_texts'] ?: get_field('el_introduction_texts', $content_acf_id);
        $pdf_footer_notes = $parent_acf['pdf_footer_notes'] ?: get_field('pdf_footer_notes', $content_acf_id);
        $fee_structure = $parent_acf['fee_structure'] ?: get_field('fee_structure', $content_acf_id);
        $pdf_clauses = $parent_acf['pdf_clauses'] ?: get_field('pdf_clauses', $content_acf_id);
        $pdf_annexes = $parent_acf['pdf_annexes'] ?: get_field('pdf_annexes', $content_acf_id);
    } else {
        // Normal product or grouped parent - get from content_acf_id
        $pdf_el_title = get_field('pdf_el_title', $content_acf_id) ?: $product->get_name();
        $pdf_el_subtitle = get_field('pdf_el_subtitle', $content_acf_id) ?: '';
        $pdf_el_text = get_field('pdf_el_text', $content_acf_id) ?: '';
        $client_fillable_text = get_field('client_fillable_pdf_text', $content_acf_id) ?: '';
        $el_introduction = get_field('el_introduction_texts', $content_acf_id) ?: '';
        $pdf_footer_notes = get_field('pdf_footer_notes', $content_acf_id) ?: '';
        $fee_structure = get_field('fee_structure', $content_acf_id) ?: '';
        $pdf_clauses = get_field('pdf_clauses', $content_acf_id) ?: [];
        $pdf_annexes = get_field('pdf_annexes', $content_acf_id) ?: [];
    }
    
    // Build product data structure
    $product_data = [
        'id' => $product_id,
        'variation_id' => $variation_id,
        'acf_id' => $content_acf_id,
        'pricing_acf_id' => $pricing_acf_id,
        'cart_item_key' => $cart_item['cart_item_key'] ?? '',
        'quantity' => $quantity,
        'name' => $product->get_name(),
        'sku' => $product->get_sku(),
        
        // Grouped product flags
        'is_grouped_parent' => $is_grouped_parent,
        'is_grouped_child' => $is_grouped_child,
        'grouped_parent_id' => $grouped_parent_id,
        'grouped_parent_name' => $cart_item['grouped_parent_name'] ?? '',
        
        // PDF Content (PDF-layer fields from parent for grouped children)
        'content' => [
            'title' => $pdf_el_title,
            'subtitle' => $pdf_el_subtitle,
            'introduction' => $el_introduction,
            'body_text' => $pdf_el_text,
            'client_fillable_text' => $client_fillable_text,
            'footer_notes' => $pdf_footer_notes,
            'fee_structure' => $fee_structure,
        ],
        
        // Structured content (from parent for grouped children)
        'clauses' => self::process_repeater_field($pdf_clauses),
        'annexes' => self::process_repeater_field($pdf_annexes),
        
        // Fees (from actual child product for correct pricing)
        'fees' => [
            'engagement_fee' => $engagement_fee * $quantity,
            'expected_cost' => $expected_cost * $quantity,
            'unit_engagement_fee' => $engagement_fee,
            'unit_expected_cost' => $expected_cost,
            'engagement_fee_formatted' => wc_price($engagement_fee * $quantity),
            'expected_cost_formatted' => wc_price($expected_cost * $quantity),
        ],
        
        // Metadata
        'meta' => [
            'categories' => self::get_product_categories($content_acf_id),
            'practice_area' => get_field('practice_area', $content_acf_id) ?: '',
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
 * IMPORTANT: When rendering PDF/Preview, you should group children under their parent
 * 
 * Example logic in your PDF template:
 * 
 * // Group products by parent
 * $grouped = [];
 * $standalone = [];
 * 
 * foreach ($data['products'] as $product) {
 *     if ($product['is_grouped_parent']) {
 *         $grouped[$product['id']] = [
 *             'parent' => $product,
 *             'children' => []
 *         ];
 *     } else if ($product['is_grouped_child'] && $product['grouped_parent_id']) {
 *         if (isset($grouped[$product['grouped_parent_id']])) {
 *             $grouped[$product['grouped_parent_id']]['children'][] = $product;
 *         }
 *     } else {
 *         $standalone[] = $product;
 *     }
 * }
 * 
 * // Render grouped products (show parent content once, list children for pricing)
 * foreach ($grouped as $group) {
 *     echo $group['parent']['content']['title'];
 *     echo $group['parent']['content']['body_text'];
 *     // List children as line items
 *     foreach ($group['children'] as $child) {
 *         echo $child['name'] . ': ' . $child['fees']['engagement_fee_formatted'];
 *     }
 * }
 * 
 * // Render standalone products
 * foreach ($standalone as $product) {
 *     echo $product['content']['title'];
 *     echo $product['content']['body_text'];
 * }
 */
    
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
        // Format: EL-YYYYMMDD-XXXXXX (6 random hex chars)
        return sprintf(
            'EL-%s-%s',
            date('Ymd'),
            strtoupper(substr(md5(uniqid(rand(), true)), 0, 6))
        );
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
        $client = $data['client'];
        $form_data = $data['form_data'];
        $totals = $data['totals'];
        $meta = $data['meta'];
        
        // Build full address string
        $address_parts = array_filter([
            $client['address']['line_1'],
            $client['address']['line_2'],
            $client['address']['city'],
            $client['address']['state'],
            $client['address']['postcode'],
            $client['address']['country'],
        ]);
        $full_address = implode(', ', $address_parts);
        
        $replacements = [
            // Client fields - uppercase format
            '{CLIENT_NAME}' => $client['name'],
            '{CLIENT_FIRST_NAME}' => $client['first_name'],
            '{CLIENT_LAST_NAME}' => $client['last_name'],
            '{CLIENT_EMAIL}' => $client['email'],
            '{CLIENT_PHONE}' => $client['phone'],
            '{CLIENT_COMPANY}' => $client['company'],
            '{CLIENT_ADDRESS}' => $full_address,
            '{CLIENT_ADDRESS_1}' => $client['address']['line_1'],
            '{CLIENT_ADDRESS_2}' => $client['address']['line_2'],
            '{CLIENT_CITY}' => $client['address']['city'],
            '{CLIENT_STATE}' => $client['address']['state'],
            '{CLIENT_POSTCODE}' => $client['address']['postcode'],
            '{CLIENT_COUNTRY}' => $client['address']['country'],
            
            // Gravity Forms format - lowercase double braces
            '{{first_name}}' => $client['first_name'],
            '{{last_name}}' => $client['last_name'],
            '{{email}}' => $client['email'],
            '{{phone}}' => $client['phone'],
            '{{company}}' => $client['company'],
            '{{street_address}}' => $client['address']['line_1'],
            '{{address_line_1}}' => $client['address']['line_1'],
            '{{address_line_2}}' => $client['address']['line_2'],
            '{{city}}' => $client['address']['city'],
            '{{state}}' => $client['address']['state'],
            '{{zip}}' => $client['address']['postcode'],
            '{{postcode}}' => $client['address']['postcode'],
            '{{country}}' => $client['address']['country'],
            '{{cosigner_first_name}}' => '',
            '{{cosigner_last_name}}' => '',
            
            // Document fields
            '{DATE}' => $meta['date'],
            '{REFERENCE}' => $meta['reference'],
            '{TODAY}' => $meta['date'],
            '{{date}}' => $meta['date'],
            '{{reference}}' => $meta['reference'],
            '{{sig}}' => '',  // Signature placeholder
            
            // Totals
            '{TOTAL_ENGAGEMENT_FEE}' => $totals['engagement_fee_formatted'] ?? wc_price($totals['engagement_fee']),
            '{TOTAL_EXPECTED_COST}' => $totals['expected_cost_formatted'] ?? wc_price($totals['expected_cost']),
            '{ENGAGEMENT_FEE}' => $totals['engagement_fee_formatted'] ?? wc_price($totals['engagement_fee']),
            '{EXPECTED_COST}' => $totals['expected_cost_formatted'] ?? wc_price($totals['expected_cost']),
            
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
     * Get assembled data as JSON for storage
     * 
     * @param bool $include_user_data Whether to include user data
     * @return string JSON encoded data
     */
    public static function get_json($include_user_data = true) {
        $data = self::assemble_data($include_user_data);
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
        
        // Check required top-level keys
        $required_keys = ['client', 'boilerplate', 'products', 'totals', 'meta'];
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
        
        // Validate meta
        if (isset($data['meta'])) {
            if (empty($data['meta']['reference'])) {
                $errors[] = "Reference ID is missing";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}