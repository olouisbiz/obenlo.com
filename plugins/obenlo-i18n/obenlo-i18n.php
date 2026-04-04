<?php
/**
 * Plugin Name: Obenlo Translation
 * Description: Standalone translation system for Obenlo (English, French, Spanish).
 * Version: 1.3.0
 * Author: Obenlo
 * Author URI: https://obenlo.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OBENLO_I18N_VERSION', '1.3.0');
define('OBENLO_I18N_DIR', plugin_dir_path(__FILE__));
define('OBENLO_I18N_URL', plugin_dir_url(__FILE__));

// Use require_once instead of autoloading for simplicity in this standalone plugin
require_once OBENLO_I18N_DIR . 'includes/class-i18n-engine.php';
require_once OBENLO_I18N_DIR . 'includes/class-i18n-admin.php';

function obenlo_i18n_init() {
    $engine = new Obenlo_I18N_Engine();
    $engine->init();

    if (is_admin()) {
        $admin = new Obenlo_I18N_Admin();
        $admin->init();
    }

    // Register Shortcode for Google Translate Widget
    add_shortcode('obenlo_translate', function() {
        if (get_option('obenlo_enable_google_translate', '0') !== '1') {
            return '';
        }
        return '<div id="google_translate_element" class="obenlo-google-translate"></div>';
    });
}
add_action('plugins_loaded', 'obenlo_i18n_init');
