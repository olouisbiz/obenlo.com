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

        // 4. Fast Responder
        if ( self::is_fast_responder( $user_id ) ) {
            $badges[] = array(
                'id'    => 'fast_responder',
                'label' => 'Fast Responder',
                'icon'  => '⚡',
                'color' => '#3b82f6',
                'desc'  => 'Typically responds within an hour'
            );
        }

        // 5. Super Host
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
     * Render badges as HTML
     * 
     * @param int $user_id
     * @param string $context 'storefront', 'directory', or 'mini'
     * @return string
     */
    public static function render_badges_html($user_id, $context = 'storefront') {
        $badges = self::get_host_badges($user_id);
        if (empty($badges)) {
            // Default "New Host" badge if no others
            return '<div class="obenlo-badges-container" style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:10px;">
                        <span style="background:#f3f4f6; color:#6b7280; padding:6px 15px; border-radius:50px; font-weight:800; font-size:0.75rem;">NEW HOST</span>
                    </div>';
        }

        $html = '<div class="obenlo-badges-container" style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:10px;">';
        
        foreach ($badges as $badge) {
            if ($context === 'storefront') {
                // Premium full badges for the storefront glass card
                $bg_color = $badge['id'] === 'verified' ? '#fef2f2' : ($badge['id'] === 'highly_rated' ? '#fff7ed' : ($badge['id'] === 'super_host' ? '#fff1f2' : '#f0fdf4'));
                
                $html .= sprintf(
                    '<span class="obenlo-badge-item" title="%s" style="background:%s; color:%s; padding:6px 15px; border-radius:50px; font-weight:800; font-size:0.75rem; display:flex; align-items:center; gap:6px; border:1px solid rgba(0,0,0,0.03);">%s %s</span>',
                    esc_attr($badge['desc']),
                    esc_attr($bg_color),
                    esc_attr($badge['color']),
                    $badge['icon'],
                    esc_html(strtoupper($badge['label']))
                );
            } elseif ($context === 'directory') {
                // Compact style for cards in the directory
                $html .= sprintf(
                    '<span class="host-stat" title="%s" style="color:%s; font-weight:700; font-size:0.8rem; display:inline-flex; align-items:center; gap:4px;">%s %s</span>',
                    esc_attr($badge['desc']),
                    esc_attr($badge['color']),
                    $badge['icon'],
                    esc_html(strtoupper($badge['label']))
                );
            }
        }
        
        $html .= '</div>';
        return $html;
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
     * Fast Responder Logic
     */
    private static function is_fast_responder( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_chat_messages';
        
        // Simple check: has the host sent at least 3 messages?
        // This is a placeholder for more complex "avg response time" logic
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE sender_id = %d", $user_id ) );
        
        return $count >= 3;
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
