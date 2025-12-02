<?php
/**
 * SLM Onboarding Flow
 * 
 * Renders the client onboarding page with:
 * - Terms of Agreement display
 * - Signature capture (drawn or typed)
 * - Password setup
 * - Account activation
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Onboarding_Flow {
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Hooks initialized in main class
    }
    
    /**
     * Render the full onboarding page
     */
    public static function render_onboarding_page($token_data) {
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        $user_id = $token_data['user_id'];
        $token = $token_data['token'];
        
        // Check current onboarding state
        $terms_signed = get_user_meta($user_id, 'slm_terms_signed', true);
        $onboarding_complete = get_user_meta($user_id, 'slm_onboarding_complete', true);
        
        // Determine which step to show
        $current_step = 'terms';
        if ($terms_signed && !$onboarding_complete) {
            $current_step = 'password';
        } elseif ($onboarding_complete) {
            $current_step = 'complete';
        }
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html__('Account Setup', 'flavor') . ' - ' . esc_html($firm_name); ?></title>
            <?php self::render_styles(); ?>
        </head>
        <body class="slm-onboarding-page">
            <div class="slm-onboarding-container">
                <!-- Header -->
                <header class="slm-onboarding-header">
                    <h1><?php echo esc_html($firm_name); ?></h1>
                    <p class="welcome-text">
                        <?php printf(
                            esc_html__('Welcome, %s', 'flavor'),
                            esc_html($token_data['first_name'] ?: $token_data['name'])
                        ); ?>
                    </p>
                </header>
                
                <!-- Progress Steps -->
                <div class="slm-progress-steps">
                    <div class="step <?php echo $current_step === 'terms' ? 'active' : ($terms_signed ? 'complete' : ''); ?>">
                        <div class="step-number">1</div>
                        <div class="step-label"><?php esc_html_e('Terms Agreement', 'flavor'); ?></div>
                    </div>
                    <div class="step-line <?php echo $terms_signed ? 'complete' : ''; ?>"></div>
                    <div class="step <?php echo $current_step === 'password' ? 'active' : ($onboarding_complete ? 'complete' : ''); ?>">
                        <div class="step-number">2</div>
                        <div class="step-label"><?php esc_html_e('Set Password', 'flavor'); ?></div>
                    </div>
                    <div class="step-line <?php echo $onboarding_complete ? 'complete' : ''; ?>"></div>
                    <div class="step <?php echo $current_step === 'complete' ? 'active complete' : ''; ?>">
                        <div class="step-number">3</div>
                        <div class="step-label"><?php esc_html_e('Complete', 'flavor'); ?></div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <main class="slm-onboarding-main">
                    <!-- Step 1: Terms Agreement -->
                    <div id="step-terms" class="slm-step-content" style="<?php echo $current_step !== 'terms' ? 'display:none;' : ''; ?>">
                        <?php self::render_terms_step($token_data); ?>
                    </div>
                    
                    <!-- Step 2: Password Setup -->
                    <div id="step-password" class="slm-step-content" style="<?php echo $current_step !== 'password' ? 'display:none;' : ''; ?>">
                        <?php self::render_password_step($token_data); ?>
                    </div>
                    
                    <!-- Step 3: Complete -->
                    <div id="step-complete" class="slm-step-content" style="<?php echo $current_step !== 'complete' ? 'display:none;' : ''; ?>">
                        <?php self::render_complete_step($token_data); ?>
                    </div>
                </main>
                
                <!-- Footer -->
                <footer class="slm-onboarding-footer">
                    <p>&copy; <?php echo date('Y') . ' ' . esc_html($firm_name); ?>. <?php esc_html_e('All rights reserved.', 'flavor'); ?></p>
                </footer>
            </div>
            
            <!-- Hidden data for JS -->
            <input type="hidden" id="slm-token" value="<?php echo esc_attr($token); ?>">
            <input type="hidden" id="slm-user-id" value="<?php echo esc_attr($user_id); ?>">
            <input type="hidden" id="slm-user-name" value="<?php echo esc_attr($token_data['name']); ?>">
            <input type="hidden" id="slm-user-email" value="<?php echo esc_attr($token_data['email']); ?>">
            
            <?php self::render_scripts(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render terms agreement step
     */
    private static function render_terms_step($token_data) {
        $terms_content = self::get_terms_content();
        ?>
        <div class="slm-terms-container">
            <h2><?php esc_html_e('Terms of Agreement', 'flavor'); ?></h2>
            <p class="step-description">
                <?php esc_html_e('Please read and sign our terms of agreement to continue.', 'flavor'); ?>
            </p>
            
            <!-- Terms Document -->
            <div class="slm-terms-document">
                <div class="terms-scroll-container" id="terms-scroll">
                    <?php echo wp_kses_post($terms_content); ?>
                </div>
                <div class="scroll-indicator" id="scroll-indicator">
                    <span class="dashicons dashicons-arrow-down-alt"></span>
                    <?php esc_html_e('Scroll to read all terms', 'flavor'); ?>
                </div>
            </div>
            
            <!-- Signature Section -->
            <div class="slm-signature-section">
                <h3><?php esc_html_e('Your Signature', 'flavor'); ?></h3>
                
                <!-- Signature Type Toggle -->
                <div class="signature-type-toggle">
                    <button type="button" class="sig-type-btn active" data-type="draw">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Draw Signature', 'flavor'); ?>
                    </button>
                    <button type="button" class="sig-type-btn" data-type="type">
                        <span class="dashicons dashicons-editor-textcolor"></span>
                        <?php esc_html_e('Type Signature', 'flavor'); ?>
                    </button>
                </div>
                
                <!-- Draw Signature -->
                <div class="signature-input-area" id="draw-signature-area">
                    <div class="signature-pad-container">
                        <canvas id="signature-pad" class="signature-pad"></canvas>
                        <button type="button" id="clear-signature" class="clear-btn">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Clear', 'flavor'); ?>
                        </button>
                    </div>
                    <p class="signature-hint"><?php esc_html_e('Use your mouse or finger to sign above', 'flavor'); ?></p>
                </div>
                
                <!-- Type Signature -->
                <div class="signature-input-area" id="type-signature-area" style="display: none;">
                    <input 
                        type="text" 
                        id="typed-signature" 
                        class="typed-signature-input"
                        placeholder="<?php esc_attr_e('Type your full legal name', 'flavor'); ?>"
                        autocomplete="off"
                    >
                    <div class="typed-signature-preview" id="typed-preview"></div>
                    <p class="signature-hint"><?php esc_html_e('Your typed name will appear as a signature', 'flavor'); ?></p>
                </div>
                
                <!-- Full Name Confirmation -->
                <div class="full-name-input">
                    <label for="full-name"><?php esc_html_e('Full Legal Name', 'flavor'); ?> <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="full-name" 
                        name="full_name"
                        value="<?php echo esc_attr($token_data['name']); ?>"
                        required
                    >
                </div>
                
                <!-- Agreement Checkbox -->
                <div class="agreement-checkbox">
                    <label>
                        <input type="checkbox" id="agree-terms" required>
                        <span><?php esc_html_e('I have read, understood, and agree to the above terms and conditions.', 'flavor'); ?></span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="button" id="submit-signature" class="slm-btn primary" disabled>
                    <span class="btn-text"><?php esc_html_e('Sign & Continue', 'flavor'); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php esc_html_e('Processing...', 'flavor'); ?>
                    </span>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render password setup step
     */
    private static function render_password_step($token_data) {
        ?>
        <div class="slm-password-container">
            <h2><?php esc_html_e('Set Your Password', 'flavor'); ?></h2>
            <p class="step-description">
                <?php esc_html_e('Create a secure password for your account.', 'flavor'); ?>
            </p>
            
            <div class="password-form">
                <div class="form-group">
                    <label for="new-password"><?php esc_html_e('New Password', 'flavor'); ?> <span class="required">*</span></label>
                    <div class="password-input-wrap">
                        <input 
                            type="password" 
                            id="new-password" 
                            name="new_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" data-target="new-password">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength">
                        <div class="strength-bar"><div class="strength-fill"></div></div>
                        <span class="strength-text"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password"><?php esc_html_e('Confirm Password', 'flavor'); ?> <span class="required">*</span></label>
                    <div class="password-input-wrap">
                        <input 
                            type="password" 
                            id="confirm-password" 
                            name="confirm_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" data-target="confirm-password">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <p class="match-indicator" id="password-match" style="display: none;"></p>
                </div>
                
                <div class="password-requirements">
                    <p><strong><?php esc_html_e('Password must:', 'flavor'); ?></strong></p>
                    <ul>
                        <li id="req-length" class="requirement">
                            <span class="dashicons dashicons-minus"></span>
                            <?php esc_html_e('Be at least 8 characters long', 'flavor'); ?>
                        </li>
                        <li id="req-upper" class="requirement">
                            <span class="dashicons dashicons-minus"></span>
                            <?php esc_html_e('Contain at least one uppercase letter', 'flavor'); ?>
                        </li>
                        <li id="req-lower" class="requirement">
                            <span class="dashicons dashicons-minus"></span>
                            <?php esc_html_e('Contain at least one lowercase letter', 'flavor'); ?>
                        </li>
                        <li id="req-number" class="requirement">
                            <span class="dashicons dashicons-minus"></span>
                            <?php esc_html_e('Contain at least one number', 'flavor'); ?>
                        </li>
                    </ul>
                </div>
                
                <button type="button" id="submit-password" class="slm-btn primary" disabled>
                    <span class="btn-text"><?php esc_html_e('Set Password & Complete', 'flavor'); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        <?php esc_html_e('Processing...', 'flavor'); ?>
                    </span>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render complete step
     */
    private static function render_complete_step($token_data) {
        $login_url = wp_login_url();
        $portal_url = home_url('/client-portal/');
        ?>
        <div class="slm-complete-container">
            <div class="success-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            
            <h2><?php esc_html_e('Account Setup Complete!', 'flavor'); ?></h2>
            
            <p class="success-message">
                <?php esc_html_e('Your account has been successfully activated. You can now access your client portal.', 'flavor'); ?>
            </p>
            
            <div class="account-info">
                <div class="info-row">
                    <span class="label"><?php esc_html_e('Email:', 'flavor'); ?></span>
                    <span class="value"><?php echo esc_html($token_data['email']); ?></span>
                </div>
            </div>
            
            <div class="complete-actions">
                <a href="<?php echo esc_url($login_url); ?>" class="slm-btn primary">
                    <?php esc_html_e('Log In to Portal', 'flavor'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get terms content
     */
    private static function get_terms_content() {
        // Check for ACF field first
        $terms_content = get_field('terms_agreement_content', 'option');
        
        if (!empty($terms_content)) {
            return $terms_content;
        }
        
        // Fallback to placeholder
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        
        return '
        <h3>GENERAL TERMS OF ENGAGEMENT</h3>
        <p><strong>' . esc_html($firm_name) . '</strong></p>
        <p><em>Last Updated: ' . date('F Y') . '</em></p>
        
        <h4>1. INTRODUCTION</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        
        <h4>2. SCOPE OF SERVICES</h4>
        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        
        <h4>3. CLIENT OBLIGATIONS</h4>
        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
        <ul>
            <li>Provide accurate and complete information</li>
            <li>Respond to communications in a timely manner</li>
            <li>Pay invoices within the agreed terms</li>
            <li>Notify us of any changes to your circumstances</li>
        </ul>
        
        <h4>4. FEES AND PAYMENT</h4>
        <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
        
        <h4>5. CONFIDENTIALITY</h4>
        <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
        
        <h4>6. DATA PROTECTION</h4>
        <p>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur.</p>
        
        <h4>7. LIMITATION OF LIABILITY</h4>
        <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
        
        <h4>8. TERMINATION</h4>
        <p>Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio.</p>
        
        <h4>9. GOVERNING LAW</h4>
        <p>Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.</p>
        
        <h4>10. ACCEPTANCE</h4>
        <p>By signing below, you acknowledge that you have read, understood, and agree to be bound by these terms and conditions.</p>
        ';
    }
    
    /**
     * AJAX: Set password
     */
    public static function ajax_set_password() {
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($token) || empty($password)) {
            wp_send_json_error(['message' => __('Missing required fields.', 'flavor')]);
        }
        
        // Validate token
        $validation = SLM_Magic_Link::validate_token($token);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        $user_id = $validation['user_id'];
        
        // Verify terms are signed
        $terms_signed = get_user_meta($user_id, 'slm_terms_signed', true);
        
        if (!$terms_signed) {
            wp_send_json_error(['message' => __('Please sign the terms agreement first.', 'flavor')]);
        }
        
        // Validate password strength
        if (strlen($password) < 8) {
            wp_send_json_error(['message' => __('Password must be at least 8 characters.', 'flavor')]);
        }
        
        // Update password
        $result = wp_set_password($password, $user_id);
        
        // Mark onboarding complete
        update_user_meta($user_id, 'slm_onboarding_complete', true);
        update_user_meta($user_id, 'slm_onboarding_completed_at', current_time('mysql'));
        
        // Mark token as used
        SLM_Magic_Link::mark_token_used($token);
        
        // Upgrade to WooCommerce customer
        if (class_exists('SLM_Woo_Customer')) {
            SLM_Woo_Customer::upgrade_user($user_id);
        }
        
        // Create client folder
        if (class_exists('SLM_Document_Storage')) {
            SLM_Document_Storage::create_client_folder($user_id);
        }
        
        // Send completion notification to lawyer
        self::send_completion_notification($user_id, $validation['created_by']);
        
        SLM_Client_Onboarding::log('Onboarding completed for user ' . $user_id);
        
        wp_send_json_success([
            'message' => __('Account setup complete!', 'flavor'),
            'redirect' => wp_login_url(),
        ]);
    }
    
    /**
     * Send completion notification to lawyer
     */
    private static function send_completion_notification($user_id, $lawyer_id) {
        $lawyer = get_userdata($lawyer_id);
        
        if (!$lawyer) {
            return false;
        }
        
        $client = get_userdata($user_id);
        $client_name = trim(get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true)) ?: $client->display_name;
        
        // Use centralized email templates if available
        if (class_exists('SLM_Email_Templates')) {
            // Send to lawyer
            SLM_Email_Templates::send($lawyer->user_email, 'lawyer-onboarding-complete', [
                'lawyer_name' => $lawyer->display_name,
                'client_name' => $client_name,
                'client_email' => $client->user_email,
                'terms_reference' => get_user_meta($user_id, 'slm_terms_reference', true),
                'admin_url' => admin_url('admin.php?page=slm-client-onboarding'),
            ]);
            
            // Send welcome email to client if enabled
            if (get_option('slm_send_welcome_email', true)) {
                SLM_Email_Templates::send($client->user_email, 'client-onboarding-complete', [
                    'first_name' => get_user_meta($user_id, 'first_name', true) ?: $client_name,
                    'email' => $client->user_email,
                    'terms_reference' => get_user_meta($user_id, 'slm_terms_reference', true),
                    'portal_url' => home_url(get_option('slm_client_portal_url', '/client-portal/')),
                ]);
            }
            
            // Send admin notification if enabled
            if (get_option('slm_notify_admin_onboarding', false)) {
                SLM_Email_Templates::send(get_option('admin_email'), 'admin-notification', [
                    'client_name' => $client_name,
                    'client_email' => $client->user_email,
                    'sent_by' => $lawyer->display_name,
                    'admin_url' => admin_url('admin.php?page=slm-client-onboarding'),
                ]);
            }
            
            return true;
        }
        
        // Fallback to inline template
        $firm_name = get_option('slm_firm_name', 'Studio Legale Metta');
        
        $subject = sprintf(__('Client Onboarding Complete - %s', 'flavor'), $client_name);
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e5e7eb; }
                .success-badge { display: inline-block; background: #dcfce7; color: #16a34a; padding: 8px 16px; border-radius: 20px; font-weight: 600; margin-bottom: 20px; }
                .info-box { background: #f3f4f6; padding: 16px; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . esc_html($firm_name) . '</h1>
                </div>
                <div class="content">
                    <span class="success-badge">✓ Onboarding Complete</span>
                    
                    <p>Dear ' . esc_html($lawyer->display_name) . ',</p>
                    
                    <p>The following client has completed their account setup:</p>
                    
                    <div class="info-box">
                        <p><strong>Client:</strong> ' . esc_html($client_name) . '</p>
                        <p><strong>Email:</strong> ' . esc_html($client->user_email) . '</p>
                        <p><strong>Completed:</strong> ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . '</p>
                    </div>
                    
                    <p>The client has:</p>
                    <ul>
                        <li>Signed the Terms of Agreement</li>
                        <li>Set their account password</li>
                        <li>Been upgraded to a full WooCommerce customer</li>
                        <li>Had their document folder created</li>
                    </ul>
                    
                    <p>They can now log in to the client portal and access their documents.</p>
                    
                    <p>Best regards,<br><strong>' . esc_html($firm_name) . ' System</strong></p>
                </div>
            </div>
        </body>
        </html>';
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $firm_name . ' <' . get_option('admin_email') . '>',
        ];
        
        return wp_mail($lawyer->user_email, $subject, $message, $headers);
    }
    
    /**
     * Render page styles
     */
    private static function render_styles() {
        ?>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
        <link rel="stylesheet" href="<?php echo includes_url('css/dashicons.min.css'); ?>">
        <style>
            <?php echo file_get_contents(SLM_ONBOARDING_PATH . 'assets/css/frontend.css'); ?>
        </style>
        <?php
    }
    
    /**
     * Render page scripts
     */
    private static function render_scripts() {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
        <script>
            var slmOnboardingFront = {
                ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                nonce: '<?php echo wp_create_nonce('slm_onboarding_front_nonce'); ?>',
                strings: {
                    signing: '<?php echo esc_js(__('Processing your signature...', 'flavor')); ?>',
                    signed: '<?php echo esc_js(__('Terms signed successfully!', 'flavor')); ?>',
                    settingPassword: '<?php echo esc_js(__('Setting your password...', 'flavor')); ?>',
                    complete: '<?php echo esc_js(__('Account setup complete!', 'flavor')); ?>',
                    error: '<?php echo esc_js(__('An error occurred. Please try again.', 'flavor')); ?>',
                    signatureRequired: '<?php echo esc_js(__('Please provide your signature.', 'flavor')); ?>',
                    nameRequired: '<?php echo esc_js(__('Please enter your full name.', 'flavor')); ?>',
                    passwordMismatch: '<?php echo esc_js(__('Passwords do not match.', 'flavor')); ?>',
                    passwordWeak: '<?php echo esc_js(__('Password does not meet requirements.', 'flavor')); ?>'
                }
            };
        </script>
        <script>
            <?php echo file_get_contents(SLM_ONBOARDING_PATH . 'assets/js/frontend.js'); ?>
        </script>
        <?php
    }
    
    /**
     * Render shortcode version of flow
     */
    public static function render_flow() {
        // Check for token in URL
        $token = isset($_GET['slm_token']) ? sanitize_text_field($_GET['slm_token']) : '';
        
        if (empty($token)) {
            return '<div class="slm-error-message">' . 
                   esc_html__('No onboarding token provided. Please use the link from your email.', 'flavor') . 
                   '</div>';
        }
        
        // Validate token
        $validation = SLM_Magic_Link::validate_token($token);
        
        if (is_wp_error($validation)) {
            return '<div class="slm-error-message">' . esc_html($validation->get_error_message()) . '</div>';
        }
        
        ob_start();
        self::render_embedded_flow($validation);
        return ob_get_clean();
    }
    
    /**
     * Render embedded flow for shortcode use
     */
/**
 * Render embedded flow for shortcode use
 */
private static function render_embedded_flow($token_data) {
    // Simplified embedded version
    $user_id = $token_data['user_id'];
    $token = $token_data['token'];
    $terms_signed = get_user_meta($user_id, 'slm_terms_signed', true);
    $onboarding_complete = get_user_meta($user_id, 'slm_onboarding_complete', true);
    
    // Hidden data for JS
    ?>
    <input type="hidden" id="slm-token" value="<?php echo esc_attr($token); ?>">
    <input type="hidden" id="slm-user-id" value="<?php echo esc_attr($user_id); ?>">
    <input type="hidden" id="slm-user-name" value="<?php echo esc_attr($token_data['name']); ?>">
    <input type="hidden" id="slm-user-email" value="<?php echo esc_attr($token_data['email']); ?>">
    <?php
    
    if ($onboarding_complete) {
        self::render_complete_step($token_data);
    } elseif ($terms_signed) {
        self::render_password_step($token_data);
    } else {
        self::render_terms_step($token_data);
    }
}
}
