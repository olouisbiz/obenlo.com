<?php
/**
 * Template Name: Community
 */

get_header(); ?>

<div class="static-page-header" style="background: #e61e4d; padding: 100px 20px; text-align: center; color: #fff;">
    <h1 style="font-size: 3.5rem; margin-bottom: 20px;"><?php esc_html_e( 'Our Community', 'obenlo' ); ?></h1>
    <p style="font-size: 1.3rem; max-width: 800px; margin: 0 auto;"><?php esc_html_e( 'The heart of Obenlo is our diverse global community of hosts and travelers.', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 1000px; margin: 0 auto; padding: 80px 20px; text-align: center;">
    <h2 style="font-size: 2.2rem; margin-bottom: 30px;"><?php esc_html_e( 'Coming Soon', 'obenlo' ); ?></h2>
    <p style="font-size: 1.1rem; color: #666; margin-bottom: 50px;"><?php esc_html_e( 'We are building a space for our community to connect, share stories, and grow together. Stay tuned for forum features, local meetups, and host spotlights.', 'obenlo' ); ?></p>
    
    <div style="background: #f7f7f7; padding: 60px; border-radius: 30px;">
        <h3 style="margin-bottom: 20px;"><?php esc_html_e( 'Join the conversation on social media', 'obenlo' ); ?></h3>
        <div style="display: flex; justify-content: center; gap: 30px; font-size: 1.2rem;">
            <a href="https://instagram.com/obenlobooking" style="color: #e61e4d; text-decoration: none; font-weight: bold;">Instagram</a>
            <a href="https://facebook.com/obenlobooking" style="color: #4267B2; text-decoration: none; font-weight: bold;">Facebook</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
