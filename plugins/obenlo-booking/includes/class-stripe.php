<?php
/**
 * Stripe Payment Gateway Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Stripe {

    public function init() {
        // Stripe-specific hooks or initialization
    }

    public static function get_publishable_key() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_stripe_live_publishable_key', '' );
        }
        return get_option( 'obenlo_stripe_sandbox_publishable_key', '' );
    }

    public static function get_secret_key() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_stripe_live_secret_key', '' );
        }
        return get_option( 'obenlo_stripe_sandbox_secret_key', '' );
    }

    /**
     * placeholder for Stripe Checkout logic
     */
    public function create_checkout_session( $booking_id, $amount, $currency = 'USD' ) {
        $secret_key = self::get_secret_key();
        if ( empty( $secret_key ) ) {
            return new WP_Error( 'missing_key', 'Stripe Secret Key is not configured.' );
        }

        // Logic to interact with Stripe API via library or wp_remote_post
        // ...
        
        return true; 
    }
}
