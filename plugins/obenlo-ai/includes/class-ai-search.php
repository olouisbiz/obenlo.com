<?php
/**
 * Obenlo AI Search
 *
 * REST API endpoint: /wp-json/obenlo/v1/ai-search
 *
 * Accepts a natural language query (e.g. "romantic candlelit dinner for two in Port-au-Prince under $100")
 * and returns structured search parameters (listing_type, location, max_price, keywords)
 * that the frontend JS can use to filter listings.
 *
 * The existing archive-listing.php + WP_Query system is not changed.
 * The AI search bar calls this endpoint to pre-parse the query, then redirects
 * to the existing archive URL with the extracted query vars appended.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Search {

    public function init() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );

        // Inject AI search bar enhancement into the frontend (non-destructive)
        add_action( 'wp_footer', [ $this, 'inject_ai_search_ui' ], 98 );
    }

    // ── REST Route ────────────────────────────────────────────────────────

    public function register_routes() {
        register_rest_route( 'obenlo/v1', '/ai-search', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_ai_search' ],
            'permission_callback' => '__return_true', // Public endpoint — no login required
            'args'                => [
                'query' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function( $v ) {
                        return ! empty( $v ) && strlen( $v ) <= 500;
                    },
                ],
            ],
        ] );
    }

    // ── REST Callback ─────────────────────────────────────────────────────

    public function handle_ai_search( WP_REST_Request $request ) {
        $query = $request->get_param( 'query' );

        // Retrieve all available listing types as context for the AI
        $terms = get_terms( [
            'taxonomy'   => 'listing_type',
            'hide_empty' => false,
            'fields'     => 'names',
        ] );
        $type_list = is_wp_error( $terms ) ? 'Stay, Experience, Service, Event' : implode( ', ', $terms );

        $prompt = <<<PROMPT
You are a search parser for Obenlo, a global service marketplace.
Parse the following natural language search query and extract structured parameters.

Available listing types on this platform: {$type_list}

User query: "{$query}"

Respond ONLY with a valid JSON object (no markdown, no explanation) with these keys:
- "listing_type": the best matching listing type from the available types above, or "" if unclear
- "location": city, country, or region if mentioned, or ""
- "max_price": numeric max budget if mentioned, or null
- "min_price": numeric min budget if mentioned, or null
- "keywords": a short cleaned-up keyword string for a standard text search (remove price/location/type info)
- "guests": number of guests/people if mentioned, or null
- "intent_summary": one sentence describing what the user is looking for

Example output:
{"listing_type":"Stay","location":"Tulum, Mexico","max_price":200,"min_price":null,"keywords":"beachfront villa","guests":2,"intent_summary":"A beachfront villa stay in Tulum for 2 guests under $200/night"}
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 200 );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response(
                [ 'error' => $result->get_error_message() ],
                503
            );
        }

        // Strip markdown fences if the model wrapped in ```json ... ```
        $clean = preg_replace( '/^```(?:json)?\s*/i', '', trim( $result ) );
        $clean = preg_replace( '/\s*```$/', '', $clean );

        $parsed = json_decode( $clean, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $parsed ) ) {
            // Fallback: return raw text and let the frontend do a keyword search
            return new WP_REST_Response( [
                'listing_type'   => '',
                'location'       => '',
                'max_price'      => null,
                'min_price'      => null,
                'keywords'       => $query,
                'guests'         => null,
                'intent_summary' => $query,
                '_raw'           => $result,
            ], 200 );
        }

        // Build the redirect URL for the listing archive with extracted params
        $archive_url = get_post_type_archive_link( 'listing' ) ?: home_url( '/listing/' );
        $url_params  = array_filter( [
            's'            => $parsed['keywords'] ?? $query,
            'listing_type' => $parsed['listing_type'] ?? '',
            'location'     => $parsed['location'] ?? '',
            'max_price'    => $parsed['max_price'] ?? '',
            'min_price'    => $parsed['min_price'] ?? '',
            'guests'       => $parsed['guests'] ?? '',
        ] );

        $redirect_url = add_query_arg( $url_params, $archive_url );

        $parsed['redirect_url'] = $redirect_url;

        return new WP_REST_Response( $parsed, 200 );
    }

    // ── UI Injection ──────────────────────────────────────────────────────
    // Enhances the existing search bar with a "🔍 Search with AI" toggle.
    // When active, queries the REST endpoint and redirects to the filtered archive.

    public function inject_ai_search_ui() {
        // Only inject on front-facing pages (not admin/dashboard)
        if ( is_admin() ) {
            return;
        }

        $rest_url = rest_url( 'obenlo/v1/ai-search' );
        $nonce    = wp_create_nonce( 'wp_rest' );
        ?>
        <style id="obenlo-ai-search-styles">
            #obenlo-ai-search-bar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 99999;
                background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
                padding: 16px 24px;
                display: flex;
                align-items: center;
                gap: 12px;
                transform: translateY(-110%);
                transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            }
            #obenlo-ai-search-bar.open {
                transform: translateY(0);
            }
            #obenlo-ai-search-input {
                flex: 1;
                padding: 14px 20px;
                border-radius: 50px;
                border: 2px solid rgba(255,255,255,0.1);
                background: rgba(255,255,255,0.08);
                color: #fff;
                font-size: 1rem;
                font-weight: 500;
                outline: none;
                transition: border-color 0.2s;
                backdrop-filter: blur(8px);
            }
            #obenlo-ai-search-input::placeholder { color: rgba(255,255,255,0.4); }
            #obenlo-ai-search-input:focus { border-color: #e61e4d; }
            #obenlo-ai-search-submit {
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                border-radius: 50px;
                padding: 14px 28px;
                font-weight: 800;
                font-size: 0.9rem;
                cursor: pointer;
                white-space: nowrap;
                transition: opacity 0.2s;
            }
            #obenlo-ai-search-submit:hover { opacity: 0.85; }
            #obenlo-ai-search-submit:disabled { opacity: 0.5; cursor: wait; }
            #obenlo-ai-search-close {
                background: rgba(255,255,255,0.1);
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 36px;
                height: 36px;
                cursor: pointer;
                font-size: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            #obenlo-ai-trigger {
                position: fixed;
                bottom: 146px;
                right: 24px;
                z-index: 9998;
                background: linear-gradient(135deg, #302b63, #24243e);
                color: #fff;
                border: none;
                border-radius: 50px;
                padding: 12px 18px;
                font-weight: 800;
                font-size: 0.8rem;
                cursor: pointer;
                box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 8px;
                transition: transform 0.2s;
            }
            #obenlo-ai-trigger:hover { transform: scale(1.05); }
            #obenlo-ai-search-hint {
                color: rgba(255,255,255,0.5);
                font-size: 0.78rem;
                white-space: nowrap;
                display: none;
            }
            @media (min-width: 600px) { #obenlo-ai-search-hint { display: block; } }
        </style>

        <button id="obenlo-ai-trigger" title="Search with Obenlo">
            🔍 Obenlo Search
        </button>

        <div id="obenlo-ai-search-bar" role="search" aria-label="Obenlo Search">
            <input
                type="text"
                id="obenlo-ai-search-input"
                placeholder="Describe what you're looking for… e.g. 'sunset boat tour for 4 in Miami under $150'"
                autocomplete="off"
                maxlength="400"
            />
            <span id="obenlo-ai-search-hint">↵ Enter or click Search</span>
            <button id="obenlo-ai-search-submit">✨ Obenlo Search</button>
            <button id="obenlo-ai-search-close" aria-label="Close Obenlo Search">✕</button>
        </div>

        <script id="obenlo-ai-search-js">
        (function () {
            'use strict';

            const REST_URL = <?php echo wp_json_encode( $rest_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            const trigger = document.getElementById('obenlo-ai-trigger');
            const bar     = document.getElementById('obenlo-ai-search-bar');
            const input   = document.getElementById('obenlo-ai-search-input');
            const submit  = document.getElementById('obenlo-ai-search-submit');
            const close   = document.getElementById('obenlo-ai-search-close');

            trigger.addEventListener('click', () => {
                bar.classList.add('open');
                setTimeout(() => input.focus(), 350);
            });

            close.addEventListener('click', () => {
                bar.classList.remove('open');
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') { bar.classList.remove('open'); }
                if (e.key === 'Enter')  { doSearch(); }
            });

            submit.addEventListener('click', doSearch);

            async function doSearch() {
                const q = input.value.trim();
                if (!q) return;

                submit.disabled    = true;
                submit.textContent = '⏳ Parsing...';

                try {
                    const res  = await fetch(REST_URL, {
                        method:  'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce':   NONCE,
                        },
                        body: JSON.stringify({ query: q }),
                    });

                    const data = await res.json();

                    if (data.error) {
                        console.warn('AI Search failed, falling back to standard search:', data.error);
                        // Redirect to standard text search on listings archive
                        const archiveUrl = new URL(<?php echo wp_json_encode( get_post_type_archive_link( 'listing' ) ?: home_url( '/listing/' ) ); ?>);
                        archiveUrl.searchParams.set('s', q);
                        window.location.href = archiveUrl.toString();
                        return;
                    }

                    if (data.redirect_url) {
                        submit.textContent = '✅ Redirecting...';
                        window.location.href = data.redirect_url;
                    }
                } catch (e) {
                    console.warn('AI Search network error, falling back to standard search:', e);
                    const archiveUrl = new URL(<?php echo wp_json_encode( get_post_type_archive_link( 'listing' ) ?: home_url( '/listing/' ) ); ?>);
                    archiveUrl.searchParams.set('s', q);
                    window.location.href = archiveUrl.toString();
                } finally {
                    submit.disabled    = false;
                    submit.textContent = '✨ Obenlo Search';
                }
            }
        })();
        </script>
        <?php
    }
}
