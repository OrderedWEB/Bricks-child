<?php
/**
 * Engagement Letter System - Tab 1: Email Autocomplete
 * 
 * Handles real-time email autocomplete on Gravity Forms email field:
 * - Search as user types in email field
 * - Display suggestion dropdown
 * - Auto-populate all form fields when client selected
 * 
 * LOAD ORDER: Tab module (after tab1-client.php)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// AJAX: EMAIL AUTOCOMPLETE SEARCH
// ============================================

/**
 * AJAX handler: Search existing clients with complete billing details
 * This searches as the user types in the email field
 */
function el_ajax_search_existing_client() {
    check_ajax_referer('el_client_search_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search_term'] ?? '');
    $search_type = sanitize_text_field($_POST['search_type'] ?? 'email');
    
    if (strlen($search_term) < 2) {
        wp_send_json_success([]);
    }
    
    global $wpdb;
    $clients = [];
    
    if ($search_type === 'email') {
        $search_pattern = '%' . $wpdb->esc_like($search_term) . '%';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_email, display_name 
            FROM {$wpdb->users} 
            WHERE user_email LIKE %s 
            ORDER BY display_name ASC 
            LIMIT 10",
            $search_pattern
        ));
        
        foreach ($users as $user_data) {
            $user = get_user_by('ID', $user_data->ID);
            
            $clients[] = [
                'id' => $user->ID,
                'first_name' => get_user_meta($user->ID, 'billing_first_name', true) ?: $user->first_name,
                'last_name' => get_user_meta($user->ID, 'billing_last_name', true) ?: $user->last_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_phone', true),
                'street_address' => get_user_meta($user->ID, 'billing_address_1', true),
                'address_2' => get_user_meta($user->ID, 'billing_address_2', true),
                'city' => get_user_meta($user->ID, 'billing_city', true),
                'state' => get_user_meta($user->ID, 'billing_state', true),
                'zip' => get_user_meta($user->ID, 'billing_postcode', true),
                'country' => get_user_meta($user->ID, 'billing_country', true),
                'display' => $user->display_name
            ];
        }
    } else {
        // Name search
        $search_pattern = '%' . $wpdb->esc_like($search_term) . '%';
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_email, display_name 
            FROM {$wpdb->users} 
            WHERE display_name LIKE %s 
            ORDER BY display_name ASC 
            LIMIT 10",
            $search_pattern
        ));
        
        foreach ($users as $user_data) {
            $user = get_user_by('ID', $user_data->ID);
            
            $clients[] = [
                'id' => $user->ID,
                'first_name' => get_user_meta($user->ID, 'billing_first_name', true) ?: $user->first_name,
                'last_name' => get_user_meta($user->ID, 'billing_last_name', true) ?: $user->last_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_phone', true),
                'street_address' => get_user_meta($user->ID, 'billing_address_1', true),
                'address_2' => get_user_meta($user->ID, 'billing_address_2', true),
                'city' => get_user_meta($user->ID, 'billing_city', true),
                'state' => get_user_meta($user->ID, 'billing_state', true),
                'zip' => get_user_meta($user->ID, 'billing_postcode', true),
                'country' => get_user_meta($user->ID, 'billing_country', true),
                'display' => $user->display_name
            ];
        }
    }
    
    wp_send_json_success($clients);
}
add_action('wp_ajax_search_existing_client', 'el_ajax_search_existing_client');
add_action('wp_ajax_nopriv_search_existing_client', 'el_ajax_search_existing_client');

// ============================================
// JAVASCRIPT: EMAIL AUTOCOMPLETE
// ============================================

/**
 * Outputs email autocomplete JavaScript directly to footer
 */
function el_enqueue_email_autocomplete_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('🔧 Email autocomplete initialized');
        
        // Attach to the email field in Gravity Forms (Field ID 2 = Email)
        $(document).on('input keyup', '#input_1_2', function() {
            var searchTerm = $(this).val().trim();
            console.log('📧 Email search:', searchTerm);
            
            if (searchTerm.length < 2) {
                $('.el-client-suggestions').remove();
                return;
            }
            
            // Debounce
            clearTimeout(window.elSearchTimeout);
            window.elSearchTimeout = setTimeout(function() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'search_existing_client',
                        search_term: searchTerm,
                        search_type: 'email',
                        nonce: '<?php echo wp_create_nonce('el_client_search_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ Search results:', response);
                        
                        $('.el-client-suggestions').remove();
                        
                        if (response.success && response.data.length > 0) {
                            var $suggestions = $('<div class="el-client-suggestions"></div>');
                            
                            response.data.forEach(function(client) {
                                var $item = $('<div class="el-suggestion-item"></div>')
                                    .html('<strong>' + client.display + '</strong><br><small>' + client.email + '</small>')
                                    .data('client', client)
                                    .on('click', function() {
                                        var c = $(this).data('client');
                                        
                                        // Name fields (Field ID 1 = Name)
                                        $('#input_1_1_3').val(c.first_name || '');  // First name
                                        $('#input_1_1_6').val(c.last_name || '');   // Last name
                                        
                                        // Email (Field ID 2)
                                        $('#input_1_2').val(c.email || '');
                                        
                                        // Phone (Field ID 5)
                                        $('#input_1_5').val(c.phone || '');
                                        
                                        // Complete address (Field ID 6)
                                        // Street address
                                        $('#input_1_6_1').val(c.street_address || '');
                                        
                                        // Address line 2 (if exists)
                                        if (c.address_2) {
                                            $('#input_1_6_2').val(c.address_2);
                                        }
                                        
                                        // City
                                        $('#input_1_6_3').val(c.city || '');
                                        
                                        // State/Province
                                        $('#input_1_6_4').val(c.state || '');
                                        
                                        // ZIP/Postal code
                                        $('#input_1_6_5').val(c.zip || '');
                                        
                                        // Country
                                        $('#input_1_6_6').val(c.country || '');
                                        
                                        // Remove suggestions
                                        $('.el-client-suggestions').remove();
                                        
                                        // Visual feedback
                                        $('#input_1_2').css('border-color', '#10b981');
                                        setTimeout(function() {
                                            $('#input_1_2').css('border-color', '');
                                        }, 2000);
                                        
                                        console.log('✅ Client data loaded:', c);
                                    });
                                $suggestions.append($item);
                            });
                            
                            $('#input_1_2').after($suggestions);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Search error:', error);
                    }
                });
            }, 300); // 300ms debounce
        });
        
        // Close suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#input_1_2, .el-client-suggestions').length) {
                $('.el-client-suggestions').remove();
            }
        });
        
        // Close suggestions on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.el-client-suggestions').remove();
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'el_enqueue_email_autocomplete_script', 9999);

// ============================================
// STYLES: EMAIL AUTOCOMPLETE
// ============================================

/**
 * Outputs email autocomplete styles directly to head
 */
function el_enqueue_email_autocomplete_styles() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    ?>
    <style type="text/css">
        /* Position context for email field */
        #input_1_2 {
            position: relative;
        }
        
        #field_1_2 {
            position: relative;
        }
        
        .el-client-suggestions {
            position: absolute;
            z-index: 9999;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            margin-top: 5px;
            width: calc(100% - 2px);
        }
        
        .el-suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }
        
        .el-suggestion-item:last-child {
            border-bottom: none;
        }
        
        .el-suggestion-item:hover {
            background-color: <?php echo defined('EL_COLOR_BG_LIGHT') ? EL_COLOR_BG_LIGHT : '#d5e4f6ff'; ?>;
        }
        
        .el-suggestion-item strong {
            display: block;
            color: #111827;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .el-suggestion-item small {
            color: #6b7280;
            font-size: 13px;
        }
    </style>
    <?php
}
add_action('wp_head', 'el_enqueue_email_autocomplete_styles');