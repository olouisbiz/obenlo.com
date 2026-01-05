<?php
/**
 * Obenlo Auth Hub
 * Manages Signups, Roles, and Auto-Login
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_obenlo_user_signup', 'handle_obenlo_signup');
add_action('wp_ajax_nopriv_obenlo_user_signup', 'handle_obenlo_signup');

function handle_obenlo_signup() {
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $role     = sanitize_text_field($_POST['role']); // 'subscriber' or 'obenlo_vendor'

    if (email_exists($email)) wp_send_json_error('Email already exists.');

    $user_id = wp_create_user($email, $password, $email);
    
    if (!is_wp_error($user_id)) {
        $user = new WP_User($user_id);
        $user->set_role($role);
        
        // Auto-login
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success(['redirect' => ($role === 'obenlo_vendor') ? home_url('/vendor-studio') : home_url('/my-bookings')]);
    }
    wp_send_json_error('Registration failed.');
}