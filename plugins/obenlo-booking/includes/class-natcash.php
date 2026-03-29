<?php
/**
 * Natcash (Natcom Haiti) Payment Gateway Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Natcash {

    public function init() {
        // Initialization if needed
    }

    public static function get_api_key() {
        return get_option( 'obenlo_natcash_api_key', '' );
    }

    public static function get_merchant_id() {
        return get_option( 'obenlo_natcash_merchant_id', '' );
    }

    /**
     * Create a Natcash Payment Request
     * Note: This is a placeholder since Natcash documentation is often guarded by NDAs.
     */
    public function create_payment( $booking_id, $amount_htg ) {
        // Placeholder for Natcash integration logic
        // This usually involves a POST to Natcom's gateway with a signature.
        
        return new WP_Error( 'not_configured', 'Natcash integration is pending official API documentation from Natcom.' );
    }

    /**
     * Verify a Natcash payment success
     */
    public function verify_transaction( $transaction_id ) {
        // Placeholder for verification logic
        return false;
    }
}
