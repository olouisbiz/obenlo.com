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
            
            $template = get_option('obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!");
            $caption = str_replace( array('{title}', '${price}', '{location}'), array($title, $price, $location), $template );
        } else {
            $excerpt = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( $post->post_content, 20 );
            $template = get_option('obenlo_social_post_template', "Latest on Obenlo:\n\n{title}\n\n{excerpt}");
            $caption = str_replace( array('{title}', '{excerpt}'), array($title, $excerpt), $template );
        }

        $image = get_the_post_thumbnail_url($post->ID, 'large');
        $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);
        ?>
        <p>Open the social sharing dashboard.</p>
        <a href="<?php echo esc_url($share_url); ?>" class="button button-primary button-large obenlo-social-push-btn" 
            data-post-id="<?php echo esc_attr($post->ID); ?>"
            data-title="<?php echo esc_attr($title); ?>"
            data-url="<?php echo esc_url($url); ?>"
            data-price="<?php echo esc_attr($price); ?>"
            data-location="<?php echo esc_attr($location); ?>"
            data-excerpt="<?php echo esc_attr(isset($excerpt) ? $excerpt : ''); ?>"
            data-type="<?php echo esc_attr($post->post_type); ?>"
            data-image="<?php echo esc_url($image); ?>"
            data-caption="<?php echo esc_attr($caption); ?>"
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
        <div id="obenlo-social-picker" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%) !important; width:90% !important; max-width:380px !important; background:#ffffff !important; border-radius:30px !important; box-shadow:0 30px 100px rgba(0,0,0,0.6) !important; z-index:9999999 !important; padding:24px; border:2px solid #e0e0e0 !important; font-family:sans-serif !important; text-align:center;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <strong style="color:#e61e4d; font-size:22px;">Obenlo Social</strong>
                <span id="obenlo-close-picker" style="cursor:pointer; color:#999; font-size:40px; line-height:0.5; padding:10px;">&times;</span>
            </div>

            <!-- Dynamic Caption Editor -->
            <div style="margin-bottom:15px; text-align:left;">
                <label style="font-size:12px; font-weight:700; color:#666; margin-bottom:5px; display:block;">Smart Caption</label>
                <textarea id="obenlo-social-caption-edit" style="width:100%; height:90px; border-radius:12px; border:1px solid #ddd; padding:12px; font-size:14px; background:#fcfcfc; resize:none;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:15px;">
                <button id="share-to-ig" style="padding:15px; border-radius:12px; background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color:#fff; border:none; cursor:pointer; font-weight:800; font-size:14px;">Instagram</button>
                <a id="share-to-wa" href="#" target="_blank" style="padding:15px; border-radius:12px; background:#25d366; color:#fff; text-decoration:none; text-align:center; font-weight:800; font-size:14px; display:block;">WhatsApp</a>
            </div>

            <a id="share-to-fb" href="#" target="_blank" style="padding:14px; border-radius:12px; background:#1877f2; color:#fff; text-decoration:none; text-align:center; font-weight:700; font-size:14px; display:block; margin-bottom:12px;">Share on Facebook</a>
            
            <!-- Visual Image for Manual Saving -->
            <div id="obenlo-social-image-container" style="margin-bottom:15px; border-radius:12px; overflow:hidden; border:1px solid #eee; background:#f9f9f9;">
                <img id="obenlo-social-preview-img" src="" style="width:100%; height:auto; display:block; min-height:120px; max-height:180px; object-fit:cover;">
                <div style="padding:10px; background:#fff;">
                    <p style="margin:0; font-size:11px; color:#666; font-weight:600;">Long-press image to Save to Photos 📸</p>
                </div>
            </div>

            <button id="share-to-native" style="padding:10px; border-radius:10px; background:#f4f4f4; color:#333; border:1px solid #ddd; cursor:pointer; font-weight:600; font-size:13px; width:100%;">System Share</button>
        </div>
        <?php
    }

    /**
     * Helper for Frontend Theme Integration
     */
    public static function render_frontend_push_button( $listing_id ) {
        if ( ! is_user_logged_in() ) return;
        $current_user_id = get_current_user_id();
        $author_id = get_post_field( 'post_author', $listing_id );

        // Only show to the host/author or admin
        if ( $current_user_id != $author_id && ! current_user_can( 'manage_options' ) ) return;

        $post = get_post( $listing_id );
        $title = $post->post_title;
        $url = get_permalink( $listing_id );
        $price = get_post_meta( $listing_id, '_obenlo_price', true );
        $location = get_post_meta( $listing_id, '_obenlo_location', true );
        $image = get_the_post_thumbnail_url( $listing_id, 'large' );

        // Generate Smart Caption
        $template = get_option( 'obenlo_social_listing_template', "New on Obenlo!\n\n{title} in {location}\nJust ${price}!" );
        $caption = str_replace( array( '{title}', '${price}', '{location}' ), array( $title, $price, $location ), $template );

        ?>
        <div class="obenlo-host-tools-card" style="margin-top: 20px; padding: 20px; background: #fff; border: 2px solid #e61e4d; border-radius: 12px; box-shadow: 0 4px 15px rgba(230,30,77,0.1);">
            <h4 style="margin: 0 0 10px 0; color: #e61e4d; font-size: 1.1rem;">Host Quick Tools</h4>
            <p style="margin: 0 0 15px 0; font-size: 0.85rem; color: #666;">Get more eyes on your listing! Share this to your Facebook, Instagram, or WhatsApp status.</p>
            <button class="obenlo-social-push-btn" 
                data-post-id="<?php echo esc_attr( $listing_id ); ?>"
                data-title="<?php echo esc_attr( $title ); ?>"
                data-url="<?php echo esc_url( $url ); ?>"
                data-price="<?php echo esc_attr( $price ); ?>"
                data-location="<?php echo esc_attr( $location ); ?>"
                data-type="listing"
                data-image="<?php echo esc_url( $image ); ?>"
                data-caption="<?php echo esc_attr( $caption ); ?>"
                style="width: 100%; background: #000; color: #fff; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);"
                onmouseover="this.style.background='#e61e4d';"
                onmouseout="this.style.background='#000';"
            >
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                Push to Social
            </button>
        </div>
        <?php
    }
}
