<?php
/**
 * Obenlo AI Host Profile
 *
 * Provides AI-powered host profile generation for the Storefront Settings page.
 *   - AJAX endpoint: obenlo_ai_generate_host_profile
 *     Accepts a plain-English summary from the host and returns a
 *     polished bio (store_description), tagline, and specialties string.
 *
 *   - UI injection: injects a floating "Obenlo Agent" button on the
 *     Storefront Settings page (?action=storefront). The host previews
 *     the draft before accepting it into the live form fields.
 *
 * Non-destructive: fields are never overwritten without explicit host action.
 * Works alongside the existing class-host-storefront.php save handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Host_Profile {

    public function init() {
        add_action( 'wp_ajax_obenlo_ai_generate_host_profile', [ $this, 'handle_generate_host_profile' ] );
        add_action( 'wp_footer', [ $this, 'inject_ai_host_profile_ui' ], 99 );
    }

    // -- AJAX Handler -------------------------------------------------------

    public function handle_generate_host_profile() {
        check_ajax_referer( 'obenlo_ai_host_profile_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Not authenticated.' ] );
        }

        $host_summary      = sanitize_textarea_field( $_POST['host_summary']      ?? '' );
        $store_name        = sanitize_text_field(     $_POST['store_name']        ?? '' );
        $store_location    = sanitize_text_field(     $_POST['store_location']    ?? '' );
        $store_specialties = sanitize_text_field(     $_POST['store_specialties'] ?? '' );
        $current_bio       = sanitize_textarea_field( $_POST['current_bio']       ?? '' );
        $current_tagline   = sanitize_text_field(     $_POST['current_tagline']   ?? '' );

        if ( empty( $host_summary ) && empty( $current_bio ) ) {
            wp_send_json_error( [ 'message' => 'Please write a brief summary about your business or yourself first.' ] );
        }

        $platform_name = get_bloginfo( 'name' );
        $name_hint     = $store_name        ? "Host/Store name: {$store_name}. "           : '';
        $loc_hint      = $store_location    ? "Location: {$store_location}. "              : '';
        $spec_hint     = $store_specialties ? "Known specialties: {$store_specialties}. "  : '';
        $existing_hint = '';

        if ( ! empty( $current_bio ) ) {
            $existing_hint = "\nExisting bio to improve:\n\"{$current_bio}\"\nExisting tagline: \"{$current_tagline}\"\nTask: Refine, elevate, and polish this existing storefront copy to make it higher-converting while keeping the authentic host persona.";
        }

        $prompt = <<<PROMPT
You are an expert copywriter for {$platform_name}, a global service and experience marketplace.
A host wants to create or upgrade their compelling public storefront profile. Here is the context:

Summary / Notes: "{$host_summary}"
{$name_hint}{$loc_hint}{$spec_hint}{$existing_hint}

Generate a professional, warm, and trust-building host profile. Respond ONLY with a valid JSON object (no markdown, no preamble) using exactly these keys:
- "bio": A compelling host bio / store description of 80 to 140 words. Written in first person. Highlight expertise, passion, and what makes them unique. No filler phrases.
- "tagline": A short catchy tagline under 80 characters that hooks guests immediately.
- "specialties": A comma-separated list of 3 to 6 specific specialties that reflect what they offer.

Example output format:
{"bio":"I have been guiding tours...","tagline":"Authentic encounters, unforgettable memories","specialties":"Private Tours, Eco-Friendly, Multilingual, Local Cuisine"}
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 400 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $clean = preg_replace( '/^```(?:json)?\s*/i', '', trim( $result ) );
        $clean = preg_replace( '/\s*```$/', '', $clean );

        $parsed = json_decode( $clean, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $parsed ) ) {
            wp_send_json_error( [ 'message' => 'AI returned an unexpected format. Please try again.' ] );
        }

        wp_send_json_success( [
            'bio'         => sanitize_textarea_field( $parsed['bio']         ?? '' ),
            'tagline'     => sanitize_text_field(     $parsed['tagline']     ?? '' ),
            'specialties' => sanitize_text_field(     $parsed['specialties'] ?? '' ),
        ] );
    }

    // -- UI Injection -------------------------------------------------------

    public function inject_ai_host_profile_ui() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $current_user  = wp_get_current_user();
        $allowed_roles = [ 'administrator', 'host' ];
        if ( ! array_intersect( $allowed_roles, (array) $current_user->roles ) ) {
            return;
        }

        $action = sanitize_key( $_GET['action'] ?? '' );
        if ( $action !== 'storefront' ) {
            return;
        }

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'obenlo_ai_host_profile_nonce' );
        ?>
        <style id="obenlo-ai-profile-styles">
            #obenlo-ai-profile-fab {
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 9999;
                background: linear-gradient(135deg, #7c3aed 0%, #e61e4d 100%);
                color: #fff;
                border: none;
                border-radius: 50px;
                padding: 13px 22px;
                font-weight: 800;
                font-size: 0.9rem;
                cursor: pointer;
                box-shadow: 0 8px 24px rgba(124,58,237,0.40);
                transition: transform 0.2s, box-shadow 0.2s;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            #obenlo-ai-profile-fab:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 32px rgba(124,58,237,0.50);
            }
            #obenlo-ai-profile-modal {
                position: fixed;
                bottom: 90px;
                right: 24px;
                z-index: 9998;
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.16);
                width: 400px;
                max-height: 80vh;
                overflow-y: auto;
                padding: 26px;
                display: none;
                border: 1px solid #ede9fe;
                animation: obenloProfileSlideUp 0.22s ease;
            }
            #obenlo-ai-profile-modal.open { display: block; }
            @keyframes obenloProfileSlideUp {
                from { opacity: 0; transform: translateY(12px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .obenlo-profile-modal-title {
                font-size: 1.05rem;
                font-weight: 800;
                color: #111;
                margin: 0 0 6px 0;
            }
            .obenlo-profile-modal-sub {
                font-size: 0.8rem;
                color: #888;
                margin: 0 0 18px 0;
            }
            #obenlo-ai-profile-summary {
                width: 100%;
                padding: 12px;
                border: 1.5px solid #ddd6fe;
                border-radius: 10px;
                font-size: 0.85rem;
                resize: vertical;
                box-sizing: border-box;
                transition: border-color 0.2s;
                font-family: inherit;
                color: #222;
            }
            #obenlo-ai-profile-summary:focus {
                border-color: #7c3aed;
                outline: none;
            }
            #obenlo-ai-profile-generate {
                width: 100%;
                margin-top: 12px;
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                border-radius: 10px;
                padding: 12px 18px;
                font-weight: 800;
                font-size: 0.88rem;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            #obenlo-ai-profile-generate:hover { opacity: 0.88; }
            #obenlo-ai-profile-generate:disabled { opacity: 0.5; cursor: wait; }
            #obenlo-ai-profile-result {
                background: #f5f3ff;
                border: 1px solid #ddd6fe;
                border-radius: 12px;
                padding: 16px;
                font-size: 0.84rem;
                color: #1e1b4b;
                line-height: 1.65;
                margin-top: 16px;
                display: none;
            }
            #obenlo-ai-profile-result.visible { display: block; }
            #obenlo-ai-profile-accept {
                margin-top: 14px;
                background: #e61e4d;
                color: #fff;
                border: none;
                border-radius: 9px;
                padding: 10px 20px;
                font-weight: 800;
                font-size: 0.82rem;
                cursor: pointer;
                width: 100%;
                transition: opacity 0.2s;
            }
            #obenlo-ai-profile-accept:hover { opacity: 0.88; }
            .obenlo-profile-spinner {
                display: inline-block;
                width: 13px;
                height: 13px;
                border: 2px solid #ddd6fe;
                border-top-color: #7c3aed;
                border-radius: 50%;
                animation: obenloProfileSpin 0.6s linear infinite;
                margin-left: 6px;
                vertical-align: middle;
            }
            @keyframes obenloProfileSpin { to { transform: rotate(360deg); } }
            #obenlo-ai-profile-close {
                float: right;
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: #aaa;
                margin: -6px -6px 0 0;
            }
        </style>

        <button id="obenlo-ai-profile-fab" title="Open Obenlo Agent Storefront Assistant">
            &#10024; Create Storefront with Obenlo Agent
        </button>

        <div id="obenlo-ai-profile-modal" role="dialog" aria-label="Obenlo Agent - Generate Host Profile">
            <button id="obenlo-ai-profile-close" aria-label="Close">&#10005;</button>
            <p class="obenlo-profile-modal-title">&#10024; Obenlo Agent Storefront Assistant</p>
            <p class="obenlo-profile-modal-sub" id="obenlo-profile-modal-sub-text">Describe your business in a few sentences, or click "Optimize Existing" to let Obenlo Agent refine your current storefront profile.</p>

            <div id="obenlo-ai-existing-notice" style="display:none; background:#ede9fe; color:#6d28d9; padding:8px 12px; border-radius:8px; font-size:0.78rem; font-weight:700; margin-bottom:12px;">
                ✏️ Existing Storefront Profile Detected
            </div>

            <textarea
                id="obenlo-ai-profile-summary"
                rows="4"
                placeholder="e.g. I am a Haitian-born chef based in Miami offering private cooking classes featuring traditional Haitian cuisine. I have been cooking for 15 years and love sharing my culture through food."
            ></textarea>

            <div style="display:flex; flex-direction:column; gap:10px; margin-top:12px;">
                <button id="obenlo-ai-profile-generate" style="width:100%; margin-top:0; background:linear-gradient(135deg,#7c3aed,#e61e4d); color:#fff; border:none; border-radius:10px; padding:12px; font-weight:800; font-size:0.88rem; cursor:pointer;">🪄 Generate New Profile</button>
                <button id="obenlo-ai-profile-optimize-existing" style="width:100%; background:#111; color:#fff; border:none; border-radius:10px; padding:12px; font-weight:800; font-size:0.88rem; cursor:pointer;">✨ Optimize Existing Storefront</button>
            </div>

            <div id="obenlo-ai-profile-result">
                <div id="obenlo-ai-profile-result-text"></div>
                <button id="obenlo-ai-profile-accept" style="display:none;">&#9989; Apply to Storefront Form</button>
            </div>
        </div>

        <script id="obenlo-ai-profile-js">
        (function () {
            'use strict';

            const AJAX_URL = <?php echo wp_json_encode( $ajax_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            const fab            = document.getElementById('obenlo-ai-profile-fab');
            const modal          = document.getElementById('obenlo-ai-profile-modal');
            const closeBtn       = document.getElementById('obenlo-ai-profile-close');
            const summary        = document.getElementById('obenlo-ai-profile-summary');
            const genBtn         = document.getElementById('obenlo-ai-profile-generate');
            const optExistingBtn = document.getElementById('obenlo-ai-profile-optimize-existing');
            const existingNotice = document.getElementById('obenlo-ai-existing-notice');
            const result         = document.getElementById('obenlo-ai-profile-result');
            const resultTxt      = document.getElementById('obenlo-ai-profile-result-text');
            const acceptBtn      = document.getElementById('obenlo-ai-profile-accept');

            let currentData = null;

            function checkExistingStorefront() {
                var bioField = document.querySelector('[name="store_description"]');
                var tagField = document.querySelector('[name="store_tagline"]');
                var bio = (bioField || {value:''}).value.trim();
                var tag = (tagField || {value:''}).value.trim();

                if (bio || tag) {
                    existingNotice.style.display = 'block';
                    fab.innerHTML = '&#10024; Edit Storefront with Obenlo Agent';
                } else {
                    existingNotice.style.display = 'none';
                    fab.innerHTML = '&#10024; Create Storefront with Obenlo Agent';
                }
            }

            // Run immediately & on load
            setTimeout(checkExistingStorefront, 300);
            window.addEventListener('DOMContentLoaded', checkExistingStorefront);

            fab.addEventListener('click', function() {
                checkExistingStorefront();
                modal.classList.toggle('open');
                if (modal.classList.contains('open')) summary.focus();
            });

            closeBtn.addEventListener('click', function() { modal.classList.remove('open'); });

            async function runGeneration(isOptimizeExisting) {
                var text = summary.value.trim();
                var bioField = document.querySelector('[name="store_description"]');
                var tagField = document.querySelector('[name="store_tagline"]');
                var curBio   = (bioField || {value:''}).value.trim();
                var curTag   = (tagField || {value:''}).value.trim();

                if (!text && !isOptimizeExisting) { summary.focus(); return; }
                if (isOptimizeExisting && !curBio && !curTag && !text) {
                    resultTxt.textContent = '⚠️ Please fill in your current storefront description or tagline in the form first (or type brief notes above), then click Optimize!';
                    result.classList.add('visible');
                    return;
                }

                var storeName        = (document.querySelector('[name="store_name"]')        || {value:''}).value.trim();
                var storeLocation    = (document.querySelector('[name="store_location"]')    || {value:''}).value.trim();
                var storeSpecialties = (document.querySelector('[name="store_specialties"]') || {value:''}).value.trim();

                genBtn.disabled    = true;
                optExistingBtn.disabled = true;
                genBtn.textContent = 'Processing...';
                result.classList.remove('visible');
                acceptBtn.style.display = 'none';
                resultTxt.innerHTML = (isOptimizeExisting ? 'Optimizing existing storefront' : 'Writing your profile') + '<span class="obenlo-profile-spinner"></span>';
                result.classList.add('visible');
                currentData = null;

                try {
                    var fd = new FormData();
                    fd.append('action',             'obenlo_ai_generate_host_profile');
                    fd.append('nonce',              NONCE);
                    fd.append('host_summary',       text);
                    fd.append('store_name',         storeName);
                    fd.append('store_location',     storeLocation);
                    fd.append('store_specialties',  storeSpecialties);
                    if (isOptimizeExisting) {
                        fd.append('current_bio',     curBio);
                        fd.append('current_tagline', curTag);
                    }

                    var res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    var rawText = await res.text();

                    // Extract JSON even if PHP notices are prepended
                    var s = rawText.indexOf('{');
                    var eIdx = rawText.lastIndexOf('}');
                    if (s === -1 || eIdx === -1) {
                        throw new Error('Server returned non-JSON: ' + rawText.substring(0, 120));
                    }
                    var data = JSON.parse(rawText.substring(s, eIdx + 1));

                    if (data.success) {
                        var d = data.data;
                        currentData = d;

                        resultTxt.innerHTML =
                            '<div style="margin-bottom:10px;"><strong>Bio / Description:</strong><br>' +
                            '<span style="font-size:0.83rem;">' + d.bio.replace(/\n/g, '<br>') + '</span></div>' +
                            '<div style="margin-bottom:10px;"><strong>Tagline:</strong><br>' +
                            '<em style="color:#4c1d95;font-weight:700;">&ldquo;' + d.tagline + '&rdquo;</em></div>' +
                            '<div><strong>Specialties:</strong><br>' +
                            '<span style="font-size:0.82rem;color:#555;">' + d.specialties + '</span></div>';

                        acceptBtn.style.display = 'block';
                    } else {
                        resultTxt.textContent = 'Error: ' + ((data.data && data.data.message) ? data.data.message : 'Could not generate profile. Please try again.');
                    }
                } catch (e) {
                    resultTxt.textContent = 'Error: ' + e.message;
                    console.error('[Obenlo AI]', e);
                } finally {
                    genBtn.disabled    = false;
                    optExistingBtn.disabled = false;
                    genBtn.textContent = 'Generate Profile';
                }
            }

            genBtn.addEventListener('click', function() { runGeneration(false); });
            optExistingBtn.addEventListener('click', function() { runGeneration(true); });

            acceptBtn.addEventListener('click', function() {
                if (!currentData) return;

                var bioField  = document.querySelector('[name="store_description"]');
                var tagField  = document.querySelector('[name="store_tagline"]');
                var specField = document.querySelector('[name="store_specialties"]');

                if (bioField)  { bioField.value  = currentData.bio;         bioField.dispatchEvent(new Event('input', { bubbles: true })); }
                if (tagField)  { tagField.value  = currentData.tagline;     tagField.dispatchEvent(new Event('input', { bubbles: true })); }
                if (specField) { specField.value = currentData.specialties; specField.dispatchEvent(new Event('input', { bubbles: true })); }

                acceptBtn.textContent = 'Applied! Save the form to keep changes.';
                setTimeout(function() {
                    acceptBtn.textContent = 'Apply to Storefront Form';
                    modal.classList.remove('open');
                }, 2500);
            });
        })();
        </script>
        <?php
    }
}
