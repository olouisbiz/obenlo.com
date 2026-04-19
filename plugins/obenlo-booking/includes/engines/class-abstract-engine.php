<?php
/**
 * Abstract Booking Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class Obenlo_Abstract_Engine {
    
    /**
     * Get Engine Unique ID
     */
    abstract public function get_id();

    /**
     * Get Human Readable Name
     */
    abstract public function get_name();

    /**
     * Render Host Dashboard Form Fields
     */
    abstract public function render_host_fields($listing_id, $slug = '');

    /**
     * Render Guest Booking Sidebar Widget
     */
    abstract public function render_booking_widget($listing_id, $slug = '');

    /**
     * Server-side Price Calculation Logic
     */
    abstract public function calculate_price($listing_id, $data, $slug = '');

    /**
     * Get JS Logic for Host Form Toggles
     */
    abstract public function get_host_js_logic($slug = '');

    /**
     * Get JS Logic for Frontend Live Total
     */
    abstract public function get_frontend_js_logic($listing_id, $slug = '');
}
