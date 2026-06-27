<?php
/**
 * Obenlo AI Listing
 *
 * Hooks into the listing creation/edit flow to offer:
 *   1. AI-powered title suggestions.
 *   2. AI-powered description optimization.
 *   3. SEO meta description generation.
 *
 * Integrates non-destructively: the host reviews the AI suggestion and
 * chooses to accept or dismiss — the original data is never overwritten
 * without explicit host action.
 *
 * Hook used: after 'obenlo_dashboard_save_listing' (admin-post.php)
 * UI injection: inside the listing form via wp_footer on dashboard pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Listing {

    public function init() {
        // AJAX: Optimize listing content on demand (host clicks a button)
        add_action( 'wp_ajax_obenlo_ai_optimize_listing', [ $this, 'handle_optimize_listing' ] );

        // AJAX: Generate a better listing title
        add_action( 'wp_ajax_obenlo_ai_suggest_title', [ $this, 'handle_suggest_title' ] );

        // AJAX: Generate SEO meta description
        add_action( 'wp_ajax_obenlo_ai_seo_meta', [ $this, 'handle_seo_meta' ] );

        // AJAX: Create a full listing draft from a plain-English idea
        add_action( 'wp_ajax_obenlo_ai_create_listing', [ $this, 'handle_create_listing' ] );

        // Inject the AI Listing UI into the host dashboard
        add_action( 'wp_footer', [ $this, 'inject_ai_listing_ui' ], 99 );
    }

    // ── AJAX: Optimize Description ────────────────────────────────────────

    public function handle_optimize_listing() {
        check_ajax_referer( 'obenlo_ai_listing_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ] );
        }

        $raw_description = sanitize_textarea_field( $_POST['description'] ?? '' );
        $listing_type    = sanitize_text_field( $_POST['listing_type'] ?? 'service' );
        $listing_title   = sanitize_text_field( $_POST['listing_title'] ?? '' );
        $parent_id       = intval( $_POST['parent_id'] ?? 0 );
        $parent_title    = sanitize_text_field( $_POST['parent_title'] ?? '' );
        $is_editing      = ! empty( $_POST['listing_id'] );

        if ( empty( $raw_description ) ) {
            wp_send_json_error( [ 'message' => 'Please write a description first, then click Optimize.' ] );
        }

        $platform_name = get_bloginfo( 'name' );
        $context_note  = '';
        if ( $parent_id > 0 && ! empty( $parent_title ) ) {
            $context_note = "\nNote: This is a sub-option / child listing under the main parent listing \"{$parent_title}\". Make sure the description highlights how this specific option or package complements the main offering.";
        } elseif ( $is_editing ) {
            $context_note = "\nNote: The host is updating an existing active listing. Focus on polishing, refining, and making the copy more engaging and high-converting.";
        }

        $prompt = <<<PROMPT
You are an expert copywriter for {$platform_name}, a global service marketplace.
A host has written the following description for their listing ("{$listing_title}" — Category: {$listing_type}).{$context_note}

Original description:
"{$raw_description}"

Rewrite this description to be:
- Compelling, warm, and professional
- Highlight unique selling points
- Between 80–150 words
- Written in second person (addressing the guest as "you")
- Free of filler phrases like "welcome to our...", "look no further", "state-of-the-art"

Return ONLY the improved description. No labels, no preamble.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 300 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'optimized' => $result ] );
    }

    // ── AJAX: Suggest Title ───────────────────────────────────────────────

    public function handle_suggest_title() {
        check_ajax_referer( 'obenlo_ai_listing_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ] );
        }

        $current_title   = sanitize_text_field( $_POST['current_title'] ?? '' );
        $listing_type    = sanitize_text_field( $_POST['listing_type'] ?? 'service' );
        $location        = sanitize_text_field( $_POST['location'] ?? '' );
        $parent_id       = intval( $_POST['parent_id'] ?? 0 );
        $parent_title    = sanitize_text_field( $_POST['parent_title'] ?? '' );

        if ( empty( $current_title ) ) {
            wp_send_json_error( [ 'message' => 'Please enter a title first.' ] );
        }

        $location_hint = $location ? " located in {$location}" : '';
        $context_note  = '';
        if ( $parent_id > 0 && ! empty( $parent_title ) ) {
            $context_note = "\nNote: This is a sub-option / child listing under parent listing \"{$parent_title}\". Generate titles suitable specifically for a sub-option, room, or package under \"{$parent_title}\".";
        }

        $prompt = <<<PROMPT
You are a top-tier marketplace listing specialist. 
Generate 3 short, catchy, and search-optimized listing titles for a "{$listing_type}" listing{$location_hint}.{$context_note}
Current title: "{$current_title}"

Rules:
- Each title must be under 60 characters
- Avoid generic words like "amazing", "best", "luxury" unless specific
- Use power words that convert (e.g., "Curated", "Handcrafted", "Private", "Authentic")
- Format: return only the 3 titles, each on its own line, numbered 1. 2. 3.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 150 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        // Parse numbered list into array
        $lines      = array_filter( array_map( 'trim', explode( "\n", $result ) ) );
        $titles     = [];
        foreach ( $lines as $line ) {
            $clean = preg_replace( '/^\d+[\.\)]\s*/', '', $line );
            if ( $clean ) {
                $titles[] = $clean;
            }
        }

        wp_send_json_success( [ 'titles' => $titles ] );
    }

    // ── AJAX: SEO Meta Description ────────────────────────────────────────

    public function handle_seo_meta() {
        check_ajax_referer( 'obenlo_ai_listing_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ] );
        }

        $listing_title   = sanitize_text_field( $_POST['listing_title'] ?? '' );
        $description     = sanitize_textarea_field( $_POST['description'] ?? '' );
        $listing_type    = sanitize_text_field( $_POST['listing_type'] ?? 'service' );

        $prompt = <<<PROMPT
Write a concise SEO meta description (under 155 characters) for a "{$listing_type}" listing titled "{$listing_title}".

Context: {$description}

Rules:
- Must be under 155 characters
- Include a clear benefit or call to action
- Do not start with "This listing..." or "Welcome..."
- Return ONLY the meta description text.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 100 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'meta' => substr( trim( $result ), 0, 155 ) ] );
    }

    // ── AJAX: Create Listing from Idea ────────────────────────────────────

    public function handle_create_listing() {
        check_ajax_referer( 'obenlo_ai_listing_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ] );
        }

        $idea          = sanitize_textarea_field( $_POST['listing_idea'] ?? '' );
        $listing_type  = sanitize_text_field( $_POST['listing_type'] ?? '' );
        $location      = sanitize_text_field( $_POST['location'] ?? '' );
        $parent_id     = intval( $_POST['parent_id'] ?? 0 );
        $parent_title  = sanitize_text_field( $_POST['parent_title'] ?? '' );
        $is_editing    = ! empty( $_POST['listing_id'] );

        if ( empty( $idea ) ) {
            wp_send_json_error( [ 'message' => 'Please describe your listing idea first.' ] );
        }

        $platform_name = get_bloginfo( 'name' );
        $type_hint     = $listing_type ? "Category: {$listing_type}. " : '';
        $loc_hint      = $location     ? "Location: {$location}. "     : '';
        $context_note  = '';
        if ( $parent_id > 0 && ! empty( $parent_title ) ) {
            $context_note = "\nIMPORTANT CONTEXT: This is a CHILD sub-option / room / package under the main parent listing \"{$parent_title}\". Generate a sub-option draft tailored specifically as an option or sub-unit of \"{$parent_title}\".";
        } elseif ( $is_editing ) {
            $context_note = "\nIMPORTANT CONTEXT: The host is refining an EXISTING active listing. Upgrade and expand their listing draft based on their idea.";
        }

        $prompt = <<<PROMPT
You are an expert marketplace listing writer for {$platform_name}, a global service & experience marketplace.
A host has the following idea for a listing:

"{$idea}"
{$type_hint}{$loc_hint}{$context_note}

Generate a complete, professional, conversion-optimised listing draft. Respond ONLY with a valid JSON object (no markdown, no preamble) using exactly these keys:
- "title": A compelling listing title under 65 characters. Use power words (e.g. Private, Authentic, Curated). No generic filler.
- "description": A warm, guest-focused description of 100–160 words. Written in second person ("you"). Highlights unique selling points. No filler phrases.
- "seo_meta": An SEO meta description under 155 characters with a clear benefit or call to action.
- "pricing_model_hint": The most appropriate pricing model from this list — per_night, per_day, per_hour, per_session, per_person, per_event, flat_fee, inquiry_only — choose ONE.

Example output format:
{"title":"Private Sunset Sail for Two – Tulum Coastline","description":"Drift along...","seo_meta":"Book a private...","pricing_model_hint":"per_person"}
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 500 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        // Strip markdown fences if present
        $clean = preg_replace( '/^```(?:json)?\s*/i', '', trim( $result ) );
        $clean = preg_replace( '/\s*```$/', '', $clean );

        $parsed = json_decode( $clean, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $parsed ) ) {
            wp_send_json_error( [ 'message' => 'AI returned an unexpected format. Please try again.' ] );
        }

        wp_send_json_success( [
            'title'               => sanitize_text_field( $parsed['title']               ?? '' ),
            'description'         => sanitize_textarea_field( $parsed['description']     ?? '' ),
            'seo_meta'            => sanitize_text_field( substr( $parsed['seo_meta'] ?? '', 0, 155 ) ),
            'pricing_model_hint'  => sanitize_key( $parsed['pricing_model_hint']         ?? '' ),
        ] );
    }

    // ── UI Injection ──────────────────────────────────────────────────────

    public function inject_ai_listing_ui() {
        // Only inject on the host dashboard listing form
        if ( ! is_user_logged_in() ) {
            return;
        }

        $action = $_GET['action'] ?? '';
        if ( ! in_array( $action, [ 'add', 'edit' ], true ) ) {
            return;
        }

        // Only on pages that contain the listing form
        $listing_id   = isset( $_GET['listing_id'] ) ? intval( $_GET['listing_id'] ) : 0;
        $parent_id    = isset( $_GET['parent_id'] )  ? intval( $_GET['parent_id'] )  : 0;

        if ( $listing_id > 0 ) {
            $post = get_post( $listing_id );
            if ( $post ) {
                $parent_id = $post->post_parent ?: $parent_id;
            }
        }

        $parent_title = '';
        if ( $parent_id > 0 ) {
            $parent_title = get_the_title( $parent_id );
        }

        $is_editing = $listing_id > 0;

        $modal_badge = '';
        if ( $parent_id > 0 ) {
            $modal_badge = '<div style="font-size:0.75rem; background:#ede9fe; color:#6d28d9; padding:4px 10px; border-radius:6px; font-weight:700; margin-bottom:10px; display:inline-block;">🔗 Sub-Option for: ' . esc_html( $parent_title ) . '</div>';
        } elseif ( $is_editing ) {
            $modal_badge = '<div style="font-size:0.75rem; background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:6px; font-weight:700; margin-bottom:10px; display:inline-block;">✏️ Editing Existing Listing</div>';
        } else {
            $modal_badge = '<div style="font-size:0.75rem; background:#feefc3; color:#b45309; padding:4px 10px; border-radius:6px; font-weight:700; margin-bottom:10px; display:inline-block;">✨ New Main Listing</div>';
        }

        $ajax_url     = admin_url( 'admin-ajax.php' );
        $nonce        = wp_create_nonce( 'obenlo_ai_listing_nonce' );
        ?>
        <style id="obenlo-ai-listing-styles">
            #obenlo-ai-listing-panel {
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
            #obenlo-ai-listing-fab {
                background: linear-gradient(135deg, #7c3aed 0%, #e61e4d 100%);
                color: #fff;
                border: none;
                border-radius: 50px;
                padding: 12px 20px;
                font-weight: 800;
                font-size: 0.9rem;
                cursor: pointer;
                box-shadow: 0 8px 24px rgba(230,30,77,0.35);
                transition: transform 0.2s, box-shadow 0.2s;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            #obenlo-ai-listing-fab:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 32px rgba(230,30,77,0.45);
            }
            #obenlo-ai-listing-modal {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.15);
                width: 380px;
                max-height: 80vh;
                overflow-y: auto;
                padding: 24px;
                display: none;
                border: 1px solid #f0f0f0;
                animation: obenloAISlideUp 0.2s ease;
            }
            #obenlo-ai-listing-modal.open { display: block; }
            @keyframes obenloAISlideUp {
                from { opacity: 0; transform: translateY(10px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .obenlo-ai-modal-title {
                font-size: 1rem;
                font-weight: 800;
                color: #111;
                margin: 0 0 6px 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .obenlo-ai-action-btn {
                width: 100%;
                padding: 11px 16px;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                background: #fafafa;
                font-weight: 700;
                font-size: 0.85rem;
                cursor: pointer;
                text-align: left;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
                transition: background 0.15s, border-color 0.15s;
                color: #222;
            }
            .obenlo-ai-action-btn:hover {
                background: #fff0f3;
                border-color: #e61e4d;
                color: #e61e4d;
            }
            .obenlo-ai-result {
                background: #f8f4ff;
                border: 1px solid #ddd6fe;
                border-radius: 10px;
                padding: 14px;
                font-size: 0.85rem;
                color: #1e1b4b;
                line-height: 1.6;
                margin-top: 12px;
                display: none;
            }
            .obenlo-ai-result.visible { display: block; }
            .obenlo-ai-accept-btn {
                background: #e61e4d;
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 8px 16px;
                font-weight: 700;
                font-size: 0.8rem;
                cursor: pointer;
                margin-top: 10px;
            }
            .obenlo-ai-close {
                position: absolute;
                top: 16px;
                right: 16px;
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: #999;
            }
            .obenlo-ai-spinner {
                display: inline-block;
                width: 14px;
                height: 14px;
                border: 2px solid #ddd6fe;
                border-top-color: #7c3aed;
                border-radius: 50%;
                animation: obenloSpin 0.6s linear infinite;
                margin-left: 6px;
            }
            @keyframes obenloSpin { to { transform: rotate(360deg); } }
        </style>

        <div id="obenlo-ai-listing-panel">
            <div id="obenlo-ai-listing-modal" role="dialog" aria-label="Obenlo Agent Listing Assistant">
                <p class="obenlo-ai-modal-title">🤖 Obenlo Agent Listing Assistant</p>
                <?php echo $modal_badge; ?>

                <!-- Create from Idea panel -->
                <div id="obenlo-ai-create-panel" style="display:none; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:14px; margin-bottom:14px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#4c1d95; margin-bottom:8px;">💬 Describe your listing idea:</label>
                    <textarea id="obenlo-ai-idea-input" rows="3" placeholder="e.g. 'Private boat tour around the bay at sunset, perfect for couples, based in Miami'" style="width:100%; padding:10px; border:1px solid #c4b5fd; border-radius:8px; font-size:0.85rem; resize:vertical; box-sizing:border-box;"></textarea>
                    <button id="obenlo-ai-create-submit" style="margin-top:10px; background:linear-gradient(135deg,#7c3aed,#e61e4d); color:#fff; border:none; border-radius:8px; padding:9px 18px; font-weight:800; font-size:0.82rem; cursor:pointer; width:100%;">🪄 Generate Full Draft</button>
                </div>

                <button class="obenlo-ai-action-btn" id="obenlo-ai-btn-create">🪄 <span><?php echo $is_editing ? 'Improve Listing from Idea' : ($parent_id > 0 ? 'Create Sub-Option Draft' : 'Create from Idea'); ?></span></button>
                <button class="obenlo-ai-action-btn" id="obenlo-ai-btn-optimize">✨ <span>Optimize Description</span></button>
                <button class="obenlo-ai-action-btn" id="obenlo-ai-btn-title">💡 <span>Suggest Better Titles</span></button>
                <button class="obenlo-ai-action-btn" id="obenlo-ai-btn-seo">🔍 <span>Generate SEO Meta</span></button>

                <div id="obenlo-ai-result" class="obenlo-ai-result">
                    <div id="obenlo-ai-result-text"></div>
                    <button class="obenlo-ai-accept-btn" id="obenlo-ai-accept" style="display:none">✅ Use This</button>
                </div>
            </div>
            <button id="obenlo-ai-listing-fab" title="Open Obenlo Agent Listing Assistant">
                <span>🪄</span> <?php echo $is_editing ? 'Edit Listing with Obenlo Agent' : ($parent_id > 0 ? 'Create Sub-Option with Obenlo Agent' : 'Create Listing with Obenlo Agent'); ?>
            </button>
        </div>

        <script id="obenlo-ai-listing-js">
        (function () {
            'use strict';

            const AJAX_URL     = <?php echo wp_json_encode( $ajax_url ); ?>;
            const NONCE        = <?php echo wp_json_encode( $nonce ); ?>;
            const LISTING_ID   = <?php echo wp_json_encode( $listing_id ); ?>;
            const PARENT_ID    = <?php echo wp_json_encode( $parent_id ); ?>;
            const PARENT_TITLE = <?php echo wp_json_encode( $parent_title ); ?>;

            const fab    = document.getElementById('obenlo-ai-listing-fab');
            const modal  = document.getElementById('obenlo-ai-listing-modal');
            const result = document.getElementById('obenlo-ai-result');
            const resultText = document.getElementById('obenlo-ai-result-text');
            const acceptBtn  = document.getElementById('obenlo-ai-accept');

            let currentAction = null;
            let currentData   = null;

            fab.addEventListener('click', () => modal.classList.toggle('open'));

            function getFormField(names) {
                for (const name of names) {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el) return el.value.trim();
                }
                return '';
            }

            async function callAI(action, payload) {
                const fd = new FormData();
                fd.append('action', action);
                fd.append('nonce', NONCE);
                fd.append('listing_id', LISTING_ID);
                fd.append('parent_id', PARENT_ID);
                fd.append('parent_title', PARENT_TITLE);
                Object.entries(payload).forEach(([k, v]) => fd.append(k, v));

                const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                const text = await res.text();

                // Extract JSON even if PHP notices are prepended
                const s = text.indexOf('{');
                const e = text.lastIndexOf('}');
                if (s === -1 || e === -1) {
                    throw new Error('Server returned non-JSON: ' + text.substring(0, 120));
                }
                return JSON.parse(text.substring(s, e + 1));
            }

            async function handleAction(actionKey) {
                const description  = getFormField(['listing_content']);
                const title        = getFormField(['listing_title']);
                const listingType  = getFormField(['listing_type']);
                const location     = getFormField(['listing_location']);

                result.classList.remove('visible');
                acceptBtn.style.display = 'none';
                resultText.innerHTML    = '⏳ Generating<span class="obenlo-ai-spinner"></span>';
                result.classList.add('visible');
                currentAction = null;

                let data, payload;

                try {
                    if (actionKey === 'optimize') {
                        payload = { description, listing_title: title, listing_type: listingType };
                        data    = await callAI('obenlo_ai_optimize_listing', payload);
                        if (data.success) {
                            currentAction = 'description';
                            currentData   = data.data.optimized;
                            resultText.textContent = data.data.optimized;
                            acceptBtn.style.display = 'inline-block';
                        } else {
                            resultText.textContent = '⚠️ ' + (data.data?.message || 'Error');
                        }
                    } else if (actionKey === 'title') {
                        payload = { current_title: title, listing_type: listingType, location };
                        data    = await callAI('obenlo_ai_suggest_title', payload);
                        if (data.success) {
                            currentAction = 'titles';
                            currentData   = data.data.titles;
                            resultText.innerHTML = data.data.titles.map((t, i) =>
                                `<div style="margin-bottom:6px; cursor:pointer; padding:6px 10px; border-radius:6px; background:#ede9fe; font-weight:700; color:#4c1d95;" data-title="${t.replace(/"/g, '&quot;')}" class="obenlo-ai-title-option">${i+1}. ${t}</div>`
                            ).join('');
                            // Click a title to use it
                            document.querySelectorAll('.obenlo-ai-title-option').forEach(el => {
                                el.addEventListener('click', () => {
                                    const tf = document.querySelector('[name="listing_title"]');
                                    if (tf) { tf.value = el.dataset.title; tf.focus(); }
                                });
                            });
                        } else {
                            resultText.textContent = '⚠️ ' + (data.data?.message || 'Error');
                        }
                    } else if (actionKey === 'seo') {
                        payload = { listing_title: title, description, listing_type: listingType };
                        data    = await callAI('obenlo_ai_seo_meta', payload);
                        if (data.success) {
                            currentAction = 'seo';
                            currentData   = data.data.meta;
                            resultText.textContent = data.data.meta;
                            acceptBtn.style.display = 'inline-block';
                        } else {
                            resultText.textContent = '⚠️ ' + (data.data?.message || 'Error');
                        }
                    }
                } catch (e) {
                    resultText.textContent = '⚠️ Error: ' + e.message;
                    console.error('[Obenlo AI]', e);
                }
            }

            document.getElementById('obenlo-ai-btn-optimize').addEventListener('click', () => handleAction('optimize'));
            document.getElementById('obenlo-ai-btn-title').addEventListener('click', () => handleAction('title'));
            document.getElementById('obenlo-ai-btn-seo').addEventListener('click', () => handleAction('seo'));

            // ── Create from Idea ──────────────────────────────────────────
            const createPanel  = document.getElementById('obenlo-ai-create-panel');
            const ideaInput    = document.getElementById('obenlo-ai-idea-input');
            const createSubmit = document.getElementById('obenlo-ai-create-submit');

            document.getElementById('obenlo-ai-btn-create').addEventListener('click', () => {
                const isOpen = createPanel.style.display !== 'none';
                createPanel.style.display = isOpen ? 'none' : 'block';
                if (!isOpen) { ideaInput.focus(); result.classList.remove('visible'); }
            });

            createSubmit.addEventListener('click', async () => {
                const idea = ideaInput.value.trim();
                if (!idea) { ideaInput.focus(); return; }

                const listingType = getFormField(['listing_type']);
                const location    = getFormField(['listing_location']);

                createSubmit.disabled    = true;
                createSubmit.textContent = '⏳ Generating...';
                result.classList.remove('visible');
                acceptBtn.style.display  = 'none';
                resultText.innerHTML     = '⏳ Drafting your listing<span class="obenlo-ai-spinner"></span>';
                result.classList.add('visible');

                try {
                    const data = await callAI('obenlo_ai_create_listing', {
                        listing_idea:  idea,
                        listing_type:  listingType,
                        location:      location,
                    });

                    if (data.success) {
                        const d = data.data;
                        currentAction = 'create';
                        currentData   = d;

                        resultText.innerHTML = [
                            `<div style="margin-bottom:8px;"><strong>Title:</strong><br><span style="color:#4c1d95;font-weight:700;">${d.title}</span></div>`,
                            `<div style="margin-bottom:8px;"><strong>Description:</strong><br><span style="font-size:0.82rem;">${d.description.replace(/\n/g,'<br>')}</span></div>`,
                            `<div style="margin-bottom:8px;"><strong>SEO Meta:</strong><br><em style="font-size:0.8rem;color:#666;">${d.seo_meta}</em></div>`,
                            d.pricing_model_hint ? `<div style="margin-top:6px; font-size:0.78rem; color:#888;">💡 Suggested pricing model: <strong>${d.pricing_model_hint.replace(/_/g,' ')}</strong></div>` : '',
                        ].join('');
                        acceptBtn.style.display  = 'inline-block';
                        acceptBtn.textContent    = '✅ Apply to Form';
                    } else {
                        resultText.textContent = '⚠️ ' + (data.data?.message || 'Error');
                    }
                } catch (e) {
                    resultText.textContent = '⚠️ Network error. Please try again.';
                    console.error('[Obenlo AI]', e);
                } finally {
                    createSubmit.disabled    = false;
                    createSubmit.textContent = '🪄 Generate Full Draft';
                }
            });

            acceptBtn.addEventListener('click', () => {
                if (currentAction === 'description') {
                    const tf = document.querySelector('[name="listing_content"]');
                    if (tf) { tf.value = currentData; tf.focus(); }
                } else if (currentAction === 'seo') {
                    navigator.clipboard?.writeText(currentData);
                    acceptBtn.textContent = '✅ Copied!';
                    setTimeout(() => { acceptBtn.textContent = '✅ Use This'; }, 2000);
                } else if (currentAction === 'create') {
                    const d = currentData;
                    const titleField = document.querySelector('[name="listing_title"]');
                    const descField  = document.querySelector('[name="listing_content"]');
                    const pmField    = document.querySelector('[name="pricing_model"]');

                    if (titleField) { titleField.value = d.title;       titleField.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (descField)  { descField.value  = d.description; descField.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (pmField && d.pricing_model_hint) {
                        const opt = pmField.querySelector(`option[value="${d.pricing_model_hint}"]`);
                        if (opt) { pmField.value = d.pricing_model_hint; }
                    }

                    acceptBtn.textContent = '✅ Applied!';
                    setTimeout(() => { acceptBtn.textContent = '✅ Apply to Form'; createPanel.style.display = 'none'; }, 1800);
                }
            });
        })();
        </script>
        <?php
    }
}
