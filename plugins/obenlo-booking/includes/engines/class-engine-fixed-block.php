<?php
/**
 * Fixed Block Booking Engine (Experiences/Tours)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Fixed_Block extends Obenlo_Abstract_Engine {

    public function get_id() { return 'fixed_block'; }
    public function get_name() { return 'Fixed Blocks / Tours'; }

    public function render_host_fields($listing_id, $slug = '') {
        $runs_json = get_post_meta($listing_id, '_obenlo_session_runs', true);
        $session_runs = !empty($runs_json) ? json_decode($runs_json, true) : array();
        ob_start();
        ?>
        <div id="fixed_block_wrapper_modular" style="margin-top:25px; padding:20px; background:#f9fafb; border-radius:12px; border:1px solid #e5e7eb;">
            <h5 style="margin-top:0; margin-bottom:10px; color:#333; font-weight:700;"><?php echo __('Tour / Experience Schedule', 'obenlo'); ?></h5>
            <p style="font-size:0.85rem; color:#666; margin-bottom:20px;">
                <?php echo __('Define the recurring days and times this tour is available.', 'obenlo'); ?>
            </p>

            <div id="session-runs-container">
                <?php if (!empty($session_runs)): ?>
                    <?php foreach ($session_runs as $run): ?>
                        <div class="session-run-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                            <select name="run_days[]" style="flex:1.5; padding:10px; border:1px solid #ddd; border-radius:8px; background:#fff;">
                                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday','Daily'] as $day): ?>
                                    <option value="<?php echo esc_attr($day); ?>" <?php selected($run['day'], $day); ?>><?php echo esc_html($day); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="time" name="run_starts[]" value="<?php echo esc_attr($run['start']); ?>" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            <span style="color:#888;">to</span>
                            <input type="time" name="run_ends[]" value="<?php echo esc_attr($run['end']); ?>" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            <button type="button" class="remove-run-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800; height:40px;">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-run-btn" style="background:#fff; border:1px dashed #ccc; color:#666; width:100%; padding:12px; border-radius:10px; cursor:pointer; font-weight:600; margin-top:10px;">+ <?php echo __('Add Recurring Time', 'obenlo'); ?></button>

            <template id="run-template">
                <div class="session-run-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                    <select name="run_days[]" style="flex:1.5; padding:10px; border:1px solid #ddd; border-radius:8px; background:#fff;">
                        <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option><option value="Sunday">Sunday</option><option value="Daily">Daily</option>
                    </select>
                    <input type="time" name="run_starts[]" value="" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <span style="color:#888;">to</span>
                    <input type="time" name="run_ends[]" value="" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <button type="button" class="remove-run-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800; height:40px;">&times;</button>
                </div>
            </template>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $runs_json = get_post_meta($listing_id, '_obenlo_session_runs', true);
        $runs = !empty($runs_json) ? json_decode($runs_json, true) : array();
        $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);

        ob_start();
        ?>
        <div class="fixed-block-booking-fields">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">SELECT DATE</label>
                <input type="date" name="fixed_block_date" id="fixed_block_date" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;" min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div id="experience-runs-selector" style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:8px;">AVAILABLE TIMES</label>
                <div id="obenlo-runs-list" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <?php if (!empty($runs)): ?>
                        <?php foreach ($runs as $idx => $run): 
                            $run_day = is_array($run) ? $run['day'] : 'Daily';
                            $start_time = is_array($run) ? $run['start'] : $run;
                            $end_time = is_array($run) ? $run['end'] : '';
                            ?>
                            <label class="run-selector" data-day="<?php echo esc_attr($run_day); ?>" style="display:none; padding:12px; border:1px solid #ddd; background:#fff; border-radius:10px; text-align:center; cursor:pointer; transition:0.2s;">
                                <input type="radio" name="fixed_time" value="<?php echo esc_attr($start_time); ?>" required style="display:none;">
                                <div style="font-weight:bold; font-size:0.95rem;">
                                    <?php echo date('g:i A', strtotime($start_time)); ?>
                                    <?php if($end_time) echo ' - ' . date('g:i A', strtotime($end_time)); ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:0.8rem; color:#999; grid-column: 1/-1;">No sessions scheduled.</p>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="start_date" id="final_fixed_start_date">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">GUESTS / TICKETS <?php if($capacity) echo '(max '.$capacity.')'; ?></label>
                <input type="number" name="guests" value="1" min="1" <?php if($capacity) echo 'max="'.$capacity.'"'; ?> style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInp = document.getElementById('fixed_block_date');
            var runs = document.querySelectorAll('.run-selector');
            var finalStart = document.getElementById('final_fixed_start_date');
            var form = dateInp ? dateInp.closest('form') : null;

            if (dateInp) {
                dateInp.addEventListener('change', function() {
                    var dateVal = this.value;
                    if (!dateVal) return;
                    var dayName = new Date(dateVal + 'T00:00:00').toLocaleDateString('en-US', {weekday: 'long'});
                    
                    runs.forEach(function(r) {
                        var day = r.getAttribute('data-day');
                        if (day === 'Daily' || day === dayName) {
                            r.style.display = 'block';
                        } else {
                            r.style.display = 'none';
                            r.querySelector('input').checked = false;
                        }
                    });
                });

                if (form) {
                    form.addEventListener('submit', function() {
                        var checkedTime = form.querySelector('input[name="fixed_time"]:checked');
                        if (checkedTime && dateInp.value) {
                            finalStart.value = dateInp.value + 'T' + checkedTime.value;
                        }
                    });
                }
            }

            // Radio button styling
            runs.forEach(function(r) {
                r.addEventListener('click', function() {
                    runs.forEach(el => { el.style.borderColor = '#ddd'; el.style.background = '#fff'; el.style.color = '#333'; });
                    this.style.borderColor = '#e61e4d';
                    this.style.background = '#fff1f2';
                    this.style.color = '#e61e4d';
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        $price = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $guests = isset($data['guests']) ? max(1, intval($data['guests'])) : 1;
        return $price * $guests;
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Price (Per Person/Ticket)', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Max Participants per Group', 'obenlo')) . "';

            // Subcategory Customizations
            if (currentSlug === 'tour') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Tour Fee (Per Person)', 'obenlo')) . "';
            } else if (currentSlug === 'photo-shoot' || currentSlug === 'photography') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Photo Session Rate', 'obenlo')) . "';
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Participants in Shoot', 'obenlo')) . "';
            } else if (currentSlug === 'food-tasting') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Tasting Experience Price', 'obenlo')) . "';
            }

            if (pricingModel) {
                 var allowed = ['per_person', 'per_event', 'flat_fee'];
                 if (!allowed.includes(pricingModel.value)) { pricingModel.value = 'per_person'; }
                 Array.from(pricingModel.options).forEach(function(opt) { if (!allowed.includes(opt.value)) { opt.hidden = true; opt.disabled = true; } });
            }
            if (durationWrapper)    durationWrapper.style.display    = 'none';
            if (slotsWrapper)       slotsWrapper.style.display       = 'none';
            if (runsWrapper)        runsWrapper.style.display        = isChild ? 'block' : 'none';
            if (genericLocWrapper)  genericLocWrapper.style.display  = isChild ? 'none'  : 'block';
            if (eventConfigWrapper) eventConfigWrapper.style.display = isChild ? 'block' : 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "
            if (bookingMode === 'fixed_block') {
                var g = form.querySelector('input[name=\"guests\"]');
                var qty = (g && g.value) ? parseInt(g.value) || 1 : 1;
                total = basePrice * qty;
            }
        ";
    }
}
