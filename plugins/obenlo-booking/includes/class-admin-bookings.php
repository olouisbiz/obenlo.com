<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Bookings
{
    public function init()
    {
        // Add booking hooks if any (currently none required for admin_post)
    }

    public function render_bookings_tab()
    {
        $bookings = get_posts(array('post_type' => 'booking', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC'));

        echo '<h3>All Platform Bookings</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>ID</th><th>Listing</th><th>Total</th><th>Status</th><th>Mode</th><th>Date</th></tr>';
        foreach ($bookings as $booking) {
            $listing_id = get_post_meta($booking->ID, '_obenlo_listing_id', true);
            $total = get_post_meta($booking->ID, '_obenlo_total_price', true);
            $status = get_post_meta($booking->ID, '_obenlo_booking_status', true);
            $mode = get_post_meta($booking->ID, '_obenlo_payment_mode', true) ?: 'legacy';
            $mode_color = ($mode === 'live') ? '#e61e4d' : '#666';
            $mode_label = ($mode === 'live') ? 'LIVE' : 'TEST';
            
            $status_bg = '#f3f4f6'; $status_color = '#374151';
            if ($status === 'confirmed') { $status_bg = '#dcfce7'; $status_color = '#166534'; }
            if ($status === 'pending_payment') { $status_bg = '#fef9c3'; $status_color = '#854d0e'; }
            if ($status === 'refunded') { $status_bg = '#fee2e2'; $status_color = '#991b1b'; }
            if ($status === 'cancelled') { $status_bg = '#f3f4f6'; $status_color = '#6b7280'; }
            
            echo '<tr>';
            echo '<td data-label="ID">#' . $booking->ID . '</td>';
            echo '<td data-label="Listing">' . get_the_title($listing_id) . '</td>';
            echo '<td data-label="Total">$' . number_format(floatval($total), 2) . '</td>';
            echo '<td data-label="Status"><span style="background:'.$status_bg.'; color:'.$status_color.'; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">' . str_replace('_', ' ', esc_html($status)) . '</span></td>';
            echo '<td data-label="Mode"><span class="badge" style="background:' . $mode_color . '; color:#fff;">' . $mode_label . '</span></td>';
            echo '<td data-label="Date">' . get_the_date('', $booking->ID) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

}
