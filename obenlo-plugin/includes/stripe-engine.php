<?php
/**
 * Obenlo Unified Stripe Engine
 * Handles Checkout Sessions and Webhook Confirmations
 */

if (!defined('ABSPATH')) exit;

// 1. CREATE CHECKOUT SESSION
add_action('wp_ajax_obenlo_checkout', 'handle_obenlo_checkout');
add_action('wp_ajax_nopriv_obenlo_checkout', 'handle_obenlo_checkout');

function handle_obenlo_checkout() {
    $secret = get_option('obenlo_stripe_secret');
    if (!$secret) wp_send_json_error('Stripe not configured.');

    require_once(OBENLO_PATH . 'vendor/autoload.php');
    \Stripe\Stripe::setApiKey($secret);

    $listing_id = intval($_POST['listing_id']);
    $price      = get_post_meta($listing_id, '_obenlo_price', true);
    
    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => get_the_title($listing_id)],
                    'unit_amount' => $price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => home_url('/booking-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'  => get_permalink($listing_id),
            'metadata' => [
                'listing_id'  => $listing_id,
                'customer_id' => get_current_user_id()
            ]
        ]);
        wp_send_json_success(['url' => $session->url]);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

// 2. WEBHOOK LISTENER
add_action('init', function() {
    if (isset($_GET['obenlo-listener']) && $_GET['obenlo-listener'] === 'stripe') {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $webhook_secret = get_option('obenlo_stripe_webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $webhook_secret);
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                // Logic to update user meta with booking details goes here
            }
            status_header(200);
        } catch(Exception $e) {
            status_header(400);
        }
        exit();
    }
});