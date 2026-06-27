<?php
/**
 * Obenlo AI Outreach Agent
 *
 * Provides a dedicated administrator dashboard tab to search for local service
 * providers, generate custom pitch emails, and contact them.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Outreach {

    public function init() {
        // Register Admin Submenu Page under Obenlo Dash parent
        add_action( 'admin_menu', [ $this, 'register_outreach_menu' ], 30 );

        // AJAX handlers
        add_action( 'wp_ajax_obenlo_outreach_search', [ $this, 'handle_outreach_search' ] );
        add_action( 'wp_ajax_obenlo_outreach_draft', [ $this, 'handle_outreach_draft' ] );
        add_action( 'wp_ajax_obenlo_outreach_send', [ $this, 'handle_outreach_send' ] );
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

        if ( empty( $category ) || empty( $location ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in both Category and Location.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are an expert lead generator and business intelligence agent for {$platform_name}, a global service and experience marketplace.
Search for or generate 5 active and high-quality local service providers matching:
Category: {$category}
Location: {$location}

Return ONLY a valid JSON array of objects (no markdown, no explanations, no wrapping in ```json). Each object must contain exactly these keys:
- "name": The business or provider's name.
- "website": A plausible website URL.
- "email": A contact email address (either their real one, or formatted as contact@domain.com).
- "phone": A contact phone number.
- "niche": The specific sub-specialty (e.g. "Deep Tissue Massage", "Guided Hikes").
- "description": A short 1-sentence summary of what makes them stand out.

Example format:
[
  {"name":"Miami Sail Charters","website":"https://miamisailcharters.com","email":"info@miamisailcharters.com","phone":"(305) 555-0199","niche":"Sunset Sailings","description":"Premium private catamaran charters for couples and groups."}
]
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 600 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $clean = preg_replace( '/^```(?:json)?\s*/i', '', trim( $result ) );
        $clean = preg_replace( '/\s*```$/', '', $clean );

        $parsed = json_decode( $clean, true );

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

    // ── AJAX: Send Email ──────────────────────────────────────────────────

    public function handle_outreach_send() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $email   = sanitize_email( $_POST['email'] ?? '' );
        $subject = sanitize_text_field( $_POST['subject'] ?? '' );
        $body    = wp_kses_post( $_POST['body'] ?? '' );

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
        }
        if ( empty( $subject ) || empty( $body ) ) {
            wp_send_json_error( [ 'message' => 'Subject and email content cannot be empty.' ] );
        }

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $formatted_body = nl2br( esc_html( $body ) );

        $sent = wp_mail( $email, $subject, $formatted_body, $headers );

        if ( $sent ) {
            wp_send_json_success( [ 'message' => 'Email sent successfully!' ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to send email. Check your WordPress mail configuration.' ] );
        }
    }

    // ── Render UI Page ────────────────────────────────────────────────────

    public function render_outreach_page() {
        $nonce = wp_create_nonce( 'obenlo_ai_outreach_nonce' );
        $ajax_url = admin_url( 'admin-ajax.php' );
        ?>
        <style>
            .obenlo-outreach-container {
                max-width: 1100px;
                margin: 20px auto;
                font-family: 'Inter', sans-serif;
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
                grid-template-columns: 1fr 1fr 180px;
                gap: 20px;
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
            .btn-outreach-search:hover {
                opacity: 0.9;
            }
            .btn-outreach-search:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .results-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
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
                padding: 16px;
                border-bottom: 1px solid #f3f4f6;
                font-size: 0.9rem;
                color: #4b5563;
                vertical-align: middle;
            }
            .results-table tr:hover td {
                background: #faf5f6;
            }
            .btn-action-draft {
                background: #ede9fe;
                color: #7c3aed;
                border: 1px solid #ddd6fe;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
                font-size: 0.8rem;
                transition: background 0.2s, color 0.2s;
            }
            .btn-action-draft:hover {
                background: #7c3aed;
                color: #fff;
            }
            .btn-action-draft:disabled {
                opacity: 0.5;
                cursor: wait;
            }
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
            #outreach-preview-modal.open {
                display: flex;
            }
            .modal-content {
                background: #fff;
                border-radius: 20px;
                width: 650px;
                max-width: 90%;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                overflow: hidden;
                animation: modalFade 0.25s ease;
            }
            @keyframes modalFade {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .modal-header {
                background: #7c3aed;
                color: #fff;
                padding: 20px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 {
                margin: 0;
                font-weight: 800;
                font-size: 1.15rem;
            }
            .modal-close {
                background: none;
                border: none;
                color: #fff;
                font-size: 1.5rem;
                cursor: pointer;
                line-height: 1;
                opacity: 0.8;
            }
            .modal-close:hover { opacity: 1; }
            .modal-body {
                padding: 24px;
            }
            .modal-body label {
                display: block;
                font-weight: 700;
                font-size: 0.8rem;
                color: #4b5563;
                margin-bottom: 6px;
                text-transform: uppercase;
            }
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
            .modal-body input[type="text"]:focus,
            .modal-body textarea:focus {
                border-color: #7c3aed;
            }
            .modal-footer {
                padding: 0 24px 24px 24px;
                display: flex;
                gap: 12px;
            }
            .btn-send-email {
                flex: 1;
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                padding: 12px 20px;
                border-radius: 10px;
                font-weight: 800;
                cursor: pointer;
            }
            .btn-copy-clipboard {
                background: #f3f4f6;
                color: #374151;
                border: 1px solid #d1d5db;
                padding: 12px 20px;
                border-radius: 10px;
                font-weight: 700;
                cursor: pointer;
            }
            .outreach-spinner {
                display: inline-block;
                width: 18px;
                height: 18px;
                border: 3px solid rgba(255,255,255,0.3);
                border-top-color: #fff;
                border-radius: 50%;
                animation: spin 0.6s linear infinite;
                vertical-align: middle;
                margin-left: 10px;
            }
            @keyframes spin { to { transform: rotate(360deg); } }

            /* Debug Panel - Larger & Readable */
            #outreach-debug-log {
                background: #1e1e1e;
                color: #00ff00;
                font-family: 'Courier New', monospace;
                font-size: 0.85rem;
                padding: 20px;
                border-radius: 12px;
                margin-top: 30px;
                white-space: pre-wrap;
                height: 300px;
                overflow-y: scroll;
                border: 2px solid #333;
                box-sizing: border-box;
            }
        </style>

        <div class="obenlo-outreach-container">
            <div class="outreach-header">
                <h1>🤖 Obenlo AI Outreach Agent</h1>
                <p>Find service providers in target niches and locations, then draft custom invitation pitches to list them on Obenlo.</p>
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
                    <div>
                        <button id="btn-search-providers" class="btn-outreach-search">🔍 Search Providers</button>
                    </div>
                </div>
            </div>

            <div class="outreach-card" id="results-card" style="display:none;">
                <h3 style="margin-top:0; font-weight:800; font-size:1.15rem; color:#222; margin-bottom:15px;">🔍 Prospective Leads</h3>
                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
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

            <!-- Client-Side Debug Panel -->
            <h3 style="margin-bottom: 10px; font-weight: 800;">🛠️ Outreach Agent Live Console Logs</h3>
            <div id="outreach-debug-log">System console initialized... Waiting for user action.</div>
        </div>

        <!-- Outreach Email Preview Modal -->
        <div id="outreach-preview-modal" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✉️ Draft Outreach Pitch</h3>
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

            const modal       = document.getElementById('outreach-preview-modal');
            const modalClose  = document.getElementById('btn-modal-close');
            const toInput     = document.getElementById('outreach-to-email');
            const subjectInput= document.getElementById('outreach-subject');
            const bodyText    = document.getElementById('outreach-body');
            const btnCopy     = document.getElementById('btn-copy');
            const btnSend     = document.getElementById('btn-send');
            const dbgPanel    = document.getElementById('outreach-debug-log');

            // Track the currently selected lead for the modal
            let currentLead = null;

            function log(msg) {
                dbgPanel.innerText += "\n" + msg;
                dbgPanel.scrollTop = dbgPanel.scrollHeight;
                console.log("[Outreach Debug]", msg);
            }

            // Capture all JS errors
            window.addEventListener('error', function(e) {
                log("Uncaught Error: " + e.message + " in " + e.filename + ":" + e.lineno);
            });

            log("Variables loaded successfully.");

            btnSearch.addEventListener('click', async () => {
                const cat = catInput.value.trim();
                const loc = locInput.value.trim();
                log("Search clicked. Category: " + cat + " | Location: " + loc);
                if (!cat || !loc) {
                    log("Aborting: missing inputs.");
                    return;
                }

                btnSearch.disabled = true;
                btnSearch.innerHTML = 'Searching<span class="outreach-spinner"></span>';
                resultsCard.style.display = 'none';
                resultsBody.innerHTML = '';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_search');
                    fd.append('nonce', NONCE);
                    fd.append('category', cat);
                    fd.append('location', loc);

                    log("Sending fetch request to " + AJAX_URL);
                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    log("Fetch completed with status: " + res.status);
                    
                    const text = await res.text();
                    log("Raw Response length: " + text.length + " characters.");
                    log("Raw Response start: " + text.substring(0, 500));

                    // Parse JSON by extracting the JSON segment to bypass php notices
                    let data;
                    try {
                        const firstBrace = text.indexOf('{');
                        const lastBrace  = text.lastIndexOf('}');
                        if (firstBrace === -1 || lastBrace === -1) {
                            throw new Error("No JSON object found in response");
                        }
                        const jsonText = text.substring(firstBrace, lastBrace + 1);
                        data = JSON.parse(jsonText);
                        log("JSON successfully parsed.");
                    } catch(jsonErr) {
                        log("JSON Parse Error: " + jsonErr.message);
                        log("Full Raw response: " + text);
                        alert("Invalid server response. Check the debug log below.");
                        return;
                    }

                    if (data.success && data.data.providers) {
                        log("Success! Found " + data.data.providers.length + " leads.");
                        data.data.providers.forEach(p => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong>${esc(p.name)}</strong></td>
                                <td><span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${esc(p.niche)}</span></td>
                                <td><a href="${esc(p.website)}" target="_blank" style="color:#7c3aed; text-decoration:none; font-weight:600;">${esc(p.website.replace(/^https?:\/\/(www\.)?/,''))}</a></td>
                                <td>${esc(p.email)}</td>
                                <td><span style="font-size:0.82rem; color:#6b7280;">${esc(p.description)}</span></td>
                                <td><button class="btn-action-draft" data-lead='${JSON.stringify(p).replace(/'/g, '&apos;')}'>✍️ Draft Outreach</button></td>
                            `;
                            resultsBody.appendChild(tr);
                        });

                        document.querySelectorAll('.btn-action-draft').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                const lead = JSON.parse(btn.dataset.lead);
                                handleDraftClick(lead, btn);
                            });
                        });

                        resultsCard.style.display = 'block';
                    } else {
                        log("Error from server callback: " + (data.data?.message || 'Unknown error'));
                        alert('Error: ' + (data.data?.message || 'Failed to retrieve providers.'));
                    }
                } catch (e) {
                    log("Network/Exception Error: " + e.message);
                    alert('Network error while searching.');
                    console.error(e);
                } finally {
                    btnSearch.disabled = false;
                    btnSearch.textContent = '🔍 Search Providers';
                }
            });

            async function handleDraftClick(lead, btn) {
                currentLead = lead;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                log("Drafting outreach email for: " + lead.name);

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
                    
                    let data;
                    try {
                        const firstBrace = text.indexOf('{');
                        const lastBrace  = text.lastIndexOf('}');
                        const jsonText = text.substring(firstBrace, lastBrace + 1);
                        data = JSON.parse(jsonText);
                    } catch(err) {
                        log("Draft JSON Parse Error: " + err.message);
                        log("Full Raw response: " + text);
                        alert("Failed to parse drafted response. Check the log.");
                        return;
                    }

                    if (data.success) {
                        log("Outreach email drafted successfully.");
                        toInput.value = lead.email;
                        subjectInput.value = data.data.subject;
                        bodyText.value = data.data.body;
                        modal.classList.add('open');
                    } else {
                        log("Error drafting email: " + (data.data?.message || 'Unknown error'));
                        alert('Error: ' + (data.data?.message || 'Failed to draft email.'));
                    }
                } catch (e) {
                    log("Error during drafting AJAX: " + e.message);
                    alert('Network error while drafting pitch.');
                    console.error(e);
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

            btnSend.addEventListener('click', async () => {
                const to = toInput.value.trim();
                const subj = subjectInput.value.trim();
                const body = bodyText.value.trim();

                if (!to || !subj || !body) return;

                btnSend.disabled = true;
                btnSend.innerHTML = 'Sending<span class="outreach-spinner"></span>';
                log("Sending email to: " + to);

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_send');
                    fd.append('nonce', NONCE);
                    fd.append('email', to);
                    fd.append('subject', subj);
                    fd.append('body', body);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    
                    let data;
                    try {
                        const firstBrace = text.indexOf('{');
                        const lastBrace  = text.lastIndexOf('}');
                        const jsonText = text.substring(firstBrace, lastBrace + 1);
                        data = JSON.parse(jsonText);
                    } catch(err) {
                        log("Send JSON Parse Error: " + err.message);
                        log("Full Raw response: " + text);
                        alert("Failed to parse send response.");
                        return;
                    }

                    if (data.success) {
                        log("Outreach sent successfully!");
                        alert('🚀 Outreach email sent successfully!');
                        modal.classList.remove('open');
                    } else {
                        log("Error sending email: " + (data.data?.message || 'Unknown error'));
                        alert('Error: ' + (data.data?.message || 'Failed to send email.'));
                    }
                } catch (e) {
                    log("Error during sending AJAX: " + e.message);
                    alert('Network error while sending email.');
                    console.error(e);
                } finally {
                    btnSend.disabled = false;
                    btnSend.textContent = '🚀 Send Pitch';
                }
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
