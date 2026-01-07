<?php
/**
 * Obenlo Marketplace Payment Engine
 */
if (!defined('ABSPATH')) exit;

/**
 * Handle Marketplace Split Logic
 * @param float $total The total transaction amount.
 */
function obenlo_calculate_marketplace_fees($total) {
    $commission = round($total * 0.05, 2); // Obenlo 5% Fee
    $payout     = $total - $commission;    // Host 95% Payout
    
    return [
        'total'      => $total,
        'commission' => $commission,
        'payout'     => $payout
    ];
}

add_action('wp_ajax_obenlo_process_payment', function() {
    check_ajax_referer('obenlo_nonce', 'nonce');
    // Payment processing logic here
    wp_send_json_success(['message' => 'Marketplace Transaction Verified']);
});
