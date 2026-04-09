<?php
/**
 * Obenlo_SEO_Sitemap: Handles Dynamic XML Sitemap Generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_SEO_Sitemap {
    
    public function init() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'render_sitemap'));
        add_filter('robots_txt', array($this, 'update_robots_txt'), 999, 2);
    }

    /**
     * Register /sitemap.xml rewrite rule
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?obenlo_sitemap=1', 'top');
    }

    /**
     * Add custom query variable
     */
    public function add_query_vars($vars) {
        $vars[] = 'obenlo_sitemap';
        return $vars;
    }

    /**
     * Render the Sitemap XML content
     */
    public function render_sitemap() {
        if (get_query_var('obenlo_sitemap') != 1) {
            return;
        }

        header('Content-Type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Static Pages & Home
        $this->add_url(home_url('/'), '1.0', 'daily');
        
        $pages = get_pages(array(
            'post_status' => 'publish',
            'exclude'     => array() // Add IDs to exclude if needed
        ));

        foreach ($pages as $page) {
            // Exclude private/dashboard pages by template or slug if necessary
            $template = get_post_meta($page->ID, '_wp_page_template', true);
            if (strpos($template, 'dashboard') !== false || strpos($page->post_name, 'account') !== false) {
                continue;
            }
            $this->add_url(get_permalink($page->ID), '0.8', 'weekly');
        }

        // 2. Active Listings
        $listings = get_posts(array(
            'post_type'      => 'listing',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ));

        foreach ($listings as $listing) {
            // Check suspension status
            if (get_post_meta($listing->ID, '_obenlo_is_suspended', true) === 'yes') {
                continue;
            }
            $this->add_url(get_permalink($listing->ID), '0.9', 'daily');
        }

        // 3. Host Storefronts
        $hosts = get_users(array(
            'role' => 'host',
        ));

        foreach ($hosts as $host) {
            // Check suspension status
            if (get_user_meta($host->ID, '_obenlo_is_suspended', true) === 'yes') {
                continue;
            }
            $this->add_url(home_url('/' . $host->user_nicename . '/'), '0.7', 'weekly');
        }

        echo '</urlset>';
        exit;
    }

    /**
     * Update robots.txt to point to the premium sitemap
     */
    public function update_robots_txt($output, $public) {
        $custom_sitemap = home_url('/sitemap.xml');
        $output = preg_replace('/Sitemap: .*/i', "Sitemap: $custom_sitemap", $output);
        
        // Ensure sitemap is present if not already matched
        if (strpos($output, "Sitemap: $custom_sitemap") === false) {
            $output .= "\nSitemap: $custom_sitemap\n";
        }

        return $output;
    }

    /**
     * Helper to output URL tags
     */
    private function add_url($url, $priority = '0.5', $changefreq = 'weekly') {
        echo '<url>';
        echo '<loc>' . esc_url($url) . '</loc>';
        echo '<priority>' . esc_html($priority) . '</priority>';
        echo '<changefreq>' . esc_html($changefreq) . '</changefreq>';
        echo '</url>';
    }
}
