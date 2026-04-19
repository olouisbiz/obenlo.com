<?php
/**
 * Shared Helpers — Global utility functions available to all Obenlo modules.
 * Single Responsibility: Cross-cutting utilities (error redirect, etc.)
 */

if (!defined('ABSPATH')) exit;

/**
 * Redirect to the previous page with a whitelisted error code in the URL.
 */
if (!function_exists('obenlo_redirect_with_error')) {
    function obenlo_redirect_with_error($error_code) {
        $redirect_url = remove_query_arg(array('obenlo_error', 'message'), wp_get_referer() ?: home_url('/host-dashboard'));
        $redirect_url = add_query_arg('obenlo_error', sanitize_key($error_code), $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
}
