<?php
/**
 * Obenlo AI Conversations
 *
 * Merges internal host-to-guest chat suggestions (draft replies) and 
 * frontend Live Support Chat into a single cohesive AI handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Conversations {

    public function init() {
        // --- 1. Host Inbox: AI Draft Replies ---
        add_action( 'wp_ajax_obenlo_ai_draft_reply', [ $this, 'handle_draft_reply' ] );
        add_action( 'wp_footer', [ $this, 'inject_ai_chat_ui' ], 99 );

        // --- 2. Frontend: Live Chat Support Agent ---
        add_action( 'wp_ajax_obenlo_send_live_message', [ $this, 'handle_send_live_message' ] );
        add_action( 'wp_ajax_nopriv_obenlo_send_live_message', [ $this, 'handle_send_live_message' ] );

        add_action( 'wp_ajax_obenlo_fetch_live_messages', [ $this, 'handle_fetch_live_messages' ] );
        add_action( 'wp_ajax_nopriv_obenlo_fetch_live_messages', [ $this, 'handle_fetch_live_messages' ] );

        add_action( 'wp_footer', [ $this, 'render_live_chat_widget' ], 100 );
    }

    // =========================================================================
    // 1. HOST INBOX DRAFTS
    // =========================================================================

    public function handle_draft_reply() {
        check_ajax_referer( 'obenlo_ai_chat_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'obenlo' ) ] );
        }

        $contact_id = isset( $_POST['contact_id'] ) ? intval( $_POST['contact_id'] ) : 0;
        if ( ! $contact_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid contact.', 'obenlo' ) ] );
        }

        $current_user_id = get_current_user_id();

        global $wpdb;
        $table    = $wpdb->prefix . 'obenlo_chat_messages';
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sender_id, message, created_at
                 FROM {$table}
                 WHERE (sender_id = %d AND receiver_id = %d)
                    OR (sender_id = %d AND receiver_id = %d)
                 ORDER BY created_at DESC
                 LIMIT 10",
                $current_user_id, $contact_id,
                $contact_id, $current_user_id
            )
        );

        if ( empty( $messages ) ) {
            wp_send_json_error( [ 'message' => __( 'No messages found in this conversation.', 'obenlo' ) ] );
        }

        $messages   = array_reverse( $messages );
        $thread     = '';
        $host_name  = wp_get_current_user()->display_name;
        $guest_data = get_userdata( $contact_id );
        $guest_name = $guest_data ? $guest_data->display_name : 'Guest';

        foreach ( $messages as $msg ) {
            $who    = ( $msg->sender_id == $current_user_id ) ? $host_name : $guest_name;
            $thread .= "{$who}: {$msg->message}\n";
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **Guest Relations Expert** for {$platform_name}, a global service marketplace.
Your ONLY mission is to draft warm, highly professional, and perfectly contextual replies on behalf of the host ({$host_name}) to their guest ({$guest_name}). You must strictly adhere to this expert persona. You do not write code, you do not write blogs, you only write impeccable guest communications.

Conversation so far:
{$thread}

Write ONLY the reply message. Be polite, concise (2-4 sentences max), and helpful. Do not include any labels like "Host:" or "Reply:". Just the message text.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 200 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'draft' => $result ] );
    }

    public function inject_ai_chat_ui() {
        if ( ! is_user_logged_in() ) return;

        $current_user = wp_get_current_user();
        $allowed_roles = [ 'administrator', 'host' ];
        if ( ! array_intersect( $allowed_roles, (array) $current_user->roles ) ) return;

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'obenlo_ai_chat_nonce' );
        ?>
        <script id="obenlo-ai-chat-js">
        (function () {
            'use strict';

            const AJAX_URL = <?php echo wp_json_encode( $ajax_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            function createButton(isCenter) {
                const btn = document.createElement('button');
                btn.id        = isCenter ? 'obenlo-ai-draft-btn-center' : 'obenlo-ai-draft-btn-floating';
                btn.type      = 'button';
                btn.innerHTML = '✨ Suggest Reply with Obenlo Agent';
                btn.title     = 'Let Obenlo Agent suggest a draft reply for this conversation';
                btn.style.cssText = `
                    background: linear-gradient(135deg, #7c3aed, #e61e4d);
                    color: #fff;
                    border: none;
                    border-radius: 30px;
                    padding: 10px 16px;
                    font-size: 0.78rem;
                    font-weight: 700;
                    cursor: pointer;
                    transition: opacity 0.2s;
                    flex-shrink: 0;
                `;

                btn.addEventListener('click', () => handleDraftClick(isCenter));
                return btn;
            }

            function injectAIButton() {
                try {
                    const floatingArea = document.querySelector('.obenlo-chat-input-area');
                    if (floatingArea && !document.getElementById('obenlo-ai-draft-btn-floating')) {
                        const btn = createButton(false);
                        floatingArea.insertBefore(btn, floatingArea.firstChild);
                    }

                    const centerArea = document.querySelector('#obenlo-center-input-area > div');
                    if (centerArea && !document.getElementById('obenlo-ai-draft-btn-center')) {
                        const btn = createButton(true);
                        centerArea.insertBefore(btn, centerArea.firstChild);
                    }
                } catch (err) {
                    console.error('[Obenlo Agent] Error injecting reply buttons:', err);
                }
            }

            async function handleDraftClick(isCenter) {
                const contactId = isCenter
                    ? (window.obenloCenterContact || 0)
                    : (window.obenloCurrentContact || 0);

                if ( ! contactId ) {
                    alert('Please select a conversation first.');
                    return;
                }

                const btn = document.getElementById(isCenter ? 'obenlo-ai-draft-btn-center' : 'obenlo-ai-draft-btn-floating');
                const origText = btn.textContent;
                btn.textContent = '⏳ Suggesting...';
                btn.disabled    = true;

                try {
                    const fd = new FormData();
                    fd.append('action',     'obenlo_ai_draft_reply');
                    fd.append('nonce',      NONCE);
                    fd.append('contact_id', contactId);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();

                    const s = text.indexOf('{');
                    const e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) throw new Error('Server returned non-JSON: ' + text.substring(0, 120));
                    const data = JSON.parse(text.substring(s, e + 1));

                    if ( data.success && data.data.draft ) {
                        const inputField = document.getElementById(isCenter ? 'obenlo-center-input' : 'obenlo-chat-input-field');
                        if ( inputField ) {
                            inputField.value = data.data.draft;
                            inputField.focus();
                            inputField.dispatchEvent(new Event('input', { bubbles: true }));
                        } else {
                            alert('Obenlo Agent Suggested Reply:\n\n' + data.data.draft);
                        }
                    } else {
                        alert('Obenlo Agent Error: ' + (data.data?.message || 'Could not generate draft.'));
                    }
                } catch (e) {
                    alert('AI Error: ' + e.message);
                } finally {
                    btn.textContent = origText;
                    btn.disabled    = false;
                }
            }

            const observer = new MutationObserver(injectAIButton);
            observer.observe(document.body, { childList: true, subtree: true });
            window.addEventListener('DOMContentLoaded', injectAIButton);
        })();
        </script>
        <?php
    }

    // =========================================================================
    // 2. LIVE FRONTEND SUPPORT AGENT
    // =========================================================================

    public function render_live_chat_widget() {
        $template = locate_template( 'template-parts/live-chat-widget.php' );
        if ( $template ) {
            include $template;
        }
    }

    private function get_live_transient_key( $session_id ) {
        return 'obenlo_live_chat_' . md5( $session_id );
    }

    public function handle_send_live_message() {
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

        if ( empty( $session_id ) || empty( $message ) ) {
            wp_send_json_error( [ 'message' => 'Invalid data.' ] );
        }

        $transient_key = $this->get_live_transient_key( $session_id );
        $history       = get_transient( $transient_key );

        if ( ! is_array( $history ) ) {
            $history = [];
        }

        $user_msg_id = empty( $history ) ? 1 : end( $history )['id'] + 1;
        $history[] = [
            'id'       => $user_msg_id,
            'content'  => $message,
            'is_staff' => false,
            'role'     => 'user',
        ];

        $recent_history = array_slice( $history, -10 );
        $history_text   = '';
        foreach ( $recent_history as $msg ) {
            $role = $msg['is_staff'] ? 'Support Agent' : 'User';
            $history_text .= "{$role}: {$msg['content']}\n";
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **Customer Support Expert** for {$platform_name}, a global service and experience marketplace.
Your ONLY mission is to provide warm, natural, and conversational support to users. You must strictly adhere to this expert persona. Do NOT sound like a robotic AI. Speak like a friendly human support agent. You do not write code or perform tasks outside of customer support.

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

        set_transient( $transient_key, $history, 12 * HOUR_IN_SECONDS );

        wp_send_json_success();
    }

    public function handle_fetch_live_messages() {
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $last_id    = isset( $_POST['last_id'] ) ? intval( $_POST['last_id'] ) : 0;

        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ] );
        }

        $transient_key = $this->get_live_transient_key( $session_id );
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
