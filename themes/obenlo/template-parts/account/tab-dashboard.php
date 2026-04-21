<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; margin-top: 0;">Welcome back, <?php echo esc_html( $user->first_name ?: $user->display_name ); ?>!</h2>
            <p style="color:#666; font-size:1.05rem; margin-bottom:30px;">Manage your stays, communicate with hosts, and keep your profile updated.</p>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:40px;">
                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#eff6ff; color:#3b82f6; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;"><?php echo __('My Trips & Bookings', 'obenlo'); ?></h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">View your upcoming bookings and past adventures.</p>
                    </div>
                    <a href="?tab=trips" style="margin-top:20px; display:inline-block; font-weight:700; color:#3b82f6; text-decoration:none;">View Trips &rarr;</a>
                </div>

                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#fef2f2; color:#ef4444; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;">Messages</h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">Check unread messages from your hosts.</p>
                    </div>
                    <a href="?tab=messages" style="margin-top:20px; display:inline-block; font-weight:700; color:#ef4444; text-decoration:none;">Open Inbox &rarr;</a>
                </div>

                <div style="background:#fff; border:1px solid #eee; border-radius:20px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="width:48px; height:48px; background:#ecfdf5; color:#10b981; border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px; height:24px;"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                        </div>
                        <h3 style="margin:0 0 8px 0; font-size:1.2rem; font-weight:800;">Guest Guide</h3>
                        <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.5;">Learn about platform rules, policies, and tips.</p>
                    </div>
                    <a href="?tab=guide" style="margin-top:20px; display:inline-block; font-weight:700; color:#10b981; text-decoration:none;">Read Guide &rarr;</a>
                </div>
            </div>

            <div style="background: #222; border-radius: 20px; padding: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h3 style="color: #fff; margin: 0 0 8px 0; font-size: 1.4rem; font-weight: 800;">Looking for your next stay?</h3>
                    <p style="color: #bbb; margin: 0; font-size: 1rem;">Explore thousands of incredible listings on Obenlo.</p>
                </div>
                <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background: #e61e4d; color: #fff; font-weight: 700; padding: 12px 24px; border-radius: 12px; text-decoration: none; display: inline-block; transition: all 0.2s;">Start Exploring</a>
            </div>

