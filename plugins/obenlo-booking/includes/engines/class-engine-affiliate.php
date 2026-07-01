<?php
/**
 * Affiliate Redirect Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Engine_Affiliate extends Obenlo_Abstract_Engine {

    public function get_id() { return 'affiliate'; }
    public function get_name() { return 'Affiliate Link'; }

    public function render_host_fields($listing_id, $slug = '') {
        $url = get_post_meta($listing_id, '_obenlo_affiliate_url', true);
        ob_start();
        ?>
        <div id="affiliate_wrapper_modular" style="margin-top:15px; padding:15px; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px;">
            <label style="display:block; font-weight:700; margin-bottom:5px;">Affiliate Booking URL</label>
            <input type="url" name="obenlo_affiliate_url" value="<?php echo esc_attr($url); ?>" placeholder="https://..." style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Instead of booking on Obenlo, guests will be redirected to this URL when they click Book.</p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $url = get_post_meta($listing_id, '_obenlo_affiliate_url', true);
        ob_start();
        ?>
        <div style="margin-bottom:15px;">
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" style="display:flex; justify-content:center; align-items:center; gap:8px; background:#e61e4d; color:#fff; padding:15px; border-radius:12px; font-weight:700; text-decoration:none; text-align:center; box-shadow:0 4px 15px rgba(230,30,77,0.3); transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
                Book on Partner Site
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            </a>
            <div style="text-align:center; font-size:0.75rem; color:#666; margin-top:10px;">
                You will be redirected to complete your booking.
            </div>
            <script>
                // Hide the default reserve button and checkout elements
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
