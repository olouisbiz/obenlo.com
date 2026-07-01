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
            .smart-search-bar.is-loading button {
                opacity: 0.5;
                pointer-events: none;
                animation: pulse 1s infinite alternate;
            }
            @keyframes pulse {
                from { transform: scale(1); }
                to { transform: scale(1.1); }
            }
        </style>

        <script id="obenlo-ai-search-js">
        (function () {
            'use strict';

            const REST_URL = <?php echo wp_json_encode( $rest_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            // Bind to all smart search forms on the page
            const searchForms = document.querySelectorAll('form.smart-search-bar');
            
            searchForms.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const input = form.querySelector('input[name="s"]');
                    if (!input) return form.submit();
                    
                    const q = input.value.trim();
                    if (!q) return;

                    form.classList.add('is-loading');
                    
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
                            form.submit();
                            return;
                        }

                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            form.submit();
                        }
                    } catch (e) {
                        console.warn('AI Search network error, falling back to standard search:', e);
                        form.submit();
                    } finally {
                        form.classList.remove('is-loading');
                    }
                });
            });
        })();
        </script>
        <?php
    }
}
