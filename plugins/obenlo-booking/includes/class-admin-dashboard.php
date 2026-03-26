<?php
/**
 * Site Admin Dashboard Logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Admin_Dashboard
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_shortcode('obenlo_admin_dashboard', array($this, 'render_dashboard'));

        // Handle Admin Actions
        add_action('admin_post_obenlo_save_settings', array($this, 'handle_save_settings'));
        add_action('admin_post_obenlo_save_payment_settings', array($this, 'handle_save_payment_settings'));
        add_action('admin_post_obenlo_update_user_fee', array($this, 'handle_update_user_fee'));
        add_action('admin_post_obenlo_transfer_demo', array($this, 'handle_transfer_demo'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Obenlo Dash',
            'Obenlo Dash',
            'manage_options',
            'obenlo-admin-dashboard',
            array($this, 'render_dashboard_in_wp_admin'),
            'dashicons-chart-area',
            26
        );
    }

    public function render_dashboard_in_wp_admin()
    {
        echo '<div class="wrap">';
        echo $this->render_dashboard();
        echo '</div>';
    }

    public function render_dashboard()
    {
        if (!current_user_can('manage_support')) {
            return '<p>You do not have permission to access the Support Console.</p>';
        }

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

        ob_start();
?>
        <div class="obenlo-admin-dashboard" data-version="1.0.2">
            <div style="text-align: right; font-size: 0.7em; color: #ccc; margin-bottom: -20px;">v1.0.2</div>
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
                <?php
                $base_url = is_admin() ? '?page=obenlo-admin-dashboard&tab=' : '?tab=';
                if (current_user_can('administrator')): ?>
                    <a href="<?php echo $base_url; ?>overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">Overview</a>
                    <a href="<?php echo $base_url; ?>listings" class="<?php echo $tab === 'listings' ? 'active' : ''; ?>">Listings</a>
                    <a href="<?php echo $base_url; ?>users" class="<?php echo $tab === 'users' ? 'active' : ''; ?>">Users</a>
                    <a href="<?php echo $base_url; ?>verifications" class="<?php echo $tab === 'verifications' ? 'active' : ''; ?>">Verifications</a>
                    <a href="<?php echo $base_url; ?>bookings" class="<?php echo $tab === 'bookings' ? 'active' : ''; ?>">Bookings</a>
                    <a href="<?php echo $base_url; ?>payments" class="<?php echo $tab === 'payments' ? 'active' : ''; ?>">Payments</a>
                    <a href="<?php echo $base_url; ?>messaging" class="<?php echo $tab === 'messaging' ? 'active' : ''; ?>">Messaging</a>
                <?php
        endif; ?>
                <a href="<?php echo $base_url; ?>communication" class="<?php echo $tab === 'communication' ? 'active' : ''; ?>">Support Tickets</a>
                <?php if (current_user_can('administrator')): ?>
                    <a href="<?php echo $base_url; ?>settings" class="<?php echo $tab === 'settings' ? 'active' : ''; ?>">Settings</a>
                    <a href="<?php echo $base_url; ?>demo_manager" class="<?php echo $tab === 'demo_manager' ? 'active' : ''; ?>">Demo Manager</a>
                <?php endif; ?>
            </div>

            <?php if (in_array($tab, array('communication', 'bookings', 'verifications'))): ?>
            <script>
                // Auto-refresh the current admin tab every 60 seconds to ensure real-time data
                setTimeout(function() {
                    window.location.reload();
                }, 60000);
            </script>
            <div style="text-align: right; font-size: 0.8em; color: #999; margin-bottom: 10px;">
                <span class="dashicons dashicons-update" style="font-size: 14px; line-height: 1;"></span> Auto-refreshing every 60s
            </div>
            <?php
        endif; ?>

            <?php
        switch ($tab) {
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
            case 'settings':
                $this->render_settings_tab();
                break;
            case 'demo_manager':
                $this->render_demo_manager_tab();
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

    private function render_overview_tab()
    {
        $user_counts = count_users();
        $hosts = isset($user_counts['avail_roles']['host']) ? $user_counts['avail_roles']['host'] : 0;
        $guests = isset($user_counts['avail_roles']['guest']) ? $user_counts['avail_roles']['guest'] : 0;

        $listings_count = wp_count_posts('listing')->publish;

        // Revenue Calculation
        $bookings = get_posts(array('post_type' => 'booking', 'posts_per_page' => -1));
        $total_revenue = 0;
        foreach ($bookings as $booking) {
            $total_revenue += floatval(get_post_meta($booking->ID, '_obenlo_total_price', true));
        }

?>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?php echo number_format($total_revenue, 2); ?></span>
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3>Recent Platform Events</h3>
                <div style="background:#fff; border:1px solid #eee; border-radius:12px; padding:20px;">
                    <?php
                    $recent_events = get_posts(array(
                        'post_type' => array('booking', 'listing', 'ticket'),
                        'posts_per_page' => 10,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    if (empty($recent_events)): ?>
                        <p style="color:#999;">No recent activity found.</p>
                    <?php else: ?>
                        <ul style="list-style:none; padding:0; margin:0;">
                        <?php foreach($recent_events as $event): 
                            $icon = '📅';
                            $label = 'Booking';
                            if($event->post_type === 'listing') { $icon = '🏠'; $label = 'New Listing'; }
                            if($event->post_type === 'ticket') { $icon = '✉️'; $label = 'Support Ticket'; }
                        ?>
                            <li style="padding:12px 0; border-bottom:1px solid #f9f9f9; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <span style="margin-right:10px;"><?php echo $icon; ?></span>
                                    <strong><?php echo esc_html($label); ?>:</strong> <?php echo esc_html($event->post_title); ?>
                                </div>
                                <span style="font-size:0.8rem; color:#aaa;"><?php echo human_time_diff(get_the_time('U', $event->ID), current_time('timestamp')) . ' ago'; ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h3>New User Registrations</h3>
                <div style="background:#fff; border:1px solid #eee; border-radius:12px; padding:20px;">
                    <?php
                    $recent_users = get_users(array(
                        'orderby' => 'registered',
                        'order' => 'DESC',
                        'number' => 10
                    ));
                    ?>
                    <ul style="list-style:none; padding:0; margin:0;">
                    <?php foreach($recent_users as $user): ?>
                        <li style="padding:12px 0; border-bottom:1px solid #f9f9f9; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="color:#222;"><?php echo esc_html($user->display_name); ?></strong>
                                <span class="badge" style="background:#eee; color:#666; margin-left:8px;"><?php echo ucfirst($user->roles[0]); ?></span>
                            </div>
                            <span style="font-size:0.8rem; color:#aaa;"><?php echo date('M j, H:i', strtotime($user->user_registered)); ?></span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_listings_tab()
    {
        $listings = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'pending', 'draft'),
            'suppress_filters' => false,
        ));

        echo '<h3>Manage All Listings</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>Title</th><th>Host</th><th>Status</th><th>Price</th><th>Actions</th></tr>';
        foreach ($listings as $listing) {
            $host = get_userdata($listing->post_author);
            $price = get_post_meta($listing->ID, '_obenlo_price', true);
            echo '<tr>';
            echo '<td><strong>' . esc_html($listing->post_title) . '</strong></td>';
            echo '<td>' . ($host ? esc_html($host->display_name) : 'Unknown') . '</td>';
            echo '<td>' . esc_html(ucfirst($listing->post_status)) . '</td>';
            echo '<td>$' . esc_html($price) . '</td>';
            echo '<td>';
            echo '<a href="' . get_permalink($listing->ID) . '" target="_blank">View</a> | ';
            echo '<a href="#" class="btn-reject">Trash</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    private function render_users_tab()
    {
        $users = get_users(array('role__in' => array('host', 'guest')));

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
            <?php foreach ($users as $user):
            $status = Obenlo_Booking_Host_Verification::get_status($user->ID);
            $v_badge = 'badge-info';
            if ($status === 'verified')
                $v_badge = 'badge-success';
            if ($status === 'rejected')
                $v_badge = 'badge-danger';
            if ($status === 'pending')
                $v_badge = 'badge-warning';
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
                        <?php if (in_array('host', $user->roles)): ?>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="action" value="obenlo_update_user_fee">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <?php wp_nonce_field('update_user_fee_' . $user->ID, 'fee_nonce'); ?>
                                <input type="number" name="fee_percentage" value="<?php echo esc_attr(get_user_meta($user->ID, '_obenlo_host_fee_percentage', true)); ?>" placeholder="Global" step="0.1" style="width:70px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                                <button type="submit" style="padding:5px 10px; background:#222; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:0.8em;">Save Fee</button>
                            </form>
                        <?php
            else: ?>
                            <span style="color:#ccc;">N/A</span>
                        <?php
            endif; ?>
                        <a href="mailto:<?php echo esc_attr($user->user_email); ?>" style="margin-left: 10px;">Contact</a>
                    </td>
                </tr>
            <?php
        endforeach; ?>
        <?php
        echo '</table>';
    }

    private function render_settings_tab()
    {
        $global_fee = get_option('obenlo_global_platform_fee', '10');
        $info_email = get_option('obenlo_info_email', 'info@obenlo.com');
        $admin_email = get_option('obenlo_admin_email', 'admin@obenlo.com');
        $from_name = get_option('obenlo_mail_from_name', 'Obenlo');
        $ga4_id = get_option('obenlo_google_analytics_id', '');
        $pixel_id = get_option('obenlo_meta_pixel_id', '');

        if (isset($_GET['sync_status'])) {
            $color = ($_GET['sync_status'] === 'success') ? '#155724' : '#721c24';
            $bg = ($_GET['sync_status'] === 'success') ? '#d4edda' : '#f8d7da';
            $border = ($_GET['sync_status'] === 'success') ? '#c3e6cb' : '#f5c6cb';
            echo '<div style="background:' . $bg . '; color:' . $color . '; border:1px solid ' . $border . '; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600;">' . esc_html(urldecode($_GET['sync_msg'])) . '</div>';
        }
        if (isset($_GET['test_status'])) {
            $color = ($_GET['test_status'] === 'success') ? '#155724' : '#721c24';
            $bg = ($_GET['test_status'] === 'success') ? '#d4edda' : '#f8d7da';
            $border = ($_GET['test_status'] === 'success') ? '#c3e6cb' : '#f5c6cb';
            echo '<div style="background:' . $bg . '; color:' . $color . '; border:1px solid ' . $border . '; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600;">' . esc_html(urldecode($_GET['test_msg'])) . '</div>';
        }
?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_save_settings">
                <?php wp_nonce_field('save_settings', 'settings_nonce'); ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">
                    <div>
                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px;">Platform Configuration</h4>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Global Platform Fee (%)</label>
                            <p style="font-size:0.8em; color:#666; margin-bottom:10px;">Default percentage taken from each booking.</p>
                            <input type="number" name="global_fee" value="<?php echo esc_attr($global_fee); ?>" step="0.1" required style="width:100px; padding:10px; border:1px solid #ddd; border-radius:8px;"> <span style="font-weight:600;">%</span>
                        </div>

                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px; margin-top:40px;">Email & Notifications</h4>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Public From Name</label>
                            <input type="text" name="mail_from_name" value="<?php echo esc_attr($from_name); ?>" placeholder="Obenlo" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">System Reply-To Email</label>
                            <input type="email" name="info_email" value="<?php echo esc_attr($info_email); ?>" placeholder="info@obenlo.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Internal Admin Notifications</label>
                            <input type="email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" placeholder="admin@obenlo.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                    </div>

                    <div>
                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px;">Tracking & Analytics</h4>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Google Analytics 4 ID</label>
                            <input type="text" name="ga4_id" value="<?php echo esc_attr($ga4_id); ?>" placeholder="G-XXXXXXXXXX" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Meta (Facebook) Pixel ID</label>
                            <input type="text" name="pixel_id" value="<?php echo esc_attr($pixel_id); ?>" placeholder="1234567890" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>

                        <div style="background:#fff3cd; padding:20px; border-radius:12px; border:1px solid #ffeeba; margin-top:40px;">
                            <h4 style="margin-top:0; color:#856404;">Emergency Tools</h4>
                            <p style="font-size:0.85em; color:#856404; margin-bottom:15px;">Use these only if the database fails during an update.</p>
                            <button type="button" id="force-install-db" style="background:#dc3545; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:700; font-size:1em; width:100%;">⚙️ Reinstall Database Tables</button>
                            <script>
                            document.getElementById('force-install-db').addEventListener('click', function() {
                                if(!confirm('This will attempt to run the database table creation scripts. Continue?')) return;
                                const btn = this;
                                btn.textContent = 'Installing...';
                                const formData = new FormData();
                                formData.append('action', 'obenlo_force_install_db');
                                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                    method: 'POST', body: formData
                                }).then(r => r.json()).then(res => {
                                    alert(res.data);
                                    btn.textContent = '⚙️ Reinstall Database Tables';
                                }).catch(err => {
                                    alert('Error installing tables. Check console.');
                                    console.error(err);
                                    btn.textContent = '⚙️ Reinstall Database Tables';
                                });
                            });
                            </script>
                        </div>
                    </div>
                </div>

                <div style="margin-top:40px; border-top:2px solid #eee; padding-top:20px;">
                    <button type="submit" name="save_obenlo_settings" value="1" style="background:#e61e4d; color:#fff; border:none; padding:15px 40px; border-radius:12px; cursor:pointer; font-weight:800; font-size:1.1em; width:100%; box-shadow:0 4px 10px rgba(230,30,77,0.2);">Save All Site Settings</button>
                </div>
            </form>
        </div>
        <?php
    }

    private function render_payments_tab()
    {
        // Payment Keys
        $payment_mode = get_option('obenlo_payment_mode', 'sandbox');
        $stripe_publishable = get_option('obenlo_stripe_publishable_key', '');
        $stripe_secret = get_option('obenlo_stripe_secret_key', '');
        $paypal_client_id = get_option('obenlo_paypal_client_id', '');
        $paypal_secret = get_option('obenlo_paypal_secret', '');
?>
        <h3>Payment Gateway Configuration</h3>
        <div style="background:#fff; border:1px solid #eee; padding:30px; border-radius:12px; max-width:600px;">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_save_payment_settings">
                <?php wp_nonce_field('save_payment_settings', 'payment_settings_nonce'); ?>
                
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
                    <input type="text" name="stripe_publishable" value="<?php echo esc_attr($stripe_publishable); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                    
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key</label>
                    <input type="password" name="stripe_secret" value="<?php echo esc_attr($stripe_secret); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <h4 style="margin-bottom:10px;">PayPal Configuration</h4>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID</label>
                    <input type="text" name="paypal_id" value="<?php echo esc_attr($paypal_client_id); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                    
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret</label>
                    <input type="password" name="paypal_secret" value="<?php echo esc_attr($paypal_secret); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:700;">Save Payment Settings</button>
            </form>
        </div>
        <?php
    }

    public function handle_save_settings()
    {
        error_log('Obenlo Settings: Request received.');
        if (!current_user_can('administrator')) {
            error_log('Obenlo Settings: Unauthorized access attempt.');
            obenlo_redirect_with_error('unauthorized');
        }

        check_admin_referer('save_settings', 'settings_nonce');
        error_log('Obenlo Settings: Nonce verified.');

        $redirect_url = add_query_arg('tab', 'settings', wp_get_referer());

        if (isset($_POST['global_fee'])) {
            update_option('obenlo_global_platform_fee', sanitize_text_field($_POST['global_fee']));
        }
        if (isset($_POST['info_email'])) {
            update_option('obenlo_info_email', sanitize_email($_POST['info_email']));
        }
        if (isset($_POST['admin_email'])) {
            update_option('obenlo_admin_email', sanitize_email($_POST['admin_email']));
        }
        if (isset($_POST['mail_from_name'])) {
            update_option('obenlo_mail_from_name', sanitize_text_field($_POST['mail_from_name']));
        }
        if (isset($_POST['ga4_id'])) {
            update_option('obenlo_google_analytics_id', sanitize_text_field($_POST['ga4_id']));
        }
        if (isset($_POST['pixel_id'])) {
            update_option('obenlo_meta_pixel_id', sanitize_text_field($_POST['pixel_id']));
        }



        error_log('Obenlo Settings: Redirecting to ' . $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    public function handle_save_payment_settings()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }
        check_admin_referer('save_payment_settings', 'payment_settings_nonce');

        if (isset($_POST['payment_mode'])) {
            update_option('obenlo_payment_mode', sanitize_text_field($_POST['payment_mode']));
        }
        if (isset($_POST['stripe_publishable'])) {
            update_option('obenlo_stripe_publishable_key', sanitize_text_field($_POST['stripe_publishable']));
        }
        if (isset($_POST['stripe_secret'])) {
            update_option('obenlo_stripe_secret_key', sanitize_text_field($_POST['stripe_secret']));
        }
        if (isset($_POST['paypal_id'])) {
            update_option('obenlo_paypal_client_id', sanitize_text_field($_POST['paypal_id']));
        }
        if (isset($_POST['paypal_secret'])) {
            update_option('obenlo_paypal_secret', sanitize_text_field($_POST['paypal_secret']));
        }

        wp_safe_redirect(add_query_arg('tab', 'payments', wp_get_referer()));
        exit;
    }

    public function handle_update_user_fee()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer('update_user_fee_' . $user_id, 'fee_nonce');

        if (isset($_POST['fee_percentage'])) {
            $fee = sanitize_text_field($_POST['fee_percentage']);
            if ($fee === '') {
                delete_user_meta($user_id, '_obenlo_host_fee_percentage');
            }
            else {
                update_user_meta($user_id, '_obenlo_host_fee_percentage', $fee);
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'users', wp_get_referer()));
        exit;
    }

    private function render_bookings_tab()
    {
        $bookings = get_posts(array('post_type' => 'booking', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC'));

        echo '<h3>All Platform Bookings</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>ID</th><th>Listing</th><th>Total</th><th>Status</th><th>Date</th></tr>';
        foreach ($bookings as $booking) {
            $listing_id = get_post_meta($booking->ID, '_obenlo_listing_id', true);
            $total = get_post_meta($booking->ID, '_obenlo_total_price', true);
            $status = get_post_meta($booking->ID, '_obenlo_booking_status', true);
            echo '<tr>';
            echo '<td>#' . $booking->ID . '</td>';
            echo '<td>' . get_the_title($listing_id) . '</td>';
            echo '<td>$' . number_format(floatval($total), 2) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td>' . get_the_date('', $booking->ID) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    private function render_communication_tab()
    {
        if (isset($_GET['broadcast_sent'])) {
            echo '<div style="background:#d4edda; padding:10px; margin-bottom:15px; border:1px solid #c3e6cb; color:#155724;">Broadcast sent successfully!</div>';
        }

        echo '<h3>Platform Communication</h3>';

        echo '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">';

        // Broadcast Section
        echo '<div>';
        echo '<h4>Send Global Broadcast</h4>';
        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="background:#f9f9f9; padding:25px; border-radius:12px; border:1px solid #eee;">';
        echo '<input type="hidden" name="action" value="obenlo_send_broadcast">';
        wp_nonce_field('send_broadcast', 'broadcast_nonce');

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
        $tickets = get_posts(array(
            'post_type' => 'ticket',
            'posts_per_page' => -1,
            'suppress_filters' => false,
            'meta_key' => '_obenlo_ticket_status',
            'orderby' => 'meta_value',
            'order' => 'ASC' // Open tickets first
        ));
        if (empty($tickets)) {
            echo '<p>No active tickets.</p>';
        }
        else {
            foreach ($tickets as $ticket) {
                $user = get_userdata($ticket->post_author);
                $type = get_post_meta($ticket->ID, '_obenlo_ticket_type', true);
                $status = get_post_meta($ticket->ID, '_obenlo_ticket_status', true);
                $status_bg = ($status === 'open') ? '#e61e4d' : '#333';

                echo '<div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:12px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.02);">';
                echo '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">';
                echo '<strong>#' . $ticket->ID . ': ' . esc_html($ticket->post_title) . '</strong>';
                echo '<span class="badge" style="background:' . $status_bg . '; color:#fff; padding:3px 10px; border-radius:10px; font-size:0.75em; font-weight:bold;">' . esc_html(strtoupper($status)) . '</span>';
                echo '</div>';
                echo '<div style="font-size:0.85em; color:#888; margin-bottom:10px;">';
                echo '<span style="color:#222; font-weight:600;">' . ($user ? $user->display_name : 'Unknown') . '</span> • ';
                echo esc_html(ucfirst($type)) . ' • ' . get_the_date('M j, H:i', $ticket->ID);
                echo '</div>';
                echo '<div style="font-size:0.9em; color:#444; line-height:1.5;">' . wp_trim_words($ticket->post_content, 12) . '</div>';
                echo '<div style="margin-top:15px; border-top:1px solid #f9f9f9; padding-top:10px; text-align:right;">';
                echo '<a href="' . esc_url(add_query_arg('ticket_id', $ticket->ID, home_url('/support'))) . '" style="color:#e61e4d; font-weight:bold; text-decoration:none; font-size:0.9em;">Manage Ticket & Reply →</a>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        echo '</div>';
    }

    private function render_messaging_oversight_tab()
    {
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
        echo '<h3>Platform Messaging Oversight</h3>';
        echo '<div style="background:#fff4f4; border:1px solid #ffcccc; padding:5px 15px; border-radius:30px; font-size:0.8rem; color:#e61e4d; font-weight:700;">Admin View Mode</div>';
        echo '</div>';
        echo '<p style="color:#666; margin-bottom:30px;">Monitor all conversations between platform users for quality control and dispute resolution.</p>';

        echo do_shortcode('[obenlo_messages_page oversight="1"]');
    }

    private function render_live_chat_tab()
    {
        global $wpdb;

        // Get unique chat sessions
        $sessions = $wpdb->get_results("
            SELECT DISTINCT meta_value as session_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_obenlo_chat_session' 
            ORDER BY post_id DESC 
            LIMIT 50
        ");

?>
        <div style="display:grid; grid-template-columns: 300px 1fr; gap:0; background:#fff; border:1px solid #ddd; border-radius:16px; height:600px; overflow:hidden;">
            <!-- Sessions Sidebar -->
            <div style="border-right:1px solid #ddd; background:#f9f9f9; overflow-y:auto;">
                <div style="padding:20px; font-weight:bold; border-bottom:1px solid #ddd; background:#fff;">Live Sessions</div>
                <?php if (empty($sessions)): ?>
                    <p style="padding:20px; color:#666;">No active chats.</p>
                <?php
        else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <?php
                $display_name = $session->session_id;
                if (strpos($display_name, 'guest_') === 0) {
                    $display_name = 'Guest (' . substr($display_name, 6, 4) . ')';
                }
?>
                        <div class="chat-session-item" data-id="<?php echo esc_attr($session->session_id); ?>" data-name="<?php echo esc_attr($display_name); ?>" style="padding:15px 20px; border-bottom:1px solid #eee; cursor:pointer; transition: background 0.2s;">
                            <div style="font-weight:bold; font-size:0.9em;"><?php echo esc_html($display_name); ?></div>
                            <div style="font-size:0.75em; color:#888;"><?php echo esc_html(strpos($session->session_id, 'guest_') === 0 ? 'Anonymous' : 'Registered'); ?></div>
                        </div>
                    <?php
            endforeach; ?>
                <?php
        endif; ?>
            </div>

            <!-- Chat Area -->
            <div id="live-chat-admin-area" style="display:flex; flex-direction:column; background:#fff;">
                <div id="chat-header" style="padding:15px 25px; border-bottom:1px solid #ddd; font-weight:bold; display:flex; justify-content:space-between; align-items:center;">
                    <span id="chat-header-title">Select a session to start chatting</span>
                    <button id="admin-chat-delete" style="display:none; color: #a00; border: 1px solid #a00; border-radius: 4px; padding: 4px 12px; background: transparent; cursor: pointer; font-size: 0.85em;">Delete Chat</button>
                </div>
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
            const chatHeaderTitle = document.getElementById('chat-header-title');
            const chatDeleteBtn = document.getElementById('admin-chat-delete');
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
                    const sessionName = this.getAttribute('data-name');
                    chatHeaderTitle.textContent = 'Chatting with ' + sessionName;
                    chatDeleteBtn.style.display = 'block';
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

            if (chatDeleteBtn) {
                chatDeleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!activeSession) return;
                    if (!confirm('Are you sure you want to delete this entire chat history? This cannot be undone.')) return;

                    const formData = new FormData();
                    formData.append('action', 'obenlo_admin_delete_chat');
                    formData.append('session_id', activeSession);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert('Error deleting chat: ' + (res.data || 'Unknown error'));
                        }
                    })
                    .catch(e => console.error(e));
                });
            }
        });
        </script>
        <?php
    }

    private function render_demo_manager_tab()
    {
        $demos = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_key' => '_obenlo_is_demo',
            'meta_value' => 'yes',
            'post_status' => array('publish', 'pending', 'draft')
        ));

        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">';
        echo '<div>';
        echo '<h3 style="margin-bottom:5px;">Demo Listing Manager</h3>';
        echo '<p style="color:#666; margin:0;">Create high-quality demo listings as an admin, then transfer them to new hosts to jumpstart their profile.</p>';
        echo '</div>';
        echo '<a href="' . esc_url(home_url('/host-dashboard?action=add&demo=1')) . '" style="background:#e61e4d; color:#fff; text-decoration:none; padding:10px 20px; border-radius:8px; font-weight:bold;">+ Create Demo Listing</a>';
        echo '</div>';
        
        echo '<table class="admin-table">';
        echo '<tr><th>Preview Name</th><th>Demo Bio</th><th>Location</th><th>Actions</th></tr>';
        
        if (empty($demos)) {
            echo '<tr><td colspan="4" style="padding:40px; text-align:center; color:#999;">No demo listings created. <a href="' . esc_url(home_url('/host-dashboard?action=add&demo=1')) . '" style="color:#e61e4d; font-weight:bold;">Create one now</a></td></tr>';
        } else {
            foreach ($demos as $demo) {
                $d_name = get_post_meta($demo->ID, '_obenlo_demo_host_name', true);
                $d_bio = get_post_meta($demo->ID, '_obenlo_demo_host_bio', true);
                $d_loc = get_post_meta($demo->ID, '_obenlo_demo_host_location', true);

                echo '<tr>';
                echo '<td><strong>' . esc_html($d_name) . '</strong><br><small>' . esc_html($demo->post_title) . '</small></td>';
                echo '<td style="max-width:300px; font-size:0.85rem;">' . wp_trim_words($d_bio, 15) . '</td>';
                echo '<td>' . esc_html($d_loc) . '</td>';
                echo '<td>';
                echo '<div style="display:flex; gap:15px; align-items:center;">';
                echo '<a href="' . get_permalink($demo->ID) . '" target="_blank" style="color:#333; font-weight:600; text-decoration:none;">View</a>';
                echo '<a href="' . esc_url(home_url("/host-dashboard?action=edit&listing_id={$demo->ID}&demo=1")) . '" style="color:#e61e4d; font-weight:700; text-decoration:none;">Edit Setup</a>';
                
                // Transfer Form
                echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:flex; gap:5px; margin:0;" onsubmit="return confirm(\'Are you sure you want to transfer this demo to a real host? This will move all demo data to their profile.\')">';
                echo '<input type="hidden" name="action" value="obenlo_transfer_demo">';
                echo '<input type="hidden" name="listing_id" value="' . $demo->ID . '">';
                wp_nonce_field('transfer_demo_' . $demo->ID, 'transfer_nonce');
                
                $users = get_users(array('role' => 'host', 'number' => 20));
                echo '<select name="target_user_id" required style="font-size:0.8rem; padding:4px;">';
                echo '<option value="">Select Host...</option>';
                foreach($users as $user) {
                    if ($user->user_login === 'demo') continue;
                    echo '<option value="' . $user->ID . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_login) . ')</option>';
                }
                echo '</select>';
                
                echo '<button type="submit" style="background:#222; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:0.8em; font-weight:700;">Transfer</button>';
                echo '</form>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    public function handle_transfer_demo()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }
        
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $user_id = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;
        
        check_admin_referer('transfer_demo_' . $listing_id, 'transfer_nonce');

        if ($listing_id && $user_id) {
            $listing = get_post($listing_id);
            if ($listing && get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes') {
                
                // 1. Reassign Author
                wp_update_post(array(
                    'ID' => $listing_id,
                    'post_author' => $user_id
                ));

                // 2. Migrate Meta to User
                $d_name = get_post_meta($listing_id, '_obenlo_demo_host_name', true);
                $d_bio = get_post_meta($listing_id, '_obenlo_demo_host_bio', true);
                $d_loc = get_post_meta($listing_id, '_obenlo_demo_host_location', true);
                $d_tag = get_post_meta($listing_id, '_obenlo_demo_host_tagline', true);
                $d_insta = get_post_meta($listing_id, '_obenlo_demo_host_instagram', true);
                $d_fb = get_post_meta($listing_id, '_obenlo_demo_host_facebook', true);

                if ($d_name) update_user_meta($user_id, 'obenlo_store_name', $d_name);
                if ($d_bio) update_user_meta($user_id, 'obenlo_store_description', $d_bio);
                if ($d_loc) update_user_meta($user_id, 'obenlo_store_location', $d_loc);
                if ($d_tag) update_user_meta($user_id, 'obenlo_store_tagline', $d_tag);
                if ($d_insta) update_user_meta($user_id, 'obenlo_instagram', $d_insta);
                if ($d_fb) update_user_meta($user_id, 'obenlo_facebook', $d_fb);

                // 3. Clean up Demo flags
                delete_post_meta($listing_id, '_obenlo_is_demo');
                delete_post_meta($listing_id, '_obenlo_demo_host_name');
                delete_post_meta($listing_id, '_obenlo_demo_host_bio');
                delete_post_meta($listing_id, '_obenlo_demo_host_location');
                delete_post_meta($listing_id, '_obenlo_demo_host_tagline');
                delete_post_meta($listing_id, '_obenlo_demo_host_instagram');
                delete_post_meta($listing_id, '_obenlo_demo_host_facebook');
                
                // Clear any restricted mode cache for the user if applicable
                clean_user_cache($user_id);
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'demo_manager', wp_get_referer()));
        exit;
    }

    private function render_verifications_tab()
    {
        echo '<h3>Host Verification Requests</h3>';
        
        $users = get_users(array(
            'meta_key' => 'obenlo_host_verification_status',
            'meta_value' => array('pending', 'verified', 'rejected'),
            'meta_compare' => 'IN'
        ));
        
        echo '<table class="admin-table">';
        echo '<tr><th>Host</th><th>Status</th><th>Document</th><th>Actions</th></tr>';
        
        if (empty($users)) {
            echo '<tr><td colspan="4" style="text-align:center; padding:30px;">No verification requests found.</td></tr>';
        } else {
            foreach ($users as $user) {
                $status = get_user_meta($user->ID, 'obenlo_host_verification_status', true);
                if (!$status) $status = 'pending';

                $doc_id = get_user_meta($user->ID, 'obenlo_verification_doc_id', true);
                $doc_url = $doc_id ? wp_get_attachment_url($doc_id) : '';
                
                $status_bg = '#fef9c3'; $status_color = '#854d0e';
                if ($status === 'verified') { $status_bg = '#dcfce7'; $status_color = '#166534'; }
                if ($status === 'rejected') { $status_bg = '#fee2e2'; $status_color = '#991b1b'; }

                echo '<tr>';
                echo '<td><strong>' . esc_html($user->display_name) . '</strong><br><small><a href="mailto:'.esc_attr($user->user_email).'" style="color:#666;">' . esc_html($user->user_email) . '</a></small></td>';
                echo '<td><span style="background:'.$status_bg.'; color:'.$status_color.'; padding:4px 10px; border-radius:12px; font-weight:700; font-size:0.8rem; text-transform:uppercase;">' . esc_html($status) . '</span></td>';
                
                echo '<td>';
                if ($doc_url) {
                    echo '<a href="' . esc_url($doc_url) . '" target="_blank" style="color:#1d4ed8; font-weight:600; text-decoration:none;">📄 View Document</a>';
                } else {
                    echo '<span style="color:#999; font-style:italic;">No active document</span>';
                }
                echo '</td>';
                
                echo '<td>';
                // Only show approve/reject for pending, but allow overriding if needed
                if ($status === 'pending' || $status === 'rejected') {
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline-block; margin-right:10px;">';
                    echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
                    echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
                    echo '<input type="hidden" name="status" value="verified">';
                    echo '<button type="submit" style="background:#10b981; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:0.8rem;">Approve</button>';
                    echo '</form>';
                }
                if ($status === 'pending' || $status === 'verified') {
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline-block;">';
                    echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
                    echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
                    echo '<input type="hidden" name="status" value="rejected">';
                    echo '<button type="submit" style="background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:0.8rem;">Reject</button>';
                    echo '</form>';
                }
                echo '</td>';
                
                echo '</tr>';
            }
        }
        
        echo '</table>';
    }
}
