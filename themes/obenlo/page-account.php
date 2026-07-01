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
    .obenlo-account-container {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
        display: flex;
        gap: 40px;
        min-height: 600px;
        font-family: 'Inter', -apple-system, sans-serif;
        --dash-brand: #e61e4d;
        --dash-brand-dark: #b5143a;
        --dash-radius-md: 14px;
        --dash-transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .account-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.03);
        height: fit-content;
        position: sticky;
        top: 100px;
    }
    .account-sidebar h1 {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0 0 25px 0;
        color: #18181b;
        letter-spacing: -0.5px;
    }
    .account-nav {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: var(--dash-radius-md);
        text-decoration: none;
        font-weight: 650;
        font-size: 0.95rem;
        color: #52525b;
        transition: var(--dash-transition);
    }
    .sidebar-link:hover {
        background: #f4f4f5;
        color: #18181b;
        transform: translateX(4px);
    }
    .sidebar-link.active {
        background: linear-gradient(135deg, var(--dash-brand), var(--dash-brand-dark));
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(230,30,77,0.25);
    }
    .sidebar-link svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        color: #a1a1aa;
        transition: transform var(--dash-transition);
    }
    .sidebar-link.active svg {
        color: #ffffff;
        transform: scale(1.1);
    }
    .host-dashboard-link {
        margin-top: 30px;
        border-top: 1px solid rgba(0,0,0,0.06);
        padding-top: 25px;
    }
    .host-btn {
        color: #18181b;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: var(--dash-radius-md);
        background: #fafafa;
        border: 1px solid rgba(0,0,0,0.06);
        transition: var(--dash-transition);
    }
    .host-btn:hover {
        background: #ffffff;
        border-color: var(--dash-brand);
        color: var(--dash-brand);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .host-btn svg {
        width: 18px;
        height: 18px;
        color: #a1a1aa;
        stroke-width: 2.2;
    }
    .host-btn:hover svg {
        color: var(--dash-brand);
    }
    .account-content {
        flex-grow: 1;
        min-width: 0;
    }
    @media (max-width: 768px) {
        body { padding-bottom: 100px !important; }
        .site-footer { margin-bottom: 90px !important; }
        .obenlo-account-container {
            flex-direction: column;
            gap: 20px;
            margin: 20px auto;
            padding: 0 15px;
            padding-bottom: 20px;
            min-height: auto;
        }
        .account-sidebar {
            width: 100%;
            height: auto;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-right: none;
            border-top: 1px solid rgba(0,0,0,0.08);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 10px 5px;
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            overflow-x: auto;
            gap: 0;
            z-index: 10000;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
            top: auto;
            border-radius: 0;
            align-items: center;
        }
        .account-sidebar h1 {
            display: none;
        }
        .account-nav {
            display: flex;
            flex-direction: row;
            gap: 0;
            width: max-content;
        }
        .sidebar-link {
            flex-direction: column;
            gap: 2px;
            padding: 8px 12px;
            min-width: 65px;
            background: transparent !important;
            box-shadow: none !important;
            justify-content: center;
        }
        .sidebar-link.active {
            color: var(--dash-brand);
            background: transparent !important;
        }
        .sidebar-link svg {
            width: 22px;
            height: 22px;
            margin-bottom: 2px;
            color: inherit;
        }
        .sidebar-link span {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .host-dashboard-link {
            margin-top: 0;
            border-top: none;
            padding-top: 0;
            display: flex;
            align-items: center;
        }
        .host-btn {
            flex-direction: column;
            gap: 2px;
            padding: 8px 12px;
            min-width: 65px;
            background: transparent !important;
            border: none;
            box-shadow: none !important;
            justify-content: center;
            font-size: 0.65rem;
        }
        .host-btn svg {
            width: 22px;
            height: 22px;
            margin-bottom: 2px;
            color: inherit;
        }
        .host-btn span {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
        }
    }
</style>

<div class="obenlo-account-container listing-layout">
    
    <!-- Sidebar -->
    <div class="account-sidebar">
        <h1>Account</h1>
        
        <div class="account-nav">
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
                $active_class = ($tab === $key) ? ' active' : '';
            ?>
                <a href="?tab=<?php echo esc_attr($key); ?>" class="sidebar-link<?php echo $active_class; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><?php echo $item['icon']; ?></svg>
                    <?php echo esc_html($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if ( in_array('host', (array)$user->roles) ) : ?>
            <div class="host-dashboard-link">
                <a href="<?php echo esc_url( home_url('/host-dashboard') ); ?>" class="host-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Host Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="account-content">
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
