<?php
/**
 * Plugin Name:       Obenlo Social Auto-Poster
 * Description:       Manually sync and push your Listings and Blog Posts directly to your official Obenlo Facebook and Instagram accounts.
 * Version: 1.9.0
 * Author:            Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'OBENLO_SOCIAL_VERSION', '1.9.0' );
define( 'OBENLO_SOCIAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'OBENLO_SOCIAL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Obenlo Social Class
 */
class Obenlo_Social {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once OBENLO_SOCIAL_DIR . 'includes/class-social-settings.php';
        require_once OBENLO_SOCIAL_DIR . 'includes/class-social-admin-ui.php';
    }

    private function init_hooks() {
        Obenlo_Social_Admin_UI::init();
        if( is_admin() || wp_doing_ajax() ) {
            Obenlo_Social_Settings::init();
        }
    }
}

// Initialize the plugin
add_action( 'plugins_loaded', array( 'Obenlo_Social', 'get_instance' ) );
