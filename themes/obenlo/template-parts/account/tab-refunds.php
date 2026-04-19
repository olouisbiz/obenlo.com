<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Refund History</h2>
            <?php
            $user_refunds = get_posts(array(
                'post_type' => 'refund',
                'author' => get_current_user_id(),
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            ?>

            <?php if (empty($user_refunds)) : ?>
                <div style="text-align: center; padding: 60px 40px; background: #fff; border: 1px solid #eee; border-radius: 20px;">
                    <h3 style="color: #888;">No refund requests found.</h3>
                </div>
            <?php else : ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($user_refunds as $refund) : 
                        $b_id = get_post_meta($refund->ID, '_obenlo_booking_id', true);
                        $r_status = get_post_meta($refund->ID, '_obenlo_refund_status', true);
                    ?>
                        <div style="background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0;">Booking #<?php echo esc_html($b_id); ?></h4>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;"><?php echo esc_html($refund->post_content); ?></p>
                            </div>
                            <span class="badge badge-info" style="background:<?php echo ($r_status === 'completed' ? '#ecfdf5; color:#059669;' : ($r_status === 'pending' ? '#fff7ed; color:#d97706;' : '#fef2f2; color:#dc2626;')); ?>"><?php echo ucfirst($r_status); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
