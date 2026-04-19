<?php
/**
 * Host Storefront Module
 * Single Responsibility: Storefront settings form UI + save handler.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Storefront
{
    public function init()
    {
        add_action('admin_post_obenlo_dashboard_save_storefront', array($this, 'handle_save_storefront'));
    }

    private function redirect_with_error($error_code) {
        obenlo_redirect_with_error($error_code);
    }

    public function render_storefront_form()
    {
        $user_id          = get_current_user_id();
        $store_name       = get_user_meta($user_id, 'obenlo_store_name', true);
        $store_desc       = get_user_meta($user_id, 'obenlo_store_description', true);
        $store_location   = get_user_meta($user_id, 'obenlo_store_location', true);
        $store_logo_id    = get_user_meta($user_id, 'obenlo_store_logo', true);
        $store_banner_id  = get_user_meta($user_id, 'obenlo_store_banner', true);
        $store_tagline    = get_user_meta($user_id, 'obenlo_store_tagline', true);
        $store_video      = get_user_meta($user_id, 'obenlo_store_video', true);
        $social_insta     = get_user_meta($user_id, 'obenlo_instagram', true);
        $social_fb        = get_user_meta($user_id, 'obenlo_facebook', true);
        $store_specialties = get_user_meta($user_id, 'obenlo_specialties', true);
        ?>
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="dashboard-title"><?php echo __('Storefront Settings', 'obenlo'); ?></h2>
            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" class="btn-primary" style="padding:10px 20px; font-size:0.9rem; display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px; height:16px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <?php echo __('View Live Storefront', 'obenlo'); ?>
            </a>
        </div>

        <div style="max-width:800px;">
            <p style="color:#666; font-size:0.95rem; margin-bottom:30px;"><?php echo __('Customize how your host profile appears to guests. A professional storefront builds trust and increases bookings.', 'obenlo'); ?></p>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="obenlo_dashboard_save_storefront">
                <?php wp_nonce_field('dashboard_save_storefront', 'dashboard_storefront_nonce'); ?>

                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Public Profile', 'obenlo'); ?></h4>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Host / Store Name', 'obenlo'); ?></label>
                        <input type="text" name="store_name" value="<?php echo esc_attr($store_name); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Host Location', 'obenlo'); ?></label>
                        <input type="text" name="store_location" value="<?php echo esc_attr($store_location); ?>" placeholder="<?php echo esc_attr(__('e.g. New York, NY', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Tagline (Catchy Hook)', 'obenlo'); ?></label>
                        <input type="text" name="store_tagline" value="<?php echo esc_attr($store_tagline); ?>" placeholder="<?php echo esc_attr(__('e.g. Luxury Haircare in the heart of Paris', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Host Specialties', 'obenlo'); ?></label>
                        <input type="text" name="store_specialties" value="<?php echo esc_attr($store_specialties); ?>" placeholder="<?php echo esc_attr(__('e.g. Organic, Pet Friendly, Multilingual', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        <p style="font-size:0.8rem; color:#888; margin-top:5px;"><?php echo __('Separate your specialties with commas.', 'obenlo'); ?></p>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Description / Bio', 'obenlo'); ?></label>
                        <textarea name="store_description" rows="5" placeholder="<?php echo esc_attr(__('Tell guests about yourself or your hospitality business...', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($store_desc); ?></textarea>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Featured Video (YouTube/Vimeo)', 'obenlo'); ?></label>
                        <input type="url" name="store_video" value="<?php echo esc_attr($store_video); ?>" placeholder="https://www.youtube.com/watch?v=..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Instagram Profile', 'obenlo'); ?></label>
                            <input type="text" name="social_insta" value="<?php echo esc_attr($social_insta); ?>" placeholder="@youraccount" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Facebook Page', 'obenlo'); ?></label>
                            <input type="text" name="social_fb" value="<?php echo esc_attr($social_fb); ?>" placeholder="facebook.com/yourpage" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Branding & Identity', 'obenlo'); ?></h4>
                    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:12px; color:#444;"><?php echo __('Host Logo', 'obenlo'); ?></label>
                            <?php if ($store_logo_id): ?>
                                <div style="margin-bottom:15px; position:relative; width:120px; height:120px;">
                                    <img src="<?php echo esc_url(wp_get_attachment_image_url($store_logo_id, 'thumbnail')); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid #eee;">
                                    <label style="display:block; margin-top:10px; color:#ef4444; font-size:0.8rem; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" name="remove_logo" value="1"> <?php echo __('Remove', 'obenlo'); ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="store_logo" accept="image/*" style="font-size:0.8rem;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:12px; color:#444;"><?php echo __('Store Banner', 'obenlo'); ?></label>
                            <?php if ($store_banner_id): ?>
                                <div style="margin-bottom:15px;">
                                    <img src="<?php echo esc_url(wp_get_attachment_image_url($store_banner_id, 'medium')); ?>" style="width:100%; height:100px; border-radius:12px; object-fit:cover; border:1px solid #eee;">
                                    <label style="display:block; margin-top:10px; color:#ef4444; font-size:0.8rem; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" name="remove_banner" value="1"> <?php echo __('Remove', 'obenlo'); ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="store_banner" accept="image/*" style="font-size:0.8rem;">
                        </div>
                    </div>
                </div>

                <div style="margin-top:40px; margin-bottom:100px;">
                    <button type="submit" class="btn-primary" style="padding:15px 40px; font-size:1.1rem; width:100%;"><?php echo __('Update My Storefront', 'obenlo'); ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    public function handle_save_storefront()
    {
        if (!isset($_POST['dashboard_storefront_nonce']) || !wp_verify_nonce($_POST['dashboard_storefront_nonce'], 'dashboard_save_storefront')) {
            $this->redirect_with_error('security_failed');
        }
        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $user_id = get_current_user_id();

        if (isset($_POST['store_name'])) {
            $store_name = sanitize_text_field($_POST['store_name']);
            update_user_meta($user_id, 'obenlo_store_name', $store_name);
            $new_slug = sanitize_title($store_name);
            if (!empty($new_slug)) wp_update_user(array('ID' => $user_id, 'user_nicename' => $new_slug));
        }
        if (isset($_POST['store_description'])) update_user_meta($user_id, 'obenlo_store_description', sanitize_textarea_field(wp_unslash($_POST['store_description'])));
        if (isset($_POST['store_location']))    update_user_meta($user_id, 'obenlo_store_location', sanitize_text_field($_POST['store_location']));
        if (isset($_POST['store_tagline']))     update_user_meta($user_id, 'obenlo_store_tagline', sanitize_text_field($_POST['store_tagline']));
        if (isset($_POST['store_video']))       update_user_meta($user_id, 'obenlo_store_video', esc_url_raw($_POST['store_video']));
        if (isset($_POST['social_insta']))      update_user_meta($user_id, 'obenlo_instagram', sanitize_text_field($_POST['social_insta']));
        if (isset($_POST['social_fb']))         update_user_meta($user_id, 'obenlo_facebook', sanitize_text_field($_POST['social_fb']));
        if (isset($_POST['store_specialties'])) update_user_meta($user_id, 'obenlo_specialties', sanitize_text_field($_POST['store_specialties']));

        // Removals
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
            $old = get_user_meta($user_id, 'obenlo_store_logo', true);
            if ($old) wp_delete_attachment($old, true);
            delete_user_meta($user_id, 'obenlo_store_logo');
        }
        if (isset($_POST['remove_banner']) && $_POST['remove_banner'] == '1') {
            $old = get_user_meta($user_id, 'obenlo_store_banner', true);
            if ($old) wp_delete_attachment($old, true);
            delete_user_meta($user_id, 'obenlo_store_banner');
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        if (isset($_FILES['store_logo']) && !empty($_FILES['store_logo']['name'])) {
            $id = media_handle_upload('store_logo', 0);
            if (!is_wp_error($id)) update_user_meta($user_id, 'obenlo_store_logo', $id);
        }
        if (isset($_FILES['store_banner']) && !empty($_FILES['store_banner']['name'])) {
            $id = media_handle_upload('store_banner', 0);
            if (!is_wp_error($id)) update_user_meta($user_id, 'obenlo_store_banner', $id);
        }

        $redirect_url = add_query_arg(array('action' => 'storefront', 'message' => 'saved'), home_url('/host-dashboard'));
        wp_safe_redirect($redirect_url);
        exit;
    }
}
