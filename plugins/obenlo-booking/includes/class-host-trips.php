<?php
/**
 * Host Trips & Testimony Module
 * Single Responsibility: Guest trip history + testimony submission + refunds tab.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Trips
{
    public function init()
    {
        add_action('admin_post_obenlo_save_testimony', array($this, 'handle_save_testimony'));
    }

    public function render_trips_section() {
        $user_id = get_current_user_id();
        $bookings = get_posts(array(
            'post_type'      => 'booking',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        ?>
        <div class="dashboard-header"><h2 class="dashboard-title"><?php echo __('My Trips', 'obenlo'); ?></h2></div>
        <p style="color:#666; font-size:1.05rem; margin-bottom:40px;"><?php echo __('Your booking history and confirmation codes.', 'obenlo'); ?></p>

        <?php if (empty($bookings)): ?>
            <div style="text-align:center; padding:80px 40px; background:#fff; border:1px dashed #ddd; border-radius:24px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="width:64px; height:64px; margin:0 auto 20px; display:block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <h3 style="color:#555; font-size:1.3rem; margin:0 0 10px 0;"><?php echo __('No trips yet', 'obenlo'); ?></h3>
                <p style="color:#888; margin:0 0 30px 0;"><?php echo __('Explore listings and make your first booking!', 'obenlo'); ?></p>
                <a href="<?php echo esc_url(home_url('/listings')); ?>" class="btn-primary"><?php echo __('Explore Listings', 'obenlo'); ?></a>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:25px;">
                <?php foreach ($bookings as $booking):
                    $listing_id    = get_post_meta($booking->ID, '_obenlo_listing_id', true);
                    $listing_title = $listing_id ? get_the_title($listing_id) : 'Unknown Listing';
                    $listing_url   = $listing_id ? get_permalink($listing_id) : '#';
                    $start_date    = get_post_meta($booking->ID, '_obenlo_start_date', true);
                    $end_date      = get_post_meta($booking->ID, '_obenlo_end_date', true);
                    $status        = get_post_meta($booking->ID, '_obenlo_booking_status', true);
                    $conf_code     = get_post_meta($booking->ID, '_obenlo_confirmation_code', true);
                    $thumb_url     = $listing_id && has_post_thumbnail($listing_id) ? get_the_post_thumbnail_url($listing_id, 'medium') : '';
                    $status_class  = 'badge-info';
                    if (in_array($status, ['confirmed', 'approved', 'completed'])) $status_class = 'badge-success';
                    if (in_array($status, ['declined', 'cancelled']))               $status_class = 'badge-danger';
                    if ($status === 'pending_payment')                               $status_class = 'badge-warning';
                ?>
                    <div style="background:#fff; border:1px solid #eee; border-radius:24px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.02); display:flex; gap:0; transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 20px rgba(0,0,0,0.02)';">
                        <?php if ($thumb_url): ?>
                            <div style="width:200px; flex-shrink:0; background:url('<?php echo esc_url($thumb_url); ?>') center/cover; min-height:160px;"></div>
                        <?php endif; ?>
                        <div style="padding:30px; flex-grow:1;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                                <div>
                                    <span class="badge <?php echo $status_class; ?>" style="margin-bottom:10px;"><?php echo esc_html(ucwords(str_replace('_', ' ', $status))); ?></span>
                                    <h3 style="margin:0; font-size:1.25rem; font-weight:800; color:#222;">
                                        <a href="<?php echo esc_url($listing_url); ?>" style="color:inherit; text-decoration:none;"><?php echo esc_html($listing_title); ?></a>
                                    </h3>
                                    <div style="color:#888; font-size:0.9rem; margin-top:6px; font-weight:500;">
                                        <?php echo esc_html($start_date); ?>
                                        <?php echo $end_date ? ' &rarr; ' . esc_html($end_date) : ''; ?>
                                    </div>
                                </div>
                                <?php if ($conf_code): ?>
                                    <div style="text-align:right;">
                                        <div style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:1.5px; color:#e61e4d; margin-bottom:4px; opacity:0.8;"><?php echo __('Code', 'obenlo'); ?></div>
                                        <div style="font-size:1.4rem; font-weight:900; font-family: 'JetBrains Mono', monospace; color:#222; letter-spacing:1px;"><?php echo esc_html($conf_code); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #f9f9f9; padding-top:20px;">
                                <a href="<?php echo esc_url($listing_url); ?>" class="btn-outline" style="padding:8px 20px; font-size:0.85rem;"><?php echo __('View Listing', 'obenlo'); ?></a>
                                <a href="?action=messages" class="btn-primary" style="padding:8px 20px; font-size:0.85rem;"><?php echo __('Contact Host', 'obenlo'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php
    }

    public function render_testimony_section() {
        $user_id = get_current_user_id();
        $user    = wp_get_current_user();

        $my_testimonies = get_posts(array(
            'post_type'      => 'testimony',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'post_status'    => array('publish', 'pending', 'draft')
        ));
        ?>
        <div class="dashboard-header"><h2 class="dashboard-title"><?php echo __('Obenlo Love', 'obenlo'); ?></h2></div>

        <div style="background:#fffcf2; border:1px solid #fde047; border-left:4px solid #eab308; border-radius:12px; padding:20px; margin-bottom:40px;">
            <p style="color:#854d0e; font-size:1.05rem; margin:0; line-height:1.6;">
                <?php echo sprintf(__('Share your experience with %s! We love hearing how our platform helps our community.', 'obenlo'), '<strong>Obenlo</strong>'); ?>
            </p>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:50px; align-items: start;">
            <div style="background:#fff; border:1px solid #eee; border-radius:24px; padding:40px; box-shadow:0 10px 30px rgba(0,0,0,0.02);">
                <h4 style="margin-top:0; margin-bottom:25px; font-size:1.2rem; font-weight:800;"><?php echo __('Write a Testimony', 'obenlo'); ?></h4>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                    <input type="hidden" name="action" value="obenlo_save_testimony">
                    <?php wp_nonce_field('save_testimony', 'testimony_nonce'); ?>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:10px; font-size:0.9rem; color:#444;"><?php echo __('How would you sum up your experience?', 'obenlo'); ?></label>
                        <input type="text" name="testimony_title" placeholder="<?php echo esc_attr(__('e.g. Best platform for local stays!', 'obenlo')); ?>" required style="width:100%; padding:15px; border:1.5px solid #eee; border-radius:14px; outline:none; font-family:inherit;">
                    </div>

                    <div style="margin-bottom:25px;">
                        <label style="display:block; font-weight:700; margin-bottom:10px; font-size:0.9rem; color:#444;"><?php echo __('Your Feedback', 'obenlo'); ?></label>
                        <textarea name="testimony_content" placeholder="<?php echo esc_attr(__('Tell us more details...', 'obenlo')); ?>" required style="width:100%; padding:15px; border:1.5px solid #eee; border-radius:14px; outline:none; font-family:inherit; height:150px;"></textarea>
                    </div>

                    <div style="margin-bottom:30px;">
                        <label style="display:block; font-weight:700; margin-bottom:10px; font-size:0.9rem; color:#444;"><?php echo __('Star Rating', 'obenlo'); ?></label>
                        <div style="display:flex; gap:10px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label style="cursor:pointer;">
                                    <input type="radio" name="testimony_rating" value="<?php echo $i; ?>" <?php checked($i, 5); ?> style="display:none;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dash-rating-star" data-val="<?php echo $i; ?>" style="width:30px; height:30px; transition:all 0.2s; color:#ddd;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <script>
                        document.querySelectorAll('.dash-rating-star').forEach(star => {
                            star.addEventListener('click', function() {
                                let val = this.dataset.val;
                                document.querySelectorAll('.dash-rating-star').forEach(s => {
                                    s.style.fill = s.dataset.val <= val ? '#f59e0b' : 'none';
                                    s.style.color = s.dataset.val <= val ? '#f59e0b' : '#ddd';
                                });
                            });
                        });
                        document.querySelector('.dash-rating-star[data-val="5"]').click();
                    </script>

                    <button type="submit" class="btn-primary" style="width:100%; padding:16px; border-radius:16px; font-size:1rem;"><?php echo __('Submit My Testimony', 'obenlo'); ?></button>
                    <p style="text-align:center; color:#888; font-size:0.8rem; margin-top:15px;"><?php echo __('Note: Testimonies are reviewed by our team before going live.', 'obenlo'); ?></p>
                </form>
            </div>

            <div>
                <h4 style="margin-top:0; margin-bottom:25px; font-size:1.2rem; font-weight:800;"><?php echo __('Your Past Feedback', 'obenlo'); ?></h4>
                <?php if (empty($my_testimonies)): ?>
                    <div style="background:#fcfcfc; border:1px dashed #ddd; padding:50px 30px; border-radius:24px; text-align:center;">
                        <p style="color:#aaa; font-size:0.95rem;"><?php echo __("You haven't written any testimonies yet. Be the first to share the love!", 'obenlo'); ?></p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        <?php foreach ($my_testimonies as $t):
                            $ts      = $t->post_status;
                            $rating  = get_post_meta($t->ID, '_obenlo_testimony_rating', true);
                            $tc      = 'badge-info';
                            if ($ts === 'publish') $tc = 'badge-success';
                            if ($ts === 'pending') $tc = 'badge-warning';
                        ?>
                            <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px;">
                                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                                    <span class="badge <?php echo $tc; ?>"><?php echo esc_html(strtoupper($ts)); ?></span>
                                    <div style="color:#f59e0b; display:flex; gap:2px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg viewBox="0 0 24 24" fill="<?php echo $i <= $rating ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" style="width:14px; height:14px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <h5 style="margin:0 0 8px 0; font-size:1.05rem; font-weight:700; color:#222;"><?php echo esc_html($t->post_title); ?></h5>
                                <p style="margin:0; font-size:0.9rem; color:#666; line-height:1.5;"><?php echo wp_trim_words($t->post_content, 30); ?></p>
                                <div style="margin-top:15px; font-size:0.75rem; color:#aaa; font-weight:500;"><?php echo get_the_date('', $t->ID); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function render_refunds_tab()
    {
        $user_id = get_current_user_id();
        $refunds = get_posts(array(
            'post_type'      => 'refund',
            'meta_query'     => array(array('key' => '_obenlo_host_id', 'value' => $user_id)),
            'posts_per_page' => -1,
            'post_status'    => 'any'
        ));

        if (empty($refunds)) {
            $refunds = get_posts(array('post_type' => 'refund', 'author' => $user_id, 'posts_per_page' => -1, 'post_status' => 'any'));
        }

        echo '<div class="dashboard-header"><h2 class="dashboard-title">' . __('Refunds', 'obenlo') . '</h2></div>';
        echo '<table class="admin-table"><thead><tr><th>Booking</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';

        if (empty($refunds)) {
            echo '<tr><td colspan="5" style="text-align:center; padding:40px;">' . __('No refund history found.', 'obenlo') . '</td></tr>';
        } else {
            foreach ($refunds as $refund) {
                $booking_id = get_post_meta($refund->ID, '_obenlo_booking_id', true);
                $status     = get_post_meta($refund->ID, '_obenlo_refund_status', true);
                $amount     = get_post_meta($booking_id, '_obenlo_total_price', true);

                echo '<tr>';
                echo '<td data-label="Booking">#' . esc_html($booking_id) . '</td>';
                echo '<td data-label="Amount">$' . number_format(floatval($amount), 2) . '</td>';
                echo '<td data-label="Status"><span class="badge badge-info" style="background:' . ($status === 'completed' ? '#ecfdf5; color:#059669;' : ($status === 'pending' ? '#fff7ed; color:#d97706;' : '#fef2f2; color:#dc2626;')) . '">' . ucfirst($status) . '</span></td>';
                echo '<td data-label="Date">' . get_the_date('', $refund->ID) . '</td>';
                echo '<td data-label="Actions">';
                if ($status === 'pending') {
                    echo '<div style="display:flex; gap:8px;">';
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="margin:0;">';
                    echo '<input type="hidden" name="action" value="obenlo_host_refund_action">';
                    echo '<input type="hidden" name="refund_id" value="' . $refund->ID . '">';
                    echo '<input type="hidden" name="refund_status" value="approved">';
                    wp_nonce_field('host_refund_action', 'host_refund_nonce');
                    echo '<button type="submit" style="background:#10b981; border:none; color:#fff; padding:6px 12px; font-size:12px; border-radius:8px; cursor:pointer;" onclick="return confirm(\'Approve this refund? This will deduct from your balance.\')">Approve</button>';
                    echo '</form>';
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="margin:0;">';
                    echo '<input type="hidden" name="action" value="obenlo_host_refund_action">';
                    echo '<input type="hidden" name="refund_id" value="' . $refund->ID . '">';
                    echo '<input type="hidden" name="refund_status" value="rejected">';
                    wp_nonce_field('host_refund_action', 'host_refund_nonce');
                    echo '<button type="submit" style="background:#ef4444; border:none; color:#fff; padding:6px 12px; font-size:12px; border-radius:8px; cursor:pointer;" onclick="return confirm(\'Reject this refund?\')">Reject</button>';
                    echo '</form></div>';
                } else {
                    echo '<span style="color:#aaa; font-size:12px;">' . __('No actions available', 'obenlo') . '</span>';
                }
                echo '</td></tr>';
            }
        }
        echo '</tbody></table>';
    }

    public function handle_save_testimony() {
        if (!is_user_logged_in()) wp_die('Unauthorized');

        if (!isset($_POST['testimony_nonce']) || !wp_verify_nonce($_POST['testimony_nonce'], 'save_testimony')) {
            wp_die('Security check failed');
        }

        $user_id     = get_current_user_id();
        $title       = sanitize_text_field($_POST['testimony_title']);
        $content     = sanitize_textarea_field($_POST['testimony_content']);
        $rating      = isset($_POST['testimony_rating']) ? intval($_POST['testimony_rating']) : 5;

        $testimony_id = wp_insert_post(array(
            'post_type'    => 'testimony',
            'post_status'  => 'pending',
            'post_title'   => $title,
            'post_content' => $content,
            'post_author'  => $user_id,
        ));

        if (!is_wp_error($testimony_id)) {
            update_post_meta($testimony_id, '_obenlo_testimony_rating', $rating);
        }

        $user       = wp_get_current_user();
        $is_host    = in_array('host', (array)$user->roles);
        $redir      = $is_host ? home_url('/host-dashboard') : home_url('/account');
        wp_safe_redirect(add_query_arg(array('action' => 'testimony', 'message' => 'saved'), $redir));
        exit;
    }
}
