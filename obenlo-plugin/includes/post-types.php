<?php
/**
 * Obenlo Custom Post Types
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('obenlo_register_post_types')) {
    function obenlo_register_post_types() {
        register_post_type('obenlo_listing', [
            'labels'      => ['name' => 'SES Listings', 'singular_name' => 'Listing'],
            'public'      => true,
            'has_archive' => true,
            'menu_icon'   => 'dashicons-palmtree',
            'supports'    => ['title', 'editor', 'thumbnail', 'author'],
            'show_in_rest' => true,
            'rewrite'     => ['slug' => 'experience'],
        ]);
    }
    add_action('init', 'obenlo_register_post_types');
}