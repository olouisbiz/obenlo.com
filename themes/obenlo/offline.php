<?php
/**
 * Template Name: Offline Page
 */
get_header(); ?>

<div class="offline-container" style="text-align: center; padding: 100px 20px; max-width: 600px; margin: 0 auto;">
    <div style="font-size: 5rem; margin-bottom: 20px;">📴</div>
    <h1 style="font-size: 2.5rem; color: #222; margin-bottom: 15px;"><?php esc_html_e( 'You\'re Offline', 'obenlo' ); ?></h1>
    <p style="font-size: 1.1rem; color: #717171; line-height: 1.6; margin-bottom: 30px;">
        <?php esc_html_e( 'It seems you\'ve lost your connection. Don\'t worry, you can still browse some of your previously visited pages.', 'obenlo' ); ?>
    </p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; padding: 14px 30px; background: var(--obenlo-primary); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        <?php esc_html_e( 'Back to Home', 'obenlo' ); ?>
    </a>
</div>

<?php get_footer(); ?>
