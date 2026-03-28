<?php
/**
 * Template Name: Guest Rules
 */

get_header(); ?>

<div class="static-page-header" style="background: #f7f7f7; padding: 60px 20px; text-align: center; border-bottom: 1px solid #eee;">
    <h1 style="font-size: 3em; color: #222;"><?php esc_html_e( 'Global Guest Rules', 'obenlo' ); ?></h1>
    <p style="color: #666; font-size: 1.25rem;"><?php printf( esc_html__( 'Standard expectations for all guests using %s.', 'obenlo' ), get_option('obenlo_brand_name', 'Obenlo') ); ?></p>
</div>

<div class="static-page-content" style="max-width: 850px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <h2><?php echo __('1. Respect the Community', 'obenlo'); ?></h2>
    <p><?php printf( esc_html__( '%s is built on mutual respect. Guests are expected to treat hosts, their properties, and the local neighborhood with care and consideration.', 'obenlo' ), get_option('obenlo_brand_name', 'Obenlo') ); ?></p>

    <h2><?php echo __('2. House Rules', 'obenlo'); ?></h2>
    <p><?php esc_html_e( 'Always follow the specific house rules provided by your host. These include check-in/out times, smoking policies, pet rules, and noise restrictions.', 'obenlo' ); ?></p>

    <h2><?php echo __('3. Group Size and Visitors', 'obenlo'); ?></h2>
    <p><?php esc_html_e( 'The number of guests should not exceed the amount specified in your booking. Unregistered visitors are generally not permitted unless explicitly allowed by the host.', 'obenlo' ); ?></p>

    <h2><?php echo __('4. Cleanliness and Damage', 'obenlo'); ?></h2>
    <p><?php esc_html_e( 'Guests should leave the property in a reasonable state of cleanliness. Any accidental damage should be reported to the host immediately.', 'obenlo' ); ?></p>

    <h2><?php echo __('5. Illegal Activity', 'obenlo'); ?></h2>
    <p><?php esc_html_e( 'No illegal activities are permitted on Obenlo properties or during Obenlo experiences. Violation of this rule will result in immediate account suspension.', 'obenlo' ); ?></p>

</div>

<?php get_footer(); ?>
