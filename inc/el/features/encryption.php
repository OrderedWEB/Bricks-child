<?php
/**
 * Engagement Letter System - Encryption & Security
 * 
 * Handles data encryption and security:
 * - AES-256-GCM encryption for sensitive data
 * - Per-file encryption keys
 * - Secure key storage and rotation
 * - GDPR compliance features
 * - Data sanitization and validation
 * 
 * LOAD ORDER: Feature module (after core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// MASTER ENCRYPTION KEY
// ============================================

/**
 * Retrieves or generates master encryption key
 * 
 * Stored securely in WordPress options (ideally move to wp-config.php).
 * 
 * @return string Master encryption key
 */
function el_get_encryption_key() {
    $key = get_option(EL_ENCRYPTION_KEY_OPTION);
    
    if (!$key) {
        // Generate new master key
        $key = base64_encode(random_bytes(32)); // 256-bit key
        update_option(EL_ENCRYPTION_KEY_OPTION, $key, false); // No autoload
        
        if (EL_DEBUG_MODE) {
            el_log('Generated new master encryption key', 'info');
        }
    }
    
    return base64_decode($key);
}

/**
 * Rotates master encryption key
 * 
 * WARNING: Re-encrypts all data. Run during maintenance window.
 * 
 * @return bool True if rotation successful
 */
function el_rotate_master_key() {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $old_key = el_get_encryption_key();
    
    // Generate new key
    $new_key = random_bytes(32);
    
    // Find all encrypted data
    global $wpdb;
    
    $posts = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key LIKE %s",
        '_el_encrypted_%'
    ));
    
    if (empty($posts)) {
        // No encrypted data, just update key
        update_option(EL_ENCRYPTION_KEY_OPTION, base64_encode($new_key), false);
        return true;
    }
    
    // Re-encrypt all data with new key
    foreach ($posts as $post_id) {
        $meta_keys = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_key FROM {$wpdb->postmeta} 
             WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            '_el_encrypted_%'
        ));
        
        foreach ($meta_keys as $meta_key) {
            $encrypted = get_post_meta($post_id, $meta_key, true);
            
            // Decrypt with old key
            $decrypted = el_decrypt_data($encrypted, $old_key);
            
            if ($decrypted) {
                // Re-encrypt with new key
                $re_encrypted = el_encrypt_data($decrypted, $new_key);
                update_post_meta($post_id, $meta_key, $re_encrypted);
            }
        }
    }
    
    // Update master key
    update_option(EL_ENCRYPTION_KEY_OPTION, base64_encode($new_key), false);
    
    if (EL_DEBUG_MODE) {
        el_log('Master key rotated - ' . count($posts) . ' posts re-encrypted', 'info');
    }
    
    return true;
}

// ============================================
// DATA ENCRYPTION
// ============================================

/**
 * Encrypts data using AES-256-GCM
 * 
 * @param mixed  $data Data to encrypt
 * @param string $key  Encryption key (null = use master key)
 * @return string|false Encrypted data or false on failure
 */
function el_encrypt_data($data, $key = null) {
    if ($key === null) {
        $key = el_get_encryption_key();
    }
    
    // Serialize data if not string
    if (!is_string($data)) {
        $data = serialize($data);
    }
    
    // Generate initialization vector
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
    
    // Encrypt data
    $encrypted = openssl_encrypt(
        $data,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    if ($encrypted === false) {
        el_log('Encryption failed: ' . openssl_error_string(), 'error');
        return false;
    }
    
    // Combine IV + encrypted data + authentication tag
    $result = base64_encode($iv . $encrypted . $tag);
    
    return $result;
}

/**
 * Decrypts data using AES-256-GCM
 * 
 * @param string $encrypted Encrypted data
 * @param string $key       Encryption key (null = use master key)
 * @return mixed|false Decrypted data or false on failure
 */
function el_decrypt_data($encrypted, $key = null) {
    if ($key === null) {
        $key = el_get_encryption_key();
    }
    
    $encrypted = base64_decode($encrypted);
    
    if ($encrypted === false) {
        return false;
    }
    
    // Extract IV, data, and tag
    $iv_length = openssl_cipher_iv_length('aes-256-gcm');
    $tag_length = 16; // GCM tag is always 16 bytes
    
    $iv = substr($encrypted, 0, $iv_length);
    $tag = substr($encrypted, -$tag_length);
    $ciphertext = substr($encrypted, $iv_length, -$tag_length);
    
    // Decrypt data
    $decrypted = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    if ($decrypted === false) {
        el_log('Decryption failed: ' . openssl_error_string(), 'error');
        return false;
    }
    
    // Try to unserialize if it was serialized
    $unserialized = @unserialize($decrypted);
    
    return $unserialized !== false ? $unserialized : $decrypted;
}

// ============================================
// SECURE STORAGE HELPERS
// ============================================

/**
 * Stores encrypted data in post meta
 * 
 * @param int    $post_id Post ID
 * @param string $key     Meta key (will be prefixed with _el_encrypted_)
 * @param mixed  $data    Data to encrypt and store
 * @return bool True if stored successfully
 */
function el_store_encrypted($post_id, $key, $data) {
    $encrypted = el_encrypt_data($data);
    
    if ($encrypted === false) {
        return false;
    }
    
    $meta_key = '_el_encrypted_' . $key;
    
    return update_post_meta($post_id, $meta_key, $encrypted);
}

/**
 * Retrieves and decrypts data from post meta
 * 
 * @param int    $post_id Post ID
 * @param string $key     Meta key (without _el_encrypted_ prefix)
 * @param mixed  $default Default value if not found
 * @return mixed Decrypted data or default
 */
function el_retrieve_encrypted($post_id, $key, $default = null) {
    $meta_key = '_el_encrypted_' . $key;
    $encrypted = get_post_meta($post_id, $meta_key, true);
    
    if (!$encrypted) {
        return $default;
    }
    
    $decrypted = el_decrypt_data($encrypted);
    
    return $decrypted !== false ? $decrypted : $default;
}

/**
 * Deletes encrypted data from post meta
 * 
 * @param int    $post_id Post ID
 * @param string $key     Meta key
 * @return bool True if deleted successfully
 */
function el_delete_encrypted($post_id, $key) {
    $meta_key = '_el_encrypted_' . $key;
    return delete_post_meta($post_id, $meta_key);
}

// ============================================
// FILE ENCRYPTION (Per-File Keys)
// ============================================

/**
 * Encrypts file with unique per-file key
 * 
 * More secure than using master key for all files.
 * 
 * @param string $file_path Path to file
 * @param int    $post_id   Associated post ID (for key storage)
 * @return string|false Encrypted file path or false on failure
 */
function el_encrypt_file($file_path, $post_id) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    // Read file
    $contents = file_get_contents($file_path);
    
    if ($contents === false) {
        return false;
    }
    
    // Generate per-file key
    $file_key = random_bytes(32);
    
    // Encrypt file contents
    $encrypted = el_encrypt_data($contents, $file_key);
    
    if ($encrypted === false) {
        return false;
    }
    
    // Store encrypted file
    $encrypted_path = $file_path . '.encrypted';
    file_put_contents($encrypted_path, $encrypted);
    
    // Store file key (encrypted with master key)
    el_store_encrypted($post_id, 'file_key_' . basename($file_path), $file_key);
    
    // Delete original file
    unlink($file_path);
    
    return $encrypted_path;
}

/**
 * Decrypts file using stored per-file key
 * 
 * @param string $encrypted_path Path to encrypted file
 * @param int    $post_id        Associated post ID
 * @return string|false Decrypted file path or false on failure
 */
function el_decrypt_file($encrypted_path, $post_id) {
    if (!file_exists($encrypted_path)) {
        return false;
    }
    
    // Read encrypted file
    $encrypted = file_get_contents($encrypted_path);
    
    if ($encrypted === false) {
        return false;
    }
    
    // Retrieve file key
    $file_name = str_replace('.encrypted', '', basename($encrypted_path));
    $file_key = el_retrieve_encrypted($post_id, 'file_key_' . $file_name);
    
    if (!$file_key) {
        return false;
    }
    
    // Decrypt contents
    $decrypted = el_decrypt_data($encrypted, $file_key);
    
    if ($decrypted === false) {
        return false;
    }
    
    // Write decrypted file
    $decrypted_path = str_replace('.encrypted', '', $encrypted_path);
    file_put_contents($decrypted_path, $decrypted);
    
    return $decrypted_path;
}

// ============================================
// GDPR COMPLIANCE
// ============================================

/**
 * Anonymises personal data in engagement letter
 * 
 * GDPR right to erasure - removes identifiable data.
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return bool True if anonymised successfully
 */
function el_anonymise_personal_data($engagement_id) {
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    // Anonymise form data
    $form_data = el_get_meta($engagement_id, 'form_data');
    
    if (!empty($form_data)) {
        $form_data['first_name'] = 'ANONYMISED';
        $form_data['last_name'] = 'USER';
        $form_data['email'] = 'anonymised@example.com';
        $form_data['phone'] = '';
        $form_data['street_address'] = '';
        $form_data['city'] = '';
        $form_data['state'] = '';
        $form_data['zip'] = '';
        
        el_set_meta($engagement_id, 'form_data', $form_data);
    }
    
    // Remove signature data
    el_delete_meta($engagement_id, 'signature_data');
    el_delete_meta($engagement_id, 'signature_ip');
    
    // Remove access logs
    el_delete_meta($engagement_id, 'access_log');
    
    // Update client ID to 0
    el_set_meta($engagement_id, 'client_id', 0);
    
    // Mark as anonymised
    el_set_meta($engagement_id, 'anonymised', true);
    el_set_meta($engagement_id, 'anonymised_date', current_time('mysql'));
    
    if (EL_DEBUG_MODE) {
        el_log('Personal data anonymised for engagement ' . $engagement_id, 'info');
    }
    
    return true;
}

/**
 * Exports personal data for GDPR data portability
 * 
 * @param int $user_id User ID
 * @return array Exportable data
 */
function el_export_personal_data($user_id) {
    global $wpdb;
    
    // Find user's engagement letters
    $posts = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = %s AND post_author = %d",
        EL_CPT_ENGAGEMENT,
        $user_id
    ));
    
    $export = [];
    
    foreach ($posts as $post_id) {
        $engagement = el_get_engagement_letter($post_id);
        
        $export[] = [
            'reference' => $engagement['reference'],
            'status' => $engagement['status'],
            'created_date' => $engagement['created_date'],
            'form_data' => $engagement['form_data'],
            'practice_area' => $engagement['practice_area'],
        ];
    }
    
    return $export;
}

// ============================================
// DATA SANITIZATION
// ============================================

/**
 * Sanitises and validates client data
 * 
 * @param array $data Raw client data
 * @return array Sanitised data
 */
function el_sanitize_client_data($data) {
    return [
        'first_name' => sanitize_text_field($data['first_name'] ?? ''),
        'last_name' => sanitize_text_field($data['last_name'] ?? ''),
        'email' => sanitize_email($data['email'] ?? ''),
        'phone' => sanitize_text_field($data['phone'] ?? ''),
        'street_address' => sanitize_text_field($data['street_address'] ?? ''),
        'city' => sanitize_text_field($data['city'] ?? ''),
        'state' => sanitize_text_field($data['state'] ?? ''),
        'zip' => sanitize_text_field($data['zip'] ?? ''),
        'country' => sanitize_text_field($data['country'] ?? ''),
        'notes' => sanitize_textarea_field($data['notes'] ?? ''),
    ];
}

/**
 * Validates required client fields
 * 
 * @param array $data Client data
 * @return array Validation result [valid => bool, errors => array]
 */
function el_validate_client_data($data) {
    $validation = [
        'valid' => true,
        'errors' => [],
    ];
    
    // Required fields
    $required = ['first_name', 'last_name', 'email'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $validation['valid'] = false;
            $validation['errors'][] = ucwords(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    // Email format
    if (!empty($data['email']) && !is_email($data['email'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'Invalid email address.';
    }
    
    return $validation;
}

// ============================================
// SECURE DELETION
// ============================================

/**
 * Securely deletes engagement letter and all associated data
 * 
 * @param int  $engagement_id Engagement letter post ID
 * @param bool $force         Force permanent deletion (bypass trash)
 * @return bool True if deleted successfully
 */
function el_secure_delete($engagement_id, $force = false) {
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    // Delete encrypted data
    global $wpdb;
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} 
         WHERE post_id = %d AND meta_key LIKE %s",
        $engagement_id,
        '_el_encrypted_%'
    ));
    
    // Delete all post meta
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} 
         WHERE post_id = %d AND meta_key LIKE %s",
        $engagement_id,
        '_el_%'
    ));
    
    // Delete associated files
    $attachment_id = el_get_meta($engagement_id, 'pdf_attachment_id');
    
    if ($attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }
    
    // Delete post
    if ($force) {
        wp_delete_post($engagement_id, true);
    } else {
        wp_trash_post($engagement_id);
    }
    
    if (EL_DEBUG_MODE) {
        el_log('Engagement ' . $engagement_id . ' securely deleted', 'info');
    }
    
    return true;
}

// ============================================
// AJAX HANDLERS (Admin Only)
// ============================================

/**
 * AJAX: Rotate master encryption key
 */
function el_ajax_rotate_key() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    $result = el_rotate_master_key();
    
    if ($result) {
        wp_send_json_success(['message' => 'Encryption key rotated successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to rotate key']);
    }
}
add_action('wp_ajax_el_rotate_key', 'el_ajax_rotate_key');

/**
 * AJAX: Anonymise personal data
 */
function el_ajax_anonymise_data() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'Invalid engagement ID']);
    }
    
    $result = el_anonymise_personal_data($engagement_id);
    
    if ($result) {
        wp_send_json_success(['message' => 'Personal data anonymised']);
    } else {
        wp_send_json_error(['message' => 'Failed to anonymise data']);
    }
}
add_action('wp_ajax_el_anonymise_data', 'el_ajax_anonymise_data');

/**
 * AJAX: Export personal data
 */
function el_ajax_export_personal_data() {
    check_ajax_referer(EL_NONCE, 'nonce');
    
    $user_id = intval($_POST['user_id'] ?? get_current_user_id());
    
    // Verify permission (self or admin)
    if ($user_id !== get_current_user_id() && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }
    
    $data = el_export_personal_data($user_id);
    
    wp_send_json_success([
        'data' => $data,
        'count' => count($data),
    ]);
}
add_action('wp_ajax_el_export_personal_data', 'el_ajax_export_personal_data');

// ============================================
// PASSWORD PROTECTION (Optional)
// ============================================

/**
 * Generates secure random password
 * 
 * @param int $length Password length (default: 16)
 * @return string Random password
 */
function el_generate_secure_password($length = 16) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Hashes password using WordPress standards
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function el_hash_password($password) {
    return wp_hash_password($password);
}

/**
 * Verifies password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash     Hashed password
 * @return bool True if password matches
 */
function el_verify_password($password, $hash) {
    return wp_check_password($password, $hash);
}

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Encryption module loaded successfully', 'info');
}