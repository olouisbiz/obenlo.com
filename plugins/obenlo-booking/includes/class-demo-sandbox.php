<?php
/**
 * Demo Sandbox Manager - Obenlo
 * Handles temporary session-based storage for demo users.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Demo_Sandbox
{
    private static $session_id = null;
    private static $is_filtering = false;
    private static $is_demo_cached = null;

    public static function init()
    {
        // Filter metadata to return sandboxed values for demo user
        add_filter('get_post_metadata', array(__CLASS__, 'filter_post_meta'), 10, 4);
        add_filter('get_user_metadata', array(__CLASS__, 'filter_user_meta'), 10, 4);

        // Filter main post fields
        add_filter('the_title', array(__CLASS__, 'filter_post_title'), 10, 2);
        add_filter('the_content', array(__CLASS__, 'filter_post_content'), 10, 1);
        add_filter('get_the_excerpt', array(__CLASS__, 'filter_post_content'), 10, 1);

        // Hide other users' sandboxed creations
        add_action('pre_get_posts', array(__CLASS__, 'isolate_sandboxed_posts'));

        // Cleanup on logout
        add_action('wp_logout', array(__CLASS__, 'cleanup_on_logout'));

        // Cleanup expired demo sessions (garbage collection)
        add_action('wp_loaded', array(__CLASS__, 'maybe_clean_expired'));

        // Intercept metadata updates to save to sandbox instead of DB
        add_filter('update_post_metadata', array(__CLASS__, 'intercept_post_update'), 10, 5);
        add_filter('update_user_metadata', array(__CLASS__, 'intercept_user_update'), 10, 5);
        add_filter('add_post_metadata', array(__CLASS__, 'intercept_post_update'), 10, 5);
        add_filter('add_user_metadata', array(__CLASS__, 'intercept_user_update'), 10, 5);
    }

    /**
     * Filter post title if sandboxed version exists
     */
    public static function filter_post_title($title, $post_id = null)
    {
        if (!$post_id || !self::is_active()) {
            return $title;
        }

        $sandboxed = self::get('post', $post_id, '_obenlo_sandboxed_title');
        return ($sandboxed !== false) ? $sandboxed : $title;
    }

    /**
     * Filter post content if sandboxed version exists
     */
    public static function filter_post_content($content)
    {
        if (!self::is_active()) {
            return $content;
        }

        global $post;
        if (!$post) return $content;

        $sandboxed = self::get('post', $post->ID, '_obenlo_sandboxed_content');
        return ($sandboxed !== false) ? $sandboxed : $content;
    }

    /**
     * Isolate sandboxed posts so users only see their own creations
     */
    public static function isolate_sandboxed_posts($query)
    {
        // Don't filter in the admin dashboard (except for AJAX which might be needed)
        // But do filter for our custom frontend dashboard queries
        if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
            return;
        }

        // Only filter listing types to avoid breaking core WP queries
        $post_type = $query->get('post_type');
        if ($post_type !== 'listing' && (is_array($post_type) && !in_array('listing', $post_type))) {
            return;
        }

        $is_demo = self::is_active();
        $session = self::get_session_id();

        if ($is_demo) {
            $meta_query = $query->get('meta_query');
            if (!is_array($meta_query)) $meta_query = array();

            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_obenlo_sandbox_session_id',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_obenlo_sandbox_session_id',
                    'value' => $session,
                    'compare' => '='
                )
            );
            $query->set('meta_query', $meta_query);
        } else {
            // For public visitors, we handle the metadata exclusion via a more robust SQL filter
            // to ensure it cannot be bypassed by other meta_queries.
            add_filter('posts_where', array(__CLASS__, 'exclude_sandboxed_from_sql'), 10, 2);
        }
    }

    /**
     * Low-level SQL filter to ensure sandboxed posts NEVER leak to public
     */
    public static function exclude_sandboxed_from_sql($where, $query)
    {
        // Only target listing types
        $post_type = $query->get('post_type');
        if ($post_type !== 'listing' && (is_array($post_type) && !in_array('listing', $post_type))) {
            return $where;
        }

        // Only for public/non-demo users
        if (self::is_active()) {
            return $where;
        }

        global $wpdb;
        $where .= " AND NOT EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} 
            WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
            AND {$wpdb->postmeta}.meta_key = '_obenlo_sandbox_session_id'
        )";

        return $where;
    }

    /**
     * Get or create a unique session ID for the current visitor
     */
    public static function get_session_id()
    {
        if (self::$session_id !== null) {
            return self::$session_id;
        }

        // Use a static variable to avoid re-triggering user lookups during the same request
        static $internal_session_id = null;
        if ($internal_session_id !== null) {
            return $internal_session_id;
        }

        // 1. Try WP session token (if logged in)
        // We use a more direct way to get the token to avoid triggering filters
        if (function_exists('wp_get_session_token')) {
            $token = wp_get_session_token();
            if ($token) {
                $internal_session_id = $token;
                self::$session_id = $token;
                return $token;
            }
        }

        // 2. Try Cookie
        if (isset($_COOKIE['obenlo_demo_session'])) {
            self::$session_id = sanitize_text_field($_COOKIE['obenlo_demo_session']);
            $internal_session_id = self::$session_id;
            return self::$session_id;
        }

        // 3. Last resort: Generate and set a cookie-based session
        $new_session = uniqid('demo_') . mt_rand(1000, 9999);
        if (!headers_sent()) {
            $cookie_path = defined('COOKIEPATH') ? COOKIEPATH : '/';
            $cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
            setcookie('obenlo_demo_session', $new_session, time() + (2 * HOUR_IN_SECONDS), $cookie_path, $cookie_domain);
        }
        $internal_session_id = $new_session;
        self::$session_id = $new_session;
        return $new_session;
    }

    /**
     * Check if sandboxing should be active for the current user
     */
    public static function is_active()
    {
        // Static cache for performance
        if (self::$is_demo_cached !== null) {
            return self::$is_demo_cached;
        }

        // Prevent infinite recursion during user retrieval
        // CRITICAL: Set guard BEFORE calling any WP functions that might trigger filters
        if (self::$is_filtering) {
            return false;
        }

        if (!function_exists('get_current_user_id') || !function_exists('wp_get_current_user')) {
            return false;
        }

        self::$is_filtering = true;

        // Quick check: if no user is logged in, it can't be 'demo'
        $user_id = get_current_user_id();
        if (!$user_id) {
            self::$is_filtering = false;
            return false; 
        }

        $user = wp_get_current_user();
        
        self::$is_filtering = false;

        self::$is_demo_cached = ($user && $user->user_login === 'demo');
        return self::$is_demo_cached;
    }

    /**
     * Save a value to the sandbox
     */
    public static function set($type, $object_id, $key, $value)
    {
        $session = self::get_session_id();
        $sandbox_key = "ob_sb_{$session}_{$type}_{$object_id}_{$key}";
        // Limit key length for transients (max 172 chars, though 64 is safer for some systems)
        $sandbox_key = substr($sandbox_key, 0, 170);
        
        set_transient($sandbox_key, $value, 2 * HOUR_IN_SECONDS);
    }

    /**
     * Get a value from the sandbox
     */
    public static function get($type, $object_id, $key)
    {
        $session = self::get_session_id();
        $sandbox_key = "ob_sb_{$session}_{$type}_{$object_id}_{$key}";
        $sandbox_key = substr($sandbox_key, 0, 170);
        
        return get_transient($sandbox_key);
    }

    /**
     * Intercept Post Meta Updates
     */
    public static function intercept_post_update($check, $object_id, $meta_key, $meta_value, $prev_value = '')
    {
        if (self::is_active()) {
            // Only sandbox Obenlo-specific meta keys to avoid breaking core WP or other plugins
            if (strpos($meta_key, '_obenlo_') !== false) {
                self::set('post', $object_id, $meta_key, $meta_value);
                return true; // Return true to indicate the update was handled and skip DB write
            }
        }
        return $check; // Return null (default $check) to proceed with normal save
    }

    /**
     * Intercept User Meta Updates
     */
    public static function intercept_user_update($check, $object_id, $meta_key, $meta_value, $prev_value = '')
    {
        if (self::is_active()) {
            if (strpos($meta_key, 'obenlo_') !== false || strpos($meta_key, '_obenlo_') !== false) {
                self::set('user', $object_id, $meta_key, $meta_value);
                return true;
            }
        }
        return $check;
    }

    /**
     * Filter Post Meta calls for demo user
     */
    public static function filter_post_meta($value, $object_id, $meta_key, $single)
    {
        if (!self::is_active() || strpos($meta_key, '_obenlo_') === false) {
            return $value;
        }

        $sandboxed = self::get('post', $object_id, $meta_key);
        if ($sandboxed !== false) {
            return $single ? $sandboxed : array($sandboxed);
        }

        return $value;
    }

    /**
     * Filter User Meta calls for demo user
     */
    public static function filter_user_meta($value, $object_id, $meta_key, $single)
    {
        if (!self::is_active() || strpos($meta_key, '_obenlo_') === false) {
            return $value;
        }

        $sandboxed = self::get('user', $object_id, $meta_key);
        if ($sandboxed !== false) {
            return $single ? $sandboxed : array($sandboxed);
        }

        return $value;
    }

    /**
     * Periodically check and clean expired demo sessions.
     */
    public static function maybe_clean_expired()
    {
        if (false === get_transient('obenlo_demo_cleanup_done')) {
            self::clean_expired_demo_sessions();
            set_transient('obenlo_demo_cleanup_done', true, HOUR_IN_SECONDS);
        }
    }

    /**
     * Delete listings marked as sandbox that haven't been modified recently.
     */
    public static function clean_expired_demo_sessions()
    {
        $sandboxed_posts = get_posts(array(
            'post_type' => 'listing',
            'meta_query' => array(
                array(
                    'key' => '_obenlo_sandbox_session_id',
                    'compare' => 'EXISTS'
                )
            ),
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'before' => '2 hours ago'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'any',
            'suppress_filters' => true
        ));

        if (!empty($sandboxed_posts)) {
            foreach ($sandboxed_posts as $post_id) {
                wp_delete_post($post_id, true);
            }
        }
    }

    /**
     * Clear session cookies and delete sandboxed posts on logout
     */
    public static function cleanup_on_logout()
    {
        $session = self::get_session_id();
        
        // 1. Delete listings created during this session
        // We look for both the current session ID and any 'visitor' remnants just in case
        $sessions_to_clean = array_unique(array($session, 'visitor'));
        
        foreach ($sessions_to_clean as $sid) {
            if (empty($sid)) continue;
            
            $sandboxed_posts = get_posts(array(
                'post_type' => 'listing',
                'meta_key' => '_obenlo_sandbox_session_id',
                'meta_value' => $sid,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_status' => 'any',
                'suppress_filters' => true // Important: Bypass filters to ensure we find them
            ));

            if (!empty($sandboxed_posts)) {
                foreach ($sandboxed_posts as $post_id) {
                    wp_delete_post($post_id, true);
                }
            }
        }
        
        // 2. Clear the session cookie
        if (!headers_sent()) {
            $cookie_path = defined('COOKIEPATH') ? COOKIEPATH : '/';
            $cookie_domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
            setcookie('obenlo_demo_session', '', time() - 3600, $cookie_path, $cookie_domain);
        }
    }
}
