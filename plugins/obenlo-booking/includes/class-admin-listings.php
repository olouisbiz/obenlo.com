<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Listings
{
    public function init()
    {
        add_action('admin_post_obenlo_toggle_listing_suspension', array($this, 'handle_toggle_listing_suspension'));
        add_action('admin_post_obenlo_trash_listing', array($this, 'handle_trash_listing'));
    }

    public function render_listings_tab()
    {
        $search = isset($_GET['listing_search']) ? sanitize_text_field($_GET['listing_search']) : '';
        $status_filter = isset($_GET['listing_status']) ? sanitize_text_field($_GET['listing_status']) : '';

        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => $status_filter ? array($status_filter) : array('publish', 'pending', 'draft'),
            'suppress_filters' => false,
        );

        if ($search) {
            $query_args['s'] = $search;
        }

        $listings = get_posts($query_args);

        echo '<h3>Manage All Listings</h3>';
        ?>
        <!-- Listing Filters -->
        <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px;">
            <form action="" method="GET" style="display:flex; gap:20px; align-items:flex-end;">
                <input type="hidden" name="page" value="obenlo-booking">
                <input type="hidden" name="tab" value="listings">
                
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Search Listings</label>
                    <input type="text" name="listing_search" value="<?php echo esc_attr($search); ?>" placeholder="Title or keyword..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Status</label>
                    <select name="listing_status" style="padding:10px; border:1px solid #ddd; border-radius:8px; min-width:150px;">
                        <option value="">All Statuses</option>
                        <option value="publish" <?php selected($status_filter, 'publish'); ?>>Published</option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                        <option value="draft" <?php selected($status_filter, 'draft'); ?>>Draft</option>
                    </select>
                </div>

                <button type="submit" style="background:#222; color:#fff; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Search</button>
                <a href="?page=obenlo-booking&tab=listings" style="padding:10px; color:#666; font-size:0.9rem; text-decoration:none;">Reset</a>
            </form>
        </div>
        <?php
        echo '<table class="admin-table">';
        echo '<tr><th>Title</th><th>Host</th><th>Status</th><th>Price</th><th>Actions</th></tr>';
        foreach ($listings as $listing) {
            $host = get_userdata($listing->post_author);
            $price = get_post_meta($listing->ID, '_obenlo_price', true);
            $is_suspended = get_post_meta($listing->ID, '_obenlo_is_suspended', true) === 'yes';
            $sus_text = $is_suspended ? 'Restore' : 'Suspend';
            $sus_class = $is_suspended ? 'btn-approve' : 'btn-reject';
            $status_bg = '#f3f4f6'; $status_color = '#374151';
            $display_status = ucfirst($listing->post_status);
            if ($listing->post_status === 'publish') { $status_bg = '#dcfce7'; $status_color = '#166534'; }
            if ($listing->post_status === 'pending') { $status_bg = '#fef9c3'; $status_color = '#854d0e'; }
            if ($is_suspended) {
                $status_bg = '#fee2e2'; $status_color = '#991b1b';
                $display_status = 'Suspended';
            }
            $status_badge = '<span style="background:'.$status_bg.'; color:'.$status_color.'; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">' . esc_html($display_status) . '</span>';

            $parent_id = $listing->post_parent;
            $type_badge = '';
            if ($parent_id == 0) {
                $type_badge = '<span class="badge" style="background:#10b981; color:#fff; font-size:0.65rem; margin-left:8px; vertical-align:middle;">PARENT</span>';
            } else {
                $parent_title = get_the_title($parent_id);
                $type_badge = '<span class="badge" style="background:#3b82f6; color:#fff; font-size:0.65rem; margin-left:8px; vertical-align:middle;">UNIT</span>';
                $type_badge .= '<div style="font-size:0.75rem; color:#888; margin-top:2px;">of ' . esc_html($parent_title) . '</div>';
            }

            echo '<tr>';
            echo '<td data-label="Title"><strong>' . esc_html($listing->post_title) . '</strong>' . $type_badge . '</td>';
            echo '<td data-label="Host">' . ($host ? esc_html($host->display_name) : 'Unknown') . '</td>';
            echo '<td data-label="Status">' . $status_badge . '</td>';
            echo '<td data-label="Price">$' . esc_html($price) . '</td>';
            echo '<td data-label="Actions">';
            echo '<a href="' . get_permalink($listing->ID) . '" target="_blank">View</a> | ';
            if (current_user_can('manage_options')) {
                $location = get_post_meta($listing->ID, '_obenlo_location', true);
                if(empty($location)) $location = 'Toronto';
                $image = get_the_post_thumbnail_url($listing->ID, 'large');
                $template = get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!");
                $caption = str_replace( array('{title}', '${price}', '{location}'), array($listing->post_title, $price, $location), $template );
                $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(get_permalink($listing->ID)) . '&quote=' . urlencode($caption);

                echo '<a href="' . esc_url($share_url) . '" target="_blank" class="obenlo-social-push-btn" 
                    data-post-id="' . $listing->ID . '" 
                    data-title="' . esc_attr($listing->post_title) . '" 
                    data-price="' . esc_attr($price) . '" 
                    data-location="' . esc_attr($location) . '" 
                    data-url="' . esc_url(get_permalink($listing->ID)) . '" 
                    data-type="listing" 
                    data-image="' . esc_url($image) . '" 
                    style="color:#e61e4d; font-weight:bold;">Push to Social</a> | ';
            }
            echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to trash this listing?\');">';
            echo '<input type="hidden" name="action" value="obenlo_trash_listing">';
            echo '<input type="hidden" name="listing_id" value="' . $listing->ID . '">';
            wp_nonce_field('trash_listing_' . $listing->ID, 'trash_nonce');
            echo '<button type="submit" class="btn-reject" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">Trash</button>';
            echo '</form> | ';
            echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline;" onsubmit="if(!\'' . $is_suspended . '\') { var r = prompt(\'Reason for suspension:\'); if(r===null) return false; this.reason.value=r; } return confirm(\'Are you sure?\');">';
            echo '<input type="hidden" name="action" value="obenlo_toggle_listing_suspension">';
            echo '<input type="hidden" name="listing_id" value="' . $listing->ID . '">';
            echo '<input type="hidden" name="reason" value="">';
            wp_nonce_field('suspend_listing_' . $listing->ID, 'suspend_nonce');
            echo '<button type="submit" class="' . $sus_class . '" style="background:none; border:none; cursor:pointer; padding:0; font:inherit; text-decoration:underline;">' . $sus_text . '</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function handle_toggle_listing_suspension() {
        if (!current_user_can('administrator')) return;
        $listing_id = intval($_POST['listing_id']);
        check_admin_referer('suspend_listing_' . $listing_id, 'suspend_nonce');

        $is_suspended = get_post_meta($listing_id, '_obenlo_is_suspended', true) === 'yes';
        if ($is_suspended) {
            delete_post_meta($listing_id, '_obenlo_is_suspended');
            delete_post_meta($listing_id, '_obenlo_suspension_reason');
        } else {
            update_post_meta($listing_id, '_obenlo_is_suspended', 'yes');
            update_post_meta($listing_id, '_obenlo_suspension_reason', sanitize_text_field($_POST['reason']));
        }
        wp_safe_redirect(add_query_arg('tab', 'listings', wp_get_referer()));
        exit;
    }

    public function handle_trash_listing()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        check_admin_referer('trash_listing_' . $listing_id, 'trash_nonce');

        if ($listing_id) {
            wp_trash_post($listing_id);
        }

        wp_safe_redirect(add_query_arg('tab', 'listings', wp_get_referer()));
        exit;
    }

}
