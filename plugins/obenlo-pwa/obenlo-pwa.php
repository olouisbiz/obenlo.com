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
        add_action('wp_head', array($this, 'inject_pwa_script'), 2);

        // AJAX for PWA subscriptions
        add_action('wp_ajax_obenlo_save_pwa_subscription', array($this, 'handle_save_subscription'));
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
            header('Access-Control-Allow-Origin: *');
            header('X-Content-Type-Options: nosniff');
            
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

            header('Cache-Control: private, no-cache, no-store, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');

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
        (function() {
            console.log('Obenlo PWA: Loader v4.0.0');
            
            // Immediate Debug Box
            if (window.location.search.includes('debug=1')) {
                const debugDiv = document.createElement('div');
                debugDiv.style.cssText = 'position:fixed;top:0;left:0;right:0;background:#000;color:#0f0;padding:12px;font-size:11px;z-index:2147483647;font-family:monospace;border-bottom:2px solid #0f0;line-height:1.4;';
                debugDiv.id = 'pwa-debug-status';
                debugDiv.innerHTML = '<b>OBENLO PWA DEBUG v4.0.0</b><br>';
                document.documentElement.appendChild(debugDiv);
                
                window.updateObenloDebug = (msg) => {
                    debugDiv.innerHTML += '<div>> ' + msg + '</div>';
                };
            }
        })();

        document.addEventListener('DOMContentLoaded', function() {
            let deferredPrompt;
            const promptUI = document.getElementById('obenlo-pwa-prompt');
            const installBtn = document.getElementById('pwa-install-btn');
            const dismissBtn = document.getElementById('pwa-dismiss-btn');
            const debugLog = window.updateObenloDebug || (() => {});

            debugLog('DOM Content Loaded');

            // Reset helper
            if (window.location.search.includes('reset_pwa=1')) {
                localStorage.removeItem('obenlo_pwa_dismissed');
                debugLog('PWA Reset flag cleared');
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
                        subscribeUserToPush();
                    }
                } else if (Notification.permission === 'granted') {
                    subscribeUserToPush();
                }
            };

            const urlBase64ToUint8Array = (base64String) => {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            };

            const subscribeUserToPush = async () => {
                try {
                    const registration = await navigator.serviceWorker.ready;
                    const publicKey = '<?php echo esc_js(get_option("obenlo_pwa_public_key")); ?>';
                    
                    if (!publicKey) {
                        console.error('Obenlo: Push Public Key missing.');
                        return;
                    }

                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(publicKey)
                    });

                    console.log('Obenlo: PWA Subscribed:', subscription);
                    
                    // Save to server
                    await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'obenlo_save_pwa_subscription',
                            subscription: JSON.stringify(subscription)
                        })
                    });
                    console.log('Obenlo: PWA Subscription saved to server.');
                } catch (err) {
                    console.error('Obenlo: Push subscription failed:', err);
                }
            };

            if (isIos() && !isStandalone()) {
                console.log('Obenlo: iOS detected, showing manual install instructions');
                setTimeout(() => {
                    const shareIcon = '<svg viewBox="0 0 24 24" style="width:20px;height:20px;vertical-align:middle;margin:0 3px;fill:none;stroke:#e61e4d;stroke-width:2.5;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>';
                    document.getElementById('pwa-prompt-desc').innerHTML = 'Tap the ' + shareIcon + ' icon and then select <b>"Add to Home Screen"</b>.';
                    installBtn.style.display = 'none';
                    promptUI.style.setProperty('display', 'flex', 'important');
                }, 3000); // Reduced delay for better UX
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

    /**
     * AJAX: Save PWA Push Subscription
     */
    public function handle_save_subscription()
    {
        $user_id = get_current_user_id();
        $sub_data = isset($_POST['subscription']) ? json_decode(stripslashes($_POST['subscription']), true) : null;

        if (!$user_id || !$sub_data) {
            wp_send_json_error('Invalid subscription data');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_pwa_subscriptions';

        $endpoint = $sub_data['endpoint'] ?? '';
        $p256dh = $sub_data['keys']['p256dh'] ?? '';
        $auth = $sub_data['keys']['auth'] ?? '';

        if (!$endpoint || !$p256dh || !$auth) {
            wp_send_json_error('Incomplete subscription keys');
        }

        // Check if already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE endpoint = %s AND user_id = %d", $endpoint, $user_id));

        if ($exists) {
            wp_send_json_success('Already subscribed');
        }

        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth
        ));

        wp_send_json_success('Subscription saved');
    }

    /**
     * Generate VAPID Keys if missing
     */
    public static function generate_keys()
    {
        if (get_option('obenlo_pwa_public_key') && get_option('obenlo_pwa_private_key')) {
            return;
        }

        // Use WebPush library from Obenlo Booking
        $autoload = WP_PLUGIN_DIR . '/obenlo-booking/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            try {
                $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
                update_option('obenlo_pwa_public_key', $keys['publicKey']);
                update_option('obenlo_pwa_private_key', $keys['privateKey']);
                error_log('Obenlo PWA: VAPID keys generated successfully.');
            } catch (\Exception $e) {
                error_log('Obenlo PWA: Error generating VAPID keys: ' . $e->getMessage());
            }
        }
    }
}

$obenlo_pwa = new Obenlo_PWA();
$obenlo_pwa->init();

// Register activation hook
register_activation_hook(__FILE__, array('Obenlo_PWA', 'generate_keys'));

// Also run it on init once if missing (for existing installs)
add_action('init', array('Obenlo_PWA', 'generate_keys'), 5);
