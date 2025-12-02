<?php
/**
 * SLM DMS Folders
 * 
 * Hierarchical folder management:
 * - Create/rename/delete folders
 * - Move documents between folders
 * - Folder permissions
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Folders {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('wp_ajax_slm_create_folder', [__CLASS__, 'ajax_create_folder']);
        add_action('wp_ajax_slm_rename_folder', [__CLASS__, 'ajax_rename_folder']);
        add_action('wp_ajax_slm_delete_folder', [__CLASS__, 'ajax_delete_folder']);
        add_action('wp_ajax_slm_move_document', [__CLASS__, 'ajax_move_document']);
        add_action('wp_ajax_slm_get_folder_tree', [__CLASS__, 'ajax_get_folder_tree']);
    }
    
    /**
     * Create folder
     */
    public static function create_folder($args) {
        $defaults = [
            'name' => '',
            'case_id' => 0,
            'parent_id' => 0,
            'created_by' => get_current_user_id(),
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        if (empty($args['name'])) {
            return new WP_Error('empty_name', __('Folder name is required.', 'flavor'));
        }
        
        // Check for duplicate name in same location
        if (self::folder_exists($args['name'], $args['case_id'], $args['parent_id'])) {
            return new WP_Error('duplicate_name', __('A folder with this name already exists.', 'flavor'));
        }
        
        $post_id = wp_insert_post([
            'post_type' => 'slm_folder',
            'post_title' => sanitize_text_field($args['name']),
            'post_status' => 'publish',
            'post_author' => $args['created_by'],
            'post_parent' => $args['parent_id'],
        ]);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Store metadata
        update_post_meta($post_id, '_slm_case_id', intval($args['case_id']));
        update_post_meta($post_id, '_slm_storage_hash', wp_generate_password(16, false));
        
        SLM_DMS::log('Folder created: ' . $post_id . ' (' . $args['name'] . ')');
        
        return $post_id;
    }
    
    /**
     * Check if folder name exists
     */
    private static function folder_exists($name, $case_id, $parent_id) {
        $query = new WP_Query([
            'post_type' => 'slm_folder',
            'post_parent' => $parent_id,
            'title' => $name,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $case_id,
                ],
            ],
        ]);
        
        return $query->have_posts();
    }
    
    /**
     * Rename folder
     */
    public static function rename_folder($folder_id, $new_name) {
        $folder = get_post($folder_id);
        
        if (!$folder || $folder->post_type !== 'slm_folder') {
            return new WP_Error('invalid_folder', __('Invalid folder.', 'flavor'));
        }
        
        $case_id = get_post_meta($folder_id, '_slm_case_id', true);
        
        // Check for duplicate name
        if (self::folder_exists($new_name, $case_id, $folder->post_parent) && $folder->post_title !== $new_name) {
            return new WP_Error('duplicate_name', __('A folder with this name already exists.', 'flavor'));
        }
        
        $result = wp_update_post([
            'ID' => $folder_id,
            'post_title' => sanitize_text_field($new_name),
        ]);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        SLM_DMS::log('Folder renamed: ' . $folder_id . ' to ' . $new_name);
        
        return true;
    }
    
    /**
     * Delete folder
     */
    public static function delete_folder($folder_id) {
        $folder = get_post($folder_id);
        
        if (!$folder || $folder->post_type !== 'slm_folder') {
            return new WP_Error('invalid_folder', __('Invalid folder.', 'flavor'));
        }
        
        // Check if folder has subfolders
        $subfolders = get_posts([
            'post_type' => 'slm_folder',
            'post_parent' => $folder_id,
            'posts_per_page' => 1,
        ]);
        
        if (!empty($subfolders)) {
            return new WP_Error('has_subfolders', __('Cannot delete folder with subfolders. Delete subfolders first.', 'flavor'));
        }
        
        // Check if folder has documents
        $documents = get_posts([
            'post_type' => 'slm_document',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_slm_folder_id',
                    'value' => $folder_id,
                ],
            ],
        ]);
        
        if (!empty($documents)) {
            return new WP_Error('has_documents', __('Cannot delete folder with documents. Move or delete documents first.', 'flavor'));
        }
        
        wp_delete_post($folder_id, true);
        
        SLM_DMS::log('Folder deleted: ' . $folder_id);
        
        return true;
    }
    
    /**
     * Move document to folder
     */
    public static function move_document($document_id, $folder_id) {
        $document = get_post($document_id);
        
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        // Validate folder if specified
        if ($folder_id > 0) {
            $folder = get_post($folder_id);
            if (!$folder || $folder->post_type !== 'slm_folder') {
                return new WP_Error('invalid_folder', __('Invalid folder.', 'flavor'));
            }
        }
        
        $old_folder = get_post_meta($document_id, '_slm_folder_id', true);
        update_post_meta($document_id, '_slm_folder_id', intval($folder_id));
        
        SLM_DMS::log('Document ' . $document_id . ' moved from folder ' . $old_folder . ' to ' . $folder_id);
        
        return true;
    }
    
    /**
     * Get folder details
     */
    public static function get_folder($folder_id) {
        $folder = get_post($folder_id);
        
        if (!$folder || $folder->post_type !== 'slm_folder') {
            return null;
        }
        
        return [
            'id' => $folder->ID,
            'name' => $folder->post_title,
            'parent_id' => $folder->post_parent,
            'case_id' => get_post_meta($folder_id, '_slm_case_id', true),
            'created_at' => $folder->post_date,
            'author' => get_the_author_meta('display_name', $folder->post_author),
        ];
    }
    
    /**
     * Get folder tree for a case
     */
    public static function get_folder_tree($case_id, $parent_id = 0) {
        $folders = get_posts([
            'post_type' => 'slm_folder',
            'post_parent' => $parent_id,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $case_id,
                ],
            ],
        ]);
        
        $tree = [];
        
        foreach ($folders as $folder) {
            $tree[] = [
                'id' => $folder->ID,
                'name' => $folder->post_title,
                'parent_id' => $folder->post_parent,
                'children' => self::get_folder_tree($case_id, $folder->ID),
                'document_count' => self::count_documents($folder->ID),
            ];
        }
        
        return $tree;
    }
    
    /**
     * Count documents in folder
     */
    public static function count_documents($folder_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'slm_document'
             AND p.post_status = 'publish'
             AND pm.meta_key = '_slm_folder_id'
             AND pm.meta_value = %d",
            $folder_id
        ));
    }
    
    /**
     * Get folder contents (documents and subfolders)
     */
    public static function get_folder_contents($folder_id, $case_id = 0) {
        // Get subfolders
        $subfolders_args = [
            'post_type' => 'slm_folder',
            'post_parent' => $folder_id,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        
        if ($case_id) {
            $subfolders_args['meta_query'] = [
                [
                    'key' => '_slm_case_id',
                    'value' => $case_id,
                ],
            ];
        }
        
        $subfolders = get_posts($subfolders_args);
        
        // Get documents
        $documents_args = [
            'post_type' => 'slm_document',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => '_slm_folder_id',
                    'value' => $folder_id,
                ],
            ],
        ];
        
        if ($case_id && $folder_id === 0) {
            $documents_args['meta_query'][] = [
                'key' => '_slm_case_id',
                'value' => $case_id,
            ];
        }
        
        $documents = get_posts($documents_args);
        
        return [
            'folders' => array_map([__CLASS__, 'get_folder'], wp_list_pluck($subfolders, 'ID')),
            'documents' => array_map(['SLM_DMS_Documents', 'get_document'], wp_list_pluck($documents, 'ID')),
        ];
    }
    
    /**
     * Get breadcrumb path for folder
     */
    public static function get_breadcrumb($folder_id) {
        $breadcrumb = [];
        $current_id = $folder_id;
        
        while ($current_id > 0) {
            $folder = get_post($current_id);
            if (!$folder) {
                break;
            }
            
            array_unshift($breadcrumb, [
                'id' => $folder->ID,
                'name' => $folder->post_title,
            ]);
            
            $current_id = $folder->post_parent;
        }
        
        return $breadcrumb;
    }
    
    /**
     * AJAX: Create folder
     */
    public static function ajax_create_folder() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $result = self::create_folder([
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'case_id' => intval($_POST['case_id'] ?? 0),
            'parent_id' => intval($_POST['parent_id'] ?? 0),
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'folder_id' => $result,
            'folder' => self::get_folder($result),
        ]);
    }
    
    /**
     * AJAX: Rename folder
     */
    public static function ajax_rename_folder() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $folder_id = intval($_POST['folder_id'] ?? 0);
        $new_name = sanitize_text_field($_POST['name'] ?? '');
        
        $result = self::rename_folder($folder_id, $new_name);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Folder renamed.', 'flavor')]);
    }
    
    /**
     * AJAX: Delete folder
     */
    public static function ajax_delete_folder() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $folder_id = intval($_POST['folder_id'] ?? 0);
        
        $result = self::delete_folder($folder_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Folder deleted.', 'flavor')]);
    }
    
    /**
     * AJAX: Move document
     */
    public static function ajax_move_document() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $document_id = intval($_POST['document_id'] ?? 0);
        $folder_id = intval($_POST['folder_id'] ?? 0);
        
        $result = self::move_document($document_id, $folder_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Document moved.', 'flavor')]);
    }
    
    /**
     * AJAX: Get folder tree
     */
    public static function ajax_get_folder_tree() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id'] ?? 0);
        
        wp_send_json_success([
            'tree' => self::get_folder_tree($case_id),
        ]);
    }
}
