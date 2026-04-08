<?php
/**
 * Obenlo_SEO_Head: Manages Meta Tags & OpenGraph
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_SEO_Head {
    public function init() {
        add_action('wp_head', array($this, 'inject_meta_tags'), 1);
        add_filter('pre_get_document_title', array($this, 'dynamic_seo_title'), 999);
    }

    /**
     * Standardize Website Titles for SEO
     */
    public function dynamic_seo_title($title) {
        if (is_singular('listing')) {
            $post_id = get_the_ID();
            $host_id = @get_post_field('post_author', $post_id);
            $host_name = $host_id ? get_the_author_meta('display_name', $host_id) : '';
            $listing_name = get_the_title($post_id);

            return $listing_name . ' • ' . $host_name . ' | Obenlo';
        }

        if (is_front_page()) {
            return 'Obenlo | Book Local Services, Events & Unique Stays';
        }

        return $title;
    }

    /**
     * Inject Meta Tags into wp_head
     */
    public function inject_meta_tags() {
        $post_id = get_the_ID();
        $is_listing = is_singular('listing');

        $description = get_bloginfo('description');
        if (is_front_page()) {
            $description = 'The all-in-one marketplace for local services, viral events, and unique stays. Discover top-rated hosts and book your next experience on Obenlo.';
        }
        
        $image = get_template_directory_uri() . '/assets/images/logo-social-profile.png';
        $url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

        if ($is_listing) {
            $content = get_post_meta($post_id, '_obenlo_listing_content', true);
            $description = $content ? wp_trim_words($content, 25, '...') : $description;

            // Use Featured Image (Listing Photo)
            if (has_post_thumbnail($post_id)) {
                $image = get_the_post_thumbnail_url($post_id, 'large');
            }
        }

        ?>
        <!-- Obenlo SEO Engine -->
        <meta name="description" content="<?php echo esc_attr($description); ?>">

        <!-- OpenGraph (WhatsApp / Facebook) -->
        <meta property="og:type" content="<?php echo $is_listing ? 'website' : 'website'; ?>">
        <meta property="og:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
        <meta property="og:description" content="<?php echo esc_attr($description); ?>">
        <meta property="og:url" content="<?php echo esc_url($url); ?>">
        <meta property="og:image" content="<?php echo esc_url($image); ?>">
        <meta property="og:site_name" content="Obenlo">

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
        <?php
    }
}
