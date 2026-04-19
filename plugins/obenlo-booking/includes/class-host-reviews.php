<?php
/**
 * Host Reviews Module
 * Single Responsibility: Reviews list UI + reply + approve handlers.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Reviews
{
    public function init()
    {
        add_action('admin_post_obenlo_reply_review', array($this, 'handle_reply_review'));
        add_action('admin_post_obenlo_approve_review', array($this, 'handle_approve_review'));
    }

    private function redirect_with_error($error_code) {
        obenlo_redirect_with_error($error_code);
    }

    public function render_reviews_list()
    {
        global $wpdb;
        $user_id = get_current_user_id();

        echo '<div class="dashboard-header"><h2 class="dashboard-title">' . __('My Reviews', 'obenlo') . '</h2></div>';

        $listing_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_author = %d AND post_type = 'listing' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')",
            $user_id
        ));

        if (empty($listing_ids)) {
            echo '<p>' . __('You have no listings yet, so no reviews can be shown.', 'obenlo') . '</p>';
            return;
        }

        $comments = get_comments(array(
            'post__in'       => $listing_ids,
            'status'         => 'all',
            'author__not_in' => array($user_id),
            'parent'         => 0,
        ));

        if (empty($comments)) {
            echo '<p>' . __('You have not received any reviews yet.', 'obenlo') . '</p>';
            return;
        }

        echo '<div class="reviews-list" style="display:flex; flex-direction:column; gap:25px;">';
        foreach ($comments as $comment) {
            $rating        = get_comment_meta($comment->comment_ID, '_obenlo_rating', true);
            $listing_title = get_the_title($comment->comment_post_ID);
            $listing_url   = get_permalink($comment->comment_post_ID);
            $replies       = get_comments(array('parent' => $comment->comment_ID, 'status' => 'approve', 'order' => 'ASC'));

            echo '<div class="review-item" style="border:1px solid #eee; padding:25px; border-radius:15px; background:#fff; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">';
            echo '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">';
            echo '<div>';
            echo '<div style="font-weight:800; font-size:1.1rem; color:#111;">' . esc_html($comment->comment_author) . '</div>';
            echo '<div style="font-size:0.85rem; color:#666; margin-top:2px;">' . sprintf(__('Reviewed %s', 'obenlo'), '<a href="' . esc_url($listing_url) . '" style="color:#e61e4d; font-weight:600; text-decoration:none;">' . esc_html($listing_title) . '</a>') . ' • ' . get_comment_date('', $comment->comment_ID) . '</div>';
            echo '</div>';

            if ($rating) {
                echo '<div style="background:#fff7ed; padding:5px 12px; border-radius:8px; border:1px solid #ffedd5; display:flex; align-items:center; gap:5px;">';
                echo '<span style="color:#f59e0b; font-size:1.1rem;">★</span>';
                echo '<span style="color:#9a3412; font-weight:800; font-size:0.95rem;">' . intval($rating) . '</span>';
                echo '</div>';
            }

            if ($comment->comment_approved == '0') {
                echo '<div style="background:#fef2f2; padding:5px 12px; border-radius:8px; border:1px solid #fee2e2; color:#991b1b; font-weight:800; font-size:0.8rem; text-transform:uppercase; margin-left:10px;">' . __('Pending Approval', 'obenlo') . '</div>';
            }
            echo '</div>';

            echo '<div style="line-height:1.7; color:#4b5563; font-size:1rem; margin-bottom:20px; font-style: italic;">"' . esc_html($comment->comment_content) . '"</div>';

            if (!empty($replies)) {
                echo '<div class="review-replies" style="margin-top:20px; padding-left:20px; border-left:3px solid #f3f4f6;">';
                foreach ($replies as $reply) {
                    echo '<div style="background:#f9fafb; padding:15px; border-radius:12px; margin-bottom:10px;">';
                    echo '<div style="font-weight:700; font-size:0.85rem; color:#374151; margin-bottom:5px; display:flex; align-items:center; gap:8px;">';
                    echo '<span style="background:#374151; color:#fff; font-size:0.65rem; padding:2px 6px; border-radius:4px; text-transform:uppercase;">' . __('Host Reply', 'obenlo') . '</span>';
                    echo '<span>' . get_comment_date('', $reply->comment_ID) . '</span>';
                    echo '</div>';
                    echo '<div style="font-size:0.95rem; color:#4b5563; line-height:1.5;">' . esc_html($reply->comment_content) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }

            $has_replied = false;
            foreach ($replies as $r) { if ($r->user_id == $user_id) $has_replied = true; }

            if ($comment->comment_approved == '0') {
                $approve_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_approve_review&comment_id=' . $comment->comment_ID), 'obenlo_approve_review_' . $comment->comment_ID);
                echo '<a href="' . esc_url($approve_url) . '" class="btn-primary" style="background:#059669; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.9rem; margin-top:10px;">';
                echo '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"></path></svg>';
                echo __('Approve Review', 'obenlo') . '</a>';
            }

            if (!$has_replied && $comment->comment_approved == '1') {
                $reply_form_id = 'reply-form-' . $comment->comment_ID;
                echo '<div style="margin-top:20px;">';
                echo '<button onclick="document.getElementById(\'' . $reply_form_id . '\').style.display=\'block\'; this.style.display=\'none\';" style="background:none; border:none; color:#e61e4d; font-weight:700; cursor:pointer; padding:0; font-size:0.9rem; display:flex; align-items:center; gap:5px;">';
                echo '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 17 4 12 9 7"></polyline><path d="M20 18v-2a4 4 0 0 0-4-4H4"></path></svg>';
                echo __('Write a Reply', 'obenlo') . '</button>';

                echo '<div id="' . $reply_form_id . '" style="display:none; margin-top:15px; background:#fdf2f8; padding:20px; border-radius:15px; border:1px solid #fbcfe8;">';
                echo '<form action="' . admin_url('admin-post.php') . '" method="POST">';
                echo '<input type="hidden" name="action" value="obenlo_reply_review">';
                echo '<input type="hidden" name="comment_id" value="' . $comment->comment_ID . '">';
                echo '<input type="hidden" name="listing_id" value="' . $comment->comment_post_ID . '">';
                wp_nonce_field('obenlo_reply_review_' . $comment->comment_ID);
                echo '<div style="margin-bottom:15px;">';
                echo '<label style="display:block; font-weight:700; font-size:0.85rem; color:#9d174d; margin-bottom:8px; text-transform:uppercase;">' . __('Your Public Response', 'obenlo') . '</label>';
                echo '<textarea name="reply_content" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #f9a8d4; min-height:100px; font-family:inherit; font-size:0.95rem;" placeholder="' . __('Thank your guest or address their feedback...', 'obenlo') . '"></textarea>';
                echo '</div>';
                echo '<div style="display:flex; gap:10px;">';
                echo '<button type="submit" class="btn-primary" style="background:#e61e4d; border:none; color:#fff; padding:10px 25px; border-radius:10px; font-weight:700; cursor:pointer;">' . __('Post Reply', 'obenlo') . '</button>';
                echo '<button type="button" onclick="document.getElementById(\'' . $reply_form_id . '\').style.display=\'none\';" style="background:none; border:none; color:#666; font-weight:600; cursor:pointer;">' . __('Cancel', 'obenlo') . '</button>';
                echo '</div>';
                echo '</form></div></div>';
            }

            echo '</div>';
        }
        echo '</div>';
    }

    public function handle_reply_review()
    {
        if (!is_user_logged_in()) wp_die('Unauthorized');

        $comment_id     = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
        $listing_id     = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $reply_content  = isset($_POST['reply_content']) ? sanitize_textarea_field($_POST['reply_content']) : '';

        if (!$comment_id || !$listing_id || empty($reply_content)) $this->redirect_with_error('invalid_data');

        check_admin_referer('obenlo_reply_review_' . $comment_id);

        $user_id = get_current_user_id();
        $listing = get_post($listing_id);
        if (!$listing || ($listing->post_author != $user_id && !current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $user    = wp_get_current_user();
        $reply_id = wp_insert_comment(array(
            'comment_post_ID'      => $listing_id,
            'comment_author'       => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_url'   => esc_url(get_author_posts_url($user_id)),
            'comment_content'      => $reply_content,
            'comment_type'         => 'comment',
            'comment_parent'       => $comment_id,
            'user_id'              => $user_id,
            'comment_approved'     => 1,
        ));

        if ($reply_id) {
            wp_safe_redirect(add_query_arg(array('action' => 'reviews', 'message' => 'saved'), home_url('/host-dashboard')));
            exit;
        } else {
            $this->redirect_with_error('booking_error');
        }
    }

    public function handle_approve_review()
    {
        if (!is_user_logged_in()) wp_die('Unauthorized');

        $comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : 0;
        if (!$comment_id) $this->redirect_with_error('invalid_data');

        check_admin_referer('obenlo_approve_review_' . $comment_id);

        $comment = get_comment($comment_id);
        if (!$comment) $this->redirect_with_error('invalid_data');

        $user_id = get_current_user_id();
        $listing = get_post($comment->comment_post_ID);
        if (!$listing || ($listing->post_author != $user_id && !current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        wp_set_comment_status($comment_id, 'approve');
        wp_safe_redirect(add_query_arg(array('action' => 'reviews', 'message' => 'approved'), home_url('/host-dashboard')));
        exit;
    }
}
