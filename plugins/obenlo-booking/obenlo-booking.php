<?php
/**
 * Plugin Name: Obenlo Booking
 * Description: Custom 100% bespoke booking platform for Stays, Experiences, and Services.
 * Version: 1.0.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'OBENLO_BOOKING_VERSION', '1.0.0' );
define( 'OBENLO_BOOKING_DIR', plugin_dir_path( __FILE__ ) );
define( 'OBENLO_BOOKING_URL', plugin_dir_url( __FILE__ ) );

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

// Initialize the plugin
function obenlo_booking_init() {
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
}
add_action( 'plugins_loaded', 'obenlo_booking_init' );

// Change author base to host safely
function obenlo_custom_author_base() {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'host';
}
add_action( 'init', 'obenlo_custom_author_base' );

// Activation hook for defining roles and rewriting rules
function obenlo_booking_activate() {
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
        update_option( 'users_can_register', 1 );
        update_option( 'default_role', 'guest' );
        error_log('Obenlo Activation: Options updated.');

        // Create essential pages if they don't exist
        $essential_pages = array(
            'wishlists' => array(
                'title'   => 'Wishlists',
                'content' => '', // Template handles content
            ),
            'messages' => array(
                'title'   => 'Messages',
                'content' => '[obenlo_messages_page]',
            ),
            'support' => array(
                'title'   => 'Support',
                'content' => '[obenlo_support_page]',
            ),
            'blog' => array(
                'title'   => 'Blog',
                'content' => '',
            ),
        );

        foreach ( $essential_pages as $slug => $data ) {
            error_log('Obenlo Activation: Checking page ' . $slug);
            $page_check = get_page_by_path( $slug );
            if ( ! isset( $page_check->ID ) ) {
                error_log('Obenlo Activation: Creating page ' . $slug);
                wp_insert_post( array(
                    'post_type'    => 'page',
                    'post_title'   => $data['title'],
                    'post_content' => $data['content'],
                    'post_status'  => 'publish',
                    'post_name'    => $slug
                ) );
            }
        }
        error_log('Obenlo Activation: Finished successfully.');
    } catch (Throwable $e) {
        error_log('Obenlo Activation FATAL ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        error_log('Obenlo Activation Trace: ' . $e->getTraceAsString());
    }
}
register_activation_hook( __FILE__, 'obenlo_booking_activate' );
