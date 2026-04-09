<?php
/**
 * Post Types & Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Post_Types {

    public function init() {
        add_action( 'init', array( $this, 'register_custom_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        
        // Demo Listing Configuration Meta Box
        add_action( 'add_meta_boxes', array( $this, 'add_demo_meta_box' ) );
        add_action( 'save_post_listing', array( $this, 'save_demo_meta_box' ) );

        // Suspension Filtering
        add_action( 'pre_get_posts', array( $this, 'hide_suspended_listings' ) );
        add_action( 'pre_get_users', array( $this, 'hide_suspended_hosts' ) );
        add_action( 'template_redirect', array( $this, 'block_suspended_single_listing' ) );
    }

    public function register_custom_post_types() {
        // 1. Listing CPT (Hierarchical to support parent/child)
        $listing_labels = array(
            'name'                  => _x( 'Listings', 'Post Type General Name', 'obenlo-booking' ),
            'singular_name'         => _x( 'Listing', 'Post Type Singular Name', 'obenlo-booking' ),
            'menu_name'             => __( 'Listings', 'obenlo-booking' ),
            'name_admin_bar'        => __( 'Listing', 'obenlo-booking' ),
            'archives'              => __( 'Listing Archives', 'obenlo-booking' ),
            'attributes'            => __( 'Listing Attributes', 'obenlo-booking' ),
            'parent_item_colon'     => __( 'Parent Listing:', 'obenlo-booking' ),
            'all_items'             => __( 'All Listings', 'obenlo-booking' ),
            'add_new_item'          => __( 'Add New Listing', 'obenlo-booking' ),
            'add_new'               => __( 'Add New', 'obenlo-booking' ),
            'new_item'              => __( 'New Listing', 'obenlo-booking' ),
            'edit_item'             => __( 'Edit Listing', 'obenlo-booking' ),
            'update_item'           => __( 'Update Listing', 'obenlo-booking' ),
            'view_item'             => __( 'View Listing', 'obenlo-booking' ),
            'view_items'            => __( 'View Listings', 'obenlo-booking' ),
            'search_items'          => __( 'Search Listing', 'obenlo-booking' ),
        );
        $listing_args = array(
            'label'                 => __( 'Listing', 'obenlo-booking' ),
            'labels'                => $listing_labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'comments' ),
            'hierarchical'          => true, // Enable Parent/Child logic
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-building',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => 'listings',
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => array( 'listing', 'listings' ), // Custom capabilities
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
        );
        register_post_type( 'listing', $listing_args );

        // 2. Booking CPT
        $booking_labels = array(
            'name'                  => _x( 'Bookings', 'Post Type General Name', 'obenlo-booking' ),
            'singular_name'         => _x( 'Booking', 'Post Type Singular Name', 'obenlo-booking' ),
            'menu_name'             => __( 'Bookings', 'obenlo-booking' ),
            'name_admin_bar'        => __( 'Booking', 'obenlo-booking' ),
            'all_items'             => __( 'All Bookings', 'obenlo-booking' ),
            'add_new_item'          => __( 'Add New Booking', 'obenlo-booking' ),
            'add_new'               => __( 'Add New', 'obenlo-booking' ),
            'new_item'              => __( 'New Booking', 'obenlo-booking' ),
            'edit_item'             => __( 'Edit Booking', 'obenlo-booking' ),
            'update_item'           => __( 'Update Booking', 'obenlo-booking' ),
            'view_item'             => __( 'View Booking', 'obenlo-booking' ),
            'search_items'          => __( 'Search Booking', 'obenlo-booking' ),
        );
        $booking_args = array(
            'label'                 => __( 'Booking', 'obenlo-booking' ),
            'labels'                => $booking_labels,
            'supports'              => array( 'title' ),
            'hierarchical'          => false,
            'public'                => false, // Internal post type
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 6,
            'menu_icon'             => 'dashicons-calendar-alt',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => array( 'booking', 'bookings' ),
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
        );
        register_post_type( 'booking', $booking_args );

        // 3. Ticket CPT (Support & Disputes)
        register_post_type( 'ticket', array(
            'label'           => __( 'Ticket', 'obenlo-booking' ),
            'labels'          => array( 'name' => 'Tickets', 'singular_name' => 'Ticket' ),
            'supports'        => array( 'title', 'editor', 'comments' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-sos',
            'capability_type' => 'post',
            'has_archive'     => false,
            'show_in_rest'    => true,
        ) );

        // 4. Broadcast CPT (Admin messages)
        register_post_type( 'broadcast', array(
            'label'           => __( 'Broadcast', 'obenlo-booking' ),
            'labels'          => array( 'name' => 'Broadcasts', 'singular_name' => 'Broadcast' ),
            'supports'        => array( 'title', 'editor' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-megaphone',
            'capability_type' => 'post',
            'has_archive'     => false,
            'show_in_rest'    => true,
        ) );

        // 5. Message CPT (P2P & Admin-User chats)
        register_post_type( 'obenlo_message', array(
            'label'           => __( 'Messages', 'obenlo-booking' ),
            'labels'          => array( 'name' => 'Messages', 'singular_name' => 'Message' ),
            'supports'        => array( 'title', 'editor', 'author' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-email-alt',
            'capability_type' => 'post',
            'has_archive'     => false,
            'show_in_rest'    => true,
        ) );

        // 6. Testimony CPT
        register_post_type( 'testimony', array(
            'label'           => __( 'Testimony', 'obenlo-booking' ),
            'labels'          => array( 
                'name' => 'Testimonies', 
                'singular_name' => 'Testimony',
                'add_new' => 'Add New Testimony',
                'add_new_item' => 'Add New Testimony',
                'edit_item' => 'Edit Testimony',
                'view_item' => 'View Testimony'
            ),
            'supports'        => array( 'title', 'editor', 'author', 'thumbnail' ),
            'public'          => true, // Publicly visible for queries
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-heart', // Heart icon for testimonials
            'capability_type' => 'post',
            'has_archive'     => false,
            'show_in_rest'    => true,
            'exclude_from_search' => true,
        ) );

        // 7. Refund CPT (Request & History)
        register_post_type( 'refund', array(
            'label'           => __( 'Refund', 'obenlo-booking' ),
            'labels'          => array( 
                'name' => 'Refunds', 
                'singular_name' => 'Refund',
                'add_new' => 'Add New Refund',
                'add_new_item' => 'Add New Refund Request',
                'edit_item' => 'Edit Refund',
                'view_item' => 'View Refund'
            ),
            'supports'        => array( 'title', 'editor', 'author' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'menu_icon'       => 'dashicons-undo',
            'capability_type' => 'post',
            'has_archive'     => false,
            'show_in_rest'    => true,
        ) );
    }

    public function register_taxonomies() {
        // Taxonomy: Listing Type (Stay, Experience, Service)
        $type_labels = array(
            'name'                       => _x( 'Listing Types', 'Taxonomy General Name', 'obenlo-booking' ),
            'singular_name'              => _x( 'Listing Type', 'Taxonomy Singular Name', 'obenlo-booking' ),
            'menu_name'                  => __( 'Types', 'obenlo-booking' ),
            'all_items'                  => __( 'All Types', 'obenlo-booking' ),
            'parent_item'                => __( 'Parent Type', 'obenlo-booking' ),
            'parent_item_colon'          => __( 'Parent Type:', 'obenlo-booking' ),
            'new_item_name'              => __( 'New Type Name', 'obenlo-booking' ),
            'add_new_item'               => __( 'Add New Type', 'obenlo-booking' ),
            'edit_item'                  => __( 'Edit Type', 'obenlo-booking' ),
            'update_item'                => __( 'Update Type', 'obenlo-booking' ),
            'view_item'                  => __( 'View Type', 'obenlo-booking' ),
            'separate_items_with_commas' => __( 'Separate types with commas', 'obenlo-booking' ),
            'add_or_remove_items'        => __( 'Add or remove types', 'obenlo-booking' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'obenlo-booking' ),
            'popular_items'              => __( 'Popular Types', 'obenlo-booking' ),
            'search_items'               => __( 'Search Types', 'obenlo-booking' ),
            'not_found'                  => __( 'Not Found', 'obenlo-booking' ),
        );
        $type_args = array(
            'labels'                     => $type_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rewrite'                    => array( 'slug' => 'type', 'with_front' => false ),
        );
        register_taxonomy( 'listing_type', array( 'listing' ), $type_args );
        flush_rewrite_rules();

        // Taxonomy: Listing Amenities
        $amenity_labels = array(
            'name'                       => _x( 'Amenities', 'Taxonomy General Name', 'obenlo-booking' ),
            'singular_name'              => _x( 'Amenity', 'Taxonomy Singular Name', 'obenlo-booking' ),
            'menu_name'                  => __( 'Amenities', 'obenlo-booking' ),
            'all_items'                  => __( 'All Amenities', 'obenlo-booking' ),
            'new_item_name'              => __( 'New Amenity Name', 'obenlo-booking' ),
            'add_new_item'               => __( 'Add New Amenity', 'obenlo-booking' ),
            'edit_item'                  => __( 'Edit Amenity', 'obenlo-booking' ),
            'update_item'                => __( 'Update Amenity', 'obenlo-booking' ),
        );
        $amenity_args = array(
            'labels'                     => $amenity_labels,
            'hierarchical'               => false, // Checkboxes/Tags
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        register_taxonomy( 'listing_amenity', array( 'listing' ), $amenity_args );

        // Seed default terms
        $this->seed_default_terms();
    }

    private function seed_default_terms() {
        // Parent categories
        $parent_types = array( 'Stay', 'Experience', 'Service', 'Event' );
        $parent_ids = array();
        foreach ( $parent_types as $type ) {
            if ( ! term_exists( $type, 'listing_type' ) ) {
                $result = wp_insert_term( $type, 'listing_type' );
                $parent_ids[ strtolower($type) ] = is_array($result) ? $result['term_id'] : 0;
            } else {
                $term = get_term_by( 'name', $type, 'listing_type' );
                $parent_ids[ strtolower($type) ] = $term ? $term->term_id : 0;
            }
        }

        // Sub-types: name => parent_key
        $sub_types = array(
            'Hotel'              => 'stay',
            'Guest House'        => 'stay',
            'Chauffeur'          => 'service',
            'Driver'             => 'service',
            'Cook'               => 'service',
            'Barbershop'         => 'service',
            'Hairdresser'        => 'service',
            'Concierge'          => 'service',
            'Personal Assistant' => 'service',
            'Babysitter'         => 'service',
            'Dogsitter'          => 'service',
            'Tour'               => 'experience',
            'Food Tasting'       => 'experience',
            'Photo Shoot'        => 'experience',
            'Show'               => 'event',
            'Class'              => 'event',
            'Celebration'        => 'event',
            'Donation & Giving'  => 'event',
            'Cleaning'           => 'service',
            'Barber'             => 'service',
            'Handyman'           => 'service',
            'Freelance'          => 'service',
            'Photographer'       => 'service',
            'Delivery'           => 'service',
        );

        foreach ( $sub_types as $name => $parent_key ) {
            if ( ! term_exists( $name, 'listing_type' ) ) {
                $parent_id = isset( $parent_ids[ $parent_key ] ) ? $parent_ids[ $parent_key ] : 0;
                wp_insert_term( $name, 'listing_type', array( 'parent' => $parent_id ) );
            }
        }
    }

    public function add_demo_meta_box() {
        add_meta_box(
            'obenlo_demo_listing_meta',
            __( 'Demo Listing Configuration', 'obenlo-booking' ),
            array( $this, 'render_demo_meta_box' ),
            'listing',
            'side',
            'default'
        );
    }

    public function render_demo_meta_box( $post ) {
        wp_nonce_field( 'obenlo_demo_meta_save', 'obenlo_demo_meta_nonce' );
        
        $is_demo = get_post_meta( $post->ID, '_obenlo_is_demo', true );
        $d_name = get_post_meta( $post->ID, '_obenlo_demo_host_name', true );
        $d_bio = get_post_meta( $post->ID, '_obenlo_demo_host_bio', true );
        $d_loc = get_post_meta( $post->ID, '_obenlo_demo_host_location', true );
        $d_tag = get_post_meta( $post->ID, '_obenlo_demo_host_tagline', true );

        echo '<p>';
        echo '<label><input type="checkbox" name="_obenlo_is_demo" value="yes" ' . checked( $is_demo, 'yes', false ) . '> <strong>Is this a Demo Listing?</strong></label>';
        echo '</p>';
        
        echo '<div style="margin-top:10px; border-top:1px solid #ddd; padding-top:10px;">';
        echo '<p><strong>Demo Host Details:</strong><br><small>Will be transferred to the real host later.</small></p>';
        
        echo '<p><label>Host Name:<br>';
        echo '<input type="text" name="_obenlo_demo_host_name" value="' . esc_attr( $d_name ) . '" style="width:100%;">';
        echo '</label></p>';

        echo '<p><label>Host Tagline:<br>';
        echo '<input type="text" name="_obenlo_demo_host_tagline" value="' . esc_attr( $d_tag ) . '" style="width:100%;">';
        echo '</label></p>';

        echo '<p><label>Host Location:<br>';
        echo '<input type="text" name="_obenlo_demo_host_location" value="' . esc_attr( $d_loc ) . '" style="width:100%;">';
        echo '</label></p>';

        echo '<p><label>Host Bio:<br>';
        echo '<textarea name="_obenlo_demo_host_bio" rows="4" style="width:100%;">' . esc_textarea( $d_bio ) . '</textarea>';
        echo '</label></p>';
        echo '</div>';
    }

    public function save_demo_meta_box( $post_id ) {
        if ( ! isset( $_POST['obenlo_demo_meta_nonce'] ) || ! wp_verify_nonce( $_POST['obenlo_demo_meta_nonce'], 'obenlo_demo_meta_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['_obenlo_is_demo'] ) && $_POST['_obenlo_is_demo'] === 'yes' ) {
            update_post_meta( $post_id, '_obenlo_is_demo', 'yes' );
            update_post_meta( $post_id, '_obenlo_demo_host_name', sanitize_text_field( $_POST['_obenlo_demo_host_name'] ) );
            update_post_meta( $post_id, '_obenlo_demo_host_tagline', sanitize_text_field( $_POST['_obenlo_demo_host_tagline'] ) );
            update_post_meta( $post_id, '_obenlo_demo_host_location', sanitize_text_field( $_POST['_obenlo_demo_host_location'] ) );
            update_post_meta( $post_id, '_obenlo_demo_host_bio', sanitize_textarea_field( wp_unslash( $_POST['_obenlo_demo_host_bio'] ) ) );
        } else {
            delete_post_meta( $post_id, '_obenlo_is_demo' );
            delete_post_meta( $post_id, '_obenlo_demo_host_name' );
            delete_post_meta( $post_id, '_obenlo_demo_host_tagline' );
            delete_post_meta( $post_id, '_obenlo_demo_host_location' );
            delete_post_meta( $post_id, '_obenlo_demo_host_bio' );
        }
    }

    public function block_suspended_single_listing() {
        if ( is_singular('listing') ) {
            global $post;
            $is_listing_suspended = get_post_meta( $post->ID, '_obenlo_is_suspended', true ) === 'yes';
            $is_author_suspended  = get_user_meta( $post->post_author, '_obenlo_is_suspended', true ) === 'yes';

            if ( $is_listing_suspended || $is_author_suspended ) {
                if ( current_user_can('administrator') ) return;
                if ( is_user_logged_in() && $post->post_author == get_current_user_id() ) return;
                
                wp_die( 'This listing is temporarily unavailable.', 'Listing Unavailable', array('response' => 404) );
            }
        } elseif ( is_author() ) {
            $author = get_queried_object();
            if ( $author && isset($author->ID) && get_user_meta( $author->ID, '_obenlo_is_suspended', true ) === 'yes' ) {
                if ( current_user_can('administrator') ) return;
                if ( is_user_logged_in() && $author->ID == get_current_user_id() ) return;
                
                wp_die( 'This profile is temporarily unavailable.', 'Profile Unavailable', array('response' => 404) );
            }
        }
    }

    public function hide_suspended_listings( $query ) {
        if ( is_admin() ) return;
        
        $pt = $query->get('post_type');
        $is_listing_query = ( $pt === 'listing' ) || ( is_array($pt) && in_array('listing', $pt) ) || is_post_type_archive('listing') || is_tax('listing_type') || is_tax('listing_amenity');
        if ( ! $is_listing_query ) return;

        if ( $query->is_singular('listing') ) return;
        if ( current_user_can('administrator') ) return;

        $user_id = get_current_user_id();
        if ( $user_id && $query->get('author') == $user_id ) return;

        // Exclude suspended hosts logic
        global $wpdb;
        $suspended_hosts = $wpdb->get_col( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_obenlo_is_suspended' AND meta_value = 'yes'" );
        if ( !empty($suspended_hosts) ) {
            $author_not_in = (array) $query->get('author__not_in');
            $author_not_in = array_unique(array_merge($author_not_in, $suspended_hosts));
            $query->set('author__not_in', $author_not_in);
        }

        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_obenlo_is_suspended',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_obenlo_is_suspended',
                'value' => 'yes',
                'compare' => '!='
            )
        );
        $query->set('meta_query', $meta_query);
    }

    public function hide_suspended_hosts( $query ) {
        if ( is_admin() ) return;
        if ( current_user_can('administrator') ) return;

        $user_id = get_current_user_id();
        $includes = $query->get('include');
        if ( $user_id && !empty($includes) && in_array($user_id, (array)$includes) ) return;
        
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_obenlo_is_suspended',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_obenlo_is_suspended',
                'value' => 'yes',
                'compare' => '!='
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
