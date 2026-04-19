<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Overview
{
    public function init()
    {
        // No specific hooks for overview yet
    }

    public function render_overview_tab()
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

}
