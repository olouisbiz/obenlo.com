<?php
/**
 * Plugin Name: Obenlo SEO
 * Description: Premium, lightweight SEO engine for Obenlo. Automates Meta Tags, OpenGraph, and JSON-LD Schema.
 * Version: 1.7.1
 * Author: Obenlo
 * Author URI: https://obenlo.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OBENLO_SEO_VERSION', '1.7.1');
define('OBENLO_SEO_DIR', plugin_dir_path(__FILE__));
define('OBENLO_SEO_URL', plugin_dir_url(__FILE__));

// Include logic classes
require_once OBENLO_SEO_DIR . 'includes/class-seo-head.php';
require_once OBENLO_SEO_DIR . 'includes/class-seo-schema.php';
require_once OBENLO_SEO_DIR . 'includes/class-seo-sitemap.php';

/**
 * Initialize Obenlo SEO
 */
function obenlo_seo_init() {
    $head = new Obenlo_SEO_Head();
    $head->init();

    $schema = new Obenlo_SEO_Schema();
    $schema->init();

    $sitemap = new Obenlo_SEO_Sitemap();
    $sitemap->init();
}
add_action('plugins_loaded', 'obenlo_seo_init');
