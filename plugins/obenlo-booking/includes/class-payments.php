<?php
/**
 * Payment and Booking Handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Payments {

    public function init() {
        // Handle frontend booking submissions
        add_action( 'admin_post_nopriv_obenlo_submit_booking', array( $this, 'handle_booking_submission' ) );
        add_action( 'admin_post_obenlo_submit_booking', array( $this, 'handle_booking_submission' ) );
    }

    public function handle_booking_submission() {
        if ( ! isset( $_POST['obenlo_booking_nonce'] ) || ! wp_verify_nonce( $_POST['obenlo_booking_nonce'], 'obenlo_submit_booking_action' ) ) {
            wp_die( 'Security check failed' );
        }

        $listing_id = isset( $_POST['listing_id'] ) ? intval( $_POST['listing_id'] ) : 0;
        $start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
        $end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
        $guests     = isset( $_POST['guests'] ) ? intval( $_POST['guests'] ) : 1;
        $payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : 'stripe';

        if ( ! $listing_id || ! $start_date ) {
            wp_die( 'Missing required fields.' );
        }

        $listing = get_post( $listing_id );
        if ( ! $listing || $listing->post_type !== 'listing' ) {
            wp_die( 'Invalid listing.' );
        }

        // Calculate Price
        $price_per_unit = get_post_meta( $listing_id, '_obenlo_price', true );
        $total_price = floatval( $price_per_unit ); // TODO: Calculate properly based on days/hours

        // Create Booking Post (Pending)
        $booking_data = array(
            'post_title'   => 'Booking for ' . $listing->post_title . ' - ' . current_time('mysql'),
            'post_status'  => 'publish', // Or 'draft' pending payment
            'post_type'    => 'booking',
            'post_author'  => is_user_logged_in() ? get_current_user_id() : 0, 
        );

        $booking_id = wp_insert_post( $booking_data );

        if ( is_wp_error( $booking_id ) ) {
            wp_die( 'Error creating booking.' );
        }

        // Generate a unique confirmation code
        $confirmation_code = 'OB-' . strtoupper( substr( md5( uniqid( $booking_id, true ) ), 0, 4 ) ) . '-' . strtoupper( substr( md5( uniqid( $listing_id, true ) ), 0, 4 ) );

        // Save Meta
        update_post_meta( $booking_id, '_obenlo_listing_id', $listing_id );
        update_post_meta( $booking_id, '_obenlo_host_id', $listing->post_author );
        update_post_meta( $booking_id, '_obenlo_start_date', $start_date );
        update_post_meta( $booking_id, '_obenlo_end_date', $end_date );
        update_post_meta( $booking_id, '_obenlo_guests', $guests );
        update_post_meta( $booking_id, '_obenlo_total_price', $total_price );
        update_post_meta( $booking_id, '_obenlo_payment_method', $payment_method );
        update_post_meta( $booking_id, '_obenlo_booking_status', 'pending_payment' );
        update_post_meta( $booking_id, '_obenlo_confirmation_code', $confirmation_code );

        // Notify for new booking request
        Obenlo_Booking_Notifications::notify_booking_event( $booking_id, 'new_booking' );

        // Redirect to appropriate payment gateway
        if ( $payment_method === 'stripe' ) {
            $this->process_stripe_checkout( $booking_id, $total_price, $listing->post_title );
        } elseif ( $payment_method === 'paypal' ) {
             $this->process_paypal_checkout( $booking_id, $total_price, $listing->post_title );
        } else {
            wp_die( 'Invalid payment method' );
        }
    }

    private function process_stripe_checkout( $booking_id, $amount, $item_name ) {
        $stripe_secret = Obenlo_Booking_Stripe::get_secret_key();

        if ( empty($stripe_secret) ) {
            error_log('Obenlo: Stripe Secret Key missing in settings. Falling back to simulation.');
        }

        // In a real implementation, we would call $stripe_service->create_checkout_session(...) here.
        // For now, we continue to simulate success but using the centralized configuration.

        update_post_meta( $booking_id, '_obenlo_booking_status', 'confirmed' );
        Obenlo_Booking_Notifications::notify_booking_event( $booking_id, 'booking_confirmed' );
        wp_safe_redirect( add_query_arg( 'obenlo_modal', 'booking_confirmed', home_url() ) );
        exit;
    }

    private function process_paypal_checkout( $booking_id, $amount, $item_name ) {
        $paypal_id = Obenlo_Booking_PayPal::get_client_id();
        
        if ( empty($paypal_id) ) {
            error_log('Obenlo: PayPal Client ID missing. Falling back to simulation.');
        }

        // Simulate success redirect
        update_post_meta( $booking_id, '_obenlo_booking_status', 'confirmed' );
        Obenlo_Booking_Notifications::notify_booking_event( $booking_id, 'booking_confirmed' );
        wp_safe_redirect( add_query_arg( 'obenlo_modal', 'booking_confirmed', home_url() ) );
        exit;
    }
    public function calculate_platform_fee( $booking_id ) {
        $host_id = get_post_meta( $booking_id, '_obenlo_host_id', true );
        $total_price = floatval( get_post_meta( $booking_id, '_obenlo_total_price', true ) );
        
        // 1. Check for host-specific override
        $fee_percentage = get_user_meta( $host_id, '_obenlo_host_fee_percentage', true );
        
        // 2. Fallback to global setting if no override
        if ( $fee_percentage === '' || $fee_percentage === false ) {
            $fee_percentage = get_option( 'obenlo_global_platform_fee', '10' );
        }
        
        $fee_percentage = floatval( $fee_percentage );
        $fee_amount = ( $total_price * $fee_percentage ) / 100;
        
        // Save to booking meta
        update_post_meta( $booking_id, '_obenlo_platform_fee_percentage', $fee_percentage );
        update_post_meta( $booking_id, '_obenlo_platform_fee_amount', $fee_amount );
        
        return $fee_amount;
    }
}
