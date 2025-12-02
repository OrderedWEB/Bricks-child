<?php
/**
 * SLM Timeline
 * 
 * Handles working days calculations, holidays,
 * and service tier timeline adjustments.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Timeline {
    
    private static $initialized = false;
    private static $holidays_cache = [];
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('admin_menu', [__CLASS__, 'add_admin_pages']);
        add_action('wp_ajax_slm_add_holiday', [__CLASS__, 'ajax_add_holiday']);
        add_action('wp_ajax_slm_delete_holiday', [__CLASS__, 'ajax_delete_holiday']);
        add_action('wp_ajax_slm_import_holidays', [__CLASS__, 'ajax_import_holidays']);
    }
    
    public static function add_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=slm_task_list',
            __('Firm Holidays', 'flavor'),
            __('Holidays', 'flavor'),
            'manage_options',
            'slm-holidays',
            [__CLASS__, 'render_holidays_page']
        );
    }
    
    /**
     * Calculate due date from start date + working days
     */
    public static function calculate_due_date($start_date, $working_days, $service_tier = 'standard') {
        if (is_string($start_date)) {
            $start_date = new DateTime($start_date);
        }
        
        $config = get_option('slm_service_tier_config');
        $tier_config = $config[$service_tier] ?? $config['standard'];
        
        // Apply multiplier
        $adjusted_days = ceil($working_days * $tier_config['multiplier']);
        $adjusted_days = max(1, $adjusted_days);
        
        $working_week = $tier_config['working_week'] ?? 5;
        
        // Get holidays for extended period
        $holidays = self::get_holidays_for_period($start_date, $adjusted_days * 2);
        
        $current_date = clone $start_date;
        $days_counted = 0;
        
        while ($days_counted < $adjusted_days) {
            $current_date->modify('+1 day');
            
            if (self::is_working_day($current_date, $working_week, $holidays)) {
                $days_counted++;
            }
        }
        
        return $current_date;
    }
    
    /**
     * Calculate working days between two dates
     */
    public static function calculate_working_days($start_date, $end_date, $service_tier = 'standard') {
        if (is_string($start_date)) {
            $start_date = new DateTime($start_date);
        }
        if (is_string($end_date)) {
            $end_date = new DateTime($end_date);
        }
        
        $config = get_option('slm_service_tier_config');
        $tier_config = $config[$service_tier] ?? $config['standard'];
        $working_week = $tier_config['working_week'] ?? 5;
        
        $diff = $start_date->diff($end_date)->days;
        $holidays = self::get_holidays_for_period($start_date, $diff + 10);
        
        $current = clone $start_date;
        $working_days = 0;
        
        while ($current <= $end_date) {
            if (self::is_working_day($current, $working_week, $holidays)) {
                $working_days++;
            }
            $current->modify('+1 day');
        }
        
        return $working_days;
    }
    
    /**
     * Check if a date is a working day
     */
    public static function is_working_day($date, $working_week = 5, $holidays = null) {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        $day_of_week = (int) $date->format('N'); // 1 = Monday, 7 = Sunday
        
        // Check weekend (if 5-day week)
        if ($working_week == 5 && $day_of_week > 5) {
            return false;
        }
        
        // Check holidays
        if ($holidays === null) {
            $holidays = self::get_holidays_for_year($date->format('Y'));
        }
        
        $date_string = $date->format('Y-m-d');
        if (in_array($date_string, $holidays)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get next working day from a date
     */
    public static function get_next_working_day($date, $service_tier = 'standard') {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        $config = get_option('slm_service_tier_config');
        $tier_config = $config[$service_tier] ?? $config['standard'];
        $working_week = $tier_config['working_week'] ?? 5;
        
        $holidays = self::get_holidays_for_year($date->format('Y'));
        
        $current = clone $date;
        $max_iterations = 30;
        
        while ($max_iterations-- > 0) {
            if (self::is_working_day($current, $working_week, $holidays)) {
                return $current;
            }
            $current->modify('+1 day');
        }
        
        return $current;
    }
    
    /**
     * Get holidays for a period
     */
    public static function get_holidays_for_period($start_date, $days) {
        if (is_string($start_date)) {
            $start_date = new DateTime($start_date);
        }
        
        $end_date = clone $start_date;
        $end_date->modify("+{$days} days");
        
        $start_year = (int) $start_date->format('Y');
        $end_year = (int) $end_date->format('Y');
        
        $holidays = [];
        for ($year = $start_year; $year <= $end_year; $year++) {
            $holidays = array_merge($holidays, self::get_holidays_for_year($year));
        }
        
        return array_unique($holidays);
    }
    
    /**
     * Get holidays for a specific year
     */
    public static function get_holidays_for_year($year) {
        if (isset(self::$holidays_cache[$year])) {
            return self::$holidays_cache[$year];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'slm_firm_holidays';
        
        // Get specific dates for this year
        $specific = $wpdb->get_col($wpdb->prepare(
            "SELECT holiday_date FROM {$table} 
             WHERE YEAR(holiday_date) = %d AND recurring = 0",
            $year
        ));
        
        // Get recurring dates (apply to this year)
        $recurring = $wpdb->get_results(
            "SELECT holiday_date FROM {$table} WHERE recurring = 1"
        );
        
        $recurring_dates = [];
        foreach ($recurring as $row) {
            $month_day = substr($row->holiday_date, 5);
            $recurring_dates[] = $year . '-' . $month_day;
        }
        
        self::$holidays_cache[$year] = array_unique(array_merge($specific, $recurring_dates));
        
        return self::$holidays_cache[$year];
    }
    
    /**
     * Add a holiday
     */
    public static function add_holiday($date, $name, $recurring = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_firm_holidays';
        
        // Check if already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE holiday_date = %s",
            $date
        ));
        
        if ($existing) {
            return new WP_Error('exists', 'Holiday already exists for this date');
        }
        
        $result = $wpdb->insert($table, [
            'holiday_date' => $date,
            'holiday_name' => $name,
            'recurring' => $recurring ? 1 : 0,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ]);
        
        if ($result) {
            // Clear cache
            self::$holidays_cache = [];
            return $wpdb->insert_id;
        }
        
        return new WP_Error('insert_failed', 'Failed to add holiday');
    }
    
    /**
     * Delete a holiday
     */
    public static function delete_holiday($holiday_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_firm_holidays';
        
        $result = $wpdb->delete($table, ['id' => $holiday_id]);
        
        if ($result) {
            self::$holidays_cache = [];
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all holidays
     */
    public static function get_all_holidays($year = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'slm_firm_holidays';
        
        $sql = "SELECT * FROM {$table}";
        if ($year) {
            $sql .= $wpdb->prepare(" WHERE YEAR(holiday_date) = %d OR recurring = 1", $year);
        }
        $sql .= " ORDER BY holiday_date ASC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Import Italian public holidays
     */
    public static function import_italian_holidays($year) {
        $holidays = [
            ['01-01', __('New Year\'s Day', 'flavor'), true],
            ['01-06', __('Epiphany', 'flavor'), true],
            ['04-25', __('Liberation Day', 'flavor'), true],
            ['05-01', __('Labour Day', 'flavor'), true],
            ['06-02', __('Republic Day', 'flavor'), true],
            ['08-15', __('Assumption of Mary', 'flavor'), true],
            ['11-01', __('All Saints\' Day', 'flavor'), true],
            ['12-08', __('Immaculate Conception', 'flavor'), true],
            ['12-25', __('Christmas Day', 'flavor'), true],
            ['12-26', __('St. Stephen\'s Day', 'flavor'), true],
        ];
        
        // Easter-based holidays (calculate for specific year)
        $easter = self::calculate_easter($year);
        $easter_monday = clone $easter;
        $easter_monday->modify('+1 day');
        
        $added = 0;
        $errors = [];
        
        // Add fixed holidays
        foreach ($holidays as $holiday) {
            $date = $year . '-' . $holiday[0];
            $result = self::add_holiday($date, $holiday[1], $holiday[2]);
            if (!is_wp_error($result)) {
                $added++;
            }
        }
        
        // Add Easter Monday (not recurring - date changes each year)
        $result = self::add_holiday($easter_monday->format('Y-m-d'), __('Easter Monday', 'flavor'), false);
        if (!is_wp_error($result)) {
            $added++;
        }
        
        return [
            'added' => $added,
            'errors' => $errors
        ];
    }
    
    /**
     * Calculate Easter date for a year
     */
    private static function calculate_easter($year) {
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return new DateTime("$year-$month-$day");
    }
    
    /**
     * Render holidays admin page
     */
    public static function render_holidays_page() {
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $holidays = self::get_all_holidays($current_year);
        ?>
        <div class="wrap">
            <h1><?php _e('Firm Holidays', 'flavor'); ?></h1>
            
            <div class="slm-holidays-header" style="display:flex;gap:20px;align-items:center;margin:20px 0;">
                <form method="get" style="display:flex;gap:10px;align-items:center;">
                    <input type="hidden" name="post_type" value="slm_task_list">
                    <input type="hidden" name="page" value="slm-holidays">
                    <label for="year"><?php _e('Year:', 'flavor'); ?></label>
                    <select name="year" id="year" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 5; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php selected($current_year, $y); ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
                
                <button type="button" class="button" id="slm-add-holiday">
                    <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle"></span>
                    <?php _e('Add Holiday', 'flavor'); ?>
                </button>
                
                <button type="button" class="button" id="slm-import-italian">
                    <span class="dashicons dashicons-download" style="vertical-align:middle"></span>
                    <?php _e('Import Italian Holidays', 'flavor'); ?>
                </button>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'flavor'); ?></th>
                        <th><?php _e('Holiday Name', 'flavor'); ?></th>
                        <th><?php _e('Recurring', 'flavor'); ?></th>
                        <th width="100"><?php _e('Actions', 'flavor'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($holidays)): ?>
                        <tr>
                            <td colspan="4"><?php _e('No holidays configured.', 'flavor'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $date = new DateTime($holiday->holiday_date);
                                    echo esc_html($date->format('d/m/Y')); 
                                    echo ' <span style="color:#666">(' . $date->format('l') . ')</span>';
                                    ?>
                                </td>
                                <td><?php echo esc_html($holiday->holiday_name); ?></td>
                                <td>
                                    <?php if ($holiday->recurring): ?>
                                        <span class="dashicons dashicons-yes" style="color:#46b450"></span>
                                        <?php _e('Yes', 'flavor'); ?>
                                    <?php else: ?>
                                        <span style="color:#999"><?php _e('No', 'flavor'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="button button-small slm-delete-holiday" data-id="<?php echo esc_attr($holiday->id); ?>">
                                        <?php _e('Delete', 'flavor'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="slm-timeline-info" style="margin-top:30px;padding:20px;background:#f6f7f7;border:1px solid #ddd;">
                <h3><?php _e('Service Tier Settings', 'flavor'); ?></h3>
                <?php 
                $config = get_option('slm_service_tier_config');
                if ($config):
                ?>
                <table class="widefat" style="max-width:500px;">
                    <thead>
                        <tr>
                            <th><?php _e('Tier', 'flavor'); ?></th>
                            <th><?php _e('Multiplier', 'flavor'); ?></th>
                            <th><?php _e('Working Week', 'flavor'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($config as $key => $tier): ?>
                            <tr>
                                <td><strong><?php echo esc_html($tier['label']); ?></strong></td>
                                <td><?php echo esc_html($tier['multiplier']); ?>x</td>
                                <td><?php echo esc_html($tier['working_week']); ?> <?php _e('days', 'flavor'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add Holiday Modal -->
        <div id="slm-holiday-modal" style="display:none;">
            <div class="slm-modal-content" style="max-width:400px;margin:100px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                <h2><?php _e('Add Holiday', 'flavor'); ?></h2>
                <form id="slm-holiday-form">
                    <p>
                        <label for="holiday_date"><?php _e('Date:', 'flavor'); ?></label><br>
                        <input type="date" name="holiday_date" id="holiday_date" required style="width:100%">
                    </p>
                    <p>
                        <label for="holiday_name"><?php _e('Name:', 'flavor'); ?></label><br>
                        <input type="text" name="holiday_name" id="holiday_name" required style="width:100%">
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" name="recurring" value="1">
                            <?php _e('Recurring annually (same day each year)', 'flavor'); ?>
                        </label>
                    </p>
                    <p style="text-align:right">
                        <button type="button" class="button slm-close-modal"><?php _e('Cancel', 'flavor'); ?></button>
                        <button type="submit" class="button button-primary"><?php _e('Add Holiday', 'flavor'); ?></button>
                    </p>
                </form>
            </div>
        </div>
        
        <style>
            #slm-holiday-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 100000;
            }
        </style>
        
        <script>
        jQuery(function($) {
            var $modal = $('#slm-holiday-modal');
            
            $('#slm-add-holiday').on('click', function() {
                $modal.show();
            });
            
            $('.slm-close-modal').on('click', function() {
                $modal.hide();
            });
            
            $('#slm-holiday-form').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_add_holiday',
                        nonce: '<?php echo wp_create_nonce('slm_tasks_admin'); ?>',
                        date: $('#holiday_date').val(),
                        name: $('#holiday_name').val(),
                        recurring: $('input[name="recurring"]').is(':checked') ? 1 : 0
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Error adding holiday');
                        }
                    }
                });
            });
            
            $('.slm-delete-holiday').on('click', function() {
                if (!confirm('<?php _e('Delete this holiday?', 'flavor'); ?>')) return;
                
                var $btn = $(this);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_delete_holiday',
                        nonce: '<?php echo wp_create_nonce('slm_tasks_admin'); ?>',
                        id: $btn.data('id')
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.closest('tr').fadeOut(function() { $(this).remove(); });
                        }
                    }
                });
            });
            
            $('#slm-import-italian').on('click', function() {
                if (!confirm('<?php _e('Import Italian public holidays for this year?', 'flavor'); ?>')) return;
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_import_holidays',
                        nonce: '<?php echo wp_create_nonce('slm_tasks_admin'); ?>',
                        year: <?php echo $current_year; ?>,
                        country: 'IT'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    // AJAX handlers
    
    public static function ajax_add_holiday() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $date = sanitize_text_field($_POST['date'] ?? '');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $recurring = !empty($_POST['recurring']);
        
        if (empty($date) || empty($name)) {
            wp_send_json_error(['message' => 'Date and name are required']);
        }
        
        $result = self::add_holiday($date, $name, $recurring);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['id' => $result, 'message' => 'Holiday added']);
    }
    
    public static function ajax_delete_holiday() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }
        
        $result = self::delete_holiday($id);
        
        if ($result) {
            wp_send_json_success(['message' => 'Holiday deleted']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete holiday']);
        }
    }
    
    public static function ajax_import_holidays() {
        check_ajax_referer('slm_tasks_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $year = intval($_POST['year'] ?? date('Y'));
        $country = sanitize_text_field($_POST['country'] ?? 'IT');
        
        if ($country === 'IT') {
            $result = self::import_italian_holidays($year);
            wp_send_json_success([
                'message' => sprintf(__('%d holidays imported for %d', 'flavor'), $result['added'], $year)
            ]);
        }
        
        wp_send_json_error(['message' => 'Unknown country']);
    }
    
    /**
     * Get timeline preview for a task
     */
    public static function get_timeline_preview($working_days, $start_date = null, $service_tier = 'standard') {
        if (!$start_date) {
            $start_date = new DateTime();
        } elseif (is_string($start_date)) {
            $start_date = new DateTime($start_date);
        }
        
        $config = get_option('slm_service_tier_config');
        $tier_config = $config[$service_tier] ?? $config['standard'];
        
        $adjusted_days = ceil($working_days * $tier_config['multiplier']);
        $adjusted_days = max(1, $adjusted_days);
        
        $due_date = self::calculate_due_date($start_date, $working_days, $service_tier);
        
        return [
            'start_date' => $start_date->format('Y-m-d'),
            'due_date' => $due_date->format('Y-m-d'),
            'original_working_days' => $working_days,
            'adjusted_working_days' => $adjusted_days,
            'tier' => $service_tier,
            'multiplier' => $tier_config['multiplier'],
            'calendar_days' => $start_date->diff($due_date)->days
        ];
    }
}
