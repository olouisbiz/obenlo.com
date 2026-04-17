<?php
/**
 * The template for displaying author (Host Storefront) pages.
 * Overhauled to a premium landing page experience.
 */

get_header();

$curauth = get_queried_object();
if (!$curauth || !is_a($curauth, 'WP_User')) {
    $author_name = get_query_var('author_name');
    if ($author_name) $curauth = get_user_by('slug', $author_name);
}

$user_id = $curauth ? $curauth->ID : 0;

// Demo Preview Overrides
$demo_listing_id = get_query_var('demo_listing_id') ?: (isset($_GET['demo_listing_id']) ? intval($_GET['demo_listing_id']) : 0);
$demo_mode_param = get_query_var('demo_listing_mode') ?: (isset($_GET['demo_mode']) ? 1 : 0);
$demo_host_slug = get_query_var('demo_host_name');
$demo_meta = [];

// New: Check if the user ID itself belongs to a virtual demo account
$is_virtual_demo_user = get_user_meta($user_id, '_obenlo_is_demo_account', true) === 'yes';

// If no specific ID but demo host slug is provided
if (!$demo_listing_id && $demo_host_slug) {
    // Attempt to find a listing where the demo host name matches the slug
    $demo_post = get_posts(array(
        'post_type' => 'listing',
        'meta_query' => array(
            array('key' => '_obenlo_is_demo', 'value' => 'yes'),
            array(
                'relation' => 'OR',
                array('key' => '_obenlo_demo_hidden', 'compare' => 'NOT EXISTS'),
                array('key' => '_obenlo_demo_hidden', 'value' => 'yes', 'compare' => '!=')
            )
        ),
        'posts_per_page' => 20, // Check multiple
        'post_parent' => 0
    ));
    
    if ($demo_post) {
        foreach($demo_post as $p) {
            $h_name = get_post_meta($p->ID, '_obenlo_demo_host_name', true);
            if (sanitize_title($h_name) === $demo_host_slug) {
                $demo_listing_id = $p->ID;
                break;
            }
        }
    }
}

// If it's a virtual demo user but we don't have a listing yet
if (!$demo_listing_id && $is_virtual_demo_user && $user_id) {
    $demo_post = get_posts(array(
        'post_type' => 'listing',
        'author' => $user_id,
        'posts_per_page' => 1,
        'post_parent' => 0
    ));
    if ($demo_post) $demo_listing_id = $demo_post[0]->ID;
}

// If still no ID but demo mode is on for a user
if (!$demo_listing_id && $demo_mode_param && $user_id) {
    // Search for demo listings authored by this user
    $demo_post = get_posts(array(
        'post_type' => 'listing',
        'author' => $user_id,
        'meta_query' => array(
            array('key' => '_obenlo_is_demo', 'value' => 'yes'),
            array(
                'relation' => 'OR',
                array('key' => '_obenlo_demo_hidden', 'compare' => 'NOT EXISTS'),
                array('key' => '_obenlo_demo_hidden', 'value' => 'yes', 'compare' => '!=')
            )
        ),
        'posts_per_page' => 1,
        'post_parent' => 0
    ));
    
    if ($demo_post) {
        $demo_listing_id = $demo_post[0]->ID;
    }
}
if ($is_virtual_demo_user) $is_demo_preview = true;

if ($demo_listing_id && get_post_meta($demo_listing_id, '_obenlo_is_demo', true) === 'yes') {
    $is_demo_hidden = get_post_meta($demo_listing_id, '_obenlo_demo_hidden', true) === 'yes';
    if ($is_demo_hidden && !current_user_can('administrator')) {
        wp_die('This storefront is currently private.', 'Private Storefront', array('response' => 404));
    }
    
    $is_demo_preview = true;
    $demo_host_name = get_post_meta($demo_listing_id, '_obenlo_demo_host_name', true);
    $demo_meta = [
        'name' => get_post_meta($demo_listing_id, '_obenlo_demo_host_name', true),
        'bio' => get_post_meta($demo_listing_id, '_obenlo_demo_host_bio', true),
        'location' => get_post_meta($demo_listing_id, '_obenlo_demo_host_location', true),
        'tagline' => get_post_meta($demo_listing_id, '_obenlo_demo_host_tagline', true),
        'insta' => get_post_meta($demo_listing_id, '_obenlo_demo_host_instagram', true),
        'fb' => get_post_meta($demo_listing_id, '_obenlo_demo_host_facebook', true),
        'specialties' => get_post_meta($demo_listing_id, '_obenlo_demo_host_specialties', true),
    ];
}

// Meta Data (Standard)
$store_name = ($is_demo_preview && !empty($demo_meta['name'])) ? $demo_meta['name'] : (get_user_meta($user_id, 'obenlo_store_name', true) ?: ($curauth ? $curauth->display_name : 'Obe Louis'));
$store_desc = ($is_demo_preview && !empty($demo_meta['bio'])) ? $demo_meta['bio'] : (get_user_meta($user_id, 'obenlo_store_description', true) ?: ($curauth ? $curauth->description : 'High-end host on Obenlo.'));
$store_location = ($is_demo_preview && !empty($demo_meta['location'])) ? $demo_meta['location'] : get_user_meta($user_id, 'obenlo_store_location', true);
$store_tagline = ($is_demo_preview && !empty($demo_meta['tagline'])) ? $demo_meta['tagline'] : get_user_meta($user_id, 'obenlo_store_tagline', true);

$store_video = get_user_meta($user_id, 'obenlo_store_video', true);
$insta = ($is_demo_preview && !empty($demo_meta['insta'])) ? $demo_meta['insta'] : get_user_meta($user_id, 'obenlo_instagram', true);
$fb = ($is_demo_preview && !empty($demo_meta['fb'])) ? $demo_meta['fb'] : get_user_meta($user_id, 'obenlo_facebook', true);
$specialties = ($is_demo_preview && !empty($demo_meta['specialties'])) ? $demo_meta['specialties'] : get_user_meta($user_id, 'obenlo_specialties', true);
$business_hours = ($is_demo_preview) ? get_post_meta($demo_listing_id, '_obenlo_demo_business_hours', true) : get_user_meta($user_id, '_obenlo_business_hours', true);
$store_logo_id = get_user_meta($user_id, 'obenlo_store_logo', true);
$store_banner_id = get_user_meta($user_id, 'obenlo_store_banner', true);

// Banner/Logo Overrides for Demo
if ($is_demo_preview) {
    $demo_logo_id = get_post_meta($demo_listing_id, '_obenlo_demo_host_logo', true);
    $demo_banner_id = get_post_meta($demo_listing_id, '_obenlo_demo_host_banner', true);

    $logo_url = $demo_logo_id ? wp_get_attachment_image_url($demo_logo_id, 'thumbnail') : 'https://images.unsplash.com/photo-1599305090598-fe179d501227?auto=format&fit=crop&q=80&w=200';
    $banner_url = $demo_banner_id ? wp_get_attachment_image_url($demo_banner_id, 'full') : 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=2000';
} else {
    $banner_url = $store_banner_id ? wp_get_attachment_image_url($store_banner_id, 'full') : 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000';
    $logo_url = $store_logo_id ? wp_get_attachment_image_url($store_logo_id, 'thumbnail') : get_avatar_url($user_id, ['size' => 150]);
}

$host_avg_rating = 0;
$host_review_count = 0;
if (class_exists('Obenlo_Booking_Reviews')) {
    $host_avg_rating = Obenlo_Booking_Reviews::get_host_average_rating($user_id);
    $host_review_count = Obenlo_Booking_Reviews::get_host_review_count($user_id);
}

// In Demo Preview, simulate high ratings
if ($is_demo_preview && ($user_id === 1 || $user_id === 0)) { 
    $host_avg_rating = 5.0;
    $host_review_count = 12;
}

$hosting_since = ($curauth && !empty($curauth->user_registered)) ? date('Y', strtotime($curauth->user_registered)) : '2024';
if ($is_demo_preview) $hosting_since = 2024;
?>

<style>
    :root {
        --primary-color: #e61e4d;
        --glass-bg: rgba(255, 255, 255, 0.75);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .storefront-wrapper {
        background: #fafafa;
        min-height: 100vh;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Hero Section */
    .premium-hero {
        height: 500px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        padding-top: 60px;
    }
    .hero-banner {
        position: absolute;
        inset: 0;
        background: url('<?php echo esc_url($banner_url); ?>') center/cover no-repeat;
        filter: brightness(0.7);
        transition: transform 0.8s ease-out;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.4) 100%);
    }

    .hero-back-link {
        position: absolute;
        top: 30px;
        left: 40px;
        z-index: 100;
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        background: rgba(0,0,0,0.3);
        padding: 8px 16px;
        border-radius: 50px;
        backdrop-filter: blur(10px);
        transition: all 0.2s;
    }
    .hero-back-link:hover {
        background: rgba(0,0,0,0.5);
        transform: translateX(-5px);
    }

    .hero-container {
        position: relative;
        z-index: 10;
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 0 40px;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 60px;
        align-items: center;
    }

    .hero-text h1 {
        font-size: 3.2rem;
        font-weight: 900;
        color: white;
        margin: 0;
        line-height: 1.1;
        text-shadow: 0 4px 30px rgba(0,0,0,0.5);
    }
    .hero-tagline {
        font-size: 1.4rem;
        color: rgba(255,255,255,0.95);
        margin-top: 15px;
        font-weight: 500;
        max-width: 500px;
        line-height: 1.4;
    }

    /* Glass Card */
    .host-glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 28px;
        padding: 20px 25px 25px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        max-width: 320px;
    }

    .host-logo-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        margin-top: -65px;
        margin-bottom: 12px;
        background: white;
        object-fit: cover;
    }

    /* Social Icons Styling */
    .social-glass-bubble {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white !important;
        border: 1.5px solid rgba(255, 255, 255, 0.4);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .social-glass-bubble svg {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }
    .social-glass-bubble:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: scale(1.15) translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        border-color: #fff;
    }

    /* Badge/Pill Styling */
    .specialty-pill {
        background: rgba(0, 0, 0, 0.05);
        color: #1a1a1a;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid rgba(0,0,0,0.02);
        transition: all 0.25s ease;
        letter-spacing: 0.3px;
    }
    .specialty-pill:hover {
        background: rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    
    .contact-host-btn {
        margin-top: 20px;
        background: linear-gradient(135deg, #FF385C 0%, #E61E4D 100%);
        color: #fff !important;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 8px 25px rgba(230,30,77,0.35);
        text-decoration: none;
    }
    .contact-host-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(230,30,77,0.45);
        filter: brightness(1.1);
    }
    .contact-host-btn:active {
        transform: translateY(1px);
    }

    /* Stats Bar */
    .premium-stats {
        display: flex;
        justify-content: center;
        gap: 40px;
        background: white;
        padding: 30px;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-top: -50px;
        position: relative;
        z-index: 20;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }
    .stat-item { text-align: center; }
    .stat-val { display: block; font-size: 1.6rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px; }
    .stat-lbl { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; }

    /* Tabs */
    .storefront-nav {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin: 60px 0 40px;
        border-bottom: 1px solid #eee;
    }
    .nav-tab {
        padding: 15px 10px;
        font-weight: 700;
        color: #666;
        text-decoration: none;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
        cursor: pointer;
    }
    .nav-tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .tab-content { display: none; animation: fadeIn 0.5s ease; }
    .tab-content.active { display: block; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Listing Grid Overhaul */
    .premium-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
    }
    .premium-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid #f0f0f0;
    }
    .premium-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.1); }
    .card-img { height: 260px; position: relative; }
    .card-badge { position: absolute; top: 15px; left: 15px; background: white; padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.75rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .card-price { position: absolute; bottom: 15px; right: 15px; background: var(--primary-color); color: white; padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 1.1rem; }

    @media (max-width: 992px) {
        .premium-hero { height: auto; min-height: 450px; padding: 120px 0 60px; }
        .hero-back-link { top: 20px; left: 20px; font-size: 0.85rem; padding: 6px 12px; }
        .hero-container { grid-template-columns: 1fr; gap: 80px; padding: 0 24px; }
        .hero-text h1 { font-size: 2.5rem; text-align: center; }
        .hero-tagline { text-align: center; font-size: 1.1rem; margin: 15px auto 0; }
        .host-glass-card { margin-top: 0; padding: 40px 20px 30px; }
        .host-logo-large { margin-top: -95px; width: 120px; height: 120px; border-width: 4px; }
        .premium-stats { flex-direction: column; gap: 20px; margin-top: 20px; padding: 20px; }
        .stat-item { padding: 0 !important; border: none !important; }
    }
</style>

<div class="storefront-wrapper">
    <!-- Hero Section -->
    <div class="premium-hero">
        <div class="hero-banner"></div>
        <div class="hero-overlay"></div>
        
        <a href="<?php echo esc_url(home_url('/listings/')); ?>" class="hero-back-link">
            &larr; Back to Listings
        </a>

        <div class="hero-container">
            <div class="hero-text">
                <h1><?php echo esc_html($store_name); ?></h1>
                <?php if($store_tagline): ?>
                    <div class="hero-tagline"><?php echo esc_html($store_tagline); ?></div>
                <?php endif; ?>
                
                <div style="margin-top:30px; display:flex; gap:15px;">
                    <?php if($insta): ?>
                        <a href="https://instagram.com/<?php echo esc_attr(str_replace('@', '', $insta)); ?>" target="_blank" class="social-glass-bubble" title="Instagram">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.332 3.608 1.308.975.975 1.245 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.063 1.366-.333 2.633-1.308 3.608-.975.975-2.242 1.245-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.063-2.633-.333-3.608-1.308-.975-.975-1.245-2.242-1.308-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.332-2.633 1.308-3.608.975-.975 2.242-1.245 3.608-1.308 1.266-.058 1.646-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.058-1.281.072-1.689.072-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.28-.058-1.688-.072-4.947-.072zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if($fb): ?>
                        <a href="<?php echo esc_url($fb); ?>" target="_blank" class="social-glass-bubble" title="Facebook">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="host-glass-card">
                <img src="<?php echo esc_url($logo_url); ?>" alt="" class="host-logo-large">
                <h2 style="margin:0; font-size:1.4rem; font-weight:800; color:#1a1a1a;">
                    <?php echo esc_html($store_name); ?>
                </h2>
                <p style="color:#666; margin:8px 0 15px; font-weight:500; font-size:0.95rem;">
                    <?php if($store_location): ?>
                        📍 <?php echo esc_html($store_location); ?>
                    <?php endif; ?>
                </p>
                <?php echo Obenlo_Booking_Badges::render_badges_html($user_id, 'storefront'); ?>

                <button onclick="<?php if(is_user_logged_in()): ?>if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $user_id; ?>, '<?php echo esc_js($store_name); ?>', '<?php echo esc_url($logo_url); ?>');} <?php else: ?>window.obenloOpenGuestContact(<?php echo $user_id; ?>, '<?php echo esc_js($store_name); ?>', '<?php echo esc_url($logo_url); ?>');<?php endif; ?>"
                        class="contact-host-btn">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Contact Host
                </button>

                <?php 
                if ($is_demo_preview && get_post_meta($demo_listing_id, '_obenlo_claim_pending', true) !== 'yes') {
                    $claim_url = home_url('/login?claim_id=' . $demo_listing_id . '#signup');
                    echo '<a href="' . esc_url($claim_url) . '" style="display:inline-block; margin-top: 15px; background: transparent; color: #e61e4d; border: 2px solid #e61e4d; padding: 10px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; transition: all 0.2s ease;">';
                    echo 'Is this your profile? Claim it';
                    echo '</a>';
                }
                ?>

                <?php if($specialties): ?>
                    <div style="margin-top:15px; padding-top:15px; border-top:1px solid rgba(0,0,0,0.05); width:100%;">
                        <div style="font-size:0.7rem; color:#aaa; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; font-weight:700;">Host Specialties</div>
                        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px;">
                            <?php 
                            $spec_list = array_filter(array_map('trim', explode(',', $specialties)));
                            foreach($spec_list as $spec): 
                            ?>
                                <span class="specialty-pill"><?php echo esc_html($spec); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="premium-stats">
        <div class="stat-item">
            <span class="stat-val"><?php echo $host_avg_rating ? number_format($host_avg_rating, 1) : '5.0'; ?> ★</span>
            <span class="stat-lbl"><?php echo $host_review_count; ?> REVIEWS</span>
        </div>
        <div class="stat-item" style="border-left:1px solid #eee; border-right:1px solid #eee; padding: 0 40px;">
            <span class="stat-val"><?php echo (date('Y') - $hosting_since) ?: '1'; ?>+</span>
            <span class="stat-lbl">YEARS HOSTING</span>
        </div>
        <div class="stat-item">
            <span class="stat-val">< 1 hour</span>
            <span class="stat-lbl">RESPONSE TIME</span>
        </div>
    </div>

    <div class="obenlo-container" style="max-width:1200px; margin:0 auto; padding:0 40px;">
        <!-- Navigation -->
        <nav class="storefront-nav">
            <a class="nav-tab active" data-tab="store">Storefront</a>
            <a class="nav-tab" data-tab="about">About Host</a>
            <?php if($host_review_count > 0): ?>
                <a class="nav-tab" data-tab="reviews">Reviews (<?php echo $host_review_count; ?>)</a>
            <?php endif; ?>
        </nav>

        <!-- Store Tab -->
        <div id="tab-store" class="tab-content active">
            <?php
            $query_args = array(
                'post_type' => 'listing',
                'author' => $user_id,
                'post_parent' => 0,
                'posts_per_page' => -1,
                'suppress_filters' => false,
            );

            if ($is_demo_preview) {
                // Show all demo listings for this host (based on host name if available, else author)
                $demo_host_name = get_post_meta($demo_listing_id, '_obenlo_demo_host_name', true);
                
                if ($demo_host_name) {
                    // Find all listings associated with this demo host name
                    $demo_listings = get_posts(array(
                        'post_type' => 'listing',
                        'meta_query' => array(
                            array('key' => '_obenlo_demo_host_name', 'value' => $demo_host_name)
                        ),
                        'fields' => 'ids',
                        'posts_per_page' => -1
                    ));
                    
                    // Also find children of these demo parents
                    if (!empty($demo_listings)) {
                        $children = get_posts(array(
                            'post_type' => 'listing',
                            'post_parent__in' => $demo_listings,
                            'fields' => 'ids',
                            'posts_per_page' => -1
                        ));
                        $demo_listings = array_merge($demo_listings, $children);
                    }
                    
                    if (!empty($demo_listings)) {
                        unset($query_args['author']);
                        $query_args['post__in'] = array_unique($demo_listings);
                        unset($query_args['post_parent']); // Show children too if they are bookable
                    }
                } else {
                    // Fallback to author match if no name set
                    $query_args['meta_query'] = array(
                        array('key' => '_obenlo_is_demo', 'value' => 'yes')
                    );
                    unset($query_args['post_parent']);
                }
            } else {
                // Filter out demo listings from standard view
                $query_args['meta_query'] = array(
                    array(
                        'key' => '_obenlo_is_demo',
                        'compare' => 'NOT EXISTS'
                    )
                );
            }

            $listings = new WP_Query($query_args);

            if ($listings->have_posts()):
                echo '<div class="premium-grid">';
                while ($listings->have_posts()): $listings->the_post();
                    $price = get_post_meta(get_the_ID(), '_obenlo_price', true);
                    $type_terms = wp_get_post_terms(get_the_ID(), 'listing_type', array('fields' => 'names'));
                    $type = !empty($type_terms) ? $type_terms[0] : 'Listing';
                    ?>
                    <article class="premium-card">
                        <a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;">
                            <div class="card-img">
                                <?php if(has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('large', array('style' => 'width:100%; height:100%; object-fit:cover;')); ?>
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:#f0f0f0;"></div>
                                <?php endif; ?>
                                <span class="card-badge"><?php echo esc_html($type); ?></span>
                                <span class="card-price">$<?php echo esc_html($price); ?></span>
                            </div>
                            <div style="padding:25px;">
                                <h4 style="margin:0 0 10px; font-size:1.3rem; font-weight:800;"><?php the_title(); ?></h4>
                                <div style="display:flex; align-items:center; gap:10px; color:#888; font-size:0.9rem;">
                                    <span style="color:#ffd700;">★★★★★</span>
                                    <span>(New)</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endwhile;
                echo '</div>';
                wp_reset_postdata();
            else:
                echo '<div style="text-align:center; padding:100px 0; color:#888;"><h3>No active listings found in this store.</h3></div>';
            endif; ?>
        </div>

        <!-- About Tab -->
        <div id="tab-about" class="tab-content">
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:60px; padding:20px 0;">
                <div>
                    <h3 style="font-size:1.8rem; font-weight:900; margin-bottom:25px;">Our Story</h3>
                    <p style="font-size:1.15rem; line-height:1.8; color:#444;"><?php echo nl2br(esc_html($store_desc)); ?></p>
                    
                    <?php if($store_video): ?>
                        <div style="margin-top:40px; border-radius:24px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.1);">
                            <?php 
                            // Simple embed logic for YouTube/Vimeo
                            if(strpos($store_video, 'youtube.com') !== false || strpos($store_video, 'youtu.be') !== false) {
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $store_video, $matches);
                                if($matches[1]) echo '<iframe width="100%" height="450" src="https://www.youtube.com/embed/'.$matches[1].'" frameborder="0" allowfullscreen></iframe>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div style="background:white; border-radius:24px; padding:30px; border:1px solid #eee; box-shadow:0 10px 30px rgba(0,0,0,0.03);">
                        <h4 style="margin-top:0; margin-bottom:20px;">Business Hours</h4>
                        <?php if(!empty($business_hours) && is_array($business_hours)): ?>
                            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
                                <?php foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day):
                                    $conf = $business_hours[$day]; ?>
                                    <li style="display:flex; justify-content:space-between; font-size:0.95rem; <?php echo $conf['active'] !== 'yes' ? 'opacity:0.4;' : 'font-weight:600;'; ?>">
                                        <span style="text-transform:capitalize;"><?php echo $day; ?></span>
                                        <span><?php echo $conf['active'] === 'yes' ? esc_html($conf['start'].' - '.$conf['end']) : 'Closed'; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color:#888;">Not specified</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <?php if($host_review_count > 0): ?>
            <div id="tab-reviews" class="tab-content">
                <div style="padding:20px 0;">
                    <h3 style="font-size:1.8rem; font-weight:900; margin-bottom:40px;">Host Reviews</h3>
                    <!-- Placeholder for actual review implementation matching standard Obenlo review styling -->
                    <p style="color:#666;">Showing the latest feedback from guests...</p>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById('tab-' + target).classList.add('active');
            
            // Scroll a bit if necessary
            if(window.innerWidth < 768) {
                document.querySelector('.storefront-nav').scrollIntoView({behavior: 'smooth'});
            }
        });
    });
    
    // Parallax effect on scroll
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const banner = document.querySelector('.hero-banner');
        if(banner) {
            banner.style.transform = `translateY(${scrolled * 0.4}px)`;
        }
    });
});
</script>

<?php get_footer(); ?>
