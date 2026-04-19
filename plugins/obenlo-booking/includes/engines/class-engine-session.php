<?php
/**
 * Session Booking Engine (Events/Classes)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Session extends Obenlo_Abstract_Engine {

    public function get_id() { return 'session'; }
    public function get_name() { return 'Events / Sessions'; }

    public function render_host_fields($listing_id, $slug = '') {
        $runs_json = get_post_meta($listing_id, '_obenlo_session_runs', true);
        $session_runs = !empty($runs_json) ? json_decode($runs_json, true) : array();
        ob_start();
        ?>
        <div id="session_wrapper_modular" style="margin-top:25px; padding:20px; background:#f9fafb; border-radius:12px; border:1px solid #e5e7eb;">
            <h5 style="margin-top:0; margin-bottom:10px; color:#333; font-weight:700;"><?php echo __('Recurring Event Schedule', 'obenlo'); ?></h5>
            <p style="font-size:0.85rem; color:#666; margin-bottom:20px;">
                <?php echo __('Optional: If this event repeats (e.g. Every Friday), add your times here. Otherwise, use the "Specific scheduled time" above.', 'obenlo'); ?>
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
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        ob_start();
        ?>
        <div class="session-booking-fields">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">EVENT DATE</label>
                <input type="text" name="start_date" class="datepicker-trigger" placeholder="Select event date" readonly style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:5px;">NO. OF TICKETS</label>
                <input type="number" name="guests" value="1" min="1" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        $price = floatval(get_post_meta($listing_id, '_obenlo_price', true));
        $guests = isset($data['guests']) ? max(1, intval($data['guests'])) : 1;
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_event';
        
        if ($pricing_model === 'per_person') {
            return $price * $guests;
        }
        
        return $price;
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Price (Per Ticket/Entry)', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Total Tickets Available', 'obenlo')) . "';

            // Subcategory Customizations
            if (currentSlug === 'class') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Course Fee (Per Student)', 'obenlo')) . "';
                if (capLabel) capLabel.innerText = '" . esc_js(__('Max Class Size', 'obenlo')) . "';
            } else if (currentSlug === 'donation' || currentSlug === 'donation-giving') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Recommended Donation', 'obenlo')) . "';
                if (capLabel) capLabel.innerText = '" . esc_js(__('Goal / Capacity', 'obenlo')) . "';
            } else if (currentSlug === 'show' || currentSlug === 'ticket') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Ticket Price', 'obenlo')) . "';
            }

            if (pricingModel) {
                 pricingModel.value = 'per_event';
                 Array.from(pricingModel.options).forEach(function(opt) { if (!['per_event','per_person','per_donation','custom_donation','flat_fee'].includes(opt.value)) { opt.hidden = true; opt.disabled = true; } });
            }
            if (durationWrapper)     durationWrapper.style.display     = 'none';
            if (slotsWrapper)        slotsWrapper.style.display        = 'none';
            if (runsWrapper)         runsWrapper.style.display         = isChild ? 'block' : 'none';
            if (eventConfigWrapper)  eventConfigWrapper.style.display  = isChild ? 'block' : 'none';
            if (genericLocWrapper)   genericLocWrapper.style.display   = isChild ? 'none'  : 'block';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "";
    }
}
