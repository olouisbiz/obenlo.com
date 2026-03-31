<?php
/**
 * Payment and Booking Handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Payments
{

    public function init()
    {
        // Handle frontend booking submissions
        add_action('admin_post_nopriv_obenlo_submit_booking', array($this, 'handle_booking_submission'));
        add_action('admin_post_obenlo_submit_booking', array($this, 'handle_booking_submission'));

        // Handle timeslot generation
        add_action('wp_ajax_nopriv_obenlo_get_timeslots', array($this, 'handle_get_timeslots'));
        add_action('wp_ajax_obenlo_get_timeslots', array($this, 'handle_get_timeslots'));

        // Handle payment returns
        add_action('template_redirect', array($this, 'handle_payment_return'));
    }

    public function handle_get_timeslots()
    {
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';

        if (!$listing_id || !$date) {
            wp_send_json_error(array('message' => 'Missing required data.'));
        }

        $host_id = get_post_field('post_author', $listing_id);
        $business_hours = get_user_meta($host_id, '_obenlo_business_hours', true);
        if (!is_array($business_hours) || empty($business_hours)) {
            $business_hours = array(
                'monday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'tuesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'wednesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'thursday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'friday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'saturday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
                'sunday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
            );
        }

        $timestamp = strtotime($date);
        $day_of_week = strtolower(date('l', $timestamp));

        if (!isset($business_hours[$day_of_week]) || $business_hours[$day_of_week]['active'] !== 'yes') {
            wp_send_json_error(array('message' => 'The host does not accept bookings on ' . date('l', $timestamp) . 's.'));
        }

        $conf = $business_hours[$day_of_week];
        $start_time = $conf['start'];
        $end_time = $conf['end'];

        $duration_val = get_post_meta($listing_id, '_obenlo_duration_val', true);
        $duration_unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';

        $duration_minutes = 60; // Default
        if ($duration_val) {
            if ($duration_unit === 'hours') {
                $duration_minutes = floatval($duration_val) * 60;
            }
            else {
                $duration_minutes = floatval($duration_val);
            }
        }

        // Get existing bookings
        $bookings = get_posts(array(
            'post_type' => 'booking',
            'meta_query' => array(
                    array(
                    'key' => '_obenlo_listing_id',
                    'value' => $listing_id
                ),
                    array(
                    'key' => '_obenlo_start_date',
                    'value' => $date . 'T',
                    'compare' => 'LIKE'
                )
            ),
            'post_status' => array('publish', 'draft'),
            'posts_per_page' => -1
        ));

        $booked_slots = array();
        foreach ($bookings as $b) {
            $booked_start = get_post_meta($b->ID, '_obenlo_start_date', true);
            $booked_dur = get_post_meta($b->ID, '_obenlo_duration_mins', true) ?: $duration_minutes;

            $dt = new DateTime($booked_start);
            $booked_slots[] = array(
                'start_time' => $dt->getTimestamp(),
                'end_time' => $dt->getTimestamp() + ($booked_dur * 60)
            );
        }

        // Generate slots
        $slots = array();
        $current_time = strtotime($date . ' ' . $start_time);
        $closing_time = strtotime($date . ' ' . $end_time);

        if ($date == date('Y-m-d')) {
            $now = current_time('timestamp');
            if ($current_time < $now) {
                // Find next valid slot interval
                $diff = ($now - $current_time) / 60;
                $chunks_to_skip = ceil($diff / $duration_minutes);
                $current_time += ($chunks_to_skip * $duration_minutes * 60);
            }
        }

        while (($current_time + ($duration_minutes * 60)) <= $closing_time) {
            $slot_end = $current_time + ($duration_minutes * 60);
            $conflict = false;

            foreach ($booked_slots as $bs) {
                if ($current_time < $bs['end_time'] && $slot_end > $bs['start_time']) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict) {
                $slots[] = array(
                    'time' => date('H:i', $current_time),
                    'label' => date('g:i A', $current_time)
                );
            }
            $current_time += ($duration_minutes * 60);
        }

        wp_send_json_success(array('slots' => $slots));
    }

    /**
     * Generate a unique Guest ID (e.g. G-X1Y2Z3)
     */
    public static function generate_guest_id()
    {
        return 'G-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    }

    /**
     * Get or create Guest ID for a user
     */
    public static function get_user_guest_id($user_id)
    {
        if (!$user_id) return '';
        $guest_id = get_user_meta($user_id, '_obenlo_guest_id', true);
        if (!$guest_id) {
            $guest_id = self::generate_guest_id();
            update_user_meta($user_id, '_obenlo_guest_id', $guest_id);
        }
        return $guest_id;
    }

    public function handle_booking_submission()
    {
        if (!isset($_POST['obenlo_booking_nonce']) || !wp_verify_nonce($_POST['obenlo_booking_nonce'], 'obenlo_submit_booking_action')) {
            obenlo_redirect_with_error('security_failed');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'stripe';

        // Verify if payment method is enabled
        $is_enabled = get_option('obenlo_' . $payment_method . '_enabled', 'yes');
        if ($is_enabled !== 'yes') {
            obenlo_redirect_with_error('discontinued_payment');
        }

        // Verify country restriction for Haitian methods
        if (in_array($payment_method, ['moncash', 'natcash'])) {
            $listing_country = get_post_meta($listing_id, '_obenlo_listing_country', true) ?: 'usa';
            if ($listing_country !== 'haiti') {
                obenlo_redirect_with_error('localized_payment_only');
            }
        }

        if (!$listing_id || !$start_date) {
            obenlo_redirect_with_error('invalid_data');
        }

        $listing = get_post($listing_id);
        if (!$listing || $listing->post_type !== 'listing') {
            obenlo_redirect_with_error('invalid_listing');
        }

        $host_id = $listing->post_author;
        $booking_start_time = strtotime($start_date);
        $booking_end_time = $end_date ? strtotime($end_date) : $booking_start_time;

        // Calculate Price based on Pricing Model
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';
        $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';
        $price_per_unit = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $total_price = $price_per_unit;
        $duration_mins = 0;

        if ($pricing_model === 'per_night' || $pricing_model === 'per_day') {
            $s = new DateTime($start_date);
            $e = $end_date ? new DateTime($end_date) : clone $s;
            $days = $s->diff($e)->days;
            if ($days > 0) {
                $total_price = $price_per_unit * $days;
            }
        }
        elseif ($pricing_model === 'per_hour') {
            $booking_duration = isset($_POST['booking_duration']) ? floatval($_POST['booking_duration']) : 1;
            $total_price = $price_per_unit * $booking_duration;
            $duration_mins = $booking_duration * 60;
        }
        elseif (in_array($pricing_model, ['per_person', 'per_session', 'per_event', 'per_donation', 'custom_donation', 'flat_fee'])) {
            if ($pricing_model === 'custom_donation') {
                $custom_amount = isset($_POST['custom_donation_amount']) ? floatval($_POST['custom_donation_amount']) : 0;
                $total_price = ($custom_amount > 0) ? $custom_amount : $price_per_unit;
            }
            elseif ($pricing_model === 'per_person') {
                $form_has_guests = true;
                $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);
                if ($capacity && $guests > $capacity) {
                    obenlo_redirect_with_error('capacity_exceeded');
                }
                $total_price = $price_per_unit * max(1, $guests);
            }
            else {
                $total_price = $price_per_unit;
            }

            if ($pricing_model === 'per_session' && $requires_slots === 'yes') {
                $duration_val = get_post_meta($listing_id, '_obenlo_duration_val', true);
                if ($duration_val) {
                    $duration_unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
                    $duration_mins = ($duration_unit === 'hours') ? floatval($duration_val) * 60 : floatval($duration_val);
                }
                else {
                    $duration_mins = 60; // fallback
                }
            }
        }

        // Addons
        $selected_addons = isset($_POST['selected_addons']) ? $_POST['selected_addons'] : array();
        if (!empty($selected_addons) && is_array($selected_addons)) {
            foreach ($selected_addons as $addon_str) {
                $parts = explode('|', $addon_str);
                if (count($parts) === 2) {
                    $total_price += floatval($parts[1]);
                }
            }
        }

        // 1. Check Vacation Blocks
        $vacation_blocks = get_user_meta($host_id, '_obenlo_vacation_blocks', true);
        if (is_array($vacation_blocks)) {
            foreach ($vacation_blocks as $block) {
                $block_start = strtotime($block['start']);
                $block_end = strtotime($block['end'] . ' 23:59:59'); // End of day
                if ($booking_start_time <= $block_end && $booking_end_time >= $block_start) {
                    obenlo_redirect_with_error('host_away');
                }
            }
        }

        // 2. Check Business Hours for exact-time bookings
        if (strpos($start_date, 'T') !== false) {
            $day_of_week = strtolower(date('l', $booking_start_time));
            $time_of_day = date('H:i', $booking_start_time);
            $business_hours = get_user_meta($host_id, '_obenlo_business_hours', true);
            if (!is_array($business_hours) || empty($business_hours)) {
                $business_hours = array(
                    'monday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                    'tuesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                    'wednesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                    'thursday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                    'friday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                    'saturday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
                    'sunday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
                );
            }

            if (is_array($business_hours) && isset($business_hours[$day_of_week])) {
                $day_config = $business_hours[$day_of_week];
                if ($day_config['active'] !== 'yes') {
                    obenlo_redirect_with_error('day_unavailable');
                }
                if ($time_of_day < $day_config['start'] || $time_of_day > $day_config['end']) {
                    obenlo_redirect_with_error('time_unavailable');
                }
            }
        }

        // 3. Double-Booking Prevention (with Multi-Unit Capacity and Ghost Timeout)
        if ($duration_mins > 0 || $pricing_model === 'per_night' || $pricing_model === 'per_day') {
            $available_units = intval(get_post_meta($listing_id, '_obenlo_available_units', true)) ?: 1;

            $existing_bookings = get_posts(array(
                'post_type' => 'booking',
                'meta_query' => array(
                        array(
                        'key' => '_obenlo_listing_id',
                        'value' => $listing_id
                    )
                ),
                'post_status' => 'publish', 
                'posts_per_page' => -1
            ));

            $active_booking_count = 0;
            $expiry_threshold = strtotime('-30 minutes'); // 30 minute grace period for pending

            foreach ($existing_bookings as $b) {
                $b_status = get_post_meta($b->ID, '_obenlo_booking_status', true);
                $b_created = strtotime($b->post_date);

                // Skip "Ghost Bookings" (pending and older than 30 mins)
                if ($b_status === 'pending' && $b_created < $expiry_threshold) {
                    continue; 
                }

                // Skip cancelled
                if ($b_status === 'cancelled' || $b_status === 'failed') {
                    continue;
                }

                $b_start = get_post_meta($b->ID, '_obenlo_start_date', true);
                if (!$b_start) continue;

                $b_start_time = strtotime($b_start);
                if ($pricing_model === 'per_night' || $pricing_model === 'per_day') {
                    $b_end = get_post_meta($b->ID, '_obenlo_end_date', true);
                    $b_end_time = $b_end ? strtotime($b_end) : $b_start_time;

                    if ($booking_start_time < $b_end_time && $b_start_time < $booking_end_time) {
                        $active_booking_count++;
                    }
                } else {
                    $b_dur = get_post_meta($b->ID, '_obenlo_duration_mins', true);
                    if ($b_dur) {
                        $b_end_time = $b_start_time + ($b_dur * 60);
                        $proposed_end_time = $booking_start_time + ($duration_mins * 60);

                        if ($booking_start_time < $b_end_time && $proposed_end_time > $b_start_time) {
                            $active_booking_count++;
                        }
                    }
                }
            }

            if ($active_booking_count >= $available_units) {
                if ($available_units > 1) {
                    error_log("Obenlo Availability: Slot full. Count ($active_booking_count) >= Units ($available_units)");
                }
                obenlo_redirect_with_error('already_booked');
            }
        }

        // Create Booking Post (Pending)
        $booking_data = array(
            'post_title' => 'Booking for ' . $listing->post_title . ' - ' . current_time('mysql'),
            'post_status' => 'publish', // Or 'draft' pending payment
            'post_type' => 'booking',
            'post_author' => is_user_logged_in() ? get_current_user_id() : 0,
        );

        $booking_id = wp_insert_post($booking_data);

        if (is_wp_error($booking_id)) {
            obenlo_redirect_with_error('booking_error');
        }

        // Generate a unique confirmation code
        $confirmation_code = 'OB-' . strtoupper(substr(md5(uniqid($booking_id, true)), 0, 4)) . '-' . strtoupper(substr(md5(uniqid($listing_id, true)), 0, 4));

        // Guest ID Handling
        $guest_id_val = '';
        if (is_user_logged_in()) {
            $guest_id_val = self::get_user_guest_id(get_current_user_id());
        } else {
            // Visitor: Single use temporary Guest ID for this booking
            $guest_id_val = self::generate_guest_id();
        }

        // Save Meta
        update_post_meta($booking_id, '_obenlo_listing_id', $listing_id);
        update_post_meta($booking_id, '_obenlo_host_id', $listing->post_author);
        update_post_meta($booking_id, '_obenlo_start_date', $start_date);
        update_post_meta($booking_id, '_obenlo_end_date', $end_date);
        update_post_meta($booking_id, '_obenlo_guests', $guests);
        update_post_meta($booking_id, '_obenlo_total_price', $total_price);
        update_post_meta($booking_id, '_obenlo_payment_method', $payment_method);
        update_post_meta($booking_id, '_obenlo_booking_status', ($total_price <= 0) ? 'confirmed' : 'pending_payment');
        update_post_meta($booking_id, '_obenlo_confirmation_code', $confirmation_code);
        update_post_meta($booking_id, '_obenlo_guest_id', $guest_id_val);
        
        $payment_mode = get_option('obenlo_payment_mode', 'sandbox');
        update_post_meta($booking_id, '_obenlo_payment_mode', $payment_mode);

        if ($duration_mins > 0) {
            update_post_meta($booking_id, '_obenlo_duration_mins', $duration_mins);
        }

        // Notify for new booking request
        Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'new_booking');

        // Redirect to appropriate payment gateway
        if ($total_price <= 0) {
            // Free Booking: Skip payment and redirect to success modal
            wp_safe_redirect(add_query_arg('obenlo_modal', 'booking_confirmed', home_url()));
            exit;
        }

        if ($payment_method === 'stripe') {
            $this->process_stripe_checkout($booking_id, $total_price, $listing->post_title);
        }
        elseif ($payment_method === 'paypal') {
            $this->process_paypal_checkout($booking_id, $total_price, $listing->post_title);
        }
        elseif ($payment_method === 'moncash') {
            $this->process_moncash_checkout($booking_id, $total_price);
        }
        elseif ($payment_method === 'natcash') {
            $this->process_natcash_checkout($booking_id, $total_price);
        }
        else {
            obenlo_redirect_with_error('invalid_payment');
        }
    }

    /**
     * Handle the return from payment gateways (Stripe/PayPal)
     */
    public function handle_payment_return()
    {
        $status_updated = false;
        $booking_id = 0;

        // 1. Handle Stripe Return
        if (isset($_GET['obenlo_stripe_success']) && isset($_GET['session_id'])) {
            $booking_id = intval($_GET['obenlo_stripe_success']);
            $session_id = sanitize_text_field($_GET['session_id']);
            
            $stripe = new Obenlo_Booking_Stripe();
            if ($stripe->verify_checkout_session($session_id)) {
                $status_updated = true;
            } else {
                error_log('Obenlo Stripe Return Verification Failed for Booking #' . $booking_id);
                obenlo_redirect_with_error('booking_error');
            }
        }

        // 2. Handle PayPal Return
        if (isset($_GET['obenlo_paypal_return']) && isset($_GET['token'])) {
            $booking_id = intval($_GET['obenlo_paypal_return']);
            $order_id = sanitize_text_field($_GET['token']);
            
            $paypal = new Obenlo_Booking_PayPal();
            $capture = $paypal->capture_order($order_id);
            
            if ($capture === true) {
                $status_updated = true;
            } else {
                error_log('Obenlo PayPal Return Capture Failed for Booking #' . $booking_id);
                obenlo_redirect_with_error('booking_error');
            }
        }

        // 3. Handle MonCash Return
        if (isset($_GET['transactionId']) && isset($_GET['orderId'])) {
            $transaction_id = sanitize_text_field($_GET['transactionId']);
            $order_id_raw = sanitize_text_field($_GET['orderId']); // OB-ID-TIMESTAMP
            $parts = explode('-', $order_id_raw);
            $booking_id = isset($parts[1]) ? intval($parts[1]) : 0;

            if ($booking_id > 0) {
                $moncash = new Obenlo_Booking_MonCash();
                if ($moncash->verify_transaction($transaction_id)) {
                    $status_updated = true;
                    update_post_meta($booking_id, '_moncash_transaction_id', $transaction_id);
                } else {
                    error_log('Obenlo MonCash Return Verification Failed for Booking #' . $booking_id);
                    obenlo_redirect_with_error('booking_error');
                }
            }
        }

        // 4. Update Status and Redirect
        if ($status_updated && $booking_id > 0) {
            update_post_meta($booking_id, '_obenlo_booking_status', 'confirmed');
            
            // Trigger Notification
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed');

            // Calculate Earnings & Update Host Balance
            $this->calculate_booking_earnings($booking_id);

            wp_safe_redirect(add_query_arg('obenlo_modal', 'booking_confirmed', home_url()));
            exit;
        }
    }

    private function process_stripe_checkout($booking_id, $amount, $item_name)
    {
        $stripe = new Obenlo_Booking_Stripe();
        $checkout_url = $stripe->create_checkout_session($booking_id, $amount);

        if (is_wp_error($checkout_url)) {
            error_log('Obenlo Stripe Error: ' . $checkout_url->get_error_message());
            obenlo_redirect_with_error('booking_error', $checkout_url->get_error_message());
        }

        wp_redirect($checkout_url);
        exit;
    }

    private function process_moncash_checkout($booking_id, $amount_usd)
    {
        $rate = floatval(get_option('obenlo_htg_exchange_rate', '100'));
        $amount_htg = $amount_usd * $rate;

        $moncash = new Obenlo_Booking_MonCash();
        $checkout_url = $moncash->create_payment($booking_id, $amount_htg);

        if (is_wp_error($checkout_url)) {
            error_log('Obenlo MonCash Checkout Error: ' . $checkout_url->get_error_message());
            obenlo_redirect_with_error('booking_error', $checkout_url->get_error_message());
        }

        wp_redirect($checkout_url);
        exit;
    }

    private function process_natcash_checkout($booking_id, $amount_usd)
    {
        $rate = floatval(get_option('obenlo_htg_exchange_rate', '100'));
        $amount_htg = $amount_usd * $rate;

        $natcash = new Obenlo_Booking_Natcash();
        $checkout_url = $natcash->create_payment($booking_id, $amount_htg);

        if (is_wp_error($checkout_url)) {
            error_log('Obenlo Natcash Checkout Error: ' . $checkout_url->get_error_message());
            obenlo_redirect_with_error('booking_error');
        }

        wp_redirect($checkout_url);
        exit;
    }

    private function process_paypal_checkout($booking_id, $amount, $item_name)
    {
        $paypal = new Obenlo_Booking_PayPal();
        $checkout_url = $paypal->create_order($booking_id, $amount);

        if (is_wp_error($checkout_url)) {
            $error_msg = $checkout_url->get_error_message();
            error_log('Obenlo PayPal Error: ' . $error_msg);
            
            if (current_user_can('administrator')) {
                $l_id = get_post_meta($booking_id, '_obenlo_listing_id', true);
                $redirect_url = get_permalink($l_id);
                $redirect_url = add_query_arg('obenlo_debug', urlencode($error_msg), $redirect_url);
                wp_safe_redirect(add_query_arg('obenlo_error', 'booking_error', $redirect_url));
                exit;
            }

            obenlo_redirect_with_error('booking_error');
        }

        wp_redirect($checkout_url);
        exit;
    }

    public function calculate_platform_fee($booking_id)
    {
        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
        $total_price = floatval(get_post_meta($booking_id, '_obenlo_total_price', true));

        // 1. Check for host-specific override
        $fee_percentage = get_user_meta($host_id, '_obenlo_host_fee_percentage', true);

        // 2. Fallback to global setting if no override
        if ($fee_percentage === '' || $fee_percentage === false) {
            $fee_percentage = get_option('obenlo_global_platform_fee', '10');
        }

        $fee_percentage = floatval($fee_percentage);
        $fee_amount = ($total_price * $fee_percentage) / 100;

        // Save to booking meta
        update_post_meta($booking_id, '_obenlo_platform_fee_percentage', $fee_percentage);
        update_post_meta($booking_id, '_obenlo_platform_fee_amount', $fee_amount);

    }

    /**
     * Calculate and record earnings/commission for a booking
     */
    public function calculate_booking_earnings($booking_id)
    {
        $gross_total = floatval(get_post_meta($booking_id, '_obenlo_total_price', true));
        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);

        if (!$gross_total || !$host_id) {
            return;
        }

        // Get commission percentage (Host override first, otherwise Global)
        $commission_pct = get_user_meta($host_id, '_obenlo_host_fee_percentage', true);
        if ($commission_pct === '' || $commission_pct === false) {
            $commission_pct = get_option('obenlo_global_platform_fee', '10');
        }
        $commission_pct = floatval($commission_pct);

        $commission_amount = $gross_total * ($commission_pct / 100);
        $net_earnings = $gross_total - $commission_amount;

        // Record on booking
        update_post_meta($booking_id, '_obenlo_booking_commission_pct', $commission_pct);
        update_post_meta($booking_id, '_obenlo_booking_commission_amount', $commission_amount);
        update_post_meta($booking_id, '_obenlo_booking_net_earnings', $net_earnings);

        // Update Host Balance
        $current_balance = floatval(get_user_meta($host_id, '_obenlo_host_balance', true));
        $new_balance = $current_balance + $net_earnings;
        update_user_meta($host_id, '_obenlo_host_balance', $new_balance);

        error_log("Obenlo Earnings: Booking #$booking_id confirmed. Host #$host_id earnings updated: +$$net_earnings. Total balance: $$new_balance");
    }
}
