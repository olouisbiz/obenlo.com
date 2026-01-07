<?php
/**
 * Obenlo Marketplace Routing Logic
 */
if (!defined('ABSPATH')) exit;

add_action('template_include', function($template) {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    
    $marketplace_routes = [
        'host-console'    => 'dashboard-host.php',
        'my-bookings'     => 'dashboard-traveler.php',
        'listings'        => 'page-explore.php',
        'secure-checkout' => 'page-checkout.php'
    ];

    if (isset($marketplace_routes[$path])) {
        $custom = get_stylesheet_directory() . '/templates/' . $marketplace_routes[$path];
        if (file_exists($custom)) return $custom;
    }
    return $template;
});
