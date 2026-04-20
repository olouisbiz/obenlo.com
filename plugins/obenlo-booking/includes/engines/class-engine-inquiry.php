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
        <div class="inquiry-booking-fields" style="background:#fcfdff; border:1.5px dashed #dbeafe; padding:20px; border-radius:20px; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px; color:#1e40af;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span style="font-weight:800; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px;"><?php echo __('Service Inquiry', 'obenlo'); ?></span>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:10px;"><?php echo __('DESCRIBE YOUR REQUIREMENTS', 'obenlo'); ?></label>
                <textarea name="inquiry_message" rows="4" placeholder="<?php echo esc_attr(__('e.g. I need a full day shoot for my wedding on July 10th...', 'obenlo')); ?>" 
                    style="width:100%; padding:15px; border:1.5px solid #e2e8f0; border-radius:14px; font-family:inherit; font-size:0.9rem; outline:none; transition:border-color 0.2s; resize:none;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            </div>
            
            <div style="display:flex; gap:8px; align-items:flex-start; color:#64748b; font-size:0.8rem; line-height:1.4;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" style="flex-shrink:0; margin-top:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span><?php echo __('The host will review your request and reply with a custom price quote.', 'obenlo'); ?></span>
            </div>
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

            var pmWrapper = document.getElementById('pricing_model_wrapper');
            if (pmWrapper) pmWrapper.style.display = 'none';
            var pmSelect = document.getElementById('pricing_model');
            if (pmSelect) pmSelect.value = 'inquiry_only';
            
            var capContainer = document.getElementById('capacity_wrapper');
            if (capContainer) capContainer.style.display = 'none';
            var unitsWrapper = document.getElementById('units_wrapper');
            if (unitsWrapper) unitsWrapper.style.display = 'none';

            if (slotsWrapper)    slotsWrapper.style.display    = 'none';
            if (durationWrapper) durationWrapper.style.display = 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "";
    }
}
