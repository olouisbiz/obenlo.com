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
     * Create a Stripe Checkout Session
     */
    public function create_checkout_session( $booking_id, $amount, $currency = 'usd' ) {
        $secret_key = self::get_secret_key();
        if ( empty( $secret_key ) ) {
            return new WP_Error( 'missing_key', 'Stripe Secret Key is not configured.' );
        }

        $listing_id = get_post_meta( $booking_id, '_obenlo_listing_id', true );
        $listing_title = get_the_title( $listing_id );

        $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $secret_key . ':' ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body' => http_build_query( array(
                'payment_method_types' => array( 'card' ),
                'line_items' => array(
                    array(
                        'price_data' => array(
                            'currency' => strtolower( $currency ),
                            'product_data' => array(
                                'name' => $listing_title,
                                'description' => 'Booking #' . $booking_id . ' on Obenlo',
                            ),
                            'unit_amount' => round( (float)$amount * 100 ), // Stripe expects cents
                        ),
                        'quantity' => 1,
                    ),
                ),
                'mode' => 'payment',
                'success_url' => str_replace( '%7BCHECKOUT_SESSION_ID%7D', '{CHECKOUT_SESSION_ID}', add_query_arg( array( 'obenlo_stripe_success' => $booking_id, 'session_id' => '{CHECKOUT_SESSION_ID}' ), home_url( '/' ) ) ),
                'cancel_url' => add_query_arg( 'obenlo_payment_cancel', '1', get_permalink( $listing_id ) ),
                'client_reference_id' => $booking_id,
                'metadata' => array(
                    'booking_id' => $booking_id,
                ),
            ) ),
            'sslverify' => ( wp_get_environment_type() !== 'local' ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Obenlo Stripe API Error: ' . $response->get_error_message() );
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['url'] ) ) {
            return $body['url'];
        }

        error_log( 'Obenlo Stripe Session Failed Body: ' . wp_remote_retrieve_body( $response ) );
        $error_msg = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Failed to create Stripe Checkout Session. Check your keys and server connectivity.';
        return new WP_Error( 'stripe_error', $error_msg );
    }

    /**
     * Verify a Stripe Checkout Session
     */
    public function verify_checkout_session( $session_id ) {
        $secret_key = self::get_secret_key();
        if ( empty( $secret_key ) ) return false;

        $response = wp_remote_get( "https://api.stripe.com/v1/checkout/sessions/{$session_id}", array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $secret_key . ':' ),
            ),
            'sslverify' => ( wp_get_environment_type() !== 'local' ),
        ) );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return ( isset( $body['payment_status'] ) && $body['payment_status'] === 'paid' );
    }
}
