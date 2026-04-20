<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px;">
    <div>
        <h2 style="font-size: 2.22rem; font-weight: 800; margin: 0; color: #222; letter-spacing: -0.8px;">My Trips & Bookings</h2>
        <p style="color: #666; margin: 8px 0 0 0; font-size: 1.05rem;">Manage your stays and review past adventures.</p>
    </div>
    <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="color: #e61e4d; font-weight: 800; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 14px; background: #fff5f7; transition: all 0.2s;" onmouseover="this.style.background='#e61e4d'; this.style.color='#fff';" onmouseout="this.style.background='#fff5f7'; this.style.color='#e61e4d';">
        Explore Listings
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
    </a>
</div>
            
            <?php
            // TRIPS LOGIC (Ported from page-trips.php)
            $bookings = get_posts( array(
                'post_type'      => 'booking',
                'author'         => $user_id,
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );
            ?>

            <?php if ( empty( $bookings ) ) : ?>
                <div style="text-align: center; padding: 80px 40px; background: #fff; border: 1px solid #eee; border-radius: 24px; box-shadow: 0 4px 25px rgba(0,0,0,0.02);">
                    <div style="width: 70px; height: 70px; background: #fff5f7; color: #e61e4d; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                    <h3 style="font-size: 1.4rem; font-weight: 800; color: #222; margin-bottom: 10px;">No trips found</h3>
                    <p style="color: #666; margin-bottom: 25px;">Your adventure begins here. Start exploring our listings.</p>
                    <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background: #e61e4d; color: #fff; font-weight: 800; padding: 14px 28px; border-radius: 14px; text-decoration: none; display: inline-block;">Find a Place</a>
                </div>
            <?php else : ?>
                <div style="display: flex; flex-direction: column; gap: 30px;">
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
                        if ( $status === 'quote_sent' )                                       { $status_color = '#10b981'; $status_bg = '#ecfdf5'; }
                        if ( $status === 'pending_payment' )                                  { $status_color = '#f97316'; $status_bg = '#fff7ed'; }
                    ?>
                        <div class="trip-card" style="background: #fff; border: 1px solid #eee; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 25px rgba(0,0,0,0.03); transition: all 0.3s; display: flex; flex-direction: column;">
                            
                            <div style="display: flex; min-height: 180px;">
                                <?php if ( $thumb_url ) : ?>
                                    <div class="trip-card-thumb" style="width: 260px; flex-shrink: 0; background: url('<?php echo esc_url($thumb_url); ?>') center/cover; position: relative;">
                                        <div style="position: absolute; top: 15px; left: 15px; background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 6px 14px; border-radius: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); backdrop-filter: blur(4px);">
                                            <?php echo esc_html( ucwords( str_replace('_', ' ', $status) ) ); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 30px;">
                                        <div style="flex: 1;">
                                            <h3 style="margin: 0 0 10px 0; font-size: 1.4rem; font-weight: 800; line-height: 1.2;">
                                                <a href="<?php echo esc_url($listing_url); ?>" style="color: #222; text-decoration: none; transition: color 0.2s;"><?php echo esc_html($listing_title); ?></a>
                                            </h3>
                                            <div style="display: flex; align-items: center; gap: 15px; color: #666; font-size: 0.95rem; font-weight: 500;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                                    <?php echo esc_html($start_date); ?>
                                                </div>
                                                <?php if ($end_date) : ?>
                                                    <div style="color: #ccc;">&bull;</div>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <?php echo esc_html($end_date); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($guests) : ?>
                                                    <div style="color: #ccc;">&bull;</div>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                                        <?php echo esc_html($guests); ?> guest(s)
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right; border-left: 1px solid #f0f0f0; padding-left: 30px;">
                                            <div style="font-size: 1.65rem; font-weight: 900; color: #222; letter-spacing: -0.5px;">$<?php echo number_format(floatval($total), 2); ?></div>
                                            <div style="font-size: 0.7rem; color: #999; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">
                                                <?php echo ( $status === 'quote_sent' || $status === 'awaiting_quote' ) ? 'Invoiced Amount' : 'Total Paid'; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; pt-20; border-top: 1px solid #f5f5f5; padding-top: 25px;">
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <?php if (in_array($status, ['pending', 'pending_payment'])) : ?>
                                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                    <input type="hidden" name="action" value="obenlo_cancel_booking">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking->ID; ?>">
                                                    <?php wp_nonce_field('cancel_booking', 'cancel_nonce'); ?>
                                                    <button type="submit" style="background: none; border: none; color: #ef4444; font-weight: 700; cursor: pointer; font-size: 0.85rem; padding: 0; text-decoration: underline;">Cancel Request</button>
                                                </form>
                                            <?php elseif (in_array($status, ['confirmed', 'approved', 'completed', 'declined', 'cancelled']) && get_post_meta($booking->ID, '_obenlo_refund_requested', true) !== 'yes') : ?>
                                                <button onclick="openRefundModal(<?php echo $booking->ID; ?>, '<?php echo esc_js($listing_title); ?>')" style="background: none; border: none; color: #666; font-weight: 700; cursor: pointer; font-size: 0.85rem; padding: 0; text-decoration: underline;">Request Refund</button>
                                            <?php elseif (get_post_meta($booking->ID, '_obenlo_refund_requested', true) === 'yes') : ?>
                                                <span style="font-size: 0.85rem; color: #f97316; font-weight: 700;">Refund Requested</span>
                                            <?php endif; ?>
                                        </div>

                                        <div style="display: flex; gap: 12px; align-items: center;">
                                            <?php if ( $status === 'quote_sent' ) : ?>
                                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin: 0; display: flex; gap: 12px; align-items: center;">
                                                    <input type="hidden" name="action" value="obenlo_pay_quote">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking->ID; ?>">
                                                    <?php wp_nonce_field('pay_quote_' . $booking->ID, 'quote_payment_nonce'); ?>
                                                    
                                                    <select name="payment_method" style="padding: 12px 15px; border-radius: 14px; border: 2px solid #eee; font-size: 0.9rem; outline: none; background: #fff; font-weight: 700; color: #444; cursor: pointer;">
                                                        <option value="stripe">Card Payment</option>
                                                        <?php if (get_option('obenlo_paypal_enabled', 'yes') === 'yes'): ?>
                                                            <option value="paypal">PayPal</option>
                                                        <?php endif; ?>
                                                        <?php if (wp_get_environment_type() === 'local'): ?>
                                                            <option value="demo_bypass">Simulate Payment (Local)</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    
                                                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 0.9rem; background: #10b981; border: none; box-shadow: 0 4px 15px rgba(16,185,129,0.3);"><?php echo __('Pay & Confirm', 'obenlo'); ?></button>
                                                </form>
                                            <?php endif; ?>

                                            <?php 
                                            $host_id = get_post_meta($booking->ID, '_obenlo_host_id', true);
                                            if ($host_id) : 
                                                $host_user = get_userdata($host_id);
                                                if ($host_user) :
                                                    $host_name = $host_user->display_name;
                                                    $host_avatar = get_avatar_url($host_id);
                                            ?>
                                                <button onclick="if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url($host_avatar); ?>');} else { window.location.href='<?php echo esc_url(home_url('/login')); ?>'; }"
                                                        style="background: #e61e4d; color: #fff; border: none; padding: 12px 25px; border-radius: 14px; font-weight: 800; cursor: pointer; font-size: 0.9rem; transition: all 0.25s; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(230,30,77,0.25);">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                    Message Host
                                                </button>
                                            <?php endif; endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ( $conf_code ) : ?>
                                <div style="background: #fdfdfd; border-top: 1px dashed #eee; padding: 25px 30px; display: flex; align-items: center; justify-content: space-between; gap: 30px;">
                                    <div style="display: flex; align-items: center; gap: 20px;">
                                        <div style="width: 50px; height: 50px; background: #fff5f7; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e61e4d;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;"><path d="M15 5V7M15 11V13M15 17V19M5 5C3.89543 5 3 5.89543 3 7V10C4.10457 10 5 10.8954 5 12C5 13.1046 4.10457 14 3 14V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V14C19.8954 14 19 13.1046 19 12C19 10.8954 19.8954 10 21 10V7C21 5.89543 20.1046 5 19 5H5Z"></path></svg>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #888; margin-bottom: 2px;">Your Confirmation Code</div>
                                            <div style="font-size: 1.5rem; font-weight: 900; font-family: 'JetBrains Mono', monospace; color: #222; letter-spacing: 2px;"><?php echo esc_html($conf_code); ?></div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php 
                                        $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                                        if ($virtual_link && in_array($status, ['confirmed', 'approved', 'completed'])) : 
                                            $secure_url = Obenlo_Booking_Virtual_Security::get_secure_join_url($booking->ID);
                                        ?>
                                            <a href="<?php echo esc_url($secure_url); ?>" target="_blank" style="background: #222; color: #fff; padding: 10px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M15 10l5 5-5 5"></path><path d="M4 4v7a4 4 0 0 0 4 4h12"></path></svg>
                                                Join Now
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="navigator.clipboard.writeText('<?php echo esc_js($conf_code); ?>').then(()=>this.innerText='Copied!')" style="background: #fff; color: #e61e4d; border: 1.5px solid #e61e4d; padding: 10px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 0.85rem; transition: all 0.2s;" onmouseover="this.style.background='#fff5f7';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#fff';this.style.transform='none';">Copy Code</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
