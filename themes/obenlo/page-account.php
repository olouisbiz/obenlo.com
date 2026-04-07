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

$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';

?>

<div class="obenlo-account-container listing-layout" style="max-width: 1200px; margin: 60px auto; padding: 0 20px; display: flex; gap: 40px; min-height: 600px;">
    
    <!-- Sidebar -->
    <div class="listing-sidebar" style="width: 250px; flex-shrink: 0; display: flex; flex-direction: column; gap: 8px;">
        <h1 style="font-size: 2rem; font-weight: 800; margin: 0 0 20px 0; color: #222;">Account</h1>
        
        <a href="?tab=dashboard" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'dashboard' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'dashboard' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='dashboard')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='dashboard')this.style.background='transparent'">
            Dashboard
        </a>
        <a href="?tab=profile" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'profile' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'profile' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='profile')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='profile')this.style.background='transparent'">
            Personal Info
        </a>
        <a href="?tab=trips" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'trips' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'trips' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='trips')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='trips')this.style.background='transparent'">
            My Trips & Bookings
        </a>
        <a href="?tab=messages" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'messages' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'messages' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='messages')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='messages')this.style.background='transparent'">
            Messages
        </a>
        <a href="?tab=announcements" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'announcements' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'announcements' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='announcements')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='announcements')this.style.background='transparent'">
            Announcements
        </a>
        <a href="?tab=support" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'support' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'support' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='support')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='support')this.style.background='transparent'">
            Help & Support
        </a>
        <a href="?tab=guide" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'guide' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'guide' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='guide')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='guide')this.style.background='transparent'">
            Guest Guide
        </a>
        <a href="?tab=refunds" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'refunds' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'refunds' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='refunds')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='refunds')this.style.background='transparent'">
            Refunds
        </a>
        <a href="?tab=testimony" style="padding: 12px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: <?php echo $tab === 'testimony' ? '#fff' : '#666'; ?>; background: <?php echo $tab === 'testimony' ? '#e61e4d' : 'transparent'; ?>; transition: all 0.2s;" onmouseover="if('<?php echo $tab; ?>'!=='testimony')this.style.background='#f5f5f5'" onmouseout="if('<?php echo $tab; ?>'!=='testimony')this.style.background='transparent'">
            Obenlo Love
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

        <?php if ( $tab === 'dashboard' ) : ?>
            <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; margin-top: 0;">Welcome back, <?php echo esc_html( $user->first_name ?: $user->display_name ); ?>!</h2>
            <p style="color:#666; font-size:1.05rem; margin-bottom:30px;">Manage your stays, communicate with hosts, and keep your profile updated.</p>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:40px;">
                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#eff6ff; color:#3b82f6; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;"><?php echo __('My Trips & Bookings', 'obenlo'); ?></h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">View your upcoming bookings and past adventures.</p>
                    </div>
                    <a href="?tab=trips" style="margin-top:20px; display:inline-block; font-weight:700; color:#3b82f6; text-decoration:none;">View Trips &rarr;</a>
                </div>

                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#fef2f2; color:#ef4444; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;">Messages</h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">Check unread messages from your hosts.</p>
                    </div>
                    <a href="?tab=messages" style="margin-top:20px; display:inline-block; font-weight:700; color:#ef4444; text-decoration:none;">Open Inbox &rarr;</a>
                </div>

                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#ecfdf5; color:#10b981; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;">Guest Guide</h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">Learn about platform rules, policies, and tips.</p>
                    </div>
                    <a href="?tab=guide" style="margin-top:20px; display:inline-block; font-weight:700; color:#10b981; text-decoration:none;">Read Guide &rarr;</a>
                </div>
            </div>

            <div style="background: #222; border-radius: 20px; padding: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h3 style="color: #fff; margin: 0 0 8px 0; font-size: 1.4rem; font-weight: 800;">Looking for your next stay?</h3>
                    <p style="color: #bbb; margin: 0; font-size: 1rem;">Explore thousands of incredible listings on Obenlo.</p>
                </div>
                <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background: #e61e4d; color: #fff; font-weight: 700; padding: 12px 24px; border-radius: 12px; text-decoration: none; display: inline-block; transition: all 0.2s;">Start Exploring</a>
            </div>

        <?php elseif ( $tab === 'profile' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Personal Info</h2>
            
            <form method="POST" action="" style="max-width: 600px; background: #fff; padding: 30px; border: 1px solid #eee; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <input type="hidden" name="action" value="update_profile">
                <?php wp_nonce_field( 'update_user_profile', 'profile_nonce' ); ?>
                
                <div class="grid-row" style="margin-bottom: 20px;">
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">First Name</label>
                        <input type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Unique Guest ID</label>
                    <div style="background: #f9fafb; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; font-family: monospace; font-weight: 800; color: #e61e4d; font-size: 1.1rem; letter-spacing: 1px;">
                        <?php echo esc_html( Obenlo_Booking_Payments::get_user_guest_id($user_id) ); ?>
                    </div>
                    <p style="font-size: 0.75rem; color: #888; margin-top: 6px;">Use this ID for check-ins at the door or when contacting support.</p>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Email Address</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                </div>

                <button type="submit" style="background: #222; color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#000'" onmouseout="this.style.background='#222'">
                    Save Changes
                </button>
            </form>

        <?php elseif ( $tab === 'trips' ) : ?>
            
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
                                                        style="background: #e61e4d; color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(230,30,77,0.3);">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                    Message Host
                                                </button>
                                            <?php endif; endif; ?>
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

        <?php elseif ( $tab === 'messages' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Messages</h2>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
                <?php echo do_shortcode('[obenlo_messages_page]'); ?>
            </div>

        <?php elseif ( $tab === 'announcements' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Platform Announcements</h2>
            <p style="color:#666; margin-bottom:30px;">Latest updates and news from Obenlo.</p>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <?php echo do_shortcode('[obenlo_broadcasts_page]'); ?>
            </div>

        <?php elseif ( $tab === 'support' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Help & Support</h2>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <?php echo do_shortcode('[obenlo_support_page]'); ?>
            </div>

        <?php elseif ( $tab === 'guide' ) : ?>
            <div class="obenlo-guest-guide">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px;">Guest Guide & Policies</h2>
                <p style="color:#666; font-size:1.05rem; margin-bottom:30px;">Welcome to the Obenlo community. Learn how to book, communicate, and ensure a smooth experience for your stays and services.</p>

                <div style="display:grid; gap:30px;">
                    <div style="background:#fff; border:1px solid #eee; border-radius:16px; padding:30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                        <h3 style="margin-top:0; color:#3b82f6; font-size:1.3rem; display:flex; align-items:center; gap:10px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            Booking Approvals & Limits
                        </h3>
                        <p style="color:#444; line-height:1.6;"><strong>Booking Requests:</strong> When you book a stay or service, the request is sent to the Host. Your card will only be charged once the Host <em>approves</em> your request. If they decline or let it expire, you are not charged.</p>
                        <p style="color:#444; line-height:1.6;"><strong>Booking Completion:</strong> Once your stay or service is finished, the host will mark the booking as <em>Completed</em>. This finalizes the transaction and releases the host's payment. If you have any issues, please contact support or message the host <em>before</em> the booking status changes to Completed.</p>
                        <p style="color:#444; line-height:1.6;"><strong>Guest Limits:</strong> Please respect the maximum capacity listed by the host. Bringing unauthorized guests violates our local community guidelines and could result in immediate cancellation without refund.</p>
                    </div>

                    <div style="background:#fff; border:1px solid #eee; border-radius:16px; padding:30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                        <h3 style="margin-top:0; color:#10b981; font-size:1.3rem; display:flex; align-items:center; gap:10px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            Communication & Support
                        </h3>
                        <p style="color:#444; line-height:1.6;"><strong>Messaging Hosts:</strong> Always communicate through Obenlo’s native messaging system. This keeps a clear record of agreements and allows us to mediate if something goes wrong.</p>
                        <p style="color:#444; line-height:1.6;"><strong>Help Center:</strong> If you face an issue the host cannot resolve—or if there's an emergency—contact the Obenlo Support team using the Help & Support tab. Do not wait until the reservation is over to report a problem.</p>
                    </div>

                    <div style="background:#fff; border:1px solid #eee; border-radius:16px; padding:30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                        <h3 style="margin-top:0; color:#8b5cf6; font-size:1.3rem; display:flex; align-items:center; gap:10px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            Reviews & Respect
                        </h3>
                        <p style="color:#444; line-height:1.6;"><strong>Leaving Reviews:</strong> After your stay, please leave an honest, constructive review. This helps future guests make informed decisions and rewards great hosts.</p>
                        <p style="color:#444; line-height:1.6;"><strong>House Rules:</strong> Treat the host’s property like your own. Excessive noise, damages, or throwing unauthorized parties will result in a permanent ban from the Obenlo platform.</p>
                    </div>
                </div>
            </div>

        <?php elseif ( $tab === 'testimony' ) : ?>
            <div class="obenlo-testimony-section">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 8px;">Obenlo Love</h2>
                <p style="color:#666; font-size:1.05rem; margin-bottom:30px;">We'd love to hear about your experience with the platform. Your feedback helps us grow and improve!</p>

                <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                    <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; border: 1px solid #a7f3d0;">
                        ❤️ Thank you! Your testimony has been submitted for moderation.
                    </div>
                <?php endif; ?>

                <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 40px;">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; font-weight: 800;">Write a Testimony</h3>
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                        <input type="hidden" name="action" value="obenlo_save_testimony">
                        <?php wp_nonce_field('save_testimony', 'testimony_nonce'); ?>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Headline</label>
                            <input type="text" name="testimony_title" placeholder="e.g. Best booking experience ever!" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Your Experience</label>
                            <textarea name="testimony_content" rows="4" placeholder="Tell us what you love about Obenlo..." required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s; resize: vertical;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'"></textarea>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Overall Rating</label>
                            <div class="star-rating" style="display: flex; gap: 10px; font-size: 2rem; color: #ddd; cursor: pointer;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <span class="star" data-value="<?php echo $i; ?>" style="transition: color 0.2s;" onclick="setRating(<?php echo $i; ?>)" onmouseover="highlightStars(<?php echo $i; ?>)" onmouseout="resetStars()">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="testimony_rating" id="testimony_rating" value="5">
                        </div>

                        <button type="submit" style="background: #e61e4d; color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 10px;" onmouseover="this.style.background='#d91945'" onmouseout="this.style.background='#e61e4d'">
                            Submit Testimony
                        </button>
                    </form>
                </div>

                <script>
                    let currentRating = 5;
                    const stars = document.querySelectorAll('.star');
                    const ratingInput = document.getElementById('testimony_rating');

                    function setRating(val) {
                        currentRating = val;
                        ratingInput.value = val;
                        updateStars(val);
                    }

                    function highlightStars(val) {
                        updateStars(val);
                    }

                    function resetStars() {
                        updateStars(currentRating);
                    }

                    function updateStars(val) {
                        stars.forEach((star, index) => {
                            if (index < val) {
                                star.style.color = '#f59e0b';
                            } else {
                                star.style.color = '#ddd';
                            }
                        });
                    }

                    // Initial state
                    updateStars(5);
                </script>

                <?php
                $user_testimonies = get_posts(array(
                    'post_type' => 'testimony',
                    'author' => get_current_user_id(),
                    'post_status' => array('pending', 'publish', 'draft'),
                    'posts_per_page' => -1
                ));
                ?>

                <?php if (!empty($user_testimonies)): ?>
                    <h3 style="margin-bottom: 20px; font-size: 1.3rem; font-weight: 800;">Your Past Testimonies</h3>
                    <div style="display: grid; gap: 15px;">
                        <?php foreach ($user_testimonies as $post): 
                            $rating = get_post_meta($post->ID, '_obenlo_testimony_rating', true);
                            $status = $post->post_status;
                            $status_label = ($status === 'publish') ? 'Approved & Live' : (($status === 'pending') ? 'Pending Review' : 'Draft');
                            $status_color = ($status === 'publish') ? '#10b981' : (($status === 'pending') ? '#f59e0b' : '#666');
                        ?>
                            <div style="background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                                <div>
                                    <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 5px;">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $rating ? '★' : '☆'); ?>
                                    </div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;"><?php echo esc_html($post->post_title); ?></h4>
                                    <p style="margin: 0; color: #666; font-size: 0.95rem;"><?php echo esc_html($post->post_content); ?></p>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: <?php echo $status_color; ?>;"><?php echo esc_html($status_label); ?></span>
                                    <div style="font-size: 0.8rem; color: #aaa; margin-top: 4px;"><?php echo get_the_date('', $post->ID); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ( $tab === 'refunds' ) : ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Refund History</h2>
            <?php
            $user_refunds = get_posts(array(
                'post_type' => 'refund',
                'author' => get_current_user_id(),
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            ?>

            <?php if (empty($user_refunds)) : ?>
                <div style="text-align: center; padding: 60px 40px; background: #fff; border: 1px solid #eee; border-radius: 20px;">
                    <h3 style="color: #888;">No refund requests found.</h3>
                </div>
            <?php else : ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($user_refunds as $refund) : 
                        $b_id = get_post_meta($refund->ID, '_obenlo_booking_id', true);
                        $r_status = get_post_meta($refund->ID, '_obenlo_refund_status', true);
                    ?>
                        <div style="background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0;">Booking #<?php echo esc_html($b_id); ?></h4>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;"><?php echo esc_html($refund->post_content); ?></p>
                            </div>
                            <span class="badge badge-info" style="background:<?php echo ($r_status === 'completed' ? '#ecfdf5; color:#059669;' : ($r_status === 'pending' ? '#fff7ed; color:#d97706;' : '#fef2f2; color:#dc2626;')); ?>"><?php echo ucfirst($r_status); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- Request Refund Modal -->
<div id="obenlo-refund-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:24px; width:100%; max-width:500px; padding:40px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative;">
        <button onclick="closeRefundModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:24px; color:#aaa; cursor:pointer;">&times;</button>
        <h3 id="refund-listing-title" style="margin-top:0; font-size:1.5rem; font-weight:900; color:#222;">Request Refund</h3>
        <p style="color:#666; margin-bottom:25px;">Please provide a reason for your refund request. The host and Obenlo team will review this shortly.</p>
        
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="obenlo_request_refund">
            <input type="hidden" id="refund-booking-id" name="booking_id" value="">
            <?php wp_nonce_field('request_refund', 'refund_nonce'); ?>
            
            <label style="display:block; font-weight:700; margin-bottom:10px; color:#444;">Reason for Refund</label>
            <textarea name="refund_reason" required rows="4" style="width:100%; padding:15px; border:1px solid #eee; border-radius:15px; font-size:1rem; outline:none; transition:all 0.2s; margin-bottom:25px; border: 1.5px solid #eee;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#eee'"></textarea>
            
            <div style="display:flex; gap:15px;">
                <button type="button" onclick="closeRefundModal()" style="flex:1; background:#f9fafb; color:#222; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer;">Cancel</button>
                <button type="submit" style="flex:1; background:#e61e4d; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(230,30,77,0.3);">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal(bookingId, listingTitle) {
    document.getElementById('refund-booking-id').value = bookingId;
    document.getElementById('refund-listing-title').textContent = 'Refund: ' + listingTitle;
    const modal = document.getElementById('obenlo-refund-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRefundModal() {
    const modal = document.getElementById('obenlo-refund-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close on background click
window.onclick = function(event) {
    const modal = document.getElementById('obenlo-refund-modal');
    if (event.target == modal) {
        closeRefundModal();
    }
}
</script>

<?php get_footer(); ?>
