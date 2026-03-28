<?php
/**
 * Template Name: About Us
 */

get_header(); ?>

<div class="static-page-header" style="background: var(--obenlo-primary); padding: 100px 20px; text-align: center; color: #fff;">
    <h1 style="font-size: 3.5rem; margin-bottom: 20px;"><?php esc_html_e( 'Our Mission', 'obenlo' ); ?></h1>
    <p style="font-size: 1.4rem; max-width: 800px; margin: 0 auto; line-height: 1.6;">
        <?php esc_html_e( 'Empowering travelers to discover unique stays and local experiences while supporting a global community of diverse hosts.', 'obenlo' ); ?>
    </p>
</div>

<div class="static-page-content" style="max-width: 1000px; margin: 0 auto; padding: 80px 20px; line-height: 1.8; color: #444;">
    
    <div style="display: flex; gap: 60px; align-items: center; margin-bottom: 80px;">
        <div style="flex: 1;">
            <h2 style="font-size: 2.5rem; color: #222; margin-bottom: 25px;"><?php esc_html_e( 'What is Obenlo?', 'obenlo' ); ?></h2>
            <p><?php esc_html_e( 'Obenlo is a community-driven travel platform designed to bridge the gap between travelers seeking authenticity and local hosts offering one-of-a-kind hospitality. From luxury hotel rooms to unique local tours and professional services, we provide a centralized marketplace for everything you need on your journey.', 'obenlo' ); ?></p>
        </div>
        <div style="flex: 1; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            <div style="background: var(--obenlo-primary); color:#fff; padding: 60px; text-align:center; font-size: 2rem; font-weight: bold;">
                Obenlo.
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-bottom: 80px; text-align: center;">
        <div class="value-card" style="padding: 40px; border-radius: 20px; background: #fff; border: 1px solid #eee; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 20px;">🛡️</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;"><?php esc_html_e( 'Trust & Safety', 'obenlo' ); ?></h3>
            <p style="font-size: 0.95rem; color: #717171;"><?php esc_html_e( 'We prioritize the security of our community with verified profiles and secure payment systems.', 'obenlo' ); ?></p>
        </div>
        <div class="value-card" style="padding: 40px; border-radius: 20px; background: #fff; border: 1px solid #eee; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 20px;">🌍</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;"><?php esc_html_e( 'Global Community', 'obenlo' ); ?></h3>
            <p style="font-size: 0.95rem; color: #717171;"><?php esc_html_e( 'Connecting people across borders through hospitality and shared local knowledge.', 'obenlo' ); ?></p>
        </div>
        <div class="value-card" style="padding: 40px; border-radius: 20px; background: #fff; border: 1px solid #eee; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 20px;">✨</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;"><?php esc_html_e( 'Quality Stays', 'obenlo' ); ?></h3>
            <p style="font-size: 0.95rem; color: #717171;"><?php esc_html_e( 'Curating the best local experiences and accommodations for every type of traveler.', 'obenlo' ); ?></p>
        </div>
    </div>

    <div style="background: #f7f7f7; border-radius: 30px; padding: 60px; text-align: center;">
        <h2 style="font-size: 2.2rem; margin-bottom: 20px;"><?php esc_html_e( 'Ready to start your journey?', 'obenlo' ); ?></h2>
        <p style="margin-bottom: 30px;"><?php esc_html_e( 'Whether you\'re looking to host your space or book your next adventure, we\'re here to help.', 'obenlo' ); ?></p>
        <div style="display: flex; justify-content: center; gap: 20px;">
            <a href="<?php echo esc_url( home_url('/become-a-host') ); ?>" style="padding: 14px 40px; background: #222; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;"><?php esc_html_e( 'Become a Host', 'obenlo' ); ?></a>
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="padding: 14px 40px; background: var(--obenlo-primary); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;"><?php esc_html_e( 'Explore Obenlo', 'obenlo' ); ?></a>
        </div>
    </div>

</div>

<?php get_footer(); ?>
