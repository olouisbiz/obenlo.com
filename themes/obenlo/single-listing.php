<?php
/**
 * The template for displaying a single listing and its booking flow.
 */

get_header(); ?>

<div class="obenlo-container">
    <?php while (have_posts()):
    the_post(); ?>
        
        <?php
    $listing_id = get_the_ID();
    $parent_id = wp_get_post_parent_id($listing_id);

    $price = get_post_meta($listing_id, '_obenlo_price', true);
    $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);
    $addons_json = get_post_meta($listing_id, '_obenlo_addons_structured', true);
    $addons = json_decode($addons_json, true);
    if (!is_array($addons))
        $addons = array();

    $type_terms = wp_get_post_terms($listing_id, 'listing_type', array('fields' => 'names'));
    if ((is_wp_error($type_terms) || empty($type_terms)) && $parent_id) {
        $type_terms = wp_get_post_terms($parent_id, 'listing_type', array('fields' => 'names'));
    }
    $type = (!is_wp_error($type_terms) && !empty($type_terms)) ? implode(', ', $type_terms) : 'Listing';
    $category = (!is_wp_error($type_terms) && !empty($type_terms)) ? strtolower($type_terms[0]) : '';

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

        <div class="listing-header">
            <?php if ($parent_id): ?>
                <a href="<?php echo get_permalink($parent_id); ?>" style="display:inline-block; margin-bottom:15px; color:#666; text-decoration:none;">
                    &larr; Back to <?php echo esc_html(get_the_title($parent_id)); ?>
                </a>
            <?php
    endif; ?>
            
            <h1 class="listing-title" style="margin-bottom: 5px;"><?php the_title(); ?></h1>
            <div style="color: #666; font-size: 0.9em; display: flex; align-items: center; gap: 5px;">
                <?php if ($avg_rating > 0): ?>
                    <span style="color: #333; font-weight: bold;">★ <?php echo $avg_rating; ?></span>
                    <span>&bull;</span>
                    <a href="#reviews" onclick="openTab(event, 'tab-reviews')" style="color: #666; text-decoration: underline;"><?php echo $review_count; ?> reviews</a>
                    <span>&bull;</span>
                <?php
    endif; ?>
                <?php echo esc_html($type); ?> &bull; Hosted by <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" style="color:#e61e4d; font-weight:bold; text-decoration:none;"><?php the_author(); ?></a>
            </div>
        </div>

        <?php
    // Fetch up to 10 images from attached media
    $images = get_attached_media('image', $listing_id);
    $image_urls = array();
    if (has_post_thumbnail()) {
        $image_urls[] = get_the_post_thumbnail_url($listing_id, 'large');
    }
    foreach ($images as $img) {
        $url = wp_get_attachment_image_url($img->ID, 'large');
        if (!in_array($url, $image_urls) && count($image_urls) < 10) {
            $image_urls[] = $url;
        }
    }
?>
        <?php if (!empty($image_urls)): ?>
            <div class="listing-gallery">
                <div class="gallery-grid">
                    <div class="gallery-main" style="background:url('<?php echo esc_url($image_urls[0]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(0)"></div>
                    <div style="display:grid; grid-template-rows:1fr 1fr; gap:10px;">
                        <?php if (isset($image_urls[1])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[1]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(1)"></div>
                        <?php
        endif; ?>
                        <?php if (isset($image_urls[2])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[2]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(2)"></div>
                        <?php
        endif; ?>
                    </div>
                    <div style="display:grid; grid-template-rows:1fr 1fr; gap:10px;">
                        <?php if (isset($image_urls[3])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[3]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(3)"></div>
                        <?php
        endif; ?>
                        <?php if (isset($image_urls[4])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[4]); ?>') center/cover; cursor:pointer; position:relative;" onclick="openLightbox(4)">
                                <?php if (count($image_urls) > 5): ?>
                                    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.4); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.2em;">+ <?php echo count($image_urls) - 5; ?> more</div>
                                <?php
            endif; ?>
                            </div>
                        <?php
        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lightbox Modal -->
            <div id="obenlo-lightbox" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center;">
                <button onclick="closeLightbox()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:2em; cursor:pointer;">&times;</button>
                <div style="position:relative; width:80%; height:80%; display:flex; align-items:center; justify-content:center;">
                    <button onclick="prevImage()" style="position:absolute; left:-50px; background:none; border:none; color:white; font-size:3em; cursor:pointer;">&#10094;</button>
                    <img id="lightbox-img" src="" style="max-width:100%; max-height:100%; object-fit:contain;">
                    <button onclick="nextImage()" style="position:absolute; right:-50px; background:none; border:none; color:white; font-size:3em; cursor:pointer;">&#10095;</button>
                </div>
                <div id="lightbox-counter" style="position:absolute; bottom:20px; color:white; font-size:1.1em;"></div>
            </div>

            <script>
                var galleryImages = <?php echo json_encode($image_urls); ?>;
                var currentImageIdx = 0;
                function openLightbox(idx) {
                    if(!galleryImages || galleryImages.length === 0) return;
                    currentImageIdx = idx;
                    document.getElementById('obenlo-lightbox').style.display = 'flex';
                    updateLightbox();
                }
                function closeLightbox() {
                    document.getElementById('obenlo-lightbox').style.display = 'none';
                }
                function prevImage() {
                    currentImageIdx = (currentImageIdx > 0) ? currentImageIdx - 1 : galleryImages.length - 1;
                    updateLightbox();
                }
                function nextImage() {
                    currentImageIdx = (currentImageIdx < galleryImages.length - 1) ? currentImageIdx + 1 : 0;
                    updateLightbox();
                }
                function updateLightbox() {
                    document.getElementById('lightbox-img').src = galleryImages[currentImageIdx];
                    document.getElementById('lightbox-counter').innerText = (currentImageIdx + 1) + ' / ' + galleryImages.length;
                }
            </script>
        <?php
    endif; ?>

        <div class="listing-layout">
            
            <div class="listing-main-content">
                <?php if (empty($image_urls) && has_post_thumbnail()): ?>
                    <div class="listing-featured-image" style="margin-bottom: 20px; border-radius: 12px; overflow: hidden;">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; display: block;')); ?>
                    </div>
                <?php
    endif; ?>

                <!-- Tabbed Interface for Content -->
                <div class="listing-tabs" style="margin-bottom: 20px; border-bottom: 1px solid #ddd; display: flex; gap: 20px;">
                    <button class="tab-btn active" onclick="openTab(event, 'tab-about')" style="background: none; border: none; font-size: 1.1em; font-weight: bold; padding: 10px 0; cursor: pointer; border-bottom: 3px solid #e61e4d; color: #e61e4d;">About</button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-amenities')" style="background: none; border: none; font-size: 1.1em; font-weight: bold; padding: 10px 0; cursor: pointer; border-bottom: 3px solid transparent; color: #666;">Amenities</button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-policies')" style="background: none; border: none; font-size: 1.1em; font-weight: bold; padding: 10px 0; cursor: pointer; border-bottom: 3px solid transparent; color: #666;">Policies</button>
                    <button class="tab-btn" id="reviews-tab-trigger" onclick="openTab(event, 'tab-reviews')" style="background: none; border: none; font-size: 1.1em; font-weight: bold; padding: 10px 0; cursor: pointer; border-bottom: 3px solid transparent; color: #666;">Reviews (<?php echo $review_count; ?>)</button>
                </div>

                <div id="tab-about" class="tab-content" style="display: block; line-height: 1.6; font-size: 1.1em; margin-bottom: 30px;">
                    <?php the_content(); ?>
                </div>

                <div id="tab-amenities" class="tab-content" style="display: none; margin-bottom: 30px;">
                    <?php if (!empty($amenity_terms) && !is_wp_error($amenity_terms)): ?>
                        <h3>What this place offers</h3>
                        <ul style="list-style: none; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:20px;">
                            <?php foreach ($amenity_terms as $amenity): ?>
                                <li style="display: flex; align-items: center; gap: 10px;">
                                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;height:24px;width:24px;fill:currentColor"><path d="m16 31c-8.284 0-15-6.716-15-15 0-8.284 6.716-15 15-15 8.284 0 15 6.716 15 15 0 8.284-6.716 15-15 15zm0-28c-7.18 0-13 5.82-13 13s5.82 13 13 13 13-5.82 13-13-5.82-13-13-13zm7.062 8.799-9.143 9.77-4.52-4.9-1.472 1.358 5.378 5.828.618.669.608-.65 9.943-10.627z"/></svg>
                                    <?php echo esc_html($amenity); ?>
                                </li>
                            <?php
        endforeach; ?>
                        </ul>
                    <?php
    else: ?>
                        <p>No amenities listed.</p>
                    <?php
    endif; ?>
                </div>

                <div id="tab-policies" class="tab-content" style="display: none; margin-bottom: 30px;">
                    <h3>Stay Policies</h3>
                    <div style="margin-top:20px; display:flex; flex-direction:column; gap:20px;">
                        <?php if ($policy_cancel): ?>
                            <div>
                                <strong>Cancellation Policy:</strong><br>
                                <p style="margin-top:5px; color:#555;"><?php echo nl2br(esc_html($policy_cancel)); ?></p>
                            </div>
                        <?php
    endif; ?>
                        <?php if ($policy_refund): ?>
                            <div>
                                <strong>Refund Policy:</strong><br>
                                <p style="margin-top:5px; color:#555;"><?php echo nl2br(esc_html($policy_refund)); ?></p>
                            </div>
                        <?php
    endif; ?>
                        <?php if ($policy_other): ?>
                            <div>
                                <strong>Other Rules & Info:</strong><br>
                                <p style="margin-top:5px; color:#555;"><?php echo nl2br(esc_html($policy_other)); ?></p>
                            </div>
                        <?php
    endif; ?>
                        <?php if (!$policy_cancel && !$policy_refund && !$policy_other): ?>
                            <p>No special policies listed.</p>
                        <?php
    endif; ?>
                    </div>
                </div>
                <div id="tab-reviews" class="tab-content" style="display: none; margin-bottom: 30px;">
                    <div id="reviews-anchor"></div>
                    <h3>Guest Reviews</h3>
                    
                    <?php if ($avg_rating > 0): ?>
                        <div style="font-size: 1.5em; font-weight: bold; margin: 20px 0; display: flex; align-items: center; gap: 10px;">
                            ★ <?php echo $avg_rating; ?> &bull; <?php echo $review_count; ?> reviews
                        </div>
                    <?php
    endif; ?>

                    <div class="reviews-list" style="margin-top: 30px; display: flex; flex-direction: column; gap: 25px;">
                        <?php
    $comments = get_comments(array(
        'post_id' => $listing_id,
        'status' => 'approve',
        'parent' => 0
    ));

    if (empty($comments)): ?>
                            <p>No reviews yet. Be the first to leave one!</p>
                        <?php
    else:
        foreach ($comments as $comment):
            $rating = get_comment_meta($comment->comment_ID, '_obenlo_rating', true);
?>
                                <div class="review-item" style="border-bottom: 1px solid #eee; padding-bottom: 25px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                        <div>
                                            <strong style="font-size: 1.1em;"><?php echo esc_html($comment->comment_author); ?></strong>
                                            <div style="font-size: 0.9em; color: #666;"><?php echo get_comment_date('F Y', $comment->comment_ID); ?></div>
                                        </div>
                                        <?php if ($rating): ?>
                                            <div style="color: #FFD700; font-size: 1.1em;">
                                                <?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?>
                                            </div>
                                        <?php
            endif; ?>
                                    </div>
                                    <div style="line-height: 1.6; color: #444;">
                                        <?php echo wpautop(esc_html($comment->comment_content)); ?>
                                    </div>
                                    
                                    <?php
            // Check for host replies
            $replies = get_comments(array(
                'parent' => $comment->comment_ID,
                'status' => 'approve'
            ));
            foreach ($replies as $reply): ?>
                                        <div class="review-reply" style="margin-top: 15px; margin-left: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                                            <strong>Response from host:</strong>
                                            <div style="margin-top: 5px; color: #555;"><?php echo wpautop(esc_html($reply->comment_content)); ?></div>
                                        </div>
                                    <?php
            endforeach; ?>
                                </div>
                        <?php
        endforeach;
    endif; ?>
                    </div>

                    <?php if (is_user_logged_in()): ?>
                        <div class="review-form-container" style="margin-top: 40px; padding: 25px; background: #fff; border: 1px solid #ddd; border-radius: 12px;">
                            <h3 style="margin-top: 0;">Leave a Review</h3>
                            <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" id="reviewform">
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 10px;">Rating</label>
                                    <div class="star-rating" style="display: flex; gap: 5px; font-size: 2em; cursor: pointer; color: #ccc;">
                                        <span class="star" data-value="1">★</span>
                                        <span class="star" data-value="2">★</span>
                                        <span class="star" data-value="3">★</span>
                                        <span class="star" data-value="4">★</span>
                                        <span class="star" data-value="5">★</span>
                                    </div>
                                    <input type="hidden" name="rating" id="selected-rating" value="" required>
                                </div>
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 10px;">Your Review</label>
                                    <textarea name="comment" rows="5" required style="width: 100%; padding: 15px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit;"></textarea>
                                </div>
                                <input name="submit" type="submit" value="Submit Review" style="padding: 12px 30px; background: #333; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                                <?php comment_id_fields(); ?>
                                <?php do_action('comment_form', $listing_id); ?>
                            </form>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var stars = document.querySelectorAll('.star-rating .star');
                                var ratingInput = document.getElementById('selected-rating');
                                
                                stars.forEach(function(star) {
                                    star.addEventListener('click', function() {
                                        var val = this.getAttribute('data-value');
                                        ratingInput.value = val;
                                        stars.forEach(function(s) {
                                            if (parseInt(s.getAttribute('data-value')) <= parseInt(val)) {
                                                s.style.color = '#FFD700';
                                            } else {
                                                s.style.color = '#ccc';
                                            }
                                        });
                                    });
                                    star.addEventListener('mouseover', function() {
                                        var val = this.getAttribute('data-value');
                                        stars.forEach(function(s) {
                                            if (parseInt(s.getAttribute('data-value')) <= parseInt(val)) {
                                                s.style.color = '#FFD700';
                                            } else {
                                                s.style.color = '#ccc';
                                            }
                                        });
                                    });
                                    star.addEventListener('mouseout', function() {
                                        var currentVal = ratingInput.value || 0;
                                        stars.forEach(function(s) {
                                            if (parseInt(s.getAttribute('data-value')) <= parseInt(currentVal)) {
                                                s.style.color = '#FFD700';
                                            } else {
                                                s.style.color = '#ccc';
                                            }
                                        });
                                    });
                                });
                            });
                        </script>
                    <?php
    else: ?>
                        <p style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px; text-align: center;">
                            Please <a href="<?php echo wp_login_url(get_permalink()); ?>" style="color: #e61e4d; font-weight: bold;">log in</a> to leave a review.
                        </p>
                    <?php
    endif; ?>
                </div>

                <script>
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("tab-content");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("tab-btn");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                        tablinks[i].style.color = "#666";
                        tablinks[i].style.borderBottomColor = "transparent";
                    }
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.className += " active";
                    evt.currentTarget.style.color = "#e61e4d";
                    evt.currentTarget.style.borderBottomColor = "#e61e4d";
                }
                </script>

                <?php
    // Check for Child Listings
    $children = get_posts(array(
        'post_type' => 'listing',
        'post_parent' => $listing_id,
        'posts_per_page' => -1
    ));
    $has_children = !empty($children);
?>

                <?php if (!empty($addons)): ?>
                    <div class="listing-addons" style="margin-bottom: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <h3>Available Addons</h3>
                        <ul style="list-style: none; padding: 0; display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($addons as $addon): ?>
                                <li style="display:flex; justify-content:space-between; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                    <strong><?php echo esc_html($addon['name']); ?></strong>
                                    <span>$<?php echo esc_html($addon['price']); ?></span>
                                </li>
                            <?php
        endforeach; ?>
                        </ul>
                    </div>
                <?php
    endif; ?>
            </div>

            <div class="listing-sidebar">
                <div class="booking-widget">
                    
                    <?php if ($has_children): ?>
                        <!-- Parent Listing with Children: Force Selection -->
                        <h3 style="margin-top:0;">Select an option to book</h3>
                        <p style="color:#666; font-size:0.9em; margin-bottom: 20px;">Please choose from the available options below to check dates and reserve.</p>
                        
                        <div class="child-options-list" style="display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($children as $child):
            $child_price = get_post_meta($child->ID, '_obenlo_price', true);
?>
                                <a href="<?php echo get_permalink($child->ID); ?>" style="display:block; padding:15px; border:1px solid #eee; border-radius:8px; text-decoration:none; color:#333; transition:0.2s; background:#f9f9f9;">
                                    <div style="font-weight:bold; margin-bottom:5px;"><?php echo esc_html($child->post_title); ?></div>
                                    <div style="font-size:0.9em; color:#e61e4d;">From $<?php echo esc_html($child_price); ?></div>
                                </a>
                            <?php
        endforeach; ?>
                        </div>

                    <?php
    else: ?>
                        <?php
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';
        $duration_val = get_post_meta($listing_id, '_obenlo_duration_val', true);
        $duration_unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
        $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';

        $cfg = array('mode' => 'date_range', 'price_unit' => '/ unit', 'date_label' => 'Dates', 'has_guests' => true);

        if ($pricing_model === 'per_night') {
            $cfg = array('mode' => 'date_range', 'price_unit' => '/ night', 'date_label' => 'Check-in / Check-out', 'has_guests' => true);
        }
        elseif ($pricing_model === 'per_day') {
            $cfg = array('mode' => 'date_range', 'price_unit' => '/ day', 'date_label' => 'Select Dates', 'has_guests' => true);
        }
        elseif ($pricing_model === 'per_hour') {
            $cfg = array('mode' => 'datetime_duration', 'price_unit' => '/ hour', 'date_label' => 'Select Date & Time', 'has_guests' => false);
        }
        elseif ($pricing_model === 'per_session') {
            $cfg_mode = ($requires_slots === 'yes') ? 'timeslot' : 'datetime';
            $cfg = array('mode' => $cfg_mode, 'price_unit' => '/ session', 'date_label' => 'Select Appointment Date', 'has_guests' => false);
        }
        elseif ($pricing_model === 'per_person') {
            $cfg = array('mode' => 'event_datetime', 'price_unit' => '/ person', 'date_label' => 'Select Event Time', 'has_guests' => true);
        }
        elseif ($pricing_model === 'flat_fee') {
            $cfg = array('mode' => 'date_only', 'price_unit' => 'flat fee', 'date_label' => 'Select Date', 'has_guests' => false);
        }

        $booking_mode = $cfg['mode'];
        $price_unit = $cfg['price_unit'];
        $date_label = $cfg['date_label'];
        $form_has_guests = $cfg['has_guests'] && $capacity;

        $host_id = get_post_field('post_author', $listing_id);
        $business_hours = get_user_meta($host_id, '_obenlo_business_hours', true) ?: array();
        $vacation_blocks = get_user_meta($host_id, '_obenlo_vacation_blocks', true) ?: array();
?>

                        <!-- Price Header -->
                        <div class="price-header">
                            <span class="price-value">$<?php echo esc_html($price); ?></span>
                            <span style="color: #666;"><?php echo esc_html($price_unit); ?></span>
                        </div>

                        <form class="booking-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                            <input type="hidden" name="action" value="obenlo_submit_booking">
                            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                            <input type="hidden" name="booking_mode" value="<?php echo esc_attr($booking_mode); ?>">
                            <?php wp_nonce_field('obenlo_submit_booking_action', 'obenlo_booking_nonce'); ?>

                            <!-- ── Date / Time Inputs ── -->
                            <?php if ($booking_mode === 'date_range'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <div style="display: flex; border: 1px solid #ccc; border-radius: 6px; overflow: hidden;">
                                        <input type="date" name="start_date" required id="start_date_input" style="border: none; padding: 10px; width: 50%; border-right: 1px solid #ccc;" min="<?php echo date('Y-m-d'); ?>">
                                        <input type="date" name="end_date" required id="end_date_input" style="border: none; padding: 10px; width: 50%;" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            <?php
        elseif ($booking_mode === 'datetime_duration'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="datetime-local" name="start_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                                <div class="form-row" style="margin-top:10px;">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">Duration (Hours)</label>
                                    <input type="number" name="booking_duration" min="1" value="1" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;">
                                </div>
                                <input type="hidden" name="booking_duration_unit" value="hours">
                            <?php
        elseif ($booking_mode === 'timeslot'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="date" id="timeslot_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-row" id="timeslots_container" style="margin-top:15px; display:none;">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 10px;">Select Available Time</label>
                                    <div id="timeslots_grid" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;"></div>
                                </div>
                                <div id="timeslot_loading" style="display:none; margin-top:10px; font-size:0.9em; color:#666;">Checking availability...</div>
                                <input type="hidden" name="start_date" id="final_start_date" required>
                                <?php if ($duration_val): ?>
                                    <input type="hidden" name="booking_duration" value="<?php echo esc_attr($duration_val); ?>">
                                    <input type="hidden" name="booking_duration_unit" value="<?php echo esc_attr($duration_unit); ?>">
                                <?php
            endif; ?>
                            <?php
        elseif ($booking_mode === 'datetime' || $booking_mode === 'event_datetime'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="datetime-local" name="start_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                            <?php
        else: /* date_only */?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="date" name="start_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            <?php
        endif; ?>

                            <!-- ── Guests / Tickets ── -->
                            <?php if ($form_has_guests): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">
                                        <?php echo in_array($booking_mode, ['event_datetime']) ? 'Tickets' : 'Guests'; ?>
                                        <?php if ($capacity)
                echo '(max ' . esc_html($capacity) . ')'; ?>
                                    </label>
                                    <input type="number" name="guests" min="1" <?php if ($capacity)
                echo 'max="' . esc_attr($capacity) . '"'; ?> value="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                                </div>
                            <?php
        else: ?>
                                <input type="hidden" name="guests" value="1">
                            <?php
        endif; ?>

                            <!-- ── Payment Method ── -->
                            <div class="form-row" style="margin-top: 10px;">
                                <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">Payment Method</label>
                                <select name="payment_method" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                                    <option value="stripe">Credit Card (Stripe)</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>

                            <?php if (!empty($addons)): ?>
                                <div class="form-row" style="margin-top: 10px;">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">Add Extras</label>
                                    <div style="display:flex; flex-direction:column; gap:8px;">
                                        <?php foreach ($addons as $idx => $addon): ?>
                                            <label style="display:flex; align-items:center; justify-content:space-between; padding:10px; border:1px solid #eee; border-radius:6px; cursor:pointer;">
                                                <span style="display:flex; align-items:center; gap:8px;">
                                                    <input type="checkbox" name="selected_addons[]" value="<?php echo esc_attr($addon['name'] . '|' . $addon['price']); ?>" class="addon-checkbox" data-price="<?php echo esc_attr($addon['price']); ?>">
                                                    <span><?php echo esc_html($addon['name']); ?></span>
                                                </span>
                                                <span style="color:#666;">+$<?php echo esc_html($addon['price']); ?></span>
                                            </label>
                                        <?php
            endforeach; ?>
                                    </div>
                                </div>
                            <?php
        endif; ?>

                            <div style="margin-top:20px; padding-top:20px; border-top:1px solid #eee; display:flex; justify-content:space-between; font-size:1.2em; font-weight:bold;">
                                <span>Total:</span>
                                <span>$<span id="live-total"><?php echo esc_html($price); ?></span></span>
                            </div>

                            <button type="submit" class="reserve-btn">
                                <?php echo in_array($booking_mode, ['event_datetime']) ? 'Buy Tickets' : 'Reserve'; ?>
                            </button>
                            <p style="text-align: center; font-size: 0.8em; color: #666; margin-top: 10px;">You won't be charged yet</p>
                        </form>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var form = document.querySelector('.booking-form');
                            var liveTotalEl = document.getElementById('live-total');
                            var basePrice = parseFloat(<?php echo json_encode($price); ?>) || 0;
                            var bookingMode = "<?php echo esc_js($booking_mode); ?>";

                            function calculateTotal() {
                                var total = basePrice;

                                if (bookingMode === 'date_range') {
                                    var s = form.querySelector('input[name="start_date"]');
                                    var e = form.querySelector('input[name="end_date"]');
                                    if (s && e && s.value && e.value) {
                                        var days = Math.ceil((new Date(e.value) - new Date(s.value)) / 86400000);
                                        if (days > 0) total = basePrice * days;
                                    }
                                } else if (bookingMode === 'datetime_duration') {
                                    var h = form.querySelector('input[name="booking_duration"]');
                                    var hrs = (h && h.value) ? parseFloat(h.value) || 1 : 1;
                                    total = basePrice * hrs;
                                } else if (bookingMode === 'date_only' || bookingMode === 'event_datetime' || bookingMode === 'datetime' || bookingMode === 'timeslot') {
                                    var g = form.querySelector('input[name="guests"]');
                                    var hasGuests = <?php echo $form_has_guests ? 'true' : 'false'; ?>;
                                    var qty = (g && g.value && hasGuests) ? parseInt(g.value) || 1 : 1;
                                    total = basePrice * qty;
                                }

                                // Add addons
                                form.querySelectorAll('.addon-checkbox:checked').forEach(function(cb) {
                                    total += parseFloat(cb.getAttribute('data-price')) || 0;
                                });

                                liveTotalEl.innerText = total.toFixed(2);
                            }

                            var businessHours = <?php echo json_encode($business_hours); ?>;
                            var vacationBlocks = <?php echo json_encode($vacation_blocks); ?>;
                            var reserveBtn = form.querySelector('.reserve-btn');
                            var errorMsgEl = document.createElement('div');
                            errorMsgEl.style.color = '#e61e4d';
                            errorMsgEl.style.marginTop = '10px';
                            errorMsgEl.style.fontWeight = 'bold';
                            errorMsgEl.style.fontSize = '0.9em';
                            errorMsgEl.style.textAlign = 'center';
                            if (reserveBtn) {
                                reserveBtn.parentNode.insertBefore(errorMsgEl, reserveBtn.nextSibling);
                            }

                            function checkAvailability() {
                                errorMsgEl.innerText = '';
                                if (!reserveBtn) return;
                                reserveBtn.disabled = false;
                                reserveBtn.style.opacity = '1';
                                reserveBtn.style.cursor = 'pointer';

                                var s = form.querySelector('input[name="start_date"]');
                                var e = form.querySelector('input[name="end_date"]');
                                
                                if (!s || !s.value) return;

                                var startD = new Date(s.value);
                                var endD = (e && e.value) ? new Date(e.value) : startD;

                                // 1. Check Vacation Blocks
                                if (vacationBlocks && vacationBlocks.length > 0) {
                                    for (var i = 0; i < vacationBlocks.length; i++) {
                                        var vStart = new Date(vacationBlocks[i].start + 'T00:00:00');
                                        var vEnd = new Date(vacationBlocks[i].end + 'T23:59:59');
                                        if (startD <= vEnd && endD >= vStart) {
                                            errorMsgEl.innerText = 'These dates are unavailable (Host is away).';
                                            reserveBtn.disabled = true;
                                            reserveBtn.style.opacity = '0.5';
                                            reserveBtn.style.cursor = 'not-allowed';
                                            return;
                                        }
                                    }
                                }

                                // 2. Check Business Hours for Exact Time
                                if ((s.type === 'datetime-local' || s.type === 'time') && s.value && s.value.includes('T')) {
                                    var dayOfWeek = startD.toLocaleDateString('en-US', {weekday: 'long'}).toLowerCase();
                                    
                                    // Extract HH:MM
                                    var tMatch = s.value.match(/T(\d{2}:\d{2})/);
                                    var timeStr = tMatch ? tMatch[1] : '';
                                    
                                    if (timeStr && businessHours && businessHours[dayOfWeek]) {
                                        var conf = businessHours[dayOfWeek];
                                        if (conf.active !== 'yes') {
                                            errorMsgEl.innerText = 'The host does not accept bookings on ' + dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1) + 's.';
                                            reserveBtn.disabled = true;
                                            reserveBtn.style.opacity = '0.5';
                                            reserveBtn.style.cursor = 'not-allowed';
                                            return;
                                        }
                                        if (timeStr < conf.start || timeStr > conf.end) {
                                            errorMsgEl.innerText = 'Available hours on ' + dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1) + ' are ' + conf.start + ' to ' + conf.end + '.';
                                            reserveBtn.disabled = true;
                                            reserveBtn.style.opacity = '0.5';
                                            reserveBtn.style.cursor = 'not-allowed';
                                            return;
                                        }
                                    }
                                }
                            }

                            // 3. Generate Timeslots if mode is timeslot
                            var timeslotDateInput = document.getElementById('timeslot_date');
                            if (bookingMode === 'timeslot' && timeslotDateInput) {
                                // Disable reserve initially until a slot is chosen
                                if (reserveBtn) {
                                    reserveBtn.disabled = true;
                                    reserveBtn.style.opacity = '0.5';
                                }
                                
                                timeslotDateInput.addEventListener('change', function() {
                                    var selectedDate = this.value;
                                    var container = document.getElementById('timeslots_container');
                                    var grid = document.getElementById('timeslots_grid');
                                    var loading = document.getElementById('timeslot_loading');
                                    var finalStart = document.getElementById('final_start_date');
                                    
                                    grid.innerHTML = '';
                                    finalStart.value = '';
                                    if (reserveBtn) {
                                        reserveBtn.disabled = true;
                                        reserveBtn.style.opacity = '0.5';
                                    }

                                    if (!selectedDate) {
                                        container.style.display = 'none';
                                        return;
                                    }
                                    
                                    // Check if date is in vacation blocks
                                    var sD = new Date(selectedDate);
                                    if (vacationBlocks && vacationBlocks.length > 0) {
                                        for (var i = 0; i < vacationBlocks.length; i++) {
                                            var vStart = new Date(vacationBlocks[i].start + 'T00:00:00');
                                            var vEnd = new Date(vacationBlocks[i].end + 'T23:59:59');
                                            if (sD <= vEnd && sD >= vStart) {
                                                container.style.display = 'block';
                                                grid.innerHTML = '<div style="grid-column:1/-1; color:#e61e4d; font-size:0.9em; font-weight:bold;">Host is away on this date.</div>';
                                                return;
                                            }
                                        }
                                    }

                                    container.style.display = 'block';
                                    loading.style.display = 'block';

                                    var data = new FormData();
                                    data.append('action', 'obenlo_get_timeslots');
                                    data.append('listing_id', <?php echo esc_js($listing_id); ?>);
                                    data.append('date', selectedDate);

                                    fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                                        method: 'POST',
                                        body: data
                                    })
                                    .then(res => res.json())
                                    .then(response => {
                                        loading.style.display = 'none';
                                        if (response.success && response.data.slots && response.data.slots.length > 0) {
                                            response.data.slots.forEach(function(slot) {
                                                var btn = document.createElement('button');
                                                btn.type = 'button';
                                                btn.className = 'timeslot-btn';
                                                btn.innerText = slot.label;
                                                btn.style.padding = '10px';
                                                btn.style.border = '1px solid #ccc';
                                                btn.style.borderRadius = '6px';
                                                btn.style.background = '#fff';
                                                btn.style.cursor = 'pointer';
                                                btn.style.fontWeight = 'bold';
                                                
                                                btn.addEventListener('click', function() {
                                                    document.querySelectorAll('.timeslot-btn').forEach(b => {
                                                        b.style.background = '#fff';
                                                        b.style.color = '#333';
                                                        b.style.borderColor = '#ccc';
                                                    });
                                                    btn.style.background = '#e61e4d';
                                                    btn.style.color = '#fff';
                                                    btn.style.borderColor = '#e61e4d';
                                                    finalStart.value = selectedDate + 'T' + slot.time;
                                                    
                                                    if (reserveBtn) {
                                                        reserveBtn.disabled = false;
                                                        reserveBtn.style.opacity = '1';
                                                    }
                                                    errorMsgEl.innerText = '';
                                                });
                                                grid.appendChild(btn);
                                            });
                                        } else {
                                            var msg = (response.data && response.data.message) ? response.data.message : 'No available slots on this date.';
                                            grid.innerHTML = '<div style="grid-column:1/-1; color:#666; font-size:0.9em;">' + msg + '</div>';
                                        }
                                    })
                                    .catch(err => {
                                        loading.style.display = 'none';
                                        grid.innerHTML = '<div style="grid-column:1/-1; color:#e61e4d; font-size:0.9em;">Error loading slots.</div>';
                                    });
                                });
                            }

                            form.addEventListener('change', function() {
                                calculateTotal();
                                checkAvailability();
                            });
                            form.addEventListener('input', function() {
                                calculateTotal();
                                checkAvailability();
                            });
                            calculateTotal();
                            checkAvailability();

                            // End date must be after start date (date_range only)
                            var startInput = form.querySelector('#start_date_input');
                            var endInput   = form.querySelector('#end_date_input');
                            if (startInput && endInput) {
                                startInput.addEventListener('change', function() {
                                    endInput.min = startInput.value;
                                    if (endInput.value && new Date(endInput.value) < new Date(startInput.value)) {
                                        endInput.value = startInput.value;
                                    }
                                });
                            }
                        });
                        </script>
                    <?php
    endif; ?>
                </div>
            </div>
            
        </div>

    <?php
endwhile; ?>
</div>

<?php get_footer(); ?>
