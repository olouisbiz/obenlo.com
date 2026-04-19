<?php
/**
 * Nightly Booking Engine (Stays)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Nightly extends Obenlo_Abstract_Engine {

    public function get_id() { return 'nightly'; }
    public function get_name() { return 'Nightly Stays'; }

    public function render_host_fields($listing_id, $slug = '') {
        $price = get_post_meta($listing_id, '_obenlo_price', true);
        $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);

        ob_start();
        ?>
        <div id="nightly_wrapper_modular">
            <p style="font-size:0.85rem; color:#666; margin-top:10px;">
                <?php echo __('This listing uses nightly stays. Guests select a check-in and check-out date.', 'obenlo'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);
        ob_start();
        ?>
        <div class="nightly-booking-fields">
            <div class="form-row" style="margin-bottom:15px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color:#666; margin-bottom: 5px;">CHECK-IN / CHECK-OUT</label>
                <div style="display: flex; border: 1px solid #ddd; border-radius: 12px; overflow: hidden; background:#fff;">
                    <input type="date" name="start_date" required id="start_date_input" style="border: none; padding: 12px; width: 50%; border-right: 1px solid #eee; font-size:0.9rem;" min="<?php echo date('Y-m-d'); ?>">
                    <input type="date" name="end_date" required id="end_date_input" style="border: none; padding: 12px; width: 50%; font-size:0.9rem;" min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:15px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color:#666; margin-bottom: 5px;">GUESTS <?php if($capacity) echo '(max '.$capacity.')'; ?></label>
                <input type="number" name="guests" value="1" min="1" <?php if($capacity) echo 'max="'.$capacity.'"'; ?> style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var start = document.getElementById('start_date_input');
            var end   = document.getElementById('end_date_input');
            if (start && end) {
                start.addEventListener('change', function() {
                    end.min = this.value;
                    if (end.value && new Date(end.value) < new Date(this.value)) {
                        end.value = this.value;
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        $price_per_night = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $start_date = isset($data['start_date']) ? $data['start_date'] : '';
        $end_date = isset($data['end_date']) ? $data['end_date'] : '';

        if (!$start_date || !$end_date) return $price_per_night;

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $start->diff($end);
        $nights = $diff->days;

        return $price_per_night * max(1, $nights);
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Price (Per Night)', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Capacity/Max Guests', 'obenlo')) . "';

            // Subcategory Customizations
            if (currentSlug === 'hotel') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Guests per Room', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Rate per Night / Room', 'obenlo')) . "';
            } else if (currentSlug === 'villa') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Total Guest Capacity', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Full Villa Rate (Per Night)', 'obenlo')) . "';
            } else if (currentSlug === 'boat') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Berths / Passengers', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Charter Rate (Per Night)', 'obenlo')) . "';
            } else if (currentSlug === 'apartment') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Monthly / Nightly Rate', 'obenlo')) . "';
            } else if (currentSlug === 'guest-house' || currentSlug === 'stay') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Stay Rate (Per Night)', 'obenlo')) . "';
            } else if (currentSlug === 'cabin' || currentSlug === 'cottage') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Cabin Rental (Per Night)', 'obenlo')) . "';
            }

            var pmWrapper = document.getElementById('pricing_model_wrapper');
            if (pmWrapper) pmWrapper.style.display = 'none';
            var pmSelect = document.getElementById('pricing_model');
            if (pmSelect) pmSelect.value = 'per_night';
            
            if (durationWrapper) durationWrapper.style.display = 'none';
            if (slotsWrapper)    slotsWrapper.style.display    = 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "
            if (bookingMode === 'nightly' || bookingMode === 'date_range') {
                var sInput = form.querySelector('input[name=\"start_date\"]');
                var eInput = form.querySelector('input[name=\"end_date\"]');
                if (sInput && eInput && sInput.value && eInput.value) {
                    var diffDays = Math.ceil((new Date(eInput.value) - new Date(sInput.value)) / 86400000);
                    if (diffDays > 0) total = basePrice * diffDays;
                }
            }
        ";
    }
}
