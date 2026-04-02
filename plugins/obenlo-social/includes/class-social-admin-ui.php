<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Obenlo_Social_Admin_UI {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_social_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_social_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_social_scripts' ) );
        add_action( 'admin_footer', array( __CLASS__, 'render_social_picker_html' ) );
        add_action( 'wp_footer', array( __CLASS__, 'render_social_picker_html' ) );
        add_action( 'wp_head', array( __CLASS__, 'add_viewport_meta' ), 1 );
    }

    public static function add_viewport_meta() {
        if ( current_user_can( 'edit_posts' ) ) {
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        }
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
        if ( current_user_can( 'edit_posts' ) ) {
            wp_enqueue_script( 'obenlo-social-admin', OBENLO_SOCIAL_URL . 'assets/admin-social.js', array('jquery'), time(), true );
            
            wp_localize_script( 'obenlo-social-admin', 'obenloSocialObj', array(
                'listing_template' => get_option('obenlo_social_listing_template'),
                'post_template'    => get_option('obenlo_social_post_template'),
                'is_admin'         => true
            ) );
        }
    }

    public static function render_social_picker_html() {
        if ( ! current_user_can( 'edit_posts' ) ) return;
        ?>
        <div id="obenlo-social-picker-overlay" style="display:none; position:fixed; top:0; left:0; width:100% !important; height:100% !important; background:rgba(0,0,0,0.7) !important; z-index:9999998 !important;"></div>
        <div id="obenlo-social-picker" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%) !important; width:90% !important; max-width:360px !important; background:#ffffff !important; border-radius:24px !important; box-shadow:0 30px 100px rgba(0,0,0,0.6) !important; z-index:9999999 !important; padding:30px; border:4px solid #e61e4d !important; font-family:sans-serif !important; text-align:center;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <strong style="color:#e61e4d; font-size:24px;">Share Listing</strong>
                <span id="obenlo-close-picker" style="cursor:pointer; color:#999; font-size:40px; line-height:0.5; padding:10px;">&times;</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:18px;">
                <a id="share-to-fb" href="#" target="_blank" style="padding:20px; border-radius:15px; background:#1877f2; color:#fff; text-decoration:none; text-align:center; font-weight:800; font-size:18px; display:block;">Post to Facebook</a>
                <button id="share-to-ig" style="padding:20px; border-radius:15px; background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color:#fff; border:none; cursor:pointer; font-weight:800; font-size:18px; width:100%;">Instagram Feed / Stories</button>
                <div style="margin-top:10px;">
                    <button id="share-to-native" style="padding:15px; border-radius:12px; background:#f4f4f4; color:#333; border:1px solid #ddd; cursor:pointer; font-weight:600; font-size:14px; width:100%;">Share via Other Apps</button>
                </div>
            </div>
        </div>
        <?php
    }
}
