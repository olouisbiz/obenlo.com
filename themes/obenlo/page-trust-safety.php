<?php
/**
 * Template Name: Trust & Safety
 */

get_header(); ?>

<div class="static-page-header" style="background: #222; padding: 100px 20px; text-align: center; color: #fff;">
    <div style="font-size: 4rem; margin-bottom: 20px;">🛡️</div>
    <h1 style="font-size: 3.5rem; margin-bottom: 20px;"><?php esc_html_e( 'Trust & Safety', 'obenlo' ); ?></h1>
    <p style="font-size: 1.3rem; max-width: 800px; margin: 0 auto;"><?php esc_html_e( 'Your safety is our top priority. We implement world-class standards to keep our community secure.', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 1000px; margin: 0 auto; padding: 80px 20px;">
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 80px;">
        <div style="padding: 30px; border: 1px solid #eee; border-radius: 20px;">
            <h3><?php esc_html_e( 'Secure Payments', 'obenlo' ); ?></h3>
            <p style="color: #666;"><?php esc_html_e( 'Every transaction is encrypted and managed through our secure payment partners.', 'obenlo' ); ?></p>
        </div>
        <div style="padding: 30px; border: 1px solid #eee; border-radius: 20px;">
            <h3><?php esc_html_e( 'Verified Profiles', 'obenlo' ); ?></h3>
            <p style="color: #666;"><?php esc_html_e( 'We encourage users to verify their identities to build a more transparent community.', 'obenlo' ); ?></p>
        </div>
        <div style="padding: 30px; border: 1px solid #eee; border-radius: 20px;">
            <h3><?php esc_html_e( '24/7 Support', 'obenlo' ); ?></h3>
            <p style="color: #666;"><?php esc_html_e( 'Our dedicated team is always here to help with any safety concerns.', 'obenlo' ); ?></p>
        </div>
    </div>

    <div style="background: #fff8f8; border: 1px solid #ffecec; padding: 40px; border-radius: 20px; text-align: center;">
        <h2 style="color: #e61e4d; margin-bottom: 15px;"><?php esc_html_e( 'Report a Concern', 'obenlo' ); ?></h2>
        <p><?php esc_html_e( 'If you experience any issues or feel unsafe, please contact our emergency support team immediately.', 'obenlo' ); ?></p>
        <a href="mailto:safety@obenlo.com" style="margin-top: 20px; display: inline-block; padding: 12px 30px; background: #e61e4d; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold;"><?php esc_html_e( 'Contact Safety Team', 'obenlo' ); ?></a>
    </div>

</div>

<?php get_footer(); ?>
