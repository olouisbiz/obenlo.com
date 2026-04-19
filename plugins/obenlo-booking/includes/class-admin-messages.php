<?php
if (!defined('ABSPATH')) { exit; }

class Obenlo_Admin_Messages
{
    public function init()
    {
        add_action('admin_post_obenlo_send_broadcast', array($this, 'handle_send_broadcast'));
        add_action('admin_post_obenlo_delete_broadcast', array($this, 'handle_delete_broadcast'));
        add_action('admin_post_obenlo_admin_delete_chat', array($this, 'handle_delete_chat'));
    }

    public function render_broadcast_tab()
    {
        if (isset($_GET['broadcast_sent'])) {
            echo '<div style="background:#d4edda; padding:10px; margin-bottom:15px; border:1px solid #c3e6cb; color:#155724;">Broadcast sent successfully!</div>';
        }
        if (isset($_GET['broadcast_deleted'])) {
            echo '<div style="background:#d1ecf1; padding:10px; margin-bottom:15px; border:1px solid #bee5eb; color:#0c5460;">Broadcast deleted successfully.</div>';
        }

        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">';

        // Column 1: Send New Broadcast
        echo '<div>';
        echo '<h3>Send New Broadcast</h3>';
        echo '<p style="color:#666; margin-bottom:20px;">Push a message to all users or specific roles via Email and PWA Push Notifications.</p>';
        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" style="background:#f9f9f9; padding:25px; border-radius:12px; border:1px solid #eee;">';
        echo '<input type="hidden" name="action" value="obenlo_send_broadcast">';
        wp_nonce_field('send_broadcast', 'broadcast_nonce');

        echo '<label style="display:block; margin-bottom:15px; font-weight:700;">Recipient Group:<br>';
        echo '<select name="broadcast_role" style="width:100%; padding:10px; margin-top:5px; border-radius:8px; border:1px solid #ddd;">';
        echo '<option value="all">All Users (Hosts & Guests)</option>';
        echo '<option value="host">Hosts Only</option>';
        echo '<option value="guest">Guests Only</option>';
        echo '</select></label>';

        echo '<label style="display:block; margin-bottom:15px; font-weight:700;">Subject:<br><input type="text" name="broadcast_title" required style="width:100%; padding:10px; margin-top:5px; border-radius:8px; border:1px solid #ddd;"></label>';
        echo '<label style="display:block; margin-bottom:15px; font-weight:700;">Message (HTML allowed):<br><textarea name="broadcast_content" required style="width:100%; padding:10px; margin-top:5px; height:200px; border-radius:8px; border:1px solid #ddd; font-family:inherit;"></textarea></label>';

        echo '<button type="submit" style="background:#e61e4d; color:white; border:none; padding:15px 25px; border-radius:12px; cursor:pointer; font-weight:bold; width:100%; font-size:1.1rem; box-shadow: 0 4px 15px rgba(230,30,77,0.2);">🚀 Send Broadcast Now</button>';
        echo '</form>';
        echo '</div>';

        // Column 2: Broadcast History
        echo '<div>';
        echo '<h3>Broadcast History</h3>';
        echo '<p style="color:#666; margin-bottom:20px;">Review or delete previously sent announcements.</p>';
        
        $broadcasts = get_posts(array(
            'post_type' => 'broadcast',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC'
        ));

        if (empty($broadcasts)) {
            echo '<div style="padding:40px; background:#fdfdfd; border:1px dashed #ddd; border-radius:12px; text-align:center; color:#999;">No broadcasts sent yet.</div>';
        } else {
            echo '<div style="display:flex; flex-direction:column; gap:15px;">';
            foreach ($broadcasts as $b) {
                $role = get_post_meta($b->ID, '_obenlo_broadcast_recipient', true);
                $role_label = ($role === 'all') ? 'Everyone' : (($role === 'host') ? 'Hosts' : 'Guests');
                
                echo '<div style="background:#fff; border:1px solid #eee; border-radius:12px; padding:15px; position:relative; box-shadow:0 2px 8px rgba(0,0,0,0.02);">';
                echo '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">';
                echo '<span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; color:#e61e4d; background:#fff1f2; padding:3px 8px; border-radius:20px;">' . esc_html($role_label) . '</span>';
                echo '<span style="font-size:0.8rem; color:#aaa;">' . get_the_date('', $b->ID) . '</span>';
                echo '</div>';
                
                echo '<h4 style="margin:5px 0; font-size:1rem; color:#222;">' . esc_html($b->post_title) . '</h4>';
                echo '<p style="font-size:0.85rem; color:#666; margin-bottom:15px;">' . wp_trim_words($b->post_content, 15) . '</p>';
                
                echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" onsubmit="return confirm(\'Delete this announcement? This cannot be undone.\');" style="margin:0;">';
                echo '<input type="hidden" name="action" value="obenlo_delete_broadcast">';
                echo '<input type="hidden" name="broadcast_id" value="' . $b->ID . '">';
                wp_nonce_field('delete_broadcast', 'broadcast_nonce');
                echo '<button type="submit" style="background:none; border:none; color:#ef4444; font-size:0.8rem; font-weight:700; cursor:pointer; padding:0; display:flex; align-items:center; gap:5px;">';
                echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px; height:14px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                echo 'Delete Announcement</button>';
                echo '</form>';
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>'; // End Column 2
        
        echo '</div>'; // End Grid
    }

    public function render_communication_tab()
    {
        $current_status = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : 'all';

        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
        echo '<h3>Active Support</h3>';
        
        // Filter UI
        echo '<div style="display:flex; gap:10px;">';
        $filters = array('all' => 'All Tickets', 'open' => 'Open', 'resolved' => 'Resolved');
        foreach ($filters as $slug => $label) {
            $is_active = ($current_status === $slug);
            $bg = $is_active ? '#e61e4d' : '#f9f9f9';
            $color = $is_active ? '#fff' : '#666';
            $border = $is_active ? '1px solid #e61e4d' : '1px solid #ddd';
            echo '<a href="' . esc_url(add_query_arg('status_filter', $slug)) . '" style="padding:8px 18px; border-radius:30px; border:' . $border . '; text-decoration:none; color:' . $color . '; background:' . $bg . '; font-size:0.85rem; font-weight:700; transition:all 0.2s;">' . esc_html($label) . '</a>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div style="background:#fff; border:1px solid #eee; border-radius:15px; padding:30px;">';
        
        $args = array(
            'post_type' => 'ticket',
            'posts_per_page' => -1,
            'suppress_filters' => false,
        );

        if ($current_status !== 'all') {
            $args['meta_query'] = array(
                array(
                    'key' => '_obenlo_ticket_status',
                    'value' => $current_status
                )
            );
            $args['orderby'] = 'ID';
            $args['order'] = 'DESC';
        } else {
            $args['meta_key'] = '_obenlo_ticket_status';
            $args['orderby'] = array(
                'meta_value' => 'ASC', // Open first
                'ID' => 'DESC'         // Newest first
            );
        }

        $tickets = get_posts($args);
        if (empty($tickets)) {
            echo '<p>No active tickets.</p>';
        }
        else {
            foreach ($tickets as $ticket) {
                $user = get_userdata($ticket->post_author);
                $type = get_post_meta($ticket->ID, '_obenlo_ticket_type', true);
                $status = get_post_meta($ticket->ID, '_obenlo_ticket_status', true);
                $status_bg = ($status === 'open') ? '#e61e4d' : '#333';

                echo '<div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:12px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.02);">';
                echo '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">';
                echo '<strong>#' . $ticket->ID . ': ' . esc_html($ticket->post_title) . '</strong>';
                echo '<span class="badge" style="background:' . $status_bg . '; color:#fff; padding:3px 10px; border-radius:10px; font-size:0.75em; font-weight:bold;">' . esc_html(strtoupper($status)) . '</span>';
                echo '</div>';
                echo '<div style="font-size:0.85em; color:#888; margin-bottom:10px;">';
                echo '<span style="color:#222; font-weight:600;">' . ($user ? $user->display_name : 'Unknown') . '</span> â€¢ ';
                echo esc_html(ucfirst($type)) . ' â€¢ ' . get_the_date('M j, H:i', $ticket->ID);
                echo '</div>';
                echo '<div style="font-size:0.9em; color:#444; line-height:1.5;">' . wp_trim_words($ticket->post_content, 12) . '</div>';
                echo '<div style="margin-top:15px; border-top:1px solid #f9f9f9; padding-top:10px; text-align:right;">';
                echo '<a href="' . esc_url(add_query_arg('ticket_id', $ticket->ID, home_url('/support'))) . '" style="color:#e61e4d; font-weight:bold; text-decoration:none; font-size:0.9em;">Manage Ticket & Reply →</a>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        echo '</div>';
    }

    public function render_messaging_oversight_tab()
    {
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">';
        echo '<h3>Platform Messaging Oversight</h3>';
        echo '<div style="background:#fff4f4; border:1px solid #ffcccc; padding:5px 15px; border-radius:30px; font-size:0.8rem; color:#e61e4d; font-weight:700;">Admin View Mode</div>';
        echo '</div>';
        echo '<p style="color:#666; margin-bottom:30px;">Monitor all conversations between platform users for quality control and dispute resolution.</p>';

        echo do_shortcode('[obenlo_messages_page oversight="1"]');
    }

    public function render_live_chat_tab()
    {
        global $wpdb;

        // Get unique chat sessions
        $sessions = $wpdb->get_results("
            SELECT DISTINCT meta_value as session_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_obenlo_chat_session' 
            ORDER BY post_id DESC 
            LIMIT 50
        ");

?>
        <div style="display:grid; grid-template-columns: 300px 1fr; gap:0; background:#fff; border:1px solid #ddd; border-radius:16px; height:600px; overflow:hidden;">
            <!-- Sessions Sidebar -->
            <div style="border-right:1px solid #ddd; background:#f9f9f9; overflow-y:auto;">
                <div style="padding:20px; font-weight:bold; border-bottom:1px solid #ddd; background:#fff;">Live Sessions</div>
                <?php if (empty($sessions)): ?>
                    <p style="padding:20px; color:#666;">No active chats.</p>
                <?php
        else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <?php
                $display_name = $session->session_id;
                if (strpos($display_name, 'guest_') === 0) {
                    $display_name = 'Guest (' . substr($display_name, 6, 4) . ')';
                }
?>
                        <div class="chat-session-item" data-id="<?php echo esc_attr($session->session_id); ?>" data-name="<?php echo esc_attr($display_name); ?>" style="padding:15px 20px; border-bottom:1px solid #eee; cursor:pointer; transition: background 0.2s;">
                            <div style="font-weight:bold; font-size:0.9em;"><?php echo esc_html($display_name); ?></div>
                            <div style="font-size:0.75em; color:#888;"><?php echo esc_html(strpos($session->session_id, 'guest_') === 0 ? 'Anonymous' : 'Registered'); ?></div>
                        </div>
                    <?php
            endforeach; ?>
                <?php
        endif; ?>
            </div>

            <!-- Chat Area -->
            <div id="live-chat-admin-area" style="display:flex; flex-direction:column; background:#fff;">
                <div id="chat-header" style="padding:15px 25px; border-bottom:1px solid #ddd; font-weight:bold; display:flex; justify-content:space-between; align-items:center;">
                    <span id="chat-header-title">Select a session to start chatting</span>
                    <button id="admin-chat-delete" style="display:none; color: #a00; border: 1px solid #a00; border-radius: 4px; padding: 4px 12px; background: transparent; cursor: pointer; font-size: 0.85em;">Delete Chat</button>
                </div>
                <div id="chat-messages" style="flex-grow:1; padding:25px; overflow-y:auto; background:#fff;"></div>
                
                <div id="chat-input-area" style="padding:20px; border-top:1px solid #ddd; background:#f9f9f9; display:none;">
                    <form id="admin-chat-form" style="display:flex; gap:10px;">
                        <input type="text" id="admin-chat-input" placeholder="Type your response..." style="flex-grow:1; padding:12px; border:1px solid #ddd; border-radius:25px;">
                        <button type="submit" style="background:#e61e4d; color:white; border:none; padding:0 25px; border-radius:25px; font-weight:bold; cursor:pointer;">Send</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let activeSession = '';
            let lastId = 0;
            let pollInterval = null;
            
            const sessionItems = document.querySelectorAll('.chat-session-item');
            const chatHeaderTitle = document.getElementById('chat-header-title');
            const chatDeleteBtn = document.getElementById('admin-chat-delete');
            const chatMessages = document.getElementById('chat-messages');
            const chatInputArea = document.getElementById('chat-input-area');
            const chatForm = document.getElementById('admin-chat-form');
            const chatInput = document.getElementById('admin-chat-input');
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            sessionItems.forEach(item => {
                item.addEventListener('click', function() {
                    sessionItems.forEach(i => i.style.background = 'transparent');
                    this.style.background = '#fff';
                    activeSession = this.getAttribute('data-id');
                    const sessionName = this.getAttribute('data-name');
                    chatHeaderTitle.textContent = 'Chatting with ' + sessionName;
                    chatDeleteBtn.style.display = 'block';
                    chatMessages.innerHTML = '';
                    chatInputArea.style.display = 'block';
                    lastId = 0;
                    fetchMessages();
                    
                    if(pollInterval) clearInterval(pollInterval);
                    pollInterval = setInterval(fetchMessages, 3000);
                });
            });

            function fetchMessages() {
                if(!activeSession) return;
                
                const url = new URL(ajaxUrl);
                url.searchParams.append('action', 'obenlo_fetch_live_messages');
                url.searchParams.append('session_id', activeSession);
                url.searchParams.append('last_id', lastId);
                
                fetch(url)
                    .then(response => response.json())
                    .then(res => {
                        if(res.success && res.data.length > 0) {
                            res.data.forEach(msg => {
                                if(msg.id > lastId) {
                                    appendMessage(msg);
                                    lastId = msg.id;
                                }
                            });
                            scrollBottom();
                        }
                    })
                    .catch(e => console.error(e));
            }

            function appendMessage(msg) {
                const align = msg.is_staff ? 'margin-left:auto; background:#e61e4d; color:white; border-radius:18px 18px 2px 18px;' : 'margin-right:auto; background:#f1f1f1; color:#333; border-radius:18px 18px 18px 2px;';
                const div = document.createElement('div');
                div.style.cssText = `max-width:70%; padding:10px 15px; margin-bottom:10px; font-size:0.9em; ${align}`;
                div.innerHTML = msg.content;
                chatMessages.appendChild(div);
            }

            function scrollBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            chatForm.addEventListener('submit', function(e){
                e.preventDefault();
                const msg = chatInput.value.trim();
                if(!msg) return;
                chatInput.value = '';

                const formData = new FormData();
                formData.append('action', 'obenlo_send_live_message');
                formData.append('session_id', activeSession);
                formData.append('message', msg);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    fetchMessages();
                })
                .catch(e => console.error(e));
            });

            if (chatDeleteBtn) {
                chatDeleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!activeSession) return;
                    if (!confirm('Are you sure you want to delete this entire chat history? This cannot be undone.')) return;

                    const formData = new FormData();
                    formData.append('action', 'obenlo_admin_delete_chat');
                    formData.append('session_id', activeSession);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert('Error deleting chat: ' + (res.data || 'Unknown error'));
                        }
                    })
                    .catch(e => console.error(e));
                });
            }
        });
        </script>
        <?php
    }

}
