<?php
/**
 * mPDF Generator for Engagement Letters
 * 
 * Integrates with Gravity PDF's bundled mPDF library to generate print-ready PDFs.
 * Uses the pagination engine to produce professional legal documents.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * REQUIREMENTS:
 *   - Gravity PDF plugin installed and active
 *   - mPDF library available via Gravity PDF
 * 
 * DOCUMENT SPECIFICATIONS:
 *   - Paper: A4 (210mm x 297mm)
 *   - Margins: 25mm top/bottom, 15mm left/right
 *   - Font: Times New Roman, 12pt
 *   - Line height: 1.4
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_MPDF_Generator {
    
    /**
     * mPDF instance
     */
    private $mpdf = null;
    
    /**
     * Destination class (namespace varies by Gravity PDF version)
     */
    private $destination_class = null;
    
    /**
     * Document configuration
     */
    private $config = [];
    
    /**
     * Default configuration
     */
    private static $default_config = [
        'format' => 'A4',
        'orientation' => 'P',
        'margin_top' => 25,
        'margin_bottom' => 25,
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_header' => 10,
        'margin_footer' => 10,
        'default_font' => 'times',
        'default_font_size' => 12,
        'mode' => 'utf-8',
        'auto_language_detection' => true,
    ];
    
    /**
     * Constructor
     * 
     * @param array $config Optional configuration overrides
     */
    public function __construct($config = []) {
        $this->config = wp_parse_args($config, self::$default_config);
    }
    
    /**
     * Check if mPDF is available via Gravity PDF
     * 
     * @return bool True if available
     */
    public static function is_mpdf_available() {
        // Check if Gravity PDF is active
        if (!class_exists('GFPDF_Core')) {
            return false;
        }
        
        // Try to load mPDF
        $mpdf_path = self::get_mpdf_path();
        
        return $mpdf_path !== false;
    }
    
    /**
     * Get path to mPDF library
     * 
     * @return string|false Path to mPDF autoloader or false if not found
     */
    private static function get_mpdf_path() {
        // Gravity PDF bundles mPDF in vendor directory
        $possible_paths = [
            // Gravity PDF current path
            WP_PLUGIN_DIR . '/gravity-pdf/vendor/autoload.php',
            // Gravity PDF 6.x path (alternative name)
            WP_PLUGIN_DIR . '/gravity-forms-pdf-extended/vendor/autoload.php',
            // Gravity PDF 5.x path
            WP_PLUGIN_DIR . '/gravity-forms-pdf-extended/vendor/mpdf/mpdf/src/Mpdf.php',
            // Direct mPDF plugin (if installed separately)
            WP_PLUGIN_DIR . '/mpdf/vendor/autoload.php',
            // Composer autoload in theme
            get_stylesheet_directory() . '/vendor/autoload.php',
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return false;
    }
    
    /**
     * Initialize mPDF instance
     * 
     * @return bool True on success
     */
    public function init() {
        if ($this->mpdf !== null) {
            return true;
        }
        
        // Load mPDF
        $mpdf_path = self::get_mpdf_path();
        
        if ($mpdf_path === false) {
            error_log('EL mPDF Generator: mPDF library not found');
            return false;
        }
        
        require_once $mpdf_path;
        
        // Check if mPDF class exists
        if (!class_exists('GFPDF_Vendor\\Mpdf\\Mpdf') && !class_exists('\\Mpdf\\Mpdf')) {
            error_log('EL mPDF Generator: Mpdf class not found after loading');
            return false;
        }
        
        // Determine which namespace to use
        $mpdf_class = class_exists('GFPDF_Vendor\\Mpdf\\Mpdf') ? 'GFPDF_Vendor\\Mpdf\\Mpdf' : '\\Mpdf\\Mpdf';
        $this->destination_class = class_exists('GFPDF_Vendor\\Mpdf\\Mpdf') ? 'GFPDF_Vendor\\Mpdf\\Output\\Destination' : '\\Mpdf\\Output\\Destination';
        
        try {
            // Get Gravity PDF font directory - it's in uploads, not in plugin dir
            $font_dir = WP_CONTENT_DIR . '/uploads/PDF_EXTENDED_TEMPLATES/fonts/';
            
            // Fallback to plugin directory if uploads doesn't exist
            if (!is_dir($font_dir)) {
                $font_dir = WP_PLUGIN_DIR . '/gravity-pdf/vendor/mpdf/mpdf/ttfonts/';
            }
            if (!is_dir($font_dir)) {
                $font_dir = WP_PLUGIN_DIR . '/gravity-forms-pdf-extended/vendor/mpdf/mpdf/ttfonts/';
            }
            
            // Create mPDF instance with configuration
            $this->mpdf = new $mpdf_class([
                'mode' => $this->config['mode'],
                'format' => $this->config['format'],
                'orientation' => $this->config['orientation'],
                'margin_left' => $this->config['margin_left'],
                'margin_right' => $this->config['margin_right'],
                'margin_top' => $this->config['margin_top'],
                'margin_bottom' => $this->config['margin_bottom'],
                'margin_header' => $this->config['margin_header'],
                'margin_footer' => $this->config['margin_footer'],
                'default_font' => $this->config['default_font'],
                'default_font_size' => $this->config['default_font_size'],
                'tempDir' => self::get_temp_dir(),
                'fontDir' => [$font_dir],
                'autoScriptToLang' => true,
                'baseScript' => 1,
                'autoLangToFont' => true,
            ]);
            
            // Set document properties
            $this->mpdf->SetTitle('Engagement Letter');
            $this->mpdf->SetAuthor('Studio Legale Metta');
            $this->mpdf->SetCreator('Engagement Letter System');
            
            // Enable auto language detection for proper character handling
            if ($this->config['auto_language_detection']) {
                $this->mpdf->autoLangToFont = true;
            }
            
            // Enable line break preservation
            $this->mpdf->use_kwt = true;
            $this->mpdf->keep_table_proportions = true;
            
            return true;
            
        } catch (\Exception $e) {
            error_log('EL mPDF Generator: Failed to initialize - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set up HTML headers and footers with images and page numbers
     * 
     * @param array $assembled_data Assembled engagement letter data
     */
    private function setup_headers_footers($assembled_data) {
        // Extract boilerplate content
        $letterhead = $assembled_data['boilerplate']['letterhead'] ?? '';
        $firm_footer = $assembled_data['boilerplate']['firm_footer'] ?? '';
        $reference = $assembled_data['meta']['reference'] ?? '';
        
        // Set HTML header with letterhead
        if (!empty($letterhead)) {
            // Process images to ensure they have absolute URLs
            $letterhead = $this->process_images_for_mpdf($letterhead);
            
            $header_html = '<div style="text-align: center; font-family: \'Times New Roman\', Times, serif;">' . 
                          $letterhead . 
                          '</div>';
            $this->mpdf->SetHTMLHeader($header_html);
        }
        
        // Set HTML footer with ONLY reference, page numbers, and firm details
        // NOTE: footer_content (general terms) is added as a body block, not in footer!
        $footer_html = '<div style="font-family: \'Times New Roman\', Times, serif; font-size: 10pt;">';
        
        // Add page numbers and reference
        $footer_html .= '<table width="100%" style="border-top: 1px solid #ccc; padding-top: 5px; font-size: 9pt;">
            <tr>
                <td width="33%" style="text-align: left;">' . esc_html($reference) . '</td>
                <td width="34%" style="text-align: center;">Page {PAGENO} of {nbpg}</td>
                <td width="33%" style="text-align: right;">' . date('d/m/Y') . '</td>
            </tr>
        </table>';
        
        // Add firm footer if present
        if (!empty($firm_footer)) {
            // Process images in footer too
            $firm_footer = $this->process_images_for_mpdf($firm_footer);
            
            $footer_html .= '<div style="text-align: center; font-size: 8pt; margin-top: 5px;">' . 
                           $firm_footer . 
                           '</div>';
        }
        
        $footer_html .= '</div>';
        
        $this->mpdf->SetHTMLFooter($footer_html);
    }
    
    /**
     * Process images in HTML content for mPDF
     * Converts relative URLs to absolute URLs and ensures HTTPS
     * 
     * @param string $html HTML content with images
     * @return string Processed HTML
     */
    private function process_images_for_mpdf($html) {
        if (empty($html)) {
            return $html;
        }
        
        // Get site URL
        $site_url = get_site_url();
        $upload_dir = wp_upload_dir();
        $upload_url = $upload_dir['baseurl'];
        
        // Log original HTML for debugging
        error_log('EL Image Processing - Original HTML: ' . substr($html, 0, 500));
        
        // Pattern to match img tags
        $pattern = '/<img([^>]*?)src=["\']([^"\']+)["\']([^>]*?)>/i';
        
        $processed_count = 0;
        $html = preg_replace_callback($pattern, function($matches) use ($site_url, $upload_url, &$processed_count) {
            $before = $matches[1];
            $src = $matches[2];
            $after = $matches[3];
            $original_src = $src;
            
            // First, convert relative URLs to absolute
            if (strpos($src, 'http') !== 0) {
                // Handle different relative URL formats
                if (strpos($src, '/') === 0) {
                    // Absolute path: /wp-content/uploads/...
                    $src = $site_url . $src;
                } elseif (strpos($src, 'wp-content') === 0) {
                    // Relative path starting with wp-content
                    $src = $site_url . '/' . $src;
                } else {
                    // Other relative paths
                    $src = $upload_url . '/' . $src;
                }
            }
            
            // CRITICAL: Force HTTPS for all image URLs
            // Mixed content (HTTP images on HTTPS site) can cause mPDF to fail
            $src = str_replace('http://', 'https://', $src);
            
            // Remove loading and decoding attributes that mPDF doesn't support
            $before = preg_replace('/\s*(loading|decoding)=["\'][^"\']*["\']/i', '', $before);
            $after = preg_replace('/\s*(loading|decoding)=["\'][^"\']*["\']/i', '', $after);
            
            // Remove srcset attribute as mPDF doesn't support it
            $after = preg_replace('/\s*srcset=["\'][^"\']*["\']/i', '', $after);
            
            if ($original_src !== $src) {
                error_log(sprintf('EL Image Processing: %s → %s', $original_src, $src));
                $processed_count++;
            }
            
            // Return img tag with absolute HTTPS URL
            return sprintf('<img%ssrc="%s"%s>', $before, esc_url($src), $after);
        }, $html);
        
        error_log(sprintf('EL Image Processing: Processed %d images', $processed_count));
        
        return $html;
    }
    
    /**
     * Get temporary directory for mPDF
     * 
     * @return string Temp directory path
     */
    private static function get_temp_dir() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/el-temp';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
            
            // Add .htaccess to protect directory
            $htaccess = $temp_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
            
            // Add index.php
            $index = $temp_dir . '/index.php';
            if (!file_exists($index)) {
                file_put_contents($index, "<?php // Silence is golden\n");
            }
        }
        
        return $temp_dir;
    }
    
    /**
     * Generate PDF from assembled data
     * 
     * @param array $assembled_data Data from EL_Print_Data_Assembler
     * @param bool $include_page_signatures Whether to add signature lines to each page
     * @param string $signature_format Format for signature line
     * @return array Result with 'success', 'pdf_path' or 'error'
     */
    public function generate_from_data($assembled_data, $include_page_signatures = false, $signature_format = '') {
        // Validate input
        if (is_wp_error($assembled_data)) {
            return [
                'success' => false,
                'error' => $assembled_data->get_error_message(),
            ];
        }
        
        // Initialize mPDF
        if (!$this->init()) {
            return [
                'success' => false,
                'error' => 'Failed to initialize PDF generator. Please ensure Gravity PDF is installed.',
            ];
        }
        
        try {
            // Create content blocks
            $blocks = EL_Content_Blocks::create_blocks_from_data($assembled_data, $include_page_signatures);
            
            // Paginate
            $engine = new EL_Page_Break_Engine($include_page_signatures, $signature_format);
            $paginated = $engine->paginate($blocks);
            
            // Set up headers and footers with images and page numbers
            $this->setup_headers_footers($assembled_data);
            
            // Generate HTML
            $html = $this->build_full_html($paginated, $assembled_data);
            
            // Write to mPDF
            $this->mpdf->WriteHTML($html);
            
            // Generate output path
            $reference = $assembled_data['meta']['reference'];
            $pdf_path = $this->get_pdf_path($reference);
            
            // Output PDF file
            $destination = $this->destination_class;
            $this->mpdf->Output($pdf_path, $destination::FILE);
            
            // Verify file was created
            if (!file_exists($pdf_path)) {
                return [
                    'success' => false,
                    'error' => 'PDF file was not created',
                ];
            }
            
            return [
                'success' => true,
                'pdf_path' => $pdf_path,
                'reference' => $reference,
                'total_pages' => $paginated['total_pages'],
                'stats' => $paginated['stats'],
            ];
            
        } catch (\Exception $e) {
            error_log('EL mPDF Generator: Generation failed - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'PDF generation failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate PDF and return as string (for streaming)
     * 
     * @param array $assembled_data Data from EL_Print_Data_Assembler
     * @param bool $include_page_signatures Whether to add signature lines
     * @param string $signature_format Format for signature line
     * @return string|false PDF content or false on failure
     */
    public function generate_string($assembled_data, $include_page_signatures = false, $signature_format = '') {
        if (is_wp_error($assembled_data)) {
            return false;
        }
        
        if (!$this->init()) {
            return false;
        }
        
        try {
            $blocks = EL_Content_Blocks::create_blocks_from_data($assembled_data, $include_page_signatures);
            $engine = new EL_Page_Break_Engine($include_page_signatures, $signature_format);
            $paginated = $engine->paginate($blocks);
            $html = $this->build_full_html($paginated, $assembled_data);
            
            $this->mpdf->WriteHTML($html);
            
            $destination = $this->destination_class;
            return $this->mpdf->Output('', $destination::STRING_RETURN);
            
        } catch (\Exception $e) {
            error_log('EL mPDF Generator: String generation failed - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Stream PDF to browser for download
     * 
     * @param array $assembled_data Data from EL_Print_Data_Assembler
     * @param string $filename Download filename
     * @param bool $include_page_signatures Whether to add signature lines
     * @param string $signature_format Format for signature line
     * @return bool True on success (exits script)
     */
    public function stream_download($assembled_data, $filename = '', $include_page_signatures = false, $signature_format = '') {
        if (is_wp_error($assembled_data)) {
            return false;
        }
        
        if (!$this->init()) {
            return false;
        }
        
        try {
            $blocks = EL_Content_Blocks::create_blocks_from_data($assembled_data, $include_page_signatures);
            $engine = new EL_Page_Break_Engine($include_page_signatures, $signature_format);
            $paginated = $engine->paginate($blocks);
            $html = $this->build_full_html($paginated, $assembled_data);
            
            $this->mpdf->WriteHTML($html);
            
            if (empty($filename)) {
                $filename = 'engagement-letter-' . $assembled_data['meta']['reference'] . '.pdf';
            }
            
            $destination = $this->destination_class;
            $this->mpdf->Output($filename, $destination::DOWNLOAD);
            exit;
            
        } catch (\Exception $e) {
            error_log('EL mPDF Generator: Stream download failed - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Stream PDF to browser for inline viewing
     * 
     * @param array $assembled_data Data from EL_Print_Data_Assembler
     * @param string $filename Filename for browser title
     * @param bool $include_page_signatures Whether to add signature lines
     * @param string $signature_format Format for signature line
     * @return bool True on success (exits script)
     */
    public function stream_inline($assembled_data, $filename = '', $include_page_signatures = false, $signature_format = '') {
        if (is_wp_error($assembled_data)) {
            return false;
        }
        
        if (!$this->init()) {
            return false;
        }
        
        try {
            $blocks = EL_Content_Blocks::create_blocks_from_data($assembled_data, $include_page_signatures);
            $engine = new EL_Page_Break_Engine($include_page_signatures, $signature_format);
            $paginated = $engine->paginate($blocks);
            $html = $this->build_full_html($paginated, $assembled_data);
            
            $this->mpdf->WriteHTML($html);
            
            if (empty($filename)) {
                $filename = 'engagement-letter-' . $assembled_data['meta']['reference'] . '.pdf';
            }
            
            $destination = $this->destination_class;
            $this->mpdf->Output($filename, $destination::INLINE);
            exit;
            
        } catch (\Exception $e) {
            error_log('EL mPDF Generator: Stream inline failed - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build full HTML document for mPDF
     * 
     * @param array $paginated Paginated result from EL_Page_Break_Engine
     * @param array $assembled_data Original assembled data
     * @return string Complete HTML document
     */
    private function build_full_html($paginated, $assembled_data) {
        $css = $this->get_document_css();
        $engine = new EL_Page_Break_Engine($paginated['include_signatures'], $paginated['signature_format']);
        $body_html = $engine->render_html($paginated);
        
        // Process images in body content
        $body_html = $this->process_images_for_mpdf($body_html);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Engagement Letter - ' . esc_html($assembled_data['meta']['reference']) . '</title>
    <style>' . $css . '</style>
</head>
<body>
' . $body_html . '
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Get CSS for PDF document
     * 
     * @return string CSS
     */
    private function get_document_css() {
        return '
/* Base typography - Force Times New Roman */
body {
    font-family: "Times New Roman", Times, serif !important;
    font-size: 12pt;
    line-height: 1.4;
    color: #000;
    margin: 0;
    padding: 0;
}

* {
    font-family: "Times New Roman", Times, serif !important;
}

/* Preserve line breaks and spacing */
p {
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Page container - mPDF handles actual page breaks */
.el-page {
    position: relative;
}

/* Headings */
h1, .el-block-h1 {
    font-size: 18pt;
    font-weight: bold;
    margin: 1.5em 0 0.5em 0;
    page-break-after: avoid;
}

h2, .el-block-h2 {
    font-size: 14pt;
    font-weight: bold;
    margin: 1.2em 0 0.4em 0;
    page-break-after: avoid;
}

h3, .el-block-h3 {
    font-size: 12pt;
    font-weight: bold;
    margin: 1em 0 0.3em 0;
    page-break-after: avoid;
}

/* Paragraphs */
p, .el-block-paragraph {
    margin: 0 0 1em 0;
    text-align: justify;
}

/* Block elements */
.el-block {
    margin-bottom: 1em;
}

/* Keep together utility */
.el-keep-together {
    page-break-inside: avoid;
}

/* Signature block - NEVER break */
.el-block-signature {
    page-break-inside: avoid;
    margin-top: 2em;
}

/* Footer */
.el-block-footer {
    font-size: 8pt;
    color: #666;
    border-top: 1px solid #ccc;
    padding-top: 1em;
    margin-top: 2em;
}

/* Page signature line */
.el-page-signature {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 10pt;
    color: #333;
    padding-top: 1em;
    border-top: 1px dotted #ccc;
}

/* Opening section two-column layout */
.el-opening-columns {
    width: 100%;
    margin-bottom: 2em;
}

.el-opening-columns:after {
    content: "";
    display: table;
    clear: both;
}

.el-opening-left {
    float: left;
    width: 48%;
}

.el-opening-right {
    float: right;
    width: 48%;
    text-align: right;
}

/* Fee structure */
.el-fee-line {
    margin: 0.5em 0;
}

.el-fee-structure {
    background: #f9f9f9;
    padding: 1em;
    border: 1px solid #ddd;
    margin: 1em 0;
}

/* Totals table */
.el-totals-table {
    width: 100%;
    max-width: 400px;
    margin: 1em 0;
    border-collapse: collapse;
}

.el-totals-table td {
    padding: 0.3em 0.5em;
    border-bottom: 1px solid #eee;
}

.el-totals-table .el-amount {
    text-align: right;
    font-weight: bold;
}

/* Clauses */
.el-clause-title {
    font-weight: bold;
    margin-bottom: 0.3em;
}

.el-clause-body {
    margin-left: 1.5em;
    margin-bottom: 1em;
}

/* Annexes */
.el-annex-title {
    font-size: 14pt;
    font-weight: bold;
    margin-top: 0;
    border-bottom: 2px solid #000;
    padding-bottom: 0.3em;
}

.el-annex-body {
    margin-top: 1em;
}

/* Footer notes */
.el-footer-notes {
    font-size: 9pt;
    color: #666;
    margin-top: 1em;
    padding-top: 0.5em;
    border-top: 1px dotted #ccc;
}

/* Product intro */
.el-product-intro {
    font-style: italic;
    margin-bottom: 1em;
}

/* Letterhead */
.el-block-letterhead {
    text-align: center;
    margin-bottom: 2em;
    padding-bottom: 1em;
    border-bottom: 2px solid #333;
}

.el-block-letterhead img {
    max-width: 100%;
    height: auto;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 1em 0;
    page-break-inside: avoid;
}

th, td {
    border: 1px solid #ccc;
    padding: 0.5em;
    text-align: left;
}

th {
    background: #f5f5f5;
    font-weight: bold;
}

/* Lists */
ul, ol {
    margin: 0.5em 0 1em 1.5em;
    padding: 0;
}

li {
    margin-bottom: 0.3em;
}

/* Dotted fields for fillable */
.el-fillable input[type="text"],
.dotty:empty::after {
    content: "................................................";
    color: #999;
}

/* Images - ensure they display properly in PDF */
img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0.5em 0;
}

/* Centered images */
.aligncenter {
    display: block;
    margin-left: auto;
    margin-right: auto;
}

/* Left/Right aligned images */
.alignleft {
    float: left;
    margin: 0 1em 0.5em 0;
}

.alignright {
    float: right;
    margin: 0 0 0.5em 1em;
}

/* Print adjustments */
@media print {
    .el-page-signature {
        position: running(footer);
    }
}
';
    }
    
    /**
     * Get PDF storage path
     * 
     * @param string $reference Document reference
     * @return string Full path to PDF file
     */
    private function get_pdf_path($reference) {
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/engagement-letters/' . date('Y/m');
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
            
            // Protect directory
            $htaccess = dirname($pdf_dir) . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
        }
        
        return $pdf_dir . '/el-' . sanitize_file_name($reference) . '.pdf';
    }
    
    /**
     * Get URL for generated PDF (for secure viewer)
     * 
     * @param string $pdf_path Full path to PDF
     * @return string URL (relative to uploads)
     */
    public static function get_pdf_url($pdf_path) {
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $pdf_path);
        
        return $upload_dir['baseurl'] . $relative_path;
    }
    
    /**
     * Delete generated PDF file
     * 
     * @param string $pdf_path Path to PDF
     * @return bool True if deleted
     */
    public static function delete_pdf($pdf_path) {
        if (file_exists($pdf_path)) {
            return unlink($pdf_path);
        }
        return false;
    }
    
    /**
     * Clean up old temporary files
     * 
     * @param int $max_age_hours Maximum age in hours (default 24)
     * @return int Number of files deleted
     */
    public static function cleanup_temp_files($max_age_hours = 24) {
        $temp_dir = self::get_temp_dir();
        $deleted = 0;
        $max_age_seconds = $max_age_hours * 3600;
        
        if (!is_dir($temp_dir)) {
            return 0;
        }
        
        $files = glob($temp_dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.htaccess' && basename($file) !== 'index.php') {
                if (time() - filemtime($file) > $max_age_seconds) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * Set document metadata
     * 
     * @param string $title Document title
     * @param string $author Author name
     * @param string $subject Subject
     * @param string $keywords Keywords
     */
    public function set_metadata($title = '', $author = '', $subject = '', $keywords = '') {
        if ($this->mpdf === null) {
            return;
        }
        
        if ($title) {
            $this->mpdf->SetTitle($title);
        }
        if ($author) {
            $this->mpdf->SetAuthor($author);
        }
        if ($subject) {
            $this->mpdf->SetSubject($subject);
        }
        if ($keywords) {
            $this->mpdf->SetKeywords($keywords);
        }
    }
    
    /**
     * Add watermark to PDF
     * 
     * @param string $text Watermark text
     * @param float $alpha Transparency (0-1)
     */
    public function add_watermark($text, $alpha = 0.1) {
        if ($this->mpdf === null) {
            return;
        }
        
        $this->mpdf->SetWatermarkText($text);
        $this->mpdf->watermarkTextAlpha = $alpha;
        $this->mpdf->showWatermarkText = true;
    }
    
    /**
     * Set PDF password protection
     * 
     * @param string $user_password Password to open document
     * @param string $owner_password Password for full access
     * @param array $permissions Allowed permissions
     */
    public function set_protection($user_password = '', $owner_password = '', $permissions = []) {
        if ($this->mpdf === null) {
            return;
        }
        
        $default_permissions = ['copy', 'print'];
        $permissions = !empty($permissions) ? $permissions : $default_permissions;
        
        $this->mpdf->SetProtection($permissions, $user_password, $owner_password);
    }
}
