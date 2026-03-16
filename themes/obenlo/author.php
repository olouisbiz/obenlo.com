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

if (!$curauth) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit;
}

$user_id = $curauth->ID;

// Meta Data
$store_name = get_user_meta($user_id, 'obenlo_store_name', true) ?: $curauth->display_name;
$store_desc = get_user_meta($user_id, 'obenlo_store_description', true) ?: $curauth->description;
$store_location = get_user_meta($user_id, 'obenlo_store_location', true);
$store_tagline = get_user_meta($user_id, 'obenlo_store_tagline', true);
$store_video = get_user_meta($user_id, 'obenlo_store_video', true);
$insta = get_user_meta($user_id, 'obenlo_instagram', true);
$fb = get_user_meta($user_id, 'obenlo_facebook', true);
$specialties = get_user_meta($user_id, 'obenlo_specialties', true);
$business_hours = get_user_meta($user_id, '_obenlo_business_hours', true);
$store_logo_id = get_user_meta($user_id, 'obenlo_store_logo', true);
$store_banner_id = get_user_meta($user_id, 'obenlo_store_banner', true);

$banner_url = $store_banner_id ? wp_get_attachment_image_url($store_banner_id, 'full') : 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000';
$logo_url = $store_logo_id ? wp_get_attachment_image_url($store_logo_id, 'thumbnail') : get_avatar_url($user_id, ['size' => 150]);

$host_avg_rating = 0;
$host_review_count = 0;
if (class_exists('Obenlo_Booking_Reviews')) {
    $host_avg_rating = Obenlo_Booking_Reviews::get_host_average_rating($user_id);
    $host_review_count = Obenlo_Booking_Reviews::get_host_review_count($user_id);
}

$hosting_since = date('Y', strtotime($curauth->user_registered));
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
        height: 550px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-banner {
        position: absolute;
        inset: 0;
        background: url('<?php echo esc_url($banner_url); ?>') center/cover no-repeat;
        filter: brightness(0.85);
        transition: transform 0.8s ease-out;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%);
    }

    .hero-container {
        position: relative;
        z-index: 10;
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 0 40px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .hero-text h1 {
        font-size: 4rem;
        font-weight: 900;
        color: white;
        margin: 0;
        line-height: 1.1;
        text-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .hero-tagline {
        font-size: 1.5rem;
        color: rgba(255,255,255,0.9);
        margin-top: 20px;
        font-weight: 500;
    }

    /* Glass Card */
    .host-glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 32px;
        padding: 40px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .host-logo-large {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-top: -110px;
        margin-bottom: 20px;
        background: white;
        object-fit: cover;
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
        .hero-container { grid-template-columns: 1fr; gap: 40px; padding-top: 60px; }
        .hero-text h1 { font-size: 3rem; text-align: center; }
        .hero-tagline { text-align: center; }
        .host-glass-card { margin-bottom: 40px; }
    }
</style>

<div class="storefront-wrapper">
    <!-- Hero Section -->
    <div class="premium-hero">
        <div class="hero-banner"></div>
        <div class="hero-overlay"></div>
        <div class="hero-container">
            <div class="hero-text">
                <h1><?php echo esc_html($store_name); ?></h1>
                <?php if($store_tagline): ?>
                    <div class="hero-tagline"><?php echo esc_html($store_tagline); ?></div>
                <?php endif; ?>
                
                <div style="margin-top:30px; display:flex; gap:15px;">
                    <?php if($insta): ?>
                        <a href="https://instagram.com/<?php echo esc_attr(str_replace('@', '', $insta)); ?>" target="_blank" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(10px); width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; border:1px solid rgba(255,255,255,0.3);">IG</a>
                    <?php endif; ?>
                    <?php if($fb): ?>
                        <a href="<?php echo esc_url($fb); ?>" target="_blank" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(10px); width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; border:1px solid rgba(255,255,255,0.3);">FB</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="host-glass-card">
                <img src="<?php echo esc_url($logo_url); ?>" alt="" class="host-logo-large">
                <h2 style="margin:0; font-size:1.8rem; font-weight:800; color:#1a1a1a;">
                    <?php echo esc_html($store_name); ?>
                </h2>
                <p style="color:#666; margin:10px 0 20px; font-weight:500;">
                    <?php if($store_location): ?>
                        📍 <?php echo esc_html($store_location); ?>
                    <?php endif; ?>
                </p>
                <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:10px;">
                    <span style="background:#fef2f2; color:#e61e4d; padding:6px 15px; border-radius:50px; font-weight:800; font-size:0.75rem;">VERIFIED HOST</span>
                    <span style="background:#eff6ff; color:#3b82f6; padding:6px 15px; border-radius:50px; font-weight:800; font-size:0.75rem;">FAST RESPONDER</span>
                    <span style="background:#f0fdf4; color:#22c55e; padding:6px 15px; border-radius:50px; font-weight:800; font-size:0.75rem;">TOP RATED</span>
                </div>

                <?php if($specialties): ?>
                    <div style="margin-top:20px; padding-top:20px; border-top:1px solid rgba(0,0,0,0.05); width:100%;">
                        <div style="font-size:0.7rem; color:#aaa; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; font-weight:700;">Host Specialties</div>
                        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:8px;">
                            <?php foreach(explode(',', $specialties) as $spec): ?>
                                <span style="font-size:0.85rem; color:#444; font-weight:600;">• <?php echo esc_html(trim($spec)); ?></span>
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
            $listings = new WP_Query(array(
                'post_type' => 'listing',
                'author' => $user_id,
                'post_parent' => 0,
                'posts_per_page' => -1
            ));

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
