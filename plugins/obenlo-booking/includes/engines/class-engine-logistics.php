<?php
/**
 * Logistics Booking Engine (Delivery/Chauffeur)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Logistics extends Obenlo_Abstract_Engine {

    public function get_id() { return 'logistics'; }
    public function get_name() { return 'Logistics / Delivery'; }

    public function render_host_fields($listing_id, $slug = '') {
        $mile_rate = get_post_meta($listing_id, '_obenlo_logistics_mile_rate', true);
        $flat_price = get_post_meta($listing_id, '_obenlo_logistics_flat_price', true);
        $flat_miles = get_post_meta($listing_id, '_obenlo_logistics_flat_miles', true);
        $flat_mins  = get_post_meta($listing_id, '_obenlo_logistics_flat_mins', true);

        ob_start();
        ?>
        <div id="logistics_wrapper_modular" style="padding:15px; background:#f0f9ff; border-radius:12px; border:1px solid #bae6fd; margin-top:15px;">
            <h4 style="margin-top:0; color:#0369a1; font-size:1rem; display:flex; align-items:center; gap:8px;">
                <span>🚚</span> Logistics Settings
            </h4>
            <div style="display:grid; grid-template-columns:1fr; gap:15px;">
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#0369a1; margin-bottom:5px;">Price Per Mile ($)</label>
                    <input type="number" name="logistics_mile_rate" step="0.01" value="<?php echo esc_attr($mile_rate); ?>" style="width:100%; border:1px solid #bae6fd; border-radius:8px; padding:10px;">
                    <p style="font-size:0.75rem; color:#0369a1; margin-top:4px;">Uses the "Base Delivery Fee" (top of page) + this Mile Rate.</p>
                </div>
            </div>
            
            <div style="margin-top:15px; padding-top:15px; border-top:1px solid #bae6fd;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#0369a1; margin:0 0 10px 0;">Advanced Pricing (Flat Fees)</label>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                    <div>
                        <label style="font-size:0.7rem; color:#666;">Flat Fee ($)</label>
                        <input type="number" name="logistics_flat_price" step="0.01" value="<?php echo esc_attr($flat_price); ?>" placeholder="e.g. 20" style="width:100%; border:1px solid #bae6fd; border-radius:8px; padding:8px;">
                    </div>
                    <div>
                        <label style="font-size:0.7rem; color:#666;">If Under Miles</label>
                        <input type="number" name="logistics_flat_miles" step="0.1" value="<?php echo esc_attr($flat_miles); ?>" placeholder="e.g. 15" style="width:100%; border:1px solid #bae6fd; border-radius:8px; padding:8px;">
                    </div>
                    <div>
                        <label style="font-size:0.7rem; color:#666;">Or Under Mins</label>
                        <input type="number" name="logistics_flat_mins" step="1" value="<?php echo esc_attr($flat_mins); ?>" placeholder="e.g. 30" style="width:100%; border:1px solid #bae6fd; border-radius:8px; padding:8px;">
                    </div>
                </div>
                <p style="font-size:0.75rem; color:#0369a1; margin-top:8px;">If distance/time is below these values, the system will use the Flat Fee instead of the per-mile calculation.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        // Resolve the subcategory slug (prefer child term, then passed slug)
        $current_slug = $slug;
        if (!$current_slug) {
            $terms = get_the_terms($listing_id, 'listing_type');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    if ($t->parent != 0) { $current_slug = $t->slug; break; }
                }
                if (!$current_slug && !empty($terms)) $current_slug = $terms[0]->slug;
            }
        }

        $pickup_labels = [
            'delivery'  => ['Sender / Pickup Address',  'Where should we collect from?'],
            'chauffeur' => ['Pickup Address',            'Where do you need to be picked up?'],
            'driver'    => ['Pickup Address',            'Where do you need to be picked up?'],
            'moving'    => ['Current Address',           'Where are you moving from?'],
            'towing'    => ['Vehicle Location',          'Where is the vehicle to be towed?'],
            'shipping'  => ['Sender Address',            'Where should we collect the package?'],
            'transport' => ['Start Address / Port',      'Departure point or port of origin'],
        ];
        $dropoff_labels = [
            'delivery'  => 'Delivery / Drop-off Address',
            'chauffeur' => 'Drop-off Destination',
            'driver'    => 'Drop-off Destination',
            'moving'    => 'Destination Address',
            'towing'    => 'Drop-off Location',
            'shipping'  => 'Recipient Address',
            'transport' => 'Destination / Port',
        ];

        $date_labels = [
            'delivery'  => ['Delivery Date',          'Pickup Date & Time'],
            'chauffeur' => ['Pickup Date & Time',      'Pickup Date & Time'],
            'driver'    => ['Pickup Date & Time',      'Pickup Date & Time'],
            'moving'    => ['Moving Date',             'When do you need to move?'],
            'towing'    => ['Service Date',            'When do you need towing?'],
            'shipping'  => ['Ship-by Date',            'When should we collect?'],
            'transport' => ['Departure Date & Time',   'Departure date'],
        ];
        $date_label = $date_labels[$current_slug][0] ?? 'Scheduled Date';

        $pickup_label       = $pickup_labels[$current_slug][0] ?? 'Pickup / Start Address';
        $pickup_placeholder = $pickup_labels[$current_slug][1] ?? 'Enter pickup address...';
        $dropoff_label      = $dropoff_labels[$current_slug]   ?? 'Drop-off / Destination';

        ob_start();
        ?>
        <div class="logistics-booking-fields" style="background:#f0f9ff; padding:15px; border-radius:12px; border:1px solid #bae6fd; margin-bottom:20px;">
            <div style="font-weight:800; color:#0369a1; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                <span>🚚</span> Route Details
            </div>

            <!-- Date & Time -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#0c4a6e; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.04em;">
                        <?php echo esc_html($date_label); ?>
                    </label>
                    <input type="date" name="logistics_date" id="logistics_date_input" required
                        min="<?php echo date('Y-m-d'); ?>"
                        style="width:100%; padding:11px; border:1px solid #bae6fd; border-radius:8px; box-sizing:border-box; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#0c4a6e; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.04em;">
                        Preferred Time
                    </label>
                    <input type="time" name="logistics_time" id="logistics_time_input"
                        style="width:100%; padding:11px; border:1px solid #bae6fd; border-radius:8px; box-sizing:border-box; font-size:0.9rem;">
                </div>
            </div>
            <!-- Hidden start_date combines date + time for payment handler -->
            <input type="hidden" name="start_date" id="logistics_start_date_combined">
            
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#0c4a6e; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.04em;">
                    <?php echo esc_html($pickup_label); ?>
                </label>
                <input type="text" name="logistics_pickup" id="logistics_pickup_input" required
                    placeholder="<?php echo esc_attr($pickup_placeholder); ?>"
                    style="width:100%; padding:12px; border:1px solid #bae6fd; border-radius:8px; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#0c4a6e; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.04em;">
                    <?php echo esc_html($dropoff_label); ?>
                </label>
                <input type="text" name="logistics_dropoff" id="logistics_dropoff_input" required
                    placeholder="Enter destination..."
                    style="width:100%; padding:12px; border:1px solid #bae6fd; border-radius:8px; box-sizing:border-box;">
            </div>

            <div id="live-distance-meta" style="display:none; text-align:right; font-size:0.8rem; color:#0369a1; font-weight:bold; margin-top:5px;">
                Distance: <span id="logis-dist-val">0</span> mi | Time: <span id="logis-time-val">0</span> min
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInp = document.getElementById('logistics_date_input');
            var timeInp = document.getElementById('logistics_time_input');
            var combined = document.getElementById('logistics_start_date_combined');
            function syncDateTime() {
                if (dateInp && combined) {
                    var t = timeInp && timeInp.value ? timeInp.value : '09:00';
                    combined.value = dateInp.value ? dateInp.value + 'T' + t : '';
                }
            }
            if (dateInp) dateInp.addEventListener('change', syncDateTime);
            if (timeInp) timeInp.addEventListener('change', syncDateTime);
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        $mile_rate = floatval(get_post_meta($listing_id, '_obenlo_logistics_mile_rate', true));
        $base_fee  = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $flat_price = floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_price', true));
        $flat_miles = floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_miles', true));
        $flat_mins  = floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_mins', true));

        $pickup = isset($data['logistics_pickup']) ? sanitize_text_field($data['logistics_pickup']) : '';
        $dropoff = isset($data['logistics_dropoff']) ? sanitize_text_field($data['logistics_dropoff']) : '';

        if (!$pickup || !$dropoff || $dropoff === 'N/A') return $base_fee;

        // Remote calculation via API Fallback
        $distance_info = obenlo_calculate_distance_osrm($pickup, $dropoff);
        $distance_miles = $distance_info['distance_miles'];
        $duration_mins = $distance_info['duration_mins'];

        $total = $base_fee + ($distance_miles * $mile_rate);

        // Flat Fee logic
        if ($flat_price > 0) {
            if (($flat_miles > 0 && $distance_miles <= $flat_miles) || ($flat_mins > 0 && $duration_mins <= $flat_mins)) {
                $total = $flat_price;
            }
        }

        return $total;
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Price (Base Rate)', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Max Capacity', 'obenlo')) . "';

            // Subcategory Customizations
            if (currentSlug === 'delivery') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Cargo Weight/Size', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Base Delivery Fee', 'obenlo')) . "';
            } else if (currentSlug === 'chauffeur' || currentSlug === 'driver') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Passengers', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Base Booking Fee', 'obenlo')) . "';
            } else if (currentSlug === 'moving') {
                if (capLabel) capLabel.innerText = '" . esc_js(__('Truck Size / Volume', 'obenlo')) . "';
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Mobilization Fee', 'obenlo')) . "';
            } else if (currentSlug === 'shipping') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Standard Shipping Rate', 'obenlo')) . "';
            } else if (currentSlug === 'towing') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Hook-up / Base Fee', 'obenlo')) . "';
            } else if (currentSlug === 'transport') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Base Fare / Booking Fee', 'obenlo')) . "';
            }

            logisWrapper.style.display = isChild ? 'block' : 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "
            var pickupInp = document.getElementById('logistics_pickup_input');
            var dropoffInp = document.getElementById('logistics_dropoff_input');
            var logisInfo = document.getElementById('logistics-info');
            var logisDist = document.getElementById('logistics-distance-val');
            var logisTime = document.getElementById('logistics-time-val');
            
            var mileRate = " . floatval(get_post_meta($listing_id, '_obenlo_logistics_mile_rate', true)) . ";
            var logisBase = basePrice;
            var flatPrice = " . floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_price', true)) . ";
            var flatMiles = " . floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_miles', true)) . ";
            var flatMins  = " . floatval(get_post_meta($listing_id, '_obenlo_logistics_flat_mins', true)) . ";
            
            let debounceTimer;
            function recalcLogisticsRoute() {
                var p = pickupInp && pickupInp.value ? pickupInp.value : '';
                var d = dropoffInp && dropoffInp.value ? dropoffInp.value : '';
                if (!p || !d || d === 'N/A') return;
                
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (liveTotalEl) liveTotalEl.innerText = 'Calc...';
                    
                    fetch('" . admin_url('admin-ajax.php') . "?action=obenlo_calc_route&pickup='+encodeURIComponent(p)+'&dropoff='+encodeURIComponent(d))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            var distMiles = parseFloat(data.data.distance_miles);
                            var distMins = parseFloat(data.data.duration_mins);
                            
                            if (logisInfo) {
                                logisInfo.style.display = 'block';
                                if (logisDist) logisDist.innerText = distMiles.toFixed(1) + ' mi';
                                if (logisTime) logisTime.innerText = distMins.toFixed(0) + ' min';
                            }
                            
                            var calcTotal = logisBase + (distMiles * mileRate);
                            if (flatPrice > 0 && ((flatMiles > 0 && distMiles <= flatMiles) || (flatMins > 0 && distMins <= flatMins))) {
                                calcTotal = flatPrice;
                            }
                            
                            total = calcTotal;
                            // Addons
                            form.querySelectorAll('.addon-checkbox:checked').forEach(function(cb) {
                                total += parseFloat(cb.getAttribute('data-price')) || 0;
                            });
                            if (liveTotalEl) liveTotalEl.innerText = total.toFixed(2);
                        } else {
                            if (liveTotalEl) liveTotalEl.innerText = logisBase.toFixed(2);
                        }
                    }).catch(err => {
                        if (liveTotalEl) liveTotalEl.innerText = logisBase.toFixed(2);
                    });
                }, 1200);
            }
            
            if (pickupInp) pickupInp.addEventListener('input', recalcLogisticsRoute);
            if (dropoffInp) dropoffInp.addEventListener('input', recalcLogisticsRoute);
        ";
    }
}

// Ensure the helper function exists globally
if (!function_exists('obenlo_calculate_distance_osrm')) {
    function obenlo_calculate_distance_osrm($pickup, $dropoff) {
        $result = ['distance_miles' => 0, 'duration_mins' => 0];
        
        $pickup = urlencode($pickup);
        $dropoff = urlencode($dropoff);
        
        $p_res = wp_remote_get("https://nominatim.openstreetmap.org/search?format=json&q={$pickup}&limit=1");
        $d_res = wp_remote_get("https://nominatim.openstreetmap.org/search?format=json&q={$dropoff}&limit=1");
        
        if (is_wp_error($p_res) || is_wp_error($d_res)) return $result;
        
        $p_body = json_decode(wp_remote_retrieve_body($p_res), true);
        $d_body = json_decode(wp_remote_retrieve_body($d_res), true);
        
        if (empty($p_body) || empty($d_body)) return $result;
        
        $p_lat = $p_body[0]['lat'];
        $p_lon = $p_body[0]['lon'];
        $d_lat = $d_body[0]['lat'];
        $d_lon = $d_body[0]['lon'];
        
        $osrm_res = wp_remote_get("https://router.project-osrm.org/route/v1/driving/{$p_lon},{$p_lat};{$d_lon},{$d_lat}?overview=false");
        
        if (is_wp_error($osrm_res)) return $result;
        $osrm_body = json_decode(wp_remote_retrieve_body($osrm_res), true);
        
        if (!empty($osrm_body['routes'][0])) {
            $meters = $osrm_body['routes'][0]['distance'];
            $seconds = $osrm_body['routes'][0]['duration'];
            $result['distance_miles'] = round($meters * 0.000621371, 1);
            $result['duration_mins'] = round($seconds / 60, 0);
        }
        
        return $result;
    }
}

// Add the AJAX endpoint
add_action('wp_ajax_obenlo_calc_route', 'obenlo_ajax_calc_route');
add_action('wp_ajax_nopriv_obenlo_calc_route', 'obenlo_ajax_calc_route');
function obenlo_ajax_calc_route() {
    $pickup = sanitize_text_field($_GET['pickup']);
    $dropoff = sanitize_text_field($_GET['dropoff']);
    
    if (!$pickup || !$dropoff) {
        wp_send_json_error('Missing data');
    }
    
    $res = obenlo_calculate_distance_osrm($pickup, $dropoff);
    wp_send_json_success($res);
}
