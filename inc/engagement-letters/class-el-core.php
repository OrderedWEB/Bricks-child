<?php
/**
 * Engagement Letters Core System
 * 
 * Handles status management, favorites, and core utilities for the engagement letter system.
 * This is the foundation class that orchestrates all EL functionality.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EL_Core {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Engagement letter statuses with display names and colors
     */
    const STATUSES = [
        'draft' => [
            'label' => 'Draft',
            'color' => '#94a3b8',
            'icon' => '📝',
            'description' => 'Work in progress, not yet ready to send'
        ],
        'ready_to_send' => [
            'label' => 'Ready to Send',
            'color' => '#3b82f6',
            'icon' => '✓',
            'description' => 'Completed and ready for client signature'
        ],
        'sent_for_signature' => [
            'label' => 'Awaiting Signature',
            'color' => '#f59e0b',
            'icon' => '⏳',
            'description' => 'Sent to client, waiting for signatures'
        ],
        'signed' => [
            'label' => 'Signed',
            'color' => '#10b981',
            'icon' => '✍️',
            'description' => 'All parties have signed, awaiting payment'
        ],
        'paid' => [
            'label' => 'Paid',
            'color' => '#06b6d4',
            'icon' => '💰',
            'description' => 'Payment received, matter active'
        ],
        'completed' => [
            'label' => 'Completed',
            'color' => '#8b5cf6',
            'icon' => '🎯',
            'description' => 'Matter concluded successfully'
        ],
        'expired' => [
            'label' => 'Expired',
            'color' => '#ef4444',
            'icon' => '⚠️',
            'description' => 'Engagement letter expired (30+ days in draft)'
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => '#6b7280',
            'icon' => '✖',
            'description' => 'Engagement cancelled by lawyer or client'
        ]
    ];
    
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
     * Constructor - Initialize hooks and load dependencies
     */
    private function __construct() {
        // Load dependent classes
        $this->load_dependencies();
        
        // Register post type and taxonomies
        add_action('init', [$this, 'register_post_type']);
        
        // Add meta boxes
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        
        // Save post meta
        add_action('save_post_engagement_letter', [$this, 'save_meta_data']);
        
        // Initialize cron job
        add_action('init', [$this, 'schedule_expiry_check']);
    }
    
    /**
     * Load all dependent class files
     */
    private function load_dependencies() {
        $dir = get_stylesheet_directory() . '/inc/engagement-letters/';
        
        $files = [
            'class-el-woocommerce.php',
            'class-el-shortcodes.php',
            'class-el-actions.php',
            'class-el-cron.php'
        ];
        
        foreach ($files as $file) {
            $filepath = $dir . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }
    
    /**
     * Register Engagement Letter custom post type
     */
    public function register_post_type() {
        $labels = [
            'name' => 'Engagement Letters',
            'singular_name' => 'Engagement Letter',
            'menu_name' => 'Engagement Letters',
            'add_new' => 'Create New',
            'add_new_item' => 'Create New Engagement Letter',
            'edit_item' => 'Edit Engagement Letter',
            'new_item' => 'New Engagement Letter',
            'view_item' => 'View Engagement Letter',
            'search_items' => 'Search Engagement Letters',
            'not_found' => 'No engagement letters found',
            'not_found_in_trash' => 'No engagement letters found in trash',
        ];
        
        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-portfolio',
            'menu_position' => 26,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
            'can_export' => true,
            'show_in_rest' => false,
        ];
        
        register_post_type('engagement_letter', $args);
    }
    
    /**
     * Add meta boxes to engagement letter edit screen
     */
    public function add_meta_boxes() {
        add_meta_box(
            'el_details',
            'Engagement Letter Details',
            [$this, 'render_details_meta_box'],
            'engagement_letter',
            'normal',
            'high'
        );
        
        add_meta_box(
            'el_status',
            'Status & Actions',
            [$this, 'render_status_meta_box'],
            'engagement_letter',
            'side',
            'high'
        );
    }
    
    /**
     * Render engagement letter details meta box
     */
    public function render_details_meta_box($post) {
        wp_nonce_field('el_details_nonce', 'el_details_nonce_field');
        
        $client_user_id = get_post_meta($post->ID, '_el_client_user_id', true);
        $woo_order_id = get_post_meta($post->ID, '_el_woo_order_id', true);
        $matter_ref = get_post_meta($post->ID, '_el_matter_ref', true);
        $practice_area = get_post_meta($post->ID, '_el_practice_area', true);
        
        // Get client details
        $client_name = '';
        $client_email = '';
        if ($client_user_id) {
            $client = get_user_by('ID', $client_user_id);
            if ($client) {
                $client_name = $client->display_name;
                $client_email = $client->user_email;
            }
        }
        
        ?>
        <style>
            .el-details-grid {
                display: grid;
                grid-template-columns: 150px 1fr;
                gap: 15px;
                margin: 15px 0;
            }
            .el-details-label {
                font-weight: 600;
                color: #1e293b;
                padding-top: 5px;
            }
            .el-details-value {
                color: #475569;
            }
            .el-details-value input,
            .el-details-value select {
                width: 100%;
                max-width: 400px;
            }
            .el-client-info {
                background: #f8fafc;
                padding: 15px;
                border-radius: 8px;
                border-left: 4px solid #667eea;
                margin-bottom: 20px;
            }
            .el-client-name {
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 5px;
            }
            .el-client-email {
                color: #64748b;
                font-size: 14px;
            }
        </style>
        
        <?php if ($client_name): ?>
        <div class="el-client-info">
            <div class="el-client-name">👤 <?php echo esc_html($client_name); ?></div>
            <div class="el-client-email">📧 <?php echo esc_html($client_email); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="el-details-grid">
            <div class="el-details-label">
                <label for="el_matter_ref">Matter Reference:</label>
            </div>
            <div class="el-details-value">
                <input 
                    type="text" 
                    id="el_matter_ref" 
                    name="el_matter_ref" 
                    value="<?php echo esc_attr($matter_ref); ?>"
                    placeholder="e.g., 2025-FAM-001"
                    aria-label="Matter reference number"
                />
            </div>
            
            <div class="el-details-label">
                <label for="el_practice_area">Practice Area:</label>
            </div>
            <div class="el-details-value">
                <select id="el_practice_area" name="el_practice_area" aria-label="Practice area">
                    <option value="">Select practice area...</option>
                    <option value="Civil Litigation" <?php selected($practice_area, 'Civil Litigation'); ?>>Civil Litigation</option>
                    <option value="Family Law" <?php selected($practice_area, 'Family Law'); ?>>Family Law</option>
                    <option value="Real Estate" <?php selected($practice_area, 'Real Estate'); ?>>Real Estate</option>
                    <option value="Citizenship" <?php selected($practice_area, 'Citizenship'); ?>>Citizenship</option>
                    <option value="Corporate" <?php selected($practice_area, 'Corporate'); ?>>Corporate</option>
                    <option value="Employment" <?php selected($practice_area, 'Employment'); ?>>Employment</option>
                    <option value="General (Includes All P.A.)" <?php selected($practice_area, 'General (Includes All P.A.)'); ?>>General (Includes All P.A.)</option>
                </select>
            </div>
            
            <?php if ($woo_order_id): ?>
            <div class="el-details-label">WooCommerce Order:</div>
            <div class="el-details-value">
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $woo_order_id . '&action=edit')); ?>" 
                   target="_blank"
                   style="color: #667eea; text-decoration: none; font-weight: 600;">
                    Order #<?php echo esc_html($woo_order_id); ?> →
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render status and actions meta box
     */
    public function render_status_meta_box($post) {
        wp_nonce_field('el_status_nonce', 'el_status_nonce_field');
        
        $current_status = get_post_meta($post->ID, '_el_status', true);
        if (empty($current_status)) {
            $current_status = 'draft';
        }
        
        $is_favorite = get_post_meta($post->ID, '_el_is_favorite', true);
        $created_date = get_the_date('F j, Y g:i a', $post);
        $modified_date = get_the_modified_date('F j, Y g:i a', $post);
        
        ?>
        <style>
            .el-status-box {
                margin: 15px 0;
            }
            .el-status-current {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px;
                background: #f8fafc;
                border-radius: 8px;
                margin-bottom: 15px;
                border-left: 4px solid currentColor;
            }
            .el-status-icon {
                font-size: 24px;
            }
            .el-status-text {
                flex: 1;
            }
            .el-status-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #64748b;
                margin-bottom: 3px;
            }
            .el-status-name {
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
            }
            .el-status-select {
                width: 100%;
                padding: 8px;
                border: 2px solid #e2e8f0;
                border-radius: 6px;
                font-size: 14px;
                margin-bottom: 10px;
            }
            .el-status-select:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .el-favorite-toggle {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 10px;
                background: #fffbeb;
                border: 2px solid #fbbf24;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                margin-bottom: 15px;
            }
            .el-favorite-toggle:hover {
                background: #fef3c7;
            }
            .el-favorite-toggle input {
                margin: 0;
            }
            .el-favorite-toggle label {
                cursor: pointer;
                margin: 0;
                font-weight: 500;
                color: #92400e;
            }
            .el-dates {
                font-size: 12px;
                color: #64748b;
                line-height: 1.6;
                padding-top: 10px;
                border-top: 1px solid #e2e8f0;
            }
            .el-dates strong {
                color: #475569;
            }
        </style>
        
        <?php
        $status_info = self::STATUSES[$current_status];
        ?>
        
        <div class="el-status-box">
            <div class="el-status-current" style="color: <?php echo esc_attr($status_info['color']); ?>;">
                <div class="el-status-icon"><?php echo $status_info['icon']; ?></div>
                <div class="el-status-text">
                    <div class="el-status-label">Current Status</div>
                    <div class="el-status-name"><?php echo esc_html($status_info['label']); ?></div>
                </div>
            </div>
            
            <label for="el_status" style="display: block; margin-bottom: 5px; font-weight: 600; color: #1e293b;">
                Change Status:
            </label>
            <select id="el_status" name="el_status" class="el-status-select" aria-label="Engagement letter status">
                <?php foreach (self::STATUSES as $status => $info): ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected($current_status, $status); ?>>
                        <?php echo esc_html($info['icon'] . ' ' . $info['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <div class="el-favorite-toggle">
                <input 
                    type="checkbox" 
                    id="el_is_favorite" 
                    name="el_is_favorite" 
                    value="1" 
                    <?php checked($is_favorite, '1'); ?>
                    aria-label="Mark as favorite"
                />
                <label for="el_is_favorite">⭐ Mark as Favorite</label>
            </div>
            
            <div class="el-dates">
                <strong>Created:</strong> <?php echo esc_html($created_date); ?><br>
                <strong>Modified:</strong> <?php echo esc_html($modified_date); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save meta data when post is saved
     */
    public function save_meta_data($post_id) {
        // Check nonces
        if (!isset($_POST['el_details_nonce_field']) || 
            !wp_verify_nonce($_POST['el_details_nonce_field'], 'el_details_nonce')) {
            return;
        }
        
        if (!isset($_POST['el_status_nonce_field']) || 
            !wp_verify_nonce($_POST['el_status_nonce_field'], 'el_status_nonce')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save matter reference
        if (isset($_POST['el_matter_ref'])) {
            update_post_meta($post_id, '_el_matter_ref', sanitize_text_field($_POST['el_matter_ref']));
        }
        
        // Save practice area
        if (isset($_POST['el_practice_area'])) {
            update_post_meta($post_id, '_el_practice_area', sanitize_text_field($_POST['el_practice_area']));
        }
        
        // Save status
        if (isset($_POST['el_status'])) {
            $old_status = get_post_meta($post_id, '_el_status', true);
            $new_status = sanitize_key($_POST['el_status']);
            
            if (array_key_exists($new_status, self::STATUSES)) {
                update_post_meta($post_id, '_el_status', $new_status);
                
                // Trigger status change hook
                if ($old_status !== $new_status) {
                    do_action('el_status_changed', $post_id, $old_status, $new_status);
                    
                    // Log status change
                    $this->log_status_change($post_id, $old_status, $new_status);
                }
            }
        }
        
        // Save favorite
        $is_favorite = isset($_POST['el_is_favorite']) ? '1' : '0';
        update_post_meta($post_id, '_el_is_favorite', $is_favorite);
    }
    
    /**
     * Log status changes for audit trail
     */
    private function log_status_change($post_id, $old_status, $new_status) {
        $logs = get_post_meta($post_id, '_el_status_history', true);
        if (!is_array($logs)) {
            $logs = [];
        }
        
        $logs[] = [
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'user_name' => wp_get_current_user()->display_name,
            'from_status' => $old_status,
            'to_status' => $new_status,
            'ip_address' => $this->get_client_ip()
        ];
        
        update_post_meta($post_id, '_el_status_history', $logs);
    }
    
    /**
     * Get client IP address (with proxy support)
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    
    /**
     * Schedule daily cron job to check for expired engagement letters
     */
    public function schedule_expiry_check() {
        if (!wp_next_scheduled('el_check_expired_letters')) {
            wp_schedule_event(time(), 'daily', 'el_check_expired_letters');
        }
    }
    
    /**
     * Get status information
     */
    public static function get_status_info($status) {
        return self::STATUSES[$status] ?? self::STATUSES['draft'];
    }
    
    /**
     * Get all statuses
     */
    public static function get_all_statuses() {
        return self::STATUSES;
    }
    
    /**
     * Toggle favorite status
     */
    public static function toggle_favorite($post_id) {
        if (get_post_type($post_id) !== 'engagement_letter') {
            return false;
        }
        
        $current = get_post_meta($post_id, '_el_is_favorite', true);
        $new_value = ($current === '1') ? '0' : '1';
        
        update_post_meta($post_id, '_el_is_favorite', $new_value);
        
        return ($new_value === '1');
    }
    
    /**
     * Check if engagement letter is favorite
     */
    public static function is_favorite($post_id) {
        return get_post_meta($post_id, '_el_is_favorite', true) === '1';
    }
    
    /**
     * Update engagement letter status
     */
    public static function update_status($post_id, $new_status) {
        if (get_post_type($post_id) !== 'engagement_letter') {
            return false;
        }
        
        if (!array_key_exists($new_status, self::STATUSES)) {
            return false;
        }
        
        $old_status = get_post_meta($post_id, '_el_status', true);
        update_post_meta($post_id, '_el_status', $new_status);
        
        // Trigger status change hook
        if ($old_status !== $new_status) {
            do_action('el_status_changed', $post_id, $old_status, $new_status);
        }
        
        return true;
    }
    
    /**
     * Get engagement letters by status
     */
    public static function get_letters_by_status($status, $args = []) {
        $defaults = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_el_status',
                    'value' => $status,
                    'compare' => '='
                ]
            ]
        ];
        
        $query_args = wp_parse_args($args, $defaults);
        return new WP_Query($query_args);
    }
    
    /**
     * Get favorite engagement letters
     */
    public static function get_favorite_letters($args = []) {
        $defaults = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_el_is_favorite',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];
        
        $query_args = wp_parse_args($args, $defaults);
        return new WP_Query($query_args);
    }
}

// Initialize the core system
EL_Core::get_instance();