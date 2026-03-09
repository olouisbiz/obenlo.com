<?php
/**
 * Communication System Logic - Tickets & Broadcasts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Communication {

    public function init() {
        add_shortcode( 'obenlo_support_page', array( $this, 'render_support_page' ) );
        add_shortcode( 'obenlo_messages_page', array( $this, 'render_messages_center' ) );
        add_action( 'admin_post_obenlo_submit_ticket', array( $this, 'handle_submit_ticket' ) );
        add_action( 'admin_post_nopriv_obenlo_submit_ticket', array( $this, 'handle_submit_ticket' ) );
        add_action( 'admin_post_obenlo_send_broadcast', array( $this, 'handle_send_broadcast' ) );
        add_action( 'admin_post_obenlo_send_direct_message', array( $this, 'handle_send_direct_message' ) );
        add_action( 'admin_post_obenlo_submit_ticket_reply', array( $this, 'handle_submit_ticket_reply' ) );
        add_action( 'admin_post_obenlo_update_ticket_status', array( $this, 'handle_ticket_status' ) );
        
        // Live Chat AJAX
        add_action( 'wp_ajax_nopriv_obenlo_send_live_message', array( $this, 'handle_send_live_message' ) );
        add_action( 'wp_ajax_obenlo_send_live_message', array( $this, 'handle_send_live_message' ) );
        add_action( 'wp_ajax_nopriv_obenlo_fetch_live_messages', array( $this, 'handle_fetch_live_messages' ) );
        add_action( 'wp_ajax_obenlo_fetch_live_messages', array( $this, 'handle_fetch_live_messages' ) );
        
        // Add Contact Host button to listings
        add_filter( 'the_content', array( $this, 'add_contact_button_to_listing' ) );
        
        // Telegram Webhook
        add_action( 'rest_api_init', array( $this, 'register_telegram_webhook' ) );
    }

    public function register_telegram_webhook() {
        register_rest_route( 'obenlo/v1', '/telegram-webhook', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'handle_telegram_webhook' ),
            'permission_callback' => '__return_true'
        ) );
    }

    public function handle_telegram_webhook( WP_REST_Request $request ) {
        $body = $request->get_json_params();
        
        // Ensure this is a message we care about
        if ( ! isset($body['message']) || ! isset($body['message']['reply_to_message']) ) {
            return new WP_REST_Response( 'Not a reply', 200 );
        }

        $reply_text = isset($body['message']['text']) ? sanitize_textarea_field($body['message']['text']) : '';
        $original_text = isset($body['message']['reply_to_message']['text']) ? $body['message']['reply_to_message']['text'] : '';

        // Extract session ID from the original message (e.g. "Session: guest_ABCD")
        if ( preg_match('/Session:\s*([a-zA-Z0-9_]+)/', $original_text, $matches) ) {
            $session_id = $matches[1];
            
            if ( $reply_text && $session_id ) {
                // Insert message back into WordPress as a staff reply
                $message_id = wp_insert_post( array(
                    'post_type'    => 'obenlo_message',
                    'post_content' => $reply_text,
                    'post_status'  => 'publish',
                    'post_author'  => 1, // System/Admin user
                    'post_title'   => 'Telegram Agent Reply'
                ) );

                if ( $message_id ) {
                    update_post_meta( $message_id, '_obenlo_chat_session', $session_id );
                    update_post_meta( $message_id, '_obenlo_is_staff_reply', '1' );
                    return new WP_REST_Response( 'Success', 200 );
                }
            }
        }
        
        return new WP_REST_Response( 'Failed to parse', 400 );
    }

    public function add_contact_button_to_listing( $content ) {
        if ( is_singular('listing') && is_main_query() ) {
            $host_id = get_post_field( 'post_author', get_the_ID() );
            if ( $host_id != get_current_user_id() ) {
                $contact_url = add_query_arg( array(
                    'recipient_id' => $host_id,
                    'new_chat'     => 1
                ), home_url('/messages') );

                $button = '<div style="margin:20px 0; padding:20px; border:1px solid #eee; border-radius:12px; display:flex; justify-content:space-between; align-items:center; background:#f9f9f9;">';
                $button .= '<div><strong>Have questions?</strong><br><span style="font-size:0.9em; color:#666;">Chat directly with the host before booking.</span></div>';
                $button .= '<a href="' . esc_url( $contact_url ) . '" style="background:#222; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold;">Contact Host</a>';
                $button .= '</div>';
                $content .= $button;
            }
        }
        return $content;
    }

    /**
     * Handle sending a direct message
     */
    public function handle_send_direct_message() {
        if ( ! is_user_logged_in() ) {
            wp_die('Please log in');
        }

        if ( ! isset( $_POST['message_nonce'] ) || ! wp_verify_nonce( $_POST['message_nonce'], 'send_message' ) ) {
            wp_die('Security check failed');
        }

        $sender_id = get_current_user_id();
        $recipient_id = intval( $_POST['recipient_id'] );
        $content = wp_kses_post( $_POST['message_content'] );
        $thread_id = isset( $_POST['thread_id'] ) ? sanitize_text_field( $_POST['thread_id'] ) : $this->generate_thread_id( $sender_id, $recipient_id );

        $message_id = wp_insert_post( array(
            'post_type'    => 'obenlo_message',
            'post_title'   => "Message Thread: $thread_id",
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_author'  => $sender_id
        ) );

        if ( $message_id ) {
            update_post_meta( $message_id, '_obenlo_recipient_id', $recipient_id );
            update_post_meta( $message_id, '_obenlo_thread_id', $thread_id );
            update_post_meta( $message_id, '_obenlo_is_read', 0 );

            // Notify Recipient
            Obenlo_Booking_Notifications::notify_message_event( $message_id, $recipient_id );

            $redirect_url = add_query_arg( 'msg_sent', '1', wp_get_referer() );
            if ( isset($_POST['redirect_to_thread']) ) {
                $redirect_url = add_query_arg( 'thread', $thread_id, home_url('/messages') );
            }
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    private function generate_thread_id( $u1, $u2 ) {
        $ids = array( $u1, $u2 );
        sort( $ids );
        return "thread_" . $ids[0] . "_" . $ids[1];
    }

    public function render_messages_center() {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to see messages.</p>';
        }

        $current_user_id = get_current_user_id();
        $threads = $this->get_user_threads( $current_user_id );
        $active_thread = isset( $_GET['thread'] ) ? sanitize_text_field( $_GET['thread'] ) : '';

        // Auto-initiate new chat if parameters present
        if ( isset($_GET['new_chat']) && isset($_GET['recipient_id']) ) {
            $recipient_id = intval($_GET['recipient_id']);
            $potential_thread = $this->generate_thread_id( $current_user_id, $recipient_id );
            $active_thread = $potential_thread;
        }

        ob_start();
        ?>
        <div class="obenlo-message-center" style="display:grid; grid-template-columns: 320px 1fr; gap:0; height:750px; background:#fff; border:1px solid #eee; border-radius:20px; overflow:hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <!-- Threads Sidebar -->
            <div class="message-threads" style="border-right:1px solid #f0f0f0; background:#fcfcfc; overflow-y:auto; display:flex; flex-direction:column;">
                <div style="padding:25px 20px; border-bottom:1px solid #f0f0f0; font-weight:800; background:#fff; font-size:1.1rem; color:#222; display:flex; justify-content:space-between; align-items:center;">
                    Inbox
                    <span style="background:#e61e4d; color:#fff; font-size:0.7rem; padding:2px 8px; border-radius:10px;"><?php echo count($threads); ?></span>
                </div>
                
                <div style="flex-grow:1;">
                    <?php if ( empty($threads) ) : ?>
                        <div style="padding:40px 20px; text-align:center; color:#999;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:30px; height:30px; margin-bottom:10px; opacity:0.3;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <p style="font-size:0.9rem;">No messages yet.</p>
                        </div>
                    <?php else : 
                        foreach ( $threads as $thread ) : 
                            $other_user_id = ( $thread['u1'] == $current_user_id ) ? $thread['u2'] : $thread['u1'];
                            $other_user = get_userdata( $other_user_id );
                            $is_active = ( $active_thread === $thread['id'] );
                            $unread_class = ''; // Could implement unread logic
                        ?>
                            <a href="<?php echo add_query_arg( 'thread', $thread['id'] ); ?>" style="display:block; padding:20px; border-bottom:1px solid #f5f5f5; text-decoration:none; color:inherit; transition:all 0.2s; <?php echo $is_active ? 'background:#fff; border-left:4px solid #e61e4d;' : ''; ?>" onmouseover="this.style.background='#fff'">
                                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                    <strong style="font-size:1rem; color:<?php echo $is_active ? '#e61e4d' : '#222'; ?>;"><?php echo esc_html( $other_user->display_name ); ?></strong>
                                    <span style="font-size:0.7rem; color:#aaa;"><?php echo date('M j', strtotime($thread['date'])); ?></span>
                                </div>
                                <div style="font-size:0.85rem; color:#666; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.4;">
                                    <?php echo esc_html( $thread['last_msg'] ); ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="message-chat" style="display:flex; flex-direction:column; background:#fff;">
                <?php if ( $active_thread ) : 
                    $chat_messages = $this->get_thread_messages( $active_thread );
                    
                    if ( empty($chat_messages) && isset($_GET['recipient_id']) ) {
                        $recipient_id = intval($_GET['recipient_id']);
                    } elseif (!empty($chat_messages)) {
                        $recipient_id = ($chat_messages[0]->post_author == $current_user_id) ? get_post_meta($chat_messages[0]->ID, '_obenlo_recipient_id', true) : $chat_messages[0]->post_author;
                    } else {
                        $recipient_id = 0;
                    }
                    
                    $recipient = $recipient_id ? get_userdata( $recipient_id ) : null;
                ?>
                    <div style="padding:20px 30px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:15px; background:#fff; z-index:10;">
                        <div style="width:40px; height:40px; background:#f0f0f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#888;">
                            <?php echo $recipient ? strtoupper(substr($recipient->display_name, 0, 1)) : '?'; ?>
                        </div>
                        <div>
                            <div style="font-weight:800; font-size:1.1rem; color:#222;"><?php echo $recipient ? esc_html( $recipient->display_name ) : 'Conversation'; ?></div>
                            <div style="font-size:0.75rem; color:#10b981; font-weight:600;">Active Now</div>
                        </div>
                    </div>

                    <div id="obenlo-chat-box" style="flex-grow:1; padding:30px; overflow-y:auto; background:#fafafa; display:flex; flex-direction:column;">
                        <?php if ( empty($chat_messages) ) : ?>
                            <div style="text-align:center; color:#999; margin:auto;">
                                <div style="font-size:3rem; margin-bottom:15px;">👋</div>
                                <p style="font-weight:700; color:#444; margin:0;">Say hello!</p>
                                <p style="font-size:0.9rem;">Start your conversation with <?php echo $recipient ? esc_html($recipient->display_name) : 'this user'; ?>.</p>
                            </div>
                        <?php else : ?>
                            <?php foreach ( $chat_messages as $msg ) : 
                                $is_me = ( $msg->post_author == $current_user_id );
                                $bubble_style = $is_me ? 'background:#e61e4d; color:white; border-radius:15px 15px 2px 15px; margin-left:auto;' : 'background:#fff; color:#333; border-radius:15px 15px 15px 2px; margin-right:auto; border:1px solid #eee;';
                            ?>
                                <div style="max-width:75%; padding:14px 20px; margin-bottom:12px; font-size:0.95rem; line-height:1.5; box-shadow: 0 2px 5px rgba(0,0,0,0.02); <?php echo $bubble_style; ?>">
                                    <?php echo wp_kses_post( $msg->post_content ); ?>
                                    <div style="font-size:0.7rem; opacity:0.6; margin-top:6px; text-align:right;">
                                        <?php echo get_the_date( 'H:i', $msg->ID ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <div style="padding:25px 30px; border-top:1px solid #f0f0f0; background:#fff;">
                        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" style="display:flex; gap:15px; align-items:center;">
                            <input type="hidden" name="action" value="obenlo_send_direct_message">
                            <input type="hidden" name="recipient_id" value="<?php echo $recipient_id; ?>">
                            <input type="hidden" name="thread_id" value="<?php echo $active_thread; ?>">
                            <input type="hidden" name="redirect_to_thread" value="1">
                            <?php wp_nonce_field( 'send_message', 'message_nonce' ); ?>
                            
                            <div style="flex-grow:1; position:relative;">
                                <input type="text" name="message_content" required placeholder="Type your message here..." style="width:100%; padding:15px 25px; border:1px solid #eee; border-radius:30px; background:#f9f9f9; outline:none; transition:all 0.2s;" onfocus="this.style.background='#fff';this.style.borderColor='#e61e4d';this.style.boxShadow='0 0 0 4px rgba(230,30,77,0.05)'" onblur="this.style.background='#f9f9f9';this.style.borderColor='#eee';this.style.boxShadow='none'">
                            </div>
                            <button type="submit" style="background:#e61e4d; color:white; border:none; width:50px; height:50px; border-radius:50%; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; box-shadow: 0 4px 10px rgba(230,30,77,0.2);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px; height:20px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </form>
                    </div>
                <?php else : ?>
                    <div style="flex-grow:1; display:flex; align-items:center; justify-content:center; color:#999; flex-direction:column; background:#fafafa;">
                        <div style="background:#fff; width:100px; height:100px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:25px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#e61e4d" stroke-width="2" style="width:40px; height:40px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <h4 style="margin:0; color:#333; font-weight:800;">Your Messages</h4>
                        <p style="font-size:0.95rem; margin-top:8px;">Choose a contact from the left to start a conversation.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <script>
            // Scroll to bottom of chat
            var chatBox = document.getElementById('obenlo-chat-box');
            if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        </script>
        <?php
        return ob_get_clean();
    }

    private function get_user_threads( $user_id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT pm.meta_value as thread_id, p.post_content, p.post_author, p.post_date 
             FROM {$wpdb->postmeta} pm 
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
             WHERE pm.meta_key = '_obenlo_thread_id' 
             AND (p.post_author = %d OR p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_obenlo_recipient_id' AND meta_value = %d))
             ORDER BY p.post_date DESC",
            $user_id, $user_id
        ) );

        $threads = array();
        foreach ( $results as $row ) {
            if ( ! isset( $threads[$row->thread_id] ) ) {
                $parts = explode( '_', $row->thread_id );
                $threads[$row->thread_id] = array(
                    'id' => $row->thread_id,
                    'u1' => $parts[1],
                    'u2' => $parts[2],
                    'last_msg' => $row->post_content,
                    'date' => $row->post_date
                );
            }
        }
        return $threads;
    }

    private function get_thread_messages( $thread_id ) {
        return get_posts( array(
            'post_type' => 'obenlo_message',
            'meta_key'  => '_obenlo_thread_id',
            'meta_value' => $thread_id,
            'posts_per_page' => -1,
            'order' => 'ASC'
        ) );
    }

    public function render_support_page() {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to contact support.</p>';
        }

        $current_user_id = get_current_user_id();
        $ticket_id = isset( $_GET['ticket_id'] ) ? intval( $_GET['ticket_id'] ) : 0;

        ob_start();

        if ( $ticket_id ) {
            $this->render_ticket_details( $ticket_id, $current_user_id );
        } else {
            $this->render_ticket_form();
        }

        return ob_get_clean();
    }

    private function render_ticket_details( $ticket_id, $current_user_id ) {
        $ticket = get_post( $ticket_id );
        if ( ! $ticket || $ticket->post_type !== 'ticket' ) {
            echo '<p>Ticket not found.</p>';
            return;
        }

        // Security check: Only author or admin/agent
        if ( $ticket->post_author != $current_user_id && ! current_user_can( 'manage_support' ) ) {
            echo '<p>You do not have permission to view this ticket.</p>';
            return;
        }

        $status = get_post_meta( $ticket_id, '_obenlo_ticket_status', true );
        $type = get_post_meta( $ticket_id, '_obenlo_ticket_type', true );
        $attachments = get_attached_media( 'image', $ticket_id );
        
        // Get Ticket Conversation (Replies)
        $replies = get_posts( array(
            'post_type'      => 'obenlo_message', // Reuse messaging system for replies
            'meta_key'       => '_obenlo_ticket_parent_id',
            'meta_value'     => $ticket_id,
            'posts_per_page' => -1,
            'order'          => 'ASC'
        ) );
        
        $back_url = wp_get_referer() ?: home_url('/support');
        ?>
        <div class="obenlo-ticket-details" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <a href="<?php echo esc_url($back_url); ?>" style="color:#666; text-decoration:none;">← Back to Support</a>
                
                <?php if ( current_user_can('manage_support') || $ticket->post_author == $current_user_id ) : ?>
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="obenlo_update_ticket_status">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <?php wp_nonce_field( 'update_ticket_status_' . $ticket_id, 'status_nonce' ); ?>
                        <?php if ( $status === 'open' ) : ?>
                            <button type="submit" name="new_status" value="closed" style="background:#222; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9em;">Close Ticket</button>
                        <?php else : ?>
                            <button type="submit" name="new_status" value="open" style="background:#4CAF50; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9em;">Reopen Ticket</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
            
            <div style="background:#fff; border:1px solid #eee; padding:40px; border-radius:16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom:30px;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <h2 style="margin:0;"><?php echo esc_html( $ticket->post_title ); ?></h2>
                    <span style="background:<?php echo $status === 'open' ? '#e61e4d' : '#333'; ?>; color:#fff; padding:5px 12px; border-radius:20px; font-size:0.8em; font-weight:bold;"><?php echo strtoupper($status); ?></span>
                </div>
                
                <div style="font-size:0.9em; color:#666; margin-bottom:30px;">
                    Type: <?php echo esc_html(ucfirst($type)); ?> | Submitted on: <?php echo get_the_date( '', $ticket_id ); ?>
                </div>

                <div style="line-height:1.7; color:#333; margin-bottom:30px; background:#f9f9f9; padding:20px; border-radius:12px;">
                    <?php echo wpautop( esc_html( $ticket->post_content ) ); ?>
                    
                    <?php if ( ! empty($attachments) ) : ?>
                        <div style="margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <?php foreach ( $attachments as $att ) : ?>
                                    <a href="<?php echo wp_get_attachment_url($att->ID); ?>" target="_blank">
                                        <img src="<?php echo wp_get_attachment_image_url($att->ID, 'thumbnail'); ?>" style="width:100px; height:100px; object-fit:cover; border-radius:8px;">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Conversation History -->
                <div class="ticket-conversation">
                    <?php if ( ! empty($replies) ) : ?>
                        <h4 style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Messages</h4>
                        <?php foreach ( $replies as $reply ) : 
                            $is_admin_reply = user_can( $reply->post_author, 'administrator' );
                            $is_internal = get_post_meta( $reply->ID, '_obenlo_is_internal_note', true );
                            
                            // Only show internal notes to staff
                            if ( $is_internal && ! current_user_can('manage_support') ) continue;
                            
                            $bubble_style = $is_admin_reply ? 'background:#f1f1f1; border-left:4px solid #e61e4d;' : 'background:#fff; border:1px solid #eee;';
                            if ( $is_internal ) $bubble_style = 'background:#fff9c4; border-left:4px solid #fbc02d;';
                        ?>
                            <div style="padding:15px 20px; border-radius:12px; margin-bottom:15px; <?php echo $bubble_style; ?>">
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <strong>
                                        <?php 
                                        if ( $is_internal ) echo '🔒 Internal Note';
                                        elseif ( $is_admin_reply ) echo 'Obenlo Support';
                                        else echo esc_html(get_userdata($reply->post_author)->display_name); 
                                        ?>
                                    </strong>
                                    <span style="font-size:0.8em; color:#999;"><?php echo get_the_date('M j, H:i', $reply->ID); ?></span>
                                </div>
                                <div style="font-size:0.95em; line-height:1.6;">
                                    <?php echo wpautop( esc_html($reply->post_content) ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Reply Form -->
                <?php if ( $status === 'open' ) : ?>
                    <div style="margin-top:40px; border-top:2px solid #f1f1f1; padding-top:30px;">
                        <h4 style="margin-bottom:15px;">Add a Reply</h4>
                        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                            <input type="hidden" name="action" value="obenlo_submit_ticket_reply">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                            <?php wp_nonce_field( 'submit_ticket_reply_' . $ticket_id, 'reply_nonce' ); ?>
                            
                            <textarea name="reply_content" required rows="4" placeholder="Type your message here..." style="width:100%; padding:15px; border:1px solid #ddd; border-radius:12px; margin-bottom:15px; font-family:inherit;"></textarea>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <?php if ( current_user_can('manage_support') ) : ?>
                                    <label style="font-size:0.9em; display:flex; align-items:center; gap:8px; cursor:pointer;">
                                        <input type="checkbox" name="is_internal_note" value="1"> 
                                        <span>Post as Internal Note (Staff only)</span>
                                    </label>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                <button type="submit" style="background:#e61e4d; color:white; border:none; padding:12px 30px; border-radius:8px; cursor:pointer; font-weight:bold;">Send Message</button>
                            </div>
                        </form>
                    </div>
                <?php else : ?>
                    <div style="margin-top:30px; text-align:center; padding:20px; background:#f9f9f9; border-radius:12px; color:#666;">
                        This ticket is closed. If you still need help, please reopen the ticket or create a new one.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_ticket_form() {
        ?>
        <div class="obenlo-support-page" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
            <h2>Contact Obenlo Support</h2>
            <p>Need help with a booking or want to raise a dispute with a host? Fill out the form below.</p>
            
            <?php if ( isset($_GET['ticket_sent']) ) : ?>
                <div style="background:#d4edda; padding:20px; margin-bottom:30px; border-radius:12px; border:1px solid #c3e6cb; color:#155724;">
                    <strong>Success!</strong> Your ticket has been received. Our team will review it and get back to you at your registration email.
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" enctype="multipart/form-data" style="background:#fff; border:1px solid #eee; padding:30px; border-radius:16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <input type="hidden" name="action" value="obenlo_submit_ticket">
                <?php wp_nonce_field( 'submit_ticket', 'ticket_nonce' ); ?>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Reason for Contact:</label>
                    <select name="ticket_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                        <option value="support">General Support</option>
                        <option value="dispute">Report an Issue / Dispute with Host</option>
                        <option value="account">Account / Billing Question</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Subject:</label>
                    <input type="text" name="ticket_title" required placeholder="Brief summary of the issue" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Describe the Issue:</label>
                    <textarea name="ticket_content" required rows="6" placeholder="Please provide as much detail as possible..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;"></textarea>
                </div>

                <div style="margin-bottom:30px; background:#f9f9f9; padding:20px; border-radius:8px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Evidence / Photo (Optional):</label>
                    <p style="font-size:0.85em; color:#666; margin-bottom:12px;">If this is a dispute, please upload photos to support your claim.</p>
                    <input type="file" name="ticket_attachments[]" multiple accept="image/*">
                </div>

                <button type="submit" style="background:#e61e4d; color:white; border:none; padding:15px 30px; border-radius:10px; cursor:pointer; font-weight:bold; width:100%; font-size:1.1em; transition: transform 0.2s;">
                    Submit Ticket to Obenlo
                </button>
            </form>
        </div>
        <?php
    }

    /**
     * Handle support ticket submission (Host Support or Guest Dispute)
     */
    public function handle_submit_ticket() {
        if ( ! isset( $_POST['ticket_nonce'] ) || ! wp_verify_nonce( $_POST['ticket_nonce'], 'submit_ticket' ) ) {
            wp_die('Security check failed');
        }

        $user_id = get_current_user_id();
        $title = sanitize_text_field( $_POST['ticket_title'] );
        $content = wp_kses_post( $_POST['ticket_content'] );
        $type = sanitize_text_field( $_POST['ticket_type'] ); // 'support' or 'dispute'
        $target_host_id = isset($_POST['host_id']) ? intval($_POST['host_id']) : 0;

        $ticket_id = wp_insert_post( array(
            'post_type'    => 'ticket',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_author'  => $user_id
        ) );

        if ( $ticket_id && ! is_wp_error( $ticket_id ) ) {
            update_post_meta( $ticket_id, '_obenlo_ticket_type', $type );
            update_post_meta( $ticket_id, '_obenlo_ticket_status', 'open' );
            if ( $target_host_id ) {
                update_post_meta( $ticket_id, '_obenlo_target_host_id', $target_host_id );
            }

            // Handle Photo Attachments (for proof)
            if ( ! empty( $_FILES['ticket_attachments']['name'][0] ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                $files = $_FILES['ticket_attachments'];
                foreach ( $files['name'] as $key => $value ) {
                    if ( $files['name'][$key] ) {
                        $file = array(
                            'name'     => $files['name'][$key],
                            'type'     => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error'    => $files['error'][$key],
                            'size'     => $files['size'][$key]
                        );
                        $attachment_id = media_handle_sideload( $file, $ticket_id );
                        if ( is_wp_error( $attachment_id ) ) {
                            // Log error?
                        }
                    }
                }
            }

            // Notify Admin
            Obenlo_Booking_Notifications::notify_ticket_event( $ticket_id, 'new_ticket' );

            $redirect_url = remove_query_arg( 'ticket_sent', wp_get_referer() );
            $redirect_url = add_query_arg( 'obenlo_modal', 'ticket_submitted', $redirect_url );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Handle support ticket reply submission
     */
    public function handle_submit_ticket_reply() {
        if ( ! is_user_logged_in() ) wp_die('Please log in');

        $ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
        if ( ! isset( $_POST['reply_nonce'] ) || ! wp_verify_nonce( $_POST['reply_nonce'], 'submit_ticket_reply_' . $ticket_id ) ) {
            wp_die('Security check failed');
        }

        $ticket = get_post( $ticket_id );
        $user_id = get_current_user_id();
        
        // Security: Author or Agent
        if ( $ticket->post_author != $user_id && ! current_user_can('manage_support') ) {
            wp_die('Unauthorized');
        }

        $content = wp_kses_post( $_POST['reply_content'] );

        $reply_id = wp_insert_post( array(
            'post_type'    => 'obenlo_message',
            'post_title'   => "Reply to Ticket #$ticket_id",
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_author'  => $user_id
        ) );

        if ( $reply_id ) {
            update_post_meta( $reply_id, '_obenlo_ticket_parent_id', $ticket_id );
            
            $is_internal = isset($_POST['is_internal_note']) && current_user_can('manage_support');
            if ( $is_internal ) {
                update_post_meta( $reply_id, '_obenlo_is_internal_note', '1' );
            } else {
                // Notify other party (don't notify for internal notes)
                Obenlo_Booking_Notifications::notify_ticket_event( $ticket_id, 'ticket_reply' );
            }

            wp_safe_redirect( add_query_arg( 'ticket_id', $ticket_id, wp_get_referer() ) );
            exit;
        }
    }

    /**
     * Handle ticket status updates
     */
    public function handle_ticket_status() {
        $ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
        if ( ! isset( $_POST['status_nonce'] ) || ! wp_verify_nonce( $_POST['status_nonce'], 'update_ticket_status_' . $ticket_id ) ) {
            wp_die('Security check failed');
        }

        $ticket = get_post( $ticket_id );
        $user_id = get_current_user_id();

        if ( $ticket->post_author != $user_id && ! current_user_can('manage_support') ) {
            wp_die('Unauthorized');
        }

        $new_status = sanitize_text_field( $_POST['new_status'] );
        update_post_meta( $ticket_id, '_obenlo_ticket_status', $new_status );

        wp_safe_redirect( add_query_arg( 'ticket_id', $ticket_id, wp_get_referer() ) );
        exit;
    }

    /**
     * Handle Admin Broadcast sending
     */
    public function handle_send_broadcast() {
        if ( ! current_user_can( 'administrator' ) ) {
            wp_die( 'Unauthorized' );
        }

        if ( ! isset( $_POST['broadcast_nonce'] ) || ! wp_verify_nonce( $_POST['broadcast_nonce'], 'send_broadcast' ) ) {
            wp_die('Security check failed');
        }

        $title = sanitize_text_field( $_POST['broadcast_title'] );
        $content = wp_kses_post( $_POST['broadcast_content'] );
        $recipient_role = sanitize_text_field( $_POST['broadcast_role'] ); // 'host', 'guest', or 'all'

        $broadcast_id = wp_insert_post( array(
            'post_type'    => 'broadcast',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish'
        ) );

        if ( $broadcast_id ) {
            update_post_meta( $broadcast_id, '_obenlo_broadcast_recipient', $recipient_role );

            // Notify Users via Email
            Obenlo_Booking_Notifications::notify_broadcast_event( $broadcast_id, $recipient_role );

            wp_safe_redirect( add_query_arg( 'broadcast_sent', '1', wp_get_referer() ) );
            exit;
        }
    }

    /**
     * Static helper to get open tickets for a user
     */
    public static function get_user_tickets( $user_id ) {
        return get_posts( array(
            'post_type'   => 'ticket',
            'author'      => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ) );
    }

    /**
     * Static helper to get recent broadcasts for a user role
     */
    public static function get_role_broadcasts( $role ) {
        return get_posts( array(
            'post_type' => 'broadcast',
            'meta_query' => array(
                'relation' => 'OR',
                array( 'key' => '_obenlo_broadcast_recipient', 'value' => $role ),
                array( 'key' => '_obenlo_broadcast_recipient', 'value' => 'all' )
            ),
            'posts_per_page' => 5
        ) );
    }

    /**
     * Handle Live Chat AJAX Send
     */
    public function handle_send_live_message() {
        $content = sanitize_textarea_field( $_POST['message'] );
        $session_id = sanitize_text_field( $_POST['session_id'] );
        $is_staff = current_user_can('manage_support');
        
        if ( empty($content) || empty($session_id) ) wp_send_json_error('Missing data');

        $args = array(
            'post_type'    => 'obenlo_message',
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id() ?: 0,
            'post_title'   => 'Live Chat Message'
        );

        $message_id = wp_insert_post( $args );

        if ( $message_id ) {
            update_post_meta( $message_id, '_obenlo_chat_session', $session_id );
            update_post_meta( $message_id, '_obenlo_is_staff_reply', $is_staff ? '1' : '0' );
            
            // Webhook for staff notification if it's a new session or visitor message
            if ( ! $is_staff ) {
                Obenlo_Booking_Notifications::notify_live_chat_webhook( $session_id, $content );
            }

            wp_send_json_success( array(
                'message_id' => $message_id,
                'time'       => current_time('H:i')
            ) );
        }
        wp_send_json_error('Save failed');
    }

    /**
     * Handle Live Chat AJAX Fetch
     */
    public function handle_fetch_live_messages() {
        $session_id = sanitize_text_field( $_GET['session_id'] );
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

        if ( empty($session_id) ) wp_send_json_error('Missing session');

        $args = array(
            'post_type'      => 'obenlo_message',
            'post_status'    => 'any', // Required for guests to read custom post types
            'meta_key'       => '_obenlo_chat_session',
            'meta_value'     => $session_id,
            'posts_per_page' => -1,
            'order'          => 'ASC',
            'orderby'        => 'ID'
        );

        // Fetch all messages for the session, then filter in PHP
        $messages = get_posts( $args );
        $data = array();

        foreach ( $messages as $msg ) {
            if ( $msg->ID <= $last_id ) continue; // Only send messages newer than the last fetched
            
            $data[] = array(
                'id'       => $msg->ID,
                'content'  => wp_kses_post( $msg->post_content ),
                'is_staff' => get_post_meta( $msg->ID, '_obenlo_is_staff_reply', true ) === '1',
                'time'     => get_the_date( 'H:i', $msg->ID )
            );
        }

        wp_send_json_success( $data );
    }
}
