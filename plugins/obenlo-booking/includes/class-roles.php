<?php
/**
 * User Roles and Capabilities
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Roles {

    public function init() {
        // Run role cleanup and dashboard widget only in admin
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'remove_unused_roles' ) );
            add_action( 'wp_dashboard_setup', array( $this, 'add_admin_dashboard_widget' ) );

            // Ensure our specific roles exist
            if ( ! get_role( 'guest' ) || ! get_role( 'host' ) || ! get_role( 'support_agent' ) ) {
                $this->add_roles();
            }
        }
    }


    public function add_roles() {
        // Clone capabilities from standard subscriber for Guest role
        $subscriber = get_role( 'subscriber' );
        $capabilities = $subscriber ? $subscriber->capabilities : array( 'read' => true );

        // Add Guest role
        add_role(
            'guest',
            __( 'Guest', 'obenlo-booking' ),
            $capabilities
        );

        // Add custom capabilities for listings to Host role
        $host_caps = array(
            'edit_listing'           => true,
            'read_listing'           => true,
            'delete_listing'         => true,
            'edit_listings'          => true,
            'edit_others_listings'   => false,
            'publish_listings'       => true,
            'read_private_listings'  => true,
            'delete_listings'        => true,
            'delete_private_listings'=> true,
            'delete_published_listings'=> true,
            'delete_others_listings' => false,
            'edit_private_listings'  => true,
            'edit_published_listings'=> true,
            
            'edit_booking'           => true,
            'read_booking'           => true,
            'delete_booking'         => false,
            'edit_bookings'          => true,
            'edit_others_bookings'   => false,
            'publish_bookings'       => false,
            'read_private_bookings'  => true,
            
            'upload_files'           => true,
        );

        $host_capabilities = array_merge( $capabilities, $host_caps );

        // Add Host role
        add_role(
            'host',
            __( 'Host', 'obenlo-booking' ),
            $host_capabilities
        );

        // Add Support Agent role
        $agent_caps = array(
            'read'                   => true,
            'upload_files'           => true,
            'manage_support'         => true,
            'read_private_posts'     => true, // To see tickets/messages
        );
        add_role(
            'support_agent',
            __( 'Support Agent', 'obenlo-booking' ),
            $agent_caps
        );

        // Administrator should have all capabilities
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            foreach ( $host_caps as $cap => $val ) {
                $admin->add_cap( $cap );
            }
            $admin->add_cap( 'manage_support' );
        }
    }

    /**
     * Remove standard WordPress roles that aren't needed for Obenlo
     */
    public function remove_unused_roles() {
        // Only run this occasionally or check if roles exist to be safe
        $roles_to_hide = array( 'editor', 'author', 'contributor' );
        foreach ( $roles_to_hide as $role ) {
            if ( get_role( $role ) ) {
                remove_role( $role );
            }
        }
    }

    /**
     * Add a dashboard widget for administrators to see platform stats
     */
    public function add_admin_dashboard_widget() {
        if ( current_user_can( 'administrator' ) ) {
            wp_add_dashboard_widget(
                'obenlo_admin_stats_widget',
                'Obenlo Platform Statistics',
                array( $this, 'render_admin_dashboard_widget' )
            );
        }
    }

    /**
     * Render the admin dashboard widget content
     */
    public function render_admin_dashboard_widget() {
        $user_counts = count_users();
        $total_users = $user_counts['total_users'];
        $avail_roles = $user_counts['avail_roles'];

        $hosts = isset( $avail_roles['host'] ) ? $avail_roles['host'] : 0;
        $guests = isset( $avail_roles['guest'] ) ? $avail_roles['guest'] : 0;
        $admins = isset( $avail_roles['administrator'] ) ? $avail_roles['administrator'] : 0;

        echo '<div style="display: flex; flex-direction: column; gap: 15px;">';
        echo '<p style="font-size: 1.1em; margin: 0;">Welcome back! Here is a summary of the Obenlo community:</p>';
        
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">';
        
        echo '<div style="background: #f0f0f1; padding: 15px; border-radius: 8px; text-align: center;">';
        echo '<span style="display: block; font-size: 2em; font-weight: bold; color: #e61e4d;">' . intval($hosts) . '</span>';
        echo '<span style="font-weight: bold;">Hosts</span>';
        echo '</div>';

        echo '<div style="background: #f0f0f1; padding: 15px; border-radius: 8px; text-align: center;">';
        echo '<span style="display: block; font-size: 2em; font-weight: bold; color: #333;">' . intval($guests) . '</span>';
        echo '<span style="font-weight: bold;">Guests</span>';
        echo '</div>';

        echo '</div>';

        echo '<p style="color: #666; font-size: 0.9em; margin: 0;">Total registered users: <strong>' . intval($total_users) . '</strong> (including ' . intval($admins) . ' Admins)</p>';
        echo '<a href="' . admin_url( 'users.php' ) . '" class="button button-primary" style="background: #e61e4d; border-color: #e61e4d; align-self: flex-start;">Manage Users</a>';
        echo '</div>';
    }
}
