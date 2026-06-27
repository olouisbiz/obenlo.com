<?php
/**
 * Plugin Name: Obenlo PWA
 * Description: True Native App experience for Obenlo — offline support, push notifications, install prompts, and seamless standalone behaviour.
 * Version: 2.0.0
 * Author: Obenlo
 * Author URI: https://obenlo.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OBENLO_PWA_VERSION', '2.0.1');
define('OBENLO_PWA_DIR', plugin_dir_path(__FILE__));
define('OBENLO_PWA_URL', plugin_dir_url(__FILE__));

class Obenlo_PWA
{
    public function init()
    {
        add_action('wp_head',             array($this, 'inject_meta_tags'),    1);
        add_action('parse_request',       array($this, 'serve_pwa_assets'),    1);
        add_action('wp_head',             array($this, 'inject_pwa_script'),   2);
        add_action('wp_enqueue_scripts',  array($this, 'enqueue_pwa_styles'));
        add_filter('body_class',          array($this, 'add_pwa_body_classes'));

        // AJAX handlers
        add_action('wp_ajax_obenlo_save_pwa_subscription', array($this, 'handle_save_subscription'));
        add_action('wp_ajax_obenlo_delete_pwa_subscription', array($this, 'handle_delete_subscription'));
    }

    // ─── Meta Tags & Manifest ─────────────────────────────────────────────────
    public function inject_meta_tags()
    {
        $logo = get_template_directory_uri() . '/assets/images/logo-social-profile-192.png';
        $logo_large = get_template_directory_uri() . '/assets/images/logo-social-profile.png';
        ?>
        <!-- PWA Core -->
        <meta name="theme-color" content="#e61e4d" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#b5143a" media="(prefers-color-scheme: dark)">
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="manifest" href="<?php echo home_url('/manifest.json'); ?>">

        <!-- iOS / Safari specific -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Obenlo">
        <link rel="apple-touch-icon" href="<?php echo esc_url($logo); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url($logo); ?>">
        
        <!-- Apple Splash Screens (iOS Startup Images) for Premium iPhone Models -->
        <!-- iPhone 15 Pro Max, 14 Pro Max -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo_large); ?>" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)">
        <!-- iPhone 15 Pro, 15, 14 Pro -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo_large); ?>" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)">
        <!-- iPhone 14 Plus, 13 Pro Max, 12 Pro Max -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo_large); ?>" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3)">
        <!-- iPhone 14, 13 Pro, 13, 12 Pro, 12 -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo_large); ?>" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">
        <!-- iPhone SE, 8, 7 -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo); ?>" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
        <!-- Fallback startup image -->
        <link rel="apple-touch-startup-image" href="<?php echo esc_url($logo); ?>">

        <!-- Viewport: native feel, prevent double-tap zoom -->
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">

        <!-- Microsoft / Edge -->
        <meta name="msapplication-TileColor" content="#e61e4d">
        <meta name="msapplication-TileImage" content="<?php echo esc_url($logo); ?>">
        <?php
    }

    // ─── Serve sw.js and manifest.json from domain root ──────────────────────
    public function serve_pwa_assets()
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

        if ($path === '/sw.js') {
            header('Content-Type: application/javascript; charset=utf-8');
            header('Service-Worker-Allowed: /');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Access-Control-Allow-Origin: *');
            $file = OBENLO_PWA_DIR . 'assets/sw.js';
            if (file_exists($file)) { readfile($file); } else { http_response_code(404); }
            exit;
        }

        if ($path === '/manifest.json') {
            header('Content-Type: application/manifest+json; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
            header('Access-Control-Allow-Origin: *');
            $manifest = file_get_contents(OBENLO_PWA_DIR . 'assets/manifest.json');
            // Resolve icon paths to absolute URLs
            $manifest = str_replace('"/wp-content/', '"' . home_url('/wp-content/'), $manifest);
            echo $manifest;
            exit;
        }
    }

    // ─── Enqueue PWA CSS ──────────────────────────────────────────────────────
    public function enqueue_pwa_styles()
    {
        wp_enqueue_style(
            'obenlo-pwa-standalone',
            OBENLO_PWA_URL . 'assets/pwa.css',
            array(),
            OBENLO_PWA_VERSION
        );
    }

    // ─── Body Classes ─────────────────────────────────────────────────────────
    public function add_pwa_body_classes($classes)
    {
        $user = wp_get_current_user();

        if (!$user->ID) {
            $classes[] = 'pwa-role-guest';
        } elseif (current_user_can('manage_options')) {
            $classes[] = 'pwa-role-admin';
        } elseif (in_array('host', (array)$user->roles)) {
            $classes[] = 'pwa-role-host';
        } else {
            $classes[] = 'pwa-role-guest';
        }

        return $classes;
    }

    // ─── Main PWA Script ──────────────────────────────────────────────────────
    public function inject_pwa_script()
    {
        $logo     = get_template_directory_uri() . '/assets/images/logo-social-profile.png';
        $logo192  = get_template_directory_uri() . '/assets/images/logo-social-profile-192.png';
        $ajax_url = admin_url('admin-ajax.php');
        $pub_key  = esc_js(get_option('obenlo_pwa_public_key', ''));
        ?>

        <!-- ░░ Obenlo PWA Universal Bottom Nav (mobile ≤768px) ░░ -->
        <?php
        $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
        $is_host     = current_user_can('manage_options') || in_array('host', (array)(wp_get_current_user()->roles ?? []));
        $is_logged_in = is_user_logged_in();
        $home_url     = home_url('/');
        $explore_url  = home_url('/listings');
        $account_url  = home_url('/account/');
        $host_url     = home_url('/host-dashboard/');
        $path         = $_SERVER['REQUEST_URI'] ?? '/';

        $active_home    = ($path === '/' || $path === '') ? 'obenlo-nav-active' : '';
        $active_explore = (strpos($path, '/listings') !== false) ? 'obenlo-nav-active' : '';
        $active_account = (strpos($path, '/account') !== false) ? 'obenlo-nav-active' : '';
        $active_host    = (strpos($path, '/host-dashboard') !== false) ? 'obenlo-nav-active' : '';
        ?>
        <nav id="obenlo-pwa-bottom-nav" aria-label="Main navigation">
            <a href="<?php echo esc_url($home_url); ?>" class="obenlo-pwa-nav-item <?php echo $active_home; ?>" aria-label="Home">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>
                <span>Home</span>
            </a>
            <a href="<?php echo esc_url($explore_url); ?>" class="obenlo-pwa-nav-item <?php echo $active_explore; ?>" aria-label="Explore">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Explore</span>
            </a>
            <?php if ($is_logged_in): ?>
            <a href="<?php echo esc_url($account_url . '?tab=trips'); ?>" class="obenlo-pwa-nav-item <?php echo $active_account; ?>" aria-label="Trips">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Trips</span>
            </a>
            <?php if ($is_host): ?>
            <a href="<?php echo esc_url($host_url); ?>" class="obenlo-pwa-nav-item <?php echo $active_host; ?>" aria-label="Host">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21V12h6v9"/></svg>
                <span>Host</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo esc_url($account_url); ?>" class="obenlo-pwa-nav-item" aria-label="Profile">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profile</span>
            </a>
            <?php else: ?>
            <a href="<?php echo esc_url(home_url('/login')); ?>" class="obenlo-pwa-nav-item" aria-label="Sign In">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Sign In</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- ░░ Obenlo PWA Offline Banner ░░ -->
        <div id="obenlo-offline-banner">⚡ You're offline — browsing saved content</div>

        <!-- ░░ Obenlo PWA Update Toast ░░ -->
        <div id="obenlo-update-toast" role="button" aria-label="Update available">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
            Update available — tap to refresh
        </div>

        <!-- ░░ Obenlo PWA Install Prompt ░░ -->
        <div id="obenlo-pwa-prompt" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:calc(100% - 32px); max-width:400px; background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.18); z-index:10000; padding:18px 20px; align-items:center; gap:14px; border:1px solid rgba(0,0,0,0.06);">
            <div style="width:54px; height:54px; background:#fff; border-radius:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
                <img src="<?php echo esc_url($logo192); ?>" alt="Obenlo" style="width:38px; height:38px; border-radius:8px; object-fit:cover;">
            </div>
            <div style="flex-grow:1; min-width:0;">
                <h4 style="margin:0 0 3px 0; font-size:15px; font-weight:800; color:#1a1a1b; letter-spacing:-0.01em; font-family:'Inter',-apple-system,sans-serif;">Get the Obenlo App</h4>
                <p id="pwa-prompt-desc" style="margin:0; font-size:12px; color:#737373; line-height:1.4; font-family:'Inter',-apple-system,sans-serif;">Instant access. Works offline. Free to install.</p>
            </div>
            <div style="display:flex; flex-direction:column; gap:6px; flex-shrink:0;">
                <button id="pwa-install-btn" style="background:#e61e4d; color:#fff; border:none; padding:9px 18px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; font-family:'Inter',-apple-system,sans-serif; white-space:nowrap; letter-spacing:-0.01em;">Get App</button>
                <button id="pwa-dismiss-btn" style="background:transparent; color:#aaa; border:none; padding:3px; font-size:11px; cursor:pointer; font-weight:600; font-family:'Inter',-apple-system,sans-serif;">Not now</button>
            </div>
        </div>

        <script>
        (function() {
            'use strict';

            // ── Config ──────────────────────────────────────────────────────
            const PUSH_PUBLIC_KEY = '<?php echo $pub_key; ?>';
            const AJAX_URL        = '<?php echo esc_url($ajax_url); ?>';
            const STORAGE_KEY     = 'obenlo_pwa_v2';
            const PROMPT_DELAY_MS = 3500;

            // ── State ───────────────────────────────────────────────────────
            let deferredInstallPrompt = null;

            // ── Utility ─────────────────────────────────────────────────────
            const isIos       = () => /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isAndroid   = () => /android/i.test(navigator.userAgent);
            const isStandalone = () =>
                window.navigator.standalone === true ||
                window.matchMedia('(display-mode: standalone)').matches ||
                window.matchMedia('(display-mode: window-controls-overlay)').matches;

            function getStorage() {
                try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch { return {}; }
            }
            function setStorage(data) {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...getStorage(), ...data })); } catch {}
            }

            // ── Debug Panel ─────────────────────────────────────────────────
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('debug_pwa')) {
                const panel = document.createElement('div');
                panel.style.cssText = 'position:fixed;top:env(safe-area-inset-top,0);left:0;right:0;background:rgba(0,0,0,0.85);color:#0f0;padding:10px 14px;font-size:10px;z-index:2147483647;font-family:monospace;line-height:1.6;pointer-events:none;';
                panel.innerHTML = `<b>OBENLO PWA v2.0.0</b><br>
                    Standalone: ${isStandalone()}<br>
                    iOS: ${isIos()} | Android: ${isAndroid()}<br>
                    HTTPS: ${location.protocol === 'https:'}<br>
                    SW: ${'serviceWorker' in navigator}<br>`;
                document.documentElement.appendChild(panel);
            }

            // ── Reset helper ─────────────────────────────────────────────────
            if (urlParams.has('reset_pwa')) {
                localStorage.removeItem(STORAGE_KEY);
            }

            // ── Offline / Online Banner ───────────────────────────────────────
            function initOfflineBanner() {
                const banner = document.getElementById('obenlo-offline-banner');
                if (!banner) return;

                const show = () => banner.classList.add('visible');
                const hide = () => banner.classList.remove('visible');

                if (!navigator.onLine) show();
                window.addEventListener('offline', show);
                window.addEventListener('online',  () => { hide(); /* brief success flash */ });
            }

            // ── Service Worker Registration ──────────────────────────────────
            function registerServiceWorker() {
                if (!('serviceWorker' in navigator)) return;

                window.addEventListener('load', async () => {
                    try {
                        const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                        console.log('[Obenlo PWA] SW registered, scope:', reg.scope);

                        // Detect update
                        reg.addEventListener('updatefound', () => {
                            const newWorker = reg.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    showUpdateToast(newWorker);
                                }
                            });
                        });

                        // Listen for SW messages
                        navigator.serviceWorker.addEventListener('message', (event) => {
                            if (event.data?.type === 'SYNC_COMPLETE') {
                                console.log('[Obenlo PWA] Background sync complete');
                            }
                        });

                        // Request notification permission once SW is ready
                        if (Notification.permission === 'granted') {
                            subscribeToPush(reg);
                        }

                    } catch (err) {
                        console.warn('[Obenlo PWA] SW registration failed:', err);
                    }
                });
            }

            // ── Update Toast ─────────────────────────────────────────────────
            function showUpdateToast(worker) {
                const toast = document.getElementById('obenlo-update-toast');
                if (!toast) return;

                toast.classList.add('show');
                toast.addEventListener('click', () => {
                    worker.postMessage({ type: 'SKIP_WAITING' });
                    window.location.reload();
                });

                // Auto-dismiss after 8s
                setTimeout(() => toast.classList.remove('show'), 8000);
            }

            // ── Push Subscription ─────────────────────────────────────────────
            function base64ToUint8(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw     = atob(base64);
                const arr     = new Uint8Array(raw.length);
                for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
                return arr;
            }

            async function subscribeToPush(registration) {
                if (!PUSH_PUBLIC_KEY || !('PushManager' in window)) return;
                try {
                    const existing = await registration.pushManager.getSubscription();
                    if (existing) return; // Already subscribed

                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: base64ToUint8(PUSH_PUBLIC_KEY)
                    });

                    await fetch(AJAX_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'obenlo_save_pwa_subscription',
                            subscription: JSON.stringify(subscription)
                        })
                    });
                    console.log('[Obenlo PWA] Push subscription saved.');
                } catch (err) {
                    console.warn('[Obenlo PWA] Push subscription failed:', err);
                }
            }

            async function requestNotificationAndSubscribe() {
                if (!('Notification' in window)) return;
                if (Notification.permission === 'denied') return;
                if (Notification.permission === 'granted') {
                    const reg = await navigator.serviceWorker.ready;
                    subscribeToPush(reg);
                    return;
                }
                const perm = await Notification.requestPermission();
                if (perm === 'granted') {
                    const reg = await navigator.serviceWorker.ready;
                    subscribeToPush(reg);
                }
            }

            // ── Install Prompt (Android / Chrome) ────────────────────────────
            function initInstallPrompt() {
                const storage = getStorage();
                if (storage.dismissed) return;   // Permanently dismissed
                if (isStandalone()) return;       // Already installed

                const promptUI   = document.getElementById('obenlo-pwa-prompt');
                const installBtn = document.getElementById('pwa-install-btn');
                const dismissBtn = document.getElementById('pwa-dismiss-btn');
                if (!promptUI) return;

                if (isIos()) {
                    // iOS: show manual instructions
                    setTimeout(() => {
                        const shareIcon = '<svg viewBox="0 0 24 24" style="width:18px;height:18px;vertical-align:middle;margin:0 2px;fill:none;stroke:#e61e4d;stroke-width:2.5;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>';
                        document.getElementById('pwa-prompt-desc').innerHTML =
                            'Tap ' + shareIcon + ' then <b>"Add to Home Screen"</b>';
                        installBtn.style.display = 'none';
                        promptUI.style.setProperty('display', 'flex', 'important');
                    }, PROMPT_DELAY_MS);

                } else {
                    // Android / Chrome: wait for native prompt
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        deferredInstallPrompt = e;
                        setTimeout(() => {
                            promptUI.style.setProperty('display', 'flex', 'important');
                        }, PROMPT_DELAY_MS);
                    });

                    installBtn?.addEventListener('click', async () => {
                        promptUI.classList.add('dismissing');
                        setTimeout(() => promptUI.style.display = 'none', 300);
                        if (deferredInstallPrompt) {
                            deferredInstallPrompt.prompt();
                            const { outcome } = await deferredInstallPrompt.userChoice;
                            console.log('[Obenlo PWA] Install outcome:', outcome);
                            deferredInstallPrompt = null;
                            if (outcome === 'accepted') {
                                setStorage({ installed: true });
                                requestNotificationAndSubscribe();
                            }
                        }
                    });

                    window.addEventListener('appinstalled', () => {
                        setStorage({ installed: true });
                        promptUI.style.display = 'none';
                        console.log('[Obenlo PWA] App installed successfully.');
                    });
                }

                dismissBtn?.addEventListener('click', () => {
                    promptUI.classList.add('dismissing');
                    setTimeout(() => promptUI.style.display = 'none', 300);
                    setStorage({ dismissed: true });
                });
            }

            // ── Standalone-Only Native Enhancements ───────────────────────────
            function initStandaloneEnhancements() {
                if (!isStandalone()) return;

                // 1. Prevent pull-to-refresh (Android)
                let startY = 0;
                document.addEventListener('touchstart', (e) => { startY = e.touches[0].pageY; }, { passive: true });
                document.addEventListener('touchmove', (e) => {
                    if (window.scrollY === 0 && e.touches[0].pageY > startY + 5) {
                        // Only suppress if at top of scroll and pulling down
                        e.preventDefault();
                    }
                }, { passive: false });

                // 2. Fix empty "Welcome back, !" strings
                document.querySelectorAll('h1, h2').forEach((el) => {
                    if (el.textContent.trim() === 'Welcome back, !') {
                        el.textContent = 'Welcome back!';
                    }
                });

                // 3. All external links open in a new tab (not hijack the app)
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a[href]');
                    if (!link) return;
                    const url = new URL(link.href, location.origin);
                    if (url.origin !== location.origin) {
                        e.preventDefault();
                        window.open(link.href, '_blank', 'noopener,noreferrer');
                    }
                });

                // 4. Smooth page transitions (fade on navigate)
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a[href]');
                    if (!link) return;
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript') || link.target === '_blank') return;
                    try {
                        const url = new URL(href, location.origin);
                        if (url.origin !== location.origin) return;
                        document.body.style.opacity = '0.7';
                        document.body.style.transition = 'opacity 0.2s ease';
                    } catch {}
                });

                // 5. Admin HUD detection
                if (
                    location.href.includes('obenlo-admin-dashboard') ||
                    location.search.includes('page=obenlo') ||
                    document.body.classList.contains('pwa-role-admin')
                ) {
                    document.body.classList.add('pwa-admin-hub');
                }

                // 6. Nav scroll indicator
                const sidebar = document.querySelector('.dashboard-sidebar, .listing-sidebar');
                if (sidebar && sidebar.scrollWidth > sidebar.clientWidth + 10) {
                    const indicator = document.createElement('div');
                    indicator.className = 'pwa-scroll-indicator';
                    indicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
                    document.body.appendChild(indicator);
                    sidebar.addEventListener('scroll', () => {
                        indicator.style.opacity = sidebar.scrollLeft > 10 ? '0' : '0.7';
                    }, { passive: true });
                }

                // 7. Subscribe to push if already granted
                if ('Notification' in window && Notification.permission === 'granted') {
                    requestNotificationAndSubscribe();
                }

                console.log('[Obenlo PWA] Standalone mode active — native enhancements applied.');
            }

            // ── Boot ─────────────────────────────────────────────────────────
            document.addEventListener('DOMContentLoaded', () => {
                initOfflineBanner();
                initInstallPrompt();
                initStandaloneEnhancements();
            });

            registerServiceWorker(); // Registers before DOMContentLoaded for speed

        })();
        </script>
        <?php
    }

    // ─── AJAX: Save Push Subscription ────────────────────────────────────────
    public function handle_save_subscription()
    {
        $user_id  = get_current_user_id();
        $sub_data = isset($_POST['subscription']) ? json_decode(stripslashes($_POST['subscription']), true) : null;

        if (!$user_id || !$sub_data) {
            wp_send_json_error('Invalid subscription data');
        }

        global $wpdb;
        $table    = $wpdb->prefix . 'obenlo_pwa_subscriptions';
        $endpoint = $sub_data['endpoint'] ?? '';
        $p256dh   = $sub_data['keys']['p256dh'] ?? '';
        $auth     = $sub_data['keys']['auth'] ?? '';

        if (!$endpoint || !$p256dh || !$auth) {
            wp_send_json_error('Incomplete subscription keys');
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE endpoint = %s AND user_id = %d",
            $endpoint, $user_id
        ));

        if ($exists) {
            wp_send_json_success('Already subscribed');
        }

        $wpdb->insert($table, [
            'user_id'  => $user_id,
            'endpoint' => $endpoint,
            'p256dh'   => $p256dh,
            'auth'     => $auth,
        ]);

        wp_send_json_success('Subscription saved');
    }

    // ─── AJAX: Delete Push Subscription (on permission revoke) ───────────────
    public function handle_delete_subscription()
    {
        $user_id  = get_current_user_id();
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field(wp_unslash($_POST['endpoint'])) : '';

        if (!$user_id || !$endpoint) {
            wp_send_json_error('Missing data');
        }

        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'obenlo_pwa_subscriptions',
            ['user_id' => $user_id, 'endpoint' => $endpoint]
        );

        wp_send_json_success('Subscription removed');
    }

    // ─── Generate VAPID Keys if missing ──────────────────────────────────────
    public static function generate_keys()
    {
        if (get_option('obenlo_pwa_public_key') && get_option('obenlo_pwa_private_key')) {
            return;
        }

        $autoload = WP_PLUGIN_DIR . '/obenlo-booking/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            try {
                $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
                update_option('obenlo_pwa_public_key',  $keys['publicKey']);
                update_option('obenlo_pwa_private_key', $keys['privateKey']);
                error_log('[Obenlo PWA] VAPID keys generated successfully.');
            } catch (\Exception $e) {
                error_log('[Obenlo PWA] VAPID key generation error: ' . $e->getMessage());
            }
        }
    }
}

// ── Boot ──────────────────────────────────────────────────────────────────────
$obenlo_pwa = new Obenlo_PWA();
$obenlo_pwa->init();

register_activation_hook(__FILE__, array('Obenlo_PWA', 'generate_keys'));
add_action('init', array('Obenlo_PWA', 'generate_keys'), 5);
