<?php
/**
 * SLM Magic Link
 * 
 * Generates secure magic links for client onboarding.
 * Handles token creation, validation, and email sending.
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Magic_Link {
    
    /**
     * Token length in bytes (will be 64 chars when hex encoded)
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Add rewrite rule for magic link landing
        add_action('init', [__CLASS__, 'add_rewrite_rules']);
        add_filter('query_vars', [__CLASS__, 'add_query_vars']);
        add_action('template_redirect', [__CLASS__, 'handle_magic_link_landing']);
    }
    
    /**
     * Add rewrite rules for magic link URLs
     */
    public static function add_rewrite_rules() {
        add_rewrite_rule(
            '^client-onboarding/([a-zA-Z0-9]+)/?$',
            'index.php?slm_onboarding=1&slm_token=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public static function add_query_vars($vars) {
        $vars[] = 'slm_onboarding';
        $vars[] = 'slm_token';
        return $vars;
    }
    
    /**
     * Handle magic link landing page
     */
    public static function handle_magic_link_landing() {
        if (!get_query_var('slm_onboarding')) {
            return;
        }
        
        $token = get_query_var('slm_token');
        
        if (empty($token)) {
            wp_redirect(home_url());
            exit;
        }
        
        // Validate token and get user data
        $validation = self::validate_token($token);
        
        if (is_wp_error($validation)) {
            // Show error page
            self::render_error_page($validation->get_error_message());
            exit;
        }
        
        // Token is valid - render onboarding flow
        if (class_exists('SLM_Onboarding_Flow')) {
            SLM_Onboarding_Flow::render_onboarding_page($validation);
        } else {
            self::render_error_page(__('Onboarding system not available.', 'flavor'));
        }
        exit;
    }
    
    /**
     * AJAX: Send magic link
     */
    public static function ajax_send_link() {
        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        
        if (!$user_id) {
            wp_send_json_error(['message' => __('Invalid user ID.', 'flavor')]);
        }
        
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => __('User not found.', 'flavor')]);
        }
        
        // Check if user already completed onboarding
        $onboarding_complete = get_user_meta($user_id, 'slm_onboarding_complete', true);
        
        if ($onboarding_complete) {
            wp_send_json_error(['message' => __('This client has already completed onboarding.', 'flavor')]);
        }
        
        // Generate and store token
        $token = self::generate_token($user_id);
        
        if (is_wp_error($token)) {
            wp_send_json_error(['message' => $token->get_error_message()]);
        }
        
        // Send email to client
        $email_sent = self::send_onboarding_email($user_id, $token);
        
        if (is_wp_error($email_sent)) {
            wp_send_json_error(['message' => $email_sent->get_error_message()]);
        }
        
        // Send copy to lawyer
        self::send_lawyer_notification($user_id, $token);
        
        wp_send_json_success([
            'message' => __('Onboarding link sent successfully.', 'flavor'),
            'expires_at' => date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'),
                time() + (SLM_MAGIC_LINK_EXPIRY_HOURS * HOUR_IN_SECONDS)
            ),
        ]);
    }
    
    /**
     * AJAX: Validate magic link
     */
    public static function ajax_validate() {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        
        if (empty($token)) {
            wp_send_json_error(['message' => __('Invalid token.', 'flavor')]);
        }
        
        $validation = self::validate_token($token);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        wp_send_json_success([
            'valid' => true,
            'user_id' => $validation['user_id'],
            'email' => $validation['email'],
            'name' => $validation['name'],
        ]);
    }
    
    /**
     * Generate a new magic link token
     */
    public static function generate_token($user_id) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('magic_links');
        
        if (!$table) {
            return new WP_Error('db_error', __('Database table not found.', 'flavor'));
        }
        
        // Invalidate any existing unused tokens for this user
        $wpdb->update(
            $table,
            ['used_at' => current_time('mysql')],
            [
                'user_id' => $user_id,
                'used_at' => null,
            ],
            ['%s'],
            ['%d', '%s']
        );
        
        // Generate new token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $token_hash = hash('sha256', $token);
        
        // Calculate expiry
        $expires_at = date('Y-m-d H:i:s', time() + (SLM_MAGIC_LINK_EXPIRY_HOURS * HOUR_IN_SECONDS));
        
        // Store in database
        $inserted = $wpdb->insert(
            $table,
            [
                'user_id' => $user_id,
                'token_hash' => $token_hash,
                'purpose' => 'onboarding',
                'created_by' => get_current_user_id(),
                'expires_at' => $expires_at,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );
        
        if (!$inserted) {
            SLM_Client_Onboarding::log('Failed to insert magic link token for user ' . $user_id, 'error');
            return new WP_Error('db_error', __('Failed to generate token.', 'flavor'));
        }
        
        SLM_Client_Onboarding::log('Generated magic link token for user ' . $user_id);
        
        return $token;
    }
    
    /**
     * Validate a magic link token
     */
    public static function validate_token($token) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('magic_links');
        
        if (!$table) {
            return new WP_Error('db_error', __('Database table not found.', 'flavor'));
        }
        
        $token_hash = hash('sha256', $token);
        
        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE token_hash = %s LIMIT 1",
            $token_hash
        ));
        
        if (!$record) {
            return new WP_Error('invalid_token', __('This link is invalid. Please contact your lawyer for a new onboarding link.', 'flavor'));
        }
        
        // Check if already used
        if ($record->used_at) {
            return new WP_Error('token_used', __('This link has already been used. If you need assistance, please contact your lawyer.', 'flavor'));
        }
        
        // Check if expired
        if (strtotime($record->expires_at) < time()) {
            return new WP_Error('token_expired', __('This link has expired. Please contact your lawyer for a new onboarding link.', 'flavor'));
        }
        
        // Get user data
        $user = get_userdata($record->user_id);
        
        if (!$user) {
            return new WP_Error('user_not_found', __('User account not found. Please contact support.', 'flavor'));
        }
        
        return [
            'token_id' => $record->id,
            'user_id' => $record->user_id,
            'token' => $token,
            'email' => $user->user_email,
            'name' => trim(get_user_meta($record->user_id, 'first_name', true) . ' ' . get_user_meta($record->user_id, 'last_name', true)) ?: $user->display_name,
            'first_name' => get_user_meta($record->user_id, 'first_name', true),
            'last_name' => get_user_meta($record->user_id, 'last_name', true),
            'expires_at' => $record->expires_at,
            'created_by' => $record->created_by,
        ];
    }
    
    /**
     * Mark token as used
     */
    public static function mark_token_used($token) {
        global $wpdb;
        
        $table = SLM_Client_Onboarding::get_table('magic_links');
        
        if (!$table) {
            return false;
        }
        
        $token_hash = hash('sha256', $token);
        
        $updated = $wpdb->update(
            $table,
            [
                'used_at' => current_time('mysql'),
                'used_ip' => self::get_client_ip(),
                'used_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            ],
            ['token_hash' => $token_hash],
            ['%s', '%s', '%s'],
            ['%s']
        );
        
        return $updated !== false;
    }
    
 /**
 * Get magic link URL
 */
public static function get_link_url($token) {
    return home_url('/client-onboarding/?slm_token=' . $token);
}
    
    /**
     * Send onboarding email to client
     */
    private static function send_onboarding_email($user_id, $token) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'flavor'));
        }
        
        // Use centralized email templates if available
        if (class_exists('SLM_Email_Templates')) {
            $sent = SLM_Email_Templates::send($user->user_email, 'client-onboarding-invitation', [
                'first_name' => get_user_meta($user_id, 'first_name', true) ?: __('Client', 'flavor'),
                'link_url' => self::get_link_url($token),
                'expiry_hours' => SLM_MAGIC_LINK_EXPIRY_HOURS,
            ]);
            
            if (!$sent) {
                return new WP_Error('email_failed', __('Failed to send email. Please try again.', 'flavor'));
            }
            
            return true;
        }
        
        // Fallback to inline template
        $first_name = get_user_meta($user_id, 'first_name', true);
        $link_url = self::get_link_url($token);
        $expiry_hours = SLM_MAGIC_LINK_EXPIRY_HOURS;
        
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        
        $subject = sprintf(__('Welcome to %s - Complete Your Account Setup', 'flavor'), $firm_name);
        
        $message = self::get_email_template('client', [
            'first_name' => $first_name ?: __('Client', 'flavor'),
            'firm_name' => $firm_name,
            'link_url' => $link_url,
            'expiry_hours' => $expiry_hours,
        ]);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
        ];
        
        $sent = wp_mail($user->user_email, $subject, $message, $headers);
        
        if (!$sent) {
            SLM_Client_Onboarding::log('Failed to send onboarding email to ' . $user->user_email, 'error');
            return new WP_Error('email_failed', __('Failed to send email. Please try again.', 'flavor'));
        }
        
        SLM_Client_Onboarding::log('Onboarding email sent to ' . $user->user_email);
        
        return true;
    }
    
    /**
     * Send notification email to lawyer
     */
    private static function send_lawyer_notification($user_id, $token) {
        $lawyer_id = get_current_user_id();
        $lawyer = get_userdata($lawyer_id);
        
        if (!$lawyer) {
            return false;
        }
        
        $client = get_userdata($user_id);
        $client_name = trim(get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true)) ?: $client->display_name;
        
        // Use centralized email templates if available
        if (class_exists('SLM_Email_Templates')) {
            return SLM_Email_Templates::send($lawyer->user_email, 'lawyer-link-sent', [
                'lawyer_name' => $lawyer->display_name,
                'client_name' => $client_name,
                'client_email' => $client->user_email,
                'link_url' => self::get_link_url($token),
                'expiry_hours' => SLM_MAGIC_LINK_EXPIRY_HOURS,
            ]);
        }
        
        // Fallback to inline template
        $link_url = self::get_link_url($token);
        $expiry_hours = SLM_MAGIC_LINK_EXPIRY_HOURS;
        
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        
        $subject = sprintf(__('Onboarding Link Sent - %s', 'flavor'), $client_name);
        
        $message = self::get_email_template('lawyer', [
            'lawyer_name' => $lawyer->display_name,
            'client_name' => $client_name,
            'client_email' => $client->user_email,
            'firm_name' => $firm_name,
            'link_url' => $link_url,
            'expiry_hours' => $expiry_hours,
        ]);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
        ];
        
        $sent = wp_mail($lawyer->user_email, $subject, $message, $headers);
        
        if ($sent) {
            SLM_Client_Onboarding::log('Lawyer notification sent to ' . $lawyer->user_email);
        }
        
        return $sent;
    }
    
    /**
     * Get email template
     */
    private static function get_email_template($type, $vars) {
        $firm_name = $vars['firm_name'] ?? 'Studio Legale Metta';
        
        // Base styles
        $styles = '
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #1e3a5f; margin: 0; font-size: 24px; }
            .content { background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e5e7eb; }
            .button { display: inline-block; background: #2563eb; color: #ffffff !important; padding: 14px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 20px 0; }
            .button:hover { background: #1d4ed8; }
            .footer { text-align: center; margin-top: 30px; font-size: 13px; color: #6b7280; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; font-size: 14px; }
            .info-box { background: #f3f4f6; padding: 16px; border-radius: 6px; margin: 20px 0; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
            .info-row:last-child { border-bottom: none; }
        ';
        
        if ($type === 'client') {
            return '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>' . $styles . '</style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>' . esc_html($firm_name) . '</h1>
                    </div>
                    <div class="content">
                        <p>Dear ' . esc_html($vars['first_name']) . ',</p>
                        
                        <p>Welcome! To complete your account setup and access our client portal, please click the button below:</p>
                        
                        <p style="text-align: center;">
                            <a href="' . esc_url($vars['link_url']) . '" class="button">Complete Account Setup</a>
                        </p>
                        
                        <p>During this process, you will:</p>
                        <ul>
                            <li>Review and sign our Terms of Agreement</li>
                            <li>Set your account password</li>
                            <li>Gain access to your secure document portal</li>
                        </ul>
                        
                        <div class="warning">
                            <strong>Important:</strong> This link will expire in ' . intval($vars['expiry_hours']) . ' hours. If you need a new link, please contact us.
                        </div>
                        
                        <p>If you did not expect this email or have any questions, please contact our office immediately.</p>
                        
                        <p>Best regards,<br><strong>' . esc_html($firm_name) . '</strong></p>
                    </div>
                    <div class="footer">
                        <p>If the button above does not work, copy and paste this link into your browser:</p>
                        <p style="word-break: break-all;">' . esc_url($vars['link_url']) . '</p>
                    </div>
                </div>
            </body>
            </html>';
        }
        
        if ($type === 'lawyer') {
            return '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>' . $styles . '</style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Onboarding Link Sent</h1>
                    </div>
                    <div class="content">
                        <p>Dear ' . esc_html($vars['lawyer_name']) . ',</p>
                        
                        <p>An onboarding link has been sent to the following client:</p>
                        
                        <div class="info-box">
                            <div class="info-row">
                                <strong>Client Name:</strong>
                                <span>' . esc_html($vars['client_name']) . '</span>
                            </div>
                            <div class="info-row">
                                <strong>Email:</strong>
                                <span>' . esc_html($vars['client_email']) . '</span>
                            </div>
                            <div class="info-row">
                                <strong>Link Expires:</strong>
                                <span>In ' . intval($vars['expiry_hours']) . ' hours</span>
                            </div>
                        </div>
                        
                        <p>The client will be asked to:</p>
                        <ul>
                            <li>Sign the Terms of Agreement</li>
                            <li>Set their account password</li>
                        </ul>
                        
                        <p>You will receive a notification when the client completes onboarding.</p>
                        
                        <div class="warning">
                            <strong>For your records:</strong> The onboarding link sent to the client is:<br>
                            <span style="word-break: break-all; font-size: 12px;">' . esc_url($vars['link_url']) . '</span>
                        </div>
                        
                        <p>Best regards,<br><strong>' . esc_html($firm_name) . ' System</strong></p>
                    </div>
                </div>
            </body>
            </html>';
        }
        
        return '';
    }
    
    /**
     * Render error page
     */
    private static function render_error_page($message) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html__('Link Error', 'flavor') . ' - ' . esc_html($firm_name); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #f3f4f6;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-container {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    padding: 48px;
                    max-width: 480px;
                    text-align: center;
                }
                .error-icon {
                    width: 64px;
                    height: 64px;
                    background: #fee2e2;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 24px;
                }
                .error-icon svg {
                    width: 32px;
                    height: 32px;
                    color: #dc2626;
                }
                h1 {
                    color: #1f2937;
                    font-size: 24px;
                    margin-bottom: 16px;
                }
                p {
                    color: #6b7280;
                    line-height: 1.6;
                    margin-bottom: 24px;
                }
                .btn {
                    display: inline-block;
                    background: #2563eb;
                    color: #fff;
                    padding: 12px 24px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: background 0.2s;
                }
                .btn:hover {
                    background: #1d4ed8;
                }
                .firm-name {
                    margin-top: 32px;
                    font-size: 14px;
                    color: #9ca3af;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1><?php echo esc_html__('Link Error', 'flavor'); ?></h1>
                <p><?php echo esc_html($message); ?></p>
                <a href="<?php echo esc_url(home_url()); ?>" class="btn">
                    <?php echo esc_html__('Return to Homepage', 'flavor'); ?>
                </a>
                <p class="firm-name"><?php echo esc_html($firm_name); ?></p>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}
