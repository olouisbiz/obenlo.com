<?php
/**
 * The front page template file
 */

get_header(); 

$hide_demo_content = get_option('obenlo_hide_demo_frontpage', 'no') === 'yes';
$user_can_see_demo = false;
if ( is_user_logged_in() ) {
    $user = wp_get_current_user();
    if ( in_array('administrator', (array) $user->roles) || in_array('host', (array) $user->roles) || $user->user_login === 'demo' ) {
        $user_can_see_demo = true;
    }
}

$demo_meta_query = array('relation' => 'AND');

if ( ! $user_can_see_demo ) {
    // 1. Always hide demos marked as HIDDEN in Demo Manager
    $demo_meta_query[] = array(
        'relation' => 'OR',
        array(
            'key' => '_obenlo_demo_hidden',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key' => '_obenlo_demo_hidden',
            'value' => 'yes',
            'compare' => '!='
        )
    );

    // 2. Hide ALL demos if global setting is ON
    if ( $hide_demo_content ) {
        $demo_meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_obenlo_is_demo',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_obenlo_is_demo',
                'value' => 'yes',
                'compare' => '!='
            )
        );
    }
}

// Cleanup if no filters applied
if ( count($demo_meta_query) <= 1 ) {
    $demo_meta_query = array();
}
?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 20px 40px;">




    <div id="services-explore" style="padding-top: 20px;"></div>

    <!-- Categories Explore Section -->

    <!-- Market Hero: Explore Categories -->
    <section class="marketplace-hero" style="text-align: center; padding: 100px 20px 80px 20px; background: linear-gradient(135deg, #fff7f8 0%, #f4f9ff 100%); border-radius: 40px; margin-bottom: 60px; position: relative; overflow: hidden;">
        
        <!-- Decorative elements -->
        <div style="position: absolute; top: -10%; right: -5%; width: 300px; height: 300px; background: rgba(230,30,77,0.03); filter: blur(80px); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -10%; left: -5%; width: 300px; height: 300px; background: rgba(59,130,246,0.03); filter: blur(80px); border-radius: 50%;"></div>

        <div style="position: relative; z-index: 2;">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; max-width: 1200px; margin: 0 auto; padding: 0 10px;">

                <?php
                $categories = array(
                    'service' => array(
                        'label' => __( 'Services', 'obenlo' ),
                        'desc'  => __( 'Cleaning, Handyman, Barber & Freelance', 'obenlo' ),
                        'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M18.5 15h-6a4.5 4.5 0 0 0-4.5 4.5V28h2v-8.5A2.503 2.503 0 0 1 12.5 17h6a2.503 2.503 0 0 1 2.5 2.5V28h2v-8.5A4.5 4.5 0 0 0 18.5 15ZM15.5 14A5 5 0 1 0 10.5 9a5.006 5.006 0 0 0 5 5Zm0-8A3 3 0 1 1 12.5 9a3.003 3.003 0 0 1 3-3Z"/></svg>',
                        'color' => '#f97316',
                        'bg'    => '#fff7ed',
                    ),
                    'event' => array(
                        'label' => __( 'Events', 'obenlo' ),
                        'desc'  => __( 'Shows, live nights & performances', 'obenlo' ),
                        'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M26 4H6a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h20a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 22H6V12h20Zm0-16H6V6h20ZM13 25a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Zm8 0a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Zm-4 0a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Z"/></svg>',
                        'color' => '#e61e4d',
                        'bg'    => '#fff1f3',
                    ),
                    'experience' => array(
                        'label' => __( 'Experiences', 'obenlo' ),
                        'desc'  => __( 'Tours, adventures & local activities', 'obenlo' ),
                        'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm0 18H4V8h24ZM8 16a4 4 0 1 0 4-4 4.005 4.005 0 0 0-4 4Zm4-2a2 2 0 1 1-2 2 2.002 2.002 0 0 1 2-2Zm4 6v-1a4.005 4.005 0 0 0-4-4 4.005 4.005 0 0 0-4 4v1H6v-1a6.007 6.007 0 0 1 6-6 6.007 6.007 0 0 1 6 6v1Zm2-10h8v2h-8Zm0 4h8v2h-8Zm0 4h4v2h-4Z"/></svg>',
                        'color' => '#10b981',
                        'bg'    => '#ecfdf5',
                    ),
                    'stay' => array(
                        'label' => __( 'Stays', 'obenlo' ),
                        'desc'  => __( 'Hotels, guest houses & unique rooms', 'obenlo' ),
                        'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M28 12H22V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v24h2V18h16v10h2V14h6v14h2V14a2 2 0 0 0-2-2ZM12 14H4v-4h8Zm0-6H4V4h8Zm8 6h-6v-4h6Zm0-6h-6V4h6Z"/></svg>',
                        'color' => '#3b82f6',
                        'bg'    => '#eff6ff',
                    ),
                );
                foreach ( $categories as $slug => $cat ) :
                    $link = get_term_link( $slug, 'listing_type' );
                    $link = is_wp_error($link) ? home_url('/') : $link;
                ?>
                    <a href="<?php echo esc_url($link); ?>" style="display:flex; flex-direction:column; gap:20px; padding:32px; background:#fff; border:1px solid #eee; border-radius:32px; text-decoration:none; color:inherit; text-align:left; transition:all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 4px 20px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-8px)';this.style.boxShadow='0 20px 40px rgba(0,0,0,0.06)';this.style.borderColor='#ddd';" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(0,0,0,0.02)';this.style.borderColor='#eee';">
                        <div style="color:<?php echo $cat['color']; ?>; width:60px; height:60px; background:<?php echo $cat['bg']; ?>; border-radius:18px; display:flex; align-items:center; justify-content:center;"><?php echo $cat['icon']; ?></div>
                        <div>
                            <div style="font-size:1.3rem; font-weight:800; color:#111; margin-bottom:8px;"><?php echo esc_html($cat['label']); ?></div>
                            <div style="font-size:0.9rem; color:#666; line-height:1.5; font-weight:500;"><?php echo esc_html($cat['desc']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Services (Top 10) -->
    <section class="featured-services" style="margin-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;"><?php esc_html_e( 'Featured Services', 'obenlo' ); ?></h2>
            <?php 
            $srv_link = get_term_link('service', 'listing_type');
            $srv_link = is_wp_error( $srv_link ) ? home_url( '/' ) : $srv_link;
            ?>
            <a href="<?php echo esc_url( $srv_link ); ?>" style="color: #222; font-weight: 600; text-decoration: underline; font-size: 0.9rem;"><?php esc_html_e( 'Show all', 'obenlo' ); ?></a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            $srv_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'service',
                    ),
                ),
            );
            if ( ! empty( $demo_meta_query ) ) {
                $srv_args['meta_query'] = array( $demo_meta_query );
            }
            $srv_query = new WP_Query( $srv_args );
            if ( $srv_query->have_posts() ) :
                while ( $srv_query->have_posts() ) : $srv_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #717171;">' . esc_html__( 'Professional services coming soon!', 'obenlo' ) . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- Featured Events (Top 10) -->
    <section class="featured-events" style="margin-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;"><?php esc_html_e( 'Featured Events', 'obenlo' ); ?></h2>
            <?php 
            $evt_link = get_term_link('event', 'listing_type');
            $evt_link = is_wp_error( $evt_link ) ? home_url( '/' ) : $evt_link;
            ?>
            <a href="<?php echo esc_url( $evt_link ); ?>" style="color: #222; font-weight: 600; text-decoration: underline; font-size: 0.9rem;"><?php esc_html_e( 'Show all', 'obenlo' ); ?></a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            $evt_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => array('event', 'show'),
                        'operator' => 'IN',
                    ),
                ),
                'orderby' => 'date',
                'order'   => 'DESC',
            );
            if ( ! empty( $demo_meta_query ) ) {
                $evt_args['meta_query'] = array( $demo_meta_query );
            }
            $evt_query = new WP_Query( $evt_args );
            if ( $evt_query->have_posts() ) :
                while ( $evt_query->have_posts() ) : $evt_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #717171;">' . esc_html__( 'No events yet — check back soon! 🎉', 'obenlo' ) . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- Featured Experiences (Top 10) -->
    <section class="featured-experiences" style="margin-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;"><?php esc_html_e( 'Featured Experiences', 'obenlo' ); ?></h2>
            <?php 
            $exp_link = get_term_link('experience', 'listing_type');
            $exp_link = is_wp_error( $exp_link ) ? home_url( '/' ) : $exp_link;
            ?>
            <a href="<?php echo esc_url( $exp_link ); ?>" style="color: #222; font-weight: 600; text-decoration: underline; font-size: 0.9rem;"><?php esc_html_e( 'Show all', 'obenlo' ); ?></a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            $exp_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'experience',
                    ),
                ),
            );
            if ( ! empty( $demo_meta_query ) ) {
                $exp_args['meta_query'] = array( $demo_meta_query );
            }
            $exp_query = new WP_Query( $exp_args );
            if ( $exp_query->have_posts() ) :
                while ( $exp_query->have_posts() ) : $exp_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #717171;">' . esc_html__( 'Explore local experiences soon!', 'obenlo' ) . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- Featured Stays (Top 10) -->
    <section class="featured-stays" style="margin-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 25px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;"><?php esc_html_e( 'Featured Stays', 'obenlo' ); ?></h2>
            <?php 
            $stay_link = get_term_link('stay', 'listing_type');
            $stay_link = is_wp_error( $stay_link ) ? home_url( '/' ) : $stay_link;
            ?>
            <a href="<?php echo esc_url( $stay_link ); ?>" style="color: #222; font-weight: 600; text-decoration: underline; font-size: 0.9rem;"><?php esc_html_e( 'Show all', 'obenlo' ); ?></a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            $stay_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'stay',
                    ),
                ),
                'meta_key'       => '_obenlo_price',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC'
            );
            if ( ! empty( $demo_meta_query ) ) {
                $stay_args['meta_query'] = array( $demo_meta_query );
            }
            $stay_query = new WP_Query( $stay_args );
            if ( $stay_query->have_posts() ) :
                while ( $stay_query->have_posts() ) : $stay_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #717171;">' . esc_html__( 'Check back soon for featured stays!', 'obenlo' ) . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- Top Reviews Section -->
    <section class="top-reviews" style="margin-bottom: 80px; padding: 60px 0; background: #f9f9f9; border-radius: 32px; margin-left: -40px; margin-right: -40px; padding-left: 40px; padding-right: 40px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;"><?php esc_html_e( 'What customers are saying', 'obenlo' ); ?></h2>
        <p style="color: #666; margin-bottom: 40px;"><?php esc_html_e( 'Obenlo experiences through the eyes of our community.', 'obenlo' ); ?></p>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
            <?php
            $testimony_args = array(
                'post_type'      => 'testimony',
                'posts_per_page' => 5,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC'
            );
            $testimony_query = new WP_Query( $testimony_args );

            if ( $testimony_query->have_posts() ) :
                while ( $testimony_query->have_posts() ) : $testimony_query->the_post();
                    $review = array(
                        'author'  => get_the_author(),
                        'comment' => get_the_content(),
                        'rating'  => get_post_meta( get_the_ID(), '_obenlo_testimony_rating', true ),
                        'date'    => get_the_date('F Y'),
                        'is_real' => true
                    );
                    include locate_template('template-parts/content-review-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                // Mock fallback
                $mock_reviews = array(
                    array('author' => 'Sarah', 'comment' => 'The mountain experience was breathtaking. The host was so professional!', 'rating' => 5, 'date' => 'March 2026'),
                    array('author' => 'Michael', 'comment' => 'Obenlo made booking our summer stay so simple. Highly recommend this platform.', 'rating' => 5, 'date' => 'February 2026'),
                    array('author' => 'Elena', 'comment' => 'Unique services that I couldnt find anywhere else. A truly casual and friendly vibe.', 'rating' => 4, 'date' => 'January 2026'),
                );
                foreach ( $mock_reviews as $review ) {
                    include locate_template('template-parts/content-review-card.php');
                }
            endif;
            ?>
        </div>
    </section>

    <!-- Featured Hosts Section (Top 10) -->
    <section class="featured-hosts" style="margin-top: 40px; padding: 40px 0;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;"><?php esc_html_e( 'Meet Our Top Providers', 'obenlo' ); ?></h2>
            <a href="<?php echo esc_url( home_url('/hosts') ); ?>" style="color: #222; font-weight: 600; text-decoration: underline; font-size: 0.9rem;"><?php esc_html_e( 'Meet them all', 'obenlo' ); ?></a>
        </div>
        <p style="color: #666; margin-bottom: 40px;"><?php esc_html_e( 'Top-rated professionals and property hosts ready to serve you.', 'obenlo' ); ?></p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px;">
            <?php
            $host_args = array( 'role' => 'host', 'number' => 10, 'orderby' => 'post_count', 'order' => 'DESC' );
            if ( $hide_demo_content && ! $user_can_see_demo ) {
                $demo_user = get_user_by('login', 'demo');
                if ( $demo_user ) {
                    $host_args['exclude'] = array( $demo_user->ID );
                }
            }
            $hosts = get_users( $host_args );
            if ( empty($hosts) ) {
                $hosts = get_users( array( 'role' => 'administrator', 'number' => 10 ) ); // Fallback
            }
            
            foreach ( $hosts as $host ) :
                include locate_template('template-parts/content-host-card.php');
            endforeach; ?>
        </div>
    </section>
        
    </div>
</main>

<style>
/* Add a hover effect to listing images */
.listing-card:hover .listing-thumbnail-wrapper img {
    transform: scale(1.05);
}
</style>

<?php get_footer(); ?>
