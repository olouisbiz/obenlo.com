<?php
/**
 * The template for displaying search results pages
 */

get_header();

$search_query = get_search_query();
$clean_query = sanitize_text_field( $search_query );

// 1. Search for Hosts (Users)
$host_args = array(
    'role__in'       => array('host', 'administrator'),
    'search'         => '*' . esc_attr( $clean_query ) . '*',
    'search_columns' => array( 'user_login', 'user_nicename', 'user_email', 'user_url', 'display_name' )
);
$host_query = new WP_User_Query( $host_args );
$hosts = $host_query->get_results();

// 2. Search for Categories (Taxonomies)
$categories = array();
if ( taxonomy_exists( 'listing_type' ) ) {
    $category_args = array(
        'taxonomy'   => 'listing_type',
        'hide_empty' => false,
        'name__like' => $clean_query,
    );
    $terms = get_terms( $category_args );
    if ( ! is_wp_error( $terms ) ) {
        $categories = $terms;
    }
}

// 3. Search for Locations (Listings by custom field)
$location_args = array(
    'post_type'  => 'listing',
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key'     => '_listing_location',
            'value'   => $clean_query,
            'compare' => 'LIKE'
        ),
        array(
            'key'     => '_obenlo_location',
            'value'   => $clean_query,
            'compare' => 'LIKE'
        )
    ),
    'posts_per_page' => -1
);
$location_query = new WP_Query( $location_args );

// 4. Search for Listings (Title / Content)
$listing_args = array(
    'post_type' => 'listing',
    's'         => $clean_query,
    'posts_per_page' => -1
);
$listing_query = new WP_Query( $listing_args );

// Combine listing IDs to prevent duplicates if a listing matches both location and text
$all_listing_ids = array();
if ( $location_query->have_posts() ) {
    foreach ( $location_query->posts as $post ) {
        $all_listing_ids[] = $post->ID;
    }
}
if ( $listing_query->have_posts() ) {
    foreach ( $listing_query->posts as $post ) {
        if ( ! in_array( $post->ID, $all_listing_ids ) ) {
            $all_listing_ids[] = $post->ID;
        }
    }
}

// Final combined listings query
$final_listings_query = false;
if ( ! empty( $all_listing_ids ) ) {
    $final_listings_query = new WP_Query(array(
        'post_type'      => 'listing',
        'post__in'       => $all_listing_ids,
        'posts_per_page' => -1,
        'orderby'        => 'post__in'
    ));
}

$has_results = !empty($hosts) || !empty($categories) || ( $final_listings_query && $final_listings_query->have_posts() );

?>

<div class="obenlo-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif;">
    <h1 style="font-size: 2em; margin-bottom: 30px;">
        Search results for "<?php echo esc_html( $clean_query ); ?>"
    </h1>

    <?php if ( ! $has_results ) : ?>
        <div style="text-align: center; padding: 60px 20px; background: #fafafa; border-radius: 12px;">
            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="fill:none; height:48px; width:48px; stroke:#ccc; stroke-width:2; margin-bottom: 20px; display:inline-block;"><circle cx="14" cy="14" r="10"></circle><path d="m21 21 8 8"></path></svg>
            <h2>No results found</h2>
            <p style="color: #666; font-size: 1.1em;">We couldn't find anything matching "<?php echo esc_html( $clean_query ); ?>". Try searching for a different host, location, or keyword.</p>
        </div>
    <?php else: ?>

        <?php /* --- HOSTS RESULTS --- */ ?>
        <?php if ( ! empty( $hosts ) ) : ?>
            <div class="search-section" style="margin-bottom: 50px;">
                <h2 style="font-size: 1.5em; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Hosts</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <?php foreach ( $hosts as $host ) : 
                        $logo_id = get_user_meta( $host->ID, 'obenlo_store_logo', true );
                        $avatar_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : get_avatar_url( $host->ID, ['size' => 150] );
                        $store_name = get_user_meta( $host->ID, 'obenlo_store_name', true ) ?: $host->display_name;
                    ?>
                        <div style="border: 1px solid #eee; border-radius: 12px; padding: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; background: #fff; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 10px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.02)';">
                             <a href="<?php echo esc_url( get_author_posts_url( $host->ID ) ); ?>" style="text-decoration: none; color: inherit; display: block; width: 100%;">
                                <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #f0f0f0;">
                                <h3 style="margin: 0 0 5px 0; font-size: 1.1em;"><?php echo esc_html( $store_name ); ?></h3>
                                <div style="font-size: 0.9em; color: #666;">View Profile</div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>


        <?php /* --- CATEGORIES RESULTS --- */ ?>
        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
            <div class="search-section" style="margin-bottom: 50px;">
                <h2 style="font-size: 1.5em; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Categories</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <?php foreach ( $categories as $category ) : 
                        $cat_link = get_term_link( $category );
                        $cat_link = is_wp_error( $cat_link ) ? home_url('/') : $cat_link;
                    ?>
                        <a href="<?php echo esc_url( $cat_link ); ?>" style="display: inline-block; padding: 12px 24px; background: #f7f7f7; color: #222; text-decoration: none; border-radius: 30px; font-weight: 500; font-size: 1rem; transition: background 0.2s, border 0.2s; border: 1px solid #ddd;" onmouseover="this.style.background='#fff'; this.style.borderColor='#222';" onmouseout="this.style.background='#f7f7f7'; this.style.borderColor='#ddd';">
                            <?php echo esc_html( $category->name ); ?> (<?php echo esc_html( $category->count ); ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>


        <?php /* --- LISTINGS & LOCATIONS RESULTS --- */ ?>
        <?php if ( $final_listings_query && $final_listings_query->have_posts() ) : ?>
            <div class="search-section">
                <h2 style="font-size: 1.5em; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Listings & Locations</h2>
                <div class="listings-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
                    <?php while ( $final_listings_query->have_posts() ) : $final_listings_query->the_post(); 
                        $price = get_post_meta( get_the_ID(), '_listing_price', true ) ?: get_post_meta( get_the_ID(), '_obenlo_price', true );
                        $location = get_post_meta( get_the_ID(), '_listing_location', true );
                        $type_terms = wp_get_post_terms( get_the_ID(), 'listing_type', array( 'fields' => 'names' ) );
                        $type = ( ! empty( $type_terms ) && ! is_wp_error( $type_terms ) ) ? implode( ', ', $type_terms ) : '';
                    ?>
                        <article class="listing-card" style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; background: #fff;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit; display: block;">
                                <div class="listing-thumbnail" style="height: 220px; background: #f0f0f0; position: relative;">
                                    <?php 
                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( 'medium', array( 'style' => 'width: 100%; height: 100%; object-fit: cover; display: block;' ) );
                                    } else {
                                        echo '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#ccc;">No Image</div>';
                                    }
                                    ?>
                                </div>
                                <div class="listing-info" style="padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                        <?php if($location) : ?>
                                            <div style="font-size: 0.9em; font-weight: bold; color: #222;"><?php echo esc_html($location); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <h3 style="margin: 0 0 10px 0; font-size: 1.1em; color: #666; font-weight: normal; line-height: 1.4;"><?php the_title(); ?></h3>
                                    <?php if($type) : ?>
                                        <div style="font-size: 0.85em; color: #888; margin-bottom: 8px;"><?php echo esc_html($type); ?></div>
                                    <?php endif; ?>
                                    
                                    <div style="font-weight: bold; color: #222; margin-top: 10px; font-size: 1.1em;">
                                       $<?php echo esc_html( $price ?: '0' ); ?> <span style="font-weight: normal; color: #666; font-size: 0.8em;">total</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php get_footer(); ?>
