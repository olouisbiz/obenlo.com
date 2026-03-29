<?php
/**
 * Admin Interface for Obenlo Translation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_I18N_Admin
{
    public function init()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_menu()
    {
        add_submenu_page(
            'obenlo-admin-dashboard', // Parent slug (from obenlo-booking)
            'Obenlo Translation',
            'Translation',
            'manage_options',
            'obenlo-translation',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('obenlo-i18n-group', 'obenlo_enable_google_translate');
        register_setting('obenlo-i18n-group', 'obenlo_i18n_es');
        register_setting('obenlo-i18n-group', 'obenlo_i18n_fr');

        // Handle raw JSON inputs
        if (isset($_POST['obenlo_i18n_es_raw'])) {
            $es_json = stripslashes($_POST['obenlo_i18n_es_raw']);
            $es_array = json_decode($es_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($es_array)) {
                update_option('obenlo_i18n_es', $es_array);
            }
        }

        if (isset($_POST['obenlo_i18n_fr_raw'])) {
            $fr_json = stripslashes($_POST['obenlo_i18n_fr_raw']);
            $fr_array = json_decode($fr_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($fr_array)) {
                update_option('obenlo_i18n_fr', $fr_array);
            }
        }
    }

    public function render_settings_page()
    {
        $es_translations = get_option('obenlo_i18n_es', array());
        $fr_translations = get_option('obenlo_i18n_fr', array());
        $enable_google = get_option('obenlo_enable_google_translate', '0');

        // Basic UI for adding/editing strings
        ?>
        <div class="wrap">
            <h1 style="font-weight: 800; color: #1a1a1b;">Obenlo Translation Settings</h1>
            <p style="color: #5e5e62;">Manage custom translations for the Obenlo platform. Add any WordPress string to the dictionary below to override it.</p>

            <form method="post" action="options.php">
                <?php settings_fields('obenlo-i18n-group'); ?>

                <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h2 style="font-size: 1.2rem; margin-top: 0; color: #1a1a1b;">Google Translate Widget</h2>
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 0;">Automatically translate the entire website into 100+ languages using Google's translation element.</p>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" name="obenlo_enable_google_translate" value="1" <?php checked('1', $enable_google); ?>>
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo ($enable_google === '1') ? '#e61e4d' : '#ccc'; ?>; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
                <style>
                    .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; <?php echo ($enable_google === '1') ? 'transform: translateX(26px);' : ''; ?> }
                </style>
                
                <div style="display: flex; gap: 20px; margin-top: 30px;">
                    <div style="flex: 1; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05);">
                        <h2 style="font-size: 1.2rem; margin-top: 0; color: #1a1a1b;">Spanish (ES) Dictionary</h2>
                        <p style="font-size: 0.85rem; color: #888; margin-bottom: 15px;">Paste valid JSON: <code>{ "Original String": "Traducción" }</code></p>
                        <textarea name="obenlo_i18n_es_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 13px; line-height: 1.5; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fcfcfc;" placeholder='{ "Search": "Buscar" }'><?php echo esc_textarea(json_encode($es_translations, JSON_PRETTY_PRINT)); ?></textarea>
                    </div>

                    <div style="flex: 1; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05);">
                        <h2 style="font-size: 1.2rem; margin-top: 0; color: #1a1a1b;">French (FR) Dictionary</h2>
                        <p style="font-size: 0.85rem; color: #888; margin-bottom: 15px;">Paste valid JSON: <code>{ "Original String": "Traduction" }</code></p>
                        <textarea name="obenlo_i18n_fr_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 13px; line-height: 1.5; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fcfcfc;" placeholder='{ "Search": "Rechercher" }'><?php echo esc_textarea(json_encode($fr_translations, JSON_PRETTY_PRINT)); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05);">
                    <?php submit_button('Save All Dictionaries', 'primary', 'submit', false, array('style' => 'background: #222; color: #fff; border: none; padding: 15px 40px; border-radius: 10px; font-weight: bold; cursor: pointer;')); ?>
                </div>
            </form>
        </div>
        <?php
    }
}
