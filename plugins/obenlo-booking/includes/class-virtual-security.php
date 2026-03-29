<?php
/**
 * Virtual Event Security Proxy - Obenlo
 * Protects raw virtual links from being shared with non-buyers.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Virtual_Security
{

    public function init()
    {
        add_action('admin_post_obenlo_join_event', array($this, 'handle_join_event'));
        add_action('admin_post_nopriv_obenlo_join_event', array($this, 'handle_join_event'));
    }

    /**
     * Handle the secure redirect to virtual events
     */
    public function handle_join_event()
    {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
        $guest_id_param = isset($_GET['guest_id']) ? sanitize_text_field($_GET['guest_id']) : '';

        if (!$booking_id) {
            obenlo_redirect_with_error('invalid_booking');
        }

        // 1. NONCE VALIDATION
        $is_valid_nonce = wp_verify_nonce($nonce, 'join_event_' . $booking_id);
        
        // 2. GUEST ID VALIDATION (Fallback for PWA/Mobile sessions)
        $is_valid_guest = false;
        $actual_guest_id = get_post_meta($booking_id, '_obenlo_guest_id', true);
        if ($guest_id_param && $actual_guest_id === $guest_id_param) {
            $is_valid_guest = true;
        }

        if (!$is_valid_nonce && !$is_valid_guest) {
            obenlo_redirect_with_error('security_failed');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            obenlo_redirect_with_error('invalid_booking');
        }

        // Verify status
        $status = get_post_meta($booking_id, '_obenlo_booking_status', true);
        if (!in_array($status, ['confirmed', 'approved', 'completed'])) {
            obenlo_redirect_with_error('invalid_booking');
        }

        // Authorization finalized (Already passed nonce or guest_id check)
        $is_authorized = true;

        $listing_id = get_post_meta($booking_id, '_obenlo_listing_id', true);
        $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);

        if (!$virtual_link) {
            obenlo_redirect_with_error('booking_error');
        }

        // Log the join event (optional)
        error_log("Obenlo: Guest joined virtual event for booking #$booking_id");

        // Mobile-Friendly Bridge Page
        // Silent PHP redirects are often blocked by mobile browsers/PWAs
        // We use a bridge page with a manual button to force the deep-link to Google Meet app
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
            <title><?php echo __('Joining Event...', 'obenlo'); ?></title>
            <style>
                body {
                    margin: 0; padding: 0;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    background: #fff; color: #333;
                    display: flex; align-items: center; justify-content: center;
                    height: 100vh; text-align: center;
                }
                .container { max-width: 90%; padding: 30px; }
                .logo { font-size: 2rem; font-weight: 800; color: #e61e4d; letter-spacing: -1px; margin-bottom: 40px; }
                .status-icon { font-size: 50px; margin-bottom: 20px; animation: bounce 2s infinite; }
                h1 { margin: 0 0 15px 0; font-size: 1.5rem; color: #222; }
                p { margin: 0 0 40px 0; color: #666; line-height: 1.5; }
                .join-btn {
                    display: inline-block;
                    background: #e61e4d; color: #fff;
                    padding: 18px 40px; border-radius: 14px;
                    text-decoration: none; font-weight: 700; font-size: 1.1rem;
                    box-shadow: 0 10px 20px rgba(230, 30, 77, 0.2);
                    transition: transform 0.2s;
                }
                .join-btn:active { transform: scale(0.96); }
                .footer { margin-top: 60px; font-size: 0.8rem; color: #999; }
                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
                    40% {transform: translateY(-10px);}
                    60% {transform: translateY(-5px);}
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="logo">Obenlo</div>
                <div class="status-icon">🚀</div>
                <h1><?php echo __('Joining Your Event...', 'obenlo'); ?></h1>
                <p><?php echo __('Please click the button below if your meeting doesn\'t open automatically.', 'obenlo'); ?></p>
                
                <a href="<?php echo esc_url($virtual_link); ?>" target="_blank" class="join-btn" id="joinBtn">
                    <?php echo __('Join Event Now', 'obenlo'); ?>
                </a>

                <div class="footer">
                    <?php echo __('Secure Redirect by Obenlo', 'obenlo'); ?>
                </div>
            </div>

            <script type="text/javascript">
                setTimeout(function() {
                    // Try auto-redirect first
                    window.location.href = "<?php echo esc_js($virtual_link); ?>";
                }, 1000);
            </script>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Generate a secure join URL for a booking
     */
    public static function get_secure_join_url($booking_id)
    {
        $guest_id = get_post_meta($booking_id, '_obenlo_guest_id', true);
        $url = admin_url('admin-post.php?action=obenlo_join_event&booking_id=' . $booking_id);
        $url = wp_nonce_url($url, 'join_event_' . $booking_id);
        
        if ($guest_id) {
            $url = add_query_arg('guest_id', $guest_id, $url);
        }
        
        return $url;
    }
}
