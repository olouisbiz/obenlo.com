<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Platform Announcements</h2>
            <p style="color:#666; margin-bottom:30px;">Latest updates and news from Obenlo.</p>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <?php echo do_shortcode('[obenlo_broadcasts_page]'); ?>
            </div>
