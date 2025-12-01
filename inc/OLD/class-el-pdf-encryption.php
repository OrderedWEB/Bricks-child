<?php
/**
 * PDF Encryption & Secure Storage for Engagement Letters
 * 
 * Implements AES-256-GCM encryption for PDFs stored on the server.
 * Provides secure storage, retrieval, and lifecycle management.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * SECURITY ARCHITECTURE:
 *   - Encryption: AES-256-GCM (authenticated encryption)
 *   - Key storage: wp-config.php constants (never in database)
 *   - Per-file IV: Unique initialization vector per encrypted file
 *   - Authentication tag: Prevents tampering
 * 
 * REQUIRED CONSTANTS (add to wp-config.php):
 *   define('EL_ENCRYPTION_KEY', 'base64:your-32-byte-key-here');
 *   define('EL_ENCRYPTION_KEY_V1', 'base64:previous-key-for-rotation');
 * 
 * FILE STRUCTURE:
 *   Encrypted file format: [IV (12 bytes)][Auth Tag (16 bytes)][Ciphertext]
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_PDF_Encryption {
    
    /**
     * Encryption algorithm
     */
    const CIPHER = 'aes-256-gcm';
    
    /**
     * IV length in bytes
     */
    const IV_LENGTH = 12;
    
    /**
     * Auth tag length in bytes
     */
    const TAG_LENGTH = 16;
    
    /**
     * Key length in bytes (256 bits)
     */
    const KEY_LENGTH = 32;
    
    /**
     * Default expiry days for encrypted files
     */
    const DEFAULT_EXPIRY_DAYS = 90;
    
    /**
     * Storage directory relative to uploads
     */
    const STORAGE_DIR = 'el-secure-docs';
    
    /**
     * Get encryption key
     * 
     * @param int $version Key version (0 = current, 1 = previous for rotation)
     * @return string|false Binary key or false if not configured
     */
    private static function get_key($version = 0) {
        $constant_name = $version === 0 ? 'EL_ENCRYPTION_KEY' : 'EL_ENCRYPTION_KEY_V' . $version;
        
        if (!defined($constant_name)) {
            error_log('EL Encryption: ' . $constant_name . ' not defined in wp-config.php');
            return false;
        }
        
        $key_string = constant($constant_name);
        
        // Handle base64 encoded keys
        if (strpos($key_string, 'base64:') === 0) {
            $key = base64_decode(substr($key_string, 7));
        } else {
            $key = $key_string;
        }
        
        // Validate key length
        if (strlen($key) !== self::KEY_LENGTH) {
            error_log('EL Encryption: Invalid key length. Expected ' . self::KEY_LENGTH . ' bytes.');
            return false;
        }
        
        return $key;
    }
    
    /**
     * Generate a new encryption key
     * 
     * @return string Base64-encoded key for wp-config.php
     */
    public static function generate_key() {
        $key = random_bytes(self::KEY_LENGTH);
        return 'base64:' . base64_encode($key);
    }
    
    /**
     * Check if encryption is properly configured
     * 
     * @return bool True if encryption is available
     */
    public static function is_configured() {
        return self::get_key() !== false && in_array(self::CIPHER, openssl_get_cipher_methods());
    }
    
    /**
     * Encrypt data
     * 
     * @param string $data Data to encrypt
     * @return string|false Encrypted data with IV and tag prepended, or false on failure
     */
    public static function encrypt($data) {
        $key = self::get_key();
        
        if ($key === false) {
            return false;
        }
        
        // Generate random IV
        $iv = random_bytes(self::IV_LENGTH);
        
        // Encrypt with GCM (provides authentication)
        $tag = '';
        $ciphertext = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            error_log('EL Encryption: Encryption failed - ' . openssl_error_string());
            return false;
        }
        
        // Prepend IV and tag to ciphertext
        return $iv . $tag . $ciphertext;
    }
    
    /**
     * Decrypt data
     * 
     * @param string $encrypted_data Data encrypted with encrypt()
     * @param int $key_version Key version to try (for rotation support)
     * @return string|false Decrypted data or false on failure
     */
    public static function decrypt($encrypted_data, $key_version = 0) {
        $key = self::get_key($key_version);
        
        if ($key === false) {
            return false;
        }
        
        // Minimum length check
        $min_length = self::IV_LENGTH + self::TAG_LENGTH + 1;
        if (strlen($encrypted_data) < $min_length) {
            error_log('EL Encryption: Data too short to be valid encrypted content');
            return false;
        }
        
        // Extract IV, tag, and ciphertext
        $iv = substr($encrypted_data, 0, self::IV_LENGTH);
        $tag = substr($encrypted_data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($encrypted_data, self::IV_LENGTH + self::TAG_LENGTH);
        
        // Decrypt
        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($decrypted === false) {
            // Try previous key version for rotation support
            if ($key_version === 0 && defined('EL_ENCRYPTION_KEY_V1')) {
                error_log('EL Encryption: Trying previous key version');
                return self::decrypt($encrypted_data, 1);
            }
            
            error_log('EL Encryption: Decryption failed - ' . openssl_error_string());
            return false;
        }
        
        return $decrypted;
    }
    
    /**
     * Get secure storage directory
     * 
     * @return string Full path to storage directory
     */
    private static function get_storage_dir() {
        $upload_dir = wp_upload_dir();
        $storage_dir = $upload_dir['basedir'] . '/' . self::STORAGE_DIR;
        
        if (!file_exists($storage_dir)) {
            wp_mkdir_p($storage_dir);
            
            // Protect directory from direct access
            self::protect_directory($storage_dir);
        }
        
        return $storage_dir;
    }
    
    /**
     * Protect directory from direct access
     * 
     * @param string $dir Directory path
     */
    private static function protect_directory($dir) {
        // .htaccess for Apache
        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $htaccess_content = "# Deny all direct access\n";
            $htaccess_content .= "Deny from all\n\n";
            $htaccess_content .= "# Block all file types\n";
            $htaccess_content .= "<FilesMatch \".*\">\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            file_put_contents($htaccess, $htaccess_content);
        }
        
        // index.php to prevent directory listing
        $index = $dir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, "<?php\n// Silence is golden\n");
        }
        
        // web.config for IIS
        $webconfig = $dir . '/web.config';
        if (!file_exists($webconfig)) {
            $webconfig_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $webconfig_content .= '<configuration><system.webServer><authorization>' . "\n";
            $webconfig_content .= '<deny users="*" />' . "\n";
            $webconfig_content .= '</authorization></system.webServer></configuration>' . "\n";
            file_put_contents($webconfig, $webconfig_content);
        }
    }
    
    /**
     * Store encrypted PDF
     * 
     * @param string $pdf_content Raw PDF content
     * @param string $reference Document reference
     * @param array $metadata Additional metadata to store
     * @return array|false Storage result or false on failure
     */
    public static function store_pdf($pdf_content, $reference, $metadata = []) {
        if (!self::is_configured()) {
            error_log('EL Encryption: Cannot store - encryption not configured');
            return false;
        }
        
        // Encrypt the PDF content
        $encrypted = self::encrypt($pdf_content);
        
        if ($encrypted === false) {
            return false;
        }
        
        // Generate storage path
        $storage_dir = self::get_storage_dir();
        $year_month = date('Y/m');
        $full_dir = $storage_dir . '/' . $year_month;
        
        if (!file_exists($full_dir)) {
            wp_mkdir_p($full_dir);
        }
        
        // Generate unique filename
        $file_id = self::generate_file_id($reference);
        $encrypted_path = $full_dir . '/' . $file_id . '.enc';
        
        // Write encrypted file
        $bytes_written = file_put_contents($encrypted_path, $encrypted);
        
        if ($bytes_written === false) {
            error_log('EL Encryption: Failed to write encrypted file');
            return false;
        }
        
        // Prepare metadata
        $storage_metadata = [
            'reference' => $reference,
            'file_id' => $file_id,
            'encrypted_path' => $encrypted_path,
            'relative_path' => $year_month . '/' . $file_id . '.enc',
            'original_size' => strlen($pdf_content),
            'encrypted_size' => $bytes_written,
            'created_at' => current_time('mysql'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::DEFAULT_EXPIRY_DAYS . ' days')),
            'checksum' => hash('sha256', $pdf_content),
            'encryption_version' => 1,
        ];
        
        // Merge with additional metadata
        $storage_metadata = array_merge($storage_metadata, $metadata);
        
        // Store metadata in database
        $db_result = self::store_metadata($storage_metadata);
        
        if (!$db_result) {
            // Cleanup file if metadata storage failed
            unlink($encrypted_path);
            return false;
        }
        
        return [
            'success' => true,
            'file_id' => $file_id,
            'reference' => $reference,
            'path' => $encrypted_path,
            'expires_at' => $storage_metadata['expires_at'],
        ];
    }
    
    /**
     * Retrieve and decrypt PDF
     * 
     * @param string $file_id File ID or reference
     * @param bool $by_reference If true, lookup by reference instead of file_id
     * @return string|false Decrypted PDF content or false on failure
     */
    public static function retrieve_pdf($file_id, $by_reference = false) {
        // Get metadata
        $metadata = self::get_metadata($file_id, $by_reference);
        
        if (!$metadata) {
            error_log('EL Encryption: Metadata not found for ' . $file_id);
            return false;
        }
        
        // Check expiry
        if (strtotime($metadata['expires_at']) < time()) {
            error_log('EL Encryption: Document has expired - ' . $file_id);
            return false;
        }
        
        // Read encrypted file
        $encrypted_path = $metadata['encrypted_path'];
        
        if (!file_exists($encrypted_path)) {
            error_log('EL Encryption: Encrypted file not found - ' . $encrypted_path);
            return false;
        }
        
        $encrypted_content = file_get_contents($encrypted_path);
        
        if ($encrypted_content === false) {
            error_log('EL Encryption: Failed to read encrypted file');
            return false;
        }
        
        // Decrypt
        $decrypted = self::decrypt($encrypted_content);
        
        if ($decrypted === false) {
            return false;
        }
        
        // Verify checksum if available
        if (!empty($metadata['checksum'])) {
            $current_checksum = hash('sha256', $decrypted);
            if ($current_checksum !== $metadata['checksum']) {
                error_log('EL Encryption: Checksum mismatch - document may be corrupted');
                return false;
            }
        }
        
        // Update access log
        self::log_access($file_id, 'retrieve');
        
        return $decrypted;
    }
    
    /**
     * Generate unique file ID
     * 
     * @param string $reference Document reference
     * @return string Unique file ID
     */
    private static function generate_file_id($reference) {
        $random = bin2hex(random_bytes(8));
        $hash = substr(hash('sha256', $reference . $random), 0, 12);
        return 'el_' . $hash . '_' . $random;
    }
    
    /**
     * Store metadata in database
     * 
     * @param array $metadata Metadata to store
     * @return bool True on success
     */
    private static function store_metadata($metadata) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        
        // Ensure table exists
        self::maybe_create_table();
        
        $result = $wpdb->insert(
            $table_name,
            [
                'file_id' => $metadata['file_id'],
                'reference' => $metadata['reference'],
                'encrypted_path' => $metadata['encrypted_path'],
                'relative_path' => $metadata['relative_path'],
                'original_size' => $metadata['original_size'],
                'encrypted_size' => $metadata['encrypted_size'],
                'checksum' => $metadata['checksum'],
                'encryption_version' => $metadata['encryption_version'],
                'created_at' => $metadata['created_at'],
                'expires_at' => $metadata['expires_at'],
                'metadata_json' => wp_json_encode($metadata),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Get metadata from database
     * 
     * @param string $identifier File ID or reference
     * @param bool $by_reference If true, lookup by reference
     * @return array|null Metadata or null if not found
     */
    private static function get_metadata($identifier, $by_reference = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        $column = $by_reference ? 'reference' : 'file_id';
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE $column = %s",
            $identifier
        ), ARRAY_A);
        
        if ($row && !empty($row['metadata_json'])) {
            $row['metadata'] = json_decode($row['metadata_json'], true);
        }
        
        return $row;
    }
    
    /**
     * Log access to encrypted document
     * 
     * @param string $file_id File ID
     * @param string $action Action performed
     */
    private static function log_access($file_id, $action) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_access_log';
        
        $wpdb->insert(
            $table_name,
            [
                'file_id' => $file_id,
                'action' => $action,
                'user_id' => get_current_user_id(),
                'ip_address' => self::get_client_ip(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'accessed_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s']
        );
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private static function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Delete encrypted document
     * 
     * @param string $file_id File ID
     * @return bool True on success
     */
    public static function delete_document($file_id) {
        global $wpdb;
        
        // Get metadata first
        $metadata = self::get_metadata($file_id);
        
        if (!$metadata) {
            return false;
        }
        
        // Delete encrypted file
        if (file_exists($metadata['encrypted_path'])) {
            unlink($metadata['encrypted_path']);
        }
        
        // Delete from database
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        $wpdb->delete($table_name, ['file_id' => $file_id], ['%s']);
        
        // Log deletion
        self::log_access($file_id, 'delete');
        
        return true;
    }
    
    /**
     * Extend document expiry
     * 
     * @param string $file_id File ID
     * @param int $days Days to extend
     * @return bool True on success
     */
    public static function extend_expiry($file_id, $days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        $new_expiry = date('Y-m-d H:i:s', strtotime("+$days days"));
        
        $result = $wpdb->update(
            $table_name,
            ['expires_at' => $new_expiry],
            ['file_id' => $file_id],
            ['%s'],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Clean up expired documents
     * 
     * @return int Number of documents deleted
     */
    public static function cleanup_expired() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        
        // Get expired documents
        $expired = $wpdb->get_results($wpdb->prepare(
            "SELECT file_id, encrypted_path FROM $table_name WHERE expires_at < %s",
            current_time('mysql')
        ), ARRAY_A);
        
        $deleted = 0;
        
        foreach ($expired as $doc) {
            if (self::delete_document($doc['file_id'])) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Create database tables if needed
     */
    public static function maybe_create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        $access_table = $wpdb->prefix . 'el_access_log';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Check if tables exist
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Encrypted documents table
        $sql1 = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            file_id varchar(50) NOT NULL,
            reference varchar(50) NOT NULL,
            encrypted_path varchar(500) NOT NULL,
            relative_path varchar(200) NOT NULL,
            original_size bigint(20) unsigned NOT NULL DEFAULT 0,
            encrypted_size bigint(20) unsigned NOT NULL DEFAULT 0,
            checksum varchar(64) NOT NULL,
            encryption_version tinyint(3) unsigned NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            metadata_json longtext,
            PRIMARY KEY (id),
            UNIQUE KEY file_id (file_id),
            KEY reference (reference),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        dbDelta($sql1);
        
        // Access log table
        $sql2 = "CREATE TABLE $access_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            file_id varchar(50) NOT NULL,
            action varchar(20) NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            ip_address varchar(45) NOT NULL,
            user_agent varchar(255) DEFAULT NULL,
            accessed_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY file_id (file_id),
            KEY user_id (user_id),
            KEY accessed_at (accessed_at)
        ) $charset_collate;";
        
        dbDelta($sql2);
    }
    
    /**
     * Re-encrypt document with new key (for key rotation)
     * 
     * @param string $file_id File ID
     * @return bool True on success
     */
    public static function rotate_encryption($file_id) {
        // Retrieve with old key (decrypt will try both keys)
        $decrypted = self::retrieve_pdf($file_id);
        
        if ($decrypted === false) {
            return false;
        }
        
        // Get metadata
        $metadata = self::get_metadata($file_id);
        
        if (!$metadata) {
            return false;
        }
        
        // Re-encrypt with current key
        $encrypted = self::encrypt($decrypted);
        
        if ($encrypted === false) {
            return false;
        }
        
        // Write new encrypted file
        $bytes_written = file_put_contents($metadata['encrypted_path'], $encrypted);
        
        if ($bytes_written === false) {
            return false;
        }
        
        // Update metadata
        global $wpdb;
        $table_name = $wpdb->prefix . 'el_encrypted_docs';
        
        $wpdb->update(
            $table_name,
            [
                'encrypted_size' => $bytes_written,
                'encryption_version' => 1,
            ],
            ['file_id' => $file_id],
            ['%d', '%d'],
            ['%s']
        );
        
        return true;
    }
}