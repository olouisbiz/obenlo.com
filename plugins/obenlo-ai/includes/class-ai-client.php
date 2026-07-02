<?php
/**
 * Obenlo AI Client
 *
 * Provider-agnostic wrapper for LLM API requests.
 * Supports: Google Gemini (default/free), OpenAI, Groq (free/fast).
 * Switch providers via WP Admin → Obenlo AI Settings without touching code.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Client {

    /**
     * Send a prompt to the configured AI provider and return the text response.
     *
     * @param  string $prompt       The full prompt string.
     * @param  int    $max_tokens   Maximum tokens to generate (default 512).
     * @return string|WP_Error      AI response text or WP_Error on failure.
     */
    public static function complete( string $prompt, int $max_tokens = 512 ) {
        $provider = get_option( 'obenlo_ai_provider', 'gemini' );

        switch ( $provider ) {
            case 'openai':
                return self::_openai( $prompt, $max_tokens );
            case 'groq':
                return self::_groq( $prompt, $max_tokens );
            case 'gemini':
            default:
                return self::_gemini( $prompt, $max_tokens );
        }
    }

    /**
     * Returns true if the active provider supports live web search (Google Search tool).
     * Useful for conditionally adding "use Google Search" to prompts.
     */
    public static function supports_search(): bool {
        return get_option( 'obenlo_ai_provider', 'gemini' ) === 'gemini';
    }

    // ── Gemini (Google AI Studio free tier) ───────────────────────────────

    private static function _gemini( string $prompt, int $max_tokens ) {
        $api_key = get_option( 'obenlo_ai_gemini_key', '' );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'obenlo_ai_no_key', __( 'Gemini API key is not configured. Go to Obenlo AI → Settings.', 'obenlo' ) );
        }

        $model    = get_option( 'obenlo_ai_gemini_model', 'gemini-2.0-flash' );
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $body = wp_json_encode( [
            'contents' => [
                [
                    'parts' => [
                        [ 'text' => $prompt ],
                    ],
                ],
            ],
            'tools' => [
                [
                    'googleSearch' => new stdClass()
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => $max_tokens,
                'temperature'     => 0.7,
            ],
        ] );

        $response = wp_remote_post( $endpoint, [
            'headers'     => [ 'Content-Type' => 'application/json' ],
            'body'        => $body,
            'timeout'     => apply_filters( 'obenlo_ai_timeout', 45 ),
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'Unknown Gemini API error.';
            return new WP_Error( 'obenlo_ai_gemini_error', $msg );
        }

        return trim( $data['candidates'][0]['content']['parts'][0]['text'] ?? '' );
    }

    // ── Groq (OpenAI-compatible, free tier) ─────────────────────────────

    private static function _groq( string $prompt, int $max_tokens ) {
        $api_key = get_option( 'obenlo_ai_groq_key', '' );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'obenlo_ai_no_key', __( 'Groq API key is not configured. Go to Obenlo AI → Settings.', 'obenlo' ) );
        }

        $model = get_option( 'obenlo_ai_groq_model', 'llama-3.3-70b-versatile' );

        $body = wp_json_encode( [
            'model'      => $model,
            'messages'   => [
                [ 'role' => 'user', 'content' => $prompt ],
            ],
            'max_tokens' => $max_tokens,
        ] );

        $response = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'        => $body,
            'timeout'     => apply_filters( 'obenlo_ai_timeout', 45 ),
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'Unknown Groq API error.';
            return new WP_Error( 'obenlo_ai_groq_error', $msg );
        }

        return trim( $data['choices'][0]['message']['content'] ?? '' );
    }

    // ── OpenAI ────────────────────────────────────────────────────────────

    private static function _openai( string $prompt, int $max_tokens ) {
        $api_key = get_option( 'obenlo_ai_openai_key', '' );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'obenlo_ai_no_key', __( 'OpenAI API key is not configured. Go to Obenlo AI → Settings.', 'obenlo' ) );
        }

        $model = get_option( 'obenlo_ai_openai_model', 'gpt-4o-mini' );

        $body = wp_json_encode( [
            'model'      => $model,
            'messages'   => [
                [ 'role' => 'user', 'content' => $prompt ],
            ],
            'max_tokens' => $max_tokens,
        ] );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'        => $body,
            'timeout'     => apply_filters( 'obenlo_ai_timeout', 45 ),
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'Unknown OpenAI API error.';
            return new WP_Error( 'obenlo_ai_openai_error', $msg );
        }

        return trim( $data['choices'][0]['message']['content'] ?? '' );
    }
}
