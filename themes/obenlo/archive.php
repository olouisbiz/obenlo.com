<?php
/**
 * The template for displaying archive pages
 */

get_header(); ?>

<style>
    /* Consistency with Front Page */
    .front-page-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    .listing-card:hover .listing-thumbnail-wrapper img {
        transform: scale(1.05);
    }
</style>

<div class="archive-header" style="background: #f7f7f7; padding: 60px 20px; text-align: center; border-bottom: 1px solid #eee;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-weight: 800;"><?php the_archive_title(); ?></h1>
        <p style="color: #666; font-size: 1rem;"><?php the_archive_description(); ?></p>
    </div>
</div>

<div class="archive-content" style="max-width: 1400px; margin: 0 auto; padding: 40px 20px;">
    
    <div class="front-page-grid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="listing-card" style="display: flex; flex-direction: column; gap: 10px; cursor: pointer;" onclick="window.location='<?php echo esc_url( get_permalink() ); ?>';">
                    <!-- Image Wrapper -->
                    <div class="listing-thumbnail-wrapper" style="width: 100%; aspect-ratio: 16/15; border-radius: 12px; overflow: hidden; position: relative; background: #f0f0f0;">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'large', array( 'style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;' ) ); ?>
                        <?php else : ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); font-size: 2rem;">
                                ✍️
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info Area -->
                    <div class="listing-info" style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline;">
                            <h2 style="margin: 0; font-size: 1rem; font-weight: 600; color: #222;"><?php echo get_the_date(); ?></h2>
                            <div style="font-size: 0.85em; display: flex; align-items: center; gap: 4px; color: var(--obenlo-primary); font-weight: 700;">
                                <?php 
                                $cats = get_the_category();
                                if(!empty($cats)) echo esc_html($cats[0]->name);
                                ?>
                            </div>
                        </div>
                        <div style="color: #222; font-weight: 600; font-size: 0.95rem; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                            <?php the_title(); ?>
                        </div>
                        <div style="color: #717171; font-size: 0.9rem; line-height: 1.5; margin-top: 2px;">
                            <?php echo wp_trim_words(get_the_excerpt(), 18); ?>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
            
            <!-- Pagination -->
            <div style="grid-column: 1/-1; display: flex; justify-content: center; margin-top: 60px;">
                <?php
                the_posts_pagination( array(
                    'prev_text'          => '&larr; Previous',
                    'next_text'          => 'Next &rarr;',
                    'type'               => 'plain',
                ) );
                ?>
            </div>

        <?php else : ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 20px;">
                <h2 style="font-size: 1.8rem; color: #222;"><?php esc_html_e( 'No stories found.', 'obenlo' ); ?></h2>
                <a href="<?php echo home_url('/blog'); ?>" style="color: var(--obenlo-primary); font-weight: 700;"><?php esc_html_e( 'Back to all posts', 'obenlo' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Custom Styles for Archive Pagination */
.pagination .nav-links {
    display: flex;
    justify-content: center;
    gap: 10px;
}
.page-numbers {
    display: inline-block;
    padding: 12px 20px;
    margin: 0 5px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    text-decoration: none;
    color: #222;
    font-weight: 600;
    transition: all 0.2s;
}
.page-numbers:hover {
    border-color: #222;
    background: #f7f7f7;
}
.page-numbers.current {
    background: #222;
    color: #fff;
    border-color: #222;
}
</style>

<?php get_footer(); ?>
