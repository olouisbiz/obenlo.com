<?php
/**
 * The template for displaying listing type archives.
 */

get_header(); ?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 40px 40px;">

    <?php /* ── MOBILE: Search pill + category row for taxonomy archives ── */ ?>
    <div class="mobile-search-wrapper">
        <?php
        $search_url = home_url('/?s=');
        $srv_link  = get_term_link('service',    'listing_type'); $srv_link  = is_wp_error($srv_link)  ? home_url('/') : $srv_link;
        $evt_link  = get_term_link('event',      'listing_type'); $evt_link  = is_wp_error($evt_link)  ? home_url('/') : $evt_link;
        $exp_link  = get_term_link('experience', 'listing_type'); $exp_link  = is_wp_error($exp_link)  ? home_url('/') : $exp_link;
        $stay_link = get_term_link('stay',       'listing_type'); $stay_link = is_wp_error($stay_link) ? home_url('/') : $stay_link;
        $current_term = get_queried_object();
        $current_slug = $current_term ? $current_term->slug : '';
        ?>
        <a href="<?php echo esc_url($search_url); ?>" class="mobile-search-pill" id="mobile-search-pill-tax" aria-label="<?php esc_attr_e('Search listings', 'obenlo'); ?>">
            <span class="mobile-search-pill-content">
                <span class="mobile-search-pill-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <span class="mobile-search-pill-text"><?php printf(esc_html__('Search %s…', 'obenlo'), single_term_title('', false)); ?></span>
            </span>
            <span class="mobile-search-pill-filter" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            </span>
        </a>
        <div class="mobile-categories-scroll" role="list">
            <a href="<?php echo esc_url($stay_link); ?>" class="mobile-category-pill cat-stay <?php echo ($current_slug === 'stay') ? 'active' : ''; ?>" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Stays', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($exp_link); ?>" class="mobile-category-pill cat-experience <?php echo ($current_slug === 'experience') ? 'active' : ''; ?>" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Experiences', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($evt_link); ?>" class="mobile-category-pill cat-event <?php echo ($current_slug === 'event') ? 'active' : ''; ?>" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Events', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($srv_link); ?>" class="mobile-category-pill cat-service <?php echo ($current_slug === 'service') ? 'active' : ''; ?>" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Services', 'obenlo'); ?></a>
        </div>
    </div><!-- .mobile-search-wrapper -->

    <header class="archive-header" style="margin-bottom: 60px; padding-bottom: 30px; border-bottom: 1px solid #ebebeb;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <nav class="breadcrumb" style="font-size: 0.85rem; color: #717171; margin-bottom: 15px;">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color: inherit; text-decoration: none;">Home</a> 
                    <span style="margin: 0 8px;">/</span> 
                    <span style="color: #222; font-weight: 500;">Details</span>
                </nav>
                <h1 class="page-title" style="font-size: 2.2rem; font-weight: 800; color: #222; margin: 0;">
                    <?php single_term_title(); ?>
                </h1>
                <?php 
                $term = get_queried_object();
                if ( $term && !empty($term->description) ) : ?>
                    <div class="archive-description" style="color: #717171; margin-top: 15px; max-width: 600px; line-height: 1.6;">
                        <?php echo wp_kses_post( $term->description ); ?>
                    </div>
                <?php else : ?>
                    <p style="color: #717171; margin-top: 10px;">Unique curated selections for your next <?php echo strtolower(single_term_title('', false)); ?>.</p>
                <?php endif; ?>
            </div>
            
            <div class="archive-stats" style="text-align: right;">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #222;">
                    <?php 
                    global $wp_query;
                    echo $wp_query->found_posts; 
                    ?>
                </span>
                <span style="font-size: 0.9rem; color: #717171;">Listings available</span>
            </div>
        </div>
    </header>

    <div class="listing-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px 30px;">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'listing-card' ); ?>
            <?php endwhile; ?>
            
            <div class="pagination-wrapper" style="grid-column: 1 / -1; margin-top: 80px; text-align: center;">
                <?php 
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '<span style="font-size: 1.2rem;">←</span> Previous',
                    'next_text' => 'Next <span style="font-size: 1.2rem;">→</span>',
                    'class'     => 'premium-pagination'
                ) ); 
                ?>
            </div>

        <?php else : ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0; background: #f9f9f9; border-radius: 24px;">
                <div style="font-size: 3rem; margin-bottom: 20px;">🔍</div>
                <h2 style="font-size: 1.5rem; color: #222; margin-bottom: 10px;">No listings found</h2>
                <p style="color: #717171; margin-bottom: 30px;">We couldn't find any results in this category right now.</p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; padding: 12px 24px; background: #222; color: #fff; text-decoration: none; border-radius: 10px; font-weight: 600;">Explore all categories</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.premium-pagination .nav-links { display: flex; justify-content: center; align-items: center; gap: 15px; }
.premium-pagination .page-numbers { 
    display: inline-flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 50%; border: 1px solid #ddd;
    text-decoration: none; color: #222; font-weight: 500; transition: all 0.2s;
}
.premium-pagination .page-numbers:hover { border-color: #222; background: #f7f7f7; }
.premium-pagination .page-numbers.current { background: #222; color: #fff; border-color: #222; }
.premium-pagination .prev, .premium-pagination .next { width: auto; padding: 0 20px; border-radius: 22px; }

.listing-card:hover .listing-thumbnail-wrapper img { transform: scale(1.05); }
</style>

<?php get_footer(); ?>
