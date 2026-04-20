<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>

<div style="margin-bottom: 40px;">
    <h2 style="font-size: 2.22rem; font-weight: 800; margin: 0 0 8px 0; color: #222; letter-spacing: -0.8px;">Welcome back, <?php echo esc_html( $user->first_name ?: $user->display_name ); ?>!</h2>
    <p style="color:#666; font-size:1.1rem; margin:0; font-weight: 500; opacity: 0.8;">Your next adventure starts here. Manage your stays and profile with ease.</p>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:25px; margin-bottom:45px;">
    <!-- Trips Card -->
    <div style="background:#fff; border:1px solid #eee; border-radius:24px; padding:32px; box-shadow:0 10px 40px rgba(0,0,0,0.03); display:flex; flex-direction:column; justify-content:space-between; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 40px rgba(0,0,0,0.03)';" onclick="window.location.href='?tab=trips'">
        <div>
            <div style="width:56px; height:56px; background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color:#3b82f6; border-radius:18px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:26px; height:26px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <h3 style="margin:0 0 10px 0; font-size:1.35rem; font-weight:800; color: #222; letter-spacing: -0.4px;"><?php echo __('Trips & Bookings', 'obenlo'); ?></h3>
            <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.6; font-weight: 500;">Review upcoming plans, past memories, and detailed itineraries.</p>
        </div>
        <div style="margin-top:25px; display:flex; align-items: center; gap: 8px; font-weight:800; color:#3b82f6; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <span>View Trips</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>
    </div>

    <!-- Messages Card -->
    <div style="background:#fff; border:1px solid #eee; border-radius:24px; padding:32px; box-shadow:0 10px 40px rgba(0,0,0,0.03); display:flex; flex-direction:column; justify-content:space-between; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 40px rgba(0,0,0,0.03)';" onclick="window.location.href='?tab=messages'">
        <div>
            <div style="width:56px; height:56px; background:linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); color:#e11d48; border-radius:18px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:26px; height:26px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3 style="margin:0 0 10px 0; font-size:1.35rem; font-weight:800; color: #222; letter-spacing: -0.4px;">Direct Messages</h3>
            <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.6; font-weight: 500;">Stay in touch with your hosts and get the support you need.</p>
        </div>
        <div style="margin-top:25px; display:flex; align-items: center; gap: 8px; font-weight:800; color:#e11d48; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <span>Open Inbox</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>
    </div>

    <!-- Personal Card -->
    <div style="background:#fff; border:1px solid #eee; border-radius:24px; padding:32px; box-shadow:0 10px 40px rgba(0,0,0,0.03); display:flex; flex-direction:column; justify-content:space-between; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 50px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 40px rgba(0,0,0,0.03)';" onclick="window.location.href='?tab=profile'">
        <div>
            <div style="width:56px; height:56px; background:linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); color:#16a34a; border-radius:18px; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:26px; height:26px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <h3 style="margin:0 0 10px 0; font-size:1.35rem; font-weight:800; color: #222; letter-spacing: -0.4px;">Profile & Security</h3>
            <p style="color:#666; font-size:0.95rem; margin:0; line-height:1.6; font-weight: 500;">Update your information and manage your account privacy.</p>
        </div>
        <div style="margin-top:25px; display:flex; align-items: center; gap: 8px; font-weight:800; color:#16a34a; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <span>Edit Profile</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>
    </div>
</div>

<div style="position: relative; overflow: hidden; background: #222; border-radius: 28px; padding: 45px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.1);">
    <!-- Decorative background element -->
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(230,30,77,0.1); border-radius: 50%; blur: 80px; pointer-events: none;"></div>
    
    <div style="position: relative; z-index: 2; flex: 1; min-width: 300px;">
        <h3 style="color: #fff; margin: 0 0 12px 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.6px;">Looking for your next stay?</h3>
        <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 1.05rem; font-weight: 400; line-height: 1.6;">Explore a curated selection of incredible listings designed for comfort and adventure.</p>
    </div>
    
    <div style="position: relative; z-index: 2;">
        <a href="<?php echo esc_url( home_url('/listings') ); ?>" style="background: #e61e4d; color: #fff; font-weight: 800; padding: 18px 36px; border-radius: 16px; text-decoration: none; display: inline-block; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 25px rgba(230,30,77,0.3); font-size: 1rem; letter-spacing: 0.3px;" onmouseover="this.style.background='#ff385c'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='#e61e4d'; this.style.transform='scale(1)';">Start Exploring Now</a>
    </div>
</div>


