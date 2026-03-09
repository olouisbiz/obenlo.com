<?php
/**
 * Template Name: Support Hub
 */

get_header(); ?>

<div class="static-page-header" style="background: #222; padding: 80px 20px; text-align: center; color: #fff;">
    <h1 style="font-size: 3rem; margin-bottom: 20px;"><?php esc_html_e( 'How can we help?', 'obenlo' ); ?></h1>
    <div style="max-width: 600px; margin: 0 auto; position: relative;">
        <input type="text" placeholder="<?php esc_attr_e( 'Search for help...', 'obenlo' ); ?>" style="width: 100%; padding: 18px 25px; border-radius: 50px; border: none; font-size: 1.1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
    </div>
</div>

<div class="static-page-content" style="max-width: 1100px; margin: 0 auto; padding: 80px 20px;">
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 80px;">
        <div style="padding: 40px; border: 1px solid #eee; border-radius: 20px; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 20px;">👤</div>
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Guest Support', 'obenlo' ); ?></h3>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;"><?php esc_html_e( 'Help with bookings, cancellations, and payments.', 'obenlo' ); ?></p>
            <a href="<?php echo home_url('/faq?type=guest'); ?>" style="color: #e61e4d; font-weight: bold; text-decoration: none;"><?php esc_html_e( 'Browse Articles', 'obenlo' ); ?> &rarr;</a>
        </div>
        <div style="padding: 40px; border: 1px solid #eee; border-radius: 20px; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 20px;">🏠</div>
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Host Support', 'obenlo' ); ?></h3>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;"><?php esc_html_e( 'Resources for managing listings and hosting guests.', 'obenlo' ); ?></p>
            <a href="<?php echo home_url('/faq?type=host'); ?>" style="color: #e61e4d; font-weight: bold; text-decoration: none;"><?php esc_html_e( 'Browse Articles', 'obenlo' ); ?> &rarr;</a>
        </div>
        <div style="padding: 40px; border: 1px solid #eee; border-radius: 20px; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 20px;">🛡️</div>
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Safety & Security', 'obenlo' ); ?></h3>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px;"><?php esc_html_e( 'Report a concern or learn about our safety standards.', 'obenlo' ); ?></p>
            <a href="<?php echo home_url('/trust-safety'); ?>" style="color: #e61e4d; font-weight: bold; text-decoration: none;"><?php esc_html_e( 'Get Help', 'obenlo' ); ?> &rarr;</a>
        </div>
    </div>

    <div style="background: #f7f7f7; border-radius: 20px; padding: 60px; display: flex; align-items: center; gap: 40px;">
        <div style="flex: 1;">
            <h2 style="margin-bottom: 15px;"><?php esc_html_e( 'Still need help?', 'obenlo' ); ?></h2>
            <p style="color: #666;"><?php esc_html_e( 'Our support team is available 24/7 to assist you with any questions or issues.', 'obenlo' ); ?></p>
        </div>
        <div>
            <a href="mailto:support@obenlo.com" style="padding: 15px 35px; background: #e61e4d; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;"><?php esc_html_e( 'Contact Us', 'obenlo' ); ?></a>
        </div>
    </div>

</div>

<?php get_footer(); ?>
