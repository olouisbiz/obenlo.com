<?php
/**
 * Obenlo Database Helpers
 * Location: /obenlo-plugin/includes/database.php
 */

if (!defined('ABSPATH')) exit;

function get_obenlo_host_stats($host_id) {
    $bookings = get_user_meta($host_id, 'obenlo_studio_bookings', true) ?: [];
    $total_revenue = 0;
    
    foreach ($bookings as $booking) {
        if ($booking['status'] === 'confirmed') {
            $total_revenue += $booking['amount'];
        }
    }

    return [
        'total_revenue' => $total_revenue,
        'booking_count' => count($bookings),
        'recent_bookings' => array_slice($bookings, 0, 5)
    ];
}