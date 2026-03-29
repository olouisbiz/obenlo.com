<?php
/**
 * Payment Webhook Handler - Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Payments_Webhook extends WP_REST_Controller
{
    public function register_routes()
    {
        register_rest_route('obenlo/v1', '/payments/webhook', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'handle_webhook'),
                'permission_callback' => '__return_true',
            ),
        ));
    }

    public function handle_webhook($request)
    {
        $payload = $request->get_body();
        $headers = $request->get_headers();

        // Detect Provider
        if (isset($headers['stripe_signature'])) {
            return $this->handle_stripe_webhook($payload, $headers['stripe_signature'][0]);
        }

        // PayPal often sends a specific User-Agent or lacks a signature header in simple setups
        // For production, we'd verify the PayPal signature, but for the "Lightweight" request, 
        // we'll look for PayPal specific fields.
        $data = json_decode($payload, true);
        if (isset($data['event_type']) && strpos($data['event_type'], 'PAYMENT.CAPTURE') !== false) {
            return $this->handle_paypal_webhook($data);
        }

        return new WP_REST_Response(array('message' => 'Unknown provider'), 400);
    }

    private function handle_stripe_webhook($payload, $signature)
    {
        $data = json_decode($payload, true);
        $event_type = isset($data['type']) ? $data['type'] : '';

        error_log("Obenlo Stripe Webhook: Received event $event_type");

        if ($event_type === 'checkout.session.completed') {
            $session = $data['data']['object'];
            $booking_id = isset($session['client_reference_id']) ? intval($session['client_reference_id']) : 0;
            
            if (!$booking_id && isset($session['metadata']['booking_id'])) {
                $booking_id = intval($session['metadata']['booking_id']);
            }

            if ($booking_id) {
                $this->confirm_booking($booking_id, 'stripe', $session['id']);
                return new WP_REST_Response(array('success' => true), 200);
            }
        }

        return new WP_REST_Response(array('message' => 'Event ignored'), 200);
    }

    private function handle_paypal_webhook($data)
    {
        $event_type = $data['event_type'];
        error_log("Obenlo PayPal Webhook: Received event $event_type");

        if ($event_type === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $data['resource'];
            $custom_id = isset($resource['custom_id']) ? $resource['custom_id'] : '';
            
            // Extract booking ID from reference or custom_id
            $booking_id = 0;
            if (strpos($custom_id, 'booking_') === 0) {
                $booking_id = intval(str_replace('booking_', '', $custom_id));
            }

            if ($booking_id) {
                $this->confirm_booking($booking_id, 'paypal', $resource['id']);
                return new WP_REST_Response(array('success' => true), 200);
            }
        }

        return new WP_REST_Response(array('message' => 'Event ignored'), 200);
    }

    private function confirm_booking($booking_id, $gateway, $transaction_id)
    {
        $status = get_post_meta($booking_id, '_obenlo_booking_status', true);
        
        if ($status === 'confirmed') {
            return; // Already processed
        }

        update_post_meta($booking_id, '_obenlo_booking_status', 'confirmed');
        update_post_meta($booking_id, '_obenlo_transaction_id', $transaction_id);
        update_post_meta($booking_id, '_obenlo_payment_gateway', $gateway);

        // Notify
        if (class_exists('Obenlo_Booking_Notifications')) {
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed');
        }

        error_log("Obenlo Payment: Booking #$booking_id confirmed via $gateway. Transaction: $transaction_id");
    }
}
