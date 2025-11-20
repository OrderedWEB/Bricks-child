<?php
/**
 * Pagination Handler for Enhanced Print Editor
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Pagination_Handler {
    
    /**
     * Check if paper-only mode is enabled
     */
    public static function is_paper_only($pdf_data) {
        // Check if paper_only flag is set in PDF data
        if (isset($pdf_data['paper_only'])) {
            return $pdf_data['paper_only'] == 1;
        }
        
        // Check default setting
        return get_option('el_default_paper_only', '0') == '1';
    }
    
    /**
     * Paginate content with optional page signatures
     */
    public static function paginate_content($html, $options = []) {
        $defaults = [
            'paper_only' => false,
            'add_page_signatures' => false,
            'signature_format' => 'Client signature …………..……………………… Date ………… Page %d/%d',
            'lines_per_page' => 45,
            'min_lines_per_page' => 2,
            'force_new_page_sections' => true,
            'keep_signatures_together' => true
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        // If not paper-only, return original HTML
        if (!$options['paper_only']) {
            return [
                'html' => $html,
                'page_count' => 1,
                'paginated' => false
            ];
        }
        
        // Create DOMDocument for HTML parsing
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        
        // Wrap content in UTF-8 encoding
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML('<div id="el-content-wrapper">' . $html . '</div>');
        libxml_clear_errors();
        
        // Process pagination
        $pages = self::split_into_pages($dom, $options);
        
        // Build paginated HTML
        $paginated_html = '';
        $total_pages = count($pages);
        
        foreach ($pages as $index => $page_content) {
            $page_num = $index + 1;
            
            // Add page wrapper
         $paginated_html .= sprintf(
    '<div class="el-page" data-page="%d">',
    $page_num
);
            
            // Add page content
            $paginated_html .= $page_content;
            
            // Add page signature if enabled
            if ($options['add_page_signatures']) {
                $signature_text = sprintf($options['signature_format'], $page_num, $total_pages);
               $paginated_html .= sprintf(
    '<div class="el-page-signature">%s</div>',
    esc_html($signature_text)
);
            }
            
            $paginated_html .= '</div>';
        }
        
        return [
            'html' => $paginated_html,
            'page_count' => $total_pages,
            'paginated' => true
        ];
    }
    
private static function split_into_pages($dom, $options) {
    // Get the body
    $body = $dom->getElementsByTagName('body')->item(0);
    if (!$body) {
        return [$dom->saveHTML()];
    }
    
    // Convert entire body to string
    $body_html = '';
    foreach ($body->childNodes as $child) {
        $body_html .= $dom->saveHTML($child);
    }
    
    // FOR NOW: Return everything as single page
    return [$body_html];
}  /**
     * Estimate number of lines for content
     */
    private static function estimate_lines($html) {
        // Strip HTML tags for estimation
        $text = strip_tags($html);
        
        // Estimate based on character count
        // Assuming average of 80 characters per line
        $char_count = strlen($text);
        $estimated_lines = ceil($char_count / 80);
        
        // Account for block elements
        $block_count = substr_count($html, '</p>') + 
                      substr_count($html, '</div>') + 
                      substr_count($html, '</h') +
                      substr_count($html, '</li>') +
                      substr_count($html, '<br');
        
        return $estimated_lines + $block_count;
    }
    
    /**
     * Check if node is a section header
     */
    private static function is_section_header($node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }
        
        $tag_name = strtolower($node->tagName);
        return in_array($tag_name, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
    }
    
    /**
     * Remove pagination markers from content
     */
    public static function remove_pagination_markers($html) {
        // Remove page wrappers
        $html = preg_replace('/<div class="el-page"[^>]*>/i', '', $html);
        $html = str_replace('</div>', '', $html);
        
        // Remove page signatures
        $html = preg_replace('/<div class="el-page-signature"[^>]*>.*?<\/div>/is', '', $html);
        
        // Clean up extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        return trim($html);
    }
    
    /**
     * Generate print CSS for paginated content
     */
    public static function generate_print_css() {
        return '
        @media print {
            .el-page {
                page-break-after: always;
                min-height: 297mm;
                padding: 20mm;
                box-sizing: border-box;
                position: relative;
            }
            
            .el-page:last-child {
                page-break-after: avoid;
            }
            
            .el-page-signature {
                position: absolute;
                bottom: 20mm;
                left: 20mm;
                right: 20mm;
                font-size: 10pt;
                color: #333;
            }
            
            .el-editor-toolbar,
            .el-page-info-bar {
                display: none !important;
            }
            
            @page {
                size: A4;
                margin: 0;
            }
        }
        ';
    }
}