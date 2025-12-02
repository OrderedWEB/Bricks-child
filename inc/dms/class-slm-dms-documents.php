<?php
/**
 * SLM DMS Documents
 * 
 * Document management:
 * - Upload with encryption
 * - Version history
 * - Metadata management
 * - Access control
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Documents {
    
    /**
     * Allowed mime types
     */
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
        'text/csv',
    ];
    
    /**
     * Max file size (50MB)
     */
    const MAX_FILE_SIZE = 52428800;
    
    /**
     * Initialize
     */
    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_slm_upload_document', [__CLASS__, 'ajax_upload_document']);
        add_action('wp_ajax_slm_delete_document', [__CLASS__, 'ajax_delete_document']);
        add_action('wp_ajax_slm_get_document_versions', [__CLASS__, 'ajax_get_versions']);
        add_action('wp_ajax_slm_download_document', [__CLASS__, 'ajax_download_document']);
        
        // Meta boxes
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_slm_document', [__CLASS__, 'save_meta_boxes'], 10, 2);
    }
    
    /**
     * Upload a new document
     * 
     * @param array $file $_FILES array element
     * @param array $args Document arguments
     * @return int|WP_Error Document post ID or error
     */
    public static function upload_document($file, $args = []) {
        $defaults = [
            'title' => '',
            'description' => '',
            'case_id' => 0,
            'folder_id' => 0,
            'category' => '',
            'tags' => [],
            'expiry_date' => '',
            'download_allowed' => true,
            'uploaded_by' => get_current_user_id(),
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate file
        $validation = self::validate_file($file);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Generate file UUID
        $file_uuid = wp_generate_uuid4();
        
        // Determine storage path
        $storage_path = self::get_storage_path($args['case_id'], $args['folder_id']);
        $encrypted_filename = $file_uuid . '.enc';
        $encrypted_path = $storage_path . '/' . $encrypted_filename;
        
        // Encrypt and store file
        $encryption_result = SLM_DMS_Encryption::encrypt_file(
            $file['tmp_name'],
            $file_uuid,
            $encrypted_path
        );
        
        if (!$encryption_result) {
            return new WP_Error('encryption_failed', __('Failed to encrypt document.', 'flavor'));
        }
        
        // Create document post
        $title = !empty($args['title']) ? $args['title'] : pathinfo($file['name'], PATHINFO_FILENAME);
        
        $post_id = wp_insert_post([
            'post_type' => 'slm_document',
            'post_title' => sanitize_text_field($title),
            'post_content' => sanitize_textarea_field($args['description']),
            'post_status' => 'publish',
            'post_author' => $args['uploaded_by'],
        ]);
        
        if (is_wp_error($post_id)) {
            // Clean up encrypted file
            @unlink($encrypted_path);
            return $post_id;
        }
        
        // Store document metadata
        update_post_meta($post_id, '_slm_file_uuid', $file_uuid);
        update_post_meta($post_id, '_slm_original_filename', sanitize_file_name($file['name']));
        update_post_meta($post_id, '_slm_mime_type', $file['type']);
        update_post_meta($post_id, '_slm_case_id', intval($args['case_id']));
        update_post_meta($post_id, '_slm_folder_id', intval($args['folder_id']));
        update_post_meta($post_id, '_slm_download_allowed', $args['download_allowed'] ? '1' : '0');
        update_post_meta($post_id, '_slm_current_version', 1);
        
        if (!empty($args['expiry_date'])) {
            update_post_meta($post_id, '_slm_expiry_date', sanitize_text_field($args['expiry_date']));
        }
        
        // Create version record
        self::create_version_record($post_id, [
            'version_number' => 1,
            'file_uuid' => $file_uuid,
            'file_path' => $encrypted_path,
            'file_size' => $encryption_result['file_size'],
            'file_hash' => $encryption_result['file_hash'],
            'mime_type' => $file['type'],
            'encryption_iv' => $encryption_result['encryption_iv'],
            'encryption_tag' => $encryption_result['encryption_tag'],
            'uploaded_by' => $args['uploaded_by'],
            'is_current' => true,
        ]);
        
        // Set category
        if (!empty($args['category'])) {
            wp_set_object_terms($post_id, $args['category'], 'slm_doc_category');
        }
        
        // Set tags
        if (!empty($args['tags'])) {
            wp_set_object_terms($post_id, $args['tags'], 'slm_doc_tag');
        }
        
        // Generate storage hash for case if needed
        if ($args['case_id'] && !get_post_meta($args['case_id'], '_slm_storage_hash', true)) {
            update_post_meta($args['case_id'], '_slm_storage_hash', wp_generate_password(16, false));
        }
        
        SLM_DMS::log('Document uploaded: ' . $post_id . ' (' . $title . ')');
        
        do_action('slm_document_uploaded', $post_id, $args);
        
        return $post_id;
    }
    
    /**
     * Upload new version of existing document
     */
    public static function upload_new_version($document_id, $file, $note = '') {
        $document = get_post($document_id);
        
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        // Validate file
        $validation = self::validate_file($file);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Get current version
        $current_version = intval(get_post_meta($document_id, '_slm_current_version', true));
        $new_version = $current_version + 1;
        
        // Generate new file UUID
        $file_uuid = wp_generate_uuid4();
        
        // Get storage path
        $case_id = get_post_meta($document_id, '_slm_case_id', true);
        $folder_id = get_post_meta($document_id, '_slm_folder_id', true);
        $storage_path = self::get_storage_path($case_id, $folder_id);
        $encrypted_path = $storage_path . '/' . $file_uuid . '.enc';
        
        // Encrypt and store
        $encryption_result = SLM_DMS_Encryption::encrypt_file(
            $file['tmp_name'],
            $file_uuid,
            $encrypted_path
        );
        
        if (!$encryption_result) {
            return new WP_Error('encryption_failed', __('Failed to encrypt document.', 'flavor'));
        }
        
        // Mark old version as not current
        global $wpdb;
        $versions_table = SLM_DMS::get_table('document_versions');
        $wpdb->update(
            $versions_table,
            ['is_current' => 0],
            ['document_id' => $document_id]
        );
        
        // Create new version record
        self::create_version_record($document_id, [
            'version_number' => $new_version,
            'file_uuid' => $file_uuid,
            'file_path' => $encrypted_path,
            'file_size' => $encryption_result['file_size'],
            'file_hash' => $encryption_result['file_hash'],
            'mime_type' => $file['type'],
            'encryption_iv' => $encryption_result['encryption_iv'],
            'encryption_tag' => $encryption_result['encryption_tag'],
            'uploaded_by' => get_current_user_id(),
            'upload_note' => $note,
            'is_current' => true,
        ]);
        
        // Update document metadata
        update_post_meta($document_id, '_slm_file_uuid', $file_uuid);
        update_post_meta($document_id, '_slm_current_version', $new_version);
        update_post_meta($document_id, '_slm_mime_type', $file['type']);
        
        SLM_DMS::log('New version uploaded for document: ' . $document_id . ' (v' . $new_version . ')');
        
        do_action('slm_document_version_uploaded', $document_id, $new_version);
        
        return $new_version;
    }
    
    /**
     * Create version record in database
     */
    private static function create_version_record($document_id, $data) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('document_versions');
        
        $wpdb->insert($table, [
            'document_id' => $document_id,
            'version_number' => $data['version_number'],
            'file_uuid' => $data['file_uuid'],
            'file_path' => $data['file_path'],
            'file_size' => $data['file_size'],
            'file_hash' => $data['file_hash'],
            'mime_type' => $data['mime_type'],
            'encryption_iv' => $data['encryption_iv'],
            'encryption_tag' => $data['encryption_tag'],
            'uploaded_by' => $data['uploaded_by'],
            'upload_note' => $data['upload_note'] ?? '',
            'is_current' => $data['is_current'] ? 1 : 0,
            'created_at' => current_time('mysql'),
        ]);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Validate uploaded file
     */
    private static function validate_file($file) {
        if (empty($file) || !isset($file['tmp_name'])) {
            return new WP_Error('no_file', __('No file uploaded.', 'flavor'));
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('Upload error occurred.', 'flavor'));
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return new WP_Error('file_too_large', __('File exceeds maximum size of 50MB.', 'flavor'));
        }
        
        // Check mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, self::ALLOWED_MIME_TYPES)) {
            return new WP_Error('invalid_type', __('File type not allowed.', 'flavor'));
        }
        
        return true;
    }
    
    /**
     * Get storage path for document
     */
    private static function get_storage_path($case_id = 0, $folder_id = 0) {
        $base_path = SLM_DMS::get_storage_path();
        
        if ($case_id) {
            $case_hash = get_post_meta($case_id, '_slm_storage_hash', true);
            if (!$case_hash) {
                $case_hash = wp_generate_password(16, false);
                update_post_meta($case_id, '_slm_storage_hash', $case_hash);
            }
            $base_path .= '/cases/' . $case_hash;
        } else {
            $base_path .= '/general';
        }
        
        if ($folder_id) {
            $folder_hash = get_post_meta($folder_id, '_slm_storage_hash', true);
            if (!$folder_hash) {
                $folder_hash = wp_generate_password(16, false);
                update_post_meta($folder_id, '_slm_storage_hash', $folder_hash);
            }
            $base_path .= '/' . $folder_hash;
        }
        
        // Ensure directory exists
        if (!file_exists($base_path)) {
            wp_mkdir_p($base_path);
            
            // Add .htaccess
            $htaccess = $base_path . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
            }
        }
        
        return $base_path;
    }
    
    /**
     * Get document content (decrypted)
     */
    public static function get_document_content($document_id, $version = null) {
        $document = get_post($document_id);
        
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        // Get version data
        $version_data = self::get_version_data($document_id, $version);
        
        if (!$version_data) {
            return new WP_Error('version_not_found', __('Document version not found.', 'flavor'));
        }
        
        // Decrypt content
        $content = SLM_DMS_Encryption::decrypt_file(
            $version_data->file_path,
            $version_data->file_uuid
        );
        
        if ($content === false) {
            return new WP_Error('decryption_failed', __('Failed to decrypt document.', 'flavor'));
        }
        
        // Verify integrity
        if (!SLM_DMS_Encryption::verify_file_hash($content, $version_data->file_hash)) {
            SLM_DMS::log('Document integrity check failed: ' . $document_id, 'error');
            return new WP_Error('integrity_failed', __('Document integrity verification failed.', 'flavor'));
        }
        
        return [
            'content' => $content,
            'mime_type' => $version_data->mime_type,
            'filename' => get_post_meta($document_id, '_slm_original_filename', true),
            'version' => $version_data->version_number,
        ];
    }
    
    /**
     * Get version data
     */
    public static function get_version_data($document_id, $version = null) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('document_versions');
        
        if ($version === null) {
            // Get current version
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE document_id = %d AND is_current = 1 LIMIT 1",
                $document_id
            ));
        }
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE document_id = %d AND version_number = %d LIMIT 1",
            $document_id,
            $version
        ));
    }
    
    /**
     * Get all versions for a document
     */
    public static function get_versions($document_id) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('document_versions');
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT v.*, u.display_name as uploaded_by_name 
             FROM $table v 
             LEFT JOIN {$wpdb->users} u ON v.uploaded_by = u.ID
             WHERE v.document_id = %d 
             ORDER BY v.version_number DESC",
            $document_id
        ));
    }
    
    /**
     * Get document details
     */
    public static function get_document($document_id) {
        $document = get_post($document_id);
        
        if (!$document || $document->post_type !== 'slm_document') {
            return null;
        }
        
        $version_data = self::get_version_data($document_id);
        
        return [
            'id' => $document->ID,
            'title' => $document->post_title,
            'description' => $document->post_content,
            'filename' => get_post_meta($document_id, '_slm_original_filename', true),
            'mime_type' => get_post_meta($document_id, '_slm_mime_type', true),
            'case_id' => get_post_meta($document_id, '_slm_case_id', true),
            'folder_id' => get_post_meta($document_id, '_slm_folder_id', true),
            'current_version' => get_post_meta($document_id, '_slm_current_version', true),
            'download_allowed' => get_post_meta($document_id, '_slm_download_allowed', true) === '1',
            'expiry_date' => get_post_meta($document_id, '_slm_expiry_date', true),
            'file_size' => $version_data ? $version_data->file_size : 0,
            'created_at' => $document->post_date,
            'author' => get_the_author_meta('display_name', $document->post_author),
            'categories' => wp_get_object_terms($document_id, 'slm_doc_category', ['fields' => 'names']),
            'tags' => wp_get_object_terms($document_id, 'slm_doc_tag', ['fields' => 'names']),
        ];
    }
    
    /**
     * Get documents list
     */
    public static function get_documents_list($args = []) {
        $defaults = [
            'case_id' => 0,
            'folder_id' => 0,
            'user_id' => 0,
            'category' => '',
            'search' => '',
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = [
            'post_type' => 'slm_document',
            'posts_per_page' => $args['per_page'],
            'paged' => $args['page'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        ];
        
        // Meta query
        $meta_query = [];
        
        if ($args['case_id']) {
            $meta_query[] = [
                'key' => '_slm_case_id',
                'value' => $args['case_id'],
            ];
        }
        
        if ($args['folder_id']) {
            $meta_query[] = [
                'key' => '_slm_folder_id',
                'value' => $args['folder_id'],
            ];
        }
        
        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }
        
        // Category
        if ($args['category']) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'slm_doc_category',
                    'field' => 'slug',
                    'terms' => $args['category'],
                ],
            ];
        }
        
        // Search
        if ($args['search']) {
            $query_args['s'] = $args['search'];
        }
        
        $query = new WP_Query($query_args);
        
        $documents = [];
        foreach ($query->posts as $post) {
            $documents[] = self::get_document($post->ID);
        }
        
        return [
            'documents' => $documents,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ];
    }
    
    /**
     * Delete document
     */
    public static function delete_document($document_id, $permanent = false) {
        $document = get_post($document_id);
        
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        if ($permanent) {
            // Delete all version files
            $versions = self::get_versions($document_id);
            foreach ($versions as $version) {
                if (file_exists($version->file_path)) {
                    SLM_DMS_Encryption::secure_delete($version->file_path);
                }
            }
            
            // Delete version records
            global $wpdb;
            $table = SLM_DMS::get_table('document_versions');
            $wpdb->delete($table, ['document_id' => $document_id]);
            
            // Delete post
            wp_delete_post($document_id, true);
            
            SLM_DMS::log('Document permanently deleted: ' . $document_id);
        } else {
            // Soft delete (trash)
            wp_trash_post($document_id);
            SLM_DMS::log('Document trashed: ' . $document_id);
        }
        
        return true;
    }
    
    /**
     * Check if user can access document
     */
    public static function user_can_access($document_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        // Admins can access all
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        $document = get_post($document_id);
        
        if (!$document) {
            return false;
        }
        
        // Document author can access
        if ($document->post_author == $user_id) {
            return true;
        }
        
        // Check case access
        $case_id = get_post_meta($document_id, '_slm_case_id', true);
        if ($case_id) {
            // Check if user is assigned to case
            $assigned_lawyers = get_post_meta($case_id, '_slm_assigned_lawyers', true);
            if (is_array($assigned_lawyers) && in_array($user_id, $assigned_lawyers)) {
                return true;
            }
            
            // Check if user is case client
            $client_id = get_post_meta($case_id, '_slm_client_id', true);
            if ($client_id == $user_id) {
                return true;
            }
        }
        
        // Lawyers can access all documents
        if (user_can($user_id, 'edit_others_posts')) {
            return true;
        }
        
        return apply_filters('slm_document_access', false, $document_id, $user_id);
    }
    
    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'slm_document_details',
            __('Document Details', 'flavor'),
            [__CLASS__, 'render_details_meta_box'],
            'slm_document',
            'normal',
            'high'
        );
        
        add_meta_box(
            'slm_document_versions',
            __('Version History', 'flavor'),
            [__CLASS__, 'render_versions_meta_box'],
            'slm_document',
            'normal',
            'default'
        );
    }
    
    /**
     * Render details meta box
     */
    public static function render_details_meta_box($post) {
        wp_nonce_field('slm_document_meta', 'slm_document_nonce');
        
        $filename = get_post_meta($post->ID, '_slm_original_filename', true);
        $mime_type = get_post_meta($post->ID, '_slm_mime_type', true);
        $case_id = get_post_meta($post->ID, '_slm_case_id', true);
        $folder_id = get_post_meta($post->ID, '_slm_folder_id', true);
        $download_allowed = get_post_meta($post->ID, '_slm_download_allowed', true);
        $expiry_date = get_post_meta($post->ID, '_slm_expiry_date', true);
        $current_version = get_post_meta($post->ID, '_slm_current_version', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Original Filename', 'flavor'); ?></th>
                <td><?php echo esc_html($filename); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('File Type', 'flavor'); ?></th>
                <td><?php echo esc_html($mime_type); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Current Version', 'flavor'); ?></th>
                <td>v<?php echo esc_html($current_version); ?></td>
            </tr>
            <tr>
                <th><label for="slm_download_allowed"><?php esc_html_e('Download Allowed', 'flavor'); ?></label></th>
                <td>
                    <input type="checkbox" name="slm_download_allowed" id="slm_download_allowed" value="1" <?php checked($download_allowed, '1'); ?>>
                </td>
            </tr>
            <tr>
                <th><label for="slm_expiry_date"><?php esc_html_e('Expiry Date', 'flavor'); ?></label></th>
                <td>
                    <input type="date" name="slm_expiry_date" id="slm_expiry_date" value="<?php echo esc_attr($expiry_date); ?>">
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render versions meta box
     */
    public static function render_versions_meta_box($post) {
        $versions = self::get_versions($post->ID);
        
        if (empty($versions)) {
            echo '<p>' . esc_html__('No versions found.', 'flavor') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Version', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Uploaded By', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Date', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Size', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Note', 'flavor') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($versions as $version) {
            $current = $version->is_current ? ' <strong>(' . __('Current', 'flavor') . ')</strong>' : '';
            echo '<tr>';
            echo '<td>v' . esc_html($version->version_number) . $current . '</td>';
            echo '<td>' . esc_html($version->uploaded_by_name) . '</td>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($version->created_at))) . '</td>';
            echo '<td>' . esc_html(size_format($version->file_size)) . '</td>';
            echo '<td>' . esc_html($version->upload_note) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Save meta boxes
     */
    public static function save_meta_boxes($post_id, $post) {
        if (!isset($_POST['slm_document_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['slm_document_nonce'], 'slm_document_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        update_post_meta($post_id, '_slm_download_allowed', isset($_POST['slm_download_allowed']) ? '1' : '0');
        
        if (isset($_POST['slm_expiry_date'])) {
            update_post_meta($post_id, '_slm_expiry_date', sanitize_text_field($_POST['slm_expiry_date']));
        }
    }
    
    /**
     * AJAX: Upload document
     */
    public static function ajax_upload_document() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        if (empty($_FILES['document'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 'flavor')]);
        }
        
        $result = self::upload_document($_FILES['document'], [
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'case_id' => intval($_POST['case_id'] ?? 0),
            'folder_id' => intval($_POST['folder_id'] ?? 0),
            'category' => sanitize_text_field($_POST['category'] ?? ''),
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'document_id' => $result,
            'message' => __('Document uploaded successfully.', 'flavor'),
        ]);
    }
    
    /**
     * AJAX: Delete document
     */
    public static function ajax_delete_document() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $document_id = intval($_POST['document_id'] ?? 0);
        
        if (!$document_id || !self::user_can_access($document_id)) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $permanent = isset($_POST['permanent']) && $_POST['permanent'] === 'true';
        
        $result = self::delete_document($document_id, $permanent);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Document deleted.', 'flavor')]);
    }
    
    /**
     * AJAX: Get versions
     */
    public static function ajax_get_versions() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $document_id = intval($_POST['document_id'] ?? 0);
        
        if (!$document_id || !self::user_can_access($document_id)) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $versions = self::get_versions($document_id);
        
        wp_send_json_success(['versions' => $versions]);
    }
    
    /**
     * AJAX: Download document
     */
    public static function ajax_download_document() {
        $document_id = intval($_GET['document_id'] ?? 0);
        $version = isset($_GET['version']) ? intval($_GET['version']) : null;
        
        if (!$document_id || !self::user_can_access($document_id)) {
            wp_die(__('Permission denied.', 'flavor'));
        }
        
        // Check if download allowed
        $download_allowed = get_post_meta($document_id, '_slm_download_allowed', true);
        if ($download_allowed !== '1' && !current_user_can('manage_options')) {
            wp_die(__('Downloads are not allowed for this document.', 'flavor'));
        }
        
        $content_data = self::get_document_content($document_id, $version);
        
        if (is_wp_error($content_data)) {
            wp_die($content_data->get_error_message());
        }
        
        // Log download
        do_action('slm_document_downloaded', $document_id, get_current_user_id());
        
        // Output file
        header('Content-Type: ' . $content_data['mime_type']);
        header('Content-Disposition: attachment; filename="' . $content_data['filename'] . '"');
        header('Content-Length: ' . strlen($content_data['content']));
        header('Cache-Control: no-cache, must-revalidate');
        
        echo $content_data['content'];
        exit;
    }
    
    /**
     * Handle REST upload
     */
    public static function handle_upload($request) {
        $files = $request->get_file_params();
        
        if (empty($files['document'])) {
            return new WP_Error('no_file', __('No file uploaded.', 'flavor'), ['status' => 400]);
        }
        
        $params = $request->get_params();
        
        $result = self::upload_document($files['document'], [
            'title' => sanitize_text_field($params['title'] ?? ''),
            'description' => sanitize_textarea_field($params['description'] ?? ''),
            'case_id' => intval($params['case_id'] ?? 0),
            'folder_id' => intval($params['folder_id'] ?? 0),
            'category' => sanitize_text_field($params['category'] ?? ''),
        ]);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return self::get_document($result);
    }
}
