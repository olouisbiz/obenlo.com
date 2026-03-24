<?php
/**
 * Host Verification Logic - Obenlo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Host_Verification {

    public function init() {
        // Handle document uploads
        add_action( 'wp_ajax_obenlo_upload_id', array( $this, 'handle_id_upload' ) );
        
        // Admin status updates
        add_action( 'admin_post_obenlo_update_host_status', array( $this, 'handle_admin_status_update' ) );

        // Dynamic welcome trigger
        add_action( 'template_redirect', array( $this, 'trigger_welcome_email' ) );
    }

    /**
     * Handle identity document upload via AJAX
     */
    public function handle_id_upload() {
        check_ajax_referer( 'obenlo_onboarding_nonce', 'security' );

        if ( ! is_user_logged_in() || ! empty( $_FILES['id_document'] ) ) {
            // Use WordPress media handle for secure upload
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attachment_id = media_handle_upload( 'id_document', 0 );

            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error( $attachment_id->get_error_message() );
            }

            $user_id = get_current_user_id();
            update_user_meta( $user_id, 'obenlo_verification_doc_id', $attachment_id );
            update_user_meta( $user_id, 'obenlo_host_verification_status', 'pending' );

            // Notify Admin
            $user = get_userdata($user_id);
            Obenlo_Booking_Notifications::send_to_admin(
                "New Verification Request: " . $user->display_name,
                "A host has uploaded an ID document for verification.\nHost: " . $user->display_name . "\nView in Admin Dashboard: " . home_url('/support-console/?tab=verifications')
            );

            wp_send_json_success( array( 'message' => 'Document uploaded successfully. Your verification is now pending.' ) );
        }

        wp_send_json_error( 'No file uploaded.' );
    }

    /**
     * Handle Admin status updates (Approve/Reject)
     */
    public function handle_admin_status_update() {
        if ( ! current_user_can( 'manage_options' ) ) {
            obenlo_redirect_with_error('unauthorized');
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

        if ( $user_id && in_array( $status, array( 'verified', 'rejected', 'pending' ) ) ) {
            update_user_meta( $user_id, 'obenlo_host_verification_status', $status );
            
            // Send Notification
            if ( $status === 'verified' ) {
                Obenlo_Booking_Notifications::notify_host_event( $user_id, 'host_verified' );
            } elseif ( $status === 'rejected' ) {
                Obenlo_Booking_Notifications::notify_host_event( $user_id, 'host_rejected' );
            }
        }

        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    /**
     * Trigger welcome email when host visits onboarding for the first time
     */
    public function trigger_welcome_email() {
        if ( ! is_user_logged_in() ) return;
        
        $user_id = get_current_user_id();
        if ( ! current_user_can( 'host' ) ) return;

        // Only on onboarding page
        if ( ! is_page_template( 'page-host-onboarding.php' ) ) {
            // Check by slug if template check fails (depending on how user creates page)
            global $post;
            if ( ! isset($post->post_name) || $post->post_name !== 'host-onboarding' ) {
                return;
            }
        }

        $sent = get_user_meta( $user_id, '_obenlo_welcome_email_sent', true );
        if ( ! $sent ) {
            Obenlo_Booking_Notifications::notify_host_event( $user_id, 'welcome_host' );
            update_user_meta( $user_id, '_obenlo_welcome_email_sent', '1' );
        }
    }

    /**
     * Helper to get verification status
     */
    public static function get_status( $user_id ) {
        return get_user_meta( $user_id, 'obenlo_host_verification_status', true ) ?: 'not_started';
    }
}
