<?php
/**
 * Template Name: Global Refund Policy
 */

get_header(); ?>

<div class="static-page-header" style="background: #f7f7f7; padding: 60px 20px; text-align: center; border-bottom: 1px solid #eee;">
    <h1 style="font-size: 3em; color: #222;"><?php esc_html_e( 'Global Refund Policy', 'obenlo' ); ?></h1>
    <p style="color: #666; font-size: 1.25rem;"><?php echo sprintf( esc_html__( 'How %s handles refund requests and disputes.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>
</div>

<div class="static-page-content" style="max-width: 850px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <h2><?php echo __('1. Refund Eligibility', 'obenlo'); ?></h2>
    <p><?php echo sprintf( esc_html__( 'Guests may be eligible for a refund if a service or stay is significantly different from the description, is inaccessible, or is canceled by the host. All refund requests must be submitted through the %s dashboard within 24 hours of discovering the issue.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>

    <h2><?php echo __('2. Travel Issues Covered', 'obenlo'); ?></h2>
    <ul>
        <li><?php esc_html_e( 'The host fails to provide access (e.g., missing keys, no response).', 'obenlo' ); ?></li>
        <li><?php esc_html_e( 'The listing contains misrepresentations (e.g., wrong number of rooms, missing essential amenities).', 'obenlo' ); ?></li>
        <li><?php esc_html_e( 'The stay or service is unsanitary or poses a safety risk.', 'obenlo' ); ?></li>
    </ul>

    <h2><?php echo __('3. Resolution Process', 'obenlo'); ?></h2>
    <p><?php echo sprintf( esc_html__( 'When a travel issue is reported, %s will typically mediate between the guest and host. If a resolution cannot be reached, %s reserves the right to issue a refund or find alternative accommodation for the guest.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>

    <h2><?php echo __('4. Payouts and Timing', 'obenlo'); ?></h2>
    <p><?php esc_html_e( 'Approved refunds are usually processed back to the original payment method within 5-10 business days.', 'obenlo' ); ?></p>

</div>

<?php get_footer(); ?>
