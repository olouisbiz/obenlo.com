<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px;">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin: 0;">My Trips & Bookings</h2>
                <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="color: #e61e4d; font-weight: 700; text-decoration: none;">Explore Listings &rarr;</a>
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
                <div style="text-align: center; padding: 60px 40px; background: #fff; border: 1px solid #eee; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="width: 64px; height: 64px; margin: 0 auto 20px; display: block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <h3 style="color: #555; font-size: 1.3rem; margin: 0 0 10px 0;">No trips yet</h3>
                    <p style="color: #888; font-size: 0.95rem; margin: 0;">Your booking history will appear here once you embark on a trip.</p>
                </div>
            <?php else : ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
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
                        <div class="trip-card" style="background: #fff; border: 1px solid #eee; border-radius: 20px; overflow: hidden; display: flex; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: transform 0.2s;">
                            
                            <?php if ( $thumb_url ) : ?>
                                <div class="trip-card-thumb" style="width: 200px; flex-shrink: 0; background: url('<?php echo esc_url($thumb_url); ?>') center/cover; min-height: 160px; border-right: 1px solid #eee;"></div>
                            <?php endif; ?>

                            <div style="padding: 25px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="margin-bottom: 8px;">
                                            <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 10px; border-radius: 20px;"><?php echo esc_html( ucwords( str_replace('_', ' ', $status) ) ); ?></span>
                                        </div>
                                        <h3 style="margin: 6px 0 4px 0; font-size: 1.2rem; font-weight: 800; color: #222; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <a href="<?php echo esc_url($listing_url); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html($listing_title); ?></a>
                                        </h3>
                                        <div style="color: #888; font-size: 0.88rem; margin-top: 4px;">
                                            <?php echo esc_html($start_date); ?>
                                            <?php echo $end_date ? ' &rarr; ' . esc_html($end_date) : ''; ?>
                                            <?php if ($guests) : ?>&nbsp;&bull;&nbsp;<?php echo esc_html($guests); ?> guest(s)<?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right; flex-shrink: 0; width: 100%;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: 10px; gap: 20px;">
                                            
                                            <div style="text-align: left;">
                                                <div style="font-size: 1.4rem; font-weight: 800; color: #222;">$<?php echo number_format(floatval($total), 2); ?></div>
                                                <div style="font-size: 0.75rem; color: #888; margin-top: 2px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                    <?php echo ( $status === 'quote_sent' || $status === 'awaiting_quote' ) ? 'Quoted Total' : 'Total Paid'; ?>
                                                </div>
                                            </div>

                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <?php if ( $status === 'quote_sent' ) : ?>
                                                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin: 0; display: flex; gap: 10px; align-items: center;">
                                                        <input type="hidden" name="action" value="obenlo_pay_quote">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking->ID; ?>">
                                                        <?php wp_nonce_field('pay_quote_' . $booking->ID, 'quote_payment_nonce'); ?>
                                                        
                                                        <select name="payment_method" style="padding: 10px; border-radius: 12px; border: 1.5px solid #eee; font-size: 0.85rem; outline: none; background: #fff; font-weight: 600;">
                                                            <option value="stripe">Card (Stripe)</option>
                                                            <?php if (get_option('obenlo_paypal_enabled', 'yes') === 'yes'): ?>
                                                                <option value="paypal">PayPal</option>
                                                            <?php endif; ?>
                                                        </select>
                                                        
                                                        <button type="submit" style="background: #10b981; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                                                            Pay & Confirm
                                                        </button>
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
                                                            style="background: #e61e4d; color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(230,30,77,0.3);">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                        Message Host
                                                    </button>
                                                <?php endif; endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 15px; display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                                            <?php if (in_array($status, ['pending', 'pending_payment'])) : ?>
                                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                    <input type="hidden" name="action" value="obenlo_cancel_booking">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking->ID; ?>">
                                                    <?php wp_nonce_field('cancel_booking', 'cancel_nonce'); ?>
                                                    <button type="submit" style="background: none; border: 1px solid #fee2e2; color: #ef4444; padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.8rem; transition: all 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                                        Cancel Booking
                                                    </button>
                                                </form>
                                            <?php elseif (in_array($status, ['confirmed', 'approved', 'completed', 'declined', 'cancelled']) && get_post_meta($booking->ID, '_obenlo_refund_requested', true) !== 'yes') : ?>
                                                <button onclick="openRefundModal(<?php echo $booking->ID; ?>, '<?php echo esc_js($listing_title); ?>')" 
                                                        style="background: #f9fafb; border: 1px solid #eee; color: #666; padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 0.8rem; transition: all 0.2s;" onmouseover="this.style.borderColor='#ddd'; this.style.background='#f3f4f6'" onmouseout="this.style.borderColor='#eee'; this.style.background='#f9fafb'">
                                                    Request Refund
                                                </button>
                                            <?php elseif (get_post_meta($booking->ID, '_obenlo_refund_requested', true) === 'yes') : ?>
                                                <span style="font-size: 0.8rem; color: #f97316; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                    Refund Requested
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( $conf_code ) : ?>
                                    <div style="margin-top: 20px; padding: 15px; background: #fafafa; border: 1px solid #eee; border-radius: 12px; display: flex; flex-direction: column; gap: 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #e61e4d; margin-bottom: 2px;">🎫 Confirmation Code</div>
                                                <div style="font-size: 1.4rem; font-weight: 900; font-family: monospace; letter-spacing: 2px; color: #222;"><?php echo esc_html($conf_code); ?></div>
                                            </div>
                                            <button onclick="navigator.clipboard.writeText('<?php echo esc_js($conf_code); ?>').then(()=>this.innerText='Copied!')" style="background: #fff; color: #e61e4d; border: 1px solid #e61e4d; padding: 6px 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.75rem;">Copy</button>
                                        </div>
                                        <?php 
                                        $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                                        if ($virtual_link && in_array($status, ['confirmed', 'approved', 'completed'])) : 
                                            $secure_url = Obenlo_Booking_Virtual_Security::get_secure_join_url($booking->ID);
                                        ?>
                                            <a href="<?php echo esc_url($secure_url); ?>" target="_blank" style="background: #222; color: #fff; border: none; padding: 12px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; flex: 1;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M15 10l5 5-5 5"></path><path d="M4 4v7a4 4 0 0 0 4 4h12"></path></svg>
                                                Join Event
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
