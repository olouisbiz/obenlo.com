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
    wp_enqueue_style('obenlo-style', get_stylesheet_uri(), array(), time());

    // Google Fonts â€” Inter variable weight for premium typography
    wp_enqueue_style('obenlo-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap', array(), null);

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


// Redirect /sitemap.xml to /wp-sitemap.xml
// Removed: Sitemap now handled natively by Obenlo SEO plugin.

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
 * --- OBENLO PWA CORE ---
 * Removed: obenlo.com now runs as a full web app.
 * All PWA functionality (service worker, manifest, install prompts) has been disabled.
 */


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
 * Ensure the required pages exist in the database (Enhanced Safety)
 */
add_action('init', function () {
    // Avoid running on every page load - check flag
    if (get_option('obenlo_pages_restored_v167')) return;

    $pages = array(
        'welcome'              => array('title' => 'Welcome',              'template' => 'page-welcome.php'),
        'about-us'             => array('title' => 'About Us',             'template' => 'page-about-us.php'),
        'account'              => array('title' => 'Account',              'template' => 'page-account.php'),
        'become-a-host'        => array('title' => 'Become a Host',        'template' => 'page-become-a-host.php'),
        'blog'                 => array('title' => 'Blog',                 'template' => 'page-blog.php'),
        'cancellation-policy'  => array('title' => 'Cancellation Policy',  'template' => 'page-cancellation-policy.php'),
        'community'            => array('title' => 'Community',            'template' => 'page-community.php'),
        'faq'                  => array('title' => 'FAQ',                  'template' => 'page-faq.php'),
        'guest-rules'          => array('title' => 'Guest Rules',          'template' => 'page-guest-rules.php'),
        'host-onboarding'      => array('title' => 'Host Onboarding',      'template' => 'page-host-onboarding.php'),
        'hosts'                => array('title' => 'Hosts',                'template' => 'page-hosts.php'),
        'how-it-works'         => array('title' => 'How It Works',         'template' => 'page-how-it-works.php'),
        'login'                => array('title' => 'Login',                'template' => 'page-login.php'),
        'privacy'              => array('title' => 'Privacy Policy',       'template' => 'page-privacy.php'),
        'refund-policy'        => array('title' => 'Refund Policy',        'template' => 'page-refund-policy.php'),
        'support'              => array('title' => 'Support',              'template' => 'page-support.php', 'content' => '[obenlo_support_page]'),
        'terms'                => array('title' => 'Terms of Service',     'template' => 'page-terms.php'),
        'trips'                => array('title' => 'Trips',                'template' => 'page-trips.php'),
        'trust-safety'         => array('title' => 'Trust & Safety',       'template' => 'page-trust-safety.php'),
        'wishlists'            => array('title' => 'Wishlists',            'template' => 'page-wishlists.php'),
        'host-dashboard'       => array('title' => 'Host Dashboard',       'template' => 'default',         'content' => '[obenlo_host_dashboard]'),
        'site-admin'           => array('title' => 'Site Admin',           'template' => 'default',         'content' => '[obenlo_admin_dashboard]'),
        'messages'             => array('title' => 'Messages',             'template' => 'default',         'content' => '[obenlo_messages_page]'),
        'broadcasts'           => array('title' => 'Broadcasts',           'template' => 'default',         'content' => '[obenlo_broadcasts_page]'),
    );

    foreach ($pages as $slug => $data) {
        // Use get_page_by_path which returns the page if it exists in ANY status (including trash/draft)
        if (!get_page_by_path($slug, OBJECT, 'page')) {
            wp_insert_post(array(
                'post_title'    => $data['title'],
                'post_name'     => $slug,
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => $data['content'] ?? '',
                'page_template' => (isset($data['template']) && $data['template'] !== 'default') ? $data['template'] : ''
            ));
        }
    }

    // Mark as completed so this block never runs again on this site
    update_option('obenlo_pages_restored_v167', time());
    flush_rewrite_rules();
});

/**
 * Filter Listing Visibility: Only show parent listings in standard shop/archive views.
 * This prevents sub-units (children) from appearing alongside their parent listing.
 *
 * Also fixes a WordPress URL rewrite conflict where /listings/?s=query incorrectly
 * inherits listing_type=stay from the URL pattern, which would restrict all text
 * searches to only the "Stay" category.
 */
function obenlo_filter_listing_children($query)
{
    if (!is_admin() && $query->is_main_query()) {

        // Always show only top-level (parent) listings in archive/search/taxonomy views
        if (is_post_type_archive('listing') || is_tax('listing_type') || is_search() || is_front_page()) {
            $query->set('post_parent', 0);
        }

        // Fix URL rewrite conflict: when a ?s= search is performed on the /listings/ archive,
        // WordPress incorrectly injects listing_type=stay from the URL rewrite rules.
        // If the user didn't explicitly choose a type via the URL (e.g. /type/stay/), clear it.
        if ($query->is_search() && isset($_GET['s']) && !is_tax('listing_type')) {
            $query->set('listing_type', '');
            $query->set('tax_query', array());
        }
    }
}
add_action('pre_get_posts', 'obenlo_filter_listing_children');

/**
 * --- WHITE LABELING & BRANDING ---
 * Removes all traces of WordPress branding for a premium experience.
 */

// 1. One-time cleanup of default content
add_action('init', function() {
    if (get_option('obenlo_initial_cleanup')) return;
    wp_delete_post(1, true); // Hello World
    wp_delete_post(2, true); // Sample Page
    wp_delete_comment(1, true); // Default Comment
    update_option('obenlo_initial_cleanup', 1);
});

// 2. Customize Login Page Branding
add_action('login_enqueue_scripts', function() {
    $logo = get_template_directory_uri() . '/assets/images/logo-social-profile.png';
    ?>
    <style type="text/css">
        #login h1 a {
            background-image: url('<?php echo esc_url($logo); ?>');
            background-size: contain;
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        body.login { background: #f9f9f9; }
        .login #login_error, .login .message, .login .success { border-left-color: #e61e4d; border-radius: 8px; }
        #loginform { border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .wp-core-ui .button-primary { background: #e61e4d !important; border-color: #e61e4d !important; border-radius: 8px; height: 40px; font-weight: 700; box-shadow: none; text-shadow: none; }
        .login #backtoblog a, .login #nav a { color: #666 !important; transition: color 0.2s; }
        .login #backtoblog a:hover, .login #nav a:hover { color: #e61e4d !important; }
    </style>
    <?php
});
add_filter('login_headerurl', function() { return home_url(); });
add_filter('login_headertext', function() { return 'Obenlo Platform'; });

// 3. Clean up the Admin Bar for all users
add_action('admin_bar_menu', function($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('about');
    $wp_admin_bar->remove_node('wporg');
    $wp_admin_bar->remove_node('documentation');
    $wp_admin_bar->remove_node('support-forums');
    $wp_admin_bar->remove_node('feedback');
}, 999);

// 4. Rename "Howdy" greeting
add_filter('gettext', function($translated_text, $text, $domain) {
    if ($text === 'Howdy, %1$s') {
        return 'Welcome, %1$s';
    }
    return $translated_text;
}, 10, 3);

// 5. Hide Dashboard Widgets for non-admins
add_action('wp_dashboard_setup', function() {
    if (!current_user_can('administrator')) {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    }
});

// 6. Footer White Labeling
add_filter('admin_footer_text', function() {
    return '<span>Management Hub provided by Obenlo Platform.</span>';
});
add_filter('update_footer', '__return_empty_string', 11);

// Temporary: Flush rewrite rules to activate the new /listings/ archive slug
add_action('init', 'flush_rewrite_rules', 999);

/**
 * --- DEMO PROFILE CLAIM LOGIC ---
 */

// Inject hidden field on the WP Registration form if 'claim_id' is in the URL
add_action('register_form', function() {
    $claim_id = isset($_GET['claim_id']) ? intval($_GET['claim_id']) : 0;
    if ($claim_id > 0) {
        echo '<input type="hidden" name="obenlo_claim_id" value="' . esc_attr($claim_id) . '">';
    }
});

// Process the claim when a new user registers
add_action('user_register', function($user_id) {
    if (isset($_POST['obenlo_claim_id']) && intval($_POST['obenlo_claim_id']) > 0) {
        $claim_listing_id = intval($_POST['obenlo_claim_id']);
        
        // 1. Mark the demo listing as "claim pending" so the button disappears
        update_post_meta($claim_listing_id, '_obenlo_claim_pending', 'yes');
        
        // 2. Fetch the newly registered user's metadata
        $user = get_userdata($user_id);
        
        // 3. Send email to the Admin
        $to = 'admin@obenlo.com';
        $subject = 'New Profile Claim Request - Obenlo Platform';
        
        $message = "A new user has requested to claim a demo profile/listing.\n\n";
        $message .= "User Details:\n";
        $message .= "Email: " . $user->user_email . "\n";
        $message .= "Username: " . $user->user_login . "\n\n";
        
        $message .= "Listing/Profile Details:\n";
        $message .= "Listing Title: " . get_the_title($claim_listing_id) . "\n";
        $message .= "Demo Host Name: " . get_post_meta($claim_listing_id, '_obenlo_demo_host_name', true) . "\n";
        $message .= "Listing URL: " . get_permalink($claim_listing_id) . "\n\n";
        
        $message .= "Please review this request in the backend to transfer ownership of the listing/profile to this user.";
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
});
