<?php
/**
 * Booking Engine Manager (Factory)
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once OBENLO_BOOKING_DIR . 'includes/engines/class-abstract-engine.php';

class Obenlo_Engine_Manager {
    
    private static $instance = null;
    private $engines = array();

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Load All Engine Classes
        $this->load_engines();
    }

    private function load_engines() {
        $engine_files = array(
            'nightly'     => 'class-engine-nightly.php',
            'slot'        => 'class-engine-slot.php',
            'fixed_block' => 'class-engine-fixed-block.php',
            'session'     => 'class-engine-session.php',
            'logistics'   => 'class-engine-logistics.php',
            'inquiry'     => 'class-engine-inquiry.php',
        );

        foreach ($engine_files as $id => $file) {
            $path = OBENLO_BOOKING_DIR . 'includes/engines/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'Obenlo_Engine_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $id)));
                if (class_exists($class_name)) {
                    $this->engines[$id] = new $class_name();
                }
            }
        }
    }

    /**
     * Get an engine by its ID
     */
    public function get_engine($engine_id) {
        // Map common dash/underscore variations
        $engine_id = str_replace('engine_', '', $engine_id);
        return isset($this->engines[$engine_id]) ? $this->engines[$engine_id] : null;
    }

    /**
     * Get engine for a listing by resolving the listing's subcategory slug
     */
    public function get_engine_for_listing($listing_id) {
        // First check if an engine has been explicitly saved on the listing
        $saved = get_post_meta($listing_id, '_obenlo_listing_engine', true);
        if ($saved) {
            return $this->get_engine($saved);
        }

        // Otherwise, derive it from the listing_type taxonomy
        $terms = get_the_terms($listing_id, 'listing_type');
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                // Include term name as slug fallback (handles WP auto-suffixed slugs e.g. 'delivery-2')
                $slugs = array_unique([$term->slug, sanitize_title($term->name)]);
                if ($term->parent) {
                    $parent = get_term($term->parent, 'listing_type');
                    if ($parent && !is_wp_error($parent)) {
                        $slugs[] = $parent->slug;
                        $slugs[] = sanitize_title($parent->name);
                        $slugs = array_unique($slugs);
                    }
                }
                $engine = $this->resolve_engine_from_slugs($slugs);
                if ($engine) return $engine;
            }
        }

        // Ultimate fallback
        return $this->get_engine('nightly');
    }

    /**
     * Get engine for a given listing_type term ID (used by AJAX handler)
     */
    public function get_engine_for_listing_type($type_id) {
        if (!$type_id) return null;
        $term = get_term($type_id, 'listing_type');
        if (!$term || is_wp_error($term)) return null;

        $slugs = array_unique([$term->slug, sanitize_title($term->name)]);
        if ($term->parent) {
            $parent = get_term($term->parent, 'listing_type');
            if ($parent && !is_wp_error($parent)) {
                $slugs[] = $parent->slug;
                $slugs[] = sanitize_title($parent->name);
                $slugs = array_unique($slugs);
            }
        }
        return $this->resolve_engine_from_slugs($slugs);
    }

    /**
     * Resolve an engine instance from an array of taxonomy slugs (child/specific first)
     */
    private function resolve_engine_from_slugs($slugs) {
        $map = array(
            // ── Nightly: Stays & Accommodations ────────────────────────
            'nightly'     => [
                'stay', 'hotel', 'guest-house', 'villa', 'apartment',
                'cabin', 'cottage', 'boat', 'hostel', 'resort',
            ],

            // ── Fixed Block: Experiences / Tours ───────────────────────
            'fixed_block' => [
                'experience', 'tour', 'food-tasting', 'photo-shoot',
                'photography', 'food-tour', 'wine-tasting',
            ],

            // ── Session: Events, Classes, Tickets ──────────────────────
            'session'     => [
                'event', 'show', 'class', 'celebration', 'donation-giving',
                'ticket', 'donation', 'concert', 'workshop', 'conference',
            ],

            // ── Logistics: Route-based / Pickup+Dropoff ─────────────────
            'logistics'   => [
                'delivery', 'chauffeur', 'driver', 'moving', 'towing',
                'shipping', 'transport', 'courier',
            ],

            // ── Slot: Time-slot Services ────────────────────────────────
            'slot'        => [
                'service', 'barber', 'barbershop', 'hairdresser', 'beauty',
                'cook', 'handyman', 'cleaning', 'babysitter', 'dogsitter',
                'massage', 'personal-trainer', 'freelance', 'photographer',
                'concierge', 'personal-assistant', 'therapist', 'tutor',
                'fitness', 'yoga', 'consulting',
            ],

            // ── Inquiry: Quote-based / Contact ──────────────────────────
            'inquiry'     => [
                'inquiry', 'quote', 'custom',
            ],
        );

        // Check child (most specific) slug first — DO NOT reverse
        foreach ($slugs as $slug) {
            foreach ($map as $engine_id => $engine_slugs) {
                if (in_array($slug, $engine_slugs, true)) {
                    return $this->get_engine($engine_id);
                }
            }
        }
        return null;
    }

    /**
     * Get all registered engines
     */
    public function get_engines() {
        return $this->engines;
    }
}
