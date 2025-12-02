<?php
/**
 * Engagement Letter System - Tab 1: Client Details
 * 
 * Handles client information collection:
 * - Gravity Forms integration
 * - Client search (existing clients)
 * - New client creation
 * - Form data storage in session
 * - Engagement letter creation
 * 
 * LOAD ORDER: Tab module (after all core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// GRAVITY FORMS INTEGRATION
// ============================================

/**
 * Processes Gravity Forms submission for client details
 * 
 * @param array $entry Gravity Forms entry array
 * @param array $form  Gravity Forms form array
 */
function el_process_client_form_submission($entry, $form) {
    // Only process engagement letter client forms
    if (!isset($form['cssClass']) || strpos($form['cssClass'], 'el-client-form') === false) {
        return;
    }
    
    // Extract form data
    $form_data = [
        'first_name' => rgar($entry, '1'),
        'last_name' => rgar($entry, '2'),
        'email' => rgar($entry, '3'),
        'phone' => rgar($entry, '4'),
        'street_address' => rgar($entry, '5'),
        'city' => rgar($entry, '6'),
        'state' => rgar($entry, '7'),
        'zip' => rgar($entry, '8'),
        'country' => rgar($entry, '9'),
        'notes' => rgar($entry, '10'),
    ];
    
    // Sanitize data
    $form_data = el_sanitize_client_data($form_data);
    
    // Validate data
    $validation = el_validate_client_data($form_data);
    
    if (!$validation['valid']) {
        if (EL_DEBUG_MODE) {
            el_log('Client form validation failed: ' . implode(', ', $validation['errors']), 'error');
        }
        return;
    }
    
    // Store in session
    el_set_session(EL_SESSION_FORM_DATA, $form_data);
    el_set_session(EL_SESSION_CLIENT_NAME, trim($form_data['first_name'] . ' ' . $form_data['last_name']));
    el_set_session(EL_SESSION_CLIENT_EMAIL, $form_data['email']);
    
    // Create or update engagement letter
    $engagement_id = el_get_current_engagement_id();
    
    if ($engagement_id) {
        // Update existing
        el_update_engagement_letter($engagement_id, [
            'form_data' => $form_data,
            'current_tab' => 1,
        ]);
    } else {
        // Create new
        $engagement_id = el_create_engagement_letter([
            'title' => 'Engagement Letter - ' . $form_data['first_name'] . ' ' . $form_data['last_name'],
            'form_data' => $form_data,
        ]);
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Client form processed for engagement ' . $engagement_id, 'info');
    }
}

// Hook to Gravity Forms submission
add_action('gform_after_submission', 'el_process_client_form_submission', 10, 2);

// ============================================
// AJAX: SAVE CLIENT DATA
// ============================================

/**
 * AJAX handler: Saves client details from form
 */
function el_ajax_save_client() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    // Check if data sent directly or inside form_data
    if (!empty($_POST['first_name']) || !empty($_POST['email'])) {
        // Direct field submission (from updated JavaScript)
        $form_data = [
            'first_name'     => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name'      => sanitize_text_field($_POST['last_name'] ?? ''),
            'email'          => sanitize_email($_POST['email'] ?? ''),
            'phone'          => sanitize_text_field($_POST['phone'] ?? ''),
            'street_address' => sanitize_text_field($_POST['street_address'] ?? ''),
            'city'           => sanitize_text_field($_POST['city'] ?? ''),
            'state'          => sanitize_text_field($_POST['state'] ?? ''),
            'zip'            => sanitize_text_field($_POST['zip'] ?? ''),
            'country'        => sanitize_text_field($_POST['country'] ?? ''),
            'notes'          => sanitize_textarea_field($_POST['notes'] ?? ''),
        ];
    } else {
        // Serialized form_data submission (from Gravity Forms serialize)
        $raw_form_data = $_POST['form_data'] ?? [];
        
        if (is_string($raw_form_data)) {
            parse_str($raw_form_data, $raw_form_data);
        }
        
        $form_data = [
            'first_name'     => sanitize_text_field($raw_form_data['input_1_3'] ?? ''),
            'last_name'      => sanitize_text_field($raw_form_data['input_1_6'] ?? ''),
            'email'          => sanitize_email($raw_form_data['input_2'] ?? ''),
            'phone'          => sanitize_text_field($raw_form_data['input_5'] ?? ''),
            'street_address' => sanitize_text_field($raw_form_data['input_6_1'] ?? ''),
            'city'           => sanitize_text_field($raw_form_data['input_6_3'] ?? ''),
            'state'          => sanitize_text_field($raw_form_data['input_6_4'] ?? ''),
            'zip'            => sanitize_text_field($raw_form_data['input_6_5'] ?? ''),
            'country'        => sanitize_text_field($raw_form_data['input_6_6'] ?? ''),
            'notes'          => sanitize_textarea_field($raw_form_data['input_7'] ?? ''),
        ];
    }
    
    // Validate
    $validation = el_validate_client_data($form_data);
    
    if (!$validation['valid']) {
        wp_send_json_error([
            'message' => 'Validation failed',
            'errors' => $validation['errors'],
        ]);
    }
    
    // Store in session
    el_set_session(EL_SESSION_FORM_DATA, $form_data);
    el_set_session(EL_SESSION_CLIENT_NAME, trim($form_data['first_name'] . ' ' . $form_data['last_name']));
    el_set_session(EL_SESSION_CLIENT_EMAIL, $form_data['email']);
    
    // Create or update engagement letter
    $engagement_id = el_get_current_engagement_id();
    
    if ($engagement_id) {
        // Update existing
        el_update_engagement_letter($engagement_id, [
            'form_data' => $form_data,
            'current_tab' => 1,
        ]);
        
        $message = 'Client details updated';
    } else {
        // Create new engagement letter
        $engagement_id = el_create_engagement_letter([
            'title' => 'Engagement Letter - ' . $form_data['first_name'] . ' ' . $form_data['last_name'],
            'form_data' => $form_data,
        ]);
        
        if (!$engagement_id) {
            wp_send_json_error(['message' => 'Failed to create engagement letter']);
        }
        
        $message = 'Client details saved';
    }
    
    wp_send_json_success([
        'message' => $message,
        'engagement_id' => $engagement_id,
        'client_name' => el_get_session(EL_SESSION_CLIENT_NAME),
    ]);
}
add_action('wp_ajax_' . EL_AJAX_SAVE_CLIENT, 'el_ajax_save_client');

// ============================================
// CLIENT SEARCH
// ============================================

/**
 * AJAX handler: Searches for existing clients
 */
function el_ajax_search_clients() {
    check_ajax_referer(EL_NONCE, 'nonce');  // Use main nonce
    
    $search_term = sanitize_text_field($_POST['search'] ?? '');
    
    if (strlen($search_term) < 2) {
        wp_send_json_error(['message' => 'Search term too short']);
    }
    
    // Search WordPress users
    $user_query = new WP_User_Query([
        'search' => '*' . $search_term . '*',
        'search_columns' => ['user_login', 'user_email', 'display_name'],
        'number' => 10,
    ]);
    
    $results = [];
    
    foreach ($user_query->get_results() as $user) {
        $results[] = [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
        ];
    }
    
    // Also search recent engagement letters
    global $wpdb;
    
    $engagement_results = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, pm.meta_value as form_data
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
         WHERE p.post_type = %s
         AND pm.meta_key = %s
         AND (
             pm.meta_value LIKE %s
             OR p.post_title LIKE %s
         )
         ORDER BY p.post_modified DESC
         LIMIT 10",
        EL_CPT_ENGAGEMENT,
        EL_META_FORM_DATA,
        '%' . $wpdb->esc_like($search_term) . '%',
        '%' . $wpdb->esc_like($search_term) . '%'
    ));
    
    foreach ($engagement_results as $result) {
        $form_data = maybe_unserialize($result->form_data);
        
        if (!empty($form_data['email'])) {
            $results[] = [
                'id' => 0,
                'engagement_id' => $result->ID,
                'name' => trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '')),
                'email' => $form_data['email'] ?? '',
                'first_name' => $form_data['first_name'] ?? '',
                'last_name' => $form_data['last_name'] ?? '',
                'form_data' => $form_data,
            ];
        }
    }
    
    // Remove duplicates by email
    $unique_results = [];
    $emails = [];
    
    foreach ($results as $result) {
        if (!in_array($result['email'], $emails)) {
            $unique_results[] = $result;
            $emails[] = $result['email'];
        }
    }
    
    wp_send_json_success([
        'results' => $unique_results,
        'count' => count($unique_results),
    ]);
}
add_action('wp_ajax_' . EL_AJAX_SEARCH_CLIENTS, 'el_ajax_search_clients');

/**
 * AJAX handler: Loads existing client data
 */
function el_ajax_load_client() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    
    $client_data = [];
    
    if ($user_id) {
        // Load from WordPress user
        $user = get_userdata($user_id);
        
        if ($user) {
            $client_data = [
                'first_name' => get_user_meta($user_id, 'first_name', true),
                'last_name' => get_user_meta($user_id, 'last_name', true),
                'email' => $user->user_email,
                'phone' => get_user_meta($user_id, 'billing_phone', true),
                'street_address' => get_user_meta($user_id, 'billing_address_1', true),
                'city' => get_user_meta($user_id, 'billing_city', true),
                'state' => get_user_meta($user_id, 'billing_state', true),
                'zip' => get_user_meta($user_id, 'billing_postcode', true),
                'country' => get_user_meta($user_id, 'billing_country', true),
            ];
            
            el_set_session(EL_SESSION_CLIENT_ID, $user_id);
        }
    } elseif ($engagement_id) {
        // Load from previous engagement
        $engagement = el_get_engagement_letter($engagement_id);
        
        if ($engagement && !empty($engagement['form_data'])) {
            $client_data = $engagement['form_data'];
        }
    }
    
    if (!empty($client_data)) {
        wp_send_json_success(['client_data' => $client_data]);
    } else {
        wp_send_json_error(['message' => 'Client data not found']);
    }
}
add_action('wp_ajax_el_load_client', 'el_ajax_load_client');

// ============================================
// NO CLIENT MODE
// ============================================

/**
 * AJAX handler: Enables "no client" mode (template only)
 */
function el_ajax_enable_no_client_mode() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    el_set_session(EL_SESSION_NO_CLIENT_MODE, true);
    
    // Create engagement letter without client data
    $engagement_id = el_get_current_engagement_id();
    
    if (!$engagement_id) {
        $engagement_id = el_create_engagement_letter([
            'title' => 'Engagement Letter Template - ' . date('Y-m-d'),
            'form_data' => [],
        ]);
    }
    
    wp_send_json_success([
        'message' => 'No client mode enabled',
        'engagement_id' => $engagement_id,
    ]);
}
add_action('wp_ajax_el_enable_no_client_mode', 'el_ajax_enable_no_client_mode');

// ============================================
// SHORTCODES
// ============================================

/**
 * Shortcode: Client form container
 * 
 * Usage: [el_client_form]
 */
function el_client_form_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to create an engagement letter.</p>';
    }
    
    // Check if form data already in session
    $form_data = el_get_session(EL_SESSION_FORM_DATA, []);
    
    $output = '<div class="el-client-form-wrapper">';
    
    // Display saved client info if exists
    if (!empty($form_data)) {
        $output .= '<div class="el-saved-client-info" style="
            background: ' . EL_COLOR_BG_LIGHT . ';
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        ">';
        $output .= '<h4 style="margin: 0 0 10px 0;">Saved Client Information</h4>';
        $output .= '<p style="margin: 5px 0;"><strong>Name:</strong> ' . esc_html($form_data['first_name'] . ' ' . $form_data['last_name']) . '</p>';
        $output .= '<p style="margin: 5px 0;"><strong>Email:</strong> ' . esc_html($form_data['email']) . '</p>';
        $output .= '<button type="button" class="button el-edit-client">Edit Client Details</button>';
        $output .= '</div>';
    }
    
    // Client search
    $output .= '<div class="el-client-search" style="margin-bottom: 30px;">';
    $output .= '<h3>Search Existing Clients</h3>';
    $output .= '<div style="display: flex; gap: 10px; margin-bottom: 15px;">';
    $output .= '<input type="text" id="elClientSearch" placeholder="Search by name or email..." style="flex: 1; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">';
    $output .= '<button type="button" id="elSearchButton" class="button">Search</button>';
    $output .= '</div>';
    $output .= '<div id="elSearchResults"></div>';
    $output .= '</div>';
    
    $output .= '<div class="el-divider" style="text-align: center; margin: 30px 0;">— OR —</div>';
    
    // Gravity Forms embed
    $output .= '<div class="el-new-client-form">';
    $output .= '<h3>Create New Client</h3>';
    
    // Check if Gravity Forms is active
    if (class_exists('GFForms')) {
        $output .= do_shortcode('[gravityform id="1" title="false" description="false" ajax="true"]');
    } else {
        $output .= '<p class="error">Gravity Forms is not installed. Please install Gravity Forms to use the client form.</p>';
    }
    
    $output .= '</div>';
    
    // No client mode button
    $output .= '<div class="el-no-client-mode" style="margin-top: 30px; text-align: center;">';
    $output .= '<p style="font-size: 13px; color: #6b7280;">Creating a template without a specific client?</p>';
    $output .= '<button type="button" id="elNoClientButton" class="button">Continue Without Client</button>';
    $output .= '</div>';
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('el_client_form', 'el_client_form_shortcode');

/**
 * Shortcode: No client button (standalone)
 * 
 * Usage: [el_no_client_button]
 */
function el_no_client_button_shortcode() {
    return '<button type="button" id="elNoClientButton" class="button el-no-client-btn">Continue Without Client</button>';
}
add_shortcode('el_no_client_button', 'el_no_client_button_shortcode');

// ============================================
// JAVASCRIPT
// ============================================

/**
 * Enqueues Tab 1 JavaScript
 */
function el_enqueue_tab1_script() {
    if (!function_exists('el_is_wizard_page') || !el_is_wizard_page()) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Client search
            $('#elSearchButton, #elClientSearch').on('click keypress', function(e) {
                // Trigger on button click OR Enter key in search field
                if (e.type === 'keypress' && e.which !== 13) {
                    return;
                }
                
                e.preventDefault();
                
                var searchTerm = $('#elClientSearch').val();
                
                if (searchTerm.length < 2) {
                    alert('Please enter at least 2 characters to search.');
                    return;
                }
                
                // Show loading
                $('#elSearchResults').html('<p style=\"color: #6b7280;\">Searching...</p>');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_SEARCH_CLIENTS . "',
                        nonce: elAjax.nonce,
                        search: searchTerm
                    },
                    success: function(response) {
                        if (response.success && response.data.results.length > 0) {
                            var html = '<div class=\"el-search-results\" style=\"max-height: 300px; overflow-y: auto;\">';
                            
                            $.each(response.data.results, function(i, client) {
                                html += '<div class=\"el-client-result\" style=\"padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;\" data-user-id=\"' + client.id + '\" data-engagement-id=\"' + (client.engagement_id || '') + '\">';
                                html += '<strong>' + client.name + '</strong><br>';
                                html += '<span style=\"font-size: 13px; color: #6b7280;\">' + client.email + '</span>';
                                html += '</div>';
                            });
                            
                            html += '</div>';
                            
                            $('#elSearchResults').html(html);
                        } else {
                            $('#elSearchResults').html('<p style=\"color: #6b7280;\">No clients found matching \"' + searchTerm + '\"</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Client search error:', xhr.responseText);
                        $('#elSearchResults').html('<p style=\"color: #dc2626;\">Search error. Please try again.</p>');
                    }
                });
            });
            
            // Load client on result click
            $(document).on('click', '.el-client-result', function() {
                var userId = $(this).data('user-id');
                var engagementId = $(this).data('engagement-id');
                
                // Show loading state
                $(this).css('opacity', '0.5');
                $('#elSearchResults').prepend('<p style=\"color: #10b981; margin-bottom: 10px;\">Loading client data...</p>');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'el_load_client',
                        nonce: elAjax.nonce,
                        user_id: userId,
                        engagement_id: engagementId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate form fields
                            var data = response.data.client_data;
                            $('input[name=\"first_name\"]').val(data.first_name || '');
                            $('input[name=\"last_name\"]').val(data.last_name || '');
                            $('input[name=\"email\"]').val(data.email || '');
                            $('input[name=\"phone\"]').val(data.phone || '');
                            $('input[name=\"street_address\"]').val(data.street_address || '');
                            $('input[name=\"city\"]').val(data.city || '');
                            $('input[name=\"state\"]').val(data.state || '');
                            $('input[name=\"zip\"]').val(data.zip || '');
                            $('input[name=\"country\"]').val(data.country || '');
                            
                            $('#elSearchResults').html('<p style=\"color: #10b981;\">✓ Client loaded successfully</p>');
                            
                            // Scroll to form
                            $('input[name=\"first_name\"]').focus();
                        } else {
                            $('#elSearchResults').html('<p style=\"color: #dc2626;\">Error loading client data</p>');
                        }
                    },
                    error: function() {
                        $('#elSearchResults').html('<p style=\"color: #dc2626;\">Network error. Please try again.</p>');
                    }
                });
            });
            
            // Hover effect for search results
            $(document).on('mouseenter', '.el-client-result', function() {
                $(this).css({
                    'background': '" . EL_COLOR_BG_LIGHT . "',
                    'transform': 'translateX(4px)'
                });
            }).on('mouseleave', '.el-client-result', function() {
                $(this).css({
                    'background': 'white',
                    'transform': 'translateX(0)'
                });
            });
            
            // No client mode button
            $('#elNoClientButton').on('click', function() {
                if (confirm('Continue without entering client details? You can add them later.')) {
                    $.ajax({
                        url: elAjax.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'el_enable_no_client_mode',
                            nonce: elAjax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Navigate to Tab 2
                                $('" . el_get_tab_selector(2) . "').click();
                            }
                        }
                    });
                }
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_tab1_script');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Tab 1 (Client Details) module loaded successfully', 'info');
}