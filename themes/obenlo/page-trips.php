<?php
/**
 * Template Name: Trips (My Bookings)
 * The template for displaying guest bookings and confirmation codes.
 */

// Redirect all traffic to the new unified Account page
wp_redirect( home_url('/account?tab=trips'), 301 );
exit;

get_header();

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/login') );
    exit;
}

$user_id = get_current_user_id();

$bookings = get_posts( array(
    'post_type'      => 'booking',
    'author'         => $user_id,
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );
?>

<div class="obenlo-container" style="max-width:1100px; margin:60px auto; padding:0 20px;">

    <div style="margin-bottom:40px;">
        <h1 style="font-size:2.2rem; font-weight:800; color:#222; margin:0 0 8px 0;"><?php echo __('My Trips', 'obenlo'); ?></h1>
        <p style="color:#666; font-size:1rem; margin:0;"><?php echo __('Your booking history and confirmation codes.', 'obenlo'); ?></p>
    </div>

    <?php if ( empty( $bookings ) ) : ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:30px; border:1px solid #eee; margin-bottom: 40px;">
            <div style="font-size:4rem; margin-bottom:20px;">🏝️</div>
            <h2 style="margin-bottom:15px;"><?php echo __('No trips found', 'obenlo'); ?></h2>
            <p style="color:#666; margin-bottom:30px; font-size:1.1rem;"><?php echo __('You haven\'t booked any experiences or stays yet.', 'obenlo'); ?></p>
            <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background:var(--obenlo-primary); color:#fff; padding:14px 35px; border-radius:12px; text-decoration:none; font-weight:700; font-size:1rem;"><?php echo __('Explore Listings', 'obenlo'); ?></a>
        </div>
    <?php else : ?>
        <div style="display:flex; flex-direction:column; gap:20px;">
            <?php foreach ( $bookings as $booking ) :
                $listing_id   = get_post_meta( $booking->ID, '_obenlo_listing_id', true );
                $listing_title = $listing_id ? get_the_title( $listing_id ) : __('Unknown Listing', 'obenlo');
                $listing_url  = $listing_id ? get_permalink( $listing_id ) : '#';
                $start_date   = get_post_meta( $booking->ID, '_obenlo_start_date', true );
                $end_date     = get_post_meta( $booking->ID, '_obenlo_end_date', true );
                $guests       = get_post_meta( $booking->ID, '_obenlo_guests', true );
                $total        = get_post_meta( $booking->ID, '_obenlo_total_price', true );
                $status       = get_post_meta( $booking->ID, '_obenlo_booking_status', true );
                $conf_code    = get_post_meta( $booking->ID, '_obenlo_confirmation_code', true );
                $thumb_url    = $listing_id && has_post_thumbnail( $listing_id ) ? get_the_post_thumbnail_url( $listing_id, 'medium' ) : '';

                $status_color = '#3b82f6';
                $status_bg    = '#eff6ff';
                if ( in_array( $status, ['confirmed', 'approved', 'completed'] ) ) { $status_color = '#10b981'; $status_bg = '#ecfdf5'; }
                if ( in_array( $status, ['declined', 'cancelled'] ) )               { $status_color = '#ef4444'; $status_bg = '#fef2f2'; }
                if ( $status === 'pending_payment' )                                  { $status_color = '#f97316'; $status_bg = '#fff7ed'; }
            ?>
                <div style="background:#fff; border:1px solid #eee; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.03); display:flex; gap:0;">

                    <?php if ( $thumb_url ) : ?>
                        <div style="width:220px; flex-shrink:0; background:url('<?php echo esc_url($thumb_url); ?>') center/cover; min-height:160px;"></div>
                    <?php endif; ?>

                    <div style="padding:28px; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px;">
                            <div>
                                <div style="margin-bottom:6px;">
                                    <span style="background:<?php echo $status_bg; ?>; color:<?php echo $status_color; ?>; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; padding:4px 10px; border-radius:20px;"><?php echo esc_html__( ucwords( str_replace('_', ' ', $status) ), 'obenlo' ); ?></span>
                                </div>
                                <h3 style="margin:6px 0 4px 0; font-size:1.2rem; font-weight:800; color:#222;">
                                    <a href="<?php echo esc_url($listing_url); ?>" style="color:inherit; text-decoration:none;"><?php echo esc_html($listing_title); ?></a>
                                </h3>
                                <div style="color:#888; font-size:0.88rem; margin-top:4px;">
                                    <?php echo esc_html($start_date); ?>
                                    <?php echo $end_date ? ' &rarr; ' . esc_html($end_date) : ''; ?>
                                    <?php if ($guests) : ?>&nbsp;&bull;&nbsp;<?php printf(_n('%d guest', '%d guests', $guests, 'obenlo'), $guests); ?><?php endif; ?>
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:1.4rem; font-weight:800; color:#222;">$<?php echo number_format(floatval($total), 2); ?></div>
                                <div style="font-size:0.8rem; color:#888; margin-top:2px;"><?php echo __('Total paid', 'obenlo'); ?></div>
                            </div>
                        </div>

                        <?php if ( $conf_code ) : ?>
                            <div style="margin-top:20px; padding:16px 20px; background:linear-gradient(135deg,#fff9f0,#fff3f7); border:2px dashed #f0c0c0; border-radius:14px; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;">
                                <div style="flex:1;">
                                    <div style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--obenlo-primary); margin-bottom:4px;">🎫 <?php echo __('Booking Confirmation Code', 'obenlo'); ?></div>
                                    <div style="font-family:monospace; font-size:1.2rem; font-weight:800; color:#333;"><?php echo esc_html($conf_code); ?></div>
                                </div>
                                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($conf_code); ?>').then(()=>this.innerText='<?php echo esc_js(__('Copied!', 'obenlo')); ?>')" style="background:var(--obenlo-primary); color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:0.85rem; white-space:nowrap;"><?php echo __('Copy Code', 'obenlo'); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
