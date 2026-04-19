<?php
if(!defined('ABSPATH')) exit;
class Obenlo_Engine_Fixed_Block extends Obenlo_Abstract_Engine {
    public function get_id() { return 'fixed_block'; }
    public function get_name() { return 'fixed_block'; }
    public function render_host_fields($listing_id, $slug = '') { return ''; }
    public function render_booking_widget($listing_id, $slug = '') { return ''; }
    public function calculate_price($listing_id, $data, $slug = '') { return 0; }
    public function get_host_js_logic($slug = '') { return ''; }
    public function get_frontend_js_logic($slug = '') { return ''; }
}
