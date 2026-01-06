<?php
/**
 * Obenlo Routing Engine - Template-Based
 */
if (!defined('ABSPATH')) exit;

add_action('template_include', function($template) {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $path);
    $base_route = $segments[0];

    $routes = [
        'dashboard-host'    => 'dashboard-host.php',
        'dashboard-traveler'=> 'dashboard-traveler.php',
        'messages'          => 'message-hub.php',
        'explore'           => 'page-explore.php',
        'checkout'          => 'page-checkout.php'
    ];

    if (isset($routes[$base_route])) {
        $custom_template = get_stylesheet_directory() . '/templates/' . $routes[$base_route];
        if (file_exists($custom_template)) return $custom_template;
    }
    return $template;
});
