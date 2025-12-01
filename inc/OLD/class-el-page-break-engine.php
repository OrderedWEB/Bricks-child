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
 * Split a paragraph block at sentence boundaries (list-aware)
 */
private function split_paragraph($block, $split_at) {
    $content = $block['content'];
    
    // Check if content contains a list (both ordered and unordered)
    $has_list = (strpos($content, '<ul') !== false || 
                 strpos($content, '<ol') !== false || 
                 strpos($content, '<li') !== false);
    
    if ($has_list) {
        // Content has a list - try to split between list items, not within them
        return $this->split_list_content($block, $split_at);
    }
    
    // Regular paragraph splitting logic
    $text = wp_strip_all_tags($content);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
    $target_char_pos = $split_at * $chars_per_line;
    
    // Find the best sentence break near the target position
    $break_pos = $this->find_sentence_break($text, $target_char_pos);
    
    // If no good break found, or break is too close to end, don't split
    if ($break_pos === false || $break_pos >= strlen($text) - 100) {
        return ['first' => null, 'second' => $block];
    }
    
    // If break is too early (less than minimum lines), don't split
    $min_chars = 2 * $chars_per_line; // At least 2 lines
    if ($break_pos < $min_chars) {
        return ['first' => null, 'second' => $block];
    }
    
    // Split the text at the sentence boundary
    $first_text = trim(substr($text, 0, $break_pos));
    $second_text = trim(substr($text, $break_pos));
    
    // Preserve HTML structure if original content had formatting
    if (strpos($content, '<') !== false) {
        // Content has HTML - try to preserve it
        $first_html = $this->preserve_html_split($content, $first_text);
        $second_html = $this->preserve_html_split($content, $second_text);
    } else {
        // Plain text - wrap in paragraphs
        $first_html = '<p>' . esc_html($first_text) . '</p>';
        $second_html = '<p>' . esc_html($second_text) . '</p>';
    }
    
    $first_block = EL_Content_Blocks::create_block(
        $block['type'],
        $first_html,
        array_merge($block['options'], ['id' => $block['options']['id'] . '_a'])
    );
    
    $second_block = EL_Content_Blocks::create_block(
        $block['type'],
        $second_html,
        array_merge($block['options'], ['id' => $block['options']['id'] . '_b'])
    );
    
    return ['first' => $first_block, 'second' => $second_block];
}


/**
 * Split content that contains lists - respects list item boundaries
 */
private function split_list_content($block, $split_at) {
    $content = $block['content'];
    $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
    $target_char_pos = $split_at * $chars_per_line;
    
    // Extract list items - handle both <li> and potentially malformed lists
    // Use DOTALL (s) modifier to handle multiline list items
    preg_match_all('/<li[^>]*>.*?<\/li>/is', $content, $list_items, PREG_OFFSET_CAPTURE);
    
    error_log('EL Pagination: Detected ' . count($list_items[0]) . ' list items in content of length ' . strlen($content));
    
    if (empty($list_items[0])) {
        // No list items found - try alternate detection for bullets/numbers
        error_log('EL Pagination: No <li> tags found, checking for bullet paragraphs');
        
        // Look for paragraphs that start with bullet-like characters or numbers
        preg_match_all('/<p[^>]*>\s*[•●○■□▪▫–—−\d]+[\.\)]\s*.*?<\/p>/is', $content, $bullet_paras, PREG_OFFSET_CAPTURE);
        
        if (!empty($bullet_paras[0])) {
            error_log('EL Pagination: Found ' . count($bullet_paras[0]) . ' bullet/numbered paragraphs');
            $list_items = $bullet_paras;
        } else {
            // Really no list structure - DON'T SPLIT AT ALL
            error_log('EL Pagination: No list structure detected, moving entire block to next page');
            return ['first' => null, 'second' => $block];
        }
    }
    
    // If list has 3 or fewer items, don't split at all
    if (count($list_items[0]) <= 3) {
        error_log('EL Pagination: Short list (' . count($list_items[0]) . ' items), keeping together');
        return ['first' => null, 'second' => $block];
    }
    
    // Find which list item to split at
    $best_split_index = -1;
    $best_distance = PHP_INT_MAX;
    
    foreach ($list_items[0] as $index => $item) {
        list($item_html, $item_offset) = $item;
        
        // Calculate distance from target position
        $distance = abs($item_offset - $target_char_pos);
        
        error_log(sprintf('EL Pagination: Item %d at offset %d, target %d, distance: %d',
            $index, $item_offset, $target_char_pos, $distance));
        
        // Must be before target position (not after) and closest to target
        if ($item_offset <= $target_char_pos && $distance < $best_distance) {
            $best_distance = $distance;
            $best_split_index = $index;
        }
    }
    
    error_log('EL Pagination: Best split index: ' . $best_split_index . ' out of ' . count($list_items[0]) . ' items');
    
    // Need at least 2 list items on first page
    if ($best_split_index < 1) {
        error_log('EL Pagination: Not enough items for first page, moving entire block');
        return ['first' => null, 'second' => $block];
    }
    
    // Need at least 2 list items on second page  
    $remaining_items = count($list_items[0]) - $best_split_index - 1;
    if ($remaining_items < 2) {
        error_log('EL Pagination: Not enough items for second page (' . $remaining_items . ' remaining), moving entire block');
        return ['first' => null, 'second' => $block];
    }
    
    // Split after the chosen list item
    $split_item = $list_items[0][$best_split_index];
    $split_offset = $split_item[1] + strlen($split_item[0]);
    
    error_log('EL Pagination: Splitting at offset ' . $split_offset . ' (after item ' . $best_split_index . ')');
    
    $first_html = substr($content, 0, $split_offset);
    $second_html = substr($content, $split_offset);
    
    // Ensure proper list wrapper closing/opening
    $first_html = $this->ensure_list_closure($first_html);
    $second_html = $this->ensure_list_opening($second_html);
    
    $first_block = EL_Content_Blocks::create_block(
        $block['type'],
        $first_html,
        array_merge($block['options'], ['id' => $block['options']['id'] . '_a'])
    );
    
    $second_block = EL_Content_Blocks::create_block(
        $block['type'],
        $second_html,
        array_merge($block['options'], ['id' => $block['options']['id'] . '_b'])
    );
    
    error_log('EL Pagination: Successfully split list content into two blocks');
    return ['first' => $first_block, 'second' => $second_block];
}

/**
 * Ensure list HTML has proper closing tags
 */
private function ensure_list_closure($html) {
    // Count opening and closing ul/ol tags
    $ul_open = substr_count($html, '<ul');
    $ul_close = substr_count($html, '</ul>');
    $ol_open = substr_count($html, '<ol');
    $ol_close = substr_count($html, '</ol>');
    
    // Add missing closing tags
    while ($ul_close < $ul_open) {
        $html .= '</ul>';
        $ul_close++;
    }
    
    while ($ol_close < $ol_open) {
        $html .= '</ol>';
        $ol_close++;
    }
    
    return $html;
}

/**
 * Ensure list HTML has proper opening tags
 */
private function ensure_list_opening($html) {
    // If starts with </li>, we need to add opening <ul> or <ol>
    if (preg_match('/^\s*<li/i', $html)) {
        // Determine if it was a ul or ol (check for ordered list attributes)
        if (preg_match('/<li[^>]*value=/i', $html)) {
            $html = '<ol>' . $html;
        } else {
            $html = '<ul>' . $html;
        }
    }
    
    return $html;
}
/**
 * Find best sentence break near target position
 * Prioritizes: period, question mark, exclamation mark
 */
private function find_sentence_break($text, $target_pos) {
    // Search window: 150 chars before and after target
    $search_before = 150;
    $search_after = 150;
    
    $search_start = max(0, $target_pos - $search_before);
    $search_end = min(strlen($text), $target_pos + $search_after);
    
    // Extract search region
    $region_before = substr($text, $search_start, $target_pos - $search_start);
    $region_after = substr($text, $target_pos, $search_end - $target_pos);
    
    // Sentence ending patterns (in priority order)
    $patterns = [
        '. ',      // Period followed by space
        '! ',      // Exclamation followed by space
        '? ',      // Question followed by space
        ".\n",     // Period followed by newline
        "!\n",     // Exclamation followed by newline
        "?\n",     // Question followed by newline
    ];
    
    $best_pos = false;
    $best_distance = PHP_INT_MAX;
    
    // Look for sentence endings BEFORE target (preferred)
    foreach ($patterns as $pattern) {
        $pos = strrpos($region_before, $pattern);
        if ($pos !== false) {
            // Calculate actual position and distance from target
            $actual_pos = $search_start + $pos + strlen($pattern);
            $distance = abs($actual_pos - $target_pos);
            
            // Must be at least 50 chars from start (avoid tiny first chunk)
            if ($actual_pos >= 100 && $distance < $best_distance) {
                $best_distance = $distance;
                $best_pos = $actual_pos;
            }
        }
    }
    
    // If found sentence break before target within 100 chars, use it
    if ($best_pos !== false && $best_distance <= 100) {
        return $best_pos;
    }
    
    // Look for sentence endings AFTER target (acceptable)
    foreach ($patterns as $pattern) {
        $pos = strpos($region_after, $pattern);
        if ($pos !== false) {
            $actual_pos = $target_pos + $pos + strlen($pattern);
            $distance = abs($actual_pos - $target_pos);
            
            // Only use if close to target (within 100 chars)
            if ($distance <= 100 && $distance < $best_distance) {
                $best_distance = $distance;
                $best_pos = $actual_pos;
            }
        }
    }
    
    // If still no sentence break, look for paragraph breaks
    if ($best_pos === false) {
        $para_pos = strrpos($region_before, "\n\n");
        if ($para_pos !== false) {
            $best_pos = $search_start + $para_pos + 2;
        }
    }
    
    // Last resort: find nearest space (but avoid this if possible)
    if ($best_pos === false) {
        $space_before = strrpos($region_before, ' ');
        if ($space_before !== false && $space_before > strlen($region_before) - 50) {
            $best_pos = $search_start + $space_before + 1;
        }
    }
    
    return $best_pos;
}

/**
 * Attempt to preserve HTML structure when splitting
 */
private function preserve_html_split($original_html, $target_text) {
    // Simple approach: if target text is at start of content, extract HTML up to that point
    // For more complex cases, fall back to plain text
    
    $plain = wp_strip_all_tags($original_html);
    $target_length = strlen($target_text);
    
    // Check if target matches start of plain text
    if (strpos($plain, $target_text) === 0) {
        // Target is at beginning - try to extract corresponding HTML
        // This is complex, so for now just wrap in paragraph
        return '<p>' . esc_html($target_text) . '</p>';
    }
    
    // Check if target matches end of plain text
    if (substr($plain, -$target_length) === $target_text) {
        return '<p>' . esc_html($target_text) . '</p>';
    }
    
    // Complex case - just wrap text
    return wpautop(esc_html($target_text));
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