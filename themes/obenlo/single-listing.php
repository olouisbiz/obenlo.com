<?php
/**
 * The template for displaying a single listing and its booking flow.
 */

get_header(); ?>

<style>
    .loading-spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: obenlo-spin 0.8s linear infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    @keyframes obenlo-spin { to { transform: rotate(360deg); } }
</style>

<div class="obenlo-container">
    <?php while (have_posts()):
    the_post(); ?>
        
        <?php
        $obenlo_error = isset($_GET['obenlo_error']) ? sanitize_text_field($_GET['obenlo_error']) : '';
        if ($obenlo_error) {
            $error_message = '';
            switch ($obenlo_error) {
                case 'security_failed':
                    $error_message = 'Security check failed. Please refresh and try again.';
                    break;
                case 'invalid_data':
                    $error_message = 'Missing required booking information.';
                    break;
                case 'invalid_listing':
                    $error_message = 'Invalid listing reference.';
                    break;
                case 'capacity_exceeded':
                    $error_message = 'Guest count exceeds this listing\'s capacity.';
                    break;
                case 'host_away':
                    $error_message = 'The host is unavailable on these dates (Vacation Block).';
                    break;
                case 'day_unavailable':
                    $error_message = 'The host does not accept bookings on this day.';
                    break;
                case 'time_unavailable':
                    $error_message = 'The selected time is outside the host\'s operating hours.';
                    break;
                case 'already_booked':
                    $error_message = 'These dates or times are already booked. Please try another slot.';
                    break;
                case 'booking_error':
                    $error_message = 'There was an error processing your booking. Please try again.';
                    if (current_user_can('administrator')) {
                        $obenlo_msg = isset($_GET['obenlo_msg']) ? sanitize_text_field($_GET['obenlo_msg']) : '';
                        if ($obenlo_msg) {
                            $error_message .= ' (Stripe: ' . esc_html($obenlo_msg) . ')';
                        } else {
                            $error_message .= ' (Admin: Check your API Keys/Logs in WordPress Settings)';
                        }
                    }
                    break;
                case 'amount_too_low':
                    $error_message = 'The total booking amount is too low for online payment. Stripe requires a minimum of $0.50 USD.';
                    break;
                case 'invalid_payment':
                    $error_message = 'Invalid payment method selected.';
                    break;
                case 'unauthorized':
                    $error_message = 'Unauthorized: Please log in to perform this action.';
                    break;
                case 'discontinued_payment':
                    $error_message = 'This payment method has been discontinued. Please select a different one.';
                    break;
            }
            if ($error_message) {
                echo '<div style="background:#fff1f2; border:1px solid #fda4af; color:#9f1239; padding:20px; border-radius:12px; margin-bottom:30px; font-weight:700; display:flex; align-items:center; gap:10px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">';
                echo '<svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                echo esc_html($error_message);
                echo '</div>';
            }
        }
        ?>
        
        <?php
    $listing_id = get_the_ID();
    $parent_id = wp_get_post_parent_id($listing_id);
    $host_id = get_post_field('post_author', $listing_id);
    $host = get_userdata($host_id);
    $host_name = $host ? $host->display_name : 'Obenlo Host';
    $is_demo = get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes';

    $price = get_post_meta($listing_id, '_obenlo_price', true);
    $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);
    $addons_json = get_post_meta($listing_id, '_obenlo_addons_structured', true);
    $addons = json_decode($addons_json, true);
    if (!is_array($addons))
        $addons = array();

    $type_terms = wp_get_post_terms($listing_id, 'listing_type');
    if ((is_wp_error($type_terms) || empty($type_terms)) && $parent_id) {
        $type_terms = wp_get_post_terms($parent_id, 'listing_type');
    }

    $children = array();
    $has_children = false;
    if ($parent_id == 0) {
        $children = get_posts(array(
            'post_type' => 'listing',
            'post_parent' => $listing_id,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        $has_children = !empty($children);
    }

    $main_cat = '';
    $sub_cat = '';
    if (!is_wp_error($type_terms) && !empty($type_terms)) {
        foreach($type_terms as $b_term) {
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
    }

    if($main_cat && $sub_cat) {
        $type = $main_cat . ' | ' . $sub_cat;
        $category = strtolower($main_cat);
    } elseif ($main_cat) {
        $type = $main_cat;
        $category = strtolower($main_cat);
    } else {
        $type = 'Listing';
        $category = '';
    }

    // Fetch amenities from parent if this is a child
    $amenity_source_id = $parent_id ? $parent_id : $listing_id;
    $amenity_terms = wp_get_post_terms($amenity_source_id, 'listing_amenity', array('fields' => 'names'));

    // Fetch policies from parent
    $policy_type = get_post_meta($amenity_source_id, '_obenlo_policy_type', true) ?: 'global';
    if ($policy_type === 'global') {
        $policy_cancel = 'Standard Global Cancellation Policy: Free cancellation for 48 hours for a full refund.';
        $policy_refund = 'Standard Global Refund Policy: 50% refund up to 7 days before the start date.';
        $policy_other = 'Standard Rules: Respect the host and other guests.';
    }
    else {
        $policy_cancel = get_post_meta($amenity_source_id, '_obenlo_policy_cancel', true);
        $policy_refund = get_post_meta($amenity_source_id, '_obenlo_policy_refund', true);
        $policy_other = get_post_meta($amenity_source_id, '_obenlo_policy_other', true);
    }

    $avg_rating = 0;
    $review_count = 0;
    if (class_exists('Obenlo_Booking_Reviews')) {
        $avg_rating = Obenlo_Booking_Reviews::get_listing_average_rating($listing_id);
        $review_count = Obenlo_Booking_Reviews::get_listing_review_count($listing_id);
    }
?>

        <?php include(locate_template('template-parts/listing/header.php')); ?>

        <?php include(locate_template('template-parts/listing/gallery.php')); ?>
        <div class="listing-layout">
            
            <?php include(locate_template('template-parts/listing/main-content.php')); ?>

            <?php include(locate_template('template-parts/listing/sidebar.php')); ?>
            
        </div>

    <?php
endwhile; ?>
</div>

<?php get_footer(); ?>
