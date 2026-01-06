<?php
/**
 * Plugin Name: Obenlo Core
 * Description: Unified Command Center for SES Management, Routing, and Auth.
 * Version: 1.1.0
 */

if (!defined('ABSPATH')) exit;

define('OBENLO_PATH', plugin_dir_path(__FILE__));

function obenlo_load_modules() {
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
obenlo_load_modules();
