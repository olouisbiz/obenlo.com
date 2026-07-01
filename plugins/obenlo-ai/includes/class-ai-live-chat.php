<?php
/**
 * Obenlo AI Live Chat (Frontend Support Agent)
 *
 * Handles the frontend live chat widget. Uses transients to store
 * session-based conversation history and connects to Gemini to
 * act as the official Obenlo Support AI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Live_Chat {

    public function init() {
        add_action( 'wp_ajax_obenlo_send_live_message', [ $this, 'handle_send_message' ] );
        add_action( 'wp_ajax_nopriv_obenlo_send_live_message', [ $this, 'handle_send_message' ] );

        add_action( 'wp_ajax_obenlo_fetch_live_messages', [ $this, 'handle_fetch_messages' ] );
        add_action( 'wp_ajax_nopriv_obenlo_fetch_live_messages', [ $this, 'handle_fetch_messages' ] );

        // Inject the frontend UI into the footer
        add_action( 'wp_footer', [ $this, 'render_chat_widget' ], 100 );
    }

    public function render_chat_widget() {
        // Look for the template in the active theme
        $template = locate_template( 'template-parts/live-chat-widget.php' );
        if ( $template ) {
            include $template;
        }
    }

    private function get_transient_key( $session_id ) {
        return 'obenlo_live_chat_' . md5( $session_id );
    }

    public function handle_send_message() {
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

        if ( empty( $session_id ) || empty( $message ) ) {
            wp_send_json_error( [ 'message' => 'Invalid data.' ] );
        }

        $transient_key = $this->get_transient_key( $session_id );
        $history       = get_transient( $transient_key );

        if ( ! is_array( $history ) ) {
            $history = [];
        }

        // Add user message to history
        $user_msg_id = empty( $history ) ? 1 : end( $history )['id'] + 1;
        $history[] = [
            'id'       => $user_msg_id,
            'content'  => $message,
            'is_staff' => false,
            'role'     => 'user',
        ];

        // Format history for the AI prompt (keep last 10 messages)
        $recent_history = array_slice( $history, -10 );
        $history_text   = '';
        foreach ( $recent_history as $msg ) {
            $role = $msg['is_staff'] ? 'Support Agent' : 'User';
            $history_text .= "{$role}: {$msg['content']}\n";
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the official Customer Support AI Agent for {$platform_name}, a global service and experience marketplace.
Your personality is warm, natural, and highly conversational. Do NOT sound like a robotic AI. Speak like a friendly human support agent.

CRITICAL KNOWLEDGE:
- Listing a service on {$platform_name} is 100% FREE. There are no monthly fees or listing fees.
- We only take a 10% platform commission on successfully completed bookings.
- Our primary listing categories are: Services (Cleaning, Barber, Freelance), Events (Shows, Live Nights), Experiences (Tours, Adventures), and Stays (Hotels, Guest houses).
- We accept major Credit Cards (via Stripe) and PayPal for bookings.
- If they want to see the platform in action, they can click "Request a Demo" in the footer, but DO NOT mention the demo unless they specifically ask to see the platform, schedule a call, or see how it works. DO NOT spam the demo link.

You have access to real-time Google Search to look up live information if needed.
Answer questions DIRECTLY and immediately. Keep replies concise, conversational, and helpful (1-2 sentences). Do not repeat yourself. Do not roleplay "checking the website" or "give me a second" — just provide the answer instantly.
If a user asks something completely beyond your knowledge or needs human assistance, politely inform them that they can reach out to our team at support@obenlo.com for further help.
NEVER say "I am a large language model" or reveal your AI architecture. You are an official member of the {$platform_name} support team.

Conversation History:
{$history_text}

Support Agent:
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 300 );

        if ( ! is_wp_error( $result ) ) {
            $ai_msg_id = $user_msg_id + 1;
            $history[] = [
                'id'       => $ai_msg_id,
                'content'  => $result,
                'is_staff' => true,
                'role'     => 'model',
            ];
        }

        // Save history for 12 hours
        set_transient( $transient_key, $history, 12 * HOUR_IN_SECONDS );

        wp_send_json_success();
    }

    public function handle_fetch_messages() {
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $last_id    = isset( $_POST['last_id'] ) ? intval( $_POST['last_id'] ) : 0;

        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ] );
        }

        $transient_key = $this->get_transient_key( $session_id );
        $history       = get_transient( $transient_key );

        if ( ! is_array( $history ) ) {
            $history = [];
        }

        $new_messages = [];
        foreach ( $history as $msg ) {
            if ( $msg['id'] > $last_id ) {
                $new_messages[] = $msg;
            }
        }

        wp_send_json_success( $new_messages );
    }

}
