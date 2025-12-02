<?php
/**
 * SLM Client Search
 * 
 * Admin interface for lawyers to search and select clients from
 * Zoho-synced WordPress users. Displays all available user meta
 * and allows sending onboarding magic links.
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Client_Search {
    
    /**
     * Zoho field groups for organized display
     */
    private static $field_groups = [
        'personal' => [
            'label' => 'Personal Information',
            'fields' => [
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'user_email' => 'Email',
                'Phone' => 'Phone',
                'Mobile' => 'Mobile',
                'Date_of_Birth' => 'Date of Birth',
                'Place_of_Birth' => 'Place of Birth',
                'Country_of_Birth' => 'Country of Birth',
                'Gender' => 'Gender',
                'Nationality' => 'Nationality',
                'Secondary_Nationality' => 'Secondary Nationality',
            ],
        ],
        'identity' => [
            'label' => 'Identity Documents',
            'fields' => [
                'Codice_Fiscale' => 'Codice Fiscale',
                'Passport_Number' => 'Passport Number',
                'Passport_Expiry' => 'Passport Expiry',
                'ID_Card_Number' => 'ID Card Number',
                'ID_Card_Expiry' => 'ID Card Expiry',
            ],
        ],
        'address' => [
            'label' => 'Address',
            'fields' => [
                'Mailing_Street' => 'Street',
                'Mailing_City' => 'City',
                'Mailing_State' => 'State/Province',
                'Mailing_Zip' => 'Postal Code',
                'Mailing_Country' => 'Country',
            ],
        ],
        'case' => [
            'label' => 'Case Information',
            'fields' => [
                'Lead_Source' => 'Lead Source',
                'Service_Type' => 'Service Type',
                'Case_Status' => 'Case Status',
                'Assigned_Lawyer' => 'Assigned Lawyer',
                'Consultation_Date' => 'Consultation Date',
            ],
        ],
        'system' => [
            'label' => 'System Information',
            'fields' => [
                'CRM_ID' => 'Zoho CRM ID',
                'slm_zoho_sync_timestamp' => 'Last Sync',
                'user_registered' => 'WordPress Registration',
            ],
        ],
    ];
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Additional hooks if needed
    }
    
    /**
     * Render the admin page
     */
    public static function render_admin_page() {
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap slm-client-onboarding-wrap">
            <h1><?php esc_html_e('Client Onboarding', 'flavor'); ?></h1>
            <p class="description"><?php esc_html_e('Search for clients synced from Zoho CRM and send them an onboarding link.', 'flavor'); ?></p>
            
            <?php echo self::render_search_interface(); ?>
        </div>
        <?php
    }
    
    /**
     * Render the search interface (used by admin page and shortcode)
     */
    public static function render_search_interface() {
        ob_start();
        ?>
        <div class="slm-client-search-container">
            <!-- Search Box -->
            <div class="slm-search-box">
                <div class="slm-search-input-wrap">
                    <input 
                        type="text" 
                        id="slm-client-search-input" 
                        class="slm-search-input" 
                        placeholder="<?php esc_attr_e('Search by name, email, phone, or Codice Fiscale...', 'flavor'); ?>"
                        autocomplete="off"
                    >
                    <button type="button" id="slm-search-btn" class="button button-primary">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Search', 'flavor'); ?>
                    </button>
                </div>
                <p class="slm-search-hint">
                    <?php esc_html_e('Enter at least 2 characters to search', 'flavor'); ?>
                </p>
            </div>
            
            <!-- Search Results -->
            <div class="slm-search-results-container">
                <div id="slm-search-results" class="slm-search-results">
                    <p class="slm-no-search"><?php esc_html_e('Enter a search term to find clients.', 'flavor'); ?></p>
                </div>
            </div>
            
            <!-- Client Details Panel -->
            <div id="slm-client-details-panel" class="slm-client-details-panel" style="display: none;">
                <div class="slm-panel-header">
                    <h2 id="slm-client-name"><?php esc_html_e('Client Details', 'flavor'); ?></h2>
                    <button type="button" id="slm-close-panel" class="slm-close-btn">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                
                <div class="slm-panel-content">
                    <!-- Onboarding Status -->
                    <div class="slm-onboarding-status" id="slm-onboarding-status">
                        <!-- Populated via JS -->
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="slm-action-buttons" id="slm-action-buttons">
                        <!-- Populated via JS -->
                    </div>
                    
                    <!-- Client Details Accordion -->
                    <div class="slm-details-accordion" id="slm-details-accordion">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
            
            <!-- Send Link Modal -->
            <div id="slm-send-link-modal" class="slm-modal" style="display: none;">
                <div class="slm-modal-overlay"></div>
                <div class="slm-modal-content">
                    <div class="slm-modal-header">
                        <h3><?php esc_html_e('Send Onboarding Link', 'flavor'); ?></h3>
                        <button type="button" class="slm-modal-close">&times;</button>
                    </div>
                    <div class="slm-modal-body">
                        <p><?php esc_html_e('An onboarding link will be sent to:', 'flavor'); ?></p>
                        <p class="slm-modal-email" id="slm-modal-email"></p>
                        
                        <div class="slm-modal-info">
                            <p><strong><?php esc_html_e('The link will:', 'flavor'); ?></strong></p>
                            <ul>
                                <li><?php esc_html_e('Expire in 24 hours', 'flavor'); ?></li>
                                <li><?php esc_html_e('Allow the client to sign our Terms of Agreement', 'flavor'); ?></li>
                                <li><?php esc_html_e('Let them set their account password', 'flavor'); ?></li>
                                <li><?php esc_html_e('Create their secure document folder', 'flavor'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="slm-modal-warning" id="slm-modal-warning" style="display: none;">
                            <span class="dashicons dashicons-warning"></span>
                            <span id="slm-warning-text"></span>
                        </div>
                    </div>
                    <div class="slm-modal-footer">
                        <button type="button" class="button slm-modal-cancel"><?php esc_html_e('Cancel', 'flavor'); ?></button>
                        <button type="button" class="button button-primary" id="slm-confirm-send-link">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Send Link', 'flavor'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Loading Overlay -->
            <div id="slm-loading-overlay" class="slm-loading-overlay" style="display: none;">
                <div class="slm-spinner"></div>
                <p id="slm-loading-text"><?php esc_html_e('Loading...', 'flavor'); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX: Search for clients
     */
    public static function ajax_search() {
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        if (strlen($search_term) < 2) {
            wp_send_json_error(['message' => __('Please enter at least 2 characters.', 'flavor')]);
        }
        
        global $wpdb;
        
        // Search in user table and user meta
        $search_like = '%' . $wpdb->esc_like($search_term) . '%';
        
        // Build query to search across multiple fields
        $query = $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.user_email, u.display_name, u.user_registered
             FROM {$wpdb->users} u
             LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
             WHERE (
                 u.user_email LIKE %s
                 OR u.display_name LIKE %s
                 OR (um.meta_key = 'first_name' AND um.meta_value LIKE %s)
                 OR (um.meta_key = 'last_name' AND um.meta_value LIKE %s)
                 OR (um.meta_key = 'Phone' AND um.meta_value LIKE %s)
                 OR (um.meta_key = 'Mobile' AND um.meta_value LIKE %s)
                 OR (um.meta_key = 'Codice_Fiscale' AND um.meta_value LIKE %s)
                 OR (um.meta_key = 'CRM_ID' AND um.meta_value LIKE %s)
             )
             ORDER BY u.display_name ASC
             LIMIT 50",
            $search_like, $search_like, $search_like, $search_like,
            $search_like, $search_like, $search_like, $search_like
        );
        
        $users = $wpdb->get_results($query);
        
        if (empty($users)) {
            wp_send_json_success(['clients' => [], 'message' => __('No clients found.', 'flavor')]);
        }
        
        $clients = [];
        
        foreach ($users as $user) {
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $last_name = get_user_meta($user->ID, 'last_name', true);
            $phone = get_user_meta($user->ID, 'Phone', true) ?: get_user_meta($user->ID, 'Mobile', true);
            $onboarding_complete = get_user_meta($user->ID, 'slm_onboarding_complete', true);
            $terms_signed = get_user_meta($user->ID, 'slm_terms_signed', true);
            
            $clients[] = [
                'id' => $user->ID,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => trim($first_name . ' ' . $last_name) ?: $user->display_name,
                'phone' => $phone,
                'registered' => $user->user_registered,
                'onboarding_complete' => (bool) $onboarding_complete,
                'terms_signed' => (bool) $terms_signed,
            ];
        }
        
        wp_send_json_success(['clients' => $clients]);
    }
    
    /**
     * AJAX: Get full client details
     */
    public static function ajax_get_details() {
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        
        if (!$user_id) {
            wp_send_json_error(['message' => __('Invalid user ID.', 'flavor')]);
        }
        
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => __('User not found.', 'flavor')]);
        }
        
        // Get all user meta
        $all_meta = get_user_meta($user_id);
        
        // Build organized field groups
        $field_groups = [];
        
        foreach (self::$field_groups as $group_key => $group) {
            $fields = [];
            
            foreach ($group['fields'] as $meta_key => $label) {
                $value = '';
                
                // Handle special cases
                if ($meta_key === 'user_email') {
                    $value = $user->user_email;
                } elseif ($meta_key === 'user_registered') {
                    $value = $user->user_registered;
                } elseif (isset($all_meta[$meta_key])) {
                    $value = $all_meta[$meta_key][0];
                }
                
                // Format dates
                if (strpos($meta_key, 'Date') !== false || strpos($meta_key, 'Expiry') !== false || $meta_key === 'user_registered' || $meta_key === 'slm_zoho_sync_timestamp') {
                    if (!empty($value) && strtotime($value)) {
                        $value = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($value));
                    }
                }
                
                $fields[$meta_key] = [
                    'label' => $label,
                    'value' => $value ?: '—',
                    'has_value' => !empty($value),
                ];
            }
            
            $field_groups[$group_key] = [
                'label' => $group['label'],
                'fields' => $fields,
            ];
        }
        
        // Get any additional Zoho fields not in our predefined groups
        $additional_fields = [];
        $known_keys = ['first_name', 'last_name', 'nickname', 'description', 'rich_editing', 
                       'syntax_highlighting', 'comment_shortcuts', 'admin_color', 'use_ssl',
                       'show_admin_bar_front', 'locale', 'wp_capabilities', 'wp_user_level',
                       'dismissed_wp_pointers', 'session_tokens', 'wp_user-settings', 
                       'wp_user-settings-time', 'wp_dashboard_quick_press_last_post_id'];
        
        // Add our onboarding keys to known
        $known_keys = array_merge($known_keys, [
            'slm_magic_link_token', 'slm_magic_link_expires', 'slm_magic_link_used',
            'slm_terms_signed', 'slm_terms_signed_date', 'slm_terms_document_id',
            'slm_onboarding_complete', 'slm_client_folder_id'
        ]);
        
        // Flatten known keys from field groups
        foreach (self::$field_groups as $group) {
            $known_keys = array_merge($known_keys, array_keys($group['fields']));
        }
        
        foreach ($all_meta as $key => $values) {
            if (in_array($key, $known_keys)) {
                continue;
            }
            // Skip WordPress internal keys
            if (strpos($key, '_') === 0 || strpos($key, 'wp_') === 0) {
                continue;
            }
            // Skip billing/shipping (WooCommerce)
            if (strpos($key, 'billing_') === 0 || strpos($key, 'shipping_') === 0) {
                continue;
            }
            
            $additional_fields[$key] = [
                'label' => ucwords(str_replace('_', ' ', $key)),
                'value' => $values[0] ?: '—',
                'has_value' => !empty($values[0]),
            ];
        }
        
        if (!empty($additional_fields)) {
            $field_groups['additional'] = [
                'label' => __('Additional Zoho Fields', 'flavor'),
                'fields' => $additional_fields,
            ];
        }
        
        // Get onboarding status
        $onboarding_status = self::get_onboarding_status($user_id);
        
        // Get magic link history
        $magic_link_history = self::get_magic_link_history($user_id);
        
        // Get WooCommerce status
        $woo_status = self::get_woocommerce_status($user_id);
        
        wp_send_json_success([
            'user_id' => $user_id,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'full_name' => trim(get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true)) ?: $user->display_name,
            'field_groups' => $field_groups,
            'onboarding_status' => $onboarding_status,
            'magic_link_history' => $magic_link_history,
            'woocommerce_status' => $woo_status,
        ]);
    }
    
    /**
     * Get onboarding status for a user
     */
    private static function get_onboarding_status($user_id) {
        $terms_signed = get_user_meta($user_id, 'slm_terms_signed', true);
        $terms_signed_date = get_user_meta($user_id, 'slm_terms_signed_date', true);
        $onboarding_complete = get_user_meta($user_id, 'slm_onboarding_complete', true);
        $has_password = self::user_has_set_password($user_id);
        $folder_id = get_user_meta($user_id, 'slm_client_folder_id', true);
        
        // Check for pending magic link
        global $wpdb;
        $table = SLM_Client_Onboarding::get_table('magic_links');
        
        $pending_link = null;
        if ($table) {
            $pending_link = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table 
                 WHERE user_id = %d 
                 AND used_at IS NULL 
                 AND expires_at > NOW()
                 ORDER BY created_at DESC 
                 LIMIT 1",
                $user_id
            ));
        }
        
        return [
            'terms_signed' => (bool) $terms_signed,
            'terms_signed_date' => $terms_signed_date ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($terms_signed_date)) : null,
            'password_set' => $has_password,
            'onboarding_complete' => (bool) $onboarding_complete,
            'has_folder' => (bool) $folder_id,
            'pending_link' => $pending_link ? [
                'expires_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($pending_link->expires_at)),
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($pending_link->created_at)),
            ] : null,
            'status_label' => self::get_status_label($terms_signed, $onboarding_complete, $pending_link),
            'status_class' => self::get_status_class($terms_signed, $onboarding_complete, $pending_link),
        ];
    }
    
    /**
     * Check if user has set their own password (not auto-generated)
     */
    private static function user_has_set_password($user_id) {
        $onboarding_complete = get_user_meta($user_id, 'slm_onboarding_complete', true);
        return (bool) $onboarding_complete;
    }
    
    /**
     * Get human-readable status label
     */
    private static function get_status_label($terms_signed, $onboarding_complete, $pending_link) {
        if ($onboarding_complete) {
            return __('Onboarding Complete', 'flavor');
        }
        
        if ($terms_signed) {
            return __('Terms Signed - Awaiting Password', 'flavor');
        }
        
        if ($pending_link) {
            return __('Link Sent - Awaiting Client', 'flavor');
        }
        
        return __('Not Started', 'flavor');
    }
    
    /**
     * Get status CSS class
     */
    private static function get_status_class($terms_signed, $onboarding_complete, $pending_link) {
        if ($onboarding_complete) {
            return 'status-complete';
        }
        
        if ($terms_signed) {
            return 'status-partial';
        }
        
        if ($pending_link) {
            return 'status-pending';
        }
        
        return 'status-not-started';
    }
    
    /**
     * Get magic link history for a user
     */
    private static function get_magic_link_history($user_id) {
        global $wpdb;
        $table = SLM_Client_Onboarding::get_table('magic_links');
        
        if (!$table) {
            return [];
        }
        
        $links = $wpdb->get_results($wpdb->prepare(
            "SELECT ml.*, u.display_name as created_by_name
             FROM $table ml
             LEFT JOIN {$wpdb->users} u ON ml.created_by = u.ID
             WHERE ml.user_id = %d
             ORDER BY ml.created_at DESC
             LIMIT 10",
            $user_id
        ));
        
        $history = [];
        
        foreach ($links as $link) {
            $status = 'expired';
            if ($link->used_at) {
                $status = 'used';
            } elseif (strtotime($link->expires_at) > time()) {
                $status = 'active';
            }
            
            $history[] = [
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link->created_at)),
                'expires_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link->expires_at)),
                'used_at' => $link->used_at ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link->used_at)) : null,
                'created_by' => $link->created_by_name ?: __('Unknown', 'flavor'),
                'status' => $status,
                'status_label' => self::get_link_status_label($status),
            ];
        }
        
        return $history;
    }
    
    /**
     * Get link status label
     */
    private static function get_link_status_label($status) {
        switch ($status) {
            case 'active':
                return __('Active', 'flavor');
            case 'used':
                return __('Used', 'flavor');
            case 'expired':
                return __('Expired', 'flavor');
            default:
                return __('Unknown', 'flavor');
        }
    }
    
    /**
     * Get WooCommerce customer status
     */
    private static function get_woocommerce_status($user_id) {
        if (!function_exists('WC')) {
            return [
                'is_customer' => false,
                'woocommerce_active' => false,
            ];
        }
        
        $customer = new WC_Customer($user_id);
        
        $has_billing = !empty($customer->get_billing_address_1()) || !empty($customer->get_billing_email());
        $order_count = wc_get_customer_order_count($user_id);
        $total_spent = wc_get_customer_total_spent($user_id);
        
        // Get pending orders
        $pending_orders = wc_get_orders([
            'customer_id' => $user_id,
            'status' => ['pending', 'on-hold'],
            'limit' => 5,
        ]);
        
        return [
            'woocommerce_active' => true,
            'is_customer' => $has_billing || $order_count > 0,
            'has_billing_address' => $has_billing,
            'order_count' => $order_count,
            'total_spent' => wc_price($total_spent),
            'pending_orders' => count($pending_orders),
            'billing_email' => $customer->get_billing_email(),
            'billing_phone' => $customer->get_billing_phone(),
            'billing_address' => $customer->get_billing_address_1(),
            'billing_city' => $customer->get_billing_city(),
            'billing_country' => $customer->get_billing_country(),
        ];
    }
}
