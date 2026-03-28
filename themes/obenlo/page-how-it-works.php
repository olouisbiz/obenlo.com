<?php
/**
 * Template Name: How it Works
 */

get_header();
?>

<div class="static-page-header">
    <h1><?php echo __('How Obenlo Works', 'obenlo'); ?></h1>
    <p><?php echo __('Everything you need to know about booking and hosting on Obenlo.', 'obenlo'); ?></p>
</div>

<div class="static-page-content">
    
    <section id="for-guests" class="static-section">
        <h2>
            <span class="static-icon-box" style="background:var(--obenlo-primary);">🧳</span>
            <?php echo __('For Guests', 'obenlo'); ?>
        </h2>
        
        <div class="standard-grid">
            <div class="standard-card">
                <h4><?php echo __('1. Find your experience', 'obenlo'); ?></h4>
                <p><?php echo __('Browse through hundreds of unique stays, experiences, and local services tailored to your needs.', 'obenlo'); ?></p>
            </div>
            <div class="standard-card">
                <h4><?php echo __('2. Connect with Hosts', 'obenlo'); ?></h4>
                <p><?php echo __('Use our secure messaging system to ask questions or verify details directly with the host before you book.', 'obenlo'); ?></p>
            </div>
            <div class="standard-card">
                <h4><?php echo __('3. Book with confidence', 'obenlo'); ?></h4>
                <p><?php echo __('Our secure payment system protects your money. Hosts are only paid after you\'ve successfully checked in.', 'obenlo'); ?></p>
            </div>
        </div>
    </section>

    <section id="for-hosts" class="static-section" style="margin-bottom: 60px;">
        <h2>
            <span class="static-icon-box" style="background:#222;">🏠</span>
            <?php echo __('For Hosts', 'obenlo'); ?>
        </h2>
        
        <div class="standard-grid">
            <div class="standard-card">
                <h4><?php echo __('1. List your space or skill', 'obenlo'); ?></h4>
                <p><?php echo __('It\'s free to list! Set your own price, schedule, and rules for your listing.', 'obenlo'); ?></p>
            </div>
            <div class="standard-card">
                <h4><?php echo __('2. Manage everything', 'obenlo'); ?></h4>
                <p><?php echo __('Your Obenlo dashboard gives you full control over bookings, messages, and your storefront.', 'obenlo'); ?></p>
            </div>
            <div class="standard-card">
                <h4><?php echo __('3. Secure processing', 'obenlo'); ?></h4>
                <p><?php echo __('We handle all the financial logistics, ensuring you get paid reliably and on time for every booking.', 'obenlo'); ?></p>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>
