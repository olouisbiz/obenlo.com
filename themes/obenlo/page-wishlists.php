<?php
/**
 * Template Name: Wishlists
 */

if ( ! is_user_logged_in() ) {
    wp_redirect( home_url('/login') );
    exit;
}

get_header();

$wishlist_ids = array();
if (class_exists('Obenlo_Booking_Wishlist')) {
    $wishlist_ids = Obenlo_Booking_Wishlist::get_user_wishlist();
}
?>

<style>
    .front-page-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
</style>

<main id="primary" class="site-main">
    <div class="obenlo-container" style="padding-top: 60px; padding-bottom: 80px; max-width: 1400px; margin: 0 auto; padding-left: 40px; padding-right: 40px;">
        <header class="archive-header" style="margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 10px;">Wishlists</h1>
        </header>

        <?php if ( ! empty( $wishlist_ids ) ) : ?>
            <div class="front-page-grid">
                <?php 
                $args = array(
                    'post_type' => 'listing',
                    'post__in'  => $wishlist_ids,
                    'orderby'   => 'post__in',
                    'posts_per_page' => -1
                );
                $query = new WP_Query( $args );

                if ( $query->have_posts() ) :
                    while ( $query->have_posts() ) : $query->the_post();
                        get_template_part( 'template-parts/content', 'listing-card' );
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No saved listings found.</p>';
                endif;
                ?>
            </div>
        <?php else : ?>
            <div class="empty-wishlist" style="text-align: center; padding: 100px 20px; background: #f9f9f9; border-radius: 24px;">
                <h2 style="font-size: 1.8rem; margin-bottom: 20px;">Create your first wishlist</h2>
                <p style="color: #717171; font-size: 1.1rem; margin-bottom: 30px;">As you search, click the heart icon to save your favorite stays and experiences.</p>
                <a href="<?php echo home_url(); ?>" class="cta-button" style="background:#222; box-shadow:none;">Start exploring</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
