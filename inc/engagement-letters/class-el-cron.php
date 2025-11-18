<?php
/**
 * Engagement Letters Cron & Automation
 * 
 * Handles scheduled tasks including 30-day auto-expiry for sent engagement letters,
 * reminder emails, and cleanup operations.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EL_Cron {
    
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
        // Register custom cron schedules
        add_filter('cron_schedules', [$this, 'add_cron_schedules']);
        
        // Schedule events on activation
        add_action('init', [$this, 'schedule_events']);
        
        // Cron event handlers
        add_action('el_check_expired_letters', [$this, 'check_expired_letters']);
        add_action('el_send_expiry_reminders', [$this, 'send_expiry_reminders']);
        add_action('el_cleanup_old_data', [$this, 'cleanup_old_data']);
        
        // Cleanup on deactivation
        register_deactivation_hook(__FILE__, [$this, 'clear_scheduled_events']);
    }
    
    /**
     * Add custom cron schedules
     */
    public function add_cron_schedules($schedules) {
        // Every 6 hours
        $schedules['six_hours'] = [
            'interval' => 6 * HOUR_IN_SECONDS,
            'display' => __('Every 6 Hours')
        ];
        
        // Twice daily
        $schedules['twice_daily'] = [
            'interval' => 12 * HOUR_IN_SECONDS,
            'display' => __('Twice Daily')
        ];
        
        return $schedules;
    }
    
    /**
     * Schedule cron events
     */
    public function schedule_events() {
        // Check for expired letters every 6 hours
        if (!wp_next_scheduled('el_check_expired_letters')) {
            wp_schedule_event(time(), 'six_hours', 'el_check_expired_letters');
        }
        
        // Send expiry reminders daily at 9 AM
        if (!wp_next_scheduled('el_send_expiry_reminders')) {
            $timestamp = strtotime('tomorrow 9:00 AM');
            wp_schedule_event($timestamp, 'daily', 'el_send_expiry_reminders');
        }
        
        // Cleanup old data weekly
        if (!wp_next_scheduled('el_cleanup_old_data')) {
            $timestamp = strtotime('next Sunday 2:00 AM');
            wp_schedule_event($timestamp, 'weekly', 'el_cleanup_old_data');
        }
    }
    
    /**
     * Clear all scheduled events (on deactivation)
     */
    public function clear_scheduled_events() {
        $events = [
            'el_check_expired_letters',
            'el_send_expiry_reminders',
            'el_cleanup_old_data'
        ];
        
        foreach ($events as $event) {
            $timestamp = wp_next_scheduled($event);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $event);
            }
        }
    }
    
    /**
     * Check and expire letters that are past 30 days
     */
    public function check_expired_letters() {
        $args = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'el_status',
                    'field' => 'slug',
                    'terms' => 'sent_for_signature'
                ]
            ],
            'meta_query' => [
                [
                    'key' => '_el_expires_date',
                    'value' => time(),
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        $expired_count = 0;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Update status to expired
                wp_set_object_terms($post_id, 'expired', 'el_status');
                
                // Log expiry timestamp
                update_post_meta($post_id, '_el_expired_date', time());
                
                // Send notification email to lawyer
                $this->send_expiry_notification($post_id);
                
                $expired_count++;
                
                // Log
                error_log(sprintf(
                    'EL Cron: Expired engagement letter #%d (%s)',
                    $post_id,
                    get_the_title($post_id)
                ));
            }
            wp_reset_postdata();
        }
        
        if ($expired_count > 0) {
            error_log(sprintf('EL Cron: Expired %d engagement letters', $expired_count));
        }
        
        return $expired_count;
    }
    
    /**
     * Send expiry reminder emails (7 days before expiry)
     */
    public function send_expiry_reminders() {
        $reminder_time = strtotime('+7 days');
        
        $args = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'el_status',
                    'field' => 'slug',
                    'terms' => 'sent_for_signature'
                ]
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_el_expires_date',
                    'value' => [$reminder_time - DAY_IN_SECONDS, $reminder_time + DAY_IN_SECONDS],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ],
                [
                    'key' => '_el_reminder_sent',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        $reminder_count = 0;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Send reminder email
                $sent = $this->send_reminder_email($post_id);
                
                if ($sent) {
                    // Mark reminder as sent
                    update_post_meta($post_id, '_el_reminder_sent', time());
                    $reminder_count++;
                    
                    error_log(sprintf(
                        'EL Cron: Sent expiry reminder for #%d',
                        $post_id
                    ));
                }
            }
            wp_reset_postdata();
        }
        
        if ($reminder_count > 0) {
            error_log(sprintf('EL Cron: Sent %d expiry reminders', $reminder_count));
        }
        
        return $reminder_count;
    }
    
    /**
     * Send expiry notification to lawyer
     */
    private function send_expiry_notification($el_id) {
        $el_data = EL_Core::get_el_data($el_id);
        
        if (!$el_data) {
            return false;
        }
        
        // Get lawyer email
        $lawyer = get_user_by('id', $el_data['author_id']);
        if (!$lawyer) {
            return false;
        }
        
        // Get client info
        $client = get_user_by('id', $el_data['client_id']);
        $client_name = $client ? $client->display_name : 'Unknown Client';
        
        // Email content
        $to = $lawyer->user_email;
        $subject = sprintf('[Expired] Engagement Letter: %s', $el_data['title']);
        
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
                .alert { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 6px; }
                .details { background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .details-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .details-row:last-child { border-bottom: none; }
                .label { font-weight: 600; color: #64748b; }
                .value { color: #1e293b; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600; }
                .footer { text-align: center; padding: 20px; color: #64748b; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⏰ Engagement Letter Expired</h1>
                </div>
                <div class="content">
                    <p>Hello <?php echo esc_html($lawyer->display_name); ?>,</p>
                    
                    <div class="alert">
                        <strong>⚠️ Signature Link Expired</strong><br>
                        The signature link for this engagement letter has expired after 30 days without being signed.
                    </div>
                    
                    <div class="details">
                        <div class="details-row">
                            <span class="label">Engagement Letter:</span>
                            <span class="value"><?php echo esc_html($el_data['title']); ?></span>
                        </div>
                        <div class="details-row">
                            <span class="label">Client:</span>
                            <span class="value"><?php echo esc_html($client_name); ?></span>
                        </div>
                        <?php if ($el_data['matter_ref']): ?>
                        <div class="details-row">
                            <span class="label">Matter Reference:</span>
                            <span class="value"><?php echo esc_html($el_data['matter_ref']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="details-row">
                            <span class="label">Sent Date:</span>
                            <span class="value"><?php echo date('F j, Y', $el_data['sent_date']); ?></span>
                        </div>
                        <div class="details-row">
                            <span class="label">Expired Date:</span>
                            <span class="value"><?php echo date('F j, Y'); ?></span>
                        </div>
                    </div>
                    
                    <p><strong>What's next?</strong></p>
                    <ul>
                        <li>Review the engagement letter status</li>
                        <li>Contact the client to follow up</li>
                        <li>Resend a fresh signature link if needed</li>
                        <li>Cancel the engagement if no longer required</li>
                    </ul>
                    
                    <center>
                        <a href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>" class="button">
                            View Engagement Letter →
                        </a>
                    </center>
                </div>
                <div class="footer">
                    <p>This is an automated notification from your engagement letter system.</p>
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
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send reminder email (7 days before expiry)
     */
    private function send_reminder_email($el_id) {
        $el_data = EL_Core::get_el_data($el_id);
        
        if (!$el_data) {
            return false;
        }
        
        // Get lawyer email
        $lawyer = get_user_by('id', $el_data['author_id']);
        if (!$lawyer) {
            return false;
        }
        
        // Get client info
        $client = get_user_by('id', $el_data['client_id']);
        $client_name = $client ? $client->display_name : 'Unknown Client';
        $client_email = $client ? $client->user_email : '';
        
        $days_remaining = ceil(($el_data['expires_date'] - time()) / DAY_IN_SECONDS);
        
        // Email content
        $to = $lawyer->user_email;
        $subject = sprintf('[Reminder] Engagement Letter Expiring Soon: %s', $el_data['title']);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1e293b; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: white; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px; }
                .warning { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 6px; }
                .countdown { text-align: center; padding: 30px; background: #f8fafc; border-radius: 8px; margin: 20px 0; }
                .countdown-number { font-size: 48px; font-weight: 700; color: #f59e0b; }
                .countdown-label { font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
                .details { background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .details-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .details-row:last-child { border-bottom: none; }
                .label { font-weight: 600; color: #64748b; }
                .value { color: #1e293b; }
                .button { display: inline-block; background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600; }
                .footer { text-align: center; padding: 20px; color: #64748b; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⏰ Signature Link Expiring Soon</h1>
                </div>
                <div class="content">
                    <p>Hello <?php echo esc_html($lawyer->display_name); ?>,</p>
                    
                    <div class="warning">
                        <strong>⚠️ Action Required</strong><br>
                        An engagement letter signature link will expire soon if not signed.
                    </div>
                    
                    <div class="countdown">
                        <div class="countdown-number"><?php echo $days_remaining; ?></div>
                        <div class="countdown-label">Days Remaining</div>
                    </div>
                    
                    <div class="details">
                        <div class="details-row">
                            <span class="label">Engagement Letter:</span>
                            <span class="value"><?php echo esc_html($el_data['title']); ?></span>
                        </div>
                        <div class="details-row">
                            <span class="label">Client:</span>
                            <span class="value"><?php echo esc_html($client_name); ?></span>
                        </div>
                        <?php if ($client_email): ?>
                        <div class="details-row">
                            <span class="label">Client Email:</span>
                            <span class="value"><?php echo esc_html($client_email); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($el_data['matter_ref']): ?>
                        <div class="details-row">
                            <span class="label">Matter Reference:</span>
                            <span class="value"><?php echo esc_html($el_data['matter_ref']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="details-row">
                            <span class="label">Sent Date:</span>
                            <span class="value"><?php echo date('F j, Y', $el_data['sent_date']); ?></span>
                        </div>
                        <div class="details-row">
                            <span class="label">Expires:</span>
                            <span class="value"><?php echo date('F j, Y g:i A', $el_data['expires_date']); ?></span>
                        </div>
                    </div>
                    
                    <p><strong>Recommended Actions:</strong></p>
                    <ul>
                        <li>Contact the client to remind them to sign</li>
                        <li>Verify they received the signature link email</li>
                        <li>Offer assistance if they're having technical issues</li>
                        <li>Resend the link if needed</li>
                    </ul>
                    
                    <center>
                        <a href="<?php echo admin_url('post.php?post=' . $el_id . '&action=edit'); ?>" class="button">
                            View Engagement Letter →
                        </a>
                    </center>
                </div>
                <div class="footer">
                    <p>This is an automated reminder from your engagement letter system.</p>
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
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Cleanup old data (run weekly)
     */
    public function cleanup_old_data() {
        $cleanup_count = 0;
        
        // Delete cancelled letters older than 90 days
        $args = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'el_status',
                    'field' => 'slug',
                    'terms' => 'cancelled'
                ]
            ],
            'date_query' => [
                [
                    'before' => '90 days ago',
                    'inclusive' => true
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Move to trash instead of permanent delete
                wp_trash_post($post_id);
                $cleanup_count++;
                
                error_log(sprintf(
                    'EL Cron: Trashed old cancelled letter #%d',
                    $post_id
                ));
            }
            wp_reset_postdata();
        }
        
        // Clear transients older than 7 days
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_el_%' 
            AND option_value < " . (time() - (7 * DAY_IN_SECONDS))
        );
        
        if ($cleanup_count > 0) {
            error_log(sprintf('EL Cron: Cleaned up %d old records', $cleanup_count));
        }
        
        return $cleanup_count;
    }
    
    /**
     * Manual trigger for testing (admin only)
     */
    public static function trigger_expiry_check() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $instance = self::get_instance();
        return $instance->check_expired_letters();
    }
    
    /**
     * Get cron status for debugging
     */
    public static function get_cron_status() {
        return [
            'check_expired' => wp_next_scheduled('el_check_expired_letters'),
            'send_reminders' => wp_next_scheduled('el_send_expiry_reminders'),
            'cleanup' => wp_next_scheduled('el_cleanup_old_data')
        ];
    }
}

// Initialize
EL_Cron::get_instance();