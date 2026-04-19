        <div class="listing-header">
            <?php if (!isset($is_demo_preview)) $is_demo_preview = false; ?>
            <?php if ($parent_id): ?>
                <a href="<?php echo get_permalink($parent_id); ?>" style="display:inline-block; margin-bottom:15px; color:#666; text-decoration:none; font-weight: 500;">
                    &larr; Back to <?php echo esc_html(get_the_title($parent_id)); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url('/listings/')); ?>" style="display:inline-block; margin-bottom:15px; color:#666; text-decoration:none; font-weight: 500;">
                    &larr; Back to Listings
                </a>
            <?php endif; ?>
            
            <h1 class="listing-title" style="margin-bottom: 5px;"><?php the_title(); ?></h1>
            <div style="color: #666; font-size: 0.9em; display: flex; align-items: center; gap: 5px;">
                <?php if ($avg_rating > 0): ?>
                    <span style="color: #333; font-weight: bold;">★ <?php echo $avg_rating; ?></span>
                    <span>&bull;</span>
                    <a href="#reviews" onclick="openTab(event, 'tab-reviews')" style="color: #666; text-decoration: underline;"><?php echo $review_count; ?> reviews</a>
                    <span>&bull;</span>
                <?php
    endif; ?>
                <?php 
                $badge_icon = '🏢';
                $display_category = $type;
                
                // Robust mapping to main categories
                $cat_check = strtolower($type);
                if (strpos($cat_check, 'stay') !== false || strpos($cat_check, 'hotel') !== false || strpos($cat_check, 'guest-house') !== false) {
                    $badge_icon = '🏠';
                    $display_category = 'Stay';
                } elseif (strpos($cat_check, 'experience') !== false || strpos($cat_check, 'tour') !== false) {
                    $badge_icon = '✨';
                    $display_category = 'Experience';
                } elseif (strpos($cat_check, 'service') !== false || in_array($cat_check, ['chauffeur', 'cook', 'barbershop', 'hairdresser', 'concierge', 'personal-assistant', 'babysitter', 'dogsitter', 'barber'])) {
                    $badge_icon = '🛠️';
                    $display_category = 'Service';
                } elseif (strpos($cat_check, 'event') !== false || strpos($cat_check, 'show') !== false || strpos($cat_check, 'dj') !== false) {
                    $badge_icon = '🎟️';
                    $display_category = 'Event';
                }

                $event_location_type = get_post_meta($listing_id, '_obenlo_event_location_type', true);
                $location_val = get_post_meta($listing_id, '_obenlo_location', true);
                ?>
                <span class="category-badge" style="background:#fef2f2; padding:4px 12px; border-radius:30px; font-weight:700; font-size:0.85rem; color:#e61e4d; margin-right:8px; border:1px solid #fee2e2;">
                    <?php echo $badge_icon; ?> <?php echo esc_html($display_category); ?>
                </span>
                
                <?php if(in_array($display_category, ['Event', 'Experience'])): ?>
                    <?php if($event_location_type === 'in_person' && $location_val): ?>
                        <span style="color: #666; font-size: 0.9rem; margin-right:8px;">📍 <?php echo esc_html($location_val); ?></span>
                    <?php elseif($event_location_type === 'virtual'): ?>
                        <span style="color: #4f46e5; font-size: 0.9rem; font-weight:bold; margin-right:8px;">🌐 Virtual Event</span>
                    <?php endif; ?>
                <?php elseif($location_val): ?>
                    <span style="color: #666; font-size: 0.9rem; margin-right:8px;">📍 <?php echo esc_html($location_val); ?></span>
                <?php endif; ?>

                <?php 
                $host_id = get_the_author_meta('ID');
                $store_name = get_user_meta($host_id, 'obenlo_store_name', true);
                $host_name = !empty($store_name) ? $store_name : get_the_author();
                $host_url = get_author_posts_url($host_id);

                // Demo Masking
                global $post;
                $is_demo = (get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes');
                $source_id = $listing_id;
                
                if (!$is_demo && !empty($post->post_parent)) {
                    $parent_is_demo = (get_post_meta($post->post_parent, '_obenlo_is_demo', true) === 'yes');
                    if ($parent_is_demo) {
                        $is_demo = true;
                        $source_id = $post->post_parent;
                    }
                }
                
                if ($is_demo) {
                    $demo_name = get_post_meta($source_id, '_obenlo_demo_host_name', true);
                    if ($demo_name) {
                        $host_name = $demo_name;
                        $host_url = home_url('/demo/' . sanitize_title($demo_name) . '/');
                    } else {
                        $host_url = trailingslashit($host_url) . 'demo/';
                    }
                }
                ?>
                <span style="color:#888;">&bull;</span> Hosted by <a href="<?php echo esc_url($host_url); ?>" style="color:#e61e4d; font-weight:bold; text-decoration:none; margin-left:5px;"><?php echo esc_html($host_name); ?></a>
            </div>
        </div>
