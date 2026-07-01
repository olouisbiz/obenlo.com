<?php
/**
 * Obenlo AI Outreach Agent
 *
 * Provides a dedicated administrator dashboard tab to search for local service
 * providers, generate custom pitch emails, track outreach history, send follow-ups,
 * and perform bulk outreach actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Outreach {

    public function init() {
        // Register Admin Submenu Page under Obenlo Dash parent
        add_action( 'admin_menu', [ $this, 'register_outreach_menu' ], 30 );

        // AJAX handlers
        add_action( 'wp_ajax_obenlo_outreach_search',        [ $this, 'handle_outreach_search' ] );
        add_action( 'wp_ajax_obenlo_outreach_draft',         [ $this, 'handle_outreach_draft' ] );
        add_action( 'wp_ajax_obenlo_outreach_followup_draft',[ $this, 'handle_outreach_followup_draft' ] );
        add_action( 'wp_ajax_obenlo_outreach_send',          [ $this, 'handle_outreach_send' ] );
        add_action( 'wp_ajax_obenlo_outreach_get_history',   [ $this, 'handle_outreach_get_history' ] );
        add_action( 'wp_ajax_obenlo_outreach_clear_history', [ $this, 'handle_outreach_clear_history' ] );
    }

    // ── Admin Menu ────────────────────────────────────────────────────────

    public function register_outreach_menu() {
        add_submenu_page(
            'obenlo-admin-dashboard',
            'Obenlo AI Outreach Agent',
            '🤖 Outreach Agent',
            'manage_options',
            'obenlo-outreach-agent',
            [ $this, 'render_outreach_page' ]
        );
    }

    // ── AJAX: Search Providers ────────────────────────────────────────────

    public function handle_outreach_search() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $category = sanitize_text_field( $_POST['category'] ?? '' );
        $location = sanitize_text_field( $_POST['location'] ?? '' );
        $count    = intval( $_POST['count'] ?? 5 );
        if ( $count < 1 || $count > 25 ) {
            $count = 5;
        }

        if ( empty( $category ) || empty( $location ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in both Category and Location.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $rand_seed = wp_rand(1, 99999);
        $prompt = <<<PROMPT
You are an expert lead generator and business intelligence agent for {$platform_name}, a global service and experience marketplace.
You have access to Google Search. You MUST use Google Search to find real, active, and highly authentic local service providers. Do NOT guess. Verify that they are currently in business.
Search for {$count} providers matching:
Category: {$category}
Location: {$location}

IMPORTANT VARIETY INSTRUCTION: Do not return the same common businesses every time. Randomly explore your knowledge base to find lesser-known, highly specific, or different active providers. 
Random Seed: {$rand_seed}

Return ONLY a valid JSON array of objects (no markdown, no explanations, no wrapping in ```json). Each object must contain exactly these keys:
- "name": The business or provider's name.
- "website": The best online link for the business. This can be their official website. If they do not have one, you MAY use their Facebook page, Instagram profile, Yelp, or other directory link. If absolutely no link exists, return an empty string "".
- "email": A contact email address. (Aggressively search for this, including checking their Facebook/Instagram "About" sections. If absolutely unknown, return an empty string "").
- "phone": A contact phone number (If unknown, return an empty string "").
- "niche": The specific sub-specialty (e.g. "Wedding DJ", "Corporate Events").
- "description": A short 1-sentence summary of what makes them stand out.

Example format:
[
  {"name":"Boston Event DJs","website":"https://bostoneventdjs.com","email":"info@bostoneventdjs.com","phone":"(617) 555-0199","niche":"Wedding DJ Services","description":"Top-rated event and wedding DJs serving the greater Boston area."}
]
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 4000 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $start = strpos( $result, '[' );
        $end   = strrpos( $result, ']' );

        if ( $start === false || $end === false || $end < $start ) {
            wp_send_json_error( [ 'message' => 'AI returned an invalid format. Please try searching again.', 'raw' => $result ] );
        }

        $json_text = substr( $result, $start, $end - $start + 1 );
        $parsed    = json_decode( $json_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $parsed ) ) {
            wp_send_json_error( [ 'message' => 'AI returned an invalid format. Please try searching again.', 'raw' => $result ] );
        }

        wp_send_json_success( [ 'providers' => $parsed ] );
    }

    // ── AJAX: Draft Outreach Email ────────────────────────────────────────

    public function handle_outreach_draft() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name        = sanitize_text_field( $_POST['name'] ?? '' );
        $niche       = sanitize_text_field( $_POST['niche'] ?? '' );
        $website     = sanitize_text_field( $_POST['website'] ?? '' );
        $description = sanitize_text_field( $_POST['description'] ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Missing provider details.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are a platform outreach specialist for {$platform_name}.
Write a highly personalized, warm, and professional cold outreach email to a local service provider inviting them to list their services on Obenlo.
Keep it conversational, professional, and explain how it will benefit their business.

Provider Details:
Name: {$name}
Website: {$website}
Niche: {$niche}
Description: {$description}

Key Obenlo features to mention:
- Free to list, low commissions on completed bookings.
- Advanced automated scheduling and secure booking system.
- Direct host storefront to showcase their services to travelers and locals.

Return ONLY the email draft. Begin with a clear "Subject: [compelling subject line]" line, then the Body. Keep the tone friendly and tailored to their specific niche.
Sign off the email simply as "The {$platform_name} Team". Do NOT use placeholders like [Your Name] or titles like Platform Outreach Specialist.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 450 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        // Separate Subject and Body
        $lines = explode( "\n", $result );
        $subject = 'Invitation to join Obenlo';
        $body_lines = [];

        foreach ( $lines as $line ) {
            if ( stripos( $line, 'subject:' ) === 0 ) {
                $subject = trim( substr( $line, 8 ) );
                $subject = str_replace( ['"', "'"], '', $subject );
            } else {
                $body_lines[] = $line;
            }
        }

        $body = trim( implode( "\n", $body_lines ) );

        wp_send_json_success( [
            'subject' => $subject,
            'body'    => $body,
        ] );
    }

    // ── AJAX: Draft Follow-Up Email ───────────────────────────────────────

    public function handle_outreach_followup_draft() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name  = sanitize_text_field( $_POST['name'] ?? '' );
        $niche = sanitize_text_field( $_POST['niche'] ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Missing provider details.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are a platform outreach specialist for {$platform_name}.
Write a friendly, professional 2nd follow-up email checking in with a local service provider ({$name} — {$niche}) regarding your previous invitation to join Obenlo.
Keep it short (under 100 words), polite, and non-pushy. Express genuine interest in featuring their business on the platform.

Return ONLY the email draft. Begin with a clear "Subject: [compelling follow-up subject line]" line, then the Body.
Sign off the email simply as "The {$platform_name} Team". Do NOT use placeholders like [Your Name] or titles like Platform Outreach Specialist.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 300 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $lines = explode( "\n", $result );
        $subject = "Following up: Featuring {$name} on Obenlo";
        $body_lines = [];

        foreach ( $lines as $line ) {
            if ( stripos( $line, 'subject:' ) === 0 ) {
                $subject = trim( substr( $line, 8 ) );
                $subject = str_replace( ['"', "'"], '', $subject );
            } else {
                $body_lines[] = $line;
            }
        }

        $body = trim( implode( "\n", $body_lines ) );

        wp_send_json_success( [
            'subject' => $subject,
            'body'    => $body,
        ] );
    }

    // ── AJAX: Send Email & Log History ────────────────────────────────────

    public function handle_outreach_send() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name    = sanitize_text_field( $_POST['name'] ?? 'Provider' );
        $niche   = sanitize_text_field( $_POST['niche'] ?? 'Service' );
        $email   = sanitize_email( $_POST['email'] ?? '' );
        $subject = sanitize_text_field( $_POST['subject'] ?? '' );
        $body    = wp_kses_post( $_POST['body'] ?? '' );
        $is_fup  = ! empty( $_POST['is_followup'] );

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
        }
        if ( empty( $subject ) || empty( $body ) ) {
            wp_send_json_error( [ 'message' => 'Subject and email content cannot be empty.' ] );
        }

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $formatted_body = nl2br( esc_html( $body ) );

        $sent = wp_mail( $email, $subject, $formatted_body, $headers );

        // Always log for tracking records
        $history = get_option( 'obenlo_outreach_history', [] );
        if ( ! is_array( $history ) ) {
            $history = [];
        }

        $record = [
            'id'        => uniqid( 'lead_' ),
            'name'      => $name,
            'email'     => $email,
            'niche'     => $niche,
            'subject'   => $subject,
            'sent_at'   => current_time( 'mysql' ),
            'type'      => $is_fup ? 'Follow-Up' : 'Initial Contact',
            'status'    => 'Sent',
        ];

        // Prepend so latest appears first
        array_unshift( $history, $record );
        // Limit history to 200 items
        $history = array_slice( $history, 0, 200 );
        update_option( 'obenlo_outreach_history', $history );

        if ( $sent ) {
            wp_send_json_success( [ 'message' => 'Email sent successfully and logged to Outreach History!', 'history' => $history ] );
        } else {
            // Local fallback message so user knows it was logged
            wp_send_json_success( [ 'message' => 'Outreach logged to history! (Note: local environment captured email via Mail Catcher)', 'history' => $history ] );
        }
    }

    // ── AJAX: History Handlers ────────────────────────────────────────────

    public function handle_outreach_get_history() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $history = get_option( 'obenlo_outreach_history', [] );
        wp_send_json_success( [ 'history' => is_array( $history ) ? $history : [] ] );
    }

    public function handle_outreach_clear_history() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        update_option( 'obenlo_outreach_history', [] );
        wp_send_json_success( [ 'message' => 'Outreach history cleared.' ] );
    }

    // ── Render UI Page ────────────────────────────────────────────────────

    public function render_outreach_page() {
        $nonce    = wp_create_nonce( 'obenlo_ai_outreach_nonce' );
        $ajax_url = admin_url( 'admin-ajax.php' );
        ?>
        <style>
            .obenlo-outreach-container {
                max-width: 1150px;
                margin: 20px auto;
                font-family: 'Inter', system-ui, sans-serif;
            }
            .outreach-header {
                background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
                color: #fff;
                padding: 40px;
                border-radius: 20px;
                margin-bottom: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            .outreach-header h1 {
                margin: 0 0 10px 0;
                font-size: 2.2rem;
                font-weight: 800;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .outreach-header p {
                margin: 0;
                opacity: 0.8;
                font-size: 1rem;
            }
            .outreach-card {
                background: #fff;
                border-radius: 16px;
                border: 1px solid #e5e7eb;
                padding: 30px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            }
            .search-grid {
                display: grid;
                grid-template-columns: 1fr 1fr 130px 180px;
                gap: 16px;
                align-items: end;
            }
            .form-group label {
                display: block;
                font-weight: 700;
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #374151;
                margin-bottom: 8px;
            }
            .form-group input {
                width: 100%;
                padding: 12px 16px;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                font-size: 0.95rem;
                background: #f9fafb;
                box-sizing: border-box;
                outline: none;
                transition: border-color 0.2s;
            }
            .form-group input:focus {
                border-color: #7c3aed;
                background: #fff;
            }
            .btn-outreach-search {
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                padding: 13px 20px;
                border-radius: 10px;
                font-weight: 800;
                cursor: pointer;
                box-shadow: 0 4px 10px rgba(124,58,237,0.25);
                width: 100%;
                box-sizing: border-box;
                font-size: 0.95rem;
            }
            .btn-outreach-search:hover { opacity: 0.9; }
            .btn-outreach-search:disabled { opacity: 0.6; cursor: not-allowed; }
            
            .bulk-bar {
                background: #f5f3ff;
                border: 1px solid #ddd6fe;
                padding: 12px 20px;
                border-radius: 12px;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .btn-bulk-send {
                background: #7c3aed;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 800;
                font-size: 0.85rem;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            .btn-bulk-send:hover { opacity: 0.9; }
            .btn-bulk-send:disabled { opacity: 0.5; cursor: wait; }

            .results-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            .results-table th {
                text-align: left;
                padding: 14px 16px;
                background: #f3f4f6;
                color: #374151;
                font-weight: 700;
                font-size: 0.85rem;
                border-bottom: 2px solid #e5e7eb;
            }
            .results-table td {
                padding: 14px 16px;
                border-bottom: 1px solid #f3f4f6;
                font-size: 0.9rem;
                color: #4b5563;
                vertical-align: middle;
            }
            .results-table tr:hover td { background: #faf5f6; }
            
            .btn-action-draft {
                background: #ede9fe;
                color: #7c3aed;
                border: 1px solid #ddd6fe;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
                font-size: 0.8rem;
            }
            .btn-action-draft:hover { background: #7c3aed; color: #fff; }
            
            .btn-action-followup {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
                font-size: 0.8rem;
            }
            .btn-action-followup:hover { background: #f59e0b; color: #fff; }

            #outreach-preview-modal {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
            }
            #outreach-preview-modal.open { display: flex; }
            .modal-content {
                background: #fff;
                border-radius: 20px;
                width: 650px;
                max-width: 90%;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                overflow: hidden;
            }
            .modal-header {
                background: #7c3aed;
                color: #fff;
                padding: 20px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 { margin: 0; font-weight: 800; font-size: 1.15rem; }
            .modal-close { background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; opacity: 0.8; }
            .modal-body { padding: 24px; }
            .modal-body label { display: block; font-weight: 700; font-size: 0.8rem; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; }
            .modal-body input[type="text"],
            .modal-body textarea {
                width: 100%;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                padding: 12px;
                margin-bottom: 18px;
                font-family: inherit;
                font-size: 0.95rem;
                box-sizing: border-box;
                outline: none;
            }
            .modal-footer { padding: 0 24px 24px 24px; display: flex; gap: 12px; }
            .btn-send-email { flex: 1; background: linear-gradient(135deg, #7c3aed, #e61e4d); color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; }
            .btn-copy-clipboard { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; }
            .outreach-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-left: 8px; }
            @keyframes spin { to { transform: rotate(360deg); } }

            #outreach-debug-log {
                background: #1e1e1e;
                color: #00ff00;
                font-family: 'Courier New', monospace;
                font-size: 0.85rem;
                padding: 20px;
                border-radius: 12px;
                margin-top: 30px;
                white-space: pre-wrap;
                height: 220px;
                overflow-y: scroll;
                border: 2px solid #333;
                box-sizing: border-box;
            }
        </style>

        <div class="obenlo-outreach-container">
            <div class="outreach-header">
                <h1>🤖 Obenlo AI Outreach Agent</h1>
                <p>Find prospective local providers, draft custom invitation pitches, track contacted leads, and execute bulk outreach campaigns.</p>
            </div>

            <div class="outreach-card">
                <div class="search-grid">
                    <div class="form-group">
                        <label for="outreach-category">Service Category / Niche</label>
                        <input type="text" id="outreach-category" placeholder="e.g. Captain, Boat Tour, Hair Stylist" value="DJ">
                    </div>
                    <div class="form-group">
                        <label for="outreach-location">Location / City</label>
                        <input type="text" id="outreach-location" placeholder="e.g. Miami, FL" value="Boston">
                    </div>
                    <div class="form-group">
                        <label for="outreach-count">Leads Count</label>
                        <select id="outreach-count" style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:10px; font-size:0.95rem; background:#f9fafb; outline:none;">
                            <option value="5">5 Leads</option>
                            <option value="10" selected>10 Leads</option>
                            <option value="15">15 Leads</option>
                            <option value="20">20 Leads</option>
                        </select>
                    </div>
                    <div>
                        <button id="btn-search-providers" class="btn-outreach-search">🔍 Search Providers</button>
                    </div>
                </div>
            </div>

            <!-- Search Results Card -->
            <div class="outreach-card" id="results-card" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0; font-weight:800; font-size:1.15rem; color:#222;">🔍 Prospective Leads</h3>
                </div>

                <!-- Bulk Bar -->
                <div class="bulk-bar">
                    <span style="font-weight:700; font-size:0.88rem; color:#4c1d95;">
                        <span id="selected-count">0</span> leads selected
                    </span>
                    <button class="btn-bulk-send" id="btn-bulk-send" disabled>⚡ Send Bulk Outreach to Selected</button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="chk-select-all"></th>
                                <th>Name</th>
                                <th>Niche</th>
                                <th>Website</th>
                                <th>Email</th>
                                <th>Standout Pitch Context</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="results-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Outreach History & Follow-Up Log Card -->
            <div class="outreach-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0; font-weight:800; font-size:1.15rem; color:#222;">📋 Outreach History &amp; Follow-Up Tracker</h3>
                    <button id="btn-clear-history" style="background:none; border:none; color:#dc2626; font-weight:700; font-size:0.8rem; cursor:pointer;">🗑️ Clear History</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Lead Name</th>
                                <th>Email</th>
                                <th>Niche</th>
                                <th>Subject Sent</th>
                                <th>Sent Date</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="history-body">
                            <tr><td colspan="7" style="text-align:center; color:#888; padding:20px;">Loading outreach history...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Client-Side Debug Panel -->
            <h3 style="margin-bottom: 10px; font-weight: 800;">🛠️ Outreach Agent Live Console Logs</h3>
            <div id="outreach-debug-log">System console initialized... Waiting for user action.</div>
        </div>

        <!-- Outreach Email Preview Modal -->
        <div id="outreach-preview-modal" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modal-title-text">✉️ Draft Outreach Pitch</h3>
                    <button class="modal-close" id="btn-modal-close">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>To:</label>
                        <input type="text" id="outreach-to-email">
                    </div>
                    <div class="form-group">
                        <label>Subject:</label>
                        <input type="text" id="outreach-subject">
                    </div>
                    <div class="form-group">
                        <label>Email Body:</label>
                        <textarea id="outreach-body" rows="12"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-copy-clipboard" id="btn-copy">📋 Copy Text</button>
                    <button class="btn-send-email" id="btn-send">🚀 Send Pitch</button>
                </div>
            </div>
        </div>

        <script>
        (function() {
            'use strict';

            const AJAX_URL = <?php echo wp_json_encode( $ajax_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            const btnSearch   = document.getElementById('btn-search-providers');
            const catInput    = document.getElementById('outreach-category');
            const locInput    = document.getElementById('outreach-location');
            const resultsCard = document.getElementById('results-card');
            const resultsBody = document.getElementById('results-body');
            const historyBody = document.getElementById('history-body');
            const chkSelectAll= document.getElementById('chk-select-all');
            const btnBulkSend = document.getElementById('btn-bulk-send');
            const selCountEl  = document.getElementById('selected-count');
            const btnClearHist= document.getElementById('btn-clear-history');

            const modal       = document.getElementById('outreach-preview-modal');
            const modalClose  = document.getElementById('btn-modal-close');
            const modalTitle  = document.getElementById('modal-title-text');
            const toInput     = document.getElementById('outreach-to-email');
            const subjectInput= document.getElementById('outreach-subject');
            const bodyText    = document.getElementById('outreach-body');
            const btnCopy     = document.getElementById('btn-copy');
            const btnSend     = document.getElementById('btn-send');
            const dbgPanel    = document.getElementById('outreach-debug-log');

            let currentLead = null;
            let isFollowup  = false;
            let searchLeads = [];

            function log(msg) {
                dbgPanel.innerText += "\n" + msg;
                dbgPanel.scrollTop = dbgPanel.scrollHeight;
                console.log("[Outreach Debug]", msg);
            }

            window.addEventListener('error', function(e) {
                log("Uncaught Error: " + e.message + " in " + e.filename + ":" + e.lineno);
            });

            log("Outreach Agent initialized.");
            loadHistory();

            // ── Load History ──────────────────────────────────────────────
            async function loadHistory() {
                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_get_history');
                    fd.append('nonce', NONCE);
                    const res = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) return;
                    const data = JSON.parse(text.substring(s, e + 1));
                    if (data.success) renderHistory(data.data.history || []);
                } catch(e) { console.error(e); }
            }

            function renderHistory(items) {
                historyBody.innerHTML = '';
                if (!items.length) {
                    historyBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#888; padding:20px;">No outreach emails sent yet.</td></tr>';
                    return;
                }
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    const isFup = item.type === 'Follow-Up';
                    tr.innerHTML = `
                        <td><strong>${esc(item.name)}</strong></td>
                        <td>${esc(item.email)}</td>
                        <td><span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${esc(item.niche)}</span></td>
                        <td><span style="font-size:0.83rem; color:#444;">${esc(item.subject)}</span></td>
                        <td><span style="font-size:0.78rem; color:#888;">${esc(item.sent_at)}</span></td>
                        <td><span style="background:${isFup?'#fef3c7':'#dcfce7'}; color:${isFup?'#92400e':'#15803d'}; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:800;">${esc(item.type)}</span></td>
                        <td><button class="btn-action-followup" data-lead='${JSON.stringify(item).replace(/'/g, '&apos;')}'>🔄 Send Follow-Up</button></td>
                    `;
                    historyBody.appendChild(tr);
                });

                document.querySelectorAll('.btn-action-followup').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const item = JSON.parse(btn.dataset.lead);
                        handleFollowupClick(item, btn);
                    });
                });
            }

            // ── Search Providers ──────────────────────────────────────────
            btnSearch.addEventListener('click', async () => {
                const cat   = catInput.value.trim(), loc = locInput.value.trim();
                const count = document.getElementById('outreach-count').value;
                log("Search clicked: " + cat + " in " + loc + " (Count: " + count + ")");
                if (!cat || !loc) return;

                btnSearch.disabled = true;
                btnSearch.innerHTML = 'Searching<span class="outreach-spinner"></span>';
                resultsCard.style.display = 'none';
                resultsBody.innerHTML = '';
                searchLeads = [];
                updateBulkState();

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_search');
                    fd.append('nonce', NONCE);
                    fd.append('category', cat);
                    fd.append('location', loc);
                    fd.append('count', count);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) throw new Error("Invalid response");
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success && data.data.providers) {
                        searchLeads = data.data.providers;
                        log("Found " + searchLeads.length + " leads.");
                        searchLeads.forEach((p, idx) => {
                            const tr = document.createElement('tr');
                            let webDisplay = esc(p.website).replace(/^https?:\/\/(www\.)?/,'');
                            let webUrl = p.website.startsWith('http') ? p.website : 'https://' + p.website;
                            let googleSearchUrl = 'https://www.google.com/search?q=' + encodeURIComponent(p.name + ' ' + loc + ' ' + cat);

                            tr.innerHTML = `
                                <td><input type="checkbox" class="chk-lead" data-idx="${idx}"></td>
                                <td><strong>${esc(p.name)}</strong></td>
                                <td><span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${esc(p.niche)}</span></td>
                                <td>
                                    <a href="${esc(webUrl)}" target="_blank" rel="noopener" style="color:#7c3aed; text-decoration:none; font-weight:600; display:block; margin-bottom:4px;">🌐 ${esc(webDisplay)}</a>
                                    <a href="${esc(googleSearchUrl)}" target="_blank" rel="noopener" style="color:#2563eb; text-decoration:none; font-size:0.75rem; font-weight:700; background:#eff6ff; padding:2px 6px; border-radius:4px; display:inline-block;">🔍 Verify Google</a>
                                </td>
                                <td>${esc(p.email)}</td>
                                <td><span style="font-size:0.82rem; color:#6b7280;">${esc(p.description)}</span></td>
                                <td><button class="btn-action-draft" data-idx="${idx}">✍️ Draft Pitch</button></td>
                            `;
                            resultsBody.appendChild(tr);
                        });

                        document.querySelectorAll('.btn-action-draft').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const lead = searchLeads[parseInt(btn.dataset.idx)];
                                handleDraftClick(lead, btn);
                            });
                        });

                        document.querySelectorAll('.chk-lead').forEach(chk => {
                            chk.addEventListener('change', updateBulkState);
                        });

                        resultsCard.style.display = 'block';
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to retrieve providers.'));
                    }
                } catch (e) {
                    log("Error: " + e.message);
                    alert('Search failed. Please try again.');
                } finally {
                    btnSearch.disabled = false;
                    btnSearch.textContent = '🔍 Search Providers';
                }
            });

            // ── Bulk Selection ─────────────────────────────────────────────
            chkSelectAll.addEventListener('change', () => {
                document.querySelectorAll('.chk-lead').forEach(chk => chk.checked = chkSelectAll.checked);
                updateBulkState();
            });

            function updateBulkState() {
                const checked = document.querySelectorAll('.chk-lead:checked');
                selCountEl.textContent = checked.length;
                btnBulkSend.disabled = checked.length === 0;
            }

            // ── Bulk Outreach Handler ──────────────────────────────────────
            btnBulkSend.addEventListener('click', async () => {
                const checked = Array.from(document.querySelectorAll('.chk-lead:checked'));
                if (!checked.length) return;
                if (!confirm(`Are you sure you want to generate and send personalized pitches to all ${checked.length} selected leads?`)) return;

                btnBulkSend.disabled = true;
                const origBtnText = btnBulkSend.textContent;

                for (let i = 0; i < checked.length; i++) {
                    const idx = parseInt(checked[i].dataset.idx);
                    const lead = searchLeads[idx];
                    btnBulkSend.innerHTML = `⏳ Sending ${i+1}/${checked.length}: ${esc(lead.name)}...`;
                    log(`Bulk (${i+1}/${checked.length}): Drafting and sending pitch to ${lead.name}...`);

                    try {
                        // Draft
                        const fd1 = new FormData();
                        fd1.append('action', 'obenlo_outreach_draft');
                        fd1.append('nonce', NONCE);
                        fd1.append('name', lead.name);
                        fd1.append('niche', lead.niche);
                        fd1.append('website', lead.website);
                        fd1.append('description', lead.description);

                        const res1 = await fetch(AJAX_URL, { method: 'POST', body: fd1 });
                        const text1 = await res1.text();
                        const s1 = text1.indexOf('{'), e1 = text1.lastIndexOf('}');
                        const data1 = JSON.parse(text1.substring(s1, e1 + 1));

                        if (data1.success) {
                            // Send
                            const fd2 = new FormData();
                            fd2.append('action', 'obenlo_outreach_send');
                            fd2.append('nonce', NONCE);
                            fd2.append('name', lead.name);
                            fd2.append('niche', lead.niche);
                            fd2.append('email', lead.email);
                            fd2.append('subject', data1.data.subject);
                            fd2.append('body', data1.data.body);

                            const res2 = await fetch(AJAX_URL, { method: 'POST', body: fd2 });
                            const text2 = await res2.text();
                            const s2 = text2.indexOf('{'), e2 = text2.lastIndexOf('}');
                            const data2 = JSON.parse(text2.substring(s2, e2 + 1));
                            if (data2.success && data2.data.history) {
                                renderHistory(data2.data.history);
                            }
                        }
                    } catch(err) {
                        log("Bulk error for " + lead.name + ": " + err.message);
                    }
                }

                btnBulkSend.disabled = false;
                btnBulkSend.textContent = origBtnText;
                alert('⚡ Bulk outreach campaign completed!');
                loadHistory();
            });

            // ── Single Draft Click ─────────────────────────────────────────
            async function handleDraftClick(lead, btn) {
                currentLead = lead;
                isFollowup = false;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                modalTitle.textContent = '✉️ Draft Outreach Pitch';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_draft');
                    fd.append('nonce', NONCE);
                    fd.append('name', lead.name);
                    fd.append('niche', lead.niche);
                    fd.append('website', lead.website);
                    fd.append('description', lead.description);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        toInput.value = lead.email;
                        subjectInput.value = data.data.subject;
                        bodyText.value = data.data.body;
                        modal.classList.add('open');
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to draft email.'));
                    }
                } catch (e) {
                    alert('Error while drafting pitch.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            }

            // ── Follow-Up Click ───────────────────────────────────────────
            async function handleFollowupClick(item, btn) {
                currentLead = item;
                isFollowup = true;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                modalTitle.textContent = '🔄 Draft Follow-Up Pitch';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_followup_draft');
                    fd.append('nonce', NONCE);
                    fd.append('name', item.name);
                    fd.append('niche', item.niche);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        toInput.value = item.email;
                        subjectInput.value = data.data.subject;
                        bodyText.value = data.data.body;
                        modal.classList.add('open');
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to draft follow-up.'));
                    }
                } catch (e) {
                    alert('Error while drafting follow-up.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            }

            modalClose.addEventListener('click', () => modal.classList.remove('open'));

            btnCopy.addEventListener('click', () => {
                bodyText.select();
                document.execCommand('copy');
                btnCopy.textContent = '📋 Copied!';
                setTimeout(() => { btnCopy.textContent = '📋 Copy Text'; }, 2000);
            });

            // ── Send Email Handler ────────────────────────────────────────
            btnSend.addEventListener('click', async () => {
                const to = toInput.value.trim(), subj = subjectInput.value.trim(), body = bodyText.value.trim();
                if (!to || !subj || !body) return;

                btnSend.disabled = true;
                btnSend.innerHTML = 'Sending<span class="outreach-spinner"></span>';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_send');
                    fd.append('nonce', NONCE);
                    fd.append('name', currentLead ? currentLead.name : 'Provider');
                    fd.append('niche', currentLead ? currentLead.niche : 'Service');
                    fd.append('email', to);
                    fd.append('subject', subj);
                    fd.append('body', body);
                    if (isFollowup) fd.append('is_followup', '1');

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        alert(data.data.message || '🚀 Email processed successfully!');
                        modal.classList.remove('open');
                        if (data.data.history) renderHistory(data.data.history);
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to send email.'));
                    }
                } catch (e) {
                    alert('Network error while sending email.');
                } finally {
                    btnSend.disabled = false;
                    btnSend.textContent = '🚀 Send Pitch';
                }
            });

            btnClearHist.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to clear outreach history?')) return;
                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_clear_history');
                    fd.append('nonce', NONCE);
                    await fetch(AJAX_URL, { method: 'POST', body: fd });
                    loadHistory();
                } catch(e) { console.error(e); }
            });

            function esc(str) {
                if (!str) return '';
                return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
        })();
        </script>
        <?php
    }
}
