<?php
/** Obenlo Marketplace Functions **/
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('obenlo-style', get_stylesheet_uri(), [], time());
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@400;900&display=swap', [], null);
});

add_theme_support('post-thumbnails');
add_theme_support('title-tag');
