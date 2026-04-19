            <div class="listing-sidebar" style="position: sticky; top: 20px; height: fit-content; align-self: flex-start;">
                <div class="booking-widget">
                    
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
        $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';
        $duration_val = get_post_meta($listing_id, '_obenlo_duration_val', true);
        $duration_unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
        $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';
        $session_runs_json = get_post_meta($listing_id, '_obenlo_session_runs', true);
        $schedule_runs = !empty($session_runs_json) ? json_decode($session_runs_json, true) : array();
        $requires_logistics = get_post_meta($listing_id, '_obenlo_requires_logistics', true) ?: 'no';

        $cfg = array('mode' => 'date_range', 'price_unit' => '/ unit', 'date_label' => 'Dates', 'has_guests' => true);

        if (!empty($schedule_runs) && is_array($schedule_runs) && count($schedule_runs) > 0) {
            $cfg = array('mode' => 'fixed_block', 'price_unit' => '/ person', 'date_label' => 'Select Date', 'has_guests' => true);
        }
        elseif ($pricing_model === 'per_night') {
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
        elseif ($pricing_model === 'per_event') {
            $cfg = array('mode' => 'event_datetime', 'price_unit' => 'event fee', 'date_label' => 'Select Event Date', 'has_guests' => true);
        }
        elseif ($pricing_model === 'per_donation') {
            $cfg = array('mode' => 'date_only', 'price_unit' => 'donation', 'date_label' => 'Donation Date', 'has_guests' => false);
        }
        elseif ($pricing_model === 'custom_donation') {
            $cfg = array('mode' => 'date_only', 'price_unit' => 'donation', 'date_label' => 'Donation Date', 'has_guests' => false);
        }
        elseif ($pricing_model === 'flat_fee') {
            $cfg = array('mode' => 'date_only', 'price_unit' => 'flat fee', 'date_label' => 'Select Date', 'has_guests' => false);
        }
        elseif ($pricing_model === 'inquiry_only') {
            $cfg = array('mode' => 'inquiry_only', 'price_unit' => 'contact for pricing', 'date_label' => '', 'has_guests' => false);
        }

        $booking_mode = $cfg['mode'];
        $price_unit = $cfg['price_unit'];
        $date_label = $cfg['date_label'];
        $form_has_guests = $cfg['has_guests'] && $capacity;

        $host_id = get_post_field('post_author', $listing_id);
        $business_hours = $is_demo ? get_post_meta($listing_id, '_obenlo_demo_business_hours', true) : get_user_meta($host_id, '_obenlo_business_hours', true);
        $business_hours = $business_hours ?: array();
        $vacation_blocks = get_user_meta($host_id, '_obenlo_vacation_blocks', true) ?: array();
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

                        <?php 
                        $event_is_fixed = get_post_meta($listing_id, '_obenlo_event_is_fixed', true);
                        $event_date = get_post_meta($listing_id, '_obenlo_event_date', true);
                        $event_start = get_post_meta($listing_id, '_obenlo_event_start_time', true);
                        $event_end = get_post_meta($listing_id, '_obenlo_event_end_time', true);
                        $event_location_type = get_post_meta($listing_id, '_obenlo_event_location_type', true) ?: 'virtual';
                        ?>

                        <?php if ($booking_mode === 'inquiry_only'): ?>
                            <div style="text-align:center; padding:25px 20px; background:#f9fafb; border-radius:15px; margin-top:20px; border:1px solid #e5e7eb;">
                                <h4 style="margin-top:0; color:#111827; font-size:1.2rem;">Interested in this service?</h4>
                                <p style="color:#4b5563; font-size:0.95rem; line-height:1.5; margin-bottom:20px;">This listing requires a custom quote or direct consultation. Please contact the host to discuss your exact needs and availability.</p>
                                <button onclick="<?php if(is_user_logged_in()): ?>if(window.obenloStartChatWith){window.obenloStartChatWith(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');} <?php else: ?>window.obenloOpenGuestContact(<?php echo $host_id; ?>, '<?php echo esc_js($host_name); ?>', '<?php echo esc_url(get_avatar_url($host_id)); ?>');<?php endif; ?>" style="background:#0f172a; color:white; width:100%; padding:15px; border-radius:12px; font-weight:bold; font-size:1.1rem; border:none; cursor:pointer; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);" onmouseover="this.style.background='#334155';" onmouseout="this.style.background='#0f172a';">Request a Quote</button>
                            </div>
                        <?php else: ?>
                        <form class="booking-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                            <input type="hidden" name="action" value="obenlo_submit_booking">
                            <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                            <input type="hidden" name="booking_mode" value="<?php echo esc_attr($booking_mode); ?>">
                            <?php wp_nonce_field('obenlo_submit_booking_action', 'obenlo_booking_nonce'); ?>

                            <!-- ── Date / Time Inputs ── -->
                            <?php if ($event_is_fixed === 'yes' && $event_date): ?>
                                <div class="form-row" style="background:#fef2f2; border:1px solid #fee2e2; padding:15px; border-radius:10px; margin-bottom:15px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: bold; margin-bottom: 5px; color:#991b1b;">Scheduled Time</label>
                                    <div style="font-size:1.1rem; font-weight:800; color:#333;">
                                        <?php 
                                        $formatted_date = date_i18n('l j F', strtotime($event_date));
                                        $start_display = !empty($event_start) ? date('g:i A', strtotime($event_start)) : '';
                                        $end_display = !empty($event_end) ? date('g:i A', strtotime($event_end)) : '';
                                        echo esc_html($formatted_date);
                                        if(!empty($start_display)) echo " &bull; " . esc_html($start_display);
                                        if(!empty($end_display)) echo " - " . esc_html($end_display);
                                        ?>
                                    </div>
                                    <div style="margin-top:8px; font-size:0.9rem; color:#666; display:flex; align-items:center; gap:5px;">
                                        <?php if($event_location_type === 'virtual'): ?>
                                            <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:700;">🌐 Virtual Event</span>
                                        <?php else: 
                                            $location = get_post_meta($listing_id, '_obenlo_location', true);
                                            if($location): ?>
                                                <span style="color:#e61e4d;">📍</span> <?php echo esc_html($location); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="start_date" value="<?php echo esc_attr($event_date . 'T' . ($event_start ?: '00:00')); ?>">
                                </div>
                            <?php elseif ($booking_mode === 'fixed_block'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="date" id="fixed_block_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-row" style="margin-top:15px;">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 10px;">Select Departure / Start Time</label>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                        <?php foreach ($schedule_runs as $idx => $run): 
                                            $run_day = is_array($run) ? $run['day'] : 'Daily';
                                            ?>
                                            <label style="display:none; padding:12px; border:1px solid #ddd; background:#fff; border-radius:10px; text-align:center; cursor:pointer; transition:0.2s;" class="run-selector" data-day="<?php echo esc_attr($run_day); ?>">
                                                <input type="radio" name="fixed_time" value="<?php echo esc_attr(is_array($run) ? $run['start'] : $run); ?>" required style="display:none;">
                                                <div style="font-weight:bold; font-size:1.05rem;">
                                                    <?php 
                                                    if (is_array($run)) {
                                                        echo date('g:i A', strtotime($run['start'])) . ' - ' . date('g:i A', strtotime($run['end']));
                                                    } else {
                                                        echo date('g:i A', strtotime($run)); 
                                                    }
                                                    ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="start_date" id="final_fixed_start_date" required>
                                <?php if ($duration_val): ?>
                                    <input type="hidden" name="booking_duration" value="<?php echo esc_attr($duration_val); ?>">
                                    <input type="hidden" name="booking_duration_unit" value="<?php echo esc_attr($duration_unit ?: 'hours'); ?>">
                                <?php endif; ?>
                            <?php elseif ($booking_mode === 'date_range'): ?>
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
                                        <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">
                                            Duration (Hours)
                                            <?php if ($duration_val): ?>
                                                <span style="color:#e61e4d; font-size:0.8rem; font-weight:700; margin-left:5px;">(Host Predefined: <?php echo esc_html($duration_val); ?> <?php echo esc_html($duration_unit); ?>)</span>
                                            <?php endif; ?>
                                        </label>
                                        <input type="number" name="booking_duration" min="0.5" step="0.5" value="<?php echo esc_attr($duration_val ?: 1); ?>" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;">
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
                                <?php if ($cfg['mode'] === 'timeslot'): ?>
                                    <div class="form-row" style="margin-top:15px;">
                                        <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;">
                                            <?php echo $pricing_model === 'per_hour' ? __('Duration (Hours)', 'obenlo') : __('Minutes / Sessions', 'obenlo'); ?>
                                            <?php if ($duration_val): ?>
                                                <span style="color:#e61e4d; font-size:0.8rem; font-weight:700; margin-left:5px;">(Host Predefined: <?php echo esc_html($duration_val); ?> <?php echo esc_html($duration_unit); ?>)</span>
                                            <?php endif; ?>
                                        </label>
                                        <input type="number" name="booking_duration" min="0.5" step="0.5" value="<?php echo esc_attr($duration_val ?: 1); ?>" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;">
                                        <input type="hidden" name="booking_duration_unit" value="<?php echo $pricing_model === 'per_hour' ? 'hours' : ($duration_unit ?: 'hours'); ?>">
                                    </div>
                                <?php elseif ($duration_val): ?>
                                    <input type="hidden" name="booking_duration" value="<?php echo esc_attr($duration_val); ?>">
                                    <input type="hidden" name="booking_duration_unit" value="<?php echo esc_attr($duration_unit ?: 'hours'); ?>">
                                <?php endif; ?>
                            <?php
        elseif ($booking_mode === 'datetime' || $booking_mode === 'event_datetime'): ?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="datetime-local" name="start_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                                <?php if ($duration_val): ?>
                                    <div class="form-row" style="margin-top:10px;">
                                        <p style="font-size:0.9rem; color:#666; font-weight:700;">🕒 <?php echo __('Duration:', 'obenlo'); ?> <?php echo esc_html($duration_val); ?> <?php echo esc_html($duration_unit); ?></p>
                                        <input type="hidden" name="booking_duration" value="<?php echo esc_attr($duration_val); ?>">
                                        <input type="hidden" name="booking_duration_unit" value="<?php echo esc_attr($duration_unit ?: 'hours'); ?>">
                                    </div>
                                <?php endif; ?>
                            <?php
        else: /* date_only */?>
                                <div class="form-row">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 5px;"><?php echo esc_html($date_label); ?></label>
                                    <input type="date" name="start_date" required style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 6px;" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            <?php
        endif; ?>

                            <!-- Custom Donation Input -->
                            <?php if ($pricing_model === 'custom_donation'): ?>
                                <div class="form-row" style="margin-bottom: 20px; background: #fffcf0; padding: 15px; border: 1px solid #fde68a; border-radius: 10px;">
                                    <label style="display: block; font-size: 0.9em; font-weight: bold; margin-bottom: 8px;">Your Donation Amount ($)</label>
                                    <div style="position:relative;">
                                        <span style="position:absolute; left:12px; top:10px; color:#888; font-weight:bold;">$</span>
                                        <input type="number" name="custom_donation_amount" min="1" step="0.01" required placeholder="<?php echo esc_attr($price); ?> (Suggested)" style="width: 100%; border: 1px solid #ccc; padding: 10px 10px 10px 30px; border-radius: 8px; font-size:1.1rem; font-weight:bold;">
                                    </div>
                                    <p style="font-size:0.8rem; color:#92400e; margin-top:8px;">Help support this cause with a contribution of your choice.</p>
                                </div>
                            <?php endif; ?>

                            <!-- Guests / Tickets -->
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

                            <!-- Logistics Module -->
                            <?php if ($requires_logistics === 'yes'): ?>
                                <div class="form-row" style="margin-bottom: 20px; background: #e0f2fe; padding: 15px; border-radius: 10px; border: 1px solid #7dd3fc;">
                                    <div style="font-weight: 800; color: #0369a1; margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                                        <span>📦</span> Logistics Details
                                    </div>
                                    <label style="display: block; font-size: 0.85em; font-weight: bold; margin-bottom: 5px; color: #0c4a6e;">Pickup Address</label>
                                    <input type="text" name="logistics_pickup" required placeholder="Where should we meet you?" style="width: 100%; padding: 10px; border: 1px solid #bae6fd; border-radius: 6px; margin-bottom: 15px;">
                                    
                                    <label style="display: block; font-size: 0.85em; font-weight: bold; margin-bottom: 5px; color: #0c4a6e;">Drop-off / Destination Address</label>
                                    <input type="text" name="logistics_dropoff" required placeholder="Where constitute the final destination?" style="width: 100%; padding: 10px; border: 1px solid #bae6fd; border-radius: 6px;">
                                </div>
                            <?php endif; ?>

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

                            <div style="margin: 15px 0; text-align: center; color: #666; font-size: 0.85em; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                SECURE CHECKOUT: You will be redirected.
                            </div>

                            <button type="submit" class="reserve-btn" id="obenlo-reserve-btn" style="background:#e61e4d; color:white; width:100%; padding:15px; border-radius:12px; font-weight:bold; font-size:1.1rem; border:none; cursor:pointer; transition:all 0.2s ease-in-out; box-shadow:0 4px 15px rgba(230,30,77,0.3);" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(230,30,77,0.4)';" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 15px rgba(230,30,77,0.3)';">
                                <?php echo in_array($booking_mode, ['event_datetime']) ? 'Buy Tickets' : 'Book Instantly'; ?>
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

                            form.querySelectorAll('input[name="fixed_time"]').forEach(function(r){
                                r.addEventListener('change', function(){
                                    form.querySelectorAll('.run-selector').forEach(function(rs) {
                                        rs.style.borderColor = '#ddd';
                                        rs.style.background = '#fff';
                                        rs.style.color = '#333';
                                    });
                                    if(this.checked) {
                                        this.parentNode.style.borderColor = '#e61e4d';
                                        this.parentNode.style.background = '#fff1f2';
                                        this.parentNode.style.color = '#e61e4d';
                                    }
                                });
                            });

                            var basePrice = parseFloat(<?php echo json_encode($price); ?>) || 0;
                            var bookingMode = "<?php echo esc_js($booking_mode); ?>";
                            
                            form.addEventListener('submit', function(e) {
                                if (bookingMode === 'fixed_block') {
                                    var fD = document.getElementById('fixed_block_date');
                                    var fT = form.querySelector('input[name="fixed_time"]:checked');
                                    var fS = document.getElementById('final_fixed_start_date');
                                    if (fD && fT && fS) {
                                        fS.value = fD.value + 'T' + fT.value;
                                    }
                                }

                                var btn = document.getElementById('obenlo-reserve-btn');
                                var method = form.querySelector('select[name="payment_method"]').value;
                                var methodLabels = { 'stripe': 'Stripe', 'paypal': 'PayPal', 'moncash': 'MonCash', 'natcash': 'Natcash' };
                                var methodLabel = methodLabels[method] || 'Gateway';
                                
                                btn.disabled = true;
                                btn.style.opacity = '0.7';
                                btn.innerHTML = '<span class="loading-spinner"></span> Redirecting to ' + methodLabel + '...';
                                btn.style.cursor = 'wait';
                            });

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
                                } else if (bookingMode === 'date_only' || bookingMode === 'event_datetime' || bookingMode === 'datetime' || bookingMode === 'timeslot' || bookingMode === 'fixed_block') {
                                    var g = form.querySelector('input[name="guests"]');
                                    var hasGuests = <?php echo $form_has_guests ? 'true' : 'false'; ?>;
                                    var qty = (g && g.value && hasGuests) ? parseInt(g.value) || 1 : 1;
                                    
                                    // Handle duration multiplier for per_hour model
                                    var pricingModel = "<?php echo esc_js($pricing_model); ?>";
                                    var durationEl = form.querySelector('input[name="booking_duration"]');
                                    var duration = (durationEl && pricingModel === 'per_hour') ? parseFloat(durationEl.value) || 1 : 1;
                                    
                                    total = basePrice * qty * duration;
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

                                // 2. Check Business Hours for Exact Time (Validation disabled per user request)
                                /*
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
                                */
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

                            var fixedDateInput = document.getElementById('fixed_block_date');
                            if (fixedDateInput) {
                                fixedDateInput.addEventListener('change', function() {
                                    var dateVal = this.value;
                                    if (!dateVal) return;
                                    var dayName = new Date(dateVal + 'T00:00:00').toLocaleDateString('en-US', {weekday: 'long'});
                                    var selectors = document.querySelectorAll('.run-selector');
                                    var found = false;
                                    selectors.forEach(function(s) {
                                        var day = s.getAttribute('data-day');
                                        if (day === 'Daily' || day === dayName) {
                                            s.style.display = 'block';
                                            found = true;
                                        } else {
                                            s.style.display = 'none';
                                            s.querySelector('input').checked = false;
                                        }
                                    });
                                    var container = document.querySelector('.run-selector').closest('.form-row');
                                    var noSlotsMsg = document.getElementById('no-runs-msg');
                                    if (!found) {
                                        if (!noSlotsMsg) {
                                            noSlotsMsg = document.createElement('div');
                                            noSlotsMsg.id = 'no-runs-msg';
                                            noSlotsMsg.style.color = '#e61e4d';
                                            noSlotsMsg.style.fontSize = '0.9rem';
                                            noSlotsMsg.style.fontWeight = 'bold';
                                            noSlotsMsg.innerText = 'No sessions scheduled for this day.';
                                            container.appendChild(noSlotsMsg);
                                        }
                                    } else if (noSlotsMsg) {
                                        noSlotsMsg.remove();
                                    }
                                });
                            }
                        });
                        </script>
                    <?php
    endif; ?>
                </div>
            </div>
