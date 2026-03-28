<?php
/**
 * Template Name: Global Cancellation Policy
 */

get_header(); ?>

<div class="static-page-header" style="background: #f7f7f7; padding: 60px 20px; text-align: center; border-bottom: 1px solid #eee;">
    <h1 style="font-size: 3em; color: #222;"><?php esc_html_e( 'Global Cancellation Policy', 'obenlo' ); ?></h1>
    <p style="color: #666; font-size: 1.25rem;"><?php esc_html_e( 'Standard cancellation terms for hosts and guests on ' . get_option('obenlo_brand_name', 'Obenlo') . '.', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 850px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <h2>1. Overview</h2>
    <p><?php esc_html_e( 'This Global Cancellation Policy applies to all bookings made on the ' . get_option('obenlo_brand_name', 'Obenlo') . ' platform where a host has not specified a custom cancellation policy. Our goal is to balance the flexibility needs of guests with the security needs of our hosts.', 'obenlo' ); ?></p>

    <h2>2. Guest Cancellations</h2>
    <ul>
        <li><strong><?php esc_html_e( 'Full Refund:', 'obenlo' ); ?></strong> <?php esc_html_e( 'Cancellations made up to 48 hours after booking and at least 14 days before the check-in/start date will receive a 100% refund.', 'obenlo' ); ?></li>
        <li><strong><?php esc_html_e( 'Partial Refund:', 'obenlo' ); ?></strong> <?php esc_html_e( 'Cancellations made between 7 and 14 days before the start date will receive a 50% refund.', 'obenlo' ); ?></li>
        <li><strong><?php esc_html_e( 'No Refund:', 'obenlo' ); ?></strong> <?php esc_html_e( 'Cancellations made less than 7 days before the start date are non-refundable.', 'obenlo' ); ?></li>
    </ul>

    <h2>3. Host Cancellations</h2>
    <p><?php esc_html_e( 'Hosts are expected to fulfill all confirmed bookings. If a host cancels a booking, the guest will receive a full refund, and the host may be subject to penalties, including a cancellation fee and an automated negative review.', 'obenlo' ); ?></p>

    <h2>4. Extenuating Circumstances</h2>
    <p><?php esc_html_e( 'Obenlo may override this policy in cases of documented emergencies, national disasters, or significant travel restrictions. Documentation will be required for any refund request under extenuating circumstances.', 'obenlo' ); ?></p>

</div>

<?php get_footer(); ?>
