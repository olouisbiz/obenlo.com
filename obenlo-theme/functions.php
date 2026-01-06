<?php
/**
 * Obenlo Theme Functions - Lean UI Layer
 */
if (!defined('ABSPATH')) exit;

// Enqueue Tailwind and Google Fonts
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('obenlo-style', get_stylesheet_uri(), [], '1.1.0');
    wp_enqueue_style('obenlo-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap', [], null);
});

// Theme Support
add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
});
