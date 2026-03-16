<?php
/**
 * Host Payout Management - Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Payout_Manager {

    public function init() {
        add_action( 'wp_ajax_obenlo_save_payout_settings', array( $this, 'save_payout_settings' ) );
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
            'moncash'  => array( 'label' => 'MonCash (Haiti)', 'field' => 'tel', 'placeholder' => '+509 XXXX XXXX' ),
            'natcash'  => array( 'label' => 'Natcash (Haiti)', 'field' => 'tel', 'placeholder' => '+509 XXXX XXXX' ),
        );
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
}
