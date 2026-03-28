<?php
/**
 * Template Name: Become a Host
 */

get_header();
?>

<div class="become-host-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo get_template_directory_uri(); ?>/assets/images/host-hero.jpg');">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1><?php printf(esc_html__('%s your home', 'obenlo'), esc_html(get_option('obenlo_brand_name', 'Obenlo'))); ?></h1>
        <p><?php esc_html_e('Join thousands of hosts earning extra income by sharing their space, experiences, or services.', 'obenlo'); ?></p>
        <a href="<?php echo esc_url( home_url('/login#signup') ); ?>" class="cta-button"><?php esc_html_e('Start Hosting Today', 'obenlo'); ?></a>
    </div>
</div>

<div class="host-benefits">
    <div class="benefit-item">
        <div class="benefit-icon">💰</div>
        <h3><?php esc_html_e('Earn Extra Income', 'obenlo'); ?></h3>
        <p style="color: #666;"><?php printf(esc_html__('Turn your extra space or unique skills into a profitable business with %s\'s secure platform.', 'obenlo'), esc_html(get_option('obenlo_brand_name', 'Obenlo'))); ?></p>
    </div>
    <div class="benefit-item">
        <div class="benefit-icon">🌍</div>
        <h3><?php esc_html_e('Connect Globally', 'obenlo'); ?></h3>
        <p style="color: #666;"><?php esc_html_e('Meet travelers and guests from all over the world and share your local culture and hospitality.', 'obenlo'); ?></p>
    </div>
    <div class="benefit-item">
        <div class="benefit-icon">🛡️</div>
        <h3><?php esc_html_e('Secure & Protected', 'obenlo'); ?></h3>
        <p style="color: #666;"><?php esc_html_e('We provide the tools and support to ensure every booking is safe, from verification to secure payments.', 'obenlo'); ?></p>
    </div>
</div>

<div class="how-it-works-brief">
    <h2><?php esc_html_e('How to get started', 'obenlo'); ?></h2>
    <div class="step-container">
        <div class="step-item">
            <span class="step-number">1</span>
            <h4><?php esc_html_e('Create your listing', 'obenlo'); ?></h4>
            <p style="font-size: 0.9em; color: #666;"><?php esc_html_e('Upload photos, set your price, and describe what makes your space or service special.', 'obenlo'); ?></p>
        </div>
        <div class="step-item">
            <span class="step-number">2</span>
            <h4><?php esc_html_e('Welcome guests', 'obenlo'); ?></h4>
            <p style="font-size: 0.9em; color: #666;"><?php esc_html_e('Communicate via our secure messenger and manage bookings through your dashboard.', 'obenlo'); ?></p>
        </div>
        <div class="step-item">
            <span class="step-number">3</span>
            <h4><?php esc_html_e('Get paid', 'obenlo'); ?></h4>
            <p style="font-size: 0.9em; color: #666;"><?php printf(esc_html__('Payments are handled securely through %s and deposited directly to you.', 'obenlo'), esc_html(get_option('obenlo_brand_name', 'Obenlo'))); ?></p>
        </div>
    </div>
</div>

<div class="ready-to-host">
    <h2><?php printf(esc_html__('Ready to %s?', 'obenlo'), esc_html(get_option('obenlo_brand_name', 'Obenlo'))); ?></h2>
    <a href="<?php echo esc_url( home_url('/login#signup') ); ?>" class="cta-button dark"><?php esc_html_e('Get Started', 'obenlo'); ?></a>
</div>

<?php get_footer(); ?>
