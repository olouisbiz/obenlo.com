<?php
/**
 * Email Notification System - Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Notifications
{

    private static $info_email = 'info@obenlo.com';
    private static $admin_email = 'admin@obenlo.com';

    public function init()
    {
        // Set custom sender hooks
        add_filter('wp_mail_from', array($this, 'set_mail_from'));
        add_filter('wp_mail_from_name', array($this, 'set_mail_from_name'));
    }

    /**
     * Set the 'From' email address to info@obenlo.com for all outgoing mail
     */
    public function set_mail_from($email)
    {
        return self::$info_email;
    }

    /**
     * Set the 'From' name to Obenlo
     */
    public function set_mail_from_name($name)
    {
        return 'Obenlo';
    }

    /**
     * Send email to site administrator
     */
    public static function send_to_admin($subject, $message)
    {
        wp_mail(self::$admin_email, $subject, $message);
    }

    /**
     * Send email to a user (Host or Guest) from info@obenlo.com
     */
    public static function send_to_user($user_id, $subject, $message)
    {
        $user = get_userdata($user_id);
        if ($user) {
            wp_mail($user->user_email, $subject, $message);
        }
    }

    /**
     * Centralized Booking Notification
     */
    public static function notify_booking_event($booking_id, $event)
    {
        $listing_id = get_post_meta($booking_id, '_obenlo_listing_id', true);
        $listing_title = get_the_title($listing_id);
        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
        $guest_id = get_post_field('post_author', $booking_id);
        $total = get_post_meta($booking_id, '_obenlo_total_price', true);

        switch ($event) {
            case 'new_booking':
                $subject = "New Booking Request: $listing_title";
                $msg = "A new booking has been requested for your listing: $listing_title.\nTotal: $$total\nView details: " . home_url('/host-dashboard/?action=bookings');
                self::send_to_user($host_id, $subject, $msg);
                self::send_to_admin("New Platform Booking #$booking_id", "A new booking request for $listing_title ($$total) has been made.");
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($host_id, 'New Booking Request', "For $listing_title ($$total)", home_url('/host-dashboard/?action=bookings'));
                }
                break;

            case 'booking_confirmed':
                $confirmation_code = get_post_meta($booking_id, '_obenlo_confirmation_code', true);
                $guest_id_val = get_post_meta($booking_id, '_obenlo_guest_id', true);
                $subject = "Booking Confirmed! - $listing_title";
                $msg = "Great news! Your booking for $listing_title has been confirmed.\n\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
                $msg .= "  BOOKING CONFIRMATION CODE\n";
                $msg .= "  " . ($confirmation_code ?: 'N/A') . "\n";
                
                if ($guest_id_val) {
                    $msg .= "  GUEST ID: " . $guest_id_val . "\n";
                }
                $msg .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                $msg .= "Keep this code and ID — the host will use them to verify your booking at check-in.\n\n";

                $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                if ($virtual_link) {
                    $secure_join_url = Obenlo_Booking_Virtual_Security::get_secure_join_url($booking_id);
                    $msg .= "🌐 VIRTUAL CLASS/EVENT LINK:\n";
                    $msg .= $secure_join_url . "\n\n";
                    $msg .= "Please use this SECURE link to join the event. Do not share this link with others.\n\n";
                }

                $msg .= "Total: $$total\n";
                $msg .= "View all your bookings: " . home_url('/trips');
                self::send_to_user($guest_id, $subject, $msg);
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($guest_id, 'Booking Confirmed!', "Your trip to $listing_title is set.", home_url('/trips'));
                }
                break;

            case 'booking_cancelled':
                $subject = "Booking Cancelled - $listing_title";
                $msg = "The booking for $listing_title has been cancelled.\nTotal refund: $$total\nView details: " . home_url();
                self::send_to_user($guest_id, $subject, $msg);
                self::send_to_user($host_id, $subject, $msg);
                if (class_exists('Obenlo_Booking_Push_Notifications')) {
                    Obenlo_Booking_Push_Notifications::send_push($guest_id, 'Booking Cancelled', "Trip to $listing_title cancelled.", home_url('/trips'));
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
        $user_id = get_post_field('post_author', $ticket_id);
        $type = get_post_meta($ticket_id, '_obenlo_ticket_type', true);

        if ($event === 'new_ticket') {
            self::send_to_admin("New Support Ticket: $ticket_title", "Type: $type\nUser ID: $user_id\nView in Admin Dashboard.");
        }
        elseif ($event === 'ticket_reply') {
            // Determine who to notify
            $last_reply = get_posts(array(
                'post_type' => 'obenlo_message',
                'meta_key' => '_obenlo_ticket_parent_id',
                'meta_value' => $ticket_id,
                'posts_per_page' => 1,
                'order' => 'DESC'
            ));

            if (!empty($last_reply)) {
                $author_id = $last_reply[0]->post_author;
                if (user_can($author_id, 'administrator')) {
                    // Notify User
                    self::send_to_user($user_id, "New Update on Support Ticket: $ticket_title", "Obenlo Support has replied to your ticket.\nView online: " . home_url('/support?ticket_id=' . $ticket_id));
                }
                else {
                    // Notify Admin
                    $author = get_userdata($author_id);
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
        $sender_id = get_post_field('post_author', $message_id);
        $sender = get_userdata($sender_id);
        $sender_name = $sender ? $sender->display_name : 'Someone';

        $subject = "New Message from $sender_name - Obenlo";
        $msg = "You have received a new message on Obenlo from $sender_name.\n\nView and reply here: " . home_url('/messages');

        self::send_to_user($recipient_id, $subject, $msg);
    }

    /**
     * Centralized Broadcast Notification
     */
    public static function notify_broadcast_event($broadcast_id, $target)
    {
        $title = get_the_title($broadcast_id);
        $content = get_post_field('post_content', $broadcast_id);

        $args = array('fields' => 'ID');
        if ($target === 'hosts') {
            $args['role'] = 'host';
        }
        elseif ($target === 'guests') {
            $args['role'] = 'guest';
        } // 'both' will return everyone if we don't specify role

        $users = get_users($args);
        $subject = "Important Update: $title";
        $msg = "A new announcement has been made on Obenlo:\n\n$content\n\nView details in your dashboard.";

        foreach ($users as $user_id) {
            self::send_to_user($user_id, $subject, $msg);
        }
    }

    /**
     * Host Specific Events (Onboarding/Verification)
     */
    public static function notify_host_event($user_id, $event)
    {
        $user = get_userdata($user_id);
        if (!$user)
            return;

        switch ($event) {
            case 'welcome_host':
                $subject = "Welcome to the Obenlo Host Community!";
                $msg = "Hi " . $user->display_name . ",\n\n";
                $msg .= "Welcome to Obenlo! We're thrilled to have you as a host.\n\n";
                $msg .= "To start hosting and earning, please complete your identity verification in your dashboard:\n";
                $msg .= home_url('/host-onboarding') . "\n\n";
                $msg .= "If you have any questions, our support team is always here to help.\n\n";
                $msg .= "Let's create amazing experiences together!\nTeam Obenlo";
                self::send_to_user($user_id, $subject, $msg);
                break;

            case 'host_verified':
                $subject = "🎉 Your account has been verified!";
                $msg = "Hi " . $user->display_name . ",\n\n";
                $msg .= "Great news! Your identity verification has been approved. Your listings are now eligible for instant bookings and featured placement.\n\n";
                $msg .= "View your dashboard: " . home_url('/host-dashboard') . "\n\n";
                $msg .= "Happy hosting!";
                self::send_to_user($user_id, $subject, $msg);
                break;

            case 'host_rejected':
                $subject = "Update regarding your verification";
                $msg = "Hi " . $user->display_name . ",\n\n";
                $msg .= "We were unable to verify your identity with the document provided.\n\n";
                $msg .= "Please log in to your dashboard to review the requirements and re-upload a clear document:\n";
                $msg .= home_url('/host-onboarding') . "\n\n";
                $msg .= "If you believe this was an error, please contact support@obenlo.com.";
                self::send_to_user($user_id, $subject, $msg);
                break;
        }
    }

    /**
     * Notify Staff via External Webhook (Telegram/WhatsApp/etc)
     */
    public static function notify_live_chat_webhook($session_id, $message)
    {
        $bot_token = get_option('obenlo_telegram_bot_token');
        $chat_ids_string = get_option('obenlo_telegram_chat_id'); // Assuming this option stores a comma-separated string of chat IDs

        if (!$bot_token || !$chat_ids_string)
            return;

        $msg = "📢 <b>Obenlo Live Chat Alert</b>\n";
        $msg .= "Session: <code>$session_id</code>\n\n";
        $msg .= "Message: " . esc_html($message) . "\n\n";
        $msg .= "Reply directly to THIS message to respond to the guest.";

        $chat_id_array = array_map('trim', explode(',', $chat_ids_string));
        $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

        foreach ($chat_id_array as $chat_id) {
            if (empty($chat_id))
                continue;

            wp_remote_post($api_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'sslverify' => false,
                'body' => array(
                    'chat_id' => $chat_id,
                    'text' => $msg,
                    'parse_mode' => 'HTML'
                ),
            ));
        }
    }
}
