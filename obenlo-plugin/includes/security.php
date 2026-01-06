<?php
/**
 * Obenlo Security & Role Intelligence
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('is_obenlo_host')) {
    function is_obenlo_host($user_id = null) {
        $user = $user_id ? get_userdata($user_id) : wp_get_current_user();
        if (!$user || !isset($user->roles)) return false;
        return in_array('obenlo_vendor', (array)$user->roles) || in_array('administrator', (array)$user->roles);
    }
}
