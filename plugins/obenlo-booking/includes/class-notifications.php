<?php
/**
 * Email Notification System - Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Notifications
{

    public function init()
    {
        add_filter('wp_mail_from', array($this, 'set_mail_from'));
        add_filter('wp_mail_from_name', array($this, 'set_mail_from_name'));
        add_filter('wp_mail_content_type', array($this, 'set_mail_content_type'));
    }

    public function set_mail_from($email)
    {
        return get_option('obenlo_info_email', 'info@obenlo.com');
    }

    public function set_mail_from_name($name)
    {
        return 'Obenlo';
    }

    public function set_mail_content_type()
    {
        return 'text/html';
    }

    /**
     * Wrap plain-text content in the Obenlo HTML email template.
     */
    public static function wrap_template($subject, $body_html, $cta_label = '', $cta_url = '')
    {
        $logo_url  = 'https://obenlo.com/wp-content/themes/obenlo/assets/images/logo-social-profile.png';
        $site_url  = home_url('/');
        $year      = date('Y');

        $cta_block = '';
        if ($cta_label && $cta_url) {
            $cta_block = '
            <div style="text-align:center; margin: 32px 0;">
                <a href="' . esc_url($cta_url) . '" style="background:#e61e4d; color:#ffffff; text-decoration:none; font-weight:700; font-size:1rem; padding:14px 36px; border-radius:14px; display:inline-block; letter-spacing:-0.01em;">'
                . esc_html($cta_label) . '
                </a>
            </div>';
        }

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc_html($subject) . '</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f7; font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f7; padding: 40px 20px;">
  <tr>
    <td align="center">
      <table width="100%" style="max-width:600px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.07);">

        <!-- Header -->
        <tr>
          <td style="background:#e61e4d; padding:28px 40px; text-align:center;">
            <a href="' . esc_url($site_url) . '" style="text-decoration:none;">
              <span style="font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:32px; font-weight:900; color:#ffffff; letter-spacing:-1.5px;">Obenlo</span>
            </a>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 40px 20px 40px; color:#1a1a1a; font-size:15px; line-height:1.7;">
            ' . $body_html . $cta_block . '
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style="padding:0 40px;">
            <hr style="border:none; border-top:1px solid #eee; margin:0;">
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:24px 40px; text-align:center; color:#888; font-size:12px; line-height:1.6;">
            <p style="margin:0 0 6px 0;">© ' . $year . ' Obenlo, Inc. All rights reserved.</p>
            <p style="margin:0;">
              <a href="' . esc_url(home_url('/privacy')) . '" style="color:#aaa; text-decoration:none;">Privacy Policy</a>
              &middot;
              <a href="' . esc_url(home_url('/terms')) . '" style="color:#aaa; text-decoration:none;">Terms of Service</a>
              &middot;
              <a href="' . esc_url(home_url('/support')) . '" style="color:#aaa; text-decoration:none;">Help Center</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>';
    }

    /**
     * Convert a plain-text message to simple HTML paragraphs.
     */
    private static function text_to_html($text)
    {
        $escaped = nl2br(esc_html($text));
        return '<p style="margin:0 0 16px 0;">' . $escaped . '</p>';
    }

    /**
     * Send email to site administrator
     */
    public static function send_to_admin($subject, $message, $cta_label = '', $cta_url = '')
    {
        $admin_email = get_option('obenlo_admin_email', 'info@obenlo.com');
        $body_html   = self::text_to_html($message);
        $html        = self::wrap_template($subject, $body_html, $cta_label, $cta_url);
        wp_mail($admin_email, $subject, $html);
    }

    /**
     * Send email to a user (Host or Guest)
     */
    public static function send_to_user($user_id, $subject, $message, $cta_label = '', $cta_url = '')
    {
        $user = get_userdata($user_id);
        if ($user) {
            $body_html = self::text_to_html($message);
            $html      = self::wrap_template($subject, $body_html, $cta_label, $cta_url);
            wp_mail($user->user_email, $subject, $html);
        }
    }

    /**
     * Send raw HTML email to a user
     */
    public static function send_html_to_user($user_id, $subject, $body_html, $cta_label = '', $cta_url = '')
    {
        $user = get_userdata($user_id);
        if ($user) {
            $html = self::wrap_template($subject, $body_html, $cta_label, $cta_url);
            wp_mail($user->user_email, $subject, $html);
        }
    }

    /**
     * Centralized Booking Notification
     */
    public static function notify_booking_event($booking_id, $event)
    {
        $listing_id    = get_post_meta($booking_id, '_obenlo_listing_id', true);
        $listing_title = get_the_title($listing_id);
        $host_id       = get_post_meta($booking_id, '_obenlo_host_id', true);
        $guest_id      = get_post_field('post_author', $booking_id);
        $total         = get_post_meta($booking_id, '_obenlo_total_price', true);

        switch ($event) {
            case 'new_booking':
                $subject = "New Booking Request: $listing_title";
                $msg     = "A new booking has been requested for your listing: <strong>$listing_title</strong>.<br>Total: <strong>\$$total</strong>";
                self::send_html_to_user($host_id, $subject, "<p style='margin:0 0 16px 0;'>$msg</p>", 'View Bookings', home_url('/host-dashboard/?action=bookings'));
                self::send_to_admin("New Platform Booking #$booking_id", "A new booking request for $listing_title (\$$total) has been made.");
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($host_id, 'New Booking Request', "For $listing_title (\$$total)", home_url('/host-dashboard/?action=bookings'));
                }
                break;

            case 'booking_confirmed':
                $confirmation_code = get_post_meta($booking_id, '_obenlo_confirmation_code', true);
                $guest_id_val      = get_post_meta($booking_id, '_obenlo_guest_id', true);
                $subject           = "Booking Confirmed! – $listing_title";

                $body_html = '
                <p style="margin:0 0 16px 0;">Great news! Your booking for <strong>' . esc_html($listing_title) . '</strong> has been confirmed.</p>
                <div style="background:#fef2f2; border:1px solid #fee2e2; border-radius:12px; padding:20px 24px; margin:0 0 20px 0; text-align:center;">
                    <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1.5px; color:#e61e4d; font-weight:700; margin-bottom:8px;">Confirmation Code</div>
                    <div style="font-size:2rem; font-weight:900; letter-spacing:4px; color:#111; font-family:monospace;">' . esc_html($confirmation_code ?: 'N/A') . '</div>';

                if ($guest_id_val) {
                    $body_html .= '<div style="margin-top:8px; font-size:0.85rem; color:#555;">Guest ID: <strong>' . esc_html($guest_id_val) . '</strong></div>';
                }

                $body_html .= '</div>
                <p style="margin:0 0 16px 0; font-size:0.9rem; color:#555;">Show this code to the host at check-in to verify your booking.</p>';

                $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                if ($virtual_link) {
                    $secure_join_url = Obenlo_Booking_Virtual_Security::get_secure_join_url($booking_id);
                    $body_html      .= '<div style="background:#eef2ff; border:1px solid #c7d2fe; border-radius:12px; padding:16px 20px; margin:0 0 20px 0;">
                        <strong style="color:#3730a3;">🌐 Virtual Event Link</strong><br>
                        <a href="' . esc_url($secure_join_url) . '" style="color:#4f46e5; word-break:break-all;">Join the session</a><br>
                        <span style="font-size:0.8rem; color:#666;">Do not share this link. It is unique to your booking.</span>
                    </div>';
                }

                $body_html .= '<p style="margin:0 0 16px 0;">Total paid: <strong>$' . esc_html($total) . '</strong></p>';

                self::send_html_to_user($guest_id, $subject, $body_html, 'View My Trips', home_url('/account?tab=trips'));
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($guest_id, 'Booking Confirmed!', "Your trip to $listing_title is set.", home_url('/account?tab=trips'));
                }
                break;

            case 'booking_cancelled':
                $subject = "Booking Cancelled – $listing_title";
                $msg     = "The booking for <strong>$listing_title</strong> has been cancelled.<br>Refund amount: <strong>\$$total</strong>";
                self::send_html_to_user($guest_id, $subject, "<p style='margin:0 0 16px 0;'>$msg</p>", 'View Account', home_url('/account'));
                self::send_html_to_user($host_id, $subject, "<p style='margin:0 0 16px 0;'>$msg</p>", 'View Dashboard', home_url('/host-dashboard/?action=bookings'));
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($guest_id, 'Booking Cancelled', "Trip to $listing_title cancelled.", home_url('/account'));
                    Obenlo_Booking_Push_Notifications::send_push($host_id, 'Booking Cancelled', "Trip to $listing_title cancelled.", home_url('/host-dashboard/?action=bookings'));
                }
                break;
        }
    }

    /**
     * Centralized Ticket Notification
     */
    public static function notify_ticket_event($ticket_id, $event)
    {
        $ticket_title = get_the_title($ticket_id);
        $user_id      = get_post_field('post_author', $ticket_id);
        $type         = get_post_meta($ticket_id, '_obenlo_ticket_type', true);

        if ($event === 'new_ticket') {
            self::send_to_admin("New Support Ticket: $ticket_title", "Type: $type\nUser ID: $user_id\nView in Admin Dashboard.");
        } elseif ($event === 'ticket_reply') {
            $last_reply = get_posts(array(
                'post_type'      => 'obenlo_message',
                'meta_key'       => '_obenlo_ticket_parent_id',
                'meta_value'     => $ticket_id,
                'posts_per_page' => 1,
                'order'          => 'DESC'
            ));

            if (!empty($last_reply)) {
                $author_id = $last_reply[0]->post_author;
                if (user_can($author_id, 'administrator')) {
                    self::send_to_user($user_id, "New Update on Support Ticket: $ticket_title", "Obenlo Support has replied to your ticket.", 'View Ticket', home_url('/support?ticket_id=' . $ticket_id));
                } else {
                    $author      = get_userdata($author_id);
                    $author_name = $author ? $author->display_name : "User #$author_id";
                    self::send_to_admin("New Reply on Ticket: $ticket_title", "$author_name has replied to ticket #$ticket_id.");
                }
            }
        }
    }

    /**
     * Centralized Message Notification
     */
    public static function notify_message_event($message_id, $recipient_id)
    {
        $sender_id   = get_post_field('post_author', $message_id);
        $sender      = get_userdata($sender_id);
        $sender_name = $sender ? $sender->display_name : 'Someone';

        $subject   = "New Message from $sender_name – Obenlo";
        $body_html = '<p style="margin:0 0 16px 0;">You have received a new message on Obenlo from <strong>' . esc_html($sender_name) . '</strong>.</p>';

        self::send_html_to_user($recipient_id, $subject, $body_html, 'View Messages', home_url('/messages'));
    }

    /**
     * Centralized Broadcast Notification
     */
    public static function notify_broadcast_event($broadcast_id, $target)
    {
        $title   = get_the_title($broadcast_id);
        $content = get_post_field('post_content', $broadcast_id);

        $args = array('fields' => 'ID');
        if ($target === 'hosts') {
            $args['role'] = 'host';
        } elseif ($target === 'guests') {
            $args['role'] = 'guest';
        }

        $users     = get_users($args);
        $subject   = "Important Update: $title";
        $body_html = '<p style="margin:0 0 16px 0;">' . wp_kses_post(wpautop($content)) . '</p>';

        foreach ($users as $user_id) {
            self::send_html_to_user($user_id, $subject, $body_html, 'View Dashboard', home_url('/account'));
        }
    }

    /**
     * Host Specific Events (Onboarding / Verification)
     */
    public static function notify_host_event($user_id, $event)
    {
        $user = get_userdata($user_id);
        if (!$user) return;

        $name = $user->display_name;

        switch ($event) {
            case 'welcome_host':
                $subject   = 'Welcome to the Obenlo Host Community!';
                $body_html = '
                <p style="margin:0 0 16px 0;">Hi <strong>' . esc_html($name) . '</strong>,</p>
                <p style="margin:0 0 16px 0;">Welcome to Obenlo! We\'re thrilled to have you as a host.</p>
                <p style="margin:0 0 16px 0;">To start hosting and earning, please complete your identity verification in your dashboard.</p>
                <p style="margin:0 0 16px 0; color:#666; font-size:0.9rem;">If you have any questions, our support team is always here to help.</p>
                <p style="margin:0;">Let\'s create amazing experiences together!<br><strong>Team Obenlo</strong></p>';
                self::send_html_to_user($user_id, $subject, $body_html, 'Complete Verification', home_url('/host-onboarding'));
                break;

            case 'host_verified':
                $subject   = '🎉 Your account has been verified!';
                $body_html = '
                <p style="margin:0 0 16px 0;">Hi <strong>' . esc_html($name) . '</strong>,</p>
                <p style="margin:0 0 16px 0;">Great news! Your identity verification has been approved.</p>
                <p style="margin:0 0 16px 0;">Your listings are now eligible for <strong>instant bookings</strong> and featured placement on the platform.</p>
                <p style="margin:0;">Happy hosting! 🏡</p>';
                self::send_html_to_user($user_id, $subject, $body_html, 'Go to Dashboard', home_url('/host-dashboard'));
                break;

            case 'host_rejected':
                $subject   = 'Update regarding your verification';
                $body_html = '
                <p style="margin:0 0 16px 0;">Hi <strong>' . esc_html($name) . '</strong>,</p>
                <p style="margin:0 0 16px 0;">We were unable to verify your identity with the document provided.</p>
                <p style="margin:0 0 16px 0;">Please log in to your dashboard to review the requirements and re-upload a clear document.</p>
                <p style="margin:0; font-size:0.9rem; color:#666;">If you believe this was an error, please contact <a href="mailto:support@obenlo.com" style="color:#e61e4d;">support@obenlo.com</a>.</p>';
                self::send_html_to_user($user_id, $subject, $body_html, 'Re-upload Document', home_url('/host-onboarding'));
                break;
        }
    }

    /**
     * Notify Staff via External Webhook (Telegram/WhatsApp/etc)
     */
    public static function notify_live_chat_webhook($session_id, $message)
    {
        $bot_token       = get_option('obenlo_telegram_bot_token');
        $chat_ids_string = get_option('obenlo_telegram_chat_id');

        if (!$bot_token || !$chat_ids_string) return;

        $msg  = "📢 <b>Obenlo Live Chat Alert</b>\n";
        $msg .= "Session: <code>$session_id</code>\n\n";
        $msg .= "Message: " . esc_html($message) . "\n\n";
        $msg .= "Reply directly to THIS message to respond to the guest.";

        $chat_id_array = array_map('trim', explode(',', $chat_ids_string));
        $api_url       = "https://api.telegram.org/bot{$bot_token}/sendMessage";

        foreach ($chat_id_array as $chat_id) {
            if (empty($chat_id)) continue;
            wp_remote_post($api_url, array(
                'method'   => 'POST',
                'timeout'  => 45,
                'sslverify' => false,
                'body'     => array(
                    'chat_id'    => $chat_id,
                    'text'       => $msg,
                    'parse_mode' => 'HTML'
                ),
            ));
        }
    }
}
