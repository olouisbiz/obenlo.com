<?php
/**
 * Template Name: Global Refund Policy
 */

get_header(); ?>

<div class="static-page-header" style="background: linear-gradient(135deg, #e61e4d 0%, #ff5a5f 100%); padding: 80px 20px; text-align: center; color: #fff; margin-bottom: 20px;">
    <h1 style="font-size: 3.5rem; margin-bottom: 10px;"><?php esc_html_e( 'Global Refund Policy', 'obenlo' ); ?></h1>
    <p style="font-size: 1.1rem; opacity: 0.9;"><?php esc_html_e( 'Last Updated: April 2026', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 850px; margin: 0 auto; padding: 60px 20px; line-height: 1.8; color: #444;">
    
    <h2>1. Refund Eligibility</h2>
    <p><?php esc_html_e( 'Guests may be eligible for a refund if a service or stay is significantly different from the description, is inaccessible, or is canceled by the host. All refund requests must be submitted through the Obenlo dashboard within 24 hours of discovering the issue.', 'obenlo' ); ?></p>

    <h2>2. Travel Issues Covered</h2>
    <ul>
        <li><?php esc_html_e( 'The host fails to provide access (e.g., missing keys, no response).', 'obenlo' ); ?></li>
        <li><?php esc_html_e( 'The listing contains misrepresentations (e.g., wrong number of rooms, missing essential amenities).', 'obenlo' ); ?></li>
        <li><?php esc_html_e( 'The stay or service is unsanitary or poses a safety risk.', 'obenlo' ); ?></li>
    </ul>

    <h2>3. Resolution Process</h2>
    <p><?php esc_html_e( 'When a travel issue is reported, Obenlo will typically mediate between the guest and host. If a resolution cannot be reached, Obenlo reserves the right to issue a refund or find alternative accommodation for the guest.', 'obenlo' ); ?></p>

    <h2>4. Payouts and Timing</h2>
    <p><?php esc_html_e( 'Approved refunds are usually processed back to the original payment method within 5-10 business days.', 'obenlo' ); ?></p>

</div>

<?php get_footer(); ?>
