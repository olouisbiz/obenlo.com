<?php
/**
 * Obenlo Sync Handler
 * Handles incoming data from Google Sheets / Forms to automate host & listing creation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Sync {

    private $namespace = 'obenlo/v1';
    private $resource_name = 'sync-form';
    private $api_key = 'OBENLO_SYNC_SECRET_1409'; // User should change this

    public function init() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_sync'),
            'permission_callback' => array($this, 'check_api_key'),
        ));
    }

    public function check_api_key($request) {
        $header = $request->get_header('X-Obenlo-Token');
        if (empty($header) || $header !== $this->api_key) {
            return new WP_Error('unauthorized', 'Invalid API Token', array('status' => 403));
        }
        return true;
    }

    public function handle_sync($request) {
        $params = $request->get_json_params();

        if (empty($params['email']) || empty($params['store_name']) || empty($params['listing_title'])) {
            return new WP_Error('missing_data', 'Email, Store Name, and Listing Title are required.', array('status' => 400));
        }

        $email = sanitize_email($params['email']);
        $user_id = email_exists($email);
        $new_user = false;

        // 1. Handle User / Host Creation
        if (!$user_id) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', strstr($email, '@', true)));
            // Ensure unique username
            $base_username = $username;
            $count = 1;
            while (username_exists($username)) {
                $username = $base_username . $count;
                $count++;
            }

            $user_id = wp_create_user($username, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            
            $user = new WP_User($user_id);
            $user->set_role('host');
            $new_user = true;
        }

        // 2. Update User Meta (Storefront)
        update_user_meta($user_id, 'obenlo_store_name', sanitize_text_field($params['store_name']));
        update_user_meta($user_id, 'obenlo_store_tagline', sanitize_text_field($params['store_tagline'] ?? ''));
        update_user_meta($user_id, 'obenlo_store_description', sanitize_textarea_field($params['store_description'] ?? ''));
        update_user_meta($user_id, 'obenlo_store_location', sanitize_text_field($params['store_location'] ?? ''));
        update_user_meta($user_id, 'obenlo_specialties', sanitize_text_field($params['specialties'] ?? ''));
        update_user_meta($user_id, 'obenlo_instagram', sanitize_text_field($params['social_insta'] ?? ''));
        update_user_meta($user_id, 'obenlo_facebook', sanitize_text_field($params['social_fb'] ?? ''));
        update_user_meta($user_id, 'obenlo_store_video', esc_url_raw($params['store_video'] ?? ''));

        // 3. Create Listing
        $is_demo = !empty($params['is_demo']) && ($params['is_demo'] === true || $params['is_demo'] === 'true' || $params['is_demo'] === 'yes');
        
        $listing_data = array(
            'post_title'   => sanitize_text_field($params['listing_title']),
            'post_content' => wp_kses_post($params['listing_content'] ?? ''),
            'post_status'  => $is_demo ? 'publish' : 'pending', // Demo listings are published to "look active"
            'post_type'    => 'listing',
            'post_author'  => $user_id,
        );

        $listing_id = wp_insert_post($listing_data);

        if (is_wp_error($listing_id)) {
            error_log('Obenlo Sync Error: Failed to insert post. ' . $listing_id->get_error_message());
            return $listing_id;
        }

        // 4. Update Listing Meta
        update_post_meta($listing_id, '_obenlo_price', sanitize_text_field($params['listing_price'] ?? '0'));
        update_post_meta($listing_id, '_obenlo_capacity', sanitize_text_field($params['listing_capacity'] ?? ''));
        update_post_meta($listing_id, '_obenlo_location', sanitize_text_field($params['listing_address'] ?? ''));
        update_post_meta($listing_id, '_obenlo_pricing_model', sanitize_text_field($params['pricing_model'] ?? 'per_night'));
        update_post_meta($listing_id, '_obenlo_listing_country', sanitize_text_field($params['listing_country'] ?? 'haiti'));

        if ($is_demo) {
            update_post_meta($listing_id, '_obenlo_is_demo', 'yes');
            // Copy storefront info to Demo Meta fields so the frontend picks them up correctly
            update_post_meta($listing_id, '_obenlo_demo_host_name', sanitize_text_field($params['store_name']));
            update_post_meta($listing_id, '_obenlo_demo_host_bio', sanitize_textarea_field($params['store_description'] ?? ''));
            update_post_meta($listing_id, '_obenlo_demo_host_location', sanitize_text_field($params['store_location'] ?? ''));
            update_post_meta($listing_id, '_obenlo_demo_host_tagline', sanitize_text_field($params['store_tagline'] ?? ''));
            update_post_meta($listing_id, '_obenlo_demo_host_instagram', sanitize_text_field($params['social_insta'] ?? ''));
            update_post_meta($listing_id, '_obenlo_demo_host_facebook', sanitize_text_field($params['social_fb'] ?? ''));
        }

        // 5. Handle Taxonomy (Category)
        if (!empty($params['category_slug'])) {
            $term = get_term_by('slug', sanitize_title($params['category_slug']), 'listing_type');
            if ($term) {
                wp_set_post_terms($listing_id, array($term->term_id), 'listing_type');
            }
        }

        // 6. Handle Amenities
        if (!empty($params['amenities'])) {
            $amenities = is_array($params['amenities']) ? $params['amenities'] : explode(',', $params['amenities']);
            $amenity_ids = array();
            foreach ($amenities as $amenity) {
                $name = trim(sanitize_text_field($amenity));
                if (empty($name)) continue;
                
                $term = term_exists($name, 'listing_amenity');
                if (!$term) {
                    $term = wp_insert_term($name, 'listing_amenity');
                }
                if (!is_wp_error($term) && isset($term['term_id'])) {
                    $amenity_ids[] = intval($term['term_id']);
                }
            }
            wp_set_post_terms($listing_id, $amenity_ids, 'listing_amenity');
        }

        $demo_url = '';
        if ($is_demo) {
            $demo_url = home_url('/demo/' . sanitize_title($params['store_name']) . '/');
        }

        return array(
            'success'    => true,
            'user_id'    => $user_id,
            'listing_id' => $listing_id,
            'is_new_user' => $new_user,
            'demo_url'   => $demo_url,
            'message'    => 'Host and Listing synced successfully.'
        );
    }
}
