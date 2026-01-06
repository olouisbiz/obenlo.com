<?php
/**
 * Obenlo Stripe Engine - Hybrid Marketplace Payments
 */
if (!defined('ABSPATH')) exit;

/**
 * Calculate Marketplace Splits
 * Ensures Obenlo keeps 5% and Host receives 95%
 */
function obenlo_calculate_payment_split($total_amount) {
    $platform_fee = round($total_amount * 0.05, 2);
    $host_amount   = $total_amount - $platform_fee;
    
    return [
        'total'    => $total_amount,
        'platform' => $platform_fee,
        'host'     => $host_amount
    ];
}

/**
 * Initialize Payment Intent (AJAX)
 */
add_action('wp_ajax_obenlo_init_checkout', function() {
    check_ajax_referer('obenlo_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('Authentication required');
    }

    $asset_id = intval($_POST['asset_id']);
    $price    = get_post_meta($asset_id, 'obenlo_price', true);
    
    // Split Logic
    $splits = obenlo_calculate_payment_split($price);

    // This is where we would call the Stripe SDK:
    // \Stripe\PaymentIntent::create([...]);

    wp_send_json_success([
        'message' => 'Payment Intent Created',
        'splits'  => $splits
    ]);
});
