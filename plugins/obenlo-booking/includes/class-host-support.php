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
            <h2 class="dashboard-title"><?php echo __('The Obenlo Business Manual', 'obenlo'); ?></h2>
        </div>
        <p style="color:#666; font-size:1.1rem; margin-bottom:50px; max-width: 900px; line-height:1.6;">
            <?php echo __('This guide is your single source of truth for running a professional business on Obenlo. Follow these pillars to ensure your storefront is high-converting, your operations are smooth, and your payouts are fast.', 'obenlo'); ?>
        </p>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">01</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#e61e4d; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <?php echo __('Identity: Managing Your Storefront', 'obenlo'); ?>
            </h3>
            <ul style="color:#555; font-size:1rem; line-height:2; margin-top:20px; padding-left:20px;">
                <li><strong>Brand Presence:</strong> Upload a high-resolution logo and a covering banner that represents your service environment.</li>
                <li><strong>The Trust Checkmark:</strong> Complete your identity verification in the <b>Verification</b> tab. Businesses with the crimson badge convert 40% better.</li>
                <li><strong>Social Links:</strong> Connect your Facebook and Instagram in the <b>Storefront</b> settings to show guests you have a wider community.</li>
                <li><strong>BIO Strategy:</strong> Write in the first person. Explain your expertise and what makes your service unique.</li>
            </ul>
        </div>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); border-left: 8px solid #3b82f6; position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">02</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#3b82f6; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <?php echo __('Inventory: Main vs. Bookables', 'obenlo'); ?>
            </h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:30px; margin-top:25px;">
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;"><?php echo __('Main Listing (The Hub)', 'obenlo'); ?></h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">This is your general business page. It hosts your address, main photos, and overall description. It is <b>not</b> bookable on its own.</p>
                </div>
                <div style="background:#f1f5f9; padding:25px; border-radius:24px;">
                    <h4 style="margin:0 0 10px 0; font-weight:800; color:#1e293b;"><?php echo __('Bookables (The Products)', 'obenlo'); ?></h4>
                    <p style="margin:0; font-size:0.95rem; color:#475569; line-height:1.6;">These are the specific units or slots guests pay for. Create individual bookables for every room, tour time, or service type you offer.</p>
                </div>
            </div>
            <p style="margin-top:20px; font-size:0.95rem; color:#e61e4d; font-weight:700;">PRO TIP: Use "Units" if you have multiple identical bookables (like 5 Deluxe Rooms) so you don't have to create 5 separate listings.</p>
        </div>

        <div style="background:#fff; border:1px solid #eee; border-radius:32px; padding:45px; margin-bottom:40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); border-left: 8px solid #10b981; position:relative; overflow:hidden;">
            <div style="position:absolute; top:40px; right:40px; font-size:4rem; font-weight:900; color:#f8f9fa; line-height:1; user-select:none;">03</div>
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#10b981; display:flex; align-items:center; gap:15px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:28px; height:28px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <?php echo __('Operations: Path to Payout', 'obenlo'); ?>
            </h3>
            <div style="display:flex; flex-direction:column; gap:20px; margin-top:25px;">
                <div style="display:flex; gap:20px; align-items:flex-start;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">1</div>
                    <div><h4 style="margin:0; font-weight:800;"><?php echo __('Approve Requests', 'obenlo'); ?></h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#666;">Speed is key. Approve bookings in the <b>Bookings</b> tab to lock in the guest's payment.</p></div>
                </div>
                <div style="display:flex; gap:20px; align-items:flex-start;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">2</div>
                    <div><h4 style="margin:0; font-weight:800;"><?php echo __('Mark Check-In', 'obenlo'); ?></h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#666;">When the service starts, mark "Check In". This prevents fraudulent refund requests.</p></div>
                </div>
                <div style="display:flex; gap:20px; align-items:flex-start; background:#fff8f1; padding:20px; border-radius:20px; border:1px dashed #f59e0b;">
                    <div style="width:30px; height:30px; border-radius:50%; background:#f59e0b; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:800; font-size:0.8rem;">3</div>
                    <div><h4 style="margin:0; font-weight:800; color:#92400e;">CRITICAL: Complete Service</h4><p style="margin:5px 0 0 0; font-size:0.95rem; color:#b45309; font-weight:600;">You MUST click "Mark Completed" after the stay/service is finished. Money ONLY enters your withdrawable balance AFTER this action.</p></div>
                </div>
            </div>
        </div>

        <div style="background:#fffcf2; border:1px solid #fde047; border-radius:32px; padding:45px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); border-left: 8px solid #d97706;">
            <h3 style="margin-top:0; font-size:1.6rem; font-weight:800; color:#d97706;"><?php echo __('Safety: Protection & Support', 'obenlo'); ?></h3>
            <p style="color:#713f12; font-size:1.05rem; line-height:1.7;"><strong>Zero Leakage Policy:</strong> Never accept payments outside of Obenlo. Doing so removes all insurance coverage and will lead to an immediate ban.</p>
        </div>
        <?php
    }
}
