<?php
/**
 * Engagement Letter System WITH ENCRYPTION - Installation Snippet
 * 
 * Add this code to class-el-core.php in the load_modules() method
 * or add directly to functions.php
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// ============================================
// OPTION 1: Update class-el-core.php
// ============================================

/**
 * In class-el-core.php, find the load_modules() method and update $modules array:
 */
public function load_modules() {
    $modules = [
        'class-el-woocommerce.php',
        'class-el-cron.php',
        'class-el-actions.php',
        'class-el-shortcodes.php',
        'class-el-encryption.php',         // ADD THIS LINE
        'class-el-encryption-admin.php'    // ADD THIS LINE
    ];
    
    foreach ($modules as $module) {
        $file = get_stylesheet_directory() . '/inc/engagement-letters/' . $module;
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// ============================================
// OPTION 2: Add directly to functions.php
// ============================================

/**
 * If you prefer to load encryption separately in functions.php:
 */

// Load Engagement Letters Module (existing)
require_once get_stylesheet_directory() . '/inc/engagement-letters/class-el-core.php';

// Load Encryption Module (NEW - add these two lines)
require_once get_stylesheet_directory() . '/inc/engagement-letters/class-el-encryption.php';
require_once get_stylesheet_directory() . '/inc/engagement-letters/class-el-encryption-admin.php';

// ============================================
// COMPLETE FILE STRUCTURE
// ============================================

/**
 * Your theme should now have this structure:
 * 
 * /wp-content/themes/bricks-child/
 * ├── functions.php
 * ├── page-lawyer-login.php
 * └── inc/
 *     └── engagement-letters/
 *         ├── class-el-core.php
 *         ├── class-el-woocommerce.php
 *         ├── class-el-cron.php
 *         ├── class-el-actions.php
 *         ├── class-el-shortcodes.php
 *         ├── class-el-encryption.php          ← NEW
 *         ├── class-el-encryption-admin.php    ← NEW
 *         └── js/
 *             └── el-actions.js
 */

// ============================================
// AFTER INSTALLATION
// ============================================

/**
 * 1. Go to: WP Admin → Engagement Letters → Encryption
 * 2. Verify "OpenSSL Available" shows ✓ Yes
 * 3. Download encryption keys (backup)
 * 4. Click "Encrypt All Existing Data"
 * 5. Move keys to wp-config.php (optional but recommended)
 * 
 * See ENCRYPTION-GUIDE.md for full documentation
 */

// ============================================
// MOVING KEYS TO WP-CONFIG.PHP (RECOMMENDED)
// ============================================

/**
 * For maximum security, add to wp-config.php (before "That's all, stop editing!"):
 * 
 * Get the actual key values from: Engagement Letters → Encryption → Advanced Configuration
 */

// Engagement Letter Encryption Keys
define('EL_ENCRYPTION_KEY', 'YOUR_KEY_FROM_ADMIN_PAGE');
define('EL_ENCRYPTION_SALT', 'YOUR_SALT_FROM_ADMIN_PAGE');

/**
 * After adding to wp-config.php, the system will automatically use these
 * constants instead of database options for better security.
 */

// ============================================
// VERIFICATION
// ============================================

/**
 * Test encryption is working:
 */

// 1. Create a new engagement letter with matter reference "TEST-123"
// 2. Check database directly:
//    SELECT meta_value FROM wp_postmeta 
//    WHERE meta_key = '_el_matter_reference' 
//    ORDER BY meta_id DESC LIMIT 1;
//
// 3. Should show: "ENC:aGVsbG93b3JsZA==" (encrypted)
// 4. View engagement letter in admin - should display "TEST-123" (decrypted)

// ============================================
// TROUBLESHOOTING
// ============================================

/**
 * If you see "[ENCRYPTED]" instead of actual data:
 */

// Check 1: Verify OpenSSL is installed
add_action('init', function() {
    if (isset($_GET['test_openssl'])) {
        if (function_exists('openssl_encrypt')) {
            echo '✓ OpenSSL is available';
        } else {
            echo '✕ OpenSSL not found';
        }
        exit;
    }
});
// Visit: yoursite.com/?test_openssl

// Check 2: Verify encryption keys exist
add_action('init', function() {
    if (isset($_GET['test_keys'])) {
        $key = get_option('el_encryption_key');
        $salt = get_option('el_encryption_salt');
        echo 'Encryption Key: ' . ($key ? 'EXISTS' : 'MISSING') . '<br>';
        echo 'Salt: ' . ($salt ? 'EXISTS' : 'MISSING');
        exit;
    }
});
// Visit: yoursite.com/?test_keys

// Check 3: Test encryption/decryption
add_action('init', function() {
    if (isset($_GET['test_encrypt']) && current_user_can('manage_options')) {
        $encryption = EL_Encryption::get_instance();
        
        $original = 'Hello World';
        $encrypted = $encryption->encrypt($original);
        $decrypted = $encryption->decrypt($encrypted);
        
        echo 'Original: ' . $original . '<br>';
        echo 'Encrypted: ' . $encrypted . '<br>';
        echo 'Decrypted: ' . $decrypted . '<br>';
        echo 'Match: ' . ($original === $decrypted ? '✓ YES' : '✕ NO');
        exit;
    }
});
// Visit: yoursite.com/?test_encrypt
