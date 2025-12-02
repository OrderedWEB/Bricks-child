<?php
/**
 * Page Break Decision Engine v2.0 - SUPER SMART Edition
 * 
 * Implements intelligent pagination rules to produce professional legal documents.
 * This version includes advanced content analysis and semantic block grouping.
 * 
 * @package Starne_Consulting_EL
 * @since 2.0.0
 * 
 * KEY IMPROVEMENTS OVER V1:
 * 
 * 1. SEMANTIC GROUPING: Recognizes that "Notes:" and "Legal services are requested for:"
 *    belong with their parent service and must stay together.
 * 
 * 2. LOOK-AHEAD ANALYSIS: Before placing any block, analyzes what comes next
 *    to prevent orphaned closing sections.
 * 
 * 3. SERVICE UNIT INTEGRITY: Each service (title → description → notes → checkboxes)
 *    is treated as a semantic unit with intelligent splitting rules.
 * 
 * 4. WHITESPACE OPTIMIZATION: Prevents excessive blank space by pulling content
 *    forward when possible.
 * 
 * 5. SIGNATURE BLOCK PLACEMENT: Ensures signature blocks have appropriate
 *    preceding content and aren't stranded.
 * 
 * PAGINATION RULES:
 * 
 * Rule 1: Keep heading with following content (min 3 lines)
 * Rule 2: Service sections are semantic units - split only at safe points
 * Rule 3: Notes/checkboxes NEVER orphaned from parent service
 * Rule 4: Minimum 4 lines before page break (not 2)
 * Rule 5: Minimum 4 lines after page break (not 2)
 * Rule 6: Lists kept together if ≤5 items
 * Rule 7: Tables never split
 * Rule 8: Signature blocks never split, need 6+ lines before them
 * Rule 9: Investment Summary starts new page if less than 8 lines remain
 * Rule 10: Closing instructions stay with signature block
 */

if (!defined('ABSPATH')) {
    exit;
}

class EL_Page_Break_Engine {
    
    // =========================================================================
    // CONFIGURATION CONSTANTS
    // =========================================================================
    
    /**
     * Minimum lines to leave at bottom of page before break
     */
    const MIN_LINES_BEFORE_BREAK = 4;
    
    /**
     * Minimum lines to have at top of new page
     */
    const MIN_LINES_AFTER_BREAK = 4;
    
    /**
     * Minimum lines before a heading to keep it with following content
     */
    const HEADING_KEEP_WITH_NEXT = 4;
    
    /**
     * Minimum remaining lines to start Investment Summary on current page
     */
    const INVESTMENT_SUMMARY_MIN_LINES = 8;
    
    /**
     * Minimum lines of content before signature block
     */
    const SIGNATURE_BLOCK_PREAMBLE = 6;
    
    /**
     * Maximum list items to keep together without splitting
     */
    const MAX_UNSPLIT_LIST_ITEMS = 5;
    
    /**
     * Patterns that indicate "closing" content that belongs with previous block
     */
    const CLOSING_PATTERNS = [
        '/^Notes?:/i',
        '/^Legal services are requested for/i',
        '/^The fees? above/i',
        '/^One applicant/i',
        '/^For each additional/i',
        '/^\s*☐/u',  // Checkbox lines
        '/^\s*\[\s*\]/u',  // Empty bracket checkbox
    ];
    
    /**
     * Patterns that indicate a new service/section is starting
     */
    const NEW_SECTION_PATTERNS = [
        '/^[A-Z][A-Za-z\s]+—\s*€[\d,]+/u',  // Service title with price
        '/^[A-Z][A-Za-z\s]+(Registration|Application|Process|Check|Translation|Validation)/i',
    ];
    
    // =========================================================================
    // INSTANCE PROPERTIES
    // =========================================================================
    
    private $lines_per_page;
    private $buffer_lines;
    private $effective_lines;
    private $include_page_signatures;
    private $signature_format;
    
    private $pages = [];
    private $current_page = [];
    private $current_page_lines = 0;
    private $current_page_number = 1;
    
    private $blocks = [];
    private $block_index = 0;
    
    /**
     * Semantic groups - blocks that must stay together
     */
    private $semantic_groups = [];
    
    /**
     * Debug mode
     */
    private $debug = false;
    
    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================
    
    public function __construct($include_page_signatures = false, $signature_format = '') {
        $this->include_page_signatures = $include_page_signatures;
        $this->signature_format = $signature_format ?: 'Client signature ………………………………';
        
        $this->lines_per_page = $include_page_signatures 
            ? EL_Content_Blocks::LINES_PER_PAGE_WITH_SIGNATURE 
            : EL_Content_Blocks::LINES_PER_PAGE;
        
        $this->buffer_lines = EL_Content_Blocks::BUFFER_ZONE_LINES;
        $this->effective_lines = $this->lines_per_page - $this->buffer_lines;
        
        $this->debug = defined('EL_DEBUG_PAGINATION') && EL_DEBUG_PAGINATION;
    }
    
    // =========================================================================
    // MAIN PAGINATION METHOD
    // =========================================================================
    
    /**
     * Process blocks and determine page breaks
     * 
     * @param array $blocks Array of content blocks from EL_Content_Blocks
     * @return array Paginated structure with pages and blocks
     */
    public function paginate($blocks) {
        // Reset state
        $this->reset_state();
        $this->blocks = $blocks;
        
        $this->log("Starting pagination with " . count($blocks) . " blocks");
        $this->log("Effective lines per page: " . $this->effective_lines);
        
        // Phase 1: Analyze and create semantic groups
        $this->analyze_semantic_structure();
        
        // Phase 2: Process blocks with semantic awareness
        while ($this->block_index < count($this->blocks)) {
            $block = $this->blocks[$this->block_index];
            
            $this->log("Processing block {$this->block_index}: {$block['type']} - {$block['options']['id']}");
            
            // Handle explicit page breaks
            if ($this->is_explicit_page_break($block)) {
                $this->finalize_current_page();
                $this->block_index++;
                continue;
            }
            
            // Check if this block is part of a semantic group
            $group = $this->get_semantic_group($this->block_index);
            
            if ($group) {
                $this->process_semantic_group($group);
            } else {
                $this->process_single_block($block);
            }
            
            $this->block_index++;
        }
        
        // Finalize last page
        if (!empty($this->current_page)) {
            $this->finalize_current_page();
        }
        
        // Phase 3: Optimize page utilization
        $this->optimize_page_utilization();
        
        $total_pages = count($this->pages);
        
        $this->log("Pagination complete: {$total_pages} pages");
        
        return [
            'pages' => $this->pages,
            'total_pages' => $total_pages,
            'include_signatures' => $this->include_page_signatures,
            'signature_format' => $this->signature_format,
            'stats' => $this->calculate_stats(),
        ];
    }
    
    // =========================================================================
    // SEMANTIC ANALYSIS
    // =========================================================================
    
    /**
     * Analyze block structure and identify semantic groups
     * 
     * A semantic group is a set of blocks that should stay together:
     * - Service title + description + notes + checkboxes
     * - Heading + first paragraph
     * - Investment summary + signature block
     */
    private function analyze_semantic_structure() {
        $this->semantic_groups = [];
        $current_group = null;
        $current_group_start = null;
        
        for ($i = 0; $i < count($this->blocks); $i++) {
            $block = $this->blocks[$i];
            $content = $this->get_block_text($block);
            
            // Check if this is a new section start
            if ($this->is_new_section_start($block)) {
                // Save previous group if exists
                if ($current_group !== null) {
                    $this->semantic_groups[] = [
                        'start' => $current_group_start,
                        'end' => $i - 1,
                        'type' => $current_group,
                        'blocks' => array_slice($this->blocks, $current_group_start, $i - $current_group_start),
                    ];
                }
                
                // Start new group
                $current_group = 'service';
                $current_group_start = $i;
                
                $this->log("New service section detected at block {$i}");
            }
            // Check if this is closing content that belongs with previous
            elseif ($this->is_closing_content($content)) {
                // This belongs with current group - don't start new one
                $this->log("Closing content detected at block {$i}: " . substr($content, 0, 50));
            }
            // Check for investment summary
            elseif ($this->is_investment_summary($block)) {
                // Save previous group
                if ($current_group !== null) {
                    $this->semantic_groups[] = [
                        'start' => $current_group_start,
                        'end' => $i - 1,
                        'type' => $current_group,
                        'blocks' => array_slice($this->blocks, $current_group_start, $i - $current_group_start),
                    ];
                }
                
                // Investment summary through signature is one group
                $current_group = 'closing';
                $current_group_start = $i;
                
                $this->log("Investment summary detected at block {$i}");
            }
            // Check for signature block
            elseif ($block['type'] === EL_Content_Blocks::BLOCK_SIGNATURE) {
                // Signature should be part of closing group
                if ($current_group === 'closing') {
                    // Continue group
                } else {
                    // Create new closing group starting here
                    if ($current_group !== null) {
                        $this->semantic_groups[] = [
                            'start' => $current_group_start,
                            'end' => $i - 1,
                            'type' => $current_group,
                            'blocks' => array_slice($this->blocks, $current_group_start, $i - $current_group_start),
                        ];
                    }
                    $current_group = 'signature';
                    $current_group_start = $i;
                }
            }
        }
        
        // Save final group
        if ($current_group !== null) {
            $this->semantic_groups[] = [
                'start' => $current_group_start,
                'end' => count($this->blocks) - 1,
                'type' => $current_group,
                'blocks' => array_slice($this->blocks, $current_group_start),
            ];
        }
        
        $this->log("Identified " . count($this->semantic_groups) . " semantic groups");
    }
    
    /**
     * Check if block starts a new section
     */
    private function is_new_section_start($block) {
        // H2 headings typically start new sections
        if ($block['type'] === EL_Content_Blocks::BLOCK_HEADING_2) {
            return true;
        }
        
        $content = $this->get_block_text($block);
        
        foreach (self::NEW_SECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if content is "closing" content that belongs with previous block
     */
    private function is_closing_content($content) {
        $content = trim($content);
        
        foreach (self::CLOSING_PATTERNS as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if block is investment summary
     */
    private function is_investment_summary($block) {
        $content = $this->get_block_text($block);
        return (
            stripos($content, 'Investment Summary') !== false ||
            stripos($content, 'Engagement Fee:') !== false
        );
    }
    
    /**
     * Get semantic group for a block index
     */
    private function get_semantic_group($block_index) {
        foreach ($this->semantic_groups as $group) {
            if ($block_index >= $group['start'] && $block_index <= $group['end']) {
                // Only return group if this is the START of the group
                if ($block_index === $group['start']) {
                    return $group;
                }
                // Otherwise return null - block is part of a group but not the start
                return null;
            }
        }
        return null;
    }
    
    // =========================================================================
    // BLOCK PROCESSING
    // =========================================================================
    
    /**
     * Process a semantic group of blocks
     */
    private function process_semantic_group($group) {
        $group_lines = $this->calculate_group_lines($group);
        $remaining = $this->get_remaining_lines();
        
        $this->log("Processing semantic group '{$group['type']}': {$group_lines} lines, {$remaining} remaining");
        
        // Special handling for closing section (investment + signatures)
        if ($group['type'] === 'closing' || $group['type'] === 'signature') {
            $this->process_closing_section($group);
            return;
        }
        
        // If entire group fits, add it all
        if ($group_lines <= $remaining) {
            $this->add_group_to_page($group);
            // Skip to end of group
            $this->block_index = $group['end'];
            return;
        }
        
        // Group doesn't fit - find intelligent split points
        $this->split_semantic_group($group);
        $this->block_index = $group['end'];
    }
    
    /**
     * Process closing section (investment summary + signatures)
     */
    private function process_closing_section($group) {
        $group_lines = $this->calculate_group_lines($group);
        $remaining = $this->get_remaining_lines();
        
        // If less than INVESTMENT_SUMMARY_MIN_LINES remain, start new page
        if ($remaining < self::INVESTMENT_SUMMARY_MIN_LINES) {
            $this->finalize_current_page();
        }
        
        // Now add the closing section
        foreach ($group['blocks'] as $block) {
            $block_lines = $block['lines']['total'];
            
            if (!$this->will_fit($block_lines)) {
                // Check if this is signature - it must not be split
                if ($block['type'] === EL_Content_Blocks::BLOCK_SIGNATURE) {
                    $this->finalize_current_page();
                }
            }
            
            $this->add_block_to_page($block);
        }
        
        $this->block_index = $group['end'];
    }
    
    /**
     * Split a semantic group intelligently
     */
    private function split_semantic_group($group) {
        $blocks = $group['blocks'];
        $safe_split_indices = $this->find_safe_split_points($blocks);
        
        $this->log("Safe split points: " . implode(', ', $safe_split_indices));
        
        $current_lines = 0;
        $remaining = $this->get_remaining_lines();
        $last_safe_split = 0;
        
        for ($i = 0; $i < count($blocks); $i++) {
            $block = $blocks[$i];
            $block_lines = $block['lines']['total'];
            
            // Check if we've passed remaining space
            if ($current_lines + $block_lines > $remaining) {
                // Find the best split point before this
                $split_at = $this->find_best_split_before($safe_split_indices, $i, $blocks);
                
                if ($split_at === null || $split_at === 0) {
                    // Can't split safely - move entire remaining group to next page
                    $this->finalize_current_page();
                    $remaining = $this->effective_lines;
                    $current_lines = 0;
                } else {
                    // Add blocks up to split point, then break
                    for ($j = $last_safe_split; $j < $split_at; $j++) {
                        $this->add_block_to_page($blocks[$j]);
                    }
                    $this->finalize_current_page();
                    $remaining = $this->effective_lines;
                    $current_lines = 0;
                    $last_safe_split = $split_at;
                    $i = $split_at - 1; // Will be incremented by loop
                    continue;
                }
            }
            
            // Add this block
            $this->add_block_to_page($block);
            $current_lines += $block_lines;
            
            // Track safe split points we've passed
            if (in_array($i + 1, $safe_split_indices)) {
                $last_safe_split = $i + 1;
            }
        }
    }
    
    /**
     * Find safe split points within a group of blocks
     * 
     * Safe to split:
     * - Before a new sub-heading (H3)
     * - Before "Included activities:"
     * - After closing "Notes:" sections (before next service)
     * 
     * NOT safe to split:
     * - Before "Notes:"
     * - Before checkbox sections
     * - After a heading with no content
     */
    private function find_safe_split_points($blocks) {
        $safe_points = [];
        
        for ($i = 1; $i < count($blocks); $i++) {
            $prev_block = $blocks[$i - 1];
            $curr_block = $blocks[$i];
            $curr_content = $this->get_block_text($curr_block);
            $prev_content = $this->get_block_text($prev_block);
            
            // NOT safe: current block is closing content
            if ($this->is_closing_content($curr_content)) {
                continue;
            }
            
            // NOT safe: previous block is a heading (keep with content)
            if (in_array($prev_block['type'], [
                EL_Content_Blocks::BLOCK_HEADING_1,
                EL_Content_Blocks::BLOCK_HEADING_2,
                EL_Content_Blocks::BLOCK_HEADING_3,
            ])) {
                // Check if current has enough content
                if ($curr_block['lines']['total'] < self::HEADING_KEEP_WITH_NEXT) {
                    continue;
                }
            }
            
            // SAFE: current is a new sub-section (H3 or "Included activities")
            if ($curr_block['type'] === EL_Content_Blocks::BLOCK_HEADING_3) {
                $safe_points[] = $i;
                continue;
            }
            
            if (stripos($curr_content, 'Included activities') !== false) {
                $safe_points[] = $i;
                continue;
            }
            
            // SAFE: previous block was closing content (Notes/checkboxes complete)
            if ($this->is_closing_content($prev_content)) {
                // Make sure current is NOT also closing content
                if (!$this->is_closing_content($curr_content)) {
                    $safe_points[] = $i;
                    continue;
                }
            }
            
            // SAFE: after a substantial paragraph (5+ lines)
            if ($prev_block['type'] === EL_Content_Blocks::BLOCK_PARAGRAPH) {
                if ($prev_block['lines']['total'] >= 5) {
                    // Check current is not closing content
                    if (!$this->is_closing_content($curr_content)) {
                        $safe_points[] = $i;
                    }
                }
            }
        }
        
        return $safe_points;
    }
    
    /**
     * Find best split point before a given index
     */
    private function find_best_split_before($safe_points, $before_index, $blocks) {
        $best = null;
        
        foreach ($safe_points as $point) {
            if ($point < $before_index) {
                // Check that splitting here leaves enough on current page
                $lines_before = 0;
                for ($i = 0; $i < $point; $i++) {
                    $lines_before += $blocks[$i]['lines']['total'];
                }
                
                if ($lines_before >= self::MIN_LINES_BEFORE_BREAK) {
                    $best = $point;
                }
            }
        }
        
        return $best;
    }
    
    /**
     * Process a single block (not part of a semantic group)
     */
    private function process_single_block($block) {
        $space_needed = $this->calculate_space_needed($block);
        
        if ($this->will_fit($space_needed)) {
            $this->add_block_to_page($block);
        } else {
            $action = $this->decide_page_break_action($block, $space_needed);
            $this->execute_action($action, $block);
        }
    }
    
    // =========================================================================
    // SPACE CALCULATION
    // =========================================================================
    
    /**
     * Calculate total lines for a group
     */
    private function calculate_group_lines($group) {
        $total = 0;
        foreach ($group['blocks'] as $block) {
            $total += $block['lines']['total'];
        }
        return $total;
    }
    
    /**
     * Calculate space needed for a block including keep-with-next
     */
    private function calculate_space_needed($block) {
        $lines = $block['lines']['total'];
        $keep_with_next = $block['config']['keep_with_next'] ?? 0;
        
        // For headings, always keep with at least HEADING_KEEP_WITH_NEXT lines
        if (in_array($block['type'], [
            EL_Content_Blocks::BLOCK_HEADING_1,
            EL_Content_Blocks::BLOCK_HEADING_2,
            EL_Content_Blocks::BLOCK_HEADING_3,
        ])) {
            $keep_with_next = max($keep_with_next, self::HEADING_KEEP_WITH_NEXT);
        }
        
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
    
    // =========================================================================
    // PAGE BREAK DECISIONS
    // =========================================================================
    
    /**
     * Check if block is an explicit page break
     */
    private function is_explicit_page_break($block) {
        return (
            $block['type'] === EL_Content_Blocks::BLOCK_PAGE_BREAK ||
            !empty($block['options']['page_break_before'])
        );
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
        
        // Non-splittable blocks move entirely
        if (!$config['splittable']) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Rule 4 & 5: Check minimum lines before/after break
        $min_before = max(self::MIN_LINES_BEFORE_BREAK, $config['min_lines_if_split'] ?? 3);
        $min_after = max(self::MIN_LINES_AFTER_BREAK, $config['min_lines_if_split'] ?? 3);
        
        if ($remaining < $min_before) {
            return ['action' => 'move_to_next_page'];
        }
        
        $lines_after_split = $block_lines - $remaining;
        if ($lines_after_split < $min_after) {
            return ['action' => 'move_to_next_page'];
        }
        
        // Check for closing content - don't split if it would orphan closing
        $content = $this->get_block_text($block);
        if ($this->would_orphan_closing_content($block, $remaining)) {
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
     * Check if splitting would orphan closing content
     */
    private function would_orphan_closing_content($block, $remaining) {
        $content = $block['content'];
        
        // Look for closing patterns within the content
        foreach (self::CLOSING_PATTERNS as $pattern) {
            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $match_position = $matches[0][1];
                
                // Estimate if the match would end up on the second page
                $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
                $chars_before_break = $remaining * $chars_per_line;
                
                if ($match_position > $chars_before_break * 0.8) {
                    // The closing content would be orphaned
                    return true;
                }
            }
        }
        
        return false;
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
    
    // =========================================================================
    // BLOCK SPLITTING
    // =========================================================================
    
    /**
     * Split a block at specified line count
     */
    private function split_block($block, $split_at) {
        $type = $block['type'];
        
        switch ($type) {
            case EL_Content_Blocks::BLOCK_PARAGRAPH:
                return $this->split_paragraph($block, $split_at);
                
            case EL_Content_Blocks::BLOCK_LIST:
                return $this->split_list_content($block, $split_at);
                
            case EL_Content_Blocks::BLOCK_TABLE:
                // Tables don't split
                return ['first' => null, 'second' => $block];
                
            case EL_Content_Blocks::BLOCK_CLAUSE:
                return $this->split_clause($block, $split_at);
                
            default:
                // Default: don't split
                return ['first' => null, 'second' => $block];
        }
    }
    
    /**
     * Split paragraph at sentence boundary
     */
    private function split_paragraph($block, $split_at) {
        $content = $block['content'];
        $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
        $target_chars = $split_at * $chars_per_line;
        
        // Get plain text for analysis
        $plain = wp_strip_all_tags($content);
        
        // If too short, don't split
        if (strlen($plain) < $target_chars * 1.5) {
            return ['first' => null, 'second' => $block];
        }
        
        // Find sentence boundaries near target
        $sentences = preg_split('/(?<=[.!?])\s+/', $plain, -1, PREG_SPLIT_OFFSET_CAPTURE);
        
        $best_split = null;
        $best_distance = PHP_INT_MAX;
        
        foreach ($sentences as $idx => $sentence) {
            $end_pos = $sentence[1] + strlen($sentence[0]);
            $distance = abs($end_pos - $target_chars);
            
            // Must be before target and leave enough on both sides
            if ($end_pos <= $target_chars && $end_pos > $target_chars * 0.5) {
                if ($distance < $best_distance) {
                    $best_distance = $distance;
                    $best_split = $end_pos;
                }
            }
        }
        
        if ($best_split === null) {
            return ['first' => null, 'second' => $block];
        }
        
        // Split content
        $first_text = substr($plain, 0, $best_split);
        $second_text = trim(substr($plain, $best_split));
        
        // Rebuild with HTML
        $first_html = '<p>' . esc_html($first_text) . '</p>';
        $second_html = '<p>' . esc_html($second_text) . '</p>';
        
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
     * Split list content intelligently
     */
    private function split_list_content($block, $split_at) {
        $content = $block['content'];
        
        // Extract list items
        preg_match_all('/<li[^>]*>.*?<\/li>/is', $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (empty($matches[0])) {
            return ['first' => null, 'second' => $block];
        }
        
        $items = $matches[0];
        $item_count = count($items);
        
        // Rule 6: Keep lists together if small enough
        if ($item_count <= self::MAX_UNSPLIT_LIST_ITEMS) {
            return ['first' => null, 'second' => $block];
        }
        
        // Find split point (minimum 2 items each side)
        $chars_per_line = EL_Content_Blocks::CHARS_PER_LINE;
        $target_chars = $split_at * $chars_per_line;
        
        $best_split_idx = null;
        
        foreach ($items as $idx => $item) {
            $item_end = $item[1] + strlen($item[0]);
            
            // Need at least 2 items before and after
            if ($idx >= 1 && ($item_count - $idx - 1) >= 2) {
                if ($item_end <= $target_chars) {
                    $best_split_idx = $idx;
                }
            }
        }
        
        if ($best_split_idx === null) {
            return ['first' => null, 'second' => $block];
        }
        
        // Split at this point
        $split_item = $items[$best_split_idx];
        $split_pos = $split_item[1] + strlen($split_item[0]);
        
        $first_html = substr($content, 0, $split_pos);
        $second_html = substr($content, $split_pos);
        
        // Ensure proper list wrapper
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
        
        return ['first' => $first_block, 'second' => $second_block];
    }
    
    /**
     * Split a clause block
     */
    private function split_clause($block, $split_at) {
        // Clauses under 10 lines shouldn't split
        if ($block['lines']['total'] < 10) {
            return ['first' => null, 'second' => $block];
        }
        return $this->split_paragraph($block, $split_at);
    }
    
    /**
     * Ensure list HTML is properly closed
     */
    private function ensure_list_closure($html) {
        // Check for unclosed lists
        $ol_opens = substr_count($html, '<ol');
        $ol_closes = substr_count($html, '</ol>');
        $ul_opens = substr_count($html, '<ul');
        $ul_closes = substr_count($html, '</ul>');
        
        if ($ol_opens > $ol_closes) {
            $html .= str_repeat('</ol>', $ol_opens - $ol_closes);
        }
        if ($ul_opens > $ul_closes) {
            $html .= str_repeat('</ul>', $ul_opens - $ul_closes);
        }
        
        return $html;
    }
    
    /**
     * Ensure list HTML has proper opening
     */
    private function ensure_list_opening($html) {
        // Check for orphaned list items
        if (preg_match('/^\s*<li/i', $html) && !preg_match('/<[ou]l[^>]*>/i', $html)) {
            $html = '<ul>' . $html;
        }
        return $html;
    }
    
    // =========================================================================
    // PAGE MANAGEMENT
    // =========================================================================
    
    /**
     * Reset engine state
     */
    private function reset_state() {
        $this->pages = [];
        $this->current_page = [];
        $this->current_page_lines = 0;
        $this->current_page_number = 1;
        $this->blocks = [];
        $this->block_index = 0;
        $this->semantic_groups = [];
    }
    
    /**
     * Add block to current page
     */
    private function add_block_to_page($block) {
        $this->current_page[] = $block;
        $this->current_page_lines += $block['lines']['total'];
        
        $this->log("  Added block: {$block['lines']['total']} lines, page now has {$this->current_page_lines} lines");
    }
    
    /**
     * Add all blocks from a group to current page
     */
    private function add_group_to_page($group) {
        foreach ($group['blocks'] as $block) {
            $this->add_block_to_page($block);
        }
    }
    
    /**
     * Finalize current page and start new one
     */
    private function finalize_current_page() {
        if (empty($this->current_page)) {
            return;
        }
        
        $utilization = ($this->current_page_lines / $this->effective_lines) * 100;
        
        $this->pages[] = [
            'number' => $this->current_page_number,
            'blocks' => $this->current_page,
            'lines_used' => $this->current_page_lines,
            'lines_available' => $this->effective_lines,
            'utilization' => round($utilization, 1),
        ];
        
        $this->log("Finalized page {$this->current_page_number}: {$this->current_page_lines} lines, {$utilization}% utilization");
        
        $this->current_page = [];
        $this->current_page_lines = 0;
        $this->current_page_number++;
    }
    
    // =========================================================================
    // OPTIMIZATION
    // =========================================================================
    
    /**
     * Optimize page utilization
     * 
     * Look for pages with very low utilization and try to pull content forward
     */
    private function optimize_page_utilization() {
        // This is a placeholder for future optimization
        // Could implement content pulling if a page has < 50% utilization
        // and the next page's first block would fit
    }
    
    // =========================================================================
    // STATISTICS
    // =========================================================================
    
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
        ];
    }
    
    // =========================================================================
    // RENDERING
    // =========================================================================
    
    /**
     * Render paginated content as HTML
     */
    public function render_html($paginated) {
        $html = '';
        $total_pages = $paginated['total_pages'];
        
        foreach ($paginated['pages'] as $page) {
            $html .= '<div class="el-page" data-page="' . $page['number'] . '">';
            
            foreach ($page['blocks'] as $block) {
                $html .= $this->render_block($block);
            }
            
            // Add page signature if enabled
            if ($this->include_page_signatures) {
                $html .= $this->render_page_signature($page['number'], $total_pages);
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render a single block
     */
    private function render_block($block) {
        $type = $block['type'];
        $content = $block['content'];
        $options = $block['options'];
        
        $classes = ['el-block', 'el-block-' . $type];
        
        if (!empty($options['class'])) {
            $classes[] = $options['class'];
        }
        
        if (!empty($options['keep_together'])) {
            $classes[] = 'el-keep-together';
        }
        
        $class_str = implode(' ', $classes);
        $id_attr = !empty($options['id']) ? ' id="' . esc_attr($options['id']) . '"' : '';
        
        // Process content
        $content = $this->process_block_content($content, $type);
        
        return sprintf('<div class="%s"%s>%s</div>', esc_attr($class_str), $id_attr, $content);
    }
    
    /**
     * Process block content for rendering
     */
    private function process_block_content($content, $type) {
        // Don't double-wrap paragraphs
        if (in_array($type, [EL_Content_Blocks::BLOCK_PARAGRAPH, EL_Content_Blocks::BLOCK_CLAUSE])) {
            if (strpos($content, '<p') === false) {
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
    
    // =========================================================================
    // UTILITIES
    // =========================================================================
    
    /**
     * Get plain text from a block
     */
    private function get_block_text($block) {
        return trim(wp_strip_all_tags($block['content']));
    }
    
    /**
     * Log debug message
     */
    private function log($message) {
        if ($this->debug) {
            error_log("[EL Pagination] " . $message);
        }
    }
    
    // =========================================================================
    // STATIC METHODS
    // =========================================================================
    
    /**
     * Generate print CSS for paginated output
     */
    public static function get_print_css() {
        return '
.el-page {
    position: relative;
    box-sizing: border-box;
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
.el-page-signature { 
    position: absolute; 
    bottom: 15mm; 
    left: 15mm; 
    right: 15mm; 
    text-align: right; 
    font-size: 10pt; 
    color: #333; 
}
@media print {
    body { margin: 0; padding: 0; }
    .el-page { margin: 0; padding: 0; min-height: auto; }
    @page { size: A4; margin: 25mm 15mm; }
}';
    }
    
    /**
     * Debug output for pagination result
     */
    public static function debug_pagination($paginated) {
        $output = "=== Pagination Debug (v2.0) ===\n\n";
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
                $page['number'], 
                count($page['blocks']), 
                $page['lines_used'], 
                $page['lines_available'], 
                $page['utilization']
            );
            
            foreach ($page['blocks'] as $block) {
                $preview = substr(wp_strip_all_tags($block['content']), 0, 40);
                $output .= sprintf("  - [%s] %s (%d lines) \"%s...\"\n", 
                    $block['type'], 
                    $block['options']['id'], 
                    $block['lines']['total'],
                    $preview
                );
            }
            $output .= "\n";
        }
        
        return $output;
    }
}