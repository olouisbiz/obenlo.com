<?php
/**
 * Viator Widget Booking Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Viator extends Obenlo_Abstract_Engine {

    public function get_id() { return 'viator'; }
    public function get_name() { return 'Viator Widget'; }

    public function render_host_fields($listing_id, $slug = '') {
        $url = get_post_meta($listing_id, '_obenlo_viator_url', true);
        ob_start();
        ?>
        <div id="viator_wrapper_modular" style="margin-top:15px; padding:15px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; color:#166534;">Viator Booking URL</label>
            <input type="url" name="obenlo_viator_url" value="<?php echo esc_attr($url); ?>" placeholder="https://www.viator.com/..." style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" />
            <p style="font-size:0.8rem; color:#15803d; margin-top:5px;">Users will be redirected to this URL when they click "Book on Viator".</p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $url = get_post_meta($listing_id, '_obenlo_viator_url', true);
        ob_start();
        ?>
        <div class="obenlo-viator-container" style="margin-bottom:15px;">
            <?php if (empty($url)): ?>
                <div style="padding:20px; text-align:center; background:#f9fafb; border:1px dashed #d1d5db; border-radius:12px; color:#6b7280;">No Viator booking URL provided.</div>
            <?php else: ?>
                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" style="display:block; width:100%; padding:15px; background-color:#166534; color:#fff; text-align:center; border-radius:8px; font-weight:bold; text-decoration:none; font-size:1.1rem; transition:background 0.2s;">
                    Explore Experiences on Viator
                </a>
            <?php endif; ?>
            <script>
                // Hide the default reserve button and total elements
                document.addEventListener('DOMContentLoaded', function() {
                    var btn = document.getElementById('obenlo-reserve-btn');
                    if(btn) {
                        btn.style.display = 'none';
                        var checkoutMsg = btn.previousElementSibling;
                        if(checkoutMsg && checkoutMsg.tagName === 'DIV' && checkoutMsg.innerHTML.includes('CHECKOUT')) {
                            checkoutMsg.style.display = 'none';
                        }
                    }
                    var totals = document.querySelector('.obenlo-booking-totals');
                    if(totals) totals.style.display = 'none';
                    var guestPicker = document.querySelector('.obenlo-guest-picker');
                    if(guestPicker) guestPicker.style.display = 'none';
                    var timePicker = document.querySelector('.obenlo-time-picker');
                    if(timePicker) timePicker.style.display = 'none';
                    var datePicker = document.getElementById('obenlo-booking-date');
                    if(datePicker) datePicker.style.display = 'none';
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        return floatval(get_post_meta($listing_id, '_obenlo_price', true));
    }

    public function get_host_js_logic($slug = '') {
        return "
            var pmWrapper = document.getElementById('pricing_model_wrapper');
            if (pmWrapper) pmWrapper.style.display = 'none';
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
