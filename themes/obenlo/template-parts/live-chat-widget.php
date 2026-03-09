<?php
/**
 * Frontend Live Chat Widget
 */
?>

<div id="obenlo-live-chat" style="position:fixed; bottom:30px; right:30px; z-index:9999; font-family: 'Inter', sans-serif;">
    <!-- Chat Bubble -->
    <div id="chat-bubble" style="width:60px; height:60px; background:#e61e4d; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 10px 25px rgba(230,30,77,0.3); transition:transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <svg viewBox="0 0 24 24" style="width:28px; height:28px; fill:white;"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
    </div>

    <!-- Chat Window -->
    <div id="chat-window" style="display:none; width:350px; height:500px; background:#fff; border-radius:16px; box-shadow:0 15px 50px rgba(0,0,0,0.15); flex-direction:column; overflow:hidden; border:1px solid #eee;">
        <div style="background:#e61e4d; color:white; padding:20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong style="display:block;"><?php esc_html_e( 'Obenlo Support', 'obenlo' ); ?></strong>
                <span style="font-size:0.8em; opacity:0.9;"><?php esc_html_e( "We're online and ready to help", "obenlo" ); ?></span>
            </div>
            <span id="close-chat" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>
        
        <div id="chat-content" style="flex-grow:1; padding:20px; overflow-y:auto; background:#f9f9f9; display:flex; flex-direction:column; gap:10px;">
            <div style="background:#fff; padding:12px; border-radius:12px; font-size:0.9em; box-shadow:0 2px 5px rgba(0,0,0,0.02); margin-right:40px;">
                <?php esc_html_e( 'Hello! How can we help you today?', 'obenlo' ); ?>
            </div>
        </div>

        <div style="padding:15px; border-top:1px solid #eee; background:#fff;">
            <form id="chat-form" style="display:flex; gap:10px;">
                <input type="text" id="chat-input" placeholder="<?php esc_attr_e( 'Type a message...', 'obenlo' ); ?>" style="flex-grow:1; border:1px solid #eee; padding:10px 15px; border-radius:25px; outline:none; font-size:0.9em; background:#f5f5f5;">
                <button type="submit" style="background:#e61e4d; border:none; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:transform 0.2s;">
                    <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:white; transform:rotate(-45deg) translate(2px, -2px);"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate or retrieve session entirely on the client side to avoid page cache issues
    let sessionId = sessionStorage.getItem('obenlo_chat_session');
    if (!sessionId) {
        sessionId = 'guest_' + Math.random().toString(36).substring(2, 10);
        sessionStorage.setItem('obenlo_chat_session', sessionId);
    }
    
    let lastId = 0;
    let pollInterval = null;

    const chatBubble = document.getElementById('chat-bubble');
    const chatWindow = document.getElementById('chat-window');
    const closeChat = document.getElementById('close-chat');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatContent = document.getElementById('chat-content');
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

    chatBubble.addEventListener('click', function() {
        chatBubble.style.display = 'none';
        chatWindow.style.display = 'flex';
        fetchMessages();
        pollInterval = setInterval(fetchMessages, 3000);
    });

    closeChat.addEventListener('click', function() {
        chatWindow.style.display = 'none';
        chatBubble.style.display = 'flex';
        if (pollInterval) clearInterval(pollInterval);
    });

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;

        chatInput.value = '';

        const formData = new FormData();
        formData.append('action', 'obenlo_send_live_message');
        formData.append('session_id', sessionId);
        formData.append('message', msg);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(res => {
            if (res.success) {
                fetchMessages();
            }
        }).catch(err => console.error('Error sending message:', err));
    });

    function fetchMessages() {
        const url = new URL(ajaxUrl);
        url.searchParams.append('action', 'obenlo_fetch_live_messages');
        url.searchParams.append('session_id', sessionId);
        url.searchParams.append('last_id', lastId);

        fetch(url)
            .then(response => response.json())
            .then(res => {
                if (res.success && res.data.length > 0) {
                    res.data.forEach(msg => {
                        if (msg.id > lastId) {
                            appendMessage(msg.content, msg.is_staff);
                            lastId = msg.id;
                        }
                    });
                    scrollBottom();
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    function appendMessage(content, isStaff) {
        const align = isStaff ? 'margin-right:40px; background:#fff;' : 'margin-left:40px; background:#e61e4d; color:white;';
        const div = document.createElement('div');
        div.style.cssText = `padding:12px; border-radius:12px; font-size:0.9em; box-shadow:0 2px 5px rgba(0,0,0,0.02); ${align}`;
        div.innerHTML = content;
        chatContent.appendChild(div);
        scrollBottom();
    }

    function scrollBottom() {
        chatContent.scrollTop = chatContent.scrollHeight;
    }
});
</script>
