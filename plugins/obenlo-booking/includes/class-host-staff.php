<?php
/**
 * Host Staff Management
 * 
 * Allows hosts to add staff members, assign them to listings,
 * and track which staff member is assigned to which booking.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Staff {

    public function init() {
        add_action('admin_post_obenlo_add_staff', array($this, 'handle_add_staff'));
        add_action('admin_post_obenlo_delete_staff', array($this, 'handle_delete_staff'));
    }

    public function handle_add_staff() {
        if (!is_user_logged_in() || !isset($_POST['obenlo_staff_nonce']) || !wp_verify_nonce($_POST['obenlo_staff_nonce'], 'obenlo_staff_action')) {
            wp_die('Security check failed.');
        }

        $host_id = get_current_user_id();
        $staff_name = sanitize_text_field($_POST['staff_name']);
        $staff_role = sanitize_text_field($_POST['staff_role']);
        $staff_email = sanitize_email($_POST['staff_email']);

        if (empty($staff_name)) {
            wp_redirect(home_url('/host-dashboard?action=staff&obenlo_error=invalid_data'));
            exit;
        }

        $staff_members = get_user_meta($host_id, '_obenlo_staff_members', true);
        if (!is_array($staff_members)) {
            $staff_members = array();
        }

        $staff_members[] = array(
            'id'    => uniqid('staff_'),
            'name'  => $staff_name,
            'role'  => $staff_role,
            'email' => $staff_email,
            'date_added' => current_time('mysql')
        );

        update_user_meta($host_id, '_obenlo_staff_members', $staff_members);

        wp_redirect(home_url('/host-dashboard?action=staff&message=saved'));
        exit;
    }

    public function handle_delete_staff() {
        if (!is_user_logged_in() || !isset($_GET['staff_id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_staff_' . $_GET['staff_id'])) {
            wp_die('Security check failed.');
        }

        $host_id = get_current_user_id();
        $staff_id = sanitize_text_field($_GET['staff_id']);
        
        $staff_members = get_user_meta($host_id, '_obenlo_staff_members', true);
        if (is_array($staff_members)) {
            foreach ($staff_members as $key => $staff) {
                if ($staff['id'] === $staff_id) {
                    unset($staff_members[$key]);
                }
            }
            update_user_meta($host_id, '_obenlo_staff_members', array_values($staff_members));
        }

        wp_redirect(home_url('/host-dashboard?action=staff&message=saved'));
        exit;
    }

    public function render_staff_tab() {
        $host_id = get_current_user_id();
        $staff_members = get_user_meta($host_id, '_obenlo_staff_members', true);
        if (!is_array($staff_members)) {
            $staff_members = array();
        }
        ?>
        <div class="dashboard-header">
            <h2 class="dashboard-title"><?php echo __('Staff & Team Management', 'obenlo'); ?></h2>
            <p style="color:#666; margin-bottom:20px;">Manage your tour guides, instructors, or service staff. Assign them to specific bookings and manage their schedules.</p>
        </div>

        <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.04); margin-bottom: 30px;">
            <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Add New Staff Member</h3>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="action" value="obenlo_add_staff">
                <?php wp_nonce_field('obenlo_staff_action', 'obenlo_staff_nonce'); ?>
                
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:5px;">Name</label>
                    <input type="text" name="staff_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:5px;">Role (e.g. Tour Guide)</label>
                    <input type="text" name="staff_role" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:5px;">Email</label>
                    <input type="email" name="staff_email" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                </div>
                <div>
                    <button type="submit" style="background:#e61e4d; color:#fff; border:none; padding:11px 20px; border-radius:6px; font-weight:bold; cursor:pointer;">Add Staff</button>
                </div>
            </form>
        </div>

        <?php if (!empty($staff_members)): ?>
            <div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04); overflow:hidden;">
                <table class="admin-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_members as $staff): 
                            $delete_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_delete_staff&staff_id=' . esc_attr($staff['id'])), 'delete_staff_' . $staff['id']);
                        ?>
                            <tr>
                                <td><strong style="color:#111;"><?php echo esc_html($staff['name']); ?></strong></td>
                                <td><?php echo esc_html($staff['role']); ?></td>
                                <td><?php echo esc_html($staff['email']); ?></td>
                                <td style="text-align:right;">
                                    <a href="<?php echo esc_url($delete_url); ?>" style="color:#ef4444; text-decoration:none; font-size:13px; font-weight:bold;" onclick="return confirm('Remove this staff member?');">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:50px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <p style="color:#666;">No staff members added yet.</p>
            </div>
        <?php endif; ?>
        <?php
    }
}
