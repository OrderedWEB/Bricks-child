<?php
/**
 * Plugin Name: Zoho WP Sync Plugin with OAuth
 * Description: Syncs Zoho CRM contacts with WordPress users bidirectionally using OAuth and syncs all Zoho fields as user meta.
 *              Provides an admin field settings page to control which fields are editable/viewable/hidden on the profile update form.
 *              Also adds front-end menu items for viewing Zoho Books data (statement, sales orders, invoices, retainer invoices)
 *              and for uploading a file to attach to the Zoho CRM contact.
 * Version: 1.8.5
 * Author: Richard King
 * Text Domain: zwps
 */

// =============================================================================
// Define Constants
// =============================================================================
define( 'ZWPS_OPTIONS', 'zwps_options' );
define( 'ZWPS_TOKENS', 'zwps_zoho_tokens' );
define( 'ZWPS_LOG_FILE', plugin_dir_path( __FILE__ ) . 'zwps_sync_log.txt' );
// Use the EU endpoint for CRM:
define( 'ZOHO_API_BASE', 'https://www.zohoapis.eu' );
// Use the EU endpoint for Zoho Books:
define( 'ZOHO_BOOKS_BASE', 'https://www.zohoapis.eu/books/v3' );

// =============================================================================
// Enqueue Bootstrap and jQuery (Front-end and Admin)
// =============================================================================
add_action('wp_enqueue_scripts', 'zwps_enqueue_scripts');
function zwps_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'zwps_admin_enqueue_scripts');
function zwps_admin_enqueue_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
}
// =============================================================================
// WooCommerce Field Mapping
// =============================================================================
function zwps_get_woocommerce_field_map() {
    return array(
        // Billing fields
        'First_Name'      => 'billing_first_name',
        'Last_Name'       => 'billing_last_name',
        'Email'           => 'billing_email',
        'Phone'           => 'billing_phone',
        'Account_Name'    => 'billing_company',
        'Mailing_Street'  => 'billing_address_1',
        'Mailing_City'    => 'billing_city',
        'Mailing_State'   => 'billing_state',
        'Mailing_Zip'     => 'billing_postcode',
        'Mailing_Country' => 'billing_country',
        
        // Shipping fields
        'Other_Street'    => 'shipping_address_1',
        'Other_City'      => 'shipping_city',
        'Other_State'     => 'shipping_state',
        'Other_Zip'       => 'shipping_postcode',
        'Other_Country'   => 'shipping_country',
    );
}
// =============================================================================
// 1. Enhanced Logging Function (with levels)
// =============================================================================
function zwps_log( $message, $level = 'info' ) {
    $levels = array( 'info', 'warning', 'error' );
    if ( ! in_array( $level, $levels ) ) {
        $level = 'info';
    }
    $log_message = strtoupper( $level ) . ': ' . $message;
    file_put_contents( ZWPS_LOG_FILE, date( 'Y-m-d H:i:s' ) . ' - ' . $log_message . PHP_EOL, FILE_APPEND );
    // Optionally, send notifications for errors.
}

// =============================================================================
// 2. API Helper Class (with token refresh logic)
// =============================================================================
class ZWPS_API {
    public static function get( $url, $args = array() ) {
        $tokens = get_option( ZWPS_TOKENS );
        if ( empty( $tokens['access_token'] ) ) {
            zwps_log( 'No Zoho access token available.', 'error' );
            return new WP_Error( 'no_token', 'No Zoho access token available.' );
        }
        $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $tokens['access_token'];
        $response = wp_remote_get( $url, $args );
        if ( is_wp_error( $response ) ) {
            zwps_log( 'API GET error: ' . $response->get_error_message(), 'error' );
            return $response;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['code'] ) && $body['code'] == 'INVALID_TOKEN' ) {
            zwps_log( 'Access token expired; refreshing token...', 'warning' );
            $refreshed = self::refresh_token();
            if ( is_wp_error( $refreshed ) ) {
                return $refreshed;
            }
            $tokens = get_option( ZWPS_TOKENS );
            $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $tokens['access_token'];
            $response = wp_remote_get( $url, $args );
        }
        return $response;
    }
    public static function post( $url, $args = array() ) {
        $tokens = get_option( ZWPS_TOKENS );
        if ( empty( $tokens['access_token'] ) ) {
            zwps_log( 'No Zoho access token available.', 'error' );
            return new WP_Error( 'no_token', 'No Zoho access token available.' );
        }
        $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $tokens['access_token'];
        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            zwps_log( 'API POST error: ' . $response->get_error_message(), 'error' );
            return $response;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['code'] ) && $body['code'] == 'INVALID_TOKEN' ) {
            zwps_log( 'Access token expired on POST; refreshing token...', 'warning' );
            $refreshed = self::refresh_token();
            if ( is_wp_error( $refreshed ) ) {
                return $refreshed;
            }
            $tokens = get_option( ZWPS_TOKENS );
            $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $tokens['access_token'];
            $response = wp_remote_post( $url, $args );
        }
        return $response;
    }
    public static function refresh_token() {
        $opts = get_option( ZWPS_OPTIONS );
        $tokens = get_option( ZWPS_TOKENS );
        if ( empty( $tokens['refresh_token'] ) ) {
            zwps_log( 'No refresh token available.', 'error' );
            return new WP_Error( 'no_refresh', 'No refresh token available.' );
        }
        $params = array(
            'refresh_token' => $tokens['refresh_token'],
            'client_id'     => trim( $opts['client_id'] ?? '' ),
            'client_secret' => trim( $opts['client_secret'] ?? '' ),
            'grant_type'    => 'refresh_token'
        );
        $url = 'https://accounts.zoho.eu/oauth/v2/token?' . http_build_query( $params );
        $response = wp_remote_post( $url );
        if ( is_wp_error( $response ) ) {
            zwps_log( 'Error refreshing token: ' . $response->get_error_message(), 'error' );
            return $response;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['access_token'] ) ) {
            $tokens['access_token'] = $body['access_token'];
            if ( isset( $body['expires_in'] ) ) {
                $tokens['expires_in'] = $body['expires_in'];
                $tokens['obtained_at'] = time();
            }
            update_option( ZWPS_TOKENS, $tokens );
            zwps_log( 'Token refreshed successfully.', 'info' );
            return true;
        } else {
            zwps_log( 'Failed to refresh token. Response: ' . print_r( $body, true ), 'error' );
            return new WP_Error( 'refresh_failed', 'Failed to refresh token.' );
        }
    }
}

// =============================================================================
// 3. Updated OAuth Flow (Including Zoho Books Scope)
// =============================================================================
function zwps_get_authorize_url() {
    $opts = get_option( ZWPS_OPTIONS );
    $cid = trim( $opts['client_id'] ?? '' );
    $redirect = trim( $opts['redirect_uri'] ?? admin_url( 'admin-post.php?action=zwps_oauth_callback' ) );
    // Request both Zoho CRM and Zoho Books scopes.
    $scopes = urlencode( 'ZohoCRM.modules.ALL ZohoBooks.fullaccess.all' );
    return "https://accounts.zoho.eu/oauth/v2/auth?scope=$scopes&client_id=$cid&response_type=code&access_type=offline&redirect_uri=" . urlencode($redirect);
}

// =============================================================================
// Helper: Get Zoho Books Customer ID by Email
// =============================================================================
function zwps_get_zoho_books_customer_id( $email ) {
    $opts = get_option( ZWPS_OPTIONS );
    $org_id = trim( $opts['zoho_books_org'] ?? '' );
    if ( empty( $org_id ) ) {
        zwps_log( 'Zoho Books Organization ID not set.', 'error' );
        return new WP_Error( 'no_org', 'Zoho Books Organization ID not set.' );
    }
    // Use the updated EU endpoint for customers
    $url = ZOHO_BOOKS_BASE . '/customers?organization_id=' . urlencode( $org_id );
    $response = ZWPS_API::get( $url );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    // Check for a successful response; typically, success is code 0
    if ( !isset( $data['code'] ) || $data['code'] != 0 ) {
        zwps_log( 'Zoho Books API returned an error: ' . print_r( $data, true ), 'error' );
        return new WP_Error( 'api_error', 'Zoho Books API error.' );
    }
    if ( !isset( $data['customers'] ) || !is_array( $data['customers'] ) ) {
        zwps_log( 'Zoho Books API did not return a valid customers array: ' . print_r( $data, true ), 'error' );
        return new WP_Error( 'no_customers', 'No customers found in Zoho Books.' );
    }
    foreach ( $data['customers'] as $customer ) {
        // Check both "customer_email" and "email" keys
        if ( ( isset( $customer['customer_email'] ) && strtolower( $customer['customer_email'] ) === strtolower( $email ) ) ||
             ( isset( $customer['email'] ) && strtolower( $customer['email'] ) === strtolower( $email ) ) ) {
            return $customer['customer_id'];
        }
    }
    zwps_log( 'No matching customer found for email ' . $email . '. Full response: ' . print_r( $data, true ), 'error' );
    return new WP_Error( 'customer_not_found', 'Customer not found in Zoho Books.' );
}

// =============================================================================
// Plugin Activation/Deactivation & Database Setup
// =============================================================================
register_activation_hook( __FILE__, 'zwps_activate_plugin' );
function zwps_activate_plugin() {
    global $wpdb;
    $table = $wpdb->prefix . 'zwps_pending_updates';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        updated_data text NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    if ( ! wp_next_scheduled( 'zwps_sync_cron' ) ) {
        wp_schedule_event( time(), 'hourly', 'zwps_sync_cron' );
    }
}
register_deactivation_hook( __FILE__, 'zwps_deactivate_plugin' );
function zwps_deactivate_plugin() {
    wp_clear_scheduled_hook( 'zwps_sync_cron' );
}

// =============================================================================
// Admin Pages (Settings, Force Sync, Pending Updates, Error Log, Field Settings)
// =============================================================================
add_action( 'admin_menu', 'zwps_admin_menu_pages' );
function zwps_admin_menu_pages() {
    add_menu_page( __( 'Zoho API Settings', 'zwps' ), __( 'Zoho Settings', 'zwps' ), 'manage_options', 'zwps-settings', 'zwps_settings_page' );
    add_submenu_page( 'zwps-settings', __( 'Force Sync', 'zwps' ), __( 'Force Sync', 'zwps' ), 'manage_options', 'zwps-force-sync', 'zwps_sync_button_page' );
    add_submenu_page( 'zwps-settings', __( 'Pending Profile Updates', 'zwps' ), __( 'Pending Updates', 'zwps' ), 'manage_options', 'zwps-pending-updates', 'zwps_pending_updates_page' );
    add_submenu_page( 'zwps-settings', __( 'Error Log', 'zwps' ), __( 'Error Log', 'zwps' ), 'manage_options', 'zwps-error-log', 'zwps_error_log_page' );
    add_submenu_page( 'zwps-settings', __( 'Field Settings', 'zwps' ), __( 'Field Settings', 'zwps' ), 'manage_options', 'zwps-field-settings', 'zwps_field_settings_page' );
}

function zwps_settings_page() {
    $opts     = get_option( ZWPS_OPTIONS );
    $cid      = esc_attr( $opts['client_id'] ?? '' );
    $secret   = esc_attr( $opts['client_secret'] ?? '' );
    $redirect = esc_url( $opts['redirect_uri'] ?? admin_url( 'admin-post.php?action=zwps_oauth_callback' ) );
    $books_org = esc_attr( $opts['zoho_books_org'] ?? '' );
    $tokens   = get_option( ZWPS_TOKENS );
    ?>
    <div class="wrap">
        <h1><?php _e( 'Zoho API Settings', 'zwps' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'zwps_settings_group' ); do_settings_sections( 'zwps-settings' ); ?>
            <table class="form-table">
                <tr>
                    <th><?php _e( 'Client ID', 'zwps' ); ?></th>
                    <td><input type="text" name="<?php echo ZWPS_OPTIONS; ?>[client_id]" value="<?php echo $cid; ?>" size="50" /></td>
                </tr>
                <tr>
                    <th><?php _e( 'Client Secret', 'zwps' ); ?></th>
                    <td><input type="text" name="<?php echo ZWPS_OPTIONS; ?>[client_secret]" value="<?php echo $secret; ?>" size="50" /></td>
                </tr>
                <tr>
                    <th><?php _e( 'Redirect URI', 'zwps' ); ?></th>
                    <td><input type="text" name="<?php echo ZWPS_OPTIONS; ?>[redirect_uri]" value="<?php echo $redirect; ?>" size="50" readonly /></td>
                </tr>
                <tr>
                    <th><?php _e( 'Zoho Books Organization ID', 'zwps' ); ?></th>
                    <td><input type="text" name="<?php echo ZWPS_OPTIONS; ?>[zoho_books_org]" value="<?php echo $books_org; ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <p><strong><?php echo empty( $tokens['access_token'] ) ? __( 'Not Authorized', 'zwps' ) : __( 'Authorized', 'zwps' ); ?></strong></p>
        <a class="button button-primary" href="<?php echo esc_url( zwps_get_authorize_url() ); ?>"><?php _e( 'Authorize with Zoho', 'zwps' ); ?></a>
    </div>
    <?php
}

add_action( 'admin_init', 'zwps_register_settings' );
function zwps_register_settings() {
    register_setting( 'zwps_settings_group', ZWPS_OPTIONS );
}

add_action( 'admin_post_zwps_oauth_callback', 'zwps_oauth_callback_handler' );
function zwps_oauth_callback_handler() {
    if ( isset( $_GET['code'] ) ) {
        $opts = get_option( ZWPS_OPTIONS );
        $params = array(
            'grant_type'    => 'authorization_code',
            'client_id'     => trim( $opts['client_id'] ?? '' ),
            'client_secret' => trim( $opts['client_secret'] ?? '' ),
            'redirect_uri'  => trim( $opts['redirect_uri'] ?? admin_url( 'admin-post.php?action=zwps_oauth_callback' ) ),
            'code'          => sanitize_text_field( $_GET['code'] )
        );
        $res  = wp_remote_post( 'https://accounts.zoho.eu/oauth/v2/token', array( 'body' => $params ) );
        $body = json_decode( wp_remote_retrieve_body( $res ), true );
        if ( isset( $body['access_token'] ) ) {
            update_option( ZWPS_TOKENS, $body );
        }
        wp_redirect( admin_url( 'admin.php?page=zwps-settings' ) );
        exit;
    }
    wp_die( __( 'No code received.', 'zwps' ) );
}

// ---------- Field Settings Admin Page ----------
function zwps_field_settings_page() {
    $found_fields   = get_option( 'zwps_found_fields', array() );
    $field_settings = get_option( 'zwps_field_settings', array() );
    ?>
    <div class="wrap">
        <h1><?php _e( 'Field Settings', 'zwps' ); ?></h1>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <?php wp_nonce_field( 'zwps_field_settings', 'zwps_field_settings_nonce' ); ?>
            <input type="hidden" name="action" value="zwps_save_field_settings">
            <?php if ( ! empty( $found_fields ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Field', 'zwps' ); ?></th>
                            <th><?php _e( 'Visibility', 'zwps' ); ?></th>
                            <th><?php _e( 'Description', 'zwps' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $found_fields as $field ) : 
                        $current = isset( $field_settings[ $field ] ) ? $field_settings[ $field ] : 'viewable';
                        ?>
                        <tr>
                            <td><?php echo esc_html( $field ); ?></td>
                            <td>
                                <select name="field_settings[<?php echo esc_attr( $field ); ?>]">
                                    <option value="editable" <?php selected( $current, 'editable' ); ?>><?php _e( 'Editable', 'zwps' ); ?></option>
                                    <option value="viewable" <?php selected( $current, 'viewable' ); ?>><?php _e( 'Viewable (read-only)', 'zwps' ); ?></option>
                                    <option value="hidden" <?php selected( $current, 'hidden' ); ?>><?php _e( 'Hidden', 'zwps' ); ?></option>
                                </select>
                            </td>
                            <td><?php _e( 'Determines if the field is editable, read-only, or hidden on the profile update form.', 'zwps' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button( __( 'Save Field Settings', 'zwps' ) ); ?>
            <?php else : ?>
                <p><?php _e( 'No custom fields found yet.', 'zwps' ); ?></p>
            <?php endif; ?>
        </form>
    </div>
    <?php
}
add_action( 'admin_post_zwps_save_field_settings', 'zwps_save_field_settings' );
function zwps_save_field_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized user', 'zwps' ) );
    }
    check_admin_referer( 'zwps_field_settings', 'zwps_field_settings_nonce' );
    $new_settings = isset( $_POST['field_settings'] ) ? (array) $_POST['field_settings'] : array();
    update_option( 'zwps_field_settings', $new_settings );
    wp_redirect( admin_url( 'admin.php?page=zwps-field-settings&updated=true' ) );
    exit;
}

// ---------- Force Sync Page ----------
function zwps_sync_button_page() {
    if ( isset( $_POST['force_sync'] ) ) {
        zwps_run_full_sync();
        echo '<div class="updated"><p>' . __( 'Sync triggered successfully.', 'zwps' ) . '</p></div>';
    }
    echo '<div class="wrap"><h2>' . __( 'Force Sync', 'zwps' ) . '</h2>';
    echo '<form method="post"><input type="submit" name="force_sync" value="' . __( 'Run Sync Now', 'zwps' ) . '" class="button button-primary"></form></div>';
}

// ---------- Pending Updates Page ----------
function zwps_pending_updates_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'zwps_pending_updates';
    $pending_updates = $wpdb->get_results( "SELECT * FROM $table_name WHERE status = 'pending'" );
    ?>
    <div class="wrap">
        <h1><?php _e( 'Pending Profile Updates', 'zwps' ); ?></h1>
        <?php if ( ! empty( $pending_updates ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'zwps' ); ?></th>
                        <th><?php _e( 'User ID', 'zwps' ); ?></th>
                        <th><?php _e( 'Updated Data', 'zwps' ); ?></th>
                        <th><?php _e( 'Date', 'zwps' ); ?></th>
                        <th><?php _e( 'Actions', 'zwps' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $pending_updates as $update ) : 
                        $data = maybe_unserialize( $update->updated_data );
                        ?>
                        <tr>
                            <td><?php echo esc_html( $update->id ); ?></td>
                            <td><?php echo esc_html( $update->user_id ); ?></td>
                            <td>
                                <?php
                                foreach ( $data as $key => $value ) {
                                    echo esc_html( $key ) . ': ' . esc_html( $value ) . '<br>';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( $update->created_at ); ?></td>
                            <td>
                                <a href="<?php echo admin_url( 'admin-post.php?action=zwps_approve_update&id=' . $update->id ); ?>"><?php _e( 'Approve', 'zwps' ); ?></a> |
                                <a href="<?php echo admin_url( 'admin-post.php?action=zwps_reject_update&id=' . $update->id ); ?>"><?php _e( 'Reject', 'zwps' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e( 'No pending updates found.', 'zwps' ); ?></p>
        <?php endif; ?>
    </div>
    <?php
}
add_action( 'admin_post_zwps_approve_update', 'zwps_handle_approve_update' );
function zwps_handle_approve_update() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized user', 'zwps' ) );
    }
    global $wpdb;
    $id = intval( $_GET['id'] );
    $table_name = $wpdb->prefix . 'zwps_pending_updates';
    $update = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
    if ( $update ) {
        $data = maybe_unserialize( $update->updated_data );
        $user_id = $update->user_id;
        wp_update_user( array(
            'ID'         => $user_id,
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'user_email' => $data['user_email']
        ) );
        foreach ( $data as $key => $value ) {
            if ( ! in_array( $key, array( 'first_name', 'last_name', 'user_email' ) ) ) {
                update_user_meta( $user_id, $key, $value );
            }
        }
        $wpdb->update( $table_name, array( 'status' => 'approved' ), array( 'id' => $id ) );
        wp_redirect( admin_url( 'admin.php?page=zwps-pending-updates' ) );
        exit;
    }
    wp_die( __( 'Update not found.', 'zwps' ) );
}
add_action( 'admin_post_zwps_reject_update', 'zwps_handle_reject_update' );
function zwps_handle_reject_update() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized user', 'zwps' ) );
    }
    global $wpdb;
    $id = intval( $_GET['id'] );
    $table_name = $wpdb->prefix . 'zwps_pending_updates';
    $wpdb->update( $table_name, array( 'status' => 'rejected' ), array( 'id' => $id ) );
    wp_redirect( admin_url( 'admin.php?page=zwps-pending-updates' ) );
    exit;
}

// ---------- Error Log Page ----------
function zwps_error_log_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Error Log', 'zwps' ); ?></h1>
        <?php
        if ( file_exists( ZWPS_LOG_FILE ) ) {
            $log_content = file_get_contents( ZWPS_LOG_FILE );
            if ( $log_content ) {
                echo '<pre style="background:#f7f7f7; padding:10px; border:1px solid #ccc; max-height:500px; overflow:auto;">' . esc_html( $log_content ) . '</pre>';
            } else {
                echo '<p>' . __( 'No log entries found.', 'zwps' ) . '</p>';
            }
        } else {
            echo '<p>' . __( 'The log file does not exist.', 'zwps' ) . '</p>';
        }
        ?>
    </div>
    <?php
}

// =============================================================================
// 4. CRM-to-WP Sync (with Pagination, Full Field Sync, and Custom Field Registration)
// =============================================================================
add_action( 'zwps_sync_cron', 'zwps_run_full_sync' );
function zwps_run_full_sync() {
    zwps_sync_crm_to_wp();
}
function zwps_sync_crm_to_wp() {
    $tokens = get_option( ZWPS_TOKENS );
    if ( empty( $tokens['access_token'] ) ) {
        zwps_log( 'No Zoho access token available.', 'error' );
        return;
    }
    $page = 1;
    do {
        $url = ZOHO_API_BASE . '/crm/v2/Contacts?page=' . $page;
        $args = array();
        $response = ZWPS_API::get( $url, $args );
        if ( is_wp_error( $response ) ) {
            zwps_log( 'Error fetching contacts from Zoho: ' . $response->get_error_message(), 'error' );
            return;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['data'] ) ) {
            zwps_log( sprintf( 'No contacts data received from Zoho on page %d.', $page ) );
            break;
        }
        $found_fields = get_option( 'zwps_found_fields', array() );
        foreach ( $body['data'] as $contact ) {
            if ( empty( $contact['Email'] ) ) {
                zwps_log( 'Skipping Zoho contact due to missing Email.', 'warning' );
                continue;
            }
            $email = sanitize_email( $contact['Email'] );
            if ( empty( $email ) ) {
                zwps_log( 'Skipping Zoho contact due to invalid Email.', 'warning' );
                continue;
            }
            $first_name = sanitize_text_field( $contact['First_Name'] ?? '' );
            $last_name  = sanitize_text_field( $contact['Last_Name'] ?? '' );
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                wp_update_user( array(
                    'ID'         => $user->ID,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'user_email' => $email
                ) );
                $user_id = $user->ID;
                zwps_log( sprintf( 'Updated user %d from Zoho contact.', $user_id ) );
            } else {
                $password = wp_generate_password();
                $username_base = sanitize_user( current( explode( '@', $email ) ), true );
                $username = $username_base;
                $suffix = 1;
                while ( username_exists( $username ) ) {
                    $username = $username_base . '_' . $suffix;
                    $suffix++;
                }
                $user_id = wp_create_user( $username, $password, $email );
                if ( is_wp_error( $user_id ) ) {
                    zwps_log( sprintf( 'Error creating WP user for %s: %s', $email, $user_id->get_error_message() ), 'error' );
                    continue;
                }
                wp_update_user( array(
                    'ID'         => $user_id,
                    'first_name' => $first_name,
                    'last_name'  => $last_name
                ) );
                zwps_log( sprintf( 'Created new user %d from Zoho contact with email: %s', $user_id, $email ) );
            }
            if ( isset( $contact['id'] ) ) {
                update_user_meta( $user_id, 'CRM_ID', sanitize_text_field( $contact['id'] ) );
            }
     $core_fields = array( 'Email', 'First_Name', 'Last_Name' );
$woo_field_map = zwps_get_woocommerce_field_map();

foreach ( $contact as $field => $value ) {
    if ( in_array( $field, $core_fields ) ) {
        continue;
    }
    if ( ! in_array( $field, $found_fields ) ) {
        $found_fields[] = $field;
    }
    register_meta( 'user', $field, array(
        'type'         => 'string',
        'description'  => 'Synced from Zoho CRM',
        'single'       => true,
        'show_in_rest' => true,
    ) );
 $value = apply_filters( 'zwps_transform_field', $value, $field, $user_id );
$value = zwps_extract_field_value( $value, $field );
    
    // Store with Zoho field name
    update_user_meta( $user_id, $field, $value );
    
    // ALSO store with WooCommerce field name if mapping exists
    if ( isset( $woo_field_map[ $field ] ) ) {
        $woo_field_name = $woo_field_map[ $field ];
        update_user_meta( $user_id, $woo_field_name, $value );
        zwps_log( sprintf( 'Mapped %s to %s for user %d', $field, $woo_field_name, $user_id ) );
    }
}
        }
        update_option( 'zwps_found_fields', $found_fields );
        $more = isset( $body['info']['more_records'] ) ? $body['info']['more_records'] : false;
        $page++;
    } while ( $more );
}
// =============================================================================
// Helper: Convert Country Names to WooCommerce ISO Codes
// =============================================================================
function zwps_convert_country_to_code( $country_name ) {
    if ( empty( $country_name ) ) {
        return '';
    }
    
    // If it's already a 2-letter code, return it
    if ( strlen( $country_name ) === 2 ) {
        return strtoupper( $country_name );
    }
    
    // Country name to ISO code mapping
    $country_map = array(
        'afghanistan' => 'AF',
        'albania' => 'AL',
        'algeria' => 'DZ',
        'andorra' => 'AD',
        'angola' => 'AO',
        'antigua and barbuda' => 'AG',
        'argentina' => 'AR',
        'armenia' => 'AM',
        'australia' => 'AU',
        'austria' => 'AT',
        'azerbaijan' => 'AZ',
        'bahamas' => 'BS',
        'bahrain' => 'BH',
        'bangladesh' => 'BD',
        'barbados' => 'BB',
        'belarus' => 'BY',
        'belgium' => 'BE',
        'belize' => 'BZ',
        'benin' => 'BJ',
        'bhutan' => 'BT',
        'bolivia' => 'BO',
        'bosnia and herzegovina' => 'BA',
        'botswana' => 'BW',
        'brazil' => 'BR',
        'brunei' => 'BN',
        'bulgaria' => 'BG',
        'burkina faso' => 'BF',
        'burundi' => 'BI',
        'cambodia' => 'KH',
        'cameroon' => 'CM',
        'canada' => 'CA',
        'cape verde' => 'CV',
        'central african republic' => 'CF',
        'chad' => 'TD',
        'chile' => 'CL',
        'china' => 'CN',
        'colombia' => 'CO',
        'comoros' => 'KM',
        'congo' => 'CG',
        'costa rica' => 'CR',
        'croatia' => 'HR',
        'cuba' => 'CU',
        'cyprus' => 'CY',
        'czech republic' => 'CZ',
        'denmark' => 'DK',
        'djibouti' => 'DJ',
        'dominica' => 'DM',
        'dominican republic' => 'DO',
        'ecuador' => 'EC',
        'egypt' => 'EG',
        'el salvador' => 'SV',
        'equatorial guinea' => 'GQ',
        'eritrea' => 'ER',
        'estonia' => 'EE',
        'ethiopia' => 'ET',
        'fiji' => 'FJ',
        'finland' => 'FI',
        'france' => 'FR',
        'gabon' => 'GA',
        'gambia' => 'GM',
        'georgia' => 'GE',
        'germany' => 'DE',
        'ghana' => 'GH',
        'greece' => 'GR',
        'grenada' => 'GD',
        'guatemala' => 'GT',
        'guinea' => 'GN',
        'guinea-bissau' => 'GW',
        'guyana' => 'GY',
        'haiti' => 'HT',
        'honduras' => 'HN',
        'hungary' => 'HU',
        'iceland' => 'IS',
        'india' => 'IN',
        'indonesia' => 'ID',
        'iran' => 'IR',
        'iraq' => 'IQ',
        'ireland' => 'IE',
        'israel' => 'IL',
        'italy' => 'IT',
        'jamaica' => 'JM',
        'japan' => 'JP',
        'jordan' => 'JO',
        'kazakhstan' => 'KZ',
        'kenya' => 'KE',
        'kiribati' => 'KI',
        'korea, north' => 'KP',
        'korea, south' => 'KR',
        'south korea' => 'KR',
        'kuwait' => 'KW',
        'kyrgyzstan' => 'KG',
        'laos' => 'LA',
        'latvia' => 'LV',
        'lebanon' => 'LB',
        'lesotho' => 'LS',
        'liberia' => 'LR',
        'libya' => 'LY',
        'liechtenstein' => 'LI',
        'lithuania' => 'LT',
        'luxembourg' => 'LU',
        'macedonia' => 'MK',
        'madagascar' => 'MG',
        'malawi' => 'MW',
        'malaysia' => 'MY',
        'maldives' => 'MV',
        'mali' => 'ML',
        'malta' => 'MT',
        'marshall islands' => 'MH',
        'mauritania' => 'MR',
        'mauritius' => 'MU',
        'mexico' => 'MX',
        'micronesia' => 'FM',
        'moldova' => 'MD',
        'monaco' => 'MC',
        'mongolia' => 'MN',
        'montenegro' => 'ME',
        'morocco' => 'MA',
        'mozambique' => 'MZ',
        'myanmar' => 'MM',
        'namibia' => 'NA',
        'nauru' => 'NR',
        'nepal' => 'NP',
        'netherlands' => 'NL',
        'new zealand' => 'NZ',
        'nicaragua' => 'NI',
        'niger' => 'NE',
        'nigeria' => 'NG',
        'norway' => 'NO',
        'oman' => 'OM',
        'pakistan' => 'PK',
        'palau' => 'PW',
        'panama' => 'PA',
        'papua new guinea' => 'PG',
        'paraguay' => 'PY',
        'peru' => 'PE',
        'philippines' => 'PH',
        'poland' => 'PL',
        'portugal' => 'PT',
        'qatar' => 'QA',
        'romania' => 'RO',
        'russia' => 'RU',
        'russian federation' => 'RU',
        'rwanda' => 'RW',
        'saint kitts and nevis' => 'KN',
        'saint lucia' => 'LC',
        'saint vincent and the grenadines' => 'VC',
        'samoa' => 'WS',
        'san marino' => 'SM',
        'sao tome and principe' => 'ST',
        'saudi arabia' => 'SA',
        'senegal' => 'SN',
        'serbia' => 'RS',
        'seychelles' => 'SC',
        'sierra leone' => 'SL',
        'singapore' => 'SG',
        'slovakia' => 'SK',
        'slovenia' => 'SI',
        'solomon islands' => 'SB',
        'somalia' => 'SO',
        'south africa' => 'ZA',
        'south sudan' => 'SS',
        'spain' => 'ES',
        'sri lanka' => 'LK',
        'sudan' => 'SD',
        'suriname' => 'SR',
        'swaziland' => 'SZ',
        'sweden' => 'SE',
        'switzerland' => 'CH',
        'syria' => 'SY',
        'taiwan' => 'TW',
        'tajikistan' => 'TJ',
        'tanzania' => 'TZ',
        'thailand' => 'TH',
        'timor-leste' => 'TL',
        'togo' => 'TG',
        'tonga' => 'TO',
        'trinidad and tobago' => 'TT',
        'tunisia' => 'TN',
        'turkey' => 'TR',
        'turkmenistan' => 'TM',
        'tuvalu' => 'TV',
        'uganda' => 'UG',
        'ukraine' => 'UA',
        'united arab emirates' => 'AE',
        'united kingdom' => 'GB',
        'uk' => 'GB',
        'great britain' => 'GB',
        'united states' => 'US',
        'usa' => 'US',
        'united states of america' => 'US',
        'uruguay' => 'UY',
        'uzbekistan' => 'UZ',
        'vanuatu' => 'VU',
        'vatican city' => 'VA',
        'venezuela' => 'VE',
        'vietnam' => 'VN',
        'yemen' => 'YE',
        'zambia' => 'ZM',
        'zimbabwe' => 'ZW',
    );
    
    $country_lower = strtolower( trim( $country_name ) );
    
    if ( isset( $country_map[ $country_lower ] ) ) {
        return $country_map[ $country_lower ];
    }
    
    // If not found, log it and return the original
    zwps_log( 'Unknown country name: ' . $country_name, 'warning' );
    return $country_name;
}
// =============================================================================
// Helper: Extract Value from Zoho Lookup Fields
// =============================================================================
function zwps_extract_field_value( $value, $field_name = '' ) {
    // If it's an array with 'name' and 'id', it's a lookup field - extract just the name
    if ( is_array( $value ) && isset( $value['name'] ) && isset( $value['id'] ) ) {
        $value = sanitize_text_field( $value['name'] );
    }
    
    // If it's still an array (multi-select or other complex field), serialize it
    if ( is_array( $value ) ) {
        return maybe_serialize( $value );
    }
    
    // Convert country names to ISO codes for country fields
    if ( strpos( $field_name, 'Country' ) !== false && ! empty( $value ) ) {
        return zwps_convert_country_to_code( $value );
    }
    
    // Otherwise, just sanitize the text
    return sanitize_text_field( $value );
}
// =============================================================================
// 5. Zoho Books Data Retrieval (with caching) and Shortcode
// =============================================================================
function zwps_get_zoho_books_data( $type, $email ) {
    $opts = get_option( ZWPS_OPTIONS );
    $org_id = trim( $opts['zoho_books_org'] ?? '' );
    if ( empty( $org_id ) ) {
        zwps_log( 'Zoho Books Organization ID not set.', 'error' );
        return new WP_Error( 'no_org', 'Zoho Books Organization ID not set.' );
    }
    $endpoints = array(
        'invoices'         => '/invoices',
        'salesorders'      => '/salesorders',
        'retainerinvoices' => '/retainerinvoices',
        'statement'        => '/customerstatements'
    );
    if ( ! isset( $endpoints[ $type ] ) ) {
        return new WP_Error( 'invalid_type', 'Invalid Zoho Books data type requested.' );
    }
    // Look up the customer_id from Zoho Books using the user's email.
    $customer_id = zwps_get_zoho_books_customer_id( $email );
    if ( is_wp_error( $customer_id ) ) {
        return $customer_id;
    }
    $transient_key = 'zwps_books_' . $type . '_' . md5( $customer_id );
    $data = get_transient( $transient_key );
    if ( false !== $data ) {
        return $data;
    }
    $url = ZOHO_BOOKS_BASE . $endpoints[ $type ] . '?customer_id=' . urlencode( $customer_id ) . '&organization_id=' . urlencode( $org_id );
    $response = ZWPS_API::get( $url );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    set_transient( $transient_key, $data, 10 * MINUTE_IN_SECONDS );
    return $data;
}
add_shortcode( 'zwps_zoho_books', 'zwps_zoho_books_page' );
function zwps_zoho_books_page() {
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to view this data.</p>';
    }
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    $type = isset( $_GET['books_type'] ) ? sanitize_text_field( $_GET['books_type'] ) : 'invoices';
    $output = '<div class="zwps-books-nav mb-3">';
    $output .= '<a class="btn btn-primary mr-2" href="' . add_query_arg( 'books_type', 'statement' ) . '">Statement</a>';
    $output .= '<a class="btn btn-primary mr-2" href="' . add_query_arg( 'books_type', 'salesorders' ) . '">Sales Orders</a>';
    $output .= '<a class="btn btn-primary mr-2" href="' . add_query_arg( 'books_type', 'invoices' ) . '">Invoices</a>';
    $output .= '<a class="btn btn-primary" href="' . add_query_arg( 'books_type', 'retainerinvoices' ) . '">Retainer Invoices</a>';
    $output .= '</div>';
    $output .= '<div class="zwps-books-data">';
    $data = zwps_get_zoho_books_data( $type, $email );
    if ( is_wp_error( $data ) ) {
        $output .= '<p>Error fetching data: ' . $data->get_error_message() . '</p>';
    } else {
        $output .= '<pre>' . esc_html( print_r( $data, true ) ) . '</pre>';
    }
    $output .= '</div>';
    return $output;
}

// =============================================================================
// 6. File Upload to Zoho CRM (Attach File to Contact Record) and Shortcode
// =============================================================================
add_shortcode( 'zwps_upload_file', 'zwps_upload_file_form' );
function zwps_upload_file_form() {
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to upload a file.</p>';
    }
    $current_user = wp_get_current_user();
    $crm_id = get_user_meta( $current_user->ID, 'CRM_ID', true );
    if ( empty( $crm_id ) ) {
        return '<p>No Zoho CRM contact record found for your account.</p>';
    }
    $output = '';
    if ( isset( $_POST['zwps_upload_nonce'] ) && wp_verify_nonce( $_POST['zwps_upload_nonce'], 'zwps_upload' ) ) {
        if ( ! empty( $_FILES['attachment']['name'] ) ) {
            $result = zwps_upload_file_to_crm( $crm_id, $_FILES['attachment'] );
            if ( is_wp_error( $result ) ) {
                $output .= '<p class="text-danger">Error: ' . $result->get_error_message() . '</p>';
            } else {
                $output .= '<p class="text-success">File uploaded successfully!</p>';
            }
        } else {
            $output .= '<p class="text-danger">Please select a file to upload.</p>';
        }
    }
    $output .= '<form method="post" enctype="multipart/form-data">';
    $output .= wp_nonce_field( 'zwps_upload', 'zwps_upload_nonce', true, false );
    $output .= '<div class="form-group"><label>Select File:</label> <input class="form-control-file" type="file" name="attachment"></div>';
    $output .= '<input class="btn btn-primary" type="submit" value="Upload File">';
    $output .= '</form>';
    return $output;
}
function zwps_upload_file_to_crm( $crm_id, $file ) {
    $tokens = get_option( ZWPS_TOKENS );
    if ( empty( $tokens['access_token'] ) ) {
        return new WP_Error( 'no_token', 'No Zoho access token available.' );
    }
    $url = ZOHO_API_BASE . '/crm/v2/Contacts/' . urlencode( $crm_id ) . '/Attachments';
    if ( ! function_exists( 'curl_init' ) ) {
        return new WP_Error( 'no_curl', 'cURL is not available on this server.' );
    }
    $temp_file = $file['tmp_name'];
    $mime_type = mime_content_type( $temp_file );
    $filename = basename( $file['name'] );
    $cfile = new CURLFile( $temp_file, $mime_type, $filename );
    $postfields = array(
        'file' => $cfile
    );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Zoho-oauthtoken ' . $tokens['access_token']
    ) );
    $result = curl_exec( $ch );
    $error  = curl_error( $ch );
    curl_close( $ch );
    if ( $error ) {
        zwps_log( 'File upload error: ' . $error, 'error' );
        return new WP_Error( 'upload_error', $error );
    }
    $result_data = json_decode( $result, true );
    if ( isset( $result_data['data'] ) ) {
        zwps_log( 'File uploaded to Zoho CRM for contact ' . $crm_id, 'info' );
        return $result_data;
    } else {
        zwps_log( 'Unexpected response from file upload: ' . print_r( $result_data, true ), 'error' );
        return new WP_Error( 'upload_failed', 'Unexpected response from Zoho CRM.' );
    }
}

// =============================================================================
// 7. Front-end User Profile Update Form (with Custom Fields)
// =============================================================================
add_shortcode( 'zwps_profile_update', 'zwps_profile_update_form' );
function zwps_profile_update_form() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged in to update your profile.', 'zwps' ) . '</p>';
    }
    $user_id = get_current_user_id();
    if ( isset( $_POST['zwps_profile_update_nonce'] ) && wp_verify_nonce( $_POST['zwps_profile_update_nonce'], 'zwps_profile_update' ) ) {
        $updated_data = array(
            'first_name' => sanitize_text_field( $_POST['first_name'] ),
            'last_name'  => sanitize_text_field( $_POST['last_name'] ),
            'user_email' => sanitize_email( $_POST['user_email'] )
        );
        $field_settings = get_option( 'zwps_field_settings', array() );
        if ( ! empty( $field_settings ) ) {
            foreach ( $field_settings as $field => $visibility ) {
                if ( $visibility === 'editable' && isset( $_POST['zwps_field_' . $field] ) ) {
                    $updated_data[ $field ] = sanitize_text_field( $_POST['zwps_field_' . $field] );
                }
            }
        }
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'zwps_pending_updates', array(
            'user_id'      => $user_id,
            'updated_data' => maybe_serialize( $updated_data ),
            'status'       => 'pending'
        ) );
        echo '<p>' . __( 'Your update has been submitted for review.', 'zwps' ) . '</p>';
    }
    $user = wp_get_current_user();
    ob_start();
    ?>
    <form method="post">
        <?php wp_nonce_field( 'zwps_profile_update', 'zwps_profile_update_nonce' ); ?>
        <div class="form-group">
            <label><?php _e( 'First Name', 'zwps' ); ?></label>
            <input class="form-control" type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required>
        </div>
        <div class="form-group">
            <label><?php _e( 'Last Name', 'zwps' ); ?></label>
            <input class="form-control" type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" required>
        </div>
        <div class="form-group">
            <label><?php _e( 'Email', 'zwps' ); ?></label>
            <input class="form-control" type="email" name="user_email" value="<?php echo esc_attr( $user->user_email ); ?>" required>
        </div>
        <?php
        $field_settings = get_option( 'zwps_field_settings', array() );
        if ( ! empty( $field_settings ) ) {
            foreach ( $field_settings as $field => $visibility ) {
                $meta_value = get_user_meta( $user->ID, $field, true );
                if ( $visibility === 'editable' ) {
                    echo '<div class="form-group"><label>' . esc_html( $field ) . '</label> <input class="form-control" type="text" name="zwps_field_' . esc_attr( $field ) . '" value="' . esc_attr( $meta_value ) . '"></div>';
                } elseif ( $visibility === 'viewable' ) {
                    echo '<div class="form-group"><label>' . esc_html( $field ) . '</label> <input class="form-control" type="text" value="' . esc_attr( $meta_value ) . '" disabled></div>';
                }
            }
        }
        ?>
        <input class="btn btn-primary" type="submit" value="<?php _e( 'Update Profile', 'zwps' ); ?>">
    </form>
    <?php
    return ob_get_clean();
}
?>
