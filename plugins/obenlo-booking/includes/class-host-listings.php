<?php
/**
 * Host Listings Module — Phase 2 (Fully Extracted)
 * Single Responsibility: Listings table, listing form (with U.B.E JS), save handler, delete handler.
 * The U.B.E (Universal Booking Engine) dynamic JS lives here — isolated from all other modules.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Listings
{
    public function init()
    {
        add_action('admin_post_nopriv_obenlo_dashboard_save_listing', array($this, 'handle_save_listing'));
        add_action('admin_post_obenlo_dashboard_save_listing',        array($this, 'handle_save_listing'));
        add_action('admin_post_obenlo_dashboard_delete_listing',      array($this, 'handle_delete_listing'));
        
        // AJAX Engine fields
        add_action('wp_ajax_obenlo_get_engine_fields', array($this, 'ajax_get_engine_fields'));
    }

    public function ajax_get_engine_fields() {
        $type_id    = isset($_POST['type_id']) ? intval($_POST['type_id']) : 0;
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        
        $engine = Obenlo_Engine_Manager::instance()->get_engine_for_listing_type($type_id);
        
        if ($engine) {
            $html = $engine->render_host_fields($listing_id);
            wp_send_json_success(array('html' => $html, 'engine' => $engine->get_id()));
        } else {
            wp_send_json_error('No engine found');
        }
    }

    // ────────────────────────────────────────────────────────────
    // LISTINGS TABLE
    // ────────────────────────────────────────────────────────────
    public function render_listings_list()
    {
        $user_id = get_current_user_id();
        $is_host_suspended      = get_user_meta($user_id, '_obenlo_is_suspended', true) === 'yes';
        $host_suspension_reason = get_user_meta($user_id, '_obenlo_suspension_reason', true);

        $user_listings = get_posts(array(
            'post_type'        => 'listing',
            'author'           => $user_id,
            'posts_per_page'   => -1,
            'post_parent'      => 0,
            'suppress_filters' => false,
        ));

        if (current_user_can('administrator')) {
            $demo_listings = get_posts(array(
                'post_type'        => 'listing',
                'meta_key'         => '_obenlo_is_demo',
                'meta_value'       => 'yes',
                'posts_per_page'   => -1,
                'post_parent'      => 0,
                'suppress_filters' => false,
            ));
            $listings = array(); $seen_ids = array();
            foreach (array_merge($user_listings, $demo_listings) as $l) {
                if (!in_array($l->ID, $seen_ids)) { $listings[] = $l; $seen_ids[] = $l->ID; }
            }
        } else {
            $listings = $user_listings;
        }
        ?>
        <div class="dashboard-header">
            <h2 class="dashboard-title"><?php echo __('My Listings', 'obenlo'); ?></h2>
            <a href="?action=add" class="btn-primary">+ <?php echo __('Add New Listing', 'obenlo'); ?></a>
        </div>

        <?php if ($is_host_suspended): ?>
            <div style="background:#fef2f2; border:1px solid #fee2e2; border-left:4px solid #ef4444; padding:20px; border-radius:12px; margin-bottom:30px;">
                <h3 style="margin-top:0; color:#991b1b; font-size:1.1rem;">⚠️ Your Host Account is Suspended</h3>
                <p style="color:#7f1d1d; margin-bottom:0;">Your account has been suspended due to a policy violation. Your listings are currently hidden from the public.
                    <?php if ($host_suspension_reason): ?><br><br><strong>Reason:</strong> <?php echo esc_html($host_suspension_reason); ?><?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (empty($listings)): ?>
            <div class="form-section" style="text-align:center; padding:60px;">
                <p style="color:#666; font-size:1.1rem;"><?php echo __("You haven't created any listings yet.", 'obenlo'); ?></p>
                <a href="?action=add" class="btn-primary" style="margin-top:20px;"><?php echo __('Create Your First Listing', 'obenlo'); ?></a>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?php echo __('Listing', 'obenlo'); ?></th>
                        <th><?php echo __('Owner', 'obenlo'); ?></th>
                        <th><?php echo __('Category', 'obenlo'); ?></th>
                        <th><?php echo __('Status', 'obenlo'); ?></th>
                        <th><?php echo __('Units/Sessions', 'obenlo'); ?></th>
                        <th><?php echo __('Actions', 'obenlo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing):
                        $type_terms   = wp_get_post_terms($listing->ID, 'listing_type', array('fields' => 'names'));
                        $type_display = !empty($type_terms) ? __($type_terms[0], 'obenlo') : __('Uncategorized', 'obenlo');
                        $children = get_posts(array('post_type' => 'listing', 'post_parent' => $listing->ID, 'posts_per_page' => -1, 'suppress_filters' => false));
                        $is_demo        = get_post_meta($listing->ID, '_obenlo_is_demo', true) === 'yes';
                        $demo_host_name = get_post_meta($listing->ID, '_obenlo_demo_host_name', true);
                        if ($is_demo && !empty($demo_host_name)) {
                            $owner_name = $demo_host_name . ' <span style="font-size:0.6rem; background:#eee; padding:2px 4px; border-radius:4px; color:#888;">Demo</span>';
                        } else {
                            $owner_name = get_the_author_meta('display_name', $listing->post_author);
                            if (empty($owner_name)) {
                                $owner_user = get_userdata($listing->post_author);
                                $owner_name = $owner_user ? $owner_user->user_login : 'Unknown';
                            }
                        }
                        $is_suspended = get_post_meta($listing->ID, '_obenlo_is_suspended', true) === 'yes';
                        $sus_reason   = get_post_meta($listing->ID, '_obenlo_suspension_reason', true);
                    ?>
                        <tr>
                            <td data-label="<?php echo esc_attr(__('Listing', 'obenlo')); ?>">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <?php if (has_post_thumbnail($listing->ID)): ?>
                                        <img src="<?php echo get_the_post_thumbnail_url($listing->ID, 'thumbnail'); ?>" style="width:40px; height:40px; border-radius:8px; object-fit:cover;">
                                    <?php endif; ?>
                                    <span style="font-weight:700; color:#222; text-align:left;"><?php echo get_the_title($listing->ID); ?></span>
                                </div>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Owner', 'obenlo')); ?>">
                                <div style="font-weight:600; color:#666; font-size:0.85rem;"><?php echo wp_kses_post($owner_name); ?></div>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Category', 'obenlo')); ?>"><span class="badge badge-info"><?php echo esc_html($type_display); ?></span></td>
                            <td data-label="<?php echo esc_attr(__('Status', 'obenlo')); ?>">
                                <span class="badge badge-success"><?php echo ucfirst($listing->post_status); ?></span>
                                <?php if ($is_suspended): ?>
                                    <div style="margin-top:8px;">
                                        <span class="badge" style="background:#e61e4d; color:#fff;">Suspended</span>
                                        <?php if ($sus_reason): ?><div style="font-size:0.7rem; color:#e61e4d; margin-top:4px; font-weight:600;">Reason: <?php echo esc_html($sus_reason); ?></div><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Units', 'obenlo')); ?>">
                                <span style="font-weight:600; color:#444;"><?php echo sprintf(__('%d units', 'obenlo'), count($children)); ?></span>
                                <a href="?action=add&parent_id=<?php echo $listing->ID; ?>" style="display:block; font-size:0.75rem; color:#e61e4d; text-decoration:none;">+ <?php echo __('Add unit', 'obenlo'); ?></a>
                            </td>
                            <td data-label="<?php echo esc_attr(__('Actions', 'obenlo')); ?>">
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <a href="?action=edit&listing_id=<?php echo $listing->ID; ?>" style="background:#f0f0f0; color:#222; padding:6px 12px; border-radius:8px; font-weight:700; text-decoration:none; font-size:0.8rem;"><?php echo __('Edit', 'obenlo'); ?></a>
                                    <a href="<?php echo get_permalink($listing->ID); ?>" target="_blank" style="color:#1d9bf0; font-weight:700; text-decoration:none; font-size:0.8rem;"><?php echo __('View', 'obenlo'); ?></a>
                                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete this listing?', 'obenlo')); ?>');">
                                        <input type="hidden" name="action" value="obenlo_dashboard_delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing->ID; ?>">
                                        <?php wp_nonce_field('obenlo_delete_listing_' . $listing->ID); ?>
                                        <button type="submit" style="background:none; border:none; color:#e61e4d; font-weight:700; font-size:0.8rem; cursor:pointer; padding:0;"><?php echo __('Delete', 'obenlo'); ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php foreach ($children as $child): ?>
                            <tr style="background:#fafafa;">
                                <td data-label="<?php echo esc_attr(__('Listing', 'obenlo')); ?>" style="padding-left:30px; font-size:0.85rem; color:#666;">└─ <?php echo get_the_title($child->ID); ?></td>
                                <td></td><td></td>
                                <td data-label="<?php echo esc_attr(__('Status', 'obenlo')); ?>"><span class="badge badge-success" style="opacity:0.6; font-size:0.7rem;"><?php echo ucfirst($child->post_status); ?></span></td>
                                <td></td>
                                <td data-label="<?php echo esc_attr(__('Actions', 'obenlo')); ?>">
                                    <div style="display:flex; gap:12px; align-items:center;">
                                        <a href="?action=edit&listing_id=<?php echo $child->ID; ?>" style="color:#666; font-size:0.8rem; font-weight:600; text-decoration:none;"><?php echo __('Edit Unit', 'obenlo'); ?></a>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete this unit?', 'obenlo')); ?>');">
                                            <input type="hidden" name="action" value="obenlo_dashboard_delete_listing">
                                            <input type="hidden" name="listing_id" value="<?php echo $child->ID; ?>">
                                            <?php wp_nonce_field('obenlo_delete_listing_' . $child->ID); ?>
                                            <button type="submit" style="background:none; border:none; color:#999; font-size:0.8rem; cursor:pointer; padding:0;"><?php echo __('Delete', 'obenlo'); ?></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif;
    }

    // ────────────────────────────────────────────────────────────
    // LISTING FORM (U.B.E. — Universal Booking Engine UI)
    // ────────────────────────────────────────────────────────────
    public function render_listing_form($listing_id = 0)
    {
        $title = ''; $content = ''; $price = ''; $capacity = ''; $available_units = 1;
        $addons = array(); $location = ''; $policy_type = 'global';
        $policy_cancel = ''; $policy_refund = ''; $policy_other = '';
        $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
        $selected_type = ''; $virtual_link = ''; $event_is_fixed = 'no';
        $event_date = ''; $event_start_time = ''; $event_end_time = '';
        $event_location_type = 'virtual'; $session_runs = array();

        if ($listing_id > 0) {
            $post = get_post($listing_id);
            if ($post && ($post->post_author == get_current_user_id() || current_user_can('administrator'))) {
                $title          = get_the_title($listing_id);
                $sandboxed      = get_post_meta($listing_id, '_obenlo_sandboxed_content', true);
                $content        = $sandboxed ? $sandboxed : $post->post_content;
                $parent_id      = $post->post_parent;
                $price          = get_post_meta($listing_id, '_obenlo_price', true);
                $capacity       = get_post_meta($listing_id, '_obenlo_capacity', true);
                $available_units = get_post_meta($listing_id, '_obenlo_available_units', true) ?: 1;
                $location       = get_post_meta($listing_id, '_obenlo_location', true);
                $virtual_link   = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                $event_is_fixed = get_post_meta($listing_id, '_obenlo_event_is_fixed', true) ?: 'no';
                $event_date     = get_post_meta($listing_id, '_obenlo_event_date', true);
                $event_start_time = get_post_meta($listing_id, '_obenlo_event_start_time', true);
                $event_end_time = get_post_meta($listing_id, '_obenlo_event_end_time', true);
                $event_location_type = get_post_meta($listing_id, '_obenlo_event_location_type', true) ?: 'virtual';
                $addons_json    = get_post_meta($listing_id, '_obenlo_addons_structured', true);
                if (!empty($addons_json)) { $decoded = json_decode($addons_json, true); if (is_array($decoded)) $addons = $decoded; }
                $runs_json      = get_post_meta($listing_id, '_obenlo_session_runs', true);
                if (!empty($runs_json)) { $decoded_runs = json_decode($runs_json, true); if (is_array($decoded_runs)) $session_runs = $decoded_runs; }
                $pricing_model  = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';
                $duration_val   = get_post_meta($listing_id, '_obenlo_duration_val', true);
                $duration_unit  = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
                $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';
                $listing_country = get_post_meta($listing_id, '_obenlo_listing_country', true) ?: 'usa';
                $type_terms     = wp_get_post_terms($listing_id, 'listing_type');
                if (!empty($type_terms) && !is_wp_error($type_terms)) { $selected_type = $type_terms[0]->term_id; }
                $policy_type    = get_post_meta($listing_id, '_obenlo_policy_type', true) ?: 'global';
                $policy_cancel  = get_post_meta($listing_id, '_obenlo_policy_cancel', true);
                $policy_refund  = get_post_meta($listing_id, '_obenlo_policy_refund', true);
                $policy_other   = get_post_meta($listing_id, '_obenlo_policy_other', true);
            } else {
                echo '<p>' . __('Invalid listing.', 'obenlo') . '</p>'; return;
            }
        } else {
            $pricing_model = 'per_night'; $duration_val = '';
            $duration_unit = 'hours'; $requires_slots = 'no'; $listing_country = 'usa';
        }

        $is_child   = ($parent_id > 0);
        $parent_post = null;
        if ($is_child) {
            $parent_post = get_post($parent_id);
            if (!$selected_type) {
                $parent_terms = wp_get_post_terms($parent_id, 'listing_type');
                if (!empty($parent_terms) && !is_wp_error($parent_terms)) { $selected_type = $parent_terms[0]->term_id; }
            }
        }

        $form_action  = esc_url(admin_url('admin-post.php'));
        $title_label  = $is_child ? __('Unit / Session Name', 'obenlo') : __('Business / Property Name', 'obenlo');
        $desc_label   = $is_child ? __('About this Unit / Session', 'obenlo') : __('About your Business / Property', 'obenlo');
        $media_label  = $is_child ? __('Unit Specific Photos', 'obenlo') : __('Primary Property Photos', 'obenlo');
        $media_hint   = $is_child ? __('Upload up to 3 photos', 'obenlo') : __('Upload up to 10 photos', 'obenlo');
        $media_limit  = $is_child ? 3 : 10;

        // Derive category flag for dynamic headings
        $category_flag = 'default';
        if ($selected_type) {
            $type_term = get_term($selected_type, 'listing_type');
            if ($type_term && !is_wp_error($type_term)) {
                $check_names = [strtolower($type_term->name)];
                $check_slugs = [$type_term->slug];
                if ($type_term->parent != 0) {
                    $parent_term = get_term($type_term->parent, 'listing_type');
                    if ($parent_term && !is_wp_error($parent_term)) { $check_names[] = strtolower($parent_term->name); $check_slugs[] = $parent_term->slug; }
                }
                foreach ($check_names as $n) {
                    if (strpos($n, 'stay') !== false)       { $category_flag = 'stay'; break; }
                    if (strpos($n, 'experience') !== false || strpos($n, 'tour') !== false) { $category_flag = 'experience'; break; }
                    if (strpos($n, 'event') !== false || strpos($n, 'show') !== false || strpos($n, 'class') !== false) { $category_flag = 'event'; break; }
                    if (strpos($n, 'service') !== false || strpos($n, 'beauty') !== false || strpos($n, 'barber') !== false) { $category_flag = 'service'; break; }
                }
                if ($category_flag === 'default') {
                    foreach ($check_slugs as $s) {
                        if (in_array($s, ['hotel', 'guest-house']))       { $category_flag = 'stay'; break; }
                        if (in_array($s, ['event', 'show', 'class', 'ticket'])) { $category_flag = 'event'; break; }
                        if (in_array($s, ['service', 'beauty', 'chauffeur', 'cook', 'barbershop', 'hairdresser'])) { $category_flag = 'service'; break; }
                    }
                }
            }
        }
        ?>
        <div class="dashboard-header">
            <h2 class="dashboard-title">
                <?php
                if ($listing_id) {
                    echo sprintf($is_child ? __('Edit Unit/Session: %s', 'obenlo') : __('Edit Listing: %s', 'obenlo'), get_the_title($listing_id));
                } else {
                    echo $is_child ? __('Add New Unit/Session', 'obenlo') : __('Add New Listing', 'obenlo');
                }
                ?>
            </h2>
            <a href="?action=list" style="color:#666; font-weight:700; text-decoration:none;">← <?php echo __('Back to Listings', 'obenlo'); ?></a>
        </div>

        <div style="max-width:800px;">
            <?php if ($is_child): ?>
                <p style="background:#eff6ff; color:#1e40af; padding:15px 20px; border-radius:12px; font-weight:600; font-size:0.9rem;">
                    <?php echo __('Adding a specific bookable unit to:', 'obenlo'); ?> <strong><?php echo esc_html($parent_post->post_title); ?></strong>
                </p>
            <?php else: ?>
                <p style="color:#666; font-size:0.95rem; margin-bottom:30px;">
                    <?php echo __("Create the main property, experience, or service. (You will add specific bookable units later).", 'obenlo'); ?>
                </p>
            <?php endif; ?>

            <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="obenlo_dashboard_save_listing">
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                <?php if ($is_child): ?>
                    <input type="hidden" name="parent_id" value="<?php echo esc_attr($parent_id > 0 ? $parent_id : filter_input(INPUT_GET, 'parent_id', FILTER_SANITIZE_NUMBER_INT)); ?>">
                <?php endif; ?>

                <?php Obenlo_Demo_Manager::render_demo_setup_ui($listing_id); ?>

                <?php wp_nonce_field('dashboard_save_listing', 'dashboard_listing_nonce'); ?>

                <!-- Basic Information -->
                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Basic Information', 'obenlo'); ?></h4>
                    <div style="margin-bottom:20px;">
                        <label id="listing_title_label" style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo $title_label; ?></label>
                        <input type="text" name="listing_title" value="<?php echo esc_attr($title); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; transition:border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label id="listing_type_label" style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo $is_child ? __('Sub-Category', 'obenlo') : __('Industry Category', 'obenlo'); ?></label>
                        <select name="listing_type" id="smart_listing_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                            <option value=""><?php echo $is_child ? __('-- Select Sub-Category --', 'obenlo') : __('-- Select Industry --', 'obenlo'); ?></option>
                            <?php
                            if ($is_child) {
                                $parent_terms   = wp_get_post_terms($parent_id, 'listing_type');
                                $parent_type_id = !empty($parent_terms) && !is_wp_error($parent_terms) ? $parent_terms[0]->term_id : 0;
                                if ($parent_type_id) {
                                    $check_term = get_term($parent_type_id, 'listing_type');
                                    while($check_term && $check_term->parent != 0) { $check_term = get_term($check_term->parent, 'listing_type'); }
                                    if ($check_term) $parent_type_id = $check_term->term_id;
                                }
                                $sub_types = get_terms(array('taxonomy' => 'listing_type', 'parent' => $parent_type_id, 'hide_empty' => false));
                                if (!is_wp_error($sub_types)) {
                                    foreach ($sub_types as $type) {
                                        $sel = ($selected_type == $type->term_id) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($type->term_id) . '" data-slug="' . esc_attr($type->slug) . '" ' . $sel . '>' . esc_html($type->name) . '</option>';
                                    }
                                }
                            } else {
                                $top_types = get_terms(array('taxonomy' => 'listing_type', 'parent' => 0, 'hide_empty' => false));
                                if (!is_wp_error($top_types)) {
                                    foreach ($top_types as $type) {
                                        $sel = ($selected_type == $type->term_id) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($type->term_id) . '" data-slug="' . esc_attr($type->slug) . '" ' . $sel . '>' . esc_html($type->name) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <?php if(!$is_child): ?><p style="font-size:0.75rem; color:#888; margin-top:5px;"><?php echo __('Choose the primary industry. You will select specific sub-types for each unit/session.', 'obenlo'); ?></p><?php endif; ?>
                    </div>

                    <?php if (!$is_child): ?>
                        <div class="grid-row" style="margin-bottom:20px;">
                            <div id="generic_location_wrapper" style="flex:2;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Address / Location', 'obenlo'); ?></label>
                                <input type="text" name="listing_location" value="<?php echo esc_attr($location); ?>" placeholder="<?php echo esc_attr(__('e.g. Tulum, Mexico', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Country', 'obenlo'); ?></label>
                                <select name="listing_country" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                    <option value="usa" <?php selected($listing_country, 'usa'); ?>><?php echo __('United States (USA)', 'obenlo'); ?></option>
                                    <option value="haiti" <?php selected($listing_country, 'haiti'); ?>><?php echo __('Haiti 🇭🇹', 'obenlo'); ?></option>
                                    <option value="other" <?php selected($listing_country, 'other'); ?>><?php echo __('Other / International', 'obenlo'); ?></option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom:0;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo esc_html($desc_label); ?></label>
                        <textarea name="listing_content" rows="6" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;" placeholder="<?php echo $is_child ? esc_attr(__('Describe this specific unit, room, or session...', 'obenlo')) : esc_attr(__('Describe your overall business, property, or service group...', 'obenlo')); ?>"><?php echo esc_textarea($content); ?></textarea>
                    </div>

                    <?php if ($is_child): ?>
                        <!-- Event Specific Configuration -->
                        <div id="event_config_wrapper" style="margin-top:20px; display:none; padding:20px; background:#f9f9f9; border-radius:12px; border:1px solid #eee;">
                            <h4 style="margin-top:0; margin-bottom:15px; color:#333;"><?php echo __('Event Schedule & Location', 'obenlo'); ?></h4>
                            <div style="margin-bottom:15px;">
                                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700;">
                                    <input type="checkbox" name="event_is_fixed" value="yes" id="event_is_fixed_toggle" <?php checked($event_is_fixed, 'yes'); ?>>
                                    <?php echo __('Specific Scheduled Time (e.g., Monday 8 April, 4pm-10pm)', 'obenlo'); ?>
                                </label>
                            </div>
                            <div id="fixed_time_fields" style="display:<?php echo ($event_is_fixed === 'yes') ? 'block' : 'none'; ?>; margin-bottom:20px;">
                                <div class="grid-row">
                                    <div class="grid-col-1-3"><label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;"><?php echo __('Event Date', 'obenlo'); ?></label><input type="date" name="event_date" value="<?php echo esc_attr($event_date); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                                    <div class="grid-col-1-3"><label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;"><?php echo __('Start Time', 'obenlo'); ?></label><input type="time" name="event_start_time" value="<?php echo esc_attr($event_start_time); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                                    <div class="grid-col-1-3"><label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;"><?php echo __('End Time', 'obenlo'); ?></label><input type="time" name="event_end_time" value="<?php echo esc_attr($event_end_time); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                                </div>
                            </div>
                            <div style="margin-bottom:15px;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Event Type', 'obenlo'); ?></label>
                                <select name="event_location_type" id="event_location_type_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                    <option value="virtual" <?php selected($event_location_type, 'virtual'); ?>><?php echo __('Virtual (Zoom, Google Meet, etc.)', 'obenlo'); ?></option>
                                    <option value="in_person" <?php selected($event_location_type, 'in_person'); ?>><?php echo __('In-Person (Physical Address)', 'obenlo'); ?></option>
                                </select>
                            </div>
                            <div id="virtual_link_wrapper" style="display:<?php echo ($event_location_type === 'virtual') ? 'block' : 'none'; ?>;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Virtual Meeting Link', 'obenlo'); ?></label>
                                <input type="url" name="virtual_link" value="<?php echo esc_url($virtual_link); ?>" placeholder="<?php echo esc_attr(__('https://zoom.us/j/...', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                            </div>
                            <div id="in_person_address_wrapper" style="display:<?php echo ($event_location_type === 'in_person') ? 'block' : 'none'; ?>;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Event Address', 'obenlo'); ?></label>
                                <input type="text" name="listing_event_address" value="<?php echo esc_attr($location); ?>" placeholder="<?php echo esc_attr(__('Enter physical address...', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pricing & Capacity -->
                <?php if ($is_child): ?>
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Pricing & Booking Rules', 'obenlo'); ?></h4>
                        <div class="grid-row" style="margin-bottom:20px;">
                            <div class="grid-col-1-2">
                                <label id="listing_price_label" style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Base Price', 'obenlo'); ?></label>
                                <div style="position:relative;"><span style="position:absolute; left:12px; top:12px; color:#888;">$</span><input type="number" step="0.01" name="listing_price" value="<?php echo esc_attr($price); ?>" required style="width:100%; padding:12px 12px 12px 30px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;"></div>
                            </div>
                            <div id="pricing_model_wrapper" class="grid-col-1-2">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Pricing Model', 'obenlo'); ?></label>
                                <select name="pricing_model" id="pricing_model" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                    <option value="per_night" <?php selected($pricing_model, 'per_night'); ?>><?php echo __('Per Night', 'obenlo'); ?></option>
                                    <option value="per_day" <?php selected($pricing_model, 'per_day'); ?>><?php echo __('Per Day', 'obenlo'); ?></option>
                                    <option value="per_hour" <?php selected($pricing_model, 'per_hour'); ?>><?php echo __('Per Hour', 'obenlo'); ?></option>
                                    <option value="per_session" <?php selected($pricing_model, 'per_session'); ?>><?php echo __('Per Session / Appointment', 'obenlo'); ?></option>
                                    <option value="per_person" <?php selected($pricing_model, 'per_person'); ?>><?php echo __('Per Person', 'obenlo'); ?></option>
                                    <option value="per_event" <?php selected($pricing_model, 'per_event'); ?>><?php echo __('Per Event (Flat Fee)', 'obenlo'); ?></option>
                                    <option value="per_donation" <?php selected($pricing_model, 'per_donation'); ?>><?php echo __('Per Donation (Fixed Amount)', 'obenlo'); ?></option>
                                    <option value="custom_donation" <?php selected($pricing_model, 'custom_donation'); ?>><?php echo __('Custom Donation Amount (Pay What You Want)', 'obenlo'); ?></option>
                                    <option value="flat_fee" <?php selected($pricing_model, 'flat_fee'); ?>><?php echo __('Flat Fee', 'obenlo'); ?></option>
                                    <option value="inquiry_only" <?php selected($pricing_model, 'inquiry_only'); ?>><?php echo __('Inquiry Only (Contact to Book)', 'obenlo'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="grid-row" style="margin-bottom:20px;">
                            <div id="capacity_wrapper" class="grid-col-1-2">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Max Capacity (Guests/Tickets)', 'obenlo'); ?></label>
                                <input type="number" name="listing_capacity" value="<?php echo esc_attr($capacity); ?>" placeholder="<?php echo esc_attr(__('Leave blank if not applicable', 'obenlo')); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;">
                            </div>
                            <div id="units_wrapper" class="grid-col-1-2">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Available Units (Concurrent Slots)', 'obenlo'); ?></label>
                                <input type="number" name="available_units" value="<?php echo esc_attr($available_units); ?>" min="1" step="1" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;">
                                <p style="font-size:0.75rem; color:#888; margin-top:4px;"><?php echo __('How many times can this be booked at once?', 'obenlo'); ?></p>
                            </div>
                        </div>
                        <div class="grid-row" style="margin-bottom:20px;">
                            <div id="duration_wrapper" class="grid-col-1-2" style="display:flex; flex-wrap:wrap; gap:10px;">
                                <div style="flex:1;"><label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Duration', 'obenlo'); ?></label><input type="number" step="0.5" name="duration_val" value="<?php echo esc_attr($duration_val); ?>" placeholder="e.g. 1.5" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;"></div>
                                <div style="flex:1;"><label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Unit', 'obenlo'); ?></label><select name="duration_unit" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;"><option value="hours" <?php selected($duration_unit, 'hours'); ?>><?php echo __('Hours', 'obenlo'); ?></option><option value="minutes" <?php selected($duration_unit, 'minutes'); ?>><?php echo __('Minutes', 'obenlo'); ?></option></select></div>
                                <p style="font-size:0.75rem; color:#888; margin-top:8px; width:100%;"><?php echo __('This predefines how long the service takes. It will appear as the default duration on your booking form.', 'obenlo'); ?></p>
                            </div>
                        </div>
                        <div id="requires_slots_wrapper" style="margin-top:10px;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="requires_slots" value="yes" <?php checked($requires_slots, 'yes'); ?> style="width:18px; height:18px;">
                                <span style="font-weight:700; color:#444;"><?php echo __('Requires Booking Time Slots', 'obenlo'); ?></span>
                            </label>
                            <p style="font-size:0.85rem; color:#666; margin-top:5px; margin-left:28px;"><?php echo __('Check this if you want the calendar to automatically show available time slots during your business hours (e.g. for Haircuts, Spa treatments).', 'obenlo'); ?></p>
                        </div>

                        <!-- Modular Booking Engine Fields (Injected dynamically) -->
                        <div id="obenlo-modular-engine-fields">
                            <?php 
                            $engine = Obenlo_Engine_Manager::instance()->get_engine_for_listing($listing_id);
                            if ($engine) {
                                echo $engine->render_host_fields($listing_id, $selected_type_slug);
                            }
                            ?>
                        </div>

                        <!-- Logistics Fallback Wrapper (for JS toggle) -->
                        <div id="logistics_wrapper" style="display:none;">
                             <?php 
                             $logis_engine = Obenlo_Engine_Manager::instance()->get_engine('logistics');
                             if ($logis_engine && (!$engine || $engine->get_id() !== 'logistics')) {
                                 echo $logis_engine->render_host_fields($listing_id);
                             }
                             ?>
                        </div>
                        <input type="hidden" name="requires_logistics" id="hidden_requires_logistics" value="<?php echo (get_post_meta($listing_id, '_obenlo_requires_logistics', true) === 'yes') ? 'yes' : 'no'; ?>">
                        
                        <!-- Legacy Ref: runsWrapper JS still looks for this, so we'll keep the ID on the modular container if needed, or update JS -->
                    </div>
                <?php else: ?>
                    <div class="form-section" style="background:#f0f9ff; border:1px solid #bae6fd; padding:20px; border-radius:12px; margin-bottom:30px;">
                        <div style="display:flex; gap:15px; align-items:center;">
                            <div style="font-size:1.5rem;">ℹ️</div>
                            <div>
                                <h4 style="margin:0 0 5px 0; color:#0369a1;"><?php echo __('Business Profile Mode', 'obenlo'); ?></h4>
                                <p style="margin:0; font-size:0.9rem; color:#0c4a6e;"><?php echo __('This is your main profile. You will add bookable units, sessions, or events (with their own pricing) after saving this profile.', 'obenlo'); ?></p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="listing_price" value="0">
                <?php endif; ?>

                <!-- Amenities -->
                <?php
                $amenity_title = 'Amenities';
                if (in_array($category_flag, ['experience', 'event', 'show'])) { $amenity_title = "What's Included"; }
                ?>
                <div class="form-section">
                    <h4 id="amenities_heading" style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo esc_html($amenity_title); ?></h4>
                    <div id="amenities-container">
                        <?php
                        $current_amenities = wp_get_post_terms($listing_id, 'listing_amenity', array('fields' => 'names'));
                        if (is_wp_error($current_amenities)) $current_amenities = array();
                        foreach ($current_amenities as $amenity_name): ?>
                            <div class="amenity-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                <input type="text" name="listing_amenities_repeater[]" value="<?php echo esc_attr($amenity_name); ?>" placeholder="e.g. WiFi or Textbook" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <button type="button" class="remove-amenity-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-amenity-btn" style="background:#f9f9f9; border:1px dashed #ccc; color:#666; width:100%; padding:12px; border-radius:10px; cursor:pointer; font-weight:600; transition:all 0.2s;" onmouseover="this.style.borderColor='#e61e4d';this.style.color='#e61e4d'" onmouseout="this.style.borderColor='#ccc';this.style.color='#666'">+ <?php echo __('Add New', 'obenlo'); ?></button>
                    <template id="amenity-template">
                        <div class="amenity-row" style="display:flex; gap:10px; margin-bottom:12px;">
                            <input type="text" name="listing_amenities_repeater[]" value="" placeholder="<?php echo esc_attr(__('e.g. WiFi or Textbook', 'obenlo')); ?>" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            <button type="button" class="remove-amenity-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                        </div>
                    </template>
                </div>

                <?php if ($is_child): ?>
                    <!-- Add-ons -->
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Add-ons (Optional Upsells)', 'obenlo'); ?></h4>
                        <div id="addons-container">
                            <?php foreach ($addons as $addon): ?>
                                <div class="addon-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                    <input type="text" name="addon_names[]" value="<?php echo esc_attr($addon['name']); ?>" placeholder="Addon (e.g. Breakfast)" style="flex:2; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                    <input type="number" step="0.01" name="addon_prices[]" value="<?php echo esc_attr($addon['price']); ?>" placeholder="$" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                    <button type="button" class="remove-addon-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="add-addon-btn" style="background:#f9f9f9; border:1px dashed #ccc; color:#666; width:100%; padding:12px; border-radius:10px; cursor:pointer; font-weight:600;">+ <?php echo __('Add New Addon', 'obenlo'); ?></button>
                        <template id="addon-template">
                            <div class="addon-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                <input type="text" name="addon_names[]" value="" placeholder="<?php echo esc_attr(__('Addon (e.g. Breakfast)', 'obenlo')); ?>" style="flex:2; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <input type="number" step="0.01" name="addon_prices[]" value="" placeholder="$" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <button type="button" class="remove-addon-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                            </div>
                        </template>

                        <template id="run-template">
                            <div class="session-run-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                                <select name="run_days[]" style="flex:1.5; padding:10px; border:1px solid #ddd; border-radius:8px; background:#fff;">
                                    <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option><option value="Sunday">Sunday</option><option value="Daily">Daily</option>
                                </select>
                                <input type="time" name="run_starts[]" value="" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <span style="color:#888;">to</span>
                                <input type="time" name="run_ends[]" value="" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <button type="button" class="remove-run-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800; height:40px;">&times;</button>
                            </div>
                        </template>
                    </div>
                <?php endif; ?>

                <!-- Media -->
                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo $media_label; ?></h4>
                    <?php if ($listing_id > 0):
                        $images    = get_attached_media('image', $listing_id);
                        $thumb_id  = get_post_thumbnail_id($listing_id);
                    ?>
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(100px, 1fr)); gap:15px; margin-bottom:20px;">
                            <?php foreach ($images as $img_id => $img):
                                $img_url    = wp_get_attachment_image_url($img_id, 'thumbnail');
                                $is_featured = ($img_id == $thumb_id);
                            ?>
                                <div style="position:relative; aspect-ratio:1; border-radius:12px; overflow:hidden; border:2px solid <?php echo $is_featured ? '#e61e4d' : '#eee'; ?>;">
                                    <img src="<?php echo esc_url($img_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php if ($is_featured): ?><div style="position:absolute; top:5px; left:5px; background:#e61e4d; color:#fff; font-size:8px; font-weight:800; padding:2px 6px; border-radius:20px;">COVER</div><?php endif; ?>
                                    <label style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:10px; text-align:center; padding:3px; cursor:pointer;">
                                        <input type="checkbox" name="delete_images[]" value="<?php echo esc_attr($img_id); ?>"> <?php echo __('Remove', 'obenlo'); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div style="background:#fcfcfc; border:2px dashed #eee; padding:30px; border-radius:15px; text-align:center;">
                        <input type="file" name="listing_images[]" multiple accept="image/*" style="display:none;" id="listing_file_input" data-limit="<?php echo $media_limit; ?>">
                        <label for="listing_file_input" style="cursor:pointer; color:#888;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px; height:40px; margin-bottom:10px; color:#ccc;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <div style="font-weight:700; color:#444;"><?php echo __('Click to upload photos', 'obenlo'); ?></div>
                            <div style="font-size:0.8rem; color:#e61e4d;"><?php echo esc_html($media_hint); ?></div>
                        </label>
                    </div>
                </div>

                <?php if (!$is_child): ?>
                    <!-- Policies -->
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo __('Policies & Rules', 'obenlo'); ?></h4>
                        <div style="margin-bottom:20px;">
                            <select name="policy_type" id="policy_type_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                <option value="global" <?php selected($policy_type, 'global'); ?>><?php echo __('Use Obenlo Global Policies (Standard)', 'obenlo'); ?></option>
                                <option value="custom" <?php selected($policy_type, 'custom'); ?>><?php echo __('Set Custom Policies', 'obenlo'); ?></option>
                            </select>
                        </div>
                        <div id="custom_policies_fields" style="display:<?php echo($policy_type === 'custom') ? 'block' : 'none'; ?>;">
                            <div style="margin-bottom:20px;"><label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Cancellation Policy', 'obenlo'); ?></label><textarea name="policy_cancel" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_cancel); ?></textarea></div>
                            <div style="margin-bottom:20px;"><label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('Refund Policy', 'obenlo'); ?></label><textarea name="policy_refund" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_refund); ?></textarea></div>
                            <div style="margin-bottom:0;"><label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo __('House Rules / Other', 'obenlo'); ?></label><textarea name="policy_other" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_other); ?></textarea></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top:40px; margin-bottom:100px;">
                    <button type="submit" class="btn-primary" style="padding:15px 40px; font-size:1.1rem; width:100%;"><?php echo sprintf(__('Save %s Now', 'obenlo'), ($is_child ? __('Unit', 'obenlo') : __('Listing', 'obenlo'))); ?></button>
                    <p style="text-align:center; color:#888; font-size:0.85rem; margin-top:15px;"><?php echo __("By saving, you agree to Obenlo's hosting standard and quality guidelines.", 'obenlo'); ?></p>
                </div>
            </form>
        </div>
        <?php
        $this->render_ube_javascript($is_child, $selected_type, $listing_id);
    }

    // ────────────────────────────────────────────────────────────
    // U.B.E. JAVASCRIPT ENGINE (isolated here, nowhere else)
    // ────────────────────────────────────────────────────────────
    private function render_ube_javascript($is_child, $selected_type, $listing_id = 0)
    {
        $types    = get_terms(array('taxonomy' => 'listing_type', 'hide_empty' => false));
        $type_map = array();
        $slug_map = array();

        if (!is_wp_error($types)) {
            foreach ($types as $type) {
                $slugs_to_check = [$type->slug];
                if ($type->parent != 0) {
                    $parent_term = get_term($type->parent, 'listing_type');
                    if ($parent_term && !is_wp_error($parent_term)) { $slugs_to_check[] = $parent_term->slug; }
                }
                $engine_assigned = 'engine_default';
                foreach(array_reverse($slugs_to_check) as $slug) {
                    if (in_array($slug, ['hotel','guest-house','stay']))                                                              { $engine_assigned = 'engine_nightly'; }
                    elseif (in_array($slug, ['barber','barbershop','hairdresser','beauty','cook','handyman','cleaning','babysitter','dogsitter','service'])) { $engine_assigned = 'engine_slot'; }
                    elseif (in_array($slug, ['tour','food-tasting','photo-shoot','experience']))                                     { $engine_assigned = 'engine_fixed_block'; }
                    elseif (in_array($slug, ['event','show','class','celebration','donation-giving','ticket','donation']))           { $engine_assigned = 'engine_session'; }
                    elseif (in_array($slug, ['delivery','chauffeur','driver']))                                                      { $engine_assigned = 'engine_logistics'; }
                    elseif (in_array($slug, ['concierge','personal-assistant','freelance','photographer']))                          { $engine_assigned = 'engine_inquiry'; }
                }
                $type_map[$type->term_id] = $engine_assigned;
                $slug_map[$type->term_id] = $type->slug;
            }
        }
        $init_type_id = $selected_type ? $selected_type : '';
        ?>
        <script>
        (function() {
            try {
                var typeMap      = <?php echo json_encode($type_map, JSON_FORCE_OBJECT); ?>;
                var slugMap      = <?php echo json_encode($slug_map, JSON_FORCE_OBJECT); ?>;
                var isChild      = <?php echo $is_child ? 'true' : 'false'; ?>;
                var initTypeId   = '<?php echo esc_js($init_type_id); ?>';
                var isEditMode   = <?php echo ($listing_id > 0) ? 'true' : 'false'; ?>;

                var typeSelect          = document.querySelector('select[name="listing_type"]');
                var priceLabel          = document.getElementById('listing_price_label');
                var capContainer        = document.getElementById('capacity_wrapper');
                var capLabel            = capContainer ? capContainer.querySelector('label') : null;
                var pricingModel        = document.getElementById('pricing_model');
                var durationWrapper     = document.getElementById('duration_wrapper');
                var slotsWrapper        = document.getElementById('requires_slots_wrapper');
                var eventConfigWrapper  = document.getElementById('event_config_wrapper');
                var genericLocWrapper   = document.getElementById('generic_location_wrapper');
                var runsWrapper         = document.getElementById('schedule_runs_wrapper');
                var logisWrapper        = document.getElementById('logistics_wrapper');
                var hiddenLogis         = document.getElementById('hidden_requires_logistics');

                function updateFormLogic(typeId) {
                    var engine = typeMap[typeId] || 'engine_default';

                    // Defaults — show everything
                    if (capContainer)       capContainer.style.display       = 'block';
                    if (durationWrapper)    durationWrapper.style.display    = 'flex';
                    if (slotsWrapper)       slotsWrapper.style.display       = 'block';
                    if (eventConfigWrapper) eventConfigWrapper.style.display = 'none';
                    if (genericLocWrapper)  genericLocWrapper.style.display  = 'block';
                    if (logisWrapper)       logisWrapper.style.display       = 'none';
                    if (hiddenLogis)        hiddenLogis.value                = 'no';

                    var amenHeading = document.getElementById('amenities_heading');
                    if (pricingModel) {
                        Array.from(pricingModel.options).forEach(function(opt) { opt.hidden = false; opt.disabled = false; });
                    }

                    // Modular AJAX Update for Dashboard Fields
                    var modularContainer = document.getElementById('obenlo-modular-engine-fields');
                    if (modularContainer && typeId) {
                        var data = new FormData();
                        data.append('action', 'obenlo_get_engine_fields');
                        data.append('type_id', typeId);
                        data.append('listing_id', '<?php echo $listing_id; ?>');

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: data })
                        .then(res => res.json())
                        .then(response => {
                            if (response.success && response.data.html) {
                                modularContainer.innerHTML = response.data.html;
                                
                                // Re-run engine specific JS logic
                                var currentSlug = slugMap[typeId] || '';
                                <?php foreach (Obenlo_Engine_Manager::instance()->get_engines() as $id => $eng): ?>
                                if (engine === 'engine_<?php echo $id; ?>') {
                                    <?php echo $eng->get_host_js_logic(); ?>
                                }
                                <?php endforeach; ?>
                            }
                        });
                    }

                    // Categories with logistics enablement
                    var currentSlug = slugMap[typeId] || '';
                    var logisSlugs = ['delivery', 'chauffeur', 'driver', 'moving', 'towing', 'shipping', 'transport', 'babysitter', 'dogsitter', 'cleaning', 'handyman', 'cook', 'hairdresser', 'barber', 'massage', 'beauty', 'service'];
                    if (logisSlugs.includes(currentSlug)) {
                        if (logisWrapper) {
                            logisWrapper.style.display = isChild ? 'block' : 'none';
                            if (hiddenLogis && isChild) hiddenLogis.value = 'yes';
                        }
                    }

                    if (engine === 'engine_default') {
                        if (priceLabel) priceLabel.innerText = '<?php echo esc_js(__('Price (Base)', 'obenlo')); ?>';
                        if (capLabel)   capLabel.innerText   = '<?php echo esc_js(__('Capacity/Max Guests', 'obenlo')); ?>';
                        if (amenHeading) amenHeading.innerText = '<?php echo esc_js(__('Amenities', 'obenlo')); ?>';
                    }

                    updateEventLocationToggles();
                }

                function updateEventLocationToggles() {
                    var evLocSel     = document.getElementById('event_location_type_select');
                    var vLinkWrapper = document.getElementById('virtual_link_wrapper');
                    var inPersWrapper = document.getElementById('in_person_address_wrapper');
                    if (evLocSel && vLinkWrapper && inPersWrapper) {
                        vLinkWrapper.style.display  = evLocSel.value === 'virtual'   ? 'block' : 'none';
                        inPersWrapper.style.display = evLocSel.value === 'in_person' ? 'block' : 'none';
                    }
                }

                if (typeSelect) {
                    typeSelect.addEventListener('change', function(e) { updateFormLogic(e.target.value); });
                    if (typeof jQuery !== 'undefined') {
                        jQuery(typeSelect).on('select2:select change', function(e) { updateFormLogic(e.target.value); });
                    }
                    // Polling safety net for custom select2 implementations
                    var lastVal = typeSelect.value;
                    setInterval(function() {
                        if (typeSelect.value !== lastVal) { lastVal = typeSelect.value; updateFormLogic(typeSelect.value); }
                    }, 500);
                    // Fire on load
                    if (initTypeId) { updateFormLogic(initTypeId); }
                    else if (typeSelect.value) { updateFormLogic(typeSelect.value); }
                }

                var evLocSel2 = document.getElementById('event_location_type_select');
                if (evLocSel2) { evLocSel2.addEventListener('change', updateEventLocationToggles); }

            } catch(err) { console.error('Obenlo UBE JS Error: ' + err); }

            // ── Addons Repeater ──────────────────────────────────────
            document.addEventListener('click', function(e) {
                if (e.target.id === 'add-addon-btn') {
                    var container = document.getElementById('addons-container');
                    var template  = document.getElementById('addon-template');
                    if (container && template) { container.appendChild(template.content.cloneNode(true)); }
                }
                if (e.target.classList.contains('remove-addon-btn')) { e.target.closest('.addon-row').remove(); }
            });

            // ── Session Runs Repeater ─────────────────────────────────
            document.addEventListener('click', function(e) {
                if (e.target.id === 'add-run-btn') {
                    var container = document.getElementById('session-runs-container');
                    var template  = document.getElementById('run-template');
                    if (container && template) { container.appendChild(template.content.cloneNode(true)); }
                }
                if (e.target.classList.contains('remove-run-btn')) { e.target.closest('.session-run-row').remove(); }
            });

            // ── Amenities Repeater ───────────────────────────────────
            document.addEventListener('click', function(e) {
                if (e.target.id === 'add-amenity-btn') {
                    var container = document.getElementById('amenities-container');
                    var template  = document.getElementById('amenity-template');
                    if (container && template) { container.appendChild(template.content.cloneNode(true)); }
                }
                if (e.target.classList.contains('remove-amenity-btn')) { e.target.closest('.amenity-row').remove(); }
            });


            // ── Policy Custom Fields Toggle ──────────────────────────
            var policySelect  = document.getElementById('policy_type_select');
            var customFields  = document.getElementById('custom_policies_fields');
            if (policySelect && customFields) {
                policySelect.addEventListener('change', function(e) { customFields.style.display = e.target.value === 'custom' ? 'block' : 'none'; });
            }

            // ── Event Fixed Toggle ───────────────────────────────────
            var eventFixedToggle = document.getElementById('event_is_fixed_toggle');
            var fixedTimeFields  = document.getElementById('fixed_time_fields');
            if (eventFixedToggle && fixedTimeFields) {
                eventFixedToggle.addEventListener('change', function() { fixedTimeFields.style.display = this.checked ? 'block' : 'none'; });
            }
        })();
        </script>
        <?php
    }

    // ────────────────────────────────────────────────────────────
    // SAVE HANDLER (fully extracted from monolith)
    // ────────────────────────────────────────────────────────────
    public function handle_save_listing()
    {
        if (!isset($_POST['dashboard_listing_nonce']) || !wp_verify_nonce($_POST['dashboard_listing_nonce'], 'dashboard_save_listing')) {
            obenlo_redirect_with_error('security_failed');
        }
        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            obenlo_redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $title      = sanitize_text_field($_POST['listing_title']);
        $content    = wp_kses_post(wp_unslash($_POST['listing_content']));
        $parent_id  = 0;
        if (isset($_POST['parent_id']))     { $parent_id = intval($_POST['parent_id']); }
        elseif (isset($_GET['parent_id'])) { $parent_id = intval($_GET['parent_id']); }

        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'listing',
            'post_parent'  => $parent_id
        );

        if ($listing_id > 0) {
            $post_data['ID'] = $listing_id;
            $existing_post   = get_post($listing_id);
            if ($existing_post) { $post_data['post_author'] = $existing_post->post_author; }
        } else {
            if ($parent_id > 0) {
                $parent                  = get_post($parent_id);
                $post_data['post_author'] = $parent ? $parent->post_author : get_current_user_id();
            } else {
                $post_data['post_author'] = get_current_user_id();
            }
        }

        if ($listing_id > 0) {
            if (isset($existing_post) && $existing_post->post_author != get_current_user_id() && !current_user_can('administrator')) {
                obenlo_redirect_with_error('unauthorized');
            }
            $new_post_id = wp_update_post($post_data);
        } else {
            $new_post_id = wp_insert_post($post_data);

            // Demo auto-attribution
            Obenlo_Demo_Manager::assign_demo_ownership($new_post_id);
        }

        if ($new_post_id && !is_wp_error($new_post_id)) {
            // ── Core Meta ───────────────────────────────────────────
            if (isset($_POST['listing_price']))    update_post_meta($new_post_id, '_obenlo_price',          sanitize_text_field($_POST['listing_price']));
            if (isset($_POST['listing_capacity'])) update_post_meta($new_post_id, '_obenlo_capacity',       sanitize_text_field($_POST['listing_capacity']));
            if (isset($_POST['available_units']))  update_post_meta($new_post_id, '_obenlo_available_units', intval($_POST['available_units']));

            if (isset($_POST['listing_location']) && !empty($_POST['listing_location'])) {
                update_post_meta($new_post_id, '_obenlo_location', sanitize_text_field($_POST['listing_location']));
            } elseif (isset($_POST['listing_event_address']) && !empty($_POST['listing_event_address'])) {
                update_post_meta($new_post_id, '_obenlo_location', sanitize_text_field($_POST['listing_event_address']));
            }

            if (isset($_POST['listing_country'])) {
                update_post_meta($new_post_id, '_obenlo_listing_country', sanitize_text_field($_POST['listing_country']));
            } elseif ($parent_id > 0) {
                $parent_country = get_post_meta($parent_id, '_obenlo_listing_country', true);
                if ($parent_country) update_post_meta($new_post_id, '_obenlo_listing_country', $parent_country);
            }

            if (isset($_POST['virtual_link'])) update_post_meta($new_post_id, '_obenlo_virtual_link', esc_url_raw($_POST['virtual_link']));

            // ── Event Scheduling ─────────────────────────────────────
            update_post_meta($new_post_id, '_obenlo_event_is_fixed', isset($_POST['event_is_fixed']) ? 'yes' : 'no');
            if (isset($_POST['event_date']))          update_post_meta($new_post_id, '_obenlo_event_date',          sanitize_text_field($_POST['event_date']));
            if (isset($_POST['event_start_time']))    update_post_meta($new_post_id, '_obenlo_event_start_time',    sanitize_text_field($_POST['event_start_time']));
            if (isset($_POST['event_end_time']))      update_post_meta($new_post_id, '_obenlo_event_end_time',      sanitize_text_field($_POST['event_end_time']));
            if (isset($_POST['event_location_type'])) update_post_meta($new_post_id, '_obenlo_event_location_type', sanitize_text_field($_POST['event_location_type']));

            // ── Booking Engine Meta ───────────────────────────────────
            if (isset($_POST['pricing_model']))  update_post_meta($new_post_id, '_obenlo_pricing_model',  sanitize_text_field($_POST['pricing_model']));
            if (isset($_POST['duration_val']))   update_post_meta($new_post_id, '_obenlo_duration_val',   sanitize_text_field($_POST['duration_val']));
            if (isset($_POST['duration_unit']))  update_post_meta($new_post_id, '_obenlo_duration_unit',  sanitize_text_field($_POST['duration_unit']));
            update_post_meta($new_post_id, '_obenlo_requires_slots', (isset($_POST['requires_slots']) && $_POST['requires_slots'] === 'yes') ? 'yes' : 'no');
            update_post_meta($new_post_id, '_obenlo_requires_logistics', (isset($_POST['requires_logistics']) && $_POST['requires_logistics'] === 'yes') ? 'yes' : 'no');
            if (isset($_POST['logistics_route'])) update_post_meta($new_post_id, '_obenlo_logistics_route', sanitize_text_field($_POST['logistics_route']));
            
            // Advanced Logistics Pricing Meta
            if (isset($_POST['logistics_mile_rate']))  update_post_meta($new_post_id, '_obenlo_logistics_mile_rate',  sanitize_text_field($_POST['logistics_mile_rate']));
            if (isset($_POST['logistics_base_price'])) update_post_meta($new_post_id, '_obenlo_logistics_base_price', sanitize_text_field($_POST['logistics_base_price']));
            if (isset($_POST['logistics_flat_price'])) update_post_meta($new_post_id, '_obenlo_logistics_flat_price', sanitize_text_field($_POST['logistics_flat_price']));
            if (isset($_POST['logistics_flat_miles'])) update_post_meta($new_post_id, '_obenlo_logistics_flat_miles', sanitize_text_field($_POST['logistics_flat_miles']));
            if (isset($_POST['logistics_flat_mins']))  update_post_meta($new_post_id, '_obenlo_logistics_flat_mins',  sanitize_text_field($_POST['logistics_flat_mins']));

            // ── Demo Configuration ────────────────────────────────────
            Obenlo_Demo_Manager::save_demo_configuration($new_post_id);

            // ── Policies (parent only) ────────────────────────────────
            if ($parent_id == 0) {
                if (isset($_POST['policy_type']))   update_post_meta($new_post_id, '_obenlo_policy_type',   sanitize_text_field($_POST['policy_type']));
                if (isset($_POST['policy_cancel'])) update_post_meta($new_post_id, '_obenlo_policy_cancel', sanitize_textarea_field(wp_unslash($_POST['policy_cancel'])));
                if (isset($_POST['policy_refund'])) update_post_meta($new_post_id, '_obenlo_policy_refund', sanitize_textarea_field(wp_unslash($_POST['policy_refund'])));
                if (isset($_POST['policy_other']))  update_post_meta($new_post_id, '_obenlo_policy_other',  sanitize_textarea_field(wp_unslash($_POST['policy_other'])));
            }

            // ── Structured Add-ons Repeater ───────────────────────────
            $structured_addons = array();
            if (isset($_POST['addon_names']) && isset($_POST['addon_prices'])) {
                $names  = $_POST['addon_names'];
                $prices = $_POST['addon_prices'];
                for ($i = 0; $i < count($names); $i++) {
                    $name = sanitize_text_field(wp_unslash($names[$i]));
                    if (!empty($name)) { $structured_addons[] = array('name' => $name, 'price' => sanitize_text_field(wp_unslash($prices[$i]))); }
                }
            }
            update_post_meta($new_post_id, '_obenlo_addons_structured', wp_json_encode($structured_addons));

            // ── Session Runs Repeater ─────────────────────────────────
            $session_runs = array();
            if (isset($_POST['run_days']) && isset($_POST['run_starts']) && isset($_POST['run_ends'])) {
                $days   = $_POST['run_days'];
                $starts = $_POST['run_starts'];
                $ends   = $_POST['run_ends'];
                for ($i = 0; $i < count($days); $i++) {
                    if (!empty($starts[$i])) {
                        $session_runs[] = array(
                            'day'   => sanitize_text_field($days[$i]),
                            'start' => sanitize_text_field($starts[$i]),
                            'end'   => sanitize_text_field($ends[$i])
                        );
                    }
                }
            }
            update_post_meta($new_post_id, '_obenlo_session_runs', wp_json_encode($session_runs));

            // ── Taxonomy: Type ────────────────────────────────────────
            $post_type_override = isset($_POST['listing_type']) ? intval($_POST['listing_type']) : 0;
            if ($parent_id > 0 && !$post_type_override) {
                $parent_terms = wp_get_post_terms($parent_id, 'listing_type');
                if ($parent_terms && !is_wp_error($parent_terms)) { $post_type_override = $parent_terms[0]->term_id; }
            }
            if ($post_type_override) { wp_set_post_terms($new_post_id, array($post_type_override), 'listing_type'); }

            // ── Taxonomy: Amenities ───────────────────────────────────
            $selected_amenities = array();
            if (isset($_POST['listing_amenities_repeater']) && is_array($_POST['listing_amenities_repeater'])) {
                foreach ($_POST['listing_amenities_repeater'] as $amenity_val) {
                    $term_name = trim(sanitize_text_field(wp_unslash($amenity_val)));
                    if (!empty($term_name)) {
                        $term = term_exists($term_name, 'listing_amenity') ?: wp_insert_term($term_name, 'listing_amenity');
                        if (!is_wp_error($term) && isset($term['term_id'])) { $selected_amenities[] = intval($term['term_id']); }
                    }
                }
            }
            wp_set_post_terms($new_post_id, $selected_amenities, 'listing_amenity');

            // ── Images ───────────────────────────────────────────────
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $del_id) {
                    if (get_post_thumbnail_id($new_post_id) == $del_id) { delete_post_thumbnail($new_post_id); }
                    wp_delete_attachment(intval($del_id), true);
                }
            }
            $remaining = get_attached_media('image', $new_post_id);
            if (!has_post_thumbnail($new_post_id) && count($remaining) > 0) { set_post_thumbnail($new_post_id, array_key_first($remaining)); }

            if (isset($_FILES['listing_images']) && !empty($_FILES['listing_images']['name'][0])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                $files = $_FILES['listing_images']; $count = count($files['name']);
                $current_count = count(get_attached_media('image', $new_post_id));
                for ($i = 0; $i < $count; $i++) {
                    if ($files['name'][$i]) {
                        if ($current_count >= 10) break;
                        $file = array('name'=>$files['name'][$i],'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]);
                        $_FILES = array('upload_file' => $file);
                        $att_id = media_handle_upload('upload_file', $new_post_id);
                        if (!is_wp_error($att_id)) { $current_count++; if (!has_post_thumbnail($new_post_id)) set_post_thumbnail($new_post_id, $att_id); }
                    }
                }
            }
        }

        $redirect_url = remove_query_arg(array('action','parent_id','message'), wp_get_referer());
        $redirect_url = add_query_arg('obenlo_modal', 'listing_saved', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    // ────────────────────────────────────────────────────────────
    // DELETE HANDLER
    // ────────────────────────────────────────────────────────────
    public function handle_delete_listing()
    {
        if (!is_user_logged_in()) obenlo_redirect_with_error('unauthorized');
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        if (!$listing_id) obenlo_redirect_with_error('invalid_listing');
        check_admin_referer('obenlo_delete_listing_' . $listing_id);
        $post = get_post($listing_id);
        if (!$post || ($post->post_author != get_current_user_id() && !current_user_can('administrator'))) { obenlo_redirect_with_error('unauthorized'); }
        $children = get_posts(array('post_type' => 'listing', 'post_parent' => $listing_id, 'posts_per_page' => -1, 'fields' => 'ids'));
        foreach ($children as $child_id) { wp_delete_post($child_id, true); }
        wp_delete_post($listing_id, true);
        wp_safe_redirect(add_query_arg(array('action' => 'list', 'message' => 'deleted'), home_url('/host-dashboard')));
        exit;
    }
}
