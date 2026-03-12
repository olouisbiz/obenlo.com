<?php
/**
 * Live Chat Admin Backend
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Live_Chat_Admin
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_chat_menu'));
        add_action('wp_ajax_obenlo_admin_fetch_sessions', array($this, 'ajax_fetch_sessions'));
        add_action('wp_ajax_obenlo_admin_delete_chat', array($this, 'ajax_delete_chat'));
    }

    public function add_chat_menu()
    {
        add_menu_page(
            'Live Chat',
            'Live Chat',
            'manage_options',
            'obenlo-live-chat',
            array($this, 'render_chat_page'),
            'dashicons-format-chat',
            30
        );
    }

    public function render_chat_page()
    {
?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Live Chat Support</h1>
            <p>Monitor and respond to guests actively chatting on the frontend.</p>
            <div style="display: flex; gap: 20px; height: 700px; margin-top: 20px;">
                <!-- Session List -->
                <div style="width: 320px; background: #fff; border: 1px solid #ccd0d4; overflow-y: auto; display: flex; flex-direction: column;" id="chat-sessions-list">
                    <div style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; background: #fcfcfc;">Active Sessions</div>
                    <div id="sessions-container" style="flex-grow: 1; overflow-y: auto;">
                        <div style="padding: 15px; color: #999;">Loading sessions...</div>
                    </div>
                </div>

                <!-- Chat Window -->
                <div style="flex-grow: 1; background: #fff; border: 1px solid #ccd0d4; display: flex; flex-direction: column;">
                    <div style="padding: 15px; border-bottom: 1px solid #eee; background: #fcfcfc; display: flex; justify-content: space-between; align-items: center;">
                        <div id="active-session-title" style="font-weight: bold;">Select a session from the left</div>
                        <button id="admin-chat-delete" class="button button-link-delete" style="display:none; color: #a00; border: 1px solid #a00; border-radius: 4px; padding: 2px 10px;">Delete Chat</button>
                    </div>
                    <div id="admin-chat-content" style="flex-grow: 1; padding: 20px; overflow-y: auto; background: #f9f9f9; display: flex; flex-direction: column; gap: 10px;">
                        <div style="margin: auto; color: #999; text-align: center;">
                            <span class="dashicons dashicons-format-chat" style="font-size: 40px; width: 40px; height: 40px; margin-bottom: 15px; opacity: 0.5;"></span>
                            <br>No chat selected
                        </div>
                    </div>
                    <div style="padding: 15px; border-top: 1px solid #eee; background: #fff;">
                        <form id="admin-chat-form" style="display: flex; gap: 10px;">
                            <input type="hidden" id="admin-session-id" value="">
                            <input type="text" id="admin-chat-input" placeholder="Type your reply here..." style="flex-grow: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; outline: none;" disabled>
                            <button type="submit" class="button button-primary" id="admin-chat-submit" style="padding: 0 30px;" disabled>Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let activeSession = '';
            let lastId = 0;
            let pollInterval = null;

            function fetchSessions() {
                $.post(ajaxurl, {
                    action: 'obenlo_admin_fetch_sessions'
                }, function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.data.length === 0) {
                            html = '<div style="padding: 20px; color: #999; text-align: center;">No active chats right now.</div>';
                        } else {
                            response.data.forEach(function(session) {
                                let bg = (activeSession === session.id) ? '#f0f0f1' : '#fff';
                                let border = (activeSession === session.id) ? 'border-left: 4px solid #e61e4d;' : 'border-left: 4px solid transparent;';
                                html += '<div class="chat-session-item" data-id="' + session.id + '" style="padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; background: ' + bg + '; ' + border + ' transition: background 0.2s;">';
                                html += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
                                html += '<strong style="color: #222;">' + session.id + '</strong>';
                                html += '<span style="font-size: 0.75em; color: #999;">' + session.time + '</span>';
                                html += '</div>';
                                html += '<div style="font-size: 0.85em; color: #666; max-height: 20px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + session.last_msg + '</div>';
                                html += '</div>';
                            });
                        }
                        $('#sessions-container').html(html);
                    }
                });
            }

            // Initial fetch and regular polling for new sessions
            fetchSessions();
            setInterval(fetchSessions, 5000);

            // Handle session selection
            $(document).on('click', '.chat-session-item', function() {
                activeSession = $(this).data('id');
                
                // Update UI visually
                $('.chat-session-item').css({'background': '#fff', 'border-left': '4px solid transparent'});
                $(this).css({'background': '#f0f0f1', 'border-left': '4px solid #e61e4d'});
                
                $('#active-session-title').html('<span style="color: #4CAF50;">●</span> Chatting with: <strong>' + activeSession + '</strong>');
                $('#admin-chat-delete').show();
                $('#admin-session-id').val(activeSession);
                $('#admin-chat-input, #admin-chat-submit').prop('disabled', false);
                $('#admin-chat-input').focus();
                
                $('#admin-chat-content').html('');
                lastId = 0;
                
                if (pollInterval) clearInterval(pollInterval);
                fetchMessages();
                pollInterval = setInterval(fetchMessages, 3000);
            });

            // Handle message submission
            $('#admin-chat-form').on('submit', function(e) {
                e.preventDefault();
                let msg = $('#admin-chat-input').val().trim();
                let sessionId = $('#admin-session-id').val();
                
                if (!msg || !sessionId) return;
                
                $('#admin-chat-input').val('');
                
                // We use the existing send_live_message action from communication class
                $.post(ajaxurl, {
                    action: 'obenlo_send_live_message',
                    session_id: sessionId,
                    message: msg
                }, function(response) {
                    if (response.success) {
                        fetchMessages();
                    }
                });
            });

            // Handle chat deletion
            $('#admin-chat-delete').on('click', function(e) {
                e.preventDefault();
                if (!activeSession) return;
                if (!confirm('Are you sure you want to delete this entire chat history? This cannot be undone.')) return;

                $.post(ajaxurl, {
                    action: 'obenlo_admin_delete_chat',
                    session_id: activeSession
                }, function(response) {
                    if (response.success) {
                        activeSession = '';
                        $('#active-session-title').html('Select a session from the left');
                        $('#admin-chat-delete').hide();
                        $('#admin-chat-content').html('<div style="margin: auto; color: #999; text-align: center;"><span class="dashicons dashicons-format-chat" style="font-size: 40px; width: 40px; height: 40px; margin-bottom: 15px; opacity: 0.5;"></span><br>Chat deleted</div>');
                        $('#admin-session-id').val('');
                        $('#admin-chat-input, #admin-chat-submit').prop('disabled', true);
                        if (pollInterval) clearInterval(pollInterval);
                        fetchSessions();
                    } else {
                        alert('Error deleting chat: ' + (response.data || 'Unknown error'));
                    }
                });
            });

            // Fetch messages for the active session
            function fetchMessages() {
                if (!activeSession) return;
                
                $.post(ajaxurl, {
                    action: 'obenlo_fetch_live_messages',
                    session_id: activeSession,
                    last_id: lastId
                }, function(response) {
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(msg) {
                            if (msg.id > lastId) {
                                appendMessage(msg.content, msg.is_staff);
                                lastId = msg.id;
                            }
                        });
                        scrollBottom();
                    }
                });
            }

            // Append message to chat window
            function appendMessage(content, isStaff) {
                let align = isStaff ? 'margin-left: auto; text-align: right;' : 'margin-right: auto; text-align: left;';
                let bubbleStyle = isStaff ? 'background: #e61e4d; color: #fff; border-radius: 12px 12px 2px 12px;' : 'background: #fff; color: #333; border: 1px solid #ddd; border-radius: 12px 12px 12px 2px;';
                
                let html = '<div style="margin-bottom: 10px; max-width: 75%; ' + align + '">';
                html += '<div style="padding: 10px 15px; font-size: 0.95em; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-block; text-align: left; ' + bubbleStyle + '">' + content + '</div>';
                html += '</div>';
                
                $('#admin-chat-content').append(html);
            }

            function scrollBottom() {
                let box = document.getElementById('admin-chat-content');
                box.scrollTop = box.scrollHeight;
            }
        });
        </script>
        <?php
    }

    public function ajax_fetch_sessions()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        // Group by session ID, get the most recent message
        $query = "
            SELECT pm.meta_value as session_id, MAX(p.ID) as last_msg_id
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'obenlo_message'
            AND pm.meta_key = '_obenlo_chat_session'
            GROUP BY pm.meta_value
            ORDER BY last_msg_id DESC
            LIMIT 50
        ";

        $results = $wpdb->get_results($query);
        $sessions = array();

        foreach ($results as $row) {
            $last_msg = get_post($row->last_msg_id);
            if ($last_msg) {
                $sessions[] = array(
                    'id' => $row->session_id,
                    'last_msg' => wp_kses_post(wp_trim_words($last_msg->post_content, 10)),
                    'time' => get_the_date('M j, H:i', $last_msg->ID)
                );
            }
        }

        wp_send_json_success($sessions);
    }

    public function ajax_delete_chat()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $session_id = isset($_POST['session_id']) ? sanitize_text_field(wp_unslash($_POST['session_id'])) : '';

        if (empty($session_id)) {
            wp_send_json_error('No session ID provided');
        }

        global $wpdb;

        // Find all posts with this session ID
        $query = $wpdb->prepare("
            SELECT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_obenlo_chat_session' AND meta_value = %s
        ", $session_id);

        $post_ids = $wpdb->get_col($query);

        if (!empty($post_ids)) {
            foreach ($post_ids as $pid) {
                wp_delete_post($pid, true); // Force delete
            }
            wp_send_json_success('Chat deleted');
        }
        else {
            wp_send_json_error('No messages found for this session');
        }
    }
}
