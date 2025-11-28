<?php
/**
 * Tab 1: User Handler - Minimal Version
 */

if (!defined('ABSPATH')) exit;

// Only disable the registration feed, don't touch validation
add_filter('gform_disable_registration', function($is_disabled, $form, $entry, $feed) {
    $email = rgar($entry, '2');
    if ($email && get_user_by('email', $email)) {
        return true; // Disable registration for existing users
    }
    return $is_disabled;
}, 10, 4);

// Process after submission
add_action('gform_after_submission_1', function($entry, $form) {
    $email = rgar($entry, '2');
    if (empty($email)) return;
    
    $user = get_user_by('email', $email);
    $user_id = $user ? $user->ID : 0;
    
    $form_data = [
        'first_name' => rgar($entry, '1.3'),
        'last_name' => rgar($entry, '1.6'),
        'email' => $email,
        'phone' => rgar($entry, '5'),
        'street_address' => rgar($entry, '6.1'),
        'address_2' => rgar($entry, '6.2'),
        'city' => rgar($entry, '6.3'),
        'state' => rgar($entry, '6.4'),
        'zip' => rgar($entry, '6.5'),
        'country' => rgar($entry, '6.6'),
    ];
    
    // Save to session
    if (function_exists('el_set_session')) {
        el_set_session('EL_SESSION_FORM_DATA', $form_data);
        el_set_session('EL_SESSION_CLIENT_EMAIL', $email);
        if ($user_id) el_set_session('EL_SESSION_CLIENT_ID', $user_id);
    }
}, 10, 2);

// Redirect confirmation
add_filter('gform_confirmation_1', function($confirmation) {
    return '<script>setTimeout(function(){jQuery("#brxe-pxdrgk").trigger("click");},500);</script>';
}, 10);