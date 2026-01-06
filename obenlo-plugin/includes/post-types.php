<?php
/**
 * Obenlo Asset Registrations - Template-Based Logic
 */
if (!defined('ABSPATH')) exit;

add_action('init', function() {
    // 1. SES ASSETS: The core products of Obenlo
    register_post_type('obenlo_ses', [
        'labels'      => ['name' => 'Obenlo Assets', 'singular_name' => 'Asset'],
        'public'      => true,
        'has_archive' => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'author'],
        'menu_icon'   => 'dashicons-admin-home',
        'show_in_rest' => true,
        'rewrite'     => ['slug' => 'explore-asset']
    ]);

    // 2. BOOKINGS: Internal record tracking
    register_post_type('obenlo_booking', [
        'labels'      => ['name' => 'Bookings'],
        'public'      => false,
        'show_ui'     => true,
        'supports'    => ['title', 'author', 'custom-fields'],
        'menu_icon'   => 'dashicons-calendar-alt'
    ]);
});
