<?php
/**
 * Engagement Letter System - Custom Post Type Registration
 * 
 * Registers and manages the engagement_letter CPT:
 * - Post type registration
 * - Meta boxes for all fields
 * - Admin columns customization
 * - Admin filters and search
 * - Quick edit functionality
 * - Post save hooks
 * 
 * LOAD ORDER: Admin module (after core modules)
 * DEPENDENCIES: constants.php, helpers.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// CPT REGISTRATION
// ============================================

/**
 * Registers engagement_letter custom post type
 */
function el_register_engagement_cpt() {
    $labels = [
        'name' => 'Engagement Letters',
        'singular_name' => 'Engagement Letter',
        'menu_name' => 'Engagement Letters',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Engagement Letter',
        'edit_item' => 'Edit Engagement Letter',
        'new_item' => 'New Engagement Letter',
        'view_item' => 'View Engagement Letter',
        'search_items' => 'Search Engagement Letters',
        'not_found' => 'No engagement letters found',
        'not_found_in_trash' => 'No engagement letters found in trash',
        'all_items' => 'All Engagement Letters',
    ];
    
    $args = [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-media-document',
        'menu_position' => 25,
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'edit_posts',
        ],
        'map_meta_cap' => true,
        'supports' => ['title', 'author'],
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ];
    
    register_post_type(EL_CPT_ENGAGEMENT, $args);
}
add_action('init', 'el_register_engagement_cpt');

// ============================================
// ADMIN COLUMNS
// ============================================

/**
 * Customizes admin columns
 * 
 * @param array $columns Existing columns
 * @return array Modified columns
 */
function el_custom_admin_columns($columns) {
    // Remove default columns
    unset($columns['date']);
    
    // Add custom columns
    $new_columns = [
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'reference' => 'Reference',
        'client' => 'Client',
        'status' => 'Status',
        'practice_area' => 'Practice Area',
        'total' => 'Total',
        'last_active' => 'Last Active',
        'author' => 'Lawyer',
    ];
    
    return $new_columns;
}
add_filter('manage_' . EL_CPT_ENGAGEMENT . '_posts_columns', 'el_custom_admin_columns');

/**
 * Populates custom admin columns
 * 
 * @param string $column  Column name
 * @param int    $post_id Post ID
 */
function el_populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'reference':
            $reference = el_get_meta($post_id, 'reference');
            echo $reference ? '<code>' . esc_html($reference) . '</code>' : '—';
            break;
            
        case 'client':
            $form_data = el_get_meta($post_id, 'form_data');
            if (!empty($form_data)) {
                $name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
                $email = $form_data['email'] ?? '';
                echo '<strong>' . esc_html($name) . '</strong>';
                if ($email) {
                    echo '<br><small>' . esc_html($email) . '</small>';
                }
            } else {
                echo '—';
            }
            break;
            
        case 'status':
            $status = el_get_meta($post_id, 'status', EL_STATUS_DRAFT);
            $status_labels = [
                EL_STATUS_DRAFT => ['label' => 'Draft', 'color' => '#6b7280'],
                EL_STATUS_GENERATED => ['label' => 'Generated', 'color' => '#3b82f6'],
                EL_STATUS_SENT => ['label' => 'Sent', 'color' => '#f59e0b'],
                EL_STATUS_SIGNED => ['label' => 'Signed', 'color' => '#10b981'],
                EL_STATUS_PAID => ['label' => 'Paid', 'color' => '#059669'],
                EL_STATUS_COMPLETED => ['label' => 'Completed', 'color' => '#047857'],
            ];
            
            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
            
            echo '<span style="
                display: inline-block;
                padding: 4px 10px;
                background: ' . esc_attr($status_info['color']) . ';
                color: white;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 600;
            ">' . esc_html($status_info['label']) . '</span>';
            break;
            
        case 'practice_area':
            $area = el_get_meta($post_id, 'practice_area');
            echo $area ? esc_html($area) : '—';
            break;
            
        case 'total':
            $cart_contents = el_get_meta($post_id, 'cart_contents');
            if (!empty($cart_contents['totals']['total'])) {
                echo el_format_currency($cart_contents['totals']['total']);
            } else {
                echo '—';
            }
            break;
            
        case 'last_active':
            $last_active = el_get_meta($post_id, 'last_active');
            if ($last_active) {
                echo '<abbr title="' . esc_attr(el_format_date($last_active, 'Y-m-d H:i:s')) . '">';
                echo esc_html(el_time_ago($last_active));
                echo '</abbr>';
            } else {
                echo '—';
            }
            break;
    }
}
add_action('manage_' . EL_CPT_ENGAGEMENT . '_posts_custom_column', 'el_populate_admin_columns', 10, 2);

/**
 * Makes columns sortable
 * 
 * @param array $columns Sortable columns
 * @return array Modified columns
 */
function el_sortable_admin_columns($columns) {
    $columns['reference'] = 'reference';
    $columns['status'] = 'status';
    $columns['last_active'] = 'last_active';
    
    return $columns;
}
add_filter('manage_edit-' . EL_CPT_ENGAGEMENT . '_sortable_columns', 'el_sortable_admin_columns');

/**
 * Handles custom column sorting
 * 
 * @param WP_Query $query Current query
 */
function el_admin_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('post_type') !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    switch ($orderby) {
        case 'reference':
            $query->set('meta_key', EL_META_REFERENCE);
            $query->set('orderby', 'meta_value');
            break;
            
        case 'status':
            $query->set('meta_key', EL_META_STATUS);
            $query->set('orderby', 'meta_value');
            break;
            
        case 'last_active':
            $query->set('meta_key', EL_META_LAST_ACTIVE);
            $query->set('orderby', 'meta_value');
            break;
    }
}
add_action('pre_get_posts', 'el_admin_column_orderby');

// ============================================
// ADMIN FILTERS
// ============================================

/**
 * Adds status filter dropdown
 */
function el_admin_status_filter() {
    global $typenow;
    
    if ($typenow !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    $current_status = $_GET['el_status'] ?? '';
    
    $statuses = [
        '' => 'All Statuses',
        EL_STATUS_DRAFT => 'Draft',
        EL_STATUS_GENERATED => 'Generated',
        EL_STATUS_SENT => 'Sent',
        EL_STATUS_SIGNED => 'Signed',
        EL_STATUS_PAID => 'Paid',
        EL_STATUS_COMPLETED => 'Completed',
    ];
    
    echo '<select name="el_status">';
    foreach ($statuses as $value => $label) {
        $selected = selected($current_status, $value, false);
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'el_admin_status_filter');

/**
 * Filters posts by status
 * 
 * @param WP_Query $query Current query
 */
function el_admin_filter_by_status($query) {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php' || $typenow !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    if (!empty($_GET['el_status'])) {
        $query->set('meta_query', [
            [
                'key' => EL_META_STATUS,
                'value' => sanitize_text_field($_GET['el_status']),
                'compare' => '=',
            ],
        ]);
    }
}
add_filter('parse_query', 'el_admin_filter_by_status');

// ============================================
// META BOXES
// ============================================

/**
 * Adds meta boxes
 */
function el_add_meta_boxes() {
    // Main details
    add_meta_box(
        'el_details',
        'Engagement Letter Details',
        'el_render_details_meta_box',
        EL_CPT_ENGAGEMENT,
        'normal',
        'high'
    );
    
    // Client information
    add_meta_box(
        'el_client',
        'Client Information',
        'el_render_client_meta_box',
        EL_CPT_ENGAGEMENT,
        'normal',
        'high'
    );
    
    // Services
    add_meta_box(
        'el_services',
        'Selected Services',
        'el_render_services_meta_box',
        EL_CPT_ENGAGEMENT,
        'normal',
        'default'
    );
    
    // Actions
    add_meta_box(
        'el_actions',
        'Actions',
        'el_render_actions_meta_box',
        EL_CPT_ENGAGEMENT,
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'el_add_meta_boxes');

/**
 * Renders details meta box
 */
function el_render_details_meta_box($post) {
    $reference = el_get_meta($post->ID, 'reference');
    $status = el_get_meta($post->ID, 'status', EL_STATUS_DRAFT);
    $practice_area = el_get_meta($post->ID, 'practice_area');
    $created = el_get_meta($post->ID, 'created_date');
    $last_active = el_get_meta($post->ID, 'last_active');
    
    ?>
    <table class="form-table">
        <tr>
            <th>Reference</th>
            <td><code><?php echo esc_html($reference ?: 'Not yet generated'); ?></code></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <select name="el_status" style="width: 200px;">
                    <option value="<?php echo EL_STATUS_DRAFT; ?>" <?php selected($status, EL_STATUS_DRAFT); ?>>Draft</option>
                    <option value="<?php echo EL_STATUS_GENERATED; ?>" <?php selected($status, EL_STATUS_GENERATED); ?>>Generated</option>
                    <option value="<?php echo EL_STATUS_SENT; ?>" <?php selected($status, EL_STATUS_SENT); ?>>Sent</option>
                    <option value="<?php echo EL_STATUS_SIGNED; ?>" <?php selected($status, EL_STATUS_SIGNED); ?>>Signed</option>
                    <option value="<?php echo EL_STATUS_PAID; ?>" <?php selected($status, EL_STATUS_PAID); ?>>Paid</option>
                    <option value="<?php echo EL_STATUS_COMPLETED; ?>" <?php selected($status, EL_STATUS_COMPLETED); ?>>Completed</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Practice Area</th>
            <td><?php echo esc_html($practice_area ?: '—'); ?></td>
        </tr>
        <tr>
            <th>Created</th>
            <td><?php echo $created ? esc_html(el_format_date($created, 'F j, Y g:i A')) : '—'; ?></td>
        </tr>
        <tr>
            <th>Last Active</th>
            <td><?php echo $last_active ? esc_html(el_format_date($last_active, 'F j, Y g:i A')) : '—'; ?></td>
        </tr>
    </table>
    <?php
    wp_nonce_field('el_save_meta', 'el_meta_nonce');
}

/**
 * Renders client meta box
 */
function el_render_client_meta_box($post) {
    $form_data = el_get_meta($post->ID, 'form_data');
    
    if (empty($form_data)) {
        echo '<p>No client information available.</p>';
        return;
    }
    
    ?>
    <table class="form-table">
        <tr>
            <th>Name</th>
            <td><?php echo esc_html(trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''))); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><a href="mailto:<?php echo esc_attr($form_data['email'] ?? ''); ?>"><?php echo esc_html($form_data['email'] ?? '—'); ?></a></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?php echo esc_html($form_data['phone'] ?? '—'); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo nl2br(esc_html(el_format_full_address($form_data))); ?></td>
        </tr>
    </table>
    <?php
}

/**
 * Renders services meta box
 */
function el_render_services_meta_box($post) {
    $cart_contents = el_get_meta($post->ID, 'cart_contents');
    
    if (empty($cart_contents['items'])) {
        echo '<p>No services selected.</p>';
        return;
    }
    
    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Service</th><th style="text-align: right;">Price</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($cart_contents['items'] as $item) {
        echo '<tr>';
        echo '<td><strong>' . esc_html($item['name']) . '</strong></td>';
        echo '<td style="text-align: right;">' . el_format_currency($item['price']) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr><th>Total</th><th style="text-align: right;"><strong>' . el_format_currency($cart_contents['totals']['total']) . '</strong></th></tr>';
    echo '</tfoot>';
    echo '</table>';
}

/**
 * Renders actions meta box
 */
function el_render_actions_meta_box($post) {
    $reference = el_get_meta($post->ID, 'reference');
    $status = el_get_meta($post->ID, 'status');
    
    ?>
    <div class="submitbox">
        <?php if ($reference && function_exists('el_has_pdf_data') && el_has_pdf_data($reference)): ?>
            <div class="misc-pub-section">
                <a href="<?php echo esc_url(add_query_arg(['action' => 'el_download_pdf', 'reference' => $reference, 'nonce' => wp_create_nonce('el_download_' . $reference)], admin_url('admin-ajax.php'))); ?>" class="button button-secondary" target="_blank">
                    📄 Download PDF
                </a>
            </div>
        <?php endif; ?>
        
        <?php if ($status === EL_STATUS_GENERATED || $status === EL_STATUS_SENT): ?>
            <div class="misc-pub-section">
                <button type="button" class="button button-secondary" onclick="alert('Send signature request functionality - integrate with email system')">
                    ✉️ Send for Signature
                </button>
            </div>
        <?php endif; ?>
        
        <div class="misc-pub-section">
            <a href="<?php echo esc_url(home_url('/engagement-letter-wizard/')); ?>" class="button button-secondary">
                ✏️ Edit in Wizard
            </a>
        </div>
    </div>
    <?php
}

// ============================================
// SAVE POST
// ============================================

/**
 * Saves meta box data
 * 
 * @param int $post_id Post ID
 */
function el_save_meta_boxes($post_id) {
    // Security checks
    if (!isset($_POST['el_meta_nonce']) || !wp_verify_nonce($_POST['el_meta_nonce'], 'el_save_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (get_post_type($post_id) !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    // Save status
    if (isset($_POST['el_status'])) {
        el_set_meta($post_id, 'status', sanitize_text_field($_POST['el_status']));
    }
    
    // Update modified date
    el_set_meta($post_id, 'modified_date', current_time('mysql'));
}
add_action('save_post', 'el_save_meta_boxes');

// ============================================
// ADMIN NOTICES
// ============================================

/**
 * Shows admin notices for engagement letters
 */
function el_admin_notices() {
    global $post, $pagenow;
    
    if ($pagenow !== 'post.php' || !$post || get_post_type($post) !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    $status = el_get_meta($post->ID, 'status');
    
    if ($status === EL_STATUS_DRAFT) {
        echo '<div class="notice notice-info"><p><strong>Draft:</strong> This engagement letter is still being prepared.</p></div>';
    }
    
    if ($status === EL_STATUS_SENT) {
        echo '<div class="notice notice-warning"><p><strong>Awaiting Signature:</strong> This engagement letter has been sent to the client.</p></div>';
    }
    
    if ($status === EL_STATUS_SIGNED) {
        echo '<div class="notice notice-success"><p><strong>Signed:</strong> Client has signed this engagement letter.</p></div>';
    }
}
add_action('admin_notices', 'el_admin_notices');

// ============================================
// ADMIN STYLES
// ============================================

/**
 * Adds admin CSS
 */
function el_admin_styles() {
    global $post_type;
    
    if ($post_type !== EL_CPT_ENGAGEMENT) {
        return;
    }
    
    ?>
    <style>
        .widefat th,
        .widefat td {
            padding: 12px;
        }
        
        .form-table th {
            width: 150px;
        }
        
        .submitbox .misc-pub-section {
            padding: 10px;
        }
        
        .submitbox .button {
            width: 100%;
            margin-bottom: 5px;
        }
    </style>
    <?php
}
add_action('admin_head', 'el_admin_styles');

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('CPT Registration module loaded successfully', 'info');
}