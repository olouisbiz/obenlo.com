<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Demo_Manager
{
    public function init()
    {
        add_action('admin_post_obenlo_transfer_demo', array($this, 'handle_transfer_demo'));
        add_action('admin_post_obenlo_toggle_demo_visibility', array($this, 'handle_toggle_demo_visibility'));
        add_action('admin_post_obenlo_delete_demo', array($this, 'handle_delete_demo'));
    }

    public function render_demo_manager_tab()
    {
        $demos = get_posts(array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'meta_key' => '_obenlo_is_demo',
            'meta_value' => 'yes',
            'post_status' => array('publish', 'pending', 'draft')
        ));

        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">';
        echo '<div>';
        echo '<h3 style="margin-bottom:5px;">Demo Listing Manager</h3>';
        echo '<p style="color:#666; margin:0;">Create high-quality demo listings as an admin, then transfer them to new hosts to jumpstart their profile.</p>';
        echo '</div>';
        echo '<a href="' . esc_url(home_url('/host-dashboard?action=add&demo=1')) . '" style="background:#e61e4d; color:#fff; text-decoration:none; padding:10px 20px; border-radius:8px; font-weight:bold;">+ Create Demo Listing</a>';
        echo '</div>';

        // --- SECTION 2: Demo Listing Manager ---
        echo '<h4 style="margin: 30px 0 15px;">Demo Listing Manager</h4>';
        echo '<p style="color:#666; font-size:0.9rem; margin-bottom:15px;">Manage all listings marked as demos, ensuring they are attached to the correct virtual or admin owners.</p>';
        
        echo '<table class="admin-table">';
        echo '<tr><th>Owner Identity</th><th>Demo Bio</th><th>Location</th><th>Actions</th></tr>';
        
        if (empty($demos)) {
            echo '<tr><td colspan="4" style="padding:40px; text-align:center; color:#999;">No demo listings created. <a href="' . esc_url(home_url('/host-dashboard?action=add&demo=1')) . '" style="color:#e61e4d; font-weight:bold;">Create one now</a></td></tr>';
        } else {
            foreach ($demos as $demo) {
                $owner_name = get_the_author_meta('display_name', $demo->post_author);
                $is_demo_acc = get_user_meta($demo->post_author, '_obenlo_is_demo_account', true) === 'yes';

                $d_name = get_post_meta($demo->ID, '_obenlo_demo_host_name', true) ?: $owner_name;
                $d_bio = get_post_meta($demo->ID, '_obenlo_demo_host_bio', true) ?: get_the_author_meta('description', $demo->post_author);
                $d_loc = get_post_meta($demo->ID, '_obenlo_demo_host_location', true) ?: get_user_meta($demo->post_author, 'obenlo_store_location', true);

                $is_hidden = get_post_meta($demo->ID, '_obenlo_demo_hidden', true) === 'yes';
                $vis_text = $is_hidden ? 'Show' : 'Hide';
                $vis_color = $is_hidden ? '#10b981' : '#666';

                echo '<tr>';
                echo '<td><strong>' . esc_html($d_name) . '</strong>' . ($is_hidden ? ' <span class="badge" style="background:#666; color:#fff; font-size:0.6rem;">HIDDEN</span>' : '') . '<br><small>' . esc_html($demo->post_title) . '</small><br><span style="font-size:0.75rem; color:#666; background:#f0f0f0; padding:2px 6px; border-radius:4px; margin-top:4px; display:inline-block;">Account: ' . ($is_demo_acc ? '👤 ' : '🛡️ Admin: ') . esc_html($owner_name) . '</span></td>';
                echo '<td style="max-width:300px; font-size:0.85rem;">' . wp_trim_words($d_bio, 15) . '</td>';
                echo '<td>' . esc_html($d_loc) . '</td>';
                echo '<td>';
                echo '<div style="display:flex; gap:15px; align-items:center;">';
                echo '<a href="' . get_permalink($demo->ID) . '" target="_blank" style="color:#333; font-weight:600; text-decoration:none;">View</a>';
                echo '<a href="' . esc_url(home_url("/host-dashboard?action=edit&listing_id={$demo->ID}&demo=1")) . '" style="color:#e61e4d; font-weight:700; text-decoration:none;">Edit Setup</a>';
                
                // Visibility Toggle
                echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline; margin:0;">';
                echo '<input type="hidden" name="action" value="obenlo_toggle_demo_visibility">';
                echo '<input type="hidden" name="listing_id" value="' . $demo->ID . '">';
                wp_nonce_field('toggle_demo_vis_' . $demo->ID, 'vis_nonce');
                echo '<button type="submit" style="background:none; border:none; color:' . $vis_color . '; cursor:pointer; font-weight:700; font-size:0.9rem; text-decoration:none; padding:0;">' . $vis_text . '</button>';
                echo '</form>';
                
                // Transfer Form
                echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:flex; gap:5px; margin:0;" onsubmit="return confirm(\'Are you sure you want to transfer this demo to a real host? This will move all demo data to their profile.\')">';
                echo '<input type="hidden" name="action" value="obenlo_transfer_demo">';
                echo '<input type="hidden" name="listing_id" value="' . $demo->ID . '">';
                wp_nonce_field('transfer_demo_' . $demo->ID, 'transfer_nonce');
                
                $users = get_users(array('role' => 'host', 'number' => 20));
                echo '<select name="target_user_id" required style="font-size:0.8rem; padding:4px;">';
                echo '<option value="">Select Host...</option>';
                foreach($users as $user) {
                    if ($user->user_login === 'demo') continue;
                    echo '<option value="' . $user->ID . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_login) . ')</option>';
                }
                echo '</select>';
                
                echo '<button type="submit" style="background:#222; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:0.8em; font-weight:700;">Transfer</button>';
                echo '</form>';
                
                // Delete Form
                echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline; margin:0;" onsubmit="return confirm(\'Permanently delete this demo listing and its contents? This cannot be undone.\')">';
                echo '<input type="hidden" name="action" value="obenlo_delete_demo">';
                echo '<input type="hidden" name="listing_id" value="' . $demo->ID . '">';
                wp_nonce_field('delete_demo_' . $demo->ID, 'delete_nonce');
                echo '<button type="submit" style="background:none; border:none; color:#e61e4d; cursor:pointer; font-weight:700; font-size:0.9rem; text-decoration:underline; padding:0;">Delete</button>';
                echo '</form>';

                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    public function handle_transfer_demo()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }
        
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $user_id = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;
        
        check_admin_referer('transfer_demo_' . $listing_id, 'transfer_nonce');

        if ($listing_id && $user_id) {
            $listing = get_post($listing_id);
            if ($listing && get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes') {
                
                // 1. Reassign Author
                wp_update_post(array(
                    'ID' => $listing_id,
                    'post_author' => $user_id
                ));

                // 2. Migrate Meta to User
                $d_name = get_post_meta($listing_id, '_obenlo_demo_host_name', true);
                $d_bio = get_post_meta($listing_id, '_obenlo_demo_host_bio', true);
                $d_loc = get_post_meta($listing_id, '_obenlo_demo_host_location', true);
                $d_tag = get_post_meta($listing_id, '_obenlo_demo_host_tagline', true);
                $d_insta = get_post_meta($listing_id, '_obenlo_demo_host_instagram', true);
                $d_fb = get_post_meta($listing_id, '_obenlo_demo_host_facebook', true);
                $d_logo = get_post_meta($listing_id, '_obenlo_demo_host_logo', true);
                $d_banner = get_post_meta($listing_id, '_obenlo_demo_host_banner', true);
                $d_hours = get_post_meta($listing_id, '_obenlo_demo_business_hours', true);
                $d_spec = get_post_meta($listing_id, '_obenlo_demo_host_specialties', true);

                if ($d_name) update_user_meta($user_id, 'obenlo_store_name', $d_name);
                if ($d_bio) update_user_meta($user_id, 'obenlo_store_description', $d_bio);
                if ($d_loc) update_user_meta($user_id, 'obenlo_store_location', $d_loc);
                if ($d_tag) update_user_meta($user_id, 'obenlo_store_tagline', $d_tag);
                if ($d_insta) update_user_meta($user_id, 'obenlo_instagram', $d_insta);
                if ($d_fb) update_user_meta($user_id, 'obenlo_facebook', $d_fb);
                if ($d_logo) update_user_meta($user_id, 'obenlo_store_logo', $d_logo);
                if ($d_banner) update_user_meta($user_id, 'obenlo_store_banner', $d_banner);
                if ($d_hours) update_user_meta($user_id, '_obenlo_business_hours', $d_hours);
                if ($d_spec) update_user_meta($user_id, 'obenlo_specialties', $d_spec);

                // 3. Clean up Demo flags
                delete_post_meta($listing_id, '_obenlo_is_demo');
                delete_post_meta($listing_id, '_obenlo_demo_host_name');
                delete_post_meta($listing_id, '_obenlo_demo_host_bio');
                delete_post_meta($listing_id, '_obenlo_demo_host_location');
                delete_post_meta($listing_id, '_obenlo_demo_host_tagline');
                delete_post_meta($listing_id, '_obenlo_demo_host_instagram');
                delete_post_meta($listing_id, '_obenlo_demo_host_facebook');
                delete_post_meta($listing_id, '_obenlo_demo_host_logo');
                delete_post_meta($listing_id, '_obenlo_demo_host_banner');
                delete_post_meta($listing_id, '_obenlo_demo_business_hours');
                delete_post_meta($listing_id, '_obenlo_demo_host_specialties');
                
                // Clear any restricted mode cache for the user if applicable
                clean_user_cache($user_id);
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'demo_manager', wp_get_referer()));
        exit;
    }

    public function handle_delete_demo()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        check_admin_referer('delete_demo_' . $listing_id, 'delete_nonce');

        if ($listing_id) {
            $listing = get_post($listing_id);
            if ($listing && get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes') {
                // Delete children (units) if any
                $children = get_posts(array(
                    'post_type' => 'listing',
                    'post_parent' => $listing_id,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'post_status' => 'any'
                ));
                foreach ($children as $child_id) {
                    wp_delete_post($child_id, true);
                }
                
                // Delete the demo parent listing
                wp_delete_post($listing_id, true);
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'demo_manager', wp_get_referer()));
        exit;
    }

    public function handle_toggle_demo_visibility()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        check_admin_referer('toggle_demo_vis_' . $listing_id, 'vis_nonce');

        if ($listing_id) {
            $is_hidden = get_post_meta($listing_id, '_obenlo_demo_hidden', true) === 'yes';
            if ($is_hidden) {
                delete_post_meta($listing_id, '_obenlo_demo_hidden');
            } else {
                update_post_meta($listing_id, '_obenlo_demo_hidden', 'yes');
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'demo_manager', wp_get_referer()));
        exit;
    }

}
