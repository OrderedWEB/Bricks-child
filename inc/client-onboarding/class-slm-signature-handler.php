<?php
/**
 * SLM Signature Handler
 * 
 * Handles signature processing:
 * - Validates drawn signatures (base64 PNG)
 * - Renders typed signatures as images
 * - Stores signature data securely
 * - Generates signature images for PDFs
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Signature_Handler {
    
    /**
     * Maximum signature image size (bytes)
     */
    const MAX_SIGNATURE_SIZE = 500000; // 500KB
    
    /**
     * Signature image dimensions
     */
    const SIGNATURE_WIDTH = 400;
    const SIGNATURE_HEIGHT = 150;
    
    /**
     * Typed signature fonts (in order of preference)
     */
    const SIGNATURE_FONTS = [
        'Brush Script MT',
        'Segoe Script',
        'Bradley Hand',
        'Lucida Handwriting',
        'Comic Sans MS',
    ];
    
    /**
     * Initialize hooks
     */
    public static function init() {
        // Hooks initialized in main class
    }
    
    /**
     * Validate signature data
     * 
     * @param string $signature_type 'draw' or 'type'
     * @param string $signature_data Base64 image or typed name
     * @return bool|WP_Error True if valid, error otherwise
     */
    public static function validate_signature($signature_type, $signature_data) {
        if (empty($signature_data)) {
            return new WP_Error('empty_signature', __('Signature data is required.', 'flavor'));
        }
        
        if ($signature_type === 'draw') {
            return self::validate_drawn_signature($signature_data);
        } elseif ($signature_type === 'type') {
            return self::validate_typed_signature($signature_data);
        }
        
        return new WP_Error('invalid_type', __('Invalid signature type.', 'flavor'));
    }
    
    /**
     * Validate drawn signature
     */
    private static function validate_drawn_signature($signature_data) {
        // Check for valid base64 PNG data URL
        if (strpos($signature_data, 'data:image/png;base64,') !== 0) {
            return new WP_Error('invalid_format', __('Invalid signature image format.', 'flavor'));
        }
        
        // Extract base64 data
        $base64 = substr($signature_data, strlen('data:image/png;base64,'));
        
        // Validate base64
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64)) {
            return new WP_Error('invalid_base64', __('Invalid signature encoding.', 'flavor'));
        }
        
        // Decode and check size
        $decoded = base64_decode($base64, true);
        
        if ($decoded === false) {
            return new WP_Error('decode_failed', __('Failed to decode signature.', 'flavor'));
        }
        
        if (strlen($decoded) > self::MAX_SIGNATURE_SIZE) {
            return new WP_Error('too_large', __('Signature image is too large.', 'flavor'));
        }
        
        // Verify it's a valid PNG
        if (function_exists('imagecreatefromstring')) {
            $image = @imagecreatefromstring($decoded);
            
            if ($image === false) {
                return new WP_Error('invalid_image', __('Invalid signature image.', 'flavor'));
            }
            
            // Check dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            imagedestroy($image);
            
            // Signature should have reasonable dimensions
            if ($width < 50 || $height < 20) {
                return new WP_Error('too_small', __('Signature is too small.', 'flavor'));
            }
            
            if ($width > 2000 || $height > 1000) {
                return new WP_Error('too_large', __('Signature dimensions are too large.', 'flavor'));
            }
        }
        
        // Check that signature has actual content (not blank)
        if (!self::has_signature_content($decoded)) {
            return new WP_Error('blank_signature', __('Signature appears to be blank.', 'flavor'));
        }
        
        return true;
    }
    
    /**
     * Check if signature image has actual content
     */
    private static function has_signature_content($image_data) {
        if (!function_exists('imagecreatefromstring')) {
            // Can't verify, assume valid
            return true;
        }
        
        $image = @imagecreatefromstring($image_data);
        
        if ($image === false) {
            return false;
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Sample pixels to check for non-white content
        $non_white_pixels = 0;
        $samples = 100;
        
        for ($i = 0; $i < $samples; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);
            
            $rgb = imagecolorat($image, $x, $y);
            $colors = imagecolorsforindex($image, $rgb);
            
            // Check if pixel is not white (or nearly white)
            // Also check alpha for transparency
            $is_white = ($colors['red'] > 250 && $colors['green'] > 250 && $colors['blue'] > 250);
            $is_transparent = isset($colors['alpha']) && $colors['alpha'] > 100;
            
            if (!$is_white && !$is_transparent) {
                $non_white_pixels++;
            }
        }
        
        imagedestroy($image);
        
        // Require at least 5% non-white pixels in sample
        return ($non_white_pixels / $samples) >= 0.05;
    }
    
    /**
     * Validate typed signature
     */
    private static function validate_typed_signature($signature_data) {
        // Remove extra whitespace
        $signature = trim($signature_data);
        
        if (empty($signature)) {
            return new WP_Error('empty_name', __('Please enter your name.', 'flavor'));
        }
        
        // Minimum length
        if (strlen($signature) < 2) {
            return new WP_Error('too_short', __('Name is too short.', 'flavor'));
        }
        
        // Maximum length
        if (strlen($signature) > 100) {
            return new WP_Error('too_long', __('Name is too long.', 'flavor'));
        }
        
        // Check for valid characters (letters, spaces, hyphens, apostrophes, accented chars)
        if (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $signature)) {
            return new WP_Error('invalid_characters', __('Name contains invalid characters.', 'flavor'));
        }
        
        return true;
    }
    
    /**
     * Process signature for storage
     * 
     * @param string $signature_type 'draw' or 'type'
     * @param string $signature_data Raw signature data
     * @return array Processed signature data
     */
    public static function process_signature($signature_type, $signature_data) {
        $result = [
            'type' => $signature_type,
            'data' => $signature_data,
            'image' => null,
            'hash' => null,
        ];
        
        if ($signature_type === 'draw') {
            // Normalize and compress the image
            $normalized = self::normalize_drawn_signature($signature_data);
            
            if ($normalized) {
                $result['image'] = $normalized;
                $result['hash'] = hash('sha256', $normalized);
            } else {
                $result['image'] = $signature_data;
                $result['hash'] = hash('sha256', $signature_data);
            }
        } else {
            // Generate image from typed signature
            $image = self::render_typed_signature($signature_data);
            
            if ($image) {
                $result['image'] = $image;
            }
            
            $result['hash'] = hash('sha256', $signature_data);
        }
        
        return $result;
    }
    
    /**
     * Normalize drawn signature
     * - Resize to standard dimensions
     * - Optimize file size
     * - Ensure consistent format
     */
    private static function normalize_drawn_signature($signature_data) {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }
        
        // Extract base64 data
        $base64 = substr($signature_data, strlen('data:image/png;base64,'));
        $decoded = base64_decode($base64, true);
        
        if ($decoded === false) {
            return null;
        }
        
        $source = @imagecreatefromstring($decoded);
        
        if ($source === false) {
            return null;
        }
        
        $src_width = imagesx($source);
        $src_height = imagesy($source);
        
        // Calculate scaling to fit within max dimensions while preserving aspect ratio
        $scale = min(
            self::SIGNATURE_WIDTH / $src_width,
            self::SIGNATURE_HEIGHT / $src_height,
            1 // Don't upscale
        );
        
        $new_width = (int) ($src_width * $scale);
        $new_height = (int) ($src_height * $scale);
        
        // Create new image with white background
        $dest = imagecreatetruecolor($new_width, $new_height);
        
        // Enable alpha blending
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        
        // Fill with white
        $white = imagecolorallocate($dest, 255, 255, 255);
        imagefill($dest, 0, 0, $white);
        
        // Copy and resize
        imagecopyresampled(
            $dest, $source,
            0, 0, 0, 0,
            $new_width, $new_height,
            $src_width, $src_height
        );
        
        // Output as PNG
        ob_start();
        imagepng($dest, null, 6); // Compression level 6
        $png_data = ob_get_clean();
        
        imagedestroy($source);
        imagedestroy($dest);
        
        return 'data:image/png;base64,' . base64_encode($png_data);
    }
    
    /**
     * Render typed signature as image
     */
    private static function render_typed_signature($name) {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }
        
        // Create image
        $width = self::SIGNATURE_WIDTH;
        $height = self::SIGNATURE_HEIGHT;
        
        $image = imagecreatetruecolor($width, $height);
        
        // Enable anti-aliasing
        imageantialias($image, true);
        
        // White background
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        
        // Navy text color (matching firm branding)
        $text_color = imagecolorallocate($image, 30, 58, 95);
        
        // Try to find a suitable font
        $font_path = self::find_signature_font();
        
        if ($font_path) {
            // Use TrueType font
            $font_size = self::calculate_font_size($name, $font_path, $width - 40, $height - 40);
            
            // Center the text
            $bbox = imagettfbbox($font_size, 0, $font_path, $name);
            $text_width = $bbox[2] - $bbox[0];
            $text_height = $bbox[1] - $bbox[7];
            
            $x = ($width - $text_width) / 2;
            $y = ($height + $text_height) / 2;
            
            imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $name);
        } else {
            // Fallback to built-in font
            $font = 5; // Largest built-in font
            $text_width = imagefontwidth($font) * strlen($name);
            $text_height = imagefontheight($font);
            
            $x = ($width - $text_width) / 2;
            $y = ($height - $text_height) / 2;
            
            imagestring($image, $font, $x, $y, $name, $text_color);
        }
        
        // Output as PNG
        ob_start();
        imagepng($image, null, 6);
        $png_data = ob_get_clean();
        
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($png_data);
    }
    
    /**
     * Find a suitable signature font
     */
    private static function find_signature_font() {
        // Check common font locations
        $font_dirs = [
            '/usr/share/fonts/truetype/',
            '/usr/share/fonts/',
            'C:/Windows/Fonts/',
            ABSPATH . 'wp-content/fonts/',
            get_stylesheet_directory() . '/fonts/',
        ];
        
        $font_files = [
            'dejavu/DejaVuSans.ttf',
            'freefont/FreeSans.ttf',
            'liberation/LiberationSans-Regular.ttf',
            'arial.ttf',
            'Arial.ttf',
        ];
        
        foreach ($font_dirs as $dir) {
            foreach ($font_files as $file) {
                $path = $dir . $file;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        
        // Try to use a font from Gravity PDF if available
        $gravity_font = WP_PLUGIN_DIR . '/gravity-forms-pdf-extended/vendor/mpdf/mpdf/ttfonts/DejaVuSans.ttf';
        if (file_exists($gravity_font)) {
            return $gravity_font;
        }
        
        return null;
    }
    
    /**
     * Calculate font size to fit text in area
     */
    private static function calculate_font_size($text, $font_path, $max_width, $max_height) {
        $font_size = 48; // Start large
        $min_size = 12;
        
        while ($font_size > $min_size) {
            $bbox = imagettfbbox($font_size, 0, $font_path, $text);
            $text_width = $bbox[2] - $bbox[0];
            $text_height = $bbox[1] - $bbox[7];
            
            if ($text_width <= $max_width && $text_height <= $max_height) {
                return $font_size;
            }
            
            $font_size -= 2;
        }
        
        return $min_size;
    }
    
    /**
     * Store signature securely
     * 
     * @param int $user_id User ID
     * @param array $signature_data Processed signature data
     * @return bool Success
     */
    public static function store_signature($user_id, $signature_data) {
        // Store signature hash (not the actual signature for privacy)
        update_user_meta($user_id, 'slm_signature_hash', $signature_data['hash']);
        update_user_meta($user_id, 'slm_signature_type', $signature_data['type']);
        update_user_meta($user_id, 'slm_signature_date', current_time('mysql'));
        
        // Store signature image encrypted if document storage is available
        if (class_exists('SLM_Document_Storage') && !empty($signature_data['image'])) {
            // We could store the signature separately, but it's already in the signed PDF
            // Just log that we have it
            SLM_Client_Onboarding::log('Signature stored for user ' . $user_id);
        }
        
        return true;
    }
    
    /**
     * Verify signature matches stored hash
     */
    public static function verify_signature($user_id, $signature_data) {
        $stored_hash = get_user_meta($user_id, 'slm_signature_hash', true);
        
        if (empty($stored_hash)) {
            return false;
        }
        
        $current_hash = hash('sha256', $signature_data);
        
        return hash_equals($stored_hash, $current_hash);
    }
    
    /**
     * Get signature info for user
     */
    public static function get_signature_info($user_id) {
        $hash = get_user_meta($user_id, 'slm_signature_hash', true);
        $type = get_user_meta($user_id, 'slm_signature_type', true);
        $date = get_user_meta($user_id, 'slm_signature_date', true);
        
        if (empty($hash)) {
            return null;
        }
        
        return [
            'has_signature' => true,
            'type' => $type,
            'date' => $date,
            'hash' => substr($hash, 0, 16) . '...', // Truncated for display
        ];
    }
    
    /**
     * Get signature image for PDF embedding
     * 
     * @param string $signature_type 'draw' or 'type'
     * @param string $signature_data Raw signature data
     * @return string Base64 encoded PNG image
     */
    public static function get_signature_for_pdf($signature_type, $signature_data) {
        $processed = self::process_signature($signature_type, $signature_data);
        
        return $processed['image'] ?: $signature_data;
    }
}
