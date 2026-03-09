<?php
/**
 * Wishlist Logic - Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Wishlist {

    public function init() {
        // AJAX handlers for wishlist toggle
        add_action( 'wp_ajax_obenlo_toggle_wishlist', array( $this, 'toggle_wishlist' ) );
        add_action( 'wp_ajax_nopriv_obenlo_toggle_wishlist', array( $this, 'toggle_wishlist' ) );
    }

    /**
     * AJAX: Toggle listing in user wishlist
     */
    public function toggle_wishlist() {
        check_ajax_referer( 'obenlo_wishlist_nonce', 'nonce' );

        $listing_id = isset( $_POST['listing_id'] ) ? intval( $_POST['listing_id'] ) : 0;
        if ( ! $listing_id ) {
            wp_send_json_error( array( 'message' => 'Invalid listing ID' ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Please log in to save items', 'require_login' => true ) );
        }

        $user_id = get_current_user_id();
        $wishlist = get_user_meta( $user_id, '_obenlo_wishlist', true );
        
        if ( ! is_array( $wishlist ) ) {
            $wishlist = array();
        }

        if ( ( $key = array_search( $listing_id, $wishlist ) ) !== false ) {
            // Remove from wishlist
            unset( $wishlist[$key] );
            $added = false;
        } else {
            // Add to wishlist
            $wishlist[] = $listing_id;
            $added = true;
        }

        // Keep values unique and reset keys
        $wishlist = array_values( array_unique( $wishlist ) );
        update_user_meta( $user_id, '_obenlo_wishlist', $wishlist );

        wp_send_json_success( array( 
            'added' => $added, 
            'count' => count( $wishlist ),
            'message' => $added ? 'Saved to wishlists' : 'Removed from wishlists'
        ) );
    }

    /**
     * Helper: Check if listing is in user's wishlist
     */
    public static function is_in_wishlist( $listing_id, $user_id = null ) {
        if ( ! $user_id ) $user_id = get_current_user_id();
        if ( ! $user_id ) return false;

        $wishlist = get_user_meta( $user_id, '_obenlo_wishlist', true );
        return is_array( $wishlist ) && in_array( $listing_id, $wishlist );
    }

    /**
     * Helper: Get user's wishlist IDs
     */
    public static function get_user_wishlist( $user_id = null ) {
        if ( ! $user_id ) $user_id = get_current_user_id();
        if ( ! $user_id ) return array();

        $wishlist = get_user_meta( $user_id, '_obenlo_wishlist', true );
        return is_array( $wishlist ) ? $wishlist : array();
    }
}
