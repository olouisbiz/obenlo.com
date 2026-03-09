<?php
/**
 * Host Badge System - Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Badges {

    /**
     * Get all badges for a host
     * 
     * @param int $user_id
     * @return array
     */
    public static function get_host_badges( $user_id ) {
        $badges = array();

        // 1. Verified Host
        if ( Obenlo_Booking_Host_Verification::get_status( $user_id ) === 'verified' ) {
            $badges[] = array(
                'id'    => 'verified',
                'label' => 'Verified Host',
                'icon'  => '🛡️',
                'color' => '#1d9bf0', // Twitter Blue
                'desc'  => 'Identity verified by Obenlo'
            );
        }

        // 2. Most Booking (Top Booked)
        if ( self::is_top_booked( $user_id ) ) {
            $badges[] = array(
                'id'    => 'top_booked',
                'label' => 'Top Booked',
                'icon'  => '🏆',
                'color' => '#e61e4d',
                'desc'  => 'Among the most popular hosts on Obenlo'
            );
        }

        // 3. Most Good Review (Highly Rated)
        if ( self::is_highly_rated( $user_id ) ) {
            $badges[] = array(
                'id'    => 'highly_rated',
                'label' => 'Highly Rated',
                'icon'  => '✨',
                'color' => '#ffb400',
                'desc'  => 'Consistently high ratings from guests'
            );
        }

        // 4. Super Host (Example of another badge)
        if ( self::is_super_host( $user_id ) ) {
            $badges[] = array(
                'id'    => 'super_host',
                'label' => 'Superhost',
                'icon'  => '🏅',
                'color' => '#ff385c',
                'desc'  => 'Experienced host with outstanding hospitality'
            );
        }

        return $badges;
    }

    /**
     * Top Booked Logic
     */
    private static function is_top_booked( $user_id ) {
        $bookings = get_posts( array(
            'post_type'  => 'booking',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'   => '_obenlo_host_id',
                    'value' => $user_id,
                ),
                array(
                    'key'   => '_obenlo_booking_status',
                    'value' => 'completed',
                ),
            ),
            'posts_per_page' => -1,
            'fields'         => 'ids'
        ) );

        return count( $bookings ) >= 5; // Badge for 5+ completed bookings
    }

    /**
     * Highly Rated Logic
     */
    private static function is_highly_rated( $user_id ) {
        if ( ! class_exists( 'Obenlo_Booking_Reviews' ) ) {
            return false;
        }

        $avg = Obenlo_Booking_Reviews::get_host_average_rating( $user_id );
        $count = Obenlo_Booking_Reviews::get_host_review_count( $user_id );

        return ( floatval( $avg ) >= 4.8 && $count >= 3 );
    }

    /**
     * Super Host Logic
     */
    private static function is_super_host( $user_id ) {
        // Combined logic: Verified + Top Booked + Highly Rated
        return ( Obenlo_Booking_Host_Verification::get_status( $user_id ) === 'verified' && 
                 self::is_top_booked( $user_id ) && 
                 self::is_highly_rated( $user_id ) );
    }
}
