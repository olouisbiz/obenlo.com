<?php
/**
 * Obenlo AI Chat
 *
 * Hooks into the Obenlo chat system (wp_obenlo_chat_messages table) to:
 *   - Generate AI-drafted reply suggestions for hosts via AJAX.
 *   - Surface the suggestion inline inside the existing chat UI.
 *
 * No existing code is modified — this hooks purely via wp_ajax actions
 * and a wp_footer injection into pages that load the chat widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Chat {

    public function init() {
        // AJAX: Generate a smart reply draft for the host
        add_action( 'wp_ajax_obenlo_ai_draft_reply', [ $this, 'handle_draft_reply' ] );

        // Inject AI assistant button into the existing chat widget
        add_action( 'wp_footer', [ $this, 'inject_ai_chat_ui' ], 99 );
    }

    // ── AJAX Handler ──────────────────────────────────────────────────────

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

        // Fetch the last 10 messages in this conversation thread
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

        // Build conversation context (oldest first)
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
You are a professional, warm, and helpful host assistant for {$platform_name}, a service marketplace platform.
Your job is to draft a short, natural reply on behalf of the host ({$host_name}) to continue this conversation with their guest ({$guest_name}).

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

    // ── UI Injection ──────────────────────────────────────────────────────
    // Injects a small "✨ AI Draft" button + JS into pages that have the
    // Obenlo chat widget. The button only appears for logged-in hosts/admins.

    public function inject_ai_chat_ui() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $current_user = wp_get_current_user();
        $allowed_roles = [ 'administrator', 'host' ];
        if ( ! array_intersect( $allowed_roles, (array) $current_user->roles ) ) {
            return;
        }

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
                    // 1. Floating chat widget
                    const floatingArea = document.querySelector('.obenlo-chat-input-area');
                    if (floatingArea && !document.getElementById('obenlo-ai-draft-btn-floating')) {
                        console.log('[Obenlo Agent] Injecting button to floating chat widget');
                        const btn = createButton(false);
                        floatingArea.insertBefore(btn, floatingArea.firstChild);
                    }

                    // 2. Inbox / Messages center page
                    const centerArea = document.querySelector('#obenlo-center-input-area > div');
                    if (centerArea && !document.getElementById('obenlo-ai-draft-btn-center')) {
                        console.log('[Obenlo Agent] Injecting button to inbox message center');
                        const btn = createButton(true);
                        centerArea.insertBefore(btn, centerArea.firstChild);
                    }
                } catch (err) {
                    console.error('[Obenlo Agent] Error injecting reply buttons:', err);
                }
            }

            async function handleDraftClick(isCenter) {
                // Read from global variables defined in class-communication.php
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

                    // Extract JSON even if PHP notices are prepended
                    const s = text.indexOf('{');
                    const e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) {
                        throw new Error('Server returned non-JSON: ' + text.substring(0, 120));
                    }
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
                    console.error('[Obenlo AI]', e);
                } finally {
                    btn.textContent = origText;
                    btn.disabled    = false;
                }
            }

            // Observe DOM for dynamically rendered chat widget / inbox
            const observer = new MutationObserver(injectAIButton);
            observer.observe(document.body, { childList: true, subtree: true });
            // Also try immediately
            window.addEventListener('DOMContentLoaded', injectAIButton);
        })();
        </script>
        <?php
    }
}
