<?php
/**
 * Host Payouts Module
 * Single Responsibility: Payout settings form, earnings chart, and payout history.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Payouts
{
    public function render_payout_tab()
    {
        $user_id     = get_current_user_id();
        $balance     = Obenlo_Booking_Payout_Manager::get_host_balance($user_id);
        $min_payout  = 20.00;
        $currency    = '$';

        $start_date  = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date    = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : '';

        $booking_args = array(
            'post_type' => 'booking', 'posts_per_page' => -1, 'post_status' => 'any',
            'meta_query' => array(array('key' => '_obenlo_host_id', 'value' => $user_id))
        );

        if ($start_date || $end_date) {
            $date_query = array('inclusive' => true);
            if ($start_date) $date_query['after']  = $start_date;
            if ($end_date)   $date_query['before'] = $end_date;
            $booking_args['date_query'] = $date_query;
        }

        $bookings = get_posts($booking_args);
        $total_earned_net = 0;
        $period_bookings_count = 0;
        $chart_data_raw = array();

        foreach ($bookings as $booking) {
            if (get_post_meta($booking->ID, '_obenlo_booking_status', true) !== 'confirmed') continue;
            $net = floatval(get_post_meta($booking->ID, '_obenlo_booking_net_earnings', true));
            $total_earned_net += $net;
            $period_bookings_count++;
            $date = get_the_date('Y-m-d', $booking);
            $chart_data_raw[$date] = ($chart_data_raw[$date] ?? 0) + $net;
        }

        $chart_start     = $start_date ? new DateTime($start_date) : new DateTime('-30 days');
        $chart_end       = $end_date   ? new DateTime($end_date)   : new DateTime('today');
        $final_chart_data = array();
        foreach (new DatePeriod($chart_start, new DateInterval('P1D'), $chart_end->modify('+1 day')) as $dt) {
            $final_chart_data[$dt->format('Y-m-d')] = 0;
        }
        foreach ($chart_data_raw as $date => $val) { if (isset($final_chart_data[$date])) $final_chart_data[$date] = $val; }
        ksort($final_chart_data);

        $history = get_posts(array(
            'post_type' => 'obenlo_payout_req', 'author' => $user_id, 'posts_per_page' => 10,
            'orderby' => 'date', 'order' => 'DESC'
        ));

        echo '<div style="margin-bottom:40px;">';
            echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
                echo '<h2 style="font-size:1.5rem; font-weight:800; margin:0;">' . __('Business Performance', 'obenlo') . '</h2>';
                echo '<div style="font-size:0.8rem; color:#888;">' . ($start_date ? esc_html($start_date) . ' ' . __('to', 'obenlo') . ' ' . esc_html($end_date) : __('Last 30 Days', 'obenlo')) . '</div>';
            echo '</div>';

            // Filter form
            echo '<form action="' . esc_url(add_query_arg('action', 'payouts', home_url('/host-dashboard'))) . '" method="GET" style="background:#fff; padding:20px; border-radius:15px; border:1px solid #eee; margin-bottom:30px; display:flex; gap:15px; align-items:flex-end;">';
                echo '<input type="hidden" name="action" value="payouts">';
                echo '<div><label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">' . __('From', 'obenlo') . '</label><input type="date" name="start_date" value="' . esc_attr($start_date) . '" style="padding:10px; border:1px solid #ddd; border-radius:8px;"></div>';
                echo '<div><label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">' . __('To', 'obenlo') . '</label><input type="date" name="end_date" value="' . esc_attr($end_date) . '" style="padding:10px; border:1px solid #ddd; border-radius:8px;"></div>';
                echo '<button type="submit" class="btn-primary" style="padding:12px 20px; border-radius:8px; height:45px;">' . __('Filter Stats', 'obenlo') . '</button>';
                echo '<a href="' . esc_url(home_url('/host-dashboard?action=payouts')) . '" style="padding:12px; font-size:0.9rem; color:#666;">' . __('Reset', 'obenlo') . '</a>';
            echo '</form>';

            // Stats Row
            echo '<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:30px;">';
                foreach (array(
                    array(__('Available Balance', 'obenlo'), $currency . number_format($balance, 2), __('Ready for withdrawal', 'obenlo'), '#10b981'),
                    array(__('Period Earnings (Net)', 'obenlo'), $currency . number_format($total_earned_net, 2), __('After platform commission', 'obenlo'), '#222'),
                    array(__('Completed Bookings', 'obenlo'), $period_bookings_count, __('In selected period', 'obenlo'), '#222'),
                ) as $stat):
                    echo '<div style="background:#fff; padding:25px; border-radius:15px; border:1px solid #eee;">';
                    echo '<div style="font-size:0.7rem; text-transform:uppercase; color:#888; letter-spacing:1px; font-weight:700; margin-bottom:5px;">' . $stat[0] . '</div>';
                    echo '<div style="font-size:1.8rem; font-weight:900; color:' . $stat[3] . ';">' . $stat[1] . '</div>';
                    echo '<div style="font-size:0.75rem; color:#888; margin-top:5px;">' . $stat[2] . '</div>';
                    echo '</div>';
                endforeach;
            echo '</div>';

            // Revenue Chart
            echo '<div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:30px; margin-bottom:40px;">';
                echo '<div style="margin-bottom:20px; font-weight:800; font-size:1.1rem;">' . __('Revenue Growth Trend', 'obenlo') . '</div>';
                echo '<div style="position: relative; height: 350px; width: 100%;"><canvas id="hostPerformanceChart"></canvas></div>';
            echo '</div>';

            echo '<div style="text-align:center; margin-bottom:50px;">';
                if ($balance >= $min_payout) {
                    echo '<button id="request-payout-btn" class="btn-primary" style="padding:15px 40px; font-size:1.1rem; border-radius:12px;">' . sprintf(__('Withdraw Available Balance (%s)', 'obenlo'), $currency . number_format($balance, 2)) . '</button>';
                } else {
                    echo '<button disabled style="background:#eee; color:#aaa; border:none; padding:15px 40px; font-size:1.1rem; border-radius:12px; cursor:not-allowed;">' . sprintf(__('Minimum %s required to withdraw', 'obenlo'), $currency . $min_payout) . '</button>';
                }
            echo '</div>';

            echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var ctx = document.getElementById("hostPerformanceChart").getContext("2d");
                var chartData = ' . json_encode($final_chart_data) . ';
                new Chart(ctx, {
                    type: "line",
                    data: { labels: Object.keys(chartData), datasets: [{ label: "' . esc_js(__('Earnings (Net)', 'obenlo')) . '", data: Object.values(chartData), borderColor: "#10b981", backgroundColor: "rgba(16,185,129,0.1)", borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: "#10b981", pointRadius: 3 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { color: "#999" }, grid: { color: "#f5f5f5" } }, x: { ticks: { color: "#999", maxRotation: 45, minRotation: 45 }, grid: { display: false } } } }
                });

                var reqBtn = document.getElementById("request-payout-btn");
                if(reqBtn) {
                    reqBtn.addEventListener("click", function() {
                        if(!confirm("' . esc_js(__('Are you sure you want to request a payout of your entire available balance?', 'obenlo')) . '")) return;
                        reqBtn.disabled = true; reqBtn.innerText = "' . esc_js(__('Processing...', 'obenlo')) . '";
                        var fd = new FormData();
                        fd.append("action", "obenlo_request_payout");
                        fd.append("security", "' . wp_create_nonce("obenlo_payout_nonce") . '");
                        fetch("' . admin_url('admin-ajax.php') . '", { method: "POST", body: fd })
                        .then(r => r.json()).then(data => {
                            if(data.success) { alert(data.data.message); window.location.reload(); }
                            else { alert("❌ " + data.data); reqBtn.disabled = false; reqBtn.innerText = "' . esc_js(__('Withdraw Earnings', 'obenlo')) . '"; }
                        });
                    });
                }
            });
            </script>';
        echo '</div>';

        // Payout History
        if (!empty($history)) {
            echo '<h3 style="font-size:1.2rem; font-weight:800; margin-bottom:20px;">' . __('Payout History', 'obenlo') . '</h3>';
            echo '<div style="background:#fff; border-radius:15px; border:1px solid #eee; overflow:hidden; margin-bottom:40px;">';
            echo '<table class="admin-table"><thead style="background:#f9f9fb;"><tr>';
            echo '<th>' . __('Date', 'obenlo') . '</th><th>' . __('Amount', 'obenlo') . '</th><th>' . __('Status', 'obenlo') . '</th></tr></thead><tbody>';
            foreach ($history as $req) {
                $amt  = get_post_meta($req->ID, '_amount', true);
                $stat = get_post_meta($req->ID, '_status', true);
                $sc   = $stat === 'paid' ? '#10b981' : ($stat === 'cancelled' ? '#ef4444' : '#f59e0b');
                echo '<tr><td data-label="' . esc_attr(__('Date', 'obenlo')) . '">' . get_the_date('', $req) . '</td>';
                echo '<td data-label="' . esc_attr(__('Amount', 'obenlo')) . '" style="font-weight:700;">' . $currency . number_format($amt, 2) . '</td>';
                echo '<td data-label="' . esc_attr(__('Status', 'obenlo')) . '"><span style="background:' . $sc . '22; color:' . $sc . '; padding:4px 10px; border-radius:6px; font-size:0.75rem; font-weight:700; text-transform:uppercase;">' . $stat . '</span></td></tr>';
            }
            echo '</tbody></table></div>';
        }

        // Payout Preferences
        $current_method  = get_user_meta($user_id, 'obenlo_payout_method', true);
        $current_details = get_user_meta($user_id, 'obenlo_payout_details', true);
        $methods = Obenlo_Booking_Payout_Manager::get_methods();

        echo '<div class="dashboard-header"><h3 style="font-size:1.2rem; font-weight:800;">' . __('Payout Preferences', 'obenlo') . '</h3></div>';
        ?>
        <form id="payout-settings-form">
            <?php wp_nonce_field('obenlo_payout_nonce', 'security'); ?>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; margin-bottom:8px;"><?php echo __('Payout Method', 'obenlo'); ?></label>
                <select name="payout_method" id="payout_method_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    <option value=""><?php echo __('Select Method...', 'obenlo'); ?></option>
                    <?php foreach ($methods as $key => $method): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($current_method, $key); ?>><?php echo esc_html($method['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:25px;">
                <label style="display:block; font-weight:700; margin-bottom:8px;"><?php echo __('Payment Details', 'obenlo'); ?></label>
                <input type="text" name="payout_details" value="<?php echo esc_attr($current_details); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                <p style="font-size:0.8rem; color:#888; margin-top:5px;" id="method_hint"></p>
            </div>
            <button type="submit" class="btn-primary" id="save-payout-btn"><?php echo __('Save Payout Preferences', 'obenlo'); ?></button>
            <div id="payout-msg" style="margin-top:15px; font-weight:600;"></div>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('payout_method_select');
            var hint = document.getElementById('method_hint');
            var methods = <?php echo json_encode($methods); ?>;
            function updateHint() {
                var m = select.value;
                if(methods[m]) {
                    hint.innerText = 'Example: ' + methods[m].placeholder;
                    document.querySelector('input[name="payout_details"]').placeholder = methods[m].placeholder;
                    document.querySelector('input[name="payout_details"]').type = methods[m].field || 'text';
                }
            }
            select.addEventListener('change', updateHint);
            updateHint();

            document.getElementById('payout-settings-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('save-payout-btn');
                var msg = document.getElementById('payout-msg');
                btn.disabled = true; btn.innerText = '<?php echo esc_js(__('Saving...', 'obenlo')); ?>';
                var fd = new FormData(this);
                fd.append('action', 'obenlo_save_payout_settings');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if(data.success) { msg.style.color = '#10b981'; msg.innerText = '✓ ' + data.data.message; }
                    else { msg.style.color = '#ef4444'; msg.innerText = '❌ ' + data.data; }
                })
                .finally(() => { btn.disabled = false; btn.innerText = '<?php echo esc_js(__('Save Payout Preferences', 'obenlo')); ?>'; });
            });
        });
        </script>
        <?php
    }
}
