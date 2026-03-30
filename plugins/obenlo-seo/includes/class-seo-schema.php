<?php
/**
 * Obenlo_SEO_Schema: Manages JSON-LD Structured Data
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_SEO_Schema {
    public function init() {
        add_action('wp_footer', array($this, 'inject_listing_schema'), 20);
    }

    /**
     * Standardize JSON-LD Schema for Google Rich Search
     */
    public function inject_listing_schema() {
        if (!is_singular('listing')) {
            return;
        }

        $post_id = get_the_ID();
        $listing_name = get_the_title($post_id);
        $listing_description = wp_trim_words(get_post_meta($post_id, '_obenlo_listing_content', true), 50);
        $listing_image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : '';
        $category_slug = get_post_meta($post_id, '_obenlo_listing_category', true);
        $price = get_post_meta($post_id, '_obenlo_listing_price', true);
        $location = get_post_meta($post_id, '_obenlo_listing_location', true);

        // Core Schema Data
        $data = array(
            "@context" => "https://schema.org",
            "name" => $listing_name,
            "description" => $listing_description,
            "url" => get_permalink($post_id),
            "image" => $listing_image,
            "offers" => array(
                "@type" => "Offer",
                "price" => $price,
                "priceCurrency" => "USD",
                "availability" => "https://schema.org/InStock"
            )
        );

        // Advanced Categorization for Rich Results
        if ($category_slug === 'stay') {
            $data["@type"] = "Hotel";
            $data["address"] = $location;
        } elseif ($category_slug === 'event' || $category_slug === 'class') {
            $data["@type"] = "Event";
            $data["eventStatus"] = "https://schema.org/EventScheduled";
            $data["eventAttendanceMode"] = "https://schema.org/OfflineEventAttendanceMode";
            
            // Check for virtual event
            $is_virtual = get_post_meta($post_id, '_obenlo_event_location_type', true);
            if ($is_virtual === 'virtual') {
                 $data["eventAttendanceMode"] = "https://schema.org/OnlineEventAttendanceMode";
            }
        } else {
            $data["@type"] = "Product";
        }

        echo "\n<!-- Obenlo Rich Search Data -->\n";
        echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}
