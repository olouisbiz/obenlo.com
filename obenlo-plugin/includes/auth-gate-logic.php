<?php
/**
 * Obenlo Auth-Gate Logic
 * Location: /obenlo-plugin/includes/auth-gate-logic.php
 */

if (!defined('ABSPATH')) exit;

// Shortcode to display the Auth Gate anywhere
add_shortcode('obenlo_auth_gate', function() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('obenlo_vendor', (array) $user->roles)) {
            wp_redirect(home_url('/vendor-studio'));
        } else {
            wp_redirect(home_url('/traveler-hub'));
        }
        exit;
    }
    
    // If not logged in, load the view from the theme
    ob_start();
    get_template_part('templates/view-auth-gate');
    return ob_get_clean();
});