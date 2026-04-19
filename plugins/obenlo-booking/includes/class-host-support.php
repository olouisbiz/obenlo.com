<?php
/**
 * Host Support Module
 * Single Responsibility: Support ticket UI + Host Guide educational content.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Support
{
    public function render_support_section()
    {
        $user_id = get_current_user_id();
        $tickets = Obenlo_Booking_Communication::get_user_tickets($user_id);
        ?>
        <div class="dashboard-header">
            <h2 class="dashboard-title"><?php echo __('Support & Assistance', 'obenlo'); ?></h2>
        </div>

        <?php if (isset($_GET['ticket_sent'])): ?>
            <div style="background:#ecfdf5; color:#065f46; padding:15px 20px; border-radius:12px; margin-bottom:30px; border:1px solid #a7f3d0; font-weight:600;">
                ✓ <?php echo __('Ticket submitted successfully! Our team will review it and get back to you.', 'obenlo'); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid-layout" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:40px; align-items: start;">
            <div class="form-section" style="margin-bottom:0; background:#fcfcfc;">
                <h4 style="margin-top:0; margin-bottom:20px;"><?php echo __('Open New Ticket', 'obenlo'); ?></h4>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                    <input type="hidden" name="action" value="obenlo_submit_ticket">
                    <input type="hidden" name="ticket_type" value="support">
                    <?php wp_nonce_field('submit_ticket', 'ticket_nonce'); ?>
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; font-size:0.9rem;"><?php echo __('Subject', 'obenlo'); ?></label>
                        <input type="text" name="ticket_title" placeholder="<?php echo esc_attr(__('How can we help?', 'obenlo')); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; font-size:0.9rem;"><?php echo __('Message Detail', 'obenlo'); ?></label>
                        <textarea name="ticket_content" placeholder="<?php echo esc_attr(__('Describe your issue or question...', 'obenlo')); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; height:120px;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%; padding:12px;"><?php echo __('Create Ticket', 'obenlo'); ?></button>
                </form>
            </div>

            <div>
                <h4 style="margin-top:0; margin-bottom:20px;"><?php echo __('Support History', 'obenlo'); ?></h4>
                <?php if (empty($tickets)): ?>
                    <div style="background:#fff; border:1px dashed #ddd; padding:40px; border-radius:15px; text-align:center; color:#888;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px; height:40px; margin-bottom:15px; opacity:0.3;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <p><?php echo __('No support history found.', 'obenlo'); ?></p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <?php foreach ($tickets as $ticket):
                            $status = get_post_meta($ticket->ID, '_obenlo_ticket_status', true);
                            $status_class = 'badge-info';
                            if ($status === 'closed' || $status === 'resolved') $status_class = 'badge-success';
                            if ($status === 'open') $status_class = 'badge-warning';
                        ?>
                            <div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:15px; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                                    <h5 style="margin:0; font-size:1.05rem;"><?php echo esc_html($ticket->post_title); ?></h5>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo esc_html(strtoupper($status)); ?></span>
                                </div>
                                <div style="font-size:0.9rem; color:#666; margin-bottom:15px; line-height:1.5;"><?php echo wp_trim_words($ticket->post_content, 20); ?></div>
                                <div style="border-top:1px solid #f5f5f5; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:0.75rem; color:#aaa;"><?php echo __('Last updated:', 'obenlo'); ?> <?php echo get_the_modified_date('', $ticket->ID); ?></span>
                                    <a href="<?php echo esc_url(add_query_arg('ticket_id', $ticket->ID, home_url('/support'))); ?>" style="color:#e61e4d; font-weight:700; text-decoration:none; font-size:0.9rem;"><?php echo __('View Conversation →', 'obenlo'); ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function render_host_guide()
    {
        ?>
        <div class="dashboard-header">
            <h2 class="dashboard-title"><?php echo __('The Ultimate Hosting Guide', 'obenlo'); ?></h2>
        </div>
        <p style="color:#666; font-size:1.1rem; margin-bottom:50px; max-width: 900px; line-height:1.6;">
            <?php echo __('Welcome to Obenlo! This guide is designed to help you understand the platform and take you from zero to launching a stunning, high-converting listing. Follow these easy steps to build trust and grow your business.', 'obenlo'); ?>
        </p>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">01</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#e61e4d; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <?php echo __('Get Your Account Ready', 'obenlo'); ?>
            </h3>
            <p style="color:#555; font-size:1.05rem; line-height:1.7; margin-top:15px;">Before welcoming your first guest, make sure your profile builds trust and your payouts are configured.</p>
            <ul style="color:#555; font-size:1rem; line-height:2; margin-top:20px; padding-left:20px;">
                <li><strong>Identity Verification:</strong> Guests prefer hosts they can trust. Head over to the <b>Verification</b> tab to upload your ID. The verified badge drastically increases your bookings.</li>
                <li><strong>Storefront Setup:</strong> On the <b>Storefront</b> tab, upload a professional logo and a beautiful banner. Write a friendly, first-person bio about your background and what you offer.</li>
                <li><strong>Link Social Media:</strong> Connect your Instagram or Facebook to let guests explore your broader community.</li>
                <li><strong>Payouts:</strong> Don't forget to configure your payout settings so you can receive your earnings smoothly!</li>
            </ul>
        </div>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); border-left: 8px solid #3b82f6; position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">02</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#3b82f6; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <?php echo __('Create the Perfect Listing', 'obenlo'); ?>
            </h3>
            <p style="color:#555; font-size:1.05rem; line-height:1.7; margin-top:15px;">Your listing is your storefront product. Here is how to make it irresistible to guests.</p>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:30px; margin-top:25px;">
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;">Choose the Right Category</h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">Obenlo automatically adapts your booking form based on the category you choose. For example, selecting "Chauffeur" will ask the guest for pickup/drop-off locations, while selecting "Stay" will show a nightly calendar. Always pick the most specific subcategory available!</p>
                </div>
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;">High-Quality Photos</h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">Upload bright, well-lit photos. The main photo should clearly show the value of your service or property. Listings with 5 or more high-quality photos receive up to three times as many bookings.</p>
                </div>
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;">Compelling Descriptions</h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">Use the description to answer common questions. Tell them what to expect, what is included, and any special amenities you provide.</p>
                </div>
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;">Offer Add-ons</h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">In the Features tab, use "Extra Perks" or "Add-ons" to upsell additional services (e.g., breakfast included, expedited delivery, or VIP access). This is a great way to boost your earnings.</p>
                </div>
            </div>
        </div>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); border-left: 8px solid #10b981; position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">03</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#10b981; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <?php echo __('Delivering an Amazing Experience', 'obenlo'); ?>
            </h3>
            <p style="color:#555; font-size:1.05rem; line-height:1.7; margin-top:15px;">Your listing is live! Now it's time to impress your clients and collect 5-star reviews.</p>
            <div style="display:flex; flex-direction:column; gap:20px; margin-top:25px;">
                <div style="display:flex; gap:20px; align-items:flex-start;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">1</div>
                    <div><h4 style="margin:0; font-weight:800;">Respond Quickly</h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#666;">Check your Inbox regularly. Responding to messages rapidly shows guests you care and dramatically increases your chances of getting a booking.</p></div>
                </div>
                <div style="display:flex; gap:20px; align-items:flex-start;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">2</div>
                    <div><h4 style="margin:0; font-weight:800;">Manage Your Bookings</h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#666;">Approve requests as soon as they come in. When the day arrives, make sure your service matches the high standards of your listing.</p></div>
                </div>
                <div style="display:flex; gap:20px; align-items:flex-start;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">3</div>
                    <div><h4 style="margin:0; font-weight:800;">Get Glowing Reviews</h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#666;">After the service is done, kindly ask your guest to leave a review. Future clients will look at these reviews first before deciding to book you!</p></div>
                </div>
            </div>
        </div>
        <?php
    }
}
