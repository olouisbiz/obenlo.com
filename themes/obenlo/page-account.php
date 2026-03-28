<?php
/**
 * Template Name: My Account
 * The template for displaying guest profile settings and bookings.
 */

get_header();

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url('/login') );
    exit;
}

$user = wp_get_current_user();
$user_id = $user->ID;

// Handle Profile Form Submission Securely
$update_message = '';
$message_type = 'success';
if ( isset( $_POST['action'] ) && $_POST['action'] === 'update_profile' ) {
    if ( isset( $_POST['profile_nonce'] ) && wp_verify_nonce( $_POST['profile_nonce'], 'update_user_profile' ) ) {
        
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name  = sanitize_text_field( $_POST['last_name'] );
        $email      = sanitize_email( $_POST['email'] );

        $user_data = array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
            'display_name' => trim( $first_name . ' ' . $last_name )
        );

        // Update User
        $user_id = wp_update_user( $user_data );

        if ( is_wp_error( $user_id ) ) {
            $update_message = $user_id->get_error_message();
            $message_type = 'error';
        } else {
            $update_message = 'Profile updated successfully.';
            $user = get_userdata( $user_id ); // Refresh user object
        }
    } else {
        $update_message = 'Security check failed. Please try again.';
        $message_type = 'error';
    }
}

$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'profile';

?>

<div class="obenlo-account-container listing-layout" style="max-width: 1200px; margin: 60px auto; padding: 0 20px; display: flex; gap: 40px; min-height: 600px;">
    
    <!-- Sidebar -->
    <div class="listing-sidebar" style="width: 250px; flex-shrink: 0; display: flex; flex-direction: column; gap: 8px;">
        <h1 style="font-size: 2rem; font-weight: 800; margin: 0 0 20px 0; color: #222;">Account</h1>
        
        <a href="?tab=profile" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'profile' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'profile' ? 'var(--obenlo-primary)' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='profile')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='profile')this.style.background='transparent'">
            Personal Info
        </a>
        <a href="?tab=trips" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'trips' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'trips' ? 'var(--obenlo-primary)' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='trips')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='trips')this.style.background='transparent'">
            My Trips & Bookings
        </a>
        <a href="?tab=messages" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'messages' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'messages' ? 'var(--obenlo-primary)' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='messages')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='messages')this.style.background='transparent'">
            Messages
        </a>
        <a href="?tab=support" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'support' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'support' ? 'var(--obenlo-primary)' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='support')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='support')this.style.background='transparent'">
            Help & Support
        </a>
        
        <?php if ( in_array('host', (array)$user->roles) ) : ?>
            <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                <a href="<?php echo esc_url( home_url('/host-dashboard') ); ?>" style="color: #222; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Switch to Host Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div style="flex-grow: 1; min-width: 0;">
        <?php if ( $update_message ) : ?>
            <div style="padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; <?php echo $message_type === 'success' ? 'background: #ecfdf5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;'; ?>">
                <?php echo esc_html( $update_message ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $tab === 'profile' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Personal Info</h2>
            
            <form method="POST" action="" style="max-width: 600px; background: #fff; padding: 30px; border: 1px solid #eee; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <input type="hidden" name="action" value="update_profile">
                <?php wp_nonce_field( 'update_user_profile', 'profile_nonce' ); ?>
                
                <div class="grid-row" style="margin-bottom: 20px;">
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">First Name</label>
                        <input type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--obenlo-primary)'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--obenlo-primary)'" onblur="this.style.borderColor='#ddd'">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Unique Guest ID</label>
                    <div style="background: #f9fafb; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; font-family: monospace; font-weight: 800; color: var(--obenlo-primary); font-size: 1.1rem; letter-spacing: 1px;">
                        <?php echo esc_html( Obenlo_Booking_Payments::get_user_guest_id($user_id) ); ?>
                    </div>
                    <p style="font-size: 0.75rem; color: #888; margin-top: 6px;">Use this ID for check-ins at the door or when contacting support.</p>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Email Address</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--obenlo-primary)'" onblur="this.style.borderColor='#ddd'">
                </div>

                <button type="submit" style="background: #222; color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#000'" onmouseout="this.style.background='#222'">
                    Save Changes
                </button>
            </form>

        <?php elseif ( $tab === 'trips' ) : ?>
            
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px;">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin: 0;">My Trips & Bookings</h2>
                <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="color: var(--obenlo-primary); font-weight: 700; text-decoration: none;">Explore Listings &rarr;</a>
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
                                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: 10px;">
                                            <div style="text-align: left;">
                                                <div style="font-size: 1.3rem; font-weight: 800; color: #222;">$<?php echo number_format(floatval($total), 2); ?></div>
                                                <div style="font-size: 0.8rem; color: #888; margin-top: 2px;">Total paid</div>
                                            </div>
                                            
                                            <?php 
                                            $host_id = get_post_meta($booking->ID, '_obenlo_host_id', true);
                                            if ($host_id) : 
                                                $host_user = get_userdata($host_id);
                                                if ($host_user) :
                                                    $host_name = $host_user->display_name;
                                                    $host_avatar = get_avatar_url($host_id);
                                            ?>
                                                <button onclick="if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url($host_avatar); ?>');} else { window.location.href='<?php echo esc_url(home_url('/login')); ?>'; }"
                                                        style="background: var(--obenlo-primary); color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(var(--obenlo-primary-rgb),0.3);">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                    Message Host
                                                </button>
                                            <?php endif; endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( $conf_code ) : ?>
                                    <div style="margin-top: 20px; padding: 15px; background: #fafafa; border: 1px solid #eee; border-radius: 12px; display: flex; flex-direction: column; gap: 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--obenlo-primary); margin-bottom: 2px;">🎫 Confirmation Code</div>
                                                <div style="font-size: 1.4rem; font-weight: 900; font-family: monospace; letter-spacing: 2px; color: #222;"><?php echo esc_html($conf_code); ?></div>
                                            </div>
                                            <button onclick="navigator.clipboard.writeText('<?php echo esc_js($conf_code); ?>').then(()=>this.innerText='Copied!')" style="background: #fff; color: var(--obenlo-primary); border: 1px solid var(--obenlo-primary); padding: 6px 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.75rem;">Copy</button>
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

        <?php elseif ( $tab === 'messages' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Messages</h2>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
                <?php echo do_shortcode('[obenlo_messages_page]'); ?>
            </div>

        <?php elseif ( $tab === 'support' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Help & Support</h2>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <?php echo do_shortcode('[obenlo_support_page]'); ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
