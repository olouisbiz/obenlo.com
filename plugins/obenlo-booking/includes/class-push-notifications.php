<?php
/**
 * Web Push Notification System - Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure composer autoload is loaded
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

class Obenlo_Booking_Push_Notifications
{
    public function init()
    {
        add_action('wp_ajax_nopriv_obenlo_get_vapid_key', array($this, 'get_vapid_key'));
        add_action('wp_ajax_obenlo_get_vapid_key', array($this, 'get_vapid_key'));
        add_action('wp_ajax_nopriv_obenlo_save_push_subscription', array($this, 'save_subscription'));
        add_action('wp_ajax_obenlo_save_push_subscription', array($this, 'save_subscription'));
    }

    /**
     * Retrieve or Generate VAPID Keys
     */
    private function get_vapid_credentials()
    {
        $keys = get_option('obenlo_vapid_keys');
        if (!$keys) {
            if (class_exists('Minishlink\WebPush\VAPID')) {
                $keys = VAPID::createVapidKeys();
                update_option('obenlo_vapid_keys', $keys);
            }
            else {
                return false;
            }
        }
        return $keys;
    }

    /**
     * AJAX Endpoint: Get Public VAPID Key
     */
    public function get_vapid_key()
    {
        $keys = $this->get_vapid_credentials();
        if ($keys && isset($keys['publicKey'])) {
            wp_send_json_success($keys['publicKey']);
        }
        else {
            wp_send_json_error('VAPID keys not configured');
        }
    }

    /**
     * AJAX Endpoint: Save Push Subscription
     */
    public function save_subscription()
    {
        if (!isset($_POST['subscription'])) {
            wp_send_json_error('Missing subscription data');
        }

        $subscription = json_decode(stripslashes($_POST['subscription']), true);
        if (!$subscription || !isset($subscription['endpoint'])) {
            wp_send_json_error('Invalid subscription format');
        }

        $user_id = is_user_logged_in() ? get_current_user_id() : 0;
        $endpoint = esc_url_raw($subscription['endpoint']);
        $p256dh = sanitize_text_field($subscription['keys']['p256dh'] ?? '');
        $auth = sanitize_text_field($subscription['keys']['auth'] ?? '');

        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_push_subscribers';

        // Check if exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE endpoint = %s", $endpoint));

        if ($existing) {
            $wpdb->update($table_name, array(
                'user_id' => $user_id,
                'p256dh' => $p256dh,
                'auth' => $auth
            ), array('id' => $existing));
        }
        else {
            $wpdb->insert($table_name, array(
                'user_id' => $user_id,
                'endpoint' => $endpoint,
                'p256dh' => $p256dh,
                'auth' => $auth
            ));
        }

        wp_send_json_success('Subscription saved');
    }

    /**
     * Send Web Push Notification to User
     */
    public static function send_push($user_id, $title, $body, $url = '')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_push_subscribers';
        $subscribers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id));

        if (empty($subscribers) || !class_exists('Minishlink\WebPush\WebPush')) {
            return false;
        }

        $instance = new self();
        $keys = $instance->get_vapid_credentials();
        if (!$keys)
            return false;

        $auth = array(
            'VAPID' => array(
                'subject' => 'mailto:admin@obenlo.com',
                'publicKey' => $keys['publicKey'],
                'privateKey' => $keys['privateKey'],
            ),
        );

        $webPush = new WebPush($auth);

        $payload = json_encode(array(
            'title' => $title,
            'body' => $body,
            'url' => $url ?: home_url()
        ));

        foreach ($subscribers as $sub) {
            try {
                $webPush->queueNotification(
                    Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth' => $sub->auth
                    ]
                ]),
                    $payload
                );
            }
            catch (Exception $e) {
                // Log exception
                error_log('WebPush Queue error: ' . $e->getMessage());
            }
        }

        // Flush queued notifications
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
                // Remove expired subscription
                $wpdb->delete($table_name, array('endpoint' => $endpoint));
            }
        }

        return true;
    }
}
