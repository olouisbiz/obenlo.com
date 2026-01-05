<?php
/**
 * Obenlo Template Router
 * Location: /obenlo-plugin/includes/routing.php
 */

if (!defined('ABSPATH')) exit;

add_filter('template_include', function($template) {
    // 1. If viewing an SES Listing, force the Single Listing Template
    if (is_singular('obenlo_listing')) {
        $new_template = locate_template(['single-obenlo_listing.php']);
        return ($new_template) ? $new_template : $template;
    }

    // 2. If viewing the 'Account' page, route by Role
    if (is_page('account')) {
        if (!is_user_logged_in()) {
            return locate_template(['templates/view-auth-gate.php']);
        }
        
        $user = wp_get_current_user();
        if (in_array('obenlo_vendor', (array) $user->roles)) {
            return locate_template(['templates/dashboard-host.php']);
        } else {
            return locate_template(['templates/dashboard-traveler.php']);
        }
    }

    return $template;
});