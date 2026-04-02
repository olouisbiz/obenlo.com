<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_Admin_UI {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_social_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_obenlo_social_push', array( __CLASS__, 'handle_social_push' ) );
    }

    public static function add_social_meta_boxes() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        add_meta_box(
            'obenlo_social_meta_box',
            'Push to Social Media',
            array( __CLASS__, 'render_social_meta_box_content' ),
            array('listing', 'post'),
            'side',
            'high'
        );
    }

    public static function render_social_meta_box_content( $post ) {
        wp_nonce_field( 'obenlo_social_push_action', 'obenlo_social_push_nonce' );
        ?>
        <p>Manually push this <?php echo esc_html($post->post_type); ?> to Obenlo's Facebook Page & Instagram Account.</p>
        <button type="button" class="button button-primary button-large" id="obenlo-social-push-btn" data-post-id="<?php echo esc_attr($post->ID); ?>">
            Push to Social
        </button>
        <div id="obenlo-social-feedback" style="margin-top: 10px; font-weight: 600;"></div>
        <?php
    }

    public static function enqueue_admin_scripts( $hook ) {
        if ( ! current_user_can( 'manage_options' ) ) return;
        
        if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            wp_enqueue_script( 'obenlo-social-admin', OBENLO_SOCIAL_URL . 'assets/admin-social.js', array('jquery'), OBENLO_SOCIAL_VERSION, true );
            wp_localize_script( 'obenlo-social-admin', 'obenloSocialObj', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'obenlo_social_push_action' )
            ) );
        }
    }

    public static function handle_social_push() {
        check_ajax_referer( 'obenlo_social_push_action', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array('message' => 'Unauthorized access.') );
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) wp_send_json_error( array('message' => 'Invalid Post ID.') );

        $post = get_post( $post_id );
        $post_type = $post->post_type;

        $title = $post->post_title;
        $permalink = get_permalink( $post_id );
        $image_url = get_the_post_thumbnail_url( $post_id, 'large' );

        if ( empty($image_url) ) {
            wp_send_json_error( array('message' => 'Cannot push without a Featured Image!') );
        }

        $message = '';
        if ( $post_type === 'listing' ) {
            $price = get_post_meta( $post_id, '_obenlo_price', true );
            $location = get_post_meta( $post_id, '_obenlo_location', true );
            if(empty($location)) $location = 'Toronto';

            $template = get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust \${price}!");
            $message = str_replace( array('{title}', '\${price}', '{location}'), array($title, $price, $location), $template );
        } else {
            $excerpt = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( $post->post_content, 20 );
            $template = get_option('obenlo_social_post_template', "Latest on the Obenlo Blog:\n\n{title}\n\n{excerpt}");
            $message = str_replace( array('{title}', '{excerpt}'), array($title, $excerpt), $template );
        }

        $fb_push = Obenlo_Social_API_Client::push_to_facebook( $post_id, $message, $image_url, $permalink );
        $ig_push = Obenlo_Social_API_Client::push_to_instagram( $post_id, $message, $image_url );

        if ( is_wp_error( $fb_push ) && is_wp_error( $ig_push ) ) {
            wp_send_json_error( array('message' => 'Failed on both platforms: ' . $fb_push->get_error_message()) );
        }

        $success_msg = 'Successfully pushed to active platforms!';
        if ( is_wp_error( $fb_push ) ) $success_msg = 'Pushed to IG only (FB Failed).';
        if ( is_wp_error( $ig_push ) ) $success_msg = 'Pushed to FB only (IG Failed).';

        update_post_meta( $post_id, '_obenlo_social_last_pushed', current_time( 'mysql' ) );

        wp_send_json_success( array('message' => $success_msg) );
    }
}
