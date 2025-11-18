<?php
/**
 * Template Name: Lawyer Login
 * Description: Secure, beautiful login page for lawyers with accessibility features
 * 
 * @package Bricks_Child
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/lawyer-dashboard/');
    wp_safe_redirect($redirect_to);
    exit;
}

// Handle login form submission
$login_error = '';
$login_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lawyer_login_nonce'])) {
    if (wp_verify_nonce($_POST['lawyer_login_nonce'], 'lawyer_login_action')) {
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            $login_error = $user->get_error_message();
            
            // Log failed login attempt
            error_log(sprintf(
                'Failed login attempt for username: %s from IP: %s',
                $username,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));
        } else {
            // Successful login
            $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/lawyer-dashboard/');
            wp_safe_redirect($redirect_to);
            exit;
        }
    } else {
        $login_error = 'Security verification failed. Please try again.';
    }
}

get_header();
?>

<style>
/* Modern Login Page Styles */
.lawyer-login-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* Animated background particles */
.lawyer-login-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.lawyer-login-container {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 480px;
    width: 100%;
    padding: 48px 40px;
    position: relative;
    z-index: 1;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.lawyer-login-header {
    text-align: center;
    margin-bottom: 40px;
}

.lawyer-login-logo {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
}

.lawyer-login-title {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px 0;
    line-height: 1.2;
}

.lawyer-login-subtitle {
    font-size: 16px;
    color: #64748b;
    margin: 0;
    font-weight: 400;
}

/* Form Styles */
.lawyer-login-form {
    margin-top: 32px;
}

.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    letter-spacing: 0.01em;
}

.form-label .required {
    color: #dc2626;
    margin-left: 2px;
}

.form-input-wrapper {
    position: relative;
}

.form-input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 20px;
    pointer-events: none;
    transition: color 0.3s ease;
}

.form-input {
    width: 100%;
    padding: 14px 16px 14px 48px;
    font-size: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #1e293b;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-input:focus + .form-input-icon {
    color: #667eea;
}

.form-input::placeholder {
    color: #94a3b8;
}

/* Password visibility toggle */
.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    font-size: 20px;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #667eea;
}

.password-toggle:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Remember Me & Forgot Password Row */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.form-checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
}

.form-checkbox-label {
    font-size: 14px;
    color: #475569;
    cursor: pointer;
    user-select: none;
}

.form-link {
    font-size: 14px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.form-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

.form-link:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Submit Button */
.submit-button {
    width: 100%;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.submit-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.submit-button:hover::before {
    left: 100%;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.submit-button:active {
    transform: translateY(0);
}

.submit-button:focus {
    outline: 2px solid #ffffff;
    outline-offset: 2px;
}

.submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Alert Messages */
.login-alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    line-height: 1.6;
    display: flex;
    align-items: start;
    gap: 12px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-alert-icon {
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.login-alert-error {
    background: #fef2f2;
    border: 2px solid #fecaca;
    color: #991b1b;
}

.login-alert-success {
    background: #f0fdf4;
    border: 2px solid #bbf7d0;
    color: #166534;
}

/* Footer */
.lawyer-login-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.footer-text {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 16px 0;
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
}

.footer-link {
    font-size: 14px;
    color: #64748b;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #667eea;
}

/* Accessibility - Skip Link */
.skip-to-login {
    position: absolute;
    left: -9999px;
    top: 0;
    z-index: 999;
    padding: 12px 24px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 0 0 8px 0;
}

.skip-to-login:focus {
    left: 0;
}

/* Loading State */
.submit-button.loading {
    pointer-events: none;
}

.submit-button.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 640px) {
    .lawyer-login-container {
        padding: 32px 24px;
    }
    
    .lawyer-login-title {
        font-size: 28px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .footer-links {
        flex-direction: column;
        gap: 12px;
    }
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
    .form-input {
        border-width: 3px;
    }
    
    .submit-button {
        border: 3px solid #ffffff;
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Print Styles */
@media print {
    .lawyer-login-wrapper {
        background: white;
    }
    
    .submit-button,
    .footer-links {
        display: none;
    }
}
</style>

<div class="lawyer-login-wrapper">
    <a href="#login-form" class="skip-to-login">Skip to login form</a>
    
    <div class="lawyer-login-container" role="main">
        <header class="lawyer-login-header">
            <div class="lawyer-login-logo" aria-hidden="true">⚖️</div>
            <h1 class="lawyer-login-title">Welcome Back</h1>
            <p class="lawyer-login-subtitle">Sign in to your lawyer portal</p>
        </header>

        <?php if ($login_error): ?>
        <div class="login-alert login-alert-error" role="alert">
            <span class="login-alert-icon" aria-hidden="true">⚠️</span>
            <span><?php echo esc_html($login_error); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($login_success): ?>
        <div class="login-alert login-alert-success" role="status">
            <span class="login-alert-icon" aria-hidden="true">✓</span>
            <span><?php echo esc_html($login_success); ?></span>
        </div>
        <?php endif; ?>

        <form method="post" action="" class="lawyer-login-form" id="login-form" novalidate>
            <?php wp_nonce_field('lawyer_login_action', 'lawyer_login_nonce'); ?>
            
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($_GET['redirect_to'] ?? home_url('/lawyer-dashboard/')); ?>">

            <div class="form-group">
                <label for="username" class="form-label">
                    Email or Username
                    <span class="required" aria-label="required">*</span>
                </label>
                <div class="form-input-wrapper">
                    <span class="form-input-icon" aria-hidden="true">👤</span>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input"
                        placeholder="Enter your email or username"
                        required
                        autocomplete="username"
                        autofocus
                        aria-required="true"
                        aria-describedby="username-hint"
                        value="<?php echo esc_attr($_POST['username'] ?? ''); ?>"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Password
                    <span class="required" aria-label="required">*</span>
                </label>
                <div class="form-input-wrapper">
                    <span class="form-input-icon" aria-hidden="true">🔒</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                        aria-required="true"
                    >
                    <button 
                        type="button" 
                        class="password-toggle" 
                        onclick="togglePassword()"
                        aria-label="Toggle password visibility"
                        tabindex="0"
                    >
                        <span id="password-toggle-icon">👁️</span>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="form-checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="form-checkbox"
                        value="1"
                    >
                    <label for="remember" class="form-checkbox-label">
                        Remember me
                    </label>
                </div>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="form-link">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="submit-button" id="submit-button">
                Sign In to Portal
            </button>
        </form>

        <footer class="lawyer-login-footer">
            <p class="footer-text">Need assistance accessing your account?</p>
            <div class="footer-links">
                <a href="<?php echo esc_url(home_url('/support/')); ?>" class="footer-link">Contact Support</a>
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="footer-link">Privacy Policy</a>
                <a href="<?php echo esc_url(home_url('/terms/')); ?>" class="footer-link">Terms of Service</a>
            </div>
        </footer>
    </div>
</div>

<script>
// Password visibility toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
    }
}

// Form submission with loading state
document.getElementById('login-form').addEventListener('submit', function() {
    const submitButton = document.getElementById('submit-button');
    submitButton.classList.add('loading');
    submitButton.disabled = true;
    submitButton.innerHTML = '<span style="opacity:0">Signing in...</span>';
});

// Accessibility: Allow Enter key on password toggle
document.querySelector('.password-toggle').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        togglePassword();
    }
});

// Auto-focus on error
<?php if ($login_error): ?>
document.getElementById('username').focus();
<?php endif; ?>
</script>

<?php get_footer(); ?>