<?php
/**
 * Template Name: Become a Host
 */

get_header();
?>

<div class="become-host-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo get_template_directory_uri(); ?>/assets/images/host-hero.jpg');">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1><?php echo esc_html(get_option('obenlo_brand_name', 'Obenlo')); ?> your home</h1>
        <p>Join thousands of hosts earning extra income by sharing their space, experiences, or services.</p>
        <a href="<?php echo esc_url( home_url('/login#signup') ); ?>" class="cta-button">Start Hosting Today</a>
    </div>
</div>

<div class="host-benefits">
    <div class="benefit-item">
        <div class="benefit-icon">💰</div>
        <h3>Earn Extra Income</h3>
        <p style="color: #666;">Turn your extra space or unique skills into a profitable business with <?php echo esc_html(get_option('obenlo_brand_name', 'Obenlo')); ?>'s secure platform.</p>
    </div>
    <div class="benefit-item">
        <div class="benefit-icon">🌍</div>
        <h3>Connect Globally</h3>
        <p style="color: #666;">Meet travelers and guests from all over the world and share your local culture and hospitality.</p>
    </div>
    <div class="benefit-item">
        <div class="benefit-icon">🛡️</div>
        <h3>Secure & Protected</h3>
        <p style="color: #666;">We provide the tools and support to ensure every booking is safe, from verification to secure payments.</p>
    </div>
</div>

<div class="how-it-works-brief">
    <h2>How to get started</h2>
    <div class="step-container">
        <div class="step-item">
            <span class="step-number">1</span>
            <h4>Create your listing</h4>
            <p style="font-size: 0.9em; color: #666;">Upload photos, set your price, and describe what makes your space or service special.</p>
        </div>
        <div class="step-item">
            <span class="step-number">2</span>
            <h4>Welcome guests</h4>
            <p style="font-size: 0.9em; color: #666;">Communicate via our secure messenger and manage bookings through your dashboard.</p>
        </div>
        <div class="step-item">
            <span class="step-number">3</span>
            <h4>Get paid</h4>
            <p style="font-size: 0.9em; color: #666;">Payments are handled securely through <?php echo esc_html(get_option('obenlo_brand_name', 'Obenlo')); ?> and deposited directly to you.</p>
        </div>
    </div>
</div>

<div class="ready-to-host">
    <h2>Ready to <?php echo esc_html(get_option('obenlo_brand_name', 'Obenlo')); ?>?</h2>
    <a href="<?php echo esc_url( home_url('/login#signup') ); ?>" class="cta-button dark">Get Started</a>
</div>

<?php get_footer(); ?>
