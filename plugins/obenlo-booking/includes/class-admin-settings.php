<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Settings
{
    public function init()
    {
        add_action('admin_post_obenlo_save_settings', array($this, 'handle_save_settings'));
        add_action('admin_post_obenlo_save_translation', array($this, 'handle_save_translation'));
    }

    public function render_settings_tab()
    {
        $global_fee = get_option('obenlo_global_platform_fee', '10');
        $info_email = get_option('obenlo_info_email', 'info@obenlo.com');
        $admin_email = get_option('obenlo_admin_email', 'admin@obenlo.com');
        $from_name = get_option('obenlo_mail_from_name', 'Obenlo');
        $ga4_id = get_option('obenlo_google_analytics_id', '');
        $pixel_id = get_option('obenlo_meta_pixel_id', '');

        if (isset($_GET['sync_status'])) {
            $color = ($_GET['sync_status'] === 'success') ? '#155724' : '#721c24';
            $bg = ($_GET['sync_status'] === 'success') ? '#d4edda' : '#f8d7da';
            $border = ($_GET['sync_status'] === 'success') ? '#c3e6cb' : '#f5c6cb';
            echo '<div style="background:' . $bg . '; color:' . $color . '; border:1px solid ' . $border . '; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600;">' . esc_html(urldecode($_GET['sync_msg'])) . '</div>';
        }
        if (isset($_GET['test_status'])) {
            $color = ($_GET['test_status'] === 'success') ? '#155724' : '#721c24';
            $bg = ($_GET['test_status'] === 'success') ? '#d4edda' : '#f8d7da';
            $border = ($_GET['test_status'] === 'success') ? '#c3e6cb' : '#f5c6cb';
            echo '<div style="background:' . $bg . '; color:' . $color . '; border:1px solid ' . $border . '; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600;">' . esc_html(urldecode($_GET['test_msg'])) . '</div>';
        }
?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_save_settings">
                <?php wp_nonce_field('save_settings', 'settings_nonce'); ?>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">
                    <div>
                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px;">Platform Configuration</h4>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Global Platform Fee (%)</label>
                            <p style="font-size:0.8em; color:#666; margin-bottom:10px;">Default percentage taken from each booking.</p>
                            <input type="number" name="global_fee" value="<?php echo esc_attr($global_fee); ?>" step="0.1" required style="width:100px; padding:10px; border:1px solid #ddd; border-radius:8px;"> <span style="font-weight:600;">%</span>
                        </div>

                        <div style="margin-bottom:25px; background:#fff3cd; padding:15px; border-radius:10px; border:1px solid #ffeeba;">
                            <label style="display:flex; align-items:center; gap:10px; font-weight:700; margin-bottom:5px;">
                                <input type="checkbox" name="hide_demo_frontpage" value="yes" <?php checked(get_option('obenlo_hide_demo_frontpage', 'no'), 'yes'); ?>>
                                Hide Demo Content from Public Frontpage
                            </label>
                            <p style="font-size:0.85em; color:#856404; margin-bottom:0px; margin-left: 24px;">Visitor guests will not see demo listings/hosts on the home page. Admins and Hosts will still see them.</p>
                        </div>

                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px; margin-top:40px;">Email & Notifications</h4>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Public From Name</label>
                            <input type="text" name="mail_from_name" value="<?php echo esc_attr($from_name); ?>" placeholder="Obenlo" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">System Reply-To Email</label>
                            <input type="email" name="info_email" value="<?php echo esc_attr($info_email); ?>" placeholder="info@obenlo.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Internal Admin Notifications</label>
                            <input type="email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" placeholder="admin@obenlo.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                    </div>

                    <div>
                        <h4 style="border-bottom:2px solid #eee; padding-bottom:10px;">Tracking & Analytics</h4>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Google Analytics 4 ID</label>
                            <input type="text" name="ga4_id" value="<?php echo esc_attr($ga4_id); ?>" placeholder="G-XXXXXXXXXX" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:5px;">Meta (Facebook) Pixel ID</label>
                            <input type="text" name="pixel_id" value="<?php echo esc_attr($pixel_id); ?>" placeholder="1234567890" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        </div>

                        <div style="background:#fff3cd; padding:20px; border-radius:12px; border:1px solid #ffeeba; margin-top:40px;">
                            <h4 style="margin-top:0; color:#856404;">Emergency Tools</h4>
                            <p style="font-size:0.85em; color:#856404; margin-bottom:15px;">Use these only if the database fails during an update.</p>
                            <button type="button" id="force-install-db" style="background:#dc3545; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:700; font-size:1em; width:100%;">⚙️ Reinstall Database Tables</button>
                            <script>
                            document.getElementById('force-install-db').addEventListener('click', function() {
                                if(!confirm('This will attempt to run the database table creation scripts. Continue?')) return;
                                const btn = this;
                                btn.textContent = 'Installing...';
                                const formData = new FormData();
                                formData.append('action', 'obenlo_force_install_db');
                                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                                    method: 'POST', body: formData
                                }).then(r => r.json()).then(res => {
                                    alert(res.data);
                                    btn.textContent = '⚙️ Reinstall Database Tables';
                                }).catch(err => {
                                    alert('Error installing tables. Check console.');
                                    console.error(err);
                                    btn.textContent = '⚙️ Reinstall Database Tables';
                                });
                            });
                            </script>
                        </div>
                    </div>
                </div>

                <div style="margin-top:40px; border-top:2px solid #eee; padding-top:20px;">
                    <button type="submit" name="save_obenlo_settings" value="1" style="background:#e61e4d; color:#fff; border:none; padding:15px 40px; border-radius:12px; cursor:pointer; font-weight:800; font-size:1.1em; width:100%; box-shadow:0 4px 10px rgba(230,30,77,0.2);">Save All Site Settings</button>
                </div>
            </form>
        </div>
        <?php
    }

    public function handle_save_settings()
    {
        error_log('Obenlo Settings: Request received.');
        if (!current_user_can('administrator')) {
            error_log('Obenlo Settings: Unauthorized access attempt.');
            obenlo_redirect_with_error('unauthorized');
        }

        check_admin_referer('save_settings', 'settings_nonce');
        error_log('Obenlo Settings: Nonce verified.');

        $redirect_url = add_query_arg('tab', 'settings', wp_get_referer());

        if (isset($_POST['global_fee'])) {
            update_option('obenlo_global_platform_fee', sanitize_text_field($_POST['global_fee']));
        }
        if (isset($_POST['info_email'])) {
            update_option('obenlo_info_email', sanitize_email($_POST['info_email']));
        }
        if (isset($_POST['admin_email'])) {
            update_option('obenlo_admin_email', sanitize_email($_POST['admin_email']));
        }
        if (isset($_POST['mail_from_name'])) {
            update_option('obenlo_mail_from_name', sanitize_text_field($_POST['mail_from_name']));
        }
        if (isset($_POST['ga4_id'])) {
            update_option('obenlo_google_analytics_id', sanitize_text_field($_POST['ga4_id']));
        }
        if (isset($_POST['pixel_id'])) {
            update_option('obenlo_meta_pixel_id', sanitize_text_field($_POST['pixel_id']));
        }

        update_option('obenlo_hide_demo_frontpage', isset($_POST['hide_demo_frontpage']) ? 'yes' : 'no');

        error_log('Obenlo Settings: Redirecting to ' . $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    public function render_translation_tab()
    {
        $es_translations = get_option('obenlo_i18n_es', array());
        $fr_translations = get_option('obenlo_i18n_fr', array());
        $enable_google = get_option('obenlo_enable_google_translate', '0');
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h3 style="margin: 0; font-size: 1.8em; font-weight: 800;">Global translation</h3>
                <p style="color:#888; margin: 5px 0 0 0;">Master console for manual dictionaries and automated fallbacks.</p>
            </div>
            <div style="background: #fff; padding: 10px 20px; border-radius: 12px; border: 1px solid #eee; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <span style="font-size: 0.85rem; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Google Widget</span>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" id="google-toggle-form">
                    <input type="hidden" name="action" value="obenlo_save_translation">
                    <?php wp_nonce_field('save_translation', 'translation_nonce'); ?>
                    <label class="switch" style="position: relative; display: inline-block; width: 46px; height: 24px;">
                        <input type="checkbox" name="enable_google" value="1" <?php checked('1', $enable_google); ?> onchange="document.getElementById('google-toggle-form').submit()">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo ($enable_google === '1') ? '#e61e4d' : '#ccc'; ?>; transition: .4s; border-radius: 24px;"></span>
                    </label>
                    <input type="hidden" name="es_raw" value='<?php echo esc_attr(json_encode($es_translations)); ?>'>
                    <input type="hidden" name="fr_raw" value='<?php echo esc_attr(json_encode($fr_translations)); ?>'>
                </form>
            </div>
        </div>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="obenlo_save_translation">
            <?php wp_nonce_field('save_translation', 'translation_nonce'); ?>
            <input type="hidden" name="enable_google" value="<?php echo esc_attr($enable_google); ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div class="stat-card" style="text-align: left; padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="margin: 0; font-size: 1.1em; color: #1a1a1b;">Spanish (ES)</h4>
                        <span style="background: rgba(230, 30, 77, 0.1); color: #e61e4d; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">MANUAL DICTIONARY</span>
                    </div>
                    <textarea name="es_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 13px; line-height: 1.6; padding: 20px; border: 1px solid #eee; border-radius: 12px; background: #fcfcfc; color: #444; outline: none; transition: border-color 0.2s;" placeholder='{ "Original": "Tratamiento" }'><?php echo esc_textarea(json_encode($es_translations, JSON_PRETTY_PRINT)); ?></textarea>
                </div>

                <div class="stat-card" style="text-align: left; padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="margin: 0; font-size: 1.1em; color: #1a1a1b;">French (FR)</h4>
                        <span style="background: rgba(34, 34, 34, 0.05); color: #222; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">MANUAL DICTIONARY</span>
                    </div>
                    <textarea name="fr_raw" style="width: 100%; height: 400px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 13px; line-height: 1.6; padding: 20px; border: 1px solid #eee; border-radius: 12px; background: #fcfcfc; color: #444; outline: none; transition: border-color 0.2s;" placeholder='{ "Original": "Traduction" }'><?php echo esc_textarea(json_encode($fr_translations, JSON_PRETTY_PRINT)); ?></textarea>
                </div>
            </div>

            <button type="submit" style="background: #222; color: #fff; border: none; padding: 18px; border-radius: 14px; font-weight: 800; font-size: 1.1em; cursor: pointer; width: 100%; box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: transform 0.2s;">
                Update Global Dictionaries
            </button>
        </form>

        <style>
            .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; <?php echo ($enable_google === '1') ? 'transform: translateX(22px);' : ''; ?> }
            textarea:focus { border-color: #e61e4d !important; }
        </style>
        <?php
    }

    public function handle_save_translation()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        check_admin_referer('save_translation', 'translation_nonce');

        $redirect_url = add_query_arg('tab', 'translation', wp_get_referer());

        // Save Google Translate Toggle
        $enable_google = isset($_POST['enable_google']) ? '1' : '0';
        update_option('obenlo_enable_google_translate', $enable_google);

        // Save Raw Dictionaries
        if (isset($_POST['es_raw'])) {
            $es_json = stripslashes($_POST['es_raw']);
            $es_array = json_decode($es_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($es_array)) {
                update_option('obenlo_i18n_es', $es_array);
            }
        }

        if (isset($_POST['fr_raw'])) {
            $fr_json = stripslashes($_POST['fr_raw']);
            $fr_array = json_decode($fr_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($fr_array)) {
                update_option('obenlo_i18n_fr', $fr_array);
            }
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

}
