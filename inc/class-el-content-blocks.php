<?php
/**
 * Content Block Handler for Engagement Letter Pagination
 * 
 * Defines block types and calculates heights for the pagination engine.
 * Works with mPDF to produce predictable page layouts.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * TYPOGRAPHY CONSTANTS (A4 @ 12pt Times New Roman):
 *   - Page height: 297mm (usable: ~247mm with 25mm margins)
 *   - Line height: 1.4 (16.8pt per line)
 *   - Characters per line: ~80 (at 12pt with 15mm side margins)
 *   - Lines per page: ~52 (without signature line)
 *   - Lines per page: ~50 (with signature line)
 *   - Buffer zone: 4 lines (safety margin for mPDF rendering variance)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Content_Blocks {
    
    /**
     * Page dimensions (A4 in mm)
     */
    const PAGE_WIDTH_MM = 210;
    const PAGE_HEIGHT_MM = 297;
    
    /**
     * Margins (mm)
     */
    const MARGIN_TOP = 35;
    const MARGIN_BOTTOM = 35;
    const MARGIN_LEFT = 15;
    const MARGIN_RIGHT = 15;
    
    /**
     * Typography settings
     */
    const BASE_FONT_SIZE_PT = 12;
    const LINE_HEIGHT_RATIO = 1.4;
    const CHARS_PER_LINE = 80;
    
    /**
     * Calculated line metrics
     */
    const LINE_HEIGHT_PT = 16.8;  // 12pt * 1.4
    const LINE_HEIGHT_MM = 5.93;  // 16.8pt / 72 * 25.4
    
    /**
     * Page capacity
     */
    const USABLE_HEIGHT_MM = 247;  // 297 - 25 - 25
    const LINES_PER_PAGE = 35;
    const LINES_PER_PAGE_WITH_SIGNATURE = 33;
    const BUFFER_ZONE_LINES = 4;
    
    /**
     * Block types
     */
    const BLOCK_LETTERHEAD = 'letterhead';
    const BLOCK_HEADING_1 = 'h1';
    const BLOCK_HEADING_2 = 'h2';
    const BLOCK_HEADING_3 = 'h3';
    const BLOCK_PARAGRAPH = 'paragraph';
    const BLOCK_LIST = 'list';
    const BLOCK_LIST_ITEM = 'list_item';
    const BLOCK_TABLE = 'table';
    const BLOCK_TABLE_ROW = 'table_row';
    const BLOCK_CLAUSE = 'clause';
    const BLOCK_ANNEX = 'annex';
    const BLOCK_SIGNATURE = 'signature';
    const BLOCK_FOOTER = 'footer';
    const BLOCK_PAGE_SIGNATURE = 'page_signature';
    const BLOCK_SPACER = 'spacer';
    const BLOCK_PAGE_BREAK = 'page_break';
    
    /**
     * Block type configurations
     * 
     * Each block type has:
     *   - base_lines: Minimum lines (for headers, spacing)
     *   - margin_before: Lines of space before
     *   - margin_after: Lines of space after
     *   - keep_with_next: Minimum lines that must follow on same page
     *   - splittable: Whether block can be split across pages
     *   - min_lines_if_split: Minimum lines on each page if split
     */
    private static $block_configs = [
        'letterhead' => [
            'base_lines' => 8,
            'margin_before' => 0,
            'margin_after' => 2,
            'keep_with_next' => 0,
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'h1' => [
            'base_lines' => 2,
            'margin_before' => 3,
            'margin_after' => 1,
            'keep_with_next' => 3,  // Must have 3 lines of content following
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'h2' => [
            'base_lines' => 1,
            'margin_before' => 2,
            'margin_after' => 1,
            'keep_with_next' => 2,  // Must have 2 lines of content following
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'h3' => [
            'base_lines' => 1,
            'margin_before' => 1,
            'margin_after' => 0,
            'keep_with_next' => 2,
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'paragraph' => [
            'base_lines' => 0,  // Calculated from content
            'margin_before' => 0,
            'margin_after' => 1,
            'keep_with_next' => 0,
            'splittable' => true,
            'min_lines_if_split' => 2,  // No orphans/widows
        ],
        'list' => [
            'base_lines' => 0,  // Sum of list items
            'margin_before' => 1,
            'margin_after' => 1,
            'keep_with_next' => 0,
            'splittable' => true,
            'min_lines_if_split' => 2,  // At least 2 items per page
        ],
        'list_item' => [
            'base_lines' => 1,
            'margin_before' => 0,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,  // Individual items don't split
            'min_lines_if_split' => 0,
        ],
        'table' => [
            'base_lines' => 0,  // Sum of rows + header
            'margin_before' => 1,
            'margin_after' => 1,
            'keep_with_next' => 0,
            'splittable' => true,
            'min_lines_if_split' => 3,  // Header + at least 2 rows
        ],
        'table_row' => [
            'base_lines' => 1,
            'margin_before' => 0,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,  // Never split a row
            'min_lines_if_split' => 0,
        ],
        'clause' => [
            'base_lines' => 0,  // Title + body calculated
            'margin_before' => 2,
            'margin_after' => 1,
            'keep_with_next' => 0,
            'splittable' => true,
            'min_lines_if_split' => 3,  // Title + 2 lines minimum
        ],
        'annex' => [
            'base_lines' => 0,
            'margin_before' => 3,
            'margin_after' => 1,
            'keep_with_next' => 2,
            'splittable' => true,
            'min_lines_if_split' => 3,
        ],
        'signature' => [
            'base_lines' => 12,  // Full signature block
            'margin_before' => 3,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,  // NEVER split signature block
            'min_lines_if_split' => 0,
        ],
        'footer' => [
            'base_lines' => 3,
            'margin_before' => 1,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'page_signature' => [
            'base_lines' => 2,  // Single line + spacing
            'margin_before' => 0,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'spacer' => [
            'base_lines' => 1,
            'margin_before' => 0,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,
            'min_lines_if_split' => 0,
        ],
        'page_break' => [
            'base_lines' => 0,
            'margin_before' => 0,
            'margin_after' => 0,
            'keep_with_next' => 0,
            'splittable' => false,
            'min_lines_if_split' => 0,
            'force_break' => true,
        ],
    ];
    
    /**
     * Create a content block
     * 
     * @param string $type Block type constant
     * @param string $content HTML content
     * @param array $options Additional options
     * @return array Block structure
     */
    public static function create_block($type, $content = '', $options = []) {
        $config = self::get_block_config($type);
        
        // Calculate content lines
        $content_lines = self::estimate_content_lines($content, $type);
        
        // Total lines including margins
        $total_lines = $config['base_lines'] + $content_lines + $config['margin_before'] + $config['margin_after'];
        
        return [
            'type' => $type,
            'content' => $content,
            'config' => $config,
            'lines' => [
                'content' => $content_lines,
                'base' => $config['base_lines'],
                'margin_before' => $config['margin_before'],
                'margin_after' => $config['margin_after'],
                'total' => $total_lines,
            ],
            'options' => wp_parse_args($options, [
                'id' => uniqid('block_'),
                'class' => '',
                'style' => '',
                'keep_together' => !$config['splittable'],
                'page_break_before' => false,
            ]),
            'meta' => [
                'created' => microtime(true),
                'height_mm' => $total_lines * self::LINE_HEIGHT_MM,
            ],
        ];
    }
    
    /**
     * Get block configuration
     * 
     * @param string $type Block type
     * @return array Configuration
     */
    public static function get_block_config($type) {
        return self::$block_configs[$type] ?? self::$block_configs['paragraph'];
    }
    
    /**
     * Estimate lines needed for content
     * 
     * @param string $content HTML content
     * @param string $type Block type for context
     * @return int Estimated line count
     */
    public static function estimate_content_lines($content, $type = 'paragraph') {
        if (empty($content)) {
            return 0;
        }
        
        // Strip HTML tags for character count
        $text = strip_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = trim($text);
        
        if (empty($text)) {
            return 0;
        }
        
        // Count characters
        $char_count = mb_strlen($text);
        
        // Base line estimate from characters
        $lines_from_chars = ceil($char_count / self::CHARS_PER_LINE);
        
        // Count explicit line breaks
        $explicit_breaks = substr_count($content, '<br') + 
                          substr_count($content, '</p>') + 
                          substr_count($content, '</div>') +
                          substr_count($content, '</li>') +
                          substr_count($content, "\n");
        
        // Count block elements that add vertical space
        $block_elements = substr_count($content, '</h1>') * 2 +
                         substr_count($content, '</h2>') * 2 +
                         substr_count($content, '</h3>') * 1 +
                         substr_count($content, '</ul>') +
                         substr_count($content, '</ol>') +
                         substr_count($content, '</table>');
        
        // Calculate total
        $estimated_lines = max($lines_from_chars, $explicit_breaks) + $block_elements;
        
        // Apply type-specific adjustments
        switch ($type) {
            case self::BLOCK_HEADING_1:
                $estimated_lines = max(2, $estimated_lines);
                break;
            case self::BLOCK_HEADING_2:
            case self::BLOCK_HEADING_3:
                $estimated_lines = max(1, $estimated_lines);
                break;
            case self::BLOCK_LIST_ITEM:
                // List items are at least 1 line
                $estimated_lines = max(1, ceil($char_count / (self::CHARS_PER_LINE - 5))); // Account for bullet indent
                break;
            case self::BLOCK_TABLE_ROW:
                // Table rows may wrap
                $estimated_lines = max(1, $estimated_lines);
                break;
        }
        
        return $estimated_lines;
    }
    
    /**
     * Create blocks from assembled print data
     * 
     * @param array $assembled_data Data from EL_Print_Data_Assembler
     * @param bool $include_page_signatures Whether to reserve space for page signatures
     * @return array Array of blocks
     */
    public static function create_blocks_from_data($assembled_data, $include_page_signatures = false) {
        $blocks = [];
        
        // NOTE: Letterhead is handled by mPDF SetHTMLHeader() in class-el-mpdf-generator.php
        // Do NOT add it as a body block or it will appear twice
        
        // 1. Opening section (top left/right combined)
        $opening_content = self::build_opening_section(
            $assembled_data['boilerplate']['opening_top_left'],
            $assembled_data['boilerplate']['opening_top_right'],
            $assembled_data['meta'],
            $assembled_data['client']
        );
        if (!empty($opening_content)) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $opening_content,
                ['id' => 'opening', 'class' => 'el-opening-section']
            );
        }
        
        // 3. Document title
        $blocks[] = self::create_block(
            self::BLOCK_HEADING_1,
            'Engagement Letter',
            ['id' => 'doc_title']
        );
        
        // 4. Reference and date line
        $ref_content = sprintf(
            '<p><strong>Date:</strong> %s<br><strong>Reference:</strong> %s</p>',
            esc_html($assembled_data['meta']['date']),
            esc_html($assembled_data['meta']['reference'])
        );
        $blocks[] = self::create_block(
            self::BLOCK_PARAGRAPH,
            $ref_content,
            ['id' => 'ref_date']
        );
        
// Products section - distinguish between main and optional products
if (!empty($assembled_data['products'])) {
    $main_product = null;
    $optional_products = [];
    
    // Separate main product from optional products
    foreach ($assembled_data['products'] as $product) {
        if (!empty($product['is_main']) || $main_product === null) {
            $main_product = $product;
        } else {
            $optional_products[] = $product;
        }
    }
    
    // MAIN PRODUCT STRUCTURE
    if ($main_product) {
        // 1. EL Introduction Text (PDF only)
        if (!empty($main_product['content']['introduction'])) {
            $intro_content = self::clean_wysiwyg_content($main_product['content']['introduction']);
            $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $intro_content, [
                'id' => 'main_product_intro',
                'product_id' => $main_product['id']
            ]);
        }
        
        // 2. Client Fillable PDF Text
        if (!empty($main_product['content']['client_fillable_text'])) {
            $fillable_content = self::clean_wysiwyg_content($main_product['content']['client_fillable_text']);
            $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $fillable_content, [
                'id' => 'main_product_fillable',
                'product_id' => $main_product['id']
            ]);
        }
        
        // 3. Service Plan Table
        if (!empty($main_product['content']['service_plan_table'])) {
            $table_content = self::clean_wysiwyg_content($main_product['content']['service_plan_table']);
            $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $table_content, [
                'id' => 'main_product_table',
                'product_id' => $main_product['id'],
                'keep_together' => true
            ]);
        }
    }
    
    // OPTIONAL PRODUCTS - just client_fillable_pdf_text for each
    foreach ($optional_products as $index => $optional_product) {
        if (!empty($optional_product['content']['client_fillable_text'])) {
            $optional_content = self::clean_wysiwyg_content($optional_product['content']['client_fillable_text']);
            $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $optional_content, [
                'id' => 'optional_product_' . $index,
                'product_id' => $optional_product['id']
            ]);
        }
    }
    
    // PDF FOOTER NOTES (from main product only)
    if ($main_product && !empty($main_product['content']['footer_notes'])) {
        $footer_notes = '<p style="font-size: 9pt; font-style: italic;">' . esc_html($main_product['content']['footer_notes']) . '</p>';
        $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $footer_notes, [
            'id' => 'pdf_footer_notes'
        ]);
    }
}
        
        // 6. Totals section
        $totals_content = self::build_totals_section($assembled_data['totals']);
        if (!empty($totals_content)) {
            $blocks[] = self::create_block(
                self::BLOCK_HEADING_2,
                'Fee Summary',
                ['id' => 'totals_heading']
            );
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $totals_content,
                ['id' => 'totals', 'class' => 'el-totals-section']
            );
        }
        
// 7. Footer content (General Terms) - Process WYSIWYG content properly
if (!empty($assembled_data['boilerplate']['footer_content'])) {
    $footer_content = self::clean_wysiwyg_content($assembled_data['boilerplate']['footer_content']);
    $blocks[] = self::create_block(
        self::BLOCK_FOOTER,
        $footer_content,
        ['id' => 'footer_content', 'class' => 'el-general-terms']
    );
}

// 8. Signature block - Process WYSIWYG content properly
if (!empty($assembled_data['boilerplate']['signature_block'])) {
    $signature_content = self::clean_wysiwyg_content($assembled_data['boilerplate']['signature_block']);
    $blocks[] = self::create_block(
        self::BLOCK_SIGNATURE,
        $signature_content,
        ['id' => 'signature_block', 'keep_together' => true]
    );
}
           
        return $blocks;
    }
    
    /**
     * Create blocks for a single product
     * 
     * @param array $product Product data
     * @param int $index Product index
     * @return array Array of blocks
     */
    private static function create_product_blocks($product, $index) {
        $blocks = [];
        $prefix = 'product_' . $index;
        
        // Introduction text (if present)
        if (!empty($product['content']['introduction'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $product['content']['introduction'],
                ['id' => $prefix . '_intro', 'class' => 'el-product-intro']
            );
        }
        
        // Product title
        $blocks[] = self::create_block(
            self::BLOCK_HEADING_2,
            esc_html($product['content']['title']),
            ['id' => $prefix . '_title']
        );
        
        // Subtitle (if present)
        if (!empty($product['content']['subtitle'])) {
            $blocks[] = self::create_block(
                self::BLOCK_HEADING_3,
                esc_html($product['content']['subtitle']),
                ['id' => $prefix . '_subtitle']
            );
        }
        
        // Body text (formal PDF text)
        if (!empty($product['content']['body_text'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $product['content']['body_text'],
                ['id' => $prefix . '_body']
            );
        }
        
        // Client fillable text (paper version)
        if (!empty($product['content']['client_fillable_text'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $product['content']['client_fillable_text'],
                ['id' => $prefix . '_fillable', 'class' => 'el-fillable']
            );
        }
        
        // Fee structure
        if (!empty($product['content']['fee_structure'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $product['content']['fee_structure'],
                ['id' => $prefix . '_fees', 'class' => 'el-fee-structure']
            );
        }
        
        // Fee line
        $fee_line = sprintf(
            '<p class="el-fee-line"><strong>Engagement Fee:</strong> %s</p>',
            $product['fees']['engagement_fee_formatted']
        );
        if ($product['fees']['expected_cost'] > 0) {
            $fee_line .= sprintf(
                '<p class="el-fee-line"><strong>Expected Total Cost:</strong> %s</p>',
                $product['fees']['expected_cost_formatted']
            );
        }
        $blocks[] = self::create_block(
            self::BLOCK_PARAGRAPH,
            $fee_line,
            ['id' => $prefix . '_fee_line']
        );
        
        // Clauses
        foreach ($product['clauses'] as $clause_index => $clause) {
            $clause_blocks = self::create_clause_blocks($clause, $prefix . '_clause_' . $clause_index);
            $blocks = array_merge($blocks, $clause_blocks);
        }
        
        // Annexes
        foreach ($product['annexes'] as $annex_index => $annex) {
            $annex_blocks = self::create_annex_blocks($annex, $prefix . '_annex_' . $annex_index);
            $blocks = array_merge($blocks, $annex_blocks);
        }
        
        // Footer notes
        if (!empty($product['content']['footer_notes'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                '<small>' . esc_html($product['content']['footer_notes']) . '</small>',
                ['id' => $prefix . '_notes', 'class' => 'el-footer-notes']
            );
        }
        
        return $blocks;
    }
    
    /**
     * Create blocks for a clause
     * 
     * @param array $clause Clause data
     * @param string $prefix ID prefix
     * @return array Array of blocks
     */
    private static function create_clause_blocks($clause, $prefix) {
        $blocks = [];
        
        // Check for page break before
        if (!empty($clause['page_break_before'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PAGE_BREAK,
                '',
                ['id' => $prefix . '_break']
            );
        }
        
        // Clause title with number
        if (!empty($clause['title'])) {
            $title = $clause['index'] . '. ' . $clause['title'];
            $blocks[] = self::create_block(
                self::BLOCK_HEADING_3,
                esc_html($title),
                ['id' => $prefix . '_title', 'class' => 'el-clause-title']
            );
        }
        
        // Clause body
        if (!empty($clause['body'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $clause['body'],
                ['id' => $prefix . '_body', 'class' => 'el-clause-body']
            );
        }
        
        return $blocks;
    }
    
    /**
     * Create blocks for an annex
     * 
     * @param array $annex Annex data
     * @param string $prefix ID prefix
     * @return array Array of blocks
     */
    private static function create_annex_blocks($annex, $prefix) {
        $blocks = [];
        
        // Annexes typically start on new page
        $blocks[] = self::create_block(
            self::BLOCK_PAGE_BREAK,
            '',
            ['id' => $prefix . '_break']
        );
        
        // Annex title
        if (!empty($annex['title'])) {
            $title = 'Schedule ' . self::number_to_letter($annex['index']) . ': ' . $annex['title'];
            $blocks[] = self::create_block(
                self::BLOCK_HEADING_2,
                esc_html($title),
                ['id' => $prefix . '_title', 'class' => 'el-annex-title']
            );
        }
        
        // Annex body
        if (!empty($annex['body'])) {
            $blocks[] = self::create_block(
                self::BLOCK_PARAGRAPH,
                $annex['body'],
                ['id' => $prefix . '_body', 'class' => 'el-annex-body']
            );
        }
        
        return $blocks;
    }
    
    /**
     * Build opening section HTML
     * 
     * @param string $top_left Left column content
     * @param string $top_right Right column content
     * @param array $meta Document metadata
     * @param array $client Client data
     * @return string HTML content
     */
    private static function build_opening_section($top_left, $top_right, $meta, $client) {
        $html = '<div class="el-opening-columns">';
        
        if (!empty($top_left)) {
            $html .= '<div class="el-opening-left">' . wpautop($top_left) . '</div>';
        }
        
        if (!empty($top_right)) {
            // Replace basic merge tags in top right
            $right_content = str_replace(
                ['{DATE}', '{REFERENCE}', '{CLIENT_NAME}'],
                [
                    esc_html($meta['date']),
                    esc_html($meta['reference']),
                    esc_html($client['name'])
                ],
                $top_right
            );
            $html .= '<div class="el-opening-right">' . wpautop($right_content) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Build totals section HTML
     * 
     * @param array $totals Totals data
     * @return string HTML content
     */
    private static function build_totals_section($totals) {
        $html = '<table class="el-totals-table">';
        $html .= '<tr><td><strong>Total Engagement Fee:</strong></td><td class="el-amount">' . $totals['engagement_fee_formatted'] . '</td></tr>';
        
        if ($totals['expected_cost'] > 0) {
            $html .= '<tr><td><strong>Estimated Total Cost:</strong></td><td class="el-amount">' . $totals['expected_cost_formatted'] . '</td></tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }
    
    /**
     * Convert number to letter (1=A, 2=B, etc.)
     * 
     * @param int $number Number to convert
     * @return string Letter
     */
    private static function number_to_letter($number) {
        return chr(64 + $number);  // A=65 in ASCII
    }
    
    /**
     * Calculate total lines for all blocks
     * 
     * @param array $blocks Array of blocks
     * @return int Total line count
     */
    public static function calculate_total_lines($blocks) {
        $total = 0;
        
        foreach ($blocks as $block) {
            $total += $block['lines']['total'];
        }
        
        return $total;
    }
    
    /**
     * Calculate total height in mm for all blocks
     * 
     * @param array $blocks Array of blocks
     * @return float Total height in mm
     */
    public static function calculate_total_height_mm($blocks) {
        $total = 0;
        
        foreach ($blocks as $block) {
            $total += $block['meta']['height_mm'];
        }
        
        return $total;
    }
    
    /**
     * Get estimated page count
     * 
     * @param array $blocks Array of blocks
     * @param bool $with_signatures Whether pages have signature lines
     * @return int Estimated page count
     */
    public static function estimate_page_count($blocks, $with_signatures = false) {
        $total_lines = self::calculate_total_lines($blocks);
        $lines_per_page = $with_signatures ? self::LINES_PER_PAGE_WITH_SIGNATURE : self::LINES_PER_PAGE;
        
        // Account for buffer zone
        $effective_lines = $lines_per_page - self::BUFFER_ZONE_LINES;
        
        return ceil($total_lines / $effective_lines);
    }
    
    /**
     * Debug output for blocks
     * 
     * @param array $blocks Array of blocks
     * @return string Debug output
     */
    public static function debug_blocks($blocks) {
        $output = "=== Content Blocks Debug ===\n\n";
        $output .= "Total blocks: " . count($blocks) . "\n";
        $output .= "Total lines: " . self::calculate_total_lines($blocks) . "\n";
        $output .= "Total height: " . round(self::calculate_total_height_mm($blocks), 1) . "mm\n";
        $output .= "Estimated pages: " . self::estimate_page_count($blocks) . "\n\n";
        
        foreach ($blocks as $i => $block) {
            $output .= sprintf(
                "%d. [%s] ID: %s, Lines: %d (content: %d, base: %d, margins: %d+%d)\n",
                $i + 1,
                $block['type'],
                $block['options']['id'],
                $block['lines']['total'],
                $block['lines']['content'],
                $block['lines']['base'],
                $block['lines']['margin_before'],
                $block['lines']['margin_after']
            );
            
            if (!empty($block['config']['keep_with_next'])) {
                $output .= "   → Keep with next: " . $block['config']['keep_with_next'] . " lines\n";
            }
            if (!$block['config']['splittable']) {
                $output .= "   → Non-splittable\n";
            }
        }
        
        return $output;
    }
    /**
 * Clean WYSIWYG content for PDF while preserving formatting
 * 
 * Keeps: bold, italic, underline, lists, headings, tables, links
 * Removes: WordPress-specific classes and attributes
 * 
 * @param string $content WYSIWYG HTML content
 * @return string Cleaned HTML
 */
private static function clean_wysiwyg_content($content) {
    if (empty($content)) {
        return '';
    }
    
    // Remove WordPress alignment classes
    $content = preg_replace('/class=["\']alignleft["\']/', '', $content);
    $content = preg_replace('/class=["\']alignright["\']/', '', $content);
    $content = preg_replace('/class=["\']aligncenter["\']/', '', $content);
    
    // Remove WordPress image classes
    $content = preg_replace('/class=["\']wp-image-\d+["\']/', '', $content);
    $content = preg_replace('/class=["\']size-(full|medium|large|thumbnail)["\']/', '', $content);
    $content = preg_replace('/class=["\']attachment-\w+["\']/', '', $content);
    
    // Remove WordPress caption classes
    $content = preg_replace('/class=["\']wp-caption["\']/', '', $content);
    $content = preg_replace('/class=["\']wp-caption-text["\']/', '', $content);
    
    // Remove empty class attributes
    $content = preg_replace('/\s+class=["\']["\']/', '', $content);
    
    // Remove WordPress shortcodes
    $content = preg_replace('/\[.*?\]/', '', $content);
    
    // Ensure proper paragraph spacing (only if content isn't already wrapped)
    if (strpos($content, '<p>') === false && strpos($content, '<ul>') === false && 
        strpos($content, '<ol>') === false && strpos($content, '<table>') === false &&
        strpos($content, '<h') === false) {
        // Only apply wpautop if there are no block-level elements
        $content = wpautop($content);
    }
    
    // Clean up multiple consecutive line breaks
    $content = preg_replace('/(<br\s*\/?>[\s]*){3,}/', '<br><br>', $content);
    
    return $content;
}
}
