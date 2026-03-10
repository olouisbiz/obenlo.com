<?php
/**
 * Site Admin Dashboard Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Admin_Dashboard {

    public function init() {
        add_shortcode( 'obenlo_admin_dashboard', array( $this, 'render_dashboard' ) );
        
        // Handle Admin Actions
        add_action( 'admin_post_obenlo_admin_action', array( $this, 'handle_admin_action' ) );
        add_action( 'admin_post_obenlo_save_settings', array( $this, 'handle_save_settings' ) );
        add_action( 'admin_post_obenlo_save_payment_settings', array( $this, 'handle_save_payment_settings' ) );
        add_action( 'admin_post_obenlo_update_user_fee', array( $this, 'handle_update_user_fee' ) );
    }

    public function render_dashboard() {
        if ( ! current_user_can( 'manage_support' ) ) {
            return '<p>You do not have permission to access the Support Console.</p>';
        }

        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';

        ob_start();
        ?>
        <div class="obenlo-admin-dashboard">
            <style>
                .admin-nav { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; display: flex; gap: 20px; }
                .admin-nav a { text-decoration: none; color: #666; font-weight: 600; padding: 10px 15px; border-radius: 8px; }
                .admin-nav a.active { background: #e61e4d; color: #fff; }
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
                .stat-card { background: #fff; border: 1px solid #eee; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; }
                .stat-value { display: block; font-size: 2.5em; font-weight: 800; color: #e61e4d; margin-bottom: 5px; }
                .stat-label { color: #666; font-weight: 600; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; }
                .admin-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #eee; }
                .admin-table th { background: #f9f9f9; padding: 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #eee; }
                .admin-table td { padding: 15px; border-bottom: 1px solid #eee; }
                .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
                .badge-host { background: #e61e4d; color: #fff; }
                .badge-guest { background: #333; color: #fff; }
                .btn-approve { color: #4CAF50; font-weight: bold; text-decoration: none; margin-right: 10px; }
                .btn-reject { color: #F44336; font-weight: bold; text-decoration: none; }
            </style>

            <div class="admin-nav">
                <?php if ( current_user_can('administrator') ) : ?>
                    <a href="?tab=overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">Overview</a>
                    <a href="?tab=listings" class="<?php echo $tab === 'listings' ? 'active' : ''; ?>">Listings</a>
                    <a href="?tab=users" class="<?php echo $tab === 'users' ? 'active' : ''; ?>">Users</a>
                    <a href="?tab=verifications" class="<?php echo $tab === 'verifications' ? 'active' : ''; ?>">Verifications</a>
                    <a href="?tab=bookings" class="<?php echo $tab === 'bookings' ? 'active' : ''; ?>">Bookings</a>
                    <a href="?tab=payments" class="<?php echo $tab === 'payments' ? 'active' : ''; ?>">Payments</a>
                    <a href="?tab=messaging" class="<?php echo $tab === 'messaging' ? 'active' : ''; ?>">Messaging</a>
                <?php endif; ?>
                <a href="?tab=communication" class="<?php echo $tab === 'communication' ? 'active' : ''; ?>">Support Tickets</a>
                <a href="?tab=live_chat" class="<?php echo $tab === 'live_chat' ? 'active' : ''; ?>">Live Chat</a>
                <?php if ( current_user_can('administrator') ) : ?>
                    <a href="?tab=settings" class="<?php echo $tab === 'settings' ? 'active' : ''; ?>">Settings</a>
                <?php endif; ?>
            </div>

            <?php if ( in_array($tab, array('communication', 'bookings', 'verifications')) ) : ?>
            <script>
                // Auto-refresh the current admin tab every 60 seconds to ensure real-time data
                setTimeout(function() {
                    window.location.reload();
                }, 60000);
            </script>
            <div style="text-align: right; font-size: 0.8em; color: #999; margin-bottom: 10px;">
                <span class="dashicons dashicons-update" style="font-size: 14px; line-height: 1;"></span> Auto-refreshing every 60s
            </div>
            <?php endif; ?>

            <?php
            switch ( $tab ) {
                case 'listings':
                    $this->render_listings_tab();
                    break;
                case 'users':
                    $this->render_users_tab();
                    break;
                case 'verifications':
                    $this->render_verifications_tab();
                    break;
                case 'bookings':
                    $this->render_bookings_tab();
                    break;
                case 'payments':
                    $this->render_payments_tab();
                    break;
                case 'messaging':
                    $this->render_messaging_oversight_tab();
                    break;
                case 'communication':
                    $this->render_communication_tab();
                    break;
                case 'live_chat':
                    $this->render_live_chat_tab();
                    break;
                case 'settings':
                    $this->render_settings_tab();
                    break;
                default:
                    $this->render_overview_tab();
                    break;
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_overview_tab() {
        $user_counts = count_users();
        $hosts = isset( $user_counts['avail_roles']['host'] ) ? $user_counts['avail_roles']['host'] : 0;
        $guests = isset( $user_counts['avail_roles']['guest'] ) ? $user_counts['avail_roles']['guest'] : 0;
        
        $listings_count = wp_count_posts( 'listing' )->publish;
        
        // Revenue Calculation
        $bookings = get_posts( array( 'post_type' => 'booking', 'posts_per_page' => -1 ) );
        $total_revenue = 0;
        foreach ( $bookings as $booking ) {
            $total_revenue += floatval( get_post_meta( $booking->ID, '_obenlo_total_price', true ) );
        }

        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?php echo number_format( $total_revenue, 2 ); ?></span>
                <span class="stat-label">Total GTV ($)</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo $listings_count; ?></span>
                <span class="stat-label">Active Listings</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo $hosts; ?></span>
                <span class="stat-label">Total Hosts</span>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?php echo $guests; ?></span>
                <span class="stat-label">Total Guests</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">System Health</span>
                <span class="stat-value">Good</span>
                <span style="font-size:0.8rem; color:#10b981; font-weight:600;">All services operational</span>
            </div>
        </div>

        <h3>Recent Activity</h3>

        <h3>Recent Activity</h3>
        <p>Site-wide overview of recent platform events will go here.</p>
        <?php
    }

    private function render_listings_tab() {
        $listings = get_posts( array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => array( 'publish', 'pending', 'draft' )
        ) );

        echo '<h3>Manage All Listings</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>Title</th><th>Host</th><th>Status</th><th>Price</th><th>Actions</th></tr>';
        foreach ( $listings as $listing ) {
            $host = get_userdata( $listing->post_author );
            $price = get_post_meta( $listing->ID, '_obenlo_price', true );
            echo '<tr>';
            echo '<td><strong>' . esc_html( $listing->post_title ) . '</strong></td>';
            echo '<td>' . ( $host ? esc_html( $host->display_name ) : 'Unknown' ) . '</td>';
            echo '<td>' . esc_html( ucfirst( $listing->post_status ) ) . '</td>';
            echo '<td>$' . esc_html( $price ) . '</td>';
            echo '<td>';
            echo '<a href="' . get_permalink( $listing->ID ) . '" target="_blank">View</a> | ';
            echo '<a href="#" class="btn-reject">Trash</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    private function render_users_tab() {
        $users = get_users( array( 'role__in' => array( 'host', 'guest' ) ) );

        echo '<h3>Site User Management</h3>';
        echo '<table class="admin-table">';
        ?>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Verification</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
            <?php foreach ( $users as $user ) : 
                $status = Obenlo_Booking_Host_Verification::get_status( $user->ID );
                $v_badge = 'badge-info';
                if($status === 'verified') $v_badge = 'badge-success';
                if($status === 'rejected') $v_badge = 'badge-danger';
                if($status === 'pending') $v_badge = 'badge-warning';
                ?>
                <tr>
                    <td>
                        <div style="font-weight:700;"><?php echo esc_html($user->display_name); ?></div>
                        <div style="font-size:0.8rem; color:#888;"><?php echo esc_html($user->user_email); ?></div>
                    </td>
                    <td><span class="badge badge-info"><?php echo ucfirst($user->roles[0]); ?></span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td><span class="badge <?php echo $v_badge; ?>"><?php echo ucfirst($status); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user->user_registered)); ?></td>
                    <td>
                        <?php if ( in_array( 'host', $user->roles ) ) : ?>
                            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="action" value="obenlo_update_user_fee">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <?php wp_nonce_field( 'update_user_fee_' . $user->ID, 'fee_nonce' ); ?>
                                <input type="number" name="fee_percentage" value="<?php echo esc_attr( get_user_meta( $user->ID, '_obenlo_host_fee_percentage', true ) ); ?>" placeholder="Global" step="0.1" style="width:70px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                                <button type="submit" style="padding:5px 10px; background:#222; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:0.8em;">Save Fee</button>
                            </form>
                        <?php else : ?>
                            <span style="color:#ccc;">N/A</span>
                        <?php endif; ?>
                        <a href="mailto:<?php echo esc_attr($user->user_email); ?>" style="margin-left: 10px;">Contact</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php
        echo '</table>';
    }

    private function render_settings_tab() {
        $global_fee = get_option( 'obenlo_global_platform_fee', '10' );
        $telegram_bot_token = get_option( 'obenlo_telegram_bot_token', '' );
        $telegram_chat_id = get_option( 'obenlo_telegram_chat_id', '' );
        ?>
        <h3>Global Platform Settings</h3>
        <div style="background:#fff; border:1px solid #eee; padding:30px; border-radius:12px; max-width:600px;">
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_save_settings">
                <?php wp_nonce_field( 'save_settings', 'settings_nonce' ); ?>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:10px;">Global Platform Fee (%)</label>
                    <p style="font-size:0.9em; color:#666; margin-bottom:15px;">The default percentage taken from each completed booking total.</p>
                    <input type="number" name="global_fee" value="<?php echo esc_attr( $global_fee ); ?>" step="0.1" required style="width:100px; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:1.1em;"> <span style="font-size:1.2em; font-weight:600; margin-left:10px;">%</span>
                </div>

                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <h4 style="margin-top:0;">Telegram Live Chat Integration</h4>
                    <input type="text" name="telegram_bot_token" value="<?php echo esc_attr( $telegram_bot_token ); ?>" placeholder="Bot Token" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:15px;">
                    <input type="text" name="telegram_chat_id" value="<?php echo esc_attr( $telegram_chat_id ); ?>" placeholder="Agent Chat ID" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:700;">Save Settings</button>
            </form>
        </div>
        <?php
    }

    private function render_payments_tab() {
        // Payment Keys
        $payment_mode = get_option( 'obenlo_payment_mode', 'sandbox' );
        $stripe_publishable = get_option( 'obenlo_stripe_publishable_key', '' );
        $stripe_secret = get_option( 'obenlo_stripe_secret_key', '' );
        $paypal_client_id = get_option( 'obenlo_paypal_client_id', '' );
        $paypal_secret = get_option( 'obenlo_paypal_secret', '' );
        ?>
        <h3>Payment Gateway Configuration</h3>
        <div style="background:#fff; border:1px solid #eee; padding:30px; border-radius:12px; max-width:600px;">
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_save_payment_settings">
                <?php wp_nonce_field( 'save_payment_settings', 'payment_settings_nonce' ); ?>
                
                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:600; margin-bottom:8px;">Transaction Mode</label>
                    <p style="font-size:0.9em; color:#666; margin-bottom:15px;">Configure your production or sandbox credentials for live bookings.</p>
                    <select name="payment_mode" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        <option value="sandbox" <?php selected($payment_mode, 'sandbox'); ?>>Sandbox (Testing)</option>
                        <option value="live" <?php selected($payment_mode, 'live'); ?>>Live (Production)</option>
                    </select>
                </div>

                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <h4 style="margin-bottom:10px;">Stripe Configuration</h4>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Publishable Key</label>
                    <input type="text" name="stripe_publishable" value="<?php echo esc_attr( $stripe_publishable ); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                    
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key</label>
                    <input type="password" name="stripe_secret" value="<?php echo esc_attr( $stripe_secret ); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <h4 style="margin-bottom:10px;">PayPal Configuration</h4>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID</label>
                    <input type="text" name="paypal_id" value="<?php echo esc_attr( $paypal_client_id ); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                    
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret</label>
                    <input type="password" name="paypal_secret" value="<?php echo esc_attr( $paypal_secret ); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:700;">Save Payment Settings</button>
            </form>
        </div>
        <?php
    }

    public function handle_save_settings() {
        if ( ! current_user_can( 'administrator' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'save_settings', 'settings_nonce' );

        if ( isset($_POST['global_fee']) ) {
            update_option( 'obenlo_global_platform_fee', sanitize_text_field($_POST['global_fee']) );
        }

        // Telegram Settings
        if ( isset($_POST['telegram_bot_token']) ) {
            update_option( 'obenlo_telegram_bot_token', sanitize_text_field($_POST['telegram_bot_token']) );
        }
        if ( isset($_POST['telegram_chat_id']) ) {
            update_option( 'obenlo_telegram_chat_id', sanitize_text_field($_POST['telegram_chat_id']) );
        }

        wp_safe_redirect( add_query_arg( 'tab', 'settings', wp_get_referer() ) );
        exit;
    }

    public function handle_save_payment_settings() {
        if ( ! current_user_can( 'administrator' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'save_payment_settings', 'payment_settings_nonce' );

        if ( isset($_POST['payment_mode']) ) {
            update_option( 'obenlo_payment_mode', sanitize_text_field($_POST['payment_mode']) );
        }
        if ( isset($_POST['stripe_publishable']) ) {
            update_option( 'obenlo_stripe_publishable_key', sanitize_text_field($_POST['stripe_publishable']) );
        }
        if ( isset($_POST['stripe_secret']) ) {
            update_option( 'obenlo_stripe_secret_key', sanitize_text_field($_POST['stripe_secret']) );
        }
        if ( isset($_POST['paypal_id']) ) {
            update_option( 'obenlo_paypal_client_id', sanitize_text_field($_POST['paypal_id']) );
        }
        if ( isset($_POST['paypal_secret']) ) {
            update_option( 'obenlo_paypal_secret', sanitize_text_field($_POST['paypal_secret']) );
        }

        wp_safe_redirect( add_query_arg( 'tab', 'payments', wp_get_referer() ) );
        exit;
    }

    public function handle_update_user_fee() {
        if ( ! current_user_can( 'administrator' ) ) wp_die( 'Unauthorized' );
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer( 'update_user_fee_' . $user_id, 'fee_nonce' );

        if ( isset($_POST['fee_percentage']) ) {
            $fee = sanitize_text_field($_POST['fee_percentage']);
            if ( $fee === '' ) {
                delete_user_meta( $user_id, '_obenlo_host_fee_percentage' );
            } else {
                update_user_meta( $user_id, '_obenlo_host_fee_percentage', $fee );
            }
        }

        wp_safe_redirect( add_query_arg( 'tab', 'users', wp_get_referer() ) );
        exit;
    }

    private function render_bookings_tab() {
        $bookings = get_posts( array( 'post_type' => 'booking', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );

        echo '<h3>All Platform Bookings</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>ID</th><th>Listing</th><th>Total</th><th>Status</th><th>Date</th></tr>';
        foreach ( $bookings as $booking ) {
            $listing_id = get_post_meta( $booking->ID, '_obenlo_listing_id', true );
            $total = get_post_meta( $booking->ID, '_obenlo_total_price', true );
            $status = get_post_meta( $booking->ID, '_obenlo_booking_status', true );
            echo '<tr>';
            echo '<td>#' . $booking->ID . '</td>';
            echo '<td>' . get_the_title( $listing_id ) . '</td>';
            echo '<td>$' . number_format( floatval($total), 2 ) . '</td>';
            echo '<td>' . esc_html( $status ) . '</td>';
            echo '<td>' . get_the_date( '', $booking->ID ) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    private function render_communication_tab() {
        if ( isset($_GET['broadcast_sent']) ) {
            echo '<div style="background:#d4edda; padding:10px; margin-bottom:15px; border:1px solid #c3e6cb; color:#155724;">Broadcast sent successfully!</div>';
        }

        echo '<h3>Platform Communication</h3>';
        
        echo '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">';

        // Broadcast Section
        echo '<div>';
        echo '<h4>Send Global Broadcast</h4>';
        echo '<form action="' . esc_url( admin_url('admin-post.php') ) . '" method="POST" style="background:#f9f9f9; padding:25px; border-radius:12px; border:1px solid #eee;">';
        echo '<input type="hidden" name="action" value="obenlo_send_broadcast">';
        wp_nonce_field( 'send_broadcast', 'broadcast_nonce' );

        echo '<label style="display:block; margin-bottom:15px;">Recipient Group:<br>';
        echo '<select name="broadcast_role" style="width:100%; padding:10px; margin-top:5px;">';
        echo '<option value="all">All Users (Hosts & Guests)</option>';
        echo '<option value="host">Hosts Only</option>';
        echo '<option value="guest">Guests Only</option>';
        echo '</select></label>';

        echo '<label style="display:block; margin-bottom:15px;">Subject:<br><input type="text" name="broadcast_title" required style="width:100%; padding:10px; margin-top:5px;"></label>';
        echo '<label style="display:block; margin-bottom:15px;">Message (HTML allowed):<br><textarea name="broadcast_content" required style="width:100%; padding:10px; margin-top:5px; height:150px;"></textarea></label>';
        
        echo '<button type="submit" style="background:#e61e4d; color:white; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:bold; width:100%;">🚀 Send Broadcast Now</button>';
        echo '</form>';
        echo '</div>';

        // Ticket Moderation Section
        echo '<div>';
        echo '<h4>Active Support Tickets</h4>';
        $tickets = get_posts( array( 
            'post_type' => 'ticket', 
            'posts_per_page' => 20,
            'meta_key' => '_obenlo_ticket_status',
            'orderby' => 'meta_value',
            'order' => 'ASC' // Open tickets first
        ) );
        if ( empty( $tickets ) ) {
            echo '<p>No active tickets.</p>';
        } else {
            foreach ( $tickets as $ticket ) {
                $user = get_userdata( $ticket->post_author );
                $type = get_post_meta( $ticket->ID, '_obenlo_ticket_type', true );
                $status = get_post_meta( $ticket->ID, '_obenlo_ticket_status', true );
                $status_bg = ( $status === 'open' ) ? '#e61e4d' : '#333';
                
                echo '<div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:12px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.02);">';
                echo '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">';
                echo '<strong>#' . $ticket->ID . ': ' . esc_html( $ticket->post_title ) . '</strong>';
                echo '<span class="badge" style="background:' . $status_bg . '; color:#fff; padding:3px 10px; border-radius:10px; font-size:0.75em; font-weight:bold;">' . esc_html(strtoupper($status)) . '</span>';
                echo '</div>';
                echo '<div style="font-size:0.85em; color:#888; margin-bottom:10px;">';
                echo '<span style="color:#222; font-weight:600;">' . ( $user ? $user->display_name : 'Unknown' ) . '</span> • ';
                echo esc_html(ucfirst($type)) . ' • ' . get_the_date( 'M j, H:i', $ticket->ID );
                echo '</div>';
                echo '<div style="font-size:0.9em; color:#444; line-height:1.5;">' . wp_trim_words( $ticket->post_content, 12 ) . '</div>';
                echo '<div style="margin-top:15px; border-top:1px solid #f9f9f9; padding-top:10px; text-align:right;">';
                echo '<a href="' . esc_url( add_query_arg( 'ticket_id', $ticket->ID, home_url('/support') ) ) . '" style="color:#e61e4d; font-weight:bold; text-decoration:none; font-size:0.9em;">Manage Ticket & Reply →</a>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        echo '</div>';
    }

    private function render_messaging_oversight_tab() {
        echo '<h3>Platform Messaging Oversight</h3>';
        echo '<p>Monitored conversations between platform users.</p>';
        
        echo do_shortcode('[obenlo_messages_page]');
    }

    private function render_live_chat_tab() {
        global $wpdb;
        
        // Get unique chat sessions
        $sessions = $wpdb->get_results( "
            SELECT DISTINCT meta_value as session_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_obenlo_chat_session' 
            ORDER BY post_id DESC 
            LIMIT 50
        " );

        ?>
        <div style="display:grid; grid-template-columns: 300px 1fr; gap:0; background:#fff; border:1px solid #ddd; border-radius:16px; height:600px; overflow:hidden;">
            <!-- Sessions Sidebar -->
            <div style="border-right:1px solid #ddd; background:#f9f9f9; overflow-y:auto;">
                <div style="padding:20px; font-weight:bold; border-bottom:1px solid #ddd; background:#fff;">Live Sessions</div>
                <?php if ( empty($sessions) ) : ?>
                    <p style="padding:20px; color:#666;">No active chats.</p>
                <?php else : ?>
                    <?php foreach ( $sessions as $session ) : ?>
                        <div class="chat-session-item" data-id="<?php echo esc_attr($session->session_id); ?>" style="padding:15px 20px; border-bottom:1px solid #eee; cursor:pointer; hover:background:#fff;">
                            <div style="font-weight:bold; font-size:0.9em;">Guest Session</div>
                            <div style="font-size:0.75em; color:#888;"><?php echo esc_html($session->session_id); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Area -->
            <div id="live-chat-admin-area" style="display:flex; flex-direction:column; background:#fff;">
                <div id="chat-header" style="padding:15px 25px; border-bottom:1px solid #ddd; font-weight:bold;">Select a session to start chatting</div>
                <div id="chat-messages" style="flex-grow:1; padding:25px; overflow-y:auto; background:#fff;"></div>
                
                <div id="chat-input-area" style="padding:20px; border-top:1px solid #ddd; background:#f9f9f9; display:none;">
                    <form id="admin-chat-form" style="display:flex; gap:10px;">
                        <input type="text" id="admin-chat-input" placeholder="Type your response..." style="flex-grow:1; padding:12px; border:1px solid #ddd; border-radius:25px;">
                        <button type="submit" style="background:#e61e4d; color:white; border:none; padding:0 25px; border-radius:25px; font-weight:bold; cursor:pointer;">Send</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let activeSession = '';
            let lastId = 0;
            let pollInterval = null;
            
            const sessionItems = document.querySelectorAll('.chat-session-item');
            const chatHeader = document.getElementById('chat-header');
            const chatMessages = document.getElementById('chat-messages');
            const chatInputArea = document.getElementById('chat-input-area');
            const chatForm = document.getElementById('admin-chat-form');
            const chatInput = document.getElementById('admin-chat-input');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            sessionItems.forEach(item => {
                item.addEventListener('click', function() {
                    sessionItems.forEach(i => i.style.background = 'transparent');
                    this.style.background = '#fff';
                    activeSession = this.getAttribute('data-id');
                    chatHeader.textContent = 'Chatting with ' + activeSession;
                    chatMessages.innerHTML = '';
                    chatInputArea.style.display = 'block';
                    lastId = 0;
                    fetchMessages();
                    
                    if(pollInterval) clearInterval(pollInterval);
                    pollInterval = setInterval(fetchMessages, 3000);
                });
            });

            function fetchMessages() {
                if(!activeSession) return;
                
                const url = new URL(ajaxUrl);
                url.searchParams.append('action', 'obenlo_fetch_live_messages');
                url.searchParams.append('session_id', activeSession);
                url.searchParams.append('last_id', lastId);
                
                fetch(url)
                    .then(response => response.json())
                    .then(res => {
                        if(res.success && res.data.length > 0) {
                            res.data.forEach(msg => {
                                if(msg.id > lastId) {
                                    appendMessage(msg);
                                    lastId = msg.id;
                                }
                            });
                            scrollBottom();
                        }
                    })
                    .catch(e => console.error(e));
            }

            function appendMessage(msg) {
                const align = msg.is_staff ? 'margin-left:auto; background:#e61e4d; color:white; border-radius:18px 18px 2px 18px;' : 'margin-right:auto; background:#f1f1f1; color:#333; border-radius:18px 18px 18px 2px;';
                const div = document.createElement('div');
                div.style.cssText = `max-width:70%; padding:10px 15px; margin-bottom:10px; font-size:0.9em; ${align}`;
                div.innerHTML = msg.content;
                chatMessages.appendChild(div);
            }

            function scrollBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            chatForm.addEventListener('submit', function(e){
                e.preventDefault();
                const msg = chatInput.value.trim();
                if(!msg) return;
                chatInput.value = '';

                const formData = new FormData();
                formData.append('action', 'obenlo_send_live_message');
                formData.append('session_id', activeSession);
                formData.append('message', msg);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    fetchMessages();
                })
                .catch(e => console.error(e));
            });
        });
        </script>
        <?php
    }

    private function render_verifications_tab() {
        if ( ! current_user_can( 'administrator' ) ) return;

        $pending_users = get_users( array(
            'meta_key'     => 'obenlo_host_verification_status',
            'meta_value'   => 'pending',
            'role__in'     => array( 'host' ),
            'number'       => -1
        ) );

        echo '<h3>Pending Host Verifications</h3>';
        
        if ( empty( $pending_users ) ) {
            echo '<p style="padding: 20px; background: #f9f9f9; border-radius: 8px;">No pending verifications at this time.</p>';
            return;
        }

        echo '<table class="admin-table">';
        echo '<tr><th>Host</th><th>Email</th><th>ID Document</th><th>Requested Date</th><th>Actions</th></tr>';
        
        foreach ( $pending_users as $user ) {
            $doc_id = get_user_meta( $user->ID, 'obenlo_verification_doc_id', true );
            $doc_url = $doc_id ? wp_get_attachment_url( $doc_id ) : '#';
            
            echo '<tr>';
            echo '<td><strong>' . esc_html( $user->display_name ) . '</strong></td>';
            echo '<td>' . esc_html( $user->user_email ) . '</td>';
            echo '<td>';
            if ( $doc_id ) {
                echo '<a href="' . esc_url( $doc_url ) . '" target="_blank" style="display:inline-block; padding:5px 10px; background:#f0f0f1; border-radius:4px; text-decoration:none; font-size:0.85em; font-weight:600;">View Document</a>';
            } else {
                echo '<span style="color:#999;">No File</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html( $user->user_registered ) . '</td>';
            echo '<td>';
            echo '<form action="' . esc_url( admin_url('admin-post.php') ) . '" method="POST" style="display:inline-block; margin-right:10px;">';
            echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
            echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
            echo '<input type="hidden" name="status" value="verified">';
            echo '<button type="submit" class="btn-approve" style="background:none; border:none; cursor:pointer; padding:0;">Approve</button>';
            echo '</form>';
            
            echo '<form action="' . esc_url( admin_url('admin-post.php') ) . '" method="POST" style="display:inline-block;">';
            echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
            echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
            echo '<input type="hidden" name="status" value="rejected">';
            echo '<button type="submit" class="btn-reject" style="background:none; border:none; cursor:pointer; padding:0;">Reject</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
