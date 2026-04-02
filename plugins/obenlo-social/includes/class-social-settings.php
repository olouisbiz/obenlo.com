<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_Settings {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function add_settings_page() {
        add_options_page(
            'Obenlo Social Sharing',
            'Obenlo Social',
            'manage_options',
            'obenlo-social',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function register_settings() {
        // We only keep the templates now, no more API tokens!
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_listing_template' );
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_post_template' );
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Obenlo Social Sharing Settings</h1>
            <p><strong>Zero-Cost & Continuous Sharing</strong>: Instead of using complex Meta APIs, Obenlo now uses yours and your guests' native device sharing. When you click "Push to Social", it will open the official share-sheet on your phone or a popup on your desktop.</p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'obenlo_social_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Listing Caption Template<br/><small>{title}, {price}, {location}</small></th>
                        <td><textarea name="obenlo_social_listing_template" style="width: 500px; height: 100px; border-radius:10px;"><?php echo esc_textarea( get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!\n\nBook yours today:") ); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Blog Post Caption Template<br/><small>{title}, {excerpt}</small></th>
                        <td><textarea name="obenlo_social_post_template" style="width: 500px; height: 100px; border-radius:10px;"><?php echo esc_textarea( get_option('obenlo_social_post_template', "Latest on the Obenlo Blog:\n\n{title}\n\n{excerpt}\n\nRead more:") ); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button('Update Social Templates'); ?>
            </form>
            
            <div style="background:#f4f9ff; padding:20px; border-radius:15px; border:1px solid #e1e8f0; margin-top:40px; max-width:600px;">
                <h4 style="margin-top:0;">💡 Why is this better?</h4>
                <ul style="margin-bottom:0;">
                    <li><strong>100% Free</strong>: No Zapier or Middleware costs.</li>
                    <li><strong>No Maintenance</strong>: No Access Tokens to refresh or API keys to update.</li>
                    <li><strong>Works with Any App</strong>: On mobile, this lets you share to Instagram Stories, WhatsApp Groups, Threads, and more.</li>
                </ul>
            </div>
        </div>
        <?php
    }
}
