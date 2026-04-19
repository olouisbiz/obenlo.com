<?php
/**
 * Host Overview & Verification Tab
 * Single Responsibility: Stats dashboard + identity verification UI.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Overview
{
    public function render_overview_tab()
    {
        $user_id = get_current_user_id();
        $user    = wp_get_current_user();

        $listings_count = count(get_posts(array('post_type' => 'listing', 'author' => $user_id, 'post_parent' => 0, 'posts_per_page' => -1, 'suppress_filters' => false)));

        $bookings = get_posts(array(
            'post_type' => 'booking',
            'posts_per_page' => -1,
            'meta_query' => array(array('key' => '_obenlo_host_id', 'value' => $user_id))
        ));
        $total_earnings = 0;
        foreach ($bookings as $booking) {
            if (get_post_meta($booking->ID, '_obenlo_booking_status', true) === 'completed') {
                $total_earnings += floatval(get_post_meta($booking->ID, '_obenlo_total_price', true));
            }
        }

        $reviews = get_comments(array(
            'author__not_in' => array($user_id),
            'post__in' => get_posts(array('post_type' => 'listing', 'author' => $user_id, 'fields' => 'ids', 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null, 'suppress_filters' => true)),
            'status' => 'approve',
            'parent' => 0
        ));
        $avg_rating = 0;
        if (!empty($reviews)) {
            $total_rating = 0;
            foreach ($reviews as $review) {
                $total_rating += intval(get_comment_meta($review->comment_ID, '_obenlo_rating', true));
            }
            $avg_rating = round($total_rating / count($reviews), 1);
        }

        $verification_status = Obenlo_Booking_Host_Verification::get_status($user_id);
        $status_label = ucfirst($verification_status);
        $status_class = 'badge-warning';
        if ($verification_status === 'verified') $status_class = 'badge-success';
        if ($verification_status === 'rejected')  $status_class = 'badge-danger';
        ?>
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px;">
            <div>
                <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; margin-top: 0; color: #222;">Welcome back, <?php echo esc_html($user->first_name ?: $user->display_name); ?>!</h2>
                <p style="color:#666; font-size:1.05rem; margin-top:0; margin-bottom:15px;"><?php echo __('Manage your hosting business, communicate with guests, and view your performance.', 'obenlo'); ?></p>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge <?php echo $status_class; ?>"><?php echo __('Account Status:', 'obenlo'); ?> <?php echo esc_html($status_label); ?></span>
                    <?php if ($verification_status !== 'verified'): ?>
                        <a href="<?php echo home_url('/host-dashboard?action=verification'); ?>" style="font-size:0.85rem; color:#e61e4d; font-weight:700; text-decoration:none;"><?php echo __('Complete Verification →', 'obenlo'); ?></a>
                    <?php else: ?>
                        <a href="<?php echo home_url('/host-onboarding?step=3&force=1'); ?>" style="font-size:0.85rem; color:#666; font-weight:600; text-decoration:none;"><?php echo __('Update Payouts', 'obenlo'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" style="color:#e61e4d; text-decoration:none; font-weight:700; font-size:0.9rem; display:flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <?php echo __('Preview My Storefront', 'obenlo'); ?>
            </a>
        </div>

        <!-- Quick Actions -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:40px;">
            <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div>
                    <div style="width:48px; height:48px; background:#eff6ff; color:#3b82f6; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;"><?php echo __('My Listings', 'obenlo'); ?></h3>
                    <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;"><?php echo __('Manage your properties, edit details, and adjust pricing.', 'obenlo'); ?></p>
                </div>
                <a href="?action=list" style="margin-top:20px; display:inline-block; font-weight:700; color:#3b82f6; text-decoration:none;"><?php echo __('View Listings &rarr;', 'obenlo'); ?></a>
            </div>
            <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div>
                    <div style="width:48px; height:48px; background:#fef2f2; color:#ef4444; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;"><?php echo __('Latest Bookings', 'obenlo'); ?></h3>
                    <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;"><?php echo __('Approve requests and prepare for upcoming guests.', 'obenlo'); ?></p>
                </div>
                <a href="?action=bookings" style="margin-top:20px; display:inline-block; font-weight:700; color:#ef4444; text-decoration:none;"><?php echo __('Manage Bookings &rarr;', 'obenlo'); ?></a>
            </div>
            <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div>
                    <div style="width:48px; height:48px; background:#ecfdf5; color:#10b981; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                    </div>
                    <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;"><?php echo __('Host Guide', 'obenlo'); ?></h3>
                    <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;"><?php echo __('Tips on boosting occupancy and following policies.', 'obenlo'); ?></p>
                </div>
                <a href="?action=guide" style="margin-top:20px; display:inline-block; font-weight:700; color:#10b981; text-decoration:none;"><?php echo __('Read Guide &rarr;', 'obenlo'); ?></a>
            </div>
        </div>

        <h3 style="margin-top:0; margin-bottom:20px; font-weight:800; font-size:1.5rem; color:#222;"><?php echo __('At a Glance', 'obenlo'); ?></h3>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label"><?php echo __('Total Earnings', 'obenlo'); ?></span>
                <span class="stat-value">$<?php echo number_format($total_earnings, 2); ?></span>
                <span style="font-size:0.8rem; color:#10b981; font-weight:600;"><?php echo __('Completed Bookings', 'obenlo'); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label"><?php echo __('Active Listings', 'obenlo'); ?></span>
                <span class="stat-value"><?php echo $listings_count; ?></span>
                <span style="font-size:0.8rem; color:#3b82f6; font-weight:600;"><?php echo __('Live on Obenlo', 'obenlo'); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label"><?php echo __('Total Bookings', 'obenlo'); ?></span>
                <span class="stat-value"><?php echo count($bookings); ?></span>
                <span style="font-size:0.8rem; color:#f97316; font-weight:600;"><?php echo __('Lifetime volume', 'obenlo'); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label"><?php echo __('Avg. Rating', 'obenlo'); ?></span>
                <span class="stat-value"><?php echo $avg_rating ?: 'N/A'; ?> ★</span>
                <span style="font-size:0.8rem; color:#eab308; font-weight:600;"><?php echo __('Host Reputation', 'obenlo'); ?></span>
            </div>
        </div>

        <div class="dashboard-grid-layout" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:30px;">
            <div class="form-section" style="margin-top:0;">
                <h4 style="margin-top:0; margin-bottom:20px;"><?php echo __('Recent Bookings', 'obenlo'); ?></h4>
                <?php
                $bm = new Obenlo_Host_Bookings();
                $bm->render_bookings_list(5);
                ?>
            </div>
            <div class="form-section" style="margin-top:0;">
                <h4 style="margin-top:0; margin-bottom:20px;"><?php echo __('Performance', 'obenlo'); ?></h4>
                <p style="color:#666; font-size:0.9rem;"><?php echo __('Your response rate and booking conversion stats will appear here as your hosting history grows.', 'obenlo'); ?></p>
            </div>
        </div>
        <?php
    }

    public function render_verification_tab() {
        $user_id = get_current_user_id();
        $status  = Obenlo_Booking_Host_Verification::get_status($user_id);

        echo '<div class="dashboard-header"><h2 class="dashboard-title">Identity Verification</h2></div>';

        if ($status === 'verified') {
            echo '<div style="background:#dcfce7; color:#166534; padding:20px; border-radius:12px; font-weight:500;">✓ Your identity has been successfully verified. Thank you for building trust in the Obenlo community.</div>';
            return;
        }
        if ($status === 'pending') {
            echo '<div style="background:#fef9c3; color:#854d0e; padding:20px; border-radius:12px; font-weight:500;">⏳ Your verification document is currently under review. Approval usually takes 24-48 hours.</div>';
            return;
        }
        if ($status === 'rejected') {
            echo '<div style="background:#fee2e2; color:#991b1b; padding:20px; border-radius:12px; margin-bottom:20px; font-weight:500;">⚠️ Your previous verification attempt was rejected. Please ensure the document is clear and a valid government-issued ID.</div>';
        }

        echo '<div style="background:#fff; padding:30px; border-radius:16px; border:1px solid #ddd; max-width:600px;">';
        echo '<h3 style="margin-top:0;">Upload Identification</h3>';
        echo '<p style="color:#666; font-size:0.95em;">To keep our community safe, we require all hosts to upload a valid government-issued ID before accepting bookings.</p>';
        echo '<div style="margin: 25px 0;"><input type="file" id="id_document_input" accept="image/jpeg,image/png,application/pdf" style="display:block; width:100%; padding:15px; border:2px dashed #ddd; border-radius:8px; cursor:pointer;"><small style="color:#888; display:block; margin-top:8px;">Max file size: 5MB. Formats: JPG, PNG, PDF</small></div>';
        echo '<button id="submit_verification" class="btn-primary" style="width:100%; border:none; padding:15px; border-radius:10px; cursor:pointer;">Submit Document for Review</button>';
        echo '</div>';

        echo "<script>
        document.getElementById('submit_verification').addEventListener('click', function() {
            const fileInput = document.getElementById('id_document_input');
            if (!fileInput.files[0]) { alert('Please select a file to upload.'); return; }
            this.textContent = 'Uploading...'; this.style.opacity = '0.7'; this.disabled = true;
            const formData = new FormData();
            formData.append('action', 'obenlo_upload_id');
            formData.append('security', '" . wp_create_nonce("obenlo_onboarding_nonce") . "');
            formData.append('id_document', fileInput.files[0]);
            fetch('" . admin_url('admin-ajax.php') . "', { method: 'POST', body: formData })
            .then(r => r.json()).then(res => {
                if (res.success) { window.location.reload(); }
                else { alert(res.data || 'Upload failed.'); this.textContent = 'Submit Document for Review'; this.style.opacity = '1'; this.disabled = false; }
            }).catch(() => { alert('Connection error.'); this.textContent='Submit Document for Review'; this.style.opacity='1'; this.disabled=false; });
        });
        </script>";
    }
}
