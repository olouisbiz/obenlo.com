<?php
/**
 * SeatGeek Booking Engine
 * Instantly redirects guests to the official SeatGeek purchase page.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_SeatGeek extends Obenlo_Abstract_Engine {

    public function get_id() { return 'SeatGeek'; }
    public function get_name() { return 'SeatGeek Integration'; }

    public function render_host_fields($listing_id, $slug = '') {
        $tm_url = get_post_meta($listing_id, '_obenlo_SeatGeek_url', true);
        ob_start();
        ?>
        <div id="SeatGeek_wrapper_modular" style="margin-top:15px; padding:15px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; color:#1e3a8a;">SeatGeek Affiliate Link</label>
            <input type="url" name="obenlo_SeatGeek_url" value="<?php echo esc_attr($tm_url); ?>" placeholder="https://www.SeatGeek.com/..." style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; font-size:13px;" />
            <p style="font-size:0.8rem; color:#1d4ed8; margin-top:5px;">This URL was automatically imported from the SeatGeek Discovery API. When guests click Reserve, they will be redirected here.</p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $tm_url = get_post_meta($listing_id, '_obenlo_SeatGeek_url', true);
        ob_start();
        ?>
        <div class="obenlo-SeatGeek-redirect" style="margin-bottom:15px; width: 100%;">
            <?php if (empty(trim($tm_url))) : ?>
                <div style="padding:20px; text-align:center; background:#fef2f2; border:1px dashed #f87171; border-radius:12px; color:#991b1b; font-size:0.9rem;">SeatGeek URL is missing!</div>
            <?php else : ?>
                <a href="<?php echo esc_url($tm_url); ?>" target="_blank" rel="noopener noreferrer" style="display:block; width:100%; padding:14px; background:#026cdf; color:#fff; text-align:center; font-weight:700; border-radius:8px; text-decoration:none; font-size:16px;">Buy Tickets on SeatGeek</a>
                <p style="text-align:center; font-size:12px; color:#6b7280; margin-top:10px;">You will be redirected to the official SeatGeek platform to complete your purchase securely.</p>
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
                    var paymentRow = document.querySelector('select[name="payment_method"]');
                    if(paymentRow) paymentRow.closest('.form-row').style.display = 'none';
                    
                    var totalRow = document.getElementById('live-total');
                    if(totalRow) totalRow.parentElement.parentElement.style.display = 'none';
                    
                    var priceHeader = document.querySelector('.price-header');
                    if(priceHeader) priceHeader.style.display = 'none';
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    public function calculate_price($listing_id, $data, $slug = '') {
        return 0; // Handled offsite
    }

    public function get_host_js_logic($slug = '') {
        return "
            var pmWrapper = document.getElementById('pricing_model_wrapper');
            if (pmWrapper) pmWrapper.style.display = 'none';
            var capContainer = document.getElementById('capacity_wrapper');
            if (capContainer) capContainer.style.display = 'none';
            var unitsWrapper = document.getElementById('units_wrapper');
            if (unitsWrapper) unitsWrapper.style.display = 'none';
            if (typeof slotsWrapper !== 'undefined' && slotsWrapper) slotsWrapper.style.display = 'none';
            if (typeof durationWrapper !== 'undefined' && durationWrapper) durationWrapper.style.display = 'none';
        ";
    }

    public function get_frontend_js_logic($listing_id, $slug = '') {
        return "";
    }
}

