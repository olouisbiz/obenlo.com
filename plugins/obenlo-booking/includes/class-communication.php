<?php
/**
 * Communication System Logic - Tickets & Broadcasts
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Communication
{

    public function init()
    {
        add_shortcode('obenlo_support_page', array($this, 'render_support_page'));
        add_shortcode('obenlo_messages_page', array($this, 'render_messages_center'));
        add_action('admin_post_obenlo_submit_ticket', array($this, 'handle_submit_ticket'));
        add_action('admin_post_nopriv_obenlo_submit_ticket', array($this, 'handle_submit_ticket'));
        add_action('admin_post_obenlo_send_broadcast', array($this, 'handle_send_broadcast'));
        add_action('admin_post_obenlo_submit_ticket_reply', array($this, 'handle_submit_ticket_reply'));
        add_action('admin_post_obenlo_update_ticket_status', array($this, 'handle_ticket_status'));

        // Native Host-Guest Chat AJAX
        add_action('wp_ajax_obenlo_send_chat_message', array($this, 'handle_send_chat_message'));
        add_action('wp_ajax_obenlo_fetch_chat_messages', array($this, 'handle_fetch_chat_messages'));
        add_action('wp_ajax_obenlo_fetch_chat_contacts', array($this, 'handle_fetch_chat_contacts'));

        // Add Contact Host button to listings
        add_filter('the_content', array($this, 'add_contact_button_to_listing'));

        // Render chat window logic in footer (needed for obenloStartChatWith)
        add_action('wp_footer', array($this, 'render_frontend_chat_widget'));

        // Contact Us form (available to all visitors)
        add_action('wp_ajax_obenlo_submit_contact_form', array($this, 'handle_contact_form'));
        add_action('wp_ajax_nopriv_obenlo_submit_contact_form', array($this, 'handle_contact_form'));

        // Guest Contact Host (no login required)
        add_action('wp_ajax_nopriv_obenlo_guest_contact_host', array($this, 'handle_guest_contact_host'));
        add_action('wp_ajax_obenlo_guest_contact_host', array($this, 'handle_guest_contact_host'));

        // Guest contact modal injected for non-logged-in users
        add_action('wp_footer', array($this, 'render_guest_contact_modal'));

    }

    public function handle_contact_form()
    {
        if (!isset($_POST['contact_nonce']) || !wp_verify_nonce($_POST['contact_nonce'], 'obenlo_contact_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $name    = sanitize_text_field($_POST['contact_name'] ?? '');
        $email   = sanitize_email($_POST['contact_email'] ?? '');
        $message = sanitize_textarea_field($_POST['contact_message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error(array('message' => 'Please fill in all fields.'));
        }

        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }

        $to      = 'info@obenlo.com';
        $subject = 'Contact Form: Message from ' . $name;

        $body_html = '
        <p style="margin:0 0 16px 0;">You have received a new message via the <strong>Contact Us</strong> form.</p>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr>
                <td style="padding:10px 14px; background:#f9f9f9; border-radius:8px 8px 0 0; border-bottom:1px solid #eee; font-weight:700; width:120px;">Name</td>
                <td style="padding:10px 14px; background:#f9f9f9; border-radius:8px 8px 0 0; border-bottom:1px solid #eee;">' . esc_html($name) . '</td>
            </tr>
            <tr>
                <td style="padding:10px 14px; border-bottom:1px solid #eee; font-weight:700;">Email</td>
                <td style="padding:10px 14px; border-bottom:1px solid #eee;"><a href="mailto:' . esc_attr($email) . '" style="color:#e61e4d;">' . esc_html($email) . '</a></td>
            </tr>
            <tr>
                <td style="padding:10px 14px; border-radius:0 0 8px 8px; font-weight:700; vertical-align:top;">Message</td>
                <td style="padding:10px 14px; border-radius:0 0 8px 8px;">' . nl2br(esc_html($message)) . '</td>
            </tr>
        </table>
        <p style="margin:24px 0 0 0; font-size:0.85rem; color:#888;">Simply reply to this email — your reply goes directly to ' . esc_html($name) . '\'s inbox.</p>';

        $html    = Obenlo_Booking_Notifications::wrap_template($subject, $body_html);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Obenlo <info@obenlo.com>',
            'Reply-To: ' . $name . ' <' . $email . '>',
        );

        $sent = wp_mail($to, $subject, $html, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => "Your message has been sent. We'll get back to you soon!"));
        } else {
            wp_send_json_error(array('message' => 'Failed to send your message. Please try again.'));
        }
    }



    public function handle_guest_contact_host()
    {
        error_log('Obenlo Debug: handle_guest_contact_host started');
        if (!isset($_POST['guest_contact_nonce']) || !wp_verify_nonce($_POST['guest_contact_nonce'], 'obenlo_guest_contact_nonce')) {
            error_log('Obenlo Debug: Nonce verification failed');
            wp_send_json_error(array('message' => 'Security check failed.'));
        }

        $visitor_name    = sanitize_text_field($_POST['visitor_name'] ?? '');
        $visitor_email   = sanitize_email($_POST['visitor_email'] ?? '');
        $visitor_message = sanitize_textarea_field($_POST['visitor_message'] ?? '');
        $host_id         = intval($_POST['host_id'] ?? 0);
        $listing_id      = intval($_POST['listing_id'] ?? 0);

        error_log("Obenlo Debug: Visitor: $visitor_name <$visitor_email>, Host ID: $host_id, Listing ID: $listing_id");

        if (empty($visitor_name) || empty($visitor_email) || empty($visitor_message) || !$host_id) {
            error_log('Obenlo Debug: Missing fields');
            wp_send_json_error(array('message' => 'Please fill in all fields.'));
        }

        if (!is_email($visitor_email)) {
            error_log('Obenlo Debug: Invalid visitor email');
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }

        $host = get_userdata($host_id);
        if (!$host) {
            error_log('Obenlo Debug: Host not found');
            wp_send_json_error(array('message' => 'Host not found.'));
        }

        error_log("Obenlo Debug: Sending guest contact email to host: " . $host->user_email);

        $listing_title = $listing_id ? get_the_title($listing_id) : 'a listing';
        $subject       = 'A visitor wants to contact you about: ' . $listing_title;

        $body_html = '
        <p style="margin:0 0 16px 0;">A visitor has reached out about your listing <strong>' . esc_html($listing_title) . '</strong>.</p>
        <table style="width:100%; border-collapse:collapse; font-size:14px; margin-bottom:20px;">
            <tr>
                <td style="padding:10px 14px; background:#f9f9f9; border-radius:8px 8px 0 0; border-bottom:1px solid #eee; font-weight:700; width:120px;">Name</td>
                <td style="padding:10px 14px; background:#f9f9f9; border-bottom:1px solid #eee;">' . esc_html($visitor_name) . '</td>
            </tr>
            <tr>
                <td style="padding:10px 14px; border-bottom:1px solid #eee; font-weight:700;">Email</td>
                <td style="padding:10px 14px; border-bottom:1px solid #eee;"><a href="mailto:' . esc_attr($visitor_email) . '" style="color:#e61e4d;">' . esc_html($visitor_email) . '</a></td>
            </tr>
            <tr>
                <td style="padding:10px 14px; border-radius:0 0 8px 8px; font-weight:700; vertical-align:top;">Message</td>
                <td style="padding:10px 14px; border-radius:0 0 8px 8px;">' . nl2br(esc_html($visitor_message)) . '</td>
            </tr>
        </table>
        <p style="margin:0; font-size:0.85rem; color:#888;">Simply reply to this email to respond directly to ' . esc_html($visitor_name) . '.</p>';

        $html    = Obenlo_Booking_Notifications::wrap_template($subject, $body_html);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Obenlo <info@obenlo.com>',
            'Reply-To: ' . $visitor_name . ' <' . $visitor_email . '>',
            'Cc: info@obenlo.com',
        );

        error_log("Obenlo Debug: Headers: " . print_r($headers, true));

        $sent = wp_mail($host->user_email, $subject, $html, $headers);

        error_log("Obenlo Debug: wp_mail result: " . ($sent ? 'TRUE' : 'FALSE'));

        if ($sent) {
            wp_send_json_success(array('message' => 'Your message has been sent to the host! They\'ll reply to your email shortly.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send. Please try again.'));
        }
    }

    public function render_guest_contact_modal()
    {
        if (is_user_logged_in()) return; // Only needed for guests
        ?>
        <div id="obenlo-guest-contact-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:99999; align-items:center; justify-content:center; padding:20px;">
            <div style="background:#fff; width:100%; max-width:480px; border-radius:20px; padding:32px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.15); font-family:'Inter',sans-serif;">
                <button onclick="document.getElementById('obenlo-guest-contact-modal').style.display='none';" style="position:absolute; top:16px; right:20px; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#999; line-height:1;">&times;</button>

                <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px;">
                    <div id="gcm-avatar-wrap" style="flex-shrink:0;"></div>
                    <div>
                        <div id="gcm-host-name" style="font-weight:900; font-size:1.1rem; color:#111;"></div>
                        <div style="font-size:0.85rem; color:#666; margin-top:2px; display:flex; align-items:center; gap:6px;">
                            <span style="width:7px; height:7px; background:#10b981; border-radius:50%; display:inline-block;"></span>
                            Usually responds within an hour
                        </div>
                    </div>
                </div>

                <h3 style="margin:0 0 6px 0; font-size:1.1rem; font-weight:800; color:#111;">Send a message</h3>
                <p style="margin:0 0 20px 0; color:#666; font-size:0.9rem;">No account needed — the host will reply directly to your email.</p>

                <form id="obenlo-guest-contact-form">
                    <input type="hidden" name="action" value="obenlo_guest_contact_host">
                    <input type="hidden" name="host_id" id="gcm-host-id" value="">
                    <input type="hidden" name="listing_id" id="gcm-listing-id" value="<?php echo esc_attr(get_queried_object_id()); ?>">
                    <input type="hidden" name="guest_contact_nonce" value="<?php echo wp_create_nonce('obenlo_guest_contact_nonce'); ?>">

                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px; color:#333;">Your Name *</label>
                        <input type="text" name="visitor_name" required placeholder="Jane Smith" style="width:100%; padding:11px 14px; border:1.5px solid #ddd; border-radius:10px; font-size:0.95rem; outline:none; box-sizing:border-box; transition:border 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px; color:#333;">Your Email *</label>
                        <input type="email" name="visitor_email" required placeholder="jane@example.com" style="width:100%; padding:11px 14px; border:1.5px solid #ddd; border-radius:10px; font-size:0.95rem; outline:none; box-sizing:border-box; transition:border 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px; color:#333;">Your Message *</label>
                        <textarea name="visitor_message" required rows="4" placeholder="Hi, I'm interested in booking your listing..." style="width:100%; padding:11px 14px; border:1.5px solid #ddd; border-radius:10px; font-size:0.95rem; outline:none; resize:vertical; box-sizing:border-box; transition:border 0.2s; font-family:inherit;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'"></textarea>
                    </div>

                    <button type="submit" id="gcm-submit-btn" style="width:100%; background:#e61e4d; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:800; font-size:1rem; cursor:pointer; transition:all 0.3s;">
                        Send Message
                    </button>
                    <div id="gcm-response" style="margin-top:14px; font-size:0.9rem; font-weight:700; text-align:center; display:none;"></div>
                </form>
            </div>
        </div>

        <script>
        window.obenloOpenGuestContact = function(hostId, hostName, hostAvatar) {
            document.getElementById('gcm-host-id').value = hostId;
            document.getElementById('gcm-host-name').innerText = 'Message ' + hostName;
            var avatarWrap = document.getElementById('gcm-avatar-wrap');
            avatarWrap.innerHTML = hostAvatar
                ? '<img src="' + hostAvatar + '" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.1);">'
                : '';
            document.getElementById('obenlo-guest-contact-modal').style.display = 'flex';
            document.getElementById('gcm-response').style.display = 'none';
            document.getElementById('obenlo-guest-contact-form').reset();
            document.getElementById('gcm-host-id').value = hostId;
        };

        document.getElementById('obenlo-guest-contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('gcm-submit-btn');
            var resp = document.getElementById('gcm-response');
            btn.innerText = 'Sending...';
            btn.disabled = true;
            resp.style.display = 'none';

            var formData = new FormData(this);
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    btn.innerText = 'Send Message';
                    btn.disabled = false;
                    resp.style.display = 'block';
                    if (data.success) {
                        resp.style.color = '#10b981';
                        resp.innerText = data.data.message;
                        document.getElementById('obenlo-guest-contact-form').reset();
                        setTimeout(function(){ document.getElementById('obenlo-guest-contact-modal').style.display='none'; }, 3500);
                    } else {
                        resp.style.color = '#e61e4d';
                        resp.innerText = data.data.message || 'Error. Please try again.';
                    }
                })
                .catch(function() {
                    btn.innerText = 'Send Message';
                    btn.disabled = false;
                    resp.style.display = 'block';
                    resp.style.color = '#e61e4d';
                    resp.innerText = 'Network error. Please try again.';
                });
        });

        // Close on backdrop click
        document.getElementById('obenlo-guest-contact-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        </script>
        <?php
    }

    public function add_contact_button_to_listing($content)
    {
        if (is_singular('listing') && is_main_query()) {
            $post_id    = get_the_ID();
            $parent_id  = wp_get_post_parent_id($post_id);

            // Resolve host: use current listing's author; if child, fall back to parent author
            $host_source = ($parent_id > 0) ? $parent_id : $post_id;
            $host_id     = get_post_field('post_author', $host_source);

            if ($host_id != get_current_user_id()) {
                $host_name   = get_the_author_meta('display_name', $host_id);
                $host_avatar = get_avatar_url($host_id);
                $logged_in   = is_user_logged_in();

                // Button action: logged-in → chat window; guest → email form modal
                if ($logged_in) {
                    $onclick = "if(window.obenloStartChatWith){window.obenloStartChatWith($host_id, '" . esc_js($host_name) . "', '" . esc_url($host_avatar) . "');}";
                } else {
                    $onclick = "window.obenloOpenGuestContact($host_id, '" . esc_js($host_name) . "', '" . esc_url($host_avatar) . "');";
                }

                $button = '
                <div style="margin:40px 0; padding:30px; border-radius:24px; display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.6); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(0,0,0,0.05); box-shadow: 0 15px 35px rgba(0,0,0,0.03); font-family: \'Inter\', sans-serif;">
                    <div style="display:flex; align-items:center; gap:20px;">
                        <img src="' . esc_url($host_avatar) . '" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:3px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                        <div>
                            <div style="font-weight:900; color:#111; font-size:1.15rem; letter-spacing:-0.02em;">Hosted by ' . esc_html($host_name) . '</div>
                            <div style="font-size:0.9rem; color:#666; margin-top:4px; display:flex; align-items:center; gap:6px;">
                                <span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block;"></span>
                                Usually responds within an hour
                            </div>
                        </div>
                    </div>
                    <a href="javascript:void(0);" onclick="' . esc_attr($onclick) . '" style="background:#e61e4d; color:white; padding:14px 32px; border-radius:16px; text-decoration:none; font-weight:800; font-size:1rem; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display:inline-flex; align-items:center; gap:10px; box-shadow: 0 8px 20px rgba(230,30,77,0.25);" onmouseover="this.style.transform=\'scale(1.05) translateY(-2px)\';this.style.background=\'#000\';this.style.boxShadow=\'0 12px 25px rgba(0,0,0,0.2)\'" onmouseout="this.style.transform=\'scale(1)\';this.style.background=\'#e61e4d\';this.style.boxShadow=\'0 8px 20px rgba(230,30,77,0.25)\'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:18px; height:18px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        Contact Host
                    </a>
                </div>';
                $content .= $button;
            }
        }
        return $content;
    }

    public function render_messages_center($atts)
    {
        $atts = shortcode_atts(array(
            'oversight' => '0',
        ), $atts);

        if (!is_user_logged_in()) {
            return '<p>Please log in to see messages.</p>';
        }

        $current_user_id = get_current_user_id();
        $is_oversight = ($atts['oversight'] === '1' && current_user_can('manage_options'));
        $pre_selected_contact = isset($_GET['recipient_id']) ? intval($_GET['recipient_id']) : 0;

        ob_start();
?>
        <div class="obenlo-message-center" style="display:grid; grid-template-columns: 320px 1fr; gap:0; height:750px; background:#fff; border:1px solid #eee; border-radius:20px; overflow:hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <!-- Threads Sidebar -->
            <div class="message-threads" style="border-right:1px solid #f0f0f0; background:#fcfcfc; display:flex; flex-direction:column;">
                <div style="padding:25px 20px; border-bottom:1px solid #f0f0f0; font-weight:800; background:#fff; font-size:1.1rem; color:#222; display:flex; justify-content:space-between; align-items:center;">
                    Inbox
                </div>
                <div id="obenlo-center-contacts" style="flex-grow:1; overflow-y:auto;">
                    <div style="padding:40px 20px; text-align:center; color:#999;">Loading...</div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="message-chat" style="display:flex; flex-direction:column; background:#fff;">
                <div id="obenlo-center-header" style="padding:20px 30px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:15px; background:#fff; z-index:10; display:none;">
                    <div id="obenlo-center-avatar" style="width:40px; height:40px; background:#f0f0f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#888;"></div>
                    <div>
                        <div id="obenlo-center-title" style="font-weight:800; font-size:1.1rem; color:#222;"></div>
                        <div style="font-size:0.75rem; color:#10b981; font-weight:600;">Active Now</div>
                    </div>
                </div>

                <div id="obenlo-center-empty" style="flex-grow:1; display:flex; align-items:center; justify-content:center; color:#999; flex-direction:column; background:#fafafa;">
                    <div style="background:#fff; width:100px; height:100px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:25px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#e61e4d" stroke-width="2" style="width:40px; height:40px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h4 style="margin:0; color:#333; font-weight:800;">Your Messages</h4>
                    <p style="font-size:0.95rem; margin-top:8px;">Choose a contact from the left to start a conversation.</p>
                </div>

                <div id="obenlo-center-room" style="flex-grow:1; padding:30px; overflow-y:auto; background:#fafafa; display:none; flex-direction:column;">
                </div>

                <div id="obenlo-center-input-area" style="padding:25px 30px; border-top:1px solid #f0f0f0; background:#fff; display:none;">
                    <div style="display:flex; gap:15px; align-items:center; width:100%;">
                        <div style="flex-grow:1; position:relative;">
                            <input type="text" id="obenlo-center-input" placeholder="Type your message here..." style="width:100%; padding:15px 25px; border:1px solid #eee; border-radius:30px; background:#f9f9f9; outline:none; transition:all 0.2s;" onkeypress="if(event.keyCode===13) obenloCenterSendMessage()">
                        </div>
                        <button onclick="obenloCenterSendMessage()" style="background:#e61e4d; color:white; border:none; width:50px; height:50px; border-radius:50%; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; box-shadow: 0 4px 10px rgba(230,30,77,0.2);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px; height:20px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .obenlo-center-contact-item { padding:20px; border-bottom:1px solid #f5f5f5; cursor:pointer; transition:all 0.2s; display:block; }
            .obenlo-center-contact-item:hover { background:#fff; }
            .obenlo-center-contact-item.active { background:#fff; border-left:4px solid #e61e4d; }
            .obenlo-center-msg { max-width:75%; padding:14px 20px; margin-bottom:12px; font-size:0.95rem; line-height:1.5; box-shadow: 0 2px 5px rgba(0,0,0,0.02); word-wrap: break-word; }
            .obenlo-center-msg.sent { background:#e61e4d; color:white; border-radius:15px 15px 2px 15px; margin-left:auto; }
            .obenlo-center-msg.received { background:#fff; color:#333; border-radius:15px 15px 15px 2px; margin-right:auto; border:1px solid #eee; }
        </style>

        <script>
            let obenloCenterContact = <?php echo $pre_selected_contact; ?>;
            let obenloCenterLastId = 0;
            let obenloCenterInterval = null;
            let obenloCenterUserId = <?php echo $current_user_id; ?>;
            let obenloIsOversight = <?php echo $is_oversight ? 'true' : 'false'; ?>;

            function obenloCenterFetchContacts() {
                jQuery.get('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_fetch_chat_contacts',
                    oversight: obenloIsOversight ? 1 : 0
                }, function(res) {
                    let html = '';
                    if (res.success && res.data.length > 0) {
                        res.data.forEach(function(c) {
                            let act = (c.contact_id == obenloCenterContact) ? 'active' : '';
                            let onclick = 'obenloCenterOpenRoom(' + c.contact_id + ', \'' + c.contact_name.replace(/'/g, "\\'") + '\'';
                            if (c.is_oversight_pair) {
                                onclick += ', ' + c.pair_a + ', ' + c.pair_b;
                            }
                            onclick += ')';
                            
                            html += '<div class="obenlo-center-contact-item ' + act + '" onclick="' + onclick + '">';
                            html += '<div style="display:flex; justify-content:space-between; margin-bottom:5px;">';
                            html += '<strong style="font-size:1rem; color:' + (act ? '#e61e4d' : '#222') + ';">' + c.contact_name + '</strong>';
                            html += '<span style="font-size:0.7rem; color:#aaa;">' + c.last_message_time + '</span>';
                            html += '</div>';
                            html += '<div style="font-size:0.85rem; color:#666; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + c.last_message + '</div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div style="padding:40px 20px; text-align:center; color:#999;">No messages yet.</div>';
                    }
                    document.getElementById('obenlo-center-contacts').innerHTML = html;
                });
            }

            function obenloCenterOpenRoom(contactId, contactName, pairA, pairB) {
                obenloCenterContact = contactId;
                if (obenloIsOversight && pairA) {
                    obenloCenterUserId = pairA;
                }
                document.getElementById('obenlo-center-empty').style.display = 'none';
                document.getElementById('obenlo-center-header').style.display = 'flex';
                document.getElementById('obenlo-center-room').style.display = 'flex';
                
                // Hide input area for oversight mode (read-only monitoring)
                document.getElementById('obenlo-center-input-area').style.display = obenloIsOversight ? 'none' : 'block';
                
                document.getElementById('obenlo-center-title').innerText = contactName;
                document.getElementById('obenlo-center-avatar').innerText = contactName.charAt(0).toUpperCase();
                
                document.getElementById('obenlo-center-room').innerHTML = '';
                obenloCenterLastId = 0;
                
                obenloCenterFetchContacts(); // refresh active state
                obenloCenterFetchMessages();
                
                if (obenloCenterInterval) clearInterval(obenloCenterInterval);
                obenloCenterInterval = setInterval(obenloCenterFetchMessages, 3000); // 3 sec polling
            }

            function obenloCenterFetchMessages() {
                if (!obenloCenterContact) return;
                jQuery.get('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_fetch_chat_messages',
                    contact_id: obenloCenterContact,
                    last_id: obenloCenterLastId,
                    oversight: obenloIsOversight ? 1 : 0
                }, function(res) {
                    if (res.success && res.data.length > 0) {
                        let room = document.getElementById('obenlo-center-room');
                        let isScrolledToBottom = room.scrollHeight - room.clientHeight <= room.scrollTop + 20;

                        res.data.forEach(function(msg) {
                            if (msg.id > obenloCenterLastId) {
                                let type = (msg.sender_id == obenloCenterUserId) ? 'sent' : 'received';
                                let html = '<div class="obenlo-center-msg ' + type + '">';
                                html += msg.message;
                                html += '<div style="font-size:0.7rem; opacity:0.6; margin-top:6px; text-align:' + (type === 'sent' ? 'right' : 'left') + ';">' + msg.time + '</div>';
                                html += '</div>';
                                room.insertAdjacentHTML('beforeend', html);
                                obenloCenterLastId = msg.id;
                            }
                        });
                        if (isScrolledToBottom) {
                            room.scrollTop = room.scrollHeight;
                        }
                    }
                });
            }

            function obenloCenterSendMessage() {
                let input = document.getElementById('obenlo-center-input');
                let txt = input.value.trim();
                if (!txt || !obenloCenterContact) return;
                
                input.value = '';
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_send_chat_message',
                    receiver_id: obenloCenterContact,
                    message: txt
                }, function(res) {
                    if (res.success) {
                        obenloCenterFetchMessages();
                        obenloCenterFetchContacts(); // update last message in sidebar
                    }
                });
            }

            // Init
            obenloCenterFetchContacts();
            if (obenloCenterContact > 0) {
                // Fetch user info for pre-selected contact to open room
                obenloCenterOpenRoom(obenloCenterContact, 'Conversation'); // Ideally we fetch the name, but this suffices to open the UI
            }
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_support_page()
    {
        $current_user_id = get_current_user_id();
        $ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

        ob_start();

        if ($ticket_id) {
            $this->render_ticket_details($ticket_id, $current_user_id);
        }
        else {
            $this->render_ticket_form();
        }

        return ob_get_clean();
    }

    private function render_ticket_details($ticket_id, $current_user_id)
    {
        $ticket = get_post($ticket_id);
        if (!$ticket || $ticket->post_type !== 'ticket') {
            echo '<p>Ticket not found.</p>';
            return;
        }

        // Security check: Only author or admin/agent
        if ($ticket->post_author != $current_user_id && !current_user_can('manage_support')) {
            echo '<p>You do not have permission to view this ticket.</p>';
            return;
        }

        $status = get_post_meta($ticket_id, '_obenlo_ticket_status', true);
        $type = get_post_meta($ticket_id, '_obenlo_ticket_type', true);
        $attachments = get_attached_media('image', $ticket_id);

        // Get Ticket Conversation (Replies)
        $replies = get_posts(array(
            'post_type' => 'obenlo_message', // Reuse messaging system for replies
            'meta_key' => '_obenlo_ticket_parent_id',
            'meta_value' => $ticket_id,
            'posts_per_page' => -1,
            'order' => 'ASC'
        ));

        $back_url = wp_get_referer() ?: home_url('/support');
?>
        <div class="obenlo-ticket-details" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <a href="<?php echo esc_url($back_url); ?>" style="color:#666; text-decoration:none;">← Back to Support</a>
                
                <?php if (current_user_can('manage_support') || $ticket->post_author == $current_user_id): ?>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="obenlo_update_ticket_status">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                        <?php wp_nonce_field('update_ticket_status_' . $ticket_id, 'status_nonce'); ?>
                        <?php if ($status === 'open'): ?>
                            <button type="submit" name="new_status" value="closed" style="background:#222; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9em;">Close Ticket</button>
                        <?php
            else: ?>
                            <button type="submit" name="new_status" value="open" style="background:#4CAF50; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9em;">Reopen Ticket</button>
                        <?php
            endif; ?>
                    </form>
                <?php
        endif; ?>
            </div>
            
            <div style="background:#fff; border:1px solid #eee; padding:40px; border-radius:16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom:30px;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <h2 style="margin:0;"><?php echo esc_html($ticket->post_title); ?></h2>
                    <span style="background:<?php echo $status === 'open' ? '#e61e4d' : '#333'; ?>; color:#fff; padding:5px 12px; border-radius:20px; font-size:0.8em; font-weight:bold;"><?php echo strtoupper($status); ?></span>
                </div>
                
                <div style="font-size:0.9em; color:#666; margin-bottom:30px;">
                    Type: <?php echo esc_html(ucfirst($type)); ?> | Submitted on: <?php echo get_the_date('', $ticket_id); ?>
                </div>

                <div style="line-height:1.7; color:#333; margin-bottom:30px; background:#f9f9f9; padding:20px; border-radius:12px;">
                    <?php echo wpautop(esc_html($ticket->post_content)); ?>
                    
                    <?php if (!empty($attachments)): ?>
                        <div style="margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                <?php foreach ($attachments as $att): ?>
                                    <a href="<?php echo wp_get_attachment_url($att->ID); ?>" target="_blank">
                                        <img src="<?php echo wp_get_attachment_image_url($att->ID, 'thumbnail'); ?>" style="width:100px; height:100px; object-fit:cover; border-radius:8px;">
                                    </a>
                                <?php
            endforeach; ?>
                            </div>
                        </div>
                    <?php
        endif; ?>
                </div>

                <!-- Conversation History -->
                <div class="ticket-conversation">
                    <?php if (!empty($replies)): ?>
                        <h4 style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Messages</h4>
                        <?php foreach ($replies as $reply):
                $is_admin_reply = user_can($reply->post_author, 'administrator');
                $is_internal = get_post_meta($reply->ID, '_obenlo_is_internal_note', true);

                // Only show internal notes to staff
                if ($is_internal && !current_user_can('manage_support'))
                    continue;

                $bubble_style = $is_admin_reply ? 'background:#f1f1f1; border-left:4px solid #e61e4d;' : 'background:#fff; border:1px solid #eee;';
                if ($is_internal)
                    $bubble_style = 'background:#fff9c4; border-left:4px solid #fbc02d;';
?>
                            <div style="padding:15px 20px; border-radius:12px; margin-bottom:15px; <?php echo $bubble_style; ?>">
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <strong>
                                        <?php
                if ($is_internal)
                    echo '🔒 Internal Note';
                elseif ($is_admin_reply)
                    echo 'Obenlo Support';
                else
                    echo esc_html(get_userdata($reply->post_author)->display_name);
?>
                                    </strong>
                                    <span style="font-size:0.8em; color:#999;"><?php echo get_the_date('M j, H:i', $reply->ID); ?></span>
                                </div>
                                <div style="font-size:0.95em; line-height:1.6;">
                                    <?php echo wpautop(esc_html($reply->post_content)); ?>
                                </div>
                            </div>
                        <?php
            endforeach; ?>
                    <?php
        endif; ?>
                </div>

                <!-- Reply Form -->
                <?php if ($status === 'open'): ?>
                    <div style="margin-top:40px; border-top:2px solid #f1f1f1; padding-top:30px;">
                        <h4 style="margin-bottom:15px;">Add a Reply</h4>
                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                            <input type="hidden" name="action" value="obenlo_submit_ticket_reply">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                            <?php wp_nonce_field('submit_ticket_reply_' . $ticket_id, 'reply_nonce'); ?>
                            
                            <textarea name="reply_content" required rows="4" placeholder="Type your message here..." style="width:100%; padding:15px; border:1px solid #ddd; border-radius:12px; margin-bottom:15px; font-family:inherit;"></textarea>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <?php if (current_user_can('manage_support')): ?>
                                    <label style="font-size:0.9em; display:flex; align-items:center; gap:8px; cursor:pointer;">
                                        <input type="checkbox" name="is_internal_note" value="1"> 
                                        <span>Post as Internal Note (Staff only)</span>
                                    </label>
                                <?php
            else: ?>
                                    <span></span>
                                <?php
            endif; ?>
                                <button type="submit" style="background:#e61e4d; color:white; border:none; padding:12px 30px; border-radius:8px; cursor:pointer; font-weight:bold;">Send Message</button>
                            </div>
                        </form>
                    </div>
                <?php
        else: ?>
                    <div style="margin-top:30px; text-align:center; padding:20px; background:#f9f9f9; border-radius:12px; color:#666;">
                        This ticket is closed. If you still need help, please reopen the ticket or create a new one.
                    </div>
                <?php
        endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_ticket_form()
    {
?>
        <div class="obenlo-support-page" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
            <h2>Contact Obenlo Support</h2>
            <p>Need help with a booking or want to raise a dispute with a host? Fill out the form below.</p>
            
            <?php if (isset($_GET['ticket_sent'])): ?>
                <div style="background:#d4edda; padding:20px; margin-bottom:30px; border-radius:12px; border:1px solid #c3e6cb; color:#155724;">
                    <strong>Success!</strong> Your ticket has been received. Our team will review it and get back to you at your registration email.
                </div>
            <?php
        endif; ?>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data" style="background:#fff; border:1px solid #eee; padding:30px; border-radius:16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <input type="hidden" name="action" value="obenlo_submit_ticket">
                <?php wp_nonce_field('submit_ticket', 'ticket_nonce'); ?>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Reason for Contact:</label>
                    <select name="ticket_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                        <option value="support">General Support</option>
                        <option value="dispute">Report an Issue / Dispute with Host</option>
                        <option value="account">Account / Billing Question</option>
                        <option value="demo">Request a Demo of Obenlo</option>
                    </select>
                </div>

                <?php if (!is_user_logged_in()): ?>
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Your Email:</label>
                    <input type="email" name="ticket_email" required placeholder="For us to reply to you" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <?php
        endif; ?>

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
    public function handle_submit_ticket()
    {
        if (!isset($_POST['ticket_nonce']) || !wp_verify_nonce($_POST['ticket_nonce'], 'submit_ticket')) {
            obenlo_redirect_with_error('security_failed');
        }

        $user_id = get_current_user_id();
        $title = sanitize_text_field($_POST['ticket_title']);
        $content = wp_kses_post($_POST['ticket_content']);
        $type = sanitize_text_field($_POST['ticket_type']); // 'support' or 'dispute'
        $target_host_id = isset($_POST['host_id']) ? intval($_POST['host_id']) : 0;
        $ticket_email = isset($_POST['ticket_email']) ? sanitize_email($_POST['ticket_email']) : '';

        $ticket_id = wp_insert_post(array(
            'post_type' => 'ticket',
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $user_id
        ));

        if ($ticket_id && !is_wp_error($ticket_id)) {
            update_post_meta($ticket_id, '_obenlo_ticket_type', $type);
            update_post_meta($ticket_id, '_obenlo_ticket_status', 'open');
            if ($target_host_id) {
                update_post_meta($ticket_id, '_obenlo_target_host_id', $target_host_id);
            }
            if ($ticket_email) {
                update_post_meta($ticket_id, '_obenlo_ticket_email', $ticket_email);
            }

            // Handle Photo Attachments (for proof)
            if (!empty($_FILES['ticket_attachments']['name'][0])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $files = $_FILES['ticket_attachments'];
                foreach ($files['name'] as $key => $value) {
                    if ($files['name'][$key]) {
                        $file = array(
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key]
                        );
                        $attachment_id = media_handle_sideload($file, $ticket_id);
                        if (is_wp_error($attachment_id)) {
                        // Log error?
                        }
                    }
                }
            }

            // Notify Admin
            Obenlo_Booking_Notifications::notify_ticket_event($ticket_id, 'new_ticket');

            $redirect_url = remove_query_arg('ticket_sent', wp_get_referer());
            $redirect_url = add_query_arg('obenlo_modal', 'ticket_submitted', $redirect_url);
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Handle support ticket reply submission
     */
    public function handle_submit_ticket_reply()
    {
        if (!is_user_logged_in()) {
            obenlo_redirect_with_error('unauthorized');
        }

        $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
        if (!isset($_POST['reply_nonce']) || !wp_verify_nonce($_POST['reply_nonce'], 'submit_ticket_reply_' . $ticket_id)) {
            obenlo_redirect_with_error('security_failed');
        }

        $ticket = get_post($ticket_id);
        $user_id = get_current_user_id();

        // Security: Author or Agent
        if ($ticket->post_author != $user_id && !current_user_can('manage_support')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $content = wp_kses_post($_POST['reply_content']);

        $reply_id = wp_insert_post(array(
            'post_type' => 'obenlo_message',
            'post_title' => "Reply to Ticket #$ticket_id",
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $user_id
        ));

        if ($reply_id) {
            update_post_meta($reply_id, '_obenlo_ticket_parent_id', $ticket_id);

            $is_internal = isset($_POST['is_internal_note']) && current_user_can('manage_support');
            if ($is_internal) {
                update_post_meta($reply_id, '_obenlo_is_internal_note', '1');
            }
            else {
                // Notify other party (don't notify for internal notes)
                Obenlo_Booking_Notifications::notify_ticket_event($ticket_id, 'ticket_reply');
            }

            wp_safe_redirect(add_query_arg('ticket_id', $ticket_id, wp_get_referer()));
            exit;
        }
    }

    /**
     * Handle ticket status updates
     */
    public function handle_ticket_status()
    {
        $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
        if (!isset($_POST['status_nonce']) || !wp_verify_nonce($_POST['status_nonce'], 'update_ticket_status_' . $ticket_id)) {
            obenlo_redirect_with_error('security_failed');
        }

        $ticket = get_post($ticket_id);
        $user_id = get_current_user_id();

        if ($ticket->post_author != $user_id && !current_user_can('manage_support')) {
            obenlo_redirect_with_error('unauthorized');
        }

        $new_status = sanitize_text_field($_POST['new_status']);
        update_post_meta($ticket_id, '_obenlo_ticket_status', $new_status);

        wp_safe_redirect(add_query_arg('ticket_id', $ticket_id, wp_get_referer()));
        exit;
    }

    /**
     * Handle Admin Broadcast sending
     */
    public function handle_send_broadcast()
    {
        if (!current_user_can('administrator')) {
            obenlo_redirect_with_error('unauthorized');
        }

        if (!isset($_POST['broadcast_nonce']) || !wp_verify_nonce($_POST['broadcast_nonce'], 'send_broadcast')) {
            obenlo_redirect_with_error('security_failed');
        }

        $title = sanitize_text_field($_POST['broadcast_title']);
        $content = wp_kses_post($_POST['broadcast_content']);
        $recipient_role = sanitize_text_field($_POST['broadcast_role']); // 'host', 'guest', or 'all'

        $broadcast_id = wp_insert_post(array(
            'post_type' => 'broadcast',
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish'
        ));

        if ($broadcast_id) {
            update_post_meta($broadcast_id, '_obenlo_broadcast_recipient', $recipient_role);

            // Notify Users via Email
            Obenlo_Booking_Notifications::notify_broadcast_event($broadcast_id, $recipient_role);

            wp_safe_redirect(add_query_arg('broadcast_sent', '1', wp_get_referer()));
            exit;
        }
    }

    /**
     * Static helper to get open tickets for a user
     */
    public static function get_user_tickets($user_id)
    {
        return get_posts(array(
            'post_type' => 'ticket',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
    }

    /**
     * Static helper to get recent broadcasts for a user role
     */
    public static function get_role_broadcasts($role)
    {
        return get_posts(array(
            'post_type' => 'broadcast',
            'meta_query' => array(
                'relation' => 'OR',
                    array('key' => '_obenlo_broadcast_recipient', 'value' => $role),
                    array('key' => '_obenlo_broadcast_recipient', 'value' => 'all')
            ),
            'posts_per_page' => 5
        ));
    }

    /**
     * Handle Native Chat AJAX Send (Using Custom Table)
     */
    public function handle_send_chat_message()
    {
        check_ajax_referer('obenlo_chat_nonce', 'nonce');
        $sender_id = get_current_user_id();
        $receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

        if (!$sender_id || !$receiver_id || empty($message)) {
            wp_send_json_error('Invalid request');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_chat_messages';

        $inserted = $wpdb->insert(
            $table,
            array(
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'created_at' => current_time('mysql'),
            'is_read' => 0
        ),
            array('%d', '%d', '%s', '%s', '%d')
        );

        if ($inserted) {
            $msg_id = $wpdb->insert_id;

            // Trigger Push Notification
            if (class_exists('Obenlo_Booking_Push_Notifications')) {
                $sender_info = get_userdata($sender_id);
                $sender_name = $sender_info ? $sender_info->display_name : 'Someone';
                Obenlo_Booking_Push_Notifications::send_push(
                    $receiver_id,
                    "New message from $sender_name",
                    wp_trim_words($message, 15, '...'),
                    home_url('/messages')
                );
            }

            wp_send_json_success(array(
                'id' => $msg_id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'message' => wp_kses_post($message),
                'time' => current_time('H:i')
            ));
        }

        wp_send_json_error('Database error');
    }

    /**
     * Handle Native Chat AJAX Fetch (Using Custom Table)
     */
    public function handle_fetch_chat_messages()
    {
        check_ajax_referer('obenlo_chat_nonce', 'nonce');
        $user_id = get_current_user_id();
        $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
        $is_oversight = (isset($_GET['oversight']) && $_GET['oversight'] == '1' && current_user_can('manage_options'));

        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_chat_messages';

        if ($is_oversight) {
            // In oversight mode, fetch messages between ANY two users (sender/receiver or receiver/sender)
            $query = $wpdb->prepare("
                SELECT * FROM $table 
                WHERE ((sender_id = %d AND receiver_id = %d) 
                   OR (sender_id = %d AND receiver_id = %d))
                  AND id > %d
                ORDER BY id ASC
                LIMIT 500
            ", $user_id, $contact_id, $contact_id, $user_id, $last_id);
            // Note: $user_id here is actually the first person in the conversation pair passed from the frontend
        } else {
            $query = $wpdb->prepare("
                SELECT * FROM $table 
                WHERE ((sender_id = %d AND receiver_id = %d) 
                   OR (sender_id = %d AND receiver_id = %d))
                  AND id > %d
                ORDER BY id ASC
                LIMIT 100
            ", $user_id, $contact_id, $contact_id, $user_id, $last_id);
        }

        $messages = $wpdb->get_results($query);
        $data = array();

        foreach ($messages as $msg) {
            $data[] = array(
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'message' => wp_kses_post($msg->message),
                'time' => gmdate('H:i', strtotime($msg->created_at) + (get_option('gmt_offset') * HOUR_IN_SECONDS))
            );

            // Mark as read if received by current user
            if ($msg->receiver_id == $user_id && $msg->is_read == 0) {
                $wpdb->update($table, array('is_read' => 1), array('id' => $msg->id));
            }
        }

        wp_send_json_success($data);
    }

    /**
     * Handle Native Chat AJAX Fetch Contacts (Recent conversations)
     */
    public function handle_fetch_chat_contacts()
    {
        check_ajax_referer('obenlo_chat_nonce', 'nonce');
        $user_id = get_current_user_id();
        $is_oversight = (isset($_GET['oversight']) && $_GET['oversight'] == '1' && current_user_can('manage_options'));

        if (!$user_id) {
            wp_send_json_error('Not logged in');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'obenlo_chat_messages';

        if ($is_oversight) {
            // Oversight: Get ALL unique conversation pairs on the site
            $query = "
                SELECT DISTINCT 
                    LEAST(sender_id, receiver_id) as user_a, 
                    GREATEST(sender_id, receiver_id) as user_b 
                FROM $table 
                ORDER BY id DESC 
                LIMIT 50
            ";
            $results = $wpdb->get_results($query);
            $contacts = array();

            foreach ($results as $row) {
                $user_a = get_userdata($row->user_a);
                $user_b = get_userdata($row->user_b);
                
                if ($user_a && $user_b) {
                    $last_msg_query = $wpdb->prepare("
                        SELECT message, created_at FROM $table 
                        WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)
                        ORDER BY id DESC LIMIT 1
                    ", $row->user_a, $row->user_b, $row->user_b, $row->user_a);
                    $last_msg = $wpdb->get_row($last_msg_query);

                    $contacts[] = array(
                        'contact_id' => $row->user_b, // In oversight mode, we'll use user_b as the 'contact' and pass user_a as current_user in JS if needed, but for simplicity we'll just show the pair
                        'contact_name' => $user_a->display_name . ' & ' . $user_b->display_name,
                        'last_message' => $last_msg ? wp_trim_words($last_msg->message, 8) : '',
                        'last_message_time' => $last_msg ? gmdate('M j, H:i', strtotime($last_msg->created_at) + (get_option('gmt_offset') * HOUR_IN_SECONDS)) : '',
                        'unread_count' => 0,
                        'is_oversight_pair' => true,
                        'pair_a' => $row->user_a,
                        'pair_b' => $row->user_b
                    );
                }
            }
            wp_send_json_success($contacts);
        }

        // Standard user view: Get all unique contacts the user has chatted with
        $query = $wpdb->prepare("
            SELECT DISTINCT
                CASE WHEN sender_id = %d THEN receiver_id ELSE sender_id END as contact_id
            FROM $table
            WHERE sender_id = %d OR receiver_id = %d
        ", $user_id, $user_id, $user_id);

        $results = $wpdb->get_results($query);
        $contacts = array();

        foreach ($results as $row) {
            $contact_user = get_userdata($row->contact_id);
            if ($contact_user) {
                // Get last message info
                $last_msg_query = $wpdb->prepare("
                    SELECT message, is_read, sender_id, created_at 
                    FROM $table 
                    WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)
                    ORDER BY id DESC LIMIT 1
                ", $user_id, $row->contact_id, $row->contact_id, $user_id);

                $last_msg = $wpdb->get_row($last_msg_query);

                // Check unread count
                $unread_query = $wpdb->prepare("
                    SELECT COUNT(*) 
                    FROM $table 
                    WHERE receiver_id = %d AND sender_id = %d AND is_read = 0
                ", $user_id, $row->contact_id);

                $unread_count = $wpdb->get_var($unread_query);

                $contacts[] = array(
                    'contact_id' => $row->contact_id,
                    'contact_name' => $contact_user->display_name,
                    'last_message' => $last_msg ? wp_trim_words($last_msg->message, 8) : '',
                    'last_message_time' => $last_msg ? gmdate('M j, H:i', strtotime($last_msg->created_at) + (get_option('gmt_offset') * HOUR_IN_SECONDS)) : '',
                    'unread_count' => intval($unread_count)
                );
            }
        }

        // Sort contacts by latest message time theoretically, but simplify for now
        wp_send_json_success($contacts);
    }

    /**
     * Render Frontend Floating Chat Widget for logged-in users
     */
    public function render_frontend_chat_widget()
    {
        if (!is_user_logged_in()) {
            return; // Don't show for logged out users. Admins can see it for testing.
        }
        $current_user_id = get_current_user_id();
?>
        <div id="obenlo-floating-chat-container">
            <style>
                #obenlo-floating-chat-container {
                    position: fixed; bottom: 30px; right: 30px; z-index: 9999;
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }
                #obenlo-chat-bubble {
                    width: 65px; height: 65px; background: #e61e4d; color: white;
                    border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    cursor: pointer; box-shadow: 0 8px 25px rgba(230,30,77,0.3); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    border: 2px solid rgba(255,255,255,0.1);
                }
                #obenlo-chat-bubble:hover { transform: scale(1.1) rotate(5deg); box-shadow: 0 12px 30px rgba(230,30,77,0.4); }
                #obenlo-chat-bubble svg { width: 32px; height: 32px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
                
                #obenlo-chat-window {
                    display: none; position: absolute; bottom: 85px; right: 0;
                    width: 380px; height: 600px; background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
                    border-radius: 24px; border: 1px solid rgba(255,255,255,0.3);
                    box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden; flex-direction: column;
                    transition: all 0.3s ease; transform-origin: bottom right;
                }
                .obenlo-chat-header {
                    background: linear-gradient(135deg, #e61e4d 0%, #d91b42 100%); color: white; padding: 20px 25px; font-weight: 800;
                    display: flex; justify-content: space-between; align-items: center; font-size: 1.1rem;
                }
                .obenlo-chat-header-close { cursor: pointer; font-size: 24px; line-height: 1; opacity: 0.8; transition: opacity 0.2s; }
                .obenlo-chat-header-close:hover { opacity: 1; }
                .obenlo-chat-header-back { cursor: pointer; display: none; margin-right: 12px; font-size: 1.2rem; }
                
                #obenlo-chat-contacts, #obenlo-chat-room { flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; background: transparent; }
                #obenlo-chat-room { display: none; }
                
                .obenlo-chat-contact-item { padding: 18px 25px; border-bottom: 1px solid rgba(0,0,0,0.03); cursor: pointer; display: flex; align-items: center; transition: all 0.2s; }
                .obenlo-chat-contact-item:hover { background: rgba(230,30,77,0.03); }
                
                .obenlo-badge { background: #e61e4d; color: white; font-size: 11px; font-weight: 800; border-radius: 12px; padding: 2px 8px; margin-left: auto; box-shadow: 0 2px 5px rgba(230,30,77,0.3); }
                
                .obenlo-chat-messages-list { flex-grow: 1; padding: 20px; overflow-y: auto; background: transparent; display: flex; flex-direction: column; }
                .obenlo-chat-msg { max-width: 85%; padding: 12px 18px; margin-bottom: 12px; border-radius: 18px; font-size: 14px; line-height: 1.5; word-wrap: break-word; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
                .obenlo-chat-msg.sent { background: #e61e4d; color: white; margin-left: auto; border-bottom-right-radius: 4px; box-shadow: 0 4px 12px rgba(230,30,77,0.15); }
                .obenlo-chat-msg.received { background: white; color: #222; border: 1px solid rgba(0,0,0,0.05); margin-right: auto; border-bottom-left-radius: 4px; }
                
                .obenlo-chat-input-area { padding: 20px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; gap: 12px; background: rgba(255,255,255,0.5); }
                .obenlo-chat-input-area input { flex-grow: 1; padding: 12px 20px; border: 1px solid rgba(0,0,0,0.08); border-radius: 30px; outline: none; transition: all 0.2s; font-size: 0.95rem; background: #fff; }
                .obenlo-chat-input-area input:focus { border-color: #e61e4d; box-shadow: 0 0 0 3px rgba(230,30,77,0.1); }
                .obenlo-chat-input-area button { background: #e61e4d; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 4px 10px rgba(230,30,77,0.2); }
                .obenlo-chat-input-area button:hover { transform: scale(1.05); background: #000; }
                
                @media (max-width: 500px) {
                    #obenlo-chat-window { position: fixed; bottom: 0; right: 0; width: 100%; height: 100%; border-radius: 0; }
                }
            </style>
            
            <div id="obenlo-chat-window">
                <div class="obenlo-chat-header">
                    <div style="display:flex; align-items:center;">
                        <span class="obenlo-chat-header-back" onclick="obenloBackToContacts()">←</span>
                        <span id="obenlo-chat-title">Messages</span>
                    </div>
                    <span class="obenlo-chat-header-close" onclick="obenloToggleChat()">×</span>
                </div>
                
                <div id="obenlo-chat-contacts">
                    <div style="padding: 20px; text-align: center; color: #999;" id="obenlo-contacts-loading">Loading...</div>
                </div>
                
                <div id="obenlo-chat-room">
                    <div class="obenlo-chat-messages-list" id="obenlo-messages-container"></div>
                    <div class="obenlo-chat-input-area">
                        <input type="text" id="obenlo-chat-input-field" placeholder="Type a message..." onkeypress="if(event.keyCode===13) obenloSendMessage()">
                        <button onclick="obenloSendMessage()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </div>
            </div>
            
        </div>

        <script>
            let obenloChatOpen = false;
            let obenloCurrentContact = 0;
            let obenloLastMsgId = 0;
            let obenloChatInterval = null;
            let obenloCurrentUserId = <?php echo $current_user_id; ?>;

            function obenloToggleChat() {
                obenloChatOpen = !obenloChatOpen;
                document.getElementById('obenlo-chat-window').style.display = obenloChatOpen ? 'flex' : 'none';
                if (obenloChatOpen) {
                    if (obenloCurrentContact === 0) {
                        obenloFetchContacts();
                    } else {
                        obenloFetchMessages();
                    }
                } else {
                    if (obenloChatInterval) clearInterval(obenloChatInterval);
                }
            }

            function obenloFetchContacts() {
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_fetch_chat_contacts',
                    nonce: '<?php echo wp_create_nonce("obenlo_chat_nonce"); ?>'
                }, function(res) {
                    let html = '';
                    if (res.success && res.data.length > 0) {
                        res.data.forEach(function(c) {
                            let unread = c.unread_count > 0 ? '<span class="obenlo-badge">' + c.unread_count + '</span>' : '';
                            html += '<div class="obenlo-chat-contact-item" onclick="obenloOpenRoom(' + c.contact_id + ', \'' + c.contact_name.replace(/'/g, "\\'") + '\', \'' + (c.contact_avatar || '') + '\')">';
                            if (c.contact_avatar) {
                                html += '<img src="' + c.contact_avatar + '" style="width:40px;height:40px;border-radius:50%;margin-right:15px;object-fit:cover;">';
                            } else {
                                html += '<div style="width:40px;height:40px;background:#eee;border-radius:50%;margin-right:15px;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#666;">' + c.contact_name.charAt(0).toUpperCase() + '</div>';
                            }
                            html += '<div style="flex-grow:1;overflow:hidden;">';
                            html += '<div style="font-weight:bold;color:#222;">' + c.contact_name + '</div>';
                            html += '<div style="font-size:12px;color:#999;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + c.last_message + '</div>';
                            html += '</div>' + unread + '</div>';
                        });
                    } else {
                        html = '<div style="padding:40px 20px;text-align:center;color:#999;">No conversations yet</div>';
                    }
                    document.getElementById('obenlo-chat-contacts').innerHTML = html;
                });
            }

            function obenloOpenRoom(contactId, contactName, contactAvatar) {
                obenloCurrentContact = contactId;
                document.getElementById('obenlo-chat-contacts').style.display = 'none';
                document.getElementById('obenlo-chat-room').style.display = 'flex';
                document.querySelector('.obenlo-chat-header-back').style.display = 'block';
                document.getElementById('obenlo-chat-title').innerText = contactName;
                
                if (contactAvatar) {
                    document.getElementById('obenlo-center-avatar').innerHTML = '<img src="' + contactAvatar + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                } else {
                    document.getElementById('obenlo-center-avatar').innerText = contactName.charAt(0).toUpperCase();
                }
                document.getElementById('obenlo-messages-container').innerHTML = '';
                obenloLastMsgId = 0;
                
                obenloFetchMessages();
                if (obenloChatInterval) clearInterval(obenloChatInterval);
                obenloChatInterval = setInterval(obenloFetchMessages, 3000); // 3 sec polling
            }

            function obenloBackToContacts() {
                obenloCurrentContact = 0;
                if (obenloChatInterval) clearInterval(obenloChatInterval);
                document.getElementById('obenlo-chat-contacts').style.display = 'flex';
                document.getElementById('obenlo-chat-room').style.display = 'none';
                document.querySelector('.obenlo-chat-header-back').style.display = 'none';
                document.getElementById('obenlo-chat-title').innerText = 'Messages';
                obenloFetchContacts();
            }

            function obenloFetchMessages() {
                if (!obenloCurrentContact) return;
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_fetch_chat_messages',
                    contact_id: obenloCurrentContact,
                    last_id: obenloLastMsgId,
                    nonce: '<?php echo wp_create_nonce("obenlo_chat_nonce"); ?>'
                }, function(res) {
                    let container = document.getElementById('obenlo-messages-container');
                    if (res.success && res.data.length > 0) {
                        let isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 20;
                        
                        res.data.forEach(function(msg) {
                            if (msg.id > obenloLastMsgId) {
                                let typeClass = (msg.sender_id == obenloCurrentUserId) ? 'sent' : 'received';
                                let html = '<div class="obenlo-chat-msg ' + typeClass + '">';
                                html += msg.message;
                                html += '<div style="font-size:10px;opacity:0.7;margin-top:4px;text-align:right;">' + msg.time + '</div>';
                                html += '</div>';
                                container.insertAdjacentHTML('beforeend', html);
                                obenloLastMsgId = msg.id;
                            }
                        });
                        if (isScrolledToBottom) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }
                });
            }

            function obenloSendMessage() {
                let input = document.getElementById('obenlo-chat-input-field');
                let txt = input.value.trim();
                if (!txt || !obenloCurrentContact) return;
                
                input.value = '';
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'obenlo_send_chat_message',
                    receiver_id: obenloCurrentContact,
                    message: txt,
                    nonce: '<?php echo wp_create_nonce("obenlo_chat_nonce"); ?>'
                }, function(res) {
                    if (res.success) {
                        obenloFetchMessages();
                    }
                });
            }

            window.obenloStartChatWith = function(hostId, hostName, hostAvatar) {
                if (!obenloChatOpen) obenloToggleChat();
                obenloOpenRoom(hostId, hostName, hostAvatar);
            };
        </script>
        <?php
    }
}
