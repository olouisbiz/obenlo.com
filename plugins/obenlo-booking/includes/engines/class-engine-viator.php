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
        $widget_code = get_post_meta($listing_id, '_obenlo_viator_widget_code', true);
        ob_start();
        ?>
        <div id="viator_wrapper_modular" style="margin-top:15px; padding:15px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
            <label style="display:block; font-weight:700; margin-bottom:5px; color:#166534;">Viator Widget HTML/Script Code</label>
            <textarea name="obenlo_viator_widget_code" rows="5" placeholder='<div data-vi-partner-id="..."></div><script async src="..."></script>' style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; font-family:monospace; font-size:12px;"><?php echo esc_textarea($widget_code); ?></textarea>
            <p style="font-size:0.8rem; color:#15803d; margin-top:5px;">Paste your exact Viator Product Widget snippet here. It will replace the Obenlo booking form on the frontend.</p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_booking_widget($listing_id, $slug = '') {
        $widget_code = get_post_meta($listing_id, '_obenlo_viator_widget_code', true);
        ob_start();
        ?>
        <div class="obenlo-viator-widget-container" style="margin-bottom:15px; width: 100%; overflow: hidden;">
            <?php 
            if (empty(trim($widget_code))) {
                echo '<div style="padding:20px; text-align:center; background:#f9fafb; border:1px dashed #d1d5db; border-radius:12px; color:#6b7280; font-size:0.9rem;">No Viator Widget code provided.</div>';
            } else {
                // Output raw script code. Allow iframe, script, and div tags.
                echo wp_kses($widget_code, array(
                    'div' => array(
                        'id' => array(),
                        'class' => array(),
                        'data-vi-partner-id' => array(),
                        'data-vi-widget-ref' => array(),
                        'data-vi-language' => array(),
                        'data-vi-currency' => array(),
                        'style' => array(),
                    ),
                    'script' => array(
                        'src' => array(),
                        'async' => array(),
                        'defer' => array(),
                        'type' => array(),
                    ),
                    'iframe' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array(),
                        'frameborder' => array(),
                        'scrolling' => array(),
                        'style' => array(),
                    )
                ));
            }
            ?>
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
