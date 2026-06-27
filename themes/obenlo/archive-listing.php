<?php
/**
 * The template for displaying listing archives.
 */

get_header(); ?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 40px 40px;">

    <?php /* ── MOBILE: Search pill + category row for archive pages ── */ ?>
    <div class="mobile-search-wrapper">
        <?php
        $search_url = home_url('/?s=');
        $srv_link  = get_term_link('service',    'listing_type'); $srv_link  = is_wp_error($srv_link)  ? home_url('/') : $srv_link;
        $evt_link  = get_term_link('event',      'listing_type'); $evt_link  = is_wp_error($evt_link)  ? home_url('/') : $evt_link;
        $exp_link  = get_term_link('experience', 'listing_type'); $exp_link  = is_wp_error($exp_link)  ? home_url('/') : $exp_link;
        $stay_link = get_term_link('stay',       'listing_type'); $stay_link = is_wp_error($stay_link) ? home_url('/') : $stay_link;
        ?>
        <a href="<?php echo esc_url($search_url); ?>" class="mobile-search-pill" id="mobile-search-pill-archive" aria-label="<?php esc_attr_e('Search listings', 'obenlo'); ?>">
            <span class="mobile-search-pill-content">
                <span class="mobile-search-pill-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <span class="mobile-search-pill-text"><?php esc_html_e('Search…', 'obenlo'); ?></span>
            </span>
            <span class="mobile-search-pill-filter" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            </span>
        </a>
        <div class="mobile-categories-scroll" role="list">
            <a href="<?php echo esc_url($stay_link); ?>" class="mobile-category-pill cat-stay" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Stays', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($exp_link); ?>" class="mobile-category-pill cat-experience" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Experiences', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($evt_link); ?>" class="mobile-category-pill cat-event" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Events', 'obenlo'); ?></a>
            <a href="<?php echo esc_url($srv_link); ?>" class="mobile-category-pill cat-service" role="listitem"><span class="cat-dot"></span><?php esc_html_e('Services', 'obenlo'); ?></a>
        </div>
    </div><!-- .mobile-search-wrapper -->

    <a href="<?php echo esc_url(home_url('/')); ?>" class="archive-back-link" style="display:inline-block; margin-bottom:20px; color:#222; text-decoration:none; font-weight: 600; font-size: 0.95rem; transition: transform 0.2s;">
        &larr; Back to Explore
    </a>
    <header class="archive-header" style="margin-bottom: 60px; padding-bottom: 30px; border-bottom: 1px solid #ebebeb;">
        <h1 class="page-title" style="font-size: 2.2rem; font-weight: 800; color: #222; margin: 0;">
            <?php 
            if ( is_search() ) {
                echo 'Results for: "' . get_search_query() . '"';
            } else {
                echo 'All Listings';
            }
            ?>
        </h1>
        <p style="color: #717171; margin-top: 10px;">Discover stays, experiences, and services from our expert hosts.</p>
    </header>

    <div class="listing-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px 30px;">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'listing-card' ); ?>
            <?php endwhile; ?>
            
            <div class="pagination-wrapper" style="grid-column: 1 / -1; margin-top: 80px; text-align: center;">
                <?php the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '← Previous',
                    'next_text' => 'Next →',
                ) ); ?>
            </div>

        <?php else : ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0;">
                <p style="font-size: 1.2rem; color: #717171;">No listings found matching your criteria.</p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; margin-top: 20px; color: #222; font-weight: 600; text-decoration: underline;">Explore all categories</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.pagination .nav-links { display: flex; justify-content: center; gap: 10px; }
.pagination .page-numbers { padding: 8px 15px; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #222; }
.pagination .page-numbers.current { background: #222; color: #fff; border-color: #222; }
.listing-card:hover .listing-thumbnail-wrapper img { transform: scale(1.05); }
</style>

<?php get_footer(); ?>
