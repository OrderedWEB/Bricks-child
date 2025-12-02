<?php
/**
 * Engagement Letter Content Blocks - UPDATED FOR PAGINATION
 * 
 * Renders different sections of the engagement letter with proper formatting
 * Includes block types and pagination support for mPDF generation
 */

if (!defined('ABSPATH')) exit;

class EL_Content_Blocks {
    
    /**
     * Block type constants
     */
    const BLOCK_HEADING_1 = 'h1';
    const BLOCK_HEADING_2 = 'h2';
    const BLOCK_HEADING_3 = 'h3';
    const BLOCK_PARAGRAPH = 'paragraph';
    const BLOCK_LIST = 'list';
    const BLOCK_TABLE = 'table';
    const BLOCK_CLAUSE = 'clause';
    const BLOCK_SIGNATURE = 'signature';
    const BLOCK_PAGE_BREAK = 'page_break';
    const BLOCK_ANNEX = 'annex';
    const BLOCK_FOOTER = 'footer';
    
    /**
     * Page layout constants (A4 @ 12pt Times New Roman)
     */
    const LINES_PER_PAGE = 47;
    const LINES_PER_PAGE_WITH_SIGNATURE = 44;
    const BUFFER_ZONE_LINES = 3;
    const CHARS_PER_LINE = 80;
    
    /**
     * Block configuration defaults
     */
    private static $block_configs = [
        'h1' => ['splittable' => false, 'keep_with_next' => 3, 'min_lines_if_split' => 2],
        'h2' => ['splittable' => false, 'keep_with_next' => 2, 'min_lines_if_split' => 2],
        'h3' => ['splittable' => false, 'keep_with_next' => 2, 'min_lines_if_split' => 2],
        'paragraph' => ['splittable' => true, 'keep_with_next' => 0, 'min_lines_if_split' => 2],
        'list' => ['splittable' => true, 'keep_with_next' => 0, 'min_lines_if_split' => 2],
        'table' => ['splittable' => true, 'keep_with_next' => 0, 'min_lines_if_split' => 3],
        'clause' => ['splittable' => true, 'keep_with_next' => 0, 'min_lines_if_split' => 3],
        'signature' => ['splittable' => false, 'keep_with_next' => 0, 'min_lines_if_split' => 10],
        'page_break' => ['splittable' => false, 'keep_with_next' => 0, 'min_lines_if_split' => 0],
        'annex' => ['splittable' => true, 'keep_with_next' => 0, 'min_lines_if_split' => 3],
        'footer' => ['splittable' => false, 'keep_with_next' => 0, 'min_lines_if_split' => 2],
    ];
    
    /**
     * Create a content block
     * 
     * @param string $type Block type constant
     * @param string $content HTML content
     * @param array $options Additional options
     * @return array Block structure
     */
    public static function create_block($type, $content, $options = []) {
        $config = self::$block_configs[$type] ?? self::$block_configs['paragraph'];
        
        $block = [
            'type' => $type,
            'content' => $content,
            'options' => wp_parse_args($options, [
                'id' => 'block_' . uniqid(),
                'class' => '',
                'style' => '',
                'keep_together' => false,
                'page_break_before' => false,
            ]),
            'config' => $config,
            'lines' => self::estimate_lines($content, $type),
        ];
        
        return $block;
    }
    
    /**
     * Estimate lines for content
     * 
     * @param string $content HTML content
     * @param string $type Block type
     * @return array Lines estimate
     */
    public static function estimate_lines($content, $type = 'paragraph') {
        $text = wp_strip_all_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $char_count = strlen($text);
        
        // Base line count
        $lines = max(1, ceil($char_count / self::CHARS_PER_LINE));
        
        // Add extra lines for headings
        if (in_array($type, [self::BLOCK_HEADING_1, self::BLOCK_HEADING_2, self::BLOCK_HEADING_3])) {
            $lines += 1; // Space after heading
        }
        
        // Add extra lines for lists (bullet points take space)
        if ($type === self::BLOCK_LIST) {
            $list_items = substr_count($content, '<li');
            $lines += ceil($list_items * 0.5);
        }
        
        // Signature blocks have minimum height
        if ($type === self::BLOCK_SIGNATURE) {
            $lines = max($lines, 10);
        }
        
        return [
            'total' => $lines,
            'content' => $lines,
            'padding' => 0,
        ];
    }
    
    /**
     * Create blocks from assembled data
     * 
     * @param array $data Data from EL_Print_Data_Assembler
     * @param bool $include_signatures Include signature lines
     * @return array Array of blocks
     */
    public static function create_blocks_from_data($data, $include_signatures = false) {

                add_action('wp_footer', function() use ($data) {
            ?>
            <script>
                console.log('🔍 Content Blocks Debug:');
                console.log('  data keys:', <?php echo json_encode(array_keys($data)); ?>);
                console.log('  parent in data:', <?php echo isset($data['parent']) ? 'true' : 'false'; ?>);
                <?php if (isset($data['parent'])): ?>
                console.log('  parent:', <?php echo json_encode($data['parent']); ?>);
                <?php endif; ?>
            </script>
            <?php
        }, 9998);
        $blocks = [];

        
    
        

        
        // Opening content
        if (!empty($data['opening_left']) || !empty($data['opening_right'])) {
            $opening_html = '<div class="el-opening">';
            if (!empty($data['opening_left'])) {
                $opening_html .= '<div class="el-opening-left">' . wp_kses_post($data['opening_left']) . '</div>';
            }
            if (!empty($data['opening_right'])) {
                $opening_html .= '<div class="el-opening-right">' . wp_kses_post($data['opening_right']) . '</div>';
            }
            $opening_html .= '</div>';
            $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $opening_html, ['id' => 'opening']);
        }
       // Reference and date
        $meta_html = '<div class="el-meta">';
        $meta_html .= '<p><strong>Reference:</strong> ' . esc_html($data['reference'] ?? '') . '</p>';
        $meta_html .= '</div>';
        $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $meta_html, ['id' => 'meta']);     

        // Parent product content (for grouped products)
        $parent = $data['parent'] ?? null;
        
        if ($parent) {
            // Parent title
            if (!empty($parent['title'])) {
                $blocks[] = self::create_block(self::BLOCK_HEADING_2, esc_html($parent['title']), [
                    'id' => 'parent_title',
                    'class' => 'el-parent-title',
                ]);
            }
            
            // Parent introduction (el_introduction_texts)
            if (!empty($parent['introduction'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, wp_kses_post($parent['introduction']), [
                    'id' => 'parent_intro',
                    'class' => 'el-introduction',
                ]);
            }
            
            // Parent description (pdf_el_text)
            if (!empty($parent['description'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, wp_kses_post($parent['description']), [
                    'id' => 'parent_description',
                    'class' => 'el-description',
                ]);
            }
            
            // Parent service plan table
            if (!empty($parent['service_plan_table'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, wp_kses_post($parent['service_plan_table']), [
                    'id' => 'parent_service_plan',
                    'class' => 'el-service-plan',
                ]);
            }
        }
        
        // Services
        $services = $data['services'] ?? [];
        foreach ($services as $index => $service) {
            // Service title
            $title = $service['pdf_title'] ?? $service['name'] ?? 'Service';
            $blocks[] = self::create_block(self::BLOCK_HEADING_2, $title, [
                'id' => 'service_' . $index . '_title',
            ]);
            
            // Service subtitle
            if (!empty($service['pdf_subtitle'])) {
                $blocks[] = self::create_block(self::BLOCK_HEADING_3, $service['pdf_subtitle'], [
                    'id' => 'service_' . $index . '_subtitle',
                ]);
            }
            
            // Service introduction
            if (!empty($service['pdf_introduction'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $service['pdf_introduction'], [
                    'id' => 'service_' . $index . '_intro',
                ]);
            }
            
            // Service text
            if (!empty($service['pdf_text'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $service['pdf_text'], [
                    'id' => 'service_' . $index . '_text',
                ]);
            }
            
            // Clauses
            if (!empty($service['pdf_clauses']) && is_array($service['pdf_clauses'])) {
                foreach ($service['pdf_clauses'] as $ci => $clause) {
                    $clause_content = is_array($clause) ? ($clause['text'] ?? '') : $clause;
                    if (!empty($clause_content)) {
                        $blocks[] = self::create_block(self::BLOCK_CLAUSE, $clause_content, [
                            'id' => 'service_' . $index . '_clause_' . $ci,
                        ]);
                    }
                }
            }
            
            // Service footer
            if (!empty($service['pdf_footer'])) {
                $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $service['pdf_footer'], [
                    'id' => 'service_' . $index . '_footer',
                    'class' => 'el-service-footer',
                ]);
            }
        }
        
        // Pricing summary
        $pricing_html = '<div class="el-pricing">';
        $pricing_html .= '<h3>Investment Summary</h3>';
        if (!empty($data['total_engagement_fee'])) {
            $pricing_html .= '<p><strong>Engagement Fee:</strong> €' . number_format($data['total_engagement_fee'], 2) . '</p>';
        }
        if (!empty($data['total_expected_cost'])) {
            $pricing_html .= '<p><strong>Expected Total Cost:</strong> €' . number_format($data['total_expected_cost'], 2) . '</p>';
        }
        $pricing_html .= '</div>';
        $blocks[] = self::create_block(self::BLOCK_PARAGRAPH, $pricing_html, ['id' => 'pricing']);
        
        // Signature block
        $signature_html = '<div class="el-signature-block">';
        if (!empty($data['signature_block'])) {
            $signature_html .= wp_kses_post($data['signature_block']);
        } else {
            $signature_html .= '<h3>Agreement</h3>';
            $signature_html .= '<p>By signing below, you confirm that you have read and agree to the terms of this engagement letter.</p>';
            $signature_html .= '<p>&nbsp;</p>';
            $signature_html .= '<p>Client Signature: _________________________________</p>';
            $signature_html .= '<p>&nbsp;</p>';
            $signature_html .= '<p>Name: ' . esc_html($client['name'] ?? '') . '</p>';
            $signature_html .= '<p>&nbsp;</p>';
            $signature_html .= '<p>Date: _________________________________</p>';
        }
        $signature_html .= '</div>';
        $blocks[] = self::create_block(self::BLOCK_SIGNATURE, $signature_html, [
            'id' => 'signature',
            'keep_together' => true,
        ]);
        
        // Footer boilerplate
        if (!empty($data['footer_boilerplate'])) {
            $blocks[] = self::create_block(self::BLOCK_FOOTER, $data['footer_boilerplate'], [
                'id' => 'footer_boilerplate',
            ]);
        }  
        return $blocks;
    }
    
    /**
     * Render client details section
     */
    public static function render_client_details($data) {
        $client = $data['client'] ?? [];
        
        ob_start();
        ?>
        <div class="el-client-details">
            <h3>Client Information</h3>
            <p><strong>Name:</strong> <?php echo esc_html($client['name'] ?? 'N/A'); ?></p>
            <?php if (!empty($client['email'])): ?>
            <p><strong>Email:</strong> <?php echo esc_html($client['email']); ?></p>
            <?php endif; ?>
            <?php if (!empty($client['phone'])): ?>
            <p><strong>Phone:</strong> <?php echo esc_html($client['phone']); ?></p>
            <?php endif; ?>
            <?php if (!empty($client['address'])): ?>
            <p><strong>Address:</strong> <?php echo esc_html($client['address']); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render engagement letter header
     */
    public static function render_header($data) {
        $reference = $data['reference'] ?? '';
        $date = $data['date'] ?? '';
        $letterhead = $data['letterhead'] ?? '';
        
        ob_start();
        ?>
        <div class="el-header">
            <?php if ($letterhead): ?>
            <div class="el-letterhead">
                <?php echo wp_kses_post($letterhead); ?>
            </div>
            <?php endif; ?>
            
            <div class="el-header-meta">
                <div class="el-reference">
                    <strong>Reference:</strong> <?php echo esc_html($reference); ?>
                </div>
                <div class="el-date">
                    <strong>Date:</strong> <?php echo esc_html($date); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render services list
     */
    public static function render_services($data) {
        $services = $data['services'] ?? [];
        
        if (empty($services)) {
            return '<p class="el-no-services">No services found.</p>';
        }
        
        ob_start();
        ?>
        <div class="el-services-list">
            <h3>Services</h3>
            <?php foreach ($services as $service): ?>
            <div class="el-service-item">
                <h4><?php echo esc_html($service['pdf_title'] ?? $service['name']); ?></h4>
                <?php if (!empty($service['pdf_text'])): ?>
                <div class="el-service-description">
                    <?php echo wp_kses_post($service['pdf_text']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render pricing summary
     */
    public static function render_pricing($data) {
        $engagement_fee = $data['total_engagement_fee'] ?? 0;
        $expected_cost = $data['total_expected_cost'] ?? 0;
        
        ob_start();
        ?>
        <div class="el-pricing-summary">
            <h3>Investment Summary</h3>
            <p><strong>Engagement Fee:</strong> €<?php echo number_format($engagement_fee, 2); ?></p>
            <?php if ($expected_cost > 0): ?>
            <p><strong>Expected Total Cost:</strong> €<?php echo number_format($expected_cost, 2); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render signature block
     */
    public static function render_signatures($data) {
        $signature_block = $data['signature_block'] ?? '';
        $client_name = $data['client']['name'] ?? '';
        
        ob_start();
        ?>
        <div class="el-signatures">
            <?php if ($signature_block): ?>
                <?php echo wp_kses_post($signature_block); ?>
            <?php else: ?>
            <h3>Signatures</h3>
            <p>Client Signature: _______________________________</p>
            <p>Name: <?php echo esc_html($client_name); ?></p>
            <p>Date: _______________________________</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render complete engagement letter
     */
    public static function render_complete($data) {
        ob_start();
        
        echo self::render_header($data);
        echo self::render_client_details($data);
        echo self::render_services($data);
        echo self::render_pricing($data);
        echo self::render_signatures($data);
        
        if (!empty($data['footer_boilerplate'])) {
            echo '<div class="el-footer-boilerplate">';
            echo wp_kses_post($data['footer_boilerplate']);
            echo '</div>';
        }
        
        return ob_get_clean();
    }
}