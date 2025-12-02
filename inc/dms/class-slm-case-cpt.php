<?php
/**
 * SLM Case Custom Post Type
 * 
 * Central hub connecting:
 * - Clients (WooCommerce customers)
 * - Documents (DMS)
 * - Tasks (Task System)
 * - Messages (Messaging)
 * - Matters/Services
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_Case_CPT {
    
    /**
     * Post type slug
     */
    const POST_TYPE = 'slm_case';
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('acf/init', [__CLASS__, 'register_fields']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [__CLASS__, 'save_case'], 10, 2);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [__CLASS__, 'admin_column_content'], 10, 2);
        add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', [__CLASS__, 'sortable_columns']);
        
        // Admin menu
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        
        // AJAX handlers
        add_action('wp_ajax_slm_get_case_summary', [__CLASS__, 'ajax_get_summary']);
        add_action('wp_ajax_slm_get_client_cases', [__CLASS__, 'ajax_get_client_cases']);
    }
    
    /**
     * Register post type
     */
    public static function register_post_type() {
        $labels = [
            'name' => __('Cases', 'flavor'),
            'singular_name' => __('Case', 'flavor'),
            'menu_name' => __('Cases', 'flavor'),
            'add_new' => __('New Case', 'flavor'),
            'add_new_item' => __('Add New Case', 'flavor'),
            'edit_item' => __('Edit Case', 'flavor'),
            'new_item' => __('New Case', 'flavor'),
            'view_item' => __('View Case', 'flavor'),
            'search_items' => __('Search Cases', 'flavor'),
            'not_found' => __('No cases found', 'flavor'),
            'not_found_in_trash' => __('No cases found in trash', 'flavor'),
        ];
        
        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Custom menu placement
            'query_var' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => ['title'],
            'show_in_rest' => true,
            'rest_base' => 'cases',
        ];
        
        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Register taxonomies
     */
    public static function register_taxonomies() {
        // Case Status
        register_taxonomy('slm_case_status', self::POST_TYPE, [
            'labels' => [
                'name' => __('Case Statuses', 'flavor'),
                'singular_name' => __('Status', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => false,
            'show_in_rest' => true,
        ]);
        
        // Practice Area
        register_taxonomy('slm_practice_area', self::POST_TYPE, [
            'labels' => [
                'name' => __('Practice Areas', 'flavor'),
                'singular_name' => __('Practice Area', 'flavor'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);
        
        // Insert default terms
        if (!term_exists('active', 'slm_case_status')) {
            wp_insert_term(__('Active', 'flavor'), 'slm_case_status', ['slug' => 'active']);
            wp_insert_term(__('Pending', 'flavor'), 'slm_case_status', ['slug' => 'pending']);
            wp_insert_term(__('On Hold', 'flavor'), 'slm_case_status', ['slug' => 'on-hold']);
            wp_insert_term(__('Completed', 'flavor'), 'slm_case_status', ['slug' => 'completed']);
            wp_insert_term(__('Archived', 'flavor'), 'slm_case_status', ['slug' => 'archived']);
        }
        
        if (!term_exists('immigration', 'slm_practice_area')) {
            wp_insert_term(__('Immigration', 'flavor'), 'slm_practice_area', ['slug' => 'immigration']);
            wp_insert_term(__('Citizenship', 'flavor'), 'slm_practice_area', ['slug' => 'citizenship']);
            wp_insert_term(__('Corporate', 'flavor'), 'slm_practice_area', ['slug' => 'corporate']);
            wp_insert_term(__('Real Estate', 'flavor'), 'slm_practice_area', ['slug' => 'real-estate']);
        }
    }
    
    /**
     * Register ACF fields
     */
    public static function register_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        acf_add_local_field_group([
            'key' => 'group_slm_case',
            'title' => __('Case Details', 'flavor'),
            'fields' => [
                // Client tab
                [
                    'key' => 'field_slm_case_tab_client',
                    'label' => __('Client', 'flavor'),
                    'type' => 'tab',
                ],
                [
                    'key' => 'field_slm_case_client',
                    'label' => __('Primary Client', 'flavor'),
                    'name' => '_slm_client_id',
                    'type' => 'user',
                    'instructions' => __('Select the primary client for this case.', 'flavor'),
                    'required' => 1,
                    'role' => ['customer', 'subscriber'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_case_additional_clients',
                    'label' => __('Additional Clients', 'flavor'),
                    'name' => '_slm_additional_clients',
                    'type' => 'user',
                    'instructions' => __('Other clients associated with this case (e.g., spouse, business partners).', 'flavor'),
                    'role' => ['customer', 'subscriber'],
                    'return_format' => 'id',
                    'multiple' => 1,
                ],
                
                // Matter tab
                [
                    'key' => 'field_slm_case_tab_matter',
                    'label' => __('Matter', 'flavor'),
                    'type' => 'tab',
                ],
                [
                    'key' => 'field_slm_case_reference',
                    'label' => __('Case Reference', 'flavor'),
                    'name' => '_slm_case_reference',
                    'type' => 'text',
                    'instructions' => __('Internal reference number (auto-generated if empty).', 'flavor'),
                    'placeholder' => 'e.g., SLM-2024-001',
                ],
                [
                    'key' => 'field_slm_case_description',
                    'label' => __('Case Description', 'flavor'),
                    'name' => '_slm_case_description',
                    'type' => 'textarea',
                    'rows' => 3,
                ],
                [
                    'key' => 'field_slm_case_services',
                    'label' => __('Services', 'flavor'),
                    'name' => '_slm_services',
                    'type' => 'post_object',
                    'instructions' => __('WooCommerce products/services for this case.', 'flavor'),
                    'post_type' => ['product'],
                    'return_format' => 'id',
                    'multiple' => 1,
                ],
                [
                    'key' => 'field_slm_case_order',
                    'label' => __('Related Order', 'flavor'),
                    'name' => '_slm_order_id',
                    'type' => 'number',
                    'instructions' => __('WooCommerce order ID if applicable.', 'flavor'),
                ],
                
                // Team tab
                [
                    'key' => 'field_slm_case_tab_team',
                    'label' => __('Team', 'flavor'),
                    'type' => 'tab',
                ],
                [
                    'key' => 'field_slm_case_lawyer',
                    'label' => __('Lead Lawyer', 'flavor'),
                    'name' => '_slm_lead_lawyer',
                    'type' => 'user',
                    'role' => ['administrator', 'editor', 'slm_lawyer'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_case_team',
                    'label' => __('Case Team', 'flavor'),
                    'name' => '_slm_case_team',
                    'type' => 'user',
                    'instructions' => __('Additional team members with access to this case.', 'flavor'),
                    'role' => ['administrator', 'editor', 'slm_lawyer', 'slm_paralegal'],
                    'return_format' => 'id',
                    'multiple' => 1,
                ],
                
                // Dates tab
                [
                    'key' => 'field_slm_case_tab_dates',
                    'label' => __('Dates', 'flavor'),
                    'type' => 'tab',
                ],
                [
                    'key' => 'field_slm_case_open_date',
                    'label' => __('Open Date', 'flavor'),
                    'name' => '_slm_open_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ],
                [
                    'key' => 'field_slm_case_target_date',
                    'label' => __('Target Completion', 'flavor'),
                    'name' => '_slm_target_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ],
                [
                    'key' => 'field_slm_case_close_date',
                    'label' => __('Close Date', 'flavor'),
                    'name' => '_slm_close_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ],
                
                // Notes tab
                [
                    'key' => 'field_slm_case_tab_notes',
                    'label' => __('Notes', 'flavor'),
                    'type' => 'tab',
                ],
                [
                    'key' => 'field_slm_case_notes',
                    'label' => __('Internal Notes', 'flavor'),
                    'name' => '_slm_internal_notes',
                    'type' => 'wysiwyg',
                    'instructions' => __('Private notes (not visible to client).', 'flavor'),
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => self::POST_TYPE,
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
    }
    
    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'slm_case_summary',
            __('Case Summary', 'flavor'),
            [__CLASS__, 'render_summary_metabox'],
            self::POST_TYPE,
            'side',
            'high'
        );
        
        add_meta_box(
            'slm_case_documents',
            __('Documents', 'flavor'),
            [__CLASS__, 'render_documents_metabox'],
            self::POST_TYPE,
            'normal',
            'default'
        );
        
        add_meta_box(
            'slm_case_tasks',
            __('Tasks', 'flavor'),
            [__CLASS__, 'render_tasks_metabox'],
            self::POST_TYPE,
            'normal',
            'default'
        );
    }
    
    /**
     * Render summary meta box
     */
    public static function render_summary_metabox($post) {
        $client_id = get_post_meta($post->ID, '_slm_client_id', true);
        $client = $client_id ? get_userdata($client_id) : null;
        
        $doc_count = self::get_document_count($post->ID);
        $task_count = self::get_task_counts($post->ID);
        $status = wp_get_post_terms($post->ID, 'slm_case_status', ['fields' => 'names']);
        ?>
        <div class="slm-case-summary">
            <p><strong><?php esc_html_e('Client:', 'flavor'); ?></strong><br>
            <?php if ($client): ?>
                <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $client_id)); ?>">
                    <?php echo esc_html($client->display_name); ?>
                </a>
            <?php else: ?>
                <em><?php esc_html_e('Not assigned', 'flavor'); ?></em>
            <?php endif; ?>
            </p>
            
            <p><strong><?php esc_html_e('Status:', 'flavor'); ?></strong><br>
            <?php echo !empty($status) ? esc_html($status[0]) : __('Not set', 'flavor'); ?>
            </p>
            
            <p><strong><?php esc_html_e('Documents:', 'flavor'); ?></strong> <?php echo intval($doc_count); ?></p>
            
            <p><strong><?php esc_html_e('Tasks:', 'flavor'); ?></strong><br>
            <?php printf(__('%d pending, %d completed', 'flavor'), $task_count['pending'], $task_count['completed']); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render documents meta box
     */
    public static function render_documents_metabox($post) {
        $documents = get_posts([
            'post_type' => 'slm_document',
            'posts_per_page' => 10,
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $post->ID,
                ],
            ],
        ]);
        
        if (empty($documents)) {
            echo '<p>' . esc_html__('No documents attached to this case.', 'flavor') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . esc_html__('Document', 'flavor') . '</th><th>' . esc_html__('Date', 'flavor') . '</th><th>' . esc_html__('Actions', 'flavor') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($documents as $doc) {
                $edit_url = admin_url('post.php?post=' . $doc->ID . '&action=edit');
                echo '<tr>';
                echo '<td><a href="' . esc_url($edit_url) . '">' . esc_html($doc->post_title) . '</a></td>';
                echo '<td>' . esc_html(get_the_date('', $doc)) . '</td>';
                echo '<td><a href="' . esc_url($edit_url) . '">' . esc_html__('Edit', 'flavor') . '</a></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        $add_url = admin_url('post-new.php?post_type=slm_document&case_id=' . $post->ID);
        echo '<p><a href="' . esc_url($add_url) . '" class="button">' . esc_html__('Add Document', 'flavor') . '</a></p>';
    }
    
    /**
     * Render tasks meta box
     */
    public static function render_tasks_metabox($post) {
        $tasks = get_posts([
            'post_type' => 'slm_task',
            'posts_per_page' => 10,
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $post->ID,
                ],
            ],
            'orderby' => 'meta_value',
            'meta_key' => '_slm_due_date',
            'order' => 'ASC',
        ]);
        
        if (empty($tasks)) {
            echo '<p>' . esc_html__('No tasks for this case.', 'flavor') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>' . esc_html__('Task', 'flavor') . '</th><th>' . esc_html__('Due', 'flavor') . '</th><th>' . esc_html__('Status', 'flavor') . '</th></tr></thead>';
            echo '<tbody>';
            foreach ($tasks as $task) {
                $due = get_post_meta($task->ID, '_slm_due_date', true);
                $status = get_post_meta($task->ID, '_slm_status', true);
                $edit_url = admin_url('post.php?post=' . $task->ID . '&action=edit');
                echo '<tr>';
                echo '<td><a href="' . esc_url($edit_url) . '">' . esc_html($task->post_title) . '</a></td>';
                echo '<td>' . ($due ? esc_html(date_i18n(get_option('date_format'), strtotime($due))) : '-') . '</td>';
                echo '<td>' . esc_html(ucfirst($status ?: 'pending')) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        $add_url = admin_url('post-new.php?post_type=slm_task&case_id=' . $post->ID);
        echo '<p><a href="' . esc_url($add_url) . '" class="button">' . esc_html__('Add Task', 'flavor') . '</a></p>';
    }
    
    /**
     * Save case
     */
    public static function save_case($post_id, $post) {
        // Auto-generate reference if empty
        $reference = get_post_meta($post_id, '_slm_case_reference', true);
        if (empty($reference)) {
            $year = date('Y');
            $count = wp_count_posts(self::POST_TYPE)->publish + 1;
            $reference = sprintf('SLM-%s-%03d', $year, $count);
            update_post_meta($post_id, '_slm_case_reference', $reference);
        }
        
        // Set open date if not set
        $open_date = get_post_meta($post_id, '_slm_open_date', true);
        if (empty($open_date)) {
            update_post_meta($post_id, '_slm_open_date', date('Y-m-d'));
        }
    }
    
    /**
     * Admin columns
     */
    public static function admin_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            if ($key === 'title') {
                $new_columns['slm_reference'] = __('Reference', 'flavor');
                $new_columns['slm_client'] = __('Client', 'flavor');
                $new_columns['slm_lawyer'] = __('Lawyer', 'flavor');
            }
        }
        return $new_columns;
    }
    
    /**
     * Admin column content
     */
    public static function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'slm_reference':
                echo esc_html(get_post_meta($post_id, '_slm_case_reference', true) ?: '-');
                break;
                
            case 'slm_client':
                $client_id = get_post_meta($post_id, '_slm_client_id', true);
                if ($client_id) {
                    $client = get_userdata($client_id);
                    if ($client) {
                        echo '<a href="' . esc_url(admin_url('user-edit.php?user_id=' . $client_id)) . '">';
                        echo esc_html($client->display_name);
                        echo '</a>';
                    }
                } else {
                    echo '-';
                }
                break;
                
            case 'slm_lawyer':
                $lawyer_id = get_post_meta($post_id, '_slm_lead_lawyer', true);
                if ($lawyer_id) {
                    $lawyer = get_userdata($lawyer_id);
                    echo $lawyer ? esc_html($lawyer->display_name) : '-';
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Sortable columns
     */
    public static function sortable_columns($columns) {
        $columns['slm_reference'] = 'slm_reference';
        return $columns;
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('Cases', 'flavor'),
            __('Cases', 'flavor'),
            'edit_posts',
            'slm-cases',
            [__CLASS__, 'render_admin_page'],
            'dashicons-portfolio',
            26
        );
        
        add_submenu_page(
            'slm-cases',
            __('All Cases', 'flavor'),
            __('All Cases', 'flavor'),
            'edit_posts',
            'edit.php?post_type=' . self::POST_TYPE
        );
        
        add_submenu_page(
            'slm-cases',
            __('Add New', 'flavor'),
            __('Add New', 'flavor'),
            'edit_posts',
            'post-new.php?post_type=' . self::POST_TYPE
        );
    }
    
    /**
     * Render admin page (dashboard)
     */
    public static function render_admin_page() {
        $stats = self::get_stats();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Case Management', 'flavor'); ?></h1>
            
            <div class="slm-case-dashboard">
                <div class="slm-stat-cards">
                    <div class="slm-stat-card">
                        <span class="slm-stat-number"><?php echo intval($stats['active']); ?></span>
                        <span class="slm-stat-label"><?php esc_html_e('Active Cases', 'flavor'); ?></span>
                    </div>
                    <div class="slm-stat-card">
                        <span class="slm-stat-number"><?php echo intval($stats['pending']); ?></span>
                        <span class="slm-stat-label"><?php esc_html_e('Pending', 'flavor'); ?></span>
                    </div>
                    <div class="slm-stat-card">
                        <span class="slm-stat-number"><?php echo intval($stats['this_month']); ?></span>
                        <span class="slm-stat-label"><?php esc_html_e('Opened This Month', 'flavor'); ?></span>
                    </div>
                    <div class="slm-stat-card">
                        <span class="slm-stat-number"><?php echo intval($stats['completed']); ?></span>
                        <span class="slm-stat-label"><?php esc_html_e('Completed', 'flavor'); ?></span>
                    </div>
                </div>
                
                <p>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . self::POST_TYPE)); ?>" class="button button-primary">
                        <?php esc_html_e('View All Cases', 'flavor'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . self::POST_TYPE)); ?>" class="button">
                        <?php esc_html_e('Add New Case', 'flavor'); ?>
                    </a>
                </p>
            </div>
        </div>
        
        <style>
            .slm-stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
            .slm-stat-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; text-align: center; border-radius: 4px; }
            .slm-stat-number { display: block; font-size: 36px; font-weight: 600; color: #1e3a5f; }
            .slm-stat-label { display: block; color: #666; margin-top: 5px; }
        </style>
        <?php
    }
    
    /**
     * Get stats
     */
    private static function get_stats() {
        global $wpdb;
        
        $active_term = get_term_by('slug', 'active', 'slm_case_status');
        $pending_term = get_term_by('slug', 'pending', 'slm_case_status');
        $completed_term = get_term_by('slug', 'completed', 'slm_case_status');
        
        return [
            'active' => $active_term ? self::count_by_status($active_term->term_id) : 0,
            'pending' => $pending_term ? self::count_by_status($pending_term->term_id) : 0,
            'completed' => $completed_term ? self::count_by_status($completed_term->term_id) : 0,
            'this_month' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts 
                WHERE post_type = %s 
                AND post_status = 'publish' 
                AND MONTH(post_date) = MONTH(CURDATE()) 
                AND YEAR(post_date) = YEAR(CURDATE())",
                self::POST_TYPE
            )),
        ];
    }
    
    /**
     * Count cases by status
     */
    private static function count_by_status($term_id) {
        $query = new WP_Query([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'slm_case_status',
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ],
        ]);
        return $query->found_posts;
    }
    
    /**
     * Get document count for case
     */
    private static function get_document_count($case_id) {
        $query = new WP_Query([
            'post_type' => 'slm_document',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_slm_case_id',
                    'value' => $case_id,
                ],
            ],
        ]);
        return $query->found_posts;
    }
    
    /**
     * Get task counts for case
     */
    private static function get_task_counts($case_id) {
        global $wpdb;
        
        $pending = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_slm_case_id' AND pm.meta_value = %d
            INNER JOIN $wpdb->postmeta ps ON p.ID = ps.post_id AND ps.meta_key = '_slm_status' AND ps.meta_value != 'completed'
            WHERE p.post_type = 'slm_task' AND p.post_status = 'publish'",
            $case_id
        ));
        
        $completed = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_slm_case_id' AND pm.meta_value = %d
            INNER JOIN $wpdb->postmeta ps ON p.ID = ps.post_id AND ps.meta_key = '_slm_status' AND ps.meta_value = 'completed'
            WHERE p.post_type = 'slm_task' AND p.post_status = 'publish'",
            $case_id
        ));
        
        return [
            'pending' => (int) $pending,
            'completed' => (int) $completed,
        ];
    }
    
    /**
     * AJAX: Get case summary
     */
    public static function ajax_get_summary() {
        check_ajax_referer('slm_ajax_nonce', 'nonce');
        
        $case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
        
        if (!$case_id) {
            wp_send_json_error(['message' => __('Invalid case ID.', 'flavor')]);
        }
        
        $case = get_post($case_id);
        if (!$case || $case->post_type !== self::POST_TYPE) {
            wp_send_json_error(['message' => __('Case not found.', 'flavor')]);
        }
        
        $client_id = get_post_meta($case_id, '_slm_client_id', true);
        $client = $client_id ? get_userdata($client_id) : null;
        
        wp_send_json_success([
            'id' => $case_id,
            'title' => $case->post_title,
            'reference' => get_post_meta($case_id, '_slm_case_reference', true),
            'client' => $client ? [
                'id' => $client->ID,
                'name' => $client->display_name,
                'email' => $client->user_email,
            ] : null,
            'documents' => self::get_document_count($case_id),
            'tasks' => self::get_task_counts($case_id),
        ]);
    }
    
    /**
     * AJAX: Get client cases
     */
    public static function ajax_get_client_cases() {
        check_ajax_referer('slm_ajax_nonce', 'nonce');
        
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        
        if (!$client_id) {
            wp_send_json_error(['message' => __('Invalid client ID.', 'flavor')]);
        }
        
        $cases = get_posts([
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_slm_client_id',
                    'value' => $client_id,
                ],
                [
                    'key' => '_slm_additional_clients',
                    'value' => '"' . $client_id . '"',
                    'compare' => 'LIKE',
                ],
            ],
        ]);
        
        $result = [];
        foreach ($cases as $case) {
            $status = wp_get_post_terms($case->ID, 'slm_case_status', ['fields' => 'names']);
            $result[] = [
                'id' => $case->ID,
                'title' => $case->post_title,
                'reference' => get_post_meta($case->ID, '_slm_case_reference', true),
                'status' => !empty($status) ? $status[0] : '',
            ];
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Create case for client
     */
    public static function create_case($client_id, $title, $args = []) {
        $case_id = wp_insert_post([
            'post_type' => self::POST_TYPE,
            'post_title' => $title,
            'post_status' => 'publish',
        ]);
        
        if (is_wp_error($case_id)) {
            return $case_id;
        }
        
        update_post_meta($case_id, '_slm_client_id', $client_id);
        
        if (!empty($args['lawyer_id'])) {
            update_post_meta($case_id, '_slm_lead_lawyer', $args['lawyer_id']);
        }
        
        if (!empty($args['order_id'])) {
            update_post_meta($case_id, '_slm_order_id', $args['order_id']);
        }
        
        if (!empty($args['services'])) {
            update_post_meta($case_id, '_slm_services', $args['services']);
        }
        
        if (!empty($args['description'])) {
            update_post_meta($case_id, '_slm_case_description', $args['description']);
        }
        
        // Set default status
        wp_set_object_terms($case_id, 'active', 'slm_case_status');
        
        // Set practice area if provided
        if (!empty($args['practice_area'])) {
            wp_set_object_terms($case_id, $args['practice_area'], 'slm_practice_area');
        }
        
        return $case_id;
    }
    
    /**
     * Get cases for user
     */
    public static function get_user_cases($user_id, $status = null) {
        $meta_query = [
            'relation' => 'OR',
            [
                'key' => '_slm_client_id',
                'value' => $user_id,
            ],
            [
                'key' => '_slm_additional_clients',
                'value' => '"' . $user_id . '"',
                'compare' => 'LIKE',
            ],
        ];
        
        $args = [
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
        ];
        
        if ($status) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'slm_case_status',
                    'field' => 'slug',
                    'terms' => $status,
                ],
            ];
        }
        
        return get_posts($args);
    }
}
