<?php
/**
 * SLM WooCommerce Customer
 * 
 * Handles upgrading WordPress users to full WooCommerce customers:
 * - Copies Zoho address data to billing/shipping
 * - Links existing draft orders
 * - Sets customer role
 * - Syncs customer data
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Woo_Customer {
    
    /**
     * Zoho field mappings to WooCommerce billing fields
     */
    const BILLING_FIELD_MAP = [
        'first_name' => 'billing_first_name',
        'last_name' => 'billing_last_name',
        'user_email' => 'billing_email',
        'Phone' => 'billing_phone',
        'Mobile' => 'billing_phone', // Fallback
        'Company' => 'billing_company',
        'Mailing_Street' => 'billing_address_1',
        'Mailing_City' => 'billing_city',
        'Mailing_State' => 'billing_state',
        'Mailing_Zip' => 'billing_postcode',
        'Mailing_Country' => 'billing_country',
    ];
    
    /**
     * Zoho field mappings to WooCommerce shipping fields
     */
    const SHIPPING_FIELD_MAP = [
        'first_name' => 'shipping_first_name',
        'last_name' => 'shipping_last_name',
        'Company' => 'shipping_company',
        'Other_Street' => 'shipping_address_1',
        'Other_City' => 'shipping_city',
        'Other_State' => 'shipping_state',
        'Other_Zip' => 'shipping_postcode',
        'Other_Country' => 'shipping_country',
    ];
    
    /**
     * Country code mappings (common names to ISO codes)
     */
    const COUNTRY_MAPPINGS = [
        'Italy' => 'IT',
        'Italia' => 'IT',
        'United States' => 'US',
        'USA' => 'US',
        'United Kingdom' => 'GB',
        'UK' => 'GB',
        'Germany' => 'DE',
        'Deutschland' => 'DE',
        'France' => 'FR',
        'Spain' => 'ES',
        'España' => 'ES',
        'Portugal' => 'PT',
        'Netherlands' => 'NL',
        'Belgium' => 'BE',
        'Switzerland' => 'CH',
        'Austria' => 'AT',
        'Greece' => 'GR',
        'Poland' => 'PL',
        'Romania' => 'RO',
        'Czech Republic' => 'CZ',
        'Hungary' => 'HU',
        'Sweden' => 'SE',
        'Norway' => 'NO',
        'Denmark' => 'DK',
        'Finland' => 'FI',
        'Ireland' => 'IE',
        'Canada' => 'CA',
        'Australia' => 'AU',
        'New Zealand' => 'NZ',
        'Brazil' => 'BR',
        'Argentina' => 'AR',
        'Mexico' => 'MX',
        'Japan' => 'JP',
        'China' => 'CN',
        'India' => 'IN',
        'South Africa' => 'ZA',
    ];
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Hook into user registration if needed
        add_action('slm_user_onboarding_complete', [__CLASS__, 'upgrade_user']);
    }
    
    /**
     * Upgrade user to WooCommerce customer
     * 
     * @param int $user_id User ID
     * @return bool|WP_Error Success or error
     */
    public static function upgrade_user($user_id) {
        if (!function_exists('WC')) {
            SLM_Client_Onboarding::log('WooCommerce not active, skipping customer upgrade for user ' . $user_id, 'warning');
            return new WP_Error('woo_not_active', __('WooCommerce is not active.', 'flavor'));
        }
        
        $user = get_userdata($user_id);
        
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'flavor'));
        }
        
        // Check if already upgraded
        $already_upgraded = get_user_meta($user_id, 'slm_woo_customer_upgraded', true);
        
        if ($already_upgraded) {
            SLM_Client_Onboarding::log('User ' . $user_id . ' already upgraded to WooCommerce customer');
            return true;
        }
        
        // Get WooCommerce customer object
        $customer = new WC_Customer($user_id);
        
        // Copy billing address from Zoho fields
        self::copy_billing_address($user_id, $customer);
        
        // Copy shipping address from Zoho fields
        self::copy_shipping_address($user_id, $customer);
        
        // Save customer
        $customer->save();
        
        // Add customer role if not present
        self::ensure_customer_role($user_id);
        
        // Link any existing draft or pending orders
        self::link_existing_orders($user_id);
        
        // Mark as upgraded
        update_user_meta($user_id, 'slm_woo_customer_upgraded', true);
        update_user_meta($user_id, 'slm_woo_customer_upgraded_at', current_time('mysql'));
        
        SLM_Client_Onboarding::log('User ' . $user_id . ' upgraded to WooCommerce customer');
        
        // Fire action for other integrations
        do_action('slm_woo_customer_upgraded', $user_id, $customer);
        
        return true;
    }
    
    /**
     * Copy billing address from Zoho fields
     */
    private static function copy_billing_address($user_id, $customer) {
        $user = get_userdata($user_id);
        
        // First name
        $first_name = get_user_meta($user_id, 'first_name', true);
        if (!empty($first_name)) {
            $customer->set_billing_first_name($first_name);
        }
        
        // Last name
        $last_name = get_user_meta($user_id, 'last_name', true);
        if (!empty($last_name)) {
            $customer->set_billing_last_name($last_name);
        }
        
        // Email
        $customer->set_billing_email($user->user_email);
        
        // Phone - try Phone first, then Mobile
        $phone = get_user_meta($user_id, 'Phone', true);
        if (empty($phone)) {
            $phone = get_user_meta($user_id, 'Mobile', true);
        }
        if (!empty($phone)) {
            $customer->set_billing_phone(self::format_phone($phone));
        }
        
        // Company
        $company = get_user_meta($user_id, 'Company', true);
        if (!empty($company)) {
            $customer->set_billing_company($company);
        }
        
        // Address
        $street = get_user_meta($user_id, 'Mailing_Street', true);
        if (!empty($street)) {
            // Split into address_1 and address_2 if needed
            $address_parts = self::split_address($street);
            $customer->set_billing_address_1($address_parts['line_1']);
            if (!empty($address_parts['line_2'])) {
                $customer->set_billing_address_2($address_parts['line_2']);
            }
        }
        
        // City
        $city = get_user_meta($user_id, 'Mailing_City', true);
        if (!empty($city)) {
            $customer->set_billing_city($city);
        }
        
        // State/Province
        $state = get_user_meta($user_id, 'Mailing_State', true);
        if (!empty($state)) {
            $customer->set_billing_state(self::normalize_state($state));
        }
        
        // Postcode
        $postcode = get_user_meta($user_id, 'Mailing_Zip', true);
        if (!empty($postcode)) {
            $customer->set_billing_postcode(self::format_postcode($postcode));
        }
        
        // Country
        $country = get_user_meta($user_id, 'Mailing_Country', true);
        if (!empty($country)) {
            $customer->set_billing_country(self::normalize_country($country));
        } else {
            // Default to Italy
            $customer->set_billing_country('IT');
        }
        
        // Also store Codice Fiscale if available (Italian tax code)
        $codice_fiscale = get_user_meta($user_id, 'Codice_Fiscale', true);
        if (!empty($codice_fiscale)) {
            update_user_meta($user_id, 'billing_codice_fiscale', strtoupper($codice_fiscale));
        }
    }
    
    /**
     * Copy shipping address from Zoho fields
     */
    private static function copy_shipping_address($user_id, $customer) {
        // Check if "Other" address fields exist (used for shipping in Zoho)
        $other_street = get_user_meta($user_id, 'Other_Street', true);
        
        // If no separate shipping address, copy from billing
        if (empty($other_street)) {
            self::copy_billing_to_shipping($customer);
            return;
        }
        
        // First name
        $first_name = get_user_meta($user_id, 'first_name', true);
        if (!empty($first_name)) {
            $customer->set_shipping_first_name($first_name);
        }
        
        // Last name
        $last_name = get_user_meta($user_id, 'last_name', true);
        if (!empty($last_name)) {
            $customer->set_shipping_last_name($last_name);
        }
        
        // Company
        $company = get_user_meta($user_id, 'Company', true);
        if (!empty($company)) {
            $customer->set_shipping_company($company);
        }
        
        // Address
        $address_parts = self::split_address($other_street);
        $customer->set_shipping_address_1($address_parts['line_1']);
        if (!empty($address_parts['line_2'])) {
            $customer->set_shipping_address_2($address_parts['line_2']);
        }
        
        // City
        $city = get_user_meta($user_id, 'Other_City', true);
        if (!empty($city)) {
            $customer->set_shipping_city($city);
        }
        
        // State
        $state = get_user_meta($user_id, 'Other_State', true);
        if (!empty($state)) {
            $customer->set_shipping_state(self::normalize_state($state));
        }
        
        // Postcode
        $postcode = get_user_meta($user_id, 'Other_Zip', true);
        if (!empty($postcode)) {
            $customer->set_shipping_postcode(self::format_postcode($postcode));
        }
        
        // Country
        $country = get_user_meta($user_id, 'Other_Country', true);
        if (!empty($country)) {
            $customer->set_shipping_country(self::normalize_country($country));
        } else {
            // Default to Italy
            $customer->set_shipping_country('IT');
        }
    }
    
    /**
     * Copy billing address to shipping
     */
    private static function copy_billing_to_shipping($customer) {
        $customer->set_shipping_first_name($customer->get_billing_first_name());
        $customer->set_shipping_last_name($customer->get_billing_last_name());
        $customer->set_shipping_company($customer->get_billing_company());
        $customer->set_shipping_address_1($customer->get_billing_address_1());
        $customer->set_shipping_address_2($customer->get_billing_address_2());
        $customer->set_shipping_city($customer->get_billing_city());
        $customer->set_shipping_state($customer->get_billing_state());
        $customer->set_shipping_postcode($customer->get_billing_postcode());
        $customer->set_shipping_country($customer->get_billing_country());
    }
    
    /**
     * Ensure user has customer role
     */
    private static function ensure_customer_role($user_id) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return;
        }
        
        // Add customer role if not already present
        if (!in_array('customer', $user->roles)) {
            $user->add_role('customer');
        }
    }
    
    /**
     * Link existing orders to customer
     */
    private static function link_existing_orders($user_id) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return;
        }
        
        // Find orders by email that are not linked to a customer
        $orders = wc_get_orders([
            'billing_email' => $user->user_email,
            'customer_id' => 0,
            'status' => ['pending', 'on-hold', 'processing', 'draft'],
            'limit' => -1,
        ]);
        
        $linked_count = 0;
        
        foreach ($orders as $order) {
            $order->set_customer_id($user_id);
            $order->save();
            $linked_count++;
            
            SLM_Client_Onboarding::log('Linked order ' . $order->get_id() . ' to user ' . $user_id);
        }
        
        if ($linked_count > 0) {
            SLM_Client_Onboarding::log('Linked ' . $linked_count . ' existing orders to user ' . $user_id);
        }
        
        // Also check for engagement letter orders
        self::link_engagement_orders($user_id, $user->user_email);
    }
    
    /**
     * Link engagement letter orders
     */
    private static function link_engagement_orders($user_id, $email) {
        $orders = wc_get_orders([
            'meta_key' => '_el_signer_email',
            'meta_value' => $email,
            'customer_id' => 0,
            'status' => ['pending', 'on-hold'],
            'limit' => -1,
        ]);
        
        foreach ($orders as $order) {
            $order->set_customer_id($user_id);
            
            // Also update billing info
            $order->set_billing_first_name(get_user_meta($user_id, 'first_name', true));
            $order->set_billing_last_name(get_user_meta($user_id, 'last_name', true));
            
            $order->save();
            
            SLM_Client_Onboarding::log('Linked engagement order ' . $order->get_id() . ' to user ' . $user_id);
        }
    }
    
    /**
     * Normalize country to ISO code
     */
    private static function normalize_country($country) {
        if (empty($country)) {
            return 'IT';
        }
        
        // If already a 2-letter code, validate and return
        if (strlen($country) === 2) {
            $country = strtoupper($country);
            $countries = WC()->countries->get_countries();
            if (isset($countries[$country])) {
                return $country;
            }
        }
        
        // Check mappings
        $country_upper = ucwords(strtolower(trim($country)));
        
        if (isset(self::COUNTRY_MAPPINGS[$country_upper])) {
            return self::COUNTRY_MAPPINGS[$country_upper];
        }
        
        // Try to find in WooCommerce countries
        $countries = WC()->countries->get_countries();
        
        foreach ($countries as $code => $name) {
            if (strtolower($name) === strtolower($country)) {
                return $code;
            }
        }
        
        // Default to Italy
        return 'IT';
    }
    
    /**
     * Normalize state/province code
     */
    private static function normalize_state($state) {
        if (empty($state)) {
            return '';
        }
        
        // For Italy, we might need to map province names to codes
        // Common Italian province mappings
        $italian_provinces = [
            'Rome' => 'RM',
            'Roma' => 'RM',
            'Milan' => 'MI',
            'Milano' => 'MI',
            'Naples' => 'NA',
            'Napoli' => 'NA',
            'Turin' => 'TO',
            'Torino' => 'TO',
            'Florence' => 'FI',
            'Firenze' => 'FI',
            'Venice' => 'VE',
            'Venezia' => 'VE',
            'Bologna' => 'BO',
            'Genoa' => 'GE',
            'Genova' => 'GE',
            'Palermo' => 'PA',
            'Bari' => 'BA',
            'Catania' => 'CT',
        ];
        
        // Check if it's already a code (2 letters)
        if (strlen($state) === 2) {
            return strtoupper($state);
        }
        
        // Check Italian provinces
        $state_title = ucwords(strtolower(trim($state)));
        if (isset($italian_provinces[$state_title])) {
            return $italian_provinces[$state_title];
        }
        
        // Return as-is for other countries
        return $state;
    }
    
    /**
     * Format phone number
     */
    private static function format_phone($phone) {
        if (empty($phone)) {
            return '';
        }
        
        // Remove common formatting characters
        $phone = preg_replace('/[\s\-\.\(\)]/', '', $phone);
        
        // Ensure Italian numbers have country code
        if (preg_match('/^3\d{9}$/', $phone)) {
            // Italian mobile without country code
            $phone = '+39' . $phone;
        } elseif (preg_match('/^0\d{6,10}$/', $phone)) {
            // Italian landline without country code
            $phone = '+39' . $phone;
        } elseif (!preg_match('/^\+/', $phone) && strlen($phone) > 6) {
            // No plus sign, might need country code
            // Leave as-is for now
        }
        
        return $phone;
    }
    
    /**
     * Format postcode
     */
    private static function format_postcode($postcode) {
        if (empty($postcode)) {
            return '';
        }
        
        // Remove spaces
        $postcode = preg_replace('/\s+/', '', $postcode);
        
        // Italian postcodes are 5 digits
        if (preg_match('/^\d{5}$/', $postcode)) {
            return $postcode;
        }
        
        // Return as-is for other formats
        return strtoupper($postcode);
    }
    
    /**
     * Split address into line 1 and line 2
     */
    private static function split_address($address) {
        $result = [
            'line_1' => '',
            'line_2' => '',
        ];
        
        if (empty($address)) {
            return $result;
        }
        
        // Check for newline or common separators
        $separators = ["\n", "\r\n", " - ", ", apt", ", Apt", ", APT", ", unit", ", Unit", ", #"];
        
        foreach ($separators as $sep) {
            if (strpos($address, $sep) !== false) {
                $parts = explode($sep, $address, 2);
                $result['line_1'] = trim($parts[0]);
                $result['line_2'] = trim(ltrim($parts[1] ?? '', '., '));
                return $result;
            }
        }
        
        // No separator found, check length
        if (strlen($address) > 100) {
            // Try to split at last comma before character 100
            $comma_pos = strrpos(substr($address, 0, 100), ',');
            if ($comma_pos !== false) {
                $result['line_1'] = trim(substr($address, 0, $comma_pos));
                $result['line_2'] = trim(ltrim(substr($address, $comma_pos + 1), ' '));
                return $result;
            }
        }
        
        // Return full address as line 1
        $result['line_1'] = $address;
        return $result;
    }
    
    /**
     * Get customer data summary
     */
    public static function get_customer_summary($user_id) {
        if (!function_exists('WC')) {
            return null;
        }
        
        $customer = new WC_Customer($user_id);
        
        return [
            'billing' => [
                'first_name' => $customer->get_billing_first_name(),
                'last_name' => $customer->get_billing_last_name(),
                'company' => $customer->get_billing_company(),
                'email' => $customer->get_billing_email(),
                'phone' => $customer->get_billing_phone(),
                'address_1' => $customer->get_billing_address_1(),
                'address_2' => $customer->get_billing_address_2(),
                'city' => $customer->get_billing_city(),
                'state' => $customer->get_billing_state(),
                'postcode' => $customer->get_billing_postcode(),
                'country' => $customer->get_billing_country(),
            ],
            'shipping' => [
                'first_name' => $customer->get_shipping_first_name(),
                'last_name' => $customer->get_shipping_last_name(),
                'company' => $customer->get_shipping_company(),
                'address_1' => $customer->get_shipping_address_1(),
                'address_2' => $customer->get_shipping_address_2(),
                'city' => $customer->get_shipping_city(),
                'state' => $customer->get_shipping_state(),
                'postcode' => $customer->get_shipping_postcode(),
                'country' => $customer->get_shipping_country(),
            ],
            'stats' => [
                'order_count' => $customer->get_order_count(),
                'total_spent' => $customer->get_total_spent(),
                'is_paying_customer' => $customer->get_is_paying_customer(),
            ],
        ];
    }
    
    /**
     * Sync customer back to Zoho (optional)
     */
    public static function sync_to_zoho($user_id) {
        // This would integrate with the Zoho sync plugin
        // For now, just fire an action that can be hooked into
        do_action('slm_sync_customer_to_zoho', $user_id);
    }
}
