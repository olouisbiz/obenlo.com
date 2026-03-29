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

    public static function get_api_url() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        return ( $mode === 'live' ) ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private static function get_access_token() {
        $client_id = self::get_client_id();
        $secret = self::get_secret();

        if ( empty( $client_id ) || empty( $secret ) ) {
            return new WP_Error( 'missing_keys', 'PayPal API Keys are not configured.' );
        }

        $response = wp_remote_post( self::get_api_url() . '/v1/oauth2/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'client_credentials',
            ),
            'sslverify' => ( wp_get_environment_type() !== 'local' ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Obenlo PayPal Token Error: ' . $response->get_error_message() );
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! isset( $body['access_token'] ) ) {
            error_log( 'Obenlo PayPal Token Failed Body: ' . wp_remote_retrieve_body( $response ) );
            return new WP_Error( 'auth_failed', 'Failed to get PayPal access token.' );
        }

        return $body['access_token'];
    }

    /**
     * Create a PayPal Order
     */
    public function create_order( $booking_id, $amount, $currency = 'USD' ) {
        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) return $token;

        $response = wp_remote_post( self::get_api_url() . '/v2/checkout/orders', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'intent' => 'CAPTURE',
                'purchase_units' => array(
                    array(
                        'reference_id' => 'booking_' . $booking_id,
                        'amount' => array(
                            'currency_code' => $currency,
                            'value' => number_format( (float)$amount, 2, '.', '' ),
                        ),
                        'description' => 'Obenlo Booking #' . $booking_id,
                    ),
                ),
                'application_context' => array(
                    'return_url' => add_query_arg( 'obenlo_paypal_return', $booking_id, home_url( '/' ) ),
                    'cancel_url' => add_query_arg( 'obenlo_payment_cancel', '1', get_permalink( get_post_meta( $booking_id, '_obenlo_listing_id', true ) ) ),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ),
            ) ),
            'sslverify' => ( wp_get_environment_type() !== 'local' ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Obenlo PayPal Order API Error: ' . $response->get_error_message() );
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! isset( $body['links'] ) ) {
            $full_body = wp_remote_retrieve_body( $response );
            error_log( 'Obenlo PayPal Order Failed Body: ' . $full_body );
            $error_msg = isset($body['message']) ? $body['message'] : 'Failed to create PayPal Order.';
            return new WP_Error( 'order_failed', $error_msg );
        }

        foreach ( $body['links'] as $link ) {
            if ( $link['rel'] === 'approve' ) {
                return $link['href'];
            }
        }

        return new WP_Error( 'order_failed', 'Failed to create PayPal Order.' );
    }

    /**
     * Capture a PayPal Order
     */
    public function capture_order( $order_id ) {
        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) return $token;

        $response = wp_remote_post( self::get_api_url() . "/v2/checkout/orders/{$order_id}/capture", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Obenlo PayPal Capture Error: ' . $response->get_error_message() );
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['status'] ) && $body['status'] === 'COMPLETED' ) {
            return true;
        }

        error_log( 'Obenlo PayPal Capture Failed Body: ' . wp_remote_retrieve_body( $response ) );
        return false;
    }
}
