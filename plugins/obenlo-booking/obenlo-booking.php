<?php
/**
 * Plugin Name: Obenlo Booking
 * Description: Custom 100% bespoke booking platform for Stays, Experiences, and Services.
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('OBENLO_BOOKING_VERSION', '1.0.5');
define('OBENLO_BOOKING_DIR', plugin_dir_path(__FILE__));
define('OBENLO_BOOKING_URL', plugin_dir_url(__FILE__));

// Include necessary classes
require_once OBENLO_BOOKING_DIR . 'includes/class-post-types.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-roles.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-frontend-dashboard.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-payments.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-stripe.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-paypal.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-reviews.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-frontend-experience.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-admin-dashboard.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-notifications.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-communication.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-host-verification.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-payout-manager.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-badges.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-wishlist.php';
require_once OBENLO_BOOKING_DIR . 'includes/class-i18n.php'; // i18n Localization
require_once OBENLO_BOOKING_DIR . 'includes/class-live-chat-admin.php'; // Live Chat Backend
require_once OBENLO_BOOKING_DIR . 'includes/class-push-notifications.php'; // Web Push Notifications
require_once OBENLO_BOOKING_DIR . 'includes/class-virtual-security.php'; // Virtual Event Security
require_once OBENLO_BOOKING_DIR . 'includes/class-demo-sandbox.php';   // Demo Sandbox
require_once OBENLO_BOOKING_DIR . 'includes/class-admin-settings.php'; // Admin Settings

// Initialize the plugin
function obenlo_booking_init()
{
    $post_types = new Obenlo_Booking_Post_Types();
    $post_types->init();

    $roles = new Obenlo_Booking_Roles();
    $roles->init();

    $frontend = new Obenlo_Booking_Frontend_Dashboard();
    $frontend->init();

    $payments = new Obenlo_Booking_Payments();
    $payments->init();

    $stripe = new Obenlo_Booking_Stripe();
    $stripe->init();

    $paypal = new Obenlo_Booking_PayPal();
    $paypal->init();

    $reviews = new Obenlo_Booking_Reviews();
    $reviews->init();

    $experience = new Obenlo_Booking_Frontend_Experience();
    $experience->init();

    $admin_dashboard = new Obenlo_Booking_Admin_Dashboard();
    $admin_dashboard->init();

    $notifications = new Obenlo_Booking_Notifications();
    $notifications->init();

    $communication = new Obenlo_Booking_Communication();
    $communication->init();

    $verification = new Obenlo_Booking_Host_Verification();
    $verification->init();

    $payouts = new Obenlo_Booking_Payout_Manager();
    $payouts->init();

    $badges = new Obenlo_Booking_Badges();

    $wishlist = new Obenlo_Booking_Wishlist();
    $wishlist->init();

    $i18n = new Obenlo_Booking_i18n();
    $i18n->init();

    $live_chat_admin = new Obenlo_Booking_Live_Chat_Admin();
    $live_chat_admin->init();

    $push_notifications = new Obenlo_Booking_Push_Notifications();
    $push_notifications->init();

    $virtual_security = new Obenlo_Booking_Virtual_Security();
    $virtual_security->init();

    $sandbox = new Obenlo_Booking_Demo_Sandbox();
    $sandbox->init();

    $admin_settings = new Obenlo_Booking_Admin_Settings();
    $admin_settings->init();

    // Check if we need to run DB updates
    obenlo_booking_update_check();
}
add_action('plugins_loaded', 'obenlo_booking_init');

/**
 * Universal Obenlo Whitelabel Redirect
 * Standardizes error handling to prevent users from seeing WordPress-branded screens.
 */
function obenlo_redirect_with_error($error_code) {
    $redirect_url = remove_query_arg(array('obenlo_error', 'message'), wp_get_referer());
    
    // Default fallback if referer is missing or invalid
    if (!$redirect_url) {
        $redirect_url = home_url('/host-dashboard');
    }

    $redirect_url = add_query_arg('obenlo_error', $error_code, $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
}

// Routine to automatically create tables on live site if plugin is already active
function obenlo_booking_update_check()
{
    if (is_admin() && get_site_option('obenlo_booking_db_version') !== OBENLO_BOOKING_VERSION) {
        obenlo_booking_install_tables();
        update_site_option('obenlo_booking_db_version', OBENLO_BOOKING_VERSION);
    }
}

function obenlo_booking_install_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_chat = $wpdb->prefix . 'obenlo_chat_messages';
    $sql_chat = "CREATE TABLE $table_chat (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) NOT NULL,
        receiver_id bigint(20) NOT NULL,
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        is_read tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id),
        KEY sender_id (sender_id),
        KEY receiver_id (receiver_id)
    ) $charset_collate;";

    $table_push = $wpdb->prefix . 'obenlo_push_subscribers';
    $sql_push = "CREATE TABLE $table_push (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        endpoint text NOT NULL,
        p256dh varchar(255) NOT NULL,
        auth varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    if (!function_exists('dbDelta')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }
    dbDelta($sql_chat);
    dbDelta($sql_push);
}

// Emergency manual trigger via admin-ajax
add_action('wp_ajax_obenlo_force_install_db', function () {
    if (!current_user_can('manage_options')) {
        obenlo_redirect_with_error('unauthorized');
    }
    obenlo_booking_install_tables();
    update_site_option('obenlo_booking_db_version', OBENLO_BOOKING_VERSION);
    wp_send_json_success('Tables forcefully created!');
});

// Route root user profile requests (obenlo.com/username) safely without breaking pages
function obenlo_root_author_request($query_vars)
{
    // Check if the query is attempting to load a page or post slug (which catches root URLs)
    if (isset($query_vars['pagename']) || isset($query_vars['name'])) {
        $slug = isset($query_vars['pagename']) ? $query_vars['pagename'] : $query_vars['name'];
        // We only care about root-level slugs (no slashes)
        if (strpos($slug, '/') === false) {
            $user = get_user_by('slug', $slug);
            if ($user) {
                // Valid user found! Convert this page query into an author query
                $query_vars['author_name'] = $slug;
                unset($query_vars['pagename']);
                unset($query_vars['name']);
                if (isset($query_vars['error'])) {
                    unset($query_vars['error']);
                }
            }
        }
    }
    return $query_vars;
}
add_filter('request', 'obenlo_root_author_request');

/**
 * Privacy: Hide demo listings from public archives and search.
 * They remain accessible via direct URL.
 */
function obenlo_hide_demo_listings_from_public($query) {
    if (!is_admin() && $query->is_main_query() && (is_archive() || is_search() || is_home() || is_post_type_archive('listing'))) {
        $meta_query = $query->get('meta_query') ?: array();
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key'     => '_obenlo_is_demo',
                'value'   => 'yes',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key'     => '_obenlo_is_demo',
                'value'   => 'no',
                'compare' => '='
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'obenlo_hide_demo_listings_from_public');

// Change author link dynamically so get_author_posts_url returns root instead of /author/
function obenlo_author_link($link, $author_id)
{
    $author = get_userdata($author_id);
    if ($author) {
        return home_url('/' . $author->user_nicename . '/');
    }
    return $link;
}
add_filter('author_link', 'obenlo_author_link', 10, 2);

// Activation hook for defining roles and rewriting rules
function obenlo_booking_activate()
{
    try {
        error_log('Obenlo Activation: Starting...');

        $post_types = new Obenlo_Booking_Post_Types();
        $post_types->register_custom_post_types();
        error_log('Obenlo Activation: Post types registered.');

        flush_rewrite_rules();
        error_log('Obenlo Activation: Rewrite rules flushed.');

        $roles = new Obenlo_Booking_Roles();
        $roles->add_roles();
        error_log('Obenlo Activation: Roles added.');

        // Enable user registration
        update_option('users_can_register', 1);
        update_option('default_role', 'guest');
        error_log('Obenlo Activation: Options updated.');

        // Initialize Native DB Tables
        obenlo_booking_install_tables();
        update_site_option('obenlo_booking_db_version', OBENLO_BOOKING_VERSION);
        error_log('Obenlo Activation: Custom tables created.');

        $essential_pages = array(
            'demo-access' => array(
                'title' => 'Demo Access',
                'content' => '', // Content is in the template file
                'template' => 'page-demo-access.php'
            )
        );

        foreach ($essential_pages as $slug => $data) {
            error_log('Obenlo Activation: Checking page ' . $slug);
            $page_check = get_page_by_path($slug);
            if (!isset($page_check->ID)) {
                $page_id = wp_insert_post(array(
                    'post_title' => $data['title'],
                    'post_name' => $slug,
                    'post_content' => $data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ));
                
                if ($page_id && !empty($data['template'])) {
                    update_post_meta($page_id, '_wp_page_template', $data['template']);
                }
                error_log('Obenlo Activation: Created page ' . $slug);
            }
        }
    }
    catch (Exception $e) {
        error_log('Obenlo Activation Error: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'obenlo_booking_activate');

// Force /listings page to load the directory archive template
function obenlo_force_listings_archive_template($template)
{
    if (is_page('listings')) {
        $archive_template = locate_template('archive-listing.php');
        if ($archive_template) {
            return $archive_template;
        }
    }
    return $template;
}
add_filter('template_include', 'obenlo_force_listings_archive_template', 99);
