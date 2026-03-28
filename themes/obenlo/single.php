<?php
/**
 * The template for displaying all single posts
 */

get_header(); ?>

<div class="single-post-wrapper" style="max-width: 800px; margin: 0 auto; padding: 80px 20px;">
    
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <header class="entry-header" style="margin-bottom: 40px; text-align: center;">
                <div style="font-size: 0.9rem; color: var(--obenlo-primary); font-weight: bold; margin-bottom: 15px; text-transform: uppercase;">
                    <?php the_category(', '); ?>
                </div>
                <?php the_title( '<h1 class="entry-title" style="font-size: 3rem; line-height: 1.2; margin-bottom: 20px; color: #222;">', '</h1>' ); ?>
                
                <div class="entry-meta" style="display: flex; align-items: center; justify-content: center; gap: 15px; color: #717171; font-size: 0.95rem;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <?php echo get_avatar( get_the_author_meta( 'ID' ), 32, '', '', array('style' => 'border-radius: 50%;') ); ?>
                        <?php the_author(); ?>
                    </span>
                    <span>&bull;</span>
                    <span><?php echo get_the_date(); ?></span>
                </div>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail" style="margin-bottom: 50px; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <?php the_post_thumbnail( 'full', array( 'style' => 'width: 100%; height: auto; display: block;' ) ); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content" style="line-height: 1.8; color: #333; font-size: 1.15rem;">
                <?php
                the_content();

                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'obenlo' ),
                    'after'  => '</div>',
                ) );
                ?>
            </div>

            <footer class="entry-footer" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid #eee;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="post-tags" style="font-size: 0.9rem;">
                        <?php the_tags( '<span style="color: #666; margin-right: 10px;">' . esc_html__( 'Tags:', 'obenlo' ) . '</span>', ' ', '' ); ?>
                    </div>
                    <div class="share-post">
                        <!-- Social share links could go here -->
                    </div>
                </div>

                <div class="author-bio" style="margin-top: 50px; padding: 40px; background: #f9f9f9; border-radius: 15px; display: flex; gap: 20px; align-items: flex-start;">
                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 80, '', '', array('style' => 'border-radius: 50%;') ); ?>
                    <div>
                        <h4 style="margin-bottom: 10px; font-size: 1.2rem;"><?php printf(__('About %s', 'obenlo'), get_the_author()); ?></h4>
                        <p style="font-size: 0.95rem; color: #666; margin: 0;"><?php the_author_meta( 'description' ); ?></p>
                    </div>
                </div>
            </footer>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        </article>
    <?php endwhile; ?>

</div>

<?php get_footer(); ?>
