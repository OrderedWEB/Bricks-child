<?php
/**
 * Engagement Letter Content Blocks - UPDATED FOR ITEM TYPES
 * 
 * Renders different sections of the engagement letter with proper formatting
 * Now supports item_type for checkbox display (mandatory/suggested/optional/hide)
 */

if (!defined('ABSPATH')) exit;

class EL_Content_Blocks {
    
    /**
     * Render client details section
     * 
     * @param array $data Complete data array
     * @return string HTML
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
     * 
     * @param array $data Complete data array
     * @return string HTML
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
     * Render services list - WITH ITEM TYPE CHECKBOXES
     * 
     * @param array $data Complete data array
     * @return string HTML
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
            
            <?php foreach ($services as $service): 
                $item_type = $service['item_type'] ?? 'optional';
                
                // Determine checkbox state
                $is_checked = ($item_type === 'mandatory');
                $checkbox_class = 'el-checkbox-' . $item_type;
                $checkbox_label = '';
                
                switch ($item_type) {
                    case 'mandatory':
                        $checkbox_label = ' (Required)';
                        break;
                    case 'suggested':
                        $checkbox_label = ' ✨ Suggested by lawyer';
                        break;
                    case 'optional':
                        $checkbox_label = ' (Optional)';
                        break;
                }
            ?>
            
            <div class="el-service-item <?php echo esc_attr($checkbox_class); ?>">
                <div class="el-service-header">
                    <label class="el-service-checkbox-label">
                        <input type="checkbox" 
                               class="el-service-checkbox" 
                               <?php checked($is_checked); ?>
                               <?php echo $item_type === 'mandatory' ? 'disabled' : ''; ?>>
                        <span class="el-service-name">
                            <?php echo esc_html($service['pdf_title'] ?? $service['name']); ?>
                            <?php if ($checkbox_label): ?>
                            <span class="el-checkbox-label"><?php echo esc_html($checkbox_label); ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                    
                    <?php if (!empty($service['engagement_fee'])): ?>
                    <span class="el-service-price">
                        €<?php echo number_format($service['engagement_fee'], 2); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($service['pdf_text'])): ?>
                <div class="el-service-description">
                    <?php echo wp_kses_post($service['pdf_text']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($service['pdf_clauses'])): ?>
                <div class="el-service-clauses">
                    <h4>Terms & Conditions</h4>
                    <ul>
                        <?php foreach ($service['pdf_clauses'] as $clause): ?>
                        <li><?php echo wp_kses_post($clause['text'] ?? $clause); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            
            <?php endforeach; ?>
        </div>
        
        <style>
        .el-services-list {
            margin: 20px 0;
        }
        
        .el-service-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
        }
        
        .el-service-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .el-service-checkbox-label {
            display: flex;
            align-items: start;
            gap: 10px;
            cursor: pointer;
            flex: 1;
        }
        
        .el-service-checkbox {
            margin-top: 4px;
            width: 18px;
            height: 18px;
        }
        
        .el-service-name {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .el-checkbox-label {
            font-size: 12px;
            font-weight: normal;
            color: #6b7280;
            font-style: italic;
        }
        
        /* Item type specific styling */
        .el-checkbox-mandatory {
            border-left: 4px solid #1e40af;
            background: #eff6ff;
        }
        
        .el-checkbox-mandatory .el-service-checkbox {
            accent-color: #1e40af;
        }
        
        .el-checkbox-suggested {
            border-left: 4px solid #0284c7;
            background: #f0f9ff;
        }
        
        .el-checkbox-suggested .el-service-checkbox {
            accent-color: #0284c7;
        }
        
        .el-checkbox-optional {
            border-left: 4px solid #6b7280;
        }
        
        .el-service-price {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            min-width: 100px;
            text-align: right;
        }
        
        .el-service-description {
            margin-top: 10px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .el-service-clauses {
            margin-top: 15px;
            padding: 15px;
            background: #fffbeb;
            border-radius: 4px;
            border: 1px solid #fef3c7;
        }
        
        .el-service-clauses h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .el-service-clauses ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .el-service-clauses li {
            margin-bottom: 8px;
            color: #451a03;
        }
        
        @media print {
            .el-service-checkbox {
                -webkit-appearance: none;
                appearance: none;
                width: 16px;
                height: 16px;
                border: 2px solid #000;
                border-radius: 2px;
                position: relative;
            }
            
            .el-service-checkbox:checked::after {
                content: '✓';
                position: absolute;
                top: -2px;
                left: 2px;
                font-size: 14px;
                font-weight: bold;
                color: #000;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render pricing summary - GROUPED PRODUCT AWARE
     * 
     * @param array $data Complete data array
     * @return string HTML
     */
    public static function render_pricing($data) {
        $engagement_fee = $data['total_engagement_fee'] ?? 0;
        $expected_cost = $data['total_expected_cost'] ?? 0;
        $pricing_source = $data['pricing_source'] ?? 'unknown';
        
        ob_start();
        ?>
        <div class="el-pricing-summary">
            <h3>Investment Summary</h3>
            
            <div class="el-pricing-row el-engagement-fee">
                <span class="el-pricing-label">Engagement Fee Due Today:</span>
                <span class="el-pricing-value">€<?php echo number_format($engagement_fee, 2); ?></span>
            </div>
            
            <?php if ($expected_cost > 0): ?>
            <div class="el-pricing-row el-expected-cost">
                <span class="el-pricing-label">Expected Total Cost:</span>
                <span class="el-pricing-value">€<?php echo number_format($expected_cost, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($data['has_grouped_parent'] ?? false): ?>
            <p class="el-pricing-note">
                <em>Note: This is a package engagement letter. The fees shown are for all services combined.</em>
            </p>
            <?php endif; ?>
        </div>
        
        <style>
        .el-pricing-summary {
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #0ea5e9;
            border-radius: 12px;
        }
        
        .el-pricing-summary h3 {
            margin: 0 0 15px 0;
            color: #0c4a6e;
            font-size: 20px;
        }
        
        .el-pricing-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #bae6fd;
        }
        
        .el-pricing-row:last-of-type {
            border-bottom: none;
        }
        
        .el-pricing-label {
            font-size: 16px;
            color: #0c4a6e;
            font-weight: 600;
        }
        
        .el-pricing-value {
            font-size: 24px;
            font-weight: 800;
            color: #075985;
        }
        
        .el-engagement-fee {
            padding-top: 15px;
            border-top: 2px solid #0ea5e9;
        }
        
        .el-pricing-note {
            margin: 15px 0 0 0;
            font-size: 13px;
            color: #0c4a6e;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render signature block
     * 
     * @param array $data Complete data array
     * @return string HTML
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
            <div class="el-signature-fields">
                <div class="el-signature-field">
                    <p><strong>Client Signature:</strong></p>
                    <p class="el-signature-line">_______________________________</p>
                    <p><strong>Name:</strong> <?php echo esc_html($client_name); ?></p>
                    <p><strong>Date:</strong> _______________________________</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .el-signatures {
            margin: 40px 0;
            padding: 30px;
            background: #fffbeb;
            border: 2px solid #fbbf24;
            border-radius: 12px;
        }
        
        .el-signature-fields {
            margin-top: 20px;
        }
        
        .el-signature-field {
            margin-bottom: 30px;
        }
        
        .el-signature-line {
            margin: 10px 0;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render complete engagement letter
     * 
     * @param array $data Complete data array
     * @return string HTML
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