<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_API_Client {

    public static function push_to_facebook( $post_id, $message, $image_url, $link_url ) {
        $page_id = get_option('obenlo_social_fb_page_id');
        $access_token = get_option('obenlo_social_fb_access_token');

        if ( empty($page_id) || empty($access_token) ) {
            return new WP_Error('missing_keys', 'Facebook Page ID or Access Token is missing.');
        }

        /**
         * Step 1: Push Photo to Page
         */
        $endpoint = "https://graph.facebook.com/v19.0/{$page_id}/photos";
        
        $body = array(
            'url'          => $image_url,
            'caption'      => $message . "\n\n" . $link_url, // Facebook "message" is often "caption" for photo endpoint
            'access_token' => $access_token
        );

        $response = wp_remote_post( $endpoint, array(
            'body'    => $body,
            'timeout' => 20
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset($body['error']) ) {
            return new WP_Error('api_error', $body['error']['message']);
        }

        return $body['id'];
    }

    public static function push_to_instagram( $post_id, $message, $image_url ) {
        $ig_user_id = get_option('obenlo_social_ig_user_id');
        $access_token = get_option('obenlo_social_fb_access_token');

        if ( empty($ig_user_id) || empty($access_token) ) {
            return new WP_Error('missing_keys', 'Instagram User ID or Access Token is missing.');
        }

        // Step 1: Create Media Container
        $container_endpoint = "https://graph.facebook.com/v19.0/{$ig_user_id}/media";
        $container_response = wp_remote_post( $container_endpoint, array(
            'body' => array(
                'image_url'    => $image_url,
                'caption'      => $message,
                'access_token' => $access_token
            ),
            'timeout' => 20
        ) );

        if ( is_wp_error( $container_response ) ) return $container_response;
        
        $container_body = json_decode( wp_remote_retrieve_body( $container_response ), true );
        if ( isset($container_body['error']) ) {
            return new WP_Error('api_error', 'IG Media Error: ' . $container_body['error']['message']);
        }

        $creation_id = $container_body['id'];

        // Step 2: Publish Container
        $publish_endpoint = "https://graph.facebook.com/v19.0/{$ig_user_id}/media_publish";
        $publish_response = wp_remote_post( $publish_endpoint, array(
            'body' => array(
                'creation_id'  => $creation_id,
                'access_token' => $access_token
            ),
            'timeout' => 20
        ) );

        if ( is_wp_error( $publish_response ) ) return $publish_response;
        
        $publish_body = json_decode( wp_remote_retrieve_body( $publish_response ), true );
        if ( isset($publish_body['error']) ) {
            return new WP_Error('api_error', 'IG Publish Error: ' . $publish_body['error']['message']);
        }

        return $publish_body['id'];
    }
}
