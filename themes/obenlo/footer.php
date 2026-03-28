</div><!-- .site-content -->

<footer class="site-footer" style="background: #f7f7f7; border-top: 1px solid #dddddd; padding: 60px 20px; margin-top: 60px;">
    <div class="footer-inner" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: left; color: #222;">
        
        <div class="footer-col">
            <h4 style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid var(--obenlo-primary); display: inline-block; padding-bottom: 5px;"><?php esc_html_e('Support', 'obenlo'); ?></h4>
            <ul style="list-style: none; padding: 0; font-size: 0.9em; line-height: 2;">
                <li><a href="<?php echo esc_url(get_option('obenlo_support_url', home_url('/support'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Help Center', 'obenlo'); ?></a></li>
                <li><a href="<?php echo esc_url(get_option('obenlo_how_it_works_url', home_url('/how-it-works'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('How Obenlo works', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/faq'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('FAQ', 'obenlo'); ?></a></li>
                <li><a href="#" class="trigger-contact-modal" style="color: inherit; text-decoration: none;"><?php esc_html_e('Contact Us', 'obenlo-booking'); ?></a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4 style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid var(--obenlo-primary); display: inline-block; padding-bottom: 5px;"><?php esc_html_e('Hosting', 'obenlo'); ?></h4>
            <ul style="list-style: none; padding: 0; font-size: 0.9em; line-height: 2;">
                <li><a href="<?php echo home_url('/become-a-host'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Obenlo your home', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/host-dashboard'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Host Dashboard', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/faq?type=host'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Support for Hosts', 'obenlo'); ?></a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4 style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid var(--obenlo-primary); display: inline-block; padding-bottom: 5px;"><?php esc_html_e('Obenlo', 'obenlo'); ?></h4>
            <ul style="list-style: none; padding: 0; font-size: 0.9em; line-height: 2;">
                <li><a href="<?php echo home_url('/about-us'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('About Us', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/blog'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Blog', 'obenlo'); ?></a></li>
                <li><a href="<?php echo esc_url(get_option('obenlo_community_url', home_url('/community'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Community', 'obenlo'); ?></a></li>
                <li><a href="<?php echo esc_url(get_option('obenlo_trust_safety_url', home_url('/trust-safety'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Trust & Safety', 'obenlo'); ?></a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4 style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid var(--obenlo-primary); display: inline-block; padding-bottom: 5px;"><?php esc_html_e('Legal', 'obenlo'); ?></h4>
            <ul style="list-style: none; padding: 0; font-size: 0.9em; line-height: 2;">
                <li><a href="<?php echo esc_url(get_option('obenlo_privacy_url', home_url('/privacy'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Privacy Policy', 'obenlo'); ?></a></li>
                <li><a href="<?php echo esc_url(get_option('obenlo_terms_url', home_url('/terms'))); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Terms of Service', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/cancellation-policy'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Cancellation Policy', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/refund-policy'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Refund Policy', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/guest-rules'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Guest Rules', 'obenlo'); ?></a></li>
                <li><a href="<?php echo home_url('/global-policies'); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e('Global Policies', 'obenlo'); ?></a></li>
            </ul>
        </div>


        <div class="footer-col">
            <h4 style="margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid var(--obenlo-primary); display: inline-block; padding-bottom: 5px;"><?php esc_html_e('Follow Us', 'obenlo-booking'); ?></h4>
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <?php 
                $socials = array(
                    'facebook'  => array('url' => get_option('obenlo_facebook_url', 'https://www.facebook.com/obenlobooking'), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'),
                    'instagram' => array('url' => get_option('obenlo_instagram_url', 'https://www.instagram.com/obenlobooking'), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.332 3.608 1.308.975.975 1.245 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.063 1.366-.333 2.633-1.308 3.608-.975.975-2.242 1.245-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.063-2.633-.333-3.608-1.308-.975-.975-1.245-2.242-1.308-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.332-2.633 1.308-3.608.975-.975 2.242-1.245 3.608-1.308 1.266-.058 1.646-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.058-1.281.072-1.689.072-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.28-.058-1.688-.072-4.947-.072zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>'),
                    'twitter'   => array('url' => get_option('obenlo_twitter_url', ''), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'),
                    'linkedin'  => array('url' => get_option('obenlo_linkedin_url', ''), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451c.98 0 1.771-.773 1.771-1.729V1.729C24 .774 23.205 0 22.225 0z"/></svg>'),
                    'youtube'   => array('url' => get_option('obenlo_youtube_url', ''), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>'),
                    'tiktok'    => array('url' => get_option('obenlo_tiktok_url', ''), 'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.9-.32-1.9-.23-2.74.12-.69.31-1.27.87-1.62 1.53-.45.77-.55 1.69-.3 2.53.23.82.72 1.58 1.41 2.02.49.33 1.05.51 1.63.53.94.04 1.9-.25 2.65-.89.65-.54 1.05-1.37 1.15-2.22V.02z"/></svg>')
                );

                foreach ($socials as $key => $data) {
                    if (!empty($data['url'])) {
                        echo '<a href="' . esc_url($data['url']) . '" target="_blank" style="color: #222; font-size: 1.5em; text-decoration: none;">' . $data['icon'] . '</a>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <div style="max-width: 1200px; margin: 40px auto 0; padding-top: 40px; border-top: 1px solid #eee; display: flex; flex-direction: column; align-items: center; gap: 20px;">
        <a href="<?php echo esc_url(home_url('/')); ?>" style="display: block; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 50" style="height: 34px; width: auto; opacity: 0.8; filter: grayscale(1); display: block;">
                <text x="0" y="38" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="38" font-weight="800" fill="var(--obenlo-primary)" letter-spacing="-1.5"><?php echo esc_html(get_option('obenlo_brand_name', 'Obenlo')); ?></text>
            </svg>
        </a>

        <div style="color: #666; font-size: 0.85em;">
            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>, Inc. <?php echo __('All rights reserved.', 'obenlo-booking'); ?> &middot; <a href="<?php echo esc_url(get_option('obenlo_privacy_url', home_url('/privacy'))); ?>" style="color: inherit; text-decoration: none;"><?php echo __('Privacy', 'obenlo-booking'); ?></a> &middot; <a href="<?php echo esc_url(get_option('obenlo_terms_url', home_url('/terms'))); ?>" style="color: inherit; text-decoration: none;"><?php echo __('Terms', 'obenlo-booking'); ?></a> &middot; <?php echo __('Sitemap', 'obenlo-booking'); ?>
        </div>
    </div>
</footer>

<!-- Global Action Confirmation Modal -->
<div id="obenlo-success-modal">
    <div class="modal-content">
        <div id="modal-icon" style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
        <h2 id="modal-title" style="margin-bottom: 15px; font-size: 1.8rem;"><?php echo __('Success!', 'obenlo-booking'); ?></h2>
        <p id="modal-message" style="color: #666; margin-bottom: 30px; font-size: 1.1rem; line-height: 1.6;"><?php echo __('Your action was completed successfully.', 'obenlo-booking'); ?></p>
        
        <div id="modal-actions" style="display: flex; flex-direction: column; gap: 12px;">
            <!-- Buttons will be injected here -->
        </div>
        
        <button onclick="closeObenloModal()" style="margin-top: 20px; background: transparent; border: none; color: #999; cursor: pointer; font-size: 0.9rem; text-decoration: underline;"><?php echo __('Close', 'obenlo-booking'); ?></button>
    </div>
</div>

<script>
    function closeObenloModal() {
        document.getElementById('obenlo-success-modal').classList.remove('active');
        setTimeout(() => {
            document.getElementById('obenlo-success-modal').style.display = 'none';
        }, 300);
        // Clean URL without refresh
        const url = new URL(window.location);
        url.searchParams.delete('obenlo_modal');
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const modalType = urlParams.get('obenlo_modal');
        
        if (modalType) {
            const modal = document.getElementById('obenlo-success-modal');
            const title = document.getElementById('modal-title');
            const msg = document.getElementById('modal-message');
            const icon = document.getElementById('modal-icon');
            const actions = document.getElementById('modal-actions');
            
            let btn1Text = '<?php echo esc_js(__('Dashboard', 'obenlo-booking')); ?>';
            let btn1Url = '<?php echo home_url('/host-dashboard'); ?>';
            let btn2Text = '<?php echo esc_js(__('Home', 'obenlo-booking')); ?>';
            let btn2Url = '<?php echo home_url(); ?>';

            if (modalType === 'listing_saved') {
                icon.innerText = '🏠';
                title.innerText = '<?php echo esc_js(__('Listing Saved!', 'obenlo-booking')); ?>';
                msg.innerText = '<?php echo esc_js(__('Your listing has been updated and is now live on Obenlo.', 'obenlo-booking')); ?>';
                btn1Text = '<?php echo esc_js(__('Manage Listings', 'obenlo-booking')); ?>';
                btn1Url = '<?php echo home_url('/host-dashboard#listings'); ?>';
            } else if (modalType === 'booking_confirmed') {
                icon.innerText = '✨';
                title.innerText = '<?php echo esc_js(__('Booking Confirmed!', 'obenlo-booking')); ?>';
                msg.innerText = '<?php echo esc_js(__('Pack your bags! Your booking is confirmed and the host has been notified.', 'obenlo-booking')); ?>';
                btn1Text = '<?php echo esc_js(__('View My Trips', 'obenlo-booking')); ?>';
                btn1Url = '<?php echo home_url('/account?tab=trips'); ?>';
            } else if (modalType === 'ticket_submitted') {
                icon.innerText = '✉️';
                title.innerText = '<?php echo esc_js(__('Ticket Received', 'obenlo-booking')); ?>';
                msg.innerText = '<?php echo esc_js(__('Our support team has received your request and will get back to you shortly.', 'obenlo-booking')); ?>';
                btn1Text = '<?php echo esc_js(__('Support Center', 'obenlo-booking')); ?>';
                btn1Url = '<?php echo home_url('/support'); ?>';
            }

            actions.innerHTML = `
                <a href="${btn1Url}" style="background: var(--obenlo-primary); color: white; padding: 14px; border-radius: 12px; text-decoration: none; font-weight: bold; font-size: 1rem;">${btn1Text}</a>
                <a href="${btn2Url}" style="background: #f7f7f7; color: #222; padding: 14px; border-radius: 12px; text-decoration: none; font-weight: bold; font-size: 1rem; border: 1px solid #ddd;">${btn2Text}</a>
            `;

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        // Language Switcher Logic
        document.querySelectorAll('.obenlo-lang-switch').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const lang = this.getAttribute('data-lang');
                // Set cookie for 1 year
                document.cookie = "obenlo_lang=" + lang + "; path=/; max-age=" + (60*60*24*365);
                // Reload to apply translation
                window.location.reload();
            });
        });

        // Close user dropdown when clicking outside
        document.addEventListener('click', function(event) {
            var menu = document.getElementById('headerUserMenu');
            if (menu && !menu.contains(event.target)) {
                menu.classList.remove('active');
            }
            var langMenu = document.getElementById('headerLangSwitcher');
            if (langMenu && !langMenu.contains(event.target)) {
                langMenu.classList.remove('active');
            }
        });

    });
</script>

<!-- Contact Us Modal -->
<div id="obenlo-contact-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 100%; max-width: 500px; border-radius: 16px; padding: 30px; position: relative; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <button id="close-contact-modal" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
        <h2 style="margin-bottom: 10px; font-size: 1.5rem;"><?php echo __('Contact Us', 'obenlo-booking'); ?></h2>
        <p style="color: #666; margin-bottom: 20px;"><?php echo __("Have a question? Send us a message and we'll get back to you shortly.", 'obenlo-booking'); ?></p>
        
        <form id="contact-us-form">
            <input type="hidden" name="action" value="obenlo_submit_contact_form">
            <input type="hidden" name="contact_nonce" value="<?php echo wp_create_nonce('obenlo_contact_nonce'); ?>">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;"><?php echo __('Your Name', 'obenlo-booking'); ?></label>
                <input type="text" name="contact_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;"><?php echo __('Your Email', 'obenlo-booking'); ?></label>
                <input type="email" name="contact_email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;"><?php echo __('Message', 'obenlo-booking'); ?></label>
                <textarea name="contact_message" required rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; resize: vertical;"></textarea>
            </div>
            
            <button type="submit" style="background: var(--obenlo-primary); color: #fff; font-weight: bold; padding: 14px 24px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem;">
                <?php echo __('Send Message', 'obenlo-booking'); ?>
            </button>
            <div id="contact-form-response" style="margin-top: 15px; font-weight: bold; text-align: center; display: none;"></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('obenlo-contact-modal');
    const triggers = document.querySelectorAll('.trigger-contact-modal');
    const closeBtn = document.getElementById('close-contact-modal');
    const form = document.getElementById('contact-us-form');
    const responseDiv = document.getElementById('contact-form-response');

    if(modal && triggers.length > 0) {
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'flex';
            });
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            responseDiv.style.display = 'none';
            form.reset();
        });

        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
                responseDiv.style.display = 'none';
                form.reset();
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerText = '<?php echo esc_js(__('Sending...', 'obenlo-booking')); ?>';
            submitBtn.disabled = true;
            responseDiv.style.display = 'none';

            const formData = new FormData(form);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.innerText = '<?php echo esc_js(__('Send Message', 'obenlo-booking')); ?>';
                submitBtn.disabled = false;
                responseDiv.style.display = 'block';
                
                if (data.success) {
                    responseDiv.style.color = 'green';
                    responseDiv.innerText = data.data.message || '<?php echo esc_js(__('Message sent successfully!', 'obenlo-booking')); ?>';
                    form.reset();
                    setTimeout(() => {
                        modal.style.display = 'none';
                        responseDiv.style.display = 'none';
                    }, 3000);
                } else {
                    responseDiv.style.color = 'red';
                    responseDiv.innerText = data.data.message || '<?php echo esc_js(__('Error sending message. Please try again.', 'obenlo-booking')); ?>';
                }
            })
            .catch(error => {
                submitBtn.innerText = '<?php echo esc_js(__('Send Message', 'obenlo-booking')); ?>';
                submitBtn.disabled = false;
                responseDiv.style.display = 'block';
                responseDiv.style.color = 'red';
                responseDiv.innerText = '<?php echo esc_js(__('Network error. Please try again later.', 'obenlo-booking')); ?>';
            });
        });
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>
