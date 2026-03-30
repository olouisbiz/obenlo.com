<?php
/**
 * Template part for displaying a listing card
 */

$price = get_post_meta( get_the_ID(), '_obenlo_price', true );
if(empty($price)) {
    // If no explicit price on parent, try to find lowest child price
    $children = get_children(array('post_parent' => get_the_ID(), 'post_type' => 'listing', 'numberposts' => 1));
    if(!empty($children)) {
        $first_child = array_values($children)[0];
        $price = get_post_meta($first_child->ID, '_obenlo_price', true);
    }
}

$location = get_post_meta( get_the_ID(), '_obenlo_location', true ); // Placeholder field
if(empty($location)){
     // Mock location
     $location = __( "Toronto, Canada", "obenlo" );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'listing-card' ); ?> style="display: flex; flex-direction: column; gap: 10px; cursor: pointer;" onclick="window.location='<?php echo esc_url( get_permalink() ); ?>';">
    
    <div class="listing-thumbnail-wrapper" style="width: 100%; aspect-ratio: 16/15; border-radius: 12px; overflow: hidden; position: relative; background: #e0e0e0;">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'large', array( 'style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;' ) ); ?>
        <?php else : ?>
            <!-- Fallback Skeleton Design if no image -->
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); font-size: 2rem;">
                ⛰️
            </div>
        <?php endif; ?>
        
        <!-- Category Badge -->
        <?php 
        $badge_terms = wp_get_post_terms( get_the_ID(), 'listing_type' );
        $badge_text = '';
        if( ! is_wp_error( $badge_terms ) && ! empty( $badge_terms ) ) {
            $main_cat = '';
            $sub_cat = '';
            foreach($badge_terms as $b_term) {
                if($b_term->parent != 0) {
                    $sub_cat = $b_term->name;
                    $p_term = get_term($b_term->parent, 'listing_type');
                    if(!is_wp_error($p_term) && $p_term) {
                        $main_cat = $p_term->name;
                    }
                    break;
                } else {
                    if(empty($main_cat)) {
                        $main_cat = $b_term->name;
                    }
                }
            }
            if($main_cat && $sub_cat) {
                $badge_text = $main_cat . ' | ' . $sub_cat;
            } elseif ($main_cat) {
                $badge_text = $main_cat;
            }
        }
        if($badge_text):
        ?>
        <div style="position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.95); padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; color: #222; box-shadow: 0 2px 6px rgba(0,0,0,0.08); z-index: 10; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php echo esc_html($badge_text); ?>
        </div>
        <?php endif; ?>
        <!-- Wishlist Heart Toggle -->
        <?php 
        $is_saved = false;
        if (class_exists('Obenlo_Booking_Wishlist')) {
            $is_saved = Obenlo_Booking_Wishlist::is_in_wishlist( get_the_ID() );
        }
        $heart_fill = $is_saved ? '#e61e4d' : 'rgba(0,0,0,0.5)';
        ?>
        <button class="wishlist-btn wishlist-heart <?php echo $is_saved ? 'active' : ''; ?>" 
                data-listing-id="<?php the_ID(); ?>"
                style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; transition: transform 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
             <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block; fill:<?php echo $heart_fill; ?>; height:18px; width:18px; stroke:#fff; stroke-width:2; overflow:visible; transition: fill 0.3s ease;">
                <path d="m16 28c7-4.733 14-10 14-17 0-1.792-.683-3.583-2.05-4.95-1.367-1.366-3.158-2.05-4.95-2.05-1.367-1.366-3.158-2.05-4.95-2.05-1.791 0-3.583.684-4.949 2.05l-2.051 2.051-2.05-2.051c-1.367-1.366-3.158-2.05-4.95-2.05-1.791 0-3.583.684-4.949 2.05-1.367 1.367-2.051 3.158-2.051 4.95 0 7 7 12.267 14 17z"></path>
             </svg>
        </button>
    </div>

    <div class="listing-info" style="display: flex; flex-direction: column; gap: 4px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <h2 style="margin: 0; font-size: 1rem; font-weight: 600; color: #222;"><?php echo esc_html( $location ); ?></h2>
            <div style="font-size: 0.9em; display: flex; align-items: center; gap: 4px; color: #222;">
                <span>★</span> <span>4.9</span>
            </div>
        </div>
        <div style="color: #717171; font-size: 0.95rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <?php the_title(); ?>
        </div>
        <?php if ( $price ) : ?>
            <div style="margin-top: 2px;">
                <span style="font-weight: 600; color: #222;">$<?php echo esc_html( $price ); ?></span> 
                <span style="color: #222; font-size: 0.95rem;">
                    <?php 
                        $terms = wp_get_post_terms( get_the_ID(), 'listing_type' );
                        if( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                            $cat = strtolower($terms[0]->name);
                            if(strpos($cat, 'stay') !== false) { esc_html_e( 'night', 'obenlo' ); }
                            elseif(strpos($cat, 'experience') !== false) { esc_html_e( 'person', 'obenlo' ); }
                            elseif(strpos($cat, 'service') !== false) { esc_html_e( 'session', 'obenlo' ); }
                            else { esc_html_e( 'unit', 'obenlo' ); }
                        } else {
                            esc_html_e( 'unit', 'obenlo' );
                        }
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</article>
