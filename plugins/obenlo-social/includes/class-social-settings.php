<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_Settings {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function add_settings_page() {
        add_options_page(
            'Obenlo Social Auto-Poster',
            'Obenlo Social',
            'manage_options',
            'obenlo-social',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function register_settings() {
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_fb_page_id' );
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_ig_user_id' );
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_fb_access_token' );
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_listing_template' );
        register_setting( 'obenlo_social_settings_group', 'obenlo_social_post_template' );
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Obenlo Social Auto-Poster Configuration</h1>
            <p>Connect your Meta Developer app here. Note: You need a long-lived Page Access Token with <code>pages_manage_posts</code>, <code>pages_read_engagement</code>, and <code>instagram_basic</code>, <code>instagram_content_publish</code> permissions.</p>
            <form method="post" action="options.php">
                <?php settings_fields( 'obenlo_social_settings_group' ); ?>
                <?php do_settings_sections( 'obenlo_social_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Facebook Page ID</th>
                        <td><input type="text" name="obenlo_social_fb_page_id" value="<?php echo esc_attr( get_option('obenlo_social_fb_page_id') ); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Instagram User ID</th>
                        <td><input type="text" name="obenlo_social_ig_user_id" value="<?php echo esc_attr( get_option('obenlo_social_ig_user_id') ); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Page Access Token</th>
                        <td><input type="password" name="obenlo_social_fb_access_token" value="<?php echo esc_attr( get_option('obenlo_social_fb_access_token') ); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Listing Auto-Caption Template<br/><small>{title}, {price}, {location}</small></th>
                        <td><textarea name="obenlo_social_listing_template" style="width: 400px; height: 100px;"><?php echo esc_textarea( get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!\n\nBook yours today:") ); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Blog Post Auto-Caption Template<br/><small>{title}, {excerpt}</small></th>
                        <td><textarea name="obenlo_social_post_template" style="width: 400px; height: 100px;"><?php echo esc_textarea( get_option('obenlo_social_post_template', "Latest on the Obenlo Blog:\n\n{title}\n\n{excerpt}\n\nRead more:") ); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
