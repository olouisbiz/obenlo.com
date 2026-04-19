            <div class="listing-main-content">
                <?php if (empty($image_urls) && has_post_thumbnail()): ?>
                    <div class="listing-featured-image" style="margin-bottom: 20px; border-radius: 12px; overflow: hidden;">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; display: block;')); ?>
                    </div>
                <?php 
                // Virtual Event Logic: Show join link if confirmed
                $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                if($virtual_link && is_user_logged_in()):
                    // Check if current user has a confirmed booking for this listing
                    $has_confirmed = false;
                    $user_bookings = get_posts(array(
                        'post_type' => 'booking',
                        'author' => get_current_user_id(),
                        'meta_query' => array(
                            array('key' => '_obenlo_listing_id', 'value' => $listing_id),
                            array('key' => '_obenlo_booking_status', 'value' => ['confirmed', 'approved', 'completed'], 'compare' => 'IN')
                        )
                    ));
                    if(!empty($user_bookings)) $has_confirmed = true;

                    if($has_confirmed):
                ?>
                    <div class="virtual-event-notice" style="background:#eef2ff; border:1px solid #c7d2fe; padding:25px; border-radius:15px; margin-bottom:30px; display:flex; align-items:center; justify-content:space-between; gap:20px;">
                        <div>
                            <h4 style="margin:0 0 5px 0; color:#3730a3;">You're booked for this virtual event!</h4>
                            <p style="margin:0; font-size:0.9rem; color:#4338ca;">Use the link below to join the session at the scheduled time.</p>
                        </div>
                        <a href="<?php echo esc_url($virtual_link); ?>" target="_blank" class="btn-primary" style="background:#4f46e5; padding:12px 25px; border-radius:10px; font-weight:800; text-decoration:none; font-size:0.95rem; display:inline-block; white-space:nowrap;">Join Session 🌐</a>
                    </div>
                <?php endif; endif; ?>
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
                        <h3><?php echo (in_array($category, ['experience', 'event', 'show'])) ? 'What\'s Included' : 'What this place offers'; ?></h3>
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
