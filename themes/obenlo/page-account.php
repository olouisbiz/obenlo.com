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
