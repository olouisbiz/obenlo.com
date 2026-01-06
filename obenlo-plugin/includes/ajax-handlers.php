<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. MESSAGING (Airbnb/Booking Hybrid)
 */
add_action('wp_ajax_obenlo_send_message', function() {
    check_ajax_referer('obenlo_nonce', 'nonce');
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'obenlo_messages', [
        'sender_id'   => get_current_user_id(),
        'receiver_id' => intval($_POST['receiver_id']),
        'message_text'=> sanitize_textarea_field($_POST['message']),
        'sent_at'     => current_time('mysql')
    ]);
    wp_send_json_success();
});

/**
 * 2. SEARCH & FILTER
 */
add_action('wp_ajax_nopriv_obenlo_filter', 'handle_obenlo_filter');
add_action('wp_ajax_obenlo_filter', 'handle_obenlo_filter');
function handle_obenlo_filter() {
    // Hybrid Filter Logic here
    wp_die();
}
