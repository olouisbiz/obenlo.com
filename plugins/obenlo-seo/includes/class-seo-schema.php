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
        add_action('wp_footer', array($this, 'inject_global_schema'), 10);
        add_action('wp_footer', array($this, 'inject_breadcrumb_schema'), 15);
    }

    /**
     * Inject BreadcrumbList Schema for hierarchical navigation
     */
    public function inject_breadcrumb_schema() {
        $post_id = get_the_ID();
        $is_listing = is_singular('listing');

        $items = array();
        // 1. Home
        $items[] = array(
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => home_url('/')
        );

        if ($is_listing) {
            $type_terms = wp_get_post_terms($post_id, 'listing_type');
            if (!is_wp_error($type_terms) && !empty($type_terms)) {
                $term = $type_terms[0];
                $pos = 2;
                
                // If sub-category, add parent first
                if ($term->parent != 0) {
                    $parent = get_term($term->parent, 'listing_type');
                    $items[] = array(
                        "@type" => "ListItem",
                        "position" => $pos++,
                        "name" => $parent->name,
                        "item" => home_url('/?s=' . urlencode($parent->name)) // Category search link
                    );
                }

                $items[] = array(
                    "@type" => "ListItem",
                    "position" => $pos++,
                    "name" => $term->name,
                    "item" => home_url('/?s=' . urlencode($term->name))
                );

                $items[] = array(
                    "@type" => "ListItem",
                    "position" => $pos++,
                    "name" => get_the_title($post_id),
                    "item" => get_permalink($post_id)
                );
            }
        }

        if (empty($items)) return;

        $breadcrumb = array(
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $items
        );

        echo "\n<!-- Obenlo Authority Navigation -->\n";
        echo '<script type="application/ld+json">' . json_encode($breadcrumb, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
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
        $price = get_post_meta($post_id, '_obenlo_price', true);
        $location = get_post_meta($post_id, '_obenlo_location', true);

        // Fetch Industry/Category
        $type_terms = wp_get_post_terms($post_id, 'listing_type');
        $main_cat = 'Product';
        if (!is_wp_error($type_terms) && !empty($type_terms)) {
            foreach($type_terms as $term) {
                $check = strtolower($term->name);
                if (strpos($check, 'stay') !== false) { $main_cat = 'Hotel'; break; }
                if (strpos($check, 'house') !== false) { $main_cat = 'BedAndBreakfast'; break; }
                if (strpos($check, 'event') !== false) { $main_cat = 'Event'; break; }
                if (strpos($check, 'experience') !== false) { $main_cat = 'Event'; break; }
                if (strpos($check, 'service') !== false) { $main_cat = 'Service'; break; }
                if (strpos($check, 'chauffeur') !== false) { $main_cat = 'TaxiService'; break; }
                if (strpos($check, 'barber') !== false) { $main_cat = 'BarberShop'; break; }
                if (strpos($check, 'clean') !== false) { $main_cat = 'Service'; break; }
                if (strpos($check, 'tutor') !== false) { $main_cat = 'EducationalOrganization'; break; }
                if (strpos($check, 'photo') !== false) { $main_cat = 'ProfessionalService'; break; }
                if (strpos($check, 'baby') !== false || strpos($check, 'child') !== false) { $main_cat = 'ChildCare'; break; }
            }
        }

        // Core Schema Data
        $data = array(
            "@context" => "https://schema.org",
            "@type" => $main_cat,
            "name" => $listing_name,
            "description" => $listing_description,
            "url" => get_permalink($post_id),
            "image" => $listing_image,
            "offers" => array(
                "@type" => "Offer",
                "price" => $price ?: "0",
                "priceCurrency" => "USD",
                "availability" => "https://schema.org/InStock",
                "url" => get_permalink($post_id)
            )
        );

        // Support Reviews in Google
        if (class_exists('Obenlo_Booking_Reviews')) {
            $avg_rating = Obenlo_Booking_Reviews::get_listing_average_rating($post_id);
            $review_count = Obenlo_Booking_Reviews::get_listing_review_count($post_id);
            if ($avg_rating > 0) {
                $data["aggregateRating"] = array(
                    "@type" => "AggregateRating",
                    "ratingValue" => $avg_rating,
                    "reviewCount" => $review_count ?: 1,
                    "bestRating" => "5",
                    "worstRating" => "1"
                );
            }
        }

        // Advanced Categorization
        if ($main_cat === 'Hotel' || $main_cat === 'BedAndBreakfast') {
            $data["address"] = array(
                "@type" => "PostalAddress",
                "streetAddress" => $location
            );
        } elseif ($main_cat === 'Event') {
            $data["eventStatus"] = "https://schema.org/EventScheduled";
            $data["eventAttendanceMode"] = "https://schema.org/OfflineEventAttendanceMode";
            
            $is_virtual = get_post_meta($post_id, '_obenlo_event_location_type', true);
            if ($is_virtual === 'virtual') {
                 $data["eventAttendanceMode"] = "https://schema.org/OnlineEventAttendanceMode";
            }
            $data["location"] = array(
                "@type" => "Place",
                "name" => $location,
                "address" => $location
            );

            // Add duration if available
            $duration_val = get_post_meta($post_id, '_obenlo_duration_val', true);
            $duration_unit = get_post_meta($post_id, '_obenlo_duration_unit', true) ?: 'hours';
            if ($duration_val) {
                $iso_duration = ($duration_unit === 'hours') ? 'PT' . $duration_val . 'H' : 'PT' . $duration_val . 'M';
                $data["duration"] = $iso_duration;
            }
        }

        echo "\n<!-- Obenlo Premium Schema v1.7.2 -->\n";
        echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Inject Global Organization & WebSite Schema
     */
    public function inject_global_schema() {
        if (!is_front_page()) {
            return;
        }

        $organization = array(
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => "Obenlo",
            "url" => home_url('/'),
            "logo" => get_template_directory_uri() . '/assets/images/logo.png',
            "sameAs" => array(
                "https://facebook.com/obenlo",
                "https://instagram.com/obenlo",
                "https://twitter.com/obenlo"
            )
        );

        $website = array(
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => "Obenlo",
            "url" => home_url('/'),
            "potentialAction" => array(
                "@type" => "SearchAction",
                "target" => home_url('/?s={search_term_string}'),
                "query-input" => "required name=search_term_string"
            )
        );

        echo "\n<!-- Obenlo Global Brand Data -->\n";
        echo '<script type="application/ld+json">' . json_encode($organization, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        echo '<script type="application/ld+json">' . json_encode($website, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
