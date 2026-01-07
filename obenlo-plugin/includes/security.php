<?php
/**
 * Obenlo Marketplace Security & Role Management
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('obenlo_get_user_role')) {
    function obenlo_get_user_role($user_id = null) {
        $user = $user_id ? get_userdata($user_id) : wp_get_current_user();
        if (!$user || !isset($user->roles)) return 'guest';
        
        if (in_array('administrator', (array)$user->roles) || in_array('obenlo_host', (array)$user->roles)) {
            return 'host';
        }
        return 'traveler';
    }
}

function is_obenlo_host() {
    return obenlo_get_user_role() === 'host';
}
