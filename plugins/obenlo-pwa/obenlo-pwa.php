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
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Obenlo">
        <link rel="apple-touch-icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile-192.png'); ?>">
        <link rel="manifest" href="<?php echo home_url('/manifest.json'); ?>">
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
                header('Service-Worker-Allowed: /');
            } else {
                header('Content-Type: application/manifest+json; charset=utf-8');
                // We will serve the manifest with dynamic paths for icons
                $manifest = file_get_contents(OBENLO_PWA_DIR . 'assets/manifest.json');
                $manifest = str_replace('/wp-content/', home_url('/wp-content/'), $manifest);
                echo $manifest;
                exit;
            }

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
        <div id="obenlo-pwa-prompt" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:94%; max-width:420px; background:#fff; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,0.2); z-index:10000; padding:20px; font-family:'Inter', -apple-system, sans-serif; align-items:center; gap:15px; border:1px solid rgba(0,0,0,0.05); animation: pwa-slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
            <style>
                @keyframes pwa-slide-up { from { transform: translate(-50%, 100%); opacity: 0; } to { transform: translate(-50%, 0); opacity: 1; } }
            </style>
            <div style="width:60px; height:60px; background:#fff; border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile.png'); ?>" alt="Obenlo App" style="width:40px; height:40px; border-radius:8px;">
            </div>
            <div style="flex-grow:1;">
                <h4 style="margin:0 0 4px 0; font-size:17px; color:#1a1a1b; font-weight:800; letter-spacing:-0.01em;">Obenlo: Better with the App</h4>
                <p style="margin:0; font-size:13px; color:#5e5e62; line-height:1.4;" id="pwa-prompt-desc">Install for a faster experience & instant notifications.</p>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button id="pwa-install-btn" style="background:#e61e4d; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer; box-shadow: 0 4px 12px rgba(230, 30, 77, 0.2);">Install</button>
                <button id="pwa-dismiss-btn" style="background:transparent; color:#999; border:none; padding:4px; font-size:12px; cursor:pointer; font-weight:600;">Maybe later</button>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let deferredPrompt;
            const promptUI = document.getElementById('obenlo-pwa-prompt');
            const installBtn = document.getElementById('pwa-install-btn');
            const dismissBtn = document.getElementById('pwa-dismiss-btn');
            
            // Reset helper for testing
            if (window.location.search.includes('reset_pwa=1')) {
                localStorage.removeItem('obenlo_pwa_dismissed');
                console.log('Obenlo: PWA dismissed flag cleared via URL parameter.');
            }

            if (localStorage.getItem('obenlo_pwa_dismissed') === 'true') {
                console.log('Obenlo: PWA prompt was previously dismissed. Use ?reset_pwa=1 to test.');
                return;
            }

            const isIos = () => /iphone|ipad|ipod/.test( window.navigator.userAgent.toLowerCase() );
            const isStandalone = () => ('standalone' in window.navigator) || (window.matchMedia('(display-mode: standalone)').matches);

            // Notification Request Logic
            const requestNotificationPermission = async () => {
                if ('Notification' in window && Notification.permission !== 'granted') {
                    console.log('Obenlo: Requesting notification permission...');
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        console.log('Obenlo: Notification permission granted.');
                    }
                }
            };

            if (isIos() && !isStandalone()) {
                console.log('Obenlo: iOS detected, showing manual install instructions');
                setTimeout(() => {
                    document.getElementById('pwa-prompt-desc').innerHTML = 'Tap the Share icon <svg viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle;fill:none;stroke:currentColor;stroke-width:2;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> and "Add to Home Screen".';
                    installBtn.style.display = 'none';
                    promptUI.style.setProperty('display', 'flex', 'important');
                }, 8000);
            } else if (!isStandalone()) {
                console.log('Obenlo: Standard browser detected, waiting for beforeinstallprompt');
                window.addEventListener('beforeinstallprompt', (e) => {
                    console.log('Obenlo PWA SUCCESS: beforeinstallprompt received!');
                    e.preventDefault();
                    deferredPrompt = e;
                    setTimeout(() => { 
                        promptUI.style.setProperty('display', 'flex', 'important'); 
                        requestNotificationPermission();
                    }, 2000);
                });

                installBtn.addEventListener('click', async () => {
                    promptUI.style.display = 'none';
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log(`Obenlo: User ${outcome} the install prompt`);
                        deferredPrompt = null;
                        if (outcome === 'accepted') requestNotificationPermission();
                    } else {
                        console.warn('Obenlo: Install button clicked but deferredPrompt is missing.');
                    }
                });
            } else {
                console.log('Obenlo: App is already in standalone mode.');
            }

            dismissBtn.addEventListener('click', () => {
                promptUI.style.display = 'none';
                localStorage.setItem('obenlo_pwa_dismissed', 'true');
            });

            // Debug Status Panel
            if (window.location.search.includes('debug_pwa=1')) {
                const debugDiv = document.createElement('div');
                debugDiv.style.cssText = 'position:fixed;top:10px;left:10px;background:rgba(0,0,0,0.8);color:#0f0;padding:10px;border-radius:8px;font-size:10px;z-index:999999;font-family:monospace;pointer-events:none;';
                debugDiv.id = 'pwa-debug-status';
                document.body.appendChild(debugDiv);
                
                const updateDebug = (msg) => {
                    debugDiv.innerHTML += '<div>> ' + msg + '</div>';
                    console.log('PWA DEBUG: ' + msg);
                };

                updateDebug('PWA Status: ' + (isStandalone() ? 'Standalone' : 'Browser'));
                updateDebug('iOS: ' + isIos());
                updateDebug('HTTPS: ' + (location.protocol === 'https:'));
                
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistration().then(reg => {
                        updateDebug('SW: ' + (reg ? 'Registered' : 'None'));
                        if (reg) updateDebug('Scope: ' + reg.scope);
                    });
                }
                
                window.addEventListener('beforeinstallprompt', () => updateDebug('EVENT: beforeinstallprompt [OK]'));
            }

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    // Remove query parameter for better installability
                    navigator.serviceWorker.register('/sw.js').then(function(reg) {
                        console.log('Obenlo PWA: ServiceWorker registered successfully');
                        
                        reg.addEventListener('updatefound', () => {
                            const newWorker = reg.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('Obenlo: New version available. Refreshing...');
                                    window.location.reload();
                                }
                            });
                        });
                    }).catch(function(err) {
                        console.error('Obenlo PWA: ServiceWorker registration failed:', err);
                    });
                });
            } else {
                console.warn('Obenlo PWA: Service workers are not supported in this browser.');
            }
        });
        </script>
        <?php
    }
}

$obenlo_pwa = new Obenlo_PWA();
$obenlo_pwa->init();
