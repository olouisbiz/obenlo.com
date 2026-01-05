<?php
/**
 * Obenlo Core Settings
 * Location: /obenlo-plugin/includes/admin-settings.php
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_menu_page('Obenlo SES', 'Obenlo SES', 'manage_options', 'obenlo-settings', 'obenlo_render_settings', 'dashicons-palmtree', 30);
});

function obenlo_render_settings() {
    if (isset($_POST['obenlo_save'])) {
        update_option('obenlo_stripe_secret', sanitize_text_field($_POST['s_secret']));
        update_option('obenlo_stripe_public', sanitize_text_field($_POST['s_public']));
        update_option('obenlo_stripe_webhook_secret', sanitize_text_field($_POST['s_webhook']));
        echo '<div class="updated"><p>Settings Saved.</p></div>';
    }

    $secret  = get_option('obenlo_stripe_secret');
    $public  = get_option('obenlo_stripe_public');
    $webhook = get_option('obenlo_stripe_webhook_secret');
    ?>
    <div class="wrap">
        <h1>Obenlo SES Configuration</h1>
        <form method="post" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #ccd0d4; max-width:600px;">
            <table class="form-table">
                <tr><th>Public Key</th><td><input type="text" name="s_public" value="<?php echo esc_attr($public); ?>" class="regular-text"></td></tr>
                <tr><th>Secret Key</th><td><input type="password" name="s_secret" value="<?php echo esc_attr($secret); ?>" class="regular-text"></td></tr>
                <tr><th>Webhook Secret</th><td><input type="text" name="s_webhook" value="<?php echo esc_attr($webhook); ?>" class="regular-text"></td></tr>
            </table>
            <input type="submit" name="obenlo_save" class="button button-primary" value="Save Configuration">
        </form>
    </div>
    <?php
}