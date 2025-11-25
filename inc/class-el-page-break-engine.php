<?php
/**
 * Page Break Decision Engine for Engagement Letters
 * 
 * Implements intelligent pagination rules to produce professional legal documents.
 * Works with EL_Content_Blocks to decide where page breaks should occur.
 * 
 * @package Starne_Consulting_EL
 * @since 1.0.0
 * 
 * PAGINATION RULES IMPLEMENTED:
 * 
 * Rule 1: Keep heading with following text
 *   - Heading + at least 2 lines of first paragraph on same page
 *   - If not possible, move heading to next page
 * 
 * Rule 2: Major headings start new page (optional)
 *   - Top-level sections may start on new page
 *   - Unless following section also fits on same page
 * 
 * Rule 3: Minimum lines at top/bottom (widows/orphans)
 *   - At least 2 lines at bottom before break
 *   - At least 2 lines at top after break
 *   - Short paragraphs (2-3 lines) kept together
 * 
 * Rule 4: List intro + first item together
 *   - Intro sentence and first bullet on same page
 * 
 * Rule 5: Keep list chunks together
 *   - At least 2 bullets per page
 *   - Short lists (≤3 items) kept on one page
 * 
 * Rule 6: Table rows together
 *   - Never split a single row
 *   - Small tables (≤8 rows) kept together
 * 
 * Rule 7: Captions with objects
 *   - Caption + first part of table/figure on same page
 * 
 * Rule 8: Signature blocks stay together
 *   - NEVER split signature block
 *   - Move entire block to new page if needed
 * 
 * Rule 9: Key notices/warnings
 *   - Critical clauses not split mid-sentence
 *   - Keep at least 3 lines or none
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Page_Break_Engine {
    
    /**
     * Lines per page (from Content Blocks)
     */
    private $lines_per_page;
    
    /**
     * Buffer zone lines
     */
    private $buffer_lines;
    
    /**
     * Effective lines per page (after buffer)
     */
    private $effective_lines;
    
    /**
     * Whether to add page signature lines
     */
    private $include_page_signatures;
    
    /**
     * Page signature format
     */
    private $signature_format;
    
    /**
     * Current page tracking
     */
    private $pages = [];
    private $current_page = [];
    private $current_page_lines = 0;
    private $current_page_number = 1;
    
    /**
     * Block index tracking for look-ahead
     */
    private $blocks = [];
    private $block_index = 0;
    
    /**
     * Constructor
     * 
     * @param bool $include_page_signatures Whether to reserve space for page signatures
     * @param string $signature_format Format for page signature line
     */
    public function __construct($include_page_signatures = false, $signature_format = '') {
        $this->include_page_signatures = $include_page_signatures;
        $this->signature_format = $signature_format ?: 'Client signature …………..……………………… Date ………… Page %d/%d';
        
        // Set lines per page based on signature inclusion
        $this->lines_per_page = $include_page_signatures 
            ? EL_Content_Blocks::LINES_PER_PAGE_WITH_SIGNATURE 
            : EL_Content_Blocks::LINES_PER_PAGE;
        
        $this->buffer_lines = EL_Content_Blocks::BUFFER_ZONE_LINES;
        $this->effective_lines = $this->lines_per_page - $this->buffer_lines;
    }
    
    /**
     * Process blocks and determine page breaks
     * 
     * @param array $blocks Array of content blocks from EL_Content_Blocks
     * @return array Paginated structure with pages and blocks
     */
    public function paginate($blocks) {
        // Reset state
        $this->pages = [];
        $this->current_page = [];
        $this->current_page_lines = 0;
        $this->current_page_number = 1;
        $this->blocks = $blocks;
        $this->block_index = 0;
        
        // Process each block
        while ($this->block_index < count($this->blocks)) {
            $block = $this->blocks[$this->block_index];
            
            // Handle explicit page breaks
            if ($block['type'] === EL_Content_Blocks::BLOCK_PAGE_BREAK || 
                !empty($block['options']['page_break_before'])) {
                $this->finalize_current_page();
                $this->block_index++;
                continue;
            }
            
            // Calculate space needed for this block
            $space_needed = $this->calculate_space_needed($block);
            
            // Check if block fits on current page
            if ($this->will_fit($space_needed)) {
                $this->add_block_to_page($block);
            } else {
                // Determine best action
                $action = $this->decide_page_break_action($block, $space_needed);
                $this->execute_action($action, $block);
            }
            
            $this->block_index++;
        }
        
        // Finalize last page
        if (!empty($this->current_page)) {
            $this->finalize_current_page();
        }
        
        // Calculate total pages and update signature line placeholders
        $total_pages = count($this->pages);
        
        return [
            'pages' => $this->pages,
            'total_pages' => $total_pages,
            'include_signatures' => $this->include_page_signatures,
            'signature_format' => $this->signature_format,
            'stats' => $this->calculate_stats(),
        ];
    }
    
    /**
     * Calculate space needed for a block including keep-with-next requirements
     */
    private function calculate_space_needed($block) {
        $lines = $block['lines']['total'];
        $keep_with_next = $block['config']['keep_with_next'];
        
        if ($keep_with_next > 0) {
            $following_lines = $this->get_following_lines($keep_with_next);
            $lines += $following_lines;
        }
        
        return $lines;
    }
    
    /**
     * Get lines from following blocks up to limit
     */
    private function get_following_lines($limit) {
        $lines = 0;
        $i = $this->block_index + 1;
        
        while ($i < count($this->blocks) && $lines < $limit) {
            $next_block = $this->blocks[$i];
            
            if ($next_block['type'] === EL_Content_Blocks::BLOCK_PAGE_BREAK) {
                break;
            }
            
            $lines += $next_block['lines']['total'];
            $i++;
        }
        
        return min($lines, $limit);
    }
    
    /**
     * Check if lines will fit on current page
     */
    private function will_fit($lines) {
        return ($this->current_page_lines + $lines) <= $this->effective_lines;
    }
    
    /**
     * Get remaining lines on current page
     */
    private function get_remaining_lines() {
        return $this->effective_lines - $this->current_page_lines;
    }
    
    /**
     * Add block to current page
     */
    private function add_block_to_page($block) {
        $this->current_page[] = $block;
        $this->current_page_lines += $block['lines']['total'];
    }
    
    /**
     * Decide what action to take when block doesn't fit
     */
    private function decide_page_break_action($block, $space_needed) {
        $remaining = $this->get_remaining_lines();
        $block_lines = $block['lines']['total'];
        $config = $block['config'];
        
        // Rule 8: Signature blocks NEVER split
        if ($block['type'] === EL_Content_Blocks::BLOCK_SIGNATURE) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Non-splittable blocks move entirely to next page
        if (!$config['splittable']) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Rule 3: Check if we can split while respecting minimum lines
        $min_lines = $config['min_lines_if_split'];
        
        if ($remaining < $min_lines) {
            return ['action' => 'move_to_next_page'];
        }
        
        $lines_after_split = $block_lines - $remaining;
        if ($lines_after_split < $min_lines) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Rule 1 & 3: Check for orphan/widow issues
        if ($remaining <= 1 || $lines_after_split <= 1) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Safe to split
        return [
            'action' => 'split',
            'lines_on_current' => $remaining,
            'lines_on_next' => $lines_after_split,
        ];
    }
    
    /**
     * Execute the decided action
     */
    private function execute_action($action, $block) {
        switch ($action['action']) {
            case 'move_to_next_page':
                $this->finalize_current_page();
                $this->add_block_to_page($block);
                break;
                
            case 'split':
                $split_blocks = $this->split_block($block, $action['lines_on_current']);
                
                if ($split_blocks['first']) {
                    $this->add_block_to_page($split_blocks['first']);
                }
                
                $this->finalize_current_page();
                
                if ($split_blocks['second']) {
                    $this->add_block_to_page($split_blocks['second']);
                }
                break;
        }
    }
    
    /**
     * Split a block at specified line count
     */
    private function split_block($block, $split_at) {
        $type = $block['type'];
        
        if ($type === EL_Content_Blocks::BLOCK_PARAGRAPH) {
            return $this->split_paragraph($block, $split_at);
        }
        
        if ($type === EL_Content_Blocks::BLOCK_LIST) {
            return $this->split_list($block, $split_at);
        }
        
        if ($type === EL_Content_Blocks::BLOCK_TABLE) {
            return $this->split_table($block, $split_at);
        }
        
        if ($type === EL_Content_Blocks::BLOCK_CLAUSE) {
            return $this->split_clause($block, $split_at);
        }
        
        return ['first' => null, 'second' => $block];
    }
    
    /**
     * Split a paragraph block
     */
    private function split_paragraph($block, $split_at) {
        $content = $block['content'];
        $text = strip_tags($content);
        $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
        $split_char_pos = $split_at * $chars_per_line;
        
        $break_pos = $this->find_break_point($text, $split_char_pos);
        
        if ($break_pos === false || $break_pos >= strlen($text) - 50) {
            return ['first' => null, 'second' => $block];
        }
        
        $first_text = substr($text, 0, $break_pos);
        $second_text = substr($text, $break_pos);
        
        $first_block = EL_Content_Blocks::create_block(
            $block['type'],
            '<p>' . esc_html(trim($first_text)) . '</p>',
            array_merge($block['options'], ['id' => $block['options']['id'] . '_a'])
        );
        
        $second_block = EL_Content_Blocks::create_block(
            $block['type'],
            '<p>' . esc_html(trim($second_text)) . '</p>',
            array_merge($block['options'], ['id' => $block['options']['id'] . '_b'])
        );
        
        return ['first' => $first_block, 'second' => $second_block];
    }
    
    /**
     * Find a good break point in text
     */
    private function find_break_point($text, $target_pos) {
        $search_start = max(0, $target_pos - 100);
        $search_end = min(strlen($text), $target_pos + 100);
        $search_region = substr($text, $search_start, $search_end - $search_start);
        
        $patterns = ['. ', '? ', '! ', ".\n", "?\n", "!\n"];
        $best_pos = false;
        $best_distance = PHP_INT_MAX;
        
        foreach ($patterns as $pattern) {
            $pos = strrpos(substr($search_region, 0, $target_pos - $search_start + 50), $pattern);
            if ($pos !== false) {
                $actual_pos = $search_start + $pos + strlen($pattern);
                $distance = abs($actual_pos - $target_pos);
                if ($distance < $best_distance) {
                    $best_distance = $distance;
                    $best_pos = $actual_pos;
                }
            }
        }
        
        if ($best_pos === false) {
            $space_pos = strrpos(substr($text, 0, $target_pos + 20), ' ');
            if ($space_pos !== false && $space_pos > $target_pos - 50) {
                $best_pos = $space_pos + 1;
            }
        }
        
        return $best_pos;
    }
    
    /**
     * Split a list block
     */
    private function split_list($block, $split_at) {
        return ['first' => null, 'second' => $block];
    }
    
    /**
     * Split a table block
     */
    private function split_table($block, $split_at) {
        return ['first' => null, 'second' => $block];
    }
    
    /**
     * Split a clause block
     */
    private function split_clause($block, $split_at) {
        if ($block['lines']['total'] < 10) {
            return ['first' => null, 'second' => $block];
        }
        return $this->split_paragraph($block, $split_at);
    }
    
    /**
     * Finalize current page and start new one
     */
    private function finalize_current_page() {
        if (empty($this->current_page)) {
            return;
        }
        
        $this->pages[] = [
            'number' => $this->current_page_number,
            'blocks' => $this->current_page,
            'lines_used' => $this->current_page_lines,
            'lines_available' => $this->effective_lines,
            'utilization' => round(($this->current_page_lines / $this->effective_lines) * 100, 1),
        ];
        
        $this->current_page = [];
        $this->current_page_lines = 0;
        $this->current_page_number++;
    }
    
    /**
     * Calculate pagination statistics
     */
    private function calculate_stats() {
        $total_lines = 0;
        $total_blocks = 0;
        $utilization_sum = 0;
        
        foreach ($this->pages as $page) {
            $total_lines += $page['lines_used'];
            $total_blocks += count($page['blocks']);
            $utilization_sum += $page['utilization'];
        }
        
        $page_count = count($this->pages);
        
        return [
            'total_pages' => $page_count,
            'total_lines' => $total_lines,
            'total_blocks' => $total_blocks,
            'average_utilization' => $page_count > 0 ? round($utilization_sum / $page_count, 1) : 0,
            'lines_per_page' => $this->lines_per_page,
            'effective_lines' => $this->effective_lines,
            'buffer_lines' => $this->buffer_lines,
        ];
    }
    
    /**
     * Generate paginated HTML output
     */
    public function render_html($paginated) {
        $html = '';
        $total_pages = $paginated['total_pages'];
        
        foreach ($paginated['pages'] as $page) {
            $page_num = $page['number'];
            $is_last_page = ($page_num === $total_pages);
            
            $html .= sprintf('<div class="el-page" data-page="%d" data-total="%d">', $page_num, $total_pages);
            
            foreach ($page['blocks'] as $block) {
                $html .= $this->render_block($block);
            }
            
            if ($this->include_page_signatures && !$is_last_page) {
                $html .= $this->render_page_signature($page_num, $total_pages);
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render a single block
     */
    private function render_block($block) {
        $id = esc_attr($block['options']['id']);
        $class = 'el-block el-block-' . esc_attr($block['type']);
        
        if (!empty($block['options']['class'])) {
            $class .= ' ' . esc_attr($block['options']['class']);
        }
        
        if (!empty($block['options']['keep_together'])) {
            $class .= ' el-keep-together';
        }
        
        $style = '';
        if (!empty($block['options']['style'])) {
            $style = ' style="' . esc_attr($block['options']['style']) . '"';
        }
        
        $tag = $this->get_block_wrapper_tag($block['type']);
        $content = $this->process_block_content($block);
        
        return sprintf('<%s id="%s" class="%s"%s>%s</%s>', $tag, $id, $class, $style, $content, $tag);
    }
    
    /**
     * Get appropriate wrapper tag for block type
     */
    private function get_block_wrapper_tag($type) {
        switch ($type) {
            case EL_Content_Blocks::BLOCK_HEADING_1:
                return 'h1';
            case EL_Content_Blocks::BLOCK_HEADING_2:
                return 'h2';
            case EL_Content_Blocks::BLOCK_HEADING_3:
                return 'h3';
            case EL_Content_Blocks::BLOCK_LIST:
                return 'ul';
            default:
                return 'div';
        }
    }
    
    /**
     * Process block content for output
     */
    private function process_block_content($block) {
        $content = $block['content'];
        
        if (in_array($block['type'], [
            EL_Content_Blocks::BLOCK_PARAGRAPH,
            EL_Content_Blocks::BLOCK_CLAUSE,
            EL_Content_Blocks::BLOCK_ANNEX,
        ])) {
            if (strpos($content, '<p>') === false && strpos($content, '<div>') === false) {
                $content = wpautop($content);
            }
        }
        
        return wp_kses_post($content);
    }
    
    /**
     * Render page signature line
     */
    private function render_page_signature($page_num, $total_pages) {
        $signature_text = sprintf($this->signature_format, $page_num, $total_pages);
        return sprintf('<div class="el-page-signature">%s</div>', esc_html($signature_text));
    }
    
    /**
     * Generate print CSS for paginated output
     */
    public static function get_print_css() {
        return '
.el-page {
    position: relative;
    box-sizing: border-box;
    padding: 25mm 15mm;
    min-height: 297mm;
    page-break-after: always;
    background: white;
}
.el-page:last-child { page-break-after: avoid; }
.el-block { margin-bottom: 1em; }
.el-block-h1 { font-size: 18pt; font-weight: bold; margin-top: 1.5em; margin-bottom: 0.5em; }
.el-block-h2 { font-size: 14pt; font-weight: bold; margin-top: 1.2em; margin-bottom: 0.4em; }
.el-block-h3 { font-size: 12pt; font-weight: bold; margin-top: 1em; margin-bottom: 0.3em; }
.el-block-paragraph { font-size: 12pt; line-height: 1.4; }
.el-block-signature { margin-top: 2em; page-break-inside: avoid; }
.el-block-footer { font-size: 8pt; color: #666; border-top: 1px solid #ccc; padding-top: 1em; margin-top: 2em; }
.el-keep-together { page-break-inside: avoid; }
.el-page-signature { position: absolute; bottom: 15mm; left: 15mm; right: 15mm; text-align: center; font-size: 10pt; color: #333; }
.el-opening-columns { display: flex; justify-content: space-between; margin-bottom: 2em; }
.el-opening-left, .el-opening-right { width: 48%; }
.el-fee-line { margin: 0.5em 0; }
.el-totals-table { width: 100%; max-width: 400px; margin: 1em 0; }
.el-totals-table td { padding: 0.3em 0; }
.el-totals-table .el-amount { text-align: right; font-weight: bold; }
.el-clause-title { font-weight: bold; }
.el-clause-body { margin-left: 1em; }
.el-footer-notes { font-size: 9pt; color: #666; margin-top: 1em; }
@media print {
    body { margin: 0; padding: 0; }
    .el-page { margin: 0; padding: 0; min-height: auto; }
    @page { size: A4; margin: 25mm 15mm; }
    .el-page-signature { position: fixed; bottom: 10mm; }
}';
    }
    
    /**
     * Debug output for pagination result
     */
    public static function debug_pagination($paginated) {
        $output = "=== Pagination Debug ===\n\n";
        $output .= "Total Pages: " . $paginated['total_pages'] . "\n";
        $output .= "Include Signatures: " . ($paginated['include_signatures'] ? 'Yes' : 'No') . "\n\n";
        
        $stats = $paginated['stats'];
        $output .= "Statistics:\n";
        $output .= "  Total Lines: " . $stats['total_lines'] . "\n";
        $output .= "  Total Blocks: " . $stats['total_blocks'] . "\n";
        $output .= "  Avg Utilization: " . $stats['average_utilization'] . "%\n";
        $output .= "  Lines/Page: " . $stats['lines_per_page'] . " (effective: " . $stats['effective_lines'] . ")\n\n";
        
        foreach ($paginated['pages'] as $page) {
            $output .= sprintf("Page %d: %d blocks, %d/%d lines (%.1f%% used)\n",
                $page['number'], count($page['blocks']), $page['lines_used'], $page['lines_available'], $page['utilization']);
            
            foreach ($page['blocks'] as $block) {
                $output .= sprintf("  - [%s] %s (%d lines)\n", $block['type'], $block['options']['id'], $block['lines']['total']);
            }
            $output .= "\n";
        }
        
        return $output;
    }
}