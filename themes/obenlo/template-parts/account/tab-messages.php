<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Messages</h2>
            <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
                <?php echo do_shortcode('[obenlo_messages_page]'); ?>
            </div>
