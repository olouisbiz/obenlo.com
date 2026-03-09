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
        <h1 style="font-size:2.2rem; font-weight:800; color:#222; margin:0 0 8px 0;">My Trips</h1>
        <p style="color:#666; font-size:1rem; margin:0;">Your booking history and confirmation codes.</p>
    </div>

    <?php if ( empty( $bookings ) ) : ?>
        <div style="text-align:center; padding:80px 40px; background:#fff; border:1px solid #eee; border-radius:20px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="width:64px; height:64px; margin:0 auto 20px; display:block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <h3 style="color:#555; font-size:1.3rem; margin:0 0 10px 0;">No trips yet</h3>
            <p style="color:#888; margin:0 0 30px 0;">Explore listings and make your first booking!</p>
            <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background:#e61e4d; color:#fff; padding:14px 35px; border-radius:12px; text-decoration:none; font-weight:700; font-size:1rem;">Explore Listings</a>
        </div>
    <?php else : ?>
        <div style="display:flex; flex-direction:column; gap:20px;">
            <?php foreach ( $bookings as $booking ) :
                $listing_id   = get_post_meta( $booking->ID, '_obenlo_listing_id', true );
                $listing_title = $listing_id ? get_the_title( $listing_id ) : 'Unknown Listing';
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
                                    <span style="background:<?php echo $status_bg; ?>; color:<?php echo $status_color; ?>; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; padding:4px 10px; border-radius:20px;"><?php echo esc_html( ucwords( str_replace('_', ' ', $status) ) ); ?></span>
                                </div>
                                <h3 style="margin:6px 0 4px 0; font-size:1.2rem; font-weight:800; color:#222;">
                                    <a href="<?php echo esc_url($listing_url); ?>" style="color:inherit; text-decoration:none;"><?php echo esc_html($listing_title); ?></a>
                                </h3>
                                <div style="color:#888; font-size:0.88rem; margin-top:4px;">
                                    <?php echo esc_html($start_date); ?>
                                    <?php echo $end_date ? ' &rarr; ' . esc_html($end_date) : ''; ?>
                                    <?php if ($guests) : ?>&nbsp;&bull;&nbsp;<?php echo esc_html($guests); ?> guest(s)<?php endif; ?>
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:1.4rem; font-weight:800; color:#222;">$<?php echo number_format(floatval($total), 2); ?></div>
                                <div style="font-size:0.8rem; color:#888; margin-top:2px;">Total paid</div>
                            </div>
                        </div>

                        <?php if ( $conf_code ) : ?>
                            <div style="margin-top:20px; padding:16px 20px; background:linear-gradient(135deg,#fff9f0,#fff3f7); border:2px dashed #f0c0c0; border-radius:14px; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#e61e4d; margin-bottom:4px;">🎫 Booking Confirmation Code</div>
                                    <div style="font-size:1.6rem; font-weight:900; font-family:monospace; letter-spacing:3px; color:#222;"><?php echo esc_html($conf_code); ?></div>
                                    <div style="font-size:0.78rem; color:#888; margin-top:4px;">Present this code to your host at check-in.</div>
                                </div>
                                <button onclick="navigator.clipboard.writeText('<?php echo esc_js($conf_code); ?>').then(()=>this.innerText='Copied!')" style="background:#e61e4d; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:0.85rem; white-space:nowrap;">Copy Code</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
