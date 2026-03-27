<?php
/**
 * Plugin Name: Obenlo PWA
 * Description: Standalone Progressive Web App functionality for Obenlo.
 * Version: 1.0.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OBENLO_PWA_DIR', plugin_dir_path(__FILE__));
define('OBENLO_PWA_URL', plugin_dir_url(__FILE__));

class Obenlo_PWA
{
    public function init()
    {
        add_action('wp_head', array($this, 'inject_meta_tags'), 1);
        add_action('parse_request', array($this, 'serve_pwa_assets'), 1);
        add_action('wp_footer', array($this, 'inject_pwa_script'), 100);
    }

    /**
     * Inject PWA Meta Tags and Manifest Link
     */
    public function inject_meta_tags()
    {
        ?>
        <meta name="theme-color" content="#e61e4d">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Obenlo">
        <link rel="apple-touch-icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile.png'); ?>">
        <link rel="manifest" href="/manifest.json?v=1.0.0">
        <?php
    }

    /**
     * Serve sw.js and manifest.json from the root domain
     */
    public function serve_pwa_assets()
    {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($request_uri, PHP_URL_PATH);

        if ($path === '/sw.js' || $path === '/manifest.json') {
            if ($path === '/sw.js') {
                header('Content-Type: application/javascript; charset=utf-8');
                $file = OBENLO_PWA_DIR . 'assets/sw.js';
            } else {
                header('Content-Type: application/json; charset=utf-8');
                $file = OBENLO_PWA_DIR . 'assets/manifest.json';
            }

            header('Service-Worker-Allowed: /');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            if (file_exists($file)) {
                readfile($file);
            } else {
                wp_send_json_error('PWA Asset Not Found');
            }
            exit;
        }
    }

    /**
     * Inject PWA Registration and Prompt Script
     */
    public function inject_pwa_script()
    {
        // Re-inject the PWA prompt HTML
        ?>
        <div id="obenlo-pwa-prompt" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:90%; max-width:400px; background:#fff; border-radius:16px; box-shadow:0 10px 40px rgba(0,0,0,0.15); z-index:10000; padding:20px; font-family:'Inter', sans-serif; align-items:center; gap:15px; border:1px solid #eee;">
            <div style="width:50px; height:50px; background:#f0f0f0; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile.png'); ?>" alt="Obenlo App" style="width:32px; height:32px; border-radius:8px;">
            </div>
            <div style="flex-grow:1;">
                <h4 style="margin:0 0 4px 0; font-size:16px; color:#222; font-weight:700;">Install Obenlo App</h4>
                <p style="margin:0; font-size:13px; color:#666; line-height:1.4;" id="pwa-prompt-desc">Book faster and get notifications directly on your phone.</p>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button id="pwa-install-btn" style="background:#e61e4d; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer;">Install</button>
                <button id="pwa-dismiss-btn" style="background:transparent; color:#999; border:none; padding:4px; font-size:12px; cursor:pointer; text-decoration:underline;">Not Now</button>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let deferredPrompt;
            const promptUI = document.getElementById('obenlo-pwa-prompt');
            const installBtn = document.getElementById('pwa-install-btn');
            const dismissBtn = document.getElementById('pwa-dismiss-btn');
            
            if (localStorage.getItem('obenlo_pwa_dismissed') === 'true') return;

            const isIos = () => /iphone|ipad|ipod/.test( window.navigator.userAgent.toLowerCase() );
            const isStandalone = () => ('standalone' in window.navigator) && (window.navigator.standalone);

            if (isIos() && !isStandalone()) {
                setTimeout(() => {
                    document.getElementById('pwa-prompt-desc').innerHTML = 'Tap the Share icon <svg viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;fill:none;stroke:currentColor;stroke-width:2;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> and select "Add to Home Screen".';
                    installBtn.style.display = 'none';
                    promptUI.style.display = 'flex';
                }, 5000);
            } else {
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    deferredPrompt = e;
                    setTimeout(() => { promptUI.style.display = 'flex'; }, 5000);
                });

                installBtn.addEventListener('click', async () => {
                    promptUI.style.display = 'none';
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        await deferredPrompt.userChoice;
                        deferredPrompt = null;
                    }
                });
            }

            dismissBtn.addEventListener('click', () => {
                promptUI.style.display = 'none';
                localStorage.setItem('obenlo_pwa_dismissed', 'true');
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js?v=1.0.0').then(function(reg) {
                        console.log('Obenlo PWA: ServiceWorker registered');
                    });
                });
            }
        });
        </script>
        <?php
    }
}

$obenlo_pwa = new Obenlo_PWA();
$obenlo_pwa->init();
