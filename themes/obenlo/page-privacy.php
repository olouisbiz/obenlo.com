<?php
/**
 * Template Name: Privacy Policy
 */

get_header();
?>

<div class="static-page-header" style="background: #f7f7f7; padding: 60px 20px; text-align: center; border-bottom: 1px solid #eee;">
    <h1 style="font-size: 3em;">Privacy Policy</h1>
    <p style="color: #666; font-size: 1.2em;">How we protect and manage your personal data.</p>
</div>

<div class="static-page-content" style="max-width: 800px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <h2>1. Information We Collect</h2>
    <p>We collect information that you provide directly to us when you create an account, list a service, or communicate with other users. This includes your name, email address, phone number, and payment information. We also automatically collect certain information when you use Obenlo, such as your IP address, device type, and browsing behavior.</p>

    <h2>2. How We Use Their Information</h2>
    <p>We use the information we collect to provide, maintain, and improve our services, including to facilitate bookings, process payments, and ensure the safety and security of our community. We also use your information to communicate with you about your account, provide support, and send you important updates and promotional offers.</p>

    <h2>3. Information Sharing and Disclosure</h2>
    <p>We share necessary information between guests and hosts to facilitate the booking process. We may also share your information with third-party service providers who perform services on our behalf, such as payment processing and analytics. We do not sell your personal data to third parties.</p>

    <h2>4. Data Retention and Security</h2>
    <p>We retain your personal information for as long as necessary to provide our services and fulfill the purposes outlined in this policy. We implement industry-standard security measures, including encryption and secure servers, to protect your information from unauthorized access, alteration, or disclosure.</p>

    <h2>5. Your Rights and Choices</h2>
    <p><?php echo sprintf( esc_html__( 'You have the right to access, correct, or delete your personal information at any time through your account settings. You can also opt-out of receiving promotional communications from us by following the instructions in those messages. If you have any questions or concerns about your data, please contact %s support.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>

    <h2>6. Cookies and Tracking Technologies</h2>
    <p><?php echo sprintf( esc_html__( 'We use cookies and similar tracking technologies to enhance your experience on %s, analyze site traffic, and personalize content. You can manage your cookie preferences through your browser settings, although disabling cookies may affect your ability to use certain features of our platform.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>

    <h2>7. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any significant changes by posting the updated policy on our website and updating the "Last Updated" date at the top of the page.</p>


</div>

<?php get_footer(); ?>
