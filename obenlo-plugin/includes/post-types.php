<?php
/**
 * Obenlo Marketplace Post Types
 */
if (!defined('ABSPATH')) exit;

add_action('init', function() {
    // Marketplace Inventory: Stays, Experiences, Services
    register_post_type('obenlo_asset', [
        'labels'      => ['name' => 'Marketplace Assets', 'singular_name' => 'Asset'],
        'public'      => true,
        'has_archive' => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'author'],
        'menu_icon'   => 'dashicons-store',
        'show_in_rest' => true,
        'rewrite'     => ['slug' => 'listing']
    ]);

    // Transactional Records: The bridge between Host and Traveler
    register_post_type('obenlo_booking', [
        'labels'      => ['name' => 'Bookings', 'singular_name' => 'Booking'],
        'public'      => false,
        'show_ui'     => true,
        'supports'    => ['title', 'author', 'custom-fields'],
        'menu_icon'   => 'dashicons-calendar-alt'
    ]);
});
