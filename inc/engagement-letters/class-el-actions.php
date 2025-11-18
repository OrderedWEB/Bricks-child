<?php
/**
 * Engagement Letters Actions Handler
 * 
 * Handles AJAX actions for engagement letters including copy/duplicate,
 * delete, send email, and other interactive operations.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EL_Actions {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
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
        // AJAX handlers
        add_action('wp_ajax_el_copy_letter', [$this, 'ajax_copy_letter']);
        add_action('wp_ajax_el_delete_letter', [$this, 'ajax_delete_letter']);
        add_action('wp_ajax_el_send_signature_email', [$this, 'ajax_send_signature_email']);
        add_action('wp_ajax_el_resend_signature_link', [$this, 'ajax_resend_signature_link']);
        add_action('wp_ajax_el_download_pdf', [$this, 'ajax_download_pdf']);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'el-actions',
            get_stylesheet_directory_uri() . '/inc/engagement-letters/js/el-actions.js',
            ['jquery'],
            '1.0.0',
            true
        );
        
        wp_localize_script('el-actions', 'elActions', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('el_actions'),
            'strings' => [
                'confirmDelete' => 'Are you sure you want to delete this engagement letter? This action cannot be undone.',
                'confirmCancel' => 'Are you sure you want to cancel this engagement letter?',
                'copying' => 'Creating copy...',
                'deleting' => 'Deleting...',
                'sending' => 'Sending...',
                'success' => 'Action completed successfully',
                'error' => 'An error occurred. Please try again.',
                'copied' => 'Engagement letter copied successfully!',
                'deleted' => 'Engagement letter deleted',
                'emailSent' => 'Email sent successfully'
            ]
        ]);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            global $post;
            if ($post && $post->post_type === 'engagement_letter') {
                $this->enqueue_scripts();
            }
        }
    }
    
    /**
     * AJAX: Copy/duplicate engagement letter
     */
    public function ajax_copy_letter() {
        check_ajax_referer('el_actions', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $original_id = absint($_POST['post_id']);
        $original = get_post($original_id);
        
        if (!$original || $original->post_type !== 'engagement_letter') {
            wp_send_json_error(['message' => 'Invalid engagement letter']);
        }
        
        // Get original data
        $original_data = EL_Core::get_el_data($original_id);
        
        // Create new post
        $new_post = [
            'post_title' => $original->post_title . ' (Copy)',
            'post_type' => 'engagement_letter',
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ];
        
        $new_id = wp_insert_post($new_post);
        
        if (is_wp_error($new_id)) {
            wp_send_json_error(['message' => 'Failed to create copy']);
        }
        
        // Copy meta data
        $meta_keys = [
            '_el_client_id',
            '_el_template_id',
            '_el_matter_reference'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $value = get_post_meta($original_id, $meta_key, true);
            if ($value) {
                update_post_meta($new_id, $meta_key, $value);
            }
        }
        
        // Copy cart state
        $cart_state = get_post_meta($original_id, '_el_cart_state', true);
        if ($cart_state) {
            update_post_meta($new_id, '_el_cart_state', $cart_state);
        }
        
        // Copy form data
        $form_data = get_post_meta($original_id, '_el_form_data', true);
        if ($form_data) {
            update_post_meta($new_id, '_el_form_data', $form_data);
        }
        
        // Set status to draft
        wp_set_object_terms($new_id, 'draft', 'el_status');
        
        // Create new order for the copy
        $client_id = get_post_meta($new_id, '_el_client_id', true);
        $template_id = get_post_meta($new_id, '_el_template_id', true);
        
        if ($client_id && $template_id) {
            do_action('save_post_engagement_letter', $new_id, get_post($new_id), false);
        }
        
        wp_send_json_success([
            'message' => 'Engagement letter copied successfully',
            'new_id' => $new_id,
            'edit_url' => admin_url('post.php?post=' . $new_id . '&action=edit'),
            'title' => get_the_title($new_id)
        ]);
    }
    
    /**
     * AJAX: Delete engagement letter
     */
    public function ajax_delete_letter() {
        check_ajax_referer('el_actions', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $post_id = absint($_POST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'engagement_letter') {
            wp_send_json_error(['message' => 'Invalid engagement letter']);
        }
        
        // Get order ID before deletion
        $order_id = get_post_meta($post_id, '_el_order_id', true);
        
        // Permanently delete or move to trash based on parameter
        $force_delete = isset($_POST['force_delete']) && $_POST['force_delete'] === 'true';
        
        if ($force_delete) {
            $deleted = wp_delete_post($post_id, true);
        } else {
            $deleted = wp_trash_post($post_id);
        }
        
        if (!$deleted) {
            wp_send_json_error(['message' => 'Failed to delete engagement letter']);
        }
        
        // Add note to order if exists
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->add_order_note(
                    sprintf(
                        'Linked Engagement Letter #%d was %s',
                        $post_id,
                        $force_delete ? 'permanently deleted' : 'moved to trash'
                    )
                );
            }
        }
        
        wp_send_json_success([
            'message' => $force_delete ? 'Engagement letter deleted permanently' : 'Engagement letter moved to trash',
            'deleted_id' => $post_id
        ]);
    }
    
    /**
     * AJAX: Send signature email to client
     */
    public function ajax_send_signature_email() {
        check_ajax_referer('el_actions', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $post_id = absint($_POST['post_id']);
        $el_data = EL_Core::get_el_data($post_id);
        
        if (!$el_data) {
            wp_send_json_error(['message' => 'Invalid engagement letter']);
        }
        
        // Check if already sent
        $current_status = $el_data['status'];
        if ($current_status === 'sent_for_signature') {
            wp_send_json_error([
                'message' => 'Signature email already sent. Use "Resend Link" to send again.'
            ]);
        }
        
        // Verify status is ready_to_send
        if ($current_status !== 'ready_to_send') {
            wp_send_json_error([
                'message' => 'Engagement letter must be in "Ready to Send" status'
            ]);
        }
        
        // Get client
        $client = get_user_by('id', $el_data['client_id']);
        if (!$client) {
            wp_send_json_error(['message' => 'Client not found']);
        }
        
        // Generate signature link (unique token)
        $signature_token = wp_generate_password(32, false);
        update_post_meta($post_id, '_el_signature_token', $signature_token);
        
        $signature_url = add_query_arg([
            'el_id' => $post_id,
            'token' => $signature_token
        ], home_url('/engagement-letter-sign/'));
        
        // Send email
        $sent = $this->send_signature_email_to_client($post_id, $client, $signature_url);
        
        if (!$sent) {
            wp_send_json_error(['message' => 'Failed to send email']);
        }
        
        // Update status
        wp_set_object_terms($post_id, 'sent_for_signature', 'el_status');
        update_post_meta($post_id, '_el_sent_date', time());
        
        // Set expiry (30 days)
        $expires = strtotime('+30 days');
        update_post_meta($post_id, '_el_expires_date', $expires);
        
        // Add note to order
        $order_id = $el_data['order_id'];
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->add_order_note(
                    sprintf(
                        'Engagement letter signature link sent to %s (%s)',
                        $client->display_name,
                        $client->user_email
                    )
                );
            }
        }
        
        wp_send_json_success([
            'message' => 'Signature email sent successfully',
            'sent_date' => time(),
            'expires_date' => $expires,
            'new_status' => 'sent_for_signature'
        ]);
    }
    
    /**
     * AJAX: Resend signature link
     */
    public function ajax_resend_signature_link() {
        check_ajax_referer('el_actions', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $post_id = absint($_POST['post_id']);
        $el_data = EL_Core::get_el_data($post_id);
        
        if (!$el_data) {
            wp_send_json_error(['message' => 'Invalid engagement letter']);
        }
        
        // Get client
        $client = get_user_by('id', $el_data['client_id']);
        if (!$client) {
            wp_send_json_error(['message' => 'Client not found']);
        }
        
        // Generate new signature token
        $signature_token = wp_generate_password(32, false);
        update_post_meta($post_id, '_el_signature_token', $signature_token);
        
        $signature_url = add_query_arg([
            'el_id' => $post_id,
            'token' => $signature_token
        ], home_url('/engagement-letter-sign/'));
        
        // Reset expiry (30 days from now)
        $expires = strtotime('+30 days');
        update_post_meta($post_id, '_el_expires_date', $expires);
        
        // Clear reminder sent flag
        delete_post_meta($post_id, '_el_reminder_sent');
        
        // If expired, set back to sent_for_signature
        if ($el_data['status'] === 'expired') {
            wp_set_object_terms($post_id, 'sent_for_signature', 'el_status');
        }
        
        // Send email
        $sent = $this->send_signature_email_to_client($post_id, $client, $signature_url);
        
        if (!$sent) {
            wp_send_json_error(['message' => 'Failed to send email']);
        }
        
        // Update sent date
        update_post_meta($post_id, '_el_sent_date', time());
        
        wp_send_json_success([
            'message' => 'Signature link resent successfully',
            'sent_date' => time(),
            'expires_date' => $expires
        ]);
    }
    
    /**
     * Send signature email to client
     */
    private function send_signature_email_to_client($el_id, $client, $signature_url) {
        $el_data = EL_Core::get_el_data($el_id);
        $lawyer = get_user_by('id', $el_data['author_id']);
        
        $to = $client->user_email;
        $subject = sprintf('Action Required: Sign Your Engagement Letter - %s', $el_data['title']);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1e293b; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: white; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px; }
                .alert { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 6px; }
                .details { background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .details-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .details-row:last-child { border-bottom: none; }
                .label { font-weight: 600; color: #64748b; }
                .value { color: #1e293b; }
                .button { display: inline-block; background: #667eea; color: white; padding: 16px 32px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; font-size: 16px; }
                .expiry-notice { background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 6px; margin: 20px 0; font-size: 14px; text-align: center; color: #991b1b; }
                .footer { text-align: center; padding: 20px; color: #64748b; font-size: 14px; border-top: 1px solid #e2e8f0; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✍️ Please Sign Your Engagement Letter</h1>
                </div>
                <div class="content">
                    <p>Dear <?php echo esc_html($client->display_name); ?>,</p>
                    
                    <div class="alert">
                        <strong>📄 Your Signature is Required</strong><br>
                        Please review and electronically sign your engagement letter to proceed with our legal services.
                    </div>
                    
                    <div class="details">
                        <div class="details-row">
                            <span class="label">Engagement Letter:</span>
                            <span class="value"><?php echo esc_html($el_data['title']); ?></span>
                        </div>
                        <?php if ($el_data['matter_ref']): ?>
                        <div class="details-row">
                            <span class="label">Matter Reference:</span>
                            <span class="value"><?php echo esc_html($el_data['matter_ref']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($lawyer): ?>
                        <div class="details-row">
                            <span class="label">Your Lawyer:</span>
                            <span class="value"><?php echo esc_html($lawyer->display_name); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <p><strong>What to expect:</strong></p>
                    <ul>
                        <li>Click the button below to review the engagement letter</li>
                        <li>Read through all terms and conditions carefully</li>
                        <li>Use your mouse or touchscreen to sign electronically</li>
                        <li>You'll receive a signed copy via email immediately</li>
                    </ul>
                    
                    <center>
                        <a href="<?php echo esc_url($signature_url); ?>" class="button">
                            Review & Sign Document →
                        </a>
                    </center>
                    
                    <div class="expiry-notice">
                        ⏰ <strong>Note:</strong> This signature link will expire in 30 days (<?php echo date('F j, Y', strtotime('+30 days')); ?>)
                    </div>
                    
                    <p style="font-size: 14px; color: #64748b; margin-top: 30px;">
                        <strong>Need help?</strong> If you have any questions or encounter any issues, 
                        please contact <?php echo $lawyer ? esc_html($lawyer->display_name) : 'our office'; ?> 
                        at <?php echo $lawyer ? esc_html($lawyer->user_email) : get_option('admin_email'); ?>.
                    </p>
                </div>
                <div class="footer">
                    <p>This email was sent from <?php echo esc_html(get_bloginfo('name')); ?></p>
                    <p style="font-size: 12px;">Please do not reply to this automated email.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $message = ob_get_clean();
        
        // Send email
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        if ($lawyer) {
            $headers[] = 'Reply-To: ' . $lawyer->display_name . ' <' . $lawyer->user_email . '>';
        }
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * AJAX: Download PDF
     */
    public function ajax_download_pdf() {
        check_ajax_referer('el_actions', 'nonce');
        
        $post_id = absint($_POST['post_id']);
        $el_data = EL_Core::get_el_data($post_id);
        
        if (!$el_data) {
            wp_send_json_error(['message' => 'Invalid engagement letter']);
        }
        
        // Check permissions
        if (!current_user_can('edit_posts') && get_current_user_id() != $el_data['client_id']) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        // Get PDF URL from meta
        $pdf_url = get_post_meta($post_id, '_el_pdf_url', true);
        
        if (!$pdf_url) {
            wp_send_json_error(['message' => 'PDF not yet generated']);
        }
        
        wp_send_json_success([
            'pdf_url' => $pdf_url,
            'filename' => sanitize_file_name($el_data['title']) . '.pdf'
        ]);
    }
    
    /**
     * Get action buttons HTML
     */
    public static function get_action_buttons($el_id, $context = 'list') {
        $el_data = EL_Core::get_el_data($el_id);
        
        if (!$el_data) {
            return '';
        }
        
        $status = $el_data['status'];
        $is_favorite = $el_data['is_favorite'];
        
        ob_start();
        ?>
        <div class="el-actions" data-el-id="<?php echo $el_id; ?>">
            <!-- Favorite Toggle -->
            <button 
                class="el-action-btn el-favorite-btn <?php echo $is_favorite ? 'is-favorite' : ''; ?>"
                data-action="toggle_favorite"
                aria-label="<?php echo $is_favorite ? 'Remove from favorites' : 'Add to favorites'; ?>"
                title="<?php echo $is_favorite ? 'Remove from favorites' : 'Add to favorites'; ?>">
                <span class="icon"><?php echo $is_favorite ? '⭐' : '☆'; ?></span>
            </button>
            
            <!-- Edit -->
            <a 
                href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>"
                class="el-action-btn"
                aria-label="Edit"
                title="Edit">
                <span class="icon">✏️</span>
            </a>
            
            <!-- Copy -->
            <button 
                class="el-action-btn"
                data-action="copy"
                aria-label="Create copy"
                title="Create copy">
                <span class="icon">📋</span>
            </button>
            
            <!-- Send/Resend -->
            <?php if ($status === 'ready_to_send'): ?>
            <button 
                class="el-action-btn el-send-btn"
                data-action="send_signature"
                aria-label="Send for signature"
                title="Send for signature">
                <span class="icon">📧</span>
            </button>
            <?php elseif (in_array($status, ['sent_for_signature', 'expired'])): ?>
            <button 
                class="el-action-btn"
                data-action="resend_signature"
                aria-label="Resend signature link"
                title="Resend signature link">
                <span class="icon">🔄</span>
            </button>
            <?php endif; ?>
            
            <!-- Download PDF -->
            <?php if (in_array($status, ['signed', 'paid', 'completed'])): ?>
            <button 
                class="el-action-btn"
                data-action="download_pdf"
                aria-label="Download PDF"
                title="Download PDF">
                <span class="icon">⬇️</span>
            </button>
            <?php endif; ?>
            
            <!-- Delete -->
            <button 
                class="el-action-btn el-delete-btn"
                data-action="delete"
                aria-label="Delete"
                title="Delete">
                <span class="icon">🗑️</span>
            </button>
        </div>
        
        <style>
            .el-actions {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .el-action-btn {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 8px;
                cursor: pointer;
                transition: all 0.2s;
                font-size: 16px;
                line-height: 1;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .el-action-btn:hover {
                background: #f1f5f9;
                border-color: #cbd5e1;
                transform: translateY(-1px);
            }
            .el-action-btn:active {
                transform: translateY(0);
            }
            .el-favorite-btn.is-favorite {
                background: #fef3c7;
                border-color: #fbbf24;
            }
            .el-send-btn {
                background: #eff6ff;
                border-color: #3b82f6;
            }
            .el-delete-btn:hover {
                background: #fef2f2;
                border-color: #ef4444;
            }
            .el-action-btn.loading {
                opacity: 0.6;
                pointer-events: none;
            }
            .el-action-btn.loading .icon {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

// Initialize
EL_Actions::get_instance();