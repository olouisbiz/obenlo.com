<?php
/**
 * Template Name: Global Policies
 */

get_header(); ?>

<div class="static-page-header" style="background: #222; padding: 100px 20px; text-align: center; color: #fff;">
    <h1 style="font-size: 3.5rem; margin-bottom: 20px;"><?php esc_html_e( 'Global Policies', 'obenlo' ); ?></h1>
    <p style="font-size: 1.3rem; max-width: 800px; margin: 0 auto;"><?php esc_html_e( 'The standard terms and safety guidelines for all Obenlo users.', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 900px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <section style="margin-bottom: 60px;">
        <h2 style="font-size: 2rem; color: #222; margin-bottom: 25px;"><?php esc_html_e( 'Standard Hosting Guidelines', 'obenlo' ); ?></h2>
        <p><?php esc_html_e( 'By default, all hosts on Obenlo agree to provide safe, clean, and accurate representations of their services. These global policies apply to all listings unless a host specifies a custom policy that meets our minimum standards.', 'obenlo' ); ?></p>
    </section>

    <div style="display: grid; gap: 30px; margin-bottom: 60px;">
        <div style="background: #f9f9f9; padding: 30px; border-radius: 20px;">
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Booking & Cancellations', 'obenlo' ); ?></h3>
            <p><?php esc_html_e( 'Our global cancellation policy ensures fairness for both guests and hosts. Hosts can choose from flexible, moderate, or strict tiers, which are governed by Obenlo\'s secure mediation process.', 'obenlo' ); ?></p>
        </div>
        
        <div style="background: #f9f9f9; padding: 30px; border-radius: 20px;">
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Trust & Verification', 'obenlo' ); ?></h3>
            <p><?php esc_html_e( 'Users must complete identity verification to access premium features. Documents are handled securely and never shared with third parties without consent.', 'obenlo' ); ?></p>
        </div>

        <div style="background: #f9f9f9; padding: 30px; border-radius: 20px;">
            <h3 style="margin-bottom: 15px;"><?php esc_html_e( 'Payout Regulations', 'obenlo' ); ?></h3>
            <p><?php esc_html_e( 'Payouts are processed after successful check-in or service completion. Fees are deducted according to the host\'s agreed-upon tier.', 'obenlo' ); ?></p>
        </div>
    </div>

    <div style="border-top: 1px solid #eee; padding-top: 40px; text-align: center;">
        <p style="color: #888; font-size: 0.9rem;"><?php esc_html_e( 'Last Updated: March 8, 2026', 'obenlo' ); ?></p>
        <a href="<?php echo home_url('/support'); ?>" style="color: #e61e4d; font-weight: bold; text-decoration: none;"><?php esc_html_e( 'Contact Support for Policy Questions', 'obenlo' ); ?> →</a>
    </div>

</div>

<?php get_footer(); ?>
