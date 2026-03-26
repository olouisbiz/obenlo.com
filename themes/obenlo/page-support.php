<?php
/**
 * Template Name: Support Hub
 */

get_header(); ?>

<div class="static-page-header" style="background: #222; padding: clamp(40px, 10vw, 80px) 20px; text-align: center; color: #fff;">
    <h1 style="font-size: clamp(1.8rem, 6vw, 3rem); margin-bottom: 20px;"><?php esc_html_e( 'How can we help?', 'obenlo' ); ?></h1>
    <div style="max-width: 600px; margin: 0 auto; position: relative;">
        <input type="text" placeholder="<?php esc_attr_e( 'Search for help...', 'obenlo' ); ?>" style="width: 100%; padding: 15px 25px; border-radius: 50px; border: none; font-size: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); outline: none;">
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

    <div id="ticket-section" style="background: #fcfcfc; border: 1px solid #eee; border-radius: 24px; padding: clamp(30px, 8vw, 60px); margin-bottom: 40px;">
        <h2 style="margin-bottom: 15px; text-align: center; font-size: clamp(1.4rem, 4vw, 2rem);"><?php esc_html_e( 'Submit a Support Ticket', 'obenlo' ); ?></h2>
        <p style="color: #666; text-align: center; margin-bottom: 30px; font-size: 0.95rem;"><?php esc_html_e( 'Need more help? Open a secure support ticket below and our team will assist you.', 'obenlo' ); ?></p>
        <?php echo do_shortcode('[obenlo_support_page]'); ?>
    </div>

</div>

<?php get_footer(); ?>
