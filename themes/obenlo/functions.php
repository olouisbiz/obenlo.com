<?php
/**
 * Obenlo functions and definitions
 */

function obenlo_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'obenlo_setup');

function obenlo_scripts()
{
    wp_enqueue_style('obenlo-style', get_stylesheet_uri(), array(), '1.0.1');

    // Google Fonts for a premium feel (Inter)
    wp_enqueue_style('obenlo-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null);

    // Wishlist Script
    wp_enqueue_script('obenlo-wishlist', get_template_directory_uri() . '/assets/js/wishlist.js', array('jquery'), '1.0.0', true);
    wp_localize_script('obenlo-wishlist', 'obenlo_wishlist', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('obenlo_wishlist_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'obenlo_scripts');

/**
 * Performance Optimizations
 */

// Disable the emoji's
function obenlo_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'obenlo_disable_emojis');

// Remove generator tag and other junk
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');


/**
 * Redirect /sitemap.xml to /wp-sitemap.xml
 */
function obenlo_sitemap_redirect()
{
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (untrailingslashit($request_uri) === '/sitemap.xml') {
        wp_redirect(home_url('/wp-sitemap.xml'), 301);
        exit;
    }
}
add_action('template_redirect', 'obenlo_sitemap_redirect');

/**
 * Add Security and Legitimacy Headers
 */
function obenlo_security_headers($headers)
{
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'no-referrer-when-downgrade';
    $headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
    return $headers;
}
add_filter('wp_headers', 'obenlo_security_headers');

/**
 * --- OBENLO PWA CORE (Theme-based) ---
 * Provides standalone PWA manifestation and Push Notification support.
 */
class Obenlo_PWA_Core
{
    public function init()
    {
        add_action('wp_head', array($this, 'inject_pwa_meta'), 1);
        add_action('wp_head', array($this, 'inject_pwa_script'), 2);
        add_action('template_redirect', array($this, 'serve_pwa_assets'), 1);

        // AJAX for PWA subscriptions (Same action name for compatibility)
        add_action('wp_ajax_obenlo_save_pwa_subscription', array($this, 'handle_save_subscription'));
    }

    public function inject_pwa_meta()
    {
        ?>
        <meta name="theme-color" content="#e61e4d">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Obenlo">
        <link rel="apple-touch-icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile-192.png'); ?>">
        <link rel="manifest" href="<?php echo home_url('/?obenlo_pwa=manifest'); ?>">
        <?php
    }

    public function serve_pwa_assets()
    {
        if (isset($_GET['obenlo_pwa'])) {
            $asset = $_GET['obenlo_pwa'];
            $file = '';
            
            header('Access-Control-Allow-Origin: *');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-cache, no-store, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');

            if ($asset === 'sw') {
                header('Content-Type: application/javascript; charset=utf-8');
                header('Service-Worker-Allowed: /');
                $file = get_template_directory() . '/assets/pwa/sw.js';
            } elseif ($asset === 'manifest') {
                header('Content-Type: application/manifest+json; charset=utf-8');
                $file = get_template_directory() . '/assets/pwa/manifest.json';
                if (file_exists($file)) {
                    $manifest = file_get_contents($file);
                    // Dynamically map icons and start_url if needed
                    $manifest = str_replace('/wp-content/', home_url('/wp-content/'), $manifest);
                    echo $manifest;
                    exit;
                }
            }

            if ($file && file_exists($file)) {
                readfile($file);
                exit;
            }
        }
    }

    public function inject_pwa_script()
    {
        ?>
        <div id="obenlo-pwa-prompt" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:94%; max-width:420px; background:#fff; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,0.2); z-index:10000; padding:20px; font-family:'Inter', sans-serif; align-items:center; gap:15px; border:1px solid rgba(0,0,0,0.05);">
            <div style="width:60px; height:60px; background:#fff; border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-social-profile.png" style="width:40px; height:40px; border-radius:8px;">
            </div>
            <div style="flex-grow:1;">
                <h4 style="margin:0 0 4px 0; font-size:17px; color:#1a1a1b; font-weight:800;">Obenlo: Better with the App</h4>
                <p style="margin:0; font-size:13px; color:#5e5e62; line-height:1.4;" id="pwa-prompt-desc">Install for a faster experience & instant notifications.</p>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <button id="pwa-install-btn" style="background:#e61e4d; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer;">Install</button>
                <button id="pwa-dismiss-btn" style="background:transparent; color:#999; border:none; padding:4px; font-size:12px; cursor:pointer; font-weight:600;">Maybe later</button>
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
            const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

            if (isStandalone()) return;

            const requestNotificationPermission = async () => {
                if ('Notification' in window && Notification.permission !== 'granted') {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') subscribeUserToPush();
                } else if (Notification.permission === 'granted') {
                    subscribeUserToPush();
                }
            };

            const urlBase64ToUint8Array = (base64String) => {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
            };

            const subscribeUserToPush = async () => {
                try {
                    const registration = await navigator.serviceWorker.ready;
                    const publicKey = '<?php echo esc_js(get_option("obenlo_pwa_public_key")); ?>';
                    if (!publicKey || publicKey.length < 20) return;

                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(publicKey)
                    });

                    await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'obenlo_save_pwa_subscription',
                            subscription: JSON.stringify(subscription)
                        })
                    });
                    console.log('Obenlo: Push Subscription saved');
                } catch (err) { console.error('Push Subscribe Failed:', err); }
            };

            if (isIos()) {
                setTimeout(() => {
                    document.getElementById('pwa-prompt-desc').innerHTML = 'Tap the Share icon and "Add to Home Screen".';
                    installBtn.style.display = 'none';
                    promptUI.style.setProperty('display', 'flex', 'important');
                }, 8000);
            } else {
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    deferredPrompt = e;
                    setTimeout(() => { 
                        promptUI.style.setProperty('display', 'flex', 'important'); 
                        requestNotificationPermission();
                    }, 3000);
                });

                installBtn.addEventListener('click', async () => {
                    promptUI.style.display = 'none';
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        deferredPrompt = null;
                        if (outcome === 'accepted') requestNotificationPermission();
                    }
                });
            }

            dismissBtn.addEventListener('click', () => {
                promptUI.style.display = 'none';
                localStorage.setItem('obenlo_pwa_dismissed', 'true');
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/?obenlo_pwa=sw', { scope: '/' }).then(reg => {
                        console.log('Obenlo PWA: Service Worker Active');
                        reg.onupdatefound = () => {
                            const newWorker = reg.installing;
                            newWorker.onstatechange = () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    window.location.reload();
                                }
                            };
                        };
                    });
                });
            }
        });
        </script>
        <?php
    }

    public function handle_save_subscription()
    {
        $user_id = get_current_user_id();
        $sub_data = isset($_POST['subscription']) ? json_decode(stripslashes($_POST['subscription']), true) : null;

        if (!$user_id || !$sub_data) {
            wp_send_json_error('Incomplete data');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_pwa_subscriptions';
        
        $endpoint = $sub_data['endpoint'] ?? '';
        $keys = $sub_data['keys'] ?? array();
        $p256dh = $keys['p256dh'] ?? '';
        $auth = $keys['auth'] ?? '';

        if (!$endpoint || !$p256dh || !$auth) {
            wp_send_json_error('Incomplete keys');
        }

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

    public static function generate_keys()
    {
        if (get_option('obenlo_pwa_public_key') && strlen(get_option('obenlo_pwa_public_key')) > 20) {
            return;
        }

        $autoload = WP_PLUGIN_DIR . '/obenlo-booking/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            try {
                $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
                update_option('obenlo_pwa_public_key', $keys['publicKey']);
                update_option('obenlo_pwa_private_key', $keys['privateKey']);
            } catch (\Exception $e) {
                error_log('Obenlo PWA: VAPID Error: ' . $e->getMessage());
            }
        }
    }
}

$obenlo_pwa_core = new Obenlo_PWA_Core();
$obenlo_pwa_core->init();

// Ensure keys exist
add_action('init', array('Obenlo_PWA_Core', 'generate_keys'), 10);

/**
 * Redirect users to the Welcome Landing Page after login and registration
 */
function obenlo_welcome_redirect($redirect_to, $request, $user)
{
    // If the user object is not valid (e.g. login failed), don't redirect
    if (is_wp_error($user) || !isset($user->roles)) {
        return $redirect_to;
    }

    // Don't redirect administrators or support agents out of the backend
    if (in_array('administrator', (array) $user->roles) || in_array('support_agent', (array) $user->roles)) {
        return $redirect_to;
    }

    // Redirect to our custom welcome page
    return home_url('/welcome/');
}
add_filter('login_redirect', 'obenlo_welcome_redirect', 10, 3);

add_filter('registration_redirect', function() {
    return home_url('/welcome/');
});

/**
 * Ensure the Welcome page exists in the database
 */
add_action('init', function() {
    if (get_page_by_path('welcome')) {
        return;
    }

    wp_insert_post(array(
        'post_title'   => 'Welcome',
        'post_name'    => 'welcome',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'page_template' => 'page-welcome.php'
    ));
});
