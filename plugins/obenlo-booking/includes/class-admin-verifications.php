<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Verifications
{
    public function init()
    {
        add_action('admin_post_obenlo_update_host_status', array($this, 'handle_update_host_status'));
        add_action('admin_post_obenlo_admin_review_action', array($this, 'handle_review_action'));
        add_action('admin_post_obenlo_admin_testimony_action', array($this, 'handle_testimony_action'));
    }

    public function render_verifications_tab()
    {
        echo '<h3>Host Verification Requests</h3>';
        
        $users = get_users(array(
            'meta_key' => 'obenlo_host_verification_status',
            'meta_value' => array('pending', 'verified', 'rejected'),
            'meta_compare' => 'IN'
        ));
        
        echo '<table class="admin-table">';
        echo '<tr><th>Host</th><th>Status</th><th>Document</th><th>Actions</th></tr>';
        
        if (empty($users)) {
            echo '<tr><td colspan="4" style="text-align:center; padding:30px;">No verification requests found.</td></tr>';
        } else {
            foreach ($users as $user) {
                $status = get_user_meta($user->ID, 'obenlo_host_verification_status', true);
                if (!$status) $status = 'pending';

                $doc_id = get_user_meta($user->ID, 'obenlo_verification_doc_id', true);
                $doc_url = $doc_id ? wp_get_attachment_url($doc_id) : '';
                
                $status_bg = '#fef9c3'; $status_color = '#854d0e';
                if ($status === 'verified') { $status_bg = '#dcfce7'; $status_color = '#166534'; }
                if ($status === 'rejected') { $status_bg = '#fee2e2'; $status_color = '#991b1b'; }

                echo '<tr>';
                echo '<td data-label="Host"><strong>' . esc_html($user->display_name) . '</strong><br><small><a href="mailto:'.esc_attr($user->user_email).'" style="color:#666;">' . esc_html($user->user_email) . '</a></small></td>';
                echo '<td data-label="Status"><span style="background:'.$status_bg.'; color:'.$status_color.'; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">' . esc_html($status) . '</span></td>';
                
                echo '<td data-label="Document">';
                if ($doc_url) {
                    echo '<a href="' . esc_url($doc_url) . '" target="_blank" style="color:#1d4ed8; font-weight:600; text-decoration:none;">📄 View Document</a>';
                } else {
                    echo '<span style="color:#999; font-style:italic;">No active document</span>';
                }
                echo '</td>';
                
                echo '<td data-label="Actions">';
                // Only show approve/reject for pending, but allow overriding if needed
                if ($status === 'pending' || $status === 'rejected') {
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline-block; margin-right:10px;">';
                    echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
                    echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
                    echo '<input type="hidden" name="status" value="verified">';
                    echo '<button type="submit" style="background:#10b981; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:0.8rem;">Approve</button>';
                    echo '</form>';
                }
                if ($status === 'pending' || $status === 'verified') {
                    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="display:inline-block;">';
                    echo '<input type="hidden" name="action" value="obenlo_update_host_status">';
                    echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
                    echo '<input type="hidden" name="status" value="rejected">';
                    echo '<button type="submit" style="background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:0.8rem;">Reject</button>';
                    echo '</form>';
                }
                echo '</td>';
                echo '</tr>';
                
                echo '</tr>';
            }
        }
        
        echo '</table>';
    }

    public function render_reviews_tab()
    {
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        
        $args = array(
            'post_type' => 'listing',
            'status'    => $status_filter === 'all' ? '' : $status_filter,
            'parent'    => 0, // Top-level reviews only
        );

        $comments = get_comments($args);

        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
        echo '<h3>Manage Platform Reviews</h3>';
        echo '<div>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=all" class="badge ' . ($status_filter === 'all' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'all' ? '#fff' : '#666') . ';">All</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=hold" class="badge ' . ($status_filter === 'hold' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'hold' ? '#fff' : '#666') . ';">Pending</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=approve" class="badge ' . ($status_filter === 'approve' ? 'badge-host' : '') . '" style="text-decoration:none; margin-right:5px; border:1px solid #eee; color:' . ($status_filter === 'approve' ? '#fff' : '#666') . ';">Approved</a>';
        echo '<a href="?page=obenlo-admin-dashboard&tab=reviews&status=trash" class="badge ' . ($status_filter === 'trash' ? 'badge-host' : '') . '" style="text-decoration:none; border:1px solid #eee; color:' . ($status_filter === 'trash' ? '#fff' : '#666') . ';">Trash</a>';
        echo '</div>';
        echo '</div>';

        if (empty($comments)) {
            echo '<p style="padding:40px; text-align:center; background:#fff; border-radius:12px; border:1px solid #eee; color:#999;">No reviews found.</p>';
            return;
        }

        echo '<table class="admin-table">';
        echo '<tr><th>Author</th><th>Rating</th><th>Listing</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr>';
        
        foreach ($comments as $comment) {
            $rating = get_comment_meta($comment->comment_ID, '_obenlo_rating', true);
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            $listing = get_post($comment->comment_post_ID);
            
            $display_status = 'Approved';
            $status_bg = '#dcfce7'; $status_color = '#166534';
            if ($comment->comment_approved === '0') { $display_status = 'Pending'; $status_bg = '#fef9c3'; $status_color = '#854d0e'; }
            if ($comment->comment_approved === 'trash') { $display_status = 'Trash'; $status_bg = '#fee2e2'; $status_color = '#991b1b'; }

            echo '<tr>';
            echo '<td data-label="Author"><strong>' . esc_html($comment->comment_author) . '</strong><br><small>' . esc_html($comment->comment_author_email) . '</small></td>';
            echo '<td data-label="Rating"><span style="color:#FFD700; font-weight:bold; font-size:1.1em;">' . $stars . '</span><br><small>(' . $rating . '/5)</small></td>';
            echo '<td data-label="Listing"><a href="' . get_permalink($listing->ID) . '" target="_blank" style="color:#222; font-weight:600; text-decoration:none;">' . esc_html($listing->post_title) . '</a></td>';
            echo '<td data-label="Review" style="max-width:300px;"><div style="font-size:0.9em; line-height:1.4; color:#444;">' . esc_html($comment->comment_content) . '</div></td>';
            echo '<td data-label="Status"><span style="background:'.$status_bg.'; color:'.$status_color.'; padding:6px 12px; border-radius:12px; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">' . $display_status . '</span></td>';
            echo '<td data-label="Date">' . date('M j, Y', strtotime($comment->comment_date)) . '</td>';
            echo '<td data-label="Actions">';
            echo '<div style="display:flex; gap:10px; align-items:center;">';
            
            $base_action_url = admin_url('admin-post.php?action=obenlo_admin_review_action&comment_id=' . $comment->comment_ID);
            $nonce = wp_create_nonce('obenlo_admin_review_' . $comment->comment_ID);
            
            if ($comment->comment_approved === '0') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=approve', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#10b981; font-weight:700; text-decoration:none; font-size:0.85rem;">Approve</a>';
            } else if ($comment->comment_approved === '1') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=unapprove', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#f97316; font-weight:700; text-decoration:none; font-size:0.85rem;">Unapprove</a>';
            }

            if ($comment->comment_approved !== 'trash') {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=trash', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Trash</a>';
            } else {
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=approve', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#10b981; font-weight:700; text-decoration:none; font-size:0.85rem;">Restore</a>';
                echo '<a href="' . wp_nonce_url($base_action_url . '&do=delete', 'obenlo_admin_review_' . $comment->comment_ID, 'nonce') . '" style="color:#000; font-weight:700; text-decoration:none; font-size:0.85rem;" onclick="return confirm(\'Permanently delete this review?\')">Delete</a>';
            }
            
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function handle_review_action()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : 0;
        $action = isset($_GET['do']) ? sanitize_text_field($_GET['do']) : '';
        
        check_admin_referer('obenlo_admin_review_' . $comment_id, 'nonce');

        if ($comment_id && $action) {
            switch ($action) {
                case 'approve':
                    wp_set_comment_status($comment_id, 'approve');
                    break;
                case 'unapprove':
                    wp_set_comment_status($comment_id, 'hold');
                    break;
                case 'trash':
                    wp_set_comment_status($comment_id, 'trash');
                    break;
                case 'delete':
                    wp_delete_comment($comment_id, true);
                    break;
            }
        }

        wp_safe_redirect(add_query_arg(['tab' => 'reviews', 'status' => isset($_GET['status']) ? $_GET['status'] : 'all'], wp_get_referer()));
        exit;
    }

    public function render_testimonies_tab()
    {
        $testimonies = get_posts(array(
            'post_type' => 'testimony',
            'post_status' => array('pending', 'publish', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        ?>
        <h2>User Testimonies (Obenlo Love)</h2>
        <p>Moderation queue for platform-wide testimonials. Approved testimonies appear on the homepage.</p>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Testimony</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonies)): ?>
                    <tr><td colspan="6" style="text-align:center;">No testimonies found.</td></tr>
                <?php else: ?>
                    <?php foreach ($testimonies as $t): 
                        $user = get_userdata($t->post_author);
                        $rating = get_post_meta($t->ID, '_obenlo_testimony_rating', true);
                        $status = $t->post_status;
                    ?>
                        <tr>
                            <td data-label="Date"><?php echo get_the_date('', $t->ID); ?></td>
                            <td data-label="User">
                                <strong><?php echo $user ? esc_html($user->display_name) : 'Unknown'; ?></strong><br>
                                <small><?php echo $user ? esc_html($user->user_email) : ''; ?></small>
                            </td>
                            <td data-label="Testimony" style="max-width: 400px;">
                                <strong><?php echo esc_html($t->post_title); ?></strong><br>
                                <span style="font-size:0.9em; color:#666;"><?php echo esc_html($t->post_content); ?></span>
                            </td>
                            <td data-label="Rating">
                                <div style="color:#f59e0b;">
                                    <?php for($i=1; $i<=5; $i++) echo ($i <= $rating ? '★' : '☆'); ?>
                                </div>
                            </td>
                            <td data-label="Status">
                                <span class="badge <?php echo $status === 'publish' ? 'badge-host' : 'badge-guest'; ?>">
                                    <?php echo esc_html(strtoupper($status)); ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="obenlo_admin_testimony_action">
                                    <input type="hidden" name="testimony_id" value="<?php echo $t->ID; ?>">
                                    <?php wp_nonce_field('testimony_action_' . $t->ID); ?>
                                    
                                    <?php if ($status !== 'publish'): ?>
                                        <button type="submit" name="testimony_status" value="publish" class="btn-approve" style="background:none; border:none; cursor:pointer;">Approve</button>
                                    <?php endif; ?>
                                    
                                    <?php if ($status !== 'trash'): ?>
                                        <button type="submit" name="testimony_status" value="trash" class="btn-reject" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Trash this testimony?')">Trash</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    public function handle_testimony_action()
    {
        if (!current_user_can('administrator')) {
            wp_die('Unauthorized');
        }

        $testimony_id = isset($_POST['testimony_id']) ? intval($_POST['testimony_id']) : 0;
        $new_status   = isset($_POST['testimony_status']) ? sanitize_text_field($_POST['testimony_status']) : '';

        check_admin_referer('testimony_action_' . $testimony_id);

        if ($testimony_id && in_array($new_status, array('publish', 'trash', 'pending'))) {
            wp_update_post(array(
                'ID' => $testimony_id,
                'post_status' => $new_status
            ));
        }

        wp_safe_redirect(add_query_arg('tab', 'testimonies', wp_get_referer()));
        exit;
    }

    public function handle_update_host_status()
    {
        if (!current_user_can('administrator')) wp_die('Unauthorized');
        $user_id = intval($_POST['user_id']);
        $status = sanitize_text_field($_POST['status']);
        
        update_user_meta($user_id, 'obenlo_host_verification_status', $status);
        
        wp_safe_redirect(add_query_arg('tab', 'verifications', wp_get_referer()));
        exit;
    }
}
