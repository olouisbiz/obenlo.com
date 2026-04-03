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
        add_action('admin_post_obenlo_save_translation', array($this, 'handle_save_translation'));
        add_action('admin_post_obenlo_admin_review_action', array($this, 'handle_review_action'));
        add_action('admin_post_obenlo_toggle_listing_suspension', array($this, 'handle_toggle_listing_suspension'));
        add_action('admin_post_obenlo_toggle_user_suspension', array($this, 'handle_toggle_user_suspension'));
        add_action('admin_post_obenlo_trash_listing', array($this, 'handle_trash_listing'));
        add_action('admin_post_obenlo_admin_testimony_action', array($this, 'handle_testimony_action'));
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
                    <a href="<?php echo $base_url; ?>reviews" class="<?php echo $tab === 'reviews' ? 'active' : ''; ?>">Reviews</a>
                    <a href="<?php echo $base_url; ?>testimonies" class="<?php echo $tab === 'testimonies' ? 'active' : ''; ?>">Testimonies</a>
                    <a href="<?php echo $base_url; ?>messaging" class="<?php echo $tab === 'messaging' ? 'active' : ''; ?>">Messaging</a>
                <?php
        endif; ?>
                <a href="<?php echo $base_url; ?>communication" class="<?php echo $tab === 'communication' ? 'active' : ''; ?>">Support Tickets</a>
                <?php if (current_user_can('administrator')): ?>
                    <a href="<?php echo $base_url; ?>settings" class="<?php echo $tab === 'settings' ? 'active' : ''; ?>">Settings</a>
                    <a href="<?php echo $base_url; ?>translation" class="<?php echo $tab === 'translation' ? 'active' : ''; ?>">Translation</a>
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
            case 'translation':
                $this->render_translation_tab();
                break;
            case 'demo_manager':
                $this->render_demo_manager_tab();
                break;
            case 'reviews':
                $this->render_reviews_tab();
                break;
            case 'testimonies':
                $this->render_testimonies_tab();
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
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

        // Query Args
        $booking_args = array('post_type' => 'booking', 'posts_per_page' => -1, 'post_status' => 'any');
        $user_args = array('role__in' => array('host', 'guest'), 'fields' => 'ID');
        
        if ($start_date || $end_date) {
            $date_query = array('inclusive' => true);
            if ($start_date) $date_query['after'] = $start_date;
            if ($end_date) $date_query['before'] = $end_date;
            
            $booking_args['date_query'] = $date_query;
            $user_args['date_query'] = $date_query;
        }

        $bookings = get_posts($booking_args);
        $total_revenue = 0;
        $site_revenue = 0;
        $confirmed_count = 0;
        $pending_count = 0;

        // Chart Data Prep (Last 30 days or selected range)
        $chart_data = array();
        
        foreach ($bookings as $booking) {
            $price = floatval(get_post_meta($booking->ID, '_obenlo_total_price', true));
            $comm = floatval(get_post_meta($booking->ID, '_obenlo_booking_commission_amount', true));
            $status = get_post_meta($booking->ID, '_obenlo_booking_status', true);
            
            $total_revenue += $price;
            $site_revenue += $comm;
            
            if ($status === 'confirmed') $confirmed_count++;
            if ($status === 'pending_payment' || $status === 'pending') $pending_count++;

            $date = get_the_date('Y-m-d', $booking);
            $chart_data[$date] = ($chart_data[$date] ?? 0) + 1;
        }
        ksort($chart_data);

        // User Counts
        $filtered_users = get_users($user_args);
        $new_users_count = count($filtered_users);

        // All-time totals for reference
        $all_hosts = count_users()['avail_roles']['host'] ?? 0;
        $all_guests = count_users()['avail_roles']['guest'] ?? 0;
?>
        <!-- Date Filter Form -->
        <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px; display:flex; gap:20px; align-items:flex-end;">
            <form action="" method="GET" style="display:flex; gap:20px; align-items:flex-end; width:100%;">
                <input type="hidden" name="page" value="obenlo-booking">
                <input type="hidden" name="tab" value="overview">
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">End Date</label>
                    <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <button type="submit" style="background:#222; color:#fff; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Filter Stats</button>
                <a href="?page=obenlo-booking&tab=overview" style="padding:10px; color:#666; font-size:0.9rem;">Reset</a>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span class="stat-label" style="margin:0;">Total GTV</span>
                    <span style="font-size:1.2rem;">💰</span>
                </div>
                <span class="stat-value">$<?php echo number_format($total_revenue, 2); ?></span>
                <div style="font-size:0.75rem; color:#888; margin-top:5px;"><?php echo count($bookings); ?> total bookings</div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span class="stat-label" style="margin:0;">Site Earnings</span>
                    <span style="font-size:1.2rem;">🏦</span>
                </div>
                <span class="stat-value" style="color:#10b981;">$<?php echo number_format($site_revenue, 2); ?></span>
                <div style="font-size:0.75rem; color:#888; margin-top:5px;">Platform commission</div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span class="stat-label" style="margin:0;">Booking Success</span>
                    <span style="font-size:1.2rem;">✅</span>
                </div>
                <span class="stat-value"><?php echo $confirmed_count; ?></span>
                <div style="font-size:0.75rem; color:#888; margin-top:5px;"><?php echo $pending_count; ?> pending payment</div>
            </div>
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span class="stat-label" style="margin:0;">User Growth</span>
                    <span style="font-size:1.2rem;">👥</span>
                </div>
                <span class="stat-value"><?php echo $new_users_count; ?></span>
                <div style="font-size:0.75rem; color:#888; margin-top:5px;">New regs in period</div>
            </div>
        </div>

        <!-- Chart Section -->
        <div style="background:#fff; border:1px solid #eee; border-radius:15px; padding:30px; margin-bottom:40px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0;">Booking Performance</h3>
                <div style="font-size:0.8rem; color:#888;">Showing: <?php echo $start_date ? esc_html($start_date) : 'Last 30 Days'; ?> to <?php echo $end_date ? esc_html($end_date) : 'Today'; ?></div>
            </div>
            
            <?php
            // If no search dates provided, default to last 30 days for the chart labels
            $chart_start = $start_date ? new DateTime($start_date) : new DateTime('-30 days');
            $chart_end = $end_date ? new DateTime($end_date) : new DateTime('today');
            
            // Generate all dates in range to fill with 0s
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($chart_start, $interval, $chart_end->modify('+1 day'));
            
            $final_chart_data = array();
            foreach ($period as $dt) {
                $final_chart_data[$dt->format('Y-m-d')] = 0;
            }
            
            // Merge actual data
            foreach($chart_data as $date => $count) {
                if (isset($final_chart_data[$date])) {
                    $final_chart_data[$date] = $count;
                }
            }
            ksort($final_chart_data);
            ?>

            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('performanceChart').getContext('2d');
            var chartData = <?php echo json_encode($final_chart_data); ?>;
            var labels = Object.keys(chartData);
            var values = Object.values(chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Bookings',
                        data: values,
                        borderColor: '#e61e4d',
                        backgroundColor: 'rgba(230, 30, 77, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#e61e4d',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#222',
                            titleFont: { size: 14 },
                            bodyFont: { size: 14 },
                            padding: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1, color: '#999' },
                            grid: { color: '#f0f0f0' }
                        },
                        x: { 
                            ticks: { color: '#999', maxRotation: 45, minRotation: 45 },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
        </script>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3>Recent Platform Events</h3>
                <div style="background:#fff; border:1px solid #eee; border-radius:12px; padding:20px;">
                    <?php
                    $event_args = array(
                        'post_type' => array('booking', 'listing', 'ticket'),
                        'posts_per_page' => 10,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    );
                    if ($start_date || $end_date) {
                        $date_query = array('inclusive' => true);
                        if ($start_date) $date_query['after'] = $start_date;
                        if ($end_date) $date_query['before'] = $end_date;
                        $event_args['date_query'] = $date_query;
                    }
                    $recent_events = get_posts($event_args);
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
                    $recent_user_args = array(
                        'orderby' => 'registered',
                        'order' => 'DESC',
                        'number' => 10
                    );
                    if ($start_date || $end_date) {
                        $date_query = array('inclusive' => true);
                        if ($start_date) $date_query['after'] = $start_date;
                        if ($end_date) $date_query['before'] = $end_date;
                        $recent_user_args['date_query'] = $date_query;
                    }
                    $recent_users = get_users($recent_user_args);
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
        $search = isset($_GET['listing_search']) ? sanitize_text_field($_GET['listing_search']) : '';
        $status_filter = isset($_GET['listing_status']) ? sanitize_text_field($_GET['listing_status']) : '';

        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => $status_filter ? array($status_filter) : array('publish', 'pending', 'draft'),
            'suppress_filters' => false,
        );

        if ($search) {
            $query_args['s'] = $search;
        }

        $listings = get_posts($query_args);

        echo '<h3>Manage All Listings</h3>';
        ?>
        <!-- Listing Filters -->
        <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px;">
            <form action="" method="GET" style="display:flex; gap:20px; align-items:flex-end;">
                <input type="hidden" name="page" value="obenlo-booking">
                <input type="hidden" name="tab" value="listings">
                
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Search Listings</label>
                    <input type="text" name="listing_search" value="<?php echo esc_attr($search); ?>" placeholder="Title or keyword..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Status</label>
                    <select name="listing_status" style="padding:10px; border:1px solid #ddd; border-radius:8px; min-width:150px;">
                        <option value="">All Statuses</option>
                        <option value="publish" <?php selected($status_filter, 'publish'); ?>>Published</option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                        <option value="draft" <?php selected($status_filter, 'draft'); ?>>Draft</option>
                    </select>
                </div>

                <button type="submit" style="background:#222; color:#fff; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Search</button>
                <a href="?page=obenlo-booking&tab=listings" style="padding:10px; color:#666; font-size:0.9rem; text-decoration:none;">Reset</a>
            </form>
        </div>
        <?php
        echo '<table class="admin-table">';
        echo '<tr><th>Title</th><th>Host</th><th>Status</th><th>Price</th><th>Actions</th></tr>';
        foreach ($listings as $listing) {
            $host = get_userdata($listing->post_author);
            $price = get_post_meta($listing->ID, '_obenlo_price', true);
            $is_suspended = get_post_meta($listing->ID, '_obenlo_is_suspended', true) === 'yes';
            $sus_text = $is_suspended ? 'Restore' : 'Suspend';
            $sus_class = $is_suspended ? 'btn-approve' : 'btn-reject';
            $display_status = ucfirst($listing->post_status);
            if ($is_suspended) {
                $display_status = '<span style="color:#e61e4d; font-weight:bold;">Suspended</span>';
            }

            echo '<tr>';
            echo '<td><strong>' . esc_html($listing->post_title) . '</strong></td>';
            echo '<td>' . ($host ? esc_html($host->display_name) : 'Unknown') . '</td>';
            echo '<td>' . $display_status . '</td>';
            echo '<td>$' . esc_html($price) . '</td>';
            echo '<td>';
            echo '<a href="' . get_permalink($listing->ID) . '" target="_blank">View</a> | ';
            if (current_user_can('manage_options')) {
                $location = get_post_meta($listing->ID, '_obenlo_location', true);
                if(empty($location)) $location = 'Toronto';
                $image = get_the_post_thumbnail_url($listing->ID, 'large');
                $template = get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!");
                $caption = str_replace( array('{title}', '${price}', '{location}'), array($listing->post_title, $price, $location), $template );
                $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(get_permalink($listing->ID)) . '&quote=' . urlencode($caption);

                echo '<a href="' . esc_url($share_url) . '" target="_blank" class="obenlo-social-push-btn" 
                    data-post-id="' . $listing->ID . '" 
                    data-title="' . esc_attr($listing->post_title) . '" 
                    data-price="' . esc_attr($price) . '" 
                    data-location="' . esc_attr($location) . '" 
                    data-url="' . esc_url(get_permalink($listing->ID)) . '" 
                    data-type="listing" 
                    data-image="' . esc_url($image) . '" 
                    style="color:#e61e4d; font-weight:bold;">Push to Social</a> | ';
            }
            echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to trash this listing?\');">';
            echo '<input type="hidden" name="action" value="obenlo_trash_listing">';
            echo '<input type="hidden" name="listing_id" value="' . $listing->ID . '">';
            wp_nonce_field('trash_listing_' . $listing->ID, 'trash_nonce');
            echo '<button type="submit" class="btn-reject" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">Trash</button>';
            echo '</form> | ';
            echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;" onsubmit="if(!\'' . $is_suspended . '\') { var r = prompt(\'Reason for suspension:\'); if(r===null) return false; this.reason.value=r; } return confirm(\'Are you sure?\');">';
            echo '<input type="hidden" name="action" value="obenlo_toggle_listing_suspension">';
            echo '<input type="hidden" name="listing_id" value="' . $listing->ID . '">';
            echo '<input type="hidden" name="reason" value="">';
            wp_nonce_field('suspend_listing_' . $listing->ID, 'suspend_nonce');
            echo '<button type="submit" class="' . $sus_class . '" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">' . $sus_text . '</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    private function render_users_tab()
    {
        $search = isset($_GET['user_search']) ? sanitize_text_field($_GET['user_search']) : '';
        $role_filter = isset($_GET['user_role']) ? sanitize_text_field($_GET['user_role']) : '';

        $query_args = array(
            'role__in' => $role_filter ? array($role_filter) : array('host', 'guest'),
            'orderby' => 'registered',
            'order' => 'DESC'
        );

        if ($search) {
            $query_args['search'] = '*' . $search . '*';
            $query_args['search_columns'] = array('user_login', 'user_nicename', 'user_email', 'display_name');
        }

        $users = get_users($query_args);

        echo '<h3>Site User Management</h3>';
?>
        <!-- User Filters -->
        <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px;">
            <form action="" method="GET" style="display:flex; gap:20px; align-items:flex-end;">
                <input type="hidden" name="page" value="obenlo-booking">
                <input type="hidden" name="tab" value="users">
                
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Search Users</label>
                    <input type="text" name="user_search" value="<?php echo esc_attr($search); ?>" placeholder="Name, email, or username..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Role</label>
                    <select name="user_role" style="padding:10px; border:1px solid #ddd; border-radius:8px; min-width:150px;">
                        <option value="">All Roles</option>
                        <option value="host" <?php selected($role_filter, 'host'); ?>>Hosts</option>
                        <option value="guest" <?php selected($role_filter, 'guest'); ?>>Guests</option>
                    </select>
                </div>

                <button type="submit" style="background:#222; color:#fff; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Search</button>
                <a href="?page=obenlo-booking&tab=users" style="padding:10px; color:#666; font-size:0.9rem;">Reset</a>
            </form>
        </div>

        <table class="admin-table">
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
                    <?php 
                    $is_suspended = get_user_meta($user->ID, '_obenlo_is_suspended', true) === 'yes';
                    if ($is_suspended): ?>
                        <td><span class="badge" style="background:#e61e4d; color:#fff;">Suspended</span></td>
                    <?php else: ?>
                        <td><span class="badge badge-success">Active</span></td>
                    <?php endif; ?>
                    <td><span class="badge <?php echo $v_badge; ?>"><?php echo ucfirst($status); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user->user_registered)); ?></td>
                    <td>
                        <?php if (in_array('host', $user->roles)): 
                            $sus_text = $is_suspended ? 'Restore Host' : 'Suspend Host';
                        ?>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:flex; gap:5px; margin-bottom:5px;">
                                <input type="hidden" name="action" value="obenlo_update_user_fee">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <?php wp_nonce_field('update_user_fee_' . $user->ID, 'fee_nonce'); ?>
                                <input type="number" name="fee_percentage" value="<?php echo esc_attr(get_user_meta($user->ID, '_obenlo_host_fee_percentage', true)); ?>" placeholder="Global" step="0.1" style="width:70px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                                <button type="submit" style="padding:5px 10px; background:#222; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:0.8em;">Save Fee</button>
                            </form>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline;" onsubmit="if(!'<?php echo $is_suspended ? '1' : ''; ?>') { var r = prompt('Reason for suspension:'); if(r===null) return false; this.reason.value=r; } return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="obenlo_toggle_user_suspension">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <input type="hidden" name="reason" value="">
                                <?php wp_nonce_field('suspend_user_' . $user->ID, 'suspend_nonce'); ?>
                                <button type="submit" style="background:none; border:none; color:#e61e4d; cursor:pointer; text-decoration:underline; padding:0;"><?php echo $sus_text; ?></button>
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

                        <div style="margin-bottom:25px; background:#fff3cd; padding:15px; border-radius:10px; border:1px solid #ffeeba;">
                            <label style="display:flex; align-items:center; gap:10px; font-weight:700; margin-bottom:5px;">
                                <input type="checkbox" name="hide_demo_frontpage" value="yes" <?php checked(get_option('obenlo_hide_demo_frontpage', 'no'), 'yes'); ?>>
                                Hide Demo Content from Public Frontpage
                            </label>
                            <p style="font-size:0.85em; color:#856404; margin-bottom:0px; margin-left: 24px;">Visitor guests will not see demo listings/hosts on the home page. Admins and Hosts will still see them.</p>
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

                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px; margin-top:40px;">Haiti Payment Gateways 🇭🇹</h4>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">USD to HTG Exchange Rate</label>
                            <p style="font-size:0.8em; color:#666; margin-bottom:10px;">Used to convert listing prices to Haitian Gourdes for MonCash/Natcash.</p>
                            <input type="number" name="htg_exchange_rate" value="<?php echo esc_attr(get_option('obenlo_htg_exchange_rate', '100')); ?>" step="0.01" style="width:150px; padding:10px; border:1px solid #ddd; border-radius:8px;"> <span style="font-weight:600;">HTG = 1 USD</span>
                        </div>
                        
                        <div style="margin-bottom:20px; background:#f9f9f9; padding:15px; border-radius:10px;">
                            <label style="display:block; font-weight:700; margin-bottom:10px;">MonCash Credentials</label>
                            <div style="margin-bottom:10px;">
                                <span style="font-size:0.75rem; color:#888; display:block; margin-bottom:3px;">Sandbox Client ID</span>
                                <input type="text" name="moncash_sandbox_client_id" value="<?php echo esc_attr(get_option('obenlo_moncash_sandbox_client_id', '')); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div style="margin-bottom:10px;">
                                <span style="font-size:0.75rem; color:#888; display:block; margin-bottom:3px;">Sandbox Secret</span>
                                <input type="password" name="moncash_sandbox_secret" value="<?php echo esc_attr(get_option('obenlo_moncash_sandbox_secret', '')); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div style="margin-bottom:10px; border-top:1px solid #eee; pt-2; mt-2;">
                                <span style="font-size:0.75rem; color:#888; display:block; margin-bottom:3px;">Live Client ID</span>
                                <input type="text" name="moncash_live_client_id" value="<?php echo esc_attr(get_option('obenlo_moncash_live_client_id', '')); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div>
                                <span style="font-size:0.75rem; color:#888; display:block; margin-bottom:3px;">Live Secret</span>
                                <input type="password" name="moncash_live_secret" value="<?php echo esc_attr(get_option('obenlo_moncash_live_secret', '')); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                        </div>

                        <div style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                            <label style="display:flex; align-items:center; gap:10px; font-weight:700; margin-bottom:10px;">
                                <input type="checkbox" name="natcash_enabled" value="yes" <?php checked(get_option('obenlo_natcash_enabled', 'yes'), 'yes'); ?>>
                                Natcash Configuration
                            </label>
                            <input type="text" name="natcash_api_key" value="<?php echo esc_attr(get_option('obenlo_natcash_api_key', '')); ?>" placeholder="API Key" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
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
        // Payment Mode
        $payment_mode = get_option('obenlo_payment_mode', 'sandbox');

        // Stripe Keys
        $stripe_live_pub = get_option('obenlo_stripe_live_publishable_key', '');
        $stripe_live_sec = get_option('obenlo_stripe_live_secret_key', '');
        $stripe_sandbox_pub = get_option('obenlo_stripe_sandbox_publishable_key', '');
        $stripe_sandbox_sec = get_option('obenlo_stripe_sandbox_secret_key', '');

        // PayPal Keys
        $paypal_live_id = get_option('obenlo_paypal_live_client_id', '');
        $paypal_live_sec = get_option('obenlo_paypal_live_secret', '');
        $paypal_sandbox_id = get_option('obenlo_paypal_sandbox_client_id', '');
        $paypal_sandbox_sec = get_option('obenlo_paypal_sandbox_secret', '');
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
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                        <div>
                            <h4 style="margin-bottom:15px; color:#e61e4d; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="stripe_enabled" value="yes" <?php checked(get_option('obenlo_stripe_enabled', 'yes'), 'yes'); ?>>
                                Stripe LIVE Keys
                            </h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Publishable Key (Live)</label>
                            <input type="text" name="stripe_live_pub" value="<?php echo esc_attr($stripe_live_pub); ?>" placeholder="pk_live_..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key (Live)</label>
                            <input type="password" name="stripe_live_sec" value="<?php echo esc_attr($stripe_live_sec); ?>" placeholder="sk_live_..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div>
                            <h4 style="margin-bottom:15px; color:#666;">Stripe SANDBOX Keys</h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Publishable Key (Test)</label>
                            <input type="text" name="stripe_sandbox_pub" value="<?php echo esc_attr($stripe_sandbox_pub); ?>" placeholder="pk_test_..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key (Test)</label>
                            <input type="password" name="stripe_sandbox_sec" value="<?php echo esc_attr($stripe_sandbox_sec); ?>" placeholder="sk_test_..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                        <div>
                            <h4 style="margin-bottom:15px; color:#0070ba; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="paypal_enabled" value="yes" <?php checked(get_option('obenlo_paypal_enabled', 'yes'), 'yes'); ?>>
                                PayPal LIVE Keys
                            </h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID (Live)</label>
                            <input type="text" name="paypal_live_id" value="<?php echo esc_attr($paypal_live_id); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret (Live)</label>
                            <input type="password" name="paypal_live_sec" value="<?php echo esc_attr($paypal_live_sec); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div>
                            <h4 style="margin-bottom:15px; color:#666;">PayPal SANDBOX Keys</h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID (Test)</label>
                            <input type="text" name="paypal_sandbox_id" value="<?php echo esc_attr($paypal_sandbox_id); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret (Test)</label>
                            <input type="password" name="paypal_sandbox_sec" value="<?php echo esc_attr($paypal_sandbox_sec); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom:25px; border-top:1px solid #eee; padding-top:25px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                        <div>
                            <h4 style="margin-bottom:15px; color:#c71e1e; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="moncash_enabled" value="yes" <?php checked(get_option('obenlo_moncash_enabled', 'yes'), 'yes'); ?>>
                                MonCash LIVE Credentials
                            </h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID (Live)</label>
                            <input type="text" name="moncash_live_id" value="<?php echo esc_attr(get_option('obenlo_moncash_live_client_id', '')); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key (Live)</label>
                            <input type="password" name="moncash_live_sec" value="<?php echo esc_attr(get_option('obenlo_moncash_live_secret', '')); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div>
                            <h4 style="margin-bottom:15px; color:#666;">MonCash SANDBOX Credentials</h4>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Client ID (Test)</label>
                            <input type="text" name="moncash_sandbox_id" value="<?php echo esc_attr(get_option('obenlo_moncash_sandbox_client_id', '')); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-bottom:10px;">
                            
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Secret Key (Test)</label>
                            <input type="password" name="moncash_sandbox_sec" value="<?php echo esc_attr(get_option('obenlo_moncash_sandbox_secret', '')); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                    </div>
                </div>

                <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:700;">Save Payment Settings</button>
            </form>
        </div>

        <h3 style="margin-top:50px;">Pending Payout Requests</h3>
        <?php
        $pending_payouts = get_posts(array(
            'post_type' => 'obenlo_payout_req',
            'meta_key' => '_status',
            'meta_value' => 'pending',
            'posts_per_page' => -1
        ));

        if (empty($pending_payouts)):
            echo '<p style="color:#666; font-style:italic;">No pending payout requests at this time.</p>';
        else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Host</th>
                        <th>Amount</th>
                        <th>Method / Details</th>
                        <th>Date Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_payouts as $payout): 
                        $host_id = $payout->post_author;
                        $host = get_userdata($host_id);
                        $amount = get_post_meta($payout->ID, '_amount', true);
                        $method = get_post_meta($payout->ID, '_method', true);
                        $details = get_post_meta($payout->ID, '_details', true);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($host->display_name); ?></strong><br>
                            <small><?php echo esc_html($host->user_email); ?></small>
                        </td>
                        <td style="font-size:1.2em; font-weight:700; color:#10b981;">$<?php echo number_format($amount, 2); ?></td>
                        <td>
                            <span class="badge badge-guest" style="text-transform:uppercase;"><?php echo esc_html($method); ?></span><br>
                            <code><?php echo esc_html($details); ?></code>
                        </td>
                        <td><?php echo get_the_date('', $payout); ?></td>
                        <td>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="obenlo_process_payout">
                                <input type="hidden" name="payout_id" value="<?php echo $payout->ID; ?>">
                                <input type="hidden" name="payout_status" value="paid">
                                <?php wp_nonce_field('process_payout_' . $payout->ID, 'security'); ?>
                                <input type="text" name="transaction_id" placeholder="Manual TX ID" style="width:120px; padding:5px; margin-right:5px; border-radius:4px; border:1px solid #ddd;">
                                <button type="submit" class="btn-approve" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Confirm you have manually sent this payout?')">Mark as Paid</button>
                                <?php if ($method === 'moncash'): ?>
                                    <button type="submit" name="payout_action" value="auto_moncash" style="background:#e61e4d; color:#fff; border:none; padding:5px 10px; border-radius:4px; margin-left:10px; font-size:0.85rem; cursor:pointer;" onclick="return confirm('Send money via MonCash API now?')">🚀 Send via MonCash</button>
                                <?php endif; ?>
                            </form>
                            | 
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="obenlo_process_payout">
                                <input type="hidden" name="payout_id" value="<?php echo $payout->ID; ?>">
                                <input type="hidden" name="payout_status" value="cancelled">
                                <?php wp_nonce_field('process_payout_' . $payout->ID, 'security'); ?>
                                <button type="submit" class="btn-reject" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Reject this payout request?')">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3 style="margin-top:50px;">Recent Processed Payouts</h3>
        <?php
        $recent_payouts = get_posts(array(
            'post_type' => 'obenlo_payout_req',
            'meta_key' => '_status',
            'meta_value' => 'paid',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        if (empty($recent_payouts)):
            echo '<p style="color:#666; font-style:italic;">No recently processed payouts.</p>';
        else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Host</th>
                        <th>Amount</th>
                        <th>Details</th>
                        <th>Date Paid</th>
                        <th>TX ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payouts as $payout): 
                        $host = get_userdata($payout->post_author);
                    ?>
                    <tr>
                        <td><?php echo esc_html($host ? $host->display_name : 'Unknown'); ?></td>
                        <td style="font-weight:700;">$<?php echo number_format(get_post_meta($payout->ID, '_amount', true), 2); ?></td>
                        <td><?php echo esc_html(strtoupper(get_post_meta($payout->ID, '_method', true))); ?></td>
                        <td><?php echo get_post_meta($payout->ID, '_paid_date', true); ?></td>
                        <td><code><?php echo esc_html(get_post_meta($payout->ID, '_transaction_id', true)); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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

        update_option('obenlo_hide_demo_frontpage', isset($_POST['hide_demo_frontpage']) ? 'yes' : 'no');

        // Haiti Payment Settings
        if (isset($_POST['htg_exchange_rate'])) {
            update_option('obenlo_htg_exchange_rate', sanitize_text_field($_POST['htg_exchange_rate']));
        }
        if (isset($_POST['moncash_sandbox_client_id'])) {
            update_option('obenlo_moncash_sandbox_client_id', sanitize_text_field($_POST['moncash_sandbox_client_id']));
        }
        if (isset($_POST['moncash_sandbox_secret'])) {
            update_option('obenlo_moncash_sandbox_secret', sanitize_text_field($_POST['moncash_sandbox_secret']));
        }
        if (isset($_POST['moncash_live_client_id'])) {
            update_option('obenlo_moncash_live_client_id', sanitize_text_field($_POST['moncash_live_client_id']));
        }
        if (isset($_POST['moncash_live_secret'])) {
            update_option('obenlo_moncash_live_secret', sanitize_text_field($_POST['moncash_live_secret']));
        }
        if (isset($_POST['natcash_api_key'])) {
            update_option('obenlo_natcash_api_key', sanitize_text_field($_POST['natcash_api_key']));
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
        // Stripe Save
        if (isset($_POST['stripe_live_pub'])) {
            update_option('obenlo_stripe_live_publishable_key', sanitize_text_field($_POST['stripe_live_pub']));
        }
        if (isset($_POST['stripe_live_sec'])) {
            update_option('obenlo_stripe_live_secret_key', sanitize_text_field($_POST['stripe_live_sec']));
        }
        if (isset($_POST['stripe_sandbox_pub'])) {
            update_option('obenlo_stripe_sandbox_publishable_key', sanitize_text_field($_POST['stripe_sandbox_pub']));
        }
        if (isset($_POST['stripe_sandbox_sec'])) {
            update_option('obenlo_stripe_sandbox_secret_key', sanitize_text_field($_POST['stripe_sandbox_sec']));
        }
        // PayPal Save
        if (isset($_POST['paypal_live_id'])) {
            update_option('obenlo_paypal_live_client_id', sanitize_text_field($_POST['paypal_live_id']));
        }
        if (isset($_POST['paypal_live_sec'])) {
            update_option('obenlo_paypal_live_secret', sanitize_text_field($_POST['paypal_live_sec']));
        }
        if (isset($_POST['paypal_sandbox_id'])) {
            update_option('obenlo_paypal_sandbox_client_id', sanitize_text_field($_POST['paypal_sandbox_id']));
        }
        if (isset($_POST['paypal_sandbox_sec'])) {
            update_option('obenlo_paypal_sandbox_secret', sanitize_text_field($_POST['paypal_sandbox_sec']));
        }

        // MonCash Save
        if (isset($_POST['moncash_live_id'])) {
            update_option('obenlo_moncash_live_client_id', sanitize_text_field($_POST['moncash_live_id']));
        }
        if (isset($_POST['moncash_live_sec'])) {
            update_option('obenlo_moncash_live_secret', sanitize_text_field($_POST['moncash_live_sec']));
        }
        if (isset($_POST['moncash_sandbox_id'])) {
            update_option('obenlo_moncash_sandbox_client_id', sanitize_text_field($_POST['moncash_sandbox_id']));
        }
        if (isset($_POST['moncash_sandbox_sec'])) {
            update_option('obenlo_moncash_sandbox_secret', sanitize_text_field($_POST['moncash_sandbox_sec']));
        }

        // Natcash Save
        if (isset($_POST['natcash_api_key'])) {
            update_option('obenlo_natcash_api_key', sanitize_text_field($_POST['natcash_api_key']));
        }

        // Visibility Toggles Save
        update_option('obenlo_stripe_enabled', isset($_POST['stripe_enabled']) ? 'yes' : 'no');
        update_option('obenlo_paypal_enabled', isset($_POST['paypal_enabled']) ? 'yes' : 'no');
        update_option('obenlo_moncash_enabled', isset($_POST['moncash_enabled']) ? 'yes' : 'no');
        update_option('obenlo_natcash_enabled', isset($_POST['natcash_enabled']) ? 'yes' : 'no');

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
        echo '<tr><th>ID</th><th>Listing</th><th>Total</th><th>Status</th><th>Mode</th><th>Date</th></tr>';
        foreach ($bookings as $booking) {
            $listing_id = get_post_meta($booking->ID, '_obenlo_listing_id', true);
            $total = get_post_meta($booking->ID, '_obenlo_total_price', true);
            $status = get_post_meta($booking->ID, '_obenlo_booking_status', true);
            $mode = get_post_meta($booking->ID, '_obenlo_payment_mode', true) ?: 'legacy';
            $mode_color = ($mode === 'live') ? '#e61e4d' : '#666';
            $mode_label = ($mode === 'live') ? 'LIVE' : 'TEST';
            
            echo '<tr>';
            echo '<td>#' . $booking->ID . '</td>';
            echo '<td>' . get_the_title($listing_id) . '</td>';
            echo '<td>$' . number_format(floatval($total), 2) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td><span class="badge" style="background:' . $mode_color . '; color:#fff;">' . $mode_label . '</span></td>';
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

    private function render_translation_tab()
    {
        $es_translations = get_option('obenlo_i18n_es', array());
        $fr_translations = get_option('obenlo_i18n_fr', array());
        $enable_google = get_option('obenlo_enable_google_translate', '0');
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h3 style="margin: 0; font-size: 1.8em; font-weight: 800;">Global translation</h3>
                <p style="color:#888; margin: 5px 0 0 0;">Master console for manual dictionaries and automated fallbacks.</p>
            </div>
            <div style="background: #fff; padding: 10px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <span style="font-size: 0.85rem; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Google Widget</span>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" id="google-toggle-form">
                    <input type="hidden" name="action" value="obenlo_save_translation">
                    <?php wp_nonce_field('save_translation', 'translation_nonce'); ?>
                    <label class="switch" style="position: relative; display: inline-block; width: 46px; height: 24px;">
                        <input type="checkbox" name="enable_google" value="1" <?php checked('1', $enable_google); ?> onchange="document.getElementById('google-toggle-form').submit()">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo ($enable_google === '1') ? '#e61e4d' : '#ccc'; ?>; transition: .4s; border-radius: 24px;"></span>
                    </label>
                    <input type="hidden" name="es_raw" value='<?php echo esc_attr(json_encode($es_translations)); ?>'>
                    <input type="hidden" name="fr_raw" value='<?php echo esc_attr(json_encode($fr_translations)); ?>'>
                </form>
            </div>
        </div>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="obenlo_save_translation">
            <?php wp_nonce_field('save_translation', 'translation_nonce'); ?>
            <input type="hidden" name="enable_google" value="<?php echo esc_attr($enable_google); ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div class="stat-card" style="text-align: left; padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="margin: 0; font-size: 1.1em; color: #1a1a1b;">Spanish (ES)</h4>
                        <span style="background: rgba(230, 30, 77, 0.1); color: #e61e4d; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">MANUAL DICTIONARY</span>
                    </div>
                    <textarea name="es_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 13px; line-height: 1.6; padding: 20px; border: 1px solid #eee; border-radius: 12px; background: #fcfcfc; color: #444; outline: none; transition: border-color 0.2s;" placeholder='{ "Original": "Tratamiento" }'><?php echo esc_textarea(json_encode($es_translations, JSON_PRETTY_PRINT)); ?></textarea>
                </div>

                <div class="stat-card" style="text-align: left; padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="margin: 0; font-size: 1.1em; color: #1a1a1b;">French (FR)</h4>
                        <span style="background: rgba(34, 34, 34, 0.05); color: #222; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">MANUAL DICTIONARY</span>
                    </div>
                    <textarea name="fr_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 13px; line-height: 1.6; padding: 20px; border: 1px solid #eee; border-radius: 12px; background: #fcfcfc; color: #444; outline: none; transition: border-color 0.2s;" placeholder='{ "Original": "Traduction" }'><?php echo esc_textarea(json_encode($fr_translations, JSON_PRETTY_PRINT)); ?></textarea>
                </div>
            </div>

            <button type="submit" style="background: #222; color: #fff; border: none; padding: 18px; border-radius: 14px; font-weight: 800; font-size: 1.1em; cursor: pointer; width: 100%; box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: transform 0.2s;">
                Update Global Dictionaries
            </button>
        </form>

        <style>
            .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; <?php echo ($enable_google === '1') ? 'transform: translateX(22px);' : ''; ?> }
            textarea:focus { border-color: #e61e4d !important; }
        </style>
        <?php
    }

    public function handle_save_translation()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        check_admin_referer('save_translation', 'translation_nonce');

        $redirect_url = add_query_arg('tab', 'translation', wp_get_referer());

        // Save Google Translate Toggle
        $enable_google = isset($_POST['enable_google']) ? '1' : '0';
        update_option('obenlo_enable_google_translate', $enable_google);

        // Save Raw Dictionaries
        if (isset($_POST['es_raw'])) {
            $es_json = stripslashes($_POST['es_raw']);
            $es_array = json_decode($es_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($es_array)) {
                update_option('obenlo_i18n_es', $es_array);
            }
        }

        if (isset($_POST['fr_raw'])) {
            $fr_json = stripslashes($_POST['fr_raw']);
            $fr_array = json_decode($fr_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($fr_array)) {
                update_option('obenlo_i18n_fr', $fr_array);
            }
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

    private function render_reviews_tab()
    {
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        
        $args = array(
            'post_type' => 'listing',
            'status'    => $status_filter === 'all' ? '' : $status_filter,
            'parent'    => 0, // Top-level reviews only
        );

        $comments = get_comments($args);

        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
        echo '<h3>Manage Platform Reviews</h3>';
        echo '<div>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=all" class="badge ' . ($status_filter === 'all' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'all' ? '#fff' : '#666') . ';">All</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=hold" class="badge ' . ($status_filter === 'hold' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'hold' ? '#fff' : '#666') . ';">Pending</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=approve" class="badge ' . ($status_filter === 'approve' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'approve' ? '#fff' : '#666') . ';">Approved</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=trash" class="badge ' . ($status_filter === 'trash' ? 'badge-host' : '') . '" style="text-decoration:none; border:1px solid #eee; color:' . ($status_filter === 'trash' ? '#fff' : '#666') . ';">Trash</a>';
        echo '</div>';
        echo '</div>';

        if (empty($comments)) {
            echo '<p style="padding:40px; text-align:center; background:#fff; border-radius:12px; border:1px solid #eee; color:#999;">No reviews found.</p>';
            return;
        }

        echo '<table class="admin-table">';
        echo '<tr><th>Author</th><th>Rating</th><th>Listing</th><th>Review</th><th>Date</th><th>Actions</th></tr>';
        
        foreach ($comments as $comment) {
            $rating = get_comment_meta($comment->comment_ID, '_obenlo_rating', true);
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            $listing = get_post($comment->comment_post_ID);
            
            $status_label = '';
            $status_color = '#666';
            if ($comment->comment_approved === '0') { $status_label = ' [PENDING]'; $status_color = '#f97316'; }
            if ($comment->comment_approved === 'trash') { $status_label = ' [TRASH]'; $status_color = '#ef4444'; }

            echo '<tr>';
            echo '<td><strong>' . esc_html($comment->comment_author) . '</strong><br><small>' . esc_html($comment->comment_author_email) . '</small></td>';
            echo '<td><span style="color:#FFD700; font-weight:bold; font-size:1.1em;">' . $stars . '</span><br><small>(' . $rating . '/5)</small></td>';
            echo '<td><a href="' . get_permalink($listing->ID) . '" target="_blank" style="color:#222; font-weight:600; text-decoration:none;">' . esc_html($listing->post_title) . '</a></td>';
            echo '<td style="max-width:300px;"><div style="font-size:0.9em; line-height:1.4; color:#444;">' . esc_html($comment->comment_content) . ' <span style="color:'.$status_color.'; font-weight:bold; font-size:0.75rem;">' . $status_label . '</span></div></td>';
            echo '<td>' . date('M j, Y', strtotime($comment->comment_date)) . '</td>';
            echo '<td>';
            echo '<div style="display:flex; gap:10px; align-items:center;">';
            
            $base_action_url = admin_url('admin-post.php?action=obenlo_admin_review_action&comment_id=' . $comment->comment_ID);
            $nonce = wp_create_nonce('obenlo_admin_review_' . $comment->comment_ID);
            
            if ($comment->comment_approved === '0') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=approve', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#10b981; font-weight:700; text-decoration:none; font-size:0.85rem;">Approve</a>';
            } else if ($comment->comment_approved === '1') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=unapprove', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#f97316; font-weight:700; text-decoration:none; font-size:0.85rem;">Unapprove</a>';
            }

            if ($comment->comment_approved !== 'trash') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=trash', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Trash</a>';
            } else {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=approve', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#10b981; font-weight:700; text-decoration:none; font-size:0.85rem;">Restore</a>';
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=delete', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#000; font-weight:700; text-decoration:none; font-size:0.85rem;" onclick="return confirm(\'Permanently delete this review?\')">Delete</a>';
            }
            
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function handle_review_action()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : 0;
        $action = isset($_GET['do']) ? sanitize_text_field($_GET['do']) : '';
        
        check_admin_referer('obenlo_admin_review_' . $comment_id, 'nonce');

        if ($comment_id && $action) {
            switch ($action) {
                case 'approve':
                    wp_set_comment_status($comment_id, 'approve');
                    break;
                case 'unapprove':
                    wp_set_comment_status($comment_id, 'hold');
                    break;
                case 'trash':
                    wp_set_comment_status($comment_id, 'trash');
                    break;
                case 'delete':
                    wp_delete_comment($comment_id, true);
                    break;
            }
        }

        wp_safe_redirect(add_query_arg(['tab' => 'reviews', 'status' => isset($_GET['status']) ? $_GET['status'] : 'all'], wp_get_referer()));
        exit;
    }

    public function handle_toggle_listing_suspension() {
        if (!current_user_can('administrator')) return;
        $listing_id = intval($_POST['listing_id']);
        check_admin_referer('suspend_listing_' . $listing_id, 'suspend_nonce');

        $is_suspended = get_post_meta($listing_id, '_obenlo_is_suspended', true) === 'yes';
        if ($is_suspended) {
            delete_post_meta($listing_id, '_obenlo_is_suspended');
            delete_post_meta($listing_id, '_obenlo_suspension_reason');
        } else {
            update_post_meta($listing_id, '_obenlo_is_suspended', 'yes');
            update_post_meta($listing_id, '_obenlo_suspension_reason', sanitize_text_field($_POST['reason']));
        }
        wp_safe_redirect(add_query_arg('tab', 'listings', wp_get_referer()));
        exit;
    }

    public function handle_toggle_user_suspension() {
        if (!current_user_can('administrator')) return;
        $user_id = intval($_POST['user_id']);
        check_admin_referer('suspend_user_' . $user_id, 'suspend_nonce');

        $is_suspended = get_user_meta($user_id, '_obenlo_is_suspended', true) === 'yes';
        if ($is_suspended) {
            delete_user_meta($user_id, '_obenlo_is_suspended');
            delete_user_meta($user_id, '_obenlo_suspension_reason');
        } else {
            update_user_meta($user_id, '_obenlo_is_suspended', 'yes');
            update_user_meta($user_id, '_obenlo_suspension_reason', sanitize_text_field($_POST['reason']));
        }
        wp_safe_redirect(add_query_arg('tab', 'users', wp_get_referer()));
        exit;
    }

    public function handle_trash_listing()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        check_admin_referer('trash_listing_' . $listing_id, 'trash_nonce');

        if ($listing_id) {
            wp_trash_post($listing_id);
        }

        wp_safe_redirect(add_query_arg('tab', 'listings', wp_get_referer()));
        exit;
    }

    private function render_testimonies_tab()
    {
        $testimonies = get_posts(array(
            'post_type' => 'testimony',
            'post_status' => array('pending', 'publish', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        ?>
        <h2>User Testimonies (Obenlo Love)</h2>
        <p>Moderation queue for platform-wide testimonials. Approved testimonies appear on the homepage.</p>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Testimony</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonies)): ?>
                    <tr><td colspan="6" style="text-align:center;">No testimonies found.</td></tr>
                <?php else: ?>
                    <?php foreach ($testimonies as $t): 
                        $user = get_userdata($t->post_author);
                        $rating = get_post_meta($t->ID, '_obenlo_testimony_rating', true);
                        $status = $t->post_status;
                    ?>
                        <tr>
                            <td><?php echo get_the_date('', $t->ID); ?></td>
                            <td>
                                <strong><?php echo $user ? esc_html($user->display_name) : 'Unknown'; ?></strong><br>
                                <small><?php echo $user ? esc_html($user->user_email) : ''; ?></small>
                            </td>
                            <td style="max-width: 400px;">
                                <strong><?php echo esc_html($t->post_title); ?></strong><br>
                                <span style="font-size:0.9em; color:#666;"><?php echo esc_html($t->post_content); ?></span>
                            </td>
                            <td>
                                <div style="color:#f59e0b;">
                                    <?php for($i=1; $i<=5; $i++) echo ($i <= $rating ? '★' : '☆'); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $status === 'publish' ? 'badge-host' : 'badge-guest'; ?>">
                                    <?php echo esc_html(strtoupper($status)); ?>
                                </span>
                            </td>
                            <td>
                                <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="obenlo_admin_testimony_action">
                                    <input type="hidden" name="testimony_id" value="<?php echo $t->ID; ?>">
                                    <?php wp_nonce_field('testimony_action_' . $t->ID); ?>
                                    
                                    <?php if ($status !== 'publish'): ?>
                                        <button type="submit" name="testimony_status" value="publish" class="btn-approve" style="background:none; border:none; cursor:pointer;">Approve</button>
                                    <?php endif; ?>
                                    
                                    <?php if ($status !== 'trash'): ?>
                                        <button type="submit" name="testimony_status" value="trash" class="btn-reject" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Trash this testimony?')">Trash</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    public function handle_testimony_action()
    {
        if (!current_user_can('administrator')) {
            wp_die('Unauthorized');
        }

        $testimony_id = isset($_POST['testimony_id']) ? intval($_POST['testimony_id']) : 0;
        $new_status   = isset($_POST['testimony_status']) ? sanitize_text_field($_POST['testimony_status']) : '';

        check_admin_referer('testimony_action_' . $testimony_id);

        if ($testimony_id && in_array($new_status, array('publish', 'trash', 'pending'))) {
            wp_update_post(array(
                'ID' => $testimony_id,
                'post_status' => $new_status
            ));
        }

        wp_safe_redirect(add_query_arg('tab', 'testimonies', wp_get_referer()));
        exit;
    }

}
