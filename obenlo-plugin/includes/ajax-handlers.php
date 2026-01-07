<?php
/**
 * Obenlo Marketplace AJAX Engine
 */
if (!defined('ABSPATH')) exit;

// 1. SIGNAL SYSTEM: Buyer to Seller Inquiry
add_action('wp_ajax_obenlo_send_signal', function() {
    check_ajax_referer('obenlo_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Login required');

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'obenlo_signals', [
        'buyer_id'  => get_current_user_id(),
        'seller_id' => intval($_POST['seller_id']),
        'asset_id'  => intval($_POST['asset_id']),
        'message'   => sanitize_textarea_field($_POST['message']),
        'created_at'=> current_time('mysql')
    ]);
    wp_send_json_success('Signal transmitted to Host');
});

// 2. MARKETPLACE FILTER: Discovery Logic
add_action('wp_ajax_nopriv_obenlo_filter_assets', 'handle_obenlo_asset_filter');
add_action('wp_ajax_obenlo_filter_assets', 'handle_obenlo_asset_filter');
function handle_obenlo_asset_filter() {
    // Logic for filtering marketplace inventory by price, location, or type
    wp_send_json_success();
    wp_die();
}
