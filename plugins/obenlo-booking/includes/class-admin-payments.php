<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Payments
{
    public function init()
    {
        add_action('admin_post_obenlo_save_payment_settings', array($this, 'handle_save_payment_settings'));
        add_action('admin_post_obenlo_process_payout', array($this, 'handle_process_payout'));
        add_action('admin_post_obenlo_admin_refund_action', array($this, 'handle_admin_refund_action'));
    }

    public function render_payments_tab()
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

        <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:700;">Save Payment Settings</button>
            </form>
        </div>
        <?php
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

        // Visibility Toggles Save
        update_option('obenlo_stripe_enabled', isset($_POST['stripe_enabled']) ? 'yes' : 'no');
        update_option('obenlo_paypal_enabled', isset($_POST['paypal_enabled']) ? 'yes' : 'no');

        wp_safe_redirect(add_query_arg('tab', 'payments', wp_get_referer()));
        exit;
    }

    public function render_payout_management_tab()
    {
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">';
        echo '<div>';
        echo '<h3 style="margin:0;">Host Payout Management</h3>';
        echo '<p style="color:#666; margin-top:5px;">Process withdrawal requests and manage host payment methods.</p>';
        echo '</div>';
        echo '</div>';

        if (isset($_GET['sync_status'])) {
            $color = ($_GET['sync_status'] === 'success') ? '#155724' : '#721c24';
            $bg = ($_GET['sync_status'] === 'success') ? '#d4edda' : '#f8d7da';
            $border = ($_GET['sync_status'] === 'success') ? '#c3e6cb' : '#f5c6cb';
            echo '<div style="background:' . $bg . '; color:' . $color . '; border:1px solid ' . $border . '; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600;">' . esc_html(urldecode($_GET['sync_msg'])) . '</div>';
        }

        ?>
        <h4 style="margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <span style="font-size:1.5rem;">📥</span> Pending Payout Requests
        </h4>
        <?php
        $pending_payouts = get_posts(array(
            'post_type' => 'obenlo_payout_req',
            'meta_key' => '_status',
            'meta_value' => 'pending',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        if (empty($pending_payouts)):
            echo '<div style="background:#f9f9f9; padding:40px; text-align:center; border-radius:12px; color:#999; border:1px dashed #ddd;">No pending payout requests at this time.</div>';
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
                        <td data-label="Host">
                            <strong><?php echo esc_html($host->display_name); ?></strong><br>
                            <small><?php echo esc_html($host->user_email); ?></small>
                        </td>
                        <td data-label="Amount" style="font-size:1.2em; font-weight:700; color:#10b981;">$<?php echo number_format($amount, 2); ?></td>
                        <td data-label="Method / Details">
                            <span class="badge badge-guest" style="text-transform:uppercase;"><?php echo esc_html($method); ?></span><br>
                            <code><?php echo esc_html($details); ?></code>
                        </td>
                        <td data-label="Date Requested"><?php echo get_the_date('', $payout); ?></td>
                        <td data-label="Actions">
                            <div style="display:flex; gap:10px; align-items:center;">
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:flex; gap:5px; margin:0;">
                                    <input type="hidden" name="action" value="obenlo_process_payout">
                                    <input type="hidden" name="payout_id" value="<?php echo $payout->ID; ?>">
                                    <input type="hidden" name="payout_status" value="paid">
                                    <?php wp_nonce_field('process_payout_' . $payout->ID, 'security'); ?>
                                    <input type="text" name="transaction_id" placeholder="TX ID" style="width:100px; padding:6px; border-radius:6px; border:1px solid #ddd; font-size:0.85rem;">
                                    <button type="submit" style="background:#222; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.85rem;" onclick="return confirm('Confirm manual payout?')">Mark Paid</button>
                                </form>
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin:0;">
                                    <input type="hidden" name="action" value="obenlo_process_payout">
                                    <input type="hidden" name="payout_id" value="<?php echo $payout->ID; ?>">
                                    <input type="hidden" name="payout_status" value="cancelled">
                                    <?php wp_nonce_field('process_payout_' . $payout->ID, 'security'); ?>
                                    <button type="submit" style="background:none; border:none; color:#e61e4d; cursor:pointer; text-decoration:underline; font-weight:600; font-size:0.85rem;" onclick="return confirm('Reject this request?')">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h4 style="margin-top:50px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <span style="font-size:1.5rem;">📜</span> Recent Processed Payouts
        </h4>
        <?php
        $recent_payouts = get_posts(array(
            'post_type' => 'obenlo_payout_req',
            'meta_key' => '_status',
            'meta_value' => 'paid',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        ));

        if (empty($recent_payouts)):
            echo '<p style="color:#666; font-style:italic;">No recently processed payouts.</p>';
        else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Host</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date Paid</th>
                        <th>TX ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payouts as $payout): 
                        $host = get_userdata($payout->post_author);
                    ?>
                    <tr>
                        <td data-label="Host"><?php echo esc_html($host ? $host->display_name : 'Deleted User'); ?></td>
                        <td data-label="Amount" style="font-weight:700;">$<?php echo number_format(get_post_meta($payout->ID, '_amount', true), 2); ?></td>
                        <td data-label="Method"><span class="badge badge-guest"><?php echo esc_html(strtoupper(get_post_meta($payout->ID, '_method', true))); ?></span></td>
                        <td data-label="Date Paid"><?php echo get_post_meta($payout->ID, '_paid_date', true); ?></td>
                        <td data-label="TX ID"><code><?php echo esc_html(get_post_meta($payout->ID, '_transaction_id', true)); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }

    public function render_refunds_tab()
    {
        $refunds = get_posts(array(
            'post_type' => 'refund',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        echo '<h3>Refund Management</h3>';
        echo '<table class="admin-table">';
        echo '<tr><th>Booking ID</th><th>Guest</th><th>Reason</th><th>Status</th><th>Actions</th></tr>';

        if (empty($refunds)) {
            echo '<tr><td colspan="5">No refund requests found.</td></tr>';
        } else {
            foreach ($refunds as $refund) {
                $booking_id = get_post_meta($refund->ID, '_obenlo_booking_id', true);
                $status = get_post_meta($refund->ID, '_obenlo_refund_status', true);
                $guest = get_userdata($refund->post_author);

                $status_bg = '#f3f4f6'; $status_color = '#374151';
                if ($status === 'completed' || $status === 'approved') { $status_bg = '#dcfce7'; $status_color = '#166534'; }
                if ($status === 'pending') { $status_bg = '#fef9c3'; $status_color = '#854d0e'; }
                if ($status === 'rejected') { $status_bg = '#fee2e2'; $status_color = '#991b1b'; }

                echo '<tr>';
                echo '<td data-label="Booking ID">#' . esc_html($booking_id) . '</td>';
                echo '<td data-label="Guest">' . ($guest ? esc_html($guest->display_name) : 'Unknown') . '</td>';
                echo '<td data-label="Reason">' . esc_html($refund->post_content) . '</td>';
                echo '<td data-label="Status"><span style="background:'.$status_bg.'; color:'.$status_color.'; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">' . esc_html($status) . '</span></td>';
                echo '<td data-label="Actions">';
                if ($status === 'pending') {
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="action" value="obenlo_admin_refund_action">';
                    echo '<input type="hidden" name="refund_id" value="' . $refund->ID . '">';
                    echo '<input type="hidden" name="refund_status" value="approved">';
                    echo '<button type="submit" class="btn-approve" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">Approve</button>';
                    echo '</form> | ';
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="action" value="obenlo_admin_refund_action">';
                    echo '<input type="hidden" name="refund_id" value="' . $refund->ID . '">';
                    echo '<input type="hidden" name="refund_status" value="rejected">';
                    echo '<button type="submit" class="btn-reject" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">Reject</button>';
                    echo '</form>';
                } else {
                    echo 'Processed';
                }
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    public function handle_process_payout()
    {
        if (!current_user_can('administrator')) wp_die('Security restricted.');
        $payout_id = intval($_POST['payout_id']);
        check_admin_referer('process_payout_' . $payout_id, 'security');

        $status = sanitize_text_field($_POST['payout_status']);
        $tx_id = isset($_POST['transaction_id']) ? sanitize_text_field($_POST['transaction_id']) : '';

        update_post_meta($payout_id, '_status', $status);
        if ($status === 'paid') {
            update_post_meta($payout_id, '_paid_date', date('Y-m-d H:i:s'));
            update_post_meta($payout_id, '_transaction_id', $tx_id);
        }

        wp_safe_redirect(add_query_arg('tab', 'payouts', wp_get_referer()));
        exit;
    }

    public function handle_admin_refund_action()
    {
        if (!current_user_can('administrator')) wp_die('Security restricted.');
        $refund_id = intval($_POST['refund_id']);
        $status = sanitize_text_field($_POST['refund_status']);

        update_post_meta($refund_id, '_obenlo_refund_status', $status);
        
        // If approved, mark booking as refunded
        if ($status === 'approved') {
            $booking_id = get_post_meta($refund_id, '_obenlo_booking_id', true);
            update_post_meta($booking_id, '_obenlo_booking_status', 'refunded');
        }

        wp_safe_redirect(add_query_arg('tab', 'refunds', wp_get_referer()));
        exit;
    }
}
