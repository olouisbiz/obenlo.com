            <div class="listing-sidebar" style="position: sticky; top: 20px; height: fit-content; align-self: flex-start;">
                <div class="booking-widget">
                    <?php if (!isset($is_demo_preview)) $is_demo_preview = false; ?>
                    <?php if ($parent_id == 0): ?>
                        <!-- Parent Listing (Business Profile): Show Children, Hide Booking Form -->
                        <h3 style="margin-top:0;">Available Options</h3>
                        <?php if ($has_children): ?>
                            <p style="color:#666; font-size:0.9em; margin-bottom: 20px;">Please choose an option below to see pricing and check availability.</p>
                            <div class="child-options-list" style="display:flex; flex-direction:column; gap:12px;">
                                <?php foreach ($children as $child):
                                    $child_price = get_post_meta($child->ID, '_obenlo_price', true);
                                    $child_img = get_the_post_thumbnail_url($child->ID, 'thumbnail');
                                    ?>
                                    <a href="<?php echo get_permalink($child->ID); ?>" style="display:flex; gap:12px; padding:12px; border:1px solid #eee; border-radius:12px; text-decoration:none; color:#333; transition:0.2s; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.03);">
                                        <?php if ($child_img): ?>
                                            <div style="width:60px; height:60px; border-radius:8px; background:url('<?php echo esc_url($child_img); ?>') center/cover;"></div>
                                        <?php endif; ?>
                                        <div style="flex:1;">
                                            <div style="font-weight:bold; margin-bottom:2px;"><?php echo esc_html($child->post_title); ?></div>
                                            <div style="font-size:0.9em; color:#e61e4d; font-weight:700;">From $<?php echo esc_html($child_price); ?></div>
                                        </div>
                                        <div style="align-self:center; color:#ccc;">&rsaquo;</div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color:#666; font-style:italic;">No booking options available at this time.</p>
                            <?php if (current_user_can('administrator') || get_current_user_id() == get_post_field('post_author', $listing_id)): ?>
                                <a href="<?php echo home_url('/host-dashboard?action=add&parent_id=' . $listing_id); ?>" class="btn-primary" style="display:block; text-align:center; margin-top:15px; background:#333; color:white; padding:10px; border-radius:8px; text-decoration:none;">+ Add Unit/Session</a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Contact Host Button (Harmonized) -->
                        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
                            <button onclick="<?php if(is_user_logged_in()): ?>if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');} <?php else: ?>window.obenloOpenGuestContact(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');<?php endif; ?>"
                                    style="width: 100%; background: #e61e4d; color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(230,30,77,0.3);"
                                    onmouseover="this.style.background='#000'; this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.background='#e61e4d'; this.style.transform='translateY(0)';"
                            >
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                Contact Host
                            </button>
                            
                            <!-- Claim Demo Listing Button -->
                            <?php 
                            $is_demo_listing = get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes';
                            if ($is_demo_listing && get_post_meta($listing_id, '_obenlo_claim_pending', true) !== 'yes') {
                                $claim_url = home_url('/login?claim_id=' . $listing_id . '#signup');
                                echo '<a href="' . esc_url($claim_url) . '" style="display:block; text-align:center; width: 100%; margin-top: 15px; background: transparent; color: #e61e4d; border: 2px solid #e61e4d; padding: 12px; border-radius: 12px; font-weight: 700; text-decoration: none; transition: all 0.2s ease;">';
                                echo 'Is this your listing? Claim it';
                                echo '</a>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <!-- Child Listing: Show Booking Form -->
                        <?php
                        $host_id = get_post_field('post_author', $listing_id);
                        $engine = Obenlo_Engine_Manager::instance()->get_engine_for_listing($listing_id);
                        $booking_mode = $engine ? $engine->get_id() : 'default';
                        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';

                        // Resolve the subcategory slug for engine-specific JS
                        $category_slug = '';
                        $_type_terms = get_the_terms($listing_id, 'listing_type');
                        if ($_type_terms && !is_wp_error($_type_terms)) {
                            foreach ($_type_terms as $_t) {
                                if ($_t->parent != 0) { $category_slug = $_t->slug; break; }
                            }
                            if (!$category_slug && !empty($_type_terms)) {
                                $category_slug = $_type_terms[0]->slug;
                            }
                        }

                        $price_unit = '/ session';
                        if ($pricing_model === 'per_night') $price_unit = '/ night';
                        elseif ($pricing_model === 'per_day') $price_unit = '/ day';
                        elseif ($pricing_model === 'per_hour') $price_unit = '/ hour';
                        elseif ($pricing_model === 'per_person') $price_unit = '/ person';
                        
                        if ($engine && $engine->get_id() === 'logistics') {
                            $price_unit = 'Base';
                        }

                        $is_inquiry = ($pricing_model === 'inquiry_only' || ($engine && $engine->get_id() === 'inquiry'));
                        ?>

                        <!-- Price Header -->
                        <div class="price-header">
                            <?php if ($pricing_model === 'custom_donation'): ?>
                                <span class="price-value">Pay What You Want</span>
                                <div style="color: #666; font-size: 0.9rem; margin-top:5px;">Every contribution counts. Thank you!</div>
                            <?php elseif ($pricing_model === 'inquiry_only'): ?>
                                <span class="price-value">Quote Based</span>
                                <div style="color: #666; font-size: 0.95rem; margin-top:5px; font-weight:600;">Custom pricing upon inquiry.</div>
                            <?php else: ?>
                                <span class="price-value">$<?php echo esc_html($price); ?></span>
                                <span style="color: #666;"><?php echo esc_html($price_unit); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Modular Booking Form -->
                        <form class="booking-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin-top:20px;">
                            <input type="hidden" name="action" value="obenlo_submit_booking">
                            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                            <input type="hidden" name="booking_mode" value="<?php echo esc_attr($engine ? $engine->get_id() : 'default'); ?>">
                            <?php wp_nonce_field('obenlo_submit_booking_action', 'obenlo_booking_nonce'); ?>

                            <!-- ── Modular Booking Fields ── -->
                            <div class="modular-booking-fields-container">
                                <?php 
                                if ($engine) {
                                    echo $engine->render_booking_widget($listing_id, $category_slug);
                                }
                                ?>
                            </div>

                            <?php if ($is_inquiry && ($engine && $engine->get_id() !== 'inquiry')): ?>
                                <!-- Add Inquiry Message for other engines in Inquiry Mode -->
                                <div style="margin-top:20px; padding:15px; background:#fcfdff; border:1.5px dashed #dbeafe; border-radius:18px;">
                                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#1e40af; margin-bottom:10px;">MESSAGE TO HOST (Optional)</label>
                                    <textarea name="inquiry_message" rows="3" placeholder="Tell the host about your specific needs..." style="width:100%; padding:10px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:0.85rem; resize:none; outline:none;"></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if (!$is_inquiry): ?>
                            <!-- ── Payment Method ── -->
                            <div class="form-row" style="margin-top: 10px;">
                                <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">Payment Method</label>
                                <select name="payment_method" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                                    <?php 
                                    $listing_country = get_post_meta($listing_id, '_obenlo_listing_country', true) ?: 'usa';
                                    $is_haiti = ($listing_country === 'haiti');
                                    ?>
                                    <?php if(get_option('obenlo_stripe_enabled', 'yes') === 'yes'): ?>
                                        <option value="stripe">Credit Card (Stripe)</option>
                                    <?php endif; ?>
                                    <?php if(get_option('obenlo_paypal_enabled', 'yes') === 'yes'): ?>
                                        <option value="paypal">PayPal</option>
                                    <?php endif; ?>
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
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                                <div style="margin-top:20px; padding-top:20px; border-top:1px solid #eee; display:flex; justify-content:space-between; font-size:1.2em; font-weight:bold;">
                                    <span>Total:</span>
                                    <span>$<span id="live-total"><?php echo esc_html($price); ?></span></span>
                                </div>
                                <div id="logistics-info" style="display:none; margin-top:5px; font-size:0.8rem; color:#666; text-align:right;">
                                    <span id="logistics-distance-val"></span> | <span id="logistics-time-val"></span>
                                </div>
                            <?php endif; ?>

                            <div style="margin: 15px 0; text-align: center; color: #666; font-size: 0.85em; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <?php echo $is_inquiry ? __('CONSULTATION MODE: Host will reply with quote.', 'obenlo') : __('SECURE CHECKOUT: You will be redirected.', 'obenlo'); ?>
                            </div>

                            <button type="submit" class="reserve-btn" id="obenlo-reserve-btn" style="background:#e61e4d; color:white; width:100%; padding:15px; border-radius:12px; font-weight:bold; font-size:1.1rem; border:none; cursor:pointer; transition:all 0.2s ease-in-out; box-shadow:0 4px 15px rgba(230,30,77,0.3);" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(230,30,77,0.4)';" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 15px rgba(230,30,77,0.3)';">
                                <?php 
                                if (in_array($booking_mode, ['event_datetime'])) {
                                    echo 'Buy Tickets';
                                } elseif (in_array($booking_mode, ['inquiry'])) {
                                    echo 'Request a Quote';
                                } else {
                                    echo 'Book Instantly';
                                }
                                ?>
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Contact Host Button (Harmonized) -->
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                            <button onclick="<?php if(is_user_logged_in()): ?>if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');} <?php else: ?>window.obenloOpenGuestContact(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');<?php endif; ?>"
                                    style="width: 100%; background: #e61e4d; color: #fff; border: none; padding: 12px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(230,30,77,0.2);"
                                    onmouseover="this.style.background='#000'; this.style.transform='translateY(-1px)';"
                                    onmouseout="this.style.background='#e61e4d'; this.style.transform='translateY(0)';"
                            >
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                Contact Host
                            </button>
                        </div>

                        <?php 
                        if ( class_exists( 'Obenlo_Social_Admin_UI' ) ) {
                            Obenlo_Social_Admin_UI::render_frontend_push_button( get_the_ID() ); 
                        }
                        ?>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var form = document.querySelector('.booking-form');
                            if (!form) return;

                            var liveTotalEl = document.getElementById('live-total');
                            var basePrice = parseFloat(<?php echo json_encode($price); ?>) || 0;
                            var bookingMode = "<?php echo esc_js($engine ? $engine->get_id() : 'default'); ?>";

                            function calculateTotal() {
                                var total = basePrice;
                                
                                // Modular Engine Logic
                                <?php if ($engine) echo $engine->get_frontend_js_logic($listing_id, $category_slug); ?>

                                // Common Addons Logic
                                form.querySelectorAll('.addon-checkbox:checked').forEach(function(cb) {
                                    total += parseFloat(cb.getAttribute('data-price')) || 0;
                                });

                                if(liveTotalEl) liveTotalEl.innerText = total.toFixed(2);
                            }

                            form.addEventListener('change', calculateTotal);
                            form.addEventListener('input', calculateTotal);
                            calculateTotal();

                            form.addEventListener('submit', function() {
                                var btn = document.getElementById('obenlo-reserve-btn');
                                var method = form.querySelector('select[name="payment_method"]').value;
                                btn.disabled = true;
                                btn.style.opacity = '0.7';
                                btn.innerHTML = 'Redirecting to ' + method.toUpperCase() + '...';
                            });
                        });
                        </script>
                        
                        <?php 
                        $google_maps_key = get_option('obenlo_google_maps_api_key', '');
                        if ($google_maps_key): ?>
                            <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($google_maps_key); ?>&libraries=places"></script>
                        <?php else: ?>
                            <?php wp_enqueue_script('jquery-ui-autocomplete'); ?>
                            <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
                        <?php endif; ?>
                    <?php ?>
                </div>
            </div>
