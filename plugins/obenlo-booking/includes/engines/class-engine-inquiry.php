<?php
/**
 * Inquiry Booking Engine (Premium Services/Quotes)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Inquiry extends Obenlo_Abstract_Engine {

    public function get_id() { return 'inquiry'; }
    public function get_name() { return 'Inquiry / Quote'; }

    public function render_host_fields($listing_id, $slug = '') {
        ob_start();
        ?>
        <div id="inquiry_wrapper_modular">
            <p style="font-size:0.85rem; color:#666; margin-top:10px;">
                <?php echo __('This listing does not have instant booking. Guests will send an inquiry to get a custom quote.', 'obenlo'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        ob_start();
        ?>
        <div class="inquiry-booking-fields">
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#666; margin-bottom:8px;">DESCRIBE YOUR REQUEST</label>
                <textarea name="inquiry_message" rows="4" placeholder="Tell the host about your specific needs..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:12px;"></textarea>
            </div>
            
            <p style="font-size:0.8rem; color:#888; text-align:center;">
                <?php echo __('The host will review your request and send a tailored offer.', 'obenlo'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        return floatval(get_post_meta($listing_id, '_obenlo_price', true));
    }

    public function get_host_js_logic($slug = '') {
        return "
            if (priceLabel) priceLabel.innerText = '" . esc_js(__('Consultation / Base Fee', 'obenlo')) . "';
            if (capLabel)   capLabel.innerText   = '" . esc_js(__('Capacity / Availability', 'obenlo')) . "';

            // Subcategory Customizations
            if (currentSlug === 'photographer') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Shoot Base Rate / Deposit', 'obenlo')) . "';
            } else if (currentSlug === 'concierge') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Service / Consultation Fee', 'obenlo')) . "';
            } else if (currentSlug === 'personal-assistant') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Retainer / Hourly Rate', 'obenlo')) . "';
            } else if (currentSlug === 'freelance') {
                if (priceLabel) priceLabel.innerText = '" . esc_js(__('Project Starting Rate', 'obenlo')) . "';
            }

            if (pricingModel) {
                 pricingModel.value = 'inquiry_only';
                 Array.from(pricingModel.options).forEach(function(opt) { if (!['inquiry_only','flat_fee'].includes(opt.value)) { opt.hidden = true; opt.disabled = true; } });
            }
            if (slotsWrapper)    slotsWrapper.style.display    = 'none';
            if (durationWrapper) durationWrapper.style.display = 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "";
    }
}
