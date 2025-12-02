<?php
/**
 * SLM DMS Encryption
 * 
 * AES-256-GCM encryption with per-file derived keys:
 * - Master key stored outside webroot
 * - HKDF key derivation per file UUID
 * - Authenticated encryption with GCM
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Encryption {
    
    /**
     * Cipher algorithm
     */
    const CIPHER = 'aes-256-gcm';
    
    /**
     * IV length for GCM (96 bits recommended)
     */
    const IV_LENGTH = 12;
    
    /**
     * Auth tag length for GCM
     */
    const TAG_LENGTH = 16;
    
    /**
     * Key length (256 bits)
     */
    const KEY_LENGTH = 32;
    
    /**
     * Cached master key
     */
    private static $master_key = null;
    
    /**
     * Initialize
     */
    public static function init() {
        // Verify encryption requirements
        if (!self::verify_requirements()) {
            add_action('admin_notices', [__CLASS__, 'show_requirements_notice']);
        }
    }
    
    /**
     * Verify system requirements
     */
    public static function verify_requirements() {
        // Check OpenSSL
        if (!extension_loaded('openssl')) {
            return false;
        }
        
        // Check cipher availability
        if (!in_array(self::CIPHER, openssl_get_cipher_methods())) {
            return false;
        }
        
        // Check for master key
        if (!self::get_master_key()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Show requirements notice
     */
    public static function show_requirements_notice() {
        $message = __('SLM DMS: Encryption requirements not met. Please ensure OpenSSL is installed and encryption key is configured.', 'flavor');
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }
    
    /**
     * Get master encryption key
     */
    public static function get_master_key() {
        if (self::$master_key !== null) {
            return self::$master_key;
        }
        
        // Priority 1: Constant defined in wp-config.php or secure config
        if (defined('SLM_MASTER_ENCRYPTION_KEY')) {
            $key = SLM_MASTER_ENCRYPTION_KEY;
            
            // Handle base64 encoded keys
            if (strpos($key, 'base64:') === 0) {
                $key = base64_decode(substr($key, 7));
            }
            
            self::$master_key = $key;
            return self::$master_key;
        }
        
        // Priority 2: Separate config file outside webroot
        $config_paths = [
            '/home/site/secure-config/encryption-keys.php',
            dirname(ABSPATH) . '/secure-config/encryption-keys.php',
            ABSPATH . '../secure-config/encryption-keys.php',
        ];
        
        foreach ($config_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (defined('SLM_MASTER_ENCRYPTION_KEY')) {
                    $key = SLM_MASTER_ENCRYPTION_KEY;
                    if (strpos($key, 'base64:') === 0) {
                        $key = base64_decode(substr($key, 7));
                    }
                    self::$master_key = $key;
                    return self::$master_key;
                }
            }
        }
        
        // Priority 3: Database option (less secure, for development)
        $db_key = get_option('slm_encryption_key');
        if ($db_key) {
            if (strpos($db_key, 'base64:') === 0) {
                $db_key = base64_decode(substr($db_key, 7));
            } else {
                $db_key = base64_decode($db_key);
            }
            self::$master_key = $db_key;
            return self::$master_key;
        }
        
        // No key found
        SLM_DMS::log('No encryption key configured', 'error');
        return null;
    }
    
    /**
     * Derive per-file encryption key using HKDF
     */
    public static function derive_file_key($file_uuid) {
        $master_key = self::get_master_key();
        
        if (!$master_key) {
            return null;
        }
        
        // Use HKDF to derive a unique key for this file
        return hash_hkdf(
            'sha256',
            $master_key,
            self::KEY_LENGTH,
            'slm_document_encryption',
            $file_uuid
        );
    }
    
    /**
     * Encrypt file content
     * 
     * @param string $plaintext Raw file content
     * @param string $file_uuid Unique file identifier
     * @return array|false ['ciphertext', 'iv', 'tag'] or false on failure
     */
    public static function encrypt($plaintext, $file_uuid) {
        $key = self::derive_file_key($file_uuid);
        
        if (!$key) {
            SLM_DMS::log('Failed to derive encryption key for ' . $file_uuid, 'error');
            return false;
        }
        
        // Generate random IV
        $iv = random_bytes(self::IV_LENGTH);
        
        // Encrypt with GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '', // Additional authenticated data (empty)
            self::TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            SLM_DMS::log('Encryption failed for ' . $file_uuid, 'error');
            return false;
        }
        
        return [
            'ciphertext' => $ciphertext,
            'iv' => $iv,
            'tag' => $tag,
        ];
    }
    
    /**
     * Decrypt file content
     * 
     * @param string $ciphertext Encrypted content
     * @param string $iv Initialization vector
     * @param string $tag Authentication tag
     * @param string $file_uuid Unique file identifier
     * @return string|false Decrypted content or false on failure
     */
    public static function decrypt($ciphertext, $iv, $tag, $file_uuid) {
        $key = self::derive_file_key($file_uuid);
        
        if (!$key) {
            SLM_DMS::log('Failed to derive decryption key for ' . $file_uuid, 'error');
            return false;
        }
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($plaintext === false) {
            SLM_DMS::log('Decryption failed for ' . $file_uuid . ' - authentication failed', 'error');
            return false;
        }
        
        return $plaintext;
    }
    
    /**
     * Encrypt and store file
     * 
     * @param string $source_path Path to source file
     * @param string $file_uuid Unique file identifier
     * @param string $dest_path Destination path for encrypted file
     * @return array|false Encryption metadata or false on failure
     */
    public static function encrypt_file($source_path, $file_uuid, $dest_path) {
        if (!file_exists($source_path)) {
            SLM_DMS::log('Source file not found: ' . $source_path, 'error');
            return false;
        }
        
        $plaintext = file_get_contents($source_path);
        
        if ($plaintext === false) {
            SLM_DMS::log('Failed to read source file: ' . $source_path, 'error');
            return false;
        }
        
        $encrypted = self::encrypt($plaintext, $file_uuid);
        
        if (!$encrypted) {
            return false;
        }
        
        // Ensure destination directory exists
        $dest_dir = dirname($dest_path);
        if (!file_exists($dest_dir)) {
            wp_mkdir_p($dest_dir);
        }
        
        // Write encrypted file: IV + Tag + Ciphertext
        $encrypted_content = $encrypted['iv'] . $encrypted['tag'] . $encrypted['ciphertext'];
        
        if (file_put_contents($dest_path, $encrypted_content) === false) {
            SLM_DMS::log('Failed to write encrypted file: ' . $dest_path, 'error');
            return false;
        }
        
        // Calculate hash of original content
        $file_hash = hash('sha256', $plaintext);
        
        // Clear plaintext from memory
        $plaintext = null;
        
        return [
            'file_uuid' => $file_uuid,
            'file_path' => $dest_path,
            'file_size' => strlen($encrypted_content),
            'file_hash' => $file_hash,
            'encryption_iv' => bin2hex($encrypted['iv']),
            'encryption_tag' => bin2hex($encrypted['tag']),
        ];
    }
    
    /**
     * Decrypt and return file content
     * 
     * @param string $encrypted_path Path to encrypted file
     * @param string $file_uuid Unique file identifier
     * @return string|false Decrypted content or false on failure
     */
    public static function decrypt_file($encrypted_path, $file_uuid) {
        if (!file_exists($encrypted_path)) {
            SLM_DMS::log('Encrypted file not found: ' . $encrypted_path, 'error');
            return false;
        }
        
        $encrypted_content = file_get_contents($encrypted_path);
        
        if ($encrypted_content === false) {
            SLM_DMS::log('Failed to read encrypted file: ' . $encrypted_path, 'error');
            return false;
        }
        
        // Extract IV, Tag, Ciphertext
        $iv = substr($encrypted_content, 0, self::IV_LENGTH);
        $tag = substr($encrypted_content, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($encrypted_content, self::IV_LENGTH + self::TAG_LENGTH);
        
        return self::decrypt($ciphertext, $iv, $tag, $file_uuid);
    }
    
    /**
     * Decrypt file using stored metadata
     * 
     * @param string $encrypted_path Path to encrypted file
     * @param string $file_uuid Unique file identifier
     * @param string $stored_iv Hex-encoded IV from database
     * @param string $stored_tag Hex-encoded tag from database
     * @return string|false Decrypted content or false on failure
     */
    public static function decrypt_file_with_metadata($encrypted_path, $file_uuid, $stored_iv, $stored_tag) {
        if (!file_exists($encrypted_path)) {
            SLM_DMS::log('Encrypted file not found: ' . $encrypted_path, 'error');
            return false;
        }
        
        $ciphertext = file_get_contents($encrypted_path);
        
        if ($ciphertext === false) {
            SLM_DMS::log('Failed to read encrypted file: ' . $encrypted_path, 'error');
            return false;
        }
        
        // If file includes IV+Tag prefix, strip it
        if (strlen($ciphertext) > self::IV_LENGTH + self::TAG_LENGTH) {
            $prefix_check = substr($ciphertext, 0, self::IV_LENGTH);
            if ($prefix_check === hex2bin($stored_iv)) {
                // File has IV+Tag embedded, use those
                $ciphertext = substr($ciphertext, self::IV_LENGTH + self::TAG_LENGTH);
            }
        }
        
        $iv = hex2bin($stored_iv);
        $tag = hex2bin($stored_tag);
        
        return self::decrypt($ciphertext, $iv, $tag, $file_uuid);
    }
    
    /**
     * Generate a new encryption key (for setup)
     */
    public static function generate_key() {
        return random_bytes(self::KEY_LENGTH);
    }
    
    /**
     * Generate base64-encoded key for config
     */
    public static function generate_key_for_config() {
        return 'base64:' . base64_encode(self::generate_key());
    }
    
    /**
     * Verify file integrity
     */
    public static function verify_file_hash($decrypted_content, $stored_hash) {
        $current_hash = hash('sha256', $decrypted_content);
        return hash_equals($stored_hash, $current_hash);
    }
    
    /**
     * Securely delete a file
     */
    public static function secure_delete($file_path) {
        if (!file_exists($file_path)) {
            return true;
        }
        
        $size = filesize($file_path);
        
        // Overwrite with random data
        $handle = fopen($file_path, 'r+');
        if ($handle) {
            fwrite($handle, random_bytes($size));
            fclose($handle);
        }
        
        // Delete the file
        return unlink($file_path);
    }
    
    /**
     * Create encrypted stream for large files (memory efficient)
     */
    public static function create_decrypt_stream($encrypted_path, $file_uuid) {
        // For very large files, this could be enhanced to use streaming
        // For now, we use the standard decrypt method
        return self::decrypt_file($encrypted_path, $file_uuid);
    }
}
