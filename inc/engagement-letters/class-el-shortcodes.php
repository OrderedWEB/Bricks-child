<?php
/**
 * Engagement Letters Shortcodes
 * 
 * Provides shortcodes for displaying engagement letter lists with search,
 * filters, sorting, and pagination.
 * 
 * @package Bricks_Child
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent duplicate class declaration
if (class_exists('EL_Shortcodes')) {
    return;
}

class EL_Shortcodes {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - register shortcodes
     */
    private function __construct() {
        // Register shortcodes
        add_shortcode('el_my_drafts', [$this, 'shortcode_my_drafts']);
        add_shortcode('el_favorites', [$this, 'shortcode_favorites']);
        add_shortcode('el_all_letters', [$this, 'shortcode_all_letters']);
        add_shortcode('el_awaiting_signature', [$this, 'shortcode_awaiting_signature']);
        add_shortcode('el_signed', [$this, 'shortcode_signed']);
        add_shortcode('el_completed', [$this, 'shortcode_completed']);
        
        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        
        // AJAX for list filtering
        add_action('wp_ajax_el_filter_list', [$this, 'ajax_filter_list']);
    }
    
    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'el-shortcodes',
            get_stylesheet_directory_uri() . '/inc/engagement-letters/css/el-shortcodes.css',
            [],
            '1.0.0'
        );
    }
    
    /**
     * Shortcode: My Drafts
     */
    public function shortcode_my_drafts($atts) {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_search' => 'true',
            'show_filters' => 'true'
        ], $atts);
        
        return $this->render_list([
            'status' => 'draft',
            'author' => get_current_user_id(),
            'title' => 'My Drafts',
            'icon' => '📝',
            'empty_message' => 'No draft engagement letters yet. Start creating one!',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ]);
    }
    
    /**
     * Shortcode: Favorites
     */
    public function shortcode_favorites($atts) {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_search' => 'true',
            'show_filters' => 'true'
        ], $atts);
        
        return $this->render_list([
            'favorite' => true,
            'author' => get_current_user_id(),
            'title' => 'Favorite Letters',
            'icon' => '⭐',
            'empty_message' => 'No favorites yet. Star your most important engagement letters!',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ]);
    }
    
    /**
     * Shortcode: All Letters
     */
    public function shortcode_all_letters($atts) {
        $atts = shortcode_atts([
            'per_page' => 20,
            'show_search' => 'true',
            'show_filters' => 'true',
            'author' => ''
        ], $atts);
        
        $query_args = [
            'title' => 'All Engagement Letters',
            'icon' => '📄',
            'empty_message' => 'No engagement letters found.',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ];
        
        if ($atts['author']) {
            $query_args['author'] = absint($atts['author']);
        }
        
        return $this->render_list($query_args);
    }
    
    /**
     * Shortcode: Awaiting Signature
     */
    public function shortcode_awaiting_signature($atts) {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_search' => 'true',
            'show_filters' => 'false'
        ], $atts);
        
        return $this->render_list([
            'status' => 'sent_for_signature',
            'author' => get_current_user_id(),
            'title' => 'Awaiting Signature',
            'icon' => '✍️',
            'empty_message' => 'No letters awaiting signature.',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ]);
    }
    
    /**
     * Shortcode: Signed
     */
    public function shortcode_signed($atts) {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_search' => 'true',
            'show_filters' => 'false'
        ], $atts);
        
        return $this->render_list([
            'status' => 'signed',
            'author' => get_current_user_id(),
            'title' => 'Signed Letters',
            'icon' => '✓',
            'empty_message' => 'No signed engagement letters yet.',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ]);
    }
    
    /**
     * Shortcode: Completed
     */
    public function shortcode_completed($atts) {
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_search' => 'true',
            'show_filters' => 'false'
        ], $atts);
        
        return $this->render_list([
            'status' => 'completed',
            'author' => get_current_user_id(),
            'title' => 'Completed Matters',
            'icon' => '🎉',
            'empty_message' => 'No completed matters yet.',
            'per_page' => absint($atts['per_page']),
            'show_search' => $atts['show_search'] === 'true',
            'show_filters' => $atts['show_filters'] === 'true'
        ]);
    }
    
    /**
     * Render engagement letter list
     */
    private function render_list($args) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="el-list-login-required">
                <p>Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to view engagement letters.</p>
            </div>';
        }
        
        $defaults = [
            'status' => null,
            'author' => null,
            'favorite' => false,
            'title' => 'Engagement Letters',
            'icon' => '📄',
            'empty_message' => 'No engagement letters found.',
            'per_page' => 10,
            'show_search' => true,
            'show_filters' => true,
            'paged' => 1
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $query_args = [
            'post_type' => 'engagement_letter',
            'posts_per_page' => $args['per_page'],
            'paged' => $args['paged'],
            'orderby' => 'modified',
            'order' => 'DESC'
        ];
        
        if ($args['status']) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'el_status',
                    'field' => 'slug',
                    'terms' => $args['status']
                ]
            ];
        }
        
        if ($args['author']) {
            $query_args['author'] = $args['author'];
        }
        
        if ($args['favorite']) {
            $query_args['meta_query'] = [
                [
                    'key' => '_el_is_favorite',
                    'value' => '1',
                    'compare' => '='
                ]
            ];
        }
        
        $query = new WP_Query($query_args);
        
        // Generate unique ID for this list
        $list_id = 'el-list-' . uniqid();
        
        ob_start();
        ?>
        
        <div class="el-list-wrapper" id="<?php echo $list_id; ?>" data-args='<?php echo json_encode($args); ?>'>
            
            <!-- Header -->
            <div class="el-list-header">
                <div class="el-list-title-wrap">
                    <span class="el-list-icon"><?php echo $args['icon']; ?></span>
                    <h2 class="el-list-title"><?php echo esc_html($args['title']); ?></h2>
                    <span class="el-list-count"><?php echo $query->found_posts; ?></span>
                </div>
                
                <?php if ($args['show_search'] || $args['show_filters']): ?>
                <div class="el-list-controls">
                    
                    <?php if ($args['show_search']): ?>
                    <div class="el-search-box">
                        <input 
                            type="search" 
                            class="el-search-input" 
                            placeholder="Search engagement letters..."
                            aria-label="Search engagement letters"
                        >
                        <button class="el-search-btn" aria-label="Search">🔍</button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($args['show_filters']): ?>
                    <div class="el-filters">
                        <select class="el-filter-status" aria-label="Filter by status">
                            <option value="">All Statuses</option>
                            <?php foreach (EL_Core::STATUSES as $slug => $status): ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($args['status'], $slug); ?>>
                                <?php echo esc_html($status['label']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select class="el-filter-sort" aria-label="Sort by">
                            <option value="modified_desc">Recently Modified</option>
                            <option value="created_desc">Newest First</option>
                            <option value="created_asc">Oldest First</option>
                            <option value="title_asc">Title A-Z</option>
                            <option value="title_desc">Title Z-A</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                </div>
                <?php endif; ?>
            </div>
            
            <!-- List Content -->
            <div class="el-list-content">
                
                <?php if ($query->have_posts()): ?>
                
                <div class="el-list-grid">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <?php echo $this->render_list_item(get_the_ID()); ?>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($query->max_num_pages > 1): ?>
                <div class="el-list-pagination">
                    <?php
                    echo paginate_links([
                        'total' => $query->max_num_pages,
                        'current' => $args['paged'],
                        'prev_text' => '← Previous',
                        'next_text' => 'Next →',
                        'type' => 'list'
                    ]);
                    ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                
                <div class="el-list-empty">
                    <div class="el-empty-icon"><?php echo $args['icon']; ?></div>
                    <p class="el-empty-message"><?php echo esc_html($args['empty_message']); ?></p>
                    <?php if ($args['status'] === 'draft'): ?>
                    <a href="<?php echo home_url('/create-engagement-letter/'); ?>" class="el-empty-action">
                        Create New Engagement Letter
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php endif; ?>
                
            </div>
            
        </div>
        
        <?php
        wp_reset_postdata();
        
        // Inline styles
        $this->output_inline_styles();
        
        return ob_get_clean();
    }
    
    /**
     * Render single list item
     */
    private function render_list_item($post_id) {
        $el_data = EL_Core::get_el_data($post_id);
        
        if (!$el_data) {
            return '';
        }
        
        $client = get_user_by('id', $el_data['client_id']);
        $client_name = $client ? $client->display_name : 'No Client';
        
        $status_info = $el_data['status_info'];
        
        ob_start();
        ?>
        
        <div class="el-list-item" data-el-id="<?php echo $post_id; ?>">
            
            <!-- Status Badge -->
            <div class="el-item-status" style="background: <?php echo $status_info['color']; ?>22; border-left-color: <?php echo $status_info['color']; ?>;">
                <span class="el-status-icon"><?php echo $status_info['icon']; ?></span>
            </div>
            
            <!-- Content -->
            <div class="el-item-content">
                
                <!-- Title & Meta -->
                <div class="el-item-header">
                    <h3 class="el-item-title">
                        <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>">
                            <?php echo esc_html($el_data['title']); ?>
                        </a>
                        <?php if ($el_data['is_favorite']): ?>
                        <span class="el-favorite-star" title="Favorite">⭐</span>
                        <?php endif; ?>
                    </h3>
                    
                    <div class="el-item-meta">
                        <span class="el-meta-client">
                            <span class="el-meta-icon">👤</span>
                            <?php echo esc_html($client_name); ?>
                        </span>
                        
                        <?php if ($el_data['matter_ref']): ?>
                        <span class="el-meta-matter">
                            <span class="el-meta-icon">📋</span>
                            <?php echo esc_html($el_data['matter_ref']); ?>
                        </span>
                        <?php endif; ?>
                        
                        <span class="el-meta-date">
                            <span class="el-meta-icon">🕒</span>
                            <?php echo human_time_diff(strtotime($el_data['modified']), current_time('timestamp')) . ' ago'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Status Info -->
                <div class="el-item-status-info">
                    <?php echo EL_Core::get_status_badge($el_data['status']); ?>
                    
                    <?php if ($el_data['status'] === 'sent_for_signature' && $el_data['expires_date']): ?>
                    <span class="el-expiry-badge">
                        <span class="el-meta-icon">⏰</span>
                        Expires in <?php echo ceil(($el_data['expires_date'] - time()) / DAY_IN_SECONDS); ?> days
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($el_data['status'] === 'signed' && $el_data['signed_date']): ?>
                    <span class="el-signed-badge">
                        Signed <?php echo date('M j, Y', $el_data['signed_date']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <!-- Actions -->
            <div class="el-item-actions">
                <?php echo EL_Actions::get_action_buttons($post_id, 'list'); ?>
            </div>
            
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX: Filter list
     */
    public function ajax_filter_list() {
        check_ajax_referer('el_actions', 'nonce');
        
        $args = json_decode(stripslashes($_POST['args']), true);
        $search = sanitize_text_field($_POST['search'] ?? '');
        $status = sanitize_text_field($_POST['status_filter'] ?? '');
        $sort = sanitize_text_field($_POST['sort'] ?? 'modified_desc');
        
        // Update args with filters
        if ($search) {
            $args['s'] = $search;
        }
        
        if ($status) {
            $args['status'] = $status;
        }
        
        // Handle sorting
        $sort_parts = explode('_', $sort);
        if (count($sort_parts) === 2) {
            $args['orderby'] = $sort_parts[0];
            $args['order'] = strtoupper($sort_parts[1]);
        }
        
        // Render updated list
        wp_send_json_success([
            'html' => $this->render_list($args)
        ]);
    }
    
    /**
     * Output inline styles
     */
    private function output_inline_styles() {
        static $styles_output = false;
        
        if ($styles_output) {
            return;
        }
        
        $styles_output = true;
        ?>
        
        <style>
        .el-list-wrapper{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.1);padding:24px;margin:24px 0}
        .el-list-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;gap:20px;flex-wrap:wrap}
        .el-list-title-wrap{display:flex;align-items:center;gap:12px}
        .el-list-icon{font-size:28px}
        .el-list-title{margin:0;font-size:24px;font-weight:700;color:#1e293b}
        .el-list-count{background:#f1f5f9;color:#64748b;padding:4px 12px;border-radius:12px;font-size:14px;font-weight:600}
        .el-list-controls{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        .el-search-box{display:flex;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;transition:all .2s}
        .el-search-box:focus-within{border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        .el-search-input{border:none;background:0 0;padding:10px 16px;font-size:14px;outline:0;min-width:250px}
        .el-search-btn{background:0 0;border:none;padding:10px 16px;cursor:pointer;font-size:16px;transition:transform .2s}
        .el-search-btn:hover{transform:scale(1.1)}
        .el-filters{display:flex;gap:8px}
        .el-filter-status,.el-filter-sort{padding:10px 16px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;font-size:14px;cursor:pointer;transition:all .2s}
        .el-filter-status:hover,.el-filter-sort:hover{border-color:#cbd5e1}
        .el-filter-status:focus,.el-filter-sort:focus{outline:0;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        .el-list-grid{display:grid;gap:16px}
        .el-list-item{display:grid;grid-template-columns:auto 1fr auto;gap:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;transition:all .2s;align-items:start}
        .el-list-item:hover{border-color:#cbd5e1;box-shadow:0 4px 8px rgba(0,0,0,.05);transform:translateY(-2px)}
        .el-item-status{padding:12px;border-radius:8px;border-left:4px solid}
        .el-status-icon{font-size:24px;display:block}
        .el-item-content{flex:1;min-width:0}
        .el-item-header{margin-bottom:12px}
        .el-item-title{margin:0 0 8px 0;font-size:18px;font-weight:600;display:flex;align-items:center;gap:8px}
        .el-item-title a{color:#1e293b;text-decoration:none;transition:color .2s}
        .el-item-title a:hover{color:#667eea}
        .el-favorite-star{font-size:16px}
        .el-item-meta{display:flex;gap:16px;flex-wrap:wrap;font-size:14px;color:#64748b}
        .el-item-meta>span{display:flex;align-items:center;gap:4px}
        .el-meta-icon{font-size:14px}
        .el-item-status-info{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px}
        .el-expiry-badge,.el-signed-badge{font-size:13px;color:#64748b;display:flex;align-items:center;gap:4px}
        .el-item-actions{display:flex;align-items:start}
        .el-list-empty{text-align:center;padding:60px 20px}
        .el-empty-icon{font-size:64px;margin-bottom:16px;opacity:.5}
        .el-empty-message{font-size:16px;color:#64748b;margin-bottom:24px}
        .el-empty-action{display:inline-block;background:#667eea;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;transition:all .2s}
        .el-empty-action:hover{background:#5568d3;transform:translateY(-2px);box-shadow:0 4px 8px rgba(102,126,234,.3)}
        .el-list-pagination{margin-top:24px;text-align:center}
        .el-list-pagination ul{list-style:none;padding:0;margin:0;display:inline-flex;gap:8px}
        .el-list-pagination li{margin:0}
        .el-list-pagination a,.el-list-pagination span{display:block;padding:8px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;color:#64748b;text-decoration:none;transition:all .2s}
        .el-list-pagination a:hover{background:#f1f5f9;border-color:#cbd5e1}
        .el-list-pagination .current{background:#667eea;border-color:#667eea;color:#fff}
        .el-list-login-required{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:20px;text-align:center}
        .el-list-login-required a{color:#ef4444;font-weight:600}
        @media (max-width:768px){
            .el-list-header{flex-direction:column;align-items:stretch}
            .el-list-controls{flex-direction:column}
            .el-search-input{min-width:100%}
            .el-filters{flex-direction:column}
            .el-filter-status,.el-filter-sort{width:100%}
            .el-list-item{grid-template-columns:1fr}
            .el-item-actions{justify-content:center}
        }
        </style>
        
        <?php
    }
}

// Initialize
EL_Shortcodes::get_instance();