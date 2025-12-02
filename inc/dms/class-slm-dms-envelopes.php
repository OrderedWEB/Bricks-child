<?php
/**
 * SLM DMS Envelopes
 * 
 * DocuSign-style document signing:
 * - Multiple signers (sequential or parallel)
 * - Field placement (signature, initials, date, text)
 * - Signature capture (drawn or typed)
 * - Certificate of signing
 * - Email notifications
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_Envelopes {
    
    /**
     * Default expiry (14 days)
     */
    const DEFAULT_EXPIRY_DAYS = 14;
    
    /**
     * Reminder days before expiry
     */
    const REMINDER_DAYS = [7, 3, 1];
    
    /**
     * Signing token length
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * Envelope statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_WAITING = 'waiting';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DECLINED = 'declined';
    const STATUS_EXPIRED = 'expired';
    const STATUS_VOIDED = 'voided';
    
    /**
     * Field types
     */
    const FIELD_SIGNATURE = 'signature';
    const FIELD_INITIALS = 'initials';
    const FIELD_DATE = 'date';
    const FIELD_TEXT = 'text';
    const FIELD_CHECKBOX = 'checkbox';
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('wp_ajax_slm_create_envelope', [__CLASS__, 'ajax_create_envelope']);
        add_action('wp_ajax_slm_send_envelope', [__CLASS__, 'ajax_send_envelope']);
        add_action('wp_ajax_slm_add_signer', [__CLASS__, 'ajax_add_signer']);
        add_action('wp_ajax_slm_add_field', [__CLASS__, 'ajax_add_field']);
        add_action('wp_ajax_slm_void_envelope', [__CLASS__, 'ajax_void_envelope']);
        add_action('wp_ajax_nopriv_slm_submit_signature', [__CLASS__, 'ajax_submit_signature']);
        add_action('wp_ajax_slm_submit_signature', [__CLASS__, 'ajax_submit_signature']);
        
        // Meta boxes
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        
        // Cron for reminders
        add_action('slm_envelope_reminders', [__CLASS__, 'send_reminders']);
        
        if (!wp_next_scheduled('slm_envelope_reminders')) {
            wp_schedule_event(time(), 'daily', 'slm_envelope_reminders');
        }
    }
    
    /**
     * Create envelope
     */
    public static function create_envelope($document_id, $args = []) {
        $defaults = [
            'title' => '',
            'message' => '',
            'signing_mode' => 'sequential', // sequential or parallel
            'expiry_days' => self::DEFAULT_EXPIRY_DAYS,
            'created_by' => get_current_user_id(),
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate document
        $document = get_post($document_id);
        if (!$document || $document->post_type !== 'slm_document') {
            return new WP_Error('invalid_document', __('Invalid document.', 'flavor'));
        }
        
        // Generate title
        $title = !empty($args['title']) ? $args['title'] : sprintf(
            __('Sign: %s', 'flavor'),
            $document->post_title
        );
        
        // Create envelope post
        $post_id = wp_insert_post([
            'post_type' => 'slm_envelope',
            'post_title' => $title,
            'post_content' => sanitize_textarea_field($args['message']),
            'post_status' => 'publish',
            'post_author' => $args['created_by'],
        ]);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Calculate expiry
        $expiry_date = date('Y-m-d H:i:s', strtotime('+' . intval($args['expiry_days']) . ' days'));
        
        // Store metadata
        update_post_meta($post_id, '_slm_document_id', $document_id);
        update_post_meta($post_id, '_slm_signing_mode', $args['signing_mode']);
        update_post_meta($post_id, '_slm_expiry_date', $expiry_date);
        update_post_meta($post_id, '_slm_status', self::STATUS_DRAFT);
        update_post_meta($post_id, '_slm_signers', []);
        update_post_meta($post_id, '_slm_current_signer_index', 0);
        
        SLM_DMS::log('Envelope created: ' . $post_id . ' for document ' . $document_id);
        
        return $post_id;
    }
    
    /**
     * Add signer to envelope
     */
    public static function add_signer($envelope_id, $signer_data) {
        $envelope = get_post($envelope_id);
        
        if (!$envelope || $envelope->post_type !== 'slm_envelope') {
            return new WP_Error('invalid_envelope', __('Invalid envelope.', 'flavor'));
        }
        
        $status = get_post_meta($envelope_id, '_slm_status', true);
        if ($status !== self::STATUS_DRAFT) {
            return new WP_Error('not_draft', __('Cannot modify sent envelope.', 'flavor'));
        }
        
        $signers = get_post_meta($envelope_id, '_slm_signers', true) ?: [];
        
        $signer = [
            'index' => count($signers),
            'name' => sanitize_text_field($signer_data['name']),
            'email' => sanitize_email($signer_data['email']),
            'user_id' => isset($signer_data['user_id']) ? intval($signer_data['user_id']) : 0,
            'role' => sanitize_text_field($signer_data['role'] ?? 'signer'),
            'signing_token' => wp_generate_password(self::TOKEN_LENGTH, false),
            'status' => 'pending',
            'signed_at' => null,
            'ip_address' => null,
        ];
        
        $signers[] = $signer;
        update_post_meta($envelope_id, '_slm_signers', $signers);
        
        return count($signers) - 1;
    }
    
    /**
     * Add field to envelope
     */
    public static function add_field($envelope_id, $field_data) {
        global $wpdb;
        
        $envelope = get_post($envelope_id);
        
        if (!$envelope || $envelope->post_type !== 'slm_envelope') {
            return new WP_Error('invalid_envelope', __('Invalid envelope.', 'flavor'));
        }
        
        $status = get_post_meta($envelope_id, '_slm_status', true);
        if ($status !== self::STATUS_DRAFT) {
            return new WP_Error('not_draft', __('Cannot modify sent envelope.', 'flavor'));
        }
        
        $table = SLM_DMS::get_table('signing_fields');
        
        $wpdb->insert($table, [
            'envelope_id' => $envelope_id,
            'signer_index' => intval($field_data['signer_index']),
            'field_type' => sanitize_text_field($field_data['field_type']),
            'page_number' => intval($field_data['page_number']),
            'x_position' => floatval($field_data['x_position']),
            'y_position' => floatval($field_data['y_position']),
            'width' => floatval($field_data['width'] ?? 200),
            'height' => floatval($field_data['height'] ?? 50),
            'required' => isset($field_data['required']) ? 1 : 1,
            'placeholder' => sanitize_text_field($field_data['placeholder'] ?? ''),
        ]);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get envelope fields
     */
    public static function get_fields($envelope_id, $signer_index = null) {
        global $wpdb;
        
        $table = SLM_DMS::get_table('signing_fields');
        
        if ($signer_index !== null) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE envelope_id = %d AND signer_index = %d ORDER BY page_number, y_position",
                $envelope_id,
                $signer_index
            ));
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE envelope_id = %d ORDER BY page_number, y_position",
            $envelope_id
        ));
    }
    
    /**
     * Send envelope for signing
     */
    public static function send_envelope($envelope_id) {
        $envelope = get_post($envelope_id);
        
        if (!$envelope || $envelope->post_type !== 'slm_envelope') {
            return new WP_Error('invalid_envelope', __('Invalid envelope.', 'flavor'));
        }
        
        $status = get_post_meta($envelope_id, '_slm_status', true);
        if ($status !== self::STATUS_DRAFT) {
            return new WP_Error('not_draft', __('Envelope already sent.', 'flavor'));
        }
        
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        if (empty($signers)) {
            return new WP_Error('no_signers', __('Add at least one signer.', 'flavor'));
        }
        
        $fields = self::get_fields($envelope_id);
        if (empty($fields)) {
            return new WP_Error('no_fields', __('Add at least one signature field.', 'flavor'));
        }
        
        // Update status
        update_post_meta($envelope_id, '_slm_status', self::STATUS_SENT);
        update_post_meta($envelope_id, '_slm_sent_at', current_time('mysql'));
        
        // Send notification to first signer (or all if parallel)
        $signing_mode = get_post_meta($envelope_id, '_slm_signing_mode', true);
        
        if ($signing_mode === 'parallel') {
            foreach ($signers as $index => $signer) {
                self::send_signing_request($envelope_id, $index);
            }
        } else {
            self::send_signing_request($envelope_id, 0);
            update_post_meta($envelope_id, '_slm_status', self::STATUS_WAITING);
        }
        
        SLM_DMS::log('Envelope sent: ' . $envelope_id);
        
        return true;
    }
    
    /**
     * Send signing request email
     */
    private static function send_signing_request($envelope_id, $signer_index) {
        $envelope = get_post($envelope_id);
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $document = SLM_DMS_Documents::get_document($document_id);
        
        if (!isset($signers[$signer_index])) {
            return false;
        }
        
        $signer = $signers[$signer_index];
        $sign_url = home_url('/sign-document/' . $signer['signing_token'] . '/');
        
        // Update signer status
        $signers[$signer_index]['status'] = 'sent';
        $signers[$signer_index]['sent_at'] = current_time('mysql');
        update_post_meta($envelope_id, '_slm_signers', $signers);
        
        // Send email
        if (class_exists('SLM_Email_Templates')) {
            SLM_Email_Templates::send($signer['email'], 'signing-request', [
                'first_name' => $signer['name'],
                'document_name' => $document['title'],
                'sign_url' => $sign_url,
                'message' => $envelope->post_content,
                'expiry_date' => get_post_meta($envelope_id, '_slm_expiry_date', true),
            ]);
        } else {
            $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
            $subject = sprintf(__('%s: Please sign %s', 'flavor'), $firm_name, $document['title']);
            $message = sprintf(
                __("Hello %s,\n\nYou have been requested to sign the document \"%s\".\n\nPlease click here to sign: %s\n\n%s", 'flavor'),
                $signer['name'],
                $document['title'],
                $sign_url,
                $envelope->post_content
            );
            
            wp_mail($signer['email'], $subject, $message);
        }
        
        SLM_DMS::log('Signing request sent to ' . $signer['email'] . ' for envelope ' . $envelope_id);
        
        return true;
    }
    
    /**
     * Validate signing token
     */
    public static function validate_signing_token($token) {
        $envelopes = get_posts([
            'post_type' => 'slm_envelope',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($envelopes as $envelope) {
            $signers = get_post_meta($envelope->ID, '_slm_signers', true);
            
            if (!is_array($signers)) {
                continue;
            }
            
            foreach ($signers as $index => $signer) {
                if ($signer['signing_token'] === $token) {
                    // Validate envelope status
                    $status = get_post_meta($envelope->ID, '_slm_status', true);
                    
                    if ($status === self::STATUS_COMPLETED) {
                        return new WP_Error('completed', __('This document has already been fully signed.', 'flavor'));
                    }
                    
                    if ($status === self::STATUS_VOIDED) {
                        return new WP_Error('voided', __('This signing request has been cancelled.', 'flavor'));
                    }
                    
                    if ($status === self::STATUS_EXPIRED) {
                        return new WP_Error('expired', __('This signing request has expired.', 'flavor'));
                    }
                    
                    // Check expiry
                    $expiry = get_post_meta($envelope->ID, '_slm_expiry_date', true);
                    if ($expiry && strtotime($expiry) < time()) {
                        update_post_meta($envelope->ID, '_slm_status', self::STATUS_EXPIRED);
                        return new WP_Error('expired', __('This signing request has expired.', 'flavor'));
                    }
                    
                    // Check if already signed
                    if ($signer['status'] === 'signed') {
                        return new WP_Error('already_signed', __('You have already signed this document.', 'flavor'));
                    }
                    
                    // Check if it's this signer's turn (for sequential)
                    $signing_mode = get_post_meta($envelope->ID, '_slm_signing_mode', true);
                    $current_index = get_post_meta($envelope->ID, '_slm_current_signer_index', true);
                    
                    if ($signing_mode === 'sequential' && $index > $current_index) {
                        return new WP_Error('not_your_turn', __('Waiting for previous signers to complete.', 'flavor'));
                    }
                    
                    return [
                        'envelope_id' => $envelope->ID,
                        'signer_index' => $index,
                        'signer' => $signer,
                        'document_id' => get_post_meta($envelope->ID, '_slm_document_id', true),
                    ];
                }
            }
        }
        
        return new WP_Error('invalid_token', __('Invalid signing link.', 'flavor'));
    }
    
    /**
     * Submit signature
     */
    public static function submit_signature($token, $signatures) {
        // Validate token
        $validation = self::validate_signing_token($token);
        
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        global $wpdb;
        
        $envelope_id = $validation['envelope_id'];
        $signer_index = $validation['signer_index'];
        $fields = self::get_fields($envelope_id, $signer_index);
        
        // Validate all required fields are signed
        foreach ($fields as $field) {
            if ($field->required && !isset($signatures[$field->id])) {
                return new WP_Error('missing_field', __('Please complete all required fields.', 'flavor'));
            }
        }
        
        // Save field values
        $table = SLM_DMS::get_table('signing_fields');
        
        foreach ($signatures as $field_id => $value) {
            $wpdb->update(
                $table,
                [
                    'field_value' => $value,
                    'signed_at' => current_time('mysql'),
                ],
                ['id' => $field_id]
            );
        }
        
        // Update signer status
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $signers[$signer_index]['status'] = 'signed';
        $signers[$signer_index]['signed_at'] = current_time('mysql');
        $signers[$signer_index]['ip_address'] = self::get_client_ip();
        update_post_meta($envelope_id, '_slm_signers', $signers);
        
        // Check if all signed
        $all_signed = true;
        foreach ($signers as $signer) {
            if ($signer['status'] !== 'signed') {
                $all_signed = false;
                break;
            }
        }
        
        if ($all_signed) {
            // Complete envelope
            self::complete_envelope($envelope_id);
        } else {
            // Send to next signer (if sequential)
            $signing_mode = get_post_meta($envelope_id, '_slm_signing_mode', true);
            
            if ($signing_mode === 'sequential') {
                $next_index = $signer_index + 1;
                update_post_meta($envelope_id, '_slm_current_signer_index', $next_index);
                
                if (isset($signers[$next_index])) {
                    self::send_signing_request($envelope_id, $next_index);
                }
            }
        }
        
        SLM_DMS::log('Signature submitted for envelope ' . $envelope_id . ' by signer ' . $signer_index);
        
        return true;
    }
    
    /**
     * Complete envelope
     */
    private static function complete_envelope($envelope_id) {
        update_post_meta($envelope_id, '_slm_status', self::STATUS_COMPLETED);
        update_post_meta($envelope_id, '_slm_completed_at', current_time('mysql'));
        
        // Generate signed document with certificate
        $signed_document_id = self::generate_signed_document($envelope_id);
        
        if (!is_wp_error($signed_document_id)) {
            update_post_meta($envelope_id, '_slm_signed_document_id', $signed_document_id);
        }
        
        // Notify all parties
        self::send_completion_notifications($envelope_id);
        
        SLM_DMS::log('Envelope completed: ' . $envelope_id);
    }
    
    /**
     * Generate signed document with certificate
     */
    private static function generate_signed_document($envelope_id) {
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $fields = self::get_fields($envelope_id);
        
        // Get original document content
        $content_data = SLM_DMS_Documents::get_document_content($document_id);
        
        if (is_wp_error($content_data)) {
            return $content_data;
        }
        
        // For now, we'll create a simple certificate page
        // In production, you'd use a PDF library to flatten signatures onto the document
        
        $certificate = self::generate_certificate($envelope_id, $signers, $fields);
        
        // Store as new version of document
        // This is a simplified implementation
        update_post_meta($envelope_id, '_slm_certificate', $certificate);
        
        return true;
    }
    
    /**
     * Generate certificate of signing
     */
    private static function generate_certificate($envelope_id, $signers, $fields) {
        $envelope = get_post($envelope_id);
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $document = SLM_DMS_Documents::get_document($document_id);
        
        $certificate = [
            'document_title' => $document['title'],
            'envelope_id' => $envelope_id,
            'completed_at' => current_time('mysql'),
            'signers' => [],
        ];
        
        foreach ($signers as $signer) {
            $certificate['signers'][] = [
                'name' => $signer['name'],
                'email' => $signer['email'],
                'signed_at' => $signer['signed_at'],
                'ip_address' => $signer['ip_address'],
            ];
        }
        
        $certificate['hash'] = hash('sha256', json_encode($certificate));
        
        return $certificate;
    }
    
    /**
     * Send completion notifications
     */
    private static function send_completion_notifications($envelope_id) {
        $envelope = get_post($envelope_id);
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $document = SLM_DMS_Documents::get_document($document_id);
        
        // Notify envelope creator
        $creator = get_userdata($envelope->post_author);
        if ($creator) {
            $subject = sprintf(__('Document Signed: %s', 'flavor'), $document['title']);
            $message = sprintf(
                __('All signers have completed signing "%s".', 'flavor'),
                $document['title']
            );
            
            wp_mail($creator->user_email, $subject, $message);
        }
        
        // Notify all signers
        foreach ($signers as $signer) {
            $subject = sprintf(__('Signing Complete: %s', 'flavor'), $document['title']);
            $message = sprintf(
                __('The document "%s" has been signed by all parties.', 'flavor'),
                $document['title']
            );
            
            wp_mail($signer['email'], $subject, $message);
        }
    }
    
    /**
     * Void envelope
     */
    public static function void_envelope($envelope_id, $reason = '') {
        $envelope = get_post($envelope_id);
        
        if (!$envelope || $envelope->post_type !== 'slm_envelope') {
            return new WP_Error('invalid_envelope', __('Invalid envelope.', 'flavor'));
        }
        
        $status = get_post_meta($envelope_id, '_slm_status', true);
        
        if ($status === self::STATUS_COMPLETED) {
            return new WP_Error('completed', __('Cannot void completed envelope.', 'flavor'));
        }
        
        update_post_meta($envelope_id, '_slm_status', self::STATUS_VOIDED);
        update_post_meta($envelope_id, '_slm_voided_at', current_time('mysql'));
        update_post_meta($envelope_id, '_slm_void_reason', sanitize_text_field($reason));
        
        // Notify signers
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $document = SLM_DMS_Documents::get_document($document_id);
        
        foreach ($signers as $signer) {
            if ($signer['status'] !== 'signed') {
                $subject = sprintf(__('Signing Request Cancelled: %s', 'flavor'), $document['title']);
                wp_mail($signer['email'], $subject, __('The signing request has been cancelled.', 'flavor'));
            }
        }
        
        SLM_DMS::log('Envelope voided: ' . $envelope_id);
        
        return true;
    }
    
    /**
     * Send reminders (cron)
     */
    public static function send_reminders() {
        $envelopes = get_posts([
            'post_type' => 'slm_envelope',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_slm_status',
                    'value' => [self::STATUS_SENT, self::STATUS_WAITING],
                    'compare' => 'IN',
                ],
            ],
        ]);
        
        foreach ($envelopes as $envelope) {
            $expiry = get_post_meta($envelope->ID, '_slm_expiry_date', true);
            $days_until_expiry = ceil((strtotime($expiry) - time()) / 86400);
            
            if (in_array($days_until_expiry, self::REMINDER_DAYS)) {
                self::send_reminder($envelope->ID, $days_until_expiry);
            }
            
            // Mark as expired
            if ($days_until_expiry < 0) {
                update_post_meta($envelope->ID, '_slm_status', self::STATUS_EXPIRED);
            }
        }
    }
    
    /**
     * Send reminder to pending signers
     */
    private static function send_reminder($envelope_id, $days_remaining) {
        $signers = get_post_meta($envelope_id, '_slm_signers', true);
        $document_id = get_post_meta($envelope_id, '_slm_document_id', true);
        $document = SLM_DMS_Documents::get_document($document_id);
        
        foreach ($signers as $signer) {
            if ($signer['status'] !== 'signed') {
                $sign_url = home_url('/sign-document/' . $signer['signing_token'] . '/');
                
                $subject = sprintf(
                    __('Reminder: Please sign %s (%d days remaining)', 'flavor'),
                    $document['title'],
                    $days_remaining
                );
                
                $message = sprintf(
                    __("Hello %s,\n\nThis is a reminder that you have a document waiting for your signature.\n\nDocument: %s\nExpires in: %d days\n\nSign here: %s", 'flavor'),
                    $signer['name'],
                    $document['title'],
                    $days_remaining,
                    $sign_url
                );
                
                wp_mail($signer['email'], $subject, $message);
            }
        }
    }
    
    /**
     * Render signing page
     */
    public static function render_signing_page($token) {
        $validation = self::validate_signing_token($token);
        
        if (is_wp_error($validation)) {
            self::render_error_page($validation->get_error_message());
            return;
        }
        
        $envelope = get_post($validation['envelope_id']);
        $document = SLM_DMS_Documents::get_document($validation['document_id']);
        $fields = self::get_fields($validation['envelope_id'], $validation['signer_index']);
        
        self::render_signing_html($envelope, $document, $validation['signer'], $fields, $token);
    }
    
    /**
     * Render signing HTML
     */
    private static function render_signing_html($envelope, $document, $signer, $fields, $token) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo get_locale(); ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php esc_html_e('Sign Document', 'flavor'); ?> - <?php echo esc_html($firm_name); ?></title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f3f4f6;
                    min-height: 100vh;
                }
                
                .signing-header {
                    background: #1e3a5f;
                    color: #fff;
                    padding: 16px 24px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                
                .signing-header h1 {
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .signer-info {
                    font-size: 14px;
                    opacity: 0.8;
                }
                
                .signing-container {
                    display: flex;
                    height: calc(100vh - 64px);
                }
                
                .document-panel {
                    flex: 1;
                    overflow: auto;
                    padding: 24px;
                    background: #e5e7eb;
                }
                
                .pdf-viewer {
                    max-width: 900px;
                    margin: 0 auto;
                }
                
                .pdf-page-container {
                    position: relative;
                    margin-bottom: 20px;
                    background: #fff;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                
                .pdf-page-container canvas {
                    display: block;
                    width: 100%;
                    height: auto;
                }
                
                .signing-field {
                    position: absolute;
                    border: 2px dashed #2563eb;
                    background: rgba(37, 99, 235, 0.1);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    color: #2563eb;
                    transition: all 0.2s;
                }
                
                .signing-field:hover {
                    background: rgba(37, 99, 235, 0.2);
                }
                
                .signing-field.signed {
                    border-color: #16a34a;
                    background: rgba(22, 163, 74, 0.1);
                }
                
                .signing-field.signed img {
                    max-width: 100%;
                    max-height: 100%;
                }
                
                .sidebar {
                    width: 350px;
                    background: #fff;
                    border-left: 1px solid #e5e7eb;
                    display: flex;
                    flex-direction: column;
                }
                
                .sidebar-header {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                }
                
                .sidebar-header h2 {
                    font-size: 16px;
                    margin-bottom: 8px;
                }
                
                .sidebar-content {
                    flex: 1;
                    overflow: auto;
                    padding: 20px;
                }
                
                .field-list {
                    list-style: none;
                }
                
                .field-item {
                    padding: 12px;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    margin-bottom: 12px;
                    cursor: pointer;
                }
                
                .field-item:hover {
                    border-color: #2563eb;
                }
                
                .field-item.completed {
                    border-color: #16a34a;
                    background: #f0fdf4;
                }
                
                .field-item-header {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-weight: 500;
                }
                
                .field-item-page {
                    font-size: 12px;
                    color: #6b7280;
                    margin-top: 4px;
                }
                
                .sidebar-footer {
                    padding: 20px;
                    border-top: 1px solid #e5e7eb;
                }
                
                .submit-btn {
                    width: 100%;
                    background: #16a34a;
                    color: #fff;
                    border: none;
                    padding: 14px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 500;
                    cursor: pointer;
                }
                
                .submit-btn:disabled {
                    background: #9ca3af;
                    cursor: not-allowed;
                }
                
                .submit-btn:not(:disabled):hover {
                    background: #15803d;
                }
                
                /* Signature modal */
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                
                .modal-overlay.active {
                    display: flex;
                }
                
                .modal {
                    background: #fff;
                    border-radius: 12px;
                    width: 500px;
                    max-width: 95%;
                }
                
                .modal-header {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                
                .modal-header h3 {
                    font-size: 18px;
                }
                
                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #6b7280;
                }
                
                .modal-body {
                    padding: 20px;
                }
                
                .signature-tabs {
                    display: flex;
                    gap: 8px;
                    margin-bottom: 16px;
                }
                
                .signature-tab {
                    flex: 1;
                    padding: 10px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    background: #fff;
                    cursor: pointer;
                    text-align: center;
                    font-weight: 500;
                }
                
                .signature-tab.active {
                    border-color: #2563eb;
                    background: #eff6ff;
                }
                
                .signature-canvas-container {
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    margin-bottom: 16px;
                }
                
                .signature-canvas {
                    width: 100%;
                    height: 150px;
                    cursor: crosshair;
                }
                
                .typed-signature {
                    display: none;
                }
                
                .typed-signature.active {
                    display: block;
                }
                
                .typed-signature input {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 16px;
                    margin-bottom: 8px;
                }
                
                .typed-preview {
                    font-family: 'Brush Script MT', cursive;
                    font-size: 32px;
                    color: #1e3a5f;
                    text-align: center;
                    padding: 20px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    min-height: 80px;
                }
                
                .modal-footer {
                    padding: 20px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    gap: 12px;
                    justify-content: flex-end;
                }
                
                .btn {
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                }
                
                .btn-secondary {
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    color: #374151;
                }
                
                .btn-primary {
                    background: #2563eb;
                    border: none;
                    color: #fff;
                }
                
                .btn-primary:hover {
                    background: #1d4ed8;
                }
                
                @media (max-width: 768px) {
                    .signing-container {
                        flex-direction: column;
                    }
                    
                    .sidebar {
                        width: 100%;
                        max-height: 300px;
                    }
                }
            </style>
        </head>
        <body>
            <header class="signing-header">
                <h1><?php echo esc_html($document['title']); ?></h1>
                <span class="signer-info"><?php printf(esc_html__('Signing as: %s', 'flavor'), esc_html($signer['name'])); ?></span>
            </header>
            
            <div class="signing-container">
                <div class="document-panel">
                    <div class="pdf-viewer" id="pdf-viewer"></div>
                </div>
                
                <aside class="sidebar">
                    <div class="sidebar-header">
                        <h2><?php esc_html_e('Required Fields', 'flavor'); ?></h2>
                        <p><?php esc_html_e('Click each field to sign', 'flavor'); ?></p>
                    </div>
                    
                    <div class="sidebar-content">
                        <ul class="field-list" id="field-list">
                            <?php foreach ($fields as $field): ?>
                            <li class="field-item" data-field-id="<?php echo esc_attr($field->id); ?>" data-page="<?php echo esc_attr($field->page_number); ?>">
                                <div class="field-item-header">
                                    <span class="field-icon">
                                        <?php echo $field->field_type === 'signature' ? '✍️' : ($field->field_type === 'date' ? '📅' : '✏️'); ?>
                                    </span>
                                    <span><?php echo esc_html(ucfirst($field->field_type)); ?></span>
                                    <?php if ($field->required): ?>
                                    <span style="color: #dc2626;">*</span>
                                    <?php endif; ?>
                                </div>
                                <div class="field-item-page">
                                    <?php printf(esc_html__('Page %d', 'flavor'), $field->page_number); ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="sidebar-footer">
                        <button class="submit-btn" id="submit-btn" disabled>
                            <?php esc_html_e('Submit Signatures', 'flavor'); ?>
                        </button>
                    </div>
                </aside>
            </div>
            
            <!-- Signature Modal -->
            <div class="modal-overlay" id="signature-modal">
                <div class="modal">
                    <div class="modal-header">
                        <h3><?php esc_html_e('Add Your Signature', 'flavor'); ?></h3>
                        <button class="modal-close" id="modal-close">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="signature-tabs">
                            <button class="signature-tab active" data-tab="draw"><?php esc_html_e('Draw', 'flavor'); ?></button>
                            <button class="signature-tab" data-tab="type"><?php esc_html_e('Type', 'flavor'); ?></button>
                        </div>
                        
                        <div class="signature-canvas-container" id="draw-panel">
                            <canvas class="signature-canvas" id="signature-canvas"></canvas>
                        </div>
                        
                        <div class="typed-signature" id="type-panel">
                            <input type="text" id="typed-name" placeholder="<?php esc_attr_e('Type your name', 'flavor'); ?>">
                            <div class="typed-preview" id="typed-preview"></div>
                        </div>
                        
                        <button class="btn btn-secondary" id="clear-signature"><?php esc_html_e('Clear', 'flavor'); ?></button>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" id="cancel-signature"><?php esc_html_e('Cancel', 'flavor'); ?></button>
                        <button class="btn btn-primary" id="apply-signature"><?php esc_html_e('Apply Signature', 'flavor'); ?></button>
                    </div>
                </div>
            </div>
            
            <script>
            (function() {
                const config = {
                    token: '<?php echo esc_js($token); ?>',
                    documentId: <?php echo intval($document['id']); ?>,
                    ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                    nonce: '<?php echo esc_js(wp_create_nonce('slm_dms_nonce')); ?>',
                    fields: <?php echo json_encode(array_map(function($f) {
                        return [
                            'id' => $f->id,
                            'type' => $f->field_type,
                            'page' => $f->page_number,
                            'x' => $f->x_position,
                            'y' => $f->y_position,
                            'width' => $f->width,
                            'height' => $f->height,
                            'required' => $f->required,
                        ];
                    }, $fields)); ?>
                };
                
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                
                let pdfDoc = null;
                let signatures = {};
                let currentFieldId = null;
                let signatureCanvas = null;
                let signatureCtx = null;
                let isDrawing = false;
                let signatureMode = 'draw';
                
                // Load document
                async function loadDocument() {
                    const formData = new FormData();
                    formData.append('action', 'slm_get_document_page');
                    formData.append('nonce', config.nonce);
                    formData.append('document_id', config.documentId);
                    formData.append('session_token', config.token);
                    
                    const response = await fetch(config.ajaxUrl, { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    if (!result.success) {
                        alert('Failed to load document');
                        return;
                    }
                    
                    const pdfData = atob(result.data.content);
                    const pdfArray = new Uint8Array(pdfData.length);
                    for (let i = 0; i < pdfData.length; i++) {
                        pdfArray[i] = pdfData.charCodeAt(i);
                    }
                    
                    pdfDoc = await pdfjsLib.getDocument({ data: pdfArray }).promise;
                    await renderDocument();
                }
                
                async function renderDocument() {
                    const viewer = document.getElementById('pdf-viewer');
                    viewer.innerHTML = '';
                    
                    for (let i = 1; i <= pdfDoc.numPages; i++) {
                        const page = await pdfDoc.getPage(i);
                        const scale = 1.5;
                        const viewport = page.getViewport({ scale });
                        
                        const container = document.createElement('div');
                        container.className = 'pdf-page-container';
                        container.dataset.page = i;
                        
                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                        container.appendChild(canvas);
                        
                        // Add field overlays
                        config.fields.filter(f => f.page == i).forEach(field => {
                            const fieldEl = document.createElement('div');
                            fieldEl.className = 'signing-field';
                            fieldEl.dataset.fieldId = field.id;
                            fieldEl.style.left = (field.x / 100 * viewport.width) + 'px';
                            fieldEl.style.top = (field.y / 100 * viewport.height) + 'px';
                            fieldEl.style.width = field.width + 'px';
                            fieldEl.style.height = field.height + 'px';
                            fieldEl.textContent = field.type === 'signature' ? 'Sign Here' : (field.type === 'date' ? 'Date' : 'Click');
                            
                            fieldEl.addEventListener('click', () => openSignatureModal(field.id, field.type));
                            container.appendChild(fieldEl);
                        });
                        
                        viewer.appendChild(container);
                    }
                }
                
                // Signature modal
                function openSignatureModal(fieldId, fieldType) {
                    currentFieldId = fieldId;
                    
                    if (fieldType === 'date') {
                        applyDate(fieldId);
                        return;
                    }
                    
                    document.getElementById('signature-modal').classList.add('active');
                    initSignatureCanvas();
                }
                
                function closeSignatureModal() {
                    document.getElementById('signature-modal').classList.remove('active');
                    currentFieldId = null;
                }
                
                function initSignatureCanvas() {
                    signatureCanvas = document.getElementById('signature-canvas');
                    signatureCtx = signatureCanvas.getContext('2d');
                    
                    const rect = signatureCanvas.parentElement.getBoundingClientRect();
                    signatureCanvas.width = rect.width;
                    signatureCanvas.height = 150;
                    
                    signatureCtx.strokeStyle = '#1e3a5f';
                    signatureCtx.lineWidth = 2;
                    signatureCtx.lineCap = 'round';
                    
                    signatureCanvas.addEventListener('mousedown', startDrawing);
                    signatureCanvas.addEventListener('mousemove', draw);
                    signatureCanvas.addEventListener('mouseup', stopDrawing);
                    signatureCanvas.addEventListener('mouseleave', stopDrawing);
                    
                    signatureCanvas.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        startDrawing(e.touches[0]);
                    });
                    signatureCanvas.addEventListener('touchmove', (e) => {
                        e.preventDefault();
                        draw(e.touches[0]);
                    });
                    signatureCanvas.addEventListener('touchend', stopDrawing);
                }
                
                function startDrawing(e) {
                    isDrawing = true;
                    signatureCtx.beginPath();
                    const rect = signatureCanvas.getBoundingClientRect();
                    signatureCtx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                }
                
                function draw(e) {
                    if (!isDrawing) return;
                    const rect = signatureCanvas.getBoundingClientRect();
                    signatureCtx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                    signatureCtx.stroke();
                }
                
                function stopDrawing() {
                    isDrawing = false;
                }
                
                function clearSignature() {
                    if (signatureCtx) {
                        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                    }
                    document.getElementById('typed-name').value = '';
                    document.getElementById('typed-preview').textContent = '';
                }
                
                function applySignature() {
                    let signatureData;
                    
                    if (signatureMode === 'draw') {
                        signatureData = signatureCanvas.toDataURL('image/png');
                    } else {
                        const name = document.getElementById('typed-name').value;
                        if (!name) {
                            alert('Please enter your name');
                            return;
                        }
                        signatureData = 'typed:' + name;
                    }
                    
                    signatures[currentFieldId] = signatureData;
                    
                    // Update field display
                    const fieldEl = document.querySelector(`[data-field-id="${currentFieldId}"]`);
                    if (fieldEl) {
                        fieldEl.classList.add('signed');
                        if (signatureMode === 'draw') {
                            fieldEl.innerHTML = `<img src="${signatureData}" alt="Signature">`;
                        } else {
                            fieldEl.innerHTML = `<span style="font-family: cursive;">${document.getElementById('typed-name').value}</span>`;
                        }
                    }
                    
                    // Update sidebar
                    const listItem = document.querySelector(`.field-item[data-field-id="${currentFieldId}"]`);
                    if (listItem) {
                        listItem.classList.add('completed');
                    }
                    
                    closeSignatureModal();
                    clearSignature();
                    checkCompletion();
                }
                
                function applyDate(fieldId) {
                    const date = new Date().toLocaleDateString();
                    signatures[fieldId] = 'date:' + date;
                    
                    const fieldEl = document.querySelector(`[data-field-id="${fieldId}"]`);
                    if (fieldEl) {
                        fieldEl.classList.add('signed');
                        fieldEl.textContent = date;
                    }
                    
                    const listItem = document.querySelector(`.field-item[data-field-id="${fieldId}"]`);
                    if (listItem) {
                        listItem.classList.add('completed');
                    }
                    
                    checkCompletion();
                }
                
                function checkCompletion() {
                    const requiredFields = config.fields.filter(f => f.required);
                    const completedRequired = requiredFields.every(f => signatures[f.id]);
                    document.getElementById('submit-btn').disabled = !completedRequired;
                }
                
                async function submitSignatures() {
                    const formData = new FormData();
                    formData.append('action', 'slm_submit_signature');
                    formData.append('nonce', config.nonce);
                    formData.append('token', config.token);
                    formData.append('signatures', JSON.stringify(signatures));
                    
                    const response = await fetch(config.ajaxUrl, { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('<?php echo esc_js(__('Thank you! Your signature has been submitted.', 'flavor')); ?>');
                        window.location.href = '<?php echo esc_js(home_url()); ?>';
                    } else {
                        alert(result.data.message || 'Submission failed');
                    }
                }
                
                // Event listeners
                document.getElementById('modal-close').addEventListener('click', closeSignatureModal);
                document.getElementById('cancel-signature').addEventListener('click', closeSignatureModal);
                document.getElementById('clear-signature').addEventListener('click', clearSignature);
                document.getElementById('apply-signature').addEventListener('click', applySignature);
                document.getElementById('submit-btn').addEventListener('click', submitSignatures);
                
                document.querySelectorAll('.signature-tab').forEach(tab => {
                    tab.addEventListener('click', () => {
                        document.querySelectorAll('.signature-tab').forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');
                        signatureMode = tab.dataset.tab;
                        
                        document.getElementById('draw-panel').style.display = signatureMode === 'draw' ? 'block' : 'none';
                        document.getElementById('type-panel').classList.toggle('active', signatureMode === 'type');
                    });
                });
                
                document.getElementById('typed-name').addEventListener('input', (e) => {
                    document.getElementById('typed-preview').textContent = e.target.value;
                });
                
                document.querySelectorAll('.field-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const page = item.dataset.page;
                        document.querySelector(`[data-page="${page}"]`)?.scrollIntoView({ behavior: 'smooth' });
                    });
                });
                
                // Initialize
                loadDocument();
            })();
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render error page
     */
    private static function render_error_page($message) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php esc_html_e('Signing Error', 'flavor'); ?></title>
            <style>
                body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f3f4f6; }
                .error { text-align: center; padding: 40px; }
                .error h1 { color: #dc2626; }
            </style>
        </head>
        <body>
            <div class="error">
                <h1>⚠️</h1>
                <p><?php echo esc_html($message); ?></p>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Get client IP
     */
    private static function get_client_ip() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
    
    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box('slm_envelope_details', __('Envelope Details', 'flavor'), [__CLASS__, 'render_details_meta_box'], 'slm_envelope', 'normal', 'high');
    }
    
    /**
     * Render details meta box
     */
    public static function render_details_meta_box($post) {
        $document_id = get_post_meta($post->ID, '_slm_document_id', true);
        $status = get_post_meta($post->ID, '_slm_status', true);
        $signers = get_post_meta($post->ID, '_slm_signers', true) ?: [];
        $mode = get_post_meta($post->ID, '_slm_signing_mode', true);
        $expiry = get_post_meta($post->ID, '_slm_expiry_date', true);
        
        $document = $document_id ? SLM_DMS_Documents::get_document($document_id) : null;
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Document', 'flavor'); ?></th>
                <td><?php echo $document ? '<a href="' . esc_url(get_edit_post_link($document_id)) . '">' . esc_html($document['title']) . '</a>' : 'N/A'; ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Status', 'flavor'); ?></th>
                <td><strong><?php echo esc_html(ucfirst($status)); ?></strong></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Signing Mode', 'flavor'); ?></th>
                <td><?php echo esc_html(ucfirst($mode)); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Expires', 'flavor'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiry))); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Signers', 'flavor'); ?></th>
                <td>
                    <?php if (empty($signers)): ?>
                        <em><?php esc_html_e('No signers added', 'flavor'); ?></em>
                    <?php else: ?>
                        <table class="widefat striped">
                            <thead><tr><th><?php esc_html_e('Name', 'flavor'); ?></th><th><?php esc_html_e('Email', 'flavor'); ?></th><th><?php esc_html_e('Status', 'flavor'); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($signers as $signer): ?>
                                <tr>
                                    <td><?php echo esc_html($signer['name']); ?></td>
                                    <td><?php echo esc_html($signer['email']); ?></td>
                                    <td><?php echo esc_html(ucfirst($signer['status'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * AJAX handlers
     */
    public static function ajax_create_envelope() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $result = self::create_envelope(intval($_POST['document_id'] ?? 0), [
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'message' => sanitize_textarea_field($_POST['message'] ?? ''),
            'signing_mode' => sanitize_text_field($_POST['signing_mode'] ?? 'sequential'),
        ]);
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['envelope_id' => $result]);
    }
    
    public static function ajax_send_envelope() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $result = self::send_envelope(intval($_POST['envelope_id'] ?? 0));
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['message' => __('Envelope sent.', 'flavor')]);
    }
    
    public static function ajax_add_signer() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $result = self::add_signer(intval($_POST['envelope_id'] ?? 0), $_POST);
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['signer_index' => $result]);
    }
    
    public static function ajax_add_field() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $result = self::add_field(intval($_POST['envelope_id'] ?? 0), $_POST);
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['field_id' => $result]);
    }
    
    public static function ajax_void_envelope() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $result = self::void_envelope(intval($_POST['envelope_id'] ?? 0), sanitize_text_field($_POST['reason'] ?? ''));
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['message' => __('Envelope voided.', 'flavor')]);
    }
    
    public static function ajax_submit_signature() {
        check_ajax_referer('slm_dms_nonce', 'nonce');
        $token = sanitize_text_field($_POST['token'] ?? '');
        $signatures = json_decode(stripslashes($_POST['signatures'] ?? '{}'), true);
        $result = self::submit_signature($token, $signatures);
        if (is_wp_error($result)) wp_send_json_error(['message' => $result->get_error_message()]);
        wp_send_json_success(['message' => __('Signature submitted.', 'flavor')]);
    }
}
