<?php
/**
 * Engagement Letters Encryption System
 * 
 * Handles AES-256-GCM encryption for all sensitive personal data including
 * client names, emails, addresses, phone numbers, and matter details.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Prevent duplicate class declaration
if (class_exists('EL_Encryption')) {
    return;
}

class EL_Encryption {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Encryption cipher
     */
    const CIPHER = 'aes-256-gcm';
    
    /**
     * Encryption key option name
     */
    const KEY_OPTION = 'el_encryption_key';
    
    /**
     * Salt option name
     */
    const SALT_OPTION = 'el_encryption_salt';
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - initialize hooks
     */
    private function __construct() {
        // Initialize encryption key
        add_action('init', [$this, 'ensure_encryption_key'], 1);
        
        // Hook into post save to encrypt data
        add_action('save_post_engagement_letter', [$this, 'encrypt_post_meta'], 5, 2);
        
        // Hook into meta get to decrypt data
        add_filter('get_post_metadata', [$this, 'decrypt_post_meta'], 10, 4);
        
        // Hook into user data save/get
        add_action('profile_update', [$this, 'encrypt_user_meta'], 10, 2);
        add_filter('get_user_metadata', [$this, 'decrypt_user_meta'], 10, 4);
        
        // Admin notice if encryption not available
        add_action('admin_notices', [$this, 'check_encryption_available']);
    }
    
    /**
     * Ensure encryption key exists
     */
    public function ensure_encryption_key() {
        if (!get_option(self::KEY_OPTION)) {
            $this->generate_encryption_key();
        }
        
        if (!get_option(self::SALT_OPTION)) {
            $this->generate_salt();
        }
    }
    
    /**
     * Generate and store encryption key
     */
    private function generate_encryption_key() {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            error_log('EL Encryption: OpenSSL not available - encryption disabled');
            return false;
        }
        
        // Generate 256-bit key
        $key = openssl_random_pseudo_bytes(32);
        $encoded_key = base64_encode($key);
        
        // Store in database (consider moving to wp-config.php for production)
        update_option(self::KEY_OPTION, $encoded_key, false);
        
        error_log('EL Encryption: New encryption key generated');
        
        return true;
    }
    
    /**
     * Generate and store salt
     */
    private function generate_salt() {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            return false;
        }
        
        $salt = openssl_random_pseudo_bytes(16);
        $encoded_salt = base64_encode($salt);
        
        update_option(self::SALT_OPTION, $encoded_salt, false);
        
        return true;
    }
    
    /**
     * Get encryption key
     */
    private function get_encryption_key() {
        // Check for key in wp-config.php first (most secure)
        if (defined('EL_ENCRYPTION_KEY')) {
            return base64_decode(EL_ENCRYPTION_KEY);
        }
        
        // Fall back to database
        $encoded_key = get_option(self::KEY_OPTION);
        
        if (!$encoded_key) {
            return false;
        }
        
        return base64_decode($encoded_key);
    }
    
    /**
     * Get salt
     */
    private function get_salt() {
        // Check for salt in wp-config.php first
        if (defined('EL_ENCRYPTION_SALT')) {
            return base64_decode(EL_ENCRYPTION_SALT);
        }
        
        // Fall back to database
        $encoded_salt = get_option(self::SALT_OPTION);
        
        if (!$encoded_salt) {
            return false;
        }
        
        return base64_decode($encoded_salt);
    }
    
    /**
     * Encrypt data
     */
    public function encrypt($data) {
        if (empty($data) || !is_string($data)) {
            return $data;
        }
        
        // Check if OpenSSL is available
        if (!function_exists('openssl_encrypt')) {
            error_log('EL Encryption: OpenSSL not available');
            return $data; // Return unencrypted if not available
        }
        
        $key = $this->get_encryption_key();
        $salt = $this->get_salt();
        
        if (!$key || !$salt) {
            error_log('EL Encryption: Missing key or salt');
            return $data;
        }
        
        // Generate initialization vector
        $iv_length = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        // Encrypt
        $tag = '';
        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $salt
        );
        
        if ($encrypted === false) {
            error_log('EL Encryption: Encryption failed - ' . openssl_error_string());
            return $data;
        }
        
        // Combine IV + tag + encrypted data
        $result = base64_encode($iv . $tag . $encrypted);
        
        // Add prefix to identify encrypted data
        return 'ENC:' . $result;
    }
    
    /**
     * Decrypt data
     */
    public function decrypt($data) {
        if (empty($data) || !is_string($data)) {
            return $data;
        }
        
        // Check if data is encrypted
        if (substr($data, 0, 4) !== 'ENC:') {
            return $data; // Not encrypted
        }
        
        // Check if OpenSSL is available
        if (!function_exists('openssl_decrypt')) {
            error_log('EL Encryption: OpenSSL not available for decryption');
            return '[ENCRYPTED]'; // Return placeholder
        }
        
        $key = $this->get_encryption_key();
        $salt = $this->get_salt();
        
        if (!$key || !$salt) {
            error_log('EL Encryption: Missing key or salt for decryption');
            return '[ENCRYPTED]';
        }
        
        // Remove prefix and decode
        $encoded = substr($data, 4);
        $decoded = base64_decode($encoded);
        
        if ($decoded === false) {
            error_log('EL Encryption: Base64 decode failed');
            return '[ENCRYPTED]';
        }
        
        // Extract IV, tag, and encrypted data
        $iv_length = openssl_cipher_iv_length(self::CIPHER);
        $tag_length = 16; // GCM tag is always 16 bytes
        
        $iv = substr($decoded, 0, $iv_length);
        $tag = substr($decoded, $iv_length, $tag_length);
        $encrypted = substr($decoded, $iv_length + $tag_length);
        
        // Decrypt
        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $salt
        );
        
        if ($decrypted === false) {
            error_log('EL Encryption: Decryption failed - ' . openssl_error_string());
            return '[ENCRYPTED]';
        }
        
        return $decrypted;
    }
    
    /**
     * Encrypt post meta on save
     */
    public function encrypt_post_meta($post_id, $post) {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Meta keys to encrypt
        $meta_keys = [
            '_el_matter_reference',
            '_el_matter_notes',
            '_el_internal_notes',
            '_el_form_data'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $value = get_post_meta($post_id, $meta_key, true);
            
            if (empty($value)) {
                continue;
            }
            
            // Skip if already encrypted
            if (is_string($value) && substr($value, 0, 4) === 'ENC:') {
                continue;
            }
            
            // Encrypt based on data type
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            $encrypted = $this->encrypt($value);
            
            if ($encrypted !== $value) {
                // Remove filter temporarily to avoid recursion
                remove_filter('get_post_metadata', [$this, 'decrypt_post_meta'], 10);
                
                update_post_meta($post_id, $meta_key, $encrypted);
                
                // Re-add filter
                add_filter('get_post_metadata', [$this, 'decrypt_post_meta'], 10, 4);
            }
        }
    }
    
    /**
     * Decrypt post meta on get
     */
    public function decrypt_post_meta($value, $object_id, $meta_key, $single) {
        // Only decrypt our specific meta keys
        $encrypted_meta_keys = [
            '_el_matter_reference',
            '_el_matter_notes',
            '_el_internal_notes',
            '_el_form_data'
        ];
        
        if (!in_array($meta_key, $encrypted_meta_keys)) {
            return $value;
        }
        
        // Get the actual value from database if needed
        if ($value === null) {
            global $wpdb;
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
                $object_id,
                $meta_key
            ));
        }
        
        if (empty($value)) {
            return $value;
        }
        
        // Handle serialized arrays
        if (is_serialized($value)) {
            $value = maybe_unserialize($value);
        }
        
        // Decrypt if encrypted
        if (is_string($value) && substr($value, 0, 4) === 'ENC:') {
            $decrypted = $this->decrypt($value);
            
            // Try to decode JSON if it was an array/object
            if ($meta_key === '_el_form_data') {
                $decoded = json_decode($decrypted, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decrypted = $decoded;
                }
            }
            
            return $single ? $decrypted : [$decrypted];
        }
        
        return $value;
    }
    
    /**
     * Encrypt user meta on save
     */
    public function encrypt_user_meta($user_id, $old_user_data) {
        $meta_keys = [
            'first_name',
            'last_name',
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_country',
            'billing_phone',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
            'shipping_country'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $value = get_user_meta($user_id, $meta_key, true);
            
            if (empty($value)) {
                continue;
            }
            
            // Skip if already encrypted
            if (is_string($value) && substr($value, 0, 4) === 'ENC:') {
                continue;
            }
            
            $encrypted = $this->encrypt($value);
            
            if ($encrypted !== $value) {
                // Remove filter temporarily
                remove_filter('get_user_metadata', [$this, 'decrypt_user_meta'], 10);
                
                update_user_meta($user_id, $meta_key, $encrypted);
                
                // Re-add filter
                add_filter('get_user_metadata', [$this, 'decrypt_user_meta'], 10, 4);
            }
        }
    }
    
    /**
     * Decrypt user meta on get
     */
    public function decrypt_user_meta($value, $object_id, $meta_key, $single) {
        $encrypted_meta_keys = [
            'first_name',
            'last_name',
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_country',
            'billing_phone',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
            'shipping_country'
        ];
        
        if (!in_array($meta_key, $encrypted_meta_keys)) {
            return $value;
        }
        
        // Get the actual value from database if needed
        if ($value === null) {
            global $wpdb;
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",
                $object_id,
                $meta_key
            ));
        }
        
        if (empty($value)) {
            return $value;
        }
        
        // Decrypt if encrypted
        if (is_string($value) && substr($value, 0, 4) === 'ENC:') {
            $decrypted = $this->decrypt($value);
            return $single ? $decrypted : [$decrypted];
        }
        
        return $value;
    }
    
    /**
     * Check if encryption is available
     */
    public function check_encryption_available() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'engagement_letter') {
            return;
        }
        
        if (!function_exists('openssl_encrypt')) {
            ?>
            <div class="notice notice-error">
                <p><strong>Engagement Letter Encryption:</strong> OpenSSL is not available on your server. Personal data encryption is disabled. Please contact your hosting provider.</p>
            </div>
            <?php
        } elseif (!in_array(self::CIPHER, openssl_get_cipher_methods())) {
            ?>
            <div class="notice notice-error">
                <p><strong>Engagement Letter Encryption:</strong> AES-256-GCM cipher is not available. Personal data encryption is disabled.</p>
            </div>
            <?php
        }
    }
    
    /**
     * Encrypt existing data (migration tool)
     */
    public static function migrate_encrypt_existing_data() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $instance = self::get_instance();
        $encrypted_count = 0;
        
        // Get all engagement letters
        $letters = get_posts([
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ]);
        
        foreach ($letters as $letter) {
            // Trigger encryption
            $instance->encrypt_post_meta($letter->ID, $letter);
            $encrypted_count++;
        }
        
        // Get all customers
        $users = get_users(['role' => 'customer']);
        
        foreach ($users as $user) {
            // Trigger encryption
            $instance->encrypt_user_meta($user->ID, null);
            $encrypted_count++;
        }
        
        return $encrypted_count;
    }
    
    /**
     * Export encryption key (for backup)
     */
    public static function export_encryption_key() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $key = get_option(self::KEY_OPTION);
        $salt = get_option(self::SALT_OPTION);
        
        if (!$key || !$salt) {
            wp_die('Encryption keys not found');
        }
        
        $export = [
            'key' => $key,
            'salt' => $salt,
            'cipher' => self::CIPHER,
            'generated' => current_time('mysql'),
            'site_url' => get_site_url()
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="el-encryption-keys-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    }
}

// Initialize
EL_Encryption::get_instance();