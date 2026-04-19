<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Users
{
    public function init()
    {
        add_action('admin_post_obenlo_update_user_fee', array($this, 'handle_update_user_fee'));
        add_action('admin_post_obenlo_toggle_user_suspension', array($this, 'handle_toggle_user_suspension'));
        add_action('admin_post_obenlo_delete_user_admin', array($this, 'handle_delete_user_admin'));
    }

    public function render_users_tab()
    {
        $search = isset($_GET['user_search']) ? sanitize_text_field($_GET['user_search']) : '';
        $role_filter = isset($_GET['user_role']) ? sanitize_text_field($_GET['user_role']) : '';

        $query_args = array(
            'role__in' => $role_filter ? array($role_filter) : array('host', 'guest'),
            'orderby' => 'registered',
            'order' => 'DESC'
        );

        if ($search) {
            $query_args['search'] = '*' . $search . '*';
            $query_args['search_columns'] = array('user_login', 'user_nicename', 'user_email', 'display_name');
        }

        $users = get_users($query_args);

        echo '<h3>Site User Management</h3>';
?>
        <!-- User Filters -->
        <div style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px;">
            <form action="" method="GET" style="display:flex; gap:20px; align-items:flex-end;">
                <input type="hidden" name="page" value="obenlo-booking">
                <input type="hidden" name="tab" value="users">
                
                <div style="flex:1;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Search Users</label>
                    <input type="text" name="user_search" value="<?php echo esc_attr($search); ?>" placeholder="Name, email, or username..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#888; margin-bottom:5px; text-transform:uppercase;">Role</label>
                    <select name="user_role" style="padding:10px; border:1px solid #ddd; border-radius:8px; min-width:150px;">
                        <option value="">All Roles</option>
                        <option value="host" <?php selected($role_filter, 'host'); ?>>Hosts</option>
                        <option value="guest" <?php selected($role_filter, 'guest'); ?>>Guests</option>
                    </select>
                </div>

                <button type="submit" style="background:#222; color:#fff; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">Search</button>
                <a href="?page=obenlo-booking&tab=users" style="padding:10px; color:#666; font-size:0.9rem;">Reset</a>
            </form>
        </div>

        <table class="admin-table">
?>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Verification</th>
                <th>Payout Settings</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user):
            $status = Obenlo_Booking_Host_Verification::get_status($user->ID);
            $v_badge = 'badge-info';
            if ($status === 'verified')
                $v_badge = 'badge-success';
            if ($status === 'rejected')
                $v_badge = 'badge-danger';
            if ($status === 'pending')
                $v_badge = 'badge-warning';
?>
                <tr>
                    <td data-label="User">
                        <div style="font-weight:700;"><?php echo esc_html($user->display_name); ?></div>
                        <div style="font-size:0.8rem; color:#888;"><?php echo esc_html($user->user_email); ?></div>
                    </td>
                    <td data-label="Role"><span class="badge badge-info"><?php echo ucfirst($user->roles[0]); ?></span></td>
                    <?php 
                    $is_suspended = get_user_meta($user->ID, '_obenlo_is_suspended', true) === 'yes';
                    if ($is_suspended): ?>
                        <td data-label="Status"><span style="background:#fee2e2; color:#991b1b; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Suspended</span></td>
                    <?php else: ?>
                        <td data-label="Status"><span style="background:#dcfce7; color:#166534; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Active</span></td>
                    <?php endif; ?>
                    <?php
                    $v_bg = '#f3f4f6'; $v_color = '#374151';
                    if ($status === 'verified') { $v_bg = '#dcfce7'; $v_color = '#166534'; }
                    if ($status === 'rejected') { $v_bg = '#fee2e2'; $v_color = '#991b1b'; }
                    if ($status === 'pending') { $v_bg = '#fef9c3'; $v_color = '#854d0e'; }
                    ?>
                    <td data-label="Verification"><span style="background:<?php echo $v_bg; ?>; color:<?php echo $v_color; ?>; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;"><?php echo esc_html($status); ?></span></td>
                    <td data-label="Payout Settings">
                        <?php 
                        $p_method = get_user_meta($user->ID, 'obenlo_payout_method', true);
                        $p_details = get_user_meta($user->ID, 'obenlo_payout_details', true);
                        if ($p_method): ?>
                            <div style="font-size:0.85rem;">
                                <span class="badge badge-guest" style="text-transform:uppercase;"><?php echo esc_html($p_method); ?></span>
                                <div style="margin-top:4px; font-family:monospace; color:#666;"><?php echo esc_html($p_details); ?></div>
                            </div>
                        <?php else: ?>
                            <span style="color:#ccc; font-style:italic; font-size:0.8rem;">Not set</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Joined"><?php echo date('M d, Y', strtotime($user->user_registered)); ?></td>
                    <td data-label="Actions">
                        <?php if (in_array('host', $user->roles)): 
                            $sus_text = $is_suspended ? 'Restore Host' : 'Suspend Host';
                        ?>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:flex; gap:5px; margin-bottom:5px;">
                                <input type="hidden" name="action" value="obenlo_update_user_fee">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <?php wp_nonce_field('update_user_fee_' . $user->ID, 'fee_nonce'); ?>
                                <input type="number" name="fee_percentage" value="<?php echo esc_attr(get_user_meta($user->ID, '_obenlo_host_fee_percentage', true)); ?>" placeholder="Global" step="0.1" style="width:70px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                                <button type="submit" style="padding:5px 10px; background:#222; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:0.8em;">Save Fee</button>
                            </form>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline;" onsubmit="if(!'<?php echo $is_suspended ? '1' : ''; ?>') { var r = prompt('Reason for suspension:'); if(r===null) return false; this.reason.value=r; } return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="obenlo_toggle_user_suspension">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <input type="hidden" name="reason" value="">
                                <?php wp_nonce_field('suspend_user_' . $user->ID, 'suspend_nonce'); ?>
                                <button type="submit" style="background:none; border:none; color:#e61e4d; cursor:pointer; text-decoration:underline; padding:0;"><?php echo $sus_text; ?></button>
                            </form>
                        <?php
            else: ?>
                            <span style="color:#ccc;">N/A</span>
                        <?php
            endif; ?>
                        <a href="mailto:<?php echo esc_attr($user->user_email); ?>" style="margin-left: 10px;">Contact</a>
                        |
                        <?php if (in_array('host', $user->roles)): ?>
                            <a href="?page=obenlo-admin-dashboard&tab=edit_host&user_id=<?php echo $user->ID; ?>" style="font-weight:bold; color:#222;">Edit Store</a> |
                            <a href="?page=obenlo-admin-dashboard&tab=manage_availability&user_id=<?php echo $user->ID; ?>" style="font-weight:bold; color:#2563eb;">Availability</a> |
                        <?php endif; ?>
                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this user and ALL their content? This cannot be undone.');">
                            <input type="hidden" name="action" value="obenlo_delete_user_admin">
                            <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                            <?php wp_nonce_field('delete_user_' . $user->ID, 'delete_nonce'); ?>
                            <button type="submit" style="background:none; border:none; color:#e61e4d; cursor:pointer; text-decoration:underline; padding:0; font-size:1rem;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php
        endforeach; ?>
        <?php
        echo '</table>';
    }

    public function handle_update_user_fee()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer('update_user_fee_' . $user_id, 'fee_nonce');

        if (isset($_POST['fee_percentage'])) {
            $fee = sanitize_text_field($_POST['fee_percentage']);
            if ($fee === '') {
                delete_user_meta($user_id, '_obenlo_host_fee_percentage');
            }
            else {
                update_user_meta($user_id, '_obenlo_host_fee_percentage', $fee);
            }
        }

        wp_safe_redirect(add_query_arg('tab', 'users', wp_get_referer()));
        exit;
    }

    public function handle_delete_user_admin()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer('delete_user_' . $user_id, 'delete_nonce');

        // Prevent self-deletion
        if ($user_id && get_current_user_id() !== $user_id) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            // Deleting user and their contents
            wp_delete_user($user_id);
        }

        wp_safe_redirect(add_query_arg('tab', 'users', wp_get_referer()));
        exit;
    }

    public function handle_toggle_user_suspension() {
        if (!current_user_can('administrator')) return;
        $user_id = intval($_POST['user_id']);
        check_admin_referer('suspend_user_' . $user_id, 'suspend_nonce');

        $is_suspended = get_user_meta($user_id, '_obenlo_is_suspended', true) === 'yes';
        if ($is_suspended) {
            delete_user_meta($user_id, '_obenlo_is_suspended');
            delete_user_meta($user_id, '_obenlo_suspension_reason');
        } else {
            update_user_meta($user_id, '_obenlo_is_suspended', 'yes');
            update_user_meta($user_id, '_obenlo_suspension_reason', sanitize_text_field($_POST['reason']));
        }
        wp_safe_redirect(add_query_arg('tab', 'users', wp_get_referer()));
        exit;
    }

    public function render_edit_host_tab()
    {
        if (!current_user_can('administrator')) return;

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $user = get_userdata($user_id);

        if (!$user) {
            echo '<p>User not found.</p>';
            return;
        }

        $store_name = get_user_meta($user_id, 'obenlo_store_name', true) ?: $user->display_name;
        $store_desc = get_user_meta($user_id, 'obenlo_store_description', true);
        $store_tagline = get_user_meta($user_id, 'obenlo_store_tagline', true);
        $store_location = get_user_meta($user_id, 'obenlo_store_location', true);
        $store_logo = get_user_meta($user_id, 'obenlo_store_logo', true);
        $store_banner = get_user_meta($user_id, 'obenlo_store_banner', true);

        echo '<h3>Edit Host Storefront: ' . esc_html($store_name) . '</h3>';
?>
        <div style="background:#fff; padding:40px; border-radius:24px; border:1px solid #eee; max-width:800px;">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="obenlo_admin_save_host_profile">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php wp_nonce_field('admin_save_host_profile_' . $user_id, 'profile_nonce'); ?>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px;">Store / Business Name</label>
                    <input type="text" name="store_name" value="<?php echo esc_attr($store_name); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px;">Store Tagline</label>
                    <input type="text" name="store_tagline" value="<?php echo esc_attr($store_tagline); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px;">Store Description</label>
                    <textarea name="store_description" rows="5" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($store_desc); ?></textarea>
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px;">Location Info</label>
                    <input type="text" name="store_location" value="<?php echo esc_attr($store_location); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-bottom:40px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:8px;">Store Logo</label>
                        <?php if($store_logo): ?>
                            <img src="<?php echo wp_get_attachment_image_url($store_logo, 'thumbnail'); ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover; margin-bottom:10px; display:block;">
                        <?php endif; ?>
                        <input type="file" name="store_logo" accept="image/*">
                    </div>
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:8px;">Store Banner</label>
                        <?php if($store_banner): ?>
                            <img src="<?php echo wp_get_attachment_image_url($store_banner, 'medium'); ?>" style="width:100%; height:100px; border-radius:10px; object-fit:cover; margin-bottom:10px; display:block;">
                        <?php endif; ?>
                        <input type="file" name="store_banner" accept="image/*">
                    </div>
                </div>

                <button type="submit" class="btn-primary">Update Host Profile</button>
            </form>
        </div>
<?php
    }

    public function handle_admin_save_host_profile()
    {
        if (!current_user_can('administrator')) wp_die('No permission');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer('admin_save_host_profile_' . $user_id, 'profile_nonce');

        // Text Meta
        update_user_meta($user_id, 'obenlo_store_name', sanitize_text_field($_POST['store_name']));
        update_user_meta($user_id, 'obenlo_store_tagline', sanitize_text_field($_POST['store_tagline']));
        update_user_meta($user_id, 'obenlo_store_description', sanitize_textarea_field($_POST['store_description']));
        update_user_meta($user_id, 'obenlo_store_location', sanitize_text_field($_POST['store_location']));

        // Files
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        if (isset($_FILES['store_logo']) && !empty($_FILES['store_logo']['name'])) {
            $attachment_id = media_handle_upload('store_logo', 0);
            if (!is_wp_error($attachment_id)) {
                update_user_meta($user_id, 'obenlo_store_logo', $attachment_id);
            }
        }
        if (isset($_FILES['store_banner']) && !empty($_FILES['store_banner']['name'])) {
            $attachment_id = media_handle_upload('store_banner', 0);
            if (!is_wp_error($attachment_id)) {
                update_user_meta($user_id, 'obenlo_store_banner', $attachment_id);
            }
        }

        wp_redirect(add_query_arg(array('page' => 'obenlo-admin-dashboard', 'tab' => 'users', 'msg' => '1'), admin_url('admin.php')));
        exit;
    }

    public function render_manage_availability_tab()
    {
        if (!current_user_can('administrator')) return;

        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $user = get_userdata($user_id);

        if (!$user) {
            echo '<p>User not found.</p>';
            return;
        }

        $business_hours = get_user_meta($user_id, '_obenlo_business_hours', true);
        if (!is_array($business_hours)) {
            $business_hours = array();
        }
        $vacation_blocks = get_user_meta($user_id, '_obenlo_vacation_blocks', true);
        if (!is_array($vacation_blocks)) {
            $vacation_blocks = array();
        }

        echo '<h3>Supervise Availability: ' . esc_html($user->display_name) . '</h3>';
?>
        <div style="background:#fff; padding:40px; border-radius:24px; border:1px solid #eee; max-width:800px;">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_admin_save_host_availability">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php wp_nonce_field('admin_save_host_availability_' . $user_id, 'availability_nonce'); ?>

                <h4 style="margin-top:0; margin-bottom:20px;">Standard Weekly Hours</h4>
                <div style="display:flex; flex-direction:column; gap:15px; margin-bottom:40px;">
                <?php
        $days = array('monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday');
        foreach ($days as $key => $label):
            $active = isset($business_hours[$key]['active']) && $business_hours[$key]['active'] === 'yes';
            $start = isset($business_hours[$key]['start']) ? $business_hours[$key]['start'] : '09:00';
            $end = isset($business_hours[$key]['end']) ? $business_hours[$key]['end'] : '17:00';
?>
                    <div style="display:flex; align-items:center; gap:20px; padding:10px; border-bottom:1px solid #f9f9f9;">
                        <label style="width:100px; font-weight:700;">
                            <input type="checkbox" name="hours[<?php echo $key; ?>][active]" value="yes" <?php checked($active); ?>> <?php echo $label; ?>
                        </label>
                        <input type="time" name="hours[<?php echo $key; ?>][start]" value="<?php echo esc_attr($start); ?>">
                        <span>to</span>
                        <input type="time" name="hours[<?php echo $key; ?>][end]" value="<?php echo esc_attr($end); ?>">
                    </div>
                <?php endforeach; ?>
                </div>

                <h4 style="margin-bottom:20px;">Vacation / Blacked Out Dates</h4>
                <div id="admin-vacation-blocks" style="display:flex; flex-direction:column; gap:15px; margin-bottom:20px;">
                    <?php if(!empty($vacation_blocks)): foreach($vacation_blocks as $idx => $block): ?>
                        <div class="vac-row" style="display:flex; gap:10px; align-items:flex-end;">
                            <div style="flex:1;"><label style="display:block; font-size:0.7rem;">Start</label><input type="date" name="vacation[<?php echo $idx; ?>][start]" value="<?php echo esc_attr($block['start']); ?>" style="width:100%;"></div>
                            <div style="flex:1;"><label style="display:block; font-size:0.7rem;">End</label><input type="date" name="vacation[<?php echo $idx; ?>][end]" value="<?php echo esc_attr($block['end']); ?>" style="width:100%;"></div>
                            <div style="flex:2;"><label style="display:block; font-size:0.7rem;">Note</label><input type="text" name="vacation[<?php echo $idx; ?>][reason]" value="<?php echo esc_attr($block['reason']); ?>" style="width:100%;"></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <p style="font-size:0.8rem; color:#888;">Note: This admin view only allows editing existing blocks or wiping them. Re-add from Host Dashboard if new ones needed.</p>

                <button type="submit" class="btn-primary" style="margin-top:20px;">Save Availability</button>
            </form>
        </div>
<?php
    }

    public function handle_admin_save_host_availability()
    {
        if (!current_user_can('administrator')) wp_die('No permission');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        check_admin_referer('admin_save_host_availability_' . $user_id, 'availability_nonce');

        // Business Hours
        $hours = isset($_POST['hours']) ? (array)$_POST['hours'] : array();
        $sanitized_hours = array();
        foreach ($hours as $day => $data) {
            $sanitized_hours[sanitize_key($day)] = array(
                'active' => isset($data['active']) && $data['active'] === 'yes' ? 'yes' : 'no',
                'start' => sanitize_text_field($data['start']),
                'end' => sanitize_text_field($data['end'])
            );
        }
        update_user_meta($user_id, '_obenlo_business_hours', $sanitized_hours);

        // Vacation Blocks
        $vacations = isset($_POST['vacation']) ? (array)$_POST['vacation'] : array();
        $sanitized_vacations = array();
        foreach ($vacations as $v) {
            if (!empty($v['start']) && !empty($v['end'])) {
                $sanitized_vacations[] = array(
                    'start' => sanitize_text_field($v['start']),
                    'end' => sanitize_text_field($v['end']),
                    'reason' => sanitize_text_field($v['reason'])
                );
            }
        }
        update_user_meta($user_id, '_obenlo_vacation_blocks', $sanitized_vacations);

        wp_redirect(add_query_arg(array('page' => 'obenlo-admin-dashboard', 'tab' => 'users', 'msg' => '1'), admin_url('admin.php')));
        exit;
    }

}
