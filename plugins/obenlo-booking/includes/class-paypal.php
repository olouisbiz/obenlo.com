<?php
/**
 * PayPal Payment Gateway Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_PayPal {

    public function init() {
        // PayPal-specific hooks or initialization
    }

    public static function get_client_id() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_paypal_live_client_id', '' );
        }
        return get_option( 'obenlo_paypal_sandbox_client_id', '' );
    }

    public static function get_secret() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_paypal_live_secret', '' );
        }
        return get_option( 'obenlo_paypal_sandbox_secret', '' );
    }

    /**
     * placeholder for PayPal logic
     */
    public function process_payment( $booking_id, $amount, $currency = 'USD' ) {
        $client_id = self::get_client_id();
        if ( empty( $client_id ) ) {
            return new WP_Error( 'missing_key', 'PayPal Client ID is not configured.' );
        }

        // Logic to interact with PayPal API
        // ...

        return true;
    }
}
