<?php
/**
 * SLM Email Templates
 * 
 * Centralized email template management for:
 * - Client onboarding invitation
 * - Lawyer notifications
 * - Completion confirmations
 * - Welcome emails
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Email_Templates {
    
    /**
     * Brand colors
     */
    const COLOR_PRIMARY = '#1e3a5f';
    const COLOR_ACCENT = '#2563eb';
    const COLOR_SUCCESS = '#16a34a';
    const COLOR_WARNING = '#ca8a04';
    const COLOR_DANGER = '#dc2626';
    const COLOR_GRAY = '#6b7280';
    const COLOR_LIGHT = '#f3f4f6';
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Allow filtering email content
        add_filter('slm_email_template', [__CLASS__, 'filter_template'], 10, 3);
    }
    
    /**
     * Send email with template
     * 
     * @param string $to Recipient email
     * @param string $template Template name
     * @param array $vars Template variables
     * @return bool Success
     */
    public static function send($to, $template, $vars = []) {
        $email_data = self::get_template($template, $vars);
        
        if (!$email_data) {
            SLM_Client_Onboarding::log('Email template not found: ' . $template, 'error');
            return false;
        }
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::get_from_name() . ' <' . self::get_from_email() . '>',
        ];
        
        // Add reply-to if different from from
        $reply_to = self::get_reply_to();
        if ($reply_to && $reply_to !== self::get_from_email()) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }
        
        $sent = wp_mail($to, $email_data['subject'], $email_data['body'], $headers);
        
        if ($sent) {
            SLM_Client_Onboarding::log('Email sent: ' . $template . ' to ' . $to);
        } else {
            SLM_Client_Onboarding::log('Email failed: ' . $template . ' to ' . $to, 'error');
        }
        
        return $sent;
    }
    
    /**
     * Get email template
     * 
     * @param string $template Template name
     * @param array $vars Variables
     * @return array|null Email data with subject and body
     */
    public static function get_template($template, $vars = []) {
        // Add common variables
        $vars = array_merge([
            'firm_name' => self::get_firm_name(),
            'firm_email' => self::get_from_email(),
            'firm_phone' => get_option('slm_firm_phone', ''),
            'site_url' => home_url(),
            'year' => date('Y'),
        ], $vars);
        
        $method = 'template_' . str_replace('-', '_', $template);
        
        if (!method_exists(__CLASS__, $method)) {
            return null;
        }
        
        $email_data = call_user_func([__CLASS__, $method], $vars);
        
        // Allow filtering
        $email_data = apply_filters('slm_email_template', $email_data, $template, $vars);
        
        return $email_data;
    }
    
    /**
     * Get firm name
     */
    private static function get_firm_name() {
        return get_option('slm_firm_name', 'Studio Legale Metta');
    }
    
    /**
     * Get from email
     */
    private static function get_from_email() {
        $email = get_option('slm_firm_email', '');
        return $email ?: get_option('admin_email');
    }
    
    /**
     * Get from name
     */
    private static function get_from_name() {
        return self::get_firm_name();
    }
    
    /**
     * Get reply-to email
     */
    private static function get_reply_to() {
        return get_option('slm_firm_email', '') ?: get_option('admin_email');
    }
    
    /**
     * Filter template hook
     */
    public static function filter_template($email_data, $template, $vars) {
        return $email_data;
    }
    
    /* =========================================================================
       EMAIL TEMPLATES
       ========================================================================= */
    
    /**
     * Template: Client Onboarding Invitation
     */
    private static function template_client_onboarding_invitation($vars) {
        $subject = sprintf(
            __('Welcome to %s - Complete Your Account Setup', 'flavor'),
            $vars['firm_name']
        );
        
        $content = '
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['first_name'])) . '</p>
        
        <p>' . __('Welcome! We are pleased to have you as a client. To complete your account setup and access our secure client portal, please click the button below:', 'flavor') . '</p>
        
        ' . self::button($vars['link_url'], __('Complete Account Setup', 'flavor')) . '
        
        <p>' . __('During this process, you will:', 'flavor') . '</p>
        <ul>
            <li>' . __('Review and sign our Terms of Agreement', 'flavor') . '</li>
            <li>' . __('Set your secure account password', 'flavor') . '</li>
            <li>' . __('Gain access to your personal document portal', 'flavor') . '</li>
        </ul>
        
        ' . self::warning_box(
            sprintf(
                __('This link will expire in %d hours. If you need a new link, please contact our office.', 'flavor'),
                $vars['expiry_hours']
            )
        ) . '
        
        <p>' . __('If you did not expect this email or have any questions, please contact our office immediately.', 'flavor') . '</p>
        
        <p>' . __('We look forward to working with you.', 'flavor') . '</p>
        ';
        
        $footer_note = sprintf(
            __('If the button above does not work, copy and paste this link into your browser: %s', 'flavor'),
            '<br><span style="word-break: break-all; font-size: 12px; color: ' . self::COLOR_GRAY . ';">' . esc_url($vars['link_url']) . '</span>'
        );
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars, $footer_note),
        ];
    }
    
    /**
     * Template: Lawyer Link Sent Notification
     */
    private static function template_lawyer_link_sent($vars) {
        $subject = sprintf(
            __('Onboarding Link Sent - %s', 'flavor'),
            $vars['client_name']
        );
        
        $content = '
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['lawyer_name'])) . '</p>
        
        <p>' . __('An onboarding link has been sent to the following client:', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Client Name', 'flavor') => $vars['client_name'],
            __('Email', 'flavor') => $vars['client_email'],
            __('Link Expires', 'flavor') => sprintf(__('In %d hours', 'flavor'), $vars['expiry_hours']),
            __('Sent At', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
        ]) . '
        
        <p>' . __('The client will be asked to:', 'flavor') . '</p>
        <ul>
            <li>' . __('Sign the Terms of Agreement', 'flavor') . '</li>
            <li>' . __('Set their account password', 'flavor') . '</li>
        </ul>
        
        <p>' . __('You will receive a notification when the client completes the onboarding process.', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Onboarding Link', 'flavor') => '<span style="word-break: break-all; font-size: 11px;">' . esc_url($vars['link_url']) . '</span>',
        ], __('For Your Records', 'flavor')) . '
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /**
     * Template: Client Onboarding Complete
     */
    private static function template_client_onboarding_complete($vars) {
        $subject = sprintf(
            __('Welcome to %s - Account Setup Complete', 'flavor'),
            $vars['firm_name']
        );
        
        $content = '
        ' . self::success_badge(__('Account Activated', 'flavor')) . '
        
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['first_name'])) . '</p>
        
        <p>' . __('Congratulations! Your account has been successfully activated. You can now access your secure client portal to:', 'flavor') . '</p>
        
        <ul>
            <li>' . __('View and download your documents', 'flavor') . '</li>
            <li>' . __('Track your case progress', 'flavor') . '</li>
            <li>' . __('Communicate securely with our team', 'flavor') . '</li>
            <li>' . __('Manage your account settings', 'flavor') . '</li>
        </ul>
        
        ' . self::button($vars['portal_url'], __('Access Client Portal', 'flavor')) . '
        
        ' . self::info_box([
            __('Email', 'flavor') => $vars['email'],
            __('Document Reference', 'flavor') => $vars['terms_reference'] ?? 'N/A',
        ], __('Your Account', 'flavor')) . '
        
        <p>' . __('If you have any questions or need assistance, please do not hesitate to contact us.', 'flavor') . '</p>
        
        <p>' . __('Thank you for choosing our firm.', 'flavor') . '</p>
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /**
     * Template: Lawyer Onboarding Complete Notification
     */
    private static function template_lawyer_onboarding_complete($vars) {
        $subject = sprintf(
            __('Client Onboarding Complete - %s', 'flavor'),
            $vars['client_name']
        );
        
        $content = '
        ' . self::success_badge(__('Onboarding Complete', 'flavor')) . '
        
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['lawyer_name'])) . '</p>
        
        <p>' . __('The following client has completed their account setup:', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Client Name', 'flavor') => $vars['client_name'],
            __('Email', 'flavor') => $vars['client_email'],
            __('Completed At', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            __('Terms Reference', 'flavor') => $vars['terms_reference'] ?? 'N/A',
        ]) . '
        
        <p>' . __('The client has:', 'flavor') . '</p>
        <ul>
            <li>' . __('Signed the Terms of Agreement', 'flavor') . '</li>
            <li>' . __('Set their account password', 'flavor') . '</li>
            <li>' . __('Been upgraded to a full customer account', 'flavor') . '</li>
            <li>' . __('Had their document folder created', 'flavor') . '</li>
        </ul>
        
        <p>' . __('The client can now log in to the client portal and access their documents.', 'flavor') . '</p>
        
        ' . self::button($vars['admin_url'], __('View Client Details', 'flavor')) . '
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /**
     * Template: Magic Link Expired Reminder
     */
    private static function template_link_expired_reminder($vars) {
        $subject = sprintf(
            __('Action Required: Complete Your %s Account Setup', 'flavor'),
            $vars['firm_name']
        );
        
        $content = '
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['first_name'])) . '</p>
        
        <p>' . __('We noticed that you have not yet completed your account setup with us. Your previous onboarding link has expired.', 'flavor') . '</p>
        
        <p>' . __('To receive a new link, please contact our office or reply to this email.', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Original Link Sent', 'flavor') => $vars['original_sent_date'],
            __('Expired', 'flavor') => $vars['expired_date'],
        ]) . '
        
        <p>' . __('Completing your account setup will allow you to:', 'flavor') . '</p>
        <ul>
            <li>' . __('Access your secure document portal', 'flavor') . '</li>
            <li>' . __('Track your case progress', 'flavor') . '</li>
            <li>' . __('Communicate securely with our team', 'flavor') . '</li>
        </ul>
        
        <p>' . __('If you have any questions, please do not hesitate to contact us.', 'flavor') . '</p>
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /**
     * Template: Password Reset (for onboarded clients)
     */
    private static function template_password_reset($vars) {
        $subject = sprintf(
            __('Password Reset Request - %s', 'flavor'),
            $vars['firm_name']
        );
        
        $content = '
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['first_name'])) . '</p>
        
        <p>' . __('We received a request to reset your account password. Click the button below to set a new password:', 'flavor') . '</p>
        
        ' . self::button($vars['reset_url'], __('Reset Password', 'flavor')) . '
        
        ' . self::warning_box(
            sprintf(
                __('This link will expire in %d minutes. If you did not request this reset, you can safely ignore this email.', 'flavor'),
                $vars['expiry_minutes'] ?? 60
            )
        ) . '
        
        <p>' . __('For security reasons, we recommend:', 'flavor') . '</p>
        <ul>
            <li>' . __('Using a unique password you do not use elsewhere', 'flavor') . '</li>
            <li>' . __('Including a mix of letters, numbers, and symbols', 'flavor') . '</li>
            <li>' . __('Making your password at least 8 characters long', 'flavor') . '</li>
        </ul>
        
        <p>' . __('If you did not request a password reset, please contact us immediately.', 'flavor') . '</p>
        ';
        
        $footer_note = sprintf(
            __('If the button above does not work, copy and paste this link: %s', 'flavor'),
            '<br><span style="word-break: break-all; font-size: 12px; color: ' . self::COLOR_GRAY . ';">' . esc_url($vars['reset_url']) . '</span>'
        );
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars, $footer_note),
        ];
    }
    
    /**
     * Template: Document Shared
     */
    private static function template_document_shared($vars) {
        $subject = sprintf(
            __('New Document Available - %s', 'flavor'),
            $vars['firm_name']
        );
        
        $content = '
        <p>' . sprintf(__('Dear %s,', 'flavor'), esc_html($vars['first_name'])) . '</p>
        
        <p>' . __('A new document has been shared with you:', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Document', 'flavor') => $vars['document_name'],
            __('Type', 'flavor') => $vars['document_type'] ?? __('Document', 'flavor'),
            __('Shared By', 'flavor') => $vars['shared_by'] ?? $vars['firm_name'],
            __('Date', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
        ]) . '
        
        <p>' . __('You can view and download this document from your client portal:', 'flavor') . '</p>
        
        ' . self::button($vars['portal_url'], __('View Document', 'flavor')) . '
        
        <p>' . __('If you have any questions about this document, please contact us.', 'flavor') . '</p>
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /**
     * Template: Admin Notification
     */
    private static function template_admin_notification($vars) {
        $subject = sprintf(
            __('[%s] Client Onboarding: %s', 'flavor'),
            $vars['firm_name'],
            $vars['client_name']
        );
        
        $content = '
        <p>' . __('A new client has completed the onboarding process:', 'flavor') . '</p>
        
        ' . self::info_box([
            __('Client Name', 'flavor') => $vars['client_name'],
            __('Email', 'flavor') => $vars['client_email'],
            __('Sent By', 'flavor') => $vars['sent_by'] ?? __('System', 'flavor'),
            __('Completed At', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            __('IP Address', 'flavor') => $vars['ip_address'] ?? 'N/A',
        ]) . '
        
        ' . self::button($vars['admin_url'], __('View in Admin', 'flavor')) . '
        ';
        
        return [
            'subject' => $subject,
            'body' => self::wrap_template($content, $vars),
        ];
    }
    
    /* =========================================================================
       TEMPLATE HELPERS
       ========================================================================= */
    
    /**
     * Wrap content in base template
     */
    private static function wrap_template($content, $vars, $footer_note = '') {
        $firm_name = $vars['firm_name'];
        $year = $vars['year'];
        
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($firm_name) . '</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: ' . self::COLOR_LIGHT . '; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, \'Helvetica Neue\', sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: ' . self::COLOR_LIGHT . ';">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; max-width: 600px;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="text-align: center; padding-bottom: 30px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: ' . self::COLOR_PRIMARY . ';">' . esc_html($firm_name) . '</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 40px; font-size: 15px; line-height: 1.6; color: #333333;">
                                        ' . $content . '
                                        
                                        <!-- Signature -->
                                        <p style="margin-top: 30px; margin-bottom: 0;">
                                            ' . __('Best regards,', 'flavor') . '<br>
                                            <strong>' . esc_html($firm_name) . '</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 20px; text-align: center;">
                            ' . ($footer_note ? '<p style="margin: 0 0 15px 0; font-size: 13px; color: ' . self::COLOR_GRAY . ';">' . $footer_note . '</p>' : '') . '
                            <p style="margin: 0; font-size: 12px; color: ' . self::COLOR_GRAY . ';">
                                &copy; ' . esc_html($year) . ' ' . esc_html($firm_name) . '. ' . __('All rights reserved.', 'flavor') . '
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
    
    /**
     * Generate button HTML
     */
    private static function button($url, $text, $color = null) {
        $color = $color ?: self::COLOR_ACCENT;
        
        return '
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 25px auto;">
            <tr>
                <td style="border-radius: 6px; background-color: ' . $color . ';">
                    <a href="' . esc_url($url) . '" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px;">' . esc_html($text) . '</a>
                </td>
            </tr>
        </table>';
    }
    
    /**
     * Generate info box HTML
     */
    private static function info_box($data, $title = null) {
        $html = '<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0; background-color: ' . self::COLOR_LIGHT . '; border-radius: 6px;">';
        
        if ($title) {
            $html .= '<tr><td style="padding: 15px 20px 10px; font-weight: 600; color: ' . self::COLOR_PRIMARY . '; border-bottom: 1px solid #e5e7eb;">' . esc_html($title) . '</td></tr>';
        }
        
        $html .= '<tr><td style="padding: 15px 20px;">';
        $html .= '<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">';
        
        foreach ($data as $label => $value) {
            $html .= '
            <tr>
                <td style="padding: 6px 0; font-size: 14px; color: ' . self::COLOR_GRAY . '; width: 140px; vertical-align: top;">' . esc_html($label) . ':</td>
                <td style="padding: 6px 0; font-size: 14px; color: #333333;">' . $value . '</td>
            </tr>';
        }
        
        $html .= '</table></td></tr></table>';
        
        return $html;
    }
    
    /**
     * Generate warning box HTML
     */
    private static function warning_box($message) {
        return '
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
            <tr>
                <td style="background-color: #fef3c7; border-left: 4px solid ' . self::COLOR_WARNING . '; padding: 15px 20px; border-radius: 0 6px 6px 0;">
                    <p style="margin: 0; font-size: 14px; color: #92400e;">
                        <strong style="color: ' . self::COLOR_WARNING . ';">⚠ ' . __('Important:', 'flavor') . '</strong> ' . esc_html($message) . '
                    </p>
                </td>
            </tr>
        </table>';
    }
    
    /**
     * Generate success badge HTML
     */
    private static function success_badge($text) {
        return '
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px 0;">
            <tr>
                <td style="background-color: #dcfce7; color: ' . self::COLOR_SUCCESS . '; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                    ✓ ' . esc_html($text) . '
                </td>
            </tr>
        </table>';
    }
    
    /**
     * Generate danger box HTML
     */
    private static function danger_box($message) {
        return '
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
            <tr>
                <td style="background-color: #fee2e2; border-left: 4px solid ' . self::COLOR_DANGER . '; padding: 15px 20px; border-radius: 0 6px 6px 0;">
                    <p style="margin: 0; font-size: 14px; color: #991b1b;">
                        <strong style="color: ' . self::COLOR_DANGER . ';">⚠ ' . __('Warning:', 'flavor') . '</strong> ' . esc_html($message) . '
                    </p>
                </td>
            </tr>
        </table>';
    }
    
    /* =========================================================================
       PREVIEW METHODS
       ========================================================================= */
    
    /**
     * Get template preview
     */
    public static function get_preview($template) {
        $sample_vars = self::get_sample_vars();
        $email_data = self::get_template($template, $sample_vars);
        
        return $email_data ? $email_data['body'] : null;
    }
    
    /**
     * Get sample variables for preview
     */
    private static function get_sample_vars() {
        return [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'client_name' => 'John Smith',
            'client_email' => 'john.smith@example.com',
            'lawyer_name' => 'Maria Rossi',
            'link_url' => home_url('/client-onboarding/sample-token-123/'),
            'portal_url' => home_url('/client-portal/'),
            'admin_url' => admin_url('admin.php?page=slm-client-onboarding'),
            'reset_url' => home_url('/reset-password/?token=sample'),
            'expiry_hours' => 24,
            'expiry_minutes' => 60,
            'terms_reference' => 'TRM-2025-000001',
            'document_name' => 'Engagement Letter - Immigration Services',
            'document_type' => 'Engagement Letter',
            'shared_by' => 'Maria Rossi',
            'sent_by' => 'Maria Rossi',
            'ip_address' => '192.168.1.100',
            'original_sent_date' => date_i18n(get_option('date_format'), strtotime('-3 days')),
            'expired_date' => date_i18n(get_option('date_format'), strtotime('-2 days')),
        ];
    }
    
    /**
     * Get available templates list
     */
    public static function get_available_templates() {
        return [
            'client-onboarding-invitation' => __('Client Onboarding Invitation', 'flavor'),
            'lawyer-link-sent' => __('Lawyer: Link Sent Notification', 'flavor'),
            'client-onboarding-complete' => __('Client: Onboarding Complete', 'flavor'),
            'lawyer-onboarding-complete' => __('Lawyer: Onboarding Complete', 'flavor'),
            'link-expired-reminder' => __('Link Expired Reminder', 'flavor'),
            'password-reset' => __('Password Reset', 'flavor'),
            'document-shared' => __('Document Shared', 'flavor'),
            'admin-notification' => __('Admin Notification', 'flavor'),
        ];
    }
}
