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

<style>
    @media (max-width: 768px) {
        .obenlo-account-container { flex-direction: column !important; padding-bottom: 90px !important; }
        .listing-sidebar { 
            width: 100% !important; 
            height: auto !important; 
            position: fixed !important; 
            bottom: 0 !important; 
            left: 0 !important; 
            right: 0 !important; 
            border-right: none !important; 
            border-top: 1px solid #f0f0f0 !important; 
            background: #fff !important; 
            padding: 10px 5px calc(10px + env(safe-area-inset-bottom)) 5px !important; 
            flex-direction: row !important; 
            justify-content: space-around !important; 
            overflow-x: auto !important; 
            gap: 0 !important; 
            z-index: 10000 !important; 
            box-shadow: 0 -5px 15px rgba(0,0,0,0.05) !important; 
            top: auto !important;
            border-radius: 0 !important;
        }
        .listing-sidebar h1, .listing-sidebar > div[style*="margin-top"] { display: none !important; }
        .listing-sidebar > div { flex-direction: row !important; width: 100% !important; justify-content: space-around !important; }
        
        .nav-item-account { 
            flex-direction: column !important; 
            gap: 2px !important; 
            padding: 8px 5px !important; 
            min-width: 60px !important; 
            background: transparent !important; 
            color: #999 !important; 
            border-radius: 0 !important; 
            white-space: nowrap !important;
            box-shadow: none !important;
            font-size: 0.65rem !important;
            text-align: center;
        }
        .nav-item-account svg { width: 22px !important; height: 22px !important; margin-bottom: 2px; }
        /* Active state for mobile bottom nav */
        /* Note: The active color is handled by the inline styles in the loop, but we override backgrounds here */
        .nav-item-account[style*="background: rgb(230, 30, 77)"] {
            background: transparent !important;
            color: #e61e4d !important;
            box-shadow: none !important;
        }
    }
    @media (min-width: 769px) {
        .listing-sidebar { position: sticky !important; top: 100px !important; }
    }
</style>

<div class="obenlo-account-container listing-layout" style="max-width: 1200px; margin: 60px auto; padding: 0 20px; display: flex; gap: 40px; min-height: 600px;">
    
    <!-- Sidebar -->
    <div class="listing-sidebar" style="width: 280px; flex-shrink: 0; background: #fff; border: 1px solid #eee; border-radius: 24px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); height: fit-content;">
        <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0 0 25px 0; color: #222; letter-spacing: -0.5px;">Account</h1>
        
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <?php
            $nav_items = array(
                'dashboard'    => array('label' => 'Dashboard', 'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>'),
                'profile'      => array('label' => 'Personal Info', 'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>'),
                'trips'        => array('label' => 'My Trips & Bookings', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>'),
                'messages'     => array('label' => 'Messages', 'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>'),
                'announcements'=> array('label' => 'Announcements', 'icon' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>'),
                'support'      => array('label' => 'Help & Support', 'icon' => '<circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>'),
                'guide'        => array('label' => 'Guest Guide', 'icon' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>'),
                'refunds'      => array('label' => 'Refunds', 'icon' => '<path d="M11 15l-3-3 3-3"></path><path d="M8 12h8"></path><circle cx="12" cy="12" r="10"></circle>'),
                'testimony'    => array('label' => 'Obenlo Love', 'icon' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>'),
            );

            foreach ($nav_items as $key => $item) :
                $is_active = ($tab === $key);
                $color     = $is_active ? '#fff' : '#222';
                $bg        = $is_active ? '#e61e4d' : 'transparent';
                $icon_color= $is_active ? '#fff' : '#e61e4d';
            ?>
                <a href="?tab=<?php echo $key; ?>" class="nav-item-account" style="display: flex; align-items: center; gap: 12px; padding: 12px 15px; border-radius: 14px; text-decoration: none; font-weight: 700; font-size: 0.95rem; color: <?php echo $color; ?>; background: <?php echo $bg; ?>; transition: all 0.2s; <?php echo $is_active ? 'box-shadow: 0 4px 15px rgba(230,30,77,0.2);' : ''; ?>" onmouseover="if(!<?php echo $is_active ? 'true' : 'false'; ?>){this.style.background='#fcfcfc'; this.style.color='#e61e4d';}" onmouseout="if(!<?php echo $is_active ? 'true' : 'false'; ?>){this.style.background='transparent'; this.style.color='#222';}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px; color: <?php echo $is_active ? '#fff' : '#999'; ?>;"><?php echo $item['icon']; ?></svg>
                    <?php echo $item['label']; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ( in_array('host', (array)$user->roles) ) : ?>
            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 25px;">
                <a href="<?php echo esc_url( home_url('/host-dashboard') ); ?>" style="color: #222; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 12px 15px; border-radius: 14px; background: #fafafa; border: 1px solid #eee; transition: all 0.2s;" onmouseover="this.style.background='#fff'; this.style.borderColor='#e61e4d'; this.style.color='#e61e4d';" onmouseout="this.style.background='#fafafa'; this.style.borderColor='#eee'; this.style.color='#222';">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px; color: #999;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Host Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div style="flex-grow: 1; min-width: 0;">
        <?php 
        $obenlo_error = isset($_GET['obenlo_error']) ? sanitize_text_field($_GET['obenlo_error']) : '';
        if ($obenlo_error) :
            $error_msg = 'An unexpected error occurred.';
            if ($obenlo_error === 'security_failed') $error_msg = 'Security check failed. Please refresh and try again.';
            if ($obenlo_error === 'booking_error') $error_msg = 'Payment processing failed. Please contact support.';
            if ($obenlo_error === 'unauthorized') $error_msg = 'You do not have permission to perform this action.';
            if ($obenlo_error === 'invalid_booking') $error_msg = 'The booking reference is invalid.';
            if ($obenlo_error === 'invalid_status') $error_msg = 'This booking is not in a state that can be paid.';
            

        ?>
            <div style="padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 10px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php echo esc_html( $error_msg ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $update_message ) : ?>
            <div style="padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; <?php echo $message_type === 'success' ? 'background: #ecfdf5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;'; ?>">
                <?php echo esc_html( $update_message ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $tab === 'dashboard' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'dashboard'); ?>
        <?php elseif ( $tab === 'profile' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'profile'); ?>
        <?php elseif ( $tab === 'trips' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'trips'); ?>

        <?php elseif ( $tab === 'messages' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'messages'); ?>

        <?php elseif ( $tab === 'announcements' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'announcements'); ?>

        <?php elseif ( $tab === 'support' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'support'); ?>

        <?php elseif ( $tab === 'guide' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'guide'); ?>

        <?php elseif ( $tab === 'testimony' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'testimony'); ?>

        <?php elseif ( $tab === 'refunds' ) : ?>
            <?php get_template_part('template-parts/account/tab', 'refunds'); ?>

        <?php endif; ?>
    </div>
</div>

<!-- Request Refund Modal -->
<?php get_template_part('template-parts/account/refund', 'modal'); ?>

<?php get_footer(); ?>
