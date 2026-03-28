<?php
/**
 * The template for displaying listing archives.
 */

get_header(); ?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 40px 40px;">
    <header class="archive-header" style="margin-bottom: 60px; padding-bottom: 30px; border-bottom: 1px solid #ebebeb;">
        <h1 class="page-title" style="font-size: 2.2rem; font-weight: 800; color: #222; margin: 0;">
            <?php 
            if ( is_search() ) {
                printf( __('Results for: "%s"', 'obenlo'), get_search_query() );
            } else {
                echo __('All Listings', 'obenlo');
            }
            ?>
        </h1>
        <p style="color: #717171; margin-top: 10px;"><?php echo __('Discover stays, experiences, and services from our expert hosts.', 'obenlo'); ?></p>
    </header>

    <div class="listing-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px 30px;">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'listing-card' ); ?>
            <?php endwhile; ?>
            
            <div class="pagination-wrapper" style="grid-column: 1 / -1; margin-top: 80px; text-align: center;">
                <?php the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '← ' . __('Previous', 'obenlo'),
                    'next_text' => __('Next', 'obenlo') . ' →',
                ) ); ?>
            </div>

        <?php else : ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0;">
                <p style="font-size: 1.2rem; color: #717171;"><?php echo __('No listings found matching your criteria.', 'obenlo'); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; margin-top: 20px; color: #222; font-weight: 600; text-decoration: underline;"><?php echo __('Explore all categories', 'obenlo'); ?></a>
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
