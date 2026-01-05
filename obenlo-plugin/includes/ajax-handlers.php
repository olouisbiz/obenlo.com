<?php
/**
 * Obenlo AJAX Handlers
 * Path: /obenlo-plugin/includes/ajax-handlers.php
 */

if (!defined('ABSPATH')) exit;

// FAVORITES TOGGLE
add_action('wp_ajax_obenlo_toggle_favorite', function() {
    if (!is_user_logged_in()) wp_send_json_error();
    $user_id = get_current_user_id();
    $host_id = intval($_POST['host_id']);
    $favs = get_user_meta($user_id, 'obenlo_fav_hosts', true) ?: [];
    
    if (in_array($host_id, $favs)) {
        $favs = array_diff($favs, [$host_id]);
        $is_fav = false;
    } else {
        $favs[] = $host_id;
        $is_fav = true;
    }
    update_user_meta($user_id, 'obenlo_fav_hosts', array_values($favs));
    wp_send_json_success(['is_fav' => $is_fav]);
});

// REPORT HOST
add_action('wp_ajax_obenlo_report_host', function() {
    $host_name = sanitize_text_field($_POST['reported_host']);
    $reason = sanitize_text_field($_POST['reason']);
    $admin_email = get_option('admin_email');
    $subject = "🚩 FLAG: Host Profile Reported - " . $host_name;
    $body = "Reason: $reason\nDetails: " . sanitize_textarea_field($_POST['details']);
    wp_mail($admin_email, $subject, $body);
    wp_send_json_success();
});