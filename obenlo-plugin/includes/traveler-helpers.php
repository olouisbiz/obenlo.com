<?php
/**
 * Obenlo Traveler Helpers
 * Location: /obenlo-plugin/includes/traveler-helpers.php
 */

if (!defined('ABSPATH')) exit;

function get_obenlo_traveler_data($user_id) {
    // Retrieve favorites stored as an array of Host/Listing IDs
    $favorites = get_user_meta($user_id, 'obenlo_fav_hosts', true) ?: [];
    
    // Retrieve booking history from user meta
    $bookings = get_user_meta($user_id, 'obenlo_traveler_bookings', true) ?: [];

    return [
        'favorites' => $favorites,
        'bookings'  => array_reverse($bookings), // Newest first
    ];
}