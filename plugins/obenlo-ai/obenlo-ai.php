<?php
/**
 * Plugin Name: Obenlo AI
 * Description: AI-powered layer for Obenlo — intelligent chat replies, listing optimization, and natural language search. Powered by Google Gemini (free tier).
 * Version: 1.0.0
 * Author: Obenlo
 * Author URI: https://obenlo.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ──────────────────────────────────────────────────────────────
define( 'OBENLO_AI_VERSION', '1.0.0' );
define( 'OBENLO_AI_DIR', plugin_dir_path( __FILE__ ) );
define( 'OBENLO_AI_URL', plugin_dir_url( __FILE__ ) );

// ── Autoload includes ──────────────────────────────────────────────────────
require_once OBENLO_AI_DIR . 'includes/class-ai-client.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-chat.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-listing.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-search.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-admin.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-host-profile.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-outreach.php';

// ── Boot on plugins_loaded (same pattern as obenlo-booking) ───────────────
function obenlo_ai_init() {
    ( new Obenlo_AI_Chat() )->init();
    ( new Obenlo_AI_Listing() )->init();
    ( new Obenlo_AI_Search() )->init();
    ( new Obenlo_AI_Admin() )->init();
    ( new Obenlo_AI_Host_Profile() )->init();
    ( new Obenlo_AI_Outreach() )->init();
}
add_action( 'plugins_loaded', 'obenlo_ai_init' );
