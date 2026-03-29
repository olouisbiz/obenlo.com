<?php
/**
 * Virtual Event Security Proxy - Obenlo
 * Protects raw virtual links from being shared with non-buyers.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Virtual_Security
{

    public function init()
    {
        add_action('admin_post_obenlo_join_event', array($this, 'handle_join_event'));
        add_action('admin_post_nopriv_obenlo_join_event', array($this, 'handle_join_event'));
    }

    /**
     * Handle the secure redirect to virtual events
     */
    public function handle_join_event()
    {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
        $guest_id_param = isset($_GET['guest_id']) ? sanitize_text_field($_GET['guest_id']) : '';

        if (!$booking_id) {
            obenlo_redirect_with_error('invalid_booking');
        }

        // 1. NONCE VALIDATION
        $is_valid_nonce = wp_verify_nonce($nonce, 'join_event_' . $booking_id);
        
        // 2. GUEST ID VALIDATION (Fallback for PWA/Mobile sessions)
        $is_valid_guest = false;
        $actual_guest_id = get_post_meta($booking_id, '_obenlo_guest_id', true);
        if ($guest_id_param && $actual_guest_id === $guest_id_param) {
            $is_valid_guest = true;
        }

        if (!$is_valid_nonce && !$is_valid_guest) {
            obenlo_redirect_with_error('security_failed');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            obenlo_redirect_with_error('invalid_booking');
        }

        // Verify status
        $status = get_post_meta($booking_id, '_obenlo_booking_status', true);
        if (!in_array($status, ['confirmed', 'approved', 'completed'])) {
            obenlo_redirect_with_error('invalid_booking');
        }

        // Authorization finalized (Already passed nonce or guest_id check)
        $is_authorized = true;

        $listing_id = get_post_meta($booking_id, '_obenlo_listing_id', true);
        $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);

        if (!$virtual_link) {
            obenlo_redirect_with_error('booking_error');
        }

        // Log the join event (optional)
        error_log("Obenlo: Guest joined virtual event for booking #$booking_id");

        // Secure Redirect
        wp_redirect(esc_url_raw($virtual_link));
        exit;
    }

    /**
     * Generate a secure join URL for a booking
     */
    public static function get_secure_join_url($booking_id)
    {
        $guest_id = get_post_meta($booking_id, '_obenlo_guest_id', true);
        $url = admin_url('admin-post.php?action=obenlo_join_event&booking_id=' . $booking_id);
        $url = wp_nonce_url($url, 'join_event_' . $booking_id);
        
        if ($guest_id) {
            $url = add_query_arg('guest_id', $guest_id, $url);
        }
        
        return $url;
    }
}
