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
        add_action('wp_head', array($this, 'inject_verification_tags'), 2);
        add_filter('pre_get_document_title', array($this, 'dynamic_seo_title'), 999);
    }

    /**
     * Inject Google/Bing verification tags
     */
    public function inject_verification_tags() {
        // Users can add their IDs here or via meta-fields later
        $google_id = get_option('obenlo_google_site_verification'); // Placeholder
        if ($google_id) {
            echo '<meta name="google-site-verification" content="' . esc_attr($google_id) . '">' . "\n";
        }
        // Branded Meta
        echo '<meta name="apple-mobile-web-app-title" content="Obenlo">' . "\n";
        echo '<meta name="application-name" content="Obenlo">' . "\n";
    }

    /**
     * Standardize Website Titles for SEO
     */
    public function dynamic_seo_title($title) {
        if (is_singular('listing')) {
            $post_id = get_the_ID();
            $title_val = get_the_title($post_id);
            $location = get_post_meta($post_id, '_obenlo_location', true);
            $industry = $this->get_listing_industry($post_id);

            // Smart Title: [Name] | [Industry] [Location] | Obenlo
            $smart_title = $title_val;
            if ($industry) $smart_title .= ' - ' . $industry;
            if ($location) $smart_title .= ' in ' . $location;
            
            return $smart_title . ' | Obenlo';
        }

        if (is_author()) {
            $author = get_queried_object();
            $store_name = get_user_meta($author->ID, 'obenlo_store_name', true);
            $name = $store_name ? $store_name : $author->display_name;
            return $name . ' - Professional Host on Obenlo';
        }

        if (is_front_page()) {
            return 'Obenlo | Stays, Experiences, Services & Events Marketplace';
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
            $description = 'Obenlo is the ultimate marketplace for Stays, unique Experiences, local Services, and viral Events. Book verified local hosts directly.';
        }
        
        $image = get_template_directory_uri() . '/assets/images/logo-social-profile.png';
        $canonical = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

        if ($is_listing) {
            $content = get_post_meta($post_id, '_obenlo_listing_content', true);
            $price = get_post_meta($post_id, '_obenlo_price', true);
            $desc_prefix = '';
            $pricing_model = get_post_meta($post_id, '_obenlo_pricing_model', true);
            
            if ($pricing_model === 'inquiry_only') {
                $desc_prefix .= 'Quote Based Service. Contact for custom pricing. ';
            } elseif ($price) {
                $desc_prefix .= 'Starting at $' . $price . '. ';
            }

            if ($location) $desc_prefix .= 'Located in ' . $location . '. ';
            
            $description = $content ? wp_trim_words($content, 20, '...') : $description;
            $description = $desc_prefix . $description;

            // Use Featured Image
            if (has_post_thumbnail($post_id)) {
                $image = get_the_post_thumbnail_url($post_id, 'large');
            }
        }

        ?>
        <!-- Obenlo SEO Engine -->
        <meta name="description" content="<?php echo esc_attr($description); ?>">
        <link rel="canonical" href="<?php echo esc_url($canonical); ?>">

        <!-- OpenGraph (WhatsApp / Facebook) -->
        <meta property="og:type" content="<?php echo $is_listing ? 'website' : 'website'; ?>">
        <meta property="og:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
        <meta property="og:description" content="<?php echo esc_attr($description); ?>">
        <meta property="og:url" content="<?php echo esc_url($canonical); ?>">
        <meta property="og:image" content="<?php echo esc_url($image); ?>">
        <meta property="og:site_name" content="Obenlo">

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
        <?php
    }

    private function get_listing_industry($post_id) {
        $type_terms = wp_get_post_terms($post_id, 'listing_type');
        if (is_wp_error($type_terms) || empty($type_terms)) return '';
        
        // Find top level industry
        foreach($type_terms as $term) {
            if ($term->parent == 0) return $term->name;
            $parent = get_term($term->parent, 'listing_type');
            if ($parent && !is_wp_error($parent)) return $parent->name;
        }
        return '';
    }
}
