<?php
/**
 * Slot Booking Engine (Services)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Slot extends Obenlo_Abstract_Engine {

    public function get_id() { return 'slot'; }
    public function get_name() { return 'Time Slots'; }

    public function render_host_fields($listing_id, $slug = '') {
        ob_start();
        ?>
        <div id="slot_wrapper_modular">
            <p style="font-size:0.85rem; color:#666; margin-top:10px;">
                <?php echo __('This listing uses time slots. Perfect for haircuts, consulting, or any service with a specific duration.', 'obenlo'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $duration = get_post_meta($listing_id, '_obenlo_duration_val', true) ?: 1;
        $unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
        $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_session';
        $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);

        // Resolve the subcategory slug (prefer child term)
        $current_slug = $slug;
        if (!$current_slug) {
            $terms = get_the_terms($listing_id, 'listing_type');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    if ($t->parent != 0) { $current_slug = $t->slug; break; }
                }
                if (!$current_slug) $current_slug = $terms[0]->slug ?? '';
            }
        }

        $mobile_slugs = ['babysitter','dogsitter','cleaning','handyman','cook',
                         'hairdresser','barber','massage','beauty','service','personal-trainer'];
        $is_mobile = in_array($current_slug, $mobile_slugs, true);

        $location_labels = [
            'babysitter'       => ['Your Home Address',         'Where do you need the babysitter?'],
            'dogsitter'        => ['Pickup Address',             'Where should we pick up your dog?'],
            'cleaning'         => ['Property Address',           'Address of the property to be cleaned'],
            'handyman'         => ['Job Site Address',           'Where is the work to be done?'],
            'cook'             => ['Your Kitchen Address',       'Where will the meal be prepared?'],
            'hairdresser'      => ['Your Location',             'Home visit address (if applicable)'],
            'barber'           => ['Your Location',             'Home visit address (if applicable)'],
            'massage'          => ['Your Address',              'Where would you like the massage?'],
            'beauty'           => ['Your Address',              'Where would you like the service?'],
            'personal-trainer' => ['Training Location',         'Home, gym, or park address'],
        ];
        $loc_label = $location_labels[$current_slug][0] ?? 'Your Address';
        $loc_hint  = $location_labels[$current_slug][1] ?? 'Enter the service address';

        ob_start();
        ?>
        <div class="slot-booking-fields">
            <?php if ($is_mobile): ?>
            <div style="margin-bottom:15px; padding:12px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#0c4a6e; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;"><?php echo esc_html($loc_label); ?></label>
                <input type="text" name="logistics_pickup" id="logistics_pickup_input" required
                    placeholder="<?php echo esc_attr($loc_hint); ?>"
                    style="width:100%; padding:12px; border:1px solid #bae6fd; border-radius:8px; box-sizing:border-box;">
                <input type="hidden" name="logistics_dropoff" value="N/A">
            </div>
            <?php endif; ?>

            <?php if ($capacity && $capacity > 1): ?>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">GUESTS / CHILDREN (Max <?php echo esc_html($capacity); ?>)</label>
                <input type="number" name="guests" value="1" min="1" max="<?php echo esc_attr($capacity); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
            </div>
            <?php else: ?>
                <input type="hidden" name="guests" value="1">
            <?php endif; ?>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">SERVICE DATE</label>
                <input type="date" name="slot_date" id="timeslot_core_date" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;" min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div id="slot-picker-container" style="display:none; margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:8px;">AVAILABLE TIMES</label>
                <div id="obenlo-time-slots" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <!-- Slots loaded via JS -->
                </div>
                <div id="slot_loading_spinner" style="display:none; font-size:0.8rem; color:#888;">Checking availability...</div>
            </div>

            <?php 
            $show_duration = ($pricing_model === 'per_hour' || $is_mobile);
            if ($show_duration): ?>
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">DURATION (HOURS)</label>
                    <input type="number" name="booking_duration" value="<?php echo esc_attr($duration); ?>" min="0.5" step="0.5" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
                </div>
            <?php else: ?>
                <input type="hidden" name="booking_duration" value="<?php echo esc_attr($duration); ?>">
            <?php endif; ?>

            <input type="hidden" name="start_date" id="final_slot_start_date">
            <input type="hidden" name="booking_duration_unit" value="<?php echo esc_attr($unit); ?>">
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInp = document.getElementById('timeslot_core_date');
            var container = document.getElementById('slot-picker-container');
            var grid = document.getElementById('obenlo-time-slots');
            var spinner = document.getElementById('slot_loading_spinner');
            var finalStart = document.getElementById('final_slot_start_date');

            if (dateInp) {
                dateInp.addEventListener('change', function() {
                    var dateVal = this.value;
                    if (!dateVal) { container.style.display = 'none'; return; }

                    container.style.display = 'block';
                    grid.innerHTML = '';
                    spinner.style.display = 'block';

                    var data = new FormData();
                    data.append('action', 'obenlo_get_timeslots');
                    data.append('listing_id', <?php echo $listing_id; ?>);
                    data.append('date', dateVal);

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: data })
                        .then(res => res.json())
                        .then(response => {
                            spinner.style.display = 'none';
                            if (response.success && response.data.slots) {
                                response.data.slots.forEach(slot => {
                                    var btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'slot-btn';
                                    btn.innerText = slot.label;
                                    btn.style.padding = '10px'; btn.style.border = '1px solid #ddd'; btn.style.borderRadius = '8px'; btn.style.background = '#fff'; btn.style.cursor = 'pointer';
                                    btn.addEventListener('click', function() {
                                        grid.querySelectorAll('.slot-btn').forEach(b => { b.style.background = '#fff'; b.style.color = '#333'; b.style.borderColor = '#ddd'; });
                                        this.style.background = '#e61e4d'; this.style.color = '#fff'; this.style.borderColor = '#e61e4d';
                                        finalStart.value = dateVal + 'T' + slot.time;
                                    });
                                    grid.appendChild(btn);
                                });
                            } else {
                                grid.innerHTML = '<div style="grid-column:1/-1; font-size:0.8rem; color:#e61e4d;">' + (response.data.message || 'No slots available.') + '</div>';
                            }
                        });
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        $price = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_session';
        
        $mobile_slugs = ['babysitter','handyman','cleaning','cook','massage','personal-trainer'];
        $is_mobile = in_array($slug, $mobile_slugs, true);

        if ($pricing_model === 'per_hour' || ($is_mobile && isset($data['booking_duration']))) {
            $duration = isset($data['booking_duration']) ? floatval($data['booking_duration']) : 1;
            return $price * $duration;
        }
        
        return $price;
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Price (Per Hour/Session)', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Max Clients per Slot', 'obenlo')) . "';
            
            // Subcategory Customizations
            if (currentSlug === 'babysitter') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Kids at once', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Hourly Babysitting Rate', 'obenlo')) . "';
            } else if (currentSlug === 'dogsitter') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Dogs Allowed', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Daily Boarding Rate', 'obenlo')) . "';
            } else if (currentSlug === 'cleaning') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Standard Cleaning Fee', 'obenlo')) . "';
            } else if (currentSlug === 'handyman') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Hourly Labor Rate', 'obenlo')) . "';
            } else if (currentSlug === 'hairdresser' || currentSlug === 'barber') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Service Starting Price', 'obenlo')) . "';
                if (capLabel)   capLabel.innerText   = '" . esc_js(__('Max Clients at once', 'obenlo')) . "';
            } else if (currentSlug === 'personal-trainer') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Rate per PT Session', 'obenlo')) . "';
            } else if (currentSlug === 'massage') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Massage Rate (Hourly)', 'obenlo')) . "';
            } else if (currentSlug === 'cook') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Booking Fee / Hourly Rate', 'obenlo')) . "';
            }

            if (pricingModel) {
                 var allowed = ['per_session','per_hour','flat_fee','inquiry_only'];
                 // Smart default for hourly-based services
                 if (['babysitter','handyman','cleaning','cook','massage','personal-trainer'].includes(currentSlug)) {
                     if (!pricingModel.value || pricingModel.value === 'per_session') {
                         pricingModel.value = 'per_hour';
                     }
                 }
                 if (!allowed.includes(pricingModel.value)) { pricingModel.value = 'per_session'; }
                 Array.from(pricingModel.options).forEach(function(opt) { if (!allowed.includes(opt.value)) { opt.hidden = true; opt.disabled = true; } });
            }
            if (durationWrapper) durationWrapper.style.display = 'flex';
            if (slotsWrapper)    slotsWrapper.style.display    = 'block';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        $model = get_post_meta($listing_id, '_obenlo_pricing_model', true);
        return "
            if (bookingMode === 'slot' || bookingMode === 'timeslot' || bookingMode === 'datetime') {
                var model = \"$model\";
                if (model === 'per_hour') {
                    var dur = form.querySelector('input[name=\"booking_duration\"]');
                    var hrs = dur ? parseFloat(dur.value) || 1 : 1;
                    total = basePrice * hrs;
                }
            }
        ";
    }
}
