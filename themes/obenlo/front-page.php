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
    $demo_meta_query[] = array(
        'relation' => 'OR',
        array(
            'key'     => '_obenlo_demo_hidden',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key'     => '_obenlo_demo_hidden',
            'value'   => 'yes',
            'compare' => '!='
        )
    );
    if ( $hide_demo_content ) {
        $demo_meta_query[] = array(
            'relation' => 'OR',
            array(
                'key'     => '_obenlo_is_demo',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key'     => '_obenlo_is_demo',
                'value'   => 'yes',
                'compare' => '!='
            )
        );
    }
}

if ( count($demo_meta_query) <= 1 ) {
    $demo_meta_query = array();
}
?>

<main id="primary" class="site-main site-content" style="max-width: 1400px; margin: 0 auto; padding: 20px 40px;">



    <div id="services-explore" style="padding-top: 20px;"></div>

    <!-- ── HERO SECTION ── -->
    <section class="ob-hero">
        <div class="ob-hero-inner">
            <div class="ob-hero-badge">
                <span class="ob-hero-badge-dot"></span>
                <?php esc_html_e('Your Local Marketplace', 'obenlo'); ?>
            </div>

            <h1>
                <?php esc_html_e('Book Services,', 'obenlo'); ?><br>
                <em><?php esc_html_e('Experiences & Stays', 'obenlo'); ?></em><br>
                <?php esc_html_e('Near You', 'obenlo'); ?>
            </h1>

            <p class="ob-hero-sub">
                <?php esc_html_e('Obenlo connects you with top-rated professionals, unique experiences, and handpicked stays in your city — all in one place.', 'obenlo'); ?>
            </p>

            <a href="#explore-categories" class="ob-hero-cta">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <?php esc_html_e('Start Exploring', 'obenlo'); ?>
            </a>


            <div class="ob-hero-stats">
                <div class="ob-hero-stat">
                    <span class="ob-hero-stat-value">500+</span>
                    <span class="ob-hero-stat-label"><?php esc_html_e('Active Listings', 'obenlo'); ?></span>
                </div>
                <div class="ob-hero-stat">
                    <span class="ob-hero-stat-value">4.9★</span>
                    <span class="ob-hero-stat-label"><?php esc_html_e('Avg. Rating', 'obenlo'); ?></span>
                </div>
                <div class="ob-hero-stat">
                    <span class="ob-hero-stat-value">2K+</span>
                    <span class="ob-hero-stat-label"><?php esc_html_e('Happy Customers', 'obenlo'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── EXPLORE CATEGORIES ── -->
    <section id="explore-categories" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Explore Categories', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Find exactly what you\'re looking for', 'obenlo'); ?></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <?php
            $categories = array(
                'service' => array(
                    'label' => __( 'Services', 'obenlo' ),
                    'desc'  => __( 'Cleaning, Handyman, Barber & Freelance', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:28px;height:28px;"><path d="M18.5 15h-6a4.5 4.5 0 0 0-4.5 4.5V28h2v-8.5A2.503 2.503 0 0 1 12.5 17h6a2.503 2.503 0 0 1 2.5 2.5V28h2v-8.5A4.5 4.5 0 0 0 18.5 15ZM15.5 14A5 5 0 1 0 10.5 9a5.006 5.006 0 0 0 5 5Zm0-8A3 3 0 1 1 12.5 9a3.003 3.003 0 0 1 3-3Z"/></svg>',
                    'color' => '#f97316',
                    'bg'    => 'linear-gradient(135deg, #fff7ed, #ffedd5)',
                ),
                'event' => array(
                    'label' => __( 'Events', 'obenlo' ),
                    'desc'  => __( 'Shows, live nights & performances', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:28px;height:28px;"><path d="M26 4H6a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h20a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 22H6V12h20Zm0-16H6V6h20ZM13 25a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Zm8 0a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Zm-4 0a1 1 0 0 1-1 1h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1Z"/></svg>',
                    'color' => '#e61e4d',
                    'bg'    => 'linear-gradient(135deg, #fff1f3, #ffe4e8)',
                ),
                'experience' => array(
                    'label' => __( 'Experiences', 'obenlo' ),
                    'desc'  => __( 'Tours, adventures & local activities', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:28px;height:28px;"><path d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm0 18H4V8h24ZM8 16a4 4 0 1 0 4-4 4.005 4.005 0 0 0-4 4Zm4-2a2 2 0 1 1-2 2 2.002 2.002 0 0 1 2-2Zm4 6v-1a4.005 4.005 0 0 0-4-4 4.005 4.005 0 0 0-4 4v1H6v-1a6.007 6.007 0 0 1 6-6 6.007 6.007 0 0 1 6 6v1Zm2-10h8v2h-8Zm0 4h8v2h-8Zm0 4h4v2h-4Z"/></svg>',
                    'color' => '#10b981',
                    'bg'    => 'linear-gradient(135deg, #ecfdf5, #d1fae5)',
                ),
                'stay' => array(
                    'label' => __( 'Stays', 'obenlo' ),
                    'desc'  => __( 'Hotels, guest houses & unique rooms', 'obenlo' ),
                    'icon'  => '<svg viewBox="0 0 32 32" fill="currentColor" style="width:28px;height:28px;"><path d="M28 12H22V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v24h2V18h16v10h2V14h6v14h2V14a2 2 0 0 0-2-2ZM12 14H4v-4h8Zm0-6H4V4h8Zm8 6h-6v-4h6Zm0-6h-6V4h6Z"/></svg>',
                    'color' => '#3b82f6',
                    'bg'    => 'linear-gradient(135deg, #eff6ff, #dbeafe)',
                ),
            );
            foreach ( $categories as $slug => $cat ) :
                $link = get_term_link( $slug, 'listing_type' );
                $link = is_wp_error($link) ? home_url('/') : $link;
            ?>
                <a href="<?php echo esc_url($link); ?>" class="ob-category-card">
                    <div class="ob-category-icon" style="background: <?php echo $cat['bg']; ?>; color: <?php echo $cat['color']; ?>;">
                        <?php echo $cat['icon']; ?>
                    </div>
                    <div>
                        <div class="ob-category-label"><?php echo esc_html($cat['label']); ?></div>
                        <div class="ob-category-desc"><?php echo esc_html($cat['desc']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php 
    $front_page_viator = get_option('obenlo_ai_front_page_viator', '');
    if (!empty(trim($front_page_viator))) : 
    ?>
    <!-- ── FEATURED TOURS & ACTIVITIES (VIATOR) ── -->
    <section class="featured-viator-widget" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Featured Tours & Activities', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Book top-rated experiences curated for you', 'obenlo'); ?></p>
            </div>
        </div>
        <div class="viator-widget-container" style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden;">
            <?php 
            // The widget script might have been escaped or restricted by wp_kses.
            // Outputting raw since it's an admin-only setting.
            echo wp_specialchars_decode($front_page_viator, ENT_QUOTES); 
            ?>
        </div>
    </section>
    <?php endif; ?>

    <?php 
    $front_page_tp = get_option('obenlo_ai_front_page_travelpayouts', '');
    if (!empty(trim($front_page_tp))) : 
    ?>
    <!-- ── FEATURED TRAVEL DEALS & TRANSPORT (TRAVELPAYOUTS) ── -->
    <section class="featured-travelpayouts-widget" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Travel Deals & Transport', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Find the best flights, hotels, and rentals for your trip', 'obenlo'); ?></p>
            </div>
        </div>
        <div class="travelpayouts-widget-container" style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); overflow:hidden;">
            <?php 
            echo wp_specialchars_decode($front_page_tp, ENT_QUOTES); 
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── FEATURED SERVICES ── -->
    <section class="featured-services" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Featured Services', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Top-rated professionals ready to help', 'obenlo'); ?></p>
            </div>
            <?php
            $srv_link = get_term_link('service', 'listing_type');
            $srv_link = is_wp_error($srv_link) ? home_url('/') : $srv_link;
            ?>
            <a href="<?php echo esc_url($srv_link); ?>" class="ob-view-all-btn">
                <?php esc_html_e('View all', 'obenlo'); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <?php
            $srv_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'service',
                    ),
                ),
            );
            if ( ! empty($demo_meta_query) ) {
                $srv_args['meta_query'] = array($demo_meta_query);
            }
            $srv_query = new WP_Query($srv_args);
            if ( $srv_query->have_posts() ) :
                while ( $srv_query->have_posts() ) : $srv_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #a1a1aa;">' . esc_html__('Professional services coming soon!', 'obenlo') . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- ── FEATURED EVENTS ── -->
    <section class="featured-events" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Featured Events', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Shows, performances & live experiences', 'obenlo'); ?></p>
            </div>
            <?php
            $evt_link = get_term_link('event', 'listing_type');
            $evt_link = is_wp_error($evt_link) ? home_url('/') : $evt_link;
            ?>
            <a href="<?php echo esc_url($evt_link); ?>" class="ob-view-all-btn">
                <?php esc_html_e('View all', 'obenlo'); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <?php
            $evt_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 8,
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
            if ( ! empty($demo_meta_query) ) {
                $evt_args['meta_query'] = array($demo_meta_query);
            }
            $evt_query = new WP_Query($evt_args);
            if ( $evt_query->have_posts() ) :
                while ( $evt_query->have_posts() ) : $evt_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #a1a1aa;">' . esc_html__('No events yet — check back soon! 🎉', 'obenlo') . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- ── FEATURED EXPERIENCES ── -->
    <section class="featured-experiences" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Featured Experiences', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Unforgettable local adventures', 'obenlo'); ?></p>
            </div>
            <?php
            $exp_link = get_term_link('experience', 'listing_type');
            $exp_link = is_wp_error($exp_link) ? home_url('/') : $exp_link;
            ?>
            <a href="<?php echo esc_url($exp_link); ?>" class="ob-view-all-btn">
                <?php esc_html_e('View all', 'obenlo'); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <?php
            $exp_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'experience',
                    ),
                ),
            );
            if ( ! empty($demo_meta_query) ) {
                $exp_args['meta_query'] = array($demo_meta_query);
            }
            $exp_query = new WP_Query($exp_args);
            if ( $exp_query->have_posts() ) :
                while ( $exp_query->have_posts() ) : $exp_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #a1a1aa;">' . esc_html__('Explore local experiences soon!', 'obenlo') . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- ── FEATURED STAYS ── -->
    <section class="featured-stays" style="margin-bottom: 80px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Featured Stays', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Handpicked rooms, houses & unique spaces', 'obenlo'); ?></p>
            </div>
            <?php
            $stay_link = get_term_link('stay', 'listing_type');
            $stay_link = is_wp_error($stay_link) ? home_url('/') : $stay_link;
            ?>
            <a href="<?php echo esc_url($stay_link); ?>" class="ob-view-all-btn">
                <?php esc_html_e('View all', 'obenlo'); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="front-page-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <?php
            $stay_args = array(
                'post_parent'    => 0,
                'post_type'      => 'listing',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'listing_type',
                        'field'    => 'slug',
                        'terms'    => 'stay',
                    ),
                ),
                'meta_key'  => '_obenlo_price',
                'orderby'   => 'meta_value_num',
                'order'     => 'DESC'
            );
            if ( ! empty($demo_meta_query) ) {
                $stay_args['meta_query'] = array($demo_meta_query);
            }
            $stay_query = new WP_Query($stay_args);
            if ( $stay_query->have_posts() ) :
                while ( $stay_query->have_posts() ) : $stay_query->the_post();
                    include locate_template('template-parts/content-listing-card.php');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="color: #a1a1aa;">' . esc_html__('Check back soon for featured stays!', 'obenlo') . '</p>';
            endif;
            ?>
        </div>
    </section>

    <!-- ── CUSTOMER REVIEWS (DARK) ── -->
    <section class="ob-reviews-section" style="margin-left: -40px; margin-right: -40px;">
        <div style="position: relative; z-index: 2;">
            <div class="ob-section-header" style="margin-bottom: 36px;">
                <div>
                    <h2 class="ob-section-title"><?php esc_html_e('What customers are saying', 'obenlo'); ?></h2>
                    <p class="ob-section-subtitle"><?php esc_html_e('Obenlo through the eyes of our community', 'obenlo'); ?></p>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $testimony_args = array(
                    'post_type'      => 'testimony',
                    'posts_per_page' => 5,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                );
                $testimony_query = new WP_Query($testimony_args);

                $stars_svg = '<svg viewBox="0 0 20 20" fill="#fbbf24" style="width:16px;height:16px;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';

                $render_review = function($author, $comment, $rating, $date) use ($stars_svg) {
                    $rating = intval($rating) ?: 5;
                    $stars_html = '';
                    for ($i = 0; $i < 5; $i++) {
                        $style = $i < $rating ? '' : 'opacity:0.25;';
                        $stars_html .= '<svg viewBox="0 0 20 20" fill="#fbbf24" style="width:16px;height:16px;' . $style . '"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
                    }
                    echo '<div class="ob-review-card">';
                    echo '<div class="ob-review-stars">' . $stars_html . '</div>';
                    echo '<p class="ob-review-text">"' . esc_html($comment) . '"</p>';
                    echo '<div class="ob-review-author-name">' . esc_html($author) . '</div>';
                    echo '<div class="ob-review-author-date">' . esc_html($date) . '</div>';
                    echo '</div>';
                };

                if ( $testimony_query->have_posts() ) :
                    while ( $testimony_query->have_posts() ) : $testimony_query->the_post();
                        $render_review(
                            get_the_author(),
                            get_the_content(),
                            get_post_meta(get_the_ID(), '_obenlo_testimony_rating', true),
                            get_the_date('F Y')
                        );
                    endwhile;
                    wp_reset_postdata();
                else :
                    $mock_reviews = array(
                        array('author' => 'Sarah M.',    'comment' => 'The mountain experience was breathtaking. The host was so professional and made everything seamless!', 'rating' => 5, 'date' => 'March 2026'),
                        array('author' => 'Michael K.',  'comment' => 'Obenlo made booking our summer stay incredibly simple. I found exactly what I needed in minutes.', 'rating' => 5, 'date' => 'February 2026'),
                        array('author' => 'Elena R.',    'comment' => 'Unique services I couldn\'t find anywhere else. A truly friendly, community-driven vibe.', 'rating' => 4, 'date' => 'January 2026'),
                    );
                    foreach ($mock_reviews as $r) {
                        $render_review($r['author'], $r['comment'], $r['rating'], $r['date']);
                    }
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- ── FEATURED HOSTS ── -->
    <section class="featured-hosts" style="margin-top: 60px; padding: 40px 0 60px;">
        <div class="ob-section-header">
            <div>
                <h2 class="ob-section-title"><?php esc_html_e('Meet Our Top Providers', 'obenlo'); ?></h2>
                <p class="ob-section-subtitle"><?php esc_html_e('Top-rated professionals ready to serve you', 'obenlo'); ?></p>
            </div>
            <a href="<?php echo esc_url(home_url('/hosts')); ?>" class="ob-view-all-btn">
                <?php esc_html_e('Meet them all', 'obenlo'); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>

        <div style="position: relative; margin: 0 -10px;">
            <!-- Left Arrow -->
            <button id="host-scroll-left" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); z-index: 10; width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.95); border: 1.5px solid #e4e4e8; box-shadow: 0 4px 14px rgba(0,0,0,0.10); cursor: pointer; display: flex; align-items: center; justify-content: center; color: #18181b; transition: all 0.2s; opacity: 0; pointer-events: none;" onmouseover="this.style.transform='translateY(-50%) scale(1.08)';this.style.background='#fff';" onmouseout="this.style.transform='translateY(-50%) scale(1)';this.style.background='rgba(255,255,255,0.95)';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"/></svg>
            </button>

            <!-- Scroll Container -->
            <div id="host-scroll-container" style="display: flex; gap: 20px; overflow-x: auto; padding: 10px 0 30px 0; -webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none; scroll-behavior: smooth;">
                <style>#host-scroll-container::-webkit-scrollbar { display: none; }</style>
                <?php
                $host_args = array('role' => 'host', 'number' => 10, 'orderby' => 'post_count', 'order' => 'DESC');
                $hosts = get_users($host_args);
                if (empty($hosts)) { $hosts = get_users(array('role' => 'administrator', 'number' => 10)); }

                foreach ($hosts as $host) : ?>
                    <div style="flex: 0 0 auto; width: 280px; scroll-snap-align: start;">
                        <?php include locate_template('template-parts/content-host-card.php'); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Right Arrow -->
            <button id="host-scroll-right" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 10; width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.95); border: 1.5px solid #e4e4e8; box-shadow: 0 4px 14px rgba(0,0,0,0.10); cursor: pointer; display: flex; align-items: center; justify-content: center; color: #18181b; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-50%) scale(1.08)';this.style.background='#fff';" onmouseout="this.style.transform='translateY(-50%) scale(1)';this.style.background='rgba(255,255,255,0.95)';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var container = document.getElementById('host-scroll-container');
            var leftBtn   = document.getElementById('host-scroll-left');
            var rightBtn  = document.getElementById('host-scroll-right');
            if (!container || !leftBtn || !rightBtn) return;

            var scrollAmount = 300;
            leftBtn.addEventListener('click', function() { container.scrollBy({ left: -scrollAmount, behavior: 'smooth' }); });
            rightBtn.addEventListener('click', function() { container.scrollBy({ left: scrollAmount, behavior: 'smooth' }); });

            function toggleArrows() {
                var atStart = container.scrollLeft <= 0;
                var atEnd   = container.scrollLeft + container.clientWidth >= container.scrollWidth - 10;
                leftBtn.style.opacity  = atStart ? '0' : '1';
                leftBtn.style.pointerEvents  = atStart ? 'none' : 'auto';
                rightBtn.style.opacity = atEnd   ? '0' : '1';
                rightBtn.style.pointerEvents = atEnd ? 'none' : 'auto';
            }

            container.addEventListener('scroll', toggleArrows, { passive: true });
            window.addEventListener('resize', toggleArrows);
            toggleArrows();
        });
        </script>
    </section>

</main>

<style>
/* Listing card hover image zoom */
.listing-card:hover .listing-thumbnail-wrapper img {
    transform: scale(1.05);
}
/* Responsive hero stats */
@media (max-width: 600px) {
    .ob-hero-stats { gap: 28px; }
    .ob-hero h1 { letter-spacing: -1px; }
    .ob-reviews-section { padding: 40px 24px; }
}
</style>

<?php get_footer(); ?>
