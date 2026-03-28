<?php
/**
 * Template Name: Blog
 */

get_header(); ?>

<style>
    .blog-filters::-webkit-scrollbar { display: none; }
    .blog-filters { -ms-overflow-style: none; scrollbar-width: none; }
    
    .blog-cat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        min-width: 80px;
        color: #717171;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        padding-bottom: 12px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
    }
    .blog-cat-item:hover { color: #222; }
    .blog-cat-item.active { color: #222; border-bottom: 2px solid #222; font-weight: 700; }
    
    /* Consistency with Front Page */
    .front-page-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
</style>

<?php
$current_category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch Categories for Filter Bar
$categories = get_terms(array(
    'taxonomy' => 'category',
    'hide_empty' => true,
));

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$grid_args = array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'post_status'    => 'publish',
    'paged'          => $paged
);

if (!empty($current_category)) {
    $grid_args['category_name'] = $current_category;
}

$grid_query = new WP_Query($grid_args);
?>

<main class="blog-main" style="max-width: 1400px; margin: 0 auto; padding: 20px 40px;">
    
    <!-- Category Toolbar (Same design as front page taxonomies) -->
    <div class="blog-filters" style="display: flex; gap: 32px; justify-content: center; margin-bottom: 40px; overflow-x: auto; border-bottom: 1px solid #eee; padding-top: 10px;">
        <a href="<?php echo home_url('/blog'); ?>" class="blog-cat-item <?php echo empty($current_category) ? 'active' : ''; ?>">
            <span style="font-size:1.5rem;">🌎</span>
            <span>All Posts</span>
        </a>
        <?php 
        $emoji_map = ['news' => '📰', 'hosting' => '🏠', 'local' => '📍', 'updates' => '✨', 'tips' => '💡'];
        foreach($categories as $cat) : 
            $cat_active = ($current_category == $cat->slug) ? 'active' : '';
            $emoji = isset($emoji_map[$cat->slug]) ? $emoji_map[$cat->slug] : '📝';
        ?>
            <a href="?category=<?php echo $cat->slug; ?>" class="blog-cat-item <?php echo $cat_active; ?>">
                <span style="font-size:1.5rem;"><?php echo $emoji; ?></span>
                <span><?php echo esc_html($cat->name); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Blog Grid - Mirroring Front Page -->
    <div class="front-page-grid">
        <?php if ( $grid_query->have_posts() ) : ?>
            <?php while ( $grid_query->have_posts() ) : $grid_query->the_post(); ?>
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

                    <!-- Info Area (Typography from content-listing-card.php) -->
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
                echo paginate_links( array(
                    'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                    'format'       => '?paged=%#%',
                    'current'      => max( 1, get_query_var( 'paged' ) ),
                    'total'        => $grid_query->max_num_pages,
                    'prev_text'    => '&larr; Previous',
                    'next_text'    => 'Next &rarr;',
                    'type'         => 'plain',
                ) );
                ?>
            </div>

        <?php wp_reset_postdata(); else : ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 20px;">
                <h2 style="font-size: 1.8rem; color: #222;"><?php esc_html_e( 'No stories found.', 'obenlo' ); ?></h2>
                <a href="<?php echo home_url('/blog'); ?>" style="color: var(--obenlo-primary); font-weight: 700;"><?php esc_html_e( 'Back to all posts', 'obenlo' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Custom Styles for Blog Pagination */
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

/* Hover effects matching front page */
.listing-card:hover .listing-thumbnail-wrapper img {
    transform: scale(1.05);
}
</style>

<?php get_footer(); ?>
