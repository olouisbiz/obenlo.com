<?php
/**
 * The front page template file
 */

get_header(); ?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 20px 40px;">

    <!-- Supply-First Hero Section -->
    <section class="marketplace-hero" style="text-align: center; padding: 80px 20px; background: linear-gradient(135deg, #fff1f3 0%, #eff6ff 100%); border-radius: 32px; margin-bottom: 60px;">
        <h1 style="font-size: 3rem; font-weight: 800; color: #111; margin-bottom: 20px; line-height: 1.2;"><?php esc_html_e( 'Get booked and paid by local customers — instantly.', 'obenlo' ); ?></h1>
        <p style="font-size: 1.2rem; color: #444; max-width: 600px; margin: 0 auto 40px auto; line-height: 1.5;"><?php esc_html_e( 'List your services, accept bookings, and get paid securely without the back-and-forth.', 'obenlo' ); ?></p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url( home_url('/become-a-host') ); ?>" style="padding: 16px 32px; background: #e61e4d; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 1.1rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(230,30,77,0.3)';" onmouseout="this.style.transform='';this.style.boxShadow='';"><?php esc_html_e( 'Start offering services', 'obenlo' ); ?></a>
            <a href="#services-explore" style="padding: 16px 32px; background: #fff; color: #222; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 1.1rem; border: 2px solid #222; transition: all 0.2s;" onmouseover="this.style.background='#222';this.style.color='#fff';" onmouseout="this.style.background='#fff';this.style.color='#222';"><?php esc_html_e( 'Book a service', 'obenlo' ); ?></a>
        </div>
    </section>

    <!-- Category Filter Bar -->
    <div class="category-filters" style="display: flex; gap: 40px; justify-content: center; margin-bottom: 40px; overflow-x: auto; padding: 20px 0; border-bottom: 1px solid #ebebeb; -webkit-overflow-scrolling: touch; white-space: nowrap;">
        
        <?php
        $current_cat = '';
        if ( is_tax('listing_type') ) {
            $current_cat = get_queried_object()->slug;
        } elseif ( isset($_GET['listing_type']) ) {
            $current_cat = $_GET['listing_type'];
        }
        
        $taxonomies = array();
        if ( taxonomy_exists( 'listing_type' ) ) {
            $terms = get_terms( array(
                'taxonomy'   => 'listing_type',
                'hide_empty' => false,
                'parent'     => 0,
            ) );
            if ( ! is_wp_error( $terms ) ) {
                $taxonomies = $terms;
            }
        }
        
        // Output an "All" button
        $all_active = empty($current_cat) ? 'active' : '';
        $all_style = empty($current_cat) ? 'border-bottom: 2px solid #222; color: #222; font-weight: 600;' : 'border-bottom: 2px solid transparent; color: #717171;';
        
        echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="cat-filter ' . $all_active . '" style="display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none; min-width: 60px; padding-bottom: 5px; ' . $all_style . '">';
        echo '<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;height:24px;width:24px;fill:currentColor"><path d="M16 2a14 14 0 1 0 14 14A14.016 14.016 0 0 0 16 2Zm0 26a12 12 0 1 1 12-12 12.014 12.014 0 0 1-12 12Z"/><path d="M16 8a8 8 0 1 0 8 8 8.009 8.009 0 0 0-8-8Zm0 14a6 6 0 1 1 6-6 6.007 6.007 0 0 1-6 6Z"/></svg>';
        echo '<span style="font-size: 0.8em;">' . esc_html__( 'All', 'obenlo' ) . '</span>';
        echo '</a>';

        foreach ( $taxonomies as $tax ) {
            $is_active = ($current_cat == $tax->slug);
            $active_class = $is_active ? 'active' : '';
            $cat_style = $is_active ? 'border-bottom: 2px solid #222; color: #222; font-weight: 600;' : 'border-bottom: 2px solid transparent; color: #717171;';
            
            $icon = '<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;height:24px;width:24px;fill:currentColor"><path d="M26 4H6a2.006 2.006 0 0 0-2 2v20a2.006 2.006 0 0 0 2 2h20a2.006 2.006 0 0 0 2-2V6a2.006 2.006 0 0 0-2-2Zm0 22H6V6h20Z"/><path d="M16 14a4 4 0 1 0 4 4 4.005 4.005 0 0 0-4-4Zm0 6a2 2 0 1 1 2-2 2.002 2.002 0 0 1-2 2Z"/></svg>';
            
            // USE CLEAN URL
            $cat_url = get_term_link($tax);
            if ( is_wp_error( $cat_url ) ) {
                $cat_url = home_url( '/' );
            }
            
            echo '<a href="' . esc_url( $cat_url ) . '" class="cat-filter ' . $active_class . '" style="display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none; min-width: 60px; transition: color 0.2s; padding-bottom: 5px; ' . $cat_style . '" onmouseover="if(!this.classList.contains(\'active\')) this.style.color=\'#222\';" onmouseout="if(!this.classList.contains(\'active\')) this.style.color=\'#717171\';">';
            echo $icon;
            echo '<span style="font-size: 0.8em;">' . esc_html( __( $tax->name, 'obenlo' ) ) . '</span>';
            echo '</a>';
        }
        ?>
    </div>

    <div id="services-explore" style="padding-top: 20px;"></div>

    <!-- Categories Explore Section -->

    <!-- Explore Categories -->
    <section style="margin-bottom: 60px; margin-top: 10px; text-align: center;">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: #222; margin-bottom: 24px; text-align: center;"><?php esc_html_e( 'Explore Categories', 'obenlo' ); ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

            <?php
            $categories = array(
                'stay' => array(
                    'label' => __( 'Stays', 'obenlo' ),
                    'desc'  => __( 'Hotels, guest houses & unique rooms', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M28 12H22V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v24h2V18h16v10h2V14h6v14h2V14a2 2 0 0 0-2-2ZM12 14H4v-4h8Zm0-6H4V4h8Zm8 6h-6v-4h6Zm0-6h-6V4h6Z"/></svg>',
                    'color' => '#3b82f6',
                    'bg'    => '#eff6ff',
                ),
                'experience' => array(
                    'label' => __( 'Experiences', 'obenlo' ),
                    'desc'  => __( 'Tours, adventures & local activities', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:32px;height:32px;"><path d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm0 18H4V8h24ZM8 16a4 4 0 1 0 4-4 4.005 4.005 0 0 0-4 4Zm4-2a2 2 0 1 1-2 2 2.002 2.002 0 0 1 2-2Zm4 6v-1a4.005 4.005 0 0 0-4-4 4.005 4.005 0 0 0-4 4v1H6v-1a6.007 6.007 0 0 1 6-6 6.007 6.007 0 0 1 6 6v1Zm2-10h8v2h-8Zm0 4h8v2h-8Zm0 4h4v2h-4Z"/></svg>',
                    'color' => '#10b981',
                    'bg'    => '#ecfdf5',
                ),
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
            );
            foreach ( $categories as $slug => $cat ) :
                $link = get_term_link( $slug, 'listing_type' );
                $link = is_wp_error($link) ? home_url('/') : $link;
            ?>
                <a href="<?php echo esc_url($link); ?>" style="display:flex; flex-direction:column; gap:16px; padding:24px; background:<?php echo $cat['bg']; ?>; border-radius:20px; text-decoration:none; color:inherit; text-align:left; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='';this.style.boxShadow='';">
                    <div style="color:<?php echo $cat['color']; ?>; width:52px;height:52px;background:#fff;border-radius:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 10px rgba(0,0,0,0.06);"><?php echo $cat['icon']; ?></div>
                    <div>
                        <div style="font-size:1.1rem;font-weight:800;color:#222;margin-bottom:4px;"><?php echo esc_html($cat['label']); ?></div>
                        <div style="font-size:0.82rem;color:#888;line-height:1.4;"><?php echo esc_html($cat['desc']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
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

    <!-- Top Reviews Section -->
    <section class="top-reviews" style="margin-bottom: 80px; padding: 60px 0; background: #f9f9f9; border-radius: 32px; margin-left: -40px; margin-right: -40px; padding-left: 40px; padding-right: 40px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;"><?php esc_html_e( 'What customers are saying', 'obenlo' ); ?></h2>
        <p style="color: #666; margin-bottom: 40px;"><?php esc_html_e( 'Obenlo experiences through the eyes of our community.', 'obenlo' ); ?></p>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
            <?php
            // Mock reviews for now - later can pull from actual comments/meta
            $mock_reviews = array(
                array('author' => 'Sarah', 'comment' => 'The mountain experience was breathtaking. The host was so professional!', 'rating' => 5, 'date' => 'March 2026'),
                array('author' => 'Michael', 'comment' => 'Obenlo made booking our summer stay so simple. Highly recommend this platform.', 'rating' => 5, 'date' => 'February 2026'),
                array('author' => 'Elena', 'comment' => 'Unique services that I couldnt find anywhere else. A truly casual and friendly vibe.', 'rating' => 4, 'date' => 'January 2026'),
            );
            foreach ( $mock_reviews as $review ) {
                include locate_template('template-parts/content-review-card.php');
            }
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
            $hosts = get_users( array( 'role' => 'host', 'number' => 10, 'orderby' => 'post_count', 'order' => 'DESC' ) );
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
