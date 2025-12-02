<?php
/**
 * SLM DMS Sharing
 * 
 * External document sharing:
 * - Time-limited links
 * - Optional password protection
 * - Download control
 * - View limits
 * - Access logging
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Sharing {
    
    /**
     * Default expiry (7 days)
     */
    const DEFAULT_EXPIRY_HOURS = 168;
    
    /**
     * Token length
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('wp_ajax_slm_create_share_link', [__CLASS__, 'ajax_create_link']);
        add_action('wp_ajax_slm_revoke_share_link', [__CLASS__, 'ajax_revoke_link']);
        add_action('wp_ajax_slm_get_share_links', [__CLASS__, 'ajax_get_links']);
        add_action('wp_ajax_nopriv_slm_verify_share_password', [__CLASS__, 'ajax_verify_password']);
        
        // Meta boxes
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
    }
    
    /**
     * Create share link
     */
    public static function create_link($document_id, $args = []) {
        $defaults = [
            'expiry_hours' => self::DEFAULT_EXPIRY_HOURS,
            'password' => null,
            'download_allowed' => false,
            'max_views' => 0,
            'recipient_name' => '',
            'recipient_email' => '',
            'notify_on_view' => true,
            'created_by' => get_current_user_id(),
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate document
        $document = get_post($document_id);
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        // Check permission
        if (!SLM_DMS_Documents::user_can_access($document_id)) {
            return new WP_Error('permission_denied', __('You cannot share this document.', 'flavor'));
        }
        
        // Generate token
        $token = wp_generate_password(self::TOKEN_LENGTH, false);
        
        // Calculate expiry
        $expiry_date = date('Y-m-d H:i:s', time() + ($args['expiry_hours'] * 3600));
        
        // Hash password if provided
        $password_hash = '';
        if (!empty($args['password'])) {
            $password_hash = wp_hash_password($args['password']);
        }
        
        // Create share link post
        $title = sprintf(
            __('Share: %s', 'flavor'),
            $document->post_title
        );
        
        if (!empty($args['recipient_name'])) {
            $title .= ' - ' . $args['recipient_name'];
        }
        
        $post_id = wp_insert_post([
            'post_type' => 'slm_share_link',
            'post_title' => $title,
            'post_status' => 'publish',
            'post_author' => $args['created_by'],
        ]);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Store metadata
        update_post_meta($post_id, '_slm_document_id', $document_id);
        update_post_meta($post_id, '_slm_access_token', $token);
        update_post_meta($post_id, '_slm_expiry_date', $expiry_date);
        update_post_meta($post_id, '_slm_password_hash', $password_hash);
        update_post_meta($post_id, '_slm_download_allowed', $args['download_allowed'] ? '1' : '0');
        update_post_meta($post_id, '_slm_max_views', intval($args['max_views']));
        update_post_meta($post_id, '_slm_view_count', 0);
        update_post_meta($post_id, '_slm_recipient_name', sanitize_text_field($args['recipient_name']));
        update_post_meta($post_id, '_slm_recipient_email', sanitize_email($args['recipient_email']));
        update_post_meta($post_id, '_slm_notify_on_view', $args['notify_on_view'] ? '1' : '0');
        update_post_meta($post_id, '_slm_status', 'active');
        
        $share_url = home_url('/shared-document/' . $token . '/');
        
        SLM_DMS::log('Share link created: ' . $post_id . ' for document ' . $document_id);
        
        // Send notification to recipient if email provided
        if (!empty($args['recipient_email'])) {
            self::send_share_notification($post_id, $args['recipient_email']);
        }
        
        return [
            'link_id' => $post_id,
            'token' => $token,
            'url' => $share_url,
            'expires' => $expiry_date,
            'has_password' => !empty($password_hash),
        ];
    }
    
    /**
     * Validate share link
     */
    public static function validate_link($token) {
        global $wpdb;
        
        // Find share link by token
        $link_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_slm_access_token' AND meta_value = %s",
            $token
        ));
        
        if (!$link_id) {
            return new WP_Error('invalid_link', __('This link is invalid or has been removed.', 'flavor'));
        }
        
        $link = get_post($link_id);
        if (!$link || $link->post_type !== 'slm_share_link') {
            return new WP_Error('invalid_link', __('This link is invalid.', 'flavor'));
        }
        
        // Check status
        $status = get_post_meta($link_id, '_slm_status', true);
        if ($status !== 'active') {
            return new WP_Error('link_revoked', __('This link has been revoked.', 'flavor'));
        }
        
        // Check expiry
        $expiry = get_post_meta($link_id, '_slm_expiry_date', true);
        if ($expiry && strtotime($expiry) < time()) {
            return new WP_Error('link_expired', __('This link has expired.', 'flavor'));
        }
        
        // Check view limit
        $max_views = intval(get_post_meta($link_id, '_slm_max_views', true));
        $view_count = intval(get_post_meta($link_id, '_slm_view_count', true));
        
        if ($max_views > 0 && $view_count >= $max_views) {
            return new WP_Error('view_limit', __('This link has reached its view limit.', 'flavor'));
        }
        
        return [
            'link_id' => $link_id,
            'document_id' => get_post_meta($link_id, '_slm_document_id', true),
            'has_password' => !empty(get_post_meta($link_id, '_slm_password_hash', true)),
            'download_allowed' => get_post_meta($link_id, '_slm_download_allowed', true) === '1',
            'recipient_name' => get_post_meta($link_id, '_slm_recipient_name', true),
        ];
    }
    
    /**
     * Verify share link password
     */
    public static function verify_password($link_id, $password) {
        $stored_hash = get_post_meta($link_id, '_slm_password_hash', true);
        
        if (empty($stored_hash)) {
            return true;
        }
        
        return wp_check_password($password, $stored_hash);
    }
    
    /**
     * Record access
     */
    public static function record_access($link_id, $access_type = 'view', $success = true) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('share_access_log');
        $document_id = get_post_meta($link_id, '_slm_document_id', true);
        
        $wpdb->insert($table, [
            'share_link_id' => $link_id,
            'document_id' => $document_id,
            'access_type' => $access_type,
            'success' => $success ? 1 : 0,
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '',
            'referer' => isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 500) : '',
            'accessed_at' => current_time('mysql'),
        ]);
        
        // Increment view count
        if ($access_type === 'view' && $success) {
            $view_count = intval(get_post_meta($link_id, '_slm_view_count', true));
            update_post_meta($link_id, '_slm_view_count', $view_count + 1);
            
            // Notify owner if enabled
            $notify = get_post_meta($link_id, '_slm_notify_on_view', true);
            if ($notify === '1') {
                self::send_view_notification($link_id);
            }
        }
    }
    
    /**
     * Revoke share link
     */
    public static function revoke_link($link_id) {
        $link = get_post($link_id);
        
        if (!$link || $link->post_type !== 'slm_share_link') {
            return new WP_Error('invalid_link', __('Invalid share link.', 'flavor'));
        }
        
        update_post_meta($link_id, '_slm_status', 'revoked');
        
        SLM_DMS::log('Share link revoked: ' . $link_id);
        
        return true;
    }
    
    /**
     * Get share links for document
     */
    public static function get_document_links($document_id) {
        $links = get_posts([
            'post_type' => 'slm_share_link',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_slm_document_id',
                    'value' => $document_id,
                ],
            ],
        ]);
        
        $result = [];
        
        foreach ($links as $link) {
            $result[] = self::get_link_data($link->ID);
        }
        
        return $result;
    }
    
    /**
     * Get link data
     */
    public static function get_link_data($link_id) {
        $link = get_post($link_id);
        
        if (!$link) {
            return null;
        }
        
        $status = get_post_meta($link_id, '_slm_status', true);
        $expiry = get_post_meta($link_id, '_slm_expiry_date', true);
        
        // Check if expired
        if ($status === 'active' && $expiry && strtotime($expiry) < time()) {
            $status = 'expired';
        }
        
        return [
            'id' => $link_id,
            'document_id' => get_post_meta($link_id, '_slm_document_id', true),
            'token' => get_post_meta($link_id, '_slm_access_token', true),
            'url' => home_url('/shared-document/' . get_post_meta($link_id, '_slm_access_token', true) . '/'),
            'expiry_date' => $expiry,
            'status' => $status,
            'has_password' => !empty(get_post_meta($link_id, '_slm_password_hash', true)),
            'download_allowed' => get_post_meta($link_id, '_slm_download_allowed', true) === '1',
            'max_views' => get_post_meta($link_id, '_slm_max_views', true),
            'view_count' => get_post_meta($link_id, '_slm_view_count', true),
            'recipient_name' => get_post_meta($link_id, '_slm_recipient_name', true),
            'recipient_email' => get_post_meta($link_id, '_slm_recipient_email', true),
            'created_at' => $link->post_date,
            'created_by' => get_the_author_meta('display_name', $link->post_author),
        ];
    }
    
    /**
     * Render shared document view
     */
    public static function render_shared_view($token) {
        // Validate link
        $validation = self::validate_link($token);
        
        if (is_wp_error($validation)) {
            self::render_error_page($validation->get_error_message());
            return;
        }
        
        // Check password
        if ($validation['has_password']) {
            $password_verified = isset($_SESSION['slm_share_' . $token]);
            
            if (!$password_verified) {
                self::render_password_page($token, $validation);
                return;
            }
        }
        
        // Record access
        self::record_access($validation['link_id'], 'view', true);
        
        // Create viewing session
        $session = SLM_DMS_Viewer::create_session(
            $validation['document_id'],
            null,
            $validation['link_id']
        );
        
        if (is_wp_error($session)) {
            self::render_error_page($session->get_error_message());
            return;
        }
        
        // Redirect to viewer
        wp_redirect($session['viewer_url']);
        exit;
    }
    
    /**
     * Render password page
     */
    private static function render_password_page($token, $validation) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php esc_html_e('Password Required', 'flavor'); ?> - <?php echo esc_html($firm_name); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #1e3a5f 0%, #0f1c2e 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .password-card {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    padding: 40px;
                    max-width: 400px;
                    width: 100%;
                    text-align: center;
                }
                .lock-icon {
                    width: 64px;
                    height: 64px;
                    margin: 0 auto 20px;
                    background: #f3f4f6;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .lock-icon svg {
                    width: 32px;
                    height: 32px;
                    fill: #1e3a5f;
                }
                h1 {
                    font-size: 20px;
                    color: #1e3a5f;
                    margin-bottom: 8px;
                }
                .subtitle {
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 24px;
                }
                .form-group {
                    margin-bottom: 16px;
                    text-align: left;
                }
                label {
                    display: block;
                    font-size: 14px;
                    font-weight: 500;
                    color: #374151;
                    margin-bottom: 6px;
                }
                input[type="password"] {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.2s;
                }
                input[type="password"]:focus {
                    outline: none;
                    border-color: #2563eb;
                }
                .error-message {
                    background: #fee2e2;
                    color: #dc2626;
                    padding: 12px;
                    border-radius: 8px;
                    font-size: 14px;
                    margin-bottom: 16px;
                }
                .submit-btn {
                    width: 100%;
                    background: #2563eb;
                    color: #fff;
                    border: none;
                    padding: 14px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background 0.2s;
                }
                .submit-btn:hover {
                    background: #1d4ed8;
                }
                .recipient {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #e5e7eb;
                    font-size: 13px;
                    color: #6b7280;
                }
            </style>
        </head>
        <body>
            <div class="password-card">
                <div class="lock-icon">
                    <svg viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    </svg>
                </div>
                
                <h1><?php esc_html_e('Password Protected', 'flavor'); ?></h1>
                <p class="subtitle"><?php esc_html_e('This document requires a password to view.', 'flavor'); ?></p>
                
                <?php if ($error): ?>
                <div class="error-message"><?php esc_html_e('Incorrect password. Please try again.', 'flavor'); ?></div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                    <input type="hidden" name="action" value="slm_verify_share_password">
                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                    <?php wp_nonce_field('slm_share_password', 'nonce'); ?>
                    
                    <div class="form-group">
                        <label for="password"><?php esc_html_e('Password', 'flavor'); ?></label>
                        <input type="password" id="password" name="password" required autofocus>
                    </div>
                    
                    <button type="submit" class="submit-btn"><?php esc_html_e('View Document', 'flavor'); ?></button>
                </form>
                
                <?php if (!empty($validation['recipient_name'])): ?>
                <p class="recipient">
                    <?php printf(esc_html__('Shared with: %s', 'flavor'), esc_html($validation['recipient_name'])); ?>
                </p>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render error page
     */
    private static function render_error_page($message) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php esc_html_e('Link Error', 'flavor'); ?></title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #1e3a5f 0%, #0f1c2e 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    color: #fff;
                }
                .error-card {
                    text-align: center;
                    padding: 40px;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    font-size: 24px;
                    margin-bottom: 12px;
                }
                .message {
                    color: #e94560;
                    font-size: 16px;
                    margin-bottom: 24px;
                }
                .contact {
                    font-size: 14px;
                    color: #a0a0a0;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-icon">🔗</div>
                <h1><?php esc_html_e('Link Unavailable', 'flavor'); ?></h1>
                <p class="message"><?php echo esc_html($message); ?></p>
                <p class="contact"><?php esc_html_e('Please contact the sender for a new link.', 'flavor'); ?></p>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Send share notification email
     */
    private static function send_share_notification($link_id, $email) {
        $link_data = self::get_link_data($link_id);
        $document = SLM_DMS_Documents::get_document($link_data['document_id']);
        
        if (class_exists('SLM_Email_Templates')) {
            SLM_Email_Templates::send($email, 'document-shared', [
                'first_name' => $link_data['recipient_name'] ?: __('Recipient', 'flavor'),
                'document_name' => $document['title'],
                'portal_url' => $link_data['url'],
                'shared_by' => $link_data['created_by'],
            ]);
        }
    }
    
    /**
     * Send view notification email
     */
    private static function send_view_notification($link_id) {
        $link = get_post($link_id);
        $owner = get_userdata($link->post_author);
        
        if (!$owner) {
            return;
        }
        
        $link_data = self::get_link_data($link_id);
        $document = SLM_DMS_Documents::get_document($link_data['document_id']);
        
        $subject = sprintf(__('Document Viewed: %s', 'flavor'), $document['title']);
        $message = sprintf(
            __('Your shared document "%s" has been viewed by %s.', 'flavor'),
            $document['title'],
            $link_data['recipient_name'] ?: __('the recipient', 'flavor')
        );
        
        wp_mail($owner->user_email, $subject, $message);
    }
    
    /**
     * Get client IP
     */
    private static function get_client_ip() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
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
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'slm_share_details',
            __('Share Link Details', 'flavor'),
            [__CLASS__, 'render_details_meta_box'],
            'slm_share_link',
            'normal',
            'high'
        );
        
        add_meta_box(
            'slm_share_access_log',
            __('Access Log', 'flavor'),
            [__CLASS__, 'render_access_log_meta_box'],
            'slm_share_link',
            'normal',
            'default'
        );
    }
    
    /**
     * Render details meta box
     */
    public static function render_details_meta_box($post) {
        $data = self::get_link_data($post->ID);
        $document = SLM_DMS_Documents::get_document($data['document_id']);
        
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Document', 'flavor'); ?></th>
                <td>
                    <a href="<?php echo esc_url(get_edit_post_link($data['document_id'])); ?>">
                        <?php echo esc_html($document['title']); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Share URL', 'flavor'); ?></th>
                <td>
                    <code style="word-break: break-all;"><?php echo esc_html($data['url']); ?></code>
                    <button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js($data['url']); ?>')">
                        <?php esc_html_e('Copy', 'flavor'); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Status', 'flavor'); ?></th>
                <td>
                    <span class="status-<?php echo esc_attr($data['status']); ?>">
                        <?php echo esc_html(ucfirst($data['status'])); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Expires', 'flavor'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($data['expiry_date']))); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Views', 'flavor'); ?></th>
                <td>
                    <?php echo intval($data['view_count']); ?>
                    <?php if ($data['max_views'] > 0): ?>
                        / <?php echo intval($data['max_views']); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Password Protected', 'flavor'); ?></th>
                <td><?php echo $data['has_password'] ? __('Yes', 'flavor') : __('No', 'flavor'); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Download Allowed', 'flavor'); ?></th>
                <td><?php echo $data['download_allowed'] ? __('Yes', 'flavor') : __('No', 'flavor'); ?></td>
            </tr>
            <?php if ($data['recipient_name'] || $data['recipient_email']): ?>
            <tr>
                <th><?php esc_html_e('Recipient', 'flavor'); ?></th>
                <td>
                    <?php echo esc_html($data['recipient_name']); ?>
                    <?php if ($data['recipient_email']): ?>
                        (<?php echo esc_html($data['recipient_email']); ?>)
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    /**
     * Render access log meta box
     */
    public static function render_access_log_meta_box($post) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('share_access_log');
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE share_link_id = %d ORDER BY accessed_at DESC LIMIT 50",
            $post->ID
        ));
        
        if (empty($logs)) {
            echo '<p>' . esc_html__('No access records yet.', 'flavor') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Date', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Type', 'flavor') . '</th>';
        echo '<th>' . esc_html__('IP Address', 'flavor') . '</th>';
        echo '<th>' . esc_html__('Status', 'flavor') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->accessed_at))) . '</td>';
            echo '<td>' . esc_html(ucfirst($log->access_type)) . '</td>';
            echo '<td>' . esc_html($log->ip_address) . '</td>';
            echo '<td>' . ($log->success ? '✓' : '✗') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * AJAX: Create share link
     */
    public static function ajax_create_link() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'flavor')]);
        }
        
        $document_id = intval($_POST['document_id'] ?? 0);
        
        $result = self::create_link($document_id, [
            'expiry_hours' => intval($_POST['expiry_hours'] ?? self::DEFAULT_EXPIRY_HOURS),
            'password' => sanitize_text_field($_POST['password'] ?? ''),
            'download_allowed' => isset($_POST['download_allowed']) && $_POST['download_allowed'] === 'true',
            'max_views' => intval($_POST['max_views'] ?? 0),
            'recipient_name' => sanitize_text_field($_POST['recipient_name'] ?? ''),
            'recipient_email' => sanitize_email($_POST['recipient_email'] ?? ''),
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Revoke share link
     */
    public static function ajax_revoke_link() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $link_id = intval($_POST['link_id'] ?? 0);
        
        $result = self::revoke_link($link_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Link revoked.', 'flavor')]);
    }
    
    /**
     * AJAX: Get share links
     */
    public static function ajax_get_links() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        
        $document_id = intval($_POST['document_id'] ?? 0);
        
        wp_send_json_success(['links' => self::get_document_links($document_id)]);
    }
    
    /**
     * AJAX: Verify password (no-priv)
     */
    public static function ajax_verify_password() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'slm_share_password')) {
            wp_die(__('Security check failed.', 'flavor'));
        }
        
        $token = sanitize_text_field($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $validation = self::validate_link($token);
        
        if (is_wp_error($validation)) {
            wp_redirect(home_url('/shared-document/' . $token . '/?error=invalid'));
            exit;
        }
        
        if (self::verify_password($validation['link_id'], $password)) {
            // Start session if not started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['slm_share_' . $token] = true;
            
            // Record successful password entry
            self::record_access($validation['link_id'], 'password_attempt', true);
            
            wp_redirect(home_url('/shared-document/' . $token . '/'));
        } else {
            // Record failed attempt
            self::record_access($validation['link_id'], 'password_attempt', false);
            
            wp_redirect(home_url('/shared-document/' . $token . '/?error=password'));
        }
        
        exit;
    }
}
