<?php
/**
 * SLM Document Storage
 * 
 * Handles secure document storage with:
 * - AES-256-GCM encryption
 * - Per-file encryption keys (derived)
 * - Obfuscated storage paths
 * - Client folder management
 * - Access logging
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Document_Storage {
    
    /**
     * Encryption cipher
     */
    const CIPHER = 'aes-256-gcm';
    
    /**
     * Tag length for GCM
     */
    const TAG_LENGTH = 16;
    
    /**
     * Default folder types for new clients
     */
    const DEFAULT_FOLDERS = [
        'terms' => 'Terms & Agreements',
        'engagement-letters' => 'Engagement Letters',
        'identity' => 'Identity Documents',
        'correspondence' => 'Correspondence',
        'case-documents' => 'Case Documents',
    ];
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Register REST endpoints for document access
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
    }
    
    /**
     * Register REST routes
     */
    public static function register_rest_routes() {
        register_rest_route('slm/v1', '/document/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'rest_get_document'],
            'permission_callback' => [__CLASS__, 'rest_permission_check'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'token' => [
                    'required' => true,
                    'type' => 'string',
                ],
            ],
        ]);
    }
    
    /**
     * REST permission check
     */
    public static function rest_permission_check($request) {
        $token = $request->get_param('token');
        
        if (empty($token)) {
            return false;
        }
        
        // Token validation would happen in the callback
        return true;
    }
    
    /**
     * Get master encryption key
     */
    private static function get_master_key() {
        // Check for defined constant
        if (defined('SLM_ENCRYPTION_KEY')) {
            $key = SLM_ENCRYPTION_KEY;
            
            // Handle base64 encoded keys
            if (strpos($key, 'base64:') === 0) {
                return base64_decode(substr($key, 7));
            }
            
            return $key;
        }
        
        // Fallback to option (less secure, but functional)
        $key = get_option('slm_encryption_key');
        
        if (empty($key)) {
            // Generate and store a new key
            $key = random_bytes(32);
            update_option('slm_encryption_key', base64_encode($key));
            return $key;
        }
        
        return base64_decode($key);
    }
    
    /**
     * Derive per-file encryption key
     */
    private static function derive_file_key($file_uuid) {
        $master_key = self::get_master_key();
        return hash_hmac('sha256', $file_uuid, $master_key, true);
    }
    
    /**
     * Encrypt content
     */
    private static function encrypt($content, $file_uuid) {
        $key = self::derive_file_key($file_uuid);
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $tag = '';
        
        $encrypted = openssl_encrypt(
            $content,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );
        
        if ($encrypted === false) {
            return false;
        }
        
        return [
            'content' => $encrypted,
            'iv' => bin2hex($iv),
            'tag' => bin2hex($tag),
        ];
    }
    
    /**
     * Decrypt content
     */
    private static function decrypt($encrypted_content, $iv_hex, $tag_hex, $file_uuid) {
        $key = self::derive_file_key($file_uuid);
        $iv = hex2bin($iv_hex);
        $tag = hex2bin($tag_hex);
        
        $decrypted = openssl_decrypt(
            $encrypted_content,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return $decrypted;
    }
    
    /**
     * Generate obfuscated storage path
     */
    private static function generate_storage_path($user_id, $document_type) {
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/private/slm-documents';
        
        // Create hash-based subdirectories for better file distribution
        $user_hash = substr(hash('sha256', $user_id . wp_salt('auth')), 0, 8);
        $type_hash = substr(hash('sha256', $document_type . wp_salt('auth')), 0, 8);
        
        $path = $base_path . '/' . substr($user_hash, 0, 2) . '/' . substr($user_hash, 2, 2) . '/' . $user_hash . '/' . $type_hash;
        
        if (!file_exists($path)) {
            wp_mkdir_p($path);
            
            // Add .htaccess protection
            self::protect_directory($path);
        }
        
        return $path;
    }
    
    /**
     * Protect directory with .htaccess
     */
    private static function protect_directory($path) {
        $htaccess = $path . '/.htaccess';
        
        if (!file_exists($htaccess)) {
            $content = "# Deny direct access\n";
            $content .= "Order Deny,Allow\n";
            $content .= "Deny from all\n";
            
            file_put_contents($htaccess, $content);
        }
        
        // Also add index.php
        $index = $path . '/index.php';
        
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
        }
    }
    
    /**
     * Store a document
     * 
     * @param array $args Document arguments
     * @return int|WP_Error Document ID or error
     */
    public static function store_document($args) {
        global $wpdb;
        
        $defaults = [
            'user_id' => 0,
            'case_id' => null,
            'folder_id' => null,
            'document_type' => 'general',
            'file_name' => '',
            'content' => '',
            'mime_type' => 'application/octet-stream',
            'is_signed' => false,
            'signed_by' => null,
            'signed_at' => null,
            'signing_ip' => null,
            'signing_user_agent' => null,
            'signing_method' => null,
            'file_hash' => null,
            'metadata' => [],
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required fields
        if (empty($args['user_id']) || empty($args['content'])) {
            return new WP_Error('missing_data', __('Missing required document data.', 'flavor'));
        }
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return new WP_Error('db_error', __('Documents table not found.', 'flavor'));
        }
        
        // Generate file UUID
        $file_uuid = wp_generate_uuid4();
        
        // Generate storage path
        $storage_path = self::generate_storage_path($args['user_id'], $args['document_type']);
        $file_path = $storage_path . '/' . $file_uuid . '.enc';
        
        // Calculate hash if not provided
        if (empty($args['file_hash'])) {
            $args['file_hash'] = hash('sha256', $args['content']);
        }
        
        // Encrypt content
        $encrypted = self::encrypt($args['content'], $file_uuid);
        
        if ($encrypted === false) {
            return new WP_Error('encryption_failed', __('Failed to encrypt document.', 'flavor'));
        }
        
        // Write encrypted content
        $written = file_put_contents($file_path, $encrypted['content']);
        
        if ($written === false) {
            return new WP_Error('storage_failed', __('Failed to store document.', 'flavor'));
        }
        
        // Store metadata in database
        $inserted = $wpdb->insert(
            $table,
            [
                'user_id' => $args['user_id'],
                'case_id' => $args['case_id'],
                'folder_id' => $args['folder_id'],
                'document_type' => $args['document_type'],
                'file_name' => $file_uuid . '.enc',
                'original_name' => $args['file_name'],
                'file_path' => $file_path,
                'file_size' => strlen($args['content']),
                'mime_type' => $args['mime_type'],
                'encryption_iv' => $encrypted['iv'],
                'encryption_tag' => $encrypted['tag'],
                'file_hash' => $args['file_hash'],
                'is_signed' => $args['is_signed'] ? 1 : 0,
                'signed_by' => $args['signed_by'],
                'signed_at' => $args['signed_at'],
                'signing_ip' => $args['signing_ip'],
                'signing_user_agent' => $args['signing_user_agent'],
                'signing_method' => $args['signing_method'],
                'created_by' => get_current_user_id() ?: $args['user_id'],
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        if (!$inserted) {
            // Clean up file
            @unlink($file_path);
            return new WP_Error('db_error', __('Failed to record document.', 'flavor'));
        }
        
        $document_id = $wpdb->insert_id;
        
        // Store additional metadata
        if (!empty($args['metadata'])) {
            update_post_meta($document_id, '_slm_document_metadata', $args['metadata']);
        }
        
        SLM_Client_Onboarding::log('Document stored: ' . $document_id . ' for user ' . $args['user_id']);
        
        return $document_id;
    }
    
    /**
     * Retrieve a document
     * 
     * @param int $document_id Document ID
     * @param int $user_id User requesting access
     * @return array|WP_Error Document data or error
     */
    public static function retrieve_document($document_id, $user_id = null) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return new WP_Error('db_error', __('Documents table not found.', 'flavor'));
        }
        
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND deleted_at IS NULL",
            $document_id
        ));
        
        if (!$document) {
            return new WP_Error('not_found', __('Document not found.', 'flavor'));
        }
        
        // Check access permission
        if ($user_id && !self::can_access_document($document, $user_id)) {
            return new WP_Error('access_denied', __('Access denied.', 'flavor'));
        }
        
        // Read encrypted content
        if (!file_exists($document->file_path)) {
            return new WP_Error('file_missing', __('Document file not found.', 'flavor'));
        }
        
        $encrypted_content = file_get_contents($document->file_path);
        
        if ($encrypted_content === false) {
            return new WP_Error('read_failed', __('Failed to read document.', 'flavor'));
        }
        
        // Get file UUID from filename
        $file_uuid = str_replace('.enc', '', $document->file_name);
        
        // Decrypt
        $content = self::decrypt(
            $encrypted_content,
            $document->encryption_iv,
            $document->encryption_tag,
            $file_uuid
        );
        
        if ($content === false) {
            return new WP_Error('decryption_failed', __('Failed to decrypt document.', 'flavor'));
        }
        
        // Log access
        self::log_access($document_id, $user_id, 'view');
        
        return [
            'id' => $document->id,
            'original_name' => $document->original_name,
            'content' => $content,
            'mime_type' => $document->mime_type,
            'file_size' => $document->file_size,
            'file_hash' => $document->file_hash,
            'is_signed' => (bool) $document->is_signed,
            'created_at' => $document->created_at,
        ];
    }
    
    /**
     * Check if user can access document
     */
    private static function can_access_document($document, $user_id) {
        // Document owner can always access
        if ($document->user_id == $user_id) {
            return true;
        }
        
        // Admins can access
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Lawyers with edit_users capability can access
        if (user_can($user_id, 'edit_users')) {
            return true;
        }
        
        // TODO: Add case-based permission check when cases are implemented
        
        return false;
    }
    
    /**
     * Log document access
     */
    private static function log_access($document_id, $user_id, $access_type, $extra = []) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('access_log');
        
        if (!$table) {
            return;
        }
        
        $wpdb->insert(
            $table,
            [
                'document_id' => $document_id,
                'user_id' => $user_id,
                'access_type' => $access_type,
                'ip_address' => self::get_client_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                'access_token' => $extra['token'] ?? null,
                'pages_viewed' => $extra['pages_viewed'] ?? null,
                'view_duration' => $extra['view_duration'] ?? null,
                'accessed_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
    }
    
    /**
     * Create client folder structure
     * 
     * @param int $user_id User ID
     * @return int|WP_Error Root folder ID or error
     */
    public static function create_client_folder($user_id) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('folders');
        
        if (!$table) {
            return new WP_Error('db_error', __('Folders table not found.', 'flavor'));
        }
        
        // Check if folder already exists
        $existing = get_user_meta($user_id, 'slm_client_folder_id', true);
        
        if ($existing) {
            return $existing;
        }
        
        // Get user info for folder name
        $user = get_userdata($user_id);
        $client_name = trim(get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true)) ?: $user->display_name;
        
        // Create root folder
        $wpdb->insert(
            $table,
            [
                'user_id' => $user_id,
                'parent_id' => null,
                'folder_name' => $client_name,
                'folder_slug' => sanitize_title($client_name . '-' . $user_id),
                'folder_type' => 'root',
                'sort_order' => 0,
                'created_by' => get_current_user_id() ?: $user_id,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s']
        );
        
        $root_folder_id = $wpdb->insert_id;
        
        if (!$root_folder_id) {
            return new WP_Error('db_error', __('Failed to create root folder.', 'flavor'));
        }
        
        // Create default subfolders
        $sort_order = 0;
        
        foreach (self::DEFAULT_FOLDERS as $slug => $name) {
            $wpdb->insert(
                $table,
                [
                    'user_id' => $user_id,
                    'parent_id' => $root_folder_id,
                    'folder_name' => $name,
                    'folder_slug' => $slug,
                    'folder_type' => 'default',
                    'sort_order' => $sort_order++,
                    'created_by' => get_current_user_id() ?: $user_id,
                    'created_at' => current_time('mysql'),
                ],
                ['%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s']
            );
        }
        
        // Store root folder ID in user meta
        update_user_meta($user_id, 'slm_client_folder_id', $root_folder_id);
        
        SLM_Client_Onboarding::log('Created folder structure for user ' . $user_id);
        
        return $root_folder_id;
    }
    
    /**
     * Get folder contents
     * 
     * @param int $folder_id Folder ID (null for root)
     * @param int $user_id User ID
     * @return array Folders and documents
     */
    public static function get_folder_contents($folder_id, $user_id) {
        global $wpdb;
        
        $folders_table = SLM_Client_Onboarding::get_table('folders');
        $docs_table = SLM_Client_Onboarding::get_table('documents');
        
        $result = [
            'folders' => [],
            'documents' => [],
        ];
        
        // Get subfolders
        if ($folders_table) {
            if ($folder_id) {
                $folders = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $folders_table WHERE parent_id = %d AND user_id = %d ORDER BY sort_order, folder_name",
                    $folder_id,
                    $user_id
                ));
            } else {
                // Get root folder
                $folders = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $folders_table WHERE user_id = %d AND parent_id IS NULL ORDER BY sort_order, folder_name",
                    $user_id
                ));
            }
            
            foreach ($folders as $folder) {
                $result['folders'][] = [
                    'id' => $folder->id,
                    'name' => $folder->folder_name,
                    'slug' => $folder->folder_slug,
                    'type' => $folder->folder_type,
                    'created_at' => $folder->created_at,
                ];
            }
        }
        
        // Get documents in folder
        if ($docs_table) {
            $where_clause = $folder_id 
                ? $wpdb->prepare("folder_id = %d", $folder_id)
                : "folder_id IS NULL";
            
            $documents = $wpdb->get_results($wpdb->prepare(
                "SELECT id, original_name, document_type, file_size, mime_type, is_signed, created_at 
                 FROM $docs_table 
                 WHERE user_id = %d AND $where_clause AND deleted_at IS NULL 
                 ORDER BY created_at DESC",
                $user_id
            ));
            
            foreach ($documents as $doc) {
                $result['documents'][] = [
                    'id' => $doc->id,
                    'name' => $doc->original_name,
                    'type' => $doc->document_type,
                    'size' => $doc->file_size,
                    'size_formatted' => size_format($doc->file_size),
                    'mime_type' => $doc->mime_type,
                    'is_signed' => (bool) $doc->is_signed,
                    'created_at' => $doc->created_at,
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Move document to folder
     */
    public static function move_to_folder($document_id, $folder_id, $user_id) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return new WP_Error('db_error', __('Documents table not found.', 'flavor'));
        }
        
        // Verify document belongs to user or user has permission
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $document_id
        ));
        
        if (!$document || !self::can_access_document($document, $user_id)) {
            return new WP_Error('access_denied', __('Access denied.', 'flavor'));
        }
        
        // Verify folder belongs to same user
        if ($folder_id) {
            $folders_table = SLM_Client_Onboarding::get_table('folders');
            $folder = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $folders_table WHERE id = %d AND user_id = %d",
                $folder_id,
                $document->user_id
            ));
            
            if (!$folder) {
                return new WP_Error('invalid_folder', __('Invalid folder.', 'flavor'));
            }
        }
        
        // Update document
        $updated = $wpdb->update(
            $table,
            [
                'folder_id' => $folder_id,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $document_id],
            ['%d', '%s'],
            ['%d']
        );
        
        if ($updated === false) {
            return new WP_Error('update_failed', __('Failed to move document.', 'flavor'));
        }
        
        return true;
    }
    
    /**
     * Delete document (soft delete)
     */
    public static function delete_document($document_id, $user_id) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('documents');
        
        if (!$table) {
            return new WP_Error('db_error', __('Documents table not found.', 'flavor'));
        }
        
        // Verify access
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $document_id
        ));
        
        if (!$document) {
            return new WP_Error('not_found', __('Document not found.', 'flavor'));
        }
        
        // Only admins/lawyers can delete
        if (!user_can($user_id, 'edit_users')) {
            return new WP_Error('access_denied', __('Only lawyers can delete documents.', 'flavor'));
        }
        
        // Soft delete
        $updated = $wpdb->update(
            $table,
            [
                'deleted_at' => current_time('mysql'),
            ],
            ['id' => $document_id],
            ['%s'],
            ['%d']
        );
        
        if ($updated === false) {
            return new WP_Error('delete_failed', __('Failed to delete document.', 'flavor'));
        }
        
        SLM_Client_Onboarding::log('Document ' . $document_id . ' soft deleted by user ' . $user_id);
        
        return true;
    }
    
    /**
     * Generate secure download URL
     */
    public static function get_download_url($document_id, $user_id) {
        // Generate time-limited token
        $token_data = [
            'document_id' => $document_id,
            'user_id' => $user_id,
            'expires' => time() + (5 * MINUTE_IN_SECONDS),
            'action' => 'download',
            'nonce' => wp_generate_password(8, false),
        ];
        
        $token = base64_encode(json_encode($token_data));
        $signature = hash_hmac('sha256', $token, wp_salt('auth'));
        
        return rest_url('slm/v1/document/' . $document_id) . '?token=' . urlencode($token . '.' . $signature);
    }
    
    /**
     * REST: Get document
     */
    public static function rest_get_document($request) {
        $document_id = $request->get_param('id');
        $token_string = $request->get_param('token');
        
        // Validate token
        $parts = explode('.', $token_string);
        
        if (count($parts) !== 2) {
            return new WP_Error('invalid_token', 'Invalid access token', ['status' => 403]);
        }
        
        list($token, $signature) = $parts;
        
        $expected_signature = hash_hmac('sha256', $token, wp_salt('auth'));
        
        if (!hash_equals($expected_signature, $signature)) {
            return new WP_Error('invalid_token', 'Invalid access token', ['status' => 403]);
        }
        
        $token_data = json_decode(base64_decode($token), true);
        
        if (!$token_data || $token_data['expires'] < time()) {
            return new WP_Error('token_expired', 'Access token expired', ['status' => 403]);
        }
        
        if ($token_data['document_id'] != $document_id) {
            return new WP_Error('token_mismatch', 'Token does not match document', ['status' => 403]);
        }
        
        // Retrieve document
        $document = self::retrieve_document($document_id, $token_data['user_id']);
        
        if (is_wp_error($document)) {
            return $document;
        }
        
        // Log download
        self::log_access($document_id, $token_data['user_id'], 'download');
        
        // Return file
        return new WP_REST_Response([
            'filename' => $document['original_name'],
            'content' => base64_encode($document['content']),
            'mime_type' => $document['mime_type'],
        ]);
    }
    
    /**
     * Get client IP
     */
    private static function get_client_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'REMOTE_ADDR',
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
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
}
