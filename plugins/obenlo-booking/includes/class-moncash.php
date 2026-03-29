<?php
/**
 * MonCash (Digicel Haiti) Payment Gateway Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_MonCash {

    public function init() {
        // Initialization if needed
    }

    public static function get_client_id() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_moncash_live_client_id', '' );
        }
        return get_option( 'obenlo_moncash_sandbox_client_id', '' );
    }

    public static function get_secret() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        if ( $mode === 'live' ) {
            return get_option( 'obenlo_moncash_live_secret', '' );
        }
        return get_option( 'obenlo_moncash_sandbox_secret', '' );
    }

    public static function get_api_url() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        return ( $mode === 'live' ) ? 'https://moncashdfs.moncash.com' : 'https://sandbox.moncashdfs.moncash.com';
    }

    public static function get_redirect_base() {
        $mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        return ( $mode === 'live' ) ? 'https://moncashdfs.moncash.com/Moncash-middleware' : 'https://sandbox.moncashdfs.moncash.com/Moncash-middleware';
    }

    private static function get_access_token() {
        $client_id = self::get_client_id();
        $secret = self::get_secret();

        if ( empty( $client_id ) || empty( $secret ) ) {
            return new WP_Error( 'missing_keys', 'MonCash API Keys are not configured.' );
        }

        $response = wp_remote_post( self::get_api_url() . '/Api/oauth/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'client_credentials',
            ),
            'sslverify' => false, // Set to true in production if possible
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return isset( $body['access_token'] ) ? $body['access_token'] : new WP_Error( 'auth_failed', 'Failed to get access token.' );
    }

    /**
     * Create a MonCash Payment Order
     * Returns the redirect URL
     */
    public function create_payment( $booking_id, $amount_htg ) {
        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) return $token;

        $response = wp_remote_post( self::get_api_url() . '/Api/v1/CreatePayment', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'amount' => round($amount_htg),
                'orderId' => 'OB-' . $booking_id . '-' . time()
            ) ),
            'sslverify' => false,
        ) );

        if ( is_wp_error( $response ) ) return $response;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['payment_token']['token'] ) ) {
            $payment_token = $body['payment_token']['token'];
            return self::get_redirect_base() . '/Payment/Redirect?token=' . $payment_token;
        }

        return new WP_Error( 'payment_failed', 'Failed to create MonCash payment.' );
    }

    /**
     * Verify a MonCash payment via Transaction ID or Order ID
     */
    public function verify_transaction( $transaction_id ) {
        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) return false;

        $response = wp_remote_post( self::get_api_url() . '/Api/v1/RetrieveTransactionPayment', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'transactionId' => $transaction_id
            ) ),
            'sslverify' => false,
        ) );

        if ( is_wp_error( $response ) ) return false;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        // Check if payment was successful
        if ( isset( $body['payment']['status'] ) && strtolower($body['payment']['status']) === 'successful' ) {
            return true;
        }

        return false;
    }

    /**
     * Disburse funds to a phone number (B2P)
     * Requires specific MonCash B2P permissions
     */
    public function disburse_funds( $receiver_phone, $amount_htg ) {
        $token = self::get_access_token();
        if ( is_wp_error( $token ) ) return $token;

        $response = wp_remote_post( self::get_api_url() . '/Api/v1/Disburse', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'amount' => round($amount_htg),
                'receiver' => $receiver_phone,
                'description' => 'Obenlo Host Payout'
            ) ),
            'sslverify' => false,
        ) );

        if ( is_wp_error( $response ) ) return $response;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['transaction_id'] ) ) {
            return $body['transaction_id'];
        }

        $error_msg = isset($body['message']) ? $body['message'] : 'Disbursement failed.';
        return new WP_Error( 'disbursement_failed', $error_msg );
    }
}
