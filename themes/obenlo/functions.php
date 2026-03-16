<?php
/**
 * Obenlo functions and definitions
 */

function obenlo_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'obenlo_setup');

function obenlo_scripts()
{
    wp_enqueue_style('obenlo-style', get_stylesheet_uri(), array(), '1.0.1');

    // Google Fonts for a premium feel (Inter)
    wp_enqueue_style('obenlo-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null);

    // Wishlist Script
    wp_enqueue_script('obenlo-wishlist', get_template_directory_uri() . '/assets/js/wishlist.js', array('jquery'), '1.0.0', true);
    wp_localize_script('obenlo-wishlist', 'obenlo_wishlist', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('obenlo_wishlist_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'obenlo_scripts');

/**
 * Performance Optimizations
 */

// Disable the emoji's
function obenlo_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'obenlo_disable_emojis');

// Remove generator tag and other junk
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

/**
 * Serve Service Worker dynamically from root domain
 */
function obenlo_serve_sw()
{
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    // Check if the request is for /sw.js or /manifest.json
    if (preg_match('#^/(sw\.js|manifest\.json)#', $request_uri)) {
        if (strpos($request_uri, 'sw.js') !== false) {
            header('Content-Type: application/javascript; charset=utf-8');
        } else {
            header('Content-Type: application/json; charset=utf-8');
        }
        header('Service-Worker-Allowed: /');
        $file = (strpos($request_uri, 'sw.js') !== false) ? '/sw.js' : '/manifest.json';
        readfile(get_template_directory() . $file);
        exit;
    }
}
add_action('parse_request', 'obenlo_serve_sw', 1);
