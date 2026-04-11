<?php
/**
 * Host Payout Management - Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Payout_Manager {

    public function init() {
        add_action( 'init', array( $this, 'register_payout_cpt' ) );
        add_action( 'wp_ajax_obenlo_save_payout_settings', array( $this, 'save_payout_settings' ) );
        add_action( 'wp_ajax_obenlo_request_payout', array( $this, 'handle_request_payout' ) );
        
        // Admin actions
        add_action( 'admin_post_obenlo_process_payout', array( $this, 'handle_admin_process_payout' ) );
    }

    /**
     * Register a hidden CPT for payout requests
     */
    public function register_payout_cpt() {
        register_post_type( 'obenlo_payout_req', array(
            'labels' => array(
                'name' => 'Payout Requests',
                'singular_name' => 'Payout Request',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array( 'title', 'custom-fields' ),
            'has_archive' => false,
        ) );
    }

    /**
     * Available payout methods
     */
    public static function get_methods() {
        return array(
            'paypal'   => array( 'label' => 'PayPal', 'field' => 'email', 'placeholder' => 'paypal@example.com' ),
            'stripe'   => array( 'label' => 'Stripe', 'field' => 'email', 'placeholder' => 'stripe-account@email.com' ),
            'cashapp'  => array( 'label' => 'CashApp', 'field' => 'text', 'placeholder' => '$Cashtag' ),
            'venmo'    => array( 'label' => 'Venmo', 'field' => 'text', 'placeholder' => '@username' ),
            'zelle'    => array( 'label' => 'Zelle', 'field' => 'text', 'placeholder' => 'Phone or Email' ),
        );
    }

    /**
     * Get host current balance
     */
    public static function get_host_balance( $user_id ) {
        return floatval( get_user_meta( $user_id, '_obenlo_host_balance', true ) );
    }

    /**
     * Save payout settings via AJAX
     */
    public function save_payout_settings() {
        check_ajax_referer( 'obenlo_payout_nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
        }

        $user_id = get_current_user_id();
        $method = isset( $_POST['payout_method'] ) ? sanitize_text_field( $_POST['payout_method'] ) : '';
        $details = isset( $_POST['payout_details'] ) ? sanitize_text_field( $_POST['payout_details'] ) : '';

        $valid_methods = array_keys( self::get_methods() );

        if ( in_array( $method, $valid_methods ) && ! empty( $details ) ) {
            update_user_meta( $user_id, 'obenlo_payout_method', $method );
            update_user_meta( $user_id, 'obenlo_payout_details', $details );
            
            // Notify Admin
            $user = get_userdata($user_id);
            Obenlo_Booking_Notifications::send_to_admin(
                "Payout Settings Updated: " . $user->display_name,
                "Host " . $user->display_name . " has updated their payout preferences to " . strtoupper($method) . ".\nHost Email: " . $user->user_email . "\nView in Admin Dashboard: " . home_url('/support-console/?tab=users')
            );

            wp_send_json_success( array( 'message' => 'Payout preferences saved successfully.' ) );
        }

        wp_send_json_error( 'Invalid payout settings.' );
    }

    /**
     * Handle host requesting a payout
     */
    public function handle_request_payout() {
        check_ajax_referer( 'obenlo_payout_nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Session expired.' );
        }

        $user_id = get_current_user_id();
        $balance = self::get_host_balance( $user_id );
        $min_payout = 20.00;

        if ( $balance < $min_payout ) {
            wp_send_json_error( 'Minimum payout is $' . number_format($min_payout, 2) . '. Your balance is $' . number_format($balance, 2) );
        }

        // Check for existing pending request
        $existing = get_posts(array(
            'post_type' => 'obenlo_payout_req',
            'author' => $user_id,
            'meta_key' => '_status',
            'meta_value' => 'pending',
            'posts_per_page' => 1
        ));

        if (!empty($existing)) {
            wp_send_json_error( 'You already have a pending payout request.' );
        }

        $method = get_user_meta($user_id, 'obenlo_payout_method', true);
        $details = get_user_meta($user_id, 'obenlo_payout_details', true);

        if (empty($method) || empty($details)) {
            wp_send_json_error( 'Please save your payout preferences first.' );
        }

        $payout_id = wp_insert_post(array(
            'post_type' => 'obenlo_payout_req',
            'post_status' => 'publish',
            'post_title' => 'Payout Request - ' . get_userdata($user_id)->display_name . ' (' . date('Y-m-d') . ')',
            'post_author' => $user_id
        ));

        if ($payout_id) {
            update_post_meta($payout_id, '_amount', $balance);
            update_post_meta($payout_id, '_method', $method);
            update_post_meta($payout_id, '_details', $details);
            update_post_meta($payout_id, '_status', 'pending');

            // Notify Admin
            $user = get_userdata($user_id);
            Obenlo_Booking_Notifications::send_to_admin(
                "💰 New Payout Request: " . $user->display_name,
                "A new payout request for $" . number_format($balance, 2) . " has been submitted.\nMethod: " . strtoupper($method) . "\nDetails: $details\n\nPlease process this in the Admin Payments Tab."
            );

            wp_send_json_success( array( 'message' => 'Payout request submitted successfully.' ) );
        }

        wp_send_json_error( 'Failed to create request.' );
    }

    /**
     * Admin process payout
     */
    public function handle_admin_process_payout() {
        if (!current_user_can('administrator')) {
            wp_die('Unauthorized');
        }

        $payout_id = isset($_POST['payout_id']) ? intval($_POST['payout_id']) : 0;
        check_admin_referer('process_payout_' . $payout_id, 'security');

        $status = sanitize_text_field($_POST['payout_status']);
        $payout_action = isset($_POST['payout_action']) ? sanitize_text_field($_POST['payout_action']) : '';
        $tx_id = sanitize_text_field($_POST['transaction_id']);
        $host_id = get_post_field('post_author', $payout_id);
        $amount_usd = get_post_meta($payout_id, '_amount', true);
        $method = get_post_meta($payout_id, '_method', true);

        if ($status === 'paid') {
            update_post_meta($payout_id, '_status', 'paid');
            update_post_meta($payout_id, '_transaction_id', $tx_id);
            update_post_meta($payout_id, '_paid_date', current_time('mysql'));

            // Deduct from host balance
            $current_bal = self::get_host_balance($host_id);
            update_user_meta($host_id, '_obenlo_host_balance', max(0, $current_bal - $amount_usd));

            // Notify Host
            Obenlo_Booking_Notifications::send_to_user(
                $host_id,
                "Your payout has been sent! 💰",
                "Hi there! Your payout request for $" . number_format($amount_usd, 2) . " has been processed and sent via " . strtoupper($method) . ".\n\nTransaction ID: " . ($tx_id ?: 'N/A') . "\n\nThank you for hosting on Obenlo!"
            );
        } elseif ($status === 'cancelled') {
            update_post_meta($payout_id, '_status', 'cancelled');
        }

        wp_safe_redirect(add_query_arg('tab', 'payments', wp_get_referer()));
        exit;
    }
}
