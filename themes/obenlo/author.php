<?php
/**
 * The template for displaying author (Host Storefront) pages.
 */

get_header();
?>
<style>
.storefront-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 40px;
    align-items: start;
}
@media (max-width: 768px) {
    .storefront-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php

$curauth = get_queried_object();

if (!$curauth || !is_a($curauth, 'WP_User')) {
    // If we're not on a valid author page, try to get from query var or fall back to 404
    $author_name = get_query_var('author_name');
    if ($author_name) {
        $curauth = get_user_by('slug', $author_name);
    }
}

if (!$curauth) {
    // Redirect or show 404 if author not found to avoid blank page
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit;
}

$user_id = $curauth->ID;

$store_name = get_user_meta($user_id, 'obenlo_store_name', true) ?: $curauth->display_name;
$store_desc = get_user_meta($user_id, 'obenlo_store_description', true) ?: $curauth->description;
$store_location = get_user_meta($user_id, 'obenlo_store_location', true);
$business_hours = get_user_meta($user_id, '_obenlo_business_hours', true);
$store_logo_id = get_user_meta($user_id, 'obenlo_store_logo', true);
$store_banner_id = get_user_meta($user_id, 'obenlo_store_banner', true);

$banner_url = $store_banner_id ? wp_get_attachment_image_url($store_banner_id, 'full') : '';
$logo_url = $store_logo_id ? wp_get_attachment_image_url($store_logo_id, 'thumbnail') : get_avatar_url($user_id, ['size' => 150]);

$host_avg_rating = 0;
$host_review_count = 0;

if (class_exists('Obenlo_Booking_Reviews')) {
    $host_avg_rating = Obenlo_Booking_Reviews::get_host_average_rating($user_id);
    $host_review_count = Obenlo_Booking_Reviews::get_host_review_count($user_id);
}

$host_badges = array();
if (class_exists('Obenlo_Booking_Badges')) {
    $host_badges = Obenlo_Booking_Badges::get_host_badges($user_id);
}
?>

<div class="storefront-hero" style="<?php echo $banner_url ? 'background: url(' . esc_url($banner_url) . ') center/cover;' : 'background: #333;'; ?> height: 300px; position:relative; display:flex; align-items:flex-end; padding:20px;">
    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.8), transparent);"></div>
    <div class="storefront-hero-content" style="position:relative; z-index:1; display:flex; align-items:center; gap:20px; color:white; max-width:1200px; margin:0 auto; width:100%;">
        <div style="width:120px; height:120px; border-radius:50%; border:4px solid white; overflow:hidden; background:white; flex-shrink:0;">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store_name); ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <h1 style="color:white; margin:0; text-shadow:0 2px 4px rgba(0,0,0,0.5); font-size: 2.8rem;"><?php echo esc_html($store_name); ?></h1>
                <?php if (!empty($host_badges)): ?>
                    <div class="host-badges" style="display:flex; gap:8px;">
                        <?php foreach ($host_badges as $badge): ?>
                            <div class="host-badge" title="<?php echo esc_attr($badge['desc']); ?>" style="background: <?php echo esc_attr($badge['color']); ?>; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); cursor: help;">
                                <span><?php echo $badge['icon']; ?></span>
                                <span><?php echo esc_html($badge['label']); ?></span>
                            </div>
                        <?php
    endforeach; ?>
                    </div>
                <?php
endif; ?>
            </div>
            <div style="font-size:1.1em; opacity:0.9; display:flex; align-items:center; gap:8px;">
                <?php if (get_the_author_meta('obenlo_hosted_by') !== ''): ?>
                    <span>Hosted by <?php echo esc_html(get_the_author_meta('obenlo_hosted_by')); ?></span>
                <?php
else: ?>
                    <span>Hosted by <?php echo esc_html($curauth->display_name); ?></span>
                <?php
endif; ?>
                
                <?php if ($store_location): ?>
                    <span>&bull;</span>
                    <span style="display:inline-flex; align-items:center; gap:4px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?php echo esc_html($store_location); ?>
                    </span>
                <?php
endif; ?>
                
                <?php if ($host_avg_rating > 0): ?>
                    <span>&bull;</span>
                    <span style="display:flex; align-items:center; gap:4px;">
                        <span style="color:#FFD700;">★</span>
                        <strong><?php echo $host_avg_rating; ?></strong>
                        <span style="font-size:0.9em;">(<?php echo $host_review_count; ?> reviews)</span>
                    </span>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="obenlo-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    
    <div class="storefront-grid">
        <div>
            <?php if ($store_desc): ?>
            <div class="storefront-about" style="margin-bottom:40px; font-size:1.1em; line-height:1.6;">
                <h2>About</h2>
                <p><?php echo nl2br(esc_html($store_desc)); ?></p>
            </div>
            <hr style="border:none; border-top:1px solid #eee; margin-bottom:40px;">
            <?php
endif; ?>

            <div class="storefront-listings">
                <h2>Listings by <?php echo esc_html($store_name); ?></h2>
        
        <?php
$listings_query = new WP_Query(array(
    'post_type' => 'listing',
    'author' => $user_id,
    'post_parent' => 0, // Top level only
    'posts_per_page' => -1
));
?>

        <?php if ($listings_query->have_posts()): ?>
            <div class="listings-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-top:20px;">
                <?php while ($listings_query->have_posts()):
        $listings_query->the_post();
        $price = get_post_meta(get_the_ID(), '_obenlo_price', true);
        $type_terms = wp_get_post_terms(get_the_ID(), 'listing_type', array('fields' => 'names'));
        $type = (!is_wp_error($type_terms) && !empty($type_terms)) ? implode(', ', $type_terms) : '';
?>
                    <article class="listing-card" style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; transition: transform 0.2s; background: #fff;">
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit; display: block;">
                            <div class="listing-thumbnail" style="height: 200px; background: #f0f0f0;">
                                <?php
        if (has_post_thumbnail()) {
            the_post_thumbnail('medium', array('style' => 'width: 100%; height: 100%; object-fit: cover; display: block;'));
        }
?>
                            </div>
                            <div class="listing-info" style="padding: 15px;">
                                <?php if ($type): ?>
                                    <div style="font-size: 0.8em; color: #666; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($type); ?></div>
                                <?php
        endif; ?>
                                <h3 style="margin: 0 0 10px 0; font-size: 1.2em;"><?php the_title(); ?></h3>
                                <div style="font-weight: bold; color: #e61e4d;">
                                   Starting from $<?php echo esc_html($price); ?>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php
    endwhile;
    wp_reset_postdata(); ?>
            </div>
        <?php
else: ?>
            <p>This host has no active listings at the moment.</p>
        <?php
endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="background:#fff; border:1px solid #eee; border-radius:12px; padding:25px; position:sticky; top:20px;">
            <h3 style="margin-top:0; margin-bottom:20px; font-size:1.2rem;">Business Hours</h3>
            <?php if (!empty($business_hours) && is_array($business_hours)): ?>
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; font-size:0.95rem;">
                    <?php
    $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    foreach ($days as $day):
        if (!isset($business_hours[$day]))
            continue;
        $conf = $business_hours[$day];
?>
                        <li style="display:flex; justify-content:space-between; padding-bottom:10px; border-bottom:1px solid #f9f9f9;">
                            <span style="text-transform:capitalize; <?php echo $conf['active'] !== 'yes' ? 'color:#999;' : 'font-weight:600;'; ?>"><?php echo esc_html($day); ?></span>
                            <?php if ($conf['active'] === 'yes'): ?>
                                <span><?php echo esc_html(date('g:i A', strtotime($conf['start']))) . ' - ' . esc_html(date('g:i A', strtotime($conf['end']))); ?></span>
                            <?php
        else: ?>
                                <span style="color:#999; font-style:italic;">Closed</span>
                            <?php
        endif; ?>
                        </li>
                    <?php
    endforeach; ?>
                </ul>
            <?php
else: ?>
                <p style="color:#666; font-size:0.9rem;">Business hours not specified.</p>
            <?php
endif; ?>
        </div>
    </div>

</div>

<?php get_footer(); ?>
