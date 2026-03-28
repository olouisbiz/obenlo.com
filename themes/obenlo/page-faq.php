<?php
/**
 * Template Name: FAQ
 */

get_header();

$type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : 'all';
$title = __('Frequently Asked Questions', 'obenlo');
$desc = __('Quick answers to common questions about Obenlo.', 'obenlo');

if ( $type === 'guest' ) {
    $title = __('Guest Support', 'obenlo');
    $desc = __('Find answers about booking, staying, and interacting with hosts.', 'obenlo');
} elseif ( $type === 'host' ) {
    $title = __('Host Support', 'obenlo');
    $desc = __('Find answers about listing your space, managing bookings, and getting paid.', 'obenlo');
}
?>

<div class="static-page-header">
    <h1><?php echo esc_html( $title ); ?></h1>
    <p><?php echo esc_html( $desc ); ?></p>
</div>

<div class="faq-content" style="max-width: 900px; margin: 0 auto; padding: 60px 20px;">
    
    <?php if ( $type === 'all' || $type === 'guest' || $type === 'host' ) : // Getting Started applies to all ?>
    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Getting Started', 'obenlo' ); ?></h2>
        
        <?php if ( $type === 'all' || $type === 'guest' ) : ?>
        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'Is Obenlo free to use?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Creating an account and listing your services or spaces on Obenlo is completely free. We only charge a small service fee when a booking is successfully completed to help us maintain and grow the platform.', 'obenlo' ); ?></p>
        </div>
        <?php endif; ?>

        <?php if ( $type === 'all' || $type === 'host' ) : ?>
        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I create a listing?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Once logged in, click on "Become a Host" or "Switch to Hosting" in the menu. Our step-by-step listing tool will guide you through adding photos, descriptions, and pricing for your service or stay.', 'obenlo' ); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Payments & Bookings', 'obenlo' ); ?></h2>

        <?php if ( $type === 'all' || $type === 'host' ) : ?>
        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I get paid as a host?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Payments are processed securely via our partners. Once a guest completes their stay or service, funds are typically released to your connected account within 24 hours of checkout/completion.', 'obenlo' ); ?></p>
        </div>
        <?php endif; ?>

        <?php if ( $type === 'all' || $type === 'guest' ) : ?>
        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How do I cancel a booking?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'You can manage and cancel bookings from your "Trips" page. Note that cancellation policies vary by host and listing—please review the specific terms on your booking confirmation.', 'obenlo' ); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="faq-section" style="margin-bottom: 60px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;"><?php esc_html_e( 'Trust & Safety', 'obenlo' ); ?></h2>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'How can I trust other users?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Obenlo promotes trust through verified profiles, transparent reviews, and a secure messaging system. We recommend communicating only through Obenlo until your booking is confirmed.', 'obenlo' ); ?></p>
        </div>

        <div class="faq-item" style="margin-bottom: 30px;">
            <h4 style="font-size: 1.2rem; margin-bottom: 10px;"><?php esc_html_e( 'What if I have an issue during my stay?', 'obenlo' ); ?></h4>
            <p style="line-height: 1.6; color: #666;"><?php esc_html_e( 'Our support team is available 24/7. You can report an issue or initiate a dispute directly through your dashboard or the help center ticket system.', 'obenlo' ); ?></p>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 40px;">
        <?php if ( $type !== 'all' ) : ?>
            <a href="<?php echo esc_url( home_url('/faq') ); ?>" style="color: var(--obenlo-primary); font-weight: bold; text-decoration: none; margin-right: 20px;"><?php echo __('&larr; View All FAQs', 'obenlo'); ?></a>
        <?php endif; ?>
        <a href="<?php echo esc_url( home_url('/support') ); ?>" style="padding: 12px 30px; background: var(--obenlo-primary); color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold;"><?php esc_html_e( 'Contact Support', 'obenlo' ); ?></a>
    </div>

</div>

<?php get_footer(); ?>
