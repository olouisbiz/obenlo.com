<?php
/**
 * Template Name: FAQ
 */

get_header();
?>

<div class="static-page-header">
    <h1>Frequently Asked Questions</h1>
    <p>Quick answers to common questions about Obenlo.</p>
</div>

<div class="faq-content" style="max-width: 900px; margin: 0 auto; padding: 60px 20px;">
    
    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Getting Started', 'obenlo' ); ?></h2>
        
        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'Is Obenlo free to use?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Creating an account and listing your services or spaces on Obenlo is completely free. We only charge a small service fee when a booking is successfully completed to help us maintain and grow the platform.', 'obenlo' ); ?></p>
        </div>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I create a listing?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Once logged in, click on "Become a Host" or "Switch to Hosting" in the menu. Our step-by-step listing tool will guide you through adding photos, descriptions, and pricing for your service or stay.', 'obenlo' ); ?></p>
        </div>
    </div>

    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Payments & Bookings', 'obenlo' ); ?></h2>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I get paid as a host?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Payments are processed securely via our partners. Once a guest completes their stay or service, funds are typically released to your connected account within 24 hours of checkout/completion.', 'obenlo' ); ?></p>
        </div>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I cancel a booking?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'You can manage and cancel bookings from your "Trips" or "Host Dashboard" page. Note that cancellation policies vary by host and listing—please review the specific terms on your booking confirmation.', 'obenlo' ); ?></p>
        </div>
    </div>

    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Trust & Safety', 'obenlo' ); ?></h2>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How can I trust other users?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Obenlo promotes trust through verified profiles, transparent reviews, and a secure messaging system. We recommend communicating only through Obenlo until your booking is confirmed.', 'obenlo' ); ?></p>
        </div>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'What if I have an issue during my stay?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Our support team is available 24/7. You can report an issue or initiate a dispute directly through your dashboard, and our mediation team will work to resolve it fairly.', 'obenlo' ); ?></p>
        </div>
    </div>

</div>

<?php get_footer(); ?>
