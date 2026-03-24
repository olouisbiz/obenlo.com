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

        if (!$booking_id || !wp_verify_nonce($nonce, 'join_event_' . $booking_id)) {
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

        // Verify ownership
        $current_user_id = get_current_user_id();
        $is_owner = ($current_user_id > 0 && $booking->post_author == $current_user_id);
        
        // If not logged in, we check if they have the guest ID from the query param (for visitors)
        if (!$is_owner) {
            $guest_id_param = isset($_GET['guest_id']) ? sanitize_text_field($_GET['guest_id']) : '';
            $actual_guest_id = get_post_meta($booking_id, '_obenlo_guest_id', true);
            
            if ($guest_id_param && $actual_guest_id === $guest_id_param) {
                $is_owner = true;
            }
        }

        if (!$is_owner && !current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

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
