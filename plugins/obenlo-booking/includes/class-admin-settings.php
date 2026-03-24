<?php
/**
 * Obenlo Booking Admin Settings
 * Handles the registration of the Obenlo Settings page in WP Admin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Admin_Settings {

    public function init() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page() {
        // Add a top-level menu page
        add_menu_page(
            'Obenlo Settings',
            'Obenlo Settings',
            'manage_options',
            'obenlo-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic',
            30
        );
    }

    public function register_settings() {
        // Option group: obenlo_settings_group
        register_setting('obenlo_settings_group', 'obenlo_google_analytics_id');
        register_setting('obenlo_settings_group', 'obenlo_meta_pixel_id');
        register_setting('obenlo_settings_group', 'obenlo_stripe_publishable_key');
        register_setting('obenlo_settings_group', 'obenlo_stripe_secret_key');
        register_setting('obenlo_settings_group', 'obenlo_paypal_client_id');
        register_setting('obenlo_settings_group', 'obenlo_paypal_secret');
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show a success message if settings were just updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error('obenlo_messages', 'obenlo_message', 'Settings Saved', 'updated');
        }
        settings_errors('obenlo_messages');
        ?>
        <div class="wrap">
            <h1 style="margin-bottom: 20px; font-weight: bold;">Obenlo API Keys & Settings</h1>
            
            <form action="options.php" method="post" style="max-width: 800px; background: #fff; padding: 30px; border: 1px solid #ccd0d4; border-radius: 8px;">
                <?php
                settings_fields('obenlo_settings_group');
                do_settings_sections('obenlo_settings_group');
                ?>
                
                <h2 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">Tracking & Analytics</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="obenlo_google_analytics_id">Google Analytics 4 (GA4) ID</label></th>
                        <td>
                            <input type="text" id="obenlo_google_analytics_id" name="obenlo_google_analytics_id" value="<?php echo esc_attr(get_option('obenlo_google_analytics_id')); ?>" class="regular-text" placeholder="G-XXXXXXXXXX" />
                            <p class="description">Used in the header to track conversions (e.g. G-1234567890).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="obenlo_meta_pixel_id">Meta (Facebook) Pixel ID</label></th>
                        <td>
                            <input type="text" id="obenlo_meta_pixel_id" name="obenlo_meta_pixel_id" value="<?php echo esc_attr(get_option('obenlo_meta_pixel_id')); ?>" class="regular-text" placeholder="123456789012345" />
                            <p class="description">Used in the header to optimize ad delivery and track purchases.</p>
                        </td>
                    </tr>
                </table>

                <h2 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 40px;">Stripe Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="obenlo_stripe_publishable_key">Publishable Key</label></th>
                        <td>
                            <input type="text" id="obenlo_stripe_publishable_key" name="obenlo_stripe_publishable_key" value="<?php echo esc_attr(get_option('obenlo_stripe_publishable_key')); ?>" class="regular-text" placeholder="pk_live_XXX..." />
                            <p class="description">Your public Stripe key used on the frontend checkout.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="obenlo_stripe_secret_key">Secret Key</label></th>
                        <td>
                            <input type="password" id="obenlo_stripe_secret_key" name="obenlo_stripe_secret_key" value="<?php echo esc_attr(get_option('obenlo_stripe_secret_key')); ?>" class="regular-text" placeholder="sk_live_XXX..." />
                            <p class="description">Your private Stripe key used for backend payment intents.</p>
                        </td>
                    </tr>
                </table>

                <h2 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 40px;">PayPal Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="obenlo_paypal_client_id">Client ID</label></th>
                        <td>
                            <input type="text" id="obenlo_paypal_client_id" name="obenlo_paypal_client_id" value="<?php echo esc_attr(get_option('obenlo_paypal_client_id')); ?>" class="regular-text" />
                            <p class="description">Your PayPal REST API Client ID.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="obenlo_paypal_secret">Secret</label></th>
                        <td>
                            <input type="password" id="obenlo_paypal_secret" name="obenlo_paypal_secret" value="<?php echo esc_attr(get_option('obenlo_paypal_secret')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
}
