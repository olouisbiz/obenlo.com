<?php
/**
 * Obenlo Theme Functions - Asset Loader
 */

add_action('wp_enqueue_scripts', function() {
    // Load CSS
    wp_enqueue_style('obenlo-style', get_stylesheet_uri());

    // Load JS (ensure jQuery is loaded first)
    wp_enqueue_script('obenlo-main-bridge', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0.0', true);

    // Pass PHP data to JS
    wp_localize_script('obenlo-main-bridge', 'obenlo_vars', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('obenlo_secure_nonce')
    ]);
});