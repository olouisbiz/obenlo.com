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
require_once OBENLO_AI_DIR . 'includes/class-ai-affiliate-outreach.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-live-chat.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-blog.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-affiliate.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-ticketmaster.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-seatgeek.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-viator.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-travelpayouts.php';
require_once OBENLO_AI_DIR . 'includes/class-ai-groupon.php';

// ── Boot on plugins_loaded (same pattern as obenlo-booking) ───────────────
function obenlo_ai_init() {
    ( new Obenlo_AI_Chat() )->init();
    ( new Obenlo_AI_Listing() )->init();
    ( new Obenlo_AI_Search() )->init();
    ( new Obenlo_AI_Admin() )->init();
    ( new Obenlo_AI_Host_Profile() )->init();
    ( new Obenlo_AI_Outreach() )->init();
    new Obenlo_AI_Affiliate_Outreach();
    ( new Obenlo_AI_Live_Chat() )->init();
    ( new Obenlo_AI_Blog() )->init();
    ( new Obenlo_AI_Affiliate() )->init();
    new Obenlo_AI_Ticketmaster();
    new Obenlo_AI_SeatGeek();
    new Obenlo_AI_Viator();
    new Obenlo_AI_Travelpayouts();
    new Obenlo_AI_Groupon();
}
add_action( 'plugins_loaded', 'obenlo_ai_init' );
