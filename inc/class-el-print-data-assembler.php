<?php
/**
 * Engagement Letter Print Data Assembler - UPDATED FOR GROUPED PRODUCTS
 * 
 * Assembles all data needed for PDF generation from:
 * - Session data (client info, grouped parent)
 * - WooCommerce cart (services)
 * - ACF fields (content, pricing)
 * - Engagement letter post meta
 */

if (!defined('ABSPATH')) exit;

class EL_Print_Data_Assembler {
    
    /**
     * Assemble complete data for PDF generation
     * 
     * @return array|WP_Error Complete data array or error
     */
    public static function assemble_data() {
        // Start session if needed
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        
        // Log start
        error_log('📦 EL Data Assembler: Starting data assembly...');
        
        // Get client data from session
        $client_data = self::get_client_data();
        
        if (is_wp_error($client_data)) {
            error_log('❌ Client data error: ' . $client_data->get_error_message());
            return $client_data;
        }
        
        // Get cart services with GROUPED PRODUCT SUPPORT
        $services_data = self::get_services_data();
        
        if (is_wp_error($services_data)) {
            error_log('❌ Services data error: ' . $services_data->get_error_message());
            return $services_data;
        }
        
        // Get pricing data with GROUPED PRODUCT PRICING
        $pricing_data = self::get_pricing_data();
        
        // Get boilerplate content
        $boilerplate_data = self::get_boilerplate_data();
        
        // Assemble complete data structure
        $complete_data = array_merge(
            [
                'reference' => self::generate_reference(),
                'date' => current_time('F j, Y'),
                'timestamp' => current_time('mysql'),
            ],
            $client_data,
            $services_data,
            $pricing_data,
            $boilerplate_data
        );
        
        error_log('✅ Data assembly complete. Services: ' . count($complete_data['services']));
        
        return $complete_data;
    }
    
    /**
     * Get client information from session
     * 
     * @return array|WP_Error Client data or error
     */
    private static function get_client_data() {
        $form_data = $_SESSION['el_form_data'] ?? [];
        
        if (empty($form_data)) {
            error_log('⚠️ No client data in session - using defaults');
            return [
                'client' => [
                    'name' => 'Test Client',
                    'email' => 'test@example.com',
                    'phone' => '',
                    'address' => '',
                ],
                'form_data' => [
                    'first_name' => 'Test',
                    'last_name' => 'Client',
                    'full_name' => 'Test Client',
                    'email' => 'test@example.com',
                ]
            ];
        }
        
        return [
            'client' => [
                'name' => $form_data['full_name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? '',
                'address' => $form_data['full_address'] ?? '',
            ],
            'form_data' => $form_data
        ];
    }
    
    /**
     * Get services data from cart - GROUPED PRODUCT AWARE
     * 
     * @return array|WP_Error Services data or error
     */
    private static function get_services_data() {
        if (!function_exists('WC') || !WC()->cart) {
            error_log('❌ WooCommerce cart not available');
            return new WP_Error('no_cart', 'Cart not available');
        }
        
        // Ensure cart is loaded from session
        WC()->cart->get_cart_from_session();
        
        $cart = WC()->cart->get_cart();
        
        if (empty($cart)) {
            error_log('❌ Cart is empty');
            return new WP_Error('empty_cart', 'No services in cart');
        }
        
        error_log('📦 Processing ' . count($cart) . ' items from cart');
        
        // Check if we have a grouped product parent in session
        $grouped_parent_id = $_SESSION['el_grouped_parent_id'] ?? null;
        $grouped_parent_data = $_SESSION['el_grouped_parent_data'] ?? [];
        
        if ($grouped_parent_id) {
            error_log('✅ Found grouped parent ID in session: ' . $grouped_parent_id);
        }
        
        $services = [];
        
        foreach ($cart as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product = $cart_item['data'];
            
            if (!$product) {
                error_log('⚠️ Product not found for cart item: ' . $cart_item_key);
                continue;
            }
            
            // Get item type (mandatory/suggested/optional/hide)
            $item_type = $cart_item['el_item_type'] ?? 'optional';
            
            error_log('📋 Processing: ' . $product->get_name() . ' (Type: ' . $item_type . ')');
            
            // SKIP hidden items
            if ($item_type === 'hide') {
                error_log('⏭️ Skipping hidden item: ' . $product->get_name());
                continue;
            }
            
            // Determine data source: parent ACF data > cart parent data > child product
            $acf_data = [];
            
            if (!empty($grouped_parent_data)) {
                // Use parent data from session (BEST - has all parent ACF fields)
                $acf_data = $grouped_parent_data;
                error_log('✅ Using parent ACF data from session for: ' . $product->get_name());
            } elseif (!empty($cart_item['parent_acf_data'])) {
                // Use parent data from cart item
                $acf_data = $cart_item['parent_acf_data'];
                error_log('✅ Using parent ACF data from cart item for: ' . $product->get_name());
            } else {
                // Fallback: get data from child product itself
                $acf_data = self::get_product_acf_data($product_id);
                error_log('⚠️ Using child product ACF data for: ' . $product->get_name());
            }
            
            // Build service data
            $service = [
                'id' => $product_id,
                'name' => $product->get_name(),
                'item_type' => $item_type, // For checkbox display
                'quantity' => $cart_item['quantity'],
                
                // PDF-specific fields from ACF data
                'pdf_title' => $acf_data['pdf_el_title'] ?? $product->get_name(),
                'pdf_subtitle' => $acf_data['pdf_el_subtitle'] ?? '',
                'pdf_text' => $acf_data['pdf_el_text'] ?? '',
                'pdf_introduction' => $acf_data['el_introduction_texts'] ?? '',
                'pdf_footer' => $acf_data['pdf_footer_notes'] ?? '',
                
                // Clauses and content
                'pdf_clauses' => $acf_data['pdf_clauses'] ?? [],
                'pdf_annexes' => $acf_data['pdf_annexes'] ?? [],
                'client_fillable_text' => $acf_data['client_fillable_pdf_text'] ?? '',
                
                // Metadata
                'practice_area' => $acf_data['practice_area'] ?? '',
                'service_type' => $acf_data['service_type'] ?? '',
                'fee_structure' => $acf_data['fee_structure'] ?? '',
                
                // Individual pricing (for display only - not used in total)
                'engagement_fee' => floatval($acf_data['engagement_fee'] ?? 0),
                'expected_cost' => floatval($acf_data['expected_total_cost'] ?? 0),
            ];
            
            $services[] = $service;
        }
        
        if (empty($services)) {
            error_log('❌ No services after processing (all hidden?)');
            return new WP_Error('no_services', 'No visible services in cart');
        }
        
        error_log('✅ Assembled ' . count($services) . ' services for PDF');
        
        return [
            'services' => $services,
            'service_count' => count($services),
            'has_grouped_parent' => !empty($grouped_parent_id)
        ];
    }
    
    /**
     * Get pricing data - GROUPED PRODUCT AWARE
     * 
     * @return array Pricing data
     */
    private static function get_pricing_data() {
        // Check for grouped parent pricing
        $grouped_parent_id = $_SESSION['el_grouped_parent_id'] ?? null;
        
        if ($grouped_parent_id) {
            // Use PARENT's engagement fee (not sum of children)
            $engagement_fee = floatval(get_field('engagement_fee_due_today', $grouped_parent_id) ?? 0);
            $expected_total = floatval(get_field('expected_total_cost', $grouped_parent_id) ?? 0);
            
            error_log('✅ Using grouped parent pricing - Fee: €' . $engagement_fee);
            
            return [
                'total_engagement_fee' => $engagement_fee,
                'total_expected_cost' => $expected_total,
                'pricing_source' => 'grouped_parent'
            ];
        }
        
        // Fallback: sum cart items (for standalone products)
        if (!function_exists('WC') || !WC()->cart) {
            return [
                'total_engagement_fee' => 0,
                'total_expected_cost' => 0,
                'pricing_source' => 'none'
            ];
        }
        
        $total_fee = 0;
        $total_expected = 0;
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            
            $fee = floatval(get_field('engagement_fee_due_today', $product_id) ?? 0);
            $expected = floatval(get_field('expected_total_cost', $product_id) ?? 0);
            
            $total_fee += ($fee * $quantity);
            $total_expected += ($expected * $quantity);
        }
        
        error_log('✅ Using summed cart pricing - Fee: €' . $total_fee);
        
        return [
            'total_engagement_fee' => $total_fee,
            'total_expected_cost' => $total_expected,
            'pricing_source' => 'cart_sum'
        ];
    }
    
    /**
     * Get boilerplate content from ACF options
     * 
     * @return array Boilerplate data
     */
    private static function get_boilerplate_data() {
        return [
            'letterhead' => get_field('boilerplate_letterhead', 'option') ?: '',
            'opening_left' => get_field('boilerplate_opening_tl', 'option') ?: '',
            'opening_right' => get_field('boilerplate_opening_tr_copy', 'option') ?: '',
            'footer_boilerplate' => get_field('footer_boilerplate', 'option') ?: '',
            'signature_block' => get_field('signature_block_template', 'option') ?: '',
            'firm_footer' => get_field('firm_footer', 'option') ?: '',
        ];
    }
    
    /**
     * Get ACF data for a specific product
     * 
     * @param int $product_id Product ID
     * @return array ACF data
     */
    private static function get_product_acf_data($product_id) {
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
     * Generate unique reference number
     * 
     * @return string Reference number
     */
    private static function generate_reference() {
        $existing_ref = $_SESSION['el_pdf_reference'] ?? null;
        
        if ($existing_ref) {
            return $existing_ref;
        }
        
        $reference = 'EL-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);
        $_SESSION['el_pdf_reference'] = $reference;
        
        return $reference;
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
        
        // Build data structure similar to assemble_data()
        return [
            'reference' => get_post_meta($post_id, '_el_reference', true),
            'date' => get_post_meta($post_id, '_el_created_date', true),
            'client' => [
                'name' => $form_data['full_name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? '',
                'address' => $form_data['full_address'] ?? '',
            ],
            'form_data' => $form_data,
            'services' => $cart_contents,
            // Add more fields as needed
        ];
    }
}