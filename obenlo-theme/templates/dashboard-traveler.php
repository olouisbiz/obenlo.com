<?php
/**
 * Template Name: Traveler Hub
 * Location: /obenlo-theme/templates/dashboard-traveler.php
 */

get_header();
$user_id = get_current_user_id();
$data = get_obenlo_traveler_data($user_id);
?>

<div class="obenlo-container" style="padding: 40px; max-width: 1200px; margin: auto;">
    <h1 style="font-size: 2.2rem; margin-bottom: 30px;">My <span style="color: #10b981;">Obenlo Hub</span></h1>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <section>
            <h3 style="margin-bottom: 20px;">Upcoming & Past Trips</h3>
            <?php if (!empty($data['bookings'])): ?>
                <?php foreach ($data['bookings'] as $trip): ?>
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong><?php echo esc_html($trip['listing_title']); ?></strong>
                            <span style="color: #10b981; font-weight: bold;">Confirmed</span>
                        </div>
                        <p style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">Dates: <?php echo esc_html($trip['dates']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; border: 2px dashed #cbd5e1; border-radius: 12px;">
                    <p>You haven't booked any experiences yet.</p>
                    <a href="<?php echo home_url('/experiences'); ?>" style="color: #10b981; font-weight: bold;">Start Exploring →</a>
                </div>
            <?php endif; ?>
        </section>

        <aside>
            <h3 style="margin-bottom: 20px;">My Favorites ❤️</h3>
            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <?php if (!empty($data['favorites'])): ?>
                    <?php foreach ($data['favorites'] as $fav_id): ?>
                        <div style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                            <a href="<?php echo get_permalink($fav_id); ?>" style="text-decoration: none; color: #334155;">
                                <?php echo get_the_title($fav_id); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 0.85rem;">Your wishlist is empty.</p>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</div>

<?php get_footer(); ?>