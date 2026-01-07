<?php
/**
 * Plugin Name: Obenlo Marketplace Core
 * Description: High-performance engine for Host/Traveler transactions and booking security.
 * Version: 1.2.0
 */

if (!defined('ABSPATH')) exit;

define('OBENLO_PATH', plugin_dir_path(__FILE__));

function obenlo_init_marketplace() {
    $modules = [
        'database.php',
        'post-types.php',
        'security.php',
        'ajax-handlers.php',
        'stripe-engine.php',
        'routing.php'
    ];
    foreach ($modules as $m) {
        $path = OBENLO_PATH . 'includes/' . $m;
        if (file_exists($path)) require_once $path;
    }
}
add_action('plugins_loaded', 'obenlo_init_marketplace');
