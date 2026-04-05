<?php
/**
 * Refund Management Logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Refunds
{
    public function init()
    {
        add_action('admin_post_obenlo_request_refund', array($this, 'handle_request_refund'));
        add_action('admin_post_obenlo_initiate_refund', array($this, 'handle_initiate_refund'));
        add_action('admin_post_obenlo_cancel_booking', array($this, 'handle_cancel_booking'));
        add_action('admin_post_obenlo_host_refund_action', array($this, 'handle_host_refund_action'));
        add_action('admin_post_obenlo_admin_refund_action', array($this, 'handle_admin_refund_action'));
    }

    public function handle_request_refund()
    {
        if (!isset($_POST['refund_nonce']) || !wp_verify_nonce($_POST['refund_nonce'], 'request_refund')) {
            $this->redirect_with_error('security_failed');
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $reason = isset($_POST['refund_reason']) ? sanitize_textarea_field($_POST['refund_reason']) : '';

        if (!$booking_id || empty($reason)) {
            $this->redirect_with_error('invalid_data');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_author != get_current_user_id()) {
            $this->redirect_with_error('unauthorized');
        }

        // Create Refund Request (CPT)
        $refund_id = wp_insert_post(array(
            'post_title' => 'Refund Request for Booking #' . $booking_id,
            'post_content' => $reason,
            'post_status' => 'publish',
            'post_type' => 'refund',
            'post_author' => get_current_user_id()
        ));

        update_post_meta($refund_id, '_obenlo_booking_id', $booking_id);
        update_post_meta($refund_id, '_obenlo_refund_status', 'pending');
        update_post_meta($booking_id, '_obenlo_refund_requested', 'yes');
        update_post_meta($booking_id, '_obenlo_refund_id', $refund_id);

        wp_safe_redirect(home_url('/account?tab=refunds&message=refund_requested'));
        exit;
    }

    public function handle_initiate_refund()
    {
        if (!isset($_POST['refund_nonce']) || !wp_verify_nonce($_POST['refund_nonce'], 'initiate_refund')) {
            $this->redirect_with_error('security_failed');
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $reason = isset($_POST['refund_reason']) ? sanitize_textarea_field($_POST['refund_reason']) : 'Host initiated refund';

        if (!$booking_id) {
            $this->redirect_with_error('invalid_data');
        }

        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
        if ($host_id != get_current_user_id() && !current_user_can('administrator')) {
            $this->redirect_with_error('unauthorized');
        }

        // Create Refund Request (CPT)
        $refund_id = wp_insert_post(array(
            'post_title' => 'Refund Initiated for Booking #' . $booking_id,
            'post_content' => $reason,
            'post_status' => 'publish',
            'post_type' => 'refund',
            'post_author' => get_current_user_id()
        ));

        update_post_meta($refund_id, '_obenlo_booking_id', $booking_id);
        update_post_meta($refund_id, '_obenlo_refund_status', 'pending'); 
        update_post_meta($booking_id, '_obenlo_refund_initiated', 'yes');
        update_post_meta($booking_id, '_obenlo_refund_id', $refund_id);

        wp_safe_redirect(home_url('/host-dashboard?action=refunds&message=refund_initiated'));
        exit;
    }

    public function handle_cancel_booking()
    {
        if (!isset($_POST['cancel_nonce']) || !wp_verify_nonce($_POST['cancel_nonce'], 'cancel_booking')) {
            $this->redirect_with_error('security_failed');
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        if (!$booking_id) {
            $this->redirect_with_error('invalid_data');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_author != get_current_user_id()) {
            $this->redirect_with_error('unauthorized');
        }

        $status = get_post_meta($booking_id, '_obenlo_booking_status', true);
        if (!in_array($status, ['pending', 'pending_payment'])) {
            // If already confirmed, they should request a refund instead
            $this->redirect_with_error('cannot_cancel_confirmed');
        }

        update_post_meta($booking_id, '_obenlo_booking_status', 'cancelled');
        Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_cancelled');

        wp_safe_redirect(home_url('/account?tab=trips&message=booking_cancelled'));
        exit;
    }

    public function handle_host_refund_action()
    {
        if (!isset($_POST['host_refund_nonce']) || !wp_verify_nonce($_POST['host_refund_nonce'], 'host_refund_action')) {
            $this->redirect_with_error('security_failed');
        }

        $refund_id = isset($_POST['refund_id']) ? intval($_POST['refund_id']) : 0;
        $status = isset($_POST['refund_status']) ? sanitize_text_field($_POST['refund_status']) : '';

        if (!$refund_id || !in_array($status, ['approved', 'rejected'])) {
            $this->redirect_with_error('invalid_data');
        }

        $booking_id = get_post_meta($refund_id, '_obenlo_booking_id', true);
        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);

        if ($host_id != get_current_user_id()) {
            $this->redirect_with_error('unauthorized');
        }

        if ($status === 'approved') {
            update_post_meta($refund_id, '_obenlo_refund_status', 'completed');
            update_post_meta($booking_id, '_obenlo_booking_status', 'refunded');

            // Logic to deduct from host balance
            $net_earnings = floatval(get_post_meta($booking_id, '_obenlo_booking_net_earnings', true));
            if ($net_earnings > 0) {
                $current_balance = floatval(get_user_meta($host_id, '_obenlo_host_balance', true));
                update_user_meta($host_id, '_obenlo_host_balance', $current_balance - $net_earnings);
            }
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'refund_approved');
        } else {
            update_post_meta($refund_id, '_obenlo_refund_status', 'rejected');
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'refund_rejected');
        }

        wp_safe_redirect(home_url('/host-dashboard?action=refunds&message=refund_updated'));
        exit;
    }

    public function handle_admin_refund_action()
    {
        if (!current_user_can('manage_options')) {
            $this->redirect_with_error('unauthorized');
        }

        $refund_id = isset($_POST['refund_id']) ? intval($_POST['refund_id']) : 0;
        $status = isset($_POST['refund_status']) ? sanitize_text_field($_POST['refund_status']) : ''; 

        if (!$refund_id || !in_array($status, ['approved', 'rejected'])) {
            $this->redirect_with_error('invalid_data');
        }

        $booking_id = get_post_meta($refund_id, '_obenlo_booking_id', true);

        if ($status === 'approved') {
            update_post_meta($refund_id, '_obenlo_refund_status', 'completed');
            update_post_meta($booking_id, '_obenlo_booking_status', 'refunded');
            
            // Logic to deduct from host balance
            $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
            $net_earnings = floatval(get_post_meta($booking_id, '_obenlo_booking_net_earnings', true));
            if ($net_earnings > 0) {
                $current_balance = floatval(get_user_meta($host_id, '_obenlo_host_balance', true));
                update_user_meta($host_id, '_obenlo_host_balance', $current_balance - $net_earnings);
            }
        } else {
            update_post_meta($refund_id, '_obenlo_refund_status', 'rejected');
        }

        wp_safe_redirect(add_query_arg('tab', 'refunds', admin_url('admin.php?page=obenlo-admin-dashboard')));
        exit;
    }

    private function redirect_with_error($error_code) {
        if (function_exists('obenlo_redirect_with_error')) {
            obenlo_redirect_with_error($error_code);
        } else {
            wp_die('Error: ' . $error_code);
        }
    }
}
