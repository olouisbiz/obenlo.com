<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_Admin_UI {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_social_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_social_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_social_scripts' ) );
    }

    public static function add_social_meta_boxes() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        add_meta_box(
            'obenlo_social_meta_box',
            'Share to Social Media',
            array( __CLASS__, 'render_social_meta_box_content' ),
            array('listing', 'post'),
            'side',
            'high'
        );
    }

    public static function render_social_meta_box_content( $post ) {
        $title = $post->post_title;
        $url = get_permalink($post->ID);
        $price = '';
        $location = '';
        
        if ($post->post_type === 'listing') {
            $price = get_post_meta($post->ID, '_obenlo_price', true);
            $location = get_post_meta($post->ID, '_obenlo_location', true);
            if(empty($location)) $location = 'Toronto';
            $template = get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!");
            $caption = str_replace( array('{title}', '${price}', '{location}'), array($title, $price, $location), $template );
        } else {
            $excerpt = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( $post->post_content, 20 );
            $template = get_option('obenlo_social_post_template', "Latest on Obenlo:\n\n{title}\n\n{excerpt}");
            $caption = str_replace( array('{title}', '{excerpt}'), array($title, $excerpt), $template );
        }

        $image = get_the_post_thumbnail_url($post->ID, 'large');
        $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url) . '&quote=' . urlencode($caption);
        ?>
        <p>Open the native share-sheet (mobile) or Facebook link (desktop).</p>
        <a href="<?php echo esc_url($share_url); ?>" target="_blank" class="button button-primary button-large obenlo-social-push-btn" 
            data-post-id="<?php echo esc_attr($post->ID); ?>"
            data-title="<?php echo esc_attr($title); ?>"
            data-url="<?php echo esc_url($url); ?>"
            data-price="<?php echo esc_attr($price); ?>"
            data-location="<?php echo esc_attr($location); ?>"
            data-excerpt="<?php echo esc_attr(isset($excerpt) ? $excerpt : ''); ?>"
            data-type="<?php echo esc_attr($post->post_type); ?>"
            data-image="<?php echo esc_url($image); ?>"
            style="width:100%; text-align:center;">
            Push to Social
        </a>
        <?php
    }

    public static function enqueue_social_scripts( $hook = '' ) {
        // Broaden the check: If user is admin, load the small sharing script on all admin pages
        // This ensures the custom "Obenlo Dash" and other admin areas are covered.
        if ( current_user_can( 'manage_options' ) ) {
            wp_enqueue_script( 'obenlo-social-admin', OBENLO_SOCIAL_URL . 'assets/admin-social.js', array('jquery'), OBENLO_SOCIAL_VERSION, true );
            
            wp_localize_script( 'obenlo-social-admin', 'obenloSocialObj', array(
                'listing_template' => get_option('obenlo_social_listing_template'),
                'post_template'    => get_option('obenlo_social_post_template'),
                'is_admin'         => true
            ) );
        }
    }
}
