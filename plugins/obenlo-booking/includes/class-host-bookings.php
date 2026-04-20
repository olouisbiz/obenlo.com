<?php
/**
 * Host Bookings Module
 * Single Responsibility: Render bookings list + handle booking actions + CSV export.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Bookings
{
    public function init()
    {
        add_action('admin_post_obenlo_dashboard_booking_action', array($this, 'handle_booking_action'));
        add_action('admin_post_obenlo_export_bookings', array($this, 'handle_export_bookings'));
    }

    private function redirect_with_error($error_code) {
        obenlo_redirect_with_error($error_code);
    }

    public function render_bookings_list($limit = -1)
    {
        $user_id = get_current_user_id();

        $args = array(
            'post_type' => 'booking',
            'posts_per_page' => $limit,
            'meta_query' => array(array('key' => '_obenlo_host_id', 'value' => $user_id)),
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => false
        );
        $bookings = get_posts($args);

        if ($limit === -1): ?>
            <div class="dashboard-header">
                <h2 class="dashboard-title"><?php echo __('My Bookings', 'obenlo'); ?></h2>
            </div>

            <div style="margin-bottom:32px; display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap;">
                <div class="search-container">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px;color:#888;margin-left:5px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="booking-code-search" class="search-input" placeholder="<?php echo esc_attr(__('Filter by ID or Conf. Code...', 'obenlo')); ?>">
                    <span id="booking-search-count" style="font-size:0.8rem; color:#888; font-weight:600; white-space:nowrap; margin-right:10px;"></span>
                </div>
                <div style="display:flex; gap:15px; align-items:center;">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="display:flex; gap:12px; align-items:center; background:#f9f9f9; padding:6px 15px; border-radius:18px; border:1.5px solid #eee;">
                        <input type="hidden" name="action" value="obenlo_export_bookings">
                        <?php wp_nonce_field('obenlo_export_bookings'); ?>
                        <span style="font-size:0.8rem; font-weight:700; color:#888; text-transform:uppercase;"><?php echo __('Date:', 'obenlo'); ?></span>
                        <input type="date" name="export_date" value="<?php echo date('Y-m-d'); ?>" style="border:none; background:transparent; font-weight:700; color:#222; outline:none; font-family:inherit;">
                        <button type="submit" class="btn-icon" title="<?php echo esc_attr(__('Export CSV', 'obenlo')); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </button>
                    </form>
                    <button onclick="document.getElementById('booking-code-search').value=''; filterBookings();" class="btn-outline" style="padding:10px 20px; font-size:0.85rem; border-color:#eee; color:#666;"><?php echo __('Reset Filters', 'obenlo'); ?></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="form-section"><p style="color:#666; font-size:1rem;"><?php echo __('You have no bookings yet.', 'obenlo'); ?></p></div>
        <?php else: ?>
            <table class="admin-table" id="bookings-table">
                <thead>
                    <tr>
                        <th><?php echo __('ID', 'obenlo'); ?></th>
                        <th><?php echo __('Listing', 'obenlo'); ?></th>
                        <th><?php echo __('Guest', 'obenlo'); ?></th>
                        <th><?php echo __('Dates', 'obenlo'); ?></th>
                        <th><?php echo __('Total', 'obenlo'); ?></th>
                        <th><?php echo __('Status', 'obenlo'); ?></th>
                        <th><?php echo __('Actions', 'obenlo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking):
                        $listing_id    = get_post_meta($booking->ID, '_obenlo_listing_id', true);
                        $listing_title = $listing_id ? get_the_title($listing_id) : __('Unknown Listing', 'obenlo');
                        $listing_thumb = $listing_id ? get_the_post_thumbnail_url($listing_id, 'thumbnail') : '';
                        $start_date    = get_post_meta($booking->ID, '_obenlo_start_date', true);
                        $end_date      = get_post_meta($booking->ID, '_obenlo_end_date', true);
                        $guests        = get_post_meta($booking->ID, '_obenlo_guests', true);
                        $total         = get_post_meta($booking->ID, '_obenlo_total_price', true);
                        $status        = get_post_meta($booking->ID, '_obenlo_booking_status', true);
                        $conf_code     = get_post_meta($booking->ID, '_obenlo_confirmation_code', true);
                        $checked_in    = get_post_meta($booking->ID, '_obenlo_checked_in', true) === 'yes';
                        $guest_user    = get_user_by('id', $booking->post_author);
                        $guest_name    = $guest_user ? $guest_user->display_name : 'Guest #' . $booking->post_author;
                        $guest_avatar  = $guest_user ? get_avatar_url($guest_user->ID) : '';
                        $status_badge  = 'badge-info';
                        if (in_array($status, ['confirmed', 'approved', 'completed'])) $status_badge = 'badge-success';
                        if (in_array($status, ['declined', 'cancelled'])) $status_badge = 'badge-danger';
                        if ($status === 'pending_payment') $status_badge = 'badge-warning';
                    ?>
                        <tr class="booking-row">
                            <td data-label="<?php echo esc_attr(__('Booking ID', 'obenlo')); ?>">
                                <span style="font-family:monospace; color:#888; font-weight:600; font-size:0.85rem;">#<?php echo $booking->ID; ?></span>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Listing', 'obenlo')); ?>">
                                <div style="display:flex; align-items:center; gap:16px;">
                                    <?php if ($listing_thumb): ?>
                                        <img src="<?php echo esc_url($listing_thumb); ?>" style="width:50px; height:50px; border-radius:12px; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:50px; height:50px; border-radius:12px; background:#f5f5f5; display:flex; align-items:center; justify-content:center; color:#ccc;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:700; color:#222; font-size:1rem; margin-bottom:2px;"><?php echo esc_html($listing_title); ?></div>
                                        <div style="font-size:0.75rem; color:#888; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;"><?php echo esc_html($conf_code ?: '───'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Guest', 'obenlo')); ?>">
                                <div class="avatar-stack">
                                    <img src="<?php echo esc_url($guest_avatar ?: get_avatar_url(0)); ?>" class="avatar-circle">
                                    <div>
                                        <div style="font-weight:700; color:#222;"><?php echo esc_html($guest_name); ?></div>
                                        <div style="font-size:0.75rem; color:#888;"><?php echo sprintf(__('%s Guests', 'obenlo'), esc_html($guests)); ?></div>
                                    </div>
                                    <?php if ($guest_user): ?>
                                        <button onclick="window.obenloStartChatWith(<?php echo $guest_user->ID; ?>, '<?php echo esc_js($guest_user->display_name); ?>', '<?php echo esc_url(get_avatar_url($guest_user->ID)); ?>')" class="btn-icon" style="width:28px; height:28px; margin-left:5px;" title="<?php echo esc_attr(__('Message Guest', 'obenlo')); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px; height:14px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Dates', 'obenlo')); ?>">
                                <div style="font-weight:700; color:#222; display:flex; align-items:center; gap:8px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px; height:14px; color:#e61e4d;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <?php echo esc_html($start_date); ?>
                                </div>
                                <?php if ($end_date && $end_date !== $start_date): ?>
                                    <div style="font-size:0.8rem; color:#888; padding-left:22px;"><?php echo __('to', 'obenlo'); ?> <?php echo esc_html($end_date); ?></div>
                                <?php endif; ?>
                                <?php
                                $pickup  = get_post_meta($booking->ID, '_obenlo_logistics_pickup', true);
                                $dropoff = get_post_meta($booking->ID, '_obenlo_logistics_dropoff', true);
                                if ($pickup || $dropoff): ?>
                                    <div style="margin-top:10px; padding:10px; background:#f0f9ff; border-radius:8px; border:1px solid #e0f2fe; font-size:0.85rem;">
                                        <div style="color:#0369a1; font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:5px;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg> Pickup</div>
                                        <div style="color:#333; margin-bottom:8px; padding-left:17px;"><?php echo esc_html($pickup ?: '──'); ?></div>
                                        <div style="color:#0369a1; font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:5px;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Drop-off</div>
                                        <div style="color:#333; padding-left:17px;"><?php echo esc_html($dropoff ?: '──'); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $msg = get_post_meta($booking->ID, '_obenlo_inquiry_message', true);
                                if ($msg): ?>
                                    <div style="margin-top:10px; padding:12px; background:#fffaf0; border-radius:12px; border:1px solid #fbd38d; font-size:0.85rem; color:#744210; font-style:italic;">
                                        <div style="font-weight:700; margin-bottom:5px; font-style:normal; font-size:0.7rem; text-transform:uppercase; color:#9c4221;">Guest Note:</div>
                                        "<?php echo esc_html($msg); ?>"
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Price', 'obenlo')); ?>">
                                <div style="font-size:1.1rem; font-weight:800; color:#222;">$<?php echo number_format(floatval($total), 2); ?></div>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Status', 'obenlo')); ?>">
                                <span class="badge <?php echo esc_attr($status_badge); ?>"><?php echo esc_html(__(str_replace('_', ' ', $status), 'obenlo')); ?></span>
                                <?php if ($checked_in): ?>
                                    <div style="margin-top:8px;"><span class="badge badge-success" style="font-size:0.65rem; padding:4px 10px;">✓ IN HOUSE</span></div>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Actions', 'obenlo')); ?>">
                                <div style="display:flex; gap:8px; align-items:center; justify-content:flex-end;">
                                    <?php
                                        $approve_url  = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=approve'), 'booking_action_' . $booking->ID);
                                        $decline_url  = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=decline'), 'booking_action_' . $booking->ID);
                                        $complete_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=complete'), 'booking_action_' . $booking->ID);

                                        if ($status === 'awaiting_quote') {
                                            echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:flex; gap:5px; align-items:center;">';
                                            echo '<input type="hidden" name="action" value="obenlo_dashboard_booking_action">';
                                            echo '<input type="hidden" name="booking_id" value="' . $booking->ID . '">';
                                            echo '<input type="hidden" name="do_action" value="send_quote">';
                                            wp_nonce_field('booking_action_' . $booking->ID);
                                            echo '<input type="number" name="quoted_price" placeholder="' . esc_attr(__('Price', 'obenlo')) . '" step="0.01" required style="width:70px; padding:6px; border:1.5px solid #eee; border-radius:8px; font-size:0.8rem;">';
                                            echo '<button type="submit" class="btn-primary" style="padding:8px 12px; font-size:0.8rem; background:#4c51bf;">' . __('Quote', 'obenlo') . '</button>';
                                            echo '</form>';
                                        }

                                        if (!in_array($status, ['declined', 'cancelled', 'completed', 'awaiting_quote'])) {
                                            if (!in_array($status, ['approved', 'confirmed'])) {
                                                echo '<a href="' . esc_url($approve_url) . '" class="btn-primary" style="padding:8px 16px; font-size:0.8rem; background:#10b981; box-shadow:none;" onclick="return confirm(\'' . esc_js(__('Approve this booking?', 'obenlo')) . '\')">' . __('Approve', 'obenlo') . '</a>';
                                                echo '<a href="' . esc_url($decline_url) . '" class="btn-outline" style="padding:7px 15px; font-size:0.8rem; border-color:#fee2e2; color:#ef4444;" onclick="return confirm(\'' . esc_js(__('Decline this booking?', 'obenlo')) . '\')">' . __('Decline', 'obenlo') . '</a>';
                                            } else {
                                                if (!$checked_in) {
                                                    $checkin_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=checkin'), 'booking_action_' . $booking->ID);
                                                    echo '<a href="' . esc_url($checkin_url) . '" class="btn-primary" style="padding:8px 16px; font-size:0.8rem; background:#e61e4d; box-shadow:none;" onclick="return confirm(\'' . esc_js(__('Check in this guest?', 'obenlo')) . '\')">' . __('Check In', 'obenlo') . '</a>';
                                                }
                                                echo '<a href="' . esc_url($complete_url) . '" class="btn-outline" style="padding:7px 15px; font-size:0.8rem;" onclick="return confirm(\'' . esc_js(__('Mark as completed?', 'obenlo')) . '\')">' . __('Complete', 'obenlo') . '</a>';
                                        }
                                    }

                                    if (in_array($status, ['confirmed', 'approved', 'completed'])) {
                                        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="margin:0; display:inline;" onsubmit="return confirm(\'' . esc_js(__('Are you sure you want to initiate a refund?', 'obenlo')) . '\');">';
                                        echo '<input type="hidden" name="action" value="obenlo_initiate_refund">';
                                        echo '<input type="hidden" name="booking_id" value="' . $booking->ID . '">';
                                        wp_nonce_field('initiate_refund', 'refund_nonce');
                                        echo '<button type="submit" style="background:none; border:none; color:#ef4444; font-weight:700; font-size:0.8rem; cursor:pointer; padding:0; text-decoration:underline; margin-left:8px;">' . __('Refund', 'obenlo') . '</button>';
                                        echo '</form>';
                                    }

                                    if (in_array($status, ['declined', 'cancelled', 'completed']) && !in_array($status, ['confirmed', 'approved'])) {
                                        echo '<span style="color:#bbb; font-weight:600; font-size:0.8rem; text-transform:uppercase;">Archived</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <script>
            function filterBookings() {
                var input = document.getElementById('booking-code-search');
                var filter = input.value.toUpperCase();
                var table = document.getElementById('bookings-table');
                var tr = table.getElementsByTagName('tr');
                var count = 0;
                for (var i = 1; i < tr.length; i++) {
                    if (tr[i].textContent.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; count++;
                    } else { tr[i].style.display = "none"; }
                }
                var cd = document.getElementById('booking-search-count');
                cd.textContent = filter === "" ? "" : "<?php echo esc_js(__('Found', 'obenlo')); ?> " + count + " <?php echo esc_js(__('booking(s)', 'obenlo')); ?>";
            }
            if (document.getElementById('booking-code-search')) {
                document.getElementById('booking-code-search').addEventListener('keyup', filterBookings);
            }
            </script>
        <?php endif;
    }

    public function handle_booking_action()
    {
        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $do_action  = isset($_GET['do_action']) ? sanitize_text_field($_GET['do_action']) : '';

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'booking_action_' . $booking_id)) {
            $this->redirect_with_error('security_failed');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            $this->redirect_with_error('invalid_booking');
        }

        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
        if ($host_id != get_current_user_id() && !current_user_can('administrator')) {
            $this->redirect_with_error('unauthorized');
        }

        if ($do_action === 'approve') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'approved');
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed');
        } elseif ($do_action === 'send_quote') {
            $price = isset($_POST['quoted_price']) ? floatval($_POST['quoted_price']) : 0;
            if ($price <= 0) $this->redirect_with_error('invalid_price');
            
            update_post_meta($booking_id, '_obenlo_total_price', $price);
            update_post_meta($booking_id, '_obenlo_booking_status', 'quote_sent');
            
            // Notify Guest
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'quote_received');
            
        } elseif ($do_action === 'complete') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'completed');
            $payments = new Obenlo_Booking_Payments();
            $payments->calculate_platform_fee($booking_id);
            $payments->release_booking_payout($booking_id);
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_completed');
        } elseif ($do_action === 'decline') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'declined');
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_cancelled');
        } elseif ($do_action === 'checkin') {
            if (get_post_meta($booking_id, '_obenlo_checked_in', true) !== 'yes') {
                update_post_meta($booking_id, '_obenlo_checked_in', 'yes');
            }
        }

        $redirect_url = remove_query_arg(array('booking_id', 'do_action', '_wpnonce'), wp_get_referer());
        wp_safe_redirect($redirect_url);
        exit;
    }

    public function handle_export_bookings()
    {
        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            obenlo_redirect_with_error('unauthorized');
        }

        check_admin_referer('obenlo_export_bookings');

        $export_date = isset($_POST['export_date']) ? sanitize_text_field($_POST['export_date']) : '';
        if (!$export_date) {
            obenlo_redirect_with_error('invalid_date');
        }

        $user_id  = get_current_user_id();
        $bookings = get_posts(array(
            'post_type'      => 'booking',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array('key' => '_obenlo_host_id', 'value' => $user_id),
                array('key' => '_obenlo_start_date', 'value' => $export_date),
            ),
            'suppress_filters' => false
        ));

        if (empty($bookings)) {
            obenlo_redirect_with_error('no_bookings');
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings-' . $export_date . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Booking ID', 'Listing', 'Guest Name', 'Guest Email', 'Start Date', 'End Date', 'Guests', 'Total', 'Status', 'Conf. Code', 'Pickup Address', 'Dropoff Address'));

        foreach ($bookings as $booking) {
            $lid          = get_post_meta($booking->ID, '_obenlo_listing_id', true);
            $listing_title = $lid ? get_the_title($lid) : 'Unknown';
            $guest_user   = get_user_by('id', $booking->post_author);
            fputcsv($output, array(
                $booking->ID,
                $listing_title,
                $guest_user ? $guest_user->display_name : 'Guest #' . $booking->post_author,
                $guest_user ? $guest_user->user_email : 'N/A',
                get_post_meta($booking->ID, '_obenlo_start_date', true),
                get_post_meta($booking->ID, '_obenlo_end_date', true) ?: 'N/A',
                get_post_meta($booking->ID, '_obenlo_guests', true),
                get_post_meta($booking->ID, '_obenlo_total_price', true),
                get_post_meta($booking->ID, '_obenlo_booking_status', true),
                get_post_meta($booking->ID, '_obenlo_confirmation_code', true),
                get_post_meta($booking->ID, '_obenlo_logistics_pickup', true) ?: 'N/A',
                get_post_meta($booking->ID, '_obenlo_logistics_dropoff', true) ?: 'N/A',
            ));
        }

        fclose($output);
        exit;
    }
}
