<?php
/**
 * Demo Manager Module
 * Single Responsibility: Handle the creation, syncing, and rendering of demo accounts and demo data.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Demo_Manager
{
    /**
     * Render the demo setup UI fields within the listing form.
     */
    public static function render_demo_setup_ui($listing_id = 0)
    {
        $is_demo_edit   = ($listing_id > 0 && get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes');
        $is_demo_create = (isset($_GET['demo']) && $_GET['demo'] == '1');
        
        if (!($is_demo_edit || $is_demo_create) || !current_user_can('administrator')) {
            return;
        }

        $demo_logo   = get_post_meta($listing_id, '_obenlo_demo_host_logo', true);
        $demo_banner = get_post_meta($listing_id, '_obenlo_demo_host_banner', true);
        $demo_hours  = get_post_meta($listing_id, '_obenlo_demo_business_hours', true) ?: array();
        ?>
        <input type="hidden" name="is_demo" value="1">
        <div style="background:#fff1f3; color:#e61e4d; padding:25px; border-radius:20px; margin-bottom:40px; border:1px solid #fecdd3; box-shadow:0 10px 30px rgba(230,30,77,0.05);">
            <h4 style="margin-top:0; margin-bottom:15px; color:#e61e4d; display:flex; align-items:center; gap:10px;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                <?php echo __('Super-Admin Demo Setup', 'obenlo'); ?>
            </h4>
            <p style="margin-top:0; margin-bottom:25px; font-size:0.95rem; opacity:0.8;"><?php echo __('Simulate a professional storefront by providing the profile assets and availability for this ghost host.', 'obenlo'); ?></p>
            <div class="grid-row" style="margin-bottom:20px;">
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Host Display Name', 'obenlo'); ?></label><input type="text" name="_obenlo_demo_host_name" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_name', true)); ?>" placeholder="e.g. Marie Antoinette" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Host Tagline', 'obenlo'); ?></label><input type="text" name="_obenlo_demo_host_tagline" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_tagline', true)); ?>" placeholder="e.g. Master Chef &amp; Host" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-bottom:25px;">
                <div><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Profile Logo', 'obenlo'); ?></label><?php if($demo_logo): ?><img src="<?php echo wp_get_attachment_image_url($demo_logo, 'thumbnail'); ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-bottom:8px; display:block; border:2px solid #fff;"><?php endif; ?><input type="file" name="demo_host_logo" accept="image/*" style="font-size:0.8rem;"></div>
                <div><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Storefront Banner', 'obenlo'); ?></label><?php if($demo_banner): ?><img src="<?php echo wp_get_attachment_image_url($demo_banner, 'medium'); ?>" style="width:100%; height:60px; border-radius:8px; object-fit:cover; margin-bottom:8px; display:block; border:2px solid #fff;"><?php endif; ?><input type="file" name="demo_host_banner" accept="image/*" style="font-size:0.8rem;"></div>
            </div>
            <div class="grid-row" style="margin-bottom:20px;">
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;">Demo Instagram (e.g. @obenlo)</label><input type="text" name="_obenlo_demo_host_instagram" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_instagram', true)); ?>" placeholder="@username" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;">Demo Facebook URL</label><input type="text" name="_obenlo_demo_host_facebook" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_facebook', true)); ?>" placeholder="https://facebook.com/..." style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
            </div>
            <div style="margin-bottom:20px;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Host Specialties', 'obenlo'); ?></label><input type="text" name="_obenlo_demo_host_specialties" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_specialties', true)); ?>" placeholder="<?php echo esc_attr(__('e.g. Organic, Pet Friendly, Multilingual', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"><p style="font-size:0.75rem; color:#e61e4d; opacity:0.7; margin-top:5px;"><?php echo __('Separate your specialties with commas.', 'obenlo'); ?></p></div>
            <div class="grid-row" style="margin-bottom:20px;">
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Demo Host Location', 'obenlo'); ?></label><input type="text" name="_obenlo_demo_host_location" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_location', true)); ?>" placeholder="e.g. Port-au-Prince, Haiti" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
                <div style="flex:1;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Featured Video (YouTube/Vimeo)', 'obenlo'); ?></label><input type="url" name="_obenlo_demo_host_video" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_video', true)); ?>" placeholder="https://www.youtube.com/watch?v=..." style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;"></div>
            </div>
            <div style="margin-bottom:25px;"><label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:8px;"><?php echo __('Demo Host Bio (Storefront Text)', 'obenlo'); ?></label><textarea name="_obenlo_demo_host_bio" rows="4" style="width:100%; padding:12px; border:1px solid #fecdd3; border-radius:10px;" placeholder="Write a compelling story..."><?php echo esc_textarea(get_post_meta($listing_id, '_obenlo_demo_host_bio', true)); ?></textarea></div>
            <div style="background:rgba(255,255,255,0.5); padding:20px; border-radius:15px; border:1px solid #fecdd3;">
                <h5 style="margin-top:0; margin-bottom:15px; color:#e61e4d;"><?php echo __('Simulated Availability', 'obenlo'); ?></h5>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:10px; margin-bottom:10px;">
                    <?php
                    $days = array('monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun');
                    foreach($days as $key => $label):
                        $active = isset($demo_hours[$key]['active']) && $demo_hours[$key]['active'] === 'yes';
                    ?>
                        <label style="background:#fff; padding:10px; border-radius:8px; border:1px solid #fecdd3; display:flex; flex-direction:column; align-items:center; gap:5px; font-size:0.75rem;">
                            <input type="checkbox" name="demo_hours[<?php echo $key; ?>][active]" value="yes" <?php checked($active); ?>>
                            <strong><?php echo $label; ?></strong>
                            <span style="font-size:0.65rem; color:#888;">9AM - 5PM</span>
                            <input type="hidden" name="demo_hours[<?php echo $key; ?>][start]" value="09:00">
                            <input type="hidden" name="demo_hours[<?php echo $key; ?>][end]" value="17:00">
                        </label>
                    <?php endforeach; ?>
                </div>
                <p style="font-size:0.75rem; color:#666; margin-bottom:0; font-style:italic;">* Availability is simplified to 9AM-5PM for active days in demo mode.</p>
            </div>
        </div>
        <?php
    }

    /**
     * Create or retrieve a demo user and assign ownership of the listing.
     * Called immediately after wp_insert_post for a new listing.
     */
    public static function assign_demo_ownership($post_id)
    {
        if (!isset($_POST['is_demo']) || $_POST['is_demo'] !== '1' || !current_user_can('administrator')) {
            return;
        }

        $demo_host_name = !empty($_POST['_obenlo_demo_host_name']) ? sanitize_text_field($_POST['_obenlo_demo_host_name']) : '';
        if ($demo_host_name) {
            $demo_user_login = 'demo_' . sanitize_title($demo_host_name);
            $demo_user_id    = username_exists($demo_user_login);
            
            if (!$demo_user_id) {
                $demo_user_id = wp_create_user($demo_user_login, wp_generate_password(), $demo_user_login . '@obenlo.com');
                if (!is_wp_error($demo_user_id)) {
                    wp_update_user(['ID' => $demo_user_id, 'display_name' => $demo_host_name]);
                    $du = new WP_User($demo_user_id); 
                    $du->set_role('host');
                    update_user_meta($demo_user_id, '_obenlo_is_demo_account', 'yes');
                }
            }
            if ($demo_user_id && !is_wp_error($demo_user_id)) {
                wp_update_post(['ID' => $post_id, 'post_author' => $demo_user_id]);
            }
        }
    }

    /**
     * Save the demo configuration metadata to the listing and sync to the user profile.
     */
    public static function save_demo_configuration($post_id)
    {
        if (!isset($_POST['is_demo']) || $_POST['is_demo'] !== '1' || !current_user_can('administrator')) {
            return;
        }

        update_post_meta($post_id, '_obenlo_is_demo', 'yes');
        
        $demo_text_fields = [
            '_obenlo_demo_host_name',
            '_obenlo_demo_host_tagline',
            '_obenlo_demo_host_location',
            '_obenlo_demo_host_specialties',
            '_obenlo_demo_host_instagram'
        ];
        
        foreach ($demo_text_fields as $f) { 
            if (isset($_POST[$f])) {
                update_post_meta($post_id, $f, sanitize_text_field($_POST[$f])); 
            }
        }
        
        if (isset($_POST['_obenlo_demo_host_bio']))      update_post_meta($post_id, '_obenlo_demo_host_bio',      sanitize_textarea_field(wp_unslash($_POST['_obenlo_demo_host_bio'])));
        if (isset($_POST['_obenlo_demo_host_facebook'])) update_post_meta($post_id, '_obenlo_demo_host_facebook', esc_url_raw($_POST['_obenlo_demo_host_facebook']));
        if (isset($_POST['_obenlo_demo_host_video']))    update_post_meta($post_id, '_obenlo_demo_host_video',    esc_url_raw($_POST['_obenlo_demo_host_video']));

        // Sync virtual user meta
        $virtual_user_id = (int) get_post_field('post_author', $post_id);
        if ($virtual_user_id && $virtual_user_id != get_current_user_id()) {
            if (isset($_POST['_obenlo_demo_host_name']))       update_user_meta($virtual_user_id, 'obenlo_store_name',        sanitize_text_field($_POST['_obenlo_demo_host_name']));
            if (isset($_POST['_obenlo_demo_host_bio']))        update_user_meta($virtual_user_id, 'obenlo_store_description', sanitize_textarea_field(wp_unslash($_POST['_obenlo_demo_host_bio'])));
            if (isset($_POST['_obenlo_demo_host_location']))   update_user_meta($virtual_user_id, 'obenlo_store_location',    sanitize_text_field($_POST['_obenlo_demo_host_location']));
            if (isset($_POST['_obenlo_demo_host_tagline']))    update_user_meta($virtual_user_id, 'obenlo_store_tagline',     sanitize_text_field($_POST['_obenlo_demo_host_tagline']));
            if (isset($_POST['_obenlo_demo_host_specialties'])) update_user_meta($virtual_user_id, 'obenlo_specialties',      sanitize_text_field($_POST['_obenlo_demo_host_specialties']));
            if (isset($_POST['_obenlo_demo_host_instagram']))  update_user_meta($virtual_user_id, 'obenlo_instagram',         sanitize_text_field($_POST['_obenlo_demo_host_instagram']));
            if (isset($_POST['_obenlo_demo_host_facebook']))   update_user_meta($virtual_user_id, 'obenlo_facebook',          sanitize_text_field($_POST['_obenlo_demo_host_facebook']));
        }

        // Demo media uploads
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        if (isset($_FILES['demo_host_logo']) && !empty($_FILES['demo_host_logo']['name'])) {
            $att = media_handle_upload('demo_host_logo', $post_id);
            if (!is_wp_error($att)) update_post_meta($post_id, '_obenlo_demo_host_logo', $att);
        }
        if (isset($_FILES['demo_host_banner']) && !empty($_FILES['demo_host_banner']['name'])) {
            $att = media_handle_upload('demo_host_banner', $post_id);
            if (!is_wp_error($att)) update_post_meta($post_id, '_obenlo_demo_host_banner', $att);
        }

        // Demo availability
        if (isset($_POST['demo_hours'])) {
            $d_hours = (array)$_POST['demo_hours']; 
            $sanitized = array();
            foreach ($d_hours as $day => $d_data) {
                $sanitized[sanitize_key($day)] = array(
                    'active' => (isset($d_data['active']) && $d_data['active'] === 'yes') ? 'yes' : 'no', 
                    'start' => '09:00', 
                    'end' => '17:00'
                );
            }
            update_post_meta($post_id, '_obenlo_demo_business_hours', $sanitized);
        }
    }
}
